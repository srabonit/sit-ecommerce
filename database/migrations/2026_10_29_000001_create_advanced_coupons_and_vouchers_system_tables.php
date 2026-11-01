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
        // ১. বিদ্যমান coupons টেবিল থাকলে নতুন কলামগুলো নিরাপদে যুক্ত করা, না থাকলে তৈরি করা
        if (!Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique()->index();
                $table->string('name')->nullable();
                $table->text('description')->nullable();
                $table->text('customer_description')->nullable();
                $table->string('discount_type')->default('fixed'); // fixed, percentage, free_shipping
                $table->decimal('discount_amount', 12, 2)->default(0);
                $table->decimal('max_discount_amount', 12, 2)->nullable();
                $table->decimal('min_order_amount', 12, 2)->default(0);
                $table->decimal('max_order_amount', 12, 2)->nullable();
                $table->integer('total_usage_limit')->nullable();
                $table->integer('per_user_limit')->default(1);
                $table->integer('current_usage_count')->default(0);
                $table->timestamp('start_date')->nullable();
                $table->timestamp('expire_date')->nullable()->index();
                $table->boolean('is_active')->default(true)->index();
                $table->boolean('is_public')->default(true);
                $table->boolean('is_stackable')->default(false);
                $table->string('applicable_to')->default('all'); // all, specific_products, specific_categories, specific_brands
                $table->string('badge_text')->nullable();
                $table->string('banner_image')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('coupons', function (Blueprint $table) {
                if (!Schema::hasColumn('coupons', 'name')) {
                    $table->string('name')->nullable();
                }
                if (!Schema::hasColumn('coupons', 'description')) {
                    $table->text('description')->nullable();
                }
                if (!Schema::hasColumn('coupons', 'customer_description')) {
                    $table->text('customer_description')->nullable();
                }
                if (!Schema::hasColumn('coupons', 'discount_type')) {
                    $table->string('discount_type')->default('fixed');
                }
                if (!Schema::hasColumn('coupons', 'discount_amount')) {
                    $table->decimal('discount_amount', 12, 2)->default(0);
                }
                if (!Schema::hasColumn('coupons', 'max_discount_amount')) {
                    $table->decimal('max_discount_amount', 12, 2)->nullable();
                }
                if (!Schema::hasColumn('coupons', 'min_order_amount')) {
                    $table->decimal('min_order_amount', 12, 2)->default(0);
                }
                if (!Schema::hasColumn('coupons', 'max_order_amount')) {
                    $table->decimal('max_order_amount', 12, 2)->nullable();
                }
                if (!Schema::hasColumn('coupons', 'total_usage_limit')) {
                    $table->integer('total_usage_limit')->nullable();
                }
                if (!Schema::hasColumn('coupons', 'per_user_limit')) {
                    $table->integer('per_user_limit')->default(1);
                }
                if (!Schema::hasColumn('coupons', 'current_usage_count')) {
                    $table->integer('current_usage_count')->default(0);
                }
                if (!Schema::hasColumn('coupons', 'start_date')) {
                    $table->timestamp('start_date')->nullable();
                }
                if (!Schema::hasColumn('coupons', 'expire_date')) {
                    $table->timestamp('expire_date')->nullable()->index();
                }
                if (!Schema::hasColumn('coupons', 'is_active')) {
                    $table->boolean('is_active')->default(true)->index();
                }
                if (!Schema::hasColumn('coupons', 'is_public')) {
                    $table->boolean('is_public')->default(true);
                }
                if (!Schema::hasColumn('coupons', 'is_stackable')) {
                    $table->boolean('is_stackable')->default(false);
                }
                if (!Schema::hasColumn('coupons', 'applicable_to')) {
                    $table->string('applicable_to')->default('all');
                }
                if (!Schema::hasColumn('coupons', 'badge_text')) {
                    $table->string('badge_text')->nullable();
                }
                if (!Schema::hasColumn('coupons', 'banner_image')) {
                    $table->string('banner_image')->nullable();
                }
            });
        }

        // ২. কুপন পণ্য ফিল্টারিং পিভট টেবিল
        if (!Schema::hasTable('coupon_products')) {
            Schema::create('coupon_products', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('coupon_id')->index();
                $table->unsignedBigInteger('product_id')->index();
                $table->boolean('is_excluded')->default(false);
                $table->timestamps();
            });
        }

        // ৩. কুপন ক্যাটাগরি ফিল্টারিং পিভট টেবিল
        if (!Schema::hasTable('coupon_categories')) {
            Schema::create('coupon_categories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('coupon_id')->index();
                $table->unsignedBigInteger('category_id')->index();
                $table->boolean('is_excluded')->default(false);
                $table->timestamps();
            });
        }

        // ৪. কুপন স্পেসিফিক ইউজার রেস্ট্রিকশন পিভট টেবিল
        if (!Schema::hasTable('coupon_users')) {
            Schema::create('coupon_users', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('coupon_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->timestamps();
            });
        }

        // ৫. ভাউচার ক্যাম্পেইন মাস্টার টেবিল
        if (!Schema::hasTable('vouchers')) {
            Schema::create('vouchers', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('code_prefix', 20)->nullable();
                $table->string('voucher_type')->default('fixed'); // fixed, percentage, free_shipping
                $table->decimal('discount_amount', 12, 2)->default(0);
                $table->decimal('max_discount', 12, 2)->nullable();
                $table->decimal('min_spend', 12, 2)->default(0);
                $table->integer('validity_days')->default(7);
                $table->integer('total_vouchers_count')->nullable();
                $table->integer('claimed_count')->default(0);
                $table->integer('max_claims_per_user')->default(1);
                $table->timestamp('claim_start')->nullable();
                $table->timestamp('claim_end')->nullable()->index();
                $table->boolean('is_active')->default(true)->index();
                $table->string('target_audience')->default('all');
                $table->string('banner_image')->nullable();
                $table->text('terms_conditions')->nullable();
                $table->timestamps();
            });
        }

        // ৬. কাস্টমার পার্সোনাল ভাউচার ওয়ালেট টেবিল
        if (!Schema::hasTable('customer_vouchers')) {
            Schema::create('customer_vouchers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('voucher_id')->index();
                $table->string('voucher_code', 60)->unique()->index();
                $table->string('status', 30)->default('claimed')->index(); // claimed, reserved, redeemed, expired, restored, revoked
                $table->timestamp('claimed_at')->nullable()->useCurrent();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamp('redeemed_at')->nullable();
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->timestamp('transaction_lock_at')->nullable();
                $table->timestamps();
            });
        }

        // ৭. সেন্ট্রাল কুপন ও ভাউচার রিডেম্পশন অডিট হিস্ট্রি টেবিল
        if (!Schema::hasTable('coupon_voucher_redemptions')) {
            Schema::create('coupon_voucher_redemptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('type', 20); // 'coupon' or 'voucher'
                $table->unsignedBigInteger('reference_id')->nullable()->index();
                $table->unsignedBigInteger('customer_voucher_id')->nullable()->index();
                $table->string('code_applied', 60);
                $table->string('discount_type', 30)->default('fixed');
                $table->decimal('discount_amount', 12, 2)->default(0);
                $table->decimal('order_subtotal', 12, 2)->default(0);
                $table->decimal('order_final_total', 12, 2)->default(0);
                $table->json('snapshot_data')->nullable();
                $table->string('status', 30)->default('completed'); // completed, reversed, refunded
                $table->timestamps();
            });
        }

        // ৮. orders টেবিলে ডিসকাউন্ট ও ভাউচার রেফারেন্স কলামের উপস্থিতি নিশ্চিত করা
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'coupon_discount')) {
                    $table->decimal('coupon_discount', 12, 2)->default(0)->nullable();
                }
                if (!Schema::hasColumn('orders', 'coupon_code')) {
                    $table->string('coupon_code', 60)->nullable();
                }
                if (!Schema::hasColumn('orders', 'voucher_code')) {
                    $table->string('voucher_code', 60)->nullable();
                }
                if (!Schema::hasColumn('orders', 'voucher_discount')) {
                    $table->decimal('voucher_discount', 12, 2)->default(0)->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_voucher_redemptions');
        Schema::dropIfExists('customer_vouchers');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('coupon_users');
        Schema::dropIfExists('coupon_categories');
        Schema::dropIfExists('coupon_products');

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $columns = ['coupon_discount', 'coupon_code', 'voucher_code', 'voucher_discount'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('orders', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};