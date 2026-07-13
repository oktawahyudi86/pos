<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'tenant_id',
    'order_number',
    'customer_name',
    'wa_number',
    'address',
    'address_note',
    'delivery_latitude',
    'delivery_longitude',
    'delivery_province',
    'delivery_city',
    'delivery_district',
    'delivery_village',
    'delivery_postal_code',
    'status',
    'payment_method',
    'subtotal',
    'shipping_cost',
    'total',
    'placed_at',
    'payment_reminded_at',
    'accepted_at',
    'processing_at',
    'out_for_delivery_at',
    'finished_at',
])]
class OnlineOrder extends Model
{
    public const STATUS_PESANAN_MASUK = 'pesanan_masuk';
    public const STATUS_KONFIRMASI_PEMBAYARAN = 'konfirmasi_pembayaran';
    public const STATUS_SEDANG_DIPROSES = 'sedang_diproses';
    public const STATUS_DIKIRIM = 'dikirim';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DIBATALKAN = 'dibatalkan';

    protected $casts = [
        'placed_at' => 'datetime',
        'payment_reminded_at' => 'datetime',
        'accepted_at' => 'datetime',
        'processing_at' => 'datetime',
        'out_for_delivery_at' => 'datetime',
        'finished_at' => 'datetime',
        'delivery_latitude' => 'decimal:7',
        'delivery_longitude' => 'decimal:7',
    ];

    public function deliveryMapUrl(): ?string
    {
        if ($this->delivery_latitude === null || $this->delivery_longitude === null) {
            return null;
        }

        return 'https://www.google.com/maps?q='.$this->delivery_latitude.','.$this->delivery_longitude;
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PESANAN_MASUK,
            self::STATUS_KONFIRMASI_PEMBAYARAN,
            self::STATUS_SEDANG_DIPROSES,
            self::STATUS_DIKIRIM,
            self::STATUS_SELESAI,
            self::STATUS_DIBATALKAN,
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PESANAN_MASUK => 'Pesanan Masuk',
            self::STATUS_KONFIRMASI_PEMBAYARAN => 'Konfirmasi Pembayaran',
            self::STATUS_SEDANG_DIPROSES => 'Sedang Diproses',
            self::STATUS_DIKIRIM => 'Dikirim',
            self::STATUS_SELESAI => 'Selesai',
            self::STATUS_DIBATALKAN => 'Dibatalkan',
        ];
    }

    public static function progressStatuses(): array
    {
        return [
            self::STATUS_PESANAN_MASUK,
            self::STATUS_KONFIRMASI_PEMBAYARAN,
            self::STATUS_SEDANG_DIPROSES,
            self::STATUS_DIKIRIM,
            self::STATUS_SELESAI,
        ];
    }

    public static function statusBadgeClasses(): array
    {
        return [
            self::STATUS_PESANAN_MASUK => 'bg-[#eef3ff] text-[#001356] border-[#b9c7df]',
            self::STATUS_KONFIRMASI_PEMBAYARAN => 'bg-[#fff8e1] text-[#7a4b00] border-[#f2d48a]',
            self::STATUS_SEDANG_DIPROSES => 'bg-[#e7fff2] text-[#005236] border-[#8fdcb7]',
            self::STATUS_DIKIRIM => 'bg-[#e8f2ff] text-[#004395] border-[#adc6ff]',
            self::STATUS_SELESAI => 'bg-[#edf7ed] text-[#1b5e20] border-[#a5d6a7]',
            self::STATUS_DIBATALKAN => 'bg-[#fff4f2] text-[#93000a] border-[#ffdad6]',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? str($this->status)->replace('_', ' ')->title()->toString();
    }

    public function statusBadgeClass(): string
    {
        return self::statusBadgeClasses()[$this->status] ?? 'bg-[#f6faff] text-[#454650] border-[#c6c5d2]';
    }

    public function statusPosition(): int
    {
        $position = array_search($this->status, self::progressStatuses(), true);

        return $position === false ? 0 : $position;
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OnlineOrderItem::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OnlineOrderStatusLog::class);
    }
}
