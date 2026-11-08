@forelse($segments as $segment)
    @php
        $campCount = (int)($segment->campaigns_count ?? 0);
        $matchCount = (int)($segment->matching_subscribers_count ?? 0);
        $isDynamic = $segment->type === 'dynamic';
    @endphp

    <tr id="segment-row-{{ $segment->id }}" class="align-middle segment-data-row">

        {{-- 1. Bulk Selection Checkbox --}}
        <td class="text-center">
            <input type="checkbox"
                   class="form-check-input segment-select-cb mt-0 shadow-none"
                   value="{{ $segment->id }}"
                   data-name="{{ $segment->name }}"
                   data-type="{{ $segment->type }}"
                   data-count="{{ $matchCount }}"
                   style="cursor: pointer; width: 15px; height: 15px; border-radius: 4px;">
        </td>

        {{-- 2. Segment ID --}}
        <td class="ps-2 text-center font-monospace bn-digit text-muted">
            <span class="segment-id-label">#{{ $segment->id }}</span>
        </td>

        {{-- 3. Segment Name & Strategic Notes --}}
        <td>
            <div class="d-flex align-items-center gap-2 segment-identity">
                <div class="segment-type-icon {{ $isDynamic ? 'segment-icon-dynamic' : 'segment-icon-snapshot' }} d-flex align-items-center justify-content-center flex-shrink-0">
                    <i class="fas {{ $isDynamic ? 'fa-filter-circle-dollar' : 'fa-camera' }}"></i>
                </div>

                <div class="min-w-0 segment-name-content">
                    <strong class="text-dark d-block text-truncate segment-name-text"
                            title="{{ $segment->name }}">
                        {{ $segment->name }}
                    </strong>

                    <small class="text-muted d-block text-truncate segment-description-text"
                           title="{{ $segment->description ?? 'No custom strategy notes provided.' }}">
                        {{ $segment->description ?? 'No custom strategy notes provided.' }}
                    </small>
                </div>
            </div>
        </td>

        {{-- 4. Rule Type Badge --}}
        <td>
            @if($isDynamic)
                <span class="badge segment-rule-badge segment-rule-dynamic">
                    <i class="fas fa-bolt me-1"></i> Dynamic
                </span>
            @else
                <span class="badge segment-rule-badge segment-rule-snapshot">
                    <i class="fas fa-bookmark me-1"></i> Snapshot
                </span>
            @endif
        </td>

        {{-- 5. Matching Audience Count with 1-Click Recalculate --}}
        <td class="text-center font-monospace">
            <div class="d-inline-flex align-items-center justify-content-center gap-1 segment-reach-control">
                <span class="badge segment-count-badge bn-digit font-monospace"
                      id="segment-count-{{ $segment->id }}">
                    {{ number_format($matchCount) }}
                </span>

                <button type="button"
                        class="btn btn-sm btn-light border segment-icon-action segment-recalculate-btn"
                        onclick="recalculateSegmentAjax({{ $segment->id }})"
                        title="Recalculate audience count now">
                    <i class="fas fa-rotate"
                       id="recalc-icon-{{ $segment->id }}"></i>
                </button>
            </div>
        </td>

        {{-- 6. Used in Campaigns Count --}}
        <td class="text-center font-monospace bn-digit">
            @if($campCount > 0)
                <span class="badge segment-campaign-badge segment-campaign-active">
                    <i class="fas fa-bullhorn me-1"></i>
                    {{ $campCount }} {{ \Illuminate\Support\Str::plural('campaign', $campCount) }}
                </span>
            @else
                <span class="badge segment-campaign-badge segment-campaign-empty">
                    0 campaigns
                </span>
            @endif
        </td>

        {{-- 7. Last Calculated Timestamp --}}
        <td class="font-monospace bn-digit">
            <div id="segment-last-calc-{{ $segment->id }}" class="segment-calculation-info">
                @if($segment->last_calculated_at)
                    <span class="text-dark d-block segment-calculation-date">
                        {{ $segment->last_calculated_at->format('M d, Y') }}
                    </span>

                    <small class="text-muted d-block segment-calculation-time">
                        {{ $segment->last_calculated_at->format('h:i A') }}
                    </small>
                @else
                    <span class="badge segment-pending-badge">
                        Pending sync
                    </span>
                @endif
            </div>
        </td>

        {{-- 8. Row Action Buttons --}}
        <td class="text-end pe-3">
            <div class="d-inline-flex align-items-center justify-content-end gap-1 segment-row-actions">

                {{-- Quick Target Campaign Launcher --}}
                <a href="{{ route('admin.email_marketing.campaigns.create', ['segment_id' => $segment->id]) }}"
                   class="btn btn-sm btn-light border segment-icon-action segment-campaign-action"
                   title="Launch Targeted Campaign with this Segment">
                    <i class="fas fa-paper-plane"></i>
                </a>

                {{-- Edit Segment & Criteria Rules --}}
                <a href="{{ route('admin.email_marketing.segments.edit', $segment->id) }}"
                   class="btn btn-sm btn-light border segment-icon-action segment-edit-action"
                   title="Edit Segment & Filter Rules">
                    <i class="fas fa-pencil"></i>
                </a>

                {{-- Delete Segment AJAX --}}
                <button type="button"
                        class="btn btn-sm btn-light border segment-icon-action segment-delete-action"
                        onclick="deleteSegmentAjax({{ $segment->id }}, '{{ addslashes($segment->name) }}')"
                        title="Delete Segment Record">
                    <i class="fas fa-trash-can"></i>
                </button>
            </div>
        </td>
    </tr>

@empty
    <tr>
        <td colspan="8" class="text-center py-5">
            <div class="segment-empty-state">
                <div class="segment-empty-icon">
                    <i class="fas fa-filter-circle-xmark"></i>
                </div>

                <span class="fw-semibold text-dark d-block segment-empty-title">
                    No audience segments found
                </span>

                <span class="text-muted d-block segment-empty-description">
                    No segments match your selected filter criteria. Try adjusting the search parameters.
                </span>
            </div>
        </td>
    </tr>
@endforelse

<style>
    /* Audience Segment Table Row Styling */
    #email-marketing-segments-root .segment-data-row {
        transition: background-color 0.15s ease;
    }

    #email-marketing-segments-root .segment-data-row:hover {
        background-color: #f8fafc;
    }

    #email-marketing-segments-root .segment-data-row > td {
        vertical-align: middle;
    }

    #email-marketing-segments-root .segment-id-label {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
    }

    /* Segment Identity */
    #email-marketing-segments-root .segment-type-icon {
        width: 32px;
        height: 32px;
        border-radius: 5px;
        font-size: 12px;
    }

    #email-marketing-segments-root .segment-icon-dynamic {
        background: #eff6ff;
        color: #2563eb;
    }

    #email-marketing-segments-root .segment-icon-snapshot {
        background: #f1f5f9;
        color: #64748b;
    }

    #email-marketing-segments-root .segment-name-content {
        line-height: 1.4;
    }

    #email-marketing-segments-root .segment-name-text {
        max-width: 250px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.4;
    }

    #email-marketing-segments-root .segment-description-text {
        max-width: 260px;
        margin-top: 2px;
        font-size: 10px;
        line-height: 1.35;
    }

    /* Rule Type Badges */
    #email-marketing-segments-root .segment-rule-badge,
    #email-marketing-segments-root .segment-count-badge,
    #email-marketing-segments-root .segment-campaign-badge,
    #email-marketing-segments-root .segment-pending-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        font-family: inherit;
        font-weight: 600;
        white-space: nowrap;
    }

    #email-marketing-segments-root .segment-rule-badge {
        padding: 5px 8px;
        font-size: 9.5px;
    }

    #email-marketing-segments-root .segment-rule-dynamic {
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid #dbeafe;
    }

    #email-marketing-segments-root .segment-rule-snapshot {
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }

    /* Audience Reach */
    #email-marketing-segments-root .segment-count-badge {
        min-width: 48px;
        padding: 5px 8px;
        background: #f8fafc;
        color: #1e293b;
        border: 1px solid #e2e8f0;
        font-size: 11px;
    }

    #email-marketing-segments-root .segment-icon-action {
        width: 27px;
        height: 27px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        font-size: 10px;
        line-height: 1;
        transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }

    #email-marketing-segments-root .segment-recalculate-btn {
        color: #2563eb;
        background: #ffffff;
    }

    #email-marketing-segments-root .segment-recalculate-btn:hover {
        color: #ffffff;
        background: #2563eb;
        border-color: #2563eb !important;
    }

    /* Campaign Usage */
    #email-marketing-segments-root .segment-campaign-badge {
        padding: 5px 7px;
        font-size: 10px;
    }

    #email-marketing-segments-root .segment-campaign-active {
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid #dbeafe;
    }

    #email-marketing-segments-root .segment-campaign-empty {
        background: #f8fafc;
        color: #94a3b8;
        border: 1px solid #e2e8f0;
    }

    /* Calculation Information */
    #email-marketing-segments-root .segment-calculation-info {
        line-height: 1.4;
    }

    #email-marketing-segments-root .segment-calculation-date {
        font-size: 11px;
        font-weight: 600;
    }

    #email-marketing-segments-root .segment-calculation-time {
        margin-top: 2px;
        font-size: 9.5px;
    }

    #email-marketing-segments-root .segment-pending-badge {
        padding: 4px 7px;
        background: #f8fafc;
        color: #64748b;
        border: 1px solid #e2e8f0;
        font-size: 9.5px;
    }

    /* Row Action Buttons */
    #email-marketing-segments-root .segment-campaign-action {
        color: #0891b2;
        background: #ffffff;
    }

    #email-marketing-segments-root .segment-campaign-action:hover {
        color: #ffffff;
        background: #0891b2;
        border-color: #0891b2 !important;
    }

    #email-marketing-segments-root .segment-edit-action {
        color: #2563eb;
        background: #ffffff;
    }

    #email-marketing-segments-root .segment-edit-action:hover {
        color: #ffffff;
        background: #2563eb;
        border-color: #2563eb !important;
    }

    #email-marketing-segments-root .segment-delete-action {
        color: #dc2626;
        background: #ffffff;
    }

    #email-marketing-segments-root .segment-delete-action:hover {
        color: #ffffff;
        background: #dc2626;
        border-color: #dc2626 !important;
    }

    /* Empty State */
    #email-marketing-segments-root .segment-empty-state {
        max-width: 420px;
        margin: 0 auto;
        padding: 12px 10px;
    }

    #email-marketing-segments-root .segment-empty-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #f8fafc;
        color: #94a3b8;
        font-size: 21px;
    }

    #email-marketing-segments-root .segment-empty-title {
        margin-bottom: 5px;
        font-size: 13px;
    }

    #email-marketing-segments-root .segment-empty-description {
        font-size: 11px;
        line-height: 1.6;
    }

    /* Tablet & Mobile */
    @media (max-width: 991.98px) {
        #email-marketing-segments-root .segment-name-text {
            max-width: 220px;
        }

        #email-marketing-segments-root .segment-description-text {
            max-width: 230px;
        }
    }

    @media (max-width: 767.98px) {
        #email-marketing-segments-root .segment-type-icon {
            width: 29px;
            height: 29px;
            font-size: 11px;
        }

        #email-marketing-segments-root .segment-name-text {
            max-width: 190px;
            font-size: 11.5px;
        }

        #email-marketing-segments-root .segment-description-text {
            max-width: 200px;
            font-size: 9.5px;
        }

        #email-marketing-segments-root .segment-rule-badge {
            padding: 5px 7px;
            font-size: 9px;
        }

        #email-marketing-segments-root .segment-count-badge {
            min-width: 42px;
            padding: 5px 6px;
            font-size: 10px;
        }

        #email-marketing-segments-root .segment-campaign-badge {
            font-size: 9px;
        }

        #email-marketing-segments-root .segment-icon-action {
            width: 26px;
            height: 26px;
        }
    }
</style>