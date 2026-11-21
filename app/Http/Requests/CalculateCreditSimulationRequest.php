<?php

namespace App\Http\Requests;

use App\Models\Vehicle;
use App\Services\CreditSimulationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CalculateCreditSimulationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'down_payment' => ['required', 'integer', 'min:1000000'],
            'tenor_months' => ['required', 'integer', Rule::in(CreditSimulationService::ALLOWED_TENORS)],
            'leasing_provider_id' => ['nullable', 'exists:leasing_providers,id'],
            'interest_rate' => ['nullable', 'numeric', 'min:0', 'max:50'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $vehicle = Vehicle::find($this->input('vehicle_id'));
            $dp = (int) $this->input('down_payment');

            if ($vehicle && $dp >= $vehicle->price) {
                $v->errors()->add('down_payment', 'Nilai uang muka (DP) tidak boleh melebihi atau sama dengan harga kendaraan.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'vehicle_id.required' => 'Pilih unit kendaraan yang ingin disimulasikan.',
            'down_payment.required' => 'Uang muka (DP) wajib diisi.',
            'down_payment.min' => 'Uang muka minimal adalah Rp 1.000.000.',
            'tenor_months.required' => 'Pilih lama tenor pembiayaan.',
            'tenor_months.in' => 'Pilihan tenor harus 12, 24, 36, 48, 60, atau 72 bulan.',
        ];
    }
}

