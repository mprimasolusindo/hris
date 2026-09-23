# Bug Fixes: Attendance Clock Out, Hire Position, Overtime Summary

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix three production bug reports: missing Clock Out on attendance capture, wrong position on pipeline hire, and approved overtime not shown on payroll attendance summary.

**Architecture:** Three independent, minimal fixes — frontend form field, hire FK copy from JobPosting, and payroll summary query against `ot_overtimes`.

**Tech Stack:** Laravel 12, Inertia/React, PHPUnit. Worktree: `.worktrees/fix-bug-reports` on branch `fix/bug-reports-attendance-hire-ot`.

## Global Constraints

- **NEVER** run `php artisan migrate:fresh`, `migrate:reset`, or `db:wipe` — see `.cursor/rules/05-database-safety.mdc`. Use `php artisan test` for verification (RefreshDatabase uses test DB only).
- Do not expand scope: no one-click clock-out product redesign; no OT pay line-item in PayrollCalculationService in this plan (display fix only for bug 3).
- Commit after each task. Work only in the worktree.

---

### Task 1: Attendance capture form — add Clock Out

**Files:**
- Modify: `resources/js/Pages/Attendance/Index.tsx`
- Test: smoke via existing attendance tests if any; manual form posts `clock_out`

**Root cause:** Capture form has `clock_out` in form state but no input; edit dialog already has it. Backend `AttendanceController@store` already accepts `clock_out`.

- [ ] **Step 1: Write failing assertion (optional PHP feature if needed)** — Prefer verifying store already accepts clock_out (PhaseOneFlowTest). Frontend change is the fix.

- [ ] **Step 2: Add Clock Out field to capture form**

After the Clock In block in the Capture attendance card, add:

```tsx
<div className="space-y-2">
    <Label>{t('clockOut')}</Label>
    <Input
        type="datetime-local"
        value={captureForm.data.clock_out}
        onChange={(e) =>
            captureForm.setData('clock_out', e.target.value)
        }
    />
</div>
```

Also replace hardcoded `"Clock in"` label with `{t('clockIn')}` if `clockIn` key exists.

- [ ] **Step 3: Null-normalize on submit**

```tsx
const submitCapture: FormEventHandler = (e) => {
    e.preventDefault();
    captureForm.transform((data) => ({
        ...data,
        site_id: data.site_id || null,
        clock_out: data.clock_out || null,
    }));
    captureForm.post(route('attendance.store'));
};
```

- [ ] **Step 4: Commit**

```bash
git add resources/js/Pages/Attendance/Index.tsx
git commit -m "fix(attendance): show Clock Out on capture form"
```

---

### Task 2: Pipeline hire — copy JobPosting position/department/site

**Files:**
- Modify: `app/Http/Controllers/Recruitment/PipelineController.php` (hire method)
- Modify: `tests/Feature/RecruitmentTalentTest.php` (or dedicated hire test)

**Root cause:** `hire()` uses `Position::query()->orderBy('id')->first()` instead of `$job->position_id`.

- [ ] **Step 1: Write failing test**

In `RecruitmentTalentTest` (or new test method), create job posting with explicit `position_id` and `department_id`, hire application, assert `emp_jobs.position_id` and `department_id` match the job posting (not the globally first position).

- [ ] **Step 2: Run test — expect FAIL**

```bash
php artisan test --filter=RecruitmentTalentTest
```

- [ ] **Step 3: Fix hire()**

Replace department/position/site selection:

```php
$departmentId = $job->department_id
    ?? Department::query()->where('company_id', $job->company_id)->orderBy('id')->value('id');

$positionId = $job->position_id; // do NOT use Position::orderBy('id')->first()

EmployeeJob::query()->create([
    'employee_id' => $employee->id,
    'company_id' => $job->company_id,
    'department_id' => $departmentId,
    'position_id' => $positionId,
    'employment_type' => 'pkwtt',
    'start_date' => $joinDate->toDateString(),
    'end_date' => null,
]);

$siteId = $job->site_id
    ?? Site::query()->where('company_id', $job->company_id)->orderBy('id')->value('id');
```

Use `$siteId` when attaching `rel_employee_sites`.

- [ ] **Step 4: Run test — expect PASS**

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Recruitment/PipelineController.php tests/Feature/RecruitmentTalentTest.php
git commit -m "fix(recruitment): copy job posting org FKs on hire"
```

---

### Task 3: Payroll attendance summary — use approved overtime hours

**Files:**
- Modify: `app/Http/Controllers/PayrollController.php` (`show` method)
- Create/Modify test: `tests/Feature/PayrollShowAttendanceSummaryTest.php` or extend existing payroll test

**Root cause:** Summary computes OT from punch duration (`hours - 8`), ignoring approved `ot_overtimes`.

- [ ] **Step 1: Write failing test**

Create employee, payroll for a month, approved Overtime with known hours, attendance punches that imply 0 OT. GET `payroll.show` → assert Inertia `attendanceSummary.overtime_hours` equals approved sum (rounded to 1 decimal).

- [ ] **Step 2: Run test — expect FAIL**

- [ ] **Step 3: Fix PayrollController::show**

```php
use App\Models\Overtime;

'overtime_hours' => round((float) Overtime::query()
    ->where('employee_id', $payroll->employee_id)
    ->where('status', 'approved')
    ->whereYear('date', $payroll->period_year)
    ->whereMonth('date', $payroll->period_month)
    ->sum('hours'), 1),
```

- [ ] **Step 4: Run test — expect PASS**

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/PayrollController.php tests/Feature/
git commit -m "fix(payroll): count approved overtime in attendance summary"
```

---

## Out of scope

- OT pay line items in `PayrollCalculationService` (PP 35/2021 rate) — follow-up
- One-click “Clock Out now” UX
- Backfilling existing hired employees’ wrong `position_id`
