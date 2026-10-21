@extends('layouts.app')

@section('content')
<style>
    .account-dashboard-bg {
        background-color: var(--site-bg-color, #f8fafc);
        min-height: 85vh;
        font-family: inherit;
    }

    .user-nav-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.05), 0 1px 2px -1px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .nav-category-title {
        font-size: 10.5px;
        letter-spacing: 0.06em;
        font-weight: 700;
        color: #94a3b8;
        padding: 14px 16px 6px;
        text-transform: uppercase;
    }

    .user-menu-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        font-size: 13.5px;
        font-weight: 600;
        color: #334155;
        text-decoration: none;
        border-radius: 10px;
        margin: 2px 8px;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        min-height: 42px;
    }

    .user-menu-link:hover {
        background-color: #f1f5f9;
        color: #2563eb;
        transform: translateX(3px);
    }

    .user-menu-link.active {
        background-color: #eff6ff;
        color: #2563eb;
        font-weight: 700;
    }

    .user-menu-link i {
        font-size: 15px;
        width: 20px;
        text-align: center;
        color: #64748b;
        transition: color 0.2s ease;
    }

    .user-menu-link:hover i,
    .user-menu-link.active i {
        color: #2563eb;
    }

    .logout-btn-link {
        color: #ef4444 !important;
        cursor: pointer;
    }

    .logout-btn-link i {
        color: #ef4444 !important;
    }

    .logout-btn-link:hover {
        background-color: #fef2f2 !important;
        color: #dc2626 !important;
    }

    .logout-btn-link:hover i {
        color: #dc2626 !important;
    }

    .dashboard-avatar-box {
        background: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.22);
    }

    /* Bengali digit protection */
    .bn-digit, 
    .user-stat-num {
        font-weight: 700 !important;
    }

    @media (max-width: 991.98px) {
        .account-dashboard-bg {
            padding-top: 14px !important;
        }

        .user-nav-card.desktop-sidebar {
            display: none !important;
        }

        .mobile-dashboard-header {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 12px 14px;
            margin-bottom: 12px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }

        .mobile-menu-scroll-row {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 6px;
            margin-bottom: 16px;
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
            gap: 6px;
            padding: 8px 16px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 9999px;
            color: #475569;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
            flex-shrink: 0;
            min-height: 38px;
            transition: all 0.2s ease;
        }

        .mob-pill-link:hover {
            background-color: #f8fafc;
            color: #2563eb;
        }

        .mob-pill-link.active {
            background: #2563eb;
            color: #ffffff !important;
            border-color: #2563eb;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);
        }

        .mob-pill-link.active i {
            color: #ffffff !important;
        }

        .mob-pill-link.text-danger {
            border-color: #fee2e2;
        }
    }
</style>

<div class="account-dashboard-bg py-4">
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

        {{-- Mobile Profile Strip --}}
        <div class="d-lg-none">
            <div class="mobile-dashboard-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2.5 min-w-0">
                    <div class="dashboard-avatar-box rounded-circle d-flex align-items-center justify-content-center fw-bold small flex-shrink-0" style="width: 38px; height: 38px;">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="overflow-hidden pe-2">
                        <h6 class="fw-bold mb-0 text-dark text-truncate" style="font-size: 13.5px;">{{ auth()->user()->name ?? 'User' }}</h6>
                        <small class="text-muted text-truncate d-block bn-digit" style="font-size: 11px;">{{ auth()->user()->phone ?? auth()->user()->email }}</small>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="m-0 flex-shrink-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-semibold d-flex align-items-center gap-1.5" style="font-size: 11.5px;">
                        <i class="fas fa-sign-out-alt"></i> <span>Sign Out</span>
                    </button>
                </form>
            </div>

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

        <div class="row g-4">
            {{-- Desktop Navigation Sidebar --}}
            <div class="col-lg-3 d-none d-lg-block">
                <div class="user-nav-card desktop-sidebar p-2 mb-4">
                    
                    <div class="d-flex align-items-center gap-3 p-3 border-bottom mb-2">
                        <div class="dashboard-avatar-box rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5 flex-shrink-0" style="width: 46px; height: 46px;">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold mb-0 text-dark text-truncate" style="font-size: 14.5px;">{{ auth()->user()->name ?? 'User' }}</h6>
                            <small class="text-muted text-truncate d-block bn-digit" style="font-size: 12px;">{{ auth()->user()->email ?? auth()->user()->phone }}</small>
                        </div>
                    </div>

                    <div class="nav-category-title">Orders & Purchases</div>
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

                    <div class="nav-category-title mt-2">Finances & Rewards</div>
                    <a href="{{ route('user.coupons') }}" class="user-menu-link {{ request()->routeIs('user.coupons') ? 'active' : '' }}">
                        <i class="fas fa-ticket-alt"></i> <span>Coupons & Vouchers</span>
                    </a>

                    <div class="nav-category-title mt-2">Support & Safety</div>
                    <a href="{{ route('user.support') }}" class="user-menu-link {{ request()->routeIs('user.support') ? 'active' : '' }}">
                        <i class="fas fa-headset"></i> <span>Customer Support</span>
                    </a>

                    <div class="border-top mt-3 pt-2">
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="user-menu-link logout-btn-link w-100 border-0 bg-transparent text-start">
                                <i class="fas fa-sign-out-alt"></i> <span>Sign Out</span>
                            </button>
                        </form>
                    </div>

                </div>
            </div>

            {{-- Main Injected Content --}}
            <div class="col-lg-9 col-12">
                @yield('dashboard_content')
            </div>

        </div>
    </div>
</div>
@endsection