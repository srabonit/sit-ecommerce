
@extends('layouts.admin')

@section('title', 'Staff Payroll & Monthly Salary - ' . config('app.name', 'Shop Now BD'))

@php
    $monthName = \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y');
@endphp

@section('admin_content')

<style>
    /* =========================================
       KAIADMIN CLASSIC — PAYROLL DESIGN SYSTEM
    ========================================= */

    #payrollRoot,
    .payroll-modal-scope {
        --pr-primary: var(--bs-primary, #1572e8);
        --pr-primary-rgb: var(--bs-primary-rgb, 21, 114, 232);
        --pr-text: var(--bs-body-color, #212529);
        --pr-muted: var(--bs-secondary-color, #6c757d);
        --pr-border: var(--bs-border-color, #dee2e6);
        --pr-surface: var(--bs-body-bg, #ffffff);
        --pr-soft: var(--bs-tertiary-bg, #f8f9fa);
        --pr-success: var(--bs-success, #31ce36);
        --pr-danger: var(--bs-danger, #f25961);
        --pr-warning: var(--bs-warning, #ffad46);
        --pr-info: var(--bs-info, #48abf7);
        --pr-radius: 5px;
        --pr-shadow: 0 1px 3px rgba(0, 0, 0, .045);

        color: var(--pr-text);
        font-family: inherit;
        letter-spacing: normal;
        min-width: 0;
    }

    #payrollRoot *,
    .payroll-modal-scope * {
        box-sizing: border-box;
    }

    #payrollRoot .card,
    #payrollRoot .btn,
    #payrollRoot .form-control,
    #payrollRoot .form-select,
    #payrollRoot .badge,
    #payrollRoot .alert,
    #payrollRoot .metric-soft-card,
    .payroll-modal-scope .modal-content,
    .payroll-modal-scope .btn,
    .payroll-modal-scope .form-control,
    .payroll-modal-scope .form-select {
        border-radius: var(--pr-radius) !important;
    }

    #payrollRoot .font-monospace,
    .payroll-modal-scope .font-monospace {
        font-variant-numeric: tabular-nums;
    }

    #payrollRoot .min-w-0 {
        min-width: 0;
    }

    /* =========================================
       PAGE HEADER
    ========================================= */

    #payrollRoot .payroll-header-card {
        padding: 17px 19px;
        background: var(--pr-surface);
        border: 1px solid var(--pr-border);
        border-radius: var(--pr-radius);
        box-shadow: var(--pr-shadow);
        margin-bottom: 16px;
    }

    #payrollRoot .page-heading {
        font-size: clamp(16px, 1.4vw, 19px);
        font-weight: 700;
        line-height: 1.4;
        color: var(--pr-text);
        letter-spacing: -.3px;
    }

    #payrollRoot .page-description {
        font-size: 12px;
        color: var(--pr-muted);
        line-height: 1.6;
    }

    #payrollRoot .header-actions-wrap {
        gap: 8px !important;
    }

    /* =========================================
       BUTTONS
    ========================================= */

    #payrollRoot .btn-compact {
        min-height: 35px;
        padding: 7px 12px;
        font-size: 11.5px;
        line-height: 1.4;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #payrollRoot .btn-compact:hover {
        transform: translateY(-1px);
    }

    #payrollRoot .btn-primary {
        background: var(--pr-primary);
        border-color: var(--pr-primary);
        color: #fff;
    }

    #payrollRoot .btn-primary:hover {
        filter: brightness(.94);
        color: #fff;
    }

    #payrollRoot .btn-outline-secondary {
        background: var(--pr-surface);
        border-color: var(--pr-border);
        color: var(--pr-text);
    }

    #payrollRoot .btn-outline-secondary:hover {
        background: var(--pr-soft);
        color: var(--pr-text);
        border-color: var(--pr-muted);
    }

    #payrollRoot .btn-success {
        color: #fff;
    }

    #payrollRoot .btn-light {
        background: var(--pr-surface);
        color: var(--pr-text);
        border-color: var(--pr-border);
    }

    #payrollRoot .btn-light:hover {
        background: var(--pr-soft);
        border-color: var(--pr-border);
    }

    /* =========================================
       PAYROLL SUMMARY CARDS
    ========================================= */

    #payrollRoot .metric-soft-card {
        position: relative;
        padding: 15px;
        border: 1px solid var(--pr-border);
        background: var(--pr-surface);
        box-shadow: var(--pr-shadow);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 10px;
        height: 100%;
        min-height: 112px;
        transition: border-color .18s ease, box-shadow .18s ease;
        overflow: hidden;
    }

    #payrollRoot .metric-soft-card:hover {
        border-color: rgba(var(--pr-primary-rgb), .3);
        box-shadow: 0 3px 12px rgba(0, 0, 0, .06);
    }

    #payrollRoot .metric-card-blue {
        border-left: 3px solid var(--pr-primary);
    }

    #payrollRoot .metric-card-amber {
        border-left: 3px solid var(--pr-warning);
    }

    #payrollRoot .metric-card-green {
        border-left: 3px solid var(--pr-success);
    }

    #payrollRoot .metric-card-red {
        border-left: 3px solid var(--pr-danger);
    }

    #payrollRoot .stat-label-title {
        font-size: 11px;
        font-weight: 650;
        letter-spacing: .025em;
        line-height: 1.4;
        color: var(--pr-muted);
        text-transform: uppercase;
    }

    #payrollRoot .stat-val-num {
        font-size: clamp(16px, 1.5vw, 20px);
        font-weight: 700;
        line-height: 1.35;
        letter-spacing: -.3px;
        color: var(--pr-text) !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-variant-numeric: tabular-nums;
    }

    #payrollRoot .metric-icon {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: var(--pr-radius);
        background: rgba(var(--pr-primary-rgb), .09);
        color: var(--pr-primary);
        font-size: 14px;
    }

    #payrollRoot .metric-icon.green {
        background: rgba(49, 206, 54, .10);
        color: var(--pr-success);
    }

    #payrollRoot .metric-icon.amber {
        background: rgba(255, 173, 70, .13);
        color: #d98b20;
    }

    #payrollRoot .metric-icon.red {
        background: rgba(242, 89, 97, .10);
        color: var(--pr-danger);
    }

    #payrollRoot .metric-subtext {
        font-size: 11px;
        color: var(--pr-muted);
        line-height: 1.45;
    }

    /* =========================================
       ALERTS
    ========================================= */

    #payrollRoot .alert {
        font-size: 12px;
        padding: 11px 14px;
        border: 1px solid transparent !important;
        border-radius: var(--pr-radius) !important;
    }

    #payrollRoot .alert-success {
        background: rgba(49, 206, 54, .08);
        color: #198754;
        border-color: rgba(49, 206, 54, .2) !important;
    }

    #payrollRoot .alert-danger {
        background: rgba(242, 89, 97, .08);
        color: #dc3545;
        border-color: rgba(242, 89, 97, .2) !important;
    }

    /* =========================================
       GENERAL CARDS
    ========================================= */

    #payrollRoot .payroll-surface-card {
        background: var(--pr-surface);
        border: 1px solid var(--pr-border) !important;
        border-radius: var(--pr-radius) !important;
        box-shadow: var(--pr-shadow) !important;
        overflow: hidden;
    }

    /* =========================================
       FILTER TOOLBAR
    ========================================= */

    #payrollRoot .filter-toolbar {
        padding: 15px;
        background: var(--pr-surface);
        border: 1px solid var(--pr-border);
        border-radius: var(--pr-radius);
        box-shadow: var(--pr-shadow);
        margin-bottom: 15px;
    }

    #payrollRoot .form-label {
        display: block;
        font-size: 12px;
        font-weight: 650;
        color: var(--pr-text);
        margin-bottom: 6px;
    }

    #payrollRoot .form-control,
    #payrollRoot .form-select {
        min-height: 38px;
        padding: 8px 11px;
        font-size: 12px;
        font-weight: 500;
        color: var(--pr-text);
        background-color: var(--pr-surface);
        border: 1px solid var(--pr-border);
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #payrollRoot .form-control:focus,
    #payrollRoot .form-select:focus {
        border-color: var(--pr-primary);
        box-shadow: 0 0 0 3px rgba(var(--pr-primary-rgb), .12);
        outline: none;
    }

    #payrollRoot .filter-actions {
        gap: 8px !important;
    }

    /* =========================================
       TABLE HEADER
    ========================================= */

    #payrollRoot .payroll-table-header {
        min-height: 49px;
        padding: 12px 16px;
        background: var(--pr-surface);
        border-bottom: 1px solid var(--pr-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    #payrollRoot .table-heading {
        font-size: 13px;
        font-weight: 700;
        color: var(--pr-text);
        line-height: 1.5;
    }

    #payrollRoot .table-count-badge {
        font-size: 11px;
        font-weight: 650;
        padding: 6px 9px;
        white-space: nowrap;
        border-radius: var(--pr-radius) !important;
    }

    /* =========================================
       PAYROLL TABLE
    ========================================= */

    #payrollRoot .payroll-table-wrap {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: var(--pr-border) transparent;
    }

    #payrollRoot .payroll-table-wrap::-webkit-scrollbar {
        height: 6px;
    }

    #payrollRoot .payroll-table-wrap::-webkit-scrollbar-thumb {
        background: var(--pr-border);
        border-radius: 10px;
    }

    #payrollRoot .custom-payroll-table {
        width: 100%;
        min-width: 1120px;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    #payrollRoot .custom-payroll-table th {
        background: var(--pr-soft);
        color: var(--pr-muted);
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .025em;
        border-bottom: 1px solid var(--pr-border);
        padding: 12px 10px;
        white-space: nowrap;
        vertical-align: middle;
    }

    #payrollRoot .custom-payroll-table td {
        padding: 11px 10px;
        vertical-align: middle;
        font-size: 12px;
        color: var(--pr-text);
        border-bottom: 1px solid var(--pr-border);
        white-space: nowrap;
        background: var(--pr-surface);
    }

    #payrollRoot .custom-payroll-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #payrollRoot .custom-payroll-table tbody tr {
        transition: background-color .12s ease;
    }

    #payrollRoot .custom-payroll-table tbody tr:hover td {
        background: var(--pr-soft);
    }

    #payrollRoot .staff-avatar {
        width: 36px;
        height: 36px;
        flex-shrink: 0;
        border-radius: var(--pr-radius);
        background: rgba(var(--pr-primary-rgb), .09);
        color: var(--pr-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 750;
    }

    #payrollRoot .staff-name {
        font-size: 12px;
        font-weight: 700;
        color: var(--pr-text);
        line-height: 1.45;
    }

    #payrollRoot .staff-meta {
        font-size: 10.5px;
        color: var(--pr-muted);
        line-height: 1.5;
    }

    #payrollRoot .table-value {
        font-size: 12px;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }

    #payrollRoot .table-net-value {
        font-size: 12.5px;
        font-weight: 750;
        color: var(--pr-text);
        font-variant-numeric: tabular-nums;
    }

    #payrollRoot .table-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 5px 8px;
        font-size: 10.5px;
        font-weight: 650;
        border-radius: var(--pr-radius);
        line-height: 1.3;
    }

    #payrollRoot .table-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 5px;
    }

    #payrollRoot .table-actions .btn {
        min-height: 31px;
        min-width: 31px;
        padding: 5px 9px;
        font-size: 11px;
    }

    #payrollRoot .empty-state {
        padding: 48px 16px !important;
        text-align: center;
        white-space: normal !important;
    }

    #payrollRoot .empty-state-icon {
        width: 58px;
        height: 58px;
        margin: 0 auto 14px;
        border-radius: var(--pr-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(var(--pr-primary-rgb), .08);
        color: var(--pr-primary);
        font-size: 23px;
    }

    /* =========================================
       STATUS BADGES
    ========================================= */

    #payrollRoot .bg-success-subtle {
        background-color: rgba(49, 206, 54, .10) !important;
    }

    #payrollRoot .bg-warning-subtle {
        background-color: rgba(255, 173, 70, .14) !important;
    }

    #payrollRoot .text-warning-emphasis {
        color: #a56b13 !important;
    }

    /* =========================================
       MODALS
    ========================================= */

    .payroll-modal-scope .modal-dialog {
        max-width: 440px;
        width: calc(100% - 24px);
    }

    .payroll-modal-scope .modal-content {
        padding: 0 !important;
        background: var(--pr-surface);
        border: 1px solid var(--pr-border);
        box-shadow: 0 12px 40px rgba(0, 0, 0, .14);
        overflow: hidden;
    }

    .payroll-modal-scope .modal-header,
    .payroll-modal-scope .modal-footer {
        padding: 13px 16px;
        background: var(--pr-surface);
        border-color: var(--pr-border);
    }

    .payroll-modal-scope .modal-header {
        min-height: 52px;
    }

    .payroll-modal-scope .modal-title-text {
        font-size: 14px;
        font-weight: 700;
        color: var(--pr-text);
    }

    .payroll-modal-scope .modal-body {
        padding: 17px;
    }

    .payroll-modal-scope .modal-footer {
        gap: 8px;
    }

    .payroll-modal-scope .form-label {
        font-size: 12px;
        font-weight: 650;
        color: var(--pr-text);
        margin-bottom: 6px;
    }

    .payroll-modal-scope .form-control,
    .payroll-modal-scope .form-select {
        min-height: 39px;
        padding: 8px 11px;
        font-size: 12px;
        border: 1px solid var(--pr-border);
        background: var(--pr-surface);
        color: var(--pr-text);
        box-shadow: none;
    }

    .payroll-modal-scope textarea.form-control {
        min-height: auto;
        resize: vertical;
    }

    .payroll-modal-scope .form-control:focus,
    .payroll-modal-scope .form-select:focus {
        border-color: var(--pr-primary);
        box-shadow: 0 0 0 3px rgba(var(--pr-primary-rgb), .12);
    }

    .payroll-modal-scope .modal-summary {
        padding: 15px;
        border: 1px solid var(--pr-border);
        background: var(--pr-soft);
        border-radius: var(--pr-radius);
        text-align: center;
        margin-bottom: 17px;
    }

    .payroll-modal-scope .modal-staff-name {
        font-size: 12px;
        font-weight: 600;
        color: var(--pr-muted);
    }

    .payroll-modal-scope .modal-amount {
        font-size: clamp(22px, 5vw, 27px);
        font-weight: 750;
        line-height: 1.4;
        letter-spacing: -.5px;
        font-variant-numeric: tabular-nums;
    }

    .payroll-modal-scope .modal-voucher {
        font-size: 11px;
        color: var(--pr-muted);
    }

    .payroll-modal-scope .form-help {
        display: block;
        margin-top: 6px;
        font-size: 11px;
        line-height: 1.5;
        color: var(--pr-muted);
    }

    .payroll-modal-scope .btn-compact {
        min-height: 36px;
        padding: 7px 13px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    /* =========================================
       RESPONSIVE — TABLET
    ========================================= */

    @media (max-width: 991.98px) {
        #payrollRoot .payroll-header-card {
            padding: 15px;
        }

        #payrollRoot .metric-soft-card {
            min-height: 105px;
        }

        #payrollRoot .custom-payroll-table th,
        #payrollRoot .custom-payroll-table td {
            padding: 10px 8px;
        }
    }

    /* =========================================
       RESPONSIVE — MOBILE
    ========================================= */

    @media (max-width: 767.98px) {

        #payrollRoot {
            padding: 12px !important;
        }

        #payrollRoot .payroll-header-card {
            padding: 14px;
        }

        #payrollRoot .payroll-header {
            align-items: stretch !important;
        }

        #payrollRoot .page-heading {
            font-size: 16px;
        }

        #payrollRoot .page-description {
            font-size: 11px;
        }

        #payrollRoot .header-actions-wrap {
            width: 100%;
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px !important;
        }

        #payrollRoot .header-actions-wrap .btn {
            width: 100%;
            min-height: 38px;
            padding: 7px 8px !important;
            font-size: 11px !important;
            white-space: normal;
            text-align: center;
        }

        #payrollRoot .metric-soft-card {
            min-height: 100px;
            padding: 11px;
        }

        #payrollRoot .stat-label-title {
            font-size: 10px;
        }

        #payrollRoot .stat-val-num {
            font-size: 15px;
        }

        #payrollRoot .metric-subtext {
            font-size: 10px;
        }

        #payrollRoot .filter-toolbar {
            padding: 12px;
        }

        #payrollRoot .filter-actions {
            display: grid !important;
            grid-template-columns: 1fr;
            gap: 8px !important;
        }

        #payrollRoot .filter-actions .btn {
            width: 100%;
            min-height: 38px;
        }

        #payrollRoot .payroll-table-header {
            align-items: flex-start;
            flex-wrap: wrap;
            padding: 12px;
        }

        #payrollRoot .table-heading {
            font-size: 12px;
        }

        #payrollRoot .custom-payroll-table {
            min-width: 1120px;
        }

        #payrollRoot .custom-payroll-table th {
            font-size: 10px;
        }

        #payrollRoot .custom-payroll-table td {
            font-size: 11.5px;
        }

        .payroll-modal-scope .modal-dialog {
            margin: 12px auto;
        }

        .payroll-modal-scope .modal-body {
            padding: 14px;
        }
    }

    /* =========================================
       RESPONSIVE — SMALL MOBILE
    ========================================= */

    @media (max-width: 380px) {

        #payrollRoot .header-actions-wrap {
            grid-template-columns: 1fr;
        }

        #payrollRoot .stat-val-num {
            font-size: 13px;
        }

        #payrollRoot .metric-icon {
            width: 28px;
            height: 28px;
            font-size: 12px;
        }

        #payrollRoot .metric-soft-card {
            padding: 9px;
        }

        #payrollRoot .filter-toolbar {
            padding: 10px;
        }
    }

    /* =========================================
       ACCESSIBILITY & OVERFLOW
    ========================================= */

    #payrollRoot button:focus-visible,
    #payrollRoot a:focus-visible,
    .payroll-modal-scope button:focus-visible {
        outline: 2px solid var(--pr-primary);
        outline-offset: 2px;
    }

    @media (prefers-reduced-motion: reduce) {
        #payrollRoot *,
        .payroll-modal-scope * {
            transition-duration: .01ms !important;
            animation-duration: .01ms !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="payrollRoot">

    {{-- =========================================
         PAGE HEADER
    ========================================== --}}
    <div class="payroll-header-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 payroll-header">

        <div class="min-w-0">
            <h5 class="page-heading mb-1 d-flex align-items-center gap-2">
                <i class="fas fa-money-check-dollar text-primary"></i>
                <span>Staff Payroll &amp; Monthly Salary</span>
            </h5>

            <small class="page-description d-block">
                Monthly salary calculation and bank disbursement with a 30-day closing cycle.
            </small>
        </div>

        <div class="header-actions-wrap d-flex flex-wrap align-items-center gap-2 flex-shrink-0">

            <button type="button"
                    class="btn btn-primary btn-compact shadow-sm fw-bold"
                    onclick="generateSalarySheet()">
                <i class="fas fa-rotate"></i>
                <span>Generate Salary (30th)</span>
            </button>

            <a href="{{ route('admin.roles.users') }}"
               class="btn btn-outline-secondary btn-compact shadow-sm">
                <i class="fas fa-user-plus"></i>
                <span>Set Staff Salary</span>
            </a>

            <a href="{{ route('admin.report.profit_loss') }}"
               class="btn btn-outline-success btn-compact shadow-sm">
                <i class="fas fa-chart-line"></i>
                <span>View P&amp;L</span>
            </a>

        </div>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3 d-flex align-items-center justify-content-between gap-2"
             role="alert">

            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <span class="fw-semibold">{{ session('success') }}</span>
            </div>

            <button type="button"
                    class="btn-close shadow-none"
                    data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    {{-- Error Alert --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3 d-flex align-items-center justify-content-between gap-2"
             role="alert">

            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-circle-exclamation"></i>
                <span class="fw-semibold">{{ session('error') }}</span>
            </div>

            <button type="button"
                    class="btn-close shadow-none"
                    data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    {{-- =========================================
         PAYROLL SUMMARY
    ========================================== --}}
    <div class="row g-3 mb-3">

        {{-- Staff Count --}}
        <div class="col-6 col-md-3">
            <div class="metric-soft-card metric-card-blue">

                <div class="d-flex align-items-center justify-content-between gap-2">
                    <span class="stat-label-title text-truncate">Total Staff</span>
                    <span class="metric-icon">
                        <i class="fas fa-users"></i>
                    </span>
                </div>

                <h5 class="fw-bold my-0 stat-val-num">
                    {{ $staffCount }} Members
                </h5>

                <small class="metric-subtext">
                    {{ $missingStaffCount }} pending generation
                </small>

            </div>
        </div>

        {{-- Total Payable --}}
        <div class="col-6 col-md-3">
            <div class="metric-soft-card metric-card-amber">

                <div class="d-flex align-items-center justify-content-between gap-2">
                    <span class="stat-label-title text-truncate">Total Payable</span>
                    <span class="metric-icon amber">
                        <i class="fas fa-receipt"></i>
                    </span>
                </div>

                <h5 class="fw-bold my-0 stat-val-num">
                    ৳ {{ number_format($totalPayable, 2) }}
                </h5>

                <small class="metric-subtext">
                    {{ $monthName }}
                </small>

            </div>
        </div>

        {{-- Total Paid --}}
        <div class="col-6 col-md-3">
            <div class="metric-soft-card metric-card-green">

                <div class="d-flex align-items-center justify-content-between gap-2">
                    <span class="stat-label-title text-truncate">Total Paid</span>
                    <span class="metric-icon green">
                        <i class="fas fa-circle-check"></i>
                    </span>
                </div>

                <h5 class="fw-bold my-0 text-success stat-val-num">
                    ৳ {{ number_format($totalPaid, 2) }}
                </h5>

                <small class="metric-subtext">
                    Paid from Bank / Cash
                </small>

            </div>
        </div>

        {{-- Pending --}}
        <div class="col-6 col-md-3">
            <div class="metric-soft-card metric-card-red">

                <div class="d-flex align-items-center justify-content-between gap-2">
                    <span class="stat-label-title text-truncate">Pending Amount</span>
                    <span class="metric-icon red">
                        <i class="fas fa-clock"></i>
                    </span>
                </div>

                <h5 class="fw-bold my-0 text-danger stat-val-num">
                    ৳ {{ number_format($totalPending, 2) }}
                </h5>

                <small class="metric-subtext">
                    Awaiting payment
                </small>

            </div>
        </div>

    </div>

    {{-- =========================================
         FILTER TOOLBAR
    ========================================== --}}
    <div class="filter-toolbar">

        <form method="GET"
              action="{{ route('admin.payroll.index') }}"
              class="row g-3 align-items-end"
              id="payrollFilterForm">

            <div class="col-6 col-md-3">
                <label class="form-label" for="payroll_month">
                    <i class="fas fa-calendar-alt text-primary me-1"></i>
                    Month
                </label>

                <select name="month"
                        id="payroll_month"
                        class="form-select font-monospace"
                        onchange="document.getElementById('payrollFilterForm').submit()">

                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}
                        </option>
                    @endfor

                </select>
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label" for="payroll_year">
                    <i class="fas fa-calendar-days text-primary me-1"></i>
                    Year
                </label>

                <select name="year"
                        id="payroll_year"
                        class="form-select font-monospace"
                        onchange="document.getElementById('payrollFilterForm').submit()">

                    @for($y = Carbon\Carbon::now()->year; $y >= 2024; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor

                </select>
            </div>

            <div class="col-12 col-md-6">
                <div class="filter-actions d-flex flex-wrap gap-2">

                    <button type="submit"
                            class="btn btn-dark btn-compact px-3 shadow-sm fw-bold">
                        <i class="fas fa-filter"></i>
                        Filter
                    </button>

                    @if($missingStaffCount > 0)
                        <button type="button"
                                class="btn btn-outline-primary btn-compact px-3 shadow-sm fw-bold"
                                onclick="generateSalarySheet()">
                            <i class="fas fa-plus-circle"></i>
                            Add {{ $missingStaffCount }} Missing Staff
                        </button>
                    @endif

                </div>
            </div>

        </form>
    </div>

    {{-- =========================================
         SALARY SHEET
    ========================================== --}}
    <div class="card payroll-surface-card p-0 mb-3">

        {{-- Table Header --}}
        <div class="payroll-table-header">

            <div class="table-heading">
                <i class="fas fa-list-check text-primary me-1"></i>
                Salary Breakdown: {{ $monthName }}
                <span class="text-muted fw-normal">(30-day closing)</span>
            </div>

            <span class="badge bg-light text-dark border table-count-badge">
                {{ count($payrolls) }} Staff
            </span>

        </div>

        {{-- Responsive Table --}}
        <div class="payroll-table-wrap">

            <table class="table align-middle custom-payroll-table">

                <thead>
                    <tr>
                        <th class="ps-3">Staff Details</th>
                        <th>Joining Date</th>
                        <th class="text-center">Days Worked</th>
                        <th class="text-end">Basic Salary</th>
                        <th class="text-end">Bonus</th>
                        <th class="text-end">Deduction</th>
                        <th class="text-end">Net Payable</th>
                        <th class="text-center">Status</th>
                        <th>Payment Account</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($payrolls as $p)
                        @php
                            $isPaid = $p->status === 'paid';
                        @endphp

                        <tr>

                            {{-- Staff Details --}}
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">

                                    <div class="staff-avatar">
                                        {{ strtoupper(substr($p->staff_name, 0, 1)) }}
                                    </div>

                                    <div class="min-w-0">
                                        <strong class="staff-name d-block text-truncate">
                                            {{ $p->staff_name }}
                                        </strong>

                                        <small class="staff-meta d-block">
                                            {{ $p->staff_role ?? 'Staff' }}
                                            <span class="mx-1">|</span>
                                            {{ $p->voucher_no }}
                                        </small>
                                    </div>

                                </div>
                            </td>

                            {{-- Joining Date --}}
                            <td>
                                <span class="table-badge bg-light text-secondary border">
                                    <i class="far fa-calendar-alt"></i>

                                    {{ !empty($p->joining_date) ? date('d M Y', strtotime($p->joining_date)) : 'N/A' }}
                                </span>
                            </td>

                            {{-- Days Worked --}}
                            <td class="text-center">
                                <span class="table-badge bg-light text-dark border">
                                    {{ $p->worked_days }} / {{ $p->working_days ?? 30 }} Days
                                </span>
                            </td>

                            {{-- Basic Salary --}}
                            <td class="text-end">
                                <span class="table-value text-muted">
                                    ৳{{ number_format((float) $p->basic_salary, 2) }}
                                </span>
                            </td>

                            {{-- Bonus --}}
                            <td class="text-end">
                                <span class="table-value text-success">
                                    +৳{{ number_format((float) $p->bonus, 2) }}
                                </span>
                            </td>

                            {{-- Deduction --}}
                            <td class="text-end">
                                <span class="table-value text-danger">
                                    -৳{{ number_format((float) $p->deduction, 2) }}
                                </span>
                            </td>

                            {{-- Net Payable --}}
                            <td class="text-end">
                                <span class="table-net-value">
                                    ৳{{ number_format((float) $p->net_salary, 2) }}
                                </span>
                            </td>

                            {{-- Payment Status --}}
                            <td class="text-center">

                                @if($isPaid)
                                    <span class="table-badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="fas fa-check-circle"></i>
                                        Paid
                                    </span>
                                @else
                                    <span class="table-badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                        <i class="fas fa-clock"></i>
                                        Pending
                                    </span>
                                @endif

                            </td>

                            {{-- Disbursement Details --}}
                            <td>

                                @if($isPaid)

                                    <small class="d-block text-muted fw-semibold" style="font-size: 11px;">
                                        <i class="fas fa-building-columns text-primary me-1"></i>
                                        {{ $p->bank_name ?? 'Bank Account' }}
                                    </small>

                                    <small class="d-block text-muted mt-1" style="font-size: 10px;">
                                        {{ date('d M Y, h:i A', strtotime($p->paid_at)) }}
                                    </small>

                                @else

                                    <span class="text-muted">—</span>

                                @endif

                            </td>

                            {{-- Actions --}}
                            <td class="text-end pe-3">

                                <div class="table-actions">

                                    @if(!$isPaid)

                                        <button type="button"
                                                class="btn btn-sm btn-success btn-compact shadow-sm"
                                                onclick="openDisburseModal({{ json_encode($p) }})"
                                                title="Pay Salary">
                                            <i class="fas fa-paper-plane"></i>
                                            Pay
                                        </button>

                                        <button type="button"
                                                class="btn btn-sm btn-light border btn-compact shadow-sm"
                                                onclick="openAdjustModal({{ json_encode($p) }})"
                                                title="Adjust Salary"
                                                aria-label="Adjust salary for {{ $p->staff_name }}">
                                            <i class="fas fa-sliders text-secondary"></i>
                                        </button>

                                    @else

                                        <a href="{{ route('admin.payroll.payslip', $p->id) }}"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="btn btn-sm btn-outline-secondary btn-compact shadow-sm"
                                           title="Print Payslip">
                                            <i class="fas fa-print"></i>
                                            Slip
                                        </a>

                                    @endif

                                    <a href="{{ route('admin.payroll.history', $p->user_id) }}"
                                       class="btn btn-sm btn-light border btn-compact shadow-sm"
                                       title="Payment History"
                                       aria-label="Payment history for {{ $p->staff_name }}">
                                        <i class="fas fa-history text-primary"></i>
                                    </a>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="10" class="empty-state">

                                <div class="empty-state-icon">
                                    <i class="fas fa-money-check-dollar"></i>
                                </div>

                                <div class="fw-bold text-dark mb-1" style="font-size: 14px;">
                                    No Salary Records Found
                                </div>

                                <p class="text-muted mb-3" style="font-size: 12px;">
                                    No salary records for {{ $monthName }}.
                                </p>

                                <button type="button"
                                        class="btn btn-primary btn-compact shadow-sm"
                                        onclick="generateSalarySheet()">
                                    <i class="fas fa-rotate"></i>
                                    Generate Salary Sheet
                                </button>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>
    </div>

</div>


{{-- =========================================
     MODAL 1: DISBURSE SALARY
========================================== --}}
<div class="modal fade payroll-modal-scope"
     id="disburseSalaryModal"
     tabindex="-1"
     aria-hidden="true"
     aria-labelledby="disburseSalaryTitle"
     style="z-index: 106000;">

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content">

            <div class="modal-header">

                <span class="modal-title-text" id="disburseSalaryTitle">
                    <i class="fas fa-money-bill-transfer text-success me-1"></i>
                    Pay Staff Salary
                </span>

                <button type="button"
                        class="btn-close shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>

            </div>

            <form action="{{ route('admin.payroll.disburse') }}"
                  method="POST"
                  id="disburseForm">

                @csrf

                <input type="hidden"
                       name="payroll_id"
                       id="disburse_payroll_id"
                       required>

                <div class="modal-body">

                    {{-- Payment Summary --}}
                    <div class="modal-summary">

                        <span class="modal-staff-name d-block"
                              id="disburse_staff_name">
                            Staff Name
                        </span>

                        <h4 class="modal-amount text-success my-1"
                            id="disburse_amount">
                            ৳0.00
                        </h4>

                        <small class="modal-voucher"
                               id="disburse_voucher">
                            SAL-2026-00
                        </small>

                    </div>

                    {{-- Bank Account --}}
                    <div class="mb-3">

                        <label class="form-label" for="disburse_bank_id">
                            Bank / Cash Account
                            <span class="text-danger">*</span>
                        </label>

                        <select name="bank_account_id"
                                id="disburse_bank_id"
                                class="form-select font-monospace"
                                required>

                            <option value="">
                                — Select Account —
                            </option>

                            @foreach($bankAccounts as $bank)
                                <option value="{{ $bank->id }}">
                                    {{ $bank->bank_name ?? $bank->account_name }}
                                    (Avail: ৳{{ number_format((float) $bank->current_balance, 2) }})
                                </option>
                            @endforeach

                        </select>

                        <small class="form-help">
                            Automatically recorded as Staff Salary expense.
                        </small>

                    </div>

                    {{-- Payment Method --}}
                    <div class="mb-3">

                        <label class="form-label" for="disburse_payment_method">
                            Payment Method
                        </label>

                        <select name="payment_method"
                                id="disburse_payment_method"
                                class="form-select font-monospace">

                            <option value="bank_transfer" selected>
                                Bank Transfer / BEFTN
                            </option>

                            <option value="cash">Cash</option>
                            <option value="bkash">bKash Payroll</option>
                            <option value="nagad">Nagad</option>
                            <option value="check">Bank Cheque</option>

                        </select>

                    </div>

                    {{-- Payment Note --}}
                    <div class="mb-1">

                        <label class="form-label" for="disburse_notes">
                            Payment Note (Optional)
                        </label>

                        <textarea name="notes"
                                  id="disburse_notes"
                                  class="form-control"
                                  rows="2"
                                  placeholder="Reference, cheque no. or transaction ID..."></textarea>

                    </div>

                </div>

                <div class="modal-footer justify-content-between">

                    <button type="button"
                            class="btn btn-light border btn-compact"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-success btn-compact fw-bold px-3">
                        <i class="fas fa-check-circle"></i>
                        Confirm Payment
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>


{{-- =========================================
     MODAL 2: ADJUST SALARY
========================================== --}}
<div class="modal fade payroll-modal-scope"
     id="adjustSalaryModal"
     tabindex="-1"
     aria-hidden="true"
     aria-labelledby="adjustSalaryTitle"
     style="z-index: 106000;">

    <div class="modal-dialog modal-dialog-centered modal-sm">

        <div class="modal-content">

            <div class="modal-header">

                <span class="modal-title-text" id="adjustSalaryTitle">
                    <i class="fas fa-sliders text-primary me-1"></i>
                    Adjust Salary
                </span>

                <button type="button"
                        class="btn-close shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>

            </div>

            <form action="{{ route('admin.payroll.adjust') }}"
                  method="POST"
                  id="adjustForm">

                @csrf

                <input type="hidden"
                       name="payroll_id"
                       id="adjust_payroll_id"
                       required>

                <div class="modal-body">

                    {{-- Worked Days --}}
                    <div class="mb-3">

                        <label class="form-label" for="adjust_worked_days">
                            Days Worked (30-day cycle)
                        </label>

                        <input type="number"
                               name="worked_days"
                               id="adjust_worked_days"
                               min="1"
                               max="30"
                               class="form-control font-monospace"
                               required>

                    </div>

                    {{-- Bonus & Deduction --}}
                    <div class="row g-2 mb-3">

                        <div class="col-6">

                            <label class="form-label text-success"
                                   for="adjust_bonus">
                                Bonus (৳)
                            </label>

                            <input type="number"
                                   step="0.01"
                                   name="bonus"
                                   id="adjust_bonus"
                                   class="form-control font-monospace text-success">

                        </div>

                        <div class="col-6">

                            <label class="form-label text-danger"
                                   for="adjust_deduction">
                                Deduction (৳)
                            </label>

                            <input type="number"
                                   step="0.01"
                                   name="deduction"
                                   id="adjust_deduction"
                                   class="form-control font-monospace text-danger">

                        </div>

                    </div>

                    {{-- Adjustment Reason --}}
                    <div class="mb-1">

                        <label class="form-label" for="adjust_notes">
                            Reason
                        </label>

                        <textarea name="notes"
                                  id="adjust_notes"
                                  class="form-control"
                                  rows="2"
                                  placeholder="Bonus or deduction reason..."></textarea>

                    </div>

                </div>

                <div class="modal-footer justify-content-between">

                    <button type="button"
                            class="btn btn-light border btn-compact"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-primary btn-compact fw-bold px-3">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>


{{-- =========================================
     HIDDEN SALARY GENERATION FORM
========================================== --}}
<form id="autoGenerateSalaryForm"
      action="{{ route('admin.payroll.generate') }}"
      method="POST"
      class="d-none">

    @csrf

    <input type="hidden" name="year" value="{{ $year }}">
    <input type="hidden" name="month" value="{{ $month }}">

</form>


{{-- =========================================
     PAYROLL JAVASCRIPT
========================================== --}}
<script>
    /**
     * Generate / Sync Monthly Salary Sheet
     */
    function generateSalarySheet() {
        const monthName = @json($monthName);

        const message =
            `Generate or sync monthly salary sheet for ${monthName} based on appointment date (30th closing)?`;

        if (confirm(message)) {
            const form = document.getElementById('autoGenerateSalaryForm');

            if (form) {
                form.submit();
            }
        }
    }


    /**
     * Open Salary Disbursement Modal
     */
    function openDisburseModal(payroll) {
        if (!payroll) {
            return;
        }

        const payrollId = document.getElementById('disburse_payroll_id');
        const staffName = document.getElementById('disburse_staff_name');
        const amount = document.getElementById('disburse_amount');
        const voucher = document.getElementById('disburse_voucher');

        if (payrollId) {
            payrollId.value = payroll.id;
        }

        if (staffName) {
            staffName.textContent = payroll.staff_name || 'Staff';
        }

        if (amount) {
            const salary = Number(payroll.net_salary) || 0;

            amount.textContent = '৳' + salary.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        if (voucher) {
            voucher.textContent = payroll.voucher_no || 'VOUCHER';
        }

        const modalEl = document.getElementById('disburseSalaryModal');

        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }


    /**
     * Open Salary Adjustment Modal
     */
    function openAdjustModal(payroll) {
        if (!payroll) {
            return;
        }

        const payrollId = document.getElementById('adjust_payroll_id');
        const workedDays = document.getElementById('adjust_worked_days');
        const bonus = document.getElementById('adjust_bonus');
        const deduction = document.getElementById('adjust_deduction');
        const notes = document.getElementById('adjust_notes');

        if (payrollId) {
            payrollId.value = payroll.id;
        }

        if (workedDays) {
            workedDays.value = payroll.worked_days || 30;
        }

        if (bonus) {
            bonus.value = Number(payroll.bonus) > 0
                ? payroll.bonus
                : '';
        }

        if (deduction) {
            deduction.value = Number(payroll.deduction) > 0
                ? payroll.deduction
                : '';
        }

        if (notes) {
            notes.value = payroll.notes || '';
        }

        const modalEl = document.getElementById('adjustSalaryModal');

        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }
</script>

@endsection
