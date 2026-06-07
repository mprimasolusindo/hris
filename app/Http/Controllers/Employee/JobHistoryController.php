<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JobHistoryController extends Controller
{
    public const EMPLOYMENT_TYPES = ['pkwt', 'pkwtt', 'outsourcing', 'magang'];

    public function store(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);

        $employee->jobs()->create($this->validateData($request, $employee));

        return back()->with('success', 'Job history added.');
    }

    public function update(Request $request, Employee $employee, EmployeeJob $job): RedirectResponse
    {
        $this->authorize('update', $employee);
        abort_unless($job->employee_id === $employee->id, 404);

        $job->update($this->validateData($request, $employee));

        return back()->with('success', 'Job history updated.');
    }

    public function destroy(Employee $employee, EmployeeJob $job): RedirectResponse
    {
        $this->authorize('update', $employee);
        abort_unless($job->employee_id === $employee->id, 404);

        $job->delete();

        return back()->with('success', 'Job history removed.');
    }

    /** @return array<string, mixed> */
    private function validateData(Request $request, Employee $employee): array
    {
        $data = $request->validate([
            'company_id' => ['required', 'exists:org_companies,id'],
            'department_id' => ['nullable', 'exists:org_departments,id'],
            'position_id' => ['nullable', 'exists:org_positions,id'],
            'manager_id' => ['nullable', 'exists:emp_employees,id'],
            'employment_type' => ['nullable', 'in:'.implode(',', self::EMPLOYMENT_TYPES)],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        if (($data['manager_id'] ?? null) == $employee->id) {
            $data['manager_id'] = null;
        }

        return $data;
    }
}
