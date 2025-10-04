<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixTodaysArrivalsTableColumns extends Migration
{
    public function up()
    {
        // First, let's check what columns already exist
        $columns = Schema::getColumnListing('todays_arrivals');
        
        Schema::table('todays_arrivals', function (Blueprint $table) use ($columns) {
            // Only add columns that don't exist
            if (!in_array('arrival_branch_id', $columns)) {
                $table->unsignedBigInteger('arrival_branch_id')->nullable()->after('arrival_date');
            }
            
            if (!in_array('main_poster', $columns)) {
                $table->string('main_poster')->nullable()->after('poster_images');
            }
            
            if (!in_array('whatsapp_message_template', $columns)) {
                $table->text('whatsapp_message_template')->nullable()->after('product_ids');
            }
            
            if (!in_array('whatsapp_enabled', $columns)) {
                $table->boolean('whatsapp_enabled')->default(true)->after('whatsapp_message_template');
            }
        });

        // Handle the branch_id to arrival_branch_id migration separately
        if (in_array('branch_id', $columns) && !in_array('arrival_branch_id', $columns)) {
            Schema::table('todays_arrivals', function (Blueprint $table) {
                $table->dropColumn('branch_id');
                $table->unsignedBigInteger('arrival_branch_id')->after('arrival_date');
            });
        }

        // Add foreign key constraint if the column exists and constraint doesn't exist
        if (in_array('arrival_branch_id', Schema::getColumnListing('todays_arrivals'))) {
            try {
                Schema::table('todays_arrivals', function (Blueprint $table) {
                    $table->foreign('arrival_branch_id')->references('id')->on('todays_arrival_branches')->onDelete('cascade');
                });
            } catch (\Exception $e) {
                // Foreign key might already exist, that's okay
            }
        }
    }

    public function down()
    {
        Schema::table('todays_arrivals', function (Blueprint $table) {
            // Drop foreign key if it exists
            try {
                $table->dropForeign(['arrival_branch_id']);
            } catch (\Exception $e) {
                // Ignore if doesn't exist
            }
            
            // Get current columns
            $columns = Schema::getColumnListing('todays_arrivals');
            
            // Drop columns that we added
            $columnsToRemove = [
                'arrival_branch_id',
                'main_poster',
                'whatsapp_message_template',
                'whatsapp_enabled'
            ];
            
            foreach ($columnsToRemove as $column) {
                if (in_array($column, $columns)) {
                    $table->dropColumn($column);
                }
            }
            
            // Add back branch_id if it doesn't exist
            if (!in_array('branch_id', $columns)) {
                $table->json('branch_id')->nullable();
            }
        });
    }
}