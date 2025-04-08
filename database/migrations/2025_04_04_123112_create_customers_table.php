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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_zohi_id')->unique();
            $table->unsignedBigInteger('plant_id')->nullable();

            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('phone_no')->nullable();

            $table->string('billing_address');
            $table->string('billing_country');
            $table->string('billing_state')->nullable();
            $table->string('billing_city');
            $table->string('billing_pincode');

            $table->string('shipping_address')->nullable();
            $table->string('shipping_country')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_pincode')->nullable();

            $table->string('contact_person')->nullable();
            $table->string('contact_person_phone')->nullable();

            $table->string('machine_deployed')->default('No');
            $table->date('machine_deployed_date')->nullable();

            $table->foreign('plant_id')->references('id')->on('plants')->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
