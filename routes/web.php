<?php

use Illuminate\Support\Facades\Route;

// Web Controllers
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

// Auth Controllers
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;

// Admin Controllers
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
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\SessionMonitorController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SiteContentController;
use App\Http\Controllers\Admin\PaymentSettingController;
use App\Http\Controllers\Admin\PaymentTransactionController;
use App\Http\Controllers\Admin\ThemeSettingController;
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

// Frontend Controllers
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\ProductReviewController;

// Public Store & Operations
Route::middleware(['throttle:custom-rate-limit'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/shop', [ProductController::class, 'index'])->name('shop');
    Route::get('/category/{slug}', [HomeController::class, 'categoryProducts'])->name('category.products');
    Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.details');
    Route::get('/ajax/quick-view/{id}', [ProductController::class, 'quickViewModal'])->name('product.quickview');

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

    // Wishlist
    Route::post('/wishlist/toggle/{id}', [UserDashboardController::class, 'toggleWishlist'])->name('wishlist.toggle');
    Route::get('/wishlist/count', [UserDashboardController::class, 'getWishlistCount'])->name('wishlist.count');
    Route::post('/wishlist/clear', [UserDashboardController::class, 'clearWishlist'])->name('wishlist.clear');

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/delivery-charge', [CheckoutController::class, 'getDeliveryCharge'])->name('checkout.deliveryCharge');
    Route::post('/checkout/process', [CheckoutController::class, 'processOrder'])->name('checkout.process');
    Route::get('/order/confirmation/{invoiceNo}', [CheckoutController::class, 'confirmation'])->name('order.confirmation');

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
        Route::get('/addresses', [UserDashboardController::class, 'addresses'])->name('addresses');
        Route::post('/addresses/update', [UserDashboardController::class, 'updateAddress'])->name('addresses.update');
        Route::get('/wishlist', [UserDashboardController::class, 'wishlist'])->name('wishlist');
        Route::get('/coupons', [UserDashboardController::class, 'coupons'])->name('coupons');
        Route::get('/support', [UserDashboardController::class, 'support'])->name('support');
        Route::post('/support/submit', [UserDashboardController::class, 'submitSupportTicket'])->name('support.submit');

        // Product Reviews
        Route::post('/product/{id}/review', [ProductReviewController::class, 'store'])->name('product.review.store');
        Route::put('/product/review/{id}', [ProductReviewController::class, 'update'])->name('product.review.update');
        Route::delete('/product/review/{id}', [ProductReviewController::class, 'destroy'])->name('product.review.destroy');
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

// Public Dynamic Form Submission
Route::post('/forms/{form}/submit', [FormSubmissionController::class, 'submit'])
    ->name('forms.submit')
    ->middleware(['throttle:10,1', FormSubmissionRateLimit::class]);

// Payment Gateways
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

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth-limiter');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth-limiter');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email')->middleware('throttle:auth-limiter');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update')->middleware('throttle:auth-limiter');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Admin Auth
Route::prefix('dashboard/auth')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'showLogin'])->name('login');
    Route::post('/', [AdminDashboardController::class, 'login'])->name('login.submit')->middleware('throttle:auth-limiter');
    Route::post('/logout', [AdminDashboardController::class, 'logout'])->name('logout');
});

// Public Automated Courier Webhook Route
Route::post('/webhook/courier/{provider}', [AdminCourierSettingController::class, 'handleWebhook'])->name('courier.webhook');

// Admin Panel Routes (Protected by Auth, Admin Gate, and Granular CheckPermission)
Route::middleware(['auth', 'admin', CheckPermission::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/search-ajax', [AdminDashboardController::class, 'ajaxGlobalSearch'])->name('search.ajax');

    // Central Global Media Library Routes (Site Media + User Uploads Control)
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

    // Branch Management Hubs
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
    });

    // Contact
    Route::prefix('contact')->name('contact.')->group(function () {
        Route::get('/customer', fn() => view('admin.modules.placeholder', ['title' => 'Customer List']))->name('customer');
        Route::get('/supplier', fn() => view('admin.modules.placeholder', ['title' => 'Supplier List']))->name('supplier');
    });

    // Product Management
    Route::prefix('product')->name('product.')->group(function () {
        Route::post('/category/quick-store-ajax', [AdminCategoryController::class, 'quickStoreAjax'])->name('category.quick_store');
        Route::get('/category', [AdminCategoryController::class, 'index'])->name('category');
        Route::post('/category/store', [AdminCategoryController::class, 'store'])->name('category.store');
        Route::get('/category/edit/{id}', [AdminCategoryController::class, 'edit'])->name('category.edit');
        Route::post('/category/update/{id}', [AdminCategoryController::class, 'update'])->name('category.update');
        Route::delete('/category/delete/{id}', [AdminCategoryController::class, 'destroy'])->name('category.destroy');
        Route::post('/category/toggle-status/{id}', [AdminCategoryController::class, 'toggleStatus'])->name('category.toggle.status');

        Route::post('/brand/quick-store-ajax', [AdminBrandController::class, 'quickStoreAjax'])->name('brand.quick_store');
        Route::get('/brand', [AdminBrandController::class, 'index'])->name('brand');
        Route::post('/brand/store', [AdminBrandController::class, 'store'])->name('brand.store');
        Route::get('/brand/edit/{id}', [AdminBrandController::class, 'edit'])->name('brand.edit');
        Route::post('/brand/update/{id}', [AdminBrandController::class, 'update'])->name('brand.update');
        Route::delete('/brand/delete/{id}', [AdminBrandController::class, 'destroy'])->name('brand.destroy');
        Route::post('/brand/toggle-status/{id}', [AdminBrandController::class, 'toggleStatus'])->name('brand.toggle.status');

        Route::get('/tags/most-used', [AdminProductController::class, 'getMostUsedTagsAjax'])->name('tags.most_used');
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
    });

    // =========================================================================
    // COMPREHENSIVE INVENTORY MANAGEMENT SYSTEM ROUTES
    // =========================================================================
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

        // AJAX Product & Variant Search for Stock In / Out Forms
        Route::get('/search-products-ajax', [InventoryController::class, 'ajaxSearchProducts'])->name('search.products.ajax');
    });

    // Adjustment Modal-e variant load korar AJAX API
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

    // Support Requests & Reviews
    Route::prefix('support-requests')->name('support.')->group(function () {
        Route::get('/', [AdminSupportController::class, 'index'])->name('index');
        Route::get('/view/{id}', [AdminSupportController::class, 'show'])->name('show');
        Route::post('/reply/{id}', [AdminSupportController::class, 'replyTicket'])->name('reply');
    });
    Route::get('/reviews', fn() => view('admin.modules.placeholder', ['title' => 'Customer Reviews']))->name('review.index');

    // Purchase
    Route::prefix('purchase')->name('purchase.')->group(function () {
        Route::get('/all', fn() => redirect()->route('admin.inventory.stock_in'))->name('index');
        Route::get('/add', fn() => redirect()->route('admin.inventory.stock_in'))->name('create');
    });

    // POS Terminal Module
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::get('/terminal/{branch_id}', [PosController::class, 'terminal'])
            ->name('terminal')
            ->middleware(EnsureBranchAccess::class);
        Route::get('/filter-products', [PosController::class, 'filterProductsAjax'])->name('filter.products');
        Route::get('/scan-barcode-ajax', [PosController::class, 'scanBarcodeAjax'])->name('scan.barcode');
        Route::get('/search-customers-ajax', [PosController::class, 'searchCustomersAjax'])->name('search.customers');
        Route::post('/create-customer-ajax', [PosController::class, 'createCustomerAjax'])->name('customer.create');
        Route::post('/place-order', [PosController::class, 'placeOrder'])->name('order.place');
        Route::get('/print-pos-receipt/{id}', [PosController::class, 'printPosThermal'])->name('print.thermal');
        Route::get('/print-a5-memo/{id}', [PosController::class, 'printA5Memo'])->name('print.a5');
    });

    // Advanced Courier Management Module
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

    // Shipping Management
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

    // Orders Management
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

        Route::get('/create-manual', [AdminOrderController::class, 'createManual'])->name('create_manual');
        Route::post('/store-manual', [AdminOrderController::class, 'storeManual'])->name('store_manual');
        Route::get('/search-products-ajax', [AdminOrderController::class, 'searchProductsAjax'])->name('search_products');
        Route::get('/search-customers-ajax', [AdminOrderController::class, 'searchCustomersAjax'])->name('search_customers');
        Route::post('/process-return-exchange/{id}', [AdminOrderController::class, 'processReturnExchange'])->name('process_return_exchange');
    });

    // =========================================================================
    // ADVANCED PAYMENT TRANSACTION MANAGEMENT & VERIFICATION SYSTEM
    // =========================================================================
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

    // Promo & Campaigns
    Route::prefix('promo')->name('promo.')->group(function () {
        Route::get('/flash-sale', fn() => view('admin.modules.placeholder', ['title' => 'Flash Sale']))->name('flashsale');
        Route::get('/coupons', fn() => view('admin.modules.placeholder', ['title' => 'Coupons']))->name('coupon');
    });

    // Accounts & General Cash Flow Ledger
    Route::prefix('accounts')->name('accounts.')->group(function () {
        Route::get('/payment-method', [PaymentSettingController::class, 'index'])->name('methods');
        Route::get('/transactions', [PaymentTransactionController::class, 'index'])->name('transactions');

        // Unified General Account Ledger
        Route::prefix('ledger')->name('ledger.')->group(function () {
            Route::get('/', [AccountLedgerController::class, 'index'])->name('index');
            Route::get('/export-csv', [AccountLedgerController::class, 'exportCsv'])->name('export');
        });
        Route::get('/ledger', [AccountLedgerController::class, 'index'])->name('ledger');

        // Operational Expenses Engine (Explicit Fixed Named Routes)
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('/expenses/store', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::match(['delete', 'post'], '/expenses/delete/{id}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
        Route::post('/expenses/category/store', [ExpenseController::class, 'storeCategory'])->name('expenses.category.store');
        Route::get('/expenses/export-csv', [ExpenseController::class, 'exportCsv'])->name('expenses.export');
        Route::get('/expenses-manage', [ExpenseController::class, 'index'])->name('expenses');

        // Bank Accounts & Liquid Cash Engine (Step 10)
        Route::prefix('bank-balance')->name('bank.')->group(function () {
            Route::get('/', [BankAccountController::class, 'index'])->name('index');
            Route::post('/account/store', [BankAccountController::class, 'storeAccount'])->name('account.store');
            Route::post('/transfer', [BankAccountController::class, 'transferBalance'])->name('transfer');
            Route::post('/deposit', [BankAccountController::class, 'deposit'])->name('deposit');
            Route::get('/export-csv', [BankAccountController::class, 'exportCsv'])->name('export');
            Route::get('/statement/{id}', [BankAccountController::class, 'statement'])->name('statement');
        });
        Route::get('/bank-balance', [BankAccountController::class, 'index'])->name('bank');

        // Business Inflow / Other Income Engine (Step 11)
        Route::prefix('income')->name('income.')->group(function () {
            Route::get('/', [IncomeController::class, 'index'])->name('index');
            Route::post('/store', [IncomeController::class, 'store'])->name('store');
            Route::match(['delete', 'post'], '/delete/{id}', [IncomeController::class, 'destroy'])->name('destroy');
            Route::post('/category/store', [IncomeController::class, 'storeCategory'])->name('category.store');
            Route::get('/export-csv', [IncomeController::class, 'exportCsv'])->name('export');
        });
        Route::get('/income', [IncomeController::class, 'index'])->name('income');

        // Capital & Owner Investment Engine (Step 12)
        Route::prefix('investment')->name('investment.')->group(function () {
            Route::get('/', [InvestmentController::class, 'index'])->name('index');
            Route::post('/store', [InvestmentController::class, 'store'])->name('store');
            Route::match(['delete', 'post'], '/delete/{id}', [InvestmentController::class, 'destroy'])->name('destroy');
            Route::get('/export-csv', [InvestmentController::class, 'exportCsv'])->name('export');
        });
        Route::get('/investment', [InvestmentController::class, 'index'])->name('investment');

        // Fixed Assets Management Engine (Step 13)
        Route::prefix('assets')->name('assets.')->group(function () {
            Route::get('/', [AssetController::class, 'index'])->name('index');
            Route::post('/store', [AssetController::class, 'store'])->name('store');
            Route::match(['delete', 'post'], '/delete/{id}', [AssetController::class, 'destroy'])->name('destroy');
            Route::get('/export-csv', [AssetController::class, 'exportCsv'])->name('export');
        });
        Route::get('/assets', [AssetController::class, 'index'])->name('assets');
    });

    // Reports & Financial Analytics (P&L, COGS, Balance Sheet, Cash Flow)
    Route::prefix('report')->name('report.')->group(function () {
        Route::get('/stock', fn() => redirect()->route('admin.inventory.balance'))->name('stock');
        Route::get('/sales', fn() => redirect()->route('admin.inventory.reports'))->name('sales');
        Route::get('/purchase', fn() => redirect()->route('admin.inventory.movements', ['movement_type' => 'purchase_in']))->name('purchase');
        Route::get('/profit-loss', [FinancialReportController::class, 'profitLoss'])->name('profit_loss');
        Route::get('/cogs-analysis', [FinancialReportController::class, 'cogsAnalysis'])->name('cogs');
        Route::get('/export-pnl', [FinancialReportController::class, 'exportPnl'])->name('export_pnl');

        // Comprehensive Balance Sheet
        Route::get('/balance-sheet', [FinancialReportController::class, 'balanceSheet'])->name('balance_sheet');
        Route::get('/export-balance-sheet', [FinancialReportController::class, 'exportBalanceSheet'])->name('export_balance_sheet');

        // Comprehensive Statement of Cash Flows
        Route::get('/cash-flow', [FinancialReportController::class, 'cashFlow'])->name('cash_flow');
        Route::get('/export-cash-flow', [FinancialReportController::class, 'exportCashFlow'])->name('export_cash_flow');
    });

    // =========================================================================
    // ADVANCED EMAIL MARKETING SYSTEM ROUTES
    // =========================================================================
    Route::prefix('email-marketing')->name('email_marketing.')->group(function () {
        // 1. Dashboard
        Route::get('/dashboard', [EmailMarketingController::class, 'dashboard'])->name('dashboard');

        // 2. All Subscribers & Instant Bulk Email Dispatch
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

        // 3. Audience Segments (Full Live AJAX, Recalculate, Bulk & Export Routes)
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
        // Aliases for Segments
        Route::get('/segments', [EmailMarketingController::class, 'segmentsIndex'])->name('segments');

        // 4. Campaigns
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

        // 5. Email Template Builder
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

        // 6. Automation & Workflows
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

        // 7. Product-Based Marketing & Targeted Audience
        Route::get('/product-marketing', [EmailMarketingController::class, 'productMarketing'])->name('product_marketing');
        Route::match(['get', 'post'], '/campaigns/create-product-targeted', [EmailMarketingController::class, 'createProductCampaign'])->name('campaigns.create_product_targeted');
        Route::match(['get', 'post'], '/product-marketing/create-campaign', [EmailMarketingController::class, 'createProductCampaign'])->name('product_marketing.create_campaign');

        // 8. Cart & Wishlist Marketing (Bulletproof Route Names & Aliases)
        Route::prefix('cart-wishlist')->name('cart_wishlist.')->group(function () {
            Route::get('/', [EmailMarketingController::class, 'cartWishlistMarketing'])->name('index');
            Route::post('/send-recovery/{id}', [EmailMarketingController::class, 'sendCartRecoveryDirect'])->name('send_recovery');
            Route::post('/bulk-delete', [EmailMarketingController::class, 'cartWishlistBulkDelete'])->name('bulk_delete');
            Route::post('/bulk-send', [EmailMarketingController::class, 'cartWishlistBulkSend'])->name('bulk_send');
            Route::delete('/delete/{id}', [EmailMarketingController::class, 'cartWishlistDestroy'])->name('destroy');
            Route::post('/delete/{id}', [EmailMarketingController::class, 'cartWishlistDestroy']);
            Route::get('/export-csv', [EmailMarketingController::class, 'cartWishlistExport'])->name('export');
        });
        // Explicit Direct Aliases (Prevents RouteNotFoundException for any legacy calls)
        Route::get('/cart-wishlist', [EmailMarketingController::class, 'cartWishlistMarketing'])->name('cart_wishlist_marketing');
        Route::get('/cart-wishlist-all', [EmailMarketingController::class, 'cartWishlistMarketing'])->name('cart_wishlist');
        Route::post('/cart-wishlist/send-recovery/{id}', [EmailMarketingController::class, 'sendCartRecoveryDirect'])->name('cart_wishlist_marketing.send_direct');
        Route::post('/cart-wishlist/send-recovery-alt/{id}', [EmailMarketingController::class, 'sendCartRecoveryDirect']);

        // 9. Customer Groups
        Route::get('/customer-groups', [EmailMarketingController::class, 'customerGroups'])->name('customer_groups');

        // 10. Scheduled Emails
        Route::get('/scheduled', [EmailMarketingController::class, 'scheduledCampaigns'])->name('scheduled');

        // 11. Email Queue & Delivery Logs
        Route::get('/queue-logs', [EmailMarketingController::class, 'queueLogs'])->name('queue_logs');

        // 12. Email Analytics & Reports
        Route::get('/analytics', [EmailMarketingController::class, 'analytics'])->name('analytics');
        Route::get('/analytics/export', [EmailMarketingController::class, 'analyticsExport'])->name('analytics.export');

        // 13. A/B Testing
        Route::get('/ab-testing', [EmailMarketingController::class, 'abTestingIndex'])->name('ab_testing');
        Route::post('/ab-testing/store', [EmailMarketingController::class, 'abTestingStore'])->name('ab_testing.store');

        // 14. Coupons & Promotions
        Route::get('/coupons', [EmailMarketingController::class, 'couponsIndex'])->name('coupons');
        Route::post('/coupons/campaign', [EmailMarketingController::class, 'createCouponCampaign'])->name('coupons.campaign');
        Route::post('/coupons/create-campaign', [EmailMarketingController::class, 'createCouponCampaign'])->name('coupons.create_campaign');

        // 15. Suppression & Unsubscribe List
        Route::get('/suppression', [EmailMarketingController::class, 'suppressionIndex'])->name('suppression');
        Route::post('/suppression/store', [EmailMarketingController::class, 'suppressionStore'])->name('suppression.store');
        Route::delete('/suppression/delete/{id}', [EmailMarketingController::class, 'suppressionDestroy'])->name('suppression.destroy');

        // 16. SMTP & Email Provider Settings
        Route::get('/providers', [EmailMarketingController::class, 'providersIndex'])->name('providers');
        Route::post('/providers/store', [EmailMarketingController::class, 'providerStore'])->name('providers.store');
        Route::match(['put', 'post'], '/providers/update/{id}', [EmailMarketingController::class, 'providerUpdate'])->name('providers.update');
        Route::match(['put', 'post'], '/providers/{id}', [EmailMarketingController::class, 'providerUpdate']);
        Route::match(['delete', 'post'], '/providers/delete/{id}', [EmailMarketingController::class, 'providerDestroy'])->name('providers.destroy');
        Route::match(['delete', 'post'], '/providers/{id}', [EmailMarketingController::class, 'providerDestroy']);
        Route::post('/providers/make-active/{id}', [EmailMarketingController::class, 'providerMakeActive'])->name('providers.make_active');
        Route::post('/providers/test-connection', [EmailMarketingController::class, 'providerTestConnection'])->name('providers.test_connection');
        Route::post('/providers/test', [EmailMarketingController::class, 'providerTestConnection'])->name('providers.test');

        // 17. Tracking & Consent Settings
        Route::get('/tracking-settings', [EmailMarketingController::class, 'trackingSettings'])->name('tracking_settings');
        Route::post('/tracking-settings/update', [EmailMarketingController::class, 'updateTrackingSettings'])->name('tracking_settings.update');

        // 18. General Marketing Settings
        Route::get('/settings', [EmailMarketingController::class, 'settingsIndex'])->name('settings');
        Route::post('/settings/update', [EmailMarketingController::class, 'updateSettings'])->name('settings.update');
    });

    // Ecommerce Settings
    Route::prefix('ecommerce-setting')->name('ecommerce.setting.')->group(function () {
        Route::get('/shipping-configuration', fn() => view('admin.modules.placeholder', ['title' => 'Shipping Configuration']))->name('shipping');
        Route::get('/company-information', fn() => view('admin.modules.placeholder', ['title' => 'Company Information']))->name('company');
    });

    // Banner Settings
    Route::prefix('banner-setting')->name('banner.setting.')->group(function () {
        Route::get('/main-banner', fn() => view('admin.modules.placeholder', ['title' => 'Main Banner']))->name('main');
        Route::get('/campaign-banner', fn() => view('admin.modules.placeholder', ['title' => 'Campaign Banner']))->name('campaign');
        Route::get('/shop-banner', fn() => view('admin.modules.placeholder', ['title' => 'Shop Banner']))->name('shop');
    });

    // Frontend Settings
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

    // Theme Settings
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
    });

    // Payment Settings
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

    // Roles & Staff Users (RBAC) - with bulkAction
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
    });

    // Live Audit Trail & Activity Logs
    Route::prefix('activity-logs')->name('activity.')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('logs');
        Route::get('/show/{id}', [ActivityLogController::class, 'show'])->name('show');
        Route::post('/cleanup', [ActivityLogController::class, 'cleanup'])->name('cleanup');
        Route::match(['delete', 'post'], '/destroy/{id}', [ActivityLogController::class, 'destroy'])->name('destroy');
    });

    // Live User Session Monitoring
    Route::prefix('user-sessions')->name('sessions.')->group(function () {
        Route::get('/', [SessionMonitorController::class, 'index'])->name('index');
        Route::post('/revoke/{id}', [SessionMonitorController::class, 'revoke'])->name('revoke');
        Route::post('/revoke-all/{userId}', [SessionMonitorController::class, 'revokeAll'])->name('revoke_all');
    });

    // Admin & Staff Account Profile System
    Route::prefix('my-profile')->name('profile.')->group(function () {
        Route::get('/', [AdminProfileController::class, 'index'])->name('index');
        Route::post('/update', [AdminProfileController::class, 'updateProfile'])->name('update');
        Route::post('/change-password', [AdminProfileController::class, 'updatePassword'])->name('password');
    });

    // Form Builder
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
    });

    // Content Management (Pages, Blogs, Menus, Widgets, Media)
    Route::prefix('content')->name('content.')->group(function () {
        // Pages
        Route::get('/pages', [SiteContentController::class, 'pagesIndex'])->name('pages.index');
        Route::get('/pages/create', [SiteContentController::class, 'pagesCreate'])->name('pages.create');
        Route::post('/pages', [SiteContentController::class, 'pagesStore'])->name('pages.store');
        Route::get('/pages/{id}/edit', [SiteContentController::class, 'pagesEdit'])->name('pages.edit');
        Route::match(['put', 'post'], '/pages/{id}', [SiteContentController::class, 'pagesUpdate'])->name('pages.update');
        Route::delete('/pages/{id}', [SiteContentController::class, 'pagesDestroy'])->name('pages.destroy');

        // Blogs
        Route::get('/blogs', [SiteContentController::class, 'blogsIndex'])->name('blogs.index');
        Route::get('/blogs/create', [SiteContentController::class, 'blogsCreate'])->name('blogs.create');
        Route::post('/blogs', [SiteContentController::class, 'blogsStore'])->name('blogs.store');
        Route::get('/blogs/{id}/edit', [SiteContentController::class, 'blogsEdit'])->name('blogs.edit');
        Route::match(['put', 'post'], '/blogs/{id}', [SiteContentController::class, 'blogsUpdate'])->name('blogs.update');
        Route::delete('/blogs/{id}', [SiteContentController::class, 'blogsDestroy'])->name('blogs.destroy');
        Route::post('/blogs/{id}/toggle-comments', [SiteContentController::class, 'blogsToggleComments'])->name('blogs.toggleComments');
        
        // Blog Comments Moderation
        Route::get('/blogs/comments', [SiteContentController::class, 'blogCommentsIndex'])->name('blogs.comments');
        Route::post('/blogs/comment/{id}/status', [SiteContentController::class, 'blogCommentStatus'])->name('blogs.comment.status');
        Route::delete('/blogs/comment/{id}', [SiteContentController::class, 'blogCommentDestroy'])->name('blogs.comment.destroy');
        Route::post('/blogs/comment/{id}/reply', [SiteContentController::class, 'blogCommentReply'])->name('blogs.comment.reply');
        Route::post('/blogs/comments/toggle-auto-approve', [SiteContentController::class, 'blogToggleAutoApprove'])->name('blogs.comments.toggleAutoApprove');

        // Menus
        Route::get('/menus', [SiteContentController::class, 'menusIndex'])->name('menus.index');
        Route::get('/menus/create', [SiteContentController::class, 'menusCreate'])->name('menus.create');
        Route::post('/menus/save', [SiteContentController::class, 'menusSave'])->name('menus.save');
        Route::delete('/menus/{id}', [SiteContentController::class, 'menusDelete'])->name('menus.delete');

        // Widgets
        Route::get('/widgets', [SiteContentController::class, 'widgetsIndex'])->name('widgets.index');
        Route::post('/widgets', [SiteContentController::class, 'widgetsStore'])->name('widgets.store');
        Route::post('/widgets/{id}/update', [SiteContentController::class, 'widgetsUpdate'])->name('widgets.update');
        Route::delete('/widgets/{id}', [SiteContentController::class, 'widgetsDestroy'])->name('widgets.destroy');
        Route::post('/widgets/{id}/toggle', [SiteContentController::class, 'widgetsToggle'])->name('widgets.toggle');

        // Media Library
        Route::get('/media', [AdminMediaController::class, 'index'])->name('media.index');
        Route::post('/media/upload', [AdminMediaController::class, 'upload'])->name('media.upload');
        Route::post('/media/{id}/update', [AdminMediaController::class, 'updateMeta'])->name('update');
        Route::delete('/media/{id}', [AdminMediaController::class, 'destroy'])->name('destroy');
    });

    // General & System Settings
    Route::prefix('settings')->name('setting.')->group(function () {
        Route::get('/general', [SettingController::class, 'general'])->name('general');
        Route::post('/general/update', [SettingController::class, 'updateGeneral'])->name('general.update');
    });

    // System Settings (Courier, Balance, Track, Webhook & Fraud Check)
    Route::prefix('system-settings')->name('system.setting.')->group(function () {
        Route::get('/smtp', fn() => view('admin.modules.placeholder', ['title' => 'SMTP Configuration']))->name('smtp');
        Route::get('/sms', fn() => view('admin.modules.placeholder', ['title' => 'SMS Configuration']))->name('sms');
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

// Universal Symlink Fallback
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

// Public Dynamic Catch-All Route
Route::middleware(['throttle:custom-rate-limit'])->group(function () {
    Route::get('/{slug}', [PageController::class, 'show'])
        ->name('pages.show')
        ->where('slug', '^(?!admin|user|dashboard|cart|checkout|payment|api|wishlist|login|register|logout|blogs|shop|category|product|products|order|forms|digital-download|webhook|storage|uploads|sliders|theme|test-500|email).*$');
});