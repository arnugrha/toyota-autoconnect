<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\SalesOpportunity;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use App\Models\VehicleInventory;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleBookingFactory extends Factory
{
    protected $model = VehicleBooking::class;

    public function definition(): array
    {
        $vehicle = Vehicle::factory()->create(['price' => 380000000]);
        $branch = Branch::factory()->create();
        $inventory = VehicleInventory::firstOrCreate(
            ['vehicle_id' => $vehicle->id, 'branch_id' => $branch->id],
            ['stock_quantity' => 5, 'reserved_quantity' => 0]
        );
        $opportunity = SalesOpportunity::factory()->create([
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
        ]);

        return [
            'code' => VehicleBooking::generateCode(),
            'sales_opportunity_id' => $opportunity->id,
            'sales_offer_id' => null,
            'customer_id' => $opportunity->user_id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'inventory_id' => $inventory->id,
            'vehicle_price' => $vehicle->price,
            'discount_amount' => 5000000,
            'booking_fee' => 5000000,
            'booking_date' => now()->toDateString(),
            'expires_at' => now()->addDays(7),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'cancellation_reason' => null,
            'notes' => fake()->sentence(),
            'created_by' => null,
            'confirmed_by' => null,
            'confirmed_at' => null,
            'cancelled_at' => null,
        ];
    }
}

