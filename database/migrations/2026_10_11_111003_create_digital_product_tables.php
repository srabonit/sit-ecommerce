<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ১. products টেবিলে ডিজিটাল ফিল্ড নিশ্চিতকরণ
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'is_virtual')) {
                $table->boolean('is_virtual')->default(false)->after('product_struct');
            }
            if (!Schema::hasColumn('products', 'is_downloadable')) {
                $table->boolean('is_downloadable')->default(false)->after('is_virtual');
            }
            if (!Schema::hasColumn('products', 'download_limit')) {
                $table->integer('download_limit')->nullable()->after('is_downloadable');
            }
            if (!Schema::hasColumn('products', 'download_expiry')) {
                $table->integer('download_expiry')->nullable()->after('download_limit');
            }
            if (!Schema::hasColumn('products', 'access_instructions')) {
                $table->text('access_instructions')->nullable()->after('download_expiry');
            }
        });

        // ২. ডাউনলোডযোগ্য ফাইলসমূহের টেবিল
        if (!Schema::hasTable('product_downloads')) {
            Schema::create('product_downloads', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->string('file_name');
                $table->text('file_path')->nullable();
                $table->text('file_url')->nullable();
                $table->timestamps();

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            });
        }

        // ৩. সফটওয়্যার লাইসেন্স কি ও ডিজিটাল অ্যাক্সেস কোড টেবিল
        if (!Schema::hasTable('product_licenses')) {
            Schema::create('product_licenses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->text('license_key');
                $table->string('status', 20)->default('available')->index(); // available, assigned
                $table->timestamp('assigned_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            });
        }

        // ৪. ক্রেতার অর্ডার ভিত্তিক সুরক্ষিত ডাউনলোড পারমিশন টেবিল
        if (!Schema::hasTable('order_downloads')) {
            Schema::create('order_downloads', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->index();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('product_download_id')->index();
                $table->string('download_token', 64)->unique();
                $table->integer('download_count')->default(0);
                $table->integer('download_limit')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();

                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('product_download_id')->references('id')->on('product_downloads')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_downloads');
        Schema::dropIfExists('product_licenses');
        Schema::dropIfExists('product_downloads');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_virtual', 'is_downloadable', 'download_limit', 'download_expiry', 'access_instructions']);
        });
    }
};