<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            $ownerRole = Role::where(['name' => 'Owner', 'guard_name' => 'web'])->first();
            if ($ownerRole) {
                // Ensure Owner only has executive, analytics, reports, payments and profile permissions
                // Revoke operational spare parts and inventory permissions
                $ownerRole->revokePermissionTo([
                    'spare-parts.view',
                    'spare-parts.manage',
                    'inventory.view',
                    'inventory.manage',
                ]);

                $ownerRole->givePermissionTo([
                    'dashboard.view',
                    'analytics.view',
                    'reports.view',
                    'payments.view',
                    'sales.view',
                    'vehicles.view',
                    'profile.view',
                    'profile.update',
                ]);
            }

            app()[PermissionRegistrar::class]->forgetCachedPermissions();
        } catch (\Throwable $e) {
            // Keep migration robust if tables are not yet present
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
