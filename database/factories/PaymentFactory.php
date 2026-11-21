<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use App\Models\VehicleBooking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'code' => Payment::generateCode() . '-' . fake()->unique()->numerify('###'),
            'vehicle_booking_id' => VehicleBooking::factory(),
            'user_id' => User::factory()->customer(),
            'gateway' => 'midtrans',
            'order_id' => 'BOOK-' . fake()->unique()->numerify('#####') . '-' . time(),
            'amount' => 5000000,
            'payment_method' => 'bank_transfer',
            'status' => 'pending',
            'snap_token' => 'mock-snap-token-' . fake()->uuid(),
            'snap_redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/' . fake()->uuid(),
        ];
    }
}

