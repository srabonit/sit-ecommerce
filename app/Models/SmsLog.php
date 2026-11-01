<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsLog extends Model
{
    protected $fillable = [
        'recipient',
        'user_id',
        'order_id',
        'campaign_id',
        'sms_type',
        'provider',
        'sender_id',
        'message_id',
        'message_content',
        'characters_count',
        'sms_parts',
        'cost',
        'status',
        'status_code',
        'gateway_response',
        'is_sandbox',
        'sent_at',
        'delivered_at',
    ];

    protected $casts = [
        'is_sandbox'       => 'boolean',
        'characters_count' => 'integer',
        'sms_parts'        => 'integer',
        'cost'             => 'decimal:4',
        'sent_at'          => 'datetime',
        'delivered_at'     => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SmsCampaign::class, 'campaign_id');
    }

    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', ['sent', 'delivered']);
    }

    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['failed', 'rejected', 'blocked', 'spam']);
    }

    public function scopeTransactional($query)
    {
        return $query->where('sms_type', 'transactional');
    }

    public function scopeOtp($query)
    {
        return $query->where('sms_type', 'otp');
    }

    public function scopeMarketing($query)
    {
        return $query->where('sms_type', 'marketing');
    }
}