<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('inventory.manage');
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'integer', Rule::exists('vehicles', 'id')],
            'branch_id' => ['required', 'integer', Rule::exists('branches', 'id')],
            'type' => ['required', 'string', Rule::in(['add', 'subtract'])],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_id.required' => 'Model kendaraan wajib dipilih.',
            'branch_id.required' => 'Cabang dealer wajib dipilih.',
            'type.required' => 'Jenis penyesuaian stok harus ditentukan.',
            'quantity.required' => 'Jumlah kuantitas perubahan stok wajib diisi.',
            'quantity.min' => 'Jumlah kuantitas minimal 1 unit.',
            'reason.required' => 'Alasan penyesuaian stok wajib dijelaskan untuk keperluan audit log.',
        ];
    }
}
