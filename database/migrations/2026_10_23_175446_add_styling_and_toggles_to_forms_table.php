<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            if (!Schema::hasColumn('forms', 'show_title')) {
                $table->boolean('show_title')->default(true)->after('name');
            }
            if (!Schema::hasColumn('forms', 'show_description')) {
                $table->boolean('show_description')->default(true)->after('description');
            }
            if (!Schema::hasColumn('forms', 'styles')) {
                $table->json('styles')->nullable()->after('redirect_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn(['show_title', 'show_description', 'styles']);
        });
    }
};