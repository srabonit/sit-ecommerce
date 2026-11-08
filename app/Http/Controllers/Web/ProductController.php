<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brand;
use App\Services\ImagePathService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Universal Image URL Helper (Connected to Central ImagePathService)
     */
    public static function getImageUrl($path)
    {
        $fallback = 'https://placehold.co/400x400?text=No+Image';
        if (empty($path)) {
            return $fallback;
        }

        return ImagePathService::resolveUrl($path, $fallback);
    }

    /**
     * Normalize Product, Gallery & Variant Images for Frontend Views & JS
     */
    protected function hydrateProductMediaUrls(Product $product): Product
    {
        if (!empty($product->thumbnail)) {
            $product->resolved_thumbnail_url = self::getImageUrl($product->thumbnail);
        } else {
            $product->resolved_thumbnail_url = 'https://placehold.co/400x400?text=No+Image';
        }

        if ($product->relationLoaded('galleries') && $product->galleries) {
            foreach ($product->galleries as $gallery) {
                $rawGal = $gallery->image_path ?? ($gallery->image ?? null);
                $gallery->resolved_url = !empty($rawGal) ? self::getImageUrl($rawGal) : null;
            }
        }

        if ($product->relationLoaded('variants') && $product->variants) {
            foreach ($product->variants as $variant) {
                if (!empty($variant->image)) {
                    $resolvedVarUrl = self::getImageUrl($variant->image);
                    $variant->image_url = $resolvedVarUrl;
                    $variant->resolved_image_url = $resolvedVarUrl;
                } else {
                    $variant->image_url = null;
                    $variant->resolved_image_url = null;
                }
            }
        }

        return $product;
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

    public function index(Request $request)
    {
        $hasCatPivot   = Schema::hasTable('product_category');
        $hasBrandPivot = Schema::hasTable('product_brand');
        $hasTagPivot   = Schema::hasTable('product_tag') && Schema::hasTable('tags');

        $categories = Category::withCount('products')->get();
        if (!$hasCatPivot || $categories->sum('products_count') == 0) {
            $categories = Category::withCount('directProducts as products_count')->get();
        }

        $withRelations = ['category', 'categories', 'brand', 'brands'];
        if ($hasTagPivot) {
            $withRelations[] = 'tags';
        }

        $query = Product::with($withRelations)->select('products.*')->distinct();
        $this->applyOnlineScope($query);

        // সার্চ কিওয়ার্ড ফিল্টারিং
        if ($request->filled('q') || $request->filled('search')) {
            $searchTerm = trim($request->input('q') ?? $request->input('search'));
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('sku', 'LIKE', "%{$searchTerm}%");
            });
        }

        // ক্যাটাগরি ফিল্টারিং (Parent + All Descendant Hierarchy Support)
        if ($request->filled('category') && $request->category !== 'all') {
            $catFilter = trim($request->category);

            $cat = Category::where('slug', $catFilter)->orWhere('id', $catFilter)->first();
            $subCat = null;
            if (!$cat && Schema::hasTable('sub_categories')) {
                $subCat = SubCategory::where('slug', $catFilter)->orWhere('id', $catFilter)->first();
            }

            if ($cat) {
                $catId = (int)$cat->id;
                $descendantSubIds = method_exists($cat, 'getAllDescendantSubCategoryIds') 
                    ? $cat->getAllDescendantSubCategoryIds() 
                    : [];

                $query->where(function ($q) use ($catId, $descendantSubIds, $hasCatPivot) {
                    $q->where('products.category_id', $catId);

                    if (!empty($descendantSubIds)) {
                        $q->orWhereIn('products.sub_category_id', $descendantSubIds);
                    }

                    if ($hasCatPivot) {
                        $q->orWhereExists(function ($subQ) use ($catId, $descendantSubIds) {
                            $subQ->select(DB::raw(1))
                                ->from('product_category')
                                ->whereColumn('product_category.product_id', 'products.id')
                                ->where(function ($pivotCheck) use ($catId, $descendantSubIds) {
                                    $pivotCheck->where(function($p) use ($catId) {
                                        $p->where('product_category.category_id', $catId)
                                          ->where('product_category.type', 'parent');
                                    });

                                    if (!empty($descendantSubIds)) {
                                        $pivotCheck->orWhere(function($c) use ($descendantSubIds) {
                                            $c->whereIn('product_category.category_id', $descendantSubIds)
                                              ->where('product_category.type', 'child');
                                        });
                                    }
                                });
                        });
                    }
                });
            } elseif ($subCat) {
                $childIds = method_exists($subCat, 'getAllDescendantIds') 
                    ? $subCat->getAllDescendantIds() 
                    : [(int)$subCat->id];

                $query->where(function ($q) use ($childIds, $hasCatPivot) {
                    $q->whereIn('products.sub_category_id', $childIds);

                    if ($hasCatPivot) {
                        $q->orWhereExists(function ($subQ) use ($childIds) {
                            $subQ->select(DB::raw(1))
                                ->from('product_category')
                                ->whereColumn('product_category.product_id', 'products.id')
                                ->whereIn('product_category.category_id', $childIds)
                                ->where('product_category.type', 'child');
                        });
                    }
                });
            }
        }

        // ব্র্যান্ড ফিল্টারিং
        if ($request->filled('brand') && $request->brand !== 'all') {
            $brandFilter = trim($request->brand);
            $b = Brand::where('slug', $brandFilter)->orWhere('id', $brandFilter)->first();

            if ($b) {
                $bId = (int)$b->id;
                $query->where(function ($q) use ($bId, $hasBrandPivot) {
                    $q->where('products.brand_id', $bId);

                    if ($hasBrandPivot) {
                        $q->orWhereExists(function ($sub) use ($bId) {
                            $sub->select(DB::raw(1))
                                ->from('product_brand')
                                ->whereColumn('product_brand.product_id', 'products.id')
                                ->where('product_brand.brand_id', $bId);
                        });
                    }
                });
            }
        }

        // ট্যাগ ফিল্টারিং (Frontend Shop Tag Filter Support)
        if ($request->filled('tag') && $request->tag !== 'all') {
            $tagFilter = trim($request->tag);
            if ($hasTagPivot) {
                $query->whereExists(function ($sub) use ($tagFilter) {
                    $sub->select(DB::raw(1))
                        ->from('product_tag')
                        ->join('tags', 'tags.id', '=', 'product_tag.tag_id')
                        ->whereColumn('product_tag.product_id', 'products.id')
                        ->where(function ($tq) use ($tagFilter) {
                            $tq->where('tags.slug', $tagFilter)
                               ->orWhere('tags.name', $tagFilter);
                        });
                });
            }
        }

        // সর্টিং ফিল্টারিং
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

        if ($request->ajax() || $request->wantsJson()) {
            $gridHtml = '';
            foreach ($products as $product) {
                $gridHtml .= '<div id="product-col-' . $product->id . '" class="product-card-wrap">';
                $gridHtml .= view('components.product-card', [
                    'product'      => $product,
                    'style'        => 'classic',
                    'btnStyle'     => 'filled',
                    'showCategory' => true,
                    'showReview'   => true,
                ])->render();
                $gridHtml .= '</div>';
            }

            if ($products->isEmpty()) {
                $gridHtml = '<div id="no-products-alert-box" class="col-12 text-center py-5 bg-white rounded-4 shadow-sm w-100" style="grid-column: 1 / -1;">
                    <i class="fas fa-box-open text-muted fs-1 mb-3"></i>
                    <p class="text-muted fs-5">No products found matching your selection.</p>
                    <a href="' . route('shop') . '" class="btn btn-primary rounded-pill px-4">Browse All Products</a>
                </div>';
            }

            $paginationHtml = $products->links('pagination::bootstrap-5')->render();
            $showingText = "Showing " . ($products->firstItem() ?? 0) . " to " . ($products->lastItem() ?? 0) . " of " . $products->total() . " items";

            return response()->json([
                'status'          => 'success',
                'grid_html'       => $gridHtml,
                'pagination_html' => $paginationHtml,
                'showing_text'    => $showingText,
                'total_count'     => $products->total()
            ]);
        }

        return view('frontend.shop', compact('products', 'categories'));
    }

    public function show($slug)
    {
        $hasTagsTable = Schema::hasTable('tags') && Schema::hasTable('product_tag');
        
        $withRelations = ['category', 'categories', 'brand', 'brands', 'galleries', 'variants'];
        if ($hasTagsTable) {
            $withRelations[] = 'tags';
        }

        $productQuery = Product::with($withRelations)->where('slug', $slug);
        $this->applyOnlineScope($productQuery);
        $product = $productQuery->firstOrFail();

        // Tags টেবিল ডেটাবেজে না থাকলেও ভিউ পেজ যাতে কখনো ক্র্যাশ না করে
        if (!$hasTagsTable) {
            $product->setRelation('tags', collect([]));
        }

        $this->hydrateProductMediaUrls($product);

        $videoEmbedUrl = null;
        if (!empty($product->video_url)) {
            if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $product->video_url, $matches)) {
                $videoEmbedUrl = 'https://www.youtube.com/embed/' . $matches[1];
            } else {
                $videoEmbedUrl = $product->video_url;
            }
        }

        $recentIds = session()->get('recently_viewed', []);
        $recentIds = array_diff($recentIds, [$product->id]);
        array_unshift($recentIds, $product->id);
        $recentIds = array_slice($recentIds, 0, 12);
        session()->put('recently_viewed', $recentIds);

        // Related products query (Category + Multiple Brands matching)
        $relatedProductsQuery = Product::where('id', '!=', $product->id)
            ->where(function ($q) use ($product) {
                if ($product->category_id) {
                    $q->where('category_id', $product->category_id);
                }
                if ($product->categories()->exists()) {
                    $catIds = $product->categories->pluck('id')->toArray();
                    $q->orWhereHas('categories', function ($sub) use ($catIds) {
                        $sub->whereIn('categories.id', $catIds);
                    });
                }
                if ($product->brand_id) {
                    $q->orWhere('brand_id', $product->brand_id);
                }
                if ($product->brands()->exists()) {
                    $brandIds = $product->brands->pluck('id')->toArray();
                    $q->orWhereHas('brands', function ($subB) use ($brandIds) {
                        $subB->whereIn('brands.id', $brandIds);
                    });
                }
            });
        $this->applyOnlineScope($relatedProductsQuery);
        $relatedProducts = $relatedProductsQuery->take(10)->get();

        $recentProductsQuery = Product::whereIn('id', array_diff($recentIds, [$product->id]));
        $this->applyOnlineScope($recentProductsQuery);
        $recentProducts = $recentProductsQuery->take(10)->get();

        $uiSettings = [
            'show_breadcrumb'        => true,
            'show_review_header'     => true,
            'show_wishlist'          => true,
            'show_social_share'      => true,
            'show_category'          => true,
            'show_brand'             => true,
            'show_tags'              => true,
            'show_sku'               => true,
            'show_whatsapp'          => true,
            'show_review_tab'        => true,
            'show_related'           => true,
            'show_recent'            => true,
            'show_delivery_sidebar'  => true,
            'store_brand_name'       => $product->brand->name ?? config('app.name', 'Shop Now BD'),
            'store_verified_badge'   => true,
            'show_delivery_options'  => true,
            'delivery_inside_dhaka'  => 70,
            'delivery_outside_dhaka' => 130,
            'delivery_dhaka_days'    => '2-3 days',
            'delivery_outside_days'  => '3-4 days',
            'show_cod_option'        => true,
            'show_return_warranty'   => true,
            'show_payment_methods'   => true,
            'active_payment_methods' => ['bKash', 'Nagad', 'Rocket', 'Visa', 'MasterCard', 'COD'],
            'show_sidebar_social'    => true,
            'custom_sidebar_html'    => '',
            'image_zoom_mode'        => 'lens',
            'slider_auto_speed'      => 3000,
        ];

        return view('frontend.product_details', compact(
            'product',
            'videoEmbedUrl',
            'relatedProducts',
            'recentProducts',
            'uiSettings'
        ));
    }

    public function quickViewModal($id)
    {
        $hasTagsTable = Schema::hasTable('tags') && Schema::hasTable('product_tag');
        
        $withRelations = ['galleries', 'variants', 'category', 'categories', 'brand', 'brands'];
        if ($hasTagsTable) {
            $withRelations[] = 'tags';
        }

        $productQuery = Product::with($withRelations)->where('id', $id);
        $this->applyOnlineScope($productQuery);
        $product = $productQuery->firstOrFail();
        
        if (!$hasTagsTable) {
            $product->setRelation('tags', collect([]));
        }

        $this->hydrateProductMediaUrls($product);

        return view('frontend.partials.quick_view_modal_body', compact('product'));
    }

    public function ajaxLiveSearch(Request $request)
    {
        $query = trim($request->input('query', ''));
        $categoryId = $request->input('category_id');

        if (strlen($query) < 2) {
            return response()->json(['status' => 'empty', 'products' => []]);
        }

        $productsQuery = Product::query()->select('products.*')->distinct();
        $this->applyOnlineScope($productsQuery);

        $productsQuery->where(function ($q) use ($query) {
            $q->where('title', 'LIKE', "%{$query}%")
                ->orWhere('sku', 'LIKE', "%{$query}%");
        });

        if (!empty($categoryId) && $categoryId !== 'all') {
            $cat = Category::find($categoryId);
            $descendantSubIds = $cat ? $cat->getAllDescendantSubCategoryIds() : [];

            $productsQuery->where(function ($q) use ($categoryId, $descendantSubIds) {
                $q->where('category_id', $categoryId);

                if (!empty($descendantSubIds)) {
                    $q->orWhereIn('sub_category_id', $descendantSubIds);
                }

                if (Schema::hasTable('product_category')) {
                    $q->orWhereExists(function ($sub) use ($categoryId, $descendantSubIds) {
                        $sub->select(DB::raw(1))
                            ->from('product_category')
                            ->whereColumn('product_category.product_id', 'products.id')
                            ->where(function ($pCheck) use ($categoryId, $descendantSubIds) {
                                $pCheck->where('product_category.category_id', $categoryId);
                                if (!empty($descendantSubIds)) {
                                    $pCheck->orWhereIn('product_category.category_id', $descendantSubIds);
                                }
                            });
                    });
                }
            });
        }

        $products = $productsQuery->latest()->take(8)->get()->map(function ($product) {
            $price = $product->sale_price ?? $product->regular_price ?? $product->price ?? 0;
            $img = self::getImageUrl($product->thumbnail);

            return [
                'id'    => $product->id,
                'title' => $product->title,
                'sku'   => $product->sku ?? '',
                'price' => number_format((float) $price, 2),
                'image' => $img,
                'url'   => route('product.details', $product->slug ?? $product->id),
            ];
        });

        return response()->json([
            'status'   => 'success',
            'products' => $products
        ]);
    }
}