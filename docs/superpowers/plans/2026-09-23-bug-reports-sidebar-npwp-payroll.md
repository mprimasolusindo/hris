# Bug Fixes: Sidebar Stay-Open, NPWP Tax Modal, Payroll Filters & Base Salary

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development.

**Goal:** Fix four UX/data bugs: keep sidebar submenu open on child routes; stop re-entering NPWP on tax profile; add payroll status filters; resolve base salary from employee contract instead of payroll generate form.

**Architecture:** Independent UI/backend fixes. No schema migrations required for the minimal path (NPWP: stop writing duplicate field; base salary: read `emp_contracts.salary_base`).

**Tech Stack:** Laravel 12, Inertia/React, PHPUnit. Worktree: `.worktrees/fix-bug-reports` on branch `fix/bug-reports-sidebar-npwp-payroll` (forked from prior bugfix commits).

## Global Constraints

- **NEVER** run `php artisan migrate:fresh`, `migrate:reset`, or `db:wipe`.
- Do not change OT pay calculation or prior attendance/hire/OT commits except as needed.
- Commit after each task.

---

### Task 1: Keep sidebar submenu open on child routes

**Files:**
- Modify: `resources/js/Components/layout/AppSidebar.tsx`

**Root cause:** `open={expanded.has(title)}` only; sidebar remounts; `isActive` may fail absolute vs pathname.

- [ ] Derive `isOpen = expanded.has(node.title) || hasActiveChild(node)`
- [ ] Normalize paths in `isActive` (pathname from absolute URLs)
- [ ] Optionally refuse `onOpenChange(false)` while `activeChild`
- [ ] Commit: `fix(nav): keep submenu open on active child route`

---

### Task 2: NPWP — use master identity, not tax modal re-entry

**Files:**
- Modify: `resources/js/Features/employees/components/EmployeeProfileTabs.tsx`
- Modify: `app/Http/Controllers/Employee/TaxProfileController.php` and/or `StoreEmployeeTaxProfileRequest` / `EmployeeTaxProfileService` as needed
- Test: feature or unit asserting tax upsert does not require client NPWP when identity has it

**Root cause:** NPWP on both `emp_identities` and `emp_tax_profiles`; tax form only binds tax_profile.npwp.

- [ ] Tax dialog: remove editable NPWP input; show read-only from `employee.identity?.npwp` (or "set in ID / NPWP")
- [ ] Prefill `has_npwp` from `tax_profile.has_npwp ?? !!identity.npwp`
- [ ] Payroll tax summary Field for NPWP reads identity NPWP
- [ ] Backend: on tax upsert, set `npwp` from `employee.identity?->npwp` (ignore or drop request npwp); keep `has_npwp` from request
- [ ] Commit: `fix(employees): use identity NPWP on tax profile`

---

### Task 3: Payroll index — status filter UI

**Files:**
- Modify: `resources/js/Pages/Payroll/Index.tsx`
- Optionally: `PayrollController::index` summary aggregates

**Root cause:** Backend already filters by `status`; UI only has month/year.

- [ ] Add Status Select: `all | draft | generated | reviewed | approved | paid` wired to `applyFilters`
- [ ] Optionally company/site selects if quick; otherwise status only (YAGNI: status is the reported bug)
- [ ] Fix summary counts to use query aggregates before paginate (not page collection)
- [ ] Commit: `fix(payroll): add status filter on runs list`

---

### Task 4: Base salary from contract, not generate form

**Files:**
- Modify: `app/Http/Controllers/PayrollController.php` (`store`)
- Modify: `app/Services/Payroll/PayrollCalculationService.php` (optional resolver helper)
- Modify: `resources/js/Pages/Payroll/Index.tsx` (remove base salary input)
- Modify: `resources/js/Features/employees/components/EmployeeProfileTabs.tsx` (show `salary_base` on contracts table)
- Update tests that POST `base_salary` (PhaseOneFlowTest, HardeningTest, etc.)

**Root cause:** Generate requires manual `base_salary`; canonical field is `emp_contracts.salary_base`.

- [ ] Resolve active contract salary for period (period end between start/end or open-ended)
- [ ] Validation error if no salary_base found
- [ ] Remove required base salary from generate form UI
- [ ] Show salary_base on employee contracts tab
- [ ] Fix tests
- [ ] Commit: `fix(payroll): resolve base salary from employee contract`

---

## Out of scope

- Dropping `emp_tax_profiles.npwp` column (migration)
- Changing generate flow to start as `draft` instead of `generated`
- Persistent Inertia layout for sidebar (derivable open is enough)
