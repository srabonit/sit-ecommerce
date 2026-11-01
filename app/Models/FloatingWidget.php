<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class FloatingWidget extends Model
{
    use HasFactory;

    protected $table = 'floating_widgets';

    protected $fillable = [
        'widget_type',
        'label',
        'value',
        'icon',
        'position',
        'bottom_offset',
        'side_offset',
        'bg_color',
        'icon_color',
        'order_index',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'bottom_offset' => 'integer',
        'side_offset'   => 'integer',
        'order_index'   => 'integer',
        'is_active'     => 'boolean',
        'settings'      => 'array',
    ];

    /**
     * Polymorphic relation to display rules.
     */
    public function rule(): MorphOne
    {
        return $this->morphOne(DisplayCampaignRule::class, 'ruleable');
    }

    /**
     * Scope to fetch only active widgets ordered by priority.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order_index', 'asc');
    }
}