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
<!-- Google Fonts: Plus Jakarta Sans & Inter -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* Professional Typography & Strict 5px Element Radius */
    #autoRulesRoot,
    .rule-modal-scope {
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        letter-spacing: -0.01em;
        color: #1e293b;
    }

    #autoRulesRoot .btn,
    #autoRulesRoot .btn-sm,
    #autoRulesRoot button,
    #autoRulesRoot .badge,
    #autoRulesRoot .form-control,
    #autoRulesRoot .form-select,
    #autoRulesRoot .card,
    #autoRulesRoot .metric-card,
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

    .bn-digit,
    .font-monospace {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
    }

    .btn-compact {
        font-size: 11px !important;
        padding: 6px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    /* Fixed Button Hover Colors (Text & Background Never Clash) */
    .btn-success {
        background-color: #16a34a !important;
        border-color: #16a34a !important;
        color: #ffffff !important;
    }
    .btn-success:hover {
        background-color: #15803d !important;
        border-color: #15803d !important;
        color: #ffffff !important;
    }

    .btn-outline-secondary {
        background-color: #ffffff !important;
        border-color: #cbd5e1 !important;
        color: #334155 !important;
    }
    .btn-outline-secondary:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }

    .btn-outline-dark {
        background-color: #ffffff !important;
        border-color: #334155 !important;
        color: #334155 !important;
    }
    .btn-outline-dark:hover {
        background-color: #0f172a !important;
        border-color: #0f172a !important;
        color: #ffffff !important;
    }

    .btn-light {
        background-color: #ffffff !important;
        border-color: #cbd5e1 !important;
        color: #475569 !important;
    }
    .btn-light:hover {
        background-color: #f1f5f9 !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }

    .btn-danger {
        background-color: #dc2626 !important;
        border-color: #dc2626 !important;
        color: #ffffff !important;
    }
    .btn-danger:hover {
        background-color: #b91c1c !important;
        border-color: #b91c1c !important;
        color: #ffffff !important;
    }

    /* Clean White Enterprise Header Card */
    .rules-header-card {
        background: #ffffff;
        color: #0f172a;
        padding: 14px 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 14px;
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
        font-size: 17px;
        line-height: 1.25;
        letter-spacing: -0.01em;
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
    .metric-card-purple { background: linear-gradient(180deg, #ffffff 0%, #faf5ff 100%); border-left: 3.5px solid #a855f7 !important; }

    .rules-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 14px;
    }

    /* Table Architecture: 100% Fit Screen with Smooth Touch Scroll */
    .table-responsive {
        width: 100%;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }
    .custom-rules-table {
        width: 100% !important;
        margin-bottom: 0;
        border-collapse: collapse;
    }
    .custom-rules-table th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 10px;
        white-space: nowrap;
    }
    .custom-rules-table td {
        padding: 8px 10px;
        vertical-align: middle;
        font-size: 12px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }
    .custom-rules-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    /* Fixed Desktop Proportions */
    @media (min-width: 992px) {
        .custom-rules-table {
            table-layout: fixed !important;
            width: 100% !important;
        }
        .pc-fit-container {
            overflow-x: hidden !important;
        }
        .col-rule-name       { width: 22% !important; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .col-rule-conditions { width: 22% !important; }
        .col-rule-trigger    { width: 14% !important; }
        .col-rule-template   { width: 30% !important; }
        .col-rule-status     { width: 6% !important; }
        .col-rule-actions    { width: 6% !important; }
    }

    /* Strict Mobile Responsiveness: Prevents overlapping and text squishing */
    @media (max-width: 991.98px) {
        .rules-header-card {
            padding: 12px 14px !important;
        }
        .header-actions-group {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .header-actions-group .btn {
            width: 100%;
            padding: 8px 6px !important;
            font-size: 11px !important;
            justify-content: center;
        }
        .header-actions-group .btn-create-rule {
            grid-column: span 2;
        }
        .custom-rules-table {
            min-width: 860px !important;
            table-layout: auto !important;
        }
        .col-rule-name       { min-width: 180px !important; }
        .col-rule-conditions { min-width: 160px !important; }
        .col-rule-trigger    { min-width: 140px !important; }
        .col-rule-template   { min-width: 260px !important; }
        .col-rule-status     { min-width: 70px !important; }
        .col-rule-actions    { min-width: 70px !important; }

        .metric-card {
            padding: 10px !important;
            min-height: 80px !important;
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
    }

    /* Fix for Template Text Wrapping - Never bleeds into Status/Actions */
    .col-rule-template {
        white-space: normal !important;
        word-break: break-word !important;
    }
    .template-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 8px 10px;
        font-size: 11px;
        line-height: 1.45;
        border-radius: 4px !important;
        word-break: break-word !important;
        white-space: normal !important;
    }

    .var-badge {
        font-family: 'Plus Jakarta Sans', 'Inter', monospace;
        font-size: 10px;
        background: #f8fafc;
        color: #0f172a;
        border: 1px solid #cbd5e1;
        padding: 3px 8px;
        cursor: pointer;
        display: inline-block;
        margin-right: 4px;
        margin-bottom: 4px;
        user-select: none;
        transition: all 0.15s ease;
    }
    .var-badge:hover {
        background: #eff6ff !important;
        border-color: #2563eb !important;
        color: #2563eb !important;
    }

    #autoRulesRoot .form-control,
    #autoRulesRoot .form-select,
    .rule-modal-scope .form-control,
    .rule-modal-scope .form-select {
        min-height: 33px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #autoRulesRoot .form-control:focus,
    #autoRulesRoot .form-select:focus,
    .rule-modal-scope .form-control:focus,
    .rule-modal-scope .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }
</style>

<div class="container-fluid px-2 px-md-4 py-3" id="autoRulesRoot">

    <!-- 1. Header Banner Card (Clean White Light Theme) -->
    <div class="rules-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase" style="font-size: 10px;">
                    <i class="fas fa-robot me-1"></i> BOT AUTOMATION
                </span>
                <span class="badge bg-light text-secondary border font-monospace" style="font-size: 10px;">
                    INSTANT DISPATCH RELAY
                </span>
            </div>
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-robot text-success"></i>
                <span>Review Auto-Reply Automation Engine</span>
            </h5>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                Configure automated response templates triggered by star ratings, keywords, and verified buyer status.
            </small>
        </div>

        <div class="header-actions-group d-flex flex-wrap gap-1">
            <button type="button" class="btn btn-success btn-compact shadow-xs fw-bold btn-create-rule" data-bs-toggle="modal" data-bs-target="#createRuleModal">
                <i class="fas fa-plus me-1"></i> Create Auto-Rule
            </button>
            <a href="{{ route('admin.product.reviews.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-arrow-left me-1 text-primary"></i> Reviews Table
            </a>
            <a href="{{ route('admin.product.reviews.settings') }}" class="btn btn-outline-dark btn-compact shadow-xs fw-semibold">
                <i class="fas fa-gear me-1 text-secondary"></i> Review Settings
            </a>
        </div>
    </div>

    <!-- 2. Reviews Status Telemetry KPI Overview -->
    <div class="row g-2 mb-3">
        <!-- KPI 1: Total Rules -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-blue h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Total Rules</span>
                    <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                        <i class="fas fa-list-check"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                        {{ number_format($totalRulesCount) }}
                    </h4>
                    <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Configured automation presets</small>
                </div>
            </div>
        </div>

        <!-- KPI 2: Active Automations -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-green h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Active Rules</span>
                    <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                        <i class="fas fa-circle-check"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-success bn-digit font-monospace stat-val-num">
                        {{ number_format($activeRulesCount) }}
                    </h4>
                    <small class="text-success d-block text-truncate font-monospace stat-sub-text">
                        <i class="fas fa-bolt me-1"></i>Active trigger bots
                    </small>
                </div>
            </div>
        </div>

        <!-- KPI 3: Star Rating Triggers -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-amber h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Rating Triggers</span>
                    <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-warning-emphasis bn-digit font-monospace stat-val-num">
                        {{ number_format($ratingTriggerCount) }}
                    </h4>
                    <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Star-score bound rules</small>
                </div>
            </div>
        </div>

        <!-- KPI 4: Verified-Only Filters -->
        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-purple h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Verified Specific</span>
                    <div class="metric-icon-square bg-purple-subtle text-primary shadow-xs" style="background-color: #f3e8ff; color: #7e22ce;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                        {{ number_format($verifiedOnlyCount) }}
                    </h4>
                    <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Targeting actual buyers only</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small border-0 shadow-xs d-flex align-items-center justify-content-between mb-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-xs d-flex align-items-center justify-content-between mb-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger py-2 px-3 small border-0 shadow-xs mb-3 font-monospace" style="font-size: 11px;">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 3. Variable Guide Surface -->
    <div class="rules-panel-card p-3 mb-3">
        <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-1" style="font-size: 12px;">
            <i class="fas fa-code text-primary"></i> <span>Available Template Variables</span>
        </h6>
        <p class="text-muted small mb-2 font-monospace" style="font-size: 11px;">These placeholders are dynamically replaced by customer, product, and review data:</p>
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

    <!-- 4. Auto Rules List Table -->
    <div class="rules-panel-card p-0 overflow-hidden mb-3">
        <div class="py-2 px-3 border-bottom bg-light d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0 small" style="font-size: 12px;">
                <i class="fas fa-list-check text-primary me-1"></i> Active Automation Rules
            </h6>
            <span class="badge bg-light text-muted border font-monospace bn-digit" style="font-size: 10px;">{{ count($rules) }} Rules</span>
        </div>

        <div class="table-responsive pc-fit-container">
            <table class="table custom-rules-table align-middle">
                <thead>
                    <tr>
                        <th class="col-rule-name">Rule Name</th>
                        <th class="col-rule-conditions">Matching Conditions</th>
                        <th class="col-rule-trigger">Trigger Event</th>
                        <th class="col-rule-template">Auto-Reply Template</th>
                        <th class="text-center col-rule-status">Status</th>
                        <th class="text-end pe-3 col-rule-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules as $r)
                        @php
                            $ratingsArr = !empty($r->ratings_json) ? json_decode($r->ratings_json, true) : [];
                            $keywordsArr = !empty($r->keywords_json) ? json_decode($r->keywords_json, true) : [];
                        @endphp
                        <tr>
                            <!-- 1. Rule Name -->
                            <td class="col-rule-name">
                                <strong class="text-dark d-block text-truncate" style="font-size: 12px;" title="{{ $r->name }}">{{ $r->name }}</strong>
                                <small class="text-muted font-monospace d-block text-truncate" style="font-size: 10px;">
                                    Target: {{ $r->product_title ? Str::limit($r->product_title, 25) : 'All Store Products' }}
                                </small>
                            </td>

                            <!-- 2. Matching Conditions -->
                            <td class="col-rule-conditions">
                                @if(is_array($ratingsArr) && count($ratingsArr) > 0)
                                    <div class="mb-1 d-flex flex-wrap gap-1">
                                        @foreach($ratingsArr as $star)
                                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle font-monospace" style="font-size: 9px; padding: 2px 5px;">⭐ {{ $star }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="badge bg-light text-muted border font-monospace" style="font-size: 9px;">Any Rating</span>
                                @endif

                                @if(is_array($keywordsArr) && count($keywordsArr) > 0)
                                    <small class="text-muted d-block text-truncate font-monospace" style="max-width: 150px; font-size: 10px;" title="{{ implode(', ', $keywordsArr) }}">
                                        Keys: {{ implode(', ', $keywordsArr) }}
                                    </small>
                                @endif

                                @if($r->verified_only)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace mt-1" style="font-size: 8.5px; padding: 2px 5px;">Verified Only</span>
                                @endif
                            </td>

                            <!-- 3. Trigger Event -->
                            <td class="col-rule-trigger">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-uppercase" style="font-size: 9px; padding: 2px 6px;">
                                    {{ str_replace('_', ' ', $r->trigger_event) }}
                                </span>
                                @if($r->require_admin_approval)
                                    <small class="text-danger d-block font-monospace mt-1" style="font-size: 9px;">Requires Mod Approval</small>
                                @endif
                            </td>

                            <!-- 4. Auto-Reply Template (Never bleeds into other columns) -->
                            <td class="col-rule-template">
                                <div class="template-box text-dark font-monospace">
                                    {{ $r->template_text }}
                                </div>
                            </td>

                            <!-- 5. Active Status Toggle -->
                            <td class="text-center col-rule-status">
                                <form action="{{ route('admin.product.reviews.auto_rules.toggle', $r->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm p-0 border-0 shadow-none" title="Toggle Status">
                                        <i class="fas fa-toggle-{{ $r->is_active ? 'on text-success' : 'off text-muted' }}" style="font-size: 18px;"></i>
                                    </button>
                                </form>
                            </td>

                            <!-- 6. Actions Toolbar -->
                            <td class="text-end pe-3 col-rule-actions">
                                <button type="button" class="btn btn-light border btn-compact p-0 d-inline-flex align-items-center justify-content-center text-danger shadow-xs" style="width: 26px; height: 26px; font-size: 10px;" title="Delete Rule" onclick="openDeleteRuleModal({{ $r->id }}, '{{ addslashes($r->name) }}')">
                                    <i class="fas fa-trash-can"></i>
                                </button>
                                <form id="delete-rule-form-{{ $r->id }}" action="{{ route('admin.product.reviews.auto_rules.delete', $r->id) }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted small font-monospace" style="font-size: 11px;">
                                <i class="fas fa-robot fa-2x mb-2 d-block opacity-25"></i>
                                No automation rules configured yet. Click 'Create Auto-Rule' to set one up.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal 1: Create Automation Rule -->
<div class="modal fade rule-modal-scope" id="createRuleModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header py-2 px-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-1" style="font-size: 13px;">
                    <i class="fas fa-robot text-success me-1"></i> <span>New Review Auto-Reply Rule</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.product.reviews.auto_rules.store') }}" method="POST">
                @csrf
                <div class="modal-body p-3">
                    <div class="row g-2 mb-2">
                        <div class="col-12 col-md-8">
                            <label class="form-label" style="font-size: 11px;">Rule Label / Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="5-Star Instant Thank You" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label" style="font-size: 11px;">Trigger Event *</label>
                            <select name="trigger_event" class="form-select font-monospace">
                                <option value="on_submission" selected>On Review Submission</option>
                                <option value="on_approved">After Manual Approval</option>
                                <option value="both">Both Events</option>
                            </select>
                        </div>
                    </div>

                    <!-- Target Ratings -->
                    <div class="mb-2 p-2 bg-light border" style="border-radius: 5px !important;">
                        <label class="form-label mb-1 d-block" style="font-size: 11px;">Apply to Star Ratings:</label>
                        <div class="d-flex gap-3 flex-wrap">
                            @for($star = 5; $star >= 1; $star--)
                                <div class="form-check m-0">
                                    <input class="form-check-input" type="checkbox" name="ratings[]" value="{{ $star }}" id="ratingCheck{{ $star }}" {{ $star >= 4 ? 'checked' : '' }}>
                                    <label class="form-check-label small font-monospace" for="ratingCheck{{ $star }}">⭐ {{ $star }} Star</label>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Keywords Match (Optional) -->
                    <div class="mb-2">
                        <label class="form-label" style="font-size: 11px;">Trigger Keywords (Optional - Comma separated):</label>
                        <input type="text" name="keywords" class="form-control font-monospace" placeholder="awesome, recommended, authentic, quick delivery">
                        <small class="text-muted font-monospace" style="font-size: 10px;">Leave blank to respond based purely on star rating.</small>
                    </div>

                    <!-- Template Textarea with Placeholders -->
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0" style="font-size: 11px;">Auto-Reply Message Template *</label>
                            <small class="text-muted font-monospace" style="font-size: 10px;">Click tag to insert</small>
                        </div>
                        <div class="d-flex flex-wrap mb-1">
                            <span class="var-badge" onclick="insertVariable('{customer_name}')">{customer_name}</span>
                            <span class="var-badge" onclick="insertVariable('{customer_first}')">{customer_first}</span>
                            <span class="var-badge" onclick="insertVariable('{product_name}')">{product_name}</span>
                            <span class="var-badge" onclick="insertVariable('{rating}')">{rating}</span>
                            <span class="var-badge" onclick="insertVariable('{store_name}')">{store_name}</span>
                            <span class="var-badge" onclick="insertVariable('{support_contact}')">{support_contact}</span>
                            <span class="var-badge" onclick="insertVariable('{review_date}')">{review_date}</span>
                        </div>
                        <textarea id="modalTemplateInput" name="template_text" rows="3" class="form-control font-monospace" placeholder="Dear {customer_name}, thank you for your feedback!..." required style="font-size: 11.5px;"></textarea>
                    </div>

                    <!-- Checkbox Controls -->
                    <div class="row g-2 pt-2 border-top">
                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="verified_only" id="mVerifiedOnly" value="1">
                                <label class="form-check-label font-monospace" for="mVerifiedOnly">Apply to Verified Buyers Only</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="require_admin_approval" id="mRequireApproval" value="1">
                                <label class="form-check-label font-monospace" for="mRequireApproval">Hold in Queue for Moderation</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2 px-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-compact px-4 shadow-xs fw-bold">
                        <i class="fas fa-check-circle me-1"></i> Save Automation Rule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Delete Rule Confirmation Modal -->
<div class="modal fade rule-modal-scope" id="deleteRuleConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 108500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-2">
                <div class="mb-2 text-danger" style="font-size: 32px;">
                    <i class="fas fa-trash-can"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Delete Auto-Reply Rule?</h6>
                <p id="deleteRuleTargetName" class="text-muted small mb-3 font-monospace" style="font-size: 11px; line-height: 1.45;"></p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-compact px-3 border" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-compact px-3 fw-bold shadow-xs" id="confirmDeleteRuleBtn">Yes, Delete</button>
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

    const startPos = area.selectionStart;
    const endPos = area.selectionEnd;
    const originalText = area.value;

    area.value = originalText.substring(0, startPos) + tag + originalText.substring(endPos, originalText.length);
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
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteRuleConfirmModal'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', () => {
    const confirmBtn = document.getElementById('confirmDeleteRuleBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', () => {
            if (targetRuleIdToDelete) {
                const targetForm = document.getElementById(`delete-rule-form-${targetRuleIdToDelete}`);
                if (targetForm) {
                    targetForm.submit();
                }
            }
        });
    }
});
</script>
@endsection