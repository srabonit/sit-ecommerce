@extends('layouts.app')

@section('title', 'Checkout - ' . config('app.name', 'Shop Now BD'))

@section('content')
@php
    $subtotal = 0;
    $hasDigitalInCart = false;
    $hasPhysicalInCart = false;

    foreach($cart as $id =>$item) {
        $rawPrice =$item['price'] ?? 0;
        if (is_string($rawPrice)) {
            $rawPrice = preg_replace('/[^0-9.]/', '',$rawPrice);
        }
        $pId = $item['id'] ?? (is_numeric($id) ? $id : ($item['product_id'] ?? null));
        $prodModel = \App\Models\Product::find($pId);$isDownloadable = !empty($item['is_downloadable']) || ($prodModel && (!empty($prodModel->is_downloadable) || str_contains(strtolower($prodModel->product_type ?? ''), 'download')));
        $qty = $isDownloadable ? 1 : (int)($item['quantity'] ?? 1);

        $subtotal += ((float)$rawPrice * $qty);

        $isDig = ($item['is_digital'] ?? false) || ($prodModel && ($prodModel->is_virtual || $prodModel->is_downloadable || in_array(strtolower($prodModel->product_type ?? ''), ['virtual', 'downloadable', 'license'])));

        if ($isDig) {$hasDigitalInCart = true;
        } else {
            $hasPhysicalInCart = true;
        }
    }

    $isMixedOrder = ($hasDigitalInCart &&$hasPhysicalInCart);
    $deliveryCharge =$initialShipping['cost'] ?? 0;
    $grandTotal = $subtotal +$deliveryCharge;
    $authUser = auth()->user();

    $defaultPayType =$hasDigitalInCart ? 'manual' : 'cod';

    // সেন্ট্রাল কিউআর ইমেজ রেজলভার
    $resolveCheckoutQr = function($path) {
        if (empty($path)) return '';
        if (class_exists(\App\Services\ImagePathService::class)) {
            return \App\Services\ImagePathService::resolveUrl($path, '');
        }
        if (function_exists('show_image')) {
            return show_image($path, '');
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) return$path;
        return asset(ltrim($path, '/'));
    };
@endphp

<style>
    .brand-pay-card { border-radius: 14px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.05); transition: all 0.3s ease; margin-top: 12px; background: #ffffff; border: 1px solid #e2e8f0; }
    .brand-card-header { padding: 12px 16px; color: #ffffff; display: flex; justify-content: space-between; align-items: center; }
    .theme-bkash .brand-card-header { background: linear-gradient(135deg, #e2136e 0%, #b80d57 100%); }
    .theme-bkash .acc-box { border-color: #f48fb1; background: #fdf2f7; color: #b80d57; }
    .theme-nagad .brand-card-header { background: linear-gradient(135deg, #f57c00 0%, #e65100 100%); }
    .theme-nagad .acc-box { border-color: #ffcc80; background: #fff8f0; color: #e65100; }
    .theme-upay .brand-card-header { background: linear-gradient(135deg, #00838f 0%, #006064 100%); }
    .theme-upay .acc-box { border-color: #80deea; background: #f0fdfa; color: #006064; }
    .theme-default .brand-card-header { background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%); }
    .theme-default .acc-box { border-color: #bfdbfe; background: #eff6ff; color: #1e40af; }
    .acc-number-digit { display: inline-flex; align-items: center; justify-content: center; min-width: 22px; height: 28px; background: #ffffff; border: 1px solid rgba(0,0,0,0.18); margin: 0 1px; font-family: 'Courier New', Courier, monospace; font-size: 15px; font-weight: 800; border-radius: 4px; color: #0f172a; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
    .qr-standee-img { max-width: 130px; max-height: 180px; object-fit: contain; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
</style>

<div class="checkout-wrapper py-3 py-md-4">
    <div class="container px-2 px-md-3">
        <div class="text-center mb-3 mb-md-4">
            <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-bold mb-2 small">Fast & Secure Checkout</span>
            <h2 class="fw-bold text-dark mb-2 fs-4 fs-md-3">Complete Your Order</h2>
        </div>

        @if(session('error'))
            <div class="alert alert-danger border-0 rounded-4 shadow-sm p-3 mb-4 d-flex align-items-center small">
                <i class="fas fa-exclamation-triangle fs-5 me-2 text-danger"></i>
                <span class="fw-bold">{{ session('error') }}</span>
            </div>
        @endif

        <div id="checkout-global-error-alert" class="alert alert-danger border-0 rounded-4 shadow-sm p-3 mb-4 d-none small">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle fs-5 me-2 text-danger"></i>
                <div>
                    <h6 class="fw-bold mb-1 small">Please fix the following:</h6>
                    <ul id="checkout-global-error-list" class="mb-0 ps-3"></ul>
                </div>
            </div>
        </div>

        <form id="checkout-main-form" novalidate enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="payment_type" id="input-payment_type" value="{{ $defaultPayType }}">

            <div class="row g-3 g-md-4">
                <div class="col-12 col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white mb-3">
                        <h5 class="fw-bold mb-3 text-dark fs-6">
                            <i class="fas fa-map-marker-alt text-primary me-1"></i> Shipping & Contact Details
                        </h5>

                        <div class="row g-2 g-md-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-dark">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="input-name" class="form-control form-control-sm rounded-3 py-2" value="{{ $authUser->name ?? '' }}" placeholder="Full name">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-dark">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" name="phone" id="input-phone" class="form-control form-control-sm rounded-3 py-2" value="{{ $authUser->phone ?? '' }}" placeholder="017XXXXXXXX">
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark">
                                    Email Address @if($hasDigitalInCart)<span class="text-danger">* (Required for digital items access)</span>@else(Optional)@endif
                                </label>
                                <input type="email" name="email" id="input-email" class="form-control form-control-sm rounded-3 py-2" value="{{ $authUser->email ?? '' }}" placeholder="example@gmail.com" {{ $hasDigitalInCart ? 'required' : '' }}>
                            </div>

                            @if($hasPhysicalInCart)
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold text-dark">District / Zone <span class="text-danger">*</span></label>
                                    <select name="district" id="district-selector" class="form-select form-select-sm rounded-3 py-2" onchange="syncDeliveryCharge()">
                                        @foreach($districts as $d)
                                            <option value="{{ $d }}" {{ (old('district') == $d || strtolower($d) == 'dhaka') ? 'selected' : '' }}>
                                                {{ $d }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold text-dark">Delivery Address <span class="text-danger">*</span></label>
                                    <textarea name="address" id="input-address" class="form-control form-control-sm rounded-3" rows="3" placeholder="Road, House, Area...">{{ $authUser->address ?? '' }}</textarea>
                                </div>
                            @else
                                <input type="hidden" name="district" value="Digital">
                                <input type="hidden" name="address" value="Digital Delivery">
                            @endif

                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark">Additional Notes (Optional)</label>
                                <textarea name="order_note" class="form-control form-control-sm rounded-3" rows="2" placeholder="Order instructions or note..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white mb-3">
                        <h5 class="fw-bold mb-2 text-dark fs-6">
                            <i class="fas fa-wallet text-primary me-2"></i> Select Payment Method
                        </h5>

                        @if($isMixedOrder)
                            <div class="alert alert-info border-0 rounded-3 py-2 px-3 small mb-3" style="font-size: 12px;">
                                <i class="fas fa-info-circle me-1"></i> <strong>সম্মিলিত অর্ডার নোটিশ:</strong> আপনার কার্টে ফিজিক্যাল পণ্যের পাশাপাশি ডিজিটাল/ভার্চুয়াল পণ্য রয়েছে। ডিজিটাল পণ্যের লাইসেন্স ও ফাইল অবিলম্বে অ্যাক্সেস পাওয়ার জন্য ফিজিক্যাল ডেলিভারি চার্জসহ সম্পূর্ণ বিলটি <strong>একসাথে অনলাইন বা ম্যানুয়াল পেমেন্টের</strong> মাধ্যমে পরিশোধ করতে হবে।
                            </div>
                        @elseif($hasDigitalInCart)
                            <div class="alert alert-info border-0 rounded-3 py-2 px-3 small mb-3" style="font-size: 12px;">
                                <i class="fas fa-info-circle me-1"></i> ডিজিটাল পণ্যের অ্যাক্সেস নিশ্চিত করতে অনুগ্রহ করে যেকোনো অনলাইন গেটওয়ে বা ম্যানুয়াল মেথডে সম্পূর্ণ পেমেন্ট পরিশোধ করুন।
                            </div>
                        @endif

                        <div class="d-flex flex-column gap-2">
                            @if(!$hasDigitalInCart)
                                <div class="payment-radio-box selected d-flex align-items-center p-3 border rounded-3" style="cursor: pointer;" onclick="setPaymentMode('cod', this, 'Cash on Delivery')">
                                    <input class="form-check-input me-3" type="radio" name="payment_method" id="pay_cod" value="Cash on Delivery" checked onchange="setPaymentMode('cod', this.closest('.payment-radio-box'), 'Cash on Delivery')">
                                    <label class="form-check-label fw-bold text-dark w-100 small" style="cursor: pointer;" for="pay_cod">
                                        <i class="fas fa-hand-holding-usd text-success fs-5 me-2 align-middle"></i> Cash on Delivery (COD)
                                    </label>
                                </div>
                            @endif

                            @if(isset($manualPayments) && count($manualPayments) > 0)
                                @foreach($manualPayments as $mIndex =>$mMethod)
                                    @php
                                        $mName = strtolower($mMethod->name);$theme = 'default';
                                        if (str_contains($mName, 'bkash'))$theme = 'bkash';
                                        elseif (str_contains($mName, 'nagad'))$theme = 'nagad';
                                        elseif (str_contains($mName, 'upay')) $theme = 'upay';$qrUrl = !empty($mMethod->qr_code_image) ?$resolveCheckoutQr($mMethod->qr_code_image) : '';$isAutoSelected = $hasDigitalInCart && ($mIndex === 0);
                                    @endphp
                                    <div class="payment-radio-box {{ $isAutoSelected ? 'selected border-primary bg-light' : '' }} d-flex align-items-center p-3 border rounded-3" style="cursor: pointer;"
                                         onclick="setPaymentMode('manual', this, '{{ addslashes($mMethod->name) }}', '{{ addslashes($mMethod->account_number ?? '') }}', `{!! addslashes($mMethod->instruction ?? '') !!}`, '{{ $qrUrl }}', '{{$theme }}')">
                                        <input class="form-check-input me-3" type="radio" name="payment_method" id="pay_m_{{ $mMethod->id }}" value="{{ $mMethod->name }}" {{ $isAutoSelected ? 'checked' : '' }}
                                               onchange="setPaymentMode('manual', this.closest('.payment-radio-box'), '{{ addslashes($mMethod->name) }}', '{{ addslashes($mMethod->account_number ?? '') }}', `{!! addslashes($mMethod->instruction ?? '') !!}`, '{{ $qrUrl }}', '{{ $theme }}')">
                                        <label class="form-check-label fw-bold text-dark w-100 d-flex justify-content-between align-items-center small" style="cursor: pointer;" for="pay_m_{{ $mMethod->id }}">
                                            <span>
                                                @if($theme === 'bkash')
                                                    <i class="fas fa-mobile-alt text-danger fs-5 me-2 align-middle"></i>
                                                @elseif($theme === 'nagad')
                                                    <i class="fas fa-mobile-alt text-warning fs-5 me-2 align-middle"></i>
                                                @elseif($theme === 'upay')
                                                    <i class="fas fa-mobile-alt text-info fs-5 me-2 align-middle"></i>
                                                @else
                                                    <i class="fas fa-qrcode text-primary fs-5 me-2 align-middle"></i>
                                                @endif
                                                {{ $mMethod->name }}
                                            </span>
                                            @if(str_contains($mName, 'qr'))
                                                <span class="badge bg-danger rounded-pill px-2 py-1" style="font-size: 9.5px;">Bangla QR</span>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            @endif

                            @if(isset($gateways) && count($gateways) > 0)
                                @foreach($gateways as $gw)
                                    <div class="payment-radio-box d-flex align-items-center p-3 border rounded-3" style="cursor: pointer;" onclick="setPaymentMode('gateway', this, '{{ addslashes($gw->name) }}')">
                                        <input class="form-check-input me-3" type="radio" name="payment_method" id="pay_gw_{{ $gw->id }}" value="{{ $gw->name }}"
                                               onchange="setPaymentMode('gateway', this.closest('.payment-radio-box'), '{{ addslashes($gw->name) }}')">
                                        <label class="form-check-label fw-bold text-dark w-100 d-flex align-items-center justify-content-between small" style="cursor: pointer;" for="pay_gw_{{ $gw->id }}">
                                            <span><i class="fas fa-credit-card text-info fs-5 me-2 align-middle"></i> {{ $gw->name }}</span>
                                            <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1" style="font-size: 9.5px;">Instant</span>
                                        </label>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <div id="manual-payment-panel" class="brand-pay-card theme-default d-none">
                            <div class="brand-card-header">
                                <div>
                                    <h6 class="fw-bold mb-0 text-white small" id="box-acc-title">Payment Instructions</h6>
                                    <small class="opacity-75" style="font-size: 11px;" id="box-acc-subtitle">Send money manually</small>
                                </div>
                                <span class="badge bg-white text-dark fw-bold px-2 py-1 rounded-pill" style="font-size: 10px;" id="box-acc-tag">Manual</span>
                            </div>

                            <div class="p-3 p-md-4">
                                <div class="row align-items-center g-3 mb-3">
                                    <div class="col-12 col-md-7">
                                        <label class="small text-muted fw-bold d-block mb-1">Account Number:</label>
                                        <div id="box-acc-number-digits" class="mb-3 d-flex flex-wrap gap-1"></div>
                                        <div class="p-2.5 rounded-3 acc-box border mb-2">
                                            <div class="small fw-semibold" style="font-size: 12px;" id="box-acc-instruction"></div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-5 text-center" id="box-qr-wrapper">
                                        <label class="small text-muted fw-bold d-block mb-2">Scan QR Code</label>
                                        <img id="box-qr-img" src="" class="qr-standee-img border p-1 bg-white" alt="QR" onerror="this.onerror=null; this.src='https://placehold.co/130x130?text=QR';">
                                    </div>
                                </div>

                                <div class="alert alert-warning border-0 rounded-3 py-2 small mb-3" style="font-size: 11.5px;">
                                    <i class="fas fa-info-circle me-1"></i> Provide <strong>any one</strong> of the following details:
                                </div>

                                <div class="row g-2">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold">Sender Number</label>
                                        <input type="text" name="sender_number" id="input-sender_number" class="form-control form-control-sm rounded-3" placeholder="01XXXXXXXXX">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold">Transaction ID (TrxID)</label>
                                        <input type="text" name="transaction_id" id="input-transaction_id" class="form-control form-control-sm rounded-3" placeholder="TrxID">
                                    </div>
                                    <div class="col-12 mt-2">
                                        <label class="form-label small fw-bold">Payment Screenshot</label>
                                        <input type="file" name="payment_screenshot_file" id="input-screenshot" class="form-control form-control-sm rounded-3" accept="image/*">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white sticky-top" style="top: 15px;">
                        <h5 class="fw-bold mb-3 text-dark fs-6">Order Summary</h5>

                        <div id="checkout-cart-items-wrapper" class="mb-3" style="max-height: 280px; overflow-y: auto;">
                            @foreach($cart as $id =>$item)
                                @php
                                    $pIdCheck = $item['id'] ?? (is_numeric($id) ? $id : ($item['product_id'] ?? null));
                                    $pObj = \App\Models\Product::find($pIdCheck);

                                    $isDownloadableItem = !empty($item['is_downloadable']) 
                                        || ($pObj && (!empty($pObj->is_downloadable) || str_contains(strtolower($pObj->product_type ?? ''), 'download')));

                                    $isVirtualItem = !empty($item['is_virtual']) 
                                        || ($pObj && (!empty($pObj->is_virtual) || str_contains(strtolower($pObj->product_type ?? ''), 'virtual') || str_contains(strtolower($pObj->product_type ?? ''), 'license')));

                                    $effectiveQty = $isDownloadableItem ? 1 : (int)($item['quantity'] ?? 1);

                                    $rawPrice =$item['price'] ?? 0;
                                    if (is_string($rawPrice)) $rawPrice = preg_replace('/[^0-9.]/', '',$rawPrice);
                                    $itemPrice = (float)$rawPrice;
                                    $itemTotal = $itemPrice * $effectiveQty;

                                    $vLine =$item['variant_name'] ?? (!empty($item['color']) || !empty($item['size']) ? trim(($item['color'] ?? '') . ' / ' . ($item['size'] ?? '')) : null);
                                @endphp
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom gap-2" id="item-row-{{ $id }}">
                                    <div class="min-w-0" style="max-width: 50%;">
                                        <h6 class="mb-0 text-dark fw-semibold small text-truncate" title="{{ $item['title'] }}">{{ $item['title'] }}</h6>
                                        @if($isDownloadableItem)
                                            <span class="badge bg-primary-subtle text-primary border" style="font-size: 8.5px; padding: 1px 5px;">Downloadable (Single File)</span>
                                        @elseif($isVirtualItem)
                                            <span class="badge bg-success-subtle text-success border" style="font-size: 8.5px; padding: 1px 5px;">License / Virtual</span>
                                        @endif
                                        @if(!empty($vLine))
                                            <span class="badge bg-light text-primary border small" style="font-size: 8.5px; padding: 1px 4px;">{{ $vLine }}</span>
                                        @endif
                                        <small class="text-muted d-block" style="font-size: 11px;">৳{{ number_format($itemPrice, 2) }}</small>
                                    </div>

                                    <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                        @if($isDownloadableItem)
                                            <span class="badge bg-light text-dark border px-2 py-1 fw-bold" style="font-size: 11px;">Qty: 1</span>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-secondary px-1.5 py-0" style="font-size: 11px;" onclick="updateQtyAjax('{{ $id }}', -1)">-</button>
                                            <span id="co-qty-text-{{ $id }}" class="small fw-bold px-1">{{ $effectiveQty }}</span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary px-1.5 py-0" style="font-size: 11px;" onclick="updateQtyAjax('{{ $id }}', 1)">+</button>
                                        @endif

                                        <span class="small fw-bold text-dark ms-1" style="font-size: 11.5px;">৳<span id="co-item-total-{{ $id }}">{{ number_format($itemTotal, 2) }}</span></span>
                                        <button type="button" class="btn btn-link text-danger p-0 ms-1" onclick="showConfirmModal('{{ $id }}')">
                                            <i class="far fa-trash-alt" style="font-size: 11px;"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-between text-secondary mb-2 small">
                            <span>Subtotal:</span>
                            <strong class="text-dark">৳<span id="summary-subtotal-val">{{ number_format($subtotal, 2) }}</span></strong>
                        </div>

                        <div class="d-flex justify-content-between text-secondary mb-2 small">
                            <span>Delivery Zone:</span>
                            <strong class="text-primary"><span id="summary-zone-name">{{ $initialShipping['zone_name'] ?? 'Standard Delivery' }}</span></strong>
                        </div>

                        <div class="d-flex justify-content-between text-secondary mb-3 small">
                            <span>Delivery Charge:</span>
                            <strong class="text-dark">৳<span id="summary-delivery-val">{{ number_format($deliveryCharge, 2) }}</span></strong>
                        </div>

                        <hr class="my-2 opacity-25">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold fs-6 text-dark">Grand Total (Payable):</span>
                            <h4 class="fw-bold text-primary mb-0 fs-5">৳<span id="summary-grandtotal-val">{{ number_format($grandTotal, 2) }}</span></h4>
                        </div>

                        <button type="button" id="confirm-order-submit-btn" onclick="dispatchCheckoutOrder()" class="btn btn-primary btn-lg w-100 py-2.5 rounded-4 fw-bold shadow-sm small">
                            <i class="fas fa-lock me-2"></i> Complete Order
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="customAppAlertModal" tabindex="-1" aria-hidden="true" style="z-index: 107000;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-body text-center p-3">
                <div id="modal-alert-icon-wrap" class="mb-2">
                    <i id="modal-alert-icon" class="fas fa-question-circle text-warning fs-2"></i>
                </div>
                <h5 id="modal-alert-title" class="fw-bold text-dark mb-1 fs-6">Are you sure?</h5>
                <p id="modal-alert-message" class="text-secondary small mb-3">Do you want to proceed?</p>
                <div class="d-flex gap-2 justify-content-center" id="modal-alert-btn-container">
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3 fw-semibold border" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="modal-alert-confirm-btn" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let isUpdating = false;

function showAppModal(title, message, type = 'confirm', onConfirm = null) {
    const modalEl = document.getElementById('customAppAlertModal');
    if (!modalEl) return;

    document.getElementById('modal-alert-title').innerText = title;
    document.getElementById('modal-alert-message').innerText = message;

    const iconEl = document.getElementById('modal-alert-icon');
    const btnContainer = document.getElementById('modal-alert-btn-container');

    if (type === 'confirm') {
        iconEl.className = 'fas fa-exclamation-triangle text-warning fs-2';
        btnContainer.innerHTML = `
            <button type="button" class="btn btn-sm btn-light rounded-pill px-3 fw-semibold border" data-bs-dismiss="modal">Cancel</button>
            <button type="button" id="modal-alert-confirm-btn" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold">Remove</button>
        `;
        document.getElementById('modal-alert-confirm-btn').onclick = function() {
            bootstrap.Modal.getInstance(modalEl).hide();
            if (onConfirm) onConfirm();
        };
    } else {
        iconEl.className = 'fas fa-info-circle text-primary fs-2';
        btnContainer.innerHTML = `
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">OK</button>
        `;
    }

    bootstrap.Modal.getOrCreateInstance(modalEl).show();
}

function showConfirmModal(productId) {
    showAppModal('Remove Product', 'Are you sure you want to remove this product?', 'confirm', () => {
        executeRemoveCheckoutItem(productId);
    });
}

function syncDeliveryCharge() {
    const selector = document.getElementById('district-selector');
    const district = selector ? selector.value : 'Dhaka';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch("{{ route('checkout.deliveryCharge') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ district: district })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('summary-subtotal-val').innerText = data.subtotal;
            document.getElementById('summary-delivery-val').innerText = data.delivery_charge;
            document.getElementById('summary-grandtotal-val').innerText = data.grand_total;
            if (document.getElementById('summary-zone-name')) {
                document.getElementById('summary-zone-name').innerText = data.zone;
            }
        }
    })
    .finally(() => {
        isUpdating = false;
    });
}

function setPaymentMode(mode, element, name = '', number = '', instruction = '', qrUrl = '', theme = 'default') {
    document.querySelectorAll('.payment-radio-box').forEach(el => {
        el.classList.remove('selected', 'border-primary', 'bg-light');
    });
    
    if (element) {
        element.classList.add('selected', 'border-primary', 'bg-light');
        const radio = element.querySelector('input[type="radio"]');
        if (radio && !radio.checked) {
            radio.checked = true;
        }
    }

    document.getElementById('input-payment_type').value = mode;

    const manualPanel = document.getElementById('manual-payment-panel');
    if (mode === 'manual') {
        manualPanel.className = 'brand-pay-card theme-' + theme;
        manualPanel.classList.remove('d-none');

        document.getElementById('box-acc-title').innerText = name + ' Payment';
        document.getElementById('box-acc-tag').innerText = name;
        document.getElementById('box-acc-instruction').innerHTML = instruction || 'Scan QR or send money manually.';

        const digitsBox = document.getElementById('box-acc-number-digits');
        digitsBox.innerHTML = '';
        if (number) {
            number.split('').forEach(d => {
                const span = document.createElement('span');
                span.className = 'acc-number-digit';
                span.innerText = d;
                digitsBox.appendChild(span);
            });
        }

        const qrWrapper = document.getElementById('box-qr-wrapper');
        const qrImg = document.getElementById('box-qr-img');
        if (qrUrl) {
            qrImg.src = qrUrl;
            qrWrapper.style.display = 'block';
        } else {
            qrWrapper.style.display = 'none';
        }
    } else {
        if (manualPanel) {
            manualPanel.classList.add('d-none');
        }
    }
}

function updateQtyAjax(productId, delta) {
    if (isUpdating) return;
    const qtyEl = document.getElementById(`co-qty-text-${productId}`);
    if (!qtyEl) return;

    let currentQty = parseInt(qtyEl.innerText) || 1;
    let newQty = currentQty + delta;
    if (newQty <= 0) return;

    isUpdating = true;
    qtyEl.innerText = newQty;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const district = document.getElementById('district-selector')?.value || 'Dhaka';

    fetch("{{ route('cart.updateQty') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ id: productId, quantity: newQty, district: district })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById(`co-item-total-${productId}`).innerText = data.item_total;
            document.getElementById('summary-subtotal-val').innerText = data.subtotal;
            syncDeliveryCharge();
        } else {
            isUpdating = false;
        }
    })
    .catch(() => {
        isUpdating = false;
    });
}

function executeRemoveCheckoutItem(productId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch("{{ route('cart.remove') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ id: productId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById(`item-row-${productId}`)?.remove();
            document.getElementById('summary-subtotal-val').innerText = data.subtotal;
            if (data.is_empty) {
                window.location.href = "{{ route('shop') }}";
            } else {
                syncDeliveryCharge();
            }
        }
    });
}

function dispatchCheckoutOrder() {
    const form = document.getElementById('checkout-main-form');
    const btn = document.getElementById('confirm-order-submit-btn');

    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    const alertBox = document.getElementById('checkout-global-error-alert');
    alertBox.classList.add('d-none');

    const payType = document.getElementById('input-payment_type').value;
    if (payType === 'manual') {
        const sender = document.getElementById('input-sender_number').value.trim();
        const trx = document.getElementById('input-transaction_id').value.trim();
        const ss = document.getElementById('input-screenshot').files.length;

        if (!sender && !trx && ss === 0) {
            showAppModal('Required Field Missing', 'দয়া করে Sender Number, Transaction ID অথবা Screenshot-এর যেকোনো একটি দিন।', 'info');
            return;
        }
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Placing Order...';

    const formData = new FormData(form);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch("{{ route('checkout.process') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok) throw data;
        return data;
    })
    .then(data => {
        if (data.status === 'success') {
            window.location.href = data.redirect;
        } else {
            throw data;
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock me-2"></i> Complete Order';
        
        let errorMsg = '';
        if (err.errors) {
            errorMsg = Object.values(err.errors)[0][0];
        } else {
            errorMsg = err.message || 'Something went wrong. Please check your details.';
        }
        showAppModal('Validation Error', errorMsg, 'info');
    });
}

document.addEventListener('DOMContentLoaded', () => {
    syncDeliveryCharge();

    @if($hasDigitalInCart && isset($manualPayments) && count($manualPayments) > 0)
        const firstManualEl = document.querySelector('.payment-radio-box.selected');
        if (firstManualEl) {
            firstManualEl.click();
        }
    @endif
});

/* =========================================================================
   REAL-TIME ABANDONED CART RECOVERY TELEMETRY (Resilient Standalone Hook)
   ========================================================================= */
(function () {
    let syncTimer = null;

    function sendAbandonedCartTelemetry() {
        const emailEl = document.getElementById('input-email');
        const phoneEl = document.getElementById('input-phone');
        const nameEl  = document.getElementById('input-name');

        const email = emailEl ? emailEl.value.trim() : '';
        const phone = phoneEl ? phoneEl.value.trim() : '';
        const name  = nameEl  ? nameEl.value.trim()  : '';

        const isValidEmail = email.includes('@') && email.includes('.');
        const isValidPhone = phone.replace(/[^0-9]/g, '').length >= 10;

        if (isValidEmail || isValidPhone) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                || document.querySelector('input[name="_token"]')?.value 
                || '{{ csrf_token() }}';

            const syncUrl = "{{ route('cart.abandoned.sync') }}";

            fetch(syncUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    email: email,
                    phone: phone,
                    name: name,
                    _token: csrfToken
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && window.sessionStorage) {
                    sessionStorage.setItem('active_abandoned_cart_token', data.recovery_token);
                }
            })
            .catch(() => {
                // Background silent heartbeat
            });
        }
    }

    function triggerDebouncedSync() {
        clearTimeout(syncTimer);
        syncTimer = setTimeout(sendAbandonedCartTelemetry, 1000);
    }

    function initCartTracker() {
        const inputs = [
            document.getElementById('input-email'),
            document.getElementById('input-phone'),
            document.getElementById('input-name')
        ];

        inputs.forEach(input => {
            if (input) {
                input.addEventListener('input', triggerDebouncedSync);
                input.addEventListener('change', sendAbandonedCartTelemetry);
                input.addEventListener('blur', sendAbandonedCartTelemetry);
            }
        });

        // যদি পেজ লোডের সময় ফিল্ডে পূর্বেই মান থাকে (যেমন লগইন করা ব্যবহারকারী)
        const hasExistingContact = (inputs[0] && inputs[0].value.trim()) || (inputs[1] && inputs[1].value.trim());
        if (hasExistingContact) {
            triggerDebouncedSync();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCartTracker);
    } else {
        initCartTracker();
    }
})();
</script>
@endsection