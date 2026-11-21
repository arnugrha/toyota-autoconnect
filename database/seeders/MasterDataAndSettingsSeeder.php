<?php

namespace Database\Seeders;

use App\Models\LeadSource;
use App\Models\PaymentMethod;
use App\Models\ServiceType;
use App\Services\SettingService;
use Illuminate\Database\Seeder;

class MasterDataAndSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed System Settings
        app(SettingService::class)->seedDefaults();

        // 2. Seed Lead Sources
        $leadSources = [
            [
                'name' => 'Website Resmi AutoConnect',
                'code' => 'website',
                'description' => 'Inquiry formulir digital dan simulasi kredit dari portal publik',
                'is_active' => true,
            ],
            [
                'name' => 'Kunjungan Showroom (Walk-in)',
                'code' => 'dealer',
                'description' => 'Pelanggan yang datang langsung berkonsultasi ke outlet dealer cabang',
                'is_active' => true,
            ],
            [
                'name' => 'Referral Pelanggan Setia',
                'code' => 'referral',
                'description' => 'Rekomendasi dari relasi atau pemilik kendaraan Toyota terdahulu',
                'is_active' => true,
            ],
            [
                'name' => 'Kampanye Digital & Iklan',
                'code' => 'campaign',
                'description' => 'Iklan promosi media sosial, Google Ads, dan kampanye digital',
                'is_active' => true,
            ],
            [
                'name' => 'Pameran Otomotif & Event',
                'code' => 'event',
                'description' => 'GIIAS, IIMS, customer gathering, dan booth pameran pusat perbelanjaan',
                'is_active' => true,
            ],
            [
                'name' => 'Hotline Telepon & WhatsApp',
                'code' => 'phone',
                'description' => 'Kontak langsung melalui nomor customer care dealer',
                'is_active' => true,
            ],
            [
                'name' => 'Sumber Lainnya',
                'code' => 'other',
                'description' => 'Kanal prospek di luar kategori terdaftar',
                'is_active' => true,
            ],
        ];

        foreach ($leadSources as $source) {
            LeadSource::firstOrCreate(
                ['code' => $source['code']],
                $source
            );
        }

        // 3. Seed Payment Methods
        $paymentMethods = [
            [
                'name' => 'BCA Virtual Account',
                'code' => 'bca_va',
                'category' => 'virtual_account',
                'instructions' => 'Transfer instan 24 jam melalui m-BCA, KlikBCA, atau ATM BCA dengan verifikasi otomatis.',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Mandiri Bill Payment',
                'code' => 'mandiri_bill',
                'category' => 'virtual_account',
                'instructions' => 'Pembayaran tagihan via Livin by Mandiri atau ATM Mandiri dengan kode perusahaan resmi.',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'BNI Virtual Account',
                'code' => 'bni_va',
                'category' => 'virtual_account',
                'instructions' => 'Pembayaran otomatis via BNI Mobile Banking, Internet Banking, atau ATM BNI.',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'BRI Virtual Account (BRIVA)',
                'code' => 'bri_va',
                'category' => 'virtual_account',
                'instructions' => 'Pembayaran tagihan instan melalui aplikasi BRImo atau jaringan ATM BRI.',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Permata Virtual Account',
                'code' => 'permata_va',
                'category' => 'virtual_account',
                'instructions' => 'Pembayaran melalui PermataMobile X atau jaringan ATM Bersama / Prima / Alto.',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'GoPay / QRIS Nasional',
                'code' => 'gopay',
                'category' => 'e_wallet',
                'instructions' => 'Pindai kode QRIS melalui GoPay, BCA, Livin, OVO, Dana, atau aplikasi perbankan apa saja.',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Kartu Kredit / Debit Online',
                'code' => 'credit_card',
                'category' => 'card',
                'instructions' => 'Pembayaran aman 3D-Secure menggunakan kartu Visa, MasterCard, atau JCB.',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Pelunasan Tunai di Dealer',
                'code' => 'cash_dealer',
                'category' => 'offline',
                'instructions' => 'Pelunasan langsung di meja kasir resmi showroom cabang Toyota AutoConnect.',
                'is_active' => true,
                'sort_order' => 8,
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::firstOrCreate(
                ['code' => $method['code']],
                $method
            );
        }

        // 4. Seed Service Types if none exist
        if (ServiceType::count() === 0) {
            $serviceTypes = [
                [
                    'name' => 'Servis Berkala 1.000 KM (Gratis Inspeksi Awal)',
                    'code' => 'SRV-01K',
                    'category' => 'quick_service',
                    'description' => 'Pemeriksaan menyeluruh 1.000 KM pertama: baut roda, fluida pendingin, kelistrikan, dan fungsi kemudi.',
                    'estimated_duration_minutes' => 60,
                    'estimated_cost' => 0,
                    'is_active' => true,
                ],
                [
                    'name' => 'Servis Berkala 10.000 KM',
                    'code' => 'SRV-10K',
                    'category' => 'regular_maintenance',
                    'description' => 'Penggantian oli mesin TMO, filter oli, pemeriksaan sistem rem, rotasi ban, dan inspeksi 25 titik keselamatan.',
                    'estimated_duration_minutes' => 90,
                    'estimated_cost' => 850000,
                    'is_active' => true,
                ],
                [
                    'name' => 'Servis Berkala 20.000 KM',
                    'code' => 'SRV-20K',
                    'category' => 'regular_maintenance',
                    'description' => 'Penggantian oli mesin, filter udara, kuras minyak rem, rotasi roda, balancing, dan tune-up komputerisasi.',
                    'estimated_duration_minutes' => 120,
                    'estimated_cost' => 1450000,
                    'is_active' => true,
                ],
                [
                    'name' => 'Perawatan AC Komprehensif',
                    'code' => 'SRV-AC',
                    'category' => 'ac_service',
                    'description' => 'Pembersihan evaporator, penggantian filter kabin cabin-air, pengisian refrigeran freon R134a, dan sanitasi fogging.',
                    'estimated_duration_minutes' => 90,
                    'estimated_cost' => 950000,
                    'is_active' => true,
                ],
                [
                    'name' => 'Inspeksi & Diagnosa Sistem Hybrid / EV',
                    'code' => 'SRV-HEV',
                    'category' => 'electrical',
                    'description' => 'Diagnosa komputerisasi Toyota Intelligent Tester: baterai traksi tegangan tinggi, inverter, generator, dan cooling fan.',
                    'estimated_duration_minutes' => 90,
                    'estimated_cost' => 1200000,
                    'is_active' => true,
                ],
                [
                    'name' => 'Uji Emisi Gas Buang Resmi & Sertifikat',
                    'code' => 'SRV-EMISI',
                    'category' => 'inspection',
                    'description' => 'Pengujian gas buang standar Dinas Lingkungan Hidup DKI Jakarta terintegrasi database e-Uji Emisi.',
                    'estimated_duration_minutes' => 45,
                    'estimated_cost' => 250000,
                    'is_active' => true,
                ],
            ];

            foreach ($serviceTypes as $st) {
                ServiceType::create($st);
            }
        }
    }
}

