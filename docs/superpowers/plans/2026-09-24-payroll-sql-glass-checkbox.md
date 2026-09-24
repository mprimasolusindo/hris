# Fix Payroll SQL + Glass Checkbox Visibility

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix the payroll index MySQL syntax error (`generated` reserved alias) and make checkboxes (and related toggles) clearly visible on the macOS 27 glass UI.

**Architecture:** Rename the SQL aggregate alias away from MySQL reserved word `GENERATED` while keeping the Inertia `summary.generated` prop. Strengthen checkbox/radio/switch unchecked borders and fills so they remain visible on frosted glass panels without leaving light-mode glass.

**Tech Stack:** Laravel 12, MySQL 8, Inertia/React, Tailwind, Radix checkbox/switch/radio.

**Spec:** User report 2026-09-24 — payroll URL SQLSTATE 1064 on `as generated`; checkbox invisible under glass; keep macOS 27 glass UI.

## Global Constraints

- NEVER `migrate:fresh` / `migrate:reset` / `db:wipe`.
- Do not rename payroll status values in the DB (`draft` / `generated` / `paid` stay as status strings).
- Inertia summary prop keys stay `draft`, `generated`, `paid`, `total_amount` (Payroll/Index.tsx).
- Prefer shared UI primitives under `resources/js/Components/ui/` over page-local style hacks.
- Light glass only: no purple/indigo glow, no dark-mode flip.
- Keep existing EmployeeController `through()` pagination fix if present on the branch (commit it with Task 1 or its own commit — do not revert).
- After UI changes: `npx tsc --noEmit` and `npm run build`.

---

### Task 1: Fix payroll summary SQL reserved alias

**Files:**
- Modify: `app/Http/Controllers/PayrollController.php`
- Create: `tests/Feature/PayrollIndexSummaryTest.php`

**Problem:** MySQL 8 rejects `… AS generated` (reserved). Error near `'generated, SUM(CASE WHEN status = 'paid'…`.

**Fix:**
1. In `PayrollController::index` `selectRaw`, change alias `as generated` → `as generated_count` (and optionally backtick other aliases if needed; `draft`/`paid` are fine).
2. Map PHP summary: `'generated' => (int) $summaryStats->generated_count`.
3. Feature test: acting as authorized user, `GET route('payroll.index')` asserts 200 and `summary` has integer keys `draft`, `generated`, `paid` and numeric `total_amount`. Seed minimal payrolls if factories exist; otherwise create via models already used in other Payroll tests. Mirror auth/setup from `tests/Feature/PayrollStoreGenerateAllTest.php`.

- [ ] **Step 1: Write failing/passing feature test** `PayrollIndexSummaryTest` hitting index
- [ ] **Step 2: Fix `selectRaw` alias + summary mapping**
- [ ] **Step 3: Run `php artisan test --filter=PayrollIndexSummaryTest` PASS
- [ ] **Step 4: Commit** `fix: avoid MySQL reserved alias in payroll summary`

---

### Task 2: Glass-visible checkbox, radio, switch + legacy Checkbox

**Files:**
- Modify: `resources/js/Components/ui/checkbox.tsx`
- Modify: `resources/js/Components/ui/radio-group.tsx` (RadioGroupItem)
- Modify: `resources/js/Components/ui/switch.tsx` (unchecked track border)
- Modify: `resources/js/Components/Checkbox.tsx` (native input — glass-visible border)

**Problem:** Unchecked checkbox uses `border-white/50 bg-white/40` on glass cards (`bg-white/55`) — invisible.

**Fix (exact classes):**
- `ui/checkbox.tsx` Root unchecked: replace with  
  `border border-slate-400/70 bg-white/80 shadow-sm backdrop-blur-sm`  
  keep checked: `data-[state=checked]:border-primary data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground`
- `ui/radio-group.tsx` RadioGroupItem: same border/bg treatment as checkbox (slate border, white/80 fill) when unchecked; primary when checked
- `ui/switch.tsx` unchecked: add visible rim `data-[state=unchecked]:border data-[state=unchecked]:border-slate-400/60 data-[state=unchecked]:bg-white/70` (keep checked primary)
- `Components/Checkbox.tsx`:  
  `rounded border border-slate-400/70 bg-white/80 text-primary shadow-sm …`

- [ ] **Step 1: Apply class updates to the four files**
- [ ] **Step 2: `npx tsc --noEmit` PASS**
- [ ] **Step 3: `npm run build` PASS**
- [ ] **Step 4: Commit** `style: make glass checkboxes and toggles visible`

---

## Spec coverage checklist

| Requirement | Task |
|---|---|
| Payroll index loads without SQL 1064 | Task 1 |
| `summary.generated` prop unchanged for UI | Task 1 |
| Checkbox visible on glass panels | Task 2 |
| Radio/switch + legacy Checkbox parity | Task 2 |
| Stay on macOS 27 glass (no theme rewrite) | Task 2 |
