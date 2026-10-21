<?php

use App\Services\ImagePathService;
use Carbon\Carbon;

if (!function_exists('show_image')) {
    function show_image($path, $fallback = 'https://placehold.co/300x300?text=No+Image')
    {
        if (empty($path)) {
            return $fallback;
        }

        $resolvedUrl = ImagePathService::resolveUrl($path, '');
        if (!empty($resolvedUrl)) {
            return $resolvedUrl;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $clean = ltrim(str_replace(['storage/', 'public/'], '', $path), '/');
        $publicRoot = is_dir(base_path('../public_html')) ? base_path('../public_html') : public_path();

        if (file_exists($publicRoot . '/' . $clean)) {
            return asset($clean);
        }

        if (file_exists($publicRoot . '/uploads/' . $clean)) {
            return asset('uploads/' . $clean);
        }

        $withoutUploads = preg_replace('#^uploads/#', '', $clean);
        if (file_exists($publicRoot . '/uploads/' . $withoutUploads)) {
            return asset('uploads/' . $withoutUploads);
        }
        if (file_exists($publicRoot . '/' . $withoutUploads)) {
            return asset($withoutUploads);
        }

        if (file_exists(storage_path('app/public/' . $clean))) {
            $destDir = $publicRoot . '/uploads/' . dirname($clean);
            if (!file_exists($destDir)) {
                @mkdir($destDir, 0777, true);
            }
            @copy(storage_path('app/public/' . $clean), $publicRoot . '/uploads/' . $clean);
            return asset('uploads/' . $clean);
        }

        if (str_starts_with($clean, 'uploads/')) {
            return asset($clean);
        }

        return $fallback;
    }
}

/**
 * Universal Dynamic Timezone Date Formatter
 * Theme / General settings-e select kora timezone onujayi shothik date & time return korbe.
 */
if (!function_exists('format_datetime')) {
    function format_datetime($date, $format = 'd M, Y h:i A')
    {
        if (empty($date)) {
            return '—';
        }

        $appTimezone = config('app.timezone', 'Asia/Dhaka');

        try {
            if ($date instanceof Carbon) {
                return $date->copy()->setTimezone($appTimezone)->format($format);
            }

            return Carbon::parse($date)->setTimezone($appTimezone)->format($format);
        } catch (\Throwable $e) {
            return date($format, strtotime($date));
        }
    }
}