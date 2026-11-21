<?php

namespace Database\Factories;

use App\Models\LeasingProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeasingProvider>
 */
class LeasingProviderFactory extends Factory
{
    protected $model = LeasingProvider::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Finance',
            'code' => fake()->unique()->slug(2),
            'description' => fake()->sentence(),
            'base_interest_rate' => 6.25,
            'min_down_payment_percentage' => 20.00,
            'max_tenor_months' => 60,
            'is_active' => true,
        ];
    }
}

