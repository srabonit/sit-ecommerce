if (!function_exists('theme_asset')) {
    function theme_asset($path, $fallback = 'https://placehold.co/150x150?text=No+Img') {
        if (empty($path)) return $fallback;
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;

        $clean = ltrim(str_replace(['public/', 'storage/'], '', $path), '/');

        if (file_exists(public_path('uploads/' . $clean))) {
            return asset('uploads/' . $clean);
        }
        if (file_exists(public_path($clean))) {
            return asset($clean);
        }
        if (file_exists(storage_path('app/public/' . $clean))) {
            $destDir = public_path('uploads/' . dirname($clean));
            if (!file_exists($destDir)) {
                @mkdir($destDir, 0777, true);
            }
            @copy(storage_path('app/public/' . $clean), public_path('uploads/' . $clean));
            return asset('uploads/' . $clean);
        }
        if (str_starts_with(ltrim($path, '/'), 'uploads/')) {
            return asset(ltrim($path, '/'));
        }
        return asset($clean);
    }
}