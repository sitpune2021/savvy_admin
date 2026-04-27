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
        Schema::create('distributor_plant_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_plant_orders_id')->constrained('distributor_plant_orders', indexName: 'dist_disp_orders_foreign')->cascadeOnDelete();
            $table->integer('dispatched_labeled_jars');
            $table->integer('dispatched_unlabeled_jars')->default(0);
            $table->json('label_breakdown')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributor_plant_dispatches');
    }
};
