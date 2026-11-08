@extends('layouts.admin')

@section('title', 'Stock In & Inbound Inventory - ' . config('app.name', 'Shop Now BD'))

@section('admin_content')
<style>
    /* =========================================================
       STOCK IN — MODERN RESPONSIVE UI
       Scoped styles | Strict 5px radius
    ========================================================= */

    #stockInRoot {
        --si-primary: #2563eb;
        --si-primary-soft: #eff6ff;
        --si-success: #059669;
        --si-success-soft: #ecfdf5;
        --si-warning: #d97706;
        --si-danger: #dc2626;
        --si-text: #0f172a;
        --si-muted: #64748b;
        --si-border: #e2e8f0;
        --si-surface: #ffffff;
        --si-soft: #f8fafc;
        color: var(--si-text);
        font-size: 12px;
    }

    /* Strict radius */
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
    #stockInRoot .selected-product-card,
    #stockInRoot .section-heading-icon,
    #stockInRoot .calc-highlight,
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

    #stockInRoot .font-monospace,
    #stockInRoot .font-monospace * {
        font-variant-numeric: tabular-nums;
    }

    #stockInRoot .font-monospace {
        font-weight: 700 !important;
    }

    #stockInRoot .card {
        border: 1px solid var(--si-border);
        background: var(--si-surface);
        box-shadow: 0 2px 8px rgba(15, 23, 42, .035);
        padding: 16px;
        margin-bottom: 16px;
        min-width: 0;
    }

    #stockInRoot .btn {
        font-weight: 600;
        transition: all .18s ease;
    }

    #stockInRoot .btn:focus-visible,
    #stockInRoot .form-control:focus-visible,
    #stockInRoot .form-select:focus-visible,
    #stockInRoot .variant-pill-select:focus-visible {
        outline: 2px solid rgba(37, 99, 235, .35);
        outline-offset: 2px;
    }

    #stockInRoot .btn-compact {
        font-size: 11px !important;
        padding: 6px 12px !important;
        line-height: 1.35 !important;
        font-weight: 700 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 3px;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #stockInRoot .btn-compact:hover {
        transform: translateY(-1px);
    }

    /* Header */
    #stockInRoot .stock-in-header {
        padding: 2px 0 14px;
        border-bottom: 1px solid var(--si-border);
    }

    #stockInRoot .stock-in-header h5 {
        color: var(--si-text);
        letter-spacing: -.35px;
    }

    #stockInRoot .header-icon {
        width: 34px;
        height: 34px;
        min-width: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--si-primary-soft);
        color: var(--si-primary);
        border: 1px solid #dbeafe;
        border-radius: 5px;
        font-size: 15px;
    }

    #stockInRoot .header-actions-wrap .btn {
        min-height: 32px;
    }

    /* Alerts */
    #stockInRoot .alert {
        border: 1px solid transparent !important;
        box-shadow: 0 2px 6px rgba(15, 23, 42, .025);
    }

    #stockInRoot .alert-success {
        background: #f0fdf4;
        border-color: #bbf7d0 !important;
    }

    #stockInRoot .alert-danger {
        background: #fff1f2;
        border-color: #fecdd3 !important;
    }

    /* Section headings */
    #stockInRoot .section-heading {
        display: flex;
        align-items: center;
        gap: 9px;
        padding-bottom: 12px;
        margin-bottom: 16px;
        border-bottom: 1px solid var(--si-border);
    }

    #stockInRoot .section-heading-icon {
        width: 29px;
        height: 29px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--si-primary-soft);
        color: var(--si-primary);
        font-size: 12px;
        flex-shrink: 0;
    }

    #stockInRoot .section-heading-icon.success {
        background: var(--si-success-soft);
        color: var(--si-success);
    }

    #stockInRoot .section-heading h6 {
        font-size: 13px;
        font-weight: 800;
        color: var(--si-text);
        margin: 0;
    }

    #stockInRoot .section-heading small {
        display: block;
        font-size: 10px;
        color: var(--si-muted);
        margin-top: 2px;
    }

    /* Form controls */
    #stockInRoot .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 5px;
        display: block;
    }

    #stockInRoot .form-control,
    #stockInRoot .form-select {
        min-height: 35px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        color: var(--si-text);
        background-color: #fff;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #stockInRoot .form-control::placeholder {
        color: #94a3b8;
        font-weight: 400;
    }

    #stockInRoot .form-control:focus,
    #stockInRoot .form-select:focus {
        border-color: var(--si-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
        outline: none;
    }

    #stockInRoot textarea.form-control {
        min-height: 64px;
        resize: vertical;
    }

    #stockInRoot .input-group-text {
        min-height: 35px;
        font-size: 11px;
        border-color: #cbd5e1;
        padding: 5px 10px;
        background: #f8fafc;
    }

    #stockInRoot .form-check-input {
        cursor: pointer;
        border-radius: 5px !important;
    }

    #stockInRoot .form-check-input:checked {
        background-color: var(--si-primary);
        border-color: var(--si-primary);
    }

    #stockInRoot .form-check-input:focus {
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
    }

    /* Product search */
    #stockInRoot .search-result-panel {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        z-index: 1050;
        max-height: 360px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #cbd5e1;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .13);
    }

    #stockInRoot .search-result-item {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: background-color .15s ease;
        min-width: 0;
    }

    #stockInRoot .search-result-item:last-child {
        border-bottom: 0;
    }

    #stockInRoot .search-result-item:hover,
    #stockInRoot .search-result-item:focus {
        background: #f1f5f9;
    }

    #stockInRoot .search-result-item img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border: 1px solid var(--si-border);
        border-radius: 5px;
        flex-shrink: 0;
    }

    #stockInRoot #btnClearSearch {
        min-width: 38px;
    }

    /* Selected product */
    #stockInRoot .selected-product-card {
        background: #f8fafc;
        border: 1px solid #cbd5e1 !important;
        padding: 13px !important;
    }

    #stockInRoot #selProdImg {
        width: 56px;
        height: 56px;
        object-fit: cover;
        border: 1px solid var(--si-border);
        background: #fff;
        border-radius: 5px;
    }

    #stockInRoot #selProdTitle {
        font-size: 13px;
        font-weight: 800;
        color: var(--si-text);
    }

    #stockInRoot #activeTargetBadgeContainer .badge {
        font-size: 10px !important;
        padding: 5px 8px !important;
        font-weight: 700;
    }

    /* Variant selector */
    #stockInRoot .variant-pill-select {
        border: 1px solid #cbd5e1;
        background: #fff;
        padding: 7px 10px;
        cursor: pointer;
        transition: all .15s ease;
        display: inline-flex;
        align-items: center;
        font-size: 10px;
        color: #475569;
        user-select: none;
    }

    #stockInRoot .variant-pill-select:hover,
    #stockInRoot .variant-pill-select.active {
        border-color: var(--si-primary);
        background: #eff6ff;
        color: #1d4ed8;
        font-weight: 700;
    }

    #stockInRoot .variant-pill-select.active {
        box-shadow: 0 0 0 2px rgba(37, 99, 235, .08);
    }

    /* Form row spacing */
    #stockInRoot .stock-form-section {
        padding: 2px 0;
    }

    #stockInRoot .form-row-block {
        margin-bottom: 13px !important;
    }

    #stockInRoot .field-help {
        font-size: 10px;
        color: var(--si-muted);
        display: block;
        margin-top: 5px;
        line-height: 1.4;
    }

    /* WAC switch */
    #stockInRoot .wac-switch-wrap {
        padding: 10px 12px;
        background: #f8fafc;
        border: 1px solid var(--si-border);
        border-radius: 5px;
    }

    /* Challan media */
    #stockInRoot .proof-media-surface {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        padding: 13px;
        transition: all .2s ease;
    }

    #stockInRoot .proof-media-surface:hover {
        border-color: #93c5fd;
        background: #f1f5f9;
    }

    #stockInRoot .proof-empty-icon-box {
        width: 39px;
        height: 39px;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        border-radius: 5px;
    }

    #stockInRoot .proof-thumb-box {
        width: 48px;
        height: 48px;
        background: #fff;
        border: 1px solid #cbd5e1;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        position: relative;
    }

    #stockInRoot .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #stockInRoot .proof-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .48);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity .15s ease;
        border-radius: 5px !important;
    }

    #stockInRoot .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    #stockInRoot #challanPreviewContainer {
        border: 1px solid var(--si-border) !important;
        padding: 9px !important;
    }

    /* Live calculation preview */
    #stockInRoot .calc-preview-card {
        background: linear-gradient(160deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #dbe3ef;
        padding: 16px;
        margin-bottom: 16px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .035);
        position: sticky;
        top: 15px;
    }

    #stockInRoot .calc-preview-heading {
        display: flex;
        align-items: center;
        gap: 9px;
        padding-bottom: 12px;
        border-bottom: 1px solid #dbe3ef;
        margin-bottom: 10px;
    }

    #stockInRoot .calc-preview-heading-icon {
        width: 30px;
        height: 30px;
        background: #dbeafe;
        color: #2563eb;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        flex-shrink: 0;
    }

    #stockInRoot .calc-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 9px 2px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 11px;
    }

    #stockInRoot .calc-row:last-child {
        border-bottom: 0;
    }

    #stockInRoot .calc-row > span:first-child {
        color: #64748b;
    }

    #stockInRoot .calc-row > span:last-child,
    #stockInRoot .calc-row > strong {
        text-align: right;
        overflow-wrap: anywhere;
    }

    #stockInRoot .calc-highlight {
        background: #fff;
        border: 1px solid #e2e8f0;
        padding: 10px;
        margin: 7px 0;
    }

    #stockInRoot .calc-highlight .calc-row {
        border: 0;
        padding: 0;
    }

    #stockInRoot .calc-total-box {
        padding: 13px;
        margin-top: 12px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 5px;
    }

    #stockInRoot .calc-total-box strong {
        font-size: 17px;
        letter-spacing: -.3px;
    }

    /* Integrity card */
    #stockInRoot .integrity-card {
        background: #fff;
        border: 1px solid var(--si-border);
        padding: 14px !important;
    }

    #stockInRoot .integrity-rule {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-top: 9px;
        line-height: 1.5;
    }

    #stockInRoot .integrity-rule i {
        color: var(--si-success);
        margin-top: 2px;
        flex-shrink: 0;
    }

    /* Submit area */
    #stockInRoot .form-submit-area {
        border-top: 1px solid var(--si-border);
        padding-top: 14px;
        margin-top: 16px;
    }

    #stockInRoot #btnSubmitStockIn:disabled {
        opacity: .55;
        cursor: not-allowed;
        transform: none;
    }

    /* Modal */
    .stock-in-modal-scope {
        z-index: 106000;
    }

    .stock-in-modal-scope .modal-content {
        border: 1px solid #e2e8f0 !important;
        overflow: hidden;
    }

    .stock-in-modal-scope .modal-header {
        padding: 13px 15px;
    }

    .stock-in-modal-scope .modal-body {
        padding: 14px;
    }

    .stock-in-modal-scope .modal-footer {
        border-top: 1px solid #e2e8f0;
    }

    #stockInNoticeIconWrap {
        border-radius: 5px !important;
    }

    /* Responsive */
    @media (min-width: 992px) {
        #stockInRoot .stock-in-main-row {
            align-items: flex-start;
        }
    }

    @media (max-width: 991.98px) {
        #stockInRoot .calc-preview-card {
            position: static;
        }
    }

    @media (max-width: 767.98px) {
        #stockInRoot {
            padding-top: 12px !important;
        }

        #stockInRoot .stock-in-header {
            align-items: flex-start !important;
            padding-bottom: 12px;
        }

        #stockInRoot .stock-in-header h5 {
            font-size: 16px !important;
            line-height: 1.4;
        }

        #stockInRoot .stock-in-header > div:first-child {
            width: 100%;
        }

        #stockInRoot .header-actions-wrap {
            width: 100%;
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 7px !important;
        }

        #stockInRoot .header-actions-wrap .btn {
            width: 100%;
            min-height: 36px;
            padding: 7px 5px !important;
            font-size: 10px !important;
            justify-content: center;
            white-space: normal;
        }

        #stockInRoot .card {
            padding: 12px !important;
            margin-bottom: 12px !important;
        }

        #stockInRoot .calc-preview-card {
            padding: 13px !important;
            margin-bottom: 12px !important;
        }

        #stockInRoot .section-heading {
            margin-bottom: 13px;
        }

        #stockInRoot .form-row-block {
            margin-bottom: 10px !important;
        }

        #stockInRoot .selected-product-card {
            padding: 10px !important;
        }

        #stockInRoot .proof-media-surface {
            padding: 10px;
        }

        #stockInRoot .form-submit-area {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        #stockInRoot .form-submit-area button {
            flex: 1 1 auto;
            min-height: 36px;
        }
    }

    @media (max-width: 420px) {
        #stockInRoot .calc-row {
            font-size: 10px;
        }

        #stockInRoot .search-result-item {
            padding: 9px 8px;
        }

        #stockInRoot .search-result-item img {
            width: 34px;
            height: 34px;
        }

        #stockInRoot #activeTargetBadgeContainer {
            gap: 5px !important;
        }

        #stockInRoot #activeTargetBadgeContainer .badge {
            white-space: normal;
        }

        #stockInRoot .proof-media-surface .btn-compact {
            white-space: normal;
            text-align: center;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #stockInRoot *,
        .stock-in-modal-scope * {
            transition-duration: .01ms !important;
            animation-duration: .01ms !important;
            scroll-behavior: auto !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="stockInRoot">

    {{-- =====================================================
         PAGE HEADER
    ====================================================== --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3 stock-in-header">
        <div>
            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2" style="font-size:18px;">
                <span class="header-icon">
                    <i class="fas fa-boxes-packing"></i>
                </span>
                <span>Stock In / Inbound Inventory</span>
            </h5>
            <small class="text-muted d-block ps-1" style="font-size:11px; line-height:1.5;">
                Add inbound shipments, opening stock, or customer returns with atomic WAC recalculation.
            </small>
        </div>

        <div class="header-actions-wrap d-flex flex-wrap gap-2">
            <a href="{{ route('admin.inventory.balance') }}"
               class="btn btn-sm btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-scale-balanced me-1"></i>
                <span>View Stock Balance</span>
            </a>

            <a href="{{ route('admin.inventory.movements') }}"
               class="btn btn-sm btn-outline-primary btn-compact shadow-xs">
                <i class="fas fa-clipboard-list me-1"></i>
                <span>Inventory Ledger</span>
            </a>
        </div>
    </div>

    {{-- =====================================================
         SUCCESS MESSAGE
    ====================================================== --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 small mb-3 d-flex align-items-center justify-content-between"
             role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-circle-check text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- =====================================================
         VALIDATION ERRORS
    ====================================================== --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small mb-3"
             role="alert">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fas fa-triangle-exclamation text-danger fs-5"></i>
                <strong class="text-dark">Please correct the following errors:</strong>
            </div>

            <ul class="mb-0 ps-4 text-dark">
                @foreach($errors->all() as $err)
                    <li class="mb-1">{{ $err }}</li>
                @endforeach
            </ul>

            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- =====================================================
         MAIN CONTENT
    ====================================================== --}}
    <div class="row g-3 stock-in-main-row">

        {{-- LEFT: PRODUCT SELECTOR + STOCK IN FORM --}}
        <div class="col-12 col-lg-8">

            {{-- =================================================
                 1. PRODUCT SELECTOR
            ================================================== --}}
            <div class="card">

                <div class="section-heading">
                    <span class="section-heading-icon">
                        <i class="fas fa-search"></i>
                    </span>
                    <div>
                        <h6>1. Select Product &amp; Variant</h6>
                        <small>Find a product and select the exact inventory target.</small>
                    </div>
                </div>

                {{-- Live AJAX Search --}}
                <div class="position-relative mb-3">

                    <label class="form-label" for="liveSearchInput">
                        Search by Product Title, ID, SKU, Barcode, Color, or Size
                    </label>

                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0">
                            <i class="fas fa-barcode" style="font-size:12px;"></i>
                        </span>

                        <input type="text"
                               id="liveSearchInput"
                               class="form-control border-start-0"
                               placeholder="Type product name, barcode, or SKU..."
                               autocomplete="off"
                               aria-label="Search products">

                        <button type="button"
                                class="btn btn-light border"
                                id="btnClearSearch"
                                onclick="clearProductSelection()"
                                style="display:none;"
                                aria-label="Clear product search">
                            <i class="fas fa-times text-muted"></i>
                        </button>
                    </div>

                    {{-- AJAX Search Results --}}
                    <div id="searchResultsPanel"
                         class="search-result-panel d-none"
                         style="border-radius:5px;">
                        <div id="searchResultsList"></div>
                    </div>
                </div>

                {{-- Selected Product Preview --}}
                <div id="selectedProductCard"
                     class="selected-product-card p-3 border bg-light d-none"
                     style="border-radius:5px;">

                    <div class="d-flex align-items-start gap-3 flex-wrap flex-sm-nowrap">

                        <img id="selProdImg"
                             src="https://placehold.co/60x60?text=Item"
                             class="border object-fit-cover shadow-xs flex-shrink-0"
                             style="width:56px;height:56px;border-radius:5px;"
                             alt="Product thumbnail">

                        <div class="flex-grow-1 min-w-0">

                            <div class="d-flex justify-content-between align-items-start gap-2">

                                <div class="min-w-0">
                                    <h6 class="fw-bold text-dark mb-1 text-truncate"
                                        id="selProdTitle"
                                        style="font-size:13px;">
                                        Product Title
                                    </h6>

                                    <div class="small text-muted font-monospace" style="font-size:10px;">
                                        ID:
                                        <span id="selProdId" class="fw-bold text-secondary">#0</span>
                                        <span class="mx-1">|</span>
                                        SKU:
                                        <span id="selProdSku" class="fw-bold text-dark">—</span>
                                    </div>
                                </div>

                                <button type="button"
                                        class="btn btn-sm btn-outline-danger btn-compact flex-shrink-0"
                                        onclick="clearProductSelection()">
                                    <i class="fas fa-rotate-left me-1"></i>
                                    Change
                                </button>
                            </div>

                            {{-- Variant Selection --}}
                            <div id="variantPickerWrapper" class="mt-3 pt-2 border-top d-none">
                                <label class="form-label text-primary mb-2 d-block">
                                    <i class="fas fa-layer-group me-1"></i>
                                    Select Variant <span class="text-danger">*</span>
                                </label>

                                <div class="d-flex flex-wrap gap-2" id="variantPillContainer"></div>
                            </div>

                            {{-- Product Stock Details --}}
                            <div class="mt-3 d-flex flex-wrap align-items-center gap-2"
                                 id="activeTargetBadgeContainer">

                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"
                                      id="badgeTargetStruct">
                                    Simple Product
                                </span>

                                <span class="badge bg-dark-subtle text-dark border px-2 py-1 font-monospace"
                                      id="badgeCurrentStock">
                                    Current Stock: 0
                                </span>

                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 font-monospace"
                                      id="badgeCurrentCost">
                                    Current Cost: ৳0.00
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =================================================
                 2. INBOUND CONSIGNMENT FORM
            ================================================== --}}
            <div class="card">

                <div class="section-heading">
                    <span class="section-heading-icon success">
                        <i class="fas fa-file-invoice"></i>
                    </span>
                    <div>
                        <h6>2. Shipment Information &amp; Quantities</h6>
                        <small>Enter supplier, warehouse, quantity and cost information.</small>
                    </div>
                </div>

                <form id="stockInForm"
                      action="{{ route('admin.inventory.stock_in.store') }}"
                      method="POST">

                    @csrf

                    {{-- Hidden Product References --}}
                    <input type="hidden" name="product_id" id="form_product_id" required>
                    <input type="hidden" name="variant_id" id="form_variant_id">
                    <input type="hidden" name="supplier_challan_slip" id="form_supplier_challan_slip" value="">

                    <div class="stock-form-section">

                        {{-- Row 1: Type, Supplier, Date --}}
                        <div class="row g-2 form-row-block">

                            <div class="col-12 col-md-4">
                                <label class="form-label" for="form_movement_type">
                                    Stock In Type <span class="text-danger">*</span>
                                </label>

                                <select name="movement_type"
                                        id="form_movement_type"
                                        class="form-select shadow-none font-monospace"
                                        required>
                                    <option value="purchase_in" selected>New Purchase Inbound</option>
                                    <option value="opening_stock">Opening Stock Balance</option>
                                    <option value="customer_return">Customer Return Restock</option>
                                    <option value="supplier_replacement">Supplier Replacement</option>
                                    <option value="stock_correction">Stock Correction / Audit In</option>
                                    <option value="other_addition">Other Approved Addition</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label" for="form_supplier_id">
                                    Supplier / Vendor
                                </label>

                                <select name="supplier_id"
                                        id="form_supplier_id"
                                        class="form-select shadow-none font-monospace">
                                    <option value="">— Optional —</option>

                                    @foreach($suppliers as $sup)
                                        <option value="{{ $sup->id }}">
                                            {{ $sup->name }} {{ !empty($sup->company_name) ? "({$sup->company_name})" : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label" for="form_stock_in_date">
                                    Stock In Date
                                </label>

                                <input type="date"
                                       name="stock_in_date"
                                       id="form_stock_in_date"
                                       class="form-control font-monospace shadow-none"
                                       value="{{ date('Y-m-d') }}">
                            </div>
                        </div>

                        {{-- Row 2: Warehouse, Branch, Reference, Invoice --}}
                        <div class="row g-2 form-row-block">

                            <div class="col-12 col-md-3">
                                <label class="form-label" for="form_warehouse_id">
                                    Target Warehouse
                                </label>

                                <select name="warehouse_id"
                                        id="form_warehouse_id"
                                        class="form-select shadow-none font-monospace">
                                    <option value="">Default Warehouse</option>

                                    @foreach($warehouses ?? [] as $wh)
                                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label" for="form_branch_id">
                                    Operating Branch
                                </label>

                                <select name="branch_id"
                                        id="form_branch_id"
                                        class="form-select shadow-none font-monospace">
                                    <option value="">HQ Branch</option>

                                    @foreach($branches ?? [] as $br)
                                        <option value="{{ $br->id }}">{{ $br->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label" for="form_reference_id">
                                    Reference / PO # <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="reference_id"
                                       id="form_reference_id"
                                       class="form-control font-monospace shadow-none"
                                       value="{{ $defaultPoNumber }}"
                                       required>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label" for="form_supplier_invoice_no">
                                    Challan / Invoice #
                                </label>

                                <input type="text"
                                       name="supplier_invoice_no"
                                       id="form_supplier_invoice_no"
                                       class="form-control font-monospace shadow-none"
                                       placeholder="INV-8891">
                            </div>
                        </div>

                        {{-- Row 3: Batch & Expiry --}}
                        <div class="row g-2 form-row-block">

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="form_batch_no">
                                    Batch / Lot # (Optional)
                                </label>

                                <input type="text"
                                       name="batch_no"
                                       id="form_batch_no"
                                       class="form-control font-monospace shadow-none"
                                       placeholder="BATCH-202611-A">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="form_expiry_date">
                                    Expiry Date (Optional)
                                </label>

                                <input type="date"
                                       name="expiry_date"
                                       id="form_expiry_date"
                                       class="form-control font-monospace shadow-none">
                            </div>
                        </div>

                        {{-- Row 4: Quantity, Cost & Selling Price --}}
                        <div class="row g-2 form-row-block">

                            <div class="col-12 col-md-4">
                                <label class="form-label" for="form_quantity">
                                    Inbound Quantity <span class="text-danger">*</span>
                                </label>

                                <div class="input-group">
                                    <input type="number"
                                           name="quantity"
                                           id="form_quantity"
                                           class="form-control font-monospace fw-bold text-success shadow-none"
                                           min="1"
                                           placeholder="50"
                                           required
                                           oninput="recalculatePreview()">

                                    <span class="input-group-text bg-light text-muted font-monospace">
                                        Units
                                    </span>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label" for="form_purchase_cost">
                                    Unit Purchase Cost (৳)
                                </label>

                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted font-monospace">৳</span>

                                    <input type="number"
                                           step="0.01"
                                           name="purchase_cost"
                                           id="form_purchase_cost"
                                           class="form-control font-monospace shadow-none"
                                           placeholder="350.00"
                                           oninput="recalculatePreview()">
                                </div>

                                <small class="field-help">
                                    Leave blank to use current unit cost.
                                </small>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label" for="form_selling_price">
                                    Selling Price (৳) (Optional)
                                </label>

                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted font-monospace">৳</span>

                                    <input type="number"
                                           step="0.01"
                                           name="selling_price"
                                           id="form_selling_price"
                                           class="form-control font-monospace shadow-none"
                                           placeholder="499.00">
                                </div>
                            </div>
                        </div>

                        {{-- WAC Switch --}}
                        <div class="wac-switch-wrap mb-3">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input shadow-none"
                                       type="checkbox"
                                       name="update_wac"
                                       id="form_update_wac"
                                       value="1"
                                       checked
                                       onchange="recalculatePreview()"
                                       style="cursor:pointer;">

                                <label class="form-check-label small fw-semibold text-dark"
                                       for="form_update_wac"
                                       style="font-size:11px;cursor:pointer;">
                                    Apply Weighted Average Costing (WAC) formula for inventory valuation
                                </label>
                            </div>

                            <small class="text-muted d-block mt-1 ms-4" style="font-size:10px;">
                                Recalculates the average inventory cost using current stock and inbound purchase cost.
                            </small>
                        </div>

                        {{-- Supplier Challan Attachment --}}
                        <div class="mb-3">

                            <label class="form-label d-flex align-items-center justify-content-between mb-2">
                                <span>
                                    <i class="fas fa-paperclip text-primary me-1"></i>
                                    Supplier Challan / Memo Scan
                                </span>

                                <span class="badge bg-light text-muted border font-monospace"
                                      style="font-size:9px;">
                                    MEDIA LIBRARY
                                </span>
                            </label>

                            <div class="proof-media-surface" style="border-radius:5px;">

                                {{-- Empty State --}}
                                <div id="noChallanAttachedNotice" class="text-center py-3">

                                    <div class="proof-empty-icon-box mx-auto mb-2">
                                        <i class="fas fa-cloud-arrow-up text-muted"></i>
                                    </div>

                                    <h6 class="fw-bold text-dark mb-1" style="font-size:12px;">
                                        No Document Attached
                                    </h6>

                                    <p class="text-muted small mb-3" style="font-size:10px;">
                                        Attach supplier delivery challan, bill copy, or shipment proof.
                                    </p>

                                    <button type="button"
                                            class="btn btn-primary btn-compact shadow-xs"
                                            onclick="chooseSupplierChallanMedia()">
                                        <i class="fas fa-photo-film me-1"></i>
                                        <span>Choose from Media Library</span>
                                    </button>
                                </div>

                                {{-- Attached Preview --}}
                                <div id="challanPreviewContainer"
                                     class="d-none bg-white border p-2"
                                     style="border-radius:5px;">

                                    <div class="d-flex align-items-center gap-2 mb-2">

                                        <div class="proof-thumb-box"
                                             onclick="zoomChallanMedia()"
                                             title="Click to Zoom">

                                            <img id="challanPreviewImg"
                                                 src="https://placehold.co/120x120?text=Challan"
                                                 alt="Challan"
                                                 onerror="this.src='https://placehold.co/120x120?text=Challan';">

                                            <div class="proof-thumb-hover-overlay">
                                                <i class="fas fa-search-plus text-white" style="font-size:10px;"></i>
                                            </div>
                                        </div>

                                        <div class="min-w-0 flex-grow-1">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle"
                                                  style="font-size:9px;">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Attached
                                            </span>

                                            <strong class="d-block text-dark text-truncate font-monospace mt-1"
                                                    id="challanPathText"
                                                    style="font-size:10px;">
                                                challan_proof.jpg
                                            </strong>

                                            <small class="text-muted" style="font-size:9px;">
                                                Supplier document successfully selected
                                            </small>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-2 border-top pt-2">

                                        <button type="button"
                                                class="btn btn-outline-secondary btn-compact flex-grow-1 py-1 px-1"
                                                onclick="zoomChallanMedia()">
                                            <i class="fas fa-expand me-1"></i>
                                            <span>Zoom</span>
                                        </button>

                                        <button type="button"
                                                class="btn btn-outline-primary btn-compact flex-grow-1 py-1 px-1"
                                                onclick="chooseSupplierChallanMedia()">
                                            <i class="fas fa-arrows-rotate me-1"></i>
                                            <span>Change</span>
                                        </button>

                                        <button type="button"
                                                class="btn btn-outline-danger btn-compact flex-grow-1 py-1 px-1"
                                                onclick="clearSupplierChallanMedia()">
                                            <i class="fas fa-trash-alt me-1"></i>
                                            <span>Remove</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="mb-3">
                            <label class="form-label" for="form_notes">
                                Consignment Notes / Audit Remarks
                            </label>

                            <textarea name="notes"
                                      id="form_notes"
                                      class="form-control shadow-none"
                                      rows="3"
                                      style="font-size:11px;"
                                      placeholder="Gate entry remarks, receiving condition, inspector note..."></textarea>
                        </div>

                        {{-- Form Submit --}}
                        <div class="form-submit-area d-flex justify-content-end gap-2">

                            <button type="button"
                                    class="btn btn-sm btn-light border btn-compact px-3"
                                    onclick="clearProductSelection()">
                                <i class="fas fa-rotate-left me-1"></i>
                                Reset
                            </button>

                            <button type="submit"
                                    class="btn btn-sm btn-success fw-bold px-4 shadow-xs btn-compact"
                                    id="btnSubmitStockIn"
                                    disabled>
                                <i class="fas fa-check-circle me-1"></i>
                                Complete Stock In
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- =================================================
             RIGHT: LIVE VALUATION PREVIEW
        ================================================== --}}
        <div class="col-12 col-lg-4">

            {{-- Live Calculation --}}
            <div class="calc-preview-card" style="border-radius:5px;">

                <div class="calc-preview-heading">
                    <span class="calc-preview-heading-icon">
                        <i class="fas fa-calculator"></i>
                    </span>

                    <div>
                        <h6 class="fw-bold text-dark mb-0" style="font-size:13px;">
                            Live Valuation Preview
                        </h6>
                        <small class="text-muted" style="font-size:10px;">
                            Automatically calculated stock value
                        </small>
                    </div>
                </div>

                <div class="calc-row">
                    <span>Target:</span>
                    <strong class="text-dark text-truncate ms-2"
                            style="max-width:170px;"
                            id="calcTargetLabel">
                        None Selected
                    </strong>
                </div>

                <div class="calc-row">
                    <span>Current Stock:</span>
                    <span class="font-monospace fw-bold text-dark" id="calcCurStock">
                        0 Units
                    </span>
                </div>

                <div class="calc-row">
                    <span>Inbound Quantity:</span>
                    <span class="font-monospace fw-bold text-success" id="calcInboundQty">
                        +0 Units
                    </span>
                </div>

                <div class="calc-highlight">
                    <div class="calc-row">
                        <span class="fw-bold text-dark">New Total Stock:</span>
                        <span class="font-monospace fw-bold text-primary"
                              style="font-size:14px;"
                              id="calcNewStock">
                            0 Units
                        </span>
                    </div>
                </div>

                <div class="calc-row">
                    <span>Current Unit Cost:</span>
                    <span class="font-monospace text-muted" id="calcCurCost">
                        ৳0.00
                    </span>
                </div>

                <div class="calc-row">
                    <span>Inbound Unit Cost:</span>
                    <span class="font-monospace text-success fw-semibold" id="calcInboundCost">
                        ৳0.00
                    </span>
                </div>

                <div class="calc-highlight">
                    <div class="calc-row">
                        <span class="fw-bold text-dark">New WAC Cost:</span>
                        <span class="font-monospace fw-bold text-info-emphasis"
                              style="font-size:14px;"
                              id="calcNewWac">
                            ৳0.00
                        </span>
                    </div>
                </div>

                {{-- Total Inbound Value --}}
                <div class="calc-total-box">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <span class="d-block small fw-bold text-dark">
                                Inbound Consignment Value
                            </span>
                            <small class="text-muted" style="font-size:9px;">
                                Quantity × Unit Cost
                            </small>
                        </div>

                        <strong class="font-monospace text-primary text-end"
                                id="calcTotalInboundVal">
                            ৳ 0.00
                        </strong>
                    </div>
                </div>
            </div>

            {{-- Stock Integrity --}}
            <div class="card integrity-card">

                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="section-heading-icon success">
                        <i class="fas fa-shield-halved"></i>
                    </span>

                    <div>
                        <h6 class="fw-bold text-dark mb-0" style="font-size:12px;">
                            Stock Integrity Rule
                        </h6>
                        <small class="text-muted" style="font-size:10px;">
                            Inventory validation guidelines
                        </small>
                    </div>
                </div>

                <div class="integrity-rule">
                    <i class="fas fa-circle-check"></i>
                    <p class="text-muted small mb-0" style="font-size:10px;">
                        Variable products require selecting a specific <strong>Variant</strong>
                        to maintain isolated stock balance.
                    </p>
                </div>

                <div class="integrity-rule">
                    <i class="fas fa-circle-check"></i>
                    <p class="text-muted small mb-0" style="font-size:10px;">
                        Unique reference tracking prevents duplicate inbound consignment records.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- =========================================================
     MODAL 1: CHALLAN IMAGE ZOOM
========================================================= --}}
<div class="modal fade stock-in-modal-scope"
     id="challanZoomModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index:106000;">

    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">

            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size:12.5px;">
                    <i class="fas fa-image text-primary me-1"></i>
                    Supplier Challan Slip
                </span>

                <button type="button"
                        class="btn-close btn-sm shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <div class="modal-body p-2">
                <img id="modalZoomChallanImgTag"
                     src=""
                     alt="Challan Slip"
                     class="img-fluid border"
                     style="max-height:480px;object-fit:contain;border-radius:5px;">
            </div>
        </div>
    </div>
</div>

{{-- =========================================================
     MODAL 2: NOTICE DIALOG
========================================================= --}}
<div class="modal fade stock-in-modal-scope"
     id="stockInNoticeModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index:106500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">

            <div class="modal-body p-1">

                <div class="bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-3"
                     id="stockInNoticeIconWrap"
                     style="width:46px;height:46px;border-radius:5px !important;">

                    <i class="fas fa-circle-info fs-4" id="stockInNoticeIcon"></i>
                </div>

                <h6 class="fw-bold text-dark mb-1"
                    id="stockInNoticeTitle"
                    style="font-size:14px;">
                    Notification
                </h6>

                <p class="text-muted small mb-3"
                   id="stockInNoticeMsg"
                   style="font-size:12px;line-height:1.5;"></p>

                <button type="button"
                        class="btn btn-dark btn-compact px-4"
                        data-bs-dismiss="modal">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

{{-- =========================================================
     JAVASCRIPT — EXISTING FUNCTIONALITY PRESERVED
========================================================= --}}
<script>
    let currentSelectedProduct = null;
    let currentSelectedVariant = null;
    let searchDebounceTimer = null;

    /* =====================================================
       NOTICE MODAL
    ====================================================== */
    function showStockInNotice(message, title = 'Notification', type = 'info') {
        const titleEl  = document.getElementById('stockInNoticeTitle');
        const msgEl    = document.getElementById('stockInNoticeMsg');
        const iconWrap = document.getElementById('stockInNoticeIconWrap');
        const iconEl   = document.getElementById('stockInNoticeIcon');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;

        if (iconWrap && iconEl) {
            if (type === 'error' || type === 'danger') {
                iconWrap.className = 'bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-3';
                iconEl.className = 'fas fa-circle-exclamation fs-4';
            } else if (type === 'success') {
                iconWrap.className = 'bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-3';
                iconEl.className = 'fas fa-circle-check fs-4';
            } else {
                iconWrap.className = 'bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-3';
                iconEl.className = 'fas fa-circle-info fs-4';
            }
        }

        const modalEl = document.getElementById('stockInNoticeModal');

        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    /* =====================================================
       MEDIA ITEM NORMALIZER
    ====================================================== */
    function extractMediaItem(mediaInput) {
        if (!mediaInput) return null;

        const item = Array.isArray(mediaInput) ? mediaInput[0] : mediaInput;

        if (!item || typeof item !== 'object') return null;

        const rawPath = item.file_path || item.path || item.url || '';
        const rawUrl  = item.url || item.file_path || item.path || '';

        if (!rawPath || rawPath === 'undefined') return null;

        return {
            path: String(rawPath).trim(),
            url: String(rawUrl).trim()
        };
    }

    /* =====================================================
       CHOOSE SUPPLIER CHALLAN MEDIA
    ====================================================== */
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

                        showStockInNotice(
                            'Supplier challan attached from Global Media Library.',
                            'Attached',
                            'success'
                        );
                    }
                }
            });

        } else {
            showStockInNotice(
                'Global Media Library is loading, please try again.',
                'Notice'
            );
        }
    }

    /* =====================================================
       CLEAR SUPPLIER CHALLAN
    ====================================================== */
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

        showStockInNotice(
            'Supplier challan attachment cleared.',
            'Cleared',
            'info'
        );
    }

    /* =====================================================
       ZOOM CHALLAN MEDIA
    ====================================================== */
    function zoomChallanMedia() {
        const previewImg = document.getElementById('challanPreviewImg');
        const zoomImgTag = document.getElementById('modalZoomChallanImgTag');

        if (previewImg && zoomImgTag && previewImg.src) {
            zoomImgTag.src = previewImg.src;

            bootstrap.Modal
                .getOrCreateInstance(document.getElementById('challanZoomModal'))
                .show();
        }
    }

    /* =====================================================
       LIVE AJAX PRODUCT SEARCH
    ====================================================== */
    document.getElementById('liveSearchInput').addEventListener('input', function(e) {

        clearTimeout(searchDebounceTimer);

        const term  = this.value.trim();
        const panel = document.getElementById('searchResultsPanel');
        const list  = document.getElementById('searchResultsList');

        if (term.length < 1) {
            panel.classList.add('d-none');
            return;
        }

        searchDebounceTimer = setTimeout(() => {

            fetch(`{{ route('admin.inventory.search.products.ajax') }}?q=${encodeURIComponent(term)}`, {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {

                list.innerHTML = '';

                if (!Array.isArray(data) || data.length === 0) {

                    list.innerHTML = `
                        <div class="p-3 text-muted text-center small">
                            <i class="fas fa-box-open me-1 opacity-50"></i>
                            No matching products found for
                            "<strong>${escapeHtml(term)}</strong>"
                        </div>
                    `;

                } else {

                    data.forEach(p => {

                        const isVar = p.product_struct === 'variable';

                        const structBadge = isVar
                            ? `<span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:9px;">Variable (${p.variants.length} Variants)</span>`
                            : `<span class="badge bg-light text-dark border" style="font-size:9px;">Simple Product</span>`;

                        const itemHtml = `
                            <div class="search-result-item d-flex align-items-center justify-content-between"
                                 onclick="handleProductSelect(${escapeJson(p)})">

                                <div class="d-flex align-items-center gap-2 overflow-hidden">

                                    <img src="${p.thumbnail}"
                                         class="border object-fit-cover shadow-xs flex-shrink-0"
                                         style="width:40px;height:40px;border-radius:5px;"
                                         alt="thumb"
                                         onerror="this.src='https://placehold.co/60x60?text=Item';">

                                    <div class="overflow-hidden">
                                        <span class="fw-bold text-dark d-block text-truncate"
                                              style="font-size:12px;">
                                            ${escapeHtml(p.title)}
                                        </span>

                                        <small class="text-muted font-monospace"
                                               style="font-size:10px;">
                                            ID: #${p.id} | SKU: ${p.sku} | Stock: ${p.current_stock}
                                        </small>
                                    </div>
                                </div>

                                <div class="text-end flex-shrink-0 ms-2">
                                    ${structBadge}

                                    <span class="d-block font-monospace fw-bold text-dark small mt-1">
                                        ৳${Number(p.purchase_cost).toFixed(2)}
                                    </span>
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

    /* =====================================================
       CLOSE SEARCH PANEL ON OUTSIDE CLICK
    ====================================================== */
    document.addEventListener('click', function(e) {

        const searchInput = document.getElementById('liveSearchInput');
        const resultsPanel = document.getElementById('searchResultsPanel');

        if (
            searchInput &&
            resultsPanel &&
            !searchInput.contains(e.target) &&
            !resultsPanel.contains(e.target)
        ) {
            resultsPanel.classList.add('d-none');
        }
    });

    /* =====================================================
       PRODUCT SELECTION
    ====================================================== */
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

                pill.className = 'variant-pill-select font-monospace small';
                pill.style.borderRadius = '5px';

                pill.innerHTML = `
                    <strong>${escapeHtml(v.title)}</strong>
                    (SKU: ${v.sku}) [Stock: ${v.stock}]
                `;

                pill.onclick = function() {

                    document.querySelectorAll('.variant-pill-select').forEach(el => {
                        el.classList.remove('active');
                    });

                    pill.classList.add('active');

                    handleVariantSelect(v);
                };

                pillContainer.appendChild(pill);
            });

            document.getElementById('badgeTargetStruct').className =
                'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle';

            document.getElementById('badgeTargetStruct').innerText = 'Select a Variant Above';

            document.getElementById('badgeCurrentStock').innerText = 'Current Stock: —';
            document.getElementById('badgeCurrentCost').innerText = 'Current Cost: —';

            document.getElementById('calcTargetLabel').innerText =
                `${prod.title} (Select Variant)`;

        } else {

            variantWrapper.classList.add('d-none');

            document.getElementById('badgeTargetStruct').className =
                'badge bg-light text-dark border';

            document.getElementById('badgeTargetStruct').innerText = 'Simple Product';

            document.getElementById('badgeCurrentStock').innerText =
                `Current Stock: ${prod.current_stock}`;

            document.getElementById('badgeCurrentCost').innerText =
                `Current Cost: ৳${Number(prod.purchase_cost).toFixed(2)}`;

            document.getElementById('form_purchase_cost').value =
                prod.purchase_cost > 0 ? prod.purchase_cost : '';

            document.getElementById('form_selling_price').value =
                prod.selling_price > 0 ? prod.selling_price : '';

            document.getElementById('btnSubmitStockIn').disabled = false;

            recalculatePreview();
        }
    }

    /* =====================================================
       VARIANT SELECTION
    ====================================================== */
    function handleVariantSelect(variant) {

        currentSelectedVariant = variant;

        document.getElementById('form_variant_id').value = variant.id;

        document.getElementById('selProdSku').innerText = variant.sku;

        document.getElementById('badgeTargetStruct').className =
            'badge bg-primary-subtle text-primary border border-primary-subtle';

        document.getElementById('badgeTargetStruct').innerText =
            `Variant: ${variant.title}`;

        document.getElementById('badgeCurrentStock').innerText =
            `Current Stock: ${variant.stock}`;

        document.getElementById('badgeCurrentCost').innerText =
            `Current Cost: ৳${Number(variant.purchase_cost).toFixed(2)}`;

        document.getElementById('form_purchase_cost').value =
            variant.purchase_cost > 0 ? variant.purchase_cost : '';

        document.getElementById('form_selling_price').value =
            variant.selling_price > 0 ? variant.selling_price : '';

        document.getElementById('btnSubmitStockIn').disabled = false;

        recalculatePreview();
    }

    /* =====================================================
       CLEAR PRODUCT SELECTION
    ====================================================== */
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

        document.getElementById('variantPickerWrapper').classList.add('d-none');
        document.getElementById('variantPillContainer').innerHTML = '';

        document.getElementById('btnSubmitStockIn').disabled = true;

        recalculatePreview();
    }

    /* =====================================================
       LIVE WAC & STOCK VALUATION CALCULATION
    ====================================================== */
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

                newWac = (
                    (curStock * curCost) +
                    (inQty * inCost)
                ) / (curStock + inQty);

            } else {

                newWac = inCost;
            }

        } else if (inCost <= 0) {

            newWac = curCost;
        }

        const totalInboundVal = inQty * (inCost > 0 ? inCost : curCost);

        document.getElementById('calcTargetLabel').innerText = targetName;

        document.getElementById('calcCurStock').innerText =
            `${curStock} Units`;

        document.getElementById('calcInboundQty').innerText =
            `+${inQty} Units`;

        document.getElementById('calcNewStock').innerText =
            `${newStock} Units`;

        document.getElementById('calcCurCost').innerText =
            `৳${curCost.toFixed(2)}`;

        document.getElementById('calcInboundCost').innerText =
            `৳${(inCost > 0 ? inCost : curCost).toFixed(2)}`;

        document.getElementById('calcNewWac').innerText =
            `৳${newWac.toFixed(2)}`;

        document.getElementById('calcTotalInboundVal').innerText =
            `৳ ${totalInboundVal.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })}`;
    }

    /* =====================================================
       HTML ESCAPE
    ====================================================== */
    function escapeHtml(str) {

        if (!str) return '';

        return String(str).replace(/[&<>"']/g, function(m) {

            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[m];
        });
    }

    /* =====================================================
       JSON ESCAPE
    ====================================================== */
    function escapeJson(obj) {
        return JSON.stringify(obj).replace(/"/g, '&quot;');
    }
</script>
@endsection