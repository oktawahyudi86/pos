<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('receipt_code', 20)->nullable()->after('invoice_number')->unique();
        });

        DB::table('transactions')->orderBy('id')->chunkById(100, function ($transactions) {
            foreach ($transactions as $transaction) {
                DB::table('transactions')
                    ->where('id', $transaction->id)
                    ->update([
                        'receipt_code' => $this->generateCode(),
                    ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(['receipt_code']);
            $table->dropColumn('receipt_code');
        });
    }

    private function generateCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (DB::table('transactions')->where('receipt_code', $code)->exists());

        return $code;
    }
};
