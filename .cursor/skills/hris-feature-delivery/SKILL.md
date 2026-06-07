---
name: hris-feature-delivery
description: |
  End-to-end recipe to ship one HRIS module in the Laravel hris codebase:
  migration, model, controller, routes, permissions, Form Request, Inertia
  page, sidebar nav, i18n, demo seeder rows, and tests. Routes payroll/HR
  law questions to hr-research-indonesia.
priority: high
---

# Skill: HRIS Feature Delivery

Step-by-step recipe for implementing **one HRIS module** from schema to
working UI with demo data. Follow in order; do not skip verification.

> Regulated math (PPh21, BPJS, THR, overtime PP 35/2021) **must** go through
> [`hr-research-indonesia`](../hr-research-indonesia/SKILL.md) — never
> hard-code statutory rates in PHP.

---

## When to activate

- Implementing a backlog item from `docs/qa/missing-features-backlog.md`
- Adding a new module (Talent, Overtime, Interviews, Billing, etc.)
- Replacing a `Talent/Placeholder` page with real functionality

---

## Delivery checklist (one module)

### 1. Schema (if new tables)

- [ ] Migration: `YYYY_MM_DD_NNNNNN_create_<prefixed_table>_table.php`
- [ ] Follow `.cursor/rules/10-migrations-and-models.mdc` (prefix, FKs, soft delete policy)
- [ ] Model in `app/Models/` with `protected $table`
- [ ] `down()` drops in reverse FK order

### 2. Backend

- [ ] Controller in `app/Http/Controllers/<Module>/`
- [ ] Form Request(s) in `app/Http/Requests/` (or inline validate for simple masters)
- [ ] Service class if business logic > ~30 lines (`app/Services/<Module>/`)
- [ ] Routes in `routes/web.php` with `can:permission.key` middleware
- [ ] Permissions in `app/Support/PermissionCatalog.php` + run `PermissionSeeder` in tests
- [ ] Policy if model-level authorization needed (employees pattern)

### 3. Frontend (Inertia + React)

- [ ] Page(s) under `resources/js/Pages/<Module>/`
- [ ] Use `HrisLayout`, existing UI primitives (`Components/ui/`)
- [ ] Wire props from controller; use `router` / `useForm` for mutations
- [ ] Nav entry in `resources/js/Components/layout/AppSidebar.tsx` (if new top-level item)
- [ ] i18n keys in `resources/js/i18n/translations.ts` (EN + ID)

### 4. Demo data

- [ ] Seeder method in `HrisIndonesiaDemoSeeder` or dedicated seeder class
- [ ] Idempotent guard (skip if marker row exists)
- [ ] Uses `IndonesianDemoData` + Faker `id_ID`

### 5. Tests

- [ ] Feature test in `tests/Feature/` covering happy path + permission gate
- [ ] Run `php artisan test --filter=<Module>`

### 6. Verification

```bash
php artisan migrate          # apply new migrations only
php artisan db:seed --class=HrisIndonesiaDemoSeeder  # additive demo
npm run build
php artisan test
```

**Never** `migrate:fresh` — see `.cursor/rules/05-database-safety.mdc`.

---

## File touch map (typical module)

| Layer | Paths |
|---|---|
| Migration | `database/migrations/` |
| Model | `app/Models/` |
| Controller | `app/Http/Controllers/` |
| Service | `app/Services/` |
| Request | `app/Http/Requests/` |
| Routes | `routes/web.php` |
| Permissions | `app/Support/PermissionCatalog.php` |
| Page | `resources/js/Pages/` |
| Nav | `resources/js/Components/layout/AppSidebar.tsx` |
| i18n | `resources/js/i18n/translations.ts` |
| Seeder | `database/seeders/HrisIndonesiaDemoSeeder.php` |
| Test | `tests/Feature/` |

---

## Patterns to follow

- **Master CRUD:** reuse `Components/master/MasterCrudPage.tsx`
- **Employee sub-resources:** `Employee/*Controller` + `Employee*Service`
- **Payroll config:** `cfg_*` tables, admin CRUD, read in calculation service
- **Reminders:** extend `ReminderSummaryService` for expiry/workflow counts

---

## QA gate

After delivery, dispatch `hris-qa-auditor` or run `hris-qa-audit` skill to
mark the backlog item done and check for regressions.

---

## Done condition

Module is done when: routes work, page renders with real data, permissions
enforce, demo seeder populates the module's tables, tests pass, and the
backlog item is checked off in `docs/qa/missing-features-backlog.md`.
