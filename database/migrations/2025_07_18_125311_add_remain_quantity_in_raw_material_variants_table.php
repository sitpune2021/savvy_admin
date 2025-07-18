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
        Schema::table('raw_material_variants', function (Blueprint $table) {
           $table->unsignedInteger('remain_quantity')->default(0)->after('total_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('raw_material_variants', function (Blueprint $table) {
            $table->dropColumn('remain_quantity');
        });
    }
};
