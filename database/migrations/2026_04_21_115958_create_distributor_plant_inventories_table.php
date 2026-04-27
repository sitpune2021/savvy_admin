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
        Schema::create('distributor_plant_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete();
            $table->foreignId('distributor_id')->constrained('distributors')->cascadeOnDelete();

            $table->integer('empty_jars')->default(0);
            $table->integer('filled_unlabeled_jars')->default(0);

            $table->json('labeled_jars')->nullable(); // {"2": 50}
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributor_plant_inventories');
    }
};
