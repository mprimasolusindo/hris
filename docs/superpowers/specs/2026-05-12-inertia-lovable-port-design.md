# HRIS UI Port — Lovable (`hr-uiux`) → Laravel + Inertia + React (`hris`)

**Status:** Design approved by product owner on 2026-05-12.  
**Next step after spec review:** Implementation plan in `docs/superpowers/plans/` (writing-plans skill), then phased execution.

---

## 1. Purpose

Deliver an admin UI in `c:\xampp\htdocs\hris` that matches the **layout, styling, components, labels, and interaction patterns** of `c:\xampp\htdocs\hr-uiux` (Lovable: Vite + React + shadcn/ui + Tailwind 3 + Supabase client), while **binding all data to Laravel** (controllers, validation, Eloquent, MySQL). Supabase is **not** a runtime dependency in `hris` after the port.

---

## 2. Locked decisions

| Topic | Decision |
|--------|-----------|
| UI integration | **Inertia.js + React** — Laravel owns HTTP routes; React renders pages from `Inertia::render`. |
| Data | **Laravel controllers + Eloquent** per screen; no stub-only phase, no Supabase bridge. |
| Authentication | **Laravel Breeze** with the **Inertia + React** stack (session, CSRF, Breeze patterns). |
| Existing Blade UI | **Replace as we go** — when an Inertia page reaches agreed parity for a feature, remove that feature’s Blade views and serve only Inertia from controllers. |

---

## 3. Non-goals (this design)

- **Production deployment**, CI/CD, and container changes.
- **Legal or payroll formula correctness** beyond what existing Laravel code and schema already provide; Indonesian compliance deep-dives follow project HR research rules when touching calculations.
- **Pixel-perfect** guarantee without a visual QA pass; parity is defined as **same components, tokens, copy, and primary flows** as `hr-uiux`, with known Tailwind major-version differences called out in section 8.

---

## 4. Source inventory (`hr-uiux`)

**Stack:** React 18, TypeScript, Vite, React Router (to be replaced mentally by Inertia navigation), TanStack Query (optional per page; server-first props preferred), react-hook-form, zod, Radix/shadcn, Tailwind 3, lucide-react, Sonner/toast, i18n via `LanguageContext` and `translations.ts`, Supabase client under `src/integrations/supabase/`.

**Shell:** `AppLayout`, `AppHeader`, `AppSidebar`, `ProtectedRoute`, `AuthContext`.

**Routes (from `App.tsx`):** `/login`, `/register`, `/dashboard`, `/employees`, `/employees/:id`, `/attendance`, `/payroll`, `/payroll/master-deductions`, `/payroll/master-allowances`, `/payroll/:id`, `/organization/companies`, `/organization/sites`, `/organization/departments`, `/organization/positions`, `/master/allowance-types`, `/shifts`, `/shifts/calendar`, `/shifts/assign`, `/shifts/:id`, `/leave`, `/leave/balance`, `/leave/approvals`, `/leave/types`, `/contracts`, `/contracts/:id`, `/vendors`, `/vendors/:id`, `/outsourcing`, `/outsourcing/tracking`, `/outsourcing/compliance`, `/vendor-billing`, plus `NotFound`.

**Sidebar grouping** mirrors `AppSidebar.tsx`: main (dashboard, employees, attendance, payroll), workforce (shifts, leave variants, contracts), outsourcing (vendors, placements, tracking, vendor billing, compliance), organization (companies, sites, departments, positions, allowance types, payroll master allowances/deductions).

**Notable dialogs / feature components:** employee and tax/allowance/deduction dialogs, contract dialog, leave dialogs, shift dialog, payroll generate/approval/payslip, vendor dialogs. Full file list lives under `hr-uiux/src/components/` and `hr-uiux/src/pages/`.

---

## 5. Current `hris` baseline

- **Laravel 12**, Vite 7, **Tailwind 4** with `@tailwindcss/vite`.
- **Blade** CRUD and lists: `routes/web.php` registers `employees` resource (except destroy), `attendance` index/store, `payroll` index/store/bulk-update/show. Controllers: `EmployeeController`, `AttendanceController`, `PayrollController`.
- **Layout:** `resources/views/layouts/app.blade.php` with inline CSS (to be superseded by the Inertia shell when the foundation phase lands).

---

## 6. Target architecture

### 6.1 Server

- Controllers return `Inertia::render('PageName', $props)` with validated, JSON-serializable props.
- Mutations use classic Laravel form requests or inline validation, then `redirect()->back()` or `Inertia::location()` as appropriate; validation errors appear on the Inertia `errors` bag.
- New controllers are introduced per domain slice as routes grow beyond the three existing controllers.

### 6.2 Client

- **Pages:** `resources/js/Pages/**` aligned to Inertia page names (PascalCase components, nested folders matching URL segments where helpful).
- **Shared UI:** Port `hr-uiux` `components/ui/*` and layout components into `resources/js/Components/` (or Breeze’s default structure, but keep a **single clear tree** documented in the implementation plan).
- **Navigation:** `@inertiajs/react` `Link` and `router.visit`; **no** `BrowserRouter` as the authority for app URLs.
- **State:** Prefer **server props** for list/detail; use TanStack Query only where it clearly reduces complexity (optional, per-page decision in implementation plan).

### 6.3 Auth

- Install and configure **Breeze (Inertia + React)**. Merge middleware and route table with existing HRIS routes so authenticated users reach the HRIS shell, and **guest** users reach Breeze login/register.
- Reconcile **`/` redirect:** today `web.php` redirects `/` to `employees.index`; after Breeze, define an explicit policy (for example redirect `/` to `/dashboard` when authenticated, and to `/login` when guest) in the implementation plan without leaving duplicate conflicting definitions.

---

## 7. Blade cutover rules

1. For a given **feature slice** (example: employees index + create + show + edit), ship Inertia pages and controller responses first.
2. Remove Blade views for that slice from `resources/views/...` once parity is checked.
3. Keep `resources/views/app.blade.php` (or Breeze root) as the **Inertia root** only; do not maintain a parallel HR-specific Blade layout after the shell migration.

---

## 8. Styling and Tailwind 3 → 4

- **Visual parity** is achieved by carrying over **CSS variables**, component class patterns, and layout structure from `hr-uiux` (`index.css`, `App.css`, shadcn themes).
- `hris` uses **Tailwind 4**; `hr-uiux` uses **Tailwind 3**. The implementation plan will include a **foundation task** to map design tokens (`@theme`, `@layer`, or project conventions) and fix any class-level incompatibilities discovered during the first migrated screen.
- **shadcn** components are ported as React + Radix; dependency versions will be pinned compatible with React 18 and the Breeze/Vite pipeline.

---

## 9. Internationalization

- Preserve **`translations.ts` keys and default strings** from `hr-uiux` so labels match Lovable unless intentionally changed for accuracy.
- Wire strings through the same **LanguageContext** pattern or a thin equivalent (implementation plan picks one approach and sticks to it project-wide).

---

## 10. Phased delivery

| Phase | Scope | Exit criteria |
|-------|--------|----------------|
| **P0 — Foundation** | Breeze Inertia React, Vite entry, root layout, sidebar/header shell, toasts/tooltips, global styles/tokens, `/dashboard` shell page | Authenticated user sees Lovable-equivalent shell; `/` behavior documented and working |
| **P1 — Core modules in Laravel today** | Employees, Attendance, Payroll (match current Eloquent behavior + Lovable UI) | Blade for these features removed; routes preserved or intentionally updated with redirects |
| **P2 — Organization + masters** | Companies, sites, departments, positions, allowance types, payroll master allowances/deductions | Each area has working CRUD/list where schema supports it |
| **P3 — Workforce** | Shifts (list, calendar, assign, detail), Leave (requests, balance, approvals, types), Contracts | Routes + UI parity with `hr-uiux`; backend wired to schema |
| **P4 — Outsourcing + billing** | Vendors, vendor detail, placements, tracking, compliance, vendor billing | Same as P3 |
| **P5 — Hardening** | Reminders/badges if still required, empty states, print flows (payslip), accessibility pass | Checklist in section 11 fully ticked for agreed scope |

Phases may ship as multiple pull requests; order inside a phase follows **risk reduction** (read-heavy pages before complex mutations).

---

## 11. Parity checklist (tick during implementation)

### Shell and global UX

- [ ] `AppLayout` structure (sidebar + main content region)
- [ ] `AppHeader` behavior and actions
- [ ] `AppSidebar` groups, icons, labels, collapse, active states, badges (including reminder counts when implemented)
- [ ] Global CSS variables and theme (light/dark if present in Lovable)
- [ ] Tooltip provider
- [ ] Toaster + Sonner placement
- [ ] NotFound page for unknown GET routes inside the app shell

### Auth

- [ ] Login page UI parity
- [ ] Register page UI parity (if product keeps self-serve register; otherwise hide route per Breeze config)
- [ ] Protected access: guest cannot hit HRIS pages

### Routes (Laravel GET names should mirror Lovable paths)

- [ ] `/dashboard`
- [ ] `/employees`, `/employees/{id}`
- [ ] `/attendance`
- [ ] `/payroll`, `/payroll/{id}`, `/payroll/master-allowances`, `/payroll/master-deductions`
- [ ] `/organization/companies`, `/organization/sites`, `/organization/departments`, `/organization/positions`
- [ ] `/master/allowance-types`
- [ ] `/shifts`, `/shifts/calendar`, `/shifts/assign`, `/shifts/{id}`
- [ ] `/leave`, `/leave/balance`, `/leave/approvals`, `/leave/types`
- [ ] `/contracts`, `/contracts/{id}`
- [ ] `/vendors`, `/vendors/{id}`
- [ ] `/outsourcing`, `/outsourcing/tracking`, `/outsourcing/compliance`
- [ ] `/vendor-billing`

### Shared UI kit (from `hr-uiux/src/components/ui/`)

- [ ] All primitives actually used by ported pages are present and styled (button, card, table, dialog, form, input, select, tabs, sidebar, badge, etc.)

### Feature components and dialogs

- [ ] Employee, allowance, deduction, tax profile dialogs
- [ ] Contract dialog
- [ ] Leave request/decision/type dialogs
- [ ] Shift form dialog
- [ ] Payroll generate, approval card, payslip printable
- [ ] Vendor, vendor PIC, vendor contract dialogs

### Data and domain helpers

- [ ] No remaining imports from `@/integrations/supabase` in `hris` React code
- [ ] `useReminders` equivalent backed by Laravel or consciously deferred with UI fallback documented
- [ ] Domain TS (`attendance`, `payrollFormula`, `tax`) either ported as pure UI helpers consuming props or moved server-side with thin UI — decision recorded per module in implementation plan

---

## 12. Risks and mitigations

| Risk | Mitigation |
|------|------------|
| Breeze scaffold conflicts with existing `web.php` | Dedicated merge task: route list, middleware, `HOME`, redirects |
| Tailwind 4 vs 3 visual drift | Foundation token-mapping task; first screen is the visual canary |
| Large surface area | Strict phase gates; checklist per phase |
| Schema gaps vs Lovable screens | Controller returns 404 or empty-state with clear “not configured” until migration exists |

---

## 13. Verification (design-level)

- **Automated:** PHPUnit for critical controller responses (auth gate, 200 on index routes with factory data where models exist); optional Pest later.
- **Manual:** Side-by-side `hr-uiux` (dev) and `hris` for each completed checklist row: labels, tables, primary buttons, dialogs open/close, form validation errors.

---

## 14. Approval log

- **2026-05-12:** Design sections 1–7 approved verbally in chat (“approved”). This document is the authoritative written record.
