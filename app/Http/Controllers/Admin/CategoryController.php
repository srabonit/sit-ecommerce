<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImagePathService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CategoryController extends Controller
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
     * Upload Category Image across all public roots & auto-register in Media Library
     */
    protected function uploadCategoryImage($file): string
    {
        $originalName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $cleanBase = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'category';
        $filename = time() . '_' . uniqid() . '_' . $cleanBase . '.' . $ext;
        $relativeDir = 'categories';
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
                    $mediaInsert['source_module'] = 'category';
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
     * Universal Delete Helper for Category Image (Safe against deleting shared media library files)
     */
    protected function deleteCategoryImage($relativePath)
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
        if (!Schema::hasTable('product_category')) {
            Schema::create('product_category', function ($table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('category_id')->index();
                $table->string('type', 20)->default('parent')->index();
                $table->timestamps();
            });
        }

        // প্যারেন্ট ক্যাটাগরিতে নিজের সরাসরি প্রোডাক্ট + এর আন্ডারে থাকা সব সাব-ক্যাটাগরির প্রোডাক্ট ইউনিক কাউন্ট হবে
        $parentCountSubquery = '(
            SELECT COUNT(DISTINCT products.id) 
            FROM products 
            LEFT JOIN product_category ON product_category.product_id = products.id 
            WHERE products.category_id = categories.id 
               OR products.sub_category_id IN (SELECT id FROM sub_categories WHERE category_id = categories.id)
               OR (product_category.category_id = categories.id AND product_category.type = "parent")
               OR (product_category.category_id IN (SELECT id FROM sub_categories WHERE category_id = categories.id) AND product_category.type = "child")
        ) as products_count';

        // চাইল্ড ক্যাটাগরিতে শুধু তার নিজের প্রোডাক্ট কাউন্ট হবে
        $childCountSubquery = '(
            SELECT COUNT(DISTINCT products.id) 
            FROM products 
            LEFT JOIN product_category ON product_category.product_id = products.id 
            WHERE products.sub_category_id = sub_categories.id 
               OR (product_category.category_id = sub_categories.id AND product_category.type = "child")
        ) as products_count';

        $parentQuery = DB::table('categories')
            ->select(
                'id',
                DB::raw('NULL as parent_id'),
                'name',
                'slug',
                'icon',
                'image',
                'description',
                'is_active',
                DB::raw("'parent' as type"),
                DB::raw("NULL as parent_name"),
                DB::raw($parentCountSubquery)
            );

        $childQuery = DB::table('sub_categories')
            ->join('categories', 'sub_categories.category_id', '=', 'categories.id')
            ->select(
                'sub_categories.id',
                'sub_categories.category_id as parent_id',
                'sub_categories.name',
                'sub_categories.slug',
                'sub_categories.icon',
                DB::raw("NULL as image"),
                'sub_categories.description',
                'sub_categories.is_active',
                DB::raw("'child' as type"),
                'categories.name as parent_name',
                DB::raw($childCountSubquery)
            );

        if ($request->filled('search')) {
            $search = trim($request->search);
            $parentQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%");
            });
            $childQuery->where(function ($q) use ($search) {
                $q->where('sub_categories.name', 'like', "%{$search}%")->orWhere('sub_categories.slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('parent_filter')) {
            if ($request->parent_filter === 'root') {
                $unionQuery = $parentQuery;
            } elseif ($request->parent_filter === 'child') {
                $unionQuery = $childQuery;
            } else {
                $catId = (int) $request->parent_filter;
                $parentQuery->where('id', $catId);
                $childQuery->where('category_id', $catId);
                $unionQuery = $parentQuery->unionAll($childQuery);
            }
        } else {
            $unionQuery = $parentQuery->unionAll($childQuery);
        }

        $categories = DB::query()->fromSub($unionQuery, 'all_cats')
            ->orderByRaw('COALESCE(parent_id, id), parent_id IS NOT NULL, id ASC')
            ->paginate(20)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'table_html'      => view('admin.category.partials.table_rows', compact('categories'))->render(),
                'pagination_html' => $categories->links('pagination::bootstrap-5')->render(),
                'total_count'     => $categories->total(),
                'entries_text'    => "Showing {$categories->firstItem()} to {$categories->lastItem()} of {$categories->total()} entries",
            ]);
        }

        $allParentCategories = DB::table('categories')->orderBy('name', 'asc')->get();

        return view('admin.category.index', compact('categories', 'allParentCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,svg|max:5120',
            'image_media' => 'nullable|string',
            'icon'        => 'nullable|string|max:100',
        ]);

        $slug = !empty($request->slug) ? Str::slug($request->slug) : Str::slug($request->name);

        if (!empty($request->parent_id)) {
            $originalSlug = $slug;
            $count = 1;
            while (DB::table('sub_categories')->where('slug', $slug)->exists()) {
                $slug = "{$originalSlug}-" . $count++;
            }

            DB::table('sub_categories')->insert([
                'category_id' => $request->parent_id,
                'name'        => $request->name,
                'slug'        => $slug,
                'icon'        => $request->icon,
                'description' => $request->description,
                'is_active'   => $request->has('is_active') ? 1 : 0,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Sub Category created successfully.']);
        }

        $originalSlug = $slug;
        $count = 1;
        while (DB::table('categories')->where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-" . $count++;
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->uploadCategoryImage($request->file('image'));
        } elseif ($request->filled('image_media')) {
            $imagePath = $this->normalizeMediaInputPath($request->input('image_media'));
        }

        DB::table('categories')->insert([
            'name'        => $request->name,
            'slug'        => $slug,
            'icon'        => $request->icon,
            'image'       => $imagePath,
            'description' => $request->description,
            'is_active'   => $request->has('is_active') ? 1 : 0,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Parent Category created successfully.']);
    }

    public function edit(Request $request, $id)
    {
        $type = $request->get('type', 'parent');

        if ($type === 'child') {
            $category = DB::table('sub_categories')->where('id', $id)->first();
            if ($category) {
                $category->parent_id = $category->category_id;
                $category->type = 'child';
                $category->image = null;
                $category->image_url = null;
            }
        } else {
            $category = DB::table('categories')->where('id', $id)->first();
            if ($category) {
                $category->parent_id = null;
                $category->type = 'parent';
                $category->image_url = !empty($category->image)
                    ? ImagePathService::resolveUrl($category->image, '')
                    : null;
            }
        }

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found.'], 404);
        }

        $availableParents = DB::table('categories')
            ->when($type === 'parent', function($q) use ($id) {
                $q->where('id', '!=', $id);
            })
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $category,
            'parents' => $availableParents
        ]);
    }

    public function update(Request $request, $id)
    {
        $currentType = $request->input('category_type', 'parent');
        $newParentId = !empty($request->parent_id) ? (int)$request->parent_id : null;
        $slug = !empty($request->slug) ? Str::slug($request->slug) : Str::slug($request->name);

        if ($currentType === 'parent' && !empty($newParentId)) {
            $parentCat = DB::table('categories')->where('id', $id)->first();
            if (!$parentCat) return response()->json(['success' => false, 'message' => 'Original category not found.'], 404);

            $originalSlug = $slug;
            $count = 1;
            while (DB::table('sub_categories')->where('slug', $slug)->exists()) {
                $slug = "{$originalSlug}-" . $count++;
            }

            $newSubCatId = DB::table('sub_categories')->insertGetId([
                'category_id' => $newParentId,
                'name'        => $request->name,
                'slug'        => $slug,
                'icon'        => $request->icon ?? $parentCat->icon,
                'description' => $request->description ?? $parentCat->description,
                'is_active'   => $request->has('is_active') ? 1 : 0,
                'created_at'  => $parentCat->created_at ?? now(),
                'updated_at'  => now(),
            ]);

            DB::table('products')->where('category_id', $id)->update([
                'category_id'     => $newParentId,
                'sub_category_id' => $newSubCatId,
            ]);

            DB::table('sub_categories')->where('category_id', $id)->update([
                'category_id' => $newParentId
            ]);

            if ($parentCat->image) {
                $this->deleteCategoryImage($parentCat->image);
            }
            DB::table('categories')->where('id', $id)->delete();

            return response()->json(['success' => true, 'message' => 'Category shifted to Sub-Category under new parent successfully.']);
        }

        if ($currentType === 'child' && empty($newParentId)) {
            $childCat = DB::table('sub_categories')->where('id', $id)->first();
            if (!$childCat) return response()->json(['success' => false, 'message' => 'Original sub-category not found.'], 404);

            $originalSlug = $slug;
            $count = 1;
            while (DB::table('categories')->where('slug', $slug)->exists()) {
                $slug = "{$originalSlug}-" . $count++;
            }

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $this->uploadCategoryImage($request->file('image'));
            } elseif ($request->filled('image_media')) {
                $imagePath = $this->normalizeMediaInputPath($request->input('image_media'));
            }

            $newParentCatId = DB::table('categories')->insertGetId([
                'name'        => $request->name,
                'slug'        => $slug,
                'icon'        => $request->icon ?? $childCat->icon,
                'image'       => $imagePath,
                'description' => $request->description ?? $childCat->description,
                'is_active'   => $request->has('is_active') ? 1 : 0,
                'created_at'  => $childCat->created_at ?? now(),
                'updated_at'  => now(),
            ]);

            DB::table('products')->where('sub_category_id', $id)->update([
                'category_id'     => $newParentCatId,
                'sub_category_id' => null,
            ]);

            DB::table('sub_categories')->where('id', $id)->delete();

            return response()->json(['success' => true, 'message' => 'Sub-Category promoted to Parent Category successfully.']);
        }

        if ($currentType === 'child') {
            $childCat = DB::table('sub_categories')->where('id', $id)->first();
            if (!$childCat) return response()->json(['success' => false, 'message' => 'Not found.'], 404);

            DB::table('sub_categories')->where('id', $id)->update([
                'category_id' => $newParentId ?? $childCat->category_id,
                'name'        => $request->name,
                'slug'        => $slug,
                'icon'        => $request->icon,
                'description' => $request->description,
                'is_active'   => $request->has('is_active') ? 1 : 0,
                'updated_at'  => now(),
            ]);

            if ($newParentId) {
                DB::table('products')->where('sub_category_id', $id)->update([
                    'category_id' => $newParentId
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Sub Category updated successfully.']);
        }

        $parentCat = DB::table('categories')->where('id', $id)->first();
        if (!$parentCat) return response()->json(['success' => false, 'message' => 'Not found.'], 404);

        $imagePath = $parentCat->image;

        if ($request->input('remove_image') == '1') {
            if ($imagePath) {
                $this->deleteCategoryImage($imagePath);
            }
            $imagePath = null;
        }

        if ($request->hasFile('image')) {
            if ($imagePath) {
                $this->deleteCategoryImage($imagePath);
            }
            $imagePath = $this->uploadCategoryImage($request->file('image'));
        } elseif ($request->filled('image_media')) {
            $normalizedMedia = $this->normalizeMediaInputPath($request->input('image_media'));
            if (!empty($normalizedMedia)) {
                if ($imagePath && $imagePath !== $normalizedMedia) {
                    $this->deleteCategoryImage($imagePath);
                }
                $imagePath = $normalizedMedia;
            }
        }

        DB::table('categories')->where('id', $id)->update([
            'name'        => $request->name,
            'slug'        => $slug,
            'icon'        => $request->icon,
            'image'       => $imagePath,
            'description' => $request->description,
            'is_active'   => $request->has('is_active') ? 1 : 0,
            'updated_at'  => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Parent Category updated successfully.']);
    }

    public function destroy(Request $request, $id)
    {
        $type = $request->get('type', 'parent');

        if ($type === 'child') {
            DB::table('products')->where('sub_category_id', $id)->update(['sub_category_id' => null]);
            DB::table('sub_categories')->where('id', $id)->delete();
            if (Schema::hasTable('product_category')) {
                DB::table('product_category')->where('category_id', $id)->where('type', 'child')->delete();
            }
        } else {
            $category = DB::table('categories')->where('id', $id)->first();
            if ($category && $category->image) {
                $this->deleteCategoryImage($category->image);
            }
            $subIds = DB::table('sub_categories')->where('category_id', $id)->pluck('id');
            DB::table('products')->whereIn('sub_category_id', $subIds)->update(['sub_category_id' => null]);
            DB::table('products')->where('category_id', $id)->update(['category_id' => null]);
            
            DB::table('sub_categories')->where('category_id', $id)->delete();
            DB::table('categories')->where('id', $id)->delete();

            if (Schema::hasTable('product_category')) {
                DB::table('product_category')->where('category_id', $id)->where('type', 'parent')->delete();
                DB::table('product_category')->whereIn('category_id', $subIds)->where('type', 'child')->delete();
            }
        }

        return response()->json(['success' => true, 'message' => 'Category deleted successfully.']);
    }

    public function toggleStatus(Request $request, $id)
    {
        $type = $request->get('type', 'parent');
        $table = ($type === 'child') ? 'sub_categories' : 'categories';

        $cat = DB::table($table)->where('id', $id)->first();
        if (!$cat) return response()->json(['success' => false], 404);

        $newStatus = $cat->is_active ? 0 : 1;
        DB::table($table)->where('id', $id)->update(['is_active' => $newStatus, 'updated_at' => now()]);

        return response()->json(['success' => true, 'status' => $newStatus, 'message' => 'Status updated.']);
    }

    public function quickStoreAjax(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'parent_id' => 'nullable|integer',
        ]);

        $name = trim($request->name);
        $slug = Str::slug($name);
        $parentId = $request->filled('parent_id') && (int)$request->parent_id > 0 ? (int)$request->parent_id : null;

        if ($parentId) {
            $originalSlug = $slug;
            $count = 1;
            while (DB::table('sub_categories')->where('slug', $slug)->exists()) {
                $slug = "{$originalSlug}-" . $count++;
            }

            $id = DB::table('sub_categories')->insertGetId([
                'category_id' => $parentId,
                'name'        => $name,
                'slug'        => $slug,
                'is_active'   => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            return response()->json([
                'success'   => true,
                'id'        => $id,
                'name'      => $name,
                'type'      => 'child',
                'parent_id' => $parentId,
                'message'   => 'Sub-category created successfully.'
            ]);
        }

        $originalSlug = $slug;
        $count = 1;
        while (DB::table('categories')->where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-" . $count++;
        }

        $id = DB::table('categories')->insertGetId([
            'name'        => $name,
            'slug'        => $slug,
            'is_active'   => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json([
            'success'   => true,
            'id'        => $id,
            'name'      => $name,
            'type'      => 'parent',
            'parent_id' => null,
            'message'   => 'Parent category created successfully.'
        ]);
    }
}