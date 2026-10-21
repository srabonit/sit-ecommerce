<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $categoriesData = [
            ['name' => 'Smartphones', 'image' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=300&q=80'],
            ['name' => 'Laptops', 'image' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=300&q=80'],
            ['name' => 'Smart Watches', 'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=300&q=80'],
            ['name' => 'Headphones', 'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=300&q=80'],
            ['name' => 'Accessories', 'image' => 'https://images.unsplash.com/photo-1583394838336-acd977736f90?w=300&q=80'],
            ['name' => 'Cameras', 'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=300&q=80'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[] = Category::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                ['name' => $cat['name'], 'image' => $cat['image']]
            );
        }

        $brandNames = ['Apple', 'Samsung', 'Sony', 'Dell', 'Asus', 'Nike', 'Xiaomi'];
        $brands = [];
        foreach ($brandNames as $bName) {
            $brands[] = Brand::firstOrCreate(
                ['slug' => Str::slug($bName)],
                ['name' => $bName]
            );
        }

        $catalog = [
            [
                'title' => 'iPhone 15 Pro Max',
                'img' => 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=600&q=80',
                'price' => 155000,
                'sale' => 148000,
                'cat' => 'Smartphones'
            ],
            [
                'title' => 'Samsung Galaxy S24 Ultra',
                'img' => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=600&q=80',
                'price' => 142000,
                'sale' => 135000,
                'cat' => 'Smartphones'
            ],
            [
                'title' => 'MacBook Pro M3 Max',
                'img' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=600&q=80',
                'price' => 245000,
                'sale' => 235000,
                'cat' => 'Laptops'
            ],
            [
                'title' => 'Sony WH-1000XM5 Headphone',
                'img' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&q=80',
                'price' => 38000,
                'sale' => 34500,
                'cat' => 'Headphones'
            ],
            [
                'title' => 'Apple Watch Series 9 GPS',
                'img' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&q=80',
                'price' => 52000,
                'sale' => 49000,
                'cat' => 'Smart Watches'
            ],
            [
                'title' => 'Dell XPS 15 OLED InfinityEdge',
                'img' => 'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?w=600&q=80',
                'price' => 185000,
                'sale' => 175000,
                'cat' => 'Laptops'
            ],
            [
                'title' => 'Sony Alpha A7 IV Mirrorless',
                'img' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=600&q=80',
                'price' => 220000,
                'sale' => 210000,
                'cat' => 'Cameras'
            ],
            [
                'title' => 'AirPods Pro 2nd Generation',
                'img' => 'https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=600&q=80',
                'price' => 28500,
                'sale' => 26500,
                'cat' => 'Accessories'
            ],
            [
                'title' => 'Samsung Galaxy Watch 6 Classic',
                'img' => 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=600&q=80',
                'price' => 34000,
                'sale' => 31000,
                'cat' => 'Smart Watches'
            ],
            [
                'title' => 'Asus ROG Zephyrus G16 Gaming',
                'img' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=600&q=80',
                'price' => 210000,
                'sale' => 198000,
                'cat' => 'Laptops'
            ],
        ];

        for ($i = 1; $i <= 100; $i++) {
            $base = $catalog[array_rand($catalog)];
            $brand = $brands[array_rand($brands)];
            $category = collect($categories)->firstWhere('name', $base['cat']) ?? $categories[array_rand($categories)];

            $variantNumber = $i > 10 ? ' (Edition ' . $i . ')' : '';
            $productTitle = $base['title'] . $variantNumber;
            $slug = Str::slug($productTitle) . '-' . Str::random(5);

            Product::create([
                'title' => $productTitle,
                'slug' => $slug,
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'thumbnail' => $base['img'],
                'regular_price' => $base['price'],
                'sale_price' => (rand(0, 1) === 1) ? $base['sale'] : null,
                'is_featured' => (rand(0, 1) === 1),
                'short_description' => 'Original high quality product with official warranty and fast home delivery.',
                'description' => 'Detailed product overview with premium specs and guaranteed durability.',
                'allow_out_of_stock_order' => false,
                'is_seo_active' => true,
            ]);
        }
    }
}