# AGENTS.md — HRIS Laravel codebase

This file orients any AI coding agent (Cursor, Claude Code, Copilot CLI,
SDK-driven, etc.) working inside `c:\xampp\htdocs\hris\`.

## What this repo is

The **implementation codebase** for an Indonesian HRIS SaaS, written in
Laravel 12 + MySQL on XAMPP. The product-discovery workspace
(prompts, references, blueprint) lives in a **sibling repo** at
`../../HRIS/product-discovery/`.

## Read these first

In order of priority:

1. `.cursor/rules/00-laravel-codebase.mdc` — tech stack, folder layout,
   current phase, scope boundaries.
2. `.cursor/rules/10-migrations-and-models.mdc` — table-prefix rule,
   model naming, FK pattern, soft-delete policy, money columns.
3. `.cursor/rules/20-hr-research-indonesia.mdc` — when and how to invoke
   the HR research skill or sub-agent.
4. `.cursor/rules/21-laravel-port-guardrail.mdc` — when porting from
   `hr-uiux` or closing a porting phase gate.
5. `.cursor/rules/22-product-ux-research.mdc` — when and how to invoke
   the product/UX research skill or sub-agent.
6. `.cursor/rules/23-hris-qa-audit.mdc` — when and how to invoke
   the QA audit skill or sub-agent for gap analysis and backlogs.
7. `.cursor/skills/hr-research-indonesia/SKILL.md` — in-session
   reasoning skill for Indonesian payroll / tax / BPJS / contracts.
8. `.cursor/skills/laravel-port-guardrail/SKILL.md` — phased porting,
   parity, verification, and HR-research routing during ports.
9. `.cursor/skills/product-ux-research/SKILL.md` — in-session
   reasoning skill for navigation, IA, dashboard/form UX, and
   competitor benchmarking.
10. `.cursor/skills/hris-qa-audit/SKILL.md` — in-session platform
    completeness audit and missing-feature backlog generation.
11. `.cursor/skills/hris-feature-delivery/SKILL.md` — end-to-end
    recipe to ship one HRIS module (schema → UI → seeder → tests).
12. `.cursor/agents/hris-qa-auditor.md` — read-only sub-agent for
    deep platform audits and prioritized backlogs.
13. `.claude/agents/hr-researcher-indonesia.md` — sub-agent for deeper
   regulation-cited research with browsing.
14. `.claude/agents/laravel-port-guardrail.md` — read-only PASS/FAIL
    review at phase gates (porting from `hr-uiux`).
15. `.claude/agents/product-ux-researcher.md` — sub-agent for deeper
    UX/IA/competitor research with browsing.
16. (Reference, in sibling repo)
    `../../HRIS/product-discovery/output/reference/hris-system-blueprint.md`
    — conceptual blueprint.
17. (Reference, in sibling repo)
    `../../HRIS/product-discovery/output/reference/hris-saas-database-structure.md`
    — schema source of truth.
18. `docs/qa/missing-features-backlog.md` — prioritized gap backlog
    maintained by the QA audit skill/agent.

## Current phase

**Phase 2+ — Full-stack HRIS implementation.** The repo contains the
33-table schema (migrations + Eloquent models), Laravel controllers/routes,
and an Inertia/React frontend (`resources/js/`). Feature modules include
employees, attendance, payroll, leave, shifts, contracts, outsourcing,
recruitment, talent, and organization management. See
`.cursor/rules/00-laravel-codebase.mdc` § "Current implementation phase"
for scope boundaries.

## Common operations

```bash
# Run all migrations on db_hris
php artisan migrate

# Roll back the latest batch
php artisan migrate:rollback

# Inspect what's applied
php artisan migrate:status

# Open a tinker REPL with all models loaded
php artisan tinker
```

> DATABASE SAFETY (mandatory): NEVER run `php artisan migrate:fresh`,
> `migrate:reset`, or `db:wipe` on `db_hris` — it holds the developer's
> test data. See `.cursor/rules/05-database-safety.mdc`.

## Indonesian-HR rule (mandatory)

Anything that touches **payroll math, allowances, deductions,
contract employment (PKWT / PKWTT), outsourcing / alih daya, BPJS, or
PPh21** must either cite the relevant regulation inline (UU / PP / PMK
+ pasal) **or** invoke the HR research skill / sub-agent. See
`.cursor/rules/20-hr-research-indonesia.mdc`.
