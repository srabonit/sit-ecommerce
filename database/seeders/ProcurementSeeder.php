<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProcurementSeeder extends Seeder
{
    /**
     * Seed initial demo suppliers, warehouses, chart of accounts, and settings.
     */
    public function run(): void
    {
        // 1. Seed Warehouses if missing
        if (Schema::hasTable('warehouses') && DB::table('warehouses')->count() === 0) {
            DB::table('warehouses')->insert([
                [
                    'name'       => 'Central Hub Warehouse',
                    'code'       => 'WH-CENTRAL',
                    'address'    => 'Tejgaon Industrial Area, Dhaka',
                    'status'     => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name'       => 'Regional Distribution Depot',
                    'code'       => 'WH-REGIONAL',
                    'address'    => 'Agrabad Commercial Area, Chittagong',
                    'status'     => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // 2. Seed Default Chart of Accounts for Procurement GL Posting
        if (Schema::hasTable('chart_of_accounts') && DB::table('chart_of_accounts')->count() === 0) {
            $glAccounts = [
                ['name' => 'Trade Creditors (Accounts Payable)', 'code' => '2100', 'type' => 'liability'],
                ['name' => 'Raw Materials / Inventory Asset',   'code' => '1300', 'type' => 'asset'],
                ['name' => 'Procurement & Purchases Expense',  'code' => '5100', 'type' => 'expense'],
                ['name' => 'Purchase Returns & Allowances',    'code' => '5150', 'type' => 'contra_expense'],
                ['name' => 'Input VAT / Tax Receivable',        'code' => '1400', 'type' => 'asset'],
                ['name' => 'Carriage & Freight Inwards',       'code' => '5200', 'type' => 'expense'],
                ['name' => 'Plant, Machinery & Equipment',     'code' => '1500', 'type' => 'asset'],
                ['name' => 'Accumulated Depreciation - Plant', 'code' => '1550', 'type' => 'contra_asset'],
                ['name' => 'Depreciation Expense',             'code' => '6100', 'type' => 'expense'],
            ];

            foreach ($glAccounts as $account) {
                DB::table('chart_of_accounts')->insert(array_merge($account, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // 3. Auto-link Chart of Account IDs to Procurement Settings
        if (Schema::hasTable('chart_of_accounts') && Schema::hasTable('procurement_settings')) {
            $apId   = DB::table('chart_of_accounts')->where('code', '2100')->value('id');
            $invId  = DB::table('chart_of_accounts')->where('code', '1300')->value('id');
            $expId  = DB::table('chart_of_accounts')->where('code', '5100')->value('id');
            $retId  = DB::table('chart_of_accounts')->where('code', '5150')->value('id');
            $vatId  = DB::table('chart_of_accounts')->where('code', '1400')->value('id');
            $frtId  = DB::table('chart_of_accounts')->where('code', '5200')->value('id');

            $updates = [
                'gl_accounts_payable_id' => $apId,
                'gl_inventory_asset_id'  => $invId,
                'gl_purchase_expense_id' => $expId,
                'gl_purchase_return_id'  => $retId,
                'gl_input_tax_id'        => $vatId,
                'gl_freight_inward_id'   => $frtId,
            ];

            foreach ($updates as $key => $val) {
                if ($val) {
                    DB::table('procurement_settings')->where('key', $key)->update(['value' => $val]);
                }
            }
        }

        // 4. Seed Initial Suppliers
        if (Schema::hasTable('suppliers') && DB::table('suppliers')->count() === 0) {
            DB::table('suppliers')->insert([
                [
                    'name'                 => 'Haque Spinning & Fabrics Mills Ltd.',
                    'company_name'         => 'Haque Textile Conglomerate',
                    'contact_person'       => 'Md. Zahidul Haque',
                    'phone'                => '01711-002233',
                    'email'                => 'procurement@haquetextile.com',
                    'bin_number'           => '001928374-0101',
                    'tax_number'           => '892019482012',
                    'payment_terms'        => 'net_30',
                    'credit_limit'         => 500000.00,
                    'opening_balance'      => 0.00,
                    'current_balance'      => 0.00,
                    'opening_balance_date' => date('Y-m-d'),
                    'address'              => 'Plot 45, BSCIC Industrial Area, Gazipur',
                    'city'                 => 'Gazipur',
                    'country'              => 'Bangladesh',
                    'status'               => 'active',
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ],
                [
                    'name'                 => 'Rahman Packaging & Accessories',
                    'company_name'         => 'Rahman Poly Industries',
                    'contact_person'       => 'Mizanur Rahman',
                    'phone'                => '01819-445566',
                    'email'                => 'sales@rahmanpoly.com',
                    'bin_number'           => '003847291-0202',
                    'tax_number'           => '542019482991',
                    'payment_terms'        => 'net_15',
                    'credit_limit'         => 200000.00,
                    'opening_balance'      => 0.00,
                    'current_balance'      => 0.00,
                    'opening_balance_date' => date('Y-m-d'),
                    'address'              => 'Holding 12, Postogola Riverview, Dhaka',
                    'city'                 => 'Dhaka',
                    'country'              => 'Bangladesh',
                    'status'               => 'active',
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ],
            ]);
        }
    }
}