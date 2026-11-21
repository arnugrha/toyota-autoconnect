<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Inquiry;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleInventory;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VehicleAndInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    /**
     * 1. Pengguna berwenang (Inventory Officer / Super Admin) dapat menambah kendaraan baru.
     */
    public function test_authorized_user_can_create_vehicle(): void
    {
        Storage::fake('public');

        $admin = User::factory()->withRole('Super Admin')->create();
        $category = VehicleCategory::create(['name' => 'MPV', 'slug' => 'mpv']);
        $branch = Branch::create([
            'name' => 'Cabang Pusat',
            'code' => 'AT-JKT-01',
            'address' => 'Jl. Sudirman',
            'city' => 'Jakarta',
            'phone' => '021-123456',
        ]);

        $file = UploadedFile::fake()->image('fortuner.jpg', 800, 600);

        $response = $this->actingAs($admin)->post(route('admin.vehicles.store'), [
            'name' => 'Toyota Fortuner 2.8 GR Sport',
            'slug' => 'toyota-fortuner-28-gr-sport',
            'category_id' => $category->id,
            'variant' => '4x4 AT',
            'price' => 750000000,
            'status' => 'available',
            'publication_status' => 'published',
            'is_featured' => 1,
            'primary_image' => $file,
        ]);

        $response->assertRedirect(route('admin.vehicles.index'));
        $this->assertDatabaseHas('vehicles', [
            'name' => 'Toyota Fortuner 2.8 GR Sport',
            'slug' => 'toyota-fortuner-28-gr-sport',
            'publication_status' => 'published',
        ]);

        // Verifikasi inventaris otomatis diinisialisasi
        $this->assertDatabaseHas('vehicle_inventories', [
            'branch_id' => $branch->id,
            'stock_quantity' => 0,
        ]);
    }

    /**
     * 2. Slug kendaraan harus unik.
     */
    public function test_vehicle_slug_must_be_unique(): void
    {
        $admin = User::factory()->withRole('Super Admin')->create();
        Vehicle::factory()->create([
            'name' => 'Toyota Yaris Cross',
            'slug' => 'toyota-yaris-cross',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.vehicles.store'), [
            'name' => 'Toyota Yaris Cross Duplikat',
            'slug' => 'toyota-yaris-cross', // Duplicate
            'price' => 350000000,
            'status' => 'available',
            'publication_status' => 'published',
        ]);

        $response->assertSessionHasErrors('slug');
    }

    /**
     * 3. Kendaraan berstatus draft tidak tampil pada katalog publik.
     */
    public function test_draft_vehicle_does_not_appear_on_public_catalog(): void
    {
        $publishedVehicle = Vehicle::factory()->create([
            'name' => 'Mobil Tayang Publik',
            'slug' => 'mobil-tayang-publik',
            'publication_status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $draftVehicle = Vehicle::factory()->create([
            'name' => 'Mobil Rahasia Draft',
            'slug' => 'mobil-rahasia-draft',
            'publication_status' => 'draft',
            'published_at' => null,
        ]);

        $response = $this->get(route('vehicles.index'));
        $response->assertStatus(200);
        $response->assertSee('Mobil Tayang Publik');
        $response->assertDontSee('Mobil Rahasia Draft');

        // Akses detail draft menghasilkan 404 Not Found
        $detailResponse = $this->get(route('vehicles.show', 'mobil-rahasia-draft'));
        $detailResponse->assertStatus(404);
    }

    /**
     * 4. Pengguna tanpa permission tidak dapat mengelola master kendaraan.
     */
    public function test_unauthorized_user_cannot_manage_vehicles(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)->get(route('admin.vehicles.index'))->assertStatus(403);
        $this->actingAs($customer)->get(route('admin.vehicles.create'))->assertStatus(403);
        $this->actingAs($customer)->post(route('admin.vehicles.store'), [])->assertStatus(403);
    }

    /**
     * 5. Inventory kendaraan dapat dibuat untuk beberapa cabang tanpa duplikasi.
     */
    public function test_vehicle_can_have_inventory_in_multiple_branches_without_duplication(): void
    {
        $vehicle = Vehicle::factory()->create(['name' => 'Toyota Raize Turbo']);
        $branchA = Branch::create(['name' => 'Cabang A', 'code' => 'BR-A', 'address' => 'A', 'city' => 'Jakarta', 'phone' => '1']);
        $branchB = Branch::create(['name' => 'Cabang B', 'code' => 'BR-B', 'address' => 'B', 'city' => 'Surabaya', 'phone' => '2']);

        $invA = VehicleInventory::create([
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branchA->id,
            'stock_quantity' => 10,
            'reserved_quantity' => 2,
        ]);

        $invB = VehicleInventory::create([
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branchB->id,
            'stock_quantity' => 5,
            'reserved_quantity' => 0,
        ]);

        $this->assertEquals(2, $vehicle->inventories()->count());
        $this->assertEquals(15, $vehicle->total_stock);
        // Total available = (10-2) + (5-0) = 8 + 5 = 13
        $this->assertEquals(13, $vehicle->total_available_stock);

        // Percobaan duplikasi vehicle_id & branch_id akan gagal
        $this->expectException(QueryException::class);
        VehicleInventory::create([
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branchA->id,
            'stock_quantity' => 1,
        ]);
    }

    /**
     * 6. Penyesuaian stok menambah kuantitas dan tercatat dalam riwayat pergerakan.
     */
    public function test_stock_adjustment_adds_stock_and_logs_movement(): void
    {
        $inventoryOfficer = User::factory()->withRole('Inventory Officer')->create();
        $vehicle = Vehicle::factory()->create();
        $branch = Branch::create(['name' => 'Cabang Bandung', 'code' => 'BR-BDG', 'address' => 'Dago', 'city' => 'Bandung', 'phone' => '022']);

        $response = $this->actingAs($inventoryOfficer)->post(route('admin.inventory.adjust.store'), [
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'type' => 'add',
            'quantity' => 5,
            'reason' => 'Penerimaan unit delivery dari pabrik.',
        ]);

        $response->assertRedirect(route('admin.inventory.index'));

        $this->assertDatabaseHas('vehicle_inventories', [
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'stock_quantity' => 5,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'movement_type' => 'adjustment_in',
            'quantity' => 5,
            'stock_before' => 0,
            'stock_after' => 5,
            'reason' => 'Penerimaan unit delivery dari pabrik.',
            'user_id' => $inventoryOfficer->id,
        ]);
    }

    /**
     * 7. Pengurangan stok tidak boleh membuat stok fisik lebih rendah dari stok yang dialokasikan (reserved).
     */
    public function test_stock_subtraction_cannot_go_below_reserved_quantity(): void
    {
        $inventoryOfficer = User::factory()->withRole('Inventory Officer')->create();
        $vehicle = Vehicle::factory()->create();
        $branch = Branch::create(['name' => 'Cabang Solo', 'code' => 'BR-SLO', 'address' => 'Slamet Riyadi', 'city' => 'Solo', 'phone' => '0271']);

        // Set initial stock: 5 fisik, 3 reserved (alokasi booking)
        VehicleInventory::create([
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'stock_quantity' => 5,
            'reserved_quantity' => 3,
        ]);

        // Coba kurangi 3 unit (sisa fisik 2 < reserved 3) -> DITOLAK
        $response = $this->actingAs($inventoryOfficer)->post(route('admin.inventory.adjust.store'), [
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'type' => 'subtract',
            'quantity' => 3,
            'reason' => 'Retur unit cacat',
        ]);

        $response->assertSessionHasErrors('quantity');

        // Stok fisik tidak berubah
        $inv = VehicleInventory::where('vehicle_id', $vehicle->id)->where('branch_id', $branch->id)->first();
        $this->assertEquals(5, $inv->stock_quantity);
    }

    /**
     * 8. Pengguna tanpa izin inventory.manage tidak dapat melakukan penyesuaian stok.
     */
    public function test_unauthorized_staff_cannot_adjust_stock(): void
    {
        $salesExec = User::factory()->withRole('Sales Executive')->create();
        $vehicle = Vehicle::factory()->create();
        $branch = Branch::create(['name' => 'Cabang Medan', 'code' => 'BR-MDN', 'address' => 'Gatot Subroto', 'city' => 'Medan', 'phone' => '061']);

        $response = $this->actingAs($salesExec)->post(route('admin.inventory.adjust.store'), [
            'vehicle_id' => $vehicle->id,
            'branch_id' => $branch->id,
            'type' => 'add',
            'quantity' => 2,
            'reason' => 'Bypass attempt',
        ]);

        $response->assertStatus(403);
    }

    /**
     * 9. Filter cabang pada katalog publik berjalan dengan benar.
     */
    public function test_public_catalog_can_filter_by_branch_inventory(): void
    {
        $branchA = Branch::create(['name' => 'Cabang Semarang', 'code' => 'BR-SMG', 'address' => 'Pemuda', 'city' => 'Semarang', 'phone' => '024']);
        $branchB = Branch::create(['name' => 'Cabang Jogja', 'code' => 'BR-JOG', 'address' => 'Malioboro', 'city' => 'Yogyakarta', 'phone' => '0274']);

        $vehicleInSemarang = Vehicle::factory()->create([
            'name' => 'Innova Semarang Ready',
            'publication_status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $vehicleInJogja = Vehicle::factory()->create([
            'name' => 'Avanza Jogja Ready',
            'publication_status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        // Semarang stock
        VehicleInventory::create([
            'vehicle_id' => $vehicleInSemarang->id,
            'branch_id' => $branchA->id,
            'stock_quantity' => 4,
            'reserved_quantity' => 0,
        ]);

        // Jogja stock
        VehicleInventory::create([
            'vehicle_id' => $vehicleInJogja->id,
            'branch_id' => $branchB->id,
            'stock_quantity' => 3,
            'reserved_quantity' => 0,
        ]);

        // Filter katalog hanya untuk branch Semarang
        $response = $this->get(route('vehicles.index', ['branch_id' => $branchA->id]));
        $response->assertStatus(200);
        $response->assertSee('Innova Semarang Ready');
        $response->assertDontSee('Avanza Jogja Ready');
    }

    /**
     * 10. Kendaraan yang direferensikan inquiry/lead tidak dapat dihapus permanen melainkan diarsipkan.
     */
    public function test_vehicle_referenced_by_lead_is_archived_instead_of_hard_deleted(): void
    {
        $admin = User::factory()->withRole('Super Admin')->create();
        $customer = User::factory()->customer()->create();

        $vehicle = Vehicle::factory()->create(['name' => 'Toyota Vios Generasi 4']);

        // Buat inquiry & lead yang mengacu pada kendaraan ini
        Inquiry::create([
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'customer_name' => 'Pelanggan Uji',
            'customer_email' => 'uji@customer.test',
            'subject' => 'Tanya Vios',
            'message' => 'Detail harga OTR',
            'status' => 'new',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.vehicles.destroy', $vehicle));

        $response->assertSessionHas('info');

        // Kendaraan tetap ada namun statusnya menjadi archived
        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'publication_status' => 'archived',
        ]);
    }
}
