<?php

namespace App\Services\Security;

use App\Models\SecurityBlockedNetwork;
use App\Models\SecuritySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class IpDetectionService
{
    /**
     * সুপরিচিত ক্লাউডফ্লেয়ার (Cloudflare) অফিসিয়াল IPv4 এবং IPv6 সাবনেট তালিকা
     */
    protected array $cloudflareSubnets = [
        // IPv4
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15',
        '104.16.0.0/13',
        '104.24.0.0/14',
        '172.64.0.0/13',
        '131.0.72.0/22',
        // IPv6
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
        '2a06:98c0::/29',
        '2c0f:f248::/32',
    ];

    /**
     * রিকোয়েস্ট থেকে ভেরিফায়েড ক্লায়েন্ট আইপি সংগ্রহ
     */
    public function getClientIp(Request $request): string
    {
        $remoteAddr = (string) $request->server('REMOTE_ADDR', '127.0.0.1');

        // ১. ক্লাউডফ্লেয়ার চেক (CF-Connecting-IP ভ্যালিডেশন)
        $isBehindCloudflare = SecuritySetting::isEnabled('security_cloudflare_support', true);
        if ($isBehindCloudflare && $this->isCloudflareIp($remoteAddr)) {
            $cfConnectingIp = $request->header('CF-Connecting-IP');
            if ($cfConnectingIp && filter_var($cfConnectingIp, FILTER_VALIDATE_IP)) {
                return trim($cfConnectingIp);
            }
        }

        // ২. ট্রাস্টেড প্রক্সি কনফিগারেশন চেক
        $trustedProxies = $this->getTrustedProxies();
        if (!empty($trustedProxies) && $this->isTrustedProxy($remoteAddr, $trustedProxies)) {
            $xForwardedFor = $request->header('X-Forwarded-For');
            if ($xForwardedFor) {
                $ips = explode(',', $xForwardedFor);
                $clientCandidate = trim($ips[0]);
                if (filter_var($clientCandidate, FILTER_VALIDATE_IP)) {
                    return $clientCandidate;
                }
            }

            $xRealIp = $request->header('X-Real-IP');
            if ($xRealIp && filter_var($xRealIp, FILTER_VALIDATE_IP)) {
                return trim($xRealIp);
            }
        }

        // ৩. ডিফল্ট নিরাপদ আইপি
        $ip = $request->ip() ?: $remoteAddr;

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '127.0.0.1';
    }

    /**
     * আইপি ভার্সন নির্ধারণ ('v4' অথবা 'v6')
     */
    public function getIpVersion(string $ip): string
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? 'v6' : 'v4';
    }

    /**
     * আইপিটি প্রাইভেট বা লোকালহোস্ট রেঞ্জের কি না
     */
    public function isPrivateOrLocal(string $ip): bool
    {
        return !filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    /**
     * ডিভাইস ক্যাটাগরি ডিটেকশন (Desktop, Mobile, Tablet, Bot)
     */
    public function getDeviceType(Request $request): string
    {
        $userAgent = (string) $request->header('User-Agent', '');

        if (preg_match('/(bot|crawl|spider|slurp|curl|wget|python|postman|insomnia|scan)/i', $userAgent)) {
            return 'Bot';
        }

        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $userAgent)) {
            return 'Tablet';
        }

        if (preg_match('/(mobile|mobi|iphone|ipod|blackberry|opera mini|iemobile|windows phone)/i', $userAgent)) {
            return 'Mobile';
        }

        return 'Desktop';
    }

    /**
     * ব্রাউজার নেম নিষ্কাশন
     */
    public function getBrowser(Request $request): string
    {
        $ua = (string) $request->header('User-Agent', '');

        if (stripos($ua, 'Edg') !== false) return 'Edge';
        if (stripos($ua, 'OPR') !== false || stripos($ua, 'Opera') !== false) return 'Opera';
        if (stripos($ua, 'Brave') !== false) return 'Brave';
        if (stripos($ua, 'Chrome') !== false && stripos($ua, 'Chromium') === false) return 'Chrome';
        if (stripos($ua, 'Firefox') !== false) return 'Firefox';
        if (stripos($ua, 'Safari') !== false && stripos($ua, 'Chrome') === false) return 'Safari';
        if (stripos($ua, 'MSIE') !== false || stripos($ua, 'Trident') !== false) return 'Internet Explorer';

        return 'Other';
    }

    /**
     * অপারেটিং সিস্টেম / প্ল্যাটফর্ম নিষ্কাশন
     */
    public function getPlatform(Request $request): string
    {
        $ua = (string) $request->header('User-Agent', '');

        if (stripos($ua, 'Windows NT 10.0') !== false) return 'Windows 10/11';
        if (stripos($ua, 'Windows NT') !== false) return 'Windows';
        if (stripos($ua, 'Android') !== false) return 'Android';
        if (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) return 'iOS';
        if (stripos($ua, 'Macintosh') !== false || stripos($ua, 'Mac OS') !== false) return 'macOS';
        if (stripos($ua, 'Linux') !== false) return 'Linux';

        return 'Unknown';
    }

    /**
     * আসল দেশ নির্ধারণ (কোনো দেশ হার্ডকোড ছাড়া রিয়েল জিও-আইপি ডেটা)
     */
    public function getCountry(Request $request): ?string
    {
        // ১. ক্লাউডফ্লেয়ার বা রিভার্স প্রক্সি কান্ট্রি হেডার
        $countryCode = $request->header('CF-IPCountry')
            ?: $request->header('X-Country-Code')
            ?: $request->header('Geoip-Country-Code');

        if ($countryCode && !in_array(strtoupper(trim($countryCode)), ['XX', 'T1', 'UNKNOWN'], true) && strlen($countryCode) <= 10) {
            return $this->convertCountryCodeToName(strtoupper(trim($countryCode)));
        }

        $ip = $this->getClientIp($request);

        // ২. লোকালহোস্ট বা প্রাইভেট আইপি হলে ডেভেলপমেন্ট মেশিনের পাবলিক WAN আইপি থেকে আসল দেশ আনবে
        if ($this->isPrivateOrLocal($ip)) {
            return Cache::remember('geoip_wan_environment_country', 86400 * 7, function () {
                try {
                    $ctx = stream_context_create([
                        'http' => [
                            'timeout' => 2,
                            'header'  => "User-Agent: Mozilla/5.0\r\n",
                        ],
                    ]);
                    $res = @file_get_contents('http://ip-api.com/json/?fields=status,country', false, $ctx);
                    if ($res) {
                        $json = json_decode($res, true);
                        if (!empty($json['status']) && $json['status'] === 'success' && !empty($json['country'])) {
                            return (string) $json['country'];
                        }
                    }
                } catch (\Throwable $e) {}

                return 'Localhost';
            });
        }

        // ৩. রিয়েল পাবলিক ভিজিটর আইপির সরাসরি দেশ নির্ধারণ (আমেরিকা, কানাডা বা যে দেশের ভিজিটর ঠিক সেই দেশই রিটার্ন করবে)
        return Cache::remember('geoip_country_' . $ip, 86400 * 30, function () use ($ip) {
            try {
                $ctx = stream_context_create([
                    'http' => [
                        'timeout' => 2,
                        'header'  => "User-Agent: Mozilla/5.0\r\n",
                    ],
                ]);
                $res = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country", false, $ctx);
                if ($res) {
                    $json = json_decode($res, true);
                    if (!empty($json['status']) && $json['status'] === 'success' && !empty($json['country'])) {
                        return (string) $json['country'];
                    }
                }
            } catch (\Throwable $e) {}

            return 'Unknown';
        });
    }

    /**
     * কান্ট্রি কোডকে পূর্ণাঙ্গ দেশের নামে রূপান্তর
     */
    protected function convertCountryCodeToName(string $code): string
    {
        $map = [
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'CA' => 'Canada',
            'DE' => 'Germany',
            'FR' => 'France',
            'AU' => 'Australia',
            'BD' => 'Bangladesh',
            'IN' => 'India',
            'PK' => 'Pakistan',
            'AE' => 'United Arab Emirates',
            'SA' => 'Saudi Arabia',
            'SG' => 'Singapore',
            'MY' => 'Malaysia',
            'JP' => 'Japan',
            'IT' => 'Italy',
            'NL' => 'Netherlands',
        ];

        return $map[$code] ?? $code;
    }

    /**
     * প্রাইভেসিবান্ধব ডিভাইস সিগনেচার হ্যাশ
     */
    public function getDeviceSignature(Request $request): string
    {
        $ip = $this->getClientIp($request);
        $ua = (string) $request->header('User-Agent', '');
        $lang = (string) $request->header('Accept-Language', '');

        $maskedIp = preg_replace('/\.\d+$/', '.0', $ip);

        return hash('sha256', $maskedIp . '|' . $ua . '|' . $lang);
    }

    /**
     * মেটাডাটা বান্ডেল
     */
    public function extractRequestMetadata(Request $request): array
    {
        $ip = $this->getClientIp($request);

        return [
            'ip_address'       => $ip,
            'ip_version'       => $this->getIpVersion($ip),
            'device_type'      => $this->getDeviceType($request),
            'browser'          => $this->getBrowser($request),
            'platform'         => $this->getPlatform($request),
            'user_agent'       => substr((string) $request->header('User-Agent', ''), 0, 255),
            'country'          => $this->getCountry($request),
            'device_signature' => $this->getDeviceSignature($request),
            'is_private'       => $this->isPrivateOrLocal($ip),
        ];
    }

    /**
     * ক্লাউডফ্লেয়ার আইপি সাবনেট ম্যাচিং
     */
    protected function isCloudflareIp(string $ip): bool
    {
        foreach ($this->cloudflareSubnets as $subnet) {
            if (SecurityBlockedNetwork::ipMatchesCidr($ip, $subnet)) {
                return true;
            }
        }
        return false;
    }

    /**
     * কাস্টম ট্রাস্টেড প্রক্সি আইপি তালিকা সংগ্রহ
     */
    protected function getTrustedProxies(): array
    {
        $configured = SecuritySetting::get('security_trusted_proxies', '');
        if (empty($configured)) {
            return [];
        }

        return array_filter(array_map('trim', explode(',', (string) $configured)));
    }

    /**
     * ট্রাস্টেড প্রক্সি ম্যাচিং
     */
    protected function isTrustedProxy(string $ip, array $trustedProxies): bool
    {
        foreach ($trustedProxies as $trusted) {
            if ($trusted === $ip || (strpos($trusted, '/') !== false && SecurityBlockedNetwork::ipMatchesCidr($ip, $trusted))) {
                return true;
            }
        }
        return false;
    }
}