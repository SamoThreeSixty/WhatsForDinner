<?php

namespace App\Http\Resources;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Ingredient */
class IngredientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $categorySlug = $this->catalogCategory?->slug ?? $this->category;

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'category' => $categorySlug,
            'category_slug' => $categorySlug,
            'category_id' => $this->category_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
