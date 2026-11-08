<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for procurement sub-tables and allocations.
     */
    public function up(): void
    {
        // 1. Supplier Product Exchanges Master
        if (!Schema::hasTable('supplier_exchanges')) {
            Schema::create('supplier_exchanges', function (Blueprint $table) {
                $table->id();
                $table->string('exchange_number', 50)->unique();
                $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->date('exchange_date');
                $table->string('reference_no', 100)->nullable();
                $table->string('settlement_type', 50)->default('ledger_adjustment');
                $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
                $table->decimal('outward_total', 15, 2)->default(0.00);
                $table->decimal('inward_total', 15, 2)->default(0.00);
                $table->decimal('differential_amount', 15, 2)->default(0.00);
                $table->enum('status', ['pending', 'approved', 'cancelled'])->default('pending');
                $table->string('attachment_file')->nullable();
                $table->text('notes')->nullable();
                $table->text('cancelled_reason')->nullable();
                $table->timestamps();
            });
        }

        // 2. Supplier Product Exchange Line Items
        if (!Schema::hasTable('supplier_exchange_items')) {
            Schema::create('supplier_exchange_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_exchange_id')->constrained('supplier_exchanges')->cascadeOnDelete();
                $table->enum('item_type', ['outward', 'inward']);
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->unsignedBigInteger('variant_id')->nullable();
                $table->string('sku', 100)->nullable();
                $table->string('batch_no', 100)->nullable();
                $table->date('expiry_date')->nullable();
                $table->decimal('quantity', 12, 3)->default(1.000);
                $table->decimal('unit_cost', 15, 2)->default(0.00);
                $table->decimal('total_cost', 15, 2)->default(0.00);
                $table->string('reason', 100)->nullable();
                $table->timestamps();
            });
        }

        // 3. Supplier Debit & Credit Notes
        if (!Schema::hasTable('supplier_debit_credit_notes')) {
            Schema::create('supplier_debit_credit_notes', function (Blueprint $table) {
                $table->id();
                $table->enum('note_type', ['debit_note', 'credit_note']);
                $table->string('note_number', 50)->unique();
                $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
                $table->unsignedBigInteger('purchase_id')->nullable();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->date('note_date');
                $table->string('reason_category', 100);
                $table->string('reason', 255);
                $table->string('reference_no', 100)->nullable();
                $table->decimal('amount', 15, 2)->default(0.00);
                $table->decimal('tax_amount', 15, 2)->default(0.00);
                $table->decimal('total_amount', 15, 2)->default(0.00);
                $table->unsignedBigInteger('chart_of_account_id')->nullable();
                $table->enum('status', ['draft', 'posted', 'cancelled'])->default('posted');
                $table->string('attachment_file')->nullable();
                $table->text('notes')->nullable();
                $table->text('cancelled_reason')->nullable();
                $table->timestamps();
            });
        }

        // 4. Supplier Payment Bill Allocations
        if (!Schema::hasTable('supplier_payment_allocations')) {
            Schema::create('supplier_payment_allocations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_payment_id')->constrained('supplier_payments')->cascadeOnDelete();
                $table->unsignedBigInteger('purchase_id');
                $table->decimal('allocated_amount', 15, 2)->default(0.00);
                $table->timestamps();

                $table->index(['supplier_payment_id', 'purchase_id'], 'idx_payment_bill_alloc');
            });
        }

        // 5. Purchase Return Line Items
        if (!Schema::hasTable('purchase_return_items')) {
            Schema::create('purchase_return_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_return_id')->constrained('purchase_returns')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->unsignedBigInteger('variant_id')->nullable();
                $table->unsignedBigInteger('purchase_item_id')->nullable();
                $table->string('sku', 100)->nullable();
                $table->string('batch_no', 100)->nullable();
                $table->decimal('quantity', 12, 3)->default(1.000);
                $table->decimal('unit_cost', 15, 2)->default(0.00);
                $table->decimal('tax', 15, 2)->default(0.00);
                $table->decimal('total', 15, 2)->default(0.00);
                $table->string('reason', 100)->default('defective');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('supplier_payment_allocations');
        Schema::dropIfExists('supplier_debit_credit_notes');
        Schema::dropIfExists('supplier_exchange_items');
        Schema::dropIfExists('supplier_exchanges');
    }
};