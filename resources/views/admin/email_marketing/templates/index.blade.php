@extends('layouts.admin')

@section('title', 'Email Templates - Email Marketing')

@php
    $totalTemplatesCount = $templates->total();
    $promotionalCount    = \App\Models\EmailTemplate::where('category', 'promotional')->count();
    $cartCount           = \App\Models\EmailTemplate::where('category', 'abandoned_cart')->count();
    $transactionalCount  = \App\Models\EmailTemplate::where('category', 'transactional')->count();
    $newsletterCount     = \App\Models\EmailTemplate::where('category', 'newsletter')->count();
@endphp

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-templates-root .btn,
    #email-marketing-templates-root .btn-sm,
    #email-marketing-templates-root button,
    #email-marketing-templates-root .badge,
    #email-marketing-templates-root .alert,
    #email-marketing-templates-root .form-control,
    #email-marketing-templates-root .form-select,
    #email-marketing-templates-root .input-group-text,
    #email-marketing-templates-root .card,
    #email-marketing-templates-root .metric-card,
    #email-marketing-templates-root .page-link,
    #email-marketing-templates-root .page-item span,
    #email-marketing-templates-root .collapse-toggle-btn,
    .template-modal-scope .modal-content,
    .template-modal-scope .btn,
    .admin-template-toast {
        border-radius: 5px !important;
    }

    #email-marketing-templates-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #email-marketing-templates-root .input-group > .form-control,
    #email-marketing-templates-root .input-group > .form-select {
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

    .template-filter-control {
        font-size: 12px !important;
        height: 32px !important;
        padding: 4px 8px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        box-shadow: none !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .template-filter-control:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        outline: none;
    }

    .template-addon-text {
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

    /* Enterprise Surface Cards */
    #email-marketing-templates-root .card {
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

    .metric-card-blue   { background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%); border-left: 3.5px solid #2563eb !important; }
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-left: 3.5px solid #10b981 !important; }
    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-left: 3.5px solid #f59e0b !important; }
    .metric-card-cyan   { background: linear-gradient(180deg, #ffffff 0%, #ecfeff 100%); border-left: 3.5px solid #06b6d4 !important; }

    /* Table Architecture: 100% Fit Screen on PC with Zero Horizontal Scroll */
    .custom-templates-table {
        width: 100%;
        margin-bottom: 0;
    }
    .custom-templates-table thead th {
        font-size: 10.5px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 9px 8px;
        white-space: nowrap;
    }
    .custom-templates-table tbody td {
        padding: 8px 8px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        font-size: 12px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .custom-templates-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .custom-templates-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .custom-templates-table th,
        .custom-templates-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 6 Columns Proportional Distribution Summing to Exactly 100% */
        .col-tp-id      { width: 4% !important; }
        .col-tp-name    { width: 28% !important; }
        .col-tp-cat     { width: 14% !important; }
        .col-tp-subject { width: 28% !important; }
        .col-tp-date    { width: 14% !important; }
        .col-tp-actions { width: 12% !important; }
    }

    /* Form Controls */
    #email-marketing-templates-root .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
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
    .card-footer nav div:has(> p) {
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

    /* Floating Toast Box */
    .admin-template-toast {
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

    /* 100/100 Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .templates-header-wrap h5 {
            font-size: 16px !important;
        }
        .top-action-btns {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .top-action-btns .btn-create-main {
            grid-column: span 2;
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
        .custom-templates-table {
            min-width: 800px !important;
            table-layout: auto !important;
        }
        #email-marketing-templates-root .card {
            margin-bottom: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-templates-root">

    {{-- 1. Header & Navigation (Original Clean Light Theme) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 templates-header-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-paintbrush text-primary"></i>
                    <span>Email Templates</span>
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace" id="templates-total-badge" style="font-size: 10px;">
                    {{ $totalTemplatesCount }} Templates
                </span>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                Design, customize, preview, and duplicate responsive email marketing layouts.
            </small>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 top-action-btns">
            <a href="{{ url('admin/email-marketing/campaigns') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-paper-plane me-1"></i> <span>Campaigns</span>
            </a>
            <a href="{{ url('admin/email-marketing/templates/create') }}" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold btn-create-main">
                <i class="fas fa-plus-circle me-1"></i> <span>Create Template</span>
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

    {{-- 3. Category Cards Overview (Collapsible Section) --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-chart-pie text-primary me-1"></i> Template Library Category Breakdown
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseTemplatesKpis" aria-expanded="true" aria-controls="collapseTemplatesKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseTemplatesKpis">
            <div class="p-3">
                <div class="row g-2">
                    {{-- Total Templates --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Total Templates</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                                    <i class="fas fa-file-code"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num" id="stat-total-templates">
                                    {{ $totalTemplatesCount }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Available in builder</small>
                            </div>
                        </div>
                    </div>

                    {{-- Promotional --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Promotional</span>
                                <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                                    <i class="fas fa-tags"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num" id="stat-promotional-templates">
                                    {{ $promotionalCount }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Sales &amp; Discount offers</small>
                            </div>
                        </div>
                    </div>

                    {{-- Abandoned Cart --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Abandoned Cart</span>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                                    <i class="fas fa-cart-shopping"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-warning-emphasis bn-digit font-monospace stat-val-num" id="stat-cart-templates">
                                    {{ $cartCount }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Recovery sequences</small>
                            </div>
                        </div>
                    </div>

                    {{-- Transactional --}}
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-cyan h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Transactional</span>
                                <div class="metric-icon-square bg-info-subtle text-info shadow-xs">
                                    <i class="fas fa-receipt"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-info bn-digit font-monospace stat-val-num" id="stat-transactional-templates">
                                    {{ $transactionalCount }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Order alerts &amp; receipts</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Instant Search & Category Filter Toolbar (Collapsible) --}}
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0 !important;">
        <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-filter text-primary me-1"></i> Search Templates &amp; Filter by Category
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseTemplatesFilter" aria-expanded="true" aria-controls="collapseTemplatesFilter" title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseTemplatesFilter">
            <div class="p-3">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-5">
                        <label class="form-label" for="templateSearchInput">
                            <i class="fas fa-search me-1 text-primary"></i> Search Template Name or Subject
                        </label>
                        <div class="input-group">
                            <span class="input-group-text template-addon-text border-end-0"><i class="fas fa-search text-muted" style="font-size: 11px;"></i></span>
                            <input type="text" id="templateSearchInput" class="form-control border-start-0 ps-0 template-filter-control shadow-none font-monospace" placeholder="e.g. Flash Sale, Welcome Email, Cart Reminder..." onkeyup="filterTemplatesTable()">
                        </div>
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label" for="templateCategoryFilter">
                            <i class="fas fa-tags me-1 text-success"></i> Template Category
                        </label>
                        <select id="templateCategoryFilter" class="form-select template-filter-control shadow-none font-monospace" onchange="filterTemplatesTable()">
                            <option value="all" selected>All Categories</option>
                            <option value="promotional">Promotional</option>
                            <option value="abandoned_cart">Abandoned Cart</option>
                            <option value="newsletter">Newsletter</option>
                            <option value="transactional">Transactional</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <button type="button" class="btn btn-light border btn-compact w-100 shadow-xs fw-semibold text-secondary" onclick="resetTemplateFilters()">
                            <i class="fas fa-rotate-left me-1"></i> <span>Reset Filters</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. Templates Table Card (Desktop 100% Fit Screen with Zero Horizontal Scroll) --}}
    <div class="card border-0 shadow-sm overflow-hidden mb-3">
        <div class="p-2 px-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 bg-light bg-opacity-25">
            <div>
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13px;">
                    <i class="fas fa-layer-group text-primary"></i> <span>Configured Design Templates</span>
                </h6>
                <small class="text-muted font-monospace bn-digit" id="showing-entries-text" style="font-size: 10px;">
                    Showing {{ $templates->firstItem() ?? 0 }} to {{ $templates->lastItem() ?? 0 }} of {{$templates->total() }} templates
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 10px;" id="visible-template-count">
                    {{ $templates->total() }} Total
                </span>
                <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseTemplatesTable" aria-expanded="true" aria-controls="collapseTemplatesTable" title="Toggle Table">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseTemplatesTable">
            <div class="table-responsive pc-fit-container" style="min-height: 250px;">
                <table class="table align-middle mb-0 custom-templates-table" id="templates-table">
                    <thead>
                        <tr>
                            <th class="ps-3 col-tp-id text-center">#</th>
                            <th class="col-tp-name">Template Name</th>
                            <th class="col-tp-cat">Category</th>
                            <th class="col-tp-subject">Default Subject</th>
                            <th class="col-tp-date">Last Modified</th>
                            <th class="text-end pe-3 col-tp-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="templates-tbody">
                        @forelse($templates as $tpl)
                            @php
                                $catBadge = match(strtolower($tpl->category ?? '')) {
                                    'promotional'    => 'bg-success-subtle text-success border border-success-subtle',
                                    'abandoned_cart' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                    'newsletter'     => 'bg-primary-subtle text-primary border border-primary-subtle',
                                    'transactional'  => 'bg-info-subtle text-info border border-info-subtle',
                                    default          => 'bg-light text-secondary border',
                                };

                                $rowSearchData = strtolower(
                                    ($tpl->name ?? '') . ' ' .
                                    ($tpl->category ?? '') . ' ' .
                                    ($tpl->subject ?? '')
                                );
                            @endphp
                            <tr class="template-entry-row align-middle" id="template-row-{{ $tpl->id }}" data-search="{{ $rowSearchData }}" data-category="{{ strtolower($tpl->category ?? '') }}">
                                <td class="ps-3 col-tp-id text-center font-monospace bn-digit text-muted" style="font-size: 11px;">
                                    {{ $tpl->id }}
                                </td>
                                <td class="col-tp-name">
                                    <div class="min-w-0" style="line-height: 1.3;">
                                        <strong class="text-dark d-block text-truncate" style="font-size: 12px;" title="{{ $tpl->name }}">
                                            {{ $tpl->name }}
                                        </strong>
                                        <small class="text-muted d-block text-truncate font-monospace" style="font-size: 10px;">
                                            Responsive HTML Layout
                                        </small>
                                    </div>
                                </td>
                                <td class="col-tp-cat">
                                    <span class="badge {{ $catBadge }} font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 9.5px;">
                                        {{ ucfirst(str_replace('_', ' ', $tpl->category)) }}
                                    </span>
                                </td>
                                <td class="col-tp-subject">
                                    <span class="text-dark d-block text-truncate font-monospace" style="font-size: 11px;" title="{{ $tpl->subject ?? '—' }}">
                                        {{ $tpl->subject ?? '—' }}
                                    </span>
                                </td>
                                <td class="col-tp-date font-monospace bn-digit" style="line-height: 1.3;">
                                    <span class="text-dark d-block" style="font-size: 11px;">{{ $tpl->updated_at->format('M d, Y') }}</span>
                                    <small class="text-muted" style="font-size: 9.5px;">{{ $tpl->updated_at->format('h:i A') }}</small>
                                </td>
                                <td class="text-end pe-3 col-tp-actions">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        {{-- 1. Quick Preview Trigger --}}
                                        <button type="button" class="btn btn-sm btn-light border p-1 text-info shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; border-radius: 5px !important;" onclick="previewTemplate({{ $tpl->id }}, '{{ addslashes($tpl->name) }}')" title="Quick Preview">
                                            <i class="fas fa-eye" style="font-size: 10px;"></i>
                                        </button>

                                        {{-- 2. Edit in Builder --}}
                                        <a href="{{ url('admin/email-marketing/templates/edit/' . $tpl->id) }}" class="btn btn-sm btn-light border p-1 text-primary shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; border-radius: 5px !important;" title="Edit in Builder">
                                            <i class="fas fa-pencil" style="font-size: 10px;"></i>
                                        </a>

                                        {{-- 3. Clone / Duplicate AJAX --}}
                                        <button type="button" class="btn btn-sm btn-light border p-1 text-secondary shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; border-radius: 5px !important;" onclick="duplicateTemplateAjax({{ $tpl->id }}, '{{ addslashes($tpl->name) }}')" title="Clone / Duplicate Template">
                                            <i class="fas fa-copy" id="dup-icon-{{ $tpl->id }}" style="font-size: 10px;"></i>
                                        </button>

                                        {{-- 4. Delete via Custom Modal --}}
                                        <button type="button" class="btn btn-sm btn-light border p-1 text-danger shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; border-radius: 5px !important;" onclick="triggerDeleteTemplate({{ $tpl->id }}, '{{ addslashes($tpl->name) }}')" title="Delete Template">
                                            <i class="fas fa-trash-can" style="font-size: 10px;"></i>
                                        </button>
                                    </div>

                                    {{-- Hidden raw HTML holder for instant client-side preview --}}
                                    <div id="tpl-raw-html-{{ $tpl->id }}" class="d-none">{!! $tpl->html_content !!}</div>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyTemplatesRow">
                                <td colspan="6" class="text-center py-5 text-muted small">
                                    <i class="fas fa-palette fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                    <span class="fw-semibold">No email templates created yet. Click "Create Template" to get started.</span>
                                </td>
                            </tr>
                        @endforelse
                        <tr id="noMatchTemplatesRow" style="display: none;">
                            <td colspan="6" class="text-center py-5 text-muted small">
                                <i class="fas fa-filter-circle-xmark fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                <span class="fw-semibold">No templates matched your search or category filter.</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Bulletproof Responsive Pagination Footer --}}
            @if($templates->hasPages())
                <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">
                    <small class="text-muted font-monospace bn-digit" style="font-size: 11px;">
                        Showing {{ $templates->firstItem() }} to {{ $templates->lastItem() }} of {{$templates->total() }} templates
                    </small>
                    <div>
                        {{ $templates->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Interactive Preview Modal with Viewport Toggle (Desktop / Tablet / Mobile) --}}
<div class="modal fade template-modal-scope" id="templatePreviewModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom py-2 px-3 bg-light d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2 min-w-0">
                    <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-1 text-truncate" style="font-size: 13.5px;">
                        <i class="fas fa-eye text-primary"></i> <span id="previewModalTitle">Template Preview</span>
                    </h6>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary btn-compact active" id="btn-view-desktop" onclick="setPreviewViewport('desktop')">
                            <i class="fas fa-desktop me-1"></i> Desktop
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-compact" id="btn-view-tablet" onclick="setPreviewViewport('tablet')">
                            <i class="fas fa-tablet-screen-button me-1"></i> Tablet
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-compact" id="btn-view-mobile" onclick="setPreviewViewport('mobile')">
                            <i class="fas fa-mobile-screen me-1"></i> Mobile
                        </button>
                    </div>
                    <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-2 p-md-3 bg-light d-flex justify-content-center">
                <iframe id="previewIframe" style="width: 100%; height: 560px; border: 1px solid #cbd5e1; border-radius: 5px; background: #ffffff; transition: width 0.25s ease;"></iframe>
            </div>
        </div>
    </div>
</div>

{{-- Modal 2: Custom Delete Confirmation Modal --}}
<div class="modal fade template-modal-scope" id="deleteTemplateModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 44px; height: 44px; border-radius: 50% !important;">
                    <i class="fas fa-trash-can fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Delete Template?</h6>
                <p class="text-muted small mb-3" style="font-size: 11px; line-height: 1.45;">
                    Are you sure you want to permanently delete <strong id="deleteTemplateTitle" class="text-dark">this template</strong>? This action cannot be undone.
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-compact shadow-xs fw-bold" id="btn-confirm-delete-execute">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Floating Toast Feedback Notification Holder --}}
<div id="custom-toast-holder"></div>

<script>
    let pendingDeleteId = null;

    // 1. Live Interactive Preview with Viewport Adjustments
    function previewTemplate(templateId, templateName = 'Template Preview') {
        const rawHtmlEl = document.getElementById('tpl-raw-html-' + templateId);
        const iframe    = document.getElementById('previewIframe');
        const titleEl   = document.getElementById('previewModalTitle');

        if (rawHtmlEl && iframe) {
            if (titleEl) titleEl.innerText = templateName;
            iframe.srcdoc = rawHtmlEl.innerHTML;
            setPreviewViewport('desktop');
            const modalEl = document.getElementById('templatePreviewModal');
            if (modalEl && window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        }
    }

    function setPreviewViewport(type) {
        const iframe     = document.getElementById('previewIframe');
        const btnDesktop = document.getElementById('btn-view-desktop');
        const btnTablet  = document.getElementById('btn-view-tablet');
        const btnMobile  = document.getElementById('btn-view-mobile');

        btnDesktop.classList.remove('active');
        btnTablet.classList.remove('active');
        btnMobile.classList.remove('active');

        if (type === 'mobile') {
            iframe.style.width = '375px';
            btnMobile.classList.add('active');
        } else if (type === 'tablet') {
            iframe.style.width = '768px';
            btnTablet.classList.add('active');
        } else {
            iframe.style.width = '100%';
            btnDesktop.classList.add('active');
        }
    }

    // 2. Real-time Search & Category Filter
    function filterTemplatesTable() {
        const query    = (document.getElementById('templateSearchInput')?.value || '').toLowerCase().trim();
        const category = (document.getElementById('templateCategoryFilter')?.value || 'all').toLowerCase();

        const rows = document.querySelectorAll('.template-entry-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowSearch   = row.getAttribute('data-search') || '';
            const rowCategory = row.getAttribute('data-category') || '';

            const matchesQuery    = !query || rowSearch.includes(query);
            const matchesCategory = category === 'all' || rowCategory === category;

            if (matchesQuery && matchesCategory) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const countBadge = document.getElementById('visible-template-count');
        if (countBadge) {
            countBadge.innerText = `${visibleCount} Showing`;
        }

        const noMatchRow = document.getElementById('noMatchTemplatesRow');
        if (noMatchRow) {
            noMatchRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }
    }

    function resetTemplateFilters() {
        const searchInput = document.getElementById('templateSearchInput');
        const catFilter   = document.getElementById('templateCategoryFilter');
        if (searchInput) searchInput.value = '';
        if (catFilter)   catFilter.value = 'all';
        filterTemplatesTable();
    }

    // 3. Clone / Duplicate AJAX
    function duplicateTemplateAjax(id, name) {
        const icon = document.getElementById(`dup-icon-${id}`);
        if (icon) icon.className = 'fas fa-spinner fa-spin';

        fetch(`{{ url('admin/email-marketing/templates/duplicate') }}/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(() => {
            if (icon) icon.className = 'fas fa-copy';
            showToast('Duplicated', `Template "${name}" duplicated successfully.`, 'success');
            setTimeout(() => window.location.reload(), 600);
        })
        .catch(() => {
            if (icon) icon.className = 'fas fa-copy';
            showToast('Error', 'Failed to duplicate template.', 'danger');
        });
    }

    // 4. Delete Modal Confirmation & AJAX
    function triggerDeleteTemplate(id, name) {
        pendingDeleteId = id;
        document.getElementById('deleteTemplateTitle').innerText = name;
        const modalEl = document.getElementById('deleteTemplateModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    document.getElementById('btn-confirm-delete-execute')?.addEventListener('click', function() {
        if (!pendingDeleteId) return;

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Deleting...';

        fetch(`{{ url('admin/email-marketing/templates/delete') }}/${pendingDeleteId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(() => {
            btn.disabled = false;
            btn.innerHTML = 'Yes, Delete';
            bootstrap.Modal.getInstance(document.getElementById('deleteTemplateModal'))?.hide();

            const row = document.getElementById(`template-row-${pendingDeleteId}`);
            if (row) row.remove();

            showToast('Deleted', 'Template removed successfully.', 'danger');
            filterTemplatesTable();
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = 'Yes, Delete';
            showToast('Error', 'Failed to remove template.', 'danger');
        });
    });

    // 5. Floating Toast Notifications
    function showToast(title, message, type = 'success') {
        const holder = document.getElementById('custom-toast-holder');
        if (!holder) return;

        const toastId = 'toast-' + Date.now();
        const typeConfigs = {
            success: { icon: 'fa-check-circle', color: 'bg-success text-white' },
            danger:  { icon: 'fa-trash-can',    color: 'bg-danger text-white' },
            warning: { icon: 'fa-triangle-exclamation', color: 'bg-warning text-dark' },
            info:    { icon: 'fa-circle-info',  color: 'bg-primary text-white' }
        };
        const config = typeConfigs[type] || typeConfigs.success;

        const html = `
            <div id="${toastId}" class="admin-template-toast ${config.color} p-3 d-flex align-items-center justify-content-between shadow-lg" style="border-radius: 5px !important;">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas ${config.icon} fs-5"></i>
                    <div>
                        <strong class="d-block" style="font-size: 13px;">${title}</strong>
                        <small style="opacity: 0.95; font-size: 11px;">${message}</small>
                    </div>
                </div>
                <button type="button" class="btn-close ${type === 'warning' ? '' : 'btn-close-white'} btn-sm ms-2 shadow-none" onclick="document.getElementById('${toastId}')?.remove()"></button>
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