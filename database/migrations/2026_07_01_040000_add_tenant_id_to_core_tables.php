<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index(['tenant_id', 'slug']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index(['tenant_id', 'sku']);
        });

        Schema::table('variant_groups', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index(['tenant_id', 'slug']);
        });

        Schema::table('addons', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index(['tenant_id', 'slug']);
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index(['tenant_id', 'key']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index(['tenant_id', 'paid_at']);
        });

        $tenantId = DB::table('tenants')->orderBy('id')->value('id');

        if ($tenantId) {
            foreach (['categories', 'products', 'variant_groups', 'addons', 'settings', 'transactions'] as $table) {
                DB::table($table)
                    ->whereNull('tenant_id')
                    ->update(['tenant_id' => $tenantId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'paid_at']);
            $table->dropConstrainedForeignId('tenant_id');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'key']);
            $table->dropConstrainedForeignId('tenant_id');
        });

        Schema::table('addons', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'slug']);
            $table->dropConstrainedForeignId('tenant_id');
        });

        Schema::table('variant_groups', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'slug']);
            $table->dropConstrainedForeignId('tenant_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'sku']);
            $table->dropConstrainedForeignId('tenant_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'slug']);
            $table->dropConstrainedForeignId('tenant_id');
        });
    }
};
