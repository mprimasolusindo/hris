<?php

namespace App\Services\Employee;

use App\Models\EmployeeEmergencyContact;
use App\Services\Employee\Concerns\ManagesEmployeeSubResource;

class EmployeeEmergencyContactService
{
    use ManagesEmployeeSubResource;

    protected function modelClass(): string
    {
        return EmployeeEmergencyContact::class;
    }
}
