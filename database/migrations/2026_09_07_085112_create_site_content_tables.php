<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ১. কাস্টম পেজেস (HTML/Raw/Blocks + Custom CSS/JS + Mandatory SEO)
        Schema::create('site_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable(); // HTML / Raw Code
            $table->json('content_blocks')->nullable(); // Elementor-style visual blocks
            $table->enum('editor_type', ['html_raw', 'blocks'])->default('html_raw');
            $table->longText('custom_css')->nullable();
            $table->longText('custom_js')->nullable();
            
            // Mandatory SEO Fields
            $table->string('seo_title');
            $table->text('seo_description');
            $table->text('seo_keywords')->nullable();
            $table->string('seo_canonical_url')->nullable();
            $table->string('seo_og_image')->nullable();
            
            $table->string('featured_image')->nullable();
            $table->enum('status', ['published', 'draft'])->default('published');
            $table->timestamps();
        });

        // ২. ব্লগ পোস্টস (Yoast SEO + Custom CSS/JS)
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('category')->nullable();
            $table->string('tags')->nullable();
            
            $table->longText('custom_css')->nullable();
            $table->longText('custom_js')->nullable();

            // Mandatory SEO
            $table->string('seo_title');
            $table->text('seo_description');
            $table->text('seo_keywords')->nullable();
            $table->string('seo_og_image')->nullable();

            $table->enum('status', ['published', 'draft'])->default('published');
            $table->timestamps();
        });

        // ৩. নেভিগেশন মেনুস
        Schema::create('site_menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->default('primary'); // primary, footer, mobile
            $table->json('items')->nullable(); // Nested menu tree
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ৪. উইজেটস
        Schema::create('site_widgets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('widget_zone'); // header, footer_col_1, sidebar, popup
            $table->string('widget_type'); // html, banner, text, category_tree
            $table->longText('content')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ৫. মিডিয়া লাইব্রেরি (WordPress-like Attachment Details)
        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type', 50)->nullable();
            $table->bigInteger('file_size')->default(0);
            $table->string('dimensions', 50)->nullable();
            $table->string('alt_text')->nullable();
            $table->string('title')->nullable();
            $table->string('caption')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_files');
        Schema::dropIfExists('site_widgets');
        Schema::dropIfExists('site_menus');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('site_pages');
    }
};