@forelse($transfers as $index => $t)
    @php
        $itemCount = DB::table('stock_transfer_items')->where('stock_transfer_id', $t->id)->count();
    @endphp
    <tr id="transferRow{{ $t->id }}">
        <td class="ps-3 fw-bold">{{ $transfers->firstItem() + $index }}</td>
        <td>
            <strong class="text-primary font-monospace">{{ $t->transfer_no }}</strong>
            <small class="d-block text-muted">{{ date('d M Y, h:i A', strtotime($t->created_at)) }}</small>
        </td>
        <td>
            <span class="fw-bold text-dark">{{ $t->branch_name ?? 'Branch #' . $t->to_branch_id }}</span>
        </td>
        <td>
            <span class="badge bg-primary-subtle text-primary border px-2 py-1">{{ $itemCount }} Products</span>
        </td>
        <td>{{ $t->assigned_by ?? 'MD SRABON IT' }}</td>
        <td class="text-end pe-3">
            <button type="button" class="btn btn-outline-primary btn-sm px-3 py-1 rounded-pill view-transfer-btn" data-id="{{ $t->id }}">
                <i class="fas fa-eye me-1"></i> View Items
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center py-5 text-muted">No assign history records found.</td>
    </tr>
@endforelse