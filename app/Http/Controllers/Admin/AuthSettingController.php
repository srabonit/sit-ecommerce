<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthenticationSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use Throwable;

class AuthSettingController extends Controller
{
    protected AuthenticationSettingsService $authSettings;

    public function __construct(AuthenticationSettingsService $authSettings)
    {
        $this->authSettings = $authSettings;
    }

    /**
     * Display the Central Authentication Settings Management Panel.
     */
    public function index(): View
    {
        $settings = $this->authSettings->all();

        $rawSecret = $this->authSettings->get('google_client_secret');
        $hasGoogleSecret = !empty($rawSecret) && !str_contains((string)$rawSecret, '•••');
        $enabledLoginMethods = $this->authSettings->getEnabledLoginMethods();
        $defaultLoginMethod = $this->authSettings->getDefaultLoginMethod();

        return view('admin.settings.auth_settings', compact(
            'settings',
            'hasGoogleSecret',
            'enabledLoginMethods',
            'defaultLoginMethod'
        ));
    }

    /**
     * Update Authentication & Security settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            // Default method must be a valid key
            'default_login_method'          => 'required|string|in:email_password,phone_password,phone_otp,email_otp,google',

            // Google OAuth Configuration
            'google_client_id'              => 'nullable|string|max:500',
            'google_client_secret'          => 'nullable|string|max:500',
            'google_redirect_url'           => 'nullable|string|max:500',

            // OTP Configuration & Security Limits
            'otp_expiry_minutes'            => 'required|integer|min:1|max:60',
            'otp_resend_cooldown_seconds'   => 'required|integer|min:10|max:300',
            'otp_max_attempts'              => 'required|integer|min:1|max:10',
            'otp_daily_limit'               => 'required|integer|min:1|max:100',

            // Two-Factor Modes & Channels (Supports: sms, email, both)
            'two_factor_mode'               => 'required|string|in:optional,mandatory',
            'two_factor_primary_type'       => 'required|string|in:sms,email,both',
        ]);

        // Explicit boolean toggle normalization
        $inputs = [
            // 1. Login Methods
            'login_email_password'            => $request->boolean('login_email_password') ? '1' : '0',
            'login_phone_password'            => $request->boolean('login_phone_password') ? '1' : '0',
            'login_phone_otp'                 => $request->boolean('login_phone_otp') ? '1' : '0',
            'login_email_otp'                 => $request->boolean('login_email_otp') ? '1' : '0',
            'login_google'                    => $request->boolean('login_google') ? '1' : '0',
            'default_login_method'            => $request->input('default_login_method'),

            // 2. Registration Rules
            'registration_email_enabled'      => $request->boolean('registration_email_enabled') ? '1' : '0',
            'registration_phone_enabled'      => $request->boolean('registration_phone_enabled') ? '1' : '0',
            'registration_email_required'     => $request->boolean('registration_email_required') ? '1' : '0',
            'registration_phone_required'     => $request->boolean('registration_phone_required') ? '1' : '0',
            'registration_otp_verification'   => $request->boolean('registration_otp_verification') ? '1' : '0',
            'registration_email_verification' => $request->boolean('registration_email_verification') ? '1' : '0',
            'registration_google_enabled'     => $request->boolean('registration_google_enabled') ? '1' : '0',
            'account_linking_enabled'         => $request->boolean('account_linking_enabled') ? '1' : '0',

            // 3. Google OAuth Settings (Clean trimmed strings)
            'google_client_id'                => $request->filled('google_client_id') ? trim((string)$request->input('google_client_id')) : null,
            'google_redirect_url'             => $request->filled('google_redirect_url') ? trim((string)$request->input('google_redirect_url')) : '/auth/google/callback',

            // 4. OTP Limits
            'otp_expiry_minutes'              => (string) $request->input('otp_expiry_minutes'),
            'otp_resend_cooldown_seconds'     => (string) $request->input('otp_resend_cooldown_seconds'),
            'otp_max_attempts'                => (string) $request->input('otp_max_attempts'),
            'otp_daily_limit'                 => (string) $request->input('otp_daily_limit'),

            // 5. Two-Factor Controls & Role Policies
            'two_factor_global'               => $request->boolean('two_factor_global') ? '1' : '0',
            'two_factor_mode'                 => $request->input('two_factor_mode'),
            'two_factor_primary_type'         => $request->input('two_factor_primary_type'),
            'two_factor_for_admin'            => $request->boolean('two_factor_for_admin') ? '1' : '0',
            'two_factor_for_staff'            => $request->boolean('two_factor_for_staff') ? '1' : '0',
            'two_factor_for_customer'         => $request->boolean('two_factor_for_customer') ? '1' : '0',
        ];

        // Google Client Secret update (Protects existing secret if bullets/dots submitted, updates when real string entered)
        $secretInput = trim((string) $request->input('google_client_secret', ''));
        if (!empty($secretInput) && !str_contains($secretInput, '•')) {
            $inputs['google_client_secret'] = $secretInput;
        }

        try {
            $this->authSettings->updateSettings($inputs);

            return back()->with('success', 'Authentication and security settings updated successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
}