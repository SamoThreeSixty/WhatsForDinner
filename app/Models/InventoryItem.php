<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends AbstractTenancyModel
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'quantity',
        'unit',
        'purchased_at',
        'expires_at',
        'category',
        'location',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'purchased_at' => 'date',
        'expires_at' => 'date',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
