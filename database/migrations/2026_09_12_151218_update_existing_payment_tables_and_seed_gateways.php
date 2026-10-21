<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ১. manual_payment_methods টেবিলে qr_code_image ও type কলাম যোগ
        Schema::table('manual_payment_methods', function (Blueprint $table) {
            if (!Schema::hasColumn('manual_payment_methods', 'qr_code_image')) {
                $table->string('qr_code_image')->nullable()->after('instruction');
            }
            if (!Schema::hasColumn('manual_payment_methods', 'type')) {
                $table->string('type')->default('personal')->after('name'); // personal, merchant, bangla_qr
            }
        });

        // ২. payment_gateways টেবিলে সব পেমেন্ট গেটওয়ের রেকর্ড সিড (Insert) করা
        $gateways = [
            [
                'code' => 'bkash',
                'name' => 'bKash Payment',
                'credentials' => json_encode([
                    'app_key' => '',
                    'app_secret' => '',
                    'username' => '',
                    'password' => ''
                ]),
                'is_live' => false,
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'nagad',
                'name' => 'Nagad PGW',
                'credentials' => json_encode([
                    'merchant_id' => '',
                    'public_key' => '',
                    'private_key' => ''
                ]),
                'is_live' => false,
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'upay',
                'name' => 'Upay Payment Gateway',
                'credentials' => json_encode([
                    'merchant_id' => '',
                    'merchant_key' => '',
                    'merchant_secret' => ''
                ]),
                'is_live' => false,
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'sslcommerz',
                'name' => 'SSLCommerz Gateway',
                'credentials' => json_encode([
                    'store_id' => '',
                    'store_password' => ''
                ]),
                'is_live' => false,
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'eps',
                'name' => 'EPS (Easy Payment System)',
                'credentials' => json_encode([
                    'merchant_id' => '',
                    'merchant_key' => '',
                    'client_secret' => ''
                ]),
                'is_live' => false,
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'shurjopay',
                'name' => 'Shurjopay Payment Gateway',
                'credentials' => json_encode([
                    'username' => '',
                    'password' => '',
                    'prefix' => 'NOK'
                ]),
                'is_live' => false,
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'stripe',
                'name' => 'Stripe International (Card)',
                'credentials' => json_encode([
                    'publishable_key' => '',
                    'secret_key' => '',
                    'webhook_secret' => ''
                ]),
                'is_live' => false,
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'paypal',
                'name' => 'PayPal Checkout',
                'credentials' => json_encode([
                    'client_id' => '',
                    'client_secret' => '',
                    'app_id' => ''
                ]),
                'is_live' => false,
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($gateways as $gw) {
            $exists = DB::table('payment_gateways')->where('code', $gw['code'])->exists();
            if (!$exists) {
                DB::table('payment_gateways')->insert($gw);
            }
        }
    }

    public function down(): void
    {
        Schema::table('manual_payment_methods', function (Blueprint $table) {
            if (Schema::hasColumn('manual_payment_methods', 'qr_code_image')) {
                $table->dropColumn('qr_code_image');
            }
            if (Schema::hasColumn('manual_payment_methods', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};