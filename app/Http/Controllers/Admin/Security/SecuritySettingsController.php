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

class SecuritySettingsController extends Controller
{
    /**
     * সিকিউরিটি সেটিংস, রুলস ও এলাওলিস্ট কনফিগারেশন প্যানেল
     */
    public function index(Request $request): View
    {
        // ১. ডেটাবেস থেকে সমস্ত সেটিংস কি-ভ্যালু আকারে নিয়ে আসা
        $allSettings = [];
        try {
            $settingsCollection = SecuritySetting::all();
            foreach ($settingsCollection as $setting) {
                $key = $setting->key ?? $setting->setting_key ?? null;
                $value = $setting->value ?? $setting->setting_value ?? null;
                if ($key !== null) {
                    $allSettings[$key] = $value;
                }
            }
        } catch (\Throwable $e) {
            $allSettings = [];
        }

        // ২. ভিউয়ের জন্য সেটিংসগুলো ক্যাটাগরি অনুযায়ী সাজানো
        $generalSettings = [
            'security_master_active'           => $allSettings['security_master_active'] ?? '1',
            'security_firewall_active'         => $allSettings['security_firewall_active'] ?? '1',
            'security_visitor_tracking_active' => $allSettings['security_visitor_tracking_active'] ?? '1',
            'security_cidr_blocking_active'    => $allSettings['security_cidr_blocking_active'] ?? '1',
        ];

        $orderSettings = [
            'security_order_limit_5min'             => $allSettings['security_order_limit_5min'] ?? 3,
            'security_order_limit_1hour'            => $allSettings['security_order_limit_1hour'] ?? 10,
            'security_order_limit_24hour'           => $allSettings['security_order_limit_24hour'] ?? 25,
            'security_suspicious_order_threshold'   => $allSettings['security_suspicious_order_threshold'] ?? 40,
        ];

        $loginSettings = [
            'security_login_max_attempts'   => $allSettings['security_login_max_attempts'] ?? 5,
            'security_login_lock_duration'  => $allSettings['security_login_lock_duration'] ?? 15,
        ];

        $formSettings = [
            'security_form_limit_per_minute' => $allSettings['security_form_limit_per_minute'] ?? 5,
            'security_honeypot_active'       => $allSettings['security_honeypot_active'] ?? '1',
        ];

        $notificationSettings = [
            'security_email_notifications'          => $allSettings['security_email_notifications'] ?? '0',
            'security_alert_notification_email'     => $allSettings['security_alert_notification_email'] ?? '',
            'security_email_min_risk_level'         => $allSettings['security_email_min_risk_level'] ?? 'high',
            'security_email_cooldown_minutes'       => $allSettings['security_email_cooldown_minutes'] ?? 30,
        ];

        $proxySettings = [
            'security_cloudflare_support' => $allSettings['security_cloudflare_support'] ?? '1',
            'security_trusted_proxies'    => $allSettings['security_trusted_proxies'] ?? '',
        ];

        // ৩. ট্রাস্টেড এলাওলিস্ট তালিকা লোড
        $allowlists = SecurityAllowlist::with('creator')->latest('id')->get();

        return view('admin.security.settings.index', compact(
            'generalSettings',
            'orderSettings',
            'loginSettings',
            'formSettings',
            'notificationSettings',
            'proxySettings',
            'allowlists'
        ));
    }

    /**
     * মাস্টার সিকিউরিটি রুলস ও সেটিংস আপডেট
     */
    public function update(Request $request): JsonResponse
    {
        // চেকবক্স ফিল্ডগুলোর তালিকা (যা আনচেক থাকলে ফর্ম সাবমিশনে আসে না)
        $checkboxFields = [
            'security_master_active',
            'security_firewall_active',
            'security_visitor_tracking_active',
            'security_cidr_blocking_active',
            'security_honeypot_active',
            'security_email_notifications',
            'security_cloudflare_support',
        ];

        // সমস্ত ইনপুট প্রসেস করা
        $inputs = $request->except(['_token']);

        // আনচেক থাকা টগলগুলোকে '0' হিসেবে সেট করা
        foreach ($checkboxFields as $checkbox) {
            $inputs[$checkbox] = $request->has($checkbox) ? '1' : '0';
        }

        foreach ($inputs as $key => $val) {
            if ($val === null) {
                $val = '';
            }

            if (method_exists(SecuritySetting::class, 'set')) {
                SecuritySetting::set($key, (string) $val);
            } else {
                SecuritySetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => (string) $val]
                );
            }

            Cache::forget('sec_setting_' . $key);
            Cache::forget('security_setting_' . $key);
        }

        Cache::forget('security_settings_all');

        // অ্যাডমিন অডিট অ্যালার্ট
        SecurityAlert::createOrAggregate(
            'settings_updated',
            'Security Configuration Updated',
            'Perimeter security settings and thresholds were updated by administrator.',
            $request->ip(),
            'low',
            $request->user()?->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Security settings saved and caches refreshed successfully.',
        ]);
    }

    /**
     * ট্রাস্টেড এলাওলিস্টে নতুন আইপি/সিআইডিআর/এন্ডপয়েন্ট যুক্ত করা
     */
    public function storeAllowlist(Request $request): JsonResponse
    {
        $request->validate([
            'type'            => 'required|in:ip,cidr,endpoint',
            'value'           => 'required|string|max:255',
            'label'           => 'required|string|max:255',
            'exemption_scope' => 'required|in:all,rate_limit_only,firewall_only',
            'note'            => 'nullable|string|max:500',
        ]);

        $item = SecurityAllowlist::create([
            'type'            => $request->input('type'),
            'value'           => trim($request->input('value')),
            'label'           => trim($request->input('label')),
            'exemption_scope' => $request->input('exemption_scope', 'all'),
            'note'            => $request->input('note'),
            'created_by'      => $request->user()?->id,
            'is_active'       => true,
        ]);

        Cache::forget('sec_allowlist_ips');
        Cache::forget('sec_allowlist_cidrs');
        Cache::forget('sec_allowlist_endpoints');

        return response()->json([
            'success' => true,
            'message' => 'Allowlist entry added successfully.',
            'data'    => $item,
        ]);
    }

    /**
     * এলাওলিস্ট এন্ট্রির স্ট্যাটাস টগল (Active/Inactive)
     */
    public function toggleAllowlist(int $id): JsonResponse
    {
        $item = SecurityAllowlist::findOrFail($id);
        $item->is_active = !$item->is_active;
        $item->save();

        Cache::forget('sec_allowlist_ips');
        Cache::forget('sec_allowlist_cidrs');
        Cache::forget('sec_allowlist_endpoints');

        return response()->json([
            'success'   => true,
            'message'   => "Allowlist entry status changed to " . ($item->is_active ? 'active' : 'inactive') . ".",
            'is_active' => $item->is_active,
        ]);
    }

    /**
     * এলাওলিস্ট এন্ট্রি ডিলিট করা
     */
    public function destroyAllowlist(int $id): JsonResponse
    {
        $item = SecurityAllowlist::findOrFail($id);
        $item->delete();

        Cache::forget('sec_allowlist_ips');
        Cache::forget('sec_allowlist_cidrs');
        Cache::forget('sec_allowlist_endpoints');

        return response()->json([
            'success' => true,
            'message' => 'Entry removed from trusted allowlist.',
        ]);
    }
}