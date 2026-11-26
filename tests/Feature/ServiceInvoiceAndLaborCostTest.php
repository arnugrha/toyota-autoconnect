<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\ServiceBooking;
use App\Models\ServiceType;
use App\Models\SparePart;
use App\Models\User;
use App\Services\ServiceBooking\ServiceBookingService;
use Database\Seeders\BranchAndCategorySeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\ServiceManagementSeeder;
use Database\Seeders\SparePartSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceInvoiceAndLaborCostTest extends TestCase
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

    public function test_end_to_end_service_flow_generates_labor_fee_and_official_invoice_for_customer(): void
    {
        $admin = User::where('email', 'superadmin@toyota-autoconnect.id')->firstOrFail();
        $customer = User::where('email', 'customer@toyota-autoconnect.id')->firstOrFail();
        $branch = Branch::firstOrFail();
        $serviceType = ServiceType::where('estimated_cost', '>', 0)->firstOrFail();
        $oil = SparePart::where('part_number', '08880-83389')->firstOrFail();

        // 1. Customer books service & Admin confirms booking => auto creates Work Order with base labor item
        $booking = ServiceBooking::create([
            'booking_code' => ServiceBooking::generateCode(),
            'user_id' => $customer->id,
            'branch_id' => $branch->id,
            'vehicle_name' => 'All New Kijang Innova Zenix HEV',
            'license_plate' => 'B 8899 ZNX',
            'current_mileage' => 20000,
            'service_type_id' => $serviceType->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '10:00',
            'status' => 'pending',
        ]);

        $order = app(ServiceBookingService::class)->confirmBooking($booking, $admin, 'Teknisi Andi');

        $order->refresh();
        $this->assertEquals((float) $serviceType->estimated_cost, (float) $order->labor_cost);
        $this->assertEquals((float) $serviceType->estimated_cost, (float) $order->final_cost);

        // 2. Admin adds spare part via dropdown (requires customer approval)
        $this->actingAs($admin)->post(route('admin.service-orders.items.store', $order), [
            'item_type' => 'part',
            'spare_part_id' => $oil->id,
            'quantity' => 1,
            'requires_approval' => '1',
        ]);

        $partItem = $order->items()->where('item_type', 'part')->firstOrFail();

        // 3. Customer approves additional spare part
        $this->actingAs($customer)->post(route('customer.service-orders.items.approve', [$order, $partItem]));

        // 4. Admin marks Work Order as completed => Official Invoice generated automatically
        $this->actingAs($admin)->put(route('admin.service-orders.status.update', $order), [
            'status' => 'completed',
            'notes' => 'Servis selesai dan sudah uji jalan.',
        ]);

        $order->refresh();
        $expectedGrandTotal = (float) $serviceType->estimated_cost + (float) $oil->selling_price;

        $this->assertNotNull($order->invoice_number);
        $this->assertStringStartsWith('INV-SRV-', $order->invoice_number);
        $this->assertEquals((float) $serviceType->estimated_cost, (float) $order->labor_cost);
        $this->assertEquals((float) $oil->selling_price, (float) $order->parts_cost);
        $this->assertEquals($expectedGrandTotal, (float) $order->final_cost);
        $this->assertEquals('unpaid', $order->payment_status);

        // 5. Customer views official invoice page and history index
        $this->actingAs($customer)
            ->get(route('customer.service-histories.index'))
            ->assertOk()
            ->assertSee($order->invoice_number)
            ->assertSee('Lihat Invoice');

        $this->actingAs($customer)
            ->get(route('customer.service-histories.show', $order))
            ->assertOk()
            ->assertSee('INVOICE SERVIS RESMI')
            ->assertSee($order->invoice_number)
            ->assertSee('Rincian Biaya Jasa Servis')
            ->assertSee('Rincian Suku Cadang Resmi')
            ->assertSee($oil->part_number)
            ->assertSee('MENUNGGU PEMBAYARAN');

        // 6. Admin marks invoice as paid => Customer sees LUNAS stamp
        $this->actingAs($admin)
            ->post(route('admin.service-orders.mark-paid', $order))
            ->assertRedirect(route('admin.service-orders.show', $order));

        $this->assertTrue($order->fresh()->isPaid());

        $this->actingAs($customer)
            ->get(route('customer.service-histories.show', $order))
            ->assertOk()
            ->assertSee('LUNAS');
    }
}
