<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TodaysArrivalController extends Controller
{
    /**
     * Helper method to format branch IDs
     */
    private function formatBranchIds($branchId): array
    {
        $branchIds = [];
        if ($branchId && is_array($branchId)) {
            $branchIds = array_map('intval', array_filter($branchId));
        }
        // If no branches specified, default to branch 1
        if (empty($branchIds)) {
            $branchIds = [1];
        }
        return $branchIds;
    }

    /**
     * Helper method to format arrival data
     */
    private function formatArrivalData($arrival): array
    {
        // Get the main image
        $mainImage = '';
        if ($arrival->poster_images && is_array($arrival->poster_images) && count($arrival->poster_images) > 0) {
            $mainImage = $this->formatImageUrl($arrival->poster_images[0]);
        }
        
        // Format poster images array - ensure it's always an array
        $posterImages = [];
        if ($arrival->poster_images) {
            if (is_array($arrival->poster_images)) {
                foreach ($arrival->poster_images as $image) {
                    if (!empty($image)) {
                        // Try multiple possible image URL formats
                        $imageUrl = $this->formatImageUrl($image);
                        $posterImages[] = $imageUrl;
                        
                        // Debug: Log the original and formatted URLs
                        \Log::info("Poster Image Debug - Original: " . $image . " | Formatted: " . $imageUrl);
                    }
                }
            } elseif (is_string($arrival->poster_images)) {
                // If it's a JSON string, decode it
                $imageArray = json_decode($arrival->poster_images, true);
                if (is_array($imageArray)) {
                    foreach ($imageArray as $image) {
                        if (!empty($image)) {
                            $imageUrl = $this->formatImageUrl($image);
                            $posterImages[] = $imageUrl;
                            
                            // Debug: Log the original and formatted URLs
                            \Log::info("Poster Image Debug - Original: " . $image . " | Formatted: " . $imageUrl);
                        }
                    }
                } else if (!empty($arrival->poster_images)) {
                    // Single image string
                    $imageUrl = $this->formatImageUrl($arrival->poster_images);
                    $posterImages[] = $imageUrl;
                    
                    // Debug: Log the original and formatted URLs
                    \Log::info("Poster Image Debug - Original: " . $arrival->poster_images . " | Formatted: " . $imageUrl);
                }
            }
        }
        
        // Get branch details
        $branches = [];
        $branchIds = $this->formatBranchIds($arrival->branch_id);
        
        if (!empty($branchIds)) {
            try {
                if (class_exists('App\Model\TodaysArrivalBranch')) {
                    $branchData = \App\Model\TodaysArrivalBranch::whereIn('id', $branchIds)
                        ->where('is_active', true)
                        ->get();
                    
                    foreach ($branchData as $branch) {
                        $branches[] = [
                            'id' => (int) $branch->id,
                            'name' => (string) ($branch->name ?? 'Unknown Branch'),
                            'phone' => (string) ($branch->whatsapp_number ?? ''),
                            'whatsapp_number' => (string) ($branch->whatsapp_number ?? ''),
                            'address' => (string) ($branch->address ?? ''),
                            'contact_person' => (string) ($branch->contact_person ?? ''),
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Fallback if branch loading fails
                foreach ($branchIds as $branchId) {
                    $branches[] = [
                        'id' => (int) $branchId,
                        'name' => 'Branch ' . $branchId,
                        'phone' => '',
                        'whatsapp_number' => '',
                        'address' => '',
                        'contact_person' => '',
                    ];
                }
            }
        }
        
        // Get product details
        $products = [];
        $productIds = $arrival->product_ids ?? [];
        
        if (!empty($productIds) && is_array($productIds)) {
            try {
                if (class_exists('App\Model\Product')) {
                    $productData = \App\Model\Product::whereIn('id', $productIds)
                        ->where('status', 1)
                        ->get();
                    
                    foreach ($productData as $product) {
                        $productImages = [];
                        
                        // Handle product images properly
                        if ($product->image) {
                            if (is_array($product->image)) {
                                foreach ($product->image as $img) {
                                    if (!empty($img)) {
                                        $productImages[] = asset('storage/app/public/product/' . $img);
                                    }
                                }
                            } elseif (is_string($product->image) && !empty($product->image)) {
                                // If it's a JSON string, decode it
                                $imageArray = json_decode($product->image, true);
                                if (is_array($imageArray)) {
                                    foreach ($imageArray as $img) {
                                        if (!empty($img)) {
                                            $productImages[] = asset('storage/app/public/product/' . $img);
                                        }
                                    }
                                } else {
                                    // Single image string
                                    $productImages[] = asset('storage/app/public/product/' . $product->image);
                                }
                            }
                        }
                        
                        $products[] = [
                            'id' => (int) $product->id,
                            'name' => (string) ($product->name ?? 'Unknown Product'),
                            'description' => (string) ($product->description ?? ''),
                            'price' => (float) ($product->price ?? 0),
                            'discount_price' => (float) ($product->discount_price ?? 0),
                            'image' => !empty($productImages) ? $productImages[0] : '',
                            'images' => $productImages, // Always return an array, never a string
                            'unit' => (string) ($product->unit ?? ''),
                            'capacity' => (float) ($product->capacity ?? 1),
                            'tax' => (float) ($product->tax ?? 0),
                            'discount' => (float) ($product->discount ?? 0),
                            'status' => (int) ($product->status ?? 1),
                            'created_at' => (string) ($product->created_at ?? ''),
                            'updated_at' => (string) ($product->updated_at ?? ''),
                            'attributes' => [],
                            'category_ids' => [],
                            'choice_options' => [],
                            'variations' => [],
                            'weight' => (float) ($product->weight ?? 0),
                            'discount_type' => (string) ($product->discount_type ?? 'amount'),
                            'tax_type' => (string) ($product->tax_type ?? 'percent'),
                            'total_stock' => (int) ($product->total_stock ?? 0),
                            'rating' => [],
                            'active_reviews' => [],
                            'maximum_order_quantity' => (int) ($product->maximum_order_quantity ?? 100),
                            'category_discount' => null,
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Fallback if product loading fails
                $products = [];
            }
        }
        
        return [
            'id' => (int) $arrival->id,
            'title' => (string) ($arrival->title ?? 'No Title'),
            'description' => (string) ($arrival->description ?? 'No Description'),
            'image' => $mainImage,
            'poster_images' => $posterImages, // Always an array of valid URLs
            'branches' => $branches, // Full branch details instead of just IDs
            'branch_id' => $branchIds, // Always an array of integers
            'products' => $products, // Add product details
            'arrival_date' => $arrival->arrival_date ? $arrival->arrival_date->format('Y-m-d') : null,
            'arrival_date_formatted' => $arrival->arrival_date ? $arrival->arrival_date->format('d/m/Y') : null,
            'is_active' => (bool) $arrival->is_active,
            'show_in_app' => (bool) $arrival->show_in_app,
            'product_ids' => is_array($arrival->product_ids) ? array_map('intval', $arrival->product_ids) : [], // Always array of integers
            'products_count' => count($products),
            'branches_count' => count($branches),
            'sort_order' => (int) ($arrival->sort_order ?? 0),
            'created_at' => $arrival->created_at ? $arrival->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $arrival->updated_at ? $arrival->updated_at->format('Y-m-d H:i:s') : null
        ];
    }
    /**
     * Main API endpoint for today's arrivals
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $arrivals = [];
            $date = $request->get('date');
            $branchId = $request->get('branch_id');

            // Debug logging
            \Log::info('TodaysArrival API Request', [
                'date' => $date,
                'branch_id' => $branchId,
                'request_all' => $request->all()
            ]);

            // Try to get data from database
            try {
                if (class_exists('App\Model\TodaysArrival')) {
                    $query = \App\Model\TodaysArrival::active()->appVisible();

                    // Filter by date if provided
                    if ($date) {
                        $query->forDate($date);
                    }

                    // Filter by branch if provided
                    if ($branchId) {
                        $query->forBranch($branchId);
                        \Log::info('Filtering by branch', ['branch_id' => $branchId]);
                    }

                    $data = $query->orderBy('sort_order', 'asc')
                        ->orderBy('arrival_date', 'desc')
                        ->get();

                    // Debug: Log results
                    \Log::info('TodaysArrival Query Results', [
                        'count' => $data->count(),
                        'arrivals' => $data->map(function($arrival) {
                            return [
                                'id' => $arrival->id,
                                'title' => $arrival->title,
                                'branch_id' => $arrival->branch_id,
                                'arrival_date' => $arrival->arrival_date
                            ];
                        })
                    ]);
                } elseif (class_exists('App\Models\TodaysArrival')) {
                    $query = \App\Models\TodaysArrival::where('is_active', true)
                        ->where('show_in_app', true);

                    // Filter by date if provided
                    if ($date) {
                        $query->whereDate('arrival_date', $date);
                    }

                    // Filter by branch if provided
                    if ($branchId) {
                        $query->whereJsonContains('branch_id', (int)$branchId);
                    }

                    $data = $query->orderBy('arrival_date', 'desc')
                        ->get();
                } else {
                    $data = collect();
                }

                foreach ($data as $arrival) {
                    $arrivals[] = $this->formatArrivalData($arrival);
                }
            } catch (\Exception $dbError) {
                $arrivals = [];
                \Log::error('TodaysArrival index error: ' . $dbError->getMessage());
            }

            $response = [
                'arrivals' => $arrivals,
                'status' => true,
                'message' => 'Today\'s arrivals retrieved successfully',
                'count' => count($arrivals),
                'filters' => [
                    'date' => $date,
                    'branch_id' => $branchId
                ]
            ];

            return response()->json($response, 200, [
                'Content-Type' => 'application/json; charset=UTF-8',
                'Cache-Control' => 'no-cache, no-store, must-revalidate'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving arrivals',
                'arrivals' => [],
                'error' => $e->getMessage()
            ], 500, [
                'Content-Type' => 'application/json; charset=UTF-8'
            ]);
        }
    }

    /**
     * Get single arrival by ID
     */
    public function show($id): JsonResponse
    {
        try {
            $arrival = null;
            
            if (class_exists('App\Model\TodaysArrival')) {
                try {
                    $arrival = \App\Model\TodaysArrival::where('id', $id)
                        ->active()
                        ->appVisible()
                        ->first();
                } catch (\Exception $e) {
                    if (class_exists('App\Models\TodaysArrival')) {
                        $arrival = \App\Models\TodaysArrival::where('id', $id)
                            ->where('is_active', true)
                            ->where('show_in_app', true)
                            ->first();
                    }
                }
            } elseif (class_exists('App\Models\TodaysArrival')) {
                $arrival = \App\Models\TodaysArrival::where('id', $id)
                    ->where('is_active', true)
                    ->where('show_in_app', true)
                    ->first();
            }

            if (!$arrival) {
                return response()->json([
                    'status' => false,
                    'message' => 'Arrival not found',
                    'arrival' => null
                ], 404);
            }

            $formattedArrival = $this->formatArrivalData($arrival);
            // Add WhatsApp message for single arrival view
            $formattedArrival['whatsapp_message'] = $arrival->formatted_whatsapp_message ?? '';

            return response()->json([
                'arrival' => $formattedArrival,
                'status' => true,
                'message' => 'Arrival retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving arrival: ' . $e->getMessage(),
                'arrival' => null
            ], 500);
        }
    }

    /**
     * Get arrivals by date range
     */
    public function getByDateRange(Request $request): JsonResponse
    {
        try {
            $arrivals = [];
            $date = $request->get('date', today()->format('Y-m-d'));
            $branchId = $request->get('branch_id');
            
            if (class_exists('App\Model\TodaysArrival')) {
                $query = \App\Model\TodaysArrival::active()->appVisible();
                
                // Filter by date if provided
                if ($date) {
                    $query->forDate($date);
                }
                
                // Filter by branch if provided
                if ($branchId) {
                    $query->forBranch($branchId);
                }
                
                $data = $query->orderBy('sort_order', 'asc')
                    ->orderBy('arrival_date', 'desc')
                    ->get();
                
                foreach ($data as $arrival) {
                    $arrivals[] = $this->formatArrivalData($arrival);
                }
            }

            return response()->json([
                'arrivals' => $arrivals,
                'status' => true,
                'message' => 'Date range arrivals retrieved successfully',
                'filters' => [
                    'date' => $date,
                    'branch_id' => $branchId
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving date range arrivals: ' . $e->getMessage(),
                'arrivals' => []
            ], 500);
        }
    }

    /**
     * Get Today's Arrival branches (from todays_arrival_branches table)
     */
    public function branches(): JsonResponse
    {
        try {
            $branches = [];

            // Get Today's Arrival specific branches
            if (class_exists('App\Model\TodaysArrivalBranch')) {
                $data = \App\Model\TodaysArrivalBranch::where('is_active', true)
                    ->orderBy('name', 'asc')
                    ->get();

                foreach ($data as $branch) {
                    $branches[] = [
                        'id' => (int) $branch->id,
                        'name' => (string) ($branch->name ?? 'Unknown Branch'),
                        'phone' => (string) ($branch->whatsapp_number ?? ''),
                        'whatsapp_number' => (string) ($branch->whatsapp_number ?? ''),
                        'address' => (string) ($branch->address ?? ''),
                        'contact_person' => (string) ($branch->contact_person ?? ''),
                        'location' => (string) ($branch->location ?? ''),
                    ];
                }
            }

            // Fallback if no branches found
            if (empty($branches)) {
                $branches = [
                    [
                        'id' => 1,
                        'name' => 'Main Branch',
                        'phone' => '',
                        'whatsapp_number' => '',
                        'address' => 'Main Address',
                        'contact_person' => '',
                        'location' => ''
                    ]
                ];
            }

            return response()->json([
                'branches' => $branches,
                'status' => true,
                'message' => 'Today\'s Arrival branches retrieved successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving branches: ' . $e->getMessage(),
                'branches' => []
            ], 500);
        }
    }

    /**
     * WhatsApp checkout method
     */
    public function whatsappCheckout($id, Request $request): JsonResponse
    {
        try {
            return response()->json([
                'status' => true,
                'message' => 'WhatsApp checkout processed',
                'whatsapp_url' => 'https://wa.me/1234567890'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error processing WhatsApp checkout'
            ], 500);
        }
    }

    /**
     * Minimal test endpoint
     */
    public function minimal(): JsonResponse
    {
        try {
            $response = [
                'arrivals' => [
                    [
                        'id' => 1,
                        'title' => 'Test Arrival 1',
                        'description' => 'This is a test arrival',
                        'image' => '',
                        'branch_id' => 1,
                        'created_at' => '2024-01-01 10:00:00',
                        'updated_at' => '2024-01-01 10:00:00'
                    ]
                ],
                'status' => true,
                'message' => 'Minimal test data retrieved successfully'
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error in minimal endpoint',
                'arrivals' => []
            ], 500);
        }
    }

    /**
     * Debug endpoint
     */
    public function debug(): JsonResponse
    {
        try {
            $debug = [
                'php_version' => PHP_VERSION,
                'model_exists' => class_exists('App\Model\TodaysArrival'),
                'models_exists' => class_exists('App\Models\TodaysArrival')
            ];

            return response()->json([
                'status' => true,
                'debug_info' => $debug,
                'message' => 'Debug information retrieved'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Debug endpoint error',
                'debug_info' => []
            ], 500);
        }
    }

    /**
     * Format image URL for consistent output
     */
    private function formatImageUrl($image): string
    {
        if (empty($image)) {
            return '';
        }

        // If already a full URL, return as is
        if (filter_var($image, FILTER_VALIDATE_URL)) {
            return $image;
        }

        // Clean up the image path
        $image = ltrim($image, '/');
        
        // Remove all possible path prefixes to get just the filename
        $pathsToRemove = [
            'uploads/arrivals/',
            'storage/arrivals/',
            'storage/app/public/arrivals/',
            'public/uploads/arrivals/',
            'public/storage/arrivals/',
            'arrivals/',
            'storage/',
            'uploads/'
        ];

        foreach ($pathsToRemove as $path) {
            if (strpos($image, $path) === 0) {
                $image = substr($image, strlen($path));
                break;
            }
        }

        // Always use uploads/arrivals/ path as confirmed from server
        return url('uploads/arrivals/' . $image);
    }
}