<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\ServiceBooking;
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

class CustomerSparePartApprovalTest extends TestCase
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

    public function test_customer_sees_confirmation_card_and_can_approve_and_reject_additional_spare_parts(): void
    {
        $admin = User::where('email', 'superadmin@toyota-autoconnect.id')->firstOrFail();
        $customer = User::where('email', 'customer@toyota-autoconnect.id')->firstOrFail();
        $branch = Branch::firstOrFail();
        $serviceType = ServiceType::firstOrFail();

        $oil = SparePart::where('part_number', '08880-83389')->firstOrFail();
        $wiper = SparePart::where('part_number', '85212-YZZ14')->firstOrFail();

        $oilStockBefore = $oil->stock_quantity;
        $wiperStockBefore = $wiper->stock_quantity;

        $booking = ServiceBooking::create([
            'booking_code' => ServiceBooking::generateCode(),
            'user_id' => $customer->id,
            'branch_id' => $branch->id,
            'vehicle_name' => 'All New Veloz Q CVT',
            'license_plate' => 'B 4567 VLZ',
            'current_mileage' => 30000,
            'service_type_id' => $serviceType->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '09:00',
            'status' => 'confirmed',
        ]);

        $order = ServiceOrder::create([
            'order_number' => ServiceOrder::generateNumber(),
            'service_booking_id' => $booking->id,
            'user_id' => $customer->id,
            'branch_id' => $branch->id,
            'vehicle_name' => $booking->vehicle_name,
            'license_plate' => $booking->license_plate,
            'current_mileage' => $booking->current_mileage,
            'service_type_id' => $serviceType->id,
            'status' => 'inspection',
            'inspection_result' => 'Oli mesin perlu diganti dan karet wiper mulai getas.',
        ]);

        // Admin adds 2 spare parts requiring customer approval
        $this->actingAs($admin)->post(route('admin.service-orders.items.store', $order), [
            'item_type' => 'part',
            'spare_part_id' => $oil->id,
            'quantity' => 1,
            'requires_approval' => '1',
        ]);

        $this->actingAs($admin)->post(route('admin.service-orders.items.store', $order), [
            'item_type' => 'part',
            'spare_part_id' => $wiper->id,
            'quantity' => 1,
            'requires_approval' => '1',
        ]);

        $oilItem = $order->items()->where('spare_part_id', $oil->id)->firstOrFail();
        $wiperItem = $order->items()->where('spare_part_id', $wiper->id)->firstOrFail();

        // Customer sees confirmation card on both booking detail and service history detail
        $this->actingAs($customer)
            ->get(route('customer.service-bookings.show', $booking))
            ->assertOk()
            ->assertSee('Konfirmasi Tambahan Suku Cadang Diperlukan')
            ->assertSee($oil->name)
            ->assertSee($wiper->name);

        // Customer approves oil => stock deducted & billed
        $this->actingAs($customer)
            ->post(route('customer.service-orders.items.approve', [$order, $oilItem]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals('approved', $oilItem->fresh()->approval_status);
        $this->assertTrue($oilItem->fresh()->stock_deducted);
        $this->assertEquals($oilStockBefore - 1, $oil->fresh()->stock_quantity);
        $this->assertDatabaseHas('spare_part_stock_movements', [
            'spare_part_id' => $oil->id,
            'type' => 'out',
            'quantity' => -1,
            'reference_type' => 'service_order',
            'reference_id' => $order->id,
        ]);

        // Customer rejects wiper => stock untouched & not billed
        $this->actingAs($customer)
            ->post(route('customer.service-orders.items.reject', [$order, $wiperItem]), [
                'customer_response_note' => 'Wiper masih cukup layak, ganti nanti saja.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals('rejected', $wiperItem->fresh()->approval_status);
        $this->assertFalse($wiperItem->fresh()->stock_deducted);
        $this->assertEquals($wiperStockBefore, $wiper->fresh()->stock_quantity);

        // Final cost only includes approved oil, NOT rejected wiper
        $order->refresh();
        $this->assertEquals((float) $oil->selling_price, (float) $order->parts_cost);
        $this->assertEquals((float) $oil->selling_price, (float) $order->final_cost);
    }

    public function test_other_customer_cannot_approve_someone_elses_service_order_item(): void
    {
        $customer1 = User::where('email', 'customer@toyota-autoconnect.id')->firstOrFail();
        $customer2 = User::factory()->create();
        $customer2->assignRole('Customer');

        $branch = Branch::firstOrFail();
        $serviceType = ServiceType::firstOrFail();
        $oil = SparePart::where('part_number', '08880-83389')->firstOrFail();

        $order = ServiceOrder::create([
            'order_number' => ServiceOrder::generateNumber(),
            'user_id' => $customer1->id,
            'branch_id' => $branch->id,
            'vehicle_name' => 'Rush GR Sport',
            'license_plate' => 'B 7777 RSH',
            'service_type_id' => $serviceType->id,
            'status' => 'inspection',
        ]);

        $item = $order->items()->create([
            'item_type' => 'part',
            'spare_part_id' => $oil->id,
            'name' => $oil->name,
            'quantity' => 1,
            'unit_price' => $oil->selling_price,
            'subtotal' => $oil->selling_price,
            'approval_status' => 'pending_approval',
        ]);

        $this->actingAs($customer2)
            ->post(route('customer.service-orders.items.approve', [$order, $item]))
            ->assertForbidden();
    }
}
