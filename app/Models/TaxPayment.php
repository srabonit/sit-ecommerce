<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class TaxPayment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tax_payments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payment_date',
        'tax_period',
        'amount',
        'payment_method',
        'bank_account_id',
        'challan_number',
        'mushak_reference',
        'document_file',
        'notes',
        'payment_status',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'float',
    ];

    /**
     * Get the user who recorded this government tax payment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope query to only completed payments.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('payment_status', 'completed');
    }

    /**
     * Scope query by specific tax period (October 2026 or 2026-Q4).
     */
    public function scopeByPeriod(Builder $query, string $period): Builder
    {
        return $query->where('tax_period', $period);
    }

    /**
     * Resolve the downloadable / previewable URL for the challan document.
     */
    public function getDocumentUrlAttribute(): ?string
    {
        if (empty($this->document_file)) {
            return null;
        }

        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($this->document_file, '');
        }

        if (filter_var($this->document_file, FILTER_VALIDATE_URL)) {
            return $this->document_file;
        }

        return asset(ltrim($this->document_file, '/'));
    }
}