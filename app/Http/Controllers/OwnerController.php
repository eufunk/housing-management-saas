<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOwnerRequest;
use App\Http\Requests\UpdateOwnerRequest;
use App\Models\Owner;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OwnerController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Owner::class, 'owner');
    }

    public function index(): Response
    {
        return Inertia::render('Owners/Index', [
            'owners' => Owner::query()
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Owners/Create');
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
