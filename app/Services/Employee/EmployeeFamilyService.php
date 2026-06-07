<?php

namespace App\Services\Employee;

use App\Models\EmployeeFamilyMember;
use App\Services\Employee\Concerns\ManagesEmployeeSubResource;

class EmployeeFamilyService
{
    use ManagesEmployeeSubResource;

    protected function modelClass(): string
    {
        return EmployeeFamilyMember::class;
    }
}
