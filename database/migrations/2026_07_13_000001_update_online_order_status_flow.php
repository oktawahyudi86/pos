<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('online_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('online_orders', 'payment_reminded_at')) {
                $table->timestamp('payment_reminded_at')->nullable()->after('placed_at');
            }
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE online_orders MODIFY status ENUM('pesanan_masuk', 'diterima', 'diproses', 'diantar', 'konfirmasi_pembayaran', 'sedang_diproses', 'dikirim', 'selesai', 'dibatalkan') NOT NULL DEFAULT 'pesanan_masuk'");
        }

        DB::table('online_orders')->where('status', 'diterima')->update(['status' => 'konfirmasi_pembayaran']);
        DB::table('online_orders')->where('status', 'diproses')->update(['status' => 'sedang_diproses']);
        DB::table('online_orders')->where('status', 'diantar')->update(['status' => 'dikirim']);

        DB::table('online_order_status_logs')->where('status', 'diterima')->update(['status' => 'konfirmasi_pembayaran']);
        DB::table('online_order_status_logs')->where('status', 'diproses')->update(['status' => 'sedang_diproses']);
        DB::table('online_order_status_logs')->where('status', 'diantar')->update(['status' => 'dikirim']);

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE online_orders MODIFY status ENUM('pesanan_masuk', 'konfirmasi_pembayaran', 'sedang_diproses', 'dikirim', 'selesai', 'dibatalkan') NOT NULL DEFAULT 'pesanan_masuk'");
        }
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE online_orders MODIFY status ENUM('pesanan_masuk', 'diterima', 'diproses', 'diantar', 'konfirmasi_pembayaran', 'sedang_diproses', 'dikirim', 'selesai', 'dibatalkan') NOT NULL DEFAULT 'pesanan_masuk'");
        }

        DB::table('online_orders')->where('status', 'konfirmasi_pembayaran')->update(['status' => 'diterima']);
        DB::table('online_orders')->where('status', 'sedang_diproses')->update(['status' => 'diproses']);
        DB::table('online_orders')->where('status', 'dikirim')->update(['status' => 'diantar']);

        DB::table('online_order_status_logs')->where('status', 'konfirmasi_pembayaran')->update(['status' => 'diterima']);
        DB::table('online_order_status_logs')->where('status', 'sedang_diproses')->update(['status' => 'diproses']);
        DB::table('online_order_status_logs')->where('status', 'dikirim')->update(['status' => 'diantar']);

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE online_orders MODIFY status ENUM('pesanan_masuk', 'diterima', 'diproses', 'diantar', 'selesai') NOT NULL DEFAULT 'pesanan_masuk'");
        }
    }
};
