<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('site_menus');
        Schema::create('site_menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('locations')->nullable(); // ['primary', 'mobile', 'footer', 'mobile_categories']
            $table->json('items')->nullable();     // [{title, url, type, original_title}]
            $table->boolean('auto_add_pages')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_menus');
    }
};