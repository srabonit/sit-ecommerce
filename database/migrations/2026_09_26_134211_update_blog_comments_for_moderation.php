<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_comments', function (Blueprint $table) {
            // 0 = pending, 1 = approved, 2 = spam
            if (!Schema::hasColumn('blog_comments', 'status')) {
                $table->tinyInteger('status')->default(0)->after('comment');
            }
            if (!Schema::hasColumn('blog_comments', 'is_edited')) {
                $table->boolean('is_edited')->default(false)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('blog_comments', function (Blueprint $table) {
            $table->dropColumn(['status', 'is_edited']);
        });
    }
};