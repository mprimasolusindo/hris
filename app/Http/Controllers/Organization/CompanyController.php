<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Organization/Companies/Index', [
            'companies' => Company::query()
                ->orderBy('name')
                ->get(['id', 'name', 'type']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:32'],
        ]);

        Company::query()->create($data);

        return redirect()
            ->route('organization.companies.index')
            ->with('success', 'Company created.');
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:32'],
        ]);

        $company->update($data);

        return redirect()
            ->route('organization.companies.index')
            ->with('success', 'Company updated.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect()
            ->route('organization.companies.index')
            ->with('success', 'Company deleted.');
    }
}
