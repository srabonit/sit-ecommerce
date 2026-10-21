@php
    $cart = $cart ?? session()->get('cart', []);
    $total = 0;

    // সেন্ট্রাল ইমেজ রেজলভার (Localhost ও cPanel উভয়ের জন্য নিরাপদ)
    $resolveDrawerImg = function($path, $fallback = 'https://placehold.co/100x100?text=No+Image') {
        if (empty($path) || $path === 'undefined' || $path === 'null') {
            return $fallback;
        }
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) {
            return show_image($path, $fallback);
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        return asset(ltrim($path, '/'));
    };
@endphp

@if(!empty($cart) && count($cart) > 0)
    <div class="d-flex flex-column gap-2 px-2 py-1">
        @foreach($cart as $key => $item)
            @php
                $itemPrice = $item['price'] ?? 0;
                if (is_string($itemPrice)) {
                    $itemPrice = preg_replace('/[^0-9.]/', '', $itemPrice);
                }
                $itemTotal = (float)$itemPrice * (int)($item['quantity'] ?? 1);
                $total += $itemTotal;

                $itemImg = $resolveDrawerImg($item['image'] ?? null);
                $variantLabel = $item['variant_name'] ?? (!empty($item['color']) || !empty($item['size']) ? trim(($item['color'] ?? '') . ' / ' . ($item['size'] ?? '')) : null);
            @endphp
            <div class="d-flex gap-2 align-items-center bg-white p-2 rounded-3 border position-relative shadow-xs" id="drawer-item-{{ $key }}">
                <!-- প্রোডাক্ট ছবি -->
                <div class="rounded-2 bg-light p-1 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border: 1px solid #e2e8f0;">
                    <img src="{{ $itemImg }}" alt="{{ $item['title'] ?? 'Product' }}" class="img-fluid object-fit-contain rounded" style="max-height: 100%; max-width: 100%;" loading="lazy" onerror="this.onerror=null; this.src='https://placehold.co/100x100?text=No+Image';">
                </div>

                <!-- ইনফো ও কোয়ান্টিটি কন্ট্রোল -->
                <div class="flex-grow-1 min-w-0 px-1" style="overflow: hidden;">
                    <h6 class="fw-bold text-dark text-truncate mb-1" style="font-size: 11.5px; line-height: 1.25;" title="{{ $item['title'] ?? '' }}">
                        <a href="{{ route('product.details', $item['slug'] ?? ($item['id'] ?? $key)) }}" class="text-decoration-none text-dark">
                            {{ $item['title'] ?? 'Product' }}
                        </a>
                    </h6>
                    
                    @if(!empty($variantLabel))
                        <span class="badge bg-light text-secondary border small mb-1" style="font-size: 8.5px; padding: 1px 4px;">{{ $variantLabel }}</span>
                    @endif

                    @if(!empty($item['sku']))
                        <small class="text-muted d-block font-monospace" style="font-size: 8.5px; line-height: 1;">SKU: {{ $item['sku'] }}</small>
                    @endif

                    <div class="d-flex align-items-center justify-content-between mt-1 gap-1">
                        <!-- কোয়ান্টিটি প্লাস/মাইনাস -->
                        <div class="input-group input-group-sm flex-shrink-0" style="width: 68px; height: 22px;">
                            <button class="btn btn-light border p-0 d-flex align-items-center justify-content-center" type="button" onclick="updateCartDrawerQty('{{ $key }}', 'dec')" style="width: 20px; font-size: 10px;">-</button>
                            <input type="text" class="form-control text-center p-0 fw-bold border-top border-bottom bg-white" value="{{ $item['quantity'] ?? 1 }}" readonly style="font-size: 11px; height: 22px;">
                            <button class="btn btn-light border p-0 d-flex align-items-center justify-content-center" type="button" onclick="updateCartDrawerQty('{{ $key }}', 'inc')" style="width: 20px; font-size: 10px;">+</button>
                        </div>

                        <!-- প্রাইস -->
                        <div class="text-end flex-grow-1 ps-1">
                            <strong class="text-dark fw-bold" style="font-size: 12px;">৳{{ number_format($itemTotal, 2) }}</strong>
                        </div>
                    </div>
                </div>

                <!-- রিমুভ বাটন -->
                <button type="button" class="btn btn-link text-danger p-1 flex-shrink-0 text-decoration-none ms-1" onclick="removeCartDrawerItem('{{ $key }}')" title="Remove">
                    <i class="far fa-trash-alt" style="font-size: 11px;"></i>
                </button>
            </div>
        @endforeach
    </div>
@else
    <div class="text-center py-5 px-3 my-auto">
        <div class="mb-3">
            <i class="fas fa-shopping-basket text-secondary opacity-25" style="font-size: 3rem;"></i>
        </div>
        <h6 class="fw-bold text-dark mb-1 fs-6">Your cart is empty</h6>
        <p class="text-muted small mb-3 px-2" style="font-size: 11.5px;">No products added yet.</p>
        <a href="{{ route('shop') }}" class="btn btn-sm btn-primary rounded-pill px-4">
            Start Shopping
        </a>
    </div>
@endif