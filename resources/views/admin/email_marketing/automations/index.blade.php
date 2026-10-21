@extends('layouts.admin')

@section('title', 'Automation & Workflows - Email Marketing')

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-automations-root .btn,
    #email-marketing-automations-root .btn-sm,
    #email-marketing-automations-root button,
    #email-marketing-automations-root .badge,
    #email-marketing-automations-root .alert,
    #email-marketing-automations-root .form-control,
    #email-marketing-automations-root .form-select,
    #email-marketing-automations-root .input-group-text,
    #email-marketing-automations-root .card,
    #email-marketing-automations-root .metric-card,
    #email-marketing-automations-root .page-link,
    #email-marketing-automations-root .page-item span,
    #email-marketing-automations-root .collapse-toggle-btn,
    .workflow-modal-scope .modal-content,
    .workflow-modal-scope .btn {
        border-radius: 5px !important;
    }

    #email-marketing-automations-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #email-marketing-automations-root .input-group > .form-control {
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
    #email-marketing-automations-root .card {
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
    .custom-workflow-table {
        width: 100%;
        margin-bottom: 0;
    }
    .custom-workflow-table thead th {
        font-size: 10px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 8px;
        white-space: nowrap;
    }
    .custom-workflow-table tbody td {
        padding: 8px 8px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        font-size: 11px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .custom-workflow-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .custom-workflow-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .custom-workflow-table th,
        .custom-workflow-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* 7 Columns Proportional Distribution Summing to Exactly 100% */
        .col-wf-id      { width: 4% !important; }
        .col-wf-name    { width: 32% !important; }
        .col-wf-trigger { width: 18% !important; }
        .col-wf-steps   { width: 10% !important; }
        .col-wf-status  { width: 11% !important; }
        .col-wf-date    { width: 12% !important; }
        .col-wf-actions { width: 13% !important; }
    }

    /* Form Controls */
    #email-marketing-automations-root .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #email-marketing-automations-root .form-control,
    #email-marketing-automations-root .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #email-marketing-automations-root .form-control:focus,
    #email-marketing-automations-root .form-select:focus {
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
        .workflow-header-wrap h5 {
            font-size: 16px !important;
        }
        .top-action-btns {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
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
        .custom-workflow-table {
            min-width: 780px !important;
            table-layout: auto !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-automations-root">

    <!-- 1. Header & Quick Navigation (Original Clean Light Theme) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 workflow-header-wrap">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-diagram-project text-primary"></i>
                <span>Automation &amp; Workflows</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Automate customer journeys for abandoned cart recovery, welcome series, and post-purchase follow-ups.
            </small>
        </div>

        <div class="d-flex gap-2 top-action-btns">
            <a href="{{ route('admin.email_marketing.automations.logs') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-list-check me-1"></i> <span>Execution Logs</span>
            </a>
            <a href="{{ route('admin.email_marketing.automations.create') }}" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold">
                <i class="fas fa-plus-circle me-1"></i> <span>Create Workflow</span>
            </a>
        </div>
    </div>

    <!-- 2. Session Feedback Messages -->
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

    <!-- 3. Overview Stats Row (Collapsible Section) -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-chart-pie text-primary me-1"></i> Automation Engine Status &amp; Performance Metrics
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseWorkflowKpis" aria-expanded="true" aria-controls="collapseWorkflowKpis" title="Toggle Section">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseWorkflowKpis">
            <div class="p-3">
                <div class="row g-2">
                    <!-- Total Workflows -->
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Total Workflows</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                                    <i class="fas fa-sitemap"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                                    {{ $automations->total() }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Configured journeys</small>
                            </div>
                        </div>
                    </div>

                    <!-- Active Engine -->
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Active Engine</span>
                                <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                                    <i class="fas fa-bolt"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num">
                                    {{ $automations->where('is_active', true)->count() }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Running in background</small>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Recovery -->
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Cart Recovery</span>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                                    <i class="fas fa-cart-shopping"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-warning-emphasis bn-digit font-monospace stat-val-num">
                                    {{ $automations->where('trigger_event', 'abandoned_cart')->count() }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Cart sequences</small>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Care -->
                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-cyan h-100 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted stat-label-title text-truncate">Customer Care</span>
                                <div class="metric-icon-square bg-info-subtle text-info shadow-xs">
                                    <i class="fas fa-heart"></i>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <h4 class="fw-bold my-0 text-info bn-digit font-monospace stat-val-num">
                                    {{ $automations->whereIn('trigger_event', ['welcome_subscriber', 'first_purchase', 'order_delivered'])->count() }}
                                </h4>
                                <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Lifecycle retention</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Quick Search & Filter Toolbar (Collapsible) -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="section-header-wrap d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark small" style="font-size: 12px;">
                <i class="fas fa-filter text-primary me-1"></i> Filter Workflows &amp; Trigger Events
            </span>
            <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseWorkflowFilter" aria-expanded="true" aria-controls="collapseWorkflowFilter" title="Toggle Filters">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>
        <div class="collapse show" id="collapseWorkflowFilter">
            <div class="p-3">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-5">
                        <label class="form-label" for="workflowSearchInput">
                            <i class="fas fa-search me-1 text-primary"></i> Search Workflow Name or Note
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                            <input type="text" id="workflowSearchInput" class="form-control border-start-0 ps-0 shadow-none" placeholder="e.g. Abandoned Cart, Welcome Series..." onkeyup="filterWorkflowsTable()">
                        </div>
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label" for="workflowTriggerFilter">
                            <i class="fas fa-bolt me-1 text-warning"></i> Trigger Event
                        </label>
                        <select id="workflowTriggerFilter" class="form-select shadow-none font-monospace" onchange="filterWorkflowsTable()">
                            <option value="all" selected>All Trigger Events</option>
                            <option value="abandoned_cart">Abandoned Cart Recovery</option>
                            <option value="welcome_subscriber">Welcome Subscriber</option>
                            <option value="first_purchase">First Purchase Welcome</option>
                            <option value="order_delivered">Order Delivered Follow-up</option>
                            <option value="win_back_inactivity">Win-Back Inactive Customers</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label" for="workflowStatusFilter">
                            <i class="fas fa-toggle-on me-1 text-success"></i> Engine Status
                        </label>
                        <select id="workflowStatusFilter" class="form-select shadow-none font-monospace" onchange="filterWorkflowsTable()">
                            <option value="all" selected>All Statuses</option>
                            <option value="active">Active Engine</option>
                            <option value="inactive">Inactive / Paused</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Workflows Table Card (Desktop 100% Fit Screen with Zero Horizontal Scroll) -->
    <div class="card border-0 shadow-sm overflow-hidden mb-3">
        <div class="p-2 px-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 bg-light bg-opacity-25">
            <div>
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13px;">
                    <i class="fas fa-network-wired text-primary"></i> <span>Configured Customer Automation Journeys</span>
                </h6>
                <small class="text-muted font-monospace bn-digit" style="font-size: 10px;">
                    Showing {{ $automations->firstItem() ?? 0 }} to {{ $automations->lastItem() ?? 0 }} of {{$automations->total() }} workflows
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 10px;" id="visible-workflow-count">
                    {{ $automations->total() }} Total Configured
                </span>
                <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseWorkflowsTable" aria-expanded="true" aria-controls="collapseWorkflowsTable" title="Toggle Table">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseWorkflowsTable">
            <div class="table-responsive pc-fit-container">
                <table class="table align-middle mb-0 custom-workflow-table" id="workflows-table">
                    <thead>
                        <tr>
                            <th class="ps-3 col-wf-id text-center">#</th>
                            <th class="col-wf-name">Workflow Name &amp; Description</th>
                            <th class="col-wf-trigger">Trigger Event</th>
                            <th class="text-center col-wf-steps">Workflow Steps</th>
                            <th class="col-wf-status">Running Status</th>
                            <th class="col-wf-date">Created At</th>
                            <th class="text-end pe-3 col-wf-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="workflows-tbody">
                        @forelse($automations as $workflow)
                            @php
                                $triggerBadge = match($workflow->trigger_event) {
                                    'abandoned_cart'      => 'bg-warning-subtle text-dark border border-warning-subtle',
                                    'welcome_subscriber'  => 'bg-primary-subtle text-primary border border-primary-subtle',
                                    'first_purchase'      => 'bg-success-subtle text-success border border-success-subtle',
                                    'order_delivered'     => 'bg-info-subtle text-info border border-info-subtle',
                                    'win_back_inactivity' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    default               => 'bg-light text-dark border',
                                };

                                $triggerIcon = match($workflow->trigger_event) {
                                    'abandoned_cart'      => 'fa-cart-shopping',
                                    'welcome_subscriber'  => 'fa-user-plus',
                                    'first_purchase'      => 'fa-bag-shopping',
                                    'order_delivered'     => 'fa-truck-fast',
                                    'win_back_inactivity' => 'fa-clock-rotate-left',
                                    default               => 'fa-bolt',
                                };
                            @endphp
                            <tr class="workflow-row" data-name="{{ strtolower($workflow->name . ' ' . ($workflow->description ?? '')) }}" data-trigger="{{ strtolower($workflow->trigger_event) }}" data-status="{{ $workflow->is_active ? 'active' : 'inactive' }}">
                                <td class="ps-3 col-wf-id text-center font-monospace bn-digit text-muted" style="font-size: 11px;">
                                    {{ $workflow->id }}
                                </td>
                                <td class="col-wf-name">
                                    <div class="min-w-0" style="line-height: 1.3;">
                                        <strong class="text-dark d-block text-truncate" style="font-size: 12px;" title="{{ $workflow->name }}">
                                            {{ $workflow->name }}
                                        </strong>
                                        <small class="text-muted d-block text-truncate font-monospace" style="font-size: 10px;" title="{{ $workflow->description ?? 'No custom workflow description.' }}">
                                            {{ $workflow->description ?? 'No custom workflow description.' }}
                                        </small>
                                    </div>
                                </td>
                                <td class="col-wf-trigger">
                                    <span class="badge {{ $triggerBadge }} font-monospace text-truncate d-inline-block px-2 py-1" style="font-size: 9px; max-width: 100%; border-radius: 5px !important;">
                                        <i class="fas {{ $triggerIcon }} me-1"></i> {{ ucfirst(str_replace('_', ' ', $workflow->trigger_event)) }}
                                    </span>
                                </td>
                                <td class="text-center col-wf-steps">
                                    <span class="badge bg-light text-dark border px-2 py-1 font-monospace bn-digit" style="font-size: 10px; border-radius: 5px !important;">
                                        {{ $workflow->steps_count }} {{ Str::plural('Step',$workflow->steps_count) }}
                                    </span>
                                </td>
                                <td class="col-wf-status">
                                    @if($workflow->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 10px;">
                                            <i class="fas fa-circle-check me-1"></i> Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 10px;">
                                            <i class="fas fa-pause me-1"></i> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="col-wf-date font-monospace bn-digit" style="line-height: 1.3;">
                                    <span class="text-dark d-block" style="font-size: 11px;">{{ $workflow->created_at->format('M d, Y') }}</span>
                                    <small class="text-muted" style="font-size: 9px;">{{ $workflow->created_at->format('h:i A') }}</small>
                                </td>
                                <td class="text-end pe-3 col-wf-actions">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        {{-- Toggle Active / Inactive --}}
                                        <form action="{{ route('admin.email_marketing.automations.toggle_status', $workflow->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light border p-1 shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; border-radius: 5px !important;" title="{{ $workflow->is_active ? 'Pause Automation' : 'Activate Automation' }}">
                                                @if($workflow->is_active)
                                                    <i class="fas fa-pause text-warning" style="font-size: 10px;"></i>
                                                @else
                                                    <i class="fas fa-play text-success" style="font-size: 10px;"></i>
                                                @endif
                                            </button>
                                        </form>

                                        {{-- Edit Workflow --}}
                                        <a href="{{ route('admin.email_marketing.automations.edit', $workflow->id) }}" class="btn btn-sm btn-light border p-1 text-primary shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; border-radius: 5px !important;" title="Edit Workflow">
                                            <i class="fas fa-pencil" style="font-size: 10px;"></i>
                                        </a>

                                        {{-- Delete Workflow via Custom Enterprise Modal --}}
                                        <button type="button" class="btn btn-sm btn-light border p-1 text-danger shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; border-radius: 5px !important;" onclick="triggerDeleteWorkflow({{ $workflow->id }}, '{{ addslashes($workflow->name) }}', '{{ route('admin.email_marketing.automations.destroy',$workflow->id) }}')" title="Delete Workflow">
                                            <i class="fas fa-trash-can" style="font-size: 10px;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyWorkflowsRow">
                                <td colspan="7" class="text-center py-5 text-muted small">
                                    <i class="fas fa-diagram-project fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                    <span class="fw-semibold">No automated marketing workflows configured yet. Click "Create Workflow" to automate your store emails.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Bulletproof Responsive Pagination Footer -->
            @if($automations->hasPages())
                <div class="p-2 px-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50 card-footer no-print">
                    <small class="text-muted font-monospace bn-digit" style="font-size: 11px;">
                        Showing {{ $automations->firstItem() }} to {{ $automations->lastItem() }} of {{$automations->total() }} workflows
                    </small>
                    <div>
                        {{ $automations->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Custom Delete Confirmation Modal (Replaces browser confirm) -->
<div class="modal fade workflow-modal-scope" id="deleteWorkflowModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 44px; height: 44px; border-radius: 50% !important;">
                    <i class="fas fa-trash-can fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Delete Workflow?</h6>
                <p class="text-muted small mb-3" style="font-size: 11px; line-height: 1.45;">
                    Are you sure you want to permanently remove <strong id="deleteWorkflowTitle" class="text-dark">this workflow</strong>? All scheduled queue executions will be halted.
                </p>
                <form id="deleteWorkflowForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-compact shadow-xs fw-bold">Yes, Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function triggerDeleteWorkflow(id, name, formUrl) {
        document.getElementById('deleteWorkflowTitle').innerText = name;
        document.getElementById('deleteWorkflowForm').action = formUrl;
        const modalEl = document.getElementById('deleteWorkflowModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function filterWorkflowsTable() {
        const query   = (document.getElementById('workflowSearchInput')?.value || '').toLowerCase().trim();
        const trigger = (document.getElementById('workflowTriggerFilter')?.value || 'all').toLowerCase();
        const status  = (document.getElementById('workflowStatusFilter')?.value || 'all').toLowerCase();

        const rows = document.querySelectorAll('.workflow-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowName    = row.getAttribute('data-name') || '';
            const rowTrigger = row.getAttribute('data-trigger') || '';
            const rowStatus  = row.getAttribute('data-status') || '';

            const matchesQuery   = !query || rowName.includes(query);
            const matchesTrigger = trigger === 'all' || rowTrigger === trigger;
            const matchesStatus  = status === 'all' || rowStatus === status;

            if (matchesQuery && matchesTrigger && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const countBadge = document.getElementById('visible-workflow-count');
        if (countBadge) {
            countBadge.innerText = `${visibleCount} Showing`;
        }
    }
</script>
@endsection