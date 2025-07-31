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
        Schema::create('jar_maintances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plant_id');
            $table->unsignedBigInteger('driver_id'); // Use unsignedBigInteger for foreign keys
            $table->unsignedBigInteger('raw_material_variants_id');

            $table->date('date');
            $table->string('type');
            $table->string('qty');
            $table->enum('status', ['pending', 'in-prgress', 'okay'])->default('pending');
            $table->softDeletes();
            $table->timestamps();

            // Foreign keys
            $table->foreign('plant_id')->references('id')->on('plants')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->foreign('raw_material_variants_id')->references('id')->on('raw_material_variants')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jar_maintances');
    }
};
