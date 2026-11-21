<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CreditSimulation;
use App\Models\InventoryMovement;
use App\Models\LeasingApplication;
use App\Models\LeasingProvider;
use App\Models\Payment;
use App\Models\PaymentNotification;
use App\Models\SalesOpportunity;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use App\Models\VehicleInventory;
use App\Services\CreditSimulationService;
use App\Services\Leasing\Adapters\MockLeasingAdapter;
use App\Services\Leasing\LeasingManager;
use App\Services\Payment\MidtransService;
use Database\Seeders\LeasingProviderSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CreditSimulationAndPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(LeasingProviderSeeder::class);
    }

    /**
     * 1. Perhitungan simulasi kredit akurat dengan pendekatan bunga flat.
     * Rumus:
     * Pokok = Harga - DP
     * Bunga total = Pokok * Suku bunga tahunan * (Tenor / 12)
     * Total kredit = Pokok + Bunga total
     * Cicilan bulanan = Total kredit / Tenor
     */
    public function test_accurate_credit_simulation_calculation(): void
    {
        $service = app(CreditSimulationService::class);

        $vehiclePrice = 400_000_000;
        $downPayment = 80_000_000; // 20%
        $tenorMonths = 36; // 3 tahun
        $interestRate = 6.00; // 6% per tahun

        $result = $service->calculate(
            vehiclePrice: $vehiclePrice,
            downPayment: $downPayment,
            tenorMonths: $tenorMonths,
            interestRate: $interestRate,
        );

        $expectedFinancingAmount = 320_000_000;
        $expectedTotalInterest = (int) round(320_000_000 * 0.06 * 3); // 57.600.000
        $expectedTotalPayment = 320_000_000 + 57_600_000; // 377.600.000
        $expectedMonthlyInstallment = (int) round(377_600_000 / 36); // 10.488.889

        $this->assertEquals($vehiclePrice, $result['vehicle_price']);
        $this->assertEquals($downPayment, $result['down_payment']);
        $this->assertEquals(20.0, $result['down_payment_percentage']);
        $this->assertEquals($expectedFinancingAmount, $result['financing_amount']);
        $this->assertEquals($expectedTotalInterest, $result['total_interest']);
        $this->assertEquals($expectedTotalPayment, $result['total_credit_payment']);
        $this->assertEquals($expectedMonthlyInstallment, $result['monthly_installment']);
    }

    /**
     * 2. Validasi simulasi menolak DP yang melebihi atau sama dengan harga kendaraan.
     */
    public function test_validation_rejects_dp_exceeding_or_equal_to_vehicle_price(): void
    {
        $customer = User::factory()->customer()->create();
        $vehicle = Vehicle::factory()->create([
            'price' => 300_000_000,
            'publication_status' => 'published',
        ]);

        $response = $this->actingAs($customer)->post(route('customer.simulations.store'), [
            'vehicle_id' => $vehicle->id,
            'down_payment' => 300_000_000, // DP == price
            'tenor_months' => 36,
        ]);

        $response->assertSessionHasErrors(['down_payment']);

        $responseExcess = $this->actingAs($customer)->post(route('customer.simulations.store'), [
            'vehicle_id' => $vehicle->id,
            'down_payment' => 350_000_000, // DP > price
            'tenor_months' => 36,
        ]);

        $responseExcess->assertSessionHasErrors(['down_payment']);
    }

    /**
     * 3. Validasi simulasi menolak tenor yang tidak didukung konfigurasi.
     */
    public function test_validation_rejects_unsupported_tenor(): void
    {
        $customer = User::factory()->customer()->create();
        $vehicle = Vehicle::factory()->create([
            'price' => 300_000_000,
            'publication_status' => 'published',
        ]);

        $response = $this->actingAs($customer)->post(route('customer.simulations.store'), [
            'vehicle_id' => $vehicle->id,
            'down_payment' => 60_000_000,
            'tenor_months' => 15, // Not in ALLOWED_TENORS [12, 24, 36, 48, 60]
        ]);

        $response->assertSessionHasErrors(['tenor_months']);
    }

    /**
     * 4. Customer dapat menyimpan hasil simulasi, melihat daftar, dan membuka detail.
     */
    public function test_customer_can_save_and_view_simulation(): void
    {
        $customer = User::factory()->customer()->create();
        $vehicle = Vehicle::factory()->create([
            'name' => 'Toyota All New Avanza',
            'price' => 250_000_000,
            'publication_status' => 'published',
        ]);
        $provider = LeasingProvider::where('code', 'mock_leasing')->first() ?? LeasingProvider::first();

        $response = $this->actingAs($customer)->post(route('customer.simulations.store'), [
            'vehicle_id' => $vehicle->id,
            'leasing_provider_id' => $provider->id,
            'down_payment' => 50_000_000,
            'tenor_months' => 48,
            'notes' => 'Simulasi untuk rencana pembelian akhir tahun.',
        ]);

        $response->assertRedirect();

        $simulation = CreditSimulation::where('user_id', $customer->id)->first();
        $this->assertNotNull($simulation);
        $this->assertEquals($vehicle->id, $simulation->vehicle_id);
        $this->assertEquals(200_000_000, $simulation->financing_amount);

        // Can view in index
        $indexResponse = $this->actingAs($customer)->get(route('customer.simulations.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Toyota All New Avanza');

        // Can view detail
        $showResponse = $this->actingAs($customer)->get(route('customer.simulations.show', $simulation));
        $showResponse->assertOk();
        $showResponse->assertSee($simulation->formatted_financing_amount);
    }

    /**
     * 5. Otorisasi scoping: Customer lain tidak boleh melihat riwayat simulasi pengguna lain.
     */
    public function test_simulation_authorization_scoping(): void
    {
        $customerA = User::factory()->customer()->create();
        $customerB = User::factory()->customer()->create();

        $vehicle = Vehicle::factory()->create(['price' => 300_000_000]);

        $simulationA = CreditSimulation::create([
            'user_id' => $customerA->id,
            'vehicle_id' => $vehicle->id,
            'vehicle_price' => 300_000_000,
            'down_payment' => 60_000_000,
            'down_payment_percentage' => 20,
            'financing_amount' => 240_000_000,
            'interest_rate' => 6.00,
            'tenor_months' => 36,
            'total_interest' => 43_200_000,
            'monthly_installment' => 7_866_667,
            'calculation_method' => 'flat',
        ]);

        $response = $this->actingAs($customerB)->get(route('customer.simulations.show', $simulationA));
        $response->assertForbidden();
    }

    /**
     * 6. Customer dapat mengajukan pembiayaan leasing dan tersimpan di database serta audit log.
     */
    public function test_customer_can_submit_leasing_application(): void
    {
        $customer = User::factory()->customer()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
        ]);

        $vehicle = Vehicle::factory()->create([
            'price' => 350_000_000,
            'publication_status' => 'published',
        ]);

        $provider = LeasingProvider::where('code', 'mock_leasing')->first() ?? LeasingProvider::first();

        $response = $this->actingAs($customer)->post(route('customer.leasing.store'), [
            'vehicle_id' => $vehicle->id,
            'leasing_provider_id' => $provider->id,
            'down_payment' => 70_000_000,
            'tenor_months' => 36,
            'employment_type' => 'karyawan_swasta',
            'monthly_income' => 25_000_000,
            'company_name' => 'PT Astra International',
            'id_card_number' => '3201123456780001',
            'npwp_number' => '09.123.456.7-890.000',
            'marital_status' => 'menikah',
            'notes' => 'Pengajuan via online portal.',
            'agree_terms' => 1,
        ]);

        $response->assertRedirect();

        $application = LeasingApplication::where('user_id', $customer->id)->first();
        $this->assertNotNull($application);
        $this->assertEquals($provider->id, $application->leasing_provider_id);
        $this->assertContains($application->status, ['submitted', 'processing']);

        // Check audit log
        $this->assertDatabaseHas('leasing_application_logs', [
            'leasing_application_id' => $application->id,
            'event_type' => 'submitted',
        ]);
    }

    /**
     * 7. Kegagalan API provider eksternal tidak menandai aplikasi sebagai 'approved'.
     */
    public function test_provider_failure_does_not_approve_application(): void
    {
        $customer = User::factory()->customer()->create();
        $vehicle = Vehicle::factory()->create(['price' => 300_000_000]);

        $provider = LeasingProvider::where('code', 'mock_leasing')->first() ?? LeasingProvider::first();

        // Bind mock adapter configured to fail
        $failingAdapter = new MockLeasingAdapter();
        $failingAdapter->setShouldFail(true);
        app(LeasingManager::class)->bind($provider->code, $failingAdapter);

        $response = $this->actingAs($customer)->post(route('customer.leasing.store'), [
            'vehicle_id' => $vehicle->id,
            'leasing_provider_id' => $provider->id,
            'down_payment' => 60_000_000,
            'tenor_months' => 36,
            'employment_type' => 'karyawan_swasta',
            'monthly_income' => 20_000_000,
            'id_card_number' => '3201123456780002',
            'marital_status' => 'lajang',
            'agree_terms' => 1,
        ]);

        $application = LeasingApplication::where('user_id', $customer->id)
            ->where('leasing_provider_id', $provider->id)
            ->first();

        $this->assertNotNull($application);
        // Provider failure must NEVER set status to approved!
        $this->assertNotEquals('approved', $application->status);
        $this->assertEquals('draft', $application->status);

        // Verify failure was logged in audit trail
        $this->assertDatabaseHas('leasing_application_logs', [
            'leasing_application_id' => $application->id,
            'event_type' => 'submission_failed',
        ]);
    }

    /**
     * 8. Mock leasing adapter dapat menangani pengajuan dan pengecekan status secara terisolasi.
     */
    public function test_mock_leasing_adapter_handles_submission_and_status_check(): void
    {
        $customer = User::factory()->customer()->create();
        $vehicle = Vehicle::factory()->create(['price' => 300_000_000]);
        $provider = LeasingProvider::where('code', 'mock_leasing')->first() ?? LeasingProvider::first();

        $application = LeasingApplication::create([
            'code' => LeasingApplication::generateCode(),
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'leasing_provider_id' => $provider->id,
            'status' => 'draft',
            'vehicle_price' => 300_000_000,
            'down_payment' => 60_000_000,
            'tenor_months' => 36,
            'monthly_installment' => 7_500_000,
            'applicant_data' => ['id_card_number' => '3201000000000001'],
        ]);

        $adapter = new MockLeasingAdapter();

        $submitResult = $adapter->submitApplication($application);

        $this->assertTrue($submitResult['success']);
        $this->assertNotEmpty($submitResult['reference_id']);

        $application->update([
            'provider_reference' => $submitResult['reference_id'],
            'status' => 'submitted',
        ]);

        $statusResult = $adapter->checkStatus($application);
        $this->assertTrue($statusResult['success']);
        $this->assertContains($statusResult['status'], ['submitted', 'processing', 'approved']);
    }

    /**
     * 8b. ACC and TAF adapters use development sandbox simulation when external endpoints are not configured.
     */
    public function test_acc_and_taf_adapters_dev_sandbox_simulation(): void
    {
        $customer = User::factory()->customer()->create();
        $vehicle = Vehicle::factory()->create(['price' => 300_000_000]);
        $accProvider = LeasingProvider::where('code', 'acc')->firstOrFail();
        $tafProvider = LeasingProvider::where('code', 'taf')->firstOrFail();

        $accApplication = LeasingApplication::create([
            'code' => LeasingApplication::generateCode(),
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'leasing_provider_id' => $accProvider->id,
            'status' => 'draft',
            'vehicle_price' => 300_000_000,
            'down_payment' => 60_000_000,
            'tenor_months' => 36,
            'monthly_installment' => 7_500_000,
            'applicant_data' => ['id_card_number' => '3201000000000001'],
        ]);

        $accAdapter = new \App\Services\Leasing\Adapters\AccLeasingAdapter();
        $accResult = $accAdapter->submitApplication($accApplication);
        $this->assertTrue($accResult['success']);
        $this->assertStringStartsWith('ACC-SIM-', $accResult['reference_id']);
        $this->assertEquals('submitted', $accResult['status']);

        $accApplication->update(['provider_reference' => $accResult['reference_id']]);
        $accStatusResult = $accAdapter->checkStatus($accApplication);
        $this->assertTrue($accStatusResult['success']);
        $this->assertEquals('processing', $accStatusResult['status']);

        // Test TAF Adapter
        $tafApplication = LeasingApplication::create([
            'code' => LeasingApplication::generateCode(),
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'leasing_provider_id' => $tafProvider->id,
            'status' => 'draft',
            'vehicle_price' => 300_000_000,
            'down_payment' => 60_000_000,
            'tenor_months' => 36,
            'monthly_installment' => 7_500_000,
            'applicant_data' => ['id_card_number' => '3201000000000002'],
        ]);

        $tafAdapter = new \App\Services\Leasing\Adapters\TafLeasingAdapter();
        $tafResult = $tafAdapter->submitApplication($tafApplication);
        $this->assertTrue($tafResult['success']);
        $this->assertStringStartsWith('TAF-SIM-', $tafResult['reference_id']);
        $this->assertEquals('submitted', $tafResult['status']);

        $tafApplication->update(['provider_reference' => $tafResult['reference_id']]);
        $tafStatusResult = $tafAdapter->checkStatus($tafApplication);
        $this->assertTrue($tafStatusResult['success']);
        $this->assertEquals('processing', $tafStatusResult['status']);
    }

    /**
     * 9. Inisiasi pembayaran tanda jadi untuk booking yang valid membuat transaksi Payment dengan Snap Token.
     */
    public function test_payment_creation_for_valid_booking(): void
    {
        $customer = User::factory()->customer()->create();
        $vehicle = Vehicle::factory()->create(['price' => 300_000_000]);
        $branch = Branch::factory()->create(['is_active' => true]);

        $opportunity = SalesOpportunity::factory()->create([
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
        ]);

        $booking = VehicleBooking::factory()->create([
            'sales_opportunity_id' => $opportunity->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'booking_fee' => 5_000_000,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'mock-snap-token-12345',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/mock-snap-token-12345',
            ], 201),
        ]);

        $response = $this->actingAs($customer)->get(route('customer.bookings.pay', $booking));
        $response->assertOk();
        $response->assertSee('mock-snap-token-12345');

        $payment = Payment::where('vehicle_booking_id', $booking->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('pending', $payment->status);
        $this->assertEquals('mock-snap-token-12345', $payment->snap_token);
        $this->assertEquals(5_000_000, $payment->amount);
    }

    /**
     * 10. Nominal pembayaran tanda jadi harus strictly sama dengan booking_fee di pemesanan.
     */
    public function test_payment_amount_strictly_matches_booking_fee(): void
    {
        $customer = User::factory()->customer()->create();
        $vehicle = Vehicle::factory()->create(['price' => 300_000_000]);
        $branch = Branch::factory()->create(['is_active' => true]);

        $bookingFee = 10_000_000;

        $opportunity = SalesOpportunity::factory()->create([
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
        ]);

        $booking = VehicleBooking::factory()->create([
            'sales_opportunity_id' => $opportunity->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'booking_fee' => $bookingFee,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'mock-snap-token-test-fee',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/mock-snap-token-test-fee',
            ], 201),
        ]);

        $service = app(MidtransService::class);
        $payment = $service->createSnapTransaction($booking, $customer);

        $this->assertEquals($bookingFee, $payment->amount);
    }

    /**
     * 11. Verifikasi SHA512 signature Midtrans: hash(order_id + status_code + gross_amount + server_key).
     */
    public function test_midtrans_signature_verification(): void
    {
        $midtransService = app(MidtransService::class);

        $orderId = 'BOOK-101-1730000000';
        $statusCode = '200';
        $grossAmount = '5000000.00';
        $serverKey = config('midtrans.server_key');

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        // Correct signature verifies
        $this->assertTrue(
            $midtransService->verifySignature($orderId, $statusCode, $grossAmount, $expectedSignature)
        );

        // Tampered signature fails
        $this->assertFalse(
            $midtransService->verifySignature($orderId, $statusCode, $grossAmount, 'invalid_signature_hash_123')
        );
    }

    /**
     * 12. Webhook menolak notifikasi dengan signature tidak sah atau nominal yang dimanipulasi.
     */
    public function test_reject_invalid_signature_or_amount_tampering(): void
    {
        $customer = User::factory()->customer()->create();
        $vehicle = Vehicle::factory()->create(['price' => 300_000_000]);
        $branch = Branch::factory()->create();

        $opportunity = SalesOpportunity::factory()->create([
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
        ]);

        $booking = VehicleBooking::factory()->create([
            'sales_opportunity_id' => $opportunity->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'booking_fee' => 5_000_000,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        $payment = Payment::create([
            'code' => Payment::generateCode(),
            'vehicle_booking_id' => $booking->id,
            'user_id' => $customer->id,
            'order_id' => 'BOOK-REJECT-' . time(),
            'amount' => 5_000_000,
            'status' => 'pending',
        ]);

        // Attempt 1: Fake signature
        $fakeSigPayload = [
            'order_id' => $payment->order_id,
            'status_code' => '200',
            'gross_amount' => '5000000.00',
            'signature_key' => 'totally_fake_signature_hash',
            'transaction_status' => 'settlement',
            'transaction_id' => 'trx-fake-sig-1',
            'payment_type' => 'bank_transfer',
        ];

        $response1 = $this->postJson(route('midtrans.notification'), $fakeSigPayload);
        $response1->assertStatus(403);
        $this->assertEquals('pending', $payment->fresh()->status);

        // Attempt 2: Tampered gross_amount (e.g., Rp 1.000 instead of 5.000.000)
        $tamperedAmount = '1000.00';
        $tamperedSig = hash('sha512', $payment->order_id . '200' . $tamperedAmount . config('midtrans.server_key'));

        $tamperedPayload = [
            'order_id' => $payment->order_id,
            'status_code' => '200',
            'gross_amount' => $tamperedAmount,
            'signature_key' => $tamperedSig,
            'transaction_status' => 'settlement',
            'transaction_id' => 'trx-tampered-amt-2',
            'payment_type' => 'bank_transfer',
        ];

        $response2 = $this->postJson(route('midtrans.notification'), $tamperedPayload);
        $response2->assertStatus(400);
        $this->assertEquals('pending', $payment->fresh()->status);
    }

    /**
     * 13. Idempotency: Notifikasi webhook duplikat dicatat aman tanpa eksekusi ganda.
     */
    public function test_idempotent_duplicate_webhook_notifications(): void
    {
        $customer = User::factory()->customer()->create();
        $vehicle = Vehicle::factory()->create(['price' => 300_000_000]);
        $branch = Branch::factory()->create();

        $opportunity = SalesOpportunity::factory()->create([
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
        ]);

        $booking = VehicleBooking::factory()->create([
            'sales_opportunity_id' => $opportunity->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'booking_fee' => 5_000_000,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        $orderId = 'BOOK-IDEMP-' . time();
        $payment = Payment::create([
            'code' => Payment::generateCode(),
            'vehicle_booking_id' => $booking->id,
            'user_id' => $customer->id,
            'order_id' => $orderId,
            'amount' => 5_000_000,
            'status' => 'pending',
        ]);

        $grossAmount = '5000000.00';
        $sig = hash('sha512', $orderId . '200' . $grossAmount . config('midtrans.server_key'));
        $transactionId = 'trx-idemp-' . time();

        $payload = [
            'order_id' => $orderId,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $sig,
            'transaction_status' => 'settlement',
            'transaction_id' => $transactionId,
            'payment_type' => 'gopay',
        ];

        // First notification: Processes successfully
        $res1 = $this->postJson(route('midtrans.notification'), $payload);
        $res1->assertOk();
        $this->assertEquals('paid', $payment->fresh()->status);
        $this->assertEquals('paid', $booking->fresh()->payment_status);

        // Second notification: Duplicated payload
        $res2 = $this->postJson(route('midtrans.notification'), $payload);
        $res2->assertOk();

        // Audit log records duplicate_ignored
        $this->assertDatabaseHas('payment_notifications', [
            'order_id' => $orderId,
            'processing_status' => 'duplicate_ignored',
        ]);
    }

    /**
     * 14. Status settlement memperbarui payment ke 'paid', booking ke 'confirmed', dan mempertahankan alokasi.
     */
    public function test_payment_settlement_updates_booking_and_confirms_inventory(): void
    {
        $customer = User::factory()->customer()->create();
        $vehicle = Vehicle::factory()->create(['price' => 300_000_000]);
        $branch = Branch::factory()->create();

        $inventory = VehicleInventory::create([
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'stock_quantity' => 5,
            'reserved_quantity' => 1,
        ]);

        $opportunity = SalesOpportunity::factory()->create([
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
        ]);

        $booking = VehicleBooking::factory()->create([
            'sales_opportunity_id' => $opportunity->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'inventory_id' => $inventory->id,
            'booking_fee' => 5_000_000,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        $orderId = 'BOOK-SETTLE-' . time();
        $payment = Payment::create([
            'code' => Payment::generateCode(),
            'vehicle_booking_id' => $booking->id,
            'user_id' => $customer->id,
            'order_id' => $orderId,
            'amount' => 5_000_000,
            'status' => 'pending',
        ]);

        $grossAmount = '5000000.00';
        $sig = hash('sha512', $orderId . '200' . $grossAmount . config('midtrans.server_key'));

        $response = $this->postJson(route('midtrans.notification'), [
            'order_id' => $orderId,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => $sig,
            'transaction_status' => 'settlement',
            'transaction_id' => 'trx-settle-1',
            'payment_type' => 'bca_va',
        ]);

        $response->assertOk();

        // Payment is paid
        $this->assertEquals('paid', $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at);

        // Booking is confirmed and marked paid
        $this->assertEquals('paid', $booking->fresh()->payment_status);
        $this->assertEquals('confirmed', $booking->fresh()->status);
        $this->assertNotNull($booking->fresh()->confirmed_at);

        // Reserved inventory is maintained
        $this->assertEquals(1, $inventory->fresh()->reserved_quantity);
    }

    /**
     * 15. Status pembayaran expired melepaskan alokasi reserved_quantity kembali ke stok dealer.
     */
    public function test_expired_or_failed_payment_releases_inventory_reservation(): void
    {
        $customer = User::factory()->customer()->create();
        $vehicle = Vehicle::factory()->create(['price' => 300_000_000]);
        $branch = Branch::factory()->create();

        $inventory = VehicleInventory::create([
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'stock_quantity' => 5,
            'reserved_quantity' => 1,
        ]);

        $opportunity = SalesOpportunity::factory()->create([
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
        ]);

        $booking = VehicleBooking::factory()->create([
            'sales_opportunity_id' => $opportunity->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'inventory_id' => $inventory->id,
            'booking_fee' => 5_000_000,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        $orderId = 'BOOK-EXPIRE-' . time();
        $payment = Payment::create([
            'code' => Payment::generateCode(),
            'vehicle_booking_id' => $booking->id,
            'user_id' => $customer->id,
            'order_id' => $orderId,
            'amount' => 5_000_000,
            'status' => 'pending',
        ]);

        $grossAmount = '5000000.00';
        $sig = hash('sha512', $orderId . '202' . $grossAmount . config('midtrans.server_key'));

        $response = $this->postJson(route('midtrans.notification'), [
            'order_id' => $orderId,
            'status_code' => '202',
            'gross_amount' => $grossAmount,
            'signature_key' => $sig,
            'transaction_status' => 'expire',
            'transaction_id' => 'trx-expire-1',
            'payment_type' => 'echannel',
        ]);

        $response->assertOk();

        // Payment marked expired
        $this->assertEquals('expired', $payment->fresh()->status);
        $this->assertEquals('expired', $booking->fresh()->status);

        // Reserved quantity is decremented (released back to stock)
        $this->assertEquals(0, $inventory->fresh()->reserved_quantity);

        // Movement audit record created
        $this->assertDatabaseHas('inventory_movements', [
            'vehicle_inventory_id' => $inventory->id,
            'movement_type' => 'reservation_released',
        ]);
    }

    /**
     * 15b. Webhook menangani notifikasi pembayaran status 'pending'.
     */
    public function test_webhook_handles_pending_payment(): void
    {
        $customer = User::factory()->customer()->create();
        $vehicle = Vehicle::factory()->create(['price' => 300_000_000]);
        $branch = Branch::factory()->create();

        $opportunity = SalesOpportunity::factory()->create([
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
        ]);

        $booking = VehicleBooking::factory()->create([
            'sales_opportunity_id' => $opportunity->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'booking_fee' => 5_000_000,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        $orderId = 'BOOK-PEND-' . time();
        $payment = Payment::create([
            'code' => Payment::generateCode(),
            'vehicle_booking_id' => $booking->id,
            'user_id' => $customer->id,
            'order_id' => $orderId,
            'amount' => 5_000_000,
            'status' => 'pending',
        ]);

        $grossAmount = '5000000.00';
        $sig = hash('sha512', $orderId . '201' . $grossAmount . config('midtrans.server_key'));

        $response = $this->postJson(route('midtrans.notification'), [
            'order_id' => $orderId,
            'status_code' => '201',
            'gross_amount' => $grossAmount,
            'signature_key' => $sig,
            'transaction_status' => 'pending',
            'transaction_id' => 'trx-pend-1',
            'payment_type' => 'bca_va',
        ]);

        $response->assertOk();
        $this->assertEquals('pending', $payment->fresh()->status);
        $this->assertEquals('pending', $booking->fresh()->payment_status);
    }

    /**
     * 15c. Webhook menangani notifikasi pembayaran gagal ('deny').
     */
    public function test_webhook_handles_failed_payment(): void
    {
        $customer = User::factory()->customer()->create();
        $vehicle = Vehicle::factory()->create(['price' => 300_000_000]);
        $branch = Branch::factory()->create();

        $opportunity = SalesOpportunity::factory()->create([
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
        ]);

        $booking = VehicleBooking::factory()->create([
            'sales_opportunity_id' => $opportunity->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'booking_fee' => 5_000_000,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        $orderId = 'BOOK-DENY-' . time();
        $payment = Payment::create([
            'code' => Payment::generateCode(),
            'vehicle_booking_id' => $booking->id,
            'user_id' => $customer->id,
            'order_id' => $orderId,
            'amount' => 5_000_000,
            'status' => 'pending',
        ]);

        $grossAmount = '5000000.00';
        $sig = hash('sha512', $orderId . '202' . $grossAmount . config('midtrans.server_key'));

        $response = $this->postJson(route('midtrans.notification'), [
            'order_id' => $orderId,
            'status_code' => '202',
            'gross_amount' => $grossAmount,
            'signature_key' => $sig,
            'transaction_status' => 'deny',
            'transaction_id' => 'trx-deny-1',
            'payment_type' => 'credit_card',
        ]);

        $response->assertOk();
        $this->assertEquals('failed', $payment->fresh()->status);
    }

    /**
     * 15d. Customer tidak dapat mengakses atau membayar booking/transaksi pengguna lain.
     */
    public function test_customer_cannot_access_or_pay_other_users_booking_or_payment(): void
    {
        $customerA = User::factory()->customer()->create(['email' => 'customerA@example.com']);
        $customerB = User::factory()->customer()->create(['email' => 'customerB@example.com']);
        $vehicle = Vehicle::factory()->create(['price' => 300_000_000]);
        $branch = Branch::factory()->create();

        $opportunity = SalesOpportunity::factory()->create([
            'user_id' => $customerA->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
        ]);

        $bookingA = VehicleBooking::factory()->create([
            'sales_opportunity_id' => $opportunity->id,
            'customer_id' => $customerA->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'booking_fee' => 5_000_000,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        $paymentA = Payment::create([
            'code' => Payment::generateCode(),
            'vehicle_booking_id' => $bookingA->id,
            'user_id' => $customerA->id,
            'order_id' => 'BOOK-A-' . time(),
            'amount' => 5_000_000,
            'status' => 'pending',
        ]);

        // Customer B tries to checkout Customer A's booking -> 403
        $checkoutResponse = $this->actingAs($customerB)->get(route('customer.bookings.pay', $bookingA));
        $checkoutResponse->assertForbidden();

        // Customer B tries to view Customer A's payment receipt -> 403
        $showResponse = $this->actingAs($customerB)->get(route('customer.payments.show', $paymentA));
        $showResponse->assertForbidden();
    }

    /**
     * 15e. Mencegah pembayaran berulang pada booking yang sudah lunas.
     */
    public function test_cannot_pay_already_paid_booking(): void
    {
        $customer = User::factory()->customer()->create();
        $vehicle = Vehicle::factory()->create(['price' => 300_000_000]);
        $branch = Branch::factory()->create();

        $opportunity = SalesOpportunity::factory()->create([
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
        ]);

        $booking = VehicleBooking::factory()->create([
            'sales_opportunity_id' => $opportunity->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'booking_fee' => 5_000_000,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($customer)->get(route('customer.bookings.pay', $booking));
        $response->assertRedirect(route('customer.bookings.show', $booking));
        $response->assertSessionHas('info');
    }

    /**
     * 15f. Admin dengan izin dapat melihat daftar transaksi pembayaran dan refund.
     */
    public function test_admin_can_view_payments_and_process_refund(): void
    {
        $financeOfficer = User::factory()->financeOfficer()->create();
        $customer = User::factory()->customer()->create();
        $vehicle = Vehicle::factory()->create(['price' => 300_000_000]);
        $branch = Branch::factory()->create();

        $opportunity = SalesOpportunity::factory()->create([
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
        ]);

        $booking = VehicleBooking::factory()->create([
            'sales_opportunity_id' => $opportunity->id,
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'booking_fee' => 5_000_000,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        $payment = Payment::create([
            'code' => Payment::generateCode(),
            'vehicle_booking_id' => $booking->id,
            'user_id' => $customer->id,
            'order_id' => 'BOOK-REFUND-' . time(),
            'amount' => 5_000_000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Finance Officer can view admin payment index
        $indexResponse = $this->actingAs($financeOfficer)->get(route('admin.payments.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee($payment->order_id);

        // Finance Officer can view detail
        $showResponse = $this->actingAs($financeOfficer)->get(route('admin.payments.show', $payment));
        $showResponse->assertOk();

        // Customer cannot access admin payments index
        $customerIndex = $this->actingAs($customer)->get(route('admin.payments.index'));
        $customerIndex->assertForbidden();

        // Finance Officer can process refund
        $refundResponse = $this->actingAs($financeOfficer)->post(route('admin.payments.refund', $payment), [
            'refund_reason' => 'Permintaan pembatalan unit oleh nasabah.',
            'refund_amount' => 5_000_000,
        ]);
        $refundResponse->assertRedirect(route('admin.payments.show', $payment));
        $this->assertEquals('refunded', $payment->fresh()->status);
        $this->assertEquals('refunded', $booking->fresh()->payment_status);
    }

    /**
     * 15g. Admin dapat melihat halaman detail leasing dan memperbarui statusnya.
     */
    public function test_admin_can_view_leasing_application_detail_and_update_status(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $customer = User::factory()->customer()->create();
        $vehicle = Vehicle::factory()->create(['price' => 300_000_000]);
        $provider = LeasingProvider::first();

        $application = LeasingApplication::create([
            'code' => LeasingApplication::generateCode(),
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'leasing_provider_id' => $provider->id,
            'status' => 'submitted',
            'vehicle_price' => 300_000_000,
            'down_payment' => 60_000_000,
            'tenor_months' => 36,
            'monthly_installment' => 7_500_000,
            'applicant_data' => ['id_card_number' => '3201000000000001'],
        ]);

        // Detail page renders successfully without 500 RouteNotFoundException
        $response = $this->actingAs($superAdmin)->get(route('admin.leasing.show', $application));
        $response->assertOk();
        $response->assertSee('Perbarui Status Berkas');

        // Status update via PUT route succeeds
        $updateResponse = $this->actingAs($superAdmin)->put(route('admin.leasing.status.update', $application), [
            'status' => 'approved',
            'notes' => 'Disetujui oleh Dealer Manager.',
        ]);
        $updateResponse->assertRedirect(route('admin.leasing.show', $application));
        $this->assertEquals('approved', $application->fresh()->status);
    }

    /**
     * 16. Pemeriksaan regresi: Modul katalog, detail kendaraan, dan portal tetap berfungsi normal.
     */
    public function test_regression_existing_modules_remain_functional(): void
    {
        $vehicle = Vehicle::factory()->create([
            'name' => 'Toyota Kijang Innova Zenix',
            'slug' => 'toyota-kijang-innova-zenix',
            'price' => 425_000_000,
            'publication_status' => 'published',
        ]);

        // Catalog index
        $catalogResponse = $this->get(route('vehicles.index'));
        $catalogResponse->assertOk();
        $catalogResponse->assertSee('Toyota Kijang Innova Zenix');

        // Vehicle detail includes Credit Simulation calculator section
        $detailResponse = $this->get(route('vehicles.show', $vehicle->slug));
        $detailResponse->assertOk();
        $detailResponse->assertSee('simulasi-kredit');
        $detailResponse->assertSee('Kalkulator Simulasi Kredit');
        $detailResponse->assertSee('Pilih Model Mobil Toyota');

        // Test bZ4X EV vehicle detail page renders with real non-zero price
        $bz4x = Vehicle::factory()->create([
            'name' => 'All New bZ4X EV',
            'slug' => 'all-new-bz4x-ev',
            'price' => 1190000000,
            'publication_status' => 'published',
        ]);
        $bz4xResponse = $this->get(route('vehicles.show', $bz4x->slug));
        $bz4xResponse->assertOk();
        $bz4xResponse->assertSee('1.190.000.000');
        $bz4xResponse->assertDontSee(':value="formatRupiah(0)"');

        // Customer portal subnav includes new tabs
        $customer = User::factory()->customer()->create();
        $portalResponse = $this->actingAs($customer)->get(route('customer.dashboard'));
        $portalResponse->assertOk();
        $portalResponse->assertSee('Simulasi Kredit');
        $portalResponse->assertSee('Pengajuan Leasing');
        $portalResponse->assertSee('Pembayaran SPK');
    }
}
