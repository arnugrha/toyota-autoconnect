<?php

namespace Tests\Feature;

use App\Models\SparePart;
use App\Models\SparePartProcurement;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\SparePartSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SparePartProcurementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(SparePartSeeder::class);
    }

    public function test_creating_procurement_automatically_increments_spare_part_stock(): void
    {
        $admin = User::where('email', 'superadmin@toyota-autoconnect.id')->firstOrFail();

        $oil = SparePart::where('part_number', '08880-83389')->firstOrFail();
        $battery = SparePart::where('part_number', '28800-YZZAA')->firstOrFail();

        $oilStockBefore = $oil->stock_quantity;
        $batteryStockBefore = $battery->stock_quantity;

        $this->actingAs($admin)
            ->get(route('admin.spare-part-procurements.create'))
            ->assertOk()
            ->assertSee('Buat Pengadaan Sparepart Baru');

        $response = $this->actingAs($admin)
            ->post(route('admin.spare-part-procurements.store'), [
                'procurement_date' => now()->toDateString(),
                'supplier_name' => 'PT Toyota Astra Motor Parts Center',
                'notes' => 'Restock rutin oli dan aki',
                'items' => [
                    [
                        'spare_part_id' => $oil->id,
                        'quantity' => 10,
                        'unit_cost' => 355000,
                    ],
                    [
                        'spare_part_id' => $battery->id,
                        'quantity' => 5,
                        'unit_cost' => 910000,
                    ],
                ],
            ]);

        $procurement = SparePartProcurement::latest('id')->firstOrFail();
        $response->assertRedirect(route('admin.spare-part-procurements.show', $procurement));

        // Verify stock incremented automatically
        $this->assertEquals($oilStockBefore + 10, $oil->fresh()->stock_quantity);
        $this->assertEquals($batteryStockBefore + 5, $battery->fresh()->stock_quantity);

        // Verify total cost calculation: (10 * 355000) + (5 * 910000) = 3,550,000 + 4,550,000 = 8,100,000
        $this->assertEquals(8100000.00, (float) $procurement->total_cost);

        // Verify stock movement ledger entries
        $this->assertDatabaseHas('spare_part_stock_movements', [
            'spare_part_id' => $oil->id,
            'type' => 'in',
            'quantity' => 10,
            'stock_before' => $oilStockBefore,
            'stock_after' => $oilStockBefore + 10,
            'reference_type' => 'procurement',
            'reference_id' => $procurement->id,
        ]);

        $this->assertDatabaseHas('spare_part_stock_movements', [
            'spare_part_id' => $battery->id,
            'type' => 'in',
            'quantity' => 5,
            'stock_before' => $batteryStockBefore,
            'stock_after' => $batteryStockBefore + 5,
            'reference_type' => 'procurement',
            'reference_id' => $procurement->id,
        ]);

        // Verify index & detail pages render 200 OK
        $this->actingAs($admin)
            ->get(route('admin.spare-part-procurements.index'))
            ->assertOk()
            ->assertSee($procurement->procurement_number);

        $this->actingAs($admin)
            ->get(route('admin.spare-part-procurements.show', $procurement))
            ->assertOk()
            ->assertSee('PT Toyota Astra Motor Parts Center');
    }
}
