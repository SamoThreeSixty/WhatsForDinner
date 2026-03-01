<?php

namespace App\Http\Controllers;

use App\Http\Resources\RecipeResource;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RecipeController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'tag' => ['nullable', 'string', 'max:255'],
            'ingredient_id' => ['nullable', 'integer', 'exists:ingredients,id'],
            'ingredient_slug' => ['nullable', 'string', 'max:255', 'exists:ingredients,slug'],
            'max_cook_time' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'source_type' => ['nullable', Rule::in(['manual', 'site_import', 'ai_generated'])],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $search = isset($validated['q']) ? trim(mb_strtolower($validated['q'])) : '';
        $limit = $validated['limit'] ?? 50;

        $query = Recipe::query()
            ->with(['tags', 'steps', 'ingredients.ingredient'])
            ->orderBy('title');

        if ($search !== '') {
            $query->where(function ($nested) use ($search) {
                $nested->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        if (isset($validated['tag'])) {
            $tag = trim(mb_strtolower($validated['tag']));
            $query->whereHas('tags', function ($tagQuery) use ($tag) {
                $tagQuery->where('slug', Str::slug($tag))
                    ->orWhere('name', 'like', '%'.$tag.'%');
            });
        }

        if (isset($validated['ingredient_id'])) {
            $query->whereHas('ingredients', function ($ingredientQuery) use ($validated) {
                $ingredientQuery->where('ingredient_id', $validated['ingredient_id']);
            });
        }

        if (isset($validated['ingredient_slug'])) {
            $query->whereHas('ingredients.ingredient', function ($ingredientQuery) use ($validated) {
                $ingredientQuery->where('slug', $validated['ingredient_slug']);
            });
        }

        if (isset($validated['max_cook_time'])) {
            $query->where('cook_time_minutes', '<=', $validated['max_cook_time']);
        }

        if (isset($validated['source_type'])) {
            $query->where('source_type', $validated['source_type']);
        }

        return RecipeResource::collection($query->limit($limit)->get());
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);
        $recipe = DB::transaction(function () use ($validated, $request) {
            $recipePayload = $this->recipePayload($validated);
            $recipePayload['created_by_user_id'] = $request->user()->id;
            $recipe = Recipe::query()->create($recipePayload);

            $this->syncSteps($recipe, $validated['steps'] ?? []);
            $this->syncIngredients($recipe, $validated['ingredients'] ?? []);
            $this->syncTags($recipe, $validated['tags'] ?? []);

            return $recipe;
        });

        return new RecipeResource($recipe->load(['tags', 'steps', 'ingredients.ingredient']));
    }

    public function show(Recipe $recipe)
    {
        return new RecipeResource($recipe->load(['tags', 'steps', 'ingredients.ingredient']));
    }

    public function update(Request $request, Recipe $recipe)
    {
        $validated = $this->validatePayload($request);

        DB::transaction(function () use ($recipe, $validated) {
            $recipe->update($this->recipePayload($validated));

            $this->syncSteps($recipe, $validated['steps'] ?? []);
            $this->syncIngredients($recipe, $validated['ingredients'] ?? []);
            $this->syncTags($recipe, $validated['tags'] ?? []);
        });

        return new RecipeResource($recipe->fresh()->load(['tags', 'steps', 'ingredients.ingredient']));
    }

    public function destroy(Recipe $recipe)
    {
        $recipe->delete();

        return response()->json();
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePayload(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'prep_time_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'cook_time_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'servings' => ['nullable', 'integer', 'min:1', 'max:100'],
            'source_type' => ['nullable', Rule::in(['manual', 'site_import', 'ai_generated'])],
            'source_url' => ['nullable', 'url', 'max:2048'],
            'nutrition' => ['nullable', 'array'],

            'steps' => ['required', 'array', 'min:1'],
            'steps.*.position' => ['nullable', 'integer', 'min:1'],
            'steps.*.instruction' => ['required', 'string'],
            'steps.*.timer_seconds' => ['nullable', 'integer', 'min:1'],

            'ingredients' => ['required', 'array', 'min:1'],
            'ingredients.*.position' => ['nullable', 'integer', 'min:1'],
            'ingredients.*.ingredient_id' => ['nullable', 'integer', 'exists:ingredients,id'],
            'ingredients.*.ingredient_slug' => ['nullable', 'string', 'max:255', 'exists:ingredients,slug'],
            'ingredients.*.ingredient_text' => ['nullable', 'string', 'max:255'],
            'ingredients.*.amount' => ['nullable', 'numeric', 'min:0'],
            'ingredients.*.unit' => ['nullable', 'string', 'max:32'],
            'ingredients.*.preparation_note' => ['nullable', 'string', 'max:255'],
            'ingredients.*.is_optional' => ['nullable', 'boolean'],

            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:64'],
        ]);

        foreach ($validated['ingredients'] as $index => $ingredientRow) {
            $hasIngredient =
                !empty($ingredientRow['ingredient_id']) ||
                !empty($ingredientRow['ingredient_slug']) ||
                !empty(trim((string) ($ingredientRow['ingredient_text'] ?? '')));

            if (! $hasIngredient) {
                throw ValidationException::withMessages([
                    'ingredients.'.$index => ['Each ingredient row must include ingredient_id, ingredient_slug, or ingredient_text.'],
                ]);
            }
        }

        return $validated;
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    private function recipePayload(array $validated): array
    {
        return [
            'title' => trim((string) $validated['title']),
            'description' => isset($validated['description']) ? trim((string) $validated['description']) : null,
            'prep_time_minutes' => $validated['prep_time_minutes'] ?? null,
            'cook_time_minutes' => $validated['cook_time_minutes'] ?? null,
            'servings' => $validated['servings'] ?? null,
            'source_type' => $validated['source_type'] ?? 'manual',
            'source_url' => $validated['source_url'] ?? null,
            'nutrition_json' => $validated['nutrition'] ?? [],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $steps
     */
    private function syncSteps(Recipe $recipe, array $steps): void
    {
        $recipe->steps()->delete();

        foreach (array_values($steps) as $index => $step) {
            $recipe->steps()->create([
                'position' => $index + 1,
                'instruction' => trim((string) $step['instruction']),
                'timer_seconds' => $step['timer_seconds'] ?? null,
            ]);
        }
    }

    /**
     * @param array<int, array<string, mixed>> $ingredients
     */
    private function syncIngredients(Recipe $recipe, array $ingredients): void
    {
        $recipe->ingredients()->delete();

        foreach (array_values($ingredients) as $index => $ingredient) {
            $ingredientId = $ingredient['ingredient_id'] ?? null;

            if ($ingredientId === null && !empty($ingredient['ingredient_slug'])) {
                $ingredientId = Ingredient::query()
                    ->withTrashed()
                    ->where('slug', trim((string) $ingredient['ingredient_slug']))
                    ->value('id');
            }

            $recipe->ingredients()->create([
                'position' => $index + 1,
                'ingredient_id' => $ingredientId,
                'ingredient_text' => isset($ingredient['ingredient_text']) ? trim((string) $ingredient['ingredient_text']) : null,
                'amount' => $ingredient['amount'] ?? null,
                'unit' => isset($ingredient['unit']) ? trim((string) $ingredient['unit']) : null,
                'preparation_note' => isset($ingredient['preparation_note']) ? trim((string) $ingredient['preparation_note']) : null,
                'is_optional' => (bool) ($ingredient['is_optional'] ?? false),
            ]);
        }
    }

    /**
     * @param array<int, string> $tags
     */
    private function syncTags(Recipe $recipe, array $tags): void
    {
        $tagIds = [];

        foreach ($tags as $tagName) {
            $normalized = trim((string) $tagName);
            if ($normalized === '') {
                continue;
            }

            $slug = Str::slug($normalized);
            if ($slug === '') {
                continue;
            }

            $tag = Tag::query()->firstOrCreate(
                ['slug' => $slug],
                ['name' => $normalized]
            );

            $tagIds[] = $tag->id;
        }

        $recipe->tags()->sync(array_values(array_unique($tagIds)));
    }
}
