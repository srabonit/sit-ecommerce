@extends('layouts.app')

@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার
    static $userDashCache = null;
    if ($userDashCache === null) {
        $userDashCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $dashSettings = $userDashCache ?? [];

    // ড্যাশবোর্ড ডাইনামিক থিম কালার ভ্যারিয়েবল
    $dashSiteBg       = $dashSettings['color_site_bg'] ?? '#f8fafc';
    $dashCardBg       = $dashSettings['color_auth_card_bg'] ?? '#ffffff';
    $dashCardBorder   = $dashSettings['color_auth_card_border'] ?? '#e2e8f0';
    $dashHeadingColor = $dashSettings['color_page_title'] ?? '#0f172a';
    $dashTextColor    = $dashSettings['color_auth_text'] ?? '#64748b';
    $dashLinkColor    = $dashSettings['color_page_sidebar_text'] ?? '#334155';
    
    $dashPrimaryColor = $dashSettings['color_common_btn_bg'] ?? ($dashSettings['color_hdr_menu_hover_text'] ?? '#2563eb');
    $dashPrimaryHover = $dashSettings['color_auth_btn_hover'] ?? '#1d4ed8';
    $dashActiveBg     = $dashSettings['color_hdr_menu_active_bg'] ?? '#eff6ff';
    $dashHoverBg      = $dashSettings['color_hdr_menu_hover_bg'] ?? '#f1f5f9';
@endphp

@section('content')
<style>
    :root {
        --dash-site-bg: {{ $dashSiteBg }};
        --dash-card-bg: {{ $dashCardBg }};
        --dash-card-border: {{ $dashCardBorder }};
        --dash-heading: {{ $dashHeadingColor }};
        --dash-text: {{ $dashTextColor }};
        --dash-link: {{ $dashLinkColor }};
        --dash-primary: {{ $dashPrimaryColor }};
        --dash-primary-hover: {{ $dashPrimaryHover }};
        --dash-active-bg: {{ $dashActiveBg }};
        --dash-hover-bg: {{ $dashHoverBg }};
    }

    .account-dashboard-bg {
        background-color: var(--dash-site-bg) !important;
        min-height: 85vh;
        font-family: inherit;
    }

    /* Universal Strict Link Text Decoration */
    .account-dashboard-bg a,
    .account-dashboard-bg a:hover,
    .user-nav-card a,
    .mobile-dashboard-header a,
    .mobile-menu-scroll-row a,
    .user-menu-link,
    .mob-pill-link {
        text-decoration: none !important;
    }

    /* Desktop Card Styling */
    .user-nav-card {
        background: var(--dash-card-bg) !important;
        border: 1px solid var(--dash-card-border) !important;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
        overflow: hidden;
    }

    .nav-category-title {
        font-size: 10.5px;
        letter-spacing: 0.07em;
        font-weight: 700;
        color: var(--dash-text) !important;
        padding: 14px 16px 6px;
        text-transform: uppercase;
        opacity: 0.8;
    }

    .user-menu-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        font-size: 13.5px;
        font-weight: 600;
        color: var(--dash-link) !important;
        border-radius: 10px;
        margin: 2px 8px;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        min-height: 42px;
    }

    .user-menu-link:hover {
        background-color: var(--dash-hover-bg) !important;
        color: var(--dash-primary) !important;
        transform: translateX(4px);
    }

    .user-menu-link.active {
        background-color: var(--dash-active-bg) !important;
        color: var(--dash-primary) !important;
        font-weight: 700;
        box-shadow: inset 3px 0 0 0 var(--dash-primary);
    }

    .user-menu-link i {
        font-size: 15px;
        width: 22px;
        text-align: center;
        color: var(--dash-text);
        transition: color 0.2s ease, transform 0.2s ease;
    }

    .user-menu-link:hover i,
    .user-menu-link.active i {
        color: var(--dash-primary) !important;
        transform: scale(1.1);
    }

    .logout-btn-link {
        color: #ef4444 !important;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .logout-btn-link i {
        color: #ef4444 !important;
    }

    .logout-btn-link:hover {
        background-color: #fef2f2 !important;
        color: #dc2626 !important;
        transform: translateX(4px);
    }

    .logout-btn-link:hover i {
        color: #dc2626 !important;
    }

    .dashboard-avatar-box {
        background: linear-gradient(135deg, var(--dash-primary) 0%, var(--dash-primary-hover) 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    }

    /* Bengali digit protection */
    .bn-digit, 
    .user-stat-num {
        font-weight: 700 !important;
    }

    /* ========================================================= */
    /* ULTRA RESPONSIVE MOBILE ARCHITECTURE (320px - 991px)       */
    /* ========================================================= */
    @media (max-width: 991.98px) {
        .account-dashboard-bg {
            padding-top: 10px !important;
        }

        .user-nav-card.desktop-sidebar {
            display: none !important;
        }

        /* User Profile Strip Box */
        .mobile-dashboard-header {
            background: var(--dash-card-bg) !important;
            border: 1px solid var(--dash-card-border) !important;
            border-radius: 14px;
            padding: 12px 14px;
            margin-bottom: 12px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .mob-avatar {
            width: 42px !important;
            height: 42px !important;
            font-size: 16px !important;
            border: 2px solid #ffffff;
            flex-shrink: 0;
        }

        .mob-user-info {
            min-width: 0;
            flex: 1;
        }

        .mob-user-name {
            font-size: 14px !important;
            font-weight: 700;
            color: var(--dash-heading) !important;
            line-height: 1.3;
            margin-bottom: 2px;
        }

        .mob-user-phone {
            font-size: 11.5px !important;
            color: var(--dash-text) !important;
            line-height: 1.2;
        }

        .mob-signout-btn {
            font-size: 11.5px !important;
            padding: 5px 12px !important;
            border-radius: 50px !important;
            font-weight: 600;
            white-space: nowrap;
            flex-shrink: 0;
            border: 1px solid #fee2e2 !important;
            color: #ef4444 !important;
            background: #ffffff !important;
            transition: all 0.2s ease;
        }

        .mob-signout-btn:hover {
            background: #fef2f2 !important;
            color: #dc2626 !important;
            border-color: #fca5a5 !important;
        }

        /* Mobile Scrollable Pill Navigation */
        .mobile-menu-scroll-row {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            white-space: nowrap;
            padding: 4px 2px 10px 2px;
            margin-bottom: 14px;
            scrollbar-width: none;
            -ms-overflow-style: none;
            -webkit-overflow-scrolling: touch;
        }

        .mobile-menu-scroll-row::-webkit-scrollbar {
            display: none;
        }

        .mob-pill-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 16px;
            background: var(--dash-card-bg) !important;
            border: 1px solid var(--dash-card-border) !important;
            border-radius: 9999px;
            color: var(--dash-link) !important;
            font-size: 12.5px;
            font-weight: 600;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
            flex-shrink: 0;
            min-height: 38px;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .mob-pill-link:hover {
            background-color: var(--dash-hover-bg) !important;
            color: var(--dash-primary) !important;
            border-color: var(--dash-primary) !important;
        }

        .mob-pill-link.active {
            background: var(--dash-primary) !important;
            color: #ffffff !important;
            border-color: var(--dash-primary) !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.28);
        }

        .mob-pill-link.active i {
            color: #ffffff !important;
        }

        .mob-pill-link i {
            font-size: 13.5px;
            transition: color 0.2s ease;
        }
    }

    /* Small Screen Optimization (iPhone SE, 360px Android) */
    @media (max-width: 575.98px) {
        .mobile-dashboard-header {
            padding: 10px 12px;
            gap: 8px;
        }
        
        .mob-avatar {
            width: 38px !important;
            height: 38px !important;
            font-size: 15px !important;
        }

        .mob-user-name {
            font-size: 13.5px !important;
        }

        .mob-signout-btn {
            padding: 4px 10px !important;
            font-size: 11px !important;
        }

        .mob-pill-link {
            padding: 7px 14px;
            font-size: 12px;
            min-height: 36px;
        }
    }

    /* Injected Child Table & Card Auto-Responsive Helper */
    .account-dashboard-bg .card {
        border: 1px solid var(--dash-card-border) !important;
        background-color: var(--dash-card-bg) !important;
        border-radius: 14px !important;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03) !important;
    }

    .account-dashboard-bg .table-responsive {
        border-radius: 10px;
        -webkit-overflow-scrolling: touch;
        border: 1px solid var(--dash-card-border);
    }

    .account-dashboard-bg table {
        margin-bottom: 0 !important;
        white-space: nowrap;
    }
</style>

<div class="account-dashboard-bg py-3 py-md-4">
    <div class="container px-2 px-md-3">
        
        {{-- Flash Feedback Notifications --}}
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-3 mb-3 py-2.5 px-3 alert-dismissible fade show d-flex align-items-center">
                <i class="fas fa-check-circle me-2 fs-5"></i> 
                <span class="small fw-semibold">{{ session('success') }}</span>
                <button type="button" class="btn-close shadow-none p-2.5" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-3 py-2.5 px-3 alert-dismissible fade show d-flex align-items-center">
                <i class="fas fa-exclamation-circle me-2 fs-5"></i> 
                <span class="small fw-semibold">{{ $errors->first() }}</span>
                <button type="button" class="btn-close shadow-none p-2.5" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Mobile Profile Strip (iPhone SE & Small Mobile Screen Optimized) --}}
        <div class="d-lg-none">
            <div class="mobile-dashboard-header">
                <div class="d-flex align-items-center gap-2.5 min-w-0 mob-user-info">
                    <div class="dashboard-avatar-box mob-avatar rounded-circle d-flex align-items-center justify-content-center fw-bold">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="overflow-hidden pe-1">
                        <h6 class="mob-user-name text-truncate mb-0">{{ auth()->user()->name ?? 'User' }}</h6>
                        <small class="mob-user-phone text-truncate d-block bn-digit">{{ auth()->user()->phone ?? auth()->user()->email }}</small>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="m-0 flex-shrink-0">
                    @csrf
                    <button type="submit" class="btn btn-sm mob-signout-btn d-flex align-items-center gap-1.5">
                        <i class="fas fa-sign-out-alt"></i> <span>Sign Out</span>
                    </button>
                </form>
            </div>

            {{-- Smooth Touch Scrollable Menu Pills --}}
            <div class="mobile-menu-scroll-row">
                <a href="{{ route('user.dashboard') }}" class="mob-pill-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-th-large"></i> <span>Overview</span>
                </a>
                <a href="{{ route('user.orders') }}" class="mob-pill-link {{ request()->routeIs('user.orders*') ? 'active' : '' }}">
                    <i class="fas fa-box"></i> <span>Orders</span>
                </a>
                <a href="{{ route('user.wishlist') }}" class="mob-pill-link {{ request()->routeIs('user.wishlist') ? 'active' : '' }}">
                    <i class="fas fa-heart text-warning"></i> <span>Wishlist</span>
                </a>
                <a href="{{ route('user.track.order') }}" class="mob-pill-link {{ request()->routeIs('user.track.order') ? 'active' : '' }}">
                    <i class="fas fa-shipping-fast"></i> <span>Track Order</span>
                </a>
                <a href="{{ route('user.profile') }}" class="mob-pill-link {{ request()->routeIs('user.profile') ? 'active' : '' }}">
                    <i class="far fa-user"></i> <span>Profile</span>
                </a>
                <a href="{{ route('user.addresses') }}" class="mob-pill-link {{ request()->routeIs('user.addresses') ? 'active' : '' }}">
                    <i class="fas fa-map-marker-alt"></i> <span>Addresses</span>
                </a>
                <a href="{{ route('user.coupons') }}" class="mob-pill-link {{ request()->routeIs('user.coupons') ? 'active' : '' }}">
                    <i class="fas fa-ticket-alt"></i> <span>Vouchers</span>
                </a>
                <a href="{{ route('user.support') }}" class="mob-pill-link {{ request()->routeIs('user.support') ? 'active' : '' }}">
                    <i class="fas fa-headset"></i> <span>Support</span>
                </a>
            </div>
        </div>

        <div class="row g-3 g-lg-4">
            {{-- Desktop Navigation Sidebar --}}
            <div class="col-lg-3 d-none d-lg-block">
                <div class="user-nav-card desktop-sidebar p-2 mb-4">
                    
                    <div class="d-flex align-items-center gap-3 p-3 border-bottom mb-2" style="border-color: var(--dash-card-border) !important;">
                        <div class="dashboard-avatar-box rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5 flex-shrink-0" style="width: 48px; height: 48px; border: 2px solid #ffffff;">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold mb-0 text-truncate" style="font-size: 15px; color: var(--dash-heading);">{{ auth()->user()->name ?? 'User' }}</h6>
                            <small class="text-truncate d-block bn-digit" style="font-size: 12px; color: var(--dash-text);">{{ auth()->user()->email ?? auth()->user()->phone }}</small>
                        </div>
                    </div>

                    <div class="nav-category-title">Orders &amp; Purchases</div>
                    <a href="{{ route('user.dashboard') }}" class="user-menu-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-th-large"></i> <span>Dashboard Overview</span>
                    </a>
                    <a href="{{ route('user.orders') }}" class="user-menu-link {{ request()->routeIs('user.orders*') ? 'active' : '' }}">
                        <i class="fas fa-box"></i> <span>My Orders</span>
                    </a>
                    <a href="{{ route('user.track.order') }}" class="user-menu-link {{ request()->routeIs('user.track.order') ? 'active' : '' }}">
                        <i class="fas fa-shipping-fast"></i> <span>Track Order</span>
                    </a>

                    <div class="nav-category-title mt-2">Saved Items</div>
                    <a href="{{ route('user.wishlist') }}" class="user-menu-link {{ request()->routeIs('user.wishlist') ? 'active' : '' }}">
                        <i class="far fa-heart"></i> <span>Wishlist</span>
                    </a>

                    <div class="nav-category-title mt-2">Account Settings</div>
                    <a href="{{ route('user.profile') }}" class="user-menu-link {{ request()->routeIs('user.profile') ? 'active' : '' }}">
                        <i class="far fa-user"></i> <span>Personal Info</span>
                    </a>
                    <a href="{{ route('user.addresses') }}" class="user-menu-link {{ request()->routeIs('user.addresses') ? 'active' : '' }}">
                        <i class="fas fa-map-marker-alt"></i> <span>Manage Addresses</span>
                    </a>

                    <div class="nav-category-title mt-2">Finances &amp; Rewards</div>
                    <a href="{{ route('user.coupons') }}" class="user-menu-link {{ request()->routeIs('user.coupons') ? 'active' : '' }}">
                        <i class="fas fa-ticket-alt"></i> <span>Coupons &amp; Vouchers</span>
                    </a>

                    <div class="nav-category-title mt-2">Support &amp; Safety</div>
                    <a href="{{ route('user.support') }}" class="user-menu-link {{ request()->routeIs('user.support') ? 'active' : '' }}">
                        <i class="fas fa-headset"></i> <span>Customer Support</span>
                    </a>

                    <div class="border-top mt-3 pt-2" style="border-color: var(--dash-card-border) !important;">
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="user-menu-link logout-btn-link w-100 border-0 bg-transparent text-start">
                                <i class="fas fa-sign-out-alt"></i> <span>Sign Out</span>
                            </button>
                        </form>
                    </div>

                </div>
            </div>

            {{-- Main Injected Content (Orders, Overview, Profile etc.) --}}
            <div class="col-lg-9 col-12">
                @yield('dashboard_content')
            </div>

        </div>
    </div>
</div>
@endsection