@extends('layouts.admin')

@section('title', 'Email Subscribers Directory - Email Marketing')

@php
    $stats = $stats ?? [];
    $totalSubscribersCount = $stats['total'] ?? (method_exists($subscribers, 'total') ? $subscribers->total() : $subscribers->count());

    // Safe route fallbacks to prevent RouteNotFoundException
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
    /* Global Strict 5px Element Radius */
    #email-marketing-subscribers-root .btn,
    #email-marketing-subscribers-root .btn-sm,
    #email-marketing-subscribers-root button,
    #email-marketing-subscribers-root .form-control,
    #email-marketing-subscribers-root .form-select,
    #email-marketing-subscribers-root .badge,
    #email-marketing-subscribers-root .input-group-text,
    #email-marketing-subscribers-root .page-link,
    #email-marketing-subscribers-root .card,
    #email-marketing-subscribers-root .modal-content,
    #email-marketing-subscribers-root .collapse-toggle-btn,
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

    /* Safe Monospace & Bengali Digit Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Compact Button & Input Styling */
    .btn-compact {
        font-size: 11.5px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    .sub-filter-control {
        font-size: 12px !important;
        height: 32px !important;
        padding: 4px 8px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        box-shadow: none !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .sub-filter-control:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        outline: none;
    }

    .sub-addon-text {
        height: 32px !important;
        background-color: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: #64748b !important;
        font-size: 11px !important;
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

    .section-header-wrap {
        padding: 10px 14px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
    }

    /* Stat Cards Architecture */
    .subscriber-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 5px !important;
        padding: 12px 14px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .subscriber-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }

    .stat-card-green  { border-left: 3.5px solid #10b981 !important; }
    .stat-card-amber  { border-left: 3.5px solid #f59e0b !important; }
    .stat-card-blue   { border-left: 3.5px solid #2563eb !important; }
    .stat-card-slate  { border-left: 3.5px solid #64748b !important; }
    .stat-card-cyan   { border-left: 3.5px solid #06b6d4 !important; }
    .stat-card-red    { border-left: 3.5px solid #ef4444 !important; }

    /* Table Container Styling */
    .subscribers-table-container {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 5px !important;
        overflow: hidden;
    }
    .subscribers-table {
        width: 100%;
        margin-bottom: 0;
    }
    .subscribers-table th {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        white-space: nowrap;
        padding: 9px 8px;
    }
    .subscribers-table td {
        padding: 8px 8px;
        vertical-align: middle;
        font-size: 12px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }
    .subscribers-table tbody tr:hover {
        background-color: #fafbfc;
    }

    /* Responsive Pagination */
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
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        gap: 3px !important;
        padding: 0 !important;
        list-style: none !important;
    }
    #subscribers-pagination-links .page-item .page-link,
    #subscribers-pagination-links .page-item span {
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
    #subscribers-pagination-links .page-item .page-link:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }
    #subscribers-pagination-links .page-item.active .page-link,
    #subscribers-pagination-links .page-item.active span {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25) !important;
    }
    #subscribers-pagination-links .page-item.disabled .page-link,
    #subscribers-pagination-links .page-item.disabled span {
        opacity: 0.45 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }

    /* Floating Toast Box */
    .admin-subscriber-toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 108000;
        min-width: 280px;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.2);
        animation: slideInRight 0.25s ease;
    }
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    @media (max-width: 767.98px) {
        .subscribers-header-wrap h5 {
            font-size: 16px !important;
        }
        .top-action-bar {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .top-action-bar .btn {
            width: 100%;
            padding: 8px 6px !important;
            font-size: 11px !important;
            justify-content: center;
        }
    }
</style>

<div class="container-fluid px-3 py-2" id="email-marketing-subscribers-root">
    
    {{-- 1. Header & Actions Toolbar --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 pb-3 border-bottom subscribers-header-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center" style="font-size: 18px;">
                    <i class="fas fa-users text-primary me-2"></i>
                    <span>Subscribers Directory</span>
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace" id="subscribers-total-badge" style="font-size: 11px;">
                    {{ number_format($totalSubscribersCount) }} Total
                </span>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 12px;">Manage marketing audiences, dynamic form leads (Newsletter, Campaign, Coupon), legal consents, customer engagement and instant broadcasts.</small>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 top-action-bar">
            <button type="button" class="btn btn-outline-success btn-compact shadow-xs fw-semibold bg-white" onclick="exportSubscribersCsv()" title="Export current filtered list to CSV">
                <i class="fas fa-file-csv me-1 text-success"></i>
                <span>Export CSV</span>
            </button>
            <button type="button" class="btn btn-primary btn-compact shadow-xs fw-bold" data-bs-toggle="modal" data-bs-target="#addSubscriberModal">
                <i class="fas fa-user-plus me-1"></i>
                <span>Add Subscriber</span>
            </button>
        </div>
    </div>

    {{-- 2. Live Analytics Stat Badges (Collapsible Section) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-chart-pie text-primary me-1"></i> Audience Overview &amp; Consent Telemetry
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseSubscriberStats" aria-expanded="true" aria-controls="collapseSubscriberStats" title="Toggle Stats">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseSubscriberStats">
            <div class="p-3">
                <div class="row g-2">
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="subscriber-stat-card stat-card-green">
                            <div class="text-muted small fw-bold text-uppercase" style="font-size: 10px;">Active Subscribed</div>
                            <h4 class="fw-bold my-1 text-success font-monospace bn-digit" id="stat-active-subscribed">{{ number_format($stats['subscribed'] ?? 0) }}</h4>
                            <small class="text-muted font-monospace" style="font-size: 10px;">Marketing Eligible</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="subscriber-stat-card stat-card-amber">
                            <div class="text-muted small fw-bold text-uppercase" style="font-size: 10px;">Unsubscribed</div>
                            <h4 class="fw-bold my-1 text-warning-emphasis font-monospace bn-digit" id="stat-unsubscribed">{{ number_format($stats['unsubscribed'] ?? 0) }}</h4>
                            <small class="text-muted font-monospace" style="font-size: 10px;">Opted Out</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="subscriber-stat-card stat-card-blue">
                            <div class="text-muted small fw-bold text-uppercase" style="font-size: 10px;">Registered Users</div>
                            <h4 class="fw-bold my-1 text-primary font-monospace bn-digit" id="stat-registered-users">{{ number_format($stats['registered'] ?? 0) }}</h4>
                            <small class="text-muted font-monospace" style="font-size: 10px;">With Store Account</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="subscriber-stat-card stat-card-slate">
                            <div class="text-muted small fw-bold text-uppercase" style="font-size: 10px;">Guest Subscribers</div>
                            <h4 class="fw-bold my-1 text-secondary font-monospace bn-digit" id="stat-guest-users">{{ number_format($stats['guest'] ?? 0) }}</h4>
                            <small class="text-muted font-monospace" style="font-size: 10px;">Checkout / Popups</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="subscriber-stat-card stat-card-cyan">
                            <div class="text-muted small fw-bold text-uppercase" style="font-size: 10px;">Emailed Accounts</div>
                            <h4 class="fw-bold my-1 text-info font-monospace bn-digit" id="stat-emailed-accounts">{{ number_format($stats['emailed'] ?? 0) }}</h4>
                            <small class="text-muted font-monospace" style="font-size: 10px;">Received Broadcasts</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="subscriber-stat-card stat-card-red">
                            <div class="text-muted small fw-bold text-uppercase" style="font-size: 10px;">Bounced / Invalid</div>
                            <h4 class="fw-bold my-1 text-danger font-monospace bn-digit" id="stat-bounced-accounts">{{ number_format($stats['bounced'] ?? 0) }}</h4>
                            <small class="text-muted font-monospace" style="font-size: 10px;">Delivery Failed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Advanced Multi-Dimensional Filter Toolbar (Collapsible Section) --}}
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 5px !important; background: #f8fafc; border: 1px solid #e2e8f0 !important;">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-filter text-primary me-1"></i> Multi-Dimensional Audience Search &amp; Filter Toolbar
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseSubscriberFilter" aria-expanded="true" aria-controls="collapseSubscriberFilter" title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseSubscriberFilter">
            <div class="p-3">
                <form id="subscriber-filter-form" onsubmit="return false;">
                    
                    {{-- Row 1: Search, Status, Account Type, Emailed Status, Source (Expanded with Dynamic Form Sources) --}}
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text sub-addon-text border-end-0"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" name="search" id="filter-search-input" class="form-control border-start-0 ps-0 sub-filter-control" placeholder="Search by email, name or phone...">
                            </div>
                        </div>

                        <div class="col-6 col-md-2">
                            <select name="status" id="filter-status" class="form-select sub-filter-control filter-trigger font-monospace">
                                <option value="">Status: All</option>
                                <option value="subscribed">Subscribed</option>
                                <option value="unsubscribed">Unsubscribed</option>
                                <option value="bounced">Bounced</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-2">
                            <select name="account_type" id="filter-account-type" class="form-select sub-filter-control filter-trigger font-monospace">
                                <option value="">Account: All Types</option>
                                <option value="registered">Registered User</option>
                                <option value="guest">Guest Subscriber</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-2">
                            <select name="emailed_status" id="filter-emailed-status" class="form-select sub-filter-control filter-trigger font-monospace">
                                <option value="">Delivery: All</option>
                                <option value="emailed">Emailed Accounts</option>
                                <option value="never_emailed">Never Emailed</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-2">
                            <select name="source" id="filter-source" class="form-select sub-filter-control filter-trigger font-monospace">
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

                    {{-- Row 2: Date Presets, Sorting, Per Page, Reset --}}
                    <div class="row g-2 align-items-center mt-2 pt-2 border-top">
                        <div class="col-12 col-md-3">
                            <select name="date_filter" id="filter-date-select" class="form-select sub-filter-control font-monospace">
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
                                <span class="input-group-text sub-addon-text text-muted"><i class="fas fa-sort me-1"></i> Sort</span>
                                <select name="sort_by" id="filter-sort-by" class="form-select sub-filter-control filter-trigger font-monospace">
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
                                <span class="input-group-text sub-addon-text text-muted">Per Page</span>
                                <select name="per_page" id="filter-per-page" class="form-select sub-filter-control filter-trigger font-monospace fw-bold">
                                    <option value="10">10 </option>
                                    <option value="25" selected>25 </option>
                                    <option value="50">50 </option>
                                    <option value="100">100 </option>
                                    <option value="all">Show All</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 d-flex justify-content-md-end">
                            <button type="button" class="btn btn-light border btn-compact w-100 w-md-auto text-secondary shadow-xs fw-semibold" onclick="resetAllFilters()">
                                <i class="fas fa-rotate-left me-1"></i>
                                <span>Reset Filters</span>
                            </button>
                        </div>
                    </div>

                    {{-- Option A: Specific Single Date Panel --}}
                    <div id="singleDateWrapper" class="row g-2 align-items-center mt-2 pt-2 border-top d-none">
                        <div class="col-12 col-sm-auto small fw-bold text-muted">
                            <i class="fas fa-calendar-day text-primary me-1"></i> Specific Join Date:
                        </div>
                        <div class="col-12 col-sm-auto">
                            <input type="date" name="specific_date" id="filter-specific-date" class="form-control sub-filter-control">
                        </div>
                        <div class="col-12 col-sm-auto d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-compact" onclick="fetchFilteredSubscribers(1)">Apply Date</button>
                            <button type="button" class="btn btn-light border btn-compact" onclick="resetDatePanels()">Clear</button>
                        </div>
                    </div>

                    {{-- Option B: Specific Month Panel (YYYY-MM) --}}
                    <div id="specificMonthWrapper" class="row g-2 align-items-center mt-2 pt-2 border-top d-none">
                        <div class="col-12 col-sm-auto small fw-bold text-muted">
                            <i class="fas fa-calendar-week text-primary me-1"></i> Specific Join Month:
                        </div>
                        <div class="col-12 col-sm-auto">
                            <input type="month" name="specific_month" id="filter-specific-month" class="form-control sub-filter-control">
                        </div>
                        <div class="col-12 col-sm-auto d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-compact" onclick="fetchFilteredSubscribers(1)">Apply Month</button>
                            <button type="button" class="btn btn-light border btn-compact" onclick="resetDatePanels()">Clear</button>
                        </div>
                    </div>

                    {{-- Option C: Specific Year Panel --}}
                    <div id="specificYearWrapper" class="row g-2 align-items-center mt-2 pt-2 border-top d-none">
                        <div class="col-12 col-sm-auto small fw-bold text-muted">
                            <i class="fas fa-calendar-alt text-primary me-1"></i> Specific Join Year:
                        </div>
                        <div class="col-12 col-sm-auto">
                            <input type="number" min="2020" max="2035" name="specific_year" id="filter-specific-year" class="form-control sub-filter-control font-monospace" placeholder="{{ date('Y') }}">
                        </div>
                        <div class="col-12 col-sm-auto d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-compact" onclick="fetchFilteredSubscribers(1)">Apply Year</button>
                            <button type="button" class="btn btn-light border btn-compact" onclick="resetDatePanels()">Clear</button>
                        </div>
                    </div>

                    {{-- Option D: Custom Date Range Panel (From - To) --}}
                    <div id="customRangeWrapper" class="row g-2 align-items-center mt-2 pt-2 border-top d-none">
                        <div class="col-12 col-sm-auto small fw-bold text-muted">
                            <i class="fas fa-calendar-check text-primary me-1"></i> Custom Range:
                        </div>
                        <div class="col-6 col-sm-auto">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text sub-addon-text text-muted">From</span>
                                <input type="date" name="start_date" id="filter-start-date" class="form-control sub-filter-control">
                            </div>
                        </div>
                        <div class="col-6 col-sm-auto">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text sub-addon-text text-muted">To</span>
                                <input type="date" name="end_date" id="filter-end-date" class="form-control sub-filter-control">
                            </div>
                        </div>
                        <div class="col-12 col-sm-auto d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-compact" onclick="fetchFilteredSubscribers(1)">Apply Range</button>
                            <button type="button" class="btn btn-light border btn-compact" onclick="resetDatePanels()">Clear</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- 4. Bulk Action Strip --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 px-1">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary btn-compact shadow-xs fw-semibold bg-white" id="btn-trigger-bulk-email" onclick="openBulkEmailModal()">
                <i class="fas fa-paper-plane me-1"></i> Bulk Send Email
            </button>
            <span class="badge bg-primary text-white font-monospace d-none" id="selected-subscribers-badge" style="font-size: 11px;">
                0 selected
            </span>
        </div>
        <div class="text-muted small font-monospace bn-digit" style="font-size: 11px;">
            <span id="showing-entries-text">Showing {{ $subscribers->firstItem() ?? 0 }} to {{ $subscribers->lastItem() ?? 0 }} of {{ number_format($subscribers->total()) }} subscribers</span>
        </div>
    </div>

    {{-- 5. Subscribers Table Container --}}
    <div class="subscribers-table-container shadow-sm mb-3">
        <div class="table-responsive">
            <table class="table subscribers-table align-middle mb-0" id="subscribers-main-table">
                <thead>
                    <tr>
                        <th style="width: 38px;" class="text-center">
                            <input type="checkbox" class="form-check-input mt-0 shadow-none" id="check-all-subscribers" style="cursor: pointer; width: 15px; height: 15px; border-radius: 4px;">
                        </th>
                        <th style="width: 55px;" class="text-center">#ID</th>
                        <th>Subscriber Details</th>
                        <th>Account Type</th>
                        <th>Consent Status</th>
                        <th>Emailed History</th>
                        <th>Source &amp; IP</th>
                        <th>Joined At</th>
                        <th class="text-end pe-3" style="min-width: 140px;">Action</th>
                    </tr>
                </thead>
                <tbody id="subscribers-table-tbody">
                    @include('admin.email_marketing.subscribers.partials.table_rows', ['subscribers' => $subscribers])
                </tbody>
            </table>
        </div>
    </div>

    {{-- 6. Responsive Pagination Footer --}}
    <div class="d-flex justify-content-center justify-content-md-end mb-4" id="subscribers-pagination-footer" style="{{ $subscribers->hasPages() ? '' : 'display: none;' }}">
        <div id="subscribers-pagination-links">
            @if($subscribers->hasPages())
                {{ $subscribers->links('pagination::bootstrap-5') }}
            @endif
        </div>
    </div>

</div>

{{-- MODAL 1: Bulk Instant Email Composer Modal --}}
<div class="modal fade" id="bulkEmailModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom py-2.5 px-3 bg-light d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                    <i class="fas fa-paper-plane text-primary"></i> <span>Bulk Instant Email Broadcast</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkEmailForm" onsubmit="submitBulkEmailForm(event)">
                <div class="modal-body p-3">
                    <div class="alert alert-info py-2 px-3 small mb-3 border-0 shadow-xs" style="border-radius: 5px;">
                        <i class="fas fa-users me-1"></i> Selected Recipients: <strong id="bulk-modal-recipient-count" class="font-monospace">0</strong> active subscriber(s).
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Email Subject <span class="text-danger">*</span></label>
                        <input type="text" id="bulk-email-subject" class="form-control form-control-sm shadow-none" placeholder="Exclusive Offer for You | Shop Now BD" required>
                    </div>

                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold mb-0 text-dark">Email Message (HTML / Text) <span class="text-danger">*</span></label>
                            <div class="d-flex gap-1">
                                <span class="badge bg-light text-dark border cursor-pointer" onclick="insertPlaceholderToBody('{name}')" style="cursor: pointer;" title="Insert Customer Name">{name}</span>
                                <span class="badge bg-light text-dark border cursor-pointer" onclick="insertPlaceholderToBody('{email}')" style="cursor: pointer;" title="Insert Customer Email">{email}</span>
                                <span class="badge bg-light text-dark border cursor-pointer" onclick="insertPlaceholderToBody('{store_name}')" style="cursor: pointer;" title="Insert Store Name">{store_name}</span>
                            </div>
                        </div>
                        <textarea id="bulk-email-body" rows="7" class="form-control form-control-sm font-monospace shadow-none" placeholder="Hello {name},&#10;&#10;We have an exciting announcement for you..." required></textarea>
                        <small class="text-muted" style="font-size: 11px;">You can paste standard HTML code or simple plain text. Click tags above to auto-insert personalization variables.</small>
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3" id="btn-submit-bulk-email">
                        <i class="fas fa-paper-plane me-1"></i> Send Broadcast
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL 2: Direct Single Email Modal --}}
<div class="modal fade" id="directEmailModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom py-2.5 px-3 bg-light d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                    <i class="fas fa-envelope text-info"></i> <span>Send Direct Email</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="directEmailForm" onsubmit="submitDirectEmailForm(event)">
                <input type="hidden" id="direct-sub-id">
                <div class="modal-body p-3">
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-muted">To:</label>
                        <input type="text" id="direct-sub-email" class="form-control form-control-sm bg-light font-monospace shadow-none" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Subject <span class="text-danger">*</span></label>
                        <input type="text" id="direct-email-subject" class="form-control form-control-sm shadow-none" placeholder="Subject line..." required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-dark">Message <span class="text-danger">*</span></label>
                        <textarea id="direct-email-body" rows="5" class="form-control form-control-sm shadow-none" placeholder="Your personalized message..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info text-white btn-compact shadow-xs fw-bold px-3" id="btn-submit-direct-email">
                        <i class="fas fa-paper-plane me-1"></i> Send Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL 3: Add New Subscriber Modal --}}
<div class="modal fade" id="addSubscriberModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom py-2.5 px-3 bg-light d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                    <i class="fas fa-user-plus text-primary"></i> <span>Add New Subscriber</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form onsubmit="submitAddSubscriberForm(event)">
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Email Address <span class="text-danger">*</span></label>
                        <input type="email" id="add-sub-email" class="form-control form-control-sm font-monospace shadow-none" placeholder="customer@example.com" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-dark">First Name</label>
                            <input type="text" id="add-sub-fname" class="form-control form-control-sm shadow-none" placeholder="John">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-dark">Last Name</label>
                            <input type="text" id="add-sub-lname" class="form-control form-control-sm shadow-none" placeholder="Doe">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Phone Number</label>
                        <input type="text" id="add-sub-phone" class="form-control form-control-sm font-monospace shadow-none" placeholder="017XXXXXXXX">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-dark">Consent Source Tag</label>
                        <select id="add-sub-source" class="form-select form-select-sm font-monospace shadow-none">
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
                <div class="modal-footer border-top py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3" id="btn-submit-add-sub">Save Subscriber</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL 4: Dynamic Edit Subscriber Modal --}}
<div class="modal fade" id="editSubscriberModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom py-2.5 px-3 bg-light d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 13.5px;">
                    <i class="fas fa-user-edit text-primary"></i> <span>Edit Subscriber Profile</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form onsubmit="submitEditSubscriberForm(event)">
                <input type="hidden" id="edit-sub-id">
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Email Address (Read-only)</label>
                        <input type="text" id="edit-sub-email" class="form-control form-control-sm bg-light font-monospace shadow-none" readonly>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-dark">First Name</label>
                            <input type="text" id="edit-sub-fname" class="form-control form-control-sm shadow-none">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-dark">Last Name</label>
                            <input type="text" id="edit-sub-lname" class="form-control form-control-sm shadow-none">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Phone Number</label>
                        <input type="text" id="edit-sub-phone" class="form-control form-control-sm font-monospace shadow-none">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-dark">Subscription Status</label>
                        <select id="edit-sub-status" class="form-select form-select-sm font-monospace shadow-none" required>
                            <option value="subscribed">Subscribed</option>
                            <option value="unsubscribed">Unsubscribed</option>
                            <option value="bounced">Bounced</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3" id="btn-submit-edit-sub">Update Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL 5: Custom Confirmation Modal --}}
<div class="modal fade" id="customConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2 bg-danger-subtle text-danger" style="width: 44px; height: 44px; border-radius: 50% !important;">
                    <i class="fas fa-trash-alt fs-5"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="confirm-modal-title" style="font-size: 14px;">Delete Subscriber</h6>
                <p class="text-secondary small mb-3" id="confirm-modal-msg" style="font-size: 11.5px; line-height: 1.45;">Are you sure you want to proceed?</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-compact shadow-xs fw-bold px-3" id="btn-confirm-action-execute">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Toast Feedback Notification Holder --}}
<div id="custom-toast-holder"></div>

{{-- JavaScript Engine --}}
<script>
    let searchTimeout = null;
    let pendingConfirmCallback = null;

    const indexUrl       = "{{ $indexUrl }}";
    const exportUrl      = "{{ $exportUrl }}";
    const storeUrl       = "{{ $storeUrl }}";
    const bulkSendUrl    = "{{ $bulkSendUrl }}";
    const updateBaseUrl  = "{{ $updateBaseUrl }}";
    const toggleStatusUrl = "{{ $toggleStatusBaseUrl }}";
    const deleteBaseUrl  = "{{ $deleteBaseUrl }}";
    const csrfToken      = "{{ csrf_token() }}";

    // 1. Live Reload-free Fetch
    function fetchFilteredSubscribers(page = 1) {
        const form = document.getElementById('subscriber-filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.append('page', page);

        const tbody = document.getElementById('subscribers-table-tbody');
        if (tbody) tbody.style.opacity = '0.35';

        fetch(indexUrl + '?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (tbody) {
                tbody.innerHTML = data.table_html;
                tbody.style.opacity = '1';
            }

            // Pagination Handling
            const paginationFooter = document.getElementById('subscribers-pagination-footer');
            const paginationLinks  = document.getElementById('subscribers-pagination-links');
            const perPageVal       = document.getElementById('filter-per-page')?.value;

            if (paginationLinks && paginationFooter) {
                if (perPageVal === 'all' || !data.pagination_html || data.pagination_html.trim() === '') {
                    paginationLinks.innerHTML = '';
                    paginationFooter.style.display = 'none';
                } else {
                    paginationLinks.innerHTML = data.pagination_html;
                    paginationFooter.style.display = 'flex';
                }
            }

            // Stats Update
            if (data.stats) {
                if (document.getElementById('stat-active-subscribed')) document.getElementById('stat-active-subscribed').innerText = Number(data.stats.subscribed).toLocaleString();
                if (document.getElementById('stat-unsubscribed')) document.getElementById('stat-unsubscribed').innerText = Number(data.stats.unsubscribed).toLocaleString();
                if (document.getElementById('stat-registered-users')) document.getElementById('stat-registered-users').innerText = Number(data.stats.registered).toLocaleString();
                if (document.getElementById('stat-guest-users')) document.getElementById('stat-guest-users').innerText = Number(data.stats.guest).toLocaleString();
                if (document.getElementById('stat-emailed-accounts')) document.getElementById('stat-emailed-accounts').innerText = Number(data.stats.emailed).toLocaleString();
                if (document.getElementById('stat-bounced-accounts')) document.getElementById('stat-bounced-accounts').innerText = Number(data.stats.bounced).toLocaleString();
                if (document.getElementById('subscribers-total-badge')) document.getElementById('subscribers-total-badge').innerText = Number(data.stats.total).toLocaleString() + ' Total';
            }

            if (data.entries_text && document.getElementById('showing-entries-text')) {
                document.getElementById('showing-entries-text').innerText = data.entries_text;
            }

            const checkAll = document.getElementById('check-all-subscribers');
            if (checkAll) checkAll.checked = false;
            updateSelectionBadge();
        })
        .catch(err => {
            console.error('Fetch error:', err);
            if (tbody) tbody.style.opacity = '1';
        });
    }

    // 2. Search Debounce & Change Triggers
    document.getElementById('filter-search-input')?.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => fetchFilteredSubscribers(1), 350);
    });

    document.querySelectorAll('.filter-trigger').forEach(el => {
        el.addEventListener('change', () => fetchFilteredSubscribers(1));
    });

    // 3. Date Presets Switcher
    document.getElementById('filter-date-select')?.addEventListener('change', function() {
        const val = this.value;
        const singleBox  = document.getElementById('singleDateWrapper');
        const monthBox   = document.getElementById('specificMonthWrapper');
        const yearBox    = document.getElementById('specificYearWrapper');
        const rangeBox   = document.getElementById('customRangeWrapper');

        if (singleBox) singleBox.classList.add('d-none');
        if (monthBox)  monthBox.classList.add('d-none');
        if (yearBox)   yearBox.classList.add('d-none');
        if (rangeBox)  rangeBox.classList.add('d-none');

        if (val === 'specific_date' && singleBox) {
            singleBox.classList.remove('d-none');
        } else if (val === 'specific_month' && monthBox) {
            monthBox.classList.remove('d-none');
        } else if (val === 'specific_year' && yearBox) {
            yearBox.classList.remove('d-none');
        } else if (val === 'custom_range' && rangeBox) {
            rangeBox.classList.remove('d-none');
        } else {
            fetchFilteredSubscribers(1);
        }
    });

    function resetDatePanels() {
        if (document.getElementById('filter-specific-date')) document.getElementById('filter-specific-date').value = '';
        if (document.getElementById('filter-specific-month')) document.getElementById('filter-specific-month').value = '';
        if (document.getElementById('filter-specific-year')) document.getElementById('filter-specific-year').value = '';
        if (document.getElementById('filter-start-date')) document.getElementById('filter-start-date').value = '';
        if (document.getElementById('filter-end-date')) document.getElementById('filter-end-date').value = '';
        if (document.getElementById('filter-date-select')) document.getElementById('filter-date-select').value = '';

        document.getElementById('singleDateWrapper')?.classList.add('d-none');
        document.getElementById('specificMonthWrapper')?.classList.add('d-none');
        document.getElementById('specificYearWrapper')?.classList.add('d-none');
        document.getElementById('customRangeWrapper')?.classList.add('d-none');

        fetchFilteredSubscribers(1);
    }

    function resetAllFilters() {
        document.getElementById('subscriber-filter-form')?.reset();
        resetDatePanels();
    }

    // 4. Pagination Link Click Interceptor
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#subscribers-pagination-links .page-link');
        if (pageLink) {
            e.preventDefault();
            const href = pageLink.getAttribute('href');
            if (href && href !== '#' && !href.startsWith('javascript:')) {
                const url = new URL(href);
                const page = url.searchParams.get('page') || 1;
                fetchFilteredSubscribers(page);
                document.querySelector('.subscribers-table-container')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    });

    // 5. Checkbox Selection & Badge Sync
    document.getElementById('check-all-subscribers')?.addEventListener('change', function() {
        document.querySelectorAll('.subscriber-select-cb').forEach(cb => cb.checked = this.checked);
        updateSelectionBadge();
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('subscriber-select-cb')) {
            updateSelectionBadge();
        }
    });

    function getSelectedSubscriberIds() {
        return Array.from(document.querySelectorAll('.subscriber-select-cb:checked')).map(cb => cb.value);
    }

    function updateSelectionBadge() {
        const selected = getSelectedSubscriberIds();
        const badge = document.getElementById('selected-subscribers-badge');
        if (badge) {
            if (selected.length > 0) {
                badge.innerText = `${selected.length} selected`;
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }
        }
    }

    // 6. CSV Exporter (Exports with Current Active Filters)
    function exportSubscribersCsv() {
        const form = document.getElementById('subscriber-filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        const url = exportUrl + '?' + params.toString();
        window.open(url, '_blank');
    }

    // 7. Bulk Email Composer
    function openBulkEmailModal() {
        const selected = getSelectedSubscriberIds();
        if (selected.length === 0) {
            showToast('Warning', 'Please select at least one subscriber from the table checkbox.', 'warning');
            return;
        }

        document.getElementById('bulk-modal-recipient-count').innerText = selected.length;
        const modalEl = document.getElementById('bulkEmailModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function insertPlaceholderToBody(tag) {
        const textarea = document.getElementById('bulk-email-body');
        if (textarea) {
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            textarea.value = text.substring(0, start) + tag + text.substring(end);
            textarea.focus();
            textarea.selectionStart = textarea.selectionEnd = start + tag.length;
        }
    }

    function submitBulkEmailForm(e) {
        e.preventDefault();
        const selected = getSelectedSubscriberIds();
        const subject = document.getElementById('bulk-email-subject')?.value.trim();
        const message = document.getElementById('bulk-email-body')?.value.trim();
        const btn = document.getElementById('btn-submit-bulk-email');

        if (!subject || !message) {
            showToast('Warning', 'Subject and message are required.', 'warning');
            return;
        }

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Broadcasting...';
        }

        fetch(bulkSendUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                subscriber_ids: selected,
                subject: subject,
                message: message
            })
        })
        .then(res => res.json())
        .then(data => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Broadcast';
            }
            if (data.success) {
                const modalEl = document.getElementById('bulkEmailModal');
                if (modalEl && window.bootstrap) bootstrap.Modal.getInstance(modalEl)?.hide();
                document.getElementById('bulkEmailForm')?.reset();
                showToast('Success', data.message, 'success');
                fetchFilteredSubscribers(1);
            } else {
                showToast('Error', data.message || 'Failed to dispatch emails', 'danger');
            }
        })
        .catch(err => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Broadcast';
            }
            showToast('Error', 'Network or server error while dispatching bulk emails.', 'danger');
        });
    }

    // 8. Direct Single Email Modal & Submission
    function openDirectEmailModal(id, email, name) {
        document.getElementById('direct-sub-id').value = id;
        document.getElementById('direct-sub-email').value = `${name} <${email}>`;
        document.getElementById('direct-email-subject').value = '';
        document.getElementById('direct-email-body').value = '';
        const modalEl = document.getElementById('directEmailModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function submitDirectEmailForm(e) {
        e.preventDefault();
        const subId   = document.getElementById('direct-sub-id')?.value;
        const subject = document.getElementById('direct-email-subject')?.value.trim();
        const message = document.getElementById('direct-email-body')?.value.trim();
        const btn     = document.getElementById('btn-submit-direct-email');

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sending...';
        }

        fetch(bulkSendUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                subscriber_ids: [subId],
                subject: subject,
                message: message
            })
        })
        .then(res => res.json())
        .then(data => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Now';
            }
            if (data.success) {
                const modalEl = document.getElementById('directEmailModal');
                if (modalEl && window.bootstrap) bootstrap.Modal.getInstance(modalEl)?.hide();
                showToast('Success', 'Direct email dispatched successfully.', 'success');
                fetchFilteredSubscribers(1);
            } else {
                showToast('Error', data.message || 'Failed to send', 'danger');
            }
        })
        .catch(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Now';
            }
            showToast('Error', 'Failed to dispatch email.', 'danger');
        });
    }

    // 9. Add Subscriber AJAX
    function submitAddSubscriberForm(e) {
        e.preventDefault();
        const email  = document.getElementById('add-sub-email')?.value.trim();
        const fname  = document.getElementById('add-sub-fname')?.value.trim();
        const lname  = document.getElementById('add-sub-lname')?.value.trim();
        const phone  = document.getElementById('add-sub-phone')?.value.trim();
        const source = document.getElementById('add-sub-source')?.value;
        const btn    = document.getElementById('btn-submit-add-sub');

        if (btn) btn.disabled = true;

        fetch(storeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                email: email,
                first_name: fname,
                last_name: lname,
                phone: phone,
                consent_source: source
            })
        })
        .then(res => res.json())
        .then(data => {
            if (btn) btn.disabled = false;
            if (data.success) {
                const modalEl = document.getElementById('addSubscriberModal');
                if (modalEl && window.bootstrap) bootstrap.Modal.getInstance(modalEl)?.hide();
                if (document.getElementById('add-sub-email')) document.getElementById('add-sub-email').value = '';
                if (document.getElementById('add-sub-fname')) document.getElementById('add-sub-fname').value = '';
                if (document.getElementById('add-sub-lname')) document.getElementById('add-sub-lname').value = '';
                if (document.getElementById('add-sub-phone')) document.getElementById('add-sub-phone').value = '';
                showToast('Success', data.message, 'success');
                fetchFilteredSubscribers(1);
            } else {
                showToast('Error', data.message || 'Could not add subscriber', 'danger');
            }
        })
        .catch(err => {
            if (btn) btn.disabled = false;
            showToast('Error', 'Email address might already exist.', 'danger');
        });
    }

    // 10. Edit Subscriber Dynamic Modal
    function openEditSubscriberModal(sub) {
        document.getElementById('edit-sub-id').value = sub.id;
        document.getElementById('edit-sub-email').value = sub.email;
        document.getElementById('edit-sub-fname').value = sub.first_name || '';
        document.getElementById('edit-sub-lname').value = sub.last_name || '';
        document.getElementById('edit-sub-phone').value = sub.phone || '';
        document.getElementById('edit-sub-status').value = sub.status || 'subscribed';
        const modalEl = document.getElementById('editSubscriberModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function submitEditSubscriberForm(e) {
        e.preventDefault();
        const id     = document.getElementById('edit-sub-id')?.value;
        const fname  = document.getElementById('edit-sub-fname')?.value.trim();
        const lname  = document.getElementById('edit-sub-lname')?.value.trim();
        const phone  = document.getElementById('edit-sub-phone')?.value.trim();
        const status = document.getElementById('edit-sub-status')?.value;
        const btn    = document.getElementById('btn-submit-edit-sub');

        if (btn) btn.disabled = true;

        fetch(`${updateBaseUrl}/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                first_name: fname,
                last_name: lname,
                phone: phone,
                status: status
            })
        })
        .then(res => res.json())
        .then(data => {
            if (btn) btn.disabled = false;
            if (data.success) {
                const modalEl = document.getElementById('editSubscriberModal');
                if (modalEl && window.bootstrap) bootstrap.Modal.getInstance(modalEl)?.hide();
                showToast('Success', data.message, 'success');
                fetchFilteredSubscribers(1);
            } else {
                showToast('Error', data.message || 'Update failed', 'danger');
            }
        })
        .catch(() => {
            if (btn) btn.disabled = false;
            showToast('Error', 'Update failed.', 'danger');
        });
    }

    // 11. Toggle Subscription Status Ajax
    function toggleSubscriberStatusAjax(id) {
        fetch(`${toggleStatusUrl}/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            showToast('Updated', `Subscriber is now ${data.status}.`, 'info');
            fetchFilteredSubscribers(1);
        })
        .catch(() => {
            fetchFilteredSubscribers(1);
        });
    }

    // 12. Delete Subscriber Confirmation
    function deleteSubscriberAjax(id, email) {
        document.getElementById('confirm-modal-title').innerText = 'Delete Subscriber';
        document.getElementById('confirm-modal-msg').innerHTML = `Are you sure you want to delete <strong>${email}</strong>?`;

        pendingConfirmCallback = function() {
            fetch(`${deleteBaseUrl}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                const modalEl = document.getElementById('customConfirmModal');
                if (modalEl && window.bootstrap) bootstrap.Modal.getInstance(modalEl)?.hide();
                showToast('Deleted', data.message, 'danger');
                fetchFilteredSubscribers(1);
            })
            .catch(() => {
                const modalEl = document.getElementById('customConfirmModal');
                if (modalEl && window.bootstrap) bootstrap.Modal.getInstance(modalEl)?.hide();
                fetchFilteredSubscribers(1);
            });
        };

        const modalEl = document.getElementById('customConfirmModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    document.getElementById('btn-confirm-action-execute')?.addEventListener('click', function() {
        if (typeof pendingConfirmCallback === 'function') {
            pendingConfirmCallback();
        }
    });

    // 13. Floating Toast Notifications
    function showToast(title, message, type = 'success') {
        const holder = document.getElementById('custom-toast-holder');
        if (!holder) return;

        const toastId = 'toast-' + Date.now();
        const typeConfigs = {
            success: { icon: 'fa-check-circle', color: 'bg-success text-white' },
            danger:  { icon: 'fa-trash-alt',    color: 'bg-danger text-white' },
            warning: { icon: 'fa-exclamation-triangle', color: 'bg-warning text-dark' },
            info:    { icon: 'fa-info-circle',   color: 'bg-primary text-white' }
        };
        const config = typeConfigs[type] || typeConfigs.success;

        const html = `
            <div id="${toastId}" class="admin-subscriber-toast ${config.color} p-3 d-flex align-items-center justify-content-between shadow-lg" style="border-radius: 5px !important;">
                <div class="d-flex align-items-center gap-3">
                    <i class="fas ${config.icon} fa-lg"></i>
                    <div>
                        <strong class="d-block" style="font-size: 13px;">${title}</strong>
                        <small style="opacity: 0.95; font-size: 11.5px;">${message}</small>
                    </div>
                </div>
                <button type="button" class="btn-close ${type === 'warning' ? '' : 'btn-close-white'} ms-3 shadow-none" onclick="document.getElementById('${toastId}')?.remove()"></button>
            </div>
        `;

        holder.insertAdjacentHTML('beforeend', html);
        setTimeout(() => {
            const el = document.getElementById(toastId);
            if (el) el.remove();
        }, 3500);
    }
</script>
@endsection