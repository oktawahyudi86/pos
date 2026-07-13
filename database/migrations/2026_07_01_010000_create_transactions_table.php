<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->enum('payment_method', ['cash', 'qris'])->default('cash');
            $table->enum('status', ['paid', 'void'])->default('paid');
            $table->unsignedInteger('subtotal')->default(0);
            $table->enum('discount_type', ['none', 'percent', 'nominal'])->default('none');
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->unsignedInteger('discount_amount')->default(0);
            $table->unsignedInteger('tax_amount')->default(0);
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('paid_amount')->default(0);
            $table->unsignedInteger('change_amount')->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
