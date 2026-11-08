<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Throwable;

class AuthenticationSettingsService
{
    public const CACHE_KEY = 'app_authentication_settings_cache';
    public const CACHE_TTL = 86400; // 24 hours (cleared on update)

    /**
     * Retrieve all cached settings with type-casting.
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            if (!Schema::hasTable('authentication_settings')) {
                return $this->getHardcodedDefaults();
            }

            $rawRecords = DB::table('authentication_settings')->get();
            $settings = [];

            foreach ($rawRecords as $record) {
                $settings[$record->key] = $this->castValue($record->value, $record->type);
            }

            return array_merge($this->getHardcodedDefaults(), $settings);
        });
    }

    /**
     * Get a specific setting value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->all();
        return $settings[$key] ?? $default;
    }

    /**
     * Update multiple authentication settings with strict fail-safe validation.
     */
    public function updateSettings(array $inputs): bool
    {
        if (!Schema::hasTable('authentication_settings')) {
            return false;
        }

        // 1. Fail-Safe Guard: At least ONE login method MUST remain enabled
        $loginMethods = [
            'login_email_password',
            'login_phone_password',
            'login_phone_otp',
            'login_email_otp',
            'login_google',
        ];

        $hasAtLeastOne = false;
        foreach ($loginMethods as $method) {
            $val = array_key_exists($method, $inputs)
                ? (bool) $inputs[$method]
                : (bool) $this->get($method, false);

            if ($val) {
                $hasAtLeastOne = true;
                break;
            }
        }

        if (!$hasAtLeastOne) {
            throw new InvalidArgumentException('Security Violation: At least one login method must remain active.');
        }

        // 2. Validate Default Login Method compatibility
        $currentDefault = $inputs['default_login_method'] ?? $this->get('default_login_method', 'email_password');
        $defaultKeyMap = [
            'email_password' => 'login_email_password',
            'phone_password' => 'login_phone_password',
            'phone_otp'      => 'login_phone_otp',
            'email_otp'      => 'login_email_otp',
            'google'         => 'login_google',
        ];

        $requiredKey = $defaultKeyMap[$currentDefault] ?? 'login_email_password';
        $isCurrentDefaultActive = array_key_exists($requiredKey, $inputs)
            ? (bool) $inputs[$requiredKey]
            : (bool) $this->get($requiredKey, false);

        if (!$isCurrentDefaultActive) {
            foreach ($defaultKeyMap as $defMethod => $targetKey) {
                $isActive = array_key_exists($targetKey, $inputs)
                    ? (bool) $inputs[$targetKey]
                    : (bool) $this->get($targetKey, false);

                if ($isActive) {
                    $inputs['default_login_method'] = $defMethod;
                    break;
                }
            }
        }

        // 3. Batch Update Records using updateOrInsert
        DB::beginTransaction();
        try {
            $existingRows = DB::table('authentication_settings')->pluck('type', 'key')->toArray();

            foreach ($inputs as $key => $rawVal) {
                $type = $existingRows[$key] ?? 'string';

                // Save google_client_secret safely as string to prevent truncation/decrypt crash
                if ($key === 'google_client_secret') {
                    $type = 'string';
                }

                // If secret is masked dots or empty, preserve existing stored secret without overwriting
                if ($key === 'google_client_secret' && (empty($rawVal) || str_contains((string)$rawVal, '•••') || str_contains((string)$rawVal, '••••'))) {
                    continue;
                }

                $dbVal = $this->prepareForStorage($rawVal, $type);

                DB::table('authentication_settings')->updateOrInsert(
                    ['key' => $key],
                    [
                        'value'      => $dbVal,
                        'type'       => $type,
                        'updated_at' => now(),
                    ]
                );
            }

            DB::commit();
            $this->clearCache();

            return true;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Check if a specific login method is active.
     */
    public function isLoginMethodEnabled(string $method): bool
    {
        $normalized = str_replace('-', '_', strtolower(trim($method)));
        $key = str_starts_with($normalized, 'login_') ? $normalized : 'login_' . $normalized;

        return (bool) $this->get($key, false);
    }

    /**
     * Get list of all currently enabled login methods.
     */
    public function getEnabledLoginMethods(): array
    {
        $methods = [
            'email_password' => $this->isLoginMethodEnabled('email_password'),
            'phone_password' => $this->isLoginMethodEnabled('phone_password'),
            'phone_otp'      => $this->isLoginMethodEnabled('phone_otp'),
            'email_otp'      => $this->isLoginMethodEnabled('email_otp'),
            'google'         => $this->isLoginMethodEnabled('google'),
        ];

        return array_keys(array_filter($methods));
    }

    /**
     * Get the active default login method.
     */
    public function getDefaultLoginMethod(): string
    {
        $default = (string) $this->get('default_login_method', 'email_password');
        if ($this->isLoginMethodEnabled($default)) {
            return $default;
        }

        $active = $this->getEnabledLoginMethods();
        return $active[0] ?? 'email_password';
    }

    /**
     * Registration permission and requirement helpers.
     */
    public function isRegistrationMethodEnabled(string $type): bool
    {
        return (bool) $this->get("registration_{$type}_enabled", true);
    }

    public function isFieldRequiredForRegistration(string $field): bool
    {
        return (bool) $this->get("registration_{$field}_required", false);
    }

    public function isRegistrationOtpRequired(): bool
    {
        return (bool) $this->get('registration_otp_verification', false);
    }

    public function isRegistrationEmailVerificationRequired(): bool
    {
        return (bool) $this->get('registration_email_verification', false);
    }

    public function isAccountLinkingEnabled(): bool
    {
        return (bool) $this->get('account_linking_enabled', true);
    }

    /**
     * Get OTP Configuration.
     */
    public function getOtpConfig(): array
    {
        return [
            'expiry_minutes'   => (int) $this->resolveOtpSetting('otp_expiry_minutes', 5),
            'resend_cooldown'  => (int) $this->resolveOtpSetting('otp_resend_cooldown_seconds', 60),
            'max_attempts'     => (int) $this->resolveOtpSetting('otp_max_attempts', 3),
            'daily_limit'      => (int) $this->resolveOtpSetting('otp_daily_limit', 10),
        ];
    }

    protected function resolveOtpSetting(string $key, mixed $default = null): mixed
    {
        $val = $this->get($key);
        if (!is_null($val)) return $val;

        if (Schema::hasTable('sms_settings')) {
            return DB::table('sms_settings')->where('key', $key)->value('value') ?? $default;
        }
        return $default;
    }

    /**
     * Get Google OAuth Settings.
     */
    public function getGoogleConfig(): array
    {
        $secret = $this->get('google_client_secret', '');
        if (is_string($secret)) {
            $secret = $this->decryptSafely($secret);
        }

        return [
            'enabled'       => $this->isLoginMethodEnabled('google'),
            'client_id'     => trim((string) $this->get('google_client_id', '')),
            'client_secret' => trim((string) ($secret ?? '')),
            'redirect_url'  => (string) $this->get('google_redirect_url', '/auth/google/callback'),
            'auto_register' => (bool) $this->get('registration_google_enabled', false),
        ];
    }

    /**
     * Get Complete 2FA Configuration.
     */
    public function getTwoFactorConfig(): array
    {
        return [
            'global_enabled' => (bool) $this->get('two_factor_global', false),
            'mode'           => (string) $this->get('two_factor_mode', 'optional'),
            'for_admin'      => (bool) $this->get('two_factor_for_admin', true),
            'for_staff'      => (bool) $this->get('two_factor_for_staff', false),
            'for_customer'   => (bool) $this->get('two_factor_for_customer', true),
            'primary_type'   => (string) $this->get('two_factor_primary_type', 'sms'),
        ];
    }

    /**
     * Determine if Two-Factor Authentication is REQUIRED for this user.
     */
    public function isTwoFactorRequiredForUser($user): bool
    {
        if (!$user) return false;

        if (!empty($user->two_factor_enabled)) {
            return true;
        }

        $config = $this->getTwoFactorConfig();
        if (!$config['global_enabled']) {
            return false;
        }

        $isMandatoryMode = ($config['mode'] === 'mandatory');
        if (!$isMandatoryMode) {
            return false;
        }

        $role = str_replace(['-', ' '], '_', strtolower(trim((string)($user->role ?? ''))));
        $userType = str_replace(['-', ' '], '_', strtolower(trim((string)($user->user_type ?? ''))));

        $isSuperOrAdmin = !empty($user->is_super_admin) || in_array($role, ['super_admin', 'admin']) || $userType === 'admin';
        if ($isSuperOrAdmin) {
            return (bool) $config['for_admin'];
        }

        $staffRoles = ['branch_manager', 'staff', 'manager', 'cashier', 'pos_operator', 'operator', 'moderator', 'order_manager', 'courier_manager', 'inventory_manager', 'accounts'];
        $isStaff = in_array($role, $staffRoles) || $userType === 'staff';
        if ($isStaff) {
            return (bool) $config['for_staff'];
        }

        return (bool) $config['for_customer'];
    }

    /**
     * Smartly resolve which channel(s) to dispatch 2FA to.
     */
    public function resolveTwoFactorChannels($user): array
    {
        $hasPhone = !empty($user->phone);
        $hasEmail = !empty($user->email);
        $config   = $this->getTwoFactorConfig();

        if (!empty($user->two_factor_enabled) && !empty($user->two_factor_type)) {
            $preferred = strtolower(trim((string)$user->two_factor_type));
        } else {
            $preferred = strtolower(trim((string)($config['primary_type'] ?? 'sms')));
        }

        $channels = [];

        if ($preferred === 'both' || $preferred === 'sms_and_email') {
            if ($hasPhone) $channels[] = 'sms';
            if ($hasEmail) $channels[] = 'email';
        } elseif ($preferred === 'email') {
            if ($hasEmail) {
                $channels[] = 'email';
            } elseif ($hasPhone) {
                $channels[] = 'sms';
            }
        } else {
            if ($hasPhone) {
                $channels[] = 'sms';
            } elseif ($hasEmail) {
                $channels[] = 'email';
            }
        }

        if (empty($channels)) {
            if ($hasEmail) $channels[] = 'email';
            elseif ($hasPhone) $channels[] = 'sms';
        }

        return $channels;
    }

    /**
     * Bust Cache.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Type casting and encryption utilities.
     */
    protected function castValue(?string $value, string $type): mixed
    {
        if (is_null($value)) {
            return null;
        }

        return match ($type) {
            'boolean'   => in_array($value, ['1', 'true', 'on', 'yes'], true),
            'integer'   => (int) $value,
            'encrypted' => $this->decryptSafely($value),
            'json'      => json_decode($value, true) ?? [],
            default     => $value,
        };
    }

    protected function prepareForStorage(mixed $value, string $type): ?string
    {
        if (is_null($value)) {
            return null;
        }

        return match ($type) {
            'boolean'   => in_array($value, [1, '1', true, 'true', 'on', 'yes'], true) ? '1' : '0',
            'integer'   => (string) ((int) $value),
            'encrypted' => Crypt::encryptString((string) $value),
            'json'      => is_array($value) ? json_encode($value) : (string) $value,
            default     => trim((string) $value),
        };
    }

    protected function decryptSafely(?string $payload): ?string
    {
        if (empty($payload)) {
            return null;
        }

        try {
            return Crypt::decryptString($payload);
        } catch (Throwable) {
            return $payload;
        }
    }

    protected function getHardcodedDefaults(): array
    {
        return [
            'login_email_password'             => true,
            'login_phone_password'             => true,
            'login_phone_otp'                  => false,
            'login_email_otp'                  => false,
            'login_google'                     => false,
            'default_login_method'             => 'email_password',
            'registration_email_enabled'       => true,
            'registration_phone_enabled'       => true,
            'registration_email_required'      => false,
            'registration_phone_required'      => true,
            'registration_otp_verification'    => false,
            'registration_email_verification'  => false,
            'registration_google_enabled'      => false,
            'account_linking_enabled'          => true,
            'google_client_id'                 => null,
            'google_client_secret'             => null,
            'google_redirect_url'              => '/auth/google/callback',
            'otp_expiry_minutes'               => 5,
            'otp_resend_cooldown_seconds'      => 60,
            'otp_max_attempts'                 => 3,
            'otp_daily_limit'                  => 10,
            'two_factor_global'                => false,
            'two_factor_mode'                  => 'optional',
            'two_factor_for_admin'             => true,
            'two_factor_for_staff'             => false,
            'two_factor_for_customer'          => true,
            'two_factor_primary_type'          => 'sms',
        ];
    }
}