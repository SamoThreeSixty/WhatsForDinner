<?php

namespace App\Http\Controllers;

use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $search = isset($validated['q']) ? trim(mb_strtolower($validated['q'])) : '';
        $limit = $validated['limit'] ?? 50;

        $query = Tag::query()->orderBy('name');

        if ($search !== '') {
            $query->where('name', 'like', '%'.$search.'%');
        }

        return TagResource::collection($query->limit($limit)->get());
    }
}
