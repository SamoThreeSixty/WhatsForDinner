<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecipeRequest;
use App\Http\Resources\RecipeResource;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\Tag;
use App\Services\RecipeQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RecipeController extends Controller
{
    public function __construct(private readonly RecipeQueryService $recipeQueryService)
    {
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'tag' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'array', 'max:25'],
            'tags.*' => ['string', 'max:255'],
            'ingredient_id' => ['nullable', 'integer', 'exists:ingredients,id'],
            'ingredient_slug' => ['nullable', 'string', 'max:255', 'exists:ingredients,slug'],
            'max_cook_time' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'source_type' => ['nullable', Rule::in(['manual', 'site_import', 'ai_generated'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $perPage = $validated['per_page'] ?? 20;
        $page = $validated['page'] ?? null;

        $query = $this->recipeQueryService->buildFilteredQuery($validated);

        return RecipeResource::collection(
            $query->paginate($perPage, ['*'], 'page', $page)
        );
    }

    public function store(RecipeRequest $request)
    {
        $validated = $request->validated();
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

    public function update(RecipeRequest $request, Recipe $recipe)
    {
        $validated = $request->validated();

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
