@php
    $cart = $cart ?? session()->get('cart', []);
    $total = 0;

    // সার্বজনীন কার্ট ইমেজ রেজলভার
    $resolveDrawerImg = function($path, $fallback = 'https://placehold.co/100x100?text=No+Image') {
        if (empty($path)) return $fallback;
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        $clean = ltrim(str_replace(['storage/', 'public/'], '', $path), '/');
        if (file_exists(public_path($clean))) return asset($clean);
        if (file_exists(public_path('uploads/' . $clean))) return asset('uploads/' . $clean);
        return asset($clean);
    };
@endphp

@if(!empty($cart) && count($cart) > 0)
    <div class="d-flex flex-column gap-2 px-2 py-1">
        @foreach($cart as $key => $item)
            @php
                $itemTotal = (float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 1);
                $total += $itemTotal;
                $itemImg = $resolveDrawerImg($item['image'] ?? null);
            @endphp
            <div class="d-flex gap-2 align-items-center bg-white p-2 rounded-3 border position-relative" id="drawer-item-{{ $key }}">
                <!-- প্রোডাক্ট ছবি -->
                <div class="rounded-3 bg-light p-1 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; border: 1px solid #f1f5f9;">
                    <img src="{{ $itemImg }}" alt="{{ $item['title'] ?? 'Product' }}" class="img-fluid object-fit-contain rounded" style="max-height: 100%; max-width: 100%;" loading="lazy">
                </div>

                <!-- ইনফো -->
                <div class="flex-grow-1 min-w-0 px-1">
                    <h6 class="fw-bold text-dark text-truncate mb-1" style="font-size: 12px;" title="{{ $item['title'] ?? '' }}">
                        <a href="{{ route('product.details', $item['slug'] ?? ($item['id'] ?? $key)) }}" class="text-decoration-none text-dark">
                            {{ $item['title'] ?? 'Product' }}
                        </a>
                    </h6>
                    
                    @if(!empty($item['variant_name']))
                        <span class="badge bg-light text-secondary border small mb-1" style="font-size: 9px; padding: 2px 4px;">{{ $item['variant_name'] }}</span>
                    @endif

                    <div class="d-flex align-items-center justify-content-between mt-1 gap-1">
                        <!-- কোয়ান্টিটি কন্ট্রোল -->
                        <div class="input-group input-group-sm flex-shrink-0" style="width: 72px;">
                            <button class="btn btn-light border py-0 px-1 fw-bold" type="button" onclick="updateCartDrawerQty('{{ $key }}', 'dec')">-</button>
                            <input type="text" class="form-control text-center p-0 fw-bold border-top border-bottom bg-white" value="{{ $item['quantity'] ?? 1 }}" readonly style="font-size: 11px;">
                            <button class="btn btn-light border py-0 px-1 fw-bold" type="button" onclick="updateCartDrawerQty('{{ $key }}', 'inc')">+</button>
                        </div>

                        <!-- প্রাইস -->
                        <div class="text-end ps-1">
                            <strong class="text-dark fw-bold" style="font-size: 12.5px;">৳{{ number_format($itemTotal, 2) }}</strong>
                        </div>
                    </div>
                </div>

                <!-- ডিলিট বাটন -->
                <button type="button" class="btn btn-link text-danger p-1 text-decoration-none flex-shrink-0" onclick="removeCartDrawerItem('{{ $key }}')" title="Remove item">
                    <i class="far fa-trash-alt" style="font-size: 12px;"></i>
                </button>
            </div>
        @endforeach
    </div>
@else
    <div class="text-center py-5 px-3 my-auto">
        <div class="mb-3">
            <i class="fas fa-shopping-basket text-secondary opacity-25" style="font-size: 3.5rem;"></i>
        </div>
        <h6 class="fw-bold text-dark mb-1 fs-6">Your cart is empty</h6>
        <p class="text-muted small mb-3 px-2">No products added yet.</p>
        <a href="{{ route('shop') }}" class="btn btn-sm btn-primary rounded-pill px-4">
            Start Shopping
        </a>
    </div>
@endif