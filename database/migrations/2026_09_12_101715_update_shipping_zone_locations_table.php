<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_zone_locations', function (Blueprint $table) {
            // যদি আগে কোড বা টাইপ কলাম থাকে, সেগুলোকে টেক্সট ফিল্ডে রূপান্তর করি
            $table->text('districts')->nullable(); // যেমন: "Dhaka, Gazipur, Narayanganj"
        });
    }

    public function down(): void
    {
        Schema::table('shipping_zone_locations', function (Blueprint $table) {
            $table->dropColumn('districts');
        });
    }
};