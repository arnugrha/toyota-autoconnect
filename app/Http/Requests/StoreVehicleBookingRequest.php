<?php

namespace App\Http\Requests;

use App\Models\SalesOpportunity;
use App\Models\VehicleInventory;
use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'sales_opportunity_id' => ['required', 'integer', 'exists:sales_opportunities,id'],
            'sales_offer_id' => ['nullable', 'integer', 'exists:sales_offers,id'],
            'booking_fee' => ['nullable', 'numeric', 'min:0'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $opportunity = SalesOpportunity::find($this->input('sales_opportunity_id'));
            if (! $opportunity) {
                return;
            }

            // Periksa ketersediaan stok di cabang opportunity
            $inventory = VehicleInventory::where('vehicle_id', $opportunity->vehicle_id)
                ->where('branch_id', $opportunity->branch_id)
                ->first();

            if (! $inventory || $inventory->available_stock <= 0) {
                $validator->errors()->add(
                    'sales_opportunity_id',
                    'Stok unit kendaraan untuk cabang ini saat ini tidak mencukupi untuk pemesanan baru.'
                );
            }
        });
    }
}

