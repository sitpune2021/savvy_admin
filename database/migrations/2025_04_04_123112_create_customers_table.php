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
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('phone_no')->nullable();

            $table->string('billing_address');
            $table->string('billing_country');
            $table->string('billing_state')->nullable();
            $table->string('billing_city');
            $table->string('billing_pincode');

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
