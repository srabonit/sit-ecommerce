@extends('layouts.admin')

@section('title', 'Daily Cash Register - ' . $branch->name)

@section('admin_content')
<style>
    /* =========================================================
       DAILY CASH REGISTER — KAIADMIN WHITE CLASSIC
    ========================================================= */

    #dailyClosingRoot {
        --closing-primary: var(--bs-primary, #1572e8);
        --closing-primary-soft: #eaf2ff;
        --closing-secondary: #6b7280;
        --closing-success: #31ce36;
        --closing-success-soft: #eaf8ec;
        --closing-danger: #f25961;
        --closing-danger-soft: #fff0f0;
        --closing-warning: #ffad46;
        --closing-warning-soft: #fff7e8;
        --closing-info: #48abf7;
        --closing-info-soft: #edf7ff;
        --closing-border: #e5e7eb;
        --closing-text: #2a2f36;
        --closing-muted: #6b7280;
        --closing-surface: #ffffff;
        --closing-body: #f5f7fd;
        --closing-radius: 8px;

        width: 100%;
        min-width: 0;
        color: var(--closing-text);
        font-size: 13px;
    }

    #dailyClosingRoot *,
    #dailyClosingRoot *::before,
    #dailyClosingRoot *::after {
        box-sizing: border-box;
    }

    #dailyClosingRoot .card,
    #dailyClosingRoot .btn,
    #dailyClosingRoot .badge,
    #dailyClosingRoot .form-control,
    #dailyClosingRoot .form-select,
    #dailyClosingRoot .input-group-text,
    #dailyClosingRoot .alert,
    #dailyClosingRoot .table {
        border-radius: var(--closing-radius);
    }

    /* Page Header */

    #dailyClosingRoot .closing-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        padding: 15px 18px;
        margin-bottom: 18px;
        background: var(--closing-surface);
        border: 1px solid var(--closing-border);
        border-radius: var(--closing-radius);
        box-shadow: 0 1px 2px rgba(0, 0, 0, .025);
    }

    #dailyClosingRoot .closing-header-info {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        min-width: 0;
    }

    #dailyClosingRoot .closing-branch-name {
        margin: 0;
        color: var(--closing-text);
        font-size: 16px;
        font-weight: 700;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    #dailyClosingRoot .closing-header-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    #dailyClosingRoot .closing-header .badge {
        font-size: 10.5px;
        font-weight: 600;
        letter-spacing: .1px;
    }

    #dailyClosingRoot .closing-header-info > .text-muted {
        font-size: 11.5px;
    }

    /* Buttons */

    #dailyClosingRoot .btn {
        font-weight: 600;
        transition: all .18s ease;
    }

    #dailyClosingRoot .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 36px;
        padding: 7px 13px;
        font-size: 11.5px;
        line-height: 1.35;
        white-space: nowrap;
        border-radius: 6px;
    }

    #dailyClosingRoot .btn-compact i,
    #dailyClosingRoot .closing-action-button i {
        font-size: 12px;
    }

    #dailyClosingRoot .btn-primary {
        background-color: var(--closing-primary);
        border-color: var(--closing-primary);
    }

    #dailyClosingRoot .btn-primary:hover {
        filter: brightness(.94);
    }

    #dailyClosingRoot .btn-warning {
        background-color: var(--closing-warning);
        border-color: var(--closing-warning);
    }

    #dailyClosingRoot .btn-danger {
        background-color: var(--closing-danger);
        border-color: var(--closing-danger);
    }

    #dailyClosingRoot .btn-success {
        background-color: var(--closing-success);
        border-color: var(--closing-success);
    }

    /* Main Panels */

    #dailyClosingRoot .closing-panel {
        height: 100%;
        min-width: 0;
        padding: 17px;
        margin-bottom: 0;
        background: var(--closing-surface);
        border: 1px solid var(--closing-border);
        border-radius: var(--closing-radius);
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035);
    }

    #dailyClosingRoot .closing-panel-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        padding-bottom: 13px;
        margin-bottom: 14px;
        border-bottom: 1px solid var(--closing-border);
    }

    #dailyClosingRoot .closing-panel-title h6 {
        display: flex;
        align-items: center;
        gap: 7px;
        margin: 0;
        color: var(--closing-text);
        font-size: 13px;
        font-weight: 700;
        line-height: 1.5;
    }

    #dailyClosingRoot .closing-panel-title h6 i {
        font-size: 14px;
    }

    #dailyClosingRoot .closing-panel-title .badge {
        padding: 5px 9px !important;
        font-size: 10px;
        font-weight: 700;
    }

    /* KPI Cards */

    #dailyClosingRoot .stat-card-widget {
        position: relative;
        height: 100%;
        min-height: 112px;
        padding: 15px;
        overflow: hidden;
        background: var(--closing-surface);
        border: 1px solid var(--closing-border);
        border-radius: var(--closing-radius);
        box-shadow: 0 1px 2px rgba(0, 0, 0, .025);
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    #dailyClosingRoot .stat-card-widget:hover {
        border-color: #cbd5e1;
        box-shadow: 0 3px 12px rgba(15, 23, 42, .055);
    }

    #dailyClosingRoot .stat-label {
        display: block;
        margin-bottom: 9px;
        color: var(--closing-muted);
        font-size: 11px;
        font-weight: 600;
        line-height: 1.5;
    }

    #dailyClosingRoot .stat-value {
        margin: 0 0 6px;
        color: var(--closing-text);
        font-size: clamp(15px, 1.4vw, 19px);
        font-weight: 700;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    #dailyClosingRoot .stat-caption {
        display: block;
        color: var(--closing-muted);
        font-size: 10.5px;
        line-height: 1.4;
    }

    /* Numeric Typography */

    #dailyClosingRoot .font-monospace,
    #dailyClosingRoot .bn-digit {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    /* Financial Table */

    #dailyClosingRoot .table-responsive {
        width: 100%;
        min-width: 0;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    #dailyClosingRoot .closing-table {
        width: 100%;
        min-width: 360px;
        margin-bottom: 0;
        color: var(--closing-text);
        font-size: 12px;
        border-collapse: separate;
        border-spacing: 0;
    }

    #dailyClosingRoot .closing-table td {
        padding: 10px 9px;
        vertical-align: middle;
        line-height: 1.5;
        border-bottom: 1px solid #edf0f3;
    }

    #dailyClosingRoot .closing-table tr:last-child td {
        border-bottom: 0;
    }

    #dailyClosingRoot .closing-table td:first-child {
        width: 65%;
        color: var(--closing-muted);
        overflow-wrap: anywhere;
    }

    #dailyClosingRoot .closing-table td:last-child {
        width: 35%;
        white-space: nowrap;
    }

    #dailyClosingRoot .closing-highlight {
        background: var(--closing-primary-soft);
    }

    #dailyClosingRoot .closing-highlight td {
        padding-top: 13px;
        padding-bottom: 13px;
        border-bottom: 0;
    }

    #dailyClosingRoot .closing-highlight td:first-child {
        color: var(--closing-text);
        font-weight: 700;
    }

    #dailyClosingRoot .closing-highlight td:last-child {
        font-size: 16px;
    }

    /* Digital Payment Summary */

    #dailyClosingRoot .closing-info-box {
        padding: 13px;
        background: #f8fafc;
        border: 1px solid var(--closing-border);
        border-radius: 7px;
    }

    #dailyClosingRoot .closing-info-title {
        display: block;
        color: #374151;
        font-size: 11.5px;
        font-weight: 700;
        line-height: 1.5;
    }

    #dailyClosingRoot .closing-info-description {
        display: block;
        margin-top: 4px;
        color: var(--closing-muted);
        font-size: 10.5px;
        line-height: 1.55;
    }

    #dailyClosingRoot .closing-digital-amount {
        color: var(--closing-text);
        font-size: 16px;
        font-weight: 700;
        white-space: nowrap;
    }

    /* Forms */

    #dailyClosingRoot .form-label {
        display: block;
        margin-bottom: 7px;
        color: #374151;
        font-size: 11.5px;
        font-weight: 600;
        line-height: 1.5;
    }

    #dailyClosingRoot .form-control,
    #dailyClosingRoot .form-select {
        min-height: 38px;
        padding: 8px 11px;
        color: var(--closing-text);
        background-color: #fff;
        border: 1px solid #dfe3e8;
        border-radius: 6px;
        font-size: 12px;
        line-height: 1.5;
        box-shadow: none;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    #dailyClosingRoot .form-control::placeholder {
        color: #a0a8b3;
        opacity: 1;
    }

    #dailyClosingRoot .form-control:focus,
    #dailyClosingRoot .form-select:focus {
        border-color: var(--closing-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
    }

    #dailyClosingRoot .form-control-lg {
        min-height: 44px;
        padding: 9px 12px;
        font-size: 15px;
    }

    #dailyClosingRoot .input-group {
        flex-wrap: nowrap;
    }

    #dailyClosingRoot .input-group-text {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 43px;
        padding: 8px 12px;
        color: #526071;
        background: #f8fafc;
        border: 1px solid #dfe3e8;
        font-size: 13px;
    }

    #dailyClosingRoot textarea.form-control {
        min-height: 72px;
        resize: vertical;
    }

    #dailyClosingRoot .form-text,
    #dailyClosingRoot .text-muted.small {
        line-height: 1.55;
    }

    #dailyClosingRoot .form-control:disabled {
        background: #f8fafc;
        opacity: .8;
    }

    /* Denomination Calculator */

    #dailyClosingRoot .denom-calculator {
        padding: 13px;
        background: #f8fafc;
        border: 1px solid var(--closing-border);
        border-radius: 7px;
    }

    #dailyClosingRoot .denom-input {
        width: 76px !important;
        height: 34px !important;
        min-height: 34px !important;
        padding: 4px 7px !important;
        text-align: center;
        font-size: 11.5px !important;
        border-radius: 5px !important;
    }

    #dailyClosingRoot .denom-calculator .row {
        row-gap: 9px;
    }

    #dailyClosingRoot .denom-calculator .border-top {
        border-color: var(--closing-border) !important;
    }

    #dailyClosingRoot #denomTallyTotal {
        font-size: 13px;
    }

    /* Cash Difference */

    #dailyClosingRoot .closing-difference {
        padding: 16px 12px;
        margin-bottom: 16px;
        text-align: center;
        background: #f8fafc;
        border: 1px solid var(--closing-border);
        border-radius: 8px;
        transition: background-color .18s ease, border-color .18s ease;
    }

    #dailyClosingRoot .closing-difference-value {
        margin: 7px 0;
        font-size: clamp(21px, 2vw, 25px);
        font-weight: 700;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    #dailyClosingRoot #differenceStatusLabel {
        font-size: 11px;
        line-height: 1.5;
    }

    /* Opening State */

    #dailyClosingRoot .closing-open-card {
        width: 100%;
        max-width: 540px;
        margin: 0 auto;
        padding: 28px;
        background: var(--closing-surface);
        border: 1px solid var(--closing-border);
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .035);
    }

    #dailyClosingRoot .closing-open-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 62px;
        height: 62px;
        border-radius: 50%;
    }

    #dailyClosingRoot .closing-open-card h5 {
        color: var(--closing-text);
        font-size: 17px;
        line-height: 1.4;
    }

    #dailyClosingRoot .closing-open-card > p {
        max-width: 420px;
        margin-right: auto;
        margin-left: auto;
        line-height: 1.7;
    }

    /* Completed State */

    #dailyClosingRoot .closing-completed {
        padding: 18px;
        background: #f8fafc;
        border: 1px solid var(--closing-border);
        border-radius: 8px;
    }

    #dailyClosingRoot .closing-completed h4 {
        font-size: clamp(17px, 2vw, 21px);
        overflow-wrap: anywhere;
    }

    #dailyClosingRoot .closing-completed .badge {
        font-size: 10.5px !important;
    }

    /* Action Buttons */

    #dailyClosingRoot .closing-action-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 40px;
        padding: 9px 13px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.4;
        border-radius: 6px;
    }

    #dailyClosingRoot .closing-action-button:disabled {
        cursor: not-allowed;
        opacity: .7;
    }

    /* Alerts */

    #dailyClosingRoot .alert {
        border-width: 1px;
        font-size: 12px;
        line-height: 1.55;
    }

    /* Badges */

    #dailyClosingRoot .badge {
        font-weight: 600;
        letter-spacing: .1px;
    }

    /* Responsive: Tablet */

    @media (max-width: 991.98px) {
        #dailyClosingRoot .closing-panel {
            padding: 15px;
        }

        #dailyClosingRoot .stat-card-widget {
            min-height: 105px;
        }

        #dailyClosingRoot .stat-value {
            font-size: 15px;
        }

        #dailyClosingRoot .closing-table td {
            padding: 9px 8px;
        }
    }

    /* Responsive: Mobile */

    @media (max-width: 767.98px) {
        #dailyClosingRoot {
            font-size: 12.5px;
        }

        #dailyClosingRoot .closing-header {
            align-items: flex-start;
            padding: 13px;
            margin-bottom: 14px;
        }

        #dailyClosingRoot .closing-header-info {
            width: 100%;
            gap: 8px;
        }

        #dailyClosingRoot .closing-branch-name {
            font-size: 14px;
        }

        #dailyClosingRoot .closing-header-actions {
            width: 100%;
        }

        #dailyClosingRoot .closing-header-actions .btn {
            flex: 1 1 auto;
        }

        #dailyClosingRoot .closing-panel {
            padding: 13px;
        }

        #dailyClosingRoot .closing-panel-title {
            padding-bottom: 11px;
            margin-bottom: 12px;
        }

        #dailyClosingRoot .closing-panel-title h6 {
            font-size: 12.5px;
        }

        #dailyClosingRoot .stat-card-widget {
            min-height: 100px;
            padding: 12px;
        }

        #dailyClosingRoot .stat-label {
            margin-bottom: 7px;
            font-size: 10.5px;
        }

        #dailyClosingRoot .stat-value {
            font-size: 14px;
        }

        #dailyClosingRoot .stat-caption {
            font-size: 10px;
        }

        #dailyClosingRoot .closing-digital-amount {
            font-size: 14px;
        }

        #dailyClosingRoot .closing-open-card {
            padding: 22px 17px;
        }

        #dailyClosingRoot .closing-info-box {
            padding: 11px;
        }

        #dailyClosingRoot .closing-table {
            font-size: 11.5px;
        }

        #dailyClosingRoot .closing-table td:first-child {
            width: 62%;
        }

        #dailyClosingRoot .closing-table td:last-child {
            width: 38%;
        }
    }

    /* Responsive: Small Mobile */

    @media (max-width: 420px) {
        #dailyClosingRoot .closing-header {
            padding: 11px;
        }

        #dailyClosingRoot .closing-header-info {
            gap: 7px;
        }

        #dailyClosingRoot .closing-header-actions {
            flex-direction: column;
            align-items: stretch;
        }

        #dailyClosingRoot .closing-header-actions .btn {
            width: 100%;
            min-height: 38px;
        }

        #dailyClosingRoot .closing-panel {
            padding: 11px;
        }

        #dailyClosingRoot .closing-panel-title {
            align-items: flex-start;
        }

        #dailyClosingRoot .closing-info-box > .d-flex {
            align-items: flex-start !important;
            flex-direction: column;
            gap: 9px;
        }

        #dailyClosingRoot .closing-digital-amount {
            font-size: 15px;
        }

        #dailyClosingRoot .denom-input {
            width: 65px !important;
        }

        #dailyClosingRoot .denom-calculator {
            padding: 9px;
        }

        #dailyClosingRoot .closing-open-card {
            padding: 18px 13px;
        }

        #dailyClosingRoot .closing-completed {
            padding: 13px 10px;
        }

        #dailyClosingRoot .closing-action-button {
            min-height: 42px;
            padding: 9px 10px;
            font-size: 11.5px;
        }

        #dailyClosingRoot .closing-table {
            min-width: 340px;
        }
    }

    /* Extra Small Mobile */

    @media (max-width: 360px) {
        #dailyClosingRoot .stat-card-widget {
            padding: 10px;
        }

        #dailyClosingRoot .stat-label {
            font-size: 10px;
        }

        #dailyClosingRoot .stat-value {
            font-size: 13px;
        }

        #dailyClosingRoot .closing-header-info > .badge {
            font-size: 9.5px;
        }

        #dailyClosingRoot .closing-open-card h5 {
            font-size: 15px;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-3" id="dailyClosingRoot">

    <!-- Branch Header -->
    <div class="closing-header">
        <div class="closing-header-info">
            <span class="badge bg-warning text-dark fw-bold px-2 py-2">
                <i class="fas fa-cash-register me-1"></i> Cash Register
            </span>

            <h5 class="closing-branch-name">{{ $branch->name }}</h5>

            <span class="badge bg-light text-dark border font-monospace px-2 py-1" style="font-size: 11px;">
                Code: {{ $branch->code ?? $branch->branch_code }}
            </span>

            <span class="text-muted small">
                <i class="far fa-calendar-alt me-1"></i> {{ date('l, d F Y') }}
            </span>
        </div>

        <div class="closing-header-actions">
            <a href="{{ route('admin.pos.terminal', $branch->id) }}" class="btn btn-primary btn-compact">
                <i class="fas fa-desktop"></i> Open POS
            </a>

            <a href="{{ route('admin.daily_closing.index', ['branch_id' => $branch->id]) }}" class="btn btn-outline-secondary btn-compact">
                <i class="fas fa-history"></i> Closing History
            </a>
        </div>
    </div>

    <!-- Alert Message -->
    <div id="sessionActionAlert" class="alert d-none py-2 px-3 mb-3" style="font-size: 12px;"></div>

    @php
        $status = $liveData['session_status'] ?? 'unopened';
    @endphp

    @if($status === 'unopened')

        <!-- Scenario 1: Register Not Opened -->
        <div class="row justify-content-center py-3">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="closing-open-card text-center">

                    <div class="mb-3">
                        <span class="closing-open-icon bg-warning-subtle text-warning">
                            <i class="fas fa-lock fa-2x"></i>
                        </span>
                    </div>

                    <h5 class="fw-bold text-dark mb-2">Cash Register Closed</h5>

                    <p class="text-muted small mb-4">
                        Enter the opening cash balance to start today's shift.
                    </p>

                    <form id="openRegisterForm" onsubmit="handleOpenRegister(event)">
                        @csrf
                        <input type="hidden" name="branch_id" value="{{ $branch->id }}">

                        <div class="text-start mb-3">
                            <label class="form-label">
                                Opening Cash (৳) <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">৳</span>

                                <input type="number" name="opening_cash" id="openCashInput"
                                       class="form-control form-control-lg font-monospace bn-digit fw-bold text-success"
                                       placeholder="0.00" value="0" min="0" step="any" required autofocus>
                            </div>

                            <small class="text-muted" style="font-size: 10.5px;">
                                Enter the cash available in the drawer.
                            </small>
                        </div>

                        <div class="text-start mb-3">
                            <label class="form-label">Opening Notes (Optional)</label>

                            <textarea name="notes" class="form-control" rows="2"
                                      placeholder="e.g. Morning shift opening balance"></textarea>
                        </div>

                        <button type="submit" class="btn btn-warning text-white fw-bold w-100 py-2 closing-action-button shadow-sm" id="btnSubmitOpen">
                            <i class="fas fa-key me-1"></i> Open Register &amp; Start Shift
                        </button>
                    </form>
                </div>
            </div>
        </div>

    @else

        <!-- Scenario 2: Register Open or Closed -->

        <!-- Live Shift KPI Cards -->
        <div class="row g-2 mb-3">

            <div class="col-6 col-md-3">
                <div class="stat-card-widget">
                    <span class="stat-label">1. Opening Cash</span>
                    <h5 class="stat-value text-dark font-monospace bn-digit">
                        ৳{{ number_format($liveData['opening_cash'], 2) }}
                    </h5>
                    <small class="stat-caption">Starting drawer balance</small>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="stat-card-widget">
                    <span class="stat-label">2. POS Cash Sales</span>
                    <h5 class="stat-value text-success font-monospace bn-digit">
                        +৳{{ number_format($liveData['pos_cash_sales'], 2) }}
                    </h5>
                    <small class="text-success stat-caption">
                        {{ $liveData['total_orders_count'] }} orders today
                    </small>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="stat-card-widget">
                    <span class="stat-label">3. Cash In / Due</span>
                    <h5 class="stat-value text-primary font-monospace bn-digit">
                        +৳{{ number_format($liveData['cash_due_collection'] + $liveData['cash_in'], 2) }}
                    </h5>
                    <small class="stat-caption">Due collections &amp; top-ups</small>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="stat-card-widget">
                    <span class="stat-label">4. Cash Expenses &amp; Out</span>
                    <h5 class="stat-value text-danger font-monospace bn-digit">
                        -৳{{ number_format($liveData['cash_expenses'] + $liveData['cash_refunds'] + $liveData['cash_out'], 2) }}
                    </h5>
                    <small class="text-danger stat-caption">Expenses &amp; payouts</small>
                </div>
            </div>

        </div>

        <div class="row g-3">

            <!-- Left: Cash Reconciliation -->
            <div class="col-12 col-lg-7">
                <div class="card closing-panel">

                    <div class="closing-panel-title">
                        <h6>
                            <i class="fas fa-calculator text-primary me-1"></i>
                            Cash Drawer Reconciliation
                        </h6>

                        <span class="badge {{ $status === 'open' ? 'bg-success' : 'bg-secondary' }} px-2 py-1">
                            Status: {{ strtoupper($status) }}
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-borderless closing-table">
                            <tbody>

                                <tr class="border-bottom">
                                    <td class="text-muted">(+) Opening Cash Balance:</td>
                                    <td class="text-end font-monospace bn-digit fw-semibold">
                                        ৳{{ number_format($liveData['opening_cash'], 2) }}
                                    </td>
                                </tr>

                                <tr class="border-bottom">
                                    <td class="text-muted">(+) POS Cash Sales:</td>
                                    <td class="text-end font-monospace bn-digit fw-semibold text-success">
                                        +৳{{ number_format($liveData['pos_cash_sales'], 2) }}
                                    </td>
                                </tr>

                                <tr class="border-bottom">
                                    <td class="text-muted">(+) Customer Due Collection:</td>
                                    <td class="text-end font-monospace bn-digit fw-semibold text-success">
                                        +৳{{ number_format($liveData['cash_due_collection'], 2) }}
                                    </td>
                                </tr>

                                <tr class="border-bottom">
                                    <td class="text-muted">(+) Cash In / Bank Withdrawals:</td>
                                    <td class="text-end font-monospace bn-digit fw-semibold text-success">
                                        +৳{{ number_format($liveData['cash_in'], 2) }}
                                    </td>
                                </tr>

                                <tr class="border-bottom">
                                    <td class="text-muted">(-) Store Cash Expenses:</td>
                                    <td class="text-end font-monospace bn-digit fw-semibold text-danger">
                                        -৳{{ number_format($liveData['cash_expenses'], 2) }}
                                    </td>
                                </tr>

                                <tr class="border-bottom">
                                    <td class="text-muted">(-) Customer Cash Refunds:</td>
                                    <td class="text-end font-monospace bn-digit fw-semibold text-danger">
                                        -৳{{ number_format($liveData['cash_refunds'], 2) }}
                                    </td>
                                </tr>

                                <tr class="border-bottom">
                                    <td class="text-muted">(-) Bank Deposit / HQ Handover:</td>
                                    <td class="text-end font-monospace bn-digit fw-semibold text-danger">
                                        -৳{{ number_format($liveData['cash_out'], 2) }}
                                    </td>
                                </tr>

                                <tr class="closing-highlight fw-bold">
                                    <td class="text-dark">(=) Expected Cash in Drawer:</td>
                                    <td class="text-end font-monospace bn-digit text-primary fs-5" id="displayExpectedCash">
                                        ৳{{ number_format($liveData['expected_cash'], 2) }}
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>

                    <!-- Digital Sales -->
                    <div class="closing-info-box mt-2">
                        <div class="d-flex justify-content-between align-items-center gap-3">
                            <div class="min-w-0">
                                <span class="closing-info-title">
                                    <i class="fas fa-mobile-alt text-info me-1"></i>
                                    Digital Payments (bKash / Nagad / Bank / Card)
                                </span>

                                <small class="closing-info-description">
                                    Digital receipts excluded from physical drawer cash.
                                </small>
                            </div>

                            <span class="closing-digital-amount font-monospace bn-digit text-dark">
                                ৳{{ number_format($liveData['digital_sales_amount'], 2) }}
                            </span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Right: Physical Cash Count and Closing -->
            <div class="col-12 col-lg-5">
                <div class="card closing-panel">

                    <div class="closing-panel-title">
                        <h6>
                            <i class="fas fa-money-bill-wave text-success me-1"></i>
                            {{ $status === 'open' ? 'Cash Drawer Closing' : 'Closing Summary & Audit' }}
                        </h6>
                    </div>

                    @if($status === 'open')

                        <!-- Denomination Calculator -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-muted small">Cash Denomination Counter</span>

                                <button type="button"
                                        class="btn btn-link text-primary p-0 text-decoration-none fw-semibold"
                                        style="font-size: 10.5px;"
                                        onclick="toggleDenomCalculator()">
                                    <i class="fas fa-coins me-1"></i>
                                    <span id="denomToggleText">Open Calculator</span>
                                </button>
                            </div>

                            <div id="denomCalculatorContainer" class="denom-calculator d-none">
                                <div class="row g-2 text-center" style="font-size: 11px;">
                                    @foreach([1000, 500, 200, 100, 50, 20, 10, 5, 2, 1] as $note)
                                        <div class="col-6 d-flex align-items-center justify-content-between">
                                            <span class="font-monospace text-muted" style="width: 48px;">৳{{ $note }} ×</span>

                                            <input type="number" class="form-control form-control-sm denom-input font-monospace"
                                                   data-multiplier="{{ $note }}" min="0" value="" placeholder="0"
                                                   oninput="calculateFromDenominations()">
                                        </div>
                                    @endforeach
                                </div>

                                <div class="text-end pt-2 border-top mt-2">
                                    <small class="text-muted me-1">Total:</small>
                                    <strong class="font-monospace bn-digit text-success" id="denomTallyTotal">৳0.00</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Physical Cash Form -->
                        <form id="closeRegisterForm" onsubmit="handleCloseRegister(event)">
                            @csrf
                            <input type="hidden" name="branch_id" value="{{ $branch->id }}">

                            <div class="mb-3">
                                <label class="form-label">
                                    Actual Cash Counted (৳) <span class="text-danger">*</span>
                                </label>

                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold">৳</span>

                                    <input type="number" name="actual_cash" id="actualCashInput"
                                           class="form-control form-control-lg font-monospace bn-digit fw-bold text-dark"
                                           placeholder="0.00" min="0" step="any" required
                                           oninput="calculateLiveDifference()">
                                </div>

                                <small class="text-muted" style="font-size: 10.5px;">
                                    Enter the total cash physically counted.
                                </small>
                            </div>

                            <!-- Live Difference -->
                            <div class="closing-difference" id="differenceContainer">
                                <span class="text-muted d-block small" style="font-size: 10.5px;">
                                    Difference (Actual - Expected):
                                </span>

                                <h4 class="closing-difference-value font-monospace bn-digit text-secondary" id="displayDifference">
                                    ৳0.00
                                </h4>

                                <small class="fw-semibold d-block" id="differenceStatusLabel" style="font-size: 11px;">
                                    Drawer in Exact Balance
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Closing Notes</label>

                                <textarea name="notes" class="form-control" rows="2"
                                          placeholder="e.g. Cash shortage due to rounding"></textarea>
                            </div>

                            <button type="submit" class="btn btn-danger fw-bold w-100 py-2 closing-action-button shadow-sm" id="btnSubmitClose">
                                <i class="fas fa-lock me-1"></i> Close Register &amp; Submit Shift
                            </button>
                        </form>

                    @else

                        <!-- Closed Register Summary -->
                        @if($sessionRecord)

                            <div class="closing-completed text-center mb-3">
                                <span class="badge bg-success mb-2 px-2 py-2" style="font-size: 11px;">
                                    <i class="fas fa-check-circle me-1"></i> Session Completed
                                </span>

                                <h4 class="fw-bold font-monospace bn-digit text-dark mb-1">
                                    Actual: ৳{{ number_format($sessionRecord->actual_cash, 2) }}
                                </h4>

                                <div class="text-muted small mb-2">
                                    Expected: ৳{{ number_format($sessionRecord->expected_cash, 2) }} |
                                    Difference:
                                    <strong class="{{ $sessionRecord->difference < 0 ? 'text-danger' : ($sessionRecord->difference > 0 ? 'text-success' : 'text-primary') }} font-monospace">
                                        {{ $sessionRecord->difference >= 0 ? '+' : '' }}৳{{ number_format($sessionRecord->difference, 2) }}
                                    </strong>
                                </div>

                                <small class="text-muted d-block" style="font-size: 10px;">
                                    Closed at: {{ \Carbon\Carbon::parse($sessionRecord->closed_at)->format('h:i A, d M Y') }}
                                </small>
                            </div>

                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.daily_closing.print', $sessionRecord->id) }}"
                                   target="_blank"
                                   class="btn btn-outline-dark closing-action-button py-2">
                                    <i class="fas fa-print me-1"></i> Print Closing Summary
                                </a>

                                @if($sessionRecord->status === 'closed' && (Auth::user()->isSuperAdmin() || Auth::user()->role === 'admin' || Auth::user()->role === 'branch_manager'))
                                    <button type="button" class="btn btn-success closing-action-button py-2"
                                            onclick="verifyClosingSession({{ $sessionRecord->id }})">
                                        <i class="fas fa-user-check me-1"></i> Verify &amp; Lock Audit
                                    </button>
                                @elseif($sessionRecord->status === 'verified')
                                    <div class="alert alert-success py-2 px-2 text-center mb-0" style="font-size: 11px;">
                                        <i class="fas fa-shield-alt me-1"></i> Verified by Management
                                    </div>
                                @endif
                            </div>

                        @endif
                    @endif

                </div>
            </div>

        </div>
    @endif
</div>

<script>
const CSRF = "{{ csrf_token() }}";
const EXPECTED_CASH = {{ (float) ($liveData['expected_cash'] ?? 0.00) }};

// Live Difference Calculator: Actual - Expected
function calculateLiveDifference() {
    const actualInput = document.getElementById('actualCashInput');
    const displayDiff = document.getElementById('displayDifference');
    const statusLabel = document.getElementById('differenceStatusLabel');
    const container   = document.getElementById('differenceContainer');

    if (!actualInput || !displayDiff) return;

    const actual = parseFloat(actualInput.value) || 0.00;
    const diff = Math.round((actual - EXPECTED_CASH) * 100) / 100;

    const sign = diff > 0 ? '+' : '';
    displayDiff.textContent = `${sign}৳${diff.toFixed(2)}`;

    if (diff === 0) {
        displayDiff.className = 'mb-0 fw-bold font-monospace bn-digit text-primary';
        statusLabel.textContent = 'Drawer in Exact Balance (0.00)';
        statusLabel.className = 'fw-semibold d-block mt-0.5 text-primary';
        container.style.background = '#eff6ff';
        container.style.borderColor = '#bfdbfe';
    } else if (diff < 0) {
        displayDiff.className = 'mb-0 fw-bold font-monospace bn-digit text-danger';
        statusLabel.textContent = `Cash Shortage of ৳${Math.abs(diff).toFixed(2)}`;
        statusLabel.className = 'fw-semibold d-block mt-0.5 text-danger';
        container.style.background = '#fef2f2';
        container.style.borderColor = '#fecaca';
    } else {
        displayDiff.className = 'mb-0 fw-bold font-monospace bn-digit text-success';
        statusLabel.textContent = `Cash Surplus / Excess of ৳${diff.toFixed(2)}`;
        statusLabel.className = 'fw-semibold d-block mt-0.5 text-success';
        container.style.background = '#f0fdf4';
        container.style.borderColor = '#bbf7d0';
    }
}

// Note Denomination Counter
function toggleDenomCalculator() {
    const box = document.getElementById('denomCalculatorContainer');
    const txt = document.getElementById('denomToggleText');
    if (box.classList.contains('d-none')) {
        box.classList.remove('d-none');
        txt.textContent = 'Hide Calculator';
    } else {
        box.classList.add('d-none');
        txt.textContent = 'Open Calculator';
    }
}

function calculateFromDenominations() {
    let total = 0;
    const inputs = document.querySelectorAll('.denom-input');

    inputs.forEach(input => {
        const mult = parseFloat(input.getAttribute('data-multiplier')) || 0;
        const count = parseInt(input.value) || 0;
        total += (mult * count);
    });

    document.getElementById('denomTallyTotal').textContent = '৳' + total.toFixed(2);

    const actualInput = document.getElementById('actualCashInput');
    if (actualInput) {
        actualInput.value = total.toFixed(2);
        calculateLiveDifference();
    }
}

// AJAX Open Register
function handleOpenRegister(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitOpen');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Opening...';

    const form = document.getElementById('openRegisterForm');
    const formData = new FormData(form);

    fetch("{{ route('admin.daily_closing.open') }}", {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            showAlertMessage(data.message || 'Failed to open register', 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-key me-1"></i> Open Register & Start Shift';
        }
    })
    .catch(() => {
        showAlertMessage('Server connection error. Please try again.', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-key me-1"></i> Open Register & Start Shift';
    });
}

// AJAX Close Register
function handleCloseRegister(e) {
    e.preventDefault();
    if (!confirm('Are you sure you want to close the cash drawer and end this shift? This action will lock today\'s session.')) {
        return;
    }

    const btn = document.getElementById('btnSubmitClose');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Closing...';

    const form = document.getElementById('closeRegisterForm');
    const formData = new FormData(form);

    fetch("{{ route('admin.daily_closing.close') }}", {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (data.print_url) {
                window.open(data.print_url, '_blank');
            }
            window.location.reload();
        } else {
            showAlertMessage(data.message || 'Failed to close register', 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-lock me-1"></i> Close Register & Submit Shift';
        }
    })
    .catch(() => {
        showAlertMessage('Server connection error. Please try again.', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock me-1"></i> Close Register & Submit Shift';
    });
}

// AJAX Manager Verification
function verifyClosingSession(sessionId) {
    if (!confirm('Confirm and lock this register audit?')) return;

    fetch("{{ url('admin/daily-closing/verify') }}/" + sessionId, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Verification failed');
        }
    });
}

function showAlertMessage(msg, type = 'info') {
    const el = document.getElementById('sessionActionAlert');
    el.className = `alert alert-${type} py-2 px-3 mb-3`;
    el.textContent = msg;
    el.classList.remove('d-none');
}
</script>
@endsection