@extends('layouts.admin')

@section('title', 'Marketing Promo Coupons - Email Marketing')

@php
    $totalCouponsCount = $coupons->total();$activeCouponsCount = $coupons->filter(function($c) {
        return !isset($c->status) || $c->status == 1 || $c->status === 'active';
    })->count();
    $inactiveCouponsCount = $totalCouponsCount -$activeCouponsCount;
@endphp

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-coupons-root .btn,
    #email-marketing-coupons-root .btn-sm,
    #email-marketing-coupons-root button,
    #email-marketing-coupons-root .badge,
    #email-marketing-coupons-root .alert,
    #email-marketing-coupons-root .form-control,
    #email-marketing-coupons-root .form-select,
    #email-marketing-coupons-root .input-group-text,
    #email-marketing-coupons-root .card,
    #email-marketing-coupons-root .metric-card,
    #email-marketing-coupons-root .page-link,
    #email-marketing-coupons-root .page-item span,
    #email-marketing-coupons-root .collapse-toggle-btn,
    .coupon-modal-scope .modal-content,
    .coupon-modal-scope .btn {
        border-radius: 5px !important;
    }

    #email-marketing-coupons-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #email-marketing-coupons-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Safe Monospace & Bengali Digit Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Compact Sleek Button Styling */
    .btn-compact {
        font-size: 11px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    /* Collapsible Trigger Buttons & Rotating Chevron */
    .collapse-toggle-btn {
        width: 24px;
        height: 24px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        color: #475569;
        transition: all 0.2s ease;
    }
    .collapse-toggle-btn:hover {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }
    .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }
    .collapse-icon {
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        font-size: 10px;
    }

    /* Enterprise Surface Cards */
    #email-marketing-coupons-root .card {
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }
    .section-header-wrap {
        padding: 10px 14px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
    }

    /* Metric Cards - Zero Overflow Architecture */
    .metric-card {
        padding: 12px 14px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }
    .stat-label-title {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.02em;
        line-height: 1.2;
    }
    .stat-val-num {
        font-size: 18px;
        line-height: 1.25;
        letter-spacing: -0.02em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .stat-sub-text {
        font-size: 10px;
        margin-top: 2px;
    }
    .metric-icon-square {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
        border-radius: 5px !important;
    }

    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-left: 3.5px solid #f59e0b !important; }
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-left: 3.5px solid #10b981 !important; }
    .metric-card-blue   { background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%); border-left: 3.5px solid #2563eb !important; }
    .metric-card-purple { background: linear-gradient(180deg, #ffffff 0%, #faf5ff 100%); border-left: 3.5px solid #a855f7 !important; }

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .custom-coupons-table {
        width: 100%;
        margin-bottom: 0;
    }
    .custom-coupons-table thead th {
        font-size: 10px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 8px;
        white-space: nowrap;
    }
    .custom-coupons-table tbody td {
        padding: 8px 8px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        font-size: 11px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .custom-coupons-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .custom-coupons-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .custom-coupons-table th,
        .custom-coupons-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 5 Columns Proportional Distribution Summing to Exactly 100% */
        .col-vn-id     { width: 6% !important; }
        .col-vn-code   { width: 26% !important; }
        .col-vn-val    { width: 24% !important; }
        .col-vn-status { width: 18% !important; }
        .col-vn-action { width: 26% !important; }
    }

    /* Form Controls */
    #email-marketing-coupons-root .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #email-marketing-coupons-root .form-control,
    #email-marketing-coupons-root .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #email-marketing-coupons-root .form-control:focus,
    #email-marketing-coupons-root .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Bulletproof Responsive Pagination */
    .card-footer nav {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        width: auto !important;
        margin: 0 !important;
    }
    .card-footer nav > div.d-sm-none,
    .card-footer nav > .d-sm-none {
        display: none !important;
    }
    .card-footer nav > div.d-none,
    .card-footer nav > .d-none.flex-sm-fill {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .card-footer nav p,
    .card-footer nav .small.text-muted,
    .card-footer nav div:has(> p),
    .card-footer nav > div.d-none > div:first-child,
    .card-footer nav .d-none.flex-sm-fill > div:first-child {
        display: none !important;
    }
    .card-footer .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 3px !important;
        padding: 0 !important;
        list-style: none !important;
    }
    .card-footer .page-item {
        display: inline-flex !important;
    }
    .card-footer .page-item .page-link,
    .card-footer .page-item span {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 25px !important;
        height: 25px !important;
        padding: 0 6px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        color: #475569 !important;
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 5px !important;
        text-decoration: none !important;
        line-height: 1 !important;
        transition: all 0.12s ease !important;
    }
    .card-footer .page-item .page-link:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }
    .card-footer .page-item.active .page-link,
    .card-footer .page-item.active span {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25) !important;
    }
    .card-footer .page-item.disabled .page-link,
    .card-footer .page-item.disabled span {
        opacity: 0.45 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }
    .card-footer nav svg {
        width: 10px !important;
        height: 10px !important;
    }

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .coupons-header-wrap h5 {
            font-size: 16px !important;
        }
        .top-action-btns {
            width: 100%;
        }
        .top-action-btns .btn {
            width: 100%;
            padding: 8px 6px !important;
            font-size: 11px !important;
            justify-content: center;
        }
        .metric-card {
            padding: 10px !important;
            min-height: 84px !important;
        }
        .stat-label-title {
            font-size: 10px !important;
        }
        .stat-val-num {
            font-size: 14px !important;
        }
        .stat-sub-text {
            font-size: 9px !important;
        }
        .metric-icon-square {
            width: 28px !important;
            height: 28px !important;
            font-size: 11px !important;
        }
        .pc-fit-container {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        .custom-coupons-table {
            min-width: 680px !important;
            table-layout: auto !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-coupons-root">

    {{-- 1. Header & Navigation (Original Clean Light Theme) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 coupons-header-wrap">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-tags text-primary"></i>
                <span>Marketing Promo Coupons</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Select store promo vouchers to embed directly into broadcasts or cart recovery drip sequences.
            </small>
        </div>

        <div class="d-flex gap-2 top-action-btns">
            <a href="{{ route('admin.email_marketing.campaigns.create') }}" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold">
                <i class="fas fa-plus-circle me-1"></i> <span>Create Campaign With Coupon</span>
            </a>
        </div>
    </div>

    {{-- 2. Feedback Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 3. Voucher Telemetry KPI Overview (Collapsible Section) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-chart-pie text-primary me-1"></i> Promo Vouchers &amp; Campaign Readiness Overview
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseCouponsKpis" aria-expanded="true" aria-controls="collapseCouponsKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseCouponsKpis">
            <div class="p-3">
                <div class="row g-2">
                    {{-- Total Vouchers --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Total Vouchers</span>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                                    <i class="fas fa-ticket"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    {{ number_format($totalCouponsCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">In store database</small>
                            </div>
                        </div>
                    </div>

                    {{-- Active Store Coupons --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Active Vouchers</span>
                                <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                                    <i class="fas fa-circle-check"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num">
                                    {{ number_format($activeCouponsCount) }}
                                </h4>
                                <small class="text-success d-block text-truncate font-monospace stat-sub-text">Ready to broadcast</small>
                            </div>
                        </div>
                    </div>

                    {{-- Inactive / Expired --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Inactive Vouchers</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                                    <i class="fas fa-pause"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-primary bn-digit font-monospace stat-val-num">
                                    {{ number_format($inactiveCouponsCount) }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Disabled vouchers</small>
                            </div>
                        </div>
                    </div>

                    {{-- Marketing Readiness --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-purple h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Automation Ready</span>
                                <div class="metric-icon-square bg-purple-subtle text-primary shadow-xs" style="background-color: #f3e8ff; color: #7e22ce;">
                                    <i class="fas fa-bolt"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    100%
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Token {coupon_code} ready</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Instant Live Search & Status Filter Toolbar (Collapsible) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-filter text-primary me-1"></i> Search Vouchers &amp; Filter by Status
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseCouponsFilter" aria-expanded="true" aria-controls="collapseCouponsFilter" title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseCouponsFilter">
            <div class="p-3">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-5">
                        <label class="form-label" for="couponSearchInput">
                            <i class="fas fa-search me-1 text-primary"></i> Search Voucher Code or Discount
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                            <input type="text" id="couponSearchInput" class="form-control border-start-0 ps-0 shadow-none" placeholder="WELCOME10, EID2026, 100..." onkeyup="filterCouponsTable()">
                        </div>
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label" for="couponStatusFilter">
                            <i class="fas fa-toggle-on me-1 text-success"></i> Voucher Status
                        </label>
                        <select id="couponStatusFilter" class="form-select shadow-none font-monospace" onchange="filterCouponsTable()">
                            <option value="all" selected>All Statuses</option>
                            <option value="active">Active Only</option>
                            <option value="inactive">Inactive Only</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <button type="button" class="btn btn-light border btn-compact w-100 shadow-xs fw-semibold text-secondary" onclick="resetCouponFilters()">
                            <i class="fas fa-rotate-left me-1"></i> <span>Reset Filters</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. Coupons Table Card (Desktop 100% Fit Screen with Zero Horizontal Scroll) --}}
    <div class="card border-0 shadow-sm overflow-hidden mb-3">
        <div class="p-2 px-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 bg-light bg-opacity-25">
            <div>
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13px;">
                    <i class="fas fa-ticket-simple text-warning"></i> <span>Available Store Promo Vouchers</span>
                </h6>
                <small class="text-muted font-monospace bn-digit" style="font-size: 10px;">
                    Showing {{ $coupons->firstItem() ?? 0 }} to {{ $coupons->lastItem() ?? 0 }} of {{$coupons->total() }} coupons
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 10px;" id="visible-coupons-count">
                    {{ $coupons->total() }} Total
                </span>
                <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseCouponsTable" aria-expanded="true" aria-controls="collapseCouponsTable" title="Toggle Table">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseCouponsTable">
            <div class="table-responsive pc-fit-container">
                <table class="table align-middle mb-0 custom-coupons-table" id="coupons-table">
                    <thead>
                        <tr>
                            <th class="ps-3 col-vn-id text-center">#ID</th>
                            <th class="col-vn-code">Voucher Code</th>
                            <th class="col-vn-val">Discount Value</th>
                            <th class="col-vn-status">Status</th>
                            <th class="text-end pe-3 col-vn-action">Marketing Action</th>
                        </tr>
                    </thead>
                    <tbody id="coupons-tbody">
                        @forelse($coupons as $coupon)
                            @php
                                $discount = $coupon->discount_amount ?? ($coupon->discount ?? ($coupon->amount ?? ($coupon->value ?? null)));
                                $isActive = !isset($coupon->status) || $coupon->status == 1 || $coupon->status === 'active';
                                $statusSlug =$isActive ? 'active' : 'inactive';

                                $rowSearchData = strtolower(
                                    ($coupon->code ?? '') . ' ' .
                                    ($discount ?? '') . ' ' .
                                    $statusSlug
                                );
                            @endphp
                            <tr class="coupon-entry-row" data-search="{{ $rowSearchData }}" data-status="{{ $statusSlug }}">
                                <td class="ps-3 col-vn-id text-center font-monospace bn-digit text-muted" style="font-size: 11px;">
                                    {{ $coupon->id }}
                                </td>
                                <td class="col-vn-code">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <span class="badge bg-light text-dark border font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 11.5px; letter-spacing: 0.5px;">
                                            {{ $coupon->code }}
                                        </span>
                                        <button type="button" class="btn btn-sm btn-light border p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 22px; height: 22px; border-radius: 4px !important;" onclick="copyCouponCode('{{ addslashes($coupon->code) }}')" title="Copy Coupon Code">
                                            <i class="fas fa-copy text-secondary" style="font-size: 9.5px;"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="col-vn-val font-monospace bn-digit">
                                    @if($discount)
                                        <span class="fw-bold text-success" style="font-size: 12.5px;">৳ {{ number_format($discount, 2) }}</span>
                                    @else
                                        <span class="badge bg-light text-secondary border font-monospace px-2 py-0" style="font-size: 10px;">Standard Discount</span>
                                    @endif
                                </td>
                                <td class="col-vn-status">
                                    @if($isActive)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 9.5px;">
                                            <i class="fas fa-circle-check me-1"></i> ACTIVE
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 9.5px;">
                                            INACTIVE
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-3 col-vn-action">
                                    <form action="{{ route('admin.email_marketing.coupons.create_campaign') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="coupon_id" value="{{ $coupon->id }}">
                                        <button type="submit" class="btn btn-sm btn-outline-primary btn-compact shadow-xs fw-semibold bg-white" title="Attach to Campaign">
                                            <i class="fas fa-paper-plane me-1"></i> <span>Attach To Campaign</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyCouponsRow">
                                <td colspan="5" class="text-center py-5 text-muted small">
                                    <i class="fas fa-ticket fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                    <span class="fw-semibold">No promotional coupons found in your store database.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Bulletproof Responsive Pagination Footer --}}
            @if($coupons->hasPages())
                <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">
                    <small class="text-muted font-monospace bn-digit" style="font-size: 11px;">
                        Showing {{ $coupons->firstItem() }} to {{ $coupons->lastItem() }} of {{$coupons->total() }} coupons
                    </small>
                    <div>
                        {{ $coupons->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Copy Notification Lightbox (Replaces browser alert) -->
<div class="modal fade coupon-modal-scope" id="couponCopyNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 44px; height: 44px; border-radius: 50% !important;">
                    <i class="fas fa-circle-check fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Voucher Code Copied!</h6>
                <p class="text-muted small mb-3" style="font-size: 11.5px; line-height: 1.45;">
                    Code <strong id="copiedVoucherCode" class="text-dark font-monospace">CODE</strong> is copied to clipboard. You can paste it into email subject or template.
                </p>
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    function copyCouponCode(code) {
        document.getElementById('copiedVoucherCode').innerText = code;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(code).then(() => {
                showCopyModal();
            }).catch(() => {
                showCopyModal();
            });
        } else {
            showCopyModal();
        }
    }

    function showCopyModal() {
        const modalEl = document.getElementById('couponCopyNoticeModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function filterCouponsTable() {
        const query  = (document.getElementById('couponSearchInput')?.value || '').toLowerCase().trim();
        const status = (document.getElementById('couponStatusFilter')?.value || 'all').toLowerCase();

        const rows = document.querySelectorAll('.coupon-entry-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowSearch = row.getAttribute('data-search') || '';
            const rowStatus = row.getAttribute('data-status') || '';

            const matchesQuery  = !query || rowSearch.includes(query);
            const matchesStatus = status === 'all' || rowStatus === status;

            if (matchesQuery && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const countBadge = document.getElementById('visible-coupons-count');
        if (countBadge) {
            countBadge.innerText = `${visibleCount} Showing`;
        }
    }

    function resetCouponFilters() {
        const searchInput  = document.getElementById('couponSearchInput');
        const statusFilter = document.getElementById('couponStatusFilter');
        if (searchInput)  searchInput.value = '';
        if (statusFilter) statusFilter.value = 'all';
        filterCouponsTable();
    }
</script>
@endsection