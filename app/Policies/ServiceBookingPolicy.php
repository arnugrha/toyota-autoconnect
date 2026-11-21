<?php

namespace App\Policies;

use App\Models\ServiceBooking;
use App\Models\User;

class ServiceBookingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Customer') || $user->can('service-bookings.view');
    }

    public function view(User $user, ServiceBooking $booking): bool
    {
        if ($user->can('service-bookings.manage') || $user->hasRole(['Super Admin', 'Dealer Manager', 'Service Advisor'])) {
            return true;
        }

        return $booking->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true; // Any authenticated user can request a service booking
    }

    public function cancel(User $user, ServiceBooking $booking): bool
    {
        if ($booking->user_id === $user->id) {
            return $booking->canBeCancelled();
        }

        return $user->can('service-bookings.manage');
    }

    public function manage(User $user): bool
    {
        return $user->can('service-bookings.manage') || $user->hasRole(['Super Admin', 'Dealer Manager', 'Service Advisor']);
    }
}
