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
        // 1. Supplier Payments Table
        if (!Schema::hasTable('supplier_payments')) {
            Schema::create('supplier_payments', function (Blueprint $table) {
                $table->id();
                $table->string('payment_number', 100)->index();
                $table->string('voucher_number', 100)->nullable()->index();
                $table->unsignedBigInteger('supplier_id')->index();
                $table->date('payment_date');

                // VARCHAR use kora holo jate ENUM truncation error kokhono na ashe
                $table->string('payment_type', 100)->default('invoice_payment');
                
                // bank_account_id nullable (bank_accounts table database-e na thakay FK constraint bad dewa holo)
                $table->unsignedBigInteger('bank_account_id')->nullable()->index();
                $table->unsignedBigInteger('branch_id')->nullable()->index();

                $table->string('payment_method', 100)->default('cash');
                $table->decimal('amount', 14, 2)->default(0.00);
                $table->decimal('allocated_amount', 14, 2)->default(0.00);
                $table->decimal('unallocated_amount', 14, 2)->default(0.00);

                $table->string('transaction_reference', 255)->nullable();
                $table->string('reference_no', 100)->nullable();
                $table->string('cheque_number', 255)->nullable();
                $table->string('cheque_no', 100)->nullable();
                $table->date('cheque_date')->nullable();
                $table->string('attachment_file', 255)->nullable();

                // VARCHAR status jate 'posted', 'completed', 'approved' shob safely boste pare
                $table->string('status', 50)->default('posted');

                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->text('notes')->nullable();
                $table->string('cancelled_reason', 255)->nullable();
                $table->timestamps();

                // Foreign key constraint with suppliers table
                if (Schema::hasTable('suppliers')) {
                    $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
                }
            });
        }

        // 2. Supplier Payment Allocations Table
        if (!Schema::hasTable('supplier_payment_allocations')) {
            Schema::create('supplier_payment_allocations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('supplier_payment_id')->index();
                $table->unsignedBigInteger('purchase_id')->index();
                $table->decimal('allocated_amount', 14, 2)->default(0.00);
                $table->decimal('amount', 14, 2)->default(0.00);
                $table->timestamps();

                $table->foreign('supplier_payment_id')->references('id')->on('supplier_payments')->onDelete('cascade');
                if (Schema::hasTable('purchases')) {
                    $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_payment_allocations');
        Schema::dropIfExists('supplier_payments');
    }
};