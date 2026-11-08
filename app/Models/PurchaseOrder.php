<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

class PurchaseOrder extends Model
{
    use HasFactory, Auditable;

    protected $table = 'purchase_orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'po_number',
        'supplier_id',
        'branch_id',
        'warehouse_id',
        'order_date',
        'expected_delivery_date',
        'reference_no',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'shipping_cost',
        'total_amount',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'cancelled_by',
        'cancelled_at',
        'terms_conditions',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'order_date'             => 'date',
        'expected_delivery_date' => 'date',
        'approved_at'            => 'datetime',
        'cancelled_at'           => 'datetime',
        'subtotal'               => 'decimal:2',
        'discount_amount'        => 'decimal:2',
        'tax_amount'             => 'decimal:2',
        'shipping_cost'          => 'decimal:2',
        'total_amount'           => 'decimal:2',
        'created_at'             => 'datetime',
        'updated_at'             => 'datetime',
    ];

    /* =========================================================================
     | RELATIONSHIPS
     ========================================================================= */

    /**
     * Supplier to whom PO was issued
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Receiving Branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Receiving Central Warehouse
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * PO Creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * PO Approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * User who cancelled the PO
     */
    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Line items under this PO
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    /**
     * Generated Purchase Bills / Invoices against this PO
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'purchase_order_id');
    }

    /**
     * Goods Received Notes (GRN) received against this PO
     */
    public function receipts(): HasMany
    {
        return $this->hasMany(PurchaseReceipt::class, 'purchase_order_id');
    }

    /* =========================================================================
     | SCOPES & HELPERS
     ========================================================================= */

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeSearch($query, $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('po_number', 'LIKE', "%{$term}%")
              ->orWhere('reference_no', 'LIKE', "%{$term}%")
              ->orWhereHas('supplier', function ($sq) use ($term) {
                  $sq->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('company_name', 'LIKE', "%{$term}%");
              });
        });
    }

    /**
     * Calculate overall receiving completion percentage
     */
    public function getReceivedPercentageAttribute(): float
    {
        $totalOrdered = (float) $this->items()->sum('quantity');
        if ($totalOrdered <= 0) {
            return 0.0;
        }

        $totalReceived = (float) $this->items()->sum('received_quantity');
        return round(min(100, ($totalReceived / $totalOrdered) * 100), 2);
    }
}