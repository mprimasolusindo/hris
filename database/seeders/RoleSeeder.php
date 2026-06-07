<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * @return array<string, array{name: string, description: string, is_system: bool, permissions: array<int, string>}>
     */
    public static function definitions(): array
    {
        return [
            'super-admin' => [
                'name' => 'Super Admin',
                'description' => 'Full system access including user and role management.',
                'is_system' => true,
                'permissions' => ['*'],
            ],
            'hr-admin' => [
                'name' => 'HR Admin',
                'description' => 'Manages employees, contracts, organization, leave types, and talent modules.',
                'is_system' => true,
                'permissions' => [
                    'dashboard.*',
                    'search.view',
                    'employees.*',
                    'contracts.*',
                    'organization.*',
                    'leave.types.*',
                    'leave.balance.*',
                    'leave.view',
                    'attendance.view',
                    'shifts.view',
                    'talent.*',
                    'master.allowance-types.view',
                    'bug-reports.view',
                    'bug-reports.create',
                ],
            ],
            'payroll-admin' => [
                'name' => 'Payroll Admin',
                'description' => 'Runs payroll, manages allowances/deductions, and vendor billing.',
                'is_system' => true,
                'permissions' => [
                    'dashboard.*',
                    'search.view',
                    'employees.view',
                    'payroll.*',
                    'payroll.master-allowances.*',
                    'payroll.master-deductions.*',
                    'master.allowance-types.*',
                    'vendor-billing.*',
                    'bug-reports.view',
                    'bug-reports.create',
                ],
            ],
            'manager' => [
                'name' => 'Manager',
                'description' => 'Approves leave and attendance for their team.',
                'is_system' => true,
                'permissions' => [
                    'dashboard.*',
                    'search.view',
                    'employees.view',
                    'attendance.view',
                    'leave.view',
                    'leave.create',
                    'leave.approvals.*',
                    'leave.balance.view',
                    'shifts.view',
                    'bug-reports.view',
                    'bug-reports.create',
                ],
            ],
            'recruiter' => [
                'name' => 'Recruiter',
                'description' => 'Manages recruitment pipeline, jobs, and candidates.',
                'is_system' => true,
                'permissions' => [
                    'dashboard.*',
                    'search.view',
                    'recruitment.*',
                    'bug-reports.view',
                    'bug-reports.create',
                ],
            ],
            'outsourcing-coordinator' => [
                'name' => 'Outsourcing Coordinator',
                'description' => 'Manages vendors, placements, tracking, compliance, and vendor billing.',
                'is_system' => true,
                'permissions' => [
                    'dashboard.*',
                    'search.view',
                    'employees.view',
                    'vendors.*',
                    'outsourcing.*',
                    'outsourcing.tracking.*',
                    'outsourcing.compliance.*',
                    'vendor-billing.*',
                    'bug-reports.view',
                    'bug-reports.create',
                ],
            ],
            'employee' => [
                'name' => 'Employee (ESS)',
                'description' => 'Self-service access to dashboard and own profile.',
                'is_system' => true,
                'permissions' => [
                    'dashboard.*',
                    'search.view',
                    'employees.view',
                    'attendance.view',
                    'leave.view',
                    'leave.create',
                    'leave.balance.view',
                    'bug-reports.view',
                    'bug-reports.create',
                ],
            ],
        ];
    }

    public function run(): void
    {
        $allPermissionIds = Permission::query()->pluck('id', 'key');

        foreach (self::definitions() as $slug => $definition) {
            $role = Role::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'is_system' => $definition['is_system'],
                ]
            );

            if (in_array('*', $definition['permissions'], true)) {
                $role->permissions()->sync($allPermissionIds->values()->all());

                continue;
            }

            $keys = PermissionCatalog::resolve($definition['permissions']);
            $permissionIds = collect($keys)
                ->map(fn (string $key) => $allPermissionIds->get($key))
                ->filter()
                ->values()
                ->all();

            $role->permissions()->sync($permissionIds);
        }
    }
}
