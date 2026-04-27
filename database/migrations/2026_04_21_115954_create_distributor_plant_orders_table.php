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
        Schema::create('distributor_plant_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('distributor_id')->constrained()->cascadeOnDelete();

            $table->integer('delivered_jars');
            $table->integer('used_previous_stock')->default(0);

            $table->integer('required_labeled_jars')->default(0);
            $table->integer('required_unlabeled_jars')->default(0);

            $table->json('jars_with_label')->nullable();

            $table->boolean('allow_remaining_stock')->default(true);

            $table->enum('status', [
                'pending',
                'approved',
                'in_production',
                'production_completed',
                'dispatched',
                'delivered',
                'closed',
                'rejected'
            ])->default('pending');

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributor_plant_orders');
    }
};
