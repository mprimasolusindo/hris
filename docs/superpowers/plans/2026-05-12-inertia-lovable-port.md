# Inertia + React Lovable UI Port — Implementation Plan

> **For agentic workers:** Use `docs/superpowers/specs/2026-05-12-inertia-lovable-port-design.md` as the source of truth. Prefer subagent-driven-development (fresh context per task) or executing-plans for long runs.

**Goal:** Port `c:\xampp\htdocs\hr-uiux` UI/UX into `c:\xampp\htdocs\hris` using Inertia + React + Breeze, with Laravel + Eloquent backing each screen, replacing Blade per feature.

**Architecture:** Breeze Inertia React entry (`resources/js/app.tsx`), Laravel `Inertia::render` for new pages, existing Blade kept only until each controller returns Inertia for that slice.

**Tech stack:** Laravel 12, Inertia Laravel v2, `@inertiajs/react`, React 18, TypeScript, Vite 7, Tailwind CSS 3.x + PostCSS (Breeze default after install; optional later upgrade to Tailwind 4 per spec section 8).

**Deviation logged (2026-05-12):** Original `hris` used Tailwind 4 + `@tailwindcss/vite`. Breeze installed Tailwind 3 + Postcss; `@tailwindcss/vite` was removed and `@types/node` bumped to `^22.13.0` to satisfy Vite 7 peer deps and unblock `npm install`.

---

### Task 0: Prerequisites

**Files:** `composer.json`, `package.json`, `docs/superpowers/specs/2026-05-12-inertia-lovable-port-design.md`

- [x] **Step 1:** Spec approved (`spec approved` in chat).
- [x] **Step 2:** Install Breeze + Inertia React + TypeScript.

```bash
cd c:\xampp\htdocs\hris
composer require laravel/breeze --dev --no-interaction
php artisan breeze:install react --typescript --no-interaction
```

- [x] **Step 3:** Fix npm peer conflict — set `@types/node` to `^22.13.0`, remove `@tailwindcss/vite` from `package.json`, run:

```bash
npm install
npm run build
```

- [x] **Step 4:** Merge HR routes + auth — edit `routes/web.php` (guest `/` → `login`, auth `/` → `dashboard`; wrap `employees`, `attendance`, `payroll`, `profile` in `Route::middleware(['auth', 'verified'])->group(...)`).
- [x] **Step 5:** Update tests — `tests/Feature/ExampleTest.php` and `tests/Feature/PhaseOneFlowTest.php` use `actingAs(User::factory()->create())` where hitting protected routes; guest `/` asserts `login`.
- [x] **Step 6:** Run `php artisan test` — expect 30 passed.

---

### Task 1: P0 — HR shell (Lovable `AppLayout` / `AppSidebar` / `AppHeader`)

**Files (to create or modify):**  
`resources/js/Layouts/HrisLayout.tsx`, `resources/js/Components/layout/*`, `resources/js/Pages/Dashboard.tsx` (replace Breeze default), `resources/css/app.css` (design tokens from `hr-uiux/src/index.css`), `package.json` (add Radix, lucide-react, class-variance-authority, clsx, tailwind-merge as needed)

- [x] Port CSS variables and fonts from `c:\xampp\htdocs\hr-uiux\src\index.css` into `resources/css/app.css` + `tailwind.config.js` extensions.
- [x] Copy minimal shadcn primitives required for sidebar: `button`, `separator`, `tooltip`, `sidebar` (or simplify to flex layout first, then parity).
- [x] Replace Breeze `AuthenticatedLayout` usage in `Dashboard` (and later all pages) with `HrisLayout` that mirrors `AppSidebar` groups and `Link` hrefs to named Laravel routes (`route()` via Ziggy or hardcoded paths matching `web.php`).
- [x] Register Ziggy `@routes` already in `app.blade.php` — use `route()` from `ziggy-js` in TSX.

**Verify:** Logged-in user sees sidebar matching Lovable groups (links can 404 until routes exist; use `disabled` or hide until controller lands).

---

### Task 2: P1 — Employees to Inertia

**Files:** `app/Http/Controllers/EmployeeController.php`, `resources/js/Pages/Employees/*.tsx`, delete `resources/views/employees/*.blade.php` when done.

- [x] Add `npm` deps: `react-hook-form`, `@hookform/resolvers`, `zod` if forms match Lovable.
- [x] Convert `index`, `create`, `edit`, `show` to `Inertia::render` with typed props (paginator serialized).
- [x] Port `EmployeeFormDialog` patterns into pages or modals; submit to existing `route('employees.store')` etc.

**Verify:** `php artisan test` + manual create/edit employee.

---

### Task 3: P1 — Attendance + Payroll Blade removal

**Files:** `AttendanceController.php`, `PayrollController.php`, `resources/js/Pages/Attendance/*.tsx`, `resources/js/Pages/Payroll/*.tsx`, remove `resources/views/attendance`, `resources/views/payroll`, remove legacy `resources/views/layouts/app.blade.php` when no Blade consumer remains.

- [x] Same pattern as employees.

---

### Task 4: P2 — Organization + masters

**Files:** `app/Http/Controllers/Organization/*`, `app/Http/Controllers/Master/AllowanceTypeController.php`, `app/Http/Controllers/Payroll/MasterAllowanceController.php`, `app/Http/Controllers/Payroll/MasterDeductionController.php`, `resources/js/Pages/Organization/**`, `resources/js/Pages/Master/**`, `resources/js/Pages/Payroll/Master*.tsx`, `resources/js/Components/master/MasterCrudPage.tsx`, `routes/web.php`, `tests/Feature/OrganizationMasterTest.php`

- [x] Companies, sites, departments, positions — Inertia CRUD wired to `org_*` models (schema fields: name/type, company_id, location).
- [x] Allowance types + master allowances + master deductions — `cfg_salary_components` with `type` earning|deduction.
- [x] Sidebar org links use named Laravel routes.
- [x] `payroll/master-*` routes registered before `payroll/{payroll}`.
- [x] `php artisan test` — 33 passed.

**Note:** Lovable template has extra columns (address on companies, formula on master allowances). Laravel schema uses simpler fields until migrations extend `cfg_salary_components` / `org_companies`.

---

### Task 5–8: P3–P5 per design spec

Follow section 10 phases in `2026-05-12-inertia-lovable-port-design.md` (shifts, leave, contracts, vendors, outsourcing, billing). Each slice: routes → controller props → TSX page → delete old artifacts.

---

## Verification commands (recurring)

```bash
cd c:\xampp\htdocs\hris
npm run build
php artisan test
php artisan route:list
```

---

## Notes for subagent-driven-development

- This repo had **no `.git`** at execution time; commits were skipped. Initialize git before requiring per-task commits.
- Full two-agent spec/quality review per task requires local prompt templates (`implementer-prompt.md`, etc.); when absent, parent agent performs review and test run after each task.
