<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreEmployeeIdentityRequest;
use App\Models\Employee;
use App\Services\Employee\EmployeeIdentityService;
use Illuminate\Http\RedirectResponse;

class IdentityController extends Controller
{
    public function __construct(private EmployeeIdentityService $service) {}

    public function store(StoreEmployeeIdentityRequest $request, Employee $employee): RedirectResponse
    {
        $this->service->upsertForEmployee($employee, $request->validated());

        return back()->with('success', 'Identity saved.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);
        if ($employee->identity) {
            $this->service->delete($employee->identity);
        }

        return back()->with('success', 'Identity removed.');
    }
}
