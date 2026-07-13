<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'tenant_id',
    'invoice_number',
    'receipt_code',
    'channel',
    'user_id',
    'customer_name',
    'customer_phone',
    'payment_method',
    'status',
    'subtotal',
    'discount_type',
    'discount_value',
    'discount_amount',
    'tax_amount',
    'total',
    'paid_amount',
    'change_amount',
    'paid_at',
])]
class Transaction extends Model
{
    protected $casts = [
        'paid_at' => 'datetime',
        'discount_value' => 'decimal:2',
    ];

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }
}
