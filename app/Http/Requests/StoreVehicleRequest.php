<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('vehicles.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('vehicles', 'slug')],
            'category_id' => ['nullable', 'integer', Rule::exists('vehicle_categories', 'id')],
            'category' => ['nullable', 'string', 'max:50'],
            'variant' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0'],
            'transmission' => ['nullable', 'string', 'max:50'],
            'fuel_type' => ['nullable', 'string', 'max:50'],
            'engine_capacity' => ['nullable', 'string', 'max:50'],
            'passenger_capacity' => ['nullable', 'integer', 'min:1', 'max:50'],
            'dimensions' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'string', Rule::in(['available', 'incoming', 'reserved', 'sold'])],
            'publication_status' => ['required', 'string', Rule::in(['draft', 'published', 'archived'])],
            'is_featured' => ['boolean'],
            'primary_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], // Max 5MB
            'additional_images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kendaraan wajib diisi.',
            'slug.unique' => 'Slug kendaraan sudah digunakan oleh model lain.',
            'price.required' => 'Harga kendaraan wajib diisi.',
            'price.min' => 'Harga kendaraan tidak boleh bernilai negatif.',
            'primary_image.image' => 'File foto utama harus berupa gambar yang valid (JPG, PNG, atau WebP).',
            'primary_image.max' => 'Ukuran foto utama tidak boleh melebihi 5 MB.',
        ];
    }
}
