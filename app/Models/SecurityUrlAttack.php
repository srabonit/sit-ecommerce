<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SecurityUrlAttack extends Model
{
    use HasFactory;

    protected $table = 'security_url_attacks';

    protected $fillable = [
        'url_path',
        'request_method',
        'total_requests',
        'failed_requests',
        'blocked_requests',
        'unique_ips_count',
        'last_attack_at',
        'sample_suspicious_payload',
    ];

    protected $casts = [
        'last_attack_at' => 'datetime',
        'total_requests' => 'integer',
        'failed_requests' => 'integer',
        'blocked_requests' => 'integer',
        'unique_ips_count' => 'integer',
    ];

    /**
     * স্কোপ: সর্বাধিক আক্রান্ত URL-গুলোর র‍্যাঙ্কিং
     */
    public function scopeMostAttacked($query, int $limit = 10)
    {
        return $query->orderByDesc('total_requests')->limit($limit);
    }

    /**
     * স্কোপ: সাম্প্রতিক আক্রান্ত URL তালিকা
     */
    public function scopeRecentAttacks($query, int $limit = 10)
    {
        return $query->orderByDesc('last_attack_at')->limit($limit);
    }

    /**
     * অ্যাটাক ও স্ক্যান হওয়া URL ট্র্যাকিং রেকর্ড হেল্পার
     *
     * @param string $path
     * @param string $method
     * @param bool $isFailed (4xx/5xx status)
     * @param bool $isBlocked
     * @param string $ip
     * @param string|null $payload
     * @return self
     */
    public static function recordAttack(
        string $path,
        string $method = 'GET',
        bool $isFailed = false,
        bool $isBlocked = false,
        string $ip = '',
        ?string $payload = null
    ): self {
        // পাথ স্যানিটাইজেশন (সর্বোচ্চ ১৯১ ক্যারেক্টার এবং ট্রিমড)
        $cleanPath = substr('/' . ltrim(trim($path), '/'), 0, 191);

        $record = static::firstOrNew(['url_path' => $cleanPath]);

        if (!$record->exists) {
            $record->request_method = strtoupper($method);
            $record->total_requests = 1;
            $record->failed_requests = $isFailed ? 1 : 0;
            $record->blocked_requests = $isBlocked ? 1 : 0;
            $record->unique_ips_count = 1;
        } else {
            $record->total_requests += 1;
            if ($isFailed) {
                $record->failed_requests += 1;
            }
            if ($isBlocked) {
                $record->blocked_requests += 1;
            }

            // ইউনিক আইপি ট্র্যাকিং (ক্যাশে ভিত্তিক লাইটওয়েট কাউন্টার)
            $ipCacheKey = 'sec_url_ips_' . md5($cleanPath);
            $seenIps = Cache::get($ipCacheKey, []);
            if (!in_array($ip, $seenIps, true) && $ip !== '') {
                $seenIps[] = $ip;
                // সর্বোচ্চ ১০০টি আইপি রেফারেন্সের জন্য ক্যাশে রাখা
                if (count($seenIps) > 100) {
                    array_shift($seenIps);
                }
                Cache::put($ipCacheKey, $seenIps, 86400);
                $record->unique_ips_count = max($record->unique_ips_count, count($seenIps));
            }
        }

        $record->last_attack_at = Carbon::now();

        // বিপজ্জনক পেলোড স্যাম্পল সেভ (যদি থাকে এবং পূর্বে না থাকে)
        if ($payload && empty($record->sample_suspicious_payload)) {
            // পাসওয়ার্ড বা সিক্রেট বাদ দিয়ে সংক্ষিপ্ত স্যাম্পল
            $sanitized = substr(strip_tags($payload), 0, 500);
            $record->sample_suspicious_payload = $sanitized;
        }

        $record->save();

        return $record;
    }
}