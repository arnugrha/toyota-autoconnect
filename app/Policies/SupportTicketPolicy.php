<?php

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Customer') || $user->can('support-tickets.view');
    }

    public function view(User $user, SupportTicket $ticket): bool
    {
        if ($user->can('support-tickets.manage') || $user->hasRole(['Super Admin', 'Dealer Manager', 'CRM Officer'])) {
            return true;
        }

        return $ticket->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true; // Any authenticated user can create a ticket
    }

    public function reply(User $user, SupportTicket $ticket): bool
    {
        if ($user->can('support-tickets.manage') || $user->hasRole(['Super Admin', 'Dealer Manager', 'CRM Officer'])) {
            return true;
        }

        return $ticket->user_id === $user->id && $ticket->status !== 'closed';
    }

    public function manage(User $user): bool
    {
        return $user->can('support-tickets.manage') || $user->hasRole(['Super Admin', 'Dealer Manager', 'CRM Officer']);
    }
}
