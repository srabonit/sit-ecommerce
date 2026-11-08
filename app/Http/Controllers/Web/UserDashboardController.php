<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Services\Auth\AuthenticationSettingsService;
use App\Services\DiscountEngineService;
use App\Services\Sms\OtpService;
use App\Services\Sms\SmsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class UserDashboardController extends Controller
{
    protected OtpService $otpService;
    protected SmsService $smsService;
    protected AuthenticationSettingsService $authSettings;

    public function __construct(
        OtpService $otpService,
        SmsService $smsService,
        AuthenticationSettingsService $authSettings
    ) {
        $this->otpService = $otpService;
        $this->smsService = $smsService;
        $this->authSettings = $authSettings;
    }

    /**
     * Display user dashboard overview.
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        $userId = $user->id;

        $totalOrders = DB::table('orders')->where('user_id', $userId)->count();
        $activeOrders = DB::table('orders')
            ->where('user_id', $userId)
            ->whereIn('order_status', ['pending', 'processing', 'shipped'])
            ->count();

        $recentOrders = DB::table('orders')
            ->where('user_id', $userId)
            ->latest('id')
            ->take(3)
            ->get();

        $loyaltyPoints = $user->loyalty_points ?? 120;
        
        // Wishlist Count (DB & Session Sync)
        $wishlistCount = 0;
        if (Schema::hasTable('wishlists')) {
            $wishlistCount = DB::table('wishlists')->where('user_id', $userId)->count();
        } else {
            $wishlistCount = count(session()->get('wishlist', []));
        }

        return view('frontend.user.dashboard', compact(
            'user',
            'totalOrders',
            'activeOrders',
            'recentOrders',
            'loyaltyPoints',
            'wishlistCount'
        ));
    }

    /**
     * Display order history list.
     */
    public function orders(Request $request)
    {
        $userId = auth()->id();
        $query = DB::table('orders')->where('user_id', $userId);

        if ($request->filled('status')) {
            $filterStatus = $request->status;
            if ($filterStatus === 'delivered') {
                $query->whereIn('order_status', ['delivered', 'completed']);
            } else {
                $query->where('order_status', $filterStatus);
            }
        }

        $orders = $query->latest('id')->paginate(10)->withQueryString();

        return view('frontend.user.orders', compact('orders'));
    }

    /**
     * Display specific order details.
     */
    public function orderDetails($invoiceNo)
    {
        $order = DB::table('orders')
            ->where('invoice_no', $invoiceNo)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (in_array(strtolower($order->order_status), ['completed', 'delivered']) && strtolower($order->payment_status) === 'unpaid') {
            $updateFields = [
                'payment_status' => 'paid',
                'updated_at'     => now(),
            ];

            if (Schema::hasColumn('orders', 'paid_amount')) {
                $updateFields['paid_amount'] = $order->grand_total;
            }
            if (Schema::hasColumn('orders', 'due_amount')) {
                $updateFields['due_amount'] = 0;
            }

            DB::table('orders')->where('id', $order->id)->update($updateFields);
            $order->payment_status = 'paid';
        }

        $itemsQuery = DB::table('order_items')->where('order_id', $order->id);

        if (Schema::hasTable('products') && Schema::hasColumn('order_items', 'product_id')) {
            $itemsQuery->leftJoin('products', 'order_items.product_id', '=', 'products.id')
                ->select('order_items.*', 'products.title as product_table_title');
        }

        $orderItems = $itemsQuery->get();

        return view('frontend.user.order_details', compact('order', 'orderItems'));
    }

    /**
     * Real-time order tracker.
     */
    public function trackOrder(Request $request)
    {
        $trackedOrder = null;
        if ($request->filled('invoice_no')) {
            $trackedOrder = DB::table('orders')
                ->where('invoice_no', trim($request->invoice_no))
                ->first();
        }

        return view('frontend.user.track_order', compact('trackedOrder'));
    }

    /**
     * Display customer profile, verification status, and security settings.
     */
    public function profile()
    {
        $user = auth()->user();
        $twoFaConfig = $this->authSettings->getTwoFactorConfig();

        $activeSessions = collect();
        if (Schema::hasTable('active_user_sessions')) {
            $activeSessions = DB::table('active_user_sessions')
                ->where('user_id', $user->id)
                ->where('is_revoked', false)
                ->latest('last_activity')
                ->take(6)
                ->get();
        }

        return view('frontend.user.profile', compact('user', 'twoFaConfig', 'activeSessions'));
    }

    /**
     * Update customer basic profile data.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'   => 'required|string|max:255',
            'gender' => 'nullable|string|in:male,female,other',
        ]);

        $updateData = [
            'name'       => trim($request->name),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('users', 'gender') && $request->filled('gender')) {
            $updateData['gender'] = $request->gender;
        }

        DB::table('users')->where('id', $user->id)->update($updateData);

        return back()->with('success', 'Profile information updated successfully.');
    }

    /**
     * Update customer password with session preservation.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password does not match our records.']);
        }

        DB::table('users')->where('id', $user->id)->update([
            'password'   => Hash::make($request->new_password),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }

    /**
     * Step A: Request OTP to verify and update to a new phone number.
     */
    public function requestPhoneChangeOtp(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_phone'        => 'required|string|max:25',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password verification failed.',
            ], 422);
        }

        $formattedPhone = $this->smsService->formatNumber(trim($request->new_phone));
        $localPhone = preg_replace('/^880/', '0', $formattedPhone);

        // Check if phone already registered to another account
        $alreadyUsed = User::where('id', '!=', $user->id)
            ->where(function ($q) use ($formattedPhone, $localPhone) {
                $q->where('phone', $formattedPhone)
                  ->orWhere('phone', $localPhone);
            })->exists();

        if ($alreadyUsed) {
            return response()->json([
                'success' => false,
                'message' => 'This mobile number is already linked to another account.',
            ], 422);
        }

        $res = $this->otpService->sendOtp(
            $formattedPhone,
            'phone_change',
            $user->id,
            $request->ip(),
            $request->userAgent()
        );

        if (!$res['status']) {
            return response()->json([
                'success'          => false,
                'message'          => $res['message'],
                'cooldown_seconds' => $res['cooldown_seconds'] ?? 0,
            ], 422);
        }

        return response()->json([
            'success'          => true,
            'message'          => "Verification code dispatched to {$formattedPhone}.",
            'cooldown_seconds' => $res['cooldown_seconds'],
        ]);
    }

    /**
     * Step B: Verify OTP and commit new phone number to account.
     */
    public function confirmPhoneChange(Request $request): JsonResponse
    {
        $request->validate([
            'new_phone' => 'required|string',
            'otp_code'  => 'required|string|min:4|max:10',
        ]);

        $user = auth()->user();
        $formattedPhone = $this->smsService->formatNumber(trim($request->new_phone));

        $verify = $this->otpService->verifyOtp($formattedPhone, trim($request->otp_code), 'phone_change');

        if (!$verify['status']) {
            return response()->json([
                'success' => false,
                'message' => $verify['message'],
            ], 422);
        }

        $userModel = User::find($user->id);
        $userModel->phone = $formattedPhone;
        $userModel->phone_verified_at = now();
        $userModel->save();

        return response()->json([
            'success'   => true,
            'message'   => 'Phone number updated and verified successfully.',
            'new_phone' => $formattedPhone,
        ]);
    }

    /**
     * Step A: Request OTP to verify and update to a new email address.
     */
    public function requestEmailChangeOtp(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_email'        => 'required|email|max:255',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password verification failed.',
            ], 422);
        }

        $newEmail = strtolower(trim($request->new_email));

        $alreadyUsed = User::where('id', '!=', $user->id)
            ->where('email', $newEmail)
            ->exists();

        if ($alreadyUsed) {
            return response()->json([
                'success' => false,
                'message' => 'This email address is already linked to another account.',
            ], 422);
        }

        $res = $this->otpService->sendEmailOtp($newEmail, 'email_change', $user->id, $request->ip());

        if (!$res['status']) {
            return response()->json([
                'success'          => false,
                'message'          => $res['message'],
                'cooldown_seconds' => $res['cooldown_seconds'] ?? 0,
            ], 422);
        }

        return response()->json([
            'success'          => true,
            'message'          => "Verification passcode dispatched to {$newEmail}.",
            'cooldown_seconds' => $res['cooldown_seconds'],
        ]);
    }

    /**
     * Step B: Verify Email OTP and commit new email address to account.
     */
    public function confirmEmailChange(Request $request): JsonResponse
    {
        $request->validate([
            'new_email' => 'required|email',
            'otp_code'  => 'required|string|min:4|max:10',
        ]);

        $user = auth()->user();
        $newEmail = strtolower(trim($request->new_email));

        $verify = $this->otpService->verifyEmailOtp($newEmail, trim($request->otp_code), 'email_change');

        if (!$verify['status']) {
            return response()->json([
                'success' => false,
                'message' => $verify['message'],
            ], 422);
        }

        $userModel = User::find($user->id);
        $userModel->email = $newEmail;
        $userModel->email_verified_at = now();
        $userModel->save();

        return response()->json([
            'success'   => true,
            'message'   => 'Email address updated and verified successfully.',
            'new_email' => $newEmail,
        ]);
    }

    /**
     * Enable, Update Channel, or Disable Two-Factor Authentication (2FA).
     * Honors individual user choice (Personal Security) and respects mandatory global policy.
     */
    public function toggleTwoFactor(Request $request): JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        $enable = $request->boolean('enable');
        $channel = $request->input('two_factor_type', 'sms');

        $userModel = User::find($user->id);

        if ($enable) {
            $request->validate([
                'two_factor_type' => 'required|string|in:sms,email,both',
            ]);

            // Channel integrity verification
            if ($channel === 'both') {
                if (empty($userModel->phone) || empty($userModel->email)) {
                    $msg = 'To activate 2FA for Both SMS and Email, you must have both a phone number and an email linked.';
                    return $request->expectsJson() ? response()->json(['success' => false, 'message' => $msg], 422) : back()->with('error', $msg);
                }
            } elseif ($channel === 'sms' && empty($userModel->phone)) {
                $msg = 'Please link a verified mobile number before enabling SMS 2FA.';
                return $request->expectsJson() ? response()->json(['success' => false, 'message' => $msg], 422) : back()->with('error', $msg);
            } elseif ($channel === 'email' && empty($userModel->email)) {
                $msg = 'Please link a verified email address before enabling Email 2FA.';
                return $request->expectsJson() ? response()->json(['success' => false, 'message' => $msg], 422) : back()->with('error', $msg);
            }

            $userModel->two_factor_enabled = true;
            $userModel->two_factor_type = $channel;
            $userModel->two_factor_confirmed_at = now();
            $userModel->save();

            $msg = 'Two-factor authentication has been successfully updated (' . strtoupper($channel) . ').';
            return $request->expectsJson() ? response()->json(['success' => true, 'message' => $msg]) : back()->with('success', $msg);
        }

        // --- DISABLING 2FA ---

        // 1. Check if Globally Mandatory for this specific user's role
        $twoFaConfig = $this->authSettings->getTwoFactorConfig();
        if ($twoFaConfig['global_enabled'] && ($twoFaConfig['mode'] === 'mandatory')) {
            $role = str_replace(['-', ' '], '_', strtolower(trim((string)($user->role ?? ''))));
            $userType = str_replace(['-', ' '], '_', strtolower(trim((string)($user->user_type ?? ''))));

            $isSuperOrAdmin = !empty($user->is_super_admin) || in_array($role, ['super_admin', 'admin']) || $userType === 'admin';
            $staffRoles = ['branch_manager', 'staff', 'manager', 'cashier', 'pos_operator', 'operator', 'moderator', 'order_manager', 'courier_manager', 'inventory_manager', 'accounts'];
            $isStaff = in_array($role, $staffRoles) || $userType === 'staff';

            $strictlyEnforced = false;
            if ($isSuperOrAdmin && $twoFaConfig['for_admin']) $strictlyEnforced = true;
            elseif ($isStaff && $twoFaConfig['for_staff']) $strictlyEnforced = true;
            elseif (!$isSuperOrAdmin && !$isStaff && $twoFaConfig['for_customer']) $strictlyEnforced = true;

            if ($strictlyEnforced) {
                $msg = 'Two-factor authentication is mandatory by company security policy and cannot be disabled.';
                return $request->expectsJson() ? response()->json(['success' => false, 'message' => $msg], 403) : back()->with('error', $msg);
            }
        }

        // 2. Disabling 2FA requires password confirmation
        $request->validate(['current_password' => 'required|string']);
        if (!Hash::check($request->current_password, $user->password)) {
            $msg = 'Incorrect password entered. 2FA setting unchanged.';
            return $request->expectsJson() ? response()->json(['success' => false, 'message' => $msg], 422) : back()->withErrors(['current_password' => $msg]);
        }

        $userModel->two_factor_enabled = false;
        $userModel->two_factor_confirmed_at = null;
        $userModel->save();

        $msg = 'Two-factor authentication has been turned off.';
        return $request->expectsJson() ? response()->json(['success' => true, 'message' => $msg]) : back()->with('success', $msg);
    }

    /**
     * Terminate all active sessions on other devices.
     */
    public function logoutOtherDevices(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password entered. Action cancelled.']);
        }

        // Built-in Laravel logout other devices
        Auth::logoutOtherDevices($request->password);

        // Terminate database active user sessions if table exists
        if (Schema::hasTable('active_user_sessions')) {
            DB::table('active_user_sessions')
                ->where('user_id', $user->id)
                ->where('session_id', '!=', session()->getId())
                ->update(['is_revoked' => true]);
        }

        return back()->with('success', 'All other active device sessions have been successfully terminated.');
    }

    /**
     * Show Delete Account View (Publicly Accessible - Play Store Compliance)
     */
    public function showDeleteAccountPage()
    {
        $user = auth()->user();

        if ($user && in_array(strtolower($user->role), ['admin', 'superadmin', 'manager', 'staff'])) {
            return redirect()->route('user.dashboard')->with('error', 'Administrative accounts cannot be deleted from here. Please contact Super Admin.');
        }

        return view('frontend.user.delete_account', compact('user'));
    }

    /**
     * Customer Self-Account Deletion (30-day Trash Policy).
     * Works for both authenticated user and unauthenticated visitors.
     */
    public function deleteAccount(Request $request)
    {
        // 1. Jodi user already Sign In kora thake
        if (Auth::check()) {
            $user = auth()->user();

            $restrictedRoles = ['admin', 'superadmin', 'manager', 'staff'];
            if (in_array(strtolower($user->role), $restrictedRoles)) {
                return redirect()->route('user.dashboard')->with('error', 'Administrative accounts cannot be deleted.');
            }

            $request->validate([
                'password'         => 'required',
                'confirm_checkbox' => 'required',
            ], [
                'confirm_checkbox.required' => 'Please check the confirmation box to proceed.',
                'password.required'         => 'Account password is required for confirmation.'
            ]);

            if (!Hash::check($request->password, $user->password)) {
                return back()->withErrors(['password' => 'Incorrect password entered. Account deletion aborted.']);
            }

            // Soft Delete (Moves to Trash for 30 days)
            $userModel = User::find($user->id);
            $userModel->delete();

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('user.account.delete.page')->with('success', 'Your account has been moved to trash. You can restore it anytime within 30 days simply by logging in.');
        }

        // 2. Jodi user Sign In na thake (Guest form)
        $request->validate([
            'username_or_phone' => 'required|string',
            'email'             => 'required|email',
            'password'          => 'required|string',
            'confirm_checkbox'  => 'required',
        ], [
            'username_or_phone.required' => 'Please enter your username or registered phone number.',
            'email.required'             => 'Please enter your registered email address.',
            'password.required'          => 'Password is required to confirm identity.',
            'confirm_checkbox.required'  => 'Please confirm that you understand the 30-day trash policy.'
        ]);

        $identity = trim($request->username_or_phone);
        $email = strtolower(trim($request->email));

        $targetUser = User::where('email', $email)
            ->where(function ($q) use ($identity) {
                $q->where('phone', $identity)
                  ->orWhere('name', $identity);
            })
            ->first();

        if (!$targetUser) {
            return back()->withErrors(['username_or_phone' => 'No user found matching this Username/Phone and Email combination.'])->withInput();
        }

        $restrictedRoles = ['admin', 'superadmin', 'manager', 'staff'];
        if (in_array(strtolower($targetUser->role), $restrictedRoles)) {
            return back()->with('error', 'Administrative accounts cannot be deleted from this public page.');
        }

        if (!Hash::check($request->password, $targetUser->password)) {
            return back()->withErrors(['password' => 'Incorrect password entered. Account deletion verification failed.'])->withInput();
        }

        // Soft Delete (Moves to Trash for 30 days)
        $targetUser->delete();

        return redirect()->route('user.account.delete.page')->with('success', 'Your account has been successfully verified and moved to trash. All data will be permanently removed after 30 days.');
    }

    /**
     * Display saved address book.
     */
    public function addresses()
    {
        $user = auth()->user();
        return view('frontend.user.addresses', compact('user'));
    }

    /**
     * Update primary delivery address.
     */
    public function updateAddress(Request $request)
    {
        $request->validate([
            'address'  => 'required|string|max:500',
            'district' => 'required|string|max:100',
        ]);

        $updateData = ['updated_at' => now()];

        if (Schema::hasColumn('users', 'address')) {
            $updateData['address'] = $request->address;
        }

        if (Schema::hasColumn('users', 'district')) {
            $updateData['district'] = $request->district;
        }

        if (count($updateData) > 1) {
            DB::table('users')->where('id', auth()->id())->update($updateData);
        }

        session()->put('user_shipping_address', [
            'address'  => $request->address,
            'district' => $request->district
        ]);

        return back()->with('success', 'Primary shipping address updated.');
    }

    /**
     * Display user wishlist items with 20 items per page and sorting.
     */
    public function wishlist(Request $request)
    {
        $userId = auth()->id();
        $wishlistIds = [];

        if (!Schema::hasTable('wishlists')) {
            Schema::create('wishlists', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('product_id');
                $table->timestamps();
                $table->index(['user_id', 'product_id']);
            });
        }

        if (auth()->check()) {
            $sessionWishlist = session()->pull('wishlist', []);
            if (!empty($sessionWishlist)) {
                foreach ($sessionWishlist as $sId) {
                    $currCount = DB::table('wishlists')->where('user_id', $userId)->count();
                    if ($currCount < 100) {
                        DB::table('wishlists')->updateOrInsert(
                            ['user_id' => $userId, 'product_id' => (int)$sId],
                            ['created_at' => now(), 'updated_at' => now()]
                        );
                    }
                }
            }

            $wishlistIds = DB::table('wishlists')
                ->where('user_id', $userId)
                ->pluck('product_id')
                ->toArray();
        } else {
            $wishlistIds = session()->get('wishlist', []);
        }

        $query = Product::with(['category', 'brand'])->whereIn('id', $wishlistIds);

        $sort = $request->input('sort', 'latest');
        if ($sort === 'price_low') {
            $query->orderBy(DB::raw('COALESCE(sale_price, regular_price)'), 'asc');
        } elseif ($sort === 'price_high') {
            $query->orderBy(DB::raw('COALESCE(sale_price, regular_price)'), 'desc');
        } elseif ($sort === 'sale') {
            $query->whereNotNull('sale_price')->where('sale_price', '>', 0)->latest('id');
        } elseif ($sort === 'in_stock') {
            $query->where('stock', '>', 0)->latest('id');
        } else {
            $query->latest('id');
        }

        $wishlistProducts = $query->paginate(20)->withQueryString();

        return view('frontend.user.wishlist', compact('wishlistProducts'));
    }

    /**
     * Clear all wishlist products (1-Click Clear).
     */
    public function clearWishlist()
    {
        if (auth()->check() && Schema::hasTable('wishlists')) {
            DB::table('wishlists')->where('user_id', auth()->id())->delete();
        }

        session()->forget('wishlist');

        return response()->json([
            'status'  => true,
            'message' => 'All wishlist items have been removed.',
            'count'   => 0
        ]);
    }

    /**
     * Toggle Wishlist Item via AJAX with 100-item Limit & DB-Session Sync.
     */
    public function toggleWishlist($id)
    {
        $productId = (int) $id;
        $isLoggedIn = auth()->check();
        $userId = auth()->id();
        $isAdded = false;
        $count = 0;

        if (!Schema::hasTable('wishlists')) {
            Schema::create('wishlists', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('product_id');
                $table->timestamps();
                $table->index(['user_id', 'product_id']);
            });
        }

        if ($isLoggedIn) {
            $exists = DB::table('wishlists')
                ->where('user_id', $userId)
                ->where('product_id', $productId)
                ->exists();

            if ($exists) {
                DB::table('wishlists')
                    ->where('user_id', $userId)
                    ->where('product_id', $productId)
                    ->delete();

                $action = 'removed';
                $message = 'Product removed from wishlist!';
                $isAdded = false;
            } else {
                $currentCount = DB::table('wishlists')->where('user_id', $userId)->count();
                if ($currentCount >= 100) {
                    return response()->json([
                        'status'        => false,
                        'limit_reached' => true,
                        'message'       => 'Wishlist limit reached! You can add a maximum of 100 products to your wishlist.',
                        'count'         => $currentCount
                    ]);
                }

                DB::table('wishlists')->insert([
                    'user_id'    => $userId,
                    'product_id' => $productId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $action = 'added';
                $message = 'Product added to wishlist!';
                $isAdded = true;
            }

            $dbIds = DB::table('wishlists')->where('user_id', $userId)->pluck('product_id')->toArray();
            session()->put('wishlist', $dbIds);
            $count = count($dbIds);

        } else {
            $wishlist = session()->get('wishlist', []);

            if (in_array($productId, $wishlist)) {
                $wishlist = array_values(array_diff($wishlist, [$productId]));
                $action = 'removed';
                $message = 'Product removed from wishlist!';
                $isAdded = false;
            } else {
                if (count($wishlist) >= 100) {
                    return response()->json([
                        'status'        => false,
                        'limit_reached' => true,
                        'message'       => 'Wishlist limit reached! You can add a maximum of 100 products to your wishlist.',
                        'count'         => count($wishlist)
                    ]);
                }

                $wishlist[] = $productId;
                $wishlist = array_values(array_unique($wishlist));
                $action = 'added';
                $message = 'Product added to wishlist!';
                $isAdded = true;
            }

            session()->put('wishlist', $wishlist);
            $count = count($wishlist);
        }

        return response()->json([
            'status'   => true,
            'action'   => $action,
            'is_added' => $isAdded,
            'message'  => $message,
            'count'    => $count
        ]);
    }

    /**
     * Get live Wishlist counter for header.
     */
    public function getWishlistCount()
    {
        $count = 0;
        if (auth()->check() && Schema::hasTable('wishlists')) {
            $count = DB::table('wishlists')->where('user_id', auth()->id())->count();
        } else {
            $count = count(session()->get('wishlist', []));
        }
        return response()->json(['count' => $count]);
    }

    /**
     * Display customer coupons and voucher wallet with real-time target user filter.
     */
    public function coupons()
    {
        $userId = auth()->id();
        $now = Carbon::now();

        $myVouchers = collect();
        if ($userId && Schema::hasTable('customer_vouchers') && Schema::hasTable('vouchers')) {
            $myVouchers = DB::table('customer_vouchers')
                ->join('vouchers', 'customer_vouchers.voucher_id', '=', 'vouchers.id')
                ->where('customer_vouchers.user_id', $userId)
                ->select(
                    'customer_vouchers.*',
                    'vouchers.title as voucher_title',
                    'vouchers.voucher_type',
                    'vouchers.discount_amount as voucher_discount',
                    'vouchers.max_discount',
                    'vouchers.min_spend',
                    'vouchers.banner_image'
                )
                ->latest('customer_vouchers.id')
                ->get();
        }

        $claimableVouchers = collect();
        if (Schema::hasTable('vouchers')) {
            $claimedVoucherIds = ($userId && Schema::hasTable('customer_vouchers'))
                ? DB::table('customer_vouchers')->where('user_id', $userId)->pluck('voucher_id')->toArray()
                : [];

            $vQuery = DB::table('vouchers')
                ->where('is_active', 1)
                ->where(function ($q) use ($now) {
                    $q->whereNull('claim_start')->orWhere('claim_start', '<=', $now);
                })
                ->where(function ($q) use ($now) {
                    $q->whereNull('claim_end')->orWhere('claim_end', '>=', $now);
                });

            if (Schema::hasColumn('vouchers', 'target_user_type')) {
                $vQuery->where(function ($q) {
                    $q->whereNull('target_user_type')
                      ->orWhereIn('target_user_type', ['all', 'registered_only']);
                });
            }

            $claimableVouchers = $vQuery->get()->map(function ($v) use ($claimedVoucherIds) {
                $v->already_claimed = in_array($v->id, $claimedVoucherIds);
                $v->is_stock_out = (!is_null($v->total_vouchers_count) && $v->claimed_count >= $v->total_vouchers_count);
                return $v;
            });
        }

        $publicCoupons = collect();
        if (Schema::hasTable('coupons')) {
            $cQuery = DB::table('coupons')
                ->where('is_active', 1)
                ->where('is_public', 1)
                ->where(function ($q) use ($now) {
                    $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
                })
                ->where(function ($q) use ($now) {
                    $q->whereNull('expire_date')->orWhere('expire_date', '>=', $now);
                });

            if (Schema::hasColumn('coupons', 'target_user_type')) {
                $cQuery->where(function ($q) {
                    $q->whereNull('target_user_type')
                      ->orWhereIn('target_user_type', ['all', 'registered_only']);
                });
            }

            $publicCoupons = $cQuery->get();
        }

        return view('frontend.user.coupons', compact('myVouchers', 'claimableVouchers', 'publicCoupons'));
    }

    /**
     * Handle 1-Click Voucher Claim from Customer Dashboard (AJAX).
     */
    public function claimVoucher(Request $request)
    {
        $request->validate([
            'voucher_id' => 'required|integer',
        ]);

        $userId = auth()->id();
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to claim this voucher.',
            ], 401);
        }

        if (Schema::hasTable('vouchers') && Schema::hasColumn('vouchers', 'target_user_type')) {
            $vRecord = DB::table('vouchers')->where('id', (int)$request->voucher_id)->first();
            if ($vRecord && ($vRecord->target_user_type ?? '') === 'guest_only') {
                return response()->json([
                    'success' => false,
                    'message' => 'This voucher campaign is only available for guest customers.',
                ], 422);
            }
        }

        if (class_exists(DiscountEngineService::class)) {
            $result = DiscountEngineService::claimVoucher((int)$request->voucher_id, $userId);
            return response()->json($result);
        }

        return response()->json([
            'success' => false,
            'message' => 'Discount service engine is currently unavailable.',
        ], 500);
    }

    public function support()
    {
        return view('frontend.user.support');
    }

    /**
     * Handle support ticket submission, save to database, and dispatch email.
     */
    public function submitSupportTicket(Request $request)
    {
        $request->validate([
            'subject'    => 'required|string|max:255',
            'case_type'  => 'nullable|string|max:100',
            'invoice_no' => 'nullable|string|max:100',
            'message'    => 'required|string|max:2000',
        ]);

        $user = auth()->user();

        do {
            $ticketNo = 'TK-' . mt_rand(100000, 999999);
        } while (DB::table('support_tickets')->where('ticket_no', $ticketNo)->exists());

        $fullSubject = "[" . ($request->case_type ?? 'General') . "] " . $request->subject;
        if (!empty($request->invoice_no)) {
            $fullSubject .= " (Inv: " . $request->invoice_no . ")";
        }

        DB::table('support_tickets')->insert([
            'ticket_no'  => $ticketNo,
            'subject'    => $fullSubject,
            'user_name'  => $user->name ?? 'Guest User',
            'user_phone' => $user->phone ?? null,
            'status'     => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $ticketData = [
            'ticket_no'  => $ticketNo,
            'name'       => $user->name ?? 'Guest',
            'email'      => $user->email ?? 'N/A',
            'phone'      => $user->phone ?? 'N/A',
            'subject'    => $request->subject,
            'case_type'  => $request->case_type ?? 'General',
            'invoice_no' => $request->invoice_no ?? 'None',
            'content'    => $request->message,
        ];

        try {
            Mail::send([], [], function ($message) use ($ticketData) {
                $message->to('info.srabonit@gmail.com')
                    ->subject('🆘 Support Request: [' . $ticketData['ticket_no'] . '] ' . $ticketData['subject'])
                    ->html("
                        <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                            <h2 style='color: #2563eb;'>New Customer Support Inquiry ({$ticketData['ticket_no']})</h2>
                            <p><strong>Customer Name:</strong> {$ticketData['name']}</p>
                            <p><strong>Customer Email:</strong> {$ticketData['email']}</p>
                            <p><strong>Customer Phone:</strong> {$ticketData['phone']}</p>
                            <hr style='border: 0; border-top: 1px solid #e2e8f0;'>
                            <p><strong>Category / Case:</strong> {$ticketData['case_type']}</p>
                            <p><strong>Subject:</strong> {$ticketData['subject']}</p>
                            <p><strong>Invoice / Order Reference:</strong> {$ticketData['invoice_no']}</p>
                            <hr style='border: 0; border-top: 1px solid #e2e8f0;'>
                            <p><strong>Message / Problem Description:</strong></p>
                            <div style='background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #cbd5e1;'>
                                " . nl2br(e($ticketData['content'])) . "
                            </div>
                        </div>
                    ");
            });
        } catch (\Exception $e) {
            \Log::error('Support mail dispatch failed: ' . $e->getMessage());
        }

        return back()->with('success', "Support ticket ({$ticketNo}) submitted successfully! Our support team will respond shortly.");
    }
}