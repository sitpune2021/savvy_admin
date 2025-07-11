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
        Schema::create('raw_stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('raw_material_variant_id');
            $table->enum('type', ['purchase']); 
            $table->unsignedInteger('quantity'); 
            $table->foreign('raw_material_variant_id')->references('id')->on('raw_material_variants')->onDelete('cascade');
            $table->softDeletes(); // For soft deleting transactions
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raw_stock_transactions');
    }
};
