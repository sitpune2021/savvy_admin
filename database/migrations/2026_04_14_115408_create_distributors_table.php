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
        Schema::create('distributors', function (Blueprint $table) {
            $table->id();
            $table->string('zoho_id')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable()->default(Hash::make('Distributor@123'));
            $table->string('phone_no');

            $table->string('full_address');
            $table->string('country');
            $table->string('state');
            $table->string('city');
            $table->string('pincode');
            $table->string('po_no')->nullable();

            $table->string('license_no')->nullable();
            $table->string('tempo_no')->nullable();
            $table->string('tempo_name')->nullable();
            
            $table->string('pan_card')->nullable();
            $table->string('aadhar_card')->nullable();

            $table->string('pan_card_FILE')->nullable();
            $table->string('aadhar_card_FILE')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributors');
    }
};
