<?php

namespace App\Services\Security;

use App\Models\SecurityAlert;
use App\Models\SecurityAllowlist;
use App\Models\SecurityBlockedIp;
use App\Models\SecuritySetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SecurityRiskEngine
{
    protected IpDetectionService $ipDetector;

    public function __construct(IpDetectionService $ipDetector)
    {
        $this->ipDetector = $ipDetector;
    }

    /**
     * অর্ডার সাবমিশনের ঝুঁকি ও ভেলোসিটি বিশ্লেষণ
     * 
     * @param Request $request
     * @param array $orderContext ['customer_phone', 'customer_email', 'order_source', 'is_pos']
     * @return array
     */
    public function evaluateOrderRisk(Request $request, array $orderContext = []): array
    {
        $ip = $this->ipDetector->getClientIp($request);
        $orderSource = $orderContext['order_source'] ?? 'online';
        $isPos = !empty($orderContext['is_pos']) || $orderSource === 'pos' || $orderSource === 'manual';

        // ১. POS ও অথেনটিকেটেড স্টাফ সেফগার্ড (False-Positive Elimination)
        if ($isPos || ($request->user() && in_array($request->user()->role, ['admin', 'staff', 'manager', 'cashier']))) {
            return [
                'risk_score' => 0,
                'risk_level' => 'low',
                'is_suspicious' => false,
                'suspicious_reason' => null,
                'security_review_status' => 'clean',
                'flags' => ['pos_or_staff_exempt'],
                'suggested_action' => 'allow',
            ];
        }

        // ২. এলাওলিস্ট চেক
        if (SecurityAllowlist::isAllowed($ip, 'rate_limit_only')) {
            return [
                'risk_score' => 0,
                'risk_level' => 'low',
                'is_suspicious' => false,
                'suspicious_reason' => null,
                'security_review_status' => 'clean',
                'flags' => ['allowlisted_ip'],
                'suggested_action' => 'allow',
            ];
        }

        $riskScore = 0;
        $flags = [];
        $reasons = [];

        // ৩. ভেলোসিটি উইন্ডো চেক (ক্যাশে ভিত্তিক লাইটওয়েট কাউন্টার)
        $velocity5Min = $this->getCacheCounter("sec_order_vel_5m_{$ip}");
        $velocity1Hour = $this->getCacheCounter("sec_order_vel_1h_{$ip}");
        $velocity24Hour = $this->getCacheCounter("sec_order_vel_24h_{$ip}");

        $limit5Min = SecuritySetting::getInt('security_order_limit_5min', 2);
        $limit1Hour = SecuritySetting::getInt('security_order_limit_1hour', 5);
        $limit24Hour = SecuritySetting::getInt('security_order_limit_24hour', 10);

        if ($velocity5Min >= $limit5Min) {
            $riskScore += 45;
            $flags[] = 'velocity_5min_exceeded';
            $reasons[] = "Rapid multiple orders ({$velocity5Min} orders in 5 mins)";
        } elseif ($velocity1Hour >= $limit1Hour) {
            $riskScore += 35;
            $flags[] = 'velocity_1hour_exceeded';
            $reasons[] = "High order frequency ({$velocity1Hour} orders in 1 hour)";
        } elseif ($velocity24Hour >= $limit24Hour) {
            $riskScore += 25;
            $flags[] = 'velocity_24hour_exceeded';
            $reasons[] = "Daily order quota exceeded ({$velocity24Hour} orders in 24 hours)";
        }

        // ৪. ডিভাইস সিগনেচার ও ইউজার এজেন্ট অ্যানোমালি
        $deviceType = $this->ipDetector->getDeviceType($request);
        if ($deviceType === 'Bot') {
            $riskScore += 40;
            $flags[] = 'bot_detected';
            $reasons[] = "Automated bot user agent";
        }

        // ৫. একাধিক ভিন্ন ভিন্ন ফোন নম্বর/ইমেইল একই আইপি থেকে ব্যবহার হচ্ছে কি না
        $phone = trim($orderContext['customer_phone'] ?? '');
        if (!empty($phone)) {
            $phoneTrackerKey = "sec_ip_phones_{$ip}";
            $trackedPhones = Cache::get($phoneTrackerKey, []);
            if (!in_array($phone, $trackedPhones, true)) {
                $trackedPhones[] = $phone;
                Cache::put($phoneTrackerKey, $trackedPhones, 86400);
            }
            if (count($trackedPhones) >= 3) {
                $riskScore += 30;
                $flags[] = 'multiple_phone_numbers_same_ip';
                $reasons[] = "Multiple customer phones (" . count($trackedPhones) . ") used from same IP";
            }
        }

        // ৬. রিস্ক লেভেল ও অ্যাকশন নির্ধারণ
        $riskScore = min(100, $riskScore);
        $isSuspicious = $riskScore >= SecuritySetting::getInt('security_suspicious_order_threshold', 40);

        $riskLevel = 'low';
        $reviewStatus = 'clean';
        $suggestedAction = 'allow';

        if ($riskScore >= 75) {
            $riskLevel = 'critical';
            $reviewStatus = 'flagged_spam';
            $suggestedAction = 'hold_and_verify';
        } elseif ($riskScore >= 50) {
            $riskLevel = 'high';
            $reviewStatus = 'under_review';
            $suggestedAction = 'manual_review';
        } elseif ($riskScore >= 35) {
            $riskLevel = 'medium';
            $reviewStatus = 'under_review';
            $suggestedAction = 'monitor';
        }

        // ৭. সাসপিশাস হলে অ্যাডমিন অ্যালার্ট তৈরি
        if ($isSuspicious) {
            SecurityAlert::createOrAggregate(
                'multiple_orders',
                "Suspicious Multiple Orders Detected from IP: {$ip}",
                implode("; ", $reasons),
                $ip,
                $riskLevel,
                $request->user()?->id,
                $request->path(),
                null,
                'Order',
                ['flags' => $flags, 'risk_score' => $riskScore]
            );
        }

        return [
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'is_suspicious' => $isSuspicious,
            'suspicious_reason' => !empty($reasons) ? implode(' | ', $reasons) : null,
            'security_review_status' => $reviewStatus,
            'flags' => $flags,
            'suggested_action' => $suggestedAction,
        ];
    }

    /**
     * সফলভাবে অর্ডার প্লেস হলে সংশ্লিষ্ট আইপির ভেলোসিটি কাউন্টার বৃদ্ধি
     */
    public function recordOrderVelocity(string $ip): void
    {
        $this->incrementCacheCounter("sec_order_vel_5m_{$ip}", 300);
        $this->incrementCacheCounter("sec_order_vel_1h_{$ip}", 3600);
        $this->incrementCacheCounter("sec_order_vel_24h_{$ip}", 86400);
    }

    /**
     * ফর্ম সাবমিশনের স্প্যাম ঝুঁকি ও হানিপট যাচাই
     */
    public function evaluateFormRisk(Request $request, string $formIdentifier = 'general_form'): array
    {
        $ip = $this->ipDetector->getClientIp($request);

        // ১. এলাওলিস্ট চেক
        if (SecurityAllowlist::isAllowed($ip, 'rate_limit_only')) {
            return ['is_spam' => false, 'risk_score' => 0, 'reason' => null];
        }

        $riskScore = 0;
        $reasons = [];

        // ২. হানিপট ফিল্ড চেক (বট ইনপুট ফাঁদ)
        // ফর্মগুলোতে লুকায়িত `_sec_hp_verification` ফিল্ড থাকবে, যা সাধারণ গ্রাহক দেখতে পাবেন না
        $honeypotValue = $request->input('_sec_hp_verification');
        if (!empty($honeypotValue)) {
            $riskScore = 100;
            $reasons[] = "Honeypot field triggered by automated bot";
        }

        // ৩. ফর্ম সাবমিশন ভেলোসিটি চেক (যেমন: ৬০ সেকেন্ডে সর্বোচ্চ ২টির বেশি সাবমিশন)
        $formVelKey = "sec_form_vel_{$ip}_{$formIdentifier}";
        $formCount = $this->getCacheCounter($formVelKey);
        $formLimit = SecuritySetting::getInt('security_form_limit_per_minute', 2);

        if ($formCount >= $formLimit) {
            $riskScore += 60;
            $reasons[] = "Form flood: Multiple submissions within 1 minute ({$formCount})";
        }

        $isSpam = $riskScore >= 50;

        if ($isSpam) {
            SecurityAlert::createOrAggregate(
                'multiple_forms',
                "Spam Form Submission from IP: {$ip}",
                "Form [{$formIdentifier}] spam attempt detected. " . implode("; ", $reasons),
                $ip,
                'high',
                $request->user()?->id,
                $request->path(),
                null,
                'FormSubmission',
                ['form_identifier' => $formIdentifier, 'risk_score' => $riskScore]
            );
        }

        return [
            'is_spam' => $isSpam,
            'risk_score' => min(100, $riskScore),
            'reason' => !empty($reasons) ? implode(' | ', $reasons) : null,
        ];
    }

    /**
     * ফর্ম সাবমিট সফল হলে ভেলোসিটি আপডেট
     */
    public function recordFormVelocity(string $ip, string $formIdentifier): void
    {
        $this->incrementCacheCounter("sec_form_vel_{$ip}_{$formIdentifier}", 60);
    }

    /**
     * লগইন অ্যাটাক ও ব্রুট-ফোর্স প্রটেকশন অ্যানালাইসিস
     * 
     * @param Request $request
     * @param string $identity (Email or Username)
     * @param bool $isFailed
     * @return array
     */
    public function handleLoginSecurity(Request $request, string $identity, bool $isFailed): array
    {
        $ip = $this->ipDetector->getClientIp($request);
        $ipFailKey = "sec_login_fail_ip_{$ip}";
        $userFailKey = "sec_login_fail_user_" . md5($identity);

        if (!$isFailed) {
            // সফল লগইনে ব্যর্থতার কাউন্টার রিসেট
            Cache::forget($ipFailKey);
            Cache::forget($userFailKey);

            return ['is_locked' => false, 'remaining_attempts' => null];
        }

        // ফেইলড অ্যাটেম্পট ইনক্রিমেন্ট (১৫ মিনিট উইন্ডো)
        $failedAttempts = $this->incrementCacheCounter($ipFailKey, 900);
        $userFailedAttempts = $this->incrementCacheCounter($userFailKey, 900);

        $maxAttempts = SecuritySetting::getInt('security_login_max_attempts', 5);
        $lockMinutes = SecuritySetting::getInt('security_login_lock_duration', 15);

        // সীমা অতিক্রম করলে স্বয়ংক্রিয় সাময়িক আইপি ব্লক
        if ($failedAttempts >= $maxAttempts) {
            SecurityBlockedIp::block(
                $ip,
                'temporary',
                $lockMinutes,
                "Exceeded maximum failed login attempts ({$failedAttempts})",
                'brute_force',
                null,
                "Auto-locked by Brute Force Engine. Targeted account: {$identity}"
            );

            SecurityAlert::createOrAggregate(
                'brute_force',
                "Brute Force Login Attack Blocked: {$ip}",
                "IP {$ip} temporarily locked for {$lockMinutes} minutes after {$failedAttempts} failed login attempts.",
                $ip,
                'critical',
                null,
                $request->path(),
                null,
                'User',
                ['identity' => $identity, 'attempts' => $failedAttempts]
            );

            return [
                'is_locked' => true,
                'lock_duration_minutes' => $lockMinutes,
                'message' => "Too many failed login attempts. Your IP has been temporarily restricted for {$lockMinutes} minutes.",
            ];
        }

        return [
            'is_locked' => false,
            'remaining_attempts' => max(0, $maxAttempts - $failedAttempts),
        ];
    }

    /**
     * ক্যাশে কাউন্টার রিড হেল্পার
     */
    protected function getCacheCounter(string $key): int
    {
        return (int) Cache::get($key, 0);
    }

    /**
     * ক্যাশে কাউন্টার অ্যাটমিক ইনক্রিমেন্ট হেল্পার
     */
    protected function incrementCacheCounter(string $key, int $ttlSeconds): int
    {
        if (!Cache::has($key)) {
            Cache::put($key, 1, $ttlSeconds);
            return 1;
        }

        return (int) Cache::increment($key);
    }
}