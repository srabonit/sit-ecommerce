<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCampaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'email_campaigns';

    protected $fillable = [
        'name',
        'subject',
        'preview_text',
        'sender_name',
        'sender_email',
        'reply_to',
        'campaign_type',
        'template_id',
        'segment_id',
        'coupon_id',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'batch_size',
        'rate_limit_per_minute',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'failed_count',
        'opened_count',
        'clicked_count',
        'unsubscribed_count',
        'bounced_count',
        'total_revenue',
        'track_opens',
        'track_clicks',
        'utm_campaign',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at'           => 'datetime',
        'started_at'             => 'datetime',
        'completed_at'           => 'datetime',
        'track_opens'            => 'boolean',
        'track_clicks'           => 'boolean',
        'batch_size'             => 'integer',
        'rate_limit_per_minute'  => 'integer',
        'total_recipients'       => 'integer',
        'sent_count'             => 'integer',
        'delivered_count'        => 'integer',
        'failed_count'           => 'integer',
        'opened_count'           => 'integer',
        'clicked_count'          => 'integer',
        'unsubscribed_count'     => 'integer',
        'bounced_count'          => 'integer',
        'total_revenue'          => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes (High-Performance Query Filters)
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Scheduled campaigns that are ready to be dispatched
     */
    public function scopeReadyToSend($query)
    {
        return $query->where('status', 'scheduled')
                     ->whereNotNull('scheduled_at')
                     ->where('scheduled_at', '<=', now());
    }

    /**
     * Scope: Draft campaigns
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope: Active or currently sending campaigns
     */
    public function scopeSending($query)
    {
        return $query->whereIn('status', ['queued', 'sending']);
    }

    /**
     * Scope: Completed campaigns
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Associated Email Template
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    /**
     * Target Audience Segment
     */
    public function segment(): BelongsTo
    {
        return $this->belongsTo(EmailSegment::class, 'segment_id');
    }

    /**
     * Attached Promotional Coupon
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    /**
     * Creator (Admin or Staff User)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Individual recipient queue & delivery status logs
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class, 'campaign_id');
    }

    /**
     * Open, click, and bounce tracking events
     */
    public function trackingEvents(): HasMany
    {
        return $this->hasMany(EmailTrackingEvent::class, 'campaign_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Performance Metric Accessors (Real-time Analytics)
    |--------------------------------------------------------------------------
    */

    /**
     * Open Rate Percentage (Opens / Delivered)
     */
    public function getOpenRateAttribute(): float
    {
        $base = $this->delivered_count > 0 ? $this->delivered_count : $this->sent_count;
        return $base > 0 ? round(($this->opened_count / $base) * 100, 2) : 0.00;
    }

    /**
     * Click Rate Percentage (Clicks / Delivered)
     */
    public function getClickRateAttribute(): float
    {
        $base = $this->delivered_count > 0 ? $this->delivered_count : $this->sent_count;
        return $base > 0 ? round(($this->clicked_count / $base) * 100, 2) : 0.00;
    }

    /**
     * Bounce Rate Percentage (Bounced / Total Sent)
     */
    public function getBounceRateAttribute(): float
    {
        return $this->sent_count > 0 ? round(($this->bounced_count / $this->sent_count) * 100, 2) : 0.00;
    }

    /**
     * Unsubscribe Rate Percentage (Unsubscribed / Total Delivered)
     */
    public function getUnsubscribeRateAttribute(): float
    {
        $base = $this->delivered_count > 0 ? $this->delivered_count : $this->sent_count;
        return $base > 0 ? round(($this->unsubscribed_count / $base) * 100, 2) : 0.00;
    }

    /**
     * Sending Progress Percentage
     */
    public function getProgressPercentageAttribute(): float
    {
        return $this->total_recipients > 0 ? round(($this->sent_count / $this->total_recipients) * 100, 1) : 0.0;
    }

    /*
    |--------------------------------------------------------------------------
    | Workflow Controls & State Checks
    |--------------------------------------------------------------------------
    */

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'scheduled']);
    }

    public function canBePaused(): bool
    {
        return in_array($this->status, ['queued', 'sending']);
    }

    public function canBeResumed(): bool
    {
        return $this->status === 'paused';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'scheduled', 'queued', 'paused', 'sending']);
    }
}