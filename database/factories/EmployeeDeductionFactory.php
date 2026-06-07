<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeDeduction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeDeduction>
 */
class EmployeeDeductionFactory extends Factory
{
    protected $model = EmployeeDeduction::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'component_id' => null,
            'name' => 'BPJS Kesehatan',
            'value' => 1,
            'status' => 'active',
            'recurring' => true,
        ];
    }
}
