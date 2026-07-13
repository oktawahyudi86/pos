<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('online_orders', function (Blueprint $table) {
            $columns = [
                'delivery_province' => 'delivery_longitude',
                'delivery_city' => 'delivery_province',
                'delivery_district' => 'delivery_city',
                'delivery_village' => 'delivery_district',
                'delivery_postal_code' => 'delivery_village',
            ];

            foreach ($columns as $column => $after) {
                if (! Schema::hasColumn('online_orders', $column)) {
                    $table->string($column, 120)->nullable()->after($after);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('online_orders', function (Blueprint $table) {
            foreach (['delivery_postal_code', 'delivery_village', 'delivery_district', 'delivery_city', 'delivery_province'] as $column) {
                if (Schema::hasColumn('online_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
