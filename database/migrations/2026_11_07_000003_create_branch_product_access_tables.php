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
        // 1. Add branch_availability_mode to products table
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'branch_availability_mode')) {
                    $table->string('branch_availability_mode', 40)->default('all');
                    $table->index('branch_availability_mode');
                }
            });
        }

        // 2. Create branch_product pivot table for Mode B / Selected Branches
        if (!Schema::hasTable('branch_product')) {
            Schema::create('branch_product', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id');
                $table->unsignedBigInteger('product_id');
                $table->boolean('is_active')->default(true);
                $table->boolean('can_sell')->default(true);
                $table->boolean('is_pos_enabled')->default(true);
                $table->integer('low_stock_alert')->nullable();
                $table->string('branch_sku', 100)->nullable();
                $table->timestamps();

                $table->unique(['branch_id', 'product_id'], 'branch_product_unique');
                $table->index('branch_id');
                $table->index('product_id');
                $table->index('is_active');
                $table->index('can_sell');
                $table->index('is_pos_enabled');
            });
        }

        // 3. Create branch_product_prices table for branch-specific pricing
        if (!Schema::hasTable('branch_product_prices')) {
            Schema::create('branch_product_prices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id');
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('variant_id')->nullable();
                $table->decimal('price', 12, 2)->nullable();
                $table->decimal('sale_price', 12, 2)->nullable();
                $table->decimal('wholesale_price', 12, 2)->nullable();
                $table->decimal('min_selling_price', 12, 2)->nullable();
                $table->decimal('discount', 12, 2)->nullable();
                $table->string('discount_type', 20)->default('fixed');
                $table->timestamps();

                $table->index(['branch_id', 'product_id', 'variant_id'], 'branch_prod_var_price_idx');
                $table->index('branch_id');
                $table->index('product_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_product_prices');
        Schema::dropIfExists('branch_product');

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'branch_availability_mode')) {
                    $table->dropIndex(['branch_availability_mode']);
                    $table->dropColumn('branch_availability_mode');
                }
            });
        }
    }
};