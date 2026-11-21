<?php

namespace App\Policies;

use App\Models\SalesOpportunity;
use App\Models\User;

class SalesOpportunityPolicy
{
    /**
     * Boleh melihat daftar opportunity.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('sales.view')
            || $user->hasPermissionTo('sales.manage')
            || $user->hasRole(['Super Admin', 'Dealer Manager', 'Sales Manager', 'Sales Executive', 'CRM Officer']);
    }

    /**
     * Boleh melihat detail opportunity.
     */
    public function view(User $user, SalesOpportunity $opportunity): bool
    {
        if ($user->hasRole('Customer')) {
            return $user->id === $opportunity->user_id;
        }

        if ($user->hasRole('Sales Executive')) {
            return $opportunity->assigned_to === $user->id
                || $opportunity->assigned_to === null
                || $user->hasPermissionTo('leads.view-team');
        }

        return $user->hasPermissionTo('sales.view')
            || $user->hasPermissionTo('sales.manage')
            || $user->hasRole(['Super Admin', 'Dealer Manager', 'Sales Manager', 'CRM Officer']);
    }

    /**
     * Boleh membuat opportunity.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('sales.manage')
            || $user->hasRole(['Super Admin', 'Dealer Manager', 'Sales Manager', 'Sales Executive', 'CRM Officer']);
    }

    /**
     * Boleh memperbarui opportunity.
     */
    public function update(User $user, SalesOpportunity $opportunity): bool
    {
        if ($user->hasRole('Sales Executive')) {
            return $opportunity->assigned_to === $user->id;
        }

        return $user->hasPermissionTo('sales.manage')
            || $user->hasRole(['Super Admin', 'Dealer Manager', 'Sales Manager']);
    }

    /**
     * Boleh mengubah penugasan sales opportunity.
     */
    public function assign(User $user, SalesOpportunity $opportunity): bool
    {
        return $user->hasPermissionTo('leads.assign')
            || $user->hasRole(['Super Admin', 'Dealer Manager', 'Sales Manager']);
    }
}

