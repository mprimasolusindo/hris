<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Support\ListPaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = ListPaginator::resolvePerPage($request);

        return Inertia::render('Admin/Saas/Tenants/Index', [
            'tenants' => ListPaginator::paginate(
                Tenant::query()
                    ->withCount(['subscriptions', 'companies', 'employees'])
                    ->orderBy('name'),
                $request,
            )->through(fn (Tenant $tenant) => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'status' => $tenant->status,
                'subscriptions_count' => $tenant->subscriptions_count,
                'companies_count' => $tenant->companies_count,
                'employees_count' => $tenant->employees_count,
            ]),
            'filters' => [
                'per_page' => is_string($perPage) ? $perPage : (string) $perPage,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        Tenant::query()->create($data);

        return redirect()->route('admin.saas.tenants.index')->with('success', 'Tenant created.');
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $this->validateData($request);

        $tenant->update($data);

        return redirect()->route('admin.saas.tenants.index')->with('success', 'Tenant updated.');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $tenant->delete();

        return redirect()->route('admin.saas.tenants.index')->with('success', 'Tenant deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,suspended,cancelled'],
        ]);
    }
}
