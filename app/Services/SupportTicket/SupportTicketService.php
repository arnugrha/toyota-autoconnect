<?php

namespace App\Services\SupportTicket;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class SupportTicketService
{
    /**
     * Create a new support ticket.
     */
    public function createTicket(User $customer, array $data, ?UploadedFile $attachment = null): SupportTicket
    {
        return DB::transaction(function () use ($customer, $data, $attachment) {
            $attachmentPath = null;
            if ($attachment) {
                $attachmentPath = $attachment->store('support-attachments', 'public');
            }

            $ticket = SupportTicket::create([
                'ticket_number' => SupportTicket::generateNumber(),
                'user_id' => $customer->id,
                'branch_id' => $data['branch_id'] ?? null,
                'category' => $data['category'],
                'priority' => $data['priority'] ?? 'medium',
                'status' => 'open',
                'subject' => $data['subject'],
                'description' => $data['description'],
                'attachment_path' => $attachmentPath,
            ]);

            // First message in thread
            $ticket->messages()->create([
                'user_id' => $customer->id,
                'message' => $data['description'],
                'attachment_path' => $attachmentPath,
                'is_internal' => false,
            ]);

            try {
                $notificationService = app(NotificationService::class);
                $notificationService->notifyUser(
                    user: $customer,
                    title: "Tiket Bantuan #{$ticket->ticket_number} Dibuat",
                    message: "Halo {$customer->name}, pertanyaan atau keluhan Anda mengenai '{$ticket->subject}' telah kami terima. Tim Customer Care kami akan segera merespons.",
                    category: 'customer_service',
                    actionUrl: route('customer.tickets.show', $ticket),
                    actionText: 'Lihat Tiket Bantuan',
                    branchId: $ticket->branch_id,
                    metadata: ['ticket_id' => $ticket->id, 'ticket_number' => $ticket->ticket_number]
                );

                $notificationService->notifyRole(
                    roleName: 'CRM Officer',
                    title: "Tiket Layanan Baru #{$ticket->ticket_number}",
                    message: "Tiket baru diajukan oleh {$customer->name}: '{$ticket->subject}'.",
                    category: 'customer_service',
                    branchId: $ticket->branch_id,
                    actionUrl: route('admin.tickets.show', $ticket)
                );
            } catch (\Throwable $e) {}

            return $ticket;
        });
    }

    /**
     * Add a message to the ticket conversation.
     */
    public function replyTicket(SupportTicket $ticket, User $sender, string $message, bool $isInternal = false, ?UploadedFile $attachment = null): SupportTicketMessage
    {
        if ($ticket->status === 'closed' && ! $sender->can('support-tickets.manage')) {
            throw new InvalidArgumentException('Tiket bantuan ini telah ditutup.');
        }

        return DB::transaction(function () use ($ticket, $sender, $message, $isInternal, $attachment) {
            $attachmentPath = null;
            if ($attachment) {
                $attachmentPath = $attachment->store('support-attachments', 'public');
            }

            $msg = $ticket->messages()->create([
                'user_id' => $sender->id,
                'message' => $message,
                'attachment_path' => $attachmentPath,
                'is_internal' => $isInternal,
            ]);

            // Update ticket status dynamically depending on who replied
            if (! $isInternal) {
                if ($sender->id === $ticket->user_id) {
                    // Customer replied
                    $ticket->update(['status' => 'in_progress']);
                } else {
                    // Staff replied -> waiting for customer
                    $ticket->update(['status' => 'waiting_customer']);
                }

                try {
                    $notificationService = app(NotificationService::class);
                    if ($sender->id === $ticket->user_id) {
                        if ($ticket->assignedTo) {
                            $notificationService->notifyUser(
                                user: $ticket->assignedTo,
                                title: "Pesan Baru pada Tiket #{$ticket->ticket_number}",
                                message: "Pelanggan {$sender->name} telah membalas tiket: " . \Illuminate\Support\Str::limit($message, 100),
                                category: 'customer_service',
                                actionUrl: route('admin.tickets.show', $ticket),
                                branchId: $ticket->branch_id
                            );
                        } else {
                            $notificationService->notifyRole(
                                roleName: 'CRM Officer',
                                title: "Balasan Baru Tiket #{$ticket->ticket_number}",
                                message: "Pelanggan {$sender->name} membalas tiket: " . \Illuminate\Support\Str::limit($message, 100),
                                category: 'customer_service',
                                branchId: $ticket->branch_id,
                                actionUrl: route('admin.tickets.show', $ticket)
                            );
                        }
                    } else {
                        if ($ticket->user) {
                            $notificationService->notifyUser(
                                user: $ticket->user,
                                title: "Balasan untuk Tiket #{$ticket->ticket_number}",
                                message: "Customer Service telah merespons tiket Anda: " . \Illuminate\Support\Str::limit($message, 120),
                                category: 'customer_service',
                                actionUrl: route('customer.tickets.show', $ticket),
                                actionText: 'Lihat Balasan Tiket',
                                branchId: $ticket->branch_id
                            );
                        }
                    }
                } catch (\Throwable $e) {}
            }

            return $msg;
        });
    }

    /**
     * Update status of a support ticket.
     */
    public function updateStatus(SupportTicket $ticket, string $newStatus, ?User $actor = null): SupportTicket
    {
        $validStatuses = ['open', 'in_progress', 'waiting_customer', 'resolved', 'closed'];

        if (! in_array($newStatus, $validStatuses)) {
            throw new InvalidArgumentException("Status tiket '{$newStatus}' tidak valid.");
        }

        return DB::transaction(function () use ($ticket, $newStatus) {
            $updates = ['status' => $newStatus];

            if ($newStatus === 'resolved' && ! $ticket->resolved_at) {
                $updates['resolved_at'] = now();
            }

            if ($newStatus === 'closed' && ! $ticket->closed_at) {
                $updates['closed_at'] = now();
            }

            $ticket->update($updates);

            try {
                if ($ticket->user && in_array($newStatus, ['resolved', 'closed'])) {
                    $statusName = $newStatus === 'resolved' ? 'Telah Diselesaikan' : 'Telah Ditutup';
                    app(NotificationService::class)->notifyUser(
                        user: $ticket->user,
                        title: "Tiket Bantuan #{$ticket->ticket_number} {$statusName}",
                        message: "Tiket keluhan/bantuan Anda mengenai '{$ticket->subject}' telah ditandai status {$newStatus}. Terima kasih telah menghubungi layanan pelanggan Toyota AutoConnect.",
                        category: 'customer_service',
                        actionUrl: route('customer.tickets.show', $ticket),
                        actionText: 'Lihat Tiket Bantuan',
                        branchId: $ticket->branch_id
                    );
                }
            } catch (\Throwable $e) {}

            return $ticket->fresh();
        });
    }

    /**
     * Assign ticket to a staff member.
     */
    public function assignAgent(SupportTicket $ticket, ?User $agent): SupportTicket
    {
        $ticket->update([
            'assigned_to' => $agent?->id,
            'status' => ($ticket->status === 'open') ? 'in_progress' : $ticket->status,
        ]);

        return $ticket->fresh();
    }
}

