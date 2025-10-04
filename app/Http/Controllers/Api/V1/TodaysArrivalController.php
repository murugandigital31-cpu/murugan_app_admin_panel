<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TodaysArrival;
use App\Models\TodaysArrivalBranch;
use App\Models\Product;
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
            $branchId = $request->header('branch-id') ?? $request->branch_id;
            $date = $request->date ?? today()->format('Y-m-d');
            
            $arrivals = TodaysArrival::with(['arrivalBranch', 'products'])
                ->active()
                ->appVisible()
                ->forDate($date)
                ->when($branchId, function($query, $branchId) {
                    return $query->forBranch($branchId);
                })
                ->orderBy('sort_order')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($arrival) {
                    return $this->formatArrivalData($arrival);
                });

            return response()->json([
                'success' => true,
                'message' => 'Today\'s arrivals retrieved successfully',
                'data' => $arrivals,
                'total' => $arrivals->count(),
                'date' => $date,
            ]);

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
            $branchId = $request->header('branch-id') ?? $request->branch_id;
            
            $arrival = TodaysArrival::with(['arrivalBranch', 'products'])
                ->active()
                ->appVisible()
                ->when($branchId, function($query, $branchId) {
                    return $query->forBranch($branchId);
                })
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Arrival details retrieved successfully',
                'data' => $this->formatArrivalData($arrival, true),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Arrival not found or not available',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get arrivals by date range
     */
    public function getByDateRange(Request $request)
    {
        try {
            $startDate = $request->start_date ?? today()->format('Y-m-d');
            $endDate = $request->end_date ?? today()->addDays(7)->format('Y-m-d');
            $branchId = $request->header('branch-id') ?? $request->branch_id;

            $arrivals = TodaysArrival::with(['arrivalBranch', 'products'])
                ->active()
                ->appVisible()
                ->whereBetween('arrival_date', [$startDate, $endDate])
                ->when($branchId, function($query, $branchId) {
                    return $query->forBranch($branchId);
                })
                ->orderBy('arrival_date')
                ->orderBy('sort_order')
                ->get()
                ->groupBy(function($arrival) {
                    return $arrival->arrival_date->format('Y-m-d');
                })
                ->map(function($arrivals, $date) {
                    return [
                        'date' => $date,
                        'arrivals' => $arrivals->map(function($arrival) {
                            return $this->formatArrivalData($arrival);
                        })
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Arrivals retrieved successfully',
                'data' => $arrivals,
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
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
            $branches = TodaysArrivalBranch::active()
                ->withArrivals()
                ->get()
                ->map(function ($branch) {
                    return [
                        'id' => $branch->id,
                        'name' => $branch->name,
                        'location' => $branch->location,
                        'whatsapp_number' => $branch->formatted_whatsapp,
                        'whatsapp_link' => $branch->whatsapp_link,
                        'contact_person' => $branch->contact_person,
                        'address' => $branch->address,
                        'arrivals_count' => $branch->todaysArrivals->count(),
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
            $arrival = TodaysArrival::with('arrivalBranch')
                ->active()
                ->whatsappEnabled()
                ->findOrFail($id);

            $products = $request->input('products', []); // Array of product IDs with quantities
            $customerName = $request->input('customer_name', '');
            $customerPhone = $request->input('customer_phone', '');
            $notes = $request->input('notes', '');

            // Build custom message for checkout
            $message = "🛒 *New Order from Today's Arrival*\n\n";
            $message .= "📅 *Arrival:* {$arrival->title}\n";
            $message .= "📍 *Branch:* {$arrival->arrivalBranch->name}\n";
            $message .= "📆 *Date:* " . $arrival->arrival_date->format('d/m/Y') . "\n\n";

            if ($customerName) {
                $message .= "👤 *Customer:* {$customerName}\n";
            }
            if ($customerPhone) {
                $message .= "📱 *Phone:* {$customerPhone}\n";
            }

            if (!empty($products)) {
                $message .= "\n🛍️ *Ordered Items:*\n";
                $productIds = array_column($products, 'id');
                $arrivalProducts = Product::whereIn('id', $productIds)->get();
                
                foreach ($products as $productData) {
                    $product = $arrivalProducts->firstWhere('id', $productData['id']);
                    if ($product) {
                        $quantity = $productData['quantity'] ?? 1;
                        $message .= "• {$product->name} x {$quantity}\n";
                    }
                }
            }

            if ($notes) {
                $message .= "\n📝 *Notes:* {$notes}\n";
            }

            $message .= "\n✅ Please confirm availability and total price.";

            $whatsappUrl = $arrival->arrivalBranch->whatsapp_link . '?text=' . urlencode($message);

            return response()->json([
                'success' => true,
                'whatsapp_url' => $whatsappUrl,
                'message' => $message,
                'branch' => [
                    'name' => $arrival->arrivalBranch->name,
                    'whatsapp_number' => $arrival->arrivalBranch->formatted_whatsapp,
                    'contact_person' => $arrival->arrivalBranch->contact_person,
                ],
                'message_text' => 'WhatsApp checkout URL generated successfully'
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
        
        $data = [
            'id' => $arrival->id,
            'title' => $arrival->title,
            'description' => $arrival->description,
            'arrival_date' => $arrival->arrival_date->format('Y-m-d'),
            'arrival_date_formatted' => $arrival->arrival_date->format('M d, Y'),
            'main_poster' => $arrival->main_poster ? $baseUrl . '/storage/' . $arrival->main_poster : null,
            'poster_images' => $arrival->poster_images ? array_map(function($image) use ($baseUrl) {
                return $baseUrl . '/storage/' . $image;
            }, $arrival->poster_images) : [],
            'products_count' => count($arrival->product_ids ?? []),
            'whatsapp_enabled' => $arrival->whatsapp_enabled,
            'whatsapp_message' => $arrival->formatted_whatsapp_message,
            'branch' => [
                'id' => $arrival->arrivalBranch->id,
                'name' => $arrival->arrivalBranch->name,
                'location' => $arrival->arrivalBranch->location,
                'whatsapp_number' => $arrival->arrivalBranch->formatted_whatsapp,
                'whatsapp_link' => $arrival->arrivalBranch->whatsapp_link,
                'contact_person' => $arrival->arrivalBranch->contact_person,
                'address' => $arrival->arrivalBranch->address,
            ],
            'sort_order' => $arrival->sort_order,
            'created_at' => $arrival->created_at->toISOString(),
        ];

        if ($includeProducts && $arrival->product_ids) {
            $products = Product::whereIn('id', $arrival->product_ids)
                ->with(['category'])
                ->get()
                ->map(function($product) use ($baseUrl) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description ?? '',
                        'price' => (float) $product->price,
                        'discount_price' => $product->discount_price ? (float) $product->discount_price : null,
                        'image' => $product->image ? $baseUrl . '/storage/product/' . $product->image : null,
                        'category' => $product->category ? $product->category->name : null,
                        'rating' => (float) ($product->rating ?? 0),
                        'unit' => $product->unit ?? 'pc',
                        'weight' => $product->weight ?? '',
                        'capacity' => $product->capacity ?? '',
                        'in_stock' => ($product->current_stock ?? 0) > 0,
                        'stock_quantity' => (int) ($product->current_stock ?? 0),
                    ];
                });
            
            $data['products'] = $products;
        }

        return $data;
    }
}