<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailConsentLog extends Model
{
    use HasFactory;

    protected $table = 'email_consent_logs';

    // Immutable audit table: only created_at is tracked
    public $timestamps = false;

    protected $fillable = [
        'subscriber_id',
        'email',
        'action',
        'source',
        'ip_address',
        'user_agent',
        'payload',
        'created_at',
    ];

    protected $casts = [
        'payload'    => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Boot model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($log) {
            if (empty($log->created_at)) {
                $log->created_at = now();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (High-Performance Query Filters)
    |--------------------------------------------------------------------------
    */

    public function scopeOptIns($query)
    {
        return $query->where('action', 'opt_in');
    }

    public function scopeOptOuts($query)
    {
        return $query->where('action', 'opt_out');
    }

    public function scopeResubscribed($query)
    {
        return $query->where('action', 'resubscribe');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Associated Subscriber
     */
    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(EmailSubscriber::class, 'subscriber_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Method for Quick Consent Logging
    |--------------------------------------------------------------------------
    */

    /**
     * Record a verified consent audit log entry
     */
    public static function logConsent(
        string $email,
        string $action,
        string $source = 'checkout',
        ?int $subscriberId = null,
        array $payload = []
    ): self {
        return self::create([
            'subscriber_id' => $subscriberId,
            'email'         => strtolower(trim($email)),
            'action'        => $action,
            'source'        => $source,
            'ip_address'    => request()->ip(),
            'user_agent'    => request()->userAgent(),
            'payload'       => $payload,
            'created_at'    => now(),
        ]);
    }
}