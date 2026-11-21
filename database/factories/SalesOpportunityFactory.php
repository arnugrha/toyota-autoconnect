<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\SalesOpportunity;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOpportunityFactory extends Factory
{
    protected $model = SalesOpportunity::class;

    public function definition(): array
    {
        $vehicle = Vehicle::factory()->create();

        return [
            'code' => SalesOpportunity::generateCode(),
            'lead_id' => null,
            'user_id' => User::factory()->customer(),
            'vehicle_id' => $vehicle->id,
            'branch_id' => Branch::factory(),
            'assigned_to' => null,
            'stage' => 'prospecting',
            'estimated_value' => $vehicle->price ?? 350000000,
            'notes' => fake()->paragraph(),
            'lost_reason' => null,
            'closed_at' => null,
        ];
    }
}

