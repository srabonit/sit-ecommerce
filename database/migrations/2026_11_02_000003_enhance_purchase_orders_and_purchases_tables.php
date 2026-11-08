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
        // 1. Enhance Purchase Orders Table
        if (Schema::hasTable('purchase_orders')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_orders', 'po_number')) {
                    $table->string('po_number')->unique()->nullable();
                }
                if (!Schema::hasColumn('purchase_orders', 'supplier_id')) {
                    $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
                }
                if (!Schema::hasColumn('purchase_orders', 'branch_id')) {
                    $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                }
                if (!Schema::hasColumn('purchase_orders', 'warehouse_id')) {
                    $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                }
                if (!Schema::hasColumn('purchase_orders', 'order_date')) {
                    $table->date('order_date')->nullable();
                }
                if (!Schema::hasColumn('purchase_orders', 'expected_delivery_date')) {
                    $table->date('expected_delivery_date')->nullable();
                }
                if (!Schema::hasColumn('purchase_orders', 'reference_no')) {
                    $table->string('reference_no')->nullable();
                }
                if (!Schema::hasColumn('purchase_orders', 'subtotal')) {
                    $table->decimal('subtotal', 14, 2)->default(0.00);
                }
                if (!Schema::hasColumn('purchase_orders', 'discount_amount')) {
                    $table->decimal('discount_amount', 14, 2)->default(0.00);
                }
                if (!Schema::hasColumn('purchase_orders', 'tax_amount')) {
                    $table->decimal('tax_amount', 14, 2)->default(0.00);
                }
                if (!Schema::hasColumn('purchase_orders', 'shipping_cost')) {
                    $table->decimal('shipping_cost', 14, 2)->default(0.00);
                }
                if (!Schema::hasColumn('purchase_orders', 'total_amount')) {
                    $table->decimal('total_amount', 14, 2)->default(0.00);
                }
                if (!Schema::hasColumn('purchase_orders', 'status')) {
                    $table->enum('status', [
                        'draft',
                        'pending_approval',
                        'approved',
                        'ordered',
                        'partially_received',
                        'fully_received',
                        'cancelled'
                    ])->default('draft');
                }
                if (!Schema::hasColumn('purchase_orders', 'created_by')) {
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('purchase_orders', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('purchase_orders', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable();
                }
                if (!Schema::hasColumn('purchase_orders', 'cancelled_by')) {
                    $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('purchase_orders', 'cancelled_at')) {
                    $table->timestamp('cancelled_at')->nullable();
                }
                if (!Schema::hasColumn('purchase_orders', 'terms_conditions')) {
                    $table->text('terms_conditions')->nullable();
                }
                if (!Schema::hasColumn('purchase_orders', 'notes')) {
                    $table->text('notes')->nullable();
                }
            });
        }

        // 2. Enhance Purchase Order Items Table
        if (Schema::hasTable('purchase_order_items')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_order_items', 'variant_id')) {
                    $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                }
                if (!Schema::hasColumn('purchase_order_items', 'sku')) {
                    $table->string('sku', 100)->nullable();
                }
                if (!Schema::hasColumn('purchase_order_items', 'barcode')) {
                    $table->string('barcode', 100)->nullable();
                }
                if (!Schema::hasColumn('purchase_order_items', 'unit_cost')) {
                    $table->decimal('unit_cost', 14, 2)->default(0.00);
                }
                if (!Schema::hasColumn('purchase_order_items', 'discount_amount')) {
                    $table->decimal('discount_amount', 14, 2)->default(0.00);
                }
                if (!Schema::hasColumn('purchase_order_items', 'tax_amount')) {
                    $table->decimal('tax_amount', 14, 2)->default(0.00);
                }
                if (!Schema::hasColumn('purchase_order_items', 'total_cost')) {
                    $table->decimal('total_cost', 14, 2)->default(0.00);
                }
                if (!Schema::hasColumn('purchase_order_items', 'received_quantity')) {
                    $table->decimal('received_quantity', 12, 2)->default(0.00);
                }
            });
        }

        // 3. Create or Enhance Purchases Table (Purchase Invoices / Bills)
        if (!Schema::hasTable('purchases')) {
            Schema::create('purchases', function (Blueprint $table) {
                $table->id();
                $table->string('purchase_number')->unique();
                $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
                $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->warehouse_id = $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->string('supplier_invoice_no')->nullable();
                $table->string('supplier_challan_no')->nullable();
                $table->date('purchase_date');
                $table->date('receiving_date')->nullable();
                $table->enum('purchase_type', [
                    'sellable_inventory',
                    'fixed_asset',
                    'raw_material',
                    'consumable',
                    'non_stock'
                ])->default('sellable_inventory');
                $table->decimal('subtotal', 14, 2)->default(0.00);
                $table->decimal('discount_amount', 14, 2)->default(0.00);
                $table->decimal('tax_amount', 14, 2)->default(0.00);
                $table->decimal('shipping_cost', 14, 2)->default(0.00);
                $table->decimal('other_charges', 14, 2)->default(0.00);
                $table->decimal('grand_total', 14, 2)->default(0.00);
                $table->decimal('paid_amount', 14, 2)->default(0.00);
                $table->decimal('due_amount', 14, 2)->default(0.00);
                $table->enum('purchase_status', [
                    'draft',
                    'pending_approval',
                    'ordered',
                    'partially_received',
                    'received',
                    'cancelled',
                    'returned'
                ])->default('draft');
                $table->enum('payment_status', [
                    'unpaid',
                    'partially_paid',
                    'paid',
                    'overdue',
                    'refunded',
                    'adjusted'
                ])->default('unpaid');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['supplier_id', 'purchase_status']);
                $table->index(['supplier_id', 'payment_status']);
                $table->index('purchase_date');
            });
        } else {
            Schema::table('purchases', function (Blueprint $table) {
                if (!Schema::hasColumn('purchases', 'purchase_number')) {
                    $table->string('purchase_number')->unique()->nullable();
                }
                if (!Schema::hasColumn('purchases', 'purchase_order_id')) {
                    $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
                }
                if (!Schema::hasColumn('purchases', 'branch_id')) {
                    $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                }
                if (!Schema::hasColumn('purchases', 'warehouse_id')) {
                    $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                }
                if (!Schema::hasColumn('purchases', 'supplier_invoice_no')) {
                    $table->string('supplier_invoice_no')->nullable();
                }
                if (!Schema::hasColumn('purchases', 'supplier_challan_no')) {
                    $table->string('supplier_challan_no')->nullable();
                }
                if (!Schema::hasColumn('purchases', 'receiving_date')) {
                    $table->date('receiving_date')->nullable();
                }
                if (!Schema::hasColumn('purchases', 'purchase_type')) {
                    $table->enum('purchase_type', [
                        'sellable_inventory',
                        'fixed_asset',
                        'raw_material',
                        'consumable',
                        'non_stock'
                    ])->default('sellable_inventory');
                }
                if (!Schema::hasColumn('purchases', 'shipping_cost')) {
                    $table->decimal('shipping_cost', 14, 2)->default(0.00);
                }
                if (!Schema::hasColumn('purchases', 'other_charges')) {
                    $table->decimal('other_charges', 14, 2)->default(0.00);
                }
                if (!Schema::hasColumn('purchases', 'paid_amount')) {
                    $table->decimal('paid_amount', 14, 2)->default(0.00);
                }
                if (!Schema::hasColumn('purchases', 'due_amount')) {
                    $table->decimal('due_amount', 14, 2)->default(0.00);
                }
                if (!Schema::hasColumn('purchases', 'purchase_status')) {
                    $table->enum('purchase_status', [
                        'draft',
                        'pending_approval',
                        'ordered',
                        'partially_received',
                        'received',
                        'cancelled',
                        'returned'
                    ])->default('draft');
                }
                if (!Schema::hasColumn('purchases', 'payment_status')) {
                    $table->enum('payment_status', [
                        'unpaid',
                        'partially_paid',
                        'paid',
                        'overdue',
                        'refunded',
                        'adjusted'
                    ])->default('unpaid');
                }
            });
        }

        // 4. Create or Enhance Purchase Items Table
        if (!Schema::hasTable('purchase_items')) {
            Schema::create('purchase_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
                $table->foreignId('purchase_order_item_id')->nullable()->constrained('purchase_order_items')->nullOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->string('sku', 100)->nullable();
                $table->string('barcode', 100)->nullable();
                $table->string('batch_no', 100)->nullable();
                $table->date('expiry_date')->nullable();
                $table->decimal('quantity', 12, 2);
                $table->decimal('unit_cost', 14, 2);
                $table->decimal('unit_cost_snapshot', 14, 2)->nullable();
                $table->decimal('discount_amount', 14, 2)->default(0.00);
                $table->decimal('tax_amount', 14, 2)->default(0.00);
                $table->decimal('subtotal', 14, 2);
                $table->decimal('total_cost', 14, 2);
                $table->decimal('landed_cost_share', 14, 2)->default(0.00);
                $table->timestamps();

                $table->index(['purchase_id', 'product_id']);
                $table->index('variant_id');
                $table->index('sku');
            });
        } else {
            Schema::table('purchase_items', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_items', 'purchase_order_item_id')) {
                    $table->foreignId('purchase_order_item_id')->nullable()->constrained('purchase_order_items')->nullOnDelete();
                }
                if (!Schema::hasColumn('purchase_items', 'variant_id')) {
                    $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                }
                if (!Schema::hasColumn('purchase_items', 'sku')) {
                    $table->string('sku', 100)->nullable();
                }
                if (!Schema::hasColumn('purchase_items', 'barcode')) {
                    $table->string('barcode', 100)->nullable();
                }
                if (!Schema::hasColumn('purchase_items', 'batch_no')) {
                    $table->string('batch_no', 100)->nullable();
                }
                if (!Schema::hasColumn('purchase_items', 'expiry_date')) {
                    $table->date('expiry_date')->nullable();
                }
                if (!Schema::hasColumn('purchase_items', 'unit_cost_snapshot')) {
                    $table->decimal('unit_cost_snapshot', 14, 2)->nullable();
                }
                if (!Schema::hasColumn('purchase_items', 'landed_cost_share')) {
                    $table->decimal('landed_cost_share', 14, 2)->default(0.00);
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