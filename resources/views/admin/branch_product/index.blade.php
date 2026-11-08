@extends('layouts.admin')

@section('title', 'Branch Product Catalog - ' . $branch->name)

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #branchCatalogRoot .btn,
    #branchCatalogRoot .btn-sm,
    #branchCatalogRoot button,
    #branchCatalogRoot .badge,
    #branchCatalogRoot .form-control,
    #branchCatalogRoot .form-select,
    #branchCatalogRoot .input-group-text,
    #branchCatalogRoot .card,
    #branchCatalogRoot .alert,
    #branchCatalogRoot .table,
    #branchCatalogRoot .table th,
    #branchCatalogRoot .table td,
    #branchCatalogRoot .modal-content {
        border-radius: 5px !important;
    }

    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
    }

    .btn-compact {
        font-size: 11.5px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    .catalog-thumb {
        width: 38px;
        height: 38px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
    }

    .mode-badge {
        font-size: 9.5px !important;
        padding: 3px 6px !important;
        font-weight: 700;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }
</style>

<div class="container-fluid py-2 px-2 px-md-3" id="branchCatalogRoot">
    <!-- Top Meta & Navigation Header Bar -->
    <div class="d-flex justify-content-between align-items-center mb-3 bg-white p-2.5 px-3 shadow-xs border">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-primary text-white fw-bold px-2.5 py-1.5" style="font-size: 12px;">
                <i class="fas fa-boxes me-1"></i> Branch Catalog
            </span>
            <span class="fw-bold text-dark fs-6">{{ $branch->name }}</span>
            <span class="badge bg-light text-dark border font-monospace" style="font-size: 11px;">
                Code: {{ $branch->code ?? $branch->branch_code }}
            </span>
            <span class="badge bg-warning text-dark font-monospace" style="font-size: 10.5px;">
                Total Items: <span id="totalItemsCount">{{ $products->total() }}</span>
            </span>
        </div>

        <div class="d-flex align-items-center gap-1.5">
            @if(isset($selectableBranches) && $selectableBranches->count() > 1)
                <div class="dropdown d-inline-block">
                    <button class="btn btn-outline-primary btn-compact py-1 px-2.5 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-store-alt me-1"></i> Switch Hub
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size: 11px;">
                        @foreach($selectableBranches as $sb)
                            <li>
                                <a class="dropdown-item py-1.5 {{ $sb->id == $branch->id ? 'active fw-bold' : '' }}" 
                                   href="{{ route('admin.branch_product.index', ['branch_id' => $sb->id]) }}">
                                    {{ $sb->name }} ({{ $sb->code ?? $sb->branch_code }})
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <a href="{{ route('admin.pos.terminal', $branch->id) }}" class="btn btn-warning text-white btn-compact py-1 px-2.5">
                <i class="fas fa-desktop me-1"></i> POS Terminal
            </a>
            <a href="{{ route('admin.branch.index') }}" class="btn btn-outline-secondary btn-compact py-1 px-2.5">
                <i class="fas fa-arrow-left me-1"></i> Branches
            </a>
        </div>
    </div>

    <!-- Alert Box for AJAX Actions -->
    <div id="catalogActionToast" class="alert d-none py-2 px-3 mb-3" style="font-size: 12px;"></div>

    <!-- Filter & Bulk Actions Bar -->
    <div class="card border shadow-xs bg-white p-2.5 mb-3">
        <form method="GET" action="{{ route('admin.branch_product.index') }}" id="catalogFilterForm" class="row g-2 align-items-center">
            <input type="hidden" name="branch_id" id="catalogBranchId" value="{{ $branch->id }}">

            <!-- Search by Title / SKU / Barcode / ID -->
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm position-relative">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" id="catalogSearchInput" class="form-control border-start-0" 
                           placeholder="Search title, SKU, barcode, ID..." value="{{ request('search') }}" style="font-size: 11.5px;" autocomplete="off">
                    
                    <span class="input-group-text bg-white border-start-0 d-none" id="catalogSearchSpinner">
                        <span class="spinner-border spinner-border-sm text-primary" style="width: 13px; height: 13px;" role="status"></span>
                    </span>

                    <button type="button" class="btn btn-outline-light border text-muted {{ request('search') ? '' : 'd-none' }}" id="catalogClearSearchBtn" onclick="clearSearchFilter()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Availability Policy Mode Filter -->
            <div class="col-6 col-md-3">
                <select name="availability_mode" id="availabilityModeFilter" class="form-select form-select-sm" style="font-size: 11.5px;">
                    <option value="all_modes" {{ request('availability_mode') === 'all_modes' ? 'selected' : '' }}>Mode: All Policies</option>
                    <option value="all" {{ request('availability_mode') === 'all' ? 'selected' : '' }}>Mode A: All Branches</option>
                    <option value="selected" {{ request('availability_mode') === 'selected' ? 'selected' : '' }}>Mode B: Selected Outlets</option>
                    <option value="main_only" {{ request('availability_mode') === 'main_only' ? 'selected' : '' }}>Mode C: Main Showroom</option>
                    <option value="head_office_only" {{ request('availability_mode') === 'head_office_only' ? 'selected' : '' }}>Mode D: Head Office / HQ</option>
                    <option value="restricted" {{ request('availability_mode') === 'restricted' ? 'selected' : '' }}>Mode E: Restricted / Private</option>
                </select>
            </div>

            <!-- Bulk Actions Dropdown -->
            <div class="col-6 col-md-3">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown" style="font-size: 11.5px;">
                        <i class="fas fa-tasks me-1"></i> Bulk Outlet Actions
                    </button>
                    <ul class="dropdown-menu shadow border-0" style="font-size: 11.5px;">
                        <li>
                            <a class="dropdown-item py-1.5 text-success" href="javascript:void(0)" onclick="executeBulkAction('assign')">
                                <i class="fas fa-check-circle me-1"></i> Assign Selected to {{ $branch->name }}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-1.5 text-danger" href="javascript:void(0)" onclick="executeBulkAction('remove')">
                                <i class="fas fa-times-circle me-1"></i> Remove Selected from {{ $branch->name }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-12 col-md-2 text-end">
                <button type="submit" id="catalogFilterBtn" class="btn btn-primary btn-sm btn-compact w-100 py-1.5">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Products Table Card -->
    <div class="card border shadow-xs bg-white p-2">
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0" style="font-size: 11.5px;">
                <thead class="table-light">
                    <tr>
                        <th class="py-2 text-center" style="width: 36px;">
                            <input type="checkbox" id="selectAllCheckbox" class="form-check-input" onclick="toggleSelectAll(this.checked)">
                        </th>
                        <th class="py-2">Product Info</th>
                        <th class="py-2">Availability Mode</th>
                        <th class="py-2 text-center">Hub Stock</th>
                        <th class="py-2 text-end">Branch Price</th>
                        <th class="py-2 text-center" style="width: 100px;">POS Enabled</th>
                        <th class="py-2 text-center" style="width: 95px;">Can Sell</th>
                        <th class="py-2 text-end" style="width: 130px;">Action</th>
                    </tr>
                </thead>
                <tbody id="branchProductTableBody">
                    @include('admin.branch_product.partials.product_rows', ['products' => $products, 'branch' => $branch])
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <div class="d-flex justify-content-between align-items-center mt-3 px-2 flex-wrap gap-2">
            <small class="text-muted" style="font-size: 11px;" id="catalogShowingText">
                Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} items
            </small>
            <div id="catalogPaginationWrapper">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>

<script>
const CSRF = "{{ csrf_token() }}";
const BRANCH_ID = {{ $branch->id }};

// Select All Checkbox Handler
function toggleSelectAll(select) {
    document.querySelectorAll('.prod-checkbox').forEach(chk => chk.checked = select);
}

// AJAX Toggle POS Status
function toggleBranchPos(productId, el) {
    const originalState = el.checked;
    el.disabled = true;

    fetch("{{ url('admin/branch-products/toggle-pos') }}/" + BRANCH_ID + "/" + productId, {
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
            el.checked = data.is_pos_enabled;
            showActionToast(data.message, 'success');
        } else {
            el.checked = !originalState;
            showActionToast(data.message || 'Action failed.', 'danger');
        }
    })
    .catch(() => {
        el.checked = !originalState;
        showActionToast('Server connection error.', 'danger');
    })
    .finally(() => {
        el.disabled = false;
    });
}

// AJAX Toggle Can Sell Status
function toggleBranchCanSell(productId, el) {
    const originalState = el.checked;
    el.disabled = true;

    fetch("{{ url('admin/branch-products/toggle-can-sell') }}/" + BRANCH_ID + "/" + productId, {
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
            el.checked = data.can_sell;
            showActionToast(data.message, 'success');
        } else {
            el.checked = !originalState;
            showActionToast(data.message || 'Action failed.', 'danger');
        }
    })
    .catch(() => {
        el.checked = !originalState;
        showActionToast('Server connection error.', 'danger');
    })
    .finally(() => {
        el.disabled = false;
    });
}

// Bulk Assign / Remove Action
function executeBulkAction(action) {
    const checked = Array.from(document.querySelectorAll('.prod-checkbox:checked')).map(cb => cb.value);

    if (checked.length === 0) {
        alert('Please select at least one product using the checkboxes.');
        return;
    }

    const actionText = action === 'assign' ? 'assign' : 'remove';
    if (!confirm(`Are you sure you want to ${actionText} ${checked.length} selected products for this branch?`)) {
        return;
    }

    fetch("{{ route('admin.branch_product.bulk_assign') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF
        },
        body: JSON.stringify({
            branch_id: BRANCH_ID,
            product_ids: checked,
            action: action
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Bulk operation failed.');
        }
    })
    .catch(() => {
        alert('Server communication error.');
    });
}

function showActionToast(msg, type = 'info') {
    const toast = document.getElementById('catalogActionToast');
    toast.className = `alert alert-${type} py-2 px-3 mb-3`;
    toast.textContent = msg;
    toast.classList.remove('d-none');
    setTimeout(() => toast.classList.add('d-none'), 3000);
}

// =========================================================================
// HIGH-SPEED SECURE AJAX LIVE SEARCH, FILTER & PAGINATION
// =========================================================================
let catalogSearchTimer = null;
const searchInput = document.getElementById('catalogSearchInput');
const searchSpinner = document.getElementById('catalogSearchSpinner');
const clearSearchBtn = document.getElementById('catalogClearSearchBtn');
const modeFilter = document.getElementById('availabilityModeFilter');
const filterForm = document.getElementById('catalogFilterForm');
const tableBody = document.getElementById('branchProductTableBody');
const showingText = document.getElementById('catalogShowingText');
const paginationWrapper = document.getElementById('catalogPaginationWrapper');
const totalItemsBadge = document.getElementById('totalItemsCount');

function clearSearchFilter() {
    if (searchInput) {
        searchInput.value = '';
        if (clearSearchBtn) clearSearchBtn.classList.add('d-none');
        fetchCatalogData();
    }
}

function fetchCatalogData(targetUrl = null) {
    if (searchSpinner) searchSpinner.classList.remove('d-none');

    let requestUrl;
    if (targetUrl) {
        requestUrl = new URL(targetUrl, window.location.origin);
    } else {
        requestUrl = new URL("{{ route('admin.branch_product.index') }}", window.location.origin);
        requestUrl.searchParams.set('branch_id', BRANCH_ID);

        const queryTerm = searchInput ? searchInput.value.trim() : '';
        if (queryTerm) {
            requestUrl.searchParams.set('search', queryTerm);
            if (clearSearchBtn) clearSearchBtn.classList.remove('d-none');
        } else {
            requestUrl.searchParams.delete('search');
            if (clearSearchBtn) clearSearchBtn.classList.add('d-none');
        }

        const selectedMode = modeFilter ? modeFilter.value : 'all_modes';
        if (selectedMode && selectedMode !== 'all_modes') {
            requestUrl.searchParams.set('availability_mode', selectedMode);
        } else {
            requestUrl.searchParams.delete('availability_mode');
        }
    }

    // Keep address bar query string synchronized without page reload
    window.history.replaceState({}, '', requestUrl.toString());

    fetch(requestUrl.toString(), {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        }
        return response.text().then(text => ({ success: true, html: text }));
    })
    .then(data => {
        if (data.html && tableBody) {
            tableBody.innerHTML = data.html;
        }
        if (data.pagination_html && paginationWrapper) {
            paginationWrapper.innerHTML = data.pagination_html;
        }
        if (data.showing_text && showingText) {
            showingText.textContent = data.showing_text;
        }
        if (data.total !== undefined && totalItemsBadge) {
            totalItemsBadge.textContent = data.total;
        }

        // Reset top select-all checkbox
        const selectAllCb = document.getElementById('selectAllCheckbox');
        if (selectAllCb) selectAllCb.checked = false;
    })
    .catch(err => {
        console.error('Catalog live filter error:', err);
    })
    .finally(() => {
        if (searchSpinner) searchSpinner.classList.add('d-none');
    });
}

// 1. Debounced Live Typing Search
if (searchInput) {
    searchInput.addEventListener('input', function () {
        if (clearSearchBtn) {
            if (this.value.trim().length > 0) {
                clearSearchBtn.classList.remove('d-none');
            } else {
                clearSearchBtn.classList.add('d-none');
            }
        }
        clearTimeout(catalogSearchTimer);
        catalogSearchTimer = setTimeout(() => {
            fetchCatalogData();
        }, 280);
    });
}

// 2. Instant Dropdown Filter Change
if (modeFilter) {
    modeFilter.addEventListener('change', function () {
        fetchCatalogData();
    });
}

// 3. Prevent Form Submit Full Page Reload
if (filterForm) {
    filterForm.addEventListener('submit', function (e) {
        e.preventDefault();
        fetchCatalogData();
    });
}

// 4. Intercept AJAX Pagination Link Clicks
if (paginationWrapper) {
    paginationWrapper.addEventListener('click', function (e) {
        const pageLink = e.target.closest('a.page-link');
        if (pageLink && pageLink.getAttribute('href') && pageLink.getAttribute('href') !== 'javascript:void(0)') {
            e.preventDefault();
            fetchCatalogData(pageLink.getAttribute('href'));
        }
    });
}
</script>
@endsection