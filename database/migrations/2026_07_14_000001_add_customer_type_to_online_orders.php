<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('online_orders', function (Blueprint $table) {
            $table->enum('customer_type', ['member', 'guest'])->default('member')->after('order_number');
            $table->foreignId('user_id')->nullable()->after('customer_type')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('online_orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'customer_type']);
        });
    }
};
