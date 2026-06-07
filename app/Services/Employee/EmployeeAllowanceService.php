<?php

namespace App\Services\Employee;

use App\Models\EmployeeAllowance;
use App\Services\Employee\Concerns\ManagesEmployeeSubResource;

class EmployeeAllowanceService
{
    use ManagesEmployeeSubResource;

    protected function modelClass(): string
    {
        return EmployeeAllowance::class;
    }
}
