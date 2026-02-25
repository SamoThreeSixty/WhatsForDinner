<?php

namespace App\Http\Controllers;

use App\Http\Requests\HouseholdRequest;
use App\Http\Resources\HouseholdResource;
use App\Models\Household;

class HouseholdController extends Controller
{
    public function index()
    {
        return HouseholdResource::collection(Household::all());
    }

    public function store(HouseholdRequest $request)
    {
        return new HouseholdResource(Household::create($request->validated()));
    }

    public function show(Household $household)
    {
        return new HouseholdResource($household);
    }

    public function update(HouseholdRequest $request, Household $household)
    {
        $household->update($request->validated());

        return new HouseholdResource($household);
    }

    public function destroy(Household $household)
    {
        $household->delete();

        return response()->json();
    }
}
