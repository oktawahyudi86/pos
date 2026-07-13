<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['variant_group_id', 'name', 'price_delta', 'is_active'])]
class VariantOption extends Model
{
    public function variantGroup(): BelongsTo
    {
        return $this->belongsTo(VariantGroup::class);
    }
}
