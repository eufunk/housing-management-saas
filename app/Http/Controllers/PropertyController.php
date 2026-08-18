<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Models\Owner;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PropertyController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Property::class, 'property');
    }

    public function index(): Response
    {
        return Inertia::render('Properties/Index', [
            'properties' => Property::query()
                ->with('owner:id,name')
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Properties/Create', [
            'owners' => Owner::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StorePropertyRequest $request): RedirectResponse
    {
        Property::create($request->validated());

        return to_route('properties.index');
    }

    public function edit(Property $property): Response
    {
        return Inertia::render('Properties/Edit', [
            'property' => $property,
            'owners' => Owner::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdatePropertyRequest $request, Property $property): RedirectResponse
    {
        $property->update($request->validated());

        return to_route('properties.index');
    }

    public function destroy(Property $property): RedirectResponse
    {
        $property->delete();

        return to_route('properties.index');
    }
}
