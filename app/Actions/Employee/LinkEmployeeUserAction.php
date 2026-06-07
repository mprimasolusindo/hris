<?php

namespace App\Actions\Employee;

use App\Models\Employee;
use App\Models\User;

class LinkEmployeeUserAction
{
    public function __invoke(Employee $employee, ?int $userId): Employee
    {
        if ($userId !== null) {
            User::query()->findOrFail($userId);
        }

        $employee->update(['user_id' => $userId]);

        return $employee->fresh();
    }
}
