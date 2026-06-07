<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreEmployeeAllowanceRequest;
use App\Models\Employee;
use App\Models\EmployeeAllowance;
use App\Models\SalaryComponent;
use App\Services\Employee\EmployeeAllowanceService;
use Illuminate\Http\RedirectResponse;

class AllowanceController extends Controller
{
    public function __construct(private EmployeeAllowanceService $service) {}

    public function store(StoreEmployeeAllowanceRequest $request, Employee $employee): RedirectResponse
    {
        $data = $request->validated();
        if (! empty($data['component_id']) && empty($data['name'])) {
            $component = SalaryComponent::query()->find($data['component_id']);
            $data['name'] = $component?->name ?? $data['name'];
        }
        $this->service->createForEmployee($employee, $data);

        return back()->with('success', 'Allowance added.');
    }

    public function update(StoreEmployeeAllowanceRequest $request, Employee $employee, EmployeeAllowance $allowance): RedirectResponse
    {
        abort_unless($allowance->employee_id === $employee->id, 404);
        $this->service->update($allowance, $request->validated());

        return back()->with('success', 'Allowance updated.');
    }

    public function destroy(Employee $employee, EmployeeAllowance $allowance): RedirectResponse
    {
        $this->authorize('update', $employee);
        abort_unless($allowance->employee_id === $employee->id, 404);
        $this->service->delete($allowance);

        return back()->with('success', 'Allowance removed.');
    }
}
