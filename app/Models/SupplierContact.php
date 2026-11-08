<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class SupplierContact extends Model
{
    use HasFactory, Auditable;

    protected $table = 'supplier_contacts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'supplier_id',
        'name',
        'designation',
        'phone',
        'alternate_phone',
        'email',
        'address',
        'is_primary',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* =========================================================================
     | RELATIONSHIPS
     ========================================================================= */

    /**
     * Supplier to which this contact belongs
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /* =========================================================================
     | SCOPES
     ========================================================================= */

    /**
     * Scope for primary contacts
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}