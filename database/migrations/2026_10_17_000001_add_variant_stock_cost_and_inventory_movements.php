<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // =========================================================================
        // ১. product_variants টেবিল আপডেট (ভ্যারিয়েন্ট স্টক ও ক্রয়মূল্য)
        // =========================================================================
        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                if (!Schema::hasColumn('product_variants', 'purchase_cost')) {
                    $table->decimal('purchase_cost', 12, 2)->default(0.00)->after('sale_price');
                }
                if (!Schema::hasColumn('product_variants', 'stock')) {
                    $table->integer('stock')->default(0)->after('purchase_cost');
                }
                if (!Schema::hasColumn('product_variants', 'alert_quantity')) {
                    $table->integer('alert_quantity')->default(5)->after('stock');
                }
            });

            // যদি পূর্বে purchase_price নামে কলাম থেকে থাকে, ডাটা purchase_cost-এ সিঙ্ক করা
            if (Schema::hasColumn('product_variants', 'purchase_price') && Schema::hasColumn('product_variants', 'purchase_cost')) {
                DB::statement("UPDATE product_variants SET purchase_cost = purchase_price WHERE (purchase_cost = 0 OR purchase_cost IS NULL) AND purchase_price > 0");
            }
        }

        // =========================================================================
        // ২. products টেবিল নিশ্চিতকরণ (সিম্পল প্রোডাক্টের ক্রয়মূল্য)
        // =========================================================================
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'purchase_cost')) {
                    // purchase_price থাকলে তার পাশে, অন্যথায় regular_price-এর পরে যুক্ত হবে
                    $afterCol = Schema::hasColumn('products', 'purchase_price') ? 'purchase_price' : 'regular_price';
                    $table->decimal('purchase_cost', 12, 2)->default(0.00)->after($afterCol);
                }
            });

            // products টেবিলে purchase_price থেকে purchase_cost-এ ব্যাকফিল
            if (Schema::hasColumn('products', 'purchase_price') && Schema::hasColumn('products', 'purchase_cost')) {
                DB::statement("UPDATE products SET purchase_cost = purchase_price WHERE (purchase_cost = 0 OR purchase_cost IS NULL) AND purchase_price > 0");
            }
        }

        // =========================================================================
        // ৩. order_items টেবিল আপডেট (অর্ডারকালীন ক্রয়মূল্যের স্থায়ী স্ন্যাপশট)
        // =========================================================================
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                if (!Schema::hasColumn('order_items', 'purchase_cost')) {
                    $table->decimal('purchase_cost', 12, 2)->default(0.00)->after('unit_price');
                }
                if (!Schema::hasColumn('order_items', 'cogs')) {
                    $table->decimal('cogs', 12, 2)->default(0.00)->after('purchase_cost');
                }
                if (!Schema::hasColumn('order_items', 'is_cost_estimated')) {
                    $table->boolean('is_cost_estimated')->default(false)->after('cogs');
                }
            });
        }

        // =========================================================================
        // ৪. inventory_movements টেবিল তৈরি (সম্পূর্ণ স্টক অডিট ও হিস্টোরি)
        // =========================================================================
        if (!Schema::hasTable('inventory_movements')) {
            Schema::create('inventory_movements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('variant_id')->nullable()->index();
                $table->string('sku', 100)->nullable()->index();
                $table->string('movement_type', 50)->index(); // 'checkout_sale', 'pos_sale', 'purchase_receive', 'adjustment', 'order_return', 'order_cancel'
                $table->integer('quantity_before')->default(0);
                $table->integer('quantity_change'); // ঋণাত্মক (যেমন: -2) বিক্রির জন্য, ধনাত্মক (যেমন: +10) ক্রয়ের জন্য
                $table->integer('quantity_after')->default(0);
                $table->decimal('unit_cost', 12, 2)->default(0.00);
                $table->decimal('unit_price', 12, 2)->nullable();
                $table->string('reference_id', 100)->nullable()->index(); // Invoice No, Order ID বা Adjustment ID
                $table->string('source', 50)->default('web'); // 'web_checkout', 'pos', 'manual_adjustment', 'purchase_order'
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->text('notes')->nullable();
                $table->timestamps();

                // Foreign Keys (শর্তসাপেক্ষ ক্যাস্কেড)
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                if (Schema::hasTable('product_variants')) {
                    $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ৪. inventory_movements টেবিল ড্রপ
        Schema::dropIfExists('inventory_movements');

        // ৩. order_items কলাম ড্রপ
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('order_items', 'is_cost_estimated')) $cols[] = 'is_cost_estimated';
                if (Schema::hasColumn('order_items', 'cogs')) $cols[] = 'cogs';
                if (Schema::hasColumn('order_items', 'purchase_cost')) $cols[] = 'purchase_cost';
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        // ২. products কলাম ড্রপ
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'purchase_cost')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('purchase_cost');
            });
        }

        // ১. product_variants কলাম ড্রপ
        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('product_variants', 'alert_quantity')) $cols[] = 'alert_quantity';
                if (Schema::hasColumn('product_variants', 'stock')) $cols[] = 'stock';
                if (Schema::hasColumn('product_variants', 'purchase_cost')) $cols[] = 'purchase_cost';
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};