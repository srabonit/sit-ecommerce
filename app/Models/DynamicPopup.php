<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class DynamicPopup extends Model
{
    use HasFactory;

    protected $table = 'dynamic_popups';

    protected $fillable = [
        'title',
        'popup_type',
        'content',
        'image',
        'button_text',
        'button_url',
        'trigger_type',
        'trigger_value',
        'frequency_days',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'trigger_value'  => 'integer',
        'frequency_days' => 'integer',
        'settings'       => 'array',
    ];

    /**
     * Polymorphic relation to display rules.
     */
    public function rule(): MorphOne
    {
        return $this->morphOne(DisplayCampaignRule::class, 'ruleable');
    }

    /**
     * Scope to fetch only active popups.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}