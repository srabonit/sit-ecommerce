<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('authentication_settings')) {
            Schema::create('authentication_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 100)->unique();
                $table->text('value')->nullable();
                $table->string('type', 30)->default('boolean'); // 'boolean', 'string', 'integer', 'encrypted', 'json'
                $table->string('group', 50)->default('general'); // 'login', 'registration', 'otp', '2fa', 'google'
                $table->string('label', 150)->nullable();
                $table->timestamps();
            });
        }

        // Seed safe default settings matching existing architecture
        $now = now();
        $defaults = [
            // --- 1. Login Methods ---
            [
                'key'        => 'login_email_password',
                'value'      => '1', // Enabled by default
                'type'       => 'boolean',
                'group'      => 'login',
                'label'      => 'Email + Password Login',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'login_phone_password',
                'value'      => '1', // Enabled (matches existing AuthController email/phone detection)
                'type'       => 'boolean',
                'group'      => 'login',
                'label'      => 'Phone Number + Password Login',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'login_phone_otp',
                'value'      => '0', // Disabled until configured by Admin
                'type'       => 'boolean',
                'group'      => 'login',
                'label'      => 'Phone Number + OTP Passwordless Login',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'login_email_otp',
                'value'      => '0',
                'type'       => 'boolean',
                'group'      => 'login',
                'label'      => 'Email + OTP Login',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'login_google',
                'value'      => '0',
                'type'       => 'boolean',
                'group'      => 'login',
                'label'      => 'Google OAuth Login',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'default_login_method',
                'value'      => 'email_password',
                'type'       => 'string',
                'group'      => 'login',
                'label'      => 'Default Active Login Method',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // --- 2. Registration Rules ---
            [
                'key'        => 'registration_email_enabled',
                'value'      => '1',
                'type'       => 'boolean',
                'group'      => 'registration',
                'label'      => 'Email Registration',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'registration_phone_enabled',
                'value'      => '1',
                'type'       => 'boolean',
                'group'      => 'registration',
                'label'      => 'Phone Number Registration',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'registration_email_required',
                'value'      => '0', // Optional by default matching current flow
                'type'       => 'boolean',
                'group'      => 'registration',
                'label'      => 'Email Address Required',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'registration_phone_required',
                'value'      => '1', // Required by default matching current flow
                'type'       => 'boolean',
                'group'      => 'registration',
                'label'      => 'Phone Number Required',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'registration_otp_verification',
                'value'      => '0',
                'type'       => 'boolean',
                'group'      => 'registration',
                'label'      => 'Require OTP Verification upon Register',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'registration_email_verification',
                'value'      => '0',
                'type'       => 'boolean',
                'group'      => 'registration',
                'label'      => 'Require Email Link Verification',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'registration_google_enabled',
                'value'      => '0',
                'type'       => 'boolean',
                'group'      => 'registration',
                'label'      => 'Auto-Register New Users with Google',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'account_linking_enabled',
                'value'      => '1',
                'type'       => 'boolean',
                'group'      => 'registration',
                'label'      => 'Link Existing Accounts by Verified Email/Phone',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // --- 3. Google OAuth Credentials ---
            [
                'key'        => 'google_client_id',
                'value'      => null,
                'type'       => 'string',
                'group'      => 'google',
                'label'      => 'Google Client ID',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'google_client_secret',
                'value'      => null,
                'type'       => 'encrypted',
                'group'      => 'google',
                'label'      => 'Google Client Secret',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'google_redirect_url',
                'value'      => '/auth/google/callback',
                'type'       => 'string',
                'group'      => 'google',
                'label'      => 'Google OAuth Callback URL',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // --- 4. OTP Lifecycle & Attack Protection ---
            [
                'key'        => 'otp_expiry_minutes',
                'value'      => '5',
                'type'       => 'integer',
                'group'      => 'otp',
                'label'      => 'OTP Validity Lifetime (Minutes)',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'otp_resend_cooldown_seconds',
                'value'      => '60',
                'type'       => 'integer',
                'group'      => 'otp',
                'label'      => 'Resend Button Cooldown (Seconds)',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'otp_max_attempts',
                'value'      => '3',
                'type'       => 'integer',
                'group'      => 'otp',
                'label'      => 'Maximum Failed OTP Verification Attempts',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'otp_daily_limit',
                'value'      => '10',
                'type'       => 'integer',
                'group'      => 'otp',
                'label'      => 'Daily OTP Dispatch Limit per Identifier',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // --- 5. Two-Factor Authentication (2FA) ---
            [
                'key'        => 'two_factor_global',
                'value'      => '0',
                'type'       => 'boolean',
                'group'      => '2fa',
                'label'      => 'Global Two-Factor Authentication',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'two_factor_mode',
                'value'      => 'optional', // 'optional' or 'mandatory'
                'type'       => 'string',
                'group'      => '2fa',
                'label'      => '2FA Enforcement Mode',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'two_factor_for_admin',
                'value'      => '0',
                'type'       => 'boolean',
                'group'      => '2fa',
                'label'      => 'Mandatory 2FA for Admin Accounts',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'two_factor_for_staff',
                'value'      => '0',
                'type'       => 'boolean',
                'group'      => '2fa',
                'label'      => 'Mandatory 2FA for Staff Accounts',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'two_factor_for_customer',
                'value'      => '0',
                'type'       => 'boolean',
                'group'      => '2fa',
                'label'      => 'Allow 2FA for Customer Accounts',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'two_factor_primary_type',
                'value'      => 'sms', // 'sms' or 'email'
                'type'       => 'string',
                'group'      => '2fa',
                'label'      => 'Primary 2FA Dispatch Channel',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($defaults as $setting) {
            DB::table('authentication_settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authentication_settings');
    }
};