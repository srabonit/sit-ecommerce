<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Blog posts table extensions
        Schema::table('blog_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('blog_posts', 'page_width')) {
                $table->string('page_width')->default('contained-wide');
            }
            if (!Schema::hasColumn('blog_posts', 'title_font_size')) {
                $table->string('title_font_size')->default('32px');
            }
            if (!Schema::hasColumn('blog_posts', 'show_title')) {
                $table->tinyInteger('show_title')->default(1);
            }
            if (!Schema::hasColumn('blog_posts', 'show_featured_image')) {
                $table->tinyInteger('show_featured_image')->default(1);
            }
            if (!Schema::hasColumn('blog_posts', 'show_sidebar_left')) {
                $table->tinyInteger('show_sidebar_left')->default(0);
            }
            if (!Schema::hasColumn('blog_posts', 'show_sidebar_right')) {
                $table->tinyInteger('show_sidebar_right')->default(1);
            }
            if (!Schema::hasColumn('blog_posts', 'enable_comments')) {
                $table->tinyInteger('enable_comments')->default(1);
            }
            if (!Schema::hasColumn('blog_posts', 'focus_keyphrase')) {
                $table->string('focus_keyphrase')->nullable();
            }
            if (!Schema::hasColumn('blog_posts', 'seo_canonical_url')) {
                $table->string('seo_canonical_url')->nullable();
            }
            if (!Schema::hasColumn('blog_posts', 'allow_indexing')) {
                $table->tinyInteger('allow_indexing')->default(1);
            }
            if (!Schema::hasColumn('blog_posts', 'editor_type')) {
                $table->string('editor_type')->default('visual');
            }
        });

        // 2. Blog comments nested table
        if (!Schema::hasTable('blog_comments')) {
            Schema::create('blog_comments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('blog_post_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('parent_id')->nullable()->index();
                $table->text('comment');
                $table->tinyInteger('status')->default(1); // 1 = approved
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_comments');
    }
};