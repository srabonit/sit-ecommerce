@extends('layouts.admin')

@section('title', 'Incomplete & Failed Orders - ' . config('app.name'))

@php
    $resolveOrderImg = function($path, $fallback = '') {
        if (empty($path) || $path === 'undefined' || $path === 'null') return$fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path,$fallback);
        }
        if (function_exists('show_image')) return show_image($path,$fallback);
        if (filter_var($path, FILTER_VALIDATE_URL)) return$path;
        return asset(ltrim($path, '/'));
    };
@endphp

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #incomplete-orders-root .btn,
    #incomplete-orders-root .btn-sm,
    #incomplete-orders-root button,
    #incomplete-orders-root .badge,
    #incomplete-orders-root .form-control,
    #incomplete-orders-root .form-select,
    #incomplete-orders-root .input-group-text,
    #incomplete-orders-root .page-link,
    #incomplete-orders-root .page-item span,
    #incomplete-orders-root .alert,
    #incomplete-orders-root .incomplete-table-card,
    #incomplete-orders-root .proof-thumb-box,
    .incomplete-modal-scope .modal-content,
    .incomplete-modal-scope .btn,
    .incomplete-modal-scope .form-control {
        border-radius: 5px !important;
    }

    #incomplete-orders-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #incomplete-orders-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Compact Button Styling */
    #incomplete-orders-root .btn-compact,
    .incomplete-modal-scope .btn-compact {
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

    .table-action-sq-btn {
        width: 26px !important;
        height: 26px !important;
        min-width: 26px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 11px !important;
        border-radius: 5px !important;
        transition: all 0.15s ease;
    }

    /* Enterprise Surface Cards */
    .incomplete-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }

    /* Table Typography & Overflow Handling */
    .table-responsive-wrapper {
        width: 100%;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
    }
    .table-responsive-wrapper::-webkit-scrollbar {
        height: 6px;
    }
    .table-responsive-wrapper::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .custom-incomplete-table {
        margin-bottom: 0;
        width: 100%;
        border-collapse: collapse;
    }
    .custom-incomplete-table th {
        background-color: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1.5px solid #e2e8f0;
        padding: 9px 10px;
        white-space: nowrap;
    }
    .custom-incomplete-table td {
        padding: 8px 10px;
        vertical-align: middle;
        font-size: 12px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }
    .custom-incomplete-table tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Column Width Architecture (Fits on PC Screens) */
    @media (min-width: 992px) {
        .col-inc-order    { width: 13%; }
        .col-inc-date     { width: 12%; }
        .col-inc-status   { width: 12%; }
        .col-inc-paystat  { width: 12%; }
        .col-inc-customer { width: 20%; max-width: 210px; }
        .col-inc-method   { width: 12%; }
        .col-inc-proof    { width: 8%; text-align: center; }
        .col-inc-amount   { width: 11%; }
        .col-inc-action   { width: 10%; text-align: right; }
    }

    /* Anti-Bleed Text Wrappers */
    .overflow-safe-cell {
        min-width: 0 !important;
        word-break: break-word !important;
        overflow-wrap: anywhere !important;
    }
    .safe-text-break {
        overflow-wrap: anywhere !important;
        word-break: break-word !important;
        white-space: normal !important;
    }

    /* Global Media Chooser Proof Thumbnail */
    .proof-thumb-box {
        width: 32px;
        height: 32px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        transition: transform 0.15s ease, border-color 0.15s ease;
    }
    .proof-thumb-box:hover {
        transform: scale(1.05);
        border-color: #2563eb;
    }
    .proof-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Bengali Safe Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Responsive Pagination */
    #paginationWrapper nav {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
    }
    #paginationWrapper nav > div:first-child.d-sm-none,
    #paginationWrapper nav p.text-muted,
    #paginationWrapper nav div:has(> p.text-muted) {
        display: none !important;
    }
    #paginationWrapper nav > div:last-child,
    #paginationWrapper nav > div.d-sm-flex {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 4px !important;
    }
    #paginationWrapper .pagination {
        margin: 0 !important;
        display: flex !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
        gap: 3px !important;
        padding: 0 !important;
        list-style: none !important;
    }
    #paginationWrapper .page-item .page-link,
    #paginationWrapper .page-item span {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 26px !important;
        height: 26px !important;
        padding: 2px 6px !important;
        font-size: 11px !important;
        color: #475569 !important;
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 5px !important;
        text-decoration: none !important;
    }
    #paginationWrapper .page-item.active .page-link,
    #paginationWrapper .page-item.active span {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 700 !important;
    }
    #paginationWrapper .page-item.disabled .page-link,
    #paginationWrapper .page-item.disabled span {
        color: #cbd5e1 !important;
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        pointer-events: none !important;
    }
    #paginationWrapper nav svg {
        width: 11px !important;
        height: 11px !important;
    }

    /* 100/100 Mobile Responsive Tuning */
    @media (max-width: 767.98px) {
        .custom-incomplete-table {
            min-width: 760px !important;
        }
        .incomplete-header h5 {
            font-size: 16px !important;
        }
        .header-top-wrap {
            flex-direction: column;
            align-items: stretch !important;
        }
        .header-top-wrap .btn-compact {
            width: 100%;
        }
        .filter-form-wrap {
            flex-direction: column;
            align-items: stretch !important;
        }
        .filter-form-wrap .btn-compact {
            width: 100%;
        }
    }
</style>

<div class="container-fluid py-3 px-2 px-md-4" id="incomplete-orders-root">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start align-items-md-center gap-2 mb-3 header-top-wrap">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-triangle-exclamation text-warning"></i>
                    <span>Incomplete &amp; Failed Orders</span>
                </h5>
                <span class="badge bg-warning-subtle text-dark border border-warning-subtle font-monospace bn-digit px-2 py-0.5" style="font-size: 10.5px;">
                    {{ $orders->total() }} Recoverable
                </span>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">Customers who abandoned checkout, had gateway failures, or dropped off before final booking.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.order.online') }}" class="btn btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-arrow-left me-1"></i>
                <span>Back to Online Orders</span>
            </a>
        </div>
    </div>

    <!-- Search & Filter Toolbar Card -->
    <div class="incomplete-table-card p-2 px-3 mb-3">
        <form action="{{ route('admin.order.incomplete') }}" method="GET" class="row g-2 align-items-center filter-form-wrap">
            <div class="col-12 col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted border-end-0" style="height: 32px;"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by invoice #, customer name, or phone..." value="{{ request('search') }}" style="height: 32px; font-size: 12px;">
                </div>
            </div>
            <div class="col-12 col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-dark btn-compact shadow-xs px-3">
                    <i class="fas fa-filter me-1"></i>
                    <span>Filter</span>
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.order.incomplete') }}" class="btn btn-outline-secondary btn-compact shadow-xs px-3">
                        <i class="fas fa-rotate-left me-1"></i>
                        <span>Reset</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="incomplete-table-card overflow-hidden">
        <div class="table-responsive-wrapper">
            <table class="table align-middle custom-incomplete-table">
                <thead>
                    <tr>
                        <th class="ps-3 col-inc-order">Order ID</th>
                        <th class="col-inc-date">Date &amp; Time</th>
                        <th class="col-inc-status">Order Status</th>
                        <th class="col-inc-paystat">Payment Status</th>
                        <th class="col-inc-customer">Customer Details</th>
                        <th class="col-inc-method">Gateway &amp; Method</th>
                        <th class="col-inc-proof">Proof Slip</th>
                        <th class="col-inc-amount">Total Amount</th>
                        <th class="col-inc-action text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $ordStatus = strtolower($order->order_status ?? 'incomplete');
                            $ordBadge = match($ordStatus) {
                                'failed'     => 'bg-danger-subtle text-danger border border-danger-subtle',
                                'cancelled'  => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                default      => 'bg-warning-subtle text-dark border border-warning-subtle'
                            };

                            $pStatus = strtolower($order->payment_status ?? 'unpaid');
                            $payBadge = match($pStatus) {
                                'paid'                 => 'bg-success-subtle text-success border border-success-subtle',
                                'failed'               => 'bg-danger-subtle text-danger border border-danger-subtle',
                                'pending_verification' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                                default                => 'bg-light text-dark border'
                            };

                            $pSubtotal = (float)($order->product_subtotal ?? ($order->subtotal ?? 0));
                            $dCharge   = (float)($order->delivery_charge ?? ($order->shipping_charge ?? 0));
                            $grand     = (float)($order->grand_total ?? 0);

                            $custPhone =$order->shipping_phone ?? ($order->customer_phone ?? ($order->phone ?? ''));
                            $proofPath =$order->payment_screenshot ?? '';
                            $proofUrl  = !empty($proofPath) ? $resolveOrderImg($proofPath) : '';
                        @endphp
                        <tr>
                            {{-- 1. ORDER NUMBER --}}
                            <td class="ps-3 col-inc-order">
                                <a href="{{ route('admin.order.view', $order->id) }}" class="fw-bold text-primary text-decoration-none font-monospace bn-digit d-inline-flex align-items-center gap-1" style="font-size: 12.5px;" title="View Details">
                                    <span>#{{ $order->invoice_no ?? $order->id }}</span>
                                </a>
                                @if(!empty($order->order_number) && $order->order_number !=$order->id)
                                    <small class="d-block text-muted font-monospace bn-digit text-truncate mt-1" style="font-size: 10px; max-width: 120px;">
                                        Ref: {{ $order->order_number }}
                                    </small>
                                @endif
                            </td>

                            {{-- 2. DATE & TIME --}}
                            <td class="col-inc-date text-nowrap">
                                <span class="text-dark fw-semibold bn-digit d-block" style="font-size: 11.5px;">
                                    <i class="far fa-calendar-alt text-muted me-1" style="font-size: 10px;"></i>{{ date('d M, Y', strtotime($order->created_at)) }}
                                </span>
                                <small class="text-muted font-monospace bn-digit mt-0.5 d-block" style="font-size: 10px;">
                                    <i class="far fa-clock me-1" style="font-size: 9px;"></i>{{ date('h:i A', strtotime($order->created_at)) }}
                                </small>
                            </td>

                            {{-- 3. ORDER STATUS --}}
                            <td class="col-inc-status text-nowrap">
                                <span class="badge {{ $ordBadge }} px-2 py-0.5 text-uppercase fw-bold" style="font-size: 9.5px;">
                                    {{ str_replace('_', ' ', $ordStatus) }}
                                </span>
                            </td>

                            {{-- 4. PAYMENT STATUS --}}
                            <td class="col-inc-paystat text-nowrap">
                                <span class="badge {{ $payBadge }} px-2 py-0.5 text-uppercase fw-bold" style="font-size: 9.5px;">
                                    {{ str_replace('_', ' ', $pStatus) }}
                                </span>
                            </td>

                            {{-- 5. CUSTOMER DETAILS (Anti-Overlap) --}}
                            <td class="col-inc-customer overflow-safe-cell">
                                <strong class="text-dark d-block text-truncate" style="font-size: 12px;" title="{{ $order->shipping_name ?? ($order->customer_name ?? ($order->name ?? 'Guest Customer')) }}">
                                    {{ $order->shipping_name ?? ($order->customer_name ?? ($order->name ?? 'Guest Customer')) }}
                                </strong>
                                @if(!empty($custPhone))
                                    <a href="tel:{{ $custPhone }}" class="text-secondary text-decoration-none small text-nowrap font-monospace bn-digit mt-1 d-inline-flex align-items-center gap-1" style="font-size: 10.5px;">
                                        <i class="fas fa-phone text-muted" style="font-size: 9px;"></i>
                                        <span>{{ $custPhone }}</span>
                                    </a>
                                @else
                                    <span class="text-muted small font-monospace mt-1 d-block" style="font-size: 10px;">N/A</span>
                                @endif
                            </td>

                            {{-- 6. GATEWAY & PAYMENT METHOD --}}
                            <td class="col-inc-method text-nowrap">
                                <span class="badge bg-light text-dark border text-uppercase font-monospace px-1.5 py-0.5 fw-semibold d-inline-block" style="font-size: 9.5px;">
                                    {{ $order->payment_method ?? 'COD' }}
                                </span>
                                @if(!empty($order->transaction_id))
                                    <small class="d-block font-monospace text-primary mt-1 bn-digit text-truncate" style="font-size: 9.5px; max-width: 120px;" title="{{ $order->transaction_id }}">
                                        Txn: {{ $order->transaction_id }}
                                    </small>
                                @endif
                            </td>

                            {{-- 7. GLOBAL MEDIA CHOOSER PAYMENT PROOF THUMBNAIL --}}
                            <td class="col-inc-proof text-center text-nowrap">
                                @if(!empty($proofUrl))
                                    <div class="proof-thumb-box mx-auto" onclick="zoomIncompleteProof('{{ $proofUrl }}', '{{ addslashes($order->invoice_no ?? $order->id) }}')" title="Click to Zoom Receipt">
                                        <img src="{{ $proofUrl }}" alt="Receipt" onerror="this.src='https://placehold.co/100x100?text=Slip';">
                                    </div>
                                @else
                                    <button type="button" class="btn btn-light border btn-compact py-0.5 px-1.5 text-muted" style="font-size: 9.5px !important;" onclick="attachIncompleteProofMedia({{ $order->id }})" title="Attach Proof from Global Media Library">
                                        <i class="fas fa-paperclip me-1"></i>Attach
                                    </button>
                                @endif
                            </td>

                            {{-- 8. TOTAL AMOUNT --}}
                            <td class="col-inc-amount text-nowrap">
                                <span class="fw-bold text-dark font-monospace bn-digit d-block" style="font-size: 12.5px;">
                                    ৳ {{ number_format($grand, 2) }}
                                </span>
                                @if($dCharge > 0)
                                    <small class="text-muted font-monospace bn-digit d-block mt-0.5" style="font-size: 9.5px; line-height: 1.2;">
                                        Prod: ৳{{ number_format($pSubtotal, 0) }} | Del: ৳{{ number_format($dCharge, 0) }}
                                    </small>
                                @endif
                            </td>

                            {{-- 9. ACTIONS --}}
                            <td class="col-inc-action text-end pe-3 text-nowrap">
                                <div class="d-inline-flex gap-1 align-items-center justify-content-end">
                                    @if(!empty($custPhone))
                                        <a href="https://wa.me/88{{ preg_replace('/[^0-9]/', '', $custPhone) }}?text=Hello%20{{ urlencode($order->shipping_name ?? 'Customer') }},%20your%20order%20%23{{ $order->invoice_no ?? $order->id }}%20was%20incomplete." target="_blank" class="btn btn-outline-success table-action-sq-btn shadow-xs" title="WhatsApp Customer to Recover">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.order.view', $order->id) }}" class="btn btn-outline-primary table-action-sq-btn shadow-xs" title="View &amp; Recover Order">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <div class="d-flex flex-column align-items-center justify-content-center py-3">
                                    <i class="fas fa-clipboard-check fa-3x mb-2 text-secondary opacity-25"></i>
                                    <h6 class="fw-bold text-secondary mb-1" style="font-size: 14px;">No Incomplete Orders</h6>
                                    <p class="small text-muted mb-0" style="font-size: 12px;">All customer checkouts and payments have been successfully processed.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-2 border-top bg-white d-flex justify-content-center overflow-auto" id="paginationWrapper">
                {{ $orders->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal 1: Payment Slip Zoom Lightbox Modal                                 -->
<!-- ========================================================================= -->
<div class="modal fade incomplete-modal-scope" id="proofZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                    <i class="fas fa-image text-primary me-1"></i> Payment Receipt Slip: <span id="proofOrderTitle" class="font-monospace bn-digit"></span>
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="modalZoomImgTag" src="" alt="Payment Proof" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 4px;">
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal 2: Bootstrap Notice Dialog Modal (Replaces browser alert())         -->
<!-- ========================================================================= -->
<div class="modal fade incomplete-modal-scope" id="orderNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-2" id="orderNoticeIconWrap" style="width: 44px; height: 44px;">
                    <i class="fas fa-circle-info fs-4" id="orderNoticeIcon"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="orderNoticeTitle" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="orderNoticeMsg" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
// Clean Notice Modal Helper (Replaces native alert())
function showIncompleteNotice(message, title = 'Notification', type = 'info') {
    const titleEl  = document.getElementById('orderNoticeTitle');
    const msgEl    = document.getElementById('orderNoticeMsg');
    const iconWrap = document.getElementById('orderNoticeIconWrap');
    const iconEl   = document.getElementById('orderNoticeIcon');

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

    const modalEl = document.getElementById('orderNoticeModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

// Lightbox Zoom Handler
function zoomIncompleteProof(imgUrl, orderRef) {
    const zoomImgTag = document.getElementById('modalZoomImgTag');
    const titleSpan  = document.getElementById('proofOrderTitle');
    if (zoomImgTag && imgUrl) {
        zoomImgTag.src = imgUrl;
        if (titleSpan) titleSpan.innerText = '#' + orderRef;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('proofZoomModal')).show();
    }
}

// Global Media Chooser Integration
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

function attachIncompleteProofMedia(orderId) {
    if (typeof window.openMediaPicker === 'function') {
        window.openMediaPicker({
            multiple: false,
            onSelect: function(media) {
                const picked = extractMediaItem(media);
                if (picked && picked.path) {
                    // Update via quick AJAX to sync payment screenshot with order
                    fetch(`/admin/orders/update-status/${orderId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            payment_screenshot: picked.path
                        })
                    })
                    .then(res => res.json())
                    .then(() => {
                        showIncompleteNotice('Payment slip attached from Global Media Library.', 'Success', 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(() => {
                        showIncompleteNotice('Selected image: ' + picked.path + '. Open order view to save.', 'Notice');
                    });
                }
            }
        });
    } else {
        showIncompleteNotice('Global Media Chooser is loading or not available on this layout.', 'Notice');
    }
}
</script>
@endsection