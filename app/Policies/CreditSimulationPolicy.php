<?php

namespace App\Policies;

use App\Models\CreditSimulation;
use App\Models\User;

class CreditSimulationPolicy
{
    /**
     * Determine whether the user can view any credit simulations.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Customer') || $user->can('financing.view');
    }

    /**
     * Determine whether the user can view the credit simulation.
     */
    public function view(User $user, CreditSimulation $simulation): bool
    {
        if ($user->hasRole('Customer')) {
            return $simulation->user_id === $user->id;
        }

        if ($user->can('financing.view')) {
            return true;
        }

        return $simulation->user_id === $user->id;
    }

    /**
     * Determine whether the user can create credit simulations.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the credit simulation.
     */
    public function delete(User $user, CreditSimulation $simulation): bool
    {
        if ($user->hasRole('Customer')) {
            return $simulation->user_id === $user->id;
        }

        if ($user->can('financing.manage')) {
            return true;
        }

        return $simulation->user_id === $user->id;
    }
}
