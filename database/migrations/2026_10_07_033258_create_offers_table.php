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
        // 1. جدول العروض
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->decimal('discount_value', 8, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->json('applicable_days')->nullable(); // e.g. ["Mon", "Tue"] or null for all
            $table->boolean('status')->default(true);
            $table->boolean('display_on_menu')->default(true);
            $table->timestamps();
        });

        // 2. جدول الربط مع الأصناف (Pivot Table)
        Schema::create('offer_menu_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_menu_item');
        Schema::dropIfExists('offers');
    }
};
