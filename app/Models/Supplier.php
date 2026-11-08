<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

class Supplier extends Model
{
    use HasFactory, Auditable;

    protected $table = 'suppliers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Basic Identity & Contact
        'name',
        'company_name',
        'contact_person',
        'phone',
        'alternate_phone',
        'additional_phones',
        'email',
        'website',
        
        // Address Details
        'address',
        'country',
        'division',
        'district',
        'city',
        'area',
        'postal_code',

        // Business & Regulatory (BIN excluded as per instruction)
        'tin_number',
        'trade_license_number',
        'business_type',

        // Banking & Settlement Info
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'bank_branch',
        'bank_routing_number',

        // Financial & Credit Controls
        'credit_limit',
        'credit_period_days',
        'opening_balance',
        'opening_balance_type',
        'opening_balance_date',
        'default_payment_method',

        // Status & Notes
        'status',
        'internal_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'additional_phones'    => 'array',
        'credit_limit'         => 'decimal:2',
        'opening_balance'      => 'decimal:2',
        'opening_balance_date' => 'date',
        'credit_period_days'   => 'integer',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
    ];

    /* =========================================================================
     | RELATIONSHIPS
     ========================================================================= */

    /**
     * Supplier Contact Persons
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(SupplierContact::class, 'supplier_id');
    }

    /**
     * Primary Contact Person
     */
    public function primaryContact()
    {
        return $this->hasOne(SupplierContact::class, 'supplier_id')->where('is_primary', true);
    }

    /**
     * Supplier Documents (Trade License, Agreement, Tax Docs)
     */
    public function documents(): HasMany
    {
        return $this->hasMany(SupplierDocument::class, 'supplier_id');
    }

    /**
     * Purchase Orders issued to this supplier
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }

    /**
     * Actual Purchases / Invoices from this supplier
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'supplier_id');
    }

    /**
     * Goods Received Notes (GRN) from this supplier
     */
    public function receipts(): HasMany
    {
        return $this->hasMany(PurchaseReceipt::class, 'supplier_id');
    }

    /**
     * Payments made to this supplier
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class, 'supplier_id');
    }

    /**
     * Immutable Supplier Financial Ledger Entries
     */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(SupplierLedgerEntry::class, 'supplier_id')->orderBy('transaction_date', 'asc')->orderBy('id', 'asc');
    }

    /**
     * Purchase Returns sent to this supplier
     */
    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class, 'supplier_id');
    }

    /**
     * Goods Exchanges / Replacements with this supplier
     */
    public function exchanges(): HasMany
    {
        return $this->hasMany(SupplierExchange::class, 'supplier_id');
    }

    /**
     * Supplier Debit and Credit Notes
     */
    public function debitCreditNotes(): HasMany
    {
        return $this->hasMany(SupplierDebitCreditNote::class, 'supplier_id');
    }

    /**
     * Fixed Assets purchased from this supplier
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'supplier_id');
    }

    /**
     * Inventory Stock In/Out movements associated with this supplier
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'supplier_id');
    }

    /* =========================================================================
     | QUERY SCOPES
     ========================================================================= */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSearch($query, $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhere('company_name', 'LIKE', "%{$term}%")
              ->orWhere('phone', 'LIKE', "%{$term}%")
              ->orWhere('alternate_phone', 'LIKE', "%{$term}%")
              ->orWhere('email', 'LIKE', "%{$term}%")
              ->orWhere('contact_person', 'LIKE', "%{$term}%");
        });
    }

    /* =========================================================================
     | FINANCIAL ACCESSORS & HELPERS
     ========================================================================= */

    /**
     * Calculate Total Billed Amount (Purchases)
     */
    public function getTotalPurchaseAmountAttribute(): float
    {
        return (float) $this->purchases()
            ->whereNotIn('purchase_status', ['draft', 'cancelled'])
            ->sum('grand_total');
    }

    /**
     * Calculate Total Paid Amount (Settled Payments)
     */
    public function getTotalPaidAmountAttribute(): float
    {
        return (float) $this->payments()
            ->whereIn('status', ['approved', 'cleared'])
            ->sum('amount');
    }

    /**
     * Calculate Total Current Due / Payable Balance
     */
    public function getCurrentDueAmountAttribute(): float
    {
        $openingDue = ($this->opening_balance_type === 'payable') ? (float) $this->opening_balance : 0.0;
        
        $purchasesDue = (float) $this->purchases()
            ->whereNotIn('purchase_status', ['draft', 'cancelled'])
            ->sum('due_amount');

        return round($openingDue + $purchasesDue, 2);
    }

    /**
     * Calculate Total Advance Balance Available
     */
    public function getAdvanceBalanceAttribute(): float
    {
        $openingAdvance = ($this->opening_balance_type === 'advance') ? (float) $this->opening_balance : 0.0;

        $unallocatedPayments = (float) $this->payments()
            ->where('payment_type', 'advance')
            ->whereIn('status', ['approved', 'cleared'])
            ->sum('unallocated_amount');

        return round($openingAdvance + $unallocatedPayments, 2);
    }
}