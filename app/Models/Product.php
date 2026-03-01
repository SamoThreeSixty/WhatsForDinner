<?php

namespace App\Models;

use App\Enums\UnitType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

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

    protected static function booted(): void
    {
        static::creating(function (self $product): void {
            if (! $product->slug) {
                $product->slug = static::nextUniqueSlug($product);
            }
        });

        static::updating(function (self $product): void {
            if ($product->isDirty('slug')) {
                $product->slug = (string) $product->getOriginal('slug');
            }
        });
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    private static function nextUniqueSlug(self $product): string
    {
        $ingredientSlug = null;

        if ($product->ingredient_id) {
            $ingredient = Ingredient::query()->withTrashed()->find($product->ingredient_id);
            $ingredientSlug = $ingredient?->slug;
        }

        $parts = array_filter([
            $ingredientSlug,
            trim((string) ($product->company ?? '')),
            trim((string) $product->name),
        ]);

        $seed = Str::slug(Str::lower(implode('-', $parts)));
        if ($seed === '') {
            $seed = 'product';
        }

        $slug = $seed;
        $counter = 2;

        while (static::query()->withTrashed()->where('slug', $slug)->exists()) {
            $slug = $seed.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
