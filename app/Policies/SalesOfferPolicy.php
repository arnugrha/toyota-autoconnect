<?php

namespace App\Policies;

use App\Models\SalesOffer;
use App\Models\User;

class SalesOfferPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('sales.view')
            || $user->hasPermissionTo('sales.manage')
            || $user->hasRole('Customer');
    }

    public function view(User $user, SalesOffer $offer): bool
    {
        if ($user->hasRole('Customer')) {
            return $user->id === $offer->customer_id;
        }

        if ($user->hasRole('Sales Executive')) {
            return $offer->salesOpportunity->assigned_to === $user->id
                || $offer->created_by === $user->id
                || $user->hasPermissionTo('leads.view-team');
        }

        return $user->hasPermissionTo('sales.view')
            || $user->hasPermissionTo('sales.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('sales.manage')
            || $user->hasRole(['Super Admin', 'Dealer Manager', 'Sales Manager', 'Sales Executive']);
    }

    public function update(User $user, SalesOffer $offer): bool
    {
        if ($user->hasRole('Customer')) {
            return false;
        }

        if ($user->hasRole('Sales Executive')) {
            return $offer->created_by === $user->id && $offer->status === 'draft';
        }

        return $user->hasPermissionTo('sales.manage')
            || $user->hasRole(['Super Admin', 'Dealer Manager', 'Sales Manager']);
    }
}

