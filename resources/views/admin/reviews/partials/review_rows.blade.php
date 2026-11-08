@php
    $svgSafeThumb = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60"><rect fill="%23f8fafc" width="60" height="60"/><text fill="%2394a3b8" font-family="sans-serif" font-size="9" dy="3.5" font-weight="bold" x="50%" y="50%" text-anchor="middle">Product</text></svg>';
@endphp

<style>
    /* =========================================================
       REVIEW TABLE ROW — MODERN ENTERPRISE DESIGN
    ========================================================= */

    .btn-action-square {
        width: 27px !important;
        height: 27px !important;
        min-width: 27px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 10px !important;
        border-radius: 5px !important;
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        transition: background-color .15s ease, color .15s ease, border-color .15s ease, transform .15s ease !important;
        cursor: pointer;
        box-shadow: none !important;
    }

    .btn-action-square:hover {
        transform: translateY(-1px);
    }

    .btn-action-inspect {
        color: #2563eb !important;
    }

    .btn-action-inspect:hover {
        background: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
    }

    .btn-action-pin {
        color: #64748b !important;
    }

    .btn-action-pin.is-pinned {
        color: #d97706 !important;
        border-color: #fde68a !important;
        background: #fffbeb !important;
    }

    .btn-action-pin:hover {
        background: #f59e0b !important;
        border-color: #f59e0b !important;
        color: #ffffff !important;
    }

    .btn-action-approve {
        color: #16a34a !important;
    }

    .btn-action-approve:hover {
        background: #16a34a !important;
        border-color: #16a34a !important;
        color: #ffffff !important;
    }

    .btn-action-delete {
        color: #dc2626 !important;
    }

    .btn-action-delete:hover {
        background: #dc2626 !important;
        border-color: #dc2626 !important;
        color: #ffffff !important;
    }

    /* Checkbox */
    .review-row-check {
        width: 15px;
        height: 15px;
        border-color: #cbd5e1;
        cursor: pointer;
        vertical-align: middle;
    }

    .review-row-check:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    /* Product Identity */
    .review-product-cell {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
    }

    .review-product-image {
        width: 38px;
        height: 38px;
        object-fit: contain;
        flex-shrink: 0;
        padding: 2px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 5px !important;
    }

    .review-product-info {
        min-width: 0;
        flex: 1;
    }

    .review-product-title {
        display: block;
        font-size: 11px;
        font-weight: 750;
        line-height: 1.4;
        color: #1e293b;
        text-decoration: none;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        transition: color .15s ease;
    }

    .review-product-title:hover {
        color: #2563eb !important;
    }

    .review-product-meta {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-top: 4px;
        min-width: 0;
    }

    .review-product-meta small {
        color: #94a3b8;
        font-size: 9px;
        white-space: nowrap;
    }

    .review-product-hub {
        color: #2563eb;
        font-size: 9px;
        font-weight: 750;
        text-decoration: none;
        white-space: nowrap;
    }

    .review-product-hub:hover {
        color: #1d4ed8;
        text-decoration: underline;
    }

    /* Customer Identity */
    .review-customer-name {
        display: block;
        max-width: 100%;
        color: #1e293b;
        font-size: 11px;
        font-weight: 750;
        text-decoration: none;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .review-customer-name:hover {
        color: #2563eb;
    }

    .review-customer-contact {
        display: block;
        margin-top: 4px;
        font-size: 9px;
        color: #94a3b8;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .review-customer-badges {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 4px;
        margin-top: 6px;
    }

    .review-customer-badges .badge {
        font-size: 8px !important;
        font-weight: 750;
        padding: 3px 5px !important;
        line-height: 1.2;
        border-radius: 4px !important;
        white-space: nowrap;
    }

    /* Rating */
    .review-rating-stars {
        display: flex;
        align-items: center;
        gap: 2px;
        white-space: nowrap;
    }

    .review-rating-stars i {
        font-size: 9px;
    }

    .review-rating-number {
        color: #334155;
        font-size: 10px;
        font-weight: 800;
        margin-left: 4px;
    }

    .review-rating-date {
        display: block;
        color: #94a3b8;
        font-size: 9px;
        margin-top: 5px;
        white-space: nowrap;
    }

    /* Review Content */
    .review-content-cell {
        min-width: 0;
        max-width: 100%;
        overflow: hidden;
        white-space: normal !important;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .review-content-title {
        display: block;
        font-size: 10px;
        font-weight: 750;
        color: #1e293b;
        margin-bottom: 4px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .review-clamp-text {
        font-size: 10px;
        line-height: 1.5;
        color: #475569;
        max-width: 100%;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        word-break: break-word;
        white-space: normal !important;
        margin-bottom: 0;
    }

    /* Review Photos */
    .review-photo-list {
        display: flex;
        align-items: center;
        gap: 5px;
        flex-wrap: wrap;
        margin-top: 7px;
    }

    .review-thumb-mini {
        width: 29px;
        height: 29px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
        border-radius: 5px !important;
        cursor: pointer;
        background: #f8fafc;
        transition: transform .15s ease, border-color .15s ease;
    }

    .review-thumb-mini:hover {
        transform: translateY(-2px);
        border-color: #2563eb;
    }

    /* Admin Reply */
    .review-admin-reply {
        margin-top: 7px;
        padding: 6px 8px;
        background: #eff6ff;
        border-left: 3px solid #3b82f6;
        border-radius: 0 5px 5px 0 !important;
        max-width: 100%;
        overflow: hidden;
    }

    .review-admin-reply-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 5px;
        margin-bottom: 3px;
    }

    .review-admin-reply-label {
        color: #2563eb;
        font-size: 9px;
        font-weight: 750;
    }

    .review-admin-reply-auto {
        font-size: 8px !important;
        padding: 2px 5px !important;
        border-radius: 4px !important;
    }

    .review-admin-reply-text {
        display: block;
        color: #475569;
        font-size: 9px;
        line-height: 1.4;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Status */
    .review-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 8px !important;
        font-weight: 800;
        letter-spacing: .035em;
        text-transform: uppercase;
        padding: 5px 7px !important;
        border-radius: 5px !important;
        white-space: nowrap;
    }

    /* Visibility */
    .review-visibility-btn {
        width: 28px;
        height: 28px;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        border-radius: 5px !important;
        background: transparent !important;
        transition: background .15s ease;
    }

    .review-visibility-btn:hover {
        background: #f1f5f9 !important;
    }

    /* Actions */
    .review-actions-toolbar {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 4px;
        white-space: nowrap;
    }

    /* Empty State */
    .review-row-empty {
        padding: 35px 15px !important;
        text-align: center;
        color: #94a3b8;
    }

    .review-row-empty-icon {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: #94a3b8;
        border-radius: 50%;
        font-size: 17px;
        margin-bottom: 9px;
    }

    /* Desktop Table Fit */
    @media (min-width: 1200px) {
        .review-clamp-text {
            -webkit-line-clamp: 2;
        }
    }

    /* Tablet / Mobile */
    @media (max-width: 991.98px) {
        .review-thumb-mini {
            width: 30px;
            height: 30px;
        }
    }

    @media (max-width: 767.98px) {
        .btn-action-square {
            width: 29px !important;
            height: 29px !important;
            min-width: 29px !important;
        }

        .review-product-image {
            width: 36px;
            height: 36px;
        }
    }
</style>

@forelse($reviews as $rev)
    @php
        $rowImages = !empty($rev->images) && is_array($rev->images)
            ? $rev->images
            : (!empty($rev->images_json) ? (json_decode($rev->images_json, true) ?: []) : []);

        $prodImg = !empty($rev->product_image_url)
            ? $rev->product_image_url
            : (!empty($rev->product_thumbnail) ? asset($rev->product_thumbnail) : $svgSafeThumb);
    @endphp

    <tr id="review-row-{{ $rev->id }}">

        {{-- 1. Row Checkbox --}}
        <td class="ps-2 text-center col-rev-chk"
            style="width: 38px; min-width: 38px; white-space: nowrap;">

            <input
                type="checkbox"
                class="form-check-input m-0 review-row-check"
                value="{{ $rev->id }}"
            >
        </td>

        {{-- 2. Product Identity --}}
        <td class="col-rev-product"
            style="width: 220px; min-width: 190px; max-width: 240px; overflow: hidden;">

            <div class="review-product-cell">

                <img
                    src="{{ $prodImg }}"
                    alt="Product"
                    class="review-product-image shadow-xs"
                    onerror="this.onerror=null; this.src='{{ $svgSafeThumb }}'"
                >

                <div class="review-product-info">

                    <a
                        href="{{ route('product.details', $rev->product_slug ?? $rev->product_id) }}"
                        target="_blank"
                        class="review-product-title"
                        title="{{ $rev->product_title }}"
                    >
                        {{ $rev->product_title ?? 'Product #' . $rev->product_id }}
                    </a>

                    <div class="review-product-meta font-monospace">
                        <small>ID: #{{ $rev->product_id }}</small>

                        <a
                            href="{{ route('admin.product.reviews.product', $rev->product_id) }}"
                            class="review-product-hub"
                            title="View Product Review Hub"
                        >
                            [Hub]
                        </a>
                    </div>

                </div>
            </div>
        </td>

        {{-- 3. Customer Identity --}}
        <td class="col-rev-customer"
            style="width: 160px; min-width: 140px; max-width: 170px; overflow: hidden;">

            <div class="min-w-0">

                @if(!empty($rev->user_id))
                    <a
                        href="{{ route('admin.product.reviews.customer', $rev->user_id) }}"
                        class="review-customer-name"
                        title="View Customer Profile"
                    >
                        {{ $rev->customer_name ?? 'Guest Customer' }}
                    </a>
                @else
                    <strong class="review-customer-name">
                        {{ $rev->customer_name ?? 'Guest Customer' }}
                    </strong>
                @endif

                <small class="review-customer-contact font-monospace">
                    {{ $rev->customer_phone ?? ($rev->customer_email ?? 'N/A') }}
                </small>

                <div class="review-customer-badges">

                    @if(!empty($rev->is_verified_purchase))
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            <i class="fas fa-check me-1"></i>
                            Verified Buyer
                        </span>
                    @endif

                    @if(!empty($rev->is_pinned))
                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle">
                            <i class="fas fa-thumbtack me-1 text-warning"></i>
                            #{{ $rev->pin_order ?: 1 }}
                        </span>
                    @endif

                </div>
            </div>
        </td>

        {{-- 4. Rating & Date --}}
        <td class="col-rev-rating"
            style="width: 110px; min-width: 100px; white-space: nowrap;">

            <div class="review-rating-stars font-monospace">
                @for($s = 1; $s <= 5; $s++)
                    <i class="fas fa-star {{ $s <= $rev->rating ? 'text-warning' : 'text-muted opacity-25' }}"></i>
                @endfor

                <span class="review-rating-number">
                    {{ number_format($rev->rating, 1) }}
                </span>
            </div>

            <small class="review-rating-date font-monospace bn-digit">
                {{ date('d M Y', strtotime($rev->created_at)) }}
            </small>
        </td>

        {{-- 5. Review Content --}}
        <td class="col-rev-content"
            style="width: 270px; min-width: 220px; max-width: 300px; overflow: hidden !important; white-space: normal !important; word-break: break-word !important;">

            <div class="review-content-cell">

                @if(!empty($rev->title))
                    <strong class="review-content-title">
                        {{ $rev->title }}
                    </strong>
                @endif

                <div class="review-clamp-text" title="{{ $rev->comment }}">
                    {{ $rev->comment }}
                </div>

                {{-- Review Photos --}}
                @if(count($rowImages) > 0)
                    <div class="review-photo-list">
                        @foreach($rowImages as $img)
                            @php
                                $cleanImg = (str_starts_with($img, 'http') || str_starts_with($img, '/'))
                                    ? $img
                                    : '/' . $img;
                            @endphp

                            <img
                                src="{{ $cleanImg }}"
                                class="review-thumb-mini shadow-xs"
                                alt="Review Photo"
                                onclick="openInspectModal({{ $rev->id }})"
                                title="Click to inspect photo"
                                onerror="this.onerror=null; this.style.display='none'"
                            >
                        @endforeach
                    </div>
                @endif

                {{-- Admin Reply --}}
                @if(!empty($rev->admin_reply))
                    <div class="review-admin-reply">

                        <div class="review-admin-reply-header font-monospace">

                            <span class="review-admin-reply-label">
                                <i class="fas fa-reply me-1"></i>
                                Store Reply
                            </span>

                            @if(!empty($rev->is_auto_replied))
                                <span class="badge bg-info-subtle text-info border border-info-subtle review-admin-reply-auto">
                                    AUTO
                                </span>
                            @endif

                        </div>

                        <div class="review-admin-reply-text">
                            {{ $rev->admin_reply }}
                        </div>

                    </div>
                @endif

            </div>
        </td>

        {{-- 6. Approval Status --}}
        <td class="text-center col-rev-status"
            style="width: 90px; min-width: 90px; white-space: nowrap !important;">

            @php
                $statBadge = match($rev->status) {
                    'approved' => 'bg-success-subtle text-success border border-success-subtle',
                    'rejected' => 'bg-danger-subtle text-danger border border-danger-subtle',
                    default    => 'bg-warning-subtle text-dark border border-warning-subtle'
                };
            @endphp

            <span class="badge {{ $statBadge }} review-status-badge font-monospace">
                {{ $rev->status }}
            </span>
        </td>

        {{-- 7. Storefront Visibility --}}
        <td class="text-center col-rev-visible"
            style="width: 60px; min-width: 60px; white-space: nowrap !important;">

            <button
                type="button"
                class="btn btn-sm p-0 border-0 shadow-none review-visibility-btn"
                onclick="toggleVisibility({{ $rev->id }})"
                title="{{ $rev->is_visible ? 'Visible on storefront' : 'Hidden from storefront' }}"
            >
                <i class="fas {{ $rev->is_visible ? 'fa-eye text-success' : 'fa-eye-slash text-muted' }}"
                   style="font-size: 13px;"></i>
            </button>
        </td>

        {{-- 8. Actions Toolbar --}}
        <td class="text-end pe-2 col-rev-actions"
            style="width: 120px; min-width: 120px; white-space: nowrap !important;">

            <div class="review-actions-toolbar">

                {{-- Inspect & Reply --}}
                <button
                    type="button"
                    class="btn-action-square btn-action-inspect"
                    onclick="openInspectModal({{ $rev->id }})"
                    title="Inspect &amp; Post Reply"
                >
                    <i class="fas fa-edit"></i>
                </button>

                {{-- Toggle Featured Pin --}}
                <button
                    type="button"
                    class="btn-action-square btn-action-pin {{ !empty($rev->is_pinned) ? 'is-pinned' : '' }}"
                    onclick="togglePin({{ $rev->id }})"
                    title="{{ !empty($rev->is_pinned) ? 'Unpin from Top' : 'Pin to Top 5' }}"
                >
                    <i class="fas fa-thumbtack"></i>
                </button>

                {{-- Quick Approve --}}
                @if($rev->status !== 'approved')
                    <button
                        type="button"
                        class="btn-action-square btn-action-approve"
                        onclick="updateReviewStatus({{ $rev->id }}, 'approved')"
                        title="Quick Approve"
                    >
                        <i class="fas fa-check"></i>
                    </button>
                @endif

                {{-- Delete Review --}}
                <button
                    type="button"
                    class="btn-action-square btn-action-delete"
                    onclick="deleteReview({{ $rev->id }})"
                    title="Delete Review"
                >
                    <i class="fas fa-trash-can"></i>
                </button>

            </div>
        </td>

    </tr>

@empty

    <tr>
        <td colspan="8" class="review-row-empty">

            <div class="review-row-empty-icon">
                <i class="fas fa-comments"></i>
            </div>

            <div class="fw-bold text-dark mb-1" style="font-size: 12px;">
                No Reviews Found
            </div>

            <div style="font-size: 10px; color: #94a3b8;">
                No product reviews found matching the selected filters.
            </div>

        </td>
    </tr>

@endforelse