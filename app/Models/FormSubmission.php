<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormSubmission extends Model
{
    use HasFactory;

    protected $table = 'form_submissions';

    protected $fillable = [
        'form_id',
        'ip_address',
        'user_agent',
        'referer',
        'status',
        'replied_by',
        'replied_at',
        'reply_subject',
        'reply_message',
        'is_ip_blocked',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at'  => 'datetime',
        'replied_at'    => 'datetime',
        'is_ip_blocked' => 'boolean',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(FormSubmissionValue::class, 'submission_id');
    }

    /**
     * Admin staff who replied to this lead.
     */
    public function repliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    /**
     * Helper Accessor: Extract customer email dynamically from submission fields.
     */
    public function getCustomerEmailAttribute(): ?string
    {
        foreach ($this->values as $item) {
            $field = $item->field;
            $fieldName  = strtolower($field->name ?? '');
            $fieldLabel = strtolower($field->label ?? '');
            $val        = trim((string) $item->value);

            if (($field && $field->type === 'email') || str_contains($fieldName, 'email') || str_contains($fieldLabel, 'email')) {
                if (filter_var($val, FILTER_VALIDATE_EMAIL)) {
                    return strtolower($val);
                }
            }
        }

        return null;
    }

    /**
     * Helper Accessor: Extract customer full name dynamically from submission fields.
     */
    public function getCustomerNameAttribute(): string
    {
        foreach ($this->values as $item) {
            $field = $item->field;
            $fieldName  = strtolower($field->name ?? '');
            $fieldLabel = strtolower($field->label ?? '');
            $val        = trim((string) $item->value);

            if (in_array($fieldName, ['name', 'full_name', 'your_name', 'customer_name']) || str_contains($fieldLabel, 'name')) {
                if (!empty($val) && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
                    return $val;
                }
            }
        }

        return 'Valued Customer';
    }
}