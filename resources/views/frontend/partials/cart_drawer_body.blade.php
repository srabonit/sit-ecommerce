@php
    $cart = $cart ?? session()->get('cart', []);
@endphp

{{-- 1. Cart Drawer Items & Coupon Section --}}
<div id="cart-drawer-content-wrapper">
    @include('frontend.partials.cart_drawer_items', ['cart' => $cart])
</div>

{{-- 2. Permanent Cart Drawer Script (No Modal, Short Inline Message Below Input) --}}
<script>
if (typeof window.applyDrawerCouponDirect === 'undefined') {
    window.showDrawerInlineMessage = function(message, isSuccess) {
        var msgEl = document.getElementById('drawer-coupon-inline-msg');
        if (msgEl) {
            msgEl.innerText = message;
            msgEl.style.color = isSuccess ? '#10b981' : '#ef4444';
            msgEl.classList.remove('d-none');
        }
    };

    window.applyDrawerCouponDirect = function(btn) {
        var box = btn.closest('.drawer-coupon-box');
        var input = box ? box.querySelector('#drawer-coupon-input') : document.getElementById('drawer-coupon-input');
        var code = input ? input.value.trim() : '';

        if (!code) {
            window.showDrawerInlineMessage('Please enter a valid coupon code.', false);
            return;
        }

        var oldText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

        var csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '{{ csrf_token() }}';

        fetch("{{ route('cart.coupon.apply') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ coupon_code: code })
        })
        .then(function(res) {
            return res.json().then(function(data) {
                if (!res.ok) {
                    throw new Error(data.message || 'Invalid coupon code. Please check and try again.');
                }
                return data;
            });
        })
        .then(function(data) {
            window.showDrawerInlineMessage(data.message || 'Coupon applied successfully!', true);
            window.syncDrawerData(data);
        })
        .catch(function(err) {
            window.showDrawerInlineMessage(err.message || 'Invalid coupon code. Please check and try again.', false);
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = oldText;
        });
    };

    window.removeDrawerCouponDirect = function(btn) {
        var csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '{{ csrf_token() }}';

        fetch("{{ route('cart.coupon.remove') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            window.showDrawerInlineMessage(data.message || 'Coupon removed.', false);
            var input = document.getElementById('drawer-coupon-input');
            if (input) input.value = '';
            window.syncDrawerData(data);
        });
    };

    window.applyDrawerVoucherDirect = function(code, btn) {
        var csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '{{ csrf_token() }}';

        fetch("{{ route('cart.voucher.apply') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ voucher_code: code })
        })
        .then(function(res) {
            return res.json().then(function(data) {
                if (!res.ok) {
                    throw new Error(data.message || 'Could not apply voucher.');
                }
                return data;
            });
        })
        .then(function(data) {
            window.showDrawerInlineMessage(data.message || 'Voucher applied successfully!', true);
            window.syncDrawerData(data);
        })
        .catch(function(err) {
            window.showDrawerInlineMessage(err.message || 'Could not apply voucher.', false);
        });
    };

    window.removeDrawerVoucherDirect = function(btn) {
        var csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '{{ csrf_token() }}';

        fetch("{{ route('cart.voucher.remove') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            window.showDrawerInlineMessage(data.message || 'Voucher removed.', false);
            window.syncDrawerData(data);
        });
    };

    window.claimAndApplyDrawerVoucherDirect = function(voucherId, btn) {
        @guest
            window.location.href = "{{ route('login') }}";
            return;
        @endguest

        var csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '{{ csrf_token() }}';
        var oldText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

        fetch("{{ url('/user/vouchers/claim') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ voucher_id: voucherId })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success && data.voucher_code) {
                window.applyDrawerVoucherDirect(data.voucher_code, btn);
            } else {
                window.showDrawerInlineMessage(data.message || 'Could not claim voucher.', false);
            }
        })
        .catch(function() {
            window.showDrawerInlineMessage('Failed to claim voucher.', false);
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = oldText;
        });
    };

    window.syncDrawerData = function(data) {
        var wrap = document.getElementById('drawer-summary-breakdown');
        var discEl = document.getElementById('drawer-discount-calc');
        var totalEl = document.getElementById('drawer-grandtotal-calc');
        var subtotalEl = document.getElementById('drawer-subtotal-calc');
        var badgesWrap = document.getElementById('drawer-applied-discounts-wrap');

        var totalDisc = parseFloat(data.total_discount || 0);

        if (subtotalEl && data.subtotal) subtotalEl.innerText = data.subtotal;
        if (discEl) discEl.innerText = totalDisc.toFixed(2);
        if (totalEl && data.grand_total) totalEl.innerText = data.grand_total;

        if (wrap) {
            if (totalDisc > 0) wrap.classList.remove('d-none');
            else wrap.classList.add('d-none');
        }

        if (badgesWrap) {
            var html = '';
            if (data.coupon_code && parseFloat(data.coupon_discount || 0) > 0) {
                html += '<span class="badge bg-success-subtle text-success border border-success-subtle d-inline-flex align-items-center gap-1 py-1 px-2" style="font-size: 10px;">' +
                    '<i class="fas fa-tag"></i> Coupon: <strong>' + data.coupon_code + '</strong> (-৳' + parseFloat(data.coupon_discount).toFixed(2) + ')' +
                    '<i class="fas fa-times ms-1 text-danger" style="cursor: pointer;" onclick="removeDrawerCouponDirect(this)" title="Remove"></i></span>';
            }
            if (data.voucher_code && parseFloat(data.voucher_discount || 0) > 0) {
                html += '<span class="badge bg-primary-subtle text-primary border border-primary-subtle d-inline-flex align-items-center gap-1 py-1 px-2" style="font-size: 10px;">' +
                    '<i class="fas fa-gift"></i> Voucher: <strong>' + data.voucher_code + '</strong> (-৳' + parseFloat(data.voucher_discount).toFixed(2) + ')' +
                    '<i class="fas fa-times ms-1 text-danger" style="cursor: pointer;" onclick="removeDrawerVoucherDirect(this)" title="Remove"></i></span>';
            }
            badgesWrap.innerHTML = html;
            if (html.trim()) badgesWrap.classList.remove('d-none');
            else badgesWrap.classList.add('d-none');
        }

        var drawerRoot = document.querySelector('#cartDrawer, .cart-drawer, #cart-drawer, .offcanvas-end, [id*="cart"]') || document.body;
        if (drawerRoot && data.grand_total) {
            drawerRoot.querySelectorAll('*').forEach(function(node) {
                if (node.children.length === 0 && (node.innerText.trim() === 'Total' || node.innerText.trim() === 'Subtotal')) {
                    var parent = node.parentElement;
                    if (parent) {
                        var amountEl = parent.querySelector('h4, span:last-child, strong, b, h5');
                        if (amountEl && amountEl !== node) {
                            if (node.innerText.trim() === 'Total') amountEl.innerText = '৳' + data.grand_total;
                            if (node.innerText.trim() === 'Subtotal' && data.subtotal) amountEl.innerText = '৳' + data.subtotal;
                        }
                    }
                }
            });
        }

        if (typeof window.syncDeliveryCharge === 'function') {
            window.syncDeliveryCharge();
        }
    };
}
</script>