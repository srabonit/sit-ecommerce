<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class SecurityAlert extends Model
{
    use HasFactory;

    protected $table = 'security_alerts';

    protected $fillable = [
        'event_type',
        'ip_address',
        'user_id',
        'risk_level',
        'title',
        'message',
        'affected_url',
        'attempt_count',
        'related_id',
        'related_type',
        'is_read',
        'email_sent',
        'email_sent_at',
        'metadata',
    ];

    protected $casts = [
        'is_read'       => 'boolean',
        'email_sent'    => 'boolean',
        'email_sent_at' => 'datetime',
        'metadata'      => 'array',
        'attempt_count' => 'integer',
    ];

    const COOLDOWN_MINUTES = 15;
    const CACHE_UNREAD_COUNT = 'sec_unread_alerts_count';

    /**
     * অ্যালার্ট তৈরি, পরিবর্তন বা মুছলে আনরিড কাউন্টার ক্যাশ অটো ক্লিয়ার হবে
     */
    protected static function booted()
    {
        static::saved(function () {
            Cache::forget(self::CACHE_UNREAD_COUNT);
        });

        static::deleted(function () {
            Cache::forget(self::CACHE_UNREAD_COUNT);
        });
    }

    /**
     * স্কোপ: অপঠিত অ্যালার্ট
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * স্কোপ: হাই এবং ক্রিটিক্যাল রিস্ক লেভেল
     */
    public function scopeCriticalOrHigh($query)
    {
        return $query->whereIn('risk_level', ['high', 'critical']);
    }

    /**
     * স্কোপ: নির্দিষ্ট ইভেন্ট টাইপ
     */
    public function scopeEventType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * অ্যাডমিন হেডারের জন্য ক্যাশড আনরিড অ্যালার্ট কাউন্টার
     */
    public static function unreadCount(): int
    {
        return Cache::remember(self::CACHE_UNREAD_COUNT, 120, function () {
            return static::unread()->count();
        });
    }

    /**
     * অ্যালার্ট ট্রিগার করার সেন্ট্রাল স্মার্ট হেল্পার (Deduplication & Grouping Engine)
     */
    public static function createOrAggregate(
        string $eventType,
        string $title,
        string $message,
        ?string $ip = null,
        string $riskLevel = 'medium',
        ?int $userId = null,
        ?string $affectedUrl = null,
        ?int $relatedId = null,
        ?string $relatedType = null,
        array $metadata = [],
        int $cooldownMinutes = self::COOLDOWN_MINUTES
    ): self {
        $cooldownTime = Carbon::now()->subMinutes($cooldownMinutes);

        // সাম্প্রতিক অপঠিত অ্যালার্ট খোঁজা (created_at অথবা updated_at বিবেচনা করে)
        $existingAlert = static::where('event_type', $eventType)
            ->where('ip_address', $ip)
            ->where('is_read', false)
            ->where(function ($q) use ($cooldownTime) {
                $q->where('updated_at', '>=', $cooldownTime)
                  ->orWhere('created_at', '>=', $cooldownTime);
            })
            ->latest('id')
            ->first();

        if ($existingAlert) {
            // সিঙ্গেল কোয়েরিতে অ্যাটেম্পট কাউন্ট ও মেটাডাটা আপডেট
            $existingAlert->attempt_count = ($existingAlert->attempt_count ?? 1) + 1;
            $existingAlert->message = $message;
            if ($affectedUrl) {
                $existingAlert->affected_url = $affectedUrl;
            }
            $existingAlert->risk_level = static::escalateRiskLevel($existingAlert->risk_level, $riskLevel);
            if (!empty($metadata)) {
                $existingAlert->metadata = array_merge($existingAlert->metadata ?? [], $metadata);
            }
            $existingAlert->save();

            return $existingAlert;
        }

        return static::create([
            'event_type'    => $eventType,
            'title'         => $title,
            'message'       => $message,
            'ip_address'    => $ip,
            'risk_level'    => $riskLevel,
            'user_id'       => $userId,
            'affected_url'  => $affectedUrl,
            'attempt_count' => 1,
            'related_id'    => $relatedId,
            'related_type'  => $relatedType,
            'is_read'       => false,
            'email_sent'    => false,
            'metadata'      => $metadata,
        ]);
    }

    /**
     * সমস্ত অপঠিত অ্যালার্ট এক ক্লিকে পঠিত করা
     */
    public static function markAllAsRead(): int
    {
        $updated = static::where('is_read', false)->update(['is_read' => true]);
        Cache::forget(self::CACHE_UNREAD_COUNT);
        return $updated;
    }

    /**
     * আক্রমণের তীব্রতায় রিস্ক লেভেল স্বয়ংক্রিয় বৃদ্ধি
     */
    protected static function escalateRiskLevel(string $currentLevel, string $newLevel): string
    {
        $priority = [
            'low'      => 1,
            'medium'   => 2,
            'high'     => 3,
            'critical' => 4,
        ];

        $currentScore = $priority[$currentLevel] ?? 2;
        $newScore = $priority[$newLevel] ?? 2;

        return ($newScore > $currentScore) ? $newLevel : $currentLevel;
    }

    /**
     * সংশ্লিষ্ট ইউজার রিলেশন
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}