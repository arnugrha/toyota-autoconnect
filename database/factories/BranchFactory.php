<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        $code = 'BR-'.strtoupper(fake()->unique()->bothify('??##'));

        return [
            'name' => 'AutoConnect '.fake()->city(),
            'code' => $code,
            'address' => fake()->address(),
            'city' => fake()->city(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'is_active' => true,
            'opening_time' => '08:30',
            'closing_time' => '17:00',
            'slot_duration_minutes' => 60,
            'slot_capacity' => 2,
        ];
    }
}

