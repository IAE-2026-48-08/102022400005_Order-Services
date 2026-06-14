<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->string('id')->primary(); // ORD-001, dst.
            $table->string('customer_id');
            $table->double('total_amount');
            $table->string('status')->default('PENDING'); // PENDING, TRANSACTION, FAILED_STOCK_UNAVAILABLE, SHIPPED, DELIVERED
            $table->string('receipt_number')->nullable(); // Kolom untuk menampung SOAP audit ReceiptNumber
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};