# Payroll Generate All Employees Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** On the payroll index Generate card, allow generating payroll for all active employees for a chosen month/year in one action.

**Architecture:** Extend `POST payroll.store` with optional `scope=all`. When `scope=all`, loop `Employee` rows with `status=active`, resolve base salary via existing `PayrollCalculationService::resolveBaseSalaryForPeriod`, call `generate` for those with a salary, skip the rest, and redirect to `payroll.index` with a flash summary. Single-employee path stays unchanged. UI adds an "All employees" select value and a confirm dialog before submit.

**Tech Stack:** Laravel 12, Inertia/React, PHPUnit Feature tests, existing `PayrollCalculationService`.

**Spec:** Approved chat design (2026-09-23): All employees option; confirm; skip no-contract; flash `Generated N… Skipped M…`; no company/site filter; sync only.

## Global Constraints

- NEVER run `php artisan migrate:fresh`, `migrate:reset`, or `db:wipe`.
- Work only in the assigned git worktree; commit on the feature branch.
- Do not change single-employee generate behavior except validation that allows either `employee_id` OR `scope=all`.
- Bulk generate only employees with `status = 'active'`.
- Reuse `resolveBaseSalaryForPeriod` and `generate`; do not duplicate payroll math.
- Flash success copy must be exactly: `Generated {N} payroll(s). Skipped {M} (no active contract).`
- Select sentinel value for all employees: `all` (string). When posting all, send `scope=all` and omit or leave empty `employee_id`.
- Redirect after bulk: `route('payroll.index')` (not show).
- No new routes, migrations, or queues.

---

### Task 1: Backend store accepts scope=all

**Files:**
- Modify: `app/Http/Controllers/PayrollController.php` (`store` method)
- Test: `tests/Feature/PayrollStoreGenerateAllTest.php` (create)

**Interfaces:**
- Consumes: `PayrollCalculationService::resolveBaseSalaryForPeriod(Employee, int, int): ?float`, `::generate(Employee, int, int, float): Payroll`
- Produces: `store` accepts `scope` optional `in:all`; when `scope=all`, ignores `employee_id`, generates for all active employees, redirects to index with exact flash string

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/PayrollStoreGenerateAllTest.php` modeled on `tests/Feature/PayrollStoreContractSalaryTest.php` (same BPJS/TaxRule/tax profile helpers).

```php
<?php

namespace Tests\Feature;

use App\Models\BpjsConfig;
use App\Models\Employee;
use App\Models\EmploymentContract;
use App\Models\TaxRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollStoreGenerateAllTest extends TestCase
{
    use RefreshDatabase;

    private function seedPayrollPrerequisites(int $employeeId): void
    {
        BpjsConfig::query()->create([
            'type' => 'kesehatan',
            'employee_percentage' => 0.0100,
            'company_percentage' => 0.0400,
        ]);

        TaxRule::query()->create([
            'name' => 'ter_monthly_A_generate_all',
            'rule_type' => 'ter_monthly',
            'ptkp_category' => 'A',
            'gross_min' => 0,
            'gross_max' => null,
            'value' => 0.0500,
        ]);

        \DB::table('emp_tax_profiles')->insert([
            'employee_id' => $employeeId,
            'has_npwp' => true,
            'tax_status' => 'TK/0',
            'tax_method' => 'ter_monthly',
            'dependents_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_store_scope_all_generates_for_active_employees_with_contracts(): void
    {
        $this->actingAs(User::factory()->create());

        $withContract = Employee::factory()->create(['status' => 'active']);
        $withoutContract = Employee::factory()->create(['status' => 'active']);
        $resigned = Employee::factory()->create(['status' => 'resigned']);

        $this->seedPayrollPrerequisites($withContract->id);
        $this->seedPayrollPrerequisites($withoutContract->id);
        $this->seedPayrollPrerequisites($resigned->id);

        EmploymentContract::query()->create([
            'employee_id' => $withContract->id,
            'contract_type' => 'pkwtt',
            'start_date' => now()->startOfYear(),
            'end_date' => null,
            'salary_base' => 5000000,
        ]);

        EmploymentContract::query()->create([
            'employee_id' => $resigned->id,
            'contract_type' => 'pkwtt',
            'start_date' => now()->startOfYear(),
            'end_date' => null,
            'salary_base' => 4000000,
        ]);

        $month = (int) now()->month;
        $year = (int) now()->year;

        $response = $this->post(route('payroll.store'), [
            'scope' => 'all',
            'period_month' => $month,
            'period_year' => $year,
        ]);

        $response->assertRedirect(route('payroll.index'));
        $response->assertSessionHas(
            'success',
            'Generated 1 payroll(s). Skipped 1 (no active contract).'
        );
        $this->assertDatabaseCount('pay_payrolls', 1);
        $this->assertDatabaseHas('pay_payrolls', [
            'employee_id' => $withContract->id,
            'period_month' => $month,
            'period_year' => $year,
        ]);
        $this->assertDatabaseMissing('pay_payrolls', [
            'employee_id' => $resigned->id,
        ]);
        $this->assertDatabaseMissing('pay_payrolls', [
            'employee_id' => $withoutContract->id,
        ]);
    }

    public function test_store_single_employee_still_requires_employee_id(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('payroll.store'), [
            'period_month' => (int) now()->month,
            'period_year' => (int) now()->year,
        ]);

        $response->assertSessionHasErrors('employee_id');
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=PayrollStoreGenerateAllTest`
Expected: FAIL (scope=all not accepted / wrong redirect)

- [ ] **Step 3: Implement store branch**

In `PayrollController::store`, replace validation/body with:

```php
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
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --filter=PayrollStoreGenerateAllTest`
Expected: PASS (2 tests)

Also run: `php artisan test --filter=PayrollStoreContractSalaryTest`
Expected: PASS (single path unchanged)

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/PayrollController.php tests/Feature/PayrollStoreGenerateAllTest.php
git commit -m "feat(payroll): generate payroll for all active employees"
```

---

### Task 2: UI All employees option + confirm

**Files:**
- Modify: `resources/js/Pages/Payroll/Index.tsx` (generate form only)

**Interfaces:**
- Consumes: `POST payroll.store` with `scope=all` + `period_month` + `period_year` (no employee_id when all)
- Produces: Select includes value `all` labeled `All employees`; confirm before submit when all selected

- [ ] **Step 1: Update generateForm and submit handler**

Change `generateForm` initial `employee_id` to keep first employee default (unchanged).

Update `submitGenerate`:

```tsx
const submitGenerate: FormEventHandler = (e) => {
    e.preventDefault();
    if (generateForm.data.employee_id === 'all') {
        if (
            !window.confirm(
                `Generate payroll for all employees for ${generateForm.data.period_month}/${generateForm.data.period_year}?`,
            )
        ) {
            return;
        }
        generateForm.transform((data) => ({
            scope: 'all',
            period_month: data.period_month,
            period_year: data.period_year,
        }));
        generateForm.post(route('payroll.store'), {
            onFinish: () => generateForm.transform((data) => data),
        });
        return;
    }
    generateForm.post(route('payroll.store'));
};
```

If `useForm.transform` reset is awkward in this codebase, prefer an explicit alternate post:

```tsx
if (generateForm.data.employee_id === 'all') {
    if (!window.confirm(`Generate payroll for all employees for ${generateForm.data.period_month}/${generateForm.data.period_year}?`)) {
        return;
    }
    router.post(route('payroll.store'), {
        scope: 'all',
        period_month: generateForm.data.period_month,
        period_year: generateForm.data.period_year,
    });
    return;
}
```

Prefer `router.post` from `@inertiajs/react` (already imported via `router`) so processing state can use `generateForm.setProcessing` or simply leave button enabled briefly — acceptable. Better: keep using `generateForm` with transform as first choice.

- [ ] **Step 2: Add SelectItem for All employees**

Inside the employee `<SelectContent>`, as the first item:

```tsx
<SelectItem value="all">All employees</SelectItem>
{employees.map((e) => (
    <SelectItem key={e.id} value={String(e.id)}>
        {e.employee_code} — {e.full_name}
    </SelectItem>
))}
```

- [ ] **Step 3: Manual sanity (no browser required)**

Confirm TypeScript builds if project has a typecheck script; otherwise rely on existing Vite setup. No new PHPUnit for UI.

- [ ] **Step 4: Commit**

```bash
git add resources/js/Pages/Payroll/Index.tsx
git commit -m "feat(payroll): add All employees option to generate form"
```

---

## Spec coverage checklist

| Requirement | Task |
|---|---|
| All employees select option | Task 2 |
| Confirm before bulk generate | Task 2 |
| Backend generate all active | Task 1 |
| Skip no active contract | Task 1 |
| Exact flash copy | Task 1 |
| Single-employee path unchanged | Task 1 |
| No company/site filter / no queues | Global Constraints |
