<?php

namespace App\Http\Controllers;

use App\Http\Requests\IngredientRequest;
use App\Http\Resources\IngredientResource;
use App\Models\Category;
use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $search = isset($validated['q']) ? trim(mb_strtolower($validated['q'])) : '';
        $limit = $validated['limit'] ?? 50;

        $query = Ingredient::query()
            ->with('catalogCategory')
            ->orderBy('name');

        if ($search !== '') {
            $query->where('name', 'like', '%'.$search.'%');
        }

        return IngredientResource::collection($query->limit($limit)->get());
    }

    public function store(IngredientRequest $request)
    {
        $payload = $this->toIngredientPayload($request->validated());

        return new IngredientResource(Ingredient::create($payload)->load('catalogCategory'));
    }

    public function show(Ingredient $ingredient)
    {
        return new IngredientResource($ingredient->load('catalogCategory'));
    }

    public function update(IngredientRequest $request, Ingredient $ingredient)
    {
        $payload = $this->toIngredientPayload($request->validated());
        $ingredient->update($payload);

        return new IngredientResource($ingredient->load('catalogCategory'));
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();

        return response()->json();
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    private function toIngredientPayload(array $validated): array
    {
        if (isset($validated['category_id']) && $validated['category_id'] !== null) {
            $categorySlug = Category::query()
                ->where('id', $validated['category_id'])
                ->value('slug');

            if (is_string($categorySlug)) {
                $validated['category'] = $categorySlug;
            }
        } elseif (isset($validated['category_slug'])) {
            $validated['category'] = $validated['category_slug'];
        }

        unset($validated['category_slug']);

        return $validated;
    }
}
