@forelse($reviews as $rev)
    @php
        $isMyReview = auth()->check() && ($rev->user_id == auth()->id());
        $revImages = !empty($rev->images_json) ? (json_decode($rev->images_json, true) ?: []) : [];
    @endphp
    <div class="modern-review-card border-bottom pb-3">
        <div class="d-flex justify-content-between align-items-start mb-1.5">
            <div class="d-flex align-items-center gap-2">
                <div class="review-avatar-circle">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                        <strong class="pdp-review-author text-dark small" style="font-size: 13px;">{{ $rev->user_name }}</strong>
                        @if($isMyReview)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 8.5px; padding: 2px 5px;">You</span>
                        @endif
                        @if(!empty($rev->is_verified_purchase))
                            <span class="badge pdp-review-verified-badge verified-buyer-badge font-monospace" style="font-size: 9px;">
                                <i class="fas fa-check-circle me-0.5"></i> Verified Purchase
                            </span>
                        @endif
                        @if(!empty($rev->is_pinned))
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle font-monospace" style="font-size: 8.5px;">
                                <i class="fas fa-thumbtack me-0.5"></i> Featured Review
                            </span>
                        @endif
                    </div>

                    <div class="small mt-0.5 d-flex align-items-center gap-1" style="font-size: 11px;">
                        @for($r = 1; $r <= 5; $r++)
                            <i class="fas fa-star" style="color: {{ $r <= $rev->rating ? 'var(--pdp-review-star, #f59e0b)' : 'rgba(148, 163, 184, 0.3)' }};"></i>
                        @endfor
                        <strong class="text-dark ms-1" style="font-size: 11px;">{{ number_format($rev->rating, 1) }}</strong>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <small class="pdp-review-date font-monospace text-muted" style="font-size: 10.5px;">{{ date('d M Y', strtotime($rev->created_at)) }}</small>
                @if($isMyReview)
                    <button type="button" class="btn-edit-review-pill" onclick="toggleReviewForm(true)">
                        <i class="fas fa-pen-to-square"></i> Edit
                    </button>
                @endif
            </div>
        </div>

        <p class="pdp-review-comment text-dark mb-2 mt-1" style="font-size: 12.5px; line-height: 1.55;">{{ $rev->comment }}</p>

        <!-- Customer Attached Photos -->
        @if(count($revImages) > 0 && (!isset($rev->images_visible) || $rev->images_visible))
            <div class="d-flex gap-2 flex-wrap mb-2">
                @foreach($revImages as $img)
                    <img src="{{ asset($img) }}" alt="Customer Review Photo" class="review-attached-img review-image-thumbnail" onclick="openReviewImageLightbox('{{ asset($img) }}')" onerror="this.onerror=null; this.style.display='none'">
                @endforeach
            </div>
        @endif

        <!-- Admin Response Box -->
        @if(!empty($rev->admin_reply) && (!isset($rev->is_reply_visible) || $rev->is_reply_visible))
            <div class="p-2.5 rounded-3 bg-light border-start border-3 border-primary mb-2 mt-1" style="font-size: 11.5px; line-height: 1.45;">
                <div class="d-flex align-items-center gap-1 fw-bold text-primary mb-0.5" style="font-size: 11px;">
                    <i class="fas fa-reply"></i>
                    <span>Store Official Reply:</span>
                </div>
                <div class="text-dark">{{ $rev->admin_reply }}</div>
            </div>
        @endif

        <!-- Helpful & Report Action Controls -->
        <div class="d-flex align-items-center justify-content-between pt-1 text-muted" style="font-size: 11px;">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-sm btn-light border py-0 px-2 text-muted fw-semibold" onclick="markReviewHelpful({{ $rev->id }}, this)" style="font-size: 10.5px; border-radius: 4px;">
                    <i class="far fa-thumbs-up me-1"></i> Helpful (<span class="helpful-num">{{ $rev->helpful_count ?? 0 }}</span>)
                </button>
                <button type="button" class="btn btn-sm btn-link text-muted p-0 text-decoration-none" onclick="if(typeof openReportModal === 'function'){ openReportModal({{ $rev->id }}); } else if(typeof reportReviewModal === 'function'){ reportReviewModal({{ $rev->id }}); }" style="font-size: 10px;">
                    <i class="far fa-flag me-0.5"></i> Report
                </button>
            </div>
            <span style="font-size: 10px;">Posted on {{ config('app.name', 'Store') }}</span>
        </div>
    </div>
@empty
    <div class="text-center py-4 text-muted small" style="font-size: 12px;">
        <i class="far fa-comment-dots fa-2x mb-2 d-block opacity-50"></i>
        No reviews found matching your filter selection.
    </div>
@endforelse