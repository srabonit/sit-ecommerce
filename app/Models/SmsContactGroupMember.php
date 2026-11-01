<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsContactGroupMember extends Model
{
    protected $fillable = [
        'group_id',
        'phone_number',
        'name',
        'custom_attributes',
        'is_valid',
    ];

    protected $casts = [
        'custom_attributes' => 'array',
        'is_valid'          => 'boolean',
    ];

    /**
     * Boot model events to automatically maintain parent group counter
     */
    protected static function booted(): void
    {
        static::created(function (SmsContactGroupMember $member) {
            $member->group?->increment('total_contacts');
        });

        static::deleted(function (SmsContactGroupMember $member) {
            $member->group?->decrement('total_contacts');
        });
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(SmsContactGroup::class, 'group_id');
    }

    /**
     * Clean and normalize phone number into standard 8801XXXXXXXXX format
     */
    public function setPhoneNumberAttribute($value): void
    {
        $cleaned = preg_replace('/[^0-9]/', '', (string) $value);

        if (str_starts_with($cleaned, '01') && strlen($cleaned) === 11) {
            $cleaned = '88' . $cleaned;
        } elseif (str_starts_with($cleaned, '1') && strlen($cleaned) === 10) {
            $cleaned = '880' . $cleaned;
        }

        $this->attributes['phone_number'] = $cleaned;
        $this->attributes['is_valid'] = (bool) preg_match('/^8801[3-9]\d{8}$/', $cleaned);
    }

    public function scopeValid($query)
    {
        return $query->where('is_valid', true);
    }
}