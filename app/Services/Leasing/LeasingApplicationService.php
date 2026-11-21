<?php

namespace App\Services\Leasing;

use App\Models\LeasingApplication;
use App\Models\LeasingApplicationLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeasingApplicationService
{
    public function __construct(
        protected LeasingManager $leasingManager
    ) {}

    /**
     * Submit application to the leasing provider via its adapter.
     */
    public function submit(LeasingApplication $application, ?User $actor = null): array
    {
        $provider = $application->leasingProvider;
        $adapter = $this->leasingManager->resolve($provider);

        $this->logEvent(
            application: $application,
            eventType: 'submission_started',
            description: "Memulai transmisi data pengajuan ke {$provider->name}.",
            user: $actor
        );

        $result = $adapter->submitApplication($application);

        return DB::transaction(function () use ($application, $result, $actor, $provider) {
            if ($result['success']) {
                $application->update([
                    'status' => 'submitted',
                    'provider_reference' => $result['reference_id'] ?? $application->provider_reference,
                    'submitted_at' => now(),
                ]);

                $this->logEvent(
                    application: $application,
                    eventType: 'submitted',
                    description: "Pengajuan berhasil diterima oleh {$provider->name}. No. Ref: " . ($result['reference_id'] ?? '-'),
                    metadata: $result['raw_response'],
                    user: $actor
                );
            } else {
                // Provider failure must NEVER set status to approved!
                $application->update([
                    'status' => 'draft',
                ]);

                $this->logEvent(
                    application: $application,
                    eventType: 'submission_failed',
                    description: "Kegagalan komunikasi dengan {$provider->name}: " . $result['message'],
                    metadata: $result['raw_response'],
                    user: $actor
                );

                Log::warning("Leasing application submission failed for {$application->code}: {$result['message']}");
            }

            return $result;
        });
    }

    /**
     * Check and sync status from the external provider.
     */
    public function syncStatus(LeasingApplication $application, ?User $actor = null): array
    {
        $provider = $application->leasingProvider;
        $adapter = $this->leasingManager->resolve($provider);

        $result = $adapter->checkStatus($application);

        if ($result['success']) {
            $newStatus = $result['status'];
            $oldStatus = $application->status;

            if ($newStatus !== $oldStatus) {
                $application->update([
                    'status' => $newStatus,
                    'response_at' => now(),
                ]);

                $this->logEvent(
                    application: $application,
                    eventType: 'status_updated',
                    description: "Pembaruan status dari provider {$provider->name}: '{$oldStatus}' -> '{$newStatus}'.",
                    metadata: $result['raw_response'],
                    user: $actor
                );
            }
        } else {
            $this->logEvent(
                application: $application,
                eventType: 'sync_failed',
                description: "Gagal memeriksa status terkini dari {$provider->name}: " . $result['message'],
                user: $actor
            );
        }

        return $result;
    }

    /**
     * Manually update the status by an authorized leasing officer.
     */
    public function updateStatus(
        LeasingApplication $application,
        string $newStatus,
        User $actor,
        ?string $notes = null,
        ?string $revisionNotes = null
    ): void {
        $oldStatus = $application->status;

        $updateData = ['status' => $newStatus];

        if ($notes) {
            $updateData['notes'] = $notes;
        }

        if ($newStatus === 'need_revision' && $revisionNotes) {
            $updateData['revision_notes'] = $revisionNotes;
        }

        $application->update($updateData);

        $desc = "Status pengajuan diperbarui oleh {$actor->name} ({$actor->role_name}) dari '{$oldStatus}' menjadi '{$newStatus}'.";
        if ($revisionNotes) {
            $desc .= " Catatan revisi: {$revisionNotes}";
        }

        $this->logEvent(
            application: $application,
            eventType: 'manual_status_change',
            description: $desc,
            metadata: ['notes' => $notes, 'revision_notes' => $revisionNotes],
            user: $actor
        );
    }

    /**
     * Cancel an application.
     */
    public function cancel(LeasingApplication $application, User $actor, ?string $reason = null): void
    {
        $application->update([
            'status' => 'cancelled',
            'notes' => $reason ? ($application->notes . "\nAlasan pembatalan: " . $reason) : $application->notes,
        ]);

        $this->logEvent(
            application: $application,
            eventType: 'cancelled',
            description: "Pengajuan dibatalkan oleh {$actor->name}." . ($reason ? " Alasan: {$reason}" : ''),
            user: $actor
        );
    }

    /**
     * Append an audit event to leasing_application_logs.
     */
    public function logEvent(
        LeasingApplication $application,
        string $eventType,
        string $description,
        ?array $metadata = null,
        ?User $user = null
    ): LeasingApplicationLog {
        return LeasingApplicationLog::create([
            'leasing_application_id' => $application->id,
            'user_id' => $user?->id,
            'event_type' => $eventType,
            'description' => $description,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}

