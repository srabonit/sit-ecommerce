<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\Form;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ShortcodeParser
{
    protected static $filters = [];

    public static function addFilter($tag, callable $callback)
    {
        self::$filters[$tag][] = $callback;
    }

    public static function applyFilters($tag, $value, ...$args)
    {
        if (isset(self::$filters[$tag])) {
            foreach (self::$filters[$tag] as $callback) {
                $value = call_user_func_array(
                    $callback,
                    array_merge([$value], $args)
                );
            }
        }

        return $value;
    }

    public static function parse($content)
    {
        if (empty($content)) {
            return '';
        }

        /*
        |--------------------------------------------------------------------------
        | 1. FORM SHORTCODE
        |--------------------------------------------------------------------------
        |
        | Supported:
        |
        | [form id="1"]
        | [form slug="contact-us"]
        |
        */

        $content = preg_replace_callback(
            '/\[form\s+([^\]]*)\]/i',
            function ($matches) {

                $attrs = self::parseAttributes($matches[1] ?? '');

                $form = null;

                /*
                 * Find by ID
                 */
                if (!empty($attrs['id'])) {

                    $form = Form::with([
                        'fields' => function ($query) {
                            $query->where('is_active', true)
                                ->orderBy('sort_order', 'asc')
                                ->orderBy('id', 'asc');
                        }
                    ])
                    ->where('id', (int) $attrs['id'])
                    ->where('is_active', true)
                    ->first();
                }

                /*
                 * Find by slug
                 */
                elseif (!empty($attrs['slug'])) {

                    $form = Form::with([
                        'fields' => function ($query) {
                            $query->where('is_active', true)
                                ->orderBy('sort_order', 'asc')
                                ->orderBy('id', 'asc');
                        }
                    ])
                    ->where('slug', $attrs['slug'])
                    ->where('is_active', true)
                    ->first();
                }

                /*
                 * Form not found
                 */
                if (!$form) {
                    return '
                        <div class="alert alert-warning my-3">
                            <strong>Form not found.</strong>
                            Please check the form ID or slug.
                        </div>
                    ';
                }

                /*
                 * Render form view
                 */
                if (view()->exists('frontend.forms.render')) {

                    return view('frontend.forms.render', [
                        'form' => $form,
                    ])->render();
                }

                return '
                    <div class="alert alert-danger my-3">
                        <strong>Form renderer view not found.</strong>
                        Please check:
                        resources/views/frontend/forms/render.blade.php
                    </div>
                ';
            },
            $content
        );


        // ------------------------------------------------------------------
        // 2. [products ...] & [sitcommerce_products ...]
        // ------------------------------------------------------------------

        $content = preg_replace_callback(
            '/\[(?:sitcommerce_)?products\s*([^\]]*)\]/i',
            function ($matches) {

                $attrs = self::parseAttributes($matches[1] ?? '');

                return self::renderProducts($attrs);
            },
            $content
        );


        // ------------------------------------------------------------------
        // 3. [featured_products ...]
        // ------------------------------------------------------------------

        $content = preg_replace_callback(
            '/\[(?:sitcommerce_)?featured_products\s*([^\]]*)\]/i',
            function ($matches) {

                $attrs = self::parseAttributes($matches[1] ?? '');

                $attrs['visibility'] = 'featured';

                return self::renderProducts($attrs);
            },
            $content
        );


        // ------------------------------------------------------------------
        // 4. [sale_products ...]
        // ------------------------------------------------------------------

        $content = preg_replace_callback(
            '/\[(?:sitcommerce_)?sale_products\s*([^\]]*)\]/i',
            function ($matches) {

                $attrs = self::parseAttributes($matches[1] ?? '');

                $attrs['on_sale'] = 'true';

                return self::renderProducts($attrs);
            },
            $content
        );


        // ------------------------------------------------------------------
        // 5. [recent_products ...]
        // ------------------------------------------------------------------

        $content = preg_replace_callback(
            '/\[(?:sitcommerce_)?recent_products\s*([^\]]*)\]/i',
            function ($matches) {

                $attrs = self::parseAttributes($matches[1] ?? '');

                $attrs['orderby'] = 'date';
                $attrs['order'] = 'DESC';

                return self::renderProducts($attrs);
            },
            $content
        );


        // ------------------------------------------------------------------
        // 6. [best_selling_products ...] & [top_rated_products ...]
        // ------------------------------------------------------------------

        $content = preg_replace_callback(
            '/\[(?:sitcommerce_)?(best_selling_products|top_rated_products)\s*([^\]]*)\]/i',
            function ($matches) {

                $attrs = self::parseAttributes($matches[2] ?? '');

                $attrs['orderby'] =
                    ($matches[1] === 'top_rated_products')
                        ? 'rating'
                        : 'popularity';

                return self::renderProducts($attrs);
            },
            $content
        );


        // ------------------------------------------------------------------
        // 7. [related_products ...]
        // ------------------------------------------------------------------

        $content = preg_replace_callback(
            '/\[(?:sitcommerce_)?related_products\s*([^\]]*)\]/i',
            function ($matches) {

                $attrs = self::parseAttributes($matches[1] ?? '');

                $attrs['orderby'] = 'rand';

                return self::renderProducts($attrs);
            },
            $content
        );


        // ------------------------------------------------------------------
        // 8. [product_category ...]
        // ------------------------------------------------------------------

        $content = preg_replace_callback(
            '/\[(?:sitcommerce_)?product_category\s*([^\]]*)\]/i',
            function ($matches) {

                $attrs = self::parseAttributes($matches[1] ?? '');

                return self::renderProducts($attrs);
            },
            $content
        );


        // ------------------------------------------------------------------
        // 9. [product_categories ...] / [categories_grid ...]
        // ------------------------------------------------------------------

        $content = preg_replace_callback(
            '/\[(?:sitcommerce_)?(product_categories|categories_grid)\s*([^\]]*)\]/i',
            function ($matches) {

                $attrs = self::parseAttributes($matches[2] ?? '');

                return self::renderCategoriesGrid($attrs);
            },
            $content
        );


        // ------------------------------------------------------------------
        // 10. Single product
        // [product id="123"]
        // [product_page sku="ABC"]
        // ------------------------------------------------------------------

        $content = preg_replace_callback(
            '/\[(?:sitcommerce_)?(product|product_page)\s*([^\]]*)\]/i',
            function ($matches) {

                $attrs = self::parseAttributes($matches[2] ?? '');

                $attrs['limit'] = 1;

                return self::renderProducts($attrs);
            },
            $content
        );


        // ------------------------------------------------------------------
        // 11. Slider
        // [slider]
        // [sitcommerce_slider]
        // ------------------------------------------------------------------

        $content = preg_replace_callback(
            '/\[(?:sitcommerce_)?slider\]/i',
            function () {

                if (view()->exists('components.shortcode-slider')) {

                    $sliders = DB::table('hero_sliders')
                        ->where('status', 1)
                        ->orderBy('serial', 'asc')
                        ->get();

                    return view(
                        'components.shortcode-slider',
                        compact('sliders')
                    )->render();
                }

                return '';
            },
            $content
        );


        // ------------------------------------------------------------------
        // 12. Native SitCommerce Components
        // ------------------------------------------------------------------

        $content = str_ireplace(
            '[sitcommerce_cart]',
            '<div class="text-center my-3">
                <a href="' . route('cart.index') . '"
                   class="btn btn-primary rounded-pill px-4">
                    View Cart
                </a>
            </div>',
            $content
        );


        $content = str_ireplace(
            '[sitcommerce_checkout]',
            '<div class="text-center my-3">
                <a href="' . route('checkout') . '"
                   class="btn btn-success rounded-pill px-4">
                    Proceed to Checkout
                </a>
            </div>',
            $content
        );


        $content = str_ireplace(
            '[sitcommerce_my_account]',
            '<div class="text-center my-3">
                <a href="' . route('login') . '"
                   class="btn btn-outline-primary rounded-pill px-4">
                    My Account
                </a>
            </div>',
            $content
        );


        $content = str_ireplace(
            '[sitcommerce_order_tracking]',
            '<div class="text-center my-3">
                <a href="' . url('/track-order') . '"
                   class="btn btn-dark rounded-pill px-4">
                    Track Order
                </a>
            </div>',
            $content
        );


        return $content;
    }


    /*
    |--------------------------------------------------------------------------
    | Parse Shortcode Attributes
    |--------------------------------------------------------------------------
    |
    | Examples:
    |
    | id="1"
    | slug="contact-us"
    | columns="4"
    | limit="8"
    |
    */

    private static function parseAttributes($text)
    {
        $attributes = [];

        $pattern = '/(\w+)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s]+))/';

        if (preg_match_all(
            $pattern,
            $text,
            $matches,
            PREG_SET_ORDER
        )) {

            foreach ($matches as $match) {

                $key = strtolower($match[1]);

                $value = '';

                if (isset($match[2]) && $match[2] !== '') {
                    $value = $match[2];
                } elseif (isset($match[3]) && $match[3] !== '') {
                    $value = $match[3];
                } elseif (isset($match[4])) {
                    $value = $match[4];
                }

                $attributes[$key] = trim($value);
            }
        }

        return $attributes;
    }


    /*
    |--------------------------------------------------------------------------
    | Render Products
    |--------------------------------------------------------------------------
    */

    private static function renderProducts($attrs)
    {
        $attrs = self::applyFilters(
            'sitcommerce_shortcode_products_query',
            $attrs
        );

        $limit = isset($attrs['limit'])
            ? (int) $attrs['limit']
            : 8;

        $columns = isset($attrs['columns'])
            ? (int) $attrs['columns']
            : 4;

        $customClass = $attrs['class'] ?? '';

        $isPaginated =
            isset($attrs['paginate']) &&
            in_array(
                strtolower($attrs['paginate']),
                ['true', '1', 'yes']
            );

        $rawQueryToken = substr(
            md5(json_encode($attrs)),
            0,
            8
        );

        $pageParamName = 'sc_p_' . $rawQueryToken;

        $query = Product::with([
            'category',
            'brand'
        ]);


        // IDs

        if (!empty($attrs['ids'])) {

            $ids = array_filter(
                array_map(
                    'trim',
                    explode(',', $attrs['ids'])
                )
            );

            $query->whereIn('id', $ids);
        }


        // Single ID

        if (!empty($attrs['id'])) {

            $query->where(
                'id',
                trim($attrs['id'])
            );
        }


        // Exclude

        if (!empty($attrs['exclude'])) {

            $excludeIds = array_filter(
                array_map(
                    'trim',
                    explode(',', $attrs['exclude'])
                )
            );

            $query->whereNotIn(
                'id',
                $excludeIds
            );
        }


        // SKUs

        if (!empty($attrs['skus'])) {

            $skus = array_filter(
                array_map(
                    'trim',
                    explode(',', $attrs['skus'])
                )
            );

            $query->whereIn(
                'sku',
                $skus
            );
        }


        // Single SKU

        if (!empty($attrs['sku'])) {

            $query->where(
                'sku',
                trim($attrs['sku'])
            );
        }


        // Category

        if (!empty($attrs['category'])) {

            $catTerms = array_filter(
                array_map(
                    'trim',
                    explode(
                        ',',
                        strtolower($attrs['category'])
                    )
                )
            );

            $operator = strtoupper(
                $attrs['category_operator'] ?? 'IN'
            );


            if ($operator === 'NOT IN') {

                $query->whereDoesntHave(
                    'category',
                    function ($q) use ($catTerms) {

                        $q->whereIn(
                            DB::raw('LOWER(name)'),
                            $catTerms
                        )
                        ->orWhereIn(
                            'slug',
                            $catTerms
                        );
                    }
                );

            } else {

                $query->whereHas(
                    'category',
                    function ($q) use ($catTerms) {

                        $q->whereIn(
                            DB::raw('LOWER(name)'),
                            $catTerms
                        )
                        ->orWhereIn(
                            'slug',
                            $catTerms
                        );
                    }
                );
            }
        }


        // Tag

        if (!empty($attrs['tag'])) {

            $tag = trim($attrs['tag']);

            if (Schema::hasColumn('products', 'tags')) {

                $query->where(
                    'tags',
                    'LIKE',
                    "%{$tag}%"
                );
            }
        }


        // Visibility

        if (!empty($attrs['visibility'])) {

            $v = strtolower(
                $attrs['visibility']
            );

            if (
                $v === 'featured' &&
                Schema::hasColumn(
                    'products',
                    'is_featured'
                )
            ) {

                $query->where(
                    'is_featured',
                    1
                );

            } elseif (
                $v === 'visible' &&
                Schema::hasColumn(
                    'products',
                    'is_active'
                )
            ) {

                $query->where(
                    'is_active',
                    1
                );

            } elseif (
                $v === 'hidden' &&
                Schema::hasColumn(
                    'products',
                    'is_active'
                )
            ) {

                $query->where(
                    'is_active',
                    0
                );
            }
        }


        // Sale

        if (
            isset($attrs['on_sale']) &&
            in_array(
                strtolower($attrs['on_sale']),
                ['true', '1', 'yes']
            )
        ) {

            $query
                ->whereNotNull('sale_price')
                ->where(
                    'sale_price',
                    '>',
                    0
                );
        }


        // Minimum price

        if (isset($attrs['min_price'])) {

            $query->where(
                DB::raw(
                    'COALESCE(sale_price, regular_price)'
                ),
                '>=',
                (float) $attrs['min_price']
            );
        }


        // Maximum price

        if (isset($attrs['max_price'])) {

            $query->where(
                DB::raw(
                    'COALESCE(sale_price, regular_price)'
                ),
                '<=',
                (float) $attrs['max_price']
            );
        }


        // Stock

        if (!empty($attrs['stock_status'])) {

            $st = strtolower(
                $attrs['stock_status']
            );

            if ($st === 'instock') {

                $query->where(
                    'stock',
                    '>',
                    0
                );

            } elseif ($st === 'outofstock') {

                $query->where(function ($q) {

                    $q->where(
                        'stock',
                        '<=',
                        0
                    )
                    ->orWhere(
                        'stock_status',
                        'out_of_stock'
                    );
                });

            } elseif ($st === 'onbackorder') {

                $query->where(
                    'stock_status',
                    'onbackorder'
                );
            }
        }


        // Minimum rating

        if (
            !empty($attrs['min_rating']) &&
            Schema::hasTable('product_reviews')
        ) {

            $minRating = (float) $attrs['min_rating'];

            $query->whereIn(
                'id',
                function ($sub) use ($minRating) {

                    $sub
                        ->select('product_id')
                        ->from('product_reviews')
                        ->where(
                            'status',
                            'approved'
                        )
                        ->groupBy('product_id')
                        ->havingRaw(
                            'AVG(rating) >= ?',
                            [$minRating]
                        );
                }
            );
        }


        // Product attributes

        if (
            !empty($attrs['attribute']) &&
            !empty($attrs['terms'])
        ) {

            $attrName = strtolower(
                trim($attrs['attribute'])
            );

            $terms = array_filter(
                array_map(
                    'trim',
                    explode(
                        ',',
                        strtolower($attrs['terms'])
                    )
                )
            );


            if (Schema::hasTable('product_variants')) {

                $query->whereHas(
                    'variants',
                    function ($vq) use (
                        $attrName,
                        $terms
                    ) {

                        if ($attrName === 'color') {

                            $vq->whereIn(
                                DB::raw('LOWER(color)'),
                                $terms
                            );

                        } elseif ($attrName === 'size') {

                            $vq->whereIn(
                                DB::raw('LOWER(size)'),
                                $terms
                            );
                        }
                    }
                );
            }
        }


        // Order

        $orderby = strtolower(
            $attrs['orderby'] ?? 'date'
        );

        $order = strtoupper(
            $attrs['order'] ?? 'DESC'
        );

        if (!in_array(
            $order,
            ['ASC', 'DESC']
        )) {

            $order = 'DESC';
        }


        switch ($orderby) {

            case 'price':

                $query->orderBy(
                    DB::raw(
                        'COALESCE(sale_price, regular_price)'
                    ),
                    $order
                );

                break;


            case 'title':

                $query->orderBy(
                    'title',
                    $order
                );

                break;


            case 'rand':

                $query->inRandomOrder();

                break;


            case 'id':

                $query->orderBy(
                    'id',
                    $order
                );

                break;


            case 'popularity':

                if (Schema::hasTable('order_items')) {

                    $query
                        ->withCount('orderItems')
                        ->orderBy(
                            'order_items_count',
                            'desc'
                        );

                } else {

                    $query->latest('id');
                }

                break;


            case 'rating':

                if (Schema::hasTable('product_reviews')) {

                    $query
                        ->withAvg(
                            [
                                'reviews' => function ($rq) {

                                    $rq->where(
                                        'status',
                                        'approved'
                                    );
                                }
                            ],
                            'rating'
                        )
                        ->orderBy(
                            'reviews_avg_rating',
                            'desc'
                        );

                } else {

                    $query->latest('id');
                }

                break;


            default:

                $query->orderBy(
                    'id',
                    $order
                );

                break;
        }


        // Get products

        $products = $isPaginated

            ? $query
                ->paginate(
                    $limit,
                    ['*'],
                    $pageParamName
                )
                ->withQueryString()

            : $query
                ->take($limit)
                ->get();


        // Empty

        if ($products->isEmpty()) {

            return '
                <div class="alert alert-light text-center py-3 text-muted my-2">
                    No products found for this selection.
                </div>
            ';
        }


        $wrapperId =
            'sc-ajax-wrap-' .
            $rawQueryToken;


        $html =
            '<div class="sitcommerce-ajax-section-wrapper position-relative"
                  id="' . $wrapperId . '">';


        $html .=
            '<div class="dynamic-product-grid my-2 ' .
            e($customClass) .
            '"
            style="
                --col-mobile: 2;
                --col-tab: 3;
                --col-pc: ' . $columns . ';
            " >';


        foreach ($products as $p) {

            $html .=
                '<div class="product-card-wrap">' .
                view(
                    'components.product-card',
                    [
                        'product' => $p
                    ]
                )->render() .
                '</div>';
        }


        $html .= '</div>';


        // Pagination

        if (
            $isPaginated &&
            method_exists(
                $products,
                'links'
            )
        ) {

            $paginationOutput =
                $products
                    ->onEachSide(1)
                    ->links(
                        'pagination::bootstrap-5'
                    );

            $html .=
                '<div
                    class="d-flex justify-content-center mt-3 pt-2 sc-pagination-box"
                    data-parent="' . $wrapperId . '"
                >' .
                (string) $paginationOutput .
                '</div>';
        }


        $html .= '</div>';


        return $html;
    }


    /*
    |--------------------------------------------------------------------------
    | Render Categories Grid
    |--------------------------------------------------------------------------
    */

    private static function renderCategoriesGrid($attrs)
    {
        $count =
            isset($attrs['number'])
                ? (int) $attrs['number']
                : (
                    isset($attrs['count'])
                        ? (int) $attrs['count']
                        : 20
                );


        $categories = Category::withCount('products')
            ->orderBy('name', 'asc')
            ->take($count)
            ->get();


        if ($categories->isEmpty()) {
            return '';
        }


        $isSlider =
            $categories->count() > 8;


        $loopCategories =
            $isSlider
                ? $categories->concat($categories)
                : $categories;


        $html = '
        <style>

            .shortcode-cat-scroller {
                overflow: hidden;
                position: relative;
                width: 100%;
                padding: 6px 0;
            }

            .shortcode-cat-track {
                display: flex;
                gap: 12px;
                width: max-content;
                animation: shortcodeCatScroll 45s linear infinite;
            }

            .shortcode-cat-track:hover {
                animation-play-state: paused;
            }

            @keyframes shortcodeCatScroll {
                0% {
                    transform: translateX(0);
                }

                100% {
                    transform: translateX(-50%);
                }
            }

            .shortcode-cat-grid {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                justify-content: center;
                padding: 6px 0;
            }

            .shortcode-cat-card {
                width: 115px;
                background: #ffffff;
                border: 1px solid #edf2f7;
                border-radius: 14px;
                padding: 12px 8px;
                text-align: center;
                text-decoration: none;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                box-shadow: 0 2px 8px rgba(0,0,0,0.02);
                transition: all 0.2s ease;
                flex-shrink: 0;
            }

            @media (min-width: 768px) {

                .shortcode-cat-card {
                    width: 130px;
                    padding: 14px 10px;
                    border-radius: 16px;
                }
            }

            .shortcode-cat-card:hover {
                border-color: #3b82f6;
                transform: translateY(-3px);
                box-shadow: 0 6px 16px rgba(59, 130, 246, 0.12);
            }

            .shortcode-cat-img-box {
                width: 48px;
                height: 48px;
                border-radius: 50%;
                background: #f1f5f9;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 8px;
                overflow: hidden;
            }

            @media (min-width: 768px) {

                .shortcode-cat-img-box {
                    width: 54px;
                    height: 54px;
                    margin-bottom: 10px;
                }
            }

            .shortcode-cat-img-box img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

        </style>
        ';


        if ($isSlider) {

            $html .=
                '<div class="shortcode-cat-scroller">
                    <div class="shortcode-cat-track">';

        } else {

            $html .=
                '<div class="shortcode-cat-grid">';
        }


        foreach ($loopCategories as $cat) {

            $catUrl =
                route(
                    'category.products',
                    $cat->slug ?? $cat->id
                );


            $catImg = null;

            $rawImg =
                $cat->image ??
                ($cat->thumbnail ?? null);


            if (!empty($rawImg)) {

                if (
                    str_starts_with(
                        $rawImg,
                        'http'
                    )
                ) {

                    $catImg = $rawImg;

                } elseif (
                    file_exists(
                        public_path(
                            'storage/' . $rawImg
                        )
                    )
                ) {

                    $catImg =
                        asset(
                            'storage/' . $rawImg
                        );

                } elseif (
                    file_exists(
                        public_path($rawImg)
                    )
                ) {

                    $catImg =
                        asset($rawImg);

                } else {

                    $catImg =
                        asset(
                            'storage/' .
                            ltrim(
                                $rawImg,
                                '/'
                            )
                        );
                }
            }


            $iconClass =
                !empty($cat->icon)
                    ? $cat->icon
                    : 'fas fa-tag';


            $html .=
                '<a href="' . $catUrl . '"
                    class="shortcode-cat-card">

                    <div class="shortcode-cat-img-box">';


            if ($catImg) {

                $html .=
                    '<img
                        src="' . $catImg . '"
                        alt="' . e($cat->name) . '"
                    >';

            } else {

                $html .=
                    '<i class="' .
                    $iconClass .
                    ' text-secondary fs-5">
                    </i>';
            }


            $html .=
                '</div>

                <span
                    class="fw-bold text-dark small text-truncate w-100"
                    style="font-size: 11.5px;"
                    title="' . e($cat->name) . '"
                >' .
                    e($cat->name) .
                '</span>

                <small
                    class="text-muted"
                    style="font-size: 10px;"
                >' .
                    ($cat->products_count ?? 0) .
                    ' Items
                </small>

            </a>';
        }


        $html .=
            $isSlider
                ? '</div></div>'
                : '</div>';


        return $html;
    }
}