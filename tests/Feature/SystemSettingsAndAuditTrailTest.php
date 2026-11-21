<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\SalesOpportunity;
use App\Models\ServiceBooking;
use App\Models\ServiceType;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use App\Models\VehicleCategory;
use App\Services\AuditTrailService;
use App\Services\SettingService;
use Database\Seeders\MasterDataAndSettingsSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingsAndAuditTrailTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $dealerManager;
    protected User $salesExecutive;
    protected User $customer;
    protected Branch $branchA;
    protected Branch $branchB;
    protected Vehicle $vehicle;
    protected VehicleCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataAndSettingsSeeder::class);

        $this->branchA = Branch::create([
            'code' => 'JKT-01',
            'name' => 'AutoConnect Jakarta Pusat',
            'slug' => 'autoconnect-jakarta-pusat',
            'city' => 'Jakarta Pusat',
            'address' => 'Jl. M.H. Thamrin No. 10',
            'phone' => '021-3900001',
            'email' => 'jakpus@toyota-autoconnect.id',
            'is_active' => true,
        ]);

        $this->branchB = Branch::create([
            'code' => 'BDG-01',
            'name' => 'AutoConnect Bandung Asia Afrika',
            'slug' => 'autoconnect-bandung-asia-afrika',
            'city' => 'Bandung',
            'address' => 'Jl. Asia Afrika No. 45',
            'phone' => '022-4200001',
            'email' => 'bandung@toyota-autoconnect.id',
            'is_active' => true,
        ]);

        $this->superAdmin = User::create([
            'name' => 'Super Administrator',
            'email' => 'admin@toyota-autoconnect.id',
            'phone' => '08110000001',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $this->superAdmin->assignRole('Super Admin');

        $this->dealerManager = User::create([
            'name' => 'Bambang Manager',
            'email' => 'manager.jakpus@toyota-autoconnect.id',
            'phone' => '08110000002',
            'password' => bcrypt('password123'),
            'is_active' => true,
            'branch_id' => $this->branchA->id,
        ]);
        $this->dealerManager->assignRole('Dealer Manager');

        $this->salesExecutive = User::create([
            'name' => 'Andi Sales',
            'email' => 'andi.sales@toyota-autoconnect.id',
            'phone' => '08110000003',
            'password' => bcrypt('password123'),
            'is_active' => true,
            'branch_id' => $this->branchA->id,
        ]);
        $this->salesExecutive->assignRole('Sales Executive');

        $this->customer = User::create([
            'name' => 'Budi Customer',
            'email' => 'budi.cust@gmail.com',
            'phone' => '081299990001',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $this->customer->assignRole('Customer');

        $this->category = VehicleCategory::create([
            'name' => 'SUV Premium',
            'slug' => 'suv-premium',
            'description' => 'Sport Utility Vehicle tangguh dengan kenyamanan premium',
            'is_active' => true,
        ]);

        $this->vehicle = Vehicle::create([
            'name' => 'All New Fortuner GR Sport',
            'slug' => 'all-new-fortuner-gr-sport',
            'category' => 'SUV Premium',
            'category_id' => $this->category->id,
            'variant' => '2.8 GR Sport 4x4 A/T',
            'price' => 740_000_000,
            'status' => 'available',
            'publication_status' => 'published',
        ]);
    }

    /**
     * 1. Super Admin dapat mengakses system settings.
     */
    public function test_01_super_admin_can_access_system_settings(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.settings.index');
        $response->assertSee('Toyota AutoConnect');
        $response->assertSee('General Settings');
        $response->assertSee('Business Settings');
        $response->assertSee('Booking Settings');
        $response->assertSee('Notification Settings');
        $response->assertSee('Payment Settings');
    }

    /**
     * 2. Role lain tidak dapat mengakses pengaturan global tanpa permission.
     */
    public function test_02_unauthorized_roles_cannot_access_or_modify_system_settings(): void
    {
        // Customer forbidden
        $this->actingAs($this->customer)
            ->get(route('admin.settings.index'))
            ->assertStatus(403);

        // Sales Executive forbidden
        $this->actingAs($this->salesExecutive)
            ->get(route('admin.settings.index'))
            ->assertStatus(403);

        // Dealer Manager without settings.manage forbidden
        $this->actingAs($this->dealerManager)
            ->get(route('admin.settings.index'))
            ->assertStatus(403);

        // Unauthorized POST attempt
        $this->actingAs($this->customer)
            ->post(route('admin.settings.update'), ['group' => 'general', 'app_name' => 'Hacked App'])
            ->assertStatus(403);
    }

    /**
     * 3. Pengaturan dapat disimpan dan diperbarui.
     */
    public function test_03_settings_can_be_updated_and_persisted_with_cache_invalidation(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.settings.update'), [
            'group' => 'general',
            'app_name' => 'Toyota AutoConnect Indonesia New',
            'app_email' => 'contact@toyota-autoconnect.id',
            'app_phone' => '021-99887766',
            'head_office_address' => 'Gedung Astra Lt. 25, Sudirman, Jakarta',
            'timezone' => 'Asia/Jakarta',
            'date_format' => 'd F Y',
            'currency_format' => 'IDR (Rp)',
            'locale' => 'id',
            'app_description' => 'Platform resmi pemesanan Toyota',
        ]);

        $response->assertRedirect(route('admin.settings.index', ['tab' => 'general']));
        $response->assertSessionHas('success');

        // Check database
        $this->assertEquals('Toyota AutoConnect Indonesia New', Setting::get('app_name'));
        $this->assertEquals('d F Y', Setting::get('date_format'));

        // Check setting service
        $settingService = app(SettingService::class);
        $this->assertEquals('Toyota AutoConnect Indonesia New', $settingService->get('app_name'));
    }

    /**
     * 4. Pengaturan default tidak mengubah transaksi yang sudah ada.
     */
    public function test_04_updating_default_booking_fee_does_not_mutate_existing_bookings(): void
    {
        // 1. Create opportunity & existing booking with 5.000.000 booking fee
        $opportunity = SalesOpportunity::create([
            'opportunity_number' => 'OPP-001',
            'code' => 'OPP-001',
            'user_id' => $this->customer->id,
            'branch_id' => $this->branchA->id,
            'vehicle_id' => $this->vehicle->id,
            'assigned_to' => $this->salesExecutive->id,
            'stage' => 'booking_fee_paid',
            'estimated_value' => 740_000_000,
        ]);

        $booking = VehicleBooking::create([
            'booking_code' => 'SPK-202611-0001',
            'code' => 'SPK-202611-0001',
            'customer_id' => $this->customer->id,
            'user_id' => $this->customer->id,
            'branch_id' => $this->branchA->id,
            'vehicle_id' => $this->vehicle->id,
            'sales_opportunity_id' => $opportunity->id,
            'booking_date' => now()->toDateString(),
            'vehicle_price' => 740_000_000,
            'booking_fee' => 5_000_000,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        // 2. Super Admin updates default booking fee in settings to 10.000.000
        $this->actingAs($this->superAdmin)->post(route('admin.settings.update'), [
            'group' => 'booking',
            'default_booking_fee' => 10_000_000,
            'reservation_duration_days' => 5,
            'booking_payment_timeout_hours' => 48,
            'cancellation_terms' => 'Syarat pembatalan baru.',
            'test_drive_slot_duration_minutes' => 60,
            'test_drive_max_daily_slots' => 10,
            'service_max_daily_capacity' => 15,
        ]);

        $this->assertEquals(10_000_000, Setting::get('default_booking_fee'));

        // 3. Verify existing booking fee has NOT mutated
        $booking->refresh();
        $this->assertEquals(5_000_000, (int) $booking->booking_fee);
    }

    /**
     * 5. Master data dapat dibuat dan diperbarui.
     */
    public function test_05_master_data_service_types_and_lead_sources_can_be_managed(): void
    {
        // 1. Create Service Type
        $resSt = $this->actingAs($this->superAdmin)->post(route('admin.service-types.store'), [
            'name' => 'Spooring & Balancing Presisi 3D',
            'code' => 'SRV-SPOORING-3D',
            'category' => 'regular_maintenance',
            'estimated_duration_minutes' => 60,
            'estimated_cost' => 350_000,
            'description' => 'Penyelarasan sudut roda kemudi komputerisasi',
            'is_active' => true,
        ]);
        $resSt->assertSessionHas('success');
        $this->assertDatabaseHas('service_types', ['code' => 'SRV-SPOORING-3D']);

        // 2. Update Service Type
        $st = ServiceType::where('code', 'SRV-SPOORING-3D')->first();
        $resUpdate = $this->actingAs($this->superAdmin)->put(route('admin.service-types.update', $st), [
            'name' => 'Spooring & Balancing 3D Laser Toyota',
            'code' => 'SRV-SPOORING-3D',
            'category' => 'quick_service',
            'estimated_duration_minutes' => 45,
            'estimated_cost' => 400_000,
            'is_active' => true,
        ]);
        $resUpdate->assertSessionHas('success');
        $this->assertDatabaseHas('service_types', [
            'id' => $st->id,
            'name' => 'Spooring & Balancing 3D Laser Toyota',
            'estimated_cost' => 400_000,
        ]);

        // 3. Create Lead Source
        $resLs = $this->actingAs($this->superAdmin)->post(route('admin.lead-sources.store'), [
            'name' => 'IIMS Auto Show 2026',
            'code' => 'iims_2026',
            'description' => 'Booth pameran JIExpo Kemayoran',
            'is_active' => true,
        ]);
        $resLs->assertSessionHas('success');
        $this->assertDatabaseHas('lead_sources', ['code' => 'iims_2026']);
    }

    /**
     * 6. Data master yang sudah digunakan tidak dapat dihapus secara sembarangan.
     */
    public function test_06_master_data_with_dependent_relations_cannot_be_deleted(): void
    {
        // Create service type
        $serviceType = ServiceType::create([
            'name' => 'Ganti Oli Synthetic 5W-30',
            'code' => 'SRV-OIL-5W30',
            'category' => 'quick_service',
            'estimated_duration_minutes' => 30,
            'estimated_cost' => 500_000,
            'is_active' => true,
        ]);

        // Attach booking to service type
        ServiceBooking::create([
            'booking_code' => 'SRV-BK-999',
            'user_id' => $this->customer->id,
            'branch_id' => $this->branchA->id,
            'vehicle_name' => 'Innova Zenix',
            'license_plate' => 'B 9999 TOY',
            'service_type_id' => $serviceType->id,
            'booking_date' => now()->toDateString(),
            'booking_time' => '10:00',
            'status' => 'confirmed',
        ]);

        // Attempt deletion of service type with booking
        $response = $this->actingAs($this->superAdmin)->delete(route('admin.service-types.destroy', $serviceType));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('service_types', ['id' => $serviceType->id]);

        // Attempt deletion of Category with vehicles attached
        $resCat = $this->actingAs($this->superAdmin)->delete(route('admin.categories.destroy', $this->category));
        $resCat->assertSessionHas('error');
        $this->assertDatabaseHas('vehicle_categories', ['id' => $this->category->id]);
    }

    /**
     * 7. Dealer hanya dapat mengelola data sesuai kewenangannya.
     */
    public function test_07_dealer_manager_audit_log_access_is_scoped(): void
    {
        // Create audit logs: 1 by staff from branch A, 1 by superAdmin, 1 by staff from branch B
        $staffBranchB = User::create([
            'name' => 'Staff Bandung',
            'email' => 'staff.bdg@toyota-autoconnect.id',
            'phone' => '08220000001',
            'password' => bcrypt('password123'),
            'is_active' => true,
            'branch_id' => $this->branchB->id,
        ]);
        $staffBranchB->assignRole('Sales Executive');

        $auditService = app(AuditTrailService::class);
        $logA = $auditService->log(
            action: 'created',
            module: 'crm',
            description: 'Lead dibuat di cabang Jakarta',
            actor: $this->salesExecutive
        );

        $logB = $auditService->log(
            action: 'created',
            module: 'crm',
            description: 'Lead dibuat di cabang Bandung',
            actor: $staffBranchB
        );

        // Give audit.view temporarily to dealerManager to check scoping
        $this->dealerManager->givePermissionTo('audit.view');

        $response = $this->actingAs($this->dealerManager)->get(route('admin.audit-logs.index'));
        $response->assertStatus(200);

        $logs = $response->viewData('auditLogs');
        $this->assertTrue($logs->contains('id', $logA->id));
        $this->assertFalse($logs->contains('id', $logB->id));

        // Attempt accessing single detail of other branch log
        $this->actingAs($this->dealerManager)
            ->get(route('admin.audit-logs.show', $logB))
            ->assertStatus(403);
    }

    /**
     * 8. Audit trail tercatat ketika data dibuat.
     */
    public function test_08_audit_trail_recorded_on_master_data_creation(): void
    {
        $this->actingAs($this->superAdmin)->post(route('admin.branches.store'), [
            'name' => 'AutoConnect Surabaya Basuki Rahmat',
            'code' => 'AT-SBY-01',
            'city' => 'Surabaya',
            'address' => 'Jl. Basuki Rahmat No. 100',
            'phone' => '031-5500001',
            'email' => 'surabaya@toyota-autoconnect.id',
            'opening_time' => '08:00',
            'closing_time' => '17:00',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('branches', ['code' => 'AT-SBY-01']);

        // Check audit log
        $this->assertDatabaseHas('audit_logs', [
            'module' => 'branches',
            'action' => 'created',
            'user_id' => $this->superAdmin->id,
        ]);
    }

    /**
     * 9. Audit trail mencatat perubahan sebelum dan sesudah.
     */
    public function test_09_audit_trail_records_old_and_new_values_diff(): void
    {
        $branch = Branch::create([
            'code' => 'MLG-01',
            'name' => 'AutoConnect Malang',
            'slug' => 'autoconnect-malang',
            'city' => 'Malang',
            'address' => 'Jl. Ijen No. 12',
            'phone' => '0341-3300001',
            'is_active' => true,
        ]);

        $this->actingAs($this->superAdmin)->put(route('admin.branches.update', $branch), [
            'name' => 'AutoConnect Malang Ijen Boulevard',
            'code' => 'MLG-01',
            'city' => 'Malang Kota',
            'address' => 'Jl. Ijen No. 12-14',
            'phone' => '0341-3300002',
            'email' => 'malang@toyota-autoconnect.id',
            'is_active' => true,
        ]);

        $log = AuditLog::where('module', 'branches')
            ->where('action', 'updated')
            ->where('auditable_id', $branch->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('AutoConnect Malang', $log->old_values['name']);
        $this->assertEquals('AutoConnect Malang Ijen Boulevard', $log->new_values['name']);
    }

    /**
     * 10. Audit trail tidak menyimpan password atau kredensial sensitif.
     */
    public function test_10_audit_trail_masks_and_protects_sensitive_keys(): void
    {
        $auditService = app(AuditTrailService::class);

        $payload = [
            'username' => 'superadmin',
            'password' => 'secret_password_123',
            'server_key' => 'SB-Mid-server-SECRETKEY',
            'snap_token' => 'token-abc-123',
            'regular_field' => 'Normal Data',
        ];

        $log = $auditService->log(
            action: 'updated',
            module: 'system_settings',
            description: 'Test sensitive data masking',
            newValues: $payload,
            actor: $this->superAdmin
        );

        $this->assertNotNull($log);
        $this->assertEquals('[PROTECTED]', $log->new_values['password']);
        $this->assertEquals('[PROTECTED]', $log->new_values['server_key']);
        $this->assertEquals('[PROTECTED]', $log->new_values['snap_token']);
        $this->assertEquals('Normal Data', $log->new_values['regular_field']);
    }

    /**
     * 11. Audit log tidak dapat diubah oleh pengguna biasa.
     */
    public function test_11_audit_log_is_immutable_and_cannot_be_modified_or_deleted(): void
    {
        $auditService = app(AuditTrailService::class);
        $log = $auditService->log('login', 'auth', 'Audit test log', null, null, null, $this->superAdmin);

        // Check model property: $timestamps is false and no updated_at
        $this->assertFalse($log->timestamps);

        // Ensure no delete route exists for audit logs
        $response = $this->actingAs($this->superAdmin)->delete('/admin/audit-logs/' . $log->id);
        $this->assertTrue(in_array($response->status(), [404, 405], true));
    }

    /**
     * 12. Filter audit log bekerja dengan benar.
     */
    public function test_12_audit_log_filters_by_module_action_and_date_range(): void
    {
        $auditService = app(AuditTrailService::class);

        // Seed 3 distinct logs
        $log1 = $auditService->log('login', 'auth', 'Login session', null, null, null, $this->superAdmin);
        $log2 = $auditService->log('created', 'branches', 'Branch created', null, null, null, $this->superAdmin);
        $log3 = $auditService->log('settings_updated', 'system_settings', 'Settings changed', null, null, null, $this->superAdmin);

        // Filter by module = system_settings
        $resModule = $this->actingAs($this->superAdmin)->get(route('admin.audit-logs.index', ['module' => 'system_settings']));
        $resModule->assertStatus(200);
        $viewLogs = $resModule->viewData('auditLogs');
        $this->assertTrue($viewLogs->contains('id', $log3->id));
        $this->assertFalse($viewLogs->contains('id', $log1->id));

        // Filter by action = login
        $resAction = $this->actingAs($this->superAdmin)->get(route('admin.audit-logs.index', ['action' => 'login']));
        $resAction->assertStatus(200);
        $viewActionLogs = $resAction->viewData('auditLogs');
        $this->assertTrue($viewActionLogs->contains('id', $log1->id));
        $this->assertFalse($viewActionLogs->contains('id', $log2->id));
    }

    /**
     * 13. Data audit log tidak dapat diakses oleh role yang tidak berwenang.
     */
    public function test_13_unauthorized_roles_receive_forbidden_on_audit_logs(): void
    {
        $this->actingAs($this->customer)
            ->get(route('admin.audit-logs.index'))
            ->assertStatus(403);

        $this->actingAs($this->salesExecutive)
            ->get(route('admin.audit-logs.index'))
            ->assertStatus(403);

        // Without audit.view, dealer manager is forbidden
        $this->actingAs($this->dealerManager)
            ->get(route('admin.audit-logs.index'))
            ->assertStatus(403);
    }
}

