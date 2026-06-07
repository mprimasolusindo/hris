<?php

namespace App\Services\Employee;

use App\Models\EmployeeDeduction;
use App\Services\Employee\Concerns\ManagesEmployeeSubResource;

class EmployeeDeductionService
{
    use ManagesEmployeeSubResource;

    protected function modelClass(): string
    {
        return EmployeeDeduction::class;
    }
}
