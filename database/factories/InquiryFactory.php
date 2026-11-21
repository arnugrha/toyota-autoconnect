<?php

namespace Database\Factories;

use App\Models\Inquiry;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    protected $model = Inquiry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->customer(),
            'vehicle_id' => Vehicle::factory(),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->numerify('0812########'),
            'subject' => fake()->randomElement([
                'Penawaran Harga OTR & Diskon',
                'Konsultasi Simulasi Kredit',
                'Pemesanan Jadwal Test Drive',
                'Ketersediaan Unit & Pilihan Warna',
                'Konsultasi Spesifikasi Teknis',
            ]),
            'message' => fake()->paragraph(),
            'status' => 'new',
            'submitted_at' => now(),
        ];
    }
}
