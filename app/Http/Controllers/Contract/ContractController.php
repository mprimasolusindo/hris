<?php

namespace App\Http\Controllers\Contract;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmploymentContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContractController extends Controller
{
    public const CONTRACT_TYPES = ['pkwt', 'pkwtt', 'outsourcing', 'magang'];

    public function index(Request $request): Response
    {
        $type = (string) $request->query('type', '');
        $status = (string) $request->query('status', '');

        $contracts = EmploymentContract::query()
            ->with('employee:id,full_name,employee_code')
            ->when($type !== '', fn ($q) => $q->where('contract_type', $type))
            ->orderByDesc('start_date')
            ->get()
            ->map(fn (EmploymentContract $contract) => $this->serializeContract($contract))
            ->filter(function (array $row) use ($status) {
                if ($status === '') {
                    return true;
                }
                if ($status === 'expiring') {
                    return $row['is_expiring'];
                }

                return $row['derived_status'] === $status;
            })
            ->values();

        return Inertia::render('Contracts/Index', [
            'contracts' => $contracts,
            'filters' => ['type' => $type, 'status' => $status],
            'contractTypes' => self::CONTRACT_TYPES,
            'employees' => Employee::query()
                ->where('status', 'active')
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'employee_code']),
            'expiringCount' => $contracts->where('is_expiring', true)->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:emp_employees,id'],
            'contract_type' => ['required', 'string', 'in:'.implode(',', self::CONTRACT_TYPES)],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'salary_base' => ['required', 'numeric', 'min:0'],
        ]);

        $contract = EmploymentContract::query()->create($data);

        return redirect()
            ->route('contracts.show', $contract)
            ->with('success', 'Contract created.');
    }

    public function show(EmploymentContract $contract): Response
    {
        $contract->load('employee:id,full_name,employee_code,company_id');

        return Inertia::render('Contracts/Show', [
            'contract' => $this->serializeContract($contract),
        ]);
    }

    public function update(Request $request, EmploymentContract $contract): RedirectResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:emp_employees,id'],
            'contract_type' => ['required', 'string', 'in:'.implode(',', self::CONTRACT_TYPES)],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'salary_base' => ['required', 'numeric', 'min:0'],
        ]);

        $contract->update($data);

        return redirect()
            ->route('contracts.show', $contract)
            ->with('success', 'Contract updated.');
    }

    public function destroy(EmploymentContract $contract): RedirectResponse
    {
        $contract->delete();

        return redirect()
            ->route('contracts.index')
            ->with('success', 'Contract deleted.');
    }

    private function serializeContract(EmploymentContract $contract): array
    {
        $today = now()->startOfDay();
        $end = $contract->end_date;
        $derivedStatus = 'active';
        $isExpiring = false;

        if ($end && $end->lt($today)) {
            $derivedStatus = 'expired';
        } elseif ($end && $end->lte($today->copy()->addDays(30))) {
            $isExpiring = true;
        }

        return [
            'id' => $contract->id,
            'employee_id' => $contract->employee_id,
            'employee_name' => $contract->employee?->full_name,
            'employee_code' => $contract->employee?->employee_code,
            'contract_type' => $contract->contract_type,
            'start_date' => $contract->start_date?->toDateString(),
            'end_date' => $contract->end_date?->toDateString(),
            'salary_base' => $contract->salary_base,
            'derived_status' => $derivedStatus,
            'is_expiring' => $isExpiring,
            'days_until_end' => $end ? $today->diffInDays($end, false) : null,
        ];
    }
}
