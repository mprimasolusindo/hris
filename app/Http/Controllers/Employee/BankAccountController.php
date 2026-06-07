<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeBankAccount;
use App\Services\Employee\EmployeeBankAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function __construct(private EmployeeBankAccountService $service) {}

    public function store(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);
        $data = $request->validate([
            'bank_name' => ['required', 'string', 'max:64'],
            'account_number' => ['required', 'string', 'max:64'],
            'account_holder' => ['required', 'string', 'max:255'],
            'is_primary' => ['boolean'],
        ]);
        $this->service->createForEmployee($employee, $data);

        return back()->with('success', 'Bank account added.');
    }

    public function update(Request $request, Employee $employee, EmployeeBankAccount $bankAccount): RedirectResponse
    {
        $this->authorize('update', $employee);
        abort_unless($bankAccount->employee_id === $employee->id, 404);
        $data = $request->validate([
            'bank_name' => ['required', 'string', 'max:64'],
            'account_number' => ['required', 'string', 'max:64'],
            'account_holder' => ['required', 'string', 'max:255'],
            'is_primary' => ['boolean'],
        ]);
        $this->service->update($bankAccount, $data);

        return back()->with('success', 'Bank account updated.');
    }

    public function destroy(Employee $employee, EmployeeBankAccount $bankAccount): RedirectResponse
    {
        $this->authorize('update', $employee);
        abort_unless($bankAccount->employee_id === $employee->id, 404);
        $this->service->delete($bankAccount);

        return back()->with('success', 'Bank account removed.');
    }
}
