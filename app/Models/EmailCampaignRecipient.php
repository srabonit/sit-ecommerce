<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EmailCampaignRecipient extends Model
{
    use HasFactory;

    protected $table = 'email_campaign_recipients';

    protected $fillable = [
        'campaign_id',
        'subscriber_id',
        'email',
        'status',
        'tracking_token',
        'sent_at',
        'delivered_at',
        'opened_at',
        'open_count',
        'clicked_at',
        'click_count',
        'error_message',
    ];

    protected $casts = [
        'sent_at'      => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at'    => 'datetime',
        'clicked_at'   => 'datetime',
        'open_count'   => 'integer',
        'click_count'  => 'integer',
    ];

    /**
     * Boot model events
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically generate unique 64-character tracking token
        static::creating(function ($recipient) {
            if (empty($recipient->tracking_token)) {
                $recipient->tracking_token = Str::random(64);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (High-Performance Query Filters)
    |--------------------------------------------------------------------------
    */

    public function scopeQueued($query)
    {
        return $query->where('status', 'queued');
    }

    public function scopeSending($query)
    {
        return $query->where('status', 'sending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['failed', 'bounced']);
    }

    public function scopeOpened($query)
    {
        return $query->where('open_count', '>', 0);
    }

    public function scopeClicked($query)
    {
        return $query->where('click_count', '>', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Parent Campaign
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    /**
     * Associated Subscriber Profile (if exists)
     */
    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(EmailSubscriber::class, 'subscriber_id');
    }

    /**
     * Detailed individual tracking event history
     */
    public function trackingEvents(): HasMany
    {
        return $this->hasMany(EmailTrackingEvent::class, 'recipient_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Status & Metric Transition Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Mark recipient status as sent and sync campaign counter
     */
    public function markAsSent(): bool
    {
        $updated = $this->update([
            'status'  => 'sent',
            'sent_at' => now(),
        ]);

        if ($updated) {
            $this->campaign()->increment('sent_count');
        }

        return $updated;
    }

    /**
     * Mark recipient status as delivered and sync campaign counter
     */
    public function markAsDelivered(): bool
    {
        $updated = $this->update([
            'status'       => 'delivered',
            'delivered_at' => now(),
        ]);

        if ($updated) {
            $this->campaign()->increment('delivered_count');
        }

        return $updated;
    }

    /**
     * Mark recipient status as failed with error log
     */
    public function markAsFailed(string $error): bool
    {
        $updated = $this->update([
            'status'        => 'failed',
            'error_message' => Str::limit($error, 1000),
        ]);

        if ($updated) {
            $this->campaign()->increment('failed_count');
        }

        return $updated;
    }

    /**
     * Record an email open event and sync campaign counter
     */
    public function recordOpen(): void
    {
        $isFirstOpen = ($this->open_count === 0);

        $this->increment('open_count');
        $this->update([
            'opened_at' => $this->opened_at ?? now(),
        ]);

        if ($isFirstOpen) {
            $this->campaign()->increment('opened_count');
        }
    }

    /**
     * Record a link click event and sync campaign counter
     */
    public function recordClick(): void
    {
        $isFirstClick = ($this->click_count === 0);

        $this->increment('click_count');
        $this->update([
            'clicked_at' => $this->clicked_at ?? now(),
        ]);

        if ($isFirstClick) {
            $this->campaign()->increment('clicked_count');
        }
    }
}