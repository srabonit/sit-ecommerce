<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use App\Models\SecurityAlert;
use App\Models\SecurityAllowlist;
use App\Models\SecuritySetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SecurityRulesAndSettingsController extends Controller
{
    /**
     * সিকিউরিটি সেটিংস, রুলস ও এলাওলিস্ট কনফিগারেশন পেজ
     */
    public function index(): View
    {
        // ১. গ্রুপভিত্তিক সমস্ত কনফিগারেশন সেটিংস লোড
        $generalSettings = SecuritySetting::getByGroup('general');
        $orderSettings = SecuritySetting::getByGroup('orders');
        $formSettings = SecuritySetting::getByGroup('forms');
        $loginSettings = SecuritySetting::getByGroup('login');
        $notificationSettings = SecuritySetting::getByGroup('notifications');
        $proxySettings = SecuritySetting::getByGroup('proxy');

        // ২. ট্রাস্টেড এলাওলিস্ট লোড (IP, CIDR, Endpoints)
        $allowlists = SecurityAllowlist::with('creator')->latest('id')->get();

        return view('admin.security.settings.index', compact(
            'generalSettings',
            'orderSettings',
            'formSettings',
            'loginSettings',
            'notificationSettings',
            'proxySettings',
            'allowlists'
        ));
    }

    /**
     * মাস্টার ফায়ারওয়াল সেটিংস, ভেলোসিটি থ্রেশহোল্ড ও রুলস আপডেট
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $request->validate([
            // মাস্টার ফায়ারওয়াল ও ট্র্যাকিং টগলস
            'security_master_active'             => 'nullable|in:0,1,on,true',
            'security_firewall_active'           => 'nullable|in:0,1,on,true',
            'security_visitor_tracking_active'   => 'nullable|in:0,1,on,true',
            'security_cidr_blocking_active'      => 'nullable|in:0,1,on,true',
            'security_exclude_staff_traffic'     => 'nullable|in:0,1,on,true',

            // মাল্টিপল অর্ডার স্প্যাম ভেলোসিটি লিমিট
            'security_order_limit_5min'          => 'required|integer|min:1|max:50',
            'security_order_limit_1hour'         => 'required|integer|min:1|max:100',
            'security_order_limit_24hour'        => 'required|integer|min:1|max:500',
            'security_suspicious_order_threshold'=> 'required|integer|min:10|max:100',

            // ফর্ম প্রটেকশন ও হানিপট
            'security_form_limit_per_minute'     => 'required|integer|min:1|max:30',
            'security_honeypot_active'           => 'nullable|in:0,1,on,true',

            // ব্রুট-ফোর্স প্রটেকশন
            'security_login_max_attempts'        => 'required|integer|min:3|max:20',
            'security_login_lock_duration'       => 'required|integer|min:1|max:1440',

            // নোটিফিকেশন ও অ্যালার্ট
            'security_email_notifications'       => 'nullable|in:0,1,on,true',
            'security_email_min_risk_level'      => 'required|in:low,medium,high,critical',
            'security_email_cooldown_minutes'    => 'required|integer|min:5|max:1440',
            'security_alert_notification_email'  => 'nullable|email|max:150',

            // প্রক্সি ও ক্লাউডফ্লেয়ার
            'security_cloudflare_support'        => 'nullable|in:0,1,on,true',
            'security_trusted_proxies'           => 'nullable|string|max:500',
        ]);

        $adminId = $request->user()?->id;

        // সেটিংস ডাটা ম্যাপিং
        $settingsToSave = [
            'security_master_active'              => [$request->boolean('security_master_active') ? '1' : '0', 'general'],
            'security_firewall_active'            => [$request->boolean('security_firewall_active') ? '1' : '0', 'general'],
            'security_visitor_tracking_active'    => [$request->boolean('security_visitor_tracking_active') ? '1' : '0', 'general'],
            'security_cidr_blocking_active'       => [$request->boolean('security_cidr_blocking_active') ? '1' : '0', 'general'],
            'security_exclude_staff_traffic'      => [$request->boolean('security_exclude_staff_traffic', true) ? '1' : '0', 'general'],

            'security_order_limit_5min'           => [$request->input('security_order_limit_5min'), 'orders'],
            'security_order_limit_1hour'          => [$request->input('security_order_limit_1hour'), 'orders'],
            'security_order_limit_24hour'         => [$request->input('security_order_limit_24hour'), 'orders'],
            'security_suspicious_order_threshold' => [$request->input('security_suspicious_order_threshold'), 'orders'],

            'security_form_limit_per_minute'      => [$request->input('security_form_limit_per_minute'), 'forms'],
            'security_honeypot_active'            => [$request->boolean('security_honeypot_active') ? '1' : '0', 'forms'],

            'security_login_max_attempts'         => [$request->input('security_login_max_attempts'), 'login'],
            'security_login_lock_duration'        => [$request->input('security_login_lock_duration'), 'login'],

            'security_email_notifications'        => [$request->boolean('security_email_notifications') ? '1' : '0', 'notifications'],
            'security_email_min_risk_level'       => [$request->input('security_email_min_risk_level'), 'notifications'],
            'security_email_cooldown_minutes'     => [$request->input('security_email_cooldown_minutes'), 'notifications'],
            'security_alert_notification_email'   => [$request->input('security_alert_notification_email'), 'notifications'],

            'security_cloudflare_support'         => [$request->boolean('security_cloudflare_support') ? '1' : '0', 'proxy'],
            'security_trusted_proxies'            => [$request->input('security_trusted_proxies', ''), 'proxy'],
        ];

        foreach ($settingsToSave as $key => [$val, $group]) {
            SecuritySetting::set($key, (string) $val, $group);
        }

        Cache::forget(SecuritySetting::CACHE_KEY_ALL_SETTINGS);

        SecurityAlert::createOrAggregate(
            'security_settings_updated',
            'Master Security Rules Updated',
            'Firewall thresholds, velocity limits, and staff exclusion toggles updated by administrator.',
            $request->ip(),
            'low',
            $adminId
        );

        return response()->json([
            'success' => true,
            'message' => 'Security settings and threshold rules updated successfully.',
        ]);
    }

    /**
     * ট্রাস্টেড এলাওলিস্ট এন্ট্রি সংরক্ষণ (IP, CIDR, অথবা Endpoint)
     */
    public function storeAllowlist(Request $request): JsonResponse
    {
        $request->validate([
            'type'            => 'required|in:ip,cidr,endpoint',
            'value'           => 'required|string|max:191',
            'label'           => 'required|string|max:150',
            'exemption_scope' => 'required|in:all,firewall_only,rate_limit_only',
            'note'            => 'nullable|string|max:500',
        ]);

        $type = $request->input('type');
        $value = trim($request->input('value'));

        if ($type === 'ip' && !filter_var($value, FILTER_VALIDATE_IP)) {
            return response()->json(['success' => false, 'message' => 'Invalid IP address format.'], 422);
        }

        if ($type === 'cidr' && !str_contains($value, '/')) {
            return response()->json(['success' => false, 'message' => 'Invalid CIDR format (192.168.1.0/24).'], 422);
        }

        $allowlist = SecurityAllowlist::updateOrCreate(
            ['value' => $value, 'type' => $type],
            [
                'label'           => $request->input('label'),
                'exemption_scope' => $request->input('exemption_scope'),
                'is_active'       => true,
                'created_by'      => $request->user()?->id,
                'note'            => $request->input('note'),
            ]
        );

        Cache::forget(SecurityAllowlist::CACHE_KEY_ALLOWLIST_DATA);

        return response()->json([
            'success' => true,
            'message' => "Entry '{$value}' successfully saved to Security Allowlist.",
            'data'    => $allowlist,
        ]);
    }

    /**
     * এলাওলিস্ট এন্ট্রি ডিলিট করা
     */
    public function destroyAllowlist(int $id): JsonResponse
    {
        $allowlist = SecurityAllowlist::findOrFail($id);
        $val = $allowlist->value;
        $allowlist->delete();

        Cache::forget(SecurityAllowlist::CACHE_KEY_ALLOWLIST_DATA);

        return response()->json([
            'success' => true,
            'message' => "Allowlist entry '{$val}' removed successfully.",
        ]);
    }

    /**
     * এলাওলিস্ট এন্ট্রি সক্রিয়/নিষ্ক্রিয় টগল করা
     */
    public function toggleAllowlist(int $id): JsonResponse
    {
        $allowlist = SecurityAllowlist::findOrFail($id);
        $allowlist->update(['is_active' => !$allowlist->is_active]);

        Cache::forget(SecurityAllowlist::CACHE_KEY_ALLOWLIST_DATA);

        return response()->json([
            'success'   => true,
            'message'   => "Allowlist entry status changed to " . ($allowlist->is_active ? 'Active' : 'Inactive') . ".",
            'is_active' => $allowlist->is_active,
        ]);
    }
}