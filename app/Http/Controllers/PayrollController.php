<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\Site;
use App\Services\Payroll\PayrollCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PayrollController extends Controller
{
    public function __construct(private readonly PayrollCalculationService $service)
    {
    }

    public function index(Request $request): Response|HttpResponse
    {
        $month = (string) $request->query('month', 'all');
        $year = (int) $request->query('year', now()->year);
        $employeeId = $request->query('employee_id');
        $companyId = $request->query('company_id');
        $siteId = $request->query('site_id');
        $status = (string) $request->query('status', 'all');

        $query = Payroll::query()
            ->with([
                'employee',
                'employee.company',
                'employee.siteAssignments' => fn ($q) => $q->with('site')->latest('start_date'),
            ])
            ->where('period_year', $year)
            ->when($month !== 'all', fn ($q) => $q->where('period_month', (int) $month))
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($employeeId, fn ($q) => $q->where('employee_id', $employeeId))
            ->when($companyId, fn ($q) => $q->whereHas('employee', fn ($eq) => $eq->where('company_id', $companyId)))
            ->when($siteId, fn ($q) => $q->whereHas('employee.siteAssignments', fn ($eq) => $eq->where('site_id', $siteId)));

        if ($request->query('export') === 'csv') {
            return $this->exportCsv($query->get());
        }

        $summaryStats = (clone $query)
            ->selectRaw("
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
                SUM(CASE WHEN status IN ('generated', 'reviewed', 'approved') THEN 1 ELSE 0 END) as generated,
                SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid,
                COALESCE(SUM(net_salary), 0) as total_amount
            ")
            ->first();

        $summary = [
            'draft' => (int) $summaryStats->draft,
            'generated' => (int) $summaryStats->generated,
            'paid' => (int) $summaryStats->paid,
            'total_amount' => (float) $summaryStats->total_amount,
        ];

        $payrolls = $query->latest()->paginate(20)->withQueryString();

        $payrolls->getCollection()->transform(function (Payroll $payroll) {
            return [
                'id' => $payroll->id,
                'employee_name' => $payroll->employee?->full_name,
                'employee_code' => $payroll->employee?->employee_code,
                'company_name' => $payroll->employee?->company?->name,
                'site_name' => $payroll->employee?->siteAssignments?->first()?->site?->name,
                'period_month' => $payroll->period_month,
                'period_year' => $payroll->period_year,
                'gross_salary' => $payroll->gross_salary,
                'total_deduction' => $payroll->total_deduction,
                'net_salary' => $payroll->net_salary,
                'status' => $payroll->status,
            ];
        });

        return Inertia::render('Payroll/Index', [
            'payrolls' => $payrolls,
            'filters' => [
                'month' => $month,
                'year' => $year,
                'employee_id' => $employeeId ? (string) $employeeId : '',
                'company_id' => $companyId ? (string) $companyId : '',
                'site_id' => $siteId ? (string) $siteId : '',
                'status' => $status,
            ],
            'summary' => $summary,
            'employees' => Employee::query()->orderBy('full_name')->get(['id', 'full_name', 'employee_code']),
            'companies' => \App\Models\Company::query()->orderBy('name')->get(['id', 'name']),
            'sites' => Site::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'scope' => ['nullable', 'in:all'],
            'employee_id' => ['required_without:scope', 'nullable', 'exists:emp_employees,id'],
            'period_month' => ['required', 'integer', 'between:1,12'],
            'period_year' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        $month = (int) $data['period_month'];
        $year = (int) $data['period_year'];

        if (($data['scope'] ?? null) === 'all') {
            $generated = 0;
            $skipped = 0;

            $employees = Employee::query()
                ->where('status', 'active')
                ->orderBy('id')
                ->get();

            foreach ($employees as $employee) {
                $baseSalary = $this->service->resolveBaseSalaryForPeriod($employee, $month, $year);
                if ($baseSalary === null) {
                    $skipped++;
                    continue;
                }
                $this->service->generate($employee, $month, $year, $baseSalary);
                $generated++;
            }

            return redirect()
                ->route('payroll.index')
                ->with('success', "Generated {$generated} payroll(s). Skipped {$skipped} (no active contract).");
        }

        $employee = Employee::query()->findOrFail($data['employee_id']);
        $baseSalary = $this->service->resolveBaseSalaryForPeriod($employee, $month, $year);

        if ($baseSalary === null) {
            throw ValidationException::withMessages([
                'employee_id' => 'No active employment contract with base salary found for this payroll period.',
            ]);
        }

        $payroll = $this->service->generate($employee, $month, $year, $baseSalary);

        return redirect()
            ->route('payroll.show', $payroll)
            ->with('success', 'Payroll generated.');
    }

    public function update(Request $request, Payroll $payroll): RedirectResponse
    {
        $data = $request->validate([
            'approval_notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'in:draft,generated,reviewed,approved,paid'],
        ]);

        $patch = ['approval_notes' => $data['approval_notes'] ?? null];

        if (! empty($data['status'])) {
            $patch['status'] = $data['status'];
        }

        $payroll->update($patch);

        return redirect()
            ->route('payroll.show', $payroll)
            ->with('success', 'Payroll updated.');
    }

    public function show(Payroll $payroll): Response
    {
        $payroll->load([
            'employee',
            'employee.company',
            'employee.siteAssignments.site',
            'items',
        ]);

        $attendanceSource = $payroll->employee->attendances()
            ->whereYear('clock_in', $payroll->period_year)
            ->whereMonth('clock_in', $payroll->period_month)
            ->get();

        $attendanceSummary = [
            'days' => $attendanceSource->whereIn('status', ['present', 'late'])->count(),
            'leave_days' => $attendanceSource->whereIn('status', ['leave', 'sick'])->count(),
            'overtime_hours' => round((float) Overtime::query()
                ->where('employee_id', $payroll->employee_id)
                ->where('status', 'approved')
                ->whereYear('date', $payroll->period_year)
                ->whereMonth('date', $payroll->period_month)
                ->sum('hours'), 1),
        ];

        return Inertia::render('Payroll/Show', [
            'payroll' => [
                'id' => $payroll->id,
                'employee_name' => $payroll->employee?->full_name,
                'employee_code' => $payroll->employee?->employee_code,
                'company_name' => $payroll->employee?->company?->name,
                'site_name' => $payroll->employee?->siteAssignments?->first()?->site?->name,
                'period_month' => $payroll->period_month,
                'period_year' => $payroll->period_year,
                'period_label' => sprintf(
                    '%02d/%d',
                    $payroll->period_month,
                    $payroll->period_year
                ),
                'gross_salary' => $payroll->gross_salary,
                'total_deduction' => $payroll->total_deduction,
                'net_salary' => $payroll->net_salary,
                'status' => $payroll->status,
                'approval_notes' => $payroll->approval_notes,
            ],
            'items' => $payroll->items->map(fn ($item) => [
                'id' => $item->id,
                'type' => $item->type,
                'name' => $item->component_name,
                'amount' => $item->amount,
            ]),
            'attendanceSummary' => $attendanceSummary,
        ]);
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'payroll_ids' => ['required', 'array', 'min:1'],
            'payroll_ids.*' => ['integer', 'exists:pay_payrolls,id'],
            'action' => ['required', 'in:reviewed,approved,paid,draft'],
            'approval_notes' => ['nullable', 'string'],
        ]);

        $payrolls = Payroll::query()
            ->whereIn('id', $data['payroll_ids'])
            ->get();

        $fromStatusRules = [
            'reviewed' => ['draft', 'generated'],
            'approved' => ['reviewed'],
            'paid' => ['approved'],
            'draft' => ['reviewed', 'approved'],
        ];

        $allowedFrom = $fromStatusRules[$data['action']];
        if ($payrolls->contains(fn ($p) => ! in_array($p->status, $allowedFrom, true))) {
            return back()->with('success', 'Some selected rows have invalid status transition.');
        }

        $patch = ['status' => $data['action']];
        $now = now();
        $userId = auth()->id();

        if ($data['action'] === 'reviewed') {
            $patch['reviewed_by'] = $userId;
            $patch['reviewed_at'] = $now;
        } elseif ($data['action'] === 'approved') {
            $patch['approved_by'] = $userId;
            $patch['approved_at'] = $now;
        } elseif ($data['action'] === 'paid') {
            $patch['paid_by'] = $userId;
            $patch['paid_at'] = $now;
        } elseif ($data['action'] === 'draft') {
            $patch['reviewed_by'] = null;
            $patch['reviewed_at'] = null;
            $patch['approved_by'] = null;
            $patch['approved_at'] = null;
        }

        if (! empty($data['approval_notes'])) {
            $patch['approval_notes'] = $data['approval_notes'];
        }

        Payroll::query()
            ->whereIn('id', $data['payroll_ids'])
            ->update($patch);

        return redirect()
            ->route('payroll.index', $request->only(['month', 'year', 'employee_id', 'company_id', 'site_id', 'status']))
            ->with('success', 'Payroll bulk action applied.');
    }

    private function exportCsv($payrolls): HttpResponse
    {
        $rows = [['Payroll ID', 'Employee', 'Code', 'Period', 'Gross', 'Deduction', 'Net', 'Status']];

        foreach ($payrolls as $payroll) {
            $rows[] = [
                (string) $payroll->id,
                (string) optional($payroll->employee)->full_name,
                (string) optional($payroll->employee)->employee_code,
                sprintf('%04d-%02d', $payroll->period_year, $payroll->period_month),
                (string) $payroll->gross_salary,
                (string) $payroll->total_deduction,
                (string) $payroll->net_salary,
                (string) $payroll->status,
            ];
        }

        $output = collect($rows)->map(function ($row) {
            return collect($row)->map(function ($value) {
                $escaped = str_replace('"', '""', (string) $value);
                return "\"{$escaped}\"";
            })->implode(',');
        })->implode(PHP_EOL);

        return response($output, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="payroll-export.csv"',
        ]);
    }
}

