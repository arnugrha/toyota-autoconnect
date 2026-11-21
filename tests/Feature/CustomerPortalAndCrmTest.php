<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPortalAndCrmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed standard roles and permissions into in-memory testing database
        $this->seed(RoleAndPermissionSeeder::class);
    }

    /**
     * 1. Customer dapat mengirim inquiry yang valid.
     */
    public function test_customer_can_submit_valid_inquiry(): void
    {
        $customer = User::factory()->customer()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@customer.test',
            'phone' => '081234567890',
        ]);

        $vehicle = Vehicle::factory()->create([
            'name' => 'Toyota Kijang Innova Zenix',
        ]);

        $response = $this->actingAs($customer)->post(route('inquiries.store'), [
            'vehicle_id' => $vehicle->id,
            'customer_name' => 'Budi Santoso',
            'customer_email' => 'budi@customer.test',
            'customer_phone' => '081234567890',
            'subject' => 'Penawaran Harga OTR & Promo',
            'message' => 'Mohon informasi estimasi diskon dan paket DP ringan untuk Innova Zenix.',
            'consent' => '1',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('inquiries', [
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'subject' => 'Penawaran Harga OTR & Promo',
            'status' => 'new',
        ]);
    }

    /**
     * 2. Inquiry tersimpan dan terhubung dengan customer serta kendaraan.
     */
    public function test_inquiry_is_saved_and_linked_to_customer_and_vehicle(): void
    {
        $customer = User::factory()->customer()->create();
        $vehicle = Vehicle::factory()->create(['name' => 'Toyota Fortuner GR Sport']);

        $this->actingAs($customer)->post(route('inquiries.store'), [
            'vehicle_id' => $vehicle->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => '081299998888',
            'subject' => 'Pemesanan Jadwal Test Drive',
            'message' => 'Saya ingin menjadwalkan sesi test drive akhir pekan ini.',
            'consent' => '1',
        ]);

        $inquiry = Inquiry::latest()->first();

        $this->assertNotNull($inquiry);
        $this->assertEquals($customer->id, $inquiry->user_id);
        $this->assertEquals($vehicle->id, $inquiry->vehicle_id);
        $this->assertEquals('Toyota Fortuner GR Sport', $inquiry->vehicle->name);
        $this->assertEquals($customer->name, $inquiry->user->name);
    }

    /**
     * 3. Inquiry menghasilkan lead yang benar.
     */
    public function test_inquiry_automatically_generates_crm_lead(): void
    {
        $customer = User::factory()->customer()->create();
        $vehicle = Vehicle::factory()->create();

        $this->actingAs($customer)->post(route('inquiries.store'), [
            'vehicle_id' => $vehicle->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => '081211112222',
            'subject' => 'Konsultasi Simulasi Kredit',
            'message' => 'Tolong hitungkan cicilan 3 tahun dengan DP 30%.',
            'consent' => '1',
        ]);

        $inquiry = Inquiry::latest()->first();
        $lead = Lead::where('inquiry_id', $inquiry->id)->first();

        $this->assertNotNull($lead);
        $this->assertEquals($customer->id, $lead->customer_id);
        $this->assertEquals($vehicle->id, $lead->vehicle_id);
        $this->assertEquals('new', $lead->status);
        $this->assertEquals('website_inquiry', $lead->source);
        $this->assertStringStartsWith('LD-', $lead->lead_number);
    }

    /**
     * 4. Inquiry gagal tidak menghasilkan lead yatim (database transaction rollback).
     */
    public function test_failed_inquiry_does_not_create_orphaned_lead(): void
    {
        $customer = User::factory()->customer()->create();

        // Send invalid payload (missing consent and required message)
        $response = $this->actingAs($customer)->post(route('inquiries.store'), [
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'subject' => 'Inquiry Test',
            // 'message' and 'consent' omitted
        ]);

        $response->assertSessionHasErrors(['message', 'consent']);
        $this->assertEquals(0, Inquiry::count());
        $this->assertEquals(0, Lead::count());
        $this->assertEquals(0, LeadActivity::count());
    }

    /**
     * 5. Customer hanya dapat melihat inquiry miliknya.
     */
    public function test_customer_can_view_own_inquiries_list_and_detail(): void
    {
        $customer = User::factory()->customer()->create();
        $inquiry = Inquiry::factory()->create([
            'user_id' => $customer->id,
            'subject' => 'Inquiry Pribadi Customer A',
        ]);

        // Customer can view inquiries listing
        $indexResponse = $this->actingAs($customer)->get(route('customer.inquiries.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Inquiry Pribadi Customer A');

        // Customer can view inquiry detail
        $showResponse = $this->actingAs($customer)->get(route('customer.inquiries.show', $inquiry));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Inquiry Pribadi Customer A');
    }

    /**
     * 6. Customer tidak dapat melihat inquiry milik pengguna lain (403 Forbidden).
     */
    public function test_customer_cannot_view_another_customer_inquiry(): void
    {
        $customerA = User::factory()->customer()->create();
        $customerB = User::factory()->customer()->create();

        $inquiryB = Inquiry::factory()->create([
            'user_id' => $customerB->id,
            'subject' => 'Inquiry Rahasia Milik Customer B',
        ]);

        // Customer A attempts to access Customer B's inquiry by ID
        $response = $this->actingAs($customerA)->get(route('customer.inquiries.show', $inquiryB));

        $response->assertStatus(403);
    }

    /**
     * 7. Sales Executive hanya dapat melihat lead yang menjadi kewenangannya.
     */
    public function test_sales_executive_can_only_view_assigned_leads(): void
    {
        $salesA = User::factory()->withRole('Sales Executive')->create(['name' => 'Sales A']);
        $salesB = User::factory()->withRole('Sales Executive')->create(['name' => 'Sales B']);

        $leadAssignedToA = Lead::factory()->create([
            'lead_number' => 'LD-ASSIGNED-A',
            'assigned_to' => $salesA->id,
        ]);

        $leadAssignedToB = Lead::factory()->create([
            'lead_number' => 'LD-ASSIGNED-B',
            'assigned_to' => $salesB->id,
        ]);

        // Sales A views CRM index -> sees only leadAssignedToA
        $response = $this->actingAs($salesA)->get(route('admin.crm.index'));
        $response->assertStatus(200);
        $response->assertSee('LD-ASSIGNED-A');
        $response->assertDontSee('LD-ASSIGNED-B');

        // Sales A tries to view detail of leadAssignedToB -> 403 Forbidden
        $unauthorizedShow = $this->actingAs($salesA)->get(route('admin.leads.show', $leadAssignedToB));
        $unauthorizedShow->assertStatus(403);

        // Sales A views own lead -> 200 OK
        $authorizedShow = $this->actingAs($salesA)->get(route('admin.leads.show', $leadAssignedToA));
        $authorizedShow->assertStatus(200);
    }

    /**
     * 8. Staf tanpa permission tidak dapat mengakses CRM.
     */
    public function test_staff_without_permission_cannot_access_crm(): void
    {
        // Content Manager does NOT have leads.view permission
        $contentManager = User::factory()->withRole('Content Manager')->create();
        $customer = User::factory()->customer()->create();

        $this->actingAs($contentManager)->get(route('admin.crm.index'))->assertStatus(403);
        $this->actingAs($customer)->get(route('admin.crm.index'))->assertStatus(403);
    }

    /**
     * 9. Status lead berubah dengan validasi yang benar.
     */
    public function test_lead_status_updates_with_validation(): void
    {
        $salesManager = User::factory()->withRole('Sales Manager')->create();
        $lead = Lead::factory()->create(['status' => 'new']);

        $response = $this->actingAs($salesManager)->put(route('admin.leads.status.update', $lead), [
            'status' => 'contacted',
            'note' => 'Sudah ditelepon via WhatsApp',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('contacted', $lead->fresh()->status);

        // Transition from lost to deal requires note
        $lead->update(['status' => 'lost']);
        $invalidTransition = $this->actingAs($salesManager)->put(route('admin.leads.status.update', $lead), [
            'status' => 'deal',
            'note' => '', // note empty
        ]);
        $invalidTransition->assertSessionHasErrors('note');
    }

    /**
     * 10. Perubahan status tercatat di riwayat aktivitas.
     */
    public function test_lead_status_change_is_recorded_in_activity_log(): void
    {
        $salesManager = User::factory()->withRole('Sales Manager')->create();
        $lead = Lead::factory()->create(['status' => 'new']);

        $this->actingAs($salesManager)->put(route('admin.leads.status.update', $lead), [
            'status' => 'interested',
            'note' => 'Customer tertarik dengan paket kredit 3 tahun.',
        ]);

        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'user_id' => $salesManager->id,
            'activity_type' => 'status_changed',
            'previous_status' => 'new',
            'new_status' => 'interested',
        ]);
    }

    /**
     * 11. Assignment lead tercatat dan customer tidak dapat ditugaskan sebagai sales.
     */
    public function test_lead_assignment_is_logged_and_customer_cannot_be_assigned(): void
    {
        $salesManager = User::factory()->withRole('Sales Manager')->create();
        $salesExec = User::factory()->withRole('Sales Executive')->create(['name' => 'Beni Sales']);
        $customer = User::factory()->customer()->create();

        $lead = Lead::factory()->create(['assigned_to' => null]);

        // Attempt assignment to a customer -> rejected
        $invalidAssign = $this->actingAs($salesManager)->put(route('admin.leads.assign', $lead), [
            'assigned_to' => $customer->id,
        ]);
        $invalidAssign->assertSessionHasErrors('assigned_to');

        // Valid assignment to Sales Executive
        $validAssign = $this->actingAs($salesManager)->put(route('admin.leads.assign', $lead), [
            'assigned_to' => $salesExec->id,
        ]);
        $validAssign->assertSessionHas('success');

        $this->assertEquals($salesExec->id, $lead->fresh()->assigned_to);

        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'user_id' => $salesManager->id,
            'activity_type' => 'assigned',
        ]);
    }

    /**
     * 12. Catatan internal tidak terlihat oleh customer.
     */
    public function test_internal_notes_are_not_visible_to_customer(): void
    {
        $customer = User::factory()->customer()->create();
        $vehicle = Vehicle::factory()->create();

        $inquiry = Inquiry::factory()->create([
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'message' => 'Pesan dari customer',
        ]);

        $lead = Lead::factory()->withInquiry($inquiry)->create([
            'notes' => 'Catatan Rahasia Staf: Customer memiliki budget ketat, tawarkan diskon khusus akhir bulan.',
        ]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => User::factory()->withRole('Sales Manager')->create()->id,
            'activity_type' => 'follow_up_note',
            'description' => 'Aktivitas Internal Staf yang Sangat Rahasia',
            'created_at' => now(),
        ]);

        // Customer views own inquiry detail
        $response = $this->actingAs($customer)->get(route('customer.inquiries.show', $inquiry));

        $response->assertStatus(200);
        $response->assertSee('Pesan dari customer');
        $response->assertDontSee('Catatan Rahasia Staf');
        $response->assertDontSee('Aktivitas Internal Staf yang Sangat Rahasia');
    }

    /**
     * 13. Filter dan pagination CRM berfungsi.
     */
    public function test_crm_filtering_and_search_work(): void
    {
        $salesManager = User::factory()->withRole('Sales Manager')->create();

        $customerA = User::factory()->customer()->create(['name' => 'Target Customer Alpha']);
        $leadA = Lead::factory()->create([
            'customer_id' => $customerA->id,
            'status' => 'new',
            'lead_number' => 'LD-ALPHA-001',
        ]);

        $customerB = User::factory()->customer()->create(['name' => 'Another Customer Beta']);
        $vehicleB = Vehicle::factory()->create(['name' => 'Toyota Kijang Innova']);
        $leadB = Lead::factory()->create([
            'customer_id' => $customerB->id,
            'vehicle_id' => $vehicleB->id,
            'status' => 'deal',
            'lead_number' => 'LD-BETA-002',
        ]);

        // Search by keyword "Alpha"
        $searchResp = $this->actingAs($salesManager)->get(route('admin.crm.index', ['q' => 'Alpha']));
        $searchResp->assertStatus(200);
        $searchResp->assertSee('LD-ALPHA-001');
        $searchResp->assertDontSee('LD-BETA-002');

        // Filter by status "deal"
        $statusResp = $this->actingAs($salesManager)->get(route('admin.crm.index', ['status' => 'deal']));
        $statusResp->assertStatus(200);
        $statusResp->assertSee('LD-BETA-002');
        $statusResp->assertDontSee('LD-ALPHA-001');
    }

    /**
     * 14. Dashboard menghitung data live dari database.
     */
    public function test_customer_dashboard_counts_live_database_figures(): void
    {
        $customer = User::factory()->customer()->create();

        Inquiry::factory()->create(['user_id' => $customer->id, 'status' => 'new']);
        Inquiry::factory()->create(['user_id' => $customer->id, 'status' => 'in_progress']);
        Inquiry::factory()->create(['user_id' => $customer->id, 'status' => 'answered']);

        // Another customer's inquiry should NOT be counted
        $otherCustomer = User::factory()->customer()->create();
        Inquiry::factory()->create(['user_id' => $otherCustomer->id, 'status' => 'new']);

        $response = $this->actingAs($customer)->get(route('customer.dashboard'));

        $response->assertStatus(200);
        // Total inquiries: 3, In progress: 2, Resolved: 1
        $response->assertSee('Total Permintaan Diajukan');
        $response->assertViewHas('stats', [
            'totalInquiries' => 3,
            'inProgressInquiries' => 2,
            'resolvedInquiries' => 1,
        ]);
    }

    /**
     * 15. Website publik, katalog, login, dan registrasi tetap berfungsi tanpa gangguan.
     */
    public function test_public_website_and_auth_features_remain_functional(): void
    {
        $vehicle = Vehicle::factory()->create([
            'slug' => 'toyota-innova-zenix-tahap4',
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('home'))->assertStatus(200);
        $this->get(route('vehicles.index'))->assertStatus(200);
        $this->get(route('vehicles.show', $vehicle->slug))->assertStatus(200);
        $this->get(route('login'))->assertStatus(200);
        $this->get(route('register'))->assertStatus(200);
    }
}
