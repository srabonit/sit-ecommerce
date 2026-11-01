<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class SecurityIpLog extends Model
{
    use HasFactory;

    protected $table = 'security_ip_logs';

    /**
     * লগ টেবিলে updated_at এর প্রয়োজন নেই; শুধুমাত্র created_at ব্যবহৃত হবে
     */
    public $timestamps = false;

    protected $fillable = [
        'ip_address',
        'ip_version',
        'user_id',
        'user_type',
        'session_id',
        'device_type',
        'browser',
        'platform',
        'user_agent',
        'request_method',
        'request_url',
        'url_path',
        'referrer_url',
        'response_status',
        'activity_type',
        'risk_score',
        'security_status',
        'country',
        'city',
        'isp',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'created_at'      => 'datetime',
        'metadata'        => 'array',
        'risk_score'      => 'integer',
        'response_status' => 'integer',
    ];

    /**
     * রিকোয়েস্ট পেলোড এক্সেসর
     * ডেটাবেসে সরাসরি কলাম থাকলে সেটি নেবে, অথবা metadata এর ভেতর থেকে সেফলি এক্সট্র্যাক্ট করবে
     */
    public function getRequestPayloadAttribute()
    {
        if (array_key_exists('request_payload', $this->attributes)) {
            return $this->attributes['request_payload'];
        }

        return $this->metadata['request_payload'] ?? $this->metadata['payload'] ?? null;
    }

    /**
     * স্কোপ: নির্দিষ্ট অ্যাক্টিভিটি টাইপ অনুযায়ী ফিল্টার
     */
    public function scopeActivity($query, string $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * স্কোপ: সাসপিশাস বা রিস্কি রিকোয়েস্ট ফিল্টার
     */
    public function scopeSuspicious($query, int $minRiskScore = 50)
    {
        return $query->where(function ($q) use ($minRiskScore) {
            $q->where('risk_score', '>=', $minRiskScore)
              ->orWhereIn('security_status', ['flagged', 'blocked', 'challenged']);
        });
    }

    /**
     * স্কোপ: নির্দিষ্ট টাইমরেঞ্জ অনুযায়ী ফিল্টার
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ]);
    }

    /**
     * স্কোপ: নির্দিষ্ট আইপির ভেলোসিটি (Velocity) গণনায় সাম্প্রতিক লগ পাওয়া
     */
    public function scopeRecentForIp($query, string $ip, int $minutes = 5)
    {
        return $query->where('ip_address', $ip)
            ->where('created_at', '>=', Carbon::now()->subMinutes($minutes));
    }

    /**
     * নিরাপদ লগ রেকর্ড হেল্পার (Safe Logging Wrapper)
     * কোনো কারণে ডাটাবেস এরর হলেও মূল ট্রানজ্যাকশন/অর্ডার যাতে বন্ধ না হয়
     */
    public static function record(array $data): ?self
    {
        try {
            if (!isset($data['created_at'])) {
                $data['created_at'] = Carbon::now();
            }

            // সেনসিটিভ প্যারামিটার বাদ দেওয়া (Privacy & Security Sanitization)
            if (isset($data['metadata']) && is_array($data['metadata'])) {
                $sensitiveKeys = ['password', 'password_confirmation', 'cvv', 'card_number', 'token', 'otp'];
                foreach ($sensitiveKeys as $key) {
                    unset($data['metadata'][$key]);
                }
            }

            return static::create($data);
        } catch (\Throwable $e) {
            Log::channel('single')->error('Security Logging Failure: ' . $e->getMessage(), [
                'ip'       => $data['ip_address'] ?? 'unknown',
                'activity' => $data['activity_type'] ?? 'unknown',
            ]);
            return null;
        }
    }

    /**
     * ইউজার রিলেশন
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}