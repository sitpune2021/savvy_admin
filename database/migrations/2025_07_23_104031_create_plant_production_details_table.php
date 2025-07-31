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
        Schema::create('plant_production_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plant_production_id');
            $table->unsignedBigInteger('raw_material_id');
            $table->string('quantity');
            $table->foreign('plant_production_id')->references('id')->on('plant_productions')->onDelete('cascade');
            $table->foreign('raw_material_id')->references('id')->on('raw_materials')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plant_production_details');
    }
};
