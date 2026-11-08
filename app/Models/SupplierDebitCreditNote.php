<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierDebitCreditNote extends Model
{
    use HasFactory;

    protected $table = 'supplier_debit_credit_notes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'note_number',
        'reference_no',
        'supplier_id',
        'purchase_id',
        'purchase_return_id',
        'supplier_exchange_id',
        'branch_id',
        'note_type',
        'reason_category',
        'reason_type',
        'reason',
        'description',
        'note_date',
        'amount',
        'tax_amount',
        'total_amount',
        'chart_of_account_id',
        'status',
        'attachment_file',
        'notes',
        'cancelled_reason',
        'created_by',
        'approved_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'note_date'    => 'date',
        'amount'       => 'decimal:2',
        'tax_amount'   => 'decimal:2',
        'total_amount' => 'decimal:2',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    /* =========================================================================
     | RELATIONSHIPS
     ========================================================================= */

    /**
     * Associated Supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Associated Purchase Bill (if linked)
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    /**
     * Operating Branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Associated Purchase Return (if generated from return)
     */
    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class, 'purchase_return_id');
    }

    /**
     * Associated Supplier Exchange (if generated from exchange price difference)
     */
    public function supplierExchange(): BelongsTo
    {
        return $this->belongsTo(SupplierExchange::class, 'supplier_exchange_id');
    }

    /**
     * Note Creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Note Approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Associated Ledger Entries
     */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(SupplierLedgerEntry::class, 'reference_id')
            ->where('reference_type', self::class);
    }

    /* =========================================================================
     | SCOPES & HELPERS
     ========================================================================= */

    public function scopeDebitNotes($query)
    {
        return $query->where('note_type', 'debit_note');
    }

    public function scopeCreditNotes($query)
    {
        return $query->where('note_type', 'credit_note');
    }

    public function scopeApproved($query)
    {
        return $query->whereIn('status', ['approved', 'posted']);
    }

    public function scopeSearch($query, $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('note_number', 'LIKE', "%{$term}%")
              ->orWhere('reference_no', 'LIKE', "%{$term}%")
              ->orWhere('reason', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%")
              ->orWhereHas('supplier', function ($sq) use ($term) {
                  $sq->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('company_name', 'LIKE', "%{$term}%");
              });
        });
    }

    public function isDebitNote(): bool
    {
        return $this->note_type === 'debit_note';
    }

    public function isCreditNote(): bool
    {
        return $this->note_type === 'credit_note';
    }
}