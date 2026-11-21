<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Customer') || $user->can('payments.view');
    }

    public function view(User $user, Payment $payment): bool
    {
        if ($user->can('payments.view') || $user->hasRole(['Finance Officer', 'Super Admin', 'Dealer Manager'])) {
            return true;
        }

        return $payment->user_id === $user->id;
    }

    public function manage(User $user): bool
    {
        return $user->can('payments.manage') || $user->hasRole(['Finance Officer', 'Super Admin']);
    }
}

