<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxRuleCondition extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tax_rule_conditions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tax_rule_id',
        'condition_type',
        'condition_id',
        'condition_value',
        'is_excluded',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'condition_id' => 'integer',
        'is_excluded'  => 'boolean',
    ];

    /**
     * Get the tax rule that owns this condition.
     */
    public function taxRule(): BelongsTo
    {
        return $this->belongsTo(TaxRule::class, 'tax_rule_id');
    }

    /**
     * Get the target entity model instance dynamically based on condition_type.
     */
    public function getTargetEntityAttribute()
    {
        if (!$this->condition_id) {
            return null;
        }

        return match ($this->condition_type) {
            'product'       => Product::find($this->condition_id),
            'variant'       => ProductVariant::find($this->condition_id),
            'category'      => Category::find($this->condition_id),
            'brand'         => Brand::find($this->condition_id),
            'branch'        => Branch::find($this->condition_id),
            'shipping_zone' => ShippingZone::find($this->condition_id),
            default         => null,
        };
    }

    /**
     * Get a human-readable display label for the condition.
     */
    public function getTargetLabelAttribute(): string
    {
        if ($this->condition_type === 'global') {
            return 'Global (All Items)';
        }

        if ($this->condition_value) {
            return ucfirst(str_replace('_', ' ', $this->condition_type)) . ': ' . $this->condition_value;
        }

        $entity = $this->target_entity;
        if ($entity) {
            return match ($this->condition_type) {
                'product'       => 'Product: ' . ($entity->title ?? ('#' . $entity->id)),
                'variant'       => 'Variant: #' . $entity->id . (!empty($entity->sku) ? ' (' . $entity->sku . ')' : ''),
                'category'      => 'Category: ' . ($entity->name ?? ('#' . $entity->id)),
                'brand'         => 'Brand: ' . ($entity->name ?? ('#' . $entity->id)),
                'branch'        => 'Branch: ' . ($entity->name ?? ('#' . $entity->id)),
                'shipping_zone' => 'Shipping Zone: ' . ($entity->name ?? ('#' . $entity->id)),
                default         => '#' . $this->condition_id,
            };
        }

        return ucfirst(str_replace('_', ' ', $this->condition_type)) . ' #' . $this->condition_id;
    }
}