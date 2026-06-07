<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeSite;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeSite>
 */
class EmployeeSiteFactory extends Factory
{
    protected $model = EmployeeSite::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'site_id' => Site::factory(),
            'start_date' => fake()->dateTimeBetween('-4 years', '-1 year'),
            'end_date' => null,
        ];
    }
}
