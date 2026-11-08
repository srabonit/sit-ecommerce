@extends('layouts.admin')

@section('title', 'Create Purchase Return (RTV)')

@section('admin_content')
<div class="container-fluid px-3 py-3 purchase-return-create-page">

    {{-- Breadcrumb & Action Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h5 class="fw-bold text-dark mb-2">
                <i class="fas fa-arrow-rotate-left text-danger me-2"></i>Create Purchase Return (Return to Vendor - RTV)
            </h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.purchase-returns.index') }}">Purchase Returns</a></li>
                    <li class="breadcrumb-item active" aria-current="page">New Return</li>
                </ol>
            </nav>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.purchase-returns.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Returns
            </a>
        </div>
    </div>

    {{-- Validation Error Alerts --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3 prc-alert">
            <div class="d-flex align-items-center gap-2 fw-semibold mb-1">
                <i class="fas fa-circle-exclamation"></i> Submission encountered errors:
            </div>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.purchase-returns.store') }}" method="POST" enctype="multipart/form-data" id="purchaseReturnForm">
        @csrf

        {{-- Sourcing & Reference Header --}}
        <div class="card prc-section-card mb-4">
            <div class="card-header prc-section-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-file-contract text-danger me-2"></i>Return Particulars &amp; Sourcing Link
                </h6>

                {{-- Quick Link Bill or GRN --}}
                <div class="d-flex flex-wrap align-items-center gap-2 prc-import-controls">
                    @if(isset($purchases) && $purchases->count() > 0)
                        <div class="input-group input-group-sm prc-import-group">
                            <select id="linkBillSelector" class="form-select form-select-sm">
                                <option value="">Import from Purchase Bill</option>
                                @foreach($purchases as $bill)
                                    <option value="{{ $bill->id }}" 
                                            data-supplier="{{ $bill->supplier_id }}" 
                                            data-warehouse="{{ $bill->warehouse_id }}"
                                            {{ request('purchase_id') == $bill->id ? 'selected' : '' }}>
                                        #{{ $bill->purchase_number }} &mdash; {{ $bill->supplier->name ?? 'Vendor' }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-danger" id="pullBillItemsBtn">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    @endif

                    @if(isset($purchaseReceipts) && $purchaseReceipts->count() > 0)
                        <div class="input-group input-group-sm prc-import-group">
                            <select id="linkGrnSelector" class="form-select form-select-sm">
                                <option value="">Import from Inward GRN</option>
                                @foreach($purchaseReceipts as $grn)
                                    <option value="{{ $grn->id }}" 
                                            data-supplier="{{ $grn->supplier_id }}" 
                                            data-warehouse="{{ $grn->warehouse_id }}"
                                            {{ request('grn_id') == $grn->id ? 'selected' : '' }}>
                                        #{{ $grn->receipt_number }} &mdash; {{ $grn->supplier->name ?? 'Vendor' }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-success" id="pullGrnItemsBtn">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card-body p-3 p-lg-4">
                <div class="row g-3">
                    <input type="hidden" name="purchase_id" id="purchase_id" value="{{ old('purchase_id', request('purchase_id')) }}">
                    <input type="hidden" name="purchase_receipt_id" id="purchase_receipt_id" value="{{ old('purchase_receipt_id', request('grn_id')) }}">

                    {{-- Supplier Selector --}}
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label fw-semibold small">Supplier / Target Vendor <span class="text-danger">*</span></label>
                        <select name="supplier_id" id="supplier_id" class="form-select form-select-sm" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" 
                                        data-balance="{{ (float) ($supplier->current_balance ?? 0) }}"
                                        {{ old('supplier_id', request('supplier_id')) == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }} {{ $supplier->company_name ? "({$supplier->company_name})" : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="small mt-2 text-muted">
                            Outstanding Ledger Due:
                            <span class="fw-bold text-danger" id="supplierBalanceNotice">৳0.00</span>
                        </div>
                    </div>

                    {{-- Return Number --}}
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                        <label class="form-label fw-semibold small">Return / RTV # <span class="text-danger">*</span></label>
                        <input type="text" name="return_number" class="form-control form-control-sm font-monospace" value="{{ old('return_number', 'RTV-' . date('Ymd') . '-' . strtoupper(Str::random(4))) }}" required>
                    </div>

                    {{-- Return Date --}}
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                        <label class="form-label fw-semibold small">Return Date <span class="text-danger">*</span></label>
                        <input type="date" name="return_date" class="form-control form-control-sm" value="{{ old('return_date', date('Y-m-d')) }}" required>
                    </div>

                    {{-- Warehouse --}}
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                        <label class="form-label fw-semibold small">Dispatch Warehouse <span class="text-danger">*</span></label>
                        <select name="warehouse_id" id="warehouse_id" class="form-select form-select-sm" required>
                            <option value="">Select Warehouse</option>
                            @foreach($warehouses ?? [] as $wh)
                                <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Operating Branch --}}
                    <div class="col-12 col-sm-6 col-md-3 col-lg-2">
                        <label class="form-label fw-semibold small">Branch</label>
                        <select name="branch_id" class="form-select form-select-sm">
                            <option value="">HQ Branch</option>
                            @foreach($branches ?? [] as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Settlement Mode --}}
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label fw-semibold small">Settlement Mode <span class="text-danger">*</span></label>
                        <select name="settlement_type" id="settlement_type" class="form-select form-select-sm" required>
                            <option value="credit_note" {{ old('settlement_type', 'credit_note') === 'credit_note' ? 'selected' : '' }}>Credit Note / Supplier Due Deduction</option>
                            <option value="cash_refund" {{ old('settlement_type') === 'cash_refund' ? 'selected' : '' }}>Cash Refund</option>
                            <option value="bank_refund" {{ old('settlement_type') === 'bank_refund' ? 'selected' : '' }}>Bank Wire Refund</option>
                            <option value="replacement" {{ old('settlement_type') === 'replacement' ? 'selected' : '' }}>Goods Replacement</option>
                        </select>
                    </div>

                    {{-- Refund Account (Conditional for cash/bank refund) --}}
                    <div class="col-12 col-md-6 col-lg-3" id="refundAccountCol" style="display: none;">
                        <label class="form-label fw-semibold small">Deposit Refund Into Account</label>
                        <select name="bank_account_id" id="bank_account_id" class="form-select form-select-sm">
                            <option value="">Select Account</option>
                            @foreach($bankAccounts ?? [] as $acc)
                                <option value="{{ $acc->id }}" {{ old('bank_account_id') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->account_name }} &mdash; {{ $acc->bank_name ?? 'Cash Account' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Gate Pass / Challan Ref --}}
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label fw-semibold small">Outward Gate Pass / Challan Ref</label>
                        <input type="text" name="reference_no" class="form-control form-control-sm" placeholder="GP-OUT-889" value="{{ old('reference_no') }}">
                    </div>

                    {{-- Posting Status --}}
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label fw-semibold small">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select form-select-sm" required>
                            <option value="approved" {{ old('status', 'approved') === 'approved' ? 'selected' : '' }}>Approved &amp; Adjust Stock Immediately</option>
                            <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Draft / Pending Verification</option>
                        </select>
                    </div>

                    {{-- Attachment --}}
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="form-label fw-semibold small">Return Gate Pass Scan / Defect Photos</label>
                        <input type="file" name="attachment_file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                    </div>

                    {{-- Narration --}}
                    <div class="col-12 col-md-6 col-lg-8">
                        <label class="form-label fw-semibold small">General Remarks / Vendor Authorization Memo</label>
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Reason for return, vendor confirmation reference, return transport details..." value="{{ old('notes') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Returned Line Items Table --}}
        <div class="card prc-section-card mb-4">
            <div class="card-header prc-section-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h6 class="fw-bold mb-1 text-dark">
                        <i class="fas fa-boxes-stacked text-danger me-2"></i>Returned Stock Items &amp; Valuation
                    </h6>
                    <small class="text-muted prc-section-description">These quantities will be deducted from your warehouse inventory and credited on the vendor statement.</small>
                </div>
                <button type="button" class="btn btn-sm btn-danger" id="addItemRowBtn">
                    <i class="fas fa-plus me-1"></i> Add Return Line
                </button>
            </div>

            <div class="table-responsive prc-items-responsive">
                <table class="table table-bordered align-middle mb-0 prc-items-table" id="itemsTable">
                    <thead class="table-light text-secondary text-uppercase">
                        <tr>
                            <th style="min-width: 220px;">Product Description <span class="text-danger">*</span></th>
                            <th style="width: 120px;">SKU / Code</th>
                            <th style="width: 110px;">Batch #</th>
                            <th class="text-center" style="width: 95px;">Billed Qty</th>
                            <th class="text-center" style="width: 95px;">Return Qty <span class="text-danger">*</span></th>
                            <th class="text-end" style="width: 110px;">Unit Rate (৳) <span class="text-danger">*</span></th>
                            <th class="text-end" style="width: 90px;">Tax (৳)</th>
                            <th class="text-end" style="width: 130px;">Line Total (৳)</th>
                            <th style="min-width: 140px;">Return Reason <span class="text-danger">*</span></th>
                            <th class="text-center" style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsContainer">
                        {{-- Row entries cloned dynamically --}}
                    </tbody>
                </table>
            </div>

            {{-- Summary & Settlement Section --}}
            <div class="card-body p-3 p-lg-4 bg-light border-top">
                <div class="row g-3 justify-content-between">
                    {{-- Left side: Accounting & Settlement Policy --}}
                    <div class="col-12 col-lg-7">
                        <div class="prc-policy-note p-3 bg-white border rounded small text-secondary">
                            <div class="fw-bold text-dark mb-2">
                                <i class="fas fa-scale-balanced text-danger me-1"></i> Return to Vendor (RTV) Financial Impact:
                            </div>
                            Posting this return automatically deducts inventory and reduces accounts payable due to this supplier. If settlement mode is <em>Credit Note</em>, the claim amount is deducted from the supplier's running ledger.
                        </div>
                    </div>

                    {{-- Right side: Invoice Return Calculations --}}
                    <div class="col-12 col-lg-5 col-xl-4">
                        <div class="card prc-total-card border p-3 shadow-none bg-white">
                            <div class="d-flex justify-content-between mb-2 small">
                                <span class="text-muted">Return Subtotal:</span>
                                <span class="fw-bold text-dark" id="displaySubtotal">৳0.00</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 small gap-2">
                                <span class="text-muted">Return VAT / Tax Reversal (৳):</span>
                                <input type="number" step="0.01" name="tax_amount" id="global_tax" class="form-control form-control-sm text-end" style="width: 110px;" value="{{ old('tax_amount', 0) }}" min="0">
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 small gap-2">
                                <span class="text-muted">Freight / Handling Deduction (৳):</span>
                                <input type="number" step="0.01" name="shipping_cost" id="shipping_cost" class="form-control form-control-sm text-end" style="width: 110px;" value="{{ old('shipping_cost', 0) }}" min="0">
                            </div>

                            <hr class="my-2">

                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <span class="fw-bold text-dark">Total Claim Value:</span>
                                <span class="fw-bold text-danger fs-5 text-nowrap" id="displayGrandTotal">৳0.00</span>
                                <input type="hidden" name="grand_total" id="grand_total" value="0.00">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white border-top p-3 d-flex flex-wrap justify-content-end gap-2">
                <a href="{{ route('admin.purchase-returns.index') }}" class="btn btn-sm btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-sm btn-danger px-4">
                    <i class="fas fa-check-circle me-1"></i> Save &amp; Post Return
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
            <input type="hidden" name="items[INDEX][purchase_item_id]" class="purchase-item-id-input">
            <small class="text-muted product-name-display d-block"></small>
        </td>
        <td>
            <input type="text" name="items[INDEX][sku]" class="form-control form-control-sm sku-input" placeholder="SKU">
        </td>
        <td>
            <input type="text" name="items[INDEX][batch_no]" class="form-control form-control-sm batch-input" placeholder="Batch #">
        </td>
        <td>
            <input type="number" step="any" class="form-control form-control-sm text-center billed-qty-input bg-light" value="0" readonly>
        </td>
        <td>
            <input type="number" step="any" name="items[INDEX][quantity]" class="form-control form-control-sm text-center return-qty-input fw-bold text-danger" value="1" min="0.01" required>
        </td>
        <td>
            <input type="number" step="0.01" name="items[INDEX][unit_cost]" class="form-control form-control-sm text-end cost-input" value="0.00" min="0" required>
        </td>
        <td>
            <input type="number" step="0.01" name="items[INDEX][tax]" class="form-control form-control-sm text-end tax-input" value="0.00" min="0">
        </td>
        <td class="text-end fw-semibold text-danger line-total-display">
            ৳0.00
        </td>
        <td>
            <select name="items[INDEX][reason]" class="form-select form-select-sm reason-select" required>
                <option value="defective">Defective / Damaged</option>
                <option value="expired">Near Expiry / Expired</option>
                <option value="wrong_item">Incorrect Item / Shade Mismatch</option>
                <option value="excess">Excess Supply Over Order</option>
                <option value="qc_rejected">QC Lab Test Rejected</option>
                <option value="other">Other Commercial Reason</option>
            </select>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-row-btn" title="Remove line">
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
    const supplierSelect = document.getElementById('supplier_id');
    const warehouseSelect = document.getElementById('warehouse_id');
    const supplierBalanceSpan = document.getElementById('supplierBalanceNotice');
    const settlementType = document.getElementById('settlement_type');
    const refundCol = document.getElementById('refundAccountCol');

    const globalTaxInput = document.getElementById('global_tax');
    const shippingInput = document.getElementById('shipping_cost');

    // Settlement mode conditional account field
    settlementType.addEventListener('change', function () {
        if (this.value === 'cash_refund' || this.value === 'bank_refund') {
            refundCol.style.display = 'block';
        } else {
            refundCol.style.display = 'none';
        }
    });

    function updateSupplierBalance() {
        const selected = supplierSelect.options[supplierSelect.selectedIndex];
        const balance = parseFloat(selected ? selected.getAttribute('data-balance') : 0) || 0;
        supplierBalanceSpan.innerText = '৳' + balance.toFixed(2);
    }

    function addRow(prefill = null) {
        const rowHtml = template.replace(/INDEX/g, rowIndex);
        container.insertAdjacentHTML('beforeend', rowHtml);
        const currentRow = container.lastElementChild;
        rowIndex++;

        setupRowEvents(currentRow);

        if (prefill) {
            currentRow.querySelector('.product-id-input').value = prefill.product_id || '';
            currentRow.querySelector('.variant-id-input').value = prefill.variant_id || '';
            currentRow.querySelector('.purchase-item-id-input').value = prefill.purchase_item_id || '';
            currentRow.querySelector('.sku-input').value = prefill.sku || '';
            currentRow.querySelector('.batch-input').value = prefill.batch_no || '';

            const billedQty = parseFloat(prefill.billed_qty ?? prefill.quantity) || 0;
            const returnQty = parseFloat(prefill.return_qty ?? 1) || 1;

            currentRow.querySelector('.billed-qty-input').value = billedQty;
            currentRow.querySelector('.return-qty-input').value = returnQty;
            currentRow.querySelector('.cost-input').value = prefill.unit_cost || 0;
            currentRow.querySelector('.tax-input').value = prefill.tax || 0;
            currentRow.querySelector('.product-name-display').innerText = prefill.product_name || prefill.name || '';
            currentRow.querySelector('.product-search-input').value = prefill.product_name || prefill.name || '';

            if (prefill.reason) {
                currentRow.querySelector('.reason-select').value = prefill.reason;
            }
        }

        calculateTotals();
    }

    function setupRowEvents(row) {
        const inputs = row.querySelectorAll('.return-qty-input, .cost-input, .tax-input');
        inputs.forEach(input => {
            input.addEventListener('input', calculateTotals);
        });

        row.querySelector('.remove-row-btn').addEventListener('click', function () {
            if (container.children.length > 1) {
                row.remove();
                calculateTotals();
            } else {
                alert('A purchase return note must have at least one product line.');
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
        let itemsSubtotal = 0;

        const rows = container.querySelectorAll('.item-row');
        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.return-qty-input').value) || 0;
            const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
            const tax = parseFloat(row.querySelector('.tax-input').value) || 0;

            const lineTotal = Math.max(0, (qty * cost) + tax);
            row.querySelector('.line-total-display').innerText = '৳' + lineTotal.toFixed(2);
            itemsSubtotal += lineTotal;
        });

        const globalTax = parseFloat(globalTaxInput.value) || 0;
        const shipping = parseFloat(shippingInput.value) || 0;

        const grandTotal = Math.max(0, itemsSubtotal + globalTax - shipping);

        document.getElementById('displaySubtotal').innerText = '৳' + itemsSubtotal.toFixed(2);
        document.getElementById('displayGrandTotal').innerText = '৳' + grandTotal.toFixed(2);
        document.getElementById('grand_total').value = grandTotal.toFixed(2);
    }

    addItemBtn.addEventListener('click', () => addRow());
    supplierSelect.addEventListener('change', updateSupplierBalance);

    [globalTaxInput, shippingInput].forEach(elem => {
        if (elem) elem.addEventListener('input', calculateTotals);
    });

    // Import from Purchase Bill
    const pullBillBtn = document.getElementById('pullBillItemsBtn');
    const linkBillSelector = document.getElementById('linkBillSelector');
    if (pullBillBtn && linkBillSelector) {
        pullBillBtn.addEventListener('click', function () {
            const billId = linkBillSelector.value;
            if (!billId) return alert('Select a Purchase Bill first.');

            const opt = linkBillSelector.options[linkBillSelector.selectedIndex];
            const supplierId = opt.getAttribute('data-supplier');
            const warehouseId = opt.getAttribute('data-warehouse');

            if (supplierId && supplierSelect) supplierSelect.value = supplierId;
            if (warehouseId && warehouseSelect) warehouseSelect.value = warehouseId;
            document.getElementById('purchase_id').value = billId;
            updateSupplierBalance();

            fetch(`{{ url('admin/procurement/purchases') }}/${billId}/items`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data && res.data.length > 0) {
                    container.innerHTML = '';
                    rowIndex = 0;
                    res.data.forEach(item => {
                        addRow({
                            product_id: item.product_id,
                            variant_id: item.variant_id,
                            purchase_item_id: item.id,
                            product_name: item.product ? item.product.name : item.product_name,
                            sku: item.sku,
                            batch_no: item.batch_no,
                            billed_qty: item.quantity,
                            return_qty: item.quantity,
                            unit_cost: item.unit_cost,
                            reason: 'defective'
                        });
                    });
                }
            })
            .catch(err => console.error(err));
        });
    }

    // Import from Inward GRN
    const pullGrnBtn = document.getElementById('pullGrnItemsBtn');
    const linkGrnSelector = document.getElementById('linkGrnSelector');
    if (pullGrnBtn && linkGrnSelector) {
        pullGrnBtn.addEventListener('click', function () {
            const grnId = linkGrnSelector.value;
            if (!grnId) return alert('Select a Goods Received Note first.');

            const opt = linkGrnSelector.options[linkGrnSelector.selectedIndex];
            const supplierId = opt.getAttribute('data-supplier');
            const warehouseId = opt.getAttribute('data-warehouse');

            if (supplierId && supplierSelect) supplierSelect.value = supplierId;
            if (warehouseId && warehouseSelect) warehouseSelect.value = warehouseId;
            document.getElementById('purchase_receipt_id').value = grnId;
            updateSupplierBalance();

            fetch(`{{ url('admin/procurement/purchase-receipts') }}/${grnId}/items`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data && res.data.length > 0) {
                    container.innerHTML = '';
                    rowIndex = 0;
                    res.data.forEach(item => {
                        const rejectedQty = parseFloat(item.rejected_quantity) || 0;
                        addRow({
                            product_id: item.product_id,
                            variant_id: item.variant_id,
                            product_name: item.product ? item.product.name : item.product_name,
                            sku: item.sku,
                            batch_no: item.batch_no,
                            billed_qty: item.delivered_quantity,
                            return_qty: rejectedQty > 0 ? rejectedQty : 1,
                            unit_cost: item.unit_cost || 0,
                            reason: rejectedQty > 0 ? 'qc_rejected' : 'defective'
                        });
                    });
                }
            })
            .catch(err => console.error(err));
        });
    }

    // Default initialization
    updateSupplierBalance();
    if (linkBillSelector && linkBillSelector.value && pullBillBtn) {
        pullBillBtn.click();
    } else if (linkGrnSelector && linkGrnSelector.value && pullGrnBtn) {
        pullGrnBtn.click();
    } else {
        addRow();
    }
});
</script>

{{-- Page-specific Styles --}}
<style>
    .purchase-return-create-page {
        min-width: 0;
        color: #344054;
    }

    .purchase-return-create-page .breadcrumb {
        font-size: 12px;
    }

    .purchase-return-create-page .breadcrumb-item a {
        color: #6c757d;
        text-decoration: none;
    }

    .purchase-return-create-page .breadcrumb-item a:hover {
        color: var(--bs-primary, #1572e8);
    }

    .purchase-return-create-page .btn {
        border-radius: 6px;
        font-weight: 600;
    }

    .purchase-return-create-page .prc-alert {
        border-radius: 7px;
    }

    .purchase-return-create-page .prc-section-card {
        border: 1px solid #e8edf3 !important;
        border-radius: 8px !important;
        background: #fff;
        box-shadow: 0 2px 8px rgba(20, 35, 60, 0.045) !important;
        overflow: hidden;
    }

    .purchase-return-create-page .prc-section-header {
        min-height: 50px;
        padding: 13px 16px;
        background: #fff;
        border-bottom: 1px solid #edf0f4;
    }

    .purchase-return-create-page .prc-section-header h6 {
        font-size: 13px;
        line-height: 1.6;
    }

    .purchase-return-create-page .prc-import-controls {
        min-width: 0;
    }

    .purchase-return-create-page .prc-import-group {
        width: 260px;
        max-width: 100%;
    }

    .purchase-return-create-page .prc-import-group .form-select {
        min-width: 0;
    }

    .purchase-return-create-page .form-label {
        color: #475467;
        margin-bottom: 6px;
        font-size: 12px;
    }

    .purchase-return-create-page .form-control,
    .purchase-return-create-page .form-select {
        min-height: 35px;
        border-color: #dfe4ea;
        border-radius: 5px;
        font-size: 12px;
    }

    .purchase-return-create-page .form-control:focus,
    .purchase-return-create-page .form-select:focus {
        border-color: var(--bs-primary, #1572e8);
        box-shadow: 0 0 0 0.15rem rgba(21, 114, 232, 0.1);
    }

    .purchase-return-create-page input[type="file"].form-control {
        padding: 5px 8px;
    }

    .purchase-return-create-page .prc-section-description {
        display: block;
        line-height: 1.6;
    }

    .purchase-return-create-page #addItemRowBtn {
        white-space: nowrap;
        padding: 7px 12px;
    }

    .purchase-return-create-page .prc-items-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .purchase-return-create-page .prc-items-table {
        min-width: 1160px;
        font-size: 12px;
    }

    .purchase-return-create-page .prc-items-table thead th {
        padding: 11px 9px;
        color: #667085;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.35px;
        white-space: nowrap;
        vertical-align: middle;
    }

    .purchase-return-create-page .prc-items-table tbody td {
        padding: 9px;
        vertical-align: middle;
        border-color: #edf0f4;
    }

    .purchase-return-create-page .prc-items-table tbody tr:hover {
        background-color: #fafbfc;
    }

    .purchase-return-create-page .prc-items-table .form-control,
    .purchase-return-create-page .prc-items-table .form-select {
        min-width: 0;
        font-size: 11.5px;
    }

    .purchase-return-create-page .prc-items-table .product-name-display {
        overflow-wrap: anywhere;
        line-height: 1.5;
    }

    .purchase-return-create-page .prc-items-table .remove-row-btn {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
    }

    .purchase-return-create-page .prc-items-table .remove-row-btn:hover {
        background: #fff1f0;
    }

    .purchase-return-create-page .prc-policy-note {
        min-height: 100%;
        line-height: 1.7;
        border-radius: 6px !important;
    }

    .purchase-return-create-page .prc-total-card {
        border-color: #e5e9ef !important;
        border-radius: 7px !important;
    }

    .purchase-return-create-page .prc-total-card .form-control {
        min-height: 32px;
    }

    .purchase-return-create-page .card-footer {
        border-top-color: #edf0f4 !important;
    }

    .purchase-return-create-page .card-footer .btn {
        min-width: 90px;
        padding: 7px 13px;
    }

    @media (max-width: 991.98px) {
        .purchase-return-create-page .prc-import-controls {
            width: 100%;
        }

        .purchase-return-create-page .prc-import-group {
            flex: 1 1 230px;
        }
    }

    @media (max-width: 767.98px) {
        .purchase-return-create-page {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        .purchase-return-create-page .prc-section-header {
            padding: 12px;
        }

        .purchase-return-create-page .prc-import-controls {
            display: grid !important;
            grid-template-columns: 1fr;
        }

        .purchase-return-create-page .prc-import-group {
            width: 100%;
        }

        .purchase-return-create-page .prc-section-description {
            font-size: 11px;
        }

        .purchase-return-create-page #addItemRowBtn {
            width: 100%;
        }

        .purchase-return-create-page .prc-total-card {
            padding: 13px !important;
        }
    }

    @media (max-width: 420px) {
        .purchase-return-create-page .prc-section-header {
            align-items: stretch !important;
        }

        .purchase-return-create-page .prc-total-card .d-flex {
            gap: 8px;
        }

        .purchase-return-create-page .prc-total-card .d-flex > span:first-child {
            min-width: 0;
        }

        .purchase-return-create-page .prc-total-card .form-control {
            width: 95px !important;
            flex-shrink: 0;
        }

        .purchase-return-create-page .card-footer {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
        }

        .purchase-return-create-page .card-footer .btn {
            width: 100%;
        }
    }
</style>
@endsection