<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOwnerRequest;
use App\Http\Requests\UpdateOwnerRequest;
use App\Models\Owner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OwnerController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Owner::class, 'owner');
    }

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        return Inertia::render('Owners/Index', [
            'owners' => Owner::query()
                ->when($search !== '', fn ($query) => $query->where(fn ($q) => $q
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")))
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString(),
            'filters' => ['search' => $search !== '' ? $search : null],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Owners/Create');
    }

    public function show(Owner $owner): Response
    {
        $owner->load(['properties' => fn ($query) => $query->orderBy('name')]);

        return Inertia::render('Owners/Show', [
            'owner' => $owner,
        ]);
    }

    public function store(StoreOwnerRequest $request): RedirectResponse
    {
        Owner::create($request->validated());

        return to_route('owners.index');
    }

    public function edit(Owner $owner): Response
    {
        return Inertia::render('Owners/Edit', [
            'owner' => $owner,
        ]);
    }

    public function update(UpdateOwnerRequest $request, Owner $owner): RedirectResponse
    {
        $owner->update($request->validated());

        return to_route('owners.index');
    }

    public function destroy(Owner $owner): RedirectResponse
    {
        $owner->delete();

        return to_route('owners.index');
    }
}
