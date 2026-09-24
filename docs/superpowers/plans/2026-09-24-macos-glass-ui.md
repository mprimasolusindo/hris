# macOS 27 Glass UI Theme Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Apply a light-mode macOS 27–inspired Liquid Glass look across the HRIS shell: atmosphere, cards, buttons, inputs, dialogs, nav (sidebar/header), tables — inherited by all pages via tokens + shared primitives.

**Architecture:** Update CSS design tokens and add glass utility classes in `resources/css/app.css`. Restyle shadcn primitives under `resources/js/Components/ui/` and layout chrome (`HrisLayout`, `AppHeader`, sidebar). No page-by-page rewrites. Light mode only for v1 (keep `.dark` tokens coherent but do not switch app to dark).

**Tech Stack:** Tailwind 3, shadcn/ui (Radix), existing HSL CSS variables.

**Spec:** Approved chat design (2026-09-24): cool gray–blue mesh backdrop; frosted panels (`backdrop-filter`); hairline borders; soft shadows; solid primary buttons for contrast; short hover transitions; no purple/neon glow.

## Global Constraints

- NEVER `migrate:fresh` / `migrate:reset` / `db:wipe`.
- Work only in the assigned git worktree; commit on the feature branch.
- Light mode first. Do not introduce purple/indigo gradient themes or heavy glow.
- Prefer changing shared tokens/primitives over editing individual Pages.
- Preserve print styles for payslip (existing `@media print` block).
- Primary / destructive buttons remain solid (readable); outline/secondary/ghost become glass.
- Use `backdrop-blur` + semi-transparent backgrounds; support `backdrop-filter` gracefully.
- Font: keep Figtree (already configured).
- Radius: bump `--radius` to `0.75rem` for softer macOS feel.
- After UI changes run `npx tsc --noEmit` (and `npm run build` once at end of branch if Vite build is available).

---

### Task 1: Glass tokens, utilities, and layout atmosphere

**Files:**
- Modify: `resources/css/app.css`
- Modify: `tailwind.config.js` (extend boxShadow / backdropBlur if needed; map any new colors)
- Modify: `resources/js/Layouts/HrisLayout.tsx`
- Modify: `resources/js/Components/layout/AppHeader.tsx` (header bar glass classes)
- Modify: `resources/js/Components/ui/sidebar.tsx` only if sidebar surface classes are defined there (glass sidebar background)

**Interfaces:**
- Produces CSS variables on `:root`:
  - `--glass-bg: 0 0% 100% / 0.55` (use separate alpha vars if HSL slash is awkward — prefer `--glass-bg: 255 255 255` + `--glass-bg-alpha: 0.55` OR use `hsl(0 0% 100% / 0.55)` directly in utilities)
  - `--glass-border: 214 32% 91% / 0.65`
  - `--glass-blur: 20px`
  - `--glass-shadow: 0 8px 32px hsl(215 25% 27% / 0.08)`
  - Soften `--background` to cool tint `210 40% 98%`
  - Soften `--card` / `--popover` / `--sidebar-background` toward translucent-friendly light values
  - `--radius: 0.75rem`
- Produces utility classes in `@layer utilities` or `@layer components`:
  - `.glass-panel` → `background: hsl(0 0% 100% / 0.55); backdrop-filter: blur(var(--glass-blur)) saturate(1.4); -webkit-backdrop-filter: ...; border: 1px solid hsl(0 0% 100% / 0.45); box-shadow: var(--glass-shadow);`
  - `.glass-chrome` → slightly denser glass for sidebar/header (`bg` alpha ~0.72)
  - `.app-atmosphere` → fixed/absolute full-bleed gradient mesh (cool blues/grays, subtle, no purple)
- `HrisLayout`: wrap with atmosphere div; main area transparent over mesh (`bg-transparent` or very light); keep padding.

- [ ] **Step 1: Update `app.css` tokens + utilities** (exact glass recipe above; keep print block)
- [ ] **Step 2: Update `HrisLayout`** to include atmosphere layer behind sidebar+content
- [ ] **Step 3: Apply `glass-chrome` to header container and sidebar root surface**
- [ ] **Step 4: Commit** `style: add macOS glass tokens and layout atmosphere`

---

### Task 2: Restyle shared UI primitives to glass

**Files (modify as needed — all under `resources/js/Components/ui/`):**
- `button.tsx`, `card.tsx`, `input.tsx`, `textarea.tsx`, `select.tsx`, `dialog.tsx`, `sheet.tsx`, `dropdown-menu.tsx`, `popover.tsx`, `table.tsx`, `badge.tsx`, `tabs.tsx`, `checkbox.tsx`, `switch.tsx`, `alert.tsx`, `alert-dialog.tsx`, `command.tsx`, `tooltip.tsx`
- Also: `resources/js/Components/auth/AuthShell.tsx` if it has opaque panels

**Interfaces:**
- Consumes: `.glass-panel` / glass token utilities from Task 1
- Card: replace opaque `bg-card shadow-sm` with glass-panel look (`bg-white/50 backdrop-blur-xl border-white/40 shadow-[...]`)
- Dialog/Sheet/Popover/Dropdown content: glass-panel
- Input/Select trigger/Textarea: `bg-white/40 backdrop-blur-md border-white/50`
- Button variants:
  - `default` / `destructive`: keep solid
  - `outline` / `secondary`: glass (`bg-white/35 backdrop-blur-md border-white/50`)
  - `ghost`: translucent hover only
- Table: header row subtle glass tint; body transparent over card
- Badge: soft glass chip
- AuthShell: use atmosphere + glass card for login panel

- [ ] **Step 1: Apply glass classes to the listed primitives**
- [ ] **Step 2: AuthShell glass**
- [ ] **Step 3: `npx tsc --noEmit` PASS**
- [ ] **Step 4: Commit** `style: apply glass material to UI primitives`

---

### Task 3: Polish chrome + verify (sidebar menu, header search, build)

**Files:**
- `resources/js/Components/layout/AppSidebar.tsx` (active/hover states softer glass highlights — not opaque gray blocks)
- `resources/js/Components/layout/AppHeader.tsx` (search input already glass via Input; ensure header bar uses glass-chrome)
- `resources/js/Components/ui/sidebar.tsx` (menu button active states)
- Optional: `sonner.tsx` toast glass

- [ ] **Step 1: Soften sidebar active/hover to glass highlights** (`bg-white/40`, no solid `#f4f4f5` slabs)
- [ ] **Step 2: Run `npx tsc --noEmit` and `npm run build`**
- [ ] **Step 3: Commit** `style: polish glass nav chrome`

---

## Spec coverage checklist

| Requirement | Task |
|---|---|
| Atmosphere mesh backdrop | Task 1 |
| Glass tokens + utilities | Task 1 |
| Layout / nav chrome | Task 1 + 3 |
| Cards/buttons/inputs/dialogs glass | Task 2 |
| Global inheritance (no page rewrites) | All |
| Light mode; no purple glow | Global Constraints |
