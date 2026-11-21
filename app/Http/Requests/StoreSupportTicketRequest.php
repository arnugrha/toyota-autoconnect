<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['nullable', 'exists:branches,id'],
            'category' => ['required', 'string', 'in:vehicle_info,service_booking,service_complaint,payment,warranty,general_inquiry,other'],
            'priority' => ['nullable', 'string', 'in:low,medium,high,urgent'],
            'subject' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string', 'max:3000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'Pilih kategori tiket pertanyaan atau keluhan.',
            'category.in' => 'Kategori yang dipilih tidak valid.',
            'subject.required' => 'Judul / subjek tiket wajib diisi.',
            'description.required' => 'Uraikan pertanyaan atau keluhan Anda secara detail.',
            'attachment.max' => 'Ukuran lampiran maksimal 5 MB.',
            'attachment.mimes' => 'Format lampiran harus berupa JPG, PNG, atau PDF.',
        ];
    }
}

