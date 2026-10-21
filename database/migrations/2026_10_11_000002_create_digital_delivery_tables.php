<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ১. orders টেবিলে ডুপ্লিকেট ইমেইল গার্ড
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'digital_delivered_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('digital_delivered_at')->nullable()->after('order_status');
            });
        }

        // ২. order_licenses টেবিল নিশ্চিতকরণ
        if (!Schema::hasTable('order_licenses')) {
            Schema::create('order_licenses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->index();
                $table->unsignedBigInteger('product_id')->index();
                $table->text('license_key');
                $table->text('access_instructions')->nullable();
                $table->timestamps();
            });
        }

        // ৩. product_licenses টেবিলে order_id কলাম নিশ্চিতকরণ
        if (Schema::hasTable('product_licenses') && !Schema::hasColumn('product_licenses', 'order_id')) {
            Schema::table('product_licenses', function (Blueprint $table) {
                $table->unsignedBigInteger('order_id')->nullable()->after('status')->index();
            });
        }
    }

    public function down(): void
    {
        //
    }
};