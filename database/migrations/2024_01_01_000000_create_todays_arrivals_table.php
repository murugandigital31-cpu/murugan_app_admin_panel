<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('todays_arrivals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('arrival_date');
            $table->string('branch_id')->nullable(); // JSON array of branch IDs
            $table->json('poster_images')->nullable(); // Array of poster image paths
            $table->json('product_ids')->nullable(); // Array of product IDs
            $table->boolean('is_active')->default(true);
            $table->boolean('show_in_app')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['arrival_date', 'is_active']);
            $table->index(['branch_id', 'arrival_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todays_arrivals');
    }
};