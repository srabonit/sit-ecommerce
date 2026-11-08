<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $table = 'inventory_movements';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Existing Product & Movement Core
        'product_id',
        'variant_id',
        'sku',
        'movement_type',
        'quantity_before',
        'quantity_change',
        'quantity_after',
        'unit_cost',
        'unit_price',
        'reference_id',
        'source',
        'created_by',
        'notes',

        // Supplier & Procurement Linkages (Phase 1 Enhancements)
        'supplier_id',
        'purchase_id',
        'purchase_item_id',
        'purchase_receipt_id',
        'purchase_return_id',
        'supplier_exchange_id',

        // Multi-location & Traceability
        'branch_id',
        'warehouse_id',
        'receiving_reference',
        'batch_no',
        'expiry_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity_before' => 'integer',
        'quantity_change' => 'integer',
        'quantity_after'  => 'integer',
        'unit_cost'       => 'decimal:2',
        'unit_price'      => 'decimal:2',
        'expiry_date'     => 'date',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'is_deduction',
        'is_addition',
        'total_cost_impact',
    ];

    /* =========================================================================
     | RELATIONSHIPS (EXISTING & PROCUREMENT INTEGRATED)
     ========================================================================= */

    /**
     * Associated Parent Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Associated Product Variant (if applicable)
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Admin/Staff who initiated this stock movement
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Associated Supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Associated Purchase Invoice / Bill
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    /**
     * Specific Purchase Item line
     */
    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class, 'purchase_item_id');
    }

    /**
     * Associated Goods Received Note (GRN)
     */
    public function purchaseReceipt(): BelongsTo
    {
        return $this->belongsTo(PurchaseReceipt::class, 'purchase_receipt_id');
    }

    /**
     * Associated Purchase Return
     */
    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class, 'purchase_return_id');
    }

    /**
     * Associated Supplier Exchange
     */
    public function supplierExchange(): BelongsTo
    {
        return $this->belongsTo(SupplierExchange::class, 'supplier_exchange_id');
    }

    /**
     * Target Branch Location
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Target Warehouse Location
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /* =========================================================================
     | ACCESSORS
     ========================================================================= */

    /**
     * Check if this movement reduced stock (sale, return to supplier, damage)
     */
    public function getIsDeductionAttribute(): bool
    {
        return (int) $this->quantity_change < 0;
    }

    /**
     * Check if this movement increased stock (purchase receive, customer return)
     */
    public function getIsAdditionAttribute(): bool
    {
        return (int) $this->quantity_change > 0;
    }

    /**
     * Total cost valuation impact of this specific movement
     */
    public function getTotalCostImpactAttribute(): float
    {
        return round(abs((int) $this->quantity_change) * (float) $this->unit_cost, 2);
    }

    /* =========================================================================
     | QUERY SCOPES FOR PROCUREMENT & AUDIT REPORTS
     ========================================================================= */

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForVariant($query, int $variantId)
    {
        return $query->where('variant_id', $variantId);
    }

    public function scopeForSupplier($query, int $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeForPurchase($query, int $purchaseId)
    {
        return $query->where('purchase_id', $purchaseId);
    }

    public function scopeSalesOnly($query)
    {
        return $query->whereIn('movement_type', ['checkout_sale', 'pos_sale']);
    }

    public function scopePurchasesOnly($query)
    {
        return $query->whereIn('movement_type', ['purchase_receive', 'initial_stock']);
    }

    public function scopeReturnsOnly($query)
    {
        return $query->where('movement_type', 'supplier_return');
    }
}