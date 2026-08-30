<?php

namespace App\Http\Controllers;

use App\Enums\UnitStatus;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Building;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UnitController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Unit::class, 'unit');
    }

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        return Inertia::render('Units/Index', [
            'units' => Unit::query()
                ->with('building:id,name')
                ->when($search !== '', fn ($query) => $query->where(fn ($q) => $q
                    ->where('unit_number', 'like', "%{$search}%")
                    ->orWhereHas('building', fn ($b) => $b->where('name', 'like', "%{$search}%"))))
                ->when(
                    $status !== '' && UnitStatus::tryFrom($status) !== null,
                    fn ($query) => $query->where('status', $status)
                )
                ->orderBy('unit_number')
                ->paginate(15)
                ->withQueryString(),
            'filters' => ['search' => $search !== '' ? $search : null, 'status' => $status !== '' ? $status : null],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Units/Create', [
            'buildings' => Building::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        Unit::create($request->validated());

        return to_route('units.index');
    }

    public function edit(Unit $unit): Response
    {
        return Inertia::render('Units/Edit', [
            'unit' => $unit,
            'buildings' => Building::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit->update($request->validated());

        return to_route('units.index');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $unit->delete();

        return to_route('units.index');
    }
}
