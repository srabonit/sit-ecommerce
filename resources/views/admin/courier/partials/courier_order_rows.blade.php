@forelse($bookings as $b)
    @php
        $st = strtolower($b->delivery_status ?? 'in_review');
        $deliveryBadgeClass = match($st) {
            'delivered', 'completed'           => 'badge-soft-success',
            'in_transit', 'shipped', 'picked' => 'badge-soft-info',
            'returned', 'return'               => 'badge-soft-danger',
            'cancelled'                        => 'badge-soft-danger',
            default                            => 'badge-soft-warning'
        };

        $codSt = strtolower($b->cod_settlement_status ?? 'unsettled');
        $codBadgeClass = match($codSt) {
            'settled' => 'badge-soft-success',
            'partial' => 'badge-soft-warning',
            default   => 'badge-soft-danger'
        };

        $provName = strtolower($b->courier_name ?? 'manual');
        $isManual = !str_contains($provName, 'steadfast') && !str_contains($provName, 'pathao') && !str_contains($provName, 'redx');
        
        $providerIcon = str_contains($provName, 'steadfast') 
            ? 'fa-bolt text-warning' 
            : (str_contains($provName, 'pathao') ? 'fa-motorcycle text-danger' : 'fa-truck text-secondary');

        // Resolve POD consignment proof slip supporting Global Media Library
        $proofUrl = null;
        $rawSlip = $b->consignment_slip ?? ($b->screenshot ?? null);
        if (!empty($rawSlip) && $rawSlip !== 'undefined' && $rawSlip !== 'null') {
            $cleanPath = ltrim(str_replace(['public_html/', 'public/'], '', $rawSlip), '/');
            if (class_exists(\App\Services\ImagePathService::class)) {
                $proofUrl = \App\Services\ImagePathService::resolveUrl($rawSlip, '');
            } elseif (function_exists('show_image')) {
                $proofUrl = show_image($rawSlip, '');
            } elseif (filter_var($rawSlip, FILTER_VALIDATE_URL)) {
                $proofUrl = $rawSlip;
            } elseif (file_exists(public_path($cleanPath))) {
                $proofUrl = asset($cleanPath);
            } elseif (file_exists(public_path('uploads/' . $cleanPath))) {
                $proofUrl = asset('uploads/' . $cleanPath);
            } elseif (file_exists(public_path('storage/' . $cleanPath))) {
                $proofUrl = asset('storage/' . $cleanPath);
            } else {
                $proofUrl = asset($cleanPath);
            }
        }
    @endphp
    <tr class="align-middle border-bottom" id="courier-row-{{ $b->id }}">
        <!-- 1. Row Selection Checkbox -->
        <td class="text-center py-2 px-1 col-w-chk">
            <input type="checkbox" class="booking-row-checkbox form-check-input mt-0 shadow-none" value="{{ $b->id }}" onchange="onRowCheckboxChange()" style="border-radius: 5px !important; cursor: pointer; width: 14px; height: 14px;">
        </td>

        <!-- 2. Booking ID -->
        <td class="font-monospace text-muted fw-bold py-2 px-1 text-center text-nowrap bn-digit col-w-id" style="font-size: 11px;">
            #{{ $b->id }}
        </td>

        <!-- 3. Invoice & Channel Source -->
        <td class="py-2 px-1 col-w-inv" style="overflow: hidden;">
            <div class="min-w-0">
                <a href="{{ route('admin.order.view', $b->order_id) }}" class="fw-bold text-primary font-monospace text-decoration-none d-block text-truncate bn-digit" style="font-size: 11px;" title="#{{ $b->invoice_no ?? $b->order_id }}">
                    #{{ $b->invoice_no ?? $b->order_id }}
                </a>
                <div class="d-flex align-items-center gap-1 mt-1 text-nowrap min-w-0">
                    <span class="badge {{ ($b->order_source ?? '') === 'pos' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' }} px-1 py-0 font-monospace" style="font-size: 8.5px; border-radius: 5px !important;">
                        {{ strtoupper($b->order_source ?? 'ONLINE') }}
                    </span>
                    <small class="text-muted bn-digit text-truncate font-monospace" style="font-size: 9px;">{{ date('d M, h:i A', strtotime($b->created_at)) }}</small>
                </div>
            </div>
        </td>

        <!-- 4. Recipient Customer Info -->
        <td class="py-2 px-1 col-w-cust" style="overflow: hidden;">
            <div class="min-w-0">
                <strong class="text-dark d-block text-truncate mb-1" style="font-size: 11px;" title="{{ $b->shipping_name ?? 'Customer' }}">
                    {{ $b->shipping_name ?? 'Customer' }}
                </strong>
                <small class="text-muted font-monospace d-block bn-digit text-truncate" style="font-size: 9.5px;">
                    <i class="fas fa-phone-alt me-1 text-muted" style="font-size: 8px;"></i>{{ $b->shipping_phone ?? 'N/A' }}
                </small>
                @if(!empty($b->shipping_address))
                    <small class="text-muted text-truncate d-block mt-1 font-monospace" style="font-size: 9px; line-height: 1.2;" title="{{ $b->shipping_address }}">
                        {{ $b->shipping_address }}
                    </small>
                @endif
            </div>
        </td>

        <!-- 5. Courier Provider & Cost Badge -->
        <td class="py-2 px-1 col-w-prov" style="overflow: hidden;">
            <div class="min-w-0">
                <div class="d-flex align-items-center gap-1 text-truncate">
                    <i class="fas {{ $providerIcon }}" style="font-size: 11px;"></i>
                    <strong class="text-dark text-capitalize text-truncate" style="font-size: 11px;" title="{{ $b->courier_name }}">{{ $b->courier_name }}</strong>
                </div>
                <div class="d-flex align-items-center gap-1 mt-1 text-nowrap min-w-0">
                    <span class="badge bg-light text-muted border text-uppercase px-1 py-0" style="font-size: 8px; border-radius: 5px !important;">
                        {{ $isManual ? 'MANUAL' : 'API' }}
                    </span>
                    <span class="badge bg-secondary-subtle text-secondary font-monospace bn-digit px-1 py-0 text-truncate" style="font-size: 8.5px; border-radius: 5px !important;" title="Delivery Cost">
                        Cost: ৳{{ number_format((float)($b->courier_cost ?? 0), 0) }}
                    </span>
                </div>
            </div>
        </td>

        <!-- 6. Tracking Code & Proof -->
        <td class="py-2 px-1 col-w-trk" style="overflow: hidden;">
            <div class="min-w-0">
                <div class="d-flex align-items-center gap-1 text-nowrap">
                    <span class="font-monospace fw-bold text-dark user-select-all bn-digit text-truncate" style="font-size: 10.5px;" title="{{ $b->tracking_code }}">{{ $b->tracking_code }}</span>
                    <button type="button" class="btn btn-link btn-xs p-0 text-muted shadow-none" onclick="copyTrackingCode('{{ $b->tracking_code }}')" title="Copy Tracking Code">
                        <i class="far fa-copy" style="font-size: 9.5px;"></i>
                    </button>

                    @if($proofUrl)
                        <div class="proof-thumb-box" style="border-radius: 5px !important;" onclick="zoomConsignmentProof('{{ $proofUrl }}', 'Invoice #{{ $b->invoice_no ?? $b->order_id }} Consignment Slip')" title="Click to Zoom Delivery Proof Slip">
                            <img src="{{ $proofUrl }}" alt="POD Slip" onerror="this.src='https://placehold.co/40x40?text=POD';">
                            <div class="proof-thumb-hover-overlay">
                                <i class="fas fa-search-plus text-white" style="font-size: 8px;"></i>
                            </div>
                        </div>
                    @else
                        <button type="button" class="btn btn-sm btn-light border py-0 px-1 text-muted shadow-xs d-inline-flex align-items-center gap-1" style="height: 18px; font-size: 8px; border-radius: 5px !important;" onclick="openManualStatusModal({{ $b->id }}, '{{ strtolower($b->delivery_status ?? 'in_transit') }}', '{{ $b->tracking_code }}', '{{ $b->invoice_no ?? $b->order_id }}', {{ (float)($b->courier_cost ?? 0) }}, {{ (float)($b->courier_cost_paid ?? 0) }}, '{{ $rawSlip ?? '' }}')" title="Attach POD / Delivery Slip via Media Library">
                            <i class="fas fa-paperclip text-primary"></i><span>Slip</span>
                        </button>
                    @endif
                </div>

                @if(!empty($b->consignment_id) && $b->consignment_id !== $b->tracking_code)
                    <small class="text-muted font-monospace d-block bn-digit text-truncate mt-1" style="font-size: 9px;" title="CN: {{ $b->consignment_id }}">
                        CN: {{ $b->consignment_id }}
                    </small>
                @endif
            </div>
        </td>

        <!-- 7. Weight -->
        <td class="text-center font-monospace py-2 px-1 text-nowrap bn-digit col-w-wt" style="font-size: 10.5px;">
            {{ number_format((float)($b->parcel_weight ?? 0.5), 1) }} kg
        </td>

        <!-- 8. COD Amount -->
        <td class="text-end font-monospace fw-bold text-dark py-2 px-1 text-nowrap bn-digit col-w-cod" style="font-size: 11px;">
            ৳{{ number_format((float)($b->cod_amount ?? 0), 2) }}
        </td>

        <!-- 9. Settled COD Amount -->
        <td class="text-end font-monospace fw-bold text-success py-2 px-1 text-nowrap bn-digit col-w-set" style="font-size: 11px;">
            ৳{{ number_format((float)($b->settled_amount ?? 0), 2) }}
        </td>

        <!-- 10. Delivery Status -->
        <td class="text-center py-2 px-1 text-nowrap col-w-dst">
            <span class="badge {{ $deliveryBadgeClass }} px-1 py-0.5 text-uppercase font-monospace fw-bold" style="font-size: 8px; border-radius: 5px !important;">
                {{ str_replace('_', ' ', $b->delivery_status ?? 'IN REVIEW') }}
            </span>
        </td>

        <!-- 11. COD Settlement Status -->
        <td class="text-center py-2 px-1 text-nowrap col-w-cst">
            <span class="badge {{ $codBadgeClass }} px-1 py-0.5 text-uppercase font-monospace fw-bold" style="font-size: 8px; border-radius: 5px !important;">
                {{ $b->cod_settlement_status ?? 'UNSETTLED' }}
            </span>
        </td>

        <!-- 12. Actions Column (100% Fit with No Cutoff) -->
        <td class="text-end py-2 px-1 text-nowrap pe-2 col-w-act">
            <div class="d-inline-flex align-items-center justify-content-end gap-1">
                @if($isManual)
                    <button type="button" class="btn btn-light border text-success shadow-xs p-0 d-inline-flex align-items-center justify-content-center" 
                            style="width: 24px; height: 24px; border-radius: 5px !important; font-size: 10px;"
                            onclick="openManualStatusModal({{ $b->id }}, '{{ strtolower($b->delivery_status ?? 'in_transit') }}', '{{ $b->tracking_code }}', '{{ $b->invoice_no ?? $b->order_id }}', {{ (float)($b->courier_cost ?? 0) }}, {{ (float)($b->courier_cost_paid ?? 0) }}, '{{ $rawSlip ?? '' }}')" 
                            title="Update Status, Cost &amp; POD Slip">
                        <i class="fas fa-pen-to-square"></i>
                    </button>
                @else
                    <button type="button" class="btn btn-light border text-primary shadow-xs p-0 d-inline-flex align-items-center justify-content-center" 
                            id="btn-sync-{{ $b->id }}" 
                            style="width: 24px; height: 24px; border-radius: 5px !important; font-size: 10px;"
                            onclick="syncLiveCourierStatus({{ $b->id }})" 
                            title="Sync Live Tracking from Courier API">
                        <i class="fas fa-rotate"></i>
                    </button>
                @endif

                <a href="{{ route('admin.order.view', $b->order_id) }}" class="btn btn-light border text-dark shadow-xs p-0 d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px; border-radius: 5px !important; font-size: 10px;" title="View Full Order Invoice">
                    <i class="fas fa-eye"></i>
                </a>

                <button type="button" class="btn btn-light border text-danger shadow-xs p-0 d-inline-flex align-items-center justify-content-center" 
                        style="width: 24px; height: 24px; border-radius: 5px !important; font-size: 10px;"
                        onclick="confirmDeleteCourierBooking({{ $b->id }}, '{{ $b->invoice_no ?? $b->order_id }}')" 
                        title="Delete Courier Record">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="12" class="text-center py-4 text-muted small">
            <i class="fas fa-boxes-packing fa-2x mb-2 d-block opacity-25"></i>
            <span class="fw-semibold">No courier booking records found matching your filter criteria.</span>
        </td>
    </tr>
@endforelse