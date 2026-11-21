<?php

namespace App\Policies;

use App\Models\ServiceOrder;
use App\Models\User;

class ServiceOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Customer') || $user->can('service-orders.view');
    }

    public function view(User $user, ServiceOrder $order): bool
    {
        if ($user->can('service-orders.manage') || $user->hasRole(['Super Admin', 'Dealer Manager', 'Service Advisor'])) {
            return true;
        }

        return $order->user_id === $user->id;
    }

    public function manage(User $user): bool
    {
        return $user->can('service-orders.manage') || $user->hasRole(['Super Admin', 'Dealer Manager', 'Service Advisor']);
    }
}
