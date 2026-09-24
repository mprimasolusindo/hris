# Sidebar Glass + Active-Only Highlight

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the app sidebar read as frosted glass, and show a white/glass chip **only** on the active route (and its active leaf) — not on every menu row or merely-expanded parents.

**Architecture:** Tighten `AppSidebar` active logic (`isActive` from route only, never from `isOpen`). Soften `SidebarMenuButton` / `SidebarMenuSubButton` so idle rows are transparent; hover is a light wash; `data-[active=true]` is the sole solid frosted chip. Slightly increase sidebar chrome translucency so the atmosphere shows through.

**Tech Stack:** React, Tailwind, shadcn Sidebar, existing `.glass-chrome` / atmosphere.

**Spec:** User screenshot 2026-09-24 + request: glass sidebar; white background only for active menu.

## Global Constraints

- NEVER `migrate:fresh` / `migrate:reset` / `db:wipe`.
- Do not change nav IA, routes, badges, or expand/collapse behavior (except active styling).
- Light glass only; no purple glow; no dark-mode flip.
- **Ruling (locked):** `SidebarMenuButton isActive={activeChild}` only — **never** `activeChild || isOpen`. Expanded-but-inactive parents stay transparent.
- **Ruling (locked):** Idle menu rows: no fill (`bg-transparent`). Hover: `hover:bg-white/25`. Active: `data-[active=true]:bg-white/55 data-[active=true]:backdrop-blur-md data-[active=true]:shadow-sm` (same for sub-buttons / NavLink `activeClassName`).
- Outline variant must not paint a persistent chip: change `outline` to transparent base (hover only).
- Sidebar surface: keep `glass-chrome` class name; reduce fill alpha in `.glass-chrome` from `0.72` → `0.48` in `app.css` so glass is visible (affects header too — acceptable shared chrome).
- After UI: `npx tsc --noEmit` and `npm run build` PASS.
- Feature branch; do not merge/push unless asked.

---

### Task 1: Transparent idle nav + active chip + glass chrome

**Files:**
- Modify: `resources/js/Components/layout/AppSidebar.tsx`
- Modify: `resources/js/Components/ui/sidebar.tsx` (`sidebarMenuButtonVariants`, `SidebarMenuSubButton`)
- Modify: `resources/css/app.css` (`.glass-chrome` alpha)

**Steps / exact edits:**

1. **AppSidebar `renderParent`:** change  
   `isActive={activeChild || isOpen}` → `isActive={activeChild}`

2. **AppSidebar NavLink classes** (leaf + sub):  
   - `className`: `hover:bg-white/25` (replace `hover:bg-white/30`)  
   - `activeClassName`: `bg-white/55 text-sidebar-foreground font-medium backdrop-blur-md shadow-sm` (replace `bg-white/40 … backdrop-blur-sm`)

3. **sidebar.tsx `sidebarMenuButtonVariants` base string:**  
   - Remove idle fill if any; ensure no default `bg-white/*` except via `data-[active=true]`  
   - Set `data-[active=true]:bg-white/55 data-[active=true]:backdrop-blur-md data-[active=true]:shadow-sm data-[active=true]:font-medium`  
   - Hover stays `hover:bg-white/25` (update from `/30` where present)  
   - `active:bg-white/40` (press) may stay or become `/25` — prefer `active:bg-white/25`  
   - **outline variant:** replace persistent `bg-white/20 shadow-[…]` with transparent idle:  
     `bg-transparent shadow-none hover:bg-white/25 hover:text-sidebar-foreground`

4. **SidebarMenuSubButton:** align active/hover with the same recipe (`data-[active=true]:bg-white/55 …`, `hover:bg-white/25`); idle transparent.

5. **app.css `.glass-chrome`:**  
   `background: hsl(0 0% 100% / 0.48);` (was `0.72`)

- [ ] **Step 1: Apply the edits above**
- [ ] **Step 2: `npx tsc --noEmit` PASS**
- [ ] **Step 3: `npm run build` PASS**
- [ ] **Step 4: Commit** `style: glass sidebar with active-only menu chips`

---

## Spec coverage checklist

| Requirement | Task |
|---|---|
| Sidebar glass (atmosphere visible) | Task 1 (`.glass-chrome` alpha) |
| No white chip on idle menus | Task 1 (transparent idle + isOpen fix) |
| White/frosted chip only when active | Task 1 |
| Expand/collapse still works | Task 1 (behavior unchanged) |
