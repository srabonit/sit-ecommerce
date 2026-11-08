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
        if (!Schema::hasTable('supplier_ledger_entries')) {
            Schema::create('supplier_ledger_entries', function (Blueprint $table) {
                $table->id();
                $table->string('entry_number')->unique();
                $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
                $table->date('transaction_date');
                $table->enum('transaction_type', [
                    'opening_balance',
                    'purchase',
                    'payment',
                    'advance',
                    'purchase_return',
                    'supplier_refund',
                    'debit_note',
                    'credit_note',
                    'adjustment'
                ]);
                $table->string('reference_type')->nullable(); // Model class or type name
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->string('reference_no')->nullable(); // Bill No, Check No, Voucher No, Return No
                $table->decimal('debit', 14, 2)->default(0.00); // Payment Outflow, Return to supplier, Advance
                $table->decimal('credit', 14, 2)->default(0.00); // Purchase Bill Inflow, Payable
                $table->decimal('balance', 14, 2)->default(0.00); // Running outstanding balance
                $table->text('description')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['supplier_id', 'transaction_date'], 'sle_supplier_date_idx');
                $table->index(['reference_type', 'reference_id'], 'sle_reference_idx');
                $table->index('transaction_type', 'sle_type_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_ledger_entries');
    }
};