# Final Fix Wave Report

## Fixes

- Overtime filters now preserve `per_page` and reset `page` to 1.
- Compliance vendor/severity filters reset `open_page` and `resolved_page` to 1.
- Vendor Billing period filters reset `lines_page` and `invoices_page` to 1.
- `ListPaginator::resolvePerPage()` now accepts only exact whitelist strings/integers or the exact string `all`; malformed values such as `10foo` fall back to 20.
- A quick scan of the other Task 3/4 filter handlers found no additional obvious cases that drop `per_page`.

## Verification

- `php artisan test tests/Unit/Support/ListPaginatorTest.php` — 7 passed, 18 assertions.
- `php artisan test tests/Feature/CoreOpsTest.php tests/Feature/OutsourcingTest.php` — 15 passed, 109 assertions.
- `npx tsc --noEmit` — passed.
- `git diff --check` — passed.
- IDE lint diagnostics for all changed source/test files — clean.

## Concerns

None identified within this fix wave.
