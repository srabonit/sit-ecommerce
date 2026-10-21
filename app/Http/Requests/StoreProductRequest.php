<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Basic Information
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'product_code' => 'nullable|string|max:100|unique:products,product_code',
            'barcode' => 'nullable|string|max:100',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',

            // Types and Structure
            'product_type' => 'required|in:physical,virtual,downloadable',
            'product_struct' => 'required|in:simple,variable,grouped,external_affiliate,sample',

            // Pricing and Stock
            'regular_price' => 'required_if:product_struct,simple|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|between:0,100',
            'stock' => 'required_if:product_struct,simple|integer|min:0',
            'allow_out_of_stock_order' => 'boolean',

            // Single Thumbnail Media
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'thumbnail_title' => 'nullable|string|max:255',
            'thumbnail_alt' => 'nullable|string|max:255',

            // Gallery Images (Maximum 10 images)
            'gallery_images' => 'nullable|array|max:10',
            'gallery_images.*.file' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gallery_images.*.title' => 'nullable|string|max:255',
            'gallery_images.*.alt' => 'nullable|string|max:255',

            // Product Variants
            'variants' => 'required_if:product_struct,variable|array',
            'variants.*.color' => 'nullable|string',
            'variants.*.size' => 'nullable|string',
            'variants.*.product_code' => 'nullable|string|distinct',
            'variants.*.barcode' => 'nullable|string',
            'variants.*.regular_price' => 'required_with:variants|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0',
            'variants.*.wholesale_price' => 'nullable|numeric|min:0',
            'variants.*.purchase_price' => 'nullable|numeric|min:0',
            'variants.*.discount_percentage' => 'nullable|numeric|between:0,100',
            'variants.*.stock' => 'required_with:variants|integer|min:0',

            // Taxonomy IDs
            'category_id' => 'nullable|integer',
            'sub_category_id' => 'nullable|integer',
            'brand_id' => 'nullable|integer',

            // Shipping and Dimensions
            'shipping_class_id' => 'nullable|integer',
            'specific_delivery_charge' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',

            // SEO Metadata
            'is_seo_active' => 'boolean',
            'focus_keyphrase' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'canonical_url' => 'nullable|url',
        ];
    }
}