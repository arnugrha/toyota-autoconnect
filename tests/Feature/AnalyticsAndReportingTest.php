<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\SalesOpportunity;
use App\Models\ServiceBooking;
use App\Models\ServiceOrder;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use App\Models\VehicleCategory;
use App\Models\VehicleInventory;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsAndReportingTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $dealerManager;
    protected User $salesExecutive;
    protected User $inventoryOfficer;
    protected User $serviceAdvisor;
    protected User $customer;
    protected Branch $branchA;
    protected Branch $branchB;
    protected Vehicle $vehicle;
    protected VehicleCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        // Branches
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

        // Users with roles
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

        $this->inventoryOfficer = User::create([
            'name' => 'Irfan Inventory',
            'email' => 'irfan.inv@toyota-autoconnect.id',
            'phone' => '08110000004',
            'password' => bcrypt('password123'),
            'is_active' => true,
            'branch_id' => $this->branchA->id,
        ]);
        $this->inventoryOfficer->assignRole('Inventory Officer');

        $this->serviceAdvisor = User::create([
            'name' => 'Siti Advisor',
            'email' => 'siti.advisor@toyota-autoconnect.id',
            'phone' => '08110000005',
            'password' => bcrypt('password123'),
            'is_active' => true,
            'branch_id' => $this->branchA->id,
        ]);
        $this->serviceAdvisor->assignRole('Service Advisor');

        $this->customer = User::create([
            'name' => 'Rudi Pelanggan',
            'email' => 'rudi.customer@example.com',
            'phone' => '081234567890',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $this->customer->assignRole('Customer');

        // Master category & vehicle
        $this->category = VehicleCategory::create([
            'name' => 'SUV',
            'slug' => 'suv',
            'description' => 'Sport Utility Vehicle Toyota',
            'is_active' => true,
        ]);

        $this->vehicle = Vehicle::create([
            'name' => 'All New Fortuner GR Sport',
            'slug' => 'all-new-fortuner-gr-sport',
            'category_id' => $this->category->id,
            'category' => 'SUV',
            'price' => 740_000_000,
            'status' => 'available',
        ]);
    }

    protected function createSalesOpportunity(Branch $branch, ?User $assignedTo = null): SalesOpportunity
    {
        return SalesOpportunity::create([
            'code' => SalesOpportunity::generateCode(),
            'user_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'branch_id' => $branch->id,
            'assigned_to' => $assignedTo?->id ?? $this->salesExecutive->id,
            'stage' => 'negotiation',
            'estimated_value' => $this->vehicle->price,
        ]);
    }

    protected function createBooking(
        Branch $branch,
        string $status = 'confirmed',
        string $paymentStatus = 'paid',
        int $price = 740_000_000,
        ?Carbon $createdAt = null,
        ?User $assignedTo = null
    ): VehicleBooking {
        static $bookingCounter = 100;
        $bookingCounter++;

        $opportunity = $this->createSalesOpportunity($branch, $assignedTo);

        $booking = VehicleBooking::create([
            'code' => 'SPK-' . date('Ymd') . '-' . $bookingCounter,
            'sales_opportunity_id' => $opportunity->id,
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'branch_id' => $branch->id,
            'vehicle_price' => $price,
            'discount_amount' => 0,
            'booking_fee' => 10_000_000,
            'booking_date' => ($createdAt ?? now())->toDateString(),
            'status' => $status,
            'payment_status' => $paymentStatus,
        ]);

        if ($createdAt) {
            $booking->created_at = $createdAt;
            $booking->saveQuietly();
        }

        return $booking;
    }

    protected function createLead(
        Branch $branch,
        string $status = 'new',
        ?User $assignedTo = null,
        string $source = 'website_inquiry',
        ?string $customerName = null
    ): Lead {
        static $leadCounter = 100;
        $leadCounter++;

        return Lead::create([
            'lead_number' => 'LD-' . date('Ymd') . '-' . $leadCounter,
            'customer_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'assigned_to' => $assignedTo?->id ?? $this->salesExecutive->id,
            'status' => $status,
            'source' => $source,
            'last_activity_at' => now(),
        ]);
    }

    protected function createServiceType(): ServiceType
    {
        static $srvCounter = 100;
        $srvCounter++;

        return ServiceType::create([
            'name' => 'Servis Berkala 10.000 KM',
            'code' => 'SRV-10K-' . $srvCounter,
            'slug' => 'servis-berkala-10000-km-' . $srvCounter,
            'category' => 'regular_maintenance',
            'estimated_duration_minutes' => 60,
            'estimated_cost' => 1_500_000,
            'is_active' => true,
        ]);
    }

    protected function createPayment(
        VehicleBooking $booking,
        int $amount,
        string $status = 'paid',
        string $method = 'bank_transfer',
        ?Carbon $paidAt = null
    ): Payment {
        return Payment::create([
            'code' => Payment::generateCode(),
            'vehicle_booking_id' => $booking->id,
            'user_id' => $this->customer->id,
            'order_id' => 'MID-' . uniqid(),
            'amount' => $amount,
            'payment_method' => $method,
            'status' => $status,
            'paid_at' => $paidAt ?? ($status === 'paid' ? now() : null),
        ]);
    }

    /**
     * 1. Perhitungan total penjualan kendaraan hanya menghitung unit SPK confirmed / completed lunas.
     */
    public function test_01_total_sales_calculation_only_counts_confirmed_completed_and_paid_bookings(): void
    {
        // 1. Valid sale: confirmed and paid
        $this->createBooking($this->branchA, 'confirmed', 'paid', 740_000_000);

        // 2. Valid sale: completed status
        $this->createBooking($this->branchA, 'completed', 'paid', 740_000_000);

        // 3. Invalid: pending booking (not counted as sold)
        $this->createBooking($this->branchA, 'pending', 'unpaid', 740_000_000);

        // 4. Invalid: cancelled booking (not counted as sold)
        $this->createBooking($this->branchA, 'cancelled', 'unpaid', 740_000_000);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.analytics.dashboard'));
        $response->assertStatus(200);

        $data = $response->viewData('data');
        $this->assertEquals(2, $data['overview']['sales_count']['value']);
        $this->assertEquals(4, $data['overview']['bookings_count']['value']); // All 4 are SPK
    }

    /**
     * 2. Perhitungan total pendapatan penjualan unit mengagregasi net_unit_price unit terjual.
     */
    public function test_02_revenue_calculation_sums_net_unit_price_of_valid_sales(): void
    {
        // Sale 1: 350.000.000
        $this->createBooking($this->branchA, 'confirmed', 'paid', 350_000_000);

        // Sale 2: 450.000.000
        $this->createBooking($this->branchA, 'completed', 'paid', 450_000_000);

        // Pending unconfirmed booking: 500.000.000 (must NOT be counted in revenue)
        $this->createBooking($this->branchA, 'pending', 'unpaid', 500_000_000);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.analytics.sales'));
        $response->assertStatus(200);

        $data = $response->viewData('data');
        $this->assertEquals(2, $data['kpis']['total_sales']);
        $this->assertEquals(800_000_000, $data['kpis']['total_revenue']);
        $this->assertEquals(400_000_000, $data['kpis']['average_deal']);
    }

    /**
     * 3. Filter rentang tanggal secara akurat mengikutsertakan dan mengecualikan transaksi.
     */
    public function test_03_date_range_filters_include_and_exclude_records_properly(): void
    {
        // Today's sale
        $this->createBooking($this->branchA, 'confirmed', 'paid', 300_000_000, now());

        // Sale from 60 days ago
        $this->createBooking($this->branchA, 'confirmed', 'paid', 300_000_000, now()->subDays(60));

        // Request 'this_month' filter: should only return 1 sale
        $response = $this->actingAs($this->superAdmin)->get(route('admin.analytics.sales', ['period' => 'this_month']));
        $response->assertStatus(200);
        $this->assertEquals(1, $response->viewData('data')['kpis']['total_sales']);

        // Request custom filter spanning back 90 days: should return both 2 sales
        $responseCustom = $this->actingAs($this->superAdmin)->get(route('admin.analytics.sales', [
            'period' => 'custom',
            'start_date' => now()->subDays(90)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));
        $responseCustom->assertStatus(200);
        $this->assertEquals(2, $responseCustom->viewData('data')['kpis']['total_sales']);
    }

    /**
     * 4. Filter cabang dealer mengisolasi data analitik secara tepat.
     */
    public function test_04_dealer_branch_filter_properly_isolates_branch_metrics(): void
    {
        // 2 sales in Branch A
        $this->createBooking($this->branchA, 'confirmed', 'paid', 400_000_000);
        $this->createBooking($this->branchA, 'confirmed', 'paid', 300_000_000);

        // 1 sale in Branch B
        $this->createBooking($this->branchB, 'confirmed', 'paid', 250_000_000);

        // Filter Branch A
        $resA = $this->actingAs($this->superAdmin)->get(route('admin.analytics.sales', ['branch_id' => $this->branchA->id]));
        $this->assertEquals(2, $resA->viewData('data')['kpis']['total_sales']);
        $this->assertEquals(700_000_000, $resA->viewData('data')['kpis']['total_revenue']);

        // Filter Branch B
        $resB = $this->actingAs($this->superAdmin)->get(route('admin.analytics.sales', ['branch_id' => $this->branchB->id]));
        $this->assertEquals(1, $resB->viewData('data')['kpis']['total_sales']);
        $this->assertEquals(250_000_000, $resB->viewData('data')['kpis']['total_revenue']);
    }

    /**
     * 5. Perhitungan rasio konversi CRM akurat (deals / total leads * 100).
     */
    public function test_05_crm_conversion_rate_calculation_is_accurate(): void
    {
        // Create 10 leads: 2 deal, 1 lost, 7 interested
        $this->createLead($this->branchA, 'deal');
        $this->createLead($this->branchA, 'deal');
        $this->createLead($this->branchA, 'lost');

        for ($i = 4; $i <= 10; $i++) {
            $this->createLead($this->branchA, 'interested');
        }

        $response = $this->actingAs($this->superAdmin)->get(route('admin.analytics.crm'));
        $response->assertStatus(200);

        $data = $response->viewData('data');
        $this->assertEquals(10, $data['kpis']['total_leads']);
        $this->assertEquals(2, $data['kpis']['deals']);
        $this->assertEquals(1, $data['kpis']['lost']);
        $this->assertEquals(20.0, $data['kpis']['conversion_rate']);
    }

    /**
     * 6. Perhitungan stok tersedia strictly sama dengan stok fisik dikurangi stok dipesan (physical - reserved).
     */
    public function test_06_available_stock_calculation_strictly_equals_physical_minus_reserved(): void
    {
        VehicleInventory::create([
            'vehicle_id' => $this->vehicle->id,
            'branch_id' => $this->branchA->id,
            'stock_quantity' => 15,
            'reserved_quantity' => 4,
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.analytics.inventory'));
        $response->assertStatus(200);

        $data = $response->viewData('data');
        $this->assertEquals(15, $data['kpis']['physical_stock']);
        $this->assertEquals(4, $data['kpis']['reserved_stock']);
        $this->assertEquals(11, $data['kpis']['available_stock']); // 15 - 4 = 11
    }

    /**
     * 7. Perhitungan transaksi pembayaran lunas dan nominal yang diterima akurat.
     */
    public function test_07_successful_payments_count_and_amount_accuracy(): void
    {
        $booking = $this->createBooking($this->branchA, 'confirmed', 'paid', 400_000_000);

        $this->createPayment($booking, 10_000_000, 'paid', 'bank_transfer');
        $this->createPayment($booking, 20_000_000, 'paid', 'qris');

        $response = $this->actingAs($this->superAdmin)->get(route('admin.analytics.payments'));
        $response->assertStatus(200);

        $data = $response->viewData('data');
        $this->assertEquals(2, $data['kpis']['paid_count']);
        $this->assertEquals(30_000_000, $data['kpis']['total_received']);
    }

    /**
     * 8. Pemisahan pembayaran pending dan failed sehingga tidak dihitung sebagai penerimaan lunas.
     */
    public function test_08_separation_of_pending_and_failed_payments_from_gross_revenue(): void
    {
        $booking = $this->createBooking($this->branchA, 'confirmed', 'paid', 400_000_000);

        // Paid: 10jt
        $this->createPayment($booking, 10_000_000, 'paid', 'bank_transfer');

        // Pending: 5jt
        $this->createPayment($booking, 5_000_000, 'pending', 'bank_transfer');

        // Failed: 5jt
        $this->createPayment($booking, 5_000_000, 'failed', 'bank_transfer');

        $response = $this->actingAs($this->superAdmin)->get(route('admin.analytics.payments'));
        $data = $response->viewData('data');

        $this->assertEquals(10_000_000, $data['kpis']['total_received']);
        $this->assertEquals(1, $data['kpis']['pending_count']);
        $this->assertEquals(5_000_000, $data['kpis']['total_pending']);
        $this->assertEquals(1, $data['kpis']['failed_count']);
        $this->assertEquals(5_000_000, $data['kpis']['total_failed']);
    }

    /**
     * 9. Perhitungan statistik bengkel dan after-sales akurat.
     */
    public function test_09_service_and_workshop_statistics_accuracy(): void
    {
        $serviceType = $this->createServiceType();

        ServiceBooking::create([
            'booking_code' => 'SRV-001',
            'user_id' => $this->customer->id,
            'branch_id' => $this->branchA->id,
            'service_type_id' => $serviceType->id,
            'vehicle_name' => 'Fortuner GR',
            'license_plate' => 'B 1234 TOY',
            'booking_date' => now()->toDateString(),
            'booking_time' => '09:00:00',
            'status' => 'confirmed',
        ]);

        // 1 completed service order: 1.500.000
        ServiceOrder::create([
            'order_number' => 'WO-001',
            'branch_id' => $this->branchA->id,
            'user_id' => $this->customer->id,
            'service_type_id' => $serviceType->id,
            'license_plate' => 'B 1234 TOY',
            'vehicle_name' => 'Fortuner GR',
            'status' => 'completed',
            'final_cost' => 1_500_000,
            'estimated_cost' => 1_500_000,
        ]);

        // 1 in_progress service order: 800.000 (not completed, should not add to revenue)
        ServiceOrder::create([
            'order_number' => 'WO-002',
            'branch_id' => $this->branchA->id,
            'user_id' => $this->customer->id,
            'service_type_id' => $serviceType->id,
            'license_plate' => 'B 5678 TOY',
            'vehicle_name' => 'Yaris Cross',
            'status' => 'in_progress',
            'estimated_cost' => 800_000,
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.analytics.after-sales'));
        $response->assertStatus(200);

        $data = $response->viewData('data');
        $this->assertEquals(1, $data['kpis']['total_bookings']);
        $this->assertEquals(1, $data['kpis']['completed_orders']);
        $this->assertEquals(1, $data['kpis']['active_orders']);
        $this->assertEquals(1_500_000, $data['kpis']['workshop_revenue']);
    }

    /**
     * 10. Super Admin memiliki akses global ke seluruh modul analitik dan komparasi dealer.
     */
    public function test_10_super_admin_has_global_access_to_all_analytics_and_dealers(): void
    {
        $this->actingAs($this->superAdmin)->get(route('admin.analytics.dashboard'))->assertStatus(200);
        $this->actingAs($this->superAdmin)->get(route('admin.analytics.sales'))->assertStatus(200);
        $this->actingAs($this->superAdmin)->get(route('admin.analytics.crm'))->assertStatus(200);
        $this->actingAs($this->superAdmin)->get(route('admin.analytics.dealers'))->assertStatus(200);
        $this->actingAs($this->superAdmin)->get(route('admin.analytics.inventory'))->assertStatus(200);
        $this->actingAs($this->superAdmin)->get(route('admin.analytics.payments'))->assertStatus(200);
        $this->actingAs($this->superAdmin)->get(route('admin.analytics.after-sales'))->assertStatus(200);
        $this->actingAs($this->superAdmin)->get(route('admin.analytics.customers'))->assertStatus(200);
        $this->actingAs($this->superAdmin)->get(route('admin.reports.index'))->assertStatus(200);
    }

    /**
     * 11. Dealer Manager terisolasi pada cabang dealernya sendiri.
     */
    public function test_11_dealer_manager_is_scoped_to_their_own_branch(): void
    {
        // Sale at Branch A
        $this->createBooking($this->branchA, 'confirmed', 'paid', 500_000_000);

        // Sale at Branch B
        $this->createBooking($this->branchB, 'confirmed', 'paid', 900_000_000);

        // Dealer Manager from Branch A requests analytics dashboard with query branch_id=Branch B
        // ScopeFilter must override requested branch with manager's actual branch_id
        $response = $this->actingAs($this->dealerManager)->get(route('admin.analytics.dashboard', [
            'branch_id' => $this->branchB->id,
        ]));
        $response->assertStatus(200);

        $data = $response->viewData('data');
        $this->assertEquals(1, $data['overview']['sales_count']['value']);
        $this->assertEquals(500_000_000, $data['overview']['sales_revenue']['value']);
    }

    /**
     * 12. Sales Executive terisolasi pada lead dan prospek yang ditugaskan kepadanya.
     */
    public function test_12_sales_executive_is_scoped_to_assigned_leads(): void
    {
        $otherSales = User::create([
            'name' => 'Budi Sales Lain',
            'email' => 'budi.other@toyota-autoconnect.id',
            'phone' => '08110000099',
            'password' => bcrypt('password123'),
            'is_active' => true,
            'branch_id' => $this->branchA->id,
        ]);
        $otherSales->assignRole('Sales Executive');

        // Lead assigned to Andi Sales
        $customerAndi = User::create([
            'name' => 'Prospek Andi',
            'email' => 'andi.prospek@example.com',
            'password' => bcrypt('secret123'),
        ]);
        $customerAndi->assignRole('Customer');

        Lead::create([
            'lead_number' => 'LD-' . date('Ymd') . '-901',
            'customer_id' => $customerAndi->id,
            'assigned_to' => $this->salesExecutive->id,
            'status' => 'deal',
        ]);

        // Lead assigned to Budi Sales Lain
        $customerBudi = User::create([
            'name' => 'Prospek Budi',
            'email' => 'budi.prospek@example.com',
            'password' => bcrypt('secret123'),
        ]);
        $customerBudi->assignRole('Customer');

        Lead::create([
            'lead_number' => 'LD-' . date('Ymd') . '-902',
            'customer_id' => $customerBudi->id,
            'assigned_to' => $otherSales->id,
            'status' => 'deal',
        ]);

        // Andi Sales accesses CRM analytics
        $response = $this->actingAs($this->salesExecutive)->get(route('admin.analytics.crm'));
        $response->assertStatus(200);

        $data = $response->viewData('data');
        $this->assertEquals(1, $data['kpis']['total_leads']);
        $this->assertEquals('Prospek Andi', $data['leads']->first()->customer->name);
    }

    /**
     * 13. Role yang tidak berwenang dilarang mengakses modul analitik tertentu (403 Forbidden).
     */
    public function test_13_unauthorized_role_gets_403_forbidden(): void
    {
        // Inventory Officer cannot access sales analytics
        $this->actingAs($this->inventoryOfficer)->get(route('admin.analytics.sales'))->assertStatus(403);

        // Service Advisor cannot access sales analytics
        $this->actingAs($this->serviceAdvisor)->get(route('admin.analytics.sales'))->assertStatus(403);

        // Sales Executive cannot access dealer comparison ranking
        $this->actingAs($this->salesExecutive)->get(route('admin.analytics.dealers'))->assertStatus(403);

        // Regular customer cannot access executive dashboard
        $this->actingAs($this->customer)->get(route('admin.analytics.dashboard'))->assertStatus(403);
    }

    /**
     * 14. Ekspor laporan CSV mengalirkan data RFC 4180 dengan UTF-8 BOM dan header yang tepat.
     */
    public function test_14_csv_export_returns_streamed_rfc4180_with_utf8_bom_and_proper_data(): void
    {
        $this->createBooking($this->branchA, 'confirmed', 'paid', 740_000_000);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.reports.export', [
            'type' => 'sales',
            'period' => 'this_month',
        ]));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
        $this->assertTrue(str_contains($response->headers->get('content-disposition'), 'attachment'));
        $this->assertTrue(str_contains($response->headers->get('content-disposition'), 'laporan-sales-'));

        // Capture streamed response content
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        // Check UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        // Check content
        $this->assertStringContainsString('All New Fortuner GR Sport', $content);
        $this->assertStringContainsString('740.000.000', $content);
    }

    /**
     * 15. Penanganan state kosong tanpa pembagian dengan nol saat belum ada data transaksi.
     */
    public function test_15_empty_state_handling_renders_gracefully_without_division_by_zero(): void
    {
        // No sales, no leads, no payments, no bookings
        $resDashboard = $this->actingAs($this->superAdmin)->get(route('admin.analytics.dashboard'));
        $resDashboard->assertStatus(200);
        $this->assertEquals(0, $resDashboard->viewData('data')['overview']['sales_count']['value']);

        $resSales = $this->actingAs($this->superAdmin)->get(route('admin.analytics.sales'));
        $resSales->assertStatus(200);
        $this->assertEquals(0, $resSales->viewData('data')['kpis']['total_sales']);

        $resCrm = $this->actingAs($this->superAdmin)->get(route('admin.analytics.crm'));
        $resCrm->assertStatus(200);
        $this->assertEquals(0.0, $resCrm->viewData('data')['kpis']['conversion_rate']);

        $resReports = $this->actingAs($this->superAdmin)->get(route('admin.reports.index', ['type' => 'sales']));
        $resReports->assertStatus(200);
        $this->assertEmpty($resReports->viewData('reportData')['rows']);
    }
}
