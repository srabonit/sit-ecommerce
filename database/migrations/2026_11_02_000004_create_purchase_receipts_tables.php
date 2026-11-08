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
        // 1. Goods Received Note / Purchase Receipts Table
        if (!Schema::hasTable('purchase_receipts')) {
            Schema::create('purchase_receipts', function (Blueprint $table) {
                $table->id();
                $table->string('receipt_number')->unique();
                $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
                $table->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
                $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->string('supplier_challan_no')->nullable();
                $table->date('receiving_date');
                $table->enum('receiving_status', [
                    'draft',
                    'under_inspection',
                    'partially_accepted',
                    'accepted',
                    'rejected',
                    'cancelled'
                ])->default('under_inspection');
                $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('inspected_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('inspection_notes')->nullable();
                $table->string('attachment_file')->nullable();
                $table->timestamps();

                $table->index(['supplier_id', 'receiving_status']);
                $table->index('receiving_date');
                $table->index('supplier_challan_no');
            });
        }

        // 2. Purchase Receipt Item Details Table
        if (!Schema::hasTable('purchase_receipt_items')) {
            Schema::create('purchase_receipt_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_receipt_id')->constrained('purchase_receipts')->cascadeOnDelete();
                $table->foreignId('purchase_order_item_id')->nullable()->constrained('purchase_order_items')->nullOnDelete();
                $table->foreignId('purchase_item_id')->nullable()->constrained('purchase_items')->nullOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->string('sku', 100)->nullable();
                $table->string('barcode', 100)->nullable();
                $table->string('batch_no', 100)->nullable();
                $table->date('expiry_date')->nullable();
                $table->decimal('ordered_quantity', 12, 2)->default(0.00);
                $table->decimal('received_quantity', 12, 2)->default(0.00);
                $table->decimal('accepted_quantity', 12, 2)->default(0.00);
                $table->decimal('rejected_quantity', 12, 2)->default(0.00);
                $table->string('rejection_reason')->nullable(); // damaged, wrong_spec, expired, quality_fail
                $table->decimal('unit_cost', 14, 2)->default(0.00);
                $table->decimal('total_cost', 14, 2)->default(0.00);
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index(['purchase_receipt_id', 'product_id'], 'pr_item_receipt_product_idx');
                $table->index('variant_id', 'pr_item_variant_idx');
                $table->index('batch_no', 'pr_item_batch_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_receipt_items');
        Schema::dropIfExists('purchase_receipts');
    }
};