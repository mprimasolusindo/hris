# Payroll Check-All and Pagination Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** On the payroll runs table, add a header “check all (current page)” control and visible pagination controls matching the Employees index pattern.

**Architecture:** Frontend-only on `resources/js/Pages/Payroll/Index.tsx`. Backend already returns Laravel `paginate(20)->withQueryString()`. Widen the Inertia `payrolls` prop type to include `links`, `current_page`, `last_page`; add header checkbox for page-scoped select-all; render pagination buttons under the table; clear selection when the current page’s row IDs change.

**Tech Stack:** React, Inertia, existing `Checkbox` / `Button` / `Link` components.

**Spec:** Approved chat design (2026-09-23): check all = current page only; indeterminate when partial; pagination like Employees; clear selection on page change; no page-size change; no select-all across all pages.

## Global Constraints

- NEVER run `php artisan migrate:fresh`, `migrate:reset`, or `db:wipe`.
- Work only in the assigned git worktree; commit on the feature branch.
- Touch only `resources/js/Pages/Payroll/Index.tsx` (no backend/controller changes unless a typing-only gap forces it — backend already paginates).
- Check-all selects/deselects **only rows on the current page** (`payrolls.data`).
- Pagination UI must match Employees: show when `last_page > 1`; map `links` to `Button`+`Link` with `preserveScroll` and `dangerouslySetInnerHTML` for labels.
- Preserve filter query string via existing Laravel links (do not strip query params in the UI).
- Clear `selected` when navigating pages (when `payrolls.data` IDs change).
- No new routes, migrations, or i18n keys required (English labels OK: use `aria-label="Select all on this page"` on the header checkbox).

---

### Task 1: Check-all + pagination on Payroll Index

**Files:**
- Modify: `resources/js/Pages/Payroll/Index.tsx`

**Interfaces:**
- Consumes: Inertia `payrolls` Laravel paginator shape `{ data, links, current_page, last_page }` (already produced by `PayrollController@index`)
- Produces: Header checkbox; page-scoped select-all; pagination footer; selection cleared on page change

- [ ] **Step 1: Widen the payrolls prop type**

Replace:

```tsx
payrolls: { data: PayrollRow[] };
```

with:

```tsx
type PaginatedPayrolls = {
    data: PayrollRow[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
};
```

and type the prop as `payrolls: PaginatedPayrolls`.

- [ ] **Step 2: Add page-scoped check-all helpers**

After `selected` / `toggleRow` state, add:

```tsx
const pageIds = payrolls.data.map((row) => row.id);
const allPageSelected =
    pageIds.length > 0 && pageIds.every((id) => selected.includes(id));
const somePageSelected = pageIds.some((id) => selected.includes(id));

const toggleAllOnPage = (checked: boolean) => {
    setSelected((prev) => {
        if (checked) {
            const merged = new Set([...prev, ...pageIds]);
            return Array.from(merged);
        }
        return prev.filter((id) => !pageIds.includes(id));
    });
};

useEffect(() => {
    setSelected([]);
}, [payrolls.current_page, pageIds.join(',')]);
```

Ruling note for implementer: using `pageIds.join(',')` in the effect dependency is intentional to clear selection when the page’s row set changes (pagination or filter refresh). Do not keep stale IDs from other pages.

- [ ] **Step 3: Header checkbox in TableHead**

Replace the empty `<TableHead />` first column with:

```tsx
<TableHead className="w-10">
    <Checkbox
        aria-label="Select all on this page"
        checked={
            allPageSelected
                ? true
                : somePageSelected
                  ? 'indeterminate'
                  : false
        }
        onCheckedChange={(c) => toggleAllOnPage(!!c)}
        disabled={pageIds.length === 0}
    />
</TableHead>
```

Confirm the shared `Checkbox` accepts `checked="indeterminate"` (radix pattern already used elsewhere). If the component’s types reject the string, cast as needed without changing the checkbox primitive file.

- [ ] **Step 4: Pagination footer**

Immediately after the closing `</Card>` of “Payroll runs” (still inside the outer `space-y-6` div), add:

```tsx
{payrolls.last_page > 1 && (
    <div className="flex flex-wrap gap-2">
        {payrolls.links.map((link, i) =>
            link.url ? (
                <Button
                    key={i}
                    variant={link.active ? 'default' : 'outline'}
                    size="sm"
                    asChild
                >
                    <Link href={link.url} preserveScroll>
                        <span
                            dangerouslySetInnerHTML={{
                                __html: link.label,
                            }}
                        />
                    </Link>
                </Button>
            ) : null,
        )}
    </div>
)}
```

- [ ] **Step 5: Typecheck**

Run: `npx tsc --noEmit`
Expected: PASS (exit 0)

- [ ] **Step 6: Commit**

```bash
git add resources/js/Pages/Payroll/Index.tsx
git commit -m "feat(payroll): add check-all and pagination to runs table"
```

---

## Spec coverage checklist

| Requirement | Task |
|---|---|
| Header check all (current page) | Task 1 |
| Indeterminate when partial | Task 1 |
| Pagination UI like Employees | Task 1 |
| Clear selection on page change | Task 1 |
| No cross-page select-all / no page size | Global Constraints |
