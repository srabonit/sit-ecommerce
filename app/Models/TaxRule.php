<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class TaxRule extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tax_rules';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tax_class_id',
        'name',
        'code',
        'tax_type',
        'rate',
        'fixed_amount',
        'calculation_type',
        'status_type',
        'priority',
        'min_taxable_amount',
        'max_taxable_amount',
        'valid_from',
        'valid_to',
        'is_active',
        'admin_note',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rate'               => 'float',
        'fixed_amount'       => 'float',
        'min_taxable_amount' => 'float',
        'max_taxable_amount' => 'float',
        'priority'           => 'integer',
        'is_active'          => 'boolean',
        'valid_from'         => 'datetime',
        'valid_to'           => 'datetime',
    ];

    /**
     * Get the tax class that owns this tax rule.
     */
    public function taxClass(): BelongsTo
    {
        return $this->belongsTo(TaxClass::class, 'tax_class_id');
    }

    /**
     * Get all target conditions associated with this tax rule.
     */
    public function conditions(): HasMany
    {
        return $this->hasMany(TaxRuleCondition::class, 'tax_rule_id');
    }

    /**
     * Get the user who created this rule.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this rule.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope a query to only include active tax rules.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include currently valid tax rules based on date.
     */
    public function scopeCurrentlyValid(Builder $query): Builder
    {
        $now = Carbon::now();
        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
            });
    }

    /**
     * Scope a query to order rules by priority descending.
     */
    public function scopeOrderedByPriority(Builder $query): Builder
    {
        return $query->orderByDesc('priority');
    }

    /**
     * Check if the rule is currently active and within valid dates.
     */
    public function isLive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();

        if ($this->valid_from && Carbon::parse($this->valid_from)->isFuture()) {
            return false;
        }

        if ($this->valid_to && Carbon::parse($this->valid_to)->isPast()) {
            return false;
        }

        return true;
    }
}