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
        Schema::create('lab_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_name'); // user input (Water, Food, etc.)
            $table->string('title')->nullable();

            $table->string('file_path');

            $table->integer('version_no')->default(1);
            $table->unsignedBigInteger('parent_id')->nullable();

            $table->date('report_date')->nullable();
            $table->date('expiry_date')->nullable();

            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_reports');
    }
};
