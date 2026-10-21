<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailAutomation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'email_automations';

    protected $fillable = [
        'name',
        'description',
        'trigger_event',
        'trigger_conditions',
        'is_active',
        'total_triggered',
        'total_completed',
        'created_by',
    ];

    protected $casts = [
        'trigger_conditions' => 'array',
        'is_active'          => 'boolean',
        'total_triggered'    => 'integer',
        'total_completed'    => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes (High-Performance Query Filters)
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Only active automations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by specific trigger event
     */
    public function scopeByTrigger($query, string $event)
    {
        return $query->where('trigger_event', $event);
    }

    /**
     * Scope: Ready to run for an incoming trigger event
     */
    public function scopeForEvent($query, string $event)
    {
        return $query->active()->byTrigger($event);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Sequential execution steps of this automation
     */
    public function steps(): HasMany
    {
        return $this->hasMany(EmailAutomationStep::class, 'automation_id')->orderBy('step_order');
    }

    /**
     * Execution and anti-duplicate idempotency logs
     */
    public function logs(): HasMany
    {
        return $this->hasMany(EmailAutomationLog::class, 'automation_id');
    }

    /**
     * Associated creator (Admin/Staff User)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Tracking events originating from this automation
     */
    public function trackingEvents(): HasMany
    {
        return $this->hasMany(EmailTrackingEvent::class, 'automation_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods & Triggers
    |--------------------------------------------------------------------------
    */

    /**
     * Increment triggered counter
     */
    public function recordTriggered(): void
    {
        $this->increment('total_triggered');
    }

    /**
     * Increment completed workflow counter
     */
    public function recordCompleted(): void
    {
        $this->increment('total_completed');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(): bool
    {
        $this->is_active = !$this->is_active;
        return $this->save();
    }

    /**
     * Human-readable label for trigger events
     */
    public function getTriggerLabelAttribute(): string
    {
        return match ($this->trigger_event) {
            'customer_registered'   => 'Customer Registration (Welcome)',
            'order_placed'          => 'Order Placed (Confirmation)',
            'order_delivered'       => 'Order Delivered (Review Request)',
            'cart_abandoned'        => 'Cart Abandoned (Recovery)',
            'wishlist_added'        => 'Item Added to Wishlist',
            'product_published'     => 'New Product Announcement',
            'product_back_in_stock' => 'Product Back in Stock',
            'birthday'              => 'Customer Birthday Greeting',
            'inactive_days'         => 'Customer Win-Back (Inactive)',
            'custom_webhook'        => 'Custom Webhook Event',
            default                 => ucwords(str_replace('_', ' ', $this->trigger_event)),
        };
    }
}