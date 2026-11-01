@extends('layouts.admin')

@section('title', 'Display & Widget Settings')

@php
    $footerBuilderRoute = Route::has('admin.theme.setting.footer') 
        ? route('admin.theme.setting.footer') 
        : (Route::has('admin.theme.footer') ? route('admin.theme.footer') : url('admin/theme-setting/footer'));

    $popupCreateRoute = Route::has('admin.theme.setting.display.popup.create')
        ? route('admin.theme.setting.display.popup.create')
        : (Route::has('admin.theme.display.popup.create') ? route('admin.theme.display.popup.create') : url('admin/theme-setting/display/popups/create'));

    $widgetStoreRoute = Route::has('admin.theme.setting.display.widget.store')
        ? route('admin.theme.setting.display.widget.store')
        : (Route::has('admin.theme.display.widget.store') ? route('admin.theme.display.widget.store') : url('admin/theme-setting/display/widgets/store'));

    $widgetHubUpdateRoute = Route::has('admin.theme.setting.display.widget_hub.update') 
        ? route('admin.theme.setting.display.widget_hub.update') 
        : (Route::has('admin.theme.setting.display.sticky_bar_rule.update') 
            ? route('admin.theme.setting.display.sticky_bar_rule.update') 
            : url('admin/theme-setting/display/widget-hub-settings'));

    $hubSettings = is_array($widgetHubRule->settings ?? null) 
        ? $widgetHubRule->settings 
        : (json_decode($widgetHubRule->settings ?? '[]', true) ?: []);

    $currentDisplayStyle = $hubSettings['display_style'] ?? 'speed_dial';

    $activeWidgetsCount = $widgets->where('is_active', true)->count();
    $totalPopupsCount = method_exists($popups, 'total') ? $popups->total() : $popups->count();
    $activePopupsCount = $popups->where('is_active', true)->count();
@endphp

@section('admin_content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    #dynamic-display-root,
    #widgetManageModal,
    #dynamicActionConfirmModal {
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        letter-spacing: -0.01em;
        color: #1e293b;
    }

    .font-monospace {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
    }

    #dynamic-display-root .btn,
    #dynamic-display-root .btn-sm,
    #dynamic-display-root button,
    #dynamic-display-root .badge,
    #dynamic-display-root .alert,
    #dynamic-display-root .form-control,
    #dynamic-display-root .form-select,
    #dynamic-display-root .input-group-text,
    #dynamic-display-root .card,
    #dynamic-display-root .metric-card,
    #dynamic-display-root .presentation-radio-card,
    #dynamic-display-root .nav-pills .nav-link,
    #dynamic-display-root .collapse-toggle-btn,
    .dynamic-modal-scope .modal-content,
    .dynamic-modal-scope .btn,
    .dynamic-display-toast {
        border-radius: 5px !important;
    }

    #dynamic-display-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
        font-size: 12px;
        background-color: #f8fafc;
        border-color: #cbd5e1;
    }

    #dynamic-display-root .input-group > .form-control,
    #dynamic-display-root .input-group > .form-select {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

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

    #dynamic-display-root .card {
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
    .metric-card-purple { background: linear-gradient(180deg, #ffffff 0%, #faf5ff 100%); border-left: 3.5px solid #a855f7 !important; }
    .metric-card-amber  { background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-left: 3.5px solid #f59e0b !important; }

    .presentation-radio-card {
        border: 1px solid #e2e8f0;
        background-color: #f8fafc;
        padding: 10px 12px;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .presentation-radio-card:hover {
        background-color: #ffffff;
        border-color: #cbd5e1;
    }

    .presentation-radio-card.active-mode {
        background-color: #ffffff;
        border-color: #2563eb;
        box-shadow: 0 0 0 1px #2563eb;
    }

    .nav-pills .nav-link {
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        padding: 6px 14px;
        transition: all 0.15s ease;
    }

    .nav-pills .nav-link.active {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.25);
    }

    .custom-display-table {
        width: 100%;
        margin-bottom: 0;
    }

    .custom-display-table thead th {
        font-size: 10px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #475569;
        background-color: #f8fafc;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 8px 10px;
        white-space: nowrap;
    }

    .custom-display-table tbody td {
        padding: 8px 10px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        font-size: 12px;
        vertical-align: middle;
        white-space: nowrap;
    }

    .custom-display-table tbody tr:hover td {
        background-color: #fafbfc;
    }

    @media (min-width: 992px) {
        .custom-display-table {
            table-layout: fixed !important;
            width: 100% !important;
        }

        .pc-fit-container {
            overflow-x: hidden !important;
        }

        .custom-display-table th,
        .custom-display-table td {
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }
    }

    .form-control, .form-select {
        min-height: 32px;
        font-size: 12px !important;
        border: 1px solid #cbd5e1 !important;
        color: #0f172a !important;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        outline: none;
    }

    .dynamic-display-toast {
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
        .display-header-wrap h5 {
            font-size: 16px !important;
        }

        .top-action-btns {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }

        .top-action-btns .btn-popup-create {
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

        .custom-display-table {
            min-width: 760px !important;
            table-layout: auto !important;
        }

        #dynamic-display-root .card {
            margin-bottom: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="dynamic-display-root">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 display-header-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-layer-group text-primary"></i>
                    <span>Display &amp; Widget Settings</span>
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace" style="font-size: 10px;">
                    Dynamic System
                </span>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">
                Manage floating widgets and popup campaigns.
            </small>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2 top-action-btns">
            <a href="{{ $footerBuilderRoute }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold bg-white" title="Footer Builder">
                <i class="fas fa-arrow-left me-1"></i> <span>Footer</span>
            </a>
            <button type="button" class="btn btn-sm btn-success btn-compact shadow-xs fw-bold" onclick="openAddWidgetModal()">
                <i class="fas fa-plus me-1"></i> <span>Add Widget</span>
            </button>
            <a href="{{ $popupCreateRoute }}" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold btn-popup-create">
                <i class="fas fa-bullhorn me-1"></i> <span>New Popup</span>
            </a>
        </div>
    </div>

    {{-- Feedback --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Overview --}}
    <div class="row g-2 mb-3">

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-blue h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Active Widgets</span>
                    <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                        {{ $activeWidgetsCount }} <span class="text-muted fw-normal" style="font-size: 11px;">/ {{ $widgets->count() }} Total</span>
                    </h4>
                    <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Floating Buttons</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-green h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Display Mode</span>
                    <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                        <i class="fas fa-sliders"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-success font-monospace stat-val-num" style="font-size: 14px;">
                        {{ $currentDisplayStyle === 'speed_dial' ? 'Speed Dial' : 'Direct Stack' }}
                    </h4>
                    <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Storefront Mode</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-purple h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Active Popups</span>
                    <div class="metric-icon-square bg-purple-subtle text-primary shadow-xs" style="background-color: #f3e8ff; color: #7e22ce;">
                        <i class="fas fa-window-restore"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                        {{ $activePopupsCount }} <span class="text-muted fw-normal" style="font-size: 11px;">/ {{ $totalPopupsCount }}</span>
                    </h4>
                    <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Popup Campaigns</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card metric-card metric-card-amber h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Position</span>
                    <div class="metric-icon-square bg-warning-subtle text-warning-emphasis shadow-xs">
                        <i class="fas fa-compass"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-warning-emphasis font-monospace stat-val-num" style="font-size: 14px;">
                        Bottom Fixed
                    </h4>
                    <small class="text-muted d-block text-truncate font-monospace stat-sub-text">Mobile &amp; Desktop</small>
                </div>
            </div>
        </div>

    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills gap-2 mb-3" id="displayTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active shadow-xs" id="tab-widgets-btn" data-bs-toggle="pill" data-bs-target="#tab-widgets" type="button" role="tab">
                <i class="fas fa-comment-dots me-1"></i> Widgets ({{ $widgets->count() }})
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link shadow-xs" id="tab-popups-btn" data-bs-toggle="pill" data-bs-target="#tab-popups" type="button" role="tab">
                <i class="fas fa-window-restore me-1"></i> Popups ({{ $totalPopupsCount }})
            </button>
        </li>
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content" id="displayTabsContent">

        {{-- TAB 1: WIDGETS --}}
        <div class="tab-pane fade show active" id="tab-widgets" role="tabpanel">

            {{-- Display Settings --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="section-header-wrap d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark small" style="font-size: 12px;">
                        <i class="fas fa-sliders text-primary me-1"></i> Display Settings
                    </span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1" style="font-size: 10px;">
                        {{ $currentDisplayStyle === 'speed_dial' ? 'Speed Dial' : 'Direct Stack' }}
                    </span>
                </div>

                <div class="p-3">
                    <form action="{{ $widgetHubUpdateRoute }}" method="POST">
                        @csrf

                        <div class="row g-3">

                            {{-- Display Mode --}}
                            <div class="col-12 col-lg-5">
                                <label class="form-label fw-bold text-dark small d-block mb-2">Display Mode</label>

                                <div class="d-flex flex-column gap-2">

                                    <label class="presentation-radio-card d-flex align-items-start gap-2 {{ $currentDisplayStyle === 'speed_dial' ? 'active-mode' : '' }}" for="style_speed_dial">
                                        <input class="form-check-input mt-1" type="radio" name="display_style" id="style_speed_dial" value="speed_dial" {{ $currentDisplayStyle === 'speed_dial' ? 'checked' : '' }} onchange="toggleDisplayStyleOptions(this.value)">

                                        <div>
                                            <strong class="d-block text-dark" style="font-size: 12px;">Speed Dial</strong>
                                            <small class="text-muted d-block" style="font-size: 11px; line-height: 1.4;">
                                                One main button expands all widgets.
                                            </small>
                                        </div>
                                    </label>

                                    <label class="presentation-radio-card d-flex align-items-start gap-2 {{ $currentDisplayStyle === 'direct_stack' ? 'active-mode' : '' }}" for="style_direct_stack">
                                        <input class="form-check-input mt-1" type="radio" name="display_style" id="style_direct_stack" value="direct_stack" {{ $currentDisplayStyle === 'direct_stack' ? 'checked' : '' }} onchange="toggleDisplayStyleOptions(this.value)">

                                        <div>
                                            <strong class="d-block text-dark" style="font-size: 12px;">Direct Stack</strong>
                                            <small class="text-muted d-block" style="font-size: 11px; line-height: 1.4;">
                                                All widgets stay visible.
                                            </small>
                                        </div>
                                    </label>

                                </div>
                            </div>

                            {{-- Speed Dial Settings --}}
                            <div class="col-12 col-lg-7" id="speedDialCustomizerWrap" style="{{ $currentDisplayStyle === 'direct_stack' ? 'display: none;' : '' }}">
                                <div class="p-3 border rounded bg-light bg-opacity-25" style="border-radius: 5px !important;">

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label fw-bold text-dark mb-0 small">Hub Settings</label>

                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="show_label" id="hub_show_label" value="1" {{ ($hubSettings['show_label'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label text-dark fw-bold" for="hub_show_label" style="font-size: 11px;">Show Label</label>
                                        </div>
                                    </div>

                                    <div class="row g-2">

                                        <div class="col-12 col-sm-6">
                                            <label class="form-label fw-bold text-dark" style="font-size: 11px;">Badge Text</label>
                                            <input type="text" name="hub_label" class="form-control font-monospace shadow-none" placeholder="Chat with Us" value="{{ $hubSettings['hub_label'] ?? 'Chat with Us' }}">
                                        </div>

                                        <div class="col-12 col-sm-6">
                                            <label class="form-label fw-bold text-dark" style="font-size: 11px;">Icon Class</label>
                                            <input type="text" name="hub_icon" class="form-control font-monospace shadow-none" placeholder="fas fa-comment-dots" value="{{ $hubSettings['hub_icon'] ?? 'fas fa-comment-dots' }}">
                                        </div>

                                        <div class="col-6 col-sm-3">
                                            <label class="form-label fw-bold text-dark" style="font-size: 11px;">Button BG</label>
                                            <input type="color" name="hub_bg_color" class="form-control form-control-color w-100 shadow-none p-1" value="{{ $hubSettings['hub_bg_color'] ?? '#1e3a8a' }}">
                                        </div>

                                        <div class="col-6 col-sm-3">
                                            <label class="form-label fw-bold text-dark" style="font-size: 11px;">Icon Color</label>
                                            <input type="color" name="hub_icon_color" class="form-control form-control-color w-100 shadow-none p-1" value="{{ $hubSettings['hub_icon_color'] ?? '#ffffff' }}">
                                        </div>

                                        <div class="col-6 col-sm-3">
                                            <label class="form-label fw-bold text-dark" style="font-size: 11px;">Badge BG</label>
                                            <input type="color" name="badge_bg_color" class="form-control form-control-color w-100 shadow-none p-1" value="{{ $hubSettings['badge_bg_color'] ?? '#ffffff' }}">
                                        </div>

                                        <div class="col-6 col-sm-3">
                                            <label class="form-label fw-bold text-dark" style="font-size: 11px;">Text Color</label>
                                            <input type="color" name="badge_text_color" class="form-control form-control-color w-100 shadow-none p-1" value="{{ $hubSettings['badge_text_color'] ?? '#1e3a8a' }}">
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3">
                                    <i class="fas fa-save me-1"></i> Save Settings
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            {{-- Widgets Table --}}
            <div class="card border-0 shadow-sm overflow-hidden mb-3">

                <div class="section-header-wrap d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark small" style="font-size: 12px;">
                        <i class="fas fa-phone-volume text-success me-1"></i> Floating Widgets
                    </span>

                    <button type="button" class="btn btn-sm btn-success btn-compact shadow-xs fw-bold" onclick="openAddWidgetModal()">
                        <i class="fas fa-plus me-1"></i> Add Widget
                    </button>
                </div>

                <div class="table-responsive pc-fit-container" style="min-height: 180px;">
                    <table class="table align-middle mb-0 custom-display-table">

                        <thead>
                            <tr>
                                <th class="ps-3 text-center" style="width: 5%;">#</th>
                                <th style="width: 20%;">Widget</th>
                                <th style="width: 14%;">Type</th>
                                <th style="width: 25%;">Destination</th>
                                <th style="width: 12%;">Position</th>
                                <th class="text-center" style="width: 7%;">Order</th>
                                <th class="text-center" style="width: 7%;">Status</th>
                                <th class="text-end pe-3" style="width: 10%;">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($widgets as $widget)

                                @php
                                    $wSettings = is_array($widget->settings) ? $widget->settings : (json_decode($widget->settings ?? '[]', true) ?: []);
                                    $actionType = $wSettings['action_type'] ?? 'link';
                                    $modalTitle = $wSettings['modal_title'] ?? '';

                                    $widgetToggleUrl = Route::has('admin.theme.setting.display.widget.toggle')
                                        ? route('admin.theme.setting.display.widget.toggle', $widget->id)
                                        : (Route::has('admin.theme.display.widget.toggle') ? route('admin.theme.display.widget.toggle', $widget->id) : url('admin/theme-setting/display/widgets/' . $widget->id . '/toggle'));

                                    $widgetDestroyUrl = Route::has('admin.theme.setting.display.widget.destroy')
                                        ? route('admin.theme.setting.display.widget.destroy', $widget->id)
                                        : (Route::has('admin.theme.display.widget.destroy') ? route('admin.theme.display.widget.destroy', $widget->id) : url('admin/theme-setting/display/widgets/' . $widget->id));
                                @endphp

                                <tr id="widget-row-{{ $widget->id }}">

                                    <td class="ps-3 text-center text-muted font-monospace bn-digit" style="font-size: 11px;">
                                        {{ $widget->id }}
                                    </td>

                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="rounded-circle d-inline-flex align-items-center justify-content-center shadow-xs text-white flex-shrink-0" style="width: 28px; height: 28px; background-color: {{ $widget->bg_color ?? '#2563eb' }}; font-size: 11px;">
                                                <i class="{{ $widget->icon ?? 'fas fa-link' }}"></i>
                                            </span>

                                            <div class="min-w-0">
                                                <strong class="d-block text-dark text-capitalize text-truncate" style="font-size: 12px;">{{ $widget->label ?: $widget->widget_type }}</strong>
                                                <small class="text-muted font-monospace d-block text-truncate" style="font-size: 10px;">{{ $widget->widget_type }}</small>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        @if($actionType === 'modal')
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 font-monospace" style="font-size: 9.5px;">
                                                <i class="fas fa-window-maximize me-1"></i> Modal
                                            </span>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace" style="font-size: 9.5px;">
                                                <i class="fas fa-link me-1"></i> Link
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($actionType === 'modal')
                                            <strong class="text-dark d-block text-truncate" style="max-width: 220px; font-size: 12px;" title="{{ $modalTitle ?: 'Custom Modal' }}">
                                                {{ $modalTitle ?: 'Custom Modal' }}
                                            </strong>
                                            <small class="text-muted font-monospace d-block" style="font-size: 10px;">
                                                HTML / Shortcode
                                            </small>
                                        @else
                                            <span class="text-dark d-block text-truncate font-monospace" style="max-width: 220px; font-size: 11.5px;" title="{{ $widget->value }}">
                                                {{ $widget->value }}
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="badge bg-light text-secondary border px-2 py-1 font-monospace" style="font-size: 9.5px;">
                                            {{ str_replace('_', ' ', strtoupper($widget->position)) }}
                                        </span>
                                    </td>

                                    <td class="text-center font-monospace bn-digit" style="font-size: 11.5px;">
                                        {{ $widget->order_index }}
                                    </td>

                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-compact shadow-xs fw-bold {{ $widget->is_active ? 'btn-success text-white' : 'btn-light border text-muted' }}" onclick="toggleWidgetStatus({{ $widget->id }}, '{{ $widgetToggleUrl }}')" id="widget-status-btn-{{ $widget->id }}" style="min-width: 68px;">
                                            {{ $widget->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </td>

                                    <td class="text-end pe-3">
                                        <div class="d-inline-flex align-items-center gap-1">

                                            <button type="button" class="btn btn-sm btn-light border p-1 text-primary shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px;" onclick="openEditWidgetModal({{ json_encode($widget) }})" title="Edit">
                                                <i class="fas fa-pencil" style="font-size: 10px;"></i>
                                            </button>

                                            <button type="button" class="btn btn-sm btn-light border p-1 text-danger shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px;" onclick="confirmDelete('{{ $widgetDestroyUrl }}', 'Delete this widget?')" title="Delete">
                                                <i class="fas fa-trash-can" style="font-size: 10px;"></i>
                                            </button>

                                        </div>
                                    </td>

                                </tr>

                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted small">
                                        <i class="fas fa-phone-volume fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                        <span class="fw-semibold">No widgets found. Add your first widget.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>
            </div>

        </div>

        {{-- TAB 2: POPUPS --}}
        <div class="tab-pane fade" id="tab-popups" role="tabpanel">

            <div class="card border-0 shadow-sm overflow-hidden mb-3">

                <div class="section-header-wrap d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark small" style="font-size: 12px;">
                        <i class="fas fa-list text-primary me-1"></i> Popup Campaigns
                    </span>

                    <a href="{{ $popupCreateRoute }}" class="btn btn-sm btn-primary btn-compact shadow-xs fw-bold">
                        <i class="fas fa-plus me-1"></i> Add Popup
                    </a>
                </div>

                <div class="table-responsive pc-fit-container" style="min-height: 200px;">
                    <table class="table align-middle mb-0 custom-display-table">

                        <thead>
                            <tr>
                                <th class="ps-3 text-center" style="width: 5%;">#</th>
                                <th style="width: 25%;">Title</th>
                                <th style="width: 15%;">Type</th>
                                <th style="width: 15%;">Trigger</th>
                                <th class="text-center" style="width: 14%;">Frequency</th>
                                <th class="text-center" style="width: 11%;">Status</th>
                                <th class="text-end pe-3" style="width: 15%;">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($popups as $popup)

                                @php
                                    $popupEditUrl = Route::has('admin.theme.setting.display.popup.edit')
                                        ? route('admin.theme.setting.display.popup.edit', $popup->id)
                                        : (Route::has('admin.theme.display.popup.edit') ? route('admin.theme.display.popup.edit', $popup->id) : url('admin/theme-setting/display/popups/' . $popup->id . '/edit'));

                                    $popupToggleUrl = Route::has('admin.theme.setting.display.popup.toggle')
                                        ? route('admin.theme.setting.display.popup.toggle', $popup->id)
                                        : (Route::has('admin.theme.display.popup.toggle') ? route('admin.theme.display.popup.toggle', $popup->id) : url('admin/theme-setting/display/popups/' . $popup->id . '/toggle'));

                                    $popupDestroyUrl = Route::has('admin.theme.setting.display.popup.destroy')
                                        ? route('admin.theme.setting.display.popup.destroy', $popup->id)
                                        : (Route::has('admin.theme.display.popup.destroy') ? route('admin.theme.display.popup.destroy', $popup->id) : url('admin/theme-setting/display/popups/' . $popup->id));
                                @endphp

                                <tr id="popup-row-{{ $popup->id }}">

                                    <td class="ps-3 text-center text-muted font-monospace bn-digit" style="font-size: 11px;">
                                        {{ $popup->id }}
                                    </td>

                                    <td>
                                        <strong class="text-dark d-block text-truncate" style="font-size: 12px; max-width: 240px;" title="{{ $popup->title }}">
                                            {{ $popup->title ?: 'Image / Custom Modal' }}
                                        </strong>

                                        <small class="text-muted font-monospace d-block" style="font-size: 10px;">
                                            {{ $popup->created_at ? 'Created ' . $popup->created_at->format('M d, Y') : 'Date N/A' }}
                                        </small>
                                    </td>

                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1 font-monospace" style="font-size: 9.5px;">
                                            {{ strtoupper(str_replace('_', ' ', $popup->popup_type)) }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="text-muted small font-monospace" style="font-size: 11px;">
                                            @if($popup->trigger_type === 'delay')
                                                <i class="fas fa-clock text-warning me-1"></i> {{ $popup->trigger_value }}s Delay
                                            @elseif($popup->trigger_type === 'scroll')
                                                <i class="fas fa-arrows-up-down text-info me-1"></i> {{ $popup->trigger_value }}% Scroll
                                            @else
                                                <i class="fas fa-bolt text-danger me-1"></i> Instant
                                            @endif
                                        </span>
                                    </td>

                                    <td class="text-center font-monospace" style="font-size: 11px;">
                                        {{ $popup->frequency_days == 0 ? 'Every Reload' : 'Every ' . $popup->frequency_days . ' Days' }}
                                    </td>

                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-compact shadow-xs fw-bold {{ $popup->is_active ? 'btn-success text-white' : 'btn-light border text-muted' }}" onclick="togglePopupStatus({{ $popup->id }}, '{{ $popupToggleUrl }}')" id="popup-status-btn-{{ $popup->id }}" style="min-width: 72px;">
                                            {{ $popup->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </td>

                                    <td class="text-end pe-3">
                                        <div class="d-inline-flex align-items-center gap-1">

                                            <a href="{{ $popupEditUrl }}" class="btn btn-sm btn-light border p-1 text-primary shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px;" title="Edit">
                                                <i class="fas fa-pencil" style="font-size: 10px;"></i>
                                            </a>

                                            <button type="button" class="btn btn-sm btn-light border p-1 text-danger shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px;" onclick="confirmDelete('{{ $popupDestroyUrl }}', 'Delete this popup?')" title="Delete">
                                                <i class="fas fa-trash-can" style="font-size: 10px;"></i>
                                            </button>

                                        </div>
                                    </td>

                                </tr>

                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted small">
                                        <i class="fas fa-window-restore fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                        <span class="fw-semibold">No popups found. Create your first popup.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                @if(method_exists($popups, 'hasPages') && $popups->hasPages())
                    <div class="p-2 px-3 border-top d-flex justify-content-end bg-light bg-opacity-50">
                        {{ $popups->links('pagination::bootstrap-5') }}
                    </div>
                @endif

            </div>
        </div>

    </div>
</div>

{{-- Widget Modal --}}
<div class="modal fade dynamic-modal-scope" id="widgetManageModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header border-bottom py-2 px-3 bg-light d-flex justify-content-between align-items-center">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" id="widgetModalTitle" style="font-size: 13.5px;">
                    <i class="fas fa-plus-circle text-success"></i> <span>Widget Settings</span>
                </h6>

                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <form id="widgetManageForm" method="POST" action="{{ $widgetStoreRoute }}">
                @csrf
                <div id="widgetFormMethodWrapper"></div>

                <div class="modal-body p-3">
                    <div class="row g-2">

                        {{-- Action Type --}}
                        <div class="col-12 mb-2 p-2 bg-light border rounded" style="border-radius: 5px !important;">
                            <label class="form-label fw-bold text-dark small d-block mb-1">
                                <i class="fas fa-sliders text-primary me-1"></i> Action Type
                            </label>

                            <div class="d-flex gap-3 flex-wrap">

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="action_type" id="act_link" value="link" checked onchange="toggleActionFields('link')">

                                    <label class="form-check-label fw-bold small text-dark" for="act_link">
                                        Direct Link
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="action_type" id="act_modal" value="modal" onchange="toggleActionFields('modal')">

                                    <label class="form-check-label fw-bold small text-danger" for="act_modal">
                                        Popup Modal
                                    </label>
                                </div>

                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-dark">Widget Type</label>

                            <select name="widget_type" id="widget_type_input" class="form-select shadow-none font-monospace" onchange="syncWidgetPresets(this.value)" required>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="phone">Phone</option>
                                <option value="support_modal">Support Modal</option>
                                <option value="coupon_modal">Coupon Modal</option>
                                <option value="messenger">Messenger</option>
                                <option value="email">Email</option>
                                <option value="custom_link">Custom Link</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-dark">Button Label</label>
                            <input type="text" name="label" id="widget_label_input" class="form-control shadow-none" placeholder="Chat with Us">
                        </div>

                        {{-- Direct Link --}}
                        <div class="col-12" id="directLinkWrap">
                            <label class="form-label fw-bold small text-dark">Target URL / Number <span class="text-danger">*</span></label>

                            <input type="text" name="value" id="widget_value_input" class="form-control font-monospace shadow-none" placeholder="8801700000000 or https://...">
                        </div>

                        {{-- Modal Settings --}}
                        <div class="col-12 d-none" id="customModalSettingsWrap">
                            <div class="p-2 border border-warning bg-warning bg-opacity-10 rounded mb-2" style="border-radius: 5px !important;">

                                <h6 class="fw-bold text-dark small mb-2 d-flex align-items-center gap-1">
                                    <i class="fas fa-window-maximize text-warning"></i> Modal Settings
                                </h6>

                                <div class="row g-2">

                                    <div class="col-12">
                                        <label class="form-label fw-bold small text-dark">Modal Title</label>
                                        <input type="text" name="modal_title" id="widget_modal_title" class="form-control shadow-none" placeholder="Live Support">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-bold small text-dark">Modal Content (HTML / Shortcode)</label>

                                        <textarea name="modal_content" id="widget_modal_content" rows="4" class="form-control font-monospace shadow-none" placeholder="Paste HTML, text or shortcode..."></textarea>

                                        <small class="text-muted font-monospace d-block mt-1" style="font-size: 10.5px;">
                                            Only your custom content will be displayed.
                                        </small>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small text-dark">Icon Class</label>
                            <input type="text" name="icon" id="widget_icon_input" class="form-control font-monospace shadow-none" value="fab fa-whatsapp">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small text-dark">Position</label>

                            <select name="position" id="widget_position_input" class="form-select font-monospace shadow-none">
                                <option value="bottom_right" selected>Bottom Right</option>
                                <option value="bottom_left">Bottom Left</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small text-dark">Sort Order</label>
                            <input type="number" name="order_index" id="widget_order_input" class="form-control font-monospace bn-digit shadow-none" value="0">
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold small text-dark">Background</label>
                            <input type="color" name="bg_color" id="widget_bg_color_input" class="form-control form-control-color w-100 shadow-none p-1" value="#25d366">
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-bold small text-dark">Icon Color</label>
                            <input type="color" name="icon_color" id="widget_icon_color_input" class="form-control form-control-color w-100 shadow-none p-1" value="#ffffff">
                        </div>

                    </div>
                </div>

                <div class="modal-footer border-top py-2 px-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>

                    <button type="submit" class="btn btn-success btn-compact shadow-xs fw-bold px-3">
                        <i class="fas fa-check me-1"></i> Save
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- Delete Confirmation --}}
<div class="modal fade dynamic-modal-scope" id="dynamicActionConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">

            <div class="modal-body p-1">

                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 44px; height: 44px; border-radius: 50% !important;">
                    <i class="fas fa-trash-can fs-4"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">Confirm Delete</h6>

                <p class="text-muted small mb-3" id="confirmModalText" style="font-size: 11px; line-height: 1.45;">
                    Are you sure?
                </p>

                <form id="dynamicActionConfirmForm" method="POST" action="">
                    @csrf
                    @method('DELETE')

                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light btn-compact border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-compact shadow-xs fw-bold">Delete</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

{{-- Toast --}}
<div id="dynamic-toast-holder"></div>

<script>
    function toggleDisplayStyleOptions(style) {
        const wrap = document.getElementById('speedDialCustomizerWrap');

        if (wrap) {
            wrap.style.display = (style === 'speed_dial') ? 'block' : 'none';
        }

        document.querySelectorAll('.presentation-radio-card').forEach(card => card.classList.remove('active-mode'));

        const activeRadio = document.getElementById(`style_${style}`);

        if (activeRadio) {
            activeRadio.closest('.presentation-radio-card')?.classList.add('active-mode');
        }
    }

    function toggleActionFields(actionType) {
        const linkWrap = document.getElementById('directLinkWrap');
        const modalWrap = document.getElementById('customModalSettingsWrap');
        const valueInput = document.getElementById('widget_value_input');

        if (actionType === 'modal') {
            linkWrap.classList.add('d-none');
            modalWrap.classList.remove('d-none');

            if (valueInput) {
                valueInput.required = false;
                valueInput.disabled = true;
            }
        } else {
            linkWrap.classList.remove('d-none');
            modalWrap.classList.add('d-none');

            if (valueInput) {
                valueInput.required = true;
                valueInput.disabled = false;
            }
        }
    }

    function togglePopupStatus(id, url) {
        const btn = document.getElementById(`popup-status-btn-${id}`);
        if (!btn) return;

        btn.disabled = true;

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;

            if (data.success) {
                if (data.is_active) {
                    btn.className = 'btn btn-sm btn-compact shadow-xs fw-bold btn-success text-white';
                    btn.innerText = 'Active';
                } else {
                    btn.className = 'btn btn-sm btn-compact shadow-xs fw-bold btn-light border text-muted';
                    btn.innerText = 'Inactive';
                }

                showToast('Success', data.message, 'success');
            } else {
                showToast('Error', data.message || 'Update failed.', 'danger');
            }
        })
        .catch(() => {
            btn.disabled = false;
            showToast('Error', 'Request failed.', 'danger');
        });
    }

    function toggleWidgetStatus(id, url) {
        const btn = document.getElementById(`widget-status-btn-${id}`);
        if (!btn) return;

        btn.disabled = true;

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;

            if (data.success) {
                if (data.is_active) {
                    btn.className = 'btn btn-sm btn-compact shadow-xs fw-bold btn-success text-white';
                    btn.innerText = 'Active';
                } else {
                    btn.className = 'btn btn-sm btn-compact shadow-xs fw-bold btn-light border text-muted';
                    btn.innerText = 'Inactive';
                }

                showToast('Success', data.message, 'success');
            } else {
                showToast('Error', data.message || 'Update failed.', 'danger');
            }
        })
        .catch(() => {
            btn.disabled = false;
            showToast('Error', 'Request failed.', 'danger');
        });
    }

    function openAddWidgetModal() {
        document.getElementById('widgetManageForm').action = "{{ $widgetStoreRoute }}";
        document.getElementById('widgetFormMethodWrapper').innerHTML = '';

        document.getElementById('widgetModalTitle').innerHTML = '<i class="fas fa-plus-circle text-success"></i> <span>Add Widget</span>';

        document.getElementById('act_link').checked = true;
        toggleActionFields('link');

        document.getElementById('widget_type_input').value = 'whatsapp';
        document.getElementById('widget_label_input').value = 'WhatsApp Us';
        document.getElementById('widget_value_input').value = '';
        document.getElementById('widget_icon_input').value = 'fab fa-whatsapp';
        document.getElementById('widget_position_input').value = 'bottom_right';
        document.getElementById('widget_bg_color_input').value = '#25d366';
        document.getElementById('widget_icon_color_input').value = '#ffffff';
        document.getElementById('widget_order_input').value = '0';
        document.getElementById('widget_modal_title').value = '';
        document.getElementById('widget_modal_content').value = '';

        const modalEl = document.getElementById('widgetManageModal');

        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function openEditWidgetModal(widget) {
        document.getElementById('widgetManageForm').action = `{{ url('admin/theme-setting/display/widgets') }}/${widget.id}/update`;
        document.getElementById('widgetFormMethodWrapper').innerHTML = '';

        document.getElementById('widgetModalTitle').innerHTML = '<i class="fas fa-pencil text-primary"></i> <span>Edit Widget #' + widget.id + '</span>';

        const settings = is_array_fallback(widget.settings);
        const actionType = settings.action_type || 'link';

        if (actionType === 'modal') {
            document.getElementById('act_modal').checked = true;
            toggleActionFields('modal');
        } else {
            document.getElementById('act_link').checked = true;
            toggleActionFields('link');
        }

        document.getElementById('widget_type_input').value = widget.widget_type;
        document.getElementById('widget_label_input').value = widget.label || '';
        document.getElementById('widget_value_input').value = widget.value || '';
        document.getElementById('widget_icon_input').value = widget.icon || '';
        document.getElementById('widget_position_input').value = widget.position || 'bottom_right';
        document.getElementById('widget_bg_color_input').value = widget.bg_color || '#25d366';
        document.getElementById('widget_icon_color_input').value = widget.icon_color || '#ffffff';
        document.getElementById('widget_order_input').value = widget.order_index || 0;

        document.getElementById('widget_modal_title').value = settings.modal_title || '';
        document.getElementById('widget_modal_content').value = settings.modal_content || '';

        const modalEl = document.getElementById('widgetManageModal');

        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function is_array_fallback(data) {
        if (!data) return {};

        if (typeof data === 'object') return data;

        try {
            return JSON.parse(data);
        } catch (e) {
            return {};
        }
    }

    function syncWidgetPresets(type) {
        const iconInput = document.getElementById('widget_icon_input');
        const bgInput = document.getElementById('widget_bg_color_input');
        const labelInput = document.getElementById('widget_label_input');

        if (type === 'whatsapp') {
            iconInput.value = 'fab fa-whatsapp';
            bgInput.value = '#25d366';
            labelInput.value = 'WhatsApp Us';
            document.getElementById('act_link').checked = true;
            toggleActionFields('link');

        } else if (type === 'phone') {
            iconInput.value = 'fas fa-phone';
            bgInput.value = '#0284c7';
            labelInput.value = 'Call Now';
            document.getElementById('act_link').checked = true;
            toggleActionFields('link');

        } else if (type === 'support_modal') {
            iconInput.value = 'fas fa-headset';
            bgInput.value = '#0f172a';
            labelInput.value = 'Live Support';
            document.getElementById('act_modal').checked = true;
            toggleActionFields('modal');

        } else if (type === 'coupon_modal') {
            iconInput.value = 'fas fa-gift';
            bgInput.value = '#ef4444';
            labelInput.value = 'Special Offer';
            document.getElementById('act_modal').checked = true;
            toggleActionFields('modal');

        } else if (type === 'messenger') {
            iconInput.value = 'fab fa-facebook-messenger';
            bgInput.value = '#0084ff';
            labelInput.value = 'Messenger';
            document.getElementById('act_link').checked = true;
            toggleActionFields('link');

        } else if (type === 'email') {
            iconInput.value = 'fas fa-envelope';
            bgInput.value = '#ea4335';
            labelInput.value = 'Email Us';
            document.getElementById('act_link').checked = true;
            toggleActionFields('link');
        }
    }

    function confirmDelete(actionUrl, message) {
        document.getElementById('dynamicActionConfirmForm').action = actionUrl;
        document.getElementById('confirmModalText').innerText = message;

        const modalEl = document.getElementById('dynamicActionConfirmModal');

        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function showToast(title, message, type = 'success') {
        const holder = document.getElementById('dynamic-toast-holder');
        if (!holder) return;

        const toastId = 'toast-' + Date.now();
        const bg = type === 'success' ? 'bg-success text-white' : 'bg-danger text-white';

        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `dynamic-display-toast ${bg} p-3 d-flex align-items-center justify-content-between shadow-lg`;
        toast.style.borderRadius = '5px';

        const content = document.createElement('div');
        content.className = 'd-flex align-items-center gap-2';

        const icon = document.createElement('i');
        icon.className = `fas ${type === 'success' ? 'fa-check-circle' : 'fa-circle-xmark'} fs-5`;

        const textWrap = document.createElement('div');

        const titleEl = document.createElement('strong');
        titleEl.className = 'd-block';
        titleEl.style.fontSize = '13px';
        titleEl.textContent = title;

        const messageEl = document.createElement('small');
        messageEl.style.opacity = '0.95';
        messageEl.style.fontSize = '11px';
        messageEl.textContent = message || '';

        textWrap.appendChild(titleEl);
        textWrap.appendChild(messageEl);

        content.appendChild(icon);
        content.appendChild(textWrap);

        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'btn-close btn-close-white btn-sm ms-2 shadow-none';
        closeBtn.onclick = () => toast.remove();

        toast.appendChild(content);
        toast.appendChild(closeBtn);

        holder.appendChild(toast);

        setTimeout(() => toast.remove(), 3500);
    }
</script>

@endsection