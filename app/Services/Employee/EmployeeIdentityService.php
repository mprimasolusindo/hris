<?php

namespace App\Services\Employee;

use App\Models\Employee;
use App\Models\EmployeeIdentity;
use App\Services\Employee\Concerns\ManagesEmployeeSubResource;
use Illuminate\Database\Eloquent\Model;

/**
 * Indonesian identity fields (NIK, NPWP, BPJS).
 *
 * @see .cursor/agents/hr-research-indonesia.md
 */
class EmployeeIdentityService
{
    use ManagesEmployeeSubResource;

    protected function modelClass(): string
    {
        return EmployeeIdentity::class;
    }

    public function upsertForEmployee(Employee $employee, array $data): EmployeeIdentity
    {
        $identity = $employee->identity;

        if ($identity) {
            $identity->update($data);

            return $identity->fresh();
        }

        return EmployeeIdentity::query()->create([
            ...$data,
            'employee_id' => $employee->id,
        ]);
    }

    public function delete(Model $model): void
    {
        $model->delete();
    }
}
