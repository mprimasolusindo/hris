<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VendorController extends Controller
{
    public function index(): Response
    {
        $vendors = Company::query()
            ->where('type', 'vendor')
            ->withCount('vendorEmployees')
            ->orderBy('name')
            ->get()
            ->map(fn (Company $vendor) => [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'placement_count' => $vendor->vendor_employees_count,
            ]);

        return Inertia::render('Vendors/Index', [
            'vendors' => $vendors,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Company::query()->create([
            'name' => $data['name'],
            'type' => 'vendor',
        ]);

        return redirect()->route('vendors.index')->with('success', 'Vendor created.');
    }

    public function show(Company $vendor): Response
    {
        abort_unless($vendor->type === 'vendor', 404);

        $vendor->load([
            'sites:id,company_id,name,location',
            'vendorEmployees.employee:id,full_name,employee_code,status,company_id',
            'vendorEmployees.employee.company:id,name',
        ]);

        return Inertia::render('Vendors/Show', [
            'vendor' => [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'sites' => $vendor->sites->map(fn ($site) => [
                    'id' => $site->id,
                    'name' => $site->name,
                    'location' => $site->location,
                ]),
                'placements' => $vendor->vendorEmployees->map(fn ($row) => [
                    'id' => $row->id,
                    'employee_id' => $row->employee_id,
                    'employee_name' => $row->employee?->full_name,
                    'employee_code' => $row->employee?->employee_code,
                    'employer_name' => $row->employee?->company?->name,
                    'status' => $row->employee?->status,
                ]),
            ],
        ]);
    }

    public function update(Request $request, Company $vendor): RedirectResponse
    {
        abort_unless($vendor->type === 'vendor', 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $vendor->update($data);

        return redirect()->route('vendors.show', $vendor)->with('success', 'Vendor updated.');
    }

    public function destroy(Company $vendor): RedirectResponse
    {
        abort_unless($vendor->type === 'vendor', 404);

        $vendor->delete();

        return redirect()->route('vendors.index')->with('success', 'Vendor deleted.');
    }
}
