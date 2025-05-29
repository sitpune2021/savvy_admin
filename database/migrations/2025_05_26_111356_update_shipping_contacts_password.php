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
        Schema::table('shipping_contacts', function (Blueprint $table) {
            $table->string('password')->nullable()->default(Hash::make('Saavy@123'))->change();
        });

       $records = DB::table('shipping_contacts')->get();

        foreach ($records as $record) {
            if (Hash::check('Savvy@123', $record->password)) {
                DB::table('shipping_contacts')
                    ->where('id', $record->id)
                    ->update(['password' => Hash::make('Saavy@123')]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_contacts', function (Blueprint $table) {
            $table->string('password')->nullable()->default(Hash::make('Savvy@123'))->change();
        });

        $records = DB::table('shipping_contacts')->get();

        foreach ($records as $record) {
            if (Hash::check('Saavy@123', $record->password)) {
                DB::table('shipping_contacts')
                    ->where('id', $record->id)
                    ->update(['password' => Hash::make('Savvy@123')]);
            }
        }
    }
};
