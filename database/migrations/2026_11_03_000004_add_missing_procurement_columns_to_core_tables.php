<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to ensure all core procurement tables have required columns.
     */
    public function up(): void
    {
        // 1. Asset Categories Table (if missing)
        if (!Schema::hasTable('asset_categories')) {
            Schema::create('asset_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->string('code', 50)->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // 2. Ensure purchase_orders columns
        if (Schema::hasTable('purchase_orders')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_orders', 'po_number')) $table->string('po_number', 50)->nullable()->after('id');
                if (!Schema::hasColumn('purchase_orders', 'warehouse_id')) $table->unsignedBigInteger('warehouse_id')->nullable()->after('supplier_id');
                if (!Schema::hasColumn('purchase_orders', 'branch_id')) $table->unsignedBigInteger('branch_id')->nullable()->after('warehouse_id');
                if (!Schema::hasColumn('purchase_orders', 'order_date')) $table->date('order_date')->nullable()->after('branch_id');
                if (!Schema::hasColumn('purchase_orders', 'delivery_date')) $table->date('delivery_date')->nullable()->after('order_date');
                if (!Schema::hasColumn('purchase_orders', 'reference_no')) $table->string('reference_no', 100)->nullable()->after('delivery_date');
                if (!Schema::hasColumn('purchase_orders', 'subtotal')) $table->decimal('subtotal', 15, 2)->default(0.00)->after('reference_no');
                if (!Schema::hasColumn('purchase_orders', 'tax_amount')) $table->decimal('tax_amount', 15, 2)->default(0.00)->after('subtotal');
                if (!Schema::hasColumn('purchase_orders', 'discount_amount')) $table->decimal('discount_amount', 15, 2)->default(0.00)->after('tax_amount');
                if (!Schema::hasColumn('purchase_orders', 'shipping_cost')) $table->decimal('shipping_cost', 15, 2)->default(0.00)->after('discount_amount');
                if (!Schema::hasColumn('purchase_orders', 'total_amount')) $table->decimal('total_amount', 15, 2)->default(0.00)->after('shipping_cost');
                if (!Schema::hasColumn('purchase_orders', 'status')) $table->string('status', 50)->default('pending')->after('total_amount');
                if (!Schema::hasColumn('purchase_orders', 'notes')) $table->text('notes')->nullable()->after('status');
                if (!Schema::hasColumn('purchase_orders', 'created_by')) $table->unsignedBigInteger('created_by')->nullable()->after('notes');
            });
        }

        // 3. Ensure purchases (Bills) columns
        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $table) {
                if (!Schema::hasColumn('purchases', 'purchase_number')) $table->string('purchase_number', 50)->nullable()->after('id');
                if (!Schema::hasColumn('purchases', 'purchase_order_id')) $table->unsignedBigInteger('purchase_order_id')->nullable()->after('purchase_number');
                if (!Schema::hasColumn('purchases', 'warehouse_id')) $table->unsignedBigInteger('warehouse_id')->nullable()->after('supplier_id');
                if (!Schema::hasColumn('purchases', 'branch_id')) $table->unsignedBigInteger('branch_id')->nullable()->after('warehouse_id');
                if (!Schema::hasColumn('purchases', 'purchase_date')) $table->date('purchase_date')->nullable()->after('branch_id');
                if (!Schema::hasColumn('purchases', 'due_date')) $table->date('due_date')->nullable()->after('purchase_date');
                if (!Schema::hasColumn('purchases', 'supplier_invoice_no')) $table->string('supplier_invoice_no', 100)->nullable()->after('due_date');
                if (!Schema::hasColumn('purchases', 'subtotal')) $table->decimal('subtotal', 15, 2)->default(0.00)->after('supplier_invoice_no');
                if (!Schema::hasColumn('purchases', 'tax_amount')) $table->decimal('tax_amount', 15, 2)->default(0.00)->after('subtotal');
                if (!Schema::hasColumn('purchases', 'discount_amount')) $table->decimal('discount_amount', 15, 2)->default(0.00)->after('tax_amount');
                if (!Schema::hasColumn('purchases', 'shipping_cost')) $table->decimal('shipping_cost', 15, 2)->default(0.00)->after('discount_amount');
                if (!Schema::hasColumn('purchases', 'grand_total')) $table->decimal('grand_total', 15, 2)->default(0.00)->after('shipping_cost');
                if (!Schema::hasColumn('purchases', 'paid_amount')) $table->decimal('paid_amount', 15, 2)->default(0.00)->after('grand_total');
                if (!Schema::hasColumn('purchases', 'due_amount')) $table->decimal('due_amount', 15, 2)->default(0.00)->after('paid_amount');
                if (!Schema::hasColumn('purchases', 'payment_status')) $table->string('payment_status', 50)->default('unpaid')->after('due_amount');
                if (!Schema::hasColumn('purchases', 'payment_method')) $table->string('payment_method', 50)->nullable()->after('payment_status');
                if (!Schema::hasColumn('purchases', 'bank_account_id')) $table->unsignedBigInteger('bank_account_id')->nullable()->after('payment_method');
                if (!Schema::hasColumn('purchases', 'status')) $table->string('status', 50)->default('received')->after('bank_account_id');
                if (!Schema::hasColumn('purchases', 'attachment_file')) $table->string('attachment_file')->nullable()->after('status');
                if (!Schema::hasColumn('purchases', 'notes')) $table->text('notes')->nullable()->after('attachment_file');
            });
        }

        // 4. Ensure purchase_receipts (GRN) columns
        if (Schema::hasTable('purchase_receipts')) {
            Schema::table('purchase_receipts', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_receipts', 'receipt_number')) $table->string('receipt_number', 50)->nullable()->after('id');
                if (!Schema::hasColumn('purchase_receipts', 'purchase_order_id')) $table->unsignedBigInteger('purchase_order_id')->nullable()->after('receipt_number');
                if (!Schema::hasColumn('purchase_receipts', 'warehouse_id')) $table->unsignedBigInteger('warehouse_id')->nullable()->after('supplier_id');
                if (!Schema::hasColumn('purchase_receipts', 'branch_id')) $table->unsignedBigInteger('branch_id')->nullable()->after('warehouse_id');
                if (!Schema::hasColumn('purchase_receipts', 'receipt_date')) $table->date('receipt_date')->nullable()->after('branch_id');
                if (!Schema::hasColumn('purchase_receipts', 'challan_no')) $table->string('challan_no', 100)->nullable()->after('receipt_date');
                if (!Schema::hasColumn('purchase_receipts', 'status')) $table->string('status', 50)->default('pending')->after('challan_no');
                if (!Schema::hasColumn('purchase_receipts', 'total_items')) $table->integer('total_items')->default(0)->after('status');
                if (!Schema::hasColumn('purchase_receipts', 'total_qty')) $table->decimal('total_qty', 12, 3)->default(0.00)->after('total_items');
                if (!Schema::hasColumn('purchase_receipts', 'attachment_file')) $table->string('attachment_file')->nullable()->after('total_qty');
                if (!Schema::hasColumn('purchase_receipts', 'notes')) $table->text('notes')->nullable()->after('attachment_file');
            });
        }

        // 5. Ensure purchase_returns columns
        if (Schema::hasTable('purchase_returns')) {
            Schema::table('purchase_returns', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_returns', 'return_number')) $table->string('return_number', 50)->nullable()->after('id');
                if (!Schema::hasColumn('purchase_returns', 'warehouse_id')) $table->unsignedBigInteger('warehouse_id')->nullable()->after('supplier_id');
                if (!Schema::hasColumn('purchase_returns', 'branch_id')) $table->unsignedBigInteger('branch_id')->nullable()->after('warehouse_id');
                if (!Schema::hasColumn('purchase_returns', 'purchase_id')) $table->unsignedBigInteger('purchase_id')->nullable()->after('branch_id');
                if (!Schema::hasColumn('purchase_returns', 'purchase_receipt_id')) $table->unsignedBigInteger('purchase_receipt_id')->nullable()->after('purchase_id');
                if (!Schema::hasColumn('purchase_returns', 'return_date')) $table->date('return_date')->nullable()->after('purchase_receipt_id');
                if (!Schema::hasColumn('purchase_returns', 'settlement_type')) $table->string('settlement_type', 50)->default('credit_note')->after('return_date');
                if (!Schema::hasColumn('purchase_returns', 'bank_account_id')) $table->unsignedBigInteger('bank_account_id')->nullable()->after('settlement_type');
                if (!Schema::hasColumn('purchase_returns', 'reference_no')) $table->string('reference_no', 100)->nullable()->after('bank_account_id');
                if (!Schema::hasColumn('purchase_returns', 'subtotal')) $table->decimal('subtotal', 15, 2)->default(0.00)->after('reference_no');
                if (!Schema::hasColumn('purchase_returns', 'tax_amount')) $table->decimal('tax_amount', 15, 2)->default(0.00)->after('subtotal');
                if (!Schema::hasColumn('purchase_returns', 'shipping_cost')) $table->decimal('shipping_cost', 15, 2)->default(0.00)->after('tax_amount');
                if (!Schema::hasColumn('purchase_returns', 'grand_total')) $table->decimal('grand_total', 15, 2)->default(0.00)->after('shipping_cost');
                if (!Schema::hasColumn('purchase_returns', 'status')) $table->string('status', 50)->default('pending')->after('grand_total');
                if (!Schema::hasColumn('purchase_returns', 'attachment_file')) $table->string('attachment_file')->nullable()->after('status');
                if (!Schema::hasColumn('purchase_returns', 'notes')) $table->text('notes')->nullable()->after('attachment_file');
                if (!Schema::hasColumn('purchase_returns', 'cancelled_reason')) $table->text('cancelled_reason')->nullable()->after('notes');
            });
        }

        // 6. Ensure supplier_payments columns
        if (Schema::hasTable('supplier_payments')) {
            Schema::table('supplier_payments', function (Blueprint $table) {
                if (!Schema::hasColumn('supplier_payments', 'voucher_number')) $table->string('voucher_number', 50)->nullable()->after('id');
                if (!Schema::hasColumn('supplier_payments', 'branch_id')) $table->unsignedBigInteger('branch_id')->nullable()->after('supplier_id');
                if (!Schema::hasColumn('supplier_payments', 'bank_account_id')) $table->unsignedBigInteger('bank_account_id')->nullable()->after('branch_id');
                if (!Schema::hasColumn('supplier_payments', 'payment_date')) $table->date('payment_date')->nullable()->after('bank_account_id');
                if (!Schema::hasColumn('supplier_payments', 'payment_method')) $table->string('payment_method', 50)->default('bank_transfer')->after('payment_date');
                if (!Schema::hasColumn('supplier_payments', 'payment_type')) $table->string('payment_type', 50)->default('bill_settlement')->after('payment_method');
                if (!Schema::hasColumn('supplier_payments', 'amount')) $table->decimal('amount', 15, 2)->default(0.00)->after('payment_type');
                if (!Schema::hasColumn('supplier_payments', 'allocated_amount')) $table->decimal('allocated_amount', 15, 2)->default(0.00)->after('amount');
                if (!Schema::hasColumn('supplier_payments', 'reference_no')) $table->string('reference_no', 100)->nullable()->after('allocated_amount');
                if (!Schema::hasColumn('supplier_payments', 'cheque_no')) $table->string('cheque_no', 100)->nullable()->after('reference_no');
                if (!Schema::hasColumn('supplier_payments', 'status')) $table->string('status', 50)->default('completed')->after('cheque_no');
                if (!Schema::hasColumn('supplier_payments', 'attachment_file')) $table->string('attachment_file')->nullable()->after('status');
                if (!Schema::hasColumn('supplier_payments', 'notes')) $table->text('notes')->nullable()->after('attachment_file');
                if (!Schema::hasColumn('supplier_payments', 'cancelled_reason')) $table->text('cancelled_reason')->nullable()->after('notes');
            });
        }

        // 7. Ensure assets (Fixed Asset Procurement) columns
        if (Schema::hasTable('assets')) {
            Schema::table('assets', function (Blueprint $table) {
                if (!Schema::hasColumn('assets', 'asset_code')) $table->string('asset_code', 50)->nullable()->after('id');
                if (!Schema::hasColumn('assets', 'asset_category_id')) $table->unsignedBigInteger('asset_category_id')->nullable()->after('name');
                if (!Schema::hasColumn('assets', 'brand')) $table->string('brand', 100)->nullable()->after('asset_category_id');
                if (!Schema::hasColumn('assets', 'model_no')) $table->string('model_no', 100)->nullable()->after('brand');
                if (!Schema::hasColumn('assets', 'serial_number')) $table->string('serial_number', 100)->nullable()->after('model_no');
                if (!Schema::hasColumn('assets', 'supplier_id')) $table->unsignedBigInteger('supplier_id')->nullable()->after('serial_number');
                if (!Schema::hasColumn('assets', 'purchase_id')) $table->unsignedBigInteger('purchase_id')->nullable()->after('supplier_id');
                if (!Schema::hasColumn('assets', 'purchase_date')) $table->date('purchase_date')->nullable()->after('purchase_id');
                if (!Schema::hasColumn('assets', 'in_service_date')) $table->date('in_service_date')->nullable()->after('purchase_date');
                if (!Schema::hasColumn('assets', 'branch_id')) $table->unsignedBigInteger('branch_id')->nullable()->after('in_service_date');
                if (!Schema::hasColumn('assets', 'department_id')) $table->unsignedBigInteger('department_id')->nullable()->after('branch_id');
                if (!Schema::hasColumn('assets', 'custodian_id')) $table->unsignedBigInteger('custodian_id')->nullable()->after('department_id');
                if (!Schema::hasColumn('assets', 'location')) $table->string('location', 255)->nullable()->after('custodian_id');
                if (!Schema::hasColumn('assets', 'purchase_cost')) $table->decimal('purchase_cost', 15, 2)->default(0.00)->after('location');
                if (!Schema::hasColumn('assets', 'installation_cost')) $table->decimal('installation_cost', 15, 2)->default(0.00)->after('purchase_cost');
                if (!Schema::hasColumn('assets', 'shipping_cost')) $table->decimal('shipping_cost', 15, 2)->default(0.00)->after('installation_cost');
                if (!Schema::hasColumn('assets', 'salvage_value')) $table->decimal('salvage_value', 15, 2)->default(0.00)->after('shipping_cost');
                if (!Schema::hasColumn('assets', 'capitalized_cost')) $table->decimal('capitalized_cost', 15, 2)->default(0.00)->after('salvage_value');
                if (!Schema::hasColumn('assets', 'depreciation_method')) $table->string('depreciation_method', 50)->default('straight_line')->after('capitalized_cost');
                if (!Schema::hasColumn('assets', 'useful_life_years')) $table->decimal('useful_life_years', 5, 2)->nullable()->after('depreciation_method');
                if (!Schema::hasColumn('assets', 'depreciation_rate')) $table->decimal('depreciation_rate', 5, 2)->nullable()->after('useful_life_years');
                if (!Schema::hasColumn('assets', 'asset_account_id')) $table->unsignedBigInteger('asset_account_id')->nullable()->after('depreciation_rate');
                if (!Schema::hasColumn('assets', 'accumulated_depreciation_account_id')) $table->unsignedBigInteger('accumulated_depreciation_account_id')->nullable()->after('asset_account_id');
                if (!Schema::hasColumn('assets', 'depreciation_expense_account_id')) $table->unsignedBigInteger('depreciation_expense_account_id')->nullable()->after('accumulated_depreciation_account_id');
                if (!Schema::hasColumn('assets', 'accumulated_depreciation')) $table->decimal('accumulated_depreciation', 15, 2)->default(0.00)->after('depreciation_expense_account_id');
                if (!Schema::hasColumn('assets', 'status')) $table->string('status', 50)->default('active')->after('accumulated_depreciation');
                if (!Schema::hasColumn('assets', 'disposal_type')) $table->string('disposal_type', 50)->nullable()->after('status');
                if (!Schema::hasColumn('assets', 'disposal_value')) $table->decimal('disposal_value', 15, 2)->nullable()->after('disposal_type');
                if (!Schema::hasColumn('assets', 'disposal_date')) $table->date('disposal_date')->nullable()->after('disposal_value');
                if (!Schema::hasColumn('assets', 'warranty_expiry_date')) $table->date('warranty_expiry_date')->nullable()->after('disposal_date');
                if (!Schema::hasColumn('assets', 'insurance_policy_no')) $table->string('insurance_policy_no', 100)->nullable()->after('warranty_expiry_date');
                if (!Schema::hasColumn('assets', 'attachment_file')) $table->string('attachment_file')->nullable()->after('insurance_policy_no');
                if (!Schema::hasColumn('assets', 'notes')) $table->text('notes')->nullable()->after('attachment_file');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe reversible no-op to prevent dropping existing production data
    }
};