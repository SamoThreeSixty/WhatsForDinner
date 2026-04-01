<?php

namespace App\Http\Resources;

use App\Models\RecipeIngredient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RecipeIngredient */
class RecipeIngredientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'recipe_id' => $this->recipe_id,
            'position' => $this->position,
            'ingredient_id' => $this->ingredient_id,
            'ingredient_slug' => $this->ingredient?->slug,
            'ingredient_name' => $this->ingredient?->name,
            'ingredient_text' => $this->ingredient_text,
            'amount' => $this->amount !== null ? (float) $this->amount : null,
            'unit' => $this->unit,
            'preparation_note' => $this->preparation_note,
            'is_optional' => $this->is_optional,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
