@forelse($products as $p)
    @php
        $imgUrl = '';
        if (!empty($p->thumbnail)) {
            $t = ltrim($p->thumbnail, '/');
            if (str_starts_with($t, 'http://') || str_starts_with($t, 'https://')) {
                $imgUrl = $t;
            } elseif (str_starts_with($t, 'uploads/')) {
                $imgUrl = asset($t);
            } elseif (str_starts_with($t, 'storage/')) {
                $imgUrl = asset($t);
            } elseif (file_exists(public_path('uploads/' . $t))) {
                $imgUrl = asset('uploads/' . $t);
            } elseif (file_exists(public_path($t))) {
                $imgUrl = asset($t);
            } elseif (file_exists(public_path('storage/' . $t))) {
                $imgUrl = asset('storage/' . $t);
            } else {
                $imgUrl = asset('uploads/' . $t);
            }
        }
        $finalPrice = (float) (!empty($p->sale_price) && $p->sale_price > 0 ? $p->sale_price : $p->regular_price);
        
        $variants = \Illuminate\Support\Facades\Schema::hasTable('product_variants') 
            ? \Illuminate\Support\Facades\DB::table('product_variants')->where('product_id', $p->id)->get() 
            : collect();
        $hasVariants = $variants->count() > 0;
        $branchStock = (int) ($p->branch_stock ?? 0);
    @endphp
    <div class="col-6 col-sm-6 col-md-4 col-xl-3 mb-2">
        <div class="card border shadow-sm rounded-3 p-2 bg-white d-flex flex-column justify-content-between h-100" style="min-height: 270px;">
            <div>
                <!-- ইমেজ বক্স -->
                <div class="rounded-2 overflow-hidden bg-light mb-2 border position-relative d-flex align-items-center justify-content-center" style="height: 120px; width: 100%;">
                    @if(!empty($imgUrl))
                        <img src="{{ $imgUrl }}" class="w-100 h-100 object-fit-cover" onerror="this.onerror=null; this.src='https://placehold.co/150x150?text=No+Image';">
                    @else
                        <img src="https://placehold.co/150x150?text=No+Image" class="w-100 h-100 object-fit-cover">
                    @endif

                    @if($hasVariants)
                        <span class="badge bg-primary position-absolute top-0 end-0 m-1.5 shadow-xs" style="font-size: 9px; padding: 2px 5px;">
                            <i class="fas fa-layer-group me-0.5"></i> {{ $variants->count() }} Var
                        </span>
                    @endif
                </div>

                <!-- টাইটেল ও প্রাইস -->
                <h6 class="fw-semibold text-dark text-truncate small mb-1" title="{{ $p->title }}" style="font-size: 12px; line-height: 1.2;">{{ $p->title }}</h6>
                
                <div class="d-flex justify-content-between align-items-center mb-1.5">
                    <span class="fw-bold text-primary small" id="gridPrice{{ $p->id }}">{{ number_format($finalPrice, 2) }} ৳</span>
                    <small class="text-muted" style="font-size: 11px;">
                        Stock: <span class="fw-bold {{ $branchStock > 0 ? 'text-success' : 'text-danger' }}" id="gridStock{{ $p->id }}">{{ $branchStock }}</span>
                    </small>
                </div>

                <!-- ভ্যারিয়েন্ট ড্রপডাউন (যদি ভ্যারিয়েশন থাকে) -->
                @if($hasVariants)
                    <div class="mb-1.5">
                        <select id="gridVariantSelect{{ $p->id }}" 
                                class="form-select form-select-sm py-0 px-1 border-primary fw-semibold text-dark shadow-none" 
                                style="font-size: 10.5px; height: 26px; border-radius: 4px;"
                                onchange="onGridVariantChanged({{ $p->id }}, this)">
                            <option value="" data-price="{{ $finalPrice }}" data-stock="{{ $branchStock }}">-- Select Variant --</option>
                            @foreach($variants as $v)
                                @php
                                    $vParts = array_filter([$v->color ?? null, $v->size ?? null]);
                                    $vLabel = !empty($vParts) ? implode(' / ', $vParts) : ($v->name ?? ('Variant #' . $v->id));
                                    $vPrice = (float) (!empty($v->sale_price) && $v->sale_price > 0 ? $v->sale_price : (!empty($v->regular_price) && $v->regular_price > 0 ? $v->regular_price : $finalPrice));
                                    $vStock = (int) ($v->stock ?? 0);
                                    $vCode  = $v->product_code ?? ($v->sku ?? ('VAR-#' . $v->id));
                                @endphp
                                <option value="{{ $v->id }}" 
                                        data-price="{{ $vPrice }}" 
                                        data-stock="{{ $vStock }}"
                                        data-code="{{ $vCode }}"
                                        data-title="{{ addslashes($p->title . ' (' . $vLabel . ')') }}">
                                    {{ $vLabel }} ({{ number_format($vPrice, 0) }}৳ | Stk: {{ $vStock }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            <div class="mt-auto">
                <!-- কোয়ান্টিটি ইনপুট -->
                <div class="input-group input-group-sm mb-2 border rounded-1 overflow-hidden" style="height: 28px;">
                    <span class="input-group-text bg-primary text-white border-0 py-0 px-2 fw-semibold" style="font-size: 11px;">Quantity</span>
                    <input type="number" id="gridQty{{ $p->id }}" class="form-control form-control-sm text-center border-0 p-0 fw-bold" value="1" min="1" max="{{ $branchStock > 0 ? $branchStock : 9999 }}">
                </div>

                <!-- বাটন (আপনার আগের মতোই সরাসরি addToPosMemo কল করবে) -->
                <button type="button" class="btn btn-primary btn-sm w-100 py-1 fw-semibold text-white rounded-1 shadow-none text-nowrap" style="font-size: 12px;"
                        onclick="addToPosMemo({{ $p->id }}, '{{ addslashes($p->title) }}', {{ $finalPrice }}, {{ $branchStock }}, '{{ $imgUrl }}')">
                    Add to pos memo
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
<div class="col-12 mt-2 py-2 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 bg-white border rounded px-3 small text-muted text-center text-sm-start">
    <div>Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} results</div>
    <div>
        @if ($products->hasPages())
            <ul class="pagination pagination-sm mb-0 justify-content-center">
                @if ($products->onFirstPage())
                    <li class="page-item disabled"><span class="page-link py-0 px-2">« Prev</span></li>
                @else
                    <li class="page-item"><a class="page-link py-0 px-2" href="javascript:void(0)" onclick="loadPosPage('{{ $products->previousPageUrl() }}')">« Prev</a></li>
                @endif

                <li class="page-item disabled"><span class="page-link py-0 px-2">{{ $products->currentPage() }} / {{ $products->lastPage() }}</span></li>

                @if ($products->hasMorePages())
                    <li class="page-item"><a class="page-link py-0 px-2" href="javascript:void(0)" onclick="loadPosPage('{{ $products->nextPageUrl() }}')">Next »</a></li>
                @else
                    <li class="page-item disabled"><span class="page-link py-0 px-2">Next »</span></li>
                @endif
            </ul>
        @endif
    </div>
</div>