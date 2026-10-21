<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ১. প্রোডাক্ট টেবিলে weight এবং shipping_class_id যোগ করা
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'weight')) {
                $table->decimal('weight', 8, 3)->default(0.000)->after('price'); // কেজি এককে (যেমন: 0.150, 0.500)
            }
            if (!Schema::hasColumn('products', 'shipping_class_id')) {
                $table->foreignId('shipping_class_id')->nullable()->after('weight');
            }
            if (!Schema::hasColumn('products', 'custom_delivery_charge')) {
                $table->decimal('custom_delivery_charge', 10, 2)->nullable()->after('shipping_class_id');
            }
            if (!Schema::hasColumn('products', 'multiply_qty_delivery_charge')) {
                $table->boolean('multiply_qty_delivery_charge')->default(false)->after('custom_delivery_charge');
            }
        });

        // ২. শিপিং ক্লাস কস্ট ও ওয়েট রুল টেবিল
        Schema::create('shipping_class_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained('shipping_zones')->onDelete('cascade');
            $table->foreignId('shipping_class_id')->nullable()->constrained('shipping_classes')->onDelete('cascade');
            $table->decimal('weight_from', 8, 3)->default(0.000);
            $table->decimal('weight_to', 8, 3)->default(0.000);
            $table->decimal('cost', 10, 2)->default(0.00);
            $table->decimal('per_kg_extra', 10, 2)->default(0.00); // ১ কেজির পর প্রতি কেজিতে বৃদ্ধি
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_class_rates');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['weight', 'shipping_class_id', 'custom_delivery_charge', 'multiply_qty_delivery_charge']);
        });
    }
};