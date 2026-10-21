<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('fraud_check_settings')) {
            Schema::create('fraud_check_settings', function (Blueprint $table) {
                $table->id();
                $table->string('provider')->default('frauded.net');
                $table->string('api_url')->default('https://frauded.net/api/check-courier');
                $table->string('api_email')->nullable();
                $table->text('api_key')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // ডিফল্ট ডাটা যুক্ত করা (স্ক্রিনশটের ক্রেডেনশিয়ালসহ)
            DB::table('fraud_check_settings')->insert([
                'provider'   => 'frauded.net',
                'api_url'    => 'https://frauded.net/api/check-courier',
                'api_email'  => 'info.srabonit@gmail.com',
                'api_key'    => 'a22e8a3df2f062593be2c1b13f30e3a498b5b01c4176140a0cbf5c881e9c854a',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fraud_check_settings');
    }
};