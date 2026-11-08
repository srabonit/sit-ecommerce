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
        // 1. Supplier Exchanges Table
        if (Schema::hasTable('supplier_exchanges')) {
            Schema::table('supplier_exchanges', function (Blueprint $table) {
                if (!Schema::hasColumn('supplier_exchanges', 'outward_total')) {
                    $table->decimal('outward_total', 14, 2)->default(0.00)->after('warehouse_id');
                }
                if (!Schema::hasColumn('supplier_exchanges', 'inward_total')) {
                    $table->decimal('inward_total', 14, 2)->default(0.00)->after('outward_total');
                }
                if (!Schema::hasColumn('supplier_exchanges', 'differential_amount')) {
                    $table->decimal('differential_amount', 14, 2)->default(0.00)->after('inward_total');
                }
                if (!Schema::hasColumn('supplier_exchanges', 'settlement_type')) {
                    $table->string('settlement_type', 50)->default('ledger_adjustment')->after('differential_amount');
                }
                if (!Schema::hasColumn('supplier_exchanges', 'reference_no')) {
                    $table->string('reference_no', 100)->nullable()->after('exchange_date')->index();
                }
                if (!Schema::hasColumn('supplier_exchanges', 'bank_account_id')) {
                    $table->unsignedBigInteger('bank_account_id')->nullable()->after('settlement_type');
                }
                if (!Schema::hasColumn('supplier_exchanges', 'attachment_file')) {
                    $table->string('attachment_file', 255)->nullable()->after('status');
                }
                if (!Schema::hasColumn('supplier_exchanges', 'cancelled_reason')) {
                    $table->string('cancelled_reason', 500)->nullable()->after('notes');
                }
            });

            // Sync purono column data with new columns
            try {
                DB::statement("UPDATE `supplier_exchanges` SET `outward_total` = COALESCE(NULLIF(`outward_total`, 0), `returned_total_value`, 0) WHERE `outward_total` = 0");
                DB::statement("UPDATE `supplier_exchanges` SET `inward_total` = COALESCE(NULLIF(`inward_total`, 0), `replacement_total_value`, 0) WHERE `inward_total` = 0");
                DB::statement("UPDATE `supplier_exchanges` SET `differential_amount` = COALESCE(NULLIF(`differential_amount`, 0), `price_difference`, 0) WHERE `differential_amount` = 0");
                DB::statement("UPDATE `supplier_exchanges` SET `settlement_type` = COALESCE(`difference_settlement`, 'ledger_adjustment') WHERE `settlement_type` IS NULL OR `settlement_type` = ''");
            } catch (\Throwable $e) {}
        }

        // 2. Supplier Exchange Items Table
        if (Schema::hasTable('supplier_exchange_items')) {
            Schema::table('supplier_exchange_items', function (Blueprint $table) {
                if (!Schema::hasColumn('supplier_exchange_items', 'reason')) {
                    $table->string('reason', 255)->nullable()->after('total_cost');
                }
                if (!Schema::hasColumn('supplier_exchange_items', 'expiry_date')) {
                    $table->date('expiry_date')->nullable()->after('batch_no');
                }
                if (!Schema::hasColumn('supplier_exchange_items', 'total')) {
                    $table->decimal('total', 14, 2)->default(0.00)->after('total_cost');
                }
            });

            try {
                DB::statement("UPDATE `supplier_exchange_items` SET `total` = `total_cost` WHERE `total` = 0 AND `total_cost` > 0");
                DB::statement("UPDATE `supplier_exchange_items` SET `reason` = `remarks` WHERE `reason` IS NULL AND `remarks` IS NOT NULL");
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};