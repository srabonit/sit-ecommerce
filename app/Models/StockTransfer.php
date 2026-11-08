<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    use HasFactory;

    protected $table = 'stock_transfers';

    protected $fillable = [
        'transfer_no',
        'from_branch_id',
        'from_warehouse_id',
        'to_branch_id',
        'to_warehouse_id',
        'created_by',
        'approved_by',
        'dispatched_by',
        'received_by',
        'status', // draft, pending_approval, approved, dispatched, partially_received, received, rejected, cancelled
        'shipping_cost',
        'tracking_reference',
        'rejection_reason',
        'approved_at',
        'dispatched_at',
        'received_at',
        'cancelled_at',
        'notes',
    ];

    protected $casts = [
        'from_branch_id'    => 'integer',
        'from_warehouse_id' => 'integer',
        'to_branch_id'      => 'integer',
        'to_warehouse_id'   => 'integer',
        'created_by'        => 'integer',
        'approved_by'       => 'integer',
        'dispatched_by'     => 'integer',
        'received_by'       => 'integer',
        'shipping_cost'     => 'decimal:2',
        'approved_at'       => 'datetime',
        'dispatched_at'     => 'datetime',
        'received_at'       => 'datetime',
        'cancelled_at'      => 'datetime',
    ];

    // =========================================================================
    // 1. EXISTING ORIGINAL RELATIONSHIPS (PRESERVED 100%)
    // =========================================================================

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class, 'stock_transfer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // =========================================================================
    // 2. ADVANCED MULTI-BRANCH & WAREHOUSE RELATIONSHIPS
    // =========================================================================

    /**
     * Source branch from where stock is dispatched.
     */
    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    /**
     * Destination branch where stock will be received.
     */
    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    /**
     * Source warehouse.
     */
    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    /**
     * Destination warehouse.
     */
    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    /**
     * Staff/Admin who approved the requisition.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Staff who physically dispatched the goods.
     */
    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    /**
     * Staff who received and verified the goods at the destination branch.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // =========================================================================
    // 3. TRANSFER STATUS & WORKFLOW HELPERS
    // =========================================================================

    /**
     * Check if goods are currently dispatched and moving between branches.
     */
    public function isInTransit(): bool
    {
        return in_array(strtolower((string)$this->status), ['dispatched', 'in_transit']);
    }

    /**
     * Check if transfer lifecycle is closed/received.
     */
    public function isCompleted(): bool
    {
        return in_array(strtolower((string)$this->status), ['received', 'partially_received']);
    }

    /**
     * Check if requisition is waiting for manager approval.
     */
    public function isPendingApproval(): bool
    {
        return strtolower((string)$this->status) === 'pending_approval';
    }

    /**
     * Check if transfer can still be modified or cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array(strtolower((string)$this->status), ['draft', 'pending_approval', 'approved']);
    }

    // =========================================================================
    // 4. QUERY SCOPES
    // =========================================================================

    /**
     * Scope query for all transfers involving a specific branch (source or destination).
     */
    public function scopeForBranch($query, int $branchId)
    {
        return $query->where(function ($q) use ($branchId) {
            $q->where('from_branch_id', $branchId)
              ->orWhere('to_branch_id', $branchId);
        });
    }

    /**
     * Scope query for transfers currently in transit.
     */
    public function scopeInTransit($query)
    {
        return $query->whereIn('status', ['dispatched', 'in_transit']);
    }

    /**
     * Scope query for transfers awaiting manager approval.
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }
}