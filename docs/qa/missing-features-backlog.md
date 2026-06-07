# HRIS Missing Features Backlog

> Generated: 2026-06-07 | Auditor: hris-qa-audit / hris-qa-auditor

## Priority summary

- **P0:** 8 items (compliance + core workflow)
- **P1:** 12 items (module stubs / schema-only)
- **P2:** 6 items (UX polish)

## Recommended build order

1. P0-001 → P0-008 (payroll compliance + config admin)
2. P1-001 → P1-004 (core ops: overtime, loans, leave engine, attendance)
3. P1-005 → P1-010 (talent layer + interviews)
4. P1-011 → P1-012 (outsourcing billing + SaaS admin)
5. P0-DATA + P1-DATA (comprehensive demo seeder)
6. P2-001 → P2-006 (dashboard, search, polish)

---

## Backlog (ordered)

### P0-001 — Payroll TER + full BPJS calculation

- **Module:** payroll
- **Status:** partial
- **Evidence:** `app/Services/Payroll/PayrollCalculationService.php` (flat 5% PPh21, 4% BPJS; ignores allowances/deductions/loans/THR)
- **Acceptance criteria:**
  - [ ] PPh21 uses TER per PMK 168/2023 from `cfg_tax_rules`
  - [ ] All BPJS types (kesehatan, jht, jp, jkk, jkm, jkp) from `cfg_bpjs`
  - [ ] Employee `emp_allowances`, `emp_deductions`, `emp_loans` included in calc
  - [ ] THR pro-rata when applicable
  - [ ] Feature tests with fixture employees
- **Dependencies:** P0-002, hr-research-indonesia for rates

### P0-002 — BPJS & tax config admin CRUD

- **Module:** payroll
- **Status:** schema-only
- **Evidence:** `cfg_bpjs`, `cfg_tax_rules` models exist; no admin routes
- **Acceptance criteria:**
  - [ ] Routes + controller for BPJS config CRUD
  - [ ] Routes + controller for tax rules CRUD
  - [ ] Inertia pages under Payroll or Admin
  - [ ] Permissions in `PermissionCatalog.php`
  - [ ] Sidebar nav entries

### P0-003 — Full BPJS seeder config

- **Module:** payroll / demo data
- **Status:** data-gap
- **Evidence:** `HrisIndonesiaDemoSeeder` seeds only 3 BPJS types
- **Acceptance criteria:**
  - [ ] All 6 BPJS types seeded with research-backed rates
  - [ ] TER bracket rows in `cfg_tax_rules`

### P0-004 — Salary component catalog + employee allowances/deductions demo

- **Module:** payroll / demo data
- **Status:** data-gap
- **Evidence:** `cfg_salary_components`, `emp_allowances`, `emp_deductions` empty after demo seed
- **Acceptance criteria:**
  - [ ] Master components (transport, meal, position, etc.)
  - [ ] Per-employee allowances/deductions linked to components

### P0-005 — Leave entitlement engine

- **Module:** leave
- **Status:** partial
- **Evidence:** `LeaveBalanceController` computed from approved leaves only; types hardcoded in `LeaveController::TYPES`
- **Acceptance criteria:**
  - [ ] `lv_leave_types` table + CRUD admin
  - [ ] Annual entitlement per type per employee
  - [ ] Balance = entitlement − approved usage
  - [ ] Leave Types page supports CRUD (not read-only)

### P0-006 — Employee emergency contact UI

- **Module:** employees
- **Status:** partial
- **Evidence:** Backend routes exist; `EmployeeProfileTabs.tsx` display-only
- **Acceptance criteria:**
  - [ ] Add/edit/delete emergency contacts on employee show page

### P0-007 — Pipeline hire creates full employee record

- **Module:** recruitment
- **Status:** partial
- **Evidence:** `PipelineController::hire` creates minimal Employee
- **Acceptance criteria:**
  - [ ] Hire creates `emp_contracts`, `emp_jobs`, `rel_employee_sites`
  - [ ] Optional link to existing org structure

### P0-008 — Wire orphan permissions to routes

- **Module:** admin / RBAC
- **Status:** partial
- **Evidence:** `PermissionCatalog.php` has keys with no routes (`attendance.update`, `payroll.update`, etc.)
- **Acceptance criteria:**
  - [ ] Each orphan permission either has a route or is removed from catalog

---

### P1-001 — Overtime module (PP 35/2021)

- **Module:** attendance
- **Status:** schema-only
- **Evidence:** `ot_overtimes` model; no controller/routes
- **Acceptance criteria:**
  - [ ] CRUD + approval workflow
  - [ ] Inertia pages + sidebar nav
  - [ ] Demo seeder rows

### P1-002 — Employee loans module

- **Module:** employees / payroll
- **Status:** schema-only
- **Evidence:** `emp_loans` model; no UI
- **Acceptance criteria:**
  - [ ] CRUD on employee profile or dedicated page
  - [ ] Monthly deduction reflected in payroll calc
  - [ ] Demo seeder for subset of employees

### P1-003 — Attendance edit/delete + shift integration

- **Module:** attendance
- **Status:** partial
- **Evidence:** `AttendanceController` index + store only
- **Acceptance criteria:**
  - [ ] Update/delete routes with `attendance.update` permission
  - [ ] Status derivation considers assigned shift

### P1-004 — Employee job history & site assignment CRUD

- **Module:** employees
- **Status:** schema-only
- **Evidence:** `emp_jobs`, `rel_employee_sites` seeded but no management UI
- **Acceptance criteria:**
  - [ ] Tabs or dialogs on employee show for job history + sites

### P1-005 — Performance management

- **Module:** talent
- **Status:** stub
- **Evidence:** `TalentModuleController::performance` → `Talent/Placeholder.tsx`
- **Acceptance criteria:**
  - [ ] New `tal_performance_reviews` table + full CRUD
  - [ ] Replace placeholder page

### P1-006 — Training management

- **Module:** talent
- **Status:** stub
- **Evidence:** `TalentModuleController::training` → Placeholder
- **Acceptance criteria:**
  - [ ] New `tal_trainings` + `rel_training_employees` tables + CRUD

### P1-007 — Talent pool

- **Module:** talent
- **Status:** stub
- **Evidence:** `TalentModuleController::talentPool` → Placeholder
- **Acceptance criteria:**
  - [ ] New `tal_talent_pool` table + CRUD

### P1-008 — Succession planning

- **Module:** talent
- **Status:** stub
- **Evidence:** `TalentModuleController::succession` → Placeholder
- **Acceptance criteria:**
  - [ ] New `tal_succession_plans` table + CRUD

### P1-009 — Nine-box grid

- **Module:** talent
- **Status:** stub
- **Evidence:** `TalentModuleController::nineBox` → Placeholder
- **Acceptance criteria:**
  - [ ] New `tal_nine_box_assessments` table + visual grid UI

### P1-010 — Recruitment interviews

- **Module:** recruitment
- **Status:** stub
- **Evidence:** `recruitment/interviews` → Placeholder
- **Acceptance criteria:**
  - [ ] New `trx_interviews` table
  - [ ] Schedule, feedback, link to application

### P1-011 — Vendor billing persistence

- **Module:** outsourcing
- **Status:** partial
- **Evidence:** `VendorBillingController` preview-only; `bill_payments` unused for invoices
- **Acceptance criteria:**
  - [ ] Invoice generation + persistence
  - [ ] CRUD for vendor invoices

### P1-012 — SaaS billing admin

- **Module:** system
- **Status:** schema-only
- **Evidence:** `sys_tenants`, `sub_plans`, `sub_subscriptions`, `bill_payments` — no UI
- **Acceptance criteria:**
  - [ ] Admin CRUD for tenants, plans, subscriptions, payments

---

### P1-DATA — Comprehensive demo seeder (additive)

- **Module:** demo data
- **Status:** data-gap
- **Evidence:** ~28 tables empty after `HrisIndonesiaDemoSeeder`
- **Acceptance criteria:**
  - [ ] Fill all tables listed in plan Phase 1
  - [ ] Idempotent guards per section
  - [ ] Indonesian names via `id_ID` faker
  - [ ] ESS user links for subset of employees

---

### P2-001 — Dashboard real charts & activity feed

- **Module:** dashboard
- **Status:** partial
- **Evidence:** `Dashboard.tsx` hardcoded `attendanceData` / `payrollData`
- **Acceptance criteria:**
  - [ ] Charts from API/controller aggregates
  - [ ] Recent activity from audit-worthy events

### P2-002 — Global search

- **Module:** shell
- **Status:** missing
- **Evidence:** `AppHeader.tsx` search input has no handler
- **Acceptance criteria:**
  - [ ] Search employees, jobs, candidates
  - [ ] Navigate to result

### P2-003 — Header notification lists

- **Module:** shell
- **Status:** partial
- **Evidence:** `AppHeader.tsx` shows `noData` despite `useReminders`
- **Acceptance criteria:**
  - [ ] Pending leaves, expiring contracts in dropdown

### P2-004 — Outsourcing compliance workflow

- **Module:** outsourcing
- **Status:** partial
- **Evidence:** `ComplianceController` read-only flags
- **Acceptance criteria:**
  - [ ] Acknowledge/resolve actions + persistence

### P2-005 — Placement update route

- **Module:** outsourcing
- **Status:** partial
- **Evidence:** `outsourcing.update` permission unused
- **Acceptance criteria:**
  - [ ] Update placement endpoint + UI

### P2-006 — Remove duplicate allowance nav

- **Module:** payroll / UX
- **Status:** partial
- **Evidence:** `master/allowance-types` duplicates `payroll/master-allowances`
- **Acceptance criteria:**
  - [ ] Consolidate to one nav entry or distinct purpose documented

---

## Module completion matrix

| Module | Backend | Frontend | Demo data | Compliance |
|---|---|---|---|---|
| Employees | Strong | Strong | Good | — |
| Attendance | Partial | Good | Good | — |
| Payroll | MVP | Good | Good | **Needs P0** |
| Leave | Basic | Partial | Empty | **Needs P0** |
| Shifts | Good | Good | Empty | — |
| Contracts | Good | Good | Empty | — |
| Outsourcing | Good | Partial | Empty | — |
| Recruitment | Good | Good | Empty | Partial |
| Talent | Stub | Stub | N/A | — |
| Org | Good | Good | Good | — |
| Admin/RBAC | Good | Good | Good | — |
| SaaS | None | None | Empty | — |

---

## Changelog

| Date | Action |
|---|---|
| 2026-06-07 | Initial audit backlog created |
| 2026-06-07 | Implementation complete — all P0/P1/P2 items addressed |

## Completion status (2026-06-07)

All backlog items implemented:

- **P0-001..008:** Payroll TER/BPJS compliance, config admin CRUD, full demo config, leave entitlement engine, emergency contacts UI, pipeline hire enrichment, orphan permissions wired
- **P1-001..012:** Overtime, loans, attendance edit/delete, job/site CRUD, talent layer (5 modules), recruitment interviews, vendor billing persistence, SaaS admin
- **P1-DATA:** `HrisIndonesiaDemoSupplementSeeder` fills all empty tables additively
- **P2-001..006:** Dashboard real charts, global search, header notifications, compliance resolve, placement update

Verification: `php artisan test` (94 passed), `npm run build` (clean), migrations round-trip OK.
