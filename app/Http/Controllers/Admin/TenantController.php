<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Saas/Tenants/Index', [
            'tenants' => Tenant::query()
                ->withCount(['subscriptions', 'companies', 'employees'])
                ->orderBy('name')
                ->get()
                ->map(fn (Tenant $tenant) => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'status' => $tenant->status,
                    'subscriptions_count' => $tenant->subscriptions_count,
                    'companies_count' => $tenant->companies_count,
                    'employees_count' => $tenant->employees_count,
                ]),
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
