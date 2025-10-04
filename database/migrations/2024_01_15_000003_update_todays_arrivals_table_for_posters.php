<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTodaysArrivalsTableForPosters extends Migration
{
    public function up()
    {
        Schema::table('todays_arrivals', function (Blueprint $table) {
            // Check if columns exist before modifying
            if (Schema::hasColumn('todays_arrivals', 'branch_id')) {
                $table->dropColumn('branch_id');
            }
            
            if (!Schema::hasColumn('todays_arrivals', 'arrival_branch_id')) {
                $table->unsignedBigInteger('arrival_branch_id')->after('arrival_date');
            }
            
            // Add poster images support only if they don't exist
            if (!Schema::hasColumn('todays_arrivals', 'poster_images')) {
                $table->json('poster_images')->nullable()->after('description');
            }
            
            if (!Schema::hasColumn('todays_arrivals', 'main_poster')) {
                $table->string('main_poster')->nullable()->after('poster_images');
            }
            
            // Add WhatsApp specific fields only if they don't exist
            if (!Schema::hasColumn('todays_arrivals', 'whatsapp_message_template')) {
                $table->text('whatsapp_message_template')->nullable()->after('product_ids');
            }
            
            if (!Schema::hasColumn('todays_arrivals', 'whatsapp_enabled')) {
                $table->boolean('whatsapp_enabled')->default(true)->after('whatsapp_message_template');
            }
        });

        // Add foreign key constraint separately to avoid conflicts
        if (Schema::hasColumn('todays_arrivals', 'arrival_branch_id')) {
            Schema::table('todays_arrivals', function (Blueprint $table) {
                try {
                    $table->foreign('arrival_branch_id')->references('id')->on('todays_arrival_branches')->onDelete('cascade');
                } catch (\Exception $e) {
                    // Foreign key might already exist, ignore the error
                }
            });
        }
    }

    public function down()
    {
        Schema::table('todays_arrivals', function (Blueprint $table) {
            // Drop foreign key if it exists
            try {
                $table->dropForeign(['arrival_branch_id']);
            } catch (\Exception $e) {
                // Foreign key might not exist, ignore the error
            }
            
            // Drop columns only if they exist
            $columnsToCheck = [
                'arrival_branch_id',
                'poster_images', 
                'main_poster',
                'whatsapp_message_template',
                'whatsapp_enabled'
            ];
            
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('todays_arrivals', $column)) {
                    $table->dropColumn($column);
                }
            }
            
            // Add back branch_id column if it doesn't exist
            if (!Schema::hasColumn('todays_arrivals', 'branch_id')) {
                $table->json('branch_id')->nullable();
            }
        });
    }
}