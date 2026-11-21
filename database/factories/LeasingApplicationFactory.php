<?php

namespace Database\Factories;

use App\Models\CreditSimulation;
use App\Models\LeasingApplication;
use App\Models\LeasingProvider;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeasingApplication>
 */
class LeasingApplicationFactory extends Factory
{
    protected $model = LeasingApplication::class;

    public function definition(): array
    {
        return [
            'code' => LeasingApplication::generateCode() . '-' . fake()->unique()->numerify('###'),
            'user_id' => User::factory()->customer(),
            'vehicle_id' => Vehicle::factory(),
            'credit_simulation_id' => null,
            'leasing_provider_id' => LeasingProvider::factory(),
            'sales_opportunity_id' => null,
            'status' => 'submitted',
            'vehicle_price' => 350000000,
            'down_payment' => 70000000,
            'tenor_months' => 36,
            'monthly_installment' => 9177778,
            'applicant_data' => [
                'employment_type' => 'Karyawan Swasta',
                'monthly_income' => 25000000,
                'company_name' => 'PT Astra International',
                'npwp_number' => '12.345.678.9-012.000',
                'marital_status' => 'Menikah',
                'id_card_number' => '3171012345670001',
            ],
            'notes' => 'Pengajuan via aplikasi web resmi.',
            'submitted_at' => now(),
        ];
    }
}

