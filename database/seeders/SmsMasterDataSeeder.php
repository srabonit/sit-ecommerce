<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class SmsMasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Initial Onecodesoft v3.0 Default Provider
        $existingProvider = DB::table('sms_providers')->where('name', 'Onecodesoft')->first();
        if (!$existingProvider) {
            DB::table('sms_providers')->insert([
                'name'                   => 'Onecodesoft',
                'driver'                 => 'onecodesoft',
                'base_url'               => 'https://api.onecodesoft.com/api',
                'api_version'            => 'v3',
                'api_token'              => Crypt::encryptString('SANDBOX_PLACEHOLDER_KEY'),
                'sender_id'              => null, // Null indicates auto-resolved approved sender ID
                'sender_type'            => 'non_masking',
                'business_name'          => config('app.name', 'SHOP NOW BD'),
                'price_per_sms'          => 0.3500,
                'balance'                => 0.00,
                'estimated_sms'          => 0,
                'sandbox_mode'           => true, // Safe testing enabled by default
                'is_active'              => true,
                'is_default'             => true,
                'last_connected_at'      => null,
                'last_balance_synced_at' => null,
                'created_at'             => $now,
                'updated_at'             => $now,
            ]);
        }

        // 2. Global Toggles & Threshold Settings (sms_settings)
        $settings = [
            // General & Master Controls
            ['key' => 'global_sms_enabled', 'value' => '1', 'group' => 'general', 'type' => 'boolean'],
            ['key' => 'sms_marketing_enabled', 'value' => '1', 'group' => 'marketing', 'type' => 'boolean'],
            ['key' => 'bulk_sms_enabled', 'value' => '1', 'group' => 'marketing', 'type' => 'boolean'],
            ['key' => 'masking_sms_enabled', 'value' => '1', 'group' => 'general', 'type' => 'boolean'],
            ['key' => 'low_balance_threshold', 'value' => '50.00', 'group' => 'general', 'type' => 'decimal'],
            ['key' => 'admin_notification_phone', 'value' => '', 'group' => 'general', 'type' => 'string'],

            // Authentication & OTP Settings
            ['key' => 'login_with_number_enabled', 'value' => '1', 'group' => 'auth', 'type' => 'boolean'],
            ['key' => 'login_with_otp_enabled', 'value' => '0', 'group' => 'auth', 'type' => 'boolean'],
            ['key' => 'register_otp_enabled', 'value' => '0', 'group' => 'auth', 'type' => 'boolean'],
            ['key' => 'password_reset_otp_enabled', 'value' => '1', 'group' => 'auth', 'type' => 'boolean'],
            ['key' => 'two_factor_auth_enabled', 'value' => '0', 'group' => 'auth', 'type' => 'boolean'],
            ['key' => 'otp_mode', 'value' => 'identity', 'group' => 'auth', 'type' => 'string'], // 'identity' or 'custom'
            ['key' => 'otp_expiry_minutes', 'value' => '5', 'group' => 'auth', 'type' => 'integer'],
            ['key' => 'otp_resend_cooldown_seconds', 'value' => '60', 'group' => 'auth', 'type' => 'integer'],
            ['key' => 'max_otp_attempts', 'value' => '3', 'group' => 'auth', 'type' => 'integer'],
            ['key' => 'max_daily_otp_per_number', 'value' => '5', 'group' => 'security', 'type' => 'integer'],
            ['key' => 'max_daily_otp_per_ip', 'value' => '15', 'group' => 'security', 'type' => 'integer'],

            // Automated Event Triggers (Enable/Disable switches)
            ['key' => 'auto_sms_order_confirmation', 'value' => '1', 'group' => 'automated', 'type' => 'boolean'],
            ['key' => 'auto_sms_order_processing', 'value' => '1', 'group' => 'automated', 'type' => 'boolean'],
            ['key' => 'auto_sms_order_shipped', 'value' => '1', 'group' => 'automated', 'type' => 'boolean'],
            ['key' => 'auto_sms_order_out_for_delivery', 'value' => '1', 'group' => 'automated', 'type' => 'boolean'],
            ['key' => 'auto_sms_order_delivered', 'value' => '1', 'group' => 'automated', 'type' => 'boolean'],
            ['key' => 'auto_sms_order_cancelled', 'value' => '1', 'group' => 'automated', 'type' => 'boolean'],
            ['key' => 'auto_sms_order_returned', 'value' => '1', 'group' => 'automated', 'type' => 'boolean'],
            ['key' => 'auto_sms_payment_received', 'value' => '1', 'group' => 'automated', 'type' => 'boolean'],
            ['key' => 'auto_sms_admin_order_alert', 'value' => '1', 'group' => 'automated', 'type' => 'boolean'],
            ['key' => 'auto_sms_admin_low_balance', 'value' => '1', 'group' => 'automated', 'type' => 'boolean'],
        ];

        foreach ($settings as $setting) {
            DB::table('sms_settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value'      => $setting['value'],
                    'group'      => $setting['group'],
                    'type'       => $setting['type'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // 3. Pre-configured Dynamic Event Templates (sms_templates)
        $templates = [
            // Order Notifications
            [
                'template_key'        => 'order_confirmation',
                'name'                => 'Order Placed & Confirmed',
                'event_type'          => 'order',
                'sender_type'         => 'auto',
                'message'             => 'Dear {customer_name}, your order #{order_id} of BDT {order_total} is confirmed! Track status at: {website_url}',
                'available_variables' => json_encode(['customer_name', 'order_id', 'order_total', 'website_url', 'store_name']),
                'is_active'           => true,
                'is_unicode'          => false,
            ],
            [
                'template_key'        => 'order_processing',
                'name'                => 'Order In Processing',
                'event_type'          => 'order',
                'sender_type'         => 'auto',
                'message'             => 'Hello {customer_name}, your order #{order_id} is being packed and prepared for shipment. Thanks for choosing {store_name}!',
                'available_variables' => json_encode(['customer_name', 'order_id', 'store_name']),
                'is_active'           => true,
                'is_unicode'          => false,
            ],
            [
                'template_key'        => 'order_shipped',
                'name'                => 'Order Handed Over to Courier',
                'event_type'          => 'order',
                'sender_type'         => 'auto',
                'message'             => 'Dear {customer_name}, order #{order_id} has been dispatched via {courier_name}. Tracking ID: {tracking_number}.',
                'available_variables' => json_encode(['customer_name', 'order_id', 'courier_name', 'tracking_number', 'store_name']),
                'is_active'           => true,
                'is_unicode'          => false,
            ],
            [
                'template_key'        => 'order_out_for_delivery',
                'name'                => 'Out For Delivery Alert',
                'event_type'          => 'order',
                'sender_type'         => 'auto',
                'message'             => 'Hello {customer_name}, order #{order_id} is out for delivery today. Please keep BDT {order_total} ready for the rider.',
                'available_variables' => json_encode(['customer_name', 'order_id', 'order_total', 'store_name']),
                'is_active'           => true,
                'is_unicode'          => false,
            ],
            [
                'template_key'        => 'order_delivered',
                'name'                => 'Order Successfully Delivered',
                'event_type'          => 'order',
                'sender_type'         => 'auto',
                'message'             => 'Dear {customer_name}, your parcel for order #{order_id} has been delivered. Thank you for shopping with {store_name}!',
                'available_variables' => json_encode(['customer_name', 'order_id', 'store_name']),
                'is_active'           => true,
                'is_unicode'          => false,
            ],
            [
                'template_key'        => 'order_cancelled',
                'name'                => 'Order Cancelled Notification',
                'event_type'          => 'order',
                'sender_type'         => 'auto',
                'message'             => 'Order #{order_id} has been cancelled. If this is a mistake, please contact our support at {website_url}.',
                'available_variables' => json_encode(['customer_name', 'order_id', 'website_url', 'store_name']),
                'is_active'           => true,
                'is_unicode'          => false,
            ],

            // Customer Account & Authentication
            [
                'template_key'        => 'welcome_sms',
                'name'                => 'New Customer Welcome Message',
                'event_type'          => 'customer',
                'sender_type'         => 'auto',
                'message'             => 'Welcome to {store_name}, {customer_name}! Explore our newest collections at {website_url}.',
                'available_variables' => json_encode(['customer_name', 'store_name', 'website_url']),
                'is_active'           => true,
                'is_unicode'          => false,
            ],
            [
                'template_key'        => 'custom_login_otp',
                'name'                => 'Custom Login / Verification OTP',
                'event_type'          => 'auth',
                'sender_type'         => 'auto',
                'message'             => 'Your {store_name} verification code is {otp_code}. Valid for 5 minutes. Never share this code with anyone.',
                'available_variables' => json_encode(['store_name', 'otp_code']),
                'is_active'           => true,
                'is_unicode'          => false,
            ],
            [
                'template_key'        => 'password_reset_otp',
                'name'                => 'Password Reset OTP',
                'event_type'          => 'auth',
                'sender_type'         => 'auto',
                'message'             => 'Your password reset code for {store_name} is {otp_code}. Valid for 5 minutes.',
                'available_variables' => json_encode(['store_name', 'otp_code']),
                'is_active'           => true,
                'is_unicode'          => false,
            ],

            // Admin Internal Alerts
            [
                'template_key'        => 'admin_new_order_alert',
                'name'                => 'Admin New Order Notification',
                'event_type'          => 'admin',
                'sender_type'         => 'auto',
                'message'             => 'Admin Alert: New order #{order_id} received for BDT {order_total} from {customer_name} ({phone}).',
                'available_variables' => json_encode(['order_id', 'order_total', 'customer_name', 'phone']),
                'is_active'           => true,
                'is_unicode'          => false,
            ],
            [
                'template_key'        => 'admin_low_balance_alert',
                'name'                => 'Admin Low Balance Alert',
                'event_type'          => 'admin',
                'sender_type'         => 'auto',
                'message'             => 'Emergency Alert: Your SMS Gateway balance is low ({balance} BDT). Please recharge immediately.',
                'available_variables' => json_encode(['balance', 'provider_name']),
                'is_active'           => true,
                'is_unicode'          => false,
            ]
        ];

        foreach ($templates as $tpl) {
            DB::table('sms_templates')->updateOrInsert(
                ['template_key' => $tpl['template_key']],
                array_merge($tpl, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }
}