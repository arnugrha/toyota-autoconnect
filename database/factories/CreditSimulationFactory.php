<?php

namespace Database\Factories;

use App\Models\CreditSimulation;
use App\Models\LeasingProvider;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CreditSimulation>
 */
class CreditSimulationFactory extends Factory
{
    protected $model = CreditSimulation::class;

    public function definition(): array
    {
        $vehiclePrice = 350000000;
        $dp = 70000000; // 20%
        $financingAmount = $vehiclePrice - $dp;
        $interestRate = 6.00;
        $tenor = 36;
        $totalInterest = (int) round($financingAmount * ($interestRate / 100) * ($tenor / 12));
        $monthlyInstallment = (int) round(($financingAmount + $totalInterest) / $tenor);

        return [
            'user_id' => User::factory()->customer(),
            'vehicle_id' => Vehicle::factory(),
            'leasing_provider_id' => LeasingProvider::factory(),
            'vehicle_price' => $vehiclePrice,
            'down_payment' => $dp,
            'down_payment_percentage' => 20.00,
            'financing_amount' => $financingAmount,
            'interest_rate' => $interestRate,
            'tenor_months' => $tenor,
            'total_interest' => $totalInterest,
            'monthly_installment' => $monthlyInstallment,
            'calculation_method' => 'flat',
        ];
    }
}

