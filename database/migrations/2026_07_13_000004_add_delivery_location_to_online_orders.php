<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('online_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('online_orders', 'address_note')) {
                $table->text('address_note')->nullable()->after('address');
            }

            if (! Schema::hasColumn('online_orders', 'delivery_latitude')) {
                $table->decimal('delivery_latitude', 10, 7)->nullable()->after('address_note');
            }

            if (! Schema::hasColumn('online_orders', 'delivery_longitude')) {
                $table->decimal('delivery_longitude', 10, 7)->nullable()->after('delivery_latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('online_orders', function (Blueprint $table) {
            foreach (['delivery_longitude', 'delivery_latitude', 'address_note'] as $column) {
                if (Schema::hasColumn('online_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
