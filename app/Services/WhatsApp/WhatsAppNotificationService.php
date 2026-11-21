<?php

namespace App\Services\WhatsApp;

use App\Models\NotificationLog;
use App\Models\User;
use App\Services\WhatsApp\Adapters\MetaWhatsAppService;
use App\Services\WhatsApp\Adapters\MockWhatsAppService;
use App\Services\WhatsApp\Contracts\WhatsAppServiceInterface;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    protected WhatsAppServiceInterface $driver;

    public function __construct(?WhatsAppServiceInterface $driver = null)
    {
        if ($driver) {
            $this->driver = $driver;
        } else {
            $enabled = config('services.whatsapp.enabled', false);
            $provider = config('services.whatsapp.provider', 'mock');

            if ($enabled && $provider === 'meta') {
                $this->driver = app(MetaWhatsAppService::class);
            } else {
                $this->driver = app(MockWhatsAppService::class);
            }
        }
    }

    /**
     * Normalize an Indonesian or international phone number to E.164 without leading plus.
     */
    public static function normalizePhoneNumber(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Strip non-digit characters except leading plus
        $cleaned = preg_replace('/[^\d+]/', '', trim($phone));

        if (empty($cleaned)) {
            return null;
        }

        // Convert +62 to 62
        if (str_starts_with($cleaned, '+62')) {
            $cleaned = substr($cleaned, 1);
        } elseif (str_starts_with($cleaned, '+')) {
            $cleaned = substr($cleaned, 1);
        } elseif (str_starts_with($cleaned, '0')) {
            $cleaned = '62' . substr($cleaned, 1);
        } elseif (str_starts_with($cleaned, '8')) {
            $cleaned = '62' . $cleaned;
        }

        // Valid phone must be digits only between 10 and 15 characters
        if (preg_match('/^[1-9]\d{9,14}$/', $cleaned)) {
            return $cleaned;
        }

        return null;
    }

    /**
     * Send notification to a User via WhatsApp.
     *
     * @param User $user Target recipient user
     * @param string $title Short title or subject
     * @param string $message Main message body
     * @param string $category Notification category
     * @param int|null $branchId Optional branch ID
     * @param array<string, mixed> $metadata Additional context metadata
     * @param bool $forceTransactional Bypass marketing opt-in if strictly operational
     * @return array{sent: bool, log: ?NotificationLog, reason: ?string}
     */
    public function sendToUser(
        User $user,
        string $title,
        string $message,
        string $category = 'announcement',
        ?int $branchId = null,
        array $metadata = [],
        bool $forceTransactional = false
    ): array {
        $prefs = $user->getNotificationPreferences();

        // Check if customer opted in to WhatsApp
        if (!$forceTransactional && (!$prefs->whatsapp || !$prefs->whatsapp_consent)) {
            return [
                'sent' => false,
                'log' => null,
                'reason' => 'User has not opted-in or provided consent for WhatsApp notifications.',
            ];
        }

        $phone = self::normalizePhoneNumber($user->phone);
        if (!$phone) {
            $log = NotificationLog::create([
                'user_id' => $user->id,
                'branch_id' => $branchId,
                'notification_type' => 'WhatsAppNotification',
                'category' => $category,
                'channel' => 'whatsapp',
                'recipient' => $user->phone ?? 'UNKNOWN',
                'title' => $title,
                'content' => $message,
                'status' => 'failed',
                'failure_reason' => 'Invalid or missing phone number for WhatsApp.',
                'metadata' => $metadata,
            ]);

            return [
                'sent' => false,
                'log' => $log,
                'reason' => 'Invalid phone number format.',
            ];
        }

        $formattedMessage = "*TOYOTA AUTOCONNECT*\n\n" . "*{$title}*\n\n" . $message . "\n\n_Pesan otomatis resmi dari Toyota AutoConnect._";

        $result = $this->driver->sendMessage($phone, $formattedMessage);

        $status = $result['success'] ? 'sent' : 'failed';
        $log = NotificationLog::create([
            'user_id' => $user->id,
            'branch_id' => $branchId,
            'notification_type' => 'WhatsAppNotification',
            'category' => $category,
            'channel' => 'whatsapp',
            'recipient' => $phone,
            'title' => $title,
            'content' => $formattedMessage,
            'status' => $status,
            'provider_message_id' => $result['message_id'] ?? null,
            'failure_reason' => $result['error'] ?? null,
            'metadata' => array_merge($metadata, ['raw_response' => $result['raw'] ?? []]),
            'sent_at' => $result['success'] ? now() : null,
        ]);

        return [
            'sent' => $result['success'],
            'log' => $log,
            'reason' => $result['error'] ?? null,
        ];
    }

    /**
     * Send direct message to a raw phone number.
     */
    public function sendToPhone(
        string $phone,
        string $title,
        string $message,
        string $category = 'announcement',
        ?int $branchId = null,
        array $metadata = []
    ): array {
        $normalized = self::normalizePhoneNumber($phone);
        if (!$normalized) {
            return [
                'sent' => false,
                'log' => null,
                'reason' => 'Invalid phone number format.',
            ];
        }

        $formattedMessage = "*TOYOTA AUTOCONNECT*\n\n" . "*{$title}*\n\n" . $message . "\n\n_Pesan otomatis resmi dari Toyota AutoConnect._";
        $result = $this->driver->sendMessage($normalized, $formattedMessage);

        $status = $result['success'] ? 'sent' : 'failed';
        $log = NotificationLog::create([
            'user_id' => null,
            'branch_id' => $branchId,
            'notification_type' => 'WhatsAppDirectNotification',
            'category' => $category,
            'channel' => 'whatsapp',
            'recipient' => $normalized,
            'title' => $title,
            'content' => $formattedMessage,
            'status' => $status,
            'provider_message_id' => $result['message_id'] ?? null,
            'failure_reason' => $result['error'] ?? null,
            'metadata' => array_merge($metadata, ['raw_response' => $result['raw'] ?? []]),
            'sent_at' => $result['success'] ? now() : null,
        ]);

        return [
            'sent' => $result['success'],
            'log' => $log,
            'reason' => $result['error'] ?? null,
        ];
    }
}

