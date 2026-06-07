<?php

namespace App\Http\Controllers\Outsourcing;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\VendorEmployee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlacementController extends Controller
{
    public function index(Request $request): Response
    {
        $vendorId = (string) $request->query('vendor_id', '');

        $placements = VendorEmployee::query()
            ->with([
                'vendor:id,name',
                'employee:id,full_name,employee_code,status,company_id',
                'employee.company:id,name',
                'employee.siteAssignments.site:id,name',
            ])
            ->when($vendorId !== '', fn ($q) => $q->where('vendor_id', $vendorId))
            ->latest()
            ->get()
            ->map(fn (VendorEmployee $row) => [
                'id' => $row->id,
                'vendor_id' => $row->vendor_id,
                'vendor_name' => $row->vendor?->name,
                'employee_id' => $row->employee_id,
                'employee_name' => $row->employee?->full_name,
                'employee_code' => $row->employee?->employee_code,
                'employer_name' => $row->employee?->company?->name,
                'site_name' => $row->employee?->siteAssignments->first()?->site?->name,
                'status' => $row->employee?->status === 'active' ? 'active' : 'ended',
                'created_at' => $row->created_at?->toDateTimeString(),
            ]);

        return Inertia::render('Outsourcing/Placements/Index', [
            'placements' => $placements,
            'filters' => ['vendor_id' => $vendorId],
            'vendors' => Company::query()->where('type', 'vendor')->orderBy('name')->get(['id', 'name']),
            'employees' => Employee::query()
                ->where('status', 'active')
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'employee_code']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vendor_id' => ['required', 'exists:org_companies,id'],
            'employee_id' => ['required', 'exists:emp_employees,id'],
        ]);

        $vendor = Company::query()->findOrFail($data['vendor_id']);
        abort_unless($vendor->type === 'vendor', 422);

        VendorEmployee::query()->updateOrCreate(
            [
                'employee_id' => $data['employee_id'],
                'vendor_id' => $data['vendor_id'],
            ],
        );

        return redirect()->route('outsourcing.index')->with('success', 'Placement assigned.');
    }

    public function update(Request $request, VendorEmployee $placement): RedirectResponse
    {
        $data = $request->validate([
            'vendor_id' => ['required', 'exists:org_companies,id'],
            'employee_id' => ['required', 'exists:emp_employees,id'],
        ]);

        $vendor = Company::query()->findOrFail($data['vendor_id']);
        abort_unless($vendor->type === 'vendor', 422);

        $placement->update([
            'vendor_id' => $data['vendor_id'],
            'employee_id' => $data['employee_id'],
        ]);

        return redirect()->route('outsourcing.index')->with('success', 'Placement updated.');
    }

    public function destroy(VendorEmployee $placement): RedirectResponse
    {
        $placement->delete();

        return redirect()->route('outsourcing.index')->with('success', 'Placement removed.');
    }
}
