<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->unsignedBigInteger('shipping_addresses_id')->nullable()->after('send_by');
            $table->foreign('shipping_addresses_id')->references('id')->on('shipping_addresses')->onDelete('cascade');
        });

        DB::table('contracts')
        ->join('shipping_contacts_multiples', 'contracts.send_by', '=', 'shipping_contacts_multiples.shipping_contacts_id')
        ->where('contracts.type', 'additional')
        ->update([
            'contracts.shipping_addresses_id' => DB::raw('shipping_contacts_multiples.shipping_id'),
        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['shipping_addresses_id']);
            $table->dropColumn('shipping_addresses_id');
        });
    }
};
