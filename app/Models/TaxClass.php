<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxClass extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tax_classes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the tax rules associated with this tax class.
     */
    public function taxRules(): HasMany
    {
        return $this->hasMany(TaxRule::class, 'tax_class_id');
    }

    /**
     * Get active tax rules under this class ordered by priority.
     */
    public function activeRules(): HasMany
    {
        return $this->hasMany(TaxRule::class, 'tax_class_id')
            ->where('is_active', true)
            ->orderByDesc('priority');
    }

    /**
     * Get the products assigned to this tax class.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'tax_class_id');
    }

    /**
     * Get the categories assigned to this tax class.
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'tax_class_id');
    }

    /**
     * Get the user who created this tax class.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}