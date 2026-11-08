@extends('layouts.admin')

@section('title', 'Add New Product - ' . config('app.name'))

@section('admin_content')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

    <style>
        /* =========================================================
           KAIADMIN LITE 1.2.0 — CLASSIC / WHITE CLASSIC
           Product Create Page — Scoped UI Styles
        ========================================================= */
        #productAddRoot {
            --product-primary: var(--bs-primary, #1572e8);
            --product-primary-hover: #1264cc;
            --product-success: var(--bs-success, #31ce36);
            --product-danger: var(--bs-danger, #f25961);
            --product-warning: var(--bs-warning, #ffad46);
            --product-info: var(--bs-info, #48abf7);
            --product-ink: #1f2937;
            --product-text: #344054;
            --product-muted: #6b7280;
            --product-border: #e5e7eb;
            --product-soft-border: #eef0f3;
            --product-surface: #ffffff;
            --product-soft: #f8f9fb;
            --product-radius: 8px;
            color: var(--product-text);
            min-width: 0;
            width: 100%;
            font-size: 13px;
        }

        #productAddRoot *,
        #productFormNoticeModal * {
            box-sizing: border-box;
        }

        #productAddRoot .product-form-card,
        #productAddRoot.product-form-card {
            background: var(--product-surface);
            border: 1px solid var(--product-border);
            border-radius: var(--product-radius) !important;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .04);
        }

        #productAddRoot .btn,
        #productAddRoot .form-control,
        #productAddRoot .form-select,
        #productAddRoot .badge,
        #productAddRoot .input-group-text,
        #productAddRoot .alert,
        #productFormNoticeModal .btn,
        #productFormNoticeModal .modal-content {
            border-radius: 6px;
        }

        #productAddRoot .input-group>.input-group-text {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        #productAddRoot .input-group>.form-control,
        #productAddRoot .input-group>.form-select {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        #productAddRoot .btn-compact,
        #productFormNoticeModal .btn-compact {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            min-height: 34px;
            padding: 6px 12px;
            font-size: 12px;
            line-height: 1.35;
            font-weight: 600;
            white-space: nowrap;
            transition: background-color .18s ease, border-color .18s ease, color .18s ease, box-shadow .18s ease;
        }

        #productAddRoot .btn:focus-visible,
        #productAddRoot button:focus-visible,
        #productAddRoot a:focus-visible {
            outline: 2px solid rgba(21, 114, 232, .35);
            outline-offset: 2px;
        }

        /* Page header */
        #productAddRoot .product-page-header h5 {
            color: var(--product-ink);
            font-size: clamp(17px, 1.5vw, 20px);
            font-weight: 700;
            letter-spacing: -.02em;
        }

        #productAddRoot .product-page-header p {
            color: var(--product-muted);
            font-size: 12.5px;
            line-height: 1.5;
        }

        #productAddRoot .header-top-actions {
            flex-shrink: 0;
        }

        /* Section headings */
        #productAddRoot .form-section-title {
            display: flex;
            align-items: center;
            gap: 9px;
            width: 100%;
            margin: 22px 0 14px;
            color: var(--product-ink);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .015em;
        }

        #productAddRoot .form-section-title::after {
            content: "";
            flex: 1;
            height: 1px;
            margin-left: 4px;
            background: var(--product-border);
        }

        #productAddRoot .form-section-title>span,
        #productAddRoot .form-section-title>div {
            flex-shrink: 0;
        }

        #productAddRoot .form-section-title i {
            font-size: 14px;
        }

        /* Form controls */
        #productAddRoot .form-label {
            display: block;
            margin-bottom: 6px;
            color: #475467;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.4;
        }

        #productAddRoot .form-control,
        #productAddRoot .form-select {
            width: 100%;
            min-height: 38px;
            padding: 8px 11px;
            color: var(--product-ink);
            background-color: #fff;
            border: 1px solid #dfe3e8;
            box-shadow: none;
            font-size: 13px;
            line-height: 1.45;
            transition: border-color .18s ease, box-shadow .18s ease;
        }

        #productAddRoot .form-control::placeholder {
            color: #98a2b3;
            opacity: 1;
        }

        #productAddRoot .form-control:focus,
        #productAddRoot .form-select:focus {
            border-color: var(--product-primary);
            box-shadow: 0 0 0 3px rgba(21, 114, 232, .10);
            outline: 0;
        }

        #productAddRoot .form-control-sm,
        #productAddRoot .form-select-sm {
            min-height: 35px;
            padding: 6px 9px;
            font-size: 12.5px;
        }

        #productAddRoot textarea.form-control {
            min-height: 82px;
            resize: vertical;
        }

        #productAddRoot .form-check {
            min-height: 20px;
        }

        #productAddRoot .form-check-input {
            cursor: pointer;
            border-color: #cbd5e1;
        }

        #productAddRoot .form-check-input:checked {
            background-color: var(--product-primary);
            border-color: var(--product-primary);
        }

        #productAddRoot .form-check-input:focus {
            border-color: var(--product-primary);
            box-shadow: 0 0 0 3px rgba(21, 114, 232, .12);
        }

        #productAddRoot .form-check-label {
            color: var(--product-text);
            font-size: 12px;
        }

        #productAddRoot .input-group .btn {
            min-height: 35px;
        }

        #productAddRoot .bg-light {
            background-color: var(--product-soft) !important;
        }

        #productAddRoot .border {
            border-color: var(--product-border) !important;
        }

        #productAddRoot .alert {
            border: 1px solid transparent;
            font-size: 12.5px;
        }

        /* Image upload */
        #productAddRoot .img-upload-box {
            padding: 20px 14px;
            text-align: center;
            background: #fafbfc;
            border: 1.5px dashed #cbd5e1;
            border-radius: 8px !important;
            cursor: pointer;
            transition: border-color .18s ease, background-color .18s ease;
        }

        #productAddRoot .img-upload-box:hover {
            background: #f1f6ff;
            border-color: var(--product-primary);
        }

        #productAddRoot .img-upload-box i {
            font-size: 25px;
        }

        #productAddRoot .img-upload-box p {
            font-size: 12px;
            font-weight: 600;
        }

        #productAddRoot .preview-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
            margin-top: 12px;
        }

        #productAddRoot .preview-item {
            position: relative;
            width: 72px;
            height: 72px;
            overflow: hidden;
            background: #fff;
            border: 1px solid var(--product-border);
            border-radius: 7px;
        }

        #productAddRoot .preview-item img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #productAddRoot .preview-item .remove-btn {
            position: absolute;
            top: 3px;
            right: 3px;
            z-index: 5;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 21px;
            height: 21px;
            padding: 0;
            color: #fff;
            background: rgba(220, 53, 69, .94);
            border: 0;
            border-radius: 5px;
            font-size: 10px;
            cursor: pointer;
        }

        #productAddRoot .thumb-preview-box {
            position: relative;
            display: inline-block;
        }

        #productAddRoot .thumb-preview-box .remove-thumb-btn {
            position: absolute;
            top: -6px;
            right: -6px;
            z-index: 5;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            padding: 0;
            color: #fff;
            background: var(--product-danger);
            border: 2px solid #fff;
            border-radius: 50%;
            font-size: 9px;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .15);
        }

        /* Variation table */
        #productAddRoot .variant-table-container {
            width: 100%;
            overflow-x: auto !important;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8f9fb;
            background: #fff;
            border: 1px solid var(--product-border);
            border-radius: 8px !important;
        }

        #productAddRoot .variant-table-container::-webkit-scrollbar {
            height: 7px;
        }

        #productAddRoot .variant-table-container::-webkit-scrollbar-track {
            background: #f8f9fb;
        }

        #productAddRoot .variant-table-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 5px;
        }

        #productAddRoot .variant-table {
            width: 100%;
            min-width: 1160px !important;
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        #productAddRoot .variant-table th {
            padding: 10px 9px;
            color: #475467;
            background: #f8f9fb;
            border-bottom: 1px solid var(--product-border);
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .035em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        #productAddRoot .variant-table td {
            padding: 7px 8px;
            color: var(--product-text);
            background: #fff;
            border-bottom: 1px solid #f0f1f3;
            font-size: 12px;
            vertical-align: middle;
        }

        #productAddRoot .variant-table tbody tr:last-child td {
            border-bottom: 0;
        }

        #productAddRoot .variant-table tbody tr:hover td {
            background: #fafbfd;
        }

        #productAddRoot .variant-table .form-control {
            width: 100% !important;
            min-width: 0;
            min-height: 34px !important;
            height: 34px !important;
            padding: 5px 7px !important;
            border: 1px solid #dfe3e8 !important;
            box-shadow: none !important;
            font-size: 12px !important;
        }

        #productAddRoot .variant-table .form-control:focus {
            border-color: var(--product-primary) !important;
            box-shadow: 0 0 0 2px rgba(21, 114, 232, .10) !important;
        }

        /* Download table */
        #productAddRoot #download-files-table {
            min-width: 650px;
            font-size: 12px;
        }

        #productAddRoot #download-files-table th {
            padding: 9px 10px;
            color: #475467;
            background: #f8f9fb;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        #productAddRoot #download-files-table td {
            padding: 7px;
            vertical-align: middle;
        }

        #productAddRoot .table-responsive {
            border-color: var(--product-border) !important;
            border-radius: 8px;
            -webkit-overflow-scrolling: touch;
        }

        /* Sidebar metaboxes */
        #productAddRoot .wc-metabox {
            margin-bottom: 16px;
            overflow: hidden;
            background: #fff;
            border: 1px solid var(--product-border);
            border-radius: 8px !important;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
        }

        #productAddRoot .wc-metabox-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            min-height: 44px;
            padding: 11px 14px;
            color: var(--product-ink);
            background: #fff;
            border-bottom: 1px solid var(--product-soft-border);
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
        }

        #productAddRoot .wc-metabox-header i {
            transition: transform .2s ease;
        }

        #productAddRoot .wc-metabox-body {
            padding: 14px;
            background: #fff;
        }

        #productAddRoot .wc-metabox-body hr {
            border-color: var(--product-border);
            opacity: 1;
        }

        /* Category / brand tabs */
        #productAddRoot .wc-box-tabs {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 10px;
            border-bottom: 1px solid var(--product-border);
        }

        #productAddRoot .wc-box-tab-btn {
            position: relative;
            padding: 0 0 9px;
            color: var(--product-muted);
            background: transparent;
            border: 0;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            transition: color .18s ease;
        }

        #productAddRoot .wc-box-tab-btn:hover,
        #productAddRoot .wc-box-tab-btn.active {
            color: var(--product-primary);
        }

        #productAddRoot .wc-box-tab-btn.active::after {
            position: absolute;
            right: 0;
            bottom: -1px;
            left: 0;
            height: 2px;
            background: var(--product-primary);
            content: "";
        }

        #productAddRoot .wc-scroll-box {
            max-height: 220px;
            overflow-y: auto;
            padding: 10px;
            background: #fff;
            border: 1px solid var(--product-border);
            border-radius: 7px !important;
            scrollbar-width: thin;
        }

        #productAddRoot .wc-item-list {
            padding-left: 0;
            margin-bottom: 0;
            list-style: none;
        }

        #productAddRoot .wc-item-list li {
            margin-bottom: 7px;
            color: var(--product-text);
            font-size: 12.5px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        #productAddRoot .wc-item-list li:last-child {
            margin-bottom: 0;
        }

        #productAddRoot .wc-item-list .sub-tree {
            padding-left: 17px;
            margin-top: 7px;
            margin-left: 5px;
            list-style: none;
            border-left: 1px dashed #cbd5e1;
        }

        #productAddRoot .wc-item-list label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 0;
            cursor: pointer;
            user-select: none;
        }

        #productAddRoot .wc-item-list .form-check-input {
            flex: 0 0 auto;
            margin-top: 0;
        }

        #productAddRoot .btn-add-item-toggle {
            display: inline-block;
            margin-top: 10px;
            color: var(--product-primary);
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        #productAddRoot .btn-add-item-toggle:hover {
            color: var(--product-primary-hover);
            text-decoration: underline;
        }

        /* Tag chips */
        #productAddRoot .wc-tag-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            max-width: 100%;
            padding: 4px 8px;
            margin: 0 4px 4px 0;
            color: #344054;
            background: #f3f5f8;
            border: 1px solid #e3e7ed;
            border-radius: 5px !important;
            font-size: 11.5px;
            overflow-wrap: anywhere;
        }

        #productAddRoot .wc-tag-chip .remove-tag-btn {
            padding: 0;
            color: var(--product-danger);
            background: transparent;
            border: 0;
            font-size: 14px;
            font-weight: 700;
            line-height: 1;
            cursor: pointer;
        }

        /* Digital product panels */
        #productAddRoot .digital-box-highlight {
            padding: 15px;
            background: #f8faff;
            border: 1px solid #e1e9f5;
            border-radius: 8px !important;
        }

        #productAddRoot .digital-box-highlight h6 {
            color: var(--product-ink);
            font-size: 13px;
            font-weight: 700;
        }

        /* Summernote */
        #productAddRoot .note-editor.note-frame {
            overflow: hidden;
            border: 1px solid #dfe3e8;
            border-radius: 7px;
        }

        #productAddRoot .note-editor .note-toolbar {
            background: #f8f9fb;
            border-bottom: 1px solid var(--product-border);
        }

        #productAddRoot .note-editor .note-editing-area {
            background: #fff;
        }

        #productAddRoot .note-editor .note-editable {
            color: var(--product-text);
            font-size: 13px;
            line-height: 1.65;
        }

        #productAddRoot .note-editor .note-btn {
            border-radius: 5px;
        }

        /* Notice modal */
        #productFormNoticeModal .modal-content {
            border: 1px solid var(--product-border);
            border-radius: 10px !important;
        }

        #productFormNoticeModal .modal-body {
            padding: 18px 14px;
        }

        /* Safe numeric typography */
        #productAddRoot .bn-digit,
        #productAddRoot .font-monospace {
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }

        #productAddRoot .text-muted {
            color: #7b8494 !important;
        }

        #productAddRoot .small {
            line-height: 1.5;
        }

        #productAddRoot img {
            max-width: 100%;
        }

        #productAddRoot .row>* {
            min-width: 0;
        }

        /* Large screens */
        @media (min-width: 1200px) {
            #productAddRoot .wc-metabox-body {
                padding: 15px;
            }
        }

        /* Tablet and below */
        @media (max-width: 991.98px) {
            #productAddRoot .form-section-title {
                margin-top: 20px;
            }

            #productAddRoot .wc-metabox {
                margin-bottom: 13px;
            }
        }

        /* Mobile */
        @media (max-width: 767.98px) {
            #productAddRoot.product-form-card {
                padding: 14px !important;
                margin-bottom: 18px !important;
            }

            #productAddRoot .product-page-header {
                align-items: stretch !important;
            }

            #productAddRoot .header-top-actions {
                width: 100%;
            }

            #productAddRoot .header-top-actions .btn-compact {
                width: 100%;
                min-height: 38px;
            }

            #productAddRoot .form-section-title {
                gap: 7px;
                margin: 19px 0 12px;
                font-size: 12.5px;
            }

            #productAddRoot .form-label {
                font-size: 11.8px;
            }

            #productAddRoot .form-control,
            #productAddRoot .form-select {
                min-height: 39px;
            }

            #productAddRoot .wc-metabox-body {
                padding: 12px;
            }

            #productAddRoot .digital-box-highlight {
                padding: 12px;
            }

            #productAddRoot .variant-table-container {
                max-width: 100%;
            }

            #productAddRoot .preview-item {
                width: 64px;
                height: 64px;
            }
        }

        /* Small mobile */
        @media (max-width: 420px) {
            #productAddRoot.product-form-card {
                padding: 11px !important;
            }

            #productAddRoot .product-page-header h5 {
                font-size: 16px;
            }

            #productAddRoot .product-page-header p {
                font-size: 11.5px;
            }

            #productAddRoot .wc-metabox-header {
                min-height: 42px;
                padding: 10px 11px;
            }

            #productAddRoot .wc-metabox-body {
                padding: 10px;
            }

            #productAddRoot .form-section-title {
                font-size: 11.8px;
            }

            #productAddRoot .wc-box-tabs {
                gap: 12px;
            }

            #productAddRoot .wc-box-tab-btn {
                font-size: 11px;
            }
        }
    </style>

    <div class="product-form-card p-3 p-md-4 mb-4" id="productAddRoot">
        <!-- Header -->
        <div
            class="product-page-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 pb-3 border-bottom">
            <div>
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center">
                    <i class="fas fa-plus-circle text-primary me-2"></i>
                    <span>Add New Product</span>
                </h5>
                <p class="text-muted d-block mt-1 mb-0">Manage product details, pricing, variations, media and inventory.
                </p>
            </div>
            <div class="header-top-actions d-flex gap-2">
                <a href="{{ route('admin.product.index') }}" class="btn btn-outline-secondary btn-compact shadow-xs">
                    <i class="fas fa-arrow-left me-1"></i>
                    <span>Back to Products</span>
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2 px-3 small mb-3" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.product.store') }}" method="POST" enctype="multipart/form-data" id="productAddForm">
            @csrf

            <div class="row g-3 g-md-4">
                <!-- Left Main Column -->
                <div class="col-12 col-lg-8">
                    <!-- 1. Title & Descriptions -->
                    <div class="form-section-title"><i class="fas fa-info-circle text-info"></i> Product Information</div>
                    <div class="mb-3">
                        <label class="form-label">Product Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}"
                            placeholder="Enter product title..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Full Description</label>
                        <textarea name="description" id="summernote-desc"
                            class="form-control">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Short Description</label>
                        <textarea name="short_description" class="form-control" rows="3"
                            placeholder="Brief summary shown beside the main image...">{{ old('short_description') }}</textarea>
                    </div>

                    <!-- 2. Product Data & Pricing -->
                    <div class="form-section-title d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-tags text-success"></i> Product Data</span>
                        <div class="d-flex align-items-center gap-3">
                            <div class="form-check m-0">
                                <input class="form-check-input" type="checkbox" name="is_virtual" value="1" id="cb-virtual"
                                    onchange="toggleDigitalOptions()" style="cursor: pointer;">
                                <label class="form-check-label small fw-semibold text-dark" for="cb-virtual"
                                    style="cursor: pointer;">Virtual</label>
                            </div>
                            <div class="form-check m-0">
                                <input class="form-check-input" type="checkbox" name="is_downloadable" value="1"
                                    id="cb-downloadable" onchange="toggleDigitalOptions()" style="cursor: pointer;">
                                <label class="form-check-label small fw-semibold text-dark" for="cb-downloadable"
                                    style="cursor: pointer;">Downloadable</label>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-light border mb-4" style="border-radius: 6px;">
                        <div class="row g-2 g-md-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Product Type</label>
                                <select name="product_type" id="product_type_select"
                                    class="form-select form-select-sm fw-semibold"
                                    onchange="handleProductTypeChange(this.value)">
                                    <option value="physical">Physical Product</option>
                                    <option value="virtual">Virtual Product</option>
                                    <option value="downloadable">Downloadable Product</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6" id="stock-field-box">
                                <label class="form-label" id="base-stock-label">Base Stock <span
                                        class="text-danger">*</span></label>
                                <input type="number" name="stock" id="base-stock-input"
                                    class="form-control form-control-sm bn-digit font-monospace"
                                    value="{{ old('stock', 10) }}" required>
                                <small class="text-primary d-none fw-semibold" id="base-stock-help"
                                    style="font-size: 11px;">
                                    <i class="fas fa-info-circle me-1"></i> Auto-synced from the sum of all variants.
                                </small>
                            </div>

                            <div class="col-6 col-md-3">
                                <label class="form-label">Regular Price (৳) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="regular_price" id="reg-price"
                                    class="form-control form-control-sm bn-digit font-monospace"
                                    value="{{ old('regular_price', 0) }}" required>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">Sale Price (৳)</label>
                                <input type="number" step="0.01" name="sale_price" id="sale-price"
                                    class="form-control form-control-sm bn-digit font-monospace"
                                    value="{{ old('sale_price') }}">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">Purchase Cost (৳)</label>
                                <input type="number" step="0.01" name="purchase_cost" id="purchase-cost-input"
                                    class="form-control form-control-sm bn-digit font-monospace"
                                    value="{{ old('purchase_cost', old('purchase_price')) }}" placeholder="Cost per unit">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">Discount (%)</label>
                                <input type="number" step="0.01" name="discount_percentage" id="disc-pct"
                                    class="form-control form-control-sm bg-white bn-digit font-monospace" readonly
                                    placeholder="Auto calc.">
                            </div>

                            <!-- Optional VAT Settings -->
                            <div class="col-12 col-md-6">
                                <label class="form-label">Product VAT Type (Optional)</label>
                                <select name="vat_type" id="vat-type" class="form-select form-select-sm">
                                    <option value="percent" {{ old('vat_type', 'percent') === 'percent' ? 'selected' : '' }}>
                                        Percentage (%)</option>
                                    <option value="flat" {{ old('vat_type') === 'flat' ? 'selected' : '' }}>Flat Amount (৳)
                                    </option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Product VAT Amount (Optional)</label>
                                <input type="number" step="0.01" min="0" name="vat_amount" id="vat-amount"
                                    class="form-control form-control-sm bn-digit font-monospace"
                                    value="{{ old('vat_amount') }}" placeholder="Leave blank for 0 VAT">
                                <small class="text-muted d-block mt-1" style="font-size: 11px;">Leave blank for 0 VAT. Enter
                                    a percentage or flat amount to include VAT in order calculations.</small>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label">Product SKU</label>
                                <input type="text" name="sku" class="form-control form-control-sm font-monospace bn-digit"
                                    value="{{ old('sku') }}" placeholder="SKU-001">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Product Code</label>
                                <input type="text" name="product_code"
                                    class="form-control form-control-sm font-monospace bn-digit"
                                    value="{{ old('product_code') }}" placeholder="P-123">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Barcode</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" name="barcode" id="barcode-input"
                                        class="form-control font-monospace bn-digit" value="{{ old('barcode') }}">
                                    <button class="btn btn-warning btn-compact text-dark fw-bold" type="button"
                                        onclick="generateBarcode()">Generate</button>
                                </div>
                            </div>

                            <div class="col-12" id="backorder-field-box">
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" name="allow_out_of_stock_order"
                                        value="1" id="switch-backorder" style="cursor: pointer;">
                                    <label class="form-check-label small fw-semibold text-dark" for="switch-backorder"
                                        style="cursor: pointer;">Allow Backorders (Order when out of stock)</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Downloadable Files Section -->
                    <div id="downloadable-files-section" class="digital-box-highlight mb-4 d-none">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
                            <i class="fas fa-file-download text-primary me-2"></i>
                            <span>Downloadable Files &amp; Settings</span>
                        </h6>
                        <div class="table-responsive border bg-white mb-3" style="border-radius: 5px;">
                            <table class="table table-bordered table-sm align-middle mb-0" id="download-files-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width: 180px;">File Name</th>
                                        <th>Upload File (Secure Storage)</th>
                                        <th>External Download URL</th>
                                        <th class="text-center" style="width: 40px;"><i class="fas fa-trash"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="download-files-tbody"></tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-compact mb-3"
                            onclick="addDownloadFileRow()">
                            <i class="fas fa-plus me-1"></i> Add File
                        </button>

                        <div class="row g-2 g-md-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Download Limit</label>
                                <input type="number" name="download_limit"
                                    class="form-control form-control-sm bn-digit font-monospace"
                                    placeholder="Unlimited (leave blank)">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Download Expiry (Days)</label>
                                <input type="number" name="download_expiry"
                                    class="form-control form-control-sm bn-digit font-monospace"
                                    placeholder="Never (leave blank)">
                            </div>
                        </div>
                    </div>

                    <!-- 4. Virtual Product Section -->
                    <div id="virtual-product-section" class="digital-box-highlight mb-4 d-none">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
                            <i class="fas fa-key text-warning me-2"></i>
                            <span>Virtual Product &amp; Software Licenses</span>
                        </h6>
                        <div class="row g-2 g-md-3">
                            <div class="col-12">
                                <label class="form-label">Pre-generated License Keys (One per line)</label>
                                <textarea name="license_keys_input"
                                    class="form-control form-control-sm font-monospace bn-digit" rows="4"
                                    placeholder="ABCD-1234-EFGH-5678&#10;WXYZ-9876-LMNO-5432"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Customer Instructions / Access Note</label>
                                <textarea name="access_instructions" class="form-control form-control-sm" rows="3"
                                    placeholder="Explain how customers can redeem a license, access a course or sign in to an account..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Advanced Variations Matrix -->
                    <div class="form-section-title d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-sitemap text-warning"></i> Product Variations</span>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="has-variants-toggle"
                                onchange="toggleVariantSection()" style="cursor: pointer;">
                            <label class="form-check-label small fw-semibold text-dark" for="has-variants-toggle"
                                style="cursor: pointer;">Enable Variations</label>
                        </div>
                    </div>

                    <div id="variant-section" class="mb-4 d-none">
                        <input type="hidden" name="product_struct" id="product_struct" value="simple">
                        <div class="p-3 bg-light border mb-3" style="border-radius: 6px;">
                            <div class="row g-2 align-items-center">
                                <div class="col-12 col-md-5">
                                    <label class="form-label text-muted">Colors (comma separated)</label>
                                    <input type="text" id="bulk-colors" class="form-control form-control-sm"
                                        placeholder="Red, Blue, Black">
                                </div>
                                <div class="col-12 col-md-5">
                                    <label class="form-label text-muted">Sizes / Specs (comma separated)</label>
                                    <input type="text" id="bulk-sizes" class="form-control form-control-sm"
                                        placeholder="M, L, XL or 41, 42">
                                </div>
                                <div class="col-12 col-md-2 pt-md-4">
                                    <button type="button" class="btn btn-dark btn-compact w-100 shadow-xs"
                                        onclick="generateBulkMatrix()">Generate All</button>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2 text-muted small">
                            <span><i class="fas fa-arrows-alt-h me-1 text-primary"></i> Scroll horizontally to view all
                                fields</span>
                            <span class="bn-digit">Minimum 1160px width</span>
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
                                            <button type="button"
                                                class="btn btn-sm btn-primary p-0 d-inline-flex align-items-center justify-content-center shadow-xs"
                                                style="width: 24px; height: 24px;" onclick="addVariantRow()"
                                                title="Add Blank Row"><i class="fas fa-plus"
                                                    style="font-size: 10px;"></i></button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="variant-tbody"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 6. Physical Dimensions & Shipping -->
                    <div id="shipping-dimensions-section">
                        <div class="form-section-title"><i class="fas fa-truck text-secondary"></i> Shipping Dimensions
                            &amp; Video</div>
                        <div class="row g-2 g-md-3 mb-4">
                            <div class="col-6 col-md-3">
                                <label class="form-label">Weight (KG)</label>
                                <input type="number" step="0.01" name="weight"
                                    class="form-control form-control-sm bn-digit font-monospace"
                                    value="{{ old('weight') }}">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">Length (cm)</label>
                                <input type="number" step="0.01" name="length"
                                    class="form-control form-control-sm bn-digit font-monospace"
                                    value="{{ old('length') }}">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">Width (cm)</label>
                                <input type="number" step="0.01" name="width"
                                    class="form-control form-control-sm bn-digit font-monospace" value="{{ old('width') }}">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">Height (cm)</label>
                                <input type="number" step="0.01" name="height"
                                    class="form-control form-control-sm bn-digit font-monospace"
                                    value="{{ old('height') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">YouTube Video URL</label>
                                <input type="url" name="video_url" class="form-control form-control-sm font-monospace"
                                    value="{{ old('video_url') }}" placeholder="https://youtube.com/watch?v=...">
                            </div>
                        </div>
                    </div>

                    <!-- 7. Advanced SEO Management -->
                    <div class="form-section-title"><i class="fas fa-search text-success"></i> SEO Settings</div>
                    <div class="p-3 bg-light border mb-4" style="border-radius: 6px;">
                        <div class="row g-2 g-md-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">SEO Meta Title</label>
                                <input type="text" name="seo_title" id="seo_title" class="form-control form-control-sm"
                                    value="{{ old('seo_title') }}" placeholder="Optimized title"
                                    oninput="updateSeoPreview()">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Focus Keyword</label>
                                <input type="text" name="focus_keyphrase" class="form-control form-control-sm"
                                    value="{{ old('focus_keyphrase') }}" placeholder="Software license, PDF book">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Canonical URL</label>
                                <input type="url" name="canonical_url" class="form-control form-control-sm font-monospace"
                                    value="{{ old('canonical_url') }}" placeholder="{{ url('/') }}/custom-url">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Meta Description</label>
                                <textarea name="meta_description" id="meta_desc" class="form-control form-control-sm"
                                    rows="3" oninput="updateSeoPreview()">{{ old('meta_description') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar Column -->
                <div class="col-12 col-lg-4">
                    <!-- 1. Publish Box -->
                    <div class="wc-metabox">
                        <div class="wc-metabox-header" data-bs-toggle="collapse" data-bs-target="#mbPublish"
                            aria-expanded="true">
                            <span>Publish</span>
                            <i class="fas fa-chevron-down small text-muted"></i>
                        </div>
                        <div class="collapse show" id="mbPublish">
                            <div class="wc-metabox-body">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="is_featured" value="1"
                                        id="switch-featured" style="cursor: pointer;">
                                    <label class="form-check-label fw-semibold small text-dark" for="switch-featured"
                                        style="cursor: pointer;">Featured Product</label>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <a href="{{ route('admin.product.index') }}"
                                        class="btn btn-light border btn-compact">Discard</a>
                                    <button type="submit" class="btn btn-primary btn-compact px-3 shadow-xs">Publish
                                        Product</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Branch & Sales Channels Box -->
                    <div class="wc-metabox" id="branch-availability-box">
                        <div class="wc-metabox-header" data-bs-toggle="collapse" data-bs-target="#mbBranchAvailability"
                            aria-expanded="true">
                            <span>Branch &amp; Sales Channels</span>
                            <i class="fas fa-chevron-down small text-muted"></i>
                        </div>
                        <div class="collapse show" id="mbBranchAvailability">
                            <div class="wc-metabox-body">
                                <input type="hidden" name="has_branch_controls" value="1">

                                <!-- Online Storefront Availability -->
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="is_sellable_online" value="1"
                                        id="switch-sellable-online" checked style="cursor: pointer;"
                                        onchange="handleOnlineSwitchChange(this)">
                                    <label class="form-check-label fw-semibold small text-dark" for="switch-sellable-online"
                                        style="cursor: pointer;">
                                        Sellable on Online Store
                                    </label>
                                    <small class="text-muted d-block mt-0.5" style="font-size: 11px;">
                                        Uncheck to hide this product completely from website/online shop.
                                    </small>
                                </div>

                                <hr class="my-2">

                                <!-- Branch Availability Mode -->
                                <div class="mb-2">
                                    <label class="form-label small fw-bold text-muted mb-1">Branch Availability Mode</label>
                                    <select name="branch_availability_mode" id="branch_availability_mode_select"
                                        class="form-select form-select-sm" onchange="handleBranchModeChange(this.value)">
                                        <option value="all" selected>All Branches (Default)</option>
                                        <option value="selected">Selected Branches Only</option>
                                        <option value="main_only">Main / Head Office Only</option>
                                        <option value="restricted">Restricted (Strict Assignment)</option>
                                        <option value="pos_only">POS Terminal Only (No Online)</option>
                                        <option value="branch_only">Branch Only (In-Store Exclusive)</option>
                                    </select>
                                </div>

                                <!-- Specific Branches Checkbox List -->
                                <div id="branch-selection-container" class="mt-2 d-none">
                                    <label class="form-label small fw-bold text-muted mb-1">Select Authorized
                                        Branches</label>
                                    <div class="wc-scroll-box" style="max-height: 180px;">
                                        <ul class="wc-item-list" id="branchList">
                                            @forelse($branches ?? [] as $br)
                                                <li id="branch-item-{{ $br->id }}">
                                                    <label class="d-flex align-items-center gap-2">
                                                        <input type="checkbox" name="branch_ids[]" value="{{ $br->id }}"
                                                            class="form-check-input branch-cb m-0"
                                                            id="branch-chk-{{ $br->id }}">
                                                        <span>{{ $br->name }} <small
                                                                class="text-muted">({{ $br->code ?? ($br->branch_code ?? 'BR-' . $br->id) }})</small></span>
                                                    </label>
                                                </li>
                                            @empty
                                                <li class="text-muted small">No active branches found.</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                    <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                        Check the branches that are authorized to stock or sell this item.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Product Categories Box -->
                    <div class="wc-metabox" id="product-categories-box">
                        <div class="wc-metabox-header" data-bs-toggle="collapse" data-bs-target="#mbCategories"
                            aria-expanded="true">
                            <span>Product Categories</span>
                            <i class="fas fa-chevron-down small text-muted"></i>
                        </div>
                        <div class="collapse show" id="mbCategories">
                            <div class="wc-metabox-body">
                                <div class="wc-box-tabs">
                                    <button type="button" class="wc-box-tab-btn active"
                                        onclick="switchCategoryTab('all')">All Categories</button>
                                    <button type="button" class="wc-box-tab-btn"
                                        onclick="switchCategoryTab('most-used')">Most Used</button>
                                </div>

                                <div class="wc-scroll-box" id="categoryScrollBox">
                                    <ul class="wc-item-list" id="categoryTreeList">
                                        @forelse($categories as $cat)
                                            @php
                                                $childCats = $subCategories->where('category_id', $cat->id);
                                            @endphp
                                            <li id="tree-parent-{{ $cat->id }}">
                                                <label class="d-flex align-items-center gap-2">
                                                    <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}"
                                                        class="form-check-input cat-cb-parent m-0" id="cat-p-${{ $cat->id }}">
                                                    <span>{{ $cat->name }}</span>
                                                </label>

                                                @if($childCats->count() > 0)
                                                    <ul class="sub-tree" id="sub-tree-{{ $cat->id }}">
                                                        @foreach($childCats as $child)
                                                            <li id="tree-child-{{ $child->id }}">
                                                                <label class="d-flex align-items-center gap-2">
                                                                    <input type="checkbox" name="sub_category_ids[]"
                                                                        value="{{ $child->id }}"
                                                                        class="form-check-input cat-cb-child m-0"
                                                                        data-parent="{{ $cat->id }}" id="cat-c-{{ $child->id }}">
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
                                            <li class="text-muted small empty-cat-note">No categories found. Add your first
                                                category below.</li>
                                        @endforelse
                                    </ul>
                                </div>

                                <a class="btn-add-item-toggle" onclick="toggleNewCategoryForm()">+ Add New Category</a>

                                <div id="inlineNewCategoryForm" class="mt-2 pt-2 border-top d-none">
                                    <div class="mb-2">
                                        <input type="text" id="new_cat_name" class="form-control form-control-sm"
                                            placeholder="Category name..." autocomplete="off">
                                    </div>
                                    <div class="mb-2">
                                        <select id="new_cat_parent" class="form-select form-select-sm">
                                            <option value="">— Parent Category —</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <button type="button" class="btn btn-outline-secondary btn-compact shadow-xs"
                                            id="btn-save-ajax-cat" onclick="saveNewCategoryAjax()">
                                            Add Category
                                        </button>
                                        <span id="cat-ajax-loader"
                                            class="spinner-border spinner-border-sm text-primary d-none"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Product Tags Box -->
                    <div class="wc-metabox" id="product-tags-box">
                        <div class="wc-metabox-header" data-bs-toggle="collapse" data-bs-target="#mbTags"
                            aria-expanded="true">
                            <span>Product Tags</span>
                            <i class="fas fa-chevron-down small text-muted"></i>
                        </div>
                        <div class="collapse show" id="mbTags">
                            <div class="wc-metabox-body">
                                <div class="d-flex gap-2 mb-1">
                                    <input type="text" id="tag_input" class="form-control form-control-sm"
                                        placeholder="Cotton, Software, License" autocomplete="off">
                                    <button type="button" class="btn btn-outline-secondary btn-compact shadow-xs"
                                        onclick="addTagFromInput()">Add</button>
                                </div>
                                <p class="text-muted small mb-2" style="font-size: 11px;">Separate tags with commas.</p>

                                <div id="selected-tags-container" class="d-flex flex-wrap gap-1 mb-2"></div>

                                <a href="javascript:void(0)" class="btn-add-item-toggle mt-1" id="btn-toggle-most-used"
                                    onclick="toggleMostUsedTags()">Choose from Most Used Tags</a>
                                <div id="most-used-tags-cloud" class="mt-2 pt-2 border-top d-none" style="font-size: 12px;">
                                    <div id="most-used-tags-list" class="d-flex flex-wrap gap-1">
                                        <span class="text-muted small">Loading tags...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Product Brands Box -->
                    <div class="wc-metabox" id="product-brands-box">
                        <div class="wc-metabox-header" data-bs-toggle="collapse" data-bs-target="#mbBrands"
                            aria-expanded="true">
                            <span>Product Brands</span>
                            <i class="fas fa-chevron-down small text-muted"></i>
                        </div>
                        <div class="collapse show" id="mbBrands">
                            <div class="wc-metabox-body">
                                <div class="wc-box-tabs">
                                    <button type="button" class="wc-box-tab-btn active" onclick="switchBrandTab('all')">All
                                        Brands</button>
                                    <button type="button" class="wc-box-tab-btn" onclick="switchBrandTab('most-used')">Most
                                        Used</button>
                                </div>

                                <div class="wc-scroll-box" id="brandScrollBox">
                                    <ul class="wc-item-list" id="brandList">
                                        @forelse($brands as $brand)
                                            <li id="brand-item-{{ $brand->id }}">
                                                <label class="d-flex align-items-center gap-2">
                                                    <input type="checkbox" name="brand_ids[]" value="{{ $brand->id }}"
                                                        class="form-check-input brand-cb m-0" id="brand-chk-{{ $brand->id }}">
                                                    <span>{{ $brand->name }}</span>
                                                </label>
                                            </li>
                                        @empty
                                            <li class="text-muted small empty-brand-note">No brands found. Add your first brand
                                                below.</li>
                                        @endforelse
                                    </ul>
                                </div>

                                <a class="btn-add-item-toggle" onclick="toggleNewBrandForm()">+ Add New Brand</a>

                                <div id="inlineNewBrandForm" class="mt-2 pt-2 border-top d-none">
                                    <div class="mb-2">
                                        <input type="text" id="new_brand_name" class="form-control form-control-sm"
                                            placeholder="Brand name..." autocomplete="off">
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <button type="button" class="btn btn-outline-secondary btn-compact shadow-xs"
                                            id="btn-save-ajax-brand" onclick="saveNewBrandAjax()">
                                            Add Brand
                                        </button>
                                        <span id="brand-ajax-loader"
                                            class="spinner-border spinner-border-sm text-primary d-none"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 6. Product Image Box -->
                    <div class="wc-metabox">
                        <div class="wc-metabox-header" data-bs-toggle="collapse" data-bs-target="#mbImage"
                            aria-expanded="true">
                            <span>Product Image</span>
                            <i class="fas fa-chevron-down small text-muted"></i>
                        </div>
                        <div class="collapse show" id="mbImage">
                            <div class="wc-metabox-body text-center">
                                <input type="hidden" name="thumbnail_media" id="thumbnail-media-input" value="">

                                <div class="d-flex flex-column gap-2 mb-2">
                                    <button type="button" class="btn btn-outline-primary btn-compact w-100 shadow-xs"
                                        onclick="chooseProductThumbnailMedia()">
                                        <i class="fas fa-images me-1"></i> Choose from Media Library
                                    </button>
                                    <span class="small text-muted" style="font-size: 11px;">Or upload from your
                                        device</span>
                                </div>

                                <div class="img-upload-box" onclick="document.getElementById('thumbnail-input').click()">
                                    <i class="fas fa-cloud-upload-alt text-muted mb-1"></i>
                                    <p class="small text-muted mb-0">Upload Image</p>
                                    <input type="file" name="thumbnail" id="thumbnail-input" class="d-none" accept="image/*"
                                        onchange="previewSingleImage(this, 'thumb-preview')">
                                </div>
                                <div id="thumb-preview" class="mt-2 text-center d-none">
                                    <div class="thumb-preview-box">
                                        <img id="thumb-preview-img" src="" class="img-thumbnail shadow-xs"
                                            style="max-height: 120px; border-radius: 5px;">
                                        <button type="button" class="remove-thumb-btn"
                                            onclick="clearThumbnail('thumbnail-input', 'thumb-preview')"
                                            title="Remove Image">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 7. Product Gallery Box -->
                    <div class="wc-metabox">
                        <div class="wc-metabox-header" data-bs-toggle="collapse" data-bs-target="#mbGallery"
                            aria-expanded="true">
                            <span>Product Gallery</span>
                            <i class="fas fa-chevron-down small text-muted"></i>
                        </div>
                        <div class="collapse show" id="mbGallery">
                            <div class="wc-metabox-body">
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                    <button type="button" class="btn btn-outline-primary btn-compact shadow-xs px-3"
                                        onclick="chooseGalleryMedia()">
                                        <i class="fas fa-images me-1"></i> Add from Media Library
                                    </button>
                                    <button type="button" id="clear-all-gallery-btn"
                                        class="btn btn-link text-danger text-decoration-none small p-0 d-none"
                                        onclick="clearAllGalleryImages()">
                                        Clear All
                                    </button>
                                </div>

                                <div class="text-center my-1">
                                    <label class="btn btn-link text-decoration-none small p-0 text-muted"
                                        style="font-size: 11px; cursor: pointer;">
                                        <i class="fas fa-upload me-1"></i> Or upload files from your device
                                        <input type="file" name="gallery_images[]" id="gallery-input" class="d-none"
                                            accept="image/*" multiple onchange="previewGalleryImages(this)">
                                    </label>
                                </div>

                                <div id="gallery-preview-container" class="preview-grid"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal: Notice / Alert Dialog -->
    <div class="modal fade" id="productFormNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg text-center p-3">
                <div class="modal-body p-1">
                    <div id="productNoticeIconWrap"
                        class="rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center mx-auto mb-2"
                        style="width: 44px; height: 44px;">
                        <i id="productNoticeIcon" class="fas fa-exclamation-triangle fs-4"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1" id="productNoticeTitle" style="font-size: 14px;">Notice</h6>
                    <p class="text-muted small mb-3" id="productNoticeMsg" style="font-size: 12px; line-height: 1.45;"></p>
                    <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

    <script>
        let downloadFileIndex = 0;

        function handleBranchModeChange(mode) {
            const branchContainer = document.getElementById('branch-selection-container');
            const onlineSwitch = document.getElementById('switch-sellable-online');

            if (['selected', 'restricted', 'pos_only', 'branch_only'].includes(mode)) {
                if (branchContainer) branchContainer.classList.remove('d-none');
            } else {
                if (branchContainer) branchContainer.classList.add('d-none');
            }

            if (['pos_only', 'branch_only'].includes(mode)) {
                if (onlineSwitch) {
                    onlineSwitch.checked = false;
                }
            }
        }

        function handleOnlineSwitchChange(el) {
            const modeSelect = document.getElementById('branch_availability_mode_select');
            if (el.checked) {
                if (modeSelect && ['pos_only', 'branch_only'].includes(modeSelect.value)) {
                    modeSelect.value = 'all';
                    handleBranchModeChange('all');
                }
            }
        }

        function showProductNotice(message, title = 'Notice', isError = false) {
            const titleEl = document.getElementById('productNoticeTitle');
            const msgEl = document.getElementById('productNoticeMsg');
            const iconWrap = document.getElementById('productNoticeIconWrap');
            const icon = document.getElementById('productNoticeIcon');

            if (titleEl) titleEl.innerText = title;
            if (msgEl) msgEl.innerText = message;
            if (iconWrap && icon) {
                iconWrap.className = isError
                    ? 'rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center mx-auto mb-2'
                    : 'rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center mx-auto mb-2';
                icon.className = isError ? 'fas fa-times-circle fs-4' : 'fas fa-exclamation-triangle fs-4';
            }

            const modalEl = document.getElementById('productFormNoticeModal');
            if (modalEl && window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        }

        function extractMediaItem(mediaInput) {
            if (!mediaInput) return null;
            const item = Array.isArray(mediaInput) ? mediaInput[0] : mediaInput;
            if (!item || typeof item !== 'object') return null;
            const rawPath = item.file_path || item.path || item.url || '';
            const rawUrl = item.url || item.file_path || item.path || '';
            if (!rawPath || rawPath === 'undefined') return null;
            return {
                path: String(rawPath).trim(),
                url: String(rawUrl).trim(),
                alt: item.alt_text || item.title || ''
            };
        }

        $(document).ready(function () {
            const SummernoteMediaButton = function (context) {
                const ui = $.summernote.ui;
                const button = ui.button({
                    contents: '<i class="note-icon-picture"></i>',
                    tooltip: 'Insert Image from Media Library',
                    click: function () {
                        if (typeof window.openMediaPicker === 'function') {
                            window.openMediaPicker({
                                multiple: false,
                                onSelect: function (media) {
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
                            showProductNotice('Media Library is loading, please try again.');
                        }
                    }
                });
                return button.render();
            };

            $('#summernote-desc').summernote({
                placeholder: 'Write product details, specifications and features...',
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
                    onImageUpload: function (files) {
                        if (files.length > 0) {
                            uploadSummernoteImageDirect(files[0]);
                        }
                    }
                }
            });

            $('#reg-price, #sale-price').on('input', function () {
                let reg = parseFloat($('#reg-price').val()) || 0;
                let sale = parseFloat($('#sale-price').val()) || 0;
                if (reg > 0 && sale > 0 && reg > sale) {
                    $('#disc-pct').val(((reg - sale) / reg * 100).toFixed(2));
                } else {
                    $('#disc-pct').val(0);
                }
            });

            if (!document.getElementById('barcode-input').value) {
                generateBarcode();
            }
            if (!document.querySelector('input[name="sku"]').value) {
                document.querySelector('input[name="sku"]').value = 'SKU-' + Math.floor(10000 + Math.random() * 90000);
            }
            if (!document.querySelector('input[name="product_code"]').value) {
                document.querySelector('input[name="product_code"]').value = 'PRD-' + Math.floor(10000 + Math.random() * 90000);
            }

            $('#tag_input').on('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addTagFromInput();
                }
            });

            $('#productAddForm').on('submit', function () {
                const hasVarToggle = document.getElementById('has-variants-toggle');
                const structInput = document.getElementById('product_struct');
                const varRows = document.querySelectorAll('#variant-tbody tr');
                if ((hasVarToggle && hasVarToggle.checked) || varRows.length > 0) {
                    if (structInput && hasVarToggle && hasVarToggle.checked) {
                        structInput.value = 'variable';
                    }
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
                        showProductNotice('Image upload failed.', 'Error', true);
                    }
                })
                .catch(err => {
                    console.error('Summernote upload error:', err);
                    showProductNotice('Failed to upload image into media library.', 'Error', true);
                });
        }

        function chooseProductThumbnailMedia() {
            if (typeof window.openMediaPicker === 'function') {
                window.openMediaPicker({
                    multiple: false,
                    onSelect: function (media) {
                        const picked = extractMediaItem(media);
                        if (picked && picked.path) {
                            document.getElementById('thumbnail-media-input').value = picked.path;
                            document.getElementById('thumbnail-input').value = '';
                            const previewBox = document.getElementById('thumb-preview');
                            previewBox.classList.remove('d-none');
                            previewBox.querySelector('img').src = picked.url;
                        }
                    }
                });
            } else {
                showProductNotice('Media Library is loading, please try again.');
            }
        }

        function chooseGalleryMedia() {
            if (typeof window.openMediaPicker === 'function') {
                window.openMediaPicker({
                    multiple: true,
                    onSelect: function (mediaList) {
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

        function generateBarcode() {
            // ৮ ডিজিটের ইউনিক সংখ্যা জেনারেট করবে (10000000 থেকে 99999999 এর মধ্যে)
            const unique8Digit = Math.floor(10000000 + Math.random() * 90000000);
            document.getElementById('barcode-input').value = unique8Digit;
        }

        function previewSingleImage(input, previewId) {
            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = function (e) {
                    let previewBox = document.getElementById(previewId);
                    previewBox.classList.remove('d-none');
                    previewBox.querySelector('img').src = e.target.result;
                    document.getElementById('thumbnail-media-input').value = '';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function clearThumbnail(inputId, previewId) {
            const input = document.getElementById(inputId);
            const previewBox = document.getElementById(previewId);
            document.getElementById('thumbnail-media-input').value = '';
            if (input) input.value = '';
            if (previewBox) {
                previewBox.classList.add('d-none');
                previewBox.querySelector('img').src = '';
            }
        }

        let dt = typeof DataTransfer !== 'undefined' ? new DataTransfer() : null;
        function previewGalleryImages(input) {
            const container = document.getElementById('gallery-preview-container');
            const clearAllBtn = document.getElementById('clear-all-gallery-btn');
            if (input.files && input.files.length > 0) {
                Array.from(input.files).forEach((file) => {
                    if (dt) {
                        try { dt.items.add(file); } catch (e) { }
                    }
                    let reader = new FileReader();
                    reader.onload = function (e) {
                        let div = document.createElement('div');
                        div.className = 'preview-item shadow-xs';
                        div.dataset.filename = file.name;
                        div.innerHTML = `
                            <img src="${e.target.result}">
                            <button type="button" class="remove-btn" onclick="removeGalleryImage(this, '${file.name.replace(/'/g, "\\'")}')" title="Remove"><i class="fas fa-times"></i></button>
                        `;
                        container.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                });
                if (dt && dt.files.length > 0) {
                    try { input.files = dt.files; } catch (e) { }
                }
                if (clearAllBtn) clearAllBtn.classList.remove('d-none');
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
                } catch (e) { }
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

        // ==========================================
        // Product Variant Matrix With Single Source of Truth Cost & Stock
        // ==========================================
        let variantIndex = 0;
        function toggleVariantSection() {
            const isChecked = document.getElementById('has-variants-toggle').checked;
            const section = document.getElementById('variant-section');
            const structInput = document.getElementById('product_struct');
            const baseStockInput = document.getElementById('base-stock-input');
            const baseStockHelp = document.getElementById('base-stock-help');
            const baseStockLabel = document.getElementById('base-stock-label');

            if (isChecked) {
                section.classList.remove('d-none');
                structInput.value = 'variable';
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
                structInput.value = 'simple';
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

        function chooseVariantMedia(idx) {
            if (typeof window.openMediaPicker === 'function') {
                window.openMediaPicker({
                    multiple: false,
                    onSelect: function (media) {
                        const picked = extractMediaItem(media);
                        if (picked && picked.path) {
                            const mediaEl = document.getElementById('var-media-' + idx);
                            const existEl = document.getElementById('var-existing-' + idx);
                            const fileEl = document.getElementById('var-file-' + idx);
                            const remFlag = document.getElementById('var-remove-flag-' + idx);
                            const imgEl = document.getElementById('var-img-preview-' + idx);
                            const btnEl = document.getElementById('var-remove-btn-' + idx);
                            const structEl = document.getElementById('product_struct');

                            if (mediaEl) mediaEl.value = picked.path;
                            if (existEl) existEl.value = picked.path;
                            if (fileEl) fileEl.value = '';
                            if (remFlag) remFlag.value = '0';
                            if (imgEl) imgEl.src = picked.url;
                            if (btnEl) btnEl.classList.remove('d-none');
                            if (structEl) structEl.value = 'variable';
                        }
                    }
                });
            }
        }

        function previewVariantFile(input, index) {
            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = function (e) {
                    const img = document.getElementById('var-img-preview-' + index);
                    if (img) img.src = e.target.result;
                    const mediaEl = document.getElementById('var-media-' + index);
                    const existEl = document.getElementById('var-existing-' + index);
                    const remFlag = document.getElementById('var-remove-flag-' + index);
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
            const fileInput = document.getElementById('var-file-' + index);
            const existingInput = document.getElementById('var-existing-' + index);
            const mediaInput = document.getElementById('var-media-' + index);
            const remFlag = document.getElementById('var-remove-flag-' + index);
            const img = document.getElementById('var-img-preview-' + index);
            const btn = document.getElementById('var-remove-btn-' + index);

            if (fileInput) fileInput.value = '';
            if (existingInput) existingInput.value = '';
            if (mediaInput) mediaInput.value = '';
            if (remFlag) remFlag.value = '1';
            if (img) img.src = 'https://placehold.co/40x40?text=No+Img';
            if (btn) btn.classList.add('d-none');
        }

        function generateBulkMatrix() {
            const colors = document.getElementById('bulk-colors').value.split(',').map(c => c.trim()).filter(c => c);
            const sizes = document.getElementById('bulk-sizes').value.split(',').map(s => s.trim()).filter(s => s);

            if (colors.length === 0 && sizes.length === 0) {
                showProductNotice('Please enter at least one color or size.', 'Validation');
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
            const previewDescEl = document.getElementById('preview-desc');
            if (!previewTitleEl || !previewDescEl) return;

            const title = document.getElementById('seo_title')?.value || document.querySelector('input[name="title"]')?.value || 'Product Title Example';
            const desc = document.getElementById('meta_desc')?.value || '';
            previewTitleEl.innerText = title;
            previewDescEl.innerText = desc.substring(0, 160) + (desc.length > 160 ? '...' : '');
        }

        // Categories Tabs
        function switchCategoryTab(tab) {
            if (tab === 'all') {
                $('#product-categories-box .wc-box-tab-btn:first').addClass('active');
                $('#product-categories-box .wc-box-tab-btn:last').removeClass('active');
                $('#categoryTreeList li').show();
            } else {
                $('#product-categories-box .wc-box-tab-btn:last').addClass('active');
                $('#product-categories-box .wc-box-tab-btn:first').removeClass('active');
                $('#categoryTreeList li').each(function () {
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

        $(document).on('change', '.cat-cb-child', function () {
            if ($(this).is(':checked')) {
                let parentId = $(this).data('parent');
                $('#cat-p-' + parentId).prop('checked', true);
            }
        });

        function saveNewCategoryAjax() {
            const name = $('#new_cat_name').val().trim();
            const parentId = $('#new_cat_parent').val();
            const btn = $('#btn-save-ajax-cat');
            const loader = $('#cat-ajax-loader');

            if (!name) {
                showProductNotice('Please enter a category name.', 'Required Field');
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
                success: function (response) {
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
                error: function (xhr) {
                    btn.prop('disabled', false);
                    loader.addClass('d-none');
                    showProductNotice(xhr.responseJSON?.message || 'Failed to create category.', 'Error', true);
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
                    success: function (tags) {
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
                            link.onclick = function () {
                                appendTagChip(item.name);
                            };
                            list.appendChild(link);
                        });
                    },
                    error: function () {
                        list.innerHTML = '<span class="text-danger small">Failed to load tags.</span>';
                    }
                });
            }
        }

        // Brands Tabs
        function switchBrandTab(tab) {
            if (tab === 'all') {
                $('#product-brands-box .wc-box-tab-btn:first').addClass('active');
                $('#product-brands-box .wc-box-tab-btn:last').removeClass('active');
                $('#brandList li').show();
            } else {
                $('#product-brands-box .wc-box-tab-btn:last').addClass('active');
                $('#product-brands-box .wc-box-tab-btn:first').removeClass('active');
                $('#brandList li').each(function () {
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
                showProductNotice('Please enter a brand name.', 'Required Field');
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
                success: function (response) {
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
                error: function (xhr) {
                    btn.prop('disabled', false);
                    loader.addClass('d-none');
                    showProductNotice(xhr.responseJSON?.message || 'Failed to create brand.', 'Error', true);
                }
            });
        }
    </script>
@endsection