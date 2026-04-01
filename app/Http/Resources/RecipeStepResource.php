<?php

namespace App\Http\Resources;

use App\Models\RecipeStep;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RecipeStep */
class RecipeStepResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'recipe_id' => $this->recipe_id,
            'position' => $this->position,
            'instruction' => $this->instruction,
            'timer_seconds' => $this->timer_seconds,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
