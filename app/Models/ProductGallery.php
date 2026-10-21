<?php

namespace App\Models;

use App\Services\ImagePathService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductGallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image_path',
        'image_title',
        'image_alt',
    ];

    protected $appends = ['image_url'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getImageUrlAttribute(): string
    {
        return ImagePathService::resolveUrl(
            $this->attributes['image_path'] ?? null,
            'https://placehold.co/300x300?text=No+Image'
        );
    }
}