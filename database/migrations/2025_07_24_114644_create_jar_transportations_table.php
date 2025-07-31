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
       Schema::create('jar_transportations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plant_id');
            $table->unsignedBigInteger('driver_id'); // Use unsignedBigInteger for foreign keys
            $table->date('date')->default(DB::raw('CURRENT_DATE')); // Use DB::raw here
            $table->enum('status', ['dispatching', 'receiving', 'received'])->default('dispatching');
            $table->softDeletes();
            $table->timestamps();

            // Foreign keys
            $table->foreign('plant_id')->references('id')->on('plants')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
        });

        // Raw SQL insert (ensure at least one driver has a valid plant_id)
        DB::statement("
            INSERT INTO jar_transportations (plant_id, driver_id, date, status, created_at, updated_at, deleted_at)
            SELECT plant_id, id, CURRENT_DATE, 'dispatching', NOW(), NOW(), deleted_at
            FROM drivers
            WHERE plant_id IS NOT NULL
        ");




    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jar_transportations');
    }
};
