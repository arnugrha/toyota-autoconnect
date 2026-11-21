<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\NotificationLog;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Notifications\ToyotaUniversalNotification;
use App\Services\NotificationService;
use App\Services\WhatsApp\Adapters\MetaWhatsAppService;
use App\Services\WhatsApp\WhatsAppNotificationService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationCenterAndIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $otherCustomer;
    protected User $superAdmin;
    protected User $dealerManager;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        $this->branch = Branch::create([
            'code' => 'JKT-01',
            'name' => 'AutoConnect Jakarta Pusat',
            'slug' => 'autoconnect-jakarta-pusat',
            'city' => 'Jakarta Pusat',
            'address' => 'Jl. Sudirman No. 1',
            'phone' => '021-1234567',
            'email' => 'jakpus@toyota-autoconnect.id',
            'is_active' => true,
        ]);

        $this->customer = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@example.com',
            'phone' => '081234567890',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $this->customer->assignRole('Customer');

        $this->otherCustomer = User::create([
            'name' => 'Dewi Lestari',
            'email' => 'dewi.lestari@example.com',
            'phone' => '089876543210',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $this->otherCustomer->assignRole('Customer');

        $this->superAdmin = User::create([
            'name' => 'Super Administrator',
            'email' => 'admin@toyota-autoconnect.id',
            'phone' => '08111222333',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $this->superAdmin->assignRole('Super Admin');

        $this->dealerManager = User::create([
            'name' => 'Dealer Manager Jakpus',
            'email' => 'manager@toyota-autoconnect.id',
            'phone' => '08222333444',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $this->dealerManager->assignRole('Dealer Manager');
    }

    /**
     * Test Notification Center page displays notifications and category filters.
     */
    public function test_notification_center_index_displays_user_notifications_with_category_counts(): void
    {
        $this->customer->notify(new ToyotaUniversalNotification(
            title: 'SPK Berhasil Dibuat',
            message: 'Pemesanan unit Toyota Kijang Innova Zenix Anda telah kami terima.',
            category: 'vehicle_booking',
            actionUrl: '/customer/bookings/1',
            enforcedChannels: ['database']
        ));

        $this->customer->notify(new ToyotaUniversalNotification(
            title: 'Pembayaran Dikonfirmasi',
            message: 'Pembayaran tanda jadi sebesar Rp 5.000.000 telah diverifikasi.',
            category: 'payment',
            enforcedChannels: ['database']
        ));

        $response = $this->actingAs($this->customer)
            ->get(route('notifications.index'));

        $response->assertOk();
        $response->assertSee('Pusat Notifikasi');
        $response->assertSee('SPK Berhasil Dibuat');
        $response->assertSee('Pembayaran Dikonfirmasi');
        $response->assertSee('Pemesanan SPK');
        $response->assertSee('Pembayaran');
    }

    /**
     * Test dropdown endpoint returns JSON payload with unread count.
     */
    public function test_notification_dropdown_returns_valid_json_payload(): void
    {
        $this->customer->notify(new ToyotaUniversalNotification(
            title: 'Jadwal Servis Dikonfirmasi',
            message: 'Booking servis Anda untuk tanggal besok telah disetujui Service Advisor.',
            category: 'service',
            enforcedChannels: ['database']
        ));

        $response = $this->actingAs($this->customer)
            ->getJson(route('notifications.dropdown'));

        $response->assertOk();
        $response->assertJsonStructure([
            'unread_count',
            'notifications' => [
                '*' => ['id', 'title', 'message', 'category', 'is_unread'],
            ],
        ]);
        $this->assertEquals(1, $response->json('unread_count'));
    }

    /**
     * Test marking a single notification as read.
     */
    public function test_mark_single_notification_as_read_and_redirects(): void
    {
        $this->customer->notify(new ToyotaUniversalNotification(
            title: 'Test Drive Dikonfirmasi',
            message: 'Jadwal test drive All New bZ4X Anda telah siap.',
            category: 'test_drive',
            actionUrl: '/customer/test-drives/5',
            enforcedChannels: ['database']
        ));

        $notification = $this->customer->notifications()->first();
        $this->assertNull($notification->read_at);

        $response = $this->actingAs($this->customer)
            ->post(route('notifications.mark-read', $notification->id));

        $response->assertRedirect('/customer/test-drives/5');
        $this->assertNotNull($notification->fresh()->read_at);
    }

    /**
     * Test marking all notifications as read.
     */
    public function test_mark_all_notifications_as_read(): void
    {
        $this->customer->notify(new ToyotaUniversalNotification(
            title: 'Notif 1',
            message: 'Pesan 1',
            enforcedChannels: ['database']
        ));
        $this->customer->notify(new ToyotaUniversalNotification(
            title: 'Notif 2',
            message: 'Pesan 2',
            enforcedChannels: ['database']
        ));

        $this->assertEquals(2, $this->customer->unreadNotifications()->count());

        $response = $this->actingAs($this->customer)
            ->post(route('notifications.read-all'));

        $response->assertRedirect();
        $this->assertEquals(0, $this->customer->unreadNotifications()->count());
    }

    /**
     * Test deleting a notification.
     */
    public function test_destroy_notification(): void
    {
        $this->customer->notify(new ToyotaUniversalNotification(
            title: 'Pengumuman Dealer',
            message: 'Showroom akan libur cuti bersama.',
            category: 'announcement',
            enforcedChannels: ['database']
        ));

        $notification = $this->customer->notifications()->first();

        $response = $this->actingAs($this->customer)
            ->delete(route('notifications.destroy', $notification->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    /**
     * Test security: A user cannot access or modify another user's notification.
     */
    public function test_user_cannot_access_or_modify_other_users_notification(): void
    {
        $this->otherCustomer->notify(new ToyotaUniversalNotification(
            title: 'Rahasia Customer Lain',
            message: 'Pesan privasi.',
            enforcedChannels: ['database']
        ));

        $otherNotification = $this->otherCustomer->notifications()->first();

        // Customer 1 tries to mark read Customer 2's notification
        $response = $this->actingAs($this->customer)
            ->post(route('notifications.mark-read', $otherNotification->id));

        $response->assertNotFound();
        $this->assertNull($otherNotification->fresh()->read_at);

        // Customer 1 tries to delete Customer 2's notification
        $deleteResponse = $this->actingAs($this->customer)
            ->delete(route('notifications.destroy', $otherNotification->id));

        $deleteResponse->assertNotFound();
        $this->assertDatabaseHas('notifications', ['id' => $otherNotification->id]);
    }

    /**
     * Test customer notification preferences form and update with WhatsApp consent.
     */
    public function test_customer_notification_preferences_edit_and_update(): void
    {
        $response = $this->actingAs($this->customer)
            ->get(route('customer.notifications.preferences'));

        $response->assertOk();
        $response->assertSee('Preferensi Notifikasi &amp; Komunikasi', false);

        // Update preferences and give WhatsApp consent
        $updateResponse = $this->actingAs($this->customer)
            ->put(route('customer.notifications.preferences.update'), [
                'in_app' => '1',
                'email' => '1',
                'whatsapp' => '1',
                'service_reminders' => '1',
                'transactional' => '1',
                'promotional' => '0',
                'whatsapp_consent' => '1',
            ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('success');

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $this->customer->id,
            'whatsapp' => true,
            'whatsapp_consent' => true,
        ]);

        $prefs = $this->customer->getNotificationPreferences();
        $this->assertNotNull($prefs->whatsapp_consent_at);
    }

    /**
     * Test Indonesian phone number normalization to E.164 format without plus.
     */
    public function test_phone_number_normalization_e164(): void
    {
        $this->assertEquals('6281234567890', WhatsAppNotificationService::normalizePhoneNumber('081234567890'));
        $this->assertEquals('6281234567890', WhatsAppNotificationService::normalizePhoneNumber('+6281234567890'));
        $this->assertEquals('6281234567890', WhatsAppNotificationService::normalizePhoneNumber('6281234567890'));
        $this->assertEquals('6281234567890', WhatsAppNotificationService::normalizePhoneNumber('0812-3456-7890'));
        $this->assertEquals('6281234567890', WhatsAppNotificationService::normalizePhoneNumber('+62 (812) 3456-7890'));
        $this->assertNull(WhatsAppNotificationService::normalizePhoneNumber('123')); // too short
        $this->assertNull(WhatsAppNotificationService::normalizePhoneNumber(null));
    }

    /**
     * Test WhatsApp service dispatches message and records audit log.
     */
    public function test_whatsapp_service_dispatches_message_and_creates_log(): void
    {
        $this->customer->getNotificationPreferences()->update([
            'whatsapp' => true,
            'whatsapp_consent' => true,
            'whatsapp_consent_at' => now(),
        ]);

        $waService = app(WhatsAppNotificationService::class);
        $result = $waService->sendToUser(
            user: $this->customer,
            title: 'Konfirmasi Pemesanan SPK',
            message: 'Unit All New Alphard HEV Anda telah siap.',
            category: 'vehicle_booking',
            branchId: $this->branch->id,
            metadata: ['test_flag' => true]
        );

        $this->assertTrue($result['sent']);
        $this->assertNotNull($result['log']);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $this->customer->id,
            'channel' => 'whatsapp',
            'category' => 'vehicle_booking',
            'status' => 'sent',
            'recipient' => '6281234567890',
        ]);
    }

    /**
     * Test WhatsApp does not dispatch when customer has not provided opt-in consent for promotional messages.
     */
    public function test_whatsapp_respects_opt_in_consent_for_marketing(): void
    {
        $this->customer->getNotificationPreferences()->update([
            'whatsapp' => false,
            'whatsapp_consent' => false,
        ]);

        $waService = app(WhatsAppNotificationService::class);
        $result = $waService->sendToUser(
            user: $this->customer,
            title: 'Promo Spesial Bunga 0%',
            message: 'Dapatkan cicilan ringan bulan ini.',
            category: 'announcement',
            forceTransactional: false
        );

        $this->assertFalse($result['sent']);
        $this->assertStringContainsString('opted-in', $result['reason']);
    }

    /**
     * Test Meta WhatsApp official API adapter with HTTP fake responses.
     */
    public function test_meta_whatsapp_adapter_http_success_and_failure(): void
    {
        config([
            'services.whatsapp.phone_number_id' => '10987654321',
            'services.whatsapp.access_token' => 'test-bearer-token',
            'services.whatsapp.version' => 'v21.0',
        ]);

        Http::fakeSequence()
            ->push([
                'messaging_product' => 'whatsapp',
                'contacts' => [['input' => '6281234567890', 'wa_id' => '6281234567890']],
                'messages' => [['id' => 'wamid.HBgLMjAyNjExMDI...']],
            ], 200)
            ->push([
                'error' => [
                    'message' => 'Rate limit exceeded on WhatsApp Cloud API.',
                    'code' => 130429,
                ],
            ], 429);

        $metaService = new MetaWhatsAppService();
        $response = $metaService->sendMessage('6281234567890', 'Pesan resmi pengujian Meta WhatsApp');

        $this->assertTrue($response['success']);
        $this->assertEquals('wamid.HBgLMjAyNjExMDI...', $response['message_id']);

        $failedResponse = $metaService->sendMessage('6281234567890', 'Pesan overload');
        $this->assertFalse($failedResponse['success']);
        $this->assertStringContainsString('Rate limit', $failedResponse['error']);
    }

    /**
     * Test NotificationService idempotency prevents duplicate dispatches.
     */
    public function test_notification_service_idempotency_prevents_duplicate_dispatches(): void
    {
        $notificationService = app(NotificationService::class);
        $key = 'unique-idemp-key-' . uniqid();

        $result1 = $notificationService->notifyUser(
            user: $this->customer,
            title: 'Pembayaran DP SPK',
            message: 'Tanda jadi telah berhasil dibayarkan.',
            category: 'payment',
            metadata: ['idempotency_key' => $key]
        );

        $this->assertTrue($result1['in_app']);

        // Second dispatch with same idempotency key
        $result2 = $notificationService->notifyUser(
            user: $this->customer,
            title: 'Pembayaran DP SPK',
            message: 'Tanda jadi telah berhasil dibayarkan.',
            category: 'payment',
            metadata: ['idempotency_key' => $key]
        );

        $this->assertFalse($result2['in_app']);
        $this->assertFalse($result2['email']);
    }

    /**
     * Test Admin notification monitoring dashboard access and resend functionality.
     */
    public function test_admin_notification_monitoring_access_and_resend(): void
    {
        $log = NotificationLog::create([
            'user_id' => $this->customer->id,
            'branch_id' => $this->branch->id,
            'notification_type' => 'EmailNotification',
            'category' => 'payment',
            'channel' => 'mail',
            'recipient' => $this->customer->email,
            'title' => 'Invoice Pembayaran Booking',
            'content' => 'Rincian invoice resmi #INV-001',
            'status' => 'failed',
            'failure_reason' => 'SMTP timeout connection',
        ]);

        // Customer forbidden
        $forbiddenResponse = $this->actingAs($this->customer)
            ->get(route('admin.notifications.monitoring'));
        $forbiddenResponse->assertForbidden();

        // Admin allowed
        $adminResponse = $this->actingAs($this->superAdmin)
            ->get(route('admin.notifications.monitoring'));
        $adminResponse->assertOk();
        $adminResponse->assertSee('Monitoring &amp; Audit Log Notifikasi', false);
        $adminResponse->assertSee('Invoice Pembayaran Booking');
        $adminResponse->assertSee('Failed');

        // Resend log action
        $resendResponse = $this->actingAs($this->superAdmin)
            ->post(route('admin.notifications.resend', $log->id));

        $resendResponse->assertRedirect();
        $resendResponse->assertSessionHas('success');

        $this->assertEquals('sent', $log->fresh()->status);
        $this->assertNull($log->fresh()->failure_reason);
    }
}
