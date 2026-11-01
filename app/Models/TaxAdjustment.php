<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class TaxAdjustment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tax_adjustments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'adjustment_type',
        'original_tax_amount',
        'adjusted_tax_amount',
        'net_tax_change',
        'reference_no',
        'reason',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'original_tax_amount' => 'float',
        'adjusted_tax_amount' => 'float',
        'net_tax_change'      => 'float',
    ];

    /**
     * Get the order associated with this tax adjustment.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Get the admin user who created this adjustment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope query to only include refund adjustments.
     */
    public function scopeRefunds(Builder $query): Builder
    {
        return $query->where('adjustment_type', 'refund');
    }

    /**
     * Scope query to only include cancellation adjustments.
     */
    public function scopeCancellations(Builder $query): Builder
    {
        return $query->where('adjustment_type', 'cancellation');
    }

    /**
     * Scope query to only include return & exchange adjustments.
     */
    public function scopeReturnExchanges(Builder $query): Builder
    {
        return $query->where('adjustment_type', 'return_exchange');
    }
}