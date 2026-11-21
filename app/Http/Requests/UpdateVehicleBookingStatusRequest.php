<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleBookingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:confirmed,cancelled,expired,completed'],
            'reason' => ['required_if:status,cancelled', 'nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_status' => ['nullable', 'string', 'in:unpaid,verified,refunded'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required_if' => 'Alasan wajib diisi jika pemesanan unit dibatalkan.',
        ];
    }
}

