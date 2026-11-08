<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseReceipt extends Model
{
    use HasFactory;

    protected $table = 'purchase_receipts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'receipt_number',
        'purchase_order_id',
        'purchase_id',
        'supplier_id',
        'branch_id',
        'warehouse_id',
        'supplier_challan_no',
        'challan_no',
        'receiving_date',
        'receipt_date',
        'receiving_status',
        'status',
        'received_by',
        'inspected_by',
        'vehicle_number',
        'inspection_notes',
        'notes',
        'attachment_file',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'receiving_date' => 'date',
        'receipt_date'   => 'date',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    /* =========================================================================
     | RELATIONSHIPS
     ========================================================================= */

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'purchase_receipt_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReceiptItem::class, 'purchase_receipt_id');
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'reference_id')
            ->where('reference_type', 'purchase_receipt');
    }

    /* =========================================================================
     | SCOPES & ACCESSORS
     ========================================================================= */

    public function scopeAccepted($query)
    {
        return $query->whereIn('receiving_status', ['accepted', 'partially_accepted', 'received']);
    }

    public function scopeUnderInspection($query)
    {
        return $query->whereIn('receiving_status', ['under_inspection', 'pending_qc']);
    }

    public function getTotalDeliveredQuantityAttribute(): float
    {
        return (float) $this->items()->sum('received_quantity');
    }

    public function getTotalAcceptedQuantityAttribute(): float
    {
        return (float) $this->items()->sum('accepted_quantity');
    }

    public function getTotalRejectedQuantityAttribute(): float
    {
        return (float) $this->items()->sum('rejected_quantity');
    }
}