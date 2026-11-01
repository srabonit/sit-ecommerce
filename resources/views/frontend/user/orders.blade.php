@extends('layouts.dashboard')

@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার
    static $ordersSettingsCache = null;
    if ($ordersSettingsCache === null) {
        $ordersSettingsCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $ordSettings = $ordersSettingsCache ?? [];

    // ডায়নামিক থিম কালার ভ্যারিয়েবল (Existing Default Colors)
    $ordersCardBg        = $ordSettings['color_auth_card_bg'] ?? '#ffffff';
    $ordersCardBorder    = $ordSettings['color_auth_card_border'] ?? '#e2e8f0';
    $ordersTitleColor    = $ordSettings['color_page_title'] ?? '#0f172a';
    $ordersTextColor     = $ordSettings['color_auth_text'] ?? '#64748b';
    $ordersBoxBg         = $ordSettings['color_site_bg'] ?? '#f8fafc';
    $ordersPrimaryColor  = $ordSettings['color_common_btn_bg'] ?? ($ordSettings['color_hdr_menu_hover_text'] ?? '#2563eb');
    $ordersPrimaryHover  = $ordSettings['color_auth_btn_hover'] ?? '#1d4ed8';
    $ordersPriceColor    = $ordSettings['color_cpc_price'] ?? '#0f172a';
    $ordersActiveBg      = $ordSettings['color_hdr_menu_active_bg'] ?? '#eff6ff';
@endphp

@section('title', 'My Orders - ' . config('app.name', 'Shop Now BD'))

@section('dashboard_content')
<style>
    :root {
        --orders-card-bg: {{ $ordersCardBg }};
        --orders-card-border: {{ $ordersCardBorder }};
        --orders-title: {{ $ordersTitleColor }};
        --orders-text: {{ $ordersTextColor }};
        --orders-box-bg: {{ $ordersBoxBg }};
        --orders-primary: {{ $ordersPrimaryColor }};
        --orders-primary-hover: {{ $ordersPrimaryHover }};
        --orders-price: {{ $ordersPriceColor }};
        --orders-active-bg: {{ $ordersActiveBg }};
    }

    /* Universal Strict Link Text Decoration */
    .my-orders-root a,
    .my-orders-root a:hover {
        text-decoration: none !important;
    }

    /* Main Container Card */
    .orders-main-card {
        background-color: var(--orders-card-bg) !important;
        border: 1px solid var(--orders-card-border) !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04), 0 1px 3px rgba(15, 23, 42, 0.02) !important;
        padding: 24px;
        transition: all 0.25s ease;
    }

    .orders-header-title {
        color: var(--orders-title) !important;
        font-weight: 700;
        font-size: 1.2rem;
        line-height: 1.35;
    }

    .orders-header-subtitle {
        color: var(--orders-text) !important;
        font-size: 12.5px;
        line-height: 1.45;
    }

    /* Filter Pills Row (Touch Scrollable on Mobile) */
    .orders-filter-container {
        display: flex;
        gap: 6px;
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
        -ms-overflow-style: none;
        -webkit-overflow-scrolling: touch;
        padding: 2px 2px 4px 2px;
    }

    .orders-filter-container::-webkit-scrollbar {
        display: none;
    }

    .btn-filter-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 16px;
        border-radius: 50px !important;
        font-size: 12px;
        font-weight: 600;
        background: #ffffff;
        border: 1px solid var(--orders-card-border);
        color: var(--orders-text);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        flex-shrink: 0;
        min-height: 32px;
    }

    .btn-filter-pill:hover {
        background-color: var(--orders-active-bg);
        border-color: var(--orders-primary);
        color: var(--orders-primary);
    }

    .btn-filter-pill.active {
        background-color: var(--orders-primary) !important;
        border-color: var(--orders-primary) !important;
        color: #ffffff !important;
        box-shadow: 0 3px 10px rgba(37, 99, 235, 0.25) !important;
    }

    /* Orders Responsive Table */
    .orders-table-wrapper {
        border: 1px solid var(--orders-card-border);
        border-radius: 12px;
        overflow: hidden;
        background: #ffffff;
    }

    .orders-table {
        margin-bottom: 0 !important;
        white-space: nowrap;
    }

    .orders-table thead {
        background-color: var(--orders-box-bg) !important;
    }

    .orders-table thead th {
        color: var(--orders-text) !important;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        border-bottom: 1px solid var(--orders-card-border);
        padding: 12px 16px;
    }

    .orders-table tbody td {
        padding: 13px 16px;
        border-bottom: 1px solid var(--orders-card-border);
        font-size: 13px;
        color: var(--orders-text);
    }

    .orders-table tbody tr:last-child td {
        border-bottom: none;
    }

    .orders-table tbody tr:hover {
        background-color: rgba(248, 250, 252, 0.7);
    }

    /* Links & Buttons */
    .order-invoice-link {
        color: var(--orders-primary) !important;
        font-weight: 700;
        transition: color 0.2s ease;
    }

    .order-invoice-link:hover {
        color: var(--orders-primary-hover) !important;
        text-decoration: underline !important;
    }

    .order-price-text {
        color: var(--orders-price) !important;
        font-weight: 700;
    }

    .btn-view-details {
        border: 1px solid var(--orders-primary) !important;
        color: var(--orders-primary) !important;
        background: transparent !important;
        border-radius: 50px !important;
        padding: 4px 14px !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s ease;
    }

    .btn-view-details:hover {
        background: var(--orders-primary) !important;
        color: #ffffff !important;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(37, 99, 235, 0.25) !important;
    }

    .status-badge-pill {
        font-size: 10px !important;
        font-weight: 700 !important;
        padding: 3px 9px !important;
        border-radius: 50px !important;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    /* Empty State */
    .empty-orders-box {
        text-align: center;
        padding: 40px 16px;
        color: var(--orders-text);
    }

    .empty-orders-box i {
        color: var(--orders-text);
        opacity: 0.3;
    }

    .btn-shop-empty {
        background-color: var(--orders-primary) !important;
        border-color: var(--orders-primary) !important;
        color: #ffffff !important;
        border-radius: 50px !important;
        padding: 8px 24px !important;
        font-weight: 700 !important;
        font-size: 13px !important;
        transition: all 0.2s ease;
    }

    .btn-shop-empty:hover {
        background-color: var(--orders-primary-hover) !important;
        border-color: var(--orders-primary-hover) !important;
        color: #ffffff !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3) !important;
    }

    /* Bengali digit protection */
    .bn-digit {
        font-weight: 700 !important;
    }

    /* Small Screen Optimization (iPhone SE / 320px–575px) */
    @media (max-width: 575.98px) {
        .orders-main-card {
            padding: 16px 12px !important;
            border-radius: 14px !important;
        }

        .orders-header-title {
            font-size: 1.1rem !important;
        }

        .btn-filter-pill {
            padding: 5px 13px;
            font-size: 11.5px;
            min-height: 30px;
        }

        .orders-table thead th {
            padding: 10px 12px;
            font-size: 10.5px;
        }

        .orders-table tbody td {
            padding: 11px 12px;
            font-size: 12.5px;
        }

        .btn-view-details {
            padding: 4px 10px !important;
            font-size: 11px !important;
        }
    }
</style>

<div class="my-orders-root">
    <div class="orders-main-card">
        
        {{-- Header & Status Filter Pills --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3 mb-md-4">
            <div>
                <h5 class="orders-header-title mb-1">
                    <i class="fas fa-box me-2" style="color: var(--orders-primary);"></i> My Orders
                </h5>
                <p class="orders-header-subtitle mb-0">Check status, track packages, and review purchase history</p>
            </div>
            
            {{-- Scrollable / Wrap Filter Pills --}}
            <div class="orders-filter-container">
                <a href="{{ route('user.orders') }}" 
                   class="btn-filter-pill {{ !request('status') ? 'active' : '' }}">
                   All
                </a>
                <a href="{{ route('user.orders', ['status' => 'pending']) }}" 
                   class="btn-filter-pill {{ request('status') === 'pending' ? 'active' : '' }}">
                   Pending
                </a>
                <a href="{{ route('user.orders', ['status' => 'processing']) }}" 
                   class="btn-filter-pill {{ request('status') === 'processing' ? 'active' : '' }}">
                   Processing
                </a>
                <a href="{{ route('user.orders', ['status' => 'delivered']) }}" 
                   class="btn-filter-pill {{ request('status') === 'delivered' ? 'active' : '' }}">
                   Delivered
                </a>
            </div>
        </div>

        {{-- Orders Table or Empty State --}}
        @if(isset($orders) && $orders->count() > 0)
            <div class="orders-table-wrapper">
                <div class="table-responsive">
                    <table class="table orders-table align-middle">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Payment</th>
                                <th>Total Amount</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $ord)
                                @php
                                    $badgeClass = match($ord->order_status ?? 'pending') {
                                        'delivered' => 'bg-success text-white',
                                        'shipped' => 'bg-info text-dark',
                                        'processing' => 'bg-primary text-white',
                                        'cancelled' => 'bg-danger text-white',
                                        default => 'bg-warning text-dark'
                                    };
                                @endphp
                                <tr>
                                    <td class="py-3">
                                        <a href="{{ route('user.orders.details', $ord->invoice_no) }}" class="order-invoice-link small">
                                            {{ $ord->invoice_no }}
                                        </a>
                                    </td>
                                    <td class="small bn-digit" style="color: var(--orders-text);">
                                        {{ date('d M, Y', strtotime($ord->created_at)) }}
                                    </td>
                                    <td class="small text-capitalize" style="color: var(--orders-title);">
                                        {{ $ord->payment_method ?? 'Cash on Delivery' }}
                                    </td>
                                    <td class="small order-price-text bn-digit">
                                        ৳{{ number_format($ord->grand_total, 2) }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $badgeClass }} status-badge-pill">
                                            {{ $ord->order_status ?? 'pending' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('user.orders.details', $ord->invoice_no) }}" class="btn btn-view-details">
                                            <span>View Details</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Responsive Pagination --}}
            <div class="mt-4 d-flex justify-content-center justify-content-md-end">
                {{ $orders->links() }}
            </div>
        @else
            <div class="empty-orders-box">
                <i class="fas fa-shopping-bag fa-3x mb-3"></i>
                <h6 class="fw-bold mb-1" style="color: var(--orders-title);">No orders found</h6>
                <p class="small mb-3" style="color: var(--orders-text);">You have not placed any orders under this filter yet.</p>
                <a href="{{ route('shop') }}" class="btn btn-shop-empty">
                    <i class="fas fa-shopping-cart me-1"></i> Shop Now
                </a>
            </div>
        @endif

    </div>
</div>
@endsection