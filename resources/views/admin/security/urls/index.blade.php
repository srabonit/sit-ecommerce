@extends('layouts.admin')

@section('title', 'URL Attack & Request Monitoring - ' . config('app.name'))

@section('admin_content')
<!-- Font Awesome 6.5.1 CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Google Fonts: Plus Jakarta Sans & Inter -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* =========================================================
       URL ATTACK MONITORING — KAIADMIN CLASSIC / WHITE THEME
       ========================================================= */

    #security-url-attacks-wrapper,
    .url-modal-scope {
        --url-primary: var(--bs-primary, #1572e8);
        --url-primary-soft: rgba(21, 114, 232, .08);
        --url-danger: var(--bs-danger, #f25961);
        --url-warning: var(--bs-warning, #ffad46);
        --url-success: var(--bs-success, #31ce36);
        --url-border: #e5e7eb;
        --url-border-light: #eef0f2;
        --url-surface: #ffffff;
        --url-page: #f5f7fb;
        --url-heading: #1f2937;
        --url-text: #374151;
        --url-muted: #7b8494;

        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        letter-spacing: -0.01em;
        color: var(--url-text);
    }

    /* FontAwesome Icon Protection */
    #security-url-attacks-wrapper i,
    #security-url-attacks-wrapper .fa,
    #security-url-attacks-wrapper .fas,
    #security-url-attacks-wrapper .far,
    #security-url-attacks-wrapper .fab,
    #security-url-attacks-wrapper .fa-solid,
    #security-url-attacks-wrapper .fa-regular,
    .url-modal-scope i,
    .url-modal-scope .fa,
    .url-modal-scope .fa-solid {
        font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome" !important;
        font-style: normal;
        font-weight: 900;
        line-height: 1;
        letter-spacing: normal !important;
        display: inline-block;
    }

    #security-url-attacks-wrapper .far,
    .url-modal-scope .far {
        font-weight: 400 !important;
    }

    #security-url-attacks-wrapper {
        background-color: var(--url-page);
        min-height: 100vh;
        color: var(--url-text);
    }

    #security-url-attacks-wrapper *,
    .url-modal-scope * {
        box-sizing: border-box;
    }

    /* Strict 5px Border Radius Rule */
    #security-url-attacks-wrapper .sec-card,
    #security-url-attacks-wrapper .metric-card,
    #security-url-attacks-wrapper .btn,
    #security-url-attacks-wrapper .btn-compact,
    #security-url-attacks-wrapper .sec-action-btn,
    #security-url-attacks-wrapper .sec-header-icon-box,
    #security-url-attacks-wrapper .metric-icon-square,
    #security-url-attacks-wrapper .form-control,
    #security-url-attacks-wrapper .form-select,
    #security-url-attacks-wrapper .input-group-text,
    #security-url-attacks-wrapper .badge,
    #security-url-attacks-wrapper .alert,
    #security-url-attacks-wrapper .rank-card,
    #security-url-attacks-wrapper .page-link,
    .url-modal-scope .modal-content,
    .url-modal-scope .btn,
    .url-modal-scope .form-control,
    .url-modal-scope .form-select,
    .url-modal-scope .input-group-text,
    .url-modal-scope .badge,
    .url-modal-scope .alert {
        border-radius: 5px !important;
    }

    /* Unified Card Surface */
    #security-url-attacks-wrapper .sec-card {
        background: var(--url-surface);
        border: 1px solid var(--url-border);
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.035);
        transition: box-shadow 0.15s ease, border-color 0.15s ease;
    }

    #security-url-attacks-wrapper .sec-card:hover {
        border-color: #d5dbe5;
        box-shadow: 0 3px 10px rgba(15, 23, 42, 0.045);
    }

    /* Tabular Numeric Data Alignment */
    #security-url-attacks-wrapper .font-tabular {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
    }

    /* Header Icon */
    #security-url-attacks-wrapper .sec-header-icon-box {
        width: 36px !important;
        height: 36px !important;
        min-width: 36px !important;
        background-color: #fef2f2 !important;
        border: 1px solid #fecaca !important;
        color: #dc2626 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
    }

    #security-url-attacks-wrapper .sec-header-icon-box i {
        font-size: 15px !important;
    }

    /* Page Title */
    #security-url-attacks-wrapper .sec-page-title {
        font-size: 17px !important;
        font-weight: 700 !important;
        color: var(--url-heading) !important;
        line-height: 1.25 !important;
        margin: 0 !important;
    }

    /* Metric Cards */
    #security-url-attacks-wrapper .metric-card {
        padding: 12px 14px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        background: var(--url-surface);
        border: 1px solid var(--url-border);
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.035);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    #security-url-attacks-wrapper .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }

    #security-url-attacks-wrapper .stat-label-title {
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: var(--url-muted);
        line-height: 1.3;
    }

    #security-url-attacks-wrapper .stat-val-num {
        font-size: clamp(1.15rem, 1.8vw, 1.4rem);
        font-weight: 800;
        line-height: 1.25;
        letter-spacing: -0.02em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: var(--url-heading);
    }

    #security-url-attacks-wrapper .stat-sub-text {
        font-size: 11px;
        font-weight: 600;
        margin-top: 2px;
    }

    #security-url-attacks-wrapper .metric-icon-square {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    /* Soft Pastel Icon Theme */
    #security-url-attacks-wrapper .icon-soft-red {
        background-color: #fef2f2 !important;
        border: 1px solid #fecaca !important;
        color: #dc2626 !important;
    }

    #security-url-attacks-wrapper .icon-soft-amber {
        background-color: #fffbeb !important;
        border: 1px solid #fef3c7 !important;
        color: #d97706 !important;
    }

    /* Compact Buttons */
    #security-url-attacks-wrapper .btn-compact {
        font-size: 12px !important;
        padding: 6px 14px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    #security-url-attacks-wrapper .btn-compact:hover {
        transform: translateY(-1px);
    }

    /* Fixed Action Buttons */
    #security-url-attacks-wrapper .sec-action-btn {
        width: 30px !important;
        height: 30px !important;
        min-width: 30px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0 !important;
        font-size: 11.5px !important;
        transition: all 0.15s ease !important;
        cursor: pointer;
    }

    /* Form Controls */
    #security-url-attacks-wrapper .form-control,
    #security-url-attacks-wrapper .form-select,
    .url-modal-scope .form-control,
    .url-modal-scope .form-select {
        min-height: 34px;
        font-size: 12px;
        font-weight: 500;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    #security-url-attacks-wrapper .form-control:focus,
    #security-url-attacks-wrapper .form-select:focus,
    .url-modal-scope .form-control:focus,
    .url-modal-scope .form-select:focus {
        border-color: var(--url-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, 0.11);
        outline: none;
    }

    #security-url-attacks-wrapper .input-group-text {
        min-height: 34px;
        font-size: 12px;
        border-color: #cbd5e1;
    }

    #security-url-attacks-wrapper .form-label,
    .url-modal-scope .form-label {
        color: #586174;
    }

    /* Unified Data Table */
    #security-url-attacks-wrapper .sec-table-container {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #security-url-attacks-wrapper .sec-table {
        margin-bottom: 0;
        width: 100%;
        border-collapse: collapse;
    }

    #security-url-attacks-wrapper .sec-table thead th {
        font-size: 11px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        font-weight: 700 !important;
        color: #475569 !important;
        background-color: #f8fafc !important;
        border-bottom: 1.5px solid var(--url-border) !important;
        padding: 10px 12px !important;
        white-space: nowrap !important;
    }

    #security-url-attacks-wrapper .sec-table tbody td {
        padding: 10px 12px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        vertical-align: middle !important;
        font-size: 12px !important;
        white-space: nowrap !important;
        color: #1e293b;
    }

    #security-url-attacks-wrapper .sec-table tbody tr:last-child td {
        border-bottom: 0 !important;
    }

    #security-url-attacks-wrapper .sec-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    /* Table Scrollbar */
    #security-url-attacks-wrapper .sec-table-container::-webkit-scrollbar {
        height: 5px;
    }

    #security-url-attacks-wrapper .sec-table-container::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    #security-url-attacks-wrapper .sec-table-container::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 5px;
    }

    /* Ranking Cards */
    #security-url-attacks-wrapper .rank-card {
        border-color: var(--url-border) !important;
        background: #fbfcfe !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        min-width: 0;
    }

    #security-url-attacks-wrapper .rank-card:hover {
        border-color: #cbd5e1 !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.045);
    }

    /* Pagination */
    #security-url-attacks-wrapper #url-pagination-container .pagination {
        margin-bottom: 0;
        flex-wrap: wrap;
        gap: 3px;
    }

    #security-url-attacks-wrapper #url-pagination-container .page-link {
        min-width: 30px;
        min-height: 30px;
        padding: 5px 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        color: #475569;
        border-color: #e2e8f0;
        box-shadow: none;
    }

    #security-url-attacks-wrapper #url-pagination-container .page-item.active .page-link {
        background: var(--url-primary);
        border-color: var(--url-primary);
        color: #fff;
    }

    #security-url-attacks-wrapper #url-pagination-container .page-item.disabled .page-link {
        color: #a1aab8;
        background: #f8fafc;
    }

    /* Modal Styling */
    .url-modal-scope .modal-content {
        border: 1px solid var(--url-border) !important;
        box-shadow: 0 12px 35px rgba(15, 23, 42, 0.15) !important;
        overflow: hidden;
    }

    .url-modal-scope .modal-header {
        background: #f8fafc;
        border-bottom: 1px solid var(--url-border-light);
    }

    .url-modal-scope .modal-title {
        font-size: 13px;
    }

    .url-modal-scope .modal-body {
        color: var(--url-text);
    }

    .url-modal-scope .modal-footer {
        background: #fbfcfe;
        border-top: 1px solid var(--url-border-light);
    }

    .url-modal-scope .btn-close {
        font-size: 10px;
    }

    /* Prevent long endpoint text from breaking layout */
    #security-url-attacks-wrapper .font-monospace {
        overflow-wrap: anywhere;
    }

    /* Loading / Fetch State */
    #security-url-attacks-wrapper #url-table-body {
        transition: opacity 0.15s ease;
    }

    /* Header action alignment */
    #security-url-attacks-wrapper .header-action-btns-wrap {
        flex-shrink: 0;
    }

    /* Mobile Responsive */
    @media (max-width: 767.98px) {
        #security-url-attacks-wrapper {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }

        #security-url-attacks-wrapper .header-action-btns-wrap {
            width: 100%;
        }

        #security-url-attacks-wrapper .metric-card {
            padding: 10px 12px !important;
        }

        #security-url-attacks-wrapper .sec-page-title {
            font-size: 15px !important;
        }

        #security-url-attacks-wrapper .sec-table thead th,
        #security-url-attacks-wrapper .sec-table tbody td {
            padding: 8px 10px !important;
        }

        #security-url-attacks-wrapper .card-footer {
            align-items: flex-start !important;
        }

        #security-url-attacks-wrapper #url-pagination-container {
            width: 100%;
            overflow-x: auto;
        }

        #security-url-attacks-wrapper #url-pagination-container .pagination {
            justify-content: flex-start;
            flex-wrap: nowrap;
            width: max-content;
        }

        .url-modal-scope .modal-dialog {
            margin: 12px;
        }
    }

    @media (max-width: 575.98px) {
        #security-url-attacks-wrapper .sec-header-icon-box {
            width: 32px !important;
            height: 32px !important;
            min-width: 32px !important;
        }

        #security-url-attacks-wrapper .sec-page-title {
            font-size: 14px !important;
        }

        #security-url-attacks-wrapper .header-action-btns-wrap .btn {
            min-height: 34px;
            padding-left: 8px !important;
            padding-right: 8px !important;
            font-size: 11px !important;
        }

        #security-url-attacks-wrapper .metric-card {
            min-height: 100px;
        }

        #security-url-attacks-wrapper .stat-val-num {
            font-size: 1.2rem;
        }
    }

    /* Tablet */
    @media (min-width: 768px) and (max-width: 991.98px) {
        #security-url-attacks-wrapper .sec-table thead th,
        #security-url-attacks-wrapper .sec-table tbody td {
            padding-left: 9px !important;
            padding-right: 9px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-3 py-2" id="security-url-attacks-wrapper">
    <div class="mx-auto" style="max-width: 1600px;">

        <!-- ================= 1. HEADER & ACTION TOOLBAR ================= -->
        <header class="sec-card p-3 mb-3">
            <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2">

                <div class="d-flex align-items-center flex-wrap gap-2">
                    <span class="sec-header-icon-box shadow-xs">
                        <i class="fas fa-link-slash"></i>
                    </span>

                    <h4 class="sec-page-title text-nowrap">URL Attack Monitoring</h4>

                    <span class="text-secondary small d-none d-md-inline">•</span>
                    <span class="text-secondary small mb-0 text-nowrap">Real-time threat tracking</span>
                </div>

                <div class="d-flex align-items-center gap-2 w-100 w-sm-auto justify-content-end header-action-btns-wrap">
                    <a href="{{ route('admin.security.urls.export') }}"
                       class="btn btn-outline-secondary btn-compact flex-fill flex-sm-grow-0 d-inline-flex align-items-center justify-content-center gap-1 shadow-xs bg-white text-dark"
                       id="btn-export-urls-csv">
                        <i class="fas fa-file-csv text-success"></i>
                        <span>Export CSV</span>
                    </a>

                    <button type="button"
                            class="btn btn-outline-secondary btn-compact flex-fill flex-sm-grow-0 d-inline-flex align-items-center justify-content-center gap-1 shadow-xs bg-white text-dark"
                            id="btn-refresh-urls"
                            title="Refresh Table">
                        <i class="fas fa-rotate text-primary" id="urls-refresh-icon"></i>
                        <span>Refresh</span>
                    </button>
                </div>

            </div>
        </header>

        <!-- ================= 2. SUMMARY METRIC CARDS ================= -->
        <div class="row g-2 mb-3">

            <!-- Targeted Endpoints -->
            <div class="col-12 col-md-4">
                <div class="metric-card">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="stat-label-title text-truncate">Targeted Endpoints</span>
                        <div class="metric-icon-square icon-soft-red shadow-xs">
                            <i class="fas fa-crosshairs"></i>
                        </div>
                    </div>

                    <div class="min-w-0">
                        <h4 class="stat-val-num font-tabular my-0 text-dark" id="stat-endpoints">
                            {{ number_format($stats['total_attacked_endpoints'] ?? 0) }}
                        </h4>

                        <small class="text-danger stat-sub-text d-block text-truncate">
                            <i class="fas fa-triangle-exclamation me-1"></i>Distinct routes targeted
                        </small>
                    </div>
                </div>
            </div>

            <!-- Blocked Hits -->
            <div class="col-12 col-md-4">
                <div class="metric-card">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="stat-label-title text-truncate">Blocked Hits</span>
                        <div class="metric-icon-square icon-soft-red shadow-xs">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                    </div>

                    <div class="min-w-0">
                        <h4 class="stat-val-num font-tabular my-0 text-danger" id="stat-blocked-hits">
                            {{ number_format($stats['total_blocked_hits'] ?? 0) }}
                        </h4>

                        <small class="text-danger stat-sub-text d-block text-truncate">
                            <i class="fas fa-ban me-1"></i>Intercepted by firewall
                        </small>
                    </div>
                </div>
            </div>

            <!-- Scanner Failed Hits -->
            <div class="col-12 col-md-4">
                <div class="metric-card">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="stat-label-title text-truncate">Scanner Failed (4xx)</span>
                        <div class="metric-icon-square icon-soft-amber shadow-xs">
                            <i class="fas fa-triangle-exclamation"></i>
                        </div>
                    </div>

                    <div class="min-w-0">
                        <h4 class="stat-val-num font-tabular my-0 text-warning" id="stat-failed-hits">
                            {{ number_format($stats['total_failed_hits'] ?? 0) }}
                        </h4>

                        <small class="text-warning-emphasis stat-sub-text d-block text-truncate">
                            <i class="fas fa-triangle-exclamation me-1"></i>Probes resulting in 404/400
                        </small>
                    </div>
                </div>
            </div>

        </div>

        <!-- ================= 3. TOP ATTACKED ROUTES ================= -->
        @if($topRanked->isNotEmpty())
            <div class="sec-card mb-3 p-0 overflow-hidden">

                <div class="py-2 px-3 bg-white border-bottom d-flex align-items-center justify-content-between gap-2">
                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 13px;">
                        <i class="fas fa-fire text-danger me-2"></i>
                        Highest Frequency Targeted Routes
                    </h6>

                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 font-tabular"
                          style="font-size: 10px;">
                        Top Threats
                    </span>
                </div>

                <div class="p-2 p-md-3">
                    <div class="row g-2">

                        @foreach($topRanked as $ranked)
                            <div class="col-12 col-md-4 col-lg">
                                <div class="border border-light-subtle p-2 bg-light bg-opacity-50 h-100 rank-card">

                                    <div class="d-flex align-items-center justify-content-between mb-1 gap-2">
                                        <span class="badge bg-light text-secondary border font-tabular px-2 py-1"
                                              style="font-size: 9.5px;">
                                            {{ $ranked->request_method }}
                                        </span>

                                        <span class="badge bg-danger-subtle text-danger px-2 py-1 font-tabular"
                                              style="font-size: 9.5px;">
                                            Critical
                                        </span>
                                    </div>

                                    <div class="fw-bold text-dark text-truncate small font-monospace"
                                         style="font-size: 11.5px;"
                                         title="{{ $ranked->url_path }}">
                                        {{ $ranked->url_path }}
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between mt-2 pt-1 border-top"
                                         style="font-size: 11px;">
                                        <span class="text-muted">Total Hits:</span>
                                        <span class="fw-bold text-danger font-tabular">
                                            {{ number_format($ranked->total_requests) }}
                                        </span>
                                    </div>

                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
        @endif

        <!-- ================= 4. FILTER CONTROLS ================= -->

        <!-- Mobile Search & Filter Toolbar -->
        <div class="sec-card p-2 mb-3 d-md-none">
            <div class="d-flex align-items-center gap-2">

                <div class="input-group input-group-sm flex-grow-1">
                    <span class="input-group-text bg-light text-muted border-end-0">
                        <i class="fas fa-search" style="font-size: 10px;"></i>
                    </span>

                    <input type="text"
                           class="form-control border-start-0 font-monospace"
                           id="mobile_filter_url_search"
                           placeholder="Search URL path..."
                           value="{{ request('search') }}"
                           oninput="syncMobileUrlSearch(this.value)">
                </div>

                <button type="button"
                        class="btn btn-outline-secondary btn-compact shadow-xs bg-white text-dark flex-shrink-0"
                        data-bs-toggle="modal"
                        data-bs-target="#modalMobileUrlFilters">
                    <i class="fas fa-sliders me-1 text-primary"></i>
                    <span>Filters</span>

                    <span class="badge bg-primary text-white px-2 py-1 ms-1 d-none"
                          id="mobileActiveUrlFilterBadge"
                          style="font-size: 9px;">0</span>
                </button>

            </div>
        </div>

        <!-- Desktop Filter Form -->
        <section class="sec-card p-3 mb-3 d-none d-md-block">
            <form id="urlFilterForm" class="row g-2 align-items-end">

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary mb-1" style="font-size: 11px;">
                        Search URL Path
                    </label>

                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted border-end-0">
                            <i class="fas fa-search" style="font-size: 10px;"></i>
                        </span>

                        <input type="text"
                               class="form-control border-start-0 font-monospace"
                               id="filter_url_search"
                               name="search"
                               placeholder="/wp-admin, .env, /checkout..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary mb-1" style="font-size: 11px;">
                        HTTP Method
                    </label>

                    <select class="form-select form-select-sm" id="filter_url_method" name="method">
                        <option value="">All Methods</option>
                        <option value="GET" {{ request('method') === 'GET' ? 'selected' : '' }}>GET</option>
                        <option value="POST" {{ request('method') === 'POST' ? 'selected' : '' }}>POST</option>
                        <option value="PUT" {{ request('method') === 'PUT' ? 'selected' : '' }}>PUT</option>
                        <option value="DELETE" {{ request('method') === 'DELETE' ? 'selected' : '' }}>DELETE</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary mb-1" style="font-size: 11px;">
                        Attack Category
                    </label>

                    <select class="form-select form-select-sm" id="filter_type" name="filter_type">
                        <option value="">All Categories</option>
                        <option value="high_failures" {{ request('filter_type') === 'high_failures' ? 'selected' : '' }}>
                            High Failures (&ge; 10)
                        </option>
                        <option value="blocked_only" {{ request('filter_type') === 'blocked_only' ? 'selected' : '' }}>
                            Blocked Only
                        </option>
                        <option value="multi_ip" {{ request('filter_type') === 'multi_ip' ? 'selected' : '' }}>
                            Multi-IP (&ge; 3 IPs)
                        </option>
                        <option value="with_payload" {{ request('filter_type') === 'with_payload' ? 'selected' : '' }}>
                            Has Payload
                        </option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-bold text-secondary mb-1" style="font-size: 11px;">
                        Sort By
                    </label>

                    <select class="form-select form-select-sm" id="filter_sort_by" name="sort_by">
                        <option value="total_requests" {{ request('sort_by') === 'total_requests' ? 'selected' : '' }}>Total Hits</option>
                        <option value="failed_requests" {{ request('sort_by') === 'failed_requests' ? 'selected' : '' }}>Failed Hits</option>
                        <option value="blocked_requests" {{ request('sort_by') === 'blocked_requests' ? 'selected' : '' }}>Blocked Hits</option>
                        <option value="unique_ips_count" {{ request('sort_by') === 'unique_ips_count' ? 'selected' : '' }}>Unique Attacker IPs</option>
                        <option value="last_attack_at" {{ request('sort_by') === 'last_attack_at' ? 'selected' : '' }}>Most Recent Attack</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <label class="form-label small fw-bold text-secondary mb-1" style="font-size: 11px;">
                        Show
                    </label>

                    <select class="form-select form-select-sm font-tabular" id="filter_url_per_page" name="per_page">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-1">
                    <button type="button"
                            class="btn btn-outline-secondary btn-compact w-100 shadow-xs"
                            id="btn-reset-url-filters"
                            style="height: 34px;">
                        <i class="fas fa-rotate-left me-1"></i>
                        <span>Reset</span>
                    </button>

                    <button type="submit"
                            class="btn btn-primary btn-compact w-100 shadow-xs fw-bold"
                            style="height: 34px;">
                        <i class="fas fa-filter me-1"></i>
                        <span>Filter</span>
                    </button>
                </div>

            </form>
        </section>

        <!-- ================= 5. MAIN DATA TABLE ================= -->
        <section class="sec-card overflow-hidden mb-3">

            <div class="sec-table-container">
                <table class="table sec-table align-middle mb-0 text-nowrap" id="main-urls-table">

                    <thead>
                        <tr>
                            <th class="ps-3" style="min-width: 260px;">Target Endpoint &amp; Method</th>
                            <th class="text-center" style="width: 90px;">Total Hits</th>
                            <th class="text-center" style="width: 100px;">Failed (4xx)</th>
                            <th class="text-center" style="width: 100px;">Blocked (403)</th>
                            <th class="text-center" style="width: 110px;">Attacker IPs</th>
                            <th style="min-width: 140px;">Last Attack Detected</th>
                            <th class="pe-3 text-end" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>

                    <tbody id="url-table-body">
                        @include('admin.security.urls.partials.url_table_rows', ['urlAttacks' => $urlAttacks])
                    </tbody>

                </table>
            </div>

            <!-- Footer Pagination -->
            <div class="card-footer bg-white border-top border-light-subtle py-2 px-3 d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2">

                <span class="small text-secondary" id="url-showing-text" style="font-size: 11.5px;">
                    Showing
                    <strong>{{ $urlAttacks->firstItem() ?? 0 }}</strong>
                    to
                    <strong>{{ $urlAttacks->lastItem() ?? 0 }}</strong>
                    of
                    <strong class="font-tabular">{{ number_format($urlAttacks->total()) }}</strong>
                    routes
                </span>

                <div id="url-pagination-container">
                    {{ $urlAttacks->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </section>

    </div>
</div>

<!-- ================= 6. MODAL: MOBILE FILTERING ================= -->
<div class="modal fade url-modal-scope" id="modalMobileUrlFilters" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border border-light-subtle shadow">

            <div class="modal-header py-2 px-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2 mb-0" style="font-size: 13.5px;">
                    <i class="fas fa-sliders text-primary"></i>
                    Filter Routes
                </h6>

                <button type="button"
                        class="btn-close shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <div class="modal-body p-3">

                <div class="mb-2">
                    <label class="form-label small fw-bold text-secondary mb-1">HTTP Method</label>

                    <select class="form-select form-select-sm"
                            id="m_filter_url_method"
                            onchange="syncUrlModalToDesktop('filter_url_method', this.value)">
                        <option value="">All Methods</option>
                        <option value="GET" {{ request('method') === 'GET' ? 'selected' : '' }}>GET</option>
                        <option value="POST" {{ request('method') === 'POST' ? 'selected' : '' }}>POST</option>
                        <option value="PUT" {{ request('method') === 'PUT' ? 'selected' : '' }}>PUT</option>
                        <option value="DELETE" {{ request('method') === 'DELETE' ? 'selected' : '' }}>DELETE</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-bold text-secondary mb-1">Attack Category</label>

                    <select class="form-select form-select-sm"
                            id="m_filter_type"
                            onchange="syncUrlModalToDesktop('filter_type', this.value)">
                        <option value="">All Categories</option>
                        <option value="high_failures" {{ request('filter_type') === 'high_failures' ? 'selected' : '' }}>
                            High Failures (&ge; 10)
                        </option>
                        <option value="blocked_only" {{ request('filter_type') === 'blocked_only' ? 'selected' : '' }}>
                            Blocked Only
                        </option>
                        <option value="multi_ip" {{ request('filter_type') === 'multi_ip' ? 'selected' : '' }}>
                            Multi-IP (&ge; 3 IPs)
                        </option>
                        <option value="with_payload" {{ request('filter_type') === 'with_payload' ? 'selected' : '' }}>
                            Has Payload
                        </option>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-bold text-secondary mb-1">Sort Order</label>

                    <select class="form-select form-select-sm"
                            id="m_filter_sort_by"
                            onchange="syncUrlModalToDesktop('filter_sort_by', this.value)">
                        <option value="total_requests" {{ request('sort_by') === 'total_requests' ? 'selected' : '' }}>Total Hits</option>
                        <option value="failed_requests" {{ request('sort_by') === 'failed_requests' ? 'selected' : '' }}>Failed Hits</option>
                        <option value="blocked_requests" {{ request('sort_by') === 'blocked_requests' ? 'selected' : '' }}>Blocked Hits</option>
                        <option value="unique_ips_count" {{ request('sort_by') === 'unique_ips_count' ? 'selected' : '' }}>Unique Attacker IPs</option>
                        <option value="last_attack_at" {{ request('sort_by') === 'last_attack_at' ? 'selected' : '' }}>Most Recent Attack</option>
                    </select>
                </div>

                <div class="mb-1">
                    <label class="form-label small fw-bold text-secondary mb-1">Show Per Page</label>

                    <select class="form-select form-select-sm font-tabular"
                            id="m_filter_url_per_page"
                            onchange="syncUrlModalToDesktop('filter_url_per_page', this.value)">
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
                        onclick="resetUrlModalFilters()">
                    Reset
                </button>

                <button type="button"
                        class="btn btn-primary btn-compact fw-bold px-3"
                        onclick="applyUrlModalFilters()">
                    Apply Filters
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ================= 7. MODAL: BULK BLOCK ATTACKING IPS ================= -->
<div class="modal fade url-modal-scope" id="modalBulkBlockIps" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border border-light-subtle shadow">

            <div class="modal-header py-2 px-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-danger d-flex align-items-center gap-2 mb-0" style="font-size: 13px;">
                    <i class="fas fa-shield-virus"></i>
                    Bulk Block Attacking IPs
                </h6>

                <button type="button"
                        class="btn-close shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <form id="formSubmitBulkBlockIps">
                <input type="hidden" id="bulk_url_id">

                <div class="modal-body p-3">

                    <div class="alert alert-warning border-0 small py-2 px-3 mb-2" style="font-size: 11px;">
                        <i class="fas fa-triangle-exclamation me-1"></i>
                        Immediately restricts all distinct IPs targeting this specific route.
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold text-dark mb-1">Target Endpoint</label>

                        <input type="text"
                               class="form-control form-control-sm font-monospace bg-light"
                               id="display_bulk_target_url"
                               readonly
                               style="font-size: 11.5px;">
                    </div>

                    <div class="row g-2 mb-2">

                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark mb-1">Block Type</label>

                            <select class="form-select form-select-sm"
                                    id="bulk_block_type"
                                    name="block_type"
                                    required>
                                <option value="temporary">Temporary</option>
                                <option value="permanent">Permanent</option>
                            </select>
                        </div>

                        <div class="col-6" id="bulk_duration_wrapper">
                            <label class="form-label small fw-bold text-dark mb-1">Duration</label>

                            <select class="form-select form-select-sm" name="duration_minutes">
                                <option value="60">1 Hour</option>
                                <option value="120" selected>2 Hours</option>
                                <option value="360">6 Hours</option>
                                <option value="1440">24 Hours</option>
                                <option value="10080">7 Days</option>
                            </select>
                        </div>

                    </div>

                    <div class="mb-1">
                        <label class="form-label small fw-bold text-dark mb-1">
                            Block Reason <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               class="form-control form-control-sm"
                               id="bulk_block_reason"
                               name="reason"
                               required
                               value="Bulk blocked for probe attacks on restricted endpoint">
                    </div>

                </div>

                <div class="modal-footer py-2 px-3 bg-light border-top d-flex justify-content-between">
                    <button type="button"
                            class="btn btn-light btn-compact border"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-danger btn-compact fw-bold px-3"
                            id="btn-submit-bulk-block">
                        Enforce Block
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- ================= 8. CONFIRMATION MODAL ================= -->
<div class="modal fade url-modal-scope" id="urlConfirmMinimalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border border-light-subtle shadow">

            <div class="modal-header border-bottom py-2 px-3">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2"
                    id="urlConfirmModalTitle">
                    <i class="fas fa-triangle-exclamation text-warning"></i>
                    Confirm Action
                </h6>

                <button type="button"
                        class="btn-close shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <div class="modal-body p-3 text-center">

                <div class="p-2 mb-2 rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center"
                     style="width: 44px; height: 44px;"
                     id="urlConfirmModalIcon">
                    <i class="fas fa-trash-alt fs-5"></i>
                </div>

                <div class="fw-semibold text-dark small mb-1" id="urlConfirmModalMessage">
                    Are you sure you want to proceed?
                </div>

                <span class="text-secondary d-block" style="font-size: 11px;">
                    This security operation will take effect immediately.
                </span>

            </div>

            <div class="modal-footer border-0 pt-0 px-3 pb-3 d-flex justify-content-center gap-2">
                <button type="button"
                        class="btn btn-sm btn-light border px-3 flex-fill"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="button"
                        class="btn btn-sm btn-danger px-3 fw-semibold flex-fill"
                        id="btnExecuteUrlConfirm">
                    Confirm
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ================= 9. NOTICE MODAL ================= -->
<div class="modal fade url-modal-scope" id="urlNoticeMinimalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border border-light-subtle shadow">

            <div class="modal-header border-bottom py-2 px-3">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2"
                    id="urlNoticeModalTitle">
                    <i class="fas fa-info-circle text-primary"></i>
                    Notification
                </h6>

                <button type="button"
                        class="btn-close shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <div class="modal-body p-3 text-center">

                <div class="p-2 mb-2 rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center"
                     style="width: 44px; height: 44px;">
                    <i class="fas fa-check fs-5"></i>
                </div>

                <div class="fw-semibold text-dark small mb-1" id="urlNoticeModalMessage"></div>

            </div>

            <div class="modal-footer border-0 pt-0 px-3 pb-3">
                <button type="button"
                        class="btn btn-sm btn-primary w-100 fw-semibold"
                        data-bs-dismiss="modal">
                    OK
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ================= 10. LIVE JAVASCRIPT AJAX HANDLER ================= -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('url-table-body');
    const paginationContainer = document.getElementById('url-pagination-container');
    const filterForm = document.getElementById('urlFilterForm');
    const searchInput = document.getElementById('filter_url_search');
    const exportBtn = document.getElementById('btn-export-urls-csv');
    const csrfToken = '{{ csrf_token() }}';
    const exportBaseUrl = `{{ route('admin.security.urls.export') }}`;

    let searchTimer = null;
    let pendingConfirmAction = null;

    // Custom UI-Friendly Notice Modal
    function showUrlNotice(msg, title = 'Notification') {
        const titleEl = document.getElementById('urlNoticeModalTitle');
        const msgEl = document.getElementById('urlNoticeModalMessage');

        if (titleEl) {
            titleEl.innerHTML = `<i class="fas fa-info-circle text-primary me-1"></i> ${title}`;
        }

        if (msgEl) {
            msgEl.innerText = msg;
        }

        bootstrap.Modal.getOrCreateInstance(
            document.getElementById('urlNoticeMinimalModal')
        ).show();
    }

    // Custom UI-Friendly Confirm Modal
    function showUrlConfirm(title, msg, onConfirm, btnText = 'Confirm', btnClass = 'btn-danger') {
        const titleEl = document.getElementById('urlConfirmModalTitle');
        const msgEl = document.getElementById('urlConfirmModalMessage');
        const btn = document.getElementById('btnExecuteUrlConfirm');

        if (titleEl) {
            titleEl.innerHTML = `<i class="fas fa-triangle-exclamation text-warning me-1"></i> ${title}`;
        }

        if (msgEl) {
            msgEl.innerHTML = msg;
        }

        if (btn) {
            btn.innerText = btnText;
            btn.className = `btn btn-sm ${btnClass} px-3 fw-semibold flex-fill`;
        }

        pendingConfirmAction = onConfirm;

        bootstrap.Modal.getOrCreateInstance(
            document.getElementById('urlConfirmMinimalModal')
        ).show();
    }

    document.getElementById('btnExecuteUrlConfirm')?.addEventListener('click', function() {
        bootstrap.Modal.getInstance(
            document.getElementById('urlConfirmMinimalModal')
        )?.hide();

        if (typeof pendingConfirmAction === 'function') {
            pendingConfirmAction();
            pendingConfirmAction = null;
        }
    });

    // 1. Export Link Sync
    function syncExportLink(params) {
        if (exportBtn) {
            exportBtn.href = `${exportBaseUrl}?${params.toString()}`;
        }
    }

    // 2. Fetch URLs Data via AJAX
    function fetchUrlsData(url = null) {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        const targetUrl = url || `{{ route('admin.security.urls.index') }}?${params.toString()}`;

        syncExportLink(params);

        if (tableBody) {
            tableBody.style.opacity = '0.4';
        }

        fetch(targetUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (tableBody) {
                tableBody.style.opacity = '1';
            }

            if (data.success) {
                if (tableBody && data.html) {
                    tableBody.innerHTML = data.html;
                }

                if (paginationContainer && data.pagination) {
                    paginationContainer.innerHTML = data.pagination;
                }

                const counter = document.getElementById('url-total-counter');

                if (counter && data.total !== undefined) {
                    counter.innerText = Number(data.total).toLocaleString();
                }

                const showingText = document.getElementById('url-showing-text');

                if (showingText && data.total !== undefined) {
                    showingText.innerHTML = `Showing results of <strong class="font-tabular">${Number(data.total).toLocaleString()}</strong> routes`;
                }
            }
        })
        .catch(err => {
            if (tableBody) {
                tableBody.style.opacity = '1';
            }

            console.error('URL attacks fetch error:', err);
        });
    }

    // 3. Debounced Search Handler
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);

            searchTimer = setTimeout(() => {
                fetchUrlsData();
            }, 350);
        });
    }

    // Mobile Search Input Sync
    window.syncMobileUrlSearch = function(val) {
        if (searchInput) {
            searchInput.value = val;

            clearTimeout(searchTimer);

            searchTimer = setTimeout(() => {
                fetchUrlsData();
            }, 350);
        }
    };

    // Modal to Desktop Form Sync
    window.syncUrlModalToDesktop = function(desktopElemId, val) {
        const desktopEl = document.getElementById(desktopElemId);

        if (desktopEl) {
            desktopEl.value = val;
        }
    };

    window.applyUrlModalFilters = function() {
        const modalEl = document.getElementById('modalMobileUrlFilters');

        if (modalEl) {
            bootstrap.Modal.getInstance(modalEl)?.hide();
        }

        updateMobileUrlFilterBadge();
        fetchUrlsData();
    };

    window.resetUrlModalFilters = function() {
        document.getElementById('m_filter_url_method').value = '';
        document.getElementById('m_filter_type').value = '';
        document.getElementById('m_filter_sort_by').value = 'total_requests';
        document.getElementById('m_filter_url_per_page').value = '20';

        document.getElementById('filter_url_method').value = '';
        document.getElementById('filter_type').value = '';
        document.getElementById('filter_sort_by').value = 'total_requests';
        document.getElementById('filter_url_per_page').value = '20';

        const mobSearch = document.getElementById('mobile_filter_url_search');

        if (mobSearch) {
            mobSearch.value = '';
        }

        if (searchInput) {
            searchInput.value = '';
        }

        updateMobileUrlFilterBadge();
        fetchUrlsData();
    };

    function updateMobileUrlFilterBadge() {
        const badge = document.getElementById('mobileActiveUrlFilterBadge');

        if (!badge) {
            return;
        }

        let activeCount = 0;

        if (document.getElementById('m_filter_url_method').value) {
            activeCount++;
        }

        if (document.getElementById('m_filter_type').value) {
            activeCount++;
        }

        if (document.getElementById('m_filter_sort_by').value !== 'total_requests') {
            activeCount++;
        }

        if (activeCount > 0) {
            badge.innerText = activeCount;
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    }

    // 4. Desktop Dropdown Auto-Refresh
    document.querySelectorAll(
        '#filter_url_method, #filter_type, #filter_sort_by, #filter_url_per_page'
    ).forEach(sel => {
        sel.addEventListener('change', () => fetchUrlsData());
    });

    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            fetchUrlsData();
        });
    }

    const resetBtn = document.getElementById('btn-reset-url-filters');

    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            filterForm.reset();

            const mobSearch = document.getElementById('mobile_filter_url_search');

            if (mobSearch) {
                mobSearch.value = '';
            }

            fetchUrlsData(`{{ route('admin.security.urls.index') }}`);
        });
    }

    // 5. Pagination Click Interceptor
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#url-pagination-container .page-link');

        if (pageLink && pageLink.getAttribute('href')) {
            e.preventDefault();
            fetchUrlsData(pageLink.getAttribute('href'));
        }
    });

    // 6. Bulk Block Modal & Reset Trigger Handlers
    document.addEventListener('click', function(e) {
        const blockBtn = e.target.closest('.btn-trigger-bulk-block');

        if (blockBtn) {
            const urlId = blockBtn.getAttribute('data-id');
            const urlPath = blockBtn.getAttribute('data-path');
            const ipCount = blockBtn.getAttribute('data-ips');

            document.getElementById('bulk_url_id').value = urlId;

            document.getElementById('display_bulk_target_url').value =
                `${urlPath} (${ipCount} Unique IPs)`;

            const modalEl = document.getElementById('modalBulkBlockIps');

            if (modalEl && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        }

        // Counter Reset Action
        const resetMetricBtn = e.target.closest('.btn-reset-url-metrics');

        if (resetMetricBtn) {
            const urlId = resetMetricBtn.getAttribute('data-id');
            const urlPath = resetMetricBtn.getAttribute('data-path');

            showUrlConfirm(
                'Reset Telemetry',
                `Reset all recorded metrics for endpoint: <strong class="text-dark">${urlPath}</strong>?`,
                function() {
                    fetch(`{{ url('/admin/security/urls/reset') }}/${urlId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(res => {
                        showUrlNotice(res.message, 'History Reset');

                        if (res.success) {
                            fetchUrlsData();
                        }
                    })
                    .catch(() => {
                        showUrlNotice('Failed to reset metric history.', 'Error');
                    });
                },
                'Reset History',
                'btn-warning'
            );
        }
    });

    // 7. Bulk Block Type Toggle
    const bulkType = document.getElementById('bulk_block_type');
    const bulkDurWrap = document.getElementById('bulk_duration_wrapper');

    if (bulkType && bulkDurWrap) {
        bulkType.addEventListener('change', function() {
            bulkDurWrap.style.display = this.value === 'permanent' ? 'none' : 'block';
        });
    }

    // 8. Bulk Block Form Submit
    const bulkForm = document.getElementById('formSubmitBulkBlockIps');

    if (bulkForm) {
        bulkForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const btn = document.getElementById('btn-submit-bulk-block');

            btn.disabled = true;
            btn.innerText = 'Enforcing...';

            const urlId = document.getElementById('bulk_url_id').value;

            const payload = {
                block_type: bulkForm.querySelector('[name="block_type"]').value,
                duration_minutes: bulkForm.querySelector('[name="duration_minutes"]').value,
                reason: document.getElementById('bulk_block_reason').value,
            };

            fetch(`{{ url('/admin/security/urls/block-all') }}/${urlId}`, {
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
                btn.disabled = false;
                btn.innerText = 'Enforce Block';

                const modalEl = document.getElementById('modalBulkBlockIps');

                if (modalEl && typeof bootstrap !== 'undefined') {
                    bootstrap.Modal.getInstance(modalEl)?.hide();
                }

                showUrlNotice(res.message, 'Block Enforced');

                if (res.success) {
                    fetchUrlsData();
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerText = 'Enforce Block';

                showUrlNotice('An error occurred during bulk blocking.', 'Error');
            });
        });
    }

    // 9. Refresh Button
    const refreshBtn = document.getElementById('btn-refresh-urls');

    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            const icon = document.getElementById('urls-refresh-icon');

            if (icon) {
                icon.classList.add('fa-spin');
            }

            fetchUrlsData();

            setTimeout(() => {
                if (icon) {
                    icon.classList.remove('fa-spin');
                }
            }, 600);
        });
    }
});
</script>
@endsection