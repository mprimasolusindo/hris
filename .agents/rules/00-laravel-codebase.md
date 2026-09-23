---
description: Always-on context for the HRIS Laravel codebase — tech stack, folder layout, current phase, and the no-UI guardrail.
alwaysApply: true
---

# HRIS Laravel Codebase — Working Context

## What this repo is

This is the **production-track Laravel codebase** for an Indonesian
HRIS (Human Resource Information System) SaaS. The product-discovery
workspace (prompts, references, blueprint) lives in a **sibling repo**
at `../../HRIS/product-discovery/`. Treat the discovery repo as
read-only reference material; all implementation goes here.

## Tech stack

- **Laravel 12** (PHP 8.2+) — full-stack framework
- **MySQL 8** via XAMPP — database (`db_hris`)
- **Vite 6** — asset bundler (already configured; do NOT touch in this phase)
- **Composer** — PHP dependencies
- **PHPUnit 11** — testing (Pest is allowed but not yet wired)

## Folder layout

```
.
├── app/
│   ├── Models/            ← Eloquent models (one per table, prefix-stripped class names)
│   ├── Http/Controllers/  ← (later phases)
│   └── ...
├── database/
│   ├── migrations/        ← Schema migrations (prefixed table names, see 10-migrations-and-models.mdc)
│   ├── seeders/           ← (later phases)
│   └── factories/         ← (later phases)
├── resources/             ← Vite assets, Inertia/React frontend (resources/js/)
├── config/                ← Laravel config
├── .cursor/
│   ├── rules/             ← This file + 10/20/21/22/30-* rules
│   └── skills/            ← hr-research-indonesia, product-ux-research, laravel-port-guardrail skills
├── .claude/agents/        ← hr-researcher-indonesia, product-ux-researcher, laravel-port-guardrail sub-agents
└── AGENTS.md              ← root agent guide (points here)
```

## Current implementation phase

**Phase 2+ — Full-stack HRIS implementation.** The repo currently contains:
- All 33 HRIS migrations + matching Eloquent models for the schema in
  `../../HRIS/product-discovery/output/reference/hris-saas-database-structure.md`.
- Laravel controllers, routes, and an Inertia/React frontend
  (`resources/js/`).
- HR research skill + sub-agent for Indonesian-domain questions.
- Product/UX research skill + sub-agent for navigation, IA, and
  competitor benchmarking.

**Out of scope unless explicitly requested:**
- Auth redesign (keep Laravel default `User` model).
- Multi-tenancy plumbing beyond the `tenant_id` column (no global
  scope, no middleware).
- Deployment, CI/CD, containerization.

If a user request would push past these boundaries, surface the boundary
explicitly and ask before proceeding.

## Domain context (essential)

- **Target market:** Indonesia. Multi-tenant SaaS, multi-company,
  multi-site. Strong outsourcing / alih daya support is a deliberate
  product differentiator.
- **Critical Indonesian compliance:** payroll tax (PPh21 — TER per PMK
  168/2023), BPJS Kesehatan + Ketenagakerjaan (JHT, JP, JKK, JKM, JKP),
  contract types (PKWT / PKWTT), severance / pesangon, THR, overtime
  (PP 35/2021).
- **Conceptual model:** three layers — Core HR, Workforce Operations,
  Talent & Growth. Outsourcing crosses all three. See
  `../../HRIS/product-discovery/output/reference/hris-system-blueprint.md`.

## Routing rules to other context

- Migration / model conventions → `10-migrations-and-models.mdc`
- Indonesian HR questions → `20-hr-research-indonesia.mdc`
- Indonesian HR cheat sheet → `30-indonesian-hr-glossary.mdc`
- Product / UX / navigation / IA questions → `22-product-ux-research.mdc`
- Deeper HR research (browsing) → dispatch
  `.claude/agents/hr-researcher-indonesia.md`
- Deeper UX/IA research (browsing) → dispatch
  `.claude/agents/product-ux-researcher.md`
