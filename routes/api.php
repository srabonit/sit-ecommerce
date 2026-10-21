<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Middleware\CheckPermission;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\PaymentSettingController;
use App\Http\Controllers\Api\OnlineOrderController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\ReturnController;
use App\Http\Controllers\Api\LoyaltyController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\NotificationSettingController;

/*
|--------------------------------------------------------------------------
| Public Routes (No Auth Token Required)
|--------------------------------------------------------------------------
*/

// Auth Routes for Customer & Staff
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'customerRegister']); // Customer Self Sign-up
    Route::post('/login', [AuthController::class, 'login']);               // Universal Login
});

// Public Product Browsing (E-Commerce View)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Public Category Browsing
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

// Public Sub-Category Browsing
Route::get('/sub-categories', [SubCategoryController::class, 'index']);
Route::get('/sub-categories/{id}', [SubCategoryController::class, 'show']);

// Public Brand Browsing
Route::get('/brands', [BrandController::class, 'index']);
Route::get('/brands/{id}', [BrandController::class, 'show']);

// Apply Coupon Code (Cart Validation)
Route::post('/coupons/apply', [CouponController::class, 'applyCoupon']);


/*
|--------------------------------------------------------------------------
| Protected Enterprise API Routes (Sanctum Auth Required)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum'])->group(function () {

    // Auth Profile & Staff Management
    Route::prefix('auth')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/logout', [AuthController::class, 'logout']);
        
        // Admin creates staff/branch users
        Route::post('/register-user', [AuthController::class, 'register'])
            ->middleware(CheckPermission::class . ':user.create');
    });

    // Inventory Management Routes
    Route::prefix('inventory')->group(function () {
        Route::get('/stocks', [InventoryController::class, 'index'])
            ->middleware(CheckPermission::class . ':inventory.view');
            
        Route::post('/adjust', [InventoryController::class, 'adjustStock'])
            ->middleware(CheckPermission::class . ':inventory.adjust');
            
        Route::post('/transfer', [InventoryController::class, 'transferStock'])
            ->middleware(CheckPermission::class . ':inventory.transfer');
            
        Route::get('/low-stock-alert', [InventoryController::class, 'lowStockAlert'])
            ->middleware(CheckPermission::class . ':inventory.view');
    });

    // Protected Product Management (Admin/Staff Actions)
    Route::prefix('products')->group(function () {
        Route::post('/', [ProductController::class, 'store'])
            ->middleware(CheckPermission::class . ':product.create');
        Route::post('/{id}', [ProductController::class, 'update'])
            ->middleware(CheckPermission::class . ':product.edit');
        Route::delete('/{id}', [ProductController::class, 'destroy'])
            ->middleware(CheckPermission::class . ':product.delete');
    });

    // Protected Category Management
    Route::prefix('categories')->group(function () {
        Route::post('/', [CategoryController::class, 'store'])
            ->middleware(CheckPermission::class . ':category.manage');
        Route::post('/{id}', [CategoryController::class, 'update'])
            ->middleware(CheckPermission::class . ':category.manage');
        Route::delete('/{id}', [CategoryController::class, 'destroy'])
            ->middleware(CheckPermission::class . ':category.manage');
    });

    // Protected SubCategory Management
    Route::prefix('sub-categories')->group(function () {
        Route::post('/', [SubCategoryController::class, 'store'])
            ->middleware(CheckPermission::class . ':category.manage');
        Route::post('/{id}', [SubCategoryController::class, 'update'])
            ->middleware(CheckPermission::class . ':category.manage');
        Route::delete('/{id}', [SubCategoryController::class, 'destroy'])
            ->middleware(CheckPermission::class . ':category.manage');
    });

    // Protected Brand Management
    Route::prefix('brands')->group(function () {
        Route::post('/', [BrandController::class, 'store'])
            ->middleware(CheckPermission::class . ':brand.manage');
        Route::post('/{id}', [BrandController::class, 'update'])
            ->middleware(CheckPermission::class . ':brand.manage');
        Route::delete('/{id}', [BrandController::class, 'destroy'])
            ->middleware(CheckPermission::class . ':brand.manage');
    });

    // POS Module Routes
    Route::prefix('pos')->group(function () {
        Route::post('/checkout', [PosController::class, 'checkout'])
            ->middleware(CheckPermission::class . ':pos.checkout');
    });

    // Dynamic Payment Configurations (Admin Panel)
    Route::prefix('payment-settings')->group(function () {
        Route::get('/gateways', [PaymentSettingController::class, 'getGateways']);
        Route::post('/gateways', [PaymentSettingController::class, 'saveGateway'])
            ->middleware(CheckPermission::class . ':user.create');

        Route::get('/manual-methods', [PaymentSettingController::class, 'getManualMethods']);
        Route::post('/manual-methods/{id?}', [PaymentSettingController::class, 'saveManualMethod'])
            ->middleware(CheckPermission::class . ':user.create');
    });

    // E-Commerce Authenticated Customer Orders
    Route::prefix('e-commerce')->group(function () {
        Route::post('/place-order', [OnlineOrderController::class, 'placeOrder']);
        Route::get('/my-orders', [OnlineOrderController::class, 'myOrders']);
    });

    // Reports & POS Receipt Routes
    Route::prefix('reports')->group(function () {
        Route::get('/pos-receipt/{invoice_no}', [ReportController::class, 'getReceiptData']);
        Route::get('/sales-summary', [ReportController::class, 'salesReport'])
            ->middleware(CheckPermission::class . ':inventory.view');
    });

    // Protected Coupon Management
    Route::prefix('coupons')->group(function () {
        Route::get('/', [CouponController::class, 'index'])
            ->middleware(CheckPermission::class . ':user.create');
        Route::post('/', [CouponController::class, 'store'])
            ->middleware(CheckPermission::class . ':user.create');
    });

    // Supplier & Purchase Order Routes
    Route::prefix('suppliers')->group(function () {
        Route::get('/', [SupplierController::class, 'index']);
        Route::post('/', [SupplierController::class, 'storeSupplier']);
        Route::post('/purchase-orders', [SupplierController::class, 'createPurchaseOrder']);
        Route::post('/purchase-orders/{id}/receive', [SupplierController::class, 'receivePurchaseOrder']);
    });

    // Return & Refund Routes
    Route::post('/returns/process', [ReturnController::class, 'processReturn']);

    // Loyalty Points Routes
    Route::prefix('loyalty')->group(function () {
        Route::get('/my-points', [LoyaltyController::class, 'getMyPoints']);
        Route::post('/redeem', [LoyaltyController::class, 'redeemPoints']);
    });

    // System Audit Logs (Super Admin Only)
    Route::get('/audit-logs', [AuditLogController::class, 'index'])
        ->middleware(CheckPermission::class . ':user.create');

    // Dynamic SMTP & SMS Gateway Settings (Admin Only)
    Route::prefix('notification-settings')->group(function () {
        Route::get('/', [NotificationSettingController::class, 'getSettings']);
        Route::post('/', [NotificationSettingController::class, 'saveSettings'])
            ->middleware(CheckPermission::class . ':user.create');
    });

});