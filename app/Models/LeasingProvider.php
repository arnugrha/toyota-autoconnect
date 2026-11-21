<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeasingProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'base_interest_rate',
        'min_down_payment_percentage',
        'max_tenor_months',
        'logo_url',
        'endpoint_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_interest_rate' => 'float',
            'min_down_payment_percentage' => 'float',
            'max_tenor_months' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(LeasingApplication::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}

