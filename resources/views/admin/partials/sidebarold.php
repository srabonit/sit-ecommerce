 @php
    $globalSettings = DB::table('settings')->pluck('value', 'key');

    $siteTitle = $globalSettings['site_title'] ?? config('app.name', 'Shop Now BD');
    $siteTagline = $globalSettings['site_tagline'] ?? 'Admin Panel';

    // Favicon path processing (Universal uploads & storage support for local and live)
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
@endphp

<style>
    .admin-dark-sidebar {
        width: 260px;
        background-color: #03172e;
        color: #94a3b8;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
        font-size: 13px;
        border-right: 1px solid #0f2b48;
        transition: all 0.3s ease-in-out;
    }

    @media (max-width: 991.98px) {
        .admin-dark-sidebar {
            position: fixed;
            top: 0;
            left: -270px;
            z-index: 1090;
            height: 100vh;
            box-shadow: 5px 0 25px rgba(0, 0, 0, 0.6);
        }

        .admin-dark-sidebar.mobile-sidebar-open {
            left: 0;
        }
    }

    .admin-sidebar-logo-area {
        padding: 16px 18px;
        background: #021020;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #0f2b48;
        text-decoration: none;
    }

    /* Clean modern square-rounded favicon box without ugly borders */
    .admin-brand-favicon-circle {
        width: 38px;
        height: 38px;
        min-width: 38px;
        min-height: 38px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        transition: transform 0.2s ease;
    }

    .admin-brand-favicon-circle:hover {
        transform: scale(1.05);
        border-color: rgba(255, 255, 255, 0.25);
    }

    .admin-brand-favicon-circle img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 3px;
        image-rendering: -webkit-optimize-contrast;
    }

    .admin-brand-favicon-circle.fallback-avatar {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #ffffff;
        font-size: 16px;
    }

    /* Mobile Close Button */
    .mobile-sidebar-close-btn {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #94a3b8;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .mobile-sidebar-close-btn:hover {
        background: #ef4444;
        color: #ffffff;
        border-color: #ef4444;
    }

    .admin-nav-list {
        padding: 12px 10px 40px;
        overflow-y: auto;
        flex-grow: 1;
    }

    .admin-nav-list::-webkit-scrollbar {
        width: 5px;
    }

    .admin-nav-list::-webkit-scrollbar-thumb {
        background: #1e3a5f;
        border-radius: 4px;
    }

    .nav-head-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 9px 14px;
        color: #94a3b8;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.2s ease;
        margin-bottom: 2px;
        cursor: pointer;
    }

    .nav-head-link:hover,
    .nav-head-link.active {
        color: #ffffff;
        background: #0f2b48;
    }

    .nav-head-link.active-dashboard {
        background: #f59e0b;
        color: #000000;
        font-weight: 700;
    }

    .nav-sub-list {
        list-style: none;
        padding-left: 28px;
        margin: 4px 0 8px;
        border-left: 1px solid #1e3a5f;
        margin-left: 20px;
    }

    .nav-sub-link {
        display: block;
        padding: 6px 12px;
        color: #8da2bd;
        text-decoration: none;
        font-size: 12.5px;
        border-radius: 6px;
        transition: 0.2s;
    }

    .nav-sub-link:hover,
    .nav-sub-link.active {
        color: #ffffff;
        background: #0b223c;
    }

    .user-footer-badge {
        padding: 14px 18px;
        background: #021020;
        border-top: 1px solid #0f2b48;
        display: flex;
        align-items: center;
        gap: 12px;
    }
</style>

<aside class="admin-dark-sidebar" id="admin-main-sidebar-container">
    <!-- Branding Header: Clean favicon + Title & Tagline -->
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
                <h6 class="fw-bold text-white mb-0 text-truncate" id="admin-brand-name-title">{{ $siteTitle }}</h6>
                <small class="text-secondary d-block text-truncate" style="font-size: 11px;"
                    id="admin-brand-portal-sub">{{ $siteTagline }}</small>
            </div>
        </a>

        <!-- Mobile Close Button -->
        <button type="button" class="mobile-sidebar-close-btn d-lg-none ms-2" onclick="closeAdminSidebar()"
            title="Close Sidebar">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="admin-nav-list" id="admin-sidebar-scroll-wrapper">
        <!-- Dashboard Link -->
        <a href="{{ route('admin.dashboard') }}"
            class="nav-head-link {{ request()->routeIs('admin.dashboard') ? 'active-dashboard' : '' }}"
            id="nav-btn-dashboard">
            <span id="nav-icon-label-dash"><i class="fas fa-tachometer-alt me-2 text-primary"></i> Dashboard</span>
        </a>

        <!-- POS -->
        <a href="{{ route('admin.pos.index') }}"
            class="nav-head-link {{ request()->routeIs('admin.pos.*') ? 'active' : '' }}" id="nav-btn-pos">
            <span><i class="fas fa-cash-register me-2 text-primary"></i> POS</span>
        </a>

        <!-- Order Accordion -->
        <a class="nav-head-link" data-bs-toggle="collapse" href="#collapseOrder" role="button" id="nav-btn-order">
            <span><i class="fas fa-shopping-cart me-2 text-primary"></i> Order</span>
            <i class="fas fa-chevron-down fs-xs"></i>
        </a>
        <div class="collapse {{ request()->routeIs('admin.order.*') ? 'show' : '' }}" id="collapseOrder">
            <ul class="nav-sub-list" id="sublist-order">
                <li><a href="{{ route('admin.order.online') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.order.online') ? 'active' : '' }}"
                        id="link-order-online">Online Orders</a></li>
                <li><a href="{{ route('admin.order.incomplete') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.order.incomplete') ? 'active' : '' }}"
                        id="link-order-incomplete">Incomplete Orders</a></li>
                <li><a href="{{ route('admin.order.return') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.order.return') ? 'active' : '' }}"
                        id="link-order-return">Return Orders</a></li>
                <li><a href="{{ route('admin.order.exchange') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.order.exchange') ? 'active' : '' }}"
                        id="link-order-exchange">Exchange Orders</a></li>
                <li><a href="{{ route('admin.order.fraud') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.order.fraud') ? 'active' : '' }}"
                        id="link-order-fraud">Fraud Checker</a></li>
            </ul>
        </div>

        <!-- ================= NEW ADVANCED COURIER MANAGEMENT ACCORDION ================= -->
        <a class="nav-head-link" data-bs-toggle="collapse" href="#collapseCourier" role="button" id="nav-btn-courier">
            <span><i class="fas fa-truck-fast me-2 text-primary"></i> Courier Manage</span>
            <i class="fas fa-chevron-down fs-xs"></i>
        </a>
        <div class="collapse {{ request()->routeIs('admin.courier.*') ? 'show' : '' }}" id="collapseCourier">
            <ul class="nav-sub-list" id="sublist-courier">
                <li>
                    <a href="{{ route('admin.courier.dashboard') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.courier.dashboard') ? 'active' : '' }}"
                        id="link-courier-dashboard">
                        <i class="fas fa-chart-pie me-1.5 text-warning"></i> Courier Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.courier.index') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.courier.index') ? 'active' : '' }}"
                        id="link-courier-all-orders">
                        <i class="fas fa-boxes-packing me-1.5 text-info"></i> All Courier Orders
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.courier.booking') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.courier.booking') ? 'active' : '' }}"
                        id="link-courier-booking">
                        <i class="fas fa-plus-circle me-1.5 text-success"></i> New Booking
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.courier.settlements') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.courier.settlements') ? 'active' : '' }}"
                        id="link-courier-settlements">
                        <i class="fas fa-money-bill-transfer me-1.5 text-primary"></i> COD & Cost Settlement
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.system.setting.courier') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.system.setting.courier*') ? 'active' : '' }}"
                        id="link-courier-settings">
                        <i class="fas fa-sliders me-1.5 text-secondary"></i> Courier Gateways
                    </a>
                </li>
            </ul>
        </div>

        <!-- Product Accordion -->
        <a class="nav-head-link" data-bs-toggle="collapse" href="#collapseProduct" role="button" id="nav-btn-product">
            <span><i class="fas fa-box-open me-2 text-primary"></i> Product</span>
            <i class="fas fa-chevron-down fs-xs"></i>
        </a>
        <div class="collapse {{ request()->routeIs('admin.product.*') ? 'show' : '' }}" id="collapseProduct">
            <ul class="nav-sub-list" id="sublist-product">
                <li><a href="{{ route('admin.product.index') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.product.index') ? 'active' : '' }}"
                        id="link-prod-all">Product</a></li>
                <li><a href="{{ route('admin.product.category') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.product.category*') ? 'active' : '' }}"
                        id="link-prod-cat">Category</a></li>
                <li><a href="{{ route('admin.product.brand') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.product.brand*') ? 'active' : '' }}"
                        id="link-prod-brand">Brand</a></li>
                <li><a href="{{ route('admin.product.barcode') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.product.barcode*') ? 'active' : '' }}"
                        id="link-prod-barcode">Barcode Print</a></li>
            </ul>
        </div>

        <!-- Support Request Menu -->
        <a href="{{ route('admin.support.index') }}"
            class="nav-head-link {{ request()->routeIs('admin.support.*') ? 'active' : '' }}" id="nav-btn-support">
            <span><i class="fas fa-headset me-2 text-primary"></i> Support Request</span>
            @php
                $pendingCount = \Illuminate\Support\Facades\Schema::hasTable('support_tickets')
                    ? \Illuminate\Support\Facades\DB::table('support_tickets')->where('status', 'pending')->count()
                    : 0;
            @endphp
            @if($pendingCount > 0)
                <span class="badge bg-danger rounded-pill">{{ $pendingCount }}</span>
            @endif
        </a>

        <!-- User & Roles Accordion -->
        <a class="nav-head-link" data-bs-toggle="collapse" href="#collapseRoles" role="button" id="nav-btn-roles">
            <span><i class="fas fa-user-shield me-2 text-primary"></i> User & Roles</span>
            <i class="fas fa-chevron-down fs-xs"></i>
        </a>
        <div class="collapse {{ request()->routeIs('admin.roles.*') ? 'show' : '' }}" id="collapseRoles">
            <ul class="nav-sub-list" id="sublist-roles">
                <li><a href="{{ route('admin.roles.index') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.roles.index*') ? 'active' : '' }}"
                        id="link-roles-manage">Role Management</a></li>
                <li><a href="{{ route('admin.roles.users') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.roles.users*') ? 'active' : '' }}"
                        id="link-roles-users">User Management</a></li>
            </ul>
        </div>

        <!-- Branch Accordion -->
        <a class="nav-head-link" data-bs-toggle="collapse" href="#collapseBranch" role="button" id="nav-btn-branch">
            <span><i class="fas fa-code-branch me-2 text-primary"></i> Branch</span>
            <i class="fas fa-chevron-down fs-xs"></i>
        </a>
        <div class="collapse {{ request()->routeIs('admin.branch.*') ? 'show' : '' }}" id="collapseBranch">
            <ul class="nav-sub-list" id="sublist-branch">
                <li><a href="{{ route('admin.branch.index') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.branch.index*') ? 'active' : '' }}"
                        id="link-branch-all">All Branches</a></li>
                <li><a href="{{ route('admin.branch.assign.history') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.branch.assign.history*') ? 'active' : '' }}"
                        id="link-branch-assign-hist">Assign History</a></li>
                <li><a href="{{ route('admin.branch.new.assign') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.branch.new.assign*') ? 'active' : '' }}"
                        id="link-branch-new-assign">New Assign</a></li>
                <li><a href="{{ route('admin.branch.products') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.branch.products*') ? 'active' : '' }}"
                        id="link-branch-products">Branch Products</a></li>
                <li><a href="{{ route('admin.branch.orders') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.branch.orders*') ? 'active' : '' }}"
                        id="link-branch-orders">Branch Orders</a></li>
            </ul>
        </div>

        <!-- Site Content Accordion -->
        <a class="nav-head-link" data-bs-toggle="collapse" href="#collapseSiteContent" role="button"
            id="nav-btn-site-content">
            <span><i class="fas fa-layer-group me-2 text-primary"></i> Site Content</span>
            <i class="fas fa-chevron-down fs-xs"></i>
        </a>
        <div class="collapse {{ request()->routeIs('admin.content.*') ? 'show' : '' }}" id="collapseSiteContent">
            <ul class="nav-sub-list" id="sublist-site-content">
                <li><a href="{{ route('admin.content.pages.index') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.content.pages.*') ? 'active' : '' }}"
                        id="link-cnt-pages"><i class="far fa-file-alt me-1"></i> Pages</a></li>
                <li><a href="{{ route('admin.content.blogs.index') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.content.blogs.*') ? 'active' : '' }}"
                        id="link-cnt-blogs"><i class="fas fa-blog me-1"></i> Blog Posts</a></li>
                <li><a href="{{ route('admin.content.blogs.comments') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.content.blogs.comments*') ? 'active' : '' }}">
                        <i class="fas fa-comments me-2"></i> Comments
                    </a></li>
                <li><a href="{{ route('admin.content.menus.index') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.content.menus.*') ? 'active' : '' }}"
                        id="link-cnt-menus"><i class="fas fa-bars me-1"></i> Menus</a></li>
                <li><a href="{{ route('admin.content.widgets.index') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.content.widgets.*') ? 'active' : '' }}"
                        id="link-cnt-widgets"><i class="fas fa-puzzle-piece me-1"></i> Widgets</a></li>
                <li><a href="{{ route('admin.content.media.index') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.content.media.*') ? 'active' : '' }}"
                        id="link-cnt-media"><i class="fas fa-photo-video me-1"></i> Media Library</a></li>
            </ul>
        </div>

        <!-- Setting Dropdown -->
        <a class="nav-head-link" data-bs-toggle="collapse" href="#settingCollapse" role="button" id="nav-btn-setting">
            <span><i class="fas fa-gear me-2 text-primary"></i> Config Setting</span>
            <i class="fas fa-chevron-down fs-xs"></i>
        </a>
        <div class="collapse {{ (request()->routeIs('admin.setting.*') || request()->routeIs('admin.system.setting.*') || request()->routeIs('admin.payment.*') || request()->routeIs('admin.shipping.*')) ? 'show' : '' }}"
            id="settingCollapse">
            <ul class="nav-sub-list" id="sublist-setting">
                <li><a href="{{ route('admin.shipping.index') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.shipping.*') ? 'active' : '' }}"><i
                            class="fas fa-ship me-1"></i> Shipping Settings</a></li>
                <li><a href="{{ route('admin.payment.setting.gateways') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.payment.*') ? 'active' : '' }}"><i
                            class="fas fa-cc-mastercard me-1"></i> Payment Gateways &amp; QR</a></li>
                <li><a href="{{ route('admin.system.setting.smtp') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.system.setting.smtp') ? 'active' : '' }}"><i
                            class="fas fa-envelope-circle-check me-1"></i> SMTP Configuration</a></li>
                <li><a href="{{ route('admin.system.setting.courier') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.system.setting.courier*') ? 'active' : '' }}"><i
                            class="fas fa-boxes-packing me-1"></i> Courier Configuration</a></li>
                <li><a href="{{ route('admin.system.setting.fraud') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.system.setting.fraud') ? 'active' : '' }}"><i
                            class="fas fa-user-secret me-1"></i> Fraud Check Config</a></li>
            </ul>
        </div>

        <!-- Theme Setting -->
        <a class="nav-head-link" data-bs-toggle="collapse" href="#collapseThemeSetting" role="button"
            id="nav-btn-theme-setting">
            <span><i class="fas fa-palette me-2 text-primary"></i> Theme Setting</span>
            <i class="fas fa-chevron-down fs-xs"></i>
        </a>
        <div class="collapse {{ request()->routeIs('admin.theme.setting.*') ? 'show' : '' }}" id="collapseThemeSetting">
            <ul class="nav-sub-list" id="sublist-theme-setting">
                <li><a href="{{ route('admin.setting.general') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.setting.general') ? 'active' : '' }}"><i
                            class="fas fa-gears me-1"></i> General Settings</a></li>
                <li><a href="{{ route('admin.theme.setting.home_builder') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.theme.setting.home_builder*') ? 'active' : '' }}"
                        id="link-thm-builder"><i class="fas fa-house-laptop me-1"></i> Home Page Builder</a></li>
                <li><a href="{{ route('admin.theme.setting.featured_products') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.theme.setting.featured_products*') ? 'active' : '' }}">
                        <i class="fas fa-fire me-1"></i> Featured Products</a></li>
                <li><a href="{{ route('admin.theme.setting.slider') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.theme.setting.slider*') ? 'active' : '' }}"
                        id="link-thm-slider"><i class="fas fa-film me-1"></i> Hero Slider</a></li>
                <li><a href="{{ route('admin.theme.setting.flash_sale') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.theme.setting.flash_sale*') ? 'active' : '' }}"
                        id="link-thm-flash"><i class="fas fa-rectangle-ad me-1"></i> Flash / CatMenu / Slide</a></li>
                <li><a href="{{ route('admin.theme.setting.popular_categories') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.theme.setting.popular_categories*') ? 'active' : '' }}"
                        id="link-thm-pop-cat"><i class="fas fa-shield-heart me-1"></i> Popular Category Slider</a></li>
                <li><a href="{{ route('admin.theme.setting.topbar') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.theme.setting.topbar*') ? 'active' : '' }}"
                        id="link-thm-topbar"><i class="fas fa-bullhorn me-1"></i> Topbar Settings</a></li>
                <li><a href="{{ route('admin.theme.setting.colors') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.theme.setting.colors*') ? 'active' : '' }}"
                        id="link-thm-colors"><i class="fas fa-paint-brush me-1"></i> Theme Colors</a></li>
                <li><a href="{{ route('admin.theme.setting.footer') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.theme.setting.footer*') ? 'active' : '' }}"
                        id="link-thm-footer"><i class="fas fa-shoe-prints me-1"></i> Footer Customize</a></li>
                <li><a href="{{ route('admin.theme.setting.product_card') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.theme.setting.product_card') ? 'active' : '' }}">
                        <i class="fas fa-th me-1"></i> Product Card View</a></li>
                <li><a href="{{ route('admin.theme.setting.cart_features') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.theme.setting.cart_features') ? 'active' : '' }}">
                        <i class="fas fa-shopping-cart me-2"></i> Site Features</a></li>
            </ul>
        </div>

        <!-- Form Builder -->
        <a class="nav-head-link" data-bs-toggle="collapse" href="#collapseFormBuilder" role="button"
            id="nav-btn-form-builder">
            <span><i class="fas fa-wand-magic-sparkles me-2 text-primary"></i> Form Builder</span>
            <i class="fas fa-chevron-down fs-xs"></i>
        </a>
        <div class="collapse {{ request()->routeIs('admin.forms.*') ? 'show' : '' }}" id="collapseFormBuilder">
            <ul class="nav-sub-list" id="sublist-form-builder">
                <li><a href="{{ route('admin.forms.index') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.forms.index') ? 'active' : '' }}"
                        id="link-form-list"><i class="fas fa-list me-1"></i> All Forms</a></li>
                <li><a href="{{ route('admin.forms.create') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.forms.create') ? 'active' : '' }}"
                        id="link-form-create"><i class="fas fa-plus-circle me-1"></i> Create New Form</a></li>
            </ul>
        </div>

        <!-- Logout Action -->
        <form action="{{ route('admin.logout') }}" method="POST" class="mt-3" id="admin-form-logout-sidebar">
            @csrf
            <button type="submit" class="nav-head-link w-100 border-0 bg-transparent text-danger" id="nav-btn-logout">
                <span><i class="fas fa-sign-out-alt me-2"></i> Logout</span>
            </button>
        </form>
    </div>

    <!-- Sidebar Bottom User Details -->
    <div class="user-footer-badge" id="admin-sidebar-footer-profile">
        <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fw-bold"
            style="width: 38px; height: 38px;" id="admin-footer-avatar">
            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
        </div>
        <div class="overflow-hidden" id="admin-footer-meta-box">
            <div class="text-white fw-bold text-truncate" style="font-size: 13px;" id="admin-footer-user-name">
                {{ auth()->user()->name ?? 'Super Admin' }}
            </div>
            <div class="text-secondary small text-truncate" style="font-size: 11px;" id="admin-footer-user-role">
                {{ auth()->user()->role ?? 'superAdmin' }}
            </div>
        </div>
    </div>
</aside>

{{-- <!-- Contact Accordion -->
<a class="nav-head-link" data-bs-toggle="collapse" href="#collapseContact" role="button" id="nav-btn-contact">
    <span><i class="fas fa-user-friends me-2"></i> Contact</span>
    <i class="fas fa-chevron-down fs-xs"></i>
</a>
<div class="collapse {{ request()->routeIs('admin.contact.*') ? 'show' : '' }}" id="collapseContact">
    <ul class="nav-sub-list" id="sublist-contact">
        <li><a href="{{ route('admin.contact.customer') }}"
                class="nav-sub-link {{ request()->routeIs('admin.contact.customer') ? 'active' : '' }}"
                id="link-contact-cust">Customer</a></li>
        <li><a href="{{ route('admin.contact.supplier') }}"
                class="nav-sub-link {{ request()->routeIs('admin.contact.supplier') ? 'active' : '' }}"
                id="link-contact-supp">Supplier</a></li>
    </ul>
</div>

<!-- Review -->
<a href="{{ route('admin.review.index') }}"
    class="nav-head-link {{ request()->routeIs('admin.review.*') ? 'active' : '' }}" id="nav-btn-review">
    <span><i class="far fa-star me-2"></i> Review</span>
</a>

<!-- Purchase Accordion -->
<a class="nav-head-link" data-bs-toggle="collapse" href="#collapsePurchase" role="button" id="nav-btn-purchase">
    <span><i class="fas fa-cart-arrow-down me-2"></i> Purchase</span>
    <i class="fas fa-chevron-down fs-xs"></i>
</a>
<div class="collapse {{ request()->routeIs('admin.purchase.*') ? 'show' : '' }}" id="collapsePurchase">
    <ul class="nav-sub-list" id="sublist-purchase">
        <li><a href="{{ route('admin.purchase.index') }}"
                class="nav-sub-link {{ request()->routeIs('admin.purchase.index') ? 'active' : '' }}"
                id="link-purch-all">All Purchase</a></li>
        <li><a href="{{ route('admin.purchase.create') }}"
                class="nav-sub-link {{ request()->routeIs('admin.purchase.create') ? 'active' : '' }}"
                id="link-purch-add">Add Purchase</a></li>
    </ul>
</div>

<!-- Promo Accordion -->
<a class="nav-head-link" data-bs-toggle="collapse" href="#collapsePromo" role="button" id="nav-btn-promo">
    <span><i class="fas fa-bolt me-2"></i> Promo</span>
    <i class="fas fa-chevron-down fs-xs"></i>
</a>
<div class="collapse {{ request()->routeIs('admin.promo.*') ? 'show' : '' }}" id="collapsePromo">
    <ul class="nav-sub-list" id="sublist-promo">
        <li><a href="{{ route('admin.promo.flashsale') }}"
                class="nav-sub-link {{ request()->routeIs('admin.promo.flashsale') ? 'active' : '' }}"
                id="link-promo-flash">Flash Sale</a></li>
        <li><a href="{{ route('admin.promo.coupon') }}"
                class="nav-sub-link {{ request()->routeIs('admin.promo.coupon') ? 'active' : '' }}"
                id="link-promo-coupon">Coupon</a></li>
    </ul>
</div>

<!-- Accounts Accordion -->
<a class="nav-head-link" data-bs-toggle="collapse" href="#collapseAccounts" role="button" id="nav-btn-accounts">
    <span><i class="fas fa-wallet me-2"></i> Accounts</span>
    <i class="fas fa-chevron-down fs-xs"></i>
</a>
<div class="collapse {{ request()->routeIs('admin.accounts.*') ? 'show' : '' }}" id="collapseAccounts">
    <ul class="nav-sub-list" id="sublist-accounts">
        <li><a href="{{ route('admin.accounts.methods') }}"
                class="nav-sub-link {{ request()->routeIs('admin.accounts.methods') ? 'active' : '' }}"
                id="link-acc-methods">Payment Method</a></li>
        <li><a href="{{ route('admin.accounts.transactions') }}"
                class="nav-sub-link {{ request()->routeIs('admin.accounts.transactions') ? 'active' : '' }}"
                id="link-acc-trx">Transaction</a></li>
        <li><a href="{{ route('admin.accounts.expenses') }}"
                class="nav-sub-link {{ request()->routeIs('admin.accounts.expenses') ? 'active' : '' }}"
                id="link-acc-exp">Expense</a></li>
        <li><a href="{{ route('admin.accounts.assets') }}"
                class="nav-sub-link {{ request()->routeIs('admin.accounts.assets') ? 'active' : '' }}"
                id="link-acc-asset">Asset</a></li>
        <li><a href="{{ route('admin.accounts.income') }}"
                class="nav-sub-link {{ request()->routeIs('admin.accounts.income') ? 'active' : '' }}"
                id="link-acc-income">Income</a></li>
        <li><a href="{{ route('admin.accounts.investment') }}"
                class="nav-sub-link {{ request()->routeIs('admin.accounts.investment') ? 'active' : '' }}"
                id="link-acc-invest">Investment</a></li>
        <li><a href="{{ route('admin.accounts.bank') }}"
                class="nav-sub-link {{ request()->routeIs('admin.accounts.bank') ? 'active' : '' }}"
                id="link-acc-bank">Bank Balance</a></li>
    </ul>
</div>

<!-- Report Accordion -->
<a class="nav-head-link" data-bs-toggle="collapse" href="#collapseReport" role="button" id="nav-btn-report">
    <span><i class="fas fa-chart-line me-2"></i> Report</span>
    <i class="fas fa-chevron-down fs-xs"></i>
</a>
<div class="collapse {{ request()->routeIs('admin.report.*') ? 'show' : '' }}" id="collapseReport">
    <ul class="nav-sub-list" id="sublist-report">
        <li><a href="{{ route('admin.report.stock') }}"
                class="nav-sub-link {{ request()->routeIs('admin.report.stock') ? 'active' : '' }}"
                id="link-rep-stock">Stock Report</a></li>
        <li><a href="{{ route('admin.report.sales') }}"
                class="nav-sub-link {{ request()->routeIs('admin.report.sales') ? 'active' : '' }}"
                id="link-rep-sales">Sales Report</a></li>
        <li><a href="{{ route('admin.report.purchase') }}"
                class="nav-sub-link {{ request()->routeIs('admin.report.purchase') ? 'active' : '' }}"
                id="link-rep-purchase">Purchase Report</a></li>
    </ul>
</div>

<!-- E-commerce Setting Accordion -->
<a class="nav-head-link" data-bs-toggle="collapse" href="#collapseEcomSetting" role="button" id="nav-btn-ecom-setting">
    <span><i class="fas fa-store me-2"></i> E-commerce Setting</span>
    <i class="fas fa-chevron-down fs-xs"></i>
</a>
<div class="collapse {{ request()->routeIs('admin.ecommerce.setting.*') ? 'show' : '' }}" id="collapseEcomSetting">
    <ul class="nav-sub-list" id="sublist-ecom-setting">
        <li><a href="{{ route('admin.ecommerce.setting.shipping') }}"
                class="nav-sub-link {{ request()->routeIs('admin.ecommerce.setting.shipping') ? 'active' : '' }}"
                id="link-ecom-shipping">Shipping Configuration</a></li>
        <li><a href="{{ route('admin.ecommerce.setting.company') }}"
                class="nav-sub-link {{ request()->routeIs('admin.ecommerce.setting.company') ? 'active' : '' }}"
                id="link-ecom-company">Company Information</a></li>
    </ul>
</div>

<!-- Banner Setting Accordion -->
<a class="nav-head-link" data-bs-toggle="collapse" href="#collapseBannerSetting" role="button"
    id="nav-btn-banner-setting">
    <span><i class="fas fa-images me-2"></i> Banner Setting</span>
    <i class="fas fa-chevron-down fs-xs"></i>
</a>
<div class="collapse {{ request()->routeIs('admin.banner.setting.*') ? 'show' : '' }}" id="collapseBannerSetting">
    <ul class="nav-sub-list" id="sublist-banner-setting">
        <li><a href="{{ route('admin.banner.setting.main') }}"
                class="nav-sub-link {{ request()->routeIs('admin.banner.setting.main') ? 'active' : '' }}"
                id="link-ban-main">Main Banner</a></li>
        <li><a href="{{ route('admin.banner.setting.campaign') }}"
                class="nav-sub-link {{ request()->routeIs('admin.banner.setting.campaign') ? 'active' : '' }}"
                id="link-ban-camp">Campaign Banner</a></li>
        <li><a href="{{ route('admin.banner.setting.shop') }}"
                class="nav-sub-link {{ request()->routeIs('admin.banner.setting.shop') ? 'active' : '' }}"
                id="link-ban-shop">Shop Banner</a></li>
    </ul>
</div>

<!-- Frontend Setting Accordion -->
<a class="nav-head-link" data-bs-toggle="collapse" href="#collapseFrontendSetting" role="button"
    id="nav-btn-front-setting">
    <span><i class="fas fa-desktop me-2"></i> Frontend Setting</span>
    <i class="fas fa-chevron-down fs-xs"></i>
</a>
<div class="collapse {{ request()->routeIs('admin.frontend.setting.*') ? 'show' : '' }}" id="collapseFrontendSetting">
    <ul class="nav-sub-list" id="sublist-front-setting">
        <li><a href="{{ route('admin.frontend.setting.logo') }}"
                class="nav-sub-link {{ request()->routeIs('admin.frontend.setting.logo') ? 'active' : '' }}"
                id="link-front-logo">Logo</a></li>
        <li><a href="{{ route('admin.frontend.setting.favicon') }}"
                class="nav-sub-link {{ request()->routeIs('admin.frontend.setting.favicon') ? 'active' : '' }}"
                id="link-front-favicon">Favicon</a></li>
        <li><a href="{{ route('admin.frontend.setting.about') }}"
                class="nav-sub-link {{ request()->routeIs('admin.frontend.setting.about') ? 'active' : '' }}"
                id="link-front-about">About Us</a></li>
        <li><a href="{{ route('admin.frontend.setting.contact') }}"
                class="nav-sub-link {{ request()->routeIs('admin.frontend.setting.contact') ? 'active' : '' }}"
                id="link-front-contact">Contact Us</a></li>
        <li><a href="{{ route('admin.frontend.setting.faq') }}"
                class="nav-sub-link {{ request()->routeIs('admin.frontend.setting.faq') ? 'active' : '' }}"
                id="link-front-faq">FAQ</a></li>
        <li><a href="{{ route('admin.frontend.setting.custompages') }}"
                class="nav-sub-link {{ request()->routeIs('admin.frontend.setting.custompages') ? 'active' : '' }}"
                id="link-front-pages">Custom Pages</a></li>
        <li><a href="{{ route('admin.frontend.setting.features') }}"
                class="nav-sub-link {{ request()->routeIs('admin.frontend.setting.features') ? 'active' : '' }}"
                id="link-front-features">Features</a></li>
        <li><a href="{{ route('admin.frontend.setting.headline') }}"
                class="nav-sub-link {{ request()->routeIs('admin.frontend.setting.headline') ? 'active' : '' }}"
                id="link-front-headline">Headline</a></li>
        <li><a href="{{ route('admin.frontend.setting.blogs') }}"
                class="nav-sub-link {{ request()->routeIs('admin.frontend.setting.blogs') ? 'active' : '' }}"
                id="link-front-blogs">Blogs</a></li>
    </ul>
</div>

<!-- My Profile Accordion -->
<a class="nav-head-link" data-bs-toggle="collapse" href="#collapseProfile" role="button" id="nav-btn-profile">
    <span><i class="far fa-user-circle me-2"></i> My Profile</span>
    <i class="fas fa-chevron-down fs-xs"></i>
</a>
<div class="collapse {{ request()->routeIs('admin.profile.*') ? 'show' : '' }}" id="collapseProfile">
    <ul class="nav-sub-list" id="sublist-profile">
        <li><a href="{{ route('admin.profile.index') }}"
                class="nav-sub-link {{ request()->routeIs('admin.profile.index') ? 'active' : '' }}"
                id="link-profile-view">My Profile</a></li>
        <li><a href="{{ route('admin.profile.password') }}"
                class="nav-sub-link {{ request()->routeIs('admin.profile.password') ? 'active' : '' }}"
                id="link-profile-pass">Change Password</a></li>
    </ul>
</div> --}}