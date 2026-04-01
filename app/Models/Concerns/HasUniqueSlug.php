<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @mixin Model
 */
trait HasUniqueSlug
{
    protected static function bootHasUniqueSlug(): void
    {
        static::creating(function (Model $model): void {
            if (!$model->getAttribute($model->slugColumn())) {
                $model->setAttribute(
                    $model->slugColumn(),
                    $model->nextUniqueSlug($model->slugSeed(), $model->slugFallbackSeed())
                );
            }
        });

        static::updating(function (Model $model): void {
            if ($model->isDirty($model->slugColumn())) {
                $model->setAttribute($model->slugColumn(), (string)$model->getOriginal($model->slugColumn()));
            }
        });
    }

    protected function slugColumn(): string
    {
        return 'slug';
    }

    protected function slugFallbackSeed(): string
    {
        return 'item';
    }

    abstract protected function slugSeed(): string;

    private function nextUniqueSlug(string $seedSource, string $fallback): string
    {
        $seed = Str::slug(Str::lower(trim($seedSource)));
        if ($seed === '') {
            $seed = $fallback;
        }

        $slug = $seed;
        $counter = 2;
        $column = $this->slugColumn();

        while ($this->slugLookupQuery()->where($column, $slug)->exists()) {
            $slug = $seed . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function slugLookupQuery(): Builder
    {
        $query = static::query();

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            /** @var Builder $query */
            $query = $query->withTrashed();
        }

        return $query;
    }
}
