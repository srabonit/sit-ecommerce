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
        if (!Schema::hasTable('tax_periods')) {
            Schema::create('tax_periods', function (Blueprint $table) {
                $table->id();
                $table->string('period_key', 20)->unique()->comment('2026-09');
                $table->string('period_name', 50)->comment('September 2026');
                $table->date('start_date');
                $table->date('end_date');
                $table->decimal('taxable_amount', 15, 2)->default(0.00);
                $table->decimal('tax_collected', 15, 2)->default(0.00);
                $table->decimal('tax_refunded', 15, 2)->default(0.00);
                $table->decimal('net_tax_liability', 15, 2)->default(0.00);
                $table->decimal('treasury_paid', 15, 2)->default(0.00);
                $table->boolean('is_locked')->default(false)->index();
                $table->timestamp('locked_at')->nullable();
                $table->unsignedBigInteger('locked_by')->nullable();
                $table->longText('closing_snapshot')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();

                $table->index(['start_date', 'end_date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_periods');
    }
};