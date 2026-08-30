<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Models\Owner;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PropertyController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Property::class, 'property');
    }

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        return Inertia::render('Properties/Index', [
            'properties' => Property::query()
                ->with('owner:id,name')
                ->when($search !== '', fn ($query) => $query->where(fn ($q) => $q
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('street', 'like', "%{$search}%")))
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString(),
            'filters' => ['search' => $search !== '' ? $search : null],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Properties/Create', [
            'owners' => Owner::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Property $property): Response
    {
        $property->load([
            'owner:id,ulid,name,email,phone',
            'buildings' => fn ($query) => $query->orderBy('name')->withCount('units'),
            'buildings.units' => fn ($query) => $query->orderBy('unit_number'),
        ]);

        return Inertia::render('Properties/Show', [
            'property' => $property,
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
