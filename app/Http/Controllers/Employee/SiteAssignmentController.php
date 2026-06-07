<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeSite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteAssignmentController extends Controller
{
    public function store(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);

        $employee->siteAssignments()->create($this->validateData($request));

        return back()->with('success', 'Site assignment added.');
    }

    public function update(Request $request, Employee $employee, EmployeeSite $siteAssignment): RedirectResponse
    {
        $this->authorize('update', $employee);
        abort_unless($siteAssignment->employee_id === $employee->id, 404);

        $siteAssignment->update($this->validateData($request));

        return back()->with('success', 'Site assignment updated.');
    }

    public function destroy(Employee $employee, EmployeeSite $siteAssignment): RedirectResponse
    {
        $this->authorize('update', $employee);
        abort_unless($siteAssignment->employee_id === $employee->id, 404);

        $siteAssignment->delete();

        return back()->with('success', 'Site assignment removed.');
    }

    /** @return array<string, mixed> */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'site_id' => ['required', 'exists:org_sites,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
    }
}
