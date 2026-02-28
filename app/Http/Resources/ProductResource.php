<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ingredient_id' => $this->ingredient_id,
            'company' => $this->company,
            'name' => $this->name,
            'unit_type' => $this->unit_type?->value ?? $this->unit_type,
            'unit_options' => $this->unit_options ?? [],
            'unit_default' => $this->unit_default,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
