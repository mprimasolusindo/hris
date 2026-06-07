---
description: Mandatory database safety guardrail for the HRIS Laravel codebase. Always applied — protects developer test data from destructive commands.
alwaysApply: true
---

# Database Safety — Mandatory Guardrail

## NEVER run `php artisan migrate:fresh`

The developer's local/dev database (`db_hris`) contains hand-built
**test data that must never be destroyed**. This is non-negotiable.

- **NEVER** run `php artisan migrate:fresh` (with or without `--seed`).
- **NEVER** run `php artisan migrate:fresh` "just for testing", "to verify
  the schema", or as part of any verification step.
- **NEVER** run `php artisan db:wipe` or `php artisan migrate:reset`.
- Do not suggest these commands to the user as a next step either.

This applies in **every** situation: feature work, debugging, schema
verification, test runs, plan execution, and any command that "comes up".
If any workflow, plan, or skill tells you to run `migrate:fresh`, you must
**skip it** and use a safe alternative below.

## Safe alternatives

| Goal | Use this instead of `migrate:fresh` |
|---|---|
| Apply new migrations | `php artisan migrate` |
| Undo the last batch | `php artisan migrate:rollback` |
| Verify a migration round-trips | `php artisan migrate:rollback --step=N` then `php artisan migrate` |
| Inspect applied state | `php artisan migrate:status` |
| Run the test suite | `php artisan test` — tests use `RefreshDatabase`, which runs against the **test connection / in-memory transactions**, NOT the dev `db_hris` |

## Verifying schema changes safely

When you add migrations and want to confirm they apply and roll back
cleanly, use a scoped rollback/re-migrate of only the new migrations:

```bash
php artisan migrate:rollback --step=4   # number of new migrations
php artisan migrate
```

This proves `down()` correctness without dropping existing tables or data.

## If a destructive reset is genuinely required

Stop and ask the user for explicit, written confirmation first. Do not
proceed on your own judgment. The default answer is always "no".
