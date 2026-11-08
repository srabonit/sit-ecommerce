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
        if (!Schema::hasTable('branch_daily_closings')) {
            Schema::create('branch_daily_closings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id');
                $table->unsignedBigInteger('user_id'); // Cashier / Closer
                $table->date('closing_date');

                // Cash Reconciliation Columns
                $table->decimal('opening_cash', 14, 2)->default(0.00);
                $table->decimal('pos_cash_sales', 14, 2)->default(0.00);
                $table->decimal('cash_due_collection', 14, 2)->default(0.00);
                $table->decimal('cash_in', 14, 2)->default(0.00);
                $table->decimal('cash_out', 14, 2)->default(0.00);
                $table->decimal('cash_expenses', 14, 2)->default(0.00);
                $table->decimal('cash_refunds', 14, 2)->default(0.00);

                // Balancing Calculations
                $table->decimal('expected_cash', 14, 2)->default(0.00);
                $table->decimal('actual_cash', 14, 2)->default(0.00);
                $table->decimal('difference', 14, 2)->default(0.00); // actual - expected

                // Shift Sales Metrics
                $table->integer('total_orders_count')->default(0);
                $table->decimal('total_sales_amount', 14, 2)->default(0.00); // All channels/methods
                $table->decimal('digital_sales_amount', 14, 2)->default(0.00); // bKash, card, bank, nagad

                // Session Lifecycle
                $table->string('status', 30)->default('closed'); // open, closed, verified
                $table->timestamp('opened_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->unsignedBigInteger('verified_by')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                // High performance indexing
                $table->index('branch_id');
                $table->index('user_id');
                $table->index('closing_date');
                $table->index('status');
                $table->index(['branch_id', 'closing_date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_daily_closings');
    }
};