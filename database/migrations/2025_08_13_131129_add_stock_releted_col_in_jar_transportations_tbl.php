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
        Schema::table('jar_transportations', function (Blueprint $table) {
            $table->bigInteger('total_quantity')->nullable();
            $table->bigInteger('allocated_quantity')->nullable();
            $table->bigInteger('allocat_quantity')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jar_transportations', function (Blueprint $table) {
            $table->dropColumn('total_quantity');
            $table->dropColumn('allocated_quantity');
            $table->dropColumn('allocat_quantity');
        });
    }
};
