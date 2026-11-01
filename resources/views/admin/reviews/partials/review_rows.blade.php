@php
    $svgSafeThumb = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60"><rect fill="%23f8fafc" width="60" height="60"/><text fill="%2394a3b8" font-family="sans-serif" font-size="9" dy="3.5" font-weight="bold" x="50%" y="50%" text-anchor="middle">Product</text></svg>';
@endphp

<style>
    /* Scoped Action Buttons - Zero Hover Clash & Strict 5px Radius */
    .btn-action-square {
        width: 26px !important;
        height: 26px !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 10px !important;
        border-radius: 5px !important;
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        transition: all 0.15s ease !important;
        cursor: pointer;
    }
    .btn-action-inspect {
        color: #2563eb !important;
    }
    .btn-action-inspect:hover {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #ffffff !important;
    }
    .btn-action-pin {
        color: #64748b !important;
    }
    .btn-action-pin.is-pinned {
        color: #f59e0b !important;
        border-color: #fde68a !important;
        background-color: #fffbeb !important;
    }
    .btn-action-pin:hover {
        background-color: #f59e0b !important;
        border-color: #f59e0b !important;
        color: #ffffff !important;
    }
    .btn-action-approve {
        color: #16a34a !important;
    }
    .btn-action-approve:hover {
        background-color: #16a34a !important;
        border-color: #16a34a !important;
        color: #ffffff !important;
    }
    .btn-action-delete {
        color: #dc2626 !important;
    }
    .btn-action-delete:hover {
        background-color: #dc2626 !important;
        border-color: #dc2626 !important;
        color: #ffffff !important;
    }

    /* Comment Text Box Clamp - Never Overflows or Overlaps Status Badge */
    .review-clamp-text {
        font-size: 11px;
        line-height: 1.45;
        max-width: 100%;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        word-break: break-word;
        white-space: normal !important;
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
        <!-- 1. Row Checkbox -->
        <td class="ps-2 text-center col-rev-chk" style="width: 38px; min-width: 38px; white-space: nowrap;">
            <input type="checkbox" class="form-check-input m-0 review-row-check" value="{{ $rev->id }}">
        </td>

        <!-- 2. Product Identity & Hub Link -->
        <td class="col-rev-product" style="width: 220px; min-width: 190px; max-width: 240px; overflow: hidden;">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <img src="{{ $prodImg }}" class="border shadow-xs" style="width: 34px; height: 34px; object-fit: contain; background: #ffffff; border-radius: 5px !important; flex-shrink: 0;" onerror="this.onerror=null; this.src='{{ $svgSafeThumb }}'">
                <div class="overflow-hidden min-w-0 flex-grow-1" style="max-width: 180px;">
                    <a href="{{ route('product.details', $rev->product_slug ?? $rev->product_id) }}" target="_blank" class="fw-bold text-dark text-truncate d-block text-decoration-none" style="font-size: 12px; line-height: 1.3;" title="{{ $rev->product_title }}">
                        {{ $rev->product_title ?? 'Product #' . $rev->product_id }}
                    </a>
                    <div class="d-flex align-items-center gap-1 font-monospace mt-1">
                        <small class="text-muted" style="font-size: 10px;">ID: #{{ $rev->product_id }}</small>
                        <a href="{{ route('admin.product.reviews.product', $rev->product_id) }}" class="text-primary text-decoration-none fw-bold" style="font-size: 10px;" title="View Product Review Hub">[Hub]</a>
                    </div>
                </div>
            </div>
        </td>

        <!-- 3. Customer Identity & Badges -->
        <td class="col-rev-customer" style="width: 160px; min-width: 140px; max-width: 170px; overflow: hidden;">
            <div class="min-w-0">
                @if(!empty($rev->user_id))
                    <a href="{{ route('admin.product.reviews.customer', $rev->user_id) }}" class="text-dark fw-bold d-block text-truncate text-decoration-none" style="font-size: 12px; max-width: 150px;" title="View Customer Profile">
                        {{ $rev->customer_name ?? 'Guest Customer' }}
                    </a>
                @else
                    <strong class="text-dark d-block text-truncate" style="font-size: 12px; max-width: 150px;">{{ $rev->customer_name ?? 'Guest Customer' }}</strong>
                @endif
                <small class="text-muted d-block font-monospace text-truncate" style="font-size: 10px;">{{ $rev->customer_phone ?? ($rev->customer_email ?? 'N/A') }}</small>
                <div class="d-flex gap-1 align-items-center mt-1 flex-wrap">
                    @if(!empty($rev->is_verified_purchase))
                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-1 py-0" style="font-size: 9px; border-radius: 4px !important;">
                            <i class="fas fa-check me-1"></i>Verified Buyer
                        </span>
                    @endif
                    @if(!empty($rev->is_pinned))
                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle font-monospace px-1 py-0" style="font-size: 9px; border-radius: 4px !important;">
                            <i class="fas fa-thumbtack me-1 text-warning"></i>#{{ $rev->pin_order ?: 1 }}
                        </span>
                    @endif
                </div>
            </div>
        </td>

        <!-- 4. Rating Stars & Date -->
        <td class="col-rev-rating" style="width: 110px; min-width: 100px; white-space: nowrap;">
            <div class="d-flex align-items-center gap-1 font-monospace">
                @for($s = 1; $s <= 5; $s++)
                    <i class="fas fa-star {{ $s <= $rev->rating ? 'text-warning' : 'text-muted opacity-25' }}" style="font-size: 10px;"></i>
                @endfor
                <span class="text-dark fw-bold ms-1" style="font-size: 10px;">{{ number_format($rev->rating, 1) }}</span>
            </div>
            <small class="text-muted font-monospace d-block mt-1 bn-digit" style="font-size: 10px;">
                {{ date('d M Y', strtotime($rev->created_at)) }}
            </small>
        </td>

        <!-- 5. Review Content (Strictly Confined - Never bleeds into Status Column) -->
        <td class="col-rev-content" style="width: 270px; min-width: 220px; max-width: 300px; overflow: hidden !important; white-space: normal !important; word-break: break-word !important;">
            @if(!empty($rev->title))
                <strong class="d-block text-dark mb-1 text-truncate" style="font-size: 11px; max-width: 100%;">{{ $rev->title }}</strong>
            @endif
            <div class="text-dark mb-1 review-clamp-text" title="{{ $rev->comment }}">
                {{ $rev->comment }}
            </div>

            @if(count($rowImages) > 0)
                <div class="d-flex gap-1 mt-1 mb-1 flex-wrap">
                    @foreach($rowImages as $img)
                        @php
                            $cleanImg = (str_starts_with($img, 'http') || str_starts_with($img, '/')) ? $img : '/' . $img;
                        @endphp
                        <img src="{{ $cleanImg }}" class="review-thumb-mini shadow-xs" style="width: 28px; height: 28px; object-fit: cover; border: 1px solid #cbd5e1; border-radius: 4px !important;" onclick="openInspectModal({{ $rev->id }})" title="Click to inspect photo" onerror="this.onerror=null; this.style.display='none'">
                    @endforeach
                </div>
            @endif

            @if(!empty($rev->admin_reply))
                <div class="mt-1 p-1.5 px-2 bg-light border-start border-3 border-primary" style="font-size: 10px; line-height: 1.35; border-radius: 0 4px 4px 0 !important; max-width: 100%; overflow: hidden;">
                    <div class="d-flex align-items-center justify-content-between mb-0 font-monospace">
                        <span class="text-primary fw-bold"><i class="fas fa-reply me-1"></i>Store Reply:</span>
                        @if(!empty($rev->is_auto_replied))
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-1 py-0" style="font-size: 8px;">AUTO</span>
                        @endif
                    </div>
                    <div class="text-dark text-truncate" style="max-width: 100%; font-size: 10px;">{{ $rev->admin_reply }}</div>
                </div>
            @endif
        </td>

        <!-- 6. Approval Status -->
        <td class="text-center col-rev-status" style="width: 90px; min-width: 90px; white-space: nowrap !important;">
            @php
                $statBadge = match($rev->status) {
                    'approved' => 'bg-success-subtle text-success border border-success-subtle',
                    'rejected' => 'bg-danger-subtle text-danger border border-danger-subtle',
                    default    => 'bg-warning-subtle text-dark border border-warning-subtle'
                };
            @endphp
            <span class="badge {{ $statBadge }} text-uppercase font-monospace px-2 py-1" style="font-size: 9.5px; border-radius: 4px !important;">
                {{ $rev->status }}
            </span>
        </td>

        <!-- 7. Storefront Visibility -->
        <td class="text-center col-rev-visible" style="width: 60px; min-width: 60px; white-space: nowrap !important;">
            <button type="button" class="btn btn-sm p-0 border-0 shadow-none" onclick="toggleVisibility({{ $rev->id }})" title="{{ $rev->is_visible ? 'Visible on storefront' : 'Hidden from storefront' }}">
                <i class="fas {{ $rev->is_visible ? 'fa-eye text-success' : 'fa-eye-slash text-muted' }}" style="font-size: 13px;"></i>
            </button>
        </td>

        <!-- 8. Actions Toolbar -->
        <td class="text-end pe-2 col-rev-actions" style="width: 120px; min-width: 120px; white-space: nowrap !important;">
            <div class="d-inline-flex gap-1 align-items-center justify-content-end">
                <!-- Inspect & Reply Modal -->
                <button type="button" class="btn-action-square btn-action-inspect shadow-xs" onclick="openInspectModal({{ $rev->id }})" title="Inspect &amp; Post Reply">
                    <i class="fas fa-edit"></i>
                </button>

                <!-- Toggle Featured Pin -->
                <button type="button" class="btn-action-square btn-action-pin shadow-xs {{ !empty($rev->is_pinned) ? 'is-pinned' : '' }}" onclick="togglePin({{ $rev->id }})" title="{{ !empty($rev->is_pinned) ? 'Unpin from Top' : 'Pin to Top 5' }}">
                    <i class="fas fa-thumbtack"></i>
                </button>

                <!-- Quick Approve (if not approved) -->
                @if($rev->status !== 'approved')
                    <button type="button" class="btn-action-square btn-action-approve shadow-xs" onclick="updateReviewStatus({{ $rev->id }}, 'approved')" title="Quick Approve">
                        <i class="fas fa-check"></i>
                    </button>
                @endif

                <!-- Delete Review -->
                <button type="button" class="btn-action-square btn-action-delete shadow-xs" onclick="deleteReview({{ $rev->id }})" title="Delete Review">
                    <i class="fas fa-trash-can"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center py-4 text-muted small font-monospace" style="font-size: 11px;">
            <i class="fas fa-comments fa-2x mb-2 d-block opacity-25"></i>
            No product reviews found matching the selected filters.
        </td>
    </tr>
@endforelse