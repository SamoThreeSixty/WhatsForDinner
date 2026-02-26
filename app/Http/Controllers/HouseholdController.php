<?php

namespace App\Http\Controllers;

use App\Http\Requests\HouseholdRequest;
use App\Http\Resources\HouseholdResource;
use App\Models\Household;
use Illuminate\Support\Str;

class HouseholdController extends Controller
{
    public function index()
    {
        return HouseholdResource::collection(Household::all());
    }

    public function store(HouseholdRequest $request)
    {
        $validated = $request->validated();

        return new HouseholdResource(Household::create([
            'name' => $validated['name'],
            'slug' => (string) Str::uuid(),
            'locale' => $validated['locale'] ?? 'en',
            'currency' => strtoupper($validated['currency'] ?? 'GBP'),
            'new_members' => true,
        ]));
    }

    public function show(Household $household)
    {
        return new HouseholdResource($household);
    }

    public function update(HouseholdRequest $request, Household $household)
    {
        $validated = $request->validated();

        $household->update([
            'name' => $validated['name'],
            'locale' => $validated['locale'] ?? $household->locale,
            'currency' => strtoupper($validated['currency'] ?? $household->currency),
        ]);

        return new HouseholdResource($household);
    }

    public function destroy(Household $household)
    {
        $household->delete();

        return response()->json();
    }
}
