<?php

namespace App\Services\Security;

use App\Models\SecurityAlert;
use App\Models\SecurityAllowlist;
use App\Models\SecurityBlockedIp;
use App\Models\SecurityBlockedNetwork;
use App\Models\SecuritySetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class IpBlockService
{
    /**
     * আইপিটি ব্লকড কি না তা ক্যাশে থেকে আল্ট্রা-ফাস্ট ভেরিফিকেশন
     * (প্রথমে সিঙ্গেল আইপি চেক, এরপর সিআইডিআর রেঞ্জ চেক)
     */
    public function isBlocked(string $ip): bool
    {
        // মাস্টার সিকিউরিটি বা ফায়ারওয়াল বন্ধ থাকলে সরাসরি বাইপাস
        if (!SecuritySetting::isEnabled('security_master_active', true) ||
            !SecuritySetting::isEnabled('security_firewall_active', true)) {
            return false;
        }

        // এলাওলিস্টে থাকলে ফায়ারওয়াল ব্লকিং কার্যকর হবে না
        if (SecurityAllowlist::isAllowed($ip, 'firewall_only')) {
            return false;
        }

        // ১. একক আইপি ব্লক যাচাই
        if (SecurityBlockedIp::isBlocked($ip)) {
            return true;
        }

        // ২. নেটওয়ার্ক / সিআইডিআর রেঞ্জ ব্লক যাচাই
        if (SecuritySetting::isEnabled('security_cidr_blocking_active', true)) {
            if (SecurityBlockedNetwork::isIpInBlockedNetwork($ip)) {
                return true;
            }
        }

        return false;
    }

    /**
     * আইপিটি কীভাবে ব্লক হয়েছে তার বিস্তারিত কারণ ও মেটাডাটা রিটার্ন করা
     */
    public function getBlockReasonDetails(string $ip): array
    {
        // একক আইপি থেকে চেক
        $directBlock = SecurityBlockedIp::active()->where('ip_address', $ip)->first();
        if ($directBlock) {
            return [
                'type' => 'single_ip',
                'reason' => $directBlock->block_reason,
                'category' => $directBlock->block_category,
                'expires_at' => $directBlock->expires_at ? $directBlock->expires_at->toDateTimeString() : 'Permanent',
                'is_permanent' => $directBlock->block_type === 'permanent',
            ];
        }

        // নেটওয়ার্ক সাবনেট থেকে চেক
        $activeCidrs = SecurityBlockedNetwork::getActiveCidrs();
        foreach ($activeCidrs as $cidr) {
            if (SecurityBlockedNetwork::ipMatchesCidr($ip, $cidr)) {
                $networkBlock = SecurityBlockedNetwork::active()->where('cidr_range', $cidr)->first();
                return [
                    'type' => 'network_cidr',
                    'cidr' => $cidr,
                    'reason' => $networkBlock ? $networkBlock->block_reason : 'Subnet restricted',
                    'category' => 'network',
                    'expires_at' => $networkBlock && $networkBlock->expires_at ? $networkBlock->expires_at->toDateTimeString() : 'Permanent',
                    'is_permanent' => empty($networkBlock->expires_at),
                ];
            }
        }

        return [
            'type' => 'unknown',
            'reason' => 'Access denied by security firewall',
            'category' => 'system',
            'expires_at' => null,
            'is_permanent' => false,
        ];
    }

    /**
     * একক আইপি নিরাপদ ব্লক করার মেথড
     */
    public function blockIp(
        string $ip,
        string $type = 'temporary',
        int $durationMinutes = 60,
        string $reason = 'Suspicious activity detected',
        string $category = 'manual',
        ?int $adminId = null,
        ?string $note = null,
        array $metadata = []
    ): array {
        // ১. সেফটি গার্ড: এলাওলিস্টে থাকা আইপি ব্লক করা যাবে না
        if (SecurityAllowlist::isAllowed($ip)) {
            return [
                'success' => false,
                'message' => 'Action rejected: IP address is protected by Security Allowlist.',
            ];
        }

        // ২. লোকালহোস্ট বা প্রাইভেট আইপি ভুল করে পার্মানেন্ট ব্লক রোধ
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            if ($type === 'permanent') {
                return [
                    'success' => false,
                    'message' => 'Action rejected: Local / Private IP ranges cannot be permanently blocked.',
                ];
            }
        }

        $record = SecurityBlockedIp::block(
            $ip,
            $type,
            $durationMinutes,
            $reason,
            $category,
            $adminId,
            $note,
            $metadata
        );

        // অ্যাডমিন অডিট অ্যালার্ট তৈরি
        SecurityAlert::createOrAggregate(
            'ip_blocked',
            "IP Address Blocked: {$ip}",
            "IP {$ip} has been blocked ({$type}) for reason: {$reason}",
            $ip,
            'high',
            $adminId,
            null,
            $record->id,
            'SecurityBlockedIp',
            ['category' => $category, 'duration_minutes' => $durationMinutes]
        );

        return [
            'success' => true,
            'message' => "IP {$ip} successfully blocked.",
            'data' => $record,
        ];
    }

    /**
     * আইপি আনব্লক করার মেথড
     */
    public function unblockIp(string $ip, ?string $note = null, ?int $adminId = null): array
    {
        $unblocked = SecurityBlockedIp::unblock($ip, $note);

        if ($unblocked) {
            SecurityAlert::createOrAggregate(
                'ip_unblocked',
                "IP Address Unblocked: {$ip}",
                "IP {$ip} was unblocked by admin.",
                $ip,
                'low',
                $adminId,
                null,
                null,
                'SecurityBlockedIp'
            );

            return [
                'success' => true,
                'message' => "IP {$ip} has been unblocked.",
            ];
        }

        return [
            'success' => false,
            'message' => "IP {$ip} was not found in active blocked list.",
        ];
    }

    /**
     * সিআইডিআর (CIDR) নেটওয়ার্ক রেঞ্জ ব্লক করার মেথড (কঠোর সেফটি গার্ডসহ)
     */
    public function blockNetwork(
        string $cidr,
        ?int $durationDays = 7,
        string $reason = 'Suspicious network attack traffic',
        ?int $adminId = null,
        ?string $note = null
    ): array {
        // ১. সিআইডিআর ফরম্যাট ভ্যালিডেশন
        if (strpos($cidr, '/') === false) {
            return [
                'success' => false,
                'message' => 'Invalid CIDR format. Example: 192.168.1.0/24 or 2001:db8::/32',
            ];
        }

        [$subnet, $mask] = explode('/', $cidr);
        $mask = (int) $mask;

        // ২. ওয়াইড-রেঞ্জ ডিজাস্টার প্রিভেনশন (IPv4-এ /16-এর চেয়ে বড় নেটওয়ার্ক ব্লক নিষিদ্ধ)
        if (filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            if ($mask < 16) {
                return [
                    'success' => false,
                    'message' => "Network range /{$mask} is too broad (over 65,536 IPs). Minimum allowed subnet mask is /16 to prevent mass collateral outage.",
                ];
            }
        }

        // ৩. অ্যাডমিন বা নিজস্ব অফিস আইপি এই সিআইডিআরে পড়ে কি না তা গার্ড চেক
        $currentAdminIp = request()->ip();
        if ($currentAdminIp && SecurityBlockedNetwork::ipMatchesCidr($currentAdminIp, $cidr)) {
            return [
                'success' => false,
                'message' => "Self-Lockout Prevention: Your current IP ({$currentAdminIp}) falls within this CIDR subnet. Block operation cancelled.",
            ];
        }

        $record = SecurityBlockedNetwork::blockCidr(
            $cidr,
            $durationDays,
            $reason,
            $adminId,
            $note
        );

        SecurityAlert::createOrAggregate(
            'network_blocked',
            "Network CIDR Blocked: {$cidr}",
            "Subnet {$cidr} blocked. Estimated impact: {$record->estimated_ip_count} IPs.",
            null,
            'critical',
            $adminId,
            null,
            $record->id,
            'SecurityBlockedNetwork',
            ['estimated_ips' => $record->estimated_ip_count]
        );

        return [
            'success' => true,
            'message' => "Network {$cidr} blocked successfully.",
            'estimated_ips' => $record->estimated_ip_count,
            'data' => $record,
        ];
    }

    /**
     * সিআইডিআর নেটওয়ার্ক আনব্লক করার মেথড
     */
    public function unblockNetwork(string $cidr, ?int $adminId = null): array
    {
        $network = SecurityBlockedNetwork::where('cidr_range', $cidr)->first();

        if ($network) {
            $network->update(['is_active' => false]);
            Cache::forget(SecurityBlockedNetwork::CACHE_KEY);

            SecurityAlert::createOrAggregate(
                'network_unblocked',
                "Network CIDR Unblocked: {$cidr}",
                "Subnet {$cidr} was unblocked.",
                null,
                'low',
                $adminId,
                null,
                $network->id,
                'SecurityBlockedNetwork'
            );

            return [
                'success' => true,
                'message' => "Network {$cidr} unblocked successfully.",
            ];
        }

        return [
            'success' => false,
            'message' => "Network {$cidr} not found in blocked records.",
        ];
    }

    /**
     * ইমার্জেন্সি ফায়ারওয়াল রিকভারি: সকল সক্রিয় ব্লক সাময়িকভাবে প্রত্যাহার
     */
    public function emergencyFlushAllBlocks(?int $adminId = null): int
    {
        $ipCount = SecurityBlockedIp::where('is_active', true)->update(['is_active' => false]);
        $netCount = SecurityBlockedNetwork::where('is_active', true)->update(['is_active' => false]);

        Cache::flush(); // সিকিউরিটি ক্যাশে সম্পূর্ণ রিসেট

        Log::channel('single')->warning("EMERGENCY FLUSH: All IP and Network blocks flushed by Admin ID: {$adminId}");

        SecurityAlert::createOrAggregate(
            'emergency_flush',
            "Emergency Security Flush Executed",
            "Emergency flush deactivated {$ipCount} IP blocks and {$netCount} CIDR network blocks.",
            null,
            'critical',
            $adminId
        );

        return $ipCount + $netCount;
    }
}