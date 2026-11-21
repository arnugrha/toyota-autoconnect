<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\InventoryMovement;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleInventory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BranchAndCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Dealer Branches
        $branches = [
            [
                'name' => 'AutoConnect Sudirman Pusat',
                'code' => 'AT-JKT-01',
                'address' => 'Jl. Jend. Sudirman Kav. 52-53, Senayan',
                'city' => 'Jakarta Pusat',
                'phone' => '021-57901234',
                'email' => 'sudirman@toyota-autoconnect.id',
                'is_active' => true,
            ],
            [
                'name' => 'AutoConnect TB Simatupang',
                'code' => 'AT-JKT-02',
                'address' => 'Jl. TB Simatupang No. 18, Cilandak',
                'city' => 'Jakarta Selatan',
                'phone' => '021-75908888',
                'email' => 'simatupang@toyota-autoconnect.id',
                'is_active' => true,
            ],
            [
                'name' => 'AutoConnect Basuki Rahmat',
                'code' => 'AT-SBY-01',
                'address' => 'Jl. Basuki Rahmat No. 102, Tegalsari',
                'city' => 'Surabaya',
                'phone' => '031-5341234',
                'email' => 'surabaya@toyota-autoconnect.id',
                'is_active' => true,
            ],
            [
                'name' => 'AutoConnect Dago Heritage',
                'code' => 'AT-BDG-01',
                'address' => 'Jl. Ir. H. Juanda (Dago) No. 78',
                'city' => 'Bandung',
                'phone' => '022-2505678',
                'email' => 'bandung@toyota-autoconnect.id',
                'is_active' => true,
            ],
        ];

        $branchModels = [];
        foreach ($branches as $branchData) {
            $branchModels[] = Branch::firstOrCreate(
                ['code' => $branchData['code']],
                $branchData
            );
        }

        // 2. Seed Vehicle Categories
        $categories = [
            ['name' => 'MPV', 'description' => 'Multi-Purpose Vehicle untuk kenyamanan keluarga Indonesia.'],
            ['name' => 'SUV', 'description' => 'Sport Utility Vehicle tangguh untuk segala medan dan mobilitas modern.'],
            ['name' => 'Hatchback', 'description' => 'Mobil kompak lincah, stylish, dan efisien untuk kebutuhan perkotaan.'],
            ['name' => 'Sedan', 'description' => 'Kemewahan prestisius dengan kenyamanan berkendara kelas atas.'],
            ['name' => 'Commercial', 'description' => 'Kendaraan niaga tangguh dan andal untuk menunjang aktivitas bisnis.'],
            ['name' => 'Hybrid EV', 'description' => 'Kendaraan ramah lingkungan berteknologi Toyota Hybrid System terdepan.'],
            ['name' => 'GR Sport', 'description' => 'Lini kendaraan berperforma tinggi dengan sentuhan DNA Gazoo Racing.'],
        ];

        foreach ($categories as $cat) {
            $slug = Str::slug($cat['name']);
            $catModel = VehicleCategory::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                    'is_active' => true,
                ]
            );

            // Connect existing vehicles with matching category string to new category_id
            Vehicle::where('category', $cat['name'])->whereNull('category_id')->update([
                'category_id' => $catModel->id,
            ]);
        }

        // 3. Initialize stock for existing vehicles in all branches
        $vehicles = Vehicle::all();
        foreach ($vehicles as $vehicle) {
            // Ensure publication_status is published for existing published vehicles
            if (empty($vehicle->publication_status)) {
                $vehicle->update(['publication_status' => 'published']);
            }

            foreach ($branchModels as $index => $branch) {
                // Berikan kuantitas stok variatif untuk simulasi realistis
                $qty = match ($index) {
                    0 => 5, // Sudirman Pusat
                    1 => 3, // TB Simatupang
                    2 => 2, // Surabaya
                    default => 4, // Bandung
                };

                $inv = VehicleInventory::firstOrCreate(
                    [
                        'vehicle_id' => $vehicle->id,
                        'branch_id' => $branch->id,
                    ],
                    [
                        'stock_quantity' => $qty,
                        'reserved_quantity' => 0,
                    ]
                );

                // Catat movement awal jika belum ada
                if ($inv->wasRecentlyCreated) {
                    InventoryMovement::create([
                        'vehicle_inventory_id' => $inv->id,
                        'user_id' => null,
                        'movement_type' => 'initial_stock',
                        'quantity' => $qty,
                        'stock_before' => 0,
                        'stock_after' => $qty,
                        'reason' => 'Inisialisasi stok awal sistem dealer.',
                        'created_at' => now(),
                    ]);
                }
            }
        }
    }
}
