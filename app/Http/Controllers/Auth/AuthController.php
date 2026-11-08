<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\AuthenticationSettingsService;
use App\Services\Sms\OtpService;
use App\Services\Sms\SmsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    protected AuthenticationSettingsService $authSettings;
    protected OtpService $otpService;
    protected SmsService $smsService;

    public function __construct(
        AuthenticationSettingsService $authSettings,
        OtpService $otpService,
        SmsService $smsService
    ) {
        $this->authSettings = $authSettings;
        $this->otpService = $otpService;
        $this->smsService = $smsService;
    }

    /**
     * User internal staff, manager ya admin hai ya nahi check karne ka helper
     */
    protected function isStaffOrAdmin($user): bool
    {
        if (!$user) {
            return false;
        }

        // 1. Super Admin check
        if (!empty($user->is_super_admin) || strtolower(trim((string)$user->role)) === 'super_admin') {
            return true;
        }

        // 2. Space aur Underscore dono formats ko normalize karna
        $role = str_replace(' ', '_', strtolower(trim((string)($user->role ?? ''))));

        $staffRoles = [
            'admin',
            'super_admin',
            'branch_manager',
            'staff',
            'manager',
            'cashier',
            'pos_operator',
            'operator',
            'moderator',
            'order_manager',
            'courier_manager',
            'inventory_manager',
            'accounts'
        ];

        if (in_array($role, $staffRoles)) {
            return true;
        }

        // 3. user_type check
        $userType = str_replace(' ', '_', strtolower(trim((string)($user->user_type ?? ''))));
        if (in_array($userType, ['admin', 'staff'])) {
            return true;
        }

        // 4. role_route_permissions table me dynamic role check
        if (Schema::hasTable('role_route_permissions')) {
            $hasRole = DB::table('role_route_permissions')
                ->where('role_name', $user->role)
                ->orWhere('role_name', $role)
                ->exists();

            if ($hasRole) {
                return true;
            }
        }

        return false;
    }

    /**
     * Display Login Screen with dynamic active tabs and settings.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->isStaffOrAdmin(Auth::user())
                ? redirect()->route('admin.dashboard')
                : redirect()->route('user.dashboard');
        }

        $settings = $this->authSettings->all();
        $enabledMethods = $this->authSettings->getEnabledLoginMethods();
        $defaultMethod = $this->authSettings->getDefaultLoginMethod();

        return view('auth.login', compact('settings', 'enabledMethods', 'defaultMethod'));
    }

    /**
     * Handle Standard Credentials (Password) Login with Multi-Method Guard & 2FA.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $loginInput = trim($request->input('login'));
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);
        $loginField = $isEmail ? 'email' : 'phone';

        // Normalize phone number if phone login
        $cleanPhone = null;
        $localPhone = null;
        $last10 = null;

        if (!$isEmail) {
            $cleanDigits = preg_replace('/[^0-9]/', '', $loginInput);
            $last10 = strlen($cleanDigits) >= 10 ? substr($cleanDigits, -10) : $cleanDigits;
            $cleanPhone = $this->smsService->formatNumber($loginInput);
            $localPhone = '0' . $last10;
        }

        // Check if user is staff/admin before applying customer restrictions
        $targetUserQuery = User::withTrashed();
        if ($isEmail) {
            $targetUserQuery->where('email', strtolower($loginInput));
        } else {
            $targetUserQuery->where(function ($q) use ($cleanPhone, $localPhone, $loginInput, $last10) {
                $q->where('phone', $cleanPhone)
                  ->orWhere('phone', $localPhone)
                  ->orWhere('phone', $loginInput)
                  ->orWhere('phone', 'like', '%' . $last10);
            });
        }
        $preUser = $targetUserQuery->first();

        $isStaff = $preUser ? $this->isStaffOrAdmin($preUser) : false;

        // Customer login method disabled check (Staff/Admin bypasses this safeguard)
        if (!$isStaff) {
            if ($isEmail && !$this->authSettings->isLoginMethodEnabled('email_password')) {
                return $this->respondLoginFailed($request, 'Email and password sign-in is currently disabled by administrator.');
            }
            if (!$isEmail && !$this->authSettings->isLoginMethodEnabled('phone_password')) {
                return $this->respondLoginFailed($request, 'Phone number and password sign-in is currently disabled. Please use OTP or another active method.');
            }
        }

        // 30 din se zyada purane trashed accounts ko permanently delete karna
        User::onlyTrashed()
            ->where('deleted_at', '<=', now()->subDays(30))
            ->forceDelete();

        // 30 din ke andar deleted user ka account reactivate karna
        if ($preUser && $preUser->trashed() && Hash::check($request->input('password'), $preUser->password)) {
            $preUser->restore();
            $preUser->update(['is_active' => 1]);

            return $this->completeSuccessfulLogin($request, $preUser, 'Welcome back! Your deactivated account has been restored.');
        }

        // Match exact DB credentials
        $dbField = $isEmail ? 'email' : 'phone';
        $dbValue = $preUser ? $preUser->{$dbField} : ($isEmail ? strtolower($loginInput) : ($cleanPhone ?? $loginInput));

        $credentials = [
            $dbField   => $dbValue,
            'password' => $request->input('password'),
        ];

        $remember = $request->boolean('remember');

        if (Auth::validate($credentials)) {
            $user = $preUser ?: User::where($dbField, $dbValue)->first();

            // Account status validation
            if (!empty($user->is_locked) || (isset($user->is_active) && !$user->is_active)) {
                return $this->respondLoginFailed($request, 'Your account is temporarily locked or deactivated. Please contact support.');
            }

            // Check if Two-Factor Authentication (2FA) is required (Personal Opt-in OR Global Policy)
            if ($this->shouldChallengeTwoFactor($user)) {
                return $this->initiateTwoFactorChallenge($request, $user, $remember);
            }

            // Standard Login Completion
            Auth::login($user, $remember);
            return $this->handlePostLoginRedirect($request, $user);
        }

        return $this->respondLoginFailed($request, 'These credentials do not match our records.');
    }

    /**
     * Request SMS OTP for passwordless login.
     */
    public function sendLoginOtp(Request $request): JsonResponse|RedirectResponse
    {
        if (!$this->authSettings->isLoginMethodEnabled('phone_otp')) {
            return response()->json([
                'success' => false,
                'message' => 'Phone OTP login is currently disabled by administrator.',
            ], 403);
        }

        $request->validate([
            'phone' => 'required|string|max:25',
        ]);

        $rawPhone = trim($request->phone);
        $cleanDigits = preg_replace('/[^0-9]/', '', $rawPhone);
        $last10 = strlen($cleanDigits) >= 10 ? substr($cleanDigits, -10) : $cleanDigits;
        $formattedPhone = $this->smsService->formatNumber($rawPhone);
        $localPhone = '0' . $last10;

        // Find existing user by phone across all formats
        $user = User::where(function ($q) use ($rawPhone, $formattedPhone, $localPhone, $last10) {
            $q->where('phone', $formattedPhone)
              ->orWhere('phone', $localPhone)
              ->orWhere('phone', $rawPhone)
              ->orWhere('phone', 'like', '%' . $last10);
        })->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'This mobile number is not registered. Please create an account first.',
            ], 404);
        }

        if (!empty($user->is_locked) || (isset($user->is_active) && !$user->is_active)) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is temporarily restricted. Please contact support.',
            ], 403);
        }

        $otpResult = $this->otpService->sendOtp(
            $formattedPhone,
            'login',
            $user->id,
            $request->ip(),
            $request->userAgent()
        );

        if (!$otpResult['status']) {
            return response()->json([
                'success'          => false,
                'message'          => $otpResult['message'],
                'cooldown_seconds' => $otpResult['cooldown_seconds'] ?? 0,
            ], 422);
        }

        return response()->json([
            'success'            => true,
            'message'            => $otpResult['message'],
            'cooldown_seconds'   => $otpResult['cooldown_seconds'],
            'expires_in_seconds' => $otpResult['expires_in_seconds'],
        ]);
    }

    /**
     * Verify submitted SMS OTP and complete login.
     */
    public function verifyLoginOtp(Request $request): JsonResponse|RedirectResponse
    {
        if (!$this->authSettings->isLoginMethodEnabled('phone_otp')) {
            return response()->json([
                'success' => false,
                'message' => 'Phone OTP login is currently disabled.',
            ], 403);
        }

        $request->validate([
            'phone'    => 'required|string',
            'otp_code' => 'required|string|min:4|max:10',
        ]);

        $rawPhone = trim($request->phone);
        $cleanDigits = preg_replace('/[^0-9]/', '', $rawPhone);
        $last10 = strlen($cleanDigits) >= 10 ? substr($cleanDigits, -10) : $cleanDigits;
        $formattedPhone = $this->smsService->formatNumber($rawPhone);
        $localPhone = '0' . $last10;

        $verifyResult = $this->otpService->verifyOtp($formattedPhone, trim($request->otp_code), 'login');

        if (!$verifyResult['status']) {
            return response()->json([
                'success'            => false,
                'message'            => $verifyResult['message'],
                'remaining_attempts' => $verifyResult['remaining_attempts'] ?? 0,
            ], 422);
        }

        $user = User::withTrashed()->where(function ($q) use ($rawPhone, $formattedPhone, $localPhone, $last10) {
            $q->where('phone', $formattedPhone)
              ->orWhere('phone', $localPhone)
              ->orWhere('phone', $rawPhone)
              ->orWhere('phone', 'like', '%' . $last10);
        })->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User account not found.',
            ], 404);
        }

        if ($user->trashed()) {
            $user->restore();
            $user->update(['is_active' => 1]);
        }

        if (!empty($user->is_locked) || (isset($user->is_active) && !$user->is_active)) {
            return response()->json([
                'success' => false,
                'message' => 'Account is deactivated or locked.',
            ], 403);
        }

        // Verify phone status if not verified
        if (method_exists($user, 'hasVerifiedPhone') && !$user->hasVerifiedPhone()) {
            $user->markPhoneAsVerified();
        }

        // Two-Factor Authentication Check for Phone OTP Login
        if ($this->shouldChallengeTwoFactor($user)) {
            return $this->initiateTwoFactorChallenge($request, $user, $request->boolean('remember'));
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $redirectUrl = $this->isStaffOrAdmin($user) ? route('admin.dashboard') : route('user.dashboard');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'message'      => 'Signed in successfully!',
                'redirect_url' => $redirectUrl,
            ]);
        }

        return redirect()->intended($redirectUrl);
    }

    /**
     * Request Email OTP for login.
     */
    public function sendEmailLoginOtp(Request $request): JsonResponse
    {
        if (!$this->authSettings->isLoginMethodEnabled('email_otp')) {
            return response()->json([
                'success' => false,
                'message' => 'Email OTP sign-in is disabled by administrator.',
            ], 403);
        }

        $request->validate(['email' => 'required|email']);
        $email = strtolower(trim($request->email));

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this email address.',
            ], 404);
        }

        if (!empty($user->is_locked) || (isset($user->is_active) && !$user->is_active)) {
            return response()->json([
                'success' => false,
                'message' => 'Account is restricted.',
            ], 403);
        }

        $res = $this->otpService->sendEmailOtp($email, 'login', $user->id, $request->ip());

        return response()->json([
            'success'          => $res['status'],
            'message'          => $res['message'],
            'cooldown_seconds' => $res['cooldown_seconds'] ?? 60,
        ], $res['status'] ? 200 : 422);
    }

    /**
     * Verify Email OTP and login.
     */
    public function verifyEmailLoginOtp(Request $request): JsonResponse
    {
        if (!$this->authSettings->isLoginMethodEnabled('email_otp')) {
            return response()->json(['success' => false, 'message' => 'Email OTP login disabled.'], 403);
        }

        $request->validate([
            'email'    => 'required|email',
            'otp_code' => 'required|string',
        ]);

        $email = strtolower(trim($request->email));
        $res = $this->otpService->verifyEmailOtp($email, trim($request->otp_code), 'login');

        if (!$res['status']) {
            return response()->json(['success' => false, 'message' => $res['message']], 422);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Account not found.'], 404);
        }

        if (is_null($user->email_verified_at)) {
            $user->email_verified_at = now();
            $user->save();
        }

        // Two-Factor Authentication Check for Email OTP Login
        if ($this->shouldChallengeTwoFactor($user)) {
            return $this->initiateTwoFactorChallenge($request, $user, $request->boolean('remember'));
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $redirectUrl = $this->isStaffOrAdmin($user) ? route('admin.dashboard') : route('user.dashboard');

        return response()->json([
            'success'      => true,
            'message'      => 'Signed in successfully!',
            'redirect_url' => $redirectUrl,
        ]);
    }

    /**
     * Two-Factor Authentication (2FA) Evaluation.
     * Evaluates personal choice & global policy.
     */
    public function shouldChallengeTwoFactor(User $user): bool
    {
        return $this->authSettings->isTwoFactorRequiredForUser($user);
    }

    /**
     * Resolves channels with strict priority:
     * 1. If Global 2FA is Mandatory for this role -> Follow Admin Settings Dispatch Channel (Email / SMS / Both)
     * 2. If Global 2FA is Optional -> Follow User's Personal Choice from profile[cite: 54]
     * 3. Intelligent fallback if a user has missing phone or missing email[cite: 54]
     */
    public function resolveUser2FaChannels(User $user): array
    {
        $hasPhone = !empty($user->phone);
        $hasEmail = !empty($user->email);

        $twoFaConfig = $this->authSettings->getTwoFactorConfig();
        $isGlobalMandatory = !empty($twoFaConfig['global_enabled']) && (($twoFaConfig['mode'] ?? '') === 'mandatory');

        $role = str_replace(['-', ' '], '_', strtolower(trim((string)($user->role ?? ''))));
        $userType = str_replace(['-', ' '], '_', strtolower(trim((string)($user->user_type ?? ''))));

        $isSuperOrAdmin = !empty($user->is_super_admin) || in_array($role, ['super_admin', 'admin']) || $userType === 'admin';
        $staffRoles = ['branch_manager', 'staff', 'manager', 'cashier', 'pos_operator', 'operator', 'moderator', 'order_manager', 'courier_manager', 'inventory_manager', 'accounts'];
        $isStaff = in_array($role, $staffRoles) || $userType === 'staff';

        $isMandatoryForRole = false;
        if ($isGlobalMandatory) {
            if ($isSuperOrAdmin && !empty($twoFaConfig['for_admin'])) {
                $isMandatoryForRole = true;
            } elseif ($isStaff && !empty($twoFaConfig['for_staff'])) {
                $isMandatoryForRole = true;
            } elseif (!$isSuperOrAdmin && !$isStaff && !empty($twoFaConfig['for_customer'])) {
                $isMandatoryForRole = true;
            }
        }

        $adminChannel = strtolower(trim((string)($twoFaConfig['primary_type'] ?? 'sms')));
        $userPersonalChannel = !empty($user->two_factor_type) ? strtolower(trim((string)$user->two_factor_type)) : null;

        if ($isMandatoryForRole) {
            // Admin Global Mandatory takes supreme priority
            $preferred = $adminChannel;
            if (!empty($user->two_factor_enabled) && $userPersonalChannel === 'both') {
                $preferred = 'both';
            }
        } elseif (!empty($user->two_factor_enabled) && !empty($userPersonalChannel)) {
            // User enabled personally on their profile
            $preferred = $userPersonalChannel;
        } else {
            // Default fallback
            $preferred = $adminChannel;
        }

        $channels = [];

        if ($preferred === 'both' || $preferred === 'sms_and_email') {
            if ($hasPhone) $channels[] = 'sms';
            if ($hasEmail) $channels[] = 'email';
        } elseif ($preferred === 'email') {
            if ($hasEmail) {
                $channels[] = 'email';
            } elseif ($hasPhone) {
                $channels[] = 'sms'; // Smart fallback if user has no email
            }
        } else {
            // Default: SMS
            if ($hasPhone) {
                $channels[] = 'sms';
            } elseif ($hasEmail) {
                $channels[] = 'email'; // Smart fallback if user has no phone
            }
        }

        if (empty($channels)) {
            if ($hasEmail) $channels[] = 'email';
            elseif ($hasPhone) $channels[] = 'sms';
        }

        return $channels;
    }

    /**
     * Intermediate 2FA Challenge Dispatch (Supports SMS, Email, Both & Smart Fallback).
     */
    public function initiateTwoFactorChallenge(Request $request, User $user, bool $remember): RedirectResponse|JsonResponse
    {
        $channels = $this->resolveUser2FaChannels($user);

        // Failsafe: jodi user-er phone/email konotai na thake tobe direct login
        if (empty($channels)) {
            Auth::login($user, $remember);
            return $this->handlePostLoginRedirect($request, $user);
        }

        $request->session()->put('auth_2fa_user_id', $user->id);
        $request->session()->put('auth_2fa_remember', $remember);
        $request->session()->put('auth_2fa_channels', $channels);

        // Dispatch 2FA Codes
        if (in_array('sms', $channels) && !empty($user->phone)) {
            $this->otpService->sendOtp($user->phone, 'two_factor', $user->id, $request->ip(), $request->userAgent());
        }

        if (in_array('email', $channels) && !empty($user->email)) {
            $this->otpService->sendEmailOtp($user->email, 'two_factor', $user->id, $request->ip());
        }

        $channelType = (in_array('sms', $channels) && in_array('email', $channels)) 
            ? 'both' 
            : (in_array('email', $channels) ? 'email' : 'sms');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'two_factor'   => true,
                'channel'      => $channelType,
                'redirect_url' => route('auth.2fa.challenge'),
            ]);
        }

        return redirect()->route('auth.2fa.challenge')->with('info', 'Please enter your two-factor security verification code.');
    }

    /**
     * Show 2FA Challenge Page with dynamic masked targets.
     */
    public function showTwoFactorChallenge(Request $request): View|RedirectResponse
    {
        $userId = $request->session()->get('auth_2fa_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login');
        }

        $channels = $request->session()->get('auth_2fa_channels', $this->resolveUser2FaChannels($user));

        $maskedPhone = !empty($user->phone) 
            ? substr($user->phone, 0, 4) . '••••' . substr($user->phone, -3) 
            : null;

        $maskedEmail = !empty($user->email) 
            ? substr($user->email, 0, 3) . '••••@' . (explode('@', $user->email)[1] ?? '') 
            : null;

        if (in_array('sms', $channels) && in_array('email', $channels)) {
            $channel = 'both';
            $maskedTarget = "Mobile ({$maskedPhone}) & Email ({$maskedEmail})";
        } elseif (in_array('email', $channels)) {
            $channel = 'email';
            $maskedTarget = $maskedEmail;
        } else {
            $channel = 'sms';
            $maskedTarget = $maskedPhone;
        }

        return view('auth.two_factor_challenge', compact('user', 'channel', 'maskedTarget'));
    }

    /**
     * Verify 2FA code (Supports code verification from either Phone, Email, or Both).
     */
    public function verifyTwoFactor(Request $request): RedirectResponse|JsonResponse
    {
        $userId = $request->session()->get('auth_2fa_user_id');
        if (!$userId) {
            return redirect()->route('login')->withErrors(['login' => 'Session expired. Please sign in again.']);
        }

        $request->validate(['code' => 'required|string|min:4|max:10']);

        $user = User::findOrFail($userId);
        $channels = $request->session()->get('auth_2fa_channels', $this->resolveUser2FaChannels($user));
        $code = trim($request->code);

        $isValid = false;
        $errMsg = 'Invalid or expired verification code.';

        // Check Email code if email channel is active
        if (in_array('email', $channels) && !empty($user->email)) {
            $verifyEmail = $this->otpService->verifyEmailOtp($user->email, $code, 'two_factor');
            if ($verifyEmail['status'] ?? false) {
                $isValid = true;
            } else {
                $errMsg = $verifyEmail['message'] ?? $errMsg;
            }
        }

        // Check SMS code if SMS channel is active or fallback
        if (!$isValid && in_array('sms', $channels) && !empty($user->phone)) {
            $verifySms = $this->otpService->verifyOtp($user->phone, $code, 'two_factor');
            if ($verifySms['status'] ?? false) {
                $isValid = true;
            } else {
                $errMsg = $verifySms['message'] ?? $errMsg;
            }
        }

        if (!$isValid) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $errMsg], 422);
            }
            return back()->withErrors(['code' => $errMsg]);
        }

        $remember = (bool) $request->session()->pull('auth_2fa_remember', false);
        $request->session()->forget(['auth_2fa_user_id', 'auth_2fa_channels']);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return $this->handlePostLoginRedirect($request, $user);
    }

    /**
     * Resend 2FA code across active channels.
     */
    public function resendTwoFactorOtp(Request $request): JsonResponse
    {
        $userId = $request->session()->get('auth_2fa_user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please log in again.'], 401);
        }

        $user = User::findOrFail($userId);
        $channels = $request->session()->get('auth_2fa_channels', $this->resolveUser2FaChannels($user));

        $cooldown = 60;
        $anySuccess = false;
        $lastMsg = 'Failed to resend code.';

        if (in_array('email', $channels) && !empty($user->email)) {
            $res = $this->otpService->sendEmailOtp($user->email, 'two_factor', $user->id, $request->ip());
            if ($res['status'] ?? false) {
                $anySuccess = true;
                $cooldown = $res['cooldown_seconds'] ?? $cooldown;
                $lastMsg = $res['message'];
            }
        }

        if (in_array('sms', $channels) && !empty($user->phone)) {
            $res = $this->otpService->sendOtp($user->phone, 'two_factor', $user->id, $request->ip(), $request->userAgent());
            if ($res['status'] ?? false) {
                $anySuccess = true;
                $cooldown = $res['cooldown_seconds'] ?? $cooldown;
                $lastMsg = $res['message'];
            }
        }

        if ($anySuccess) {
            return response()->json([
                'success'          => true,
                'message'          => 'New verification code has been dispatched.',
                'cooldown_seconds' => $cooldown,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $lastMsg,
        ], 422);
    }

    /**
     * Display Registration Form with dynamic validation flags.
     */
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return $this->isStaffOrAdmin(Auth::user())
                ? redirect()->route('admin.dashboard')
                : redirect()->route('user.dashboard');
        }

        $settings = $this->authSettings->all();

        return view('auth.register', compact('settings'));
    }

    /**
     * Send OTP during registration before form submit (Strict duplicate phone prevention).
     */
    public function sendRegistrationOtp(Request $request): JsonResponse
    {
        $request->validate(['phone' => 'required|string']);

        $rawPhone = trim($request->phone);
        $cleanDigits = preg_replace('/[^0-9]/', '', $rawPhone);
        $last10 = strlen($cleanDigits) >= 10 ? substr($cleanDigits, -10) : $cleanDigits;
        $formattedPhone = $this->smsService->formatNumber($rawPhone);
        $localPhone = '0' . $last10;

        // Check duplicate phone across all formats
        $alreadyRegistered = User::where(function ($q) use ($rawPhone, $formattedPhone, $localPhone, $last10) {
            $q->where('phone', $rawPhone)
              ->orWhere('phone', $formattedPhone)
              ->orWhere('phone', $localPhone)
              ->orWhere('phone', 'like', '%' . $last10);
        })->exists();

        if ($alreadyRegistered) {
            return response()->json([
                'success' => false,
                'message' => 'This mobile number is already registered. Please sign in instead.',
            ], 422);
        }

        $res = $this->otpService->sendOtp($formattedPhone, 'register', null, $request->ip(), $request->userAgent());

        return response()->json([
            'success'          => $res['status'],
            'message'          => $res['message'],
            'cooldown_seconds' => $res['cooldown_seconds'] ?? 60,
        ], $res['status'] ? 200 : 422);
    }

    /**
     * Verify Registration Phone OTP in real-time before form submit.
     */
    public function verifyRegistrationOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone'    => 'required|string',
            'otp_code' => 'required|string|min:4|max:10',
        ]);

        $rawPhone = trim($request->phone);
        $formattedPhone = $this->smsService->formatNumber($rawPhone);
        $code = trim($request->otp_code);

        $verify = $this->otpService->verifyOtp($formattedPhone, $code, 'register');

        if (!$verify['status']) {
            return response()->json([
                'success'            => false,
                'message'            => $verify['message'] ?? 'Invalid or expired verification code.',
                'remaining_attempts' => $verify['remaining_attempts'] ?? 0,
            ], 422);
        }

        $cleanDigits = preg_replace('/[^0-9]/', '', $rawPhone);
        $last10 = strlen($cleanDigits) >= 10 ? substr($cleanDigits, -10) : $cleanDigits;

        $request->session()->put('verified_registration_phone', [
            'formatted_phone' => $formattedPhone,
            'last10'          => $last10,
            'verified_at'     => now()->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Phone number verified successfully!',
        ]);
    }

    /**
     * Send Email OTP during registration before form submit (Strict duplicate email prevention).
     */
    public function sendRegistrationEmailOtp(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email|max:255']);
        $email = strtolower(trim($request->email));

        // Check if email already registered
        if (User::where('email', $email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This email address is already registered. Please sign in instead.',
            ], 422);
        }

        $res = $this->otpService->sendEmailOtp($email, 'register', null, $request->ip());

        return response()->json([
            'success'          => $res['status'],
            'message'          => $res['message'],
            'cooldown_seconds' => $res['cooldown_seconds'] ?? 60,
        ], $res['status'] ? 200 : 422);
    }

    /**
     * Verify Registration Email OTP in real-time before form submit.
     */
    public function verifyRegistrationEmailOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'otp_code' => 'required|string|min:4|max:10',
        ]);

        $email = strtolower(trim($request->email));
        $code  = trim($request->otp_code);

        $verify = $this->otpService->verifyEmailOtp($email, $code, 'register');

        if (!$verify['status']) {
            return response()->json([
                'success'            => false,
                'message'            => $verify['message'] ?? 'Invalid or expired verification code.',
                'remaining_attempts' => $verify['remaining_attempts'] ?? 0,
            ], 422);
        }

        $request->session()->put('verified_registration_email', [
            'email'       => $email,
            'verified_at' => now()->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Email address verified successfully!',
        ]);
    }

    /**
     * Handle Customer Registration with dynamic admin rules.
     */
    public function register(Request $request)
    {
        User::onlyTrashed()
            ->where('deleted_at', '<=', now()->subDays(30))
            ->forceDelete();

        $rules = [
            'name'     => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ];

        // Dynamic Phone validation
        $phoneRequired = $this->authSettings->isFieldRequiredForRegistration('phone');
        $phoneEnabled  = $this->authSettings->isRegistrationMethodEnabled('phone');

        if ($phoneEnabled) {
            $rules['phone'] = ($phoneRequired ? 'required' : 'nullable') . '|string|max:25';
        }

        // Dynamic Email validation
        $emailRequired = $this->authSettings->isFieldRequiredForRegistration('email');
        $emailEnabled  = $this->authSettings->isRegistrationMethodEnabled('email');

        if ($emailEnabled) {
            $rules['email'] = ($emailRequired ? 'required' : 'nullable') . '|email|max:255|unique:users,email';
        }

        // Phone OTP Code validation if required
        if ($this->authSettings->isRegistrationOtpRequired()) {
            $rules['otp_code'] = 'required|string|min:4|max:10';
        }

        // Email OTP Code validation if required
        $isEmailOtpRequired = (bool) $this->authSettings->get('registration_email_verification', false);
        if ($isEmailOtpRequired && $emailEnabled && $request->filled('email')) {
            $rules['email_otp_code'] = 'required|string|min:4|max:10';
        }

        $request->validate($rules);

        // Normalize Phone Number and Check Duplicates
        $formattedPhone = null;
        if ($request->filled('phone')) {
            $rawPhone = trim($request->phone);
            $cleanDigits = preg_replace('/[^0-9]/', '', $rawPhone);
            $last10 = strlen($cleanDigits) >= 10 ? substr($cleanDigits, -10) : $cleanDigits;
            $formattedPhone = $this->smsService->formatNumber($rawPhone);
            $localPhone = '0' . $last10;

            $exists = User::where(function ($q) use ($rawPhone, $formattedPhone, $localPhone, $last10) {
                $q->where('phone', $rawPhone)
                  ->orWhere('phone', $formattedPhone)
                  ->orWhere('phone', $localPhone)
                  ->orWhere('phone', 'like', '%' . $last10);
            })->exists();

            if ($exists) {
                return back()->withErrors(['phone' => 'This phone number is already registered.'])->withInput();
            }
        }

        // 1. Verify Phone OTP if registration phone OTP is on
        $isPhoneVerified = false;
        if ($this->authSettings->isRegistrationOtpRequired() && $formattedPhone) {
            $sessionVerifiedPhone = $request->session()->get('verified_registration_phone');
            $cleanDigits = preg_replace('/[^0-9]/', '', $request->phone);
            $last10 = strlen($cleanDigits) >= 10 ? substr($cleanDigits, -10) : $cleanDigits;

            if ($sessionVerifiedPhone && ($sessionVerifiedPhone['last10'] === $last10)) {
                $isPhoneVerified = true;
            } else {
                $verify = $this->otpService->verifyOtp($formattedPhone, trim($request->otp_code), 'register');
                if (!$verify['status']) {
                    return back()->withErrors(['otp_code' => $verify['message']])->withInput();
                }
                $isPhoneVerified = true;
            }
        }

        // 2. Verify Email OTP if registration email verification is on
        $isEmailVerified = false;
        $cleanEmail = $request->filled('email') ? strtolower(trim($request->email)) : null;

        if ($isEmailOtpRequired && $cleanEmail) {
            $sessionVerifiedEmail = $request->session()->get('verified_registration_email');

            if ($sessionVerifiedEmail && ($sessionVerifiedEmail['email'] === $cleanEmail)) {
                $isEmailVerified = true;
            } else {
                $verifyEmail = $this->otpService->verifyEmailOtp($cleanEmail, trim($request->email_otp_code), 'register');
                if (!$verifyEmail['status']) {
                    return back()->withErrors(['email_otp_code' => $verifyEmail['message']])->withInput();
                }
                $isEmailVerified = true;
            }
        }

        $request->session()->forget(['verified_registration_phone', 'verified_registration_email']);

        $user = new User();
        $user->name = trim($request->name);
        $user->phone = $formattedPhone;
        $user->phone_verified_at = $isPhoneVerified ? now() : null;
        $user->email = $cleanEmail;
        $user->email_verified_at = $isEmailVerified ? now() : null;
        $user->password = Hash::make($request->password);
        $user->role = 'customer';
        $user->user_type = 'customer';
        $user->is_active = 1;
        $user->is_locked = 0;
        $user->is_super_admin = 0;
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('user.dashboard')->with('success', 'Account created successfully!');
    }

    /**
     * Logout and destroy session.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /**
     * Helpers for Post-Login Redirection & Responses
     */
    protected function handlePostLoginRedirect(Request $request, User $user)
    {
        $request->session()->regenerate();

        if ($this->isStaffOrAdmin($user)) {
            session()->forget('url.intended');
            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended(route('user.dashboard'));
    }

    protected function completeSuccessfulLogin(Request $request, User $user, string $message): RedirectResponse
    {
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        session()->forget('url.intended');

        if ($this->isStaffOrAdmin($user)) {
            return redirect()->route('admin.dashboard')->with('success', $message);
        }

        return redirect()->route('user.dashboard')->with('success', $message);
    }

    protected function respondLoginFailed(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return back()->withErrors(['login' => $message])->onlyInput('login');
    }
}