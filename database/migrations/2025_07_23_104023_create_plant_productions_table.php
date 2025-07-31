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
        Schema::create('plant_productions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plant_id');
            $table->string('quantity');
            $table->date('production_date');
            $table->foreign('plant_id')->references('id')->on('plants')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plant_productions');
    }
};
