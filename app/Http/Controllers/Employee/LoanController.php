<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeLoan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function store(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);

        $data = $this->validateData($request);

        $employee->loans()->create($data);

        return back()->with('success', 'Loan added.');
    }

    public function update(Request $request, Employee $employee, EmployeeLoan $loan): RedirectResponse
    {
        $this->authorize('update', $employee);
        abort_unless($loan->employee_id === $employee->id, 404);

        $loan->update($this->validateData($request));

        return back()->with('success', 'Loan updated.');
    }

    public function destroy(Employee $employee, EmployeeLoan $loan): RedirectResponse
    {
        $this->authorize('update', $employee);
        abort_unless($loan->employee_id === $employee->id, 404);

        $loan->delete();

        return back()->with('success', 'Loan removed.');
    }

    /** @return array<string, mixed> */
    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'remaining_amount' => ['nullable', 'numeric', 'min:0'],
            'monthly_deduction' => ['required', 'numeric', 'min:0'],
        ]);

        $data['remaining_amount'] = $data['remaining_amount'] ?? $data['amount'];

        return $data;
    }
}
