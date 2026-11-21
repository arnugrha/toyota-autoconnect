<?php

namespace App\Http\Requests;

use App\Models\Vehicle;
use App\Services\CreditSimulationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeasingApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'leasing_provider_id' => ['required', 'exists:leasing_providers,id'],
            'credit_simulation_id' => ['nullable', 'exists:credit_simulations,id'],
            'down_payment' => ['required', 'integer', 'min:1000000'],
            'tenor_months' => ['required', 'integer', Rule::in(CreditSimulationService::ALLOWED_TENORS)],
            'employment_type' => ['required', 'string', 'max:50'],
            'monthly_income' => ['required', 'integer', 'min:1000000'],
            'company_name' => ['nullable', 'string', 'max:100'],
            'id_card_number' => ['nullable', 'string', 'max:30'],
            'npwp_number' => ['nullable', 'string', 'max:30'],
            'marital_status' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:500'],
            'agree_terms' => ['accepted'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $vehicle = Vehicle::find($this->input('vehicle_id'));
            $dp = (int) $this->input('down_payment');

            if ($vehicle && $dp >= $vehicle->price) {
                $v->errors()->add('down_payment', 'Uang muka tidak boleh melebihi atau sama dengan harga kendaraan.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'vehicle_id.required' => 'Pilih mobil Toyota yang diajukan pembiayaannya.',
            'leasing_provider_id.required' => 'Pilih perusahaan leasing mitra resmi.',
            'down_payment.required' => 'Nominal uang muka (DP) wajib diisi.',
            'tenor_months.required' => 'Pilih lama tenor pembiayaan.',
            'employment_type.required' => 'Jenis pekerjaan atau sumber penghasilan wajib dipilih.',
            'monthly_income.required' => 'Estimasi penghasilan per bulan wajib diisi.',
            'agree_terms.accepted' => 'Anda harus menyetujui pernyataan pengajuan dan penerusan data ke pihak leasing.',
        ];
    }
}

