<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shipping_addresses', function (Blueprint $table) {
            $table->dropColumn('contact_person');
            $table->dropColumn('contact_person_phone');
            $table->dropColumn('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_addresses', function (Blueprint $table) {
            $table->string('contact_person')->nullable();
            $table->string('contact_person_phone')->nullable();
            $table->string('password')->nullable()->default(Hash::make('Savvy@123'));
        });
    }
};
