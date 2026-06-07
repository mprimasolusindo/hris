---
name: User Management RBAC
overview: Add a custom action-level RBAC system (roles + permissions) with a User/Role management UI, seeded with 7 Indonesian-HRIS roles, and enforce access across backend routes/policies and the React sidebar.
todos:
  - id: schema
    content: Create sys_roles, sys_permissions, rel_role_permissions, rel_user_roles migrations (prefixed, reversible down()) and Role/Permission models + HasRoles trait on User
    status: completed
  - id: gates
    content: Wire Gate::before (super-admin) + dynamic permission gates in AppServiceProvider (migration-safe); share roles/permissions via HandleInertiaRequests
    status: completed
  - id: seeders
    content: Add PermissionSeeder (module.action catalog) + RoleSeeder (7 roles->permissions), update DemoAdminUserSeeder and DatabaseSeeder
    status: completed
  - id: enforce-routes
    content: Apply can:<permission> middleware to route groups in web.php and replace EmployeePolicy stubs with real permission checks
    status: completed
  - id: user-crud
    content: Build Admin\UserController + Store/UpdateUserRequest with roles[] sync and safety guards; Users Index/Create/Edit pages
    status: completed
  - id: role-crud
    content: Build Admin\RoleController with permissions[] sync; Roles Index/Edit (permission matrix) pages
    status: completed
  - id: sidebar
    content: Add permission field to nav nodes + useCan() helper in AppSidebar, filter nav, add Users/Roles admin menu, add i18n keys
    status: completed
  - id: verify
    content: Run migrate:fresh --seed and add PHPUnit feature tests for permission enforcement, multi-role assignment, and last-super-admin guard
    status: completed
isProject: false
---

# User Management & Role-Based Access Control (RBAC)

## Goal
Add a "Users" and "Roles" admin area to manage system users with **multiple roles each**, backed by a custom action-level permission system, and enforce that access on both the Laravel backend and the React sidebar.

## Decisions (confirmed)
- **Engine:** custom tables following `sys_` / `rel_` prefix conventions (no Spatie).
- **Granularity:** action-level permissions (`module.action`, e.g. `employees.create`).
- **Enforcement:** full stack — backend `can:` middleware + policies AND sidebar/UI gating.

## Researched role set (Indonesian HRIS)
Seeded as `is_system` roles (protected from deletion):
- `super-admin` — everything (granted via `Gate::before`).
- `hr-admin` — employees, contracts, organization, leave types, talent, attendance/shift view.
- `payroll-admin` — payroll runs, allowances/deductions, billing, employee view.
- `manager` — view team, approve leave/attendance, view shifts.
- `recruiter` — recruitment (jobs, candidates, pipeline, interviews).
- `outsourcing-coordinator` — vendors, placements, tracking, compliance, vendor billing.
- `employee` (ESS) — dashboard + own profile only.

Note: manager "team-only" data scoping is deferred (query-level concern, not this RBAC pass).

## Data model
New prefixed tables (per [10-migrations-and-models.mdc](/Users/ade/Sites/hris/.cursor/rules/10-migrations-and-models.mdc)):

```mermaid
erDiagram
    users ||--o{ rel_user_roles : has
    sys_roles ||--o{ rel_user_roles : assigned
    sys_roles ||--o{ rel_role_permissions : grants
    sys_permissions ||--o{ rel_role_permissions : in
    sys_roles {
        id pk
        string name
        string slug UK
        string description
        boolean is_system
    }
    sys_permissions {
        id pk
        string key UK
        string name
        string module
    }
```

- `sys_roles`: `name`, `slug` (unique), `description` nullable, `is_system` bool, `timestamps`, `softDeletes`. Model `Role`.
- `sys_permissions`: `key` (unique, `module.action`), `name`, `module`, `timestamps`. Model `Permission`.
- `rel_role_permissions`: `role_id` + `permission_id`, both `cascadeOnDelete`, unique composite.
- `rel_user_roles`: `user_id` + `role_id`, both `cascadeOnDelete`, unique composite.

Migration filenames use the `YYYY_MM_DD_NNNNNN_create_<table>_table.php` convention; `down()` drops pivots before parents.

## Backend wiring
- **Models:** `Role` (`belongsToMany` Permission + User), `Permission`, and a `HasRoles` trait on `User` adding `roles()`, `hasRole()`, `hasAnyRole()`, `hasPermission()`, `permissionKeys()` (cached per-request).
- **Gates:** in [AppServiceProvider.php](/Users/ade/Sites/hris/app/Providers/AppServiceProvider.php) `boot()`:
  - `Gate::before` → `super-admin` short-circuits to `true`.
  - Loop `Permission::pluck('key')` and `Gate::define($key, fn($u) => $u->hasPermission($key))`, wrapped in try/catch so it is migration-safe.
- **Route enforcement:** wrap existing route groups in [web.php](/Users/ade/Sites/hris/routes/web.php) with Laravel's built-in `can:<permission>` middleware (gates make this work with no custom middleware). New users/roles routes guarded by `can:users.*` / `can:roles.*`.
- **Policies:** replace stub returns in [EmployeePolicy.php](/Users/ade/Sites/hris/app/Policies/EmployeePolicy.php) with real `$user->can('employees.<action>')` checks.
- **Inertia share:** extend [HandleInertiaRequests.php](/Users/ade/Sites/hris/app/Http/Middleware/HandleInertiaRequests.php) `auth` payload with `roles` (slugs) and `permissions` (keys).

## User & Role management UI
- **Controllers:** `Admin\UserController` (resource: index/create/store/edit/update/destroy) + `Admin\RoleController` (resource). `StoreUserRequest`/`UpdateUserRequest` validate name/email/password and a `roles[]` array; sync via `$user->roles()->sync()`. Role form syncs `permissions[]`.
- **Safety guards:** cannot delete/demote the last `super-admin`; cannot delete `is_system` roles; cannot remove your own `super-admin` role.
- **Pages (Inertia + `HrisLayout`):**
  - `Pages/Admin/Users/Index.tsx` — table of users with role badges, search.
  - `Pages/Admin/Users/Create.tsx` + `Edit.tsx` — name/email/password + multi-select role checkboxes; optional link to existing employee.
  - `Pages/Admin/Roles/Index.tsx` — roles list.
  - `Pages/Admin/Roles/Edit.tsx` — permission matrix (module rows x action checkboxes).
- **Sidebar:** add `permission` (or `permissions`) field to `NavLeaf`/`NavParent` in [AppSidebar.tsx](/Users/ade/Sites/hris/resources/js/Components/layout/AppSidebar.tsx), add a `useCan()` helper reading shared `auth.permissions`, filter nav nodes (hide empty parents). Add an Admin-section "Users" + "Roles" group.

## Seeding
- `PermissionSeeder` — full `module.action` catalog (view/create/update/delete per module + specials like `leave.approve`, `payroll.run`, `users.assign-roles`).
- `RoleSeeder` — the 7 roles and their permission mappings.
- Update [DemoAdminUserSeeder.php](/Users/ade/Sites/hris/database/seeders/DemoAdminUserSeeder.php) to assign `super-admin` to the demo admin.
- Wire both into `DatabaseSeeder`.

## i18n
Add EN/ID keys (`navUsers`, `navRoles`, `permissions`, etc.) to `resources/js/i18n/translations.ts`, matching existing pattern.

## Verification
- `php artisan migrate:fresh --seed` round-trips cleanly (migrations + 7 roles + permissions).
- PHPUnit feature tests: a `payroll-admin` is 403'd from `employees.store`; `super-admin` passes; user create with `roles[]` persists pivots; last-super-admin guard blocks deletion.
- Manual: log in as demo admin → see Users/Roles menu; create a user with 2 roles; sidebar reflects each role's permissions.

## Out of scope
- Multi-tenant scoping of roles (`tenant_id`), team-only data scoping for managers, disabling public registration, API/Sanctum tokens.
