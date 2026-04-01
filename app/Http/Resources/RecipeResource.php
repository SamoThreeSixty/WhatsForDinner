<?php

namespace App\Http\Resources;

use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Recipe */
class RecipeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'household_id' => $this->household_id,
            'created_by_user_id' => $this->created_by_user_id,
            'title' => $this->title,
            'description' => $this->description,
            'prep_time_minutes' => $this->prep_time_minutes,
            'cook_time_minutes' => $this->cook_time_minutes,
            'servings' => $this->servings,
            'source_type' => $this->source_type,
            'source_url' => $this->source_url,
            'nutrition' => $this->nutrition_json ?? [],
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'steps' => RecipeStepResource::collection($this->whenLoaded('steps')),
            'ingredients' => RecipeIngredientResource::collection($this->whenLoaded('ingredients')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
