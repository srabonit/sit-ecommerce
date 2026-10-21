@extends('layouts.admin')

@section('title', 'Edit Product - ' . config('app.name'))

@php
    $resolveAdminProductImg = function($path, $fallback = 'https://placehold.co/80x80?text=No+Img') {
        if (empty($path) || $path === 'undefined' || $path === 'null') {
            return $fallback;
        }
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) {
            return show_image($path, $fallback);
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        $clean = ltrim(str_replace(['public_html/', 'public/'], '', $path), '/');
        if (str_starts_with($clean, 'uploads/') || str_starts_with($clean, 'storage/')) {
            return asset($clean);
        }
        return asset('uploads/' . $clean);
    };

    $isVariableProduct = ($product->product_struct === 'variable') || ($variants && $variants->count() > 0);
@endphp

@section('admin_content')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

<style>
    /* Global Strict 5px Element Radius */
    #productEditRoot .btn,
    #productEditRoot .btn-sm,
    #productEditRoot .btn-xs,
    #productEditRoot button:not(.remove-thumb-btn):not(.remove-btn):not(.remove-tag-btn):not(.note-btn),
    #productEditRoot .form-control,
    #productEditRoot .form-select,
    #productEditRoot .badge,
    #productEditRoot .input-group-text,
    #productEditRoot .alert,
    #productEditNoticeModal .btn,
    #productEditNoticeModal .modal-content {
        border-radius: 5px !important;
    }

    #productEditRoot .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #productEditRoot .input-group > .form-control,
    #productEditRoot .input-group > .form-select {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Compact & Sleek Button Sizing */
    #productEditRoot .btn-compact,
    #productEditNoticeModal .btn-compact {
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

    /* Enterprise Surface Cards */
    .product-form-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    .form-section-title {
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .form-section-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e2e8f0;
        margin-left: 8px;
    }

    /* Form Controls */
    #productEditRoot .form-label {
        font-size: 11.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 4px;
    }
    #productEditRoot .form-control,
    #productEditRoot .form-select {
        font-size: 12.5px;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #productEditRoot .form-control:focus,
    #productEditRoot .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Image Upload Box */
    .img-upload-box {
        border: 2px dashed #cbd5e1;
        border-radius: 6px !important;
        padding: 14px;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .img-upload-box:hover {
        border-color: #2563eb;
        background: #eff6ff;
    }
    .preview-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }
    .preview-item {
        position: relative;
        width: 68px;
        height: 68px;
        border-radius: 5px !important;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: #ffffff;
    }
    .preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .preview-item .remove-btn {
        position: absolute;
        top: 2px;
        right: 2px;
        background: rgba(220, 38, 38, 0.9);
        color: white;
        border: none;
        border-radius: 3px !important;
        width: 18px;
        height: 18px;
        font-size: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 5;
    }
    .thumb-preview-box {
        position: relative;
        display: inline-block;
    }
    .thumb-preview-box .remove-thumb-btn {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #dc2626;
        color: #fff;
        border: 2px solid #fff;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 1px 3px rgba(0,0,0,0.25);
        z-index: 5;
    }

    /* ================= 100% BULLETPROOF VARIANT TABLE STYLING ================= */
    .variant-table-container {
        overflow-x: auto !important;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px !important;
        background: #ffffff;
    }
    .variant-table-container::-webkit-scrollbar {
        height: 6px;
    }
    .variant-table-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
    .variant-table {
        min-width: 1160px !important; /* Prevents any input crushing on PC and Mobile */
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }
    .variant-table th {
        background: #f8fafc;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        white-space: nowrap;
        padding: 9px 10px;
        border-bottom: 1.5px solid #e2e8f0;
    }
    .variant-table td {
        padding: 6px 8px;
        vertical-align: middle;
        font-size: 12px;
        border-bottom: 1px solid #f1f5f9;
        background: #ffffff;
    }
    .variant-table tbody tr:hover td {
        background-color: #fafbfc;
    }
    .variant-table .form-control {
        font-size: 12px !important;
        height: 32px !important;
        padding: 4px 8px !important;
        border: 1px solid #cbd5e1 !important;
        width: 100% !important;
        box-shadow: none !important;
    }
    .variant-table .form-control:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15) !important;
    }

    /* WooCommerce Style Meta Box Panels */
    .wc-metabox {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 6px !important;
        margin-bottom: 16px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
    }
    .wc-metabox-header {
        padding: 10px 14px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 700;
        font-size: 13px;
        color: #1e293b;
        background: #f8fafc;
        cursor: pointer;
        border-radius: 5px 5px 0 0;
    }
    .wc-metabox-body {
        padding: 12px 14px;
    }
    .wc-box-tabs {
        display: flex;
        gap: 12px;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 8px;
    }
    .wc-box-tab-btn {
        background: none;
        border: none;
        padding-bottom: 5px;
        font-size: 11.5px;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
    }
    .wc-box-tab-btn.active {
        color: #2563eb;
        border-bottom: 2px solid #2563eb;
    }
    .wc-scroll-box {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #cbd5e1;
        border-radius: 5px !important;
        padding: 8px 10px;
        background: #ffffff;
        scrollbar-width: thin;
    }
    .wc-item-list {
        list-style: none;
        padding-left: 0;
        margin-bottom: 0;
    }
    .wc-item-list li {
        margin-bottom: 5px;
        font-size: 12.5px;
        color: #1e293b;
    }
    .wc-item-list .sub-tree {
        list-style: none;
        padding-left: 16px;
        margin-top: 4px;
        border-left: 2px dotted #cbd5e1;
        margin-left: 4px;
    }
    .wc-item-list label {
        cursor: pointer;
        user-select: none;
        margin-bottom: 0;
    }
    .btn-add-item-toggle {
        font-size: 12px;
        color: #2563eb;
        text-decoration: underline;
        display: inline-block;
        margin-top: 8px;
        cursor: pointer;
        font-weight: 600;
    }
    .btn-add-item-toggle:hover {
        color: #1d4ed8;
    }

    .wc-tag-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background-color: #f1f5f9;
        color: #1e293b;
        border: 1px solid #cbd5e1;
        padding: 2px 8px;
        border-radius: 5px !important;
        font-size: 11.5px;
        margin-right: 4px;
        margin-bottom: 4px;
    }
    .wc-tag-chip .remove-tag-btn {
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        color: #dc2626;
        font-weight: bold;
        font-size: 13px;
        line-height: 1;
    }
    .digital-box-highlight {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 6px !important;
        padding: 14px;
    }

    /* Safe Bengali Digits */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Mobile 100/100 Tuning */
    @media (max-width: 767.98px) {
        .header-top-actions {
            width: 100%;
        }
        .header-top-actions .btn-compact {
            width: 100%;
        }
    }
</style>

<div class="product-form-card p-3 p-md-4 mb-4" id="productEditRoot">
    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 pb-3 border-bottom">
        <div>
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center" style="font-size: 18px;">
                <i class="fas fa-edit text-primary me-2"></i>
                <span>Edit Product</span>
            </h5>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">Modify product specifications, pricing, inventory, and variants.</small>
        </div>
        <div class="header-top-actions d-flex gap-2">
            <a href="{{ route('admin.product.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-arrow-left me-1"></i>
                <span>Back to Products</span>
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger py-2 px-3 small border-0 shadow-xs mb-3" role="alert">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.product.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="productEditForm">
        @csrf
        @method('PUT')

        <div class="row g-3 g-md-4">
            <!-- Left Main Column -->
            <div class="col-12 col-lg-8">
                <!-- 1. Title & Descriptions -->
                <div class="form-section-title"><i class="fas fa-info-circle text-info"></i> Product Information</div>
                <div class="mb-3">
                    <label class="form-label">Product Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $product->title) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Full Description</label>
                    <textarea name="description" id="summernote-desc" class="form-control">{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label">Product Short Description</label>
                    <textarea name="short_description" class="form-control" rows="3">{{ old('short_description', $product->short_description) }}</textarea>
                </div>

                <!-- 2. Product Data, Types & Pricing -->
                <div class="form-section-title d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-tags text-success"></i> Product Data</span>
                    <div class="d-flex align-items-center gap-3">
                        <div class="form-check m-0">
                            <input class="form-check-input" type="checkbox" name="is_virtual" value="1" id="cb-virtual" onchange="toggleDigitalOptions()" {{ $product->is_virtual ? 'checked' : '' }} style="cursor: pointer;">
                            <label class="form-check-label small fw-semibold text-dark" for="cb-virtual" style="cursor: pointer;">Virtual</label>
                        </div>
                        <div class="form-check m-0">
                            <input class="form-check-input" type="checkbox" name="is_downloadable" value="1" id="cb-downloadable" onchange="toggleDigitalOptions()" {{ $product->is_downloadable ? 'checked' : '' }} style="cursor: pointer;">
                            <label class="form-check-label small fw-semibold text-dark" for="cb-downloadable" style="cursor: pointer;">Downloadable</label>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-light border mb-4" style="border-radius: 6px;">
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Product Type</label>
                            <select name="product_type" id="product_type_select" class="form-select form-select-sm fw-semibold" onchange="handleProductTypeChange(this.value)">
                                <option value="physical" {{ $product->product_type == 'physical' ? 'selected' : '' }}>Physical Product</option>
                                <option value="virtual" {{ $product->product_type == 'virtual' ? 'selected' : '' }}>Virtual Product</option>
                                <option value="downloadable" {{ $product->product_type == 'downloadable' ? 'selected' : '' }}>Downloadable Product</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6" id="stock-field-box">
                            <label class="form-label" id="base-stock-label">
                                Base Stock
                                @if($isVariableProduct)
                                    <span class="badge bg-secondary-subtle text-secondary small px-1.5 py-0.5">Calculated</span>
                                @else
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                            <input type="number" name="stock" id="base-stock-input" class="form-control form-control-sm bn-digit font-monospace {{ $isVariableProduct ? 'bg-light' : '' }}" value="{{ old('stock', $product->stock) }}" {{ $isVariableProduct ? 'readonly' : '' }} required>
                            <small class="text-primary {{ $isVariableProduct ? '' : 'd-none' }} fw-semibold" id="base-stock-help" style="font-size: 11px;">
                                <i class="fas fa-info-circle me-1"></i> Auto-synced from the sum of all variants.
                            </small>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Regular Price (৳) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="regular_price" id="reg-price" class="form-control form-control-sm bn-digit font-monospace" value="{{ old('regular_price', $product->regular_price) }}" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Sale Price (৳)</label>
                            <input type="number" step="0.01" name="sale_price" id="sale-price" class="form-control form-control-sm bn-digit font-monospace" value="{{ old('sale_price', $product->sale_price) }}">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Purchase Cost (৳)</label>
                            <input type="number" step="0.01" name="purchase_cost" id="purchase-cost-input" class="form-control form-control-sm bn-digit font-monospace" value="{{ old('purchase_cost', $product->purchase_cost ?? $product->purchase_price) }}" placeholder="Cost per unit">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Discount (%)</label>
                            <input type="number" step="0.01" name="discount_percentage" id="disc-pct" class="form-control form-control-sm bg-white bn-digit font-monospace" readonly value="{{ old('discount_percentage', $product->discount_percentage) }}">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Product SKU</label>
                            <input type="text" name="sku" class="form-control form-control-sm font-monospace bn-digit" value="{{ old('sku', $product->sku ?? '') }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Product Code</label>
                            <input type="text" name="product_code" class="form-control form-control-sm font-monospace bn-digit" value="{{ old('product_code', $product->product_code) }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Barcode</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="barcode" id="barcode-input" class="form-control font-monospace bn-digit" value="{{ old('barcode', $product->barcode) }}">
                                <button class="btn btn-warning btn-compact text-dark fw-bold" type="button" onclick="generateBarcode()">Gen</button>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="allow_out_of_stock_order" value="1" id="switch-backorder" {{ $product->allow_out_of_stock_order ? 'checked' : '' }} style="cursor: pointer;">
                                <label class="form-check-label small fw-semibold text-dark" for="switch-backorder" style="cursor: pointer;">Allow Backorders (Order when out of stock)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Downloadable Files Section -->
                <div id="downloadable-files-section" class="digital-box-highlight mb-4 {{ $product->is_downloadable ? '' : 'd-none' }}">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center" style="font-size: 13.5px;">
                        <i class="fas fa-file-download text-primary me-2"></i>
                        <span>Downloadable Files &amp; Settings</span>
                    </h6>
                    <input type="hidden" name="removed_download_ids" id="removed-download-ids" value="">

                    <div class="table-responsive border bg-white mb-3" style="border-radius: 5px;">
                        <table class="table table-bordered table-sm align-middle mb-0" id="download-files-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 180px;">File Name</th>
                                    <th>File (Secure Storage / Current)</th>
                                    <th>Or External Download URL</th>
                                    <th class="text-center" style="width: 40px;"><i class="fas fa-trash"></i></th>
                                </tr>
                            </thead>
                            <tbody id="download-files-tbody">
                                @foreach($downloadFiles as $dIndex => $df)
                                    <tr id="existing-download-row-{{ $df->id }}">
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $df->file_name }}</span>
                                        </td>
                                        <td>
                                            @if($df->file_path)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i> Hosted Securely</span>
                                            @else
                                                <span class="text-muted small">No local file</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="small text-truncate d-inline-block font-monospace" style="max-width: 250px;">{{ $df->file_url ?? '—' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-compact p-0 d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;" onclick="removeExistingDownloadFile({{ $df->id }})"><i class="fas fa-times" style="font-size: 10px;"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-compact mb-3" onclick="addDownloadFileRow()">
                        <i class="fas fa-plus me-1"></i> Add File
                    </button>

                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Download Limit</label>
                            <input type="number" name="download_limit" class="form-control form-control-sm bn-digit font-monospace" value="{{ old('download_limit', $product->download_limit) }}" placeholder="Unlimited (leave blank)">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Download Expiry (Days)</label>
                            <input type="number" name="download_expiry" class="form-control form-control-sm bn-digit font-monospace" value="{{ old('download_expiry', $product->download_expiry) }}" placeholder="Never (leave blank)">
                        </div>
                    </div>
                </div>

                <!-- 4. Virtual Product Section -->
                <div id="virtual-product-section" class="digital-box-highlight mb-4 {{ $product->is_virtual ? '' : 'd-none' }}">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center" style="font-size: 13.5px;">
                        <i class="fas fa-key text-warning me-2"></i>
                        <span>Virtual Product &amp; Software Licenses</span>
                    </h6>
                    <input type="hidden" name="removed_license_ids" id="removed-license-ids" value="">

                    @if($licenses->count() > 0)
                        <div class="mb-3">
                            <label class="small fw-bold text-dark mb-1">Assigned / Available Licenses Pool:</label>
                            <div class="table-responsive border bg-white" style="max-height: 180px; overflow-y: auto; border-radius: 5px;">
                                <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 12px;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>License Key</th>
                                            <th>Status</th>
                                            <th class="text-center" style="width: 40px;"><i class="fas fa-trash"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($licenses as $lic)
                                            <tr id="lic-row-{{ $lic->id }}">
                                                <td class="font-monospace bn-digit">{{ $lic->license_key }}</td>
                                                <td>
                                                    @if($lic->status === 'available')
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Available</span>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 bn-digit">Assigned (Order #{{ $lic->order_id }})</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($lic->status === 'available')
                                                        <button type="button" class="btn btn-link text-danger p-0" onclick="removeLicenseKey({{ $lic->id }})"><i class="fas fa-times"></i></button>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <div class="row g-2 g-md-3">
                        <div class="col-12">
                            <label class="form-label">Add More License Keys (One per line)</label>
                            <textarea name="license_keys_input" class="form-control form-control-sm font-monospace bn-digit" rows="3" placeholder="Paste new license keys here..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Customer Instructions / Access Note</label>
                            <textarea name="access_instructions" class="form-control form-control-sm" rows="3">{{ old('access_instructions', $product->access_instructions) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- 5. Advanced Variations Matrix (With Preserved Variant IDs & Individual Costs) -->
                <div class="form-section-title d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-sitemap text-warning"></i> Product Variations</span>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="has-variants-toggle" onchange="toggleVariantSection()" {{ $isVariableProduct ? 'checked' : '' }} style="cursor: pointer;">
                        <label class="form-check-label small fw-semibold text-dark" for="has-variants-toggle" style="cursor: pointer;">Enable Variations</label>
                    </div>
                </div>
                
                <div id="variant-section" class="mb-4 {{ $isVariableProduct ? '' : 'd-none' }}">
                    <input type="hidden" name="product_struct" id="product_struct" value="{{ $isVariableProduct ? 'variable' : 'simple' }}">
                    
                    <div class="p-3 bg-light border mb-3" style="border-radius: 6px;">
                        <div class="row g-2 align-items-center">
                            <div class="col-12 col-md-5">
                                <label class="form-label text-muted">Colors (comma separated):</label>
                                <input type="text" id="bulk-colors" class="form-control form-control-sm" placeholder=" Red, Blue, Black">
                            </div>
                            <div class="col-12 col-md-5">
                                <label class="form-label text-muted">Sizes / Specs (comma separated):</label>
                                <input type="text" id="bulk-sizes" class="form-control form-control-sm" placeholder=" M, L, XL or 41, 42">
                            </div>
                            <div class="col-12 col-md-2 pt-md-4">
                                <button type="button" class="btn btn-dark btn-compact w-100 shadow-xs" onclick="generateBulkMatrix()">Generate All</button>
                            </div>
                        </div>
                    </div>

                    <!-- Scroll Notice for Mobile & PC -->
                    <div class="d-flex justify-content-between align-items-center mb-1 text-muted small" style="font-size: 11px;">
                        <span><i class="fas fa-arrows-alt-h me-1 text-primary"></i> Scroll horizontally to view all fields clearly</span>
                        <span class="bn-digit">Minimum 1160px Canvas</span>
                    </div>

                    <div class="variant-table-container">
                        <table class="table align-middle variant-table" id="variant-table">
                            <thead>
                                <tr>
                                    <th style="min-width: 145px; width: 145px;">Image</th>
                                    <th style="min-width: 110px; width: 110px;">Color</th>
                                    <th style="min-width: 95px; width: 95px;">Size/Spec</th>
                                    <th style="min-width: 120px; width: 120px;">SKU</th>
                                    <th style="min-width: 140px; width: 140px;">Barcode</th>
                                    <th style="min-width: 105px; width: 105px;">Cost (৳)</th>
                                    <th style="min-width: 105px; width: 105px;">Reg. Price (৳)</th>
                                    <th style="min-width: 105px; width: 105px;">Sale Price (৳)</th>
                                    <th style="min-width: 85px; width: 85px;">Stock</th>
                                    <th style="min-width: 80px; width: 80px;">Alert Qty</th>
                                    <th class="text-center" style="min-width: 44px; width: 44px;">
                                        <button type="button" class="btn btn-sm btn-primary p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 24px; height: 24px;" onclick="addVariantRow()" title="Add Blank Row"><i class="fas fa-plus" style="font-size: 10px;"></i></button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="variant-tbody">
                                @foreach($variants as $index => $var)
                                @php
                                    $hasVarImg = !empty($var->image) && $var->image !== 'undefined';
                                    $varImgSrc = $hasVarImg ? $resolveAdminProductImg($var->image, 'https://placehold.co/40x40?text=No+Img') : 'https://placehold.co/40x40?text=No+Img';
                                    $vCostVal = !empty($var->purchase_cost) ? (float)$var->purchase_cost : (!empty($var->purchase_price) ? (float)$var->purchase_price : '');
                                @endphp
                                <tr id="variant-row-{{ $index }}">
                                    <td style="min-width: 145px; width: 145px;">
                                        <!-- CRITICAL: Preserve Existing Variant ID so order items & movements stay linked -->
                                        <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $var->id }}">

                                        <div class="d-flex align-items-center gap-1">
                                            <div class="thumb-preview-box" id="var-preview-box-{{ $index }}">
                                                <img id="var-img-preview-{{ $index }}" src="{{ $varImgSrc }}" class="border object-fit-cover shadow-xs" style="width: 34px; height: 34px; border-radius: 5px !important;" onerror="this.src='https://placehold.co/40x40?text=No+Img'">
                                                <button type="button" class="remove-thumb-btn {{ $hasVarImg ? '' : 'd-none' }}" id="var-remove-btn-{{ $index }}" style="width: 16px; height: 16px; font-size: 8px; top:-4px; right:-4px;" onclick="clearVariantImage({{ $index }})" title="Remove image">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <input type="hidden" name="variants[{{ $index }}][existing_image]" id="var-existing-{{ $index }}" value="{{ $hasVarImg ? $var->image : '' }}">
                                                <input type="hidden" name="variants[{{ $index }}][media_path]" id="var-media-{{ $index }}" value="">
                                                <input type="hidden" name="variants[{{ $index }}][remove_image]" id="var-remove-flag-{{ $index }}" value="0">
                                            </div>

                                            <button type="button" class="btn btn-outline-primary btn-compact p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 25px; height: 25px; border-radius: 5px !important;" onclick="chooseVariantMedia({{ $index }})" title="Choose from Media Library">
                                                <i class="fas fa-images" style="font-size: 10px;"></i>
                                            </button>

                                            <label class="btn btn-outline-secondary btn-compact p-0 d-inline-flex align-items-center justify-content-center mb-0 shadow-xs" style="width: 25px; height: 25px; border-radius: 5px !important; cursor: pointer;" title="Upload File">
                                                <i class="fas fa-upload" style="font-size: 10px;"></i>
                                                <input type="file" name="variants[{{ $index }}][image]" id="var-file-{{ $index }}" class="d-none" accept="image/*" onchange="previewVariantFile(this, {{ $index }})">
                                            </label>
                                        </div>
                                    </td>
                                    <td style="min-width: 110px; width: 110px;"><input type="text" name="variants[{{ $index }}][color]" class="form-control" value="{{ $var->color }}" placeholder="Color"></td>
                                    <td style="min-width: 95px; width: 95px;"><input type="text" name="variants[{{ $index }}][size]" class="form-control" value="{{ $var->size }}" placeholder="Size"></td>
                                    <td style="min-width: 120px; width: 120px;"><input type="text" name="variants[{{ $index }}][sku]" class="form-control font-monospace bn-digit" value="{{ $var->product_code ?? ($var->sku ?? '') }}" placeholder="SKU"></td>
                                    <td style="min-width: 140px; width: 140px;"><input type="text" name="variants[{{ $index }}][barcode]" class="form-control font-monospace bn-digit" value="{{ $var->barcode }}" placeholder="Barcode"></td>
                                    <td style="min-width: 105px; width: 105px;"><input type="number" step="0.01" name="variants[{{ $index }}][purchase_cost]" class="form-control font-monospace bn-digit" value="{{ $vCostVal }}" placeholder="Cost"></td>
                                    <td style="min-width: 105px; width: 105px;"><input type="number" step="0.01" name="variants[{{ $index }}][regular_price]" class="form-control font-monospace bn-digit" value="{{ $var->regular_price }}" placeholder="Reg. Price"></td>
                                    <td style="min-width: 105px; width: 105px;"><input type="number" step="0.01" name="variants[{{ $index }}][sale_price]" class="form-control font-monospace bn-digit" value="{{ $var->sale_price }}" placeholder="Sale Price"></td>
                                    <td style="min-width: 85px; width: 85px;"><input type="number" name="variants[{{ $index }}][stock]" class="form-control font-monospace bn-digit" value="{{ $var->stock }}" placeholder="Stock"></td>
                                    <td style="min-width: 80px; width: 80px;"><input type="number" name="variants[{{ $index }}][alert_quantity]" class="form-control font-monospace bn-digit" value="{{ $var->alert_quantity ?? 5 }}" placeholder="Alert"></td>
                                    <td class="text-center" style="min-width: 44px; width: 44px;">
                                        <button type="button" class="btn btn-outline-danger btn-compact p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 26px; height: 26px; border-radius: 5px !important;" onclick="this.closest('tr').remove()"><i class="fas fa-trash" style="font-size: 10px;"></i></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 6. Physical Dimensions & Shipping -->
                <div id="shipping-dimensions-section" class="{{ $product->is_virtual ? 'd-none' : '' }}">
                    <div class="form-section-title"><i class="fas fa-truck text-secondary"></i> Shipping Dimensions &amp; Video</div>
                    <div class="row g-2 g-md-3 mb-4">
                        <div class="col-6 col-md-3">
                            <label class="form-label">Weight (KG)</label>
                            <input type="number" step="0.01" name="weight" class="form-control form-control-sm bn-digit font-monospace" value="{{ old('weight', $product->weight) }}">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Length (cm)</label>
                            <input type="number" step="0.01" name="length" class="form-control form-control-sm bn-digit font-monospace" value="{{ old('length', $product->length) }}">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Width (cm)</label>
                            <input type="number" step="0.01" name="width" class="form-control form-control-sm bn-digit font-monospace" value="{{ old('width', $product->width) }}">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Height (cm)</label>
                            <input type="number" step="0.01" name="height" class="form-control form-control-sm bn-digit font-monospace" value="{{ old('height', $product->height) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Youtube Video URL</label>
                            <input type="url" name="video_url" class="form-control form-control-sm font-monospace" value="{{ old('video_url', $product->video_url ?? '') }}" placeholder="https://youtube.com/watch?v=...">
                        </div>
                    </div>
                </div>

                <!-- 7. Advanced SEO Management -->
                <div class="form-section-title"><i class="fas fa-search text-success"></i> SEO Settings</div>
                <div class="p-3 bg-light border mb-4" style="border-radius: 6px;">
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">SEO Meta Title</label>
                            <input type="text" name="seo_title" id="seo_title" class="form-control form-control-sm" value="{{ old('seo_title', $product->seo_title ?? $product->title) }}" oninput="updateSeoPreview()">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Focus Keyword(s)</label>
                            <input type="text" name="focus_keyphrase" class="form-control form-control-sm" value="{{ old('focus_keyphrase', $product->focus_keyphrase ?? '') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Canonical URL</label>
                            <input type="url" name="canonical_url" class="form-control form-control-sm font-monospace" value="{{ old('canonical_url', $product->canonical_url ?? '') }}" placeholder="{{ url('/') }}/custom-url">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Meta Description</label>
                            <textarea name="meta_description" id="meta_desc" class="form-control form-control-sm" rows="3" oninput="updateSeoPreview()">{{ old('meta_description', $product->meta_description ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar Column -->
            <div class="col-12 col-lg-4">
                <!-- 1. Publish Box -->
                <div class="wc-metabox">
                    <div class="wc-metabox-header" data-bs-toggle="collapse" data-bs-target="#mbPublish" aria-expanded="true">
                        <span>Publish</span>
                        <i class="fas fa-chevron-down small text-muted"></i>
                    </div>
                    <div class="collapse show" id="mbPublish">
                        <div class="wc-metabox-body">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="switch-featured" {{ $product->is_featured ? 'checked' : '' }} style="cursor: pointer;">
                                <label class="form-check-label fw-semibold small text-dark" for="switch-featured" style="cursor: pointer;">Featured Product</label>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.product.index') }}" class="btn btn-light border btn-compact">Cancel</a>
                                <button type="submit" class="btn btn-primary btn-compact px-3 shadow-xs">Update Product</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Product categories Box -->
                <div class="wc-metabox" id="product-categories-box">
                    <div class="wc-metabox-header" data-bs-toggle="collapse" data-bs-target="#mbCategories" aria-expanded="true">
                        <span>Product categories</span>
                        <i class="fas fa-chevron-down small text-muted"></i>
                    </div>
                    <div class="collapse show" id="mbCategories">
                        <div class="wc-metabox-body">
                            <div class="wc-box-tabs">
                                <button type="button" class="wc-box-tab-btn active" onclick="switchCategoryTab('all')">All categories</button>
                                <button type="button" class="wc-box-tab-btn" onclick="switchCategoryTab('most-used')">Most Used</button>
                            </div>

                            <div class="wc-scroll-box" id="categoryScrollBox">
                                <ul class="wc-item-list" id="categoryTreeList">
                                    @forelse($categories as $cat)
                                        @php
                                            $childCats = $subCategories->where('category_id', $cat->id);
                                            $isParentChecked = in_array((int)$cat->id, $selectedCatIds);
                                        @endphp
                                        <li id="tree-parent-{{ $cat->id }}">
                                            <label class="d-flex align-items-center gap-2">
                                                <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}" class="form-check-input cat-cb-parent m-0" id="cat-p-{{ $cat->id }}" {{ $isParentChecked ? 'checked' : '' }}>
                                                <span>{{ $cat->name }}</span>
                                            </label>

                                            @if($childCats->count() > 0)
                                                <ul class="sub-tree" id="sub-tree-{{ $cat->id }}">
                                                    @foreach($childCats as $child)
                                                        @php
                                                            $isChildChecked = in_array((int)$child->id, $selectedSubCatIds);
                                                        @endphp
                                                        <li id="tree-child-{{ $child->id }}">
                                                            <label class="d-flex align-items-center gap-2">
                                                                <input type="checkbox" name="sub_category_ids[]" value="{{ $child->id }}" class="form-check-input cat-cb-child m-0" data-parent="{{ $cat->id }}" id="cat-c-{{ $child->id }}" {{ $isChildChecked ? 'checked' : '' }}>
                                                                <span>{{ $child->name }}</span>
                                                            </label>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <ul class="sub-tree d-none" id="sub-tree-{{ $cat->id }}"></ul>
                                            @endif
                                        </li>
                                    @empty
                                        <li class="text-muted small empty-cat-note">No categories found.</li>
                                    @endforelse
                                </ul>
                            </div>

                            <a class="btn-add-item-toggle" onclick="toggleNewCategoryForm()">+ Add new category</a>

                            <div id="inlineNewCategoryForm" class="mt-2 pt-2 border-top d-none">
                                <div class="mb-2">
                                    <input type="text" id="new_cat_name" class="form-control form-control-sm" placeholder="Category name..." autocomplete="off">
                                </div>
                                <div class="mb-2">
                                    <select id="new_cat_parent" class="form-select form-select-sm">
                                        <option value="">— Parent category —</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <button type="button" class="btn btn-outline-secondary btn-compact shadow-xs" id="btn-save-ajax-cat" onclick="saveNewCategoryAjax()">
                                        Add new category
                                    </button>
                                    <span id="cat-ajax-loader" class="spinner-border spinner-border-sm text-primary d-none"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Product Tags Box -->
                <div class="wc-metabox" id="product-tags-box">
                    <div class="wc-metabox-header" data-bs-toggle="collapse" data-bs-target="#mbTags" aria-expanded="true">
                        <span>Product tags</span>
                        <i class="fas fa-chevron-down small text-muted"></i>
                    </div>
                    <div class="collapse show" id="mbTags">
                        <div class="wc-metabox-body">
                            <div class="d-flex gap-2 mb-1">
                                <input type="text" id="tag_input" class="form-control form-control-sm" placeholder=" Cotton, Software, License" autocomplete="off">
                                <button type="button" class="btn btn-outline-secondary btn-compact shadow-xs" onclick="addTagFromInput()">Add</button>
                            </div>
                            <p class="text-muted small mb-2" style="font-size: 11px;">Separate tags with commas</p>

                            <div id="selected-tags-container" class="d-flex flex-wrap gap-1 mb-2"></div>

                            <a href="javascript:void(0)" class="btn-add-item-toggle mt-1" id="btn-toggle-most-used" onclick="toggleMostUsedTags()">Choose from the most used tags</a>
                            <div id="most-used-tags-cloud" class="mt-2 pt-2 border-top d-none" style="font-size: 12px;">
                                <div id="most-used-tags-list" class="d-flex flex-wrap gap-1">
                                    <span class="text-muted small">Loading tags...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Product Brands Box -->
                <div class="wc-metabox" id="product-brands-box">
                    <div class="wc-metabox-header" data-bs-toggle="collapse" data-bs-target="#mbBrands" aria-expanded="true">
                        <span>Product brands</span>
                        <i class="fas fa-chevron-down small text-muted"></i>
                    </div>
                    <div class="collapse show" id="mbBrands">
                        <div class="wc-metabox-body">
                            <div class="wc-box-tabs">
                                <button type="button" class="wc-box-tab-btn active" onclick="switchBrandTab('all')">All brands</button>
                                <button type="button" class="wc-box-tab-btn" onclick="switchBrandTab('most-used')">Most Used</button>
                            </div>

                            <div class="wc-scroll-box" id="brandScrollBox">
                                <ul class="wc-item-list" id="brandList">
                                    @forelse($brands as $brand)
                                        @php
                                            $isBrandChecked = in_array((int)$brand->id, $selectedBrandIds ?? []);
                                        @endphp
                                        <li id="brand-item-{{ $brand->id }}">
                                            <label class="d-flex align-items-center gap-2">
                                                <input type="checkbox" name="brand_ids[]" value="{{ $brand->id }}" class="form-check-input brand-cb m-0" id="brand-chk-${brand.id}" {{ $isBrandChecked ? 'checked' : '' }}>
                                                <span>{{ $brand->name }}</span>
                                            </label>
                                        </li>
                                    @empty
                                        <li class="text-muted small empty-brand-note">No brands found.</li>
                                    @endforelse
                                </ul>
                            </div>

                            <a class="btn-add-item-toggle" onclick="toggleNewBrandForm()">+ Add new brand</a>

                            <div id="inlineNewBrandForm" class="mt-2 pt-2 border-top d-none">
                                <div class="mb-2">
                                    <input type="text" id="new_brand_name" class="form-control form-control-sm" placeholder="Brand name..." autocomplete="off">
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <button type="button" class="btn btn-outline-secondary btn-compact shadow-xs" id="btn-save-ajax-brand" onclick="saveNewBrandAjax()">
                                        Add new brand
                                    </button>
                                    <span id="brand-ajax-loader" class="spinner-border spinner-border-sm text-primary d-none"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. Product Image (Thumbnail) Box (With Media Library Option) -->
                <div class="wc-metabox">
                    <div class="wc-metabox-header" data-bs-toggle="collapse" data-bs-target="#mbImage" aria-expanded="true">
                        <span>Product image</span>
                        <i class="fas fa-chevron-down small text-muted"></i>
                    </div>
                    <div class="collapse show" id="mbImage">
                        <div class="wc-metabox-body text-center">
                            <input type="hidden" name="remove_thumbnail" id="remove-thumbnail-flag" value="0">
                            <input type="hidden" name="thumbnail_media" id="thumbnail-media-input" value="">

                            <div class="d-flex flex-column gap-2 mb-2">
                                <button type="button" class="btn btn-outline-primary btn-compact w-100 shadow-xs" onclick="chooseProductThumbnailMedia()">
                                    <i class="fas fa-images me-1"></i> Choose from Media Library
                                </button>
                                <span class="small text-muted" style="font-size: 11px;">or replace with local file:</span>
                            </div>

                            <div class="img-upload-box" onclick="document.getElementById('thumbnail-input').click()">
                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-1"></i>
                                <p class="small text-muted mb-0">Upload local file</p>
                                <input type="file" name="thumbnail" id="thumbnail-input" class="d-none" accept="image/*" onchange="previewSingleImage(this, 'thumb-preview')">
                            </div>

                            @php
                                $thumbImgSrc = $resolveAdminProductImg($product->thumbnail, '');
                            @endphp

                            <div id="thumb-preview" class="mt-2 text-center {{ !empty($product->thumbnail) ? '' : 'd-none' }}">
                                <div class="thumb-preview-box">
                                    <img id="thumb-preview-img" src="{{ $thumbImgSrc }}" class="img-thumbnail shadow-xs" style="max-height: 120px; border-radius: 5px;" onerror="this.src='https://placehold.co/120x120?text=No+Img'">
                                    <button type="button" class="remove-thumb-btn" onclick="removeProductThumbnail()" title="Delete / Remove Thumbnail">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6. Product Gallery Box (With Media Library Multi-Select) -->
                <div class="wc-metabox">
                    <div class="wc-metabox-header" data-bs-toggle="collapse" data-bs-target="#mbGallery" aria-expanded="true">
                        <span>Product gallery</span>
                        <i class="fas fa-chevron-down small text-muted"></i>
                    </div>
                    <div class="collapse show" id="mbGallery">
                        <div class="wc-metabox-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <button type="button" class="btn btn-outline-primary btn-compact shadow-xs px-3" onclick="chooseGalleryMedia()">
                                    <i class="fas fa-images me-1"></i> Add from Media Library
                                </button>
                                <button type="button" id="clear-all-gallery-btn" class="btn btn-link text-danger text-decoration-none small p-0 d-none" onclick="clearAllGalleryImages()">
                                    Clear all
                                </button>
                            </div>

                            <div class="text-center my-1">
                                <label class="btn btn-link text-decoration-none small p-0 text-muted" style="font-size: 11px; cursor: pointer;">
                                    <i class="fas fa-upload me-1"></i> or upload files from computer
                                    <input type="file" name="gallery_images[]" id="gallery-input" class="d-none" accept="image/*" multiple onchange="previewGalleryImages(this)">
                                </label>
                            </div>

                            <div id="gallery-preview-container" class="preview-grid"></div>

                            @if($galleries->count() > 0)
                            <div class="mt-2 pt-2 border-top">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small fw-bold text-muted">Current Gallery</span>
                                    <button type="button" class="btn btn-link text-danger text-decoration-none small p-0" onclick="removeAllExistingGallery()">
                                        Remove all
                                    </button>
                                </div>
                                <input type="hidden" name="removed_galleries" id="removed-galleries" value="">
                                <div class="preview-grid" id="existing-gallery-grid">
                                    @foreach($galleries as $g)
                                    @php
                                        $gImgSrc = $resolveAdminProductImg($g->image_path, 'https://placehold.co/80x80?text=No+Img');
                                    @endphp
                                    <div class="preview-item shadow-xs" id="existing-g-{{ $g->id }}" data-id="{{ $g->id }}">
                                        <img src="{{ $gImgSrc }}" onerror="this.src='https://placehold.co/80x80?text=No+Img'">
                                        <button type="button" class="remove-btn" onclick="removeExistingGallery({{ $g->id }})" title="Remove"><i class="fas fa-trash"></i></button>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal: Notice / Alert Dialog -->
<div class="modal fade" id="productEditNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div id="productEditNoticeIconWrap" class="rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center mx-auto mb-2" style="width: 44px; height: 44px;">
                    <i id="productEditNoticeIcon" class="fas fa-exclamation-triangle fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="productEditNoticeTitle" style="font-size: 14px;">Notice</h6>
                <p class="text-muted small mb-3" id="productEditNoticeMsg" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
    let downloadFileIndex = 100;
    let removedDownloadIds = [];
    let removedLicenseIds = [];

    function showProductEditNotice(message, title = 'Notice', isError = false) {
        const titleEl = document.getElementById('productEditNoticeTitle');
        const msgEl = document.getElementById('productEditNoticeMsg');
        const iconWrap = document.getElementById('productEditNoticeIconWrap');
        const icon = document.getElementById('productEditNoticeIcon');

        if (titleEl) titleEl.innerText = title;
        if (msgEl) msgEl.innerText = message;
        if (iconWrap && icon) {
            iconWrap.className = isError 
                ? 'rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2' 
                : 'rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center mx-auto mb-2';
            icon.className = isError ? 'fas fa-times-circle fs-4' : 'fas fa-exclamation-triangle fs-4';
        }

        const modalEl = document.getElementById('productEditNoticeModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function extractMediaItem(mediaInput) {
        if (!mediaInput) return null;
        const item = Array.isArray(mediaInput) ? mediaInput[0] : mediaInput;
        if (!item || typeof item !== 'object') return null;
        const rawPath = item.file_path || item.path || item.url || '';
        const rawUrl  = item.url || item.file_path || item.path || '';
        if (!rawPath || rawPath === 'undefined') return null;
        return {
            path: String(rawPath).trim(),
            url:  String(rawUrl).trim(),
            alt:  item.alt_text || item.title || ''
        };
    }

    $(document).ready(function() {
        const SummernoteMediaButton = function (context) {
            const ui = $.summernote.ui;
            const button = ui.button({
                contents: '<i class="note-icon-picture"></i>',
                tooltip: 'Insert Image from Media Library',
                click: function () {
                    if (typeof window.openMediaPicker === 'function') {
                        window.openMediaPicker({
                            multiple: false,
                            onSelect: function(media) {
                                const picked = extractMediaItem(media);
                                if (picked && picked.url) {
                                    context.invoke('editor.insertImage', picked.url, function ($image) {
                                        if (picked.alt) {
                                            $image.attr('alt', picked.alt);
                                        }
                                        $image.addClass('img-fluid rounded');
                                        $image.css('max-width', '100%');
                                    });
                                }
                            }
                        });
                    } else {
                        showProductEditNotice('Media Library is loading, please try again.');
                    }
                }
            });
            return button.render();
        };

        $('#summernote-desc').summernote({
            placeholder: 'Write comprehensive product details, specifications, features...',
            tabsize: 2,
            height: 220,
            toolbar: [
                ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'mediaImage', 'video']],
                ['view', ['fullscreen', 'codeview']]
            ],
            buttons: {
                mediaImage: SummernoteMediaButton
            },
            callbacks: {
                onImageUpload: function(files) {
                    if (files.length > 0) {
                        uploadSummernoteImageDirect(files[0]);
                    }
                }
            }
        });

        $('#reg-price, #sale-price').on('input', function() {
            let reg = parseFloat($('#reg-price').val()) || 0;
            let sale = parseFloat($('#sale-price').val()) || 0;
            if (reg > 0 && sale > 0 && reg > sale) {
                $('#disc-pct').val(((reg - sale) / reg * 100).toFixed(2));
            } else {
                $('#disc-pct').val(0);
            }
        });

        @php
            $existingTagArray = !empty($productTags) ? (array)$productTags : [];
        @endphp
        const initialTags = @json($existingTagArray);
        if (Array.isArray(initialTags) && initialTags.length > 0) {
            initialTags.forEach(t => appendTagChip(t));
        }

        $('#tag_input').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addTagFromInput();
            }
        });

        $('#productEditForm').on('submit', function() {
            const hasVarToggle = document.getElementById('has-variants-toggle');
            const structInput = document.getElementById('product_struct');
            if (hasVarToggle && structInput) {
                structInput.value = hasVarToggle.checked ? 'variable' : 'simple';
            }
        });
    });

    function uploadSummernoteImageDirect(file) {
        const formData = new FormData();
        formData.append("files[]", file);
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        formData.append("_token", token);

        fetch("{{ route('admin.media.upload') }}", {
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            const uploadedItems = res.uploaded || res.files || [];
            if (res.status === 'success' && uploadedItems.length > 0) {
                const imgUrl = uploadedItems[0].url;
                $('#summernote-desc').summernote('insertImage', imgUrl, function ($image) {
                    $image.addClass('img-fluid rounded');
                    $image.css('max-width', '100%');
                });
            } else {
                showProductEditNotice('Image upload failed.', 'Error', true);
            }
        })
        .catch(err => {
            console.error('Summernote upload error:', err);
            showProductEditNotice('Failed to upload image into media library.', 'Error', true);
        });
    }

    function chooseProductThumbnailMedia() {
        if (typeof window.openMediaPicker === 'function') {
            window.openMediaPicker({
                multiple: false,
                onSelect: function(media) {
                    const picked = extractMediaItem(media);
                    if (picked && picked.path) {
                        document.getElementById('thumbnail-media-input').value = picked.path;
                        document.getElementById('thumbnail-input').value = '';
                        document.getElementById('remove-thumbnail-flag').value = '0';
                        const previewBox = document.getElementById('thumb-preview');
                        previewBox.classList.remove('d-none');
                        document.getElementById('thumb-preview-img').src = picked.url;
                    }
                }
            });
        }
    }

    function chooseGalleryMedia() {
        if (typeof window.openMediaPicker === 'function') {
            window.openMediaPicker({
                multiple: true,
                onSelect: function(mediaList) {
                    const list = Array.isArray(mediaList) ? mediaList : (mediaList ? [mediaList] : []);
                    if (list.length > 0) {
                        const container = document.getElementById('gallery-preview-container');
                        const clearAllBtn = document.getElementById('clear-all-gallery-btn');

                        list.forEach(rawItem => {
                            const picked = extractMediaItem(rawItem);
                            if (!picked || !picked.path) return;
                            let div = document.createElement('div');
                            div.className = 'preview-item shadow-xs';
                            div.innerHTML = `
                                <img src="${picked.url}" onerror="this.src='https://placehold.co/70x70?text=Img'">
                                <input type="hidden" name="gallery_media[]" value="${picked.path}">
                                <button type="button" class="remove-btn" onclick="this.closest('.preview-item').remove()" title="Remove"><i class="fas fa-times"></i></button>
                            `;
                            container.appendChild(div);
                        });

                        if (clearAllBtn) clearAllBtn.classList.remove('d-none');
                    }
                }
            });
        }
    }

    function handleProductTypeChange(type) {
        if (type === 'virtual') {
            $('#cb-virtual').prop('checked', true);
            $('#cb-downloadable').prop('checked', false);
        } else if (type === 'downloadable') {
            $('#cb-virtual').prop('checked', true);
            $('#cb-downloadable').prop('checked', true);
        } else {
            $('#cb-virtual').prop('checked', false);
            $('#cb-downloadable').prop('checked', false);
        }
        toggleDigitalOptions();
    }

    function toggleDigitalOptions() {
        const isVirtual = $('#cb-virtual').is(':checked');
        const isDownloadable = $('#cb-downloadable').is(':checked');

        if (isDownloadable) {
            $('#downloadable-files-section').removeClass('d-none');
            if ($('#download-files-tbody').children().length === 0) {
                addDownloadFileRow();
            }
        } else {
            $('#downloadable-files-section').addClass('d-none');
        }

        if (isVirtual) {
            $('#virtual-product-section').removeClass('d-none');
            $('#shipping-dimensions-section').addClass('d-none');
        } else {
            $('#virtual-product-section').addClass('d-none');
            $('#shipping-dimensions-section').removeClass('d-none');
        }
    }

    function addDownloadFileRow() {
        const tbody = document.getElementById('download-files-tbody');
        const tr = document.createElement('tr');
        tr.id = 'download-row-' + downloadFileIndex;
        tr.innerHTML = `
            <td>
                <input type="text" name="downloadable_files[${downloadFileIndex}][name]" class="form-control form-control-sm" placeholder="File Display Name">
            </td>
            <td>
                <input type="file" name="downloadable_files[${downloadFileIndex}][file]" class="form-control form-control-sm">
            </td>
            <td>
                <input type="url" name="downloadable_files[${downloadFileIndex}][url]" class="form-control form-control-sm font-monospace" placeholder="https://drive.google.com/...">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-compact p-0 d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px;" onclick="document.getElementById('download-row-${downloadFileIndex}').remove()"><i class="fas fa-times" style="font-size: 10px;"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
        downloadFileIndex++;
    }

    function removeExistingDownloadFile(id) {
        if (!removedDownloadIds.includes(id)) {
            removedDownloadIds.push(id);
        }
        document.getElementById('removed-download-ids').value = removedDownloadIds.join(',');
        document.getElementById('existing-download-row-' + id)?.remove();
    }

    function removeLicenseKey(id) {
        if (!removedLicenseIds.includes(id)) {
            removedLicenseIds.push(id);
        }
        document.getElementById('removed-license-ids').value = removedLicenseIds.join(',');
        document.getElementById('lic-row-' + id)?.remove();
    }

    function generateBarcode() {
        document.getElementById('barcode-input').value = Math.floor(100000000000 + Math.random() * 900000000000);
    }

    function previewSingleImage(input, previewId) {
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                let previewBox = document.getElementById(previewId);
                previewBox.classList.remove('d-none');
                document.getElementById('thumb-preview-img').src = e.target.result;
                document.getElementById('remove-thumbnail-flag').value = '0';
                document.getElementById('thumbnail-media-input').value = '';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeProductThumbnail() {
        document.getElementById('thumbnail-input').value = '';
        document.getElementById('thumb-preview').classList.add('d-none');
        document.getElementById('thumb-preview-img').src = '';
        document.getElementById('thumbnail-media-input').value = '';
        document.getElementById('remove-thumbnail-flag').value = '1';
    }

    let dt = typeof DataTransfer !== 'undefined' ? new DataTransfer() : null;
    function previewGalleryImages(input) {
        const container = document.getElementById('gallery-preview-container');
        const clearBtn = document.getElementById('clear-all-gallery-btn');
        if (input.files && input.files.length > 0) {
            Array.from(input.files).forEach(file => {
                if (dt) {
                    try { dt.items.add(file); } catch (e) {}
                }
                let reader = new FileReader();
                reader.onload = function(e) {
                    let div = document.createElement('div');
                    div.className = 'preview-item shadow-xs';
                    div.innerHTML = `<img src="${e.target.result}"><button type="button" class="remove-btn" onclick="removeGalleryImage(this, '${file.name.replace(/'/g, "\\'")}')" title="Remove"><i class="fas fa-times"></i></button>`;
                    container.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
            if (dt && dt.files.length > 0) {
                try { input.files = dt.files; } catch (e) {}
            }
            if (clearBtn) clearBtn.classList.remove('d-none');
        }
    }

    function removeGalleryImage(btn, filename) {
        btn.closest('.preview-item').remove();
        if (dt && typeof DataTransfer !== 'undefined') {
            try {
                const newDt = new DataTransfer();
                for (let i = 0; i < dt.items.length; i++) {
                    const f = dt.items[i].getAsFile();
                    if (f && f.name !== filename) {
                        newDt.items.add(f);
                    }
                }
                dt = newDt;
                document.getElementById('gallery-input').files = dt.files;
            } catch (e) {}
        }
        if ($('#gallery-preview-container .preview-item').length === 0) {
            document.getElementById('clear-all-gallery-btn')?.classList.add('d-none');
        }
    }

    function clearAllGalleryImages() {
        if (typeof DataTransfer !== 'undefined') {
            dt = new DataTransfer();
        }
        document.getElementById('gallery-input').value = '';
        document.getElementById('gallery-preview-container').innerHTML = '';
        document.getElementById('clear-all-gallery-btn')?.classList.add('d-none');
    }

    let removedGals = [];
    function removeExistingGallery(id) {
        if (!removedGals.includes(id)) {
            removedGals.push(id);
        }
        document.getElementById('removed-galleries').value = removedGals.join(',');
        document.getElementById('existing-g-' + id)?.remove();
    }

    function removeAllExistingGallery() {
        document.querySelectorAll('#existing-gallery-grid .preview-item').forEach(item => {
            let id = parseInt(item.dataset.id);
            if (id && !removedGals.includes(id)) {
                removedGals.push(id);
            }
            item.remove();
        });
        document.getElementById('removed-galleries').value = removedGals.join(',');
    }

    // ==========================================
    // Variant Row Handling (Preserving IDs & Syncing Stock)
    // ==========================================
    function chooseVariantMedia(idx) {
        if (typeof window.openMediaPicker === 'function') {
            window.openMediaPicker({
                multiple: false,
                onSelect: function(media) {
                    const picked = extractMediaItem(media);
                    if (picked && picked.path) {
                        const mediaEl  = document.getElementById('var-media-' + idx);
                        const existEl  = document.getElementById('var-existing-' + idx);
                        const fileEl   = document.getElementById('var-file-' + idx);
                        const remFlag  = document.getElementById('var-remove-flag-' + idx);
                        const imgEl    = document.getElementById('var-img-preview-' + idx);
                        const remBtn   = document.getElementById('var-remove-btn-' + idx);
                        const structEl = document.getElementById('product_struct');

                        if (mediaEl) mediaEl.value = picked.path;
                        if (existEl) existEl.value = picked.path;
                        if (fileEl) fileEl.value = '';
                        if (remFlag) remFlag.value = '0';
                        if (imgEl) imgEl.src = picked.url;
                        if (remBtn) remBtn.classList.remove('d-none');
                        if (structEl) structEl.value = 'variable';
                    }
                }
            });
        }
    }

    function previewVariantFile(input, index) {
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('var-img-preview-' + index);
                if (img) img.src = e.target.result;
                const mediaEl  = document.getElementById('var-media-' + index);
                const existEl  = document.getElementById('var-existing-' + index);
                const remFlag  = document.getElementById('var-remove-flag-' + index);
                const structEl = document.getElementById('product_struct');

                if (mediaEl) mediaEl.value = '';
                if (existEl) existEl.value = '';
                if (remFlag) remFlag.value = '0';
                if (structEl) structEl.value = 'variable';
                document.getElementById('var-remove-btn-' + index)?.classList.remove('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function clearVariantImage(index) {
        const fileInput     = document.getElementById('var-file-' + index);
        const existingInput = document.getElementById('var-existing-' + index);
        const mediaInput    = document.getElementById('var-media-' + index);
        const remFlag       = document.getElementById('var-remove-flag-' + index);
        const img           = document.getElementById('var-img-preview-' + index);
        const btn           = document.getElementById('var-remove-btn-' + index);

        if (fileInput) fileInput.value = '';
        if (existingInput) existingInput.value = '';
        if (mediaInput) mediaInput.value = '';
        if (remFlag) remFlag.value = '1';
        if (img) img.src = 'https://placehold.co/40x40?text=No+Img';
        if (btn) btn.classList.add('d-none');
    }

    let variantIndex = {{ count($variants) + 10 }};

    function toggleVariantSection() {
        const isChecked = document.getElementById('has-variants-toggle').checked;
        const section = document.getElementById('variant-section');
        const structInput = document.getElementById('product_struct');
        const baseStockInput = document.getElementById('base-stock-input');
        const baseStockHelp = document.getElementById('base-stock-help');
        const baseStockLabel = document.getElementById('base-stock-label');

        structInput.value = isChecked ? 'variable' : 'simple';

        if (isChecked) {
            section.classList.remove('d-none');
            if (baseStockInput) {
                baseStockInput.setAttribute('readonly', 'readonly');
                baseStockInput.classList.add('bg-light');
            }
            if (baseStockHelp) baseStockHelp.classList.remove('d-none');
            if (baseStockLabel) baseStockLabel.innerHTML = 'Base Stock <span class="badge bg-secondary-subtle text-secondary small px-1.5 py-0.5">Calculated</span>';

            if (document.getElementById('variant-tbody').children.length === 0) {
                addVariantRow();
            }
        } else {
            section.classList.add('d-none');
            if (baseStockInput) {
                baseStockInput.removeAttribute('readonly');
                baseStockInput.classList.remove('bg-light');
            }
            if (baseStockHelp) baseStockHelp.classList.add('d-none');
            if (baseStockLabel) baseStockLabel.innerHTML = 'Base Stock <span class="text-danger">*</span>';
        }
    }

    function addVariantRow(color = '', size = '', sharedImgUrl = '', sharedImgPath = '') {
        const regPrice = document.getElementById('reg-price').value || '';
        const salePrice = document.getElementById('sale-price').value || '';
        const defaultCost = document.getElementById('purchase-cost-input').value || '';
        const tbody = document.getElementById('variant-tbody');
        const tr = document.createElement('tr');
        const autoSku = 'V-' + Math.floor(1000 + Math.random() * 9000);
        const autoBarcode = Math.floor(100000000000 + Math.random() * 900000000000);
        const displayImg = sharedImgUrl ? sharedImgUrl : 'https://placehold.co/40x40?text=No+Img';
        const hasImgClass = sharedImgUrl ? '' : 'd-none';

        document.getElementById('product_struct').value = 'variable';
        const toggleEl = document.getElementById('has-variants-toggle');
        if (toggleEl && !toggleEl.checked) {
            toggleEl.checked = true;
            toggleVariantSection();
        }

        tr.innerHTML = `
            <td style="min-width: 145px; width: 145px;">
                <!-- Empty ID for newly added variant row -->
                <input type="hidden" name="variants[${variantIndex}][id]" value="">

                <div class="d-flex align-items-center gap-1">
                    <div class="thumb-preview-box" id="var-preview-box-${variantIndex}">
                        <img id="var-img-preview-${variantIndex}" src="${displayImg}" class="border object-fit-cover shadow-xs" style="width: 34px; height: 34px; border-radius: 5px !important;" onerror="this.src='https://placehold.co/40x40?text=No+Img'">
                        <button type="button" class="remove-thumb-btn ${hasImgClass}" id="var-remove-btn-${variantIndex}" style="width: 16px; height: 16px; font-size: 8px; top:-4px; right:-4px;" onclick="clearVariantImage(${variantIndex})" title="Remove image">
                            <i class="fas fa-times"></i>
                        </button>
                        <input type="hidden" name="variants[${variantIndex}][existing_image]" id="var-existing-${variantIndex}" value="${sharedImgPath}">
                        <input type="hidden" name="variants[${variantIndex}][media_path]" id="var-media-${variantIndex}" value="${sharedImgPath}">
                        <input type="hidden" name="variants[${variantIndex}][remove_image]" id="var-remove-flag-${variantIndex}" value="0">
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-compact p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 25px; height: 25px; border-radius: 5px !important;" onclick="chooseVariantMedia(${variantIndex})" title="Pick from Media Library">
                        <i class="fas fa-images" style="font-size: 10px;"></i>
                    </button>

                    <label class="btn btn-outline-secondary btn-compact p-0 d-inline-flex align-items-center justify-content-center mb-0 shadow-xs" style="width: 25px; height: 25px; border-radius: 5px !important; cursor: pointer;" title="Upload File">
                        <i class="fas fa-upload" style="font-size: 10px;"></i>
                        <input type="file" name="variants[${variantIndex}][image]" id="var-file-${variantIndex}" class="d-none" accept="image/*" onchange="previewVariantFile(this, ${variantIndex})">
                    </label>
                </div>
            </td>
            <td style="min-width: 110px; width: 110px;"><input type="text" name="variants[${variantIndex}][color]" class="form-control" value="${color}" placeholder="Color"></td>
            <td style="min-width: 95px; width: 95px;"><input type="text" name="variants[${variantIndex}][size]" class="form-control" value="${size}" placeholder="Size"></td>
            <td style="min-width: 120px; width: 120px;"><input type="text" name="variants[${variantIndex}][sku]" class="form-control font-monospace bn-digit" value="${autoSku}"></td>
            <td style="min-width: 140px; width: 140px;"><input type="text" name="variants[${variantIndex}][barcode]" class="form-control font-monospace bn-digit" value="${autoBarcode}"></td>
            <td style="min-width: 105px; width: 105px;"><input type="number" step="0.01" name="variants[${variantIndex}][purchase_cost]" class="form-control font-monospace bn-digit" value="${defaultCost}" placeholder="Cost"></td>
            <td style="min-width: 105px; width: 105px;"><input type="number" step="0.01" name="variants[${variantIndex}][regular_price]" class="form-control font-monospace bn-digit" value="${regPrice}" placeholder="Reg. Price"></td>
            <td style="min-width: 105px; width: 105px;"><input type="number" step="0.01" name="variants[${variantIndex}][sale_price]" class="form-control font-monospace bn-digit" value="${salePrice}" placeholder="Sale Price"></td>
            <td style="min-width: 85px; width: 85px;"><input type="number" name="variants[${variantIndex}][stock]" class="form-control font-monospace bn-digit" value="10" placeholder="Stock"></td>
            <td style="min-width: 80px; width: 80px;"><input type="number" name="variants[${variantIndex}][alert_quantity]" class="form-control font-monospace bn-digit" value="5" placeholder="Alert"></td>
            <td class="text-center" style="min-width: 44px; width: 44px;">
                <button type="button" class="btn btn-outline-danger btn-compact p-0 d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 26px; height: 26px; border-radius: 5px !important;" onclick="this.closest('tr').remove()"><i class="fas fa-trash" style="font-size: 10px;"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
        variantIndex++;
    }

    function generateBulkMatrix() {
        const colors = document.getElementById('bulk-colors').value.split(',').map(c => c.trim()).filter(c => c);
        const sizes  = document.getElementById('bulk-sizes').value.split(',').map(s => s.trim()).filter(s => s);

        if (colors.length === 0 && sizes.length === 0) {
            showProductEditNotice('Please enter at least one color or size.', 'Validation');
            return;
        }

        document.getElementById('variant-tbody').innerHTML = '';

        if (colors.length > 0 && sizes.length > 0) {
            colors.forEach(c => {
                sizes.forEach(s => {
                    addVariantRow(c, s);
                });
            });
        } else if (colors.length > 0) {
            colors.forEach(c => addVariantRow(c, ''));
        } else {
            sizes.forEach(s => addVariantRow('', s));
        }
    }

    function updateSeoPreview() {
        const previewTitleEl = document.getElementById('preview-title');
        const previewDescEl  = document.getElementById('preview-desc');
        if (!previewTitleEl || !previewDescEl) return;

        const title = document.getElementById('seo_title')?.value || document.querySelector('input[name="title"]')?.value || 'Product Title';
        const desc  = document.getElementById('meta_desc')?.value || '';
        previewTitleEl.innerText = title + ' - ' + '{{ config('app.name') }}';
        previewDescEl.innerText = desc.substring(0, 160) + (desc.length > 160 ? '...' : '');
    }

    // Categories
    function switchCategoryTab(tab) {
        if (tab === 'all') {
            $('#product-categories-box .wc-box-tab-btn:first').addClass('active');
            $('#product-categories-box .wc-box-tab-btn:last').removeClass('active');
            $('#categoryTreeList li').show();
        } else {
            $('#product-categories-box .wc-box-tab-btn:last').addClass('active');
            $('#product-categories-box .wc-box-tab-btn:first').removeClass('active');
            $('#categoryTreeList li').each(function() {
                if ($(this).find('input[type="checkbox"]:checked').length > 0) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    }

    function toggleNewCategoryForm() {
        $('#inlineNewCategoryForm').toggleClass('d-none');
        if (!$('#inlineNewCategoryForm').hasClass('d-none')) {
            $('#new_cat_name').focus();
        }
    }

    $(document).on('change', '.cat-cb-child', function() {
        if ($(this).is(':checked')) {
            let parentId = $(this).data('parent');$('#cat-p-' + parentId).prop('checked', true);
        }
    });

    function saveNewCategoryAjax() {
        const name = $('#new_cat_name').val().trim();
        const parentId = $('#new_cat_parent').val();
        const btn = $('#btn-save-ajax-cat');
        const loader = $('#cat-ajax-loader');

        if (!name) {
            showProductEditNotice('Please enter a category name.', 'Required Field');
            $('#new_cat_name').focus();
            return;
        }

        btn.prop('disabled', true);
        loader.removeClass('d-none');

        $.ajax({
            url: "{{ route('admin.product.category.quick_store') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                name: name,
                parent_id: parentId
            },
            success: function(response) {
                btn.prop('disabled', false);
                loader.addClass('d-none');

                if (response.success) {
                    $('.empty-cat-note').remove();

                    if (response.type === 'child') {
                        let subTree = $('#sub-tree-' + response.parent_id);
                        subTree.removeClass('d-none');
                        let childHtml = `
                            <li id="tree-child-${response.id}">
                                <label class="d-flex align-items-center gap-2">
                                    <input type="checkbox" name="sub_category_ids[]" value="${response.id}" class="form-check-input cat-cb-child m-0" data-parent="${response.parent_id}" id="cat-c-${response.id}" checked>
                                    <span>${response.name}</span>
                                </label>
                            </li>
                        `;
                        subTree.append(childHtml);
                        $('#cat-p-' + response.parent_id).prop('checked', true);
                    } else {
                        let parentHtml = `
                            <li id="tree-parent-${response.id}">
                                <label class="d-flex align-items-center gap-2">
                                    <input type="checkbox" name="category_ids[]" value="${response.id}" class="form-check-input cat-cb-parent m-0" id="cat-p-${response.id}" checked>
                                    <span>${response.name}</span>
                                </label>
                                <ul class="sub-tree d-none" id="sub-tree-${response.id}"></ul>
                            </li>
                        `;
                        $('#categoryTreeList').append(parentHtml);
                        $('#new_cat_parent').append(`<option value="${response.id}">${response.name}</option>`);
                    }

                    $('#new_cat_name').val('');
                    $('#new_cat_parent').val('');
                    $('#inlineNewCategoryForm').addClass('d-none');
                    let scrollEl = document.getElementById('categoryScrollBox');
                    scrollEl.scrollTop = scrollEl.scrollHeight;
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false);
                loader.addClass('d-none');
                showProductEditNotice(xhr.responseJSON?.message || 'Failed to create category.', 'Error', true);
            }
        });
    }

    // Product Tags
    let currentTags = [];
    function addTagFromInput() {
        const input = document.getElementById('tag_input');
        const raw = input.value.trim();
        if (!raw) return;

        const tags = raw.split(',').map(t => t.trim()).filter(t => t.length > 0);
        tags.forEach(t => appendTagChip(t));
        input.value = '';
    }

    function appendTagChip(tagName) {
        if (!tagName) return;
        const normalized = tagName.toLowerCase();
        if (currentTags.some(t => t.toLowerCase() === normalized)) return;

        currentTags.push(tagName);
        renderTagChips();
    }

    function removeTag(tagName) {
        currentTags = currentTags.filter(t => t.toLowerCase() !== tagName.toLowerCase());
        renderTagChips();
    }

    function renderTagChips() {
        const container = document.getElementById('selected-tags-container');
        container.innerHTML = '';

        currentTags.forEach(tag => {
            const chip = document.createElement('span');
            chip.className = 'wc-tag-chip';
            chip.innerHTML = `
                <span>${tag}</span>
                <button type="button" class="remove-tag-btn" onclick="removeTag('${tag.replace(/'/g, "\\'")}')" title="Remove">&times;</button>
                <input type="hidden" name="tags[]" value="${tag}">
            `;
            container.appendChild(chip);
        });
    }

    function toggleMostUsedTags() {
        const cloud = document.getElementById('most-used-tags-cloud');
        const list = document.getElementById('most-used-tags-list');
        cloud.classList.toggle('d-none');

        if (!cloud.classList.contains('d-none') && list.children.length <= 1) {
            $.ajax({
                url: "{{ route('admin.product.tags.most_used') }}",
                type: "GET",
                success: function(tags) {
                    list.innerHTML = '';
                    if (!tags || tags.length === 0) {
                        list.innerHTML = '<span class="text-muted small">No tags used yet.</span>';
                        return;
                    }

                    tags.forEach(item => {
                        const link = document.createElement('a');
                        link.href = 'javascript:void(0)';
                        link.className = 'badge bg-light text-primary border text-decoration-none p-1 px-2';
                        link.innerText = item.name;
                        link.onclick = function() {
                            appendTagChip(item.name);
                        };
                        list.appendChild(link);
                    });
                },
                error: function() {
                    list.innerHTML = '<span class="text-danger small">Failed to load tags.</span>';
                }
            });
        }
    }

    // Brands
    function switchBrandTab(tab) {
        if (tab === 'all') {
            $('#product-brands-box .wc-box-tab-btn:first').addClass('active');
            $('#product-brands-box .wc-box-tab-btn:last').removeClass('active');
            $('#brandList li').show();
        } else {
            $('#product-brands-box .wc-box-tab-btn:last').addClass('active');
            $('#product-brands-box .wc-box-tab-btn:first').removeClass('active');
            $('#brandList li').each(function() {
                if ($(this).find('input[type="checkbox"]:checked').length > 0) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    }

    function toggleNewBrandForm() {
        $('#inlineNewBrandForm').toggleClass('d-none');
        if (!$('#inlineNewBrandForm').hasClass('d-none')) {
            $('#new_brand_name').focus();
        }
    }

    function saveNewBrandAjax() {
        const name = $('#new_brand_name').val().trim();
        const btn = $('#btn-save-ajax-brand');
        const loader = $('#brand-ajax-loader');

        if (!name) {
            showProductEditNotice('Please enter a brand name.', 'Required Field');
            $('#new_brand_name').focus();
            return;
        }

        btn.prop('disabled', true);
        loader.removeClass('d-none');

        $.ajax({
            url: "{{ route('admin.product.brand.quick_store') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                name: name
            },
            success: function(response) {
                btn.prop('disabled', false);
                loader.addClass('d-none');

                if (response.success) {
                    $('.empty-brand-note').remove();

                    let brandHtml = `
                        <li id="brand-item-${response.id}">
                            <label class="d-flex align-items-center gap-2">
                                <input type="checkbox" name="brand_ids[]" value="${response.id}" class="form-check-input brand-cb m-0" id="brand-chk-${response.id}" checked>
                                <span>${response.name}</span>
                            </label>
                        </li>
                    `;
                    $('#brandList').append(brandHtml);
                    $('#new_brand_name').val('');
                    $('#inlineNewBrandForm').addClass('d-none');

                    let scrollEl = document.getElementById('brandScrollBox');
                    scrollEl.scrollTop = scrollEl.scrollHeight;
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false);
                loader.addClass('d-none');
                showProductEditNotice(xhr.responseJSON?.message || 'Failed to create brand.', 'Error', true);
            }
        });
    }
</script>
@endsection