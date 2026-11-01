<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsBlacklist extends Model
{
    protected $fillable = [
        'type',
        'value',
        'reason',
        'blocked_by',
    ];

    public function blocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    public function scopePhone($query)
    {
        return $query->where('type', 'phone');
    }

    public function scopeIp($query)
    {
        return $query->where('type', 'ip');
    }

    public function scopeWord($query)
    {
        return $query->where('type', 'word');
    }

    /**
     * Check if a specific target value is blacklisted under given type
     */
    public static function isBlocked(string $type, string $value): bool
    {
        return static::where('type', $type)
            ->where('value', trim($value))
            ->exists();
    }

    /**
     * Check if a phone number is blacklisted
     */
    public static function isPhoneBlocked(string $phone): bool
    {
        return static::isBlocked('phone', $phone);
    }

    /**
     * Check if an IP address is blacklisted
     */
    public static function isIpBlocked(string $ip): bool
    {
        return static::isBlocked('ip', $ip);
    }

    /**
     * Scan message text against all blacklisted spam words
     */
    public static function containsSpamWords(string $text): bool
    {
        $words = static::word()->pluck('value');
        foreach ($words as $word) {
            if (!empty($word) && stripos($text, $word) !== false) {
                return true;
            }
        }
        return false;
    }
}