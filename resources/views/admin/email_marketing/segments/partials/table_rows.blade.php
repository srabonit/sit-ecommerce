@forelse($segments as $segment)
    @php
        $campCount = (int)($segment->campaigns_count ?? 0);
        $matchCount = (int)($segment->matching_subscribers_count ?? 0);
        $isDynamic = $segment->type === 'dynamic';
    @endphp
    <tr id="segment-row-{{ $segment->id }}" class="align-middle">
        {{-- 1. Bulk Selection Checkbox --}}
        <td class="text-center">
            <input type="checkbox" 
                   class="form-check-input segment-select-cb mt-0 shadow-none" 
                   value="{{ $segment->id }}" 
                   data-name="{{ $segment->name }}"
                   data-type="{{ $segment->type }}"
                   data-count="{{ $matchCount }}"
                   style="cursor: pointer; width: 15px; height: 15px; border-radius: 5px !important;">
        </td>

        {{-- 2. Segment ID --}}
        <td class="ps-2 text-center font-monospace bn-digit text-muted" style="font-size: 11px;">
            #{{ $segment->id }}
        </td>

        {{-- 3. Segment Name & Strategic Notes --}}
        <td>
            <div class="d-flex align-items-center gap-2">
                <div class="rounded {{ $isDynamic ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-secondary' }} d-flex align-items-center justify-content-center fw-bold text-uppercase flex-shrink-0 shadow-xs" style="width: 30px; height: 30px; border-radius: 5px !important;">
                    <i class="fas {{ $isDynamic ? 'fa-filter-circle-dollar' : 'fa-camera' }}" style="font-size: 11px;"></i>
                </div>
                <div class="min-w-0" style="line-height: 1.3;">
                    <strong class="text-dark d-block text-truncate" style="font-size: 12px; max-width: 240px;" title="{{ $segment->name }}">
                        {{ $segment->name }}
                    </strong>
                    <small class="text-muted font-monospace d-block text-truncate" style="font-size: 10px; max-width: 250px;" title="{{ $segment->description ?? 'No custom strategy notes provided.' }}">
                        {{ $segment->description ?? 'No custom strategy notes provided.' }}
                    </small>
                </div>
            </div>
        </td>

        {{-- 4. Rule Type Badge --}}
        <td>
            @if($isDynamic)
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 9.5px;">
                    <i class="fas fa-bolt me-1"></i> Dynamic
                </span>
            @else
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 9.5px;">
                    <i class="fas fa-bookmark me-1"></i> Snapshot
                </span>
            @endif
        </td>

        {{-- 5. Matching Audience Count with 1-Click Recalculate --}}
        <td class="text-center font-monospace">
            <div class="d-inline-flex align-items-center gap-1">
                <span class="badge bg-light text-dark border px-2 py-1 fw-bold bn-digit font-monospace" id="segment-count-{{ $segment->id }}" style="border-radius: 5px !important; font-size: 11.5px;">
                    {{ number_format($matchCount) }}
                </span>
                <button type="button" 
                        class="btn btn-sm btn-light border p-0 text-primary shadow-xs d-inline-flex align-items-center justify-content-center" 
                        onclick="recalculateSegmentAjax({{ $segment->id }})" 
                        style="width: 24px; height: 24px; border-radius: 5px !important;" 
                        title="Recalculate audience count now">
                    <i class="fas fa-rotate" id="recalc-icon-{{ $segment->id }}" style="font-size: 10px;"></i>
                </button>
            </div>
        </td>

        {{-- 6. Used in Campaigns Count --}}
        <td class="text-center font-monospace bn-digit">
            @if($campCount > 0)
                <span class="badge bg-light text-primary border font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 10.5px;">
                    <i class="fas fa-bullhorn me-1 text-primary"></i> {{ $campCount }} {{ \Illuminate\Support\Str::plural('campaign', $campCount) }}
                </span>
            @else
                <span class="badge bg-light text-muted border font-monospace px-2 py-1" style="border-radius: 5px !important; font-size: 10px;">
                    0 campaigns
                </span>
            @endif
        </td>

        {{-- 7. Last Calculated Timestamp --}}
        <td class="font-monospace bn-digit" style="line-height: 1.3;">
            <div id="segment-last-calc-{{ $segment->id }}">
                @if($segment->last_calculated_at)
                    <span class="text-dark d-block" style="font-size: 11px;">
                        {{ $segment->last_calculated_at->format('M d, Y') }}
                    </span>
                    <small class="text-muted" style="font-size: 9.5px;">
                        {{ $segment->last_calculated_at->format('h:i A') }}
                    </small>
                @else
                    <span class="badge bg-light text-muted border font-monospace px-2 py-0" style="border-radius: 5px !important; font-size: 9.5px;">
                        Pending sync
                    </span>
                @endif
            </div>
        </td>

        {{-- 8. Row Action Buttons --}}
        <td class="text-end pe-3">
            <div class="d-inline-flex align-items-center gap-1">
                {{-- Quick Target Campaign Launcher --}}
                <a href="{{ route('admin.email_marketing.campaigns.create', ['segment_id' => $segment->id]) }}" 
                   class="btn btn-sm btn-light border p-1 text-info shadow-xs d-inline-flex align-items-center justify-content-center" 
                   style="width: 26px; height: 26px; border-radius: 5px !important;" 
                   title="Launch Targeted Campaign with this Segment">
                    <i class="fas fa-paper-plane" style="font-size: 10px;"></i>
                </a>

                {{-- Edit Segment & Criteria Rules --}}
                <a href="{{ route('admin.email_marketing.segments.edit', $segment->id) }}" 
                   class="btn btn-sm btn-light border p-1 text-primary shadow-xs d-inline-flex align-items-center justify-content-center" 
                   style="width: 26px; height: 26px; border-radius: 5px !important;" 
                   title="Edit Segment & Filter Rules">
                    <i class="fas fa-pencil" style="font-size: 10px;"></i>
                </a>

                {{-- Delete Segment AJAX --}}
                <button type="button" 
                        class="btn btn-sm btn-light border p-1 text-danger shadow-xs d-inline-flex align-items-center justify-content-center" 
                        style="width: 26px; height: 26px; border-radius: 5px !important;" 
                        onclick="deleteSegmentAjax({{ $segment->id }}, '{{ addslashes($segment->name) }}')" 
                        title="Delete Segment Record">
                    <i class="fas fa-trash-can" style="font-size: 10px;"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center py-5 text-muted small">
            <i class="fas fa-filter-circle-xmark fa-2x mb-2 d-block text-secondary opacity-25"></i>
            <span class="fw-semibold text-dark d-block mb-1">No audience segments found</span>
            <span>No segments match your selected filter criteria. Try adjusting the search parameters.</span>
        </td>
    </tr>
@endforelse