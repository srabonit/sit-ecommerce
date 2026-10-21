<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Foreign Keys for Category, SubCategory & Brand
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('sub_category_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();

            // Basic Info
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('product_code')->unique()->nullable();
            $table->string('barcode')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();

            // Types & Structure
            $table->enum('product_type', ['physical', 'virtual', 'downloadable'])->default('physical');
            $table->enum('product_struct', ['simple', 'variable', 'grouped', 'external_affiliate', 'sample'])->default('simple');

            // Price & Stock
            $table->decimal('regular_price', 10, 2)->default(0.00);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->decimal('wholesale_price', 10, 2)->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->boolean('allow_out_of_stock_order')->default(false);

            // Thumbnail & Meta
            $table->string('thumbnail')->nullable();
            $table->string('thumbnail_title')->nullable();
            $table->string('thumbnail_alt')->nullable();

            // Flags & Links
            $table->boolean('is_featured')->default(false);
            $table->json('linked_product_ids')->nullable();
            $table->string('external_product_url')->nullable();

            // Shipping & Dimensions
            $table->unsignedBigInteger('shipping_class_id')->nullable();
            $table->decimal('specific_delivery_charge', 10, 2)->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('length', 8, 2)->nullable();

            // SEO Fields
            $table->boolean('is_seo_active')->default(true);
            $table->string('focus_keyphrase')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical_url')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};