<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentDataSeeder extends Seeder
{
    public function run(): void
    {
        // ১. ম্যানুয়াল পেমেন্ট মেথডস ও বাংলা QR
        $manualMethods = [
            [
                'name' => 'Bangla QR (All Banks & MFS)',
                'account_number' => 'Merchant: 01700000000',
                'instruction' => 'আপনার bKash, Nagad, Rocket বা ব্যাংকের অ্যাপ দিয়ে নিচের Bangla QR স্ক্যান করে পেমেন্ট করুন। পেমেন্ট শেষে ট্রানজেকশন আইডি (TrxID) ও প্রেরক নাম্বার নিচে লিখুন।<br><div class="text-center my-2"><img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=BANGLA_QR_DEMO_01700000000" class="img-fluid rounded border p-1" style="max-width:180px;"></div>',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'bKash Personal (Manual)',
                'account_number' => '01700-000000 (Personal)',
                'instruction' => 'উপরের নাম্বারে bKash Send Money করুন। এরপর প্রেরক মোবাইল নম্বর ও SMS-এ প্রাপ্ত Transaction ID নিচে লিখুন।',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Nagad Personal (Manual)',
                'account_number' => '01800-000000 (Personal)',
                'instruction' => 'উপরের নাম্বারে নগদ Send Money করুন। এরপর প্রেরক মোবাইল নম্বর ও SMS-এ প্রাপ্ত Transaction ID নিচে লিখুন।',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($manualMethods as $method) {
            DB::table('manual_payment_methods')->updateOrInsert(
                ['name' => $method['name']],
                $method
            );
        }

        // ২. অটোমেটেড পেমেন্ট গেটওয়েজ (API রেডি)
        $gateways = [
            [
                'code' => 'sslcommerz',
                'name' => 'SSLCommerz Gateway',
                'credentials' => json_encode([
                    'store_id' => 'testbox',
                    'store_password' => 'qwerty',
                ]),
                'is_live' => false,
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'bkash',
                'name' => 'bKash Checkout API',
                'credentials' => json_encode([
                    'app_key' => '',
                    'app_secret' => '',
                    'username' => '',
                    'password' => '',
                ]),
                'is_live' => false,
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'nagad',
                'name' => 'Nagad Online Gateway',
                'credentials' => json_encode([
                    'merchant_id' => '',
                    'public_key' => '',
                    'private_key' => '',
                ]),
                'is_live' => false,
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($gateways as $gw) {
            DB::table('payment_gateways')->updateOrInsert(
                ['code' => $gw['code']],
                $gw
            );
        }
    }
}