<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmsContactGroup extends Model
{
    protected $fillable = [
        'name',
        'description',
        'total_contacts',
        'created_by',
    ];

    protected $casts = [
        'total_contacts' => 'integer',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(SmsContactGroupMember::class, 'group_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Recalculate and update the total contacts count for this group
     */
    public function refreshTotalContacts(): void
    {
        $this->update([
            'total_contacts' => $this->members()->count(),
        ]);
    }
}