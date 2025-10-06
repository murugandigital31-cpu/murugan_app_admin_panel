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
            // Get all active Today's Arrival branches that have arrivals
            $branchIds = TodaysArrival::active()
                ->appVisible()
                ->whereNotNull('branch_id')
                ->pluck('branch_id')
                ->flatten()
                ->unique()
                ->filter()
                ->values();

            $branches = TodaysArrivalBranch::whereIn('id', $branchIds)
                ->where('is_active', true)
                ->get()
                ->map(function ($branch) {
                    return [
                        'id' => $branch->id,
                        'name' => $branch->name,
                        'phone' => $branch->contact_person ?? '',
                        'whatsapp_number' => $branch->whatsapp_number,
                        'address' => $branch->address,
                        'latitude' => null,
                        'longitude' => null,
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
                $branch = TodaysArrivalBranch::find($branchId);
            } else if ($arrival->branch_id && count($arrival->branch_id) > 0) {
                // Use first branch if no specific branch requested
                $branch = TodaysArrivalBranch::find($arrival->branch_id[0]);
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
            // Handle JSON string format
            $branchIds = $arrival->branch_id;
            if (is_string($branchIds)) {
                $branchIds = json_decode($branchIds, true);
            }
            
            if (is_array($branchIds) && count($branchIds) > 0) {
                // Filter out null/empty values
                $branchIds = array_filter($branchIds, function($id) {
                    return $id !== null && $id !== '' && $id !== 0;
                });
                
                if (!empty($branchIds)) {
                    $branches = TodaysArrivalBranch::whereIn('id', $branchIds)
                        ->get()
                        ->map(function($branch) {
                            return [
                                'id' => (int) $branch->id,
                                'name' => $branch->name ?? '',
                                'phone' => $branch->contact_person ?? '',
                                'whatsapp_number' => $branch->whatsapp_number ?? '',
                                'address' => $branch->address ?? '',
                                'latitude' => null, // TodaysArrivalBranch doesn't have lat/lng
                                'longitude' => null,
                            ];
                        })
                        ->toArray();
                }
            }
        }
        
        // Ensure we always have an array, never null
        $branches = $branches ?: [];
        
        $data = [
            'id' => (int) $arrival->id,
            'title' => $arrival->title ?? '',
            'description' => $arrival->description ?? '',
            'arrival_date' => $arrival->arrival_date->format('Y-m-d'),
            'arrival_date_formatted' => $arrival->arrival_date->format('M d, Y'),
            'poster_images' => $this->formatPosterImages($arrival->poster_images, $baseUrl),
            'main_poster' => $this->formatMainPoster($arrival->main_poster, $baseUrl),
            'products_count' => count($arrival->product_ids ?? []),
            'branches_count' => count($branches),
            'branches' => $branches,
            'whatsapp_message' => $arrival->formatted_whatsapp_message ?? '',
            'sort_order' => (int) ($arrival->sort_order ?? 0),
            'created_at' => $arrival->created_at->toISOString(),
            'updated_at' => $arrival->updated_at->toISOString(),
        ];

        // Debug info (remove in production)
        if (config('app.debug')) {
            $data['debug'] = [
                'branch_id_raw' => $arrival->branch_id,
                'branch_id_type' => gettype($arrival->branch_id),
                'product_ids_raw' => $arrival->product_ids,
                'product_ids_type' => gettype($arrival->product_ids),
                'poster_images_raw' => $arrival->poster_images,
                'main_poster_raw' => $arrival->main_poster,
            ];
        }

        if ($includeProducts && $arrival->product_ids) {
            // Handle JSON string format for product_ids
            $productIds = $arrival->product_ids;
            if (is_string($productIds)) {
                $productIds = json_decode($productIds, true);
            }
            
            if (is_array($productIds) && !empty($productIds)) {
                // Filter out null/empty values
                $productIds = array_filter($productIds, function($id) {
                    return $id !== null && $id !== '' && $id !== 0;
                });
                
                if (!empty($productIds)) {
                    $products = Product::whereIn('id', $productIds)
                        ->get()
                ->map(function($product) use ($baseUrl) {
                    // Handle image as array (Flutter expects List<String>)
                    $imageUrls = [];
                    if ($product->image) {
                        $imageData = $product->image;
                        
                        // If it's a JSON string, decode it
                        if (is_string($imageData) && (strpos($imageData, '[') === 0 || strpos($imageData, '{') === 0)) {
                            $imageData = json_decode($imageData, true);
                        }
                        
                        // If it's an array, process all images
                        if (is_array($imageData)) {
                            foreach ($imageData as $imageName) {
                                if ($imageName) {
                                    if (filter_var($imageName, FILTER_VALIDATE_URL)) {
                                        $imageUrls[] = $imageName;
                                    } else {
                                        $imageUrls[] = $baseUrl . '/storage/product/' . $imageName;
                                    }
                                }
                            }
                        } else if (is_string($imageData) && $imageData) {
                            // Single image as string
                            if (filter_var($imageData, FILTER_VALIDATE_URL)) {
                                $imageUrls[] = $imageData;
                            } else {
                                $imageUrls[] = $baseUrl . '/storage/product/' . $imageData;
                            }
                        }
                    }
                    
                    return [
                        'id' => (int) $product->id,
                        'name' => $product->name ?? '',
                        'description' => $product->description ?? '',
                        'price' => (float) ($product->price ?? 0),
                        'discount_price' => $product->discount_price ? (float) $product->discount_price : null,
                        'image' => $imageUrls, // Return as array for Flutter
                        'unit' => $product->unit ?? 'pc',
                        'tax' => (float) ($product->tax ?? 0),
                        'available_time_starts' => $product->available_time_starts ?? null,
                        'available_time_ends' => $product->available_time_ends ?? null,
                        'status' => (int) ($product->status ?? 1),
                        'created_at' => $product->created_at ? $product->created_at->toISOString() : null,
                    ];
                });
            
                $data['products'] = $products->toArray();
                } else {
                    $data['products'] = [];
                }
            } else {
                $data['products'] = [];
            }
        }

        return $data;
    }

    /**
     * Format main poster image URL
     */
    private function formatMainPoster($mainPoster, $baseUrl)
    {
        if (!$mainPoster) {
            return null;
        }

        // Handle both single filename and array format
        if (is_string($mainPoster) && (strpos($mainPoster, '[') === 0 || strpos($mainPoster, '{') === 0)) {
            $decoded = json_decode($mainPoster, true);
            if (is_array($decoded) && count($decoded) > 0) {
                $mainPoster = $decoded[0];
            }
        } else if (is_array($mainPoster) && count($mainPoster) > 0) {
            $mainPoster = $mainPoster[0];
        }

        if (filter_var($mainPoster, FILTER_VALIDATE_URL)) {
            return $mainPoster;
        }

        return $baseUrl . '/storage/arrivals/' . $mainPoster;
    }

    /**
     * Format poster images array
     */
    private function formatPosterImages($posterImages, $baseUrl)
    {
        if (!$posterImages) {
            return [];
        }

        // Handle JSON string format
        if (is_string($posterImages)) {
            $posterImages = json_decode($posterImages, true);
        }

        if (!is_array($posterImages) || empty($posterImages)) {
            return [];
        }

        return array_map(function($image) use ($baseUrl) {
            if (!$image) return null;
            
            if (filter_var($image, FILTER_VALIDATE_URL)) {
                return $image;
            }
            return $baseUrl . '/storage/arrivals/' . $image;
        }, array_filter($posterImages)); // Remove null/empty values
    }
}