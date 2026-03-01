<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $category): void {
            if (! $category->slug) {
                $category->slug = static::nextUniqueSlug(Str::slug((string) $category->name));
            }
        });

        static::updating(function (self $category): void {
            if ($category->isDirty('slug')) {
                $category->slug = (string) $category->getOriginal('slug');
            }
        });
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }

    private static function nextUniqueSlug(string $base): string
    {
        $seed = $base !== '' ? $base : 'category';
        $slug = $seed;
        $counter = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $seed.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
