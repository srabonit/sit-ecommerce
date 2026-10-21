<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImagePathService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BrandController extends Controller
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
     * Upload Brand Logo across all public roots & auto-register in Media Library
     */
    protected function uploadBrandLogo($file): string
    {
        $originalName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
        $cleanBase = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'brand';
        $filename = time() . '_' . uniqid() . '_' . $cleanBase . '.' . $ext;
        $relativeDir = 'brands';
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
                $mimeType = @mime_content_type($primaryFullPath) ?: 'image/png';
                $fileSize = @filesize($primaryFullPath) ?: 0;
                $imgInfo  = @getimagesize($primaryFullPath);
                $dims     = $imgInfo ? ($imgInfo[0] . ' × ' . $imgInfo[1]) : null;
                $title    = ucwords(str_replace(['-', '_'], ' ', pathinfo($originalName, PATHINFO_FILENAME)));

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
                    $mediaInsert['source_module'] = 'brand';
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
     * Universal Delete Helper for Brand Logo (Safe against deleting shared media library files)
     */
    protected function deleteBrandLogo($relativePath)
    {
        if (empty($relativePath) || filter_var($relativePath, FILTER_VALIDATE_URL)) return;

        $clean = ltrim($relativePath, '/');

        // Global Media Library থেকে সিলেক্ট করা ফাইল ডিস্ক থেকে কোনোভাবেই ডিলিট করা যাবে না
        if (str_starts_with($clean, 'uploads/media/') || str_starts_with($clean, 'media/')) {
            return;
        }

        // মিডিয়া লাইব্রেরি টেবিলে এই ফাইলটি রেজিস্টার করা থাকলে ডিস্ক ফাইল সুরক্ষিত থাকবে
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

    public function index(Request $request)
    {
        $hasPivot = Schema::hasTable('product_brand');

        $countSubquery = $hasPivot
            ? '(SELECT COUNT(DISTINCT products.id) FROM products LEFT JOIN product_brand ON product_brand.product_id = products.id WHERE products.brand_id = brands.id OR product_brand.brand_id = brands.id) as products_count'
            : '(SELECT COUNT(*) FROM products WHERE products.brand_id = brands.id) as products_count';

        $query = DB::table('brands')
            ->select(
                'brands.*',
                DB::raw($countSubquery)
            );

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status == '1' ? 1 : 0);
        }

        $brands = $query->latest('id')
                        ->paginate(20)
                        ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'table_html'      => view('admin.brand.partials.table_rows', compact('brands'))->render(),
                'pagination_html' => $brands->links('pagination::bootstrap-5')->render(),
                'total_count'     => $brands->total(),
                'entries_text'    => "Showing {$brands->firstItem()} to {$brands->lastItem()} of {$brands->total()} entries",
            ]);
        }

        return view('admin.brand.index', compact('brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'slug'       => 'nullable|string|max:255|unique:brands,slug',
            'logo'       => 'nullable|image|mimes:jpeg,png,jpg,webp,svg,gif|max:5120',
            'logo_media' => 'nullable|string',
        ]);

        $slug = !empty($request->slug) ? Str::slug($request->slug) : Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;
        while (DB::table('brands')->where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-" . $count++;
        }

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $this->uploadBrandLogo($request->file('logo'));
        } elseif ($request->filled('logo_media')) {
            $logoPath = $this->normalizeMediaInputPath($request->input('logo_media'));
        }

        DB::table('brands')->insert([
            'name'        => $request->name,
            'slug'        => $slug,
            'logo'        => $logoPath,
            'description' => $request->description,
            'is_active'   => $request->has('is_active') ? 1 : 0,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Brand created successfully.']);
    }

    public function edit($id)
    {
        $brand = DB::table('brands')->where('id', $id)->first();
        if (!$brand) {
            return response()->json(['success' => false, 'message' => 'Brand not found.'], 404);
        }

        $brand->logo_url = !empty($brand->logo)
            ? ImagePathService::resolveUrl($brand->logo, '')
            : null;

        return response()->json([
            'success' => true,
            'data'    => $brand
        ]);
    }

    public function update(Request $request, $id)
    {
        $brand = DB::table('brands')->where('id', $id)->first();
        if (!$brand) {
            return response()->json(['success' => false, 'message' => 'Brand not found.'], 404);
        }

        $request->validate([
            'name'       => 'required|string|max:255',
            'slug'       => 'nullable|string|max:255|unique:brands,slug,' . $id,
            'logo'       => 'nullable|image|mimes:jpeg,png,jpg,webp,svg,gif|max:5120',
            'logo_media' => 'nullable|string',
        ]);

        $slug = !empty($request->slug) ? Str::slug($request->slug) : Str::slug($request->name);

        $logoPath = $brand->logo;

        if ($request->input('remove_logo') == '1') {
            if ($logoPath) {
                $this->deleteBrandLogo($logoPath);
            }
            $logoPath = null;
        }

        if ($request->hasFile('logo')) {
            if ($logoPath) {
                $this->deleteBrandLogo($logoPath);
            }
            $logoPath = $this->uploadBrandLogo($request->file('logo'));
        } elseif ($request->filled('logo_media')) {
            $normalizedMedia = $this->normalizeMediaInputPath($request->input('logo_media'));
            if (!empty($normalizedMedia)) {
                if ($logoPath && $logoPath !== $normalizedMedia) {
                    $this->deleteBrandLogo($logoPath);
                }
                $logoPath = $normalizedMedia;
            }
        }

        DB::table('brands')->where('id', $id)->update([
            'name'        => $request->name,
            'slug'        => $slug,
            'logo'        => $logoPath,
            'description' => $request->description,
            'is_active'   => $request->has('is_active') ? 1 : 0,
            'updated_at'  => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Brand updated successfully.']);
    }

    public function destroy($id)
    {
        $brand = DB::table('brands')->where('id', $id)->first();
        if ($brand) {
            if ($brand->logo) {
                $this->deleteBrandLogo($brand->logo);
            }
            DB::table('products')->where('brand_id', $id)->update(['brand_id' => null]);
            DB::table('brands')->where('id', $id)->delete();
            if (Schema::hasTable('product_brand')) {
                DB::table('product_brand')->where('brand_id', $id)->delete();
            }
        }

        return response()->json(['success' => true, 'message' => 'Brand deleted successfully.']);
    }

    public function toggleStatus($id)
    {
        $brand = DB::table('brands')->where('id', $id)->first();
        if (!$brand) return response()->json(['success' => false], 404);

        $newStatus = $brand->is_active ? 0 : 1;
        DB::table('brands')->where('id', $id)->update(['is_active' => $newStatus, 'updated_at' => now()]);

        return response()->json(['success' => true, 'status' => $newStatus, 'message' => 'Brand status updated.']);
    }

    /**
     * Product Add/Edit পেজ থেকে AJAX দিয়ে পেজ রিলোড ছাড়াই নতুন ব্র্যান্ড তৈরির মেথড
     */
    public function quickStoreAjax(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $name = trim($request->name);
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;
        while (DB::table('brands')->where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-" . $count++;
        }

        $id = DB::table('brands')->insertGetId([
            'name'        => $name,
            'slug'        => $slug,
            'is_active'   => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'id'      => $id,
            'name'    => $name,
            'message' => 'Brand created successfully.'
        ]);
    }
}