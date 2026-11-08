
@extends('layouts.admin')

@section('title', 'Assign History - ' . config('app.name'))

@section('admin_content')

<style>
    /* =========================================
       Branch Assignment History
       Kaiadmin Classic / White Theme
    ========================================= */

    #branchAssignHistoryPage {
        --bah-primary: var(--bs-primary, #1572e8);
        --bah-bg: #f8f9fa;
        --bah-border: #ebedf2;
        --bah-text: #2a2f5b;
        --bah-muted: #8d9498;
        --bah-radius: 5px;
        color: var(--bah-text);
        font-size: 13px;
    }

    #branchAssignHistoryPage .bah-page-title {
        font-size: 19px;
        font-weight: 700;
        color: #2a2f5b;
        letter-spacing: -.35px;
    }

    #branchAssignHistoryPage .bah-card {
        background: #fff;
        border: 1px solid var(--bah-border) !important;
        border-radius: var(--bah-radius) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .035) !important;
    }

    #branchAssignHistoryPage .bah-primary-btn {
        min-height: 36px;
        padding: 7px 15px;
        border-radius: var(--bah-radius);
        font-size: 12px;
        font-weight: 600;
        background: var(--bah-primary);
        border-color: var(--bah-primary);
        color: #fff;
        box-shadow: none !important;
        transition: all .2s ease;
    }

    #branchAssignHistoryPage .bah-primary-btn:hover {
        filter: brightness(.93);
        color: #fff;
    }

    #branchAssignHistoryPage .form-select {
        min-height: 38px;
        border-radius: var(--bah-radius);
        border-color: #ebedf2;
        color: #495057;
        font-size: 12px;
        box-shadow: none;
    }

    #branchAssignHistoryPage .form-select:focus {
        border-color: var(--bah-primary);
        box-shadow: 0 0 0 2px rgba(21, 114, 232, .10);
    }

    #branchAssignHistoryPage .bah-filter-label {
        display: block;
        margin-bottom: 6px;
        color: #495057;
        font-size: 12px;
        font-weight: 600;
    }

    #branchAssignHistoryPage .bah-table-wrap {
        width: 100%;
        overflow-x: auto;
        scrollbar-width: thin;
    }

    #branchAssignHistoryPage .bah-table {
        width: 100%;
        min-width: 680px;
        border-collapse: separate;
        border-spacing: 0;
    }

    #branchAssignHistoryPage .bah-table thead th {
        padding: 12px 10px;
        background: #f8f9fa;
        color: #6c757d;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .45px;
        vertical-align: middle;
        white-space: nowrap;
        border-bottom: 1px solid var(--bah-border);
    }

    #branchAssignHistoryPage .bah-table tbody td {
        padding: 11px 10px;
        color: #495057;
        font-size: 12px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f1f5;
    }

    #branchAssignHistoryPage .bah-table tbody tr:last-child td {
        border-bottom: 0;
    }

    #branchAssignHistoryPage .bah-table tbody tr {
        transition: background .15s ease;
    }

    #branchAssignHistoryPage .bah-table tbody tr:hover {
        background: #fafbfc;
    }

    #branchAssignHistoryPage .bah-table .text-primary {
        color: var(--bah-primary) !important;
    }

    #branchAssignHistoryPage .bah-pagination {
        border-top: 1px solid var(--bah-border) !important;
    }

    #branchAssignHistoryPage .bah-pagination nav {
        display: flex;
        justify-content: flex-end;
    }

    #branchAssignHistoryPage .bah-pagination .pagination {
        margin-bottom: 0;
        flex-wrap: wrap;
        gap: 3px;
    }

    #branchAssignHistoryPage .bah-pagination .page-link {
        min-width: 30px;
        min-height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 9px;
        border-radius: var(--bah-radius) !important;
        border-color: var(--bah-border);
        color: #495057;
        font-size: 11px;
        box-shadow: none;
    }

    #branchAssignHistoryPage .bah-pagination .active > .page-link {
        background: var(--bah-primary);
        border-color: var(--bah-primary);
        color: #fff;
    }

    #branchAssignHistoryPage .alert {
        border-radius: var(--bah-radius) !important;
        font-size: 12px;
    }

    #branchAssignHistoryPage .bah-filter-spinner {
        color: var(--bah-primary);
    }

    /* Transfer Items Modal */

    #transferItemsModal .modal-content {
        border: 1px solid var(--bah-border) !important;
        border-radius: 6px !important;
        overflow: hidden;
    }

    #transferItemsModal .modal-header {
        background: #fff;
        border-bottom: 1px solid var(--bah-border) !important;
    }

    #transferItemsModal .modal-title,
    #transferItemsModal #modalTransferTitle {
        color: #2a2f5b;
        font-size: 14px;
        font-weight: 700;
    }

    #transferItemsModal .modal-body {
        background: #fff;
    }

    #transferItemsModal .table {
        min-width: 400px;
    }

    #transferItemsModal .table thead th {
        padding: 10px;
        background: #f8f9fa;
        color: #6c757d;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        white-space: nowrap;
        border-bottom: 1px solid var(--bah-border);
    }

    #transferItemsModal .table tbody td {
        padding: 11px 10px;
        font-size: 12px;
        border-bottom: 1px solid #f0f1f5;
    }

    #transferItemsModal code {
        font-size: 11px;
        color: #e83e8c;
    }

    #transferItemsModal .spinner-border {
        width: 28px;
        height: 28px;
    }

    @media (max-width: 767.98px) {
        #branchAssignHistoryPage {
            padding-top: 12px !important;
        }

        #branchAssignHistoryPage .bah-page-title {
            font-size: 17px;
        }

        #branchAssignHistoryPage .bah-filter-card {
            padding: 12px !important;
        }

        #branchAssignHistoryPage .bah-table thead th,
        #branchAssignHistoryPage .bah-table tbody td {
            padding: 10px 9px;
        }

        #branchAssignHistoryPage .bah-pagination nav {
            justify-content: center;
        }

        #transferItemsModal .modal-dialog {
            margin: 10px;
        }
    }

    @media (max-width: 374.98px) {
        #branchAssignHistoryPage .bah-page-title {
            font-size: 15px;
        }

        #branchAssignHistoryPage .bah-primary-btn {
            padding: 6px 10px;
            font-size: 11px;
        }
    }
</style>

<div id="branchAssignHistoryPage" class="container-fluid py-3 px-2 px-md-3">

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <div>
            <h5 class="bah-page-title mb-0">
                <i class="fas fa-history me-2 text-primary"></i>
                Assign History
            </h5>
        </div>

        <a href="{{ route('admin.branch.new.assign') }}"
           class="btn btn-primary bah-primary-btn align-self-start align-self-md-auto">
            <i class="fas fa-plus me-1"></i>
            New Assign
        </a>
    </div>

    <!-- Success Notification -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 mb-3"
             role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Branch Filter -->
    <div class="card bah-card bah-filter-card mb-3">
        <div class="card-body p-3">
            <div class="row g-3 align-items-end">

                <div class="col-12 col-md-4 col-lg-3">
                    <label class="bah-filter-label">
                        <i class="fas fa-filter me-1 text-muted"></i>
                        Filter by Branch
                    </label>

                    <select id="filterBranchSelect" class="form-select form-select-sm">
                        <option value="">All Branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto">
                    <span id="historyFilterSpinner"
                          class="spinner-border spinner-border-sm bah-filter-spinner d-none"
                          role="status"></span>
                </div>

            </div>
        </div>
    </div>

    <!-- Assignment History Table -->
    <div class="card bah-card overflow-hidden position-relative">

        <div class="card-header bg-white border-bottom py-3 px-3">
            <h6 class="mb-0 fw-bold text-dark" style="font-size: 13px;">
                <i class="fas fa-list-alt me-2 text-primary"></i>
                Assignment Records
            </h6>
        </div>

        <div class="card-body p-0">
            <div class="bah-table-wrap table-responsive">
                <table class="table table-hover align-middle mb-0 bah-table text-nowrap">
                    <thead>
                        <tr>
                            <th class="ps-3" style="width: 60px;">SL</th>
                            <th>TRANSFER NO / DATE</th>
                            <th>BRANCH</th>
                            <th>PRODUCTS COUNT</th>
                            <th>ASSIGN BY</th>
                            <th class="text-end pe-3">ACTION</th>
                        </tr>
                    </thead>

                    <tbody id="historyTableBody">
                        @include('admin.branch.partials.assign_history_rows')
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="card-footer bah-pagination bg-white border-0 py-3 px-3 overflow-auto"
             id="historyPagination">
            {{ $transfers->links() }}
        </div>

    </div>

</div>

<!-- Modal: Transfer Items -->
<div class="modal fade" id="transferItemsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow">

            <div class="modal-header py-3 px-3">
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    <div class="text-primary">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h6 class="fw-bold mb-0 text-truncate pe-2"
                        id="modalTransferTitle">
                        Transfer Details
                    </h6>
                </div>

                <button type="button"
                        class="btn-close flex-shrink-0"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-3">

                <!-- Loading State -->
                <div id="modalLoading" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>

                <!-- Transfer Items Table -->
                <div class="table-responsive" id="modalTableWrap">
                    <table class="table table-sm align-middle small mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Code</th>
                                <th class="text-end">Assigned Qty</th>
                            </tr>
                        </thead>

                        <tbody id="modalItemsBody"></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterSelect = document.getElementById('filterBranchSelect');
    const tableBody = document.getElementById('historyTableBody');
    const spinner = document.getElementById('historyFilterSpinner');
    const modalEl = document.getElementById('transferItemsModal');
    const transferModal = new bootstrap.Modal(modalEl);

    // 1. AJAX branch filtering
    filterSelect.addEventListener('change', function () {
        const branchId = this.value;
        spinner.classList.remove('d-none');

        const url = new URL("{{ route('admin.branch.assign.history') }}", window.location.origin);
        if (branchId) url.searchParams.set('branch_id', branchId);

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(res => res.text())
        .then(html => {
            tableBody.innerHTML = html;
        })
        .catch(err => console.error(err))
        .finally(() => {
            spinner.classList.add('d-none');
        });
    });

    // 2. View transfer items using event delegation
    tableBody.addEventListener('click', function (e) {
        const btn = e.target.closest('.view-transfer-btn');
        if (btn) {
            const transferId = btn.getAttribute('data-id');
            document.getElementById('modalLoading').classList.remove('d-none');
            document.getElementById('modalTableWrap').classList.add('d-none');
            document.getElementById('modalItemsBody').innerHTML = '';
            transferModal.show();

            fetch("{{ url('admin/branch/assign-history') }}/" + transferId, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('modalLoading').classList.add('d-none');
                document.getElementById('modalTableWrap').classList.remove('d-none');

                if (data.success) {
                    document.getElementById('modalTransferTitle').textContent = `${data.transfer.transfer_no} (${data.transfer.branch_name})`;
                    let rows = '';
                    data.items.forEach(item => {
                        rows += `
                            <tr>
                                <td class="fw-bold text-dark">${item.title}</td>
                                <td><code>${item.product_code ?? 'N/A'}</code></td>
                                <td class="text-end fw-bold text-primary">${item.quantity} Pcs</td>
                            </tr>
                        `;
                    });
                    document.getElementById('modalItemsBody').innerHTML = rows;
                }
            })
            .catch(err => {
                document.getElementById('modalLoading').classList.add('d-none');
                console.error(err);
            });
        }
    });
});
</script>

@endsection
