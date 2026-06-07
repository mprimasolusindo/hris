<?php

namespace App\Actions\Employee;

use App\Models\Employee;
use App\Services\Employee\EmployeeService;

class ArchiveEmployeeAction
{
    public function __construct(private EmployeeService $employees) {}

    public function __invoke(Employee $employee): void
    {
        $this->employees->archive($employee);
    }
}
