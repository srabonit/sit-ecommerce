<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Traits\Auditable;

class SupplierLedgerEntry extends Model
{
    use HasFactory, Auditable;

    protected $table = 'supplier_ledger_entries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'entry_number',
        'supplier_id',
        'transaction_date',
        'transaction_type',
        'reference_type',
        'reference_id',
        'reference_no',
        'debit',
        'credit',
        'balance',
        'description',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'transaction_date' => 'date',
        'debit'            => 'decimal:2',
        'credit'           => 'decimal:2',
        'balance'          => 'decimal:2',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
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
     * Polymorphic source transaction (Purchase, SupplierPayment, PurchaseReturn, etc.)
     */
    public function reference(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'reference_type', 'reference_id');
    }

    /**
     * User who recorded/triggered this entry
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* =========================================================================
     | SCOPES & HELPERS
     ========================================================================= */

    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('transaction_type', $type);
    }
}