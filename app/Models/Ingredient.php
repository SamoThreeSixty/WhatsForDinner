<?php

namespace App\Models;

use App\Enums\UnitType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
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
}
