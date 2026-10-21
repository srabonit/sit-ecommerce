@forelse($branches as $index => $b)
    <tr id="branchRow{{ $b->id }}">
        <td class="ps-3 fw-bold">{{ $branches->firstItem() + $index }}</td>
        <td>
            <strong class="text-dark d-block">{{ $b->name }}</strong>
            <small class="text-muted font-monospace">{{ $b->code }} | Type: {{ ucfirst($b->type) }}</small>
        </td>
        <td>{{ $b->phone ?? '-' }}</td>
        <td>{{ $b->email ?? '-' }}</td>
        <td><small class="text-muted text-wrap" style="max-width: 250px;">{{ $b->address ?? '-' }}</small></td>
        <td>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="switch{{ $b->id }}" {{ $b->is_active ? 'checked' : '' }} onchange="toggleBranchStatus({{ $b->id }})">
                <label class="form-check-label small" for="switch{{ $b->id }}">
                    <span class="badge {{ $b->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-muted' }} border">
                        {{ $b->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </label>
            </div>
        </td>
        <td class="text-end pe-3">
            <a href="{{ route('admin.branch.edit', $b->id) }}" class="btn btn-outline-primary btn-sm px-2 py-1 rounded-2" title="Edit Branch">
                <i class="fas fa-edit"></i>
            </a>
            <button type="button" class="btn btn-outline-danger btn-sm px-2 py-1 rounded-2 ms-1 delete-branch-btn" 
                    data-id="{{ $b->id }}" 
                    data-name="{{ $b->name }}">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center py-5 text-muted">No branches found matching search.</td>
    </tr>
@endforelse