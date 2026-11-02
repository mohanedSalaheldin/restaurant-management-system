<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->enum('payment_method', ['cash', 'card'])->default('cash');
            $table->decimal('amount_tendered', 10, 2); // amount handed over by the customer
            $table->decimal('amount_paid', 10, 2); // portion applied to the bill
            $table->decimal('change_returned', 10, 2)->default(0.00);
            $table->string('reference_number')->nullable(); // card slip or transaction number
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};