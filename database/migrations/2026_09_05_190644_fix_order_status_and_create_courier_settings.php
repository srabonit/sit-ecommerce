<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ১. order_status কলামকে নিরাপদ VARCHAR(50) তে রূপান্তর
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_status', 50)->default('pending')->change();
            if (!Schema::hasColumn('orders', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('orders', 'parcel_id')) {
                $table->string('parcel_id')->nullable()->after('courier_tracking_id');
            }
        });

        // ২. ডায়নামিক কুরিয়ার কনফিগারেশন টেবিল
        if (!Schema::hasTable('courier_settings')) {
            Schema::create('courier_settings', function (Blueprint $table) {
                $table->id();
                $table->string('courier_name'); // Steadfast, Pathao, RedX
                $table->string('courier_code')->unique(); // steadfast, pathao
                $table->string('api_key')->nullable();
                $table->string('secret_key')->nullable();
                $table->string('api_url')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // ডিফল্ট কুরিয়ার ডাটা যোগ করা
            DB::table('courier_settings')->insert([
                ['courier_name' => 'Steadfast Courier', 'courier_code' => 'steadfast', 'api_key' => '', 'secret_key' => '', 'api_url' => 'https://portal.steadfast.com.bd/api/v1', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['courier_name' => 'Pathao Courier', 'courier_code' => 'pathao', 'api_key' => '', 'secret_key' => '', 'api_url' => 'https://api-hermes.pathao.com', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down(): void
    {
        // Rollback
    }
};