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
        Schema::create('digital_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->integer('balance')->default(0);
            $table->unsignedBigInteger('accept_by')->nullable();
            $table->foreign('accept_by')->references('id')->on('shipping_contacts')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->softDeletes(); // For soft deleting distributions
            $table->timestamps();
        });

        DB::insert("
            INSERT INTO digital_cards (order_id, balance, accept_by, deleted_at, created_at, updated_at)
            SELECT 
                o.id AS order_id,
                COALESCE(c.quantity, 0) AS balance,
                (
                    SELECT scs.shipping_contacts_id
                    FROM shipping_contacts_multiples scs
                    WHERE scs.shipping_id = s.id
                    AND scs.mode = 'main'
                    LIMIT 1
                ) AS accept_by,
                o.deleted_at,
                o.created_at,
                o.updated_at
            FROM orders o
            JOIN contracts c ON c.id = o.contract_id
            JOIN shipping_addresses s ON s.id = o.shipping_id
            WHERE o.status = 'completed'
        ");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_cards');
    }
};
