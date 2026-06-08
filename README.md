<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

# HRIS — Schema (Phase 1)

This Laravel project hosts the implementation of an Indonesian HRIS
SaaS. The product-discovery workspace (prompts, blueprint, schema
reference) lives in the sibling repo `../../HRIS/product-discovery/`.

## Local setup (XAMPP / MySQL)

```bash
# 1. Ensure XAMPP MySQL is running and the DB exists:
#    create database db_hris; (in phpMyAdmin or `mysql -u root`)
# 2. Install dependencies (already done if vendor/ is present):
composer install
# 3. Generate app key (already set in .env if APP_KEY is filled):
php artisan key:generate
# 4. Run migrations:
php artisan migrate
```

The `.env` file is pre-configured for the XAMPP defaults:
`DB_HOST=127.0.0.1`, `DB_DATABASE=db_hris`, `DB_USERNAME=root`,
`DB_PASSWORD=` (empty).

## Table-prefix convention

Every business table uses a 3- or 4-letter prefix so the schema is
self-documenting from `phpMyAdmin`. Source: `.cursor/rules/10-migrations-and-models.mdc`.

| Prefix | Domain |
|---|---|
| `sys_` | SaaS / system primitives |
| `sub_` | subscription plans |
| `bill_`| billing payments |
| `org_` | organization (companies, sites, departments, positions) |
| `emp_` | employee domain (master, identities, jobs, contracts, allowances, loans) |
| `att_` | attendance & shift templates |
| `lv_`  | leave |
| `ot_`  | overtime |
| `pay_` | payroll |
| `trx_` | transactional (recruitment) |
| `rel_` | pivot / link tables |
| `cfg_` | configuration (BPJS, tax rules, salary components) |

Default Laravel framework tables (`users`, `cache`, `cache_locks`,
`jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens`,
`sessions`, `migrations`) keep their unprefixed names — they are
infrastructure, not HRIS business data.

## Tables created (33)

| Domain | Tables |
|---|---|
| SaaS / system | `sys_tenants`, `sub_plans`, `sub_subscriptions`, `bill_payments` |
| Organization | `org_companies`, `org_sites`, `org_departments`, `org_positions` |
| Employee master | `emp_employees`, `emp_identities`, `emp_family_members`, `emp_emergency_contacts`, `emp_bank_accounts`, `emp_tax_profiles` |
| Employment | `emp_jobs`, `rel_employee_sites`, `rel_vendor_employees` |
| Attendance & shift | `att_shifts`, `att_attendances`, `rel_employee_shifts` |
| Leave & overtime | `lv_leaves`, `ot_overtimes` |
| Contract | `emp_contracts` |
| Payroll | `cfg_salary_components`, `pay_payrolls`, `pay_payroll_items` |
| Benefit & loan | `emp_allowances`, `emp_loans` |
| Recruitment | `trx_jobs`, `trx_candidates`, `trx_applications` |
| Configuration | `cfg_bpjs`, `cfg_tax_rules` |

Eloquent models for all 33 live in `app/Models/`. Class names are
prefix-stripped (`emp_employees` → `Employee`, `org_companies` →
`Company`, `att_attendances` → `Attendance`). Recruitment job postings
are `trx_jobs` → `JobPosting` (the suffix avoids confusion with the
Laravel queue's `Job` class).

## Verification commands

```bash
php artisan migrate              # apply all migrations
php artisan migrate:rollback     # roll back the most recent batch
php artisan migrate:status       # show which migrations are applied
php artisan tinker               # REPL to query models, e.g. App\Models\Employee::count()
```

> DATABASE SAFETY (mandatory): NEVER run `php artisan migrate:fresh`,
> `migrate:reset`, or `db:wipe` — `db_hris` holds hand-built test data.
> See `.cursor/rules/05-database-safety.mdc`.

## Demo data on the production server

Deploy uses `composer install --no-dev`. `fakerphp/faker` is a **production**
dependency (required by `HrisIndonesiaDemoSeeder`), so it is installed on every
deploy.

After deploy, SSH to the server and run (Plesk PHP 8.3 example):

```bash
cd /var/www/vhosts/mitraprimasolusindo.com/hris
PHP=/opt/plesk/php/8.3/bin/php

$PHP artisan migrate --force
$PHP artisan db:seed --class=PermissionSeeder --force
$PHP artisan db:seed --class=RoleSeeder --force
$PHP artisan db:seed --class=HrisIndonesiaDemoSeeder --force
$PHP artisan optimize:clear
```

Use **`HrisIndonesiaDemoSeeder`** (not `HrisIndonesiaDemoSupplementSeeder` alone).
The main seeder creates the demo company and employees, then runs the supplement
automatically.

Login: `admin@demo.hris.local` / `password`

If you see `Class "Faker\Factory" not found` before this fix is deployed, run
once on the server: `$PHP /path/to/composer.phar require fakerphp/faker --no-interaction`

## Out of scope (Phase 1)

- UI / Blade views, `resources/js`, `resources/css` — no front-end work yet.
- Controllers, form requests, policies, observers, factories, seeders.
- Multi-tenancy plumbing beyond the `tenant_id` column (no global scope, no middleware).
- Auth redesign — Laravel default `User` model and `users` table are unchanged.
- BPJS percentages and PPh21 brackets are NOT seeded; the `cfg_bpjs`
  and `cfg_tax_rules` tables are intentionally empty until values are
  sourced via the Indonesian HR research skill (see
  `.cursor/skills/hr-research-indonesia/SKILL.md`).

## Phase 1 web modules (employee, attendance, payroll basic)

This repository now includes basic route-level parity for core Phase 1
flows from the UI source:

- `employees`:
  - list + filter (`search`, `status`)
  - create employee
  - edit employee
- `attendance`:
  - daily list/reporting (`date`, `site_id`)
  - attendance capture (clock in/out, status)
- `payroll`:
  - list by period (month/year)
  - generate payroll per employee and period
  - payroll detail with generated earning/deduction items

### Important payroll compliance note

Payroll generation reads statutory percentages from configuration tables
(`cfg_bpjs`, `cfg_tax_rules`) when available. No statutory percentage is
hard-coded in calculator logic. Keep regulatory values maintained through
the HR research discipline (`.cursor/skills/hr-research-indonesia/SKILL.md`).

## Porting guardrails

Porting `hr-uiux` (Lovable) into this repo runs in **four ordered phases**
(1 Core → 2 Operations → 3 Outsourcing → 4 Recruitment). Do not skip
phases. Full acceptance criteria:
[`../../HRIS/product-discovery/output/prompt/coding/02-laravel-vite-port-from-loveable-phased.md`](../../HRIS/product-discovery/output/prompt/coding/02-laravel-vite-port-from-loveable-phased.md).

| Mechanism | When to use |
|---|---|
| **Skill** — `.cursor/skills/laravel-port-guardrail/SKILL.md` | During implementation: declare active phase, parity checklist, schema/scope limits, run verification before claiming done. |
| **Subagent** — `.claude/agents/laravel-port-guardrail.md` | After each phase or before merge: read-only PASS/FAIL review with actionable blockers. |

Routing rule: `.cursor/rules/21-laravel-port-guardrail.mdc`.

**Regulated Indonesian HR logic** (PPh21, BPJS, PKWT/PKWTT, THR,
outsourcing) still uses `hr-research-indonesia` /
`hr-researcher-indonesia` — the port guardrail only enforces that
delegation happened.

## Working with AI agents in this codebase

See `AGENTS.md` for the agent orientation. Key files:

- `.cursor/rules/00-laravel-codebase.mdc` — tech stack & scope.
- `.cursor/rules/10-migrations-and-models.mdc` — schema conventions.
- `.cursor/rules/20-hr-research-indonesia.mdc` — when to invoke the HR skill / sub-agent.
- `.cursor/rules/21-laravel-port-guardrail.mdc` — when to invoke port guardrail skill / sub-agent.
- `.cursor/skills/hr-research-indonesia/SKILL.md` — Indonesian HR / payroll / tax / BPJS / contract reasoning.
- `.cursor/skills/laravel-port-guardrail/SKILL.md` — phased porting discipline and verification.
- `.claude/agents/hr-researcher-indonesia.md` — sub-agent for deep regulation-cited research.
- `.claude/agents/laravel-port-guardrail.md` — sub-agent for phase-gate port reviews (PASS/FAIL).
