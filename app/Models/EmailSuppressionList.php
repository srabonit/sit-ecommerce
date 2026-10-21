<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailSuppressionList extends Model
{
    use HasFactory;

    protected $table = 'email_suppression_list';

    protected $fillable = [
        'email',
        'reason',
        'details',
        'blocked_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes (High-Performance Query Filters)
    |--------------------------------------------------------------------------
    */

    public function scopeHardBounces($query)
    {
        return $query->where('reason', 'hard_bounce');
    }

    public function scopeSpamComplaints($query)
    {
        return $query->where('reason', 'spam_complaint');
    }

    public function scopeManualBlocks($query)
    {
        return $query->where('reason', 'manual_block');
    }

    public function scopeUnsubscribes($query)
    {
        return $query->where('reason', 'unsubscribe');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Admin/Staff user who manually blocked this email (if applicable)
     */
    public function blockedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    /**
     * Linked subscriber record (if exists)
     */
    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(EmailSubscriber::class, 'email', 'email');
    }

    /*
    |--------------------------------------------------------------------------
    | Static Helpers & Safeguard Logic
    |--------------------------------------------------------------------------
    */

    /**
     * Check if a given email is suppressed/blacklisted
     */
    public static function isSuppressed(string $email): bool
    {
        $normalized = strtolower(trim($email));
        return self::where('email', $normalized)->exists();
    }

    /**
     * Add an email to the suppression list safely (Idempotent)
     */
    public static function suppress(
        string $email,
        string $reason = 'hard_bounce',
        ?string $details = null,
        ?int $blockedBy = null
    ): self {
        $normalized = strtolower(trim($email));

        // Also update subscriber status if present in system
        EmailSubscriber::where('email', $normalized)->update([
            'status'            => in_array($reason, ['hard_bounce', 'soft_bounce_limit']) ? 'bounced' : 'complained',
            'marketing_consent' => false,
        ]);

        return self::updateOrCreate(
            ['email' => $normalized],
            [
                'reason'     => $reason,
                'details'    => $details,
                'blocked_by' => $blockedBy,
            ]
        );
    }

    /**
     * Remove an email from suppression list
     */
    public static function unsuppress(string $email): bool
    {
        $normalized = strtolower(trim($email));
        return (bool) self::where('email', $normalized)->delete();
    }
}