@php
    $svgPlaceholder = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='150' height='150' viewBox='0 0 150 150'><rect fill='%23f1f5f9' width='150' height='150'/><text fill='%2394a3b8' font-family='sans-serif' font-size='12' dy='4' font-weight='bold' x='50%25' y='50%25' text-anchor='middle'>No Image</text></svg>";
@endphp

@forelse($products as $p)
    @php
        $imgUrl = '';
        $rawThumb = $p->thumbnail ?? ($p->image ?? '');
        if (!empty($rawThumb)) {
            $t = ltrim(str_replace('\\', '/', $rawThumb), '/');
            if (str_starts_with($t, 'http://') || str_starts_with($t, 'https://')) {
                $imgUrl = $t;
            } else {
                // লোকালহোস্ট এবং লাইভ সার্ভার উভয়ের জন্য ডায়নামিক রুট সিঙ্ক
                $imgUrl = url('thumb/' . $t . '?w=150&h=150');
            }
        }
        $finalPrice = (float) (!empty($p->sale_price) && $p->sale_price > 0 ? $p->sale_price : $p->regular_price);
        
        // Controller থেকে attach করা eager-loaded ভ্যারিয়েন্ট ব্যবহার করা
        $variants = $p->relationLoaded('variants') 
            ? $p->variants 
            : (!empty($p->variants) ? $p->variants : collect());
            
        $hasVariants = $variants->count() > 0;
        $branchStock = (int) ($p->branch_stock ?? 0);

        // Product-level resolved tax values (0 is strictly honored)
        $pTaxRate      = isset($p->resolved_tax_rate) ? $p->resolved_tax_rate : ($p->vat_amount ?? null);
        $pTaxType      = $p->tax_type ?? ($p->vat_type ?? 'percent');
        $pTaxFixed     = (float)($p->tax_fixed_amount ?? 0);
        $pTaxInclusive = !empty($p->is_tax_inclusive);

        // Safe title string for JavaScript functions
        $pTitle = $p->title ?? ($p->name ?? 'Product');
        $safeTitleJs = addslashes(str_replace(["\r", "\n"], '', $pTitle));
    @endphp
    <div class="col-6 col-sm-6 col-md-4 col-xl-3 mb-2">
        <div class="card border shadow-xs rounded-1 p-2 bg-white d-flex flex-column justify-content-between h-100" 
             style="min-height: 270px;"
             data-product-id="{{ $p->id }}"
             data-tax-rate="{{ $pTaxRate !== null ? $pTaxRate : '' }}"
             data-tax-type="{{ $pTaxType }}"
             data-tax-fixed="{{ $pTaxFixed }}"
             data-is-inclusive="{{ $pTaxInclusive ? '1' : '0' }}">
            <div>
                <!-- ইমেজ বক্স (অন-দ্য-ফ্লাই থাম্বনেইল ও লাইভ/লোকাল সিঙ্কড ফলব্যাক) -->
                <div class="rounded-1 overflow-hidden bg-light mb-2 border position-relative d-flex align-items-center justify-content-center" style="height: 120px; width: 100%;">
                    @if(!empty($imgUrl))
                        <img src="{{ $imgUrl }}" class="w-100 h-100 object-fit-cover" loading="lazy" onerror="this.onerror=null; this.src='{{ $svgPlaceholder }}';">
                    @else
                        <img src="{{ $svgPlaceholder }}" class="w-100 h-100 object-fit-cover" loading="lazy">
                    @endif

                    @if($hasVariants)
                        <span class="badge bg-primary position-absolute top-0 end-0 m-1.5 shadow-xs" style="font-size: 9px; padding: 2px 5px; border-radius: 4px;">
                            <i class="fas fa-layer-group me-0.5"></i> {{ $variants->count() }} Var
                        </span>
                    @endif
                </div>

                <!-- টাইটেল ও প্রাইস -->
                <h6 class="fw-semibold text-dark text-truncate small mb-1" title="{{ $pTitle }}" style="font-size: 12px; line-height: 1.2;">{{ $pTitle }}</h6>
                
                <div class="d-flex justify-content-between align-items-center mb-1.5">
                    <span class="fw-bold text-primary small font-monospace bn-digit" id="gridPrice{{ $p->id }}">{{ number_format($finalPrice, 2) }} ৳</span>
                    <small class="text-muted" style="font-size: 11px;">
                        Stock: <span class="fw-bold font-monospace bn-digit {{ $branchStock > 0 ? 'text-success' : 'text-danger' }}" id="gridStock{{ $p->id }}">{{ $branchStock }}</span>
                    </small>
                </div>

                <!-- ভ্যারিয়েন্ট ড্রপডাউন (যদি ভ্যারিয়েশন থাকে) -->
                @if($hasVariants)
                    <div class="mb-1.5">
                        <select id="gridVariantSelect{{ $p->id }}" 
                                class="form-select form-select-sm py-0 px-1 border-primary fw-semibold text-dark shadow-none" 
                                style="font-size: 10.5px; height: 26px; border-radius: 4px;"
                                onchange="onGridVariantChanged({{ $p->id }}, this)">
                            <option value="" 
                                    data-price="{{ $finalPrice }}" 
                                    data-stock="{{ $branchStock }}"
                                    data-tax-rate="{{ $pTaxRate !== null ? $pTaxRate : '' }}"
                                    data-tax-type="{{ $pTaxType }}"
                                    data-tax-fixed="{{ $pTaxFixed }}"
                                    data-is-inclusive="{{ $pTaxInclusive ? '1' : '0' }}">-- Select Variant --</option>
                            @foreach($variants as $v)
                                @php
                                    $vParts = array_filter([$v->color ?? null, $v->size ?? null]);
                                    $vLabel = !empty($vParts) ? implode(' / ', $vParts) : ($v->name ?? ('Variant #' . $v->id));
                                    $vPrice = (float) (!empty($v->sale_price) && $v->sale_price > 0 ? $v->sale_price : (!empty($v->regular_price) && $v->regular_price > 0 ? $v->regular_price : $finalPrice));
                                    $vStock = (int) ($v->stock ?? 0);
                                    $vCode  = $v->code ?? ($v->product_code ?? ($v->sku ?? ('VAR-#' . $v->id)));

                                    $vTaxRate  = isset($v->tax_rate) ? $v->tax_rate : $pTaxRate;
                                    $vTaxType  = $v->tax_type ?? $pTaxType;
                                    $vTaxFixed = isset($v->tax_fixed_amount) ? (float)$v->tax_fixed_amount : $pTaxFixed;
                                    $vTaxInc   = isset($v->is_tax_inclusive) ? (bool)$v->is_tax_inclusive : $pTaxInclusive;
                                @endphp
                                <option value="{{ $v->id }}" 
                                        data-price="{{ $vPrice }}" 
                                        data-stock="{{ $vStock }}"
                                        data-code="{{ e($vCode) }}"
                                        data-title="{{ e($pTitle . ' (' . $vLabel . ')') }}"
                                        data-tax-rate="{{ $vTaxRate !== null ? $vTaxRate : '' }}"
                                        data-tax-type="{{ $vTaxType }}"
                                        data-tax-fixed="{{ $vTaxFixed }}"
                                        data-is-inclusive="{{ $vTaxInc ? '1' : '0' }}">
                                    {{ $vLabel }} ({{ number_format($vPrice, 0) }}৳ | Stk: {{ $vStock }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            <div class="mt-auto">
                <!-- কোয়ান্টিটি ইনপুট -->
                <div class="input-group input-group-sm mb-2 border rounded-1 overflow-hidden" style="height: 28px;">
                    <span class="input-group-text bg-primary text-white border-0 py-0 px-2 fw-semibold" style="font-size: 11px;">Quantity</span>
                    <input type="number" id="gridQty{{ $p->id }}" class="form-control form-control-sm text-center border-0 p-0 fw-bold font-monospace bn-digit" value="1" min="1" max="{{ $branchStock > 0 ? $branchStock : 9999 }}">
                </div>

                <!-- বাটন: ট্যাক্স রেট সরাসরি ফাংশনে পাস করা -->
                <button type="button" class="btn btn-primary btn-sm w-100 py-1 fw-semibold text-white rounded-1 shadow-none text-nowrap" style="font-size: 11.5px;"
                        onclick="addToPosMemo({{ $p->id }}, '{{ $safeTitleJs }}', {{ $finalPrice }}, {{ $branchStock }}, '{{ $imgUrl }}', {{ $pTaxRate !== null ? $pTaxRate : 'null' }}, {{ $pTaxInclusive ? '1' : '0' }}, '{{ $pTaxType }}', {{ $pTaxFixed }})">
                    <i class="fas fa-cart-plus me-1"></i> Add to pos memo
                </button>
            </div>
        </div>
    </div>
@empty
    <div class="col-12 text-center py-5 text-muted">
        <i class="fas fa-box-open fa-3x mb-2 text-secondary"></i>
        <p class="mb-0">No products found matching your filter.</p>
    </div>
@endforelse

<!-- ক্লিন মিনি পেজিনেশন -->
<div class="col-12 mt-2 py-2 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-white border rounded-1 px-3 small text-muted text-center text-sm-start">
    <div class="font-monospace bn-digit">Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} results</div>
    <div>
        @if ($products->hasPages())
            <ul class="pagination pagination-sm mb-0 justify-content-center">
                @if ($products->onFirstPage())
                    <li class="page-item disabled"><span class="page-link py-0 px-2">« Prev</span></li>
                @else
                    <li class="page-item"><a class="page-link py-0 px-2" href="javascript:void(0)" onclick="loadPosPage('{{ $products->previousPageUrl() }}')">« Prev</a></li>
                @endif

                <li class="page-item disabled"><span class="page-link py-0 px-2 font-monospace bn-digit">{{ $products->currentPage() }} / {{ $products->lastPage() }}</span></li>

                @if ($products->hasMorePages())
                    <li class="page-item"><a class="page-link py-0 px-2" href="javascript:void(0)" onclick="loadPosPage('{{ $products->nextPageUrl() }}')">Next »</a></li>
                @else
                    <li class="page-item disabled"><span class="page-link py-0 px-2">Next »</span></li>
                @endif
            </ul>
        @endif
    </div>
</div>