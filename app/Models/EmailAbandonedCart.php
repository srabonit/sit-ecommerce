<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EmailAbandonedCart extends Model
{
    use HasFactory;

    protected $table = 'email_abandoned_carts';

    protected $fillable = [
        'user_id',
        'email',
        'phone',
        'customer_name',
        'cart_items',
        'cart_value',
        'recovery_token',
        'abandoned_at',
        'is_recovered',
        'recovered_at',
        'recovered_order_id',
        'reminder_sent_count',
        'last_reminder_at',
    ];

    protected $casts = [
        'cart_items'          => 'array',
        'cart_value'          => 'decimal:2',
        'is_recovered'        => 'boolean',
        'abandoned_at'        => 'datetime',
        'recovered_at'        => 'datetime',
        'last_reminder_at'    => 'datetime',
        'reminder_sent_count' => 'integer',
    ];

    /**
     * Boot model events
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically assign secure unique 64-character token and abandonment timestamp
        static::creating(function ($cart) {
            if (empty($cart->recovery_token)) {
                $cart->recovery_token = Str::random(64);
            }
            if (empty($cart->abandoned_at)) {
                $cart->abandoned_at = now();
            }
            if (!isset($cart->reminder_sent_count)) {
                $cart->reminder_sent_count = 0;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (High-Performance Query Filters)
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Pending unrecovered carts
     */
    public function scopePendingRecovery($query)
    {
        return $query->where('is_recovered', false);
    }

    /**
     * Scope: Successfully recovered carts
     */
    public function scopeRecovered($query)
    {
        return $query->where('is_recovered', true);
    }

    /**
     * Scope: Ready for automated reminder based on delay in minutes
     * (With spam protection: checks both abandonment delay and interval between reminders)
     */
    public function scopeReadyForReminder($query, int $delayMinutes = 60, int $maxReminders = 3)
    {
        return $query->where('is_recovered', false)
                     ->where('reminder_sent_count', '<', $maxReminders)
                     ->where('abandoned_at', '<=', now()->subMinutes($delayMinutes))
                     ->where(function ($q) use ($delayMinutes) {
                         $q->whereNull('last_reminder_at')
                           ->orWhere('last_reminder_at', '<=', now()->subMinutes($delayMinutes));
                     });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Registered user account (if logged in during abandonment)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Converted Order (when customer completes purchase via recovery link)
     */
    public function recoveredOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'recovered_order_id');
    }

    /**
     * Associated subscriber profile (if consented)
     */
    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(EmailSubscriber::class, 'email', 'email');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Accessors & Actions
    |--------------------------------------------------------------------------
    */

    /**
     * Generate secure cart recovery URL
     */
    public function getRecoveryUrlAttribute(): string
    {
        return url('/cart/recover/' . $this->recovery_token);
    }

    /**
     * Blade alias compatibility: reminders_sent_count / reminder_count
     */
    public function getRemindersSentCountAttribute(): int
    {
        return (int) ($this->reminder_sent_count ?? 0);
    }

    public function getReminderCountAttribute(): int
    {
        return (int) ($this->reminder_sent_count ?? 0);
    }

    /**
     * Blade alias compatibility: last_reminder_sent_at
     */
    public function getLastReminderSentAtAttribute()
    {
        return $this->last_reminder_at;
    }

    /**
     * Total items count in cart
     */
    public function getItemsCountAttribute(): int
    {
        $items = $this->cart_items;
        return is_array($items) ? count($items) : 0;
    }

    /**
     * Formatted cart currency string
     */
    public function getFormattedCartValueAttribute(): string
    {
        return '৳ ' . number_format((float) ($this->cart_value ?? 0), 2);
    }

    /**
     * Mark cart as successfully recovered
     */
    public function markAsRecovered(int $orderId): bool
    {
        return $this->update([
            'is_recovered'       => true,
            'recovered_at'       => now(),
            'recovered_order_id' => $orderId,
        ]);
    }

    /**
     * Increment reminder count and record dispatch timestamp (Null-safe)
     */
    public function recordReminderSent(): bool
    {
        return $this->update([
            'reminder_sent_count' => ($this->reminder_sent_count ?? 0) + 1,
            'last_reminder_at'    => now(),
        ]);
    }
}