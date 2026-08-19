<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBuildingRequest;
use App\Http\Requests\UpdateBuildingRequest;
use App\Models\Building;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BuildingController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Building::class, 'building');
    }

    public function index(): Response
    {
        return Inertia::render('Buildings/Index', [
            'buildings' => Building::query()
                ->with('property:id,name')
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Buildings/Create', [
            'properties' => Property::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreBuildingRequest $request): RedirectResponse
    {
        Building::create($request->validated());

        return to_route('buildings.index');
    }

    public function edit(Building $building): Response
    {
        return Inertia::render('Buildings/Edit', [
            'building' => $building,
            'properties' => Property::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateBuildingRequest $request, Building $building): RedirectResponse
    {
        $building->update($request->validated());

        return to_route('buildings.index');
    }

    public function destroy(Building $building): RedirectResponse
    {
        $building->delete();

        return to_route('buildings.index');
    }
}
