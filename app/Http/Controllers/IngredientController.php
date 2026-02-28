<?php

namespace App\Http\Controllers;

use App\Http\Requests\IngredientRequest;
use App\Http\Resources\IngredientResource;
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

        $query = Ingredient::query()->orderBy('name');

        if ($search !== '') {
            $query->where('name', 'like', '%'.$search.'%');
        }

        return IngredientResource::collection($query->limit($limit)->get());
    }

    public function store(IngredientRequest $request)
    {
        return new IngredientResource(Ingredient::create($request->validated()));
    }

    public function show(Ingredient $ingredient)
    {
        return new IngredientResource($ingredient);
    }

    public function update(IngredientRequest $request, Ingredient $ingredient)
    {
        $ingredient->update($request->validated());

        return new IngredientResource($ingredient);
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();

        return response()->json();
    }
}
