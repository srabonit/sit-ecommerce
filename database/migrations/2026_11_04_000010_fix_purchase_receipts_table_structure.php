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
        // 1. Purchase Receipts Table Fix
        if (Schema::hasTable('purchase_receipts')) {
            // MySQL ENUM কে সেফ VARCHAR এ রূপান্তর
            try {
                DB::statement("ALTER TABLE `purchase_receipts` MODIFY COLUMN `receiving_status` VARCHAR(50) NOT NULL DEFAULT 'under_inspection'");
            } catch (\Throwable $e) {}

            Schema::table('purchase_receipts', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_receipts', 'challan_no')) {
                    $table->string('challan_no', 100)->nullable()->after('supplier_challan_no')->index();
                }
                if (!Schema::hasColumn('purchase_receipts', 'receipt_date')) {
                    $table->date('receipt_date')->nullable()->after('receiving_date')->index();
                }
                if (!Schema::hasColumn('purchase_receipts', 'status')) {
                    $table->string('status', 50)->default('received')->after('receiving_status')->index();
                }
                if (!Schema::hasColumn('purchase_receipts', 'vehicle_number')) {
                    $table->string('vehicle_number', 100)->nullable()->after('attachment_file');
                }
                if (!Schema::hasColumn('purchase_receipts', 'notes')) {
                    $table->text('notes')->nullable()->after('vehicle_number');
                }
            });

            try {
                DB::statement("UPDATE `purchase_receipts` SET `challan_no` = `supplier_challan_no` WHERE `challan_no` IS NULL");
                DB::statement("UPDATE `purchase_receipts` SET `receipt_date` = `receiving_date` WHERE `receipt_date` IS NULL");
                DB::statement("UPDATE `purchase_receipts` SET `status` = `receiving_status` WHERE `status` = 'received' AND `receiving_status` != 'under_inspection'");
            } catch (\Throwable $e) {}
        }

        // 2. Purchase Receipt Items Table Fix
        if (Schema::hasTable('purchase_receipt_items')) {
            Schema::table('purchase_receipt_items', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_receipt_items', 'delivered_quantity')) {
                    $table->decimal('delivered_quantity', 12, 2)->default(0.00)->after('ordered_quantity');
                }
                if (!Schema::hasColumn('purchase_receipt_items', 'received_qty')) {
                    $table->decimal('received_qty', 12, 2)->default(0.00)->after('received_quantity');
                }
                if (!Schema::hasColumn('purchase_receipt_items', 'rejected_qty')) {
                    $table->decimal('rejected_qty', 12, 2)->default(0.00)->after('rejected_quantity');
                }
            });

            try {
                DB::statement("UPDATE `purchase_receipt_items` SET `delivered_quantity` = `received_quantity`, `received_qty` = `received_quantity`, `rejected_qty` = `rejected_quantity` WHERE `delivered_quantity` = 0");
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