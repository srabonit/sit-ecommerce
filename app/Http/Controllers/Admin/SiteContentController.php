<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImagePathService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SiteContentController extends Controller
{
    /**
     * Dynamic Public Web Root Detector (Localhost & cPanel Public_html Safe)
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
     * Universal Upload Helper for Content (Saves across all public roots & auto-registers in Media Library)
     */
    protected function saveContentFile($file, $subFolder): string
    {
        $originalName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $cleanBase = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'content';
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
                $sourceMod = str_contains($relativeDir, 'page') ? 'page' : 'blog';

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
     * Universal Delete Helper for Content (Safe against deleting shared media library files)
     */
    protected function deleteContentFile($relativePath)
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

    // =========================================================================
    // 1. PAGES MANAGEMENT
    // =========================================================================
    public function pagesIndex()
    {
        $this->ensurePageColumnsExist();
        $pages = DB::table('site_pages')->latest('id')->paginate(15);
        return view('admin.content.pages.index', compact('pages'));
    }

    public function pagesCreate()
    {
        $this->ensurePageColumnsExist();
        return view('admin.content.pages.create');
    }

    public function pagesStore(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug'  => 'required|string|unique:site_pages,slug',
        ]);

        $featuredImage = null;
        if ($request->hasFile('featured_image_file')) {
            $featuredImage = $this->saveContentFile($request->file('featured_image_file'), 'pages');
        } elseif ($request->filled('featured_image')) {
            $featuredImage = $this->normalizeMediaInputPath($request->input('featured_image'));
        }

        $this->ensurePageColumnsExist();

        $editorType = $request->input('editor_type', 'html_raw');
        if (!in_array($editorType, ['html_raw', 'blocks'])) {
            $editorType = 'html_raw';
        }

        DB::table('site_pages')->insert([
            'title'               => $validated['title'],
            'slug'                => Str::slug($validated['slug']),
            'content'             => $request->input('content', ''),
            'content_blocks'      => null,
            'editor_type'         => $editorType,
            'page_width'          => $request->input('page_width', 'contained-wide'),
            'title_font_size'     => $request->input('title_font_size', '32px'),
            'featured_image'      => (string) ($featuredImage ?? ''),
            'show_title'          => $request->has('show_title') ? 1 : 0,
            'show_featured_image' => $request->has('show_featured_image') ? 1 : 0,
            'show_sidebar_widget' => $request->has('show_sidebar_widget') ? 1 : 0,
            'custom_css'          => (string) ($request->input('custom_css') ?? ''),
            'custom_js'           => (string) ($request->input('custom_js') ?? ''),
            'seo_title'           => $request->input('seo_title') ?: $validated['title'],
            'seo_description'     => (string) ($request->input('seo_description') ?? ''),
            'seo_keywords'        => (string) ($request->input('seo_keywords') ?? ''),
            'focus_keyphrase'     => (string) ($request->input('focus_keyphrase') ?? ''),
            'seo_canonical_url'   => $request->input('seo_canonical_url'),
            'allow_indexing'      => $request->has('allow_indexing') ? 1 : 0,
            'status'              => $request->input('status', 'published'),
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        return redirect()->route('admin.content.pages.index')->with('success', 'Page published successfully.');
    }

    public function pagesEdit($id)
    {
        $this->ensurePageColumnsExist();
        $page = DB::table('site_pages')->where('id', $id)->first();
        abort_if(!$page, 404);
        return view('admin.content.pages.edit', compact('page'));
    }

    public function pagesUpdate(Request $request, $id)
    {
        $page = DB::table('site_pages')->where('id', $id)->first();
        abort_if(!$page, 404);

        $request->validate([
            'title' => 'required|string|max:255',
            'slug'  => 'required|string|unique:site_pages,slug,' . $id,
        ]);

        $featuredImage = $page->featured_image ?? '';

        if ($request->input('remove_featured_image') == '1') {
            if ($featuredImage) {
                $this->deleteContentFile($featuredImage);
            }
            $featuredImage = '';
        }

        if ($request->hasFile('featured_image_file')) {
            if ($featuredImage) {
                $this->deleteContentFile($featuredImage);
            }
            $featuredImage = $this->saveContentFile($request->file('featured_image_file'), 'pages');
        } elseif ($request->filled('featured_image')) {
            $normalizedMedia = $this->normalizeMediaInputPath($request->input('featured_image'));
            if (!empty($normalizedMedia)) {
                if ($featuredImage && $featuredImage !== $normalizedMedia) {
                    $this->deleteContentFile($featuredImage);
                }
                $featuredImage = $normalizedMedia;
            }
        }

        $this->ensurePageColumnsExist();

        $editorType = $request->input('editor_type', 'html_raw');
        if (!in_array($editorType, ['html_raw', 'blocks'])) {
            $editorType = 'html_raw';
        }

        DB::table('site_pages')->where('id', $id)->update([
            'title'               => $request->input('title'),
            'slug'                => Str::slug($request->input('slug')),
            'content'             => $request->input('content', ''),
            'editor_type'         => $editorType,
            'page_width'          => $request->input('page_width', 'contained-wide'),
            'title_font_size'     => $request->input('title_font_size', '32px'),
            'featured_image'      => (string) ($featuredImage ?? ''),
            'show_title'          => $request->has('show_title') ? 1 : 0,
            'show_featured_image' => $request->has('show_featured_image') ? 1 : 0,
            'show_sidebar_widget' => $request->has('show_sidebar_widget') ? 1 : 0,
            'custom_css'          => (string) ($request->input('custom_css') ?? ''),
            'custom_js'           => (string) ($request->input('custom_js') ?? ''),
            'seo_title'           => $request->input('seo_title') ?: $request->input('title'),
            'seo_description'     => (string) ($request->input('seo_description') ?? ''),
            'seo_keywords'        => (string) ($request->input('seo_keywords') ?? ''),
            'focus_keyphrase'     => (string) ($request->input('focus_keyphrase') ?? ''),
            'seo_canonical_url'   => $request->input('seo_canonical_url'),
            'allow_indexing'      => $request->has('allow_indexing') ? 1 : 0,
            'status'              => $request->input('status', 'published'),
            'updated_at'          => now(),
        ]);

        return redirect()->route('admin.content.pages.index')->with('success', 'Page updated successfully.');
    }

    public function pagesDestroy($id)
    {
        $page = DB::table('site_pages')->where('id', $id)->first();
        abort_if(!$page, 404);

        $protectedSlugs = ['home'];
        if (in_array(strtolower($page->slug), $protectedSlugs)) {
            return redirect()->route('admin.content.pages.index')->with('error', 'Action prohibited: Homepage cannot be deleted.');
        }

        if ($page->featured_image) {
            $this->deleteContentFile($page->featured_image);
        }

        DB::table('site_pages')->where('id', $id)->delete();
        return redirect()->route('admin.content.pages.index')->with('success', 'Page deleted.');
    }

    private function ensurePageColumnsExist()
    {
        $columns = [
            'page_width'          => 'string',
            'title_font_size'     => 'string',
            'show_title'          => 'tinyInteger',
            'show_featured_image' => 'tinyInteger',
            'show_sidebar_widget' => 'tinyInteger',
            'focus_keyphrase'     => 'string',
            'allow_indexing'      => 'tinyInteger',
        ];

        foreach ($columns as $column => $type) {
            if (!Schema::hasColumn('site_pages', $column)) {
                Schema::table('site_pages', function ($table) use ($column, $type) {
                    if ($type === 'tinyInteger') {
                        $table->tinyInteger($column)->default(1);
                    } else {
                        $table->string($column)->nullable();
                    }
                });
            }
        }
    }

    // =========================================================================
    // 2. BLOG POSTS & COMMENT MODERATION
    // =========================================================================
    public function blogsIndex(Request $request)
    {
        $this->ensureBlogColumnsExist();

        $query = DB::table('blog_posts');
        if (Schema::hasColumn('blog_posts', 'author_id')) {
            $query->leftJoin('users', 'blog_posts.author_id', '=', 'users.id')
                  ->select('blog_posts.*', 'users.name as author_name');
        } else {
            $query->select('blog_posts.*', DB::raw("'Editorial Desk' as author_name"));
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('blog_posts.title', 'like', "%{$q}%")
                    ->orWhere('blog_posts.category', 'like', "%{$q}%");
                if (Schema::hasColumn('blog_posts', 'author_id')) {
                    $sub->orWhere('users.name', 'like', "%{$q}%");
                }
            });
        }

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('blog_posts.category', $request->category);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('blog_posts.status', $request->status);
        }

        if ($request->filled('comments') && $request->comments !== 'all') {
            $query->where('blog_posts.enable_comments', $request->comments === 'enabled' ? 1 : 0);
        }

        $sort = $request->get('sort', 'recent');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('blog_posts.id', 'asc');
                break;
            case 'title_asc':
                $query->orderBy('blog_posts.title', 'asc');
                break;
            case 'title_desc':
                $query->orderBy('blog_posts.title', 'desc');
                break;
            case 'recent':
            default:
                $query->latest('blog_posts.id');
                break;
        }

        $posts = $query->paginate(15)->withQueryString();
        $categories = DB::table('blog_posts')->whereNotNull('category')->distinct()->pluck('category');

        if ($request->ajax()) {
            return response()->json([
                'html'       => view('admin.content.blogs.partials.posts_table_rows', compact('posts'))->render(),
                'pagination' => $posts->links('pagination::bootstrap-5')->render(),
                'total'      => $posts->total()
            ]);
        }

        return view('admin.content.blogs.index', compact('posts', 'categories'));
    }

    public function blogsCreate()
    {
        $this->ensureBlogColumnsExist();
        return view('admin.content.blogs.create');
    }

    public function blogsStore(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug'  => 'required|string|unique:blog_posts,slug',
        ]);

        $featuredImage = null;
        if ($request->hasFile('featured_image_file')) {
            $featuredImage = $this->saveContentFile($request->file('featured_image_file'), 'blogs');
        } elseif ($request->filled('featured_image')) {
            $featuredImage = $this->normalizeMediaInputPath($request->input('featured_image'));
        }

        $this->ensureBlogColumnsExist();

        $insertData = [
            'title'               => $validated['title'],
            'slug'                => Str::slug($validated['slug']),
            'excerpt'             => $request->input('excerpt', ''),
            'content'             => $request->input('content', ''),
            'featured_image'      => (string) ($featuredImage ?? ''),
            'category'            => $request->input('category', 'General'),
            'tags'                => (string) ($request->input('tags') ?? ''),
            'editor_type'         => $request->input('editor_type', 'visual'),
            'page_width'          => $request->input('page_width', 'contained-wide'),
            'title_font_size'     => $request->input('title_font_size', '32px'),
            'show_title'          => $request->has('show_title') ? 1 : 0,
            'show_featured_image' => $request->has('show_featured_image') ? 1 : 0,
            'show_sidebar_left'   => $request->has('show_sidebar_left') ? 1 : 0,
            'show_sidebar_right'  => $request->has('show_sidebar_right') ? 1 : 0,
            'enable_comments'     => $request->has('enable_comments') ? 1 : 0,
            'custom_css'          => (string) ($request->input('custom_css') ?? ''),
            'custom_js'           => (string) ($request->input('custom_js') ?? ''),
            'seo_title'           => $request->input('seo_title') ?: $validated['title'],
            'seo_description'     => $request->input('seo_description') ?: Str::limit(strip_tags($request->input('content', '')), 160),
            'seo_keywords'        => (string) ($request->input('seo_keywords') ?? ''),
            'focus_keyphrase'     => (string) ($request->input('focus_keyphrase') ?? ''),
            'seo_canonical_url'   => $request->input('seo_canonical_url'),
            'allow_indexing'      => $request->has('allow_indexing') ? 1 : 0,
            'seo_og_image'        => (string) ($featuredImage ?? ''),
            'status'              => $request->input('status', 'published'),
            'created_at'          => now(),
            'updated_at'          => now(),
        ];

        if (Schema::hasColumn('blog_posts', 'author_id')) {
            $insertData['author_id'] = auth()->id() ?? 1;
        }

        DB::table('blog_posts')->insert($insertData);

        return redirect()->route('admin.content.blogs.index')->with('success', 'Blog post created successfully.');
    }

    public function blogsEdit($id)
    {
        $this->ensureBlogColumnsExist();
        $post = DB::table('blog_posts')->where('id', $id)->first();
        abort_if(!$post, 404);
        return view('admin.content.blogs.edit', compact('post'));
    }

    public function blogsUpdate(Request $request, $id)
    {
        $post = DB::table('blog_posts')->where('id', $id)->first();
        abort_if(!$post, 404);

        $request->validate([
            'title' => 'required|string|max:255',
            'slug'  => 'required|string|unique:blog_posts,slug,' . $id,
        ]);

        $featuredImage = $post->featured_image ?? '';

        if ($request->input('remove_featured_image') == '1') {
            if ($featuredImage) {
                $this->deleteContentFile($featuredImage);
            }
            $featuredImage = '';
        }

        if ($request->hasFile('featured_image_file')) {
            if ($featuredImage) {
                $this->deleteContentFile($featuredImage);
            }
            $featuredImage = $this->saveContentFile($request->file('featured_image_file'), 'blogs');
        } elseif ($request->filled('featured_image')) {
            $normalizedMedia = $this->normalizeMediaInputPath($request->input('featured_image'));
            if (!empty($normalizedMedia)) {
                if ($featuredImage && $featuredImage !== $normalizedMedia) {
                    $this->deleteContentFile($featuredImage);
                }
                $featuredImage = $normalizedMedia;
            }
        }

        $this->ensureBlogColumnsExist();

        DB::table('blog_posts')->where('id', $id)->update([
            'title'               => $request->input('title'),
            'slug'                => Str::slug($request->input('slug')),
            'excerpt'             => $request->input('excerpt', ''),
            'content'             => $request->input('content', ''),
            'featured_image'      => (string) ($featuredImage ?? ''),
            'category'            => $request->input('category', 'General'),
            'tags'                => (string) ($request->input('tags') ?? ''),
            'editor_type'         => $request->input('editor_type', 'visual'),
            'page_width'          => $request->input('page_width', 'contained-wide'),
            'title_font_size'     => $request->input('title_font_size', '32px'),
            'show_title'          => $request->has('show_title') ? 1 : 0,
            'show_featured_image' => $request->has('show_featured_image') ? 1 : 0,
            'show_sidebar_left'   => $request->has('show_sidebar_left') ? 1 : 0,
            'show_sidebar_right'  => $request->has('show_sidebar_right') ? 1 : 0,
            'enable_comments'     => $request->has('enable_comments') ? 1 : 0,
            'custom_css'          => (string) ($request->input('custom_css') ?? ''),
            'custom_js'           => (string) ($request->input('custom_js') ?? ''),
            'seo_title'           => $request->input('seo_title') ?: $request->input('title'),
            'seo_description'     => $request->input('seo_description') ?: Str::limit(strip_tags($request->input('content', '')), 160),
            'seo_keywords'        => (string) ($request->input('seo_keywords') ?? ''),
            'focus_keyphrase'     => (string) ($request->input('focus_keyphrase') ?? ''),
            'seo_canonical_url'   => $request->input('seo_canonical_url'),
            'allow_indexing'      => $request->has('allow_indexing') ? 1 : 0,
            'seo_og_image'        => (string) ($featuredImage ?? ''),
            'status'              => $request->input('status', 'published'),
            'updated_at'          => now(),
        ]);

        return redirect()->route('admin.content.blogs.index')->with('success', 'Blog post updated successfully.');
    }

    public function blogsDestroy($id)
    {
        $post = DB::table('blog_posts')->where('id', $id)->first();
        if ($post && $post->featured_image) {
            $this->deleteContentFile($post->featured_image);
        }

        DB::table('blog_posts')->where('id', $id)->delete();
        if (Schema::hasTable('blog_comments')) {
            DB::table('blog_comments')->where('blog_post_id', $id)->delete();
        }
        return redirect()->route('admin.content.blogs.index')->with('success', 'Blog post deleted successfully.');
    }

    public function blogsToggleComments($id)
    {
        $post = DB::table('blog_posts')->where('id', $id)->first();
        if ($post) {
            $newStatus = !empty($post->enable_comments) ? 0 : 1;
            DB::table('blog_posts')->where('id', $id)->update(['enable_comments' => $newStatus]);
            return response()->json(['status' => 'success', 'enabled' => $newStatus]);
        }
        return response()->json(['status' => 'error'], 404);
    }

    public function blogCommentsIndex(Request $request)
    {
        $query = DB::table('blog_comments')
            ->leftJoin('users', 'blog_comments.user_id', '=', 'users.id')
            ->leftJoin('blog_posts', 'blog_comments.blog_post_id', '=', 'blog_posts.id')
            ->select(
                'blog_comments.*',
                'users.name as user_name',
                'users.email as user_email',
                'users.phone as user_phone',
                'users.created_at as user_created_at',
                'blog_posts.title as post_title',
                'blog_posts.slug as post_slug',
                'blog_posts.created_at as post_created_at'
            );

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('users.name', 'like', "%{$q}%")
                    ->orWhere('users.email', 'like', "%{$q}%")
                    ->orWhere('blog_comments.comment', 'like', "%{$q}%")
                    ->orWhere('blog_comments.blog_post_id', $q);
                if (Schema::hasColumn('users', 'phone')) {
                    $sub->orWhere('users.phone', 'like', "%{$q}%");
                }
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'pending') {
                $query->where('blog_comments.status', 0);
            } elseif ($request->status === 'approved') {
                $query->where('blog_comments.status', 1);
            } elseif ($request->status === 'spam') {
                $query->where('blog_comments.status', 2);
            }
        }

        $sort = $request->get('sort', 'recent_comment');
        switch ($sort) {
            case 'oldest':
            case 'old_comment':
                $query->orderBy('blog_comments.id', 'asc');
                break;
            case 'recent_post':
                $query->orderBy('blog_posts.id', 'desc')->orderBy('blog_comments.id', 'desc');
                break;
            case 'old_post':
                $query->orderBy('blog_posts.id', 'asc')->orderBy('blog_comments.id', 'desc');
                break;
            case 'new_user':
                $query->orderBy('users.id', 'desc');
                break;
            case 'old_user':
                $query->orderBy('users.id', 'asc');
                break;
            case 'recent_comment':
            default:
                $query->orderBy('blog_comments.id', 'desc');
                break;
        }

        $comments = $query->paginate(15)->withQueryString();
        $pendingCount = DB::table('blog_comments')->where('status', 0)->count();
        $autoApproveSetting = DB::table('settings')->where('key', 'blog_comment_auto_approve')->value('value') ?? '1';

        if ($request->ajax()) {
            return response()->json([
                'html'          => view('admin.content.blogs.partials.comments_table_rows', compact('comments'))->render(),
                'pagination'    => $comments->links('pagination::bootstrap-5')->render(),
                'total'         => $comments->total(),
                'pending_count' => $pendingCount
            ]);
        }

        return view('admin.content.blogs.comments', compact('comments', 'pendingCount', 'autoApproveSetting'));
    }

    public function blogCommentStatus(Request $request, $id)
    {
        $status = (int) $request->input('status', 1);
        DB::table('blog_comments')->where('id', $id)->update([
            'status'     => $status,
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Comment status updated successfully.');
    }

    public function blogCommentDestroy($id)
    {
        DB::table('blog_comments')->where('parent_id', $id)->delete();
        DB::table('blog_comments')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Comment deleted successfully.');
    }

    public function blogCommentReply(Request $request, $id)
    {
        $request->validate(['reply' => 'required|string|max:1000']);

        $parent = DB::table('blog_comments')->where('id', $id)->first();
        if (!$parent) {
            return response()->json(['status' => 'error', 'message' => 'Comment not found.'], 404);
        }

        $cleanReply = e(strip_tags(trim($request->reply)));

        DB::table('blog_comments')->insert([
            'blog_post_id' => $parent->blog_post_id,
            'user_id'      => auth()->id() ?? 1,
            'parent_id'    => $id,
            'comment'      => $cleanReply,
            'status'       => 1,
            'is_edited'    => 0,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Reply posted successfully!']);
    }

    public function blogToggleAutoApprove(Request $request)
    {
        $enable = $request->boolean('auto_approve') ? '1' : '0';

        $exists = DB::table('settings')->where('key', 'blog_comment_auto_approve')->first();
        if ($exists) {
            DB::table('settings')->where('key', 'blog_comment_auto_approve')->update(['value' => $enable, 'updated_at' => now()]);
        } else {
            DB::table('settings')->insert([
                'key'        => 'blog_comment_auto_approve',
                'value'      => $enable,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return response()->json([
            'status'       => 'success',
            'auto_approve' => $enable,
            'message'      => $enable === '1' ? 'Auto-approve is now ENABLED.' : 'Auto-approve is now DISABLED (Comments will wait for approval).'
        ]);
    }

    private function ensureBlogColumnsExist()
    {
        $columns = [
            'author_id'           => 'bigInteger',
            'views_count'         => 'bigInteger',
            'page_width'          => 'string',
            'title_font_size'     => 'string',
            'show_title'          => 'tinyInteger',
            'show_featured_image' => 'tinyInteger',
            'show_sidebar_left'   => 'tinyInteger',
            'show_sidebar_right'  => 'tinyInteger',
            'enable_comments'     => 'tinyInteger',
            'focus_keyphrase'     => 'string',
            'seo_canonical_url'   => 'string',
            'allow_indexing'      => 'tinyInteger',
            'editor_type'         => 'string',
        ];

        foreach ($columns as $column => $type) {
            if (!Schema::hasColumn('blog_posts', $column)) {
                Schema::table('blog_posts', function ($table) use ($column, $type) {
                    if ($type === 'tinyInteger') {
                        $table->tinyInteger($column)->default(1);
                    } elseif ($type === 'bigInteger') {
                        $table->unsignedBigInteger($column)->default(0);
                    } else {
                        $table->string($column)->nullable();
                    }
                });
            }
        }
    }

    // =========================================================================
    // 3. MENUS MANAGEMENT
    // =========================================================================
    public function menusIndex(Request $request)
    {
        $allMenus = DB::table('site_menus')->get();
        $selectedMenuId = $request->get('menu', optional($allMenus->first())->id);
        $currentMenu = $allMenus->where('id', $selectedMenuId)->first();

        $pages = DB::table('site_pages')->select('id', 'title', 'slug')->get();
        $blogPosts = DB::table('blog_posts')->select('id', 'title', 'slug')->get();
        $categories = DB::table('categories')->select('id', 'name', 'slug')->get();

        return view('admin.content.menus.index', compact('allMenus', 'currentMenu', 'pages', 'blogPosts', 'categories'));
    }

    public function menusCreate()
    {
        $allMenus = DB::table('site_menus')->get();
        $currentMenu = null;
        $pages = DB::table('site_pages')->select('id', 'title', 'slug')->get();
        $blogPosts = DB::table('blog_posts')->select('id', 'title', 'slug')->get();
        $categories = DB::table('categories')->select('id', 'name', 'slug')->get();

        return view('admin.content.menus.index', compact('allMenus', 'currentMenu', 'pages', 'blogPosts', 'categories'));
    }

    public function menusSave(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $menuId = $request->input('menu_id');
        $locations = $request->input('locations', []);

        if (!empty($locations)) {
            $otherMenus = DB::table('site_menus')->where('id', '!=', $menuId)->get();
            foreach ($otherMenus as $om) {
                $omLocs = json_decode($om->locations, true) ?: [];
                $updatedLocs = array_diff($omLocs, $locations);
                DB::table('site_menus')->where('id', $om->id)->update(['locations' => json_encode(array_values($updatedLocs))]);
            }
        }

        $menuData = [
            'name'           => $request->input('name'),
            'locations'      => json_encode($locations),
            'items'          => json_encode($request->input('items', [])),
            'auto_add_pages' => $request->boolean('auto_add_pages', false),
            'updated_at'     => now(),
        ];

        if ($menuId) {
            DB::table('site_menus')->where('id', $menuId)->update($menuData);
            $targetId = $menuId;
        } else {
            $menuData['created_at'] = now();
            $targetId = DB::table('site_menus')->insertGetId($menuData);
        }

        return response()->json([
            'status'   => 'success',
            'message'  => 'Menu saved successfully!',
            'redirect' => route('admin.content.menus.index', ['menu' => $targetId])
        ]);
    }

    public function menusDelete($id)
    {
        DB::table('site_menus')->where('id', $id)->delete();
        return redirect()->route('admin.content.menus.index')->with('success', 'Menu deleted.');
    }

    // =========================================================================
    // 4. WIDGETS MANAGEMENT
    // =========================================================================
    public function widgetsIndex()
    {
        $widgets = DB::table('site_widgets')->orderBy('widget_zone')->orderBy('order_index', 'asc')->get();
        return view('admin.content.widgets.index', compact('widgets'));
    }

    public function widgetsStore(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'widget_zone' => 'required|string',
            'widget_type' => 'required|string',
        ]);

        DB::table('site_widgets')->insert([
            'title'       => $request->input('title'),
            'widget_zone' => $request->input('widget_zone'),
            'widget_type' => $request->input('widget_type'),
            'content'     => $request->input('content'),
            'order_index' => $request->input('order_index', 0),
            'is_active'   => $request->has('is_active') ? 1 : 0,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return redirect()->back()->with('success', 'Widget added.');
    }

    public function widgetsUpdate(Request $request, $id)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'widget_zone' => 'required|string',
            'widget_type' => 'required|string',
        ]);

        DB::table('site_widgets')->where('id', $id)->update([
            'title'       => $request->input('title'),
            'widget_zone' => $request->input('widget_zone'),
            'widget_type' => $request->input('widget_type'),
            'content'     => $request->input('content'),
            'order_index' => $request->input('order_index', 0),
            'is_active'   => $request->has('is_active') ? 1 : 0,
            'updated_at'  => now(),
        ]);

        return redirect()->back()->with('success', 'Widget updated.');
    }

    public function widgetsDestroy($id)
    {
        DB::table('site_widgets')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Widget deleted.');
    }

    public function widgetsToggle($id)
    {
        $widget = DB::table('site_widgets')->where('id', $id)->first();
        if ($widget) {
            DB::table('site_widgets')->where('id', $id)->update([
                'is_active'  => $widget->is_active ? 0 : 1,
                'updated_at' => now()
            ]);
        }
        return redirect()->back()->with('success', 'Widget status updated.');
    }

    // =========================================================================
    // 5. MEDIA LIBRARY
    // =========================================================================
    public function mediaIndex()
    {
        $files = DB::table('media_files')->latest('id')->paginate(36);
        return view('admin.content.media.index', compact('files'));
    }

    public function mediaUpload(Request $request)
    {
        $request->validate([
            'files.*' => 'nullable|file|mimes:jpeg,png,jpg,webp,svg,gif,pdf|max:10240',
            'file'    => 'nullable|file|mimes:jpeg,png,jpg,webp,svg,gif,pdf|max:10240'
        ]);

        $uploaded = [];
        $filesToProcess = [];

        if ($request->hasFile('files')) {
            $filesToProcess = $request->file('files');
        } elseif ($request->hasFile('file')) {
            $filesToProcess = [$request->file('file')];
        }

        if (!empty($filesToProcess)) {
            foreach ($filesToProcess as $file) {
                if (!$file->isValid()) continue;

                $originalName = $file->getClientOriginalName();
                $cleanSubDir = 'media';
                
                $path = $this->saveContentFile($file, $cleanSubDir);

                $mediaId = DB::table('media_files')->insertGetId([
                    'file_name'   => $originalName,
                    'file_path'   => $path,
                    'file_type'   => $file->getMimeType(),
                    'file_size'   => $file->getSize(),
                    'title'       => pathinfo($originalName, PATHINFO_FILENAME),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                $uploaded[] = [
                    'id'   => $mediaId,
                    'url'  => ImagePathService::resolveUrl($path),
                    'name' => $originalName
                ];
            }
        }

        return response()->json(['status' => 'success', 'files' => $uploaded]);
    }

    public function mediaDestroy($id)
    {
        $file = DB::table('media_files')->where('id', $id)->first();
        if ($file) {
            $this->deleteContentFile($file->file_path);
            DB::table('media_files')->where('id', $id)->delete();
        }

        return response()->json(['status' => 'success', 'message' => 'Media deleted.']);
    }
}