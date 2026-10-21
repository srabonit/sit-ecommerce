<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_zone_locations', function (Blueprint $table) {
            // যদি পুরনো code বা type কলাম থাকে, তবে সেগুলোকে ড্রপ করে দিচ্ছি
            if (Schema::hasColumn('shipping_zone_locations', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('shipping_zone_locations', 'type')) {
                $table->dropColumn('type');
            }
            // districts কলামটি না থাকলে যুক্ত হবে
            if (!Schema::hasColumn('shipping_zone_locations', 'districts')) {
                $table->text('districts')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('shipping_zone_locations', function (Blueprint $table) {
            $table->string('code')->nullable();
            $table->string('type')->default('country')->nullable();
            $table->dropColumn('districts');
        });
    }
};