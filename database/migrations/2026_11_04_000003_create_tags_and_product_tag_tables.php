<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tags Table
        if (!Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->timestamps();
            });
        }

        // 2. Product-Tag Pivot Table
        if (!Schema::hasTable('product_tag')) {
            Schema::create('product_tag', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('tag_id')->index();
                $table->timestamps();

                if (Schema::hasTable('products')) {
                    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                }
                $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
                $table->unique(['product_id', 'tag_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_tag');
        Schema::dropIfExists('tags');
    }
};