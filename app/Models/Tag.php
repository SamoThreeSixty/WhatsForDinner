<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasUniqueSlug;

    protected $fillable = [
        'slug',
        'name',
    ];

    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class)->withTimestamps();
    }

    protected function slugSeed(): string
    {
        return (string) $this->name;
    }

    protected function slugFallbackSeed(): string
    {
        return 'tag';
    }
}
