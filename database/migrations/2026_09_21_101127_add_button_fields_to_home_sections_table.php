<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('home_sections')) {
            Schema::table('home_sections', function (Blueprint $table) {
                if (!Schema::hasColumn('home_sections', 'btn_text')) {
                    $table->string('btn_text')->nullable()->after('title');
                }
                if (!Schema::hasColumn('home_sections', 'btn_url')) {
                    $table->string('btn_url')->nullable()->after('btn_text');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('home_sections')) {
            Schema::table('home_sections', function (Blueprint $table) {
                $table->dropColumn(['btn_text', 'btn_url']);
            });
        }
    }
};