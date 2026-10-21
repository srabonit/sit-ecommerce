<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentVerificationHistory extends Model
{
    use HasFactory;

    protected $table = 'payment_verification_histories';

    protected $fillable = [
        'payment_transaction_id',
        'order_id',
        'invoice_no',
        'action_type',
        'old_status',
        'new_status',
        'amount',
        'payment_method',
        'transaction_id',
        'reason',
        'remarks',
        'performed_by',
        'performed_by_name',
        'performed_by_role',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id')->withoutGlobalScopes();
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by')->withoutGlobalScopes();
    }

    // =========================================================================
    // ACCESSORS & HELPERS
    // =========================================================================

    /**
     * Human-readable Action Type Label
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action_type) {
            'created'                => 'Transaction Submitted',
            'initial_migration_sync' => 'System Migration Sync',
            'mark_checked'           => 'Marked as Checked',
            'approve'                => 'Approved Transaction',
            'mark_paid'              => 'Verified & Marked Paid',
            'mark_partially_paid'    => 'Marked Partially Paid',
            'mark_unmatched'         => 'Marked as Unmatched',
            'mark_fake'              => 'Rejected / Marked Fake',
            'under_review'           => 'Moved to Under Review',
            'mark_failed'            => 'Marked as Failed',
            'refund'                 => 'Payment Refunded',
            'note_added'             => 'Internal Note Added',
            'order_sync'             => 'Synced from Order Status',
            'cod_settled'            => 'COD Payment Settled',
            default                  => ucwords(str_replace('_', ' ', (string) $this->action_type)),
        };
    }
}