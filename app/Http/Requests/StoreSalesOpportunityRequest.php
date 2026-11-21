<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'lead_id' => ['nullable', 'integer', 'exists:leads,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'stage' => ['nullable', 'string', 'in:prospecting,qualification,presentation,test_drive,negotiation,booking,closed_won,closed_lost'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

