<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EmailAutomationLog extends Model
{
    use HasFactory;

    protected $table = 'email_automation_logs';

    protected $fillable = [
        'automation_id',
        'step_id',
        'subscriber_id',
        'email',
        'order_id',
        'abandoned_cart_id',
        'idempotency_key',
        'status',
        'error_message',
        'executed_at',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes (High-Performance Query Filters)
    |--------------------------------------------------------------------------
    */

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeSkipped($query)
    {
        return $query->where('status', 'skipped');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Parent Automation Workflow
     */
    public function automation(): BelongsTo
    {
        return $this->belongsTo(EmailAutomation::class, 'automation_id');
    }

    /**
     * Associated Workflow Step
     */
    public function step(): BelongsTo
    {
        return $this->belongsTo(EmailAutomationStep::class, 'step_id');
    }

    /**
     * Associated Subscriber
     */
    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(EmailSubscriber::class, 'subscriber_id');
    }

    /**
     * Associated Order (if triggered by purchase/delivery)
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Associated Abandoned Cart (if triggered by cart abandonment)
     */
    public function abandonedCart(): BelongsTo
    {
        return $this->belongsTo(EmailAbandonedCart::class, 'abandoned_cart_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Static Generator & State Transition Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Generate standard cryptographic idempotency key
     */
    public static function generateKey(int $automationId, int $stepId, string $email, ?string $context = null): string
    {
        $normalizedEmail = strtolower(trim($email));
        $contextString = $context ?? 'general';
        return hash('sha256', "{$automationId}_{$stepId}_{$normalizedEmail}_{$contextString}");
    }

    /**
     * Mark log entry as successfully sent
     */
    public function markAsSent(): bool
    {
        return $this->update([
            'status'      => 'sent',
            'executed_at' => now(),
        ]);
    }

    /**
     * Mark log entry as failed with an error description
     */
    public function markAsFailed(string $error): bool
    {
        return $this->update([
            'status'        => 'failed',
            'error_message' => Str::limit($error, 1000),
            'executed_at'   => now(),
        ]);
    }

    /**
     * Mark log entry as skipped (order already completed or consent missing)
     */
    public function markAsSkipped(?string $reason = null): bool
    {
        return $this->update([
            'status'        => 'skipped',
            'error_message' => $reason ? Str::limit($reason, 1000) : null,
            'executed_at'   => now(),
        ]);
    }
}