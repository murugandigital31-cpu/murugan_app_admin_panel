<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TodaysArrivalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // First, let's check if we have branches
        $branches = DB::table('branches')->where('status', 1)->pluck('id')->toArray();
        
        if (empty($branches)) {
            // Create a sample branch if none exists
            $branchId = DB::table('branches')->insertGetId([
                'name' => 'Main Branch',
                'email' => 'main@muruganflowers.com',
                'phone' => '+971501234567',
                'whatsapp_number' => '+971501234567',
                'address' => 'Dubai, UAE',
                'latitude' => '25.2048',
                'longitude' => '55.2708',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $branches = [$branchId];
        }

        // Get some sample products
        $products = DB::table('products')->where('status', 1)->limit(5)->pluck('id')->toArray();
        
        if (empty($products)) {
            // Create sample products if none exist
            $productIds = [];
            for ($i = 1; $i <= 3; $i++) {
                $productId = DB::table('products')->insertGetId([
                    'name' => "Fresh Flower Bouquet {$i}",
                    'description' => "Beautiful fresh flowers arrangement for today's arrival",
                    'price' => 50 + ($i * 10),
                    'unit' => 'piece',
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $productIds[] = $productId;
            }
            $products = $productIds;
        }

        // Create sample today's arrivals
        $todaysArrivals = [
            [
                'title' => 'Fresh Spring Flowers Collection',
                'description' => 'Beautiful collection of fresh spring flowers just arrived today. Perfect for any occasion!',
                'arrival_date' => Carbon::today(),
                'branch_id' => json_encode(array_slice($branches, 0, 2)), // First 2 branches
                'poster_images' => json_encode([
                    'arrivals/spring_collection_1.jpg',
                    'arrivals/spring_collection_2.jpg'
                ]),
                'product_ids' => json_encode(array_slice($products, 0, 3)), // First 3 products
                'is_active' => true,
                'show_in_app' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Premium Roses Arrival',
                'description' => 'Premium quality roses imported from the best gardens. Available in multiple colors.',
                'arrival_date' => Carbon::today(),
                'branch_id' => json_encode($branches), // All branches
                'poster_images' => json_encode([
                    'arrivals/premium_roses_1.jpg'
                ]),
                'product_ids' => json_encode(array_slice($products, 1, 2)), // 2 products
                'is_active' => true,
                'show_in_app' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Wedding Special Arrangements',
                'description' => 'Special wedding flower arrangements perfect for your big day. Limited stock available.',
                'arrival_date' => Carbon::today(),
                'branch_id' => json_encode([$branches[0]]), // Only first branch
                'poster_images' => json_encode([
                    'arrivals/wedding_special_1.jpg',
                    'arrivals/wedding_special_2.jpg',
                    'arrivals/wedding_special_3.jpg'
                ]),
                'product_ids' => json_encode($products), // All products
                'is_active' => true,
                'show_in_app' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        // Insert the test data
        foreach ($todaysArrivals as $arrival) {
            DB::table('todays_arrivals')->insert($arrival);
        }

        $this->command->info('Today\'s Arrival test data created successfully!');
        $this->command->info('Created ' . count($todaysArrivals) . ' sample arrivals');
        $this->command->info('Using ' . count($branches) . ' branches and ' . count($products) . ' products');
    }
}