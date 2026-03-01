<?php

namespace App\Http\Resources;

use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin InventoryItem */
class InventoryItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'quantity' => (float) $this->quantity,
            'unit' => $this->unit,
            'purchased_at' => $this->purchased_at?->toDateString(),
            'expires_at' => $this->expires_at?->toDateString(),
            'category' => $this->category,
            'location' => $this->location,
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product?->id,
                'ingredient_id' => $this->product?->ingredient_id,
                'company' => $this->product?->company,
                'name' => $this->product?->name,
                'unit_type' => $this->product?->unit_type?->value ?? $this->product?->unit_type,
                'unit_options' => $this->product?->unit_options ?? [],
                'unit_default' => $this->product?->unit_default,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
