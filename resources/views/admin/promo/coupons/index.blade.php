@extends('layouts.admin')

@section('title', 'Coupons & Vouchers Management')

@php
    $couponIndexUrl   = Route::has('admin.promo.coupons.index') ? route('admin.promo.coupons.index') : url('/admin/promo/coupons');
    $couponCreateUrl  = Route::has('admin.promo.coupons.create') ? route('admin.promo.coupons.create') : url('/admin/promo/coupons/create');
    $voucherCreateUrl = Route::has('admin.promo.coupons.vouchers.create') ? route('admin.promo.coupons.vouchers.create') : url('/admin/promo/coupons/vouchers/create');

    $activeRedemptionsCount = (int)($metrics['total_redemptions'] ?? 0);
    $voidedRedemptionsCount = (int)($metrics['total_voided_redemptions'] ?? 0);
    $totalLogsCount         = $activeRedemptionsCount + $voidedRedemptionsCount;
    $redemptionFilter       = request('redemption_status', 'all'); // 'all', 'completed', 'restored'
@endphp

@section('admin_content')

<style>
    /* Coupons & Vouchers - Kaiadmin Classic */
    #promoManagementRoot {
        --promo-radius: 5px;
        --promo-border: var(--bs-border-color, #e5e7eb);
        --promo-muted: #6b7280;
        color: var(--bs-body-color);
    }

    #promoManagementRoot .promo-card,
    #promoManagementRoot .card {
        border: 1px solid var(--promo-border) !important;
        border-radius: var(--promo-radius) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .04) !important;
        background: #fff;
    }

    #promoManagementRoot .promo-header h4 {
        font-size: 19px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    #promoManagementRoot .promo-header p {
        font-size: 12px;
        margin-bottom: 0;
    }

    #promoManagementRoot .btn {
        border-radius: var(--promo-radius);
        font-size: 12px;
        font-weight: 600;
    }

    #promoManagementRoot .btn-sm {
        font-size: 11px;
    }

    #promoManagementRoot .form-control,
    #promoManagementRoot .form-select,
    #promoManagementRoot .input-group-text {
        min-height: 35px;
        border-radius: var(--promo-radius);
        font-size: 12px;
    }

    #promoManagementRoot .input-group > :first-child {
        border-radius: var(--promo-radius) 0 0 var(--promo-radius);
    }

    #promoManagementRoot .input-group > :last-child {
        border-radius: 0 var(--promo-radius) var(--promo-radius) 0;
    }

    #promoManagementRoot .promo-kpi {
        min-height: 105px;
        padding: 16px;
    }

    #promoManagementRoot .promo-kpi-icon {
        width: 44px;
        height: 44px;
        min-width: 44px;
        border-radius: var(--promo-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
    }

    #promoManagementRoot .promo-kpi-label {
        color: var(--promo-muted);
        font-size: 11px;
        font-weight: 600;
        display: block;
        margin-bottom: 3px;
    }

    #promoManagementRoot .promo-kpi-value {
        font-size: 21px;
        line-height: 1.3;
        font-weight: 700;
        margin: 0;
        color: var(--bs-body-color);
    }

    #promoManagementRoot .promo-kpi-meta {
        display: block;
        margin-top: 3px;
        font-size: 10px;
        font-weight: 600;
    }

    #promoManagementRoot .promo-tabs-header {
        padding: 12px 16px 0;
        background: #fff;
        border-bottom: 1px solid var(--promo-border);
    }

    #promoManagementRoot .nav-tabs {
        flex-wrap: nowrap;
        overflow-x: auto;
        overflow-y: hidden;
        border-bottom: 0;
        scrollbar-width: thin;
    }

    #promoManagementRoot .nav-tabs .nav-link {
        white-space: nowrap;
        padding: 10px 14px;
        font-size: 12px;
        font-weight: 600;
        color: var(--promo-muted);
        border-radius: var(--promo-radius) var(--promo-radius) 0 0;
        border: 1px solid transparent;
    }

    #promoManagementRoot .nav-tabs .nav-link.active {
        color: var(--bs-primary);
        background: #fff;
        border-color: var(--promo-border) var(--promo-border) #fff;
    }

    #promoManagementRoot .promo-tab-body {
        padding: 18px;
    }

    #promoManagementRoot .promo-table-wrap {
        width: 100%;
        overflow-x: auto;
        border: 1px solid var(--promo-border);
        border-radius: var(--promo-radius);
        -webkit-overflow-scrolling: touch;
    }

    #promoManagementRoot .promo-table {
        min-width: 1050px;
        margin-bottom: 0;
        vertical-align: middle;
    }

    #promoManagementRoot .promo-table th {
        padding: 11px 10px;
        background: var(--bs-tertiary-bg, #f8f9fa);
        color: #667085;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        white-space: nowrap;
        border-bottom: 1px solid var(--promo-border);
    }

    #promoManagementRoot .promo-table td {
        padding: 11px 10px;
        font-size: 12px;
        border-color: var(--promo-border);
    }

    #promoManagementRoot .promo-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #promoManagementRoot .promo-table .badge {
        font-size: 9px;
        font-weight: 600;
        border-radius: 4px;
    }

    #promoManagementRoot .promo-table .form-check-input {
        cursor: pointer;
        width: 2em;
        height: 1em;
    }

    #promoManagementRoot .promo-table .btn-group .btn {
        min-width: 30px;
    }

    #promoManagementRoot .promo-filter-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 14px;
    }

    #promoManagementRoot .promo-search {
        width: 100%;
        max-width: 340px;
    }

    #promoManagementRoot .promo-progress {
        height: 4px;
        max-width: 90px;
        background: #edf0f2;
        border-radius: 5px;
    }

    #promoManagementRoot .promo-progress .progress-bar {
        border-radius: 5px;
    }

    #promoManagementRoot .promo-filter-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        padding: 4px;
        background: var(--bs-tertiary-bg, #f8f9fa);
        border: 1px solid var(--promo-border);
        border-radius: var(--promo-radius);
    }

    #promoManagementRoot .promo-filter-buttons .btn {
        white-space: nowrap;
        padding: 6px 10px;
    }

    #promoManagementRoot .promo-empty {
        padding: 35px 15px !important;
        color: var(--promo-muted);
        font-size: 12px;
        text-align: center;
    }

    #promoManagementRoot .promo-empty i {
        display: block;
        margin-bottom: 10px;
        font-size: 25px;
        opacity: .45;
    }

    #promoManagementRoot .pagination {
        margin-bottom: 0;
        flex-wrap: wrap;
    }

    #deleteConfirmModal .modal-content {
        border-radius: var(--promo-radius) !important;
    }

    @media (max-width: 767.98px) {
        #promoManagementRoot {
            padding: 16px 12px !important;
        }

        #promoManagementRoot .promo-header {
            align-items: flex-start !important;
        }

        #promoManagementRoot .promo-header h4 {
            font-size: 17px;
        }

        #promoManagementRoot .promo-header-actions {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            width: 100%;
        }

        #promoManagementRoot .promo-header-actions .btn {
            white-space: normal;
            min-height: 38px;
            padding: 7px 8px;
        }

        #promoManagementRoot .promo-kpi {
            min-height: 95px;
            padding: 11px;
        }

        #promoManagementRoot .promo-kpi-icon {
            width: 36px;
            height: 36px;
            min-width: 36px;
            font-size: 14px;
        }

        #promoManagementRoot .promo-kpi-value {
            font-size: 17px;
        }

        #promoManagementRoot .promo-kpi-label {
            font-size: 10px;
        }

        #promoManagementRoot .promo-tab-body {
            padding: 12px;
        }

        #promoManagementRoot .promo-search {
            max-width: 100% !important;
        }

        #promoManagementRoot .promo-filter-bar > form {
            width: 100%;
            max-width: 100% !important;
        }

        #promoManagementRoot .promo-filter-bar > .small,
        #promoManagementRoot .promo-filter-bar > span {
            font-size: 11px;
        }

        #promoManagementRoot .promo-filter-buttons {
            width: 100%;
        }

        #promoManagementRoot .promo-filter-buttons .btn {
            flex: 1 1 auto;
            font-size: 10px;
            padding: 6px 7px;
        }

        #promoManagementRoot .promo-table {
            min-width: 1050px;
        }
    }

    @media (max-width: 380px) {
        #promoManagementRoot .promo-header-actions {
            grid-template-columns: 1fr;
        }

        #promoManagementRoot .promo-kpi {
            padding: 9px;
        }

        #promoManagementRoot .promo-kpi-icon {
            width: 32px;
            height: 32px;
            min-width: 32px;
        }

        #promoManagementRoot .promo-kpi-value {
            font-size: 15px;
        }
    }
</style>

<div class="container-fluid px-3 py-4" id="promoManagementRoot">

    <!-- Page Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 promo-header">
        <div>
            <h4 class="text-dark mb-1">
                <i class="fas fa-tags text-primary me-2"></i>Coupons &amp; Vouchers
            </h4>
            <p class="text-muted">Manage discounts, vouchers and redemptions.</p>
        </div>

        <div class="d-flex gap-2 promo-header-actions">
            <a href="{{ $couponCreateUrl }}" class="btn btn-primary btn-sm px-3">
                <i class="fas fa-plus me-1"></i> Add Coupon
            </a>
            <a href="{{ $voucherCreateUrl }}" class="btn btn-success btn-sm px-3">
                <i class="fas fa-gift me-1"></i> Add Voucher
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">

        <div class="col-6 col-lg-3">
            <div class="card promo-kpi h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="promo-kpi-icon bg-primary-subtle text-primary">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="promo-kpi-label">Total Coupons</span>
                        <h4 class="promo-kpi-value">{{ number_format($metrics['total_coupons'] ?? 0) }}</h4>
                        <small class="promo-kpi-meta text-success">{{ $metrics['active_coupons'] ?? 0 }} Active</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card promo-kpi h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="promo-kpi-icon bg-success-subtle text-success">
                        <i class="fas fa-gift"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="promo-kpi-label">Voucher Campaigns</span>
                        <h4 class="promo-kpi-value">{{ number_format($metrics['total_vouchers'] ?? 0) }}</h4>
                        <small class="promo-kpi-meta text-success">{{ $metrics['active_vouchers'] ?? 0 }} Active</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card promo-kpi h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="promo-kpi-icon bg-info-subtle text-info">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="promo-kpi-label">Active Redemptions</span>
                        <h4 class="promo-kpi-value">{{ number_format($activeRedemptionsCount) }}</h4>
                        <div class="d-flex align-items-center flex-wrap gap-1 mt-1">
                            <small class="text-success fw-semibold" style="font-size: 10px;">{{ $activeRedemptionsCount }} Applied</small>
                            @if($voidedRedemptionsCount > 0)
                                <span class="badge bg-warning-subtle text-dark border border-warning-subtle py-0 px-1" style="font-size: 9px;" title="Voided or Restored Redemptions">
                                    {{ $voidedRedemptionsCount }} Voided
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card promo-kpi h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="promo-kpi-icon bg-danger-subtle text-danger">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="promo-kpi-label">Discount Given</span>
                        <h4 class="promo-kpi-value">৳{{ number_format($metrics['total_discount_given'] ?? 0, 2) }}</h4>
                        <small class="text-muted d-block mt-1" style="font-size: 10px;">Customer savings</small>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Main Tabs -->
    <div class="card overflow-hidden">

        <div class="promo-tabs-header">
            <ul class="nav nav-tabs" id="promoTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'coupons' ? 'active' : '' }}"
                       href="{{ $couponIndexUrl }}?tab=coupons">
                        <i class="fas fa-ticket-alt me-1"></i>
                        Coupons ({{ $metrics['total_coupons'] ?? 0 }})
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'vouchers' ? 'active' : '' }}"
                       href="{{ $couponIndexUrl }}?tab=vouchers">
                        <i class="fas fa-gift me-1"></i>
                        Vouchers ({{ $metrics['total_vouchers'] ?? 0 }})
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'redemptions' ? 'active' : '' }}"
                       href="{{ $couponIndexUrl }}?tab=redemptions">
                        <i class="fas fa-history me-1"></i>
                        Redemptions ({{ $activeRedemptionsCount }})
                        @if($voidedRedemptionsCount > 0)
                            <span class="badge bg-warning text-dark ms-1 py-0 px-1" style="font-size: 9px;">{{ $voidedRedemptionsCount }} voided</span>
                        @endif
                    </a>
                </li>
            </ul>
        </div>

        <div class="promo-tab-body">

            {{-- =========================================================================
                 TAB 1: STORE COUPONS
                 ========================================================================= --}}
            @if($tab === 'coupons')

                <div class="promo-filter-bar">
                    <form method="GET" action="{{ $couponIndexUrl }}" class="d-flex gap-2 promo-search">
                        <input type="hidden" name="tab" value="coupons">

                        <div class="input-group input-group-sm">
                            <input type="text" name="search_coupon" class="form-control" placeholder="Search code or name..." value="{{ request('search_coupon') }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>

                        @if(request('search_coupon'))
                            <a href="{{ $couponIndexUrl }}?tab=coupons" class="btn btn-light btn-sm border" title="Clear search">
                                <i class="fas fa-undo"></i>
                            </a>
                        @endif
                    </form>

                    <div class="small text-muted">Showing {{ $coupons->count() }} coupons</div>
                </div>

                <div class="promo-table-wrap">
                    <table class="table table-hover promo-table">
                        <thead>
                            <tr>
                                <th>Code / Name</th>
                                <th>Discount</th>
                                <th>Min / Max Spend</th>
                                <th>Usage / Limit</th>
                                <th>Validity</th>
                                <th>Eligibility</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($coupons as $c)
                                @php
                                    $editCouponUrl   = Route::has('admin.promo.coupons.edit') ? route('admin.promo.coupons.edit', $c->id) : url('/admin/promo/coupons/edit/' . $c->id);
                                    $deleteCouponUrl = Route::has('admin.promo.coupons.destroy') ? route('admin.promo.coupons.destroy', $c->id) : url('/admin/promo/coupons/delete/' . $c->id);
                                    $toggleCouponUrl = Route::has('admin.promo.coupons.toggle_status') ? route('admin.promo.coupons.toggle_status', $c->id) : url('/admin/promo/coupons/toggle-status/' . $c->id);

                                    // Dynamic Live Usage Count calculation
                                    $usedCount = (int)($c->dynamic_used_count ?? ($c->used_count ?? 0));
                                    $usedCount = max($usedCount, (int)($c->used_count ?? 0));
                                    $limitCount = !empty($c->total_usage_limit) ? (int)$c->total_usage_limit : (!empty($c->usage_limit) ? (int)$c->usage_limit : null);
                                    $isLimitReached = ($limitCount !== null && $usedCount >= $limitCount);
                                    $usagePct = $limitCount ? min(100, round(($usedCount / $limitCount) * 100)) : 0;

                                    $startDate = !empty($c->start_date) ? \Carbon\Carbon::parse($c->start_date) : null;
                                    $expireDate = !empty($c->expire_date) ? \Carbon\Carbon::parse($c->expire_date) : (!empty($c->expires_at) ? \Carbon\Carbon::parse($c->expires_at) : null);
                                    $isExpired = $expireDate && $expireDate->isPast();
                                    $isUpcoming = $startDate && $startDate->isFuture();
                                    $discountSaved = (float)($c->dynamic_discount_disbursed ?? 0);
                                    $targetUser = $c->target_user_type ?? 'all';
                                @endphp

                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1">
                                                {{ $c->code }}
                                            </span>

                                            @if($c->name)
                                                <div>
                                                    <strong class="d-block text-dark small">{{ $c->name }}</strong>
                                                    @if(!empty($c->badge_text))
                                                        <span class="badge bg-warning text-dark">{{ $c->badge_text }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        @if($c->discount_type === 'percentage')
                                            <strong class="text-dark">{{ (float)$c->discount_amount }}% OFF</strong>
                                            @if(!empty($c->max_discount_amount) || !empty($c->max_discount))
                                                <small class="text-muted d-block" style="font-size: 10px;">Up to ৳{{ number_format($c->max_discount_amount ?? $c->max_discount, 2) }}</small>
                                            @endif
                                        @elseif($c->discount_type === 'free_shipping')
                                            <span class="badge bg-info-subtle text-info border">FREE SHIPPING</span>
                                        @else
                                            <strong class="text-dark">৳{{ number_format($c->discount_amount ?? ($c->value ?? 0), 2) }} OFF</strong>
                                        @endif
                                    </td>

                                    <td>
                                        <small class="text-muted d-block">Min: ৳{{ number_format($c->min_order_amount ?? ($c->min_spend ?? 0), 0) }}</small>
                                        @if(!empty($c->max_order_amount))
                                            <small class="text-muted d-block">Max: ৳{{ number_format($c->max_order_amount, 0) }}</small>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="small fw-bold">
                                            <span class="{{ $isLimitReached ? 'text-danger fw-bolder' : 'text-dark' }}">{{ $usedCount }}</span> /
                                            <span class="text-muted">{{ $limitCount ? $limitCount : '∞' }}</span>
                                        </div>

                                        @if($limitCount)
                                            <div class="progress promo-progress mt-1" title="{{ $usagePct }}% Used">
                                                <div class="progress-bar {{ $isLimitReached ? 'bg-danger' : ($usagePct > 75 ? 'bg-warning' : 'bg-success') }}" style="width: {{ $usagePct }}%;"></div>
                                            </div>
                                        @endif

                                        <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                                            <small class="text-muted" style="font-size: 10px;">Per user: {{ $c->per_user_limit ?? ($c->usage_limit_per_user ?? 1) }}</small>
                                            @if($isLimitReached)
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Limit Reached</span>
                                            @endif
                                        </div>

                                        @if($discountSaved > 0)
                                            <small class="text-success fw-bold d-block mt-1" style="font-size: 10px;">
                                                <i class="fas fa-coins me-1"></i>৳{{ number_format($discountSaved, 2) }} saved
                                            </small>
                                        @endif
                                    </td>

                                    <td>
                                        <small class="d-block text-muted" style="font-size: 11px;">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            {{ $startDate ? $startDate->format('d M, Y') : 'Immediate' }}
                                        </small>

                                        <small class="d-block text-muted" style="font-size: 11px;">
                                            <i class="far fa-clock me-1"></i>
                                            {{ $expireDate ? $expireDate->format('d M, Y') : 'Never Expires' }}
                                        </small>

                                        @if($isExpired)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle mt-1">Expired</span>
                                        @elseif($isUpcoming)
                                            <span class="badge bg-info-subtle text-info border border-info-subtle mt-1">Upcoming</span>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle mt-1">Active</span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            @if($c->is_public)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="width: fit-content;">Public</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border" style="width: fit-content;">Secret Code</span>
                                            @endif

                                            @if($targetUser === 'registered_only')
                                                <span class="badge bg-info-subtle text-info border border-info-subtle" style="width: fit-content;">
                                                    <i class="fas fa-user-lock me-1"></i> Registered
                                                </span>
                                            @elseif($targetUser === 'guest_only')
                                                <span class="badge bg-warning-subtle text-dark border border-warning-subtle" style="width: fit-content;">
                                                    <i class="fas fa-user-tag me-1"></i> Guests
                                                </span>
                                            @else
                                                <span class="badge bg-light text-muted border" style="width: fit-content;">
                                                    <i class="fas fa-users me-1"></i> All Users
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input toggle-status-switch" type="checkbox" role="switch"
                                                   data-url="{{ $toggleCouponUrl }}"
                                                   {{ $c->is_active ? 'checked' : '' }}>
                                        </div>
                                    </td>

                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ $editCouponUrl }}" class="btn btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" title="Delete" onclick="confirmDelete('{{ $deleteCouponUrl }}', 'Coupon: {{ $c->code }}')">
                                                <i class="far fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="8" class="promo-empty">
                                        <i class="fas fa-ticket-alt"></i>
                                        No coupons found. <span class="fw-semibold">Add a coupon</span> to get started.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($coupons->hasPages())
                    <div class="mt-3 d-flex justify-content-end">
                        {{ $coupons->links() }}
                    </div>
                @endif

            {{-- =========================================================================
                 TAB 2: VOUCHER CAMPAIGNS
                 ========================================================================= --}}
            @elseif($tab === 'vouchers')

                <div class="promo-filter-bar">
                    <form method="GET" action="{{ $couponIndexUrl }}" class="d-flex gap-2 promo-search">
                        <input type="hidden" name="tab" value="vouchers">

                        <div class="input-group input-group-sm">
                            <input type="text" name="search_voucher" class="form-control" placeholder="Search vouchers..." value="{{ request('search_voucher') }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>

                        @if(request('search_voucher'))
                            <a href="{{ $couponIndexUrl }}?tab=vouchers" class="btn btn-light btn-sm border" title="Clear search">
                                <i class="fas fa-undo"></i>
                            </a>
                        @endif
                    </form>

                    <div class="small text-muted">Showing {{ $vouchers->count() }} vouchers</div>
                </div>

                <div class="promo-table-wrap">
                    <table class="table table-hover promo-table">
                        <thead>
                            <tr>
                                <th>Campaign</th>
                                <th>Prefix</th>
                                <th>Discount</th>
                                <th>Min Spend</th>
                                <th>Validity</th>
                                <th>Claims</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($vouchers as $v)
                                @php
                                    $editVoucherUrl   = Route::has('admin.promo.coupons.vouchers.edit') ? route('admin.promo.coupons.vouchers.edit', $v->id) : url('/admin/promo/coupons/vouchers/edit/' . $v->id);
                                    $deleteVoucherUrl = Route::has('admin.promo.coupons.vouchers.destroy') ? route('admin.promo.coupons.vouchers.destroy', $v->id) : url('/admin/promo/coupons/vouchers/delete/' . $v->id);
                                    $toggleVoucherUrl = Route::has('admin.promo.coupons.vouchers.toggle_status') ? route('admin.promo.coupons.vouchers.toggle_status', $v->id) : url('/admin/promo/coupons/vouchers/toggle-status/' . $v->id);

                                    $claimedCount = (int)($v->dynamic_claimed_count ?? ($v->claimed_count ?? 0));
                                    $usedVoucherCount = (int)($v->dynamic_used_count ?? 0);
                                    $totalVouchers = !empty($v->total_vouchers_count) ? (int)$v->total_vouchers_count : null;
                                    $isVoucherExhausted = ($totalVouchers !== null && $claimedCount >= $totalVouchers);
                                    $vPct = $totalVouchers ? min(100, round(($claimedCount / $totalVouchers) * 100)) : 0;
                                    $vDiscountSaved = (float)($v->dynamic_discount_disbursed ?? 0);
                                @endphp

                                <tr>
                                    <td>
                                        <strong class="text-dark small d-block">{{ $v->title }}</strong>
                                        <small class="text-muted" style="font-size: 10px;">Max {{ $v->max_claims_per_user }} per customer</small>
                                    </td>

                                    <td>
                                        <span class="badge bg-light text-secondary border font-monospace px-2 py-1">
                                            {{ $v->code_prefix }}
                                        </span>
                                    </td>

                                    <td>
                                        @if($v->voucher_type === 'percentage')
                                            <strong class="text-success">{{ (float)$v->discount_amount }}% OFF</strong>
                                            @if($v->max_discount)
                                                <small class="text-muted d-block" style="font-size: 10px;">Cap: ৳{{ number_format($v->max_discount, 2) }}</small>
                                            @endif
                                        @elseif($v->voucher_type === 'free_shipping')
                                            <span class="badge bg-info-subtle text-info border">FREE SHIPPING</span>
                                        @else
                                            <strong class="text-success">৳{{ number_format($v->discount_amount, 2) }} OFF</strong>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="small text-dark">৳{{ number_format($v->min_spend ?? 0, 0) }}</span>
                                    </td>

                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $v->validity_days }} Days after claim
                                        </span>

                                        @if($v->claim_start || $v->claim_end)
                                            <small class="text-muted d-block mt-1" style="font-size: 10px;">
                                                Window: {{ $v->claim_start ? \Carbon\Carbon::parse($v->claim_start)->format('d M') : 'Open' }} - {{ $v->claim_end ? \Carbon\Carbon::parse($v->claim_end)->format('d M, Y') : 'Open' }}
                                            </small>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="small fw-bold text-dark">
                                            <span class="{{ $isVoucherExhausted ? 'text-danger' : 'text-dark' }}">{{ $claimedCount }}</span> / {{ $totalVouchers ? $totalVouchers : '∞' }}
                                        </div>

                                        @if($totalVouchers)
                                            <div class="progress promo-progress mt-1" style="max-width: 100px;">
                                                <div class="progress-bar {{ $isVoucherExhausted ? 'bg-danger' : 'bg-success' }}" style="width: {{ $vPct }}%;"></div>
                                            </div>
                                        @endif

                                        <small class="text-muted d-block mt-1" style="font-size: 10px;">
                                            Redeemed: <strong>{{ $usedVoucherCount }}</strong> orders
                                        </small>

                                        @if($vDiscountSaved > 0)
                                            <small class="text-success fw-bold d-block" style="font-size: 10px;">
                                                ৳{{ number_format($vDiscountSaved, 2) }} saved
                                            </small>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input toggle-status-switch" type="checkbox" role="switch"
                                                   data-url="{{ $toggleVoucherUrl }}"
                                                   {{ $v->is_active ? 'checked' : '' }}>
                                        </div>
                                    </td>

                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ $editVoucherUrl }}" class="btn btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" title="Delete" onclick="confirmDelete('{{ $deleteVoucherUrl }}', 'Voucher: {{ $v->title }}')">
                                                <i class="far fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="8" class="promo-empty">
                                        <i class="fas fa-gift"></i>
                                        No vouchers found. <span class="fw-semibold">Add a voucher</span> to get started.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($vouchers->hasPages())
                    <div class="mt-3 d-flex justify-content-end">
                        {{ $vouchers->links() }}
                    </div>
                @endif

            {{-- =========================================================================
                 TAB 3: REDEMPTION LOGS
                 ========================================================================= --}}
            @elseif($tab === 'redemptions')

                <div class="promo-filter-bar">

                    <!-- Redemption Filters -->
                    <div class="promo-filter-buttons">
                        <button type="button" class="btn {{ $redemptionFilter === 'all' ? 'btn-white bg-white shadow-sm fw-bold text-dark' : 'btn-light text-muted' }}" onclick="filterRedemptionLogs('all')">
                            All ({{ $totalLogsCount }})
                        </button>

                        <button type="button" class="btn {{ $redemptionFilter === 'completed' ? 'btn-white bg-white shadow-sm fw-bold text-success' : 'btn-light text-muted' }}" onclick="filterRedemptionLogs('completed')">
                            <i class="fas fa-check-circle me-1"></i> Completed ({{ $activeRedemptionsCount }})
                        </button>

                        <button type="button" class="btn {{ $redemptionFilter === 'restored' ? 'btn-white bg-white shadow-sm fw-bold text-warning' : 'btn-light text-muted' }}" onclick="filterRedemptionLogs('restored')">
                            <i class="fas fa-undo me-1"></i> Restored ({{ $voidedRedemptionsCount }})
                        </button>
                    </div>

                    <span class="small text-muted">Showing {{ $redemptions->count() }} records</span>
                </div>

                <div class="promo-table-wrap">
                    <table class="table table-hover promo-table" id="redemptionLogsTable">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Type</th>
                                <th>Code</th>
                                <th>Discount</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($redemptions as $r)
                                @php
                                    $orderViewUrl = Route::has('admin.order.view') ? route('admin.order.view', $r->order_id) : url('/admin/orders/view/' . $r->order_id);
                                    $toggleRedemptionUrl = url('/admin/promo/coupons/redemptions/' . $r->id . '/toggle-status');

                                    // 100% Fail-safe Property Access
                                    $rCode = $r->code_used ?? ($r->coupon_code ?? ($r->code ?? ($r->voucher_code ?? ($r->promo_code ?? 'N/A'))));
                                    $rDiscount = (float)($r->discount_amount ?? ($r->discount ?? ($r->amount ?? ($r->value ?? 0))));
                                    $rType = $r->discount_type ?? ($r->type ?? 'coupon');
                                    $rStatus = $r->status ?? 'completed';
                                    $isVoided = in_array($rStatus, ['restored', 'void', 'voided', 'cancelled']);
                                @endphp

                                <tr class="redemption-log-row {{ $isVoided ? 'bg-light bg-opacity-75 table-light' : '' }}" data-status="{{ $isVoided ? 'restored' : 'completed' }}">
                                    <td>
                                        <a href="{{ $orderViewUrl }}" class="fw-bold {{ $isVoided ? 'text-secondary' : 'text-primary' }} text-decoration-none small">
                                            {{ $r->invoice_no ?? ('Order #' . $r->order_id) }}
                                        </a>
                                    </td>

                                    <td>
                                        <strong class="d-block small text-dark">{{ $r->user_name ?? 'Guest User' }}</strong>
                                        @if(!empty($r->user_phone))
                                            <small class="text-muted" style="font-size: 10px;">{{ $r->user_phone }}</small>
                                        @endif
                                    </td>

                                    <td>
                                        @if($rType === 'voucher')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="fas fa-gift me-1"></i> Voucher
                                            </span>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                <i class="fas fa-tag me-1"></i> Coupon
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="font-monospace fw-bold {{ $isVoided ? 'text-decoration-line-through text-muted' : 'text-dark' }} small">{{ $rCode }}</span>
                                    </td>

                                    <td>
                                        <strong class="{{ $isVoided ? 'text-muted text-decoration-line-through' : 'text-danger' }} small">-৳{{ number_format($rDiscount, 2) }}</strong>
                                    </td>

                                    <td>
                                        <small class="text-muted" style="font-size: 11px;">
                                            {{ \Carbon\Carbon::parse($r->created_at)->format('d M, Y h:i A') }}
                                        </small>
                                    </td>

                                    <td>
                                        @if(!$isVoided)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i> Completed
                                            </span>
                                        @else
                                            <span class="badge bg-warning text-dark border border-warning-subtle">
                                                <i class="fas fa-undo me-1"></i> Restored / Voided
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-end">
                                        @if(!$isVoided)
                                            <a href="{{ $toggleRedemptionUrl }}" class="btn btn-outline-warning btn-sm py-1 px-2" title="Void and restore quota">
                                                <i class="fas fa-undo me-1"></i> Void
                                            </a>
                                        @else
                                            <a href="{{ $toggleRedemptionUrl }}" class="btn btn-outline-success btn-sm py-1 px-2" title="Re-complete redemption">
                                                <i class="fas fa-check me-1"></i> Re-apply
                                            </a>
                                        @endif
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="8" class="promo-empty">
                                        <i class="fas fa-history"></i>
                                        No redemptions found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($redemptions->hasPages())
                    <div class="mt-3 d-flex justify-content-end">
                        {{ $redemptions->links() }}
                    </div>
                @endif

            @endif

        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-3">
                <div class="text-danger mb-2">
                    <i class="fas fa-trash-alt fa-2x"></i>
                </div>

                <h5 class="fw-bold text-dark mb-1 fs-6">Delete Item?</h5>
                <p id="deleteItemLabel" class="text-muted small mb-3">Are you sure you want to delete this item?</p>

                <form id="deleteActionForm" method="POST">
                    @csrf
                    @method('DELETE')

                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-sm btn-light border px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-danger px-3">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Live AJAX Status Switch for Coupons and Vouchers
    document.querySelectorAll('.toggle-status-switch').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            var url = this.getAttribute('data-url');
            var isChecked = this.checked;
            var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (!data.success) {
                    toggle.checked = !isChecked;
                    alert(data.message || 'Failed to update status.');
                }
            })
            .catch(function() {
                toggle.checked = !isChecked;
                alert('Connection error occurred.');
            });
        });
    });
});

function filterRedemptionLogs(status) {
    var rows = document.querySelectorAll('.redemption-log-row');
    rows.forEach(function(row) {
        var rStatus = row.getAttribute('data-status');
        if (status === 'all') {
            row.style.display = '';
        } else if (status === 'completed' && rStatus === 'completed') {
            row.style.display = '';
        } else if (status === 'restored' && rStatus === 'restored') {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function confirmDelete(url, label) {
    var modalEl = document.getElementById('deleteConfirmModal');
    var form = document.getElementById('deleteActionForm');
    var labelEl = document.getElementById('deleteItemLabel');

    if (form) form.action = url;
    if (labelEl) labelEl.innerText = 'Are you sure you want to delete "' + label + '"?';

    if (window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}
</script>
@endsection