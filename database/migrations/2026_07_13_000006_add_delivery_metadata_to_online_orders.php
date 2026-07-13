<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('online_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('online_orders', 'delivery_place_id')) {
                $table->string('delivery_place_id', 255)->nullable()->after('delivery_postal_code');
            }

            if (! Schema::hasColumn('online_orders', 'delivery_address_label')) {
                $table->string('delivery_address_label', 20)->nullable()->after('delivery_place_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('online_orders', function (Blueprint $table) {
            if (Schema::hasColumn('online_orders', 'delivery_address_label')) {
                $table->dropColumn('delivery_address_label');
            }

            if (Schema::hasColumn('online_orders', 'delivery_place_id')) {
                $table->dropColumn('delivery_place_id');
            }
        });
    }
};
