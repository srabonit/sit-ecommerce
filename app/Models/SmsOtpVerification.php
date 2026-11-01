<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SmsOtpVerification extends Model
{
    protected $fillable = [
        'phone_number',
        'user_id',
        'otp_hash',
        'provider_otp_id',
        'provider_message_id',
        'mode',
        'purpose',
        'attempts',
        'resend_count',
        'is_verified',
        'ip_address',
        'user_agent',
        'next_resend_allowed_at',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'is_verified'            => 'boolean',
        'attempts'               => 'integer',
        'resend_count'           => 'integer',
        'next_resend_allowed_at' => 'datetime',
        'expires_at'             => 'datetime',
        'verified_at'            => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Set hashed OTP for custom verification flow
     */
    public function setPlainOtp(string $otp): void
    {
        $this->otp_hash = Hash::make(trim($otp));
    }

    /**
     * Verify plain OTP against the stored secure hash
     */
    public function checkOtp(string $plainOtp): bool
    {
        if (empty($this->otp_hash)) {
            return false;
        }

        return Hash::check(trim($plainOtp), $this->otp_hash);
    }

    /**
     * Check if OTP has expired
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return true;
        }

        return Carbon::now()->isAfter($this->expires_at);
    }

    /**
     * Check if a resend request is currently within the cooldown period
     */
    public function isUnderCooldown(): bool
    {
        if (!$this->next_resend_allowed_at) {
            return false;
        }

        return Carbon::now()->isBefore($this->next_resend_allowed_at);
    }

    /**
     * Get remaining cooldown wait time in seconds
     */
    public function getCooldownRemainingSeconds(): int
    {
        if (!$this->isUnderCooldown()) {
            return 0;
        }

        return (int) Carbon::now()->diffInSeconds($this->next_resend_allowed_at, false);
    }

    /**
     * Scope for pending active verifications
     */
    public function scopePending($query)
    {
        return $query->where('is_verified', false)
            ->where('expires_at', '>', Carbon::now());
    }

    /**
     * Scope for verified sessions
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }
}