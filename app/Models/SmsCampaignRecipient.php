<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsCampaignRecipient extends Model
{
    protected $fillable = [
        'campaign_id',
        'customer_id',
        'phone_number',
        'email',
        'personalized_sms',
        'sms_message_id',
        'sms_status',
        'email_status',
        'failure_reason',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SmsCampaign::class, 'campaign_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function scopePendingSms($query)
    {
        return $query->where('sms_status', 'pending');
    }

    public function scopePendingEmail($query)
    {
        return $query->where('email_status', 'pending');
    }

    public function scopeDeliveredSms($query)
    {
        return $query->where('sms_status', 'delivered');
    }

    public function scopeFailedSms($query)
    {
        return $query->where('sms_status', 'failed');
    }
}