<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // System Permissions (Tahap 1 - Tahap 4)
        $permissions = [
            // Core & Users
            'dashboard.view',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.view',
            'roles.manage',
            'permissions.manage',
            'profile.view',
            'profile.update',
            'settings.manage',
            'audit.view',
            'master-data.manage',

            // Customer Portal & Inquiries
            'customer-portal.view',
            'customer-profile.view',
            'customer-profile.update',
            'inquiries.create',
            'inquiries.view-own',
            'inquiries.manage',

            // CRM & Leads
            'crm.dashboard.view',
            'leads.view',
            'leads.view-team',
            'leads.create',
            'leads.update',
            'leads.assign',
            'leads.change-status',
            'leads.add-activity',
            'leads.manage',

            // Vehicles & Inventory
            'vehicles.view',
            'vehicles.create',
            'vehicles.update',
            'vehicles.delete',
            'vehicles.publish',
            'inventory.view',
            'inventory.manage',

            // Customers
            'customers.view',
            'customers.manage',

            // Sales, Test Drive & Others
            'sales.view',
            'sales.manage',
            'test-drives.view',
            'test-drives.manage',
            'financing.view',
            'financing.manage',
            'payments.view',
            'payments.manage',
            'service-bookings.view',
            'service-bookings.manage',
            'service-orders.view',
            'service-orders.manage',
            'service-schedules.manage',
            'support-tickets.view',
            'support-tickets.manage',
            'notifications.view',
            'notifications.manage',
            'content.view',
            'content.manage',
            'reports.view',
            'analytics.view',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // 11 Core Roles with logical permissions
        $rolePermissions = [
            'Super Admin' => $permissions,

            'Dealer Manager' => [
                'dashboard.view',
                'crm.dashboard.view',
                'users.view',
                'roles.view',
                'profile.view',
                'profile.update',
                'vehicles.view',
                'inventory.view',
                'customers.view',
                'leads.view',
                'leads.view-team',
                'leads.manage',
                'inquiries.manage',
                'sales.view',
                'test-drives.view',
                'financing.view',
                'payments.view',
                'service-bookings.view',
                'service-bookings.manage',
                'service-orders.view',
                'service-orders.manage',
                'service-schedules.manage',
                'support-tickets.view',
                'support-tickets.manage',
                'notifications.view',
                'notifications.manage',
                'content.view',
                'reports.view',
                'analytics.view',
            ],

            'Sales Manager' => [
                'dashboard.view',
                'crm.dashboard.view',
                'profile.view',
                'profile.update',
                'vehicles.view',
                'customers.view',
                'customers.manage',
                'leads.view',
                'leads.view-team',
                'leads.create',
                'leads.update',
                'leads.assign',
                'leads.change-status',
                'leads.add-activity',
                'leads.manage',
                'inquiries.manage',
                'sales.view',
                'sales.manage',
                'test-drives.view',
                'test-drives.manage',
                'notifications.view',
                'reports.view',
                'analytics.view',
            ],

            'Sales Executive' => [
                'dashboard.view',
                'crm.dashboard.view',
                'profile.view',
                'profile.update',
                'vehicles.view',
                'customers.view',
                'leads.view',
                'leads.update',
                'leads.change-status',
                'leads.add-activity',
                'sales.view',
                'sales.manage',
                'test-drives.view',
                'test-drives.manage',
                'analytics.view',
            ],

            'CRM Officer' => [
                'dashboard.view',
                'crm.dashboard.view',
                'profile.view',
                'profile.update',
                'customers.view',
                'customers.manage',
                'leads.view',
                'leads.view-team',
                'leads.create',
                'leads.update',
                'leads.assign',
                'leads.change-status',
                'leads.add-activity',
                'leads.manage',
                'inquiries.manage',
                'test-drives.view',
                'support-tickets.view',
                'support-tickets.manage',
                'notifications.view',
                'notifications.manage',
                'reports.view',
                'analytics.view',
            ],

            'Inventory Officer' => [
                'dashboard.view',
                'profile.view',
                'profile.update',
                'vehicles.view',
                'vehicles.create',
                'vehicles.update',
                'vehicles.delete',
                'vehicles.publish',
                'inventory.view',
                'inventory.manage',
                'analytics.view',
            ],

            'Finance Officer' => [
                'dashboard.view',
                'profile.view',
                'profile.update',
                'customers.view',
                'sales.view',
                'financing.view',
                'payments.view',
                'payments.manage',
                'notifications.view',
                'reports.view',
                'analytics.view',
            ],

            'Leasing Officer' => [
                'dashboard.view',
                'profile.view',
                'profile.update',
                'customers.view',
                'sales.view',
                'financing.view',
                'financing.manage',
            ],

            'Service Advisor' => [
                'dashboard.view',
                'profile.view',
                'profile.update',
                'customers.view',
                'service-bookings.view',
                'service-bookings.manage',
                'service-orders.view',
                'service-orders.manage',
                'service-schedules.manage',
                'notifications.view',
                'analytics.view',
            ],

            'Content Manager' => [
                'dashboard.view',
                'profile.view',
                'profile.update',
                'vehicles.view',
                'vehicles.publish',
                'content.view',
                'content.manage',
            ],

            'Owner' => [
                'dashboard.view',
                'analytics.view',
                'reports.view',
                'payments.view',
                'sales.view',
                'vehicles.view',
                'inventory.view',
                'profile.view',
                'profile.update',
            ],

            'Customer' => [
                'customer-portal.view',
                'customer-profile.view',
                'customer-profile.update',
                'inquiries.create',
                'inquiries.view-own',
                'profile.view',
                'profile.update',
                'test-drives.view',
                'service-bookings.view',
                'financing.view',
                'support-tickets.view',
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($perms);
        }

        // Seed initial reference accounts for development & testing
        $initialAccounts = [
            [
                'name' => 'Super Administrator Toyota',
                'email' => 'superadmin@toyota-autoconnect.id',
                'phone' => '081290001001',
                'password' => 'password123',
                'role' => 'Super Admin',
            ],
            [
                'name' => 'Budi Santoso (Dealer Manager)',
                'email' => 'dealermanager@toyota-autoconnect.id',
                'phone' => '081290001002',
                'password' => 'password123',
                'role' => 'Dealer Manager',
            ],
            [
                'name' => 'Rina Wijaya (Sales Manager)',
                'email' => 'salesmanager@toyota-autoconnect.id',
                'phone' => '081290001003',
                'password' => 'password123',
                'role' => 'Sales Manager',
            ],
            [
                'name' => 'Dimas Pratama (Sales Executive)',
                'email' => 'salesexec@toyota-autoconnect.id',
                'phone' => '081290001004',
                'password' => 'password123',
                'role' => 'Sales Executive',
            ],
            [
                'name' => 'Maya Anggraini (CRM Officer)',
                'email' => 'crmofficer@toyota-autoconnect.id',
                'phone' => '081290001006',
                'password' => 'password123',
                'role' => 'CRM Officer',
            ],
            [
                'name' => 'Bambang Soediro (Owner / Komisaris)',
                'email' => 'owner@toyota-autoconnect.id',
                'phone' => '081290001007',
                'password' => 'password123',
                'role' => 'Owner',
            ],
            [
                'name' => 'Ahmad Customer',
                'email' => 'customer@toyota-autoconnect.id',
                'phone' => '081290001005',
                'password' => 'password123',
                'role' => 'Customer',
            ],
        ];

        foreach ($initialAccounts as $acc) {
            $user = User::firstOrCreate(
                ['email' => $acc['email']],
                [
                    'name' => $acc['name'],
                    'phone' => $acc['phone'],
                    'password' => Hash::make($acc['password']),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            if (! $user->hasRole($acc['role'])) {
                $user->syncRoles([$acc['role']]);
            }
        }
    }
}
