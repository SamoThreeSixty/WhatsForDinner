<?php

namespace App\Services;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class RecipeQueryService
{
    /**
     * @param array<string, mixed> $filters
     */
    public function buildFilteredQuery(array $filters): Builder
    {
        $search = isset($filters['q']) ? trim(mb_strtolower((string) $filters['q'])) : '';

        $query = Recipe::query()
            ->with(['tags', 'steps', 'ingredients.ingredient'])
            ->orderBy('title');

        if ($search !== '') {
            $query->where(function ($nested) use ($search) {
                $nested->whereRaw('LOWER(title) like ?', ['%'.$search.'%'])
                    ->orWhereRaw('LOWER(description) like ?', ['%'.$search.'%']);
            });
        }

        $normalizedTags = $this->normalizedTagSlugs($filters);

        if ($normalizedTags !== []) {
            $query->whereHas('tags', function ($tagQuery) use ($normalizedTags) {
                $tagQuery->whereIn('slug', $normalizedTags);
            });
        }

        if (isset($filters['ingredient_id'])) {
            $query->whereHas('ingredients', function ($ingredientQuery) use ($filters) {
                $ingredientQuery->where('ingredient_id', $filters['ingredient_id']);
            });
        }

        if (isset($filters['ingredient_slug'])) {
            $query->whereHas('ingredients.ingredient', function ($ingredientQuery) use ($filters) {
                $ingredientQuery->where('slug', $filters['ingredient_slug']);
            });
        }

        if (isset($filters['max_cook_time'])) {
            $query->where('cook_time_minutes', '<=', $filters['max_cook_time']);
        }

        if (isset($filters['source_type'])) {
            $query->where('source_type', $filters['source_type']);
        }

        return $query;
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<int, string>
     */
    private function normalizedTagSlugs(array $filters): array
    {
        $normalizedTags = [];

        if (isset($filters['tag'])) {
            $tag = trim((string) $filters['tag']);
            if ($tag !== '') {
                $normalizedTags[] = Str::slug($tag);
            }
        }

        if (isset($filters['tags']) && is_array($filters['tags'])) {
            foreach ($filters['tags'] as $tag) {
                $normalized = Str::slug(trim((string) $tag));
                if ($normalized !== '') {
                    $normalizedTags[] = $normalized;
                }
            }
        }

        return array_values(array_unique($normalizedTags));
    }
}

