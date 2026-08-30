<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTenantRequest;
use App\Http\Requests\UpdateTenantRequest;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Tenant::class, 'tenant');
    }

    public function index(): Response
    {
        return Inertia::render('Tenants/Index', [
            'tenants' => Tenant::query()
                ->orderBy('name')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Tenants/Create');
    }

    public function store(StoreTenantRequest $request): RedirectResponse
    {
        Tenant::create($request->validated());

        return to_route('tenants.index');
    }

    public function edit(Tenant $tenant): Response
    {
        return Inertia::render('Tenants/Edit', [
            'tenant' => $tenant,
        ]);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $tenant->update($request->validated());

        return to_route('tenants.index');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $tenant->delete();

        return to_route('tenants.index');
    }
}
