---
description: Activate product/UX research discipline whenever the conversation touches navigation, information architecture, dashboard/form design, usability, accessibility, or HRIS competitor benchmarking. Loaded on demand.
---

# Product & UX Research — Routing Rule

When the user's request, your plan, or the file you're editing involves
**any** product UX / information architecture topic, you must use one of
the project's two product-UX-research mechanisms.

## Trigger phrases

Activate this rule when you see (English or Indonesian):

- navigation, sidebar, menu, sitemap, information architecture, IA
- user flow, wireframe, dashboard layout, form design, onboarding
- empty state, error state, loading state, breadcrumb, tab navigation
- accordion, mega menu, mobile navigation, collapsible menu
- usability, heuristic evaluation, Nielsen, WCAG, accessibility, a11y
- keyboard navigation, screen reader, contrast, focus state
- competitor analysis, feature parity, benchmark, BambooHR, Workday,
  Gusto, Rippling, Deel, Talenta, Mekari, Gajihub
- HRIS UX patterns, admin panel design, SaaS navigation

…or any code/file referencing the same domain (e.g.
`AppSidebar.tsx`, `HrisLayout.tsx`, `resources/js/Components/layout/`,
nav i18n keys in `translations.ts`).

**Do NOT activate for Indonesian labor law / payroll / BPJS / PPh21
topics** — route those to `20-hr-research-indonesia.mdc` instead.

## Two mechanisms — pick one

### 1. In-session skill (default for short answers)

Read and follow:

- `.cursor/skills/product-ux-research/SKILL.md`

Use this for inline reasoning — "how should Leave sub-items be grouped?",
"what's the ideal number of sidebar items?", "should admin be separated?",
etc.

### 2. Sub-agent dispatch (for deeper / multi-source research)

Dispatch the `product-ux-researcher` sub-agent defined at:

- `.claude/agents/product-ux-researcher.md`

Use this when the question needs **browsing / multi-source synthesis /
competitor cross-checking**, e.g. "compare BambooHR vs Talenta nav
structure", "research best practices for payroll module IA in HRIS",
"validate our sidebar redesign against enterprise SaaS patterns".

## Output discipline (mandatory regardless of mechanism)

Every claim about UX or product patterns must include:

1. **Source reference** — author / publication / URL.
2. **Context** — when the pattern applies (audience, app type, scale).
3. **Tradeoff or caveat** — what you gain vs sacrifice.

If you can't cite, label `[unverified — needs research]` and either
dispatch the sub-agent or ask the user.

## Don't

- Don't conflate UX grouping decisions with Indonesian HR regulation
  requirements — those are separate concerns.
- Don't invent competitor feature claims without verification.
- Don't recommend nav libraries that conflict with the existing shadcn/ui
  sidebar + collapsible stack unless explicitly requested.
- Don't skip i18n (EN + ID) when changing user-visible nav labels.
