---
description: Activate HRIS QA audit discipline when auditing platform completeness, generating missing-feature backlogs, or before claiming a module is done. Loaded on demand.
---

# HRIS QA Audit — Routing Rule

When the user's request involves **platform completeness**, **gap analysis**,
**what is missing**, **QA review**, or **backlog generation**, use one of
the project's QA mechanisms.

## Trigger phrases

- missing feature, gap analysis, audit, QA review, backlog, completeness
- "what is not implemented", stub, placeholder, schema-only
- demo data coverage, seeder gaps
- before release, phase complete verification

## Two mechanisms — pick one

### 1. In-session skill (default)

Read and follow:

- `.cursor/skills/hris-qa-audit/SKILL.md`

Use for inline audits and updating `docs/qa/missing-features-backlog.md`.

### 2. Read-only sub-agent (deep audit)

Dispatch:

- `.cursor/agents/hris-qa-auditor.md`

Use when a thorough, evidence-backed backlog is needed without implementation.

## Implementation routing

After audit identifies work items, follow:

- `.cursor/skills/hris-feature-delivery/SKILL.md` for each module
- `.cursor/skills/hr-research-indonesia/SKILL.md` for payroll/tax/BPJS law
- `.cursor/skills/product-ux-research/SKILL.md` for navigation/IA gaps

## Database safety

Auditors and implementers must **never** run `migrate:fresh`, `migrate:reset`,
or `db:wipe`. See `.cursor/rules/05-database-safety.mdc`.
