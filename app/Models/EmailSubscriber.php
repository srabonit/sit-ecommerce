<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EmailSubscriber extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'email_subscribers';

    protected $fillable = [
        'user_id',
        'email',
        'first_name',
        'last_name',
        'phone',
        'status',
        'marketing_consent',
        'consent_at',
        'consent_source',
        'consent_ip',
        'unsubscribe_token',
        'unsubscribed_at',
        'unsubscribe_reason',
    ];

    protected $casts = [
        'marketing_consent' => 'boolean',
        'consent_at'        => 'datetime',
        'unsubscribed_at'   => 'datetime',
    ];

    /**
     * Boot model events
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically assign a secure 64-character unsubscribe token on creation
        static::creating(function ($subscriber) {
            if (empty($subscriber->unsubscribe_token)) {
                $subscriber->unsubscribe_token = Str::random(64);
            }
            if (empty($subscriber->consent_at) && $subscriber->marketing_consent) {
                $subscriber->consent_at = now();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (High-Performance Query Filters)
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Only active, consented subscribers
     */
    public function scopeSubscribed($query)
    {
        return $query->where('status', 'subscribed')
                     ->where('marketing_consent', true);
    }

    /**
     * Scope: Eligible for marketing (Not suppressed, consented, and active)
     */
    public function scopeEligibleForMarketing($query)
    {
        return $query->subscribed()
                     ->whereNotExists(function ($subQuery) {
                         $subQuery->selectRaw(1)
                                  ->from('email_suppression_list')
                                  ->whereColumn('email_suppression_list.email', 'email_subscribers.email');
                     });
    }

    /**
     * Scope: Registered e-commerce users only
     */
    public function scopeRegisteredUsers($query)
    {
        return $query->whereNotNull('user_id');
    }

    /**
     * Scope: Guest subscribers only
     */
    public function scopeGuests($query)
    {
        return $query->whereNull('user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Linked registered user account (if any)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Historical consent audit logs
     */
    public function consentLogs(): HasMany
    {
        return $this->hasMany(EmailConsentLog::class, 'subscriber_id');
    }

    /**
     * Campaign recipients history
     */
    public function campaignDeliveries(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class, 'subscriber_id');
    }

    /**
     * Automation trigger logs for this subscriber
     */
    public function automationLogs(): HasMany
    {
        return $this->hasMany(EmailAutomationLog::class, 'subscriber_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers & Business Logic
    |--------------------------------------------------------------------------
    */

    /**
     * Get full name or email fallback
     */
    public function getFullNameAttribute(): string
    {
        $fullName = trim("{$this->first_name} {$this->last_name}");
        return $fullName !== '' ? $fullName : ($this->user->name ?? explode('@', $this->email)[0]);
    }

    /**
     * Check if subscriber is active and eligible
     */
    public function isEligible(): bool
    {
        return $this->status === 'subscribed' && $this->marketing_consent;
    }

    /**
     * Unsubscribe this subscriber cleanly
     */
    public function unsubscribe(?string $reason = null): bool
    {
        return $this->update([
            'status'             => 'unsubscribed',
            'marketing_consent'  => false,
            'unsubscribed_at'    => now(),
            'unsubscribe_reason' => $reason,
        ]);
    }

    /**
     * Re-subscribe this subscriber
     */
    public function resubscribe(string $source = 'user_action'): bool
    {
        return $this->update([
            'status'             => 'subscribed',
            'marketing_consent'  => true,
            'consent_at'         => now(),
            'consent_source'     => $source,
            'unsubscribed_at'    => null,
            'unsubscribe_reason' => null,
        ]);
    }
}