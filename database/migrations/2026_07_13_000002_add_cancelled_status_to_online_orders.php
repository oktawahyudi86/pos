<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE online_orders MODIFY status ENUM('pesanan_masuk', 'konfirmasi_pembayaran', 'sedang_diproses', 'dikirim', 'selesai', 'dibatalkan') NOT NULL DEFAULT 'pesanan_masuk'");
        }
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE online_orders MODIFY status ENUM('pesanan_masuk', 'konfirmasi_pembayaran', 'sedang_diproses', 'dikirim', 'selesai') NOT NULL DEFAULT 'pesanan_masuk'");
        }
    }
};
