<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CommunicationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'phone_number',
        'email',
        'sms_marketing_subscribed',
        'email_marketing_subscribed',
        'transactional_sms_allowed',
        'sms_unsubscribed_at',
        'email_unsubscribed_at',
        'unsubscription_reason',
        'consent_source',
    ];

    protected $casts = [
        'sms_marketing_subscribed'   => 'boolean',
        'email_marketing_subscribed' => 'boolean',
        'transactional_sms_allowed'  => 'boolean',
        'sms_unsubscribed_at'        => 'datetime',
        'email_unsubscribed_at'      => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a phone number can receive marketing SMS
     */
    public static function canReceiveMarketingSms(?string $phone): bool
    {
        if (empty($phone)) {
            return false;
        }

        $pref = static::where('phone_number', $phone)->first();
        if ($pref && !$pref->sms_marketing_subscribed) {
            return false;
        }

        return true;
    }

    /**
     * Check if an email address can receive marketing emails
     */
    public static function canReceiveMarketingEmail(?string $email): bool
    {
        if (empty($email)) {
            return false;
        }

        $pref = static::where('email', $email)->first();
        if ($pref && !$pref->email_marketing_subscribed) {
            return false;
        }

        return true;
    }

    /**
     * Unsubscribe a phone number from SMS marketing without touching email subscription
     */
    public static function unsubscribeSms(string $phone, ?string $reason = null): self
    {
        return static::updateOrCreate(
            ['phone_number' => $phone],
            [
                'sms_marketing_subscribed' => false,
                'sms_unsubscribed_at'        => Carbon::now(),
                'unsubscription_reason'     => $reason,
            ]
        );
    }

    /**
     * Unsubscribe an email address from email marketing without touching SMS subscription
     */
    public static function unsubscribeEmail(string $email, ?string $reason = null): self
    {
        return static::updateOrCreate(
            ['email' => $email],
            [
                'email_marketing_subscribed' => false,
                'email_unsubscribed_at'      => Carbon::now(),
                'unsubscription_reason'      => $reason,
            ]
        );
    }
}