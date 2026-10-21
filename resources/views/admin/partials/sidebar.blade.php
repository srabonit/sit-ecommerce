@php
    $u = auth()->user();
    $globalSettings = \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key');

    $siteTitle = $globalSettings['site_title'] ?? config('app.name', 'Shop Now BD');
    $siteTagline = $globalSettings['site_tagline'] ?? 'BEST ONLINE SHOP IN BD';

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

    // একক ও নির্ভুল Unchecked Transaction Counter
    $txnAlerts = class_exists(\App\Services\PaymentTransactionService::class)
        ? \App\Services\PaymentTransactionService::getUncheckedAlertsSummary()
        : ['total_unchecked_count' => 0, 'pending_checkout_count' => 0];

    $uncheckedTxnCount    = (int) ($txnAlerts['total_unchecked_count'] ?? 0);
    $pendingCheckoutCount = (int) ($txnAlerts['pending_checkout_count'] ?? 0);
@endphp

<style>
    /* Global Strict 5px Element Radius */
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

    /* Main Dark Sidebar Container */
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
        transition: left 0.25s ease-in-out, transform 0.25s ease-in-out;
        user-select: none;
    }

    /* Mobile Drawer & Backdrop (100/100 Mobile Support) */
    @media (max-width: 991.98px) {
        .admin-dark-sidebar {
            position: fixed;
            top: 0;
            left: -275px;
            z-index: 1060;
            height: 100vh;
            box-shadow: 6px 0 25px rgba(0, 0, 0, 0.65);
        }

        .admin-dark-sidebar.mobile-sidebar-open {
            left: 0 !important;
        }

        .admin-sidebar-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(3, 23, 46, 0.65);
            backdrop-filter: blur(3px);
            z-index: 1055;
            display: none;
            opacity: 0;
            transition: opacity 0.2s ease-in-out;
        }

        .admin-sidebar-backdrop.active {
            display: block !important;
            opacity: 1 !important;
        }
    }

    /* Brand Header Area */
    .admin-sidebar-logo-area {
        padding: 14px 16px;
        background: #021020;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #0f2b48;
        text-decoration: none;
    }

    .admin-brand-favicon-circle {
        width: 36px;
        height: 36px;
        min-width: 36px;
        min-height: 36px;
        background: rgba(255, 255, 255, 0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.12);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        transition: transform 0.15s ease, border-color 0.15s ease;
    }

    .admin-brand-favicon-circle:hover {
        transform: scale(1.04);
        border-color: rgba(255, 255, 255, 0.25);
    }

    .admin-brand-favicon-circle img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 2px;
    }

    .admin-brand-favicon-circle.fallback-avatar {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #ffffff;
        font-size: 15px;
    }

    /* Mobile Close Button */
    .mobile-sidebar-close-btn {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #94a3b8;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s ease;
        flex-shrink: 0;
        font-size: 13px;
    }

    .mobile-sidebar-close-btn:hover {
        background: #ef4444;
        color: #ffffff;
        border-color: #ef4444;
    }

    /* Nav List Scroll Area */
    .admin-nav-list {
        padding: 10px 8px 30px;
        overflow-y: auto;
        flex-grow: 1;
        scrollbar-width: thin;
        scrollbar-color: #1e3a5f transparent;
    }

    .admin-nav-list::-webkit-scrollbar {
        width: 4px;
    }

    .admin-nav-list::-webkit-scrollbar-thumb {
        background: #1e3a5f;
        border-radius: 4px;
    }

    /* STRICT SAME COLOR RULE FOR ICON & TEXT */
    .nav-head-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
        color: #94a3b8;
        text-decoration: none;
        font-weight: 500;
        font-size: 12.5px;
        transition: all 0.15s ease;
        margin-bottom: 2px;
        cursor: pointer;
    }

    /* Icons strictly inherit the text color */
    .nav-head-link i,
    .nav-sub-link i {
        color: currentColor !important;
        transition: color 0.15s ease;
    }

    .nav-head-link:hover,
    .nav-head-link.active {
        color: #ffffff !important;
        background: #0f2b48;
    }

    .nav-head-link.active-dashboard {
        background: #f59e0b !important;
        color: #03172e !important;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.25);
    }

    .nav-head-link.active-dashboard i {
        color: #03172e !important;
    }

    /* Animated Chevron Rotate */
    .nav-head-link .fa-chevron-down {
        font-size: 10px;
        transition: transform 0.2s ease;
    }

    .nav-head-link[aria-expanded="true"] .fa-chevron-down {
        transform: rotate(180deg);
        color: #ffffff !important;
    }

    /* Sub-Navigation Styling */
    .nav-sub-list {
        list-style: none;
        padding-left: 14px;
        margin: 3px 0 6px 16px;
        border-left: 1.5px solid #1e3a5f;
    }

    .nav-sub-link {
        display: flex;
        align-items: center;
        padding: 6px 10px;
        color: #8da2bd;
        text-decoration: none;
        font-size: 12px;
        margin-bottom: 1px;
        transition: all 0.15s ease;
    }

    .nav-sub-link:hover {
        color: #ffffff !important;
        background: #0b223c;
    }

    .nav-sub-link.active {
        color: #f59e0b !important;
        background: #0b223c;
        font-weight: 600;
    }

    /* Global Media Chooser Primary Accent */
    .media-chooser-menu-item {
        background: rgba(37, 99, 235, 0.1) !important;
        color: #60a5fa !important;
        border: 1px solid rgba(59, 130, 246, 0.35);
        font-weight: 600;
    }
    .media-chooser-menu-item:hover,
    .media-chooser-menu-item.active {
        background: #2563eb !important;
        color: #ffffff !important;
        border-color: #2563eb;
    }

    /* User Profile Bottom Footer */
    .user-footer-badge {
        padding: 12px 16px;
        background: #021020;
        border-top: 1px solid #0f2b48;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-avatar-sq {
        width: 36px;
        height: 36px;
        min-width: 36px;
        background: #f59e0b;
        color: #03172e;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
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
        @if(!$u || $u->hasAccess('admin.dashboard'))
            <a href="{{ route('admin.dashboard') }}"
                class="nav-head-link {{ request()->routeIs('admin.dashboard') ? 'active-dashboard' : '' }}"
                id="nav-btn-dashboard">
                <span id="nav-icon-label-dash"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</span>
            </a>
        @endif

        <!-- POS -->
        @if(!$u || $u->hasAnyAccess(['admin.pos.terminal', 'admin.pos.index']))
            <a href="{{ route('admin.pos.index') }}"
                class="nav-head-link {{ request()->routeIs('admin.pos.*') ? 'active' : '' }}" id="nav-btn-pos">
                <span><i class="fas fa-cash-register me-2"></i> POS</span>
            </a>
        @endif

        <!-- Order Accordion -->
        @if(!$u || $u->hasAnyAccess(['admin.order.online', 'admin.order.create_manual', 'admin.order.incomplete', 'admin.order.return', 'admin.order.exchange', 'admin.order.fraud']))
            @php $isOrderActive = request()->routeIs('admin.order.*'); @endphp
            <a class="nav-head-link {{ $isOrderActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseOrder" role="button" aria-expanded="{{ $isOrderActive ? 'true' : 'false' }}" id="nav-btn-order">
                <span><i class="fas fa-shopping-cart me-2"></i> Order</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isOrderActive ? 'show' : '' }}" id="collapseOrder">
                <ul class="nav-sub-list" id="sublist-order">
                    <li><a href="{{ route('admin.order.create_manual') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.order.create_manual') ? 'active' : '' }}"
                            id="link-order-create-manual">
                            <i class="fas fa-circle-plus me-2" style="font-size: 10px;"></i> Add Manual Order
                        </a></li>
                    <li><a href="{{ route('admin.order.online') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.order.online') ? 'active' : '' }}"
                            id="link-order-online"><i class="fas fa-globe me-2" style="font-size: 10px;"></i> Online Orders</a></li>
                    <li><a href="{{ route('admin.order.incomplete') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.order.incomplete') ? 'active' : '' }}"
                            id="link-order-incomplete"><i class="fas fa-hourglass-half me-2" style="font-size: 10px;"></i> Incomplete Orders</a></li>
                    <li><a href="{{ route('admin.order.return') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.order.return') ? 'active' : '' }}"
                            id="link-order-return"><i class="fas fa-rotate-left me-2" style="font-size: 10px;"></i> Return Orders</a></li>
                    <li><a href="{{ route('admin.order.exchange') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.order.exchange') ? 'active' : '' }}"
                            id="link-order-exchange"><i class="fas fa-arrow-right-arrow-left me-2" style="font-size: 10px;"></i> Exchange Orders</a></li>
                    <li><a href="{{ route('admin.order.fraud') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.order.fraud') ? 'active' : '' }}"
                            id="link-order-fraud"><i class="fas fa-shield-alt me-2" style="font-size: 10px;"></i> Fraud Checker</a></li>
                </ul>
            </div>
        @endif

        <!-- Courier Manage Accordion -->
        @if(!$u || $u->hasAnyAccess(['admin.courier.dashboard', 'admin.courier.index', 'admin.courier.booking', 'admin.courier.settlements', 'admin.system.setting.courier']))
            @php $isCourierActive = request()->routeIs('admin.courier.*'); @endphp
            <a class="nav-head-link {{ $isCourierActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseCourier" role="button" aria-expanded="{{ $isCourierActive ? 'true' : 'false' }}" id="nav-btn-courier">
                <span><i class="fas fa-truck-fast me-2"></i> Courier Manage</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isCourierActive ? 'show' : '' }}" id="collapseCourier">
                <ul class="nav-sub-list" id="sublist-courier">
                    <li>
                        <a href="{{ route('admin.courier.dashboard') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.courier.dashboard') ? 'active' : '' }}"
                            id="link-courier-dashboard">
                            <i class="fas fa-chart-pie me-2" style="font-size: 10px;"></i> Courier Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.courier.index') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.courier.index') ? 'active' : '' }}"
                            id="link-courier-all-orders">
                            <i class="fas fa-boxes-packing me-2" style="font-size: 10px;"></i> All Courier Orders
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.courier.booking') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.courier.booking') ? 'active' : '' }}"
                            id="link-courier-booking">
                            <i class="fas fa-plus-circle me-2" style="font-size: 10px;"></i> New Booking
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.courier.settlements') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.courier.settlements') ? 'active' : '' }}"
                            id="link-courier-settlements">
                            <i class="fas fa-money-bill-transfer me-2" style="font-size: 10px;"></i> COD &amp; Cost Settlement
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.system.setting.courier') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.system.setting.courier*') ? 'active' : '' }}"
                            id="link-courier-settings">
                            <i class="fas fa-sliders me-2" style="font-size: 10px;"></i> Courier Gateways
                        </a>
                    </li>
                </ul>
            </div>
        @endif

        <!-- Transactions Accordion -->
        @if(!$u || $u->hasAnyAccess(['admin.transactions.dashboard', 'admin.transactions.checkout', 'admin.transactions.pos', 'admin.transactions.cod', 'admin.transactions.manual', 'admin.transactions.index', 'admin.transactions.unchecked', 'admin.transactions.history', 'admin.transactions.reports', 'admin.order.online', 'admin.payment.setting.gateways']))
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
                    <li><a href="{{ route('admin.transactions.dashboard') }}" class="nav-sub-link {{ request()->routeIs('admin.transactions.dashboard') ? 'active' : '' }}"><i class="fas fa-chart-line me-2" style="font-size: 10px;"></i> Transaction Dashboard</a></li>
                    <li>
                        <a href="{{ route('admin.transactions.checkout') }}" class="nav-sub-link d-flex justify-content-between align-items-center {{ request()->routeIs('admin.transactions.checkout') ? 'active' : '' }}">
                            <span><i class="fas fa-cart-shopping me-2" style="font-size: 10px;"></i> Checkout Requests</span>
                            <span class="badge bg-warning text-dark rounded-pill {{ $pendingCheckoutCount > 0 ? '' : 'd-none' }}" id="sidebar-checkout-txn-badge" style="font-size: 9.5px;">{{ $pendingCheckoutCount }}</span>
                        </a>
                    </li>
                    <li><a href="{{ route('admin.transactions.pos') }}" class="nav-sub-link {{ request()->routeIs('admin.transactions.pos') ? 'active' : '' }}"><i class="fas fa-cash-register me-2" style="font-size: 10px;"></i> POS Cash Orders</a></li>
                    <li><a href="{{ route('admin.transactions.cod') }}" class="nav-sub-link {{ request()->routeIs('admin.transactions.cod') ? 'active' : '' }}"><i class="fas fa-hand-holding-dollar me-2" style="font-size: 10px;"></i> COD Orders</a></li>
                    <li><a href="{{ route('admin.transactions.manual') }}" class="nav-sub-link {{ request()->routeIs('admin.transactions.manual') ? 'active' : '' }}"><i class="fas fa-keyboard me-2" style="font-size: 10px;"></i> Manual Transactions</a></li>
                    <li><a href="{{ route('admin.transactions.index') }}" class="nav-sub-link {{ request()->routeIs('admin.transactions.index') ? 'active' : '' }}"><i class="fas fa-list me-2" style="font-size: 10px;"></i> All Transactions</a></li>
                    <li>
                        <a href="{{ route('admin.transactions.unchecked') }}" class="nav-sub-link d-flex justify-content-between align-items-center {{ request()->routeIs('admin.transactions.unchecked') ? 'active' : '' }}">
                            <span><i class="fas fa-bell me-2" style="font-size: 10px;"></i> Unchecked</span>
                            <span class="badge bg-danger rounded-pill {{ $uncheckedTxnCount > 0 ? '' : 'd-none' }}" id="sidebar-sub-unchecked-badge" style="font-size: 9.5px;">{{ $uncheckedTxnCount }}</span>
                        </a>
                    </li>
                    <li><a href="{{ route('admin.transactions.history') }}" class="nav-sub-link {{ request()->routeIs('admin.transactions.history') ? 'active' : '' }}"><i class="fas fa-history me-2" style="font-size: 10px;"></i> Verification History</a></li>
                    <li><a href="{{ route('admin.transactions.reports') }}" class="nav-sub-link {{ request()->routeIs('admin.transactions.reports') ? 'active' : '' }}"><i class="fas fa-file-invoice me-2" style="font-size: 10px;"></i> Transaction Reports</a></li>
                    <li><a href="{{ route('admin.transactions.trash') }}" class="nav-sub-link {{ request()->routeIs('admin.transactions.trash') ? 'active' : '' }}"><i class="fas fa-trash-can me-2" style="font-size: 10px;"></i> Trashed Records</a></li>
                    <li><a href="{{ route('admin.payment.setting.gateways') }}" class="nav-sub-link {{ request()->routeIs('admin.payment.setting.*') ? 'active' : '' }}"><i class="fas fa-credit-card me-2" style="font-size: 10px;"></i> Payment Settings</a></li>
                </ul>
            </div>
        @endif

        <!-- Accounts & Operational Expenses Accordion -->
        @php 
            $isAccountsActive = request()->routeIs('admin.accounts.*'); 
        @endphp
        <a class="nav-head-link {{ $isAccountsActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseAccounts" role="button" aria-expanded="{{ $isAccountsActive ? 'true' : 'false' }}" id="nav-btn-accounts">
            <span><i class="fas fa-wallet me-2"></i> Accounts &amp; Expenses</span>
            <i class="fas fa-chevron-down"></i>
        </a>
        <div class="collapse {{ $isAccountsActive ? 'show' : '' }}" id="collapseAccounts">
            <ul class="nav-sub-list" id="sublist-accounts">
                <li>
                    @php
                        $expenseUrl = Route::has('admin.accounts.expenses.index') 
                            ? route('admin.accounts.expenses.index') 
                            : (Route::has('admin.accounts.expenses') ? route('admin.accounts.expenses') : url('/admin/accounts/expenses'));
                    @endphp
                    <a href="{{ $expenseUrl }}"
                        class="nav-sub-link {{ request()->routeIs('admin.accounts.expenses*') ? 'active' : '' }}"
                        id="link-acc-expenses">
                        <i class="fas fa-receipt me-2" style="font-size: 10px;"></i> Operational Expenses
                    </a>
                </li>
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
                <li>
                    <a href="{{ route('admin.accounts.methods') }}"
                        class="nav-sub-link {{ request()->routeIs('admin.accounts.methods') ? 'active' : '' }}"
                        id="link-acc-methods">
                        <i class="fas fa-credit-card me-2" style="font-size: 10px;"></i> Payment Methods
                    </a>
                </li>
                <li>
                    @php
                        $ledgerUrl = Route::has('admin.accounts.ledger.index') 
                            ? route('admin.accounts.ledger.index') 
                            : (Route::has('admin.accounts.ledger') ? route('admin.accounts.ledger') : url('/admin/accounts/ledger'));
                    @endphp
                    <a href="{{ $ledgerUrl }}"
                        class="nav-sub-link {{ request()->routeIs('admin.accounts.ledger*') ? 'active' : '' }}"
                        id="link-acc-ledger">
                        <i class="fas fa-list-check me-2" style="font-size: 10px;"></i> Account Ledgers
                    </a>
                </li>
                <li>
                    @php
                        $bankUrl = Route::has('admin.accounts.bank.index') 
                            ? route('admin.accounts.bank.index') 
                            : (Route::has('admin.accounts.bank') ? route('admin.accounts.bank') : url('/admin/accounts/bank-balance'));
                    @endphp
                    <a href="{{ $bankUrl }}"
                        class="nav-sub-link {{ request()->routeIs('admin.accounts.bank*') ? 'active' : '' }}"
                        id="link-acc-bank">
                        <i class="fas fa-building-columns me-2" style="font-size: 10px;"></i> Bank &amp; Cash Balance
                    </a>
                </li>
            </ul>
        </div>

        <!-- Product Accordion -->
        @if(!$u || $u->hasAnyAccess(['admin.product.index', 'admin.product.create', 'admin.product.category', 'admin.product.brand', 'admin.product.barcode']))
            @php $isProductActive = request()->routeIs('admin.product.*'); @endphp
            <a class="nav-head-link {{ $isProductActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseProduct" role="button" aria-expanded="{{ $isProductActive ? 'true' : 'false' }}" id="nav-btn-product">
                <span><i class="fas fa-box-open me-2"></i> Product</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isProductActive ? 'show' : '' }}" id="collapseProduct">
                <ul class="nav-sub-list" id="sublist-product">
                    <li><a href="{{ route('admin.product.index') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.product.index') ? 'active' : '' }}"
                            id="link-prod-all"><i class="fas fa-box me-2" style="font-size: 10px;"></i> Product</a></li>
                    <li><a href="{{ route('admin.product.category') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.product.category*') ? 'active' : '' }}"
                            id="link-prod-cat"><i class="fas fa-tags me-2" style="font-size: 10px;"></i> Category</a></li>
                    <li><a href="{{ route('admin.product.brand') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.product.brand*') ? 'active' : '' }}"
                            id="link-prod-brand"><i class="fas fa-award me-2" style="font-size: 10px;"></i> Brand</a></li>
                    <li><a href="{{ route('admin.product.barcode') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.product.barcode*') ? 'active' : '' }}"
                            id="link-prod-barcode"><i class="fas fa-barcode me-2" style="font-size: 10px;"></i> Barcode Print</a></li>
                </ul>
            </div>
        @endif

        <!-- Inventory Management Accordion -->
        @if(!$u || $u->hasAnyAccess(['admin.inventory.dashboard', 'admin.inventory.stock_in', 'admin.inventory.stock_out', 'admin.inventory.balance', 'admin.inventory.movements', 'admin.inventory.reports']))
            @php $isInvActive = request()->routeIs('admin.inventory.*'); @endphp
            <a class="nav-head-link {{ $isInvActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseInventory" role="button" aria-expanded="{{ $isInvActive ? 'true' : 'false' }}" id="nav-btn-inventory">
                <span><i class="fas fa-boxes-packing me-2"></i> Inventory</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isInvActive ? 'show' : '' }}" id="collapseInventory">
                <ul class="nav-sub-list" id="sublist-inventory">
                    <li><a href="{{ route('admin.inventory.dashboard') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.inventory.dashboard') ? 'active' : '' }}"
                            id="link-inv-dash"><i class="fas fa-chart-line me-2" style="font-size: 10px;"></i> Inventory Dashboard</a></li>
                    <li><a href="{{ route('admin.inventory.stock_in') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.inventory.stock_in*') ? 'active' : '' }}"
                            id="link-inv-stock-in"><i class="fas fa-arrow-down-long me-2" style="font-size: 10px;"></i> Stock In</a></li>
                    <li><a href="{{ route('admin.inventory.stock_out') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.inventory.stock_out*') ? 'active' : '' }}"
                            id="link-inv-stock-out"><i class="fas fa-arrow-up-long me-2" style="font-size: 10px;"></i> Stock Out</a></li>
                    <li><a href="{{ route('admin.inventory.balance') }}"
                            class="nav-sub-link {{ (request()->routeIs('admin.inventory.balance') && !request()->filled('stock_status')) ? 'active' : '' }}"
                            id="link-inv-balance"><i class="fas fa-scale-balanced me-2" style="font-size: 10px;"></i> Stock Balance</a></li>
                    <li><a href="{{ route('admin.inventory.movements') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.inventory.movements') ? 'active' : '' }}"
                            id="link-inv-movements"><i class="fas fa-clipboard-list me-2" style="font-size: 10px;"></i> Inventory Ledger</a></li>
                    <li><a href="{{ route('admin.inventory.balance', ['stock_status' => 'low_stock']) }}"
                            class="nav-sub-link {{ (request()->routeIs('admin.inventory.balance') && request('stock_status') === 'low_stock') ? 'active' : '' }}"
                            id="link-inv-low-stock"><i class="fas fa-triangle-exclamation me-2" style="font-size: 10px;"></i> Low Stock Items</a></li>
                    <li><a href="{{ route('admin.inventory.balance', ['stock_status' => 'out_of_stock']) }}"
                            class="nav-sub-link {{ (request()->routeIs('admin.inventory.balance') && request('stock_status') === 'out_of_stock') ? 'active' : '' }}"
                            id="link-inv-out-stock"><i class="fas fa-circle-xmark me-2" style="font-size: 10px;"></i> Out of Stock Items</a></li>
                    <li><a href="{{ route('admin.inventory.reports') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.inventory.reports*') ? 'active' : '' }}"
                            id="link-inv-reports"><i class="fas fa-file-invoice-dollar me-2" style="font-size: 10px;"></i> Inventory Reports</a></li>
                </ul>
            </div>
        @endif

        <!-- Reports & Financial Analytics (P&L, Balance Sheet, Cash Flow & COGS) Accordion -->
        @if(!$u || $u->hasAnyAccess(['admin.report.profit_loss', 'admin.report.balance_sheet', 'admin.report.cash_flow', 'admin.report.cogs', 'admin.report.sales', 'admin.report.stock', 'admin.report.purchase']))
            @php $isReportActive = request()->routeIs('admin.report.*'); @endphp
            <a class="nav-head-link {{ $isReportActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseReports" role="button" aria-expanded="{{ $isReportActive ? 'true' : 'false' }}" id="nav-btn-reports">
                <span><i class="fas fa-chart-line me-2"></i> Reports &amp; P&amp;L</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isReportActive ? 'show' : '' }}" id="collapseReports">
                <ul class="nav-sub-list" id="sublist-reports">
                    <li>
                        <a href="{{ route('admin.report.profit_loss') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.report.profit_loss') ? 'active' : '' }}"
                            id="link-report-pnl">
                            <i class="fas fa-file-invoice-dollar me-2" style="font-size: 10px;"></i> Profit &amp; Loss (P&amp;L)
                        </a>
                    </li>
                    <li>
                        @php
                            $balanceSheetUrl = Route::has('admin.report.balance_sheet') 
                                ? route('admin.report.balance_sheet') 
                                : url('/admin/report/balance-sheet');
                        @endphp
                        <a href="{{ $balanceSheetUrl }}"
                            class="nav-sub-link {{ request()->routeIs('admin.report.balance_sheet*') ? 'active' : '' }}"
                            id="link-report-balance-sheet">
                            <i class="fas fa-scale-balanced me-2" style="font-size: 10px;"></i> Balance Sheet
                        </a>
                    </li>
                    <li>
                        @php
                            $cashFlowUrl = Route::has('admin.report.cash_flow') 
                                ? route('admin.report.cash_flow') 
                                : url('/admin/report/cash-flow');
                        @endphp
                        <a href="{{ $cashFlowUrl }}"
                            class="nav-sub-link {{ request()->routeIs('admin.report.cash_flow*') ? 'active' : '' }}"
                            id="link-report-cash-flow">
                            <i class="fas fa-money-bill-trend-up me-2" style="font-size: 10px;"></i> Cash Flow Statement
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.report.cogs') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.report.cogs') ? 'active' : '' }}"
                            id="link-report-cogs">
                            <i class="fas fa-boxes-stacked me-2" style="font-size: 10px;"></i> COGS Analysis
                        </a>
                    </li>
                </ul>
            </div>
        @endif

        <!-- ========================================================================= -->
        <!-- ADVANCED EMAIL MARKETING MODULE (ACCORDION & ALL SUBMENUS)                 -->
        <!-- ========================================================================= -->
        @if(!$u || $u->role === 'superAdmin' || $u->hasAnyAccess(['admin.email_marketing.dashboard', 'admin.email_marketing.subscribers', 'admin.email_marketing.segments', 'admin.email_marketing.campaigns', 'admin.email_marketing.templates', 'admin.email_marketing.automations', 'admin.email_marketing.settings', 'admin.email_marketing.analytics']))
            @php 
                $isEmailMarketingActive = request()->routeIs('admin.email_marketing.*') || request()->is('admin/email-marketing*'); 
            @endphp
            <a class="nav-head-link {{ $isEmailMarketingActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseEmailMarketing" role="button" aria-expanded="{{ $isEmailMarketingActive ? 'true' : 'false' }}" id="nav-btn-email-marketing">
                <span><i class="fas fa-envelope-open-text me-2"></i> Email Marketing</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isEmailMarketingActive ? 'show' : '' }}" id="collapseEmailMarketing">
                <ul class="nav-sub-list" id="sublist-email-marketing">
                    {{-- 1. Dashboard --}}
                    <li>
                        @php $emDashUrl = Route::has('admin.email_marketing.dashboard') ? route('admin.email_marketing.dashboard') : url('/admin/email-marketing/dashboard'); @endphp
                        <a href="{{ $emDashUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.dashboard*') || request()->is('admin/email-marketing/dashboard*')) ? 'active' : '' }}" id="link-em-dashboard">
                            <i class="fas fa-chart-pie me-2" style="font-size: 10px;"></i> Dashboard
                        </a>
                    </li>

                    {{-- 2. All Subscribers --}}
                    <li>
                        @php $emSubUrl = Route::has('admin.email_marketing.subscribers.index') ? route('admin.email_marketing.subscribers.index') : url('/admin/email-marketing/subscribers'); @endphp
                        <a href="{{ $emSubUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.subscribers*') || request()->is('admin/email-marketing/subscribers*')) ? 'active' : '' }}" id="link-em-subscribers">
                            <i class="fas fa-users me-2" style="font-size: 10px;"></i> All Subscribers
                        </a>
                    </li>

                    {{-- 3. Audience Segments --}}
                    <li>
                        @php $emSegUrl = Route::has('admin.email_marketing.segments.index') ? route('admin.email_marketing.segments.index') : url('/admin/email-marketing/segments'); @endphp
                        <a href="{{ $emSegUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.segments*') || request()->is('admin/email-marketing/segments*')) ? 'active' : '' }}" id="link-em-segments">
                            <i class="fas fa-filter-circle-dollar me-2" style="font-size: 10px;"></i> Audience Segments
                        </a>
                    </li>

                    {{-- 4. Campaigns --}}
                    <li>
                        @php $emCampUrl = Route::has('admin.email_marketing.campaigns.index') ? route('admin.email_marketing.campaigns.index') : url('/admin/email-marketing/campaigns'); @endphp
                        <a href="{{ $emCampUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.campaigns*') || request()->is('admin/email-marketing/campaigns*')) ? 'active' : '' }}" id="link-em-campaigns">
                            <i class="fas fa-paper-plane me-2" style="font-size: 10px;"></i> Campaigns
                        </a>
                    </li>

                    {{-- 5. Email Template Builder --}}
                    <li>
                        @php $emTplUrl = Route::has('admin.email_marketing.templates.index') ? route('admin.email_marketing.templates.index') : url('/admin/email-marketing/templates'); @endphp
                        <a href="{{ $emTplUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.templates*') || request()->is('admin/email-marketing/templates*')) ? 'active' : '' }}" id="link-em-templates">
                            <i class="fas fa-paintbrush me-2" style="font-size: 10px;"></i> Email Template Builder
                        </a>
                    </li>

                    {{-- 6. Automation & Workflows --}}
                    <li>
                        @php $emAutoUrl = Route::has('admin.email_marketing.automations.index') ? route('admin.email_marketing.automations.index') : url('/admin/email-marketing/automations'); @endphp
                        <a href="{{ $emAutoUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.automations*') || request()->is('admin/email-marketing/automations*')) ? 'active' : '' }}" id="link-em-automations">
                            <i class="fas fa-diagram-project me-2" style="font-size: 10px;"></i> Automation &amp; Workflows
                        </a>
                    </li>

                    {{-- 7. Product-Based Marketing --}}
                    <li>
                        @php $emProdUrl = Route::has('admin.email_marketing.product_marketing') ? route('admin.email_marketing.product_marketing') : url('/admin/email-marketing/product-marketing'); @endphp
                        <a href="{{ $emProdUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.product_marketing*') || request()->is('admin/email-marketing/product-marketing*')) ? 'active' : '' }}" id="link-em-prod-marketing">
                            <i class="fas fa-box-open me-2" style="font-size: 10px;"></i> Product-Based Marketing
                        </a>
                    </li>

                    {{-- 8. Cart & Wishlist Marketing --}}
                    <li>
                        @php $emCartUrl = Route::has('admin.email_marketing.cart_wishlist_marketing') ? route('admin.email_marketing.cart_wishlist_marketing') : url('/admin/email-marketing/cart-wishlist'); @endphp
                        <a href="{{ $emCartUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.cart_wishlist*') || request()->is('admin/email-marketing/cart-wishlist*')) ? 'active' : '' }}" id="link-em-cart-wishlist">
                            <i class="fas fa-cart-arrow-down me-2" style="font-size: 10px;"></i> Cart &amp; Wishlist Marketing
                        </a>
                    </li>

                    {{-- 9. Customer Groups --}}
                    <li>
                        @php $emGrpUrl = Route::has('admin.email_marketing.customer_groups') ? route('admin.email_marketing.customer_groups') : url('/admin/email-marketing/customer-groups'); @endphp
                        <a href="{{ $emGrpUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.customer_groups*') || request()->is('admin/email-marketing/customer-groups*')) ? 'active' : '' }}" id="link-em-customer-groups">
                            <i class="fas fa-people-group me-2" style="font-size: 10px;"></i> Customer Groups
                        </a>
                    </li>

                    {{-- 10. Scheduled Emails --}}
                    <li>
                        @php $emSchedUrl = Route::has('admin.email_marketing.scheduled') ? route('admin.email_marketing.scheduled') : url('/admin/email-marketing/scheduled'); @endphp
                        <a href="{{ $emSchedUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.scheduled*') || request()->is('admin/email-marketing/scheduled*')) ? 'active' : '' }}" id="link-em-scheduled">
                            <i class="fas fa-clock me-2" style="font-size: 10px;"></i> Scheduled Emails
                        </a>
                    </li>

                    {{-- 11. Email Queue & Delivery Logs --}}
                    <li>
                        @php $emQueueUrl = Route::has('admin.email_marketing.queue_logs') ? route('admin.email_marketing.queue_logs') : url('/admin/email-marketing/queue-logs'); @endphp
                        <a href="{{ $emQueueUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.queue_logs*') || request()->is('admin/email-marketing/queue-logs*')) ? 'active' : '' }}" id="link-em-queue-logs">
                            <i class="fas fa-list-check me-2" style="font-size: 10px;"></i> Email Queue &amp; Logs
                        </a>
                    </li>

                    {{-- 12. Email Analytics & Reports --}}
                    <li>
                        @php $emAnalyticsUrl = Route::has('admin.email_marketing.analytics') ? route('admin.email_marketing.analytics') : url('/admin/email-marketing/analytics'); @endphp
                        <a href="{{ $emAnalyticsUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.analytics*') || request()->is('admin/email-marketing/analytics*')) ? 'active' : '' }}" id="link-em-analytics">
                            <i class="fas fa-chart-line me-2" style="font-size: 10px;"></i> Analytics &amp; Reports
                        </a>
                    </li>

                    {{-- 13. A/B Testing --}}
                    <li>
                        @php $emAbUrl = Route::has('admin.email_marketing.ab_testing') ? route('admin.email_marketing.ab_testing') : url('/admin/email-marketing/ab-testing'); @endphp
                        <a href="{{ $emAbUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.ab_testing*') || request()->is('admin/email-marketing/ab-testing*')) ? 'active' : '' }}" id="link-em-ab-testing">
                            <i class="fas fa-flask-vial me-2" style="font-size: 10px;"></i> A/B Testing
                        </a>
                    </li>

                    {{-- 14. Coupons & Promotions --}}
                    <li>
                        @php $emCpnUrl = Route::has('admin.email_marketing.coupons') ? route('admin.email_marketing.coupons') : url('/admin/email-marketing/coupons'); @endphp
                        <a href="{{ $emCpnUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.coupons*') || request()->is('admin/email-marketing/coupons*')) ? 'active' : '' }}" id="link-em-coupons">
                            <i class="fas fa-ticket me-2" style="font-size: 10px;"></i> Coupons &amp; Promotions
                        </a>
                    </li>

                    {{-- 15. Suppression & Unsubscribe List --}}
                    <li>
                        @php $emSuppUrl = Route::has('admin.email_marketing.suppression') ? route('admin.email_marketing.suppression') : url('/admin/email-marketing/suppression'); @endphp
                        <a href="{{ $emSuppUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.suppression*') || request()->is('admin/email-marketing/suppression*')) ? 'active' : '' }}" id="link-em-suppression">
                            <i class="fas fa-ban me-2" style="font-size: 10px;"></i> Suppression List
                        </a>
                    </li>

                    {{-- 16. SMTP & Email Provider Settings --}}
                    <li>
                        @php $emProvUrl = Route::has('admin.email_marketing.providers') ? route('admin.email_marketing.providers') : url('/admin/email-marketing/providers'); @endphp
                        <a href="{{ $emProvUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.providers*') || request()->is('admin/email-marketing/providers*')) ? 'active' : '' }}" id="link-em-providers">
                            <i class="fas fa-server me-2" style="font-size: 10px;"></i> SMTP &amp; Providers
                        </a>
                    </li>

                    {{-- 17. Tracking & Consent Settings --}}
                    <li>
                        @php $emTrkUrl = Route::has('admin.email_marketing.tracking_settings') ? route('admin.email_marketing.tracking_settings') : url('/admin/email-marketing/tracking-settings'); @endphp
                        <a href="{{ $emTrkUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.tracking_settings*') || request()->is('admin/email-marketing/tracking-settings*')) ? 'active' : '' }}" id="link-em-tracking-settings">
                            <i class="fas fa-shield-halved me-2" style="font-size: 10px;"></i> Tracking &amp; Consent
                        </a>
                    </li>

                    {{-- 18. General Settings --}}
                    <li>
                        @php $emSetUrl = Route::has('admin.email_marketing.settings') ? route('admin.email_marketing.settings') : url('/admin/email-marketing/settings'); @endphp
                        <a href="{{ $emSetUrl }}" class="nav-sub-link {{ (request()->routeIs('admin.email_marketing.settings*') || request()->is('admin/email-marketing/settings*')) ? 'active' : '' }}" id="link-em-settings">
                            <i class="fas fa-sliders me-2" style="font-size: 10px;"></i> Marketing Settings
                        </a>
                    </li>
                </ul>
            </div>
        @endif

        <!-- Support Request Menu -->
        @if(!$u || $u->hasAccess('admin.support.index'))
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
        @if(!$u || $u->hasAnyAccess(['admin.roles.index', 'admin.roles.users', 'admin.sessions.index', 'admin.activity.logs', 'admin.roles.trash']))
            @php $isRolesActive = request()->routeIs('admin.roles.*') || request()->routeIs('admin.activity.*') || request()->routeIs('admin.sessions.*'); @endphp
            <a class="nav-head-link {{ $isRolesActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseRoles" role="button" aria-expanded="{{ $isRolesActive ? 'true' : 'false' }}" id="nav-btn-roles">
                <span><i class="fas fa-user-shield me-2"></i> User &amp; Roles</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isRolesActive ? 'show' : '' }}" id="collapseRoles">
                <ul class="nav-sub-list" id="sublist-roles">
                    <li><a href="{{ route('admin.roles.index') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.roles.index*') ? 'active' : '' }}"
                            id="link-roles-manage"><i class="fas fa-id-badge me-2" style="font-size: 10px;"></i> Role Management</a></li>
                    <li><a href="{{ route('admin.roles.users') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.roles.users*') ? 'active' : '' }}"
                            id="link-roles-users"><i class="fas fa-users-gear me-2" style="font-size: 10px;"></i> User Management</a></li>
                    <li><a href="{{ route('admin.sessions.index') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.sessions.index*') ? 'active' : '' }}"><i class="fas fa-tower-broadcast me-2" style="font-size: 10px;"></i> Live Sessions</a></li>
                    <li><a href="{{ route('admin.activity.logs') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.activity.logs*') ? 'active' : '' }}"><i class="fas fa-clipboard-list me-2" style="font-size: 10px;"></i> Audit Logs</a></li>
                    <li><a href="{{ route('admin.roles.trash') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.roles.trash*') ? 'active' : '' }}"><i class="fas fa-trash-can me-2" style="font-size: 10px;"></i> Trashed Accounts</a></li>
                </ul>
            </div>
        @endif

        <!-- Branch Accordion -->
        @if(!$u || $u->hasAnyAccess(['admin.branch.index', 'admin.branch.assign.history', 'admin.branch.new.assign', 'admin.branch.products', 'admin.branch.orders']))
            @php $isBranchActive = request()->routeIs('admin.branch.*'); @endphp
            <a class="nav-head-link {{ $isBranchActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseBranch" role="button" aria-expanded="{{ $isBranchActive ? 'true' : 'false' }}" id="nav-btn-branch">
                <span><i class="fas fa-code-branch me-2"></i> Branch</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isBranchActive ? 'show' : '' }}" id="collapseBranch">
                <ul class="nav-sub-list" id="sublist-branch">
                    <li><a href="{{ route('admin.branch.index') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.branch.index*') ? 'active' : '' }}"
                            id="link-branch-all"><i class="fas fa-building me-2" style="font-size: 10px;"></i> All Branches</a></li>
                    <li><a href="{{ route('admin.branch.assign.history') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.branch.assign.history*') ? 'active' : '' }}"
                            id="link-branch-assign-hist"><i class="fas fa-clock-rotate-left me-2" style="font-size: 10px;"></i> Assign History</a></li>
                    <li><a href="{{ route('admin.branch.new.assign') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.branch.new.assign*') ? 'active' : '' }}"
                            id="link-branch-new-assign"><i class="fas fa-plus me-2" style="font-size: 10px;"></i> New Assign</a></li>
                    <li><a href="{{ route('admin.branch.products') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.branch.products*') ? 'active' : '' }}"
                            id="link-branch-products"><i class="fas fa-boxes me-2" style="font-size: 10px;"></i> Branch Products</a></li>
                    <li><a href="{{ route('admin.branch.orders') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.branch.orders*') ? 'active' : '' }}"
                            id="link-branch-orders"><i class="fas fa-truck-ramp-box me-2" style="font-size: 10px;"></i> Branch Orders</a></li>
                </ul>
            </div>
        @endif

        <!-- Site Content Accordion (With Global Media Chooser Highlight) -->
        @if(!$u || $u->hasAnyAccess(['admin.content.pages.index', 'admin.content.blogs.index', 'admin.content.blogs.comments', 'admin.content.menus.index', 'admin.content.widgets.index', 'admin.content.media.index', 'admin.media.index']))
            @php 
                $isMediaActive = request()->routeIs('admin.content.media.*') || request()->routeIs('admin.media.*');
                $isContentActive = request()->routeIs('admin.content.*') || $isMediaActive; 
            @endphp
            <a class="nav-head-link {{ $isContentActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseSiteContent" role="button" aria-expanded="{{ $isContentActive ? 'true' : 'false' }}"
                id="nav-btn-site-content">
                <span><i class="fas fa-layer-group me-2"></i> Site Content</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isContentActive ? 'show' : '' }}" id="collapseSiteContent">
                <ul class="nav-sub-list" id="sublist-site-content">
                    <li><a href="{{ route('admin.content.pages.index') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.content.pages.*') ? 'active' : '' }}"
                            id="link-cnt-pages"><i class="far fa-file-alt me-2" style="font-size: 10px;"></i> Pages</a></li>
                    <li><a href="{{ route('admin.content.blogs.index') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.content.blogs.index') || request()->routeIs('admin.content.blogs.create') || request()->routeIs('admin.content.blogs.edit') ? 'active' : '' }}"
                            id="link-cnt-blogs"><i class="fas fa-blog me-2" style="font-size: 10px;"></i> Blog Posts</a></li>
                    <li><a href="{{ route('admin.content.blogs.comments') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.content.blogs.comments*') ? 'active' : '' }}">
                            <i class="fas fa-comments me-2" style="font-size: 10px;"></i> Comments
                        </a></li>
                    <li><a href="{{ route('admin.content.menus.index') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.content.menus.*') ? 'active' : '' }}"
                            id="link-cnt-menus"><i class="fas fa-bars me-2" style="font-size: 10px;"></i> Menus</a></li>
                    <li><a href="{{ route('admin.content.widgets.index') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.content.widgets.*') ? 'active' : '' }}"
                            id="link-cnt-widgets"><i class="fas fa-puzzle-piece me-2" style="font-size: 10px;"></i> Widgets</a></li>
                    
                    {{-- Global Media Chooser Primary Route Link --}}
                    <li>
                        <a href="{{ route('admin.content.media.index') }}"
                            class="nav-sub-link d-flex justify-content-between align-items-center media-chooser-menu-item mb-1 {{ $isMediaActive ? 'active' : '' }}"
                            id="link-cnt-media"
                            title="Global Media Chooser & Asset Picker">
                            <span><i class="fas fa-photo-film me-2"></i> Media Library</span>
                            <span class="badge bg-primary text-white px-1.5 py-0 media-badge-tag" style="font-size: 9px;">Global</span>
                        </a>
                    </li>
                </ul>
            </div>
        @endif

        <!-- Config Setting Dropdown -->
        @if(!$u || $u->hasAnyAccess(['admin.shipping.index', 'admin.payment.setting.gateways', 'admin.system.setting.smtp', 'admin.system.setting.courier', 'admin.system.setting.fraud']))
            @php $isSettingActive = (request()->routeIs('admin.system.setting.*') || request()->routeIs('admin.payment.*') || request()->routeIs('admin.shipping.*')); @endphp
            <a class="nav-head-link {{ $isSettingActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#settingCollapse" role="button" aria-expanded="{{ $isSettingActive ? 'true' : 'false' }}" id="nav-btn-setting">
                <span><i class="fas fa-gear me-2"></i> Config Setting</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isSettingActive ? 'show' : '' }}" id="settingCollapse">
                <ul class="nav-sub-list" id="sublist-setting">
                    <li><a href="{{ route('admin.shipping.index') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.shipping.*') ? 'active' : '' }}"><i
                                class="fas fa-ship me-2" style="font-size: 10px;"></i> Shipping Settings</a></li>
                    <li><a href="{{ route('admin.payment.setting.gateways') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.payment.*') ? 'active' : '' }}"><i
                                class="fas fa-credit-card me-2" style="font-size: 10px;"></i> Payment Gateways &amp; QR</a></li>
                    <li><a href="{{ route('admin.system.setting.smtp') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.system.setting.smtp') ? 'active' : '' }}"><i
                                class="fas fa-envelope me-2" style="font-size: 10px;"></i> SMTP Configuration</a></li>
                    <li><a href="{{ route('admin.system.setting.courier') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.system.setting.courier*') ? 'active' : '' }}"><i
                                class="fas fa-boxes-packing me-2" style="font-size: 10px;"></i> Courier Configuration</a></li>
                    <li><a href="{{ route('admin.system.setting.fraud') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.system.setting.fraud') ? 'active' : '' }}"><i
                                class="fas fa-user-secret me-2" style="font-size: 10px;"></i> Fraud Check Config</a></li>
                </ul>
            </div>
        @endif

        <!-- Theme Setting -->
        @if(!$u || $u->hasAnyAccess(['admin.setting.general', 'admin.theme.setting.home_builder', 'admin.theme.setting.featured_products', 'admin.theme.setting.slider', 'admin.theme.setting.flash_sale', 'admin.theme.setting.popular_categories', 'admin.theme.setting.topbar', 'admin.theme.setting.colors', 'admin.theme.setting.footer', 'admin.theme.setting.product_card', 'admin.theme.setting.cart_features']))
            @php $isThemeActive = (request()->routeIs('admin.theme.setting.*') || request()->routeIs('admin.setting.general')); @endphp
            <a class="nav-head-link {{ $isThemeActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseThemeSetting" role="button" aria-expanded="{{ $isThemeActive ? 'true' : 'false' }}"
                id="nav-btn-theme-setting">
                <span><i class="fas fa-palette me-2"></i> Theme Setting</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isThemeActive ? 'show' : '' }}" id="collapseThemeSetting">
                <ul class="nav-sub-list" id="sublist-theme-setting">
                    <li><a href="{{ route('admin.setting.general') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.setting.general') ? 'active' : '' }}"><i
                                class="fas fa-sliders me-2" style="font-size: 10px;"></i> General Settings</a></li>
                    <li><a href="{{ route('admin.theme.setting.home_builder') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.theme.setting.home_builder*') ? 'active' : '' }}"
                            id="link-thm-builder"><i class="fas fa-house-laptop me-2" style="font-size: 10px;"></i> Home Page Builder</a></li>
                    <li><a href="{{ route('admin.theme.setting.featured_products') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.theme.setting.featured_products*') ? 'active' : '' }}">
                            <i class="fas fa-fire me-2" style="font-size: 10px;"></i> Featured Products</a></li>
                    <li><a href="{{ route('admin.theme.setting.slider') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.theme.setting.slider*') ? 'active' : '' }}"
                            id="link-thm-slider"><i class="fas fa-film me-2" style="font-size: 10px;"></i> Hero Slider</a></li>
                    <li><a href="{{ route('admin.theme.setting.flash_sale') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.theme.setting.flash_sale*') ? 'active' : '' }}"
                            id="link-thm-flash"><i class="fas fa-rectangle-ad me-2" style="font-size: 10px;"></i> Flash / CatMenu / Slide</a></li>
                    <li><a href="{{ route('admin.theme.setting.popular_categories') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.theme.setting.popular_categories*') ? 'active' : '' }}"
                            id="link-thm-pop-cat"><i class="fas fa-heart me-2" style="font-size: 10px;"></i> Popular Category Slider</a></li>
                    <li><a href="{{ route('admin.theme.setting.topbar') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.theme.setting.topbar*') ? 'active' : '' }}"
                            id="link-thm-topbar"><i class="fas fa-bullhorn me-2" style="font-size: 10px;"></i> Topbar Settings</a></li>
                    <li><a href="{{ route('admin.theme.setting.colors') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.theme.setting.colors*') ? 'active' : '' }}"
                            id="link-thm-colors"><i class="fas fa-paint-brush me-2" style="font-size: 10px;"></i> Theme Colors</a></li>
                    <li><a href="{{ route('admin.theme.setting.footer') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.theme.setting.footer*') ? 'active' : '' }}"
                            id="link-thm-footer"><i class="fas fa-shoe-prints me-2" style="font-size: 10px;"></i> Footer Customize</a></li>
                    <li><a href="{{ route('admin.theme.setting.product_card') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.theme.setting.product_card') ? 'active' : '' }}">
                            <i class="fas fa-th me-2" style="font-size: 10px;"></i> Product Card View</a></li>
                    <li><a href="{{ route('admin.theme.setting.cart_features') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.theme.setting.cart_features') ? 'active' : '' }}">
                            <i class="fas fa-cart-shopping me-2" style="font-size: 10px;"></i> Site Features</a></li>
                </ul>
            </div>
        @endif

        <!-- Form Builder -->
        @if(!$u || $u->hasAnyAccess(['admin.forms.index', 'admin.forms.create']))
            @php $isFormActive = request()->routeIs('admin.forms.*'); @endphp
            <a class="nav-head-link {{ $isFormActive ? 'active' : '' }}" data-bs-toggle="collapse" href="#collapseFormBuilder" role="button" aria-expanded="{{ $isFormActive ? 'true' : 'false' }}"
                id="nav-btn-form-builder">
                <span><i class="fas fa-wand-magic-sparkles me-2"></i> Form Builder</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse {{ $isFormActive ? 'show' : '' }}" id="collapseFormBuilder">
                <ul class="nav-sub-list" id="sublist-form-builder">
                    <li><a href="{{ route('admin.forms.index') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.forms.index') ? 'active' : '' }}"
                            id="link-form-list"><i class="fas fa-list me-2" style="font-size: 10px;"></i> All Forms</a></li>
                    <li><a href="{{ route('admin.forms.create') }}"
                            class="nav-sub-link {{ request()->routeIs('admin.forms.create') ? 'active' : '' }}"
                            id="link-form-create"><i class="fas fa-plus-circle me-2" style="font-size: 10px;"></i> Create New Form</a></li>
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