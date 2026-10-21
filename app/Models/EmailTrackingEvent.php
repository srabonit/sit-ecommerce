<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTrackingEvent extends Model
{
    use HasFactory;

    protected $table = 'email_tracking_events';

    // Since tracking events only record creation timestamp
    const UPDATED_AT = null;

    protected $fillable = [
        'campaign_id',
        'automation_id',
        'recipient_id',
        'email',
        'event_type',
        'target_url',
        'ip_address',
        'user_agent',
        'event_meta',
        'created_at',
    ];

    protected $casts = [
        'event_meta' => 'array',
        'created_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes (High-Performance Query Filters)
    |--------------------------------------------------------------------------
    */

    public function scopeOpens($query)
    {
        return $query->where('event_type', 'open');
    }

    public function scopeClicks($query)
    {
        return $query->where('event_type', 'click');
    }

    public function scopeBounces($query)
    {
        return $query->where('event_type', 'bounce');
    }

    public function scopeComplaints($query)
    {
        return $query->where('event_type', 'complaint');
    }

    public function scopeUnsubscribes($query)
    {
        return $query->where('event_type', 'unsubscribe');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Associated Campaign
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    /**
     * Associated Automation Workflow
     */
    public function automation(): BelongsTo
    {
        return $this->belongsTo(EmailAutomation::class, 'automation_id');
    }

    /**
     * Associated Specific Campaign Recipient
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(EmailCampaignRecipient::class, 'recipient_id');
    }

    /**
     * Linked Subscriber (Matched by Email)
     */
    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(EmailSubscriber::class, 'email', 'email');
    }

    /*
    |--------------------------------------------------------------------------
    | Factory Helper for Quick Event Logging
    |--------------------------------------------------------------------------
    */

    /**
     * Log a fast tracking event
     */
    public static function logEvent(
        string $eventType,
        string $email,
        array $attributes = []
    ): self {
        return self::create(array_merge([
            'event_type' => $eventType,
            'email'      => strtolower(trim($email)),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ], $attributes));
    }
}