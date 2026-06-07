---
description: Conventions for migrations and Eloquent models in the HRIS Laravel codebase. Always applied when touching database/migrations/ or app/Models/.
alwaysApply: true
---

# Migrations & Models — Conventions

## Table-name prefixes (mandatory)

Every business table uses a 3- or 4-letter prefix. Source of truth:
`../../HRIS/product-discovery/output/reference/hris-saas-database-structure.md`.

| Prefix | Domain |
|---|---|
| `mst_` | master data (static / reference) |
| `org_` | organization structure |
| `emp_` | employee domain |
| `trx_` | transactional (recruitment, applications) |
| `att_` | attendance |
| `pay_` | payroll |
| `lv_`  | leave |
| `ot_`  | overtime |
| `rel_` | pivot / many-to-many link tables |
| `sys_` | SaaS / system primitives (tenants) |
| `sub_` | subscription / billing plan |
| `bill_`| billing payment events |
| `cfg_` | configuration tables |

Default Laravel tables (`users`, `cache`, `cache_locks`, `jobs`,
`job_batches`, `failed_jobs`, `password_reset_tokens`, `sessions`,
`migrations`) keep their unprefixed names — they are framework
infrastructure, not HRIS business data.

## Model class naming

- Class name is the **prefix-stripped, singular, PascalCase** form.
  Example: `org_companies` → `Company`, `emp_employees` → `Employee`,
  `att_attendances` → `Attendance`.
- Always declare `protected $table = 'prefixed_name';` because Laravel
  cannot infer prefixed table names from class names.
- Class collision avoidance:
  - `trx_jobs` (recruitment) → model `JobPosting` (not `Job`, to avoid
    confusion with `Illuminate\Queue\Job`).

## Foreign keys

- Use `foreignId('xxx_id')->constrained('prefixed_table')` because the
  default Laravel inference doesn't know about prefixes.
- Self-references and cross-prefix references use the explicit form:
  `$table->foreignId('manager_id')->nullable()->references('id')->on('emp_employees')->nullOnDelete();`
- Cascade policy:
  - **`restrictOnDelete()`** on master refs (company, department,
    position, plan).
  - **`cascadeOnDelete()`** on child / pivot rows (employee_identities,
    payroll_items, employee_shifts, etc.).
  - **`nullOnDelete()`** on optional refs (`tenant_id`, `manager_id`,
    `approved_by`).

## Column conventions

- **Money / amounts:** `decimal(18, 2)` — IDR, no fractional sub-cent.
- **Percentages:** `decimal(7, 4)` — store `0.0570` for 5,70%.
- **Status / type:** `string()` (varchar), NOT MySQL `enum`. Document
  allowed values in the model's PHPDoc.
- **Dates:** `date()` for plain dates (birth, join, period_start);
  `dateTime()` for clock_in/clock_out and similar timestamps.
- **GPS:** `decimal(10, 7)` for `latitude` and `longitude`.
- **Timestamps:** every table has `$table->timestamps()`.
- **Soft delete:** master / employee tables get `$table->softDeletes()`
  + `use SoftDeletes;` in the model. Append-only transactional tables
  (`att_attendances`, `pay_payrolls`, `pay_payroll_items`,
  `bill_payments`) do NOT get soft delete — once posted they're
  immutable.

## Tenant scoping

- `tenant_id` (nullable, FK → `sys_tenants`) appears only on top-level
  tenant-scoped tables: `sub_subscriptions`, `bill_payments`,
  `org_companies`, `emp_employees`. Other tables inherit tenancy via
  the FK chain.
- Don't add a global scope or middleware in this phase — defer
  multi-tenancy plumbing.

## Indexes

- Composite index on `att_attendances(employee_id, clock_in)` — high-
  volume query pattern.
- Unique on `(tenant_id, employee_code)` for `emp_employees`.
- Unique on `(tenant_id, name)` for `org_companies`.
- Add domain-specific indexes when adding features; document them in
  the migration.

## Migration filename convention

`YYYY_MM_DD_NNNNNN_create_<prefixed_table>_table.php`

Where `NNNNNN` is a 6-digit sequence so multiple migrations on the same
date sort deterministically (the default Laravel timestamp resolution
is per-second, which can collide on a fast scaffold).

## down() correctness

Every migration must drop in reverse FK order so `php artisan
migrate:rollback` then `php artisan migrate` round-trips cleanly. This
is non-negotiable — it's part of the scaffold acceptance criteria.

## What NOT to do

- Do not use MySQL `enum` columns.
- Do not hard-code BPJS percentages or PPh21 brackets in code — they
  belong in `cfg_bpjs` / `cfg_tax_rules` and are sourced via the
  HR research skill (see `20-hr-research-indonesia.mdc`).
- Do not rename tables to drop the prefix to "match Laravel
  conventions". The prefix IS the convention here.
- Do not add factories or seeders in this phase unless asked.
