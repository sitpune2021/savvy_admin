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
        Schema::create('shipping_contacts_multiples', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipping_id');
            $table->unsignedBigInteger('shipping_contacts_id');
            $table->foreign('shipping_id')->references('id')->on('shipping_addresses')->onDelete('cascade');
            $table->foreign('shipping_contacts_id')->references('id')->on('shipping_contacts')->onDelete('cascade');
            $table->string('mode')->default('main');
            $table->softDeletes();
            $table->timestamps();
        });
        DB::statement("
            INSERT INTO shipping_contacts_multiples (
                shipping_contacts_id, 
                shipping_id, 
                created_at, 
                updated_at, 
                deleted_at
            )
            SELECT 
                id AS shipping_contacts_id,
                shipping_id,
                created_at,
                updated_at,
                deleted_at
            FROM shipping_contacts
            WHERE shipping_id IS NOT NULL
        ");

        Schema::table('shipping_contacts', function (Blueprint $table) {
            $table->dropForeign(['shipping_id']); // Drop FK first
            $table->dropColumn('shipping_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_contacts', function (Blueprint $table) {
            $table->unsignedBigInteger('shipping_id')->nullable()->after('id');
            $table->foreign('shipping_id')->references('id')->on('shipping_addresses')->onDelete('cascade');
        });

        // Optional: Repopulate the column with "main" contact's data
        DB::statement("
            UPDATE shipping_contacts sc
            JOIN (
                SELECT shipping_contacts_id, shipping_id
                FROM shipping_contacts_multiples
                WHERE mode = 'main'
            ) scm ON sc.id = scm.shipping_contacts_id
            SET sc.shipping_id = scm.shipping_id
        ");
    
        Schema::dropIfExists('shipping_contacts_multiples');
    }
};
