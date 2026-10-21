@extends('layouts.admin')

@section('title', 'All Branch Products - ' . config('app.name'))

@section('admin_content')
<div class="container-fluid py-3 px-2 px-md-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <h5 class="fw-bold text-dark mb-0">All Branch Products - <span id="totalProductCount">{{ $stocks->total() }}</span></h5>
        <a href="{{ route('admin.branch.new.assign') }}" class="btn btn-warning btn-sm fw-bold text-white px-3 shadow-sm align-self-start align-self-md-auto">
            <i class="fas fa-plus me-1"></i> Assign Product to Branch
        </a>
    </div>

    <!-- ফিল্টারিং বার -->
    <div class="card border-0 shadow-sm rounded-4 mb-3 p-3">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="productSearchInput" class="form-control border-start-0" placeholder="search product...">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <select id="branchFilterSelect" class="form-select form-select-sm rounded-pill">
                    <option value="">Filter by branch (All)</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <span id="productLoadingSpinner" class="spinner-border spinner-border-sm text-primary d-none"></span>
            </div>
        </div>
    </div>

    <!-- টেবিল কার্ড -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden position-relative">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small text-nowrap">
                <thead class="bg-light text-muted text-uppercase">
                    <tr>
                        <th class="ps-3">PRODUCT</th>
                        <th>BRANCH</th>
                        <th>CATEGORY</th>
                        <th>BASE PRICE</th>
                        <th>BRANCH STOCK</th>
                        <th class="text-end pe-3">ACTION</th>
                    </tr>
                </thead>
                <tbody id="branchProductsTableBody">
                    @include('admin.branch.partials.branch_product_rows')
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0 py-3 overflow-auto" id="branchProductsPagination">
            {{ $stocks->links() }}
        </div>
    </div>
</div>

<!-- Modal: স্টক অ্যাডজাস্টমেন্ট -->
<div class="modal fade" id="adjustStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow p-4 text-center">
            <div class="text-primary mb-2">
                <i class="fas fa-cubes fa-3x"></i>
            </div>
            <h6 class="fw-bold text-dark mb-1" id="adjustStockTitle">Adjust Stock</h6>
            <p class="text-muted small mb-3">Update available stock in this branch</p>
            <input type="number" id="newStockInput" class="form-control text-center fs-5 mb-3 fw-bold" min="0">
            <div class="d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-warning fw-bold text-white rounded-pill px-3" id="saveStockBtn">Save Stock</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const CSRF = "{{ csrf_token() }}";
    const modalEl = document.getElementById('adjustStockModal');
    const adjustModal = new bootstrap.Modal(modalEl);
    const tableBody = document.getElementById('branchProductsTableBody');
    const searchInput = document.getElementById('productSearchInput');
    const branchFilter = document.getElementById('branchFilterSelect');
    const spinner = document.getElementById('productLoadingSpinner');
    const saveBtn = document.getElementById('saveStockBtn');
    const stockInput = document.getElementById('newStockInput');

    let editingStockId = null;

    // ১. Adjust Stock বাটন ক্লিক হ্যান্ডলার (ইভেন্ট ডেলিগেশন - নো কোটেশন কনফ্লিক্ট)
    tableBody.addEventListener('click', function (e) {
        const btn = e.target.closest('.adjust-stock-btn');
        if (btn) {
            editingStockId = btn.getAttribute('data-id');
            const title = btn.getAttribute('data-title');
            const stock = btn.getAttribute('data-stock');

            document.getElementById('adjustStockTitle').textContent = title;
            stockInput.value = stock;
            adjustModal.show();
        }
    });

    // ২. স্টক সেভ রিকোয়েস্ট (AJAX)
    saveBtn.addEventListener('click', function () {
        if (!editingStockId) return;

        const qty = parseInt(stockInput.value);
        if (isNaN(qty) || qty < 0) {
            alert('Please enter a valid stock quantity (0 or greater).');
            return;
        }

        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        fetch("{{ url('admin/branch/update-stock') }}/" + editingStockId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify({ quantity: qty })
        })
        .then(res => res.json())
        .then(data => {
            adjustModal.hide();
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Stock';

            if (data.success) {
                // ব্যাজ ও বাটনের ডাটা-স্টক তাৎক্ষণিক আপডেট
                const badge = document.getElementById('stockQtyBadge' + editingStockId);
                if (badge) badge.textContent = qty;

                const rowBtn = document.querySelector(`.adjust-stock-btn[data-id="${editingStockId}"]`);
                if (rowBtn) rowBtn.setAttribute('data-stock', qty);
            } else {
                alert(data.message || 'Failed to update stock.');
            }
        })
        .catch(err => {
            adjustModal.hide();
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Stock';
            console.error(err);
            alert('Server error.');
        });
    });

    // ৩. লাইভ সার্চ ও ব্রাঞ্চ ফিল্টার
    function fetchFilteredProducts() {
        spinner.classList.remove('d-none');
        const url = new URL("{{ route('admin.branch.products') }}", window.location.origin);
        if (searchInput.value.trim()) url.searchParams.set('search', searchInput.value.trim());
        if (branchFilter.value) url.searchParams.set('branch_id', branchFilter.value);

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
        .finally(() => {
            spinner.classList.add('d-none');
        });
    }

    let debounce;
    searchInput.addEventListener('input', () => {
        clearTimeout(debounce);
        debounce = setTimeout(fetchFilteredProducts, 250);
    });

    branchFilter.addEventListener('change', fetchFilteredProducts);
});
</script>
@endsection