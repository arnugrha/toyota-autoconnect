<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\TestDriveBooking;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestDriveBookingFactory extends Factory
{
    protected $model = TestDriveBooking::class;

    public function definition(): array
    {
        $user = User::factory()->customer()->create();

        return [
            'code' => TestDriveBooking::generateCode(),
            'user_id' => $user->id,
            'vehicle_id' => Vehicle::factory(),
            'branch_id' => Branch::factory(),
            'lead_id' => null,
            'assigned_to' => null,
            'scheduled_at' => now()->addDays(2)->setHour(10)->setMinute(0)->setSecond(0),
            'duration_minutes' => 60,
            'status' => 'pending',
            'customer_name' => $user->name,
            'customer_phone' => $user->phone ?? '081234567890',
            'customer_email' => $user->email,
            'customer_note' => fake()->sentence(),
            'staff_note' => null,
            'confirmed_at' => null,
            'completed_at' => null,
            'cancelled_at' => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => [
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'staff_note' => 'Jadwal telah dikonfirmasi oleh customer service.',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'confirmed_at' => now()->subDay(),
            'completed_at' => now(),
            'staff_note' => 'Test drive telah selesai dilaksanakan dengan baik.',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => 'Permintaan pembatalan dari pelanggan.',
        ]);
    }
}

