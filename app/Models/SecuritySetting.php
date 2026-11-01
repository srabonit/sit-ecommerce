<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SecuritySetting extends Model
{
    use HasFactory;

    protected $table = 'security_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
        'description',
    ];

    /**
     * ক্যাশে কি প্রিফিক্স ও কনফিগারেশন
     */
    const CACHE_PREFIX = 'security_setting_';
    const CACHE_ALL_KEY = 'security_settings_all_cached';
    const CACHE_TTL_SECONDS = 86400; // ২৪ ঘণ্টা

    /**
     * মডেল সেভ, আপডেট বা ডিলিট হলে স্বয়ংক্রিয় ক্যাশে ক্লিয়ারেন্স
     */
    protected static function booted()
    {
        static::saved(function ($setting) {
            static::flushSettingCache($setting->key);
        });

        static::deleted(function ($setting) {
            static::flushSettingCache($setting->key);
        });
    }

    /**
     * সেন্ট্রাল ক্যাশ ক্লিয়ারেন্স হেল্পার
     */
    public static function flushSettingCache(string $key): void
    {
        Cache::forget(self::CACHE_PREFIX . $key);
        Cache::forget('sec_setting_' . $key);
        Cache::forget(self::CACHE_ALL_KEY);
        Cache::forget('security_settings_all');
    }

    /**
     * ক্যাশে থেকে দ্রুত সেটিং মান রিটার্ন করার সেন্ট্রাল মেথড
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember(self::CACHE_PREFIX . $key, self::CACHE_TTL_SECONDS, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting !== null ? $setting->value : $default;
        });
    }

    /**
     * সমস্ত সেটিংস একবারে ক্যাশ থেকে সংগ্রহ (হাই-পারফরম্যান্স ব্যাচ লোডিং)
     */
    public static function getAllCached(): array
    {
        return Cache::remember(self::CACHE_ALL_KEY, self::CACHE_TTL_SECONDS, function () {
            return static::pluck('value', 'key')->toArray();
        });
    }

    /**
     * বুলিয়ান (Boolean) ফিচারের জন্য মাস্টার টগল ভেরিফিকেশন
     */
    public static function isEnabled(string $key, bool $default = false): bool
    {
        $value = static::get($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'on', 'yes'], true);
    }

    /**
     * ইনটিজার থ্রেশহোল্ড (অর্ডার লিমিট, টাইম উইন্ডো) পাওয়ার মেথড
     */
    public static function getInt(string $key, int $default = 0): int
    {
        $value = static::get($key, $default);
        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * সেটিং মান ডাটাবেসে সেভ/আপডেট করার হেল্পার (Safe Update with Description Protection)
     */
    public static function set(string $key, $value, string $group = 'general', ?string $description = null): self
    {
        $payload = [
            'value' => is_array($value) ? json_encode($value) : (string) $value,
            'group' => $group,
        ];

        // বিবরণ শুধুমাত্র প্রদান করা হলেই আপডেট হবে, নাল হলে আগেরটি অক্ষুণ্ণ থাকবে
        if ($description !== null) {
            $payload['description'] = $description;
        }

        $setting = static::updateOrCreate(['key' => $key], $payload);

        static::flushSettingCache($key);

        return $setting;
    }

    /**
     * গ্রুপ অনুযায়ী সকল সেটিংস লোড করা
     */
    public static function getByGroup(string $group): array
    {
        return static::where('group', $group)->pluck('value', 'key')->toArray();
    }
}