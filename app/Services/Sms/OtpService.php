<?php

namespace App\Services\Sms;

use App\Models\SmsBlacklist;
use App\Models\SmsOtpVerification;
use App\Models\SmsSetting;
use App\Services\Sms\Drivers\OnecodesoftDriver;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class OtpService
{
    protected SmsService $smsService;
    protected OnecodesoftDriver $driver;

    public function __construct(SmsService $smsService, OnecodesoftDriver $driver)
    {
        $this->smsService = $smsService;
        $this->driver = $driver;
    }

    /**
     * Generate or request OTP dispatch
     */
    public function sendOtp(
        string $phone,
        string $purpose = 'login',
        ?int $userId = null,
        ?string $ip = null,
        ?string $userAgent = null
    ): array {
        $formattedPhone = $this->smsService->formatNumber($phone);

        // 1. Blacklist check (safe execution)
        try {
            if (class_exists(SmsBlacklist::class)) {
                if (method_exists(SmsBlacklist::class, 'isPhoneBlocked') && SmsBlacklist::isPhoneBlocked($formattedPhone)) {
                    return [
                        'status'  => false,
                        'message' => 'This mobile number is blocked from requesting verification codes.',
                    ];
                }

                if ($ip && method_exists(SmsBlacklist::class, 'isIpBlocked') && SmsBlacklist::isIpBlocked($ip)) {
                    return [
                        'status'  => false,
                        'message' => 'Requests from this network IP address are restricted.',
                    ];
                }
            }
        } catch (Throwable $e) {
            Log::warning('SmsBlacklist check exception bypassed in OTP: ' . $e->getMessage());
        }

        // 2. Daily Rate Limiting (Per Phone & Per IP)
        $maxPerNumber = (int) $this->getSettingValue('max_daily_otp_per_number', 5);
        $dailyCountNumber = SmsOtpVerification::where('phone_number', $formattedPhone)
            ->whereDate('created_at', Carbon::today())
            ->count();

        if ($dailyCountNumber >= $maxPerNumber) {
            return [
                'status'  => false,
                'message' => 'Daily verification limit exceeded for this number. Please try again tomorrow.',
            ];
        }

        if ($ip) {
            $maxPerIp = (int) $this->getSettingValue('max_daily_otp_per_ip', 15);
            $dailyCountIp = SmsOtpVerification::where('ip_address', $ip)
                ->whereDate('created_at', Carbon::today())
                ->count();

            if ($dailyCountIp >= $maxPerIp) {
                return [
                    'status'  => false,
                    'message' => 'Daily verification limit exceeded from your network. Please try again later.',
                ];
            }
        }

        // 3. Active Session & Cooldown Verification
        $latestRecord = SmsOtpVerification::where('phone_number', $formattedPhone)
            ->where('purpose', $purpose)
            ->where('is_verified', false)
            ->latest('id')
            ->first();

        if ($latestRecord && $this->isRecordUnderCooldown($latestRecord)) {
            $remainingSeconds = $this->getCooldownRemainingSeconds($latestRecord);
            return [
                'status'                 => false,
                'message'                => 'Please wait before requesting another code.',
                'cooldown_seconds'       => $remainingSeconds,
                'next_resend_allowed_at' => $latestRecord->next_resend_allowed_at ? Carbon::parse($latestRecord->next_resend_allowed_at)->toIso8601String() : null,
            ];
        }

        // 4. Resolve Settings
        $mode = (string) $this->getSettingValue('otp_mode', 'identity');
        $expiryMinutes = (int) $this->getSettingValue('otp_expiry_minutes', 5);
        $cooldownSeconds = (int) $this->getSettingValue('otp_resend_cooldown_seconds', 60);

        $now = Carbon::now();
        $expiresAt = $now->copy()->addMinutes($expiryMinutes);
        $cooldownUntil = $now->copy()->addSeconds($cooldownSeconds);

        // 5. Dispatch based on OTP mode
        if ($mode === 'identity') {
            $response = $this->driver->requestIdentityOtp($formattedPhone);

            if (!($response['status'] ?? false)) {
                return [
                    'status'  => false,
                    'message' => $response['message'] ?? 'Failed to send OTP via Identity gateway.',
                ];
            }

            $otpRecord = SmsOtpVerification::create([
                'phone_number'           => $formattedPhone,
                'user_id'                => $userId,
                'provider_otp_id'        => $response['provider_otp_id'] ?? null,
                'mode'                   => 'identity',
                'purpose'                => $purpose,
                'ip_address'             => $ip,
                'user_agent'             => $userAgent,
                'next_resend_allowed_at' => $cooldownUntil,
                'expires_at'             => $expiresAt,
            ]);

            return [
                'status'             => true,
                'message'            => 'Verification code sent to your phone number.',
                'mode'               => 'identity',
                'verification_id'    => $otpRecord->id,
                'cooldown_seconds'   => $cooldownSeconds,
                'expires_in_seconds' => $expiryMinutes * 60,
            ];
        }

        // 6. Custom OTP Generation & Dispatch
        $plainOtp = (string) random_int(100000, 999999);

        // Try template sending first
        $smsResult = $this->smsService->sendTemplate('custom_login_otp', $formattedPhone, [
            'otp_code' => $plainOtp,
        ], [
            'user_id'    => $userId,
            'ip_address' => $ip,
        ]);

        // Fallback to direct SMS if template does not exist or fails
        if (!($smsResult['status'] ?? false)) {
            $fallbackMessage = "Your verification code is {$plainOtp}. Valid for {$expiryMinutes} minutes. - " . config('app.name', 'SHOP NOW BD');
            $smsResult = $this->smsService->send($formattedPhone, $fallbackMessage, 'otp', [
                'user_id'    => $userId,
                'ip_address' => $ip,
            ]);
        }

        if (!($smsResult['status'] ?? false)) {
            return [
                'status'  => false,
                'message' => $smsResult['message'] ?? 'Failed to deliver SMS verification code.',
            ];
        }

        // Persist verification record
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
            'message'            => 'Verification code sent to your phone number.',
            'mode'               => 'custom',
            'verification_id'    => $otpRecord->id,
            'cooldown_seconds'   => $cooldownSeconds,
            'expires_in_seconds' => $expiryMinutes * 60,
        ];
    }

    /**
     * Verify an entered OTP against active verification record
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
                'message' => 'No pending OTP verification found for this number.',
            ];
        }

        if ($this->isRecordExpired($record)) {
            return [
                'status'  => false,
                'message' => 'The verification code has expired. Please request a new code.',
            ];
        }

        $maxAttempts = (int) $this->getSettingValue('max_otp_attempts', 3);
        if (($record->attempts ?? 0) >= $maxAttempts) {
            return [
                'status'  => false,
                'message' => 'Maximum verification attempts exceeded. Please request a new code.',
            ];
        }

        $record->increment('attempts');

        // Verification validation based on mode
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
                'message'            => "Invalid code. You have {$remaining} attempt(s) remaining.",
                'remaining_attempts' => $remaining,
            ];
        }

        // Mark as successfully verified
        $record->update([
            'is_verified' => true,
            'verified_at' => Carbon::now(),
        ]);

        return [
            'status'          => true,
            'message'         => 'Phone number verified successfully.',
            'verification_id' => $record->id,
            'user_id'         => $record->user_id,
        ];
    }

    /**
     * Safe setting value retriever
     */
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

    /**
     * Check if the OTP record is currently under cooldown
     */
    protected function isRecordUnderCooldown($record): bool
    {
        if (method_exists($record, 'isUnderCooldown')) {
            return $record->isUnderCooldown();
        }

        return !empty($record->next_resend_allowed_at) && Carbon::parse($record->next_resend_allowed_at)->isFuture();
    }

    /**
     * Get remaining seconds for cooldown
     */
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

    /**
     * Check if the OTP record has expired
     */
    protected function isRecordExpired($record): bool
    {
        if (method_exists($record, 'isExpired')) {
            return $record->isExpired();
        }

        return !empty($record->expires_at) && Carbon::parse($record->expires_at)->isPast();
    }

    /**
     * Set OTP securely on record instance
     */
    protected function assignPlainOtp($record, string $plainOtp): void
    {
        if (method_exists($record, 'setPlainOtp')) {
            $record->setPlainOtp($plainOtp);
            return;
        }

        if (Schema::hasColumn('sms_otp_verifications', 'otp_code')) {
            $record->otp_code = $plainOtp;
        }

        if (Schema::hasColumn('sms_otp_verifications', 'otp_hash')) {
            $record->otp_hash = Hash::make($plainOtp);
        }
    }

    /**
     * Validate entered OTP against the record
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
            return (string) $record->otp_code === $cleanCode;
        }

        return false;
    }
}