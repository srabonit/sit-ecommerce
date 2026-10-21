<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product') ?? $this->route('id');

        return [
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $productId,
            'product_code' => 'nullable|string|max:100|unique:products,product_code,' . $productId,
            'barcode' => 'nullable|string|max:100',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',

            'product_type' => 'sometimes|required|in:physical,virtual,downloadable',
            'product_struct' => 'sometimes|required|in:simple,variable,grouped,external_affiliate,sample',

            'regular_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|between:0,100',
            'stock' => 'nullable|integer|min:0',
            'allow_out_of_stock_order' => 'boolean',

            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'thumbnail_title' => 'nullable|string|max:255',
            'thumbnail_alt' => 'nullable|string|max:255',

            'gallery_images' => 'nullable|array|max:10',
            'gallery_images.*.file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gallery_images.*.title' => 'nullable|string|max:255',
            'gallery_images.*.alt' => 'nullable|string|max:255',

            'is_seo_active' => 'boolean',
            'focus_keyphrase' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'canonical_url' => 'nullable|url',
        ];
    }
}