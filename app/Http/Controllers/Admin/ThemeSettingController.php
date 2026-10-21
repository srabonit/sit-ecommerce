<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImagePathService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Category;

class ThemeSettingController extends Controller
{
    /**
     * Dynamic Public Web Root Detector (Localhost & cPanel Safe)
     */
    protected function getPublicRoot(): string
    {
        return ImagePathService::resolvePublicRoot();
    }

    /**
     * Normalize Media Library or Input Path
     */
    protected function normalizeMediaInputPath($inputPath): ?string
    {
        if (empty($inputPath) || !is_string($inputPath)) {
            return null;
        }
        $inputPath = trim($inputPath);
        if ($inputPath === '' || $inputPath === 'undefined' || $inputPath === 'null') {
            return null;
        }

        if (filter_var($inputPath, FILTER_VALIDATE_URL)) {
            $parsedHost = parse_url($inputPath, PHP_URL_HOST);
            $currentHost = request()->getHost();
            if ($parsedHost && in_array($parsedHost, [$currentHost, '127.0.0.1', 'localhost'], true)) {
                $parsedPath = parse_url($inputPath, PHP_URL_PATH);
                if (!empty($parsedPath)) {
                    $inputPath = ltrim($parsedPath, '/');
                }
            } else {
                return $inputPath;
            }
        }

        $clean = ltrim(str_replace('\\', '/', $inputPath), '/');
        $clean = preg_replace('#^(public_html/|public/)#i', '', $clean);

        return $clean !== '' ? $clean : null;
    }

    /**
     * Universal File Upload Helper for Theme (Mirrors across all public roots & auto-registers in Media Library)
     */
    protected function saveThemeFile($file, $subFolder): string
    {
        $originalName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $cleanBase = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'theme';
        $filename = time() . '_' . uniqid() . '_' . $cleanBase . '.' . $ext;
        $relativeDir = trim($subFolder, '/');
        $relativePath = 'uploads/' . $relativeDir . '/' . $filename;

        $primaryRoot = $this->getPublicRoot();
        $primaryDir = $primaryRoot . '/uploads/' . $relativeDir;
        if (!is_dir($primaryDir)) {
            @mkdir($primaryDir, 0777, true);
        }

        $primaryFullPath = $primaryDir . '/' . $filename;
        $file->move($primaryDir, $filename);

        foreach (ImagePathService::getAllPublicRoots() as $root) {
            $mirrorDir = $root . '/uploads/' . $relativeDir;
            $mirrorFile = $mirrorDir . '/' . $filename;
            if ($mirrorFile !== $primaryFullPath) {
                if (!is_dir($mirrorDir)) {
                    @mkdir($mirrorDir, 0777, true);
                }
                @copy($primaryFullPath, $mirrorFile);
            }
        }

        try {
            $storageDir = storage_path('app/public/' . $relativeDir);
            if (!is_dir($storageDir)) {
                @mkdir($storageDir, 0777, true);
            }
            @copy($primaryFullPath, $storageDir . '/' . $filename);
        } catch (\Throwable $e) {
            // Ignore storage mirror errors safely
        }

        if (Schema::hasTable('media_files')) {
            try {
                $mimeType = @mime_content_type($primaryFullPath) ?: 'image/jpeg';
                $fileSize = @filesize($primaryFullPath) ?: 0;
                $imgInfo  = @getimagesize($primaryFullPath);
                $dims     = $imgInfo ? ($imgInfo[0] . ' × ' . $imgInfo[1]) : null;
                $title    = ucwords(str_replace(['-', '_'], ' ', pathinfo($originalName, PATHINFO_FILENAME)));
                $sourceMod = str_contains($relativeDir, 'offer') ? 'theme_offer' : 'slider';

                $mediaInsert = [
                    'file_name'  => $originalName,
                    'file_path'  => $relativePath,
                    'file_type'  => $mimeType,
                    'file_size'  => $fileSize,
                    'dimensions' => $dims,
                    'title'      => $title,
                    'alt_text'   => $title,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('media_files', 'media_category')) {
                    $mediaInsert['media_category'] = 'site_content';
                }
                if (Schema::hasColumn('media_files', 'source_module')) {
                    $mediaInsert['source_module'] = $sourceMod;
                }
                if (Schema::hasColumn('media_files', 'file_hash')) {
                    $mediaInsert['file_hash'] = @hash_file('sha256', $primaryFullPath) ?: md5($relativePath);
                }
                if (Schema::hasColumn('media_files', 'uploaded_by')) {
                    $mediaInsert['uploaded_by'] = Auth::id() ?? 1;
                }

                DB::table('media_files')->insert($mediaInsert);
            } catch (\Throwable $e) {
                // Ignore media registration errors safely
            }
        }

        return $relativePath;
    }

    /**
     * Universal File Delete Helper for Theme
     */
    protected function deleteThemeFile($relativePath)
    {
        if (empty($relativePath) || filter_var($relativePath, FILTER_VALIDATE_URL)) return;

        $clean = ltrim($relativePath, '/');

        // Global Media Library er file gulo onno jaygay use thakay disk theke direct delete kora jabe na
        if (str_starts_with($clean, 'uploads/media/') || str_starts_with($clean, 'media/')) {
            return;
        }

        if (Schema::hasTable('media_files')) {
            $isMedia = DB::table('media_files')
                ->where('file_path', $relativePath)
                ->orWhere('file_path', $clean)
                ->orWhere('file_path', 'uploads/' . $clean)
                ->exists();
            if ($isMedia) {
                return;
            }
        }

        if (str_starts_with($clean, 'storage/')) {
            $clean = substr($clean, 8);
        }

        foreach (ImagePathService::getAllPublicRoots() as $root) {
            foreach ([$root . '/' . $clean, $root . '/storage/' . $clean, $root . '/uploads/' . $clean] as $candidate) {
                if (is_file($candidate)) {
                    @unlink($candidate);
                }
            }
        }

        $file3 = storage_path('app/public/' . $clean);
        if (is_file($file3)) @unlink($file3);
    }

    // ================= HOME PAGE BUILDER (ELEMENTOR & SHORTCODE ENGINE) =================
    public function homeBuilderIndex()
    {
        $sections = DB::table('home_sections')->orderBy('order_index', 'asc')->get();
        $categories = Category::orderBy('name', 'asc')->get();
        return view('admin.theme.home_builder', compact('sections', 'categories'));
    }

    public function homeBuilderStore(Request $request)
    {
        $title = $request->filled('title') ? trim($request->input('title')) : '';

        $data = [
            'title'          => $title,
            'btn_text'       => $request->input('btn_text'),
            'btn_url'        => $request->input('btn_url'),
            'section_type'   => $request->input('section_type', 'custom_html_shortcode'),
            'order_index'    => (int)$request->input('order_index', 0),
            'is_active'      => $request->has('is_active') ? 1 : 0,
            'custom_content' => $request->input('custom_content'),
            'created_at'     => now(),
            'updated_at'     => now(),
        ];

        if (Schema::hasColumn('home_sections', 'icon')) {
            $data['icon'] = $request->input('icon') ?: 'fas fa-layer-group';
        }
        if (Schema::hasColumn('home_sections', 'title_color')) {
            $data['title_color'] = $request->input('title_color') ?: '#0f172a';
        }
        if (Schema::hasColumn('home_sections', 'show_header')) {
            $data['show_header'] = $request->has('show_header') ? 1 : 0;
        }
        if (Schema::hasColumn('home_sections', 'btn_bg')) {
            $data['btn_bg'] = $request->input('btn_bg');
        }
        if (Schema::hasColumn('home_sections', 'btn_color')) {
            $data['btn_color'] = $request->input('btn_color');
        }
        if (Schema::hasColumn('home_sections', 'custom_css')) {
            $data['custom_css'] = $request->input('custom_css');
        }

        DB::table('home_sections')->insert($data);

        Cache::forget('global_settings_cache');

        return redirect()->back()->with('success', 'New section block added to Home Page.');
    }

    public function homeBuilderUpdate(Request $request, $id)
    {
        $title = $request->filled('title') ? trim($request->input('title')) : '';

        $data = [
            'title'          => $title,
            'btn_text'       => $request->input('btn_text'),
            'btn_url'        => $request->input('btn_url'),
            'is_active'      => $request->has('is_active') ? 1 : 0,
            'order_index'    => (int)$request->input('order_index', 0),
            'custom_content' => $request->input('custom_content'),
            'updated_at'     => now(),
        ];

        if (Schema::hasColumn('home_sections', 'icon')) {
            $data['icon'] = $request->input('icon') ?: 'fas fa-layer-group';
        }
        if (Schema::hasColumn('home_sections', 'title_color')) {
            $data['title_color'] = $request->input('title_color') ?: '#0f172a';
        }
        if (Schema::hasColumn('home_sections', 'show_header')) {
            $data['show_header'] = $request->has('show_header') ? 1 : 0;
        }
        if (Schema::hasColumn('home_sections', 'btn_bg')) {
            $data['btn_bg'] = $request->input('btn_bg');
        }
        if (Schema::hasColumn('home_sections', 'btn_color')) {
            $data['btn_color'] = $request->input('btn_color');
        }
        if (Schema::hasColumn('home_sections', 'custom_css')) {
            $data['custom_css'] = $request->input('custom_css');
        }

        DB::table('home_sections')->where('id', $id)->update($data);

        Cache::forget('global_settings_cache');

        return redirect()->back()->with('success', 'Section block updated.');
    }

    public function homeBuilderDestroy($id)
    {
        DB::table('home_sections')->where('id', $id)->delete();
        Cache::forget('global_settings_cache');
        return back()->with('success', 'Section block deleted.');
    }

    // ================= FEATURED PRODUCTS SETTING =================
    public function featuredProductsIndex()
    {
        $settings = DB::table('settings')->whereIn('key', [
            'show_featured_products_section',
            'featured_products_count',
            'featured_enable_pagination',
            'featured_grid_pc'
        ])->pluck('value', 'key');

        $showFeatured     = ($settings['show_featured_products_section'] ?? '1') == '1';
        $featuredCount    = (int)($settings['featured_products_count'] ?? 12);
        $enablePagination = ($settings['featured_enable_pagination'] ?? '1') == '1';
        $gridPc           = $settings['featured_grid_pc'] ?? '4';

        return view('admin.theme.featured_products', compact('showFeatured', 'featuredCount', 'enablePagination', 'gridPc'));
    }

    public function featuredProductsUpdate(Request $request)
    {
        $status     = $request->has('show_featured_products_section') ? '1' : '0';
        $pagination = $request->has('featured_enable_pagination') ? '1' : '0';
        $count      = (int)$request->input('featured_products_count', 12);
        if ($count <= 0) $count = 12;

        $gridPc = $request->input('featured_grid_pc', '4');

        DB::table('settings')->updateOrInsert(
            ['key' => 'show_featured_products_section'],
            ['value' => $status, 'updated_at' => now()]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'featured_enable_pagination'],
            ['value' => $pagination, 'updated_at' => now()]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'featured_products_count'],
            ['value' => (string)$count, 'updated_at' => now()]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'featured_grid_pc'],
            ['value' => (string)$gridPc, 'updated_at' => now()]
        );

        Cache::forget('global_settings_cache');

        return back()->with('success', 'Featured Products settings updated successfully.');
    }

    // ================= HERO SLIDER =================
    public function sliderIndex()
    {
        $this->ensureSliderColumnsExist();
        $sliders = DB::table('hero_sliders')->orderBy('serial', 'asc')->get();
        return view('admin.theme.slider', compact('sliders'));
    }

    public function sliderStore(Request $request)
    {
        $this->ensureSliderColumnsExist();

        $request->validate([
            'title'       => 'nullable|string|max:255',
            'image'       => 'nullable|image|max:5120',
            'image_media' => 'nullable|string',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->saveThemeFile($request->file('image'), 'sliders');
        } elseif ($request->filled('image_media')) {
            $imagePath = $this->normalizeMediaInputPath($request->input('image_media'));
        }

        $data = [
            'badge_text'     => $request->badge_text,
            'badge_bg'       => $request->badge_bg ?? '#2563eb',
            'badge_color'    => $request->badge_color ?? '#ffffff',
            'title'          => $request->title ?? '',
            'title_color'    => $request->title_color ?? '#ffffff',
            'subtitle'       => $request->subtitle,
            'subtitle_color' => $request->subtitle_color ?? 'rgba(255,255,255,0.7)',
            'btn_text'       => $request->btn_text ?? 'Source Now',
            'btn_url'        => $request->btn_url ?? '/shop',
            'btn_bg'         => $request->btn_bg ?? '#2563eb',
            'btn_color'      => $request->btn_color ?? '#ffffff',
            'image'          => $imagePath,
            'serial'         => $request->serial ?? 0,
            'status'         => $request->has('status') ? 1 : 0,
            'created_at'     => now(),
            'updated_at'     => now(),
        ];

        if (Schema::hasColumn('hero_sliders', 'show_content')) {
            $data['show_content'] = $request->has('show_content') ? 1 : 0;
        }
        if (Schema::hasColumn('hero_sliders', 'enable_overlay')) {
            $data['enable_overlay'] = $request->has('enable_overlay') ? 1 : 0;
        }
        if (Schema::hasColumn('hero_sliders', 'overlay_color')) {
            $data['overlay_color'] = $request->input('overlay_color', '#0f172a');
        }
        if (Schema::hasColumn('hero_sliders', 'overlay_opacity')) {
            $data['overlay_opacity'] = $request->input('overlay_opacity', '0.45');
        }
        if (Schema::hasColumn('hero_sliders', 'mobile_view_ratio')) {
            $data['mobile_view_ratio'] = $request->input('mobile_view_ratio', 'landscape');
        }

        DB::table('hero_sliders')->insert($data);

        Cache::forget('global_settings_cache');

        return back()->with('success', 'Slider created successfully.');
    }

    public function sliderUpdate(Request $request, $id)
    {
        $this->ensureSliderColumnsExist();

        $request->validate([
            'title'       => 'nullable|string|max:255',
            'image'       => 'nullable|image|max:5120',
            'image_media' => 'nullable|string',
        ]);

        $slider = DB::table('hero_sliders')->where('id', $id)->first();
        $imagePath = $slider->image;

        if ($request->input('remove_image') == '1') {
            $this->deleteThemeFile($slider->image);
            $imagePath = null;
        }

        if ($request->hasFile('image')) {
            $this->deleteThemeFile($slider->image);
            $imagePath = $this->saveThemeFile($request->file('image'), 'sliders');
        } elseif ($request->filled('image_media')) {
            $normalizedMedia = $this->normalizeMediaInputPath($request->input('image_media'));
            if (!empty($normalizedMedia)) {
                if ($imagePath && $imagePath !== $normalizedMedia) {
                    $this->deleteThemeFile($imagePath);
                }
                $imagePath = $normalizedMedia;
            }
        }

        $data = [
            'badge_text'     => $request->badge_text,
            'badge_bg'       => $request->badge_bg ?? '#2563eb',
            'badge_color'    => $request->badge_color ?? '#ffffff',
            'title'          => $request->title ?? '',
            'title_color'    => $request->title_color ?? '#ffffff',
            'subtitle'       => $request->subtitle,
            'subtitle_color' => $request->subtitle_color ?? 'rgba(255,255,255,0.7)',
            'btn_text'       => $request->btn_text ?? 'Source Now',
            'btn_url'        => $request->btn_url ?? '/shop',
            'btn_bg'         => $request->btn_bg ?? '#2563eb',
            'btn_color'      => $request->btn_color ?? '#ffffff',
            'image'          => $imagePath,
            'serial'         => $request->serial ?? 0,
            'status'         => $request->has('status') ? 1 : 0,
            'updated_at'     => now(),
        ];

        if (Schema::hasColumn('hero_sliders', 'show_content')) {
            $data['show_content'] = $request->has('show_content') ? 1 : 0;
        }
        if (Schema::hasColumn('hero_sliders', 'enable_overlay')) {
            $data['enable_overlay'] = $request->has('enable_overlay') ? 1 : 0;
        }
        if (Schema::hasColumn('hero_sliders', 'overlay_color')) {
            $data['overlay_color'] = $request->input('overlay_color', '#0f172a');
        }
        if (Schema::hasColumn('hero_sliders', 'overlay_opacity')) {
            $data['overlay_opacity'] = $request->input('overlay_opacity', '0.45');
        }
        if (Schema::hasColumn('hero_sliders', 'mobile_view_ratio')) {
            $data['mobile_view_ratio'] = $request->input('mobile_view_ratio', 'landscape');
        }

        DB::table('hero_sliders')->where('id', $id)->update($data);

        Cache::forget('global_settings_cache');

        return back()->with('success', 'Slider updated successfully.');
    }

    public function sliderDestroy($id)
    {
        $slider = DB::table('hero_sliders')->where('id', $id)->first();
        if ($slider && $slider->image) {
            $this->deleteThemeFile($slider->image);
        }
        DB::table('hero_sliders')->where('id', $id)->delete();
        
        Cache::forget('global_settings_cache');
        
        return back()->with('success', 'Slider deleted.');
    }

    private function ensureSliderColumnsExist()
    {
        $columns = [
            'show_content'      => ['type' => 'tinyInteger', 'default' => 1],
            'enable_overlay'    => ['type' => 'tinyInteger', 'default' => 1],
            'overlay_color'     => ['type' => 'string', 'default' => '#0f172a'],
            'overlay_opacity'   => ['type' => 'string', 'default' => '0.45'],
            'mobile_view_ratio' => ['type' => 'string', 'default' => 'landscape'],
        ];

        foreach ($columns as $column => $meta) {
            if (!Schema::hasColumn('hero_sliders', $column)) {
                Schema::table('hero_sliders', function ($table) use ($column, $meta) {
                    if ($meta['type'] === 'tinyInteger') {
                        $table->tinyInteger($column)->default($meta['default']);
                    } else {
                        $table->string($column)->default($meta['default'])->nullable();
                    }
                });
            }
        }
    }

    // ================= FLASH SALE & OFFER CARDS =================
    public function flashSaleIndex()
    {
        $flashSale = DB::table('flash_sales')->first();
        if (!$flashSale) {
            DB::table('flash_sales')->insert([
                'title'      => 'Deals & Offers',
                'subtitle'   => 'Flash discounts expire soon',
                'end_time'   => now()->addDays(2)->addHours(15),
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $flashSale = DB::table('flash_sales')->first();
        }

        $offerCards = DB::table('offer_cards')->orderBy('serial', 'asc')->get();
        $allProducts = Product::select('id', 'title', 'sale_price', 'regular_price')->latest()->get();
        $selectedProductIds = json_decode($flashSale->product_ids ?? '[]', true) ?? [];

        $showCatMenu = DB::table('settings')->where('key', 'show_home_category_menu')->value('value');
        $showCatMenu = ($showCatMenu === null || $showCatMenu === '1');

        return view('admin.theme.flash_sale', compact('flashSale', 'offerCards', 'allProducts', 'selectedProductIds', 'showCatMenu'));
    }

    public function flashSaleUpdate(Request $request)
    {
        $flashSale = DB::table('flash_sales')->first();

        DB::table('flash_sales')->where('id', $flashSale->id)->update([
            'title'             => $request->title,
            'subtitle'          => $request->subtitle,
            'end_time'          => $request->end_time,
            'status'            => $request->has('status') ? 1 : 0,
            'product_ids'       => json_encode($request->product_ids ?? []),
            'right_card_status' => $request->has('right_card_status') ? 1 : 0,
            'updated_at'        => now(),
        ]);

        $catMenuVal = $request->has('show_home_category_menu') ? '1' : '0';
        DB::table('settings')->updateOrInsert(
            ['key' => 'show_home_category_menu'],
            ['value' => $catMenuVal, 'updated_at' => now()]
        );

        Cache::forget('global_settings_cache');

        return back()->with('success', 'Settings updated successfully.');
    }

    public function offerCardStore(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'image'       => 'nullable|image|max:5120',
            'image_media' => 'nullable|string',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->saveThemeFile($request->file('image'), 'theme/offers');
        } elseif ($request->filled('image_media')) {
            $imagePath = $this->normalizeMediaInputPath($request->input('image_media'));
        }

        DB::table('offer_cards')->insert([
            'badge_text'  => $request->badge_text ?? 'Special Voucher',
            'badge_bg'    => $request->badge_bg ?? '#10b981',
            'badge_color' => '#ffffff',
            'title'       => $request->title,
            'subtitle'    => $request->subtitle,
            'image'       => $imagePath,
            'btn_text'    => $request->btn_text ?? 'Claim Voucher',
            'btn_url'     => $request->btn_url ?? '/shop',
            'serial'      => $request->serial ?? 0,
            'status'      => $request->has('status') ? 1 : 0,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        Cache::forget('global_settings_cache');

        return back()->with('success', 'New offer slide added successfully.');
    }

    public function offerCardUpdate(Request $request, $id)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'image'       => 'nullable|image|max:5120',
            'image_media' => 'nullable|string',
        ]);

        $card = DB::table('offer_cards')->where('id', $id)->first();
        $imagePath = $card->image;

        if ($request->input('remove_image') == '1') {
            $this->deleteThemeFile($card->image);
            $imagePath = null;
        }

        if ($request->hasFile('image')) {
            $this->deleteThemeFile($card->image);
            $imagePath = $this->saveThemeFile($request->file('image'), 'theme/offers');
        } elseif ($request->filled('image_media')) {
            $normalizedMedia = $this->normalizeMediaInputPath($request->input('image_media'));
            if (!empty($normalizedMedia)) {
                if ($imagePath && $imagePath !== $normalizedMedia) {
                    $this->deleteThemeFile($imagePath);
                }
                $imagePath = $normalizedMedia;
            }
        }

        DB::table('offer_cards')->where('id', $id)->update([
            'badge_text'  => $request->badge_text,
            'badge_bg'    => $request->badge_bg ?? '#10b981',
            'badge_color' => '#ffffff',
            'title'       => $request->title,
            'subtitle'    => $request->subtitle,
            'image'       => $imagePath,
            'btn_text'    => $request->btn_text,
            'btn_url'     => $request->btn_url,
            'serial'      => $request->serial ?? 0,
            'status'      => $request->has('status') ? 1 : 0,
            'updated_at'  => now(),
        ]);

        Cache::forget('global_settings_cache');

        return back()->with('success', 'Offer slide updated successfully.');
    }

    public function offerCardDestroy($id)
    {
        $card = DB::table('offer_cards')->where('id', $id)->first();
        if ($card && $card->image) {
            $this->deleteThemeFile($card->image);
        }
        DB::table('offer_cards')->where('id', $id)->delete();
        
        Cache::forget('global_settings_cache');
        
        return back()->with('success', 'Offer slide deleted.');
    }

    // ================= POPULAR CATEGORY SLIDER =================
    public function popularCategoriesIndex()
    {
        $allCategories = Category::withCount('products')->orderBy('name', 'asc')->get();

        $selectedJson = DB::table('settings')->where('key', 'popular_category_ids')->value('value');
        $selectedCategoryIds = !empty($selectedJson) ? json_decode($selectedJson, true) : [];

        $sectionStatus = DB::table('settings')->where('key', 'show_popular_categories_section')->value('value');
        $sectionStatus = ($sectionStatus === null || $sectionStatus === '1');

        return view('admin.theme.popular_categories', compact('allCategories', 'selectedCategoryIds', 'sectionStatus'));
    }

    public function popularCategoriesUpdate(Request $request)
    {
        $statusVal = $request->has('show_popular_categories_section') ? '1' : '0';
        DB::table('settings')->updateOrInsert(
            ['key' => 'show_popular_categories_section'],
            ['value' => $statusVal, 'updated_at' => now()]
        );

        $selectedIds = $request->input('category_ids', []);
        DB::table('settings')->updateOrInsert(
            ['key' => 'popular_category_ids'],
            ['value' => json_encode(array_values($selectedIds)), 'updated_at' => now()]
        );

        Cache::forget('global_settings_cache');

        return back()->with('success', 'Popular Category Slider settings updated successfully.');
    }

    // ================= TOPBAR SETTINGS =================
    public function topbarIndex()
    {
        $settings = DB::table('settings')->whereIn('key', [
            'show_topbar_desktop',
            'show_topbar_mobile',
            'topbar_bg',
            'topbar_color',
            'topbar_slider_desktop',
            'topbar_slider_mobile',
            'topbar_notices_json',
            'topbar_left_html',
            'topbar_right_html'
        ])->pluck('value', 'key');

        $notices = !empty($settings['topbar_notices_json']) ? json_decode($settings['topbar_notices_json'], true) : [
            ['text' => 'Free Shipping on orders over ৳1500', 'icon' => 'fas fa-check-circle'],
            ['text' => '7-Day Easy Returns & Instant Refund', 'icon' => 'fas fa-undo'],
            ['text' => '100% Secure Checkout with Leading Gateways', 'icon' => 'fas fa-shield-alt'],
            ['text' => 'Hotline Support: +880 1774-937404 (10 AM - 10 PM)', 'icon' => 'fas fa-headset']
        ];

        return view('admin.theme.topbar', compact('settings', 'notices'));
    }

    public function topbarUpdate(Request $request)
    {
        $desktopTopbar = $request->has('show_topbar_desktop') ? '1' : '0';
        $mobileTopbar = $request->has('show_topbar_mobile') ? '1' : '0';
        $desktopSlider = $request->has('topbar_slider_desktop') ? '1' : '0';
        $mobileSlider = $request->has('topbar_slider_mobile') ? '1' : '0';

        DB::table('settings')->updateOrInsert(['key' => 'show_topbar_desktop'], ['value' => $desktopTopbar, 'updated_at' => now()]);
        DB::table('settings')->updateOrInsert(['key' => 'show_topbar_mobile'], ['value' => $mobileTopbar, 'updated_at' => now()]);
        DB::table('settings')->updateOrInsert(['key' => 'topbar_bg'], ['value' => $request->topbar_bg ?? '#0066ff', 'updated_at' => now()]);
        DB::table('settings')->updateOrInsert(['key' => 'topbar_color'], ['value' => $request->topbar_color ?? '#ffffff', 'updated_at' => now()]);
        DB::table('settings')->updateOrInsert(['key' => 'topbar_slider_desktop'], ['value' => $desktopSlider, 'updated_at' => now()]);
        DB::table('settings')->updateOrInsert(['key' => 'topbar_slider_mobile'], ['value' => $mobileSlider, 'updated_at' => now()]);

        $texts = $request->input('notice_texts', []);
        $icons = $request->input('notice_icons', []);
        $formattedNotices = [];

        foreach ($texts as $i => $txt) {
            $t = trim($txt);
            if (!empty($t)) {
                $formattedNotices[] = [
                    'text' => $t,
                    'icon' => !empty($icons[$i]) ? trim($icons[$i]) : 'fas fa-check-circle'
                ];
            }
        }

        DB::table('settings')->updateOrInsert(['key' => 'topbar_notices_json'], ['value' => json_encode($formattedNotices), 'updated_at' => now()]);
        DB::table('settings')->updateOrInsert(['key' => 'topbar_left_html'], ['value' => $request->topbar_left_html ?? '', 'updated_at' => now()]);
        DB::table('settings')->updateOrInsert(['key' => 'topbar_right_html'], ['value' => $request->topbar_right_html ?? '', 'updated_at' => now()]);

        Cache::forget('global_settings_cache');

        return back()->with('success', 'Topbar settings updated successfully.');
    }

    // ================= THEME COLORS CONTROL =================
    public function themeColorsIndex()
    {
        $colorKeys = [
            'color_site_bg', 'color_card_bg', 'color_header_bg', 'color_header_text',
            'color_header_search_btn_bg', 'color_menu_text', 'color_menu_hover',
            'color_menu_active_bg', 'color_card_btn_cart_bg', 'color_card_btn_cart_text',
            'color_card_btn_order_bg', 'color_card_btn_order_text', 'color_details_btn_cart_bg',
            'color_details_btn_cart_text', 'color_details_btn_order_bg', 'color_details_btn_order_text',
            'color_common_btn_bg', 'color_common_btn_text', 'color_section_title',
            'color_product_title', 'color_product_price', 'color_footer_bg', 'color_footer_text'
        ];

        $colors = DB::table('settings')->whereIn('key', $colorKeys)->pluck('value', 'key')->toArray();

        return view('admin.theme.colors', compact('colors'));
    }

    public function themeColorsUpdate(Request $request)
    {
        $colorKeys = [
            'color_site_bg', 'color_card_bg', 'color_header_bg', 'color_header_text',
            'color_header_search_btn_bg', 'color_menu_text', 'color_menu_hover',
            'color_menu_active_bg', 'color_card_btn_cart_bg', 'color_card_btn_cart_text',
            'color_card_btn_order_bg', 'color_card_btn_order_text', 'color_details_btn_cart_bg',
            'color_details_btn_cart_text', 'color_details_btn_order_bg', 'color_details_btn_order_text',
            'color_common_btn_bg', 'color_common_btn_text', 'color_section_title',
            'color_product_title', 'color_product_price', 'color_footer_bg', 'color_footer_text'
        ];

        if ($request->has('reset_defaults')) {
            DB::table('settings')->whereIn('key', $colorKeys)->delete();
            Cache::forget('global_settings_cache');
            return back()->with('success', 'All theme colors restored to factory defaults.');
        }

        foreach ($colorKeys as $key) {
            if ($request->has($key)) {
                DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $request->input($key), 'updated_at' => now()]
                );
            }
        }

        Cache::forget('global_settings_cache');

        return back()->with('success', 'Theme colors updated successfully.');
    }

    // ================= FOOTER BUILDER / CUSTOMIZER =================
    public function footerBuilderIndex()
    {
        $keys = [
            'footer_bg', 'footer_text_color', 'footer_heading_color', 'footer_bottom_bg',
            'footer_logo', 'footer_show_logo', 'footer_logo_width', 'footer_logo_height',
            'footer_logo_mobile_width', 'footer_logo_mobile_height', 'footer_brand_title',
            'footer_show_brand_title', 'footer_brand_tagline', 'footer_show_brand_tagline',
            'footer_about_text', 'footer_phone', 'footer_email', 'footer_track_btn_text',
            'footer_track_btn_url', 'footer_show_accordion_mobile', 'footer_mobile_align',
            'footer_col2_title', 'footer_col3_title', 'footer_col4_title', 'footer_show_social',
            'footer_show_payment_badges', 'footer_payment_only_logo', 'footer_copyright_text',
            'footer_quick_links_json', 'footer_support_links_json', 'footer_social_links_json',
            'footer_payment_badges_json'
        ];

        $footerSettings = DB::table('settings')->whereIn('key', $keys)->pluck('value', 'key')->toArray();

        $quickLinks = !empty($footerSettings['footer_quick_links_json'])
            ? json_decode($footerSettings['footer_quick_links_json'], true)
            : [
                ['label' => 'Home', 'url' => '/'],
                ['label' => 'Shop', 'url' => '/shop'],
                ['label' => 'My Cart', 'url' => '/cart'],
                ['label' => 'Checkout', 'url' => '/checkout']
            ];

        $supportLinks = !empty($footerSettings['footer_support_links_json'])
            ? json_decode($footerSettings['footer_support_links_json'], true)
            : [
                ['label' => 'About Us', 'url' => '/about-us'],
                ['label' => 'Contact Us', 'url' => '/contact-us'],
                ['label' => 'Latest Blogs', 'url' => '/blogs']
            ];

        $socialLinks = !empty($footerSettings['footer_social_links_json'])
            ? json_decode($footerSettings['footer_social_links_json'], true)
            : [
                ['icon' => 'fab fa-facebook-f', 'url' => '#'],
                ['icon' => 'fab fa-instagram', 'url' => '#'],
                ['icon' => 'fab fa-youtube', 'url' => '#'],
                ['icon' => 'fab fa-tiktok', 'url' => '#']
            ];

        $paymentBadges = !empty($footerSettings['footer_payment_badges_json'])
            ? json_decode($footerSettings['footer_payment_badges_json'], true)
            : [
                ['name' => 'COD', 'image' => ''],
                ['name' => 'BanglaQR', 'image' => ''],
                ['name' => 'Bkash', 'image' => ''],
                ['name' => 'Nagad', 'image' => '']
            ];

        return view('admin.theme.footer_builder', compact('footerSettings', 'quickLinks', 'supportLinks', 'socialLinks', 'paymentBadges'));
    }

    public function footerBuilderUpdate(Request $request)
    {
        $simpleKeys = [
            'footer_bg', 'footer_text_color', 'footer_heading_color', 'footer_bottom_bg',
            'footer_logo_width', 'footer_logo_height', 'footer_logo_mobile_width',
            'footer_logo_mobile_height', 'footer_brand_title', 'footer_brand_tagline',
            'footer_about_text', 'footer_phone', 'footer_email', 'footer_track_btn_text',
            'footer_track_btn_url', 'footer_mobile_align', 'footer_col2_title',
            'footer_col3_title', 'footer_col4_title', 'footer_copyright_text'
        ];

        foreach ($simpleKeys as $key) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $request->input($key) ?? '', 'updated_at' => now()]
            );
        }

        if ($request->hasFile('footer_logo')) {
            $oldLogo = DB::table('settings')->where('key', 'footer_logo')->value('value');
            $this->deleteThemeFile($oldLogo);
            $logoPath = $this->saveThemeFile($request->file('footer_logo'), 'theme/footer');
            DB::table('settings')->updateOrInsert(['key' => 'footer_logo'], ['value' => $logoPath, 'updated_at' => now()]);
        } elseif ($request->filled('footer_logo_media')) {
            $normalizedMedia = $this->normalizeMediaInputPath($request->input('footer_logo_media'));
            if (!empty($normalizedMedia)) {
                $oldLogo = DB::table('settings')->where('key', 'footer_logo')->value('value');
                if ($oldLogo && $oldLogo !== $normalizedMedia) {
                    $this->deleteThemeFile($oldLogo);
                }
                DB::table('settings')->updateOrInsert(['key' => 'footer_logo'], ['value' => $normalizedMedia, 'updated_at' => now()]);
            }
        } elseif ($request->has('remove_footer_logo') && $request->remove_footer_logo == '1') {
            $oldLogo = DB::table('settings')->where('key', 'footer_logo')->value('value');
            $this->deleteThemeFile($oldLogo);
            DB::table('settings')->updateOrInsert(['key' => 'footer_logo'], ['value' => '', 'updated_at' => now()]);
        }

        DB::table('settings')->updateOrInsert(['key' => 'footer_show_logo'], ['value' => $request->has('footer_show_logo') ? '1' : '0', 'updated_at' => now()]);
        DB::table('settings')->updateOrInsert(['key' => 'footer_show_brand_title'], ['value' => $request->has('footer_show_brand_title') ? '1' : '0', 'updated_at' => now()]);
        DB::table('settings')->updateOrInsert(['key' => 'footer_show_brand_tagline'], ['value' => $request->has('footer_show_brand_tagline') ? '1' : '0', 'updated_at' => now()]);
        DB::table('settings')->updateOrInsert(['key' => 'footer_show_accordion_mobile'], ['value' => $request->has('footer_show_accordion_mobile') ? '1' : '0', 'updated_at' => now()]);
        DB::table('settings')->updateOrInsert(['key' => 'footer_show_social'], ['value' => $request->has('footer_show_social') ? '1' : '0', 'updated_at' => now()]);
        DB::table('settings')->updateOrInsert(['key' => 'footer_show_payment_badges'], ['value' => $request->has('footer_show_payment_badges') ? '1' : '0', 'updated_at' => now()]);
        DB::table('settings')->updateOrInsert(['key' => 'footer_payment_only_logo'], ['value' => $request->has('footer_payment_only_logo') ? '1' : '0', 'updated_at' => now()]);

        $qLabels = $request->input('quick_link_labels', []);
        $qUrls = $request->input('quick_link_urls', []);
        $formattedQuickLinks = [];
        foreach ($qLabels as $i => $lbl) {
            if (!empty(trim($lbl))) {
                $formattedQuickLinks[] = [
                    'label' => trim($lbl),
                    'url'   => !empty($qUrls[$i]) ? trim($qUrls[$i]) : '#'
                ];
            }
        }
        DB::table('settings')->updateOrInsert(['key' => 'footer_quick_links_json'], ['value' => json_encode($formattedQuickLinks), 'updated_at' => now()]);

        $sLabels = $request->input('support_link_labels', []);
        $sUrls = $request->input('support_link_urls', []);
        $formattedSupportLinks = [];
        foreach ($sLabels as $i => $lbl) {
            if (!empty(trim($lbl))) {
                $formattedSupportLinks[] = [
                    'label' => trim($lbl),
                    'url'   => !empty($sUrls[$i]) ? trim($sUrls[$i]) : '#'
                ];
            }
        }
        DB::table('settings')->updateOrInsert(['key' => 'footer_support_links_json'], ['value' => json_encode($formattedSupportLinks), 'updated_at' => now()]);

        $socIcons = $request->input('social_icons', []);
        $socUrls = $request->input('social_urls', []);
        $formattedSocials = [];
        foreach ($socIcons as $i => $ico) {
            if (!empty(trim($ico))) {
                $formattedSocials[] = [
                    'icon' => trim($ico),
                    'url'  => !empty($socUrls[$i]) ? trim($socUrls[$i]) : '#'
                ];
            }
        }
        DB::table('settings')->updateOrInsert(['key' => 'footer_social_links_json'], ['value' => json_encode($formattedSocials), 'updated_at' => now()]);

        $payNames = $request->input('payment_names', []);
        $existingImgs = $request->input('payment_existing_images', []);
        $uploadedImgs = $request->file('payment_images', []);
        $formattedPayments = [];

        foreach ($payNames as $i => $name) {
            $imgPath = $existingImgs[$i] ?? '';
            if (isset($uploadedImgs[$i]) && $uploadedImgs[$i]->isValid()) {
                $imgPath = $this->saveThemeFile($uploadedImgs[$i], 'theme/payments');
            }
            if (!empty(trim($name)) || !empty($imgPath)) {
                $formattedPayments[] = [
                    'name'  => trim($name ?? ''),
                    'image' => $imgPath
                ];
            }
        }
        DB::table('settings')->updateOrInsert(['key' => 'footer_payment_badges_json'], ['value' => json_encode($formattedPayments), 'updated_at' => now()]);

        Cache::forget('global_settings_cache');

        return back()->with('success', 'Footer settings updated successfully.');
    }

    // ================= PRODUCT CARD VIEW SETTINGS =================
    public function productCardViewIndex()
    {
        $settings = DB::table('settings')->pluck('value', 'key')->toArray();
        return view('admin.theme.product_card', compact('settings'));
    }

    public function productCardViewUpdate(Request $request)
    {
        $booleanKeys = [
            'card_show_price_decimal',
            'card_show_review',
            'card_show_wishlist',
            'card_show_sale_badge',
            'card_enable_quick_view',
            'card_show_stockout_btn',
            'detail_show_reviews',
            'detail_allow_review'
        ];

        foreach ($booleanKeys as $bKey) {
            $val = $request->has($bKey) ? '1' : '0';
            DB::table('settings')->updateOrInsert(['key' => $bKey], ['value' => $val, 'updated_at' => now()]);
        }

        if ($request->has('card_sale_badge_text')) {
            DB::table('settings')->updateOrInsert(['key' => 'card_sale_badge_text'], ['value' => trim($request->card_sale_badge_text), 'updated_at' => now()]);
        }

        if ($request->has('card_hover_style')) {
            DB::table('settings')->updateOrInsert(['key' => 'card_hover_style'], ['value' => $request->card_hover_style, 'updated_at' => now()]);
        }

        Cache::forget('global_settings_cache');
        Cache::forget('product_card_settings_cache');

        return redirect()->back()->with('success', 'Product Card & Review Settings updated successfully!');
    }

    // ================= CART, HEADER & STICKY SETTINGS =================
    public function cartFeaturesIndex()
    {
        $keys = [
            'feature_buy_now_btn',
            'feature_floating_cart_modal',
            'feature_instant_checkout_modal',
            'feature_ajax_search',
            'feature_quick_view',
            'feature_sticky_header',
            'feature_glassmorphism_header',
            'feature_sticky_footer',
            'sticky_footer_show_mobile',
            'sticky_footer_show_tablet',
            'sticky_footer_show_desktop',
            'sticky_footer_desktop_style',
            'sticky_footer_glassmorphism',
            'sticky_footer_hide_on_scroll',
            'sticky_footer_bg',
            'sticky_footer_text_color',
            'sticky_footer_active_color',
            'sticky_footer_items_json',
            'feature_scroll_to_top',
            'scroll_top_show_mobile',
            'scroll_top_show_tablet',
            'scroll_top_show_desktop',
            'scroll_top_style',
            'scroll_top_position',
            'feature_sticky_product_bar',
            'sticky_product_bar_show_mobile',
            'sticky_product_bar_show_tablet',
            'sticky_product_bar_show_desktop',
            'sticky_product_bar_position',
            'sticky_product_bar_show_buynow'
        ];
        $settings = DB::table('settings')->whereIn('key', $keys)->pluck('value', 'key')->toArray();

        $defaultFooterItems = [
            ['title' => 'Shop', 'icon' => 'fas fa-store', 'url' => '/shop', 'action_type' => 'link'],
            ['title' => 'Search', 'icon' => 'fas fa-search', 'url' => '#', 'action_type' => 'search'],
            ['title' => 'Wishlist', 'icon' => 'far fa-heart', 'url' => '/user/wishlist', 'action_type' => 'wishlist'],
            ['title' => 'Cart', 'icon' => 'fas fa-shopping-bag', 'url' => '/cart', 'action_type' => 'cart'],
            ['title' => 'Account', 'icon' => 'fas fa-user', 'url' => '/user/dashboard', 'action_type' => 'link']
        ];

        $footerItems = !empty($settings['sticky_footer_items_json']) 
            ? json_decode($settings['sticky_footer_items_json'], true) 
            : $defaultFooterItems;

        return view('admin.theme.cart_features', compact('settings', 'footerItems'));
    }

    public function cartFeaturesUpdate(Request $request)
    {
        $booleanKeys = [
            'feature_buy_now_btn',
            'feature_floating_cart_modal',
            'feature_instant_checkout_modal',
            'feature_ajax_search',
            'feature_quick_view',
            'feature_sticky_header',
            'feature_glassmorphism_header',
            'feature_sticky_footer',
            'sticky_footer_show_mobile',
            'sticky_footer_show_tablet',
            'sticky_footer_show_desktop',
            'sticky_footer_glassmorphism',
            'sticky_footer_hide_on_scroll',
            'feature_scroll_to_top',
            'scroll_top_show_mobile',
            'scroll_top_show_tablet',
            'scroll_top_show_desktop',
            'feature_sticky_product_bar',
            'sticky_product_bar_show_mobile',
            'sticky_product_bar_show_tablet',
            'sticky_product_bar_show_desktop',
            'sticky_product_bar_show_buynow'
        ];

        foreach ($booleanKeys as $key) {
            $val = $request->has($key) ? '1' : '0';
            DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $val, 'updated_at' => now()]);
        }

        $directValues = [
            'sticky_footer_desktop_style'   => $request->input('sticky_footer_desktop_style', 'boxed_floating'),
            'scroll_top_style'              => $request->input('scroll_top_style', 'classic_rounded'),
            'scroll_top_position'           => $request->input('scroll_top_position', 'right'),
            'sticky_footer_bg'              => $request->input('sticky_footer_bg', '#ffffff'),
            'sticky_footer_text_color'      => $request->input('sticky_footer_text_color', '#64748b'),
            'sticky_footer_active_color'    => $request->input('sticky_footer_active_color', '#2563eb'),
            'sticky_product_bar_position'   => $request->input('sticky_product_bar_position', 'top'),
        ];

        foreach ($directValues as $dKey => $dVal) {
            DB::table('settings')->updateOrInsert(['key' => $dKey], ['value' => $dVal, 'updated_at' => now()]);
        }

        $titles  = $request->input('item_title', []);
        $icons   = $request->input('item_icon', []);
        $urls    = $request->input('item_url', []);
        $actions = $request->input('item_action_type', []);

        $itemsList = [];
        foreach ($titles as $i => $title) {
            $cleanTitle = trim($title ?? '');
            if (!empty($cleanTitle)) {
                $itemsList[] = [
                    'title'       => $cleanTitle,
                    'icon'        => !empty($icons[$i]) ? trim($icons[$i]) : 'fas fa-circle',
                    'url'         => !empty($urls[$i]) ? trim($urls[$i]) : '#',
                    'action_type' => $actions[$i] ?? 'link'
                ];
            }
        }

        DB::table('settings')->updateOrInsert(
            ['key' => 'sticky_footer_items_json'],
            ['value' => json_encode($itemsList), 'updated_at' => now()]
        );

        Cache::forget('global_settings_cache');
        Cache::forget('product_card_settings_cache');

        return back()->with('success', 'All Feature Settings including Sticky Product Bar updated successfully!');
    }
}