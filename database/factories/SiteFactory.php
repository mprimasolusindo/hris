<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Site>
 */
class SiteFactory extends Factory
{
    protected $model = Site::class;

    public function definition(): array
    {
        $faker = fake('id_ID');

        return [
            'company_id' => Company::factory(),
            'name' => 'Kantor '.$faker->city(),
            'location' => $faker->streetAddress().', '.$faker->city(),
        ];
    }
}
