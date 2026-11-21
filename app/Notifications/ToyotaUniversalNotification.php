<?php

namespace App\Notifications;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ToyotaUniversalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $category = 'announcement',
        public ?string $actionUrl = null,
        public ?string $actionText = null,
        public ?int $branchId = null,
        public array $metadata = [],
        public bool $isTransactional = true,
        public ?array $enforcedChannels = null
    ) {
    }

    /**
     * Determine notification channels based on user preferences.
     *
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        if ($this->enforcedChannels !== null) {
            return $this->enforcedChannels;
        }

        $channels = ['database'];

        if ($notifiable instanceof User) {
            $prefs = $notifiable->getNotificationPreferences();

            $channels = [];

            // In-app channel
            if ($prefs->in_app) {
                $channels[] = 'database';
            }

            // Mail channel
            if ($prefs->email && !empty($notifiable->email)) {
                // If service reminder, check service_reminders pref
                if ($this->category === 'service' && !$prefs->service_reminders && !$this->isTransactional) {
                    // skipped
                } elseif (!$this->isTransactional && !$prefs->promotional) {
                    // promotional skipped
                } else {
                    $channels[] = 'mail';
                }
            }
        }

        return !empty($channels) ? $channels : ['database'];
    }

    /**
     * Representation for database storage.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(mixed $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'category' => $this->category,
            'action_url' => $this->actionUrl,
            'action_text' => $this->actionText,
            'branch_id' => $this->branchId,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Representation for mail delivery.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title . ' - Toyota AutoConnect')
            ->view('emails.notification', [
                'title' => $this->title,
                'messageText' => $this->message,
                'categoryName' => $this->getCategoryLabel(),
                'actionUrl' => $this->actionUrl,
                'actionText' => $this->actionText,
                'recipientEmail' => $notifiable->email ?? 'Pelanggan',
            ]);
    }

    /**
     * Map category code to human-readable label.
     */
    public function getCategoryLabel(): string
    {
        return match ($this->category) {
            'vehicle_booking' => 'Pemesanan Kendaraan',
            'payment' => 'Pembayaran',
            'test_drive' => 'Test Drive',
            'sales_offer' => 'Penawaran Kendaraan',
            'service' => 'Servis & Perawatan',
            'customer_service' => 'Customer Service',
            'account' => 'Informasi Akun',
            default => 'Pengumuman Resmi',
        };
    }
}

