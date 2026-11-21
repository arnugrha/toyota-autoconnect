<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\InventoryMovement;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\SalesOffer;
use App\Models\SalesOpportunity;
use App\Models\TestDriveBooking;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use App\Models\VehicleInventory;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestDriveAndSalesManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    /**
     * 1. Customer dapat mengajukan test drive.
     */
    public function test_customer_can_submit_test_drive_booking(): void
    {
        $customer = User::factory()->customer()->create([
            'name' => 'Aditya Pratama',
            'email' => 'aditya@example.com',
            'phone' => '081234567890',
        ]);

        $vehicle = Vehicle::factory()->create([
            'name' => 'Toyota Yaris Cross',
            'publication_status' => 'published',
            'price' => 350000000,
        ]);

        $branch = Branch::factory()->create([
            'name' => 'AutoConnect Sudirman',
            'is_active' => true,
            'slot_capacity' => 2,
        ]);

        $bookingDate = now()->addDays(2)->format('Y-m-d');

        $response = $this->actingAs($customer)->post(route('test-drives.store'), [
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'scheduled_date' => $bookingDate,
            'scheduled_time' => '10:00',
            'customer_note' => 'Ingin mencoba varian hybrid.',
        ]);

        $booking = TestDriveBooking::where('user_id', $customer->id)->first();
        $this->assertNotNull($booking);

        $response->assertRedirect(route('customer.test-drives.show', $booking));
        $this->assertStringStartsWith('TDB-', $booking->code);
        $this->assertEquals('pending', $booking->status);
        $this->assertEquals('Aditya Pratama', $booking->customer_name);
        $this->assertEquals('aditya@example.com', $booking->customer_email);
        $this->assertEquals('081234567890', $booking->customer_phone);

        // Memastikan lead CRM dibuat secara otomatis
        $this->assertDatabaseHas('leads', [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'test_drive',
            'source' => 'test_drive_booking',
        ]);

        // Memastikan Sales Opportunity dibuka
        $this->assertDatabaseHas('sales_opportunities', [
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'stage' => 'test_drive',
        ]);
    }

    /**
     * 2. Customer tidak dapat melihat booking milik orang lain (IDOR Prevention).
     */
    public function test_customer_cannot_view_or_cancel_other_customer_booking(): void
    {
        $customerA = User::factory()->customer()->create(['name' => 'Customer A']);
        $customerB = User::factory()->customer()->create(['name' => 'Customer B']);

        $branch = Branch::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $bookingA = TestDriveBooking::factory()->create([
            'user_id' => $customerA->id,
            'branch_id' => $branch->id,
            'vehicle_id' => $vehicle->id,
            'scheduled_at' => now()->addDays(3),
            'status' => 'pending',
        ]);

        // Customer B mencoba melihat booking milik Customer A -> 403 Forbidden
        $response = $this->actingAs($customerB)->get(route('customer.test-drives.show', $bookingA));
        $response->assertStatus(403);

        // Customer B mencoba membatalkan booking milik Customer A -> 403 Forbidden
        $cancelResponse = $this->actingAs($customerB)->post(route('customer.test-drives.cancel', $bookingA), [
            'cancellation_reason' => 'Mencoba membatalkan',
        ]);
        $cancelResponse->assertStatus(403);
    }

    /**
     * 3. Booking pada tanggal lampau ditolak.
     */
    public function test_booking_on_past_date_is_rejected(): void
    {
        $customer = User::factory()->customer()->create();
        $vehicle = Vehicle::factory()->create();
        $branch = Branch::factory()->create();

        $yesterday = now()->subDays(1)->format('Y-m-d');

        $response = $this->actingAs($customer)->post(route('test-drives.store'), [
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'scheduled_date' => $yesterday,
            'scheduled_time' => '10:00',
        ]);

        $response->assertSessionHasErrors('scheduled_date');
        $this->assertEquals(0, TestDriveBooking::count());
    }

    /**
     * 4. Slot yang sudah penuh tidak dapat dipesan.
     */
    public function test_booking_slot_capacity_limit_prevents_overbooking(): void
    {
        $customer1 = User::factory()->customer()->create();
        $customer2 = User::factory()->customer()->create();
        $customer3 = User::factory()->customer()->create();

        $vehicle = Vehicle::factory()->create();
        $branch = Branch::factory()->create(['slot_capacity' => 2]);

        $scheduledDateTime = now()->addDays(2)->setHour(10)->setMinute(0)->setSecond(0);

        // Buat 2 booking aktif pada jam yang sama (mencapai kapasitas 2)
        TestDriveBooking::factory()->create([
            'branch_id' => $branch->id,
            'scheduled_at' => $scheduledDateTime,
            'status' => 'confirmed',
        ]);
        TestDriveBooking::factory()->create([
            'branch_id' => $branch->id,
            'scheduled_at' => $scheduledDateTime,
            'status' => 'pending',
        ]);

        // Customer 3 mencoba memesan slot yang sama -> Ditolak di backend
        $response = $this->actingAs($customer3)->post(route('test-drives.store'), [
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'scheduled_date' => $scheduledDateTime->format('Y-m-d'),
            'scheduled_time' => '10:00',
        ]);

        $response->assertSessionHasErrors('scheduled_time');
        $this->assertEquals(2, TestDriveBooking::count());
    }

    /**
     * 5. Staf berwenang dapat mengonfirmasi atau menolak booking.
     */
    public function test_authorized_staff_can_confirm_or_reject_test_drive_booking(): void
    {
        $salesManager = User::factory()->withRole('Sales Manager')->create();
        $booking = TestDriveBooking::factory()->create(['status' => 'pending']);

        // Konfirmasi booking
        $response = $this->actingAs($salesManager)->put(route('admin.test-drives.status.update', $booking), [
            'status' => 'confirmed',
            'staff_note' => 'Unit Yaris Cross siap di showroom cabang.',
        ]);

        $response->assertSessionHas('success');
        $booking->refresh();
        $this->assertEquals('confirmed', $booking->status);
        $this->assertNotNull($booking->confirmed_at);
        $this->assertEquals($salesManager->id, $booking->confirmed_by);
        $this->assertEquals('Unit Yaris Cross siap di showroom cabang.', $booking->staff_note);

        // Penolakan booking dengan alasan
        $booking2 = TestDriveBooking::factory()->create(['status' => 'pending']);
        $rejectResponse = $this->actingAs($salesManager)->put(route('admin.test-drives.status.update', $booking2), [
            'status' => 'rejected',
            'reason' => 'Unit kendaraan sedang dalam jadwal servis berkala.',
        ]);

        $rejectResponse->assertSessionHas('success');
        $booking2->refresh();
        $this->assertEquals('rejected', $booking2->status);
        $this->assertEquals('Unit kendaraan sedang dalam jadwal servis berkala.', $booking2->rejection_reason);
    }

    /**
     * 6. Perubahan status tercatat dalam aktivitas CRM.
     */
    public function test_status_changes_are_recorded_in_activity(): void
    {
        $salesManager = User::factory()->withRole('Sales Manager')->create(['name' => 'Bambang Sales']);
        $customer = User::factory()->customer()->create();
        $vehicle = Vehicle::factory()->create(['name' => 'Toyota Fortuner 2.8']);
        $branch = Branch::factory()->create();

        $lead = Lead::create([
            'lead_number' => Lead::generateLeadNumber(),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'test_drive',
            'source' => 'test_drive_booking',
            'last_activity_at' => now(),
        ]);

        $booking = TestDriveBooking::factory()->create([
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'lead_id' => $lead->id,
            'status' => 'pending',
        ]);

        $this->actingAs($salesManager)->put(route('admin.test-drives.status.update', $booking), [
            'status' => 'completed',
            'staff_note' => 'Pelanggan sangat puas dengan performa mesin diesel.',
        ]);

        // Periksa lead activities
        $activity = LeadActivity::where('lead_id', $lead->id)
            ->where('activity_type', 'test_drive_completed')
            ->first();

        $this->assertNotNull($activity);
        $this->assertStringContainsString('Bambang Sales', $activity->description);
    }

    /**
     * 7. Test drive terhubung dengan lead yang sesuai tanpa membuat duplikat.
     */
    public function test_test_drive_links_to_existing_crm_lead_without_duplication(): void
    {
        $customer = User::factory()->customer()->create();
        $vehicle = Vehicle::factory()->create();
        $branch = Branch::factory()->create();

        // Lead sudah ada sebelumnya
        $existingLead = Lead::create([
            'lead_number' => Lead::generateLeadNumber(),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'contacted',
            'source' => 'website_inquiry',
            'last_activity_at' => now(),
        ]);

        $this->actingAs($customer)->post(route('test-drives.store'), [
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'scheduled_date' => now()->addDays(2)->format('Y-m-d'),
            'scheduled_time' => '14:30',
        ]);

        // Tidak boleh ada lead baru untuk pelanggan dan mobil yang sama
        $this->assertEquals(1, Lead::where('customer_id', $customer->id)->count());

        $existingLead->refresh();
        $this->assertEquals('test_drive', $existingLead->status);

        $booking = TestDriveBooking::where('user_id', $customer->id)->first();
        $this->assertEquals($existingLead->id, $booking->lead_id);
    }

    /**
     * 8. Sales Executive hanya dapat mengakses opportunity sesuai cakupannya.
     */
    public function test_sales_executive_can_only_access_assigned_opportunities(): void
    {
        $salesA = User::factory()->withRole('Sales Executive')->create();
        $salesB = User::factory()->withRole('Sales Executive')->create();

        $oppA = SalesOpportunity::factory()->create([
            'assigned_to' => $salesA->id,
        ]);
        $oppB = SalesOpportunity::factory()->create([
            'assigned_to' => $salesB->id,
        ]);

        // Sales A dapat melihat opp A
        $this->actingAs($salesA)->get(route('admin.sales.opportunities.show', $oppA))->assertStatus(200);

        // Sales A tidak dapat melihat opp B
        $this->actingAs($salesA)->get(route('admin.sales.opportunities.show', $oppB))->assertStatus(403);
    }

    /**
     * 9. Penawaran menghitung harga dan diskon dengan benar di backend.
     */
    public function test_sales_offer_calculates_price_and_discount_accurately_in_backend(): void
    {
        $salesManager = User::factory()->withRole('Sales Manager')->create();
        $vehicle = Vehicle::factory()->create(['price' => 500000000]); // 500 Juta
        $opp = SalesOpportunity::factory()->create(['vehicle_id' => $vehicle->id]);

        $validUntil = now()->addDays(14)->format('Y-m-d');

        // Diskon 20 Juta (4%, di bawah max 25%), Biaya Tambahan 5 Juta
        $response = $this->actingAs($salesManager)->post(route('admin.sales.offers.store'), [
            'sales_opportunity_id' => $opp->id,
            'discount_amount' => 20000000,
            'additional_cost' => 5000000,
            'valid_until' => $validUntil,
            'notes' => 'Paket promo diskon cash.',
        ]);

        $response->assertSessionHas('success');

        $offer = SalesOffer::where('sales_opportunity_id', $opp->id)->first();
        $this->assertNotNull($offer);
        $this->assertEquals(500000000, $offer->vehicle_price);
        $this->assertEquals(20000000, $offer->discount_amount);
        $this->assertEquals(5000000, $offer->additional_cost);
        // Total = 500.000.000 - 20.000.000 + 5.000.000 = 485.000.000
        $this->assertEquals(485000000, $offer->total_amount);

        // Cek penolakan diskon melebihi 25% (25% dari 500jt = 125jt. Kirim diskon 150jt -> ditolak)
        $invalidResponse = $this->actingAs($salesManager)->post(route('admin.sales.offers.store'), [
            'sales_opportunity_id' => $opp->id,
            'discount_amount' => 150000000,
            'valid_until' => $validUntil,
        ]);
        $invalidResponse->assertSessionHasErrors('discount_amount');
    }

    /**
     * 10. Harga penawaran tetap sama meskipun harga master kendaraan berubah (Snapshot Integrity).
     */
    public function test_sales_offer_price_snapshot_persists_even_if_master_vehicle_price_changes(): void
    {
        $salesManager = User::factory()->withRole('Sales Manager')->create();
        $vehicle = Vehicle::factory()->create(['price' => 300000000]);
        $opp = SalesOpportunity::factory()->create(['vehicle_id' => $vehicle->id]);

        $this->actingAs($salesManager)->post(route('admin.sales.offers.store'), [
            'sales_opportunity_id' => $opp->id,
            'discount_amount' => 10000000,
            'valid_until' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $offer = SalesOffer::where('sales_opportunity_id', $opp->id)->first();
        $this->assertEquals(300000000, $offer->vehicle_price);
        $this->assertEquals(290000000, $offer->total_amount);

        // Ubah harga master kendaraan menjadi 360 Juta
        $vehicle->update(['price' => 360000000]);

        $offer->refresh();
        // Harga penawaran harus tetap 300 Juta dan total tetap 290 Juta
        $this->assertEquals(300000000, $offer->vehicle_price);
        $this->assertEquals(290000000, $offer->total_amount);
    }

    /**
     * 11. Booking kendaraan memeriksa stok terlebih dahulu.
     */
    public function test_vehicle_booking_checks_stock_before_creation(): void
    {
        $salesManager = User::factory()->withRole('Sales Manager')->create();
        $vehicle = Vehicle::factory()->create();
        $branch = Branch::factory()->create();

        // Stok 0 di cabang ini
        VehicleInventory::create([
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'stock_quantity' => 0,
            'reserved_quantity' => 0,
        ]);

        $opp = SalesOpportunity::factory()->create([
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
        ]);

        $response = $this->actingAs($salesManager)->post(route('admin.sales.bookings.store'), [
            'sales_opportunity_id' => $opp->id,
            'booking_fee' => 5000000,
        ]);

        $response->assertSessionHasErrors('sales_opportunity_id');
        $this->assertEquals(0, VehicleBooking::count());
    }

    /**
     * 12. Pengalokasian stok tidak menyebabkan stok negatif dan mengunci unit dengan aman.
     */
    public function test_vehicle_booking_confirmation_allocates_stock_safely_without_negative_inventory(): void
    {
        $salesManager = User::factory()->withRole('Sales Manager')->create();
        $vehicle = Vehicle::factory()->create(['price' => 450000000]);
        $branch = Branch::factory()->create();

        // Hanya 1 unit fisik tersedia
        $inventory = VehicleInventory::create([
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'stock_quantity' => 1,
            'reserved_quantity' => 0,
        ]);

        $opp = SalesOpportunity::factory()->create([
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
        ]);

        // Buat booking pending
        $this->actingAs($salesManager)->post(route('admin.sales.bookings.store'), [
            'sales_opportunity_id' => $opp->id,
            'booking_fee' => 5000000,
        ]);

        $booking = VehicleBooking::first();
        $this->assertEquals('pending', $booking->status);
        $this->assertEquals(0, $inventory->fresh()->reserved_quantity);

        // Konfirmasi booking
        $this->actingAs($salesManager)->put(route('admin.sales.bookings.confirm', $booking));

        $booking->refresh();
        $this->assertEquals('confirmed', $booking->status);

        $inventory->refresh();
        $this->assertEquals(1, $inventory->reserved_quantity);
        $this->assertEquals(1, $inventory->stock_quantity);
        $this->assertEquals(0, $inventory->available_stock); // 1 - 1 = 0

        // Riwayat alokasi tercatat pada InventoryMovement
        $this->assertDatabaseHas('inventory_movements', [
            'vehicle_inventory_id' => $inventory->id,
            'movement_type' => 'reservation',
        ]);

        // Percobaan booking kedua pada cabang yang sama dan konfirmasi harus gagal karena available_stock = 0
        $opp2 = SalesOpportunity::factory()->create([
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
        ]);

        $booking2 = VehicleBooking::factory()->create([
            'sales_opportunity_id' => $opp2->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'inventory_id' => $inventory->id,
            'status' => 'pending',
        ]);

        $confirmResponse = $this->actingAs($salesManager)->put(route('admin.sales.bookings.confirm', $booking2));
        $confirmResponse->assertSessionHas('error');
        $this->assertEquals('pending', $booking2->fresh()->status);
        $this->assertEquals(1, $inventory->fresh()->reserved_quantity);
    }

    /**
     * 13. Pembatalan booking melepaskan alokasi stok sesuai aturan.
     */
    public function test_cancelling_booking_releases_allocated_inventory_stock(): void
    {
        $salesManager = User::factory()->withRole('Sales Manager')->create();
        $vehicle = Vehicle::factory()->create();
        $branch = Branch::factory()->create();

        $inventory = VehicleInventory::create([
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'stock_quantity' => 3,
            'reserved_quantity' => 1, // Sudah teralokasi 1 unit
        ]);

        $opp = SalesOpportunity::factory()->create([
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
        ]);

        $booking = VehicleBooking::factory()->create([
            'sales_opportunity_id' => $opp->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'inventory_id' => $inventory->id,
            'status' => 'confirmed',
        ]);

        $this->assertEquals(1, $inventory->reserved_quantity);

        // Batalkan booking
        $response = $this->actingAs($salesManager)->put(route('admin.sales.bookings.cancel', $booking), [
            'cancellation_reason' => 'Pelanggan membatalkan pembelian unit.',
        ]);

        $response->assertSessionHas('success');
        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);

        $inventory->refresh();
        // Reserved quantity berkurang dari 1 menjadi 0
        $this->assertEquals(0, $inventory->reserved_quantity);
        $this->assertEquals(3, $inventory->available_stock);

        // Catatan movement release dibuat
        $this->assertDatabaseHas('inventory_movements', [
            'vehicle_inventory_id' => $inventory->id,
            'movement_type' => 'release',
        ]);
    }

    /**
     * 14. Pengguna tanpa permission tidak dapat mengubah data sensitif.
     */
    public function test_unauthorized_user_cannot_modify_sensitive_sales_data(): void
    {
        $customer = User::factory()->customer()->create();
        $opp = SalesOpportunity::factory()->create();
        $booking = VehicleBooking::factory()->create();

        // Customer mencoba mengubah status booking orang lain
        $this->actingAs($customer)->put(route('admin.sales.bookings.confirm', $booking))->assertStatus(403);

        // Customer mencoba mengubah stage opportunity staf
        $this->actingAs($customer)->put(route('admin.sales.opportunities.update', $opp), [
            'stage' => 'closed_won',
        ])->assertStatus(403);
    }

    /**
     * 15. Fitur katalog, CRM, dan inventory dari tahap sebelumnya tetap berjalan lancar.
     */
    public function test_catalog_crm_and_inventory_remain_fully_functional(): void
    {
        $vehicle = Vehicle::factory()->create([
            'publication_status' => 'published',
            'slug' => 'toyota-innova-zenix-v',
        ]);

        // Katalog publik
        $this->get(route('vehicles.index'))->assertStatus(200);
        $this->get(route('vehicles.show', 'toyota-innova-zenix-v'))->assertStatus(200);

        // CRM index
        $crmOfficer = User::factory()->withRole('CRM Officer')->create();
        $this->actingAs($crmOfficer)->get(route('admin.crm.index'))->assertStatus(200);

        // Inventory index
        $invOfficer = User::factory()->withRole('Inventory Officer')->create();
        $this->actingAs($invOfficer)->get(route('admin.inventory.index'))->assertStatus(200);

        // Test Drive Management Index
        $salesManager = User::factory()->withRole('Sales Manager')->create();
        $this->actingAs($salesManager)->get(route('admin.test-drives.index'))->assertStatus(200);
        $this->actingAs($salesManager)->get(route('admin.sales.opportunities.create'))->assertStatus(200);

        // Customer Dashboard
        $customer = User::factory()->customer()->create();
        $this->actingAs($customer)->get(route('customer.dashboard'))->assertStatus(200);
    }
}
