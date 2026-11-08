@extends('layouts.admin')

@section('title', 'Branch Pricing - ' . ($product->name ?? $product->title) . ' (' . $branch->name . ')')

@section('admin_content')
<style>
    /* Global Strict 5px Element Radius */
    #branchPricingRoot .btn,
    #branchPricingRoot .btn-sm,
    #branchPricingRoot button,
    #branchPricingRoot .badge,
    #branchPricingRoot .form-control,
    #branchPricingRoot .form-select,
    #branchPricingRoot .input-group-text,
    #branchPricingRoot .card,
    #branchPricingRoot .alert,
    #branchPricingRoot .table,
    #branchPricingRoot .table th,
    #branchPricingRoot .table td {
        border-radius: 5px !important;
    }

    .bn-digit,
    .font-monospace {
        font-weight: 700 !important;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
    }

    .btn-compact {
        font-size: 11.5px !important;
        padding: 5px 12px !important;
        line-height: 1.3 !important;
        font-weight: 600 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        transition: all 0.15s ease;
    }

    .pricing-input {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
        font-weight: 700 !important;
        height: 30px !important;
        font-size: 11.5px !important;
    }

    .global-val-badge {
        font-size: 10.5px;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
        padding: 2px 6px;
        display: inline-block;
    }
</style>

<div class="container-fluid py-2 px-2 px-md-3" id="branchPricingRoot">
    <!-- Top Action & Meta Header -->
    <div class="d-flex justify-content-between align-items-center mb-3 bg-white p-2.5 px-3 shadow-xs border">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-success text-white fw-bold px-2.5 py-1.5" style="font-size: 12px;">
                <i class="fas fa-tags me-1"></i> Branch Pricing Override
            </span>
            <span class="badge bg-warning text-dark fw-bold px-2 py-1" style="font-size: 11px;">
                <i class="fas fa-store me-1"></i> {{ $branch->name }}
            </span>
            <span class="fw-bold text-dark fs-6">{{ $product->name ?? $product->title }}</span>
            <span class="badge bg-light text-dark border font-monospace" style="font-size: 11px;">
                SKU: {{ $product->sku ?? $product->product_code }}
            </span>
        </div>

        <div class="d-flex align-items-center gap-1.5">
            <a href="{{ route('admin.branch_product.index', ['branch_id' => $branch->id]) }}" class="btn btn-outline-secondary btn-compact py-1 px-2.5">
                <i class="fas fa-arrow-left me-1"></i> Branch Catalog
            </a>
            <a href="{{ route('admin.branch_product.availability', $product->id) }}" class="btn btn-light border btn-compact py-1 px-2.5">
                <i class="fas fa-shield-alt me-1"></i> Availability Policy
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 mb-3" style="font-size: 12px;">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 mb-3" style="font-size: 12px;">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close btn-sm shadow-none" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.branch_product.pricing.update', [$branch->id, $product->id]) }}" method="POST">
        @csrf

        <!-- 1. Base Product Pricing Card -->
        <div class="card border shadow-xs bg-white p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                <div>
                    <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">
                        <i class="fas fa-dollar-sign text-primary me-1"></i> Base Product Price Overrides
                    </h6>
                    <small class="text-muted" style="font-size: 10.5px;">
                        Leave blank to automatically fallback to central store price. Setting values here applies specifically to {{ $branch->name }}.
                    </small>
                </div>
                <button type="button" class="btn btn-light border btn-compact py-1 px-2" onclick="copyGlobalToBase()">
                    <i class="fas fa-copy me-1"></i> Copy Global Prices
                </button>
            </div>

            <div class="row g-2">
                <!-- Global References Display -->
                <div class="col-12 mb-2">
                    <div class="p-2.5 bg-light border d-flex gap-3 flex-wrap align-items-center" style="border-radius: 5px;">
                        <span class="text-muted fw-bold" style="font-size: 11px;">Central Defaults:</span>
                        <span class="global-val-badge">
                            Regular: <strong class="font-monospace text-dark">৳{{ number_format($product->regular_price ?? $product->price, 2) }}</strong>
                        </span>
                        <span class="global-val-badge">
                            Sale: <strong class="font-monospace text-success">৳{{ number_format($product->sale_price ?? 0, 2) }}</strong>
                        </span>
                        <span class="global-val-badge">
                            Purchase Cost: <strong class="font-monospace text-muted">৳{{ number_format($product->effective_purchase_cost, 2) }}</strong>
                        </span>
                    </div>
                </div>

                <!-- Base Branch Regular Price -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label fw-bold text-dark" style="font-size: 11px;">
                        Branch Regular Price (৳)
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light fw-bold">৳</span>
                        <input type="number" name="base_price" id="input_base_price" class="form-control pricing-input" 
                               placeholder="{{ number_format($product->regular_price ?? $product->price, 2) }}" 
                               value="{{ $basePriceRecord->price ?? '' }}" min="0" step="any">
                    </div>
                    <small class="text-muted" style="font-size: 10px;">Primary MRP for this hub</small>
                </div>

                <!-- Base Branch Sale Price -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label fw-bold text-dark" style="font-size: 11px;">
                        Branch Special / Sale Price (৳)
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light fw-bold">৳</span>
                        <input type="number" name="base_sale_price" id="input_base_sale_price" class="form-control pricing-input text-success" 
                               placeholder="{{ number_format($product->sale_price ?? 0, 2) }}" 
                               value="{{ $basePriceRecord->sale_price ?? '' }}" min="0" step="any">
                    </div>
                    <small class="text-muted" style="font-size: 10px;">Leave empty if no branch discount</small>
                </div>

                <!-- Base Branch Wholesale Price -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label fw-bold text-dark" style="font-size: 11px;">
                        Branch Wholesale Price (৳)
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light fw-bold">৳</span>
                        <input type="number" name="base_wholesale_price" id="input_base_wholesale" class="form-control pricing-input text-primary" 
                               placeholder="0.00" value="{{ $basePriceRecord->wholesale_price ?? '' }}" min="0" step="any">
                    </div>
                    <small class="text-muted" style="font-size: 10px;">For bulk / B2B sales at this hub</small>
                </div>

                <!-- Base Branch Floor / Min Selling Price -->
                <div class="col-12 col-sm-6 col-lg-3">
                    <label class="form-label fw-bold text-dark" style="font-size: 11px;">
                        Min Floor Selling Price (৳)
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light fw-bold">৳</span>
                        <input type="number" name="base_min_selling_price" id="input_base_min_price" class="form-control pricing-input text-danger" 
                               placeholder="0.00" value="{{ $basePriceRecord->min_selling_price ?? '' }}" min="0" step="any">
                    </div>
                    <small class="text-muted" style="font-size: 10px;">Lowest price cashier can discount to</small>
                </div>
            </div>
        </div>

        <!-- 2. Product Variants Specific Pricing Table (If Product has Variants) -->
        @if(!empty($variantPrices) && count($variantPrices) > 0)
            <div class="card border shadow-xs bg-white p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2 flex-wrap gap-2">
                    <div>
                        <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">
                            <i class="fas fa-layer-group text-warning me-1"></i> Variant-Level Pricing Overrides
                        </h6>
                        <small class="text-muted" style="font-size: 10.5px;">
                            Customize specific prices per variant (Size / Color) for this branch. Blank fields default to base branch price or global variant price.
                        </small>
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-compact py-0.5 px-2" onclick="copyBaseToAllVariants()">
                        <i class="fas fa-arrow-down me-1"></i> Apply Base to All Variants
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0" style="font-size: 11.5px;">
                        <thead class="table-light">
                            <tr>
                                <th class="py-2">Variant</th>
                                <th class="py-2">Global Default</th>
                                <th class="py-2" style="width: 140px;">Regular Price (৳)</th>
                                <th class="py-2" style="width: 140px;">Sale Price (৳)</th>
                                <th class="py-2" style="width: 140px;">Wholesale (৳)</th>
                                <th class="py-2" style="width: 140px;">Min Floor (৳)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($variantPrices as $vId => $item)
                                @php
                                    $v = $item['variant'];
                                    $vo = $item['override'];
                                @endphp
                                <tr>
                                    <!-- Variant Info -->
                                    <td class="py-2">
                                        <div class="fw-bold text-dark">{{ $v->variant_title }}</div>
                                        <small class="text-muted font-monospace bn-digit">SKU: {{ $v->sku ?? $v->product_code }}</small>
                                    </td>

                                    <!-- Global Reference -->
                                    <td class="py-2">
                                        <div class="font-monospace text-muted" style="font-size: 10.5px;">
                                            MRP: ৳{{ number_format($v->regular_price ?? $product->regular_price, 2) }}
                                        </div>
                                        @if(!empty($v->sale_price))
                                            <div class="font-monospace text-success" style="font-size: 10px;">
                                                Sale: ৳{{ number_format($v->sale_price, 2) }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Variant Branch Regular Price -->
                                    <td class="py-2">
                                        <input type="number" name="variants[{{ $v->id }}][price]" 
                                               class="form-control form-control-sm pricing-input variant-regular-input" 
                                               value="{{ $vo->price ?? '' }}" 
                                               placeholder="{{ number_format($v->regular_price ?? $product->regular_price, 2) }}" 
                                               min="0" step="any">
                                    </td>

                                    <!-- Variant Branch Sale Price -->
                                    <td class="py-2">
                                        <input type="number" name="variants[{{ $v->id }}][sale_price]" 
                                               class="form-control form-control-sm pricing-input text-success variant-sale-input" 
                                               value="{{ $vo->sale_price ?? '' }}" 
                                               placeholder="{{ number_format($v->sale_price ?? 0, 2) }}" 
                                               min="0" step="any">
                                    </td>

                                    <!-- Variant Branch Wholesale Price -->
                                    <td class="py-2">
                                        <input type="number" name="variants[{{ $v->id }}][wholesale_price]" 
                                               class="form-control form-control-sm pricing-input text-primary variant-wholesale-input" 
                                               value="{{ $vo->wholesale_price ?? '' }}" 
                                               placeholder="0.00" min="0" step="any">
                                    </td>

                                    <!-- Variant Branch Min Selling Price -->
                                    <td class="py-2">
                                        <input type="number" name="variants[{{ $v->id }}][min_selling_price]" 
                                               class="form-control form-control-sm pricing-input text-danger variant-min-input" 
                                               value="{{ $vo->min_selling_price ?? '' }}" 
                                               placeholder="0.00" min="0" step="any">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Sticky Submit Footer -->
        <div class="d-flex justify-content-between align-items-center bg-white p-3 border shadow-xs" style="border-radius: 5px;">
            <div class="text-muted small" style="font-size: 11.5px;">
                <i class="fas fa-info-circle text-primary me-1"></i> These prices will immediately reflect in {{ $branch->name }}'s POS terminal and receipts.
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.branch_product.index', ['branch_id' => $branch->id]) }}" class="btn btn-light border btn-compact py-2 px-3">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary fw-bold btn-compact py-2 px-4 fs-6">
                    <i class="fas fa-save me-1"></i> Save Branch Prices
                </button>
            </div>
        </div>
    </form>
</div>

<script>
const GLOBAL_REGULAR = {{ (float) ($product->regular_price ?? $product->price ?? 0) }};
const GLOBAL_SALE = {{ (float) ($product->sale_price ?? 0) }};

function copyGlobalToBase() {
    document.getElementById('input_base_price').value = GLOBAL_REGULAR > 0 ? GLOBAL_REGULAR.toFixed(2) : '';
    document.getElementById('input_base_sale_price').value = GLOBAL_SALE > 0 ? GLOBAL_SALE.toFixed(2) : '';
}

function copyBaseToAllVariants() {
    const baseReg = document.getElementById('input_base_price').value;
    const baseSale = document.getElementById('input_base_sale_price').value;
    const baseWholesale = document.getElementById('input_base_wholesale').value;
    const baseMin = document.getElementById('input_base_min_price').value;

    if (!baseReg && !baseSale) {
        alert('Please enter Base Branch Regular or Sale price first.');
        return;
    }

    if (baseReg) {
        document.querySelectorAll('.variant-regular-input').forEach(input => input.value = baseReg);
    }
    if (baseSale) {
        document.querySelectorAll('.variant-sale-input').forEach(input => input.value = baseSale);
    }
    if (baseWholesale) {
        document.querySelectorAll('.variant-wholesale-input').forEach(input => input.value = baseWholesale);
    }
    if (baseMin) {
        document.querySelectorAll('.variant-min-input').forEach(input => input.value = baseMin);
    }
}
</script>
@endsection