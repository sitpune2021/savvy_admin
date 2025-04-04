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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone_no');

            $table->string('full_address');
            $table->string('country');
            $table->string('state');
            $table->string('city');
            $table->string('pincode');

            $table->string('license_no')->nullable();
            $table->string('vehicle_no')->nullable();
            $table->string('vehicle_name')->nullable();
            
            $table->string('pan_card')->nullable();
            $table->string('aadhar_card')->nullable();

            $table->string('pan_card_FILE')->nullable();
            $table->string('aadhar_card_FILE')->nullable();
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
