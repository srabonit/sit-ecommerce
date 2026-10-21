@extends('layouts.admin')

@section('title', 'All Branches - ' . config('app.name'))

@section('admin_content')
<div class="container-fluid py-3 px-2 px-md-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <h5 class="fw-bold text-dark mb-0">All Branch</h5>
        <a href="{{ route('admin.branch.create') }}" class="btn btn-warning btn-sm fw-bold text-white px-3 shadow-sm align-self-start align-self-md-auto">
            <i class="fas fa-plus me-1"></i> Add Branch
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 py-2 px-3 mb-3 small" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- লাইভ সার্চ বার ও সার্চ ক্লিয়ার বাটন -->
    <div class="card border-0 shadow-sm rounded-4 mb-3 p-3">
        <div class="row g-2 align-items-center justify-content-between">
            <div class="col-12 col-md-5">
                <div class="input-group input-group-sm position-relative">
                    <span class="input-group-text bg-white border-end-0 rounded-start-pill"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="ajaxBranchSearch" class="form-control border-start-0 border-end-0" placeholder="Live search by name, code, phone or address...">
                    <button class="btn btn-outline-light border border-start-0 text-muted rounded-end-pill d-none" type="button" id="clearSearchBtn">
                        <i class="fas fa-times"></i>
                    </button>
                    <span id="branchSearchSpinner" class="spinner-border spinner-border-sm text-primary position-absolute end-0 me-4 top-50 translate-middle-y d-none" style="z-index: 5;"></span>
                </div>
            </div>
            <div class="col-12 col-md-3 text-md-end">
                <span class="text-muted small">Total Branches: <strong id="totalBranchCount">{{ $branches->total() }}</strong></span>
            </div>
        </div>
    </div>

    <!-- টেবিল কার্ড -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden position-relative">
        <div id="tableLoader" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none justify-content-center align-items-center" style="z-index: 10;">
            <div class="spinner-border text-primary" role="status"></div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small text-nowrap">
                    <thead class="bg-light text-muted text-uppercase">
                        <tr>
                            <th class="ps-3" style="width: 60px;">SL</th>
                            <th>NAME</th>
                            <th>PHONE</th>
                            <th>EMAIL</th>
                            <th>ADDRESS</th>
                            <th>ACTIVE</th>
                            <th class="text-end pe-3">ACTION</th>
                        </tr>
                    </thead>
                    <tbody id="branchTableBody">
                        @include('admin.branch.partials.branch_rows')
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 py-3 overflow-auto" id="branchPagination">
            {{ $branches->links() }}
        </div>
    </div>
</div>

<!-- Modal: Delete Confirmation -->
<div class="modal fade" id="branchDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow text-center p-4">
            <div class="text-danger mb-2">
                <i class="fas fa-trash-alt fa-3x"></i>
            </div>
            <h6 class="fw-bold text-dark mb-1">Delete Branch?</h6>
            <p class="text-muted small mb-3" id="deleteBranchText">Are you sure you want to delete this branch?</p>
            <div class="d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-danger rounded-pill px-3" id="confirmDeleteSubmitBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const CSRF = "{{ csrf_token() }}";
    const searchInput = document.getElementById('ajaxBranchSearch');
    const clearBtn = document.getElementById('clearSearchBtn');
    const tableBody = document.getElementById('branchTableBody');
    const spinner = document.getElementById('branchSearchSpinner');
    const loader = document.getElementById('tableLoader');
    const modalEl = document.getElementById('branchDeleteModal');
    const deleteModal = new bootstrap.Modal(modalEl);
    
    let currentDeleteId = null;
    let debounceTimer = null;

    // ১. লাইভ সার্চ ফাংশন
    function performLiveSearch(term = '') {
        if (spinner) spinner.classList.remove('d-none');
        if (loader) {
            loader.classList.remove('d-none');
            loader.classList.add('d-flex');
        }

        const url = new URL("{{ route('admin.branch.index') }}", window.location.origin);
        if (term) url.searchParams.set('search', term);

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => response.text())
        .then(html => {
            tableBody.innerHTML = html;
        })
        .catch(err => console.error('Search error:', err))
        .finally(() => {
            if (spinner) spinner.classList.add('d-none');
            if (loader) {
                loader.classList.add('d-none');
                loader.classList.remove('d-flex');
            }
        });
    }

    searchInput.addEventListener('input', function () {
        const query = this.value.trim();
        clearBtn.classList.toggle('d-none', !query);

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            performLiveSearch(query);
        }, 250);
    });

    clearBtn.addEventListener('click', function () {
        searchInput.value = '';
        clearBtn.classList.add('d-none');
        performLiveSearch('');
    });

    // ২. ইভেন্ট ডেলিগেশন (ডিলিট বাটন ক্লিক ১০০% গ্যারান্টি)
    tableBody.addEventListener('click', function (e) {
        const delBtn = e.target.closest('.delete-branch-btn');
        if (delBtn) {
            currentDeleteId = delBtn.getAttribute('data-id');
            const name = delBtn.getAttribute('data-name');
            document.getElementById('deleteBranchText').textContent = `Are you sure you want to delete branch "${name}"?`;
            deleteModal.show();
        }
    });

    // ৩. মোডাল কনফার্ম ডিলিট এক্সিকিউশন
    document.getElementById('confirmDeleteSubmitBtn').addEventListener('click', function () {
        if (!currentDeleteId) return;

        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Deleting...';

        fetch("{{ url('admin/branch/delete') }}/" + currentDeleteId, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF
            }
        })
        .then(res => res.json())
        .then(data => {
            deleteModal.hide();
            btn.disabled = false;
            btn.textContent = 'Delete';

            if (data.success) {
                const targetRow = document.getElementById('branchRow' + currentDeleteId);
                if (targetRow) {
                    targetRow.style.transition = 'all 0.3s ease';
                    targetRow.style.opacity = '0';
                    setTimeout(() => targetRow.remove(), 300);
                }
            } else {
                alert(data.message || 'Cannot delete this branch.');
            }
        })
        .catch(err => {
            deleteModal.hide();
            btn.disabled = false;
            btn.textContent = 'Delete';
            console.error(err);
            alert('Failed to connect to server.');
        });
    });

    // ৪. স্ট্যাটাস টগল
    window.toggleBranchStatus = function(id) {
        fetch("{{ url('admin/branch/toggle-status') }}/" + id, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) alert('Failed to update status.');
        });
    };
});
</script>
@endsection