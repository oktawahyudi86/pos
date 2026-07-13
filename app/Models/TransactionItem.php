<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['transaction_id', 'product_id', 'product_name', 'base_price', 'unit_price', 'quantity', 'line_total', 'note'])]
class TransactionItem extends Model
{
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variantOptions(): HasMany
    {
        return $this->hasMany(TransactionItemVariantOption::class);
    }

    public function addons(): HasMany
    {
        return $this->hasMany(TransactionItemAddon::class);
    }
}
