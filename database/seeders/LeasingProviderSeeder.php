<?php

namespace Database\Seeders;

use App\Models\LeasingProvider;
use Illuminate\Database\Seeder;

class LeasingProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            [
                'name' => 'Toyota Astra Financial Services (TAF)',
                'code' => 'taf',
                'description' => 'Lembaga pembiayaan resmi Toyota Astra Motor dengan promo paket bunga spesial dan proses persetujuan cepat.',
                'base_interest_rate' => 5.75,
                'min_down_payment_percentage' => 20.00,
                'max_tenor_months' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Astra Credit Companies (ACC)',
                'code' => 'acc',
                'description' => 'Perusahaan pembiayaan otomotif terpercaya grup Astra dengan jaringan cabang luas di seluruh Indonesia.',
                'base_interest_rate' => 5.99,
                'min_down_payment_percentage' => 20.00,
                'max_tenor_months' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'BCA Finance',
                'code' => 'bca_finance',
                'description' => 'Solusi pembiayaan kendaraan bermotor dengan suku bunga kompetitif dan opsi fix & cap hingga 6 tahun.',
                'base_interest_rate' => 6.25,
                'min_down_payment_percentage' => 25.00,
                'max_tenor_months' => 72,
                'is_active' => true,
            ],
            [
                'name' => 'Mandiri Utama Finance (MUF)',
                'code' => 'muf',
                'description' => 'Anak perusahaan Bank Mandiri yang menyediakan pembiayaan mobil Toyota baru dengan skema konvensional dan syariah.',
                'base_interest_rate' => 6.50,
                'min_down_payment_percentage' => 20.00,
                'max_tenor_months' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'AutoConnect Mock Leasing (Dev & Testing)',
                'code' => 'mock_leasing',
                'description' => 'Adapter leasing simulasi untuk keperluan development lokal dan pengujian integrasi otomatis.',
                'base_interest_rate' => 5.50,
                'min_down_payment_percentage' => 15.00,
                'max_tenor_months' => 60,
                'is_active' => true,
            ],
        ];

        foreach ($providers as $provider) {
            LeasingProvider::updateOrCreate(
                ['code' => $provider['code']],
                $provider
            );
        }
    }
}

