<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImagePathService;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Pivot, tag, variant image column ebong digital product-er proyojonio table o column check kora
     */
    protected function ensurePivotTables()
    {
        // 1. Category Pivot
        if (!Schema::hasTable('product_category')) {
            Schema::create('product_category', function ($table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('category_id')->index();
                $table->string('type', 20)->default('parent')->index();
                $table->timestamps();
            });

            $existing = DB::table('products')->whereNotNull('category_id')->get(['id', 'category_id', 'sub_category_id']);
            foreach ($existing as $p) {
                if ($p->category_id) {
                    DB::table('product_category')->insert([
                        'product_id'  => $p->id,
                        'category_id' => $p->category_id,
                        'type'        => 'parent',
                        'created_at'  => now(),
                        'updated_at'  => now()
                    ]);
                }
                if ($p->sub_category_id) {
                    DB::table('product_category')->insert([
                        'product_id'  => $p->id,
                        'category_id' => $p->sub_category_id,
                        'type'        => 'child',
                        'created_at'  => now(),
                        'updated_at'  => now()
                    ]);
                }
            }
        }

        // 2. Brand Pivot
        if (!Schema::hasTable('product_brand')) {
            Schema::create('product_brand', function ($table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('brand_id')->index();
                $table->timestamps();
            });

            $existingBrands = DB::table('products')->whereNotNull('brand_id')->get(['id', 'brand_id']);
            foreach ($existingBrands as $pb) {
                if ($pb->brand_id) {
                    DB::table('product_brand')->insert([
                        'product_id' => $pb->id,
                        'brand_id'   => $pb->brand_id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }

        // 3. Tags & Product Tag Pivot
        if (!Schema::hasTable('tags')) {
            Schema::create('tags', function ($table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('product_tag')) {
            Schema::create('product_tag', function ($table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('tag_id')->index();
                $table->timestamps();
            });
        }

        // 4. Digital Product Downloads
        if (!Schema::hasTable('product_downloads')) {
            Schema::create('product_downloads', function ($table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->string('file_name');
                $table->text('file_path')->nullable();
                $table->text('file_url')->nullable();
                $table->timestamps();
            });
        }

        // 5. Product Licenses
        if (!Schema::hasTable('product_licenses')) {
            Schema::create('product_licenses', function ($table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->text('license_key');
                $table->string('status')->default('available');
                $table->timestamp('assigned_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        // 6. Products Table Digital & Cost Columns
        if (Schema::hasTable('products')) {
            Schema::table('products', function ($table) {
                if (!Schema::hasColumn('products', 'is_virtual')) {
                    $table->boolean('is_virtual')->default(false);
                    $table->boolean('is_downloadable')->default(false);
                    $table->integer('download_limit')->nullable();
                    $table->integer('download_expiry')->nullable();
                    $table->text('access_instructions')->nullable();
                }
                if (!Schema::hasColumn('products', 'purchase_cost')) {
                    $table->decimal('purchase_cost', 12, 2)->default(0.00)->nullable();
                }
            });
        }

        // 7. Product Variants Table Columns (Stock, Purchase Cost, Image, Alert Qty)
        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function ($table) {
                if (!Schema::hasColumn('product_variants', 'image')) {
                    $table->text('image')->nullable();
                }
                if (!Schema::hasColumn('product_variants', 'purchase_cost')) {
                    $table->decimal('purchase_cost', 12, 2)->default(0.00)->nullable();
                }
                if (!Schema::hasColumn('product_variants', 'purchase_price')) {
                    $table->decimal('purchase_price', 12, 2)->default(0.00)->nullable();
                }
                if (!Schema::hasColumn('product_variants', 'stock')) {
                    $table->integer('stock')->default(0);
                }
                if (!Schema::hasColumn('product_variants', 'alert_quantity')) {
                    $table->integer('alert_quantity')->default(5);
                }
            });
        }
    }

    protected function syncProductTags($productId, array $tagNames)
    {
        DB::table('product_tag')->where('product_id', $productId)->delete();

        foreach ($tagNames as $name) {
            $name = trim($name);
            if (empty($name)) continue;

            $slug = Str::slug($name);
            if (empty($slug)) {
                $slug = 'tag-' . md5($name);
            }

            $tag = DB::table('tags')->where('name', $name)->first();
            if (!$tag) {
                $tagId = DB::table('tags')->insertGetId([
                    'name'       => $name,
                    'slug'       => $slug,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                $tagId = $tag->id;
            }

            DB::table('product_tag')->insert([
                'product_id' => $productId,
                'tag_id'     => $tagId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Downloadable File Save Helper
     */
    protected function handleDownloadableFiles(Request $request, $productId)
    {
        if ($request->has('downloadable_files')) {
            foreach ($request->downloadable_files as $index => $fileData) {
                $fileName = trim($fileData['name'] ?? '');
                $fileUrl  = trim($fileData['url'] ?? '');
                $uploadedFilePath = null;

                if ($request->hasFile("downloadable_files.{$index}.file")) {
                    $uploaded = $request->file("downloadable_files.{$index}.file");
                    $originalName = $uploaded->getClientOriginalName();
                    if (empty($fileName)) {
                        $fileName = $originalName;
                    }
                    $storedName = time() . '_' . uniqid() . '.' . $uploaded->getClientOriginalExtension();
                    $uploadedFilePath = $uploaded->storeAs('digital_downloads/' . $productId, $storedName);
                }

                if (!empty($fileName) && (!empty($uploadedFilePath) || !empty($fileUrl))) {
                    DB::table('product_downloads')->insert([
                        'product_id' => $productId,
                        'file_name'  => $fileName,
                        'file_path'  => $uploadedFilePath,
                        'file_url'   => $fileUrl ?: null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Bulk License Key Save Helper
     */
    protected function handleLicenseKeys(Request $request, $productId)
    {
        if ($request->filled('license_keys_input')) {
            $rawKeys = explode("\n", str_replace("\r", "", $request->license_keys_input));
            foreach ($rawKeys as $key) {
                $cleanKey = trim($key);
                if (!empty($cleanKey)) {
                    $exists = DB::table('product_licenses')
                        ->where('product_id', $productId)
                        ->where('license_key', $cleanKey)
                        ->exists();

                    if (!$exists) {
                        DB::table('product_licenses')->insert([
                            'product_id'  => $productId,
                            'license_key' => $cleanKey,
                            'status'      => 'available',
                            'created_at'  => now(),
                            'updated_at'  => now()
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Normalize Media Library or Existing Image Path so it saves cleanly in DB
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
     * Universal Product, Gallery & Variant Image Uploader (Works on both XAMPP & cPanel)
     */
    protected function uploadProductImage($file, $subFolder)
    {
        $originalName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $cleanBase = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'product';
        $filename = time() . '_' . uniqid() . '_' . $cleanBase . '.' . $ext;
        $relativeDir = trim($subFolder, '/');
        $relativePath = 'uploads/' . $relativeDir . '/' . $filename;

        $primaryRoot = (!empty($_SERVER['DOCUMENT_ROOT']) && is_dir($_SERVER['DOCUMENT_ROOT']))
            ? rtrim($_SERVER['DOCUMENT_ROOT'], '/\\')
            : rtrim(public_path(), '/\\');

        $primaryDir = $primaryRoot . '/uploads/' . $relativeDir;
        if (!is_dir($primaryDir)) {
            @mkdir($primaryDir, 0777, true);
        }

        $primaryFullPath = $primaryDir . '/' . $filename;
        $file->move($primaryDir, $filename);

        $candidateRoots = array_unique(array_filter([
            rtrim(public_path(), '/\\'),
            is_dir(base_path('../public_html')) ? rtrim(base_path('../public_html'), '/\\') : null,
        ]));

        foreach ($candidateRoots as $root) {
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
            // safely ignore
        }

        if (Schema::hasTable('media_files')) {
            try {
                $mimeType = @mime_content_type($primaryFullPath) ?: 'image/jpeg';
                $fileSize = @filesize($primaryFullPath) ?: 0;
                $imgInfo  = @getimagesize($primaryFullPath);
                $dims     = $imgInfo ? ($imgInfo[0] . ' × ' . $imgInfo[1]) : null;
                $sourceModule = str_contains($relativeDir, 'variant')
                    ? 'product_variant'
                    : (str_contains($relativeDir, 'gallery') ? 'product_gallery' : 'product');

                $mediaInsert = [
                    'file_name'  => $originalName,
                    'file_path'  => $relativePath,
                    'file_type'  => $mimeType,
                    'file_size'  => $fileSize,
                    'dimensions' => $dims,
                    'title'      => ucwords(str_replace(['-', '_'], ' ', pathinfo($originalName, PATHINFO_FILENAME))),
                    'alt_text'   => ucwords(str_replace(['-', '_'], ' ', pathinfo($originalName, PATHINFO_FILENAME))),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('media_files', 'media_category')) {
                    $mediaInsert['media_category'] = 'site_content';
                }
                if (Schema::hasColumn('media_files', 'source_module')) {
                    $mediaInsert['source_module'] = $sourceModule;
                }
                if (Schema::hasColumn('media_files', 'file_hash')) {
                    $mediaInsert['file_hash'] = @hash_file('sha256', $primaryFullPath) ?: md5($relativePath);
                }
                if (Schema::hasColumn('media_files', 'uploaded_by')) {
                    $mediaInsert['uploaded_by'] = Auth::id() ?? 1;
                }

                DB::table('media_files')->insert($mediaInsert);
            } catch (\Throwable $e) {
                // safely ignore
            }
        }

        return $relativePath;
    }

    protected function deleteProductImage($relativePath)
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

        $roots = ImagePathService::getAllPublicRoots();
        foreach ($roots as $root) {
            foreach ([$root . '/' . $clean, $root . '/storage/' . $clean, $root . '/uploads/' . $clean] as $candidate) {
                if (is_file($candidate)) {
                    @unlink($candidate);
                }
            }
        }

        $storageFile = storage_path('app/public/' . $clean);
        if (is_file($storageFile)) {
            @unlink($storageFile);
        }
    }

    /**
     * Extract variant image from either manual file upload or Media Library selection
     */
    protected function resolveVariantImageFromRequest(Request $request, $idx, array $variant, bool $isUpdate = false): ?string
    {
        $varImgPath = null;

        if ($isUpdate && !empty($variant['existing_image'])) {
            $varImgPath = $this->normalizeMediaInputPath($variant['existing_image']);
        }

        if ($isUpdate && !empty($variant['remove_image']) && (string) $variant['remove_image'] === '1') {
            $varImgPath = null;
        }

        $uploadedFile = null;
        if ($request->hasFile("variants.{$idx}.image")) {
            $uploadedFile = $request->file("variants.{$idx}.image");
        } elseif (isset($request->file('variants')[$idx]['image'])) {
            $uploadedFile = $request->file('variants')[$idx]['image'];
        } elseif (isset($variant['image']) && $variant['image'] instanceof UploadedFile) {
            $uploadedFile = $variant['image'];
        }

        if ($uploadedFile instanceof UploadedFile && $uploadedFile->isValid()) {
            return $this->uploadProductImage($uploadedFile, 'products/variants');
        }

        if (!empty($variant['media_path']) && $variant['media_path'] !== 'undefined') {
            $normalizedMedia = $this->normalizeMediaInputPath($variant['media_path']);
            if (!empty($normalizedMedia)) {
                return $normalizedMedia;
            }
        }

        if (!$isUpdate && !empty($variant['existing_image']) && $variant['existing_image'] !== 'undefined') {
            $normalizedExisting = $this->normalizeMediaInputPath($variant['existing_image']);
            if (!empty($normalizedExisting)) {
                return $normalizedExisting;
            }
        }

        return $varImgPath;
    }

    /**
     * প্রোডাক্ট তালিকা, ফিল্টারিং, সর্টিং, পেজিনেশন ও ভ্যালুয়েশন সামারি
     */
    public function index(Request $request)
    {
        $this->ensurePivotTables();

        $query = DB::table('products');

        if (Schema::hasTable('categories') && Schema::hasColumn('products', 'category_id')) {
            $query->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                  ->select('products.*', 'categories.name as category_name');
        } else {
            $query->select('products.*', DB::raw("NULL as category_name"));
        }

        if (Schema::hasTable('brands') && Schema::hasColumn('products', 'brand_id')) {
            $query->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                  ->addSelect('brands.name as brand_name');
        } else {
            $query->addSelect(DB::raw("NULL as brand_name"));
        }

        // ১. লাইভ সার্চ ফিল্টার
        if ($request->filled('search')) {
            $search = trim($request->search);
            $cleanSearch = ltrim($search, '#');
            $query->where(function ($q) use ($search, $cleanSearch) {
                $q->where('products.title', 'like', "%{$search}%")
                  ->orWhere('products.product_code', 'like', "%{$search}%")
                  ->orWhere('products.barcode', 'like', "%{$search}%")
                  ->orWhere('products.id', $cleanSearch);
                if (Schema::hasColumn('products', 'sku')) {
                    $q->orWhere('products.sku', 'like', "%{$search}%");
                }
            });
        }

        // ২. ক্যাটাগরি ফিল্টার
        if ($request->filled('category_id')) {
            $catId = (int)$request->category_id;
            $query->where(function($q) use ($catId) {
                $q->where('products.category_id', $catId)
                  ->orWhereExists(function($sub) use ($catId) {
                      $sub->select(DB::raw(1))
                          ->from('product_category')
                          ->whereColumn('product_category.product_id', 'products.id')
                          ->where('product_category.category_id', $catId);
                  });
            });
        }

        // ৩. ব্র্যান্ড ফিল্টার
        if ($request->filled('brand_id')) {
            $bId = (int)$request->brand_id;
            $query->where(function($q) use ($bId) {
                $q->where('products.brand_id', $bId)
                  ->orWhereExists(function($sub) use ($bId) {
                      $sub->select(DB::raw(1))
                          ->from('product_brand')
                          ->whereColumn('product_brand.product_id', 'products.id')
                          ->where('product_brand.brand_id', $bId);
                  });
            });
        }

        // ৪. প্রোডাক্ট টাইপ
        if ($request->filled('product_type')) {
            $type = $request->product_type;
            if ($type === 'virtual') {
                $query->where('products.is_virtual', 1);
            } elseif ($type === 'downloadable') {
                $query->where('products.is_downloadable', 1);
            } else {
                $query->where('products.product_type', $type);
            }
        }

        // ৫. স্টক স্ট্যাটাস ফিল্টার
        if ($request->filled('stock_status')) {
            $stockStatus = strtolower($request->stock_status);
            if ($stockStatus === 'instock') {
                $query->where('products.stock', '>', 0);
            } elseif ($stockStatus === 'outofstock') {
                $query->where('products.stock', '<=', 0);
            } elseif ($stockStatus === 'low_stock' || $stockStatus === 'low') {
                $query->where('products.stock', '>', 0)->where('products.stock', '<', 6);
            }
        }

        // ৬. ফিচার্ড প্রোডাক্ট ফিল্টার
        if ($request->filled('is_featured') && $request->is_featured !== 'all') {
            $query->where('products.is_featured', $request->is_featured == '1' ? 1 : 0);
        }

        // ৭. ডেট ফিল্টারিং
        if ($request->filled('date_filter')) {
            $dFilter = $request->date_filter;
            switch ($dFilter) {
                case 'today':
                    $query->whereDate('products.created_at', Carbon::today());
                    break;
                case 'single_date':
                    if ($request->filled('single_date')) {
                        $query->whereDate('products.created_at', Carbon::parse($request->single_date)->toDateString());
                    }
                    break;
                case 'last_month':
                    $query->whereBetween('products.created_at', [
                        Carbon::now()->subMonth()->startOfMonth(),
                        Carbon::now()->subMonth()->endOfMonth()
                    ]);
                    break;
                case '1_month':
                    $query->where('products.created_at', '>=', Carbon::now()->subDays(30)->startOfDay());
                    break;
                case '3_days':
                    $query->where('products.created_at', '>=', Carbon::now()->subDays(3)->startOfDay());
                    break;
                case '7_days':
                    $query->where('products.created_at', '>=', Carbon::now()->subDays(7)->startOfDay());
                    break;
                case 'custom':
                    if ($request->filled('start_date')) {
                        $query->where('products.created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
                    }
                    if ($request->filled('end_date')) {
                        $query->where('products.created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
                    }
                    break;
            }
        } elseif ($request->filled('single_date')) {
            $query->whereDate('products.created_at', Carbon::parse($request->single_date)->toDateString());
        } elseif ($request->filled('start_date') || $request->filled('end_date')) {
            if ($request->filled('start_date')) {
                $query->where('products.created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
            }
            if ($request->filled('end_date')) {
                $query->where('products.created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
            }
        }

        // ৮. প্রাইস ফিল্টার
        if ($request->filled('min_price')) {
            $query->where('products.regular_price', '>=', (float)$request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('products.regular_price', '<=', (float)$request->max_price);
        }

        // ৯. সর্টিং
        $sortBy = $request->get('sort_by', 'latest');
        switch ($sortBy) {
            case 'name_asc':
                $query->orderBy('products.title', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('products.title', 'desc');
                break;
            case 'price_high':
            case 'high_mrp':
                $query->orderBy('products.regular_price', 'desc');
                break;
            case 'price_low':
            case 'low_mrp':
                $query->orderBy('products.regular_price', 'asc');
                break;
            case 'oldest':
            case 'asc':
                $query->orderBy('products.id', 'asc');
                break;
            case 'latest':
            case 'desc':
            default:
                $query->orderBy('products.id', 'desc');
                break;
        }

        // ১০. বটম সামারি ক্যালকুলেশন (Single Source of Truth - Purchase Cost & Stock)
        $summaryQuery       = clone $query;
        $totalProductsCount = (int) $summaryQuery->count();
        $totalStockQty      = (int) (clone $query)->sum('products.stock');
        $totalRetailValue   = (float) (clone $query)->sum(DB::raw('COALESCE(products.stock, 0) * COALESCE(products.regular_price, 0)'));
        $totalPurchaseCost  = (float) (clone $query)->sum(DB::raw('COALESCE(products.stock, 0) * COALESCE(NULLIF(products.purchase_cost, 0), products.purchase_price, 0)'));

        // ১১. এক ক্লিকে EXCEL / CSV এক্সপোর্ট
        if ($request->get('export') === 'excel' || $request->get('export') === 'csv') {
            return $this->exportProductsToExcel($query, $totalProductsCount, $totalStockQty, $totalRetailValue, $totalPurchaseCost);
        }

        // ১২. এক ক্লিকে PDF এক্সপোর্ট
        if ($request->get('export') === 'pdf' || $request->get('export') === 'print') {
            $exportProducts = $query->get();
            return view('admin.product.export_pdf', compact('exportProducts', 'totalProductsCount', 'totalStockQty', 'totalRetailValue', 'totalPurchaseCost', 'request'));
        }

        // ১৩. পেজিনেশন
        $perPageInput = $request->input('per_page', 20);
        $perPage = ($perPageInput === 'all') ? 100000 : max(5, (int) $perPageInput);

        $products = $query->paginate($perPage)->withQueryString();

        // ১৪. AJAX রেসপন্স
        if ($request->ajax()) {
            $from = $products->firstItem() ?? 0;
            $to = $products->lastItem() ?? 0;
            $total = $products->total();

            return response()->json([
                'table_html'      => view('admin.product.partials.table_rows', compact('products'))->render(),
                'pagination_html' => ($perPageInput === 'all' || !$products->hasPages()) ? '' : $products->links('pagination::bootstrap-5')->render(),
                'total_count'     => $total,
                'entries_text'    => "Showing {$from} to {$to} of {$total} entries",
                'summary'         => [
                    'total_products'  => number_format($totalProductsCount),
                    'total_stock'     => number_format($totalStockQty),
                    'retail_value'    => number_format($totalRetailValue, 2),
                    'purchase_cost'   => number_format($totalPurchaseCost, 2),
                ]
            ]);
        }
        
        $categories = Schema::hasTable('categories') ? DB::table('categories')->get() : collect();
        $brands = Schema::hasTable('brands') ? DB::table('brands')->get() : collect();

        return view('admin.product.index', compact(
            'products', 'categories', 'brands', 'perPage', 
            'totalProductsCount', 'totalStockQty', 'totalRetailValue', 'totalPurchaseCost'
        ));
    }

    /**
     * এক্সেল / সিএসভি এক্সপোর্ট জেনারেটর
     */
    protected function exportProductsToExcel($query, $totalCount, $totalStock, $totalRetail, $totalCost)
    {
        $products = $query->get();
        $filename = "products_catalog_" . date('Y_m_d_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Product ID',
            'Title',
            'SKU / Code',
            'Barcode',
            'Type',
            'Structure',
            'Category',
            'Brand',
            'Stock Qty',
            'Stock Status',
            'Regular Price (MRP)',
            'Sale Price',
            'Purchase Cost',
            'Total Stock Retail Value',
            'Featured',
            'Created Date'
        ];

        $callback = function () use ($products, $columns, $totalCount, $totalStock, $totalRetail, $totalCost) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            foreach ($products as $prod) {
                $stockStatus = $prod->stock > 0 ? ($prod->stock < 6 ? 'Low Stock' : 'In Stock') : 'Out of Stock';
                $lineValue = ($prod->stock > 0) ? ($prod->stock * (float)$prod->regular_price) : 0;
                $costVal = !empty($prod->purchase_cost) ? (float)$prod->purchase_cost : (!empty($prod->purchase_price) ? (float)$prod->purchase_price : null);

                fputcsv($file, [
                    '#' . $prod->id,
                    $prod->title,
                    $prod->product_code ?? ($prod->sku ?? '—'),
                    $prod->barcode ?? '—',
                    strtoupper($prod->product_type ?? 'physical'),
                    strtoupper($prod->product_struct ?? 'simple'),
                    $prod->category_name ?? 'Uncategorized',
                    $prod->brand_name ?? '—',
                    $prod->stock,
                    $stockStatus,
                    number_format((float)$prod->regular_price, 2, '.', ''),
                    !empty($prod->sale_price) ? number_format((float)$prod->sale_price, 2, '.', '') : '—',
                    !is_null($costVal) ? number_format($costVal, 2, '.', '') : '—',
                    number_format($lineValue, 2, '.', ''),
                    $prod->is_featured ? 'YES' : 'NO',
                    date('d M Y, h:i A', strtotime($prod->created_at)),
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, [
                'TOTAL CATALOG SUMMARY',
                "Total Products: {$totalCount}",
                '',
                '',
                '',
                '',
                '',
                '',
                "Total Stock: {$totalStock}",
                '',
                '',
                '',
                "Total Cost: " . number_format($totalCost, 2, '.', '') . ' BDT',
                "Total Retail Value: " . number_format($totalRetail, 2, '.', '') . ' BDT',
                '',
                ''
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function create()
    {
        $this->ensurePivotTables();

        $categories = Schema::hasTable('categories') ? DB::table('categories')->orderBy('name', 'asc')->get() : collect();
        $subCategories = Schema::hasTable('sub_categories') ? DB::table('sub_categories')->orderBy('name', 'asc')->get() : collect();
        $brands = Schema::hasTable('brands') ? DB::table('brands')->orderBy('name', 'asc')->get() : collect();

        return view('admin.product.create', compact('categories', 'subCategories', 'brands'));
    }

    public function store(Request $request)
    {
        $this->ensurePivotTables();

        $request->validate([
            'title'            => 'required|string|max:255',
            'regular_price'    => 'required|numeric',
            'stock'            => 'nullable|integer',
            'thumbnail'        => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,svg|max:5120',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,svg|max:5120',
        ]);

        DB::beginTransaction();

        try {
            $thumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $this->uploadProductImage($request->file('thumbnail'), 'products/thumbnails');
            } elseif ($request->filled('thumbnail_media')) {
                $thumbnailPath = $this->normalizeMediaInputPath($request->input('thumbnail_media'));
            }

            $barcode = $request->barcode ?: rand(100000000000, 999999999999);

            $metaDescription = $request->filled('meta_description') 
                ? $request->meta_description 
                : Str::limit(strip_tags($request->short_description ?? $request->description ?? ''), 160);

            $selectedParentIds = array_values(array_unique(array_filter(array_map('intval', (array) $request->input('category_ids', [])))));
            $selectedChildIds  = array_values(array_unique(array_filter(array_map('intval', (array) $request->input('sub_category_ids', [])))));

            $primaryCatId = !empty($selectedParentIds) ? $selectedParentIds[0] : ($request->category_id ?: null);
            $primarySubCatId = !empty($selectedChildIds) ? $selectedChildIds[0] : ($request->sub_category_id ?: null);

            $selectedBrandIds = array_values(array_unique(array_filter(array_map('intval', (array) $request->input('brand_ids', [])))));
            $primaryBrandId = !empty($selectedBrandIds) ? $selectedBrandIds[0] : ($request->brand_id ?: null);

            $isVirtual = $request->has('is_virtual') ? 1 : ($request->product_type === 'virtual' ? 1 : 0);
            $isDownloadable = $request->has('is_downloadable') ? 1 : ($request->product_type === 'downloadable' ? 1 : 0);

            $hasVariantRows = $request->has('variants') && is_array($request->variants) && count($request->variants) > 0;
            $resolvedProductStruct = ($request->product_struct === 'variable' || $hasVariantRows) ? 'variable' : ($request->product_struct ?? 'simple');

            $rawPurchaseCost = $request->filled('purchase_cost') ? $request->purchase_cost : ($request->filled('purchase_price') ? $request->purchase_price : null);

            $productInsertData = [
                'category_id'              => $primaryCatId,
                'sub_category_id'          => $primarySubCatId,
                'brand_id'                 => $primaryBrandId,
                'title'                    => $request->title,
                'slug'                     => Str::slug($request->title) . '-' . strtolower(Str::random(5)),
                'product_code'             => $request->product_code ?? 'PRD-' . strtoupper(Str::random(6)),
                'barcode'                  => $barcode,
                'short_description'        => $request->short_description,
                'description'              => $request->description,
                'product_type'             => $request->product_type ?? 'physical',
                'product_struct'           => $resolvedProductStruct,
                'is_virtual'               => $isVirtual,
                'is_downloadable'          => $isDownloadable,
                'download_limit'           => $request->filled('download_limit') ? (int)$request->download_limit : null,
                'download_expiry'          => $request->filled('download_expiry') ? (int)$request->download_expiry : null,
                'access_instructions'      => $request->access_instructions ?: null,
                'regular_price'            => $request->regular_price,
                'sale_price'               => $request->sale_price ?: null,
                'wholesale_price'          => $request->wholesale_price ?: null,
                'purchase_cost'            => !is_null($rawPurchaseCost) ? (float)$rawPurchaseCost : 0.00,
                'purchase_price'           => !is_null($rawPurchaseCost) ? (float)$rawPurchaseCost : null,
                'discount_percentage'      => $request->discount_percentage ?: null,
                'stock'                    => ($resolvedProductStruct === 'variable') ? 0 : (int)($request->stock ?? 0),
                'allow_out_of_stock_order' => $request->has('allow_out_of_stock_order') ? 1 : 0,
                'thumbnail'                => $thumbnailPath,
                'is_featured'              => $request->has('is_featured') ? 1 : 0,
                'weight'                   => $isVirtual ? null : ($request->weight ?: null),
                'height'                   => $isVirtual ? null : ($request->height ?: null),
                'width'                    => $isVirtual ? null : ($request->width ?: null),
                'length'                   => $isVirtual ? null : ($request->length ?: null),
                'video_url'                => $request->video_url ?: null,
                'is_seo_active'            => 1,
                'focus_keyphrase'          => $request->focus_keyphrase ?: null,
                'meta_description'         => $metaDescription,
                'canonical_url'            => $request->canonical_url ?: null,
                'created_at'               => now(),
                'updated_at'               => now(),
            ];

            if (Schema::hasColumn('products', 'seo_title')) {
                $productInsertData['seo_title'] = $request->seo_title ?? $request->title;
            }

            if (Schema::hasColumn('products', 'sku')) {
                $productInsertData['sku'] = $request->sku ?? null;
            }

            $productId = DB::table('products')->insertGetId($productInsertData);

            // ক্যাটাগরি ও ব্র্যান্ড পিভট ম্যাপিং
            foreach ($selectedParentIds as $pId) {
                DB::table('product_category')->insert([
                    'product_id'  => $productId,
                    'category_id' => $pId,
                    'type'        => 'parent',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
            foreach ($selectedChildIds as $cId) {
                DB::table('product_category')->insert([
                    'product_id'  => $productId,
                    'category_id' => $cId,
                    'type'        => 'child',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            foreach ($selectedBrandIds as $bId) {
                DB::table('product_brand')->insert([
                    'product_id' => $productId,
                    'brand_id'   => $bId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $tagsInput = (array) $request->input('tags', []);
            $this->syncProductTags($productId, $tagsInput);

            if ($isDownloadable) {
                $this->handleDownloadableFiles($request, $productId);
            }

            if ($isVirtual) {
                $this->handleLicenseKeys($request, $productId);
            }

            if ($request->filled('gallery_media') && is_array($request->input('gallery_media'))) {
                foreach ($request->input('gallery_media') as $mediaPath) {
                    $cleanMediaPath = $this->normalizeMediaInputPath($mediaPath);
                    if (!empty($cleanMediaPath)) {
                        DB::table('product_galleries')->insert([
                            'product_id' => $productId,
                            'image_path' => $cleanMediaPath,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }

            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $gFile) {
                    $gImagePath = $this->uploadProductImage($gFile, 'products/gallery');

                    DB::table('product_galleries')->insert([
                        'product_id' => $productId,
                        'image_path' => $gImagePath,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            // ভ্যারিয়েন্ট প্রসেসিং (স্বতন্ত্র Purchase Cost ও Stock সহ)
            if ($resolvedProductStruct === 'variable' && $request->has('variants') && is_array($request->variants)) {
                $totalVarStock = 0;
                foreach ($request->variants as $idx => $variant) {
                    if (!is_array($variant)) continue;

                    $varImgPath = $this->resolveVariantImageFromRequest($request, $idx, $variant, false);
                    $hasColorOrSize = !empty($variant['color']) || !empty($variant['size']);

                    if ($hasColorOrSize || !empty($varImgPath)) {
                        $vStock = isset($variant['stock']) ? max(0, (int)$variant['stock']) : 0;
                        $totalVarStock += $vStock;

                        $vCost = !empty($variant['purchase_cost']) 
                            ? (float)$variant['purchase_cost'] 
                            : (!empty($variant['purchase_price']) ? (float)$variant['purchase_price'] : (!empty($rawPurchaseCost) ? (float)$rawPurchaseCost : 0.00));

                        $varData = [
                            'product_id'     => $productId,
                            'color'          => $variant['color'] ?? null,
                            'size'           => $variant['size'] ?? null,
                            'product_code'   => $variant['sku'] ?? ($variant['product_code'] ?? null),
                            'barcode'        => $variant['barcode'] ?? null,
                            'regular_price'  => !empty($variant['regular_price']) ? (float)$variant['regular_price'] : (float)$request->regular_price,
                            'sale_price'     => !empty($variant['sale_price']) ? (float)$variant['sale_price'] : null,
                            'stock'          => $vStock,
                            'alert_quantity' => isset($variant['alert_quantity']) ? (int)$variant['alert_quantity'] : 5,
                            'image'          => $varImgPath,
                            'purchase_cost'  => $vCost,
                            'purchase_price' => $vCost,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ];

                        if (Schema::hasColumn('product_variants', 'sku')) {
                            $varData['sku'] = $variant['sku'] ?? ($variant['product_code'] ?? null);
                        }

                        DB::table('product_variants')->insert($varData);
                    }
                }

                // প্যারেন্ট স্টক স্বয়ংক্রিয়ভাবে ভ্যারিয়েন্টগুলোর যোগফলের সমান করা
                InventoryService::syncParentProductStock($productId);
            }

            DB::commit();
            return redirect()->route('admin.product.index')->with('success', 'Product created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating product: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $this->ensurePivotTables();

        $product = DB::table('products')->where('id', $id)->first();
        if (!$product) {
            return redirect()->route('admin.product.index')->with('error', 'Product not found.');
        }

        $galleries = DB::table('product_galleries')->where('product_id', $id)->get();
        $variants = DB::table('product_variants')->where('product_id', $id)->get();

        $categories = Schema::hasTable('categories') ? DB::table('categories')->orderBy('name', 'asc')->get() : collect();
        $subCategories = Schema::hasTable('sub_categories') ? DB::table('sub_categories')->orderBy('name', 'asc')->get() : collect();
        $brands = Schema::hasTable('brands') ? DB::table('brands')->orderBy('name', 'asc')->get() : collect();

        $pivotCats = DB::table('product_category')->where('product_id', $id)->get();
        $selectedCatIds = $pivotCats->where('type', 'parent')->pluck('category_id')->map(fn($v) => (int)$v)->toArray();
        $selectedSubCatIds = $pivotCats->where('type', 'child')->pluck('category_id')->map(fn($v) => (int)$v)->toArray();

        if (empty($selectedCatIds) && !empty($product->category_id)) {
            $selectedCatIds[] = (int)$product->category_id;
        }
        if (empty($selectedSubCatIds) && !empty($product->sub_category_id)) {
            $selectedSubCatIds[] = (int)$product->sub_category_id;
        }

        $selectedBrandIds = DB::table('product_brand')->where('product_id', $id)->pluck('brand_id')->map(fn($v) => (int)$v)->toArray();
        if (empty($selectedBrandIds) && !empty($product->brand_id)) {
            $selectedBrandIds[] = (int)$product->brand_id;
        }

        $productTags = DB::table('tags')
            ->join('product_tag', 'tags.id', '=', 'product_tag.tag_id')
            ->where('product_tag.product_id', $id)
            ->pluck('tags.name')
            ->toArray();

        $downloadFiles = DB::table('product_downloads')->where('product_id', $id)->get();
        $licenses = DB::table('product_licenses')->where('product_id', $id)->get();

        return view('admin.product.edit', compact(
            'product', 'galleries', 'variants', 'categories', 'subCategories', 'brands', 'selectedCatIds', 'selectedSubCatIds', 'selectedBrandIds', 'productTags', 'downloadFiles', 'licenses'
        ));
    }

    public function update(Request $request, $id)
    {
        $this->ensurePivotTables();

        $product = DB::table('products')->where('id', $id)->first();
        if (!$product) {
            return redirect()->route('admin.product.index')->with('error', 'Product not found.');
        }

        $request->validate([
            'title'            => 'required|string|max:255',
            'regular_price'    => 'required|numeric',
            'stock'            => 'nullable|integer',
            'thumbnail'        => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,svg|max:5120',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,svg|max:5120',
        ]);

        DB::beginTransaction();

        try {
            $thumbnailPath = $product->thumbnail;

            if ($request->input('remove_thumbnail') == '1') {
                $this->deleteProductImage($thumbnailPath);
                $thumbnailPath = null;
            }

            if ($request->hasFile('thumbnail')) {
                $this->deleteProductImage($thumbnailPath);
                $thumbnailPath = $this->uploadProductImage($request->file('thumbnail'), 'products/thumbnails');
            } elseif ($request->filled('thumbnail_media')) {
                $normalizedThumbMedia = $this->normalizeMediaInputPath($request->input('thumbnail_media'));
                if (!empty($normalizedThumbMedia)) {
                    if ($thumbnailPath && $thumbnailPath !== $normalizedThumbMedia) {
                        $this->deleteProductImage($thumbnailPath);
                    }
                    $thumbnailPath = $normalizedThumbMedia;
                }
            }

            $barcode = $request->barcode ?: (!empty($product->barcode) ? $product->barcode : rand(100000000000, 999999999999));

            $metaDescription = $request->filled('meta_description') 
                ? $request->meta_description 
                : Str::limit(strip_tags($request->short_description ?? $request->description ?? ''), 160);

            $selectedParentIds = array_values(array_unique(array_filter(array_map('intval', (array) $request->input('category_ids', [])))));
            $selectedChildIds  = array_values(array_unique(array_filter(array_map('intval', (array) $request->input('sub_category_ids', [])))));

            $primaryCatId = !empty($selectedParentIds) ? $selectedParentIds[0] : ($request->category_id ?: null);
            $primarySubCatId = !empty($selectedChildIds) ? $selectedChildIds[0] : ($request->sub_category_id ?: null);

            $selectedBrandIds = array_values(array_unique(array_filter(array_map('intval', (array) $request->input('brand_ids', [])))));
            $primaryBrandId = !empty($selectedBrandIds) ? $selectedBrandIds[0] : ($request->brand_id ?: null);

            $isVirtual = $request->has('is_virtual') ? 1 : ($request->product_type === 'virtual' ? 1 : 0);
            $isDownloadable = $request->has('is_downloadable') ? 1 : ($request->product_type === 'downloadable' ? 1 : 0);

            $rawPurchaseCost = $request->filled('purchase_cost') ? $request->purchase_cost : ($request->filled('purchase_price') ? $request->purchase_price : null);

            $productUpdateData = [
                'category_id'              => $primaryCatId,
                'sub_category_id'          => $primarySubCatId,
                'brand_id'                 => $primaryBrandId,
                'title'                    => $request->title,
                'product_code'             => $request->product_code ?? $product->product_code,
                'barcode'                  => $barcode,
                'short_description'        => $request->short_description,
                'description'              => $request->description,
                'product_type'             => $request->product_type ?? 'physical',
                'product_struct'           => $request->product_struct ?? 'simple',
                'is_virtual'               => $isVirtual,
                'is_downloadable'          => $isDownloadable,
                'download_limit'           => $request->filled('download_limit') ? (int)$request->download_limit : null,
                'download_expiry'          => $request->filled('download_expiry') ? (int)$request->download_expiry : null,
                'access_instructions'      => $request->access_instructions ?: null,
                'regular_price'            => $request->regular_price,
                'sale_price'               => $request->sale_price ?: null,
                'wholesale_price'          => $request->wholesale_price ?: null,
                'purchase_cost'            => !is_null($rawPurchaseCost) ? (float)$rawPurchaseCost : 0.00,
                'purchase_price'           => !is_null($rawPurchaseCost) ? (float)$rawPurchaseCost : null,
                'discount_percentage'      => $request->discount_percentage ?: null,
                'stock'                    => ($request->product_struct === 'variable') ? (int)$product->stock : (int)($request->stock ?? 0),
                'allow_out_of_stock_order' => $request->has('allow_out_of_stock_order') ? 1 : 0,
                'thumbnail'                => $thumbnailPath,
                'is_featured'              => $request->has('is_featured') ? 1 : 0,
                'weight'                   => $isVirtual ? null : ($request->weight ?: null),
                'height'                   => $isVirtual ? null : ($request->height ?: null),
                'width'                    => $isVirtual ? null : ($request->width ?: null),
                'length'                   => $isVirtual ? null : ($request->length ?: null),
                'video_url'                => $request->video_url ?: null,
                'focus_keyphrase'          => $request->focus_keyphrase ?: null,
                'meta_description'         => $metaDescription,
                'canonical_url'            => $request->canonical_url ?: null,
                'updated_at'               => now(),
            ];

            if (Schema::hasColumn('products', 'seo_title')) {
                $productUpdateData['seo_title'] = $request->seo_title ?? $request->title;
            }

            if (Schema::hasColumn('products', 'sku')) {
                $productUpdateData['sku'] = $request->sku ?? $product->sku;
            }

            DB::table('products')->where('id', $id)->update($productUpdateData);

            // ক্যাটাগরি ও ব্র্যান্ড পিভট আপডেট
            DB::table('product_category')->where('product_id', $id)->delete();
            foreach ($selectedParentIds as $pId) {
                DB::table('product_category')->insert([
                    'product_id'  => $id,
                    'category_id' => $pId,
                    'type'        => 'parent',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
            foreach ($selectedChildIds as $cId) {
                DB::table('product_category')->insert([
                    'product_id'  => $id,
                    'category_id' => $cId,
                    'type'        => 'child',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            DB::table('product_brand')->where('product_id', $id)->delete();
            foreach ($selectedBrandIds as $bId) {
                DB::table('product_brand')->insert([
                    'product_id' => $id,
                    'brand_id'   => $bId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $tagsInput = (array) $request->input('tags', []);
            $this->syncProductTags($id, $tagsInput);

            if ($request->filled('removed_download_ids')) {
                $removedDownIds = array_filter(explode(',', $request->removed_download_ids));
                if (!empty($removedDownIds)) {
                    $filesToDelete = DB::table('product_downloads')->whereIn('id', $removedDownIds)->get();
                    foreach ($filesToDelete as $fDel) {
                        if ($fDel->file_path && file_exists(storage_path('app/' . $fDel->file_path))) {
                            @unlink(storage_path('app/' . $fDel->file_path));
                        }
                    }
                    DB::table('product_downloads')->whereIn('id', $removedDownIds)->delete();
                }
            }

            if ($isDownloadable) {
                $this->handleDownloadableFiles($request, $id);
            }

            if ($isVirtual) {
                $this->handleLicenseKeys($request, $id);
            }

            if ($request->filled('removed_license_ids')) {
                $removedLicIds = array_filter(explode(',', $request->removed_license_ids));
                if (!empty($removedLicIds)) {
                    DB::table('product_licenses')->whereIn('id', $removedLicIds)->delete();
                }
            }

            if ($request->has('removed_galleries')) {
                $removedIds = array_filter(explode(',', $request->removed_galleries));
                if (!empty($removedIds)) {
                    $removedImages = DB::table('product_galleries')->whereIn('id', $removedIds)->get();
                    foreach ($removedImages as $rImg) {
                        $this->deleteProductImage($rImg->image_path);
                    }
                    DB::table('product_galleries')->whereIn('id', $removedIds)->delete();
                }
            }

            if ($request->filled('gallery_media') && is_array($request->input('gallery_media'))) {
                foreach ($request->input('gallery_media') as $mediaPath) {
                    $cleanMediaPath = $this->normalizeMediaInputPath($mediaPath);
                    if (!empty($cleanMediaPath)) {
                        DB::table('product_galleries')->insert([
                            'product_id' => $id,
                            'image_path' => $cleanMediaPath,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }

            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $gFile) {
                    $gImagePath = $this->uploadProductImage($gFile, 'products/gallery');

                    DB::table('product_galleries')->insert([
                        'product_id' => $id,
                        'image_path' => $gImagePath,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            // =========================================================================
            // SMART VARIANT UPDATE (Preserves existing variant IDs & order history)
            // =========================================================================
            if ($request->product_struct === 'variable') {
                $keptVariantIds = [];

                if ($request->has('variants') && is_array($request->variants)) {
                    foreach ($request->variants as $idx => $variant) {
                        if (!is_array($variant)) continue;

                        $varImgPath = $this->resolveVariantImageFromRequest($request, $idx, $variant, true);
                        $hasColorOrSize = !empty($variant['color']) || !empty($variant['size']);

                        if ($hasColorOrSize || !empty($varImgPath)) {
                            $vStock = isset($variant['stock']) ? max(0, (int)$variant['stock']) : 0;
                            $vCost = !empty($variant['purchase_cost']) 
                                ? (float)$variant['purchase_cost'] 
                                : (!empty($variant['purchase_price']) ? (float)$variant['purchase_price'] : (!empty($rawPurchaseCost) ? (float)$rawPurchaseCost : 0.00));

                            $varData = [
                                'product_id'     => $id,
                                'color'          => $variant['color'] ?? null,
                                'size'           => $variant['size'] ?? null,
                                'product_code'   => $variant['sku'] ?? ($variant['product_code'] ?? null),
                                'barcode'        => $variant['barcode'] ?? null,
                                'regular_price'  => !empty($variant['regular_price']) ? (float)$variant['regular_price'] : (float)$request->regular_price,
                                'sale_price'     => !empty($variant['sale_price']) ? (float)$variant['sale_price'] : null,
                                'stock'          => $vStock,
                                'alert_quantity' => isset($variant['alert_quantity']) ? (int)$variant['alert_quantity'] : 5,
                                'image'          => $varImgPath,
                                'purchase_cost'  => $vCost,
                                'purchase_price' => $vCost,
                                'updated_at'     => now(),
                            ];

                            if (Schema::hasColumn('product_variants', 'sku')) {
                                $varData['sku'] = $variant['sku'] ?? ($variant['product_code'] ?? null);
                            }

                            // যদি ফর্ম থেকে বিদ্যমান ভ্যারিয়েন্ট আইডি আসে, তবে পূর্বের রো-টি আপডেট হবে (নতুন আইডি তৈরি হবে না)
                            $existingVarId = !empty($variant['id']) ? (int)$variant['id'] : null;
                            if ($existingVarId && DB::table('product_variants')->where('id', $existingVarId)->where('product_id', $id)->exists()) {
                                DB::table('product_variants')->where('id', $existingVarId)->update($varData);
                                $keptVariantIds[] = $existingVarId;
                            } else {
                                $varData['created_at'] = now();
                                $newVarId = DB::table('product_variants')->insertGetId($varData);
                                $keptVariantIds[] = $newVarId;
                            }
                        }
                    }
                }

                // শুধুমাত্র যে ভ্যারিয়েন্টগুলো ফর্ম থেকে ডিলিট করা হয়েছে সেগুলোকে রিমুভ করা
                if (!empty($keptVariantIds)) {
                    DB::table('product_variants')
                        ->where('product_id', $id)
                        ->whereNotIn('id', $keptVariantIds)
                        ->delete();
                } else {
                    DB::table('product_variants')->where('product_id', $id)->delete();
                }

                // প্যারেন্ট প্রোডাক্টের স্টক ভ্যারিয়েন্টগুলোর যোগফলের সমান করা
                InventoryService::syncParentProductStock($id);
            } else {
                DB::table('product_variants')->where('product_id', $id)->delete();
            }

            DB::commit();
            return redirect()->route('admin.product.index')->with('success', 'Product updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating product: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $product = DB::table('products')->where('id', $id)->first();
        if ($product) {
            $this->deleteProductImage($product->thumbnail);

            $galleries = DB::table('product_galleries')->where('product_id', $id)->get();
            foreach ($galleries as $g) {
                $this->deleteProductImage($g->image_path);
            }

            $downFiles = DB::table('product_downloads')->where('product_id', $id)->get();
            foreach ($downFiles as $df) {
                if ($df->file_path && file_exists(storage_path('app/' . $df->file_path))) {
                    @unlink(storage_path('app/' . $df->file_path));
                }
            }

            DB::table('products')->where('id', $id)->delete();
            if (Schema::hasTable('product_variants')) {
                DB::table('product_variants')->where('product_id', $id)->delete();
            }
            if (Schema::hasTable('product_category')) {
                DB::table('product_category')->where('product_id', $id)->delete();
            }
            if (Schema::hasTable('product_brand')) {
                DB::table('product_brand')->where('product_id', $id)->delete();
            }
            if (Schema::hasTable('product_tag')) {
                DB::table('product_tag')->where('product_id', $id)->delete();
            }
            if (Schema::hasTable('product_downloads')) {
                DB::table('product_downloads')->where('product_id', $id)->delete();
            }
            if (Schema::hasTable('product_licenses')) {
                DB::table('product_licenses')->where('product_id', $id)->delete();
            }
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Product deleted successfully.']);
        }

        return redirect()->route('admin.product.index')->with('success', 'Product deleted successfully.');
    }

    public function toggleFeatured($id)
    {
        $product = DB::table('products')->where('id', $id)->first();
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found.'], 404);
        }

        $newStatus = $product->is_featured ? 0 : 1;
        DB::table('products')->where('id', $id)->update([
            'is_featured' => $newStatus,
            'updated_at'  => now(),
        ]);

        return response()->json([
            'success'     => true,
            'is_featured' => $newStatus,
            'message'     => 'Featured status updated.'
        ]);
    }

    public function bulkAction(Request $request)
    {
        $action = $request->action;
        $ids = (array) $request->ids;

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No items selected.'], 422);
        }

        if ($action === 'delete') {
            $products = DB::table('products')->whereIn('id', $ids)->get();
            foreach ($products as $p) {
                $this->deleteProductImage($p->thumbnail);
                $galleries = DB::table('product_galleries')->where('product_id', $p->id)->get();
                foreach ($galleries as $g) {
                    $this->deleteProductImage($g->image_path);
                }
            }
            DB::table('products')->whereIn('id', $ids)->delete();
            if (Schema::hasTable('product_variants')) {
                DB::table('product_variants')->whereIn('product_id', $ids)->delete();
            }
            if (Schema::hasTable('product_category')) {
                DB::table('product_category')->whereIn('product_id', $ids)->delete();
            }
            if (Schema::hasTable('product_brand')) {
                DB::table('product_brand')->whereIn('product_id', $ids)->delete();
            }
            if (Schema::hasTable('product_tag')) {
                DB::table('product_tag')->whereIn('product_id', $ids)->delete();
            }
            return response()->json(['success' => true, 'message' => count($ids) . ' products deleted successfully.']);
        }

        if ($action === 'mark_featured') {
            DB::table('products')->whereIn('id', $ids)->update(['is_featured' => 1, 'updated_at' => now()]);
            return response()->json(['success' => true, 'message' => 'Selected products marked as Featured.']);
        }

        if ($action === 'unmark_featured') {
            DB::table('products')->whereIn('id', $ids)->update(['is_featured' => 0, 'updated_at' => now()]);
            return response()->json(['success' => true, 'message' => 'Selected products unmarked from Featured.']);
        }

        if ($action === 'out_of_stock') {
            DB::table('products')->whereIn('id', $ids)->update(['stock' => 0, 'updated_at' => now()]);
            if (Schema::hasTable('product_variants')) {
                DB::table('product_variants')->whereIn('product_id', $ids)->update(['stock' => 0, 'updated_at' => now()]);
            }
            return response()->json(['success' => true, 'message' => 'Selected products marked as Out of Stock.']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action.'], 422);
    }

    public function getMostUsedTagsAjax()
    {
        if (!Schema::hasTable('tags') || !Schema::hasTable('product_tag')) {
            return response()->json([]);
        }

        $tags = DB::table('tags')
            ->leftJoin('product_tag', 'tags.id', '=', 'product_tag.tag_id')
            ->select('tags.name', DB::raw('COUNT(product_tag.id) as count'))
            ->groupBy('tags.id', 'tags.name')
            ->orderByDesc('count')
            ->take(30)
            ->get();

        return response()->json($tags);
    }

    public function searchTagsAjax(Request $request)
    {
        $term = trim($request->get('term', ''));
        if (empty($term) || !Schema::hasTable('tags')) {
            return response()->json([]);
        }

        $tags = DB::table('tags')
            ->where('name', 'like', "%{$term}%")
            ->pluck('name');

        return response()->json($tags);
    }
}