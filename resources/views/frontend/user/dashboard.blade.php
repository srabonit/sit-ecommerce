@extends('layouts.dashboard')

@php
    // সেন্ট্রাল গ্লোবাল সেটিংস ক্যাশ রেজলভার
    static $userOverviewCache = null;
    if ($userOverviewCache === null) {
        $userOverviewCache = \Illuminate\Support\Facades\Cache::remember('global_settings_cache', 86400, function() {
            return \Illuminate\Support\Facades\DB::table('settings')->pluck('value', 'key')->toArray();
        });
    }
    $dashSettings = $userOverviewCache ?? [];

    // ডায়নামিক থিম কালার ভ্যারিয়েবল (ডিফল্ট হিসেবে এক্সিস্টিং কালারগুলো সেট করা)
    $heroGradStart    = $dashSettings['color_common_btn_bg'] ?? '#1e3a8a';
    $heroGradEnd      = $dashSettings['color_hdr_menu_hover_text'] ?? '#3b82f6';
    $cardBg           = $dashSettings['color_auth_card_bg'] ?? '#ffffff';
    $cardBorder       = $dashSettings['color_auth_card_border'] ?? '#edf2f7';
    $headingColor     = $dashSettings['color_page_title'] ?? '#0f172a';
    $textColor        = $dashSettings['color_auth_text'] ?? '#64748b';
    $primaryColor     = $dashSettings['color_common_btn_bg'] ?? '#2563eb';
    $primaryHover     = $dashSettings['color_auth_btn_hover'] ?? '#1d4ed8';
    $activePillBg     = $dashSettings['color_hdr_menu_active_bg'] ?? '#eff6ff';
    $hoverBg          = $dashSettings['color_hdr_menu_hover_bg'] ?? '#f8fafc';
    $productPriceCol  = $dashSettings['color_cpc_price'] ?? '#0f172a';

    // সেশন ও ইউজার অ্যাড্রেস রেজলভার
    $sessionAddr = session()->get('user_shipping_address', []);
    $displayDistrict = $user->district ?? $sessionAddr['district'] ?? 'Dhaka';
    $displayAddress = $user->address ?? $sessionAddr['address'] ?? 'No delivery address saved yet.';
@endphp

@section('title', 'My Account - ' . config('app.name', 'Shop Now BD'))

@section('dashboard_content')
<style>
    :root {
        --dash-hero-start: {{ $heroGradStart }};
        --dash-hero-end: {{ $heroGradEnd }};
        --dash-card-bg: {{ $cardBg }};
        --dash-card-border: {{ $cardBorder }};
        --dash-heading: {{ $headingColor }};
        --dash-text: {{ $textColor }};
        --dash-primary: {{ $primaryColor }};
        --dash-primary-hover: {{ $primaryHover }};
        --dash-active-bg: {{ $activePillBg }};
        --dash-hover-bg: {{ $hoverBg }};
        --dash-price: {{ $productPriceCol }};
    }

    /* Universal Strict Text-Decoration Reset */
    .dashboard-overview-root a,
    .dashboard-overview-root a:hover,
    .quick-action-tile,
    .quick-action-tile:hover {
        text-decoration: none !important;
    }

    /* Welcome Hero Card */
    .welcome-hero-card {
        background: linear-gradient(135deg, var(--dash-hero-start) 0%, var(--dash-hero-end) 100%) !important;
        border-radius: 16px;
        color: #ffffff;
        padding: 24px;
        position: relative;
        overflow: hidden;
        border: none;
    }

    .welcome-hero-card::after {
        content: '';
        position: absolute;
        right: -30px;
        bottom: -30px;
        width: 140px;
        height: 140px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
        pointer-events: none;
    }

    .btn-hero-shop {
        background: #ffffff !important;
        color: var(--dash-primary) !important;
        border: none !important;
        transition: all 0.2s ease;
    }
    .btn-hero-shop:hover {
        background: #f8fafc !important;
        color: var(--dash-primary-hover) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
    }

    /* Stat Box Cards */
    .stat-box-card {
        background: var(--dash-card-bg) !important;
        border: 1px solid var(--dash-card-border) !important;
        border-radius: 14px;
        padding: 16px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
        display: flex;
        align-items: center;
        gap: 14px;
        height: 100%;
        transition: all 0.2s ease;
    }
    .stat-box-card:hover {
        transform: translateY(-2px);
        border-color: var(--dash-primary) !important;
        box-shadow: 0 4px 15px rgba(15, 23, 42, 0.06);
    }
    .stat-icon-circle {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    /* Primary Container Cards */
    .recent-orders-card {
        background: var(--dash-card-bg) !important;
        border: 1px solid var(--dash-card-border) !important;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
    }

    .card-title-text {
        color: var(--dash-heading) !important;
        font-weight: 700;
        font-size: 1.05rem;
    }

    .text-theme-link {
        color: var(--dash-primary) !important;
        transition: color 0.2s ease;
    }
    .text-theme-link:hover {
        color: var(--dash-primary-hover) !important;
    }

    /* Quick Action Shortcuts */
    .quick-action-tile {
        background: var(--dash-card-bg) !important;
        border: 1px solid var(--dash-card-border) !important;
        border-radius: 12px;
        padding: 12px 14px;
        color: var(--dash-heading) !important;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.2s ease;
        height: 100%;
        min-height: 48px;
    }
    .quick-action-tile:hover {
        border-color: var(--dash-primary) !important;
        color: var(--dash-primary) !important;
        background: var(--dash-hover-bg) !important;
        transform: translateY(-1px);
    }
    .quick-action-tile i {
        font-size: 15px;
        width: 20px;
        text-align: center;
        flex-shrink: 0;
    }

    /* Table & Badges */
    .table-responsive {
        border: 1px solid var(--dash-card-border);
        border-radius: 12px;
        overflow: hidden;
        -webkit-overflow-scrolling: touch;
    }
    .table-custom-dash {
        margin-bottom: 0 !important;
        white-space: nowrap;
    }
    .table-custom-dash thead {
        background-color: var(--dash-hover-bg) !important;
    }
    .table-custom-dash thead th {
        color: var(--dash-text) !important;
        font-size: 11px;
        letter-spacing: 0.05em;
        font-weight: 700;
        border-bottom: 1px solid var(--dash-card-border);
        padding: 10px 14px;
    }
    .table-custom-dash tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--dash-card-border);
        color: var(--dash-text);
        font-size: 13px;
    }
    .table-custom-dash tbody tr:last-child td {
        border-bottom: none;
    }

    .btn-action-detail {
        border: 1px solid var(--dash-card-border) !important;
        color: var(--dash-heading) !important;
        background: #ffffff !important;
        font-size: 12px !important;
        padding: 4px 14px !important;
        border-radius: 50px !important;
        transition: all 0.2s ease;
    }
    .btn-action-detail:hover {
        border-color: var(--dash-primary) !important;
        background: var(--dash-primary) !important;
        color: #ffffff !important;
    }

    /* Bengali Digits Protection */
    .bn-digit {
        font-weight: 700 !important;
    }

    /* Small Screen Optimization (iPhone SE, 360px–575px) */
    @media (max-width: 575.98px) {
        .welcome-hero-card {
            padding: 18px 16px !important;
        }
        .welcome-hero-card h3 {
            font-size: 1.25rem !important;
        }
        .recent-orders-card {
            padding: 16px 14px !important;
        }
        .stat-box-card {
            padding: 12px 14px !important;
            gap: 12px;
        }
        .stat-icon-circle {
            width: 40px;
            height: 40px;
            font-size: 16px;
        }
        .stat-box-card h4 {
            font-size: 1.15rem !important;
        }
        .quick-action-tile {
            padding: 10px 12px !important;
            font-size: 12px !important;
            gap: 8px;
        }
    }
</style>

<div class="dashboard-overview-root">
    {{-- Welcome Hero Banner --}}
    <div class="welcome-hero-card shadow-sm mb-3 mb-md-4">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-8">
                <span class="badge bg-white text-primary rounded-pill px-3 py-1 mb-2 fw-bold small" style="color: var(--dash-primary) !important;">
                    Customer Account
                </span>
                <h3 class="fw-bold mb-1">Hello, {{ $user->name }}!</h3>
                <p class="mb-0 text-white-50 small">Track your packages, check order progress, and manage personal preferences all in one place.</p>
            </div>
            <div class="col-12 col-md-4 text-md-end">
                <a href="{{ route('shop') }}" class="btn btn-hero-shop rounded-pill px-4 py-2 fw-bold shadow-sm small w-100 w-md-auto d-inline-flex align-items-center justify-content-center">
                    <i class="fas fa-shopping-bag me-1"></i> Start Shopping
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Cards Row --}}
    <div class="row g-2 g-md-3 mb-3 mb-md-4">
        <div class="col-12 col-sm-4">
            <div class="stat-box-card">
                <div class="stat-icon-circle bg-primary-subtle" style="color: var(--dash-primary) !important;">
                    <i class="fas fa-box-open"></i>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold mb-0 bn-digit" style="color: var(--dash-heading);">{{ $activeOrders ?? 0 }}</h4>
                    <span class="small" style="color: var(--dash-text);">Active Orders</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-sm-4">
            <div class="stat-box-card">
                <div class="stat-icon-circle bg-warning-subtle text-warning">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold mb-0 bn-digit" style="color: var(--dash-heading);">{{ $loyaltyPoints ?? 0 }}</h4>
                    <span class="small" style="color: var(--dash-text);">Reward Points</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-sm-4">
            <div class="stat-box-card">
                <div class="stat-icon-circle bg-danger-subtle text-danger">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold mb-0 bn-digit" style="color: var(--dash-heading);">{{ $wishlistCount ?? 0 }}</h4>
                    <span class="small" style="color: var(--dash-text);">Wishlist Items</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Orders Table Section --}}
    <div class="recent-orders-card mb-3 mb-md-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title-text mb-0">
                <i class="fas fa-history me-2" style="color: var(--dash-primary);"></i> Recent Orders
            </h5>
            <a href="{{ route('user.orders') }}" class="text-theme-link small fw-bold">
                View All Orders &rarr;
            </a>
        </div>

        @if(isset($recentOrders) && $recentOrders->count() > 0)
            <div class="table-responsive">
                <table class="table table-custom-dash align-middle">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $ord)
                            @php
                                $badgeClass = match($ord->order_status ?? 'pending') {
                                    'delivered' => 'bg-success',
                                    'shipped' => 'bg-info text-dark',
                                    'processing' => 'bg-primary text-white',
                                    'cancelled' => 'bg-danger',
                                    default => 'bg-warning text-dark'
                                };
                            @endphp
                            <tr>
                                <td class="fw-bold small py-3" style="color: var(--dash-primary);">
                                    {{ $ord->invoice_no }}
                                </td>
                                <td class="small" style="color: var(--dash-text);">
                                    {{ date('d M, Y', strtotime($ord->created_at)) }}
                                </td>
                                <td class="fw-bold small bn-digit" style="color: var(--dash-price);">
                                    ৳{{ number_format($ord->grand_total, 2) }}
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $badgeClass }} rounded-pill text-uppercase px-2 py-1" style="font-size: 10px;">
                                        {{ $ord->order_status ?? 'pending' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('user.orders.details', $ord->invoice_no) }}" class="btn btn-action-detail fw-semibold">
                                        Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4 text-muted small">
                <i class="fas fa-shopping-basket fa-2x mb-2 opacity-50" style="color: var(--dash-text);"></i>
                <p class="mb-0">You have no recent orders placed yet.</p>
            </div>
        @endif
    </div>

    {{-- Bottom Two Column: Primary Shipping Address & Quick Shortcuts --}}
    <div class="row g-3 g-md-4">
        <div class="col-12 col-md-6">
            <div class="recent-orders-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="card-title-text mb-0" style="font-size: 14.5px;">
                        <i class="fas fa-map-pin me-2" style="color: var(--dash-primary);"></i> Primary Shipping Address
                    </h6>
                    <a href="{{ route('user.addresses') }}" class="small text-theme-link fw-semibold">Edit</a>
                </div>
                <div class="p-3 rounded-3 small" style="background-color: var(--dash-hover-bg); border: 1px solid var(--dash-card-border); color: var(--dash-text);">
                    <strong class="d-block mb-1" style="color: var(--dash-heading);">{{ $user->name }}</strong>
                    <div class="bn-digit mb-1">{{ $user->phone ?? 'Phone number not provided' }}</div>
                    <div class="mb-1">{{ $displayAddress }}</div>
                    <div><strong>District:</strong> {{ $displayDistrict }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="recent-orders-card h-100">
                <h6 class="card-title-text mb-3" style="font-size: 14.5px;">
                    <i class="fas fa-bolt me-2" style="color: var(--dash-primary);"></i> Quick Shortcuts
                </h6>
                <div class="row g-2">
                    <div class="col-6">
                        <a href="{{ route('user.profile') }}" class="quick-action-tile">
                            <i class="far fa-user" style="color: var(--dash-primary);"></i> <span>Edit Profile</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('user.coupons') }}" class="quick-action-tile">
                            <i class="fas fa-percent text-success"></i> <span>Vouchers</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('user.track.order') }}" class="quick-action-tile">
                            <i class="fas fa-truck text-info"></i> <span>Track Order</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('user.support') }}" class="quick-action-tile">
                            <i class="fas fa-life-ring text-warning"></i> <span>Help Desk</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection