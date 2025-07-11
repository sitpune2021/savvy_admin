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
        Schema::create('raw_distributions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('raw_stock_transactions_id');
            $table->unsignedBigInteger('plant_id');
            $table->decimal('quantity', 12, 2);
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->foreign('raw_stock_transactions_id')->references('id')->on('raw_stock_transactions')->onDelete('cascade');
            $table->foreign('plant_id')->references('id')->on('plants')->onDelete('cascade');
            $table->timestamp('accepted_at')->nullable();
            $table->softDeletes(); // For soft deleting distributions
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raw_distributions');
    }
};
