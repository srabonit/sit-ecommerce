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

class SettingController extends Controller
{
    /**
     * ডাইনামিক পাবলিক রুট ডিটেক্টর
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
     * Universal Upload Helper for Settings across all public roots & auto-register in Media Library
     */
    protected function saveSettingFile($file, $subFolder): string
    {
        $originalName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
        $cleanBase = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'setting';
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
                    $mediaInsert['source_module'] = 'setting';
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
     * Universal Delete Helper for Settings
     */
    protected function deleteSettingFile($relativePath)
    {
        if (empty($relativePath) || filter_var($relativePath, FILTER_VALIDATE_URL)) return;

        $clean = ltrim($relativePath, '/');

        // Media Library theke select kora file onno jaygay use thakle disk theke delete hobe na
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

    public function general()
    {
        $settings = DB::table('settings')->pluck('value', 'key')->toArray();
        return view('admin.settings.general', compact('settings'));
    }

    public function updateGeneral(Request $request)
    {
        $data = $request->except(['_token']);

        // Cross বাটন প্রেস করে ডিলিট করা হলে
        if ($request->input('remove_header_logo') == '1') {
            $oldLogo = DB::table('settings')->where('key', 'header_logo')->value('value');
            $this->deleteSettingFile($oldLogo);
            $data['header_logo'] = '';
        }
        unset($data['remove_header_logo']);

        if ($request->input('remove_site_icon') == '1') {
            $oldIcon = DB::table('settings')->where('key', 'site_icon')->value('value');
            $this->deleteSettingFile($oldIcon);
            $data['site_icon'] = '';
        }
        unset($data['remove_site_icon']);

        // ফাইল আপলোড ও মিডিয়া লাইব্রেরি হ্যান্ডলিং (site_icon & header_logo)
        foreach (['site_icon', 'header_logo'] as $fileKey) {
            $mediaKey = $fileKey . '_media';

            if ($request->hasFile($fileKey)) {
                $oldFile = DB::table('settings')->where('key', $fileKey)->value('value');
                $this->deleteSettingFile($oldFile);

                $path = $this->saveSettingFile($request->file($fileKey), 'settings');
                $data[$fileKey] = $path;
            } elseif ($request->filled($mediaKey)) {
                $normalizedMedia = $this->normalizeMediaInputPath($request->input($mediaKey));
                if (!empty($normalizedMedia)) {
                    $oldFile = DB::table('settings')->where('key', $fileKey)->value('value');
                    if ($oldFile && $oldFile !== $normalizedMedia) {
                        $this->deleteSettingFile($oldFile);
                    }
                    $data[$fileKey] = $normalizedMedia;
                }
            } else {
                if (!isset($data[$fileKey])) {
                    unset($data[$fileKey]);
                }
            }

            unset($data[$mediaKey]);
        }

        foreach ($data as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value ?? '', 'updated_at' => now()]
            );
        }

        Cache::forget('global_settings_cache');

        return back()->with('success', 'General settings updated successfully.');
    }
}