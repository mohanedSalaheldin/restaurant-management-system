<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // e.g. INV-20261102-0001
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('table_id')->nullable()->constrained()->nullOnDelete();
            
            $table->decimal('subtotal', 10, 2); // after item offers, before tax and service
            $table->decimal('tax_rate', 5, 2)->default(14.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('service_rate', 5, 2)->default(10.00);
            $table->decimal('service_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2);
            
            $table->enum('status', ['unpaid', 'paid', 'partially_paid', 'cancelled'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};