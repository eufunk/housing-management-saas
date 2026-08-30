<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContractorRequest;
use App\Http\Requests\UpdateContractorRequest;
use App\Models\Contractor;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ContractorController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Contractor::class, 'contractor');
    }

    public function index(): Response
    {
        return Inertia::render('Contractors/Index', [
            'contractors' => Contractor::query()
                ->orderBy('company_name')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Contractors/Create');
    }

    public function store(StoreContractorRequest $request): RedirectResponse
    {
        Contractor::create($request->validated());

        return to_route('contractors.index');
    }

    public function edit(Contractor $contractor): Response
    {
        return Inertia::render('Contractors/Edit', [
            'contractor' => $contractor,
        ]);
    }

    public function update(UpdateContractorRequest $request, Contractor $contractor): RedirectResponse
    {
        $contractor->update($request->validated());

        return to_route('contractors.index');
    }

    public function destroy(Contractor $contractor): RedirectResponse
    {
        $contractor->delete();

        return to_route('contractors.index');
    }
}
