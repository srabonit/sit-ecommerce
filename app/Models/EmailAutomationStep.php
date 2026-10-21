<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailAutomationStep extends Model
{
    use HasFactory;

    protected $table = 'email_automation_steps';

    protected $fillable = [
        'automation_id',
        'step_order',
        'step_type',
        'delay_minutes',
        'template_id',
        'subject',
        'preview_text',
        'coupon_id',
        'step_conditions',
    ];

    protected $casts = [
        'step_order'      => 'integer',
        'delay_minutes'   => 'integer',
        'step_conditions' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes (High-Performance Query Filters)
    |--------------------------------------------------------------------------
    */

    public function scopeEmailsOnly($query)
    {
        return $query->where('step_type', 'send_email');
    }

    public function scopeDelaysOnly($query)
    {
        return $query->where('step_type', 'delay');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('step_order', 'asc');
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
     * Associated Email Template (for send_email steps)
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    /**
     * Attached Promotional Coupon (if any)
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    /**
     * Execution and anti-duplicate idempotency logs for this step
     */
    public function logs(): HasMany
    {
        return $this->hasMany(EmailAutomationLog::class, 'step_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods & Checks
    |--------------------------------------------------------------------------
    */

    public function isEmailStep(): bool
    {
        return $this->step_type === 'send_email';
    }

    public function isDelayStep(): bool
    {
        return $this->step_type === 'delay';
    }

    public function isConditionStep(): bool
    {
        return $this->step_type === 'condition_check';
    }

    /**
     * Format delay time into readable human text
     */
    public function getFormattedDelayAttribute(): string
    {
        if ($this->delay_minutes <= 0) {
            return 'Immediately';
        }

        if ($this->delay_minutes < 60) {
            return "{$this->delay_minutes} Minutes";
        }

        if ($this->delay_minutes < 1440) {
            $hours = round($this->delay_minutes / 60, 1);
            return "{$hours} Hours";
        }

        $days = round($this->delay_minutes / 1440, 1);
        return "{$days} Days";
    }
}