<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\ShortcodeParser;


class PageController extends Controller
{
    public function show($slug)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Get Published Page
        |--------------------------------------------------------------------------
        */
        $page = DB::table('site_pages')
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        abort_if(!$page, 404);


        /*
        |--------------------------------------------------------------------------
        | 2. Decode Content Blocks
        |--------------------------------------------------------------------------
        */
        $blocks = [];

        if (!empty($page->content_blocks)) {

            $blocks = is_array($page->content_blocks)
                ? $page->content_blocks
                : json_decode($page->content_blocks, true);
        }


        /*
        |--------------------------------------------------------------------------
        | 3. Process Dynamic Content Blocks
        |--------------------------------------------------------------------------
        */
        if (is_array($blocks)) {

            foreach ($blocks as &$block) {

                /*
                |--------------------------------------------------------------------------
                | Products Grid
                |--------------------------------------------------------------------------
                */
                if (($block['type'] ?? '') === 'products_grid') {

                    $limit = $block['data']['limit'] ?? 8;

                    $prodQuery = DB::table('products');

                    if (Schema::hasColumn('products', 'status')) {

                        $prodQuery->where('status', 1);

                    } elseif (Schema::hasColumn('products', 'is_active')) {

                        $prodQuery->where('is_active', 1);
                    }

                    $block['data']['products'] = $prodQuery
                        ->latest('id')
                        ->take($limit)
                        ->get();
                }


                /*
                |--------------------------------------------------------------------------
                | Recent Blog Posts
                |--------------------------------------------------------------------------
                */
                if (($block['type'] ?? '') === 'recent_posts') {

                    $limit = $block['data']['limit'] ?? 3;

                    $block['data']['posts'] = DB::table('blog_posts')
                        ->where('status', 'published')
                        ->latest('id')
                        ->take($limit)
                        ->get();
                }
            }

            unset($block);
        }


        /*
        |--------------------------------------------------------------------------
        | 4. Parse Page Content Shortcodes
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | এখানেই Page Content এর shortcode process হবে।
        |
        | Example:
        |
        | [products limit="4"]
        |
        | [form id="1"]
        |
        | [form slug="contact-us"]
        |
        |--------------------------------------------------------------------------
        */
        $renderedContent = '';

        if (!empty($page->content)) {

            $renderedContent = ShortcodeParser::parse(
                $page->content
            );
        }


        /*
        |--------------------------------------------------------------------------
        | 5. Sidebar Widgets
        |--------------------------------------------------------------------------
        */
        $sidebarWidgets = collect();

        if (Schema::hasTable('site_widgets')) {

            $sidebarWidgets = DB::table('site_widgets')
                ->where('is_active', 1)
                ->orderBy('order_index', 'asc')
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | 6. Popular Categories
        |--------------------------------------------------------------------------
        */
        $popularCategories = collect();

        if (Schema::hasTable('categories')) {

            $popularCategories = DB::table('categories')
                ->latest('id')
                ->take(6)
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | 7. Return Dynamic Page
        |--------------------------------------------------------------------------
        */
        return view(
            'frontend.pages.dynamic',
            compact(
                'page',
                'blocks',
                'sidebarWidgets',
                'popularCategories',
                'renderedContent'
            )
        );
    }
}