<?php

namespace App\Services\Employee;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    public function create(array $data): Employee
    {
        return DB::transaction(fn () => Employee::query()->create($data));
    }

    public function update(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data) {
            $employee->update($data);

            return $employee->fresh();
        });
    }

    public function archive(Employee $employee): void
    {
        DB::transaction(fn () => $employee->delete());
    }

    public function restore(Employee $employee): void
    {
        DB::transaction(fn () => $employee->restore());
    }
}
