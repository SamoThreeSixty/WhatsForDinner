<?php

namespace App\Http\Resources;

use App\Models\Household;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Household */
class HouseholdResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'locale' => $this->locale,
            'currency' => $this->currency,
            'new_members' => $this->open_to_new_members,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
