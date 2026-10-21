@extends('layouts.admin')

@section('title', 'New Assign Product - ' . config('app.name'))

@section('admin_content')
<div class="container-fluid py-3 px-2 px-md-3">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-dark mb-0">New Assign Product</h5>
            <a href="{{ route('admin.branch.assign.history') }}" class="btn btn-warning btn-sm fw-bold text-white px-3">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('admin.branch.assign.store') }}" method="POST" id="assignProductForm">
                @csrf

                <!-- Date & Branch -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Date <span class="text-danger">*</span></label>
                        <input type="date" name="transfer_date" class="form-control rounded-2" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Branch <span class="text-danger">*</span></label>
                        <select name="branch_id" id="assignBranchDropdown" class="form-select rounded-2" required>
                            <option value="" disabled selected>Select Branch</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- সার্চ বক্স ও ড্রপডাউন সিলেকশন এরিয়া -->
                <div class="card border p-3 mb-4 rounded-3 bg-light">
                    <!-- অপশন ১: লাইভ সার্চ -->
                    <div class="position-relative mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">
                            <i class="fas fa-search me-1 text-primary"></i> Real-time Live Search (Type Name, SKU, ID or Category)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="productLiveSearchInput" class="form-control border-start-0" 
                                   placeholder="Type product name, SKU, product ID or category name..." autocomplete="off">
                            <span id="productSearchSpinner" class="input-group-text bg-white border-start-0 d-none">
                                <span class="spinner-border spinner-border-sm text-primary"></span>
                            </span>
                        </div>

                        <!-- লাইভ সার্চ ড্রপডাউন রেজাল্ট -->
                        <div id="searchResultsList" class="position-absolute start-0 end-0 bg-white border rounded-3 shadow-lg mt-1 d-none" 
                             style="top: 100%; z-index: 1050; max-height: 250px; overflow-y: auto;">
                        </div>
                    </div>

                    <div class="text-center text-muted small fw-bold mb-2">--- OR SELECT MANUALLY ---</div>

                    <!-- অপশন ২: ম্যানুয়াল সিলেক্ট ড্রপডাউন -->
                    <div>
                        <label class="form-label small fw-bold text-dark mb-1">
                            <i class="fas fa-list me-1 text-secondary"></i> Select Product from Dropdown
                        </label>
                        <div class="d-flex flex-column flex-md-row gap-2">
                            <select id="productSelectDropdown" class="form-select rounded-2">
                                <option value="" disabled selected>-- Browse & Select Product --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" 
                                            data-title="{{ $p->title }}" 
                                            data-code="{{ $p->product_code ?? 'N/A' }}" 
                                            data-cat="{{ $p->category_name ?? 'General' }}" 
                                            data-stock="{{ $p->stock }}">
                                        {{ $p->title }} | SKU: {{ $p->product_code ?? 'N/A' }} | Cat: {{ $p->category_name ?? 'General' }} (Available Stock: {{ $p->stock }})
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-dark text-white fw-bold px-4 flex-shrink-0" id="addItemBtn">Add Item</button>
                        </div>
                    </div>
                </div>

                <!-- Selected Items Table -->
                <div class="table-responsive border rounded-3 mb-4">
                    <table class="table align-middle mb-0 small text-nowrap">
                        <thead class="bg-light text-muted text-uppercase">
                            <tr>
                                <th class="ps-3">PRODUCT</th>
                                <th>CATEGORY</th>
                                <th>CODE / SKU</th>
                                <th>CURRENT MAIN STOCK</th>
                                <th style="width: 170px;">ASSIGN QUANTITY</th>
                                <th class="text-end pe-3">ACTION</th>
                            </tr>
                        </thead>
                        <tbody id="assignedItemsTableBody">
                            <tr id="emptyPlaceholderRow">
                                <td colspan="6" class="text-center py-4 text-muted">No products added</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">Transfer Notes (Optional)</label>
                    <textarea name="notes" rows="2" class="form-control rounded-2" placeholder=" transfer via delivery van"></textarea>
                </div>

                <button type="submit" class="btn btn-warning fw-bold text-white px-4" id="submitAssignBtn" disabled>Assign Products</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal: সতর্কতা নোটিস -->
<div class="modal fade" id="assignNoticeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow text-center p-4">
            <div class="text-warning mb-2">
                <i class="fas fa-exclamation-circle fa-3x"></i>
            </div>
            <h6 class="fw-bold text-dark mb-1" id="assignNoticeTitle">Notice</h6>
            <p class="text-muted small mb-3" id="assignNoticeMsg"></p>
            <button type="button" class="btn btn-sm btn-dark rounded-pill px-4" data-bs-dismiss="modal">OK</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('productLiveSearchInput');
    const searchSpinner = document.getElementById('productSearchSpinner');
    const resultsList = document.getElementById('searchResultsList');
    const manualDropdown = document.getElementById('productSelectDropdown');
    const addItemBtn = document.getElementById('addItemBtn');
    const tableBody = document.getElementById('assignedItemsTableBody');
    const submitBtn = document.getElementById('submitAssignBtn');
    const emptyRow = document.getElementById('emptyPlaceholderRow');
    const noticeModal = new bootstrap.Modal(document.getElementById('assignNoticeModal'));

    const addedProductIds = new Set();
    let debounceTimer;

    function showNotice(title, msg) {
        document.getElementById('assignNoticeTitle').textContent = title;
        document.getElementById('assignNoticeMsg').textContent = msg;
        noticeModal.show();
    }

    // টেবিলের রো তৈরির কমন ফাংশন
    function appendProductToTable(id, title, code, cat, stock) {
        if (addedProductIds.has(String(id))) {
            showNotice('Duplicate Item', 'This product is already added in the list.');
            return;
        }

        if (stock <= 0) {
            showNotice('Low Stock Warning', 'Notice: Main central stock is 0 for this item!');
        }

        if (emptyRow) emptyRow.style.display = 'none';

        addedProductIds.add(String(id));

        const tr = document.createElement('tr');
        tr.id = 'rowProd' + id;
        tr.innerHTML = `
            <td class="ps-3 fw-bold text-dark text-wrap" style="min-width: 200px;">
                ${title}
                <input type="hidden" name="product_ids[]" value="${id}">
            </td>
            <td><span class="badge bg-light text-dark border">${cat}</span></td>
            <td><code>${code}</code></td>
            <td><span class="badge ${stock > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'} border fs-6">${stock}</span></td>
            <td>
                <input type="number" name="quantities[]" class="form-control form-control-sm rounded-2 text-center fw-bold" 
                       value="1" min="1" max="${stock > 0 ? stock : 9999}" required>
            </td>
            <td class="text-end pe-3">
                <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn rounded-circle px-2 py-1"><i class="fas fa-times"></i></button>
            </td>
        `;

        tableBody.appendChild(tr);
        submitBtn.removeAttribute('disabled');

        tr.querySelector('.remove-item-btn').addEventListener('click', function () {
            tr.remove();
            addedProductIds.delete(String(id));
            if (addedProductIds.size === 0) {
                emptyRow.style.display = 'table-row';
                submitBtn.setAttribute('disabled', 'disabled');
            }
        });
    }

    // ১. ম্যানুয়াল ড্রপডাউন দিয়ে অ্যাড
    addItemBtn.addEventListener('click', function () {
        const prodId = manualDropdown.value;
        if (!prodId) {
            showNotice('Selection Required', 'Please select a product from dropdown first.');
            return;
        }

        const opt = manualDropdown.options[manualDropdown.selectedIndex];
        appendProductToTable(
            prodId,
            opt.getAttribute('data-title'),
            opt.getAttribute('data-code'),
            opt.getAttribute('data-cat'),
            parseInt(opt.getAttribute('data-stock')) || 0
        );
        manualDropdown.value = '';
    });

    // ২. লাইভ সার্চ ফেচ
    function fetchLiveProducts(query = '') {
        searchSpinner.classList.remove('d-none');

        fetch("{{ route('admin.branch.search.products') }}?q=" + encodeURIComponent(query), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(products => {
            if (products.length === 0) {
                resultsList.innerHTML = `<div class="p-3 text-center text-muted small">No products found matching "${query}"</div>`;
            } else {
                let html = '<div class="list-group list-group-flush small">';
                products.forEach(p => {
                    const sku = p.product_code ? p.product_code : 'N/A';
                    const cat = p.category_name ? p.category_name : 'General';
                    const isAdded = addedProductIds.has(String(p.id));

                    html += `
                        <a href="javascript:void(0)" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center select-search-item ${isAdded ? 'bg-light text-muted disabled' : ''}"
                           data-id="${p.id}" 
                           data-title="${p.title.replace(/"/g, '&quot;')}" 
                           data-code="${sku}" 
                           data-cat="${cat}" 
                           data-stock="${p.stock}">
                            <div>
                                <strong class="d-block text-dark">${p.title}</strong>
                                <span class="text-muted small">ID: #${p.id} | SKU: <code>${sku}</code> | Cat: <strong>${cat}</strong></span>
                            </div>
                            <div class="text-end">
                                <span class="badge ${p.stock > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'} border">
                                    Stock: ${p.stock}
                                </span>
                                ${isAdded ? '<span class="d-block small text-primary fw-bold mt-1">Added</span>' : ''}
                            </div>
                        </a>
                    `;
                });
                html += '</div>';
                resultsList.innerHTML = html;
            }
            resultsList.classList.remove('d-none');
        })
        .catch(err => console.error(err))
        .finally(() => {
            searchSpinner.classList.add('d-none');
        });
    }

    searchInput.addEventListener('input', function () {
        const val = this.value.trim();
        clearTimeout(debounceTimer);

        if (val.length === 0) {
            resultsList.classList.add('d-none');
            return;
        }

        debounceTimer = setTimeout(() => {
            fetchLiveProducts(val);
        }, 250);
    });

    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !resultsList.contains(e.target)) {
            resultsList.classList.add('d-none');
        }
    });

    // ৩. লাইভ সার্চ রেজাল্ট থেকে ক্লিক
    resultsList.addEventListener('click', function (e) {
        const item = e.target.closest('.select-search-item');
        if (!item || item.classList.contains('disabled')) return;

        appendProductToTable(
            item.getAttribute('data-id'),
            item.getAttribute('data-title'),
            item.getAttribute('data-code'),
            item.getAttribute('data-cat'),
            parseInt(item.getAttribute('data-stock')) || 0
        );

        resultsList.classList.add('d-none');
        searchInput.value = '';
    });
});
</script>
@endsection