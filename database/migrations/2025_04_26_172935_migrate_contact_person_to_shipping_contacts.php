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
        $addresses = DB::table('shipping_addresses')->whereNotNull('contact_person')->get();

        foreach ($addresses as $address) {
            DB::table('shipping_contacts')->insert([
                'shipping_id' => $address->id,
                'name' => $address->contact_person,
                'phone' => $address->contact_person_phone,
                'password' => $address->password,
                'created_at' => $address->created_at,
                'updated_at' => $address->updated_at,
                'deleted_at' => $address->deleted_at,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('shipping_contacts')->delete();
    }
};
