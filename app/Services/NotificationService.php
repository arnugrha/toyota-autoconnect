<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\User;
use App\Notifications\ToyotaUniversalNotification;
use App\Services\WhatsApp\WhatsAppNotificationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Throwable;

class NotificationService
{
    public function __construct(
        protected WhatsAppNotificationService $whatsAppService
    ) {
    }

    /**
     * Dispatch multi-channel notification to a single user.
     *
     * @return array{in_app: bool, email: bool, whatsapp: bool, logs: array<int, NotificationLog>}
     */
    public function notifyUser(
        User $user,
        string $title,
        string $message,
        string $category = 'announcement',
        ?string $actionUrl = null,
        ?string $actionText = null,
        ?int $branchId = null,
        array $metadata = [],
        bool $isTransactional = true
    ): array {
        // Idempotency check
        if (!empty($metadata['idempotency_key'])) {
            $existing = NotificationLog::where('metadata->idempotency_key', $metadata['idempotency_key'])
                ->where('created_at', '>=', now()->subMinutes(15))
                ->first();

            if ($existing) {
                Log::info("Skipping duplicate notification for key {$metadata['idempotency_key']}");
                return [
                    'in_app' => false,
                    'email' => false,
                    'whatsapp' => false,
                    'logs' => [$existing],
                ];
            }
        }

        $prefs = $user->getNotificationPreferences();
        $results = [
            'in_app' => false,
            'email' => false,
            'whatsapp' => false,
            'logs' => [],
        ];

        // 1. In-App Notification (Database)
        if ($prefs->in_app) {
            try {
                $user->notify(new ToyotaUniversalNotification(
                    title: $title,
                    message: $message,
                    category: $category,
                    actionUrl: $actionUrl,
                    actionText: $actionText,
                    branchId: $branchId,
                    metadata: $metadata,
                    isTransactional: $isTransactional,
                    enforcedChannels: ['database']
                ));

                $log = NotificationLog::create([
                    'user_id' => $user->id,
                    'branch_id' => $branchId,
                    'notification_type' => 'InAppNotification',
                    'category' => $category,
                    'channel' => 'in_app',
                    'recipient' => $user->name . ' (' . $user->email . ')',
                    'title' => $title,
                    'content' => $message,
                    'status' => 'delivered',
                    'metadata' => $metadata,
                    'sent_at' => now(),
                ]);

                $results['in_app'] = true;
                $results['logs'][] = $log;
            } catch (Throwable $e) {
                Log::error('In-app notification dispatch failed: ' . $e->getMessage());
                $results['logs'][] = NotificationLog::create([
                    'user_id' => $user->id,
                    'branch_id' => $branchId,
                    'notification_type' => 'InAppNotification',
                    'category' => $category,
                    'channel' => 'in_app',
                    'recipient' => $user->email,
                    'title' => $title,
                    'content' => $message,
                    'status' => 'failed',
                    'failure_reason' => $e->getMessage(),
                    'metadata' => $metadata,
                ]);
            }
        }

        // 2. Email Notification
        $shouldSendEmail = $prefs->email && !empty($user->email);
        if ($shouldSendEmail) {
            if ($category === 'service' && !$prefs->service_reminders && !$isTransactional) {
                $shouldSendEmail = false;
            } elseif (!$isTransactional && !$prefs->promotional) {
                $shouldSendEmail = false;
            }
        }

        if ($shouldSendEmail) {
            try {
                $user->notify(new ToyotaUniversalNotification(
                    title: $title,
                    message: $message,
                    category: $category,
                    actionUrl: $actionUrl,
                    actionText: $actionText,
                    branchId: $branchId,
                    metadata: $metadata,
                    isTransactional: $isTransactional,
                    enforcedChannels: ['mail']
                ));

                $log = NotificationLog::create([
                    'user_id' => $user->id,
                    'branch_id' => $branchId,
                    'notification_type' => 'EmailNotification',
                    'category' => $category,
                    'channel' => 'mail',
                    'recipient' => $user->email,
                    'title' => $title,
                    'content' => $message,
                    'status' => 'sent',
                    'metadata' => $metadata,
                    'sent_at' => now(),
                ]);

                $results['email'] = true;
                $results['logs'][] = $log;
            } catch (Throwable $e) {
                Log::error('Email notification dispatch failed: ' . $e->getMessage());
                $results['logs'][] = NotificationLog::create([
                    'user_id' => $user->id,
                    'branch_id' => $branchId,
                    'notification_type' => 'EmailNotification',
                    'category' => $category,
                    'channel' => 'mail',
                    'recipient' => $user->email,
                    'title' => $title,
                    'content' => $message,
                    'status' => 'failed',
                    'failure_reason' => $e->getMessage(),
                    'metadata' => $metadata,
                ]);
            }
        }

        // 3. WhatsApp Notification
        if ($prefs->whatsapp && ($prefs->whatsapp_consent || $isTransactional)) {
            $waResult = $this->whatsAppService->sendToUser(
                user: $user,
                title: $title,
                message: $message,
                category: $category,
                branchId: $branchId,
                metadata: $metadata,
                forceTransactional: $isTransactional
            );

            if ($waResult['sent']) {
                $results['whatsapp'] = true;
            }
            if (!empty($waResult['log'])) {
                $results['logs'][] = $waResult['log'];
            }
        }

        return $results;
    }

    /**
     * Dispatch notification to all users matching a role.
     *
     * @return Collection<int, array>
     */
    public function notifyRole(
        string $roleName,
        string $title,
        string $message,
        string $category = 'announcement',
        ?int $branchId = null,
        ?string $actionUrl = null,
        ?string $actionText = null,
        array $metadata = []
    ): Collection {
        $users = User::role($roleName)->where('is_active', true)->get();

        return $users->map(fn (User $user) => $this->notifyUser(
            user: $user,
            title: $title,
            message: $message,
            category: $category,
            actionUrl: $actionUrl,
            actionText: $actionText,
            branchId: $branchId,
            metadata: $metadata,
            isTransactional: true
        ));
    }

    /**
     * Safely resend or retry a notification log.
     */
    public function resend(NotificationLog $log): bool
    {
        if ($log->channel === 'whatsapp' && $log->user) {
            $result = $this->whatsAppService->sendToUser(
                user: $log->user,
                title: $log->title,
                message: $log->content ?? $log->title,
                category: $log->category,
                branchId: $log->branch_id,
                metadata: array_merge($log->metadata ?? [], ['retry_for_log_id' => $log->id]),
                forceTransactional: true
            );

            if ($result['sent']) {
                $log->update(['status' => 'sent', 'failure_reason' => null, 'sent_at' => now()]);
                return true;
            }
            return false;
        }

        if ($log->channel === 'mail' && $log->user) {
            try {
                $log->user->notify(new ToyotaUniversalNotification(
                    title: $log->title,
                    message: $log->content ?? $log->title,
                    category: $log->category,
                    branchId: $log->branch_id,
                    metadata: array_merge($log->metadata ?? [], ['retry_for_log_id' => $log->id]),
                    isTransactional: true,
                    enforcedChannels: ['mail']
                ));

                $log->update(['status' => 'sent', 'failure_reason' => null, 'sent_at' => now()]);
                return true;
            } catch (Throwable $e) {
                $log->update(['failure_reason' => $e->getMessage()]);
                return false;
            }
        }

        if ($log->channel === 'in_app' && $log->user) {
            try {
                $log->user->notify(new ToyotaUniversalNotification(
                    title: $log->title,
                    message: $log->content ?? $log->title,
                    category: $log->category,
                    branchId: $log->branch_id,
                    metadata: array_merge($log->metadata ?? [], ['retry_for_log_id' => $log->id]),
                    isTransactional: true,
                    enforcedChannels: ['database']
                ));

                $log->update(['status' => 'delivered', 'failure_reason' => null, 'sent_at' => now()]);
                return true;
            } catch (Throwable $e) {
                $log->update(['failure_reason' => $e->getMessage()]);
                return false;
            }
        }

        return false;
    }
}

