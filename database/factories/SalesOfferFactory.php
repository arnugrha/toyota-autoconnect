<?php

namespace Database\Factories;

use App\Models\SalesOffer;
use App\Models\SalesOpportunity;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOfferFactory extends Factory
{
    protected $model = SalesOffer::class;

    public function definition(): array
    {
        $vehicle = Vehicle::factory()->create(['price' => 400000000]);
        $opportunity = SalesOpportunity::factory()->create([
            'vehicle_id' => $vehicle->id,
        ]);

        $price = $vehicle->price;
        $discount = 10000000;
        $additionalCost = 2500000;
        $total = $price - $discount + $additionalCost;

        return [
            'code' => SalesOffer::generateCode(),
            'sales_opportunity_id' => $opportunity->id,
            'customer_id' => $opportunity->user_id,
            'vehicle_id' => $vehicle->id,
            'vehicle_price' => $price,
            'discount_amount' => $discount,
            'additional_cost' => $additionalCost,
            'total_amount' => $total,
            'valid_until' => now()->addDays(14),
            'status' => 'draft',
            'notes' => fake()->sentence(),
            'created_by' => null,
        ];
    }
}

