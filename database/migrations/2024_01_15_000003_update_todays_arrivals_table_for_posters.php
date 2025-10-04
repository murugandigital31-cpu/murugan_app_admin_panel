<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTodaysArrivalsTableForPosters extends Migration
{
    public function up()
    {
        Schema::table('todays_arrivals', function (Blueprint $table) {
            // Remove old branch_id JSON column and add reference to new branches table
            $table->dropColumn('branch_id');
            $table->unsignedBigInteger('arrival_branch_id')->after('arrival_date');
            
            // Add poster images support
            $table->json('poster_images')->nullable()->after('description');
            $table->string('main_poster')->nullable()->after('poster_images');
            
            // Add WhatsApp specific fields
            $table->text('whatsapp_message_template')->nullable()->after('product_ids');
            $table->boolean('whatsapp_enabled')->default(true)->after('whatsapp_message_template');
            
            // Add foreign key constraint
            $table->foreign('arrival_branch_id')->references('id')->on('todays_arrival_branches')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('todays_arrivals', function (Blueprint $table) {
            $table->dropForeign(['arrival_branch_id']);
            $table->dropColumn([
                'arrival_branch_id',
                'poster_images', 
                'main_poster',
                'whatsapp_message_template',
                'whatsapp_enabled'
            ]);
            $table->json('branch_id')->nullable();
        });
    }
}