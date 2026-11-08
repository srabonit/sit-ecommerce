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
        // 1. Purchase Returns (Return to Vendor - RTV)
        if (!Schema::hasTable('purchase_returns')) {
            Schema::create('purchase_returns', function (Blueprint $table) {
                $table->id();
                $table->string('return_number', 100)->unique();
                $table->unsignedBigInteger('purchase_id')->nullable()->index();
                $table->unsignedBigInteger('purchase_receipt_id')->nullable()->index();
                $table->unsignedBigInteger('supplier_id')->index();
                $table->unsignedBigInteger('warehouse_id')->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->date('return_date');
                $table->string('settlement_type', 50)->default('credit_note');
                $table->unsignedBigInteger('bank_account_id')->nullable();
                $table->string('reference_no', 100)->nullable();
                $table->decimal('subtotal', 14, 2)->default(0.00);
                $table->decimal('tax_amount', 14, 2)->default(0.00);
                $table->decimal('shipping_cost', 14, 2)->default(0.00);
                $table->decimal('grand_total', 14, 2)->default(0.00);
                $table->string('status', 50)->default('approved')->index();
                $table->string('attachment_file', 255)->nullable();
                $table->text('notes')->nullable();
                $table->string('cancelled_reason', 500)->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamps();

                if (Schema::hasTable('suppliers')) {
                    $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
                }
                if (Schema::hasTable('purchases')) {
                    $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('set null');
                }
            });
        }

        // 2. Purchase Return Items
        if (!Schema::hasTable('purchase_return_items')) {
            Schema::create('purchase_return_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_return_id')->index();
                $table->unsignedBigInteger('purchase_item_id')->nullable();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('variant_id')->nullable();
                $table->string('sku', 100)->nullable();
                $table->string('batch_no', 100)->nullable();
                $table->decimal('quantity', 12, 2)->default(1.00);
                $table->decimal('unit_cost', 14, 2)->default(0.00);
                $table->decimal('tax', 14, 2)->default(0.00);
                $table->decimal('total', 14, 2)->default(0.00);
                $table->string('reason', 255)->default('defective');
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->foreign('purchase_return_id')->references('id')->on('purchase_returns')->onDelete('cascade');
                if (Schema::hasTable('products')) {
                    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('purchase_returns');
    }
};