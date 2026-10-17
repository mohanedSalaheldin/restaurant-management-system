<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // النادل أو المستخدم المنشئ
            $table->foreignId('table_id')->nullable()->constrained()->nullOnDelete(); // اختياري في حال Takeaway
            $table->enum('order_type', ['dine_in', 'takeaway'])->default('dine_in');
            $table->unsignedInteger('guest_count')->default(1);
            $table->enum('status', [
                'draft',
                'sent_to_kitchen',
                'in_preparation',
                'ready',
                'served',
                'completed',
                'cancelled'
            ])->default('draft');
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
