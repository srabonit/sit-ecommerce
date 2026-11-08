@extends('layouts.admin')

@section('title', 'Create Goods Received Note (GRN)')

@section('admin_content')
<div class="container-fluid px-3 py-3 grn-create-page">

    {{-- Breadcrumb & Action Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 grn-page-header">
        <div>
            <h5 class="fw-bold text-dark mb-2 grn-page-title">
                <i class="fas fa-truck-ramp-box text-success me-2"></i>Create Goods Received Note (GRN / Inward Receipt)
            </h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 grn-breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.purchase-receipts.index') }}">Purchase Receipts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">New GRN</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.purchase-receipts.index') }}" class="btn btn-sm btn-outline-secondary grn-action-btn">
                <i class="fas fa-arrow-left me-1"></i> Back to Receipts
            </a>
        </div>
    </div>

    {{-- Validation Error Alerts --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3 grn-validation-alert">
            <div class="d-flex align-items-center gap-2 fw-semibold mb-2">
                <i class="fas fa-circle-exclamation"></i> Receipt creation encountered errors:
            </div>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.purchase-receipts.store') }}" method="POST" enctype="multipart/form-data" id="grnForm">
        @csrf

        {{-- Sourcing & Gate Entry Header --}}
        <div class="card border-0 shadow-sm mb-4 grn-section-card">
            <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <h6 class="fw-bold mb-0 text-dark grn-section-title">
                    <i class="fas fa-file-contract text-success me-2"></i>Consignment Logistics &amp; Inward PO Sourcing
                </h6>

                {{-- Link Purchase Order --}}
                @if(isset($purchaseOrders) && $purchaseOrders->count() > 0)
                    <div class="d-flex flex-wrap align-items-center gap-2 grn-po-import">
                        <label for="poSelector" class="small fw-semibold text-secondary mb-0">Import Order:</label>
                        <select id="poSelector" class="form-select form-select-sm grn-po-selector">
                            <option value="">Select Open Purchase Order (PO)</option>
                            @foreach($purchaseOrders as $po)
                                <option value="{{ $po->id }}" data-supplier="{{ $po->supplier_id }}" data-warehouse="{{ $po->warehouse_id }}" {{ request('po_id') == $po->id ? 'selected' : '' }}>
                                    #{{ $po->po_number }} &mdash; {{ $po->supplier->name ?? 'Vendor' }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-sm btn-outline-success grn-action-btn" id="pullPoItemsBtn">
                            <i class="fas fa-download me-1"></i> Pull Items
                        </button>
                    </div>
                @endif
            </div>

            <div class="card-body p-3 p-lg-4">
                <div class="row g-3">
                    <input type="hidden" name="purchase_order_id" id="purchase_order_id" value="{{ old('purchase_order_id', request('po_id')) }}">

                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold small">Supplier / Delivering Vendor <span class="text-danger">*</span></label>
                        <select name="supplier_id" id="supplier_id" class="form-select form-select-sm" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id', request('supplier_id')) == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }} {{ $supplier->company_name ? "({$supplier->company_name})" : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold small">GRN Number <span class="text-danger">*</span></label>
                        <input type="text" name="receipt_number" class="form-control form-control-sm font-monospace" value="{{ old('receipt_number', 'GRN-' . date('Ymd') . '-' . strtoupper(Str::random(4))) }}" required>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold small">Receiving Date <span class="text-danger">*</span></label>
                        <input type="date" name="receipt_date" class="form-control form-control-sm" value="{{ old('receipt_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold small">Supplier Delivery Challan # <span class="text-danger">*</span></label>
                        <input type="text" name="challan_no" class="form-control form-control-sm" placeholder="CHN-88401" value="{{ old('challan_no') }}" required>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label fw-semibold small">Inspection / QC Status <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select form-select-sm" required>
                            <option value="received" {{ old('status', 'received') === 'received' ? 'selected' : '' }}>Inspected &amp; Stock Accepted</option>
                            <option value="pending_qc" {{ old('status') === 'pending_qc' ? 'selected' : '' }}>Quarantine / Pending QC</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small">Receiving Warehouse <span class="text-danger">*</span></label>
                        <select name="warehouse_id" id="warehouse_id" class="form-select form-select-sm" required>
                            <option value="">Select Warehouse</option>
                            @foreach($warehouses ?? [] as $wh)
                                <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small">Operating Branch</label>
                        <select name="branch_id" class="form-select form-select-sm">
                            <option value="">HQ Branch</option>
                            @foreach($branches ?? [] as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small">Vehicle / Transporter Info</label>
                        <input type="text" name="vehicle_number" class="form-control form-control-sm" placeholder="Truck DHAKA-METRO-TA-1102" value="{{ old('vehicle_number') }}">
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label fw-semibold small">Challan Scan / QC Report</label>
                        <input type="file" name="attachment_file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold small">Receiving &amp; Gate Entry Remarks</label>
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Package condition, seal numbers, driver remarks..." value="{{ old('notes') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Consignment Line Items & Quality Inspection Table --}}
        <div class="card border-0 shadow-sm mb-3 grn-section-card">
            <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="grn-inspection-heading">
                    <h6 class="fw-bold mb-1 text-dark grn-section-title">
                        <i class="fas fa-boxes-packing text-success me-2"></i>Physical Inspection &amp; Stock Admission
                    </h6>
                    <small class="text-muted grn-section-description">Only accepted quantities are credited to warehouse inventory. Rejected quantities remain quarantined for vendor return (RTV).</small>
                </div>
                <button type="button" class="btn btn-sm btn-success grn-action-btn" id="addItemRowBtn">
                    <i class="fas fa-plus me-1"></i> Add Item Line
                </button>
            </div>

            <div class="table-responsive grn-items-table-wrap">
                <table class="table table-bordered align-middle mb-0 grn-items-table" id="itemsTable" style="font-size: 12px;">
                    <thead class="table-light text-secondary text-uppercase" style="font-size: 10.5px; letter-spacing: 0.5px;">
                        <tr>
                            <th style="min-width: 220px;">Product Description <span class="text-danger">*</span></th>
                            <th style="width: 120px;">SKU / Code</th>
                            <th style="width: 110px;">Batch / Lot #</th>
                            <th style="width: 120px;">Expiry Date</th>
                            <th class="text-center" style="width: 100px;">Delivered Qty <span class="text-danger">*</span></th>
                            <th class="text-center" style="width: 100px;">Accepted Qty <span class="text-danger">*</span></th>
                            <th class="text-center" style="width: 95px;">Rejected Qty</th>
                            <th style="min-width: 140px;">QC / Defect Remarks</th>
                            <th class="text-center" style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsContainer">
                        {{-- Injected dynamically --}}
                    </tbody>
                </table>
            </div>

            {{-- Summary Breakdown --}}
            <div class="card-body p-3 p-lg-4 bg-light border-top">
                <div class="row g-3 justify-content-between align-items-center">
                    <div class="col-12 col-md-6">
                        <div class="p-3 bg-white border rounded small text-secondary grn-integrity-note">
                            <div class="fw-bold text-dark mb-2"><i class="fas fa-shield-halved text-success me-1"></i> Inventory Integrity Rule:</div>
                            Approved accepted stock updates the real-time stock ledger and assigns designated batch numbers. Any rejected surplus is documented for issuing an immediate Return to Vendor (RTV).
                        </div>
                    </div>

                    <div class="col-12 col-md-5 col-lg-4">
                        <div class="card border p-3 shadow-none bg-white grn-summary-card">
                            <div class="d-flex justify-content-between align-items-center mb-2 small gap-2">
                                <span class="text-muted">Total Delivered (Challan):</span>
                                <span class="fw-bold text-dark" id="displayDeliveredTotal">0.00</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2 small gap-2">
                                <span class="text-muted">Accepted into Stock:</span>
                                <span class="fw-bold text-success fs-6" id="displayAcceptedTotal">0.00</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center small gap-2">
                                <span class="text-muted">Rejected / Quarantined:</span>
                                <span class="fw-bold text-danger fs-6" id="displayRejectedTotal">0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-end gap-2">
                <a href="{{ route('admin.purchase-receipts.index') }}" class="btn btn-sm btn-secondary grn-action-btn">Cancel</a>
                <button type="submit" class="btn btn-sm btn-success px-4 grn-action-btn">
                    <i class="fas fa-check-circle me-1"></i> Save &amp; Post Inward GRN
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Row Template --}}
<template id="rowTemplate">
    <tr class="item-row">
        <td>
            <input type="text" class="form-control form-control-sm product-search-input mb-1" placeholder="Product name or SKU..." autocomplete="off">
            <input type="hidden" name="items[INDEX][product_id]" class="product-id-input" required>
            <input type="hidden" name="items[INDEX][variant_id]" class="variant-id-input">
            <input type="hidden" name="items[INDEX][po_item_id]" class="po-item-id-input">
            <small class="text-muted product-name-display d-block"></small>
        </td>
        <td>
            <input type="text" name="items[INDEX][sku]" class="form-control form-control-sm sku-input" placeholder="SKU">
        </td>
        <td>
            <input type="text" name="items[INDEX][batch_no]" class="form-control form-control-sm batch-input" placeholder="Batch #">
        </td>
        <td>
            <input type="date" name="items[INDEX][expiry_date]" class="form-control form-control-sm expiry-input">
        </td>
        <td>
            <input type="number" step="any" name="items[INDEX][delivered_quantity]" class="form-control form-control-sm text-center delivered-qty-input fw-bold" value="1" min="0.01" required>
        </td>
        <td>
            <input type="number" step="any" name="items[INDEX][accepted_quantity]" class="form-control form-control-sm text-center accepted-qty-input fw-bold text-success" value="1" min="0" required>
        </td>
        <td>
            <input type="number" step="any" name="items[INDEX][rejected_quantity]" class="form-control form-control-sm text-center rejected-qty-input fw-bold text-danger bg-light" value="0" readonly>
        </td>
        <td>
            <input type="text" name="items[INDEX][remarks]" class="form-control form-control-sm" placeholder="Broken seal, QC passed">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-row-btn" title="Remove row">
                <i class="fas fa-trash-can"></i>
            </button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let rowIndex = 0;
    const container = document.getElementById('itemsContainer');
    const template = document.getElementById('rowTemplate').innerHTML;
    const addItemBtn = document.getElementById('addItemRowBtn');
    const poSelector = document.getElementById('poSelector');
    const pullPoBtn = document.getElementById('pullPoItemsBtn');
    const supplierSelect = document.getElementById('supplier_id');
    const warehouseSelect = document.getElementById('warehouse_id');

    function addRow(prefill = null) {
        const rowHtml = template.replace(/INDEX/g, rowIndex);
        container.insertAdjacentHTML('beforeend', rowHtml);
        const currentRow = container.lastElementChild;
        rowIndex++;

        setupRowEvents(currentRow);

        if (prefill) {
            currentRow.querySelector('.product-id-input').value = prefill.product_id || '';
            currentRow.querySelector('.variant-id-input').value = prefill.variant_id || '';
            currentRow.querySelector('.po-item-id-input').value = prefill.po_item_id || '';
            currentRow.querySelector('.sku-input').value = prefill.sku || '';
            currentRow.querySelector('.batch-input').value = prefill.batch_no || '';
            currentRow.querySelector('.expiry-input').value = prefill.expiry_date || '';

            const delivered = parseFloat(prefill.delivered_quantity ?? prefill.quantity) || 1;
            const accepted = parseFloat(prefill.accepted_quantity ?? delivered) || delivered;
            const rejected = Math.max(0, delivered - accepted);

            currentRow.querySelector('.delivered-qty-input').value = delivered;
            currentRow.querySelector('.accepted-qty-input').value = accepted;
            currentRow.querySelector('.rejected-qty-input').value = rejected;

            currentRow.querySelector('.product-name-display').innerText = prefill.product_name || prefill.name || '';
            currentRow.querySelector('.product-search-input').value = prefill.product_name || prefill.name || '';
        }

        calculateTotals();
    }

    function setupRowEvents(row) {
        const deliveredInput = row.querySelector('.delivered-qty-input');
        const acceptedInput = row.querySelector('.accepted-qty-input');
        const rejectedInput = row.querySelector('.rejected-qty-input');

        function updateRowRejection() {
            const delivered = parseFloat(deliveredInput.value) || 0;
            let accepted = parseFloat(acceptedInput.value) || 0;

            if (accepted > delivered) {
                accepted = delivered;
                acceptedInput.value = accepted;
            }

            const rejected = Math.max(0, delivered - accepted);
            rejectedInput.value = rejected.toFixed(2);
            calculateTotals();
        }

        deliveredInput.addEventListener('input', updateRowRejection);
        acceptedInput.addEventListener('input', updateRowRejection);

        row.querySelector('.remove-row-btn').addEventListener('click', function () {
            if (container.children.length > 1) {
                row.remove();
                calculateTotals();
            } else {
                alert('A goods receipt note must have at least one product line.');
            }
        });

        const searchInput = row.querySelector('.product-search-input');
        searchInput.addEventListener('change', function () {
            if (!row.querySelector('.product-id-input').value) {
                row.querySelector('.product-id-input').value = 1;
            }
            row.querySelector('.product-name-display').innerText = searchInput.value;
            calculateTotals();
        });
    }

    function calculateTotals() {
        let totalDelivered = 0;
        let totalAccepted = 0;
        let totalRejected = 0;

        const rows = container.querySelectorAll('.item-row');
        rows.forEach(row => {
            const delivered = parseFloat(row.querySelector('.delivered-qty-input').value) || 0;
            const accepted = parseFloat(row.querySelector('.accepted-qty-input').value) || 0;
            const rejected = parseFloat(row.querySelector('.rejected-qty-input').value) || 0;

            totalDelivered += delivered;
            totalAccepted += accepted;
            totalRejected += rejected;
        });

        document.getElementById('displayDeliveredTotal').innerText = totalDelivered.toFixed(2);
        document.getElementById('displayAcceptedTotal').innerText = totalAccepted.toFixed(2);
        document.getElementById('displayRejectedTotal').innerText = totalRejected.toFixed(2);
    }

    addItemBtn.addEventListener('click', () => addRow());

    // Import from Purchase Order
    if (pullPoBtn && poSelector) {
        pullPoBtn.addEventListener('click', function () {
            const poId = poSelector.value;
            if (!poId) {
                alert('Please select an open Purchase Order first.');
                return;
            }

            const selectedOption = poSelector.options[poSelector.selectedIndex];
            const supplierId = selectedOption.getAttribute('data-supplier');
            const warehouseId = selectedOption.getAttribute('data-warehouse');

            if (supplierId && supplierSelect) supplierSelect.value = supplierId;
            if (warehouseId && warehouseSelect) warehouseSelect.value = warehouseId;
            document.getElementById('purchase_order_id').value = poId;

            fetch(`{{ url('admin/procurement/purchases/po-items') }}/${poId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data && res.data.length > 0) {
                    container.innerHTML = '';
                    rowIndex = 0;
                    res.data.forEach(item => {
                        const remainingToReceive = Math.max(0, (item.quantity || 0) - (item.received_quantity || 0));
                        addRow({
                            product_id: item.product_id,
                            variant_id: item.variant_id,
                            po_item_id: item.id,
                            product_name: item.product ? item.product.name : item.product_name,
                            sku: item.sku,
                            delivered_quantity: remainingToReceive > 0 ? remainingToReceive : item.quantity,
                            accepted_quantity: remainingToReceive > 0 ? remainingToReceive : item.quantity
                        });
                    });
                } else {
                    alert('Could not pull open order items.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Failed to connect to the server.');
            });
        });
    }

    @if(request('po_id'))
        if (pullPoBtn) pullPoBtn.click();
    @else
        addRow();
    @endif
});
</script>

<style>
    .grn-create-page {
        color: #344054;
    }

    .grn-create-page .grn-page-header {
        padding: 2px 0 4px;
    }

    .grn-create-page .grn-page-title {
        font-size: 1.15rem;
        line-height: 1.5;
    }

    .grn-create-page .grn-breadcrumb {
        font-size: 12px;
    }

    .grn-create-page .grn-section-card {
        border: 1px solid #e8ecf1 !important;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
    }

    .grn-create-page .grn-section-card > .card-header {
        min-height: 52px;
    }

    .grn-create-page .grn-section-title {
        font-size: 14px;
        line-height: 1.5;
    }

    .grn-create-page .grn-section-description {
        display: block;
        font-size: 11.5px;
        line-height: 1.6;
        max-width: 780px;
    }

    .grn-create-page .grn-action-btn {
        border-radius: 6px;
        font-weight: 600;
        padding: 7px 12px;
        transition: all .2s ease;
    }

    .grn-create-page .grn-action-btn:focus-visible {
        outline: 3px solid rgba(25, 135, 84, .18);
        outline-offset: 2px;
    }

    .grn-create-page .grn-po-import {
        max-width: 100%;
    }

    .grn-create-page .grn-po-selector {
        width: 250px;
        max-width: 100%;
        border-radius: 6px;
    }

    .grn-create-page .form-label {
        color: #475467;
        margin-bottom: 6px;
    }

    .grn-create-page .form-control,
    .grn-create-page .form-select {
        min-height: 36px;
        border-color: #dfe3e8;
        border-radius: 6px;
    }

    .grn-create-page .form-control:focus,
    .grn-create-page .form-select:focus {
        border-color: var(--bs-primary, #0d6efd);
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .08);
    }

    .grn-create-page .grn-validation-alert {
        border-radius: 8px;
    }

    .grn-create-page .grn-items-table-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .grn-create-page .grn-items-table {
        min-width: 1120px;
    }

    .grn-create-page .grn-items-table thead th {
        padding: 12px 10px;
        white-space: nowrap;
        vertical-align: middle;
        font-weight: 700;
        background: #f8fafc;
        border-bottom-width: 1px;
    }

    .grn-create-page .grn-items-table tbody td {
        padding: 10px;
        vertical-align: middle;
        background: #fff;
    }

    .grn-create-page .grn-items-table tbody tr:hover td {
        background: #fbfcfe;
    }

    .grn-create-page .grn-items-table .form-control {
        min-width: 0;
        font-size: 12px;
    }

    .grn-create-page .grn-items-table .product-search-input {
        min-width: 190px;
    }

    .grn-create-page .grn-items-table .sku-input,
    .grn-create-page .grn-items-table .batch-input {
        min-width: 95px;
    }

    .grn-create-page .grn-items-table .expiry-input {
        min-width: 125px;
    }

    .grn-create-page .grn-items-table .delivered-qty-input,
    .grn-create-page .grn-items-table .accepted-qty-input,
    .grn-create-page .grn-items-table .rejected-qty-input {
        min-width: 80px;
    }

    .grn-create-page .grn-items-table .remove-row-btn {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        text-decoration: none;
    }

    .grn-create-page .grn-items-table .remove-row-btn:hover {
        background: #fff1f2;
    }

    .grn-create-page .product-name-display {
        overflow-wrap: anywhere;
    }

    .grn-create-page .grn-integrity-note {
        border-color: #e5eaf0 !important;
        border-radius: 7px;
        line-height: 1.7;
    }

    .grn-create-page .grn-summary-card {
        border-color: #e5eaf0 !important;
        border-radius: 8px;
    }

    .grn-create-page .card-footer {
        gap: 8px;
    }

    @media (max-width: 991.98px) {
        .grn-create-page .grn-page-header {
            align-items: flex-start !important;
        }

        .grn-create-page .grn-po-import {
            width: 100%;
        }

        .grn-create-page .grn-po-selector {
            flex: 1 1 220px;
        }
    }

    @media (max-width: 767.98px) {
        .grn-create-page {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .grn-create-page .grn-page-header {
            margin-bottom: 1rem !important;
        }

        .grn-create-page .grn-page-title {
            font-size: 1.05rem;
        }

        .grn-create-page .grn-section-card > .card-header {
            align-items: flex-start !important;
        }

        .grn-create-page .grn-po-import {
            display: grid !important;
            grid-template-columns: auto minmax(0, 1fr);
            width: 100%;
        }

        .grn-create-page .grn-po-import label {
            grid-column: 1 / -1;
        }

        .grn-create-page .grn-po-selector {
            width: 100%;
            min-width: 0;
        }

        .grn-create-page .grn-po-import .grn-action-btn {
            width: 100%;
        }

        .grn-create-page .grn-inspection-heading {
            width: 100%;
        }

        .grn-create-page #addItemRowBtn {
            width: 100%;
        }

        .grn-create-page .grn-items-table {
            min-width: 1120px;
        }

        .grn-create-page .grn-summary-card {
            width: 100%;
        }

        .grn-create-page .card-footer {
            justify-content: stretch !important;
        }

        .grn-create-page .card-footer > * {
            flex: 1 1 auto;
            text-align: center;
        }
    }

    @media (max-width: 420px) {
        .grn-create-page .grn-page-title {
            font-size: 1rem;
        }

        .grn-create-page .grn-po-import {
            grid-template-columns: 1fr;
        }

        .grn-create-page .grn-po-import label {
            grid-column: auto;
        }

        .grn-create-page .grn-section-title {
            font-size: 13px;
        }

        .grn-create-page .card-footer {
            flex-direction: column;
        }

        .grn-create-page .card-footer > * {
            width: 100%;
        }
    }
</style>
@endsection