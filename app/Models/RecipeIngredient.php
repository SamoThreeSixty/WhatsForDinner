<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeIngredient extends Model
{
    protected $fillable = [
        'recipe_id',
        'position',
        'ingredient_id',
        'ingredient_text',
        'amount',
        'unit',
        'preparation_note',
        'is_optional',
    ];

    protected $casts = [
        'position' => 'integer',
        'amount' => 'decimal:3',
        'is_optional' => 'boolean',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
