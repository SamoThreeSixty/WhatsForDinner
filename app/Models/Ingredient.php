<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ingredient extends Model
{
    use HasFactory, SoftDeletes, HasUniqueSlug;

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
        });

        static::updating(function (self $ingredient): void {
            $ingredient->name = Str::lower(trim((string) $ingredient->name));
        });
    }

    protected function slugSeed(): string
    {
        return (string) $this->name;
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

    protected function slugFallbackSeed(): string
    {
        return 'ingredient';
    }
}
