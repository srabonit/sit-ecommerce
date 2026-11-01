<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SecurityAllowlist extends Model
{
    use HasFactory;

    protected $table = 'security_allowlists';

    protected $fillable = [
        'type',
        'value',
        'label',
        'exemption_scope',
        'is_active',
        'created_by',
        'note',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    const CACHE_KEY_ALLOWLIST_DATA = 'sec_active_allowlist_records';
    const CACHE_KEY_IPS            = 'sec_allowlist_ips';
    const CACHE_KEY_CIDRS          = 'sec_allowlist_cidrs';
    const CACHE_KEY_ENDPOINTS      = 'sec_allowlist_endpoints';
    const CACHE_TTL_SECONDS        = 3600; // ১ ঘণ্টা

    /**
     * সেভ, আপডেট বা ডিলিট হলে সমস্ত এলাওলিস্ট ক্যাশে স্বয়ংক্রিয়ভাবে ক্লিয়ার হবে
     */
    protected static function booted()
    {
        static::saved(function () {
            static::flushAllowlistCache();
        });

        static::deleted(function () {
            static::flushAllowlistCache();
        });
    }

    /**
     * সমস্ত এলাওলিস্ট ক্যাশে ফ্ল্যাশ করার সেন্ট্রাল মেথড
     */
    public static function flushAllowlistCache(): void
    {
        Cache::forget(self::CACHE_KEY_ALLOWLIST_DATA);
        Cache::forget(self::CACHE_KEY_IPS);
        Cache::forget(self::CACHE_KEY_CIDRS);
        Cache::forget(self::CACHE_KEY_ENDPOINTS);
    }

    /**
     * সক্রিয় স্কোপ
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * ক্যাশে থেকে সক্রিয় সকল এলাওলিস্ট রেকর্ড সংগ্রহ
     */
    public static function getActiveAllowlist(): array
    {
        return Cache::remember(self::CACHE_KEY_ALLOWLIST_DATA, self::CACHE_TTL_SECONDS, function () {
            return static::active()->get(['type', 'value', 'exemption_scope'])->toArray();
        });
    }

    /**
     * নির্দিষ্ট আইপি বা রুট এলাওলিস্টের অন্তর্ভুক্ত কি না তা যাচাই
     *
     * @param string $ip
     * @param string $scope ('any', 'all', 'firewall_only', 'rate_limit_only')
     * @param string|null $endpoint (ঐচ্ছিক পাথ বা রুট এক্সাম্পশন চেক)
     * @return bool
     */
    public static function isAllowed(string $ip, string $scope = 'any', ?string $endpoint = null): bool
    {
        $records = static::getActiveAllowlist();

        if (empty($records)) {
            return false;
        }

        $ip = trim($ip);

        foreach ($records as $record) {
            $recordScope = $record['exemption_scope'] ?? 'all';

            // স্কোপ যাচাই:
            // ১. 'any' পাস করলে যেকোনো স্কোপের এলাও রেকর্ড গ্রহণযোগ্য
            // ২. রেকর্ডের স্কোপ 'all' হলে তা সব ধরনের রিকোয়েস্টেই কাজ করবে
            // ৩. অন্যথায় রিকোয়েস্টকৃত স্কোপ এবং রেকর্ডের স্কোপ হুবহু এক হতে হবে
            if ($scope !== 'any' && $recordScope !== 'all' && $recordScope !== $scope) {
                continue;
            }

            $recordVal = trim($record['value']);

            // ১. একক IP ম্যাচিং
            if ($record['type'] === 'ip' && $recordVal === $ip) {
                return true;
            }

            // ২. CIDR সাবনেট ম্যাচিং
            if ($record['type'] === 'cidr' && SecurityBlockedNetwork::ipMatchesCidr($ip, $recordVal)) {
                return true;
            }

            // ৩. স্পেসিফিক রুট বা এন্ডপয়েন্ট এক্সাম্পশন (যেমন: payment/callback, webhook/*)
            if ($record['type'] === 'endpoint' && $endpoint !== null) {
                $pattern = trim($recordVal, '/');
                $targetPath = trim($endpoint, '/');

                if ($pattern === $targetPath || Str::is($pattern, $targetPath) || Str::is($recordVal, $endpoint)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * দ্রুত এলাওলিস্টে নতুন আইপি বা সিআইডিআর যুক্ত করার হেল্পার
     */
    public static function addIp(
        string $ip,
        string $label = 'Trusted IP',
        string $scope = 'all',
        ?int $adminId = null,
        ?string $note = null
    ): self {
        $ip = trim($ip);
        $type = strpos($ip, '/') !== false ? 'cidr' : 'ip';

        $allowlist = static::updateOrCreate(
            ['value' => $ip, 'type' => $type],
            [
                'label'           => $label,
                'exemption_scope' => $scope,
                'is_active'       => true,
                'created_by'      => $adminId,
                'note'            => $note,
            ]
        );

        static::flushAllowlistCache();
        return $allowlist;
    }

    /**
     * অ্যাডমিন ইউজার রিলেশন
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}