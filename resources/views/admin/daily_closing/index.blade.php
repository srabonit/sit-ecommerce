
@extends('layouts.admin')

@section('title', 'Daily Cash Register & Closing History')

@section('admin_content')
<style>
    #dailyClosingHistoryRoot {
        --closing-primary: var(--bs-primary, #1572e8);
        --closing-primary-soft: #eef5ff;
        --closing-border: #e7eaf0;
        --closing-muted: #77808d;
        --closing-text: #252b36;
        --closing-surface: #ffffff;
        --closing-page: #f7f8fb;
        --closing-success: var(--bs-success, #31ce36);
        --closing-danger: var(--bs-danger, #f25961);
        --closing-warning: var(--bs-warning, #ffad46);
        --closing-info: var(--bs-info, #48abf7);
        --closing-radius: 6px;
        color: var(--closing-text);
        font-size: 13px;
        min-width: 0;
    }

    #dailyClosingHistoryRoot *,
    #closingDetailsModal * {
        box-sizing: border-box;
    }

    #dailyClosingHistoryRoot .card,
    #dailyClosingHistoryRoot .btn,
    #dailyClosingHistoryRoot .badge,
    #dailyClosingHistoryRoot .form-control,
    #dailyClosingHistoryRoot .form-select,
    #dailyClosingHistoryRoot .input-group-text,
    #dailyClosingHistoryRoot .alert,
    #closingDetailsModal .modal-content {
        border-radius: var(--closing-radius);
    }

    #dailyClosingHistoryRoot .card {
        background: var(--closing-surface);
        border: 1px solid var(--closing-border);
        box-shadow: 0 2px 8px rgba(31, 41, 55, .035);
    }

    #dailyClosingHistoryRoot .closing-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        padding: 15px 17px;
        margin-bottom: 16px;
        background: var(--closing-surface);
        border: 1px solid var(--closing-border);
        border-radius: var(--closing-radius);
        box-shadow: 0 2px 8px rgba(31, 41, 55, .025);
    }

    #dailyClosingHistoryRoot .closing-header-title {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        min-width: 0;
    }

    #dailyClosingHistoryRoot .closing-page-title {
        margin: 0;
        color: var(--closing-text);
        font-size: clamp(15px, 1.4vw, 18px);
        font-weight: 700;
        letter-spacing: -.2px;
        line-height: 1.35;
    }

    #dailyClosingHistoryRoot .closing-total-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 9px;
        color: #5d6878;
        background: #f3f5f8;
        border: 1px solid var(--closing-border);
        border-radius: 5px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    #dailyClosingHistoryRoot .closing-header-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    #dailyClosingHistoryRoot .closing-section-card {
        padding: 17px;
        margin-bottom: 16px;
    }

    #dailyClosingHistoryRoot .closing-label {
        display: block;
        margin-bottom: 6px;
        color: #515b6b;
        font-size: 12px;
        font-weight: 600;
    }

    #dailyClosingHistoryRoot .form-control,
    #dailyClosingHistoryRoot .form-select {
        min-height: 36px;
        padding-top: 6px;
        padding-bottom: 6px;
        color: var(--closing-text);
        background-color: #fff;
        border-color: #dfe4eb;
        font-size: 12px;
        box-shadow: none;
    }

    #dailyClosingHistoryRoot .form-control:focus,
    #dailyClosingHistoryRoot .form-select:focus {
        border-color: var(--closing-primary);
        box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
    }

    #dailyClosingHistoryRoot .btn-compact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 34px;
        padding: 7px 12px;
        border-radius: var(--closing-radius);
        font-size: 11.5px;
        font-weight: 600;
        line-height: 1.25;
        white-space: nowrap;
        transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease;
    }

    #dailyClosingHistoryRoot .btn-compact:focus-visible,
    #dailyClosingHistoryRoot a:focus-visible {
        outline: 2px solid rgba(21, 114, 232, .45);
        outline-offset: 2px;
    }

    #dailyClosingHistoryRoot .closing-filter-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    #dailyClosingHistoryRoot .closing-table-card {
        padding: 12px;
        overflow: hidden;
    }

    #dailyClosingHistoryRoot .closing-table-card .table-responsive {
        width: 100%;
        border: 1px solid var(--closing-border);
        border-radius: 5px;
    }

    #dailyClosingHistoryRoot .closing-table {
        min-width: 1050px;
        width: 100%;
        margin-bottom: 0;
        color: #394252;
        font-size: 12px;
        vertical-align: middle;
    }

    #dailyClosingHistoryRoot .closing-table thead th {
        padding: 11px 9px;
        color: #566173;
        background: #f6f8fb;
        border-bottom: 1px solid var(--closing-border);
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .25px;
        white-space: nowrap;
    }

    #dailyClosingHistoryRoot .closing-table tbody td {
        padding: 10px 9px;
        border-color: #edf0f4;
        white-space: nowrap;
    }

    #dailyClosingHistoryRoot .closing-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #dailyClosingHistoryRoot .closing-table tbody tr:hover > * {
        background-color: #f8faff;
    }

    #dailyClosingHistoryRoot .font-monospace,
    #dailyClosingHistoryRoot .bn-digit {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }

    #dailyClosingHistoryRoot .closing-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding: 14px 4px 2px;
    }

    #dailyClosingHistoryRoot .closing-pagination > div {
        min-width: 0;
        max-width: 100%;
    }

    #dailyClosingHistoryRoot .closing-pagination nav,
    #dailyClosingHistoryRoot .closing-pagination .pagination {
        margin-bottom: 0;
    }

    #dailyClosingHistoryRoot .closing-pagination .page-link {
        font-size: 12px;
    }

    #dailyClosingHistoryRoot #historyActionToast {
        border: 1px solid transparent;
    }

    #closingDetailsModal .modal-dialog {
        width: calc(100% - 24px);
        max-width: 520px;
    }

    #closingDetailsModal .modal-content {
        padding: 18px;
        border: 1px solid var(--closing-border);
        box-shadow: 0 12px 36px rgba(20, 30, 50, .14);
    }

    #closingDetailsModal .modal-header-title {
        margin: 0;
        color: var(--closing-text);
        font-size: 15px;
        font-weight: 700;
        line-height: 1.4;
    }

    #closingDetailsModal .table {
        margin-bottom: 8px;
    }

    #closingDetailsModal .table td {
        padding: 9px 8px;
        border-color: #edf0f4;
        font-size: 12px;
        line-height: 1.45;
        vertical-align: middle;
    }

    #closingDetailsModal .table td:first-child {
        width: 55%;
    }

    #closingDetailsModal .modal-action-row {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }

    @media (max-width: 767.98px) {
        #dailyClosingHistoryRoot {
            padding-top: 10px !important;
            font-size: 12px;
        }

        #dailyClosingHistoryRoot .closing-header {
            align-items: flex-start;
            padding: 13px;
            gap: 12px;
        }

        #dailyClosingHistoryRoot .closing-header-title {
            width: 100%;
            gap: 8px;
        }

        #dailyClosingHistoryRoot .closing-page-title {
            font-size: 15px;
        }

        #dailyClosingHistoryRoot .closing-header-actions {
            width: 100%;
        }

        #dailyClosingHistoryRoot .closing-header-actions .btn {
            flex: 1 1 auto;
        }

        #dailyClosingHistoryRoot .closing-section-card {
            padding: 13px;
        }

        #dailyClosingHistoryRoot .closing-filter-actions {
            width: 100%;
        }

        #dailyClosingHistoryRoot .closing-filter-actions .btn-primary {
            flex: 1;
        }

        #dailyClosingHistoryRoot .closing-table-card {
            padding: 9px;
        }

        #dailyClosingHistoryRoot .closing-pagination {
            align-items: flex-start;
        }

        #dailyClosingHistoryRoot .closing-pagination > div {
            width: 100%;
            overflow-x: auto;
        }

        #dailyClosingHistoryRoot .closing-pagination .pagination {
            flex-wrap: nowrap;
            width: max-content;
        }

        #closingDetailsModal .modal-content {
            padding: 14px;
        }
    }

    @media (max-width: 420px) {
        #dailyClosingHistoryRoot .closing-header-actions {
            flex-direction: column;
            align-items: stretch;
        }

        #dailyClosingHistoryRoot .closing-header-actions .btn {
            width: 100%;
        }

        #dailyClosingHistoryRoot .closing-total-badge {
            margin-left: 0;
        }

        #dailyClosingHistoryRoot .closing-filter-actions .btn {
            min-width: 0;
        }

        #dailyClosingHistoryRoot .closing-pagination {
            flex-direction: column;
        }

        #closingDetailsModal .modal-dialog {
            margin: 10px auto;
        }

        #closingDetailsModal .modal-action-row .btn {
            flex: 1 1 auto;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-3" id="dailyClosingHistoryRoot">

    <!-- Page Header -->
    <div class="closing-header">
        <div class="closing-header-title">
            <span class="badge bg-warning text-dark fw-bold px-2 py-2">
                <i class="fas fa-history me-1"></i> Register Audit Logs
            </span>

            <h5 class="closing-page-title">Daily Closing History</h5>

            <span class="closing-total-badge font-monospace">
                Total Logs: {{ $closings->total() }}
            </span>
        </div>

        <div class="closing-header-actions">
            @if(!empty($branchId))
                <a href="{{ route('admin.daily_closing.session', $branchId) }}" class="btn btn-primary btn-compact">
                    <i class="fas fa-cash-register"></i>
                    Today's Session
                </a>

                <a href="{{ route('admin.pos.terminal', $branchId) }}" class="btn btn-warning text-white btn-compact">
                    <i class="fas fa-desktop"></i>
                    POS Terminal
                </a>
            @else
                <a href="{{ route('admin.daily_closing.session') }}" class="btn btn-primary btn-compact">
                    <i class="fas fa-cash-register"></i>
                    Current Session
                </a>
            @endif
        </div>
    </div>

    <!-- Alert Message -->
    <div id="historyActionToast" class="alert d-none py-2 px-3 mb-3" style="font-size: 12px;"></div>

    <!-- Filters -->
    <div class="card closing-section-card">
        <form method="GET" action="{{ route('admin.daily_closing.index') }}" id="closingFilterForm">
            <div class="row g-3 align-items-end">

                <!-- Branch Filter -->
                <div class="col-12 col-md-3">
                    <label class="closing-label">Branch / Hub</label>
                    <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Branches</option>
                        @foreach($selectableBranches as $sb)
                            <option value="{{ $sb->id }}" {{ (string)$branchId === (string)$sb->id ? 'selected' : '' }}>
                                {{ $sb->name }} ({{ $sb->code ?? $sb->branch_code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="closing-label">Session Status</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Statuses</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                        <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="col-12 col-sm-6 col-md-4">
                    <label class="closing-label">Date Range</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="date" name="from_date" class="form-control form-control-sm font-monospace"
                               value="{{ request('from_date') }}">
                        <span class="text-muted small">&ndash;</span>
                        <input type="date" name="to_date" class="form-control form-control-sm font-monospace"
                               value="{{ request('to_date') }}">
                    </div>
                </div>

                <!-- Filter Actions -->
                <div class="col-12 col-md-3">
                    <div class="closing-filter-actions">
                        <button type="submit" class="btn btn-primary btn-sm btn-compact flex-grow-1">
                            <i class="fas fa-filter"></i> Apply
                        </button>

                        <a href="{{ route('admin.daily_closing.index', ['branch_id' => $branchId]) }}"
                           class="btn btn-light border btn-sm btn-compact px-3"
                           title="Reset Filters">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <!-- Closing History Table -->
    <div class="card closing-table-card">
        <div class="table-responsive">
            <table class="table table-hover table-sm closing-table">
                <thead>
                    <tr>
                        <th style="width: 45px;">#ID</th>
                        <th>Date</th>
                        <th>Branch</th>
                        <th>Cashier</th>
                        <th class="text-end">Opening</th>
                        <th class="text-end">POS Cash</th>
                        <th class="text-end">Digital</th>
                        <th class="text-end">Expected</th>
                        <th class="text-end">Actual Count</th>
                        <th class="text-end">Difference</th>
                        <th class="text-center">Status</th>
                        <th class="text-end" style="width: 120px;">Actions</th>
                    </tr>
                </thead>

                <tbody id="dailyClosingTableBody">
                    @include('admin.daily_closing.partials.closing_rows', ['closings' => $closings])
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="closing-pagination">
            <small class="text-muted" style="font-size: 11px;">
                Showing {{ $closings->firstItem() ?? 0 }}–{{ $closings->lastItem() ?? 0 }} of {{ $closings->total() }} sessions
            </small>

            <div>
                {{ $closings->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Session Breakdown Modal -->
<div class="modal fade" id="closingDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg p-3" style="font-size: 12px;">

            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                <h6 class="modal-header-title mb-0">
                    <i class="fas fa-file-invoice text-primary me-1"></i>
                    Session Details (<span id="modalSessionId"></span>)
                </h6>

                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>

            <div id="modalLoadingSpinner" class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                <span class="ms-1 text-muted small">Loading details...</span>
            </div>

            <div id="modalContentBody" class="d-none">
                <table class="table table-sm table-borderless mb-2">
                    <tbody>
                        <tr class="border-bottom">
                            <td class="text-muted py-1">Branch / Outlet:</td>
                            <td class="text-end fw-bold py-1" id="mBranch"></td>
                        </tr>

                        <tr class="border-bottom">
                            <td class="text-muted py-1">Cashier in Charge:</td>
                            <td class="text-end fw-bold py-1" id="mCashier"></td>
                        </tr>

                        <tr class="border-bottom">
                            <td class="text-muted py-1">Opening Cash:</td>
                            <td class="text-end font-monospace bn-digit py-1" id="mOpening"></td>
                        </tr>

                        <tr class="border-bottom">
                            <td class="text-muted py-1">POS Cash Sales:</td>
                            <td class="text-end font-monospace bn-digit text-success py-1" id="mCashSales"></td>
                        </tr>

                        <tr class="border-bottom">
                            <td class="text-muted py-1">Digital Sales (bKash/Bank/Card):</td>
                            <td class="text-end font-monospace bn-digit text-primary py-1" id="mDigitalSales"></td>
                        </tr>

                        <tr class="border-bottom">
                            <td class="text-muted py-1">Due Collected (Cash):</td>
                            <td class="text-end font-monospace bn-digit text-success py-1" id="mDueCash"></td>
                        </tr>

                        <tr class="border-bottom">
                            <td class="text-muted py-1">Store Cash Expenses:</td>
                            <td class="text-end font-monospace bn-digit text-danger py-1" id="mExpenses"></td>
                        </tr>

                        <tr class="border-bottom bg-light">
                            <td class="text-dark fw-bold py-2">Expected Drawer Cash:</td>
                            <td class="text-end font-monospace bn-digit fw-bold text-primary py-2" id="mExpected"></td>
                        </tr>

                        <tr class="border-bottom bg-light">
                            <td class="text-dark fw-bold py-2">Actual Counted Cash:</td>
                            <td class="text-end font-monospace bn-digit fw-bold text-dark py-2" id="mActual"></td>
                        </tr>

                        <tr class="border-bottom">
                            <td class="fw-bold py-2">Shortage / Surplus:</td>
                            <td class="text-end font-monospace bn-digit fw-bold py-2" id="mDifference"></td>
                        </tr>

                        <tr>
                            <td class="text-muted py-1">Remarks / Note:</td>
                            <td class="text-end py-1 text-muted small" id="mNotes"></td>
                        </tr>
                    </tbody>
                </table>

                <div class="modal-action-row border-top pt-2 mt-2">
                    <a href="#" id="modalPrintLink" target="_blank" class="btn btn-outline-dark btn-compact px-3">
                        <i class="fas fa-print"></i> Print Summary
                    </a>

                    <button type="button" class="btn btn-light border btn-compact px-3" data-bs-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
const CSRF = "{{ csrf_token() }}";

// Show Session Breakdown Modal
function showClosingDetailsModal(id) {
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('closingDetailsModal'));
    document.getElementById('modalSessionId').textContent = '#' + id;
    document.getElementById('modalLoadingSpinner').classList.remove('d-none');
    document.getElementById('modalContentBody').classList.add('d-none');
    modal.show();

    fetch("{{ url('admin/daily-closing/show') }}/" + id, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.session) {
            const s = data.session;
            document.getElementById('mBranch').textContent = s.branch_name + (s.branch_code ? ` (${s.branch_code})` : '');
            document.getElementById('mCashier').textContent = s.cashier_name || 'N/A';
            document.getElementById('mOpening').textContent = '৳' + Number(s.opening_cash).toFixed(2);
            document.getElementById('mCashSales').textContent = '+৳' + Number(s.pos_cash_sales).toFixed(2);
            document.getElementById('mDigitalSales').textContent = '৳' + Number(s.digital_sales_amount).toFixed(2);
            document.getElementById('mDueCash').textContent = '+৳' + Number(s.cash_due_collection || 0).toFixed(2);
            document.getElementById('mExpenses').textContent = '-৳' + Number(s.cash_expenses || 0).toFixed(2);
            document.getElementById('mExpected').textContent = '৳' + Number(s.expected_cash).toFixed(2);
            document.getElementById('mActual').textContent = '৳' + Number(s.actual_cash || 0).toFixed(2);

            const diff = Number(s.difference || 0);
            const diffEl = document.getElementById('mDifference');
            diffEl.textContent = (diff >= 0 ? '+' : '') + '৳' + diff.toFixed(2);
            diffEl.className = 'text-end font-monospace bn-digit fw-bold py-1.5 ' + (diff < 0 ? 'text-danger' : (diff > 0 ? 'text-success' : 'text-primary'));

            document.getElementById('mNotes').textContent = s.notes || 'None';
            document.getElementById('modalPrintLink').href = "{{ url('admin/daily-closing/print') }}/" + s.id;

            document.getElementById('modalLoadingSpinner').classList.add('d-none');
            document.getElementById('modalContentBody').classList.remove('d-none');
        } else {
            alert(data.message || 'Failed to load session details.');
            modal.hide();
        }
    })
    .catch(() => {
        alert('Server communication error.');
        modal.hide();
    });
}

// Manager Verification Action
function verifySessionRow(id) {
    if (!confirm(`Are you sure you want to verify and lock Register Session #${id}?`)) {
        return;
    }

    fetch("{{ url('admin/daily-closing/verify') }}/" + id, {
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
            alert(data.message || 'Verification failed.');
        }
    })
    .catch(() => {
        alert('Server connection error.');
    });
}
</script>
@endsection
