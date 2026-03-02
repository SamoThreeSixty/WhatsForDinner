<?php

namespace App\Http\Controllers;

use App\Enums\UnitType;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function metadata()
    {
        return response()->json([
            'unit_types' => array_map(
                static fn (UnitType $type) => [
                    'value' => $type->value,
                    'label' => ucfirst($type->value),
                ],
                UnitType::cases()
            ),
            'units_by_type' => [
                UnitType::Count->value => ['each', 'pack'],
                UnitType::Mass->value => ['g', 'kg'],
                UnitType::Volume->value => ['ml', 'l'],
            ],
        ]);
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'ingredient_id' => ['nullable', 'integer', 'exists:ingredients,id'],
            'ingredient_slug' => ['nullable', 'string', 'max:255', 'exists:ingredients,slug'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $search = isset($validated['q']) ? trim(mb_strtolower($validated['q'])) : '';
        $limit = $validated['limit'] ?? 50;

        $query = Product::query()
            ->with('ingredient')
            ->orderBy('name');

        if ($search !== '') {
            $query->where('name', 'like', '%'.$search.'%');
        }

        if (isset($validated['ingredient_id'])) {
            $query->where('ingredient_id', $validated['ingredient_id']);
        }

        if (isset($validated['ingredient_slug'])) {
            $query->whereHas('ingredient', function ($ingredientQuery) use ($validated) {
                $ingredientQuery->where('slug', $validated['ingredient_slug']);
            });
        }

        return ProductResource::collection($query->limit($limit)->get());
    }

    public function store(ProductRequest $request)
    {
        $payload = $request->validated();
        unset($payload['ingredient_slug']);

        return new ProductResource(Product::create($payload)->load('ingredient'));
    }

    public function show(Product $product)
    {
        return new ProductResource($product->load('ingredient'));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $payload = $request->validated();
        unset($payload['ingredient_slug']);
        $product->update($payload);

        return new ProductResource($product->load('ingredient'));
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json();
    }
}
