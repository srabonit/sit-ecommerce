<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_cards', function (Blueprint $table) {
            $table->id();
            $table->string('badge_text')->default('Special Voucher');
            $table->string('badge_bg', 30)->default('#10b981');
            $table->string('badge_color', 30)->default('#ffffff');
            $table->string('title')->default('Get ৳150 Off');
            $table->text('subtitle')->nullable();
            $table->string('image')->nullable();
            $table->string('btn_text')->default('Claim Voucher');
            $table->string('btn_url')->default('/shop');
            $table->integer('serial')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_cards');
    }
};