---
name: product-ux-research
description: |
  Use when the user asks about HRIS product UX, information architecture,
  navigation patterns, dashboard design, form usability, accessibility,
  design systems, or competitor/feature benchmarking (BambooHR, Workday,
  Gusto, Rippling, Deel; ID market: Talenta/Mekari, Gajihub).
  Triggers on UX/IA/navigation/usability/design-system topics.
  Defers Indonesian-HR domain specifics to hr-research-indonesia.
priority: high
---

# Skill: Product & UX Research

A research and citation discipline for HRIS product, UX, and information
architecture questions in this Laravel + Inertia/React codebase. Use it
whenever you're advising on, reviewing, or implementing UI/UX that touches
navigation, workflows, dashboards, forms, or competitive positioning.

> If you need *deep* research with browsing, dispatch the
> [`product-ux-researcher`](../../../.claude/agents/product-ux-researcher.md)
> sub-agent instead. This skill is for in-line, in-session reasoning.

---

## When to activate

Activate when **any** of the following appear in the user's message, your
plan, or the file you're editing:

**UX / IA terms:** navigation, sidebar, menu, information architecture,
IA, sitemap, user flow, wireframe, dashboard layout, form design,
onboarding, empty state, error state, loading state, breadcrumb, tab
navigation, accordion, mega menu, mobile navigation.

**Usability / accessibility:** usability, heuristic evaluation, Nielsen,
WCAG, accessibility, a11y, keyboard navigation, screen reader, contrast,
focus state, touch target.

**Product / competitive:** competitor analysis, feature parity, benchmark,
BambooHR, Workday, Gusto, Rippling, Deel, Talenta, Mekari, Gajihub,
HRIS UX patterns, admin panel design, SaaS navigation.

**Code / file signals:** anything under `resources/js/Components/layout/`,
`resources/js/Layouts/`, sidebar/navigation components, design tokens,
i18n label changes for nav, dashboard pages, form-heavy feature modules.

**Do NOT activate for:** Indonesian labor law, payroll math, BPJS, PPh21,
contract regulations — route those to `hr-research-indonesia` instead.

---

## Research output discipline

Every claim about UX or product patterns MUST follow this format:

> **Claim** + **source reference** (author / publication / URL) +
> **context** (when it applies) + **tradeoff / caveat**.

Example:

> Enterprise SaaS sidebars should limit **primary navigation to 5–8
> top-level items** (Nielsen Norman Group / enterprise IA guidance).
> Beyond 8 items, users struggle to form a mental model. Tradeoff:
> consolidating modules may hide infrequent admin tasks — move those to
> a separated bottom "Admin" or "Settings" section.

If the source can't be cited, label the statement
`[unverified — needs research]` and either invoke the sub-agent or ask
the user for confirmation.

---

## Authoritative sources (use these first)

### UX / IA foundations
- **Nielsen Norman Group** (nngroup.com) — usability heuristics, navigation,
  form design, dashboard patterns.
- **UX Patterns for Developers** (uxpatterns.dev) — sidebar, accordion,
  breadcrumb, form patterns with implementation notes.
- **Smashing Magazine** — mobile navigation, responsive patterns.
- **Material Design / Apple HIG** — platform conventions for navigation
  and interaction (secondary reference).

### Enterprise SaaS navigation
- Enterprise IA guidance: 5–8 primary items, workflow-based grouping,
  persistent sidebar for admin/dashboard apps.
- Accordion sidebar: top-level 5–8 sections, each with 3–6 subsections;
  auto-expand active section; chevron affordance; max 2–3 nesting levels.
- Separate admin/setup items from daily workflow modules.

### HRIS-specific competitors (benchmarking only — not primary citations)
- **Global:** BambooHR, Workday, Gusto, Rippling, Deel.
- **Indonesia:** Talenta (Mekari), Gajihub, Sleekr (historical).
- Use competitor UX for **feature placement and workflow inspiration**;
  always note market/context differences (Indonesian compliance modules
  like BPJS/PPh21 are non-negotiable differentiators).

### Accessibility
- **WCAG 2.2** (w3.org/WAI/WCAG22) — contrast, focus, keyboard, labels.
- **WAI-ARIA Authoring Practices** — accordion, menu, sidebar patterns.

---

## Quick-reference: HRIS navigation IA

Recommended module grouping for this product (aligned with blueprint):

| Top-level module | Sub-items | Rationale |
|---|---|---|
| Dashboard | (direct link) | Entry point, KPIs |
| Employees | Directory, Contracts | Core HR entity + lifecycle |
| Time & Attendance | Attendance, Shifts | Daily ops, high frequency |
| Leave | Requests, Approvals, Balances, Types | Self-contained workflow |
| Payroll | Runs, Allowance Types, Master Allowances/Deductions | Compensation cluster |
| Recruitment | Jobs, Candidates, Pipeline, Interviews | Hiring workflow |
| Talent | Performance, Training, Talent Pool, Succession, Nine-Box | Growth & retention |
| Outsourcing | Vendors, Placements, Tracking, Billing, Compliance | Differentiator module |
| Admin (separated) | Organization (Companies/Sites/Depts/Positions), System | Setup, infrequent |

Order by **workflow frequency** — most-used modules at top.

---

## Quick-reference: sidebar UX checklist

When reviewing or implementing sidebar navigation:

- [ ] 5–8 primary top-level items (excluding separated admin section)
- [ ] Collapsible parent menus with chevron rotation affordance
- [ ] Auto-expand parent containing the active route
- [ ] Parent highlighted when any child is active
- [ ] Badge counts on leaves; aggregated badge on collapsed parent
- [ ] Tooltips on parent buttons in icon-collapsed mode
- [ ] i18n labels in EN + ID (`resources/js/i18n/translations.ts`)
- [ ] Max 2 nesting levels (parent → child, no deeper)
- [ ] Admin/setup separated visually at bottom

---

## Repository pointers

- Sidebar implementation: `resources/js/Components/layout/AppSidebar.tsx`
- Layout shell: `resources/js/Layouts/HrisLayout.tsx`
- UI primitives: `resources/js/Components/ui/sidebar.tsx`,
  `resources/js/Components/ui/collapsible.tsx`
- i18n: `resources/js/i18n/translations.ts`
- Conceptual blueprint (sibling product-discovery repo):
  `../../../../HRIS/product-discovery/output/reference/hris-system-blueprint.md`
- Sub-agent for deeper research (browsing + multi-source synthesis):
  [`.claude/agents/product-ux-researcher.md`](../../../.claude/agents/product-ux-researcher.md)
- Indonesian HR domain (separate skill):
  [`.cursor/skills/hr-research-indonesia/SKILL.md`](../hr-research-indonesia/SKILL.md)

---

## Operating rules

1. **Cite or label.** Every UX/product claim is either source-backed or
   labeled `[unverified — needs research]`.
2. **Separate UX from HR domain.** Navigation grouping is a UX decision;
   payroll compliance is an HR-regulation decision — don't conflate.
3. **Prefer workflow-based grouping** over feature-inventory grouping.
4. **Respect existing design system.** This repo uses shadcn/ui sidebar +
   collapsible primitives; don't introduce alternate nav libraries without
   explicit request.
5. **i18n is mandatory** for any user-visible nav label change (EN + ID).
6. **When unsure**, dispatch the sub-agent or ask the user, don't invent
   competitor feature claims.
