<?php

namespace App\Services\Employee;

use App\Models\EmployeeBankAccount;
use App\Services\Employee\Concerns\ManagesEmployeeSubResource;

class EmployeeBankAccountService
{
    use ManagesEmployeeSubResource;

    protected function modelClass(): string
    {
        return EmployeeBankAccount::class;
    }
}
