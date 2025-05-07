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
        Schema::create('machine_dispensaries', function (Blueprint $table) {
            $table->id();
            $table->string('model_name');
            $table->string('serial_number');
            $table->enum('machine_type',['2_tab', '3_tab']);
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('shipping_id')->nullable();
            $table->string('documents')->nullable();
            $table->string('warranty');
            $table->string('garanty');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('shipping_id')->references('id')->on('shipping_addresses')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_dispensaries');
    }
};
