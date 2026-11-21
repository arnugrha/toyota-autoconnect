<?php

namespace Database\Factories;

use App\Models\Inquiry;
use App\Models\Lead;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lead_number' => Lead::generateLeadNumber().'-'.fake()->unique()->numerify('####'),
            'customer_id' => User::factory()->customer(),
            'inquiry_id' => null,
            'vehicle_id' => Vehicle::factory(),
            'assigned_to' => null,
            'status' => 'new',
            'source' => 'website_inquiry',
            'notes' => fake()->sentence(),
            'next_follow_up_at' => now()->addDays(2),
            'last_activity_at' => now(),
        ];
    }

    public function assignedTo(User $sales): static
    {
        return $this->state(fn () => [
            'assigned_to' => $sales->id,
        ]);
    }

    public function withInquiry(Inquiry $inquiry): static
    {
        return $this->state(fn () => [
            'inquiry_id' => $inquiry->id,
            'customer_id' => $inquiry->user_id,
            'vehicle_id' => $inquiry->vehicle_id,
        ]);
    }
}
