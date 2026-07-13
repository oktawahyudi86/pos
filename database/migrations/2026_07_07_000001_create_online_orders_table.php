<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('wa_number');
            $table->text('address');
            $table->enum('status', ['pesanan_masuk', 'konfirmasi_pembayaran', 'sedang_diproses', 'dikirim', 'selesai', 'dibatalkan'])->default('pesanan_masuk');
            $table->enum('payment_method', ['manual_transfer', 'qris'])->default('manual_transfer');
            $table->unsignedInteger('subtotal')->default(0);
            $table->unsignedInteger('shipping_cost')->default(0);
            $table->unsignedInteger('total')->default(0);
            $table->timestamp('placed_at')->nullable();
            $table->timestamp('payment_reminded_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('out_for_delivery_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_orders');
    }
};
