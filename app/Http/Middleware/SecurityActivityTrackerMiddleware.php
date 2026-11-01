<?php

namespace App\Http\Middleware;

use App\Models\SecurityIpLog;
use App\Models\SecuritySetting;
use App\Models\SecurityUrlAttack;
use App\Services\Security\IpDetectionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityActivityTrackerMiddleware
{
    protected IpDetectionService $ipDetector;

    public function __construct(IpDetectionService $ipDetector)
    {
        $this->ipDetector = $ipDetector;
    }

    /**
     * রিকোয়েস্ট হ্যান্ডলিং
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * ব্রাউজারে রেসপন্স ডেলিভারি সম্পন্ন হওয়ার পর ব্যাকগ্রাউন্ডে অ্যাক্টিভিটি লগিং
     */
    public function terminate(Request $request, Response $response): void
    {
        // ১. মাস্টার সিকিউরিটি বা ভিজিটর ট্র্যাকিং বন্ধ থাকলে রিটার্ন
        if (!SecuritySetting::isEnabled('security_master_active', true) ||
            !SecuritySetting::isEnabled('security_visitor_tracking_active', true)) {
            return;
        }

        // ২. স্ট্যাটিক ফাইল ও অ্যাসেট এক্সক্লুশন (Performance Optimization)
        if ($this->shouldSkipRequest($request)) {
            return;
        }

        try {
            $ip = $this->ipDetector->getClientIp($request);
            $statusCode = $response->getStatusCode();
            $path = '/' . ltrim($request->path(), '/');
            $method = strtoupper($request->method());

            // ৩. অ্যাক্টিভিটি টাইপ ও রিস্ক বিশ্লেষণ
            $activityType = $this->determineActivityType($request);
            $isSuspiciousQuery = $this->hasSuspiciousQueryPayload($request);
            $riskScore = 0;
            $securityStatus = 'allowed';

            if ($statusCode >= 400 || $isSuspiciousQuery) {
                $riskScore = $isSuspiciousQuery ? 60 : ($statusCode === 404 ? 20 : 35);
                $securityStatus = 'flagged';

                // আক্রান্ত বা স্ক্যান হওয়া URL ট্র্যাকিং টেবিলে আপডেট
                SecurityUrlAttack::recordAttack(
                    $path,
                    $method,
                    $statusCode >= 400,
                    false,
                    $ip,
                    $isSuspiciousQuery ? $request->getQueryString() : null
                );
            }

            // ৪. ডেডিকেটেড লগ সেভ
            SecurityIpLog::record([
                'ip_address' => $ip,
                'ip_version' => $this->ipDetector->getIpVersion($ip),
                'user_id' => $request->user()?->id,
                'user_type' => $request->user() ? ($request->user()->role ?? 'customer') : 'guest',
                'session_id' => $request->hasSession() ? $request->session()->getId() : null,
                'device_type' => $this->ipDetector->getDeviceType($request),
                'browser' => $this->ipDetector->getBrowser($request),
                'platform' => $this->ipDetector->getPlatform($request),
                'user_agent' => substr((string) $request->header('User-Agent', ''), 0, 255),
                'request_method' => $method,
                'request_url' => $request->fullUrl(),
                'url_path' => $path,
                'referrer_url' => $request->header('referer'),
                'response_status' => $statusCode,
                'activity_type' => $activityType,
                'risk_score' => $riskScore,
                'security_status' => $securityStatus,
                'country' => $this->ipDetector->getCountry($request),
                'metadata' => [
                    'query_string' => $isSuspiciousQuery ? $request->getQueryString() : null,
                ],
            ]);
        } catch (\Throwable $e) {
            // সাইলেন্ট ব্যাকগ্রাউন্ড হ্যান্ডলিং: লগিং ফেইলিউরে কোনো এরর ছড়াবে না
        }
    }

    /**
     * স্ট্যাটিক অ্যাসেট ও অপ্রয়োজনীয় পাথ স্কিপ করার ফিল্টার
     */
    protected function shouldSkipRequest(Request $request): bool
    {
        $path = strtolower($request->path());

        // এক্সটেনশন ফিল্টার
        $ignoredExtensions = [
            '.css', '.js', '.map', '.png', '.jpg', '.jpeg', '.gif',
            '.svg', '.webp', '.ico', '.woff', '.woff2', '.ttf', '.eot',
            '.pdf', '.mp4', '.webm'
        ];

        foreach ($ignoredExtensions as $ext) {
            if (str_ends_with($path, $ext)) {
                return true;
            }
        }

        // লাইভওয়্যার বা অভ্যন্তরীণ পোbrackets স্কিপ
        if (str_starts_with($path, 'livewire/') || str_starts_with($path, '_debugbar/')) {
            return true;
        }

        return false;
    }

    /**
     * রিকোয়েস্ট পাথের ওপর ভিত্তি করে অ্যাক্টিভিটি টাইপ নির্ধারণ
     */
    protected function determineActivityType(Request $request): string
    {
        $path = strtolower($request->path());

        if (str_contains($path, 'checkout')) return 'checkout_view';
        if (str_contains($path, 'cart')) return 'cart_action';
        if (str_contains($path, 'login')) return 'login_view';
        if (str_contains($path, 'register')) return 'register_view';
        if (str_contains($path, 'product/')) return 'product_view';
        if (str_contains($path, 'category/')) return 'category_view';
        if (str_contains($path, 'search')) return 'search_query';
        if (str_contains($path, 'pos')) return 'pos_terminal';
        if (str_contains($path, 'admin')) return 'admin_panel';

        return 'page_visit';
    }

    /**
     * বিপজ্জনক বা অ্যাটাক কুয়েরি প্যারামিটার যাচাই
     */
    protected function hasSuspiciousQueryPayload(Request $request): bool
    {
        $queryString = rawurldecode((string) $request->getQueryString());

        if (empty($queryString)) {
            return false;
        }

        $suspiciousPatterns = [
            '/union\s+select/i',
            '/<script\b[^>]*>/i',
            '/base64_decode/i',
            '/\.\.\//i',                 // Path traversal
            '/\/etc\/passwd/i',
            '/\.env/i',
            '/wp-login|wp-admin/i',
            '/select.+from/i',
            '/concat\s*\(/i',
            '/javascript:/i',
            '/eval\s*\(/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $queryString)) {
                return true;
            }
        }

        return false;
    }
}