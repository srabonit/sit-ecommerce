<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\AuthenticationSettingsService;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Throwable;

class SocialAuthController extends Controller
{
    protected AuthenticationSettingsService $authSettings;

    public function __construct(AuthenticationSettingsService $authSettings)
    {
        $this->authSettings = $authSettings;
    }

    /**
     * Check if Laravel Socialite package is installed.
     */
    protected function isSocialiteInstalled(): bool
    {
        return class_exists(Socialite::class);
    }

    /**
     * Dynamically resolve the absolute Redirect URI based on the current active domain and scheme.
     * Prevents old domain collisions when the project is moved to another server.
     */
    protected function resolveDynamicRedirectUri(array $config): string
    {
        $rawRedirect = $config['redirect_url'] ?? '/auth/google/callback';
        $path = parse_url($rawRedirect, PHP_URL_PATH) ?: '/auth/google/callback';

        $isHttps = request()->isSecure()
            || strtolower((string) request()->header('X-Forwarded-Proto')) === 'https'
            || strtolower((string) request()->header('X-Forwarded-Ssl')) === 'on'
            || strtolower((string) env('FORCE_HTTPS', 'false')) === 'true';

        return $isHttps ? secure_url($path) : url($path);
    }

    /**
     * Determine whether SSL verification should be enabled based on host and environment.
     */
    protected function shouldVerifySsl(): bool
    {
        $host = strtolower(trim((string) request()->getHost()));

        // Localhost, IP, and development domains disable SSL verification
        if (
            app()->isLocal()
            || in_array($host, ['127.0.0.1', 'localhost', '::1'])
            || str_ends_with($host, '.test')
            || str_ends_with($host, '.local')
        ) {
            return false;
        }

        return true;
    }

    /**
     * Build dynamic Google Socialite Provider instance with smart environment adaptation.
     */
    protected function getGoogleProvider(bool $forceDisableSsl = false)
    {
        if (!$this->isSocialiteInstalled()) {
            return null;
        }

        $config = $this->authSettings->getGoogleConfig();

        if (empty($config['client_id']) || empty($config['client_secret'])) {
            return null;
        }

        $redirectUrl = $this->resolveDynamicRedirectUri($config);

        Config::set('services.google', [
            'client_id'     => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'redirect'      => $redirectUrl,
        ]);

        $verifySsl = $forceDisableSsl ? false : $this->shouldVerifySsl();

        $guzzleClient = new GuzzleClient([
            'verify'          => $verifySsl,
            'timeout'         => 20,
            'connect_timeout' => 10,
        ]);

        try {
            if (class_exists(GoogleProvider::class)) {
                $provider = Socialite::buildProvider(
                    GoogleProvider::class,
                    [
                        'client_id'     => $config['client_id'],
                        'client_secret' => $config['client_secret'],
                        'redirect'      => $redirectUrl,
                    ]
                );
                return $provider->setHttpClient($guzzleClient);
            }
            return Socialite::driver('google')->setHttpClient($guzzleClient);
        } catch (Throwable $e) {
            Log::error('Google Provider Build Error: ' . $e->getMessage());
            return Socialite::driver('google')->setHttpClient($guzzleClient);
        }
    }

    /**
     * Redirect user to Google OAuth Consent screen.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        if (!$this->isSocialiteInstalled()) {
            return redirect()->route('login')->with('error', 'Google Socialite package is not installed. Please run "composer require laravel/socialite".');
        }

        if (!$this->authSettings->isLoginMethodEnabled('google')) {
            return redirect()->route('login')->with('error', 'Google Sign-in is currently disabled by administrator.');
        }

        $provider = $this->getGoogleProvider();

        if (!$provider) {
            return redirect()->route('login')->with('error', 'Google OAuth credentials (Client ID or Client Secret) are not configured properly in Admin Settings.');
        }

        try {
            return $provider
                ->scopes(['openid', 'profile', 'email'])
                ->redirect();
        } catch (Throwable $e) {
            Log::error('Google OAuth Redirect Error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Could not initiate Google sign in: ' . $e->getMessage());
        }
    }

    /**
     * Handle incoming Google OAuth Callback.
     * Incorporates automatic fallback for SSL issues (cURL 60) and stateless sessions.
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        if (!$this->isSocialiteInstalled()) {
            return redirect()->route('login')->with('error', 'Google Socialite package is not installed.');
        }

        if (!$this->authSettings->isLoginMethodEnabled('google')) {
            return redirect()->route('login')->with('error', 'Google Sign-in is currently disabled.');
        }

        $provider = $this->getGoogleProvider();

        if (!$provider) {
            return redirect()->route('login')->with('error', 'Google service configuration missing in Admin Settings.');
        }

        if ($request->has('error')) {
            return redirect()->route('login')->with('error', 'Google authentication was cancelled: ' . $request->get('error'));
        }

        $googleUser = null;

        // Step 1: Standard retrieval
        try {
            $googleUser = $provider->user();
        } catch (Throwable $e) {
            // Step 2: Stateless fallback for session/cookie mismatch
            try {
                $googleUser = $provider->stateless()->user();
            } catch (Throwable $ex) {
                // Step 3: cURL 60 SSL Auto-Recovery Fallback (works across misconfigured hosts)
                if (str_contains($ex->getMessage(), 'cURL error 60') || str_contains($ex->getMessage(), 'certificate')) {
                    try {
                        $fallbackProvider = $this->getGoogleProvider(true);
                        $googleUser = $fallbackProvider ? $fallbackProvider->stateless()->user() : null;
                    } catch (Throwable $fallbackEx) {
                        Log::error('Google OAuth Fallback Error: ' . $fallbackEx->getMessage());
                    }
                }

                if (!$googleUser) {
                    Log::error('Google OAuth Callback Final Error: ' . $ex->getMessage());
                    return redirect()->route('login')->with('error', 'Google authentication failed: ' . $ex->getMessage());
                }
            }
        }

        if (!$googleUser) {
            return redirect()->route('login')->with('error', 'Failed to retrieve user profile from Google.');
        }

        $googleId      = $googleUser->getId();
        $email         = strtolower(trim((string) $googleUser->getEmail()));
        $name          = trim((string) $googleUser->getName()) ?: 'Google User';
        $avatar        = $googleUser->getAvatar();
        $emailVerified = (bool) ($googleUser->user['email_verified'] ?? true);

        if (empty($email) || !$emailVerified) {
            return redirect()->route('login')->with('error', 'Google account does not possess a verified email address.');
        }

        // 1. Check if user already exists by google_id
        $user = User::withTrashed()->where('google_id', $googleId)->first();

        // 2. Fallback check by verified email address
        if (!$user) {
            $user = User::withTrashed()->where('email', $email)->first();

            if ($user) {
                if (!$this->authSettings->isAccountLinkingEnabled()) {
                    return redirect()->route('login')->with('error', 'An account already exists with this email. Automatic account linking is disabled.');
                }

                $user->google_id = $googleId;
                if (empty($user->google_avatar)) {
                    $user->google_avatar = $avatar;
                }
                if (is_null($user->email_verified_at)) {
                    $user->email_verified_at = now();
                }
                $user->save();
            }
        }

        // 3. New User Registration flow
        if (!$user) {
            if (!$this->authSettings->get('registration_google_enabled', false)) {
                return redirect()->route('login')->with('error', 'Registration with Google is not allowed. Please register with Phone/Email first.');
            }

            $user = new User();
            $user->name              = $name;
            $user->email             = $email;
            $user->email_verified_at = now();
            $user->phone             = null;
            $user->google_id         = $googleId;
            $user->google_avatar     = $avatar;
            $user->password          = Hash::make(Str::random(32));
            $user->role              = 'customer';
            $user->user_type         = 'customer';
            $user->is_active         = true;
            $user->is_locked         = false;
            $user->is_super_admin    = false;
            $user->save();
        }

        // 4. Restore soft-deleted account within 30 days
        if ($user->trashed()) {
            $user->restore();
            $user->update(['is_active' => 1]);
        }

        // 5. Account lock and deactivation guard
        if (!empty($user->is_locked) || (isset($user->is_active) && !$user->is_active)) {
            return redirect()->route('login')->with('error', 'Your account is locked or deactivated. Please contact support.');
        }

        // 6. Two-Factor Authentication Guard for Google Login
        if ($this->authSettings->isTwoFactorRequiredForUser($user)) {
            return app(AuthController::class)->initiateTwoFactorChallenge($request, $user, true);
        }

        // 7. Complete authentication session
        Auth::login($user, true);
        $request->session()->regenerate();

        if ($this->isStaffOrAdmin($user)) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended(route('user.dashboard'))->with('success', 'Signed in with Google successfully!');
    }

    protected function isStaffOrAdmin($user): bool
    {
        if (!$user) return false;

        if (!empty($user->is_super_admin) || strtolower(trim((string)$user->role)) === 'super_admin') {
            return true;
        }

        $role = str_replace(' ', '_', strtolower(trim((string)($user->role ?? ''))));
        $staffRoles = [
            'admin', 'super_admin', 'branch_manager', 'staff', 'manager',
            'cashier', 'pos_operator', 'operator', 'moderator',
            'order_manager', 'courier_manager', 'inventory_manager', 'accounts'
        ];

        return in_array($role, $staffRoles);
    }
}