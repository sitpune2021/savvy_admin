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
        Schema::create('raw_stock_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('raw_material_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // who did the action
            $table->foreignId('plant_id')->nullable()->constrained()->onDelete('set null'); // optional for distribution/accept
            $table->enum('action', ['purchase', 'distribution', 'acceptance', 'rejection']);
            $table->decimal('quantity', 12, 2);
            $table->text('note')->nullable(); // optional: reason or comment
            $table->timestamp('action_time')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes(); // For soft deleting distributions
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raw_stock_logs');
    }
};
