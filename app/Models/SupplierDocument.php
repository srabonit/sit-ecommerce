<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class SupplierDocument extends Model
{
    use HasFactory, Auditable;

    protected $table = 'supplier_documents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'supplier_id',
        'document_type',
        'title',
        'file_path',
        'file_name',
        'file_size_kb',
        'file_extension',
        'expiry_date',
        'uploaded_by',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expiry_date'  => 'date',
        'file_size_kb' => 'integer',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    /* =========================================================================
     | RELATIONSHIPS
     ========================================================================= */

    /**
     * Supplier to which this document belongs
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Admin/Staff who uploaded the document
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /* =========================================================================
     | SCOPES & HELPERS
     ========================================================================= */

    /**
     * Scope for expired documents
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')->where('expiry_date', '<', now()->toDateString());
    }

    /**
     * Scope for documents expiring within given days
     */
    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now()->toDateString(), now()->addDays($days)->toDateString()]);
    }

    /**
     * Check if document is currently expired
     */
    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }
}