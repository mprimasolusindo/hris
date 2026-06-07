<?php

namespace App\Http\Controllers\Employee;

use App\Actions\Employee\ArchiveEmployeeAction;
use App\Actions\Employee\RestoreEmployeeAction;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;

class ArchiveController extends Controller
{
    public function archive(Employee $employee, ArchiveEmployeeAction $action): RedirectResponse
    {
        $this->authorize('archive', $employee);
        $action($employee);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee archived.');
    }

    public function restore(int $employeeId, RestoreEmployeeAction $action): RedirectResponse
    {
        $employee = Employee::withTrashed()->findOrFail($employeeId);
        $this->authorize('restore', $employee);
        $action($employee);

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Employee restored.');
    }
}
