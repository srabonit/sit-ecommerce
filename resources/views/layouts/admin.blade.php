@php
    $globalSettings = DB::table('settings')->pluck('value', 'key');

    // Site title & Tagline
    $siteTitle = $globalSettings['site_title'] ?? config('app.name', 'Shop Now BD');
    $siteTagline = $globalSettings['site_tagline'] ?? 'Admin Panel';
    
    // Favicon Helper
    $fav = $globalSettings['site_icon'] ?? null;
    $finalFav = null;
    if (!empty($fav)) {
        if (filter_var($fav, FILTER_VALIDATE_URL)) {
            $finalFav = $fav;
        } elseif (file_exists(public_path($fav))) {
            $finalFav = asset($fav);
        } elseif (file_exists(public_path('uploads/' . ltrim($fav, '/')))) {
            $finalFav = asset('uploads/' . ltrim($fav, '/'));
        } else {
            $finalFav = asset(ltrim(str_replace('storage/', '', $fav), '/'));
        }
    }
@endphp
<!DOCTYPE html>
<html lang="en" id="admin-html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title id="admin-page-title">@yield('title', 'Admin Panel - ' . $siteTitle)</title>
    
    @if(!empty($finalFav))
        <link rel="icon" href="{{ $finalFav }}" type="image/x-icon">
    @endif

    <!-- Enterprise Typography Stack: Plus Jakarta Sans (English) + Hind Siliguri (Bengali 300 & 700 Safe Glyphs) + Devanagari -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;700&family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --app-font-family: 'Plus Jakarta Sans', 'Hind Siliguri', 'Noto Sans Devanagari', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --admin-bg: #f8fafc;
            --admin-surface: #ffffff;
            --admin-primary: #2563eb;
            --admin-primary-hover: #1d4ed8;
            --admin-primary-light: #eff6ff;
            --admin-primary-dark: #1e3a8a;
            --admin-border-color: #e2e8f0;
            --admin-border-subtle: #f1f5f9;
            --admin-text-main: #0f172a;
            --admin-text-muted: #64748b;
            --admin-text-sub: #94a3b8;
            --admin-shadow-xs: 0 1px 2px 0 rgba(15, 23, 42, 0.04);
            --admin-shadow-sm: 0 1px 3px 0 rgba(15, 23, 42, 0.06), 0 1px 2px -1px rgba(15, 23, 42, 0.04);
            --admin-shadow-md: 0 4px 6px -1px rgba(15, 23, 42, 0.07), 0 2px 4px -2px rgba(15, 23, 42, 0.05);
            --admin-shadow-lg: 0 10px 15px -3px rgba(15, 23, 42, 0.08), 0 4px 6px -4px rgba(15, 23, 42, 0.04);
            --admin-radius-sm: 8px;
            --admin-radius-md: 12px;
            --admin-radius-lg: 16px;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            background-color: var(--admin-bg);
            font-family: var(--app-font-family);
            color: var(--admin-text-main);
            margin: 0;
            padding: 0;
            line-height: 1.55;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            overflow-x: hidden;
        }

        /* Safe Bengali digit rendering to prevent broken glyph '১' */
        .bn-digit, 
        .digit-bold, 
        .stat-value, 
        .table td.amount, 
        .table td.number {
            font-weight: 700 !important;
        }
        
        .digit-light {
            font-weight: 300 !important;
        }

        .admin-layout-wrapper {
            display: flex;
            min-height: 100vh;
            position: relative;
            background-color: var(--admin-bg);
            width: 100%;
        }

        /* Sidebar Styling */
        .admin-sidebar {
            width: 260px;
            background: var(--admin-surface);
            padding: 24px 16px;
            border-right: 1px solid var(--admin-border-color);
            flex-shrink: 0;
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.28s ease;
            z-index: 1090;
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: sticky;
            top: 0;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .admin-sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .admin-sidebar::-webkit-scrollbar-thumb {
            background-color: #e2e8f0;
            border-radius: 4px;
        }

        .brand-icon-sq {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-primary-dark) 100%);
            border-radius: var(--admin-radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.22);
            flex-shrink: 0;
        }

        .menu-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            color: #475569;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            transition: background-color 0.15s ease, color 0.15s ease, transform 0.15s ease;
            margin-bottom: 3px;
            min-height: 42px;
        }

        .menu-link:hover {
            background-color: #f1f5f9;
            color: var(--admin-primary);
            transform: translateX(2px);
        }

        .menu-link.active {
            background-color: var(--admin-primary-light);
            color: var(--admin-primary);
            font-weight: 700;
        }

        .menu-link.logout-link:hover {
            background-color: #fef2f2;
            color: #ef4444;
        }

        /* Viewport Container */
        .admin-main-viewport {
            flex-grow: 1;
            padding: 24px 32px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            min-width: 0;
            min-height: 100vh;
        }

        .admin-content-container {
            flex: 1 0 auto;
        }

        /* Header Components */
        .header-search-bar {
            background: var(--admin-surface);
            border: 1px solid var(--admin-border-color);
            border-radius: 9999px;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            width: 340px;
            box-shadow: var(--admin-shadow-xs);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .header-search-bar:focus-within {
            border-color: var(--admin-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

.alert-dismissible .btn-close {
    position: absolute;
    top: 0;
    right: 0;
    z-index: 2;
    padding: -1.75rem 1rem;
    padding: 12px !important;
}


        .search-input {
            border: none;
            outline: none;
            font-size: 13px;
            font-weight: 500;
            width: 100%;
            background: transparent;
            color: var(--admin-text-main);
        }

        .search-input::placeholder {
            color: var(--admin-text-sub);
        }

        .search-shortcut-badge {
            background: #f1f5f9;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 10px;
            color: var(--admin-text-muted);
            font-weight: 700;
            letter-spacing: 0.5px;
            border: 1px solid #e2e8f0;
        }

        .header-action-btn {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            border: 1px solid var(--admin-border-color);
            background: var(--admin-surface);
            color: var(--admin-text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: var(--admin-shadow-xs);
        }

        .header-action-btn:hover {
            background-color: #f8fafc;
            color: var(--admin-primary);
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }

        .profile-avatar-circle {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--admin-primary-dark) 0%, var(--admin-primary) 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(30, 58, 138, 0.22);
            flex-shrink: 0;
        }

        /* Mobile Backdrop Overlay */
        .admin-sidebar-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 1085;
            display: none;
            opacity: 0;
            transition: opacity 0.25s ease-in-out;
        }

        .admin-sidebar-backdrop.active {
            display: block;
            opacity: 1;
        }

        /* Responsive Breakpoints */
        @media (max-width: 991.98px) {
            .admin-sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                transform: translateX(-100%);
                width: 280px;
                box-shadow: var(--admin-shadow-lg);
            }
            
            .admin-sidebar.mobile-sidebar-open {
                transform: translateX(0);
            }

            .admin-main-viewport {
                padding: 20px !important;
            }
        }

        @media (max-width: 767.98px) {
            .admin-main-viewport {
                padding: 14px 12px !important;
            }

            .header-search-bar {
                width: 100% !important;
            }

            .header-action-btn, 
            .profile-avatar-circle {
                width: 38px;
                height: 38px;
            }
        }
    </style>
</head>
<body id="admin-body-root">

<div class="admin-sidebar-backdrop" id="adminSidebarBackdrop" onclick="closeAdminSidebar()"></div>

<div class="admin-layout-wrapper" id="admin-page-wrapper">
    @include('admin.partials.sidebar')

    <main class="admin-main-viewport" id="admin-content-viewport">
        @include('admin.partials.header')

        <div class="admin-content-container py-2" id="admin-injected-content">
            @yield('admin_content')
        </div>

        @include('admin.partials.footer')
    </main>
</div>

<!-- Reusable WordPress-Style Global Media Picker Modal -->
@include('admin.partials.media_picker_modal')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function toggleAdminSidebar() {
        const sidebar = document.getElementById('admin-main-sidebar-container');
        const backdrop = document.getElementById('adminSidebarBackdrop');
        if (sidebar) {
            sidebar.classList.toggle('mobile-sidebar-open');
            if (backdrop) {
                backdrop.classList.toggle('active');
            }
        }
    }

    function closeAdminSidebar() {
        const sidebar = document.getElementById('admin-main-sidebar-container');
        const backdrop = document.getElementById('adminSidebarBackdrop');
        if (sidebar) {
            sidebar.classList.remove('mobile-sidebar-open');
        }
        if (backdrop) {
            backdrop.classList.remove('active');
        }
    }
</script>
</body>
</html>