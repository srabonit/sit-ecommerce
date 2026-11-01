@forelse($records as $row)
    @if($reportType === 'product_wise')
        <tr>
            <td class="ps-3 font-monospace text-secondary" style="font-size: 12px;">#{{ $row->product_id }}</td>
            <td class="fw-semibold text-dark text-truncate" style="max-width: 220px;" title="{{ $row->title }}">{{ $row->title }}</td>
            <td class="text-center font-monospace">{{ number_format($row->total_qty ?? 0) }}</td>
            <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
            <td class="text-center font-monospace" style="font-size: 12px;">{{ number_format($row->avg_rate ?? 0, 2) }}%</td>
            <td class="text-end pe-3 fw-bold text-success font-monospace">৳{{ number_format($row->total_tax ?? 0, 2) }}</td>
        </tr>
    @elseif($reportType === 'variant_wise')
        <tr>
            <td class="ps-3 font-monospace text-secondary" style="font-size: 12px;">#{{ $row->variant_id }}</td>
            <td class="fw-semibold text-dark text-truncate" style="max-width: 220px;" title="{{ $row->title }}">{{ $row->title }}</td>
            <td class="text-center font-monospace">{{ number_format($row->total_qty ?? 0) }}</td>
            <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
            <td class="text-center font-monospace" style="font-size: 12px;">{{ number_format($row->avg_rate ?? 0, 2) }}%</td>
            <td class="text-end pe-3 fw-bold text-success font-monospace">৳{{ number_format($row->total_tax ?? 0, 2) }}</td>
        </tr>
    @elseif($reportType === 'category_wise')
        <tr>
            <td class="ps-3 font-monospace text-secondary" style="font-size: 12px;">#{{ $row->category_id ?? 'N/A' }}</td>
            <td class="fw-semibold text-dark">{{ $row->category_name }}</td>
            <td class="text-center font-monospace">{{ number_format($row->total_qty ?? 0) }}</td>
            <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
            <td class="text-end pe-3 fw-bold text-success font-monospace">৳{{ number_format($row->total_tax ?? 0, 2) }}</td>
        </tr>
    @elseif($reportType === 'brand_wise')
        <tr>
            <td class="ps-3 font-monospace text-secondary" style="font-size: 12px;">#{{ $row->brand_id ?? 'N/A' }}</td>
            <td class="fw-semibold text-dark">{{ $row->brand_name }}</td>
            <td class="text-center font-monospace">{{ number_format($row->total_qty ?? 0) }}</td>
            <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
            <td class="text-end pe-3 fw-bold text-success font-monospace">৳{{ number_format($row->total_tax ?? 0, 2) }}</td>
        </tr>
    @elseif($reportType === 'branch_wise')
        <tr>
            <td class="ps-3 font-monospace text-secondary" style="font-size: 12px;">#{{ $row->branch_id }}</td>
            <td class="fw-semibold text-dark">{{ $row->branch_name }}</td>
            <td><span class="badge bg-light text-dark border px-2 py-1 font-monospace" style="border-radius: 5px;">{{ $row->bin_number ?? 'N/A' }}</span></td>
            <td class="text-center font-monospace">{{ number_format($row->total_orders ?? 0) }}</td>
            <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
            <td class="text-end fw-bold text-success font-monospace">৳{{ number_format($row->total_tax ?? 0, 2) }}</td>
            <td class="text-end pe-3 font-monospace">৳{{ number_format($row->total_sales ?? 0, 2) }}</td>
        </tr>
    @elseif($reportType === 'customer_wise')
        <tr>
            <td class="ps-3 font-monospace text-primary fw-medium" style="font-size: 12px;">{{ $row->customer_phone }}</td>
            <td class="fw-semibold text-dark">{{ $row->customer_name ?: 'Guest Customer' }}</td>
            <td class="text-center font-monospace">{{ number_format($row->order_count ?? 0) }}</td>
            <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
            <td class="text-end fw-bold text-success font-monospace">৳{{ number_format($row->total_tax ?? 0, 2) }}</td>
            <td class="text-end pe-3 font-monospace">৳{{ number_format($row->total_spent ?? 0, 2) }}</td>
        </tr>
    @elseif($reportType === 'shipping_tax')
        <tr>
            <td class="ps-3 fw-semibold font-monospace text-dark" style="font-size: 12px;">{{ $row->invoice_no }}</td>
            <td class="text-secondary" style="font-size: 12px;">{{ !empty($row->created_at) ? \Carbon\Carbon::parse($row->created_at)->format('d M Y, h:i A') : 'N/A' }}</td>
            <td>{{ $row->delivery_city ?? ($row->courier_name ?? 'Standard Courier') }}</td>
            <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
            <td class="text-end pe-3 fw-bold text-primary font-monospace">৳{{ number_format($row->total_tax ?? 0, 2) }}</td>
        </tr>
    @elseif($reportType === 'payment_history')
        <tr>
            <td class="ps-3 text-secondary" style="font-size: 12px;">{{ !empty($row->payment_date) ? \Carbon\Carbon::parse($row->payment_date)->format('d M Y') : 'N/A' }}</td>
            <td><span class="badge bg-secondary-subtle text-secondary px-2 py-1 font-monospace" style="border-radius: 5px;">{{ $row->tax_period }}</span></td>
            <td class="font-monospace" style="font-size: 12px;">
                {{ $row->challan_number ?: 'N/A' }}
                @if(!empty($row->mushak_reference)) <small class="text-secondary">({{ $row->mushak_reference }})</small> @endif
            </td>
            <td>{{ ucfirst(str_replace('_', ' ', $row->payment_method ?? 'Cash')) }}</td>
            <td class="text-end pe-3 fw-bold text-success font-monospace">৳{{ number_format($row->amount ?? 0, 2) }}</td>
        </tr>
    @elseif($reportType === 'adjustment_history' || $reportType === 'refund_history')
        <tr>
            <td class="ps-3 text-secondary" style="font-size: 12px;">{{ !empty($row->created_at) ? \Carbon\Carbon::parse($row->created_at)->format('d M Y') : 'N/A' }}</td>
            <td class="fw-semibold font-monospace text-dark" style="font-size: 12px;">
                {{ !empty($row->order) ? ($row->order->invoice_no ?? '#'.$row->order_id) : (!empty($row->invoice_no) ? $row->invoice_no : '#'.($row->order_id ?? '')) }}
            </td>
            <td>
                <span class="badge bg-danger-subtle text-danger px-2 py-1" style="border-radius: 5px;">
                    {{ ucfirst(str_replace('_', ' ', $row->adjustment_type ?? 'Adjustment')) }}
                </span>
            </td>
            <td class="text-end text-secondary font-monospace">৳{{ number_format($row->original_tax_amount ?? 0, 2) }}</td>
            <td class="text-end fw-bold text-danger font-monospace">-৳{{ number_format($row->net_tax_change ?? 0, 2) }}</td>
            <td class="pe-3 small text-secondary">{{ $row->reason ?: ($row->reference_no ?: '-') }}</td>
        </tr>
    @else
        <tr>
            <td class="ps-3 fw-semibold font-monospace text-dark" style="font-size: 12px;">{{ $row->invoice_no }}</td>
            <td class="text-secondary" style="font-size: 12px;">{{ !empty($row->created_at) ? \Carbon\Carbon::parse($row->created_at)->format('d M Y, h:i A') : 'N/A' }}</td>
            <td>
                <div class="fw-semibold text-dark">{{ $row->customer_name ?: 'Guest' }}</div>
                <small class="text-secondary font-monospace" style="font-size: 11px;">{{ $row->customer_phone ?: '' }}</small>
            </td>
            <td class="text-end font-monospace">৳{{ number_format($row->taxable_amount ?? 0, 2) }}</td>
            <td class="text-end text-secondary font-monospace">৳{{ number_format($row->product_tax_amount ?? 0, 2) }}</td>
            <td class="text-end text-secondary font-monospace">৳{{ number_format($row->shipping_tax_amount ?? 0, 2) }}</td>
            <td class="text-end fw-bold text-success font-monospace">৳{{ number_format($row->tax_amount ?? ($row->total_tax ?? 0), 2) }}</td>
            <td class="text-end fw-bold text-dark font-monospace">৳{{ number_format($row->grand_total ?? 0, 2) }}</td>
            <td class="text-center pe-3">
                @if(!empty($row->is_pos))
                    <span class="badge bg-success-subtle text-success px-2 py-1" style="border-radius: 5px;">POS</span>
                @else
                    <span class="badge bg-primary-subtle text-primary px-2 py-1" style="border-radius: 5px;">Online</span>
                @endif
            </td>
        </tr>
    @endif
@empty
    <tr>
        <td colspan="10" class="text-center py-5">
            <div class="text-secondary">
                <i class="fas fa-file-invoice-dollar fs-2 opacity-50 mb-2 d-block"></i>
                <h6 class="fw-semibold text-dark">No Records Found</h6>
                <p class="small mb-0">Try adjusting your filter parameters or search keywords.</p>
            </div>
        </td>
    </tr>
@endforelse