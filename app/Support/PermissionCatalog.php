<?php

namespace App\Support;

/**
 * Canonical permission catalog for the HRIS RBAC system.
 *
 * Keys follow module.action convention (e.g. employees.view).
 */
class PermissionCatalog
{
    /**
     * @return array<int, array{key: string, name: string, module: string}>
     */
    public static function all(): array
    {
        $permissions = [];

        $modules = [
            'dashboard' => ['View Dashboard'],
            'employees' => ['View Employees', 'Create Employees', 'Update Employees', 'Delete Employees', 'Archive Employees', 'Restore Employees', 'Bulk Update Employees', 'Import Employees', 'Export Employees'],
            'attendance' => ['View Attendance', 'Create Attendance', 'Update Attendance'],
            'overtime' => ['View Overtime', 'Create Overtime', 'Update Overtime', 'Delete Overtime', 'Approve Overtime'],
            'payroll' => ['View Payroll', 'Create Payroll', 'Update Payroll', 'Delete Payroll', 'Run Payroll', 'Bulk Update Payroll'],
            'payroll.master-allowances' => ['View Master Allowances', 'Create Master Allowances', 'Update Master Allowances', 'Delete Master Allowances'],
            'payroll.master-deductions' => ['View Master Deductions', 'Create Master Deductions', 'Update Master Deductions', 'Delete Master Deductions'],
            'payroll.bpjs-config' => ['View Bpjs Config', 'Create Bpjs Config', 'Update Bpjs Config', 'Delete Bpjs Config'],
            'payroll.tax-rules' => ['View Tax Rules', 'Create Tax Rules', 'Update Tax Rules', 'Delete Tax Rules'],
            'organization.companies' => ['View Companies', 'Create Companies', 'Update Companies', 'Delete Companies'],
            'organization.sites' => ['View Sites', 'Create Sites', 'Update Sites', 'Delete Sites'],
            'organization.departments' => ['View Departments', 'Create Departments', 'Update Departments', 'Delete Departments'],
            'organization.positions' => ['View Positions', 'Create Positions', 'Update Positions', 'Delete Positions'],
            'shifts' => ['View Shifts', 'Create Shifts', 'Update Shifts', 'Delete Shifts', 'Assign Shifts'],
            'leave' => ['View Leave', 'Create Leave', 'Update Leave', 'Cancel Leave'],
            'leave.approvals' => ['View Leave Approvals', 'Approve Leave'],
            'leave.balance' => ['View Leave Balance'],
            'leave.types' => ['View Leave Types', 'Create Leave Types', 'Update Leave Types', 'Delete Leave Types'],
            'vendors' => ['View Vendors', 'Create Vendors', 'Update Vendors', 'Delete Vendors'],
            'outsourcing' => ['View Outsourcing', 'Create Outsourcing', 'Update Outsourcing', 'Delete Outsourcing'],
            'outsourcing.tracking' => ['View Placement Tracking'],
            'outsourcing.compliance' => ['View Compliance', 'Resolve Compliance'],
            'vendor-billing' => ['View Vendor Billing', 'Create Vendor Billing', 'Update Vendor Billing'],
            'contracts' => ['View Contracts', 'Create Contracts', 'Update Contracts', 'Delete Contracts'],
            'recruitment.jobs' => ['View Jobs', 'Create Jobs', 'Update Jobs', 'Delete Jobs'],
            'recruitment.candidates' => ['View Candidates', 'Create Candidates', 'Update Candidates', 'Delete Candidates'],
            'recruitment.pipeline' => ['View Pipeline', 'Manage Pipeline'],
            'recruitment.interviews' => ['View Interviews', 'Create Interviews', 'Update Interviews', 'Delete Interviews'],
            'talent.performance' => ['View Performance', 'Create Performance', 'Update Performance', 'Delete Performance'],
            'talent.training' => ['View Training', 'Create Training', 'Update Training', 'Delete Training', 'Assign Training'],
            'talent.talent-pool' => ['View Talent Pool', 'Create Talent Pool', 'Update Talent Pool', 'Delete Talent Pool'],
            'talent.succession' => ['View Succession', 'Create Succession', 'Update Succession', 'Delete Succession'],
            'talent.nine-box' => ['View Nine Box', 'Create Nine Box', 'Update Nine Box', 'Delete Nine Box'],
            'master.allowance-types' => ['View Allowance Types', 'Create Allowance Types', 'Update Allowance Types', 'Delete Allowance Types'],
            'search' => ['View Search'],
            'saas.tenants' => ['View Tenants', 'Create Tenants', 'Update Tenants', 'Delete Tenants'],
            'saas.plans' => ['View Plans', 'Create Plans', 'Update Plans', 'Delete Plans'],
            'saas.subscriptions' => ['View Subscriptions', 'Create Subscriptions', 'Update Subscriptions', 'Delete Subscriptions'],
            'saas.payments' => ['View Payments', 'Create Payments', 'Update Payments', 'Delete Payments'],
            'bug-reports' => ['View Bug Reports', 'Create Bug Reports', 'Update Bug Reports', 'Delete Bug Reports', 'Manage Bug Report Settings'],
            'users' => ['View Users', 'Create Users', 'Update Users', 'Delete Users', 'Assign Roles'],
            'roles' => ['View Roles', 'Create Roles', 'Update Roles', 'Delete Roles'],
        ];

        $actionMap = [
            'View' => 'view',
            'Create' => 'create',
            'Update' => 'update',
            'Delete' => 'delete',
            'Archive' => 'archive',
            'Restore' => 'restore',
            'Bulk Update' => 'bulk-update',
            'Import' => 'import',
            'Export' => 'export',
            'Run' => 'run',
            'Assign' => 'assign',
            'Cancel' => 'cancel',
            'Approve' => 'approve',
            'Resolve' => 'resolve',
            'Manage' => 'manage',
        ];

        foreach ($modules as $module => $names) {
            foreach ($names as $name) {
                $action = 'view';
                foreach ($actionMap as $prefix => $slug) {
                    if (str_starts_with($name, $prefix)) {
                        $action = $slug;
                        break;
                    }
                }

                $permissions[] = [
                    'key' => "{$module}.{$action}",
                    'name' => $name,
                    'module' => $module,
                ];
            }
        }

        return $permissions;
    }

    /**
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return array_column(self::all(), 'key');
    }

    /**
     * @param  array<int, string>  $patterns  Wildcard patterns (e.g. employees.*)
     * @return array<int, string>
     */
    public static function resolve(array $patterns): array
    {
        $keys = self::keys();
        $resolved = [];

        foreach ($patterns as $pattern) {
            if (str_ends_with($pattern, '.*')) {
                $prefix = substr($pattern, 0, -1);
                foreach ($keys as $key) {
                    if (str_starts_with($key, $prefix)) {
                        $resolved[] = $key;
                    }
                }
            } else {
                $resolved[] = $pattern;
            }
        }

        return array_values(array_unique($resolved));
    }
}
