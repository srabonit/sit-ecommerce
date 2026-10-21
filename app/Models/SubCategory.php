<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SubCategory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_category', 'category_id', 'product_id')
                    ->wherePivot('type', 'child')
                    ->withTimestamps();
    }

    public function directProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'sub_category_id');
    }

    /**
     * এই চাইল্ড ক্যাটাগরির নিজস্ব এবং অধীনস্থ সমস্ত নেস্টেড চাইল্ডের ID বের করার মেথড
     */
    public function getAllDescendantIds(): array
    {
        $ids = [(int)$this->id];

        if (Schema::hasColumn('sub_categories', 'parent_id')) {
            $queue = [(int)$this->id];
            while (!empty($queue)) {
                $nested = DB::table('sub_categories')->whereIn('parent_id', $queue)->pluck('id')->toArray();
                if (empty($nested)) {
                    break;
                }
                $ids = array_merge($ids, $nested);
                $queue = $nested;
            }
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }
}