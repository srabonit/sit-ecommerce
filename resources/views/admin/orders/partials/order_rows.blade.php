@forelse($orders as $order)
    <tr class="align-middle border-bottom order-data-row">
        {{-- CHECKBOX SELECTION --}}
        <td class="ps-2 pe-1 text-center" style="width: 32px;">
            <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="form-check-input order-checkbox m-0" style="cursor: pointer; width: 15px; height: 15px; border-radius: 4px !important;">
        </td>

        {{-- 1. ORDER NUMBER, INVOICE & SOURCE CHANNEL --}}
        <td class="col-cell-order">
            <div class="d-flex flex-column align-items-start">
                <a href="{{ route('admin.order.view', $order->id) }}" class="fw-bold text-primary text-decoration-none font-monospace bn-digit d-inline-flex align-items-center gap-1" style="font-size: 12.5px;" title="View Order #{{ $order->id }}">
                    <span>#{{ $order->id }}</span>
                </a>
                
                @if(!empty($order->invoice_no) && $order->invoice_no != $order->id)
                    <small class="d-inline-flex align-items-center text-secondary font-monospace bn-digit mt-1 text-truncate" style="font-size: 10px; max-width: 120px;" title="{{ $order->invoice_no }}">
                        Inv: {{ Str::limit($order->invoice_no, 15) }}
                    </small>
                @elseif(!empty($order->order_number) && $order->order_number != $order->id)
                    <small class="d-inline-flex align-items-center text-secondary font-monospace bn-digit mt-1 text-truncate" style="font-size: 10px; max-width: 120px;" title="{{ $order->order_number }}">
                        Ref: {{ Str::limit($order->order_number, 15) }}
                    </small>
                @endif

                {{-- Channel Source Badge --}}
                @php
                    $orderSource = strtolower($order->order_source ?? ($order->order_type ?? 'web'));
                    $isPos = $orderSource === 'pos';
                    $isManual = in_array($orderSource, ['manual', 'facebook', 'whatsapp', 'phone']);
                @endphp
                @if($isPos)
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0 mt-1 d-inline-flex align-items-center" style="font-size: 9px; border-radius: 4px !important;">
                        <i class="fas fa-cash-register me-1" style="font-size: 8px;"></i>POS
                    </span>
                @elseif($isManual)
                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-1.5 py-0 mt-1 d-inline-flex align-items-center" style="font-size: 9px; border-radius: 4px !important;">
                        <i class="fas fa-phone-alt me-1" style="font-size: 8px;"></i>{{ strtoupper($orderSource) }}
                    </span>
                @else
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-1.5 py-0 mt-1 d-inline-flex align-items-center" style="font-size: 9px; border-radius: 4px !important;">
                        <i class="fas fa-globe me-1" style="font-size: 8px;"></i>WEB
                    </span>
                @endif
            </div>
        </td>

        {{-- 2. DATE --}}
        <td class="col-cell-date text-nowrap">
            <div class="d-flex flex-column">
                <span class="text-dark fw-semibold bn-digit" style="font-size: 11.5px;">
                    <i class="far fa-calendar-alt text-muted me-1" style="font-size: 10px;"></i>{{ date('d M, Y', strtotime($order->created_at)) }}
                </span>
                <small class="text-muted font-monospace bn-digit mt-1" style="font-size: 10px;">
                    <i class="far fa-clock me-1" style="font-size: 9px;"></i>{{ date('h:i A', strtotime($order->created_at)) }}
                </small>
            </div>
        </td>

        {{-- 3. CUSTOMER (FIXED UNBROKEN OVERFLOW) --}}
        <td class="col-cell-customer">
            <div class="d-flex flex-column" style="min-width: 0;">
                <strong class="text-dark d-block text-truncate cust-name-text" style="font-size: 12px; line-height: 1.3;" title="{{ $order->shipping_name ?? ($order->customer_name ?? ($order->name ?? 'Guest Customer')) }}">
                    {{ $order->shipping_name ?? ($order->customer_name ?? ($order->name ?? 'Guest Customer')) }}
                </strong>
                
                @php
                    $custPhone = $order->shipping_phone ?? ($order->customer_phone ?? ($order->phone ?? ''));
                @endphp
                @if(!empty($custPhone))
                    <a href="tel:{{ $custPhone }}" class="text-secondary text-decoration-none small text-nowrap font-monospace bn-digit mt-1 d-inline-flex align-items-center gap-1" style="font-size: 10.5px;">
                        <i class="fas fa-phone text-muted" style="font-size: 9px;"></i>
                        <span>{{ $custPhone }}</span>
                    </a>
                @else
                    <span class="text-muted small font-monospace mt-1" style="font-size: 10px;">N/A</span>
                @endif

                @php
                    $custAddress = $order->shipping_address ?? ($order->customer_address ?? ($order->address ?? ''));
                @endphp
                @if(!empty($custAddress))
                    <span class="text-muted small mt-1 d-block cust-address-box" style="font-size: 10px; line-height: 1.25;" title="{{ $custAddress }}">
                        <i class="fas fa-map-marker-alt text-danger opacity-75 me-1" style="font-size: 8.5px;"></i>{{ $custAddress }}
                    </span>
                @endif
            </div>
        </td>

        {{-- 4. COURIER --}}
        <td class="col-cell-courier text-nowrap">
            @if(!empty($order->courier_name))
                <div class="d-flex flex-column align-items-start">
                    <span class="badge bg-light text-primary border px-2 py-0.5" style="font-size: 9.5px; border-radius: 4px !important;">
                        <i class="fas fa-truck-fast me-1" style="font-size: 8.5px;"></i>{{ $order->courier_name }}
                    </span>
                    @php
                        $trackingVal = $order->courier_tracking_id ?? ($order->tracking_code ?? ($order->parcel_id ?? ''));
                    @endphp
                    @if(!empty($trackingVal))
                        <small class="font-monospace text-muted mt-1 d-inline-flex align-items-center bn-digit text-truncate" style="font-size: 9.5px; max-width: 100px;" title="{{ $trackingVal }}">
                            {{ $trackingVal }}
                        </small>
                    @endif
                </div>
            @else
                <span class="badge bg-light text-secondary border px-1.5 py-0.5 text-muted" style="font-size: 9.5px; border-radius: 4px !important;">
                    <i class="fas fa-minus-circle me-1" style="font-size: 8px;"></i>Not Assigned
                </span>
            @endif
        </td>

        {{-- 5. TOTAL & FINANCIAL BREAKDOWN --}}
        <td class="col-cell-total text-nowrap">
            @php
                $pSubtotal = (float)($order->product_subtotal ?? ($order->subtotal ?? 0));
                $dCharge   = (float)($order->delivery_charge ?? ($order->shipping_charge ?? 0));
                $gTotal    = (float)($order->grand_total ?? 0);
            @endphp
            <div class="d-flex flex-column">
                <span class="fw-bold text-dark font-monospace bn-digit" style="font-size: 12.5px;">
                    ৳{{ number_format($gTotal, 2) }}
                </span>
                
                @if($dCharge > 0)
                    <div class="text-muted font-monospace bn-digit mt-0.5" style="font-size: 9.5px; line-height: 1.2;">
                        <span>Prod: ৳{{ number_format($pSubtotal, 0) }}</span> | <span>Del: ৳{{ number_format($dCharge, 0) }}</span>
                    </div>
                @endif
                
                <span class="badge bg-light text-secondary border font-monospace px-1.5 py-0 mt-1 d-inline-block text-uppercase align-self-start" style="font-size: 8.5px; border-radius: 4px !important;">
                    {{ $order->payment_method ?? 'COD' }}
                </span>
            </div>
        </td>

        {{-- 6. ORDER STATUS --}}
        <td class="col-cell-status text-nowrap">
            @php
                $status = strtolower($order->order_status ?? 'pending');
                $badgeClass = match($status) {
                    'pending'              => 'bg-danger-subtle text-danger border border-danger-subtle',
                    'received'             => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                    'confirmed'            => 'bg-primary-subtle text-primary border border-primary-subtle',
                    'processing', 'packed' => 'bg-dark text-white border border-dark',
                    'shipped'              => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                    'attempt_to_deliver'   => 'bg-secondary-subtle text-dark border',
                    'delivered'            => 'bg-success-subtle text-success border border-success-subtle',
                    'cancelled'            => 'bg-danger-subtle text-muted border',
                    'returned'             => 'bg-secondary-subtle text-dark border',
                    'exchange', 'exchanged'=> 'bg-info-subtle text-dark border',
                    default                => 'bg-light text-dark border'
                };
            @endphp
            <div class="d-flex flex-column align-items-start">
                <span class="badge {{ $badgeClass }} px-2 py-0.5 text-uppercase fw-bold" style="font-size: 9.5px; border-radius: 4px !important;">
                    {{ str_replace('_', ' ', $status) }}
                </span>

                @if(!empty($order->is_stock_deducted))
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-1 py-0 mt-1 font-monospace d-inline-flex align-items-center" style="font-size: 8.5px; border-radius: 4px !important;" title="Physical stock deducted">
                        <i class="fas fa-check-double me-1" style="font-size: 7.5px;"></i>Deducted
                    </span>
                @else
                    <span class="badge bg-light text-muted border px-1 py-0 mt-1 font-monospace d-inline-flex align-items-center" style="font-size: 8.5px; border-radius: 4px !important;" title="Estimated order quantity reserved">
                        <i class="fas fa-clock me-1" style="font-size: 7.5px;"></i>Reserved
                    </span>
                @endif
            </div>
        </td>

        {{-- 7. PAYMENT STATUS --}}
        <td class="col-cell-payment text-nowrap">
            @php
                $pStatus = strtolower($order->payment_status ?? 'unpaid');
                $payBadge = match($pStatus) {
                    'paid'                      => 'bg-success-subtle text-success border border-success-subtle',
                    'partially_paid', 'partial' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                    'pending_verification'      => 'bg-info-subtle text-info border border-info-subtle',
                    'refunded'                  => 'bg-secondary-subtle text-muted border',
                    default                     => 'bg-danger-subtle text-danger border border-danger-subtle'
                };
            @endphp
            <span class="badge {{ $payBadge }} px-2 py-0.5 text-uppercase fw-bold" style="font-size: 9.5px; border-radius: 4px !important;">
                {{ str_replace('_', ' ', $pStatus) }}
            </span>
        </td>

        {{-- 8. ACTIONS --}}
        <td class="col-cell-action text-end pe-2 text-nowrap">
            <div class="d-inline-flex gap-1 align-items-center justify-content-end">
                <a href="{{ route('admin.order.view', $order->id) }}" class="btn btn-outline-primary table-action-btn shadow-xs" title="View &amp; Edit Order">
                    <i class="fas fa-eye"></i>
                </a>

                <a href="{{ route('admin.order.invoice', $order->id) }}" target="_blank" class="btn btn-outline-secondary table-action-btn shadow-xs" title="Print Invoice">
                    <i class="fas fa-print"></i>
                </a>

                <button type="button" class="btn btn-outline-danger table-action-btn shadow-xs" title="Delete Order" onclick="confirmDeleteOrder({{ $order->id }})">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="text-center py-5 text-muted">
            <div class="d-flex flex-column align-items-center justify-content-center py-3">
                <i class="fas fa-box-open fa-3x mb-2 text-secondary opacity-25"></i>
                <h6 class="fw-bold text-secondary mb-1" style="font-size: 14px;">No Orders Found</h6>
                <p class="small text-muted mb-0" style="font-size: 12px;">No orders match the selected filters or search keyword.</p>
            </div>
        </td>
    </tr>
@endforelse