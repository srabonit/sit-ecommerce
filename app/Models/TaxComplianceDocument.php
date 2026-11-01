<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class TaxComplianceDocument extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tax_compliance_documents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'doc_type',
        'title',
        'reference_no',
        'file_path',
        'issue_date',
        'expiry_date',
        'meta_data',
        'uploaded_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'issue_date'  => 'date',
        'expiry_date' => 'date',
        'meta_data'   => 'array',
    ];

    /**
     * Get the user who uploaded this compliance document.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Scope query to filter by document type (bin_certificate, trade_license, mushak_challan).
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('doc_type', $type);
    }

    /**
     * Scope query to only include currently active / non-expired documents.
     */
    public function scopeValid(Builder $query): Builder
    {
        $today = Carbon::today();
        return $query->where(function ($q) use ($today) {
            $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', $today);
        });
    }

    /**
     * Check if the document has expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        if (empty($this->expiry_date)) {
            return false;
        }

        return Carbon::parse($this->expiry_date)->isPast();
    }

    /**
     * Resolve the downloadable / previewable URL for the compliance document.
     */
    public function getFileUrlAttribute(): ?string
    {
        if (empty($this->file_path)) {
            return null;
        }

        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($this->file_path, '');
        }

        if (filter_var($this->file_path, FILTER_VALIDATE_URL)) {
            return $this->file_path;
        }

        return asset(ltrim($this->file_path, '/'));
    }
}