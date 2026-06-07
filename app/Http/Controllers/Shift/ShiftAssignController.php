<?php

namespace App\Http\Controllers\Shift;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShiftAssignController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Shifts/Assign', [
            'shifts' => Shift::query()->orderBy('name')->get(['id', 'name']),
            'employees' => Employee::query()
                ->where('status', 'active')
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'employee_code']),
            'selectedShiftId' => $request->query('shift'),
            'defaultDate' => now()->toDateString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'shift_id' => ['required', 'exists:att_shifts,id'],
            'date' => ['required', 'date'],
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['integer', 'exists:emp_employees,id'],
        ]);

        foreach ($data['employee_ids'] as $employeeId) {
            EmployeeShift::query()->updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'date' => $data['date'],
                ],
                ['shift_id' => $data['shift_id']],
            );
        }

        return redirect()
            ->route('shifts.calendar', ['week' => $data['date']])
            ->with('success', 'Shift assignments saved.');
    }
}
