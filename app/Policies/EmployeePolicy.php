<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('employees.view');
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->can('employees.view');
    }

    public function create(User $user): bool
    {
        return $user->can('employees.create');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->can('employees.update');
    }

    public function archive(User $user, Employee $employee): bool
    {
        return $user->can('employees.archive');
    }

    public function restore(User $user, Employee $employee): bool
    {
        return $user->can('employees.restore');
    }

    public function bulkUpdate(User $user): bool
    {
        return $user->can('employees.bulk-update');
    }

    public function import(User $user): bool
    {
        return $user->can('employees.import');
    }

    public function export(User $user): bool
    {
        return $user->can('employees.export');
    }
}
