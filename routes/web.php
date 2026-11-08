<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Controllers Imports
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\Web\UserDashboardController;
use App\Http\Controllers\Web\DigitalProductController;
use App\Http\Controllers\Web\Payment\PaymentGatewayController;
use App\Http\Controllers\Web\FormSubmissionController;
use App\Http\Middleware\FormSubmissionRateLimit;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\EnsureBranchAccess;

/*
|--------------------------------------------------------------------------
| Auth Controllers Imports
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\SocialAuthController;

/*
|--------------------------------------------------------------------------
| Admin Controllers Imports
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\BarcodeController as AdminBarcodeController;
use App\Http\Controllers\Admin\SupportController as AdminSupportController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\CourierSettingController as AdminCourierSettingController;
use App\Http\Controllers\Admin\CourierManagementController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\StaffUserController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\SessionMonitorController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\BranchProductController;
use App\Http\Controllers\Admin\BranchPOSController;
use App\Http\Controllers\Admin\BranchDailyClosingController;
use App\Http\Controllers\Admin\BranchReportController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\AuthSettingController;
use App\Http\Controllers\Admin\SiteContentController;
use App\Http\Controllers\Admin\PaymentSettingController;
use App\Http\Controllers\Admin\PaymentTransactionController;
use App\Http\Controllers\Admin\ThemeSettingController;
use App\Http\Controllers\Admin\ThemeExtendedColorController;
use App\Http\Controllers\Admin\DynamicDisplayController;
use App\Http\Controllers\Admin\ShippingController as AdminShippingController;
use App\Http\Controllers\Admin\FormController;
use App\Http\Controllers\Admin\FormFieldController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\FinancialReportController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\BankAccountController;
use App\Http\Controllers\Admin\IncomeController;
use App\Http\Controllers\Admin\InvestmentController;
use App\Http\Controllers\Admin\AssetController;
use App\Http\Controllers\Admin\AccountLedgerController;
use App\Http\Controllers\Admin\EmailMarketingController;
use App\Http\Controllers\Admin\AdminProductReviewController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\ImageThumbnailController;

/*
|--------------------------------------------------------------------------
| Procurement & Inventory Management Controllers Imports
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\PurchaseReceiptController;
use App\Http\Controllers\Admin\SupplierPaymentController;
use App\Http\Controllers\Admin\PurchaseReturnController;
use App\Http\Controllers\Admin\SupplierExchangeController;
use App\Http\Controllers\Admin\SupplierDebitCreditNoteController;
use App\Http\Controllers\Admin\AssetProcurementController;

/*
|--------------------------------------------------------------------------
| Enterprise Tax & VAT Controllers Imports
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Admin\Tax\TaxDashboardController;
use App\Http\Controllers\Admin\Tax\TaxClassController;
use App\Http\Controllers\Admin\Tax\TaxRuleController;
use App\Http\Controllers\Admin\Tax\TaxPaymentController;
use App\Http\Controllers\Admin\Tax\TaxDocumentController;
use App\Http\Controllers\Admin\Tax\TaxReportController;
use App\Http\Controllers\Admin\Tax\TaxPeriodController;
use App\Http\Controllers\Admin\Tax\TaxSettingController;

/*
|--------------------------------------------------------------------------
| Advanced Security & Threat Prevention Controllers Imports
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Admin\Security\SecurityDashboardController;
use App\Http\Controllers\Admin\Security\SecurityIpManagementController;
use App\Http\Controllers\Admin\Security\SecuritySuspiciousOrderController;
use App\Http\Controllers\Admin\Security\SecurityUrlMonitoringController;
use App\Http\Controllers\Admin\Security\SecurityBlockingManagementController;
use App\Http\Controllers\Admin\Security\SecurityRulesAndSettingsController;
use App\Http\Controllers\Admin\Security\SecurityLogsAndAlertsController;

/*
|--------------------------------------------------------------------------
| SMS Marketing & Gateway Management Controllers Imports
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Admin\SmsSettingController;
use App\Http\Controllers\Admin\SmsTemplateController;
use App\Http\Controllers\Admin\SmsCampaignController;
use App\Http\Controllers\Admin\SmsLogController;
use App\Http\Controllers\Admin\SmsBlacklistController;

/*
|--------------------------------------------------------------------------
| Frontend Controllers Imports
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\ProductReviewController;

/*
|==========================================================================
| SECTION 1: PUBLIC STORE & OPERATIONS
|==========================================================================
*/
Route::middleware(['throttle:custom-rate-limit'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/shop', [ProductController::class, 'index'])->name('shop');
    Route::get('/category/{slug}', [HomeController::class, 'categoryProducts'])->name('category.products');
    Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.details');
    Route::get('/ajax/quick-view/{id}', [ProductController::class, 'quickViewModal'])->name('product.quickview');

    // Live Product Reviews AJAX Fetch for PDP Tabs
    Route::get('/ajax/product/{id}/reviews', [ProductReviewController::class, 'getReviewsAjax'])->name('product.reviews.ajax');

    // Secure Digital Product Download Route
    Route::get('/digital-download/{token}', [DigitalProductController::class, 'downloadFile'])->name('digital.download');

    // Blog Front
    Route::get('/blogs', [BlogController::class, 'index'])->name('blogs.index');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blogs.show');
    Route::post('/blog/{slug}/comment', [BlogController::class, 'postComment'])->name('blogs.comment');
    Route::post('/blog/comment/update/{id}', [BlogController::class, 'updateComment'])->name('blogs.comment.update');
    Route::post('/blog/comment/delete/{id}', [BlogController::class, 'deleteComment'])->name('blogs.comment.delete');

    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{id}', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/update-qty', [CartController::class, 'updateQty'])->name('cart.updateQty');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::post('/cart/abandoned/sync', [\App\Http\Controllers\Web\AbandonedCartController::class, 'sync'])->name('cart.abandoned.sync');

    // Cart Coupon & Voucher Operations
    Route::post('/cart/coupon/apply', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
    Route::post('/cart/coupon/remove', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');
    Route::post('/cart/voucher/apply', [CartController::class, 'applyVoucher'])->name('cart.voucher.apply');
    Route::post('/cart/voucher/remove', [CartController::class, 'removeVoucher'])->name('cart.voucher.remove');

    // Wishlist
    Route::post('/wishlist/toggle/{id}', [UserDashboardController::class, 'toggleWishlist'])->name('wishlist.toggle');
    Route::get('/wishlist/count', [UserDashboardController::class, 'getWishlistCount'])->name('wishlist.count');
    Route::post('/wishlist/clear', [UserDashboardController::class, 'clearWishlist'])->name('wishlist.clear');

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/delivery-charge', [CheckoutController::class, 'getDeliveryCharge'])->name('checkout.deliveryCharge');
    Route::post('/checkout/process', [CheckoutController::class, 'processOrder'])->name('checkout.process');
    Route::get('/order/confirmation/{invoiceNo}', [CheckoutController::class, 'confirmation'])->name('order.confirmation');

    // Checkout Coupon & Voucher Operations
    Route::post('/checkout/coupon/apply', [CheckoutController::class, 'applyCoupon'])->name('checkout.coupon.apply');
    Route::post('/checkout/coupon/remove', [CheckoutController::class, 'removeCoupon'])->name('checkout.coupon.remove');
    Route::post('/checkout/voucher/apply', [CheckoutController::class, 'applyVoucher'])->name('checkout.voucher.apply');
    Route::post('/checkout/voucher/remove', [CheckoutController::class, 'removeVoucher'])->name('checkout.voucher.remove');

    // Live APIs
    Route::get('/api/filter-products', [HomeController::class, 'filterProducts'])->name('home.filterProducts');
    Route::get('/api/live-search', [ProductController::class, 'ajaxLiveSearch'])->name('api.liveSearch');

    // Public Accessible Delete Account
    Route::get('/user/delete-account', [UserDashboardController::class, 'showDeleteAccountPage'])->name('user.account.delete.page');
    Route::post('/user/delete-account', [UserDashboardController::class, 'deleteAccount'])->name('user.account.delete');

    // User Panel (Only Protected Routes)
    Route::middleware(['auth'])->prefix('user')->name('user.')->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
        Route::get('/orders', [UserDashboardController::class, 'orders'])->name('orders');
        Route::get('/orders/{invoice_no}', [UserDashboardController::class, 'orderDetails'])->name('orders.details');
        Route::get('/downloads', [DigitalProductController::class, 'userDownloads'])->name('downloads');
        Route::get('/track-order', [UserDashboardController::class, 'trackOrder'])->name('track.order');
        Route::get('/profile', [UserDashboardController::class, 'profile'])->name('profile');
        Route::post('/profile/update', [UserDashboardController::class, 'updateProfile'])->name('profile.update');
        Route::post('/password/update', [UserDashboardController::class, 'updatePassword'])->name('password.update');

        // Customer Identity Updates via OTP
        Route::post('/phone/change-otp', [UserDashboardController::class, 'requestPhoneChangeOtp'])->name('phone.change.otp');
        Route::post('/phone/change-confirm', [UserDashboardController::class, 'confirmPhoneChange'])->name('phone.change.confirm');
        Route::post('/email/change-otp', [UserDashboardController::class, 'requestEmailChangeOtp'])->name('email.change.otp');
        Route::post('/email/change-confirm', [UserDashboardController::class, 'confirmEmailChange'])->name('email.change.confirm');

        // Two-Factor Authentication & Session Termination
        Route::post('/two-factor/toggle', [UserDashboardController::class, 'toggleTwoFactor'])->name('two_factor.toggle');
        Route::post('/sessions/logout-others', [UserDashboardController::class, 'logoutOtherDevices'])->name('sessions.logout_others');

        Route::get('/addresses', [UserDashboardController::class, 'addresses'])->name('addresses');
        Route::post('/addresses/update', [UserDashboardController::class, 'updateAddress'])->name('addresses.update');
        Route::get('/wishlist', [UserDashboardController::class, 'wishlist'])->name('wishlist');
        Route::get('/coupons', [UserDashboardController::class, 'coupons'])->name('coupons');
        Route::post('/vouchers/claim', [UserDashboardController::class, 'claimVoucher'])->name('vouchers.claim');
        Route::get('/support', [UserDashboardController::class, 'support'])->name('support');
        Route::post('/support/submit', [UserDashboardController::class, 'submitSupportTicket'])->name('support.submit');

        // Customer Product Reviews Actions
        Route::post('/product/{id}/review', [ProductReviewController::class, 'store'])->name('product.review.store');
        Route::put('/product/review/{id}', [ProductReviewController::class, 'update'])->name('product.review.update');
        Route::delete('/product/review/{id}', [ProductReviewController::class, 'destroy'])->name('product.review.destroy');
        Route::post('/product/review/{id}/helpful', [ProductReviewController::class, 'toggleHelpful'])->name('product.review.helpful');
        Route::post('/product/review/{id}/report', [ProductReviewController::class, 'reportReview'])->name('product.review.report');
    });

    Route::get('/track-order', [UserDashboardController::class, 'trackOrder'])->name('guest.track.order');

    // Public Email Marketing Recovery & Tracking
    Route::prefix('email')->name('email.')->group(function () {
        Route::get('/track/open/{token}', [EmailMarketingController::class, 'trackOpen'])->name('track.open');
        Route::get('/track/click/{token}', [EmailMarketingController::class, 'trackClick'])->name('track.click');
        Route::get('/unsubscribe/{token}', [EmailMarketingController::class, 'unsubscribePage'])->name('unsubscribe');
        Route::post('/unsubscribe/{token}', [EmailMarketingController::class, 'processUnsubscribe'])->name('unsubscribe.process');
    });
    Route::get('/cart/recover/{token}', [EmailMarketingController::class, 'recoverCart'])->name('cart.recover');
});

/*
|==========================================================================
| SECTION 2: DYNAMIC FORM SUBMISSIONS
|==========================================================================
*/
Route::get('/forms/{slug}', [FormSubmissionController::class, 'show'])->name('forms.show');
Route::post('/forms/{form}/submit', [FormSubmissionController::class, 'submit'])
    ->name('forms.submit')
    ->middleware(['throttle:10,1', FormSubmissionRateLimit::class]);

/*
|==========================================================================
| SECTION 3: PAYMENT GATEWAYS & CALLBACKS
|==========================================================================
*/
Route::prefix('payment')->name('payment.')->group(function () {
    Route::prefix('sslcommerz')->name('sslcommerz.')->group(function () {
        Route::get('/pay/{invoiceNo}', [PaymentGatewayController::class, 'sslcommerzPay'])->name('pay');
        Route::post('/success', [PaymentGatewayController::class, 'sslcommerzSuccess'])->name('success');
        Route::post('/fail', [PaymentGatewayController::class, 'sslcommerzFail'])->name('fail');
        Route::post('/cancel', [PaymentGatewayController::class, 'sslcommerzCancel'])->name('cancel');
        Route::post('/ipn', [PaymentGatewayController::class, 'sslcommerzIpn'])->name('ipn');
    });

    Route::prefix('bkash')->name('bkash.')->group(function () {
        Route::get('/pay/{invoiceNo}', [PaymentGatewayController::class, 'bkashPay'])->name('pay');
        Route::get('/callback', [PaymentGatewayController::class, 'bkashCallback'])->name('callback');
        Route::get('/refund/{invoiceNo}', [PaymentGatewayController::class, 'bkashRefund'])->name('refund');
    });

    Route::prefix('nagad')->name('nagad.')->group(function () {
        Route::get('/pay/{invoiceNo}', [PaymentGatewayController::class, 'nagadPay'])->name('pay');
        Route::get('/callback', [PaymentGatewayController::class, 'nagadCallback'])->name('callback');
    });

    Route::prefix('upay')->name('upay.')->group(function () {
        Route::get('/pay/{invoiceNo}', [PaymentGatewayController::class, 'upayPay'])->name('pay');
        Route::get('/callback', [PaymentGatewayController::class, 'upayCallback'])->name('callback');
    });

    Route::prefix('eps')->name('eps.')->group(function () {
        Route::get('/pay/{invoiceNo}', [PaymentGatewayController::class, 'epsPay'])->name('pay');
        Route::match(['get', 'post'], '/callback', [PaymentGatewayController::class, 'epsCallback'])->name('callback');
    });

    Route::prefix('shurjopay')->name('shurjopay.')->group(function () {
        Route::get('/pay/{invoiceNo}', [PaymentGatewayController::class, 'shurjopayPay'])->name('pay');
        Route::match(['get', 'post'], '/response', [PaymentGatewayController::class, 'shurjopayResponse'])->name('response');
        Route::post('/ipn', [PaymentGatewayController::class, 'shurjopayIpn'])->name('ipn');
    });

    Route::prefix('stripe')->name('stripe.')->group(function () {
        Route::get('/pay/{invoiceNo}', [PaymentGatewayController::class, 'stripePay'])->name('pay');
        Route::get('/success/{invoiceNo}', [PaymentGatewayController::class, 'stripeSuccess'])->name('success');
        Route::get('/cancel/{invoiceNo}', [PaymentGatewayController::class, 'stripeCancel'])->name('cancel');
        Route::post('/webhook', [PaymentGatewayController::class, 'stripeWebhook'])->name('webhook');
    });

    Route::prefix('paypal')->name('paypal.')->group(function () {
        Route::get('/pay/{invoiceNo}', [PaymentGatewayController::class, 'paypalPay'])->name('pay');
        Route::get('/success/{invoiceNo}', [PaymentGatewayController::class, 'paypalSuccess'])->name('success');
        Route::get('/cancel/{invoiceNo}', [PaymentGatewayController::class, 'paypalCancel'])->name('cancel');
    });
});

/*
|==========================================================================
| SECTION 4: AUTHENTICATION (CUSTOMER, OTP, OAUTH, 2FA & ADMIN)
|==========================================================================
*/
Route::middleware('guest')->group(function () {
    // 4.1 Standard & Dynamic Credentials Login
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth-limiter');

    // 4.2 Registration with Live OTP Verification Support (Phone & Email)
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth-limiter');
    
    // Registration Phone OTP
    Route::post('/auth/otp/register/send', [AuthController::class, 'sendRegistrationOtp'])
        ->name('auth.otp.register.send')
        ->middleware('throttle:auth-limiter');
    Route::post('/auth/otp/register/verify', [AuthController::class, 'verifyRegistrationOtp'])
        ->name('auth.otp.register.verify')
        ->middleware('throttle:auth-limiter');

    // Registration Email OTP
    Route::post('/auth/email-otp/register/send', [AuthController::class, 'sendRegistrationEmailOtp'])
        ->name('auth.email_otp.register.send')
        ->middleware('throttle:auth-limiter');
    Route::post('/auth/email-otp/register/verify', [AuthController::class, 'verifyRegistrationEmailOtp'])
        ->name('auth.email_otp.register.verify')
        ->middleware('throttle:auth-limiter');

    // 4.3 Passwordless Phone Number + OTP Login
    Route::post('/auth/otp/login/send', [AuthController::class, 'sendLoginOtp'])
        ->name('auth.otp.login.send')
        ->middleware('throttle:auth-limiter');
    Route::post('/auth/otp/login/verify', [AuthController::class, 'verifyLoginOtp'])
        ->name('auth.otp.login.verify')
        ->middleware('throttle:auth-limiter');

    // 4.4 Passwordless Email + OTP Login
    Route::post('/auth/email-otp/login/send', [AuthController::class, 'sendEmailLoginOtp'])
        ->name('auth.email_otp.login.send')
        ->middleware('throttle:auth-limiter');
    Route::post('/auth/email-otp/login/verify', [AuthController::class, 'verifyEmailLoginOtp'])
        ->name('auth.email_otp.login.verify')
        ->middleware('throttle:auth-limiter');

    // 4.5 Google OAuth 2.0 Redirection & Callback
    Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

    // 4.6 Dual Password Reset Pipeline (Email Link & Phone SMS Link)
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
        ->name('password.email')
        ->middleware('throttle:auth-limiter');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])
        ->name('password.update')
        ->middleware('throttle:auth-limiter');

    Route::post('/forgot-password/phone/send-otp', [ForgotPasswordController::class, 'sendPhoneResetOtp'])
        ->name('password.phone.send_otp')
        ->middleware('throttle:auth-limiter');
    Route::post('/forgot-password/phone/verify-otp', [ForgotPasswordController::class, 'verifyPhoneResetOtp'])
        ->name('password.phone.verify_otp')
        ->middleware('throttle:auth-limiter');
    Route::get('/reset-password/phone/{token}', [ForgotPasswordController::class, 'showPhoneResetForm'])->name('password.reset.phone');
    Route::post('/reset-password/phone', [ForgotPasswordController::class, 'resetWithPhone'])
        ->name('password.phone.update')
        ->middleware('throttle:auth-limiter');
});

// 4.7 Two-Factor Authentication (2FA) Challenge Pipeline
Route::get('/auth/2fa/challenge', [AuthController::class, 'showTwoFactorChallenge'])->name('auth.2fa.challenge');
Route::post('/auth/2fa/verify', [AuthController::class, 'verifyTwoFactor'])
    ->name('auth.2fa.verify')
    ->middleware('throttle:auth-limiter');
Route::post('/auth/2fa/resend', [AuthController::class, 'resendTwoFactorOtp'])
    ->name('auth.2fa.resend')
    ->middleware('throttle:auth-limiter');

// 4.8 Universal Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// 4.9 Dedicated Admin Authentication
Route::prefix('dashboard/auth')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'showLogin'])->name('login');
    Route::post('/', [AdminDashboardController::class, 'login'])->name('login.submit')->middleware('throttle:auth-limiter');
    Route::post('/logout', [AdminDashboardController::class, 'logout'])->name('logout');
});

/*
|==========================================================================
| SECTION 5: AUTOMATED COURIER WEBHOOK
|==========================================================================
*/
Route::post('/webhook/courier/{provider}', [AdminCourierSettingController::class, 'handleWebhook'])->name('courier.webhook');

/*
|==========================================================================
| SECTION 6: ADMIN PANEL CORE & MODULES
|==========================================================================
*/
Route::middleware(['auth', 'admin', CheckPermission::class])->prefix('admin')->name('admin.')->group(function () {

    /* --- 6.1 Admin Dashboard & Global Search --- */
    Route::get('/', fn() => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/search-ajax', [AdminDashboardController::class, 'ajaxGlobalSearch'])->name('search.ajax');

    /* --- 6.2 Central Global Media Library --- */
    Route::prefix('media')->name('media.')->group(function () {
        Route::get('/', [AdminMediaController::class, 'index'])->name('index');
        Route::post('/upload', [AdminMediaController::class, 'upload'])->name('upload');
        Route::get('/{id}/download', [AdminMediaController::class, 'download'])->name('download');
        Route::post('/{id}/update', [AdminMediaController::class, 'updateMeta'])->name('update');
        Route::post('/{id}/replace', [AdminMediaController::class, 'replace'])->name('replace');
        Route::get('/{id}/usage', [AdminMediaController::class, 'usage'])->name('usage');
        Route::delete('/{id}', [AdminMediaController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-delete', [AdminMediaController::class, 'bulkDestroy'])->name('bulk.destroy');
    });

    /* --- 6.3 Branch Management Hubs --- */
    Route::prefix('branch')->name('branch.')->group(function () {
        Route::get('/all', [BranchController::class, 'index'])->name('index');
        Route::get('/create', [BranchController::class, 'create'])->name('create');
        Route::post('/store', [BranchController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [BranchController::class, 'edit'])->name('edit')->middleware(EnsureBranchAccess::class);
        Route::post('/update/{id}', [BranchController::class, 'update'])->name('update')->middleware(EnsureBranchAccess::class);
        Route::match(['delete', 'post'], '/delete/{id}', [BranchController::class, 'destroy'])->name('destroy');
        Route::post('/toggle-status/{id}', [BranchController::class, 'toggleStatus'])->name('toggle.status');
        Route::get('/new-assign', [BranchController::class, 'newAssign'])->name('new.assign');
        Route::post('/assign-product-store', [BranchController::class, 'storeAssign'])->name('assign.store');
        Route::get('/assign-history', [BranchController::class, 'assignHistory'])->name('assign.history');
        Route::get('/assign-history/{id}', [BranchController::class, 'showAssignDetails'])->name('assign.details');
        Route::get('/products', [BranchController::class, 'branchProducts'])->name('products');
        Route::post('/update-stock/{id}', [BranchController::class, 'updateProductStock'])->name('stock.update');
        Route::get('/orders', [BranchController::class, 'branchOrders'])->name('orders');
        Route::get('/search-products-ajax', [BranchController::class, 'searchProductsAjax'])->name('search.products');

        // Multi-Branch Staff & Permissions Management
        Route::get('/manage-staff/{id}', [BranchController::class, 'manageStaff'])->name('staff.manage');
        Route::post('/assign-staff/{id}', [BranchController::class, 'assignStaff'])->name('staff.assign');
        Route::delete('/remove-staff/{branchId}/{userId}', [BranchController::class, 'removeStaff'])->name('staff.remove');

        // Branch Operational Settings & Context Switching
        Route::post('/update-settings/{id}', [BranchController::class, 'updateSettings'])->name('settings.update');
        Route::post('/switch-context/{id}', [BranchController::class, 'switchContext'])->name('switch');
    });


    

    /* --- 6.3.1 Branch Product Availability & Pricing --- */
    Route::prefix('branch-products')->name('branch_product.')->group(function () {
        Route::get('/', [BranchProductController::class, 'index'])->name('index');
        Route::get('/availability/{productId}', [BranchProductController::class, 'editAvailability'])->name('availability');
        Route::post('/availability/{productId}', [BranchProductController::class, 'updateAvailability'])->name('availability.update');
        Route::get('/pricing/{branchId}/{productId}', [BranchProductController::class, 'editPricing'])->name('pricing');
        Route::post('/pricing/{branchId}/{productId}', [BranchProductController::class, 'updatePricing'])->name('pricing.update');
        Route::post('/toggle-pos/{branchId}/{productId}', [BranchProductController::class, 'togglePosStatus'])->name('toggle_pos');
        Route::post('/toggle-can-sell/{branchId}/{productId}', [BranchProductController::class, 'toggleCanSell'])->name('toggle_can_sell');
        Route::post('/bulk-assign', [BranchProductController::class, 'bulkAssign'])->name('bulk_assign');
    });

    /* --- 6.4 Contacts (Customer & Supplier) --- */
    Route::prefix('contact')->name('contact.')->group(function () {
        Route::get('/customer', fn() => view('admin.modules.placeholder', ['title' => 'Customer List']))->name('customer');
        Route::get('/supplier', fn() => redirect()->route('admin.suppliers.index'))->name('supplier');
    });

    /* --- 6.5 Product Management & Product Reviews --- */
    Route::prefix('product')->name('product.')->group(function () {
        Route::post('/category/quick-store-ajax', [AdminCategoryController::class, 'quickStoreAjax'])->name('category.quick_store');
        Route::get('/category', [AdminCategoryController::class, 'index'])->name('category');
        Route::post('/category/store', [AdminCategoryController::class, 'store'])->name('category.store');
        Route::get('/category/edit/{id}', [AdminCategoryController::class, 'edit'])->name('category.edit');
        Route::post('/category/update/{id}', [AdminCategoryController::class, 'update'])->name('category.update');
        Route::delete('/category/delete/{id}', [AdminCategoryController::class, 'destroy'])->name('category.destroy');
        Route::post('/category/toggle-status/{id}', [AdminCategoryController::class, 'toggleStatus'])->name('toggle.status');

        Route::post('/brand/quick-store-ajax', [AdminBrandController::class, 'quickStoreAjax'])->name('brand.quick_store');
        Route::get('/brand', [AdminBrandController::class, 'index'])->name('brand');
        Route::post('/brand/store', [AdminBrandController::class, 'store'])->name('brand.store');
        Route::get('/brand/edit/{id}', [AdminBrandController::class, 'edit'])->name('brand.edit');
        Route::post('/brand/update/{id}', [AdminBrandController::class, 'update'])->name('brand.update');
        Route::delete('/brand/delete/{id}', [AdminBrandController::class, 'destroy'])->name('brand.destroy');
        Route::post('/brand/toggle-status/{id}', [AdminBrandController::class, 'toggleStatus'])->name('brand.toggle.status');

        Route::get('/tags/most-used', [AdminProductController::class, 'getMostUsedTagsTagsAjax'])->name('tags.most_used');
        Route::get('/tags/search-ajax', [AdminProductController::class, 'searchTagsAjax'])->name('tags.search');

        Route::get('/barcode-print', [AdminBarcodeController::class, 'index'])->name('barcode');
        Route::get('/barcode/search-product', [AdminBarcodeController::class, 'searchProduct'])->name('barcode.search');
        Route::get('/barcode/search-order', [AdminBarcodeController::class, 'searchOrder'])->name('barcode.search_order');
        Route::get('/barcode/search-ticket', [AdminBarcodeController::class, 'searchTicket'])->name('barcode.search_ticket');
        Route::get('/barcode/get-details/{id}', [AdminBarcodeController::class, 'getDetails'])->name('barcode.details');

        Route::get('/color', fn() => view('admin.modules.placeholder', ['title' => 'Colors']))->name('color');
        Route::get('/all', [AdminProductController::class, 'index'])->name('index');
        Route::get('/create', [AdminProductController::class, 'create'])->name('create');
        Route::post('/store', [AdminProductController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [AdminProductController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [AdminProductController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [AdminProductController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-action', [AdminProductController::class, 'bulkAction'])->name('bulk.action');
        Route::post('/toggle-featured/{id}', [AdminProductController::class, 'toggleFeatured'])->name('toggle.featured');

        // Product Reviews Dashboard & Management
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [AdminProductReviewController::class, 'index'])->name('index');
            Route::get('/dashboard', [AdminProductReviewController::class, 'dashboard'])->name('dashboard');
            Route::get('/show/{id}', [AdminProductReviewController::class, 'show'])->name('show');
            Route::post('/status/{id}', [AdminProductReviewController::class, 'updateStatus'])->name('status');
            Route::post('/toggle-visibility/{id}', [AdminProductReviewController::class, 'toggleVisibility'])->name('toggle_visibility');
            Route::match(['delete', 'post'], '/destroy/{id}', [AdminProductReviewController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-action', [AdminProductReviewController::class, 'bulkAction'])->name('bulk_action');
            Route::post('/reply/{id}', [AdminProductReviewController::class, 'reply'])->name('reply');
            Route::match(['delete', 'post'], '/reply/delete/{id}', [AdminProductReviewController::class, 'deleteReply'])->name('reply.delete');
            Route::post('/toggle-pin/{id}', [AdminProductReviewController::class, 'togglePin'])->name('toggle_pin');
            Route::post('/pin-order', [AdminProductReviewController::class, 'updatePinOrder'])->name('pin_order');
            Route::get('/product/{productId}', [AdminProductReviewController::class, 'productReviews'])->name('product');
            Route::post('/product/{productId}/settings', [AdminProductReviewController::class, 'saveProductSettings'])->name('product.settings');
            Route::get('/customer/{userId}', [AdminProductReviewController::class, 'customerReviews'])->name('customer');
            Route::get('/auto-rules', [AdminProductReviewController::class, 'autoRulesIndex'])->name('auto_rules');
            Route::post('/auto-rules/store', [AdminProductReviewController::class, 'storeAutoRule'])->name('auto_rules.store');
            Route::post('/auto-rules/toggle/{id}', [AdminProductReviewController::class, 'toggleAutoRule'])->name('auto_rules.toggle');
            Route::match(['delete', 'post'], '/auto-rules/delete/{id}', [AdminProductReviewController::class, 'deleteAutoRule'])->name('auto_rules.delete');
            Route::get('/settings', [AdminProductReviewController::class, 'settingsIndex'])->name('settings');
            Route::post('/settings/update', [AdminProductReviewController::class, 'updateSettings'])->name('settings.update');
        });
    });

    Route::get('/reviews', [AdminProductReviewController::class, 'index'])->name('review.index');

    /* --- 6.6 Inventory Management System --- */
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/dashboard', [InventoryController::class, 'dashboard'])->name('dashboard');
        Route::get('/stock-in', [InventoryController::class, 'stockIn'])->name('stock_in');
        Route::post('/stock-in', [InventoryController::class, 'storeStockIn'])->name('stock_in.store');
        Route::get('/stock-out', [InventoryController::class, 'stockOut'])->name('stock_out');
        Route::post('/stock-out', [InventoryController::class, 'storeStockOut'])->name('stock_out.store');
        Route::get('/balance', [InventoryController::class, 'balance'])->name('balance');
        Route::get('/movements', [InventoryController::class, 'movements'])->name('movements');
        Route::get('/product/{id}/details', [InventoryController::class, 'productStockDetails'])->name('product.details');
        Route::get('/reports', [InventoryController::class, 'reports'])->name('reports');
        Route::post('/adjust', [InventoryController::class, 'adjustStock'])->name('adjust');
        Route::get('/search-products-ajax', [InventoryController::class, 'ajaxSearchProducts'])->name('search.products.ajax');
    });

    Route::get('/api/products/{id}/variants', function ($id) {
        if (!\Illuminate\Support\Facades\Schema::hasTable('product_variants')) {
            return response()->json([]);
        }
        $variants = \Illuminate\Support\Facades\DB::table('product_variants')
            ->where('product_id', $id)
            ->select('id', 'color', 'size', 'stock', 'product_code', 'sku', 'purchase_cost')
            ->get();
        return response()->json($variants);
    })->name('products.variants.ajax');

    /* --- 6.7 Support Requests --- */
    Route::prefix('support-requests')->name('support.')->group(function () {
        Route::get('/', [AdminSupportController::class, 'index'])->name('index');
        Route::get('/view/{id}', [AdminSupportController::class, 'show'])->name('show');
        Route::post('/reply/{id}', [AdminSupportController::class, 'replyTicket'])->name('reply');
    });

    /*
    |--------------------------------------------------------------------------
    | 6.8 ENTERPRISE PROCUREMENT & SUPPLY CHAIN MANAGEMENT HUB
    |--------------------------------------------------------------------------
    */
    Route::prefix('procurement')->group(function () {

        // Suppliers & Running Ledger Statements
        Route::get('suppliers/{supplier}/ledger', [SupplierController::class, 'ledger'])->name('suppliers.ledger');
        Route::resource('suppliers', SupplierController::class);

        // Purchase Orders AJAX Endpoints
        Route::get('purchase-orders/search-products-ajax', [PurchaseOrderController::class, 'searchProductsAjax'])->name('purchase-orders.search-products');
        Route::get('purchase-orders/find-product-by-sku-ajax', [PurchaseOrderController::class, 'findProductBySkuAjax'])->name('purchase-orders.find-by-sku');
        Route::post('purchase-orders/quick-store-product-ajax', [PurchaseOrderController::class, 'quickStoreProductAjax'])->name('purchase-orders.quick-store-product');

        // Purchase Orders (Requisition & Approval Pipeline)
        Route::post('purchase-orders/{purchase_order}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
        Route::post('purchase-orders/{purchase_order}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
        Route::resource('purchase-orders', PurchaseOrderController::class)->except(['edit', 'update', 'destroy']);
        Route::delete('purchase-orders/{id}', [PurchaseOrderController::class, 'destroy'])->name('purchase-orders.destroy');

        // Purchase Invoices / Bills
        Route::get('purchases/po-items/{purchaseOrder}', [PurchaseController::class, 'getPoItems'])->name('purchases.po-items');
        Route::resource('purchases', PurchaseController::class)->except(['edit', 'update', 'destroy']);

        // Goods Received Notes (GRN & Quality Inspection)
        Route::resource('purchase-receipts', PurchaseReceiptController::class)->except(['edit', 'update', 'destroy']);

        // Supplier Payment Vouchers & Advance Fund Allocations
        Route::get('supplier-payments/unpaid-purchases/{supplier}', [SupplierPaymentController::class, 'getUnpaidPurchases'])->name('supplier-payments.unpaid-purchases');
        Route::post('supplier-payments/{supplierPayment}/allocate-advance', [SupplierPaymentController::class, 'allocateAdvance'])->name('supplier-payments.allocate-advance');
        Route::post('supplier-payments/{supplierPayment}/cancel', [SupplierPaymentController::class, 'cancel'])->name('supplier-payments.cancel');
        Route::resource('supplier-payments', SupplierPaymentController::class)->except(['edit', 'update', 'destroy']);

        // Purchase Returns (RTV - Return to Vendor)
        Route::post('purchase-returns/{purchaseReturn}/approve', [PurchaseReturnController::class, 'approve'])->name('purchase-returns.approve');
        Route::resource('purchase-returns', PurchaseReturnController::class)->except(['edit', 'update', 'destroy']);

        // Supplier Product Exchanges (Defective/Replacement Swaps)
        Route::post('supplier-exchanges/{supplierExchange}/approve', [SupplierExchangeController::class, 'approve'])->name('supplier-exchanges.approve');
        Route::resource('supplier-exchanges', SupplierExchangeController::class)->except(['edit', 'update', 'destroy']);

        // Debit & Credit Notes (Adjustments, Claims & Discounts)
        Route::post('supplier-debit-credit-notes/{supplierDebitCreditNote}/approve', [SupplierDebitCreditNoteController::class, 'approve'])->name('supplier-debit-credit-notes.approve');
        Route::post('supplier-debit-credit-notes/{supplierDebitCreditNote}/cancel', [SupplierDebitCreditNoteController::class, 'cancel'])->name('supplier-debit-credit-notes.cancel');
        Route::resource('supplier-debit-credit-notes', SupplierDebitCreditNoteController::class)->except(['edit', 'update', 'destroy']);

        // Fixed Asset Procurement & Capitalization
        Route::post('procurement-assets/{asset}/transfer', [AssetProcurementController::class, 'transfer'])->name('procurement-assets.transfer');
        Route::post('procurement-assets/{asset}/depreciate', [AssetProcurementController::class, 'depreciate'])->name('procurement-assets.depreciate');
        Route::post('procurement-assets/{asset}/dispose', [AssetProcurementController::class, 'dispose'])->name('procurement-assets.dispose');
        Route::resource('procurement-assets', AssetProcurementController::class)->except(['edit', 'update', 'destroy']);
    });

   /* --- 6.9 Advanced Multi-Branch POS Terminal Module --- */
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [BranchPOSController::class, 'terminal'])->name('index');
        Route::get('/terminal/{branch_id?}', [BranchPOSController::class, 'terminal'])
            ->name('terminal')
            ->middleware(EnsureBranchAccess::class);
        Route::get('/filter-products', [BranchPOSController::class, 'searchProducts'])->name('filter.products');
        Route::get('/scan-barcode-ajax', [BranchPOSController::class, 'scanBarcode'])->name('scan.barcode');
        Route::get('/search-customers-ajax', [BranchPOSController::class, 'searchCustomers'])->name('search.customers');
        Route::post('/create-customer-ajax', [BranchPOSController::class, 'quickCustomer'])->name('customer.create');
        Route::post('/place-order', [BranchPOSController::class, 'checkout'])->name('order.place');
        Route::post('/checkout', [BranchPOSController::class, 'checkout'])->name('checkout');

        // Multi-Format Thermal & Invoice Print Routes (2-inch, 3-inch, A5, A4)
        Route::get('/print-pos-receipt/{id}', [BranchPOSController::class, 'printThermal'])->name('print.thermal');
        Route::get('/print-pos-receipt-58mm/{id}', [BranchPOSController::class, 'printThermal58'])->name('print.thermal58');
        Route::get('/print-a5-memo/{id}', [BranchPOSController::class, 'printA5'])->name('print.a5');
        Route::get('/print-a4-memo/{id}', [BranchPOSController::class, 'printA4'])->name('print.a4');
        Route::get('/print-receipt/{id}/{format?}', [BranchPOSController::class, 'printReceipt'])->name('print.receipt');

        Route::get('/stats-ajax', [BranchPOSController::class, 'getStats'])->name('stats');
    });

    /* --- 6.9.1 Branch Daily Cash Register & Closing --- */
    Route::prefix('daily-closing')->name('daily_closing.')->group(function () {
        Route::get('/', [BranchDailyClosingController::class, 'index'])->name('index');
        Route::get('/session/{branch_id?}', [BranchDailyClosingController::class, 'currentSession'])
            ->name('session')
            ->middleware(EnsureBranchAccess::class);
        Route::post('/open', [BranchDailyClosingController::class, 'open'])->name('open');
        Route::post('/close', [BranchDailyClosingController::class, 'close'])->name('close');
        Route::post('/verify/{id}', [BranchDailyClosingController::class, 'verify'])->name('verify');
        Route::get('/show/{id}', [BranchDailyClosingController::class, 'show'])->name('show');
        Route::get('/print/{id}', [BranchDailyClosingController::class, 'printSummary'])->name('print');
    });

    /* --- 6.10 Advanced Courier Management --- */
    Route::prefix('courier-management')->name('courier.')->group(function () {
        Route::get('/dashboard', [CourierManagementController::class, 'dashboard'])->name('dashboard');
        Route::get('/orders', [CourierManagementController::class, 'index'])->name('index');
        Route::get('/booking', [CourierManagementController::class, 'createBooking'])->name('booking');
        Route::post('/booking/store', [CourierManagementController::class, 'storeBooking'])->name('booking.store');
        Route::get('/search-order-ajax', [CourierManagementController::class, 'searchOrderForBooking'])->name('search.order');
        Route::get('/order-details-ajax/{id}', [CourierManagementController::class, 'getOrderDetailsForBooking'])->name('order.details');
        Route::get('/courier-info-ajax/{name}', [CourierManagementController::class, 'getCourierInfoAjax'])->name('info.ajax');
        Route::get('/settlements', [CourierManagementController::class, 'settlements'])->name('settlements');
        Route::post('/settlement-update/{id}', [CourierManagementController::class, 'updateSettlement'])->name('settlement.update');
        Route::post('/sync-status/{id}', [CourierManagementController::class, 'syncStatus'])->name('sync.status');
        Route::post('/manual-status-update/{id}', [CourierManagementController::class, 'updateManualStatus'])->name('manual.status.update');
        Route::match(['delete', 'post'], '/destroy/{id}', [CourierManagementController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-action', [CourierManagementController::class, 'bulkAction'])->name('bulk.action');
    });

    /* --- 6.11 Shipping Management --- */
    Route::prefix('shipping-management')->name('shipping.')->group(function () {
        Route::get('/zones', [AdminShippingController::class, 'zonesIndex'])->name('index');
        Route::get('/zones/create', [AdminShippingController::class, 'zoneCreate'])->name('zone.create');
        Route::post('/zones/store', [AdminShippingController::class, 'zoneStore'])->name('zone.store');
        Route::get('/zones/edit/{id}', [AdminShippingController::class, 'zoneEdit'])->name('zone.edit');
        Route::post('/zones/update/{id}', [AdminShippingController::class, 'zoneUpdate'])->name('zone.update');
        Route::delete('/zones/delete/{id}', [AdminShippingController::class, 'zoneDestroy'])->name('zone.destroy');
        Route::post('/zones/{zoneId}/save-weight-rates', [AdminShippingController::class, 'saveWeightRates'])->name('weight.rates.save');
        Route::post('/zones/{zoneId}/methods/store', [AdminShippingController::class, 'methodStore'])->name('method.store');
        Route::delete('/methods/delete/{id}', [AdminShippingController::class, 'methodDestroy'])->name('method.destroy');
        Route::get('/product/search', [AdminShippingController::class, 'searchProduct'])->name('product.search');
        Route::post('/product/update-shipping', [AdminShippingController::class, 'updateProductShipping'])->name('product.shipping.update');
        Route::get('/settings', [AdminShippingController::class, 'settingsIndex'])->name('settings');
        Route::post('/settings/update', [AdminShippingController::class, 'settingsUpdate'])->name('settings.update');
        Route::get('/classes', [AdminShippingController::class, 'classesIndex'])->name('classes');
        Route::post('/classes/store', [AdminShippingController::class, 'classStore'])->name('class.store');
        Route::delete('/classes/delete/{id}', [AdminShippingController::class, 'classDestroy'])->name('class.destroy');
        Route::get('/local-pickup', [AdminShippingController::class, 'pickupIndex'])->name('pickup');
        Route::post('/local-pickup/update', [AdminShippingController::class, 'pickupUpdate'])->name('pickup.update');
        Route::post('/local-pickup/location/store', [AdminShippingController::class, 'pickupLocationStore'])->name('pickup.location.store');
        Route::delete('/local-pickup/location/delete/{id}', [AdminShippingController::class, 'pickupLocationDestroy'])->name('pickup.location.destroy');
    });

    /* --- 6.12 Orders Management & Fraud Checker --- */
    Route::prefix('orders')->name('order.')->group(function () {
        Route::get('/', fn() => redirect()->route('admin.order.online'));
        Route::get('/online', [AdminOrderController::class, 'index'])->name('online');
        Route::get('/view/{id}', [AdminOrderController::class, 'show'])->name('view');
        Route::post('/status-update/{id}', [AdminOrderController::class, 'updateStatus'])->name('status.update');
        Route::post('/update-status/{id}', [AdminOrderController::class, 'updateStatus'])->name('update_status');
        Route::post('/send-courier/{id}', [AdminOrderController::class, 'sendToCourier'])->name('courier.send');
        Route::post('/courier-send/{id}', [AdminOrderController::class, 'sendToCourier'])->name('send_courier');
        Route::get('/print-invoice/{id}', [AdminOrderController::class, 'printInvoice'])->name('invoice.print');
        Route::get('/invoice/{id}', [AdminOrderController::class, 'printInvoice'])->name('invoice');
        Route::match(['delete', 'post'], '/delete/{id}', [AdminOrderController::class, 'destroy'])->name('destroy');
        Route::post('/delete/{id}', [AdminOrderController::class, 'destroy'])->name('delete');
        Route::post('/bulk-action', [AdminOrderController::class, 'bulkAction'])->name('bulk.action');
        Route::post('/bulk-action-run', [AdminOrderController::class, 'bulkAction'])->name('bulk_action');
        Route::get('/incomplete', [AdminOrderController::class, 'incompleteOrders'])->name('incomplete');
        Route::get('/return', [AdminOrderController::class, 'returnOrders'])->name('return');
        Route::get('/exchange', [AdminOrderController::class, 'exchangeOrders'])->name('exchange');
        Route::match(['get', 'post'], '/fraud-checker', [AdminOrderController::class, 'fraudChecker'])->name('fraud');

        // Dedicated Manual SMS Trigger Route
        Route::post('/send-manual-sms/{id}', [AdminOrderController::class, 'sendManualSms'])->name('send_manual_sms');

        Route::get('/create-manual', [AdminOrderController::class, 'createManual'])->name('create_manual');
        Route::post('/store-manual', [AdminOrderController::class, 'storeManual'])->name('store_manual');
        Route::get('/search-products-ajax', [AdminOrderController::class, 'searchProductsAjax'])->name('search_products');
        Route::get('/search-customers-ajax', [AdminOrderController::class, 'searchCustomersAjax'])->name('search_customers');
        Route::post('/process-return-exchange/{id}', [AdminOrderController::class, 'processReturnExchange'])->name('process_return_exchange');
    });

    /* --- 6.13 Payment Transactions Management --- */
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/dashboard', [PaymentTransactionController::class, 'dashboard'])->name('dashboard');
        Route::get('/checkout-requests', [PaymentTransactionController::class, 'checkoutRequests'])->name('checkout');
        Route::get('/pos-cash', [PaymentTransactionController::class, 'posCashOrders'])->name('pos');
        Route::get('/cod-orders', [PaymentTransactionController::class, 'codOrders'])->name('cod');
        Route::get('/manual', [PaymentTransactionController::class, 'manualTransactions'])->name('manual');
        Route::get('/all', [PaymentTransactionController::class, 'index'])->name('index');
        Route::get('/unchecked', [PaymentTransactionController::class, 'uncheckedTransactions'])->name('unchecked');
        Route::get('/verification-history', [PaymentTransactionController::class, 'verificationHistory'])->name('history');
        Route::get('/reports', [PaymentTransactionController::class, 'reports'])->name('reports');
        Route::get('/trash', [PaymentTransactionController::class, 'trashedTransactions'])->name('trash');

        Route::get('/show/{id}', [PaymentTransactionController::class, 'show'])->name('show');
        Route::post('/verify/{id}', [PaymentTransactionController::class, 'verifyAction'])->name('verify');
        Route::post('/manual-store', [PaymentTransactionController::class, 'storeManual'])->name('store_manual');
        Route::get('/search-order-ajax', [PaymentTransactionController::class, 'searchOrderAjax'])->name('search_order');
        Route::post('/bulk-action', [PaymentTransactionController::class, 'bulkAction'])->name('bulk_action');
        Route::match(['delete', 'post'], '/destroy/{id}', [PaymentTransactionController::class, 'destroy'])->name('destroy');
    });

    /* --- 6.14 Enterprise Tax & VAT System --- */
    Route::prefix('tax')->name('tax.')->group(function () {
        Route::get('/dashboard', [TaxDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/chart-ajax', [TaxDashboardController::class, 'chartDataAjax'])->name('dashboard.chart_ajax');

        Route::prefix('classes')->name('classes.')->group(function () {
            Route::get('/', [TaxClassController::class, 'index'])->name('index');
            Route::post('/store', [TaxClassController::class, 'store'])->name('store');
            Route::get('/edit/{id}', [TaxClassController::class, 'edit'])->name('edit');
            Route::match(['put', 'post'], '/update/{id}', [TaxClassController::class, 'update'])->name('update');
            Route::match(['delete', 'post'], '/delete/{id}', [TaxClassController::class, 'destroy'])->name('destroy');
            Route::post('/toggle-status/{id}', [TaxClassController::class, 'toggleStatus'])->name('toggle_status');
        });

        Route::prefix('rules')->name('rules.')->group(function () {
            Route::get('/', [TaxRuleController::class, 'index'])->name('index');
            Route::get('/create', [TaxRuleController::class, 'create'])->name('create');
            Route::post('/store', [TaxRuleController::class, 'store'])->name('store');
            Route::get('/edit/{id}', [TaxRuleController::class, 'edit'])->name('edit');
            Route::match(['put', 'post'], '/update/{id}', [TaxRuleController::class, 'update'])->name('update');
            Route::match(['delete', 'post'], '/delete/{id}', [TaxRuleController::class, 'destroy'])->name('destroy');
            Route::post('/toggle-status/{id}', [TaxRuleController::class, 'toggleStatus'])->name('toggle_status');
            Route::get('/search-entities-ajax', [TaxRuleController::class, 'searchEntitiesAjax'])->name('search_entities');
        });

        Route::get('/product-tax', fn() => redirect()->route('admin.tax.rules.index', ['type' => 'product']))->name('product_tax');
        Route::get('/category-tax', fn() => redirect()->route('admin.tax.rules.index', ['type' => 'category']))->name('category_tax');
        Route::get('/shipping-tax', fn() => redirect()->route('admin.tax.rules.index', ['type' => 'shipping_zone']))->name('shipping_tax');
        Route::get('/branch-tax', fn() => redirect()->route('admin.tax.rules.index', ['type' => 'branch']))->name('branch_tax');
        Route::get('/service-tax', fn() => redirect()->route('admin.tax.rules.index', ['type' => 'service_fee']))->name('service_tax');

        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [TaxPaymentController::class, 'index'])->name('index');
            Route::post('/store', [TaxPaymentController::class, 'store'])->name('store');
            Route::get('/show/{id}', [TaxPaymentController::class, 'show'])->name('show');
            Route::match(['put', 'post'], '/update/{id}', [TaxPaymentController::class, 'update'])->name('update');
            Route::match(['delete', 'post'], '/delete/{id}', [TaxPaymentController::class, 'destroy'])->name('destroy');
            Route::get('/download/{id}', [TaxPaymentController::class, 'downloadChallan'])->name('download');
        });

        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', [TaxDocumentController::class, 'index'])->name('index');
            Route::post('/store', [TaxDocumentController::class, 'store'])->name('store');
            Route::get('/show/{id}', [TaxDocumentController::class, 'show'])->name('show');
            Route::match(['put', 'post'], '/update/{id}', [TaxDocumentController::class, 'update'])->name('update');
            Route::match(['delete', 'post'], '/delete/{id}', [TaxDocumentController::class, 'destroy'])->name('destroy');
            Route::get('/download/{id}', [TaxDocumentController::class, 'download'])->name('download');
        });

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [TaxReportController::class, 'index'])->name('index');
            Route::get('/export-csv', [TaxReportController::class, 'exportCsv'])->name('export');
            Route::get('/print', [TaxReportController::class, 'printReport'])->name('print');
        });

        Route::prefix('periods')->name('periods.')->group(function () {
            Route::get('/', [TaxPeriodController::class, 'index'])->name('index');
            Route::post('/close', [TaxPeriodController::class, 'close'])->name('close');
            Route::get('/{id}', [TaxPeriodController::class, 'show'])->name('show');
            Route::post('/unlock/{id}', [TaxPeriodController::class, 'unlock'])->name('unlock');
        });

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [TaxSettingController::class, 'index'])->name('index');
            Route::post('/update', [TaxSettingController::class, 'update'])->name('update');
        });

        Route::get('/collection', fn() => redirect()->route('admin.tax.reports.index', ['report_type' => 'collection_history']))->name('collection');
        Route::get('/history', fn() => redirect()->route('admin.tax.reports.index', ['report_type' => 'all_transactions']))->name('history');
        Route::get('/adjustments', fn() => redirect()->route('admin.tax.reports.index', ['report_type' => 'adjustment_history']))->name('adjustments');
        
        Route::get('/audit-logs', [TaxReportController::class, 'auditLogs'])->name('audit_logs');
    });

    /*
    |--------------------------------------------------------------------------
    | 6.15 ADVANCED SECURITY, IP DETECTION, FIREWALL & TRACKER SYSTEM
    |--------------------------------------------------------------------------
    */
    Route::prefix('security')->name('security.')->group(function () {
        Route::get('/dashboard', [SecurityDashboardController::class, 'index'])->name('dashboard');

        Route::prefix('ips')->name('ips.')->group(function () {
            Route::get('/', [SecurityIpManagementController::class, 'index'])->name('index');
            Route::get('/export-csv', [SecurityIpManagementController::class, 'export'])->name('export');
            Route::get('/show/{ip}', [SecurityIpManagementController::class, 'show'])->name('show');
            Route::post('/block', [SecurityIpManagementController::class, 'block'])->name('block');
            Route::post('/unblock', [SecurityIpManagementController::class, 'unblock'])->name('unblock');
        });

        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [SecuritySuspiciousOrderController::class, 'index'])->name('index');
            Route::get('/clusters', [SecuritySuspiciousOrderController::class, 'clusters'])->name('clusters');
            Route::get('/show/{id}', [SecuritySuspiciousOrderController::class, 'show'])->name('show');
            Route::post('/status/{id}', [SecuritySuspiciousOrderController::class, 'updateStatus'])->name('update_status');
            Route::post('/quick-block', [SecuritySuspiciousOrderController::class, 'quickBlockAction'])->name('quick_block');
        });

        Route::prefix('urls')->name('urls.')->group(function () {
            Route::get('/', [SecurityUrlMonitoringController::class, 'index'])->name('index');
            Route::get('/show/{id}', [SecurityUrlMonitoringController::class, 'show'])->name('show');
            Route::get('/export-csv', [SecurityUrlMonitoringController::class, 'export'])->name('export');
            Route::post('/block-all/{id}', [SecurityUrlMonitoringController::class, 'blockAllAttackingIps'])->name('block_all');
            Route::delete('/reset/{id}', [SecurityUrlMonitoringController::class, 'resetMetrics'])->name('reset');
        });

        Route::prefix('blocking')->name('blocking.')->group(function () {
            Route::get('/ips', [SecurityBlockingManagementController::class, 'blockedIps'])->name('ips');
            Route::post('/ips/store', [SecurityBlockingManagementController::class, 'storeBlockedIp'])->name('ips.store');
            Route::post('/ips/unblock/{id}', [SecurityBlockingManagementController::class, 'unblockIp'])->name('ips.unblock');
            Route::post('/ips/update/{id}', [SecurityBlockingManagementController::class, 'updateBlockedIp'])->name('ips.update');

            Route::get('/networks', [SecurityBlockingManagementController::class, 'blockedNetworks'])->name('networks');
            Route::post('/networks/store', [SecurityBlockingManagementController::class, 'storeBlockedNetwork'])->name('networks.store');
            Route::post('/networks/unblock/{id}', [SecurityBlockingManagementController::class, 'unblockNetwork'])->name('networks.unblock');

            Route::get('/users', [SecurityBlockingManagementController::class, 'userRestrictions'])->name('users');
            Route::post('/users/store', [SecurityBlockingManagementController::class, 'storeUserRestriction'])->name('users.store');
            Route::post('/users/lift/{id}', [SecurityBlockingManagementController::class, 'liftUserRestriction'])->name('users.lift');

            Route::post('/emergency-flush', [SecurityBlockingManagementController::class, 'emergencyFlush'])->name('emergency_flush');
        });

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SecurityRulesAndSettingsController::class, 'index'])->name('index');
            Route::post('/update', [SecurityRulesAndSettingsController::class, 'updateSettings'])->name('update');
            Route::post('/allowlist/store', [SecurityRulesAndSettingsController::class, 'storeAllowlist'])->name('allowlist.store');
            Route::post('/allowlist/toggle/{id}', [SecurityRulesAndSettingsController::class, 'toggleAllowlist'])->name('allowlist.toggle');
            Route::match(['delete', 'post'], '/allowlist/{id}', [SecurityRulesAndSettingsController::class, 'destroyAllowlist'])->name('allowlist.destroy');
        });

        Route::prefix('logs')->name('logs.')->group(function () {
            Route::get('/', [SecurityLogsAndAlertsController::class, 'logs'])->name('index');
            Route::get('/export-csv', [SecurityLogsAndAlertsController::class, 'exportLogs'])->name('export');
            Route::get('/show/{id}', [SecurityLogsAndAlertsController::class, 'showLog'])->name('show');
            Route::post('/cleanup', [SecurityLogsAndAlertsController::class, 'cleanupLogs'])->name('cleanup');
        });

        Route::prefix('alerts')->name('alerts.')->group(function () {
            Route::get('/', [SecurityLogsAndAlertsController::class, 'alerts'])->name('index');
            Route::post('/mark-read/{id}', [SecurityLogsAndAlertsController::class, 'markAlertAsRead'])->name('mark_read');
            Route::post('/mark-all-read', [SecurityLogsAndAlertsController::class, 'markAllAlertsAsRead'])->name('mark_all_read');
            Route::delete('/destroy/{id}', [SecurityLogsAndAlertsController::class, 'destroyAlert'])->name('destroy');
        });
    });

    /* --- 6.16 Promotions, Coupons & Vouchers --- */
    Route::prefix('promo')->name('promo.')->group(function () {
        Route::get('/flash-sale', fn() => view('admin.modules.placeholder', ['title' => 'Flash Sale']))->name('flashsale');

        Route::prefix('coupons')->name('coupons.')->group(function () {
            Route::get('/', [AdminCouponController::class, 'index'])->name('index');
            Route::get('/create', [AdminCouponController::class, 'createCoupon'])->name('create');
            Route::post('/store', [AdminCouponController::class, 'storeCoupon'])->name('store');
            Route::get('/edit/{id}', [AdminCouponController::class, 'editCoupon'])->name('edit');
            Route::post('/update/{id}', [AdminCouponController::class, 'updateCoupon'])->name('update');
            Route::match(['delete', 'post'], '/delete/{id}', [AdminCouponController::class, 'destroyCoupon'])->name('destroy');
            Route::post('/toggle-status/{id}', [AdminCouponController::class, 'toggleCouponStatus'])->name('toggle_status');

            Route::get('/search-products-ajax', [AdminCouponController::class, 'searchProductsAjax'])->name('search_products_ajax');
            Route::get('/search-categories-ajax', [AdminCouponController::class, 'searchCategoriesAjax'])->name('search_categories_ajax');
            Route::match(['get', 'post'], '/redemptions/{id}/toggle-status', [AdminCouponController::class, 'toggleRedemptionStatus'])->name('redemptions.toggle_status');

            Route::prefix('vouchers')->name('vouchers.')->group(function () {
                Route::get('/create', [AdminCouponController::class, 'createVoucher'])->name('create');
                Route::post('/store', [AdminCouponController::class, 'storeVoucher'])->name('store');
                Route::get('/edit/{id}', [AdminCouponController::class, 'editVoucher'])->name('edit');
                Route::post('/update/{id}', [AdminCouponController::class, 'updateVoucher'])->name('update');
                Route::match(['delete', 'post'], '/delete/{id}', [AdminCouponController::class, 'destroyVoucher'])->name('destroy');
                Route::post('/toggle-status/{id}', [AdminCouponController::class, 'toggleVoucherStatus'])->name('toggle_status');
            });
        });

        Route::get('/coupons', [AdminCouponController::class, 'index'])->name('coupon');
    });

    /* --- 6.17 Accounts, Expenses, Banking & Assets Ledger --- */
    Route::prefix('accounts')->name('accounts.')->group(function () {
        Route::get('/payment-method', [PaymentSettingController::class, 'index'])->name('methods');
        Route::get('/transactions', [PaymentTransactionController::class, 'index'])->name('transactions');

        Route::prefix('ledger')->name('ledger.')->group(function () {
            Route::get('/', [AccountLedgerController::class, 'index'])->name('index');
            Route::get('/export-csv', [AccountLedgerController::class, 'exportCsv'])->name('export');
        });
        Route::get('/ledger', [AccountLedgerController::class, 'index'])->name('ledger');

        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('/expenses/store', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::match(['delete', 'post'], '/expenses/delete/{id}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
        Route::post('/expenses/category/store', [ExpenseController::class, 'storeCategory'])->name('expenses.category.store');
        Route::get('/expenses/export-csv', [ExpenseController::class, 'exportCsv'])->name('expenses.export');
        Route::get('/expenses-manage', [ExpenseController::class, 'index'])->name('expenses');

        Route::prefix('bank-balance')->name('bank.')->group(function () {
            Route::get('/', [BankAccountController::class, 'index'])->name('index');
            Route::post('/account/store', [BankAccountController::class, 'storeAccount'])->name('account.store');
            Route::post('/transfer', [BankAccountController::class, 'transferBalance'])->name('transfer');
            Route::post('/deposit', [BankAccountController::class, 'deposit'])->name('deposit');
            Route::get('/export-csv', [BankAccountController::class, 'exportCsv'])->name('export');
            Route::get('/statement/{id}', [BankAccountController::class, 'statement'])->name('statement');
        });
        Route::get('/bank-balance', [BankAccountController::class, 'index'])->name('bank');

        Route::prefix('income')->name('income.')->group(function () {
            Route::get('/', [IncomeController::class, 'index'])->name('index');
            Route::post('/store', [IncomeController::class, 'store'])->name('store');
            Route::match(['delete', 'post'], '/delete/{id}', [IncomeController::class, 'destroy'])->name('destroy');
            Route::post('/category/store', [IncomeController::class, 'storeCategory'])->name('category.store');
            Route::get('/export-csv', [IncomeController::class, 'exportCsv'])->name('export');
        });
        Route::get('/income', [IncomeController::class, 'index'])->name('income');

        Route::prefix('investment')->name('investment.')->group(function () {
            Route::get('/', [InvestmentController::class, 'index'])->name('index');
            Route::post('/store', [InvestmentController::class, 'store'])->name('store');
            Route::match(['delete', 'post'], '/delete/{id}', [InvestmentController::class, 'destroy'])->name('destroy');
            Route::get('/export-csv', [InvestmentController::class, 'exportCsv'])->name('export');
        });
        Route::get('/investment', [IncomeController::class, 'index'])->name('investment');

        Route::prefix('assets')->name('assets.')->group(function () {
            Route::get('/', [AssetController::class, 'index'])->name('index');
            Route::post('/store', [AssetController::class, 'store'])->name('store');
            Route::match(['delete', 'post'], '/delete/{id}', [AssetController::class, 'destroy'])->name('destroy');
            Route::get('/export-csv', [AssetController::class, 'exportCsv'])->name('export');
        });
        Route::get('/assets', [AssetController::class, 'index'])->name('assets');
    });

    /* --- 6.18 Financial Reports & Balance Sheets --- */
    Route::prefix('report')->name('report.')->group(function () {
        Route::get('/stock', fn() => redirect()->route('admin.inventory.balance'))->name('stock');
        Route::get('/sales', fn() => redirect()->route('admin.inventory.reports'))->name('sales');
        Route::get('/purchase', fn() => redirect()->route('admin.inventory.movements', ['movement_type' => 'purchase_in']))->name('purchase');
        Route::get('/profit-loss', [FinancialReportController::class, 'profitLoss'])->name('profit_loss');
        Route::get('/cogs-analysis', [FinancialReportController::class, 'cogsAnalysis'])->name('cogs');
        Route::get('/export-pnl', [FinancialReportController::class, 'exportPnl'])->name('export_pnl');

        Route::get('/balance-sheet', [FinancialReportController::class, 'balanceSheet'])->name('balance_sheet');
        Route::get('/export-balance-sheet', [FinancialReportController::class, 'exportBalanceSheet'])->name('export_balance_sheet');

        Route::get('/cash-flow', [FinancialReportController::class, 'cashFlow'])->name('cash_flow');
        Route::get('/export-cash-flow', [FinancialReportController::class, 'exportCashFlow'])->name('export_cash_flow');
    });

    /*
    |--------------------------------------------------------------------------
    | 6.18.1 STAFF PAYROLL & SALARY DISBURSEMENT (30TH OF MONTH AUTO-CALC)
    |--------------------------------------------------------------------------
    */
    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/', [PayrollController::class, 'index'])->name('index');
        Route::post('/generate', [PayrollController::class, 'generate'])->name('generate');
        Route::post('/disburse', [PayrollController::class, 'disburse'])->name('disburse');
        Route::post('/adjust', [PayrollController::class, 'adjust'])->name('adjust');
        Route::get('/history/{userId}', [PayrollController::class, 'staffSalaryHistory'])->name('history');
        Route::get('/payslip/{id}', [PayrollController::class, 'printSlip'])->name('payslip');
    });

    /*
    |--------------------------------------------------------------------------
    | 6.18.2 BRANCH FINANCIAL & ANALYTICS REPORTS
    |--------------------------------------------------------------------------
    */
    Route::prefix('branch-report')->name('branch_report.')->group(function () {
        Route::get('/dashboard', [BranchReportController::class, 'dashboard'])->name('dashboard');
        Route::get('/sales', [BranchReportController::class, 'salesReport'])->name('sales');
        Route::get('/sales-person', [BranchReportController::class, 'salesPersonReport'])->name('sales_person');
        Route::get('/comparison', [BranchReportController::class, 'comparisonReport'])->name('comparison');
        Route::get('/inventory', [BranchReportController::class, 'inventoryReport'])->name('inventory');
        Route::get('/export-sales-csv', [BranchReportController::class, 'exportSalesCsv'])->name('export_sales_csv');
    });

    /* --- 6.19 Advanced Email Marketing System --- */
    Route::prefix('email-marketing')->name('email_marketing.')->group(function () {
        Route::get('/dashboard', [EmailMarketingController::class, 'dashboard'])->name('dashboard');

        Route::prefix('subscribers')->name('subscribers.')->group(function () {
            Route::get('/', [EmailMarketingController::class, 'subscribersIndex'])->name('index');
            Route::post('/store', [EmailMarketingController::class, 'subscriberStore'])->name('store');
            Route::post('/update/{id}', [EmailMarketingController::class, 'subscriberUpdate'])->name('update');
            Route::delete('/delete/{id}', [EmailMarketingController::class, 'subscriberDestroy'])->name('destroy');
            Route::post('/delete/{id}', [EmailMarketingController::class, 'subscriberDestroy']);
            Route::post('/toggle-status/{id}', [EmailMarketingController::class, 'subscriberToggleStatus'])->name('toggle_status');
            Route::get('/export-csv', [EmailMarketingController::class, 'subscribersExport'])->name('export');
            Route::post('/bulk-send-instant', [EmailMarketingController::class, 'bulkSendEmailInstant'])->name('bulk_send_instant');
        });

        Route::prefix('segments')->name('segments.')->group(function () {
            Route::get('/', [EmailMarketingController::class, 'segmentsIndex'])->name('index');
            Route::get('/create', [EmailMarketingController::class, 'segmentCreate'])->name('create');
            Route::post('/store', [EmailMarketingController::class, 'segmentStore'])->name('store');
            Route::get('/edit/{id}', [EmailMarketingController::class, 'segmentEdit'])->name('edit');
            Route::post('/update/{id}', [EmailMarketingController::class, 'segmentUpdate'])->name('update');
            Route::delete('/delete/{id}', [EmailMarketingController::class, 'segmentDestroy'])->name('destroy');
            Route::post('/delete/{id}', [EmailMarketingController::class, 'segmentDestroy']);
            Route::post('/preview-count', [EmailMarketingController::class, 'segmentPreviewCount'])->name('preview_count');
            Route::post('/recalculate/{id}', [EmailMarketingController::class, 'segmentRecalculate'])->name('recalculate');
            Route::post('/bulk-delete', [EmailMarketingController::class, 'segmentBulkDelete'])->name('bulk_delete');
            Route::post('/bulk-recalculate', [EmailMarketingController::class, 'segmentBulkRecalculate'])->name('bulk_recalculate');
            Route::get('/export-csv', [EmailMarketingController::class, 'segmentsExport'])->name('export');
        });
        Route::get('/segments', [EmailMarketingController::class, 'segmentsIndex'])->name('segments');

        Route::prefix('campaigns')->name('campaigns.')->group(function () {
            Route::get('/', [EmailMarketingController::class, 'campaignsIndex'])->name('index');
            Route::get('/create', [EmailMarketingController::class, 'campaignCreate'])->name('create');
            Route::post('/store', [EmailMarketingController::class, 'campaignStore'])->name('store');
            Route::get('/edit/{id}', [EmailMarketingController::class, 'campaignEdit'])->name('edit');
            Route::post('/update/{id}', [EmailMarketingController::class, 'campaignUpdate'])->name('update');
            Route::delete('/delete/{id}', [EmailMarketingController::class, 'campaignDestroy'])->name('destroy');
            Route::get('/view/{id}', [EmailMarketingController::class, 'campaignShow'])->name('show');
            Route::post('/send-test/{id}', [EmailMarketingController::class, 'campaignSendTest'])->name('send_test');
            Route::post('/dispatch-now/{id}', [EmailMarketingController::class, 'campaignDispatchNow'])->name('dispatch_now');
            Route::post('/pause/{id}', [EmailMarketingController::class, 'campaignPause'])->name('pause');
            Route::post('/resume/{id}', [EmailMarketingController::class, 'campaignResume'])->name('resume');
            Route::post('/cancel/{id}', [EmailMarketingController::class, 'campaignCancel'])->name('cancel');
            Route::get('/recipients/{id}', [EmailMarketingController::class, 'campaignRecipients'])->name('recipients');
        });

        Route::prefix('templates')->name('templates.')->group(function () {
            Route::get('/', [EmailMarketingController::class, 'templatesIndex'])->name('index');
            Route::get('/create', [EmailMarketingController::class, 'templateCreate'])->name('create');
            Route::post('/store', [EmailMarketingController::class, 'templateStore'])->name('store');
            Route::get('/edit/{id}', [EmailMarketingController::class, 'templateEdit'])->name('edit');
            Route::post('/update/{id}', [EmailMarketingController::class, 'templateUpdate'])->name('update');
            Route::delete('/delete/{id}', [EmailMarketingController::class, 'templateDestroy'])->name('destroy');
            Route::post('/preview', [EmailMarketingController::class, 'templatePreviewAjax'])->name('preview');
            Route::post('/duplicate/{id}', [EmailMarketingController::class, 'templateDuplicate'])->name('duplicate');
        });

        Route::prefix('automations')->name('automations.')->group(function () {
            Route::get('/', [EmailMarketingController::class, 'automationsIndex'])->name('index');
            Route::get('/create', [EmailMarketingController::class, 'automationCreate'])->name('create');
            Route::post('/store', [EmailMarketingController::class, 'automationStore'])->name('store');
            Route::get('/edit/{id}', [EmailMarketingController::class, 'automationEdit'])->name('edit');
            Route::post('/update/{id}', [EmailMarketingController::class, 'automationUpdate'])->name('update');
            Route::delete('/delete/{id}', [EmailMarketingController::class, 'automationDestroy'])->name('destroy');
            Route::post('/toggle-status/{id}', [EmailMarketingController::class, 'automationToggleStatus'])->name('toggle_status');
            Route::get('/logs', [EmailMarketingController::class, 'automationLogs'])->name('logs');
        });

        Route::get('/product-marketing', [EmailMarketingController::class, 'productMarketing'])->name('product_marketing');
        Route::match(['get', 'post'], '/campaigns/create-product-targeted', [EmailMarketingController::class, 'createProductCampaign'])->name('campaigns.create_product_targeted');
        Route::match(['get', 'post'], '/product-marketing/create-campaign', [EmailMarketingController::class, 'createProductCampaign'])->name('product_marketing.create_campaign');

        Route::prefix('cart-wishlist')->name('cart_wishlist.')->group(function () {
            Route::get('/', [EmailMarketingController::class, 'cartWishlistMarketing'])->name('index');
            Route::post('/send-recovery/{id}', [EmailMarketingController::class, 'sendCartRecoveryDirect'])->name('send_recovery');
            Route::post('/bulk-delete', [EmailMarketingController::class, 'cartWishlistBulkDelete'])->name('bulk_delete');
            Route::post('/bulk-send', [EmailMarketingController::class, 'cartWishlistBulkSend'])->name('bulk_send');
            Route::delete('/delete/{id}', [EmailMarketingController::class, 'cartWishlistDestroy'])->name('destroy');
            Route::post('/delete/{id}', [EmailMarketingController::class, 'cartWishlistDestroy']);
            Route::get('/export-csv', [EmailMarketingController::class, 'cartWishlistExport'])->name('export');
        });
        Route::get('/cart-wishlist', [EmailMarketingController::class, 'cartWishlistMarketing'])->name('cart_wishlist_marketing');
        Route::get('/cart-wishlist-all', [EmailMarketingController::class, 'cartWishlistMarketing'])->name('cart_wishlist');
        Route::post('/cart-wishlist/send-recovery/{id}', [EmailMarketingController::class, 'sendCartRecoveryDirect'])->name('cart_wishlist_marketing.send_direct');
        Route::post('/cart-wishlist/send-recovery-alt/{id}', [EmailMarketingController::class, 'sendCartRecoveryDirect']);

        Route::get('/customer-groups', [EmailMarketingController::class, 'customerGroups'])->name('customer_groups');
        Route::get('/scheduled', [EmailMarketingController::class, 'scheduledCampaigns'])->name('scheduled');
        Route::get('/queue-logs', [EmailMarketingController::class, 'queueLogs'])->name('queue_logs');
        Route::get('/analytics', [EmailMarketingController::class, 'analytics'])->name('analytics');
        Route::get('/analytics/export', [EmailMarketingController::class, 'analyticsExport'])->name('analytics.export');

        Route::get('/ab-testing', [EmailMarketingController::class, 'abTestingIndex'])->name('ab_testing');
        Route::post('/ab-testing/store', [EmailMarketingController::class, 'abTestingStore'])->name('ab_testing.store');

        Route::get('/coupons', [EmailMarketingController::class, 'couponsIndex'])->name('coupons');
        Route::post('/coupons/campaign', [EmailMarketingController::class, 'createCouponCampaign'])->name('coupons.campaign');
        Route::post('/coupons/create-campaign', [EmailMarketingController::class, 'createCouponCampaign'])->name('coupons.create_campaign');

        Route::get('/suppression', [EmailMarketingController::class, 'suppressionIndex'])->name('suppression');
        Route::post('/suppression/store', [EmailMarketingController::class, 'suppressionStore'])->name('suppression.store');
        Route::delete('/suppression/delete/{id}', [EmailMarketingController::class, 'suppressionDestroy'])->name('suppression.destroy');

        Route::get('/providers', [EmailMarketingController::class, 'providersIndex'])->name('providers');
        Route::post('/providers/store', [EmailMarketingController::class, 'providerStore'])->name('store');
        Route::match(['put', 'post'], '/providers/update/{id}', [EmailMarketingController::class, 'providerUpdate'])->name('providers.update');
        Route::match(['put', 'post'], '/providers/{id}', [EmailMarketingController::class, 'providerUpdate']);
        Route::match(['delete', 'post'], '/providers/delete/{id}', [EmailMarketingController::class, 'providerDestroy'])->name('providers.destroy');
        Route::match(['delete', 'post'], '/providers/{id}', [EmailMarketingController::class, 'providerDestroy']);
        Route::post('/providers/make-active/{id}', [EmailMarketingController::class, 'providerMakeActive'])->name('providers.make_active');
        Route::post('/providers/test-connection', [EmailMarketingController::class, 'providerTestConnection'])->name('providers.test_connection');
        Route::post('/providers/test', [EmailMarketingController::class, 'providerTestConnection'])->name('providers.test');

        Route::get('/tracking-settings', [EmailMarketingController::class, 'trackingSettings'])->name('tracking_settings');
        Route::post('/tracking-settings/update', [EmailMarketingController::class, 'updateTrackingSettings'])->name('tracking_settings.update');

        Route::get('/settings', [EmailMarketingController::class, 'settingsIndex'])->name('settings');
        Route::post('/settings/update', [EmailMarketingController::class, 'updateSettings'])->name('settings.update');
    });

    /* --- 6.20 SMS Gateway, Marketing & Notifications --- */
    Route::prefix('sms')->name('sms.')->group(function () {
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SmsSettingController::class, 'index'])->name('index');
            Route::post('/update', [SmsSettingController::class, 'update'])->name('update');
            Route::post('/provider', [SmsSettingController::class, 'updateProvider'])->name('provider.update');
            Route::post('/test-sms', [SmsSettingController::class, 'testSms'])->name('test');
            Route::post('/sync-balance', [SmsSettingController::class, 'syncBalance'])->name('sync_balance');
        });

        Route::prefix('templates')->name('templates.')->group(function () {
            Route::get('/', [SmsTemplateController::class, 'index'])->name('index');
            Route::get('/create', [SmsTemplateController::class, 'create'])->name('create');
            Route::post('/store', [SmsTemplateController::class, 'store'])->name('store');
            Route::get('/edit/{template}', [SmsTemplateController::class, 'edit'])->name('edit');
            Route::match(['put', 'post'], '/update/{template}', [SmsTemplateController::class, 'update'])->name('update');
            Route::match(['delete', 'post'], '/delete/{template}', [SmsTemplateController::class, 'destroy'])->name('destroy');
            Route::post('/toggle-status/{template}', [SmsTemplateController::class, 'toggleStatus'])->name('toggle_status');
            Route::post('/preview-metrics', [SmsTemplateController::class, 'previewMetrics'])->name('preview_metrics');
        });

        Route::prefix('campaigns')->name('campaigns.')->group(function () {
            Route::get('/', [SmsCampaignController::class, 'index'])->name('index');
            Route::get('/create', [SmsCampaignController::class, 'create'])->name('create');
            Route::post('/store', [SmsCampaignController::class, 'store'])->name('store');
            Route::get('/show/{campaign}', [SmsCampaignController::class, 'show'])->name('show');
            Route::post('/cancel/{campaign}', [SmsCampaignController::class, 'cancel'])->name('cancel');
            Route::post('/retry-failed/{campaign}', [SmsCampaignController::class, 'retryFailed'])->name('retry_failed');
            Route::post('/dispatch-now/{campaign}', [SmsCampaignController::class, 'dispatchNow'])->name('dispatch_now');    
            Route::match(['get', 'post'], '/filter-recipients-ajax', [SmsCampaignController::class, 'filterRecipientsAjax'])->name('filter_recipients_ajax');
            Route::get('/cron-run', [SmsCampaignController::class, 'cronRunScheduled'])->name('cron_run');
        });

        Route::prefix('logs')->name('logs.')->group(function () {
            Route::get('/', [SmsLogController::class, 'index'])->name('index');
            Route::post('/bulk-delete', [SmsLogController::class, 'bulkDelete'])->name('bulk_delete');
            Route::get('/show/{log}', [SmsLogController::class, 'show'])->name('show');
            Route::post('/check-status/{log}', [SmsLogController::class, 'checkStatus'])->name('check_status');
            Route::post('/resend/{log}', [SmsLogController::class, 'resend'])->name('resend');
            Route::get('/export-csv', [SmsLogController::class, 'exportCsv'])->name('export');
            Route::delete('/{log}', [SmsLogController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('blacklist')->name('blacklist.')->group(function () {
            Route::get('/', [SmsBlacklistController::class, 'index'])->name('index');
            Route::post('/store', [SmsBlacklistController::class, 'store'])->name('store');
            Route::match(['delete', 'post'], '/delete/{blacklist}', [SmsBlacklistController::class, 'destroy'])->name('destroy');
            Route::post('/toggle-status/{blacklist}', [SmsBlacklistController::class, 'toggleStatus'])->name('toggle_status');
        });
    });

    /* --- 6.21 Ecommerce & Banner Settings --- */
    Route::prefix('ecommerce-setting')->name('ecommerce.setting.')->group(function () {
        Route::get('/shipping-configuration', fn() => view('admin.modules.placeholder', ['title' => 'Shipping Configuration']))->name('shipping');
        Route::get('/company-information', fn() => view('admin.modules.placeholder', ['title' => 'Company Information']))->name('company');
    });

    Route::prefix('banner-setting')->name('banner.setting.')->group(function () {
        Route::get('/main-banner', fn() => view('admin.modules.placeholder', ['title' => 'Main Banner']))->name('main');
        Route::get('/campaign-banner', fn() => view('admin.modules.placeholder', ['title' => 'Campaign Banner']))->name('campaign');
        Route::get('/shop-banner', fn() => view('admin.modules.placeholder', ['title' => 'Shop Banner']))->name('shop');
    });

    /* --- 6.22 Frontend & Site Settings --- */
    Route::prefix('frontend-setting')->name('frontend.setting.')->group(function () {
        Route::get('/logo', fn() => view('admin.modules.placeholder', ['title' => 'Logo Settings']))->name('logo');
        Route::get('/favicon', fn() => view('admin.modules.placeholder', ['title' => 'Favicon Settings']))->name('favicon');
        Route::get('/about-us', fn() => view('admin.modules.placeholder', ['title' => 'About Us Settings']))->name('about');
        Route::get('/contact-us', fn() => view('admin.modules.placeholder', ['title' => 'Contact Us Settings']))->name('contact');
        Route::get('/faq', fn() => view('admin.modules.placeholder', ['title' => 'FAQ Settings']))->name('faq');
        Route::get('/custom-pages', fn() => view('admin.modules.placeholder', ['title' => 'Custom Pages']))->name('custompages');
        Route::get('/features', fn() => view('admin.modules.placeholder', ['title' => 'Features Settings']))->name('features');
        Route::get('/headline', fn() => view('admin.modules.placeholder', ['title' => 'Headline Marquee']))->name('headline');
        Route::get('/blogs', fn() => view('admin.modules.placeholder', ['title' => 'Blogs']))->name('blogs');
    });

    /* --- 6.23 Dynamic Theme Settings & Display Engine --- */
    Route::prefix('theme-setting')->name('theme.setting.')->group(function () {
        Route::get('/slider', [ThemeSettingController::class, 'sliderIndex'])->name('slider');
        Route::post('/slider/store', [ThemeSettingController::class, 'sliderStore'])->name('slider.store');
        Route::post('/slider/update/{id}', [ThemeSettingController::class, 'sliderUpdate'])->name('slider.update');
        Route::delete('/slider/delete/{id}', [ThemeSettingController::class, 'sliderDestroy'])->name('slider.destroy');
        Route::post('/slider/toggle/{id}', [ThemeSettingController::class, 'sliderToggle'])->name('slider.toggle');

        Route::get('/flash-sale', [ThemeSettingController::class, 'flashSaleIndex'])->name('flash_sale');
        Route::post('/flash-sale/update', [ThemeSettingController::class, 'flashSaleUpdate'])->name('flash_sale.update');
        Route::post('/offer-card/store', [ThemeSettingController::class, 'offerCardStore'])->name('offer_card.store');
        Route::post('/offer-card/update/{id}', [ThemeSettingController::class, 'offerCardUpdate'])->name('offer_card.update');
        Route::delete('/offer-card/delete/{id}', [ThemeSettingController::class, 'offerCardDestroy'])->name('offer_card.destroy');

        Route::get('/popular-categories', [ThemeSettingController::class, 'popularCategoriesIndex'])->name('popular_categories');
        Route::post('/popular-categories/update', [ThemeSettingController::class, 'popularCategoriesUpdate'])->name('popular_categories.update');

        Route::get('/topbar', [ThemeSettingController::class, 'topbarIndex'])->name('topbar');
        Route::post('/topbar/update', [ThemeSettingController::class, 'topbarUpdate'])->name('topbar.update');

        Route::get('/colors', [ThemeSettingController::class, 'themeColorsIndex'])->name('colors');
        Route::post('/colors/update', [ThemeSettingController::class, 'themeColorsUpdate'])->name('colors.update');
        Route::get('/extended-colors', [ThemeExtendedColorController::class, 'index'])->name('extended_colors');
        Route::post('/extended-colors/update', [ThemeExtendedColorController::class, 'update'])->name('extended_colors.update');

        Route::get('/footer', [ThemeSettingController::class, 'footerBuilderIndex'])->name('footer');
        Route::post('/footer/update', [ThemeSettingController::class, 'footerBuilderUpdate'])->name('footer.update');

        Route::get('/product-card', [ThemeSettingController::class, 'productCardViewIndex'])->name('product_card');
        Route::post('/product-card/update', [ThemeSettingController::class, 'productCardViewUpdate'])->name('product_card.update');

        Route::get('/cart-features', [ThemeSettingController::class, 'cartFeaturesIndex'])->name('cart_features');
        Route::post('/cart-features/update', [ThemeSettingController::class, 'cartFeaturesUpdate'])->name('cart_features.update');

        Route::get('/home-builder', [ThemeSettingController::class, 'homeBuilderIndex'])->name('home_builder');
        Route::post('/home-builder/store', [ThemeSettingController::class, 'homeBuilderStore'])->name('home_builder.store');
        Route::post('/home-builder/update/{id}', [ThemeSettingController::class, 'homeBuilderUpdate'])->name('home_builder.update');
        Route::delete('/home-builder/delete/{id}', [ThemeSettingController::class, 'homeBuilderDestroy'])->name('home_builder.destroy');

        Route::get('/featured-products', [ThemeSettingController::class, 'featuredProductsIndex'])->name('featured_products');
        Route::post('/featured-products/update', [ThemeSettingController::class, 'featuredProductsUpdate'])->name('featured_products.update');

        // Centralized Dynamic Display Engine
        Route::prefix('display')->name('display.')->group(function () {
            Route::get('/', [DynamicDisplayController::class, 'index'])->name('index');
            
            Route::get('/popups/create', [DynamicDisplayController::class, 'popupCreate'])->name('popup.create');
            Route::post('/popups/store', [DynamicDisplayController::class, 'popupStore'])->name('popup.store');
            Route::get('/popups/{id}/edit', [DynamicDisplayController::class, 'popupEdit'])->name('popup.edit');
            Route::post('/popups/{id}/update', [DynamicDisplayController::class, 'popupUpdate'])->name('popup.update');
            Route::post('/popups/{id}/toggle', [DynamicDisplayController::class, 'popupToggle'])->name('popup.toggle');
            Route::delete('/popups/{id}', [DynamicDisplayController::class, 'popupDestroy'])->name('popup.destroy');

            Route::post('/widgets/store', [DynamicDisplayController::class, 'widgetStore'])->name('widget.store');
            Route::post('/widgets/{id}/update', [DynamicDisplayController::class, 'widgetUpdate'])->name('widget.update');
            Route::post('/widgets/{id}/toggle', [DynamicDisplayController::class, 'widgetToggle'])->name('widget.toggle');
            Route::delete('/widgets/{id}', [DynamicDisplayController::class, 'widgetDestroy'])->name('widget.destroy');

            Route::post('/sticky-bar-rule', [DynamicDisplayController::class, 'updateStickyBarRule'])->name('sticky_bar_rule.update');
        });
    });

    /* --- 6.24 Admin Payment Gateway Settings --- */
    Route::prefix('payment-settings')->name('payment.')->group(function () {
        Route::get('/gateways', [PaymentSettingController::class, 'index'])->name('setting.gateways');
        Route::post('/manual/store', [PaymentSettingController::class, 'storeManual'])->name('store_manual');
        Route::post('/manual/{id}/update', [PaymentSettingController::class, 'updateManual'])->name('update_manual');
        Route::delete('/manual/{id}', [PaymentSettingController::class, 'destroyManual'])->name('destroy_manual');
        Route::post('/manual/{id}/toggle', [PaymentSettingController::class, 'toggleManual'])->name('toggle_manual');
        Route::post('/gateway/{id}/update', [PaymentSettingController::class, 'updateGateway'])->name('update_gateway');
        Route::post('/gateway/{id}/toggle', [PaymentSettingController::class, 'toggleGateway'])->name('toggle_gateway');
        Route::get('/manual-methods', [PaymentSettingController::class, 'index'])->name('setting.manual');
        Route::get('/manual-history', [PaymentTransactionController::class, 'checkoutRequests'])->name('setting.history');
    });

    /* --- 6.25 Roles & Staff Users (RBAC) --- */
    Route::prefix('user-roles')->name('roles.')->group(function () {
        Route::get('/roles', [RolePermissionController::class, 'index'])->name('index');
        Route::get('/roles/create', [RolePermissionController::class, 'create'])->name('create');
        Route::post('/roles/store', [RolePermissionController::class, 'store'])->name('store');
        Route::get('/roles/edit/{roleName}', [RolePermissionController::class, 'edit'])->name('edit');
        Route::delete('/roles/delete/{roleName}', [RolePermissionController::class, 'destroy'])->name('destroy');

        Route::get('/users', [StaffUserController::class, 'index'])->name('users');
        Route::post('/users/bulk-action', [StaffUserController::class, 'bulkAction'])->name('users.bulk_action');
        Route::get('/users/create-staff', [StaffUserController::class, 'createStaff'])->name('users.create_staff');
        Route::get('/users/create-customer', [StaffUserController::class, 'createCustomer'])->name('users.create_customer');
        Route::post('/users/store-staff', [StaffUserController::class, 'storeStaff'])->name('users.store_staff');
        Route::post('/users/store-customer', [StaffUserController::class, 'storeCustomer'])->name('users.store_customer');
        Route::get('/users/view/{id}', [StaffUserController::class, 'show'])->name('users.show');
        Route::get('/users/edit-staff/{id}', [StaffUserController::class, 'editStaff'])->name('users.edit_staff');
        Route::post('/users/update-staff/{id}', [StaffUserController::class, 'updateStaff'])->name('users.update_staff');
        Route::post('/users/update-status/{id}', [StaffUserController::class, 'updateStatus'])->name('users.update_status');
        Route::post('/users/reset-password/{id}', [StaffUserController::class, 'resetPassword'])->name('users.reset_password');
        Route::post('/users/force-logout/{id}', [StaffUserController::class, 'forceLogout'])->name('users.force_logout');
        Route::post('/users/unlock/{id}', [StaffUserController::class, 'unlock'])->name('users.unlock');
        Route::get('/document/download/{id}', [StaffUserController::class, 'downloadDoc'])->name('users.download_doc');
        Route::match(['delete', 'post'], '/users/delete/{id}', [StaffUserController::class, 'destroy'])->name('users.destroy');
        Route::get('/trash', [StaffUserController::class, 'trash'])->name('trash');
        Route::post('/trash/restore/{id}', [StaffUserController::class, 'restore'])->name('trash.restore');
        Route::post('/trash/force-delete/{id}', [StaffUserController::class, 'forceDelete'])->name('trash.force_delete');

        // Staff Salary History Link within RBAC Users Management
        Route::get('/users/salary-history/{id}', [PayrollController::class, 'staffSalaryHistory'])->name('users.salary_history');
    });

    /* --- 6.26 Activity Logs & User Sessions --- */
    Route::prefix('activity-logs')->name('activity.')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('logs');
        Route::get('/show/{id}', [ActivityLogController::class, 'show'])->name('show');
        Route::post('/cleanup', [ActivityLogController::class, 'cleanup'])->name('cleanup');
        Route::match(['delete', 'post'], '/destroy/{id}', [ActivityLogController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('user-sessions')->name('sessions.')->group(function () {
        Route::get('/', [SessionMonitorController::class, 'index'])->name('index');
        Route::post('/revoke/{id}', [SessionMonitorController::class, 'revoke'])->name('revoke');
        Route::post('/revoke-all/{userId}', [SessionMonitorController::class, 'revokeAll'])->name('revoke_all');
    });

    /* --- 6.27 Admin & Staff Profile --- */
    Route::prefix('my-profile')->name('profile.')->group(function () {
        Route::get('/', [AdminProfileController::class, 'index'])->name('index');
        Route::post('/update', [AdminProfileController::class, 'updateProfile'])->name('update');
        Route::post('/change-password', [AdminProfileController::class, 'updatePassword'])->name('password');
    });

    /* --- 6.28 Form Builder & Admin Submissions --- */
    Route::prefix('forms')->name('forms.')->group(function () {
        Route::get('/', [FormController::class, 'index'])->name('index');
        Route::get('/create', [FormController::class, 'create'])->name('create');
        Route::post('/', [FormController::class, 'store'])->name('store');
        Route::post('/{form}/fields', [FormFieldController::class, 'store'])->name('fields.store');
        Route::post('/{form}/fields/reorder', [FormFieldController::class, 'reorder'])->name('fields.reorder');
        Route::get('/{form}/edit', [FormController::class, 'edit'])->name('edit');
        Route::put('/{form}', [FormController::class, 'update'])->name('update');
        Route::put('/{form}/fields/{field}', [FormFieldController::class, 'update'])->name('fields.update');
        Route::delete('/{form}/fields/{field}', [FormFieldController::class, 'destroy'])->name('fields.destroy');
        Route::delete('/{form}', [FormController::class, 'destroy'])->name('destroy');
        Route::post('/{form}/fields/{field}/clone', [FormFieldController::class, 'clone'])->name('fields.clone');

        Route::get('/submissions', [FormController::class, 'submissions'])->name('submissions.index');
        Route::post('/submissions/bulk-reply', [FormController::class, 'bulkReplySubmissions'])->name('submissions.bulk_reply');
        Route::post('/submissions/bulk-status', [FormController::class, 'bulkSubmissionStatus'])->name('submissions.bulk_status');
        Route::match(['delete', 'post'], '/submissions/bulk-delete', [FormController::class, 'bulkSubmissionDestroy'])->name('submissions.bulk_destroy');
        Route::get('/submissions/{id}', [FormController::class, 'showSubmission'])->name('submissions.show');
        Route::post('/submissions/{id}/reply', [FormController::class, 'replySubmission'])->name('submissions.reply');
        Route::post('/submissions/{id}/block-ip', [FormController::class, 'blockIp'])->name('submissions.block_ip');
        Route::post('/submissions/{id}/status', [FormController::class, 'updateSubmissionStatus'])->name('submissions.status');
        Route::match(['delete', 'post'], '/submissions/{id}', [FormController::class, 'destroySubmission'])->name('submissions.destroy');
    });

    /* --- 6.29 Content Management (CMS) --- */
    Route::prefix('content')->name('content.')->group(function () {
        
        // Pages Management Routes
        Route::prefix('pages')->name('pages.')->group(function () {
            Route::get('/', [SiteContentController::class, 'pagesIndex'])->name('index');
            Route::get('/create', [SiteContentController::class, 'pagesCreate'])->name('create');
            Route::post('/', [SiteContentController::class, 'pagesStore'])->name('store');
            Route::get('/{id}/edit', [SiteContentController::class, 'pagesEdit'])->name('edit');
            Route::match(['put', 'post'], '/{id}', [SiteContentController::class, 'pagesUpdate'])->name('update');
            Route::delete('/{id}', [SiteContentController::class, 'pagesDestroy'])->name('destroy');
        });

        // Blog Posts & Comments Routes
        Route::prefix('blogs')->name('blogs.')->group(function () {
            Route::get('/', [SiteContentController::class, 'blogsIndex'])->name('index');
            Route::get('/create', [SiteContentController::class, 'blogsCreate'])->name('create');
            Route::post('/', [SiteContentController::class, 'blogsStore'])->name('store');
            Route::get('/{id}/edit', [SiteContentController::class, 'blogsEdit'])->name('edit');
            Route::match(['put', 'post'], '/{id}', [SiteContentController::class, 'blogsUpdate'])->name('update');
            Route::delete('/{id}', [SiteContentController::class, 'blogsDestroy'])->name('destroy');
            Route::post('/{id}/toggle-comments', [SiteContentController::class, 'blogsToggleComments'])->name('toggleComments');
            
            Route::get('/comments', [SiteContentController::class, 'blogCommentsIndex'])->name('comments');
            Route::post('/comment/{id}/status', [SiteContentController::class, 'blogCommentStatus'])->name('comment.status');
            Route::delete('/comment/{id}', [SiteContentController::class, 'blogCommentDestroy'])->name('comment.destroy');
            Route::post('/comment/{id}/reply', [SiteContentController::class, 'blogCommentReply'])->name('comment.reply');
            Route::post('/comments/toggle-auto-approve', [SiteContentController::class, 'blogToggleAutoApprove'])->name('comments.toggleAutoApprove');
        });

        // Menus Routes
        Route::prefix('menus')->name('menus.')->group(function () {
            Route::get('/', [SiteContentController::class, 'menusIndex'])->name('index');
            Route::get('/create', [SiteContentController::class, 'menusCreate'])->name('create');
            Route::post('/save', [SiteContentController::class, 'menusSave'])->name('save');
            Route::delete('/{id}', [SiteContentController::class, 'menusDelete'])->name('delete');
        });

        // Widgets Routes
        Route::prefix('widgets')->name('widgets.')->group(function () {
            Route::get('/', [SiteContentController::class, 'widgetsIndex'])->name('index');
            Route::post('/', [SiteContentController::class, 'widgetsStore'])->name('store');
            Route::post('/{id}/update', [SiteContentController::class, 'widgetsUpdate'])->name('update');
            Route::delete('/{id}', [SiteContentController::class, 'widgetsDestroy'])->name('destroy');
            Route::post('/{id}/toggle', [SiteContentController::class, 'widgetsToggle'])->name('toggle');
        });

        // Media Library Routes
        Route::prefix('media')->name('media.')->group(function () {
            Route::get('/', [AdminMediaController::class, 'index'])->name('index');
            Route::post('/upload', [AdminMediaController::class, 'upload'])->name('upload');
            Route::post('/{id}/update', [AdminMediaController::class, 'updateMeta'])->name('update');
            Route::delete('/{id}', [AdminMediaController::class, 'destroy'])->name('destroy');
        });
    });

    /* --- 6.30 General & Authentication Settings --- */
    Route::prefix('settings')->name('setting.')->group(function () {
        Route::get('/general', [SettingController::class, 'general'])->name('general');
        Route::post('/general/update', [SettingController::class, 'updateGeneral'])->name('general.update');
    });

    // Central Dynamic Authentication Settings Management Panel
    Route::prefix('settings/auth')->name('settings.auth.')->group(function () {
        Route::get('/', [AuthSettingController::class, 'index'])->name('index');
        Route::post('/', [AuthSettingController::class, 'update'])->name('update');
    });

    /* --- 6.31 System Settings (Courier, Balance, Webhooks & Fraud Check) --- */
    Route::prefix('system-settings')->name('system.setting.')->group(function () {
        Route::get('/smtp', fn() => view('admin.modules.placeholder', ['title' => 'SMTP Configuration']))->name('smtp');
        Route::get('/sms', fn() => redirect()->route('admin.sms.settings.index'))->name('sms');
        Route::get('/courier', [AdminCourierSettingController::class, 'index'])->name('courier');
        Route::post('/courier/{id}', [AdminCourierSettingController::class, 'update'])->name('courier.update');
        Route::post('/courier/balance/{id}', [AdminCourierSettingController::class, 'checkBalance'])->name('courier.balance');
        Route::get('/courier/track/{orderId}', [AdminCourierSettingController::class, 'trackOrder'])->name('courier.track');
        Route::get('/fraud-check', [AdminOrderController::class, 'fraudSetting'])->name('fraud');
        Route::post('/fraud-check/update', [AdminOrderController::class, 'updateFraudSetting'])->name('fraud.update');
        Route::get('/popup-notice', fn() => view('admin.modules.placeholder', ['title' => 'Popup Notice']))->name('popup');
    });

    Route::get('/seo-settings', fn() => view('admin.modules.placeholder', ['title' => 'SEO Meta Configuration']))->name('seo.index');
});

Route::get('/test-500', fn() => abort(500));

/*
|==========================================================================
| SECTION 7: UNIVERSAL SYMLINK & MEDIA SERVE FALLBACK
|==========================================================================
*/
$serveMediaFallback = function (string $relativePath) {
    $clean = ltrim(str_replace(['..', "\0", '\\'], ['', '', '/'], $relativePath), '/');
    $clean = preg_replace('#^(public_html/|public/|storage/storage/|uploads/uploads/)#i', '', $clean);

    if ($clean === '' || str_contains($clean, '..')) {
        abort(404);
    }

    $ext = strtolower(pathinfo($clean, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico', 'bmp', 'avif', 'pdf'];
    if (!in_array($ext, $allowedExtensions, true)) {
        abort(404);
    }

    $withoutStorage = preg_replace('#^storage/#i', '', $clean);
    $withoutUploads = preg_replace('#^uploads/#i', '', $withoutStorage);

    $docRoot = (!empty($_SERVER['DOCUMENT_ROOT']) && is_dir($_SERVER['DOCUMENT_ROOT']))
        ? rtrim($_SERVER['DOCUMENT_ROOT'], '/')
        : null;
    $cpanelRoot = is_dir(base_path('../public_html'))
        ? rtrim(base_path('../public_html'), '/')
        : null;

    $candidates = array_filter([
        $docRoot ? $docRoot . '/' . $clean : null,
        $docRoot ? $docRoot . '/uploads/' . $withoutUploads : null,
        $docRoot ? $docRoot . '/storage/' . $withoutStorage : null,
        $cpanelRoot ? $cpanelRoot . '/' . $clean : null,
        $cpanelRoot ? $cpanelRoot . '/uploads/' . $withoutUploads : null,
        $cpanelRoot ? $cpanelRoot . '/storage/' . $withoutStorage : null,
        public_path($clean),
        public_path('uploads/' . $withoutUploads),
        public_path('storage/' . $withoutStorage),
        storage_path('app/public/' . $withoutStorage),
        storage_path('app/public/' . $withoutUploads),
        storage_path('app/public/uploads/' . $withoutUploads),
        storage_path('app/' . $withoutStorage),
    ]);

    foreach ($candidates as $filePath) {
        if (is_file($filePath) && is_readable($filePath)) {
            return response()->file($filePath, [
                'Cache-Control' => 'public, max-age=2592000',
            ]);
        }
    }

    abort(404);
};

Route::get('/storage/{path}', fn($path) => $serveMediaFallback('storage/' . $path))->where('path', '.*');
Route::get('/uploads/{path}', fn($path) => $serveMediaFallback('uploads/' . $path))->where('path', '.*');
Route::get('/products/{path}', fn($path) => $serveMediaFallback('products/' . $path))->where('path', '.*');
Route::get('/sliders/{filename}', fn($filename) => $serveMediaFallback('sliders/' . $filename))->where('filename', '.*');
Route::get('/theme/offers/{filename}', fn($filename) => $serveMediaFallback('theme/offers/' . $filename))->where('filename', '.*');

// On-the-fly Dynamic Image Thumbnail Resizer
Route::get('/thumb/{path}', [\App\Http\Controllers\Admin\ImageThumbnailController::class, 'thumb'])
    ->where('path', '.*')
    ->name('image.thumb');
/*
|==========================================================================
| SECTION 8: PUBLIC DYNAMIC CATCH-ALL ROUTE
|==========================================================================
*/
Route::middleware(['throttle:custom-rate-limit'])->group(function () {
    Route::get('/{slug}', [PageController::class, 'show'])
        ->name('pages.show')
        ->where('slug', '^(?!admin|user|dashboard|cart|checkout|payment|api|wishlist|login|register|logout|auth|forgot-password|reset-password|blogs|shop|category|product|products|order|forms|digital-download|webhook|storage|uploads|sliders|theme|test-500|email|thumb).*$');
});