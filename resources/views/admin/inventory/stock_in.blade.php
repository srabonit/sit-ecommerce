@extends('layouts.admin')

@section('title', 'Stock In & Inbound Inventory - ' . config('app.name', 'Shop Now BD'))

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius Across All Controls */
    #stockInRoot .btn,
    #stockInRoot .btn-sm,
    #stockInRoot button,
    #stockInRoot .form-control,
    #stockInRoot .form-select,
    #stockInRoot .badge,
    #stockInRoot .alert,
    #stockInRoot .input-group-text,
    #stockInRoot .card,
    #stockInRoot .calc-preview-card,
    #stockInRoot .variant-pill-select,
    #stockInRoot .search-result-panel,
    #stockInRoot .proof-media-surface,
    #stockInRoot .proof-thumb-box,
    .stock-in-modal-scope .modal-content,
    .stock-in-modal-scope .btn,
    .stock-in-modal-scope .form-control {
        border-radius: 5px !important;
    }

    #stockInRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #stockInRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Safe Monospace & Bengali Digit Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Enterprise Surface Cards */
    #stockInRoot .card {
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        padding: 16px;
        margin-bottom: 16px;
    }

    /* Compact Button Styling */
    .btn-compact {
        font-size: 11px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    /* Live Search Results Dropdown */
    .search-result-panel {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1050;
        max-height: 360px;
        overflow-y: auto;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }

    .search-result-item {
        padding: 8px 12px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: background-color 0.15s ease;
    }
    .search-result-item:hover {
        background-color: #f8fafc;
    }

    /* Variant Pill Selector */
    .variant-pill-select {
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        padding: 6px 12px;
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
    }
    .variant-pill-select:hover, .variant-pill-select.active {
        border-color: #0284c7;
        background: #e0f2fe;
        color: #0369a1;
        font-weight: 700;
    }

    /* Calculation Preview Summary Card */
    .calc-preview-card {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px dashed #cbd5e1;
        padding: 16px;
        margin-bottom: 16px;
    }

    /* Global Media Chooser Surface Box for Supplier Challan */
    .proof-media-surface {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        padding: 10px;
        transition: all 0.2s ease;
    }
    .proof-media-surface:hover {
        border-color: #3b82f6;
        background: #f1f5f9;
    }
    .proof-empty-icon-box {
        width: 36px;
        height: 36px;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
    }
    .proof-thumb-box {
        width: 44px;
        height: 44px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        position: relative;
    }
    .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .proof-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.15s ease;
        border-radius: 5px !important;
    }
    .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    /* Form Controls */
    #stockInRoot .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #stockInRoot .form-control,
    #stockInRoot .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #stockInRoot .form-control:focus,
    #stockInRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .stock-in-header h5 {
            font-size: 16px !important;
        }
        .header-actions-wrap {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .header-actions-wrap .btn {
            width: 100%;
            padding: 8px 6px !important;
            font-size: 11px !important;
            justify-content: center;
        }
        #stockInRoot .card {
            padding: 12px !important;
            margin-bottom: 12px !important;
        }
        .calc-preview-card {
            padding: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="stockInRoot">
    <!-- Header (Original Clean Light Theme) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 stock-in-header">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size: 18px;">
                <i class="fas fa-boxes-packing text-primary"></i>
                <span>Stock In / Inbound Inventory Consignment</span>
            </h5>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Add inbound purchase shipments, opening stock, or customer returns with atomic WAC recalculation.
            </small>
        </div>
        <div class="header-actions-wrap d-flex flex-wrap gap-2">
            <a href="{{ route('admin.inventory.balance') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-scale-balanced me-1"></i> <span>View Stock Balance</span>
            </a>
            <a href="{{ route('admin.inventory.movements') }}" class="btn btn-sm btn-outline-primary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-clipboard-list me-1"></i> <span>Inventory Ledger</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3 d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-xs mb-3" role="alert">
            <div class="d-flex align-items-center gap-1 mb-1">
                <i class="fas fa-triangle-exclamation text-danger fs-5"></i>
                <strong class="text-dark">Please correct the following errors:</strong>
            </div>
            <ul class="mb-0 ps-3 text-dark">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">
        <!-- Left Side: Product Selector & Stock In Form -->
        <div class="col-12 col-lg-8">
            <div class="card mb-3">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 d-flex align-items-center gap-1" style="font-size: 13px;">
                    <i class="fas fa-search text-primary"></i> <span>1. Search &amp; Select Product / Variant</span>
                </h6>

                <!-- Live AJAX Search Bar -->
                <div class="position-relative mb-3">
                    <label class="form-label" for="liveSearchInput">
                        Search by Product Title, ID, SKU, Barcode, Color, or Size
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0">
                            <i class="fas fa-barcode" style="font-size: 11px;"></i>
                        </span>
                        <input type="text" id="liveSearchInput" class="form-control border-start-0" placeholder="Type product name, scan barcode, or enter SKU (e.g. PRD-102)..." autocomplete="off">
                        <button type="button" class="btn btn-light border" id="btnClearSearch" onclick="clearProductSelection()" style="display: none;">
                            <i class="fas fa-times text-muted"></i>
                        </button>
                    </div>

                    <!-- AJAX Search Results Dropdown -->
                    <div id="searchResultsPanel" class="search-result-panel d-none" style="border-radius: 5px;">
                        <div id="searchResultsList"></div>
                    </div>
                </div>

                <!-- Selected Product Overview Card -->
                <div id="selectedProductCard" class="p-3 border bg-light d-none" style="border-radius: 5px;">
                    <div class="d-flex align-items-start gap-3 flex-wrap flex-sm-nowrap">
                        <img id="selProdImg" src="https://placehold.co/60x60?text=Item" class="border object-fit-cover shadow-xs flex-shrink-0" style="width: 54px; height: 54px; border-radius: 5px;" alt="Thumbnail">
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div class="min-w-0">
                                    <h6 class="fw-bold text-dark mb-1 text-truncate" id="selProdTitle" style="font-size: 13px;">Product Title</h6>
                                    <div class="small text-muted font-monospace bn-digit" style="font-size: 11px;">
                                        ID: <span id="selProdId" class="fw-bold text-secondary">#0</span> | SKU: <span id="selProdSku" class="fw-bold text-dark">—</span>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-compact flex-shrink-0" onclick="clearProductSelection()">
                                    <i class="fas fa-rotate-left me-1"></i> Change
                                </button>
                            </div>

                            <!-- Variant Picker (If variable product) -->
                            <div id="variantPickerWrapper" class="mt-2 pt-2 border-top d-none">
                                <label class="form-label text-primary mb-1 d-block">
                                    <i class="fas fa-layer-group me-1"></i> Select Variant <span class="text-danger">*</span> (Parent selection restricted)
                                </label>
                                <div class="d-flex flex-wrap gap-1" id="variantPillContainer"></div>
                            </div>

                            <!-- Active Target Selection Badge -->
                            <div class="mt-2 d-flex flex-wrap align-items-center gap-1" id="activeTargetBadgeContainer">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" id="badgeTargetStruct" style="font-size: 10px;">Simple Product</span>
                                <span class="badge bg-dark-subtle text-dark border px-2 py-1 bn-digit font-monospace" id="badgeCurrentStock" style="font-size: 10px;">Current Stock: 0</span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 bn-digit font-monospace" id="badgeCurrentCost" style="font-size: 10px;">Current Cost: ৳0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock In Inbound Form -->
            <div class="card">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3 d-flex align-items-center gap-1" style="font-size: 13px;">
                    <i class="fas fa-file-invoice text-success"></i> <span>2. Inbound Shipment Details &amp; Quantities</span>
                </h6>

                <form id="stockInForm" action="{{ route('admin.inventory.stock_in.store') }}" method="POST">
                    @csrf
                    <!-- Hidden Product / Variant IDs -->
                    <input type="hidden" name="product_id" id="form_product_id" required>
                    <input type="hidden" name="variant_id" id="form_variant_id">
                    <!-- Hidden Input for Supplier Challan / Invoice Memo Proof -->
                    <input type="hidden" name="supplier_challan_slip" id="form_supplier_challan_slip" value="">

                    <div class="row g-2 mb-2">
                        <!-- Movement Type -->
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="form_movement_type">
                                Stock In Type <span class="text-danger">*</span>
                            </label>
                            <select name="movement_type" id="form_movement_type" class="form-select shadow-none font-monospace" required>
                                <option value="purchase_in" selected>New Purchase Inbound</option>
                                <option value="opening_stock">Opening Stock Balance</option>
                                <option value="customer_return">Customer Return Restock</option>
                                <option value="supplier_replacement">Supplier Replacement</option>
                                <option value="stock_correction">Stock Correction / Audit In</option>
                                <option value="other_addition">Other Approved Addition</option>
                            </select>
                        </div>

                        <!-- Supplier Selection -->
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="form_supplier_id">Supplier / Vendor</label>
                            <select name="supplier_id" id="form_supplier_id" class="form-select shadow-none font-monospace">
                                <option value="">— Select Supplier (Optional) —</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->name }} {{ !empty($sup->company_name) ? "({$sup->company_name})" : '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date -->
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="form_stock_in_date">Stock In Date</label>
                            <input type="date" name="stock_in_date" id="form_stock_in_date" class="form-control font-monospace bn-digit shadow-none" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <!-- Reference / PO Number -->
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="form_reference_id">
                                Reference / PO Number <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="reference_id" id="form_reference_id" class="form-control font-monospace bn-digit shadow-none" value="{{ $defaultPoNumber }}" required>
                        </div>

                        <!-- Supplier Invoice Memo -->
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="form_supplier_invoice_no">Supplier Memo / Invoice #</label>
                            <input type="text" name="supplier_invoice_no" id="form_supplier_invoice_no" class="form-control font-monospace shadow-none" placeholder="e.g. SUP-INV-9901">
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <!-- Inbound Quantity -->
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="form_quantity">
                                Inbound Quantity <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" name="quantity" id="form_quantity" class="form-control font-monospace bn-digit fw-bold text-success shadow-none" min="1" placeholder="e.g. 50" required oninput="recalculatePreview()">
                                <span class="input-group-text bg-light text-muted font-monospace" style="font-size: 11px;">Units</span>
                            </div>
                        </div>

                        <!-- Purchase Cost per Unit -->
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="form_purchase_cost">Unit Purchase Cost (৳)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted font-monospace" style="font-size: 11px;">৳</span>
                                <input type="number" step="0.01" name="purchase_cost" id="form_purchase_cost" class="form-control font-monospace bn-digit shadow-none" placeholder="e.g. 350.00" oninput="recalculatePreview()">
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 10px;">Leave blank to maintain current unit cost.</small>
                        </div>

                        <!-- Selling Price per Unit -->
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="form_selling_price">
                                New Selling Price (৳) <span class="text-muted fw-normal">(Optional)</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted font-monospace" style="font-size: 11px;">৳</span>
                                <input type="number" step="0.01" name="selling_price" id="form_selling_price" class="form-control font-monospace bn-digit shadow-none" placeholder="e.g. 499.00">
                            </div>
                        </div>
                    </div>

                    <!-- WAC Recalculation Toggle -->
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input shadow-none" type="checkbox" name="update_wac" id="form_update_wac" value="1" checked onchange="recalculatePreview()" style="cursor: pointer;">
                        <label class="form-check-label small fw-semibold text-dark" for="form_update_wac" style="font-size: 11px; cursor: pointer;">
                            Apply Weighted Average Costing (WAC) formula to update inventory valuation
                        </label>
                    </div>

                    <!-- Modern Global Media Chooser for Supplier Challan / Invoice Slip Attachment -->
                    <div class="mb-3">
                        <label class="form-label d-flex align-items-center justify-content-between mb-1">
                            <span>
                                <i class="fas fa-paperclip text-primary me-1"></i> Supplier Challan / Consignment Memo (Media Chooser):
                            </span>
                            <span class="badge bg-light text-muted border font-monospace" style="font-size: 8.5px;">GLOBAL MEDIA</span>
                        </label>
                        <div class="proof-media-surface" style="border-radius: 5px;">
                            <!-- Empty State -->
                            <div id="noChallanAttachedNotice" class="text-center py-2">
                                <div class="proof-empty-icon-box mx-auto mb-2" style="border-radius: 5px;">
                                    <i class="fas fa-cloud-arrow-up text-muted"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1" style="font-size: 11px;">No Challan / Invoice Attached</h6>
                                <p class="text-muted small mb-2" style="font-size: 10px;">Attach supplier delivery challan, bill copy, or shipment proof screenshot.</p>
                                <button type="button" class="btn btn-primary btn-compact shadow-xs" onclick="chooseSupplierChallanMedia()">
                                    <i class="fas fa-photo-film me-1"></i>
                                    <span>Choose from Media Library</span>
                                </button>
                            </div>

                            <!-- Attached Preview Card (100% Mobile & PC Responsive) -->
                            <div id="challanPreviewContainer" class="d-none bg-white border p-1" style="border-radius: 5px;">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <div class="proof-thumb-box" onclick="zoomChallanMedia()" title="Click to Zoom Image">
                                        <img id="challanPreviewImg" src="https://placehold.co/120x120?text=Challan" alt="Challan" onerror="this.src='https://placehold.co/120x120?text=Challan';">
                                        <div class="proof-thumb-hover-overlay">
                                            <i class="fas fa-search-plus text-white" style="font-size: 9px;"></i>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-grow-1">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 8.5px;">
                                            <i class="fas fa-check-circle me-1"></i>Challan Attached
                                        </span>
                                        <strong class="d-block text-dark text-truncate font-monospace" id="challanPathText" style="font-size: 10px;">challan_proof.jpg</strong>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1 border-top pt-1">
                                    <button type="button" class="btn btn-outline-secondary btn-compact flex-grow-1 py-0 px-1" style="height: 22px; font-size: 9px !important;" onclick="zoomChallanMedia()">
                                        <i class="fas fa-expand me-1"></i><span>Zoom</span>
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-compact flex-grow-1 py-0 px-1" style="height: 22px; font-size: 9px !important;" onclick="chooseSupplierChallanMedia()">
                                        <i class="fas fa-arrows-rotate me-1"></i><span>Change</span>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-compact flex-grow-1 py-0 px-1" style="height: 22px; font-size: 9px !important;" onclick="clearSupplierChallanMedia()">
                                        <i class="fas fa-trash-alt me-1"></i><span>Remove</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes / Audit Memo -->
                    <div class="mb-3">
                        <label class="form-label" for="form_notes">Consignment Notes / Audit Remarks</label>
                        <textarea name="notes" id="form_notes" class="form-control shadow-none" rows="2" style="font-size: 11px;" placeholder="Received shipment via truck #5, quality checked and placed in warehouse bin A-4..."></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-sm btn-light border btn-compact" onclick="clearProductSelection()">Reset</button>
                        <button type="submit" class="btn btn-sm btn-success fw-bold px-4 shadow-xs btn-compact" id="btnSubmitStockIn" disabled>
                            <i class="fas fa-check-circle me-1"></i> Complete Stock In
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Side: Real-time Mathematical Calculation Card -->
        <div class="col-12 col-lg-4">
            <div class="calc-preview-card" style="border-radius: 5px;">
                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-1" style="font-size: 13px;">
                    <i class="fas fa-calculator text-primary"></i> <span>Live Valuation Impact Preview</span>
                </h6>

                <div class="d-flex justify-content-between align-items-center py-1 border-bottom small">
                    <span class="text-muted">Target Type:</span>
                    <strong class="text-dark text-truncate ms-2" style="max-width: 170px;" id="calcTargetLabel">None Selected</strong>
                </div>

                <div class="d-flex justify-content-between align-items-center py-1 border-bottom small">
                    <span class="text-muted">Current Stock:</span>
                    <span class="font-monospace bn-digit fw-bold text-dark" id="calcCurStock">0 Units</span>
                </div>

                <div class="d-flex justify-content-between align-items-center py-1 border-bottom small">
                    <span class="text-muted">Inbound Quantity:</span>
                    <span class="font-monospace bn-digit fw-bold text-success" id="calcInboundQty">+0 Units</span>
                </div>

                <div class="d-flex justify-content-between align-items-center py-2 px-2 border-bottom small bg-white mt-1 mb-1" style="border-radius: 5px;">
                    <span class="fw-bold text-dark">New Total Stock:</span>
                    <span class="font-monospace bn-digit fw-bold text-primary" style="font-size: 14px;" id="calcNewStock">0 Units</span>
                </div>

                <div class="d-flex justify-content-between align-items-center py-1 border-bottom small mt-1">
                    <span class="text-muted">Current Unit Cost:</span>
                    <span class="font-monospace bn-digit text-muted" id="calcCurCost">৳0.00</span>
                </div>

                <div class="d-flex justify-content-between align-items-center py-1 border-bottom small">
                    <span class="text-muted">Inbound Unit Cost:</span>
                    <span class="font-monospace bn-digit text-success fw-semibold" id="calcInboundCost">৳0.00</span>
                </div>

                <div class="d-flex justify-content-between align-items-center py-2 px-2 border-bottom small bg-white mt-1 mb-2" style="border-radius: 5px;">
                    <span class="fw-bold text-dark">Resulting WAC Cost:</span>
                    <span class="font-monospace bn-digit fw-bold text-info-emphasis" style="font-size: 14px;" id="calcNewWac">৳0.00</span>
                </div>

                <div class="p-2 bg-primary bg-opacity-10 border border-primary border-opacity-25" style="border-radius: 5px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small fw-bold text-dark">Consignment Inbound Value:</span>
                        <strong class="font-monospace bn-digit text-primary fs-6" id="calcTotalInboundVal">৳ 0.00</strong>
                    </div>
                </div>
            </div>

            <!-- Helpful Hints Card -->
            <div class="card p-3">
                <h6 class="fw-bold text-dark mb-1 small d-flex align-items-center gap-1">
                    <i class="fas fa-shield-halved text-success"></i> <span>Data Integrity Protection</span>
                </h6>
                <p class="text-muted small mb-1" style="font-size: 11px; line-height: 1.4;">
                    &bull; Variable products require selecting a specific <strong>Variant</strong> to maintain isolated variant stock positions.
                </p>
                <p class="text-muted small mb-0" style="font-size: 11px; line-height: 1.4;">
                    &bull; Unique PO reference tracking prevents duplicate inbound consignment entries during re-submission.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Challan Screenshot Zoom Lightbox Modal -->
<div class="modal fade stock-in-modal-scope" id="challanZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                    <i class="fas fa-image text-primary me-1"></i> Supplier Challan / Invoice Slip
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="modalZoomChallanImgTag" src="" alt="Challan Slip" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Bootstrap Notice Dialog Modal -->
<div class="modal fade stock-in-modal-scope" id="stockInNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-2" id="stockInNoticeIconWrap" style="width: 44px; height: 44px; border-radius: 50% !important;">
                    <i class="fas fa-circle-info fs-4" id="stockInNoticeIcon"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="stockInNoticeTitle" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="stockInNoticeMsg" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentSelectedProduct = null;
    let currentSelectedVariant = null;
    let searchDebounceTimer = null;

    function showStockInNotice(message, title = 'Notification', type = 'info') {
        const titleEl  = document.getElementById('stockInNoticeTitle');
        const msgEl    = document.getElementById('stockInNoticeMsg');
        const iconWrap = document.getElementById('stockInNoticeIconWrap');
        const iconEl   = document.getElementById('stockInNoticeIcon');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;

        if (iconWrap && iconEl) {
            if (type === 'error' || type === 'danger') {
                iconWrap.className = 'rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2';
                iconEl.className = 'fas fa-circle-exclamation fs-4';
            } else if (type === 'success') {
                iconWrap.className = 'rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-2';
                iconEl.className = 'fas fa-circle-check fs-4';
            } else {
                iconWrap.className = 'rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-2';
                iconEl.className = 'fas fa-circle-info fs-4';
            }
        }

        const modalEl = document.getElementById('stockInNoticeModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    // Global Media Chooser Integration for Supplier Challan
    function extractMediaItem(mediaInput) {
        if (!mediaInput) return null;
        const item = Array.isArray(mediaInput) ? mediaInput[0] : mediaInput;
        if (!item || typeof item !== 'object') return null;
        const rawPath = item.file_path || item.path || item.url || '';
        const rawUrl  = item.url || item.file_path || item.path || '';
        if (!rawPath || rawPath === 'undefined') return null;
        return {
            path: String(rawPath).trim(),
            url:  String(rawUrl).trim()
        };
    }

    function chooseSupplierChallanMedia() {
        if (typeof window.openMediaPicker === 'function') {
            window.openMediaPicker({
                multiple: false,
                onSelect: function(media) {
                    const picked = extractMediaItem(media);
                    if (picked && picked.path) {
                        const inputHidden = document.getElementById('form_supplier_challan_slip');
                        const previewImg  = document.getElementById('challanPreviewImg');
                        const pathText    = document.getElementById('challanPathText');
                        const container   = document.getElementById('challanPreviewContainer');
                        const emptyNotice = document.getElementById('noChallanAttachedNotice');

                        if (inputHidden) inputHidden.value = picked.path;
                        if (previewImg) previewImg.src = picked.url;
                        if (pathText) {
                            pathText.innerText = picked.path.split('/').pop() || picked.path;
                            pathText.title = picked.path;
                        }

                        if (container) {
                            container.classList.remove('d-none');
                            container.classList.add('d-block');
                        }
                        if (emptyNotice) {
                            emptyNotice.classList.add('d-none');
                            emptyNotice.classList.remove('d-block');
                        }

                        showStockInNotice('Supplier challan attached from Global Media Library.', 'Attached', 'success');
                    }
                }
            });
        } else {
            showStockInNotice('Global Media Library is loading or not available on this layout.', 'Notice');
        }
    }

    function clearSupplierChallanMedia() {
        const inputHidden = document.getElementById('form_supplier_challan_slip');
        const container   = document.getElementById('challanPreviewContainer');
        const emptyNotice = document.getElementById('noChallanAttachedNotice');

        if (inputHidden) inputHidden.value = '';
        if (container) {
            container.classList.add('d-none');
            container.classList.remove('d-block');
        }
        if (emptyNotice) {
            emptyNotice.classList.remove('d-none');
            emptyNotice.classList.add('d-block');
        }

        showStockInNotice('Supplier challan attachment cleared.', 'Cleared', 'info');
    }

    function zoomChallanMedia() {
        const previewImg = document.getElementById('challanPreviewImg');
        const zoomImgTag = document.getElementById('modalZoomChallanImgTag');
        if (previewImg && zoomImgTag && previewImg.src) {
            zoomImgTag.src = previewImg.src;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('challanZoomModal')).show();
        }
    }

    // Live AJAX Product Search
    document.getElementById('liveSearchInput').addEventListener('input', function(e) {
        clearTimeout(searchDebounceTimer);
        const term = this.value.trim();
        const panel = document.getElementById('searchResultsPanel');
        const list = document.getElementById('searchResultsList');

        if (term.length < 1) {
            panel.classList.add('d-none');
            return;
        }

        searchDebounceTimer = setTimeout(() => {
            fetch(`{{ route('admin.inventory.search.products.ajax') }}?q=${encodeURIComponent(term)}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                list.innerHTML = '';
                if (!Array.isArray(data) || data.length === 0) {
                    list.innerHTML = `<div class="p-3 text-muted text-center small"><i class="fas fa-box-open me-1 opacity-50"></i> No matching products found for "<strong>${escapeHtml(term)}</strong>"</div>`;
                } else {
                    data.forEach(p => {
                        const isVar = p.product_struct === 'variable';
                        const structBadge = isVar 
                            ? `<span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:9.5px;">Variable (${p.variants.length} Variants)</span>`
                            : `<span class="badge bg-light text-dark border" style="font-size:9.5px;">Simple Product</span>`;

                        const itemHtml = `
                            <div class="search-result-item d-flex align-items-center justify-content-between" onclick="handleProductSelect(${escapeJson(p)})">
                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                    <img src="${p.thumbnail}" class="border object-fit-cover shadow-xs flex-shrink-0" style="width: 38px; height: 38px; border-radius: 5px;" alt="thumb" onerror="this.src='https://placehold.co/60x60?text=Item';">
                                    <div class="overflow-hidden">
                                        <span class="fw-bold text-dark d-block text-truncate" style="font-size: 12px;">${escapeHtml(p.title)}</span>
                                        <small class="text-muted font-monospace bn-digit" style="font-size: 10.5px;">
                                            ID: #${p.id} | SKU: ${p.sku} | Stock: ${p.current_stock}
                                        </small>
                                    </div>
                                </div>
                                <div class="text-end flex-shrink-0 ms-2">
                                    ${structBadge}
                                    <span class="d-block font-monospace bn-digit fw-bold text-dark small mt-1">৳${Number(p.purchase_cost).toFixed(2)}</span>
                                </div>
                            </div>
                        `;
                        list.insertAdjacentHTML('beforeend', itemHtml);
                    });
                }
                panel.classList.remove('d-none');
            })
            .catch(err => console.error('Search error:', err));
        }, 220);
    });

    document.addEventListener('click', function(e) {
        if (!document.getElementById('liveSearchInput').contains(e.target) && !document.getElementById('searchResultsPanel').contains(e.target)) {
            document.getElementById('searchResultsPanel').classList.add('d-none');
        }
    });

    function handleProductSelect(prod) {
        currentSelectedProduct = prod;
        currentSelectedVariant = null;

        document.getElementById('searchResultsPanel').classList.add('d-none');
        document.getElementById('liveSearchInput').value = prod.title;
        document.getElementById('btnClearSearch').style.display = 'block';

        document.getElementById('selProdImg').src = prod.thumbnail;
        document.getElementById('selProdTitle').innerText = prod.title;
        document.getElementById('selProdId').innerText = '#' + prod.id;
        document.getElementById('selProdSku').innerText = prod.sku;
        document.getElementById('selectedProductCard').classList.remove('d-none');

        document.getElementById('form_product_id').value = prod.id;
        document.getElementById('form_variant_id').value = '';

        const isVar = prod.product_struct === 'variable';
        const variantWrapper = document.getElementById('variantPickerWrapper');
        const pillContainer = document.getElementById('variantPillContainer');

        if (isVar && Array.isArray(prod.variants) && prod.variants.length > 0) {
            variantWrapper.classList.remove('d-none');
            pillContainer.innerHTML = '';
            document.getElementById('btnSubmitStockIn').disabled = true;

            prod.variants.forEach(v => {
                const pill = document.createElement('div');
                pill.className = 'variant-pill-select font-monospace bn-digit small';
                pill.style.borderRadius = '5px';
                pill.innerHTML = `<strong>${escapeHtml(v.title)}</strong> (SKU: ${v.sku}) [Stock: ${v.stock}]`;
                pill.onclick = function() {
                    document.querySelectorAll('.variant-pill-select').forEach(el => el.classList.remove('active'));
                    pill.classList.add('active');
                    handleVariantSelect(v);
                };
                pillContainer.appendChild(pill);
            });

            document.getElementById('badgeTargetStruct').className = 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle';
            document.getElementById('badgeTargetStruct').innerText = 'Select a Variant Above';
            document.getElementById('badgeCurrentStock').innerText = 'Current Stock: —';
            document.getElementById('badgeCurrentCost').innerText = 'Current Cost: —';
            document.getElementById('calcTargetLabel').innerText = `${prod.title} (Select Variant)`;
        } else {
            variantWrapper.classList.add('d-none');
            document.getElementById('badgeTargetStruct').className = 'badge bg-light text-dark border';
            document.getElementById('badgeTargetStruct').innerText = 'Simple Product';
            document.getElementById('badgeCurrentStock').innerText = `Current Stock: ${prod.current_stock}`;
            document.getElementById('badgeCurrentCost').innerText = `Current Cost: ৳${Number(prod.purchase_cost).toFixed(2)}`;

            document.getElementById('form_purchase_cost').value = prod.purchase_cost > 0 ? prod.purchase_cost : '';
            document.getElementById('form_selling_price').value = prod.selling_price > 0 ? prod.selling_price : '';

            document.getElementById('btnSubmitStockIn').disabled = false;
            recalculatePreview();
        }
    }

    function handleVariantSelect(variant) {
        currentSelectedVariant = variant;
        document.getElementById('form_variant_id').value = variant.id;

        document.getElementById('selProdSku').innerText = variant.sku;
        document.getElementById('badgeTargetStruct').className = 'badge bg-primary-subtle text-primary border border-primary-subtle';
        document.getElementById('badgeTargetStruct').innerText = `Variant: ${variant.title}`;
        document.getElementById('badgeCurrentStock').innerText = `Current Stock: ${variant.stock}`;
        document.getElementById('badgeCurrentCost').innerText = `Current Cost: ৳${Number(variant.purchase_cost).toFixed(2)}`;

        document.getElementById('form_purchase_cost').value = variant.purchase_cost > 0 ? variant.purchase_cost : '';
        document.getElementById('form_selling_price').value = variant.selling_price > 0 ? variant.selling_price : '';

        document.getElementById('btnSubmitStockIn').disabled = false;
        recalculatePreview();
    }

    function clearProductSelection() {
        currentSelectedProduct = null;
        currentSelectedVariant = null;

        document.getElementById('liveSearchInput').value = '';
        document.getElementById('btnClearSearch').style.display = 'none';
        document.getElementById('selectedProductCard').classList.add('d-none');
        document.getElementById('form_product_id').value = '';
        document.getElementById('form_variant_id').value = '';
        document.getElementById('form_quantity').value = '';
        document.getElementById('form_purchase_cost').value = '';
        document.getElementById('form_selling_price').value = '';
        document.getElementById('btnSubmitStockIn').disabled = true;

        recalculatePreview();
    }

    function recalculatePreview() {
        const inQty = parseInt(document.getElementById('form_quantity').value) || 0;
        const inCost = parseFloat(document.getElementById('form_purchase_cost').value) || 0;
        const isWac = document.getElementById('form_update_wac').checked;

        let curStock = 0;
        let curCost = 0;
        let targetName = 'None Selected';

        if (currentSelectedVariant) {
            curStock = currentSelectedVariant.stock;
            curCost = currentSelectedVariant.purchase_cost;
            targetName = `${currentSelectedProduct.title} (${currentSelectedVariant.title})`;
        } else if (currentSelectedProduct) {
            curStock = currentSelectedProduct.current_stock;
            curCost = currentSelectedProduct.purchase_cost;
            targetName = currentSelectedProduct.title;
        }

        const newStock = curStock + inQty;

        let newWac = inCost > 0 ? inCost : curCost;
        if (inCost > 0 && isWac && inQty > 0) {
            if (curStock > 0) {
                newWac = ((curStock * curCost) + (inQty * inCost)) / (curStock + inQty);
            } else {
                newWac = inCost;
            }
        } else if (inCost <= 0) {
            newWac = curCost;
        }

        const totalInboundVal = inQty * (inCost > 0 ? inCost : curCost);

        document.getElementById('calcTargetLabel').innerText = targetName;
        document.getElementById('calcCurStock').innerText = `${curStock} Units`;
        document.getElementById('calcInboundQty').innerText = `+${inQty} Units`;
        document.getElementById('calcNewStock').innerText = `${newStock} Units`;
        document.getElementById('calcCurCost').innerText = `৳${curCost.toFixed(2)}`;
        document.getElementById('calcInboundCost').innerText = `৳${(inCost > 0 ? inCost : curCost).toFixed(2)}`;
        document.getElementById('calcNewWac').innerText = `৳${newWac.toFixed(2)}`;
        document.getElementById('calcTotalInboundVal').innerText = `৳ ${totalInboundVal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>"']/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }

    function escapeJson(obj) {
        return JSON.stringify(obj).replace(/"/g, '&quot;');
    }
</script>
@endsection