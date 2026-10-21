@extends('layouts.admin')

@section('title', 'All Assign Product History - ' . config('app.name'))

@section('admin_content')
<div class="container-fluid py-3 px-2 px-md-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <h5 class="fw-bold text-dark mb-0">All Assign Product History</h5>
        <a href="{{ route('admin.branch.new.assign') }}" class="btn btn-warning btn-sm fw-bold text-white px-3 shadow-sm align-self-start align-self-md-auto">
            <i class="fas fa-plus me-1"></i> + New Assign
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 py-2 px-3 mb-3 small" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- ব্রাঞ্চ ফিল্টার ড্রপডাউন -->
    <div class="card border-0 shadow-sm rounded-4 mb-3 p-3">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-3">
                <select id="filterBranchSelect" class="form-select form-select-sm rounded-pill">
                    <option value="">Filter by branch (All)</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <span id="historyFilterSpinner" class="spinner-border spinner-border-sm text-primary d-none"></span>
            </div>
        </div>
    </div>

    <!-- টেবিল কার্ড -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden position-relative">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small text-nowrap">
                    <thead class="bg-light text-muted text-uppercase">
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
        <div class="card-footer bg-white border-0 py-3 overflow-auto" id="historyPagination">
            {{ $transfers->links() }}
        </div>
    </div>
</div>

<!-- Modal: ট্রান্সফার আইটেমস ভিউ -->
<div class="modal fade" id="transferItemsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h6 class="fw-bold text-dark mb-0 text-truncate pe-2" id="modalTransferTitle">Transfer Details</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div id="modalLoading" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <div class="table-responsive" id="modalTableWrap">
                    <table class="table table-sm align-middle small mb-0">
                        <thead class="bg-light">
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

    // ১. ব্রাঞ্চ ড্রপডাউন সিলেক্ট করলে AJAX ফিল্টারিং
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

    // ২. View Items বাটনে ক্লিক ইভেন্ট (ইভেন্ট ডেলিগেশন)
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