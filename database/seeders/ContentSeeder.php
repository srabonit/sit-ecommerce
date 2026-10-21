<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        // ১. সিস্টেম ও ডিফল্ট পেজসমূহ
        $systemPages = [
            [
                'title' => 'Home',
                'slug' => 'home',
                'editor_type' => 'blocks',
                'content' => '<h1>Welcome to our Store</h1>',
                'seo_title' => 'Shop Now BD - Premium Online Shopping',
                'seo_description' => 'Best online shop in Bangladesh with high quality products.',
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'editor_type' => 'html_raw',
                'content' => '<div class="about-hero py-4"><h2>Leading E-commerce Platform</h2><p class="lead">We provide authentic fashion, electronics, and lifestyle products with doorstep delivery all over Bangladesh.</p><div class="row g-4 mt-2"><div class="col-md-4"><div class="p-3 border rounded bg-light"><h5>Authentic Products</h5><p class="small text-muted mb-0">Direct from genuine manufacturers and brands.</p></div></div><div class="col-md-4"><div class="p-3 border rounded bg-light"><h5>Fast Delivery</h5><p class="small text-muted mb-0">Inside Dhaka 24-48 hours, outside Dhaka 3-5 days.</p></div></div><div class="col-md-4"><div class="p-3 border rounded bg-light"><h5>24/7 Support</h5><p class="small text-muted mb-0">Dedicated team ready to assist via call or chat.</p></div></div></div></div>',
                'seo_title' => 'About Our Company - Shop Now BD',
                'seo_description' => 'Learn more about our mission, vision and customer service standards.',
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'editor_type' => 'html_raw',
                'content' => '<div class="row g-4"><div class="col-md-6"><h3>Get in Touch</h3><p class="text-muted">Have any query or order problem? Reach us directly.</p><ul class="list-unstyled mt-3"><li class="mb-2"><i class="fas fa-map-marker-alt text-primary me-2"></i> Dhaka, Bangladesh</li><li class="mb-2"><i class="fas fa-phone-alt text-primary me-2"></i> +880 1700-000000</li><li class="mb-2"><i class="fas fa-envelope text-primary me-2"></i> support@shopnowbd.com</li></ul></div><div class="col-md-6"><form class="p-4 border rounded bg-light shadow-sm"><div class="mb-3"><label class="form-label fw-bold small">Your Name</label><input type="text" class="form-control" placeholder="John Doe"></div><div class="mb-3"><label class="form-label fw-bold small">Phone Number</label><input type="text" class="form-control" placeholder="017xxxxxxxx"></div><div class="mb-3"><label class="form-label fw-bold small">Message</label><textarea class="form-control" rows="4" placeholder="How can we help?"></textarea></div><button type="button" class="btn btn-primary w-100">Send Message</button></form></div></div>',
                'seo_title' => 'Contact Customer Care - Shop Now BD',
                'seo_description' => 'Contact our help desk for delivery, orders, and business queries.',
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($systemPages as $page) {
            DB::table('site_pages')->updateOrInsert(['slug' => $page['slug']], $page);
        }

        // ২. ২০টি আকর্ষণীয় ব্লগ পোস্ট (রিয়েল ইমেজ ও ক্যাটাগরি সহ)
        $categories = ['Fashion & Style', 'E-commerce Tips', 'Tech Gadgets', 'Lifestyle', 'Watches & Bags'];
        $images = [
            'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=900&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=900&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=900&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=900&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=900&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?w=900&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=900&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=900&auto=format&fit=crop&q=80',
        ];

        $postTitles = [
            'Top 10 Trendy Casual Outfits for Summer in Bangladesh',
            'How to Choose the Perfect Wristwatch for Formal and Casual Wear',
            'The Ultimate Guide to Shopping Genuine Leather Bags Online',
            'Smart Watches vs Analog Watches: What Suits Your Personality?',
            'Essential Wardrobe Basics Every Modern Gentleman Should Own',
            'Top 5 Premium Ladies Dress Styles for Festive Seasons',
            'How to Take Care of Pure Cotton Fabrics for Long Life',
            'Everything You Need to Know Before Buying Wireless Earbuds',
            'Safe Online Payment Tips: Avoid Fraud While Shopping in BD',
            'The Rise of Minimalist Fashion: Why Less is Always More',
            'Top Sneaker Trends of the Year You Shouldn’t Miss',
            'How Courier Cash on Delivery Works for Online Store Shoppers',
            'Winter Collection Highlights: Jackets, Hoodies & Blazers',
            'Finding the Best Bag for Work and Daily Commute',
            'How to Spot Fake vs Original Products in Online Stores',
            '10 Life-Hacks to Save Money on Seasonal Online Sales',
            'Comfort Meets Style: Choosing Everyday Casual Footwear',
            'Why Eco-Friendly Organic Cotton Is the Future of Clothing',
            'Top Gift Ideas for Your Loved Ones on Special Occasions',
            'Our Promise: Fast Dispatch and Hassle-Free Product Returns'
        ];

        DB::table('blog_posts')->truncate();

        foreach ($postTitles as $i => $title) {
            $cat = $categories[$i % count($categories)];
            $img = $images[$i % count($images)];
            $slug = Str::slug($title);

            DB::table('blog_posts')->insert([
                'title' => $title,
                'slug' => $slug,
                'excerpt' => 'Discover the best practical advice, styling ideas, and tips in our complete walkthrough on ' . strtolower($title) . '.',
                'content' => '<p class="lead">Shopping online has revolutionized the way we explore new trends and upgrade our lifestyle. In this feature, we delve deep into the essential nuances of <strong>' . $title . '</strong>.</p>
                <h4>Why Quality Matters Most</h4>
                <p>When selecting your preferred items, durability and material comfort always take precedence. A reliable piece not only highlights your aesthetic appeal but also provides genuine value for money throughout repeated daily use.</p>
                <div class="my-4 p-3 bg-light border-start border-4 border-primary rounded-end">
                    <em>"Style is a way to say who you are without having to speak. Always prioritize comfort and precision craftsmanship."</em>
                </div>
                <h4>Tips for Making the Best Purchase</h4>
                <ul>
                    <li>Double check the size guide and fabric composition specifications.</li>
                    <li>Read authentic buyer feedback and review actual product photos.</li>
                    <li>Take advantage of seasonal campaign vouchers and discount codes.</li>
                </ul>
                <p>Whether you are styling for daily work, college, or weekend outings, keeping your collection curated with genuine essentials guarantees lasting confidence.</p>',
                'featured_image' => $img,
                'category' => $cat,
                'tags' => 'fashion, lifestyle, shopping, ecommerce',
                'seo_title' => $title . ' - Shop Now BD Blog',
                'seo_description' => 'Read our in-depth article on ' . $title . '. Tips, style guides, and recommendations.',
                'status' => 'published',
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now(),
            ]);
        }

        // ৩. ডাইনামিক উইজেট সিডিং
        DB::table('site_widgets')->truncate();
        DB::table('site_widgets')->insert([
            [
                'title' => 'Search Articles',
                'widget_zone' => 'sidebar-right',
                'widget_type' => 'search',
                'content' => null,
                'order_index' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Popular Categories',
                'widget_zone' => 'sidebar-right',
                'widget_type' => 'categories',
                'content' => null,
                'order_index' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Mega Campaign Banner',
                'widget_zone' => 'sidebar-right',
                'widget_type' => 'custom_html',
                'content' => '<div class="card border-0 rounded-4 overflow-hidden text-center text-white p-4 shadow-sm" style="background: linear-gradient(135deg, #0d6efd 0%, #002b75 100%);">
                    <span class="badge bg-warning text-dark px-3 py-1 rounded-pill mb-2 fw-bold">SPECIAL OFFER</span>
                    <h5 class="fw-bold mb-1">Get 20% Discount</h5>
                    <p class="small opacity-75 mb-3">On all new collection watches &amp; bags</p>
                    <a href="/shop" class="btn btn-light btn-sm rounded-pill px-4 fw-bold text-primary">Shop Now</a>
                </div>',
                'order_index' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Customer Care Hotline',
                'widget_zone' => 'sidebar-left',
                'widget_type' => 'custom_html',
                'content' => '<div class="p-3 bg-light rounded-4 border text-center">
                    <i class="fas fa-headset fa-2x text-primary mb-2"></i>
                    <h6 class="fw-bold mb-1">Need Assistance?</h6>
                    <p class="small text-muted mb-2">Call our helpline 10 AM - 10 PM</p>
                    <a href="tel:+8801700000000" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold">+880 1700-000000</a>
                </div>',
                'order_index' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}