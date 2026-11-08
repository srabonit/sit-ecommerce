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
        if (Schema::hasTable('product_stocks')) {
            Schema::table('product_stocks', function (Blueprint $table) {
                if (!Schema::hasColumn('product_stocks', 'variant_id')) {
                    $table->unsignedBigInteger('variant_id')->nullable()->after('product_id');
                }
                if (!Schema::hasColumn('product_stocks', 'sku')) {
                    $table->string('sku', 100)->nullable()->after('variant_id');
                }
                if (!Schema::hasColumn('product_stocks', 'reserved_quantity')) {
                    $table->integer('reserved_quantity')->default(0)->after('quantity');
                }

                // Composite Index for high-speed POS Variant Stock queries
                $table->index(['branch_id', 'product_id', 'variant_id'], 'branch_prod_var_idx');
                $table->index('sku');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('product_stocks')) {
            Schema::table('product_stocks', function (Blueprint $table) {
                // Drop indexes safely
                try {
                    $table->dropIndex('branch_prod_var_idx');
                    $table->dropIndex(['sku']);
                } catch (\Throwable $e) {
                    // Ignore if indexes do not exist
                }

                $columns = ['variant_id', 'sku', 'reserved_quantity'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('product_stocks', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};