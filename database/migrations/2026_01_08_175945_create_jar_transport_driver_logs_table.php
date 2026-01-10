<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Schema::create('jar_transport_driver_logs', function (Blueprint $table) {
        //     $table->id();
        //     $table->unsignedBigInteger('driver_id');
        //     $table->foreign('driver_id')
        //         ->references('id')
        //         ->on('drivers')
        //         ->onDelete('cascade');

        //     $table->unsignedBigInteger('jar_transport_log_id');
        //     $table->foreign('jar_transport_log_id')
        //         ->references('id')
        //         ->on('jar_transport_logs')
        //         ->onDelete('cascade');

        //     $table->enum('action', ['receiving', 'received']);

        //     $table->enum('status', ['pending', 'accept', 'cancle'])
        //         ->default('pending');

        //     $table->string('remark')->nullable();

        //     $table->timestamps();

        //     $table->unique(
        //         ['jar_transport_log_id', 'action'],
        //         'unique_log_action'
        //     );
        // });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jar_transport_driver_logs');
    }
};
