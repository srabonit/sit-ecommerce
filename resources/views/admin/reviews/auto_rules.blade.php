@extends('layouts.admin')

@section('title', 'Smart Review Auto-Reply Automation - ' . config('app.name'))

@php
    $totalRulesCount    = count($rules ?? []);
    $activeRulesCount   = collect($rules ?? [])->where('is_active', true)->count();
    $verifiedOnlyCount  = collect($rules ?? [])->where('verified_only', true)->count();
    $ratingTriggerCount = collect($rules ?? [])->filter(function($r) {
        $arr = !empty($r->ratings_json) ? json_decode($r->ratings_json, true) : [];
        return is_array($arr) && count($arr) > 0;
    })->count();
@endphp

@section('admin_content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
    #autoRulesRoot,
    .rule-modal-scope {
        --ar-primary: #2563eb;
        --ar-primary-soft: #eff6ff;
        --ar-success: #16a34a;
        --ar-success-soft: #f0fdf4;
        --ar-warning: #d97706;
        --ar-warning-soft: #fffbeb;
        --ar-purple: #7c3aed;
        --ar-purple-soft: #f5f3ff;
        --ar-danger: #dc2626;
        --ar-text: #172033;
        --ar-muted: #64748b;
        --ar-border: #e2e8f0;
        --ar-surface: #ffffff;
        --ar-soft: #f8fafc;

        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
        color: var(--ar-text);
        letter-spacing: -0.01em;
    }

    #autoRulesRoot *,
    .rule-modal-scope * {
        box-sizing: border-box;
    }

    #autoRulesRoot .btn,
    #autoRulesRoot button,
    #autoRulesRoot .badge,
    #autoRulesRoot .form-control,
    #autoRulesRoot .form-select,
    #autoRulesRoot .card,
    #autoRulesRoot .rules-header-card,
    #autoRulesRoot .rules-panel-card,
    #autoRulesRoot .var-badge,
    #autoRulesRoot .alert,
    .rule-modal-scope .modal-content,
    .rule-modal-scope .btn,
    .rule-modal-scope .form-control,
    .rule-modal-scope .form-select,
    .rule-modal-scope .badge {
        border-radius: 5px !important;
    }

    #autoRulesRoot .font-monospace,
    #autoRulesRoot .bn-digit,
    .rule-modal-scope .font-monospace {
        font-family: 'Inter', sans-serif !important;
        font-variant-numeric: tabular-nums;
    }

    #autoRulesRoot .btn,
    .rule-modal-scope .btn {
        font-family: inherit;
        font-weight: 600;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease, transform .18s ease;
    }

    #autoRulesRoot .btn-compact,
    .rule-modal-scope .btn-compact {
        min-height: 32px;
        padding: 6px 11px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        font-size: 11px !important;
        line-height: 1.3;
        white-space: nowrap;
    }

    #autoRulesRoot .btn-success,
    .rule-modal-scope .btn-success {
        background: #16a34a !important;
        border-color: #16a34a !important;
        color: #fff !important;
    }

    #autoRulesRoot .btn-success:hover,
    .rule-modal-scope .btn-success:hover {
        background: #15803d !important;
        border-color: #15803d !important;
        color: #fff !important;
    }

    #autoRulesRoot .btn-outline-secondary,
    #autoRulesRoot .btn-outline-dark,
    #autoRulesRoot .btn-light,
    .rule-modal-scope .btn-light {
        background: #fff !important;
        border-color: #cbd5e1 !important;
        color: #334155 !important;
    }

    #autoRulesRoot .btn-outline-secondary:hover,
    #autoRulesRoot .btn-outline-dark:hover,
    #autoRulesRoot .btn-light:hover,
    .rule-modal-scope .btn-light:hover {
        background: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }

    #autoRulesRoot .btn-danger,
    .rule-modal-scope .btn-danger {
        background: #dc2626 !important;
        border-color: #dc2626 !important;
        color: #fff !important;
    }

    #autoRulesRoot .btn-danger:hover,
    .rule-modal-scope .btn-danger:hover {
        background: #b91c1c !important;
        border-color: #b91c1c !important;
        color: #fff !important;
    }

    #autoRulesRoot .rules-header-card {
        background: var(--ar-surface);
        border: 1px solid var(--ar-border);
        border-radius: 5px;
        padding: 16px 18px;
        margin-bottom: 14px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
    }

    #autoRulesRoot .page-heading {
        display: flex;
        align-items: center;
        gap: 11px;
    }

    #autoRulesRoot .page-heading-icon {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: var(--ar-success-soft);
        color: var(--ar-success);
        border: 1px solid #dcfce7;
        border-radius: 5px;
        font-size: 16px;
    }

    #autoRulesRoot .page-heading h5 {
        font-size: 16px;
        font-weight: 800;
        color: var(--ar-text);
        margin: 0;
        line-height: 1.35;
    }

    #autoRulesRoot .page-heading p {
        color: var(--ar-muted);
        font-size: 11px;
        margin: 3px 0 0;
        line-height: 1.5;
    }

    #autoRulesRoot .header-actions-group {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 7px;
    }

    #autoRulesRoot .metric-card {
        height: 100%;
        min-height: 94px;
        padding: 13px 14px;
        background: #fff;
        border: 1px solid var(--ar-border);
        border-left: 3px solid transparent !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
        transition: transform .18s ease, box-shadow .18s ease;
    }

    #autoRulesRoot .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(15, 23, 42, .065);
    }

    #autoRulesRoot .metric-card-blue {
        border-left-color: #2563eb !important;
        background: linear-gradient(120deg, #fff 55%, #f0f7ff);
    }

    #autoRulesRoot .metric-card-green {
        border-left-color: #16a34a !important;
        background: linear-gradient(120deg, #fff 55%, #f0fdf4);
    }

    #autoRulesRoot .metric-card-amber {
        border-left-color: #d97706 !important;
        background: linear-gradient(120deg, #fff 55%, #fffbeb);
    }

    #autoRulesRoot .metric-card-purple {
        border-left-color: #7c3aed !important;
        background: linear-gradient(120deg, #fff 55%, #f5f3ff);
    }

    #autoRulesRoot .metric-icon-square {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 5px;
        font-size: 13px;
    }

    #autoRulesRoot .stat-label-title {
        color: var(--ar-muted);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .025em;
        text-transform: uppercase;
    }

    #autoRulesRoot .stat-val-num {
        color: var(--ar-text);
        font-size: 21px;
        font-weight: 800;
        line-height: 1.3;
        margin: 0;
    }

    #autoRulesRoot .stat-sub-text {
        display: block;
        color: var(--ar-muted);
        font-size: 10px;
        line-height: 1.4;
        margin-top: 2px;
    }

    #autoRulesRoot .rules-panel-card {
        background: #fff;
        border: 1px solid var(--ar-border);
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
        margin-bottom: 14px;
    }

    #autoRulesRoot .section-heading {
        color: var(--ar-text);
        font-size: 12px;
        font-weight: 800;
        margin: 0;
    }

    #autoRulesRoot .section-description {
        color: var(--ar-muted);
        font-size: 10px;
        margin: 4px 0 0;
        line-height: 1.5;
    }

    #autoRulesRoot .variable-guide {
        padding: 13px 15px;
    }

    #autoRulesRoot .variable-guide-heading {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 5px;
    }

    #autoRulesRoot .variable-guide-heading i {
        color: var(--ar-primary);
        font-size: 13px;
    }

    #autoRulesRoot .var-badge {
        display: inline-flex;
        align-items: center;
        margin: 0 5px 5px 0;
        padding: 5px 8px;
        background: #f8fafc;
        border: 1px solid #dbe3ed;
        color: #334155;
        font-family: 'Inter', sans-serif;
        font-size: 10px;
        font-weight: 600;
        cursor: pointer;
        user-select: none;
        transition: all .15s ease;
    }

    #autoRulesRoot .var-badge:hover,
    .rule-modal-scope .var-badge:hover {
        background: #eff6ff !important;
        border-color: #93c5fd !important;
        color: #1d4ed8 !important;
    }

    #autoRulesRoot .rules-table-header {
        min-height: 48px;
        padding: 10px 14px;
        background: #fff;
        border-bottom: 1px solid var(--ar-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }

    #autoRulesRoot .table-count {
        padding: 5px 9px;
        background: #f8fafc;
        border: 1px solid var(--ar-border);
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    #autoRulesRoot .table-responsive {
        width: 100%;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }

    #autoRulesRoot .custom-rules-table {
        width: 100% !important;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #autoRulesRoot .custom-rules-table thead th {
        padding: 10px 11px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        color: #64748b;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .045em;
        white-space: nowrap;
    }

    #autoRulesRoot .custom-rules-table tbody td {
        padding: 11px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 11px;
        vertical-align: middle;
    }

    #autoRulesRoot .custom-rules-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #autoRulesRoot .custom-rules-table tbody tr:hover td {
        background: #fbfdff;
    }

    #autoRulesRoot .rule-name {
        display: block;
        color: #172033;
        font-size: 11px;
        font-weight: 800;
        line-height: 1.45;
    }

    #autoRulesRoot .rule-subtext {
        display: block;
        margin-top: 4px;
        color: #94a3b8;
        font-size: 9px;
        line-height: 1.4;
    }

    #autoRulesRoot .condition-badge,
    #autoRulesRoot .trigger-badge {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 4px 6px;
        border: 1px solid transparent;
        border-radius: 5px;
        font-size: 9px;
        font-weight: 700;
        line-height: 1.2;
        white-space: nowrap;
    }

    #autoRulesRoot .condition-star {
        background: #fffbeb;
        border-color: #fde68a;
        color: #92400e;
    }

    #autoRulesRoot .condition-neutral {
        background: #f1f5f9;
        border-color: #e2e8f0;
        color: #64748b;
    }

    #autoRulesRoot .condition-verified {
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: #15803d;
        margin-top: 5px;
    }

    #autoRulesRoot .trigger-badge {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #1d4ed8;
        text-transform: uppercase;
    }

    #autoRulesRoot .template-box {
        padding: 9px 10px;
        background: #f8fafc;
        border: 1px solid #e8edf4;
        border-radius: 5px;
        color: #475569;
        font-family: 'Inter', sans-serif;
        font-size: 10px;
        line-height: 1.55;
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    #autoRulesRoot .rule-status-btn {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e2e8f0 !important;
        border-radius: 5px;
        background: #fff;
        transition: all .15s ease;
    }

    #autoRulesRoot .rule-status-btn:hover {
        background: #f8fafc;
        border-color: #cbd5e1 !important;
    }

    #autoRulesRoot .rule-status-btn i {
        font-size: 17px;
    }

    #autoRulesRoot .delete-rule-btn {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 !important;
        border: 1px solid #fee2e2 !important;
        background: #fff7f7 !important;
        color: #dc2626 !important;
        font-size: 11px !important;
    }

    #autoRulesRoot .delete-rule-btn:hover {
        background: #dc2626 !important;
        border-color: #dc2626 !important;
        color: #fff !important;
    }

    #autoRulesRoot .empty-state {
        padding: 42px 20px !important;
        text-align: center;
    }

    #autoRulesRoot .empty-state-icon {
        width: 52px;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px;
        border-radius: 5px;
        background: #f1f5f9;
        color: #94a3b8;
        font-size: 22px;
    }

    #autoRulesRoot .form-control,
    #autoRulesRoot .form-select,
    .rule-modal-scope .form-control,
    .rule-modal-scope .form-select {
        min-height: 36px;
        padding: 7px 10px;
        border: 1px solid #cbd5e1;
        border-radius: 5px !important;
        background-color: #fff;
        color: #172033;
        box-shadow: none;
        font-family: 'Inter', sans-serif;
        font-size: 11px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #autoRulesRoot .form-control:focus,
    #autoRulesRoot .form-select:focus,
    .rule-modal-scope .form-control:focus,
    .rule-modal-scope .form-select:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
        outline: none;
    }

    .rule-modal-scope {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
    }

    .rule-modal-scope .modal-content {
        overflow: hidden;
        border: 1px solid #e2e8f0 !important;
        background: #fff;
        box-shadow: 0 18px 50px rgba(15, 23, 42, .18);
    }

    .rule-modal-scope .modal-header,
    .rule-modal-scope .modal-footer {
        background: #f8fafc;
        border-color: #e8edf4;
    }

    .rule-modal-scope .modal-title {
        color: #172033;
        font-size: 13px;
        font-weight: 800;
    }

    .rule-modal-scope .modal-body {
        padding: 18px;
    }

    .rule-modal-scope .form-label {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: 10px;
        font-weight: 700;
    }

    .rule-modal-scope .form-text,
    .rule-modal-scope small {
        font-size: 10px;
    }

    .rule-modal-scope .form-check {
        display: flex;
        align-items: center;
        gap: 7px;
        min-height: 25px;
    }

    .rule-modal-scope .form-check-input {
        margin: 0;
        flex-shrink: 0;
        cursor: pointer;
    }

    .rule-modal-scope .form-check-label {
        color: #475569;
        font-size: 10px;
        font-weight: 600;
        cursor: pointer;
    }

    .rule-modal-scope .var-badge {
        display: inline-flex;
        margin: 0 4px 5px 0;
        padding: 4px 7px;
        border: 1px solid #dbe3ed;
        border-radius: 5px;
        background: #f8fafc;
        color: #334155;
        font-family: 'Inter', sans-serif;
        font-size: 9px;
        font-weight: 600;
        cursor: pointer;
        user-select: none;
    }

    .rule-modal-scope .rating-box {
        padding: 11px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 5px;
        background: #f8fafc;
    }

    .rule-modal-scope .rating-options {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 18px;
    }

    .rule-modal-scope .approval-options {
        padding-top: 12px;
        border-top: 1px solid #e2e8f0;
    }

    #deleteRuleConfirmModal .modal-body {
        padding: 24px 18px;
    }

    #deleteRuleConfirmModal .delete-icon {
        width: 52px;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 13px;
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fee2e2;
        border-radius: 5px;
        font-size: 20px;
    }

    @media (min-width: 1200px) {
        #autoRulesRoot .custom-rules-table {
            table-layout: fixed;
        }

        #autoRulesRoot .col-rule-name { width: 19%; }
        #autoRulesRoot .col-rule-conditions { width: 20%; }
        #autoRulesRoot .col-rule-trigger { width: 14%; }
        #autoRulesRoot .col-rule-template { width: 31%; }
        #autoRulesRoot .col-rule-status { width: 8%; }
        #autoRulesRoot .col-rule-actions { width: 8%; }
    }

    @media (max-width: 1199.98px) {
        #autoRulesRoot .custom-rules-table {
            min-width: 920px;
            table-layout: auto;
        }

        #autoRulesRoot .col-rule-name { min-width: 175px; }
        #autoRulesRoot .col-rule-conditions { min-width: 165px; }
        #autoRulesRoot .col-rule-trigger { min-width: 135px; }
        #autoRulesRoot .col-rule-template { min-width: 280px; }
        #autoRulesRoot .col-rule-status { min-width: 75px; }
        #autoRulesRoot .col-rule-actions { min-width: 75px; }
    }

    @media (max-width: 767.98px) {
        #autoRulesRoot {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }

        #autoRulesRoot .rules-header-card {
            padding: 13px !important;
        }

        #autoRulesRoot .header-actions-group {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 7px;
        }

        #autoRulesRoot .header-actions-group .btn {
            width: 100%;
            min-width: 0;
            min-height: 35px;
            padding: 7px 5px !important;
            font-size: 10px !important;
        }

        #autoRulesRoot .header-actions-group .btn-create-rule {
            grid-column: 1 / -1;
        }

        #autoRulesRoot .page-heading h5 {
            font-size: 15px;
        }

        #autoRulesRoot .metric-card {
            min-height: 85px;
            padding: 10px !important;
        }

        #autoRulesRoot .metric-icon-square {
            width: 27px;
            height: 27px;
            font-size: 11px;
        }

        #autoRulesRoot .stat-label-title {
            font-size: 9px;
        }

        #autoRulesRoot .stat-val-num {
            font-size: 17px;
        }

        #autoRulesRoot .stat-sub-text {
            font-size: 9px;
        }

        #autoRulesRoot .rules-table-header {
            align-items: flex-start;
            padding: 11px;
        }

        #autoRulesRoot .variable-guide {
            padding: 12px;
        }

        .rule-modal-scope .modal-dialog {
            margin: 10px;
        }

        .rule-modal-scope .modal-body {
            padding: 14px;
        }

        .rule-modal-scope .rating-options {
            gap: 10px 13px;
        }
    }

    @media (max-width: 380px) {
        #autoRulesRoot .metric-card {
            padding: 8px !important;
        }

        #autoRulesRoot .stat-val-num {
            font-size: 15px;
        }

        #autoRulesRoot .stat-label-title {
            font-size: 8px;
        }

        #autoRulesRoot .header-actions-group .btn {
            font-size: 9px !important;
        }
    }
</style>

<div class="container-fluid px-2 px-md-3 py-3" id="autoRulesRoot">

    {{-- Page Header --}}
    <div class="rules-header-card d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
        <div class="page-heading">
            <div class="page-heading-icon">
                <i class="fas fa-robot"></i>
            </div>
            <div>
                <h5>Review Auto-Reply</h5>
                <p>Manage automated responses based on customer ratings and review activity.</p>
            </div>
        </div>

        <div class="header-actions-group">
            <button type="button"
                    class="btn btn-success btn-compact btn-create-rule"
                    data-bs-toggle="modal"
                    data-bs-target="#createRuleModal">
                <i class="fas fa-plus"></i>
                Create Auto-Rule
            </button>

            <a href="{{ route('admin.product.reviews.index') }}"
               class="btn btn-outline-secondary btn-compact">
                <i class="fas fa-arrow-left text-primary"></i>
                Reviews Table
            </a>

            <a href="{{ route('admin.product.reviews.settings') }}"
               class="btn btn-outline-dark btn-compact">
                <i class="fas fa-gear"></i>
                Review Settings
            </a>
        </div>
    </div>

    {{-- KPI Overview --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-blue">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <span class="stat-label-title text-truncate">Total Rules</span>
                    <div class="metric-icon-square bg-primary-subtle text-primary">
                        <i class="fas fa-list-check"></i>
                    </div>
                </div>
                <div>
                    <h4 class="stat-val-num bn-digit">{{ number_format($totalRulesCount) }}</h4>
                    <small class="stat-sub-text">Configured automation presets</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-green">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <span class="stat-label-title text-truncate">Active Rules</span>
                    <div class="metric-icon-square bg-success-subtle text-success">
                        <i class="fas fa-circle-check"></i>
                    </div>
                </div>
                <div>
                    <h4 class="stat-val-num bn-digit text-success">{{ number_format($activeRulesCount) }}</h4>
                    <small class="stat-sub-text text-success">
                        <i class="fas fa-bolt me-1"></i>Active trigger bots
                    </small>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-amber">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <span class="stat-label-title text-truncate">Rating Triggers</span>
                    <div class="metric-icon-square bg-warning-subtle text-warning-emphasis">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div>
                    <h4 class="stat-val-num bn-digit" style="color:#b45309;">
                        {{ number_format($ratingTriggerCount) }}
                    </h4>
                    <small class="stat-sub-text">Star-score bound rules</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-purple">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <span class="stat-label-title text-truncate">Verified Specific</span>
                    <div class="metric-icon-square" style="background:#f3e8ff;color:#7e22ce;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
                <div>
                    <h4 class="stat-val-num bn-digit" style="color:#7e22ce;">
                        {{ number_format($verifiedOnlyCount) }}
                    </h4>
                    <small class="stat-sub-text">Targeting actual buyers only</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Feedback Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small border-0 shadow-sm d-flex align-items-center justify-content-between mb-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success"></i>
                <span class="fw-semibold">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-sm d-flex align-items-center justify-content-between mb-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle text-danger"></i>
                <span class="fw-semibold">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger py-2 px-3 small border-0 shadow-sm mb-3">
            <div class="fw-bold mb-1">
                <i class="fas fa-circle-exclamation me-1"></i>
                Please check the following:
            </div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Available Template Variables --}}
    <div class="rules-panel-card variable-guide">
        <div class="variable-guide-heading">
            <i class="fas fa-code"></i>
            <h6 class="section-heading">Available Template Variables</h6>
        </div>

        <p class="section-description mb-2">
            Click a variable to insert it into the auto-reply message template.
        </p>

        <div class="d-flex flex-wrap">
            <span class="var-badge" onclick="insertVariable('{customer_name}')" title="Full Customer Name">{customer_name}</span>
            <span class="var-badge" onclick="insertVariable('{customer_first}')" title="First Name Only">{customer_first}</span>
            <span class="var-badge" onclick="insertVariable('{product_name}')" title="Product Title">{product_name}</span>
            <span class="var-badge" onclick="insertVariable('{rating}')" title="Rating Given (1-5)">{rating}</span>
            <span class="var-badge" onclick="insertVariable('{store_name}')" title="Store Title">{store_name}</span>
            <span class="var-badge" onclick="insertVariable('{support_contact}')" title="Support Phone Number">{support_contact}</span>
            <span class="var-badge" onclick="insertVariable('{review_date}')" title="Review Date">{review_date}</span>
        </div>
    </div>

    {{-- Automation Rules Table --}}
    <div class="rules-panel-card overflow-hidden">
        <div class="rules-table-header">
            <div>
                <h6 class="section-heading">
                    <i class="fas fa-list-check text-primary me-1"></i>
                    Automation Rules
                </h6>
                <p class="section-description">Review matching conditions, response templates and rule status.</p>
            </div>

            <span class="table-count bn-digit">
                {{ count($rules) }} Rules
            </span>
        </div>

        <div class="table-responsive">
            <table class="table custom-rules-table align-middle">
                <thead>
                    <tr>
                        <th class="col-rule-name">Rule Name</th>
                        <th class="col-rule-conditions">Matching Conditions</th>
                        <th class="col-rule-trigger">Trigger Event</th>
                        <th class="col-rule-template">Auto-Reply Template</th>
                        <th class="text-center col-rule-status">Status</th>
                        <th class="text-center col-rule-actions">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($rules as $r)
                        @php
                            $ratingsArr = !empty($r->ratings_json) ? json_decode($r->ratings_json, true) : [];
                            $keywordsArr = !empty($r->keywords_json) ? json_decode($r->keywords_json, true) : [];
                        @endphp

                        <tr>
                            {{-- Rule Name --}}
                            <td class="col-rule-name">
                                <strong class="rule-name" title="{{ $r->name }}">
                                    {{ $r->name }}
                                </strong>

                                <small class="rule-subtext" title="{{ $r->product_title ?: 'All Store Products' }}">
                                    <i class="fas fa-box-open me-1"></i>
                                    Target: {{ $r->product_title ? Str::limit($r->product_title, 25) : 'All Store Products' }}
                                </small>
                            </td>

                            {{-- Matching Conditions --}}
                            <td class="col-rule-conditions">
                                @if(is_array($ratingsArr) && count($ratingsArr) > 0)
                                    <div class="d-flex flex-wrap gap-1 mb-1">
                                        @foreach($ratingsArr as $star)
                                            <span class="condition-badge condition-star">
                                                <i class="fas fa-star"></i>{{ $star }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="condition-badge condition-neutral">Any Rating</span>
                                @endif

                                @if(is_array($keywordsArr) && count($keywordsArr) > 0)
                                    <small class="rule-subtext mt-2"
                                           title="{{ implode(', ', $keywordsArr) }}"
                                           style="overflow-wrap:anywhere;">
                                        <strong class="text-secondary">Keywords:</strong>
                                        {{ implode(', ', $keywordsArr) }}
                                    </small>
                                @endif

                                @if($r->verified_only)
                                    <div>
                                        <span class="condition-badge condition-verified">
                                            <i class="fas fa-shield-check"></i>
                                            Verified Only
                                        </span>
                                    </div>
                                @endif
                            </td>

                            {{-- Trigger Event --}}
                            <td class="col-rule-trigger">
                                <span class="trigger-badge">
                                    {{ str_replace('_', ' ', $r->trigger_event) }}
                                </span>

                                @if($r->require_admin_approval)
                                    <small class="rule-subtext mt-2" style="color:#dc2626;">
                                        <i class="fas fa-lock me-1"></i>
                                        Requires Mod Approval
                                    </small>
                                @endif
                            </td>

                            {{-- Auto-Reply Template --}}
                            <td class="col-rule-template">
                                <div class="template-box">
                                    {{ $r->template_text }}
                                </div>
                            </td>

                            {{-- Active Status Toggle --}}
                            <td class="text-center col-rule-status">
                                <form action="{{ route('admin.product.reviews.auto_rules.toggle', $r->id) }}"
                                      method="POST"
                                      class="d-inline">
                                    @csrf

                                    <button type="submit"
                                            class="rule-status-btn"
                                            title="Toggle Status"
                                            aria-label="Toggle rule status">
                                        <i class="fas fa-toggle-{{ $r->is_active ? 'on text-success' : 'off text-muted' }}"></i>
                                    </button>
                                </form>
                            </td>

                            {{-- Delete Action --}}
                            <td class="text-center col-rule-actions">
                                <button type="button"
                                        class="btn delete-rule-btn"
                                        title="Delete Rule"
                                        aria-label="Delete rule"
                                        onclick="openDeleteRuleModal({{ $r->id }}, '{{ addslashes($r->name) }}')">
                                    <i class="fas fa-trash-can"></i>
                                </button>

                                <form id="delete-rule-form-{{ $r->id }}"
                                      action="{{ route('admin.product.reviews.auto_rules.delete', $r->id) }}"
                                      method="POST"
                                      class="d-none">
                                    @csrf
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1" style="font-size:13px;">
                                    No Automation Rules Yet
                                </h6>
                                <p class="text-muted mb-3" style="font-size:11px;">
                                    Create your first rule to automate customer review responses.
                                </p>
                                <button type="button"
                                        class="btn btn-success btn-compact"
                                        data-bs-toggle="modal"
                                        data-bs-target="#createRuleModal">
                                    <i class="fas fa-plus"></i>
                                    Create Auto-Rule
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Create Automation Rule Modal --}}
<div class="modal fade rule-modal-scope"
     id="createRuleModal"
     tabindex="-1"
     aria-labelledby="createRuleModalTitle"
     aria-hidden="true"
     style="z-index:108000;">

    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header py-3 px-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="page-heading-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div>
                        <h6 class="modal-title" id="createRuleModalTitle">New Review Auto-Reply Rule</h6>
                        <small class="text-muted">Configure when and how automatic replies are generated.</small>
                    </div>
                </div>

                <button type="button"
                        class="btn-close shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <form action="{{ route('admin.product.reviews.auto_rules.store') }}" method="POST">
                @csrf

                <div class="modal-body">
                    {{-- Basic Rule Details --}}
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label">Rule Label / Name *</label>
                            <input type="text"
                                   name="name"
                                   class="form-control"
                                   placeholder="5-Star Instant Thank You"
                                   required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Trigger Event *</label>
                            <select name="trigger_event" class="form-select font-monospace">
                                <option value="on_submission" selected>On Review Submission</option>
                                <option value="on_approved">After Manual Approval</option>
                                <option value="both">Both Events</option>
                            </select>
                        </div>
                    </div>

                    {{-- Target Ratings --}}
                    <div class="rating-box mb-3">
                        <label class="form-label mb-2">Apply to Star Ratings</label>

                        <div class="rating-options">
                            @for($star = 5; $star >= 1; $star--)
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="ratings[]"
                                           value="{{ $star }}"
                                           id="ratingCheck{{ $star }}"
                                           {{ $star >= 4 ? 'checked' : '' }}>

                                    <label class="form-check-label" for="ratingCheck{{ $star }}">
                                        <span class="text-warning">★</span>
                                        {{ $star }} Star
                                    </label>
                                </div>
                            @endfor
                        </div>
                    </div>

                    {{-- Keywords --}}
                    <div class="mb-3">
                        <label class="form-label">Trigger Keywords <span class="text-muted fw-normal">(Optional — comma separated)</span></label>
                        <input type="text"
                               name="keywords"
                               class="form-control font-monospace"
                               placeholder="awesome, recommended, authentic, quick delivery">

                        <small class="text-muted d-block mt-1">
                            Leave blank to respond based purely on star rating.
                        </small>
                    </div>

                    {{-- Reply Template --}}
                    <div class="mb-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-2">
                            <label class="form-label mb-0">Auto-Reply Message Template *</label>
                            <small class="text-muted">Click a tag to insert it</small>
                        </div>

                        <div class="d-flex flex-wrap mb-2">
                            <span class="var-badge" onclick="insertVariable('{customer_name}')">{customer_name}</span>
                            <span class="var-badge" onclick="insertVariable('{customer_first}')">{customer_first}</span>
                            <span class="var-badge" onclick="insertVariable('{product_name}')">{product_name}</span>
                            <span class="var-badge" onclick="insertVariable('{rating}')">{rating}</span>
                            <span class="var-badge" onclick="insertVariable('{store_name}')">{store_name}</span>
                            <span class="var-badge" onclick="insertVariable('{support_contact}')">{support_contact}</span>
                            <span class="var-badge" onclick="insertVariable('{review_date}')">{review_date}</span>
                        </div>

                        <textarea id="modalTemplateInput"
                                  name="template_text"
                                  rows="4"
                                  class="form-control font-monospace"
                                  placeholder="Dear {customer_name}, thank you for your feedback!..."
                                  required
                                  style="resize:vertical;min-height:100px;"></textarea>
                    </div>

                    {{-- Rule Options --}}
                    <div class="approval-options">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="verified_only"
                                           id="mVerifiedOnly"
                                           value="1">
                                    <label class="form-check-label" for="mVerifiedOnly">
                                        Apply to Verified Buyers Only
                                    </label>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="require_admin_approval"
                                           id="mRequireApproval"
                                           value="1">
                                    <label class="form-check-label" for="mRequireApproval">
                                        Hold in Queue for Moderation
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer px-3 py-3">
                    <button type="button"
                            class="btn btn-light btn-compact px-3"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-success btn-compact px-4">
                        <i class="fas fa-check-circle"></i>
                        Save Automation Rule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Rule Confirmation Modal --}}
<div class="modal fade rule-modal-scope"
     id="deleteRuleConfirmModal"
     tabindex="-1"
     aria-labelledby="deleteRuleConfirmTitle"
     aria-hidden="true"
     style="z-index:108500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="delete-icon">
                    <i class="fas fa-trash-can"></i>
                </div>

                <h6 class="fw-bold text-dark mb-2" id="deleteRuleConfirmTitle" style="font-size:14px;">
                    Delete Auto-Reply Rule?
                </h6>

                <p id="deleteRuleTargetName"
                   class="text-muted mb-3"
                   style="font-size:11px;line-height:1.6;"></p>

                <div class="d-flex justify-content-center gap-2">
                    <button type="button"
                            class="btn btn-light btn-compact px-3"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="button"
                            class="btn btn-danger btn-compact px-3"
                            id="confirmDeleteRuleBtn">
                        <i class="fas fa-trash-can"></i>
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let targetRuleIdToDelete = null;

    function insertVariable(tag) {
        const area = document.getElementById('modalTemplateInput');

        if (!area) return;

        const startPos = area.selectionStart ?? area.value.length;
        const endPos = area.selectionEnd ?? area.value.length;
        const originalText = area.value;

        area.value =
            originalText.substring(0, startPos) +
            tag +
            originalText.substring(endPos);

        area.focus();
        area.selectionStart = startPos + tag.length;
        area.selectionEnd = startPos + tag.length;
    }

    function openDeleteRuleModal(ruleId, ruleName) {
        targetRuleIdToDelete = ruleId;

        const labelEl = document.getElementById('deleteRuleTargetName');

        if (labelEl) {
            labelEl.innerText = `Are you sure you want to delete rule: "${ruleName}"?`;
        }

        const modalElement = document.getElementById('deleteRuleConfirmModal');

        if (modalElement && typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.show();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const confirmBtn = document.getElementById('confirmDeleteRuleBtn');

        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => {
                if (!targetRuleIdToDelete) return;

                const targetForm = document.getElementById(
                    `delete-rule-form-${targetRuleIdToDelete}`
                );

                if (targetForm) {
                    targetForm.submit();
                }
            });
        }
    });
</script>
@endsection