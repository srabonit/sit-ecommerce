@extends('layouts.admin')

@section('title', 'VAT Periods & Monthly Closing')

@section('admin_content')
<div class="container-fluid px-2 px-md-3 py-3 vat-periods-wrapper">

 <!-- Page Header -->
<div class="vat-page-header d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
    <div class="d-flex align-items-center gap-3 min-w-0">
        <span class="vat-header-icon d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #e0f2fe; color: #0284c7; border-radius: 8px; font-size: 18px;">
            <i class="fas fa-calendar-check"></i>
        </span>
        <div class="min-w-0">
            <h4 class="fw-bold mb-1 text-dark text-truncate" style="font-size: 18px;">
                VAT Periods & Closing
            </h4>
            <p class="text-secondary small mb-0 text-truncate">
                Manage Monthly Tax Audits & Closing
            </p>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap flex-shrink-0">
        <a href="{{ route('admin.tax.settings.index') }}"
           class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center justify-content-center" style="height: 34px; gap: 6px;">
            <i class="fas fa-sliders" style="font-size: 12px;"></i>
            <span>VAT Settings</span>
        </a>

        <button type="button"
                class="btn btn-primary btn-sm px-3 fw-semibold d-inline-flex align-items-center justify-content-center"
                data-bs-toggle="modal"
                data-bs-target="#closePeriodModal" style="height: 34px; gap: 6px;">
            <i class="fas fa-lock" style="font-size: 12px;"></i>
            <span>Close / Lock</span>
        </button>
    </div>
</div>
    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-circle-check"></i>
                <div class="fw-medium">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-circle-exclamation"></i>
                <div class="fw-medium">{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Metric Cards -->
    <div class="row g-3 mb-4">

        <!-- Current Net VAT -->
        <div class="col-6 col-xl-3">
            <div class="card vat-metric-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                        <span class="vat-metric-label">Current Net VAT</span>
                        <div class="vat-metric-icon vat-icon-primary">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>

                    <div class="vat-metric-value text-primary font-monospace">
                        ৳{{ number_format($currentReconciliation['output_vat']['net_vat'] ?? 0, 2) }}
                    </div>

                    <div class="vat-metric-footer">
                        Active:
                        <strong class="text-dark">
                            {{ $currentReconciliation['period']['month_name'] ?? 'This Month' }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Closed VAT Total -->
        <div class="col-6 col-xl-3">
            <div class="card vat-metric-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                        <span class="vat-metric-label">Closed VAT Total</span>
                        <div class="vat-metric-icon vat-icon-success">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                    </div>

                    <div class="vat-metric-value text-success font-monospace">
                        ৳{{ number_format($allTimeStats->total_net_liability ?? 0, 2) }}
                    </div>

                    <div class="vat-metric-footer">
                        Locked:
                        <strong class="text-dark">
                            {{ $allTimeStats->total_closed_periods ?? 0 }}
                        </strong>
                        periods
                    </div>
                </div>
            </div>
        </div>

        <!-- Treasury Deposited -->
        <div class="col-6 col-xl-3">
            <div class="card vat-metric-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                        <span class="vat-metric-label">Treasury Deposited</span>
                        <div class="vat-metric-icon vat-icon-info">
                            <i class="fas fa-building-columns"></i>
                        </div>
                    </div>

                    <div class="vat-metric-value text-dark font-monospace">
                        ৳{{ number_format($allTimeStats->total_treasury_deposited ?? 0, 2) }}
                    </div>

                    <div class="vat-metric-footer">
                        Verified Treasury Challans
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Sales Base -->
        <div class="col-6 col-xl-3">
            <div class="card vat-metric-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                        <span class="vat-metric-label">Current Sales Base</span>
                        <div class="vat-metric-icon vat-icon-secondary">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>

                    <div class="vat-metric-value text-secondary font-monospace">
                        ৳{{ number_format($currentReconciliation['sales']['taxable_base'] ?? 0, 2) }}
                    </div>

                    <div class="vat-metric-footer">
                        {{ number_format($currentReconciliation['sales']['total_orders'] ?? 0) }} delivered orders
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Search & Filter Panel -->
    <div class="card vat-panel-card mb-4">
        <div class="card-header vat-panel-header">
            <div class="d-flex align-items-center gap-2">
                <span class="vat-section-icon">
                    <i class="fas fa-filter"></i>
                </span>
                <h6 class="mb-0 fw-semibold">Search &amp; Filters</h6>
            </div>
        </div>

        <div class="card-body p-3">
            <form action="{{ route('admin.tax.periods.index') }}" method="GET" class="row g-3 align-items-end">

                <div class="col-12 col-md-5">
                    <label class="form-label vat-form-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Search month, period key, or remarks..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <label class="form-label vat-form-label">Period Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="locked" {{ request('status') === 'locked' ? 'selected' : '' }}>Locked / Closed</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    </select>
                </div>

                <div class="col-6 col-md-4">
                    <label class="form-label vat-form-label d-none d-md-block">&nbsp;</label>
                    <div class="d-flex align-items-center gap-2">
                        <button type="submit"
                                class="btn btn-primary flex-grow-1 d-inline-flex align-items-center justify-content-center gap-2">
                            <i class="fas fa-filter"></i>
                            <span>Apply Filters</span>
                        </button>

                        @if(request()->hasAny(['search', 'status']))
                            <a href="{{ route('admin.tax.periods.index') }}"
                               class="btn btn-outline-secondary vat-reset-btn"
                               title="Clear Filters"
                               aria-label="Clear Filters">
                                <i class="fas fa-redo"></i>
                            </a>
                        @endif
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- Periods Data Table -->
    <div class="card vat-panel-card mb-4">

        <div class="card-header vat-panel-header">
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3 min-w-0">
            <span class="vat-section-icon d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px; background: #e0f2fe; color: #0284c7; border-radius: 8px; font-size: 16px;">
                <i class="fas fa-calendar-alt"></i>
            </span>
            <div class="min-w-0">
                <h6 class="mb-1 fw-semibold text-dark text-truncate" style="font-size: 15px;">VAT Period History</h6>
                <small class="text-muted d-block text-truncate">Monthly reconciliation and closing records</small>
            </div>
        </div>
    </div>
</div>

        <div class="table-responsive vat-table-responsive">
            <table class="table table-hover align-middle mb-0 vat-periods-table">
                <thead>
                    <tr>
                        <th class="ps-3">Period Key</th>
                        <th>Period / Date Range</th>
                        <th class="text-end">Taxable Sales</th>
                        <th class="text-end">VAT Collected</th>
                        <th class="text-end">VAT Refunded</th>
                        <th class="text-end">Net VAT</th>
                        <th class="text-end">Treasury Paid</th>
                        <th class="text-center">Status</th>
                        <th class="pe-3 text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($periods as $period)
                        <tr>
                            <td class="ps-3">
                                <span class="vat-period-key font-monospace">
                                    {{ $period->period_key }}
                                </span>
                            </td>

                            <td>
                                <strong class="text-dark d-block vat-period-name">
                                    {{ $period->period_name }}
                                </strong>
                                <span class="text-secondary font-monospace vat-date-range">
                                    {{ date('d M Y', strtotime($period->start_date)) }} — {{ date('d M Y', strtotime($period->end_date)) }}
                                </span>
                            </td>

                            <td class="text-end font-monospace">
                                ৳{{ number_format($period->taxable_amount, 2) }}
                            </td>

                            <td class="text-end font-monospace text-dark">
                                ৳{{ number_format($period->tax_collected, 2) }}
                            </td>

                            <td class="text-end font-monospace text-danger">
                                @if((float)$period->tax_refunded > 0)
                                    -৳{{ number_format($period->tax_refunded, 2) }}
                                @else
                                    ৳0.00
                                @endif
                            </td>

                            <td class="text-end font-monospace fw-bold text-dark">
                                ৳{{ number_format($period->net_tax_liability, 2) }}
                            </td>

                            <td class="text-end font-monospace text-success fw-semibold">
                                ৳{{ number_format($period->treasury_paid, 2) }}
                            </td>

                            <td class="text-center">
                                @if($period->is_locked)
                                    <span class="badge vat-status-badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="fas fa-lock me-1"></i> Locked
                                    </span>
                                @else
                                    <span class="badge vat-status-badge bg-warning-subtle text-warning border border-warning-subtle">
                                        <i class="fas fa-lock-open me-1"></i> Open
                                    </span>
                                @endif
                            </td>

                            <td class="pe-3 text-center">
                                <div class="btn-group btn-group-sm vat-action-group">
                                    <button type="button"
                                            class="btn btn-outline-primary"
                                            title="View Snapshot"
                                            onclick="viewPeriodSnapshot({{ $period->id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    @if($period->is_locked && auth()->user() && method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin())
                                        <form action="{{ route('admin.tax.periods.unlock', $period->id) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Warning: Are you sure you want to unlock this VAT period?');">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-outline-warning"
                                                    title="Reopen Period">
                                                <i class="fas fa-unlock"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="vat-empty-state">
                                    <div class="vat-empty-icon">
                                        <i class="fas fa-inbox"></i>
                                    </div>
                                    <h6 class="fw-semibold text-dark mb-1">No VAT Periods Found</h6>
                                    <p class="small text-secondary mb-0">
                                        Try changing your filters or close an active monthly period.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($periods->hasPages())
            <div class="card-footer vat-pagination-footer">
                {{ $periods->links() }}
            </div>
        @endif

    </div>
</div>

<!-- Close Period Modal -->
<div class="modal fade" id="closePeriodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content vat-modal-content">

            <div class="modal-header">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <span class="vat-modal-icon vat-icon-primary">
                        <i class="fas fa-lock"></i>
                    </span>
                    Close VAT Period
                </h6>

                <button type="button"
                        class="btn-close shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <form action="{{ route('admin.tax.periods.close') }}" method="POST">
                @csrf

                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label vat-form-label">
                            Target Period (YYYY-MM)
                            <span class="text-danger">*</span>
                        </label>

                        <input type="month"
                               name="period_key"
                               class="form-control font-monospace"
                               value="{{ old('period_key', $prevMonthKey) }}"
                               required>

                        <span class="text-secondary d-block mt-2 vat-helper-text">
                            Previous month is selected by default.
                        </span>
                    </div>

                    <div class="mb-1">
                        <label class="form-label vat-form-label">Remarks / Note</label>

                        <textarea name="remarks"
                                  class="form-control"
                                  rows="3"
                                  placeholder="Verified by audit and approved for period locking."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-light border"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary fw-semibold">
                        <i class="fas fa-lock me-1"></i>
                        Confirm &amp; Lock
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- Period Details / Snapshot Modal -->
<div class="modal fade" id="periodDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content vat-modal-content">

            <div class="modal-header">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" id="detailModalTitle">
                    <span class="vat-modal-icon vat-icon-primary">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </span>
                    VAT Closing Snapshot
                </h6>

                <button type="button"
                        class="btn-close shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <div class="modal-body p-3" id="periodDetailBody">

                <div class="text-center py-4" id="snapshotSpinner">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="ms-2 small text-secondary">Loading snapshot...</span>
                </div>

                <div id="snapshotContent" class="d-none">

                    <!-- Snapshot Metadata -->
                    <div class="vat-snapshot-meta mb-4">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                            <span class="text-secondary small">Period</span>
                            <strong class="text-dark font-monospace text-end" id="snapPeriodName"></strong>
                        </div>

                        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                            <span class="text-secondary small">Locked By</span>
                            <span class="fw-semibold text-dark text-end" id="snapLockedBy"></span>
                        </div>

                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <span class="text-secondary small">Locked At</span>
                            <span class="font-monospace text-secondary text-end" id="snapLockedAt"></span>
                        </div>
                    </div>

                    <!-- Sales & Output VAT -->
                    <div class="vat-snapshot-section-title">
                        <i class="fas fa-chart-line me-2"></i>
                        Sales &amp; Output VAT
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-bordered mb-0 vat-snapshot-table">
                            <tbody>
                                <tr>
                                    <td class="text-secondary">Delivered Orders</td>
                                    <td class="text-end fw-bold" id="snapOrdersCount"></td>
                                </tr>

                                <tr>
                                    <td class="text-secondary">Gross Sales</td>
                                    <td class="text-end" id="snapGrossSales"></td>
                                </tr>

                                <tr>
                                    <td class="text-secondary">Taxable Sales Base</td>
                                    <td class="text-end fw-semibold" id="snapTaxableBase"></td>
                                </tr>

                                <tr>
                                    <td class="text-secondary">Gross VAT Collected</td>
                                    <td class="text-end" id="snapGrossVat"></td>
                                </tr>

                                <tr>
                                    <td class="text-secondary">Adjusted / Reversed VAT</td>
                                    <td class="text-end text-danger" id="snapReversedVat"></td>
                                </tr>

                                <tr class="table-light">
                                    <td class="fw-bold text-dark">Net VAT Liability</td>
                                    <td class="text-end fw-bold text-primary" id="snapNetVat"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Treasury Settlement -->
                    <div class="vat-snapshot-section-title">
                        <i class="fas fa-building-columns me-2"></i>
                        Treasury Settlement
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0 vat-snapshot-table">
                            <tbody>
                                <tr>
                                    <td class="text-secondary">Treasury Deposited</td>
                                    <td class="text-end text-success fw-semibold" id="snapTreasuryDeposited"></td>
                                </tr>

                                <tr>
                                    <td class="text-secondary">Net Balance Due</td>
                                    <td class="text-end text-danger fw-bold" id="snapBalanceDue"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="vat-snapshot-remarks mt-3 text-secondary small" id="snapRemarksWrap">
                        <strong>Remarks:</strong>
                        <span id="snapRemarks"></span>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-light border"
                        data-bs-dismiss="modal">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>

<style>
    /* =========================================================
       VAT PERIODS — KAIADMIN CLASSIC / WHITE STYLE
       Page-scoped styling. No Bootstrap imports.
    ========================================================= */

    .vat-periods-wrapper {
        --vat-primary: var(--bs-primary, #1572e8);
        --vat-success: var(--bs-success, #31ce36);
        --vat-info: var(--bs-info, #48abf7);
        --vat-warning: var(--bs-warning, #ffad46);
        --vat-danger: var(--bs-danger, #f25961);
        --vat-heading: #2a2f5b;
        --vat-text: #575962;
        --vat-muted: #8d9498;
        --vat-border: #ebedf2;
        --vat-surface: #ffffff;
        --vat-soft-bg: #f8f9fc;

        color: var(--vat-text);
        min-width: 0;
    }

    .vat-periods-wrapper *,
    #closePeriodModal *,
    #periodDetailsModal * {
        box-sizing: border-box;
    }

    .vat-periods-wrapper .card,
    .vat-periods-wrapper .btn,
    .vat-periods-wrapper .form-control,
    .vat-periods-wrapper .form-select,
    .vat-periods-wrapper .input-group-text,
    .vat-periods-wrapper .badge,
    #closePeriodModal .modal-content,
    #periodDetailsModal .modal-content,
    #closePeriodModal .btn,
    #periodDetailsModal .btn,
    #closePeriodModal .form-control,
    #periodDetailsModal .form-control {
        border-radius: 5px;
    }

    /* Page Header */
    .vat-page-header {
        min-width: 0;
    }

    .vat-page-header h4 {
        color: var(--vat-heading) !important;
        font-size: clamp(1.1rem, 1.6vw, 1.35rem);
        line-height: 1.35;
    }

    .vat-page-header p {
        line-height: 1.6;
    }

    .vat-header-icon {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: var(--vat-primary);
        background: rgba(21, 114, 232, 0.09);
        border-radius: 5px;
        font-size: 16px;
    }

    .vat-page-header .btn {
        min-height: 38px;
        padding: 8px 13px;
        font-size: 13px;
        font-weight: 600;
    }

    /* Metric Cards */
    .vat-metric-card {
        border: 1px solid var(--vat-border);
        background: var(--vat-surface);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.035);
        transition: box-shadow 0.2s ease, transform 0.2s ease;
        min-width: 0;
    }

    .vat-metric-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.055);
        transform: translateY(-1px);
    }

    .vat-metric-card .card-body {
        padding: 17px;
        min-width: 0;
    }

    .vat-metric-label {
        display: block;
        color: var(--vat-muted);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.45px;
        line-height: 1.5;
        text-transform: uppercase;
    }

    .vat-metric-icon {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 5px;
        font-size: 17px;
    }

    .vat-icon-primary {
        background: rgba(21, 114, 232, 0.10);
        color: var(--vat-primary);
    }

    .vat-icon-success {
        background: rgba(49, 206, 54, 0.11);
        color: var(--vat-success);
    }

    .vat-icon-info {
        background: rgba(72, 171, 247, 0.12);
        color: var(--vat-info);
    }

    .vat-icon-secondary {
        background: rgba(108, 117, 125, 0.10);
        color: #6c757d;
    }

    .vat-metric-value {
        font-size: clamp(1.05rem, 1.55vw, 1.4rem);
        font-weight: 700;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }

    .vat-metric-footer {
        border-top: 1px solid var(--vat-border);
        margin-top: 13px;
        padding-top: 10px;
        color: var(--vat-muted);
        font-size: 11px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    /* Panel Cards */
    .vat-panel-card {
        border: 1px solid var(--vat-border);
        background: var(--vat-surface);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.035);
        overflow: hidden;
    }

    .vat-panel-header {
        background: #fff;
        border-bottom: 1px solid var(--vat-border);
        padding: 14px 17px;
    }

    .vat-panel-header h6 {
        color: var(--vat-heading);
        font-size: 13px;
    }

    .vat-panel-header small {
        font-size: 11px;
    }

    .vat-section-icon {
        width: 31px;
        height: 31px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: rgba(21, 114, 232, 0.08);
        color: var(--vat-primary);
        border-radius: 5px;
        font-size: 13px;
    }

    .vat-table-info {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border: 1px solid var(--vat-border);
        border-radius: 5px;
        background: var(--vat-soft-bg);
        color: var(--vat-muted);
        font-size: 11px;
        font-weight: 600;
    }

    /* Form Elements */
    .vat-periods-wrapper .form-label,
    #closePeriodModal .form-label {
        color: var(--vat-heading);
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 7px;
    }

    .vat-periods-wrapper .form-control,
    .vat-periods-wrapper .form-select,
    #closePeriodModal .form-control {
        min-height: 39px;
        border: 1px solid #e3e6ed;
        background-color: #fff;
        color: var(--vat-text);
        font-size: 13px;
        box-shadow: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .vat-periods-wrapper .input-group-text {
        min-height: 39px;
        border: 1px solid #e3e6ed;
        background: var(--vat-soft-bg);
        color: var(--vat-muted);
        padding-left: 12px;
        padding-right: 12px;
    }

    .vat-periods-wrapper .form-control:focus,
    .vat-periods-wrapper .form-select:focus,
    #closePeriodModal .form-control:focus {
        border-color: rgba(21, 114, 232, 0.55);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, 0.09);
    }

    .vat-periods-wrapper .form-control::placeholder,
    #closePeriodModal .form-control::placeholder {
        color: #a5aab2;
        opacity: 1;
    }

    .vat-reset-btn {
        width: 40px;
        min-width: 40px;
        height: 39px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* Buttons */
    .vat-periods-wrapper .btn,
    #closePeriodModal .btn,
    #periodDetailsModal .btn {
        min-height: 36px;
        padding: 7px 12px;
        font-size: 12px;
        font-weight: 600;
        transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .vat-periods-wrapper .btn-primary,
    #closePeriodModal .btn-primary {
        background-color: var(--vat-primary);
        border-color: var(--vat-primary);
    }

    .vat-periods-wrapper .btn-primary:hover,
    #closePeriodModal .btn-primary:hover {
        filter: brightness(0.95);
    }

    /* Data Table */
    .vat-table-responsive {
        width: 100%;
        overflow-x: auto;
        overscroll-behavior-x: contain;
        scrollbar-width: thin;
        scrollbar-color: #cbd0d8 #f5f6f8;
    }

    .vat-periods-table {
        width: 100%;
        min-width: 1050px;
        color: var(--vat-text);
        font-size: 12px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .vat-periods-table thead th {
        background: #f8f9fc;
        color: #6b7280;
        border-bottom: 1px solid var(--vat-border);
        border-top: 0;
        padding: 12px 10px;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.35px;
        text-transform: uppercase;
        white-space: nowrap;
        vertical-align: middle;
    }

    .vat-periods-table tbody td {
        padding: 12px 10px;
        border-bottom: 1px solid #f0f1f4;
        vertical-align: middle;
        white-space: nowrap;
    }

    .vat-periods-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .vat-periods-table tbody tr:hover td {
        background-color: #fafbfe;
    }

    .vat-period-key {
        display: inline-block;
        padding: 5px 7px;
        border-radius: 4px;
        background: rgba(21, 114, 232, 0.07);
        color: var(--vat-primary);
        font-size: 11px;
        font-weight: 700;
    }

    .vat-period-name {
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 12px;
    }

    .vat-date-range {
        display: inline-block;
        margin-top: 3px;
        font-size: 10.5px;
    }

    .vat-status-badge {
        padding: 6px 9px;
        font-size: 10.5px;
        font-weight: 600;
        white-space: nowrap;
    }

    .vat-action-group {
        gap: 4px;
    }

    .vat-action-group .btn {
        width: 31px;
        height: 31px;
        min-height: 31px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px !important;
    }

    /* Empty State */
    .vat-empty-state {
        padding: 12px 10px;
    }

    .vat-empty-icon {
        width: 54px;
        height: 54px;
        margin: 0 auto 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #f3f6fb;
        color: #9ba5b5;
        font-size: 23px;
    }

    .vat-empty-state h6 {
        font-size: 13px;
    }

    .vat-empty-state p {
        font-size: 12px;
    }

    /* Pagination */
    .vat-pagination-footer {
        display: flex;
        justify-content: flex-end;
        padding: 12px 16px;
        background: #fff;
        border-top: 1px solid var(--vat-border);
    }

    .vat-pagination-footer nav {
        max-width: 100%;
        overflow-x: auto;
    }

    .vat-pagination-footer .pagination {
        margin-bottom: 0;
        flex-wrap: wrap;
        gap: 3px;
    }

    .vat-pagination-footer .page-link {
        border-radius: 4px;
        font-size: 12px;
    }

    /* Modals */
    #closePeriodModal .modal-dialog,
    #periodDetailsModal .modal-dialog {
        width: calc(100% - 24px);
        max-width: 500px;
    }

    #closePeriodModal .modal-content,
    #periodDetailsModal .modal-content {
        border: 1px solid var(--vat-border);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.13);
        overflow: hidden;
    }

    .vat-modal-content .modal-header {
        padding: 15px 18px;
        background: #fff;
        border-bottom: 1px solid var(--vat-border);
    }

    .vat-modal-content .modal-title {
        color: var(--vat-heading);
        font-size: 14px;
    }

    .vat-modal-content .modal-footer {
        padding: 12px 18px;
        border-top: 1px solid var(--vat-border);
        background: #fff;
    }

    .vat-modal-icon {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        font-size: 13px;
    }

    .vat-helper-text {
        font-size: 11px;
        line-height: 1.5;
    }

    /* Snapshot Details */
    .vat-snapshot-meta {
        padding: 13px;
        background: #f8f9fc;
        border: 1px solid var(--vat-border);
        border-radius: 5px;
    }

    .vat-snapshot-meta > div:last-child {
        margin-bottom: 0 !important;
    }

    .vat-snapshot-section-title {
        display: flex;
        align-items: center;
        padding-bottom: 9px;
        margin-bottom: 10px;
        border-bottom: 1px solid var(--vat-border);
        color: var(--vat-heading);
        font-size: 12px;
        font-weight: 700;
    }

    .vat-snapshot-table {
        font-size: 11.5px;
    }

    .vat-snapshot-table td {
        padding: 9px 10px;
        vertical-align: middle;
    }

    .vat-snapshot-table td:first-child {
        width: 60%;
    }

    .vat-snapshot-remarks {
        padding: 11px;
        background: #f8f9fc;
        border: 1px solid var(--vat-border);
        border-radius: 5px;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    /* Alerts */
    .vat-periods-wrapper .alert {
        border-radius: 5px;
        padding: 12px 15px;
        font-size: 12px;
    }

    /* Tablet */
    @media (max-width: 991.98px) {
        .vat-metric-card .card-body {
            padding: 14px;
        }

        .vat-periods-table {
            min-width: 1050px;
        }
    }

    /* Mobile */
    @media (max-width: 575.98px) {
        .vat-periods-wrapper {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }

        .vat-page-header {
            align-items: flex-start !important;
        }

        .vat-page-header > div:last-child {
            display: flex;
            width: 100%;
        }

        .vat-page-header > div:last-child .btn {
            flex: 1 1 auto;
            min-width: 0;
            padding-left: 9px;
            padding-right: 9px;
            font-size: 11.5px;
        }

        .vat-metric-card .card-body {
            padding: 12px;
        }

        .vat-metric-label {
            font-size: 9.5px;
            letter-spacing: 0.2px;
        }

        .vat-metric-icon {
            width: 31px;
            height: 31px;
            font-size: 14px;
        }

        .vat-metric-value {
            font-size: clamp(0.9rem, 4vw, 1.1rem);
        }

        .vat-metric-footer {
            font-size: 10px;
            margin-top: 10px;
            padding-top: 8px;
        }

        .vat-panel-header {
            padding: 12px;
        }

        .vat-panel-card > .card-body {
            padding: 12px !important;
        }

        .vat-periods-wrapper .form-control,
        .vat-periods-wrapper .form-select {
            font-size: 12px;
        }

        .vat-reset-btn {
            min-width: 38px;
            width: 38px;
        }

        .vat-pagination-footer {
            justify-content: center;
            padding: 10px;
        }

        #closePeriodModal .modal-dialog,
        #periodDetailsModal .modal-dialog {
            width: auto;
            margin: 0.5rem;
        }

        .vat-modal-content .modal-header {
            padding: 13px;
        }

        .vat-modal-content .modal-body {
            padding: 12px !important;
        }

        .vat-modal-content .modal-footer {
            padding: 10px 12px;
        }

        .vat-snapshot-meta {
            padding: 10px;
        }

        .vat-snapshot-meta > div {
            gap: 10px !important;
            font-size: 11px;
        }

        .vat-snapshot-table {
            font-size: 10.5px;
        }

        .vat-snapshot-table td {
            padding: 8px;
        }
    }

    @media (max-width: 359.98px) {
        .vat-metric-card .card-body {
            padding: 9px;
        }

        .vat-metric-label {
            font-size: 9px;
        }

        .vat-metric-icon {
            width: 27px;
            height: 27px;
            font-size: 12px;
        }

        .vat-metric-value {
            font-size: 0.85rem;
        }

        .vat-page-header > div:last-child .btn {
            font-size: 10.5px;
        }
    }
</style>

<script>
function viewPeriodSnapshot(periodId) {
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('periodDetailsModal'));
    const spinner = document.getElementById('snapshotSpinner');
    const content = document.getElementById('snapshotContent');

    spinner.classList.remove('d-none');
    content.classList.add('d-none');
    modal.show();

    fetch("{{ url('admin/tax/periods') }}/" + periodId, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert(data.message || 'Error loading snapshot.');
            modal.hide();
            return;
        }

        const p = data.period;
        const snap = data.snapshot;

        document.getElementById('snapPeriodName').textContent = p.period_name + " (" + p.period_key + ")";
        document.getElementById('snapLockedBy').textContent = data.locked_by || 'System';
        document.getElementById('snapLockedAt').textContent = p.locked_at ? new Date(p.locked_at).toLocaleString() : 'N/A';

        const sales = snap.sales || {};
        const output = snap.output_vat || {};
        const treasury = snap.treasury || {};

        document.getElementById('snapOrdersCount').textContent = (sales.total_orders || 0).toLocaleString();
        document.getElementById('snapGrossSales').textContent = '৳' + Number(sales.gross_sales || p.taxable_amount || 0).toFixed(2);
        document.getElementById('snapTaxableBase').textContent = '৳' + Number(sales.taxable_base || p.taxable_amount || 0).toFixed(2);

        document.getElementById('snapGrossVat').textContent = '৳' + Number(output.gross_vat || p.tax_collected || 0).toFixed(2);
        document.getElementById('snapReversedVat').textContent = '-৳' + Number(output.reversed_vat || p.tax_refunded || 0).toFixed(2);
        document.getElementById('snapNetVat').textContent = '৳' + Number(output.net_vat || p.net_tax_liability || 0).toFixed(2);

        document.getElementById('snapTreasuryDeposited').textContent = '৳' + Number(treasury.deposited || p.treasury_paid || 0).toFixed(2);
        document.getElementById('snapBalanceDue').textContent = '৳' + Number(treasury.balance_due || 0).toFixed(2);

        document.getElementById('snapRemarks').textContent = p.remarks || 'None';

        spinner.classList.add('d-none');
        content.classList.remove('d-none');
    })
    .catch(err => {
        console.error(err);
        alert('Server connection error.');
        modal.hide();
    });
}
</script>
@endsection