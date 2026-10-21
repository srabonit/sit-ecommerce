<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // ডিফল্ট সেটিংস ডাটা ইনসার্ট
        DB::table('settings')->insert([
            ['key' => 'site_title', 'value' => 'Shop Now BD'],
            ['key' => 'site_tagline', 'value' => 'BEST ONLINE SHOP IN BD'],
            ['key' => 'tagline_status', 'value' => 'on'],
            ['key' => 'logo_width', 'value' => '150'],
            ['key' => 'logo_height', 'value' => '40'],
            ['key' => 'timezone', 'value' => 'Asia/Dhaka'],
            ['key' => 'date_format', 'value' => 'Y-m-d'],
            ['key' => 'time_format', 'value' => 'H:i'],
            ['key' => 'home_page', 'value' => 'home'],
            ['key' => 'blog_page', 'value' => 'blog'],
            ['key' => 'products_per_page', 'value' => '16'],
            ['key' => 'grid_mobile', 'value' => '2'],
            ['key' => 'grid_tab', 'value' => '3'],
            ['key' => 'grid_pc', 'value' => '4'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};