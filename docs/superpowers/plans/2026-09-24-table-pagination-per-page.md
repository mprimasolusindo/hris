# Global Table Pagination + Per-Page Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Every list table Index page supports pagination UI and a Show filter: All / 10 / 20 / 30 / 50 / 100 (default 20).

**Architecture:** Shared PHP `App\Support\ListPaginator` resolves `per_page` and paginates query builders or collections. Shared React `TablePagination` + `PerPageSelect`. `MasterCrudPage` gains first-class paginated props so ~18 master screens inherit UI. Remaining custom Index pages are wired domain-by-domain.

**Tech Stack:** Laravel LengthAwarePaginator, Inertia/React, PHPUnit.

**Spec:** Approved chat design (2026-09-24): full roll-out; `per_page` ∈ {10,20,30,50,100,all}; default 20; `all` = single-page full set with same paginator JSON shape; skip non-table views (dashboard, search, calendars).

## Global Constraints

- NEVER run `migrate:fresh` / `migrate:reset` / `db:wipe`.
- Work only in the assigned git worktree; commit on the feature branch.
- Allowed `per_page` values exactly: `10`, `20`, `30`, `50`, `100`, `all`. Invalid → default `20`.
- Default page size: `20` for all newly wired pages (Employees may change from 15 → 20; Users from 15 → 20 for consistency).
- Always `withQueryString()` (or equivalent query on LengthAwarePaginator) so filters survive page/per_page changes.
- Pass `filters.per_page` (string) to Inertia on every list page.
- Pagination UI: show when `last_page > 1` OR always show PerPageSelect when there is at least one row option (always show PerPageSelect under tables).
- Do not add check-all in this plan.
- Prefer editing shared components over copy-pasting pagination markup.

---

### Task 1: Shared ListPaginator + React controls + tests

**Files:**
- Create: `app/Support/ListPaginator.php`
- Create: `tests/Unit/Support/ListPaginatorTest.php`
- Create: `resources/js/Components/table/TablePagination.tsx`
- Create: `resources/js/Components/table/PerPageSelect.tsx`
- Create: `resources/js/types/pagination.ts`

**Interfaces:**
- Produces:
  - `ListPaginator::OPTIONS = [10, 20, 30, 50, 100]`
  - `ListPaginator::resolvePerPage(Request $request, int $default = 20): int|string` — returns int in OPTIONS or `'all'`
  - `ListPaginator::paginate(\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query, Request $request, int $default = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator`
  - `ListPaginator::paginateCollection(\Illuminate\Support\Collection|array $items, Request $request, int $default = 20): LengthAwarePaginator` — for post-filter lists (e.g. contracts)
  - TS type `Paginated<T> = { data: T[]; links: Array<{ url: string | null; label: string; active: boolean }>; current_page: number; last_page: number; per_page: number; total: number }`
  - `<TablePagination paginator={Paginated} />`
  - `<PerPageSelect value={string} onChange={(v: string) => void} />` labels: All, 10, 20, 30, 50, 100 (`value` for All is `"all"`)

- [ ] **Step 1: Write failing unit tests for ListPaginator**

```php
<?php

namespace Tests\Unit\Support;

use App\Models\User;
use App\Support\ListPaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ListPaginatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_per_page_accepts_allowed_values(): void
    {
        $this->assertSame(20, ListPaginator::resolvePerPage(Request::create('/', 'GET', ['per_page' => '20'])));
        $this->assertSame('all', ListPaginator::resolvePerPage(Request::create('/', 'GET', ['per_page' => 'all'])));
        $this->assertSame(20, ListPaginator::resolvePerPage(Request::create('/', 'GET', ['per_page' => '999'])));
    }

    public function test_paginate_limits_results(): void
    {
        User::factory()->count(5)->create();
        $paginator = ListPaginator::paginate(User::query()->orderBy('id'), Request::create('/', 'GET', ['per_page' => '2']));
        $this->assertCount(2, $paginator->items());
        $this->assertSame(5, $paginator->total());
        $this->assertSame(3, $paginator->lastPage());
    }

    public function test_paginate_all_returns_single_page(): void
    {
        User::factory()->count(5)->create();
        $paginator = ListPaginator::paginate(User::query()->orderBy('id'), Request::create('/', 'GET', ['per_page' => 'all']));
        $this->assertCount(5, $paginator->items());
        $this->assertSame(1, $paginator->lastPage());
    }

    public function test_paginate_collection_slices_page(): void
    {
        $items = collect(range(1, 25))->map(fn ($n) => ['id' => $n]);
        $paginator = ListPaginator::paginateCollection($items, Request::create('/', 'GET', ['per_page' => '10', 'page' => 2]));
        $this->assertCount(10, $paginator->items());
        $this->assertSame(2, $paginator->currentPage());
        $this->assertSame(25, $paginator->total());
    }
}
```

- [ ] **Step 2: Run tests — expect FAIL**

`php artisan test --filter=ListPaginatorTest`

- [ ] **Step 3: Implement ListPaginator**

```php
<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class ListPaginator
{
    public const OPTIONS = [10, 20, 30, 50, 100];

    public static function resolvePerPage(Request $request, int $default = 20): int|string
    {
        $raw = $request->query('per_page', (string) $default);
        if (! is_scalar($raw)) {
            return $default;
        }
        $value = (string) $raw;
        if ($value === 'all') {
            return 'all';
        }
        $n = (int) $value;

        return in_array($n, self::OPTIONS, true) ? $n : $default;
    }

    public static function paginate(EloquentBuilder|QueryBuilder $query, Request $request, int $default = 20): LengthAwarePaginatorContract
    {
        $perPage = self::resolvePerPage($request, $default);
        if ($perPage === 'all') {
            $items = $query->get();

            return self::makePaginator($items, $items->count(), max($items->count(), 1), 1, $request);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public static function paginateCollection(Collection|array $items, Request $request, int $default = 20): LengthAwarePaginatorContract
    {
        $collection = $items instanceof Collection ? $items->values() : collect($items)->values();
        $perPage = self::resolvePerPage($request, $default);
        $total = $collection->count();
        if ($perPage === 'all') {
            return self::makePaginator($collection, $total, max($total, 1), 1, $request);
        }
        $page = max(1, (int) $request->query('page', 1));
        $slice = $collection->forPage($page, $perPage)->values();

        return self::makePaginator($slice, $total, $perPage, $page, $request);
    }

    private static function makePaginator($items, int $total, int $perPage, int $page, Request $request): LengthAwarePaginator
    {
        return (new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]))->withQueryString();
    }
}
```

- [ ] **Step 4: Implement TS types + React components**

`resources/js/types/pagination.ts`:

```ts
export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type Paginated<T> = {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};
```

`PerPageSelect.tsx`: Select with values `all|10|20|30|50|100`, labels `All|10|20|30|50|100`, trigger width ~w-28, call `onChange` with string value.

`TablePagination.tsx`: if `paginator.last_page <= 1` return null; else map links like Employees Index (`Button`+`Link`+`preserveScroll`+`dangerouslySetInnerHTML`).

- [ ] **Step 5: Run ListPaginatorTest — expect PASS**

- [ ] **Step 6: Commit**

`git commit -m "feat: add ListPaginator and shared table pagination controls"`

---

### Task 2: MasterCrudPage + all MasterCrud list screens

**Files:**
- Modify: `resources/js/Components/master/MasterCrudPage.tsx`
- Modify every MasterCrud Index under `resources/js/Pages/...` that uses MasterCrudPage (Companies, Sites, Departments, Positions, MasterAllowances, MasterDeductions, Shifts, Vendors, BpjsConfig, TaxRules, Holidays, WorkSchedules if MasterCrud, Admin Tenants/Plans, Talent Performance/Training/TalentPool/Succession, Recruitment Interviews)
- Modify matching controllers’ `index` methods to `Request $request` + `ListPaginator::paginate(...)` and pass `filters: ['per_page' => (string) $request->query('per_page', '20')]` (normalized via resolvePerPage string form: if int cast to string, if all keep `all`)

**Interfaces:**
- Consumes: ListPaginator, TablePagination, PerPageSelect
- Produces: MasterCrudPage props:
  - `items: Paginated<T>` (required — break array-only callers)
  - `filters: { per_page: string }`
  - `indexUrl: string` (route for current index, used by PerPageSelect)

MasterCrudPage changes:
- Render rows from `items.data`
- Below table: `<PerPageSelect value={filters.per_page} onChange={(v) => router.get(indexUrl, { ...filters, per_page: v, page: 1 })} />` + `<TablePagination paginator={items} />`

Controller pattern (example Companies):

```php
public function index(Request $request): Response
{
    $perPage = ListPaginator::resolvePerPage($request);

    return Inertia::render('Organization/Companies/Index', [
        'companies' => ListPaginator::paginate(
            Company::query()->orderBy('name')->select(['id', 'name', 'type']),
            $request,
        ),
        'filters' => [
            'per_page' => is_string($perPage) ? $perPage : (string) $perPage,
        ],
    ]);
}
```

Page pattern:

```tsx
export default function Index({ companies, filters }: PageProps<{
  companies: Paginated<CompanyRow>;
  filters: { per_page: string };
}>) {
  return (
    <MasterCrudPage
      items={companies}
      filters={filters}
      indexUrl={route('organization.companies.index')}
      ...
    />
  );
}
```

Apply the same to every MasterCrud consumer. For WorkSchedules/Holidays/etc., follow the same prop names they already use for the list (`items` vs named).

- [ ] **Step 1: Update MasterCrudPage**
- [ ] **Step 2: Update all MasterCrud controllers + Index pages** (complete list via ripgrep `MasterCrudPage` imports)
- [ ] **Step 3:** `npx tsc --noEmit` PASS; smoke `php artisan test --filter=ListPaginatorTest`
- [ ] **Step 4: Commit** `feat: paginate MasterCrud list screens with per-page control`

---

### Task 3: Wire already-paginated operational lists (per_page + UI)

**Files:**
- `app/Services/Employee/EmployeeQueryService.php` — use ListPaginator (default 20)
- `app/Http/Controllers/EmployeeController.php` — pass filters.per_page
- `resources/js/Pages/Employees/Index.tsx` — PerPageSelect + TablePagination (replace inline links)
- `app/Http/Controllers/PayrollController.php` — ListPaginator instead of paginate(20); filters.per_page
- `resources/js/Pages/Payroll/Index.tsx` — PerPageSelect + reuse TablePagination
- `app/Http/Controllers/AttendanceController.php` + `Pages/Attendance/Index.tsx`
- `app/Http/Controllers/Leave/LeaveController.php` + `Pages/Leave/Index.tsx`
- `app/Http/Controllers/Overtime/OvertimeController.php` + `Pages/Overtime/Index.tsx`
- `app/Http/Controllers/Admin/UserController.php` + `Pages/Admin/Users/Index.tsx`

Preserve existing filters when changing per_page (`router.get` merge filters + `page: 1`).

Note: Attendance summary currently counts only the current page collection — **Ruling:** keep page-scoped summary for this plan (document in report); do not change summary semantics beyond existing behavior.

- [ ] Implement all of the above
- [ ] `npx tsc --noEmit` PASS
- [ ] Commit `feat: add per-page controls to employees payroll attendance leave overtime users`

---

### Task 4: Remaining custom table Index pages

**Files (controllers + pages):**
- Contracts (`ListPaginator::paginateCollection` after status filter)
- BugReports
- Admin Roles
- Admin SaaS Subscriptions + Payments
- Outsourcing Placements, Compliance, Tracking
- Billing VendorBilling (each table that lists rows)
- Talent NineBox
- Recruitment Jobs, Candidates, Pipeline
- Leave Approvals, Leave Balance, Leave Types
- WorkSchedules (if not MasterCrud)

For each: replace `->get()` list with `ListPaginator::paginate` (or collection helper), add `filters.per_page`, render `PerPageSelect` + `TablePagination`.

Skip: Dashboard, Search, Shift calendar views without classic tables.

- [ ] Implement all listed screens
- [ ] `npx tsc --noEmit` PASS
- [ ] Focused feature smoke if existing tests hit these indexes
- [ ] Commit `feat: paginate remaining list tables with per-page filter`

---

## Spec coverage checklist

| Requirement | Task |
|---|---|
| Shared per_page resolver + all option | Task 1 |
| Shared pagination UI | Task 1 |
| Master CRUD tables | Task 2 |
| Existing paginated ops lists + per_page | Task 3 |
| All remaining table indexes | Task 4 |
| Default 20; options All/10/20/30/50/100 | Global Constraints |
