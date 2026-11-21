<?php

namespace App\Policies;

use App\Models\TestDriveBooking;
use App\Models\User;

class TestDriveBookingPolicy
{
    /**
     * Boleh melihat daftar test drive booking.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('test-drives.view')
            || $user->hasPermissionTo('test-drives.manage')
            || $user->hasRole('Customer');
    }

    /**
     * Boleh melihat detail test drive booking.
     */
    public function view(User $user, TestDriveBooking $booking): bool
    {
        if ($user->hasRole('Customer')) {
            return $user->id === $booking->user_id;
        }

        if ($user->hasRole('Sales Executive')) {
            // Sales executive dapat melihat jika ditugaskan atau jika belum ada penugasan di cabangnya
            return $booking->assigned_to === $user->id
                || $booking->assigned_to === null
                || $user->hasPermissionTo('leads.view-team');
        }

        return $user->hasPermissionTo('test-drives.view')
            || $user->hasPermissionTo('test-drives.manage');
    }

    /**
     * Pelanggan atau staf dapat membuat test drive.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Boleh membatalkan jadwal test drive.
     */
    public function cancel(User $user, TestDriveBooking $booking): bool
    {
        if ($user->hasRole('Customer')) {
            return $user->id === $booking->user_id && $booking->can_be_cancelled_by_customer;
        }

        return $user->hasPermissionTo('test-drives.manage')
            || $user->hasRole(['Super Admin', 'Dealer Manager', 'Sales Manager']);
    }

    /**
     * Boleh mengonfirmasi, menolak, atau menyelesaikan test drive.
     */
    public function manage(User $user, TestDriveBooking $booking): bool
    {
        if ($user->hasRole('Customer')) {
            return false;
        }

        if ($user->hasRole('Sales Executive')) {
            return $booking->assigned_to === $user->id
                || $booking->assigned_to === null
                || $user->hasPermissionTo('test-drives.manage');
        }

        return $user->hasPermissionTo('test-drives.manage')
            || $user->hasRole(['Super Admin', 'Dealer Manager', 'Sales Manager']);
    }
}

