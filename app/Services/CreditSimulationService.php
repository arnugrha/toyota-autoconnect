<?php

namespace App\Services;

use App\Models\CreditSimulation;
use App\Models\LeasingProvider;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class CreditSimulationService
{
    /**
     * Allowed standard tenors in months.
     */
    public const ALLOWED_TENORS = [12, 24, 36, 48, 60, 72];

    /**
     * Calculate loan estimation based on flat interest rate approach.
     *
     * @return array{
     *     vehicle_price: int,
     *     down_payment: int,
     *     down_payment_percentage: float,
     *     financing_amount: int,
     *     interest_rate: float,
     *     tenor_months: int,
     *     tenor_years: float,
     *     total_interest: int,
     *     total_credit_payment: int,
     *     monthly_installment: int,
     *     calculation_method: string
     * }
     */
    public function calculate(
        int $vehiclePrice,
        int $downPayment,
        int $tenorMonths,
        float $interestRate,
        string $method = 'flat'
    ): array {
        if ($vehiclePrice <= 0) {
            throw new InvalidArgumentException('Harga kendaraan harus lebih besar dari 0.');
        }

        if ($downPayment <= 0 || $downPayment >= $vehiclePrice) {
            throw new InvalidArgumentException('Nilai uang muka (DP) harus lebih besar dari 0 dan lebih kecil dari harga kendaraan.');
        }

        if (! in_array($tenorMonths, self::ALLOWED_TENORS, true)) {
            throw new InvalidArgumentException('Pilihan tenor tidak valid.');
        }

        if ($interestRate < 0) {
            throw new InvalidArgumentException('Suku bunga tidak boleh negatif.');
        }

        $financingAmount = $vehiclePrice - $downPayment;
        $tenorYears = $tenorMonths / 12;
        $totalInterest = (int) round($financingAmount * ($interestRate / 100) * $tenorYears);
        $totalCreditPayment = $financingAmount + $totalInterest;
        $monthlyInstallment = (int) round($totalCreditPayment / $tenorMonths);
        $downPaymentPercentage = round(($downPayment / $vehiclePrice) * 100, 2);

        return [
            'vehicle_price' => $vehiclePrice,
            'down_payment' => $downPayment,
            'down_payment_percentage' => $downPaymentPercentage,
            'financing_amount' => $financingAmount,
            'interest_rate' => $interestRate,
            'tenor_months' => $tenorMonths,
            'tenor_years' => round($tenorYears, 1),
            'total_interest' => $totalInterest,
            'total_credit_payment' => $totalCreditPayment,
            'monthly_installment' => $monthlyInstallment,
            'calculation_method' => $method,
        ];
    }

    /**
     * Persist calculated simulation for an authenticated customer.
     */
    public function saveSimulation(
        User $user,
        Vehicle $vehicle,
        int $downPayment,
        int $tenorMonths,
        ?float $interestRate = null,
        ?LeasingProvider $provider = null,
        ?string $notes = null
    ): CreditSimulation {
        $rate = $interestRate ?? ($provider ? $provider->base_interest_rate : 6.00);

        $calculation = $this->calculate(
            vehiclePrice: $vehicle->price,
            downPayment: $downPayment,
            tenorMonths: $tenorMonths,
            interestRate: $rate,
        );

        return CreditSimulation::create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'leasing_provider_id' => $provider?->id,
            'vehicle_price' => $calculation['vehicle_price'],
            'down_payment' => $calculation['down_payment'],
            'down_payment_percentage' => $calculation['down_payment_percentage'],
            'financing_amount' => $calculation['financing_amount'],
            'interest_rate' => $calculation['interest_rate'],
            'tenor_months' => $calculation['tenor_months'],
            'total_interest' => $calculation['total_interest'],
            'monthly_installment' => $calculation['monthly_installment'],
            'calculation_method' => $calculation['calculation_method'],
            'notes' => $notes,
        ]);
    }

    /**
     * Compare a collection of credit simulations.
     */
    public function compareSimulations(Collection $simulations): array
    {
        return $simulations->map(function (CreditSimulation $sim) {
            return [
                'id' => $sim->id,
                'vehicle_name' => $sim->vehicle->name,
                'provider_name' => $sim->leasingProvider?->name ?? 'Estimasi Standar',
                'vehicle_price' => $sim->vehicle_price,
                'down_payment' => $sim->down_payment,
                'down_payment_percentage' => $sim->down_payment_percentage,
                'financing_amount' => $sim->financing_amount,
                'interest_rate' => $sim->interest_rate,
                'tenor_months' => $sim->tenor_months,
                'total_interest' => $sim->total_interest,
                'monthly_installment' => $sim->monthly_installment,
                'created_at' => $sim->created_at->format('d M Y, H:i'),
            ];
        })->toArray();
    }
}

