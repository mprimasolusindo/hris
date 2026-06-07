<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeEmergencyContact;
use App\Services\Employee\EmployeeEmergencyContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmergencyContactController extends Controller
{
    public function __construct(private EmployeeEmergencyContactService $service) {}

    public function store(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'relationship' => ['required', 'string', 'max:32'],
            'phone' => ['required', 'string', 'max:32'],
        ]);
        $this->service->createForEmployee($employee, $data);

        return back()->with('success', 'Emergency contact added.');
    }

    public function update(Request $request, Employee $employee, EmployeeEmergencyContact $emergencyContact): RedirectResponse
    {
        $this->authorize('update', $employee);
        abort_unless($emergencyContact->employee_id === $employee->id, 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'relationship' => ['required', 'string', 'max:32'],
            'phone' => ['required', 'string', 'max:32'],
        ]);
        $this->service->update($emergencyContact, $data);

        return back()->with('success', 'Emergency contact updated.');
    }

    public function destroy(Employee $employee, EmployeeEmergencyContact $emergencyContact): RedirectResponse
    {
        $this->authorize('update', $employee);
        abort_unless($emergencyContact->employee_id === $employee->id, 404);
        $this->service->delete($emergencyContact);

        return back()->with('success', 'Emergency contact removed.');
    }
}
