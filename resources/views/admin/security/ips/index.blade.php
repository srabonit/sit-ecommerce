@extends('layouts.admin')

@section('title', 'IP Directory - ' . config('app.name'))

@php
    $totalIps = $ips->total();
    $pageItems = collect($ips->items());
    $highRiskCount = $pageItems->filter(fn($i) => (int)($i->risk_score ?? $i->max_risk ?? 0) >= 75)->count();
    $blockedCount = $pageItems->filter(fn($i) => !empty($i->is_blocked))->count();
    $failedAuthCount = $pageItems->sum(fn($i) => (int)($i->failed_logins_count ?? $i->failed_logins ?? 0));
    $activeOrdersCount = $pageItems->sum(fn($i) => (int)($i->orders_count ?? 0));
@endphp

@section('admin_content')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
/* =========================================================
   IP DIRECTORY — KAIADMIN LITE CLASSIC / WHITE DESIGN
   Scoped styling — Bootstrap 5 compatible
========================================================= */

#security-ips-wrapper,
.sec-modal-scope {
    --sec-primary: var(--bs-primary, #1572e8);
    --sec-primary-hover: #1262c4;
    --sec-secondary: #686c78;
    --sec-success: #31ce36;
    --sec-danger: #f25961;
    --sec-warning: #ffad46;
    --sec-info: #48abf7;

    --sec-body-bg: #f5f7fd;
    --sec-card-bg: #ffffff;
    --sec-sidebar-bg: #ffffff;
    --sec-header-bg: #ffffff;

    --sec-heading: #2a2f5b;
    --sec-text: #575962;
    --sec-muted: #8d9498;
    --sec-border: #ebedf2;

    --sec-radius: 6px;
    --sec-shadow: 0 1px 3px rgba(0, 0, 0, .035);

    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    color: var(--sec-text);
    letter-spacing: 0;
    font-size: 13px;
}

#security-ips-wrapper {
    width: 100%;
    min-height: 100vh;
    background: var(--sec-body-bg);
    padding-bottom: 24px;
}

#security-ips-wrapper *,
.sec-modal-scope * {
    box-sizing: border-box;
}

/* Font Awesome Protection */
#security-ips-wrapper i,
#security-ips-wrapper .fa,
#security-ips-wrapper .fas,
#security-ips-wrapper .far,
#security-ips-wrapper .fab,
#security-ips-wrapper .fa-solid,
#security-ips-wrapper .fa-regular,
.sec-modal-scope i,
.sec-modal-scope .fa,
.sec-modal-scope .fas,
.sec-modal-scope .far {
    font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome" !important;
    font-style: normal;
    letter-spacing: normal;
    line-height: 1;
    display: inline-block;
}

#security-ips-wrapper .far,
.sec-modal-scope .far {
    font-weight: 400 !important;
}

/* Layout */
#security-ips-wrapper > .mx-auto {
    width: 100%;
    max-width: 1600px !important;
}

#security-ips-wrapper .min-w-0 {
    min-width: 0;
}

/* Cards */
#security-ips-wrapper .sec-card {
    background: var(--sec-card-bg);
    border: 1px solid var(--sec-border);
    border-radius: var(--sec-radius) !important;
    box-shadow: var(--sec-shadow);
    transition: border-color .2s ease, box-shadow .2s ease;
}

#security-ips-wrapper .sec-card:hover {
    border-color: #dfe3ec;
    box-shadow: 0 2px 6px rgba(0, 0, 0, .045);
}

/* Page Header */
#security-ips-wrapper .sec-page-title {
    margin: 0;
    color: var(--sec-heading);
    font-size: clamp(16px, 1.4vw, 19px);
    font-weight: 700;
    line-height: 1.35;
    letter-spacing: -.35px;
}

#security-ips-wrapper .sec-page-subtitle {
    color: var(--sec-muted);
    font-size: 12px;
    line-height: 1.5;
    margin-top: 3px;
}

#security-ips-wrapper .sec-header-icon-box {
    width: 42px !important;
    height: 42px !important;
    min-width: 42px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex-shrink: 0 !important;
    border: 1px solid rgba(21, 114, 232, .15) !important;
    background: rgba(21, 114, 232, .08) !important;
    color: var(--sec-primary) !important;
    border-radius: 6px !important;
}

#security-ips-wrapper .sec-header-icon-box i {
    font-size: 17px;
}

/* Protected Badge */
#security-ips-wrapper .badge-status-protected {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(49, 206, 54, .10) !important;
    color: #20a52a !important;
    border: 1px solid rgba(49, 206, 54, .22) !important;
    padding: 4px 9px !important;
    font-size: 10px !important;
    font-weight: 700 !important;
    border-radius: 20px !important;
}

/* Summary Cards */
#security-ips-wrapper .metric-card {
    height: 100%;
    min-height: 105px;
    padding: 15px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    background: var(--sec-card-bg);
    border: 1px solid var(--sec-border);
    border-radius: var(--sec-radius) !important;
    box-shadow: var(--sec-shadow);
    transition: box-shadow .2s ease, transform .2s ease;
    overflow: hidden;
}

#security-ips-wrapper .metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, .055);
}

#security-ips-wrapper .stat-label-title {
    color: var(--sec-muted);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .35px;
    line-height: 1.4;
}

#security-ips-wrapper .stat-val-num {
    margin: 4px 0 0;
    color: var(--sec-heading);
    font-size: clamp(19px, 2vw, 25px);
    font-weight: 700;
    line-height: 1.3;
    letter-spacing: -.5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

#security-ips-wrapper .stat-sub-text {
    margin-top: 4px;
    font-size: 11px;
    font-weight: 500;
}

#security-ips-wrapper .metric-icon-square {
    width: 36px;
    height: 36px;
    min-width: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px !important;
    font-size: 15px;
}

#security-ips-wrapper .icon-box-blue {
    background: rgba(21, 114, 232, .09) !important;
    border: 1px solid rgba(21, 114, 232, .14) !important;
    color: var(--sec-primary) !important;
}

#security-ips-wrapper .icon-box-teal {
    background: rgba(72, 171, 247, .10) !important;
    border: 1px solid rgba(72, 171, 247, .17) !important;
    color: #3298df !important;
}

#security-ips-wrapper .icon-box-green {
    background: rgba(49, 206, 54, .09) !important;
    border: 1px solid rgba(49, 206, 54, .16) !important;
    color: #27b52e !important;
}

#security-ips-wrapper .icon-box-red {
    background: rgba(242, 89, 97, .09) !important;
    border: 1px solid rgba(242, 89, 97, .16) !important;
    color: var(--sec-danger) !important;
}

/* Quick Stats */
#security-ips-wrapper .sub-metric-pill-card {
    min-height: 48px;
    padding: 10px 13px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    background: var(--sec-card-bg);
    border: 1px solid var(--sec-border);
    border-radius: var(--sec-radius) !important;
    box-shadow: var(--sec-shadow);
}

#security-ips-wrapper .sub-metric-label {
    color: var(--sec-muted);
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
}

#security-ips-wrapper .sub-metric-value {
    font-size: 15px;
    font-weight: 700;
    line-height: 1.2;
}

/* Numeric Data */
#security-ips-wrapper .bn-digit,
#security-ips-wrapper .font-tabular {
    font-family: 'Inter', sans-serif !important;
    font-variant-numeric: tabular-nums;
    font-feature-settings: "tnum";
}

/* Form Controls */
#security-ips-wrapper .form-control,
#security-ips-wrapper .form-select,
.sec-modal-scope .form-control,
.sec-modal-scope .form-select {
    min-height: 36px;
    padding: 7px 11px;
    border: 1px solid #e3e6ed;
    border-radius: 5px !important;
    background-color: #ffffff;
    color: #575962;
    font-family: inherit;
    font-size: 12px;
    font-weight: 500;
    line-height: 1.5;
    box-shadow: none;
    transition: border-color .2s ease, box-shadow .2s ease;
}

#security-ips-wrapper .form-control::placeholder,
.sec-modal-scope .form-control::placeholder {
    color: #b5bac5;
    opacity: 1;
}

#security-ips-wrapper .form-control:focus,
#security-ips-wrapper .form-select:focus,
.sec-modal-scope .form-control:focus,
.sec-modal-scope .form-select:focus {
    border-color: var(--sec-primary);
    outline: 0;
    box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
}

#security-ips-wrapper .form-label,
.sec-modal-scope .form-label {
    display: block;
    margin-bottom: 6px;
    color: #575962;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.4;
}

#security-ips-wrapper .input-group-text {
    border-color: #e3e6ed;
    background: #f8f9fc !important;
    color: #8d9498 !important;
    border-radius: 5px 0 0 5px;
}

#security-ips-wrapper .input-group > .form-control {
    min-width: 0;
}

/* Buttons */
#security-ips-wrapper .btn-compact {
    min-height: 35px;
    padding: 7px 13px !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    border-radius: 5px !important;
    font-family: inherit;
    font-size: 11.5px !important;
    font-weight: 600 !important;
    line-height: 1.3 !important;
    white-space: nowrap;
    transition: all .2s ease;
}

#security-ips-wrapper .btn-primary,
.sec-modal-scope .btn-primary {
    background-color: var(--sec-primary) !important;
    border-color: var(--sec-primary) !important;
    color: #ffffff !important;
}

#security-ips-wrapper .btn-primary:hover,
.sec-modal-scope .btn-primary:hover {
    background-color: var(--sec-primary-hover) !important;
    border-color: var(--sec-primary-hover) !important;
}

#security-ips-wrapper .btn-outline-secondary,
.sec-modal-scope .btn-outline-secondary {
    background: #ffffff !important;
    border-color: #e0e4ec !important;
    color: #575962 !important;
}

#security-ips-wrapper .btn-outline-secondary:hover,
.sec-modal-scope .btn-outline-secondary:hover {
    background: #f5f7fd !important;
    border-color: #cbd2df !important;
    color: var(--sec-heading) !important;
}

#security-ips-wrapper .btn-danger,
.sec-modal-scope .btn-danger {
    background-color: var(--sec-danger);
    border-color: var(--sec-danger);
}

/* Action Buttons */
#security-ips-wrapper .sec-action-btn {
    width: 32px !important;
    height: 32px !important;
    min-width: 32px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
    border-radius: 5px !important;
    font-size: 12px !important;
    cursor: pointer;
    transition: all .2s ease;
}

#security-ips-wrapper .sec-action-btn-view {
    background: rgba(21, 114, 232, .08) !important;
    border: 1px solid rgba(21, 114, 232, .18) !important;
    color: var(--sec-primary) !important;
}

#security-ips-wrapper .sec-action-btn-view:hover {
    background: var(--sec-primary) !important;
    border-color: var(--sec-primary) !important;
    color: #ffffff !important;
}

#security-ips-wrapper .sec-action-btn-block {
    background: rgba(242, 89, 97, .08) !important;
    border: 1px solid rgba(242, 89, 97, .18) !important;
    color: var(--sec-danger) !important;
}

#security-ips-wrapper .sec-action-btn-block:hover {
    background: var(--sec-danger) !important;
    border-color: var(--sec-danger) !important;
    color: #ffffff !important;
}

#security-ips-wrapper .sec-action-btn-unblock {
    background: rgba(49, 206, 54, .08) !important;
    border: 1px solid rgba(49, 206, 54, .18) !important;
    color: #25ad2d !important;
}

#security-ips-wrapper .sec-action-btn-unblock:hover {
    background: var(--sec-success) !important;
    border-color: var(--sec-success) !important;
    color: #ffffff !important;
}

/* IP Address */
#security-ips-wrapper .ip-link-text {
    color: var(--sec-heading);
    font-size: 12px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    text-decoration: none;
    overflow-wrap: anywhere;
}

#security-ips-wrapper .ip-link-text:hover {
    color: var(--sec-primary) !important;
}

#security-ips-wrapper .protocol-tag {
    display: inline-flex;
    align-items: center;
    padding: 3px 6px;
    border-radius: 4px;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: .2px;
    line-height: 1.2;
}

#security-ips-wrapper .tag-v4 {
    background: #f1f3f8;
    color: #686c78;
    border: 1px solid #e4e7ed;
}

#security-ips-wrapper .tag-v6 {
    background: rgba(21, 114, 232, .08);
    color: var(--sec-primary);
    border: 1px solid rgba(21, 114, 232, .16);
}

#security-ips-wrapper .badge-blocked-pill {
    display: inline-flex;
    align-items: center;
    padding: 3px 7px;
    background: rgba(242, 89, 97, .12);
    color: #dc3545;
    border: 1px solid rgba(242, 89, 97, .15);
    border-radius: 4px;
    font-size: 9px;
    font-weight: 700;
    line-height: 1.2;
}

/* Data Table */
#security-ips-wrapper .sec-table-container {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
}

#security-ips-wrapper .sec-table {
    width: 100%;
    min-width: 1190px;
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
}

#security-ips-wrapper .sec-table thead th {
    padding: 12px 10px !important;
    background: #f8f9fc !important;
    color: #686c78 !important;
    border-top: 0 !important;
    border-bottom: 1px solid #ebedf2 !important;
    font-size: 10px !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: .35px !important;
    line-height: 1.4;
    white-space: nowrap !important;
    vertical-align: middle;
}

#security-ips-wrapper .sec-table tbody td {
    padding: 11px 10px !important;
    background: #ffffff;
    color: #575962;
    border-bottom: 1px solid #f0f1f5 !important;
    font-size: 11.5px !important;
    font-weight: 500;
    line-height: 1.5;
    vertical-align: middle !important;
    white-space: nowrap !important;
}

#security-ips-wrapper .sec-table tbody tr:last-child td {
    border-bottom: 0 !important;
}

#security-ips-wrapper .sec-table tbody tr:hover td {
    background-color: #fafbfe;
}

#security-ips-wrapper .sec-table tbody tr.table-danger td {
    background-color: rgba(242, 89, 97, .045) !important;
}

#security-ips-wrapper .sec-table tbody tr.table-danger:hover td {
    background-color: rgba(242, 89, 97, .075) !important;
}

/* Column Widths — All 10 Columns Preserved */
#security-ips-wrapper .col-ip       { width: 22%; min-width: 175px; }
#security-ips-wrapper .col-country  { width: 12%; min-width: 120px; }
#security-ips-wrapper .col-device   { width: 14%; min-width: 135px; }
#security-ips-wrapper .col-hits     { width: 6%; min-width: 65px; }
#security-ips-wrapper .col-orders   { width: 6%; min-width: 65px; }
#security-ips-wrapper .col-forms    { width: 6%; min-width: 65px; }
#security-ips-wrapper .col-fails    { width: 7%; min-width: 80px; }
#security-ips-wrapper .col-risk     { width: 7%; min-width: 75px; }
#security-ips-wrapper .col-activity { width: 10%; min-width: 110px; }
#security-ips-wrapper .col-actions  { width: 8%; min-width: 100px; }

/* Table Country Badge */
#security-ips-wrapper .sec-table .badge.bg-light {
    background: #f5f6fa !important;
    color: #575962 !important;
    border-color: #e8eaf0 !important;
    font-weight: 600 !important;
}

/* Pagination */
#security-ips-wrapper .card-footer {
    background: #ffffff !important;
    border-top: 1px solid var(--sec-border) !important;
}

#security-ips-wrapper #ip-pagination-container {
    max-width: 100%;
    overflow-x: auto;
}

#security-ips-wrapper #ip-pagination-container .pagination {
    margin-bottom: 0;
    gap: 3px;
    flex-wrap: wrap;
}

#security-ips-wrapper #ip-pagination-container .page-link {
    min-width: 30px;
    min-height: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 9px;
    border: 1px solid #ebedf2;
    border-radius: 4px !important;
    color: var(--sec-primary);
    font-size: 11px;
    box-shadow: none;
}

#security-ips-wrapper #ip-pagination-container .page-item.active .page-link {
    background: var(--sec-primary);
    border-color: var(--sec-primary);
    color: #ffffff;
}

#security-ips-wrapper #ip-pagination-container .page-item.disabled .page-link {
    color: #b5bac5;
    background: #f8f9fc;
}

/* Mobile Search and Filters */
#security-ips-wrapper #mobile_filter_search {
    min-width: 0;
}

#security-ips-wrapper #mobileActiveFilterBadge {
    font-size: 9px !important;
}

/* Modal */
.sec-modal-scope .modal-content {
    background: #ffffff;
    border: 1px solid #ebedf2 !important;
    border-radius: 7px !important;
    box-shadow: 0 8px 30px rgba(0, 0, 0, .12);
    overflow: hidden;
}

.sec-modal-scope .modal-header {
    padding: 14px 17px !important;
    background: #ffffff !important;
    border-bottom: 1px solid #ebedf2 !important;
}

.sec-modal-scope .modal-title {
    color: var(--sec-heading) !important;
    font-size: 14px !important;
    font-weight: 700 !important;
}

.sec-modal-scope .modal-body {
    padding: 18px !important;
    background: #ffffff;
}

.sec-modal-scope .modal-footer {
    padding: 12px 17px !important;
    background: #f8f9fc !important;
    border-top: 1px solid #ebedf2 !important;
}

.sec-modal-scope .btn-close {
    opacity: .6;
    box-shadow: none !important;
}

.sec-modal-scope .btn-close:hover {
    opacity: 1;
}

.sec-modal-scope .form-control,
.sec-modal-scope .form-select {
    min-height: 37px;
}

.sec-modal-scope textarea.form-control {
    min-height: auto;
    resize: vertical;
}

.sec-modal-scope .btn-compact {
    min-height: 35px;
    padding: 7px 13px;
    font-size: 11.5px;
    font-weight: 600;
    border-radius: 5px !important;
}

/* Confirmation / Notice Modal */
.sec-modal-scope .minimal-modal-card {
    padding: 24px !important;
    background: #ffffff !important;
    border: 1px solid #ebedf2 !important;
    border-radius: 8px !important;
    box-shadow: 0 12px 35px rgba(0, 0, 0, .12) !important;
    text-align: left !important;
}

.sec-modal-scope .minimal-modal-title {
    margin: 0 0 8px !important;
    color: var(--sec-heading) !important;
    font-size: 16px !important;
    font-weight: 700 !important;
    line-height: 1.4;
    letter-spacing: -.2px !important;
}

.sec-modal-scope .minimal-modal-subtext {
    margin-bottom: 20px !important;
    color: #8d9498 !important;
    font-size: 12px !important;
    font-weight: 400;
    line-height: 1.65 !important;
}

.sec-modal-scope .btn-minimal-action {
    min-height: 35px;
    padding: 8px 17px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border: 1px solid var(--sec-danger) !important;
    border-radius: 5px !important;
    background: var(--sec-danger) !important;
    color: #ffffff !important;
    font-family: inherit;
    font-size: 12px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    box-shadow: none !important;
    transition: all .2s ease;
}

.sec-modal-scope .btn-minimal-action:hover {
    filter: brightness(.93);
    color: #ffffff !important;
}

.sec-modal-scope .btn-minimal-cancel {
    min-height: 35px;
    padding: 7px 14px !important;
    border: 1px solid transparent !important;
    border-radius: 5px !important;
    background: transparent !important;
    color: #686c78 !important;
    font-family: inherit;
    font-size: 12px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    box-shadow: none !important;
}

.sec-modal-scope .btn-minimal-cancel:hover {
    background: #f5f6fa !important;
    color: var(--sec-heading) !important;
}

/* Scrollbar */
#security-ips-wrapper .sec-table-container::-webkit-scrollbar,
#security-ips-wrapper #ip-pagination-container::-webkit-scrollbar {
    height: 6px;
}

#security-ips-wrapper .sec-table-container::-webkit-scrollbar-track {
    background: #f5f6fa;
}

#security-ips-wrapper .sec-table-container::-webkit-scrollbar-thumb {
    background: #cbd2df;
    border-radius: 10px;
}

/* Bootstrap Utilities Compatibility */
#security-ips-wrapper .shadow-xs {
    box-shadow: 0 1px 3px rgba(0, 0, 0, .045) !important;
}

#security-ips-wrapper .gap-2\.5 {
    gap: .625rem !important;
}

#security-ips-wrapper .gap-1\.5 {
    gap: .375rem !important;
}

#security-ips-wrapper .p-2\.5 {
    padding: .625rem !important;
}

#security-ips-wrapper .mb-2\.5 {
    margin-bottom: .625rem !important;
}

#security-ips-wrapper .py-2\.5 {
    padding-top: .625rem !important;
    padding-bottom: .625rem !important;
}

#security-ips-wrapper .px-1\.5 {
    padding-left: .375rem !important;
    padding-right: .375rem !important;
}

#security-ips-wrapper .py-0\.5 {
    padding-top: .125rem !important;
    padding-bottom: .125rem !important;
}

/* Tablet */
@media (max-width: 991.98px) {
    #security-ips-wrapper {
        padding-left: 0;
        padding-right: 0;
    }

    #security-ips-wrapper .metric-card {
        min-height: 100px;
    }

    #security-ips-wrapper .sec-table {
        min-width: 1190px;
    }
}

/* Mobile */
@media (max-width: 767.98px) {
    #security-ips-wrapper {
        min-height: auto;
        padding-bottom: 15px;
    }

    #security-ips-wrapper .header-top-wrap {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 13px !important;
    }

    #security-ips-wrapper .header-top-wrap > div:first-child {
        align-items: center;
    }

    #security-ips-wrapper .sec-page-subtitle {
        font-size: 11px;
    }

    #security-ips-wrapper .header-action-btns-wrap {
        width: 100%;
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px !important;
    }

    #security-ips-wrapper .header-action-btns-wrap .btn {
        width: 100%;
        min-height: 38px;
        margin: 0 !important;
        justify-content: center;
    }

    #security-ips-wrapper .metric-card {
        min-height: 90px;
        padding: 11px !important;
    }

    #security-ips-wrapper .stat-label-title {
        font-size: 9px;
        letter-spacing: .2px;
    }

    #security-ips-wrapper .stat-val-num {
        font-size: 19px !important;
    }

    #security-ips-wrapper .metric-icon-square {
        width: 30px;
        height: 30px;
        min-width: 30px;
        font-size: 12px;
    }

    #security-ips-wrapper .sub-metric-pill-card {
        min-height: 43px;
        padding: 8px 10px;
    }

    #security-ips-wrapper .sub-metric-label {
        font-size: 9px;
    }

    #security-ips-wrapper .sub-metric-value {
        font-size: 13px;
    }

    #security-ips-wrapper .sec-table {
        min-width: 1190px;
    }

    #security-ips-wrapper .sec-table thead th,
    #security-ips-wrapper .sec-table tbody td {
        padding: 9px 9px !important;
    }

    #security-ips-wrapper .sec-action-btn {
        width: 32px !important;
        height: 32px !important;
        min-width: 32px !important;
    }

    #security-ips-wrapper .card-footer {
        padding: 12px !important;
    }

    #security-ips-wrapper .card-footer > span {
        width: 100%;
        text-align: center;
    }

    #security-ips-wrapper #ip-pagination-container {
        width: 100%;
        display: flex;
        justify-content: center;
    }

    .sec-modal-scope .modal-dialog {
        width: calc(100% - 24px);
        max-width: 420px;
        margin: 12px auto;
    }

    .sec-modal-scope .minimal-modal-card {
        padding: 20px !important;
    }
}

/* Small Mobile */
@media (max-width: 374.98px) {
    #security-ips-wrapper {
        font-size: 12px;
    }

    #security-ips-wrapper .sec-page-title {
        font-size: 15px !important;
    }

    #security-ips-wrapper .sec-header-icon-box {
        width: 36px !important;
        height: 36px !important;
        min-width: 36px !important;
    }

    #security-ips-wrapper .metric-card {
        padding: 9px !important;
        min-height: 84px;
    }

    #security-ips-wrapper .stat-val-num {
        font-size: 17px !important;
    }

    #security-ips-wrapper .header-action-btns-wrap .btn {
        padding-left: 7px !important;
        padding-right: 7px !important;
        font-size: 10.5px !important;
    }

    #security-ips-wrapper .sub-metric-pill-card {
        padding: 7px 8px;
    }

    .sec-modal-scope .modal-body {
        padding: 14px !important;
    }
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
    #security-ips-wrapper *,
    .sec-modal-scope * {
        transition-duration: .01ms !important;
        animation-duration: .01ms !important;
    }
}
</style>


<div class="container-fluid px-2 px-sm-3 px-md-4 py-3" id="security-ips-wrapper">

    <div class="mx-auto" style="max-width: 1600px;">

        <!-- 1. PAGE HEADER -->
        <header class="sec-card p-3 mb-3">

            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 header-top-wrap">

                <div class="d-flex align-items-center gap-2">

                    <span class="sec-header-icon-box shadow-xs">
                        <i class="fas fa-network-wired"></i>
                    </span>

                    <div class="ms-2">
                        <h4 class="sec-page-title">IP Directory</h4>
                        <p class="sec-page-subtitle mb-0 d-none d-sm-block">
                            Monitor visitor IP activity and security risks.
                        </p>
                    </div>

                </div>

                <div class="header-action-btns-wrap d-flex align-items-center gap-2">

                    <a href="{{ route('admin.security.ips.export') }}"
                       id="btn-export-ips-csv"
                       class="btn btn-outline-secondary btn-compact shadow-xs bg-white text-dark fw-semibold"
                       title="Export CSV">

                        <i class="fas fa-file-csv text-success me-1"></i>
                        <span>Export CSV</span>

                    </a>

                    <button type="button"
                            class="btn ms-1 btn-primary btn-compact shadow-xs fw-bold px-3"
                            data-bs-toggle="modal"
                            data-bs-target="#modalManualBlockIp">

                        <i class="fas fa-plus me-1"></i>
                        <span>Block IP</span>

                    </button>

                </div>

            </div>

        </header>


        <!-- 2. SUMMARY CARDS -->
        <div class="row g-2 mb-3">

            <div class="col-6 col-xl-3">
                <div class="metric-card">

                    <div class="d-flex align-items-center justify-content-between mb-1">

                        <span class="stat-label-title text-truncate">Total IPs</span>

                        <div class="metric-icon-square icon-box-blue shadow-xs">
                            <i class="fas fa-network-wired"></i>
                        </div>

                    </div>

                    <div class="min-w-0">
                        <h4 class="stat-val-num bn-digit font-tabular my-0" id="ip-total-counter">
                            {{ number_format($totalIps) }}
                        </h4>
                    </div>

                </div>
            </div>


            <div class="col-6 col-xl-3">
                <div class="metric-card">

                    <div class="d-flex align-items-center justify-content-between mb-1">

                        <span class="stat-label-title">IP Records</span>

                        <div class="metric-icon-square icon-box-teal shadow-xs">
                            <i class="fas fa-globe"></i>
                        </div>

                    </div>

                    <div class="min-w-0">
                        <h4 class="stat-val-num bn-digit font-tabular my-0">
                            {{ number_format($totalIps) }}
                        </h4>
                    </div>

                </div>
            </div>


            <div class="col-6 col-xl-3">
                <div class="metric-card">

                    <div class="d-flex align-items-center justify-content-between mb-1">

                        <span class="stat-label-title">Orders</span>

                        <div class="metric-icon-square icon-box-green shadow-xs">
                            <i class="fas fa-bag-shopping"></i>
                        </div>

                    </div>

                    <div class="min-w-0">
                        <h4 class="stat-val-num text-success bn-digit font-tabular my-0">
                            {{ number_format($activeOrdersCount) }}
                        </h4>
                    </div>

                </div>
            </div>


            <div class="col-6 col-xl-3">
                <div class="metric-card">

                    <div class="d-flex align-items-center justify-content-between mb-1">

                        <span class="stat-label-title">High-Risk IPs</span>

                        <div class="metric-icon-square icon-box-red shadow-xs">
                            <i class="fas fa-bell"></i>
                        </div>

                    </div>

                    <div class="min-w-0">
                        <h4 class="stat-val-num text-danger bn-digit font-tabular my-0">
                            {{ $highRiskCount }}
                        </h4>
                    </div>

                </div>
            </div>

        </div>


        <!-- 3. QUICK STATS -->
        <div class="row g-2 mb-3">

            <div class="col-6 col-md-3">
                <div class="sub-metric-pill-card">

                    <span class="sub-metric-label">Blocked</span>

                    <strong class="sub-metric-value text-danger bn-digit font-tabular">
                        {{ $blockedCount }}
                    </strong>

                </div>
            </div>


            <div class="col-6 col-md-3">
                <div class="sub-metric-pill-card">

                    <span class="sub-metric-label">High Risk</span>

                    <strong class="sub-metric-value text-warning bn-digit font-tabular">
                        {{ $highRiskCount }}
                    </strong>

                </div>
            </div>


            <div class="col-6 col-md-3">
                <div class="sub-metric-pill-card">

                    <span class="sub-metric-label">Failed Logins</span>

                    <strong class="sub-metric-value text-danger bn-digit font-tabular">
                        {{ $failedAuthCount }}
                    </strong>

                </div>
            </div>


            <div class="col-6 col-md-3">
                <div class="sub-metric-pill-card">

                    <span class="sub-metric-label">On This Page</span>

                    <strong class="sub-metric-value text-primary bn-digit font-tabular">
                        {{ $ips->count() }}
                    </strong>

                </div>
            </div>

        </div>


        <!-- 4. FILTERS -->

        <!-- Mobile Search -->
        <div class="sec-card p-2.5 mb-3 d-md-none">

            <div class="d-flex align-items-center gap-2">

                <div class="input-group input-group-sm flex-grow-1">

                    <span class="input-group-text bg-light text-muted border-end-0">
                        <i class="fas fa-search" style="font-size: 10px;"></i>
                    </span>

                    <input type="text"
                           class="form-control border-start-0"
                           id="mobile_filter_search"
                           placeholder="Search IP / Country..."
                           value="{{ request('search') }}"
                           oninput="syncMobileSearch(this.value)">

                </div>

                <button type="button"
                        class="btn me-1 btn-outline-secondary btn-compact shadow-xs bg-white text-dark fw-semibold flex-shrink-0"
                        data-bs-toggle="modal"
                        data-bs-target="#modalMobileFilters">

                    <i class="fas fa-sliders me-1 text-primary"></i>

                    <span>Filters</span>

                    <span class="badge bg-primary text-white rounded-pill px-1.5 py-0.5 ms-1 d-none"
                          id="mobileActiveFilterBadge"
                          style="font-size: 8.5px;">0</span>

                </button>

            </div>

        </div>


        <!-- Desktop Filters -->
        <section class="sec-card p-3 mb-3 d-none d-md-block">

            <form id="ipFilterForm"
                  method="GET"
                  action="{{ route('admin.security.ips.index') }}"
                  class="row g-3 align-items-end">

                <div class="col-md-3">

                    <label class="form-label small fw-bold text-secondary mb-1" style="font-size: 11px;">
                        Search IP / Country
                    </label>

                    <div class="input-group input-group-sm">

                        <span class="input-group-text bg-light text-muted border-end-0">
                            <i class="fas fa-search" style="font-size: 10px;"></i>
                        </span>

                        <input type="text"
                               class="form-control border-start-0"
                               id="filter_search"
                               name="search"
                               placeholder="IP address or country..."
                               value="{{ request('search') }}">

                    </div>

                </div>


                <div class="col-md-2">

                    <label class="form-label small fw-bold text-secondary mb-1" style="font-size: 11px;">
                        IP Version
                    </label>

                    <select class="form-select form-select-sm"
                            id="filter_ip_version"
                            name="ip_version">

                        <option value="">All Versions</option>

                        <option value="v4" {{ request('ip_version') === 'v4' ? 'selected' : '' }}>
                            IPv4
                        </option>

                        <option value="v6" {{ request('ip_version') === 'v6' ? 'selected' : '' }}>
                            IPv6
                        </option>

                    </select>

                </div>


                <div class="col-md-2">

                    <label class="form-label small fw-bold text-secondary mb-1" style="font-size: 11px;">
                        Device
                    </label>

                    <select class="form-select form-select-sm"
                            id="filter_device_type"
                            name="device_type">

                        <option value="">All Devices</option>

                        <option value="Desktop" {{ request('device_type') === 'Desktop' ? 'selected' : '' }}>
                            Desktop
                        </option>

                        <option value="Mobile" {{ request('device_type') === 'Mobile' ? 'selected' : '' }}>
                            Mobile
                        </option>

                        <option value="Tablet" {{ request('device_type') === 'Tablet' ? 'selected' : '' }}>
                            Tablet
                        </option>

                        <option value="Bot" {{ request('device_type') === 'Bot' ? 'selected' : '' }}>
                            Bot / Scanner
                        </option>

                    </select>

                </div>


                <div class="col-md-2">

                    <label class="form-label small fw-bold text-secondary mb-1" style="font-size: 11px;">
                        Risk Level
                    </label>

                    <select class="form-select form-select-sm"
                            id="filter_risk_level"
                            name="risk_level">

                        <option value="">All Levels</option>

                        <option value="high" {{ request('risk_level') === 'high' ? 'selected' : '' }}>
                            High (75-100)
                        </option>

                        <option value="medium" {{ request('risk_level') === 'medium' ? 'selected' : '' }}>
                            Medium (40-74)
                        </option>

                        <option value="low" {{ request('risk_level') === 'low' ? 'selected' : '' }}>
                            Low (Below 40)
                        </option>

                    </select>

                </div>


                <div class="col-md-1">

                    <label class="form-label small fw-bold text-secondary mb-1" style="font-size: 11px;">
                        Per Page
                    </label>

                    <select class="form-select form-select-sm bn-digit font-tabular"
                            id="filter_per_page"
                            name="per_page">

                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>

                    </select>

                </div>


                <div class="col-md-2">

                    <button type="button"
                            class="btn btn-outline-secondary btn-compact w-100 shadow-xs"
                            id="btn-reset-filters"
                            style="height: 36px;">

                        <i class="fas fa-rotate-left me-1"></i>
                        <span>Reset</span>

                    </button>

                </div>

            </form>

        </section>


        <!-- 5. IP TABLE -->
        <section class="sec-card overflow-hidden">

            <div class="sec-table-container">

                <table class="table sec-table align-middle mb-0" id="main-ips-table">

                    <thead>

                        <tr>

                            <th class="ps-3 col-ip">IP Address</th>
                            <th class="col-country">Country</th>
                            <th class="col-device">Device / OS</th>
                            <th class="text-center col-hits">Hits</th>
                            <th class="text-center col-orders">Orders</th>
                            <th class="text-center col-forms">Forms</th>
                            <th class="text-center col-fails">Login Fails</th>
                            <th class="text-center col-risk">Risk</th>
                            <th class="col-activity">Last Seen</th>
                            <th class="text-end pe-3 pe-md-4 col-actions">Actions</th>

                        </tr>

                    </thead>


                    <tbody id="ip-table-body">

                        @forelse($ips as $ip)

                            @php
                                $isV6 = filter_var($ip->ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
                                $risk = (int) ($ip->risk_score ?? $ip->max_risk ?? 0);

                                $riskBadge = match(true) {
                                    $risk >= 75 => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    $risk >= 40 => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                    default     => 'bg-success-subtle text-success border border-success-subtle',
                                };

                                $hits = (int) ($ip->total_requests ?? $ip->total_hits ?? $ip->hits_count ?? 0);
                                $orders = (int) ($ip->orders_count ?? 0);
                                $forms = (int) ($ip->forms_count ?? 0);
                                $failedLogins = (int) ($ip->failed_logins_count ?? $ip->failed_logins ?? 0);
                                $lastSeen = $ip->last_activity ?? $ip->last_seen ?? $ip->updated_at ?? $ip->created_at;
                                $isBlocked = (bool) ($ip->is_blocked ?? false);
                            @endphp


                            <tr class="{{ $isBlocked ? 'table-danger bg-opacity-25' : '' }}"
                                data-ip="{{ $ip->ip_address }}">

                                <td class="ps-3 fw-bold text-dark col-ip">

                                    <div class="d-inline-flex align-items-center gap-2">

                                        <a href="{{ route('admin.security.ips.show', $ip->ip_address) }}"
                                           class="text-decoration-none ip-link-text">

                                            {{ $ip->ip_address }}

                                        </a>

                                        <span class="protocol-tag {{ $isV6 ? 'tag-v6' : 'tag-v4' }}">
                                            {{ $isV6 ? 'IPv6' : 'IPv4' }}
                                        </span>

                                        @if($isBlocked)
                                            <span class="badge-blocked-pill">Blocked</span>
                                        @endif

                                    </div>

                                </td>


                                <td class="col-country">

                                    <span class="badge bg-light text-dark border px-2 py-1"
                                          style="font-size: 10.5px; font-weight: 600;">

                                        <i class="fas fa-globe text-secondary me-1"></i>
                                        {{ $ip->country ?: 'Unknown' }}

                                    </span>

                                </td>


                                <td class="text-muted small col-device" style="font-size: 11.5px;">
                                    {{ $ip->device_type ?? 'Desktop' }} ({{ $ip->platform ?? 'OS' }})
                                </td>


                                <td class="text-center fw-bold text-dark bn-digit font-tabular col-hits">
                                    {{ number_format($hits) }}
                                </td>


                                <td class="text-center fw-bold bn-digit font-tabular col-orders {{ $orders > 0 ? 'text-warning' : 'text-muted' }}">
                                    {{ number_format($orders) }}
                                </td>


                                <td class="text-center fw-semibold bn-digit font-tabular text-secondary col-forms">
                                    {{ number_format($forms) }}
                                </td>


                                <td class="text-center fw-bold bn-digit font-tabular col-fails {{ $failedLogins > 0 ? 'text-danger' : 'text-muted' }}">
                                    {{ number_format($failedLogins) }}
                                </td>


                                <td class="text-center col-risk">

                                    <span class="badge {{ $riskBadge }} bn-digit font-tabular px-2 py-0.5"
                                          style="font-size: 9.5px; border-radius: 4px;">

                                        {{ $risk }}/100

                                    </span>

                                </td>


                                <td class="text-muted small col-activity" style="font-size: 11px;">
                                    {{ $lastSeen ? \Carbon\Carbon::parse($lastSeen)->diffForHumans() : 'N/A' }}
                                </td>


                                <td class="text-end pe-3 pe-md-4 col-actions">

                                    <div class="d-inline-flex align-items-center justify-content-end gap-1.5">

                                        <a href="{{ route('admin.security.ips.show', $ip->ip_address) }}"
                                           class="sec-action-btn sec-action-btn-view shadow-xs"
                                           title="View Details">

                                            <i class="fas fa-eye"></i>

                                        </a>


                                        @if(!$isBlocked)

                                            <button type="button"
                                                    class="sec-action-btn ms-1 sec-action-btn-block shadow-xs btn-direct-block"
                                                    data-ip="{{ $ip->ip_address }}"
                                                    title="Block IP">

                                                <i class="fas fa-ban"></i>

                                            </button>

                                        @else

                                            <button type="button"
                                                    class="sec-action-btn sec-action-btn-unblock shadow-xs btn-direct-unblock"
                                                    data-ip="{{ $ip->ip_address }}"
                                                    title="Unblock IP">

                                                <i class="fas fa-lock-open"></i>

                                            </button>

                                        @endif

                                    </div>

                                </td>

                            </tr>


                        @empty

                            <tr>

                                <td colspan="10" class="py-5 text-center text-muted">

                                    <i class="fas fa-network-wired fa-2x mb-2 text-secondary opacity-25 d-block"></i>

                                    <p class="small fw-semibold mb-0">
                                        No IP records found.
                                    </p>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            <!-- Pagination -->
            <div class="card-footer bg-white border-top border-light-subtle py-2.5 px-3 d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2">

                <span class="small text-muted"
                      id="ip-showing-text"
                      style="font-size: 11.5px;">

                    Showing
                    <strong>{{ $ips->firstItem() ?? 0 }}</strong>
                    to
                    <strong>{{ $ips->lastItem() ?? 0 }}</strong>
                    of
                    <strong class="bn-digit font-tabular">{{ number_format($ips->total()) }}</strong>
                    entries

                </span>


                <div id="ip-pagination-container">
                    {{ $ips->links('pagination::bootstrap-5') }}
                </div>

            </div>

        </section>

    </div>

</div>


<!-- 6. MOBILE FILTER MODAL -->
<div class="modal fade sec-modal-scope"
     id="modalMobileFilters"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 107500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content border-0 shadow-lg"
             style="border-radius: 10px !important;">

            <div class="modal-header py-2.5 px-3 bg-light border-bottom d-flex justify-content-between align-items-center">

                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-1.5 mb-0"
                    style="font-size: 13.5px;">

                    <i class="fas fa-sliders me-1 text-primary"></i>
                    Filters

                </h6>

                <button type="button"
                        class="btn-close btn-sm shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>

            </div>


            <div class="modal-body p-3">

                <div class="mb-2.5">

                    <label class="form-label small fw-bold text-secondary mb-1">
                        IP Version
                    </label>

                    <select class="form-select form-select-sm"
                            id="m_filter_ip_version"
                            onchange="syncFromModalToDesktop('filter_ip_version', this.value)">

                        <option value="">All Versions</option>

                        <option value="v4" {{ request('ip_version') === 'v4' ? 'selected' : '' }}>
                            IPv4
                        </option>

                        <option value="v6" {{ request('ip_version') === 'v6' ? 'selected' : '' }}>
                            IPv6
                        </option>

                    </select>

                </div>


                <div class="mb-2.5">

                    <label class="form-label small fw-bold text-secondary mb-1">
                        Device
                    </label>

                    <select class="form-select form-select-sm"
                            id="m_filter_device_type"
                            onchange="syncFromModalToDesktop('filter_device_type', this.value)">

                        <option value="">All Devices</option>

                        <option value="Desktop" {{ request('device_type') === 'Desktop' ? 'selected' : '' }}>
                            Desktop
                        </option>

                        <option value="Mobile" {{ request('device_type') === 'Mobile' ? 'selected' : '' }}>
                            Mobile
                        </option>

                        <option value="Tablet" {{ request('device_type') === 'Tablet' ? 'selected' : '' }}>
                            Tablet
                        </option>

                        <option value="Bot" {{ request('device_type') === 'Bot' ? 'selected' : '' }}>
                            Bot / Scanner
                        </option>

                    </select>

                </div>


                <div class="mb-2.5">

                    <label class="form-label small fw-bold text-secondary mb-1">
                        Risk Level
                    </label>

                    <select class="form-select form-select-sm"
                            id="m_filter_risk_level"
                            onchange="syncFromModalToDesktop('filter_risk_level', this.value)">

                        <option value="">All Levels</option>

                        <option value="high" {{ request('risk_level') === 'high' ? 'selected' : '' }}>
                            High (75-100)
                        </option>

                        <option value="medium" {{ request('risk_level') === 'medium' ? 'selected' : '' }}>
                            Medium (40-74)
                        </option>

                        <option value="low" {{ request('risk_level') === 'low' ? 'selected' : '' }}>
                            Low (Below 40)
                        </option>

                    </select>

                </div>


                <div class="mb-2">

                    <label class="form-label small fw-bold text-secondary mb-1">
                        Per Page
                    </label>

                    <select class="form-select form-select-sm bn-digit font-tabular"
                            id="m_filter_per_page"
                            onchange="syncFromModalToDesktop('filter_per_page', this.value)">

                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>

                    </select>

                </div>

            </div>


            <div class="modal-footer py-2 px-3 bg-light border-top d-flex justify-content-between">

                <button type="button"
                        class="btn btn-outline-secondary btn-compact"
                        onclick="resetModalFilters()">

                    Reset

                </button>

                <button type="button"
                        class="btn btn-primary btn-compact shadow-xs fw-bold px-3"
                        onclick="applyModalFilters()">

                    Apply

                </button>

            </div>

        </div>

    </div>

</div>


<!-- 7. MANUAL BLOCK IP MODAL -->
<div class="modal fade sec-modal-scope"
     id="modalManualBlockIp"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 108000;">

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content border-0 shadow-lg"
             style="border-radius: 8px !important;">

            <div class="modal-header py-2.5 px-3 bg-light border-bottom d-flex justify-content-between align-items-center">

                <h6 class="modal-title fw-bold text-danger d-flex align-items-center gap-1.5 mb-0"
                    style="font-size: 13px;">

                    <i class="fas fa-ban"></i>
                    Block IP

                </h6>

                <button type="button"
                        class="btn-close btn-sm shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>

            </div>


            <form id="formSubmitBlockIp"
                  method="POST"
                  action="javascript:void(0);">

                @csrf

                <div class="modal-body p-3">

                    <div class="mb-3">

                        <label class="form-label small fw-bold text-dark mb-1">
                            IP Address <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               class="form-control form-control-sm bn-digit font-tabular"
                               id="modal_block_ip_address"
                               name="ip_address"
                               required
                               placeholder="203.0.113.195">

                    </div>


                    <div class="row g-2 mb-3">

                        <div class="col-6">

                            <label class="form-label small fw-bold text-dark mb-1">
                                Block Type
                            </label>

                            <select class="form-select form-select-sm"
                                    id="modal_block_type"
                                    name="block_type"
                                    required>

                                <option value="temporary">Temporary</option>
                                <option value="permanent">Permanent</option>

                            </select>

                        </div>


                        <div class="col-6" id="duration_wrapper">

                            <label class="form-label small fw-bold text-dark mb-1">
                                Duration
                            </label>

                            <select class="form-select form-select-sm"
                                    id="modal_duration_minutes"
                                    name="duration_minutes">

                                <option value="15">15 Mins</option>
                                <option value="60" selected>1 Hour</option>
                                <option value="360">6 Hours</option>
                                <option value="1440">24 Hours</option>
                                <option value="10080">7 Days</option>

                            </select>

                        </div>

                    </div>


                    <div class="mb-3">

                        <label class="form-label small fw-bold text-dark mb-1">
                            Reason <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               class="form-control form-control-sm"
                               id="modal_block_reason"
                               name="block_reason"
                               required
                               placeholder="Repeated suspicious activity">

                    </div>


                    <div class="mb-1">

                        <label class="form-label small fw-bold text-dark mb-1">
                            Admin Note (Optional)
                        </label>

                        <textarea class="form-control form-control-sm"
                                  id="modal_admin_note"
                                  name="admin_note"
                                  rows="2"
                                  placeholder="Internal note..."></textarea>

                    </div>

                </div>


                <div class="modal-footer py-2 px-3 bg-light border-top d-flex justify-content-between">

                    <button type="button"
                            class="btn btn-light btn-compact border"
                            data-bs-dismiss="modal">

                        Cancel

                    </button>

                    <button type="submit"
                            class="btn btn-danger btn-compact shadow-xs fw-bold px-3"
                            id="btn-confirm-block-submit">

                        Block IP

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- 8. CONFIRM MODAL -->
<div class="modal fade sec-modal-scope"
     id="ipConfirmMinimalModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 108500;">

    <div class="modal-dialog modal-dialog-centered"
         style="max-width: 400px;">

        <div class="modal-content minimal-modal-card">

            <h6 class="minimal-modal-title"
                id="ipConfirmModalTitle">

                Block this IP?

            </h6>

            <p class="minimal-modal-subtext"
               id="ipConfirmModalMessage">

                This IP will be blocked from accessing the site.

            </p>

            <div class="d-flex align-items-center gap-3">

                <button type="button"
                        class="btn-minimal-action"
                        id="btnExecuteIpConfirm">

                    Block IP

                </button>

                <button type="button"
                        class="btn-minimal-cancel"
                        data-bs-dismiss="modal">

                    Cancel

                </button>

            </div>

        </div>

    </div>

</div>


<!-- 9. NOTICE MODAL -->
<div class="modal fade sec-modal-scope"
     id="ipNoticeMinimalModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index: 108600;">

    <div class="modal-dialog modal-dialog-centered"
         style="max-width: 380px;">

        <div class="modal-content minimal-modal-card">

            <h6 class="minimal-modal-title"
                id="ipNoticeModalTitle">

                Notice

            </h6>

            <p class="minimal-modal-subtext"
               id="ipNoticeModalMessage"
               style="margin-bottom: 16px !important;"></p>

            <div class="d-flex align-items-center">

                <button type="button"
                        class="btn-minimal-action"
                        style="background-color: #1572e8 !important; border-color: #1572e8 !important;"
                        data-bs-dismiss="modal">

                    OK

                </button>

            </div>

        </div>

    </div>

</div>


<!-- 10. AJAX HANDLERS -->
<script>
(function() {
    const tableBody = document.getElementById('ip-table-body');
    const paginationContainer = document.getElementById('ip-pagination-container');
    const filterForm = document.getElementById('ipFilterForm');
    const searchInput = document.getElementById('filter_search');
    const csrfToken = '{{ csrf_token() }}';

    let searchTimer = null;
    let pendingConfirmAction = null;

    // Notice Modal
    function showAppNotice(msg, title = 'Notice') {
        const titleEl = document.getElementById('ipNoticeModalTitle');
        const msgEl   = document.getElementById('ipNoticeModalMessage');
        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = msg;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('ipNoticeMinimalModal')).show();
    }

    // Confirmation Modal
    function showAppConfirm(title, msg, onConfirm, btnText = 'Block IP', btnColor = '#c93424') {
        const titleEl = document.getElementById('ipConfirmModalTitle');
        const msgEl   = document.getElementById('ipConfirmModalMessage');
        const btn     = document.getElementById('btnExecuteIpConfirm');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerHTML = msg;
        if (btn) {
            btn.innerText = btnText;
            btn.style.backgroundColor = btnColor;
        }

        pendingConfirmAction = onConfirm;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('ipConfirmMinimalModal')).show();
    }

    document.getElementById('btnExecuteIpConfirm')?.addEventListener('click', function() {
        bootstrap.Modal.getInstance(document.getElementById('ipConfirmMinimalModal'))?.hide();
        if (typeof pendingConfirmAction === 'function') {
            pendingConfirmAction();
            pendingConfirmAction = null;
        }
    });

    // 1. Fetch IP Data
    function fetchIpsData(url = null) {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        const targetUrl = url || `{{ route('admin.security.ips.index') }}?${params.toString()}`;

        if (tableBody) tableBody.style.opacity = '0.4';

        fetch(targetUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (tableBody) tableBody.style.opacity = '1';

            if (data.success) {
                if (tableBody && data.html) {
                    tableBody.innerHTML = data.html;
                }
                if (paginationContainer && data.pagination) {
                    paginationContainer.innerHTML = data.pagination;
                }
                const counter = document.getElementById('ip-total-counter');
                if (counter && data.total !== undefined) {
                    counter.innerText = Number(data.total).toLocaleString();
                }
                const showingText = document.getElementById('ip-showing-text');
                if (showingText && data.total !== undefined) {
                    showingText.innerHTML = `Showing <strong class="bn-digit font-tabular">${Number(data.total).toLocaleString()}</strong> entries`;
                }
            }
        })
        .catch(err => {
            if (tableBody) tableBody.style.opacity = '1';
            console.error('IPs fetch error:', err);
        });
    }

    // 2. Search
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                fetchIpsData();
            }, 350);
        });
    }

    // Mobile Search Sync
    window.syncMobileSearch = function(val) {
        if (searchInput) {
            searchInput.value = val;
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                fetchIpsData();
            }, 350);
        }
    };

    // Mobile Filter Sync
    window.syncFromModalToDesktop = function(desktopElemId, val) {
        const desktopEl = document.getElementById(desktopElemId);
        if (desktopEl) {
            desktopEl.value = val;
        }
    };

    window.applyModalFilters = function() {
        const modalEl = document.getElementById('modalMobileFilters');
        if (modalEl) {
            bootstrap.Modal.getInstance(modalEl)?.hide();
        }
        updateMobileFilterBadge();
        fetchIpsData();
    };

    window.resetModalFilters = function() {
        document.getElementById('m_filter_ip_version').value = '';
        document.getElementById('m_filter_device_type').value = '';
        document.getElementById('m_filter_risk_level').value = '';
        document.getElementById('m_filter_per_page').value = '20';

        document.getElementById('filter_ip_version').value = '';
        document.getElementById('filter_device_type').value = '';
        document.getElementById('filter_risk_level').value = '';
        document.getElementById('filter_per_page').value = '20';

        const mobSearch = document.getElementById('mobile_filter_search');
        if (mobSearch) mobSearch.value = '';
        if (searchInput) searchInput.value = '';

        updateMobileFilterBadge();
        fetchIpsData();
    };

    function updateMobileFilterBadge() {
        const badge = document.getElementById('mobileActiveFilterBadge');
        if (!badge) return;

        let activeCount = 0;
        if (document.getElementById('m_filter_ip_version').value) activeCount++;
        if (document.getElementById('m_filter_device_type').value) activeCount++;
        if (document.getElementById('m_filter_risk_level').value) activeCount++;

        if (activeCount > 0) {
            badge.innerText = activeCount;
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    }

    // 3. Auto Refresh Filters
    document.querySelectorAll('#filter_ip_version, #filter_device_type, #filter_risk_level, #filter_per_page').forEach(el => {
        el.addEventListener('change', () => fetchIpsData());
    });

    // 4. Reset Filters
    const resetBtn = document.getElementById('btn-reset-filters');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            filterForm.reset();
            const mobSearch = document.getElementById('mobile_filter_search');
            if (mobSearch) mobSearch.value = '';
            fetchIpsData(`{{ route('admin.security.ips.index') }}`);
        });
    }

    // 5. Pagination
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#ip-pagination-container .page-link');
        if (pageLink && pageLink.getAttribute('href')) {
            e.preventDefault();
            fetchIpsData(pageLink.getAttribute('href'));
        }
    });

    // 6. Block / Unblock IP
    document.addEventListener('click', function(e) {
        const blockBtn = e.target.closest('.btn-direct-block');
        if (blockBtn) {
            const ip = blockBtn.getAttribute('data-ip');
            showAppConfirm(
                'Block IP?',
                `Block IP <strong class="text-dark">[${ip}]</strong> from accessing the site?`,
                function() {
                    blockBtn.disabled = true;
                    fetch(`{{ route('admin.security.ips.block') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            ip_address: ip,
                            block_type: 'temporary',
                            duration_minutes: 1440,
                            block_reason: 'Direct 1-click restriction from IP Directory'
                        })
                    })
                    .then(r => r.json())
                    .then(res => {
                        showAppNotice(res.message, 'IP Block');
                        if (res.success) fetchIpsData();
                    })
                    .catch(() => {
                        blockBtn.disabled = false;
                    });
                },
                'Block IP',
                '#c93424'
            );
        }

        const unblockBtn = e.target.closest('.btn-direct-unblock');
        if (unblockBtn) {
            const ip = unblockBtn.getAttribute('data-ip');
            showAppConfirm(
                'Unblock IP?',
                `Remove the block for IP <strong class="text-dark">[${ip}]</strong>?`,
                function() {
                    unblockBtn.disabled = true;
                    fetch(`{{ route('admin.security.ips.unblock') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ ip_address: ip, note: 'Lifted via IP Directory' })
                    })
                    .then(r => r.json())
                    .then(res => {
                        showAppNotice(res.message, 'IP Unblocked');
                        if (res.success) fetchIpsData();
                    })
                    .catch(() => {
                        unblockBtn.disabled = false;
                    });
                },
                'Unblock IP',
                '#16a34a'
            );
        }
    });

    // 7. Block Type / Duration
    const blockTypeSelect = document.getElementById('modal_block_type');
    const durationWrapper = document.getElementById('duration_wrapper');
    if (blockTypeSelect && durationWrapper) {
        blockTypeSelect.addEventListener('change', function() {
            durationWrapper.style.display = this.value === 'permanent' ? 'none' : 'block';
        });
    }

    // 8. Manual Block Form
    const formSubmitBlockIp = document.getElementById('formSubmitBlockIp');
    if (formSubmitBlockIp) {
        formSubmitBlockIp.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('btn-confirm-block-submit');
            submitBtn.disabled = true;
            submitBtn.innerText = 'Blocking...';

            const payload = {
                ip_address: document.getElementById('modal_block_ip_address').value,
                block_type: document.getElementById('modal_block_type').value,
                duration_minutes: document.getElementById('modal_duration_minutes').value,
                block_reason: document.getElementById('modal_block_reason').value,
                admin_note: document.getElementById('modal_admin_note').value,
            };

            fetch(`{{ route('admin.security.ips.block') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(res => {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Block IP';

                const modalEl = document.getElementById('modalManualBlockIp');
                if (modalEl && typeof bootstrap !== 'undefined') {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                }
                formSubmitBlockIp.reset();
                showAppNotice(res.message, 'Firewall Updated');
                if (res.success) fetchIpsData();
            })
            .catch(() => {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Block IP';
                showAppNotice('Network error. Please try again.', 'Error');
            });
        });
    }
})();
</script>

@endsection