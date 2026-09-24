# Glassy Input / Select / Textarea (Screenshot Parity)

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Restyle shared `Input`, `SelectTrigger`, and `Textarea` to match the frosted glass capsule look from the chat-composer screenshot (semi-transparent fill, backdrop blur, hairline border, soft elevation, high roundness).

**Architecture:** One shared Tailwind class string applied to the three primitives (plus legacy `TextInput` if present). Single-line controls use pill (`rounded-full`); multi-line textarea uses the same glass material with large but non-pill radius so dense HR forms stay usable.

**Tech Stack:** React, Tailwind 3, Radix Select, existing macOS glass tokens.

**Spec:** User screenshot 2026-09-24 (iMessage-style frosted pill composer) + request for input/select/textarea only.

## Global Constraints

- NEVER `migrate:fresh` / `migrate:reset` / `db:wipe`.
- Change shared primitives only — no page-by-page rewrites.
- Light glass only; no purple/indigo glow; no dark-mode flip.
- Do **not** redesign layout chrome, buttons, cards, or circular icon buttons from the screenshot — only text fields.
- **Ruling (locked):** `Input` and `SelectTrigger` → `rounded-full` (pill). `Textarea` → `rounded-3xl` (same glass recipe, not a capsule — multi-line pills clip poorly).
- Exact glass recipe (use verbatim in class strings):
  - Fill: `bg-white/60`
  - Blur: `backdrop-blur-xl`
  - Border: `border border-white/70`
  - Shadow: `shadow-[0_2px_12px_hsl(215_25%_27%/0.08)]`
  - Horizontal padding single-line: `px-4` (was `px-3`)
  - Keep existing height (`h-10`), focus rings, disabled, placeholder, file: variants
- After UI: `npx tsc --noEmit` and `npm run build` PASS.
- Commit on feature branch; do not merge/push unless asked.

---

### Task 1: Apply screenshot glass recipe to form fields

**Files:**
- Modify: `resources/js/Components/ui/input.tsx`
- Modify: `resources/js/Components/ui/select.tsx` (`SelectTrigger` only — leave `SelectContent` as existing `glass-panel`)
- Modify: `resources/js/Components/ui/textarea.tsx`
- Modify if present: `resources/js/Components/TextInput.tsx` (or equivalent legacy wrapper) to match Input glass recipe

**Exact class targets:**

1. **Input** — replace surface classes with:
   ```
   flex h-10 w-full rounded-full border border-white/70 bg-white/60 backdrop-blur-xl shadow-[0_2px_12px_hsl(215_25%_27%/0.08)] px-4 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm
   ```

2. **SelectTrigger** — same surface as Input (pill + glass), keep chevron and `[&>span]:line-clamp-1`:
   ```
   flex h-10 w-full items-center justify-between rounded-full border border-white/70 bg-white/60 backdrop-blur-xl shadow-[0_2px_12px_hsl(215_25%_27%/0.08)] px-4 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 [&>span]:line-clamp-1
   ```

3. **Textarea** — glass recipe with `rounded-3xl` and `px-4 py-3`:
   ```
   flex min-h-[80px] w-full rounded-3xl border border-white/70 bg-white/60 backdrop-blur-xl shadow-[0_2px_12px_hsl(215_25%_27%/0.08)] px-4 py-3 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50
   ```

4. Legacy TextInput: mirror Input classes if the file exists.

- [ ] **Step 1: Update the three (or four) component files**
- [ ] **Step 2: `npx tsc --noEmit` PASS**
- [ ] **Step 3: `npm run build` PASS**
- [ ] **Step 4: Commit** `style: frosted pill glass for input select textarea`

---

## Spec coverage checklist

| Requirement | Task |
|---|---|
| Frosted semi-transparent fill + blur | Task 1 |
| Pill shape for input/select | Task 1 |
| Textarea glass without broken pill | Task 1 |
| Soft elevation shadow | Task 1 |
| Global via primitives | Task 1 |
