---
name: hris-qa-audit
description: |
  Audit the Laravel HRIS codebase against the product blueprint and database
  schema. Classify gaps (severity, module, acceptance criteria) and emit a
  prioritized missing-feature backlog. Use when completing a module, before a
  release, or when asked "what is missing". Dispatch hris-qa-auditor for
  read-only deep audits.
priority: high
---

# Skill: HRIS QA Audit

In-session discipline for auditing **implementation completeness** of the
Indonesian HRIS Laravel codebase against:

- `../../hris-product-discovery/output/reference/hris-system-blueprint.md`
- `../../hris-product-discovery/output/reference/hris-saas-database-structure.md`
- Routes in `routes/web.php`, controllers under `app/Http/Controllers/`
- Inertia pages under `resources/js/Pages/`
- Seeders under `database/seeders/`

> For a **read-only deep audit**, dispatch
> [`.cursor/agents/hris-qa-auditor.md`](../../agents/hris-qa-auditor.md).

---

## When to activate

- "What is missing?", "audit the platform", "QA review", "backlog"
- Before claiming a phase or module is complete
- After a large feature batch, before merge
- When demo data does not exercise a module's UI

---

## Audit procedure

1. **Inventory routes** — `routes/web.php` + `routes/auth.php`; map to controllers.
2. **Inventory pages** — `resources/js/Pages/`; cross-check `AppSidebar.tsx` nav items.
3. **Inventory schema** — migrations + models; flag tables with no routes or seeders.
4. **Compare to blueprint** — Core HR, Workforce Operations, Talent & Growth layers.
5. **Classify each gap** using the matrix below.
6. **Prioritize** — P0 (blocks demo/compliance), P1 (module incomplete), P2 (polish).

---

## Gap classification

| Severity | Meaning | Example |
|---|---|---|
| **P0** | Compliance or core workflow broken | Payroll ignores TER; no BPJS admin |
| **P1** | Module stubbed or schema-only | Talent placeholders; overtime no UI |
| **P2** | UX polish / orphan permissions | Hardcoded dashboard charts; dead nav dupes |

| Status | Meaning |
|---|---|
| `missing` | No route/controller/page |
| `stub` | Route exists but placeholder UI or empty controller |
| `partial` | CRUD incomplete or read-only preview |
| `schema-only` | Model + migration, no application layer |
| `data-gap` | Feature exists but demo seeder empty |

---

## Backlog output format

Write or update `docs/qa/missing-features-backlog.md` with:

```markdown
# HRIS Missing Features Backlog

> Generated: YYYY-MM-DD | Auditor: hris-qa-audit

## Priority summary
- P0: N items
- P1: N items
- P2: N items

## Backlog (ordered)

### P0-001 — <title>
- **Module:** payroll | attendance | …
- **Status:** missing | stub | partial | schema-only | data-gap
- **Evidence:** <file paths>
- **Acceptance criteria:**
  - [ ] …
- **Dependencies:** …
```

Each item must have testable acceptance criteria and file evidence.

---

## Module checklist (quick scan)

| Module | Route prefix | Expected minimum |
|---|---|---|
| Employees | `employees` | CRUD + sub-resources + policy |
| Attendance | `attendance` | List + create + edit/delete |
| Payroll | `payroll` | Generate + TER/BPJS + cfg admin |
| Leave | `leave` | Request + approval + entitlement |
| Shifts | `shifts` | CRUD + assign + calendar |
| Contracts | `contracts` | CRUD + expiry reminders |
| Outsourcing | `outsourcing`, `vendors` | Placements + billing + compliance |
| Recruitment | `recruitment` | Jobs + candidates + pipeline + interviews |
| Talent | `performance`, `training`, … | Full CRUD, not Placeholder |
| Org | `organization` | Companies/sites/depts/positions |
| Admin | `admin` | Users + roles |
| SaaS | tenants/subscriptions | Admin CRUD |

---

## Routing to other skills

| Topic | Route to |
|---|---|
| Indonesian payroll/tax/BPJS law | `hr-research-indonesia` |
| Navigation/IA/form UX | `product-ux-research` |
| hr-uiux porting parity | `laravel-port-guardrail` |
| Implementing a backlog item | `hris-feature-delivery` |

---

## Done condition

Audit is complete when `docs/qa/missing-features-backlog.md` exists,
every blueprint module is scored, items are prioritized P0→P2, and each
item has acceptance criteria + evidence paths.
