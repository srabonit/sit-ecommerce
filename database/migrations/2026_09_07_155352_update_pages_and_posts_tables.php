<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Pages Table
        if (!Schema::hasTable('pages')) {
            Schema::create('pages', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->string('template')->default('default');
                $table->longText('content_blocks')->nullable();
                $table->longText('html_content')->nullable();
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->boolean('is_published')->default(true);
                $table->timestamps();
            });
        } else {
            Schema::table('pages', function (Blueprint $table) {
                if (!Schema::hasColumn('pages', 'content_blocks')) {
                    $table->longText('content_blocks')->nullable()->after('slug');
                }
                if (!Schema::hasColumn('pages', 'template')) {
                    $table->string('template')->default('default')->after('slug');
                }
            });
        }

        // 2. Posts Table
        if (!Schema::hasTable('posts')) {
            Schema::create('posts', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('excerpt')->nullable();
                $table->longText('content');
                $table->string('featured_image')->nullable();
                $table->string('category')->nullable();
                $table->boolean('is_published')->default(true);
                $table->unsignedBigInteger('views_count')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Rollback schema
    }
};