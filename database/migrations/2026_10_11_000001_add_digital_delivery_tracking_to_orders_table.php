<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ১. orders টেবিলে ডুপ্লিকেট ইমেইল প্রতিরোধক কলাম
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'digital_delivered_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('digital_delivered_at')->nullable()->after('order_status');
            });
        }

        // ২. গ্রাহকের অর্ডারের সাথে বরাদ্দকৃত লাইসেন্স কি সংরক্ষণের টেবিল
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

        // ৩. গ্রাহকের সিকিউর ডাউনলোড ফাইল ও টোকেন সংরক্ষণের টেবিল
        if (!Schema::hasTable('order_downloads')) {
            Schema::create('order_downloads', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->index();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('product_download_id')->nullable()->index();
                $table->string('download_token', 64)->unique();
                $table->integer('download_limit')->nullable();
                $table->integer('download_count')->default(0);
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        // ৪. product_licenses টেবিলে order_id কলাম নিশ্চিতকরণ
        if (Schema::hasTable('product_licenses') && !Schema::hasColumn('product_licenses', 'order_id')) {
            Schema::table('product_licenses', function (Blueprint $table) {
                $table->unsignedBigInteger('order_id')->nullable()->after('status')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'digital_delivered_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('digital_delivered_at');
            });
        }
    }
};