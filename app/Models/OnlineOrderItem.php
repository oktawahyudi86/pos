<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'online_order_id',
    'product_id',
    'product_name',
    'base_price',
    'unit_price',
    'quantity',
    'line_total',
    'note',
    'variant_payload',
    'addon_payload',
])]
class OnlineOrderItem extends Model
{
    protected $casts = [
        'variant_payload' => 'array',
        'addon_payload' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(OnlineOrder::class, 'online_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
