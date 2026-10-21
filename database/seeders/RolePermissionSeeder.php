<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions List
        $permissions = [
            'user.create', 'user.view', 'user.edit', 'user.delete',
            'product.create', 'product.view', 'product.edit', 'product.delete',
            'inventory.view', 'inventory.adjust', 'inventory.transfer',
            'category.manage', 'brand.manage',
            'pos.checkout',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Roles
        $superAdminRole    = Role::firstOrCreate(['name' => 'super-admin']);
        $branchManagerRole = Role::firstOrCreate(['name' => 'branch-manager']);
        $posOperatorRole   = Role::firstOrCreate(['name' => 'pos-operator']);
        $customerRole      = Role::firstOrCreate(['name' => 'customer']);

        // Assign All Permissions to Super Admin
        $superAdminRole->syncPermissions(Permission::all());

        // Assign Specific Permissions to Branch Manager
        $branchManagerRole->syncPermissions([
            'product.view',
            'inventory.view',
            'inventory.adjust',
            'inventory.transfer',
            'pos.checkout'
        ]);

        // Assign POS Checkout Permissions to Operator
        $posOperatorRole->syncPermissions([
            'pos.checkout',
            'product.view',
            'inventory.view'
        ]);
    }
}