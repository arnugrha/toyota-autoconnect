<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'stage' => ['nullable', 'string', 'in:prospecting,qualification,presentation,test_drive,negotiation,booking,closed_won,closed_lost'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'lost_reason' => ['required_if:stage,closed_lost', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'lost_reason.required_if' => 'Alasan wajib diisi jika peluang penjualan ditandai Closed Lost.',
        ];
    }
}

