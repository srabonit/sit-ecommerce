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
        // ১. orders টেবিলে এন্টারপ্রাইজ ফিন্যান্সিয়াল ও স্টক ট্র্যাকিং কলাম যুক্তকরণ
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'product_subtotal')) {
                    $table->decimal('product_subtotal', 14, 2)->default(0.00)->after('grand_total');
                }
                if (!Schema::hasColumn('orders', 'delivery_charge')) {
                    $table->decimal('delivery_charge', 12, 2)->default(0.00)->after('product_subtotal');
                }
                if (!Schema::hasColumn('orders', 'actual_courier_cost')) {
                    $table->decimal('actual_courier_cost', 12, 2)->default(0.00)->after('delivery_charge');
                }
                if (!Schema::hasColumn('orders', 'return_courier_cost')) {
                    $table->decimal('return_courier_cost', 12, 2)->default(0.00)->after('actual_courier_cost');
                }
                if (!Schema::hasColumn('orders', 'expected_cod_amount')) {
                    $table->decimal('expected_cod_amount', 14, 2)->default(0.00)->after('return_courier_cost');
                }
                if (!Schema::hasColumn('orders', 'actual_cod_received')) {
                    $table->decimal('actual_cod_received', 14, 2)->default(0.00)->after('expected_cod_amount');
                }
                if (!Schema::hasColumn('orders', 'is_stock_deducted')) {
                    $table->boolean('is_stock_deducted')->default(false)->after('order_status')->index();
                }
                if (!Schema::hasColumn('orders', 'is_stock_restored')) {
                    $table->boolean('is_stock_restored')->default(false)->after('is_stock_deducted')->index();
                }
                if (!Schema::hasColumn('orders', 'is_cod_settled')) {
                    $table->boolean('is_cod_settled')->default(false)->after('is_stock_restored')->index();
                }
                if (!Schema::hasColumn('orders', 'settled_at')) {
                    $table->dateTime('settled_at')->nullable()->after('is_cod_settled');
                }
            });

            // ঐতিহাসিক অর্ডারের ডেটা প্রিজারভেশন (পুরোনো অর্ডার যাতে পুনরায় স্টক না কাটে)
            DB::table('orders')
                ->whereIn(DB::raw('LOWER(order_status)'), ['delivered', 'shipped', 'completed'])
                ->update(['is_stock_deducted' => true]);

            DB::table('orders')
                ->whereIn(DB::raw('LOWER(order_status)'), ['cancelled', 'returned'])
                ->update(['is_stock_restored' => true]);
        }

        // ২. products টেবিলে এস্টিমেটেড ও রিজার্ভড স্টক কলাম
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'estimated_order_qty')) {
                    $table->integer('estimated_order_qty')->default(0)->after('stock');
                }
                if (!Schema::hasColumn('products', 'reserved_stock')) {
                    $table->integer('reserved_stock')->default(0)->after('estimated_order_qty');
                }
            });
        }

        // ৩. product_variants টেবিলে এস্টিমেটেড ও রিজার্ভড স্টক কলাম
        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                if (!Schema::hasColumn('product_variants', 'estimated_order_qty')) {
                    $table->integer('estimated_order_qty')->default(0)->after('stock');
                }
                if (!Schema::hasColumn('product_variants', 'reserved_stock')) {
                    $table->integer('reserved_stock')->default(0)->after('estimated_order_qty');
                }
            });
        }

        // ৪. courier_bookings টেবিলে রিকনসিলিয়েশন ও চার্জ কলাম
        if (Schema::hasTable('courier_bookings')) {
            Schema::table('courier_bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('courier_bookings', 'delivery_fee')) {
                    $table->decimal('delivery_fee', 12, 2)->default(0.00)->after('cod_amount');
                }
                if (!Schema::hasColumn('courier_bookings', 'return_fee')) {
                    $table->decimal('return_fee', 12, 2)->default(0.00)->after('delivery_fee');
                }
                if (!Schema::hasColumn('courier_bookings', 'cod_fee')) {
                    $table->decimal('cod_fee', 12, 2)->default(0.00)->after('return_fee');
                }
                if (!Schema::hasColumn('courier_bookings', 'reconciled_amount')) {
                    $table->decimal('reconciled_amount', 14, 2)->default(0.00)->after('cod_fee');
                }
                if (!Schema::hasColumn('courier_bookings', 'discrepancy_amount')) {
                    $table->decimal('discrepancy_amount', 14, 2)->default(0.00)->after('reconciled_amount');
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
                $cols = ['product_subtotal', 'delivery_charge', 'actual_courier_cost', 'return_courier_cost', 'expected_cod_amount', 'actual_cod_received', 'is_stock_deducted', 'is_stock_restored', 'is_cod_settled', 'settled_at'];
                $table->dropColumn(array_filter($cols, fn($c) => Schema::hasColumn('orders', $c)));
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $cols = ['estimated_order_qty', 'reserved_stock'];
                $table->dropColumn(array_filter($cols, fn($c) => Schema::hasColumn('products', $c)));
            });
        }

        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $cols = ['estimated_order_qty', 'reserved_stock'];
                $table->dropColumn(array_filter($cols, fn($c) => Schema::hasColumn('product_variants', $c)));
            });
        }

        if (Schema::hasTable('courier_bookings')) {
            Schema::table('courier_bookings', function (Blueprint $table) {
                $cols = ['delivery_fee', 'return_fee', 'cod_fee', 'reconciled_amount', 'discrepancy_amount'];
                $table->dropColumn(array_filter($cols, fn($c) => Schema::hasColumn('courier_bookings', $c)));
            });
        }
    }
};