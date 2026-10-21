<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('home_sections', function (Blueprint $table) {
            $table->string('title')->nullable()->default('')->change();
            if (!Schema::hasColumn('home_sections', 'icon')) {
                $table->string('icon')->nullable()->default('fas fa-layer-group')->after('title');
            }
            if (!Schema::hasColumn('home_sections', 'title_color')) {
                $table->string('title_color')->nullable()->default('#0f172a')->after('icon');
            }
            if (!Schema::hasColumn('home_sections', 'show_header')) {
                $table->tinyInteger('show_header')->default(1)->after('title_color');
            }
            if (!Schema::hasColumn('home_sections', 'btn_bg')) {
                $table->string('btn_bg')->nullable()->after('btn_url');
            }
            if (!Schema::hasColumn('home_sections', 'btn_color')) {
                $table->string('btn_color')->nullable()->after('btn_bg');
            }
            if (!Schema::hasColumn('home_sections', 'custom_css')) {
                $table->text('custom_css')->nullable()->after('custom_content');
            }
        });
    }

    public function down(): void
    {
        //
    }
};