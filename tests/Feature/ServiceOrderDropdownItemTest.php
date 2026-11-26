<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\ServiceOrder;
use App\Models\ServiceType;
use App\Models\SparePart;
use App\Models\User;
use Database\Seeders\BranchAndCategorySeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\ServiceManagementSeeder;
use Database\Seeders\SparePartSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceOrderDropdownItemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            RoleAndPermissionSeeder::class,
            BranchAndCategorySeeder::class,
            ServiceManagementSeeder::class,
            SparePartSeeder::class,
        ]);
    }

    public function test_admin_adds_spare_part_and_labor_items_strictly_via_dropdown_ids(): void
    {
        $admin = User::where('email', 'superadmin@toyota-autoconnect.id')->firstOrFail();
        $customer = User::where('email', 'customer@toyota-autoconnect.id')->firstOrFail();
        $branch = Branch::firstOrFail();
        $serviceType = ServiceType::firstOrFail();
        $oil = SparePart::where('part_number', '08880-83389')->firstOrFail();

        $order = ServiceOrder::create([
            'order_number' => ServiceOrder::generateNumber(),
            'user_id' => $customer->id,
            'branch_id' => $branch->id,
            'vehicle_name' => 'All New Avanza 1.5 G CVT',
            'license_plate' => 'B 1234 TYT',
            'current_mileage' => 20000,
            'service_type_id' => $serviceType->id,
            'status' => 'in_progress',
            'estimated_cost' => $serviceType->estimated_cost,
        ]);

        // Show page renders dropdown options from master spare_parts and service_types
        $this->actingAs($admin)
            ->get(route('admin.service-orders.show', $order))
            ->assertOk()
            ->assertSee($oil->part_number)
            ->assertSee($oil->name)
            ->assertSee($serviceType->code);

        // Rejects manual text submission without spare_part_id
        $this->actingAs($admin)
            ->post(route('admin.service-orders.items.store', $order), [
                'item_type' => 'part',
                'quantity' => 1,
            ])
            ->assertSessionHasErrors('spare_part_id');

        // Adds spare part via dropdown (default pending customer approval => stock not deducted yet)
        $stockBefore = $oil->stock_quantity;
        $this->actingAs($admin)
            ->post(route('admin.service-orders.items.store', $order), [
                'item_type' => 'part',
                'spare_part_id' => $oil->id,
                'quantity' => 2,
                'requires_approval' => '1',
            ])
            ->assertRedirect(route('admin.service-orders.show', $order));

        $partItem = $order->items()->where('item_type', 'part')->firstOrFail();
        $this->assertEquals($oil->id, $partItem->spare_part_id);
        $this->assertEquals($oil->name, $partItem->name);
        $this->assertEquals((float) $oil->selling_price, (float) $partItem->unit_price);
        $this->assertEquals('pending_approval', $partItem->approval_status);
        $this->assertFalse($partItem->stock_deducted);
        $this->assertEquals($stockBefore, $oil->fresh()->stock_quantity);

        // Adds labor item via dropdown from service_types
        $this->actingAs($admin)
            ->post(route('admin.service-orders.items.store', $order), [
                'item_type' => 'labor',
                'service_type_id' => $serviceType->id,
                'quantity' => 1,
                'unit_price' => 350000,
            ])
            ->assertRedirect(route('admin.service-orders.show', $order));

        $order->refresh();
        $this->assertEquals(350000.0, (float) $order->labor_cost);
        $this->assertEquals(0.0, (float) $order->parts_cost); // pending part not billed yet
        $this->assertEquals(350000.0, (float) $order->final_cost);
    }

    public function test_admin_cannot_add_spare_part_exceeding_available_stock(): void
    {
        $admin = User::where('email', 'superadmin@toyota-autoconnect.id')->firstOrFail();
        $customer = User::where('email', 'customer@toyota-autoconnect.id')->firstOrFail();
        $branch = Branch::firstOrFail();
        $serviceType = ServiceType::firstOrFail();
        $battery = SparePart::where('part_number', '28800-YZZAA')->firstOrFail(); // stock = 3

        $order = ServiceOrder::create([
            'order_number' => ServiceOrder::generateNumber(),
            'user_id' => $customer->id,
            'branch_id' => $branch->id,
            'vehicle_name' => 'Innova Zenix',
            'license_plate' => 'B 9999 ZNX',
            'service_type_id' => $serviceType->id,
            'status' => 'inspection',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.service-orders.items.store', $order), [
                'item_type' => 'part',
                'spare_part_id' => $battery->id,
                'quantity' => 10,
                'requires_approval' => '1',
            ])
            ->assertSessionHas('error');

        $this->assertEquals(0, $order->items()->count());
    }
}
