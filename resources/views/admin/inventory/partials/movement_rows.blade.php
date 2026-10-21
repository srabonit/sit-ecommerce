@php
    // Safe thumbnail and proof voucher resolver supporting Global Media Library
    $resolveThumb = function($path, $fallback = 'https://placehold.co/60x60?text=Item') {
        if (empty($path)) return $fallback;
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) return show_image($path, $fallback);
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        return asset(ltrim(str_replace(['public_html/', 'public/'], '', $path), '/'));
    };
@endphp

@forelse($movements as $m)
    @php
        $thumb = $resolveThumb($m->product_thumbnail ?? null);

        // Variant attribute text formatting
        $variantParts = array_filter([$m->variant_color ?? null, $m->variant_size ?? null]);
        $hasVariant = count($variantParts) > 0;
        $variantText = $hasVariant ? implode(' / ', $variantParts) : null;

        // Product and variant SKU resolution
        $displaySku = $m->sku ?? ($m->variant_sku ?? ($m->parent_code ?? '—'));

        // Valuation financial impact calculation
        $costImpact = abs((int) ($m->quantity_change ?? 0)) * (float) ($m->unit_cost ?? 0);

        // Resolve audit evidence / consignment proof voucher supporting Global Media Library
        $proofRaw = $m->proof_attachment ?? ($m->document ?? ($m->proof ?? ($m->attachment ?? null)));
        $proofUrl = !empty($proofRaw) ? $resolveThumb($proofRaw, '') : null;

        // Movement type badge metadata mapping
        $typeMeta = match($m->movement_type ?? 'adjustment') {
            'checkout_sale'    => ['label' => 'Online Sale',      'class' => 'bg-primary-subtle text-primary border-primary-subtle',         'icon' => 'fa-globe'],
            'pos_sale'         => ['label' => 'POS Sale',         'class' => 'bg-info-subtle text-info-emphasis border-info-subtle',          'icon' => 'fa-cash-register'],
            'order_cancel'     => ['label' => 'Cancel Restock',   'class' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle', 'icon' => 'fa-ban'],
            'order_return'     => ['label' => 'Return Restock',   'class' => 'bg-secondary-subtle text-secondary border-secondary-subtle',   'icon' => 'fa-rotate-left'],
            'adjustment'       => ['label' => 'Adjustment',       'class' => 'bg-dark-subtle text-dark border-dark-subtle',                  'icon' => 'fa-sliders'],
            'purchase_receive' => ['label' => 'Purchase Inbound', 'class' => 'bg-success-subtle text-success border-success-subtle',          'icon' => 'fa-arrow-down'],
            'damage_loss'      => ['label' => 'Damage / Loss',    'class' => 'bg-danger-subtle text-danger border-danger-subtle',            'icon' => 'fa-triangle-exclamation'],
            'inventory_audit'  => ['label' => 'Audit Reconcile',  'class' => 'bg-light text-dark border',                                     'icon' => 'fa-clipboard-check'],
            default            => ['label' => ucwords(str_replace('_', ' ', $m->movement_type ?? 'movement')), 'class' => 'bg-light text-dark border', 'icon' => 'fa-circle-dot'],
        };

        // Fail-safe product edit URL
        $productEditUrl = Route::has('admin.product.edit') && !empty($m->product_id)
            ? route('admin.product.edit', $m->product_id)
            : 'javascript:void(0);';
    @endphp
    <tr id="movement-row-{{ $m->id }}" class="align-middle border-bottom" style="transition: background-color 0.15s ease;">
        <!-- 1. Audit Log ID -->
        <td class="col-m-id font-monospace bn-digit fw-semibold text-secondary text-center text-nowrap py-2 ps-2" style="font-size: 11px; width: 60px;">
            #{{ $m->id }}
        </td>

        <!-- 2. Transaction Timestamp -->
        <td class="col-m-date py-2 px-2 text-nowrap" style="line-height: 1.3; width: 110px;">
            <div class="min-w-0">
                <span class="text-dark fw-semibold d-block bn-digit font-monospace" style="font-size: 11px;">
                    {{ date('d M Y', strtotime($m->created_at)) }}
                </span>
                <small class="text-muted bn-digit font-monospace d-block" style="font-size: 10px;">
                    {{ date('h:i:s A', strtotime($m->created_at)) }}
                </small>
            </div>
        </td>

        <!-- 3. Product & Attributes (Media Chooser Thumbnail & Attributes) -->
        <td class="col-m-prod py-2 px-2" style="min-width: 200px; max-width: 300px;">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <!-- Media Chooser Interactive Thumbnail with Quick Zoom -->
                <div class="proof-thumb-box flex-shrink-0" style="width: 36px; height: 36px; border-radius: 5px !important; overflow: hidden; border: 1px solid #cbd5e1; background: #ffffff; cursor: pointer; position: relative;" onclick="if(typeof zoomInvImage === 'function'){ zoomInvImage('{{ $thumb }}', '{{ addslashes($m->product_title ?? 'Product') }}'); }" title="Click to Zoom Thumbnail">
                    <img src="{{ $thumb }}" alt="{{ $m->product_title ?? 'Product' }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='https://placehold.co/60x60?text=Item';">
                    <div class="proof-thumb-hover-overlay" style="border-radius: 5px !important; position: absolute; inset: 0; background: rgba(15, 23, 42, 0.45); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.15s ease;">
                        <i class="fas fa-search-plus text-white" style="font-size: 8px;"></i>
                    </div>
                </div>

                <div class="min-w-0 flex-grow-1" style="line-height: 1.3;">
                    <a href="{{ $productEditUrl }}" class="text-dark fw-bold text-decoration-none d-block text-truncate" style="font-size: 12px;" title="{{ $m->product_title ?? 'Product #' . $m->product_id }}">
                        {{ $m->product_title ?? 'Product #' . $m->product_id }}
                    </a>
                    @if($hasVariant)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace text-truncate d-inline-block px-1 py-0 mt-1" style="font-size: 9px; max-width: 130px; border-radius: 5px !important;" title="{{ $variantText }}">
                            <i class="fas fa-layer-group me-1"></i>{{ $variantText }}
                        </span>
                    @endif
                </div>
            </div>
        </td>

        <!-- 4. Product SKU / Code -->
        <td class="col-m-sku font-monospace text-nowrap bn-digit py-2 px-2" style="font-size: 11px; width: 110px;" title="{{ $displaySku }}">
            <span class="badge bg-light text-dark border px-2 py-1 text-truncate d-inline-block font-monospace" style="border-radius: 5px !important; font-size: 10px; max-width: 100%;">
                {{ $displaySku }}
            </span>
        </td>

        <!-- 5. Movement Type Badge -->
        <td class="col-m-type text-nowrap py-2 px-2" style="width: 120px;">
            <span class="badge border {{ $typeMeta['class'] }}" style="border-radius: 5px !important; font-size: 9px; padding: 3px 6px;">
                <i class="fas {{ $typeMeta['icon'] }} me-1"></i>{{ $typeMeta['label'] }}
            </span>
        </td>

        <!-- 6. Change Indicator (+ / -) -->
        <td class="col-m-change text-center text-nowrap py-2 px-2" style="width: 80px;">
            @if($m->quantity_change > 0)
                <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold bn-digit font-monospace px-2 py-1" style="font-size: 11px; border-radius: 5px !important;">
                    +{{ $m->quantity_change }}
                </span>
            @elseif($m->quantity_change < 0)
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold bn-digit font-monospace px-2 py-1" style="font-size: 11px; border-radius: 5px !important;">
                    {{ $m->quantity_change }}
                </span>
            @else
                <span class="badge bg-light text-muted border bn-digit font-monospace px-2 py-1" style="font-size: 11px; border-radius: 5px !important;">
                    0
                </span>
            @endif
        </td>

        <!-- 7. Balance Flow (Before -> After) -->
        <td class="col-m-flow text-center text-nowrap font-monospace bn-digit py-2 px-2" style="font-size: 11px; width: 110px;">
            <span class="text-muted">{{ $m->quantity_before }}</span>
            <i class="fas fa-arrow-right-long text-secondary mx-1 opacity-75" style="font-size: 9px;"></i>
            <strong class="text-dark">{{ $m->quantity_after }}</strong>
        </td>

        <!-- 8. Weighted Unit Cost -->
        <td class="col-m-cost text-end text-nowrap font-monospace bn-digit py-2 px-2 text-muted" style="font-size: 11px; width: 100px;">
            ৳ {{ number_format((float) ($m->unit_cost ?? 0), 2) }}
        </td>

        <!-- 9. Total Cost Impact -->
        <td class="col-m-imp text-end text-nowrap font-monospace bn-digit py-2 px-2" style="font-size: 12px; width: 110px;">
            <strong class="{{ $m->quantity_change < 0 ? 'text-danger' : 'text-success' }}">
                ৳ {{ number_format($costImpact, 2) }}
            </strong>
        </td>

        <!-- 10. Reference ID & Global Media Proof Voucher -->
        <td class="col-m-ref text-nowrap py-2 px-2" style="line-height: 1.3; width: 120px;">
            <div class="d-flex align-items-center gap-1 min-w-0">
                <!-- Proof Voucher Lightbox Thumbnail -->
                @if($proofUrl)
                    <div class="proof-thumb-box flex-shrink-0" style="width: 22px; height: 22px; border-radius: 5px !important; overflow: hidden; border: 1px solid #cbd5e1; background: #ffffff; cursor: pointer; position: relative;" onclick="if(typeof zoomProofAttachment === 'function'){ zoomProofAttachment('{{ $proofUrl }}', 'Voucher #{{ $m->reference_id ?? $m->id }}'); }" title="Click to Zoom Proof Voucher">
                        <img src="{{ $proofUrl }}" alt="Voucher" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='https://placehold.co/40x40?text=Slip';">
                        <div class="proof-thumb-hover-overlay" style="border-radius: 5px !important; position: absolute; inset: 0; background: rgba(15, 23, 42, 0.45); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.15s ease;">
                            <i class="fas fa-search-plus text-white" style="font-size: 8px;"></i>
                        </div>
                    </div>
                @endif

                <div class="min-w-0 flex-grow-1">
                    @if(!empty($m->reference_id))
                        <span class="fw-bold text-primary font-monospace d-block text-truncate" style="font-size: 11px;" title="{{ $m->reference_id }}">
                            {{ $m->reference_id }}
                        </span>
                    @else
                        <span class="text-muted d-block font-monospace" style="font-size: 10px;">—</span>
                    @endif
                    <small class="text-muted text-uppercase text-truncate d-block font-monospace" style="font-size: 9px;">
                        Src: {{ $m->source ?? 'sys' }}
                    </small>
                </div>
            </div>
        </td>

        <!-- 11. Initiator User & Notes -->
        <td class="col-m-user text-nowrap py-2 pe-3 px-2" style="font-size: 11px; width: 130px; line-height: 1.3;">
            <div class="d-flex align-items-center gap-1 min-w-0">
                <i class="fas fa-user-circle text-secondary opacity-75 flex-shrink-0" style="font-size: 10px;"></i>
                <span class="text-dark fw-semibold text-truncate" title="{{ $m->creator_name ?? 'System Automated' }}">
                    {{ $m->creator_name ?? 'System Automated' }}
                </span>
            </div>
            @if(!empty($m->notes))
                <small class="text-muted text-truncate d-block mt-1 font-monospace" style="font-size: 9px;" title="{{ $m->notes }}">
                    <i class="fas fa-comment-dots me-1"></i>{{ $m->notes }}
                </small>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="11" class="text-center py-5 text-muted small">
            <i class="fas fa-box-open fa-2x mb-2 d-block opacity-25"></i>
            <span class="fw-semibold">No stock movement logs found matching the selected criteria.</span>
        </td>
    </tr>
@endforelse