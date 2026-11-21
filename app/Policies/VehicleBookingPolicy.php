<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VehicleBooking;

class VehicleBookingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('sales.view')
            || $user->hasPermissionTo('sales.manage')
            || $user->hasRole(['Customer', 'Inventory Officer', 'Super Admin', 'Dealer Manager', 'Sales Manager', 'Sales Executive']);
    }

    public function view(User $user, VehicleBooking $booking): bool
    {
        if ($user->hasRole('Customer')) {
            return $user->id === $booking->customer_id;
        }

        if ($user->hasRole('Inventory Officer')) {
            // Inventory Officer can view bookings related to branch inventories
            return true;
        }

        if ($user->hasRole('Sales Executive')) {
            return $booking->salesOpportunity->assigned_to === $user->id
                || $booking->created_by === $user->id
                || $user->hasPermissionTo('leads.view-team');
        }

        return $user->hasPermissionTo('sales.view')
            || $user->hasPermissionTo('sales.manage')
            || $user->hasRole(['Super Admin', 'Dealer Manager', 'Sales Manager']);
    }

    public function create(User $user): bool
    {
        // Inventory officer cannot create sales bookings!
        if ($user->hasRole('Inventory Officer') && ! $user->hasRole(['Super Admin', 'Sales Manager', 'Sales Executive'])) {
            return false;
        }

        return $user->hasPermissionTo('sales.manage')
            || $user->hasRole(['Super Admin', 'Dealer Manager', 'Sales Manager', 'Sales Executive']);
    }

    public function confirm(User $user, VehicleBooking $booking): bool
    {
        // Confirming allocates physical inventory; requires manager or super admin or sales.manage
        if ($user->hasRole('Inventory Officer')) {
            return false;
        }

        return $user->hasPermissionTo('sales.manage')
            || $user->hasRole(['Super Admin', 'Dealer Manager', 'Sales Manager']);
    }

    public function cancel(User $user, VehicleBooking $booking): bool
    {
        if ($user->hasRole('Inventory Officer')) {
            return false;
        }

        if ($user->hasRole('Customer')) {
            return $user->id === $booking->customer_id && $booking->status === 'pending';
        }

        return $user->hasPermissionTo('sales.manage')
            || $user->hasRole(['Super Admin', 'Dealer Manager', 'Sales Manager']);
    }

    public function update(User $user, VehicleBooking $booking): bool
    {
        if ($user->hasRole('Inventory Officer')) {
            return false;
        }

        return $user->hasPermissionTo('sales.manage')
            || $user->hasRole(['Super Admin', 'Dealer Manager', 'Sales Manager']);
    }
}

