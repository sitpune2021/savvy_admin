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
        Schema::create('raw_stock_for_plants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plant_id');
            $table->foreign('plant_id')->references('id')->on('plants')->onDelete('cascade');
            $table->unsignedBigInteger('raw_material_variants_id');
            $table->foreign('raw_material_variants_id')->references('id')->on('raw_material_variants')->onDelete('cascade');
            $table->unsignedInteger('total_quantity')->default(0);
            $table->softDeletes(); // For soft deleting distributions
            $table->timestamps();
        });

        DB::insert("
            INSERT INTO raw_stock_for_plants (plant_id, raw_material_variants_id, total_quantity, created_at, updated_at)
            SELECT 
                p.id AS plant_id,
                r.id AS raw_material_variants_id,
                0 AS total_quantity,
                NOW() AS created_at,
                NOW() AS updated_at
            FROM plants p
            CROSS JOIN raw_material_variants r
            LEFT JOIN raw_stock_for_plants rsp 
                ON rsp.plant_id = p.id AND rsp.raw_material_variants_id = r.id
            WHERE rsp.id IS NULL;
        ");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raw_stock_for_plants');
    }
};
