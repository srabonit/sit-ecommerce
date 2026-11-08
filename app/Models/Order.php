<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'invoice_no',
        'branch_id',
        'warehouse_id',
        'user_id',
        'sales_person_id',
        'order_type',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'grand_total',
        'paid_amount',
        'due_amount',
        'payment_method',
        'transaction_id',
        'sender_number',
        'payment_screenshot',
        'payment_status',
        'order_status',
        'notes',
    ];

    protected $casts = [
        'branch_id'       => 'integer',
        'warehouse_id'    => 'integer',
        'user_id'         => 'integer',
        'sales_person_id' => 'integer',
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'grand_total'     => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'due_amount'      => 'decimal:2',
    ];

    /**
     * The "booted" method of the model.
     * Preserves existing multi-branch global scope isolation.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new BranchScope);
    }

    // =========================================================================
    // 1. EXISTING ORIGINAL RELATIONSHIPS (PRESERVED 100%)
    // =========================================================================

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // =========================================================================
    // 2. ADVANCED MULTI-BRANCH RELATIONSHIPS
    // =========================================================================

    /**
     * Sales Representative / Staff member attributed to this sale.
     */
    public function salesPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    /**
     * Source warehouse assigned to this order.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    // =========================================================================
    // 3. MULTI-BRANCH SALES QUERY SCOPES & HELPERS
    // =========================================================================

    /**
     * Scope query specifically for a branch (can bypass global scope when needed).
     */
    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope query for a dedicated sales representative's sales.
     */
    public function scopeForSalesPerson($query, int $salesPersonId)
    {
        return $query->where('sales_person_id', $salesPersonId);
    }

    /**
     * Scope POS terminal sales only.
     */
    public function scopePosOrders($query)
    {
        return $query->where('order_type', 'pos');
    }

    /**
     * Scope online ecommerce storefront sales only.
     */
    public function scopeOnlineOrders($query)
    {
        return $query->where(function ($q) {
            $q->where('order_type', 'online')
              ->orWhereNull('order_type');
        });
    }

    /**
     * Check if this order was placed through a POS terminal.
     */
    public function isPosOrder(): bool
    {
        return strtolower((string)$this->order_type) === 'pos';
    }
}