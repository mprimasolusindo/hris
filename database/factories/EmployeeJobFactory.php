<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeJob;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeJob>
 */
class EmployeeJobFactory extends Factory
{
    protected $model = EmployeeJob::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'company_id' => Company::factory(),
            'department_id' => Department::factory(),
            'position_id' => Position::factory(),
            'manager_id' => null,
            'employment_type' => fake()->randomElement(['pkwtt', 'pkwt', 'outsourcing', 'magang']),
            'start_date' => fake()->dateTimeBetween('-5 years', '-1 year'),
            'end_date' => null,
        ];
    }
}
