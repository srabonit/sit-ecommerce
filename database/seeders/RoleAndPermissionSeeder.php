<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define Granular Permissions
        $permissions = [
            // Product Module
            'product.view', 'product.create', 'product.edit', 'product.delete',
            // Category & Brand
            'category.manage', 'brand.manage',
            // Inventory & Multi-Stock
            'inventory.view', 'inventory.adjust', 'inventory.transfer',
            // POS Operations
            'pos.access', 'pos.checkout', 'pos.apply-discount',
            // Orders & Sales
            'order.view', 'order.create', 'order.edit', 'order.delete', 'order.status-change',
            // Branch & Warehouse Management
            'branch.manage', 'warehouse.manage',
            // User & Role Management
            'user.view', 'user.create', 'user.edit', 'user.delete',
            'role.manage',
            // Reports & Analytics
            'report.view', 'report.export',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'api');
        }

        // 2. Define Multi-Roles
        $superAdminRole = Role::findOrCreate('Super Admin', 'api');
        $branchManagerRole = Role::findOrCreate('Branch Manager', 'api');
        $posOperatorRole = Role::findOrCreate('POS Operator', 'api');
        $inventoryManagerRole = Role::findOrCreate('Inventory Manager', 'api');

        // Give All Permissions to Super Admin
        $superAdminRole->syncPermissions(Permission::all());

        // Assign Specific Permissions to Branch Manager
        $branchManagerRole->syncPermissions([
            'product.view', 'inventory.view', 'inventory.adjust',
            'pos.access', 'pos.checkout', 'order.view', 'order.create', 'order.edit',
            'report.view'
        ]);

        // Assign Specific Permissions to POS Operator
        $posOperatorRole->syncPermissions([
            'pos.access', 'pos.checkout', 'order.view', 'order.create'
        ]);

        // Create Default Super Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@ecommerce.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'user_type' => 'super_admin',
                'is_active' => true,
            ]
        );

        $admin->assignRole($superAdminRole);
    }
}