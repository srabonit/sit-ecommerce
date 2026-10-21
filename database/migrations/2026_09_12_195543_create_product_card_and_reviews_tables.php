<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('product_reviews')) {
            Schema::create('product_reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->tinyInteger('rating')->default(5);
                $table->text('comment');
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');
                $table->timestamps();
                $table->unique(['product_id', 'user_id']); // এক ইউজার এক প্রোডাক্টে মাত্র একবার রিভিউ দিতে পারবে
            });
        }

        if (Schema::hasTable('settings')) {
            $defaultSettings = [
                'card_show_review'        => '1',
                'card_show_wishlist'      => '1',
                'card_show_sale_badge'    => '1',
                'card_sale_badge_text'    => 'Sale!',
                'card_enable_quick_view'  => '1',
                'card_show_stockout_btn'  => '1',
                'card_hover_style'        => 'gallery_flip', // options: none, zoom, gallery_flip, slide_up
                'detail_show_reviews'     => '1',
                'detail_allow_review'     => '1',
            ];

            foreach ($defaultSettings as $key => $value) {
                DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $value]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};