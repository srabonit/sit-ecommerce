<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_zone_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained('shipping_zones')->onDelete('cascade');
            $table->string('method_type'); // 'flat_rate' বা 'free_shipping'
            $table->string('title'); // কাস্টমার চেকআউটে যা দেখবে
            $table->boolean('is_enabled')->default(true);
            $table->string('tax_status')->default('taxable'); // taxable, none
            $table->decimal('cost', 10, 2)->default(0.00);
            
            // ফ্রি শিপিংয়ের জন্য অতিরিক্ত কন্ডিশন
            $table->string('free_shipping_requires')->default('no_requirement'); // no_requirement, min_amount, coupon, etc.
            $table->decimal('min_amount', 10, 2)->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_zone_methods');
    }
};