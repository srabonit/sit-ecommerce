<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmsCampaign extends Model
{
    protected $fillable = [
        'name',
        'description',
        'channel',
        'sms_provider_id',
        'sender_id',
        'sender_type',
        'target_audience_type',
        'targeting_filters',
        'sms_message',
        'email_template_id',
        'email_subject',
        'email_content',
        'total_recipients',
        'processed_recipients',
        'successful_count',
        'failed_count',
        'estimated_cost',
        'actual_cost',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'targeting_filters'    => 'array',
        'total_recipients'     => 'integer',
        'processed_recipients' => 'integer',
        'successful_count'     => 'integer',
        'failed_count'         => 'integer',
        'estimated_cost'       => 'decimal:4',
        'actual_cost'          => 'decimal:4',
        'scheduled_at'         => 'datetime',
        'started_at'           => 'datetime',
        'completed_at'         => 'datetime',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(SmsProvider::class, 'sms_provider_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(SmsCampaignRecipient::class, 'campaign_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(SmsLog::class, 'campaign_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    /**
     * Calculate delivery progress percentage
     */
    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_recipients === 0) {
            return 0;
        }

        return (int) min(100, round(($this->processed_recipients / $this->total_recipients) * 100));
    }

    public function hasSms(): bool
    {
        return in_array($this->channel, ['sms_only', 'sms_and_email'], true);
    }

    public function hasEmail(): bool
    {
        return in_array($this->channel, ['email_only', 'sms_and_email'], true);
    }
}