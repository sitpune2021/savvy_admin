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
            $table->date('date')->nullable()->after('duration_type');
            $table->enum('frequency', ['daily', 'alternate_day', 'weekly', 'monthly'])->nullable()->default('monthly')->change();
            $table->unsignedBigInteger('send_by')->nullable()->after('status');
            $table->foreign('send_by')->references('id')->on('shipping_contacts')->onDelete('cascade');
            $table->enum('accepted_status', ['pending', 'accepted', 'rejected'])->nullable()->after('send_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('date');
            $table->enum('frequency', ['daily', 'alternate_day', 'weekly', 'monthly'])->default('monthly')->change();
            $table->dropForeign(['send_by']);
            $table->dropColumn('accepted_status');
        });
    }
};
