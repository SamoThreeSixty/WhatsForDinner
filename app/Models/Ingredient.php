<?php

namespace App\Models;

use App\Enums\UnitType;
use App\Models\Traits\BelongsToHousehold;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use HasFactory, SoftDeletes, BelongsToHousehold;

    protected $fillable = [
        'household_id',
        'name',
        'category',
        'location',
        'unit_type',
        'unit',
        'quantity',
        'purchased_at',
        'expires_at',
        'batch_reference',
    ];

    protected $casts = [
        'unit_type' => UnitType::class,
        'quantity' => 'decimal:3',
        'purchased_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }
}
