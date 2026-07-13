<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'key', 'value'])]
class Setting extends Model
{
    protected $casts = [
        'value' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function getValue(string $key, mixed $default = null, ?int $tenantId = null): mixed
    {
        $tenantId ??= auth()->user()?->tenant_id;

        return static::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->where('key', $key)
            ->first()?->value ?? $default;
    }

    public static function setValue(string $key, mixed $value, ?int $tenantId = null): void
    {
        $tenantId ??= auth()->user()?->tenant_id;

        static::query()->updateOrCreate([
            'tenant_id' => $tenantId,
            'key' => $key,
        ], ['value' => $value]);
    }
}
