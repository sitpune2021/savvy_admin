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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->enum('type',['additional', 'contracts'])->default('contracts');

            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('product_id');
            
            $table->integer('quantity');
            $table->integer('price');
            $table->enum('delivery_frequency', ['daily', 'alternate_day', 'custom'])->default('daily');
            $table->time('delivery_time')->nullable();
            
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->integer('duration')->nullable();
            $table->enum('duration_type', ['days', 'weeks','months', 'years'])->default('months');

            $table->enum('status', ['expired', 'active', 'cancelled','paused'])->default('active');


            $table->softDeletes(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
