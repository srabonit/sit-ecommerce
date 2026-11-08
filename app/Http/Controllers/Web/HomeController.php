<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brand;
use App\Services\ShortcodeParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    /**
     * Universal Image URL Resolver
     */
    protected function resolveImageUrl($path)
    {
        if (empty($path)) {
            return 'https://placehold.co/400x400?text=Product';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $clean = ltrim($path, '/');
        if (str_starts_with($clean, 'uploads/')) {
            return asset($clean);
        }
        if (str_starts_with($clean, 'storage/')) {
            return asset($clean);
        }

        return asset('storage/' . $clean);
    }

    /**
     * Schema-safe Online Storefront Scope:
     * Hides branch-restricted and POS-only products from public website.
     */
    protected function applyOnlineScope($query)
    {
        if (Schema::hasColumn('products', 'is_active')) {
            $query->where('products.is_active', true);
        } elseif (Schema::hasColumn('products', 'status')) {
            $query->where(function ($sq) {
                $sq->where('products.status', 'active')
                   ->orWhere('products.status', 1)
                   ->orWhere('products.status', '1');
            });
        }

        if (Schema::hasColumn('products', 'is_sellable_online')) {
            $query->where('products.is_sellable_online', true);
        }

        if (Schema::hasColumn('products', 'branch_availability_mode')) {
            $query->where(function ($bQ) {
                $bQ->whereNull('products.branch_availability_mode')
                   ->orWhereNotIn('products.branch_availability_mode', ['pos_only', 'branch_only']);
            });
        }

        return $query;
    }

    public function index()
    {
        // ১. ক্যাটাগরি লিস্ট (মাল্টিপল প্রোডাক্ট পিভট সহ কাউন্ট)
        $hasPivot = Schema::hasTable('product_category');

        $categories = Category::withCount(['products' => function ($q) {
                // withCount via product_category pivot
            }])
            ->orderBy('name', 'asc')
            ->get();

        if (!$hasPivot || $categories->sum('products_count') == 0) {
            $categories = Category::withCount('directProducts as products_count')
                ->orderBy('name', 'asc')
                ->get();
        }

        // ২. পপুলার ক্যাটাগরি স্লাইডারের ডেটা রিড
        $popularCategoryIdsJson = DB::table('settings')->where('key', 'popular_category_ids')->value('value');
        $popularCategoryIds = !empty($popularCategoryIdsJson) ? json_decode($popularCategoryIdsJson, true) : [];

        if (!empty($popularCategoryIds) && is_array($popularCategoryIds)) {
            $intCatIds = array_values(array_filter(array_map('intval', $popularCategoryIds)));
            $popularCategories = Category::withCount('products')->whereIn('id', $intCatIds)->get();
        } else {
            $popularCategories = $categories;
        }

        // ৩. ফিচার্ড প্রোডাক্টস ডাইনামিক লিমিট, কলাম ও পেজিনেশন সেটিংস
        $themeSettings = DB::table('settings')->whereIn('key', [
            'show_featured_products_section',
            'featured_products_count',
            'featured_enable_pagination',
            'featured_grid_pc'
        ])->pluck('value', 'key');

        $isFeaturedActive = ($themeSettings['show_featured_products_section'] ?? '1') == '1';
        $featuredLimit = (int)($themeSettings['featured_products_count'] ?? 12);
        if ($featuredLimit <= 0) $featuredLimit = 12;
        $enablePagination = ($themeSettings['featured_enable_pagination'] ?? '1') == '1';
        $colPc = $themeSettings['featured_grid_pc'] ?? '4';

        $prodQuery = Product::with(['category', 'categories', 'brand'])->select('products.*');
        $this->applyOnlineScope($prodQuery);

        $prodQuery->where(function ($q) {
            $q->where('is_featured', 1)
              ->orWhere(function ($subQ) {
                  $subQ->latest('products.id');
              });
        });

        if ($enablePagination) {
            $featuredProducts = $prodQuery->paginate($featuredLimit, ['*'], 'featured_page')->withQueryString();
        } else {
            $featuredProducts = $prodQuery->take($featuredLimit)->get();
        }

        // ৪. ডিলস অ্যান্ড অফারস (ফ্ল্যাশ সেল) প্রোডাক্টস হ্যান্ডলিং
        $flashSaleConfig = Schema::hasTable('flash_sales') ? DB::table('flash_sales')->first() : null;
        $dealProducts = collect();
        $flashSaleProductImages = [];

        if ($flashSaleConfig && !empty($flashSaleConfig->product_ids)) {
            $rawIds = is_array($flashSaleConfig->product_ids) 
                ? $flashSaleConfig->product_ids 
                : json_decode($flashSaleConfig->product_ids, true);

            if (!empty($rawIds) && is_array($rawIds)) {
                $intIds = array_values(array_filter(array_map('intval', $rawIds)));
                if (!empty($intIds)) {
                    $dealQuery = Product::with(['category', 'categories', 'brand'])
                        ->select('products.*')
                        ->whereIn('products.id', $intIds);
                    $this->applyOnlineScope($dealQuery);
                    $dealProducts = $dealQuery->get();
                }
            }
        }

        if ($dealProducts->isEmpty()) {
            $dealQuery = Product::with(['category', 'categories', 'brand'])
                ->select('products.*')
                ->whereNotNull('products.sale_price')
                ->where('products.sale_price', '>', 0);
            $this->applyOnlineScope($dealQuery);
            $dealProducts = $dealQuery->latest('products.id')
                ->take(5)
                ->get();
        }

        if ($dealProducts->isEmpty()) {
            if ($featuredProducts instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                $dealProducts = $featuredProducts->getCollection()->take(5);
            } else {
                $dealProducts = $featuredProducts->take(5);
            }
        }

        foreach ($dealProducts as $p) {
            $img = $this->resolveImageUrl($p->thumbnail);
            $flashSaleProductImages[] = [
                'id'    => $p->id,
                'title' => $p->title,
                'image' => $img,
                'url'   => route('product.details', $p->slug ?? $p->id)
            ];
        }

        // ৫. হোম পেজ ডায়নামিক কাস্টম ব্লক এবং শর্টকোড পার্সিং (Elementor Style)
        $homeDynamicSections = collect();
        if (Schema::hasTable('home_sections')) {
            $homeDynamicSections = DB::table('home_sections')
                ->where('is_active', 1)
                ->orderBy('order_index', 'asc')
                ->get()
                ->map(function ($sec) {
                    if (!empty($sec->custom_content)) {
                        $sec->rendered_content = ShortcodeParser::parse($sec->custom_content);
                    }
                    return $sec;
                });
        }

        $homeSettings = [
            'show_widget'            => true,
            'widget_position'        => 'left',
            'custom_widget_html'     => null,
            'show_hero_slider'       => true,
            'show_offer_card'        => true,
            'show_deals'             => true,
            'show_cat_slider'        => true,
            'show_featured'          => $isFeaturedActive,
        ];

        $heroSliders = Schema::hasTable('hero_sliders') 
            ? DB::table('hero_sliders')->where('status', 1)->orderBy('serial', 'asc')->get() 
            : collect();

        $offerCards = Schema::hasTable('offer_cards') 
            ? DB::table('offer_cards')->where('status', 1)->orderBy('serial', 'asc')->get() 
            : collect();

        return view('frontend.home', compact(
            'categories',
            'popularCategories',
            'featuredProducts',
            'dealProducts',
            'homeSettings',
            'heroSliders',
            'flashSaleConfig',
            'offerCards',
            'flashSaleProductImages',
            'homeDynamicSections',
            'colPc'
        ));
    }

    /**
     * ক্যাটাগরি পেজে প্যারেন্ট ক্যাটাগরি এবং তার অধীনস্থ সমস্ত চাইল্ড ক্যাটাগরির প্রোডাক্ট প্রদর্শন
     */
    public function categoryProducts(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)->orWhere('id', $slug)->first();
        $isChild = false;
        $subCategory = null;

        if (!$category && Schema::hasTable('sub_categories')) {
            $subCategory = SubCategory::where('slug', $slug)->orWhere('id', $slug)->first();
            if ($subCategory) {
                $category = $subCategory;
                $isChild = true;
            }
        }

        if (!$category) {
            abort(404, 'Category not found');
        }

        $hasPivot = Schema::hasTable('product_category');
        $query = Product::with(['category', 'categories', 'brand'])->select('products.*')->distinct();
        $this->applyOnlineScope($query);

        if ($isChild) {
            // ১. যদি ইউজার সরাসরি কোনো Child Category পেজে থাকে:
            $childIds = method_exists($subCategory, 'getAllDescendantIds') 
                ? $subCategory->getAllDescendantIds() 
                : [(int)$subCategory->id];

            $query->where(function ($q) use ($childIds, $hasPivot) {
                $q->whereIn('products.sub_category_id', $childIds);

                if ($hasPivot) {
                    $q->orWhereExists(function ($sub) use ($childIds) {
                        $sub->select(DB::raw(1))
                            ->from('product_category')
                            ->whereColumn('product_category.product_id', 'products.id')
                            ->whereIn('product_category.category_id', $childIds)
                            ->where('product_category.type', 'child');
                    });
                }
            });
        } else {
            // ২. যদি ইউজার কোনো Main / Parent Category পেজে থাকে:
            $parentId = (int)$category->id;
            $descendantSubIds = method_exists($category, 'getAllDescendantSubCategoryIds') 
                ? $category->getAllDescendantSubCategoryIds() 
                : [];

            $query->where(function ($q) use ($parentId, $descendantSubIds, $hasPivot) {
                $q->where('products.category_id', $parentId);

                if (!empty($descendantSubIds)) {
                    $q->orWhereIn('products.sub_category_id', $descendantSubIds);
                }

                if ($hasPivot) {
                    $q->orWhereExists(function ($sub) use ($parentId, $descendantSubIds) {
                        $sub->select(DB::raw(1))
                            ->from('product_category')
                            ->whereColumn('product_category.product_id', 'products.id')
                            ->where(function ($pivotCheck) use ($parentId, $descendantSubIds) {
                                $pivotCheck->where(function ($p) use ($parentId) {
                                    $p->where('product_category.category_id', $parentId)
                                      ->where('product_category.type', 'parent');
                                });

                                if (!empty($descendantSubIds)) {
                                    $pivotCheck->orWhere(function ($c) use ($descendantSubIds) {
                                        $c->whereIn('product_category.category_id', $descendantSubIds)
                                          ->where('product_category.type', 'child');
                                    });
                                }
                            });
                    });
                }
            });
        }

        // সর্টিং লজিক
        $sort = $request->input('sort', 'latest');
        if ($sort === 'price_low') {
            $query->orderBy(DB::raw('COALESCE(sale_price, regular_price)'), 'asc');
        } elseif ($sort === 'price_high') {
            $query->orderBy(DB::raw('COALESCE(sale_price, regular_price)'), 'desc');
        } elseif ($sort === 'sale') {
            $query->whereNotNull('sale_price')->where('sale_price', '>', 0)->latest('id');
        } elseif ($sort === 'in_stock') {
            $query->where('stock', '>', 0)->latest('id');
        } else {
            $query->latest('id');
        }

        $dbPerPage = DB::table('settings')->where('key', 'products_per_page')->value('value')
                  ?? DB::table('settings')->where('key', 'product_per_page')->value('value')
                  ?? 12;

        $perPage = ((int) $dbPerPage > 0) ? (int) $dbPerPage : 12;

        $products = $query->paginate($perPage)->withQueryString();

        $categories = Category::withCount('products')->get();
        if (!$hasPivot || $categories->sum('products_count') == 0) {
            $categories = Category::withCount('directProducts as products_count')->get();
        }

        return view('frontend.shop', compact('products', 'category', 'categories'));
    }

    /**
     * AJAX প্রোডাক্ট ফিল্টার (Hierarchy সহ)
     */
    public function filterProducts(Request $request)
    {
        $categoryId = $request->input('category_id');
        $page = (int)$request->input('featured_page', 1);

        $settings = DB::table('settings')->whereIn('key', [
            'featured_products_count',
            'featured_enable_pagination'
        ])->pluck('value', 'key');

        $perPage = (int)($settings['featured_products_count'] ?? 12);
        if ($perPage <= 0) $perPage = 12;
        $enablePagination = ($settings['featured_enable_pagination'] ?? '1') == '1';

        $query = Product::with(['category', 'categories', 'brand'])->select('products.*')->distinct();
        $this->applyOnlineScope($query);

        if ($categoryId && $categoryId !== 'all') {
            $catId = (int)$categoryId;
            $hasPivot = Schema::hasTable('product_category');

            $category = Category::find($catId);
            $descendantSubIds = $category ? $category->getAllDescendantSubCategoryIds() : [];

            $query->where(function ($q) use ($catId, $descendantSubIds, $hasPivot) {
                $q->where('products.category_id', $catId);

                if (!empty($descendantSubIds)) {
                    $q->orWhereIn('products.sub_category_id', $descendantSubIds);
                }

                if ($hasPivot) {
                    $q->orWhereExists(function ($sub) use ($catId, $descendantSubIds) {
                        $sub->select(DB::raw(1))
                            ->from('product_category')
                            ->whereColumn('product_category.product_id', 'products.id')
                            ->where(function ($pivotCheck) use ($catId, $descendantSubIds) {
                                $pivotCheck->where('product_category.category_id', $catId);
                                if (!empty($descendantSubIds)) {
                                    $pivotCheck->orWhereIn('product_category.category_id', $descendantSubIds);
                                }
                            });
                    });
                }
            });
        } else {
            $query->where(function ($q) {
                $q->where('is_featured', 1)
                  ->orWhere(fn($sub) => $sub->latest('products.id'));
            });
        }

        if ($enablePagination) {
            $products = $query->latest('id')->paginate($perPage, ['*'], 'featured_page', $page);
        } else {
            $products = $query->latest('id')->take($perPage)->get();
        }

        $html = '';
        $itemCollection = ($products instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $products->getCollection() : $products;

        foreach ($itemCollection as $product) {
            $html .= '<div class="product-card-wrap">';
            $html .= view('components.product-card', ['product' => $product])->render();
            $html .= '</div>';
        }

        if ($itemCollection->isEmpty()) {
            $html = '<div class="col-12 text-center text-muted py-5" style="grid-column: 1 / -1;">No products found in this category.</div>';
        }

        $paginationHtml = '';
        if ($enablePagination && $products instanceof \Illuminate\Pagination\LengthAwarePaginator && $products->hasPages()) {
            $paginationHtml = (string)$products->onEachSide(1)->links('pagination::bootstrap-5');
        }

        return response()->json([
            'status'          => 'success',
            'html'            => $html,
            'pagination_html' => $paginationHtml,
            'count'           => ($products instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $products->total() : $products->count()
        ]);
    }
}