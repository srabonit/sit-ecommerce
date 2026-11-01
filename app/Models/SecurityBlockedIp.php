<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class SecurityBlockedIp extends Model
{
    use HasFactory;

    protected $table = 'security_blocked_ips';

    protected $fillable = [
        'ip_address',
        'ip_version',
        'block_type',
        'block_reason',
        'block_category',
        'start_at',
        'expires_at',
        'is_active',
        'created_by',
        'admin_note',
        'metadata',
    ];

    protected $casts = [
        'start_at'   => 'datetime',
        'expires_at' => 'datetime',
        'is_active'  => 'boolean',
        'metadata'   => 'array',
    ];

    const CACHE_PREFIX = 'sec_blocked_ip_';
    const CACHE_TTL_SECONDS = 3600; // ১ ঘণ্টা

    /**
     * মডেল পরিবর্তন বা ডিলিট হলে সংশ্লিষ্ট আইপির ক্যাশে স্বয়ংক্রিয়ভাবে ক্লিয়ার হবে
     */
    protected static function booted()
    {
        static::saved(function ($model) {
            Cache::forget(self::CACHE_PREFIX . $model->ip_address);
        });

        static::deleted(function ($model) {
            Cache::forget(self::CACHE_PREFIX . $model->ip_address);
        });
    }

    /**
     * সক্রিয় এবং মেয়াদ উত্তীর্ণ না হওয়া ব্লকগুলোর জন্য স্কোপ
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
     * আইপিটি বর্তমানে ব্লকড কি না তা দ্রুত ক্যাশে থেকে যাচাই
     * (মেয়াদোত্তীর্ণ ব্লকের ক্ষেত্রে ডায়নামিক TTL ম্যানেজমেন্ট)
     */
    public static function isBlocked(string $ip): bool
    {
        $cacheKey = self::CACHE_PREFIX . $ip;
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return (bool) $cached;
        }

        $record = static::active()->where('ip_address', $ip)->first();

        if (!$record) {
            // ব্লকড না থাকলে ৫০০ সেকেন্ডের জন্য ক্যাশ করে রাখা (যাতে প্রতি রিকোয়েস্টে ডিবি কুয়েরি না লাগে)
            Cache::put($cacheKey, false, 300);
            return false;
        }

        // ব্লকের ধরন অনুযায়ী ক্যাশ TTL নির্ধারণ
        $ttl = self::CACHE_TTL_SECONDS;
        if ($record->expires_at) {
            $remainingSeconds = Carbon::now()->diffInSeconds($record->expires_at, false);
            if ($remainingSeconds <= 0) {
                return false;
            }
            $ttl = min($ttl, $remainingSeconds);
        }

        Cache::put($cacheKey, true, $ttl);
        return true;
    }

    /**
     * দ্রুত আইপি ব্লক তৈরি করার সেন্ট্রাল হেল্পার
     */
    public static function block(
        string $ip,
        string $type = 'temporary',
        ?int $durationMinutes = 60,
        string $reason = 'Suspicious activity detected',
        string $category = 'manual',
        ?int $adminId = null,
        ?string $note = null,
        array $metadata = []
    ): self {
        $ipVersion = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? 'v6' : 'v4';
        $expiresAt = ($type === 'temporary' && $durationMinutes > 0) 
            ? Carbon::now()->addMinutes($durationMinutes) 
            : null;

        $record = static::updateOrCreate(
            ['ip_address' => $ip],
            [
                'ip_version'     => $ipVersion,
                'block_type'     => $type,
                'block_reason'   => $reason,
                'block_category' => $category,
                'start_at'       => Carbon::now(),
                'expires_at'     => $expiresAt,
                'is_active'      => true,
                'created_by'     => $adminId,
                'admin_note'     => $note,
                'metadata'       => $metadata,
            ]
        );

        Cache::forget(self::CACHE_PREFIX . $ip);
        return $record;
    }

    /**
     * আইপি আনব্লক (Unblock) করার হেল্পার
     */
    public static function unblock(string $ip, ?string $note = null): bool
    {
        $blocked = static::where('ip_address', $ip)
            ->where('is_active', true)
            ->latest('id')
            ->first();

        if ($blocked) {
            $blocked->update([
                'is_active'  => false,
                'admin_note' => $note ? ($blocked->admin_note . " | Unblocked: " . $note) : $blocked->admin_note,
            ]);
            Cache::forget(self::CACHE_PREFIX . $ip);
            return true;
        }

        return false;
    }

    /**
     * অ্যাডমিন ইউজার রিলেশন
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}