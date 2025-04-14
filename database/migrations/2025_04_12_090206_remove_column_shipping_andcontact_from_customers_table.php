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
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('shipping_address')->nullable();
            $table->dropColumn('shipping_country')->nullable();
            $table->dropColumn('shipping_state')->nullable();
            $table->dropColumn('shipping_city')->nullable();
            $table->dropColumn('shipping_pincode')->nullable();

            $table->dropColumn('contact_person')->nullable();
            $table->dropColumn('contact_person_phone')->nullable();

            $table->dropColumn('machine_deployed')->default('No');
            $table->dropColumn('machine_deployed_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('shipping_address')->nullable();
            $table->string('shipping_country')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_pincode')->nullable();

            $table->string('contact_person')->nullable();
            $table->string('contact_person_phone')->nullable();

            $table->string('machine_deployed')->default('No');
            $table->date('machine_deployed_date')->nullable();
        });
    }
};
