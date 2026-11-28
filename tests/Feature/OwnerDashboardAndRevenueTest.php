<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Payment;
use App\Models\ServiceOrder;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleBooking;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerDashboardAndRevenueTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        $this->owner = User::where('email', 'owner@toyota-autoconnect.id')->first()
            ?? User::factory()->create(['name' => 'Owner Toyota'])->assignRole('Owner');

        $this->branch = Branch::firstOrCreate(
            ['code' => 'JKT-01'],
            [
                'name' => 'Toyota Sudirman Jakarta',
                'slug' => 'toyota-sudirman-jakarta',
                'city' => 'Jakarta Selatan',
                'address' => 'Jl. Jend. Sudirman Kav. 1',
                'phone' => '021-5551234',
                'email' => 'sudirman@toyota.id',
                'is_active' => true,
            ]
        );
    }

    /**
     * 1. Owner redirects straight to revenue & profit hub upon accessing dashboard.
     */
    public function test_owner_is_redirected_to_revenue_monitoring(): void
    {
        $response = $this->actingAs($this->owner)->get(route('dashboard'));

        $response->assertRedirect(route('admin.owner.revenue'));
    }

    /**
     * 2. Owner revenue page displays total revenue, gross profit, net profit, and business breakdown.
     */
    public function test_owner_revenue_page_displays_revenue_profit_and_business_metrics(): void
    {
        $customer = User::factory()->customer()->create(['name' => 'Budi Pembeli']);

        $vehicle = Vehicle::factory()->create([
            'name' => 'Innova Zenix Hybrid',
            'price' => 450000000,
        ]);

        // Create confirmed SPK unit booking using factory
        $booking = VehicleBooking::factory()->create([
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'branch_id' => $this->branch->id,
            'vehicle_price' => 450000000,
            'booking_fee' => 5000000,
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);

        // Create settled payment
        Payment::create([
            'code' => Payment::generateCode(),
            'order_id' => 'PAY-TEST-001',
            'vehicle_booking_id' => $booking->id,
            'user_id' => $customer->id,
            'amount' => 5000000,
            'payment_type' => 'booking_fee',
            'payment_method' => 'bank_transfer',
            'status' => 'paid',
            'transaction_time' => now(),
        ]);

        // Create completed service order
        $serviceType = ServiceType::firstOrCreate(
            ['code' => 'SB-01'],
            [
                'name' => 'Servis Berkala 10.000 KM',
                'slug' => 'servis-berkala-10000-km',
                'description' => 'Servis berkala',
                'estimated_duration_minutes' => 60,
                'estimated_cost' => 1200000,
                'is_active' => true,
            ]
        );

        ServiceOrder::create([
            'order_number' => 'WO-TEST-001',
            'user_id' => $customer->id,
            'branch_id' => $this->branch->id,
            'service_type_id' => $serviceType->id,
            'vehicle_name' => 'Innova Zenix',
            'license_plate' => 'B 1234 TOY',
            'status' => 'completed',
            'labor_cost' => 500000,
            'parts_cost' => 700000,
            'final_cost' => 1200000,
        ]);

        $response = $this->actingAs($this->owner)->get(route('admin.owner.revenue'));

        $response->assertOk();
        $response->assertSee('Total Omzet Bisnis');
        $response->assertSee('Estimasi Laba Kotor');
        $response->assertSee('Proyeksi Laba Bersih');
        $response->assertSee('Kas Masuk Terealisasi');
        $response->assertSee('Performa Pendapatan Berdasarkan Cabang Dealer');
        $response->assertSee('Estimasi Laba');
    }

    /**
     * 3. Owner sidebar only shows revenue, profit, analytics, and business reports menus.
     *    Operational menus (spare parts, work orders, leads, support tickets, system settings) are omitted.
     */
    public function test_owner_sidebar_contains_only_financial_and_revenue_menus(): void
    {
        $response = $this->actingAs($this->owner)->get(route('admin.owner.revenue'));

        $response->assertOk();

        // Allowed Financial & Revenue Menus
        $response->assertSee('Portal Finansial');
        $response->assertSee('Pendapatan &amp; Laba', false);
        $response->assertSee('Dashboard Eksekutif');
        $response->assertSee('Analisis Penjualan Unit');
        $response->assertSee('Komparasi Omzet Cabang');
        $response->assertSee('Arus Kas Pembayaran');
        $response->assertSee('Laporan Keuangan &amp; Omzet', false);

        // Forbidden Operational Menus for Owner
        $response->assertDontSee('Kelola Sparepart');
        $response->assertDontSee('Pengadaan Sparepart');
        $response->assertDontSee('Stok Sparepart');
        $response->assertDontSee('Manajemen Pengguna');
        $response->assertDontSee('Pusat Tiket CS');
        $response->assertDontSee('CRM &amp; Prospek Lead', false);
        $response->assertDontSee('Perintah Kerja (WO)');
        $response->assertDontSee('Pengaturan Sistem');
    }

    /**
     * 4. Owner cannot access operational spare parts management.
     */
    public function test_owner_cannot_access_spare_parts_management(): void
    {
        $response = $this->actingAs($this->owner)->get(route('admin.spare-parts.index'));

        $response->assertForbidden();
    }
}
