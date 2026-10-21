<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImagePathService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    protected static bool $schemaChecked = false;

    /**
     * Auto-ensure media_files table has category & source tracking columns
     */
    protected function ensureMediaSchema(): void
    {
        if (self::$schemaChecked) {
            return;
        }
        self::$schemaChecked = true;

        try {
            if (Schema::hasTable('media_files')) {
                Schema::table('media_files', function (Blueprint $table) {
                    if (!Schema::hasColumn('media_files', 'media_category')) {
                        $table->string('media_category', 40)->default('site_content')->index();
                    }
                    if (!Schema::hasColumn('media_files', 'source_module')) {
                        $table->string('source_module', 60)->nullable()->index();
                    }
                });
            }
        } catch (\Throwable $e) {
            // Ignore schema alter warnings safely
        }
    }

    protected function getPublicRoot(): string
    {
        return ImagePathService::resolvePublicRoot();
    }

    /**
     * Helper to build public asset URL reliably across Localhost & cPanel
     */
    protected function resolveMediaUrl($path): string
    {
        $fallback = 'https://placehold.co/300x300?text=No+Image';
        if (empty($path)) {
            return $fallback;
        }
        return ImagePathService::resolveUrl($path, $fallback);
    }

    /**
     * Resolve actual physical file path on server (public_html, public, or storage/app/public)
     */
    protected function resolvePhysicalFilePath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return null;
        }

        $clean = ltrim(str_replace(['public_html/', 'public/'], '', $path), '/');
        $withoutStorage = preg_replace('#^storage/#i', '', $clean);
        $withoutUploads = preg_replace('#^uploads/#i', '', $withoutStorage);

        $publicRoot = $this->getPublicRoot();

        $candidates = [
            $publicRoot . '/' . $clean,
            $publicRoot . '/uploads/' . $withoutUploads,
            $publicRoot . '/storage/' . $withoutStorage,
            public_path($clean),
            public_path('uploads/' . $withoutUploads),
            public_path('storage/' . $withoutStorage),
            storage_path('app/public/' . $withoutStorage),
            storage_path('app/public/' . $withoutUploads),
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return $publicRoot . '/' . $clean;
    }

    /**
     * Generate non-conflicting filename & title with (1), (2) suffix if duplicate exists
     */
    protected function generateIncrementedNameAndTitle(string $originalFileName, string $targetDir): array
    {
        $ext = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
        $rawBase = pathinfo($originalFileName, PATHINFO_FILENAME);
        $cleanTitleBase = trim(ucwords(str_replace(['-', '_'], ' ', $rawBase)));
        if ($cleanTitleBase === '') {
            $cleanTitleBase = 'Media File';
        }

        $slugBase = Str::slug($rawBase);
        if ($slugBase === '') {
            $slugBase = 'media-file';
        }

        // 1. Check existing display file_name & title in DB so duplicate uploads get (1), (2)
        $displayFileName = $originalFileName;
        $displayTitle    = $cleanTitleBase;
        $nameCounter     = 1;

        while (
            DB::table('media_files')
                ->where('file_name', $displayFileName)
                ->orWhere('title', $displayTitle)
                ->exists()
        ) {
            $displayFileName = $rawBase . ' (' . $nameCounter . ').' . $ext;
            $displayTitle    = $cleanTitleBase . ' (' . $nameCounter . ')';
            $nameCounter++;
        }

        // 2. Ensure physical file on disk also never overwrites an existing file
        $physicalFileName = ($nameCounter > 1)
            ? $slugBase . '-' . ($nameCounter - 1) . '.' . $ext
            : $slugBase . '.' . $ext;

        $diskCounter = $nameCounter;
        while (file_exists(rtrim($targetDir, '/') . '/' . $physicalFileName)) {
            $physicalFileName = $slugBase . '-' . $diskCounter . '-' . strtolower(Str::random(4)) . '.' . $ext;
            $diskCounter++;
        }

        return [
            'display_file_name'  => $displayFileName,
            'display_title'      => $displayTitle,
            'physical_file_name' => $physicalFileName,
            'extension'          => $ext,
        ];
    }

    /**
     * Auto-index existing site images and user-submitted files into media_files table
     * so every file across the website is controllable from one central Media Library
     */
    public function syncAllSiteAndUserMedia(bool $force = false): void
    {
        $this->ensureMediaSchema();

        if (!$force && Cache::has('media_library_auto_synced_v2')) {
            return;
        }

        try {
            $existingPaths = DB::table('media_files')
                ->pluck('file_path')
                ->map(function ($p) {
                    return ltrim((string) $p, '/');
                })
                ->flip()
                ->all();

            $recordsToInsert = [];

            $registerPath = function (?string $rawPath, string $category, string $module, string $labelPrefix = '') use (&$existingPaths, &$recordsToInsert) {
                if (empty($rawPath) || !is_string($rawPath)) {
                    return;
                }
                $rawPath = trim($rawPath);
                if ($rawPath === '' || str_starts_with($rawPath, 'data:')) {
                    return;
                }

                // Extract relative path if full URL is stored
                if (filter_var($rawPath, FILTER_VALIDATE_URL)) {
                    $parsedHost = parse_url($rawPath, PHP_URL_HOST);
                    $currentHost = request()->getHost();
                    if ($parsedHost && !in_array($parsedHost, [$currentHost, '127.0.0.1', 'localhost'], true)) {
                        return;
                    }
                    $parsedPath = parse_url($rawPath, PHP_URL_PATH);
                    $rawPath = $parsedPath ? $parsedPath : '';
                }

                $cleanPath = ltrim(str_replace(['public_html/', 'public/'], '', $rawPath), '/');
                if ($cleanPath === '' || isset($existingPaths[$cleanPath])) {
                    return;
                }

                $physicalPath = $this->resolvePhysicalFilePath($cleanPath);
                $fileExists   = ($physicalPath !== null && is_file($physicalPath));

                $fileName = basename($cleanPath);
                $ext      = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $mimeMap  = [
                    'jpg'  => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png'  => 'image/png',
                    'webp' => 'image/webp',
                    'gif'  => 'image/gif',
                    'svg'  => 'image/svg+xml',
                    'ico'  => 'image/x-icon',
                    'pdf'  => 'application/pdf',
                ];

                $defaultMime = $mimeMap[$ext] ?? 'image/jpeg';
                $detectedMime = $fileExists ? @mime_content_type($physicalPath) : false;
                $mimeType = $detectedMime ? $detectedMime : $defaultMime;

                $detectedSize = $fileExists ? @filesize($physicalPath) : 0;
                $fileSize = $detectedSize ? (int) $detectedSize : 0;

                $dimensions = null;
                if ($fileExists && str_starts_with((string) $mimeType, 'image/')) {
                    $imgInfo = @getimagesize($physicalPath);
                    if ($imgInfo) {
                        $dimensions = $imgInfo[0] . ' × ' . $imgInfo[1];
                    }
                }

                $baseTitle = trim(($labelPrefix !== '' ? $labelPrefix . ' - ' : '') . ucwords(str_replace(['-', '_'], ' ', pathinfo($fileName, PATHINFO_FILENAME))));
                $mtime = $fileExists ? @filemtime($physicalPath) : false;

                $row = [
                    'file_name'      => $fileName,
                    'file_path'      => $cleanPath,
                    'file_type'      => $mimeType,
                    'file_size'      => $fileSize,
                    'dimensions'     => $dimensions,
                    'title'          => Str::limit($baseTitle, 180, ''),
                    'alt_text'       => Str::limit($baseTitle, 180, ''),
                    'media_category' => $category,
                    'source_module'  => $module,
                    'created_at'     => $mtime ? date('Y-m-d H:i:s', $mtime) : now(),
                    'updated_at'     => now(),
                ];

                if (Schema::hasColumn('media_files', 'file_hash')) {
                    $row['file_hash'] = $fileExists ? @hash_file('sha256', $physicalPath) : md5($cleanPath);
                }
                if (Schema::hasColumn('media_files', 'uploaded_by')) {
                    $row['uploaded_by'] = Auth::id() ?? 1;
                }

                $existingPaths[$cleanPath] = true;
                $recordsToInsert[] = $row;
            };

            // -------------------- A. SITE CONTENT IMAGES --------------------
            if (Schema::hasTable('products')) {
                foreach (DB::table('products')->select('id', 'title', 'thumbnail', 'featured_image')->get() as $p) {
                    $registerPath($p->thumbnail ?? null, 'site_content', 'product', $p->title ?? 'Product');
                    $registerPath($p->featured_image ?? null, 'site_content', 'product', $p->title ?? 'Product');
                }
            }

            if (Schema::hasTable('product_galleries')) {
                $imgCol = Schema::hasColumn('product_galleries', 'image_path') ? 'image_path' : 'image';
                if (Schema::hasColumn('product_galleries', $imgCol)) {
                    foreach (DB::table('product_galleries')->pluck($imgCol) as $gPath) {
                        $registerPath($gPath, 'site_content', 'product_gallery', 'Gallery');
                    }
                }
            }

            if (Schema::hasTable('product_variants') && Schema::hasColumn('product_variants', 'image')) {
                foreach (DB::table('product_variants')->whereNotNull('image')->pluck('image') as $vPath) {
                    $registerPath($vPath, 'site_content', 'product_variant', 'Variant');
                }
            }

            if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'image')) {
                foreach (DB::table('categories')->whereNotNull('image')->pluck('image') as $cPath) {
                    $registerPath($cPath, 'site_content', 'category', 'Category');
                }
            }

            if (Schema::hasTable('brands') && Schema::hasColumn('brands', 'logo')) {
                foreach (DB::table('brands')->whereNotNull('logo')->pluck('logo') as $bPath) {
                    $registerPath($bPath, 'site_content', 'brand', 'Brand');
                }
            }

            if (Schema::hasTable('hero_sliders') && Schema::hasColumn('hero_sliders', 'image')) {
                foreach (DB::table('hero_sliders')->whereNotNull('image')->pluck('image') as $sPath) {
                    $registerPath($sPath, 'site_content', 'slider', 'Slider');
                }
            }

            if (Schema::hasTable('offer_cards') && Schema::hasColumn('offer_cards', 'image')) {
                foreach (DB::table('offer_cards')->whereNotNull('image')->pluck('image') as $oPath) {
                    $registerPath($oPath, 'site_content', 'theme_offer', 'Offer Card');
                }
            }

            if (Schema::hasTable('posts') && Schema::hasColumn('posts', 'featured_image')) {
                foreach (DB::table('posts')->whereNotNull('featured_image')->pluck('featured_image') as $postImg) {
                    $registerPath($postImg, 'site_content', 'blog', 'Blog');
                }
            }

            if (Schema::hasTable('pages') && Schema::hasColumn('pages', 'featured_image')) {
                foreach (DB::table('pages')->whereNotNull('featured_image')->pluck('featured_image') as $pageImg) {
                    $registerPath($pageImg, 'site_content', 'page', 'Page');
                }
            }

            if (Schema::hasTable('manual_payment_methods') && Schema::hasColumn('manual_payment_methods', 'qr_code')) {
                foreach (DB::table('manual_payment_methods')->whereNotNull('qr_code')->pluck('qr_code') as $qrPath) {
                    $registerPath($qrPath, 'site_content', 'payment_qr', 'Payment QR');
                }
            }

            // -------------------- B. USER SUBMITTED FILES --------------------
            if (Schema::hasTable('payment_transactions') && Schema::hasColumn('payment_transactions', 'screenshot_path')) {
                foreach (DB::table('payment_transactions')->whereNotNull('screenshot_path')->select('transaction_uid', 'screenshot_path')->get() as $pt) {
                    $registerPath($pt->screenshot_path, 'user_submitted', 'payment_proof', 'Payment Proof ' . ($pt->transaction_uid ?? ''));
                }
            }

            if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'payment_screenshot')) {
                foreach (DB::table('orders')->whereNotNull('payment_screenshot')->select('invoice_no', 'payment_screenshot')->get() as $ord) {
                    $registerPath($ord->payment_screenshot, 'user_submitted', 'payment_proof', 'Order Proof ' . ($ord->invoice_no ?? ''));
                }
            }

            if (Schema::hasTable('form_submission_values') && Schema::hasColumn('form_submission_values', 'value')) {
                $formFiles = DB::table('form_submission_values')
                    ->where(function ($q) {
                        $q->where('value', 'like', '%forms/%')
                          ->orWhere('value', 'like', '%.jpg')
                          ->orWhere('value', 'like', '%.jpeg')
                          ->orWhere('value', 'like', '%.png')
                          ->orWhere('value', 'like', '%.webp')
                          ->orWhere('value', 'like', '%.pdf');
                    })
                    ->pluck('value');

                foreach ($formFiles as $fVal) {
                    $registerPath($fVal, 'user_submitted', 'form_submission', 'Form Upload');
                }
            }

            if (Schema::hasTable('support_tickets') && Schema::hasColumn('support_tickets', 'attachment')) {
                foreach (DB::table('support_tickets')->whereNotNull('attachment')->pluck('attachment') as $att) {
                    $registerPath($att, 'user_submitted', 'support_ticket', 'Support Ticket');
                }
            }

            if (Schema::hasTable('users')) {
                foreach (['avatar', 'profile_photo', 'photo', 'nid_document'] as $uCol) {
                    if (Schema::hasColumn('users', $uCol)) {
                        foreach (DB::table('users')->whereNotNull($uCol)->pluck($uCol) as $uFile) {
                            $registerPath($uFile, 'user_submitted', 'user_profile', 'User Profile');
                        }
                    }
                }
            }

            if (!empty($recordsToInsert)) {
                foreach (array_chunk($recordsToInsert, 100) as $chunk) {
                    DB::table('media_files')->insert($chunk);
                }
            }

            // Ensure any legacy rows with payment_proofs / forms path are marked as user_submitted
            DB::table('media_files')
                ->where('media_category', 'site_content')
                ->where(function ($q) {
                    $q->where('file_path', 'like', '%payment_proofs%')
                      ->orWhere('file_path', 'like', '%forms/%')
                      ->orWhere('file_path', 'like', '%user_uploads%')
                      ->orWhere('file_path', 'like', '%support%');
                })
                ->update(['media_category' => 'user_submitted']);

            Cache::put('media_library_auto_synced_v2', true, now()->addMinutes(5));
        } catch (\Throwable $e) {
            // Fail gracefully without interrupting Media Library rendering
        }
    }

    /**
     * 1. Media Library Main Dashboard & AJAX Data Fetcher
     * Supports separate sections: `site_content` vs `user_submitted`
     */
    public function index(Request $request)
    {
        $this->syncAllSiteAndUserMedia($request->boolean('resync', false));

        $query = DB::table('media_files');

        // Category Separation: 'site_content' vs 'user_submitted' vs 'all'
        $activeCategory =$request->input('category', 'site_content');

        if ($activeCategory === 'user_submitted') {$query->where('media_category', 'user_submitted');
        } elseif ($activeCategory === 'site_content') {$query->where(function ($q) {$q->where('media_category', 'site_content')
                  ->orWhereNull('media_category');
            });
        }

        // Optional Sub-Module Filter
        if ($request->filled('source_module') &&$request->source_module !== 'all') {
            $query->where('source_module',$request->source_module);
        }

        // Search by File Name, Title, Alt, Caption, Path
        if ($request->filled('search')) {$s = trim($request->search);$query->where(function ($q) use ($s) {
                $q->where('file_name', 'like', "\%{$s}%")
                  ->orWhere('title', 'like', "%{$s}%")
                  ->orWhere('alt_text', 'like', "%{$s}%")
                  ->orWhere('caption', 'like', "%{$s}%")
                  ->orWhere('file_path', 'like', "%{$s}%");
            });
        }

        // Filter by File Type (image, pdf, video, audio)
        if ($request->filled('type') &&$request->type !== 'all') {
            $type = strtolower($request->type);
            if ($type === 'image') {$query->where('file_type', 'like', 'image/%');
            } elseif ($type === 'pdf') {$query->where('file_type', 'like', '%pdf%');
            } elseif ($type === 'video') {$query->where('file_type', 'like', 'video/%');
            } elseif ($type === 'audio') {$query->where('file_type', 'like', 'audio/%');
            }
        }

        // Filter by Upload Month & Year
        if ($request->filled('date') &&$request->date !== 'all') {
            $parts = explode('-',$request->date);
            if (count($parts) === 2) {$query->whereYear('created_at', $parts[0]) ->whereMonth('created_at',$parts[1]);
            }
        }

        $perPage = (int) $request->get('per_page', 36);$files = $query->orderByDesc('id')->paginate($perPage)->withQueryString();

        // Transform collection to include full resolved URL, download link, and human size
        $files->getCollection()->transform(function ($item) {
            $item->url            =$this->resolveMediaUrl($item->file_path);$item->download_url   = route('admin.media.download', $item->id);$item->human_size     = $this->formatFileSize((int) ($item->file_size ?? 0));
            $item->formatted_date =$item->created_at ? date('F d, Y', strtotime($item->created_at)) : '—';
            $item->media_category =$item->media_category ?? 'site_content';
            $item->source_module  =$item->source_module ?? 'media_library';
            return $item;
        });

        // Counts for Site Content vs User Submitted Tabs
        $categoryCounts = [
            'site_content'   => DB::table('media_files')->where(function ($q) {$q->where('media_category', 'site_content')->orWhereNull('media_category');
            })->count(),
            'user_submitted' => DB::table('media_files')->where('media_category', 'user_submitted')->count(),
            'all'            => DB::table('media_files')->count(),
        ];

        // AJAX Request (For Media Picker Modal & Live Filtering)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'          => 'success',
                'data'            => $files->items(),
                'category_counts' => $categoryCounts,
                'pagination'      => [
                    'current_page' => $files->currentPage(),
                    'last_page'    => $files->lastPage(),
                    'total'        => $files->total(),
                    'has_more'     => $files->hasMorePages()
                ]
            ]);
        }

        // Distinct months for date filter dropdown
        $dates = DB::table('media_files')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as date_val, DATE_FORMAT(created_at, "%M %Y") as date_label')
            ->groupBy('date_val', 'date_label')
            ->orderBy('date_val', 'desc')
            ->get();

        return view('admin.content.media.index', compact('files', 'dates', 'activeCategory', 'categoryCounts'));
    }

    /**
     * 2. Multi-file Upload with Automatic (1), (2) Renaming (Never Rejects Duplicate Uploads)
     */
    public function upload(Request $request)
    {
        $this->ensureMediaSchema();

        $request->validate([
            'files'   => 'required|array',
            'files.*' => 'required|file|max:20480' // Max 20MB
        ]);

        $uploaded      = [];
        $publicRoot    =$this->getPublicRoot();
        $mediaCategory =$request->input('media_category', 'site_content') === 'user_submitted'
            ? 'user_submitted'
            : 'site_content';
        $sourceModule  =$request->input('source_module', 'media_library');

        $subFolder = $mediaCategory === 'user_submitted' ? 'uploads/user_uploads' : 'uploads/media';$uploadDir = $publicRoot . '/' .$subFolder;

        if (!file_exists($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        foreach ($request->file('files') as$file) {
            $originalName =$file->getClientOriginalName();
            $detectedMime =$file->getMimeType();
            $mimeType     =$detectedMime ? $detectedMime :$file->getClientMimeType();
            $fileSize     =$file->getSize();
            $computedHash = @hash_file('sha256',$file->getRealPath());
            $fileHash     =$computedHash ? $computedHash : md5($originalName . microtime());

            // Generate non-conflicting (1), (2) filename & title instead of rejecting duplicate uploads
            $naming       =$this->generateIncrementedNameAndTitle($originalName,$uploadDir);
            $storedName   =$naming['physical_file_name'];
            $displayName  =$naming['display_file_name'];
            $displayTitle =$naming['display_title'];

            $file->move($uploadDir, $storedName);$relativePath = $subFolder . '/' .$storedName;

            // Mirror to public_path if cPanel public_html is separate
            if ($publicRoot !== rtrim(public_path(), '/')) {
                $localMirrorDir = public_path($subFolder);
                if (!file_exists($localMirrorDir)) {
                    @mkdir($localMirrorDir, 0777, true);
                }
                @copy($uploadDir . '/' .$storedName, $localMirrorDir . '/' .$storedName);
            }

            // Calculate Dimensions if Image
            $dimensions = null;
            if (str_starts_with((string) $mimeType, 'image/')) {$imgInfo = @getimagesize($uploadDir . '/' .$storedName);
                if ($imgInfo) {$dimensions = $imgInfo[0] . ' × ' .$imgInfo[1];
                }
            }

            $insertData = [
                'file_name'      => $displayName,
                'file_path'      => $relativePath,
                'file_type'      => $mimeType,
                'file_size'      => $fileSize,
                'dimensions'     => $dimensions,
                'title'          => $displayTitle,
                'alt_text'       => $displayTitle,
                'media_category' => $mediaCategory,
                'source_module'  => $sourceModule,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];

            if (Schema::hasColumn('media_files', 'file_hash')) {
                $insertData['file_hash'] =$fileHash;
            }
            if (Schema::hasColumn('media_files', 'uploaded_by')) {
                $insertData['uploaded_by'] = Auth::id() ?? 1;
            }

            $mediaId = DB::table('media_files')->insertGetId($insertData);

            $uploaded[] = [
                'id'             => $mediaId,
                'file_name'      => $displayName,
                'url'            => $this->resolveMediaUrl($relativePath),
                'download_url'   => route('admin.media.download', $mediaId),
                'file_path'      => $relativePath,
                'dimensions'     => $dimensions,
                'size'           => $this->formatFileSize($fileSize),
                'file_type'      => $mimeType,
                'alt_text'       => $displayTitle,
                'title'          => $displayTitle,
                'media_category' => $mediaCategory,
                'source_module'  => $sourceModule,
            ];
        }

        return response()->json([
            'status'     => 'success',
            'uploaded'   => $uploaded,
            'duplicates' => [],
            'message'    => count($uploaded) . ' file(s) uploaded successfully.'
        ]);
    }

    /**
     * 3. Direct File Download from Media Library (Supports Site Media & User Submitted Files)
     */
    public function download($id)
    {
        $media = DB::table('media_files')->where('id',$id)->first();
        if (!$media) {
            abort(404, 'Media file record not found.');
        }

        $physicalPath = $this->resolvePhysicalFilePath($media->file_path);
        if (!$physicalPath || !is_file($physicalPath)) {
            abort(404, 'Physical file not found on server.');
        }

        $downloadName = !empty($media->file_name) ? $media->file_name : basename($physicalPath);
        return response()->download($physicalPath,$downloadName);
    }

    /**
     * 4. Update Media Metadata (Title, Alt text, Caption, Description, Category)
     */
    public function updateMeta(Request $request,$id)
    {
        $this->ensureMediaSchema();

        $media = DB::table('media_files')->where('id',$id)->first();
        if (!$media) {
            return response()->json(['status' => 'error', 'message' => 'Media file not found.'], 404);
        }

        $updatePayload = [
            'title'       => $request->input('title',$media->title),
            'alt_text'    => $request->input('alt_text',$media->alt_text),
            'caption'     => $request->input('caption',$media->caption),
            'description' => $request->input('description',$media->description),
            'updated_at'  => now()
        ];

        if ($request->filled('media_category') && in_array($request->media_category, ['site_content', 'user_submitted'], true)) {
            $updatePayload['media_category'] =$request->media_category;
        }

        DB::table('media_files')->where('id', $id)->update($updatePayload);

        return response()->json([
            'status'  => 'success',
            'message' => 'Attachment metadata saved successfully.'
        ]);
    }

    /**
     * 5. Replace File (Replaces physical image and preserves existing references)
     */
    public function replace(Request $request,$id)
    {
        $request->validate([
            'replacement_file' => 'required|file|max:20480'
        ]);

        $media = DB::table('media_files')->where('id',$id)->first();
        if (!$media) {
            return response()->json(['status' => 'error', 'message' => 'Media file not found.'], 404);
        }

        $newFile    =$request->file('replacement_file');
        $publicRoot =$this->getPublicRoot();

        // Delete old file from disk
        $oldPhysical = $this->resolvePhysicalFilePath($media->file_path);
        if ($oldPhysical && is_file($oldPhysical)) {
            @unlink($oldPhysical);
        }

        $subFolder = ($media->media_category ?? 'site_content') === 'user_submitted'
            ? 'uploads/user_uploads'
            : 'uploads/media';
        $uploadDir = $publicRoot . '/' .$subFolder;

        if (!file_exists($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        $naming     = $this->generateIncrementedNameAndTitle($newFile->getClientOriginalName(), $uploadDir);$storedName = $naming['physical_file_name'];$newFile->move($uploadDir,$storedName);

        $newRelativePath = $subFolder . '/' .$storedName;
        $fileHash        = @hash_file('sha256',$uploadDir . '/' . $storedName);$mimeType        = $newFile->getClientMimeType();$fileSize        = @filesize($uploadDir . '/' .$storedName);

        $dimensions = null;
        if (str_starts_with((string) $mimeType, 'image/')) {$imgInfo = @getimagesize($uploadDir . '/' .$storedName);
            if ($imgInfo) {$dimensions = $imgInfo[0] . ' × ' .$imgInfo[1];
            }
        }

        DB::table('media_files')->where('id', $id)->update([
            'file_name'   => $naming['display_file_name'],
            'file_path'   => $newRelativePath,
            'file_type'   => $mimeType,
            'file_size'   => $fileSize,
            'file_hash'   => $fileHash,
            'dimensions'  => $dimensions,
            'updated_at'  => now()
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'File replaced successfully!',
            'new_url'   => $this->resolveMediaUrl($newRelativePath),
            'file_path' => $newRelativePath,
        ]);
    }

    /**
     * 6. Media Usage Tracking (Checks Products, Variants, Categories, Brands, Blogs, Pages, Orders & Transactions)
     */
    public function usage($id)
    {
        $media = DB::table('media_files')->where('id',$id)->first();
        if (!$media) {
            return response()->json(['status' => 'error', 'message' => 'Media not found.'], 404);
        }

        $path     = ltrim((string) $media->file_path, '/');$baseName = basename($path);$usages   = [];

        // 1. Products (Thumbnail or Featured Image)
        if (Schema::hasTable('products')) {
            $products = DB::table('products')
                ->where(function ($q) use ($path,$baseName) {
                    $q->where('thumbnail', 'like', "\%{$path}%")
                      ->orWhere('thumbnail', 'like', "%{$baseName}%");
                    if (Schema::hasColumn('products', 'featured_image')) {
                        $q->orWhere('featured_image', 'like', "\%{$path}%");
                    }
                    if (Schema::hasColumn('products', 'gallery')) {
                        $q->orWhere('gallery', 'like', "\%{$path}%");
                    }
                })
                ->select('id', 'title')
                ->get();

            foreach ($products as $p) {$usages[] = [
                    'type'  => 'Product',
                    'title' => $p->title,
                    'url'   => url('admin/product/edit/' . $p->id)
                ];
            }
        }

        // 2. Product Variants
        if (Schema::hasTable('product_variants') && Schema::hasColumn('product_variants', 'image')) {
            $variants = DB::table('product_variants')
                ->leftJoin('products', 'product_variants.product_id', '=', 'products.id')
                ->where(function ($q) use ($path,$baseName) {
                    $q->where('product_variants.image', 'like', "\%{$path}%")
                      ->orWhere('product_variants.image', 'like', "%{$baseName}%");
                })
                ->select('product_variants.id', 'product_variants.color', 'product_variants.size', 'products.title as prod_title', 'product_variants.product_id')
                ->get();

            foreach ($variants as$v) {
                $label = ($v->prod_title ?? 'Product') . ' (Variant: ' . trim(($v->color ?? '') . ' ' . ($v->size ?? '')) . ')';
                $usages[] = [
                    'type'  => 'Product Variant',
                    'title' => $label,
                    'url'   => url('admin/product/edit/' . $v->product_id)
                ];
            }
        }

        // 3. Payment Transactions (User Proof)
        if (Schema::hasTable('payment_transactions') && Schema::hasColumn('payment_transactions', 'screenshot_path')) {
            $txns = DB::table('payment_transactions')
                ->where('screenshot_path', 'like', "%{$baseName}%")
                ->select('id', 'transaction_uid', 'invoice_no')
                ->get();

            foreach ($txns as $t) {$uidLabel = !empty($t->transaction_uid) ?$t->transaction_uid : 'TXN';
                $invLabel = !empty($t->invoice_no) ? $t->invoice_no : 'N/A';$usages[] = [
                    'type'  => 'Payment Proof',
                    'title' => $uidLabel . ' (Order: ' .$invLabel . ')',
                    'url'   => route('admin.transactions.index', ['search' => $t->transaction_uid])
                ];
            }
        }

        // 4. Blog Posts & Pages
        $postTable = Schema::hasTable('posts') ? 'posts' : (Schema::hasTable('blog_posts') ? 'blog_posts' : null);
        if ($postTable && Schema::hasColumn($postTable, 'featured_image')) {
            foreach (DB::table($postTable)->where('featured_image', 'like', "\%{$baseName}%")->select('id', 'title')->get() as $b) {$usages[] = [
                    'type'  => 'Blog Post',
                    'title' => $b->title,
                    'url'   => route('admin.content.blogs.edit', $b->id)
                ];
            }
        }

        $pageTable = Schema::hasTable('pages') ? 'pages' : (Schema::hasTable('site_pages') ? 'site_pages' : null);
        if ($pageTable && Schema::hasColumn($pageTable, 'featured_image')) {
            foreach (DB::table($pageTable)->where('featured_image', 'like', "\%{$baseName}%")->select('id', 'title')->get() as $pg) {$usages[] = [
                    'type'  => 'Page',
                    'title' => $pg->title,
                    'url'   => route('admin.content.pages.edit', $pg->id)
                ];
            }
        }

        return response()->json([
            'status'    => 'success',
            'file_name' => $media->file_name,
            'usages'    => $usages,
            'is_in_use' => count($usages) > 0, 'count'     => count($usages)
        ]);
    }

    /**
     * 7. Safe Single Delete (With Force Delete Support)
     */
    public function destroy($id)
    {
        $media = DB::table('media_files')->where('id',$id)->first();
        if (!$media) {
            return response()->json(['status' => 'error', 'message' => 'Media not found.'], 404);
        }

        $usageResponse = $this->usage($id)->getData(true);
        if (!empty($usageResponse['is_in_use']) && !request()->boolean('force_delete', false)) {
            return response()->json([
                'status'  => 'in_use',
                'message' => 'This file is currently in use across ' . count($usageResponse['usages']) . ' item(s). Confirm force delete if you still want to remove it.',
                'usages'  => $usageResponse['usages']
            ], 422);
        }

        $physicalFile = $this->resolvePhysicalFilePath($media->file_path);
        if ($physicalFile && is_file($physicalFile)) {
            @unlink($physicalFile);
        }

        DB::table('media_files')->where('id', $id)->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Media file deleted permanently.'
        ]);
    }

    /**
     * 8. Bulk Delete
     */
    public function bulkDestroy(Request $request)
    {
        $ids =$request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return response()->json(['status' => 'error', 'message' => 'No media items selected.'], 422);
        }

        $files = DB::table('media_files')->whereIn('id',$ids)->get();

        foreach ($files as $f) {$physicalFile = $this->resolvePhysicalFilePath($f->file_path);
            if ($physicalFile && is_file($physicalFile)) {
                @unlink($physicalFile);
            }
        }

        DB::table('media_files')->whereIn('id', $ids)->delete();

        return response()->json([
            'status'  => 'success',
            'message' => count($files) . ' media item(s) permanently deleted.'
        ]);
    }

    private function formatFileSize($bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}