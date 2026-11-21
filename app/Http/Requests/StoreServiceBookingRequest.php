<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'exists:branches,id'],
            'service_type_id' => ['required', 'exists:service_types,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'vehicle_name' => ['required', 'string', 'max:100'],
            'license_plate' => ['required', 'string', 'max:20'],
            'current_mileage' => ['nullable', 'integer', 'min:0', 'max:2000000'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'booking_time' => ['required', 'string'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'branch_id.required' => 'Pilih cabang bengkel resmi Toyota tempat Anda ingin melakukan servis.',
            'service_type_id.required' => 'Pilih jenis paket layanan servis yang diinginkan.',
            'vehicle_name.required' => 'Nama model kendaraan wajib diisi.',
            'license_plate.required' => 'Nomor polisi kendaraan wajib diisi.',
            'booking_date.required' => 'Pilih tanggal jadwal servis kendaraan.',
            'booking_date.after_or_equal' => 'Tanggal servis tidak boleh berada di masa lalu.',
            'booking_time.required' => 'Pilih jam kedatangan servis.',
        ];
    }
}

