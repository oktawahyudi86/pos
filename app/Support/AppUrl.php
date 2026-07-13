<?php

namespace App\Support;

class AppUrl
{
    public static function route(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        return route($name, $parameters, $absolute);
    }

    public static function publicReceipt(string $code): string
    {
        return self::route('transactions.receipt.short', ['code' => $code]);
    }
}
