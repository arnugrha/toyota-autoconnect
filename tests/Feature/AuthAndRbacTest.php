<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthAndRbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed standard roles and permissions into in-memory testing database
        $this->seed(RoleAndPermissionSeeder::class);
    }

    /**
     * 1. Pengunjung dapat membuka halaman login.
     */
    public function test_visitor_can_view_login_page(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Masuk ke Akun');
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
    }

    /**
     * 2. Pengunjung dapat membuka halaman registrasi.
     */
    public function test_visitor_can_view_register_page(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('Buat Akun Pelanggan');
        $response->assertSee('name="name"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="phone"', false);
        $response->assertSee('name="password"', false);
        $response->assertSee('name="password_confirmation"', false);
    }

    /**
     * 3. Customer dapat mendaftar dengan input valid.
     */
    public function test_customer_can_register_with_valid_input(): void
    {
        $payload = [
            'name' => 'Ahmad Pelanggan',
            'email' => 'ahmad.pelanggan@example.com',
            'phone' => '081298765432',
            'password' => 'Rahasia1234!',
            'password_confirmation' => 'Rahasia1234!',
            'terms' => '1',
        ];

        $response = $this->post(route('register'), $payload);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'name' => 'Ahmad Pelanggan',
            'email' => 'ahmad.pelanggan@example.com',
            'phone' => '081298765432',
            'is_active' => true,
        ]);

        $user = User::where('email', 'ahmad.pelanggan@example.com')->first();
        $this->assertTrue(Hash::check('Rahasia1234!', $user->password));
    }

    /**
     * 4. Email duplikat ditolak saat registrasi.
     */
    public function test_duplicate_email_registration_is_rejected(): void
    {
        User::factory()->create([
            'email' => 'terdaftar@example.com',
        ]);

        $payload = [
            'name' => 'Duplikat User',
            'email' => 'terdaftar@example.com',
            'phone' => '081200000000',
            'password' => 'Rahasia1234!',
            'password_confirmation' => 'Rahasia1234!',
            'terms' => '1',
        ];

        $response = $this->post(route('register'), $payload);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertEquals(1, User::where('email', 'terdaftar@example.com')->count());
    }

    /**
     * 5. Password yang tidak valid ditolak saat registrasi.
     */
    public function test_invalid_password_registration_is_rejected(): void
    {
        // Password too short (< 8 chars)
        $shortPwResponse = $this->post(route('register'), [
            'name' => 'Testing Password',
            'email' => 'shortpw@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
            'terms' => '1',
        ]);
        $shortPwResponse->assertSessionHasErrors('password');

        // Password confirmation mismatch
        $mismatchResponse = $this->post(route('register'), [
            'name' => 'Testing Password',
            'email' => 'mismatch@example.com',
            'password' => 'Password1234!',
            'password_confirmation' => 'DifferentPassword1234!',
            'terms' => '1',
        ]);
        $mismatchResponse->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /**
     * 6. Registrasi publik selalu memberikan role Customer.
     */
    public function test_public_registration_always_assigns_customer_role(): void
    {
        $this->post(route('register'), [
            'name' => 'Budi Customer',
            'email' => 'budi@customer.test',
            'password' => 'PasswordBudi123!',
            'password_confirmation' => 'PasswordBudi123!',
            'terms' => '1',
        ]);

        $user = User::where('email', 'budi@customer.test')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('Customer'));
        $this->assertFalse($user->hasRole('Super Admin'));
    }

    /**
     * 7. Request registrasi tidak dapat memilih role Super Admin (Privilege Escalation Protection).
     */
    public function test_registration_request_cannot_assign_super_admin_role(): void
    {
        $this->post(route('register'), [
            'name' => 'Hacker Wannabe',
            'email' => 'hacker@tryhard.com',
            'role' => 'Super Admin',
            'roles' => ['Super Admin'],
            'is_admin' => 1,
            'password' => 'HackedPassword123!',
            'password_confirmation' => 'HackedPassword123!',
            'terms' => '1',
        ]);

        $user = User::where('email', 'hacker@tryhard.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('Customer'));
        $this->assertFalse($user->hasRole('Super Admin'));
    }

    /**
     * 8. Login dengan kredensial benar berhasil.
     */
    public function test_login_with_valid_credentials_succeeds(): void
    {
        $user = User::factory()->customer()->create([
            'email' => 'user.login@test.com',
            'password' => Hash::make('ValidPass123!'),
            'is_active' => true,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'user.login@test.com',
            'password' => 'ValidPass123!',
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * 9. Login dengan kredensial salah ditolak secara aman.
     */
    public function test_login_with_invalid_credentials_fails(): void
    {
        User::factory()->customer()->create([
            'email' => 'user.real@test.com',
            'password' => Hash::make('ValidPass123!'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'user.real@test.com',
            'password' => 'WrongPassword!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * 10. Pengguna dapat logout dan session di-invalidate.
     */
    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }

    /**
     * 11. Pengguna yang belum login tidak dapat mengakses dashboard internal.
     */
    public function test_unauthenticated_user_cannot_access_internal_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    /**
     * 12. Customer tidak dapat mengakses halaman Super Admin atau staf (/admin/users).
     */
    public function test_customer_cannot_access_super_admin_users_page(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    /**
     * 13. Staf tanpa permission tidak dapat melakukan tindakan terlarang.
     */
    public function test_staff_without_permission_cannot_perform_forbidden_actions(): void
    {
        // Content Manager has content permissions, but lacks users.view and users.create
        $contentManager = User::factory()->withRole('Content Manager')->create();

        $this->assertFalse($contentManager->hasPermissionTo('users.create'));

        $response = $this->actingAs($contentManager)->get(route('admin.users.create'));

        $response->assertStatus(403);
    }

    /**
     * 14. Super Admin dapat mengakses halaman pengelolaan pengguna.
     */
    public function test_super_admin_can_access_user_management_page(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Akun Pengguna &amp; Staf', false);
    }

    /**
     * 15. Pengguna tidak dapat mengubah role dirinya melalui profil.
     */
    public function test_user_cannot_escalate_role_via_profile_update(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->put(route('profile.update'), [
            'name' => 'Customer Berubah Nama',
            'phone' => '081234567890',
            'role' => 'Super Admin',
            'roles' => ['Super Admin'],
        ]);

        $response->assertSessionHas('status');
        $customer->refresh();

        $this->assertEquals('Customer Berubah Nama', $customer->name);
        $this->assertTrue($customer->hasRole('Customer'));
        $this->assertFalse($customer->hasRole('Super Admin'));
    }

    /**
     * 16. Profil pengguna hanya dapat diubah sesuai aturan otorisasi dan kata sandi saat ini diverifikasi.
     */
    public function test_user_profile_and_password_update_rules(): void
    {
        $user = User::factory()->customer()->create([
            'password' => Hash::make('OldPassword123!'),
        ]);

        // Attempt password change with WRONG current password
        $failResponse = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'WrongOldPassword!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $failResponse->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('OldPassword123!', $user->fresh()->password));

        // Attempt password change with CORRECT current password
        $successResponse = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $successResponse->assertSessionHas('password_status');
        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
    }

    /**
     * 17. Halaman publik dan katalog Tahap 2 tetap dapat diakses.
     */
    public function test_public_pages_and_catalog_remain_accessible(): void
    {
        $vehicle = Vehicle::factory()->create([
            'name' => 'Toyota Yaris Cross HEV',
            'slug' => 'toyota-yaris-cross-hev',
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('home'))->assertStatus(200);
        $this->get(route('vehicles.index'))->assertStatus(200);
        $this->get(route('vehicles.show', $vehicle->slug))->assertStatus(200);
        $this->get(route('promo'))->assertStatus(200);
        $this->get(route('about'))->assertStatus(200);
        $this->get(route('contact'))->assertStatus(200);
    }

    /**
     * 18. Route dan middleware tidak menghasilkan redirect loop.
     */
    public function test_routes_and_middleware_do_not_produce_redirect_loops(): void
    {
        $customer = User::factory()->customer()->create();
        $superAdmin = User::factory()->superAdmin()->create();

        // Already logged in Customer visiting /login -> redirected to customer.dashboard
        $resp1 = $this->actingAs($customer)->get(route('login'));
        $resp1->assertRedirect(route('customer.dashboard'));

        // Customer opening customer.dashboard -> 200 OK (no further redirect)
        $this->actingAs($customer)->get(route('customer.dashboard'))->assertStatus(200);

        // Customer attempting staff dashboard -> redirected to customer.dashboard (not infinite loop)
        $resp2 = $this->actingAs($customer)->get(route('dashboard'));
        $resp2->assertRedirect(route('customer.dashboard'));

        // Already logged in Super Admin visiting /login -> redirected to admin.dashboard
        $resp3 = $this->actingAs($superAdmin)->get(route('login'));
        $resp3->assertRedirect(route('admin.dashboard'));

        // Super Admin opening dashboard -> 200 OK
        $this->actingAs($superAdmin)->get(route('admin.dashboard'))->assertStatus(200);

        // Logout before testing unauthenticated login flows
        $this->post(route('logout'));
        $this->assertGuest();

        // Deactivated user cannot login
        $inactiveUser = User::factory()->inactive()->customer()->create([
            'email' => 'inactive@test.com',
            'password' => Hash::make('Password123!'),
        ]);

        $respInactive = $this->post(route('login'), [
            'email' => 'inactive@test.com',
            'password' => 'Password123!',
        ]);

        $respInactive->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * 19. Halaman login petugas (/admin/login) dapat dirender dengan branding portal staf.
     */
    public function test_admin_login_page_renders_cleanly_with_staff_branding(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertSee('PORTAL OPERASIONAL &amp; DEALER', false);
        $response->assertSee('Masuk Portal Petugas');
        $response->assertSee(route('admin.login'));
        $response->assertSee('Masuk sebagai Pelanggan');
    }

    /**
     * 20. Staf dan Super Admin dapat login melalui route /admin/login dan diarahkan ke admin dashboard.
     */
    public function test_staff_can_login_via_admin_login_route(): void
    {
        $sales = User::factory()->withRole('Sales Executive')->create([
            'email' => 'sales.login.test@toyota.id',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->post(route('admin.login'), [
            'email' => 'sales.login.test@toyota.id',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($sales);
    }

    /**
     * 21. Pelanggan ditolak jika mencoba login melalui route /admin/login.
     */
    public function test_customer_cannot_login_via_admin_login_route(): void
    {
        $customer = User::factory()->customer()->create([
            'email' => 'customer.blocked@gmail.com',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->post(route('admin.login'), [
            'email' => 'customer.blocked@gmail.com',
            'password' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('khusus untuk login petugas', session('errors')->first('email'));
        $this->assertGuest();
    }

    /**
     * 22. Pengguna yang sudah login diarahkan secara sesuai saat membuka /admin/login.
     */
    public function test_authenticated_users_are_redirected_appropriately_on_admin_login(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $customer = User::factory()->customer()->create();

        // Staf membuka /admin/login -> dialihkan ke admin dashboard
        $this->actingAs($superAdmin)
            ->get(route('admin.login'))
            ->assertRedirect(route('admin.dashboard'));

        // Pelanggan membuka /admin/login -> dialihkan ke customer dashboard
        $this->actingAs($customer)
            ->get(route('admin.login'))
            ->assertRedirect(route('customer.dashboard'));
    }
}
