@forelse($products as $p)
    @php
        $pricing = \App\Services\BranchProductService::resolvePrice($p, $branch->id);
        $stock = $p->getBranchStock($branch->id);
        $mode = strtolower($p->branch_availability_mode ?? 'all');
        $isSellable = \App\Services\BranchProductService::isProductSellableInBranch($p, $branch->id);

        // Pivot record for this branch if exists
        $pivot = $p->branches ? $p->branches->firstWhere('id', $branch->id)?->pivot : null;
        $canSell = $pivot ? (bool)$pivot->can_sell : $isSellable;
        $posEnabled = $pivot ? (bool)$pivot->is_pos_enabled : true;
    @endphp
    <tr>
        <!-- Checkbox -->
        <td class="py-2 text-center">
            <input type="checkbox" class="form-check-input prod-checkbox" value="{{ $p->id }}">
        </td>

        <!-- Product Title, Image & SKU -->
        <td class="py-2">
            <div class="d-flex align-items-center gap-2">
                <img src="{{ $p->thumbnail_url }}" alt="{{ $p->name ?? $p->title }}" class="catalog-thumb flex-shrink-0" onerror="this.src='/assets/placeholder.png'">
                <div class="min-width-0">
                    <a href="{{ route('admin.product.edit', $p->id) }}" class="fw-bold text-dark text-decoration-none text-truncate d-block" style="max-width: 250px;">
                        {{ $p->name ?? $p->title }}
                    </a>
                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                        <span class="text-muted font-monospace bn-digit" style="font-size: 10px;">
                            {{ $p->sku ?? $p->product_code }}
                        </span>
                        @if($p->category)
                            <span class="badge bg-light text-muted border" style="font-size: 9px; padding: 1px 4px;">
                                {{ $p->category->name }}
                            </span>
                        @endif
                        @if($p->is_variable)
                            <span class="badge bg-info-subtle text-info border font-monospace" style="font-size: 9px; padding: 1px 4px;">
                                {{ $p->variants->count() }} Variants
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </td>

        <!-- Availability Policy Mode Badge -->
        <td class="py-2">
            @if($mode === 'all')
                <span class="badge bg-success-subtle text-success border border-success-subtle mode-badge">
                    <i class="fas fa-globe me-0.5"></i> All Outlets
                </span>
            @elseif($mode === 'selected')
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle mode-badge">
                    <i class="fas fa-check-square me-0.5"></i> Selected Outlets
                </span>
            @elseif($mode === 'main_only')
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle mode-badge">
                    <i class="fas fa-crown me-0.5"></i> Main Showroom
                </span>
            @elseif($mode === 'head_office_only')
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle mode-badge">
                    <i class="fas fa-building me-0.5"></i> HQ Warehouse
                </span>
            @else
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle mode-badge">
                    <i class="fas fa-lock me-0.5"></i> Restricted
                </span>
            @endif
        </td>

        <!-- Current Available Branch Stock -->
        <td class="py-2 text-center">
            <span class="fw-bold font-monospace bn-digit fs-6 {{ $stock > 0 ? 'text-success' : 'text-danger' }}">
                {{ $stock }}
            </span>
            <small class="text-muted d-block" style="font-size: 9.5px;">Units</small>
        </td>

        <!-- Effective Branch Price & Override Indicator -->
        <td class="py-2 text-end">
            <div class="fw-bold font-monospace bn-digit text-dark">
                ৳{{ number_format($pricing['effective_price'], 2) }}
            </div>
            @if($pricing['is_branch_override'])
                <span class="badge bg-warning text-dark" style="font-size: 8.5px; padding: 1px 3px;">
                    Branch Override
                </span>
            @else
                <small class="text-muted" style="font-size: 9.5px;">Global Default</small>
            @endif
        </td>

        <!-- POS Terminal Visible Toggle -->
        <td class="py-2 text-center">
            <div class="form-check form-switch d-inline-block mb-0">
                <input class="form-check-input" type="checkbox" {{ $posEnabled ? 'checked' : '' }} 
                       onchange="toggleBranchPos({{ $p->id }}, this)">
            </div>
        </td>

        <!-- Sellable Toggle -->
        <td class="py-2 text-center">
            <div class="form-check form-switch d-inline-block mb-0">
                <input class="form-check-input" type="checkbox" {{ $canSell ? 'checked' : '' }} 
                       onchange="toggleBranchCanSell({{ $p->id }}, this)">
            </div>
        </td>

        <!-- Action Links -->
        <td class="py-2 text-end">
            <div class="btn-group btn-group-sm">
                <a href="{{ route('admin.branch_product.pricing', [$branch->id, $p->id]) }}" 
                   class="btn btn-outline-success py-0.5 px-2" title="Branch Price Override" style="font-size: 11px;">
                    <i class="fas fa-dollar-sign"></i>
                </a>
                <a href="{{ route('admin.branch_product.availability', $p->id) }}" 
                   class="btn btn-outline-primary py-0.5 px-2" title="Access Policy" style="font-size: 11px;">
                    <i class="fas fa-shield-alt"></i>
                </a>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center py-4 text-muted">
            <i class="fas fa-box-open fa-2x d-block mb-2 text-secondary"></i>
            No products found matching the criteria in {{ $branch->name }}.
        </td>
    </tr>
@endforelse