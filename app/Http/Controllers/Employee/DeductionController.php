<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreEmployeeDeductionRequest;
use App\Models\Employee;
use App\Models\EmployeeDeduction;
use App\Models\SalaryComponent;
use App\Services\Employee\EmployeeDeductionService;
use Illuminate\Http\RedirectResponse;

class DeductionController extends Controller
{
    public function __construct(private EmployeeDeductionService $service) {}

    public function store(StoreEmployeeDeductionRequest $request, Employee $employee): RedirectResponse
    {
        $data = $request->validated();
        if (! empty($data['component_id']) && empty($data['name'])) {
            $component = SalaryComponent::query()->find($data['component_id']);
            $data['name'] = $component?->name ?? $data['name'];
        }
        $this->service->createForEmployee($employee, $data);

        return back()->with('success', 'Deduction added.');
    }

    public function update(StoreEmployeeDeductionRequest $request, Employee $employee, EmployeeDeduction $deduction): RedirectResponse
    {
        abort_unless($deduction->employee_id === $employee->id, 404);
        $this->service->update($deduction, $request->validated());

        return back()->with('success', 'Deduction updated.');
    }

    public function destroy(Employee $employee, EmployeeDeduction $deduction): RedirectResponse
    {
        $this->authorize('update', $employee);
        abort_unless($deduction->employee_id === $employee->id, 404);
        $this->service->delete($deduction);

        return back()->with('success', 'Deduction removed.');
    }
}
