<?php

namespace App\Models;

use App\Enums\UnitType;
use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes, HasUniqueSlug;

    protected $fillable = [
        'slug',
        'ingredient_id',
        'company',
        'name',
        'unit_type',
        'unit_options',
        'unit_default',
    ];

    protected $casts = [
        'unit_type' => UnitType::class,
        'unit_options' => 'array',
    ];

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    protected function slugSeed(): string
    {
        if (! $this->ingredient_id) {
            throw new \LogicException('Product slug seed requires ingredient_id.');
        }

        $ingredient = Ingredient::query()->withTrashed()->find($this->ingredient_id);
        if (! $ingredient?->slug) {
            throw new \LogicException('Product slug seed requires a valid ingredient with a slug.');
        }

        $parts = array_filter([
            $ingredient->slug,
            trim((string) ($this->company ?? '')),
            trim((string) $this->name),
        ]);

        return Str::lower(implode('-', $parts));
    }

    protected function slugFallbackSeed(): string
    {
        return 'product';
    }
}
