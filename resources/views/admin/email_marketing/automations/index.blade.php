
@extends('layouts.admin')

@section('title', 'Automation & Workflows - Email Marketing')

@php
    $automations = $automations ?? collect();

    $totalAutomationsCount = method_exists($automations, 'total')
        ? $automations->total()
        : $automations->count();

    $activeCount = $automations->where('is_active', true)->count();
    $cartRecoveryCount = $automations->where('trigger_event', 'abandoned_cart')->count();
    $customerCareCount = $automations->whereIn('trigger_event', [
        'welcome_subscriber',
        'first_purchase',
        'order_delivered'
    ])->count();

    $logsRoute = Route::has('admin.email_marketing.automations.logs')
        ? route('admin.email_marketing.automations.logs')
        : url('admin/email-marketing/automations/logs');

    $createRoute = Route::has('admin.email_marketing.automations.create')
        ? route('admin.email_marketing.automations.create')
        : url('admin/email-marketing/automations/create');
@endphp

@section('admin_content')

<style>
    /* =========================================================
       AUTOMATION WORKFLOWS - ENTERPRISE ADMIN UI
    ========================================================= */

    #email-marketing-automations-root {
        --wf-primary: #2563eb;
        --wf-primary-dark: #1d4ed8;
        --wf-text: #0f172a;
        --wf-muted: #64748b;
        --wf-border: #e2e8f0;
        --wf-surface: #ffffff;
        --wf-soft: #f8fafc;
        --wf-radius: 5px;
    }

    #email-marketing-automations-root *,
    .workflow-modal-scope * {
        box-sizing: border-box;
    }

    /* Strict 5px Radius */
    #email-marketing-automations-root .btn,
    #email-marketing-automations-root .badge,
    #email-marketing-automations-root .alert,
    #email-marketing-automations-root .form-control,
    #email-marketing-automations-root .form-select,
    #email-marketing-automations-root .input-group-text,
    #email-marketing-automations-root .card,
    #email-marketing-automations-root .page-link,
    #email-marketing-automations-root .collapse-toggle-btn,
    .workflow-modal-scope .modal-content,
    .workflow-modal-scope .btn {
        border-radius: 5px !important;
    }

    #email-marketing-automations-root .input-group > .input-group-text {
        border-radius: 5px 0 0 5px !important;
    }

    #email-marketing-automations-root .input-group > .form-control {
        border-radius: 0 5px 5px 0 !important;
    }

    #email-marketing-automations-root .card {
        border: 1px solid var(--wf-border);
        background: var(--wf-surface);
        box-shadow: 0 2px 7px rgba(15, 23, 42, .035);
        margin-bottom: 14px;
    }

    #email-marketing-automations-root .font-monospace,
    #email-marketing-automations-root .bn-digit {
        font-weight: 700 !important;
    }

    /* Header */
    .workflow-header-wrap {
        gap: 12px;
    }

    .workflow-title {
        font-size: 19px;
        font-weight: 800;
        letter-spacing: -.45px;
        color: var(--wf-text);
    }

    .workflow-subtitle {
        color: var(--wf-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    .workflow-title-icon {
        width: 34px;
        height: 34px;
        border-radius: 5px;
        background: #eff6ff;
        color: var(--wf-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }

    /* Compact Buttons */
    .btn-compact {
        min-height: 32px;
        padding: 6px 12px !important;
        font-size: 11.5px !important;
        font-weight: 700 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        white-space: nowrap;
        transition: all .18s ease;
    }

    .btn-compact:hover {
        transform: translateY(-1px);
    }

    .btn-primary {
        background-color: var(--wf-primary);
        border-color: var(--wf-primary);
    }

    .btn-primary:hover {
        background-color: var(--wf-primary-dark);
        border-color: var(--wf-primary-dark);
    }

    /* Section Header */
    .section-header-wrap {
        min-height: 42px;
        padding: 9px 13px;
        background: #fff;
        border-bottom: 1px solid var(--wf-border);
    }

    .section-heading {
        color: var(--wf-text);
        font-size: 12px;
        font-weight: 800;
    }

    .collapse-toggle-btn {
        width: 26px;
        height: 26px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #64748b;
        transition: all .2s ease;
    }

    .collapse-toggle-btn:hover {
        color: #fff;
        background: var(--wf-primary);
        border-color: var(--wf-primary);
    }

    .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    .collapse-icon {
        font-size: 10px;
        transition: transform .25s ease;
    }

    /* Metric Cards */
    .metric-card {
        min-height: 112px;
        padding: 13px;
        height: 100%;
        position: relative;
        overflow: hidden;
        transition: transform .18s ease, box-shadow .18s ease;
        border: 1px solid var(--wf-border) !important;
        border-left: 3px solid transparent !important;
        margin: 0 !important;
    }

    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(15, 23, 42, .07);
    }

    .metric-card-blue {
        background: linear-gradient(145deg, #fff, #eff6ff);
        border-left-color: #2563eb !important;
    }

    .metric-card-green {
        background: linear-gradient(145deg, #fff, #f0fdf4);
        border-left-color: #10b981 !important;
    }

    .metric-card-amber {
        background: linear-gradient(145deg, #fff, #fffbeb);
        border-left-color: #f59e0b !important;
    }

    .metric-card-cyan {
        background: linear-gradient(145deg, #fff, #ecfeff);
        border-left-color: #06b6d4 !important;
    }

    .stat-label-title {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        letter-spacing: .02em;
    }

    .stat-val-num {
        font-size: 21px;
        font-weight: 800;
        line-height: 1.3;
        letter-spacing: -.5px;
        margin: 0;
        color: var(--wf-text);
    }

    .stat-sub-text {
        font-size: 10px;
        color: #94a3b8;
        display: block;
        margin-top: 2px;
    }

    .metric-icon-square {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px !important;
        font-size: 14px;
        flex-shrink: 0;
    }

    /* Filter Controls */
    #email-marketing-automations-root .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 5px;
    }

    .workflow-filter-control {
        height: 35px !important;
        padding: 5px 10px !important;
        font-size: 11.5px !important;
        color: #0f172a !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #fff !important;
        box-shadow: none !important;
    }

    .workflow-filter-control:focus {
        border-color: var(--wf-primary) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .11) !important;
    }

    .workflow-addon-text {
        height: 35px !important;
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: #64748b !important;
        font-size: 11px !important;
    }

    /* Table */
    .pc-fit-container {
        width: 100%;
        overflow-x: hidden;
    }

    .custom-workflow-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .custom-workflow-table thead th {
        padding: 11px 8px;
        background: #f8fafc;
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .045em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .custom-workflow-table tbody td {
        padding: 10px 8px;
        color: #334155;
        font-size: 11.5px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
    }

    .custom-workflow-table tbody tr {
        transition: background .15s ease;
    }

    .custom-workflow-table tbody tr:hover td {
        background: #f8fbff;
    }

    .custom-workflow-table tbody tr:last-child td {
        border-bottom: 0;
    }

    @media (min-width: 992px) {
        .custom-workflow-table {
            table-layout: fixed;
        }

        .custom-workflow-table th,
        .custom-workflow-table td {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .col-wf-id      { width: 4%; }
        .col-wf-name    { width: 30%; }
        .col-wf-trigger { width: 18%; }
        .col-wf-steps   { width: 10%; }
        .col-wf-status  { width: 12%; }
        .col-wf-date    { width: 13%; }
        .col-wf-actions { width: 13%; }
    }

    .workflow-name {
        font-size: 12px;
        font-weight: 800;
        color: #1e293b;
        display: block;
        max-width: 100%;
    }

    .workflow-description {
        display: block;
        color: #94a3b8;
        font-size: 10px;
        max-width: 100%;
        margin-top: 3px;
    }

    .workflow-action-btn {
        width: 28px;
        height: 28px;
        padding: 0 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e2e8f0 !important;
        background: #fff !important;
        transition: all .15s ease;
    }

    .workflow-action-btn:hover {
        background: #eff6ff !important;
        border-color: #bfdbfe !important;
        transform: translateY(-1px);
    }

    .workflow-action-btn i {
        font-size: 10px;
    }

    /* Pagination */
    #email-marketing-automations-root .card-footer nav {
        display: flex;
        justify-content: flex-end;
        width: auto;
        margin: 0;
    }

    #email-marketing-automations-root .card-footer nav > div.d-sm-none,
    #email-marketing-automations-root .card-footer nav p,
    #email-marketing-automations-root .card-footer nav .small.text-muted {
        display: none !important;
    }

    #email-marketing-automations-root .card-footer .pagination {
        margin: 0;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 3px;
    }

    #email-marketing-automations-root .card-footer .page-link,
    #email-marketing-automations-root .card-footer .page-item span {
        min-width: 27px;
        height: 27px;
        padding: 0 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #cbd5e1 !important;
        border-radius: 5px !important;
        background: #fff !important;
        color: #475569 !important;
        font-size: 10.5px;
        font-weight: 700;
        text-decoration: none;
    }

    #email-marketing-automations-root .card-footer .page-item.active .page-link,
    #email-marketing-automations-root .card-footer .page-item.active span {
        background: var(--wf-primary) !important;
        border-color: var(--wf-primary) !important;
        color: #fff !important;
    }

    #email-marketing-automations-root .card-footer .page-item.disabled {
        opacity: .45;
    }

    /* Empty State */
    .workflow-empty-state {
        padding: 48px 15px !important;
        text-align: center;
        color: #94a3b8;
    }

    .workflow-empty-icon {
        width: 55px;
        height: 55px;
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: #94a3b8;
        font-size: 23px;
        margin: 0 auto 13px;
    }

    /* Delete Modal */
    .workflow-modal-scope {
        z-index: 106500;
    }

    .workflow-modal-scope .modal-content {
        border: 1px solid #e2e8f0;
        padding: 8px;
    }

    .delete-icon-wrap {
        width: 48px;
        height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        background: #fef2f2;
        color: #dc2626;
        font-size: 19px;
    }

    /* Mobile */
    @media (max-width: 767.98px) {
        #email-marketing-automations-root {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        .workflow-title {
            font-size: 16px;
        }

        .workflow-title-icon {
            width: 30px;
            height: 30px;
            font-size: 13px;
        }

        .workflow-subtitle {
            font-size: 10.5px;
        }

        .top-action-btns {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 7px !important;
        }

        .top-action-btns .btn {
            width: 100%;
            min-height: 34px;
            padding: 7px 5px !important;
            font-size: 10.5px !important;
        }

        .metric-card {
            min-height: 91px;
            padding: 10px !important;
        }

        .stat-label-title {
            font-size: 9.5px;
        }

        .stat-val-num {
            font-size: 17px;
        }

        .stat-sub-text {
            font-size: 9px;
        }

        .metric-icon-square {
            width: 27px;
            height: 27px;
            font-size: 11px;
        }

        .section-header-wrap {
            padding: 9px 10px;
        }

        .section-heading {
            font-size: 10.5px;
        }

        .pc-fit-container {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        .custom-workflow-table {
            min-width: 780px;
            table-layout: auto;
        }

        .custom-workflow-table thead th {
            padding: 10px 8px;
        }

        .custom-workflow-table tbody td {
            padding: 9px 8px;
        }

        #email-marketing-automations-root .card {
            margin-bottom: 11px;
        }

        #email-marketing-automations-root .card-footer nav {
            justify-content: center;
        }

        .filter-grid > div {
            min-width: 0;
        }
    }

    @media (max-width: 380px) {
        .stat-label-title {
            font-size: 9px;
        }

        .stat-val-num {
            font-size: 15px;
        }

        .metric-card {
            padding: 8px !important;
        }
    }

    /* Toast */
    .admin-automation-toast {
        position: fixed;
        right: 20px;
        bottom: 20px;
        z-index: 108000;
        min-width: 260px;
        max-width: calc(100vw - 30px);
        padding: 12px 15px;
        background: #fff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px rgba(15, 23, 42, .15);
        border-radius: 5px;
        animation: workflowToastIn .25s ease;
    }

    @keyframes workflowToastIn {
        from { transform: translateX(30px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative"
     id="email-marketing-automations-root">

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 workflow-header-wrap">

        <div class="d-flex align-items-center gap-2">
            <div class="workflow-title-icon">
                <i class="fas fa-diagram-project"></i>
            </div>

            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h5 class="workflow-title mb-0">
                        Automation &amp; Workflows
                    </h5>

                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace"
                          id="automations-total-badge"
                          style="font-size:10px;">
                        {{ number_format($totalAutomationsCount) }} Workflows
                    </span>
                </div>

                <p class="workflow-subtitle mb-0 mt-1">
                    Automate customer journeys, recover abandoned carts and improve customer retention.
                </p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 top-action-btns">
            <a href="{{ $logsRoute }}"
               class="btn btn-sm btn-outline-secondary btn-compact bg-white">
                <i class="fas fa-list-check"></i>
                Execution Logs
            </a>

            <a href="{{ $createRoute }}"
               class="btn btn-sm btn-primary btn-compact shadow-sm">
                <i class="fas fa-plus"></i>
                Create Workflow
            </a>
        </div>
    </div>

    {{-- SESSION MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small mb-3 d-flex align-items-center justify-content-between"
             role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-circle-check fs-5"></i>
                <span class="fw-semibold">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close shadow-none"
                    data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small mb-3 d-flex align-items-center justify-content-between"
             role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-circle-exclamation fs-5"></i>
                <span class="fw-semibold">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close shadow-none"
                    data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- KPI SECTION --}}
    <div class="card overflow-hidden mb-3">

        <div class="section-header-wrap d-flex justify-content-between align-items-center">
            <span class="section-heading">
                <i class="fas fa-chart-pie text-primary me-1"></i>
                Automation Engine Overview
            </span>

            <button type="button"
                    class="collapse-toggle-btn"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseWorkflowKpis"
                    aria-expanded="true"
                    aria-controls="collapseWorkflowKpis">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseWorkflowKpis">
            <div class="p-3">
                <div class="row g-2">

                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-blue">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="stat-label-title">Total Workflows</span>
                                <div class="metric-icon-square bg-primary-subtle text-primary">
                                    <i class="fas fa-sitemap"></i>
                                </div>
                            </div>
                            <h4 class="stat-val-num font-monospace">
                                {{ number_format($totalAutomationsCount) }}
                            </h4>
                            <small class="stat-sub-text">Configured customer journeys</small>
                        </div>
                    </div>

                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-green">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="stat-label-title">Active Engine</span>
                                <div class="metric-icon-square bg-success-subtle text-success">
                                    <i class="fas fa-bolt"></i>
                                </div>
                            </div>
                            <h4 class="stat-val-num text-success font-monospace">
                                {{ number_format($activeCount) }}
                            </h4>
                            <small class="stat-sub-text">Currently running workflows</small>
                        </div>
                    </div>

                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-amber">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="stat-label-title">Cart Recovery</span>
                                <div class="metric-icon-square bg-warning-subtle text-warning-emphasis">
                                    <i class="fas fa-cart-shopping"></i>
                                </div>
                            </div>
                            <h4 class="stat-val-num text-warning-emphasis font-monospace">
                                {{ number_format($cartRecoveryCount) }}
                            </h4>
                            <small class="stat-sub-text">Abandoned cart sequences</small>
                        </div>
                    </div>

                    <div class="col-6 col-xl-3">
                        <div class="card metric-card metric-card-cyan">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="stat-label-title">Customer Care</span>
                                <div class="metric-icon-square bg-info-subtle text-info">
                                    <i class="fas fa-heart"></i>
                                </div>
                            </div>
                            <h4 class="stat-val-num text-info font-monospace">
                                {{ number_format($customerCareCount) }}
                            </h4>
                            <small class="stat-sub-text">Customer lifecycle automation</small>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- FILTER SECTION --}}
    <div class="card overflow-hidden mb-3">

        <div class="section-header-wrap d-flex justify-content-between align-items-center">
            <span class="section-heading">
                <i class="fas fa-filter text-primary me-1"></i>
                Search &amp; Filter Workflows
            </span>

            <button type="button"
                    class="collapse-toggle-btn"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseWorkflowFilter"
                    aria-expanded="true"
                    aria-controls="collapseWorkflowFilter">
                <i class="fas fa-chevron-down collapse-icon"></i>
            </button>
        </div>

        <div class="collapse show" id="collapseWorkflowFilter">
            <div class="p-3">
                <div class="row g-2 align-items-end filter-grid">

                    <div class="col-12 col-md-5">
                        <label class="form-label" for="workflowSearchInput">
                            <i class="fas fa-search text-primary me-1"></i>
                            Search Workflow
                        </label>

                        <div class="input-group">
                            <span class="input-group-text workflow-addon-text border-end-0">
                                <i class="fas fa-search"></i>
                            </span>

                            <input type="text"
                                   id="workflowSearchInput"
                                   class="form-control workflow-filter-control border-start-0 ps-0"
                                   placeholder="Search by name or description..."
                                   oninput="filterWorkflowsTable()">
                        </div>
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label" for="workflowTriggerFilter">
                            <i class="fas fa-bolt text-warning me-1"></i>
                            Trigger Event
                        </label>

                        <select id="workflowTriggerFilter"
                                class="form-select workflow-filter-control"
                                onchange="filterWorkflowsTable()">
                            <option value="all">All Trigger Events</option>
                            <option value="abandoned_cart">Abandoned Cart Recovery</option>
                            <option value="welcome_subscriber">Welcome Subscriber</option>
                            <option value="first_purchase">First Purchase Welcome</option>
                            <option value="order_delivered">Order Delivered Follow-up</option>
                            <option value="win_back_inactivity">Win-Back Inactive Customers</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label" for="workflowStatusFilter">
                            <i class="fas fa-toggle-on text-success me-1"></i>
                            Engine Status
                        </label>

                        <select id="workflowStatusFilter"
                                class="form-select workflow-filter-control"
                                onchange="filterWorkflowsTable()">
                            <option value="all">All Statuses</option>
                            <option value="active">Active Engine</option>
                            <option value="inactive">Inactive / Paused</option>
                        </select>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- WORKFLOW TABLE --}}
    <div class="card overflow-hidden mb-3">

        <div class="p-3 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 bg-light bg-opacity-50">

            <div>
                <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2"
                    style="font-size:13px;">
                    <i class="fas fa-network-wired text-primary"></i>
                    Configured Automation Journeys
                </h6>

                <small class="text-muted font-monospace"
                       id="showing-entries-text"
                       style="font-size:10px;">
                    Showing
                    {{ method_exists($automations, 'firstItem') ? ($automations->firstItem() ?? 0) : 1 }}
                    to
                    {{ method_exists($automations, 'lastItem') ? ($automations->lastItem() ?? 0) : $automations->count() }}
                    of
                    {{ number_format($totalAutomationsCount) }} workflows
                </small>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-white text-secondary border px-2 py-2 font-monospace"
                      id="visible-workflow-count"
                      style="font-size:10px;">
                    {{ number_format($totalAutomationsCount) }} Total
                </span>

                <button type="button"
                        class="collapse-toggle-btn"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseWorkflowsTable"
                        aria-expanded="true"
                        aria-controls="collapseWorkflowsTable">
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="collapseWorkflowsTable">

            <div class="table-responsive pc-fit-container"
                 style="min-height:250px;">

                <table class="table align-middle custom-workflow-table"
                       id="workflows-table">

                    <thead>
                        <tr>
                            <th class="ps-3 col-wf-id text-center">#</th>
                            <th class="col-wf-name">Workflow Name &amp; Description</th>
                            <th class="col-wf-trigger">Trigger Event</th>
                            <th class="col-wf-steps text-center">Steps</th>
                            <th class="col-wf-status">Status</th>
                            <th class="col-wf-date">Created At</th>
                            <th class="col-wf-actions text-end pe-3">Actions</th>
                        </tr>
                    </thead>

                    <tbody id="workflows-tbody">

                        @forelse($automations as $workflow)

                            @php
                                $triggerBadge = match($workflow->trigger_event) {
                                    'abandoned_cart' => 'bg-warning-subtle text-dark border border-warning-subtle',
                                    'welcome_subscriber' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                    'first_purchase' => 'bg-success-subtle text-success border border-success-subtle',
                                    'order_delivered' => 'bg-info-subtle text-info border border-info-subtle',
                                    'win_back_inactivity' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    default => 'bg-light text-dark border',
                                };

                                $triggerIcon = match($workflow->trigger_event) {
                                    'abandoned_cart' => 'fa-cart-shopping',
                                    'welcome_subscriber' => 'fa-user-plus',
                                    'first_purchase' => 'fa-bag-shopping',
                                    'order_delivered' => 'fa-truck-fast',
                                    'win_back_inactivity' => 'fa-clock-rotate-left',
                                    default => 'fa-bolt',
                                };

                                $editWorkflowUrl = Route::has('admin.email_marketing.automations.edit')
                                    ? route('admin.email_marketing.automations.edit', $workflow->id)
                                    : url('admin/email-marketing/automations/' . $workflow->id . '/edit');

                                $toggleStatusUrl = Route::has('admin.email_marketing.automations.toggle_status')
                                    ? route('admin.email_marketing.automations.toggle_status', $workflow->id)
                                    : url('admin/email-marketing/automations/' . $workflow->id . '/toggle-status');

                                $destroyWorkflowUrl = Route::has('admin.email_marketing.automations.destroy')
                                    ? route('admin.email_marketing.automations.destroy', $workflow->id)
                                    : url('admin/email-marketing/automations/' . $workflow->id);

                                $workflowStepsCount = $workflow->steps_count
                                    ?? ($workflow->steps ? $workflow->steps->count() : 0);
                            @endphp

                            <tr class="workflow-row"
                                data-name="{{ strtolower($workflow->name . ' ' . ($workflow->description ?? '')) }}"
                                data-trigger="{{ strtolower($workflow->trigger_event) }}"
                                data-status="{{ $workflow->is_active ? 'active' : 'inactive' }}">

                                <td class="ps-3 text-center font-monospace text-muted col-wf-id"
                                    style="font-size:11px;">
                                    {{ $workflow->id }}
                                </td>

                                <td class="col-wf-name">
                                    <div class="min-w-0">
                                        <strong class="workflow-name text-truncate"
                                                title="{{ $workflow->name }}">
                                            {{ $workflow->name }}
                                        </strong>

                                        <small class="workflow-description text-truncate"
                                               title="{{ $workflow->description ?? 'No custom workflow description.' }}">
                                            {{ $workflow->description ?? 'No custom workflow description.' }}
                                        </small>
                                    </div>
                                </td>

                                <td class="col-wf-trigger">
                                    <span class="badge {{ $triggerBadge }} px-2 py-2 text-truncate d-inline-block"
                                          style="font-size:9.5px;max-width:100%;">
                                        <i class="fas {{ $triggerIcon }} me-1"></i>
                                        {{ ucfirst(str_replace('_', ' ', $workflow->trigger_event)) }}
                                    </span>
                                </td>

                                <td class="text-center col-wf-steps">
                                    <span class="badge bg-light text-dark border px-2 py-2 font-monospace"
                                          style="font-size:10px;">
                                        {{ $workflowStepsCount }}
                                        {{ Str::plural('Step', $workflowStepsCount) }}
                                    </span>
                                </td>

                                <td class="col-wf-status">
                                    @if($workflow->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-2"
                                              style="font-size:9.5px;">
                                            <i class="fas fa-circle-check me-1"></i>
                                            Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-2"
                                              style="font-size:9.5px;">
                                            <i class="fas fa-pause me-1"></i>
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <td class="col-wf-date font-monospace">
                                    <span class="d-block text-dark"
                                          style="font-size:11px;">
                                        {{ $workflow->created_at->format('M d, Y') }}
                                    </span>

                                    <small class="text-muted"
                                           style="font-size:9.5px;">
                                        {{ $workflow->created_at->format('h:i A') }}
                                    </small>
                                </td>

                                <td class="text-end pe-3 col-wf-actions">

                                    <div class="d-inline-flex align-items-center gap-1">

                                        {{-- STATUS TOGGLE --}}
                                        <form action="{{ $toggleStatusUrl }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf

                                            <button type="submit"
                                                    class="btn workflow-action-btn"
                                                    title="{{ $workflow->is_active ? 'Pause Automation' : 'Activate Automation' }}"
                                                    aria-label="{{ $workflow->is_active ? 'Pause Automation' : 'Activate Automation' }}">
                                                @if($workflow->is_active)
                                                    <i class="fas fa-pause text-warning"></i>
                                                @else
                                                    <i class="fas fa-play text-success"></i>
                                                @endif
                                            </button>
                                        </form>

                                        {{-- EDIT --}}
                                        <a href="{{ $editWorkflowUrl }}"
                                           class="btn workflow-action-btn text-primary"
                                           title="Edit Workflow"
                                           aria-label="Edit Workflow">
                                            <i class="fas fa-pencil"></i>
                                        </a>

                                        {{-- DELETE --}}
                                        <button type="button"
                                                class="btn workflow-action-btn text-danger"
                                                title="Delete Workflow"
                                                aria-label="Delete Workflow"
                                                onclick="triggerDeleteWorkflow(
                                                    {{ $workflow->id }},
                                                    @js($workflow->name),
                                                    @js($destroyWorkflowUrl)
                                                )">
                                            <i class="fas fa-trash-can"></i>
                                        </button>

                                    </div>
                                </td>
                            </tr>

                        @empty

                            <tr id="emptyWorkflowsRow">
                                <td colspan="7" class="workflow-empty-state">
                                    <div class="workflow-empty-icon">
                                        <i class="fas fa-diagram-project"></i>
                                    </div>

                                    <strong class="d-block text-dark mb-1"
                                            style="font-size:13px;">
                                        No Automation Workflows Found
                                    </strong>

                                    <span class="d-block mb-3"
                                          style="font-size:11px;">
                                        Start automating your customer email journeys.
                                    </span>

                                    <a href="{{ $createRoute }}"
                                       class="btn btn-primary btn-compact">
                                        <i class="fas fa-plus"></i>
                                        Create Your First Workflow
                                    </a>
                                </td>
                            </tr>

                        @endforelse

                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            @if(method_exists($automations, 'hasPages') && $automations->hasPages())
                <div class="card-footer p-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50">

                    <small class="text-muted font-monospace"
                           style="font-size:10.5px;">
                        Showing {{ $automations->firstItem() }}
                        to {{ $automations->lastItem() }}
                        of {{ number_format($automations->total()) }} workflows
                    </small>

                    <div>
                        {{ $automations->links('pagination::bootstrap-5') }}
                    </div>

                </div>
            @endif

        </div>
    </div>
</div>

{{-- DELETE CONFIRMATION MODAL --}}
<div class="modal fade workflow-modal-scope"
     id="deleteWorkflowModal"
     tabindex="-1"
     aria-labelledby="deleteWorkflowModalTitle"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg text-center">

            <div class="modal-body p-3">

                <div class="delete-icon-wrap mx-auto mb-3">
                    <i class="fas fa-trash-can"></i>
                </div>

                <h6 class="fw-bold text-dark mb-2"
                    id="deleteWorkflowModalTitle"
                    style="font-size:14px;">
                    Delete Workflow?
                </h6>

                <p class="text-muted mb-3"
                   style="font-size:11px;line-height:1.6;">
                    Are you sure you want to permanently remove
                    <strong id="deleteWorkflowTitle" class="text-dark">this workflow</strong>?
                    All scheduled queue executions will be halted.
                </p>

                <form id="deleteWorkflowForm"
                      method="POST"
                      action=""
                      onsubmit="lockDeleteBtn(this)">

                    @csrf
                    @method('DELETE')

                    <div class="d-flex justify-content-center gap-2">
                        <button type="button"
                                class="btn btn-light border btn-compact"
                                data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button type="submit"
                                class="btn btn-danger btn-compact submit-btn">
                            <i class="fas fa-trash-can me-1"></i>
                            Yes, Delete
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const root = document.getElementById('email-marketing-automations-root');

    if (!root) return;

    /* =========================================
       DELETE WORKFLOW
    ========================================= */

    window.triggerDeleteWorkflow = function (id, name, formUrl) {
        const title = document.getElementById('deleteWorkflowTitle');
        const form = document.getElementById('deleteWorkflowForm');
        const modalEl = document.getElementById('deleteWorkflowModal');

        if (!title || !form || !modalEl) return;

        title.textContent = name || 'this workflow';
        form.action = formUrl;

        const submitBtn = form.querySelector('.submit-btn');

        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-trash-can me-1"></i> Yes, Delete';
        }

        if (window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    };

    /* =========================================
       DELETE BUTTON LOCK
    ========================================= */

    window.lockDeleteBtn = function (form) {
        const btn = form.querySelector('.submit-btn');

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Deleting...';
        }
    };

    /* =========================================
       TABLE FILTERING
    ========================================= */

    window.filterWorkflowsTable = function () {
        const searchInput = document.getElementById('workflowSearchInput');
        const triggerInput = document.getElementById('workflowTriggerFilter');
        const statusInput = document.getElementById('workflowStatusFilter');

        const query = (searchInput?.value || '').toLowerCase().trim();
        const trigger = (triggerInput?.value || 'all').toLowerCase();
        const status = (statusInput?.value || 'all').toLowerCase();

        const rows = root.querySelectorAll('.workflow-row');

        let visibleCount = 0;

        rows.forEach(function (row) {
            const rowName = (row.dataset.name || '').toLowerCase();
            const rowTrigger = (row.dataset.trigger || '').toLowerCase();
            const rowStatus = (row.dataset.status || '').toLowerCase();

            const matchesQuery = !query || rowName.includes(query);
            const matchesTrigger = trigger === 'all' || rowTrigger === trigger;
            const matchesStatus = status === 'all' || rowStatus === status;

            const isVisible = matchesQuery && matchesTrigger && matchesStatus;

            row.style.display = isVisible ? '' : 'none';

            if (isVisible) visibleCount++;
        });

        const countBadge = document.getElementById('visible-workflow-count');

        if (countBadge) {
            countBadge.textContent = visibleCount + ' Showing';
        }

        const emptyRow = document.getElementById('workflowFilterEmptyRow');

        if (emptyRow) emptyRow.remove();

        if (rows.length > 0 && visibleCount === 0) {
            const tbody = document.getElementById('workflows-tbody');

            if (tbody) {
                const tr = document.createElement('tr');
                tr.id = 'workflowFilterEmptyRow';

                const td = document.createElement('td');
                td.colSpan = 7;
                td.className = 'workflow-empty-state';

                td.innerHTML = `
                    <div class="workflow-empty-icon">
                        <i class="fas fa-filter"></i>
                    </div>
                    <strong class="d-block text-dark mb-1" style="font-size:13px;">
                        No Matching Workflows
                    </strong>
                    <span style="font-size:11px;">
                        Try changing your search or filter criteria.
                    </span>
                `;

                tr.appendChild(td);
                tbody.appendChild(tr);
            }
        }
    };

})();
</script>

@endsection
