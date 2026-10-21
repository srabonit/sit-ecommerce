<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImagePathService
{
    /**
     * Get the primary active public document root path (Supports cPanel DOCUMENT_ROOT, public_html, and local public)
     */
    public static function resolvePublicRoot(): string
    {
        if (!empty($_SERVER['DOCUMENT_ROOT']) && is_dir($_SERVER['DOCUMENT_ROOT'])) {
            return rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
        }

        if (is_dir(base_path('../public_html'))) {
            return rtrim(base_path('../public_html'), '/\\');
        }

        return rtrim(public_path(), '/\\');
    }

    /**
     * Get all candidate public roots on the server so files are found/saved everywhere
     */
    public static function getAllPublicRoots(): array
    {
        $roots = [];

        if (!empty($_SERVER['DOCUMENT_ROOT']) && is_dir($_SERVER['DOCUMENT_ROOT'])) {
            $roots[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
        }

        $roots[] = rtrim(public_path(), '/\\');

        if (is_dir(base_path('../public_html'))) {
            $roots[] = rtrim(base_path('../public_html'), '/\\');
        }

        return array_values(array_unique(array_filter($roots)));
    }

    /**
     * Universal Image URL Resolver for Frontend and Admin Panels (XAMPP + cPanel)
     * Handles null defaults safely without throwing PHP 8+ TypeErrors
     */
    public static function resolveUrl(?string $path, ?string $default = ''): string
    {
        $fallback = $default ?? '';

        if (empty($path) || $path === 'undefined' || $path === 'null') {
            return $fallback;
        }

        $path = trim($path);

        // If already a full URL
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $parsedHost = parse_url($path, PHP_URL_HOST);
            $currentHost = request()->getHost();
            // If it's an external URL ( placehold.co or CDN), return as-is
            if ($parsedHost && !in_array($parsedHost, [$currentHost, '127.0.0.1', 'localhost'], true)) {
                return $path;
            }
            $parsedPath = parse_url($path, PHP_URL_PATH);
            if (!empty($parsedPath)) {
                $path = $parsedPath;
            } else {
                return $path;
            }
        }

        // Clean redundant prefixes while keeping uploads/ or storage/ intact
        $clean = ltrim(str_replace('\\', '/', $path), '/');
        $clean = preg_replace('#^(public_html/|public/)#i', '', $clean);
        $clean = str_replace(['storage/storage/', 'uploads/uploads/'], ['storage/', 'uploads/'], $clean);

        $withoutStorage = preg_replace('#^storage/#i', '', $clean);
        $withoutUploads = preg_replace('#^uploads/#i', '', $withoutStorage);

        $roots = self::getAllPublicRoots();

        // 1. Check exact path or uploads/ path across all public roots
        foreach ($roots as $root) {
            if (is_file($root . '/' . $clean)) {
                return asset($clean);
            }
            if (is_file($root . '/uploads/' . $withoutUploads)) {
                return asset('uploads/' . $withoutUploads);
            }
            if (is_file($root . '/storage/' . $withoutStorage)) {
                return asset('storage/' . $withoutStorage);
            }
            if (is_file($root . '/' . $withoutUploads)) {
                return asset($withoutUploads);
            }
        }

        // 2. Check storage/app/public and auto-copy to active public uploads/ if symlink is missing on cPanel
        $storageCandidates = [
            storage_path('app/public/' . $withoutStorage),
            storage_path('app/public/' . $withoutUploads),
            storage_path('app/public/uploads/' . $withoutUploads),
        ];

        foreach ($storageCandidates as $storageFile) {
            if (is_file($storageFile)) {
                $primaryRoot = self::resolvePublicRoot();
                $destFile = $primaryRoot . '/uploads/' . $withoutUploads;
                $destDir  = dirname($destFile);
                if (!is_dir($destDir)) {
                    @mkdir($destDir, 0777, true);
                }
                @copy($storageFile, $destFile);
                return asset('uploads/' . $withoutUploads);
            }
        }

        // 3. Safe fallback preserving uploads/ prefix
        if (str_starts_with($clean, 'uploads/') || str_starts_with($clean, 'storage/')) {
            return asset($clean);
        }

        return asset('uploads/' . $withoutUploads);
    }

    /**
     * Universal File Upload Handler with Duplicate Prevention ((1), (2) naming)
     * Mirrors across all public roots so both XAMPP and cPanel work 100%
     */
    public static function upload($file, string $subFolder = 'products', bool $isUserUpload = false): string
    {
        $folderPrefix = $isUserUpload ? 'user_uploads/' . trim($subFolder, '/') : trim($subFolder, '/');
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName     = Str::slug($originalName) ?: 'media';
        $extension    = strtolower($file->getClientOriginalExtension());

        $filename    = $safeName . '.' . $extension;
        $relativeDir = 'uploads/' . $folderPrefix;

        $primaryRoot = self::resolvePublicRoot();
        $targetDir   = $primaryRoot . '/' . $relativeDir;

        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }

        $counter = 1;
        while (file_exists($targetDir . '/' . $filename)) {
            $filename = $safeName . ' (' . $counter . ').' . $extension;
            $counter++;
        }

        $file->move($targetDir, $filename);
        $primaryFullPath = $targetDir . '/' . $filename;

        // Mirror to all other public roots ( public_path and public_html)
        foreach (self::getAllPublicRoots() as $root) {
            if ($root !== $primaryRoot) {
                $mirrorDir = $root . '/' . $relativeDir;
                if (!is_dir($mirrorDir)) {
                    @mkdir($mirrorDir, 0777, true);
                }
                @copy($primaryFullPath, $mirrorFile ?? ($mirrorDir . '/' . $filename));
            }
        }

        return $relativeDir . '/' . $filename;
    }
}