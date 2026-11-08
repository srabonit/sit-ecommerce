
@extends('layouts.admin')

@section('title', 'Activity Logs - ' . config('app.name'))

@section('admin_content')

<style>
    /* =========================================================
       KAIADMIN CLASSIC - ACTIVITY LOGS
       Scoped UI | Bootstrap 5 Compatible
    ========================================================= */

    #auditLogsRoot {
        --audit-primary: var(--bs-primary, #1572e8);
        --audit-primary-soft: rgba(21, 114, 232, .08);
        --audit-body-bg: #f8f9fa;
        --audit-card-bg: #ffffff;
        --audit-heading: #2a2f5b;
        --audit-text: #495057;
        --audit-muted: #8d9498;
        --audit-border: #ebedf2;
        --audit-success: #31ce36;
        --audit-danger: #f25961;
        --audit-warning: #ffad46;
        --audit-info: #48abf7;

        color: var(--audit-text);
        font-size: 13px;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        padding: 18px 20px;
    }

    /* Base */

    #auditLogsRoot *,
    #logDiffModal *,
    #cleanupModal *,
    #auditConfirmModal * {
        box-sizing: border-box;
    }

    #auditLogsRoot .btn,
    #auditLogsRoot button,
    #auditLogsRoot .form-control,
    #auditLogsRoot .form-select,
    #auditLogsRoot .badge,
    #auditLogsRoot .input-group,
    #auditLogsRoot .input-group-text,
    #auditLogsRoot .alert,
    #logDiffModal .btn,
    #cleanupModal .btn,
    #cleanupModal .form-control,
    #cleanupModal .form-select,
    #cleanupModal .alert,
    #auditConfirmModal .btn {
        border-radius: 5px !important;
    }

    #auditLogsRoot .audit-card-table,
    #auditLogsRoot .filter-card-box,
    #logDiffModal .modal-content,
    #cleanupModal .modal-content,
    #auditConfirmModal .modal-content {
        border-radius: 8px !important;
    }

    #auditLogsRoot .text-dark,
    #logDiffModal .text-dark,
    #cleanupModal .text-dark,
    #auditConfirmModal .text-dark {
        color: var(--audit-heading) !important;
    }

    #auditLogsRoot .text-muted {
        color: var(--audit-muted) !important;
    }

    #auditLogsRoot .shadow-xs,
    #logDiffModal .shadow-xs,
    #cleanupModal .shadow-xs,
    #auditConfirmModal .shadow-xs {
        box-shadow: none !important;
    }

    /* Page Header */

    #auditLogsRoot .audit-page-header {
        margin-bottom: 18px;
    }

    #auditLogsRoot .audit-page-title {
        color: var(--audit-heading);
        font-size: clamp(17px, 2vw, 20px);
        font-weight: 700;
        letter-spacing: -.35px;
        line-height: 1.4;
        margin: 0;
    }

    #auditLogsRoot .audit-page-title i {
        font-size: 17px;
    }

    #auditLogsRoot .audit-page-subtitle {
        display: block;
        font-size: 12px;
        color: var(--audit-muted);
        margin-top: 4px;
    }

    #auditLogsRoot .audit-header-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    #auditLogsRoot .audit-header-actions .btn {
        min-height: 34px;
        padding: 7px 13px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        white-space: nowrap;
        margin: 0 !important;
        transition: all .2s ease;
    }

    #auditLogsRoot .audit-header-actions .btn:hover {
        transform: translateY(-1px);
    }

    /* Common Cards */

    #auditLogsRoot .filter-card-box,
    #auditLogsRoot .audit-card-table {
        position: relative;
        background: var(--audit-card-bg);
        border: 1px solid var(--audit-border);
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035);
    }

    #auditLogsRoot .filter-card-box {
        padding: 17px;
        margin-bottom: 18px;
    }

    #auditLogsRoot .audit-card-table {
        margin-bottom: 20px;
        overflow: hidden;
    }

    /* Form Controls */

    #auditLogsRoot .form-control,
    #auditLogsRoot .form-select,
    #cleanupModal .form-control,
    #cleanupModal .form-select {
        min-height: 35px;
        padding: 6px 10px;
        font-size: 12.5px;
        line-height: 1.5;
        color: var(--audit-heading);
        background-color: #fff;
        border: 1px solid #e3e6ed;
        box-shadow: none;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    #auditLogsRoot .form-control::placeholder,
    #cleanupModal .form-control::placeholder {
        color: #adb5bd;
        opacity: 1;
    }

    #auditLogsRoot .form-control:focus,
    #auditLogsRoot .form-select:focus,
    #cleanupModal .form-control:focus,
    #cleanupModal .form-select:focus {
        border-color: var(--audit-primary);
        box-shadow: 0 0 0 3px var(--audit-primary-soft);
        outline: none;
    }

    #auditLogsRoot .input-group {
        min-height: 35px;
        border: 1px solid #e3e6ed !important;
        background: #fff;
        transition: border-color .2s ease;
    }

    #auditLogsRoot .input-group:focus-within {
        border-color: var(--audit-primary) !important;
        box-shadow: 0 0 0 3px var(--audit-primary-soft);
    }

    #auditLogsRoot .input-group .form-control {
        min-height: 33px;
    }

    #auditLogsRoot .input-group-text {
        min-height: 33px;
        padding: 0 11px;
        color: var(--audit-muted);
        background: #fff;
        border: 0;
    }

    /* Buttons */

    #auditLogsRoot .btn,
    #logDiffModal .btn,
    #cleanupModal .btn,
    #auditConfirmModal .btn {
        font-size: 12px;
        font-weight: 600;
        min-height: 32px;
        transition: all .2s ease;
    }

    #auditLogsRoot .btn-primary,
    #logDiffModal .btn-primary {
        background-color: var(--audit-primary);
        border-color: var(--audit-primary);
    }

    #auditLogsRoot .btn-primary:hover,
    #logDiffModal .btn-primary:hover {
        filter: brightness(.94);
    }

    #auditLogsRoot .btn-light,
    #cleanupModal .btn-light,
    #auditConfirmModal .btn-light {
        background: #fff;
        border-color: var(--audit-border);
        color: var(--audit-text);
    }

    #auditLogsRoot .btn-light:hover,
    #cleanupModal .btn-light:hover,
    #auditConfirmModal .btn-light:hover {
        background: #f5f7fa;
        border-color: #d8dce5;
    }

    #auditLogsRoot .btn-outline-danger:hover {
        background: var(--audit-danger);
        border-color: var(--audit-danger);
    }

    #auditLogsRoot .btn:focus-visible,
    #logDiffModal .btn:focus-visible,
    #cleanupModal .btn:focus-visible,
    #auditConfirmModal .btn:focus-visible {
        outline: 2px solid var(--audit-primary);
        outline-offset: 2px;
        box-shadow: none;
    }

    /* Filter Toolbar */

    #auditLogsRoot #auditFilterForm .row {
        --bs-gutter-y: 10px;
    }

    #auditLogsRoot #auditFilterForm .form-control,
    #auditLogsRoot #auditFilterForm .form-select {
        width: 100%;
    }

    #auditLogsRoot #auditFilterForm button[type="submit"] {
        min-height: 35px;
        padding: 6px 12px !important;
    }

    #auditLogsRoot #auditFilterForm button[title="Reset Filters"] {
        min-width: 35px;
        min-height: 35px;
        padding: 5px 9px !important;
        margin-left: 0 !important;
    }

    /* AJAX Alert */

    #auditLogsRoot #ajaxAlertBox {
        border: 1px solid transparent !important;
        padding: 10px 13px !important;
        font-size: 12px;
        border-radius: 6px !important;
    }

    #auditLogsRoot #ajaxAlertBox.alert-success {
        background: #eaf8ef;
        border-color: #c8ebd3 !important;
        color: #198754;
    }

    #auditLogsRoot #ajaxAlertBox.alert-danger {
        background: #fff0f0;
        border-color: #f5cccc !important;
        color: #dc3545;
    }

    #auditLogsRoot #ajaxAlertBox .btn-close {
        font-size: 10px;
    }

    /* Table */

    #auditLogsRoot .table-responsive {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #ced4da #f8f9fa;
    }

    #auditLogsRoot .custom-audit-table {
        width: 100%;
        min-width: 1180px;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: auto;
    }

    #auditLogsRoot .custom-audit-table th {
        background: #f8f9fa;
        color: #6c757d;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .35px;
        white-space: nowrap;
        padding: 12px 12px;
        vertical-align: middle;
        border-bottom: 1px solid var(--audit-border);
        border-top: 0;
    }

    #auditLogsRoot .custom-audit-table td {
        padding: 11px 12px;
        vertical-align: middle;
        font-size: 12px;
        line-height: 1.5;
        color: var(--audit-text);
        border-bottom: 1px solid #f0f1f4;
        background: #fff;
    }

    #auditLogsRoot .custom-audit-table tbody tr {
        transition: background-color .15s ease;
    }

    #auditLogsRoot .custom-audit-table tbody tr:hover td {
        background: #fafbfe;
    }

    #auditLogsRoot .custom-audit-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #auditLogsRoot .custom-audit-table td,
    #auditLogsRoot .custom-audit-table th {
        overflow-wrap: anywhere;
    }

    #auditLogsRoot .custom-audit-table .font-monospace {
        font-size: 11.5px;
    }

    /* Soft Action Badges */

    #auditLogsRoot .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 8px;
        font-size: 10.5px;
        font-weight: 700;
        line-height: 1.35;
        border-radius: 4px !important;
        white-space: nowrap;
    }

    #auditLogsRoot .badge-soft-create {
        background: #e8f8ee;
        color: #198754;
        border: 1px solid #ccebd7;
    }

    #auditLogsRoot .badge-soft-update {
        background: #e9f3ff;
        color: #1572e8;
        border: 1px solid #d0e4ff;
    }

    #auditLogsRoot .badge-soft-delete {
        background: #fff0f0;
        color: #dc3545;
        border: 1px solid #f5d0d2;
    }

    #auditLogsRoot .badge-soft-status {
        background: #fff7e6;
        color: #b7791f;
        border: 1px solid #f7e3b5;
    }

    #auditLogsRoot .badge-soft-auth {
        background: #f4edff;
        color: #7952b3;
        border: 1px solid #e5d8fa;
    }

    #auditLogsRoot .badge-soft-misc {
        background: #f0f2f5;
        color: #606975;
        border: 1px solid #e0e4e9;
    }

    /* Table Pagination */

    #auditLogsRoot #auditPaginationWrapper {
        padding: 12px 16px !important;
        background: #fff !important;
        border-top: 1px solid var(--audit-border) !important;
    }

    #auditLogsRoot #auditStatsText {
        font-size: 11.5px;
        color: var(--audit-muted);
        margin: 0;
    }

    #auditLogsRoot #auditPaginationLinks {
        max-width: 100%;
        overflow-x: auto;
    }

    #auditLogsRoot #auditPaginationLinks .pagination {
        margin: 0;
        flex-wrap: wrap;
        gap: 3px;
    }

    #auditLogsRoot #auditPaginationLinks .page-link {
        min-width: 30px;
        min-height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 5px 9px;
        border-radius: 4px !important;
        border-color: var(--audit-border);
        color: var(--audit-text);
        font-size: 11px;
        box-shadow: none;
    }

    #auditLogsRoot #auditPaginationLinks .page-item.active .page-link {
        background: var(--audit-primary);
        border-color: var(--audit-primary);
        color: #fff;
    }

    /* Loading Overlay */

    #auditLoadingOverlay {
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, .78);
        backdrop-filter: blur(2px);
        z-index: 20;
        display: none;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    #auditLoadingOverlay .spinner-border {
        width: 36px !important;
        height: 36px !important;
    }

    /* Modals */

    #logDiffModal .modal-dialog,
    #cleanupModal .modal-dialog,
    #auditConfirmModal .modal-dialog {
        width: calc(100% - 24px);
        max-width: 100%;
        margin: 12px auto;
    }

    #logDiffModal .modal-content,
    #cleanupModal .modal-content,
    #auditConfirmModal .modal-content {
        background: #fff;
        border: 1px solid var(--audit-border) !important;
        padding: 18px !important;
        box-shadow: 0 12px 40px rgba(0, 0, 0, .12) !important;
    }

    #logDiffModal .modal-header,
    #cleanupModal .modal-header {
        border-bottom: 1px solid var(--audit-border);
    }

    #logDiffModal h6,
    #cleanupModal h6,
    #auditConfirmModal h6 {
        font-size: 14px !important;
        line-height: 1.5;
    }

    #logDiffModal .btn-close,
    #cleanupModal .btn-close {
        font-size: 11px;
    }

    /* Snapshot Information */

    #logDiffModal .modal-content > .bg-light {
        background: #f8f9fc !important;
        border-color: var(--audit-border) !important;
        padding: 12px !important;
    }

    #logDiffModal .modal-content > .row {
        --bs-gutter-y: 12px;
    }

    #logDiffModal .diff-box {
        background: #f7f8fa;
        color: #343a40;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 13px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 11.5px;
        line-height: 1.65;
        max-height: 300px;
        overflow: auto;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
        word-break: break-word;
        margin: 0;
    }

    /* Cleanup Radio Options */

    #cleanupModal .cleanup-radio-group {
        gap: 7px !important;
    }

    #cleanupModal .cleanup-radio-group .form-check {
        display: flex;
        align-items: center;
        min-height: 36px;
        margin: 0 !important;
        padding: 7px 10px 7px 30px;
        background: #f8f9fb;
        border: 1px solid var(--audit-border);
        border-radius: 5px;
        transition: all .2s ease;
        cursor: pointer;
    }

    #cleanupModal .cleanup-radio-group .form-check:hover {
        background: var(--audit-primary-soft);
        border-color: #c7ddfa;
    }

    #cleanupModal .cleanup-radio-group .form-check-input {
        margin-left: -19px;
        margin-top: 0;
        flex-shrink: 0;
        cursor: pointer;
    }

    #cleanupModal .cleanup-radio-group .form-check-label {
        font-size: 11.5px;
        line-height: 1.4;
        cursor: pointer;
    }

    #cleanupModal .form-label {
        font-size: 12px !important;
        color: var(--audit-heading);
        font-weight: 600;
    }

    #cleanupModal .cleanup-mode-panel small {
        font-size: 11px !important;
        line-height: 1.5;
    }

    #cleanupModal .alert {
        line-height: 1.6;
    }

    /* Confirmation Modal */

    #auditConfirmModal .modal-content {
        max-width: 380px;
        margin-left: auto;
        margin-right: auto;
    }

    #auditConfirmModal .modal-body {
        padding: 5px !important;
    }

    #auditConfirmModal #auditConfirmModalMsg {
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    /* Numeric Fields */

    #auditLogsRoot .bn-digit,
    #auditLogsRoot .font-monospace,
    #cleanupModal .bn-digit,
    #cleanupModal .font-monospace {
        font-weight: 600;
    }

    /* Responsive Layout */

    @media (min-width: 1400px) {
        #auditLogsRoot {
            padding: 22px 24px;
        }

        #auditLogsRoot .filter-card-box {
            padding: 20px;
        }
    }

    @media (max-width: 1199.98px) {
        #auditLogsRoot {
            padding: 16px;
        }

        #auditLogsRoot .filter-card-box {
            padding: 15px;
        }

        #auditLogsRoot .custom-audit-table {
            min-width: 1150px;
        }
    }

    @media (max-width: 991.98px) {
        #auditLogsRoot {
            padding: 14px;
        }

        #auditLogsRoot .audit-page-header {
            align-items: flex-start !important;
        }

        #auditLogsRoot #auditFilterForm .row {
            --bs-gutter-x: 10px;
            --bs-gutter-y: 10px;
        }

        #auditLogsRoot #auditFilterForm .col-md-1 {
            width: 100%;
            justify-content: flex-start !important;
        }

        #auditLogsRoot #auditFilterForm .col-md-1 button[type="submit"] {
            width: auto !important;
            min-width: 100px;
        }

        #auditLogsRoot #auditFilterForm .col-md-1 button[title="Reset Filters"] {
            min-width: 36px;
        }
    }

    @media (max-width: 767.98px) {
        #auditLogsRoot {
            padding: 12px 10px;
        }

        #auditLogsRoot .audit-page-header {
            gap: 12px !important;
        }

        #auditLogsRoot .audit-page-title {
            font-size: 17px;
        }

        #auditLogsRoot .audit-page-subtitle {
            font-size: 11px;
        }

        #auditLogsRoot .audit-header-actions {
            width: 100%;
        }

        #auditLogsRoot .audit-header-actions .btn {
            flex: 1 1 0;
            min-height: 36px;
            padding: 7px 8px;
            font-size: 11.5px;
        }

        #auditLogsRoot .filter-card-box {
            padding: 12px !important;
            margin-bottom: 14px;
        }

        #auditLogsRoot #auditFilterForm .row {
            --bs-gutter-x: 8px;
            --bs-gutter-y: 9px;
        }

        #auditLogsRoot #auditFilterForm .col-6 {
            width: 50%;
        }

        #auditLogsRoot #auditFilterForm .col-12 {
            width: 100%;
        }

        #auditLogsRoot #auditFilterForm .form-control,
        #auditLogsRoot #auditFilterForm .form-select {
            min-height: 37px;
            font-size: 12px;
        }

        #auditLogsRoot #auditFilterForm .col-md-1 {
            display: flex;
            flex-direction: row;
        }

        #auditLogsRoot #auditFilterForm button[type="submit"] {
            flex: 1;
        }

        #auditLogsRoot .audit-card-table {
            margin-bottom: 15px;
        }

        #auditLogsRoot .custom-audit-table {
            min-width: 1180px;
        }

        #auditLogsRoot .custom-audit-table th {
            padding: 10px;
            font-size: 10.5px;
        }

        #auditLogsRoot .custom-audit-table td {
            padding: 9px 10px;
            font-size: 11.5px;
        }

        #auditLogsRoot #auditPaginationWrapper {
            align-items: flex-start !important;
            padding: 11px !important;
        }

        #auditLogsRoot #auditStatsText {
            font-size: 11px;
        }

        #auditLogsRoot #auditPaginationLinks {
            width: 100%;
        }

        #auditLogsRoot #auditPaginationLinks .pagination {
            justify-content: flex-start;
        }

        #logDiffModal .modal-content,
        #cleanupModal .modal-content,
        #auditConfirmModal .modal-content {
            padding: 14px !important;
        }

        #logDiffModal .diff-box {
            max-height: 220px;
            font-size: 11px;
        }
    }

    @media (max-width: 575.98px) {
        #auditLogsRoot {
            padding: 10px 8px;
        }

        #auditLogsRoot .audit-page-title {
            font-size: 16px;
        }

        #auditLogsRoot .audit-header-actions .btn {
            font-size: 11px;
            padding: 6px;
        }

        #auditLogsRoot .filter-card-box {
            padding: 10px !important;
        }

        #auditLogsRoot #auditFilterForm .form-control,
        #auditLogsRoot #auditFilterForm .form-select {
            font-size: 11.5px;
            padding-left: 7px;
            padding-right: 7px;
        }

        #auditLogsRoot .custom-audit-table {
            min-width: 1180px;
        }

        #logDiffModal .modal-dialog,
        #cleanupModal .modal-dialog,
        #auditConfirmModal .modal-dialog {
            width: calc(100% - 16px);
            margin: 8px auto;
        }

        #logDiffModal .modal-content,
        #cleanupModal .modal-content,
        #auditConfirmModal .modal-content {
            padding: 12px !important;
        }

        #logDiffModal .modal-content > .row {
            margin-bottom: 12px !important;
        }

        #logDiffModal .modal-content > .bg-light {
            gap: 8px !important;
        }

        #cleanupModal .cleanup-radio-group {
            flex-direction: column;
            align-items: stretch;
        }

        #cleanupModal .cleanup-radio-group .form-check {
            width: 100%;
        }

        #cleanupModal .modal-content .d-flex.justify-content-end {
            flex-wrap: wrap;
        }
    }

    @media (max-width: 359.98px) {
        #auditLogsRoot .audit-header-actions {
            gap: 5px;
        }

        #auditLogsRoot .audit-header-actions .btn {
            font-size: 10px;
            gap: 4px;
        }

        #auditLogsRoot .audit-page-title {
            font-size: 15px;
        }

        #auditLogsRoot #auditFilterForm .form-control,
        #auditLogsRoot #auditFilterForm .form-select {
            font-size: 11px;
        }
    }

    /* Reduced Motion */

    @media (prefers-reduced-motion: reduce) {
        #auditLogsRoot *,
        #logDiffModal *,
        #cleanupModal *,
        #auditConfirmModal * {
            transition-duration: .01ms !important;
            animation-duration: .01ms !important;
        }
    }
</style>

<div class="container-fluid audit-page-container" id="auditLogsRoot">

    <!-- Page Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 audit-page-header">
        <div>
            <h5 class="audit-page-title d-flex align-items-center">
                <i class="fas fa-clipboard-list text-primary me-2"></i>
                <span>Activity Logs</span>
            </h5>
            <small class="audit-page-subtitle">Admin and employee activities</small>
        </div>

        <div class="audit-header-actions d-flex gap-2">
            @if(auth()->user()->isSuperAdmin())
                <button type="button" class="btn btn-outline-danger btn-sm px-3 fw-semibold d-inline-flex align-items-center" onclick="openCleanupModal()">
                    <i class="fas fa-broom"></i>
                    <span>Clear Logs</span>
                </button>
            @endif

            <button type="button" class="btn btn-light btn-sm px-3 border fw-semibold d-inline-flex align-items-center" onclick="triggerFilterAjax(true)">
                <i class="fas fa-rotate"></i>
                <span>Refresh</span>
            </button>
        </div>
    </div>

    <!-- AJAX Alerts -->
    <div id="ajaxAlertBox" class="alert d-none alert-dismissible fade show mb-3 small py-2 px-3 align-items-center gap-2" role="alert">
        <i id="ajaxAlertIcon" class="fas fa-circle-check fs-6 flex-shrink-0 me-1"></i>
        <div id="ajaxAlertMessage" class="fw-semibold"></div>
        <button type="button" class="btn-close btn-sm shadow-none ms-auto py-2" onclick="document.getElementById('ajaxAlertBox').classList.add('d-none')"></button>
    </div>

    <!-- Filters -->
    <div class="filter-card-box">
        <form id="auditFilterForm" onsubmit="event.preventDefault(); triggerFilterAjax();">
            <div class="row g-2 align-items-center">

                <!-- Search -->
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm overflow-hidden">
                        <span class="input-group-text bg-white border-0">
                            <i class="fas fa-search" style="font-size: 11px;"></i>
                        </span>
                        <input type="text" name="search" id="filter_search" class="form-control border-0 py-1 shadow-none"
                               value="{{ request('search') }}" placeholder="Search user, reference, IP..." autocomplete="off">
                    </div>
                </div>

                <!-- Module -->
                <div class="col-6 col-sm-3 col-md-2">
                    <select name="module" id="filter_module" class="form-select form-select-sm shadow-none">
                        <option value="all">All Modules</option>
                        @foreach($modules as $m)
                            <option value="{{ $m }}" {{ request('module') === $m ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $m)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action -->
                <div class="col-6 col-sm-3 col-md-2">
                    <select name="action_type" id="filter_action" class="form-select form-select-sm shadow-none">
                        <option value="all">All Actions</option>
                        @foreach($actions as $a)
                            <option value="{{ $a }}" {{ request('action_type') === $a ? 'selected' : '' }}>
                                {{ strtoupper($a) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Role -->
                <div class="col-6 col-sm-3 col-md-2">
                    <select name="role" id="filter_role" class="form-select form-select-sm shadow-none">
                        <option value="all">All Roles</option>
                        @foreach($roles as $r)
                            <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>
                                {{ ucwords(str_replace(['_', '-'], ' ', $r)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date -->
                <div class="col-6 col-sm-3 col-md-2">
                    <input type="date" name="date" id="filter_date" class="form-control form-control-sm shadow-none bn-digit" value="{{ request('date') }}">
                </div>

                <!-- Filter Actions -->
                <div class="col-12 col-md-1 d-flex gap-1 justify-content-end">
                    <button type="submit" class="btn btn-primary btn-sm px-2 w-100 fw-semibold">
                        Filter
                    </button>
                    <button type="button" class="btn btn-light btn-sm border px-2 text-muted" onclick="resetFilterForm()" title="Reset Filters">
                        <i class="fas fa-rotate-left"></i>
                    </button>
                </div>

            </div>
        </form>
    </div>

    <!-- Activity Table -->
    <div class="audit-card-table">

        <!-- Loading Overlay -->
        <div id="auditLoadingOverlay">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table custom-audit-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="min-width: 170px;">Staff / User</th>
                        <th style="width: 110px;">Action</th>
                        <th style="width: 130px;">Module</th>
                        <th style="min-width: 130px;">Record Ref</th>
                        <th style="min-width: 250px;">Description</th>
                        <th style="min-width: 140px;">Device & IP</th>
                        <th style="width: 140px;">Date & Time</th>
                        <th class="text-end" style="width: 110px;">Details</th>
                    </tr>
                </thead>

                <tbody id="auditTableBody">
                    @include('admin.activity_logs.partials.table_rows', ['logs' => $logs])
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-2 p-md-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-light bg-opacity-50" id="auditPaginationWrapper">

            <small class="text-muted bn-digit" id="auditStatsText">
                Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{$logs->total() }} entries
            </small>

            <div id="auditPaginationLinks">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
</div>

<!-- Modal 1: Activity Details -->
<div class="modal fade" id="logDiffModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">

            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center">
                    <i class="fas fa-code-compare text-primary me-2"></i>
                    <span>Activity Comparison</span>
                </h6>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <!-- Record Information -->
            <div class="p-2 bg-light rounded-2 border mb-3 small d-flex flex-wrap gap-3 justify-content-between">
                <div>
                    <span class="text-muted">Staff:</span>
                    <strong id="modal_diff_user" class="text-dark ms-1"></strong>
                </div>

                <div>
                    <span class="text-muted">Action:</span>
                    <strong id="modal_diff_action" class="text-primary ms-1"></strong>
                </div>

                <div>
                    <span class="text-muted">Module:</span>
                    <strong id="modal_diff_module" class="text-secondary ms-1"></strong>
                </div>

                <div>
                    <span class="text-muted">Reference:</span>
                    <strong id="modal_diff_ref" class="text-danger font-monospace bn-digit ms-1"></strong>
                </div>
            </div>

            <!-- Old and New Values -->
            <div class="row g-2 mb-3">

                <div class="col-12 col-md-6">
                    <span class="small fw-bold text-danger d-flex align-items-center mb-2">
                        <i class="fas fa-clock-rotate-left me-2"></i>
                        <span>Old Values</span>
                    </span>
                    <pre class="diff-box" id="modal_diff_old">None</pre>
                </div>

                <div class="col-12 col-md-6">
                    <span class="small fw-bold text-success d-flex align-items-center mb-2">
                        <i class="fas fa-check-double me-2"></i>
                        <span>New Values</span>
                    </span>
                    <pre class="diff-box" id="modal_diff_new">None</pre>
                </div>

            </div>

            <!-- Modal Actions -->
            <div class="d-flex justify-content-between align-items-center pt-3 border-top">

                @if(auth()->user()->isSuperAdmin())
                    <button type="button" class="btn btn-sm btn-outline-danger px-3 fw-semibold d-inline-flex align-items-center" id="btnModalDeleteLog" onclick="deleteCurrentModalLog()">
                        <i class="fas fa-trash-can me-2"></i>
                        <span>Delete Record</span>
                    </button>
                @else
                    <div></div>
                @endif

                <button type="button" class="btn btn-sm btn-primary px-4 fw-semibold" data-bs-dismiss="modal">
                    Close
                </button>

            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Log Cleanup -->
<div class="modal fade" id="cleanupModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg">

            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                <h6 class="fw-bold text-danger mb-0 d-flex align-items-center">
                    <i class="fas fa-broom me-2"></i>
                    <span>Log Cleanup</span>
                </h6>

                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <form id="logCleanupForm" onsubmit="event.preventDefault(); executeLogCleanup();">

                <!-- Cleanup Mode -->
                <label class="form-label small fw-bold text-dark mb-2">
                    Cleanup Mode
                </label>

                <div class="d-flex flex-wrap gap-1 cleanup-radio-group mb-3">

                    <div class="form-check form-check-inline mb-1">
                        <input class="form-check-input" type="radio" name="cleanup_type" id="type_days" value="days" checked onchange="switchCleanupMode('days')">
                        <label class="form-check-label small fw-semibold" for="type_days">
                            Retention Days
                        </label>
                    </div>

                    <div class="form-check form-check-inline mb-1">
                        <input class="form-check-input" type="radio" name="cleanup_type" id="type_single_id" value="single_id" onchange="switchCleanupMode('single_id')">
                        <label class="form-check-label small fw-semibold text-primary" for="type_single_id">
                            Log ID
                        </label>
                    </div>

                    <div class="form-check form-check-inline mb-1">
                        <input class="form-check-input" type="radio" name="cleanup_type" id="type_date" value="single_date" onchange="switchCleanupMode('single_date')">
                        <label class="form-check-label small fw-semibold" for="type_date">
                            Single Date
                        </label>
                    </div>

                    <div class="form-check form-check-inline mb-1">
                        <input class="form-check-input" type="radio" name="cleanup_type" id="type_range" value="date_range" onchange="switchCleanupMode('date_range')">
                        <label class="form-check-label small fw-semibold" for="type_range">
                            Date Range
                        </label>
                    </div>

                    <div class="form-check form-check-inline mb-1">
                        <input class="form-check-input" type="radio" name="cleanup_type" id="type_all" value="all" onchange="switchCleanupMode('all')">
                        <label class="form-check-label small fw-semibold text-danger" for="type_all">
                            Delete All
                        </label>
                    </div>

                </div>

                <!-- Panel 1: Retention Days -->
                <div id="panel_days" class="cleanup-mode-panel mb-3">

                    <label class="form-label small fw-bold text-muted mb-1">
                        Delete Logs Older Than
                    </label>

                    <select name="days" id="cleanupDaysSelect" class="form-select form-select-sm fw-semibold">
                        <option value="7">7 Days</option>
                        <option value="15">15 Days</option>
                        <option value="30">30 Days</option>
                        <option value="60">60 Days</option>
                        <option value="90" selected>90 Days</option>
                        <option value="180">180 Days</option>
                        <option value="365">1 Year</option>
                    </select>

                </div>

                <!-- Panel 2: Log ID -->
                <div id="panel_single_id" class="cleanup-mode-panel mb-3 d-none">

                    <label class="form-label small fw-bold text-muted mb-1">
                        Log Record ID
                    </label>

                    <input type="number" name="log_id" id="cleanupLogIdInput" class="form-control form-control-sm font-monospace bn-digit" placeholder="Enter log ID">

                    <small class="text-muted d-block mt-1">
                        This record will be permanently deleted.
                    </small>

                </div>

                <!-- Panel 3: Single Date -->
                <div id="panel_single_date" class="cleanup-mode-panel mb-3 d-none">

                    <label class="form-label small fw-bold text-muted mb-1">
                        Select Date
                    </label>

                    <input type="date" name="specific_date" id="cleanupSpecificDate" class="form-control form-control-sm bn-digit" value="{{ date('Y-m-d') }}">

                    <small class="text-muted d-block mt-1">
                        All logs from this date will be deleted.
                    </small>

                </div>

                <!-- Panel 4: Date Range -->
                <div id="panel_date_range" class="cleanup-mode-panel mb-3 d-none">

                    <label class="form-label small fw-bold text-muted mb-1">
                        Date Range
                    </label>

                    <div class="row g-2">
                        <div class="col-6">
                            <input type="date" name="start_date" id="cleanupStartDate" class="form-control form-control-sm bn-digit" placeholder="From Date">
                        </div>

                        <div class="col-6">
                            <input type="date" name="end_date" id="cleanupEndDate" class="form-control form-control-sm bn-digit" placeholder="To Date">
                        </div>
                    </div>

                    <small class="text-muted d-block mt-1">
                        All logs within this range will be deleted.
                    </small>

                </div>

                <!-- Panel 5: Delete All Warning -->
                <div id="panel_all" class="cleanup-mode-panel mb-3 d-none">

                    <div class="alert alert-danger py-2 px-3 mb-0 small border-0 bg-danger-subtle text-danger">
                        <i class="fas fa-triangle-exclamation me-2"></i>
                        <strong>Warning:</strong> All activity and audit logs will be permanently deleted. This cannot be undone.
                    </div>

                </div>

                <!-- Cleanup Actions -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">

                    <button type="button" class="btn btn-light btn-sm px-3 border text-muted" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-danger btn-sm px-3 fw-bold d-inline-flex align-items-center" id="btnExecuteCleanup">
                        <i class="fas fa-trash-can me-2"></i>
                        <span>Execute Cleanup</span>
                    </button>

                </div>

            </form>
        </div>
    </div>
</div>

<!-- Modal 3: Confirmation -->
<div class="modal fade" id="auditConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center">

            <div class="modal-body p-1">

                <div class="mb-2 text-danger">
                    <i class="fas fa-triangle-exclamation fa-2x"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1" id="auditConfirmModalTitle">
                    Confirm Action
                </h6>

                <p class="text-muted small mb-3" id="auditConfirmModalMsg">
                    Are you sure you want to proceed?
                </p>

                <div class="d-flex justify-content-center gap-2">

                    <button type="button" class="btn btn-light btn-sm px-3 border" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="button" class="btn btn-danger btn-sm px-3 fw-bold" id="btnAuditConfirmProceed">
                        Proceed
                    </button>

                </div>

            </div>
        </div>
    </div>
</div>

<script>
    let filterDebounceTimer = null;
    let currentAjaxUrl = "{{ route('admin.activity.logs') }}";
    let activeModalLogId = null;
    let pendingAuditConfirmCallback = null;

    function showAuditConfirmModal(title, message, onConfirm) {
        document.getElementById('auditConfirmModalTitle').innerText = title;
        document.getElementById('auditConfirmModalMsg').innerText = message;
        pendingAuditConfirmCallback = onConfirm;

        const modalEl = document.getElementById('auditConfirmModal');
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    document.getElementById('btnAuditConfirmProceed').addEventListener('click', function () {
        const modalEl = document.getElementById('auditConfirmModal');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();

        if (typeof pendingAuditConfirmCallback === 'function') {
            pendingAuditConfirmCallback();
            pendingAuditConfirmCallback = null;
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        // 1. Real-time debounced AJAX search filter (350ms)
        const searchInput = document.getElementById('filter_search');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(filterDebounceTimer);
                filterDebounceTimer = setTimeout(() => {
                    triggerFilterAjax();
                }, 350);
            });
        }

        // 2. Instant AJAX trigger on dropdown and date changes
        ['filter_module', 'filter_action', 'filter_role', 'filter_date'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('change', () => triggerFilterAjax());
            }
        });

        // 3. AJAX pagination click interceptor
        document.addEventListener('click', function (e) {
            const pageLink = e.target.closest('#auditPaginationLinks a');
            if (pageLink) {
                e.preventDefault();
                const url = pageLink.getAttribute('href');
                if (url && url !== '#') {
                    fetchLogsByUrl(url);
                }
            }
        });
    });

    // Execute AJAX filter query
    function triggerFilterAjax(resetPage = true) {
        const form = document.getElementById('auditFilterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);

        const baseUrl = "{{ route('admin.activity.logs') }}";
        const targetUrl = baseUrl + '?' + params.toString();

        fetchLogsByUrl(targetUrl);
    }

    // Fetch logs from specific URL via AJAX
    function fetchLogsByUrl(url) {
        currentAjaxUrl = url;
        const overlay = document.getElementById('auditLoadingOverlay');
        if (overlay) overlay.style.display = 'flex';

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('auditTableBody').innerHTML = data.html;
                document.getElementById('auditPaginationLinks').innerHTML = data.pagination;
                document.getElementById('auditStatsText').innerText =
                    `Showing ${data.firstItem} to ${data.lastItem} of ${data.total} audit entries`;

                // Update browser history state without page reload
                window.history.replaceState({}, '', url);
            }
        })
        .catch(err => {
            console.error('AJAX Log Fetch Error:', err);
        })
        .finally(() => {
            if (overlay) overlay.style.display = 'none';
        });
    }

    // Reset filter form controls
    function resetFilterForm() {
        document.getElementById('filter_search').value = '';
        document.getElementById('filter_module').value = 'all';
        document.getElementById('filter_action').value = 'all';
        document.getElementById('filter_role').value = 'all';
        document.getElementById('filter_date').value = '';
        triggerFilterAjax();
    }

    // Open Diff Snapshot Modal
    function viewLogDetail(logId) {
        activeModalLogId = logId;
        fetch("{{ url('admin/activity-logs/show') }}/" + logId, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                const d = res.data;
                document.getElementById('modal_diff_user').innerText = `${d.user_name} (${d.role})`;
                document.getElementById('modal_diff_action').innerText = d.action;
                document.getElementById('modal_diff_module').innerText = d.module;
                document.getElementById('modal_diff_ref').innerText = d.record_ref;

                document.getElementById('modal_diff_old').innerText = d.old_values ? JSON.stringify(d.old_values, null, 2) : 'No prior state recorded.';
                document.getElementById('modal_diff_new').innerText = d.new_values ? JSON.stringify(d.new_values, null, 2) : 'No new state recorded.';

                const modal = new bootstrap.Modal(document.getElementById('logDiffModal'));
                modal.show();
            } else {
                showAlertNotification(res.message || 'Failed to load details.', 'danger');
            }
        })
        .catch(err => showAlertNotification('Communication error with server.', 'danger'));
    }

    // Delete specific log from inside the Diff Modal
    function deleteCurrentModalLog() {
        if (!activeModalLogId) return;
        const modalEl = document.getElementById('logDiffModal');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();

        deleteSingleLog(activeModalLogId);
    }

    // Delete specific single log entry
    function deleteSingleLog(logId) {
        showAuditConfirmModal(
            'Delete Audit Log?',
            `Are you sure you want to permanently delete audit log record #${logId}?`,
            () => {
                const overlay = document.getElementById('auditLoadingOverlay');
                if (overlay) overlay.style.display = 'flex';

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

                fetch("{{ url('admin/activity-logs/destroy') }}/" + logId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showAlertNotification(data.message, 'success');
                        const rowEl = document.getElementById('log-row-' + logId);
                        if (rowEl) {
                            rowEl.style.transition = 'all 0.3s ease';
                            rowEl.style.opacity = '0';
                            setTimeout(() => rowEl.remove(), 300);
                        }
                        fetchLogsByUrl(currentAjaxUrl);
                    } else {
                        showAlertNotification(data.message || 'Failed to delete log entry.', 'danger');
                    }
                })
                .catch(err => {
                    console.error('Delete Log Error:', err);
                    showAlertNotification('Server communication error during log deletion.', 'danger');
                })
                .finally(() => {
                    if (overlay) overlay.style.display = 'none';
                });
            }
        );
    }

    // Open Cleanup Modal
    function openCleanupModal() {
        const modal = new bootstrap.Modal(document.getElementById('cleanupModal'));
        modal.show();
    }

    // Switch Cleanup Mode Panels
    function switchCleanupMode(mode) {
        document.querySelectorAll('.cleanup-mode-panel').forEach(p => p.classList.add('d-none'));

        if (mode === 'single_id') {
            document.getElementById('panel_single_id').classList.remove('d-none');
        } else if (mode === 'single_date') {
            document.getElementById('panel_single_date').classList.remove('d-none');
        } else if (mode === 'date_range') {
            document.getElementById('panel_date_range').classList.remove('d-none');
        } else if (mode === 'all') {
            document.getElementById('panel_all').classList.remove('d-none');
        } else {
            document.getElementById('panel_days').classList.remove('d-none');
        }
    }

    // Execute Log Cleanup via AJAX
    function executeLogCleanup() {
        const form = document.getElementById('logCleanupForm');
        const formData = new FormData(form);
        const cleanupType = formData.get('cleanup_type');

        const confirmTitle = cleanupType === 'all' ? 'Purge All Audit Logs?' : 'Confirm Log Cleanup';
        const confirmMsg = cleanupType === 'all'
            ? 'Are you absolutely sure you want to permanently delete ALL audit activity logs? This action cannot be reversed!'
            : 'Confirm purging selected activity log records?';

        showAuditConfirmModal(confirmTitle, confirmMsg, () => {
            const btn = document.getElementById('btnExecuteCleanup');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Purging...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            fetch("{{ route('admin.activity.cleanup') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(Object.fromEntries(formData))
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const modalEl = document.getElementById('cleanupModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();

                    showAlertNotification(data.message, 'success');
                    fetchLogsByUrl(currentAjaxUrl);
                } else {
                    showAlertNotification(data.message || 'Cleanup operation failed.', 'danger');
                }
            })
            .catch(err => {
                console.error(err);
                showAlertNotification('Failed to execute log cleanup.', 'danger');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        });
    }

    // Display dynamic alert notification
    function showAlertNotification(msg, type = 'success') {
        const box = document.getElementById('ajaxAlertBox');
        const msgEl = document.getElementById('ajaxAlertMessage');
        const icon = document.getElementById('ajaxAlertIcon');

        if (box && msgEl) {
            box.className = `alert alert-${type} alert-dismissible fade show mb-3 small py-2 px-3 border-0 shadow-xs d-flex align-items-center gap-2`;
            icon.className = type === 'success' ? 'fas fa-circle-check fs-6 text-success flex-shrink-0 me-1' : 'fas fa-circle-exclamation fs-6 text-danger flex-shrink-0 me-1';
            msgEl.innerText = msg;
            box.classList.remove('d-none');

            setTimeout(() => {
                box.classList.add('d-none');
            }, 5000);
        }
    }
</script>
@endsection
