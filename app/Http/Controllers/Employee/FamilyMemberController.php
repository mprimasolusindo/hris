<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeFamilyMember;
use App\Services\Employee\EmployeeFamilyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FamilyMemberController extends Controller
{
    public function __construct(private EmployeeFamilyService $service) {}

    public function store(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'relationship' => ['required', 'string', 'max:32'],
            'birth_date' => ['nullable', 'date'],
            'is_dependent' => ['boolean'],
        ]);
        $this->service->createForEmployee($employee, $data);

        return back()->with('success', 'Family member added.');
    }

    public function update(Request $request, Employee $employee, EmployeeFamilyMember $familyMember): RedirectResponse
    {
        $this->authorize('update', $employee);
        abort_unless($familyMember->employee_id === $employee->id, 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'relationship' => ['required', 'string', 'max:32'],
            'birth_date' => ['nullable', 'date'],
            'is_dependent' => ['boolean'],
        ]);
        $this->service->update($familyMember, $data);

        return back()->with('success', 'Family member updated.');
    }

    public function destroy(Employee $employee, EmployeeFamilyMember $familyMember): RedirectResponse
    {
        $this->authorize('update', $employee);
        abort_unless($familyMember->employee_id === $employee->id, 404);
        $this->service->delete($familyMember);

        return back()->with('success', 'Family member removed.');
    }
}
