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
        Schema::create('distributor_plant_productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_plant_orders_id')->constrained('distributor_plant_orders', indexName: 'dist_plant_orders_foreign')->cascadeOnDelete();

            $table->integer('delivered_jars');
            $table->integer('used_previous_stock');
            $table->integer('total_available');

            $table->integer('leak_jars')->default(0);
            $table->integer('green_jars')->default(0);

            $table->integer('usable_jars');

            $table->integer('labeled_jars');
            $table->integer('unlabeled_jars');

            $table->json('label_breakdown')->nullable();

            $table->integer('remaining_stock')->default(0);

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributor_plant_productions');
    }
};
