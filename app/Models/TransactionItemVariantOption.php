<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['transaction_item_id', 'variant_option_id', 'variant_group_name', 'option_name', 'price_delta'])]
class TransactionItemVariantOption extends Model
{
    public function item(): BelongsTo
    {
        return $this->belongsTo(TransactionItem::class, 'transaction_item_id');
    }
}
