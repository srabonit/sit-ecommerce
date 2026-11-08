@extends('layouts.admin')

@section('title', 'Email Subscribers Directory - Email Marketing')

@php
    $stats = $stats ?? [];

    $totalSubscribersCount = $stats['total'] ?? (
        method_exists($subscribers, 'total')
            ? $subscribers->total()
            : $subscribers->count()
    );

    // Safe route fallbacks
    $indexUrl = Route::has('admin.email_marketing.subscribers.index')
        ? route('admin.email_marketing.subscribers.index')
        : url('admin/email-marketing/subscribers');

    $exportUrl = Route::has('admin.email_marketing.subscribers.export')
        ? route('admin.email_marketing.subscribers.export')
        : url('admin/email-marketing/subscribers/export');

    $storeUrl = Route::has('admin.email_marketing.subscribers.store')
        ? route('admin.email_marketing.subscribers.store')
        : url('admin/email-marketing/subscribers/store');

    $bulkSendUrl = Route::has('admin.email_marketing.subscribers.bulk_send_instant')
        ? route('admin.email_marketing.subscribers.bulk_send_instant')
        : url('admin/email-marketing/subscribers/bulk-send-instant');

    $updateBaseUrl       = url('admin/email-marketing/subscribers/update');
    $toggleStatusBaseUrl = url('admin/email-marketing/subscribers/toggle-status');
    $deleteBaseUrl       = url('admin/email-marketing/subscribers/delete');
@endphp

@section('admin_content')

<style>
/* =========================================================
   EMAIL SUBSCRIBERS DIRECTORY
   Modern Admin UI | Bootstrap 5 | Strict 5px Radius
========================================================= */

#email-marketing-subscribers-root {
    --sub-primary: #2563eb;
    --sub-primary-dark: #1d4ed8;
    --sub-primary-soft: #eff6ff;
    --sub-success: #059669;
    --sub-warning: #d97706;
    --sub-danger: #dc2626;
    --sub-info: #0891b2;
    --sub-text: #1e293b;
    --sub-muted: #64748b;
    --sub-border: #e2e8f0;
    --sub-bg: #f8fafc;
    --sub-white: #ffffff;
    --sub-radius: 5px;
    color: var(--sub-text);
    font-size: 13px;
}

/* Global Radius */

#email-marketing-subscribers-root .btn,
#email-marketing-subscribers-root button,
#email-marketing-subscribers-root input,
#email-marketing-subscribers-root select,
#email-marketing-subscribers-root textarea,
#email-marketing-subscribers-root .form-control,
#email-marketing-subscribers-root .form-select,
#email-marketing-subscribers-root .badge,
#email-marketing-subscribers-root .input-group-text,
#email-marketing-subscribers-root .page-link,
#email-marketing-subscribers-root .card,
#email-marketing-subscribers-root .modal-content,
#email-marketing-subscribers-root .collapse-toggle-btn,
#email-marketing-subscribers-root .subscriber-stat-card,
#email-marketing-subscribers-root .subscribers-table-container,
.admin-subscriber-toast {
    border-radius: 5px !important;
}

#email-marketing-subscribers-root .input-group > .input-group-text {
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
}

#email-marketing-subscribers-root .input-group > .form-control,
#email-marketing-subscribers-root .input-group > .form-select {
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
}

/* Typography */

#email-marketing-subscribers-root h4,
#email-marketing-subscribers-root h5,
#email-marketing-subscribers-root h6 {
    letter-spacing: -0.25px;
}

#email-marketing-subscribers-root .font-monospace,
#email-marketing-subscribers-root .bn-digit {
    font-weight: 700 !important;
    font-variant-numeric: tabular-nums;
}

/* Main Header */

#email-marketing-subscribers-root .subscribers-header-wrap {
    border-color: var(--sub-border) !important;
}

#email-marketing-subscribers-root .subscribers-header-wrap h5 {
    font-size: 18px !important;
    letter-spacing: -0.45px;
}

#email-marketing-subscribers-root .subscribers-header-wrap small {
    color: var(--sub-muted) !important;
    line-height: 1.6;
}

#email-marketing-subscribers-root .top-action-bar {
    gap: 7px !important;
}

/* Compact Buttons */

#email-marketing-subscribers-root .btn-compact {
    min-height: 32px;
    padding: 6px 12px !important;
    font-size: 11.5px !important;
    line-height: 1.3 !important;
    font-weight: 600 !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 3px;
    white-space: nowrap;
    transition: all 0.18s ease;
}

#email-marketing-subscribers-root .btn {
    font-weight: 600;
    transition: all 0.18s ease;
}

#email-marketing-subscribers-root .btn-primary {
    background: var(--sub-primary);
    border-color: var(--sub-primary);
}

#email-marketing-subscribers-root .btn-primary:hover {
    background: var(--sub-primary-dark);
    border-color: var(--sub-primary-dark);
    transform: translateY(-1px);
}

#email-marketing-subscribers-root .btn-outline-primary:hover,
#email-marketing-subscribers-root .btn-outline-success:hover {
    transform: translateY(-1px);
}

#email-marketing-subscribers-root .btn:disabled {
    opacity: 0.65;
    cursor: not-allowed;
    transform: none;
}

/* Common Cards */

#email-marketing-subscribers-root .card {
    background: var(--sub-white);
    border: 1px solid var(--sub-border) !important;
    box-shadow: 0 2px 7px rgba(15, 23, 42, 0.035) !important;
    overflow: hidden;
}

/* Section Headers */

#email-marketing-subscribers-root .section-header-wrap {
    min-height: 43px;
    padding: 10px 14px;
    background: var(--sub-white);
    border-bottom: 1px solid var(--sub-border);
}

#email-marketing-subscribers-root .section-header-wrap > span {
    color: var(--sub-text);
    font-size: 12px !important;
    letter-spacing: -0.1px;
}

/* Collapse Button */

#email-marketing-subscribers-root .collapse-toggle-btn {
    width: 26px;
    height: 26px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #64748b;
    transition: all 0.2s ease;
}

#email-marketing-subscribers-root .collapse-toggle-btn:hover {
    background: var(--sub-primary);
    border-color: var(--sub-primary);
    color: #fff;
}

#email-marketing-subscribers-root .collapse-icon {
    font-size: 10px;
    transition: transform 0.25s ease;
}

#email-marketing-subscribers-root .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
    transform: rotate(180deg);
}

/* =========================================================
   STAT CARDS
========================================================= */

#email-marketing-subscribers-root .subscriber-stat-card {
    position: relative;
    min-height: 105px;
    padding: 13px 14px;
    background: #fff;
    border: 1px solid var(--sub-border);
    border-left: 3px solid #64748b;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    overflow: hidden;
}

#email-marketing-subscribers-root .subscriber-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.07);
}

#email-marketing-subscribers-root .subscriber-stat-card h4 {
    font-size: 23px;
    line-height: 1.2;
    margin-top: 7px !important;
    margin-bottom: 5px !important;
    font-weight: 750 !important;
}

#email-marketing-subscribers-root .subscriber-stat-card > div {
    font-size: 9.5px !important;
    letter-spacing: 0.45px;
    color: #64748b !important;
}

#email-marketing-subscribers-root .subscriber-stat-card small {
    font-size: 10px !important;
    color: #94a3b8 !important;
}

#email-marketing-subscribers-root .stat-card-green {
    border-left-color: #10b981 !important;
}

#email-marketing-subscribers-root .stat-card-amber {
    border-left-color: #f59e0b !important;
}

#email-marketing-subscribers-root .stat-card-blue {
    border-left-color: #2563eb !important;
}

#email-marketing-subscribers-root .stat-card-slate {
    border-left-color: #64748b !important;
}

#email-marketing-subscribers-root .stat-card-cyan {
    border-left-color: #06b6d4 !important;
}

#email-marketing-subscribers-root .stat-card-red {
    border-left-color: #ef4444 !important;
}

/* =========================================================
   FILTER TOOLBAR
========================================================= */

#email-marketing-subscribers-root #collapseSubscriberFilter {
    background: #fff;
}

#email-marketing-subscribers-root .sub-filter-control {
    height: 34px !important;
    padding: 5px 9px !important;
    font-size: 11.5px !important;
    color: #334155 !important;
    background-color: #fff !important;
    border: 1px solid #dbe2ea !important;
    box-shadow: none !important;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

#email-marketing-subscribers-root .sub-filter-control::placeholder {
    color: #94a3b8;
}

#email-marketing-subscribers-root .sub-filter-control:focus {
    border-color: var(--sub-primary) !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10) !important;
    outline: none;
}

#email-marketing-subscribers-root .sub-addon-text {
    min-height: 34px;
    padding: 5px 9px !important;
    background: #f8fafc !important;
    border-color: #dbe2ea !important;
    color: #64748b !important;
    font-size: 10.5px !important;
    white-space: nowrap;
}

#email-marketing-subscribers-root .input-group {
    flex-wrap: nowrap;
}

#email-marketing-subscribers-root #subscriber-filter-form .border-top {
    border-color: #edf1f5 !important;
}

#email-marketing-subscribers-root #subscriber-filter-form .small.fw-bold {
    font-size: 11px !important;
}

#email-marketing-subscribers-root #subscriber-filter-form .btn-compact {
    min-height: 32px;
}

/* =========================================================
   BULK ACTION BAR
========================================================= */

#email-marketing-subscribers-root #btn-trigger-bulk-email {
    min-height: 32px;
}

#email-marketing-subscribers-root #selected-subscribers-badge {
    padding: 5px 9px !important;
    font-size: 10.5px !important;
}

#email-marketing-subscribers-root #showing-entries-text {
    color: #64748b;
    font-size: 10.5px;
}

/* =========================================================
   SUBSCRIBER TABLE
========================================================= */

#email-marketing-subscribers-root .subscribers-table-container {
    background: #fff;
    border: 1px solid var(--sub-border);
    overflow: hidden;
}

#email-marketing-subscribers-root .table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

#email-marketing-subscribers-root .subscribers-table {
    width: 100%;
    min-width: 1050px;
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
}

#email-marketing-subscribers-root .subscribers-table thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    padding: 11px 10px;
    color: #475569;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    font-size: 10px;
    font-weight: 750;
    letter-spacing: 0.45px;
    text-transform: uppercase;
    white-space: nowrap;
}

#email-marketing-subscribers-root .subscribers-table tbody td {
    padding: 10px;
    color: #334155;
    background: #fff;
    border-bottom: 1px solid #f1f5f9;
    font-size: 11.5px;
    vertical-align: middle;
    white-space: nowrap;
    transition: background 0.15s ease;
}

#email-marketing-subscribers-root .subscribers-table tbody tr:last-child td {
    border-bottom: 0;
}

#email-marketing-subscribers-root .subscribers-table tbody tr:hover td {
    background: #f8fbff;
}

#email-marketing-subscribers-root .subscribers-table .form-check-input {
    width: 15px;
    height: 15px;
    margin: 0;
    border: 1px solid #cbd5e1;
    cursor: pointer;
    box-shadow: none !important;
}

#email-marketing-subscribers-root .subscribers-table .form-check-input:checked {
    background-color: var(--sub-primary);
    border-color: var(--sub-primary);
}

/* Table Buttons */

#email-marketing-subscribers-root .subscribers-table .btn {
    min-height: 27px;
    padding: 4px 7px;
    font-size: 10.5px;
}

/* Table Badges */

#email-marketing-subscribers-root .subscribers-table .badge {
    padding: 4px 7px;
    font-size: 9.5px;
    font-weight: 650;
    letter-spacing: 0.1px;
}

/* =========================================================
   PAGINATION
========================================================= */

#subscribers-pagination-links nav {
    display: flex !important;
    align-items: center !important;
    justify-content: flex-end !important;
    margin: 0 !important;
}

#subscribers-pagination-links nav > div.d-sm-none,
#subscribers-pagination-links nav > .d-sm-none,
#subscribers-pagination-links nav p,
#subscribers-pagination-links nav .small.text-muted {
    display: none !important;
}

#subscribers-pagination-links .pagination {
    display: flex !important;
    flex-wrap: wrap !important;
    align-items: center !important;
    gap: 4px !important;
    margin: 0 !important;
    padding: 0 !important;
    list-style: none !important;
}

#subscribers-pagination-links .page-item .page-link,
#subscribers-pagination-links .page-item span {
    min-width: 29px !important;
    height: 29px !important;
    padding: 0 8px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    color: #475569 !important;
    background: #fff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 5px !important;
    font-size: 11px !important;
    font-weight: 650 !important;
    text-decoration: none !important;
    line-height: 1 !important;
    transition: all 0.15s ease;
}

#subscribers-pagination-links .page-item .page-link:hover {
    color: var(--sub-primary) !important;
    background: var(--sub-primary-soft) !important;
    border-color: #bfdbfe !important;
}

#subscribers-pagination-links .page-item.active .page-link,
#subscribers-pagination-links .page-item.active span {
    color: #fff !important;
    background: var(--sub-primary) !important;
    border-color: var(--sub-primary) !important;
    box-shadow: 0 2px 5px rgba(37, 99, 235, 0.18);
}

#subscribers-pagination-links .page-item.disabled .page-link,
#subscribers-pagination-links .page-item.disabled span {
    opacity: 0.4 !important;
    background: #f8fafc !important;
    cursor: not-allowed !important;
}

/* AJAX Loading */

#subscribers-table-tbody {
    transition: opacity 0.2s ease;
}

/* =========================================================
   MODALS
========================================================= */

.modal .modal-content {
    border: 1px solid #e2e8f0 !important;
    border-radius: 5px !important;
    overflow: hidden;
    box-shadow: 0 18px 50px rgba(15, 23, 42, 0.16) !important;
}

.modal .modal-header,
.modal .modal-footer {
    background: #f8fafc !important;
    border-color: #e8edf3 !important;
}

.modal .modal-header {
    min-height: 49px;
}

.modal .modal-title {
    font-size: 13.5px !important;
    letter-spacing: -0.15px;
}

.modal .modal-body {
    background: #fff;
}

.modal .modal-footer {
    min-height: 51px;
}

.modal .form-label {
    font-size: 11.5px !important;
    margin-bottom: 6px;
}

.modal .form-control,
.modal .form-select {
    min-height: 36px;
    padding: 7px 10px;
    font-size: 12px;
    border-color: #dbe2ea;
}

.modal textarea.form-control {
    min-height: auto;
    resize: vertical;
}

.modal .form-control:focus,
.modal .form-select:focus {
    border-color: var(--sub-primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.09) !important;
}

.modal .btn-compact {
    min-height: 32px;
}

.modal .alert {
    border-radius: 5px !important;
    font-size: 11.5px;
}

/* Confirmation Modal */

#customConfirmModal .modal-content {
    padding: 20px !important;
}

#customConfirmModal .modal-body {
    padding: 0 !important;
}

#customConfirmModal .rounded-circle {
    border-radius: 50% !important;
}

/* Placeholder Tags */

#bulkEmailModal .badge {
    padding: 5px 8px !important;
    font-size: 10px;
    font-weight: 650;
    cursor: pointer;
    transition: all 0.15s ease;
}

#bulkEmailModal .badge:hover {
    background: #eff6ff !important;
    color: #2563eb !important;
    border-color: #bfdbfe !important;
}

/* =========================================================
   TOAST NOTIFICATIONS
========================================================= */

#custom-toast-holder {
    position: relative;
    z-index: 108000;
}

.admin-subscriber-toast {
    position: fixed;
    right: 22px;
    bottom: 22px;
    z-index: 108000;
    min-width: 280px;
    max-width: min(420px, calc(100vw - 30px));
    padding: 13px 15px;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.18);
    animation: subscriberToastIn 0.25s ease;
}

@keyframes subscriberToastIn {
    from {
        opacity: 0;
        transform: translateX(25px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Focus Accessibility */

#email-marketing-subscribers-root button:focus-visible,
#email-marketing-subscribers-root a:focus-visible,
#email-marketing-subscribers-root input:focus-visible,
#email-marketing-subscribers-root select:focus-visible {
    outline: 2px solid rgba(37, 99, 235, 0.5);
    outline-offset: 2px;
}

/* =========================================================
   TABLET RESPONSIVE
========================================================= */

@media (max-width: 991.98px) {
    #email-marketing-subscribers-root .subscriber-stat-card {
        min-height: 100px;
    }

    #email-marketing-subscribers-root .subscriber-stat-card h4 {
        font-size: 21px;
    }

    #email-marketing-subscribers-root .section-header-wrap {
        padding: 9px 12px;
    }
}

/* =========================================================
   MOBILE RESPONSIVE
========================================================= */

@media (max-width: 767.98px) {

    #email-marketing-subscribers-root {
        padding-left: 10px !important;
        padding-right: 10px !important;
    }

    #email-marketing-subscribers-root .subscribers-header-wrap {
        padding-bottom: 12px !important;
        gap: 12px !important;
    }

    #email-marketing-subscribers-root .subscribers-header-wrap h5 {
        font-size: 16px !important;
    }

    #email-marketing-subscribers-root .subscribers-header-wrap small {
        font-size: 10.5px !important;
        line-height: 1.5;
    }

    #email-marketing-subscribers-root .top-action-bar {
        width: 100%;
        display: grid !important;
        grid-template-columns: 1fr 1fr;
        gap: 7px !important;
    }

    #email-marketing-subscribers-root .top-action-bar .btn {
        width: 100%;
        min-height: 36px;
        padding: 7px 5px !important;
        font-size: 10.5px !important;
    }

    #email-marketing-subscribers-root .subscriber-stat-card {
        min-height: 94px;
        padding: 10px;
    }

    #email-marketing-subscribers-root .subscriber-stat-card > div {
        font-size: 9px !important;
    }

    #email-marketing-subscribers-root .subscriber-stat-card h4 {
        font-size: 20px;
    }

    #email-marketing-subscribers-root .subscriber-stat-card small {
        font-size: 9px !important;
    }

    #email-marketing-subscribers-root #collapseSubscriberStats > div,
    #email-marketing-subscribers-root #collapseSubscriberFilter > div {
        padding: 11px !important;
    }

    #email-marketing-subscribers-root #subscriber-filter-form .row {
        --bs-gutter-x: 7px;
        --bs-gutter-y: 8px;
    }

    #email-marketing-subscribers-root .sub-filter-control {
        height: 35px !important;
        font-size: 11px !important;
    }

    #email-marketing-subscribers-root .sub-addon-text {
        min-height: 35px;
        font-size: 10px !important;
        padding: 5px 7px !important;
    }

    #email-marketing-subscribers-root #subscriber-filter-form .btn-compact {
        min-height: 35px;
        font-size: 10.5px !important;
    }

    #email-marketing-subscribers-root #subscriber-filter-form .mt-2.pt-2 {
        margin-top: 10px !important;
        padding-top: 10px !important;
    }

    #email-marketing-subscribers-root .bulk-action-mobile {
        width: 100%;
    }

    #email-marketing-subscribers-root #btn-trigger-bulk-email {
        min-height: 34px;
    }

    #email-marketing-subscribers-root #showing-entries-text {
        font-size: 9.5px;
    }

    #email-marketing-subscribers-root .subscribers-table {
        min-width: 1050px;
    }

    #email-marketing-subscribers-root .subscribers-table thead th {
        padding: 10px 8px;
        font-size: 9.5px;
    }

    #email-marketing-subscribers-root .subscribers-table tbody td {
        padding: 9px 8px;
        font-size: 10.5px;
    }

    #subscribers-pagination-links nav {
        justify-content: center !important;
    }

    #subscribers-pagination-links .pagination {
        justify-content: center;
    }

    .admin-subscriber-toast {
        right: 10px;
        bottom: 12px;
        left: 10px;
        width: auto;
        min-width: 0;
        max-width: none;
    }

    .modal-dialog {
        margin: 10px !important;
    }

    .modal .modal-body {
        padding: 14px !important;
    }

    .modal .modal-header,
    .modal .modal-footer {
        padding: 10px 12px !important;
    }
}

/* Small Mobile */

@media (max-width: 380px) {
    #email-marketing-subscribers-root .subscriber-stat-card {
        min-height: 88px;
        padding: 8px;
    }

    #email-marketing-subscribers-root .subscriber-stat-card h4 {
        font-size: 18px;
    }

    #email-marketing-subscribers-root .section-header-wrap > span {
        font-size: 10.5px !important;
    }

    #email-marketing-subscribers-root .top-action-bar .btn {
        font-size: 9.5px !important;
    }
}

/* Reduced Motion */

@media (prefers-reduced-motion: reduce) {
    #email-marketing-subscribers-root *,
    .admin-subscriber-toast {
        animation-duration: 0.01ms !important;
        transition-duration: 0.01ms !important;
    }
}
</style>


<div class="container-fluid px-3 py-3" id="email-marketing-subscribers-root">

    {{-- =====================================================
         1. PAGE HEADER
    ====================================================== --}}

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3 pb-3 border-bottom subscribers-header-wrap">

        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">

                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center">
                    <i class="fas fa-users text-primary me-2"></i>
                    <span>Subscribers Directory</span>
                </h5>

                <span
                    class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace"
                    id="subscribers-total-badge"
                    style="font-size: 11px;"
                >
                    {{ number_format($totalSubscribersCount) }} Total
                </span>

            </div>

            <small class="d-block mt-1">
                Manage marketing audiences, newsletter leads, customer engagement, consent records and instant broadcasts.
            </small>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 top-action-bar">

            <button
                type="button"
                class="btn btn-outline-success btn-compact bg-white"
                onclick="exportSubscribersCsv()"
                title="Export current filtered list to CSV"
            >
                <i class="fas fa-file-csv me-1"></i>
                <span>Export CSV</span>
            </button>

            <button
                type="button"
                class="btn btn-primary btn-compact fw-bold"
                data-bs-toggle="modal"
                data-bs-target="#addSubscriberModal"
            >
                <i class="fas fa-user-plus me-1"></i>
                <span>Add Subscriber</span>
            </button>

        </div>
    </div>


    {{-- =====================================================
         2. AUDIENCE STATISTICS
    ====================================================== --}}

    <div class="card mb-3">

        <div class="section-header-wrap d-flex justify-content-between align-items-center">

            <span class="fw-bold">
                <i class="fas fa-chart-pie text-primary me-1"></i>
                Audience Overview &amp; Consent Telemetry
            </span>

            <button
                type="button"
                class="collapse-toggle-btn no-print"
                data-bs-toggle="collapse"
                data-bs-target="#collapseSubscriberStats"
                aria-expanded="true"
                aria-controls="collapseSubscriberStats"
                title="Toggle Statistics"
            >
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>

        </div>

        <div class="collapse show" id="collapseSubscriberStats">

            <div class="p-3">

                <div class="row g-2">

                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="subscriber-stat-card stat-card-green">

                            <div class="text-muted small fw-bold text-uppercase">
                                Active Subscribed
                            </div>

                            <h4 class="text-success font-monospace bn-digit" id="stat-active-subscribed">
                                {{ number_format($stats['subscribed'] ?? 0) }}
                            </h4>

                            <small>Marketing Eligible</small>

                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="subscriber-stat-card stat-card-amber">

                            <div class="text-muted small fw-bold text-uppercase">
                                Unsubscribed
                            </div>

                            <h4 class="text-warning-emphasis font-monospace bn-digit" id="stat-unsubscribed">
                                {{ number_format($stats['unsubscribed'] ?? 0) }}
                            </h4>

                            <small>Opted Out</small>

                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="subscriber-stat-card stat-card-blue">

                            <div class="text-muted small fw-bold text-uppercase">
                                Registered Users
                            </div>

                            <h4 class="text-primary font-monospace bn-digit" id="stat-registered-users">
                                {{ number_format($stats['registered'] ?? 0) }}
                            </h4>

                            <small>With Store Account</small>

                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="subscriber-stat-card stat-card-slate">

                            <div class="text-muted small fw-bold text-uppercase">
                                Guest Subscribers
                            </div>

                            <h4 class="text-secondary font-monospace bn-digit" id="stat-guest-users">
                                {{ number_format($stats['guest'] ?? 0) }}
                            </h4>

                            <small>Checkout / Popups</small>

                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="subscriber-stat-card stat-card-cyan">

                            <div class="text-muted small fw-bold text-uppercase">
                                Emailed Accounts
                            </div>

                            <h4 class="text-info font-monospace bn-digit" id="stat-emailed-accounts">
                                {{ number_format($stats['emailed'] ?? 0) }}
                            </h4>

                            <small>Received Broadcasts</small>

                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="subscriber-stat-card stat-card-red">

                            <div class="text-muted small fw-bold text-uppercase">
                                Bounced / Invalid
                            </div>

                            <h4 class="text-danger font-monospace bn-digit" id="stat-bounced-accounts">
                                {{ number_format($stats['bounced'] ?? 0) }}
                            </h4>

                            <small>Delivery Failed</small>

                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>


    {{-- =====================================================
         3. ADVANCED FILTER TOOLBAR
    ====================================================== --}}

    <div class="card mb-3">

        <div class="section-header-wrap d-flex justify-content-between align-items-center">

            <span class="fw-bold">
                <i class="fas fa-filter text-primary me-1"></i>
                Audience Search &amp; Advanced Filters
            </span>

            <button
                type="button"
                class="collapse-toggle-btn no-print"
                data-bs-toggle="collapse"
                data-bs-target="#collapseSubscriberFilter"
                aria-expanded="true"
                aria-controls="collapseSubscriberFilter"
                title="Toggle Filters"
            >
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>

        </div>

        <div class="collapse show" id="collapseSubscriberFilter">

            <div class="p-3">

                <form id="subscriber-filter-form" onsubmit="return false;">

                    {{-- ROW 1 --}}

                    <div class="row g-2 align-items-center">

                        <div class="col-12 col-md-4">

                            <div class="input-group input-group-sm">

                                <span class="input-group-text sub-addon-text border-end-0">
                                    <i class="fas fa-search"></i>
                                </span>

                                <input
                                    type="text"
                                    name="search"
                                    id="filter-search-input"
                                    class="form-control border-start-0 ps-0 sub-filter-control"
                                    placeholder="Search by email, name or phone..."
                                >

                            </div>

                        </div>

                        <div class="col-6 col-md-2">

                            <select
                                name="status"
                                id="filter-status"
                                class="form-select sub-filter-control filter-trigger font-monospace"
                            >
                                <option value="">Status: All</option>
                                <option value="subscribed">Subscribed</option>
                                <option value="unsubscribed">Unsubscribed</option>
                                <option value="bounced">Bounced</option>
                                <option value="pending">Pending</option>
                            </select>

                        </div>

                        <div class="col-6 col-md-2">

                            <select
                                name="account_type"
                                id="filter-account-type"
                                class="form-select sub-filter-control filter-trigger font-monospace"
                            >
                                <option value="">Account: All Types</option>
                                <option value="registered">Registered User</option>
                                <option value="guest">Guest Subscriber</option>
                            </select>

                        </div>

                        <div class="col-6 col-md-2">

                            <select
                                name="emailed_status"
                                id="filter-emailed-status"
                                class="form-select sub-filter-control filter-trigger font-monospace"
                            >
                                <option value="">Delivery: All</option>
                                <option value="emailed">Emailed Accounts</option>
                                <option value="never_emailed">Never Emailed</option>
                            </select>

                        </div>

                        <div class="col-6 col-md-2">

                            <select
                                name="source"
                                id="filter-source"
                                class="form-select sub-filter-control filter-trigger font-monospace"
                            >
                                <option value="">Source: All</option>
                                <option value="newsletter">Newsletter Form</option>
                                <option value="campaign_lead">Campaign Lead Form</option>
                                <option value="coupon_claim">Coupon Claim Form</option>
                                <option value="contact_form">Contact Form</option>
                                <option value="checkout">Checkout</option>
                                <option value="popup">Popup Modal</option>
                                <option value="footer">Footer Form</option>
                                <option value="admin_manual">Manual Added</option>
                            </select>

                        </div>

                    </div>


                    {{-- ROW 2 --}}

                    <div class="row g-2 align-items-center mt-2 pt-2 border-top">

                        <div class="col-12 col-md-3">

                            <select
                                name="date_filter"
                                id="filter-date-select"
                                class="form-select sub-filter-control font-monospace"
                            >
                                <option value="">Joined: All Time</option>
                                <option value="today">Joined Today</option>
                                <option value="yesterday">Joined Yesterday</option>
                                <option value="this_week">This Week</option>
                                <option value="last_week">Last Week</option>
                                <option value="this_month">This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="this_year">This Year</option>
                                <option value="specific_date">Specific Single Date...</option>
                                <option value="specific_month">Specific Month (YYYY-MM)...</option>
                                <option value="specific_year">Specific Year...</option>
                                <option value="custom_range">Custom Date Range (From - To)...</option>
                            </select>

                        </div>

                        <div class="col-6 col-md-3">

                            <div class="input-group input-group-sm">

                                <span class="input-group-text sub-addon-text">
                                    <i class="fas fa-sort me-1"></i> Sort
                                </span>

                                <select
                                    name="sort_by"
                                    id="filter-sort-by"
                                    class="form-select sub-filter-control filter-trigger font-monospace"
                                >
                                    <option value="latest">Newest Joined</option>
                                    <option value="oldest">Oldest Joined</option>
                                    <option value="email_asc">Email: A to Z</option>
                                    <option value="email_desc">Email: Z to A</option>
                                    <option value="most_emailed">Most Emailed First</option>
                                </select>

                            </div>

                        </div>

                        <div class="col-6 col-md-3">

                            <div class="input-group input-group-sm">

                                <span class="input-group-text sub-addon-text">
                                    Per Page
                                </span>

                                <select
                                    name="per_page"
                                    id="filter-per-page"
                                    class="form-select sub-filter-control filter-trigger font-monospace fw-bold"
                                >
                                    <option value="10">10</option>
                                    <option value="25" selected>25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="all">Show All</option>
                                </select>

                            </div>

                        </div>

                        <div class="col-12 col-md-3 d-flex justify-content-md-end">

                            <button
                                type="button"
                                class="btn btn-light border btn-compact w-100 w-md-auto text-secondary"
                                onclick="resetAllFilters()"
                            >
                                <i class="fas fa-rotate-left me-1"></i>
                                Reset Filters
                            </button>

                        </div>

                    </div>


                    {{-- SPECIFIC DATE PANEL --}}

                    <div
                        id="singleDateWrapper"
                        class="row g-2 align-items-center mt-2 pt-2 border-top d-none"
                    >

                        <div class="col-12 col-sm-auto small fw-bold text-muted">
                            <i class="fas fa-calendar-day text-primary me-1"></i>
                            Specific Join Date:
                        </div>

                        <div class="col-12 col-sm-auto">

                            <input
                                type="date"
                                name="specific_date"
                                id="filter-specific-date"
                                class="form-control sub-filter-control"
                            >

                        </div>

                        <div class="col-12 col-sm-auto d-flex gap-2">

                            <button
                                type="button"
                                class="btn btn-primary btn-compact"
                                onclick="fetchFilteredSubscribers(1)"
                            >
                                Apply Date
                            </button>

                            <button
                                type="button"
                                class="btn btn-light border btn-compact"
                                onclick="resetDatePanels()"
                            >
                                Clear
                            </button>

                        </div>

                    </div>


                    {{-- SPECIFIC MONTH PANEL --}}

                    <div
                        id="specificMonthWrapper"
                        class="row g-2 align-items-center mt-2 pt-2 border-top d-none"
                    >

                        <div class="col-12 col-sm-auto small fw-bold text-muted">
                            <i class="fas fa-calendar-week text-primary me-1"></i>
                            Specific Join Month:
                        </div>

                        <div class="col-12 col-sm-auto">

                            <input
                                type="month"
                                name="specific_month"
                                id="filter-specific-month"
                                class="form-control sub-filter-control"
                            >

                        </div>

                        <div class="col-12 col-sm-auto d-flex gap-2">

                            <button
                                type="button"
                                class="btn btn-primary btn-compact"
                                onclick="fetchFilteredSubscribers(1)"
                            >
                                Apply Month
                            </button>

                            <button
                                type="button"
                                class="btn btn-light border btn-compact"
                                onclick="resetDatePanels()"
                            >
                                Clear
                            </button>

                        </div>

                    </div>


                    {{-- SPECIFIC YEAR PANEL --}}

                    <div
                        id="specificYearWrapper"
                        class="row g-2 align-items-center mt-2 pt-2 border-top d-none"
                    >

                        <div class="col-12 col-sm-auto small fw-bold text-muted">
                            <i class="fas fa-calendar-alt text-primary me-1"></i>
                            Specific Join Year:
                        </div>

                        <div class="col-12 col-sm-auto">

                            <input
                                type="number"
                                min="2020"
                                max="2035"
                                name="specific_year"
                                id="filter-specific-year"
                                class="form-control sub-filter-control font-monospace"
                                placeholder="{{ date('Y') }}"
                            >

                        </div>

                        <div class="col-12 col-sm-auto d-flex gap-2">

                            <button
                                type="button"
                                class="btn btn-primary btn-compact"
                                onclick="fetchFilteredSubscribers(1)"
                            >
                                Apply Year
                            </button>

                            <button
                                type="button"
                                class="btn btn-light border btn-compact"
                                onclick="resetDatePanels()"
                            >
                                Clear
                            </button>

                        </div>

                    </div>


                    {{-- CUSTOM DATE RANGE PANEL --}}

                    <div
                        id="customRangeWrapper"
                        class="row g-2 align-items-center mt-2 pt-2 border-top d-none"
                    >

                        <div class="col-12 col-sm-auto small fw-bold text-muted">
                            <i class="fas fa-calendar-check text-primary me-1"></i>
                            Custom Range:
                        </div>

                        <div class="col-6 col-sm-auto">

                            <div class="input-group input-group-sm">

                                <span class="input-group-text sub-addon-text">
                                    From
                                </span>

                                <input
                                    type="date"
                                    name="start_date"
                                    id="filter-start-date"
                                    class="form-control sub-filter-control"
                                >

                            </div>

                        </div>

                        <div class="col-6 col-sm-auto">

                            <div class="input-group input-group-sm">

                                <span class="input-group-text sub-addon-text">
                                    To
                                </span>

                                <input
                                    type="date"
                                    name="end_date"
                                    id="filter-end-date"
                                    class="form-control sub-filter-control"
                                >

                            </div>

                        </div>

                        <div class="col-12 col-sm-auto d-flex gap-2">

                            <button
                                type="button"
                                class="btn btn-primary btn-compact"
                                onclick="fetchFilteredSubscribers(1)"
                            >
                                Apply Range
                            </button>

                            <button
                                type="button"
                                class="btn btn-light border btn-compact"
                                onclick="resetDatePanels()"
                            >
                                Clear
                            </button>

                        </div>

                    </div>

                </form>

            </div>
        </div>
    </div>


    {{-- =====================================================
         4. BULK ACTION BAR
    ====================================================== --}}

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 px-1">

        <div class="d-flex align-items-center gap-2">

            <button
                type="button"
                class="btn btn-outline-primary btn-compact bg-white"
                id="btn-trigger-bulk-email"
                onclick="openBulkEmailModal()"
            >
                <i class="fas fa-paper-plane me-1"></i>
                Bulk Send Email
            </button>

            <span
                class="badge bg-primary text-white font-monospace d-none"
                id="selected-subscribers-badge"
            >
                0 selected
            </span>

        </div>

        <div class="text-muted small font-monospace bn-digit">

            <span id="showing-entries-text">
                Showing {{ $subscribers->firstItem() ?? 0 }}
                to {{ $subscribers->lastItem() ?? 0 }}
                of {{ number_format($subscribers->total()) }} subscribers
            </span>

        </div>

    </div>


    {{-- =====================================================
         5. SUBSCRIBERS TABLE
    ====================================================== --}}

    <div class="subscribers-table-container shadow-sm mb-3">

        <div class="table-responsive">

            <table
                class="table subscribers-table align-middle mb-0"
                id="subscribers-main-table"
            >

                <thead>

                    <tr>

                        <th style="width: 38px;" class="text-center">

                            <input
                                type="checkbox"
                                class="form-check-input mt-0 shadow-none"
                                id="check-all-subscribers"
                                style="cursor: pointer; width: 15px; height: 15px;"
                            >

                        </th>

                        <th style="width: 55px;" class="text-center">
                            #ID
                        </th>

                        <th>Subscriber Details</th>

                        <th>Account Type</th>

                        <th>Consent Status</th>

                        <th>Emailed History</th>

                        <th>Source &amp; IP</th>

                        <th>Joined At</th>

                        <th class="text-end pe-3" style="min-width: 140px;">
                            Action
                        </th>

                    </tr>

                </thead>

                <tbody id="subscribers-table-tbody">

                    @include(
                        'admin.email_marketing.subscribers.partials.table_rows',
                        ['subscribers' => $subscribers]
                    )

                </tbody>

            </table>

        </div>

    </div>


    {{-- =====================================================
         6. PAGINATION
    ====================================================== --}}

    <div
        class="d-flex justify-content-center justify-content-md-end mb-4"
        id="subscribers-pagination-footer"
        style="{{ $subscribers->hasPages() ? '' : 'display: none;' }}"
    >

        <div id="subscribers-pagination-links">

            @if($subscribers->hasPages())
                {{ $subscribers->links('pagination::bootstrap-5') }}
            @endif

        </div>

    </div>

</div>


{{-- =========================================================
     MODAL 1: BULK EMAIL
========================================================= --}}

<div
    class="modal fade"
    id="bulkEmailModal"
    tabindex="-1"
    aria-hidden="true"
    style="z-index: 106000;"
>

    <div class="modal-dialog modal-dialog-centered modal-lg">

        <div class="modal-content">

            <div class="modal-header py-2 px-3">

                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">

                    <i class="fas fa-paper-plane text-primary"></i>

                    Bulk Instant Email Broadcast

                </h6>

                <button
                    type="button"
                    class="btn-close btn-sm shadow-none"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>

            <form id="bulkEmailForm" onsubmit="submitBulkEmailForm(event)">

                <div class="modal-body p-3">

                    <div class="alert alert-info py-2 px-3 small mb-3 border-0">

                        <i class="fas fa-users me-1"></i>

                        Selected Recipients:

                        <strong
                            id="bulk-modal-recipient-count"
                            class="font-monospace"
                        >
                            0
                        </strong>

                        active subscriber(s).

                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-semibold text-dark">
                            Email Subject
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            id="bulk-email-subject"
                            class="form-control form-control-sm shadow-none"
                            placeholder="Exclusive Offer for You | Shop Now BD"
                            required
                        >

                    </div>

                    <div class="mb-2">

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">

                            <label class="form-label fw-semibold mb-0 text-dark">
                                Email Message (HTML / Text)
                                <span class="text-danger">*</span>
                            </label>

                            <div class="d-flex gap-1 flex-wrap">

                                <span
                                    class="badge bg-light text-dark border"
                                    onclick="insertPlaceholderToBody('{name}')"
                                    style="cursor: pointer;"
                                    title="Insert Customer Name"
                                >
                                    {name}
                                </span>

                                <span
                                    class="badge bg-light text-dark border"
                                    onclick="insertPlaceholderToBody('{email}')"
                                    style="cursor: pointer;"
                                    title="Insert Customer Email"
                                >
                                    {email}
                                </span>

                                <span
                                    class="badge bg-light text-dark border"
                                    onclick="insertPlaceholderToBody('{store_name}')"
                                    style="cursor: pointer;"
                                    title="Insert Store Name"
                                >
                                    {store_name}
                                </span>

                            </div>

                        </div>

                        <textarea
                            id="bulk-email-body"
                            rows="7"
                            class="form-control form-control-sm font-monospace shadow-none"
                            placeholder="Hello {name},&#10;&#10;We have an exciting announcement for you..."
                            required
                        ></textarea>

                        <small class="text-muted d-block mt-2" style="font-size: 10.5px;">
                            You can paste standard HTML code or simple plain text.
                            Click the tags above to insert personalization variables.
                        </small>

                    </div>

                </div>

                <div class="modal-footer py-2 px-3 d-flex justify-content-between">

                    <button
                        type="button"
                        class="btn btn-light btn-compact border"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary btn-compact fw-bold px-3"
                        id="btn-submit-bulk-email"
                    >
                        <i class="fas fa-paper-plane me-1"></i>
                        Send Broadcast
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


{{-- =========================================================
     MODAL 2: DIRECT EMAIL
========================================================= --}}

<div
    class="modal fade"
    id="directEmailModal"
    tabindex="-1"
    aria-hidden="true"
    style="z-index: 106000;"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header py-2 px-3">

                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">

                    <i class="fas fa-envelope text-info"></i>

                    Send Direct Email

                </h6>

                <button
                    type="button"
                    class="btn-close btn-sm shadow-none"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>

            <form id="directEmailForm" onsubmit="submitDirectEmailForm(event)">

                <input type="hidden" id="direct-sub-id">

                <div class="modal-body p-3">

                    <div class="mb-3">

                        <label class="form-label fw-semibold text-muted">
                            To:
                        </label>

                        <input
                            type="text"
                            id="direct-sub-email"
                            class="form-control form-control-sm bg-light font-monospace shadow-none"
                            readonly
                        >

                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-semibold text-dark">
                            Subject
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            id="direct-email-subject"
                            class="form-control form-control-sm shadow-none"
                            placeholder="Subject line..."
                            required
                        >

                    </div>

                    <div class="mb-2">

                        <label class="form-label fw-semibold text-dark">
                            Message
                            <span class="text-danger">*</span>
                        </label>

                        <textarea
                            id="direct-email-body"
                            rows="5"
                            class="form-control form-control-sm shadow-none"
                            placeholder="Your personalized message..."
                            required
                        ></textarea>

                    </div>

                </div>

                <div class="modal-footer py-2 px-3 d-flex justify-content-between">

                    <button
                        type="button"
                        class="btn btn-light btn-compact border"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-info text-white btn-compact fw-bold px-3"
                        id="btn-submit-direct-email"
                    >
                        <i class="fas fa-paper-plane me-1"></i>
                        Send Now
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


{{-- =========================================================
     MODAL 3: ADD SUBSCRIBER
========================================================= --}}

<div
    class="modal fade"
    id="addSubscriberModal"
    tabindex="-1"
    aria-hidden="true"
    style="z-index: 106000;"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header py-2 px-3">

                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">

                    <i class="fas fa-user-plus text-primary"></i>

                    Add New Subscriber

                </h6>

                <button
                    type="button"
                    class="btn-close btn-sm shadow-none"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>

            <form onsubmit="submitAddSubscriberForm(event)">

                <div class="modal-body p-3">

                    <div class="mb-3">

                        <label class="form-label fw-semibold text-dark">
                            Email Address
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="email"
                            id="add-sub-email"
                            class="form-control form-control-sm font-monospace shadow-none"
                            placeholder="customer@example.com"
                            required
                        >

                    </div>

                    <div class="row g-2 mb-3">

                        <div class="col-6">

                            <label class="form-label fw-semibold text-dark">
                                First Name
                            </label>

                            <input
                                type="text"
                                id="add-sub-fname"
                                class="form-control form-control-sm shadow-none"
                                placeholder="John"
                            >

                        </div>

                        <div class="col-6">

                            <label class="form-label fw-semibold text-dark">
                                Last Name
                            </label>

                            <input
                                type="text"
                                id="add-sub-lname"
                                class="form-control form-control-sm shadow-none"
                                placeholder="Doe"
                            >

                        </div>

                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-semibold text-dark">
                            Phone Number
                        </label>

                        <input
                            type="text"
                            id="add-sub-phone"
                            class="form-control form-control-sm font-monospace shadow-none"
                            placeholder="017XXXXXXXX"
                        >

                    </div>

                    <div class="mb-2">

                        <label class="form-label fw-semibold text-dark">
                            Consent Source Tag
                        </label>

                        <select
                            id="add-sub-source"
                            class="form-select form-select-sm font-monospace shadow-none"
                        >
                            <option value="newsletter">Newsletter Form</option>
                            <option value="campaign_lead">Campaign Lead Form</option>
                            <option value="coupon_claim">Coupon Claim Form</option>
                            <option value="contact_form">Contact Form</option>
                            <option value="admin_manual" selected>Admin Manual Addition</option>
                            <option value="pos">POS Offline Customer</option>
                            <option value="checkout">Store Checkout</option>
                            <option value="popup">Newsletter Popup</option>
                            <option value="footer">Website Footer</option>
                        </select>

                    </div>

                </div>

                <div class="modal-footer py-2 px-3 d-flex justify-content-between">

                    <button
                        type="button"
                        class="btn btn-light btn-compact border"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary btn-compact fw-bold px-3"
                        id="btn-submit-add-sub"
                    >
                        <i class="fas fa-save me-1"></i>
                        Save Subscriber
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


{{-- =========================================================
     MODAL 4: EDIT SUBSCRIBER
========================================================= --}}

<div
    class="modal fade"
    id="editSubscriberModal"
    tabindex="-1"
    aria-hidden="true"
    style="z-index: 106000;"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header py-2 px-3">

                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">

                    <i class="fas fa-user-edit text-primary"></i>

                    Edit Subscriber Profile

                </h6>

                <button
                    type="button"
                    class="btn-close btn-sm shadow-none"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>

            </div>

            <form onsubmit="submitEditSubscriberForm(event)">

                <input type="hidden" id="edit-sub-id">

                <div class="modal-body p-3">

                    <div class="mb-3">

                        <label class="form-label fw-semibold text-muted">
                            Email Address (Read-only)
                        </label>

                        <input
                            type="text"
                            id="edit-sub-email"
                            class="form-control form-control-sm bg-light font-monospace shadow-none"
                            readonly
                        >

                    </div>

                    <div class="row g-2 mb-3">

                        <div class="col-6">

                            <label class="form-label fw-semibold text-dark">
                                First Name
                            </label>

                            <input
                                type="text"
                                id="edit-sub-fname"
                                class="form-control form-control-sm shadow-none"
                            >

                        </div>

                        <div class="col-6">

                            <label class="form-label fw-semibold text-dark">
                                Last Name
                            </label>

                            <input
                                type="text"
                                id="edit-sub-lname"
                                class="form-control form-control-sm shadow-none"
                            >

                        </div>

                    </div>

                    <div class="mb-3">

                        <label class="form-label fw-semibold text-dark">
                            Phone Number
                        </label>

                        <input
                            type="text"
                            id="edit-sub-phone"
                            class="form-control form-control-sm font-monospace shadow-none"
                        >

                    </div>

                    <div class="mb-2">

                        <label class="form-label fw-semibold text-dark">
                            Subscription Status
                        </label>

                        <select
                            id="edit-sub-status"
                            class="form-select form-select-sm font-monospace shadow-none"
                            required
                        >
                            <option value="subscribed">Subscribed</option>
                            <option value="unsubscribed">Unsubscribed</option>
                            <option value="bounced">Bounced</option>
                            <option value="pending">Pending</option>
                        </select>

                    </div>

                </div>

                <div class="modal-footer py-2 px-3 d-flex justify-content-between">

                    <button
                        type="button"
                        class="btn btn-light btn-compact border"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary btn-compact fw-bold px-3"
                        id="btn-submit-edit-sub"
                    >
                        <i class="fas fa-save me-1"></i>
                        Update Profile
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


{{-- =========================================================
     MODAL 5: CONFIRMATION
========================================================= --}}

<div
    class="modal fade"
    id="customConfirmModal"
    tabindex="-1"
    aria-hidden="true"
    style="z-index: 106500;"
>

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content text-center">

            <div class="modal-body p-3">

                <div
                    class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 bg-danger-subtle text-danger"
                    style="width: 48px; height: 48px;"
                >
                    <i class="fas fa-trash-alt fs-5"></i>
                </div>

                <h6
                    class="fw-bold text-dark mb-2"
                    id="confirm-modal-title"
                    style="font-size: 14px;"
                >
                    Delete Subscriber
                </h6>

                <p
                    class="text-secondary small mb-3"
                    id="confirm-modal-msg"
                    style="font-size: 11.5px; line-height: 1.5;"
                >
                    Are you sure you want to proceed?
                </p>

                <div class="d-flex justify-content-center gap-2">

                    <button
                        type="button"
                        class="btn btn-light border btn-compact px-3"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        class="btn btn-danger btn-compact fw-bold px-3"
                        id="btn-confirm-action-execute"
                    >
                        Yes, Delete
                    </button>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- TOAST HOLDER --}}

<div id="custom-toast-holder"></div>


{{-- =========================================================
     JAVASCRIPT ENGINE
========================================================= --}}

<script>
(function () {

    'use strict';

    let searchTimeout = null;
    let pendingConfirmCallback = null;

    const indexUrl        = @json($indexUrl);
    const exportUrl       = @json($exportUrl);
    const storeUrl        = @json($storeUrl);
    const bulkSendUrl     = @json($bulkSendUrl);
    const updateBaseUrl   = @json($updateBaseUrl);
    const toggleStatusUrl = @json($toggleStatusBaseUrl);
    const deleteBaseUrl   = @json($deleteBaseUrl);
    const csrfToken       = @json(csrf_token());


    // =====================================================
    // HELPER: BUTTON LOADING STATE
    // =====================================================

    function setButtonLoading(button, loadingHtml, defaultHtml) {

        if (!button) return;

        if (button.dataset.originalHtml === undefined) {
            button.dataset.originalHtml = defaultHtml || button.innerHTML;
        }

        button.disabled = loadingHtml !== null;

        button.innerHTML = loadingHtml !== null
            ? loadingHtml
            : button.dataset.originalHtml;

    }


    // =====================================================
    // HELPER: AJAX HEADERS
    // =====================================================

    function ajaxHeaders(json = false) {

        const headers = {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        };

        if (json) {
            headers['Content-Type'] = 'application/json';
        }

        return headers;

    }


    // =====================================================
    // 1. FILTERED SUBSCRIBER FETCH
    // =====================================================

    function fetchFilteredSubscribers(page = 1) {

        const form = document.getElementById('subscriber-filter-form');

        if (!form) return;

        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        params.set('page', page);

        const tbody = document.getElementById('subscribers-table-tbody');

        if (tbody) {
            tbody.style.opacity = '0.35';
            tbody.style.pointerEvents = 'none';
        }

        fetch(indexUrl + '?' + params.toString(), {
            headers: ajaxHeaders()
        })

        .then(res => {

            if (!res.ok) {
                throw new Error('Unable to fetch subscribers.');
            }

            return res.json();

        })

        .then(data => {

            if (tbody) {

                tbody.innerHTML = data.table_html || '';

                tbody.style.opacity = '1';
                tbody.style.pointerEvents = '';

            }


            // Pagination

            const paginationFooter = document.getElementById('subscribers-pagination-footer');
            const paginationLinks = document.getElementById('subscribers-pagination-links');

            const perPageVal = document.getElementById('filter-per-page')?.value;

            if (paginationLinks && paginationFooter) {

                if (
                    perPageVal === 'all' ||
                    !data.pagination_html ||
                    data.pagination_html.trim() === ''
                ) {

                    paginationLinks.innerHTML = '';
                    paginationFooter.style.display = 'none';

                } else {

                    paginationLinks.innerHTML = data.pagination_html;
                    paginationFooter.style.display = 'flex';

                }

            }


            // Statistics

            if (data.stats) {

                const statMappings = {
                    'stat-active-subscribed': data.stats.subscribed,
                    'stat-unsubscribed': data.stats.unsubscribed,
                    'stat-registered-users': data.stats.registered,
                    'stat-guest-users': data.stats.guest,
                    'stat-emailed-accounts': data.stats.emailed,
                    'stat-bounced-accounts': data.stats.bounced
                };

                Object.entries(statMappings).forEach(([id, value]) => {

                    const element = document.getElementById(id);

                    if (element) {
                        element.innerText = Number(value || 0).toLocaleString();
                    }

                });


                const totalBadge = document.getElementById('subscribers-total-badge');

                if (totalBadge) {
                    totalBadge.innerText =
                        Number(data.stats.total || 0).toLocaleString() + ' Total';
                }

            }


            // Showing Entries

            if (data.entries_text) {

                const entries = document.getElementById('showing-entries-text');

                if (entries) {
                    entries.innerText = data.entries_text;
                }

            }


            // Reset Selection

            const checkAll = document.getElementById('check-all-subscribers');

            if (checkAll) {
                checkAll.checked = false;
                checkAll.indeterminate = false;
            }

            updateSelectionBadge();

        })

        .catch(err => {

            console.error('Subscriber fetch error:', err);

            if (tbody) {
                tbody.style.opacity = '1';
                tbody.style.pointerEvents = '';
            }

            showToast(
                'Error',
                'Unable to load subscribers. Please try again.',
                'danger'
            );

        });

    }


    // =====================================================
    // 2. SEARCH DEBOUNCE
    // =====================================================

    document.getElementById('filter-search-input')?.addEventListener('input', function () {

        clearTimeout(searchTimeout);

        searchTimeout = setTimeout(() => {
            fetchFilteredSubscribers(1);
        }, 350);

    });


    // Filter triggers

    document.querySelectorAll('.filter-trigger').forEach(element => {

        element.addEventListener('change', function () {
            fetchFilteredSubscribers(1);
        });

    });


    // =====================================================
    // 3. DATE FILTER SWITCHER
    // =====================================================

    document.getElementById('filter-date-select')?.addEventListener('change', function () {

        const val = this.value;

        const singleBox = document.getElementById('singleDateWrapper');
        const monthBox = document.getElementById('specificMonthWrapper');
        const yearBox = document.getElementById('specificYearWrapper');
        const rangeBox = document.getElementById('customRangeWrapper');


        [singleBox, monthBox, yearBox, rangeBox].forEach(element => {
            element?.classList.add('d-none');
        });


        if (val === 'specific_date') {

            singleBox?.classList.remove('d-none');

        } else if (val === 'specific_month') {

            monthBox?.classList.remove('d-none');

        } else if (val === 'specific_year') {

            yearBox?.classList.remove('d-none');

        } else if (val === 'custom_range') {

            rangeBox?.classList.remove('d-none');

        } else {

            fetchFilteredSubscribers(1);

        }

    });


    // =====================================================
    // 4. RESET DATE PANELS
    // =====================================================

    function resetDatePanels() {

        const fields = [
            'filter-specific-date',
            'filter-specific-month',
            'filter-specific-year',
            'filter-start-date',
            'filter-end-date'
        ];

        fields.forEach(id => {

            const element = document.getElementById(id);

            if (element) {
                element.value = '';
            }

        });


        const dateSelect = document.getElementById('filter-date-select');

        if (dateSelect) {
            dateSelect.value = '';
        }


        [
            'singleDateWrapper',
            'specificMonthWrapper',
            'specificYearWrapper',
            'customRangeWrapper'
        ].forEach(id => {

            document.getElementById(id)?.classList.add('d-none');

        });


        fetchFilteredSubscribers(1);

    }


    // =====================================================
    // 5. RESET ALL FILTERS
    // =====================================================

    function resetAllFilters() {

        document.getElementById('subscriber-filter-form')?.reset();

        resetDatePanels();

    }


    // =====================================================
    // 6. PAGINATION INTERCEPTOR
    // =====================================================

    document.addEventListener('click', function (e) {

        const pageLink = e.target.closest(
            '#subscribers-pagination-links .page-link'
        );

        if (!pageLink) return;

        e.preventDefault();

        const href = pageLink.getAttribute('href');

        if (href && href !== '#' && !href.startsWith('javascript:')) {

            const url = new URL(href, window.location.origin);

            const page = url.searchParams.get('page') || 1;

            fetchFilteredSubscribers(page);

            document
                .querySelector('.subscribers-table-container')
                ?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });

        }

    });


    // =====================================================
    // 7. CHECKBOX SELECTION
    // =====================================================

    document.getElementById('check-all-subscribers')?.addEventListener(
        'change',
        function () {

            document.querySelectorAll('.subscriber-select-cb').forEach(cb => {

                cb.checked = this.checked;

            });

            updateSelectionBadge();

        }
    );


    document.addEventListener('change', function (e) {

        if (e.target.classList.contains('subscriber-select-cb')) {

            updateSelectionBadge();

            const checkboxes = Array.from(
                document.querySelectorAll('.subscriber-select-cb')
            );

            const checked = checkboxes.filter(cb => cb.checked);

            const checkAll = document.getElementById('check-all-subscribers');

            if (checkAll) {

                checkAll.checked =
                    checkboxes.length > 0 &&
                    checked.length === checkboxes.length;

                checkAll.indeterminate =
                    checked.length > 0 &&
                    checked.length < checkboxes.length;

            }

        }

    });


    function getSelectedSubscriberIds() {

        return Array.from(
            document.querySelectorAll('.subscriber-select-cb:checked')
        ).map(cb => cb.value);

    }


    function updateSelectionBadge() {

        const selected = getSelectedSubscriberIds();

        const badge = document.getElementById('selected-subscribers-badge');

        if (!badge) return;

        if (selected.length > 0) {

            badge.innerText = `${selected.length} selected`;

            badge.classList.remove('d-none');

        } else {

            badge.classList.add('d-none');

        }

    }


    // =====================================================
    // 8. CSV EXPORT
    // =====================================================

    function exportSubscribersCsv() {

        const form = document.getElementById('subscriber-filter-form');

        if (!form) return;

        const formData = new FormData(form);

        const params = new URLSearchParams(formData);

        const url = exportUrl + '?' + params.toString();

        window.open(url, '_blank');

    }


    // =====================================================
    // 9. BULK EMAIL MODAL
    // =====================================================

    function openBulkEmailModal() {

        const selected = getSelectedSubscriberIds();

        if (selected.length === 0) {

            showToast(
                'Warning',
                'Please select at least one subscriber from the table checkbox.',
                'warning'
            );

            return;

        }


        const countElement = document.getElementById(
            'bulk-modal-recipient-count'
        );

        if (countElement) {
            countElement.innerText = selected.length;
        }


        const modalEl = document.getElementById('bulkEmailModal');

        if (modalEl && window.bootstrap) {

            bootstrap.Modal
                .getOrCreateInstance(modalEl)
                .show();

        }

    }


    // =====================================================
    // 10. EMAIL PLACEHOLDER INSERTION
    // =====================================================

    function insertPlaceholderToBody(tag) {

        const textarea = document.getElementById('bulk-email-body');

        if (!textarea) return;

        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;

        const text = textarea.value;

        textarea.value =
            text.substring(0, start) +
            tag +
            text.substring(end);

        textarea.focus();

        textarea.selectionStart = textarea.selectionEnd =
            start + tag.length;

    }


    // =====================================================
    // 11. BULK EMAIL SUBMISSION
    // =====================================================

    function submitBulkEmailForm(e) {

        e.preventDefault();

        const selected = getSelectedSubscriberIds();

        const subject = document
            .getElementById('bulk-email-subject')
            ?.value.trim();

        const message = document
            .getElementById('bulk-email-body')
            ?.value.trim();

        const btn = document.getElementById('btn-submit-bulk-email');


        if (selected.length === 0) {

            showToast('Warning', 'Please select subscribers first.', 'warning');

            return;

        }


        if (!subject || !message) {

            showToast('Warning', 'Subject and message are required.', 'warning');

            return;

        }


        setButtonLoading(
            btn,
            '<i class="fas fa-spinner fa-spin me-1"></i> Broadcasting...'
        );


        fetch(bulkSendUrl, {

            method: 'POST',

            headers: ajaxHeaders(true),

            body: JSON.stringify({

                subscriber_ids: selected,

                subject: subject,

                message: message

            })

        })

        .then(res => {

            if (!res.ok) {
                throw new Error('Bulk email request failed.');
            }

            return res.json();

        })

        .then(data => {

            setButtonLoading(btn, null);

            if (data.success) {

                const modalEl = document.getElementById('bulkEmailModal');

                if (modalEl && window.bootstrap) {
                    bootstrap.Modal.getInstance(modalEl)?.hide();
                }

                document.getElementById('bulkEmailForm')?.reset();

                showToast(
                    'Success',
                    data.message || 'Broadcast dispatched successfully.',
                    'success'
                );

                fetchFilteredSubscribers(1);

            } else {

                showToast(
                    'Error',
                    data.message || 'Failed to dispatch emails.',
                    'danger'
                );

            }

        })

        .catch(err => {

            console.error(err);

            setButtonLoading(btn, null);

            showToast(
                'Error',
                'Network or server error while dispatching bulk emails.',
                'danger'
            );

        });

    }


    // =====================================================
    // 12. DIRECT EMAIL MODAL
    // =====================================================

    function openDirectEmailModal(id, email, name) {

        document.getElementById('direct-sub-id').value = id;

        document.getElementById('direct-sub-email').value =
            `${name} <${email}>`;

        document.getElementById('direct-email-subject').value = '';

        document.getElementById('direct-email-body').value = '';


        const modalEl = document.getElementById('directEmailModal');

        if (modalEl && window.bootstrap) {

            bootstrap.Modal
                .getOrCreateInstance(modalEl)
                .show();

        }

    }


    // =====================================================
    // 13. DIRECT EMAIL SUBMISSION
    // =====================================================

    function submitDirectEmailForm(e) {

        e.preventDefault();

        const subId = document.getElementById('direct-sub-id')?.value;

        const subject = document
            .getElementById('direct-email-subject')
            ?.value.trim();

        const message = document
            .getElementById('direct-email-body')
            ?.value.trim();

        const btn = document.getElementById('btn-submit-direct-email');


        if (!subId || !subject || !message) {

            showToast(
                'Warning',
                'Recipient, subject and message are required.',
                'warning'
            );

            return;

        }


        setButtonLoading(
            btn,
            '<i class="fas fa-spinner fa-spin me-1"></i> Sending...'
        );


        fetch(bulkSendUrl, {

            method: 'POST',

            headers: ajaxHeaders(true),

            body: JSON.stringify({

                subscriber_ids: [subId],

                subject: subject,

                message: message

            })

        })

        .then(res => {

            if (!res.ok) {
                throw new Error('Direct email request failed.');
            }

            return res.json();

        })

        .then(data => {

            setButtonLoading(btn, null);

            if (data.success) {

                const modalEl = document.getElementById('directEmailModal');

                if (modalEl && window.bootstrap) {
                    bootstrap.Modal.getInstance(modalEl)?.hide();
                }

                showToast(
                    'Success',
                    'Direct email dispatched successfully.',
                    'success'
                );

                fetchFilteredSubscribers(1);

            } else {

                showToast(
                    'Error',
                    data.message || 'Failed to send email.',
                    'danger'
                );

            }

        })

        .catch(err => {

            console.error(err);

            setButtonLoading(btn, null);

            showToast(
                'Error',
                'Failed to dispatch email.',
                'danger'
            );

        });

    }


    // =====================================================
    // 14. ADD SUBSCRIBER AJAX
    // =====================================================

    function submitAddSubscriberForm(e) {

        e.preventDefault();

        const email = document
            .getElementById('add-sub-email')
            ?.value.trim();

        const fname = document
            .getElementById('add-sub-fname')
            ?.value.trim();

        const lname = document
            .getElementById('add-sub-lname')
            ?.value.trim();

        const phone = document
            .getElementById('add-sub-phone')
            ?.value.trim();

        const source = document
            .getElementById('add-sub-source')
            ?.value;

        const btn = document.getElementById('btn-submit-add-sub');


        if (!email) {

            showToast('Warning', 'Email address is required.', 'warning');

            return;

        }


        setButtonLoading(
            btn,
            '<i class="fas fa-spinner fa-spin me-1"></i> Saving...'
        );


        fetch(storeUrl, {

            method: 'POST',

            headers: ajaxHeaders(true),

            body: JSON.stringify({

                email: email,

                first_name: fname,

                last_name: lname,

                phone: phone,

                consent_source: source

            })

        })

        .then(res => {

            if (!res.ok) {
                throw new Error('Subscriber creation failed.');
            }

            return res.json();

        })

        .then(data => {

            setButtonLoading(btn, null);

            if (data.success) {

                const modalEl = document.getElementById('addSubscriberModal');

                if (modalEl && window.bootstrap) {
                    bootstrap.Modal.getInstance(modalEl)?.hide();
                }

                document.getElementById('add-sub-email').value = '';
                document.getElementById('add-sub-fname').value = '';
                document.getElementById('add-sub-lname').value = '';
                document.getElementById('add-sub-phone').value = '';

                showToast(
                    'Success',
                    data.message || 'Subscriber added successfully.',
                    'success'
                );

                fetchFilteredSubscribers(1);

            } else {

                showToast(
                    'Error',
                    data.message || 'Could not add subscriber.',
                    'danger'
                );

            }

        })

        .catch(err => {

            console.error(err);

            setButtonLoading(btn, null);

            showToast(
                'Error',
                'Email address might already exist or the request failed.',
                'danger'
            );

        });

    }


    // =====================================================
    // 15. EDIT SUBSCRIBER MODAL
    // =====================================================

    function openEditSubscriberModal(sub) {

        document.getElementById('edit-sub-id').value = sub.id;

        document.getElementById('edit-sub-email').value = sub.email;

        document.getElementById('edit-sub-fname').value =
            sub.first_name || '';

        document.getElementById('edit-sub-lname').value =
            sub.last_name || '';

        document.getElementById('edit-sub-phone').value =
            sub.phone || '';

        document.getElementById('edit-sub-status').value =
            sub.status || 'subscribed';


        const modalEl = document.getElementById('editSubscriberModal');

        if (modalEl && window.bootstrap) {

            bootstrap.Modal
                .getOrCreateInstance(modalEl)
                .show();

        }

    }


    // =====================================================
    // 16. EDIT SUBSCRIBER SUBMISSION
    // =====================================================

    function submitEditSubscriberForm(e) {

        e.preventDefault();

        const id = document.getElementById('edit-sub-id')?.value;

        const fname = document
            .getElementById('edit-sub-fname')
            ?.value.trim();

        const lname = document
            .getElementById('edit-sub-lname')
            ?.value.trim();

        const phone = document
            .getElementById('edit-sub-phone')
            ?.value.trim();

        const status = document
            .getElementById('edit-sub-status')
            ?.value;

        const btn = document.getElementById('btn-submit-edit-sub');


        if (!id) {

            showToast('Error', 'Subscriber ID is missing.', 'danger');

            return;

        }


        setButtonLoading(
            btn,
            '<i class="fas fa-spinner fa-spin me-1"></i> Updating...'
        );


        fetch(`${updateBaseUrl}/${id}`, {

            method: 'POST',

            headers: ajaxHeaders(true),

            body: JSON.stringify({

                first_name: fname,

                last_name: lname,

                phone: phone,

                status: status

            })

        })

        .then(res => {

            if (!res.ok) {
                throw new Error('Subscriber update failed.');
            }

            return res.json();

        })

        .then(data => {

            setButtonLoading(btn, null);

            if (data.success) {

                const modalEl = document.getElementById('editSubscriberModal');

                if (modalEl && window.bootstrap) {
                    bootstrap.Modal.getInstance(modalEl)?.hide();
                }

                showToast(
                    'Success',
                    data.message || 'Subscriber updated successfully.',
                    'success'
                );

                fetchFilteredSubscribers(1);

            } else {

                showToast(
                    'Error',
                    data.message || 'Update failed.',
                    'danger'
                );

            }

        })

        .catch(err => {

            console.error(err);

            setButtonLoading(btn, null);

            showToast('Error', 'Update failed.', 'danger');

        });

    }


    // =====================================================
    // 17. TOGGLE SUBSCRIBER STATUS
    // =====================================================

    function toggleSubscriberStatusAjax(id) {

        fetch(`${toggleStatusUrl}/${id}`, {

            method: 'POST',

            headers: ajaxHeaders()

        })

        .then(res => {

            if (!res.ok) {
                throw new Error('Status update failed.');
            }

            return res.json();

        })

        .then(data => {

            showToast(
                'Updated',
                `Subscriber is now ${data.status}.`,
                'info'
            );

            fetchFilteredSubscribers(1);

        })

        .catch(err => {

            console.error(err);

            showToast(
                'Error',
                'Unable to update subscriber status.',
                'danger'
            );

            fetchFilteredSubscribers(1);

        });

    }


    // =====================================================
    // 18. DELETE SUBSCRIBER CONFIRMATION
    // =====================================================

    function deleteSubscriberAjax(id, email) {

        const title = document.getElementById('confirm-modal-title');

        const message = document.getElementById('confirm-modal-msg');


        if (title) {
            title.innerText = 'Delete Subscriber';
        }

        if (message) {

            message.replaceChildren();

            message.append(
                document.createTextNode(
                    'Are you sure you want to delete '
                )
            );

            const strong = document.createElement('strong');

            strong.textContent = email;

            message.append(strong);

            message.append(
                document.createTextNode('?')
            );

        }


        pendingConfirmCallback = function () {

            const confirmBtn = document.getElementById(
                'btn-confirm-action-execute'
            );

            if (confirmBtn) {

                confirmBtn.disabled = true;

                confirmBtn.innerHTML =
                    '<i class="fas fa-spinner fa-spin me-1"></i> Deleting...';

            }


            fetch(`${deleteBaseUrl}/${id}`, {

                method: 'DELETE',

                headers: ajaxHeaders()

            })

            .then(res => {

                if (!res.ok) {
                    throw new Error('Delete request failed.');
                }

                return res.json();

            })

            .then(data => {

                const modalEl = document.getElementById('customConfirmModal');

                if (modalEl && window.bootstrap) {
                    bootstrap.Modal.getInstance(modalEl)?.hide();
                }

                showToast(
                    'Deleted',
                    data.message || 'Subscriber deleted successfully.',
                    'success'
                );

                fetchFilteredSubscribers(1);

            })

            .catch(err => {

                console.error(err);

                showToast(
                    'Error',
                    'Unable to delete subscriber.',
                    'danger'
                );

            })

            .finally(() => {

                if (confirmBtn) {

                    confirmBtn.disabled = false;

                    confirmBtn.innerHTML = 'Yes, Delete';

                }

            });

        };


        const modalEl = document.getElementById('customConfirmModal');

        if (modalEl && window.bootstrap) {

            bootstrap.Modal
                .getOrCreateInstance(modalEl)
                .show();

        }

    }


    // =====================================================
    // 19. CONFIRM ACTION BUTTON
    // =====================================================

    document
        .getElementById('btn-confirm-action-execute')
        ?.addEventListener('click', function () {

            if (typeof pendingConfirmCallback === 'function') {

                const callback = pendingConfirmCallback;

                pendingConfirmCallback = null;

                callback();

            }

        });


    // =====================================================
    // 20. TOAST NOTIFICATIONS
    // =====================================================

    function showToast(title, message, type = 'success') {

        const holder = document.getElementById('custom-toast-holder');

        if (!holder) return;


        const toastId = 'toast-' + Date.now() + '-' +
            Math.random().toString(36).slice(2, 7);


        const typeConfigs = {

            success: {
                icon: 'fa-check-circle',
                color: 'bg-success text-white'
            },

            danger: {
                icon: 'fa-times-circle',
                color: 'bg-danger text-white'
            },

            warning: {
                icon: 'fa-exclamation-triangle',
                color: 'bg-warning text-dark'
            },

            info: {
                icon: 'fa-info-circle',
                color: 'bg-primary text-white'
            }

        };


        const config = typeConfigs[type] || typeConfigs.success;


        // Build toast safely using DOM APIs

        const toast = document.createElement('div');

        toast.id = toastId;

        toast.className =
            `admin-subscriber-toast ${config.color} d-flex align-items-center justify-content-between gap-3`;


        const content = document.createElement('div');

        content.className = 'd-flex align-items-center gap-3';


        const icon = document.createElement('i');

        icon.className = `fas ${config.icon} fa-lg`;


        const textWrapper = document.createElement('div');


        const heading = document.createElement('strong');

        heading.className = 'd-block';

        heading.style.fontSize = '13px';

        heading.textContent = title;


        const description = document.createElement('small');

        description.style.fontSize = '11.5px';

        description.style.opacity = '0.95';

        description.textContent = message;


        textWrapper.append(heading, description);

        content.append(icon, textWrapper);


        const closeButton = document.createElement('button');

        closeButton.type = 'button';

        closeButton.className =
            `btn-close ${type === 'warning' ? '' : 'btn-close-white'} ms-2 shadow-none`;

        closeButton.setAttribute('aria-label', 'Close notification');

        closeButton.addEventListener('click', function () {
            toast.remove();
        });


        toast.append(content, closeButton);

        holder.appendChild(toast);


        setTimeout(() => {

            const element = document.getElementById(toastId);

            if (element) {
                element.remove();
            }

        }, 4000);

    }


    // =====================================================
    // 21. EXPOSE FUNCTIONS FOR BLADE INLINE HANDLERS
    // =====================================================

    window.fetchFilteredSubscribers = fetchFilteredSubscribers;

    window.resetDatePanels = resetDatePanels;

    window.resetAllFilters = resetAllFilters;

    window.exportSubscribersCsv = exportSubscribersCsv;

    window.openBulkEmailModal = openBulkEmailModal;

    window.insertPlaceholderToBody = insertPlaceholderToBody;

    window.submitBulkEmailForm = submitBulkEmailForm;

    window.openDirectEmailModal = openDirectEmailModal;

    window.submitDirectEmailForm = submitDirectEmailForm;

    window.submitAddSubscriberForm = submitAddSubscriberForm;

    window.openEditSubscriberModal = openEditSubscriberModal;

    window.submitEditSubscriberForm = submitEditSubscriberForm;

    window.toggleSubscriberStatusAjax = toggleSubscriberStatusAjax;

    window.deleteSubscriberAjax = deleteSubscriberAjax;

    window.showToast = showToast;


})();
</script>

@endsection