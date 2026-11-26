<?php

namespace Database\Seeders;

use App\Models\SparePart;
use App\Models\SparePartStockMovement;
use Illuminate\Database\Seeder;

class SparePartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parts = [
            [
                'part_number' => '08880-83389',
                'name' => 'Toyota Motor Oil (TMO) Full Synthetic 5W-30 4L',
                'category' => 'oil_fluids',
                'unit' => 'Galon',
                'purchase_price' => 360000,
                'selling_price' => 450000,
                'stock_quantity' => 35,
                'min_stock' => 10,
                'description' => 'Oli mesin full synthetic resmi Toyota untuk perlindungan optimal mesin bensin modern.',
            ],
            [
                'part_number' => '08880-83885',
                'name' => 'Toyota Motor Oil (TMO) 0W-20 Hybrid & LCGC 4L',
                'category' => 'oil_fluids',
                'unit' => 'Galon',
                'purchase_price' => 410000,
                'selling_price' => 520000,
                'stock_quantity' => 24,
                'min_stock' => 8,
                'description' => 'Oli viskositas rendah khusus lini Hybrid (Innova Zenix HEV, Yaris Cross HEV) dan Agya/Calya.',
            ],
            [
                'part_number' => '90915-YZZN2',
                'name' => 'Oil Filter Toyota Genuine Parts (Avanza/Veloz/Rush/Yaris)',
                'category' => 'filters',
                'unit' => 'Pcs',
                'purchase_price' => 28000,
                'selling_price' => 45000,
                'stock_quantity' => 60,
                'min_stock' => 15,
                'description' => 'Saringan oli resmi Toyota untuk menjaga kebersihan sirkulasi pelumas mesin.',
            ],
            [
                'part_number' => '17801-0Y040',
                'name' => 'Air Filter / Saringan Udara Mesin Toyota',
                'category' => 'filters',
                'unit' => 'Pcs',
                'purchase_price' => 115000,
                'selling_price' => 165000,
                'stock_quantity' => 28,
                'min_stock' => 10,
                'description' => 'Elemen penyaring udara pembakaran mesin standar pabrik Toyota.',
            ],
            [
                'part_number' => '87139-YZZ30',
                'name' => 'Cabin AC Air Filter Anti-Bacterial Toyota',
                'category' => 'ac_cooling',
                'unit' => 'Pcs',
                'purchase_price' => 120000,
                'selling_price' => 175000,
                'stock_quantity' => 18,
                'min_stock' => 8,
                'description' => 'Filter kabin AC penyaring debu mikro dan bau tak sedap.',
            ],
            [
                'part_number' => '04465-BZ170',
                'name' => 'Brake Pad Front / Kampas Rem Depan Set',
                'category' => 'brakes',
                'unit' => 'Set',
                'purchase_price' => 380000,
                'selling_price' => 515000,
                'stock_quantity' => 14,
                'min_stock' => 5,
                'description' => 'Kampas rem depan asli Toyota bebas asbes dengan daya cengkeram presisi.',
            ],
            [
                'part_number' => '04466-0K040',
                'name' => 'Brake Pad Rear / Kampas Rem Belakang Set (Fortuner/Innova)',
                'category' => 'brakes',
                'unit' => 'Set',
                'purchase_price' => 420000,
                'selling_price' => 575000,
                'stock_quantity' => 4,
                'min_stock' => 5,
                'description' => 'Kampas rem belakang khusus Kijang Innova dan Fortuner (stok menipis untuk uji pengadaan).',
            ],
            [
                'part_number' => '90919-01275',
                'name' => 'Spark Plug Iridium / Busi Iridium Toyota',
                'category' => 'engine',
                'unit' => 'Pcs',
                'purchase_price' => 95000,
                'selling_price' => 135000,
                'stock_quantity' => 40,
                'min_stock' => 12,
                'description' => 'Busi Iridium tahan lama untuk pembakaran mesin efisien hingga 100.000 KM.',
            ],
            [
                'part_number' => '28800-YZZAA',
                'name' => 'Toyota Genuine Battery / Aki MF 46B24R',
                'category' => 'electrical',
                'unit' => 'Unit',
                'purchase_price' => 920000,
                'selling_price' => 1250000,
                'stock_quantity' => 3,
                'min_stock' => 5,
                'description' => 'Aki kering Maintenance Free bergaransi resmi bengkel Toyota.',
            ],
            [
                'part_number' => '85212-YZZ14',
                'name' => 'Wiper Blade Set Depan (Kanan & Kiri)',
                'category' => 'body_accessories',
                'unit' => 'Set',
                'purchase_price' => 145000,
                'selling_price' => 210000,
                'stock_quantity' => 22,
                'min_stock' => 6,
                'description' => 'Karet wiper aerodinamis sapuan bersih dan senyap saat hujan lebat.',
            ],
            [
                'part_number' => '08889-80072',
                'name' => 'Toyota Super Long Life Coolant (SLLC) 4L',
                'category' => 'ac_cooling',
                'unit' => 'Galon',
                'purchase_price' => 140000,
                'selling_price' => 195000,
                'stock_quantity' => 20,
                'min_stock' => 6,
                'description' => 'Cairan pendingin radiator siap pakai anti-korosi warna pink resmi Toyota.',
            ],
            [
                'part_number' => '08823-80005',
                'name' => 'Toyota Genuine Brake Fluid DOT 3 (500ml)',
                'category' => 'oil_fluids',
                'unit' => 'Botol',
                'purchase_price' => 48000,
                'selling_price' => 75000,
                'stock_quantity' => 30,
                'min_stock' => 10,
                'description' => 'Minyak rem resmi Toyota berketahanan titik didih tinggi.',
            ],
        ];

        foreach ($parts as $data) {
            $part = SparePart::firstOrCreate(
                ['part_number' => $data['part_number']],
                $data
            );

            if ($part->wasRecentlyCreated && $part->stock_quantity > 0) {
                SparePartStockMovement::create([
                    'spare_part_id' => $part->id,
                    'type' => 'in',
                    'quantity' => $part->stock_quantity,
                    'stock_before' => 0,
                    'stock_after' => $part->stock_quantity,
                    'reference_type' => 'manual',
                    'notes' => 'Stok awal katalog Toyota Genuine Parts.',
                ]);
            }
        }
    }
}
