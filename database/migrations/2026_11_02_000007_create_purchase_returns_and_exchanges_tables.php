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
        // 1. Purchase Returns Table
        if (!Schema::hasTable('purchase_returns')) {
            Schema::create('purchase_returns', function (Blueprint $table) {
                $table->id();
                $table->string('return_number')->unique();
                $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
                $table->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->date('return_date');
                $table->enum('return_reason', [
                    'defective',
                    'wrong_product',
                    'damaged',
                    'expired',
                    'quality_issue',
                    'excess_supply',
                    'other'
                ])->default('defective');
                $table->enum('status', [
                    'draft',
                    'pending_approval',
                    'approved',
                    'sent',
                    'partially_settled',
                    'settled',
                    'rejected',
                    'cancelled'
                ])->default('draft');
                $table->enum('settlement_type', [
                    'cash_refund',
                    'bank_refund',
                    'mfs_refund',
                    'credit_balance',
                    'future_adjustment',
                    'replacement'
                ])->default('credit_balance');
                $table->decimal('subtotal', 14, 2)->default(0.00);
                $table->decimal('tax_amount', 14, 2)->default(0.00);
                $table->decimal('total_return_amount', 14, 2)->default(0.00);
                $table->decimal('refunded_amount', 14, 2)->default(0.00);
                $table->decimal('adjusted_amount', 14, 2)->default(0.00);
                $table->string('supplier_reference_no')->nullable();
                $table->string('attachment_file')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['supplier_id', 'status'], 'pr_supplier_status_idx');
                $table->index('return_date', 'pr_date_idx');
            });
        }

        // 2. Purchase Return Items Table
        if (!Schema::hasTable('purchase_return_items')) {
            Schema::create('purchase_return_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_return_id')->constrained('purchase_returns')->cascadeOnDelete();
                $table->foreignId('purchase_item_id')->nullable()->constrained('purchase_items')->nullOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->string('sku', 100)->nullable();
                $table->string('barcode', 100)->nullable();
                $table->string('batch_no', 100)->nullable();
                $table->decimal('return_quantity', 12, 2);
                $table->decimal('unit_cost', 14, 2);
                $table->decimal('tax_amount', 14, 2)->default(0.00);
                $table->decimal('total_cost', 14, 2);
                $table->string('item_reason')->nullable();
                $table->timestamps();

                $table->index(['purchase_return_id', 'product_id'], 'pri_return_product_idx');
                $table->index('variant_id', 'pri_variant_idx');
            });
        }

        // 3. Supplier Exchanges / Replacements Table
        if (!Schema::hasTable('supplier_exchanges')) {
            Schema::create('supplier_exchanges', function (Blueprint $table) {
                $table->id();
                $table->string('exchange_number')->unique();
                $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
                $table->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
                $table->foreignId('purchase_return_id')->nullable()->constrained('purchase_returns')->nullOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->date('exchange_date');
                $table->enum('status', [
                    'requested',
                    'approved',
                    'sent_to_supplier',
                    'partially_received',
                    'completed',
                    'rejected',
                    'cancelled'
                ])->default('requested');
                $table->decimal('returned_total_value', 14, 2)->default(0.00);
                $table->decimal('replacement_total_value', 14, 2)->default(0.00);
                $table->decimal('price_difference', 14, 2)->default(0.00); // (+) Payable to supplier, (-) Receivable from supplier
                $table->enum('difference_settlement', [
                    'supplier_payment',
                    'supplier_refund',
                    'credit_balance',
                    'even_exchange'
                ])->default('even_exchange');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['supplier_id', 'status'], 'se_supplier_status_idx');
                $table->index('exchange_date', 'se_date_idx');
            });
        }

        // 4. Supplier Exchange Items Table (Both Outward Return & Inward Replacement)
        if (!Schema::hasTable('supplier_exchange_items')) {
            Schema::create('supplier_exchange_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_exchange_id')->constrained('supplier_exchanges')->cascadeOnDelete();
                $table->enum('item_type', ['returned_out', 'replacement_in']);
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->string('sku', 100)->nullable();
                $table->string('barcode', 100)->nullable();
                $table->string('batch_no', 100)->nullable();
                $table->decimal('quantity', 12, 2);
                $table->decimal('unit_cost', 14, 2);
                $table->decimal('total_cost', 14, 2);
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index(['supplier_exchange_id', 'item_type'], 'sei_exchange_type_idx');
                $table->index(['product_id', 'variant_id'], 'sei_prod_var_idx');
            });
        }

        // 5. Supplier Debit & Credit Notes Table
        if (!Schema::hasTable('supplier_debit_credit_notes')) {
            Schema::create('supplier_debit_credit_notes', function (Blueprint $table) {
                $table->id();
                $table->string('note_number')->unique();
                $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
                $table->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
                $table->foreignId('purchase_return_id')->nullable()->constrained('purchase_returns')->nullOnDelete();
                $table->foreignId('supplier_exchange_id')->nullable()->constrained('supplier_exchanges')->nullOnDelete();
                $table->enum('note_type', ['debit_note', 'credit_note']);
                $table->enum('reason_type', [
                    'purchase_return',
                    'damaged_goods',
                    'short_supply',
                    'overcharge',
                    'supplier_discount',
                    'purchase_rebate',
                    'compensation',
                    'other'
                ]);
                $table->date('note_date');
                $table->decimal('amount', 14, 2);
                $table->decimal('tax_amount', 14, 2)->default(0.00);
                $table->decimal('total_amount', 14, 2);
                $table->enum('status', ['draft', 'approved', 'applied', 'void'])->default('approved');
                $table->string('attachment_file')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('description')->nullable();
                $table->timestamps();

                $table->index(['supplier_id', 'note_type'], 'sdcn_supplier_type_idx');
                $table->index('note_date', 'sdcn_date_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_debit_credit_notes');
        Schema::dropIfExists('supplier_exchange_items');
        Schema::dropIfExists('supplier_exchanges');
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('purchase_returns');
    }
};