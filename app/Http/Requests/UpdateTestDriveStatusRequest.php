<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTestDriveStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:confirmed,rejected,completed,cancelled,no_show'],
            'staff_note' => ['nullable', 'string', 'max:1000'],
            'reason' => ['required_if:status,rejected,cancelled', 'nullable', 'string', 'max:1000'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Status perubahan test drive tidak valid.',
            'reason.required_if' => 'Alasan wajib diisi jika jadwal ditolak atau dibatalkan.',
        ];
    }
}

