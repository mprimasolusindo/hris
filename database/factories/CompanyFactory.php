<?php

namespace Database\Factories;

use App\Models\Company;
use Database\Seeders\Support\IndonesianDemoData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'name' => fake()->unique()->randomElement(IndonesianDemoData::COMPANY_NAMES).' '.fake()->companySuffix(),
            'type' => 'main',
        ];
    }
}
