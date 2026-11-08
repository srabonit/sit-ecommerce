
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

<style>
/* =========================================================
   KAIADMIN LITE — CLASSIC WHITE DESIGN SYSTEM
   Scoped to Display & Widget Settings
========================================================= */

#dynamic-display-root,
#widgetManageModal,
#dynamicActionConfirmModal {
    --display-primary: var(--bs-primary, #1572e8);
    --display-primary-hover: #1263c4;
    --display-secondary: #6c757d;
    --display-success: #31ce36;
    --display-danger: #f25961;
    --display-warning: #ffad46;
    --display-info: #48abf7;

    --display-body: #f7f9fc;
    --display-card: #ffffff;
    --display-heading: #2a2f5b;
    --display-text: #575962;
    --display-muted: #8d9498;
    --display-border: #e5e7eb;
    --display-light: #f8f9fa;

    --display-radius: 7px;
    --display-radius-sm: 5px;

    font-family: 'Public Sans', 'Inter', -apple-system,
        BlinkMacSystemFont, 'Segoe UI', sans-serif;

    color: var(--display-text);
    letter-spacing: normal;
}

#dynamic-display-root *,
#widgetManageModal *,
#dynamicActionConfirmModal * {
    box-sizing: border-box;
}

#dynamic-display-root {
    width: 100%;
    min-width: 0;
    padding-top: 18px;
    padding-bottom: 28px;
}

#dynamic-display-root .card,
#widgetManageModal .modal-content,
#dynamicActionConfirmModal .modal-content {
    background: var(--display-card);
    border: 1px solid var(--display-border) !important;
    border-radius: var(--display-radius) !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, .035) !important;
}

#dynamic-display-root .card {
    margin-bottom: 18px;
}

#dynamic-display-root .btn,
#widgetManageModal .btn,
#dynamicActionConfirmModal .btn {
    border-radius: 5px;
    font-weight: 600;
    transition: background-color .2s ease,
                border-color .2s ease,
                box-shadow .2s ease;
}

#dynamic-display-root .btn:focus-visible,
#widgetManageModal .btn:focus-visible,
#dynamicActionConfirmModal .btn:focus-visible {
    outline: 2px solid var(--display-primary);
    outline-offset: 2px;
}

#dynamic-display-root .btn-compact {
    min-height: 34px;
    padding: 6px 12px !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    font-size: 12px !important;
    line-height: 1.35;
    white-space: nowrap;
}

#dynamic-display-root .btn-primary {
    background-color: var(--display-primary);
    border-color: var(--display-primary);
}

#dynamic-display-root .btn-primary:hover {
    background-color: var(--display-primary-hover);
    border-color: var(--display-primary-hover);
}

#dynamic-display-root .form-control,
#dynamic-display-root .form-select,
#widgetManageModal .form-control,
#widgetManageModal .form-select {
    width: 100%;
    min-height: 38px;
    padding: 8px 11px;
    border: 1px solid var(--display-border) !important;
    border-radius: 5px !important;
    background-color: #fff;
    color: #2a2f5b !important;
    font-size: 13px !important;
    box-shadow: none !important;
    transition: border-color .2s ease, box-shadow .2s ease;
}

#dynamic-display-root .form-control:focus,
#dynamic-display-root .form-select:focus,
#widgetManageModal .form-control:focus,
#widgetManageModal .form-select:focus {
    border-color: var(--display-primary) !important;
    box-shadow: 0 0 0 3px rgba(21, 114, 232, .10) !important;
}

#dynamic-display-root .form-control::placeholder,
#widgetManageModal .form-control::placeholder {
    color: #adb5bd;
    opacity: 1;
}

#dynamic-display-root .form-label,
#widgetManageModal .form-label {
    display: block;
    margin-bottom: 7px;
    color: var(--display-heading);
    font-size: 12px;
    font-weight: 600;
}

#dynamic-display-root .form-check-input,
#widgetManageModal .form-check-input {
    cursor: pointer;
    flex-shrink: 0;
}

#dynamic-display-root .form-check-input:checked,
#widgetManageModal .form-check-input:checked {
    background-color: var(--display-primary);
    border-color: var(--display-primary);
}

#dynamic-display-root .form-switch .form-check-input {
    width: 2.1em;
    min-height: 1.1em;
}

#dynamic-display-root .input-group > .input-group-text {
    background: var(--display-light);
    border-color: var(--display-border);
    color: var(--display-muted);
    font-size: 12px;
}

#dynamic-display-root .input-group > .form-control,
#dynamic-display-root .input-group > .form-select {
    min-width: 0;
}

#dynamic-display-root .font-monospace,
#widgetManageModal .font-monospace {
    font-family: 'SFMono-Regular', Consolas, monospace !important;
    font-variant-numeric: tabular-nums;
}

/* PAGE HEADER */

#dynamic-display-root .display-header-wrap {
    margin-bottom: 22px !important;
}

#dynamic-display-root .display-header-wrap h5 {
    color: var(--display-heading) !important;
    font-size: clamp(17px, 2vw, 21px) !important;
    font-weight: 700;
    letter-spacing: -.35px;
}

#dynamic-display-root .display-header-wrap > div > small {
    margin-top: 5px !important;
    color: var(--display-muted) !important;
    font-size: 12px !important;
}

#dynamic-display-root .display-header-wrap .badge {
    border-radius: 4px;
    font-size: 10px !important;
    font-weight: 600;
}

/* OVERVIEW METRICS */

#dynamic-display-root .metric-card {
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
    min-height: 112px;
    padding: 16px;
    overflow: hidden;
    background: #fff;
    border: 1px solid var(--display-border) !important;
    border-radius: var(--display-radius) !important;
    transition: box-shadow .2s ease, transform .2s ease;
}

#dynamic-display-root .metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, .055) !important;
}

#dynamic-display-root .metric-card-blue {
    border-left: 3px solid #1572e8 !important;
}

#dynamic-display-root .metric-card-green {
    border-left: 3px solid #31ce36 !important;
}

#dynamic-display-root .metric-card-purple {
    border-left: 3px solid #6861ce !important;
}

#dynamic-display-root .metric-card-amber {
    border-left: 3px solid #ffad46 !important;
}

#dynamic-display-root .stat-label-title {
    color: var(--display-muted) !important;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.4;
}

#dynamic-display-root .stat-val-num {
    color: var(--display-heading) !important;
    font-size: clamp(17px, 2vw, 21px);
    font-weight: 700;
    line-height: 1.35;
    letter-spacing: -.4px;
}

#dynamic-display-root .stat-sub-text {
    margin-top: 4px;
    color: var(--display-muted) !important;
    font-size: 10px;
}

#dynamic-display-root .metric-icon-square {
    display: flex;
    width: 36px;
    height: 36px;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    border-radius: 6px !important;
    font-size: 15px;
}

/* SECTION HEADERS */

#dynamic-display-root .section-header-wrap {
    display: flex;
    min-height: 48px;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 11px 16px;
    background: #fff;
    border-bottom: 1px solid var(--display-border);
}

#dynamic-display-root .section-header-wrap > span {
    color: var(--display-heading) !important;
    font-size: 13px !important;
    font-weight: 700;
}

#dynamic-display-root .section-header-wrap > span i {
    margin-right: 5px;
}

/* NAVIGATION TABS */

#dynamic-display-root #displayTabs {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 0;
}

#dynamic-display-root #displayTabs .nav-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 38px;
    padding: 8px 16px;
    color: var(--display-text);
    background: #fff;
    border: 1px solid var(--display-border);
    border-radius: 5px;
    font-size: 12px;
    font-weight: 600;
    transition: all .2s ease;
}

#dynamic-display-root #displayTabs .nav-link:hover {
    color: var(--display-primary);
    border-color: var(--display-primary);
}

#dynamic-display-root #displayTabs .nav-link.active {
    color: #fff !important;
    background: var(--display-primary) !important;
    border-color: var(--display-primary) !important;
    box-shadow: 0 2px 5px rgba(21, 114, 232, .15);
}

/* DISPLAY MODE SELECTION */

#dynamic-display-root .presentation-radio-card {
    display: flex;
    min-height: 68px;
    align-items: flex-start;
    gap: 10px;
    padding: 13px 14px;
    background: #fff;
    border: 1px solid var(--display-border);
    border-radius: 6px;
    cursor: pointer;
    transition: all .2s ease;
}

#dynamic-display-root .presentation-radio-card:hover {
    background: #f8fbff;
    border-color: #a7c8f5;
}

#dynamic-display-root .presentation-radio-card.active-mode {
    background: #f5f9ff;
    border-color: var(--display-primary);
    box-shadow: 0 0 0 1px var(--display-primary);
}

#dynamic-display-root .presentation-radio-card strong {
    color: var(--display-heading) !important;
    font-size: 13px !important;
}

#dynamic-display-root .presentation-radio-card small {
    color: var(--display-muted) !important;
    font-size: 11px !important;
    line-height: 1.5 !important;
}

#dynamic-display-root .presentation-radio-card .form-check-input {
    margin-top: 3px;
}

/* TABLE DESIGN */

#dynamic-display-root .custom-display-table {
    width: 100%;
    min-width: 920px;
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
    table-layout: auto;
}

#dynamic-display-root .custom-display-table thead th {
    padding: 12px 11px;
    color: #6c757d;
    background: #f8f9fa;
    border-bottom: 1px solid var(--display-border);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .35px;
    text-transform: uppercase;
    vertical-align: middle;
    white-space: nowrap;
}

#dynamic-display-root .custom-display-table tbody td {
    padding: 11px;
    color: var(--display-text);
    background: #fff;
    border-bottom: 1px solid #f0f1f3;
    font-size: 12px;
    line-height: 1.5;
    vertical-align: middle;
}

#dynamic-display-root .custom-display-table tbody tr:last-child td {
    border-bottom: 0;
}

#dynamic-display-root .custom-display-table tbody tr:hover td {
    background: #fafcff;
}

#dynamic-display-root .custom-display-table th,
#dynamic-display-root .custom-display-table td {
    overflow-wrap: anywhere;
}

#dynamic-display-root .custom-display-table .text-truncate {
    max-width: 240px;
}

#dynamic-display-root .pc-fit-container {
    width: 100%;
    max-width: 100%;
    overflow-x: auto !important;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}

#dynamic-display-root .pc-fit-container::-webkit-scrollbar {
    height: 6px;
}

#dynamic-display-root .pc-fit-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

/* STATUS BADGES */

#dynamic-display-root .badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: .1px;
}

/* TABLE ACTION BUTTONS */

#dynamic-display-root .custom-display-table .btn {
    min-width: 30px;
    min-height: 30px;
    padding: 5px;
}

#dynamic-display-root .custom-display-table .btn i {
    font-size: 11px !important;
}

/* SETTINGS PANEL */

#dynamic-display-root #speedDialCustomizerWrap > div {
    background: #f8f9fb !important;
    border: 1px solid var(--display-border) !important;
    border-radius: 6px !important;
}

#dynamic-display-root .card .p-3 {
    min-width: 0;
}

#dynamic-display-root .form-control-color {
    height: 39px;
    min-height: 39px;
    padding: 4px !important;
    cursor: pointer;
}

/* ALERTS */

#dynamic-display-root .alert {
    border: 1px solid var(--display-border) !important;
    border-radius: 6px !important;
    font-size: 12px;
}

#dynamic-display-root .alert-success {
    background: #f0fbf3;
    border-color: #ccebd4 !important;
}

#dynamic-display-root .alert-danger {
    background: #fff5f5;
    border-color: #f4d2d2 !important;
}

/* MODALS */

#widgetManageModal,
#dynamicActionConfirmModal {
    --bs-modal-border-radius: 8px;
}

#widgetManageModal .modal-dialog {
    width: calc(100% - 24px);
    max-width: 720px;
    margin: 12px auto;
}

#widgetManageModal .modal-content {
    overflow: hidden;
}

#widgetManageModal .modal-header,
#widgetManageModal .modal-footer {
    min-height: 54px;
    padding: 12px 18px !important;
    background: #fff !important;
    border-color: var(--display-border) !important;
}

#widgetManageModal .modal-title {
    color: var(--display-heading) !important;
    font-size: 15px !important;
    font-weight: 700;
}

#widgetManageModal .modal-body {
    padding: 20px !important;
    background: #fff;
}

#widgetManageModal .modal-body .row {
    row-gap: 10px;
}

#widgetManageModal .modal-body .bg-light {
    background: #f8f9fb !important;
}

#widgetManageModal .modal-body .border {
    border-color: var(--display-border) !important;
}

#widgetManageModal textarea.form-control {
    min-height: 105px;
    resize: vertical;
}

#dynamicActionConfirmModal .modal-content {
    padding: 10px;
}

#dynamicActionConfirmModal .modal-body {
    padding: 12px !important;
}

#dynamicActionConfirmModal h6 {
    color: var(--display-heading) !important;
    font-size: 16px !important;
}

#dynamicActionConfirmModal p {
    font-size: 12px !important;
}

/* PAGINATION */

#dynamic-display-root .pagination {
    flex-wrap: wrap;
    gap: 4px;
    margin-bottom: 0;
}

#dynamic-display-root .pagination .page-link {
    min-width: 32px;
    min-height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 5px 10px;
    border-radius: 5px !important;
    color: var(--display-primary);
    font-size: 12px;
}

#dynamic-display-root .pagination .active > .page-link {
    color: #fff;
    background: var(--display-primary);
    border-color: var(--display-primary);
}

/* TOAST */

#dynamic-display-root .dynamic-display-toast,
.dynamic-display-toast {
    position: fixed;
    right: 20px;
    bottom: 20px;
    z-index: 108000;
    display: flex;
    min-width: 280px;
    max-width: min(420px, calc(100vw - 32px));
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 16px;
    border-radius: 7px !important;
    box-shadow: 0 8px 25px rgba(0, 0, 0, .15);
    animation: displayToastIn .25s ease;
}

@keyframes displayToastIn {
    from {
        transform: translateY(10px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* RESPONSIVE DESIGN */

@media (min-width: 1200px) {
    #dynamic-display-root .custom-display-table {
        min-width: 920px;
    }
}

@media (max-width: 991.98px) {
    #dynamic-display-root {
        padding-left: 12px !important;
        padding-right: 12px !important;
    }

    #dynamic-display-root .metric-card {
        min-height: 105px;
    }

    #dynamic-display-root .custom-display-table {
        min-width: 920px;
    }

    #widgetManageModal .modal-dialog {
        max-width: 650px;
    }
}

@media (max-width: 767.98px) {
    #dynamic-display-root {
        padding-top: 12px;
        padding-bottom: 20px;
    }

    #dynamic-display-root .display-header-wrap {
        align-items: flex-start !important;
        gap: 12px !important;
    }

    #dynamic-display-root .display-header-wrap h5 {
        font-size: 17px !important;
    }

    #dynamic-display-root .top-action-btns {
        display: grid;
        width: 100%;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    #dynamic-display-root .top-action-btns .btn-popup-create {
        grid-column: 1 / -1;
    }

    #dynamic-display-root .top-action-btns .btn {
        width: 100%;
        min-height: 38px;
        padding: 7px 8px !important;
        font-size: 12px !important;
    }

    #dynamic-display-root .row.g-2 {
        --bs-gutter-x: 10px;
        --bs-gutter-y: 10px;
    }

    #dynamic-display-root .metric-card {
        min-height: 98px;
        padding: 12px !important;
    }

    #dynamic-display-root .stat-label-title {
        font-size: 10px;
    }

    #dynamic-display-root .stat-val-num {
        font-size: 17px;
    }

    #dynamic-display-root .stat-sub-text {
        font-size: 10px;
    }

    #dynamic-display-root .metric-icon-square {
        width: 30px;
        height: 30px;
        font-size: 13px;
    }

    #dynamic-display-root #displayTabs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        width: 100%;
    }

    #dynamic-display-root #displayTabs .nav-item,
    #dynamic-display-root #displayTabs .nav-link {
        width: 100%;
    }

    #dynamic-display-root #displayTabs .nav-link {
        min-height: 40px;
        padding: 7px 5px;
        font-size: 11px;
    }

    #dynamic-display-root .section-header-wrap {
        padding: 11px 12px;
    }

    #dynamic-display-root .section-header-wrap > span {
        font-size: 12px !important;
    }

    #dynamic-display-root .section-header-wrap .btn {
        min-height: 34px;
        font-size: 11px !important;
    }

    #dynamic-display-root .presentation-radio-card {
        min-height: 62px;
        padding: 11px;
    }

    #dynamic-display-root .pc-fit-container {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }

    #dynamic-display-root .custom-display-table {
        width: max-content;
        min-width: 920px;
        table-layout: auto;
    }

    #dynamic-display-root .custom-display-table thead th {
        padding: 10px;
        font-size: 10px;
    }

    #dynamic-display-root .custom-display-table tbody td {
        padding: 10px;
        font-size: 11px;
    }

    #widgetManageModal .modal-dialog {
        width: calc(100% - 20px);
        max-width: none;
        margin: 10px auto;
    }

    #widgetManageModal .modal-body {
        padding: 15px !important;
    }

    #dynamicActionConfirmModal .modal-dialog {
        max-width: 340px;
        margin: 12px auto;
    }
}

@media (max-width: 420px) {
    #dynamic-display-root .metric-card {
        min-height: 90px;
        padding: 9px !important;
    }

    #dynamic-display-root .stat-label-title {
        font-size: 9px;
    }

    #dynamic-display-root .stat-val-num {
        font-size: 15px;
    }

    #dynamic-display-root .stat-sub-text {
        font-size: 9px;
    }

    #dynamic-display-root .metric-icon-square {
        width: 27px;
        height: 27px;
        font-size: 11px;
    }

    #dynamic-display-root .metric-card .stat-val-num span {
        font-size: 9px !important;
    }

    #dynamic-display-root .top-action-btns .btn {
        font-size: 11px !important;
    }

    #widgetManageModal .modal-body .row {
        --bs-gutter-x: 10px;
    }

    #widgetManageModal .form-label {
        font-size: 11px;
    }

    .dynamic-display-toast {
        right: 10px;
        bottom: 10px;
        min-width: 0;
        width: calc(100% - 20px);
    }
}

@media (prefers-reduced-motion: reduce) {
    #dynamic-display-root *,
    #widgetManageModal *,
    #dynamicActionConfirmModal * {
        transition-duration: .01ms !important;
        animation-duration: .01ms !important;
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
                    <span>Floating widgets.</span>
                </h5>
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
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small shadow-xs mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small shadow-xs mb-3 d-flex align-items-center justify-content-between" role="alert">
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
                    <div class="metric-icon-square bg-primary-subtle text-primary">
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
                    <div class="metric-icon-square bg-success-subtle text-success">
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
                    <div class="metric-icon-square text-primary" style="background-color: #f3e8ff; color: #7e22ce;">
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
                    <div class="metric-icon-square bg-warning-subtle text-warning-emphasis">
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
                <div class="section-header-wrap">
                    <span class="fw-bold text-dark small">
                        <i class="fas fa-sliders text-primary me-1"></i> Display Settings
                    </span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1">
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

                                    <label class="presentation-radio-card {{ $currentDisplayStyle === 'speed_dial' ? 'active-mode' : '' }}" for="style_speed_dial">
                                        <input class="form-check-input mt-1" type="radio" name="display_style" id="style_speed_dial" value="speed_dial" {{ $currentDisplayStyle === 'speed_dial' ? 'checked' : '' }} onchange="toggleDisplayStyleOptions(this.value)">

                                        <div>
                                            <strong class="d-block text-dark">Speed Dial</strong>
                                            <small class="text-muted d-block">
                                                One main button expands all widgets.
                                            </small>
                                        </div>
                                    </label>

                                    <label class="presentation-radio-card {{ $currentDisplayStyle === 'direct_stack' ? 'active-mode' : '' }}" for="style_direct_stack">
                                        <input class="form-check-input mt-1" type="radio" name="display_style" id="style_direct_stack" value="direct_stack" {{ $currentDisplayStyle === 'direct_stack' ? 'checked' : '' }} onchange="toggleDisplayStyleOptions(this.value)">

                                        <div>
                                            <strong class="d-block text-dark">Direct Stack</strong>
                                            <small class="text-muted d-block">
                                                All widgets stay visible.
                                            </small>
                                        </div>
                                    </label>

                                </div>
                            </div>

                            {{-- Speed Dial Settings --}}
                            <div class="col-12 col-lg-7" id="speedDialCustomizerWrap" style="{{ $currentDisplayStyle === 'direct_stack' ? 'display: none;' : '' }}">
                                <div class="p-3 border rounded bg-light bg-opacity-25">

                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                        <label class="form-label fw-bold text-dark mb-0 small">Hub Settings</label>

                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="show_label" id="hub_show_label" value="1" {{ ($hubSettings['show_label'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label text-dark fw-bold" for="hub_show_label" style="font-size: 11px;">Show Label</label>
                                        </div>
                                    </div>

                                    <div class="row g-3">

                                        <div class="col-12 col-sm-6">
                                            <label class="form-label fw-bold text-dark">Badge Text</label>
                                            <input type="text" name="hub_label" class="form-control font-monospace shadow-none" placeholder="Chat with Us" value="{{ $hubSettings['hub_label'] ?? 'Chat with Us' }}">
                                        </div>

                                        <div class="col-12 col-sm-6">
                                            <label class="form-label fw-bold text-dark">Icon Class</label>
                                            <input type="text" name="hub_icon" class="form-control font-monospace shadow-none" placeholder="fas fa-comment-dots" value="{{ $hubSettings['hub_icon'] ?? 'fas fa-comment-dots' }}">
                                        </div>

                                        <div class="col-6 col-sm-3">
                                            <label class="form-label fw-bold text-dark">Button BG</label>
                                            <input type="color" name="hub_bg_color" class="form-control form-control-color w-100 shadow-none p-1" value="{{ $hubSettings['hub_bg_color'] ?? '#1e3a8a' }}">
                                        </div>

                                        <div class="col-6 col-sm-3">
                                            <label class="form-label fw-bold text-dark">Icon Color</label>
                                            <input type="color" name="hub_icon_color" class="form-control form-control-color w-100 shadow-none p-1" value="{{ $hubSettings['hub_icon_color'] ?? '#ffffff' }}">
                                        </div>

                                        <div class="col-6 col-sm-3">
                                            <label class="form-label fw-bold text-dark">Badge BG</label>
                                            <input type="color" name="badge_bg_color" class="form-control form-control-color w-100 shadow-none p-1" value="{{ $hubSettings['badge_bg_color'] ?? '#ffffff' }}">
                                        </div>

                                        <div class="col-6 col-sm-3">
                                            <label class="form-label fw-bold text-dark">Text Color</label>
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

                <div class="section-header-wrap">
                    <span class="fw-bold text-dark small">
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

                                    <td class="ps-3 text-center text-muted font-monospace bn-digit">
                                        {{ $widget->id }}
                                    </td>

                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="rounded-circle d-inline-flex align-items-center justify-content-center text-white flex-shrink-0" style="width: 32px; height: 32px; background-color: {{ $widget->bg_color ?? '#2563eb' }}; font-size: 12px;">
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
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 font-monospace">
                                                <i class="fas fa-window-maximize me-1"></i> Modal
                                            </span>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 font-monospace">
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
                                        <span class="badge bg-light text-secondary border px-2 py-1 font-monospace">
                                            {{ str_replace('_', ' ', strtoupper($widget->position)) }}
                                        </span>
                                    </td>

                                    <td class="text-center font-monospace bn-digit">
                                        {{ $widget->order_index }}
                                    </td>

                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-compact shadow-xs fw-bold {{ $widget->is_active ? 'btn-success text-white' : 'btn-light border text-muted' }}" onclick="toggleWidgetStatus({{ $widget->id }}, '{{ $widgetToggleUrl }}')" id="widget-status-btn-{{ $widget->id }}" style="min-width: 68px;">
                                            {{ $widget->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </td>

                                    <td class="text-end pe-3">
                                        <div class="d-inline-flex align-items-center gap-1">

                                            <button type="button" class="btn btn-sm btn-light border p-1 text-primary shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px;" onclick="openEditWidgetModal({{ json_encode($widget) }})" title="Edit">
                                                <i class="fas fa-pencil"></i>
                                            </button>

                                            <button type="button" class="btn btn-sm btn-light border p-1 text-danger shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px;" onclick="confirmDelete('{{ $widgetDestroyUrl }}', 'Delete this widget?')" title="Delete">
                                                <i class="fas fa-trash-can"></i>
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

                <div class="section-header-wrap">
                    <span class="fw-bold text-dark small">
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

                                    <td class="ps-3 text-center text-muted font-monospace bn-digit">
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
                                        <span class="badge bg-light text-dark border px-2 py-1 font-monospace">
                                            {{ strtoupper(str_replace('_', ' ', $popup->popup_type)) }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="text-muted small font-monospace">
                                            @if($popup->trigger_type === 'delay')
                                                <i class="fas fa-clock text-warning me-1"></i> {{ $popup->trigger_value }}s Delay
                                            @elseif($popup->trigger_type === 'scroll')
                                                <i class="fas fa-arrows-up-down text-info me-1"></i> {{ $popup->trigger_value }}% Scroll
                                            @else
                                                <i class="fas fa-bolt text-danger me-1"></i> Instant
                                            @endif
                                        </span>
                                    </td>

                                    <td class="text-center font-monospace">
                                        {{ $popup->frequency_days == 0 ? 'Every Reload' : 'Every ' . $popup->frequency_days . ' Days' }}
                                    </td>

                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-compact shadow-xs fw-bold {{ $popup->is_active ? 'btn-success text-white' : 'btn-light border text-muted' }}" onclick="togglePopupStatus({{ $popup->id }}, '{{ $popupToggleUrl }}')" id="popup-status-btn-{{ $popup->id }}" style="min-width: 72px;">
                                            {{ $popup->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </td>

                                    <td class="text-end pe-3">
                                        <div class="d-inline-flex align-items-center gap-1">

                                            <a href="{{ $popupEditUrl }}" class="btn btn-sm btn-light border p-1 text-primary shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px;" title="Edit">
                                                <i class="fas fa-pencil"></i>
                                            </a>

                                            <button type="button" class="btn btn-sm btn-light border p-1 text-danger shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px;" onclick="confirmDelete('{{ $popupDestroyUrl }}', 'Delete this popup?')" title="Delete">
                                                <i class="fas fa-trash-can"></i>
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
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" id="widgetModalTitle">
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
                        <div class="col-12 mb-2 p-3 bg-light border rounded">
                            <label class="form-label fw-bold text-dark small d-block mb-2">
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
                            <div class="p-3 border border-warning bg-warning bg-opacity-10 rounded mb-2">

                                <h6 class="fw-bold text-dark small mb-3 d-flex align-items-center gap-1">
                                    <i class="fas fa-window-maximize text-warning"></i> Modal Settings
                                </h6>

                                <div class="row g-3">

                                    <div class="col-12">
                                        <label class="form-label fw-bold small text-dark">Modal Title</label>
                                        <input type="text" name="modal_title" id="widget_modal_title" class="form-control shadow-none" placeholder="Live Support">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-bold small text-dark">Modal Content (HTML / Shortcode)</label>

                                        <textarea name="modal_content" id="widget_modal_content" rows="4" class="form-control font-monospace shadow-none" placeholder="Paste HTML, text or shortcode..."></textarea>

                                        <small class="text-muted font-monospace d-block mt-2" style="font-size: 11px;">
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

                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 48px; height: 48px; border-radius: 50% !important;">
                    <i class="fas fa-trash-can fs-4"></i>
                </div>

                <h6 class="fw-bold text-dark mb-2" style="font-size: 14px;">Confirm Delete</h6>

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
