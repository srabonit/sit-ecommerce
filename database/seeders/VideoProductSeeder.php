<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductGallery;
use Illuminate\Support\Str;

class VideoProductSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure baseline Category & Brand exist
        $category = Category::firstOrCreate(
            ['slug' => 'gadgets-electronics'],
            ['name' => 'Gadgets & Electronics', 'image' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=300&q=80']
        );

        $brand = Brand::firstOrCreate(
            ['slug' => 'tech-gear'],
            ['name' => 'TechGear']
        );

        $productsWithMedia = [
            [
                'title' => 'Sony WH-1000XM5 Wireless Headphones',
                'price' => 38500,
                'sale_price' => 35000,
                'video_url' => 'https://www.youtube.com/watch?v=sCWyZ3yq6d0',
                'thumbnail' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&q=80',
                'galleries' => [
                    'https://images.unsplash.com/photo-1484704849700-f032a568e944?w=800&q=80',
                    'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=800&q=80',
                    'https://images.unsplash.com/photo-1524678606370-a47ad25cb82a?w=800&q=80'
                ]
            ],
            [
                'title' => 'Apple Watch Ultra 2 Titanium Case',
                'price' => 95000,
                'sale_price' => 89999,
                'video_url' => 'https://www.youtube.com/watch?v=kY3Puhq8e8g',
                'thumbnail' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&q=80',
                'galleries' => [
                    'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=800&q=80',
                    'https://images.unsplash.com/photo-1434493789847-2f02dc6ca35d?w=800&q=80',
                    'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=800&q=80'
                ]
            ],
            [
                'title' => 'Samsung Galaxy S24 Ultra 5G AI',
                'price' => 145000,
                'sale_price' => 138000,
                'video_url' => 'https://www.youtube.com/watch?v=2r1pPuh8Bv4',
                'thumbnail' => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=800&q=80',
                'galleries' => [
                    'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=800&q=80',
                    'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800&q=80',
                    'https://images.unsplash.com/photo-1580910051074-3eb694886505?w=800&q=80'
                ]
            ],
            [
                'title' => 'Fujifilm X-T5 Mirrorless Digital Camera',
                'price' => 185000,
                'sale_price' => 175000,
                'video_url' => 'https://www.youtube.com/watch?v=Jm5R4yVlT1w',
                'thumbnail' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=800&q=80',
                'galleries' => [
                    'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=800&q=80',
                    'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=800&q=80',
                    'https://images.unsplash.com/photo-1495707902641-75cac588d2e9?w=800&q=80'
                ]
            ],
            [
                'title' => 'Apple MacBook Pro M3 Space Black',
                'price' => 245000,
                'sale_price' => 235000,
                'video_url' => 'https://www.youtube.com/watch?v=0pg_Y41waaE',
                'thumbnail' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800&q=80',
                'galleries' => [
                    'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=800&q=80',
                    'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?w=800&q=80',
                    'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?w=800&q=80'
                ]
            ]
        ];

        foreach ($productsWithMedia as $data) {
            $slug = Str::slug($data['title']) . '-' . Str::random(4);

            $product = Product::create([
                'title' => $data['title'],
                'slug' => $slug,
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'thumbnail' => $data['thumbnail'],
                'video_url' => $data['video_url'],
                'regular_price' => $data['price'],
                'sale_price' => $data['sale_price'],
                'is_featured' => true,
                'short_description' => 'Official authentic device with genuine brand warranty and YouTube official showcase.',
                'description' => '<p>Get top tier performance with official warranty coverage. All accessories and original box included.</p>',
                'allow_out_of_stock_order' => false,
                'is_seo_active' => true,
            ]);

            foreach ($data['galleries'] as $galleryImg) {
                ProductGallery::create([
                    'product_id' => $product->id,
                    'image_path' => $galleryImg
                ]);
            }
        }
    }
}