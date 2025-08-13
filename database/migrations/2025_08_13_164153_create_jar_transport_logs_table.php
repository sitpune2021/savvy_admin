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
        Schema::create('jar_transport_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jar_transportation_id');
            $table->foreign('jar_transportation_id')->references('id')->on('jar_transportations')->onDelete('cascade');
            $table->enum('action', ['dispatching', 'receiving', 'received']);
            $table->date('date'); // Use DB::raw here
            $table->bigInteger('quantity'); // Quantity related to the action
            $table->json('stocks'); // Optional remarks for the log entry
            $table->softDeletes(); // Optional: if you want to keep a record of deleted logs
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jar_transport_logs');
    }
};
