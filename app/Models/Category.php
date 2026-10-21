<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Category extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Multiple products relation via pivot table
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_category', 'category_id', 'product_id')
                    ->wherePivot('type', 'parent')
                    ->withTimestamps();
    }

    /**
     * Legacy direct products relation (backup)
     */
    public function directProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function subCategories(): HasMany
    {
        return $this->hasMany(SubCategory::class, 'category_id');
    }

    /**
     * রিকার্সিভলি এই ক্যাটাগরির সমস্ত চাইল্ড ও নেস্টেড চাইল্ড সাব-ক্যাটাগরির ID সংগ্রহ করার মেথড
     *
     * @return array
     */
    public function getAllDescendantSubCategoryIds(): array
    {
        if (!Schema::hasTable('sub_categories')) {
            return [];
        }

        $allSubIds = [];
        $directSubs = DB::table('sub_categories')->where('category_id', $this->id)->pluck('id')->toArray();
        $allSubIds = array_merge($allSubIds, $directSubs);

        // যদি সাব-ক্যাটাগরি টেবিলের নিজস্ব parent_id থাকে (Nested SubCategories)
        if (Schema::hasColumn('sub_categories', 'parent_id')) {
            $queue = $directSubs;
            while (!empty($queue)) {
                $nested = DB::table('sub_categories')->whereIn('parent_id', $queue)->pluck('id')->toArray();
                if (empty($nested)) {
                    break;
                }
                $allSubIds = array_merge($allSubIds, $nested);
                $queue = $nested;
            }
        }

        return array_values(array_unique(array_map('intval', $allSubIds)));
    }
}