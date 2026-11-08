<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('inventory_movements')) {
            Schema::table('inventory_movements', function (Blueprint $table) {
                // Supplier & Procurement Reference Links
                if (!Schema::hasColumn('inventory_movements', 'supplier_id')) {
                    $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
                }
                if (!Schema::hasColumn('inventory_movements', 'purchase_id')) {
                    $table->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
                }
                if (!Schema::hasColumn('inventory_movements', 'purchase_item_id')) {
                    $table->foreignId('purchase_item_id')->nullable()->constrained('purchase_items')->nullOnDelete();
                }
                if (!Schema::hasColumn('inventory_movements', 'purchase_receipt_id')) {
                    $table->foreignId('purchase_receipt_id')->nullable()->constrained('purchase_receipts')->nullOnDelete();
                }
                if (!Schema::hasColumn('inventory_movements', 'purchase_return_id')) {
                    $table->foreignId('purchase_return_id')->nullable()->constrained('purchase_returns')->nullOnDelete();
                }
                if (!Schema::hasColumn('inventory_movements', 'supplier_exchange_id')) {
                    $table->foreignId('supplier_exchange_id')->nullable()->constrained('supplier_exchanges')->nullOnDelete();
                }

                // Location Tracking (Warehouse & Branch)
                if (!Schema::hasColumn('inventory_movements', 'branch_id')) {
                    $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                }
                if (!Schema::hasColumn('inventory_movements', 'warehouse_id')) {
                    $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                }

                // Batch & Traceability
                if (!Schema::hasColumn('inventory_movements', 'receiving_reference')) {
                    $table->string('receiving_reference', 100)->nullable();
                }
                if (!Schema::hasColumn('inventory_movements', 'batch_no')) {
                    $table->string('batch_no', 100)->nullable();
                }
                if (!Schema::hasColumn('inventory_movements', 'expiry_date')) {
                    $table->date('expiry_date')->nullable();
                }

                // Performance Indexes for fast reporting and ledger audits
                $table->index(['supplier_id', 'movement_type'], 'im_supplier_mtype_idx');
                $table->index(['purchase_id', 'product_id'], 'im_purchase_product_idx');
                $table->index('purchase_receipt_id', 'im_receipt_idx');
                $table->index(['branch_id', 'warehouse_id'], 'im_location_idx');
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