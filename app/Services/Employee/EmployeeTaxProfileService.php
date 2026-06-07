<?php

namespace App\Services\Employee;

use App\Models\Employee;
use App\Models\EmployeeTaxProfile;
use App\Services\Employee\Concerns\ManagesEmployeeSubResource;
use Illuminate\Database\Eloquent\Model;

/**
 * PPh21 / PTKP / TER tax profile (PMK 168/2023).
 *
 * @see .cursor/agents/hr-research-indonesia.md
 */
class EmployeeTaxProfileService
{
    use ManagesEmployeeSubResource;

    protected function modelClass(): string
    {
        return EmployeeTaxProfile::class;
    }

    public function upsertForEmployee(Employee $employee, array $data): EmployeeTaxProfile
    {
        $profile = $employee->taxProfile;

        if ($profile) {
            $profile->update($data);

            return $profile->fresh();
        }

        return EmployeeTaxProfile::query()->create([
            ...$data,
            'employee_id' => $employee->id,
        ]);
    }

    public function delete(Model $model): void
    {
        $model->delete();
    }
}
