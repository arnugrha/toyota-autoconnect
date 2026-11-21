<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Inquiry;
use App\Models\Payment;
use App\Models\PaymentNotification;
use App\Models\SalesOpportunity;
use App\Models\ServiceBooking;
use App\Models\ServiceType;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use App\Models\VehicleCategory;
use App\Models\VehicleInventory;
use App\Services\Payment\MidtransService;
use App\Services\SettingService;
use Database\Seeders\MasterDataAndSettingsSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SecurityHardeningAndPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $dealerManager;
    protected User $salesExecutive;
    protected User $customerA;
    protected User $customerB;
    protected Branch $branch;
    protected Vehicle $vehicle;
    protected VehicleCategory $category;
    protected ServiceType $serviceType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataAndSettingsSeeder::class);

        $this->branch = Branch::create([
            'code' => 'JKT-01',
            'name' => 'Toyota AutoConnect Sudirman',
            'slug' => 'toyota-autoconnect-sudirman',
            'city' => 'Jakarta Pusat',
            'address' => 'Jl. Jend. Sudirman Kav. 1',
            'phone' => '021-5700001',
            'email' => 'sudirman@toyota-autoconnect.id',
            'is_active' => true,
        ]);

        $this->superAdmin = User::create([
            'name' => 'Sec Super Administrator',
            'email' => 'sec.superadmin@example.com',
            'phone' => '08110009901',
            'password' => bcrypt('Password123!'),
            'is_active' => true,
        ]);
        $this->superAdmin->assignRole('Super Admin');

        $this->dealerManager = User::create([
            'name' => 'Sec Dealer Manager Sudirman',
            'email' => 'sec.manager.sudirman@example.com',
            'phone' => '08110009902',
            'password' => bcrypt('Password123!'),
            'is_active' => true,
            'branch_id' => $this->branch->id,
        ]);
        $this->dealerManager->assignRole('Dealer Manager');

        $this->salesExecutive = User::create([
            'name' => 'Sec Sales Executive Budi',
            'email' => 'sec.sales.budi@example.com',
            'phone' => '08110009903',
            'password' => bcrypt('Password123!'),
            'is_active' => true,
            'branch_id' => $this->branch->id,
        ]);
        $this->salesExecutive->assignRole('Sales Executive');

        $this->customerA = User::create([
            'name' => 'Customer Ahmad',
            'email' => 'sec.customer.ahmad@example.com',
            'phone' => '08129999901',
            'password' => bcrypt('Password123!'),
            'is_active' => true,
        ]);
        $this->customerA->assignRole('Customer');

        $this->customerB = User::create([
            'name' => 'Customer Bambang',
            'email' => 'sec.customer.bambang@example.com',
            'phone' => '08129999902',
            'password' => bcrypt('Password123!'),
            'is_active' => true,
        ]);
        $this->customerB->assignRole('Customer');

        $this->category = VehicleCategory::create([
            'name' => 'Hybrid EV',
            'slug' => 'hybrid-ev',
            'description' => 'Kendaraan ramah lingkungan berteknologi elektrifikasi Toyota.',
            'is_active' => true,
        ]);

        $this->vehicle = Vehicle::create([
            'name' => 'All New Kijang Innova Zenix HEV',
            'slug' => 'all-new-kijang-innova-zenix-hev',
            'category' => 'Hybrid EV',
            'category_id' => $this->category->id,
            'variant' => '2.0 Q HEV Modellista',
            'price' => 630_000_000,
            'status' => 'available',
            'is_featured' => true,
            'published_at' => now(),
        ]);

        $this->serviceType = ServiceType::create([
            'name' => 'Servis Berkala 10.000 KM',
            'code' => 'SB-10K',
            'category' => 'periodic',
            'estimated_duration_minutes' => 90,
            'estimated_cost' => 850_000,
            'is_active' => true,
        ]);
    }

    /**
     * 1. Test HTTP Security Headers are properly attached on all web responses.
     */
    public function test_http_responses_include_all_required_security_headers(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');
        $this->assertFalse($response->headers->has('X-Powered-By'));
    }

    /**
     * 2. Test Customer Data Isolation: Customer B cannot view Customer A's vehicle booking.
     */
    public function test_customer_cannot_view_another_customer_vehicle_booking(): void
    {
        $opportunity = SalesOpportunity::create([
            'code' => SalesOpportunity::generateCode(),
            'lead_id' => null,
            'user_id' => $this->customerA->id,
            'vehicle_id' => $this->vehicle->id,
            'branch_id' => $this->branch->id,
            'assigned_to' => $this->salesExecutive->id,
            'stage' => 'booking',
            'estimated_value' => $this->vehicle->price,
        ]);

        $booking = VehicleBooking::create([
            'code' => VehicleBooking::generateCode(),
            'sales_opportunity_id' => $opportunity->id,
            'customer_id' => $this->customerA->id,
            'vehicle_id' => $this->vehicle->id,
            'branch_id' => $this->branch->id,
            'vehicle_price' => $this->vehicle->price,
            'discount_amount' => 0,
            'booking_fee' => 5_000_000,
            'booking_date' => now()->toDateString(),
            'expires_at' => now()->addDays(7),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'created_by' => $this->salesExecutive->id,
        ]);

        // Customer A can access their own booking
        $this->actingAs($this->customerA)
            ->get(route('customer.bookings.show', $booking))
            ->assertStatus(200);

        // Customer B cannot access Customer A's booking (403 Forbidden)
        $this->actingAs($this->customerB)
            ->get(route('customer.bookings.show', $booking))
            ->assertStatus(403);
    }

    /**
     * 3. Test Customer Data Isolation: Customer B cannot view Customer A's inquiry.
     */
    public function test_customer_cannot_view_another_customer_inquiry(): void
    {
        $inquiry = Inquiry::create([
            'user_id' => $this->customerA->id,
            'vehicle_id' => $this->vehicle->id,
            'customer_name' => $this->customerA->name,
            'customer_email' => $this->customerA->email,
            'customer_phone' => $this->customerA->phone,
            'subject' => 'Pertanyaan Ketersediaan Innova Zenix',
            'message' => 'Pertanyaan mengenai ketersediaan unit Innova Zenix.',
            'status' => 'new',
            'submitted_at' => now(),
        ]);

        // Customer A can view their inquiry
        $this->actingAs($this->customerA)
            ->get(route('customer.inquiries.show', $inquiry))
            ->assertStatus(200);

        // Customer B cannot view Customer A's inquiry (403 Forbidden)
        $this->actingAs($this->customerB)
            ->get(route('customer.inquiries.show', $inquiry))
            ->assertStatus(403);
    }

    /**
     * 4. Test Customer Data Isolation: Customer B cannot view Customer A's support ticket.
     */
    public function test_customer_cannot_view_another_customer_support_ticket(): void
    {
        $ticket = SupportTicket::create([
            'ticket_number' => SupportTicket::generateNumber(),
            'user_id' => $this->customerA->id,
            'branch_id' => $this->branch->id,
            'subject' => 'Kendala Aplikasi Test Drive',
            'category' => 'technical',
            'priority' => 'medium',
            'status' => 'open',
            'description' => 'Mohon bantuan terkait jadwal test drive yang belum terkonfirmasi.',
        ]);

        // Customer A can view their ticket
        $this->actingAs($this->customerA)
            ->get(route('customer.tickets.show', $ticket))
            ->assertStatus(200);

        // Customer B cannot view Customer A's ticket (403 Forbidden)
        $this->actingAs($this->customerB)
            ->get(route('customer.tickets.show', $ticket))
            ->assertStatus(403);
    }

    /**
     * 5. Test Customer Data Isolation: Customer B cannot view Customer A's service booking.
     */
    public function test_customer_cannot_view_another_customer_service_booking(): void
    {
        $serviceBooking = ServiceBooking::create([
            'booking_code' => ServiceBooking::generateCode(),
            'user_id' => $this->customerA->id,
            'branch_id' => $this->branch->id,
            'vehicle_id' => $this->vehicle->id,
            'vehicle_name' => $this->vehicle->name,
            'license_plate' => 'B 1234 TOY',
            'current_mileage' => 10000,
            'service_type_id' => $this->serviceType->id,
            'booking_date' => now()->addDays(2)->toDateString(),
            'booking_time' => '09:00',
            'status' => 'pending',
        ]);

        // Customer A can view their service booking
        $this->actingAs($this->customerA)
            ->get(route('customer.service-bookings.show', $serviceBooking))
            ->assertStatus(200);

        // Customer B cannot view Customer A's service booking (403 Forbidden)
        $this->actingAs($this->customerB)
            ->get(route('customer.service-bookings.show', $serviceBooking))
            ->assertStatus(403);
    }

    /**
     * 6. Test RBAC: Unauthenticated guests are redirected to login.
     */
    public function test_unauthenticated_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
        $this->get('/customer/dashboard')->assertRedirect(route('login'));
        $this->get('/admin/settings')->assertRedirect(route('login'));
    }

    /**
     * 7. Test RBAC: Customer role cannot access admin dashboard or settings.
     */
    public function test_customer_role_cannot_access_admin_dashboard_or_settings(): void
    {
        $this->actingAs($this->customerA)
            ->get(route('admin.dashboard'))
            ->assertStatus(403);

        $this->actingAs($this->customerA)
            ->get(route('admin.settings.index'))
            ->assertStatus(403);
    }

    /**
     * 8. Test RBAC: Dealer Manager cannot access Super Admin system settings.
     */
    public function test_dealer_manager_cannot_access_super_admin_system_settings(): void
    {
        $this->actingAs($this->dealerManager)
            ->get(route('admin.settings.index'))
            ->assertStatus(403);

        $this->actingAs($this->superAdmin)
            ->get(route('admin.settings.index'))
            ->assertStatus(200);
    }

    /**
     * 9. Test Midtrans Webhook: Reject invalid signature with 403 Forbidden.
     */
    public function test_midtrans_webhook_rejects_invalid_signature(): void
    {
        Config::set('midtrans.server_key', 'test-server-key-123');

        $opportunity = SalesOpportunity::create([
            'code' => SalesOpportunity::generateCode(),
            'user_id' => $this->customerA->id,
            'vehicle_id' => $this->vehicle->id,
            'branch_id' => $this->branch->id,
            'assigned_to' => $this->salesExecutive->id,
            'stage' => 'booking',
        ]);

        $booking = VehicleBooking::create([
            'code' => VehicleBooking::generateCode(),
            'sales_opportunity_id' => $opportunity->id,
            'customer_id' => $this->customerA->id,
            'vehicle_id' => $this->vehicle->id,
            'branch_id' => $this->branch->id,
            'vehicle_price' => $this->vehicle->price,
            'booking_fee' => 5_000_000,
            'booking_date' => now()->toDateString(),
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $payment = Payment::create([
            'code' => Payment::generateCode(),
            'vehicle_booking_id' => $booking->id,
            'user_id' => $this->customerA->id,
            'gateway' => 'midtrans',
            'order_id' => 'ORDER-TEST-INVALID-001',
            'amount' => 5_000_000,
            'status' => 'pending',
        ]);

        $payload = [
            'order_id' => $payment->order_id,
            'status_code' => '200',
            'gross_amount' => '5000000.00',
            'signature_key' => 'invalid-fake-signature-hash',
            'transaction_status' => 'settlement',
            'transaction_id' => 'midtrans-tx-001',
        ];

        $response = $this->postJson(route('midtrans.notification'), $payload);

        $response->assertStatus(403);
        $this->assertDatabaseHas('payment_notifications', [
            'order_id' => $payment->order_id,
            'processing_status' => 'signature_invalid',
        ]);
        $this->assertEquals('pending', $payment->fresh()->status);
    }

    /**
     * 10. Test Midtrans Webhook: Accept valid signature and process idempotently.
     */
    public function test_midtrans_webhook_processes_valid_signature_and_handles_duplicates(): void
    {
        $serverKey = 'test-server-key-production-ready';
        Config::set('midtrans.server_key', $serverKey);
        Config::set('services.midtrans.server_key', $serverKey);

        $opportunity = SalesOpportunity::create([
            'code' => SalesOpportunity::generateCode(),
            'user_id' => $this->customerA->id,
            'vehicle_id' => $this->vehicle->id,
            'branch_id' => $this->branch->id,
            'assigned_to' => $this->salesExecutive->id,
            'stage' => 'booking',
        ]);

        $booking = VehicleBooking::create([
            'code' => VehicleBooking::generateCode(),
            'sales_opportunity_id' => $opportunity->id,
            'customer_id' => $this->customerA->id,
            'vehicle_id' => $this->vehicle->id,
            'branch_id' => $this->branch->id,
            'vehicle_price' => $this->vehicle->price,
            'booking_fee' => 5_000_000,
            'booking_date' => now()->toDateString(),
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $orderId = 'ORDER-TEST-VALID-002';
        $payment = Payment::create([
            'code' => Payment::generateCode(),
            'vehicle_booking_id' => $booking->id,
            'user_id' => $this->customerA->id,
            'gateway' => 'midtrans',
            'order_id' => $orderId,
            'amount' => 5_000_000,
            'status' => 'pending',
        ]);

        $statusCode = '200';
        $grossAmount = '5000000.00';
        $validSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        $transactionId = 'midtrans-tx-valid-999';

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $validSignature,
            'transaction_status' => 'settlement',
            'transaction_id' => $transactionId,
            'payment_type' => 'bank_transfer',
        ];

        // First transmission: successfully processed
        $response1 = $this->postJson(route('midtrans.notification'), $payload);
        $response1->assertStatus(200);

        $this->assertEquals('paid', $payment->fresh()->status);
        $this->assertEquals('paid', $booking->fresh()->payment_status);

        // Second transmission (duplicate webhook replay): safely ignored via idempotency
        $response2 = $this->postJson(route('midtrans.notification'), $payload);
        $response2->assertStatus(200);

        $this->assertDatabaseHas('payment_notifications', [
            'order_id' => $orderId,
            'processing_status' => 'duplicate_ignored',
        ]);
    }

    /**
     * 11. Test Brute Force Login Rate Limiting.
     */
    public function test_login_rate_limiting_protects_against_brute_force(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'bruteforce@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // 6th attempt should be blocked by rate limiter
        $response = $this->post('/login', [
            'email' => 'bruteforce@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('Terlalu banyak percobaan', session('errors')->first('email'));
    }

    /**
     * 12. Test Race Condition / Concurrency: Inventory over-allocation prevention.
     */
    public function test_vehicle_booking_confirmation_locks_and_prevents_overselling(): void
    {
        // Setup inventory with 0 stock
        $inventory = VehicleInventory::create([
            'vehicle_id' => $this->vehicle->id,
            'branch_id' => $this->branch->id,
            'stock_quantity' => 1,
            'reserved_quantity' => 1, // Available = 0
            'minimum_stock_level' => 1,
        ]);

        $opportunity = SalesOpportunity::create([
            'code' => SalesOpportunity::generateCode(),
            'user_id' => $this->customerA->id,
            'vehicle_id' => $this->vehicle->id,
            'branch_id' => $this->branch->id,
            'assigned_to' => $this->salesExecutive->id,
            'stage' => 'booking',
        ]);

        $booking = VehicleBooking::create([
            'code' => VehicleBooking::generateCode(),
            'sales_opportunity_id' => $opportunity->id,
            'customer_id' => $this->customerA->id,
            'vehicle_id' => $this->vehicle->id,
            'branch_id' => $this->branch->id,
            'inventory_id' => $inventory->id,
            'vehicle_price' => $this->vehicle->price,
            'booking_fee' => 5_000_000,
            'booking_date' => now()->toDateString(),
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        // Attempt confirmation by Super Admin when stock is insufficient
        $response = $this->actingAs($this->superAdmin)
            ->put(route('admin.sales.bookings.confirm', $booking));

        $response->assertSessionHas('error');
        $this->assertStringContainsString('tidak mencukupi', session('error'));

        // Reserved quantity remains 1, does not oversell
        $this->assertEquals(1, $inventory->fresh()->reserved_quantity);
        $this->assertEquals('pending', $booking->fresh()->status);
    }

    /**
     * 13. Test Custom Production Error Views render without leaking internal paths or stack traces.
     */
    public function test_custom_error_views_exist_and_render_without_debug_traces(): void
    {
        $errorCodes = ['403', '404', '419', '429', '500', '503'];

        foreach ($errorCodes as $code) {
            $viewContent = view("errors.{$code}")->render();

            $this->assertStringContainsString('Toyota AutoConnect', $viewContent);
            $this->assertStringContainsString($code, $viewContent);
            $this->assertStringNotContainsString('vendor/laravel', $viewContent);
            $this->assertStringNotContainsString('Stack trace:', $viewContent);
        }
    }

    /**
     * 14. Test Sensitive Data Masking in Audit Trail and System Settings.
     */
    public function test_sensitive_credentials_are_never_logged_or_exposed_in_plain_text(): void
    {
        $auditService = app(\App\Services\AuditTrailService::class);

        // Log sensitive settings update
        $log = $auditService->logSettings(
            group: 'system_settings',
            oldValues: ['midtrans_server_key' => 'old-secret-key-12345'],
            newValues: ['midtrans_server_key' => 'new-secret-key-67890'],
            actor: $this->superAdmin
        );

        $this->assertNotNull($log);
        $this->assertEquals('[PROTECTED]', $log->new_values['midtrans_server_key']);
        $this->assertNotEquals('new-secret-key-67890', $log->new_values['midtrans_server_key']);
    }

    /**
     * 15. Test Production Cache commands execute cleanly without closure errors.
     */
    public function test_production_cache_commands_run_cleanly(): void
    {
        $this->assertEquals(0, Artisan::call('config:cache'));
        $this->assertEquals(0, Artisan::call('route:cache'));
        $this->assertEquals(0, Artisan::call('view:cache'));
        $this->assertEquals(0, Artisan::call('event:cache'));

        // Clear after testing
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('event:clear');
    }
}
