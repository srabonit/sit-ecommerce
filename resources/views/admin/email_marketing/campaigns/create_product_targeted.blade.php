@php
    $pTitle = $product->title ?? ($product->name ?? 'Product #' . $product->id);$pSku   = $product->sku ?? ($product->product_code ?? 'NO-SKU');
    
    // Smart Price Resolution
    $salePrice = (float)($product->sale_price ?? ($product->offer_price ?? 0));
    $mrpPrice  = (float)($product->mrp ?? ($product->regular_price ?? 0));
    $pPrice    =$salePrice > 0 ? $salePrice : ($mrpPrice > 0 ? $mrpPrice : (float)($product->price ?? 0));
    
    $pImg = $product->thumbnail ?? ($product->image ?? null);

    // Fail-safe thumbnail resolver
    $resolveThumb = function($path,$fallback = 'https://placehold.co/60x60?text=Product') {
        if (empty($path)) return$fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path,$fallback);
        }
        if (function_exists('show_image')) return show_image($path,$fallback);
        if (filter_var($path, FILTER_VALIDATE_URL)) return$path;
        return asset(ltrim(str_replace(['public_html/', 'public/'], '', $path), '/'));
    };

    $thumbUrl = !empty($pImg) ? $resolveThumb($pImg) : 'https://placehold.co/60x60?text=Product';
@endphp

@extends('layouts.admin')

@section('title', 'Target Buyers - ' . $pTitle)

@section('admin_content')
<style>
    /* Strict 5px Element Radius Across All Controls */
    #email-marketing-product-targeted-root .btn,
    #email-marketing-product-targeted-root .btn-sm,
    #email-marketing-product-targeted-root button,
    #email-marketing-product-targeted-root .badge,
    #email-marketing-product-targeted-root .alert,
    #email-marketing-product-targeted-root .form-control,
    #email-marketing-product-targeted-root .form-select,
    #email-marketing-product-targeted-root .input-group-text,
    #email-marketing-product-targeted-root .card,
    #email-marketing-product-targeted-root .metric-card,
    #email-marketing-product-targeted-root .product-thumb-box,
    #email-marketing-product-targeted-root .collapse-toggle-btn,
    .target-modal-scope .modal-content,
    .target-modal-scope .btn {
        border-radius: 5px !important;
    }

    #email-marketing-product-targeted-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #email-marketing-product-targeted-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Safe Monospace & Bengali Digit Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Compact Sleek Button Styling */
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

    /* Collapsible Trigger Buttons & Rotating Chevron */
    .collapse-toggle-btn {
        width: 24px;
        height: 24px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        color: #475569;
        transition: all 0.2s ease;
    }
    .collapse-toggle-btn:hover {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
    }
    .collapse-toggle-btn[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }
    .collapse-icon {
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        font-size: 10px;
    }

    /* Enterprise Surface Cards */
    #email-marketing-product-targeted-root .card {
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }
    .section-header-wrap {
        padding: 10px 14px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
    }

    /* Metric Cards - Zero Overflow Architecture */
    .metric-card {
        padding: 12px 14px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    }
    .stat-label-title {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.02em;
        line-height: 1.2;
    }
    .stat-val-num {
        font-size: 18px;
        line-height: 1.25;
        letter-spacing: -0.02em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .stat-sub-text {
        font-size: 10px;
        margin-top: 2px;
    }
    .metric-icon-square {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
        border-radius: 5px !important;
    }

    .metric-card-blue   { background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 100%); border-left: 3.5px solid #2563eb !important; }
    .metric-card-green  { background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-left: 3.5px solid #10b981 !important; }
    .metric-card-dark   { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; border: 1px solid #334155; }

    /* Product Thumbnail Box */
    .product-thumb-box {
        width: 48px;
        height: 48px;
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
    .product-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .product-thumb-hover-overlay {
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
    .product-thumb-box:hover .product-thumb-hover-overlay {
        opacity: 1;
    }

    /* Form Controls */
    #email-marketing-product-targeted-root .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
        display: block;
    }
    #email-marketing-product-targeted-root .form-control,
    #email-marketing-product-targeted-root .form-select {
        min-height: 32px;
        font-size: 12px;
        border: 1px solid #cbd5e1;
        padding: 5px 10px;
        color: #0f172a;
        background-color: #ffffff;
        box-shadow: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #email-marketing-product-targeted-root .form-control:focus,
    #email-marketing-product-targeted-root .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        outline: none;
    }

    /* Buyer Inbox List Item */
    .buyer-inbox-item {
        border-bottom: 1px solid #f1f5f9;
        transition: background-color 0.15s ease;
    }
    .buyer-inbox-item:hover {
        background-color: #f8fafc;
    }
    .btn-copy-email {
        opacity: 0.6;
        transition: opacity 0.15s ease;
    }
    .buyer-inbox-item:hover .btn-copy-email {
        opacity: 1;
    }

    /* Mobile Responsiveness Tuning (< 768px) */
    @media (max-width: 767.98px) {
        .target-header-wrap h5 {
            font-size: 16px !important;
        }
        .top-action-btns {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .top-action-btns .btn {
            width: 100%;
            padding: 8px 6px !important;
            font-size: 11px !important;
            justify-content: center;
        }
        .metric-card {
            padding: 10px !important;
            min-height: 84px !important;
        }
        .stat-label-title {
            font-size: 10px !important;
        }
        .stat-val-num {
            font-size: 14px !important;
        }
        .stat-sub-text {
            font-size: 9px !important;
        }
        .metric-icon-square {
            width: 28px !important;
            height: 28px !important;
            font-size: 11px !important;
        }
        #email-marketing-product-targeted-root .card {
            margin-bottom: 12px !important;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4 position-relative" id="email-marketing-product-targeted-root">

    {{-- 1. Header & Navigation (Original Clean Light Theme)[cite: 21] --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 target-header-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-bullseye text-primary"></i>
                    <span>Target Previous Buyers</span>
                </h5>
                <span class="badge bg-light text-primary border font-monospace px-2 py-1" style="font-size: 10px;">
                    SKU: {{ $pSku }}
                </span>
            </div>
            <small class="text-muted d-block" style="font-size: 12px; line-height: 1.35;">
                Create targeted audience segments and promotional broadcasts for purchasers of <strong class="text-dark">{{ $pTitle }}</strong>.
            </small>
        </div>

        <div class="d-flex gap-2 top-action-btns">
            <a href="{{ route('admin.email_marketing.product_marketing') }}" class="btn btn-sm btn-outline-secondary btn-compact shadow-xs fw-semibold">
                <i class="fas fa-arrow-left me-1"></i> <span>Back to Products</span>
            </a>
            <button type="button" class="btn btn-sm btn-outline-success btn-compact bg-white shadow-xs fw-bold" onclick="exportBuyerEmailsCsv()">
                <i class="fas fa-file-csv text-success me-1"></i> <span>Export Buyers CSV</span>
            </button>
        </div>
    </div>

    {{-- 2. Product Summary & Audience Reach Metrics (3-Column Overview)[cite: 21] --}}
    <div class="row g-2 mb-3">
        {{-- Selected Product Card[cite: 21] --}}
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card metric-card metric-card-blue h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Product Specifications</span>
                    <div class="metric-icon-square bg-primary-subtle text-primary shadow-xs">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="product-thumb-box shadow-xs" onclick="zoomTargetProductImage('{{ $thumbUrl }}', '{{ addslashes($pTitle) }}')" title="Click to Zoom Image">
                            <img src="{{ $thumbUrl }}" alt="{{ $pTitle }}" onerror="this.src='https://placehold.co/60x60?text=Product';">
                            <div class="product-thumb-hover-overlay">
                                <i class="fas fa-search-plus text-white" style="font-size: 9px;"></i>
                            </div>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 12.5px; line-height: 1.3;" title="{{ $pTitle }}">
                                {{ $pTitle }}
                            </h6>
                            <small class="text-muted font-monospace bn-digit d-block text-truncate" style="font-size: 10px;">
                                Item ID: #{{ $product->id }} | SKU: {{$pSku }}
                            </small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between pt-1 border-top mt-1">
                        <span class="text-muted small" style="font-size: 11px;">Current Price:</span>
                        <span class="fw-bold text-success font-monospace bn-digit" style="font-size: 13px;">
                            ৳ {{ number_format($pPrice, 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Identified Unique Buyers[cite: 21] --}}
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card metric-card metric-card-green h-100 mb-0">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted stat-label-title text-truncate">Identified Product Buyers</span>
                    <div class="metric-icon-square bg-success-subtle text-success shadow-xs">
                        <i class="fas fa-users-viewfinder"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h4 class="fw-bold my-0 text-dark bn-digit font-monospace stat-val-num">
                        {{ number_format($buyerEmails->count()) }} <span style="font-size: 11px;" class="fw-normal text-muted">Unique Inboxes</span>
                    </h4>
                    <small class="text-success d-block text-truncate font-monospace stat-sub-text mt-1">
                        <i class="fas fa-circle-check me-1"></i>Completed &amp; Delivered Orders
                    </small>
                </div>
            </div>
        </div>

        {{-- Retargeting Conversion Action Card[cite: 21] --}}
        <div class="col-12 col-md-12 col-xl-4">
            <div class="card metric-card metric-card-dark h-100 mb-0 shadow-xs">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-white text-opacity-75 stat-label-title text-truncate">Automated Retargeting</span>
                    <div class="metric-icon-square bg-white bg-opacity-15 text-white shadow-xs">
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <strong class="text-white d-block mb-1" style="font-size: 12px;">Convert to Reusable Segment</strong>
                    <p class="text-white text-opacity-75 small mb-2" style="font-size: 10.5px; line-height: 1.4;">
                        Save these buyers as an audience segment to broadcast discount coupons, refill reminders, or variant alerts.
                    </p>
                    <button type="button" class="btn btn-warning btn-compact text-dark fw-bold shadow-xs w-100" onclick="document.getElementById('saveSegmentForm').submit()">
                        <i class="fas fa-layer-group me-1"></i> <span>Save Segment Now</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Main Working Row (Segment Form + Buyer List)[cite: 21] --}}
    <div class="row g-3">
        {{-- Left: Fast Segment Creator Form (Collapsible)[cite: 21] --}}
        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm mb-3">
                <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                    <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                        <i class="fas fa-sliders text-primary me-1"></i> 1. Configure Segment for these Buyers
                    </span>
                    <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseSegmentConfig" aria-expanded="true" aria-controls="collapseSegmentConfig" title="Toggle Section">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>
                <div class="collapse show" id="collapseSegmentConfig">
                    <div class="p-3">
                        <form action="{{ route('admin.email_marketing.segments.store') }}" method="POST" id="saveSegmentForm">
                            @csrf
                            <input type="hidden" name="filter_criteria[purchased_product_id]" value="{{ $product->id }}">

                            <div class="mb-2">
                                <label class="form-label" for="segment_name">
                                    Segment Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" id="segment_name" class="form-control" value="Buyers of {{ $pTitle }}" required>
                                <small class="text-muted d-block mt-1" style="font-size: 10px;">You will be able to select this segment when scheduling any email broadcast or automation.</small>
                            </div>

                            <div class="mb-2">
                                <label class="form-label" for="segment_type">
                                    Segment Evaluation Type <span class="text-danger">*</span>
                                </label>
                                <select name="type" id="segment_type" class="form-select font-monospace shadow-none" required>
                                    <option value="dynamic" selected>Dynamic (Auto-updates whenever new customer orders are completed)</option>
                                    <option value="snapshot">Snapshot (Fixed to current {{ $buyerEmails->count() }} identified buyers)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="segment_description">Internal Notes &amp; Strategy</label>
                                <textarea name="description" id="segment_description" rows="2" class="form-control shadow-none" style="font-size: 11px;">Targeted segment containing past customers who purchased {{ $pTitle }} (SKU: {{$pSku }}).</textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-compact shadow-xs fw-bold px-3">
                                    <i class="fas fa-save me-1"></i> Create Segment &amp; Proceed
                                </button>
                                <a href="{{ route('admin.email_marketing.product_marketing') }}" class="btn btn-light border btn-compact shadow-xs text-secondary">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Identified Buyer Emails List with Live Filter (Collapsible)[cite: 21] --}}
        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm mb-3 overflow-hidden">
                <div class="section-header-wrap d-flex justify-content-between align-items-center bg-white">
                    <div>
                        <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                            <i class="fas fa-envelope text-success me-1"></i> Verified Buyer Inboxes
                        </span>
                        <span class="badge bg-light text-secondary border font-monospace ms-1 px-2 py-0" style="font-size: 9.5px;" id="buyerCountBadge">
                            {{ $buyerEmails->count() }} Found
                        </span>
                    </div>
                    <button type="button" class="collapse-toggle-btn shadow-xs no-print" data-bs-toggle="collapse" data-bs-target="#collapseBuyersList" aria-expanded="true" aria-controls="collapseBuyersList" title="Toggle Buyers List">
                        <i class="fas fa-chevron-down collapse-icon"></i>
                    </button>
                </div>

                <div class="collapse show" id="collapseBuyersList">
                    {{-- Live Search Filter Box --}}
                    <div class="p-2 border-bottom bg-light bg-opacity-50">
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0 py-1"><i class="fas fa-search" style="font-size: 10px;"></i></span>
                            <input type="text" id="buyerSearchInput" class="form-control border-start-0 ps-0 shadow-none py-1" style="font-size: 11px; min-height: 28px;" placeholder="Search buyer email..." onkeyup="filterBuyersList()">
                        </div>
                    </div>

                    <div style="max-height: 380px; overflow-y: auto; -webkit-overflow-scrolling: touch;">
                        <ul class="list-group list-group-flush" id="buyerListContainer" style="font-size: 11.5px;">
                            @forelse($buyerEmails as $email)
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 buyer-inbox-item" data-email="{{ strtolower($email) }}">
                                    <div class="d-flex align-items-center gap-2 min-w-0">
                                        <i class="fas fa-user-check text-success flex-shrink-0" style="font-size: 11px;"></i>
                                        <span class="text-dark font-monospace text-truncate" title="{{ $email }}">{{ $email }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1 flex-shrink-0 ms-2">
                                        <button type="button" class="btn btn-sm btn-light border p-0 btn-copy-email d-inline-flex align-items-center justify-content-center shadow-xs" style="width: 22px; height: 22px; border-radius: 4px !important;" onclick="copyBuyerEmail('{{ addslashes($email) }}')" title="Copy Email">
                                            <i class="fas fa-copy text-secondary" style="font-size: 9px;"></i>
                                        </button>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-1 py-0" style="font-size: 9px;">
                                            Buyer
                                        </span>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-center text-muted py-5 small" id="emptyBuyerNotice">
                                    <i class="fas fa-users-slash fa-2x mb-2 d-block text-secondary opacity-25"></i>
                                    <span>No completed customer orders found for this product yet.</span>
                                </li>
                            @endforelse
                        </ul>
                    </div>

                    @if($buyerEmails->count() > 0)
                        <div class="p-2 px-3 border-top bg-light bg-opacity-25 d-flex justify-content-between align-items-center">
                            <small class="text-muted font-monospace bn-digit" style="font-size: 10px;">
                                Ready for segment dispatch
                            </small>
                            <button type="button" class="btn btn-sm btn-link text-success p-0 text-decoration-none fw-semibold font-monospace" style="font-size: 10.5px;" onclick="exportBuyerEmailsCsv()">
                                <i class="fas fa-file-csv me-1"></i> Download CSV
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Lightbox Zoom Modal for Product Thumbnail -->
<div class="modal fade target-modal-scope" id="targetProductZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 108000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12px;" id="targetZoomModalTitle">
                    <i class="fas fa-image text-primary me-1"></i> Product Preview
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="targetZoomModalImgTag" src="" alt="Product Photo" class="img-fluid border" style="max-height: 380px; object-fit: contain; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Notice Dialog Modal (Replaces Browser Alert) -->
<div class="modal fade target-modal-scope" id="targetNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-2" id="targetNoticeIconWrap" style="width: 44px; height: 44px; border-radius: 50% !important;">
                    <i class="fas fa-circle-info fs-4" id="targetNoticeIcon"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="targetNoticeTitle" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="targetNoticeMsg" style="font-size: 11.5px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    function zoomTargetProductImage(url, title = 'Product Preview') {
        const titleEl = document.getElementById('targetZoomModalTitle');
        const imgTag  = document.getElementById('targetZoomModalImgTag');
        if (url && imgTag) {
            if (titleEl) titleEl.innerText = title;
            imgTag.src = url;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('targetProductZoomModal')).show();
        }
    }

    function showTargetNotice(message, title = 'Notification', type = 'info') {
        document.getElementById('targetNoticeTitle').innerText = title;
        document.getElementById('targetNoticeMsg').innerText = message;
        const iconWrap = document.getElementById('targetNoticeIconWrap');
        const iconEl   = document.getElementById('targetNoticeIcon');

        if (type === 'success') {
            iconWrap.className = 'rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mx-auto mb-2';
            iconEl.className = 'fas fa-circle-check fs-4';
        } else if (type === 'warning') {
            iconWrap.className = 'rounded-circle bg-warning-subtle text-warning-emphasis d-inline-flex align-items-center justify-content-center mx-auto mb-2';
            iconEl.className = 'fas fa-circle-exclamation fs-4';
        } else {
            iconWrap.className = 'rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-2';
            iconEl.className = 'fas fa-circle-info fs-4';
        }

        const modalEl = document.getElementById('targetNoticeModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function copyBuyerEmail(email) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(email).then(() => {
                showTargetNotice(`Email "${email}" copied to clipboard.`, 'Copied', 'success');
            }).catch(() => {
                showTargetNotice(`Email: ${email}`, 'Buyer Email');
            });
        } else {
            showTargetNotice(`Email: ${email}`, 'Buyer Email');
        }
    }

    function filterBuyersList() {
        const query = (document.getElementById('buyerSearchInput')?.value || '').toLowerCase().trim();
        const items = document.querySelectorAll('.buyer-inbox-item');
        let count = 0;

        items.forEach(item => {
            const email = item.getAttribute('data-email') || '';
            if (!query || email.includes(query)) {
                item.style.display = '';
                count++;
            } else {
                item.style.display = 'none';
            }
        });

        const badge = document.getElementById('buyerCountBadge');
        if (badge) {
            badge.innerText = `${count} Showing`;
        }
    }

    function exportBuyerEmailsCsv() {
        const emails = @json($buyerEmails);
        if (!emails || emails.length === 0) {
            showTargetNotice('No buyer emails available to export for this product.', 'Empty Audience', 'warning');
            return;
        }

        let csvContent = "data:text/csv;charset=utf-8,Email Address\n";
        emails.forEach(function(email) {
            csvContent += `"${email}"\n`;
        });

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "buyers_product_{{ $product->id }}_" + new Date().toISOString().slice(0, 10) + ".csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
@endsection