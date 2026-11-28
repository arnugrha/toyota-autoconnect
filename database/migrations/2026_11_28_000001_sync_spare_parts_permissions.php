<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
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

            $permissions = [
                'spare-parts.view',
                'spare-parts.manage',
                'audit.view',
                'master-data.manage',
            ];

            foreach ($permissions as $name) {
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            }

            $rolesWithSpareParts = [
                'Super Admin',
                'Dealer Manager',
                'Inventory Officer',
                'Service Advisor',
            ];

            foreach ($rolesWithSpareParts as $roleName) {
                $role = Role::where(['name' => $roleName, 'guard_name' => 'web'])->first();
                if ($role) {
                    $role->givePermissionTo(['spare-parts.view', 'spare-parts.manage']);
                }
            }

            // Also assign audit and master-data to Super Admin & Dealer Manager
            $superAdmin = Role::where(['name' => 'Super Admin', 'guard_name' => 'web'])->first();
            if ($superAdmin) {
                $superAdmin->givePermissionTo(['audit.view', 'master-data.manage']);
            }

            $dealerManager = Role::where(['name' => 'Dealer Manager', 'guard_name' => 'web'])->first();
            if ($dealerManager) {
                $dealerManager->givePermissionTo(['audit.view', 'master-data.manage']);
            }

            app()[PermissionRegistrar::class]->forgetCachedPermissions();
        } catch (\Throwable $e) {
            // Graceful fallback if database connection or tables are not ready
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep permissions intact
    }
};
