<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Model\TodaysArrival;
use App\Model\TodaysArrivalBranch;
use App\Model\Product;
use App\Model\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TodaysArrivalController extends Controller
{
    /**
     * Get today's arrivals for mobile app
     */
    public function index(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            $offset = $request->get('offset', 0);
            $branchId = $request->get('branch_id');
            $date = $request->get('date', today()->format('Y-m-d'));
            
            $query = TodaysArrival::active()
                ->appVisible()
                ->forDate($date);
            
            // Filter by branch if provided
            if ($branchId) {
                $query->forBranch($branchId);
            }
            
            $totalCount = $query->count();
            
            $arrivals = $query->orderBy('sort_order')
                             ->orderBy('created_at', 'desc')
                             ->skip($offset)
                             ->take($limit)
                             ->get();
            
            // Transform data for mobile app
            $formattedArrivals = $arrivals->map(function($arrival) {
                return $this->formatArrivalData($arrival);
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Today\'s arrivals retrieved successfully',
                'data' => [
                    'arrivals' => $formattedArrivals,
                    'total_count' => $totalCount,
                    'limit' => (int) $limit,
                    'offset' => (int) $offset,
                    'date' => $date,
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve today\'s arrivals',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific arrival details
     */
    public function show(Request $request, $id)
    {
        try {
            $branchId = $request->get('branch_id');
            
            $arrival = TodaysArrival::active()
                ->appVisible()
                ->where('id', $id)
                ->first();
            
            if (!$arrival) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arrival not found or not available'
                ], 404);
            }
            
            // Check if arrival is available for the requested branch
            if ($branchId && $arrival->branch_id && !in_array($branchId, $arrival->branch_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arrival not available for this branch'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Arrival details retrieved successfully',
                'data' => $this->formatArrivalData($arrival, true),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve arrival details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get arrivals by date range
     */
    public function getByDateRange(Request $request)
    {
        try {
            $startDate = $request->get('start_date', today()->format('Y-m-d'));
            $endDate = $request->get('end_date', today()->addDays(7)->format('Y-m-d'));
            $branchId = $request->get('branch_id');

            $query = TodaysArrival::active()
                ->appVisible()
                ->whereBetween('arrival_date', [$startDate, $endDate]);
            
            if ($branchId) {
                $query->forBranch($branchId);
            }

            $arrivals = $query->orderBy('arrival_date')
                ->orderBy('sort_order')
                ->get()
                ->groupBy(function($arrival) {
                    return $arrival->arrival_date->format('Y-m-d');
                })
                ->map(function($arrivals, $date) {
                    return [
                        'date' => $date,
                        'date_formatted' => \Carbon\Carbon::parse($date)->format('M d, Y'),
                        'arrivals' => $arrivals->map(function($arrival) {
                            return $this->formatArrivalData($arrival);
                        })->values()
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Arrivals retrieved successfully',
                'data' => [
                    'arrivals_by_date' => $arrivals,
                    'date_range' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve arrivals',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all available branches for today's arrivals
     */
    public function branches()
    {
        try {
            // Get all active branches that have today's arrivals
            $branchIds = TodaysArrival::active()
                ->appVisible()
                ->whereNotNull('branch_id')
                ->pluck('branch_id')
                ->flatten()
                ->unique()
                ->filter()
                ->values();

            $branches = Branch::whereIn('id', $branchIds)
                ->get()
                ->map(function ($branch) {
                    return [
                        'id' => $branch->id,
                        'name' => $branch->name,
                        'phone' => $branch->phone,
                        'whatsapp_number' => $branch->whatsapp_number,
                        'address' => $branch->address,
                        'latitude' => $branch->latitude ?? null,
                        'longitude' => $branch->longitude ?? null,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $branches,
                'total' => $branches->count(),
                'message' => 'Branches retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve branches',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate WhatsApp checkout URL for specific arrival
     */
    public function whatsappCheckout(Request $request, $id)
    {
        try {
            $arrival = TodaysArrival::active()
                ->appVisible()
                ->where('id', $id)
                ->first();
            
            if (!$arrival) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arrival not found'
                ], 404);
            }

            $branchId = $request->input('branch_id');
            $selectedProducts = $request->input('products', []); // Array of product IDs with quantities
            $customerName = $request->input('customer_name', '');
            $customerPhone = $request->input('customer_phone', '');
            $notes = $request->input('notes', '');

            // Get branch details
            $branch = null;
            if ($branchId && $arrival->branch_id && in_array($branchId, $arrival->branch_id)) {
                $branch = Branch::find($branchId);
            } else if ($arrival->branch_id && count($arrival->branch_id) > 0) {
                // Use first branch if no specific branch requested
                $branch = Branch::find($arrival->branch_id[0]);
            }

            if (!$branch) {
                return response()->json([
                    'success' => false,
                    'message' => 'No branch available for this arrival'
                ], 404);
            }

            // Build custom message for checkout
            $message = "🛒 *New Order from Today's Arrival*\n\n";
            $message .= "� *Arrival:* {$arrival->title}\n";
            $message .= "📍 *Branch:* {$branch->name}\n";
            $message .= "📆 *Date:* " . $arrival->arrival_date->format('d/m/Y') . "\n\n";

            if ($customerName) {
                $message .= "👤 *Customer:* {$customerName}\n";
            }
            if ($customerPhone) {
                $message .= "📱 *Phone:* {$customerPhone}\n";
            }

            if (!empty($selectedProducts) && $arrival->product_ids) {
                $message .= "\n🛍️ *Interested Products:*\n";
                $productIds = array_column($selectedProducts, 'id');
                $availableProducts = Product::whereIn('id', $productIds)
                    ->whereIn('id', $arrival->product_ids)
                    ->get();
                
                foreach ($selectedProducts as $productData) {
                    $product = $availableProducts->firstWhere('id', $productData['id']);
                    if ($product) {
                        $quantity = $productData['quantity'] ?? 1;
                        $message .= "• {$product->name} x {$quantity}\n";
                    }
                }
            }

            if ($notes) {
                $message .= "\n📝 *Notes:* {$notes}\n";
            }

            $message .= "\n✅ Please confirm availability and pricing.";

            // Create WhatsApp URL
            $whatsappNumber = $branch->whatsapp_number ?? $branch->phone;
            $whatsappUrl = "https://wa.me/{$whatsappNumber}?text=" . urlencode($message);

            return response()->json([
                'success' => true,
                'whatsapp_url' => $whatsappUrl,
                'message_preview' => $message,
                'branch' => [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'whatsapp_number' => $whatsappNumber,
                    'address' => $branch->address,
                ],
                'message' => 'WhatsApp checkout URL generated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate WhatsApp checkout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format arrival data for API response
     */
    private function formatArrivalData($arrival, $includeProducts = false)
    {
        $baseUrl = config('app.url');
        
        // Get branches for this arrival
        $branches = [];
        if ($arrival->branch_id) {
            $branches = Branch::whereIn('id', $arrival->branch_id)
                ->get()
                ->map(function($branch) {
                    return [
                        'id' => $branch->id,
                        'name' => $branch->name,
                        'phone' => $branch->phone,
                        'whatsapp_number' => $branch->whatsapp_number,
                        'address' => $branch->address,
                        'latitude' => $branch->latitude ?? null,
                        'longitude' => $branch->longitude ?? null,
                    ];
                })
                ->toArray();
        }
        
        $data = [
            'id' => $arrival->id,
            'title' => $arrival->title,
            'description' => $arrival->description,
            'arrival_date' => $arrival->arrival_date->format('Y-m-d'),
            'arrival_date_formatted' => $arrival->arrival_date->format('M d, Y'),
            'poster_images' => $arrival->formatted_poster_images,
            'main_poster' => $arrival->main_poster ? $baseUrl . '/storage/' . $arrival->main_poster : null,
            'products_count' => count($arrival->product_ids ?? []),
            'branches_count' => count($branches),
            'branches' => $branches,
            'whatsapp_message' => $arrival->formatted_whatsapp_message,
            'sort_order' => $arrival->sort_order,
            'created_at' => $arrival->created_at->toISOString(),
            'updated_at' => $arrival->updated_at->toISOString(),
        ];

        if ($includeProducts && $arrival->product_ids) {
            $products = Product::whereIn('id', $arrival->product_ids)
                ->get()
                ->map(function($product) use ($baseUrl) {
                    $imageUrl = null;
                    if ($product->image) {
                        if (filter_var($product->image, FILTER_VALIDATE_URL)) {
                            $imageUrl = $product->image;
                        } else {
                            $imageUrl = $baseUrl . '/storage/product/' . $product->image;
                        }
                    }
                    
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description ?? '',
                        'price' => (float) $product->price,
                        'discount_price' => $product->discount_price ? (float) $product->discount_price : null,
                        'image' => $imageUrl,
                        'unit' => $product->unit ?? 'pc',
                        'tax' => (float) ($product->tax ?? 0),
                        'available_time_starts' => $product->available_time_starts ?? null,
                        'available_time_ends' => $product->available_time_ends ?? null,
                        'status' => (int) ($product->status ?? 1),
                        'created_at' => $product->created_at->toISOString(),
                    ];
                });
            
            $data['products'] = $products;
        }

        return $data;
    }
}