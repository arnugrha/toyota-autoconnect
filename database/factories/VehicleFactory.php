<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $baseName = fake()->randomElement([
            'Toyota Avanza',
            'Toyota Veloz',
            'Toyota Innova',
            'Toyota Fortuner',
            'Toyota Yaris',
            'Toyota Corolla',
            'Toyota Camry',
            'Toyota Hilux',
            'Toyota Hiace',
            'Toyota Raize',
            'Toyota Rush',
            'Toyota Agya',
            'Toyota Alphard',
            'Toyota Voxy',
            'Toyota bZ4X',
            'Toyota Land Cruiser',
        ]);

        $name = $baseName.' '.fake()->unique()->lexify('???-').fake()->unique()->numerify('#####');

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'category' => fake()->randomElement(['MPV', 'SUV', 'Hatchback', 'Sedan', 'Commercial']),
            'variant' => '1.5 G CVT',
            'description' => fake()->paragraph(),
            'price' => fake()->numberBetween(200000000, 900000000),
            'transmission' => 'Automatic',
            'fuel_type' => 'Bensin',
            'engine_capacity' => '1.496 cc',
            'passenger_capacity' => 7,
            'dimensions' => '4.395 x 1.730 x 1.700 mm',
            'status' => 'available',
            'is_featured' => false,
            'published_at' => now()->subDay(),
        ];
    }

    /**
     * State for unpublished draft vehicles.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => null,
        ]);
    }

    /**
     * State for featured vehicles.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * State with null price.
     */
    public function withoutPrice(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => null,
        ]);
    }
}
