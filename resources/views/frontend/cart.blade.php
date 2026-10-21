@extends('layouts.app')

@section('title', 'Shopping Cart - ' . config('app.name', 'SIT ECOMMERCE'))

@section('content')
@php
    $subtotal = 0;
    foreach($cart as $item) {
        $subtotal += ($item['price'] * $item['quantity']);
    }
    $deliveryCharge = 70;
    $grandTotal = $subtotal > 0 ? ($subtotal + $deliveryCharge) : 0;
    $isEmpty = count($cart) === 0;

    // সার্বজনীন কার্ট ইমেজ রেজলভার (Localhost ও cPanel উভয়ের জন্য নিরাপদ)
    $resolveCartImg = function($path, $fallback = 'https://placehold.co/100x100?text=Product') {
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

<style>
    .cart-item-card {
        border: 1px solid #edf2f7;
        border-radius: 14px;
        background: #ffffff;
        transition: all 0.2s ease;
    }
    .cart-item-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
    }
    .cart-img-wrapper {
        width: 60px;
        height: 60px;
        background: #f8fafc;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4px;
        flex-shrink: 0;
    }
    .qty-pill {
        background: #f1f5f9;
        border-radius: 8px;
        padding: 2px 6px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .qty-action-btn {
        border: none;
        background: transparent;
        color: #475569;
        font-weight: bold;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: 0.2s;
    }
    .qty-action-btn:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .qty-value {
        font-size: 13px;
        font-weight: 700;
        min-width: 16px;
        text-align: center;
        color: #1e293b;
    }
    .trash-action-btn {
        background: none;
        border: none;
        color: #94a3b8;
        font-size: 15px;
        transition: 0.2s;
        padding: 4px;
        border-radius: 6px;
    }
    .trash-action-btn:hover {
        color: #ef4444;
        background: #fef2f2;
    }
    .panel-card {
        border: 1px solid #edf2f7;
        border-radius: 16px;
        background: #ffffff;
        padding: 16px;
    }
    @media (min-width: 768px) {
        .panel-card { padding: 24px; }
        .cart-img-wrapper { width: 70px; height: 70px; }
    }

    /* মোবাইলে সুপার ক্লিন ২-স্তরের রেসপন্সিভ কার্ড */
    @media (max-width: 575.98px) {
        .cart-item-card {
            padding: 10px !important;
            flex-direction: row !important;
            align-items: flex-start !important;
            gap: 10px !important;
        }
        .cart-img-wrapper {
            width: 52px !important;
            height: 52px !important;
        }
        .cart-content-box {
            flex: 1 1 auto;
            min-width: 0;
        }
        .cart-mobile-bottom-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 6px;
            width: 100%;
        }
        .desktop-cart-block {
            display: none !important;
        }
    }
    @media (min-width: 576px) {
        .mobile-cart-block {
            display: none !important;
        }
    }
</style>

<div class="bg-light py-3 py-md-5">
    <div class="container px-2 px-md-3">

        <!-- Empty State -->
        <div id="cart-empty-state" class="{{ $isEmpty ? '' : 'd-none' }} text-center py-5 panel-card shadow-sm my-4">
            <div class="mb-3 text-secondary">
                <i class="fas fa-shopping-bag fa-3x fa-md-4x text-muted opacity-50"></i>
            </div>
            <h4 class="fw-bold text-dark fs-5 fs-md-4">Your cart is empty</h4>
            <p class="text-muted small">Explore our store and add items to your cart.</p>
            <a href="{{ route('shop') }}" class="btn btn-primary px-4 py-2 mt-2 rounded-pill fw-semibold small">
                Start Shopping
            </a>
        </div>

        <!-- Active Cart Section -->
        <div id="cart-main-content" class="row g-3 g-md-4 {{ $isEmpty ? 'd-none' : '' }}">
            
            <!-- Left Side: Product List -->
            <div class="col-12 col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="fw-bold text-dark mb-0 fs-5 fs-md-4">Shopping Cart</h3>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold" style="font-size: 11.5px;" onclick="openCancelOrderModal()">
                        <i class="fas fa-trash-alt me-1"></i> Clear Cart
                    </button>
                </div>

                <div class="d-none d-md-flex justify-content-between text-muted small px-3 mb-2 fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                    <span>Product</span>
                    <div class="d-flex gap-5" style="margin-right: 40px;">
                        <span>Quantity</span>
                        <span>Price</span>
                    </div>
                </div>

                <!-- Items Container -->
                <div id="cart-items-wrapper" class="d-flex flex-column gap-2 gap-md-3 mb-3">
                    @foreach($cart as $id => $item)
                        @php
                            $itemImg = $resolveCartImg($item['image'] ?? null);
                            $itemTotal = $item['price'] * $item['quantity'];
                            $variantLabel = $item['variant_name'] ?? (!empty($item['color']) || !empty($item['size']) ? trim(($item['color'] ?? '') . ' / ' . ($item['size'] ?? '')) : null);
                        @endphp
                        <div id="cart-item-row-{{ $id }}" class="cart-item-card p-2 p-md-3 d-flex align-items-center justify-content-between gap-2">
                            
                            <!-- Thumbnail -->
                            <div class="cart-img-wrapper flex-shrink-0">
                                <img src="{{ $itemImg }}" alt="{{ $item['title'] }}" class="img-fluid rounded object-fit-contain" style="max-height: 48px;" onerror="this.onerror=null; this.src='https://placehold.co/100x100?text=Product';">
                            </div>

                            <!-- Mobile Layout -->
                            <div class="cart-content-box mobile-cart-block">
                                <div class="d-flex justify-content-between align-items-start gap-1">
                                    <h6 class="mb-0 fw-bold text-dark text-truncate small" style="max-width: 200px;" title="{{ $item['title'] }}">
                                        {{ $item['title'] }}
                                    </h6>
                                    <button type="button" class="trash-action-btn p-0" onclick="openDeleteModal('{{ $id }}', '{{ addslashes($item['title']) }}')" title="Remove item">
                                        <i class="far fa-trash-alt text-danger" style="font-size: 13px;"></i>
                                    </button>
                                </div>
                                @if(!empty($variantLabel))
                                    <span class="badge bg-light text-secondary border small my-1" style="font-size: 9px; padding: 1px 4px;">{{ $variantLabel }}</span>
                                @endif
                                <small class="text-muted d-block" style="font-size: 11px;">Unit: ৳{{ number_format($item['price'], 0) }}</small>
                                
                                <div class="cart-mobile-bottom-row">
                                    <div class="qty-pill py-0.5">
                                        <button type="button" class="qty-action-btn" onclick="updateQuantityAJAX('{{ $id }}', -1)">-</button>
                                        <span id="qty-val-mob-{{ $id }}" class="qty-value" style="font-size: 11px;">{{ $item['quantity'] }}</span>
                                        <button type="button" class="qty-action-btn" onclick="updateQuantityAJAX('{{ $id }}', 1)">+</button>
                                    </div>
                                    <div class="fw-bold text-dark small" style="font-size: 13px;">
                                        ৳<span id="item-total-mob-{{ $id }}">{{ number_format($itemTotal, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Desktop Layout -->
                            <div class="d-none d-sm-flex align-items-center justify-content-between flex-grow-1 desktop-cart-block">
                                <div class="min-w-0 ps-2" style="max-width: 55%;">
                                    <h6 class="mb-1 fw-bold text-dark text-truncate small" title="{{ $item['title'] }}">
                                        {{ $item['title'] }}
                                    </h6>
                                    @if(!empty($variantLabel))
                                        <span class="badge bg-light text-secondary border small mb-1" style="font-size: 9.5px; padding: 2px 5px;">{{ $variantLabel }}</span>
                                    @endif
                                    <small class="text-muted d-block" style="font-size: 11px;">Unit: ৳{{ number_format($item['price'], 2) }}</small>
                                </div>

                                <div class="d-flex align-items-center gap-3 gap-md-4 flex-shrink-0">
                                    <div class="qty-pill">
                                        <button type="button" class="qty-action-btn" onclick="updateQuantityAJAX('{{ $id }}', -1)">-</button>
                                        <span id="qty-val-{{ $id }}" class="qty-value">{{ $item['quantity'] }}</span>
                                        <button type="button" class="qty-action-btn" onclick="updateQuantityAJAX('{{ $id }}', 1)">+</button>
                                    </div>

                                    <div class="fw-bold text-dark small" style="min-width: 75px; text-align: right; font-size: 13px;">
                                        ৳<span id="item-total-{{ $id }}">{{ number_format($itemTotal, 2) }}</span>
                                    </div>

                                    <button type="button" class="trash-action-btn" onclick="openDeleteModal('{{ $id }}', '{{ addslashes($item['title']) }}')" title="Remove item">
                                        <i class="far fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>

                <!-- Bottom Back & Cancel Order -->
                <div class="d-flex justify-content-between align-items-center pt-2">
                    <a href="{{ route('shop') }}" class="btn btn-outline-secondary px-3 px-md-4 py-2 rounded-3 fw-semibold small">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                    <button type="button" class="btn btn-danger px-3 px-md-4 py-2 rounded-3 fw-semibold small shadow-sm" onclick="openCancelOrderModal()">
                        Cancel Order
                    </button>
                </div>
            </div>

            <!-- Right Side: Summary & Checkout -->
            <div class="col-12 col-lg-4">
                <div class="d-flex flex-column gap-3">
                    
                    <!-- Coupon Box -->
                    <div class="panel-card shadow-sm">
                        <h6 class="fw-bold text-dark mb-2 fs-6">Coupon Code</h6>
                        <div class="mb-2">
                            <input type="text" class="form-control form-control-sm py-2 rounded-3 bg-light border-0" placeholder="Enter coupon code">
                        </div>
                        <button type="button" class="btn btn-outline-primary w-100 py-2 rounded-3 fw-semibold small" onclick="alert('Feature coming soon!')">
                            Apply Coupon
                        </button>
                    </div>

                    <!-- Order Summary Panel -->
                    <div class="panel-card shadow-sm">
                        <h5 class="fw-bold text-dark mb-3 fs-6">Order Summary</h5>

                        <div class="d-flex justify-content-between text-secondary mb-2 small">
                            <span>Subtotal</span>
                            <strong class="text-dark">৳<span id="summary-subtotal">{{ number_format($subtotal, 2) }}</span></strong>
                        </div>

                        <!-- Area Wise Delivery Selector -->
                        <div class="mb-2">
                            <label class="small text-muted fw-bold d-block mb-1">Delivery Area</label>
                            <select id="cart-delivery-selector" class="form-select form-select-sm rounded-3 py-2 bg-light border-0" onchange="recalculateTotals()">
                                <option value="inside" data-cost="70" selected>Inside Dhaka (৳70)</option>
                                <option value="outside" data-cost="130">Outside Dhaka (৳130)</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-between text-secondary mb-2 small">
                            <span>Delivery Charge</span>
                            <strong class="text-dark">৳<span id="summary-delivery">70.00</span></strong>
                        </div>

                        <hr class="my-2 opacity-25">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold text-dark small">Total</span>
                            <h4 class="fw-bold text-dark mb-0 fs-5">৳<span id="summary-grandtotal">{{ number_format($subtotal + 70, 2) }}</span></h4>
                        </div>

                        <a href="{{ route('checkout') }}" class="btn btn-primary w-100 py-2.5 rounded-3 fw-bold shadow-sm text-center small">
                            Check Out
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- MODALS -->
<div class="modal fade" id="customDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content border-0 rounded-4 shadow-lg p-3 text-center">
            <div class="modal-body pt-3">
                <div class="mb-2">
                    <div class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-circle" style="width: 55px; height: 55px;">
                        <i class="far fa-trash-alt fa-lg"></i>
                    </div>
                </div>
                <h5 class="fw-bold text-dark mb-1 fs-6">Remove Product?</h5>
                <p class="text-muted small mb-0">Remove <strong id="modal-product-name" class="text-dark"></strong> from cart?</p>
            </div>
            <div class="modal-footer border-0 d-flex gap-2 justify-content-center pb-2">
                <button type="button" class="btn btn-light px-3 py-1.5 rounded-3 small fw-semibold text-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirm-delete-action-btn" class="btn btn-danger px-3 py-1.5 rounded-3 small fw-semibold">Remove</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="customCancelOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content border-0 rounded-4 shadow-lg p-3 text-center">
            <div class="modal-body pt-3">
                <div class="mb-2">
                    <div class="d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning rounded-circle" style="width: 55px; height: 55px;">
                        <i class="fas fa-exclamation-triangle fa-lg text-danger"></i>
                    </div>
                </div>
                <h5 class="fw-bold text-dark mb-1 fs-6">Clear Cart?</h5>
                <p class="text-muted small mb-0">This will clear all items from your cart.</p>
            </div>
            <div class="modal-footer border-0 d-flex gap-2 justify-content-center pb-2">
                <button type="button" class="btn btn-light px-3 py-1.5 rounded-3 small fw-semibold text-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger px-3 py-1.5 rounded-3 small fw-semibold" onclick="executeClearCart()">Yes, Clear</button>
            </div>
        </div>
    </div>
</div>

<script>
    let itemToDeleteId = null;
    let deleteModalObj = null;
    let cancelOrderModalObj = null;

    document.addEventListener('DOMContentLoaded', () => {
        deleteModalObj = new bootstrap.Modal(document.getElementById('customDeleteModal'));
        cancelOrderModalObj = new bootstrap.Modal(document.getElementById('customCancelOrderModal'));

        document.getElementById('confirm-delete-action-btn').addEventListener('click', () => {
            if (itemToDeleteId) {
                performItemRemoval(itemToDeleteId);
            }
        });
    });

    function openDeleteModal(productId, productTitle) {
        itemToDeleteId = productId;
        document.getElementById('modal-product-name').innerText = `"${productTitle}"`;
        deleteModalObj.show();
    }

    function openCancelOrderModal() {
        cancelOrderModalObj.show();
    }

    function recalculateTotals() {
        const subtotal = parseFloat(document.getElementById('summary-subtotal').innerText.replace(/,/g, '')) || 0;
        const selector = document.getElementById('cart-delivery-selector');
        const deliveryCost = parseFloat(selector.options[selector.selectedIndex].getAttribute('data-cost')) || 70;

        document.getElementById('summary-delivery').innerText = deliveryCost.toFixed(2);
        const grandTotal = subtotal + deliveryCost;
        document.getElementById('summary-grandtotal').innerText = grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateQuantityAJAX(productId, change) {
        const qtySpan = document.getElementById(`qty-val-${productId}`) || document.getElementById(`qty-val-mob-${productId}`);
        let currentQty = parseInt(qtySpan ? qtySpan.innerText : 1) || 1;
        let newQty = currentQty + change;

        if (newQty <= 0) {
            openDeleteModal(productId, 'this product');
            return;
        }

        const deliveryType = document.getElementById('cart-delivery-selector').value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch("{{ route('cart.updateQty') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ id: productId, quantity: newQty, delivery_type: deliveryType })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (document.getElementById(`qty-val-${productId}`)) document.getElementById(`qty-val-${productId}`).innerText = newQty;
                if (document.getElementById(`qty-val-mob-${productId}`)) document.getElementById(`qty-val-mob-${productId}`).innerText = newQty;
                if (document.getElementById(`item-total-${productId}`)) document.getElementById(`item-total-${productId}`).innerText = data.item_total;
                if (document.getElementById(`item-total-mob-${productId}`)) document.getElementById(`item-total-mob-${productId}`).innerText = data.item_total;
                
                document.getElementById('summary-subtotal').innerText = data.cart_subtotal;
                recalculateTotals();

                if (window.updateAllCartBadges) {
                    window.updateAllCartBadges(data.cart_count);
                }
            }
        })
        .catch(err => console.error('Qty update error:', err));
    }

    function performItemRemoval(productId) {
        const confirmBtn = document.getElementById('confirm-delete-action-btn');
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Removing...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch("{{ route('cart.remove') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ id: productId })
        })
        .then(res => res.json())
        .then(data => {
            confirmBtn.disabled = false;
            confirmBtn.innerText = 'Remove';
            deleteModalObj.hide();

            if (data.status === 'success') {
                const row = document.getElementById(`cart-item-row-${productId}`);
                if (row) {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'scale(0.95)';
                    setTimeout(() => row.remove(), 300);
                }

                document.getElementById('summary-subtotal').innerText = data.cart_subtotal;
                recalculateTotals();

                if (window.updateAllCartBadges) {
                    window.updateAllCartBadges(data.cart_count);
                }

                if (data.is_empty) {
                    document.getElementById('cart-main-content').classList.add('d-none');
                    document.getElementById('cart-empty-state').classList.remove('d-none');
                }
            }
        })
        .catch(err => {
            console.error('Remove error:', err);
            confirmBtn.disabled = false;
            confirmBtn.innerText = 'Remove';
        });
    }

    function executeClearCart() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch("{{ route('cart.remove') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ id: 'all_clear' })
        })
        .then(res => res.json())
        .then(() => {
            cancelOrderModalObj.hide();
            if (window.updateAllCartBadges) {
                window.updateAllCartBadges(0);
            }
            document.getElementById('cart-main-content').classList.add('d-none');
            document.getElementById('cart-empty-state').classList.remove('d-none');
        });
    }
</script>
@endsection