<?php

namespace Tests\Feature;

use App\Models\SparePart;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SparePartManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_super_admin_can_view_and_manage_spare_parts(): void
    {
        $admin = User::where('email', 'superadmin@toyota-autoconnect.id')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.spare-parts.index'))
            ->assertOk()
            ->assertSee('Manajemen Master & Stok Sparepart');

        $this->actingAs($admin)
            ->post(route('admin.spare-parts.store'), [
                'part_number' => '08880-TEST01',
                'name' => 'Toyota Test Oil 5W-30',
                'category' => 'oil_fluids',
                'unit' => 'Galon',
                'purchase_price' => 300000,
                'selling_price' => 420000,
                'stock_quantity' => 15,
                'min_stock' => 5,
                'description' => 'Testing part',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.spare-parts.index'));

        $part = SparePart::where('part_number', '08880-TEST01')->firstOrFail();
        $this->assertEquals(15, $part->stock_quantity);

        $this->assertDatabaseHas('spare_part_stock_movements', [
            'spare_part_id' => $part->id,
            'type' => 'in',
            'quantity' => 15,
            'stock_before' => 0,
            'stock_after' => 15,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.spare-parts.update', $part), [
                'part_number' => '08880-TEST01',
                'name' => 'Toyota Test Oil Updated',
                'category' => 'oil_fluids',
                'unit' => 'Galon',
                'purchase_price' => 310000,
                'selling_price' => 450000,
                'min_stock' => 6,
                'description' => 'Updated desc',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.spare-parts.index'));

        $this->assertEquals('Toyota Test Oil Updated', $part->fresh()->name);

        $this->actingAs($admin)
            ->get(route('admin.spare-parts.movements', $part))
            ->assertOk()
            ->assertSee('08880-TEST01');

        $this->actingAs($admin)
            ->post(route('admin.spare-parts.toggle-status', $part))
            ->assertRedirect();

        $this->assertFalse($part->fresh()->is_active);
    }

    public function test_customer_cannot_access_spare_parts_management(): void
    {
        $customer = User::where('email', 'customer@toyota-autoconnect.id')->firstOrFail();

        $this->actingAs($customer)
            ->get(route('admin.spare-parts.index'))
            ->assertForbidden();
    }
}
