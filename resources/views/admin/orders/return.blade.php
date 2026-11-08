@extends('layouts.admin')

@section('title', 'Return Orders - ' . config('app.name'))

@php
    $resolveOrderImg = function($path,$fallback = '') {
        if (empty($path) ||$path === 'undefined' || $path === 'null') return$fallback;
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
    #return-orders-root .btn,
    #return-orders-root .btn-sm,
    #return-orders-root button,
    #return-orders-root .badge,
    #return-orders-root .form-control,
    #return-orders-root .form-select,
    #return-orders-root .input-group-text,
    #return-orders-root .page-link,
    #return-orders-root .page-item span,
    #return-orders-root .alert,
    #return-orders-root .return-table-card,
    #return-orders-root .proof-thumb-box,
    .return-modal-scope .modal-content,
    .return-modal-scope .btn,
    .return-modal-scope .form-control {
        border-radius: 5px !important;
    }

    #return-orders-root .input-group > .input-group-text {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    #return-orders-root .input-group > .form-control {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    /* Compact Button Styling */
    #return-orders-root .btn-compact,
    .return-modal-scope .btn-compact {
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
    .return-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
    }

    /* Table Typography & Scrolling */
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

    .custom-return-table {
        margin-bottom: 0;
        width: 100%;
        border-collapse: collapse;
    }
    .custom-return-table th {
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
    .custom-return-table td {
        padding: 8px 10px;
        vertical-align: middle;
        font-size: 12px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }
    .custom-return-table tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Desktop Column Width Controls */
    @media (min-width: 992px) {
        .col-ret-order    { width: 13%; }
        .col-ret-date     { width: 12%; }
        .col-ret-status   { width: 12%; }
        .col-ret-stock    { width: 11%; }
        .col-ret-customer { width: 18%; max-width: 200px; }
        .col-ret-courier  { width: 12%; }
        .col-ret-proof    { width: 9%; text-align: center; }
        .col-ret-amount   { width: 11%; }
        .col-ret-action   { width: 10%; text-align: right; }
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

    /* Safe Typography */
    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
    }

    /* Pagination Styling */
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
        .custom-return-table {
            min-width: 780px !important;
        }
        .return-header h5 {
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

<div class="container-fluid py-3 px-2 px-md-4" id="return-orders-root">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start align-items-md-center gap-2 mb-3 header-top-wrap">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 18px;">
                    <i class="fas fa-rotate-left text-danger"></i>
                    <span>Return Orders</span>
                </h5>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 12px; line-height: 1.35;">Courier Returns, Damage Inspection & Restock Logs</small>
        </div>
        <div>
            <a href="{{ route('admin.order.online') }}" class="btn btn-outline-secondary btn-compact shadow-xs">
                <i class="fas fa-arrow-left me-1"></i>
                <span>Back to Online Orders</span>
            </a>
        </div>
    </div>

    <!-- Search & Filter Toolbar Card -->
    <div class="return-table-card p-2 px-3 mb-3">
        <form action="{{ route('admin.order.return') }}" method="GET" class="row g-2 align-items-center filter-form-wrap">
            <div class="col-12 col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted border-end-0" style="height: 32px;"><i class="fas fa-search" style="font-size: 11px;"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by invoice #, order ID, phone, or customer..." value="{{ request('search') }}" style="height: 32px; font-size: 12px;">
                </div>
            </div>
            <div class="col-12 col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-dark btn-compact shadow-xs px-3">
                    <i class="fas fa-filter me-1"></i>
                    <span>Filter</span>
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.order.return') }}" class="btn btn-outline-secondary btn-compact shadow-xs px-3">
                        <i class="fas fa-rotate-left me-1"></i>
                        <span>Reset</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="return-table-card overflow-hidden">
        <div class="table-responsive-wrapper">
            <table class="table align-middle custom-return-table">
                <thead>
                    <tr>
                        <th class="ps-3 col-ret-order">Order</th>
                        <th class="col-ret-date">Date &amp; Time</th>
                        <th class="col-ret-status">Return Status</th>
                        <th class="col-ret-stock">Stock State</th>
                        <th class="col-ret-customer">Customer</th>
                        <th class="col-ret-courier">Courier Partner</th>
                        <th class="col-ret-proof">Inspection Proof</th>
                        <th class="col-ret-amount">Amount &amp; Charges</th>
                        <th class="col-ret-action text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $status = strtolower($order->order_status ?? 'returned');
                            $statusBadge = match($status) {
                                'returned', 'return' => 'bg-secondary-subtle text-dark border',
                                'return_requested'   => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                default              => 'bg-danger-subtle text-danger border border-danger-subtle'
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
                            <td class="ps-3 col-ret-order">
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
                            <td class="col-ret-date text-nowrap">
                                <span class="text-dark fw-semibold bn-digit d-block" style="font-size: 11.5px;">
                                    <i class="far fa-calendar-alt text-muted me-1" style="font-size: 10px;"></i>{{ date('d M, Y', strtotime($order->created_at)) }}
                                </span>
                                <small class="text-muted font-monospace bn-digit mt-0.5 d-block" style="font-size: 10px;">
                                    <i class="far fa-clock me-1" style="font-size: 9px;"></i>{{ date('h:i A', strtotime($order->created_at)) }}
                                </small>
                            </td>

                            {{-- 3. RETURN STATUS --}}
                            <td class="col-ret-status text-nowrap">
                                <span class="badge {{ $statusBadge }} px-2 py-0.5 text-uppercase fw-bold" style="font-size: 9.5px;">
                                    {{ str_replace('_', ' ', $status) }}
                                </span>
                            </td>

                            {{-- 4. STOCK RECONCILIATION STATE --}}
                            <td class="col-ret-stock text-nowrap">
                                @if(!empty($order->is_stock_restored))
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5" style="font-size: 9.5px;">
                                        <i class="fas fa-check-double me-1"></i> Restocked
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-1.5 py-0.5" style="font-size: 9.5px;">
                                        <i class="fas fa-clock me-1"></i> Pending Restock
                                    </span>
                                @endif
                            </td>

                            {{-- 5. CUSTOMER DETAILS --}}
                            <td class="col-ret-customer overflow-safe-cell">
                                <strong class="text-dark d-block text-truncate" style="font-size: 12px;" title="{{ $order->shipping_name ?? ($order->customer_name ?? ($order->name ?? 'Customer')) }}">
                                    {{ $order->shipping_name ?? ($order->customer_name ?? ($order->name ?? 'Customer')) }}
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

                            {{-- 6. COURIER PARTNER & TRACKING --}}
                            <td class="col-ret-courier text-nowrap">
                                @if(!empty($order->courier_name))
                                    <span class="badge bg-light text-primary border text-capitalize px-1.5 py-0.5 fw-semibold d-inline-block" style="font-size: 9.5px;">
                                        {{ $order->courier_name }}
                                    </span>
                                    @if(!empty($order->courier_tracking_id))
                                        <small class="d-block font-monospace text-muted mt-1 bn-digit text-truncate" style="font-size: 9.5px; max-width: 120px;" title="{{ $order->courier_tracking_id }}">
                                            {{ $order->courier_tracking_id }}
                                        </small>
                                    @endif
                                @else
                                    <span class="text-muted" style="font-size: 11px;">—</span>
                                @endif
                            </td>

                            {{-- 7. GLOBAL MEDIA CHOOSER RETURN / INSPECTION PROOF --}}
                            <td class="col-ret-proof text-center text-nowrap">
                                @if(!empty($proofUrl))
                                    <div class="proof-thumb-box mx-auto" onclick="zoomReturnProof('{{ $proofUrl }}', '{{ addslashes($order->invoice_no ?? $order->id) }}')" title="Click to Zoom Damage/Return Slip">
                                        <img src="{{ $proofUrl }}" alt="Return Proof" onerror="this.src='https://placehold.co/100x100?text=Slip';">
                                    </div>
                                @else
                                    <button type="button" class="btn btn-light border btn-compact py-0.5 px-1.5 text-muted" style="font-size: 9.5px !important;" onclick="attachReturnProofMedia({{ $order->id }})" title="Attach Return Proof from Global Media Library">
                                        <i class="fas fa-paperclip me-1"></i>Attach
                                    </button>
                                @endif
                            </td>

                            {{-- 8. AMOUNT BREAKDOWN --}}
                            <td class="col-ret-amount text-nowrap">
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
                            <td class="col-ret-action text-end pe-3 text-nowrap">
                                <div class="d-inline-flex gap-1 align-items-center justify-content-end">
                                    @if(!empty($custPhone))
                                        <a href="https://wa.me/88{{ preg_replace('/[^0-9]/', '', $custPhone) }}?text=Hello%20{{ urlencode($order->shipping_name ?? 'Customer') }},%20regarding%20your%20return%20order%20%23{{ $order->invoice_no ?? $order->id }}." target="_blank" class="btn btn-outline-success table-action-sq-btn shadow-xs" title="WhatsApp Customer">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.order.view', $order->id) }}" class="btn btn-outline-primary table-action-sq-btn shadow-xs" title="Manage Return &amp; Exchange">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <div class="d-flex flex-column align-items-center justify-content-center py-3">
                                    <i class="fas fa-rotate-left fa-3x mb-2 text-secondary opacity-25"></i>
                                    <h6 class="fw-bold text-secondary mb-1" style="font-size: 14px;">No Return Orders</h6>
                                    <p class="small text-muted mb-0" style="font-size: 12px;">There are no recorded parcel returns matching your filter criteria.</p>
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
<!-- Modal 1: Return Proof Zoom Lightbox Modal                                 -->
<!-- ========================================================================= -->
<div class="modal fade return-modal-scope" id="returnProofZoomModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg text-center p-2">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small" style="font-size: 12.5px;">
                    <i class="fas fa-image text-danger me-1"></i> Return Inspection Proof: <span id="proofOrderRef" class="font-monospace bn-digit"></span>
                </span>
                <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="modalZoomImgTag" src="" alt="Return Proof" class="img-fluid border" style="max-height: 480px; object-fit: contain; border-radius: 4px;">
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal 2: Bootstrap Notice Dialog Modal (Replaces browser alert())         -->
<!-- ========================================================================= -->
<div class="modal fade return-modal-scope" id="returnNoticeModal" tabindex="-1" aria-hidden="true" style="z-index: 106500;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-1">
                <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-2" id="returnNoticeIconWrap" style="width: 44px; height: 44px;">
                    <i class="fas fa-circle-info fs-4" id="returnNoticeIcon"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1" id="returnNoticeTitle" style="font-size: 14px;">Notification</h6>
                <p class="text-muted small mb-3" id="returnNoticeMsg" style="font-size: 12px; line-height: 1.45;"></p>
                <button type="button" class="btn btn-dark btn-compact px-4" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
// Clean Notice Modal Helper (Replaces native alert())
function showReturnNotice(message, title = 'Notification', type = 'info') {
    const titleEl  = document.getElementById('returnNoticeTitle');
    const msgEl    = document.getElementById('returnNoticeMsg');
    const iconWrap = document.getElementById('returnNoticeIconWrap');
    const iconEl   = document.getElementById('returnNoticeIcon');

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

    const modalEl = document.getElementById('returnNoticeModal');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

// Lightbox Zoom Handler for Return Proof
function zoomReturnProof(imgUrl, orderRef) {
    const zoomImgTag = document.getElementById('modalZoomImgTag');
    const titleSpan  = document.getElementById('proofOrderRef');
    if (zoomImgTag && imgUrl) {
        zoomImgTag.src = imgUrl;
        if (titleSpan) titleSpan.innerText = '#' + orderRef;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('returnProofZoomModal')).show();
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

function attachReturnProofMedia(orderId) {
    if (typeof window.openMediaPicker === 'function') {
        window.openMediaPicker({
            multiple: false,
            onSelect: function(media) {
                const picked = extractMediaItem(media);
                if (picked && picked.path) {
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
                        showReturnNotice('Return inspection proof slip attached from Global Media Library.', 'Success', 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch(() => {
                        showReturnNotice('Selected image: ' + picked.path + '. Open order details to save.', 'Notice');
                    });
                }
            }
        });
    } else {
        showReturnNotice('Global Media Chooser is loading or not available on this layout.', 'Notice');
    }
}
</script>
@endsection