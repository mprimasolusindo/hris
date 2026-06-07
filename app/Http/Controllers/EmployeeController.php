<?php

namespace App\Http\Controllers;

use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Http\Resources\Employee\EmployeeResource;
use App\Http\Resources\Employee\EmployeeSummaryResource;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\SalaryComponent;
use App\Models\Site;
use App\Models\User;
use App\Services\Employee\EmployeeQueryService;
use App\Services\Employee\EmployeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function __construct(
        private EmployeeService $employeeService,
        private EmployeeQueryService $queryService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Employee::class);

        $employees = $this->queryService->paginate($request);

        return Inertia::render('Employees/Index', [
            'employees' => EmployeeSummaryResource::collection($employees),
            'filters' => [
                'search' => (string) $request->query('search', ''),
                'status' => (string) $request->query('status', ''),
            ],
            'statusOptions' => ['active', 'resigned', 'terminated', 'retired', 'suspended'],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Employee::class);

        return Inertia::render('Employees/Create', [
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'statusOptions' => ['active', 'resigned', 'terminated', 'retired', 'suspended'],
            'salaryComponents' => $this->catalogComponents(),
        ]);
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $employee = $this->employeeService->create($request->validated());

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Employee created.');
    }

    public function show(Employee $employee): Response
    {
        $this->authorize('view', $employee);

        $employee = $this->queryService->loadForShow($employee);

        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfMonth();
        $attendanceRows = Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('clock_in', [$periodStart, $periodEnd])
            ->get();

        $attendanceSummary = [
            'attendance_days' => $attendanceRows->whereIn('status', ['present', 'late'])->count(),
            'leave_days' => $attendanceRows->whereIn('status', ['leave', 'sick'])->count(),
            'absent_days' => $attendanceRows->where('status', 'absent')->count(),
        ];

        $estimatedGross = $employee->allowances
            ->where('status', 'active')
            ->sum('amount');

        $estimatedDeductions = $employee->deductions
            ->where('status', 'active')
            ->sum('value');

        return Inertia::render('Employees/Show', [
            'employee' => (new EmployeeResource($employee))->resolve(),
            'attendanceSummary' => $attendanceSummary,
            'payrollEstimate' => [
                'gross_allowances' => $estimatedGross,
                'total_deductions' => $estimatedDeductions,
                'estimated_net' => max(0, $estimatedGross - $estimatedDeductions),
            ],
            'salaryComponents' => $this->catalogComponents(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'statusOptions' => ['active', 'resigned', 'terminated', 'retired', 'suspended'],
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'positions' => Position::query()->orderBy('name')->get(['id', 'name']),
            'sites' => Site::query()->orderBy('name')->get(['id', 'name']),
            'managers' => Employee::query()
                ->where('id', '!=', $employee->id)
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'employee_code']),
        ]);
    }

    public function edit(Employee $employee): RedirectResponse
    {
        return redirect()->route('employees.show', $employee);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $this->employeeService->update($employee, $request->validated());

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Employee updated.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $this->authorize('archive', $employee);
        $this->employeeService->archive($employee);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee archived.');
    }

    /** @return array<int, array<string, mixed>> */
    private function catalogComponents(): array
    {
        return SalaryComponent::query()
            ->orderBy('type')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'type', 'calculation_method', 'default_value', 'is_taxable'])
            ->map(fn (SalaryComponent $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'code' => $c->code,
                'type' => $c->type,
                'calculation_method' => $c->calculation_method,
                'default_value' => $c->default_value,
                'is_taxable' => $c->is_taxable,
            ])
            ->all();
    }
}
