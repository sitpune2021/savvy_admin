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
        Schema::create('distributor_plant_order_acceptances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_plant_orders_id')->constrained('distributor_plant_orders', indexName: 'dist_acc_orders_foreign')->cascadeOnDelete();
            $table->integer('received_labeled_jars');
            $table->integer('received_unlabeled_jars')->default(0);
            $table->integer('damaged_jars')->default(0);
            $table->text('remarks')->nullable();
            $table->enum('status', ['accepted', 'partial', 'rejected'])->default('accepted');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributor_plant_order_acceptances');
    }
};
