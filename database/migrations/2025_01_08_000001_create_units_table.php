<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('unit_name', 50);
            $table->string('unit_short_name', 20)->unique();
            $table->enum('unit_type', ['weight', 'volume', 'length', 'piece', 'other'])->default('other');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Insert default units
        DB::table('units')->insert([
            [
                'unit_name' => 'Kilogram',
                'unit_short_name' => 'kg',
                'unit_type' => 'weight',
                'is_active' => 1,
                'is_default' => 1,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'unit_name' => 'Gram',
                'unit_short_name' => 'gm',
                'unit_type' => 'weight',
                'is_active' => 1,
                'is_default' => 1,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'unit_name' => 'Liter',
                'unit_short_name' => 'ltr',
                'unit_type' => 'volume',
                'is_active' => 1,
                'is_default' => 1,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'unit_name' => 'Milliliter',
                'unit_short_name' => 'ml',
                'unit_type' => 'volume',
                'is_active' => 1,
                'is_default' => 1,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'unit_name' => 'Piece',
                'unit_short_name' => 'pc',
                'unit_type' => 'piece',
                'is_active' => 1,
                'is_default' => 1,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('units');
    }
}

