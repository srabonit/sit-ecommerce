<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImageThumbnailController extends Controller
{
    /**
     * লোকালহোস্ট এবং লাইভ সার্ভারের (cPanel/VPS) পাথ নিখুঁতভাবে শনাক্তকারী হেল্পার
     */
    protected function resolvePhysicalPath(string $rawPath): ?string
    {
        $clean = ltrim(str_replace(['..', "\0", '\\'], ['', '', '/'], urldecode($rawPath)), '/');
        $clean = preg_replace('#^(public_html/|public/|storage/storage/|uploads/uploads/)#i', '', $clean);

        if ($clean === '' || str_contains($clean, '..')) {
            return null;
        }

        $withoutStorage = preg_replace('#^storage/#i', '', $clean);
        $withoutUploads = preg_replace('#^uploads/#i', '', $withoutStorage);

        $docRoot = (!empty($_SERVER['DOCUMENT_ROOT']) && is_dir($_SERVER['DOCUMENT_ROOT']))
            ? rtrim($_SERVER['DOCUMENT_ROOT'], '/')
            : null;

        $cpanelRoot = is_dir(base_path('../public_html'))
            ? rtrim(base_path('../public_html'), '/')
            : null;

        // লোকাল এবং লাইভ সার্ভারের সব সম্ভাব্য লোকেশন স্ক্যান
        $candidates = array_filter([
            $docRoot ? $docRoot . '/' . $clean : null,
            $docRoot ? $docRoot . '/uploads/' . $withoutUploads : null,
            $docRoot ? $docRoot . '/storage/' . $withoutStorage : null,
            $cpanelRoot ? $cpanelRoot . '/' . $clean : null,
            $cpanelRoot ? $cpanelRoot . '/uploads/' . $withoutUploads : null,
            $cpanelRoot ? $cpanelRoot . '/storage/' . $withoutStorage : null,
            public_path($clean),
            public_path('uploads/' . $withoutUploads),
            public_path('storage/' . $withoutStorage),
            storage_path('app/public/' . $withoutStorage),
            storage_path('app/public/' . $withoutUploads),
            storage_path('app/public/uploads/' . $withoutUploads),
            storage_path('app/' . $withoutStorage),
        ]);

        foreach ($candidates as $filePath) {
            if (is_file($filePath) && is_readable($filePath)) {
                return $filePath;
            }
        }

        return null;
    }

    /**
     * অন-দ্য-ফ্লাই ইমেজ রিসাইজিং ইঞ্জিন (JPG, JPEG, JPE, PNG, WEBP, GIF সাপোর্টেড)
     */
    public function thumb(Request $request, $path)
    {
        $fullPath = $this->resolvePhysicalPath($path);

        // ফাইল ডিস্কে পাওয়া না গেলে লাইটওয়েট এসভিজি প্লেসহোল্ডার রিটার্ন
        if (!$fullPath) {
            $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='150' height='150' viewBox='0 0 150 150'><rect fill='#f1f5f9' width='150' height='150'/><text fill='#94a3b8' font-family='sans-serif' font-size='12' dy='4' font-weight='bold' x='50%' y='50%' text-anchor='middle'>No Image</text></svg>";
            return response($svg, 200)->header('Content-Type', 'image/svg+xml');
        }

        $targetW = min(400, max(50, (int) $request->get('w', 150)));
        $targetH = min(400, max(50, (int) $request->get('h', 150)));
        $quality = 75;

        // ব্রাউজার ক্যাশ চেক (একই ছবি বারবার সার্ভার থেকে ডাউনলোড হওয়া বন্ধ রাখে)
        $lastModified = filemtime($fullPath);
        $etag = md5($fullPath . $lastModified . $targetW . $targetH);

        if ($request->header('If-None-Match') === $etag) {
            return response('', 304);
        }

        $imageInfo = @getimagesize($fullPath);
        if (!$imageInfo) {
            return response()->file($fullPath);
        }

        [$origW, $origH, $imageType] = $imageInfo;

        // সকল এক্সটেনশন (jpg, jpeg, jpe, png, webp, gif) মেমোরিতে লোড
        $src = match ($imageType) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($fullPath),
            IMAGETYPE_PNG  => @imagecreatefrompng($fullPath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($fullPath) : null,
            IMAGETYPE_GIF  => @imagecreatefromgif($fullPath),
            default        => null,
        };

        if (!$src) {
            return response()->file($fullPath);
        }

        // সেন্টার-ক্রপ অ্যাসপেক্ট রেশিও হিসাব
        $srcRatio  = $origW / $origH;
        $destRatio = $targetW / $targetH;

        if ($srcRatio > $destRatio) {
            $cropW = (int) ($origH * $destRatio);
            $cropH = $origH;
            $srcX  = (int) (($origW - $cropW) / 2);
            $srcY  = 0;
        } else {
            $cropW = $origW;
            $cropH = (int) ($origW / $destRatio);
            $srcX  = 0;
            $srcY  = (int) (($origH - $cropH) / 2);
        }

        $dest = imagecreatetruecolor($targetW, $targetH);

        // PNG / Transparent ছবির ব্যাকগ্রাউন্ড যাতে কালো না হয়ে যায়, পরিষ্কার সাদা ব্যাকগ্রাউন্ড দেওয়া
        $whiteBg = imagecolorallocate($dest, 255, 255, 255);
        imagefilledrectangle($dest, 0, 0, $targetW, $targetH, $whiteBg);

        imagecopyresampled($dest, $src, 0, 0, $srcX, $srcY, $targetW, $targetH, $cropW, $cropH);

        // মেমোরি থেকে দ্রুত স্ট্রিমিং (ডিস্কে কোনো ডুপ্লিকেট ফাইল তৈরি হবে না)
        ob_start();
        imagejpeg($dest, null, $quality);
        $imageData = ob_get_clean();

        imagedestroy($src);
        imagedestroy($dest);

        return response($imageData, 200, [
            'Content-Type'  => 'image/jpeg',
            'Cache-Control' => 'public, max-age=2592000, immutable', // ৩০ দিনের স্ট্রং ক্যাশ
            'ETag'          => $etag,
            'Last-Modified' => gmdate('D, d M Y H:i:s', $lastModified) . ' GMT',
        ]);
    }
}