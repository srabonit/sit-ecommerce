<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Categories Table Fix (Missing status column)
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (!Schema::hasColumn('categories', 'status')) {
                    $table->string('status', 20)->default('active')->after('id');
                }
            });
        }

        // 2. Suppliers Table Fix (Missing current_balance column)
        if (Schema::hasTable('suppliers')) {
            Schema::table('suppliers', function (Blueprint $table) {
                if (!Schema::hasColumn('suppliers', 'current_balance')) {
                    $table->decimal('current_balance', 15, 2)->default(0.00)->after('opening_balance');
                }
                if (!Schema::hasColumn('suppliers', 'payment_terms')) {
                    $table->string('payment_terms', 50)->default('net_30')->after('status');
                }
            });
            DB::statement("UPDATE `suppliers` SET `current_balance` = `opening_balance` WHERE `current_balance` = 0.00 AND `opening_balance` != 0.00");
        }

        // 3. Purchases Table Fix (Ensure purchase_no and purchase_number exist)
        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                if (!Schema::hasColumn('purchases', 'purchase_no')) {
                    $table->string('purchase_no', 100)->nullable()->after('id');
                }
                if (!Schema::hasColumn('purchases', 'purchase_number')) {
                    $table->string('purchase_number', 100)->nullable()->after('id');
                }
                if (!Schema::hasColumn('purchases', 'due_date')) {
                    $table->date('due_date')->nullable()->after('purchase_date');
                }
                if (!Schema::hasColumn('purchases', 'payment_terms')) {
                    $table->string('payment_terms', 50)->default('net_30');
                }
                if (!Schema::hasColumn('purchases', 'round_off')) {
                    $table->decimal('round_off', 15, 2)->default(0.00);
                }
            });

            DB::statement("UPDATE `purchases` SET `purchase_no` = COALESCE(NULLIF(`purchase_no`, ''), `purchase_number`, CONCAT('BILL-', `id`)) WHERE `purchase_no` IS NULL OR `purchase_no` = ''");
            DB::statement("UPDATE `purchases` SET `purchase_number` = `purchase_no` WHERE `purchase_number` IS NULL OR `purchase_number` = ''");
        }

        // 4. Supplier Payments Table Fix
        if (Schema::hasTable('supplier_payments')) {
            DB::statement("ALTER TABLE `supplier_payments` MODIFY COLUMN `payment_type` VARCHAR(100) NOT NULL DEFAULT 'invoice_payment'");
            DB::statement("ALTER TABLE `supplier_payments` MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'posted'");

            Schema::table('supplier_payments', function (Blueprint $table) {
                if (!Schema::hasColumn('supplier_payments', 'voucher_number')) {
                    $table->string('voucher_number', 100)->nullable()->after('id');
                }
                if (!Schema::hasColumn('supplier_payments', 'allocated_amount')) {
                    $table->decimal('allocated_amount', 15, 2)->default(0.00)->after('amount');
                }
                if (!Schema::hasColumn('supplier_payments', 'unallocated_amount')) {
                    $table->decimal('unallocated_amount', 15, 2)->default(0.00)->after('allocated_amount');
                }
            });

            DB::statement("UPDATE `supplier_payments` SET `voucher_number` = COALESCE(NULLIF(`voucher_number`, ''), `payment_number`, CONCAT('SPV-', `id`)) WHERE `voucher_number` IS NULL OR `voucher_number` = ''");
            DB::statement("UPDATE `supplier_payments` SET `payment_number` = `voucher_number` WHERE `payment_number` IS NULL OR `payment_number` = ''");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};