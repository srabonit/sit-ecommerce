<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailSegment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'email_segments';

    protected $fillable = [
        'name',
        'description',
        'type',
        'filter_criteria',
        'matching_subscribers_count',
        'last_calculated_at',
        'created_by',
    ];

    protected $casts = [
        'filter_criteria'            => 'array',
        'matching_subscribers_count' => 'integer',
        'last_calculated_at'         => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes (High-Performance Query Filters)
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Dynamic segments (Automatically re-evaluates customer audience)
     */
    public function scopeDynamic($query)
    {
        return $query->where('type', 'dynamic');
    }

    /**
     * Scope: Snapshot segments (Fixed point-in-time audience list)
     */
    public function scopeSnapshot($query)
    {
        return $query->where('type', 'snapshot');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Creator of this segment (Admin or Staff User)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Campaigns targeting this segment
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(EmailCampaign::class, 'segment_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers & State Checks
    |--------------------------------------------------------------------------
    */

    /**
     * Check if the segment is dynamically evaluated
     */
    public function isDynamic(): bool
    {
        return $this->type === 'dynamic';
    }

    /**
     * Check if the segment is a static snapshot
     */
    public function isSnapshot(): bool
    {
        return $this->type === 'snapshot';
    }

    /**
     * Update the cached subscriber count and calculation timestamp
     */
    public function updateMatchingCount(int $count): bool
    {
        return $this->update([
            'matching_subscribers_count' => $count,
            'last_calculated_at'         => now(),
        ]);
    }
}