<?php

namespace App\Services\Analytics;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ScopeFilter
{
    /**
     * Resolve the branch ID that the user is authorized to inspect.
     */
    public static function getAuthorizedBranchId(User $user, ?int $requestedBranchId = null): ?int
    {
        if ($user->hasRole(['Super Admin', 'Owner'])) {
            return $requestedBranchId ?: null;
        }

        // Branch-scoped staff are strictly limited to their assigned branch
        if (! empty($user->branch_id)) {
            return (int) $user->branch_id;
        }

        return $requestedBranchId ?: null;
    }

    /**
     * Apply branch scoping to an Eloquent / Query Builder query.
     */
    public static function applyBranchScope(Builder $query, User $user, ?int $requestedBranchId = null, string $column = 'branch_id'): Builder
    {
        $branchId = self::getAuthorizedBranchId($user, $requestedBranchId);

        if ($branchId !== null) {
            $query->where($column, $branchId);
        }

        return $query;
    }

    /**
     * Apply user scoping for sales executives.
     */
    public static function applySalesExecutiveScope(Builder $query, User $user, string $userColumn = 'assigned_to'): Builder
    {
        if ($user->hasRole('Sales Executive') && ! $user->can('leads.view-team')) {
            $query->where($userColumn, $user->id);
        }

        return $query;
    }
}
