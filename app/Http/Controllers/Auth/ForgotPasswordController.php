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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class ForgotPasswordController extends Controller
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
     * Show Forgot Password Request Screen (Supports Email Link & Phone SMS Link tabs).
     */
    public function showLinkRequestForm(): View
    {
        $settings = $this->authSettings->all();
        $isPhoneResetActive = !empty($settings['login_phone_password']) || !empty($settings['login_phone_otp']);

        return view('auth.passwords.email', compact('settings', 'isPhoneResetActive'));
    }

    /*
    |--------------------------------------------------------------------------
    | 1. EMAIL PASSWORD RESET PIPELINE
    |--------------------------------------------------------------------------
    */

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $email = strtolower(trim($request->email));
        $user = DB::table('users')->where('email', $email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'We could not find a user with that email address.'])->withInput();
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'email'      => $email,
                'token'      => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $resetUrl = route('password.reset', ['token' => $token, 'email' => $email]);

        try {
            Mail::send([], [], function ($message) use ($email, $resetUrl) {
                $appName = config('app.name', 'Shop Now BD');
                $message->to($email)
                        ->subject("{$appName} - Password Reset Request")
                        ->html("
                            <div style='font-family: Arial, sans-serif; max-width: 520px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 8px;'>
                                <h3 style='color: #0f172a; margin-top: 0;'>Reset Your Password</h3>
                                <p style='color: #475569; font-size: 14px;'>You recently requested to reset your account password. Click the button below to proceed:</p>
                                <p style='margin: 24px 0;'><a href='{$resetUrl}' style='display:inline-block;padding:12px 24px;background:#2563eb;color:#fff;text-decoration:none;font-weight:bold;border-radius:6px;'>Reset Password</a></p>
                                <p style='color: #64748b; font-size: 12px;'>If you did not request a password reset, no further action is required.</p>
                                <hr style='border: 0; border-top: 1px solid #e2e8f0; margin-top: 24px;'>
                                <p style='color: #94a3b8; font-size: 11px;'>&copy; " . date('Y') . " {$appName}. All rights reserved.</p>
                            </div>
                        ");
            });

            return back()->with('status', 'We have emailed your password reset link!');
        } catch (Throwable $e) {
            return back()->withErrors(['email' => 'Failed to send email. Please check your SMTP configuration.'])->withInput();
        }
    }

    public function showResetForm(Request $request, $token = null)
    {
        $token = $token ?: $request->query('token');
        return view('auth.passwords.reset', ['token' => $token, 'email' => $request->email]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $email = strtolower(trim($request->email));
        $resetRecord = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return back()->withErrors(['email' => 'Invalid or expired password reset token.']);
        }

        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return back()->withErrors(['email' => 'This password reset link has expired. Please request a new one.']);
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->password_attempts = 0;
            $user->setRememberToken(Str::random(60));
            $user->save();

            $this->invalidateUserSessions($user->id);
        }

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return redirect()->route('login')->with('success', 'Your password has been reset successfully! You can now sign in.');
    }

    /*
    |--------------------------------------------------------------------------
    | 2. PHONE NUMBER SMS RESET LINK PIPELINE
    |--------------------------------------------------------------------------
    */

    /**
     * Send Password Reset Link to registered mobile number via High-Priority SMS.
     */
    public function sendPhoneResetOtp(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate(['phone' => 'required|string']);

        $rawPhone = trim($request->phone);
        $cleanDigits = preg_replace('/[^0-9]/', '', $rawPhone);
        $last10 = strlen($cleanDigits) >= 10 ? substr($cleanDigits, -10) : $cleanDigits;
        $formattedPhone = $this->smsService->formatNumber($rawPhone);
        $localPhone = '0' . $last10;

        $user = User::where(function ($q) use ($rawPhone, $formattedPhone, $localPhone, $last10) {
            $q->where('phone', $formattedPhone)
              ->orWhere('phone', $localPhone)
              ->orWhere('phone', $rawPhone)
              ->orWhere('phone', 'like', '%' . $last10);
        })->first();

        if (!$user) {
            $errMsg = 'No account found registered with this mobile number.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $errMsg], 404);
            }
            return back()->withErrors(['phone' => $errMsg])->withInput();
        }

        if (!empty($user->is_locked) || (isset($user->is_active) && !$user->is_active)) {
            $errMsg = 'Account is temporarily restricted. Password reset unavailable.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $errMsg], 403);
            }
            return back()->withErrors(['phone' => $errMsg])->withInput();
        }

        // Generate Secure Cryptographic Token
        $token = Str::random(64);

        // Store in password_reset_tokens table against normalized phone number
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $formattedPhone],
            [
                'email'      => $formattedPhone,
                'token'      => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $appName = config('app.name', 'Shop Now BD');
        $resetUrl = route('password.reset.phone', ['token' => $token, 'phone' => $formattedPhone]);

        // Professional SMS Body containing direct password reset URL
        $smsBody = "Reset your {$appName} account password here: {$resetUrl} (Valid for 15 minutes). Do not share this link.";

        $smsResult = $this->smsService->send($formattedPhone, $smsBody, 'otp', [
            'user_id'    => $user->id,
            'ip_address' => $request->ip(),
        ]);

        if (!($smsResult['status'] ?? false)) {
            $errMsg = $smsResult['message'] ?? 'Failed to deliver SMS. Please check your network or try again.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $errMsg], 422);
            }
            return back()->withErrors(['phone' => $errMsg])->withInput();
        }

        $successMsg = 'We have sent a secure password reset link to your mobile phone via SMS!';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'          => true,
                'message'          => $successMsg,
                'cooldown_seconds' => 60,
            ]);
        }

        return back()->with('status', $successMsg);
    }

    /**
     * Backward-compatibility handler for OTP verification (if needed).
     */
    public function verifyPhoneResetOtp(Request $request): JsonResponse
    {
        return $this->sendPhoneResetOtp($request);
    }

    /**
     * Display Set New Password screen for phone reset (Clicked from SMS link).
     */
    public function showPhoneResetForm(Request $request, $token = null): View|RedirectResponse
    {
        $token = $token ?: ($request->query('token') ?: $request->route('token'));
        $phone = $request->query('phone') ?: ($request->route('phone') ?: session('reset_phone'));

        if (!$token || !$phone) {
            return redirect()->route('password.request')->withErrors(['phone' => 'Invalid password reset link. Please request a new link.']);
        }

        $formattedPhone = $this->smsService->formatNumber(trim($phone));
        $cleanDigits = preg_replace('/[^0-9]/', '', $formattedPhone);
        $last10 = strlen($cleanDigits) >= 10 ? substr($cleanDigits, -10) : $cleanDigits;
        $localPhone = '0' . $last10;

        // Check active reset record from database
        $resetRecord = DB::table('password_reset_tokens')
            ->where(function ($q) use ($formattedPhone, $localPhone) {
                $q->where('email', $formattedPhone)
                  ->orWhere('email', $localPhone);
            })
            ->first();

        if (!$resetRecord || !Hash::check($token, $resetRecord->token)) {
            return redirect()->route('password.request')->withErrors(['phone' => 'Invalid or expired password reset link.']);
        }

        // 15 Minutes Expiration Limit
        if (Carbon::parse($resetRecord->created_at)->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $resetRecord->email)->delete();
            return redirect()->route('password.request')->withErrors(['phone' => 'This password reset link has expired. Please request a new one.']);
        }

        $maskedPhone = substr($formattedPhone, 0, 5) . '••••' . substr($formattedPhone, -3);

        return view('auth.passwords.reset_phone', compact('formattedPhone', 'token', 'maskedPhone'));
    }

    /**
     * Complete password update via verified phone link.
     */
    public function resetWithPhone(Request $request): RedirectResponse
    {
        $request->validate([
            'token'                 => 'required|string',
            'phone'                 => 'required|string',
            'password'              => 'required|min:6|confirmed',
        ]);

        $rawPhone = trim($request->phone);
        $cleanDigits = preg_replace('/[^0-9]/', '', $rawPhone);
        $last10 = strlen($cleanDigits) >= 10 ? substr($cleanDigits, -10) : $cleanDigits;
        $formattedPhone = $this->smsService->formatNumber($rawPhone);
        $localPhone = '0' . $last10;

        $resetRecord = DB::table('password_reset_tokens')
            ->where(function ($q) use ($formattedPhone, $localPhone) {
                $q->where('email', $formattedPhone)
                  ->orWhere('email', $localPhone);
            })
            ->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return redirect()->route('password.request')->withErrors(['phone' => 'Invalid or expired password reset authorization.']);
        }

        if (Carbon::parse($resetRecord->created_at)->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $resetRecord->email)->delete();
            return redirect()->route('password.request')->withErrors(['phone' => 'This reset session has expired. Please request a new link.']);
        }

        $user = User::where(function ($q) use ($rawPhone, $formattedPhone, $localPhone, $last10) {
            $q->where('phone', $formattedPhone)
              ->orWhere('phone', $localPhone)
              ->orWhere('phone', $rawPhone)
              ->orWhere('phone', 'like', '%' . $last10);
        })->first();

        if (!$user) {
            return redirect()->route('password.request')->withErrors(['phone' => 'User account not found.']);
        }

        $user->password = Hash::make($request->password);
        $user->password_attempts = 0;
        $user->setRememberToken(Str::random(60));

        if (method_exists($user, 'hasVerifiedPhone') && !$user->hasVerifiedPhone()) {
            $user->markPhoneAsVerified();
        }

        $user->save();

        // Invalidate sessions and cleanup tokens
        $this->invalidateUserSessions($user->id);
        DB::table('password_reset_tokens')->where('email', $resetRecord->email)->delete();

        return redirect()->route('login')->with('success', 'Your password has been changed successfully! Sign in with your new password.');
    }

    /**
     * Session and token cleanup helper to prevent session hijacking.
     */
    protected function invalidateUserSessions(int $userId): void
    {
        if (Schema::hasColumn('users', 'force_logout')) {
            DB::table('users')->where('id', $userId)->update(['force_logout' => true]);
        }

        if (Schema::hasTable('active_user_sessions')) {
            DB::table('active_user_sessions')->where('user_id', $userId)->update(['is_revoked' => true]);
        }
    }
}