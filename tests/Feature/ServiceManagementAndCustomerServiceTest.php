<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\ServiceBooking;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\ServiceReminder;
use App\Models\ServiceSchedule;
use App\Models\ServiceType;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\ServiceManagementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ServiceManagementAndCustomerServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $otherCustomer;
    protected User $serviceAdvisor;
    protected User $crmOfficer;
    protected User $superAdmin;
    protected Branch $branch;
    protected ServiceType $serviceType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(ServiceManagementSeeder::class);

        $this->branch = Branch::firstOrCreate(
            ['code' => 'JKT-PST-01'],
            [
                'name' => 'AutoConnect Jakarta Pusat',
                'slug' => 'autoconnect-jakarta-pusat',
                'city' => 'Jakarta Pusat',
                'address' => 'Jl. M.H. Thamrin No. 9',
                'phone' => '021-3901122',
                'email' => 'jakpus@autoconnect.toyota.co.id',
                'is_active' => true,
            ]
        );

        $this->serviceType = ServiceType::first();

        // Ensure Sunday is non-operational and Monday is operational with max_slot_capacity 2 and max_daily_capacity 4
        ServiceSchedule::updateOrCreate(
            ['branch_id' => $this->branch->id, 'day_of_week' => 0],
            [
                'opening_time' => '08:00',
                'closing_time' => '17:00',
                'slot_duration_minutes' => 60,
                'max_slot_capacity' => 2,
                'max_daily_capacity' => 4,
                'is_operational' => false,
            ]
        );

        ServiceSchedule::updateOrCreate(
            ['branch_id' => $this->branch->id, 'day_of_week' => 1],
            [
                'opening_time' => '08:00',
                'closing_time' => '17:00',
                'slot_duration_minutes' => 60,
                'max_slot_capacity' => 2,
                'max_daily_capacity' => 4,
                'is_operational' => true,
            ]
        );

        $this->customer = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
        ]);
        $this->customer->assignRole('Customer');

        $this->otherCustomer = User::factory()->create([
            'name' => 'Dewi Anggraini',
            'email' => 'dewi@example.com',
            'phone' => '081298765432',
        ]);
        $this->otherCustomer->assignRole('Customer');

        $this->serviceAdvisor = User::factory()->create([
            'name' => 'Ahmad Service Advisor',
            'email' => 'ahmad.sa@toyota.co.id',
        ]);
        $this->serviceAdvisor->assignRole('Service Advisor');

        $this->crmOfficer = User::factory()->create([
            'name' => 'Siti CRM Officer',
            'email' => 'siti.crm@toyota.co.id',
        ]);
        $this->crmOfficer->assignRole('CRM Officer');

        $this->superAdmin = User::factory()->create([
            'name' => 'Super Admin Toyota',
            'email' => 'admin@toyota.co.id',
        ]);
        $this->superAdmin->assignRole('Super Admin');
    }

    /**
     * 1. Pelanggan dapat membuat booking servis dengan sukses.
     */
    public function test_customer_can_create_service_booking_successfully(): void
    {
        // Find next Monday
        $targetDate = now()->next(\Carbon\Carbon::MONDAY)->toDateString();

        $response = $this->actingAs($this->customer)->post(route('customer.service-bookings.store'), [
            'branch_id' => $this->branch->id,
            'service_type_id' => $this->serviceType->id,
            'vehicle_name' => 'All New Yaris Cross HEV',
            'license_plate' => 'B 1999 TOY',
            'current_mileage' => 10000,
            'booking_date' => $targetDate,
            'booking_time' => '09:00',
            'customer_complaints' => 'Ganti oli berkala dan pengecekan rem.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('service_bookings', [
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'vehicle_name' => 'All New Yaris Cross HEV',
            'license_plate' => 'B 1999 TOY',
            'booking_time' => '09:00',
            'status' => 'pending',
        ]);
    }

    /**
     * Test available slots endpoint returns available and is_available flags correctly.
     */
    public function test_available_slots_endpoint_returns_valid_is_available_flags(): void
    {
        $mondayDate = now()->next(\Carbon\Carbon::MONDAY)->toDateString();

        $response = $this->actingAs($this->customer)->getJson(
            route('customer.service-bookings.available-slots', [
                'branch_id' => $this->branch->id,
                'date' => $mondayDate,
            ])
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'slots' => [
                '*' => ['time', 'available', 'is_available', 'remaining_capacity'],
            ],
        ]);

        $slots = $response->json('slots');
        $this->assertNotEmpty($slots);
        $this->assertTrue($slots[0]['available']);
        $this->assertTrue($slots[0]['is_available']);
        $this->assertGreaterThan(0, $slots[0]['remaining_capacity']);
    }

    /**
     * 2. Pelanggan tidak dapat mengakses atau membatalkan booking milik pelanggan lain.
     */
    public function test_customer_cannot_view_or_cancel_other_customer_service_booking(): void
    {
        $targetDate = now()->next(\Carbon\Carbon::MONDAY)->toDateString();

        $booking = ServiceBooking::create([
            'booking_code' => ServiceBooking::generateCode(),
            'user_id' => $this->otherCustomer->id,
            'branch_id' => $this->branch->id,
            'service_type_id' => $this->serviceType->id,
            'vehicle_name' => 'Kijang Innova Zenix',
            'license_plate' => 'B 8888 ZNX',
            'booking_date' => $targetDate,
            'booking_time' => '10:00',
            'status' => 'pending',
        ]);

        // Customer attempts to view other customer's booking
        $viewResponse = $this->actingAs($this->customer)->get(route('customer.service-bookings.show', $booking));
        $viewResponse->assertForbidden();

        // Customer attempts to cancel other customer's booking
        $cancelResponse = $this->actingAs($this->customer)->post(route('customer.service-bookings.cancel', $booking), [
            'cancellation_reason' => 'Mencoba batalkan punya orang lain',
        ]);
        $cancelResponse->assertForbidden();

        $this->assertEquals('pending', $booking->fresh()->status);
    }

    /**
     * 3. Tidak dapat melakukan booking pada hari libur / non-operasional bengkel.
     */
    public function test_cannot_book_when_day_is_closed_or_non_operational(): void
    {
        // Next Sunday (day_of_week = 0) is closed
        $sundayDate = now()->next(\Carbon\Carbon::SUNDAY)->toDateString();

        $response = $this->actingAs($this->customer)->post(route('customer.service-bookings.store'), [
            'branch_id' => $this->branch->id,
            'service_type_id' => $this->serviceType->id,
            'vehicle_name' => 'Avanza 1.5 G',
            'license_plate' => 'B 1234 AVZ',
            'booking_date' => $sundayDate,
            'booking_time' => '09:00',
        ]);

        $response->assertSessionHasErrors(['booking_time']);
        $this->assertDatabaseMissing('service_bookings', [
            'license_plate' => 'B 1234 AVZ',
            'booking_date' => $sundayDate,
        ]);
    }

    /**
     * 4. Booking melebihi kapasitas per slot waktu ditolak oleh backend.
     */
    public function test_cannot_book_when_slot_capacity_is_exceeded(): void
    {
        $targetDate = now()->next(\Carbon\Carbon::MONDAY)->toDateString();

        // Slot capacity is 2. Fill 2 bookings for 09:00 slot
        for ($i = 1; $i <= 2; $i++) {
            ServiceBooking::create([
                'booking_code' => ServiceBooking::generateCode() . $i,
                'user_id' => $this->otherCustomer->id,
                'branch_id' => $this->branch->id,
                'service_type_id' => $this->serviceType->id,
                'vehicle_name' => "Mobil {$i}",
                'license_plate' => "B 100{$i} TY",
                'booking_date' => $targetDate,
                'booking_time' => '09:00',
                'status' => 'confirmed',
            ]);
        }

        // 3rd booking for 09:00 must fail
        $response = $this->actingAs($this->customer)->post(route('customer.service-bookings.store'), [
            'branch_id' => $this->branch->id,
            'service_type_id' => $this->serviceType->id,
            'vehicle_name' => 'Mobil Ketiga',
            'license_plate' => 'B 3333 TY',
            'booking_date' => $targetDate,
            'booking_time' => '09:00',
        ]);

        $response->assertSessionHasErrors(['booking_time']);
        $this->assertDatabaseMissing('service_bookings', [
            'license_plate' => 'B 3333 TY',
        ]);
    }

    /**
     * 5. Booking melebihi kuota harian cabang ditolak oleh backend.
     */
    public function test_cannot_book_when_daily_capacity_is_exceeded(): void
    {
        $targetDate = now()->next(\Carbon\Carbon::MONDAY)->toDateString();

        // Daily capacity is 4. Fill 4 bookings across different slots (08:00 and 10:00)
        ServiceBooking::create([
            'booking_code' => 'BK-1',
            'user_id' => $this->otherCustomer->id,
            'branch_id' => $this->branch->id,
            'service_type_id' => $this->serviceType->id,
            'vehicle_name' => 'Mobil 1',
            'license_plate' => 'B 1111 AA',
            'booking_date' => $targetDate,
            'booking_time' => '08:00',
            'status' => 'confirmed',
        ]);
        ServiceBooking::create([
            'booking_code' => 'BK-2',
            'user_id' => $this->otherCustomer->id,
            'branch_id' => $this->branch->id,
            'service_type_id' => $this->serviceType->id,
            'vehicle_name' => 'Mobil 2',
            'license_plate' => 'B 2222 AA',
            'booking_date' => $targetDate,
            'booking_time' => '08:00',
            'status' => 'confirmed',
        ]);
        ServiceBooking::create([
            'booking_code' => 'BK-3',
            'user_id' => $this->otherCustomer->id,
            'branch_id' => $this->branch->id,
            'service_type_id' => $this->serviceType->id,
            'vehicle_name' => 'Mobil 3',
            'license_plate' => 'B 3333 AA',
            'booking_date' => $targetDate,
            'booking_time' => '10:00',
            'status' => 'confirmed',
        ]);
        ServiceBooking::create([
            'booking_code' => 'BK-4',
            'user_id' => $this->otherCustomer->id,
            'branch_id' => $this->branch->id,
            'service_type_id' => $this->serviceType->id,
            'vehicle_name' => 'Mobil 4',
            'license_plate' => 'B 4444 AA',
            'booking_date' => $targetDate,
            'booking_time' => '10:00',
            'status' => 'confirmed',
        ]);

        // 5th booking on 11:00 should fail because daily limit is 4
        $response = $this->actingAs($this->customer)->post(route('customer.service-bookings.store'), [
            'branch_id' => $this->branch->id,
            'service_type_id' => $this->serviceType->id,
            'vehicle_name' => 'Mobil Kelima',
            'license_plate' => 'B 5555 AA',
            'booking_date' => $targetDate,
            'booking_time' => '11:00',
        ]);

        $response->assertSessionHasErrors(['booking_time']);
    }

    /**
     * 6. Petugas dealer dapat mengonfirmasi booking dan menerbitkan Work Order (Service Order).
     */
    public function test_staff_can_confirm_service_booking_and_generate_service_order(): void
    {
        $targetDate = now()->next(\Carbon\Carbon::MONDAY)->toDateString();

        $booking = ServiceBooking::create([
            'booking_code' => ServiceBooking::generateCode(),
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_type_id' => $this->serviceType->id,
            'vehicle_name' => 'Fortuner 2.8 GR Sport',
            'license_plate' => 'B 7777 FOR',
            'current_mileage' => 15000,
            'booking_date' => $targetDate,
            'booking_time' => '13:00',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->serviceAdvisor)->post(route('admin.service-bookings.confirm', $booking), [
            'technician_name' => 'Slamet Riyadi',
        ]);

        $response->assertRedirect();
        $booking->refresh();

        $this->assertEquals('confirmed', $booking->status);
        $this->assertEquals($this->serviceAdvisor->id, $booking->confirmed_by);

        $this->assertDatabaseHas('service_orders', [
            'service_booking_id' => $booking->id,
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'vehicle_name' => 'Fortuner 2.8 GR Sport',
            'license_plate' => 'B 7777 FOR',
            'technician_name' => 'Slamet Riyadi',
            'status' => 'confirmed',
        ]);
    }

    /**
     * 7. Petugas dealer dapat melakukan reschedule booking dengan alasan yang jelas.
     */
    public function test_staff_can_reschedule_service_booking_with_reason(): void
    {
        $oldDate = now()->next(\Carbon\Carbon::MONDAY)->toDateString();
        $newDate = now()->next(\Carbon\Carbon::MONDAY)->addWeek()->toDateString();

        $booking = ServiceBooking::create([
            'booking_code' => ServiceBooking::generateCode(),
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_type_id' => $this->serviceType->id,
            'vehicle_name' => 'Corolla Cross HEV',
            'license_plate' => 'B 5555 COR',
            'booking_date' => $oldDate,
            'booking_time' => '14:00',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->serviceAdvisor)->post(route('admin.service-bookings.reschedule', $booking), [
            'booking_date' => $newDate,
            'booking_time' => '15:00',
            'reschedule_reason' => 'Stall servis sedang kalibrasi alat uji emisi.',
        ]);

        $response->assertRedirect();
        $booking->refresh();

        $this->assertEquals($newDate, $booking->booking_date->toDateString());
        $this->assertEquals('15:00', $booking->booking_time);
        $this->assertEquals('Stall servis sedang kalibrasi alat uji emisi.', $booking->reschedule_reason);
    }

    /**
     * 8. Petugas dealer dapat menolak booking dengan alasan yang dicatat.
     */
    public function test_staff_can_reject_service_booking_with_reason(): void
    {
        $targetDate = now()->next(\Carbon\Carbon::MONDAY)->toDateString();

        $booking = ServiceBooking::create([
            'booking_code' => ServiceBooking::generateCode(),
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_type_id' => $this->serviceType->id,
            'vehicle_name' => 'Vios 1.5 G',
            'license_plate' => 'B 1111 VIO',
            'booking_date' => $targetDate,
            'booking_time' => '09:00',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->serviceAdvisor)->post(route('admin.service-bookings.reject', $booking), [
            'rejection_reason' => 'Bengkel sedang renovasi stall cat dan perbaikan khusus.',
        ]);

        $response->assertRedirect();
        $booking->refresh();

        $this->assertEquals('rejected', $booking->status);
        $this->assertEquals('Bengkel sedang renovasi stall cat dan perbaikan khusus.', $booking->rejection_reason);
    }

    /**
     * 9. Service Order dapat diperbarui melalui tahapan status dengan audit log yang valid.
     */
    public function test_service_order_workflow_transitions_with_audit_logs(): void
    {
        $order = ServiceOrder::create([
            'order_number' => ServiceOrder::generateNumber(),
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'vehicle_name' => 'All New Veloz Q CVT',
            'license_plate' => 'B 2026 VLZ',
            'service_type_id' => $this->serviceType->id,
            'service_advisor_id' => $this->serviceAdvisor->id,
            'status' => 'confirmed',
        ]);

        // Stage: vehicle_received
        $this->actingAs($this->serviceAdvisor)->put(route('admin.service-orders.status.update', $order), [
            'status' => 'vehicle_received',
            'notes' => 'Kendaraan tiba di bengkel, STNK diperiksa.',
        ]);

        $order->refresh();
        $this->assertEquals('vehicle_received', $order->status);
        $this->assertNotNull($order->received_at);
        $this->assertDatabaseHas('service_order_logs', [
            'service_order_id' => $order->id,
            'old_status' => 'confirmed',
            'new_status' => 'vehicle_received',
        ]);

        // Stage: inspection
        $this->actingAs($this->serviceAdvisor)->put(route('admin.service-orders.status.update', $order), [
            'status' => 'inspection',
        ]);
        $this->assertEquals('inspection', $order->fresh()->status);

        // Stage: in_progress
        $this->actingAs($this->serviceAdvisor)->put(route('admin.service-orders.status.update', $order), [
            'status' => 'in_progress',
        ]);
        $this->assertEquals('in_progress', $order->fresh()->status);

        // Stage: completed
        $this->actingAs($this->serviceAdvisor)->put(route('admin.service-orders.status.update', $order), [
            'status' => 'completed',
        ]);
        $order->refresh();
        $this->assertEquals('completed', $order->status);
        $this->assertNotNull($order->completed_at);
    }

    /**
     * 10. Pencatatan hasil inspeksi teknisi dan rekomendasi perbaikan.
     */
    public function test_recording_technician_inspection_and_repair_actions(): void
    {
        $order = ServiceOrder::create([
            'order_number' => ServiceOrder::generateNumber(),
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_type_id' => $this->serviceType->id,
            'vehicle_name' => 'All New Avanza',
            'license_plate' => 'B 1234 ABC',
            'status' => 'inspection',
        ]);

        $response = $this->actingAs($this->serviceAdvisor)->post(route('admin.service-orders.inspection.store', $order), [
            'technician_name' => 'Wahyudi',
            'current_mileage' => 20500,
            'inspection_result' => 'Minyak rem berkurang 10%, filter udara kotor ringan.',
            'repair_actions' => 'Kuras minyak rem dan semprot pembersih filter udara.',
        ]);

        $response->assertRedirect();
        $order->refresh();

        $this->assertEquals('Wahyudi', $order->technician_name);
        $this->assertEquals(20500, $order->current_mileage);
        $this->assertEquals('Minyak rem berkurang 10%, filter udara kotor ringan.', $order->inspection_result);
        $this->assertEquals('Kuras minyak rem dan semprot pembersih filter udara.', $order->repair_actions);
    }

    /**
     * 11. Penambahan dan penghapusan item suku cadang / jasa otomatis menghitung total biaya akhir.
     */
    public function test_adding_and_removing_items_recalculates_final_cost(): void
    {
        $order = ServiceOrder::create([
            'order_number' => ServiceOrder::generateNumber(),
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_type_id' => $this->serviceType->id,
            'vehicle_name' => 'Raize 1.0T GR Sport',
            'license_plate' => 'B 9999 RAI',
            'status' => 'in_progress',
        ]);

        // Add part item: 4 x 100,000 = 400,000
        $this->actingAs($this->serviceAdvisor)->post(route('admin.service-orders.items.store', $order), [
            'item_type' => 'part',
            'name' => 'Toyota Motor Oil 1L',
            'quantity' => 4,
            'unit_price' => 100000,
        ]);

        // Add labor item: 1 x 250,000 = 250,000
        $this->actingAs($this->serviceAdvisor)->post(route('admin.service-orders.items.store', $order), [
            'item_type' => 'labor',
            'name' => 'Jasa Servis Berkala',
            'quantity' => 1,
            'unit_price' => 250000,
        ]);

        $order->refresh();
        $this->assertEquals(650000, (float) $order->final_cost);
        $this->assertEquals(2, $order->items()->count());

        // Remove the labor item
        $laborItem = $order->items()->where('item_type', 'labor')->first();
        $this->actingAs($this->serviceAdvisor)->delete(route('admin.service-orders.items.destroy', [$order, $laborItem]));

        $order->refresh();
        $this->assertEquals(400000, (float) $order->final_cost);
        $this->assertEquals(1, $order->items()->count());
    }

    /**
     * 12. Penyelesaian Service Order otomatis membuat pengingat perawatan berkala (after-sales reminder).
     */
    public function test_service_order_completion_automatically_creates_reminder(): void
    {
        $order = ServiceOrder::create([
            'order_number' => ServiceOrder::generateNumber(),
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'service_type_id' => $this->serviceType->id,
            'vehicle_name' => 'Camry 2.5 V',
            'license_plate' => 'B 1 CMR',
            'current_mileage' => 30000,
            'status' => 'in_progress',
        ]);

        $this->actingAs($this->serviceAdvisor)->put(route('admin.service-orders.status.update', $order), [
            'status' => 'completed',
            'notes' => 'Pengerjaan selesai dengan baik.',
        ]);

        $this->assertDatabaseHas('service_reminders', [
            'user_id' => $this->customer->id,
            'license_plate' => 'B 1 CMR',
            'last_mileage' => 30000,
            'next_service_mileage' => 40000,
            'status' => 'pending',
        ]);
    }

    /**
     * 13. Perintah artisan scheduler pengingat servis mengirimkan reminder yang jatuh tempo.
     */
    public function test_artisan_command_sends_due_service_reminders(): void
    {
        // Reminder due tomorrow
        $reminder = ServiceReminder::create([
            'user_id' => $this->customer->id,
            'vehicle_name' => 'Yaris GR',
            'license_plate' => 'B 1000 GR',
            'next_service_date' => now()->addDays(3)->toDateString(),
            'recommended_service' => 'Servis Berkala 10.000 KM',
            'status' => 'pending',
        ]);

        $this->artisan('toyota:send-service-reminders')
            ->expectsOutputToContain('Berhasil mengirim 1 pengingat servis pelanggan.')
            ->assertExitCode(0);

        $reminder->refresh();
        $this->assertEquals('sent', $reminder->status);
        $this->assertNotNull($reminder->reminder_sent_at);
    }

    /**
     * 14. Pelanggan dapat membuat tiket bantuan (Helpdesk CS) dengan kategori dan prioritas.
     */
    public function test_customer_can_create_support_ticket(): void
    {
        $response = $this->actingAs($this->customer)->post(route('customer.tickets.store'), [
            'category' => 'warranty',
            'priority' => 'high',
            'branch_id' => $this->branch->id,
            'subject' => 'Pertanyaan Klaim Garansi Baterai HEV',
            'description' => 'Apakah garansi baterai hybrid 8 tahun masih berlaku jika servis di luar kota?',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('support_tickets', [
            'user_id' => $this->customer->id,
            'category' => 'warranty',
            'priority' => 'high',
            'subject' => 'Pertanyaan Klaim Garansi Baterai HEV',
            'status' => 'open',
        ]);

        // First message thread created
        $this->assertDatabaseHas('support_ticket_messages', [
            'user_id' => $this->customer->id,
            'is_internal' => false,
        ]);
    }

    /**
     * 15. Staf CS dapat membalas tiket atau menambahkan catatan internal yang tidak terlihat oleh pelanggan.
     */
    public function test_staff_can_reply_and_add_internal_notes_hidden_from_customer(): void
    {
        $ticket = SupportTicket::create([
            'ticket_number' => SupportTicket::generateNumber(),
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'category' => 'service_complaint',
            'priority' => 'medium',
            'status' => 'open',
            'subject' => 'Waktu Tunggu Ruang Tamu Bengkel',
            'description' => 'Kenapa kemarin pengerjaan molor 30 menit?',
        ]);

        // Staff adds internal note (is_internal = 1)
        $this->actingAs($this->crmOfficer)->post(route('admin.tickets.reply', $ticket), [
            'message' => 'Catatan internal: Kemarin ada keterlambatan pasokan oli dari gudang.',
            'is_internal' => 1,
        ]);

        // Staff sends public reply to customer (is_internal = 0)
        $this->actingAs($this->crmOfficer)->post(route('admin.tickets.reply', $ticket), [
            'message' => 'Halo Bapak Budi, kami mohon maaf atas ketidaknyamanan kemarin.',
            'is_internal' => 0,
        ]);

        $ticket->refresh();
        $this->assertEquals('waiting_customer', $ticket->status);

        // When customer views ticket thread, internal note is filtered out
        $customerView = $this->actingAs($this->customer)->get(route('customer.tickets.show', $ticket));
        $customerView->assertOk();
        $customerView->assertSee('Halo Bapak Budi, kami mohon maaf');
        $customerView->assertDontSee('Catatan internal: Kemarin ada keterlambatan');

        // When staff views ticket thread, internal note is visible
        $staffView = $this->actingAs($this->crmOfficer)->get(route('admin.tickets.show', $ticket));
        $staffView->assertOk();
        $staffView->assertSee('Catatan internal: Kemarin ada keterlambatan');
    }

    /**
     * 16. Staf CS dapat memperbarui status tiket dan menugaskan petugas (agent assignment).
     */
    public function test_staff_can_update_ticket_status_and_assign_agent(): void
    {
        $ticket = SupportTicket::create([
            'ticket_number' => SupportTicket::generateNumber(),
            'user_id' => $this->customer->id,
            'category' => 'general_inquiry',
            'priority' => 'low',
            'status' => 'open',
            'subject' => 'Ketersediaan warna bZ4X',
            'description' => 'Warna Platinum White Pearl apakah ada unit display?',
        ]);

        // Assign agent
        $this->actingAs($this->crmOfficer)->put(route('admin.tickets.assign', $ticket), [
            'assigned_to' => $this->crmOfficer->id,
        ]);

        $ticket->refresh();
        $this->assertEquals($this->crmOfficer->id, $ticket->assigned_to);

        // Update status to resolved
        $this->actingAs($this->crmOfficer)->put(route('admin.tickets.status.update', $ticket), [
            'status' => 'resolved',
        ]);

        $ticket->refresh();
        $this->assertEquals('resolved', $ticket->status);
        $this->assertNotNull($ticket->resolved_at);
    }
}
