<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('home_sections')) {
            Schema::create('home_sections', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('section_type')->default('custom_html_shortcode'); 
                $table->integer('order_index')->default(0);
                $table->boolean('is_active')->default(true);
                $table->json('settings')->nullable();
                $table->longText('custom_content')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('home_sections');
    }
};