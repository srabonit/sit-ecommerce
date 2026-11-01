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

    // ================= HOME PAGE BUILDER =================
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

        $this->flushAllThemeCaches();

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

        $this->flushAllThemeCaches();

        return redirect()->back()->with('success', 'Section block updated.');
    }

    public function homeBuilderDestroy($id)
    {
        DB::table('home_sections')->where('id', $id)->delete();
        $this->flushAllThemeCaches();
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

        DB::table('settings')->updateOrInsert(['key' => 'show_featured_products_section'], ['value' => $status, 'updated_at' => now()]);
        DB::table('settings')->updateOrInsert(['key' => 'featured_enable_pagination'], ['value' => $pagination, 'updated_at' => now()]);
        DB::table('settings')->updateOrInsert(['key' => 'featured_products_count'], ['value' => (string)$count, 'updated_at' => now()]);
        DB::table('settings')->updateOrInsert(['key' => 'featured_grid_pc'], ['value' => (string)$gridPc, 'updated_at' => now()]);

        $this->flushAllThemeCaches();

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

        $this->flushAllThemeCaches();

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

        $this->flushAllThemeCaches();

        return back()->with('success', 'Slider updated successfully.');
    }

    public function sliderDestroy($id)
    {
        $slider = DB::table('hero_sliders')->where('id', $id)->first();
        if ($slider && $slider->image) {
            $this->deleteThemeFile($slider->image);
        }
        DB::table('hero_sliders')->where('id', $id)->delete();
        
        $this->flushAllThemeCaches();
        
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
        DB::table('settings')->updateOrInsert(['key' => 'show_home_category_menu'], ['value' => $catMenuVal, 'updated_at' => now()]);

        $this->flushAllThemeCaches();

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

        $this->flushAllThemeCaches();

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

        $this->flushAllThemeCaches();

        return back()->with('success', 'Offer slide updated successfully.');
    }

    public function offerCardDestroy($id)
    {
        $card = DB::table('offer_cards')->where('id', $id)->first();
        if ($card && $card->image) {
            $this->deleteThemeFile($card->image);
        }
        DB::table('offer_cards')->where('id', $id)->delete();
        
        $this->flushAllThemeCaches();
        
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
        DB::table('settings')->updateOrInsert(['key' => 'show_popular_categories_section'], ['value' => $statusVal, 'updated_at' => now()]);

        $selectedIds = $request->input('category_ids', []);
        DB::table('settings')->updateOrInsert(['key' => 'popular_category_ids'], ['value' => json_encode(array_values($selectedIds)), 'updated_at' => now()]);

        $this->flushAllThemeCaches();

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
            $t = trim($txt ?? '');
            if ($t !== '') {
                $formattedNotices[] = [
                    'text' => $t,
                    'icon' => !empty($icons[$i]) ? trim($icons[$i]) : 'fas fa-check-circle'
                ];
            }
        }

        DB::table('settings')->updateOrInsert(['key' => 'topbar_notices_json'], ['value' => json_encode($formattedNotices), 'updated_at' => now()]);
        DB::table('settings')->updateOrInsert(['key' => 'topbar_left_html'], ['value' => $request->topbar_left_html ?? '', 'updated_at' => now()]);
        DB::table('settings')->updateOrInsert(['key' => 'topbar_right_html'], ['value' => $request->topbar_right_html ?? '', 'updated_at' => now()]);

        $this->flushAllThemeCaches();

        return back()->with('success', 'Topbar settings updated successfully.');
    }

    // ================= THEME COLORS CONTROL (ALL STOREFRONT MODULES) =================
    public function themeColorsIndex()
    {
        $allSettings = DB::table('settings')->pluck('value', 'key')->toArray();

        // 1. App Master Layout & Global Elements
        $appColorKeys = [
            'color_site_bg', 'color_common_btn_bg', 'color_common_btn_text',
            'color_app_spb_bg', 'color_app_spb_border', 'color_app_spb_title', 'color_app_spb_title_hover', 'color_app_spb_price',
            'color_app_spb_cart_btn_bg', 'color_app_spb_cart_btn_text', 'color_app_spb_cart_btn_hover_bg',
            'color_app_spb_buynow_bg', 'color_app_spb_buynow_text', 'color_app_spb_buynow_hover_bg', 'color_app_spb_buynow_hover_text',
            'color_app_search_modal_bg', 'color_app_search_modal_border',
            'color_app_search_price', 'color_app_search_cart_btn_bg', 'color_app_search_cart_btn_text', 'color_app_search_cart_btn_hover_bg', 'color_app_search_cart_btn_hover_text',
            'color_app_drawer_bg', 'color_app_drawer_border', 'color_app_drawer_icon', 'color_app_drawer_total_price',
            'color_app_drawer_checkout_bg', 'color_app_drawer_checkout_text', 'color_app_drawer_checkout_hover_bg', 'color_app_drawer_checkout_hover_text',
            'color_app_quickview_spinner',
            'color_app_scroll_top_bg', 'color_app_scroll_top_color'
        ];

        // 2. Header Scoped Elements
        $headerColorKeys = [
            'color_hdr_bg', 'color_hdr_border', 'color_hdr_sec_menu_bg', 'color_hdr_sec_menu_border',
            'color_hdr_search_border', 'color_hdr_search_bg', 'color_hdr_search_input_text', 'color_hdr_search_placeholder',
            'color_hdr_search_cat_bg', 'color_hdr_search_cat_text', 'color_hdr_search_btn_bg', 'color_hdr_search_btn_text', 'color_hdr_search_btn_hover_bg', 'color_hdr_search_btn_hover_text',
            'color_hdr_icon_text', 'color_hdr_icon_hover', 'color_hdr_icon_active', 'color_hdr_badge_bg', 'color_hdr_badge_text',
            'color_hdr_cat_btn_bg', 'color_hdr_cat_btn_text', 'color_hdr_cat_btn_hover_bg', 'color_hdr_cat_btn_hover_text', 'color_hdr_cat_btn_active_bg', 'color_hdr_cat_btn_active_text',
            'color_hdr_cat_drop_bg', 'color_hdr_cat_drop_text', 'color_hdr_cat_drop_hover_bg', 'color_hdr_cat_drop_hover_text',
            'color_hdr_menu_text', 'color_hdr_menu_bg', 'color_hdr_menu_hover_text', 'color_hdr_menu_hover_bg', 'color_hdr_menu_active_text', 'color_hdr_menu_active_bg',
            'color_hdr_menu_drop_bg', 'color_hdr_menu_drop_text', 'color_hdr_menu_drop_hover_bg', 'color_hdr_menu_drop_hover_text',
            'color_hdr_support_icon', 'color_hdr_support_label', 'color_hdr_support_phone', 'color_hdr_support_phone_hover',
            'color_hdr_mob_toggle_bg', 'color_hdr_mob_toggle_icon', 'color_hdr_mob_toggle_hover_bg', 'color_hdr_mob_toggle_hover_icon',
            'color_hdr_mob_head_bg', 'color_hdr_mob_head_text', 'color_hdr_mob_head_icon',
            'color_hdr_mob_tab_bg', 'color_hdr_mob_tab_text', 'color_hdr_mob_tab_active_bg', 'color_hdr_mob_tab_active_text',
            'color_hdr_mob_body_bg', 'color_hdr_mob_item_text', 'color_hdr_mob_item_border', 'color_hdr_mob_item_hover_bg', 'color_hdr_mob_item_hover_text',
            'color_hdr_mob_item_active_bg', 'color_hdr_mob_item_active_text'
        ];

        // 3. Footer Scoped Elements (Desktop + Mobile + Social Icon Normal & Hover)
        $footerColorKeys = [
            'color_ftr_bg', 'color_ftr_border', 'color_ftr_text', 'color_ftr_logo_border',
            'color_ftr_brand_title', 'color_ftr_brand_tagline', 'color_ftr_about_text',
            'color_ftr_heading', 'color_ftr_heading_icon',
            'color_ftr_link_text', 'color_ftr_link_hover_text', 'color_ftr_link_icon', 'color_ftr_link_hover_icon',
            'color_ftr_contact_icon', 'color_ftr_contact_text', 'color_ftr_contact_hover',
            
            // Social Icons Normal & Hover Controls
            'color_ftr_social_icon', 'color_ftr_social_bg', 'color_ftr_social_border',
            'color_ftr_social_hover_icon', 'color_ftr_social_hover_bg', 'color_ftr_social_hover_border',

            'color_ftr_payment_bg', 'color_ftr_payment_border', 'color_ftr_payment_text', 'color_ftr_payment_logo_border',
            'color_ftr_payment_hover_bg', 'color_ftr_payment_hover_border',
            'color_ftr_bottom_bg', 'color_ftr_bottom_border', 'color_ftr_copyright_text', 'color_ftr_developer_text',

            // Mobile Footer Elements
            'color_ftr_mob_brand_bg', 'color_ftr_mob_brand_border', 'color_ftr_mob_logo_box_bg', 'color_ftr_mob_logo_box_border',
            'color_ftr_mob_brand_arrow', 'color_ftr_mob_acc_bg', 'color_ftr_mob_acc_border', 'color_ftr_mob_acc_item_border',
            'color_ftr_mob_acc_btn_text', 'color_ftr_mob_acc_btn_icon', 'color_ftr_mob_acc_chevron', 'color_ftr_mob_acc_chevron_active',
            'color_ftr_mob_acc_body_bg', 'color_ftr_mob_acc_link_text', 'color_ftr_mob_acc_link_hover',
            'color_ftr_mob_pay_pill_bg', 'color_ftr_mob_pay_pill_border', 'color_ftr_mob_pay_pill_text',
            'color_ftr_mob_secure_border', 'color_ftr_mob_secure_icon', 'color_ftr_mob_secure_text'
        ];

        // 4. Product Card Scoped Elements
        $productCardColorKeys = [
            'color_cpc_card_bg', 'color_cpc_card_border', 'color_cpc_card_border_hover', 'color_cpc_card_shadow_hover',
            'color_cpc_img_wrap_bg', 'color_cpc_body_bg',
            'color_cpc_title', 'color_cpc_title_hover', 'color_cpc_category', 'color_cpc_category_hover',
            'color_cpc_price', 'color_cpc_old_price', 'color_cpc_rating_star', 'color_cpc_rating_text',
            'color_cpc_sale_badge_bg', 'color_cpc_sale_badge_text', 'color_cpc_sale_badge_border',
            'color_cpc_wishlist_bg', 'color_cpc_wishlist_icon', 'color_cpc_wishlist_border',
            'color_cpc_wishlist_hover_bg', 'color_cpc_wishlist_hover_icon', 'color_cpc_wishlist_hover_border',
            'color_cpc_wishlist_active_bg', 'color_cpc_wishlist_active_icon', 'color_cpc_wishlist_active_border',
            'color_cpc_qv_bg', 'color_cpc_qv_icon', 'color_cpc_qv_border',
            'color_cpc_qv_hover_bg', 'color_cpc_qv_hover_icon', 'color_cpc_qv_hover_border',
            'color_cpc_cart_bg', 'color_cpc_cart_text', 'color_cpc_cart_border', 'color_cpc_cart_icon',
            'color_cpc_cart_hover_bg', 'color_cpc_cart_hover_text', 'color_cpc_cart_hover_border', 'color_cpc_cart_hover_icon',
            'color_cpc_order_bg', 'color_cpc_order_text', 'color_cpc_order_border', 'color_cpc_order_icon',
            'color_cpc_order_hover_bg', 'color_cpc_order_hover_text', 'color_cpc_order_hover_border', 'color_cpc_order_hover_icon',
            'color_cpc_opt_bg', 'color_cpc_opt_text', 'color_cpc_opt_border', 'color_cpc_opt_icon',
            'color_cpc_opt_hover_bg', 'color_cpc_opt_hover_text', 'color_cpc_opt_hover_border', 'color_cpc_opt_hover_icon',
            'color_cpc_stockout_bg', 'color_cpc_stockout_text', 'color_cpc_stockout_border', 'color_cpc_stockout_icon',
        ];

        // 5. Product Details & Quick View Modal Elements (with WhatsApp Normal & Hover controls)
        $productDetailColorKeys = [
            'color_pdp_wrapper_bg', 'color_pdp_card_bg', 'color_pdp_card_border',
            'color_pdp_zoom_bg', 'color_pdp_zoom_border', 'color_pdp_lens_border',
            'color_pdp_thumb_border', 'color_pdp_thumb_active_border',
            'color_pdp_thumb_arrow_bg', 'color_pdp_thumb_arrow_text', 'color_pdp_thumb_arrow_hover_bg', 'color_pdp_thumb_arrow_hover_text',
            'color_pdp_discount_bg', 'color_pdp_discount_text',
            'color_pdp_breadcrumb_link', 'color_pdp_breadcrumb_hover', 'color_pdp_breadcrumb_active',
            'color_pdp_title', 'color_pdp_rating_star', 'color_pdp_rating_text',
            'color_pdp_action_btn_bg', 'color_pdp_action_btn_border', 'color_pdp_action_btn_icon',
            'color_pdp_action_btn_hover_bg', 'color_pdp_action_btn_hover_border', 'color_pdp_action_btn_hover_icon',
            'color_pdp_action_btn_active_bg', 'color_pdp_action_btn_active_border', 'color_pdp_action_btn_active_icon',
            'color_pdp_sku_badge_bg', 'color_pdp_sku_badge_text', 'color_pdp_sku_badge_border',
            'color_pdp_stock_in_bg', 'color_pdp_stock_in_text', 'color_pdp_stock_in_border',
            'color_pdp_stock_out_bg', 'color_pdp_stock_out_text', 'color_pdp_stock_out_border',
            'color_pdp_stock_pre_bg', 'color_pdp_stock_pre_text', 'color_pdp_stock_pre_border',
            'color_pdp_price_box_bg', 'color_pdp_price_box_border',
            'color_pdp_price', 'color_pdp_old_price',
            'color_pdp_saving_bg', 'color_pdp_saving_text', 'color_pdp_estimated_text',
            'color_pdp_short_desc_bg', 'color_pdp_short_desc_border', 'color_pdp_short_desc_text',
            'color_pdp_swatch_label', 'color_pdp_swatch_active_label',
            'color_pdp_swatch_bg', 'color_pdp_swatch_text', 'color_pdp_swatch_border',
            'color_pdp_swatch_hover_bg', 'color_pdp_swatch_hover_border',
            'color_pdp_swatch_active_bg', 'color_pdp_swatch_active_text', 'color_pdp_swatch_active_border',
            'color_pdp_qty_label', 'color_pdp_qty_border',
            'color_pdp_qty_btn_bg', 'color_pdp_qty_btn_text', 'color_pdp_qty_btn_border',
            'color_pdp_qty_btn_hover_bg', 'color_pdp_qty_btn_hover_text',
            'color_pdp_qty_input_bg', 'color_pdp_qty_input_text',

            // Buy Now Full Controls
            'color_pdp_buynow_bg', 'color_pdp_buynow_text', 'color_pdp_buynow_border', 'color_pdp_buynow_icon',
            'color_pdp_buynow_hover_bg', 'color_pdp_buynow_hover_text', 'color_pdp_buynow_hover_border', 'color_pdp_buynow_hover_icon',

            // Add to Cart Full Controls
            'color_pdp_cart_bg', 'color_pdp_cart_text', 'color_pdp_cart_border', 'color_pdp_cart_icon',
            'color_pdp_cart_hover_bg', 'color_pdp_cart_hover_text', 'color_pdp_cart_hover_border', 'color_pdp_cart_hover_icon',

            // WhatsApp Normal & Hover Controls
            'color_pdp_wa_bg', 'color_pdp_wa_text', 'color_pdp_wa_border', 'color_pdp_wa_icon',
            'color_pdp_wa_hover_bg', 'color_pdp_wa_hover_text', 'color_pdp_wa_hover_border', 'color_pdp_wa_hover_icon',

            'color_pdp_stockout_bg', 'color_pdp_stockout_text', 'color_pdp_stockout_border', 'color_pdp_stockout_icon',
            'color_pdp_sidebar_bg', 'color_pdp_sidebar_border', 'color_pdp_sidebar_brand_title',
            'color_pdp_sidebar_verified_badge', 'color_pdp_sidebar_heading',
            'color_pdp_sidebar_feature_icon_bg', 'color_pdp_sidebar_feature_icon', 'color_pdp_sidebar_feature_text',
            'color_pdp_sidebar_social_bg', 'color_pdp_sidebar_social_border', 'color_pdp_sidebar_social_icon',
            'color_pdp_sidebar_social_hover_bg', 'color_pdp_sidebar_social_hover_border', 'color_pdp_sidebar_social_hover_icon',
            'color_pdp_tab_border', 'color_pdp_tab_link_text', 'color_pdp_tab_link_active_text', 'color_pdp_tab_link_active_border',
            'color_pdp_desc_text',
            'color_pdp_spec_th_bg', 'color_pdp_spec_th_text', 'color_pdp_spec_td_bg', 'color_pdp_spec_td_text', 'color_pdp_spec_border',
            'color_pdp_review_box_bg', 'color_pdp_review_box_border',
            'color_pdp_review_author', 'color_pdp_review_date', 'color_pdp_review_comment',
            'color_pdp_review_star', 'color_pdp_review_verified_badge_bg', 'color_pdp_review_verified_badge_text'
        ];

        // 6. Cart Drawer & Main Cart Page Scoped Elements
        $cartColorKeys = [
            'color_cart_page_bg', 'color_cart_panel_bg', 'color_cart_panel_border',
            'color_cart_item_bg', 'color_cart_item_border', 'color_cart_item_border_hover',
            'color_cart_item_img_bg', 'color_cart_item_img_border',
            'color_cart_item_title', 'color_cart_item_title_hover', 'color_cart_item_price',
            'color_cart_variant_bg', 'color_cart_variant_text', 'color_cart_variant_border',
            'color_cart_sku_text',
            'color_cart_qty_btn_bg', 'color_cart_qty_btn_text', 'color_cart_qty_btn_border',
            'color_cart_qty_btn_hover_bg', 'color_cart_qty_btn_hover_text',
            'color_cart_remove_btn', 'color_cart_remove_btn_hover',
            'color_cart_checkout_btn_bg', 'color_cart_checkout_btn_text', 'color_cart_checkout_btn_hover',
            'color_cart_empty_icon', 'color_cart_empty_btn_bg', 'color_cart_empty_btn_text', 'color_cart_empty_btn_hover_bg'
        ];

        // 7. Checkout Page Scoped Elements
        $checkoutColorKeys = [
            'color_checkout_page_bg', 'color_checkout_card_bg', 'color_checkout_card_border',
            'color_checkout_heading', 'color_checkout_text', 'color_checkout_label',
            'color_checkout_input_bg', 'color_checkout_input_border', 'color_checkout_input_focus_border',
            'color_checkout_paybox_bg', 'color_checkout_paybox_border',
            'color_checkout_paybox_active_bg', 'color_checkout_paybox_active_border',
            'color_checkout_total_price',
            'color_checkout_btn_bg', 'color_checkout_btn_text', 'color_checkout_btn_hover'
        ];

        // 8. Order Confirmation & Thermal Token Elements
        $tokenColorKeys = [
            'color_token_page_bg', 'color_token_success_icon_bg', 'color_token_success_icon_text',
            'color_token_heading', 'color_token_subtext',
            'color_token_total_card_bg', 'color_token_total_card_border', 'color_token_total_card_label', 'color_token_total_price',
            'color_token_download_btn_bg', 'color_token_download_btn_text', 'color_token_download_btn_hover',
            'color_token_continue_btn_bg', 'color_token_continue_btn_text', 'color_token_continue_btn_border', 'color_token_continue_btn_hover',
            'color_token_memo_bg', 'color_token_memo_border', 'color_token_memo_text', 'color_token_memo_divider',
            'color_token_digital_box_bg', 'color_token_digital_box_border'
        ];

        // 9. Reusable Components (Category Card, Section Header, etc.)
        $componentColorKeys = [
            'color_cat_card_bg', 'color_cat_card_border', 'color_cat_card_border_hover',
            'color_cat_card_title', 'color_cat_card_title_hover', 'color_cat_card_count',
            'color_section_title', 'color_section_link', 'color_section_link_hover',
        ];

        // 10. Home Page Specific Colors
        $homeColorKeys = [
            'color_home_cat_menu_bg', 'color_home_cat_menu_border', 'color_home_cat_header_text', 'color_home_cat_header_icon',
            'color_home_cat_header_badge_bg', 'color_home_cat_header_badge_text', 'color_home_cat_item_text', 'color_home_cat_item_hover_bg',
            'color_home_cat_item_hover_text', 'color_home_cat_item_active_bg', 'color_home_cat_item_active_text', 'color_home_cat_flyout_bg',
            'color_home_cat_flyout_border', 'color_home_cat_flyout_item_text', 'color_home_cat_flyout_item_hover_bg', 'color_home_cat_flyout_item_hover_text',
            'color_home_deals_card_bg', 'color_home_deals_card_border', 'color_home_deals_timer_box_bg', 'color_home_deals_badge_bg',
            'color_home_deals_badge_text', 'color_home_deals_title', 'color_home_deals_subtitle', 'color_home_deals_tile_bg',
            'color_home_deals_tile_text', 'color_home_deals_tile_label',
            'color_home_pop_card_bg', 'color_home_pop_card_border', 'color_home_pop_title', 'color_home_pop_title_icon',
            'color_home_pop_btn_text', 'color_home_pop_btn_border', 'color_home_pop_btn_hover_bg', 'color_home_pop_btn_hover_text',
            'color_home_pop_pill_bg', 'color_home_pop_pill_border', 'color_home_pop_pill_hover_border', 'color_home_pop_pill_icon_bg',
            'color_home_pop_pill_icon', 'color_home_pop_pill_text', 'color_home_pop_pill_count',
            'color_home_feat_card_bg', 'color_home_feat_card_border', 'color_home_feat_title', 'color_home_feat_title_icon',
            'color_home_feat_subtitle', 'color_home_feat_filter_active_bg', 'color_home_feat_filter_active_text', 'color_home_feat_filter_btn_bg',
            'color_home_feat_filter_btn_text', 'color_home_feat_filter_btn_border', 'color_home_feat_filter_btn_hover_bg', 'color_home_feat_filter_btn_hover_text'
        ];

        // 11. Shop Page Specific Colors
        $shopColorKeys = [
            'color_shop_page_bg', 'color_shop_top_bg', 'color_shop_top_border', 'color_shop_heading', 'color_shop_showing_text',
            'color_shop_sort_bg', 'color_shop_sort_text', 'color_shop_sort_border', 'color_shop_sort_focus_border',
            'color_shop_sidebar_bg', 'color_shop_sidebar_border', 'color_shop_cat_head_text', 'color_shop_cat_head_icon',
            'color_shop_cat_badge_bg', 'color_shop_cat_badge_text', 'color_shop_cat_item_text', 'color_shop_cat_item_hover_bg',
            'color_shop_cat_item_hover_text', 'color_shop_cat_item_active_bg', 'color_shop_cat_item_active_text', 'color_shop_flyout_bg',
            'color_shop_flyout_border', 'color_shop_flyout_text', 'color_shop_flyout_hover_bg', 'color_shop_flyout_hover_text',
            'color_shop_pag_card_bg', 'color_shop_pag_card_border', 'color_shop_pag_counter_text', 'color_shop_pag_btn_bg',
            'color_shop_pag_btn_text', 'color_shop_pag_btn_border', 'color_shop_pag_btn_hover_bg', 'color_shop_pag_btn_hover_text',
            'color_shop_pag_btn_active_bg', 'color_shop_pag_btn_active_text'
        ];

        // 12. Custom Dynamic Page Specific Colors
        $pageColorKeys = [
            'color_page_bg', 'color_page_text', 'color_page_title', 'color_page_card_bg', 'color_page_card_border',
            'color_page_sidebar_bg', 'color_page_sidebar_border', 'color_page_sidebar_header_text', 'color_page_sidebar_text',
            'color_page_breadcrumb_link', 'color_page_breadcrumb_hover'
        ];

        // 13. Blog Pages Specific Colors
        $blogColorKeys = [
            'color_blog_page_bg', 'color_blog_hero_bg', 'color_blog_hero_border', 'color_blog_hero_badge_bg', 'color_blog_hero_badge_text',
            'color_blog_hero_title', 'color_blog_hero_desc',
            'color_blog_search_bg', 'color_blog_search_text', 'color_blog_search_border', 'color_blog_search_btn_bg', 'color_blog_search_btn_text',
            'color_blog_chip_bg', 'color_blog_chip_text', 'color_blog_chip_border', 'color_blog_chip_active_bg', 'color_blog_chip_active_text',
            'color_blog_chip_hover_bg', 'color_blog_chip_hover_text',
            'color_blog_card_bg', 'color_blog_card_border', 'color_blog_card_hover_border', 'color_blog_badge_bg', 'color_blog_badge_text',
            'color_blog_meta_text', 'color_blog_meta_icon', 'color_blog_title', 'color_blog_title_hover', 'color_blog_excerpt', 'color_blog_divider',
            'color_blog_readmore', 'color_blog_readmore_hover',
            'color_blog_pag_active_bg', 'color_blog_pag_active_text', 'color_blog_pag_btn_bg', 'color_blog_pag_btn_text', 'color_blog_pag_btn_border',
            'color_blog_single_text', 'color_blog_comment_bubble_bg', 'color_blog_comment_text', 'color_blog_comment_btn_bg', 'color_blog_comment_btn_text'
        ];

        // 14. Auth (Login/Register/Reset) & Error Templates
        $authErrorColorKeys = [
            'color_auth_page_bg', 'color_auth_card_bg', 'color_auth_card_border',
            'color_auth_heading', 'color_auth_text',
            'color_auth_input_bg', 'color_auth_input_border', 'color_auth_input_focus_border',
            'color_auth_btn_bg', 'color_auth_btn_text', 'color_auth_btn_hover',
            'color_auth_link', 'color_auth_link_hover'
        ];

        $productModuleKeys = array_merge($productCardColorKeys, $productDetailColorKeys);
        $colorKeys = array_merge(
            $appColorKeys, $headerColorKeys, $footerColorKeys, $productModuleKeys,
            $cartColorKeys, $checkoutColorKeys, $tokenColorKeys, $componentColorKeys,
            $homeColorKeys, $shopColorKeys, $pageColorKeys, $blogColorKeys, $authErrorColorKeys
        );

        $defaults = [
            // App Layout Defaults
            'color_site_bg'                       => '#f8fafc',
            'color_common_btn_bg'                 => '#2563eb',
            'color_common_btn_text'               => '#ffffff',
            'color_app_spb_bg'                    => '#ffffff',
            'color_app_spb_border'                => 'rgba(226, 232, 240, 0.9)',
            'color_app_spb_title'                 => '#0f172a',
            'color_app_spb_title_hover'           => '#2563eb',
            'color_app_spb_price'                 => '#2563eb',
            'color_app_spb_cart_btn_bg'           => '#f8fafc',
            'color_app_spb_cart_btn_text'         => '#0f172a',
            'color_app_spb_cart_btn_hover_bg'     => '#e2e8f0',
            'color_app_spb_buynow_bg'             => '#0dcaf0',
            'color_app_spb_buynow_text'           => '#ffffff',
            'color_app_spb_buynow_hover_bg'       => '#0bb5d8',
            'color_app_spb_buynow_hover_text'     => '#ffffff',
            'color_app_search_modal_bg'           => '#ffffff',
            'color_app_search_modal_border'       => '#e2e8f0',
            'color_app_search_price'              => '#2563eb',
            'color_app_search_cart_btn_bg'        => '#2563eb',
            'color_app_search_cart_btn_text'      => '#ffffff',
            'color_app_search_cart_btn_hover_bg'  => '#1d4ed8',
            'color_app_search_cart_btn_hover_text'=> '#ffffff',
            'color_app_drawer_bg'                 => '#ffffff',
            'color_app_drawer_border'             => '#e2e8f0',
            'color_app_drawer_icon'               => '#2563eb',
            'color_app_drawer_total_price'        => '#dc2626',
            'color_app_drawer_checkout_bg'        => '#0f172a',
            'color_app_drawer_checkout_text'      => '#ffffff',
            'color_app_drawer_checkout_hover_bg'  => '#1e293b',
            'color_app_drawer_checkout_hover_text'=> '#ffffff',
            'color_app_quickview_spinner'         => '#2563eb',
            'color_app_scroll_top_bg'             => '#ea580c',
            'color_app_scroll_top_color'          => '#ffffff',

            // Header Defaults
            'color_hdr_bg'                        => '#ffffff',
            'color_hdr_border'                    => '#e2e8f0',
            'color_hdr_sec_menu_bg'               => '#ffffff',
            'color_hdr_sec_menu_border'           => '#f1f5f9',
            'color_hdr_search_border'             => '#2563eb',
            'color_hdr_search_bg'                 => '#ffffff',
            'color_hdr_search_input_text'         => '#0f172a',
            'color_hdr_search_placeholder'        => '#94a3b8',
            'color_hdr_search_cat_bg'             => '#f8fafc',
            'color_hdr_search_cat_text'           => '#475569',
            'color_hdr_search_btn_bg'             => '#2563eb',
            'color_hdr_search_btn_text'           => '#ffffff',
            'color_hdr_search_btn_hover_bg'       => '#1d4ed8',
            'color_hdr_search_btn_hover_text'     => '#ffffff',
            'color_hdr_icon_text'                 => '#334155',
            'color_hdr_icon_hover'                => '#2563eb',
            'color_hdr_icon_active'               => '#f59e0b',
            'color_hdr_badge_bg'                  => '#ef4444',
            'color_hdr_badge_text'                => '#ffffff',
            'color_hdr_cat_btn_bg'                => '#f8fafc',
            'color_hdr_cat_btn_text'              => '#0f172a',
            'color_hdr_cat_btn_hover_bg'          => '#e2e8f0',
            'color_hdr_cat_btn_hover_text'        => '#2563eb',
            'color_hdr_cat_btn_active_bg'         => '#2563eb',
            'color_hdr_cat_btn_active_text'       => '#ffffff',
            'color_hdr_cat_drop_bg'               => '#ffffff',
            'color_hdr_cat_drop_text'             => '#334155',
            'color_hdr_cat_drop_hover_bg'         => '#f1f5f9',
            'color_hdr_cat_drop_hover_text'       => '#2563eb',
            'color_hdr_menu_text'                 => '#334155',
            'color_hdr_menu_bg'                   => 'transparent',
            'color_hdr_menu_hover_text'           => '#00a8ff',
            'color_hdr_menu_hover_bg'             => '#e0f2fe',
            'color_hdr_menu_active_text'          => '#2563eb',
            'color_hdr_menu_active_bg'            => '#e0f2fe',
            'color_hdr_menu_drop_bg'              => '#ffffff',
            'color_hdr_menu_drop_text'            => '#475569',
            'color_hdr_menu_drop_hover_bg'        => '#f8fafc',
            'color_hdr_menu_drop_hover_text'      => '#2563eb',
            'color_hdr_support_icon'              => '#2563eb',
            'color_hdr_support_label'             => '#64748b',
            'color_hdr_support_phone'             => '#0f172a',
            'color_hdr_support_phone_hover'       => '#2563eb',
            'color_hdr_mob_toggle_bg'             => '#f8fafc',
            'color_hdr_mob_toggle_icon'           => '#0f172a',
            'color_hdr_mob_toggle_hover_bg'       => '#e2e8f0',
            'color_hdr_mob_toggle_hover_icon'     => '#2563eb',
            'color_hdr_mob_head_bg'               => '#f8fafc',
            'color_hdr_mob_head_text'             => '#0f172a',
            'color_hdr_mob_head_icon'             => '#2563eb',
            'color_hdr_mob_tab_bg'                => '#e2e8f0',
            'color_hdr_mob_tab_text'              => '#64748b',
            'color_hdr_mob_tab_active_bg'         => '#2563eb',
            'color_hdr_mob_tab_active_text'       => '#ffffff',
            'color_hdr_mob_body_bg'               => '#ffffff',
            'color_hdr_mob_item_text'             => '#0f172a',
            'color_hdr_mob_item_border'           => '#f1f5f9',
            'color_hdr_mob_item_hover_bg'         => '#f8fafc',
            'color_hdr_mob_item_hover_text'       => '#2563eb',
            'color_hdr_mob_item_active_bg'        => '#eff6ff',
            'color_hdr_mob_item_active_text'      => '#2563eb',

            // Footer Defaults (with Social Icons Hover Fix)
            'color_ftr_bg'                        => '#020b18',
            'color_ftr_border'                    => 'rgba(255, 255, 255, 0.08)',
            'color_ftr_text'                      => '#94a3b8',
            'color_ftr_logo_border'               => 'transparent',
            'color_ftr_brand_title'               => '#ffffff',
            'color_ftr_brand_tagline'             => '#94a3b8',
            'color_ftr_about_text'                => '#94a3b8',
            'color_ftr_heading'                   => '#ffffff',
            'color_ftr_heading_icon'              => '#f59e0b',
            'color_ftr_link_text'                 => '#94a3b8',
            'color_ftr_link_hover_text'           => '#ffffff',
            'color_ftr_link_icon'                 => 'rgba(148, 163, 184, 0.6)',
            'color_ftr_link_hover_icon'           => '#f59e0b',
            'color_ftr_contact_icon'              => '#f59e0b',
            'color_ftr_contact_text'              => '#94a3b8',
            'color_ftr_contact_hover'             => '#ffffff',
            
            // Social Normal & Hover
            'color_ftr_social_icon'               => '#ffffff',
            'color_ftr_social_bg'                 => 'rgba(255, 255, 255, 0.06)',
            'color_ftr_social_border'             => 'rgba(255, 255, 255, 0.08)',
            'color_ftr_social_hover_icon'         => '#ffffff',
            'color_ftr_social_hover_bg'           => '#2563eb',
            'color_ftr_social_hover_border'       => '#2563eb',

            'color_ftr_payment_bg'                => 'rgba(255, 255, 255, 0.03)',
            'color_ftr_payment_border'            => 'rgba(255, 255, 255, 0.1)',
            'color_ftr_payment_text'              => '#ffffff',
            'color_ftr_payment_logo_border'       => 'transparent',
            'color_ftr_payment_hover_bg'          => 'rgba(255, 255, 255, 0.07)',
            'color_ftr_payment_hover_border'      => 'rgba(255, 255, 255, 0.22)',
            'color_ftr_bottom_bg'                 => '#010813',
            'color_ftr_bottom_border'             => 'rgba(255, 255, 255, 0.08)',
            'color_ftr_copyright_text'            => '#94a3b8',
            'color_ftr_developer_text'            => '#ffffff',

            // Mobile Footer Defaults
            'color_ftr_mob_brand_bg'              => 'rgba(255, 255, 255, 0.03)',
            'color_ftr_mob_brand_border'          => 'rgba(255, 255, 255, 0.08)',
            'color_ftr_mob_logo_box_bg'           => 'rgba(255, 255, 255, 0.06)',
            'color_ftr_mob_logo_box_border'       => 'rgba(255, 255, 255, 0.08)',
            'color_ftr_mob_brand_arrow'           => '#94a3b8',
            'color_ftr_mob_acc_bg'                => 'rgba(255, 255, 255, 0.02)',
            'color_ftr_mob_acc_border'            => 'rgba(255, 255, 255, 0.08)',
            'color_ftr_mob_acc_item_border'       => 'rgba(255, 255, 255, 0.06)',
            'color_ftr_mob_acc_btn_text'          => '#ffffff',
            'color_ftr_mob_acc_btn_icon'          => '#94a3b8',
            'color_ftr_mob_acc_chevron'           => '#94a3b8',
            'color_ftr_mob_acc_chevron_active'    => '#f59e0b',
            'color_ftr_mob_acc_body_bg'           => 'transparent',
            'color_ftr_mob_acc_link_text'         => '#94a3b8',
            'color_ftr_mob_acc_link_hover'        => '#ffffff',
            'color_ftr_mob_pay_pill_bg'           => '#ffffff',
            'color_ftr_mob_pay_pill_border'       => 'transparent',
            'color_ftr_mob_pay_pill_text'         => '#0f172a',
            'color_ftr_mob_secure_border'         => 'rgba(255, 255, 255, 0.08)',
            'color_ftr_mob_secure_icon'           => '#ffffff',
            'color_ftr_mob_secure_text'           => '#94a3b8',

            // Product Card Defaults
            'color_cpc_card_bg'                   => '#ffffff',
            'color_cpc_card_border'               => '#edf2f7',
            'color_cpc_card_border_hover'         => '#cbd5e1',
            'color_cpc_card_shadow_hover'         => 'rgba(0,0,0,0.06)',
            'color_cpc_img_wrap_bg'               => '#f8fafc',
            'color_cpc_body_bg'                   => 'transparent',
            'color_cpc_title'                     => '#1e293b',
            'color_cpc_title_hover'               => '#2563eb',
            'color_cpc_category'                  => '#64748b',
            'color_cpc_category_hover'            => '#2563eb',
            'color_cpc_price'                     => '#0f172a',
            'color_cpc_old_price'                 => '#94a3b8',
            'color_cpc_rating_star'               => '#f59e0b',
            'color_cpc_rating_text'               => '#64748b',
            'color_cpc_sale_badge_bg'             => '#ef4444',
            'color_cpc_sale_badge_text'           => '#ffffff',
            'color_cpc_sale_badge_border'         => 'transparent',
            'color_cpc_wishlist_bg'               => '#f1592a',
            'color_cpc_wishlist_icon'             => '#ffffff',
            'color_cpc_wishlist_border'           => 'rgba(255, 255, 255, 0.4)',
            'color_cpc_wishlist_hover_bg'         => '#1f2033',
            'color_cpc_wishlist_hover_icon'       => '#ffffff',
            'color_cpc_wishlist_hover_border'     => 'transparent',
            'color_cpc_wishlist_active_bg'        => '#dc2626',
            'color_cpc_wishlist_active_icon'      => '#ffffff',
            'color_cpc_wishlist_active_border'    => 'transparent',
            'color_cpc_qv_bg'                     => '#ef4444',
            'color_cpc_qv_icon'                   => '#ffffff',
            'color_cpc_qv_border'                 => 'rgba(255, 255, 255, 0.4)',
            'color_cpc_qv_hover_bg'               => '#1f2033',
            'color_cpc_qv_hover_icon'             => '#ffffff',
            'color_cpc_qv_hover_border'           => 'transparent',
            'color_cpc_cart_bg'                   => '#2563eb',
            'color_cpc_cart_text'                 => '#ffffff',
            'color_cpc_cart_border'               => '#2563eb',
            'color_cpc_cart_icon'                 => '#ffffff',
            'color_cpc_cart_hover_bg'             => '#1d4ed8',
            'color_cpc_cart_hover_text'           => '#ffffff',
            'color_cpc_cart_hover_border'         => '#1d4ed8',
            'color_cpc_cart_hover_icon'           => '#ffffff',
            'color_cpc_order_bg'                  => '#0f172a',
            'color_cpc_order_text'                => '#ffffff',
            'color_cpc_order_border'              => '#0f172a',
            'color_cpc_order_icon'                => '#f59e0b',
            'color_cpc_order_hover_bg'            => '#1e293b',
            'color_cpc_order_hover_text'          => '#ffffff',
            'color_cpc_order_hover_border'        => '#1e293b',
            'color_cpc_order_hover_icon'          => '#f59e0b',
            'color_cpc_opt_bg'                    => '#0f172a',
            'color_cpc_opt_text'                  => '#ffffff',
            'color_cpc_opt_border'                => '#0f172a',
            'color_cpc_opt_icon'                  => '#ffffff',
            'color_cpc_opt_hover_bg'              => '#1e293b',
            'color_cpc_opt_hover_text'            => '#ffffff',
            'color_cpc_opt_hover_border'          => '#1e293b',
            'color_cpc_opt_hover_icon'            => '#ffffff',
            'color_cpc_stockout_bg'               => '#fee2e2',
            'color_cpc_stockout_text'             => '#dc2626',
            'color_cpc_stockout_border'           => '#fca5a5',
            'color_cpc_stockout_icon'             => '#dc2626',

            // Product Details & Quick View Defaults (with Buy Now, Cart & WhatsApp Fixed)
            'color_pdp_wrapper_bg'                => '#f8fafc',
            'color_pdp_card_bg'                   => '#ffffff',
            'color_pdp_card_border'               => '#edf2f7',
            'color_pdp_zoom_bg'                   => '#ffffff',
            'color_pdp_zoom_border'               => '#f1f5f9',
            'color_pdp_lens_border'               => '#2563eb',
            'color_pdp_thumb_border'              => '#e2e8f0',
            'color_pdp_thumb_active_border'       => '#2563eb',
            'color_pdp_thumb_arrow_bg'            => '#f8fafc',
            'color_pdp_thumb_arrow_text'          => '#475569',
            'color_pdp_thumb_arrow_hover_bg'      => '#2563eb',
            'color_pdp_thumb_arrow_hover_text'    => '#ffffff',
            'color_pdp_discount_bg'               => '#ef4444',
            'color_pdp_discount_text'             => '#ffffff',
            'color_pdp_breadcrumb_link'           => '#64748b',
            'color_pdp_breadcrumb_hover'          => '#2563eb',
            'color_pdp_breadcrumb_active'         => '#0f172a',
            'color_pdp_title'                     => '#0f172a',
            'color_pdp_rating_star'               => '#f59e0b',
            'color_pdp_rating_text'               => '#64748b',
            'color_pdp_action_btn_bg'             => '#ffffff',
            'color_pdp_action_btn_border'         => '#e2e8f0',
            'color_pdp_action_btn_icon'           => '#64748b',
            'color_pdp_action_btn_hover_bg'       => '#f8fafc',
            'color_pdp_action_btn_hover_border'   => '#cbd5e1',
            'color_pdp_action_btn_hover_icon'     => '#2563eb',
            'color_pdp_action_btn_active_bg'      => '#fee2e2',
            'color_pdp_action_btn_active_border'  => '#fca5a5',
            'color_pdp_action_btn_active_icon'    => '#ef4444',
            'color_pdp_sku_badge_bg'              => '#f8fafc',
            'color_pdp_sku_badge_text'            => '#2563eb',
            'color_pdp_sku_badge_border'          => '#e2e8f0',
            'color_pdp_stock_in_bg'               => '#ecfdf5',
            'color_pdp_stock_in_text'             => '#059669',
            'color_pdp_stock_in_border'           => '#a7f3d0',
            'color_pdp_stock_out_bg'              => '#fef2f2',
            'color_pdp_stock_out_text'            => '#dc2626',
            'color_pdp_stock_out_border'          => '#fecaca',
            'color_pdp_stock_pre_bg'              => '#fffbeb',
            'color_pdp_stock_pre_text'            => '#d97706',
            'color_pdp_stock_pre_border'          => '#fde68a',
            'color_pdp_price_box_bg'              => '#f8fafc',
            'color_pdp_price_box_border'          => '#edf2f7',
            'color_pdp_price'                     => '#dc2626',
            'color_pdp_old_price'                 => '#94a3b8',
            'color_pdp_saving_bg'                 => '#fee2e2',
            'color_pdp_saving_text'               => '#dc2626',
            'color_pdp_estimated_text'            => '#475569',
            'color_pdp_short_desc_bg'             => '#fdfdfd',
            'color_pdp_short_desc_border'         => '#2563eb',
            'color_pdp_short_desc_text'           => '#475569',
            'color_pdp_swatch_label'              => '#475569',
            'color_pdp_swatch_active_label'       => '#2563eb',
            'color_pdp_swatch_bg'                 => '#ffffff',
            'color_pdp_swatch_text'               => '#1e293b',
            'color_pdp_swatch_border'             => '#cbd5e1',
            'color_pdp_swatch_hover_bg'           => '#f8fafc',
            'color_pdp_swatch_hover_border'       => '#94a3b8',
            'color_pdp_swatch_active_bg'          => '#eff6ff',
            'color_pdp_swatch_active_text'        => '#2563eb',
            'color_pdp_swatch_active_border'      => '#2563eb',
            'color_pdp_qty_label'                 => '#0f172a',
            'color_pdp_qty_border'                => '#cbd5e1',
            'color_pdp_qty_btn_bg'                => '#f8fafc',
            'color_pdp_qty_btn_text'              => '#334155',
            'color_pdp_qty_btn_border'            => '#cbd5e1',
            'color_pdp_qty_btn_hover_bg'          => '#e2e8f0',
            'color_pdp_qty_btn_hover_text'        => '#0f172a',
            'color_pdp_qty_input_bg'              => '#ffffff',
            'color_pdp_qty_input_text'            => '#0f172a',

            // Buy Now Controls
            'color_pdp_buynow_bg'                 => '#0dcaf0',
            'color_pdp_buynow_text'               => '#ffffff',
            'color_pdp_buynow_border'             => '#0dcaf0',
            'color_pdp_buynow_icon'               => '#ffffff',
            'color_pdp_buynow_hover_bg'           => '#0bb5d8',
            'color_pdp_buynow_hover_text'         => '#ffffff',
            'color_pdp_buynow_hover_border'       => '#0bb5d8',
            'color_pdp_buynow_hover_icon'         => '#ffffff',

            // Cart Controls
            'color_pdp_cart_bg'                   => '#ffc107',
            'color_pdp_cart_text'                 => '#212529',
            'color_pdp_cart_border'               => '#ffc107',
            'color_pdp_cart_icon'                 => '#212529',
            'color_pdp_cart_hover_bg'             => '#e0a800',
            'color_pdp_cart_hover_text'           => '#212529',
            'color_pdp_cart_hover_border'         => '#e0a800',
            'color_pdp_cart_hover_icon'           => '#212529',

            // WhatsApp Controls Fixed
            'color_pdp_wa_bg'                     => '#25D366',
            'color_pdp_wa_text'                   => '#ffffff',
            'color_pdp_wa_border'                 => '#25D366',
            'color_pdp_wa_icon'                   => '#ffffff',
            'color_pdp_wa_hover_bg'               => '#128C7E',
            'color_pdp_wa_hover_text'             => '#ffffff',
            'color_pdp_wa_hover_border'           => '#128C7E',
            'color_pdp_wa_hover_icon'             => '#ffffff',

            'color_pdp_stockout_bg'               => '#fee2e2',
            'color_pdp_stockout_text'             => '#dc2626',
            'color_pdp_stockout_border'           => '#fca5a5',
            'color_pdp_stockout_icon'             => '#dc2626',
            'color_pdp_sidebar_bg'                => '#ffffff',
            'color_pdp_sidebar_border'            => '#edf2f7',
            'color_pdp_sidebar_brand_title'       => '#0f172a',
            'color_pdp_sidebar_verified_badge'    => '#16a34a',
            'color_pdp_sidebar_heading'           => '#475569',
            'color_pdp_sidebar_feature_icon_bg'   => '#eff6ff',
            'color_pdp_sidebar_feature_icon'      => '#2563eb',
            'color_pdp_sidebar_feature_text'      => '#475569',
            'color_pdp_sidebar_social_bg'         => '#ffffff',
            'color_pdp_sidebar_social_border'     => '#cbd5e1',
            'color_pdp_sidebar_social_icon'       => '#64748b',
            'color_pdp_sidebar_social_hover_bg'   => '#2563eb',
            'color_pdp_sidebar_social_hover_border'=> '#2563eb',
            'color_pdp_sidebar_social_hover_icon' => '#ffffff',
            'color_pdp_tab_border'                => '#e2e8f0',
            'color_pdp_tab_link_text'             => '#64748b',
            'color_pdp_tab_link_active_text'      => '#2563eb',
            'color_pdp_tab_link_active_border'    => '#2563eb',
            'color_pdp_desc_text'                 => '#334155',
            'color_pdp_spec_th_bg'                => '#f8fafc',
            'color_pdp_spec_th_text'              => '#0f172a',
            'color_pdp_spec_td_bg'                => '#ffffff',
            'color_pdp_spec_td_text'              => '#334155',
            'color_pdp_spec_border'               => '#e2e8f0',
            'color_pdp_review_box_bg'             => '#f8fafc',
            'color_pdp_review_box_border'         => '#e2e8f0',
            'color_pdp_review_author'             => '#0f172a',
            'color_pdp_review_date'               => '#64748b',
            'color_pdp_review_comment'            => '#334155',
            'color_pdp_review_star'               => '#f59e0b',
            'color_pdp_review_verified_badge_bg'  => '#ecfdf5',
            'color_pdp_review_verified_badge_text'=> '#059669',

            // Cart & Drawer Defaults
            'color_cart_page_bg'                  => '#f8fafc',
            'color_cart_panel_bg'                 => '#ffffff',
            'color_cart_panel_border'             => '#edf2f7',
            'color_cart_item_bg'                  => '#ffffff',
            'color_cart_item_border'              => '#edf2f7',
            'color_cart_item_border_hover'        => '#cbd5e1',
            'color_cart_item_img_bg'              => '#f8fafc',
            'color_cart_item_img_border'          => '#e2e8f0',
            'color_cart_item_title'               => '#0f172a',
            'color_cart_item_title_hover'         => '#2563eb',
            'color_cart_item_price'               => '#0f172a',
            'color_cart_variant_bg'               => '#f8fafc',
            'color_cart_variant_text'             => '#64748b',
            'color_cart_variant_border'           => '#e2e8f0',
            'color_cart_sku_text'                 => '#94a3b8',
            'color_cart_qty_btn_bg'               => '#f8fafc',
            'color_cart_qty_btn_text'             => '#334155',
            'color_cart_qty_btn_border'           => '#e2e8f0',
            'color_cart_qty_btn_hover_bg'         => '#e2e8f0',
            'color_cart_qty_btn_hover_text'       => '#0f172a',
            'color_cart_remove_btn'               => '#ef4444',
            'color_cart_remove_btn_hover'         => '#dc2626',
            'color_cart_checkout_btn_bg'          => '#2563eb',
            'color_cart_checkout_btn_text'        => '#ffffff',
            'color_cart_checkout_btn_hover'       => '#1d4ed8',
            'color_cart_empty_icon'               => '#94a3b8',
            'color_cart_empty_btn_bg'             => '#2563eb',
            'color_cart_empty_btn_text'           => '#ffffff',
            'color_cart_empty_btn_hover_bg'       => '#1d4ed8',

            // Checkout Page Defaults
            'color_checkout_page_bg'              => '#f8fafc',
            'color_checkout_card_bg'              => '#ffffff',
            'color_checkout_card_border'          => '#edf2f7',
            'color_checkout_heading'              => '#0f172a',
            'color_checkout_text'                 => '#334155',
            'color_checkout_label'                => '#0f172a',
            'color_checkout_input_bg'             => '#ffffff',
            'color_checkout_input_border'         => '#cbd5e1',
            'color_checkout_input_focus_border'   => '#2563eb',
            'color_checkout_paybox_bg'            => '#ffffff',
            'color_checkout_paybox_border'        => '#e2e8f0',
            'color_checkout_paybox_active_bg'     => '#eff6ff',
            'color_checkout_paybox_active_border' => '#2563eb',
            'color_checkout_total_price'          => '#2563eb',
            'color_checkout_btn_bg'               => '#2563eb',
            'color_checkout_btn_text'             => '#ffffff',
            'color_checkout_btn_hover'            => '#1d4ed8',

            // Order Confirmation / Token Defaults
            'color_token_page_bg'                 => '#f1f5f9',
            'color_token_success_icon_bg'         => '#16a34a',
            'color_token_success_icon_text'       => '#ffffff',
            'color_token_heading'                 => '#0f172a',
            'color_token_subtext'                 => '#64748b',
            'color_token_total_card_bg'           => '#ffffff',
            'color_token_total_card_border'       => '#cbd5e1',
            'color_token_total_card_label'        => '#64748b',
            'color_token_total_price'             => '#0f172a',
            'color_token_download_btn_bg'         => '#0f172a',
            'color_token_download_btn_text'       => '#ffffff',
            'color_token_download_btn_hover'      => '#1e293b',
            'color_token_continue_btn_bg'         => '#ffffff',
            'color_token_continue_btn_text'       => '#475569',
            'color_token_continue_btn_border'     => '#cbd5e1',
            'color_token_continue_btn_hover'      => '#f8fafc',
            'color_token_memo_bg'                 => '#ffffff',
            'color_token_memo_border'             => '#94a3b8',
            'color_token_memo_text'               => '#0f172a',
            'color_token_memo_divider'            => '#475569',
            'color_token_digital_box_bg'          => '#f8fafc',
            'color_token_digital_box_border'      => '#2563eb',

            // Category Card Component Defaults
            'color_cat_card_bg'                   => '#f8fafc',
            'color_cat_card_border'               => '#edf2f7',
            'color_cat_card_border_hover'         => '#2563eb',
            'color_cat_card_title'                => '#0f172a',
            'color_cat_card_title_hover'          => '#2563eb',
            'color_cat_card_count'                => '#64748b',

            // Section Header Component Defaults
            'color_section_title'                 => '#0f172a',
            'color_section_link'                  => '#2563eb',
            'color_section_link_hover'            => '#1d4ed8',

            // Home Page Defaults
            'color_home_cat_menu_bg'              => '#ffffff',
            'color_home_cat_menu_border'          => '#edf2f7',
            'color_home_cat_header_text'          => '#0f172a',
            'color_home_cat_header_icon'          => '#2563eb',
            'color_home_cat_header_badge_bg'      => '#eff6ff',
            'color_home_cat_header_badge_text'    => '#2563eb',
            'color_home_cat_item_text'            => '#334155',
            'color_home_cat_item_hover_bg'        => '#f1f5f9',
            'color_home_cat_item_hover_text'      => '#2563eb',
            'color_home_cat_item_active_bg'       => '#eff6ff',
            'color_home_cat_item_active_text'     => '#2563eb',
            'color_home_cat_flyout_bg'            => '#ffffff',
            'color_home_cat_flyout_border'        => '#e2e8f0',
            'color_home_cat_flyout_item_text'     => '#475569',
            'color_home_cat_flyout_item_hover_bg' => '#f8fafc',
            'color_home_cat_flyout_item_hover_text'=> '#2563eb',
            'color_home_deals_card_bg'            => '#ffffff',
            'color_home_deals_card_border'        => '#fee2e2',
            'color_home_deals_timer_box_bg'       => '#0f172a',
            'color_home_deals_badge_bg'           => '#ef4444',
            'color_home_deals_badge_text'         => '#ffffff',
            'color_home_deals_title'              => '#ffffff',
            'color_home_deals_subtitle'           => 'rgba(255, 255, 255, 0.7)',
            'color_home_deals_tile_bg'            => '#ffffff',
            'color_home_deals_tile_text'          => '#0f172a',
            'color_home_deals_tile_label'         => 'rgba(255, 255, 255, 0.7)',
            'color_home_pop_card_bg'              => '#ffffff',
            'color_home_pop_card_border'          => '#edf2f7',
            'color_home_pop_title'                => '#0f172a',
            'color_home_pop_title_icon'           => '#2563eb',
            'color_home_pop_btn_text'             => '#2563eb',
            'color_home_pop_btn_border'           => '#2563eb',
            'color_home_pop_btn_hover_bg'         => '#2563eb',
            'color_home_pop_btn_hover_text'       => '#ffffff',
            'color_home_pop_pill_bg'              => '#ffffff',
            'color_home_pop_pill_border'          => '#edf2f7',
            'color_home_pop_pill_hover_border'    => '#3b82f6',
            'color_home_pop_pill_icon_bg'         => '#f1f5f9',
            'color_home_pop_pill_icon'            => '#475569',
            'color_home_pop_pill_text'            => '#0f172a',
            'color_home_pop_pill_count'           => '#94a3b8',
            'color_home_feat_card_bg'             => '#ffffff',
            'color_home_feat_card_border'         => '#edf2f7',
            'color_home_feat_title'               => '#0f172a',
            'color_home_feat_title_icon'          => '#ef4444',
            'color_home_feat_subtitle'            => '#64748b',
            'color_home_feat_filter_active_bg'    => '#2563eb',
            'color_home_feat_filter_active_text'  => '#ffffff',
            'color_home_feat_filter_btn_bg'       => '#ffffff',
            'color_home_feat_filter_btn_text'     => '#475569',
            'color_home_feat_filter_btn_border'   => '#e2e8f0',
            'color_home_feat_filter_btn_hover_bg' => '#eff6ff',
            'color_home_feat_filter_btn_hover_text'=> '#2563eb',

            // Shop Page Defaults
            'color_shop_page_bg'                  => '#f8fafc',
            'color_shop_top_bg'                   => '#ffffff',
            'color_shop_top_border'               => '#edf2f7',
            'color_shop_heading'                  => '#0f172a',
            'color_shop_showing_text'             => '#64748b',
            'color_shop_sort_bg'                  => '#f8fafc',
            'color_shop_sort_text'                => '#334155',
            'color_shop_sort_border'              => '#cbd5e1',
            'color_shop_sort_focus_border'        => '#2563eb',
            'color_shop_sidebar_bg'               => '#ffffff',
            'color_shop_sidebar_border'           => '#edf2f7',
            'color_shop_cat_head_text'            => '#0f172a',
            'color_shop_cat_head_icon'            => '#2563eb',
            'color_shop_cat_badge_bg'             => '#eff6ff',
            'color_shop_cat_badge_text'           => '#2563eb',
            'color_shop_cat_item_text'            => '#334155',
            'color_shop_cat_item_hover_bg'        => '#f1f5f9',
            'color_shop_cat_item_hover_text'      => '#2563eb',
            'color_shop_cat_item_active_bg'       => '#eff6ff',
            'color_shop_cat_item_active_text'     => '#2563eb',
            'color_shop_flyout_bg'                => '#ffffff',
            'color_shop_flyout_border'            => '#e2e8f0',
            'color_shop_flyout_text'              => '#475569',
            'color_shop_flyout_hover_bg'          => '#f8fafc',
            'color_shop_flyout_hover_text'        => '#2563eb',
            'color_shop_pag_card_bg'              => '#ffffff',
            'color_shop_pag_card_border'          => '#edf2f7',
            'color_shop_pag_counter_text'         => '#64748b',
            'color_shop_pag_btn_bg'               => '#ffffff',
            'color_shop_pag_btn_text'             => '#334155',
            'color_shop_pag_btn_border'           => '#e2e8f0',
            'color_shop_pag_btn_hover_bg'         => '#eff6ff',
            'color_shop_pag_btn_hover_text'       => '#2563eb',
            'color_shop_pag_btn_active_bg'        => '#2563eb',
            'color_shop_pag_btn_active_text'      => '#ffffff',

            // Custom Page Defaults
            'color_page_bg'                       => '#f8fafc',
            'color_page_text'                     => '#1e293b',
            'color_page_title'                    => '#0f172a',
            'color_page_card_bg'                  => '#ffffff',
            'color_page_card_border'              => '#e2e8f0',
            'color_page_sidebar_bg'               => '#ffffff',
            'color_page_sidebar_border'           => '#e2e8f0',
            'color_page_sidebar_header_text'      => '#0f172a',
            'color_page_sidebar_text'             => '#475569',
            'color_page_breadcrumb_link'          => '#64748b',
            'color_page_breadcrumb_hover'         => '#2563eb',

            // Blog Pages Defaults
            'color_blog_page_bg'                  => '#f8fafc',
            'color_blog_hero_bg'                  => '#f8fafc',
            'color_blog_hero_border'              => '#e2e8f0',
            'color_blog_hero_badge_bg'            => '#2563eb',
            'color_blog_hero_badge_text'          => '#ffffff',
            'color_blog_hero_title'               => '#0f172a',
            'color_blog_hero_desc'                => '#64748b',
            'color_blog_search_bg'                => '#ffffff',
            'color_blog_search_text'              => '#0f172a',
            'color_blog_search_border'            => '#cbd5e1',
            'color_blog_search_btn_bg'            => '#2563eb',
            'color_blog_search_btn_text'          => '#ffffff',
            'color_blog_chip_bg'                  => '#ffffff',
            'color_blog_chip_text'                => '#475569',
            'color_blog_chip_border'              => '#e2e8f0',
            'color_blog_chip_active_bg'           => '#2563eb',
            'color_blog_chip_active_text'         => '#ffffff',
            'color_blog_chip_hover_bg'            => '#eff6ff',
            'color_blog_chip_hover_text'          => '#2563eb',
            'color_blog_card_bg'                  => '#ffffff',
            'color_blog_card_border'              => '#edf2f7',
            'color_blog_card_hover_border'        => '#cbd5e1',
            'color_blog_badge_bg'                 => 'rgba(15, 23, 42, 0.75)',
            'color_blog_badge_text'               => '#ffffff',
            'color_blog_meta_text'                => '#64748b',
            'color_blog_meta_icon'                => '#94a3b8',
            'color_blog_title'                    => '#0f172a',
            'color_blog_title_hover'              => '#2563eb',
            'color_blog_excerpt'                  => '#64748b',
            'color_blog_divider'                  => '#f1f5f9',
            'color_blog_readmore'                 => '#2563eb',
            'color_blog_readmore_hover'           => '#1d4ed8',
            'color_blog_pag_active_bg'            => '#2563eb',
            'color_blog_pag_active_text'          => '#ffffff',
            'color_blog_pag_btn_bg'               => '#ffffff',
            'color_blog_pag_btn_text'             => '#334155',
            'color_blog_pag_btn_border'           => '#e2e8f0',
            'color_blog_single_text'              => '#334155',
            'color_blog_comment_bubble_bg'        => '#f1f5f9',
            'color_blog_comment_text'             => '#1e293b',
            'color_blog_comment_btn_bg'           => '#2563eb',
            'color_blog_comment_btn_text'         => '#ffffff',

            // Auth Defaults
            'color_auth_page_bg'                  => '#f8fafc',
            'color_auth_card_bg'                  => '#ffffff',
            'color_auth_card_border'              => '#edf2f7',
            'color_auth_heading'                  => '#0f172a',
            'color_auth_text'                     => '#64748b',
            'color_auth_input_bg'                 => '#ffffff',
            'color_auth_input_border'             => '#cbd5e1',
            'color_auth_input_focus_border'       => '#2563eb',
            'color_auth_btn_bg'                   => '#2563eb',
            'color_auth_btn_text'                 => '#ffffff',
            'color_auth_btn_hover'                => '#1d4ed8',
            'color_auth_link'                     => '#2563eb',
            'color_auth_link_hover'               => '#1d4ed8',
        ];

        $colors = [];
        foreach ($colorKeys as $k) {
            $colors[$k] = $allSettings[$k] ?? ($defaults[$k] ?? '#ffffff');
        }

        return view('admin.theme.colors', compact('colors'));
    }

    public function themeColorsUpdate(Request $request)
    {
        $appColorKeys = [
            'color_site_bg', 'color_common_btn_bg', 'color_common_btn_text',
            'color_app_spb_bg', 'color_app_spb_border', 'color_app_spb_title', 'color_app_spb_title_hover', 'color_app_spb_price',
            'color_app_spb_cart_btn_bg', 'color_app_spb_cart_btn_text', 'color_app_spb_cart_btn_hover_bg',
            'color_app_spb_buynow_bg', 'color_app_spb_buynow_text', 'color_app_spb_buynow_hover_bg', 'color_app_spb_buynow_hover_text',
            'color_app_search_modal_bg', 'color_app_search_modal_border',
            'color_app_search_price', 'color_app_search_cart_btn_bg', 'color_app_search_cart_btn_text', 'color_app_search_cart_btn_hover_bg', 'color_app_search_cart_btn_hover_text',
            'color_app_drawer_bg', 'color_app_drawer_border', 'color_app_drawer_icon', 'color_app_drawer_total_price',
            'color_app_drawer_checkout_bg', 'color_app_drawer_checkout_text', 'color_app_drawer_checkout_hover_bg', 'color_app_drawer_checkout_hover_text',
            'color_app_quickview_spinner',
            'color_app_scroll_top_bg', 'color_app_scroll_top_color'
        ];

        $headerColorKeys = [
            'color_hdr_bg', 'color_hdr_border', 'color_hdr_sec_menu_bg', 'color_hdr_sec_menu_border',
            'color_hdr_search_border', 'color_hdr_search_bg', 'color_hdr_search_input_text', 'color_hdr_search_placeholder',
            'color_hdr_search_cat_bg', 'color_hdr_search_cat_text', 'color_hdr_search_btn_bg', 'color_hdr_search_btn_text', 'color_hdr_search_btn_hover_bg', 'color_hdr_search_btn_hover_text',
            'color_hdr_icon_text', 'color_hdr_icon_hover', 'color_hdr_icon_active', 'color_hdr_badge_bg', 'color_hdr_badge_text',
            'color_hdr_cat_btn_bg', 'color_hdr_cat_btn_text', 'color_hdr_cat_btn_hover_bg', 'color_hdr_cat_btn_hover_text', 'color_hdr_cat_btn_active_bg', 'color_hdr_cat_btn_active_text',
            'color_hdr_cat_drop_bg', 'color_hdr_cat_drop_text', 'color_hdr_cat_drop_hover_bg', 'color_hdr_cat_drop_hover_text',
            'color_hdr_menu_text', 'color_hdr_menu_bg', 'color_hdr_menu_hover_text', 'color_hdr_menu_hover_bg', 'color_hdr_menu_active_text', 'color_hdr_menu_active_bg',
            'color_hdr_menu_drop_bg', 'color_hdr_menu_drop_text', 'color_hdr_menu_drop_hover_bg', 'color_hdr_menu_drop_hover_text',
            'color_hdr_support_icon', 'color_hdr_support_label', 'color_hdr_support_phone', 'color_hdr_support_phone_hover',
            'color_hdr_mob_toggle_bg', 'color_hdr_mob_toggle_icon', 'color_hdr_mob_toggle_hover_bg', 'color_hdr_mob_toggle_hover_icon',
            'color_hdr_mob_head_bg', 'color_hdr_mob_head_text', 'color_hdr_mob_head_icon',
            'color_hdr_mob_tab_bg', 'color_hdr_mob_tab_text', 'color_hdr_mob_tab_active_bg', 'color_hdr_mob_tab_active_text',
            'color_hdr_mob_body_bg', 'color_hdr_mob_item_text', 'color_hdr_mob_item_border', 'color_hdr_mob_item_hover_bg', 'color_hdr_mob_item_hover_text',
            'color_hdr_mob_item_active_bg', 'color_hdr_mob_item_active_text'
        ];

        $footerColorKeys = [
            'color_ftr_bg', 'color_ftr_border', 'color_ftr_text', 'color_ftr_logo_border',
            'color_ftr_brand_title', 'color_ftr_brand_tagline', 'color_ftr_about_text',
            'color_ftr_heading', 'color_ftr_heading_icon',
            'color_ftr_link_text', 'color_ftr_link_hover_text', 'color_ftr_link_icon', 'color_ftr_link_hover_icon',
            'color_ftr_contact_icon', 'color_ftr_contact_text', 'color_ftr_contact_hover',
            
            // Social Icons Normal & Hover
            'color_ftr_social_icon', 'color_ftr_social_bg', 'color_ftr_social_border',
            'color_ftr_social_hover_icon', 'color_ftr_social_hover_bg', 'color_ftr_social_hover_border',

            'color_ftr_payment_bg', 'color_ftr_payment_border', 'color_ftr_payment_text', 'color_ftr_payment_logo_border',
            'color_ftr_payment_hover_bg', 'color_ftr_payment_hover_border',
            'color_ftr_bottom_bg', 'color_ftr_bottom_border', 'color_ftr_copyright_text', 'color_ftr_developer_text',

            'color_ftr_mob_brand_bg', 'color_ftr_mob_brand_border', 'color_ftr_mob_logo_box_bg', 'color_ftr_mob_logo_box_border',
            'color_ftr_mob_brand_arrow', 'color_ftr_mob_acc_bg', 'color_ftr_mob_acc_border', 'color_ftr_mob_acc_item_border',
            'color_ftr_mob_acc_btn_text', 'color_ftr_mob_acc_btn_icon', 'color_ftr_mob_acc_chevron', 'color_ftr_mob_acc_chevron_active',
            'color_ftr_mob_acc_body_bg', 'color_ftr_mob_acc_link_text', 'color_ftr_mob_acc_link_hover',
            'color_ftr_mob_pay_pill_bg', 'color_ftr_mob_pay_pill_border', 'color_ftr_mob_pay_pill_text',
            'color_ftr_mob_secure_border', 'color_ftr_mob_secure_icon', 'color_ftr_mob_secure_text'
        ];

        $productCardColorKeys = [
            'color_cpc_card_bg', 'color_cpc_card_border', 'color_cpc_card_border_hover', 'color_cpc_card_shadow_hover',
            'color_cpc_img_wrap_bg', 'color_cpc_body_bg',
            'color_cpc_title', 'color_cpc_title_hover', 'color_cpc_category', 'color_cpc_category_hover',
            'color_cpc_price', 'color_cpc_old_price', 'color_cpc_rating_star', 'color_cpc_rating_text',
            'color_cpc_sale_badge_bg', 'color_cpc_sale_badge_text', 'color_cpc_sale_badge_border',
            'color_cpc_wishlist_bg', 'color_cpc_wishlist_icon', 'color_cpc_wishlist_border',
            'color_cpc_wishlist_hover_bg', 'color_cpc_wishlist_hover_icon', 'color_cpc_wishlist_hover_border',
            'color_cpc_wishlist_active_bg', 'color_cpc_wishlist_active_icon', 'color_cpc_wishlist_active_border',
            'color_cpc_qv_bg', 'color_cpc_qv_icon', 'color_cpc_qv_border',
            'color_cpc_qv_hover_bg', 'color_cpc_qv_hover_icon', 'color_cpc_qv_hover_border',
            'color_cpc_cart_bg', 'color_cpc_cart_text', 'color_cpc_cart_border', 'color_cpc_cart_icon',
            'color_cpc_cart_hover_bg', 'color_cpc_cart_hover_text', 'color_cpc_cart_hover_border', 'color_cpc_cart_hover_icon',
            'color_cpc_order_bg', 'color_cpc_order_text', 'color_cpc_order_border', 'color_cpc_order_icon',
            'color_cpc_order_hover_bg', 'color_cpc_order_hover_text', 'color_cpc_order_hover_border', 'color_cpc_order_hover_icon',
            'color_cpc_opt_bg', 'color_cpc_opt_text', 'color_cpc_opt_border', 'color_cpc_opt_icon',
            'color_cpc_opt_hover_bg', 'color_cpc_opt_hover_text', 'color_cpc_opt_hover_border', 'color_cpc_opt_hover_icon',
            'color_cpc_stockout_bg', 'color_cpc_stockout_text', 'color_cpc_stockout_border', 'color_cpc_stockout_icon',
        ];

        $productDetailColorKeys = [
            'color_pdp_wrapper_bg', 'color_pdp_card_bg', 'color_pdp_card_border',
            'color_pdp_zoom_bg', 'color_pdp_zoom_border', 'color_pdp_lens_border',
            'color_pdp_thumb_border', 'color_pdp_thumb_active_border',
            'color_pdp_thumb_arrow_bg', 'color_pdp_thumb_arrow_text', 'color_pdp_thumb_arrow_hover_bg', 'color_pdp_thumb_arrow_hover_text',
            'color_pdp_discount_bg', 'color_pdp_discount_text',
            'color_pdp_breadcrumb_link', 'color_pdp_breadcrumb_hover', 'color_pdp_breadcrumb_active',
            'color_pdp_title', 'color_pdp_rating_star', 'color_pdp_rating_text',
            'color_pdp_action_btn_bg', 'color_pdp_action_btn_border', 'color_pdp_action_btn_icon',
            'color_pdp_action_btn_hover_bg', 'color_pdp_action_btn_hover_border', 'color_pdp_action_btn_hover_icon',
            'color_pdp_action_btn_active_bg', 'color_pdp_action_btn_active_border', 'color_pdp_action_btn_active_icon',
            'color_pdp_sku_badge_bg', 'color_pdp_sku_badge_text', 'color_pdp_sku_badge_border',
            'color_pdp_stock_in_bg', 'color_pdp_stock_in_text', 'color_pdp_stock_in_border',
            'color_pdp_stock_out_bg', 'color_pdp_stock_out_text', 'color_pdp_stock_out_border',
            'color_pdp_stock_pre_bg', 'color_pdp_stock_pre_text', 'color_pdp_stock_pre_border',
            'color_pdp_price_box_bg', 'color_pdp_price_box_border',
            'color_pdp_price', 'color_pdp_old_price',
            'color_pdp_saving_bg', 'color_pdp_saving_text', 'color_pdp_estimated_text',
            'color_pdp_short_desc_bg', 'color_pdp_short_desc_border', 'color_pdp_short_desc_text',
            'color_pdp_swatch_label', 'color_pdp_swatch_active_label',
            'color_pdp_swatch_bg', 'color_pdp_swatch_text', 'color_pdp_swatch_border',
            'color_pdp_swatch_hover_bg', 'color_pdp_swatch_hover_border',
            'color_pdp_swatch_active_bg', 'color_pdp_swatch_active_text', 'color_pdp_swatch_active_border',
            'color_pdp_qty_label', 'color_pdp_qty_border',
            'color_pdp_qty_btn_bg', 'color_pdp_qty_btn_text', 'color_pdp_qty_btn_border',
            'color_pdp_qty_btn_hover_bg', 'color_pdp_qty_btn_hover_text',
            'color_pdp_qty_input_bg', 'color_pdp_qty_input_text',

            // Buy Now Controls
            'color_pdp_buynow_bg', 'color_pdp_buynow_text', 'color_pdp_buynow_border', 'color_pdp_buynow_icon',
            'color_pdp_buynow_hover_bg', 'color_pdp_buynow_hover_text', 'color_pdp_buynow_hover_border', 'color_pdp_buynow_hover_icon',

            // Cart Controls
            'color_pdp_cart_bg', 'color_pdp_cart_text', 'color_pdp_cart_border', 'color_pdp_cart_icon',
            'color_pdp_cart_hover_bg', 'color_pdp_cart_hover_text', 'color_pdp_cart_hover_border', 'color_pdp_cart_hover_icon',

            // WhatsApp Controls Fixed
            'color_pdp_wa_bg', 'color_pdp_wa_text', 'color_pdp_wa_border', 'color_pdp_wa_icon',
            'color_pdp_wa_hover_bg', 'color_pdp_wa_hover_text', 'color_pdp_wa_hover_border', 'color_pdp_wa_hover_icon',

            'color_pdp_stockout_bg', 'color_pdp_stockout_text', 'color_pdp_stockout_border', 'color_pdp_stockout_icon',
            'color_pdp_sidebar_bg', 'color_pdp_sidebar_border', 'color_pdp_sidebar_brand_title',
            'color_pdp_sidebar_verified_badge', 'color_pdp_sidebar_heading',
            'color_pdp_sidebar_feature_icon_bg', 'color_pdp_sidebar_feature_icon', 'color_pdp_sidebar_feature_text',
            'color_pdp_sidebar_social_bg', 'color_pdp_sidebar_social_border', 'color_pdp_sidebar_social_icon',
            'color_pdp_sidebar_social_hover_bg', 'color_pdp_sidebar_social_hover_border', 'color_pdp_sidebar_social_hover_icon',
            'color_pdp_tab_border', 'color_pdp_tab_link_text', 'color_pdp_tab_link_active_text', 'color_pdp_tab_link_active_border',
            'color_pdp_desc_text',
            'color_pdp_spec_th_bg', 'color_pdp_spec_th_text', 'color_pdp_spec_td_bg', 'color_pdp_spec_td_text', 'color_pdp_spec_border',
            'color_pdp_review_box_bg', 'color_pdp_review_box_border',
            'color_pdp_review_author', 'color_pdp_review_date', 'color_pdp_review_comment',
            'color_pdp_review_star', 'color_pdp_review_verified_badge_bg', 'color_pdp_review_verified_badge_text'
        ];

        $cartColorKeys = [
            'color_cart_page_bg', 'color_cart_panel_bg', 'color_cart_panel_border',
            'color_cart_item_bg', 'color_cart_item_border', 'color_cart_item_border_hover',
            'color_cart_item_img_bg', 'color_cart_item_img_border',
            'color_cart_item_title', 'color_cart_item_title_hover', 'color_cart_item_price',
            'color_cart_variant_bg', 'color_cart_variant_text', 'color_cart_variant_border',
            'color_cart_sku_text',
            'color_cart_qty_btn_bg', 'color_cart_qty_btn_text', 'color_cart_qty_btn_border',
            'color_cart_qty_btn_hover_bg', 'color_cart_qty_btn_hover_text',
            'color_cart_remove_btn', 'color_cart_remove_btn_hover',
            'color_cart_checkout_btn_bg', 'color_cart_checkout_btn_text', 'color_cart_checkout_btn_hover',
            'color_cart_empty_icon', 'color_cart_empty_btn_bg', 'color_cart_empty_btn_text', 'color_cart_empty_btn_hover_bg'
        ];

        $checkoutColorKeys = [
            'color_checkout_page_bg', 'color_checkout_card_bg', 'color_checkout_card_border',
            'color_checkout_heading', 'color_checkout_text', 'color_checkout_label',
            'color_checkout_input_bg', 'color_checkout_input_border', 'color_checkout_input_focus_border',
            'color_checkout_paybox_bg', 'color_checkout_paybox_border',
            'color_checkout_paybox_active_bg', 'color_checkout_paybox_active_border',
            'color_checkout_total_price',
            'color_checkout_btn_bg', 'color_checkout_btn_text', 'color_checkout_btn_hover'
        ];

        $tokenColorKeys = [
            'color_token_page_bg', 'color_token_success_icon_bg', 'color_token_success_icon_text',
            'color_token_heading', 'color_token_subtext',
            'color_token_total_card_bg', 'color_token_total_card_border', 'color_token_total_card_label', 'color_token_total_price',
            'color_token_download_btn_bg', 'color_token_download_btn_text', 'color_token_download_btn_hover',
            'color_token_continue_btn_bg', 'color_token_continue_btn_text', 'color_token_continue_btn_border', 'color_token_continue_btn_hover',
            'color_token_memo_bg', 'color_token_memo_border', 'color_token_memo_text', 'color_token_memo_divider',
            'color_token_digital_box_bg', 'color_token_digital_box_border'
        ];

        $componentColorKeys = [
            'color_cat_card_bg', 'color_cat_card_border', 'color_cat_card_border_hover',
            'color_cat_card_title', 'color_cat_card_title_hover', 'color_cat_card_count',
            'color_section_title', 'color_section_link', 'color_section_link_hover',
        ];

        $homeColorKeys = [
            'color_home_cat_menu_bg', 'color_home_cat_menu_border', 'color_home_cat_header_text', 'color_home_cat_header_icon',
            'color_home_cat_header_badge_bg', 'color_home_cat_header_badge_text', 'color_home_cat_item_text', 'color_home_cat_item_hover_bg',
            'color_home_cat_item_hover_text', 'color_home_cat_item_active_bg', 'color_home_cat_item_active_text', 'color_home_cat_flyout_bg',
            'color_home_cat_flyout_border', 'color_home_cat_flyout_item_text', 'color_home_cat_flyout_item_hover_bg', 'color_home_cat_flyout_item_hover_text',
            'color_home_deals_card_bg', 'color_home_deals_card_border', 'color_home_deals_timer_box_bg', 'color_home_deals_badge_bg',
            'color_home_deals_badge_text', 'color_home_deals_title', 'color_home_deals_subtitle', 'color_home_deals_tile_bg',
            'color_home_deals_tile_text', 'color_home_deals_tile_label',
            'color_home_pop_card_bg', 'color_home_pop_card_border', 'color_home_pop_title', 'color_home_pop_title_icon',
            'color_home_pop_btn_text', 'color_home_pop_btn_border', 'color_home_pop_btn_hover_bg', 'color_home_pop_btn_hover_text',
            'color_home_pop_pill_bg', 'color_home_pop_pill_border', 'color_home_pop_pill_hover_border', 'color_home_pop_pill_icon_bg',
            'color_home_pop_pill_icon', 'color_home_pop_pill_text', 'color_home_pop_pill_count',
            'color_home_feat_card_bg', 'color_home_feat_card_border', 'color_home_feat_title', 'color_home_feat_title_icon',
            'color_home_feat_subtitle', 'color_home_feat_filter_active_bg', 'color_home_feat_filter_active_text', 'color_home_feat_filter_btn_bg',
            'color_home_feat_filter_btn_text', 'color_home_feat_filter_btn_border', 'color_home_feat_filter_btn_hover_bg', 'color_home_feat_filter_btn_hover_text'
        ];

        $shopColorKeys = [
            'color_shop_page_bg', 'color_shop_top_bg', 'color_shop_top_border', 'color_shop_heading', 'color_shop_showing_text',
            'color_shop_sort_bg', 'color_shop_sort_text', 'color_shop_sort_border', 'color_shop_sort_focus_border',
            'color_shop_sidebar_bg', 'color_shop_sidebar_border', 'color_shop_cat_head_text', 'color_shop_cat_head_icon',
            'color_shop_cat_badge_bg', 'color_shop_cat_badge_text', 'color_shop_cat_item_text', 'color_shop_cat_item_hover_bg',
            'color_shop_cat_item_hover_text', 'color_shop_cat_item_active_bg', 'color_shop_cat_item_active_text', 'color_shop_flyout_bg',
            'color_shop_flyout_border', 'color_shop_flyout_text', 'color_shop_flyout_hover_bg', 'color_shop_flyout_hover_text',
            'color_shop_pag_card_bg', 'color_shop_pag_card_border', 'color_shop_pag_counter_text', 'color_shop_pag_btn_bg',
            'color_shop_pag_btn_text', 'color_shop_pag_btn_border', 'color_shop_pag_btn_hover_bg', 'color_shop_pag_btn_hover_text',
            'color_shop_pag_btn_active_bg', 'color_shop_pag_btn_active_text'
        ];

        $pageColorKeys = [
            'color_page_bg', 'color_page_text', 'color_page_title', 'color_page_card_bg', 'color_page_card_border',
            'color_page_sidebar_bg', 'color_page_sidebar_border', 'color_page_sidebar_header_text', 'color_page_sidebar_text',
            'color_page_breadcrumb_link', 'color_page_breadcrumb_hover'
        ];

        $blogColorKeys = [
            'color_blog_page_bg', 'color_blog_hero_bg', 'color_blog_hero_border', 'color_blog_hero_badge_bg', 'color_blog_hero_badge_text',
            'color_blog_hero_title', 'color_blog_hero_desc',
            'color_blog_search_bg', 'color_blog_search_text', 'color_blog_search_border', 'color_blog_search_btn_bg', 'color_blog_search_btn_text',
            'color_blog_chip_bg', 'color_blog_chip_text', 'color_blog_chip_border', 'color_blog_chip_active_bg', 'color_blog_chip_active_text',
            'color_blog_chip_hover_bg', 'color_blog_chip_hover_text',
            'color_blog_card_bg', 'color_blog_card_border', 'color_blog_card_hover_border', 'color_blog_badge_bg', 'color_blog_badge_text',
            'color_blog_meta_text', 'color_blog_meta_icon', 'color_blog_title', 'color_blog_title_hover', 'color_blog_excerpt', 'color_blog_divider',
            'color_blog_readmore', 'color_blog_readmore_hover',
            'color_blog_pag_active_bg', 'color_blog_pag_active_text', 'color_blog_pag_btn_bg', 'color_blog_pag_btn_text', 'color_blog_pag_btn_border',
            'color_blog_single_text', 'color_blog_comment_bubble_bg', 'color_blog_comment_text', 'color_blog_comment_btn_bg', 'color_blog_comment_btn_text'
        ];

        $authErrorColorKeys = [
            'color_auth_page_bg', 'color_auth_card_bg', 'color_auth_card_border',
            'color_auth_heading', 'color_auth_text',
            'color_auth_input_bg', 'color_auth_input_border', 'color_auth_input_focus_border',
            'color_auth_btn_bg', 'color_auth_btn_text', 'color_auth_btn_hover',
            'color_auth_link', 'color_auth_link_hover'
        ];

        $productModuleKeys = array_merge($productCardColorKeys, $productDetailColorKeys);
        $colorKeys = array_merge(
            $appColorKeys, $headerColorKeys, $footerColorKeys, $productModuleKeys,
            $cartColorKeys, $checkoutColorKeys, $tokenColorKeys, $componentColorKeys,
            $homeColorKeys, $shopColorKeys, $pageColorKeys, $blogColorKeys, $authErrorColorKeys
        );

        // Header Reset
        if ($request->input('reset_header') === '1') {
            DB::table('settings')->whereIn('key', $headerColorKeys)->delete();
            $this->flushAllThemeCaches();
            return back()->with('success', 'All header colors restored to system defaults.');
        }

        // Footer Reset
        if ($request->input('reset_footer') === '1') {
            DB::table('settings')->whereIn('key', $footerColorKeys)->delete();
            $this->flushAllThemeCaches();
            return back()->with('success', 'All footer colors restored to system defaults.');
        }

        // Product Module Reset
        if ($request->input('reset_product') === '1') {
            DB::table('settings')->whereIn('key', $productModuleKeys)->delete();
            $this->flushAllThemeCaches();
            return back()->with('success', 'All product card, details and quick view colors restored to defaults.');
        }

        // Cart Module Reset
        if ($request->input('reset_cart') === '1') {
            DB::table('settings')->whereIn('key', $cartColorKeys)->delete();
            $this->flushAllThemeCaches();
            return back()->with('success', 'Cart page and drawer colors restored to defaults.');
        }

        // Checkout Module Reset
        if ($request->input('reset_checkout') === '1') {
            DB::table('settings')->whereIn('key', $checkoutColorKeys)->delete();
            $this->flushAllThemeCaches();
            return back()->with('success', 'Checkout page colors restored to defaults.');
        }

        // Token Module Reset
        if ($request->input('reset_token') === '1') {
            DB::table('settings')->whereIn('key', $tokenColorKeys)->delete();
            $this->flushAllThemeCaches();
            return back()->with('success', 'Order Confirmation token colors restored to defaults.');
        }

        // Components Reset
        if ($request->input('reset_components') === '1') {
            DB::table('settings')->whereIn('key', $componentColorKeys)->delete();
            $this->flushAllThemeCaches();
            return back()->with('success', 'Category and section component colors restored to defaults.');
        }

        // App Master Layout Reset
        if ($request->input('reset_master') === '1') {
            DB::table('settings')->whereIn('key', $appColorKeys)->delete();
            $this->flushAllThemeCaches();
            return back()->with('success', 'App Master layout and floating colors restored to defaults.');
        }

        // Home Page Reset
        if ($request->input('reset_home') === '1') {
            DB::table('settings')->whereIn('key', $homeColorKeys)->delete();
            $this->flushAllThemeCaches();
            return back()->with('success', 'Home page sections and widgets colors restored to defaults.');
        }

        // Shop Page Reset
        if ($request->input('reset_shop') === '1') {
            DB::table('settings')->whereIn('key', $shopColorKeys)->delete();
            $this->flushAllThemeCaches();
            return back()->with('success', 'Shop page and sidebar colors restored to defaults.');
        }

        // Custom Pages Reset
        if ($request->input('reset_pages') === '1') {
            DB::table('settings')->whereIn('key', $pageColorKeys)->delete();
            $this->flushAllThemeCaches();
            return back()->with('success', 'Custom dynamic page colors restored to defaults.');
        }

        // Blog Reset
        if ($request->input('reset_blogs') === '1') {
            DB::table('settings')->whereIn('key', $blogColorKeys)->delete();
            $this->flushAllThemeCaches();
            return back()->with('success', 'Blog and comment section colors restored to defaults.');
        }

        // Auth Reset
        if ($request->input('reset_auth') === '1') {
            DB::table('settings')->whereIn('key', $authErrorColorKeys)->delete();
            $this->flushAllThemeCaches();
            return back()->with('success', 'Authentication and Form colors restored to defaults.');
        }

        // Global Defaults Reset
        if ($request->input('reset_defaults') === '1') {
            DB::table('settings')->whereIn('key', $colorKeys)->delete();
            $this->flushAllThemeCaches();
            return back()->with('success', 'All theme colors restored to system defaults.');
        }

        foreach ($colorKeys as $key) {
            if ($request->filled($key)) {
                $val = trim($request->input($key));
                DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $val, 'updated_at' => now()]
                );

                // Two-way sync with legacy settings for 100% backward compatibility
                if ($key === 'color_hdr_bg') {
                    DB::table('settings')->updateOrInsert(['key' => 'color_header_bg'], ['value' => $val, 'updated_at' => now()]);
                    DB::table('settings')->updateOrInsert(['key' => 'header_bg'], ['value' => $val, 'updated_at' => now()]);
                } elseif ($key === 'color_hdr_icon_text') {
                    DB::table('settings')->updateOrInsert(['key' => 'color_header_text'], ['value' => $val, 'updated_at' => now()]);
                    DB::table('settings')->updateOrInsert(['key' => 'header_text'], ['value' => $val, 'updated_at' => now()]);
                } elseif ($key === 'color_hdr_search_btn_bg') {
                    DB::table('settings')->updateOrInsert(['key' => 'color_header_search_btn_bg'], ['value' => $val, 'updated_at' => now()]);
                } elseif ($key === 'color_hdr_menu_text') {
                    DB::table('settings')->updateOrInsert(['key' => 'color_menu_text'], ['value' => $val, 'updated_at' => now()]);
                    DB::table('settings')->updateOrInsert(['key' => 'menu_color'], ['value' => $val, 'updated_at' => now()]);
                } elseif ($key === 'color_hdr_menu_hover_text') {
                    DB::table('settings')->updateOrInsert(['key' => 'color_menu_hover'], ['value' => $val, 'updated_at' => now()]);
                    DB::table('settings')->updateOrInsert(['key' => 'menu_hover_color'], ['value' => $val, 'updated_at' => now()]);
                } elseif ($key === 'color_hdr_menu_active_bg') {
                    DB::table('settings')->updateOrInsert(['key' => 'color_menu_active_bg'], ['value' => $val, 'updated_at' => now()]);
                    DB::table('settings')->updateOrInsert(['key' => 'menu_hover_bg'], ['value' => $val, 'updated_at' => now()]);
                } elseif ($key === 'color_ftr_bg') {
                    DB::table('settings')->updateOrInsert(['key' => 'footer_bg'], ['value' => $val, 'updated_at' => now()]);
                    DB::table('settings')->updateOrInsert(['key' => 'color_footer_bg'], ['value' => $val, 'updated_at' => now()]);
                } elseif ($key === 'color_ftr_text') {
                    DB::table('settings')->updateOrInsert(['key' => 'footer_text_color'], ['value' => $val, 'updated_at' => now()]);
                    DB::table('settings')->updateOrInsert(['key' => 'color_footer_text'], ['value' => $val, 'updated_at' => now()]);
                } elseif ($key === 'color_ftr_heading') {
                    DB::table('settings')->updateOrInsert(['key' => 'footer_heading_color'], ['value' => $val, 'updated_at' => now()]);
                } elseif ($key === 'color_ftr_bottom_bg') {
                    DB::table('settings')->updateOrInsert(['key' => 'footer_bottom_bg'], ['value' => $val, 'updated_at' => now()]);
                } elseif ($key === 'color_cpc_price' || $key === 'color_pdp_price') {
                    DB::table('settings')->updateOrInsert(['key' => 'product_price_color'], ['value' => $val, 'updated_at' => now()]);
                } elseif ($key === 'color_cpc_title' || $key === 'color_pdp_title') {
                    DB::table('settings')->updateOrInsert(['key' => 'product_title_color'], ['value' => $val, 'updated_at' => now()]);
                }
            }
        }

        $this->flushAllThemeCaches();

        return back()->with('success', 'Theme colors updated successfully.');
    }

    /**
     * Flush all theme-related caches across the entire storefront
     */
    protected function flushAllThemeCaches(): void
    {
        Cache::forget('global_settings_cache');
        Cache::forget('product_card_settings_cache');
        Cache::forget('product_detail_theme_settings_cache');
        cache()->forget('global_settings_cache');
        cache()->forget('product_card_settings_cache');
        cache()->forget('product_detail_theme_settings_cache');
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
            $val = $request->input($key) ?? '';
            DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $val, 'updated_at' => now()]);
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
            $cleanLbl = trim($lbl ?? '');
            if ($cleanLbl !== '') {
                $formattedQuickLinks[] = [
                    'label' => $cleanLbl,
                    'url'   => !empty($qUrls[$i]) ? trim($qUrls[$i]) : '#'
                ];
            }
        }
        DB::table('settings')->updateOrInsert(['key' => 'footer_quick_links_json'], ['value' => json_encode($formattedQuickLinks), 'updated_at' => now()]);

        $sLabels = $request->input('support_link_labels', []);
        $sUrls = $request->input('support_link_urls', []);
        $formattedSupportLinks = [];
        foreach ($sLabels as $i => $lbl) {
            $cleanSub = trim($lbl ?? '');
            if ($cleanSub !== '') {
                $formattedSupportLinks[] = [
                    'label' => $cleanSub,
                    'url'   => !empty($sUrls[$i]) ? trim($sUrls[$i]) : '#'
                ];
            }
        }
        DB::table('settings')->updateOrInsert(['key' => 'footer_support_links_json'], ['value' => json_encode($formattedSupportLinks), 'updated_at' => now()]);

        $socIcons = $request->input('social_icons', []);
        $socUrls = $request->input('social_urls', []);
        $formattedSocials = [];
        foreach ($socIcons as $i => $ico) {
            $cleanIco = trim($ico ?? '');
            if ($cleanIco !== '') {
                $formattedSocials[] = [
                    'icon' => $cleanIco,
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
            $cleanName = trim($name ?? '');
            if ($cleanName !== '' || !empty($imgPath)) {
                $formattedPayments[] = [
                    'name'  => $cleanName,
                    'image' => $imgPath
                ];
            }
        }
        DB::table('settings')->updateOrInsert(['key' => 'footer_payment_badges_json'], ['value' => json_encode($formattedPayments), 'updated_at' => now()]);

        $this->flushAllThemeCaches();

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

        $this->flushAllThemeCaches();

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
            if ($cleanTitle !== '') {
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

        $this->flushAllThemeCaches();

        return back()->with('success', 'All Feature Settings including Sticky Product Bar updated successfully!');
    }
}