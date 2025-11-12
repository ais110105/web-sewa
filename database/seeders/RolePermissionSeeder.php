<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions based on existing features
        $permissions = [
            // Dashboard Access
            'view-dashboard-privilege',    // Admin/Owner dashboard
            'view-dashboard-standard',     // User dashboard

            // User Management (Admin/Owner only)
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',

            // Role Management (Owner only)
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles',

            // Permission Management (Owner only)
            'view-permissions',
            'assign-permissions',

            // Category Management (Admin/Owner)
            'view-categories',
            'create-categories',
            'edit-categories',
            'delete-categories',

            // Item Management (Admin/Owner - manage inventory)
            'view-items',
            'create-items',
            'edit-items',
            'delete-items',

            // Catalog (Regular Users - browse and rent items)
            'view-catalog',        
            'manage-cart',        

            // My Rentals (Regular Users - their own rentals only)
            'view-rentals',        
            'create-rentals',      
            'cancel-rentals',      

            // Rental Management (Admin/Owner - manage ALL user rentals)
            'manage-all-rentals',  

            // Payment Management (Admin/Owner - view ALL transactions)
            'manage-all-payments', 

            // Profile Management (All users)
            'view-profile',
            'edit-profile',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        
        $owner = Role::firstOrCreate(['name' => 'owner']);
        $owner->syncPermissions(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            // Dashboard
            'view-dashboard-privilege',

            // User Management (view and edit only, no create/delete)
            'view-users',
            'edit-users',

            // Category Management
            'view-categories',
            'create-categories',
            'edit-categories',
            'delete-categories',

            // Item Management (Full CRUD)
            'view-items',
            'create-items',
            'edit-items',
            'delete-items',

            // Rental Management (manage all users' rentals)
            'manage-all-rentals',

            // Payment Management (view all transactions)
            'manage-all-payments',

            // Profile
            'view-profile',
            'edit-profile',
        ]);

        $user = Role::firstOrCreate(['name' => 'user']);
        $user->syncPermissions([
            // Dashboard
            'view-dashboard-standard',

            // Catalog & Cart
            'view-catalog',
            'manage-cart',

            // My Rentals (own rentals only)
            'view-rentals',
            'create-rentals',
            'cancel-rentals',

            // Profile
            'view-profile',
            'edit-profile',
        ]);
    }
}
