<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Support\ListPaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = ListPaginator::resolvePerPage($request);

        return Inertia::render('Organization/Departments/Index', [
            'departments' => ListPaginator::paginate(
                Department::query()->with('company:id,name')->orderBy('name'),
                $request,
            )->through(fn (Department $department) => [
                'id' => $department->id,
                'name' => $department->name,
                'company_id' => $department->company_id,
                'company_name' => $department->company?->name,
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
        ]);

        Department::query()->create($data);

        return redirect()
            ->route('organization.departments.index')
            ->with('success', 'Department created.');
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'exists:org_companies,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $department->update($data);

        return redirect()
            ->route('organization.departments.index')
            ->with('success', 'Department updated.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $department->delete();

        return redirect()
            ->route('organization.departments.index')
            ->with('success', 'Department deleted.');
    }
}
