<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $day = fake()->dateTimeBetween('-60 days', '-1 day');
        $clockIn = (clone $day)->setTime(8, fake()->numberBetween(0, 25), 0);
        $clockOut = (clone $day)->setTime(17, fake()->numberBetween(0, 35), 0);

        return [
            'employee_id' => Employee::factory(),
            'site_id' => null,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'latitude' => fake()->latitude(-6.4, -6.1),
            'longitude' => fake()->longitude(106.7, 107.1),
            'status' => fake()->randomElement(['present', 'present', 'present', 'late']),
        ];
    }
}
