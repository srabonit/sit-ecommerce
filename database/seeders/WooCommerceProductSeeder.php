<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductGallery;
use Illuminate\Support\Str;

class WooCommerceProductSeeder extends Seeder
{
    public function run(): void
    {
        // ফাইলের নাম চেক (products.csv অথবা নতুন এক্সপোর্ট ফাইল)
        $filePath = database_path('data/products.csv');
        if (!file_exists($filePath)) {
            $filePath = database_path('data/wc-product-export-14-9-2026-1789327523308.csv');
        }

        if (!file_exists($filePath)) {
            $this->command->error("CSV file not found at: database/data/products.csv");
            return;
        }

        $this->command->warn("Clearing previous products, categories, brands, and galleries...");

        // আগের সব ডাটা মুছে ফেলা হচ্ছে
        Schema::disableForeignKeyConstraints();
        ProductGallery::truncate();
        Product::truncate();
        Category::truncate();
        Brand::truncate();
        Schema::enableForeignKeyConstraints();

        $this->command->info("Starting WooCommerce CSV Import from: {$filePath}");

        $file = fopen($filePath, 'r');
        $header = fgetcsv($file);

        if (!$header) {
            $this->command->error("CSV file is empty or corrupted!");
            fclose($file);
            return;
        }

        // CSV হেডার ম্যাপিং (BOM ক্যারেক্টার হ্যান্ডলিং সহ)
        $cleanHeader = array_map(function($h) {
            return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
        }, $header);
        $headerMap = array_flip($cleanHeader);

        $insertedCount = 0;

        while (($row = fgetcsv($file)) !== false) {
            $name = trim($row[$headerMap['Name'] ?? 4] ?? '');

            // নাম খালি থাকলে স্কিপ করবে
            if (empty($name)) {
                continue;
            }

            $sku          = trim($row[$headerMap['SKU'] ?? 2] ?? '');
            $regularPrice = (float)($row[$headerMap['Regular price'] ?? 26] ?? 0);
            $salePriceRaw = trim($row[$headerMap['Sale price'] ?? 25] ?? '');
            $salePrice    = (!empty($salePriceRaw) && is_numeric($salePriceRaw)) ? (float)$salePriceRaw : null;
            
            // বিবরণী ও ক্লিনআপ
            $shortDesc    = $row[$headerMap['Short description'] ?? 8] ?? null;
            $description  = $row[$headerMap['Description'] ?? 9] ?? null;
            
            // \r\n এবং অতিরিক্ত লিটারেল \n ক্লিন করা
            if ($shortDesc) {
                $shortDesc = str_replace(['\r\n', '\n', '\r'], "\n", $shortDesc);
            }
            if ($description) {
                $description = str_replace(['\r\n', '\n', '\r'], "\n", $description);
            }

            $isFeatured   = (trim($row[$headerMap['Is featured?'] ?? 6] ?? '0') === '1');
            $rawCategories= trim($row[$headerMap['Categories'] ?? 27] ?? '');
            $rawBrand     = trim($row[$headerMap['Brands'] ?? 40] ?? '');
            $rawImages    = trim($row[$headerMap['Images'] ?? 30] ?? '');
            $stockVal     = trim($row[$headerMap['Stock'] ?? 15] ?? '');

            // ১. ক্যাটাগরি প্রসেসিং (লাস্ট স্পেসিফিক ক্যাটাগরি নেওয়া)
            $categoryId = null;
            if (!empty($rawCategories)) {
                $categoriesList = array_map('trim', explode(',', $rawCategories));
                $primaryCatString = end($categoriesList);
                $catParts = array_map('trim', explode('>', $primaryCatString));
                $leafCategoryName = end($catParts);

                if (!empty($leafCategoryName)) {
                    $category = Category::firstOrCreate(
                        ['name' => $leafCategoryName],
                        ['slug' => Str::slug($leafCategoryName) ?: 'cat-' . Str::random(5)]
                    );
                    $categoryId = $category->id;
                }
            }

            // ২. ব্র্যান্ড প্রসেসিং
            $brandId = null;
            if (!empty($rawBrand)) {
                $brand = Brand::firstOrCreate(
                    ['name' => $rawBrand],
                    ['slug' => Str::slug($rawBrand) ?: 'brand-' . Str::random(5)]
                );
                $brandId = $brand->id;
            }

            // ৩. ইমেজ ও গ্যালারি হ্যান্ডলিং
            $images = [];
            if (!empty($rawImages)) {
                $images = array_unique(array_filter(array_map('trim', explode(',', $rawImages))));
            }
            $thumbnail = !empty($images) ? array_shift($images) : null;

            // ৪. ইউনিক স্লাগ তৈরি
            $baseSlug = Str::slug($name);
            $slug = $baseSlug ?: 'product-' . Str::random(6);
            if (Product::where('slug', $slug)->exists()) {
                $slug .= '-' . (!empty($sku) ? Str::slug($sku) : Str::random(4));
            }

            // ৫. প্রোডাক্ট ডাটাবেজে ইনসার্ট
            $product = Product::create([
                'title'                    => $name,
                'slug'                     => $slug,
                'sku'                      => !empty($sku) ? $sku : 'SKU-' . rand(10000, 99999),
                'category_id'              => $categoryId,
                'brand_id'                 => $brandId,
                'thumbnail'                => $thumbnail,
                'regular_price'            => $regularPrice > 0 ? $regularPrice : ($salePrice ?? 0),
                'sale_price'               => ($salePrice && $salePrice < $regularPrice) ? $salePrice : null,
                'stock'                    => is_numeric($stockVal) ? (int)$stockVal : 50,
                'is_featured'              => $isFeatured,
                'short_description'        => $shortDesc,
                'description'              => $description,
                'allow_out_of_stock_order' => false,
                'is_seo_active'            => true,
            ]);

            // ৬. বাকি ছবিগুলো গ্যালারিতে ইনসার্ট
            if (!empty($images)) {
                foreach ($images as $imgUrl) {
                    ProductGallery::create([
                        'product_id' => $product->id,
                        'image_path' => $imgUrl,
                    ]);
                }
            }

            $insertedCount++;
        }

        fclose($file);

        $this->command->info("🎉 Success! Previous data removed and newly imported {$insertedCount} products with categories, brands & galleries.");
    }
}