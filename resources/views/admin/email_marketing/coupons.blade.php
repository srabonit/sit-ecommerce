
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
/* =========================================================
   EMAIL MARKETING COUPONS
   Kaiadmin Lite v1.2.0 - Classic White Design
========================================================= */

#email-marketing-coupons-root {
    --coupon-primary: var(--bs-primary, #1572e8);
    --coupon-secondary: #6c757d;
    --coupon-success: #31ce36;
    --coupon-warning: #ffad46;
    --coupon-danger: #f25961;
    --coupon-info: #48abf7;

    --coupon-bg: #f8f9fa;
    --coupon-card: #ffffff;
    --coupon-heading: #1a2035;
    --coupon-text: #495057;
    --coupon-muted: #8d9498;
    --coupon-border: #ebecec;

    --coupon-radius: 5px;
    --coupon-shadow: 0 1px 3px rgba(0, 0, 0, .04);

    width: 100%;
    min-width: 0;
    color: var(--coupon-text);
    font-size: 13px;
}

#email-marketing-coupons-root *,
#email-marketing-coupons-root *::before,
#email-marketing-coupons-root *::after,
.coupon-modal-scope *,
.coupon-modal-scope *::before,
.coupon-modal-scope *::after {
    box-sizing: border-box;
}

/* Page Layout */

#email-marketing-coupons-root .coupons-page-header {
    margin-bottom: 20px;
}

#email-marketing-coupons-root .coupons-page-title {
    color: var(--coupon-heading);
    font-size: clamp(17px, 2vw, 21px);
    font-weight: 700;
    letter-spacing: -.35px;
    line-height: 1.35;
}

#email-marketing-coupons-root .coupons-page-description {
    color: var(--coupon-muted);
    font-size: 12px;
    line-height: 1.6;
    margin-bottom: 0;
}

#email-marketing-coupons-root .coupons-header-icon {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 5px;
    background: rgba(21, 114, 232, .09);
    color: var(--coupon-primary);
    font-size: 15px;
}

/* Global Component Radius */

#email-marketing-coupons-root .btn,
#email-marketing-coupons-root .badge,
#email-marketing-coupons-root .alert,
#email-marketing-coupons-root .form-control,
#email-marketing-coupons-root .form-select,
#email-marketing-coupons-root .input-group-text,
#email-marketing-coupons-root .card,
#email-marketing-coupons-root .page-link,
#email-marketing-coupons-root .collapse-toggle-btn,
.coupon-modal-scope .modal-content,
.coupon-modal-scope .btn {
    border-radius: var(--coupon-radius);
}

/* Cards */

#email-marketing-coupons-root .card {
    min-width: 0;
    background: var(--coupon-card);
    border: 1px solid var(--coupon-border);
    box-shadow: var(--coupon-shadow);
    margin-bottom: 16px;
}

#email-marketing-coupons-root .section-header-wrap {
    min-height: 45px;
    padding: 10px 15px;
    background: #fff;
    border-bottom: 1px solid var(--coupon-border);
}

#email-marketing-coupons-root .section-title {
    color: var(--coupon-heading);
    font-size: 12px;
    font-weight: 700;
    line-height: 1.4;
    margin: 0;
}

#email-marketing-coupons-root .section-title i {
    color: var(--coupon-primary);
}

/* Compact Buttons */

#email-marketing-coupons-root .btn-compact {
    min-height: 33px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 7px 12px;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.3;
    white-space: nowrap;
    transition: all .18s ease;
}

#email-marketing-coupons-root .btn {
    font-weight: 600;
}

#email-marketing-coupons-root .btn:focus-visible {
    box-shadow: 0 0 0 3px rgba(21, 114, 232, .15);
}

#email-marketing-coupons-root .btn-primary {
    background-color: var(--coupon-primary);
    border-color: var(--coupon-primary);
}

#email-marketing-coupons-root .btn-primary:hover {
    filter: brightness(.94);
}

#email-marketing-coupons-root .top-action-btns {
    flex-shrink: 0;
}

/* Collapse Toggle */

#email-marketing-coupons-root .collapse-toggle-btn {
    width: 28px;
    height: 28px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #6c757d;
    background: #fff;
    border: 1px solid var(--coupon-border);
    transition: all .2s ease;
}

#email-marketing-coupons-root .collapse-toggle-btn:hover {
    color: var(--coupon-primary);
    background: #f4f8ff;
    border-color: rgba(21, 114, 232, .3);
}

#email-marketing-coupons-root .collapse-icon {
    font-size: 10px;
    transition: transform .25s ease;
}

#email-marketing-coupons-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
    transform: rotate(180deg);
}

/* Alerts */

#email-marketing-coupons-root .alert {
    padding: 11px 14px;
    font-size: 12px;
    border: 1px solid transparent;
}

#email-marketing-coupons-root .alert-success {
    background: #f0faf2;
    border-color: #d5f0da;
}

#email-marketing-coupons-root .alert-danger {
    background: #fff3f3;
    border-color: #f8d7da;
}

/* =========================================================
   KPI METRIC CARDS
========================================================= */

#email-marketing-coupons-root .metric-card {
    position: relative;
    height: 100%;
    min-height: 105px;
    padding: 13px 14px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
    background: #fff;
    border: 1px solid var(--coupon-border);
    border-left: 3px solid var(--coupon-primary);
    box-shadow: none;
    transition: border-color .2s ease, box-shadow .2s ease;
}

#email-marketing-coupons-root .metric-card:hover {
    box-shadow: 0 3px 12px rgba(0, 0, 0, .055);
}

#email-marketing-coupons-root .metric-card-amber {
    border-left-color: #ffad46;
}

#email-marketing-coupons-root .metric-card-green {
    border-left-color: #31ce36;
}

#email-marketing-coupons-root .metric-card-blue {
    border-left-color: #1572e8;
}

#email-marketing-coupons-root .metric-card-purple {
    border-left-color: #6861ce;
}

#email-marketing-coupons-root .stat-label-title {
    color: #8d9498;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.4;
}

#email-marketing-coupons-root .stat-val-num {
    color: var(--coupon-heading);
    font-size: clamp(19px, 2vw, 23px);
    font-weight: 700;
    line-height: 1.3;
    letter-spacing: -.5px;
    overflow-wrap: anywhere;
}

#email-marketing-coupons-root .stat-sub-text {
    margin-top: 3px;
    color: var(--coupon-muted);
    font-size: 10px;
    line-height: 1.4;
}

#email-marketing-coupons-root .metric-icon-square {
    width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 5px;
    font-size: 14px;
}

#email-marketing-coupons-root .metric-card-amber .metric-icon-square {
    color: #e69b2c;
    background: #fff5e5;
}

#email-marketing-coupons-root .metric-card-green .metric-icon-square {
    color: #31a94a;
    background: #eaf8ed;
}

#email-marketing-coupons-root .metric-card-blue .metric-icon-square {
    color: #1572e8;
    background: #edf4ff;
}

#email-marketing-coupons-root .metric-card-purple .metric-icon-square {
    color: #6861ce;
    background: #f0efff;
}

/* =========================================================
   FORM & FILTER PANEL
========================================================= */

#email-marketing-coupons-root .form-label {
    display: block;
    margin-bottom: 6px;
    color: #495057;
    font-size: 11px;
    font-weight: 700;
}

#email-marketing-coupons-root .form-control,
#email-marketing-coupons-root .form-select {
    width: 100%;
    min-height: 36px;
    padding: 7px 11px;
    color: #495057;
    background-color: #fff;
    border: 1px solid #ebedf2;
    box-shadow: none;
    font-size: 12px;
    line-height: 1.4;
    transition: border-color .15s ease, box-shadow .15s ease;
}

#email-marketing-coupons-root .form-control::placeholder {
    color: #adb5bd;
    opacity: 1;
}

#email-marketing-coupons-root .form-control:focus,
#email-marketing-coupons-root .form-select:focus {
    border-color: var(--coupon-primary);
    box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
    outline: none;
}

#email-marketing-coupons-root .input-group-text {
    min-width: 37px;
    justify-content: center;
    color: #8d9498;
    background: #f8f9fa;
    border-color: #ebedf2;
    font-size: 12px;
}

#email-marketing-coupons-root .input-group > .input-group-text {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

#email-marketing-coupons-root .input-group > .form-control {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

#email-marketing-coupons-root .filter-reset-btn {
    min-height: 36px;
    background: #fff;
    border-color: #ebedf2;
    color: #6c757d;
}

#email-marketing-coupons-root .filter-reset-btn:hover {
    color: var(--coupon-primary);
    background: #f4f8ff;
    border-color: var(--coupon-primary);
}

/* =========================================================
   TABLE DESIGN
========================================================= */

#email-marketing-coupons-root .coupons-table-card {
    overflow: hidden;
}

#email-marketing-coupons-root .table-heading {
    color: var(--coupon-heading);
    font-size: 13px;
    font-weight: 700;
    line-height: 1.4;
}

#email-marketing-coupons-root .table-subtitle {
    color: var(--coupon-muted);
    font-size: 10px;
    line-height: 1.5;
}

#email-marketing-coupons-root .pc-fit-container {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

#email-marketing-coupons-root .custom-coupons-table {
    width: 100%;
    min-width: 690px;
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
    table-layout: auto;
}

#email-marketing-coupons-root .custom-coupons-table thead th {
    padding: 11px 10px;
    color: #6c757d;
    background: #f8f9fa;
    border-bottom: 1px solid #ebecec;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .45px;
    text-transform: uppercase;
    white-space: nowrap;
    vertical-align: middle;
}

#email-marketing-coupons-root .custom-coupons-table tbody td {
    padding: 11px 10px;
    color: #495057;
    background: #fff;
    border-bottom: 1px solid #f0f1f3;
    font-size: 11.5px;
    line-height: 1.5;
    vertical-align: middle;
}

#email-marketing-coupons-root .custom-coupons-table tbody tr:last-child td {
    border-bottom: 0;
}

#email-marketing-coupons-root .custom-coupons-table tbody tr:hover td {
    background: #fafbfd;
}

#email-marketing-coupons-root .custom-coupons-table .col-vn-id {
    width: 65px;
    min-width: 55px;
}

#email-marketing-coupons-root .custom-coupons-table .col-vn-code {
    min-width: 165px;
}

#email-marketing-coupons-root .custom-coupons-table .col-vn-val {
    min-width: 145px;
}

#email-marketing-coupons-root .custom-coupons-table .col-vn-status {
    min-width: 110px;
}

#email-marketing-coupons-root .custom-coupons-table .col-vn-action {
    min-width: 180px;
}

#email-marketing-coupons-root .custom-coupons-table .font-monospace {
    font-weight: 600;
}

#email-marketing-coupons-root .coupon-code-badge {
    display: inline-flex;
    align-items: center;
    max-width: 100%;
    padding: 6px 9px;
    color: #495057;
    background: #f8f9fa;
    border: 1px solid #ebedf2;
    border-radius: 5px;
    font-size: 11px;
    letter-spacing: .4px;
    overflow-wrap: anywhere;
}

#email-marketing-coupons-root .coupon-copy-btn {
    width: 27px;
    height: 27px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: #fff;
    border: 1px solid #ebedf2;
    color: #8d9498;
    border-radius: 5px;
    transition: all .15s ease;
}

#email-marketing-coupons-root .coupon-copy-btn:hover {
    color: var(--coupon-primary);
    background: #edf4ff;
    border-color: rgba(21, 114, 232, .3);
}

#email-marketing-coupons-root .coupon-status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 9px;
    border-radius: 5px;
    font-size: 9.5px;
    font-weight: 700;
    letter-spacing: .3px;
    white-space: nowrap;
}

#email-marketing-coupons-root .coupon-status-active {
    color: #28a745;
    background: #eaf8ed;
    border: 1px solid #d6f0dc;
}

#email-marketing-coupons-root .coupon-status-inactive {
    color: #6c757d;
    background: #f1f2f4;
    border: 1px solid #e4e6e9;
}

#email-marketing-coupons-root .coupon-standard-badge {
    color: #6c757d;
    background: #f8f9fa;
    border: 1px solid #ebedf2;
    font-size: 10px;
}

#email-marketing-coupons-root .coupon-action-btn {
    min-height: 30px;
    padding: 6px 10px;
    font-size: 10.5px;
}

/* Empty State */

#email-marketing-coupons-root #emptyCouponsRow td {
    padding: 45px 15px;
    color: #8d9498;
    white-space: normal;
}

#email-marketing-coupons-root #emptyCouponsRow i {
    color: #c5c9d0;
}

/* =========================================================
   PAGINATION
========================================================= */

#email-marketing-coupons-root .card-footer {
    padding: 12px 15px;
    background: #fff;
    border-top: 1px solid #ebecec;
}

#email-marketing-coupons-root .card-footer nav {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    width: auto;
    margin: 0;
}

#email-marketing-coupons-root .card-footer nav > div.d-sm-none,
#email-marketing-coupons-root .card-footer nav > .d-sm-none {
    display: none !important;
}

#email-marketing-coupons-root .card-footer nav > div.d-none,
#email-marketing-coupons-root .card-footer nav > .d-none.flex-sm-fill {
    display: flex !important;
    align-items: center;
    justify-content: center;
}

#email-marketing-coupons-root .card-footer nav p,
#email-marketing-coupons-root .card-footer nav .small.text-muted,
#email-marketing-coupons-root .card-footer nav div:has(> p),
#email-marketing-coupons-root .card-footer nav > div.d-none > div:first-child,
#email-marketing-coupons-root .card-footer nav .d-none.flex-sm-fill > div:first-child {
    display: none !important;
}

#email-marketing-coupons-root .card-footer .pagination {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 0;
    margin: 0;
    list-style: none;
}

#email-marketing-coupons-root .card-footer .page-item {
    display: inline-flex;
}

#email-marketing-coupons-root .card-footer .page-item .page-link,
#email-marketing-coupons-root .card-footer .page-item span {
    min-width: 29px;
    height: 29px;
    padding: 0 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    background: #fff;
    border: 1px solid #ebecec;
    border-radius: 5px;
    font-size: 11px;
    font-weight: 600;
    line-height: 1;
    text-decoration: none;
    transition: all .15s ease;
}

#email-marketing-coupons-root .card-footer .page-item .page-link:hover {
    color: var(--coupon-primary);
    background: #f4f8ff;
    border-color: rgba(21, 114, 232, .3);
}

#email-marketing-coupons-root .card-footer .page-item.active .page-link,
#email-marketing-coupons-root .card-footer .page-item.active span {
    color: #fff;
    background: var(--coupon-primary);
    border-color: var(--coupon-primary);
}

#email-marketing-coupons-root .card-footer .page-item.disabled .page-link,
#email-marketing-coupons-root .card-footer .page-item.disabled span {
    opacity: .45;
    background: #f8f9fa;
    cursor: not-allowed;
}

#email-marketing-coupons-root .card-footer nav svg {
    width: 12px;
    height: 12px;
}

/* =========================================================
   COPY NOTIFICATION MODAL
========================================================= */

.coupon-modal-scope .modal-content {
    background: #fff;
    border: 1px solid #ebecec;
    border-radius: 8px;
}

.coupon-modal-scope .modal-body {
    padding: 14px 8px;
}

.coupon-modal-scope .copy-success-icon {
    width: 48px;
    height: 48px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #31a94a;
    background: #eaf8ed;
    border-radius: 50%;
    font-size: 21px;
}

.coupon-modal-scope .copy-modal-title {
    color: #1a2035;
    font-size: 15px;
    font-weight: 700;
}

.coupon-modal-scope .copy-modal-description {
    color: #8d9498;
    font-size: 11.5px;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

/* =========================================================
   RESPONSIVE DESIGN
========================================================= */

/* Large Desktop */

@media (min-width: 1200px) {
    #email-marketing-coupons-root .metric-card {
        min-height: 110px;
        padding: 14px 16px;
    }

    #email-marketing-coupons-root .custom-coupons-table {
        min-width: 0;
    }

    #email-marketing-coupons-root .custom-coupons-table thead th,
    #email-marketing-coupons-root .custom-coupons-table tbody td {
        padding-left: 9px;
        padding-right: 9px;
    }
}

/* Tablet & Smaller Desktop */

@media (max-width: 991.98px) {
    #email-marketing-coupons-root .custom-coupons-table {
        min-width: 690px;
    }

    #email-marketing-coupons-root .metric-card {
        min-height: 100px;
    }
}

/* Mobile Landscape & Portrait */

@media (max-width: 767.98px) {
    #email-marketing-coupons-root {
        padding-left: 10px !important;
        padding-right: 10px !important;
    }

    #email-marketing-coupons-root .coupons-page-header {
        gap: 12px;
        margin-bottom: 15px;
    }

    #email-marketing-coupons-root .coupons-page-title {
        font-size: 17px;
    }

    #email-marketing-coupons-root .coupons-page-description {
        font-size: 11px;
    }

    #email-marketing-coupons-root .top-action-btns {
        width: 100%;
    }

    #email-marketing-coupons-root .top-action-btns .btn {
        width: 100%;
        min-height: 38px;
        white-space: normal;
        text-align: center;
    }

    #email-marketing-coupons-root .metric-card {
        min-height: 91px;
        padding: 10px;
    }

    #email-marketing-coupons-root .stat-label-title {
        font-size: 10px;
    }

    #email-marketing-coupons-root .stat-val-num {
        font-size: 18px;
    }

    #email-marketing-coupons-root .stat-sub-text {
        font-size: 9px;
    }

    #email-marketing-coupons-root .metric-icon-square {
        width: 29px;
        height: 29px;
        font-size: 12px;
    }

    #email-marketing-coupons-root .section-header-wrap {
        padding: 10px 12px;
    }

    #email-marketing-coupons-root .section-title {
        font-size: 11px;
    }

    #email-marketing-coupons-root .filter-reset-btn {
        min-height: 36px;
    }

    #email-marketing-coupons-root .pc-fit-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #email-marketing-coupons-root .custom-coupons-table {
        min-width: 690px;
    }

    #email-marketing-coupons-root .card-footer {
        padding: 12px 10px;
    }

    #email-marketing-coupons-root .card-footer nav {
        justify-content: center;
        max-width: 100%;
    }

    #email-marketing-coupons-root .card-footer .pagination {
        gap: 3px;
    }

    #email-marketing-coupons-root .card-footer .page-item .page-link,
    #email-marketing-coupons-root .card-footer .page-item span {
        min-width: 27px;
        height: 27px;
        padding: 0 6px;
    }
}

/* Small Mobile */

@media (max-width: 380px) {
    #email-marketing-coupons-root .coupons-page-title {
        font-size: 15px;
    }

    #email-marketing-coupons-root .metric-card {
        min-height: 85px;
        padding: 8px;
    }

    #email-marketing-coupons-root .stat-label-title {
        font-size: 9px;
    }

    #email-marketing-coupons-root .stat-val-num {
        font-size: 16px;
    }

    #email-marketing-coupons-root .metric-icon-square {
        width: 25px;
        height: 25px;
        font-size: 10px;
    }

    #email-marketing-coupons-root .section-title {
        font-size: 10px;
    }
}

/* Reduced Motion */

@media (prefers-reduced-motion: reduce) {
    #email-marketing-coupons-root *,
    .coupon-modal-scope * {
        transition-duration: .01ms !important;
        animation-duration: .01ms !important;
    }
}
</style>


<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-coupons-root">

    {{-- 1. Page Header --}}

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 coupons-page-header">

        <div class="d-flex align-items-center gap-2">
            <div class="coupons-header-icon">
                <i class="fas fa-tags"></i>
            </div>

            <div>
                <h5 class="coupons-page-title mb-1">
                    Marketing Promo Coupons
                </h5>

                <p class="coupons-page-description">
                    Manage store vouchers for email campaigns and automated cart recovery.
                </p>
            </div>
        </div>

        <div class="d-flex gap-2 top-action-btns">
            <a href="{{ route('admin.email_marketing.campaigns.create') }}" class="btn btn-sm btn-primary btn-compact shadow-sm">
                <i class="fas fa-plus-circle"></i>
                <span>Create Campaign With Coupon</span>
            </a>
        </div>

    </div>


    {{-- 2. Feedback Messages --}}

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3 d-flex align-items-center justify-content-between gap-2" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>

            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3 d-flex align-items-center justify-content-between gap-2" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>

            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif


    {{-- 3. Coupon Statistics --}}

    <div class="card mb-3">

        <div class="section-header-wrap d-flex justify-content-between align-items-center">

            <h6 class="section-title">
                <i class="fas fa-chart-pie me-1"></i>
                Coupon Overview
            </h6>

            <button type="button" class="collapse-toggle-btn no-print" data-bs-toggle="collapse" data-bs-target="#collapseCouponsKpis" aria-expanded="true" aria-controls="collapseCouponsKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>

        </div>

        <div class="collapse show" id="collapseCouponsKpis">

            <div class="p-3">

                <div class="row g-2">

                    {{-- Total Vouchers --}}

                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber h-100 mb-0">

                            <div class="d-flex align-items-center justify-content-between gap-1 mb-2">
                                <span class="stat-label-title text-truncate">Total Vouchers</span>

                                <div class="metric-icon-square">
                                    <i class="fas fa-ticket"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="stat-val-num my-0 font-monospace bn-digit">
                                    {{ number_format($totalCouponsCount) }}
                                </h4>

                                <small class="stat-sub-text d-block text-truncate">
                                    Total store coupons
                                </small>
                            </div>

                        </div>
                    </div>


                    {{-- Active Vouchers --}}

                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">

                            <div class="d-flex align-items-center justify-content-between gap-1 mb-2">
                                <span class="stat-label-title text-truncate">Active Vouchers</span>

                                <div class="metric-icon-square">
                                    <i class="fas fa-circle-check"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="stat-val-num my-0 font-monospace bn-digit text-success">
                                    {{ number_format($activeCouponsCount) }}
                                </h4>

                                <small class="stat-sub-text d-block text-truncate">
                                    Ready for campaigns
                                </small>
                            </div>

                        </div>
                    </div>


                    {{-- Inactive Vouchers --}}

                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">

                            <div class="d-flex align-items-center justify-content-between gap-1 mb-2">
                                <span class="stat-label-title text-truncate">Inactive Vouchers</span>

                                <div class="metric-icon-square">
                                    <i class="fas fa-pause"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="stat-val-num my-0 font-monospace bn-digit text-primary">
                                    {{ number_format($inactiveCouponsCount) }}
                                </h4>

                                <small class="stat-sub-text d-block text-truncate">
                                    Disabled vouchers
                                </small>
                            </div>

                        </div>
                    </div>


                    {{-- Automation Readiness --}}

                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-purple h-100 mb-0">

                            <div class="d-flex align-items-center justify-content-between gap-1 mb-2">
                                <span class="stat-label-title text-truncate">Automation Ready</span>

                                <div class="metric-icon-square">
                                    <i class="fas fa-bolt"></i>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <h4 class="stat-val-num my-0 font-monospace bn-digit">
                                    100%
                                </h4>

                                <small class="stat-sub-text d-block text-truncate">
                                    Token {coupon_code} ready
                                </small>
                            </div>

                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- 4. Search & Filters --}}

    <div class="card mb-3">

        <div class="section-header-wrap d-flex justify-content-between align-items-center">

            <h6 class="section-title">
                <i class="fas fa-filter me-1"></i>
                Search &amp; Filter Coupons
            </h6>

            <button type="button" class="collapse-toggle-btn no-print" data-bs-toggle="collapse" data-bs-target="#collapseCouponsFilter" aria-expanded="true" aria-controls="collapseCouponsFilter" title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>

        </div>

        <div class="collapse show" id="collapseCouponsFilter">

            <div class="p-3">

                <div class="row g-3 align-items-end">

                    <div class="col-12 col-md-5">

                        <label class="form-label" for="couponSearchInput">
                            <i class="fas fa-search me-1 text-primary"></i>
                            Search Coupon
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>

                            <input type="text" id="couponSearchInput" class="form-control border-start-0 ps-0 shadow-none" placeholder="Search code or discount..." onkeyup="filterCouponsTable()">

                        </div>

                    </div>


                    <div class="col-6 col-md-4">

                        <label class="form-label" for="couponStatusFilter">
                            <i class="fas fa-toggle-on me-1 text-success"></i>
                            Coupon Status
                        </label>

                        <select id="couponStatusFilter" class="form-select shadow-none font-monospace" onchange="filterCouponsTable()">

                            <option value="all" selected>All Statuses</option>
                            <option value="active">Active Only</option>
                            <option value="inactive">Inactive Only</option>

                        </select>

                    </div>


                    <div class="col-6 col-md-3">

                        <button type="button" class="btn btn-light border btn-compact filter-reset-btn w-100 shadow-none" onclick="resetCouponFilters()">
                            <i class="fas fa-rotate-left"></i>
                            <span>Reset Filters</span>
                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- 5. Coupons Table --}}

    <div class="card coupons-table-card mb-3">

        {{-- Table Header --}}

        <div class="p-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 bg-white">

            <div>

                <h6 class="table-heading mb-1 d-flex align-items-center gap-2">
                    <i class="fas fa-ticket-simple text-warning"></i>
                    Available Store Coupons
                </h6>

                <small class="table-subtitle font-monospace bn-digit">
                    Showing {{ $coupons->firstItem() ?? 0 }} to {{ $coupons->lastItem() ?? 0 }} of {{$coupons->total() }} coupons
                </small>

            </div>

            <div class="d-flex align-items-center gap-2">

                <span class="badge bg-light text-secondary border font-monospace px-2 py-2" style="font-size: 10px;" id="visible-coupons-count">
                    {{ $coupons->total() }} Total
                </span>

                <button type="button" class="collapse-toggle-btn no-print" data-bs-toggle="collapse" data-bs-target="#collapseCouponsTable" aria-expanded="true" aria-controls="collapseCouponsTable" title="Toggle Table">
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
                            <th class="col-vn-code">Coupon Code</th>
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

                                {{-- Coupon ID --}}

                                <td class="ps-3 col-vn-id text-center font-monospace bn-digit text-muted" style="font-size: 11px;">
                                    {{ $coupon->id }}
                                </td>


                                {{-- Coupon Code --}}

                                <td class="col-vn-code">

                                    <div class="d-inline-flex align-items-center gap-2">

                                        <span class="coupon-code-badge font-monospace">
                                            {{ $coupon->code }}
                                        </span>

                                        <button type="button" class="btn btn-sm coupon-copy-btn shadow-none" onclick="copyCouponCode('{{ addslashes($coupon->code) }}')" title="Copy Coupon Code">
                                            <i class="fas fa-copy" style="font-size: 10px;"></i>
                                        </button>

                                    </div>

                                </td>


                                {{-- Discount Value --}}

                                <td class="col-vn-val font-monospace bn-digit">

                                    @if($discount)

                                        <span class="fw-bold text-success" style="font-size: 12px;">
                                            ৳ {{ number_format($discount, 2) }}
                                        </span>

                                    @else

                                        <span class="badge coupon-standard-badge font-monospace px-2 py-1">
                                            Standard Discount
                                        </span>

                                    @endif

                                </td>


                                {{-- Coupon Status --}}

                                <td class="col-vn-status">

                                    @if($isActive)

                                        <span class="coupon-status-badge coupon-status-active font-monospace">
                                            <i class="fas fa-circle-check me-1"></i>
                                            ACTIVE
                                        </span>

                                    @else

                                        <span class="coupon-status-badge coupon-status-inactive font-monospace">
                                            INACTIVE
                                        </span>

                                    @endif

                                </td>


                                {{-- Marketing Action --}}

                                <td class="text-end pe-3 col-vn-action">

                                    <form action="{{ route('admin.email_marketing.coupons.create_campaign') }}" method="POST" class="d-inline">

                                        @csrf

                                        <input type="hidden" name="coupon_id" value="{{ $coupon->id }}">

                                        <button type="submit" class="btn btn-sm btn-outline-primary btn-compact coupon-action-btn shadow-none bg-white" title="Attach to Campaign">
                                            <i class="fas fa-paper-plane"></i>
                                            <span>Attach To Campaign</span>
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr id="emptyCouponsRow">

                                <td colspan="5" class="text-center py-5 text-muted small">

                                    <i class="fas fa-ticket fa-2x mb-2 d-block opacity-25"></i>

                                    <span class="fw-semibold">
                                        No promotional coupons found.
                                    </span>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Pagination --}}

            @if($coupons->hasPages())

                <div class="p-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-white card-footer no-print">

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


<!-- Modal: Copy Notification -->

<div class="modal fade coupon-modal-scope" id="couponCopyNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content border-0 shadow-lg text-center p-3">

            <div class="modal-body p-1">

                <div class="copy-success-icon mx-auto mb-3">
                    <i class="fas fa-circle-check"></i>
                </div>

                <h6 class="copy-modal-title mb-2">
                    Coupon Copied!
                </h6>

                <p class="copy-modal-description mb-3">
                    Code <strong id="copiedVoucherCode" class="text-dark font-monospace">CODE</strong> has been copied. Paste it into your email subject or template.
                </p>

                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">
                    OK
                </button>

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
