
@extends('layouts.admin')

@section('title', 'Non-Sale Stock Out & Waste Management - ' . config('app.name', 'Shop Now BD'))

@section('admin_content')
<style>
    /* =========================================================
       STOCK OUT — MODERN RESPONSIVE UI
       Strict 5px radius | Fully scoped styles
    ========================================================= */

    #stockOutRoot {
        --so-primary: #2563eb;
        --so-danger: #dc3545;
        --so-text: #172033;
        --so-muted: #64748b;
        --so-border: #e2e8f0;
        --so-surface: #ffffff;
        --so-soft: #f8fafc;
        --so-radius: 5px;
        color: var(--so-text);
    }

    #stockOutRoot *,
    .stock-out-modal-scope * {
        box-sizing: border-box;
    }

    #stockOutRoot .btn,
    #stockOutRoot button,
    #stockOutRoot .form-control,
    #stockOutRoot .form-select,
    #stockOutRoot .badge,
    #stockOutRoot .alert,
    #stockOutRoot .input-group-text,
    #stockOutRoot .card,
    #stockOutRoot .loss-preview-card,
    #stockOutRoot .variant-pill-select,
    #stockOutRoot .search-result-panel,
    #stockOutRoot .proof-media-surface,
    #stockOutRoot .proof-thumb-box,
    #stockOutRoot .selected-product-panel,
    .stock-out-modal-scope .modal-content,
    .stock-out-modal-scope .btn,
    .stock-out-modal-scope .form-control {
        border-radius: 5px !important;
    }

    #stockOutRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    #stockOutRoot .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    #stockOutRoot .font-monospace,
    #stockOutRoot .bn-digit {
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums;
    }

    /* Header */
    #stockOutRoot .stock-out-header {
        padding: 2px 0 15px;
        border-bottom: 1px solid var(--so-border);
    }

    #stockOutRoot .page-title-icon {
        width: 36px;
        height: 36px;
        min-width: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff1f2;
        color: #dc3545;
        border: 1px solid #fecdd3;
        border-radius: 5px;
        font-size: 15px;
    }

    #stockOutRoot .page-heading {
        font-size: 18px;
        font-weight: 800;
        letter-spacing: -0.35px;
        color: #172033;
        line-height: 1.35;
    }

    #stockOutRoot .page-subtitle {
        font-size: 11.5px;
        color: var(--so-muted);
        line-height: 1.5;
        margin-top: 3px;
    }

    #stockOutRoot .header-actions-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }

    #stockOutRoot .btn-compact {
        min-height: 32px;
        padding: 6px 12px !important;
        font-size: 11px !important;
        line-height: 1.3 !important;
        font-weight: 700 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        white-space: nowrap;
        transition: all .18s ease;
    }

    #stockOutRoot .btn-compact:hover {
        transform: translateY(-1px);
    }

    #stockOutRoot .btn-danger {
        background: #dc3545;
        border-color: #dc3545;
    }

    #stockOutRoot .btn-danger:hover {
        background: #bb2d3b;
        border-color: #bb2d3b;
    }

    /* Cards */
    #stockOutRoot .card {
        border: 1px solid var(--so-border);
        background: var(--so-surface);
        box-shadow: 0 2px 8px rgba(15, 23, 42, .035);
        padding: 16px;
        margin-bottom: 16px;
        transition: box-shadow .18s ease;
    }

    #stockOutRoot .card:hover {
        box-shadow: 0 4px 13px rgba(15, 23, 42, .055);
    }

    #stockOutRoot .section-heading {
        display: flex;
        align-items: center;
        gap: 9px;
        font-size: 12.5px;
        font-weight: 800;
        color: #1e293b;
        padding-bottom: 11px;
        margin-bottom: 15px;
        border-bottom: 1px solid #edf0f4;
    }

    #stockOutRoot .section-number {
        display: inline-flex;
        width: 25px;
        height: 25px;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        background: #fff1f2;
        color: #dc3545;
        border: 1px solid #fecdd3;
        border-radius: 5px;
        font-size: 11px;
        font-weight: 800;
    }

    #stockOutRoot .section-heading-icon {
        color: #dc3545;
        font-size: 13px;
    }

    /* Form */
    #stockOutRoot .form-label {
        display: block;
        font-size: 11px;
        font-weight: 750;
        color: #334155;
        margin-bottom: 5px;
    }

    #stockOutRoot .form-control,
    #stockOutRoot .form-select {
        min-height: 36px;
        font-size: 12px;
        border: 1px solid #d7dee8;
        padding: 7px 10px;
        color: #172033;
        background-color: #fff;
        box-shadow: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    #stockOutRoot .form-control::placeholder {
        color: #94a3b8;
        opacity: 1;
    }

    #stockOutRoot .form-control:focus,
    #stockOutRoot .form-select:focus {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, .10);
        outline: none;
    }

    #stockOutRoot textarea.form-control {
        min-height: 72px;
        resize: vertical;
    }

    #stockOutRoot .input-group-text {
        min-height: 36px;
        padding: 6px 11px;
        border: 1px solid #d7dee8;
        background: #f8fafc;
        color: #64748b;
        font-size: 11px;
    }

    #stockOutRoot .form-hint {
        display: block;
        color: #94a3b8;
        font-size: 10px;
        margin-top: 4px;
    }

    #stockOutRoot .required-mark {
        color: #dc3545;
        font-weight: 800;
    }

    /* Live Search */
    #stockOutRoot .search-result-panel {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        z-index: 1050;
        max-height: 360px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #dbe2ea;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .13);
    }

    #stockOutRoot .search-result-item {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: background-color .15s ease;
    }

    #stockOutRoot .search-result-item:last-child {
        border-bottom: 0;
    }

    #stockOutRoot .search-result-item:hover,
    #stockOutRoot .search-result-item:focus {
        background: #f8fafc;
        outline: none;
    }

    #stockOutRoot .search-result-item img {
        width: 42px;
        height: 42px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
        border-radius: 5px;
        flex-shrink: 0;
    }

    #stockOutRoot .search-result-title {
        font-size: 12px;
        font-weight: 750;
        color: #1e293b;
    }

    #stockOutRoot .search-result-meta {
        font-size: 10px;
        color: #64748b;
        line-height: 1.5;
    }

    /* Selected Product */
    #stockOutRoot .selected-product-panel {
        background: #f8fafc;
        border: 1px solid #dbe3ed;
        padding: 13px;
        box-shadow: inset 3px 0 0 #dc3545;
    }

    #stockOutRoot .selected-product-image {
        width: 58px;
        height: 58px;
        object-fit: cover;
        flex-shrink: 0;
        border: 1px solid #dbe3ed;
        border-radius: 5px;
        background: #fff;
    }

    #stockOutRoot .selected-product-title {
        font-size: 13px;
        font-weight: 800;
        color: #172033;
        line-height: 1.4;
    }

    #stockOutRoot .selected-product-meta {
        font-size: 10.5px;
        color: #64748b;
        overflow-wrap: anywhere;
    }

    #stockOutRoot .variant-pill-select {
        border: 1px solid #d5dce6;
        background: #fff;
        padding: 7px 11px;
        cursor: pointer;
        transition: all .16s ease;
        display: inline-flex;
        align-items: center;
        font-size: 10.5px;
        color: #334155;
        text-align: left;
    }

    #stockOutRoot .variant-pill-select:hover,
    #stockOutRoot .variant-pill-select.active {
        border-color: #dc3545;
        background: #fff1f2;
        color: #b42332;
        font-weight: 800;
    }

    #stockOutRoot .variant-pill-select:focus-visible {
        outline: 2px solid rgba(220, 53, 69, .35);
        outline-offset: 2px;
    }

    #stockOutRoot .product-info-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 10px;
    }

    #stockOutRoot .product-info-badges .badge {
        font-size: 9.5px;
        padding: 5px 8px;
        font-weight: 700;
    }

    /* Loss Preview */
    #stockOutRoot .loss-preview-card {
        background: linear-gradient(160deg, #fff 0%, #fff7f7 100%);
        border: 1px solid #fecaca;
        box-shadow: 0 3px 12px rgba(220, 53, 69, .045);
        padding: 16px;
        margin-bottom: 16px;
        position: relative;
        overflow: hidden;
    }

    #stockOutRoot .loss-preview-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: #dc3545;
    }

    #stockOutRoot .preview-heading {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12.5px;
        font-weight: 800;
        color: #b42332;
        margin-bottom: 14px;
    }

    #stockOutRoot .preview-heading-icon {
        width: 29px;
        height: 29px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fee2e2;
        border: 1px solid #fecaca;
        border-radius: 5px;
        font-size: 12px;
    }

    #stockOutRoot .preview-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        padding: 9px 0;
        border-bottom: 1px solid #f1dede;
        font-size: 11px;
    }

    #stockOutRoot .preview-row:last-of-type {
        border-bottom: 0;
    }

    #stockOutRoot .preview-label {
        color: #64748b;
        flex-shrink: 0;
    }

    #stockOutRoot .preview-value {
        color: #1e293b;
        font-weight: 750;
        text-align: right;
        min-width: 0;
        overflow-wrap: anywhere;
    }

    #stockOutRoot .preview-remaining {
        background: #fff;
        border: 1px solid #e2e8f0;
        padding: 10px;
        margin: 10px 0 4px;
        border-radius: 5px;
    }

    #stockOutRoot .preview-remaining .preview-value {
        color: #2563eb;
        font-size: 14px;
    }

    #stockOutRoot .loss-total-box {
        background: rgba(220, 53, 69, .075);
        border: 1px solid rgba(220, 53, 69, .20);
        border-radius: 5px;
        padding: 12px;
        margin-top: 12px;
    }

    #stockOutRoot .loss-total-label {
        font-size: 10.5px;
        font-weight: 750;
        color: #b42332;
    }

    #stockOutRoot .loss-total-value {
        font-size: 19px;
        font-weight: 850;
        color: #dc3545;
        white-space: nowrap;
    }

    /* Media Evidence */
    #stockOutRoot .proof-media-surface {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        padding: 13px;
        transition: all .2s ease;
    }

    #stockOutRoot .proof-media-surface:hover {
        border-color: #ef4444;
        background: #fffafa;
    }

    #stockOutRoot .proof-empty-icon-box {
        width: 40px;
        height: 40px;
        background: #e2e8f0;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        border-radius: 5px;
    }

    #stockOutRoot .proof-thumb-box {
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

    #stockOutRoot .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #stockOutRoot .proof-thumb-hover-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity .15s ease;
        border-radius: 5px;
    }

    #stockOutRoot .proof-thumb-box:hover .proof-thumb-hover-overlay {
        opacity: 1;
    }

    #stockOutRoot .proof-preview-card {
        background: #fff;
        border: 1px solid #dbe3ed;
        padding: 9px;
        border-radius: 5px;
    }

    /* Submit Area */
    #stockOutRoot .form-submit-area {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
        border-top: 1px solid #edf0f4;
        padding-top: 14px;
        margin-top: 6px;
    }

    #stockOutRoot #btnSubmitStockOut:disabled {
        opacity: .5;
        cursor: not-allowed;
        transform: none;
    }

    /* Accounting Note */
    #stockOutRoot .policy-card {
        border: 1px solid #dbeafe;
        background: #f8fbff;
        padding: 15px;
    }

    #stockOutRoot .policy-icon {
        width: 29px;
        height: 29px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #dbeafe;
        color: #2563eb;
        border-radius: 5px;
        flex-shrink: 0;
    }

    #stockOutRoot .policy-text {
        color: #64748b;
        font-size: 10.5px;
        line-height: 1.65;
    }

    /* Alerts */
    #stockOutRoot .alert {
        border: 1px solid transparent;
        font-size: 11.5px;
    }

    /* Modal */
    .stock-out-modal-scope {
        z-index: 106000;
    }

    .stock-out-modal-scope .modal-content {
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }

    .stock-out-modal-scope .modal-header {
        padding: 13px 15px;
    }

    .stock-out-modal-scope .modal-body {
        padding: 14px;
    }

    .stock-out-modal-scope .modal-dialog {
        max-width: min(500px, calc(100% - 24px));
    }

    .stock-out-modal-scope .modal-dialog.modal-sm {
        max-width: 360px;
    }

    #stockOutNoticeIconWrap {
        width: 44px;
        height: 44px;
        border-radius: 5px !important;
    }

    /* Responsive */
    @media (min-width: 992px) {
        #stockOutRoot .sticky-preview-column {
            position: sticky;
            top: 16px;
            align-self: flex-start;
        }
    }

    @media (max-width: 991.98px) {
        #stockOutRoot .loss-preview-card {
            margin-top: 0;
        }
    }

    @media (max-width: 767.98px) {
        #stockOutRoot {
            padding-top: 12px !important;
        }

        #stockOutRoot .stock-out-header {
            align-items: flex-start !important;
            padding-bottom: 12px;
        }

        #stockOutRoot .page-heading {
            font-size: 15px;
        }

        #stockOutRoot .page-title-icon {
            width: 32px;
            height: 32px;
            min-width: 32px;
            font-size: 13px;
        }

        #stockOutRoot .page-subtitle {
            font-size: 10.5px;
        }

        #stockOutRoot .header-actions-wrap {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 7px;
        }

        #stockOutRoot .header-actions-wrap .btn {
            width: 100%;
            min-height: 36px;
            padding: 7px 5px !important;
            font-size: 10px !important;
            white-space: normal;
        }

        #stockOutRoot .card {
            padding: 12px;
            margin-bottom: 12px;
        }

        #stockOutRoot .loss-preview-card {
            padding: 13px;
            margin-bottom: 12px;
        }

        #stockOutRoot .section-heading {
            font-size: 11.5px;
            margin-bottom: 12px;
        }

        #stockOutRoot .selected-product-panel {
            padding: 10px;
        }

        #stockOutRoot .selected-product-image {
            width: 48px;
            height: 48px;
        }

        #stockOutRoot .form-submit-area {
            display: grid;
            grid-template-columns: 1fr 2fr;
        }

        #stockOutRoot .form-submit-area .btn {
            width: 100%;
            min-height: 36px;
        }
    }

    @media (max-width: 420px) {
        #stockOutRoot .product-info-badges .badge {
            font-size: 8.5px;
        }

        #stockOutRoot .variant-pill-select {
            width: 100%;
        }

        #stockOutRoot .loss-total-value {
            font-size: 16px;
        }

        #stockOutRoot .proof-media-surface {
            padding: 9px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #stockOutRoot *,
        .stock-out-modal-scope * {
            transition: none !important;
            animation: none !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="stockOutRoot">

    <!-- ===================== PAGE HEADER ===================== -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3 stock-out-header">
        <div class="d-flex align-items-start gap-2">
            <span class="page-title-icon">
                <i class="fas fa-arrow-up-from-bracket"></i>
            </span>

            <div class="min-w-0">
                <h5 class="page-heading mb-0">
                    Non-Sale Stock Out / Write-Off &amp; Return
                </h5>
                <p class="page-subtitle mb-0">
                    Remove damaged, expired, lost, internal use, or supplier return inventory without recording fake sales revenue.
                </p>
            </div>
        </div>

        <div class="header-actions-wrap">
            <a href="{{ route('admin.inventory.balance') }}"
               class="btn btn-sm btn-outline-secondary btn-compact shadow-sm">
                <i class="fas fa-scale-balanced"></i>
                <span>View Stock Balance</span>
            </a>

            <a href="{{ route('admin.inventory.movements') }}"
               class="btn btn-sm btn-outline-primary btn-compact shadow-sm">
                <i class="fas fa-clipboard-list"></i>
                <span>Inventory Ledger</span>
            </a>
        </div>
    </div>

    <!-- ===================== SESSION MESSAGES ===================== -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 shadow-sm mb-3 d-flex align-items-center justify-content-between"
             role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle text-success fs-5"></i>
                <span class="fw-semibold text-dark">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 shadow-sm mb-3"
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

    <!-- ===================== MAIN CONTENT ===================== -->
    <div class="row g-3">

        <!-- LEFT: PRODUCT SELECTION + STOCK OUT FORM -->
        <div class="col-12 col-lg-8">

            <!-- PRODUCT SELECTOR -->
            <div class="card">

                <div class="section-heading">
                    <span class="section-number">01</span>
                    <span class="section-heading-icon">
                        <i class="fas fa-search"></i>
                    </span>
                    <span>Select Product / Variant to Deduct</span>
                </div>

                <!-- Live AJAX Search -->
                <div class="position-relative mb-3">
                    <label class="form-label" for="liveSearchInput">
                        Search by Product Title, ID, SKU, Barcode, Color, or Size
                        <span class="required-mark">*</span>
                    </label>

                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-barcode"></i>
                        </span>

                        <input type="text"
                               id="liveSearchInput"
                               class="form-control"
                               placeholder="Scan barcode or type SKU, product name..."
                               autocomplete="off"
                               aria-label="Search inventory products"
                               aria-controls="searchResultsPanel">

                        <button type="button"
                                class="btn btn-light border"
                                id="btnClearSearch"
                                onclick="clearProductSelection()"
                                style="display:none;"
                                aria-label="Clear product selection">
                            <i class="fas fa-times text-muted"></i>
                        </button>
                    </div>

                    <small class="form-hint">
                        <i class="fas fa-lightbulb me-1"></i>
                        Search results will appear automatically. Select a product to continue.
                    </small>

                    <!-- AJAX Search Results -->
                    <div id="searchResultsPanel"
                         class="search-result-panel d-none">
                        <div id="searchResultsList"></div>
                    </div>
                </div>

                <!-- Selected Product Overview -->
                <div id="selectedProductCard"
                     class="selected-product-panel d-none">

                    <div class="d-flex align-items-start gap-3">
                        <img id="selProdImg"
                             src="https://placehold.co/60x60?text=Item"
                             class="selected-product-image"
                             alt="Selected product thumbnail">

                        <div class="flex-grow-1 min-w-0">

                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div class="min-w-0">
                                    <h6 class="selected-product-title mb-1 text-truncate"
                                        id="selProdTitle">
                                        Product Title
                                    </h6>

                                    <div class="selected-product-meta font-monospace">
                                        ID:
                                        <span id="selProdId" class="text-secondary">#0</span>
                                        <span class="mx-1">|</span>
                                        SKU:
                                        <span id="selProdSku" class="text-dark">—</span>
                                    </div>
                                </div>

                                <button type="button"
                                        class="btn btn-sm btn-outline-danger btn-compact flex-shrink-0"
                                        onclick="clearProductSelection()">
                                    <i class="fas fa-rotate-left"></i>
                                    Change
                                </button>
                            </div>

                            <!-- Variant Selector -->
                            <div id="variantPickerWrapper" class="mt-3 pt-2 border-top d-none">
                                <label class="form-label text-danger mb-2">
                                    <i class="fas fa-layer-group me-1"></i>
                                    Select Variant to Deduct
                                    <span class="required-mark">*</span>
                                </label>

                                <div class="d-flex flex-wrap gap-2"
                                     id="variantPillContainer"></div>
                            </div>

                            <!-- Selected Product Details -->
                            <div class="product-info-badges"
                                 id="activeTargetBadgeContainer">

                                <span class="badge bg-light text-dark border px-2 py-1"
                                      id="badgeTargetStruct">
                                    Simple Product
                                </span>

                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 font-monospace"
                                      id="badgeCurrentStock">
                                    Available Stock: 0
                                </span>

                                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 font-monospace"
                                      id="badgeCurrentCost">
                                    Unit Valuation: ৳0.00
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STOCK OUT FORM -->
            <div class="card">

                <div class="section-heading">
                    <span class="section-number">02</span>
                    <span class="section-heading-icon">
                        <i class="fas fa-file-circle-xmark"></i>
                    </span>
                    <span>Removal Reason &amp; Quantity</span>
                </div>

                <form id="stockOutForm"
                      action="{{ route('admin.inventory.stock_out.store') }}"
                      method="POST">

                    @csrf

                    <input type="hidden" name="product_id" id="form_product_id" required>
                    <input type="hidden" name="variant_id" id="form_variant_id">
                    <input type="hidden" name="damage_proof_attachment" id="form_damage_proof_attachment" value="">

                    <!-- ROW 1: REASON / WAREHOUSE / BRANCH -->
                    <div class="row g-3 mb-3">

                        <div class="col-12 col-md-4">
                            <label class="form-label" for="form_movement_type">
                                Stock Out Reason <span class="required-mark">*</span>
                            </label>

                            <select name="movement_type"
                                    id="form_movement_type"
                                    class="form-select font-monospace"
                                    required>
                                <option value="damage_out" selected>Damaged / Broken Goods</option>
                                <option value="expired_out">Expired / Rotten Stock</option>
                                <option value="supplier_return">Return Back to Supplier</option>
                                <option value="internal_use">Internal Company Use / Display</option>
                                <option value="lost_stock">Lost / Missing / Stolen Goods</option>
                                <option value="adjustment_out">Audit Reconciliation Discrepancy</option>
                                <option value="other_removal">Other Approved Removal</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label" for="form_warehouse_id">
                                Source Warehouse
                            </label>

                            <select name="warehouse_id"
                                    id="form_warehouse_id"
                                    class="form-select font-monospace">
                                <option value="">Default Warehouse</option>

                                @foreach($warehouses ?? [] as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label" for="form_branch_id">
                                Operating Branch
                            </label>

                            <select name="branch_id"
                                    id="form_branch_id"
                                    class="form-select font-monospace">
                                <option value="">HQ Branch</option>

                                @foreach($branches ?? [] as $br)
                                    <option value="{{ $br->id }}">{{ $br->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- ROW 2: REFERENCE / BATCH / DATE -->
                    <div class="row g-3 mb-3">

                        <div class="col-12 col-md-4">
                            <label class="form-label" for="form_reference_id">
                                Reference / Memo #
                                <span class="required-mark">*</span>
                            </label>

                            <input type="text"
                                   name="reference_id"
                                   id="form_reference_id"
                                   class="form-control font-monospace bn-digit"
                                   value="{{ $defaultOutRef }}"
                                   required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label" for="form_batch_no">
                                Batch / Lot # (Optional)
                            </label>

                            <input type="text"
                                   name="batch_no"
                                   id="form_batch_no"
                                   class="form-control font-monospace"
                                   placeholder="BATCH-202611-A">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label" for="form_stock_out_date">
                                Deduction Date
                            </label>

                            <input type="date"
                                   name="stock_out_date"
                                   id="form_stock_out_date"
                                   class="form-control font-monospace bn-digit"
                                   value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <!-- ROW 3: QUANTITY -->
                    <div class="mb-3">

                        <label class="form-label" for="form_quantity">
                            Quantity to Deduct
                            <span class="required-mark">*</span>
                        </label>

                        <div class="input-group">
                            <input type="number"
                                   name="quantity"
                                   id="form_quantity"
                                   class="form-control font-monospace bn-digit fw-bold text-danger"
                                   min="1"
                                   placeholder="Enter quantity"
                                   required
                                   oninput="recalculateOutPreview()">

                            <span class="input-group-text font-monospace">
                                Units
                            </span>
                        </div>

                        <small class="form-hint">
                            Enter the number of units to remove from the selected product or variant.
                        </small>

                        <small class="text-danger d-none mt-2 fw-semibold"
                               id="errOverStock"
                               style="font-size:11px;">
                            <i class="fas fa-circle-exclamation me-1"></i>
                            Cannot remove more than available stock!
                        </small>
                    </div>

                    <!-- DAMAGE / DISPOSAL PROOF -->
                    <div class="mb-3">

                        <label class="form-label d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                            <span>
                                <i class="fas fa-paperclip text-primary me-1"></i>
                                Disposal Proof / Damage Evidence
                            </span>

                            <span class="badge bg-light text-muted border font-monospace">
                                MEDIA LIBRARY
                            </span>
                        </label>

                        <div class="proof-media-surface">

                            <!-- Empty State -->
                            <div id="noProofAttachedNotice" class="text-center py-2">

                                <div class="proof-empty-icon-box mx-auto mb-2">
                                    <i class="fas fa-cloud-arrow-up"></i>
                                </div>

                                <h6 class="fw-bold text-dark mb-1"
                                    style="font-size:12px;">
                                    No Evidence Attached
                                </h6>

                                <p class="text-muted small mb-3"
                                   style="font-size:10.5px;">
                                    Attach broken item photo, expiration label, disposal slip, or return memo.
                                </p>

                                <button type="button"
                                        class="btn btn-primary btn-compact shadow-sm"
                                        onclick="chooseProofMedia()">
                                    <i class="fas fa-photo-film"></i>
                                    Choose from Media Library
                                </button>
                            </div>

                            <!-- Attached Preview -->
                            <div id="proofPreviewContainer"
                                 class="proof-preview-card d-none">

                                <div class="d-flex align-items-center gap-3 mb-2">

                                    <div class="proof-thumb-box"
                                         onclick="zoomProofMedia()"
                                         title="Click to Zoom Image"
                                         role="button"
                                         tabindex="0"
                                         aria-label="Zoom attached proof image"
                                         onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();zoomProofMedia();}">

                                        <img id="proofPreviewImg"
                                             src="https://placehold.co/120x120?text=Proof"
                                             alt="Proof"
                                             onerror="this.src='https://placehold.co/120x120?text=Proof';">

                                        <div class="proof-thumb-hover-overlay">
                                            <i class="fas fa-search-plus text-white"></i>
                                        </div>
                                    </div>

                                    <div class="min-w-0 flex-grow-1">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle mb-1"
                                              style="font-size:9px;">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Proof Attached
                                        </span>

                                        <strong class="d-block text-dark text-truncate font-monospace"
                                                id="proofPathText"
                                                style="font-size:10.5px;">
                                            evidence_doc.jpg
                                        </strong>

                                        <small class="text-muted" style="font-size:9px;">
                                            Evidence file selected successfully
                                        </small>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap align-items-center gap-2 border-top pt-2">

                                    <button type="button"
                                            class="btn btn-outline-secondary btn-compact flex-fill"
                                            onclick="zoomProofMedia()">
                                        <i class="fas fa-expand"></i>
                                        Zoom
                                    </button>

                                    <button type="button"
                                            class="btn btn-outline-primary btn-compact flex-fill"
                                            onclick="chooseProofMedia()">
                                        <i class="fas fa-arrows-rotate"></i>
                                        Change
                                    </button>

                                    <button type="button"
                                            class="btn btn-outline-danger btn-compact flex-fill"
                                            onclick="clearProofMedia()">
                                        <i class="fas fa-trash-alt"></i>
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- NOTES -->
                    <div class="mb-3">
                        <label class="form-label" for="form_notes">
                            Explanation / Disposal Notes
                            <span class="required-mark">*</span>
                        </label>

                        <textarea name="notes"
                                  id="form_notes"
                                  class="form-control"
                                  rows="3"
                                  placeholder="Reason for write-off, disposal approval note, damage description..."
                                  required></textarea>

                        <small class="form-hint">
                            Provide a clear explanation for audit and inventory record purposes.
                        </small>
                    </div>

                    <!-- SUBMIT -->
                    <div class="form-submit-area">

                        <button type="button"
                                class="btn btn-sm btn-light border btn-compact"
                                onclick="clearProductSelection()">
                            <i class="fas fa-rotate-left"></i>
                            Reset Selection
                        </button>

                        <button type="submit"
                                class="btn btn-sm btn-danger fw-bold px-4 shadow-sm btn-compact"
                                id="btnSubmitStockOut"
                                disabled>
                            <i class="fas fa-trash-can"></i>
                            Confirm &amp; Deduct Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- RIGHT: LOSS PREVIEW & ACCOUNTING POLICY -->
        <div class="col-12 col-lg-4 sticky-preview-column">

            <!-- LOSS PREVIEW -->
            <div class="loss-preview-card">

                <div class="preview-heading">
                    <span class="preview-heading-icon">
                        <i class="fas fa-chart-line"></i>
                    </span>
                    <span>Stock Removal Impact Preview</span>
                </div>

                <div class="preview-row">
                    <span class="preview-label">Target Item:</span>
                    <strong class="preview-value text-truncate"
                            style="max-width:170px;"
                            id="calcTargetLabel">
                        None Selected
                    </strong>
                </div>

                <div class="preview-row">
                    <span class="preview-label">Current Available:</span>
                    <span class="preview-value font-monospace bn-digit"
                          id="calcCurStock">
                        0 Units
                    </span>
                </div>

                <div class="preview-row">
                    <span class="preview-label">Removal Quantity:</span>
                    <span class="preview-value text-danger font-monospace bn-digit"
                          id="calcOutboundQty">
                        -0 Units
                    </span>
                </div>

                <div class="preview-remaining">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <span class="fw-bold text-dark"
                              style="font-size:11px;">
                            Remaining Stock
                        </span>

                        <span class="preview-value font-monospace bn-digit"
                              id="calcRemainingStock">
                            0 Units
                        </span>
                    </div>
                </div>

                <div class="preview-row">
                    <span class="preview-label">Item Unit Valuation:</span>
                    <span class="preview-value font-monospace bn-digit"
                          id="calcUnitCost">
                        ৳0.00
                    </span>
                </div>

                <div class="loss-total-box">
                    <div class="d-flex flex-column gap-2">
                        <span class="loss-total-label">
                            <i class="fas fa-triangle-exclamation me-1"></i>
                            Inventory Loss / Write-Off
                        </span>

                        <strong class="loss-total-value font-monospace bn-digit"
                                id="calcTotalLossVal">
                            ৳ 0.00
                        </strong>
                    </div>
                </div>

                <div class="mt-3 d-flex align-items-start gap-2 text-muted"
                     style="font-size:9.5px;line-height:1.5;">
                    <i class="fas fa-circle-info text-danger mt-1"></i>
                    <span>
                        This is an estimated inventory valuation impact based on the selected item's purchase cost.
                    </span>
                </div>
            </div>

            <!-- ACCOUNTING POLICY -->
            <div class="card policy-card">

                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="policy-icon">
                        <i class="fas fa-shield-halved"></i>
                    </span>

                    <div>
                        <h6 class="fw-bold text-dark mb-0"
                            style="font-size:12px;">
                            Accounting Policy Note
                        </h6>
                        <small class="text-muted" style="font-size:9px;">
                            Important inventory control guidelines
                        </small>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-2 mb-2">
                    <i class="fas fa-check-circle text-primary mt-1"></i>
                    <p class="policy-text mb-0">
                        Sales orders should always be processed via
                        <strong class="text-dark">Checkout or POS Terminal</strong>.
                    </p>
                </div>

                <div class="d-flex align-items-start gap-2">
                    <i class="fas fa-check-circle text-primary mt-1"></i>
                    <p class="policy-text mb-0">
                        Stock write-off directly updates real-time warehouse inventory and records permanent ledger logs.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===================== MODAL 1: PROOF IMAGE ZOOM ===================== -->
<div class="modal fade stock-out-modal-scope"
     id="proofZoomModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index:106000;">

    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content shadow-lg text-center p-2">

            <div class="modal-header border-0 pb-0">
                <span class="fw-bold text-dark"
                      style="font-size:12.5px;">
                    <i class="fas fa-image text-danger me-1"></i>
                    Disposal Proof / Damage Evidence
                </span>

                <button type="button"
                        class="btn-close btn-sm shadow-none"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <div class="modal-body p-2">
                <img id="modalZoomProofImgTag"
                     src=""
                     alt="Proof Image"
                     class="img-fluid border"
                     style="max-height:480px;object-fit:contain;border-radius:5px;">
            </div>
        </div>
    </div>
</div>

<!-- ===================== MODAL 2: NOTICE DIALOG ===================== -->
<div class="modal fade stock-out-modal-scope"
     id="stockOutNoticeModal"
     tabindex="-1"
     aria-hidden="true"
     style="z-index:106500;">

    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg text-center p-3">

            <div class="modal-body p-1">

                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-3"
                     id="stockOutNoticeIconWrap">
                    <i class="fas fa-circle-info fs-4"
                       id="stockOutNoticeIcon"></i>
                </div>

                <h6 class="fw-bold text-dark mb-2"
                    id="stockOutNoticeTitle"
                    style="font-size:14px;">
                    Notification
                </h6>

                <p class="text-muted small mb-3"
                   id="stockOutNoticeMsg"
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

<script>
    let currentSelectedProduct = null;
    let currentSelectedVariant = null;
    let searchDebounceTimer = null;

    /* =========================================================
       NOTICE / FEEDBACK MODAL
    ========================================================= */

    function showStockOutNotice(message, title = 'Notification', type = 'info') {
        const titleEl  = document.getElementById('stockOutNoticeTitle');
        const msgEl    = document.getElementById('stockOutNoticeMsg');
        const iconWrap = document.getElementById('stockOutNoticeIconWrap');
        const iconEl   = document.getElementById('stockOutNoticeIcon');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;

        if (iconWrap && iconEl) {
            if (type === 'error' || type === 'danger') {
                iconWrap.className = 'rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-3';
                iconEl.className = 'fas fa-circle-exclamation fs-4';
            } else if (type === 'success') {
                iconWrap.className = 'rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-3';
                iconEl.className = 'fas fa-circle-check fs-4';
            } else {
                iconWrap.className = 'rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-3';
                iconEl.className = 'fas fa-circle-info fs-4';
            }
        }

        const modalEl = document.getElementById('stockOutNoticeModal');

        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    /* =========================================================
       MEDIA ITEM EXTRACTION
    ========================================================= */

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

    /* =========================================================
       CHOOSE DISPOSAL PROOF FROM MEDIA LIBRARY
    ========================================================= */

    function chooseProofMedia() {
        if (typeof window.openMediaPicker === 'function') {

            window.openMediaPicker({
                multiple: false,

                onSelect: function(media) {
                    const picked = extractMediaItem(media);

                    if (picked && picked.path) {
                        const inputHidden = document.getElementById('form_damage_proof_attachment');
                        const previewImg  = document.getElementById('proofPreviewImg');
                        const pathText    = document.getElementById('proofPathText');
                        const container   = document.getElementById('proofPreviewContainer');
                        const emptyNotice = document.getElementById('noProofAttachedNotice');

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

                        showStockOutNotice(
                            'Evidence attachment added from Global Media Library.',
                            'Attached',
                            'success'
                        );
                    }
                }
            });

        } else {
            showStockOutNotice(
                'Global Media Library is loading, please try again.',
                'Notice'
            );
        }
    }

    /* =========================================================
       REMOVE ATTACHED PROOF
    ========================================================= */

    function clearProofMedia() {
        const inputHidden = document.getElementById('form_damage_proof_attachment');
        const container   = document.getElementById('proofPreviewContainer');
        const emptyNotice = document.getElementById('noProofAttachedNotice');

        if (inputHidden) inputHidden.value = '';

        if (container) {
            container.classList.add('d-none');
            container.classList.remove('d-block');
        }

        if (emptyNotice) {
            emptyNotice.classList.remove('d-none');
            emptyNotice.classList.add('d-block');
        }

        showStockOutNotice(
            'Evidence attachment cleared.',
            'Cleared',
            'info'
        );
    }

    /* =========================================================
       ZOOM PROOF IMAGE
    ========================================================= */

    function zoomProofMedia() {
        const previewImg = document.getElementById('proofPreviewImg');
        const zoomImgTag = document.getElementById('modalZoomProofImgTag');
        const modalEl = document.getElementById('proofZoomModal');

        if (previewImg && zoomImgTag && previewImg.src && modalEl && window.bootstrap) {
            zoomImgTag.src = previewImg.src;
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    /* =========================================================
       LIVE AJAX PRODUCT SEARCH
    ========================================================= */

    const liveSearchInput = document.getElementById('liveSearchInput');

    if (liveSearchInput) {
        liveSearchInput.addEventListener('input', function() {

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
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Product search request failed.');
                    }

                    return res.json();
                })
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

                            const variantCount = Array.isArray(p.variants)
                                ? p.variants.length
                                : 0;

                            const structBadge = isVar
                                ? `<span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:9px;">Variable (${variantCount} Variants)</span>`
                                : `<span class="badge bg-light text-dark border" style="font-size:9px;">Simple Product</span>`;

                            const itemHtml = `
                                <div class="search-result-item d-flex align-items-center justify-content-between"
                                     onclick="handleProductSelect(${escapeJson(p)})"
                                     role="button"
                                     tabindex="0"
                                     onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();handleProductSelect(${escapeJson(p)});}">

                                    <div class="d-flex align-items-center gap-2 overflow-hidden">

                                        <img src="${escapeHtml(p.thumbnail || 'https://placehold.co/60x60?text=Item')}"
                                             alt="Product thumbnail"
                                             onerror="this.src='https://placehold.co/60x60?text=Item';">

                                        <div class="overflow-hidden">
                                            <span class="search-result-title d-block text-truncate">
                                                ${escapeHtml(p.title)}
                                            </span>

                                            <span class="search-result-meta font-monospace">
                                                ID: #${escapeHtml(String(p.id))}
                                                | SKU: ${escapeHtml(String(p.sku ?? '—'))}
                                                | Stock: ${escapeHtml(String(p.current_stock ?? 0))}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="text-end flex-shrink-0 ms-2">
                                        ${structBadge}

                                        <span class="d-block font-monospace fw-bold text-danger small mt-1">
                                            ৳${Number(p.purchase_cost || 0).toFixed(2)}
                                        </span>
                                    </div>
                                </div>
                            `;

                            list.insertAdjacentHTML('beforeend', itemHtml);
                        });
                    }

                    panel.classList.remove('d-none');

                })
                .catch(err => {
                    console.error('Search error:', err);
                });

            }, 220);
        });
    }

    /* =========================================================
       CLOSE SEARCH RESULTS ON OUTSIDE CLICK
    ========================================================= */

    document.addEventListener('click', function(e) {

        const input = document.getElementById('liveSearchInput');
        const panel = document.getElementById('searchResultsPanel');

        if (
            input &&
            panel &&
            !input.contains(e.target) &&
            !panel.contains(e.target)
        ) {
            panel.classList.add('d-none');
        }
    });

    /* =========================================================
       SELECT PRODUCT
    ========================================================= */

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

            document.getElementById('btnSubmitStockOut').disabled = true;

            prod.variants.forEach(v => {

                const pill = document.createElement('button');

                pill.type = 'button';
                pill.className = 'variant-pill-select font-monospace bn-digit small';

                pill.innerHTML = `
                    <span>
                        <strong>${escapeHtml(v.title)}</strong>
                        <span class="d-block mt-1" style="font-size:9px;">
                            SKU: ${escapeHtml(String(v.sku ?? '—'))}
                            | Stock: ${escapeHtml(String(v.stock ?? 0))}
                        </span>
                    </span>
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

            document.getElementById('badgeTargetStruct').innerText =
                'Select a Variant Above';

            document.getElementById('badgeCurrentStock').innerText =
                'Available Stock: —';

            document.getElementById('badgeCurrentCost').innerText =
                'Unit Valuation: —';

            document.getElementById('calcTargetLabel').innerText =
                `${prod.title} (Select Variant)`;

            recalculateOutPreview();

        } else {

            variantWrapper.classList.add('d-none');

            document.getElementById('badgeTargetStruct').className =
                'badge bg-light text-dark border';

            document.getElementById('badgeTargetStruct').innerText =
                'Simple Product';

            document.getElementById('badgeCurrentStock').innerText =
                `Available Stock: ${prod.current_stock}`;

            document.getElementById('badgeCurrentCost').innerText =
                `Unit Valuation: ৳${Number(prod.purchase_cost).toFixed(2)}`;

            recalculateOutPreview();
        }
    }

    /* =========================================================
       SELECT PRODUCT VARIANT
    ========================================================= */

    function handleVariantSelect(variant) {

        currentSelectedVariant = variant;

        document.getElementById('form_variant_id').value = variant.id;

        document.getElementById('selProdSku').innerText = variant.sku;

        document.getElementById('badgeTargetStruct').className =
            'badge bg-primary-subtle text-primary border border-primary-subtle';

        document.getElementById('badgeTargetStruct').innerText =
            `Variant: ${variant.title}`;

        document.getElementById('badgeCurrentStock').innerText =
            `Available Stock: ${variant.stock}`;

        document.getElementById('badgeCurrentCost').innerText =
            `Unit Valuation: ৳${Number(variant.purchase_cost).toFixed(2)}`;

        recalculateOutPreview();
    }

    /* =========================================================
       RESET PRODUCT SELECTION
    ========================================================= */

    function clearProductSelection() {

        currentSelectedProduct = null;
        currentSelectedVariant = null;

        document.getElementById('liveSearchInput').value = '';

        document.getElementById('btnClearSearch').style.display = 'none';

        document.getElementById('selectedProductCard').classList.add('d-none');

        document.getElementById('form_product_id').value = '';
        document.getElementById('form_variant_id').value = '';
        document.getElementById('form_quantity').value = '';

        document.getElementById('errOverStock').classList.add('d-none');

        document.getElementById('btnSubmitStockOut').disabled = true;

        document.getElementById('variantPickerWrapper').classList.add('d-none');

        document.getElementById('variantPillContainer').innerHTML = '';

        recalculateOutPreview();
    }

    /* =========================================================
       REAL-TIME STOCK & LOSS CALCULATION
    ========================================================= */

    function recalculateOutPreview() {

        const outQty = parseInt(document.getElementById('form_quantity').value) || 0;

        let curStock = 0;
        let curCost = 0;
        let targetName = 'None Selected';

        if (currentSelectedVariant) {

            curStock = Number(currentSelectedVariant.stock) || 0;
            curCost = Number(currentSelectedVariant.purchase_cost) || 0;

            targetName =
                `${currentSelectedProduct.title} (${currentSelectedVariant.title})`;

        } else if (currentSelectedProduct) {

            curStock = Number(currentSelectedProduct.current_stock) || 0;
            curCost = Number(currentSelectedProduct.purchase_cost) || 0;

            targetName = currentSelectedProduct.title;
        }

        const remainingStock = curStock - outQty;
        const totalLossVal = outQty * curCost;

        const isOverStock = outQty > curStock;

        const hasSelection =
            (currentSelectedVariant !== null) ||
            (currentSelectedProduct && currentSelectedProduct.product_struct !== 'variable');

        if (isOverStock && outQty > 0) {

            document.getElementById('errOverStock').classList.remove('d-none');

            document.getElementById('btnSubmitStockOut').disabled = true;

            document.getElementById('calcRemainingStock').className =
                'preview-value font-monospace bn-digit text-danger';

        } else {

            document.getElementById('errOverStock').classList.add('d-none');

            document.getElementById('btnSubmitStockOut').disabled =
                !(hasSelection && outQty > 0);

            document.getElementById('calcRemainingStock').className =
                'preview-value font-monospace bn-digit';
        }

        document.getElementById('calcTargetLabel').innerText = targetName;

        document.getElementById('calcCurStock').innerText =
            `${curStock} Units`;

        document.getElementById('calcOutboundQty').innerText =
            `-${outQty} Units`;

        document.getElementById('calcRemainingStock').innerText =
            `${remainingStock >= 0 ? remainingStock : 0} Units`;

        document.getElementById('calcUnitCost').innerText =
            `৳${curCost.toFixed(2)}`;

        document.getElementById('calcTotalLossVal').innerText =
            `৳ ${totalLossVal.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })}`;
    }

    /* =========================================================
       HTML / JSON ESCAPING HELPERS
    ========================================================= */

    function escapeHtml(str) {

        if (str === null || str === undefined) return '';

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

    function escapeJson(obj) {

        return JSON.stringify(obj).replace(/"/g, '&quot;');
    }
</script>
@endsection
