<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First check if the table exists and has data
        if (Schema::hasTable('todays_arrival_branches')) {
            $count = DB::table('todays_arrival_branches')->count();
            
            // Only add sample data if table is empty
            if ($count === 0) {
                DB::table('todays_arrival_branches')->insert([
                    [
                        'name' => 'Main Branch - Dubai',
                        'location' => 'Dubai',
                        'whatsapp_number' => '+971501234567',
                        'contact_person' => 'Ahmed',
                        'address' => 'Dubai Mall, Dubai, UAE',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'name' => 'Sharjah Branch',
                        'location' => 'Sharjah',
                        'whatsapp_number' => '+971507654321',
                        'contact_person' => 'Mohammed',
                        'address' => 'Mega Mall, Sharjah, UAE',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'name' => 'Abu Dhabi Branch',
                        'location' => 'Abu Dhabi',
                        'whatsapp_number' => '+971509876543',
                        'contact_person' => 'Hassan',
                        'address' => 'Marina Mall, Abu Dhabi, UAE',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]);
                
                echo "Added 3 sample branches to todays_arrival_branches table.\n";
            } else {
                echo "todays_arrival_branches table already has {$count} records.\n";
            }
        } else {
            echo "Warning: todays_arrival_branches table does not exist.\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the sample data
        if (Schema::hasTable('todays_arrival_branches')) {
            DB::table('todays_arrival_branches')->whereIn('whatsapp_number', [
                '+971501234567',
                '+971507654321', 
                '+971509876543'
            ])->delete();
        }
    }
};