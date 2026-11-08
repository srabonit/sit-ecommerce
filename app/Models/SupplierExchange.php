<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierExchange extends Model
{
    use HasFactory;

    protected $table = 'supplier_exchanges';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'exchange_number',
        'supplier_id',
        'purchase_id',
        'purchase_return_id',
        'branch_id',
        'warehouse_id',
        'exchange_date',
        'reference_no',
        'status',
        
        // Outward valuation fields (Supports both names)
        'outward_total',
        'returned_total_value',
        
        // Inward valuation fields (Supports both names)
        'inward_total',
        'replacement_total_value',
        
        // Differential fields (Supports both names)
        'differential_amount',
        'price_difference',
        
        // Settlement fields (Supports both names)
        'settlement_type',
        'difference_settlement',
        'bank_account_id',
        
        'attachment_file',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
        'cancelled_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'exchange_date'           => 'date',
        'approved_at'             => 'datetime',
        'outward_total'           => 'decimal:2',
        'returned_total_value'    => 'decimal:2',
        'inward_total'            => 'decimal:2',
        'replacement_total_value' => 'decimal:2',
        'differential_amount'     => 'decimal:2',
        'price_difference'        => 'decimal:2',
        'created_at'              => 'datetime',
        'updated_at'              => 'datetime',
    ];

    /* =========================================================================
     | RELATIONSHIPS
     ========================================================================= */

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class, 'purchase_return_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierExchangeItem::class, 'supplier_exchange_id');
    }

    /**
     * Outward dispatched items
     */
    public function outwardItems(): HasMany
    {
        return $this->hasMany(SupplierExchangeItem::class, 'supplier_exchange_id')
            ->whereIn('item_type', ['outward', 'returned_out']);
    }

    /**
     * Inward replacement items
     */
    public function inwardItems(): HasMany
    {
        return $this->hasMany(SupplierExchangeItem::class, 'supplier_exchange_id')
            ->whereIn('item_type', ['inward', 'replacement_in']);
    }

    /**
     * Aliases for backward compatibility
     */
    public function returnedItems(): HasMany
    {
        return $this->outwardItems();
    }

    public function replacementItems(): HasMany
    {
        return $this->inwardItems();
    }

    /* =========================================================================
     | SCOPES & ACCESSORS
     ========================================================================= */

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'requested', 'sent_to_supplier']);
    }

    public function getNetDifferentialAttribute(): float
    {
        $inward  = (float) ($this->inward_total > 0 ? $this->inward_total : $this->replacement_total_value);
        $outward = (float) ($this->outward_total > 0 ? $this->outward_total : $this->returned_total_value);
        return round($inward - $outward, 2);
    }
}