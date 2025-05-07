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
        Schema::table('contracts', function (Blueprint $table) {
            DB::statement("ALTER TABLE contracts MODIFY COLUMN status ENUM('expired', 'active', 'cancelled','paused','in-progress') DEFAULT 'active'");

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('status');
            DB::statement("ALTER TABLE contracts MODIFY COLUMN status ENUM('expired', 'active', 'cancelled','paused') DEFAULT 'active'");
        });
    }
};
