<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class SecurityBlockedNetwork extends Model
{
    use HasFactory;

    protected $table = 'security_blocked_networks';

    protected $fillable = [
        'cidr_range',
        'ip_version',
        'start_ip',
        'end_ip',
        'estimated_ip_count',
        'block_reason',
        'expires_at',
        'is_active',
        'created_by',
        'admin_note',
    ];

    protected $casts = [
        'expires_at'         => 'datetime',
        'is_active'          => 'boolean',
        'estimated_ip_count' => 'integer',
    ];

    const CACHE_KEY = 'sec_active_blocked_cidrs';
    const CACHE_TTL_SECONDS = 3600; // ১ ঘণ্টা

    /**
     * সেভ, আপডেট বা ডিলিট হলে সক্রিয় সিআইডিআর ক্যাশে স্বয়ংক্রিয়ভাবে রিফ্রেশ হবে
     */
    protected static function booted()
    {
        static::saved(function () {
            Cache::forget(self::CACHE_KEY);
        });

        static::deleted(function () {
            Cache::forget(self::CACHE_KEY);
        });
    }

    /**
     * সক্রিয় এবং মেয়াদ উত্তীর্ণ না হওয়া নেটওয়ার্ক ব্লকের জন্য স্কোপ
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', Carbon::now());
            });
    }

    /**
     * সক্রিয় সকল CIDR তালিকা ক্যাশে থেকে সংগ্রহ
     */
    public static function getActiveCidrs(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            return static::active()->pluck('cidr_range')->toArray();
        });
    }

    /**
     * কোনো নির্দিষ্ট IP সক্রিয় কোনো Blocked CIDR-এর অন্তর্ভুক্ত কি না তা দ্রুত যাচাই
     */
    public static function isIpInBlockedNetwork(string $ip): bool
    {
        $activeCidrs = static::getActiveCidrs();

        if (empty($activeCidrs)) {
            return false;
        }

        foreach ($activeCidrs as $cidr) {
            if (static::ipMatchesCidr($ip, $cidr)) {
                return true;
            }
        }

        return false;
    }

    /**
     * IPv4 এবং IPv6 সাবনেট রেঞ্জ ম্যাচিং অ্যালগরিদম (Bitwise Matching)
     */
    public static function ipMatchesCidr(string $ip, string $cidr): bool
    {
        if (strpos($cidr, '/') === false) {
            return $ip === $cidr;
        }

        [$subnet, $maskBits] = explode('/', $cidr, 2);
        $maskBits = (int) $maskBits;

        // IPv4 Matching
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            if ($maskBits < 0 || $maskBits > 32) {
                return false;
            }
            if ($maskBits === 0) {
                return true;
            }

            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            $mask = -1 << (32 - $maskBits);
            $subnetLong &= $mask;

            return ($ipLong & $mask) === $subnetLong;
        }

        // IPv6 Matching
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            if ($maskBits < 0 || $maskBits > 128) {
                return false;
            }
            if ($maskBits === 0) {
                return true;
            }

            $ipBinary = inet_pton($ip);
            $subnetBinary = inet_pton($subnet);

            if ($ipBinary === false || $subnetBinary === false) {
                return false;
            }

            $bytes = (int) ($maskBits / 8);
            $remainingBits = $maskBits % 8;

            if (substr($ipBinary, 0, $bytes) !== substr($subnetBinary, 0, $bytes)) {
                return false;
            }

            if ($remainingBits > 0) {
                $ipByte = ord($ipBinary[$bytes]);
                $subnetByte = ord($subnetBinary[$bytes]);
                $mask = 0xFF << (8 - $remainingBits);

                return ($ipByte & $mask) === ($subnetByte & $mask);
            }

            return true;
        }

        return false;
    }

    /**
     * CIDR রেঞ্জ ব্লক যুক্ত করার সেন্ট্রাল হেল্পার
     */
    public static function blockCidr(
        string $cidr,
        ?int $durationDays = 7,
        string $reason = 'Suspicious network activity',
        ?int $adminId = null,
        ?string $note = null
    ): self {
        $ipVersion = strpos($cidr, ':') !== false ? 'v6' : 'v4';
        $expiresAt = ($durationDays && $durationDays > 0) ? Carbon::now()->addDays($durationDays) : null;

        $startIp = null;
        $endIp = null;
        $estimatedCount = 1;

        if (strpos($cidr, '/') !== false) {
            [$subnet, $maskBits] = explode('/', $cidr, 2);
            $maskBits = (int) $maskBits;

            if ($ipVersion === 'v4' && $maskBits <= 32 && filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $estimatedCount = ($maskBits === 32) ? 1 : (int) pow(2, (32 - $maskBits));
                $ipLong = ip2long($subnet);
                $mask = ($maskBits === 0) ? 0 : (-1 << (32 - $maskBits));
                $startLong = $ipLong & $mask;
                $endLong = $startLong | (~$mask & 0xFFFFFFFF);
                $startIp = long2ip($startLong);
                $endIp = long2ip($endLong);
            }
        } else {
            $startIp = $cidr;
            $endIp = $cidr;
        }

        $record = static::updateOrCreate(
            ['cidr_range' => $cidr],
            [
                'ip_version'         => $ipVersion,
                'start_ip'           => $startIp,
                'end_ip'             => $endIp,
                'estimated_ip_count' => $estimatedCount,
                'block_reason'       => $reason,
                'expires_at'         => $expiresAt,
                'is_active'          => true,
                'created_by'         => $adminId,
                'admin_note'         => $note,
            ]
        );

        Cache::forget(self::CACHE_KEY);
        return $record;
    }

    /**
     * অ্যাডমিন ইউজার রিলেশন
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}