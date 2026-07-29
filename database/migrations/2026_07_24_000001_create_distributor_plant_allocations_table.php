<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributor_plant_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_id')->constrained('distributors')->cascadeOnDelete();
            $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['distributor_id', 'plant_id'], 'distributor_plant_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_plant_allocations');
    }
};
