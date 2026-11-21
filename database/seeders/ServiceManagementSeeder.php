<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\ServiceSchedule;
use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class ServiceManagementSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Service Types
        $serviceTypes = [
            [
                'name' => 'Servis Berkala Resmi (Periodic Maintenance)',
                'code' => 'periodic_maintenance',
                'category' => 'regular_maintenance',
                'description' => 'Paket perawatan rutin berkala resmi Toyota (10.000 KM - 100.000 KM) untuk menjaga performa mesin, garansi pabrik, dan keandalan berkendara.',
                'estimated_duration_minutes' => 90,
                'estimated_cost' => 850000,
                'is_active' => true,
            ],
            [
                'name' => 'Ganti Oli Mesin & Filter (TMO Synthetic)',
                'code' => 'oil_change',
                'category' => 'quick_service',
                'description' => 'Penggantian pelumas mesin asli Toyota Motor Oil (TMO) Full Synthetic beserta filter oli baru dan pembersihan filter udara.',
                'estimated_duration_minutes' => 45,
                'estimated_cost' => 475000,
                'is_active' => true,
            ],
            [
                'name' => 'Pemeriksaan & Servis Sistem Rem (Brake Care)',
                'code' => 'brake_service',
                'category' => 'repair',
                'description' => 'Pembersihan kampas rem depan/belakang, kuras minyak rem standar DOT-4, dan inspeksi piringan rotor cakram.',
                'estimated_duration_minutes' => 60,
                'estimated_cost' => 350000,
                'is_active' => true,
            ],
            [
                'name' => 'Tune-Up Mesin & Ruang Bakar (Engine Tune-Up)',
                'code' => 'engine_tuneup',
                'category' => 'repair',
                'description' => 'Pembersihan throttle body, busi iridium, scanner diagnosa ECU komputer, dan pembersihan injektor bahan bakar.',
                'estimated_duration_minutes' => 90,
                'estimated_cost' => 600000,
                'is_active' => true,
            ],
            [
                'name' => 'Perawatan & Servis AC Mobil (AC Cleaning)',
                'code' => 'ac_service',
                'category' => 'ac_service',
                'description' => 'Pengurasan dan pengisian kembali freon murni, penggantian filter kabin antibakteri, serta pembersihan evaporator AC.',
                'estimated_duration_minutes' => 60,
                'estimated_cost' => 420000,
                'is_active' => true,
            ],
            [
                'name' => 'Spooring 3D, Balancing & Rotasi Ban',
                'code' => 'wheel_alignment',
                'category' => 'quick_service',
                'description' => 'Penyelarasan sudut roda computerized 3D dan balancing roda untuk kestabilan kemudi serta keausan ban yang merata.',
                'estimated_duration_minutes' => 45,
                'estimated_cost' => 280000,
                'is_active' => true,
            ],
            [
                'name' => 'Perbaikan Umum & Kelistrikan (General Repair)',
                'code' => 'general_repair',
                'category' => 'electrical',
                'description' => 'Penanganan keluhan transmisi, suspensi kaki-kaki, sistem pengisian aki baterai, dan sistem kelistrikan body kendaraan.',
                'estimated_duration_minutes' => 120,
                'estimated_cost' => 500000,
                'is_active' => true,
            ],
            [
                'name' => 'Uji Emisi Gas Buang Resmi Toyota',
                'code' => 'emission_test',
                'category' => 'inspection',
                'description' => 'Pengujian kadar gas buang tersertifikasi resmi terhubung dengan sistem Dinas Lingkungan Hidup.',
                'estimated_duration_minutes' => 30,
                'estimated_cost' => 150000,
                'is_active' => true,
            ],
        ];

        foreach ($serviceTypes as $st) {
            ServiceType::updateOrCreate(['code' => $st['code']], $st);
        }

        // 2. Seed Default Service Schedules for All Active Branches
        $branches = Branch::where('is_active', true)->get();

        foreach ($branches as $branch) {
            // Days: Monday (1) to Saturday (6)
            for ($day = 1; $day <= 6; $day++) {
                $closingTime = ($day === 6) ? '14:00:00' : '16:00:00';
                $dailyCap = ($day === 6) ? 10 : 16;

                ServiceSchedule::updateOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'day_of_week' => $day,
                    ],
                    [
                        'opening_time' => '08:00:00',
                        'closing_time' => $closingTime,
                        'slot_duration_minutes' => 60,
                        'max_slot_capacity' => 2,
                        'max_daily_capacity' => $dailyCap,
                        'is_operational' => true,
                        'notes' => 'Jadwal operasional bengkel resmi cabang ' . $branch->name,
                    ]
                );
            }

            // Sunday (0) closed by default
            ServiceSchedule::updateOrCreate(
                [
                    'branch_id' => $branch->id,
                    'day_of_week' => 0,
                ],
                [
                    'opening_time' => '08:00:00',
                    'closing_time' => '12:00:00',
                    'slot_duration_minutes' => 60,
                    'max_slot_capacity' => 0,
                    'max_daily_capacity' => 0,
                    'is_operational' => false,
                    'notes' => 'Bengkel tutup pada hari Minggu / Hari Libur Nasional.',
                ]
            );
        }
    }
}

