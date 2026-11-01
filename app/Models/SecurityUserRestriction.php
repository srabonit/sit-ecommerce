<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SecurityUserRestriction extends Model
{
    use HasFactory;

    protected $table = 'security_user_restrictions';

    protected $fillable = [
        'user_id',
        'restriction_type',
        'reason',
        'admin_note',
        'expires_at',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active'  => 'boolean',
    ];

    const CACHE_PREFIX = 'sec_user_restriction_';
    const CACHE_TTL_SECONDS = 1800; // ৩০ মিনিট

    /**
     * মডেল পরিবর্তন বা ডিলিট হলে ইউজারের রেস্ট্রিকশন ক্যাশে স্বয়ংক্রিয়ভাবে ইনভ্যালিডেট হবে
     */
    protected static function booted()
    {
        static::saved(function ($model) {
            Cache::forget(self::CACHE_PREFIX . $model->user_id);
        });

        static::deleted(function ($model) {
            Cache::forget(self::CACHE_PREFIX . $model->user_id);
        });
    }

    /**
     * স্কোপ: সক্রিয় এবং মেয়াদ শেষ না হওয়া রেস্ট্রিকশন
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
     * নির্দিষ্ট ইউজার কোনো অ্যাকশনের জন্য রেস্ট্রিক্টেড কি না তা দ্রুত যাচাই
     *
     * @param int $userId
     * @param string|null $action ('login', 'checkout', 'form', null)
     * @return bool
     */
    public static function isUserRestricted(int $userId, ?string $action = null): bool
    {
        $cacheKey = self::CACHE_PREFIX . $userId;
        $activeRestrictions = Cache::get($cacheKey);

        if ($activeRestrictions === null) {
            $records = static::active()
                ->where('user_id', $userId)
                ->get(['restriction_type', 'expires_at']);

            $activeRestrictions = $records->pluck('restriction_type')->toArray();

            // ডায়নামিক TTL নির্ধারণ (সবচেয়ে কাছের মেয়াদ অনুযায়ী ক্যাশ টাইম সিঙ্ক হবে)
            $ttl = self::CACHE_TTL_SECONDS;
            foreach ($records as $rec) {
                if ($rec->expires_at) {
                    $diff = Carbon::now()->diffInSeconds($rec->expires_at, false);
                    if ($diff > 0 && $diff < $ttl) {
                        $ttl = $diff;
                    }
                }
            }

            Cache::put($cacheKey, $activeRestrictions, $ttl);
        }

        if (empty($activeRestrictions)) {
            return false;
        }

        // পার্মানেন্ট সাসপেনশন বা সাময়িক সম্পূর্ণ লক থাকলে সব অ্যাকশন সরাসরি বাতিল
        if (in_array('permanent_suspension', $activeRestrictions, true) || 
            in_array('temporary_lock', $activeRestrictions, true)) {
            return true;
        }

        if ($action === 'login' && in_array('login_restricted', $activeRestrictions, true)) {
            return true;
        }

        if ($action === 'checkout' && in_array('checkout_restricted', $activeRestrictions, true)) {
            return true;
        }

        if ($action === 'form' && in_array('form_restricted', $activeRestrictions, true)) {
            return true;
        }

        return false;
    }

    /**
     * ইউজারের ওপর নতুন রেস্ট্রিকশন আরোপ করার সেন্ট্রাল হেল্পার
     */
    public static function applyRestriction(
        int $userId,
        string $type,
        string $reason = 'Security policy violation',
        ?int $durationHours = null,
        ?int $adminId = null,
        ?string $adminNote = null
    ): self {
        $expiresAt = ($durationHours && $durationHours > 0) 
            ? Carbon::now()->addHours($durationHours) 
            : null;

        $record = static::create([
            'user_id'          => $userId,
            'restriction_type' => $type,
            'reason'           => $reason,
            'admin_note'       => $adminNote,
            'expires_at'       => $expiresAt,
            'is_active'        => true,
            'created_by'       => $adminId,
        ]);

        Cache::forget(self::CACHE_PREFIX . $userId);

        return $record;
    }

    /**
     * ইউজারের সমস্ত সক্রিয় রেস্ট্রিকশন প্রত্যাহার (Lift/Clear) করার হেল্পার
     */
    public static function liftAllRestrictions(int $userId, ?string $note = null): int
    {
        $sanitizedNote = $note ? addslashes($note) : 'Manual Admin Lift';

        $updated = static::where('user_id', $userId)
            ->where('is_active', true)
            ->update([
                'is_active'  => false,
                'admin_note' => DB::raw("CONCAT(COALESCE(admin_note, ''), ' | Lifted: {$sanitizedNote}')"),
            ]);

        Cache::forget(self::CACHE_PREFIX . $userId);

        return $updated;
    }

    /**
     * রেস্ট্রিক্টেড ইউজার রিলেশন
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * রেস্ট্রিকশন প্রদানকারী অ্যাডমিন রিলেশন
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}