---
name: laravel-port-guardrail
description: |
  Use when porting hr-uiux (Lovable) features into the Laravel hris
  codebase, reviewing a phase gate, or before claiming a porting task
  is complete. Enforces phased build order, prefixed schema conventions,
  files-to-touch scope, verification commands, and HR-research routing.
priority: high
---

# Skill: Laravel Port Guardrail

Discipline for porting the Lovable/React UI in `c:/xampp/htdocs/hr-uiux`
into this Laravel 12 + Vite codebase. Use it on every porting session,
phase gate, and completion claim.

> For **regulated Indonesian HR logic** (PPh21, BPJS, PKWT/PKWTT, THR,
> outsourcing), this skill does **not** replace
> [`hr-research-indonesia`](../hr-research-indonesia/SKILL.md) — it
> **routes** to that skill or
> [`hr-researcher-indonesia`](../../../.claude/agents/hr-researcher-indonesia.md).
> Do not duplicate legal content here.

> For a **read-only phase review** with PASS/FAIL, dispatch
> [`laravel-port-guardrail`](../../../.claude/agents/laravel-port-guardrail.md)
> at phase gates or before merge.

---

## When to activate

Activate when **any** of the following appear in the user's message,
your plan, or the files you're editing:

**Porting triggers:** port, porting, Lovable, loveable, `hr-uiux`,
migrate UI, parity, phase gate, Phase 1–4 module work.

**Module / phase names:** Employee, Attendance, Payroll (basic), Shift,
Leave, Contract, Outsourcing, Vendor, Recruitment, Talent.

**Code / path signals:** new or changed files under `routes/`,
`resources/`, `app/Http/Controllers/`, Blade views, Vite entrypoints,
or migrations/models added during a port (not pure schema-scaffold work).

**Completion claims:** "phase complete", "ready for next phase", "port
done", "parity achieved".

---

## Phase gate (hard stop)

Build order is **fixed**. Declare the active phase at the start of every
porting session:

| Phase | Name | Modules (from discovery prompt 02) |
|---|---|---|
| **1** | Core | Employee + Attendance + Payroll basic |
| **2** | Operations | Shift + Leave + Contract |
| **3** | Differentiator | Outsourcing + Vendor management |
| **4** | Strategic | Talent / Recruitment |

**Rules:**

1. Do **not** implement routes, controllers, models, or UI for a later
   phase until the current phase's acceptance criteria are met **and**
   the user or reviewer approves advancing.
2. At each phase end, fill the **Completion report template** (below)
   and run verification commands.
3. Prefer dispatching the
   [`laravel-port-guardrail`](../../../.claude/agents/laravel-port-guardrail.md)
   subagent for an independent PASS/FAIL before claiming "ready for next
   phase".
4. Master porting spec (sibling discovery repo):
   `../../../../HRIS/product-discovery/output/prompt/coding/02-laravel-vite-port-from-loveable-phased.md`

### Phase acceptance (summary)

- **Phase 1:** Employee CRUD/profile; attendance capture/list/report;
  payroll basic E2E; no hard-coded statutory rates (use `cfg_*`).
- **Phase 2:** Shift list/assignment; leave lifecycle; contract
  lifecycle; role-sensitive actions guarded.
- **Phase 3:** Vendor profiles; employee–vendor–site mapping; outsourcing
  workspace; compliance flags for expiring/mismatched records.
- **Phase 4:** Recruitment/talent route parity; pipeline tests; stable
  employee integration.

Full criteria live in prompt 02 — do not skip checklist items.

---

## Parity discipline

### Before coding

1. In `hr-uiux` (read-only), list routes, pages, and components that map
   to the active phase module(s).
2. Note filters, status chips, CRUD actions, and role constraints visible
   in the source UI.
3. Record the list in your plan or session notes (file paths in `hr-uiux`).

### After coding

Produce a **parity checklist** — one row per source feature:

| hr-uiux item | Laravel route | Handler | Status (missing / partial / done) |
|---|---|---|---|
| … | … | … | … |

Gaps must be **documented**, not silently dropped. Enhancements wait until
parity is done unless the user explicitly defers an item.

---

## Schema discipline

Follow `.cursor/rules/10-migrations-and-models.mdc` and the schema
reference in the discovery repo
(`hris-saas-database-structure.md`).

| Rule | Requirement |
|---|---|
| Table names | Prefixed (`org_`, `emp_`, `att_`, `pay_`, …) — never drop prefixes |
| Status / type columns | `string()` — **no** MySQL `enum` |
| Money | `decimal(18, 2)` (IDR) |
| Percentages | `decimal(7, 4)` |
| Foreign keys | `foreignId(...)->constrained('prefixed_table')` or explicit `references()->on()` |
| Soft delete | Per table policy in `10-migrations-and-models.mdc` |
| Rollback | `down()` drops in reverse FK order; `migrate:rollback` then `migrate` must round-trip |
| Statutory values | **No** hard-coded BPJS/PPh21 — use `cfg_bpjs` / `cfg_tax_rules` |

New migrations only when the ported feature needs schema not already in
the 33-table scaffold — justify each new table/column.

---

## Scope discipline

1. Respect **Files-to-touch** from the active discovery prompt (usually
   prompt 02 for feature work).
2. Do **not** expand scope (refactors, unrelated modules, new deps) without
   asking the user first.
3. Do **not** edit `hr-uiux` — it is read-only parity reference.
4. Do **not** weaken `.cursor/rules/00-laravel-codebase.mdc` or
   `10-migrations-and-models.mdc`.
5. One phase per session when possible; checkpoint commits per module
   inside a phase when the user wants git history.

---

## Verification before completion

Do **not** say "done", "complete", or "ready for next phase" without
running applicable commands and pasting output (or an explicit failure
report with root cause).

### Every phase gate

```bash
php artisan test
php artisan migrate:status
php artisan migrate:rollback    # latest batch only
php artisan migrate
npm run build
```

### Before final handoff (after Phase 4 or large merge)

```bash
php artisan test
php artisan route:list
```

If a command fails: **stop**, report the exact failure, hypothesize cause,
list remediation options. Do not hide failures.

### Guardrail artifact sanity (after creating/updating guardrails)

```powershell
Test-Path .cursor\skills\laravel-port-guardrail\SKILL.md
Test-Path .claude\agents\laravel-port-guardrail.md
Test-Path .cursor\rules\21-laravel-port-guardrail.mdc
php artisan about
```

---

## Indonesian HR routing

When port work touches **any** of: PPh21, TER, BPJS, PKWT/PKWTT, THR,
pesangon, outsourcing/alih daya, payroll math, allowances/deductions,
contract employment, or `cfg_bpjs` / `cfg_tax_rules` / `pay_payroll_items`:

1. Read and follow
   [`.cursor/skills/hr-research-indonesia/SKILL.md`](../hr-research-indonesia/SKILL.md),
   **or**
2. Dispatch
   [`.claude/agents/hr-researcher-indonesia.md`](../../../.claude/agents/hr-researcher-indonesia.md)
   for deeper regulation research.

**Block completion** if regulated logic ships with only
`[unverified — needs research]` claims and no citation or HR research
artifact. Routing rule: `.cursor/rules/20-hr-research-indonesia.mdc`.

---

## Completion report template

Fill this at the end of each phase (copy into chat or PR description):

```markdown
## Phase gate report — Phase N

- Active phase: N — <name>
- hr-uiux parity: <N>/<M> items checked
- Files touched: <list>
- Verification: <pass|fail + command output summary>
- HR research: <citations attached | not applicable | blocked — reason>
- Ready for next phase: <yes|no>
```

---

## Escalation

| Situation | Action |
|---|---|
| Implementing within an **approved** active phase | Use **this skill** inline |
| End of phase, before merge/PR, or user asks for review | Dispatch **`laravel-port-guardrail` subagent** (read-only PASS/FAIL) |
| Regulated HR rule unclear or needs browsing | Dispatch **`hr-researcher-indonesia`** (not the port guardrail subagent) |
| Subagent returns **FAIL** | Fix blockers; re-run verification; re-dispatch or get explicit user override |
| Scope creep or wrong phase detected mid-work | Stop implementation; report; ask user to confirm phase or defer work |

Parent may proceed to the next phase only on subagent **PASS** or
**documented user override** in the session.

---

## Repository pointers

| Item | Path |
|---|---|
| Source UI (read-only) | `c:/xampp/htdocs/hr-uiux` |
| Porting prompt | `../../../../HRIS/product-discovery/output/prompt/coding/02-laravel-vite-port-from-loveable-phased.md` |
| Guardrail setup prompt | `../../../../HRIS/product-discovery/output/prompt/coding/03-laravel-port-guardrail-skill-subagent.md` |
| Schema SOOT | `../../../../HRIS/product-discovery/output/reference/hris-saas-database-structure.md` |
| Blueprint | `../../../../HRIS/product-discovery/output/reference/hris-system-blueprint.md` |
| Port guardrail subagent | [`.claude/agents/laravel-port-guardrail.md`](../../../.claude/agents/laravel-port-guardrail.md) |
| HR research skill | [`.cursor/skills/hr-research-indonesia/SKILL.md`](../hr-research-indonesia/SKILL.md) |
| Routing rule | [`.cursor/rules/21-laravel-port-guardrail.mdc`](../../rules/21-laravel-port-guardrail.mdc) |
