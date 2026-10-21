<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Warehouse;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InitialMasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Branches
        $branchMain = Branch::create([
            'name'      => 'Main Branch - Dhaka',
            'code'      => 'BR-DHK',
            'address'   => 'Dhanmondi, Dhaka',
            'is_active' => true
        ]);

        $branchCtg = Branch::create([
            'name'      => 'Chittagong Branch',
            'code'      => 'BR-CTG',
            'address'   => 'Agrabad, Chattogram',
            'is_active' => true
        ]);

        // 2. Create Warehouses
        $warehouseCentral = Warehouse::create([
            'name'      => 'Central Warehouse',
            'code'      => 'WH-CENTRAL',
            'address'   => 'Gazipur',
            'is_active' => true
        ]);

        // 3. Create Super Admin User
        $admin = User::create([
            'name'      => 'Super Admin',
            'email'     => 'admin@example.com',
            'password'  => Hash::make('password123'),
            'user_type' => 'admin',
            'is_active' => true,
        ]);
        $admin->assignRole('super-admin');

        // 4. Create Branch Staff User
        $manager = User::create([
            'name'      => 'Dhaka Branch Manager',
            'email'     => 'manager.dhaka@example.com',
            'password'  => Hash::make('password123'),
            'user_type' => 'staff',
            'branch_id' => $branchMain->id,
            'is_active' => true,
        ]);
        $manager->assignRole('branch-manager');

        // 5. Create Sample Customer
        $customer = User::create([
            'name'      => 'John Doe',
            'email'     => 'customer@example.com',
            'password'  => Hash::make('password123'),
            'phone'     => '01700000000',
            'user_type' => 'customer',
            'is_active' => true,
        ]);
        $customer->assignRole('customer');

        // 6. Create Categories & Brands
        $catElectronics = Category::create(['name' => 'Electronics', 'slug' => 'electronics']);
        $catFashion     = Category::create(['name' => 'Fashion & Apparel', 'slug' => 'fashion-apparel']);

        $brandApple = Brand::create(['name' => 'Apple', 'slug' => 'apple']);
        $brandNike  = Brand::create(['name' => 'Nike', 'slug' => 'nike']);

        // 7. Create Products matching products table schema
        $products = [
            [
                'title'          => 'iPhone 15 Pro Max',
                'product_code'   => 'IPH-15-PM',
                'category_id'    => $catElectronics->id,
                'brand_id'       => $brandApple->id,
                'regular_price'  => 145000.00,
                'purchase_price' => 130000.00,
                'stock'          => 250,
            ],
            [
                'title'          => 'Nike Air Force 1',
                'product_code'   => 'NK-AF1-01',
                'category_id'    => $catFashion->id,
                'brand_id'       => $brandNike->id,
                'regular_price'  => 12500.00,
                'purchase_price' => 9500.00,
                'stock'          => 100,
            ]
        ];

        foreach ($products as $prodData) {
            $product = Product::create([
                'title'          => $prodData['title'],
                'slug'           => Str::slug($prodData['title']),
                'product_code'   => $prodData['product_code'],
                'category_id'    => $prodData['category_id'],
                'brand_id'       => $prodData['brand_id'],
                'regular_price'  => $prodData['regular_price'],
                'purchase_price' => $prodData['purchase_price'],
                'stock'          => $prodData['stock'],
                'product_type'   => 'physical',
                'product_struct' => 'simple',
            ]);

            // Branch Stock Record
            ProductStock::create([
                'product_id'     => $product->id,
                'branch_id'      => $branchMain->id,
                'quantity'       => 50,
                'alert_quantity' => 5,
            ]);

            // Warehouse Stock Record
            ProductStock::create([
                'product_id'     => $product->id,
                'warehouse_id'   => $warehouseCentral->id,
                'quantity'       => 200,
                'alert_quantity' => 10,
            ]);
        }
    }
}