<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_generation_failures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('shipping_id')->nullable()->constrained('shipping_addresses')->nullOnDelete();
            $table->date('failure_date');
            $table->string('source')->default('contract_scheduler');
            $table->string('reason');
            $table->text('details')->nullable();
            $table->timestamp('attempted_at');
            $table->timestamps();

            $table->index(['failure_date', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_generation_failures');
    }
};
