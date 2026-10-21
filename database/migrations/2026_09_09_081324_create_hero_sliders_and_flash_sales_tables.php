<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_sliders', function (Blueprint $table) {
            $table->id();
            $table->string('badge_text')->nullable();
            $table->string('badge_bg', 30)->default('#2563eb');
            $table->string('badge_color', 30)->default('#ffffff');
            $table->string('title');
            $table->string('title_color', 30)->default('#ffffff');
            $table->text('subtitle')->nullable();
            $table->string('subtitle_color', 30)->default('rgba(255,255,255,0.7)');
            $table->string('btn_text')->default('Source Now');
            $table->string('btn_url')->default('/shop');
            $table->string('btn_bg', 30)->default('#2563eb');
            $table->string('btn_color', 30)->default('#ffffff');
            $table->string('image');
            $table->integer('serial')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('flash_sales', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Deals & Offers');
            $table->string('subtitle')->nullable()->default('Flash discounts expire soon');
            $table->dateTime('end_time')->nullable();
            $table->boolean('status')->default(true);
            $table->json('product_ids')->nullable();
            $table->boolean('right_card_status')->default(true);
            $table->string('right_card_badge')->nullable()->default('Special Voucher');
            $table->string('right_card_title')->nullable()->default('Get ৳150 Off');
            $table->string('right_card_subtitle')->nullable()->default('Special introductory discount on your first online checkout.');
            $table->string('right_card_image')->nullable();
            $table->string('right_card_btn_text')->nullable()->default('Claim Voucher');
            $table->string('right_card_btn_url')->nullable()->default('/shop');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_sliders');
        Schema::dropIfExists('flash_sales');
    }
};