<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeasingApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('financing.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in(['draft', 'submitted', 'processing', 'approved', 'rejected', 'need_revision', 'cancelled']),
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
            'revision_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

