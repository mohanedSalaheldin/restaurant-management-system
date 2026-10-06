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
    Schema::create('tables', function (Blueprint $table) {
        $table->id();
        $table->string('table_number')->unique(); // e.g. T01, VIP-1
        $table->enum('type', ['private', 'public'])->default('public'); // Private or Public
        $table->unsignedInteger('min_capacity')->default(1);
        $table->unsignedInteger('max_capacity')->default(4);
        $table->string('location')->nullable(); // Ground Floor, First Floor, Garden
        $table->enum('status', ['available', 'occupied', 'reserved', 'maintenance'])->default('available');
        $table->string('unique_token', 64)->unique(); // Used for QR code link
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
