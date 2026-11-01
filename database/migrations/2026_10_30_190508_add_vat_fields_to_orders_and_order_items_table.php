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
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'tax_amount')) {
                    $table->decimal('tax_amount', 12, 2)->default(0.00)->after('grand_total');
                }
                if (!Schema::hasColumn('orders', 'product_tax_amount')) {
                    $table->decimal('product_tax_amount', 12, 2)->default(0.00)->after('tax_amount');
                }
                if (!Schema::hasColumn('orders', 'shipping_tax_amount')) {
                    $table->decimal('shipping_tax_amount', 12, 2)->default(0.00)->after('product_tax_amount');
                }
                if (!Schema::hasColumn('orders', 'taxable_base_amount')) {
                    $table->decimal('taxable_base_amount', 14, 2)->default(0.00)->after('shipping_tax_amount');
                }
                if (!Schema::hasColumn('orders', 'is_tax_inclusive')) {
                    $table->boolean('is_tax_inclusive')->default(false)->after('taxable_base_amount');
                }
                if (!Schema::hasColumn('orders', 'tax_breakdown')) {
                    $table->longText('tax_breakdown')->nullable()->after('is_tax_inclusive');
                }
            });
        }

        $itemsTable = Schema::hasTable('order_items') ? 'order_items' : (Schema::hasTable('order_details') ? 'order_details' : null);
        if ($itemsTable) {
            Schema::table($itemsTable, function (Blueprint $table) use ($itemsTable) {
                if (!Schema::hasColumn($itemsTable, 'tax_rate')) {
                    $table->decimal('tax_rate', 8, 4)->default(0.0000)->after('unit_price');
                }
                if (!Schema::hasColumn($itemsTable, 'tax_amount')) {
                    $table->decimal('tax_amount', 12, 2)->default(0.00)->after('tax_rate');
                }
                if (!Schema::hasColumn($itemsTable, 'taxable_base_amount')) {
                    $table->decimal('taxable_base_amount', 14, 2)->default(0.00)->after('tax_amount');
                }
                if (!Schema::hasColumn($itemsTable, 'is_tax_inclusive')) {
                    $table->boolean('is_tax_inclusive')->default(false)->after('taxable_base_amount');
                }
                if (!Schema::hasColumn($itemsTable, 'tax_rule_id')) {
                    $table->unsignedBigInteger('tax_rule_id')->nullable()->after('is_tax_inclusive');
                }
                if (!Schema::hasColumn($itemsTable, 'tax_class_id')) {
                    $table->unsignedBigInteger('tax_class_id')->nullable()->after('tax_rule_id');
                }
                if (!Schema::hasColumn($itemsTable, 'tax_breakdown')) {
                    $table->longText('tax_breakdown')->nullable()->after('tax_class_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $cols = ['tax_amount', 'product_tax_amount', 'shipping_tax_amount', 'taxable_base_amount', 'is_tax_inclusive', 'tax_breakdown'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('orders', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};