<?php

namespace App\Http\Requests;

use App\Models\SalesOpportunity;
use Illuminate\Foundation\Http\FormRequest;

class StoreSalesOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'sales_opportunity_id' => ['required', 'integer', 'exists:sales_opportunities,id'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'additional_cost' => ['nullable', 'numeric', 'min:0'],
            'valid_until' => ['required', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'valid_until.after_or_equal' => 'Masa berlaku penawaran tidak boleh berada di masa lalu.',
            'discount_amount.min' => 'Diskon tidak boleh bernilai negatif.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $opportunity = SalesOpportunity::with('vehicle')->find($this->input('sales_opportunity_id'));
            if (! $opportunity) {
                return;
            }

            $vehiclePrice = $opportunity->vehicle->price ?? 0;
            $discount = (int) $this->input('discount_amount', 0);

            // Validasi batas diskon maksimal: tidak boleh melebihi 25% dari harga kendaraan
            $maxDiscount = (int) round($vehiclePrice * 0.25);
            if ($discount > $maxDiscount) {
                $validator->errors()->add(
                    'discount_amount',
                    'Diskon melebihi batas maksimal yang diizinkan (maksimal 25% atau Rp '.number_format($maxDiscount, 0, ',', '.').').'
                );
            }
        });
    }
}

