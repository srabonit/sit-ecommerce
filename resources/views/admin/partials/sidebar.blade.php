@php
$u = auth()->user();
$globalSettings = \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key');

$siteTitle = $globalSettings['site_title'] ?? config('app.name', 'Shop Now BD');
$siteTagline = $globalSettings['site_tagline'] ?? 'BEST ONLINE SHOP IN BD';

// সুপার অ্যাডমিন ও অ্যাডমিন রোল নিরাপদ চেকার (মেথড না থাকলেও ক্র্যাশ করবে না)
$isSuperAdmin = $u ? (method_exists($u, 'isSuperAdmin') ? $u->isSuperAdmin() : in_array(strtolower((string) ($u->role ?? '')), ['superadmin', 'admin'])) : true;
$isAdminOrSuper = $u ? in_array(strtolower((string) ($u->role ?? '')), ['superadmin', 'admin']) : true;

// Favicon path processing
$dbSiteIcon = $globalSettings['site_icon'] ?? null;
$finalFavicon = null;
if (!empty($dbSiteIcon)) {
    if (filter_var($dbSiteIcon, FILTER_VALIDATE_URL)) {
        $finalFavicon = $dbSiteIcon;
    } else {
        $cleanIcon = ltrim(str_replace(['storage/', 'public/'], '', $dbSiteIcon), '/');
        if (file_exists(public_path($cleanIcon))) {
            $finalFavicon = asset($cleanIcon);
        } elseif (file_exists(public_path('uploads/' . $cleanIcon))) {
            $finalFavicon = asset('uploads/' . $cleanIcon);
        } else {
            $finalFavicon = asset($cleanIcon);
        }
    }
}

// Transaction Counters
$txnAlerts = class_exists(\App\Services\PaymentTransactionService::class)
    ? \App\Services\PaymentTransactionService::getUncheckedAlertsSummary()
    : ['total_unchecked_count' => 0, 'pending_checkout_count' => 0];

$uncheckedTxnCount = (int) ($txnAlerts['total_unchecked_count'] ?? 0);
$pendingCheckoutCount = (int) ($txnAlerts['pending_checkout_count'] ?? 0);

// Live Pending Review Counter
$pendingReviewCount = \Illuminate\Support\Facades\Schema::hasTable('product_reviews')
    ? (int) \Illuminate\Support\Facades\DB::table('product_reviews')->where('status', 'pending')->count()
    : 0;

// Live Active Coupon Counter
$activeCouponCount = \Illuminate\Support\Facades\Schema::hasTable('coupons')
    ? (int) \Illuminate\Support\Facades\DB::table('coupons')->where('is_active', 1)->count()
    : 0;

// Security Alert & Suspicious Orders Counter
$securityAlertCount = \Illuminate\Support\Facades\Schema::hasTable('security_alerts')
    ? (int) \Illuminate\Support\Facades\DB::table('security_alerts')->where('is_read', 0)->count()
    : 0;

$suspiciousOrderBadgeCount = (\Illuminate\Support\Facades\Schema::hasTable('orders') && \Illuminate\Support\Facades\Schema::hasColumn('orders', 'is_suspicious'))
    ? (int) \Illuminate\Support\Facades\DB::table('orders')->where('is_suspicious', 1)->where('security_review_status', 'under_review')->count()
    : 0;

// Procurement Pending PO Counter
$pendingPoCount = \Illuminate\Support\Facades\Schema::hasTable('purchase_orders')
    ? (int) \Illuminate\Support\Facades\DB::table('purchase_orders')->where('status', 'pending')->count()
    : 0;

// Payroll Pending Counter
$pendingPayrollCount = (\Illuminate\Support\Facades\Schema::hasTable('staff_payrolls') && \Illuminate\Support\Facades\Schema::hasColumn('staff_payrolls', 'status'))
    ? (int) \Illuminate\Support\Facades\DB::table('staff_payrolls')->where('status', 'pending')->count()
    : 0;

// Daily Register Closing Pending Audit Counter
$pendingClosingAuditCount = \Illuminate\Support\Facades\Schema::hasTable('branch_daily_closings')
    ? (int) \Illuminate\Support\Facades\DB::table('branch_daily_closings')->where('status', 'closed')->count()
    : 0;

// Safe Fail-Safe URLs
$couponIndexUrl = Route::has('admin.promo.coupons.index') ? route('admin.promo.coupons.index') : url('/admin/promo/coupons');
$couponCreateUrl = Route::has('admin.promo.coupons.create') ? route('admin.promo.coupons.create') : url('/admin/promo/coupons/create');
$voucherIndexUrl = Route::has('admin.promo.coupons.index') ? route('admin.promo.coupons.index', ['tab' => 'vouchers']) : url('/admin/promo/coupons?tab=vouchers');
$voucherCreateUrl = Route::has('admin.promo.coupons.vouchers.create') ? route('admin.promo.coupons.vouchers.create') : url('/admin/promo/coupons/vouchers/create');
$redemptionLogsUrl = Route::has('admin.promo.coupons.index') ? route('admin.promo.coupons.index', ['tab' => 'redemptions']) : url('/admin/promo/coupons?tab=redemptions');

$taxPeriodsUrl = Route::has('admin.tax.periods.index') ? route('admin.tax.periods.index') : url('/admin/tax/periods');
$taxSettingsUrl = Route::has('admin.tax.settings.index') ? route('admin.tax.settings.index') : url('/admin/tax/settings');
@endphp

<style>
    /* Sidebar Theme */
    .admin-dark-sidebar {
        --sidebar-bg: #ffffff;
        --sidebar-surface: #f8fafc;
        --sidebar-text: #475467;
        --sidebar-muted: #667085;
        --sidebar-heading: #101828;
        --sidebar-border: #eaecf0;
        --sidebar-hover: #f2f4f7;
        --sidebar-active: #fff7e6;
        --sidebar-accent: #d97706;
        --sidebar-accent-strong: #b45309;
        width: 260px;
        min-height: 100vh;
        display: flex;
        flex: 0 0 260px;
        flex-direction: column;
        color: var(--sidebar-text);
        background: var(--sidebar-bg);
        border-right: 1px solid var(--sidebar-border);
        font-size: 13px;
        user-select: none;
        transition: left .25s ease, transform .25s ease;
    }

    .admin-dark-sidebar .btn,
    .admin-dark-sidebar .nav-head-link,
    .admin-dark-sidebar .nav-sub-link,
    .admin-dark-sidebar .badge,
    .admin-dark-sidebar .admin-brand-favicon-circle,
    .admin-dark-sidebar .mobile-sidebar-close-btn,
    .admin-dark-sidebar .user-avatar-sq,
    .admin-dark-sidebar .media-badge-tag {
        border-radius: 5px !important;
    }

    .admin-sidebar-logo-area {
        min-height: 68px;
        padding: 13px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fff;
        border-bottom: 1px solid var(--sidebar-border);
        text-decoration: none;
    }

    .admin-brand-favicon-circle {
        width: 36px;
        height: 36px;
        min-width: 36px;
        min-height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        color: var(--sidebar-accent-strong);
        background: var(--sidebar-active);
        border: 1px solid #f3e2bd;
        transition: background .15s ease, border-color .15s ease;
    }

    .admin-brand-favicon-circle:hover {
        background: #ffefc7;
        border-color: #e9c77e;
    }

    .admin-brand-favicon-circle img {
        width: 100%;
        height: 100%;
        padding: 2px;
        object-fit: contain;
    }

    .admin-brand-favicon-circle.fallback-avatar {
        color: #fff;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        font-size: 15px;
    }

    .mobile-sidebar-close-btn {
        width: 32px;
        height: 32px;
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        color: #667085;
        background: #f2f4f7;
        border: 1px solid var(--sidebar-border);
        cursor: pointer;
        transition: .15s ease;
    }

    .mobile-sidebar-close-btn:hover {
        color: #fff;
        background: #dc3545;
        border-color: #dc3545;
    }

    .admin-nav-list {
        flex: 1 1 auto;
        min-height: 0;
        padding: 12px 10px 24px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #d0d5dd transparent;
    }

    .admin-nav-list::-webkit-scrollbar { width: 4px; }
    .admin-nav-list::-webkit-scrollbar-thumb {
        background: #d0d5dd;
        border-radius: 4px;
    }

    .admin-dark-sidebar .nav-head-link {
        min-height: 38px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 3px;
        padding: 8px 11px;
        color: var(--sidebar-text);
        background: transparent;
        border: 0;
        text-decoration: none;
        font-size: 12.5px;
        font-weight: 500;
        line-height: 1.4;
        cursor: pointer;
        transition: color .15s ease, background .15s ease;
    }

    .admin-dark-sidebar .nav-head-link > span:first-child {
        min-width: 0;
    }

    .admin-dark-sidebar .nav-head-link i,
    .admin-dark-sidebar .nav-sub-link i {
        color: currentColor !important;
        transition: color .15s ease;
    }

    .admin-dark-sidebar .nav-head-link:hover,
    .admin-dark-sidebar .nav-head-link.active {
        color: var(--sidebar-heading) !important;
        background: var(--sidebar-hover);
    }

    .admin-dark-sidebar .nav-head-link.active-dashboard {
        color: var(--sidebar-accent-strong) !important;
        background: var(--sidebar-active) !important;
        font-weight: 600;
        box-shadow: inset 3px 0 0 var(--sidebar-accent);
    }

    .admin-dark-sidebar .nav-head-link.active-dashboard i {
        color: var(--sidebar-accent-strong) !important;
    }

    .admin-dark-sidebar .nav-head-link .fa-chevron-down {
        flex: 0 0 auto;
        color: var(--sidebar-muted);
        font-size: 10px;
        transition: transform .2s ease;
    }

    .admin-dark-sidebar .nav-head-link[aria-expanded="true"] .fa-chevron-down {
        color: var(--sidebar-heading) !important;
        transform: rotate(180deg);
    }

    .admin-dark-sidebar .nav-sub-list {
        margin: 3px 0 7px 17px;
        padding: 2px 0 2px 10px;
        list-style: none;
        border-left: 1px solid var(--sidebar-border);
    }

    .admin-dark-sidebar .nav-sub-link,
    .admin-dark-sidebar .nav-sub-list .nav-link {
        min-height: 32px;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 2px;
        padding: 6px 9px;
        color: var(--sidebar-muted);
        background: transparent;
        border-radius: 5px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 400;
        line-height: 1.4;
        transition: color .15s ease, background .15s ease;
    }

    .admin-dark-sidebar .nav-sub-link:hover,
    .admin-dark-sidebar .nav-sub-list .nav-link:hover {
        color: var(--sidebar-heading) !important;
        background: var(--sidebar-hover);
    }

    .admin-dark-sidebar .nav-sub-link.active,
    .admin-dark-sidebar .nav-sub-list .nav-link.active {
        color: var(--sidebar-accent-strong) !important;
        background: var(--sidebar-active);
        font-weight: 600;
    }

    .admin-dark-sidebar .media-chooser-menu-item {
        color: #175cd3 !important;
        background: #eff8ff !important;
        border: 1px solid #d1e9ff;
        font-weight: 600;
    }

    .admin-dark-sidebar .media-chooser-menu-item:hover,
    .admin-dark-sidebar .media-chooser-menu-item.active {
        color: #fff !important;
        background: #1570ef !important;
        border-color: #1570ef;
    }

    .user-footer-badge {
        min-height: 66px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 16px;
        background: #fff;
        border-top: 1px solid var(--sidebar-border);
    }

    .user-avatar-sq {
        width: 36px;
        height: 36px;
        min-width: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        background: #d97706;
        font-size: 14px;
        font-weight: 700;
    }

    .admin-dark-sidebar #admin-brand-name-title { color: #101828 !important; }
    .admin-dark-sidebar #admin-footer-user-name { color: #344054 !important; }
    .admin-dark-sidebar #admin-footer-user-role { color: #667085 !important; }

    .admin-sidebar-backdrop { display: none; }

    @media (max-width: 991.98px) {
        .admin-dark-sidebar {
            position: fixed;
            top: 0;
            left: -280px;
            z-index: 1060;
            width: min(260px, calc(100vw - 48px));
            height: 100vh;
            min-height: 100vh;
            box-shadow: 8px 0 24px rgba(16, 24, 40, .12);
        }

        .admin-dark-sidebar.mobile-sidebar-open { left: 0 !important; }

        .admin-sidebar-backdrop {
            position: fixed;
            inset: 0;
            z-index: 1055;
            display: none;
            background: rgba(16, 24, 40, .42);
            opacity: 0;
            transition: opacity .2s ease;
        }

        .admin-sidebar-backdrop.active {
            display: block !important;
            opacity: 1 !important;
        }

        .admin-nav-list {
            overscroll-behavior: contain;
            padding-bottom: 20px;
        }
    }
</style>

<!-- Mobile Touch Backdrop -->
<div class="admin-sidebar-backdrop d-lg-none" id="adminSidebarBackdrop" onclick="closeAdminSidebar()"></div>

<aside class="admin-dark-sidebar sidebar-wrapper" id="admin-main-sidebar-container">
    <!-- Branding Header -->
    <div class="admin-sidebar-logo-area" id="admin-brand-header-box">
        <a href="{{ route('admin.dashboard') }}"
            class="text-decoration-none d-flex align-items-center gap-2 overflow-hidden flex-grow-1">
            @if(!empty($finalFavicon))
                <div class="admin-brand-favicon-circle flex-shrink-0" id="admin-brand-favicon-box">
                    <img src="{{ $finalFavicon }}" alt="Favicon" id="admin-brand-favicon-img"
                        onerror="this.parentElement.innerHTML='<i class=\'fas fa-crown\'></i>'; this.parentElement.classList.add('fallback-avatar');">
                </div>
            @else
                <div class="admin-brand-favicon-circle fallback-avatar flex-shrink-0" id="admin-brand-avatar-box">
                    <i class="fas fa-crown"></i>
                </div>
            @endif
            <div class="overflow-hidden">
                <h6 class="fw-bold text-white mb-0 text-truncate" style="font-size: 13.5px;" id="admin-brand-name-title">{{ $siteTitle }}</h6>
                <small class="text-secondary d-block text-truncate" style="font-size: 10.5px;"
                    id="admin-brand-portal-sub">{{ $siteTagline }}</small>
            </div>
        </a>

        <!-- Mobile Close Button -->
        <button type="button" class="mobile-sidebar-close-btn d-lg-none ms-2"
            onclick="closeAdminSidebar()"
            title="Close Sidebar">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="admin-nav-list" id="admin-sidebar-scroll-wrapper">
        <!-- Dashboard Link -->
        @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.dashboard'))
            <a href="{{ route('admin.dashboard') }}"
                class="nav-head-link {{ request()->routeIs('admin.dashboard') ? 'active-dashboard' : '' }}"
                id="nav-btn-dashboard">
                <span id="nav-icon-label-dash"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</span>
            </a>
        @endif

        <!-- ========================================================================= -->
        <!-- 1. MULTI-BRANCH POS & CASH REGISTER ACCORDION                             -->
        <!-- ========================================================================= -->
        @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.pos.terminal', 'admin.pos.index', 'admin.daily_closing.session', 'admin.daily_closing.index']))
            @php 
                $isPosActive = request()->routeIs('admin.pos.*') || request()->routeIs('admin.daily_closing.*'); 
            @endphp
            <a class="nav-head-link {{ $isPosActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapsePosRegister" role="button" aria-expanded="{{ $isPosActive ? 'true' : 'false' }}" id="nav-btn-pos">
                <span><i class="fas fa-cash-register me-2"></i> POS &amp; Register</span>
                <span class="d-flex align-items-center gap-1">
                    @if($pendingClosingAuditCount > 0)
                        <span class="badge bg-danger rounded-pill" style="font-size: 9.5px; padding: 2px 6px;" title="Pending Audits">{{ $pendingClosingAuditCount }}</span>
                    @endif
                    <i class="fas fa-chevron-down"></i>
                </span>
            </a>
            <div class="collapse {{ $isPosActive ? 'show' : '' }}" id="collapsePosRegister">
                <ul class="nav-sub-list" id="sublist-pos-register">
                    @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.pos.terminal', 'admin.pos.index']))
                        <li>
                            <a href="{{ route('admin.pos.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.pos.*') ? 'active' : '' }}"
                                id="link-pos-terminal">
                                <i class="fas fa-desktop me-2" style="font-size: 10px;"></i> POS Terminal
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.daily_closing.session'))
                        <li>
                            <a href="{{ route('admin.daily_closing.session') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.daily_closing.session') ? 'active' : '' }}"
                                id="link-daily-closing-session">
                                <i class="fas fa-money-bill-transfer me-2" style="font-size: 10px;"></i> Today's Register Session
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.daily_closing.index'))
                        <li>
                            <a href="{{ route('admin.daily_closing.index') }}"
                                class="nav-sub-link d-flex justify-content-between align-items-center {{ request()->routeIs('admin.daily_closing.index') ? 'active' : '' }}"
                                id="link-daily-closing-history">
                                <span><i class="fas fa-receipt me-2" style="font-size: 10px;"></i> Closing History &amp; Audit</span>
                                @if($pendingClosingAuditCount > 0)
                                    <span class="badge bg-danger rounded-pill" style="font-size: 9px;">{{ $pendingClosingAuditCount }}</span>
                                @endif
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- Order Accordion -->
        @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.order.online', 'admin.order.create_manual', 'admin.order.incomplete', 'admin.order.return', 'admin.order.exchange', 'admin.order.fraud']))
            @php $isOrderActive = request()->routeIs('admin.order.*'); @endphp
            <a class="nav-head-link {{ $isOrderActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseOrder" role="button" aria-expanded="{{ $isOrderActive ? 'true' : 'false' }}" id="nav-btn-order">
                <span><i class="fas fa-shopping-cart me-2"></i> Order</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isOrderActive ? 'show' : '' }}" id="collapseOrder">
                <ul class="nav-sub-list" id="sublist-order">
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.order.online'))
                        <li><a href="{{ route('admin.order.online') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.order.online') ? 'active' : '' }}"
                                id="link-order-online"><i class="fas fa-globe me-2" style="font-size: 10px;"></i> All Orders</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.order.create_manual'))
                        <li><a href="{{ route('admin.order.create_manual') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.order.create_manual') ? 'active' : '' }}"
                                id="link-order-create-manual">
                                <i class="fas fa-circle-plus me-2" style="font-size: 10px;"></i> Create Order</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.order.incomplete'))
                        <li><a href="{{ route('admin.order.incomplete') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.order.incomplete') ? 'active' : '' }}"
                                id="link-order-incomplete"><i class="fas fa-hourglass-half me-2" style="font-size: 10px;"></i> Incomplete Orders</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.order.return'))
                        <li><a href="{{ route('admin.order.return') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.order.return') ? 'active' : '' }}"
                                id="link-order-return"><i class="fas fa-rotate-left me-2" style="font-size: 10px;"></i> Return Orders</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.order.exchange'))
                        <li><a href="{{ route('admin.order.exchange') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.order.exchange') ? 'active' : '' }}"
                                id="link-order-exchange"><i class="fas fa-arrow-right-arrow-left me-2" style="font-size: 10px;"></i> Exchange Orders</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.order.fraud'))
                        <li><a href="{{ route('admin.order.fraud') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.order.fraud') ? 'active' : '' }}"
                                id="link-order-fraud"><i class="fas fa-shield-alt me-2" style="font-size: 10px;"></i> Fraud Checker</a></li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- Courier Accordion -->
        @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.courier.dashboard', 'admin.courier.index', 'admin.courier.booking', 'admin.courier.settlements', 'admin.system.setting.courier']))
            @php $isCourierActive = request()->routeIs('admin.courier.*'); @endphp
            <a class="nav-head-link {{ $isCourierActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseCourier" role="button" aria-expanded="{{ $isCourierActive ? 'true' : 'false' }}" id="nav-btn-courier">
                <span><i class="fas fa-truck-fast me-2"></i> Courier</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isCourierActive ? 'show' : '' }}" id="collapseCourier">
                <ul class="nav-sub-list" id="sublist-courier">
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.courier.dashboard'))
                        <li>
                            <a href="{{ route('admin.courier.dashboard') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.courier.dashboard') ? 'active' : '' }}"
                                id="link-courier-dashboard">
                                <i class="fas fa-chart-pie me-2" style="font-size: 10px;"></i> Courier Dashboard
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.courier.index'))
                        <li>
                            <a href="{{ route('admin.courier.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.courier.index') ? 'active' : '' }}"
                                id="link-courier-all-orders">
                                <i class="fas fa-boxes-packing me-2" style="font-size: 10px;"></i> All Courier Orders
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.courier.booking'))
                        <li>
                            <a href="{{ route('admin.courier.booking') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.courier.booking') ? 'active' : '' }}"
                                id="link-courier-booking">
                                <i class="fas fa-plus-circle me-2" style="font-size: 10px;"></i> New Booking
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.courier.settlements'))
                        <li>
                            <a href="{{ route('admin.courier.settlements') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.courier.settlements') ? 'active' : '' }}"
                                id="link-courier-settlements">
                                <i class="fas fa-money-bill-transfer me-2" style="font-size: 10px;"></i> COD Settlement
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.system.setting.courier'))
                        <li>
                            <a href="{{ route('admin.system.setting.courier') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.system.setting.courier*') ? 'active' : '' }}"
                                id="link-courier-settings">
                                <i class="fas fa-sliders me-2" style="font-size: 10px;"></i> Courier Gateways
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- Transactions Accordion -->
        @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.transactions.dashboard', 'admin.transactions.checkout', 'admin.transactions.pos', 'admin.transactions.cod', 'admin.transactions.manual', 'admin.transactions.index', 'admin.transactions.unchecked', 'admin.transactions.history', 'admin.transactions.reports', 'admin.transactions.trash', 'admin.payment.setting.gateways']))
            @php $isTxnActive = request()->routeIs('admin.transactions.*'); @endphp
            <a class="nav-head-link {{ $isTxnActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseTransactions" role="button" aria-expanded="{{ $isTxnActive ? 'true' : 'false' }}" id="nav-btn-transactions">
                <span class="text-nowrap"><i class="fas fa-money-check-dollar me-2"></i> Transactions</span>
                <span class="d-flex align-items-center gap-1">
                    <span class="badge bg-danger rounded-pill {{ $uncheckedTxnCount > 0 ? '' : 'd-none' }}" id="sidebar-unchecked-txn-badge" style="font-size: 10px; padding: 2px 6px;">{{ $uncheckedTxnCount }}</span>
                    <i class="fas fa-chevron-down"></i>
                </span>
            </a>
            <div class="collapse {{ $isTxnActive ? 'show' : '' }}" id="collapseTransactions">
                <ul class="nav-sub-list" id="sublist-transactions">
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.transactions.dashboard'))
                        <li><a href="{{ route('admin.transactions.dashboard') }}" class="nav-sub-link {{ request()->routeIs('admin.transactions.dashboard') ? 'active' : '' }}"><i class="fas fa-chart-line me-2" style="font-size: 10px;"></i> Overview</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.transactions.checkout'))
                        <li>
                            <a href="{{ route('admin.transactions.checkout') }}" class="nav-sub-link d-flex justify-content-between align-items-center {{ request()->routeIs('admin.transactions.checkout') ? 'active' : '' }}">
                                <span><i class="fas fa-cart-shopping me-2" style="font-size: 10px;"></i> Checkout Requests</span>
                                <span class="badge bg-warning text-dark rounded-pill {{ $pendingCheckoutCount > 0 ? '' : 'd-none' }}" id="sidebar-checkout-txn-badge" style="font-size: 9.5px;">{{ $pendingCheckoutCount }}</span>
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.transactions.pos'))
                        <li><a href="{{ route('admin.transactions.pos') }}" class="nav-sub-link {{ request()->routeIs('admin.transactions.pos') ? 'active' : '' }}"><i class="fas fa-cash-register me-2" style="font-size: 10px;"></i> POS Cash Orders</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.transactions.cod'))
                        <li><a href="{{ route('admin.transactions.cod') }}" class="nav-sub-link {{ request()->routeIs('admin.transactions.cod') ? 'active' : '' }}"><i class="fas fa-hand-holding-dollar me-2" style="font-size: 10px;"></i> COD Orders</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.transactions.manual'))
                        <li><a href="{{ route('admin.transactions.manual') }}" class="nav-sub-link {{ request()->routeIs('admin.transactions.manual') ? 'active' : '' }}"><i class="fas fa-keyboard me-2" style="font-size: 10px;"></i> Manual Transactions</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.transactions.index'))
                        <li><a href="{{ route('admin.transactions.index') }}" class="nav-sub-link {{ request()->routeIs('admin.transactions.index') ? 'active' : '' }}"><i class="fas fa-list me-2" style="font-size: 10px;"></i> All Transactions</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.transactions.unchecked'))
                        <li>
                            <a href="{{ route('admin.transactions.unchecked') }}" class="nav-sub-link d-flex justify-content-between align-items-center {{ request()->routeIs('admin.transactions.unchecked') ? 'active' : '' }}">
                                <span><i class="fas fa-bell me-2" style="font-size: 10px;"></i> Unchecked</span>
                                <span class="badge bg-danger rounded-pill {{ $uncheckedTxnCount > 0 ? '' : 'd-none' }}" id="sidebar-sub-unchecked-badge" style="font-size: 9.5px;">{{ $uncheckedTxnCount }}</span>
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.transactions.history'))
                        <li><a href="{{ route('admin.transactions.history') }}" class="nav-sub-link {{ request()->routeIs('admin.transactions.history') ? 'active' : '' }}"><i class="fas fa-history me-2" style="font-size: 10px;"></i> Verification History</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.transactions.reports'))
                        <li><a href="{{ route('admin.transactions.reports') }}" class="nav-sub-link {{ request()->routeIs('admin.transactions.reports') ? 'active' : '' }}"><i class="fas fa-file-invoice me-2" style="font-size: 10px;"></i> Transaction Reports</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.transactions.destroy'))
                        <li><a href="{{ route('admin.transactions.trash') }}" class="nav-sub-link {{ request()->routeIs('admin.transactions.trash') ? 'active' : '' }}"><i class="fas fa-trash-can me-2" style="font-size: 10px;"></i> Trashed Records</a></li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- Accounts & Expenses Accordion -->
        @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.accounts.expenses.index', 'admin.accounts.income.index', 'admin.accounts.investment.index', 'admin.accounts.assets', 'admin.accounts.methods', 'admin.accounts.ledger.index', 'admin.accounts.bank.index', 'admin.accounts.expenses', 'admin.accounts.income', 'admin.accounts.investment', 'admin.accounts.ledger', 'admin.accounts.bank']))
            @php 
                $isAccountsActive = request()->routeIs('admin.accounts.*'); 
            @endphp
            <a class="nav-head-link {{ $isAccountsActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseAccounts" role="button" aria-expanded="{{ $isAccountsActive ? 'true' : 'false' }}" id="nav-btn-accounts">
                <span><i class="fas fa-wallet me-2"></i> Accounts &amp; Expenses</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isAccountsActive ? 'show' : '' }}" id="collapseAccounts">
                <ul class="nav-sub-list" id="sublist-accounts">
                    @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.accounts.expenses.index', 'admin.accounts.expenses']))
                        <li>
                            @php
        $expenseUrl = Route::has('admin.accounts.expenses.index')
            ? route('admin.accounts.expenses.index')
            : (Route::has('admin.accounts.expenses') ? route('admin.accounts.expenses') : url('/admin/accounts/expenses'));
                            @endphp
                            <a href="{{ $expenseUrl }}"
                                class="nav-sub-link {{ request()->routeIs('admin.accounts.expenses*') ? 'active' : '' }}"
                                id="link-acc-expenses">
                                <i class="fas fa-receipt me-2" style="font-size: 10px;"></i> Expenses
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.accounts.income.index', 'admin.accounts.income']))
                        <li>
                            @php
        $incomeUrl = Route::has('admin.accounts.income.index')
            ? route('admin.accounts.income.index')
            : (Route::has('admin.accounts.income') ? route('admin.accounts.income') : url('/admin/accounts/income'));
                            @endphp
                            <a href="{{ $incomeUrl }}"
                                class="nav-sub-link {{ request()->routeIs('admin.accounts.income*') ? 'active' : '' }}"
                                id="link-acc-income">
                                <i class="fas fa-hand-holding-dollar me-2" style="font-size: 10px;"></i> Income
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.accounts.investment.index', 'admin.accounts.investment']))
                        <li>
                            @php
        $investmentUrl = Route::has('admin.accounts.investment.index')
            ? route('admin.accounts.investment.index')
            : (Route::has('admin.accounts.investment') ? route('admin.accounts.investment') : url('/admin/accounts/investment'));
                            @endphp
                            <a href="{{ $investmentUrl }}"
                                class="nav-sub-link {{ request()->routeIs('admin.accounts.investment*') ? 'active' : '' }}"
                                id="link-acc-investment">
                                <i class="fas fa-chart-line me-2" style="font-size: 10px;"></i> Investment
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.accounts.assets', 'admin.accounts.assets.index']))
                        <li>
                            @php
        $assetsUrl = Route::has('admin.accounts.assets')
            ? route('admin.accounts.assets')
            : url('/admin/accounts/assets');
                            @endphp
                            <a href="{{ $assetsUrl }}"
                                class="nav-sub-link {{ request()->routeIs('admin.accounts.assets*') ? 'active' : '' }}"
                                id="link-acc-assets">
                                <i class="fas fa-building me-2" style="font-size: 10px;"></i> Assets
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.accounts.methods'))
                        <li>
                            <a href="{{ route('admin.accounts.methods') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.accounts.methods') ? 'active' : '' }}"
                                id="link-acc-methods">
                                <i class="fas fa-credit-card me-2" style="font-size: 10px;"></i> Payment Methods
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.accounts.ledger.index', 'admin.accounts.ledger']))
                        <li>
                            @php
        $ledgerUrl = Route::has('admin.accounts.ledger.index')
            ? route('admin.accounts.ledger.index')
            : (Route::has('admin.accounts.ledger') ? route('admin.accounts.ledger') : url('/admin/accounts/ledger'));
                            @endphp
                            <a href="{{ $ledgerUrl }}"
                                class="nav-sub-link {{ request()->routeIs('admin.accounts.ledger*') ? 'active' : '' }}"
                                id="link-acc-ledger">
                                <i class="fas fa-list-check me-2" style="font-size: 10px;"></i> Ledgers
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.accounts.bank.index', 'admin.accounts.bank']))
                        <li>
                            @php
        $bankUrl = Route::has('admin.accounts.bank.index')
            ? route('admin.accounts.bank.index')
            : (Route::has('admin.accounts.bank') ? route('admin.accounts.bank') : url('/admin/accounts/bank-balance'));
                            @endphp
                            <a href="{{ $bankUrl }}"
                                class="nav-sub-link {{ request()->routeIs('admin.accounts.bank*') ? 'active' : '' }}"
                                id="link-acc-bank">
                                <i class="fas fa-building-columns me-2" style="font-size: 10px;"></i> Bank &amp; Cash
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- Staff Payroll & Salary Disbursement -->
        @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.payroll.index', 'admin.payroll.history']))
            @php 
                $isPayrollActive = request()->routeIs('admin.payroll.*'); 
            @endphp
            <a class="nav-head-link {{ $isPayrollActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapsePayroll" role="button" aria-expanded="{{ $isPayrollActive ? 'true' : 'false' }}" id="nav-btn-payroll">
                <span class="text-nowrap"><i class="fas fa-money-check-dollar me-2"></i> Staff Payroll</span>
                <span class="d-flex align-items-center gap-1">
                    @if($pendingPayrollCount > 0)
                        <span class="badge bg-danger rounded-pill" style="font-size: 9.5px; padding: 2px 6px;" title="Pending Salaries">{{ $pendingPayrollCount }}</span>
                    @endif
                    <i class="fas fa-chevron-down"></i>
                </span>
            </a>
            <div class="collapse {{ $isPayrollActive ? 'show' : '' }}" id="collapsePayroll">
                <ul class="nav-sub-list" id="sublist-payroll">
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.payroll.index'))
                        <li>
                            <a href="{{ route('admin.payroll.index') }}"
                                class="nav-sub-link {{ (request()->routeIs('admin.payroll.index') && !request()->filled('status')) ? 'active' : '' }}"
                                id="link-payroll-sheet">
                                <i class="fas fa-file-invoice-dollar me-2" style="font-size: 10px;"></i> Monthly Salary Sheet
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.payroll.index', ['status' => 'pending']) }}"
                                class="nav-sub-link d-flex justify-content-between align-items-center {{ (request()->routeIs('admin.payroll.index') && request('status') === 'pending') ? 'active' : '' }}"
                                id="link-payroll-pending">
                                <span><i class="fas fa-clock me-2" style="font-size: 10px;"></i> Due / Pending Salaries</span>
                                @if($pendingPayrollCount > 0)
                                    <span class="badge bg-danger rounded-pill" style="font-size: 9px;">{{ $pendingPayrollCount }}</span>
                                @endif
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- Enterprise Tax & VAT Accordion -->
        @if(!$u || $isSuperAdmin || $u->hasAnyAccess(['admin.tax.dashboard', 'admin.tax.classes.index', 'admin.tax.rules.index', 'admin.tax.periods.index', 'admin.tax.settings.index', 'admin.tax.payments.index', 'admin.tax.documents.index', 'admin.tax.reports.index', 'admin.tax.adjustments', 'admin.tax.audit_logs']))
            @php 
                $isTaxActive = request()->routeIs('admin.tax.*'); 
            @endphp
            <a class="nav-head-link {{ $isTaxActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseTax" role="button" aria-expanded="{{ $isTaxActive ? 'true' : 'false' }}" id="nav-btn-tax">
                <span><i class="fas fa-file-invoice-dollar me-2"></i> Tax &amp; VAT</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isTaxActive ? 'show' : '' }}" id="collapseTax">
                <ul class="nav-sub-list" id="sublist-tax">
                    @if(!$u || $isSuperAdmin || $u->hasAccess('admin.tax.dashboard'))
                        <li>
                            <a href="{{ route('admin.tax.dashboard') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.tax.dashboard') ? 'active' : '' }}"
                                id="link-tax-dashboard">
                                <i class="fas fa-chart-pie me-2" style="font-size: 10px;"></i> Tax Dashboard
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isSuperAdmin || $u->hasAccess('admin.tax.classes.index'))
                        <li>
                            <a href="{{ route('admin.tax.classes.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.tax.classes.*') ? 'active' : '' }}"
                                id="link-tax-classes">
                                <i class="fas fa-shapes me-2" style="font-size: 10px;"></i> Tax Classes
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isSuperAdmin || $u->hasAccess('admin.tax.rules.index'))
                        <li>
                            <a href="{{ route('admin.tax.rules.index') }}"
                                class="nav-sub-link {{ (request()->routeIs('admin.tax.rules.*') && !request()->filled('type')) ? 'active' : '' }}"
                                id="link-tax-rules">
                                <i class="fas fa-scale-balanced me-2" style="font-size: 10px;"></i> Tax Rules
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isSuperAdmin || $u->hasAccess('admin.tax.payments.index'))
                        <li>
                            <a href="{{ route('admin.tax.payments.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.tax.payments.*') ? 'active' : '' }}"
                                id="link-tax-payments">
                                <i class="fas fa-receipt me-2" style="font-size: 10px;"></i> Tax Payments
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isSuperAdmin || $u->hasAccess('admin.tax.periods.index'))
                        <li>
                            <a href="{{ $taxPeriodsUrl }}"
                                class="nav-sub-link {{ request()->routeIs('admin.tax.periods.*') ? 'active' : '' }}"
                                id="link-tax-periods">
                                <i class="fas fa-calendar-check me-2" style="font-size: 10px;"></i> Tax Periods
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isSuperAdmin || $u->hasAccess('admin.tax.reports.index'))
                        <li>
                            <a href="{{ route('admin.tax.reports.index') }}"
                                class="nav-sub-link {{ (request()->routeIs('admin.tax.reports.*') && !request()->filled('report_type')) ? 'active' : '' }}"
                                id="link-tax-reports">
                                <i class="fas fa-chart-line me-2" style="font-size: 10px;"></i> Tax Reports
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isSuperAdmin || $u->hasAccess('admin.tax.documents.index'))
                        <li>
                            <a href="{{ route('admin.tax.documents.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.tax.documents.*') ? 'active' : '' }}"
                                id="link-tax-documents">
                                <i class="fas fa-file-shield me-2" style="font-size: 10px;"></i> Tax Documents
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isSuperAdmin || $u->hasAccess('admin.tax.reports.index'))
                        <li>
                            <a href="{{ route('admin.tax.adjustments') }}"
                                class="nav-sub-link {{ (request()->routeIs('admin.tax.reports.*') && request('report_type') === 'adjustment_history') ? 'active' : '' }}"
                                id="link-tax-adjustments">
                                <i class="fas fa-sliders me-2" style="font-size: 10px;"></i> Tax Adjustments
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isSuperAdmin || $u->hasAccess('admin.tax.audit_logs'))
                        <li>
                            <a href="{{ route('admin.tax.audit_logs') }}"
                                class="nav-sub-link {{ (request()->routeIs('admin.activity.logs*') && request('module') === 'tax') ? 'active' : '' }}"
                                id="link-tax-audit-logs">
                                <i class="fas fa-clipboard-check me-2" style="font-size: 10px;"></i> Tax Audit Logs
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isSuperAdmin || $u->hasAccess('admin.tax.settings.index'))
                        <li>
                            <a href="{{ $taxSettingsUrl }}"
                                class="nav-sub-link {{ request()->routeIs('admin.tax.settings.*') ? 'active' : '' }}"
                                id="link-tax-settings">
                                <i class="fas fa-gear me-2" style="font-size: 10px;"></i> VAT &amp; Tax Settings
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- Product Accordion -->
        @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.product.index', 'admin.product.category', 'admin.product.brand', 'admin.product.barcode', 'admin.product.reviews.index', 'admin.review.index']))
            @php 
                $isProductActive = request()->routeIs('admin.product.*') || request()->routeIs('admin.review.*'); 
            @endphp
            <a class="nav-head-link {{ $isProductActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseProduct" role="button" aria-expanded="{{ $isProductActive ? 'true' : 'false' }}" id="nav-btn-product">
                <span class="text-nowrap"><i class="fas fa-box-open me-2"></i>Products</span>
                <span class="d-flex align-items-center gap-1">
                    <span class="badge bg-danger rounded-pill {{ $pendingReviewCount > 0 ? '' : 'd-none' }}" id="sidebar-prod-pending-badge" style="font-size: 9.5px; padding: 2px 6px;">{{ $pendingReviewCount }}</span>
                    <i class="fas fa-chevron-down"></i>
                </span>
            </a>
            <div class="collapse {{ $isProductActive ? 'show' : '' }}" id="collapseProduct">
                <ul class="nav-sub-list" id="sublist-product">
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.product.index'))
                        <li><a href="{{ route('admin.product.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.product.index') ? 'active' : '' }}" id="link-prod-all"><i
                                    class="fas fa-box me-2" style="font-size: 10px;"></i>All Product</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.product.index'))
                        <li>
                            <a href="{{ route('admin.product.create') }}" class="nav-sub-link  {{ request()->routeIs('admin.product.create') ? 'active' : '' }}">
                                <i class="fas fa-plus me-1"></i>
                                <span>Add New Product</span>
                            </a>
                        </li>
                    @endif

                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.product.category'))
                        <li><a href="{{ route('admin.product.category') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.product.category*') ? 'active' : '' }}" id="link-prod-cat"><i
                                    class="fas fa-tags me-2" style="font-size: 10px;"></i> Category</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.product.brand'))
                        <li><a href="{{ route('admin.product.brand') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.product.brand*') ? 'active' : '' }}" id="link-prod-brand"><i
                                    class="fas fa-award me-2" style="font-size: 10px;"></i> Brand</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.product.barcode'))
                        <li><a href="{{ route('admin.product.barcode') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.product.barcode*') ? 'active' : '' }}"
                                id="link-prod-barcode"><i class="fas fa-barcode me-2" style="font-size: 10px;"></i> Barcode Print</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.product.reviews.index', 'admin.review.index']))
                        <li>
                            <a href="{{ route('admin.product.reviews.index') }}"
                                class="nav-sub-link d-flex justify-content-between align-items-center {{ (request()->routeIs('admin.product.reviews.*') || request()->routeIs('admin.review.*')) ? 'active' : '' }}"
                                id="link-prod-reviews">
                                <span><i class="fas fa-star-half-stroke me-2" style="font-size: 10px;"></i> Product Reviews</span>
                                <span class="badge bg-danger rounded-pill {{ $pendingReviewCount > 0 ? '' : 'd-none' }}" id="sidebar-sub-pending-reviews-badge" style="font-size: 9.5px;">{{ $pendingReviewCount }}</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- Promo: Coupons & Vouchers Accordion -->
        @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.promo.coupons.index', 'admin.promo.coupons.create', 'admin.promo.coupons.vouchers.create', 'admin.promo.coupon']))
            @php 
                $isPromoActive = request()->routeIs('admin.promo.coupons.*') || request()->routeIs('admin.promo.coupon*') || request()->is('admin/promo/coupons*'); 
            @endphp
            <a class="nav-head-link {{ $isPromoActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseCoupons" role="button" aria-expanded="{{ $isPromoActive ? 'true' : 'false' }}" id="nav-btn-coupons">
                <span class="text-nowrap"><i class="fas fa-ticket-alt me-2"></i> Coupons &amp; Vouchers</span>
                <span class="d-flex align-items-center gap-1">
                    <span class="badge bg-success rounded-pill {{ $activeCouponCount > 0 ? '' : 'd-none' }}" id="sidebar-active-coupon-badge" style="font-size: 9.5px; padding: 2px 6px;">{{ $activeCouponCount }}</span>
                    <i class="fas fa-chevron-down"></i>
                </span>
            </a>
            <div class="collapse {{ $isPromoActive ? 'show' : '' }}" id="collapseCoupons">
                <ul class="nav-sub-list" id="sublist-coupons">
                    @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.promo.coupons.index', 'admin.promo.coupon']))
                        <li>
                            <a href="{{ $couponIndexUrl }}"
                                class="nav-sub-link {{ (request()->routeIs('admin.promo.coupons.index') && request('tab', 'coupons') === 'coupons') ? 'active' : '' }}"
                                id="link-coupon-all">
                                <i class="fas fa-tags me-2" style="font-size: 10px;"></i> All Coupons
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.promo.coupons.create'))
                        <li>
                            <a href="{{ $couponCreateUrl }}"
                                class="nav-sub-link {{ request()->routeIs('admin.promo.coupons.create') ? 'active' : '' }}"
                                id="link-coupon-create">
                                <i class="fas fa-plus-circle me-2" style="font-size: 10px;"></i> Add Coupon
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.promo.coupons.index'))
                        <li>
                            <a href="{{ $voucherIndexUrl }}"
                                class="nav-sub-link {{ (request()->routeIs('admin.promo.coupons.index') && request('tab') === 'vouchers') ? 'active' : '' }}"
                                id="link-voucher-campaigns">
                                <i class="fas fa-gift me-2" style="font-size: 10px;"></i> Voucher Campaigns
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.promo.coupons.vouchers.create'))
                        <li>
                            <a href="{{ $voucherCreateUrl }}"
                                class="nav-sub-link {{ request()->routeIs('admin.promo.coupons.vouchers.create') ? 'active' : '' }}"
                                id="link-voucher-create">
                                <i class="fas fa-circle-plus me-2" style="font-size: 10px;"></i> New Voucher
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.promo.coupons.index'))
                        <li>
                            <a href="{{ $redemptionLogsUrl }}"
                                class="nav-sub-link {{ (request()->routeIs('admin.promo.coupons.index') && request('tab') === 'redemptions') ? 'active' : '' }}"
                                id="link-coupon-redemptions">
                                <i class="fas fa-history me-2" style="font-size: 10px;"></i> Redemption Logs
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- Inventory Management Accordion -->
        @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.inventory.dashboard', 'admin.inventory.stock_in', 'admin.inventory.stock_out', 'admin.inventory.balance', 'admin.inventory.movements', 'admin.inventory.reports']))
            @php $isInvActive = request()->routeIs('admin.inventory.*'); @endphp
            <a class="nav-head-link {{ $isInvActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseInventory" role="button" aria-expanded="{{ $isInvActive ? 'true' : 'false' }}" id="nav-btn-inventory">
                <span><i class="fas fa-boxes-packing me-2"></i> Inventory</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isInvActive ? 'show' : '' }}" id="collapseInventory">
                <ul class="nav-sub-list" id="sublist-inventory">
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.inventory.dashboard'))
                        <li><a href="{{ route('admin.inventory.dashboard') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.inventory.dashboard') ? 'active' : '' }}"
                                id="link-inv-dash"><i class="fas fa-chart-line me-2" style="font-size: 10px;"></i> Inventory Dashboard</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.inventory.stock_in'))
                        <li><a href="{{ route('admin.inventory.stock_in') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.inventory.stock_in*') ? 'active' : '' }}"
                                id="link-inv-stock-in"><i class="fas fa-arrow-down-long me-2" style="font-size: 10px;"></i> Stock In</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.inventory.stock_out'))
                        <li><a href="{{ route('admin.inventory.stock_out') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.inventory.stock_out*') ? 'active' : '' }}"
                                id="link-inv-stock-out"><i class="fas fa-arrow-up-long me-2" style="font-size: 10px;"></i> Stock Out</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.inventory.balance'))
                        <li><a href="{{ route('admin.inventory.balance') }}"
                                class="nav-sub-link {{ (request()->routeIs('admin.inventory.balance') && !request()->filled('stock_status')) ? 'active' : '' }}"
                                id="link-inv-balance"><i class="fas fa-scale-balanced me-2" style="font-size: 10px;"></i> Stock Balance</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.inventory.movements'))
                        <li><a href="{{ route('admin.inventory.movements') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.inventory.movements') ? 'active' : '' }}"
                                id="link-inv-movements"><i class="fas fa-clipboard-list me-2" style="font-size: 10px;"></i> Inventory Ledger</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.inventory.balance'))
                        <li><a href="{{ route('admin.inventory.balance', ['stock_status' => 'low_stock']) }}"
                                class="nav-sub-link {{ (request()->routeIs('admin.inventory.balance') && request('stock_status') === 'low_stock') ? 'active' : '' }}"
                                id="link-inv-low-stock"><i class="fas fa-triangle-exclamation me-2" style="font-size: 10px;"></i> Low Stock Items</a></li>
                        <li><a href="{{ route('admin.inventory.balance', ['stock_status' => 'out_of_stock']) }}"
                                class="nav-sub-link {{ (request()->routeIs('admin.inventory.balance') && request('stock_status') === 'out_of_stock') ? 'active' : '' }}"
                                id="link-inv-out-stock"><i class="fas fa-circle-xmark me-2" style="font-size: 10px;"></i> Out of Stock Items</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.inventory.reports'))
                        <li><a href="{{ route('admin.inventory.reports') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.inventory.reports*') ? 'active' : '' }}"
                                id="link-inv-reports"><i class="fas fa-file-invoice-dollar me-2" style="font-size: 10px;"></i> Inventory Reports</a></li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- Enterprise Procurement & Supply Chain Hub -->
        @php 
            $isProcurementActive = request()->routeIs('admin.suppliers.*')
    || request()->routeIs('admin.purchase-orders.*')
    || request()->routeIs('admin.purchases.*')
    || request()->routeIs('admin.purchase-receipts.*')
    || request()->routeIs('admin.supplier-payments.*')
    || request()->routeIs('admin.purchase-returns.*')
    || request()->routeIs('admin.supplier-exchanges.*')
    || request()->routeIs('admin.supplier-debit-credit-notes.*')
    || request()->routeIs('admin.procurement-assets.*')
    || request()->is('admin/procurement*'); 
        @endphp
        @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.suppliers.index', 'admin.purchase-orders.index', 'admin.purchases.index', 'admin.purchase-receipts.index', 'admin.supplier-payments.index', 'admin.purchase-returns.index', 'admin.supplier-exchanges.index', 'admin.supplier-debit-credit-notes.index', 'admin.procurement-assets.index']))
            <a class="nav-head-link {{ $isProcurementActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseProcurement" role="button" aria-expanded="{{ $isProcurementActive ? 'true' : 'false' }}" id="nav-btn-procurement">
                <span class="text-nowrap"><i class="fas fa-handshake-angle me-2"></i> Procurement</span>
                <span class="d-flex align-items-center gap-1">
                    @if($pendingPoCount > 0)
                        <span class="badge bg-warning text-dark rounded-pill" style="font-size: 9.5px; padding: 2px 6px;" title="Pending POs">{{ $pendingPoCount }}</span>
                    @endif
                    <i class="fas fa-chevron-down"></i>
                </span>
            </a>
            <div class="collapse {{ $isProcurementActive ? 'show' : '' }}" id="collapseProcurement">
                <ul class="nav-sub-list" id="sublist-procurement">
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.suppliers.index'))
                        <li>
                            <a href="{{ route('admin.suppliers.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}"
                                id="link-proc-suppliers">
                                <i class="fas fa-truck-field me-2" style="font-size: 10px;"></i> Suppliers &amp; Ledger
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.purchase-orders.index'))
                        <li>
                            <a href="{{ route('admin.purchase-orders.index') }}"
                                class="nav-sub-link d-flex justify-content-between align-items-center {{ request()->routeIs('admin.purchase-orders.*') ? 'active' : '' }}"
                                id="link-proc-po">
                                <span><i class="fas fa-file-signature me-2" style="font-size: 10px;"></i> Purchase Orders (PO)</span>
                                @if($pendingPoCount > 0)
                                    <span class="badge bg-warning text-dark rounded-pill" style="font-size: 9px;">{{ $pendingPoCount }}</span>
                                @endif
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.purchases.index'))
                        <li>
                            <a href="{{ route('admin.purchases.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.purchases.*') ? 'active' : '' }}"
                                id="link-proc-purchases">
                                <i class="fas fa-file-invoice-dollar me-2" style="font-size: 10px;"></i> Purchase Invoices
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.purchase-receipts.index'))
                        <li>
                            <a href="{{ route('admin.purchase-receipts.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.purchase-receipts.*') ? 'active' : '' }}"
                                id="link-proc-grn">
                                <i class="fas fa-dolly me-2" style="font-size: 10px;"></i> Goods Receipts (GRN)
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.supplier-payments.index'))
                        <li>
                            <a href="{{ route('admin.supplier-payments.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.supplier-payments.*') ? 'active' : '' }}"
                                id="link-proc-payments">
                                <i class="fas fa-money-check-dollar me-2" style="font-size: 10px;"></i> Supplier Payments
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.purchase-returns.index'))
                        <li>
                            <a href="{{ route('admin.purchase-returns.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.purchase-returns.*') ? 'active' : '' }}"
                                id="link-proc-returns">
                                <i class="fas fa-arrow-rotate-left me-2" style="font-size: 10px;"></i> Purchase Returns (RTV)
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.supplier-exchanges.index'))
                        <li>
                            <a href="{{ route('admin.supplier-exchanges.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.supplier-exchanges.*') ? 'active' : '' }}"
                                id="link-proc-exchanges">
                                <i class="fas fa-arrow-right-arrow-left me-2" style="font-size: 10px;"></i> Supplier Exchanges
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.supplier-debit-credit-notes.index'))
                        <li>
                            <a href="{{ route('admin.supplier-debit-credit-notes.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.supplier-debit-credit-notes.*') ? 'active' : '' }}"
                                id="link-proc-notes">
                                <i class="fas fa-note-sticky me-2" style="font-size: 10px;"></i> Debit &amp; Credit Notes
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.procurement-assets.index'))
                        <li>
                            <a href="{{ route('admin.procurement-assets.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.procurement-assets.*') ? 'active' : '' }}"
                                id="link-proc-assets">
                                <i class="fas fa-cubes-stacked me-2" style="font-size: 10px;"></i> Asset Procurement
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- ========================================================================= -->
        <!-- 2. BRANCH MANAGEMENT HUB ACCORDION                                        -->
        <!-- ========================================================================= -->
        @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.branch.index', 'admin.branch_product.index', 'admin.branch.assign.history', 'admin.branch.new.assign', 'admin.branch.products', 'admin.branch.orders']))
            @php 
                $isBranchActive = request()->routeIs('admin.branch.*') || request()->routeIs('admin.branch_product.*'); 
            @endphp
            <a class="nav-head-link {{ $isBranchActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseBranch" role="button" aria-expanded="{{ $isBranchActive ? 'true' : 'false' }}" id="nav-btn-branch">
                <span><i class="fas fa-code-branch me-2"></i> Branch</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isBranchActive ? 'show' : '' }}" id="collapseBranch">
                <ul class="nav-sub-list" id="sublist-branch">
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.branch.index'))
                        <li><a href="{{ route('admin.branch.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.branch.index*') ? 'active' : '' }}"
                                id="link-branch-all"><i class="fas fa-building me-2" style="font-size: 10px;"></i> All Branches</a></li>
                    @endif

                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.branch_product.index'))
                        <li><a href="{{ route('admin.branch_product.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.branch_product.*') ? 'active' : '' }}"
                                id="link-branch-product-catalog"><i class="fas fa-tags me-2" style="font-size: 10px;"></i> Branch Catalog &amp; Pricing</a></li>
                    @endif

                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.branch.new.assign'))
                        <li><a href="{{ route('admin.branch.new.assign') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.branch.new.assign*') ? 'active' : '' }}"
                                id="link-branch-new-assign"><i class="fas fa-dolly me-2" style="font-size: 10px;"></i> New Stock Transfer</a></li>
                    @endif

                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.branch.assign.history'))
                        <li><a href="{{ route('admin.branch.assign.history') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.branch.assign.history*') ? 'active' : '' }}"
                                id="link-branch-assign-hist"><i class="fas fa-clock-rotate-left me-2" style="font-size: 10px;"></i> Transfer History</a></li>
                    @endif

                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.branch.products'))
                        <li><a href="{{ route('admin.branch.products') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.branch.products*') ? 'active' : '' }}"
                                id="link-branch-products"><i class="fas fa-boxes me-2" style="font-size: 10px;"></i> Branch Stock Overview</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.branch.orders'))
                        <li><a href="{{ route('admin.branch.orders') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.branch.orders*') ? 'active' : '' }}"
                                id="link-branch-orders"><i class="fas fa-truck-ramp-box me-2" style="font-size: 10px;"></i> Branch Orders</a></li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- ========================================================================= -->
        <!-- 3. REPORTS & FINANCIAL ANALYTICS ACCORDION                                -->
        <!-- ========================================================================= -->
        @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.report.profit_loss', 'admin.report.balance_sheet', 'admin.report.cash_flow', 'admin.report.cogs', 'admin.branch_report.dashboard', 'admin.branch_report.sales', 'admin.branch_report.sales_person', 'admin.branch_report.comparison', 'admin.branch_report.inventory']))
            @php 
                $isReportActive = request()->routeIs('admin.report.*') || request()->routeIs('admin.branch_report.*'); 
            @endphp
            <a class="nav-head-link {{ $isReportActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseReports" role="button" aria-expanded="{{ $isReportActive ? 'true' : 'false' }}" id="nav-btn-reports">
                <span><i class="fas fa-chart-line me-2"></i> Reports &amp; Analytics</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isReportActive ? 'show' : '' }}" id="collapseReports">
                <ul class="nav-sub-list" id="sublist-reports">
                    {{-- Branch Reports --}}
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.branch_report.dashboard'))
                        <li>
                            <a href="{{ route('admin.branch_report.dashboard') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.branch_report.dashboard*') ? 'active' : '' }}"
                                id="link-branch-rep-dashboard">
                                <i class="fas fa-chart-pie me-2" style="font-size: 10px;"></i> Branch Analytics KPI
                            </a>
                        </li>
                    @endif

                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.branch_report.sales'))
                        <li>
                            <a href="{{ route('admin.branch_report.sales') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.branch_report.sales*') ? 'active' : '' }}"
                                id="link-branch-rep-sales">
                                <i class="fas fa-file-invoice-dollar me-2" style="font-size: 10px;"></i> Branch Sales Ledger
                            </a>
                        </li>
                    @endif

                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.branch_report.sales_person'))
                        <li>
                            <a href="{{ route('admin.branch_report.sales_person') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.branch_report.sales_person*') ? 'active' : '' }}"
                                id="link-branch-rep-sales-person">
                                <i class="fas fa-user-check me-2" style="font-size: 10px;"></i> Staff Sales Performance
                            </a>
                        </li>
                    @endif

                    @if(!$u || $isAdminOrSuper)
                        @if(Route::has('admin.branch_report.comparison'))
                            <li>
                                <a href="{{ route('admin.branch_report.comparison') }}"
                                    class="nav-sub-link {{ request()->routeIs('admin.branch_report.comparison*') ? 'active' : '' }}"
                                    id="link-branch-rep-comparison">
                                    <i class="fas fa-balance-scale me-2" style="font-size: 10px;"></i> Multi-Branch Comparison
                                </a>
                            </li>
                        @endif
                    @endif

                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.branch_report.inventory'))
                        <li>
                            <a href="{{ route('admin.branch_report.inventory') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.branch_report.inventory*') ? 'active' : '' }}"
                                id="link-branch-rep-inventory">
                                <i class="fas fa-boxes-stacked me-2" style="font-size: 10px;"></i> Stock Valuation Audit
                            </a>
                        </li>
                    @endif

                    {{-- General Financial P&L --}}
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.report.profit_loss'))
                        <li>
                            <a href="{{ route('admin.report.profit_loss') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.report.profit_loss') ? 'active' : '' }}"
                                id="link-report-pnl">
                                <i class="fas fa-scale-balanced me-2" style="font-size: 10px;"></i> Enterprise P&amp;L
                            </a>
                        </li>
                    @endif

                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.report.balance_sheet'))
                        @php
        $balanceSheetUrl = Route::has('admin.report.balance_sheet')
            ? route('admin.report.balance_sheet')
            : url('/admin/report/balance-sheet');
                        @endphp
                        <li>
                            <a href="{{ $balanceSheetUrl }}"
                                class="nav-sub-link {{ request()->routeIs('admin.report.balance_sheet*') ? 'active' : '' }}"
                                id="link-report-balance-sheet">
                                <i class="fas fa-landmark me-2" style="font-size: 10px;"></i> Balance Sheet
                            </a>
                        </li>
                    @endif

                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.report.cash_flow'))
                        @php
        $cashFlowUrl = Route::has('admin.report.cash_flow')
            ? route('admin.report.cash_flow')
            : url('/admin/report/cash-flow');
                        @endphp
                        <li>
                            <a href="{{ $cashFlowUrl }}"
                                class="nav-sub-link {{ request()->routeIs('admin.report.cash_flow*') ? 'active' : '' }}"
                                id="link-report-cash-flow">
                                <i class="fas fa-money-bill-trend-up me-2" style="font-size: 10px;"></i> Cash Flow Statement
                            </a>
                        </li>
                    @endif

                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.report.cogs'))
                        <li>
                            <a href="{{ route('admin.report.cogs') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.report.cogs') ? 'active' : '' }}"
                                id="link-report-cogs">
                                <i class="fas fa-calculator me-2" style="font-size: 10px;"></i> COGS Analysis
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- Advanced Email Marketing Module -->
        @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.email_marketing.dashboard', 'admin.email_marketing.subscribers', 'admin.email_marketing.segments', 'admin.email_marketing.campaigns', 'admin.email_marketing.templates', 'admin.email_marketing.automations', 'admin.email_marketing.settings', 'admin.email_marketing.analytics']))
            @php 
                $isEmailMarketingActive = request()->routeIs('admin.email_marketing.*') || request()->is('admin/email-marketing*'); 
            @endphp
            <a class="nav-head-link {{ $isEmailMarketingActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseEmailMarketing" role="button" aria-expanded="{{ $isEmailMarketingActive ? 'true' : 'false' }}" id="nav-btn-email-marketing">
                <span><i class="fas fa-envelope-open-text me-2"></i> Email Marketing</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isEmailMarketingActive ? 'show' : '' }}" id="collapseEmailMarketing">
                <ul class="nav-sub-list" id="sublist-email-marketing">
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.email_marketing.dashboard'))
                        @php $emDashUrl = Route::has('admin.email_marketing.dashboard') ? route('admin.email_marketing.dashboard') : url('/admin/email-marketing/dashboard'); @endphp
                        <li>
                            <a href="{{ $emDashUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.dashboard*') || request()->is('admin/email-marketing/dashboard*')) ? 'active' : '' }}" id="link-em-dashboard">
                                <i class="fas fa-chart-pie me-2" style="font-size: 10px;"></i> Dashboard
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.email_marketing.subscribers.index'))
                        @php $emSubUrl = Route::has('admin.email_marketing.subscribers.index') ? route('admin.email_marketing.subscribers.index') : url('/admin/email-marketing/subscribers'); @endphp
                        <li>
                            <a href="{{ $emSubUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.subscribers*') || request()->is('admin/email-marketing/subscribers*')) ? 'active' : '' }}" id="link-em-subscribers">
                                <i class="fas fa-users me-2" style="font-size: 10px;"></i> All Subscribers
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.email_marketing.segments.index'))
                        @php $emSegUrl = Route::has('admin.email_marketing.segments.index') ? route('admin.email_marketing.segments.index') : url('/admin/email-marketing/segments'); @endphp
                        <li>
                            <a href="{{ $emSegUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.segments*') || request()->is('admin/email-marketing/segments*')) ? 'active' : '' }}" id="link-em-segments">
                                <i class="fas fa-filter-circle-dollar me-2" style="font-size: 10px;"></i> Audience Segments
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.email_marketing.campaigns.index'))
                        @php $emCampUrl = Route::has('admin.email_marketing.campaigns.index') ? route('admin.email_marketing.campaigns.index') : url('/admin/email-marketing/campaigns'); @endphp
                        <li>
                            <a href="{{ $emCampUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.campaigns*') || request()->is('admin/email-marketing/campaigns*')) ? 'active' : '' }}" id="link-em-campaigns">
                                <i class="fas fa-paper-plane me-2" style="font-size: 10px;"></i> Campaigns
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.email_marketing.templates.index'))
                        @php $emTplUrl = Route::has('admin.email_marketing.templates.index') ? route('admin.email_marketing.templates.index') : url('/admin/email-marketing/templates'); @endphp
                        <li>
                            <a href="{{ $emTplUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.templates*') || request()->is('admin/email-marketing/templates*')) ? 'active' : '' }}" id="link-em-templates">
                                <i class="fas fa-paintbrush me-2" style="font-size: 10px;"></i> Email Template Builder
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.email_marketing.automations.index'))
                        @php $emAutoUrl = Route::has('admin.email_marketing.automations.index') ? route('admin.email_marketing.automations.index') : url('/admin/email-marketing/automations'); @endphp
                        <li>
                            <a href="{{ $emAutoUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.automations*') || request()->is('admin/email-marketing/automations*')) ? 'active' : '' }}" id="link-em-automations">
                                <i class="fas fa-diagram-project me-2" style="font-size: 10px;"></i> Automation &amp; Workflows
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.email_marketing.product_marketing'))
                        @php $emProdUrl = Route::has('admin.email_marketing.product_marketing') ? route('admin.email_marketing.product_marketing') : url('/admin/email-marketing/product-marketing'); @endphp
                        <li>
                            <a href="{{ $emProdUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.product_marketing*') || request()->is('admin/email-marketing/product-marketing*')) ? 'active' : '' }}" id="link-em-prod-marketing">
                                <i class="fas fa-box-open me-2" style="font-size: 10px;"></i> Product-Based Marketing
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.email_marketing.cart_wishlist.index'))
                        @php $emCartUrl = Route::has('admin.email_marketing.cart_wishlist_marketing') ? route('admin.email_marketing.cart_wishlist_marketing') : url('/admin/email-marketing/cart-wishlist'); @endphp
                        <li>
                            <a href="{{ $emCartUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.cart_wishlist*') || request()->is('admin/email-marketing/cart-wishlist*')) ? 'active' : '' }}" id="link-em-cart-wishlist">
                                <i class="fas fa-cart-arrow-down me-2" style="font-size: 10px;"></i> Cart &amp; Wishlist Marketing
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.email_marketing.customer_groups'))
                        @php $emGrpUrl = Route::has('admin.email_marketing.customer_groups') ? route('admin.email_marketing.customer_groups') : url('/admin/email-marketing/customer-groups'); @endphp
                        <li>
                            <a href="{{ $emGrpUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.customer_groups*') || request()->is('admin/email-marketing/customer-groups*')) ? 'active' : '' }}" id="link-em-customer-groups">
                                <i class="fas fa-people-group me-2" style="font-size: 10px;"></i> Customer Groups
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.email_marketing.scheduled'))
                        @php $emSchedUrl = Route::has('admin.email_marketing.scheduled') ? route('admin.email_marketing.scheduled') : url('/admin/email-marketing/scheduled'); @endphp
                        <li>
                            <a href="{{ $emSchedUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.scheduled*') || request()->is('admin/email-marketing/scheduled*')) ? 'active' : '' }}" id="link-em-scheduled">
                                <i class="fas fa-clock me-2" style="font-size: 10px;"></i> Scheduled Emails
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.email_marketing.analytics'))
                        @php $emQueueUrl = Route::has('admin.email_marketing.queue_logs') ? route('admin.email_marketing.queue_logs') : url('/admin/email-marketing/queue-logs'); @endphp
                        <li>
                            <a href="{{ $emQueueUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.queue_logs*') || request()->is('admin/email-marketing/queue-logs*')) ? 'active' : '' }}" id="link-em-queue-logs">
                                <i class="fas fa-list-check me-2" style="font-size: 10px;"></i> Email Queue &amp; Logs
                            </a>
                        </li>
                        @php $emAnalyticsUrl = Route::has('admin.email_marketing.analytics') ? route('admin.email_marketing.analytics') : url('/admin/email-marketing/analytics'); @endphp
                        <li>
                            <a href="{{ $emAnalyticsUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.analytics*') || request()->is('admin/email-marketing/analytics*')) ? 'active' : '' }}" id="link-em-analytics">
                                <i class="fas fa-chart-line me-2" style="font-size: 10px;"></i> Analytics &amp; Reports
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.email_marketing.ab_testing'))
                        @php $emAbUrl = Route::has('admin.email_marketing.ab_testing') ? route('admin.email_marketing.ab_testing') : url('/admin/email-marketing/ab-testing'); @endphp
                        <li>
                            <a href="{{ $emAbUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.ab_testing*') || request()->is('admin/email-marketing/ab-testing*')) ? 'active' : '' }}" id="link-em-ab-testing">
                                <i class="fas fa-flask-vial me-2" style="font-size: 10px;"></i> A/B Testing
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.email_marketing.coupons'))
                        @php $emCpnUrl = Route::has('admin.email_marketing.coupons') ? route('admin.email_marketing.coupons') : url('/admin/email-marketing/coupons'); @endphp
                        <li>
                            <a href="{{ $emCpnUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.coupons*') || request()->is('admin/email-marketing/coupons*')) ? 'active' : '' }}" id="link-em-coupons">
                                <i class="fas fa-ticket me-2" style="font-size: 10px;"></i> Coupons &amp; Promotions
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.email_marketing.suppression'))
                        @php $emSuppUrl = Route::has('admin.email_marketing.suppression') ? route('admin.email_marketing.suppression') : url('/admin/email-marketing/suppression'); @endphp
                        <li>
                            <a href="{{ $emSuppUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.suppression*') || request()->is('admin/email-marketing/suppression*')) ? 'active' : '' }}" id="link-em-suppression">
                                <i class="fas fa-ban me-2" style="font-size: 10px;"></i> Suppression List
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.email_marketing.providers'))
                        @php $emProvUrl = Route::has('admin.email_marketing.providers') ? route('admin.email_marketing.providers') : url('/admin/email-marketing/providers'); @endphp
                        <li>
                            <a href="{{ $emProvUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.providers*') || request()->is('admin/email-marketing/providers*')) ? 'active' : '' }}" id="link-em-providers">
                                <i class="fas fa-server me-2" style="font-size: 10px;"></i> SMTP &amp; Providers
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.email_marketing.tracking_settings'))
                        @php $emTrkUrl = Route::has('admin.email_marketing.tracking_settings') ? route('admin.email_marketing.tracking_settings') : url('/admin/email-marketing/tracking-settings'); @endphp
                        <li>
                            <a href="{{ $emTrkUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.tracking_settings*') || request()->is('admin/email-marketing/tracking-settings*')) ? 'active' : '' }}" id="link-em-tracking-settings">
                                <i class="fas fa-shield-halved me-2" style="font-size: 10px;"></i> Tracking &amp; Consent
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.email_marketing.settings'))
                        @php $emSetUrl = Route::has('admin.email_marketing.settings') ? route('admin.email_marketing.settings') : url('/admin/email-marketing/settings'); @endphp
                        <li>
                            <a href="{{ $emSetUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.settings*') || request()->is('admin/email-marketing/settings*')) ? 'active' : '' }}" id="link-em-settings">
                                <i class="fas fa-sliders me-2" style="font-size: 10px;"></i> Marketing Settings
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- SMS Gateway, Marketing & Notifications -->
        @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.sms.settings.index', 'admin.sms.templates.index', 'admin.sms.campaigns.index', 'admin.sms.logs.index', 'admin.sms.blacklist.index']))
            @php 
                $isSmsActive = request()->routeIs('admin.sms.*') || request()->is('admin/sms*'); 
            @endphp
            <a class="nav-head-link {{ $isSmsActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseSmsMarketing" role="button" aria-expanded="{{ $isSmsActive ? 'true' : 'false' }}" id="nav-btn-sms-marketing">
                <span><i class="fas fa-comment-sms me-2"></i> SMS Marketing</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isSmsActive ? 'show' : '' }}" id="collapseSmsMarketing">
                <ul class="nav-sub-list" id="sublist-sms-marketing">
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.sms.campaigns.index'))
                        <li>
                            <a href="{{ route('admin.sms.campaigns.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.sms.campaigns.*') ? 'active' : '' }}"
                                id="link-sms-campaigns">
                                <i class="fas fa-bullhorn me-2" style="font-size: 10px;"></i> SMS Campaigns
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.sms.templates.index'))
                        <li>
                            <a href="{{ route('admin.sms.templates.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.sms.templates.*') ? 'active' : '' }}"
                                id="link-sms-templates">
                                <i class="fas fa-file-lines me-2" style="font-size: 10px;"></i> Message Templates
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.sms.logs.index'))
                        <li>
                            <a href="{{ route('admin.sms.logs.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.sms.logs.*') ? 'active' : '' }}"
                                id="link-sms-logs">
                                <i class="fas fa-receipt me-2" style="font-size: 10px;"></i> SMS Logs &amp; History
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.sms.blacklist.index'))
                        <li>
                            <a href="{{ route('admin.sms.blacklist.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.sms.blacklist.*') ? 'active' : '' }}"
                                id="link-sms-blacklist">
                                <i class="fas fa-shield-halved me-2" style="font-size: 10px;"></i> Blacklist Numbers
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.sms.settings.index'))
                        <li>
                            <a href="{{ route('admin.sms.settings.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.sms.settings.*') ? 'active' : '' }}"
                                id="link-sms-settings">
                                <i class="fas fa-sliders me-2" style="font-size: 10px;"></i> Settings &amp; Gateway
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- Support Request Menu -->
        @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.support.index'))
            <a href="{{ route('admin.support.index') }}"
                class="nav-head-link {{ request()->routeIs('admin.support.*') ? 'active' : '' }}" id="nav-btn-support">
                <span><i class="fas fa-headset me-2"></i> Support Request</span>
                @php
    $pendingCount = \Illuminate\Support\Facades\Schema::hasTable('support_tickets')
        ? \Illuminate\Support\Facades\DB::table('support_tickets')->where('status', 'pending')->count()
        : 0;
                @endphp
                @if($pendingCount > 0)
                    <span class="badge bg-danger rounded-pill px-2 py-0.5" style="font-size: 10px;">{{ $pendingCount }}</span>
                @endif
            </a>
        @endif

        <!-- User & Roles Accordion -->
        @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.roles.index', 'admin.roles.users', 'admin.sessions.index', 'admin.activity.logs', 'admin.roles.trash']))
            @php $isRolesActive = request()->routeIs('admin.roles.*') || request()->routeIs('admin.activity.*') || request()->routeIs('admin.sessions.*'); @endphp
            <a class="nav-head-link {{ $isRolesActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseRoles" role="button" aria-expanded="{{ $isRolesActive ? 'true' : 'false' }}" id="nav-btn-roles">
                <span><i class="fas fa-user-shield me-2"></i> User &amp; Roles</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isRolesActive ? 'show' : '' }}" id="collapseRoles">
                <ul class="nav-sub-list" id="sublist-roles">
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.roles.index'))
                        <li><a href="{{ route('admin.roles.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.roles.index*') ? 'active' : '' }}"
                                id="link-roles-manage"><i class="fas fa-id-badge me-2" style="font-size: 10px;"></i> Role Management</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.roles.users'))
                        <li><a href="{{ route('admin.roles.users') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.roles.users*') ? 'active' : '' }}"
                                id="link-roles-users"><i class="fas fa-users-gear me-2" style="font-size: 10px;"></i> User Management</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.sessions.index'))
                        <li><a href="{{ route('admin.sessions.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.sessions.index*') ? 'active' : '' }}"><i class="fas fa-tower-broadcast me-2" style="font-size: 10px;"></i> Live Sessions</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.activity.logs'))
                        <li><a href="{{ route('admin.activity.logs') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.activity.logs*') ? 'active' : '' }}"><i class="fas fa-clipboard-list me-2" style="font-size: 10px;"></i> Audit Logs</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.roles.trash'))
                        <li><a href="{{ route('admin.roles.trash') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.roles.trash*') ? 'active' : '' }}"><i class="fas fa-trash-can me-2" style="font-size: 10px;"></i> Trashed Accounts</a></li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- IP & Network Security Accordion -->
        @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.security.dashboard', 'admin.security.ips.index', 'admin.security.orders.index', 'admin.security.blocking.ips', 'admin.security.settings.index']))
            @php 
                $isSecurityActive = request()->routeIs('admin.security.*') || request()->is('admin/security*'); 
            @endphp
            <a class="nav-head-link {{ $isSecurityActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseSecurity" role="button" aria-expanded="{{ $isSecurityActive ? 'true' : 'false' }}" id="nav-btn-security">
                <span class="text-nowrap"><i class="fas fa-shield-halved me-2"></i> IP &amp; Network Security</span>
                <span class="d-flex align-items-center gap-1">
                    @if($securityAlertCount > 0)
                        <span class="badge bg-danger rounded-pill" id="sidebar-sec-alert-badge" style="font-size: 9.5px; padding: 2px 6px;" title="Unread Security Alerts">{{ $securityAlertCount }}</span>
                    @elseif($suspiciousOrderBadgeCount > 0)
                        <span class="badge bg-warning text-dark rounded-pill" style="font-size: 9.5px; padding: 2px 6px;" title="Suspicious Orders">{{ $suspiciousOrderBadgeCount }}</span>
                    @endif
                    <i class="fas fa-chevron-down"></i>
                </span>
            </a>
            <div class="collapse {{ $isSecurityActive ? 'show' : '' }}" id="collapseSecurity">
                <ul class="nav-sub-list" id="sublist-security">
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.security.dashboard'))
                        <li>
                            <a href="{{ route('admin.security.dashboard') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.security.dashboard') ? 'active' : '' }}"
                                id="link-sec-dashboard">
                                <i class="fas fa-chart-line me-2" style="font-size: 10px;"></i> Security Dashboard
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.security.ips.index'))
                        <li>
                            <a href="{{ route('admin.security.ips.index') }}"
                                class="nav-sub-link {{ (request()->routeIs('admin.security.ips.index') && !request()->filled('risk_level') && !request()->filled('view')) ? 'active' : '' }}"
                                id="link-sec-all-ips">
                                <i class="fas fa-network-wired me-2" style="font-size: 10px;"></i> All IP Addresses
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.security.logs.index'))
                        <li>
                            <a href="{{ route('admin.security.logs.index') }}"
                                class="nav-sub-link {{ (request()->routeIs('admin.security.logs.index') && !request()->filled('activity_type') && !request()->filled('security_status')) ? 'active' : '' }}"
                                id="link-sec-activity-history">
                                <i class="fas fa-timeline me-2" style="font-size: 10px;"></i> IP Activity History
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.security.blocking.ips'))
                        <li>
                            <a href="{{ route('admin.security.blocking.ips') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.security.blocking.ips') ? 'active' : '' }}"
                                id="link-sec-blocked-ips">
                                <i class="fas fa-ban me-2" style="font-size: 10px;"></i> Blocked IPs
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.security.blocking.networks'))
                        <li>
                            <a href="{{ route('admin.security.blocking.networks') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.security.blocking.networks') ? 'active' : '' }}"
                                id="link-sec-network-monitoring">
                                <i class="fas fa-diagram-project me-2" style="font-size: 10px;"></i> Network Monitoring
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.security.blocking.users'))
                        <li>
                            <a href="{{ route('admin.security.blocking.users') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.security.blocking.users') ? 'active' : '' }}"
                                id="link-sec-user-blocking">
                                <i class="fas fa-user-xmark me-2" style="font-size: 10px;"></i> User Blocking
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.security.logs.index'))
                        <li>
                            <a href="{{ route('admin.security.logs.index', ['activity_type' => 'user']) }}"
                                class="nav-sub-link {{ (request()->routeIs('admin.security.logs.index') && request('activity_type') === 'user') ? 'active' : '' }}"
                                id="link-sec-user-activity">
                                <i class="fas fa-user-clock me-2" style="font-size: 10px;"></i> User Activity
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.security.ips.index'))
                        <li>
                            <a href="{{ route('admin.security.ips.index', ['risk_level' => 'high']) }}"
                                class="nav-sub-link {{ (request()->routeIs('admin.security.ips.index') && request('risk_level') === 'high') ? 'active' : '' }}"
                                id="link-sec-suspicious-ips">
                                <i class="fas fa-triangle-exclamation me-2" style="font-size: 10px;"></i> Suspicious IPs
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.security.logs.index'))
                        <li>
                            <a href="{{ route('admin.security.logs.index', ['security_status' => 'flagged']) }}"
                                class="nav-sub-link {{ (request()->routeIs('admin.security.logs.index') && request('security_status') === 'flagged') ? 'active' : '' }}"
                                id="link-sec-spam-detection">
                                <i class="fas fa-shield-virus me-2" style="font-size: 10px;"></i> Spam Detection
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.security.orders.clusters'))
                        <li>
                            <a href="{{ route('admin.security.orders.clusters') }}"
                                class="nav-sub-link d-flex justify-content-between align-items-center {{ request()->routeIs('admin.security.orders.clusters') ? 'active' : '' }}"
                                id="link-sec-multi-order">
                                <span><i class="fas fa-cart-shopping me-2" style="font-size: 10px;"></i> Multiple Order Detection</span>
                                @if($suspiciousOrderBadgeCount > 0)
                                    <span class="badge bg-warning text-dark rounded-pill" style="font-size: 9px;">{{ $suspiciousOrderBadgeCount }}</span>
                                @endif
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.security.logs.index'))
                        <li>
                            <a href="{{ route('admin.security.logs.index', ['activity_type' => 'form_submit']) }}"
                                class="nav-sub-link {{ (request()->routeIs('admin.security.logs.index') && request('activity_type') === 'form_submit') ? 'active' : '' }}"
                                id="link-sec-multi-form">
                                <i class="fas fa-file-lines me-2" style="font-size: 10px;"></i> Multiple Form Submission
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.security.logs.index'))
                        <li>
                            <a href="{{ route('admin.security.logs.index', ['activity_type' => 'login_failed']) }}"
                                class="nav-sub-link {{ (request()->routeIs('admin.security.logs.index') && request('activity_type') === 'login_failed') ? 'active' : '' }}"
                                id="link-sec-login-attack">
                                <i class="fas fa-user-lock me-2" style="font-size: 10px;"></i> Login Attack Monitoring
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.security.urls.index'))
                        <li>
                            <a href="{{ route('admin.security.urls.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.security.urls.*') ? 'active' : '' }}"
                                id="link-sec-url-attack">
                                <i class="fas fa-link-slash me-2" style="font-size: 10px;"></i> URL Attack Monitoring
                            </a>
                        </li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.security.settings.index'))
                        <li>
                            <a href="{{ route('admin.security.settings.index', ['tab' => 'ratelimit']) }}"
                                class="nav-sub-link {{ (request()->routeIs('admin.security.settings.*') && request('tab') === 'ratelimit') ? 'active' : '' }}"
                                id="link-sec-rate-limit">
                                <i class="fas fa-gauge-high me-2" style="font-size: 10px;"></i> Rate Limit Settings
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.security.settings.index', ['tab' => 'rules']) }}"
                                class="nav-sub-link {{ (request()->routeIs('admin.security.settings.*') && request('tab') === 'rules') ? 'active' : '' }}"
                                id="link-sec-rules">
                                <i class="fas fa-scale-balanced me-2" style="font-size: 10px;"></i> Security Rules
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.security.settings.index', ['tab' => 'notifications']) }}"
                                class="nav-sub-link {{ (request()->routeIs('admin.security.settings.*') && request('tab') === 'notifications') ? 'active' : '' }}"
                                id="link-sec-email-notifications">
                                <i class="fas fa-bell me-2" style="font-size: 10px;"></i> Email Notifications
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.security.settings.index') }}"
                                class="nav-sub-link {{ (request()->routeIs('admin.security.settings.*') && !request()->filled('tab')) ? 'active' : '' }}"
                                id="link-sec-settings">
                                <i class="fas fa-sliders me-2" style="font-size: 10px;"></i> Security Settings
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- Site Content Accordion -->
        @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.content.pages.index', 'admin.content.blogs.index', 'admin.content.blogs.comments', 'admin.content.menus.index', 'admin.content.widgets.index', 'admin.content.media.index', 'admin.media.index', 'admin.theme.setting.display.index']))
            @php 
                $isMediaActive = request()->routeIs('admin.content.media.*') || request()->routeIs('admin.media.*');
    $isDisplayActive = request()->routeIs('admin.theme.setting.display.*') || request()->routeIs('admin.theme.display.*') || request()->is('admin/theme-setting/display*');
    $isContentActive = request()->routeIs('admin.content.*') || $isMediaActive || $isDisplayActive; 
            @endphp
            <a class="nav-head-link {{ $isContentActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseSiteContent" role="button" aria-expanded="{{ $isContentActive ? 'true' : 'false' }}"
                id="nav-btn-site-content">
                <span><i class="fas fa-layer-group me-2"></i> Site Content</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isContentActive ? 'show' : '' }}" id="collapseSiteContent">
                <ul class="nav-sub-list" id="sublist-site-content">
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.content.pages.index'))
                        <li><a href="{{ route('admin.content.pages.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.content.pages.*') ? 'active' : '' }}"
                                id="link-cnt-pages"><i class="far fa-file-alt me-2" style="font-size: 10px;"></i> Pages</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.content.blogs.index'))
                        <li><a href="{{ route('admin.content.blogs.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.content.blogs.index') || request()->routeIs('admin.content.blogs.create') || request()->routeIs('admin.content.blogs.edit') ? 'active' : '' }}"
                                id="link-cnt-blogs"><i class="fas fa-blog me-2" style="font-size: 10px;"></i> Blog Posts</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.content.blogs.comments'))
                        <li><a href="{{ route('admin.content.blogs.comments') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.content.blogs.comments*') ? 'active' : '' }}">
                                <i class="fas fa-comments me-2" style="font-size: 10px;"></i> Comments
                            </a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.content.menus.index'))
                        <li><a href="{{ route('admin.content.menus.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.content.menus.*') ? 'active' : '' }}"
                                id="link-cnt-menus"><i class="fas fa-bars me-2" style="font-size: 10px;"></i> Menus</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.content.widgets.index'))
                        <li><a href="{{ route('admin.content.widgets.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.content.widgets.*') ? 'active' : '' }}"
                                id="link-cnt-widgets"><i class="fas fa-puzzle-piece me-2" style="font-size: 10px;"></i> Widgets</a></li>
                    @endif

                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.theme.setting.display.index'))
                        <li>
                            @php
        $dynamicDisplayUrl = Route::has('admin.theme.setting.display.index')
            ? route('admin.theme.setting.display.index')
            : (Route::has('admin.theme.display.index') ? route('admin.theme.display.index') : url('/admin/theme-setting/display'));
                            @endphp
                            <a href="{{ $dynamicDisplayUrl }}"
                                class="nav-sub-link {{ $isDisplayActive ? 'active' : '' }}"
                                id="link-cnt-display-widgets"
                                title="Display Widgets & Popups">
                                <i class="fas fa-wand-magic-sparkles me-2" style="font-size: 10px;"></i> Display Widgets
                            </a>
                        </li>
                    @endif
                    
                    @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.content.media.index', 'admin.media.index']))
                        <li>
                            <a href="{{ route('admin.content.media.index') }}"
                                class="nav-sub-link d-flex justify-content-between align-items-center media-chooser-menu-item mb-1 {{ $isMediaActive ? 'active' : '' }}"
                                id="link-cnt-media"
                                title="Global Media Chooser & Asset Picker">
                                <span><i class="fas fa-photo-film me-2"></i> Media Library</span>
                                <span class="badge bg-primary text-white px-1.5 py-0 media-badge-tag" style="font-size: 9px;">Global</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- Settings Dropdown -->
        @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.shipping.index', 'admin.payment.setting.gateways', 'admin.system.setting.smtp', 'admin.system.setting.courier', 'admin.system.setting.fraud']))
            @php $isSettingActive = (request()->routeIs('admin.system.setting.*') || request()->routeIs('admin.payment.*') || request()->routeIs('admin.shipping.*')); @endphp
            <a class="nav-head-link {{ $isSettingActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#settingCollapse" role="button" aria-expanded="{{ $isSettingActive ? 'true' : 'false' }}" id="nav-btn-setting">
                <span><i class="fas fa-gear me-2"></i> Settings</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isSettingActive ? 'show' : '' }}" id="settingCollapse">
                <ul class="nav-sub-list" id="sublist-setting">
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.shipping.index'))
                        <li><a href="{{ route('admin.shipping.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.shipping.*') ? 'active' : '' }}"><i
                                    class="fas fa-ship me-2" style="font-size: 10px;"></i> Shipping Settings</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.payment.setting.gateways'))
                        <li><a href="{{ route('admin.payment.setting.gateways') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.payment.*') ? 'active' : '' }}"><i
                                    class="fas fa-credit-card me-2" style="font-size: 10px;"></i> Payment Gateways</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.system.setting.smtp'))
                        <li><a href="{{ route('admin.system.setting.smtp') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.system.setting.smtp') ? 'active' : '' }}"><i
                                    class="fas fa-envelope me-2" style="font-size: 10px;"></i> SMTP Settings</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.system.setting.courier'))
                        <li><a href="{{ route('admin.system.setting.courier') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.system.setting.courier*') ? 'active' : '' }}"><i
                                    class="fas fa-boxes-packing me-2" style="font-size: 10px;"></i> Courier Settings</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.system.setting.fraud'))
                        <li><a href="{{ route('admin.system.setting.fraud') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.system.setting.fraud') ? 'active' : '' }}"><i
                                    class="fas fa-user-secret me-2" style="font-size: 10px;"></i> Fraud Settings</a></li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- Theme Setting -->
        @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.setting.general', 'admin.theme.setting.home_builder', 'admin.theme.setting.featured_products', 'admin.theme.setting.slider', 'admin.theme.setting.flash_sale', 'admin.theme.setting.popular_categories', 'admin.theme.setting.topbar', 'admin.theme.setting.colors', 'admin.theme.setting.footer', 'admin.theme.setting.product_card', 'admin.theme.setting.cart_features', 'admin.settings.auth.index']))
            @php $isThemeActive = (request()->routeIs('admin.theme.setting.*') || request()->routeIs('admin.setting.general') || request()->routeIs('admin.settings.auth.*')); @endphp
            <a class="nav-head-link {{ $isThemeActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseThemeSetting" role="button" aria-expanded="{{ $isThemeActive ? 'true' : 'false' }}"
                id="nav-btn-theme-setting">
                <span><i class="fas fa-palette me-2"></i> Theme Setting</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isThemeActive ? 'show' : '' }}" id="collapseThemeSetting">
                <ul class="nav-sub-list" id="sublist-theme-setting">
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.setting.general'))
                        <li><a href="{{ route('admin.setting.general') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.setting.general') ? 'active' : '' }}"><i
                                    class="fas fa-sliders me-2" style="font-size: 10px;"></i> General Settings</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.theme.setting.home_builder'))
                        <li><a href="{{ route('admin.theme.setting.home_builder') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.theme.setting.home_builder*') ? 'active' : '' }}"
                                id="link-thm-builder"><i class="fas fa-house-laptop me-2" style="font-size: 10px;"></i> Home Builder</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.theme.setting.featured_products'))
                        <li><a href="{{ route('admin.theme.setting.featured_products') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.theme.setting.featured_products*') ? 'active' : '' }}">
                                <i class="fas fa-fire me-2" style="font-size: 10px;"></i> Featured Products</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.theme.setting.slider'))
                        <li><a href="{{ route('admin.theme.setting.slider') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.theme.setting.slider*') ? 'active' : '' }}"
                                id="link-thm-slider"><i class="fas fa-film me-2" style="font-size: 10px;"></i> Hero Slider</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.theme.setting.flash_sale'))
                        <li><a href="{{ route('admin.theme.setting.flash_sale') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.theme.setting.flash_sale*') ? 'active' : '' }}"
                                id="link-thm-flash"><i class="fas fa-rectangle-ad me-2" style="font-size: 10px;"></i> Promotions &amp; Slides</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.theme.setting.popular_categories'))
                        <li><a href="{{ route('admin.theme.setting.popular_categories') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.theme.setting.popular_categories*') ? 'active' : '' }}"
                                id="link-thm-pop-cat"><i class="fas fa-heart me-2" style="font-size: 10px;"></i> Popular Categories</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.theme.setting.topbar'))
                        <li><a href="{{ route('admin.theme.setting.topbar') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.theme.setting.topbar*') ? 'active' : '' }}"
                                id="link-thm-topbar"><i class="fas fa-bullhorn me-2" style="font-size: 10px;"></i> Topbar Settings</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.theme.setting.colors'))
                        <li><a href="{{ route('admin.theme.setting.colors') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.theme.setting.colors*') ? 'active' : '' }}"
                                id="link-thm-colors"><i class="fas fa-paint-brush me-2" style="font-size: 10px;"></i> Theme Colors</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.theme.setting.footer'))
                        <li><a href="{{ route('admin.theme.setting.footer') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.theme.setting.footer*') ? 'active' : '' }}"
                                id="link-thm-footer"><i class="fas fa-shoe-prints me-2" style="font-size: 10px;"></i> Footer Customize</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.theme.setting.product_card'))
                        <li><a href="{{ route('admin.theme.setting.product_card') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.theme.setting.product_card') ? 'active' : '' }}">
                                <i class="fas fa-th me-2" style="font-size: 10px;"></i> Product Cards</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.theme.setting.cart_features'))
                        <li><a href="{{ route('admin.theme.setting.cart_features') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.theme.setting.cart_features') ? 'active' : '' }}">
                                <i class="fas fa-cart-shopping me-2" style="font-size: 10px;"></i> Site Features</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.settings.auth.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.settings.auth.*') ? 'active' : '' }}" href="{{ route('admin.settings.auth.index') }}">
                                <i class="fas fa-user-shield me-2"></i><span>Login &amp; 2FA</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- Form Builder -->
        @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.forms.index', 'admin.forms.create', 'admin.forms.submissions.index', 'admin.forms.submissions']))
            @php $isFormActive = request()->routeIs('admin.forms.*'); @endphp
            <a class="nav-head-link {{ $isFormActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseFormBuilder" role="button" aria-expanded="{{ $isFormActive ? 'true' : 'false' }}"
                id="nav-btn-form-builder">
                <span><i class="fas fa-wand-magic-sparkles me-2"></i> Form Builder</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isFormActive ? 'show' : '' }}" id="collapseFormBuilder">
                <ul class="nav-sub-list" id="sublist-form-builder">
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.forms.index'))
                        <li><a href="{{ route('admin.forms.index') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.forms.index') ? 'active' : '' }}"
                                id="link-form-list"><i class="fas fa-list me-2" style="font-size: 10px;"></i> All Forms</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAccess('admin.forms.create'))
                        <li><a href="{{ route('admin.forms.create') }}"
                                class="nav-sub-link {{ request()->routeIs('admin.forms.create') ? 'active' : '' }}"
                                id="link-form-create"><i class="fas fa-plus-circle me-2" style="font-size: 10px;"></i> Create New Form</a></li>
                    @endif
                    @if(!$u || $isAdminOrSuper || $u->hasAnyAccess(['admin.forms.submissions.index', 'admin.forms.submissions']))
                        @php
        $submissionsUrl = Route::has('admin.forms.submissions.index')
            ? route('admin.forms.submissions.index')
            : (Route::has('admin.forms.submissions') ? route('admin.forms.submissions') : url('/admin/forms/submissions'));
                        @endphp
                        <li><a href="{{ $submissionsUrl }}"
                                class="nav-sub-link {{ request()->routeIs('admin.forms.submissions*') ? 'active' : '' }}"
                                id="link-form-submissions">
                                <i class="fas fa-address-book me-2" style="font-size: 10px;"></i> Submissions
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif

        <!-- Logout Action -->
        <form action="{{ route('admin.logout') }}" method="POST" class="mt-3" id="admin-form-logout-sidebar">
            @csrf
            <button type="submit" class="nav-head-link w-100 border-0 bg-transparent text-danger fw-bold" id="nav-btn-logout">
                <span><i class="fas fa-sign-out-alt me-2 text-danger"></i> Logout</span>
            </button>
        </form>
    </div>

    <!-- Sidebar Bottom User Details -->
    <div class="user-footer-badge" id="admin-sidebar-footer-profile">
        <div class="user-avatar-sq flex-shrink-0" id="admin-footer-avatar">
            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
        </div>
        <div class="overflow-hidden flex-grow-1" id="admin-footer-meta-box">
            <div class="text-white fw-bold text-truncate" style="font-size: 13px;" id="admin-footer-user-name">
                {{ auth()->user()->name ?? 'Super Admin' }}
            </div>
            <div class="text-secondary small text-truncate" style="font-size: 11px;" id="admin-footer-user-role">
                {{ auth()->user()->role ?? 'superAdmin' }}
            </div>
        </div>
        <form action="{{ route('admin.logout') }}" method="POST" class="m-0 p-0 flex-shrink-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Sign Out">
                <i class="fas fa-power-off"></i>
            </button>
        </form>
    </div>
</aside>

<script>
    // Universal Mobile Sidebar Toggle & Close Handlers
    window.openAdminSidebar = function() {
        const sidebar = document.getElementById('admin-main-sidebar-container');
        const backdrop = document.getElementById('adminSidebarBackdrop');
        if (sidebar) sidebar.classList.add('mobile-sidebar-open');
        if (backdrop) backdrop.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    window.closeAdminSidebar = function() {
        const sidebar = document.getElementById('admin-main-sidebar-container');
        const backdrop = document.getElementById('adminSidebarBackdrop');
        if (sidebar) sidebar.classList.remove('mobile-sidebar-open');
        if (backdrop) backdrop.classList.remove('active');
        document.body.style.overflow = '';
    };
</script>