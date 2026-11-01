<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SmsSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
    ];

    /**
     * Cache duration in seconds (24 hours)
     */
    protected const CACHE_TTL = 86400;

    /**
     * Model booted lifecycle hooks to guarantee cache auto-invalidation
     * regardless of whether setVal(), save(), or updateOrCreate() is used.
     */
    protected static function booted(): void
    {
        static::saved(function (self $setting) {
            static::clearCacheForKey($setting->key, $setting->group ?? null);
        });

        static::deleted(function (self $setting) {
            static::clearCacheForKey($setting->key, $setting->group ?? null);
        });
    }

    /**
     * Clear all cached instances of a setting
     */
    protected static function clearCacheForKey(string $key, ?string $group = null): void
    {
        Cache::forget("sms_setting_{$key}");
        Cache::forget('sms_settings_all');

        if (!empty($group)) {
            Cache::forget("sms_settings_group_{$group}");
        }
    }

    /**
     * Retrieve a setting value with in-memory caching and accurate type casting
     */
    public static function getVal(string $key, mixed $default = null): mixed
    {
        return Cache::remember("sms_setting_{$key}", self::CACHE_TTL, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            if (!$setting) {
                return $default;
            }

            $val = $setting->value;
            $type = $setting->type ?? null;

            // Strict type conversions
            if ($type === 'boolean' || in_array(strtolower((string) $val), ['true', 'false'], true)) {
                return filter_var($val, FILTER_VALIDATE_BOOLEAN);
            }

            if ($type === 'integer') {
                return (int) $val;
            }

            if ($type === 'decimal' || $type === 'float') {
                return (float) $val;
            }

            if ($type === 'json') {
                return is_string($val) ? (json_decode($val, true) ?? $default) : $val;
            }

            return $val;
        });
    }

    /**
     * Update or create a setting, safe against missing schema columns
     */
    public static function setVal(string $key, mixed $value, string $group = 'general', string $type = 'string'): self
    {
        $storedValue = match ($type) {
            'boolean' => ($value === true || $value === 1 || $value === '1' || $value === 'true' || $value === 'on') ? '1' : '0',
            'json'    => is_array($value) ? json_encode($value) : $value,
            default   => (string) $value,
        };

        $payload = ['value' => $storedValue];

        if (Schema::hasColumn('sms_settings', 'group')) {
            $payload['group'] = $group;
        }

        if (Schema::hasColumn('sms_settings', 'type')) {
            $payload['type'] = $type;
        }

        $setting = self::updateOrCreate(['key' => $key], $payload);

        static::clearCacheForKey($key, $group);

        return $setting;
    }

    /**
     * Check if a specific feature toggle is enabled (returns strict boolean)
     */
    public static function isEnabled(string $key, bool $default = false): bool
    {
        $val = self::getVal($key, null);

        if ($val === null) {
            return $default;
        }

        if (is_bool($val)) {
            return $val;
        }

        return in_array(strtolower(trim((string) $val)), ['1', 'true', 'on', 'yes'], true);
    }

    /**
     * Get all settings as a key-value array with caching
     */
    public static function getAll(): array
    {
        return Cache::remember('sms_settings_all', self::CACHE_TTL, function () {
            return self::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Get all settings under a specific group
     */
    public static function getByGroup(string $group): array
    {
        return Cache::remember("sms_settings_group_{$group}", self::CACHE_TTL, function () use ($group) {
            return self::where('group', $group)->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Flush all SMS related settings cache manually
     */
    public static function flushCache(): void
    {
        Cache::forget('sms_settings_all');
        $keys = self::pluck('key')->toArray();
        foreach ($keys as $k) {
            Cache::forget("sms_setting_{$k}");
        }
    }
}