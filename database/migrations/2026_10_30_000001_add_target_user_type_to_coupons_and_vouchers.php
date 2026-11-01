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
        // 1. Add target_user_type to coupons table
        if (Schema::hasTable('coupons') && !Schema::hasColumn('coupons', 'target_user_type')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->string('target_user_type', 30)->default('all')->after('per_user_limit');
            });
        }

        // 2. Add target_user_type to vouchers table
        if (Schema::hasTable('vouchers') && !Schema::hasColumn('vouchers', 'target_user_type')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->string('target_user_type', 30)->default('registered_only')->after('max_claims_per_user');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('coupons') && Schema::hasColumn('coupons', 'target_user_type')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->dropColumn('target_user_type');
            });
        }

        if (Schema::hasTable('vouchers') && Schema::hasColumn('vouchers', 'target_user_type')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->dropColumn('target_user_type');
            });
        }
    }
};