<?php

namespace App\Http\Requests;

use App\Models\Branch;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreTestDriveBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'scheduled_time' => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'scheduled_date.after_or_equal' => 'Tanggal test drive tidak boleh berada di masa lalu.',
            'vehicle_id.exists' => 'Kendaraan yang dipilih tidak valid.',
            'branch_id.exists' => 'Cabang dealer yang dipilih tidak valid.',
            'scheduled_time.regex' => 'Format jam test drive harus JJ:MM (contoh 10:00).',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $branch = Branch::find($this->input('branch_id'));
            if (! $branch || ! $branch->is_active) {
                $validator->errors()->add('branch_id', 'Cabang dealer yang dipilih sedang tidak aktif.');
                return;
            }

            $vehicle = Vehicle::find($this->input('vehicle_id'));
            if (! $vehicle || $vehicle->publication_status !== 'published') {
                $validator->errors()->add('vehicle_id', 'Kendaraan yang dipilih tidak tersedia untuk uji kemudi.');
                return;
            }

            try {
                $scheduledAt = Carbon::createFromFormat(
                    'Y-m-d H:i',
                    $this->input('scheduled_date').' '.$this->input('scheduled_time')
                );

                if ($scheduledAt->isPast()) {
                    $validator->errors()->add('scheduled_date', 'Waktu jadwal test drive tidak boleh berada di masa lalu.');
                    return;
                }

                // Periksa ketersediaan slot di cabang backend
                if (! $branch->isSlotAvailable($scheduledAt)) {
                    $validator->errors()->add(
                        'scheduled_time',
                        'Kapasitas slot jadwal test drive pada jam tersebut sudah penuh di cabang ini. Silakan pilih slot jam lain.'
                    );
                }
            } catch (\Exception $e) {
                $validator->errors()->add('scheduled_time', 'Format tanggal dan jam tidak valid.');
            }
        });
    }
}
