<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Site;
use App\Support\ListPaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SiteController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = ListPaginator::resolvePerPage($request);

        return Inertia::render('Organization/Sites/Index', [
            'sites' => ListPaginator::paginate(
                Site::query()->with('company:id,name')->orderBy('name'),
                $request,
            )->through(fn (Site $site) => [
                'id' => $site->id,
                'name' => $site->name,
                'location' => $site->location,
                'company_id' => $site->company_id,
                'company_name' => $site->company?->name,
            ]),
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'filters' => [
                'per_page' => is_string($perPage) ? $perPage : (string) $perPage,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'exists:org_companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        Site::query()->create($data);

        return redirect()
            ->route('organization.sites.index')
            ->with('success', 'Site created.');
    }

    public function update(Request $request, Site $site): RedirectResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'exists:org_companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        $site->update($data);

        return redirect()
            ->route('organization.sites.index')
            ->with('success', 'Site updated.');
    }

    public function destroy(Site $site): RedirectResponse
    {
        $site->delete();

        return redirect()
            ->route('organization.sites.index')
            ->with('success', 'Site deleted.');
    }
}
