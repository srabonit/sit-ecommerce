<?php

namespace Database\Seeders;

use App\Models\SecuritySetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class SecuritySettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ১. Master Controls
            ['key' => 'security_master_active', 'value' => '1', 'group' => 'general', 'description' => 'Global security master switch.'],
            ['key' => 'security_firewall_active', 'value' => '1', 'group' => 'general', 'description' => '403 Forbidden blocking for blacklisted IPs.'],
            ['key' => 'security_visitor_tracking_active', 'value' => '1', 'group' => 'general', 'description' => 'Real-time visitor and request tracking.'],
            ['key' => 'security_cidr_blocking_active', 'value' => '1', 'group' => 'general', 'description' => 'CIDR subnet range blocking.'],

            // ২. Order Velocity
            ['key' => 'security_order_limit_5min', 'value' => '3', 'group' => 'orders', 'description' => 'Max orders per IP in 5 minutes.'],
            ['key' => 'security_order_limit_1hour', 'value' => '8', 'group' => 'orders', 'description' => 'Max orders per IP in 1 hour.'],
            ['key' => 'security_order_limit_24hour', 'value' => '20', 'group' => 'orders', 'description' => 'Max orders per IP in 24 hours.'],
            ['key' => 'security_suspicious_order_threshold', 'value' => '40', 'group' => 'orders', 'description' => 'Risk score threshold for review hold.'],

            // ৩. Form & Honeypot
            ['key' => 'security_form_limit_per_minute', 'value' => '5', 'group' => 'forms', 'description' => 'Max form submits per minute.'],
            ['key' => 'security_honeypot_active', 'value' => '1', 'group' => 'forms', 'description' => 'Invisible dummy input honeypot.'],

            // ৪. Login Protection
            ['key' => 'security_login_max_attempts', 'value' => '5', 'group' => 'login', 'description' => 'Max failed logins before lockout.'],
            ['key' => 'security_login_lock_duration', 'value' => '15', 'group' => 'login', 'description' => 'Lockout cooldown minutes.'],

            // ৫. Notifications
            ['key' => 'security_email_notifications', 'value' => '0', 'group' => 'notifications', 'description' => 'Send email on critical threats.'],
            ['key' => 'security_email_min_risk_level', 'value' => 'high', 'group' => 'notifications', 'description' => 'Min risk level for alert email.'],
            ['key' => 'security_email_cooldown_minutes', 'value' => '30', 'group' => 'notifications', 'description' => 'Cooldown between alert emails.'],
            ['key' => 'security_alert_notification_email', 'value' => null, 'group' => 'notifications', 'description' => 'Recipient alert email.'],

            // ৬. Cloudflare & Proxy
            ['key' => 'security_cloudflare_support', 'value' => '1', 'group' => 'proxy', 'description' => 'Trust CF-Connecting-IP header.'],
            ['key' => 'security_trusted_proxies', 'value' => '127.0.0.1, 10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16', 'group' => 'proxy', 'description' => 'Trusted proxy subnets.'],
        ];

        foreach ($settings as $setting) {
            SecuritySetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        Cache::forget('security_settings_all');
    }
}