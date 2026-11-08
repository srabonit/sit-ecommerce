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
        // 1. Categories Table Fix (Add missing status column)
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (!Schema::hasColumn('categories', 'status')) {
                    $table->string('status', 20)->default('active')->after('id');
                }
            });
        }

        // 2. Purchases Table Fix (Ensure purchase_no & purchase_number both exist with safe defaults)
        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                if (!Schema::hasColumn('purchases', 'purchase_no')) {
                    $table->string('purchase_no', 100)->nullable()->after('id');
                }
                if (!Schema::hasColumn('purchases', 'purchase_number')) {
                    $table->string('purchase_number', 100)->nullable()->after('id');
                }
                if (!Schema::hasColumn('purchases', 'payment_terms')) {
                    $table->string('payment_terms', 50)->default('net_30')->nullable();
                }
                if (!Schema::hasColumn('purchases', 'round_off')) {
                    $table->decimal('round_off', 15, 2)->default(0.00);
                }
                if (!Schema::hasColumn('purchases', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable();
                }
            });

            // Sync purchase_no and purchase_number if empty
            DB::statement("UPDATE `purchases` SET `purchase_no` = COALESCE(`purchase_no`, `purchase_number`, CONCAT('BILL-', `id`)) WHERE `purchase_no` IS NULL OR `purchase_no` = ''");
            DB::statement("UPDATE `purchases` SET `purchase_number` = `purchase_no` WHERE `purchase_number` IS NULL OR `purchase_number` = ''");
        }

        // 3. Supplier Payments Table Fix (Convert ENUM to VARCHAR to eliminate truncation errors)
        if (Schema::hasTable('supplier_payments')) {
            // Direct MySQL statements bypass doctrine dbal enum limitations
            DB::statement("ALTER TABLE `supplier_payments` MODIFY COLUMN `payment_type` VARCHAR(50) NOT NULL DEFAULT 'invoice_payment'");
            DB::statement("ALTER TABLE `supplier_payments` MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'posted'");

            Schema::table('supplier_payments', function (Blueprint $table) {
                if (!Schema::hasColumn('supplier_payments', 'voucher_number')) {
                    $table->string('voucher_number', 100)->nullable()->after('id');
                }
                if (!Schema::hasColumn('supplier_payments', 'payment_number')) {
                    $table->string('payment_number', 100)->nullable()->after('id');
                }
                if (!Schema::hasColumn('supplier_payments', 'allocated_amount')) {
                    $table->decimal('allocated_amount', 15, 2)->default(0.00)->after('amount');
                }
                if (!Schema::hasColumn('supplier_payments', 'cheque_date')) {
                    $table->date('cheque_date')->nullable();
                }
                if (!Schema::hasColumn('supplier_payments', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable();
                }
            });

            DB::statement("UPDATE `supplier_payments` SET `voucher_number` = COALESCE(`voucher_number`, `payment_number`, CONCAT('SPV-', `id`)) WHERE `voucher_number` IS NULL OR `voucher_number` = ''");
            DB::statement("UPDATE `supplier_payments` SET `payment_number` = `voucher_number` WHERE `payment_number` IS NULL OR `payment_number` = ''");
        }

        // 4. Supplier Payment Allocations Table Fix
        if (Schema::hasTable('supplier_payment_allocations')) {
            Schema::table('supplier_payment_allocations', function (Blueprint $table) {
                if (!Schema::hasColumn('supplier_payment_allocations', 'allocated_amount')) {
                    $table->decimal('allocated_amount', 15, 2)->default(0.00)->after('purchase_id');
                }
                if (!Schema::hasColumn('supplier_payment_allocations', 'amount')) {
                    $table->decimal('amount', 15, 2)->default(0.00)->after('purchase_id');
                }
            });
        }

        // 5. Suppliers Table Fix
        if (Schema::hasTable('suppliers')) {
            Schema::table('suppliers', function (Blueprint $table) {
                if (!Schema::hasColumn('suppliers', 'status')) {
                    $table->string('status', 20)->default('active')->after('id');
                }
                if (!Schema::hasColumn('suppliers', 'current_balance')) {
                    $table->decimal('current_balance', 15, 2)->default(0.00);
                }
                if (!Schema::hasColumn('suppliers', 'opening_balance')) {
                    $table->decimal('opening_balance', 15, 2)->default(0.00);
                }
                if (!Schema::hasColumn('suppliers', 'credit_limit')) {
                    $table->decimal('credit_limit', 15, 2)->default(0.00);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe rollback
    }
};