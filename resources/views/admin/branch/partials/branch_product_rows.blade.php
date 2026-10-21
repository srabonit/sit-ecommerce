@forelse($stocks as $index => $s)
    <tr id="stockRow{{ $s->stock_id }}">
        <td class="ps-3 fw-bold">{{ $stocks->firstItem() + $index }}</td>
        <td>
            <strong class="text-dark d-block">{{ $s->product_title }}</strong>
            <small class="text-muted font-monospace">{{ $s->product_code ?? 'SKU-NONE' }}</small>
        </td>
        <td><span class="badge bg-primary-subtle text-primary border">{{ $s->branch_name }}</span></td>
        <td>{{ $s->category_name ?? 'General' }}</td>
        <td class="fw-bold">{{ number_format($s->regular_price, 2) }} ৳</td>
        <td>
            <span class="badge {{ $s->current_stock <= $s->alert_quantity ? 'bg-danger' : 'bg-success' }} px-2 py-1 fs-6" id="stockQtyBadge{{ $s->stock_id }}">
                {{ $s->current_stock }}
            </span>
        </td>
        <td class="text-end pe-3">
            <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1 rounded-2 adjust-stock-btn" 
                    data-id="{{ $s->stock_id }}" 
                    data-title="{{ htmlspecialchars($s->product_title, ENT_QUOTES) }}" 
                    data-stock="{{ $s->current_stock }}">
                <i class="fas fa-edit me-1"></i> Adjust Stock
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center py-5 text-danger fw-bold">No products found.</td>
    </tr>
@endforelse