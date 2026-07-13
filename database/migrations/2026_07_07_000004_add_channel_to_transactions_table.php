<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('channel', ['offline', 'online'])->default('offline')->after('receipt_code');
            $table->index(['tenant_id', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'channel']);
            $table->dropColumn('channel');
        });
    }
};
