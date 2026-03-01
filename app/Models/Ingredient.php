<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ingredient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug',
        'name',
        'category',
        'category_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $ingredient): void {
            $ingredient->name = Str::lower(trim((string) $ingredient->name));

            if (! $ingredient->slug) {
                $ingredient->slug = static::nextUniqueSlug(Str::slug($ingredient->name));
            }
        });

        static::updating(function (self $ingredient): void {
            $ingredient->name = Str::lower(trim((string) $ingredient->name));

            if ($ingredient->isDirty('slug')) {
                $ingredient->slug = (string) $ingredient->getOriginal('slug');
            }
        });
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function catalogCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    private static function nextUniqueSlug(string $base): string
    {
        $seed = $base !== '' ? $base : 'ingredient';
        $slug = $seed;
        $counter = 2;

        while (static::query()->withTrashed()->where('slug', $slug)->exists()) {
            $slug = $seed.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
