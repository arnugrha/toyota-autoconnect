<?php

namespace App\Policies;

use App\Models\LeasingApplication;
use App\Models\User;

class LeasingApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Customer') || $user->can('financing.view');
    }

    public function view(User $user, LeasingApplication $application): bool
    {
        if ($user->can('financing.view') || $user->hasRole(['Leasing Officer', 'Super Admin', 'Dealer Manager', 'Sales Manager'])) {
            return true;
        }

        if ($user->hasRole('Sales Executive')) {
            return $application->salesOpportunity?->assigned_to === $user->id;
        }

        return $application->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Customer') || $user->can('financing.manage');
    }

    public function update(User $user, LeasingApplication $application): bool
    {
        return $user->can('financing.manage') || $user->hasRole(['Leasing Officer', 'Super Admin', 'Sales Manager']);
    }

    public function cancel(User $user, LeasingApplication $application): bool
    {
        if ($user->can('financing.manage')) {
            return true;
        }

        return $application->user_id === $user->id && $application->canBeCancelled();
    }
}

