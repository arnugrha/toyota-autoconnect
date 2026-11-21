<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditSimulation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'leasing_provider_id',
        'vehicle_price',
        'down_payment',
        'down_payment_percentage',
        'financing_amount',
        'interest_rate',
        'tenor_months',
        'total_interest',
        'monthly_installment',
        'calculation_method',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'vehicle_price' => 'integer',
            'down_payment' => 'integer',
            'down_payment_percentage' => 'float',
            'financing_amount' => 'integer',
            'interest_rate' => 'float',
            'tenor_months' => 'integer',
            'total_interest' => 'integer',
            'monthly_installment' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function leasingProvider(): BelongsTo
    {
        return $this->belongsTo(LeasingProvider::class);
    }

    public function leasingApplications(): HasMany
    {
        return $this->hasMany(LeasingApplication::class);
    }

    public function getFormattedVehiclePriceAttribute(): string
    {
        return 'Rp ' . number_format($this->vehicle_price, 0, ',', '.');
    }

    public function getFormattedDownPaymentAttribute(): string
    {
        return 'Rp ' . number_format($this->down_payment, 0, ',', '.');
    }

    public function getFormattedFinancingAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->financing_amount, 0, ',', '.');
    }

    public function getFormattedTotalInterestAttribute(): string
    {
        return 'Rp ' . number_format($this->total_interest, 0, ',', '.');
    }

    public function getFormattedMonthlyInstallmentAttribute(): string
    {
        return 'Rp ' . number_format($this->monthly_installment, 0, ',', '.');
    }

    public function getTenorYearsAttribute(): float
    {
        return round($this->tenor_months / 12, 1);
    }
}

