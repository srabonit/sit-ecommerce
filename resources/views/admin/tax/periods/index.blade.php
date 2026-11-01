@extends('layouts.admin')

@section('title', 'VAT Periods & Monthly Closing')

@section('admin_content')
<div class="container-fluid px-2 px-md-3 py-2 vat-periods-wrapper">
    <!-- Top Header & Actions -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mb-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark fs-5 fs-md-4 d-flex align-items-center gap-2">
                <i class="fas fa-calendar-check text-primary"></i> VAT Periods &amp; Closing
            </h4>
            <p class="text-secondary small mb-0">Review monthly tax reconciliations, archive audit snapshots, and manage period locking.</p>
        </div>

        <div class="d-flex align-items-center gap-2 w-100 w-sm-auto justify-content-start justify-content-sm-end">
            <a href="{{ route('admin.tax.settings.index') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
                <i class="fas fa-sliders"></i> <span>VAT Settings</span>
            </a>
            <button type="button" class="btn btn-sm btn-primary fw-semibold d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#closePeriodModal">
                <i class="fas fa-lock"></i> <span>Close / Lock</span>
            </button>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border border-success-subtle shadow-sm mb-3 py-2 px-3" role="alert">
            <div class="d-flex align-items-center small">
                <i class="fas fa-circle-check me-2 fs-6"></i>
                <div class="fw-medium">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border border-danger-subtle shadow-sm mb-3 py-2 px-3" role="alert">
            <div class="d-flex align-items-center small">
                <i class="fas fa-circle-exclamation me-2 fs-6"></i>
                <div class="fw-medium">{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Metric Highlights Strip -->
    <div class="row g-2 mb-3">
        <!-- 1. Current Net VAT -->
        <div class="col-6 col-xl-3">
            <div class="card border border-light-subtle shadow-sm bg-white h-100">
                <div class="card-body p-2 p-md-3 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                        <span class="text-secondary small fw-semibold text-uppercase text-truncate" style="font-size: 11px; letter-spacing: 0.5px;">Current Net VAT</span>
                        <div class="p-2 rounded-1 bg-primary-subtle text-primary flex-shrink-0">
                            <i class="fas fa-chart-line fs-5"></i>
                        </div>
                    </div>
                    <div class="fw-bold text-primary metric-value font-monospace text-truncate">
                        ৳{{ number_format($currentReconciliation['output_vat']['net_vat'] ?? 0, 2) }}
                    </div>
                    <div class="text-secondary small mt-2 pt-1 border-top text-truncate" style="font-size: 11px;">
                        Active: <strong class="text-dark">{{ $currentReconciliation['period']['month_name'] ?? 'This Month' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Total Closed VAT -->
        <div class="col-6 col-xl-3">
            <div class="card border border-light-subtle shadow-sm bg-white h-100">
                <div class="card-body p-2 p-md-3 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                        <span class="text-secondary small fw-semibold text-uppercase text-truncate" style="font-size: 11px; letter-spacing: 0.5px;">Closed VAT Total</span>
                        <div class="p-2 rounded-1 bg-success-subtle text-success flex-shrink-0">
                            <i class="fas fa-shield-halved fs-5"></i>
                        </div>
                    </div>
                    <div class="fw-bold text-success metric-value font-monospace text-truncate">
                        ৳{{ number_format($allTimeStats->total_net_liability ?? 0, 2) }}
                    </div>
                    <div class="text-secondary small mt-2 pt-1 border-top text-truncate" style="font-size: 11px;">
                        Locked: <strong class="text-dark">{{ $allTimeStats->total_closed_periods ?? 0 }}</strong> periods
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Treasury Deposited -->
        <div class="col-6 col-xl-3">
            <div class="card border border-light-subtle shadow-sm bg-white h-100">
                <div class="card-body p-2 p-md-3 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                        <span class="text-secondary small fw-semibold text-uppercase text-truncate" style="font-size: 11px; letter-spacing: 0.5px;">Treasury Deposited</span>
                        <div class="p-2 rounded-1 bg-info-subtle text-info flex-shrink-0">
                            <i class="fas fa-building-columns fs-5"></i>
                        </div>
                    </div>
                    <div class="fw-bold text-dark metric-value font-monospace text-truncate">
                        ৳{{ number_format($allTimeStats->total_treasury_deposited ?? 0, 2) }}
                    </div>
                    <div class="text-secondary small mt-2 pt-1 border-top text-truncate" style="font-size: 11px;">
                        Verified Treasury Challans
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Current Sales Base -->
        <div class="col-6 col-xl-3">
            <div class="card border border-light-subtle shadow-sm bg-white h-100">
                <div class="card-body p-2 p-md-3 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                        <span class="text-secondary small fw-semibold text-uppercase text-truncate" style="font-size: 11px; letter-spacing: 0.5px;">Current Sales Base</span>
                        <div class="p-2 rounded-1 bg-secondary-subtle text-secondary flex-shrink-0">
                            <i class="fas fa-receipt fs-5"></i>
                        </div>
                    </div>
                    <div class="fw-bold text-secondary metric-value font-monospace text-truncate">
                        ৳{{ number_format($currentReconciliation['sales']['taxable_base'] ?? 0, 2) }}
                    </div>
                    <div class="text-secondary small mt-2 pt-1 border-top text-truncate" style="font-size: 11px;">
                        {{ number_format($currentReconciliation['sales']['total_orders'] ?? 0) }} delivered orders
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Panel -->
    <div class="card border border-light-subtle shadow-sm bg-white mb-3">
        <div class="card-body p-2 p-md-3">
            <form action="{{ route('admin.tax.periods.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-secondary"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search month, period key, or remarks..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="locked" {{ request('status') === 'locked' ? 'selected' : '' }}>Locked / Closed</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    </select>
                </div>

                <div class="col-6 col-md-4 d-flex align-items-center gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1">
                        <i class="fas fa-filter"></i> <span>Filter</span>
                    </button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.tax.periods.index') }}" class="btn btn-sm btn-outline-secondary" title="Clear Filters">
                            <i class="fas fa-redo"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Periods Data Table -->
    <div class="card border border-light-subtle shadow-sm bg-white mb-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap" style="font-size: 12.5px;">
                <thead class="table-light text-secondary">
                    <tr>
                        <th class="ps-3 py-2">Period Key</th>
                        <th class="py-2">Period / Date Range</th>
                        <th class="text-end py-2">Taxable Sales</th>
                        <th class="text-end py-2">VAT Collected</th>
                        <th class="text-end py-2">VAT Refunded</th>
                        <th class="text-end py-2">Net VAT</th>
                        <th class="text-end py-2">Treasury Paid</th>
                        <th class="text-center py-2">Status</th>
                        <th class="pe-3 text-center py-2" style="width: 110px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periods as $period)
                        <tr>
                            <td class="ps-3 fw-bold font-monospace text-primary">
                                {{ $period->period_key }}
                            </td>
                            <td>
                                <strong class="text-dark d-block text-truncate" style="max-width: 180px;">{{ $period->period_name }}</strong>
                                <span class="text-secondary font-monospace" style="font-size: 11px;">
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
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 font-monospace" style="font-size: 10.5px;">
                                        <i class="fas fa-lock me-1"></i> Locked
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 font-monospace" style="font-size: 10.5px;">
                                        <i class="fas fa-lock-open me-1"></i> Open
                                    </span>
                                @endif
                            </td>
                            <td class="pe-3 text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary border-light-subtle py-1 px-2" title="View Snapshot" onclick="viewPeriodSnapshot({{ $period->id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    @if($period->is_locked && auth()->user() && method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin())
                                        <form action="{{ route('admin.tax.periods.unlock', $period->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Warning: Are you sure you want to unlock this VAT period?');">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-warning border-light-subtle py-1 px-2" title="Reopen Period">
                                                <i class="fas fa-unlock"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-secondary">
                                <i class="fas fa-inbox fs-2 opacity-50 mb-2 d-block"></i>
                                <h6 class="fw-semibold text-dark">No VAT Periods Found</h6>
                                <p class="small mb-0">Try modifying your filter parameters or close an active monthly period.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($periods->hasPages())
            <div class="card-footer bg-white border-0 py-2 px-3 d-flex justify-content-center justify-content-md-end">
                {{ $periods->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Close Period Modal -->
<div class="modal fade" id="closePeriodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border border-light-subtle shadow">
            <div class="modal-header border-0 pb-1 pt-3 px-3">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fas fa-lock text-primary"></i> Close VAT Period
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.tax.periods.close') }}" method="POST">
                @csrf
                <div class="modal-body p-3">
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-dark mb-1">Target Period (YYYY-MM) <span class="text-danger">*</span></label>
                        <input type="month" name="period_key" class="form-control form-control-sm font-monospace" value="{{ old('period_key', $prevMonthKey) }}" required>
                        <span class="text-secondary d-block mt-1" style="font-size: 11px;">Previous month is selected by default.</span>
                    </div>

                    <div class="mb-1">
                        <label class="form-label small fw-semibold text-dark mb-1">Remarks / Note</label>
                        <textarea name="remarks" class="form-control form-control-sm" rows="3" placeholder="Verified by audit and approved for period locking."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-3 pb-3">
                    <button type="button" class="btn btn-sm btn-light border px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3 fw-semibold">Confirm &amp; Lock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Period Details / Snapshot Modal -->
<div class="modal fade" id="periodDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border border-light-subtle shadow">
            <div class="modal-header border-bottom py-2 px-3">
                <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center gap-2" id="detailModalTitle">
                    <i class="fas fa-file-invoice-dollar text-primary"></i> VAT Closing Snapshot
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3" id="periodDetailBody">
                <div class="text-center py-4" id="snapshotSpinner">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="ms-2 small text-secondary">Loading snapshot...</span>
                </div>

                <div id="snapshotContent" class="d-none">
                    <div class="bg-light border border-light-subtle rounded-1 p-2 mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-secondary small">Period:</span>
                            <strong class="text-dark font-monospace" id="snapPeriodName"></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-secondary small">Locked By:</span>
                            <span class="fw-semibold text-dark" id="snapLockedBy"></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-secondary small">Locked At:</span>
                            <span class="font-monospace text-secondary" id="snapLockedAt"></span>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark small mb-2 border-bottom pb-1">Sales &amp; Output VAT</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-3 font-monospace text-nowrap" style="font-size: 11px;">
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

                    <h6 class="fw-bold text-dark small mb-2 border-bottom pb-1">Treasury Settlement</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-2 font-monospace text-nowrap" style="font-size: 11px;">
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

                    <div class="mt-2 text-secondary small" style="font-size: 11px;" id="snapRemarksWrap">
                        <strong>Remarks:</strong> <span id="snapRemarks"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-3 pb-3">
                <button type="button" class="btn btn-sm btn-light border px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Strict 5px Radius Rule */
    .vat-periods-wrapper .card,
    .vat-periods-wrapper .btn,
    .vat-periods-wrapper .form-control,
    .vat-periods-wrapper .form-select,
    .vat-periods-wrapper .input-group-text,
    .vat-periods-wrapper .badge,
    .vat-periods-wrapper .alert,
    .vat-periods-wrapper .rounded-1,
    .modal-content,
    .modal-content .btn,
    .modal-content .form-control,
    .modal-content .rounded-1 {
        border-radius: 5px !important;
    }

    /* Button Group Radius */
    .vat-periods-wrapper .btn-group .btn:first-child {
        border-top-left-radius: 5px !important;
        border-bottom-left-radius: 5px !important;
    }

    .vat-periods-wrapper .btn-group .btn:last-child {
        border-top-right-radius: 5px !important;
        border-bottom-right-radius: 5px !important;
    }

    /* Fluid Typographic Metric Value */
    .vat-periods-wrapper .metric-value {
        font-size: clamp(1.15rem, 1.8vw, 1.45rem);
        line-height: 1.2;
    }

    /* Responsive Mobile Optimization */
    @media (max-width: 575.98px) {
        .vat-periods-wrapper {
            padding-left: 6px !important;
            padding-right: 6px !important;
        }

        .vat-periods-wrapper .metric-value {
            font-size: 1.15rem;
        }

        #closePeriodModal .modal-dialog,
        #periodDetailsModal .modal-dialog {
            margin: 0.5rem;
        }

        #periodDetailsModal .modal-body {
            padding: 0.75rem !important;
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