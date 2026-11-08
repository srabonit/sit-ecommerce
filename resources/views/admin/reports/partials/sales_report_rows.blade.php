
@forelse($orders as $order)
    <tr class="align-middle">
        {{-- Invoice --}}
        <td class="ps-3">
            <a href="{{ \Illuminate\Support\Facades\Route::has('admin.order.view') ? route('admin.order.view', $order->id) : url('admin/orders/' . $order->id) }}"
               class="fw-semibold font-monospace text-primary text-decoration-none"
               style="font-size: 12px;">
                {{ $order->invoice_no ?? ('#' . $order->id) }}
            </a>
        </td>

        {{-- Date --}}
        <td class="font-monospace text-muted text-nowrap" style="font-size: 11px;">
            {{ $order->created_at->format('d M Y, h:i A') }}
        </td>

        {{-- Customer --}}
        <td style="min-width: 145px;">
            <div class="fw-semibold text-dark text-nowrap" style="font-size: 12px;">
                {{ $order->user?->name ?? 'Walk-in Customer' }}
            </div>
            <small class="text-muted font-monospace">
                {{ $order->user?->phone ?? 'N/A' }}
            </small>
        </td>

        {{-- Sales Person --}}
        <td>
            <span class="badge bg-light text-dark border fw-medium px-2 py-1"
                  style="font-size: 10.5px;">
                {{ $order->salesPerson?->name ?? 'Cashier' }}
            </span>
        </td>

        {{-- Order Type --}}
        <td class="text-center">
            <span class="badge {{ ($order->order_type ?? '') === 'pos' ? 'bg-warning text-dark' : 'bg-info text-white' }} px-2 py-1"
                  style="font-size: 10px;">
                {{ strtoupper($order->order_type ?? 'ONLINE') }}
            </span>
        </td>

        {{-- Items --}}
        <td class="text-center font-monospace fw-semibold text-dark">
            {{ $order->items_count ?? ($order->items ? $order->items->count() : 1) }}
        </td>

        {{-- Subtotal --}}
        <td class="text-end font-monospace text-muted text-nowrap">
            ৳{{ number_format($order->subtotal ?? $order->grand_total, 2) }}
        </td>

        {{-- Discount --}}
        <td class="text-end font-monospace text-muted text-nowrap">
            ৳{{ number_format($order->discount_amount ?? 0, 2) }}
        </td>

        {{-- Grand Total --}}
        <td class="text-end font-monospace fw-bold text-dark text-nowrap">
            ৳{{ number_format($order->grand_total, 2) }}
        </td>

        {{-- Paid --}}
        <td class="text-end font-monospace fw-semibold text-success text-nowrap">
            ৳{{ number_format($order->paid_amount, 2) }}
        </td>

        {{-- Due --}}
        <td class="text-end font-monospace fw-semibold text-danger text-nowrap">
            ৳{{ number_format($order->due_amount, 2) }}
        </td>

        {{-- Payment Method --}}
        <td class="text-center">
            <span class="badge bg-light text-dark border fw-medium px-2 py-1"
                  style="font-size: 10px;">
                {{ strtoupper($order->payment_method) }}
            </span>
        </td>

        {{-- Status --}}
        <td class="text-center">
            <span class="badge bg-success-subtle text-success fw-semibold px-2 py-1"
                  style="font-size: 10px;">
                {{ ucfirst($order->order_status ?? 'completed') }}
            </span>
        </td>

        {{-- Print Actions --}}
        <td class="text-end pe-3">
            <div class="btn-group btn-group-sm" role="group">
                <a href="{{ \Illuminate\Support\Facades\Route::has('admin.pos.print.thermal') ? route('admin.pos.print.thermal', $order->id) : url('admin/pos/print-thermal/' . $order->id) }}"
                   target="_blank"
                   class="btn btn-outline-secondary px-2 py-1"
                   title="Thermal Receipt"
                   aria-label="Thermal Receipt">
                    <i class="fas fa-receipt"></i>
                </a>

                <a href="{{ \Illuminate\Support\Facades\Route::has('admin.pos.print.a5') ? route('admin.pos.print.a5', $order->id) : url('admin/pos/print-a5/' . $order->id) }}"
                   target="_blank"
                   class="btn btn-outline-primary px-2 py-1"
                   title="A5 Memo"
                   aria-label="A5 Memo">
                    <i class="fas fa-print"></i>
                </a>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="14" class="text-center py-4">
            <div class="d-flex flex-column align-items-center gap-2 text-muted">
                <i class="fas fa-receipt" style="font-size: 24px; opacity: .45;"></i>
                <span class="fw-medium" style="font-size: 12px;">
                    No sales records found matching your filters.
                </span>
            </div>
        </td>
    </tr>
@endforelse
