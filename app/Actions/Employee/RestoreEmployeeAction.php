<?php

namespace App\Actions\Employee;

use App\Models\Employee;
use App\Services\Employee\EmployeeService;

class RestoreEmployeeAction
{
    public function __construct(private EmployeeService $employees) {}

    public function __invoke(Employee $employee): void
    {
        $this->employees->restore($employee);
    }
}
