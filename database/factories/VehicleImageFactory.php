<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\VehicleImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleImage>
 */
class VehicleImageFactory extends Factory
{
    protected $model = VehicleImage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'image_path' => 'images/placeholder-car.svg',
            'alt_text' => 'Toyota Vehicle Image',
            'sort_order' => 1,
            'is_primary' => true,
        ];
    }
}
