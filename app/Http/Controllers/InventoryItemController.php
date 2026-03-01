<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryItemRequest;
use App\Http\Resources\InventoryItemResource;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    public function metadata()
    {
        return response()->json([
            'locations' => InventoryItem::query()
                ->whereNotNull('location')
                ->select('location')
                ->distinct()
                ->orderBy('location')
                ->pluck('location')
                ->values(),
        ]);
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $search = isset($validated['q']) ? trim(mb_strtolower($validated['q'])) : '';
        $limit = $validated['limit'] ?? 100;

        $query = InventoryItem::query()
            ->with('product.ingredient')
            ->latest('created_at');

        if ($search !== '') {
            $query->where(function ($nested) use ($search) {
                $nested->where('category', 'like', '%'.$search.'%')
                    ->orWhere('location', 'like', '%'.$search.'%')
                    ->orWhereHas('product', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', '%'.$search.'%')
                            ->orWhere('company', 'like', '%'.$search.'%');
                    });
            });
        }

        return InventoryItemResource::collection($query->limit($limit)->get());
    }

    public function store(InventoryItemRequest $request)
    {
        $inventoryItem = InventoryItem::query()->create($request->validated());

        return new InventoryItemResource($inventoryItem->load('product.ingredient'));
    }

    public function show(InventoryItem $inventoryItem)
    {
        return new InventoryItemResource($inventoryItem->load('product.ingredient'));
    }

    public function update(InventoryItemRequest $request, InventoryItem $inventoryItem)
    {
        $inventoryItem->update($request->validated());

        return new InventoryItemResource($inventoryItem->load('product.ingredient'));
    }

    public function destroy(InventoryItem $inventoryItem)
    {
        $inventoryItem->delete();

        return response()->json();
    }
}
