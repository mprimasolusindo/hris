<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Employee;
use Database\Seeders\Support\IndonesianDemoData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        $faker = IndonesianDemoData::makeFaker();
        $gender = fake()->randomElement(['male', 'female']);

        return [
            'tenant_id' => null,
            'company_id' => Company::factory(),
            'employee_code' => 'EMP-'.fake()->unique()->numerify('#####'),
            'full_name' => $gender === 'female'
                ? $faker->firstName('female').' '.$faker->lastName()
                : $faker->firstName('male').' '.$faker->lastName(),
            'email' => $faker->unique()->safeEmail(),
            'phone' => IndonesianDemoData::indonesianMobile($faker),
            'gender' => $gender,
            'birth_date' => $faker->dateTimeBetween('-55 years', '-22 years'),
            'marital_status' => fake()->randomElement(['single', 'married', 'divorced', 'widowed']),
            'religion' => fake()->randomElement(IndonesianDemoData::RELIGIONS),
            'status' => 'active',
            'join_date' => $faker->dateTimeBetween('-8 years', '-6 months'),
            'resign_date' => null,
        ];
    }

    /**
     * Deterministic demo row: unique email and employee code by sequence.
     */
    public function demoSequence(int $sequence, int $companyId): static
    {
        $faker = IndonesianDemoData::makeFaker();
        $gender = $sequence % 2 === 0 ? 'female' : 'male';

        return $this->state(fn () => [
            'tenant_id' => null,
            'company_id' => $companyId,
            'employee_code' => sprintf('EMP-%05d', $sequence),
            'full_name' => $gender === 'female'
                ? $faker->firstName('female').' '.$faker->lastName()
                : $faker->firstName('male').' '.$faker->lastName(),
            'email' => IndonesianDemoData::workEmail($sequence),
            'phone' => IndonesianDemoData::indonesianMobile($faker),
            'gender' => $gender,
            'birth_date' => $faker->dateTimeBetween('-52 years', '-23 years'),
            'marital_status' => fake()->randomElement(['single', 'married', 'divorced', 'widowed']),
            'religion' => fake()->randomElement(IndonesianDemoData::RELIGIONS),
            'status' => 'active',
            'join_date' => $faker->dateTimeBetween('-6 years', '-3 months'),
            'resign_date' => null,
        ]);
    }
}
