<?php

namespace App\Services\Sms;

use App\Models\SmsBlacklist;
use App\Models\SmsOtpVerification;
use App\Models\SmsSetting;
use App\Services\Auth\AuthenticationSettingsService;
use App\Services\Sms\Drivers\OnecodesoftDriver;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Throwable;

class OtpService
{
    protected SmsService $smsService;
    protected OnecodesoftDriver $driver;
    protected ?AuthenticationSettingsService $authSettings;

    public function __construct(
        SmsService $smsService,
        OnecodesoftDriver $driver,
        ?AuthenticationSettingsService $authSettings = null
    ) {
        $this->smsService = $smsService;
        $this->driver = $driver;
        $this->authSettings = $authSettings ?? (class_exists(AuthenticationSettingsService::class) ? app(AuthenticationSettingsService::class) : null);
    }

    /**
     * Generate and dispatch cryptographic 6-digit OTP via High-Priority SMS
     */
    public function sendOtp(
        string $phone,
        string $purpose = 'login',
        ?int $userId = null,
        ?string $ip = null,
        ?string $userAgent = null
    ): array {
        $formattedPhone = $this->smsService->formatNumber($phone);

        // 1. Blacklist Check (Mobile number & IP)
        try {
            if (class_exists(SmsBlacklist::class)) {
                if (method_exists(SmsBlacklist::class, 'isPhoneBlocked') && SmsBlacklist::isPhoneBlocked($formattedPhone)) {
                    return [
                        'status'  => false,
                        'message' => 'This mobile number is restricted from requesting verification codes.',
                    ];
                }

                if ($ip && method_exists(SmsBlacklist::class, 'isIpBlocked') && SmsBlacklist::isIpBlocked($ip)) {
                    return [
                        'status'  => false,
                        'message' => 'Requests from this network IP address are currently restricted.',
                    ];
                }
            }
        } catch (Throwable $e) {
            Log::warning('SmsBlacklist check exception bypassed in OTP: ' . $e->getMessage());
        }

        // 2. Resolve Dynamic Settings
        $expiryMinutes   = (int) $this->resolveSetting('otp_expiry_minutes', 5);
        $cooldownSeconds = (int) $this->resolveSetting('otp_resend_cooldown_seconds', 60);
        $maxPerNumber    = (int) $this->resolveSetting('otp_daily_limit', 10);
        $maxPerIp        = (int) $this->resolveSetting('max_daily_otp_per_ip', 25);
        $maxAttempts     = (int) $this->resolveSetting('otp_max_attempts', 3);

        // 3. Daily Rate Limiting (Per Phone & Per IP)
        $dailyCountNumber = SmsOtpVerification::where('phone_number', $formattedPhone)
            ->whereDate('created_at', Carbon::today())
            ->count();

        if ($dailyCountNumber >= $maxPerNumber) {
            return [
                'status'  => false,
                'message' => 'Daily verification limit exceeded for this mobile number. Please try again tomorrow.',
            ];
        }

        if ($ip) {
            $dailyCountIp = SmsOtpVerification::where('ip_address', $ip)
                ->whereDate('created_at', Carbon::today())
                ->count();

            if ($dailyCountIp >= $maxPerIp) {
                return [
                    'status'  => false,
                    'message' => 'Daily verification limit reached from your network IP. Please try again later.',
                ];
            }
        }

        // 4. Cooldown Check on active pending record
        $latestRecord = SmsOtpVerification::where('phone_number', $formattedPhone)
            ->where('purpose', $purpose)
            ->where('is_verified', false)
            ->latest('id')
            ->first();

        if ($latestRecord && $this->isRecordUnderCooldown($latestRecord)) {
            $remainingSeconds = $this->getCooldownRemainingSeconds($latestRecord);
            return [
                'status'                 => false,
                'message'                => "Please wait {$remainingSeconds} seconds before requesting a new code.",
                'cooldown_seconds'       => $remainingSeconds,
                'next_resend_allowed_at' => $latestRecord->next_resend_allowed_at ? Carbon::parse($latestRecord->next_resend_allowed_at)->toIso8601String() : null,
            ];
        }

        $now = Carbon::now();
        $expiresAt = $now->copy()->addMinutes($expiryMinutes);
        $cooldownUntil = $now->copy()->addSeconds($cooldownSeconds);

        // 5. Guaranteed 6-Digit Cryptographic Code Generation
        $plainOtp = (string) random_int(100000, 999999);
        $appName = config('app.name', 'Shop Now BD');

        // 6. Professional, Purpose-Specific SMS Messages (No corrupted template collisions)
        $smsBody = match ($purpose) {
            'register'       => "Your Account Registration OTP is {$plainOtp}. Valid for {$expiryMinutes} minutes. Do not share this code. - {$appName}",
            'phone_change'   => "Your Phone Number Update OTP is {$plainOtp}. Valid for {$expiryMinutes} minutes. Do not share this code. - {$appName}",
            'password_reset' => "Your Password Reset OTP is {$plainOtp}. Valid for {$expiryMinutes} minutes. Do not share this code. - {$appName}",
            'two_factor'     => "Your 2FA Security Authentication OTP is {$plainOtp}. Valid for {$expiryMinutes} minutes. - {$appName}",
            default          => "Your Login OTP is {$plainOtp}. Valid for {$expiryMinutes} minutes. Do not share this code. - {$appName}",
        };

        // Dispatch High-Priority OTP SMS
        $smsResult = $this->smsService->send($formattedPhone, $smsBody, 'otp', [
            'user_id'    => $userId,
            'ip_address' => $ip,
        ]);

        if (!($smsResult['status'] ?? false)) {
            return [
                'status'  => false,
                'message' => $smsResult['message'] ?? 'Failed to deliver SMS verification code. Please check your network or try again.',
            ];
        }

        // 7. Persist Securely in Database (Always stored hashed)
        $otpRecord = new SmsOtpVerification([
            'phone_number'           => $formattedPhone,
            'user_id'                => $userId,
            'provider_message_id'    => $smsResult['message_id'] ?? null,
            'mode'                   => 'custom',
            'purpose'                => $purpose,
            'ip_address'             => $ip,
            'user_agent'             => $userAgent,
            'next_resend_allowed_at' => $cooldownUntil,
            'expires_at'             => $expiresAt,
        ]);

        $this->assignPlainOtp($otpRecord, $plainOtp);
        $otpRecord->save();

        return [
            'status'             => true,
            'message'            => "Verification code sent to your mobile phone.",
            'mode'               => 'custom',
            'verification_id'    => $otpRecord->id,
            'cooldown_seconds'   => $cooldownSeconds,
            'expires_in_seconds' => $expiryMinutes * 60,
        ];
    }

    /**
     * Verify submitted OTP code against active record
     */
    public function verifyOtp(string $phone, string $code, string $purpose = 'login'): array
    {
        $formattedPhone = $this->smsService->formatNumber($phone);
        $cleanCode = trim($code);

        $record = SmsOtpVerification::where('phone_number', $formattedPhone)
            ->where('purpose', $purpose)
            ->where('is_verified', false)
            ->latest('id')
            ->first();

        if (!$record) {
            return [
                'status'  => false,
                'message' => 'No active verification code found for this phone number. Please request a new code.',
            ];
        }

        if ($this->isRecordExpired($record)) {
            return [
                'status'  => false,
                'message' => 'The verification code has expired. Please request a new code.',
            ];
        }

        $maxAttempts = (int) $this->resolveSetting('otp_max_attempts', 3);
        if (($record->attempts ?? 0) >= $maxAttempts) {
            return [
                'status'  => false,
                'message' => 'Maximum verification attempts exceeded. For security, please request a new code.',
            ];
        }

        $record->increment('attempts');

        // Validation based on record mode
        $isValid = false;
        if ($record->mode === 'identity') {
            $gatewayCheck = $this->driver->verifyIdentityOtp($formattedPhone, $cleanCode);
            $isValid = (bool) ($gatewayCheck['status'] ?? false);
        } else {
            $isValid = $this->validateOtpCode($record, $cleanCode);
        }

        if (!$isValid) {
            $remaining = max(0, $maxAttempts - $record->attempts);
            return [
                'status'             => false,
                'message'            => $remaining > 0 
                    ? "Invalid code. You have {$remaining} attempt(s) remaining." 
                    : 'Maximum attempts reached. Please request a new code.',
                'remaining_attempts' => $remaining,
            ];
        }

        // Single-use: Mark as verified immediately
        $record->update([
            'is_verified' => true,
            'verified_at' => Carbon::now(),
        ]);

        return [
            'status'          => true,
            'message'         => 'Mobile number verified successfully.',
            'verification_id' => $record->id,
            'user_id'         => $record->user_id,
        ];
    }

    /**
     * Dispatch an Email OTP code (For Email Login, Email 2FA, Email Change)
     */
    public function sendEmailOtp(string $email, string $purpose = 'login', ?int $userId = null, ?string $ip = null): array
    {
        $cleanEmail = strtolower(trim($email));

        $expiryMinutes   = (int) $this->resolveSetting('otp_expiry_minutes', 5);
        $cooldownSeconds = (int) $this->resolveSetting('otp_resend_cooldown_seconds', 60);

        $cacheKey = "email_otp_{$purpose}_" . md5($cleanEmail);
        $existing = Cache::get($cacheKey);

        if ($existing && isset($existing['cooldown_until']) && Carbon::parse($existing['cooldown_until'])->isFuture()) {
            $remaining = Carbon::now()->diffInSeconds(Carbon::parse($existing['cooldown_until']));
            return [
                'status'           => false,
                'message'          => "Please wait {$remaining} seconds before requesting another code.",
                'cooldown_seconds' => $remaining,
            ];
        }

        $plainOtp = (string) random_int(100000, 999999);
        $now = Carbon::now();

        $purposeLabel = match ($purpose) {
            'register'       => 'Registration Verification',
            'password_reset' => 'Password Reset',
            'two_factor'     => 'Security Verification',
            'email_change'   => 'Email Address Update',
            default          => 'Sign In Verification',
        };

        try {
            Mail::send([], [], function ($message) use ($cleanEmail, $plainOtp, $expiryMinutes, $purposeLabel) {
                $appName = config('app.name', 'Shop Now BD');
                $message->to($cleanEmail)
                    ->subject("{$appName} - {$purposeLabel} Code: {$plainOtp}")
                    ->html("
                        <div style='font-family: Arial, sans-serif; max-width: 520px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 8px;'>
                            <h3 style='color: #0f172a; margin-top: 0;'>{$purposeLabel}</h3>
                            <p style='color: #475569; font-size: 14px;'>Use the following verification code to complete your request:</p>
                            <div style='background: #f1f5f9; padding: 16px; text-align: center; font-size: 28px; font-weight: bold; letter-spacing: 6px; color: #0284c7; border-radius: 6px; margin: 20px 0;'>
                                {$plainOtp}
                            </div>
                            <p style='color: #64748b; font-size: 12px;'>This code is confidential and will expire in {$expiryMinutes} minutes. If you did not initiate this request, please secure your account immediately.</p>
                            <hr style='border: 0; border-top: 1px solid #e2e8f0; margin-top: 24px;'>
                            <p style='color: #94a3b8; font-size: 11px; text-align: center;'>&copy; " . date('Y') . " {$appName}. All rights reserved.</p>
                        </div>
                    ");
            });

            Cache::put($cacheKey, [
                'hash'           => Hash::make($plainOtp),
                'attempts'       => 0,
                'expires_at'     => $now->copy()->addMinutes($expiryMinutes)->toIso8601String(),
                'cooldown_until' => $now->copy()->addSeconds($cooldownSeconds)->toIso8601String(),
                'user_id'        => $userId,
            ], $now->copy()->addMinutes($expiryMinutes));

            return [
                'status'             => true,
                'message'            => 'Verification code sent to your email address.',
                'cooldown_seconds'   => $cooldownSeconds,
                'expires_in_seconds' => $expiryMinutes * 60,
            ];
        } catch (Throwable $e) {
            Log::error("Email OTP delivery failed for {$cleanEmail}: " . $e->getMessage());
            return [
                'status'  => false,
                'message' => 'Failed to deliver email code. Please check your email configuration.',
            ];
        }
    }

    /**
     * Verify an entered Email OTP code
     */
    public function verifyEmailOtp(string $email, string $code, string $purpose = 'login'): array
    {
        $cleanEmail = strtolower(trim($email));
        $cleanCode  = trim($code);

        $cacheKey = "email_otp_{$purpose}_" . md5($cleanEmail);
        $stored = Cache::get($cacheKey);

        if (!$stored) {
            return [
                'status'  => false,
                'message' => 'No active verification code found or code has expired.',
            ];
        }

        if (Carbon::now()->isAfter(Carbon::parse($stored['expires_at']))) {
            Cache::forget($cacheKey);
            return [
                'status'  => false,
                'message' => 'Verification code has expired. Please request a new code.',
            ];
        }

        $maxAttempts = (int) $this->resolveSetting('otp_max_attempts', 3);
        $attempts = ($stored['attempts'] ?? 0) + 1;
        $stored['attempts'] = $attempts;

        if ($attempts > $maxAttempts) {
            Cache::forget($cacheKey);
            return [
                'status'  => false,
                'message' => 'Maximum verification attempts exceeded. Please request a new code.',
            ];
        }

        if (!Hash::check($cleanCode, $stored['hash'])) {
            Cache::put($cacheKey, $stored, Carbon::parse($stored['expires_at']));
            $remaining = max(0, $maxAttempts - $attempts);
            return [
                'status'             => false,
                'message'            => "Invalid code. You have {$remaining} attempt(s) remaining.",
                'remaining_attempts' => $remaining,
            ];
        }

        Cache::forget($cacheKey);

        return [
            'status'  => true,
            'message' => 'Email verified successfully.',
            'user_id' => $stored['user_id'] ?? null,
        ];
    }

    /**
     * Resolve setting value across Auth Settings and SMS Settings
     */
    protected function resolveSetting(string $key, mixed $default = null): mixed
    {
        if ($this->authSettings) {
            $val = $this->authSettings->get($key);
            if (!is_null($val)) {
                return $val;
            }
        }

        return $this->getSettingValue($key, $default);
    }

    protected function getSettingValue(string $key, mixed $default = null): mixed
    {
        if (class_exists(SmsSetting::class)) {
            if (method_exists(SmsSetting::class, 'getVal')) {
                return SmsSetting::getVal($key, $default);
            }
            if (Schema::hasTable('sms_settings')) {
                return SmsSetting::where('key', $key)->value('value') ?? $default;
            }
        }
        return $default;
    }

    protected function isRecordUnderCooldown($record): bool
    {
        if (method_exists($record, 'isUnderCooldown')) {
            return $record->isUnderCooldown();
        }

        return !empty($record->next_resend_allowed_at) && Carbon::parse($record->next_resend_allowed_at)->isFuture();
    }

    protected function getCooldownRemainingSeconds($record): int
    {
        if (method_exists($record, 'getCooldownRemainingSeconds')) {
            return (int) $record->getCooldownRemainingSeconds();
        }

        if (!empty($record->next_resend_allowed_at)) {
            $diff = Carbon::now()->diffInSeconds(Carbon::parse($record->next_resend_allowed_at), false);
            return max(0, (int) $diff);
        }

        return 0;
    }

    protected function isRecordExpired($record): bool
    {
        if (method_exists($record, 'isExpired')) {
            return $record->isExpired();
        }

        return !empty($record->expires_at) && Carbon::parse($record->expires_at)->isPast();
    }

    /**
     * Store OTP code securely (Never store plain text in database)
     */
    protected function assignPlainOtp($record, string $plainOtp): void
    {
        $hash = Hash::make($plainOtp);

        if (Schema::hasColumn('sms_otp_verifications', 'otp_hash')) {
            $record->otp_hash = $hash;
        }

        if (Schema::hasColumn('sms_otp_verifications', 'otp_code')) {
            $record->otp_code = $hash;
        }
    }

    /**
     * Validate entered OTP code securely
     */
    protected function validateOtpCode($record, string $cleanCode): bool
    {
        if (method_exists($record, 'checkOtp')) {
            return $record->checkOtp($cleanCode);
        }

        if (!empty($record->otp_hash)) {
            return Hash::check($cleanCode, $record->otp_hash);
        }

        if (isset($record->otp_code)) {
            if (str_starts_with((string)$record->otp_code, '$2y$')) {
                return Hash::check($cleanCode, $record->otp_code);
            }
            return (string) $record->otp_code === $cleanCode;
        }

        return false;
    }
}