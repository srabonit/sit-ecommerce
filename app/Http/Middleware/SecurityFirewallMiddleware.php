<?php

namespace App\Http\Middleware;

use App\Models\SecurityAllowlist;
use App\Models\SecurityIpLog;
use App\Models\SecuritySetting;
use App\Models\SecurityUrlAttack;
use App\Models\SecurityUserRestriction;
use App\Services\Security\IpBlockService;
use App\Services\Security\IpDetectionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityFirewallMiddleware
{
    protected IpDetectionService $ipDetector;
    protected IpBlockService $blockService;

    public function __construct(IpDetectionService $ipDetector, IpBlockService $blockService)
    {
        $this->ipDetector = $ipDetector;
        $this->blockService = $blockService;
    }

    /**
     * Incoming request jachai, admin/staff traffic filtering ebong firewall enforcements
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Master security ba firewall bondho thakle shorashori bypass
        if (!SecuritySetting::isEnabled('security_master_active', true) ||
            !SecuritySetting::isEnabled('security_firewall_active', true)) {
            return $next($request);
        }

        $ip = $this->ipDetector->getClientIp($request);
        $currentPath = '/' . ltrim($request->path(), '/');
        $isStaffOrAdmin = $this->isStaffOrAdminUser($request);

        // 2. Admin ba Staff traffic handling (Internal working route requests exclude kora)
        $isAdminRoute = $request->is('admin*') || str_starts_with($currentPath, '/admin') || $request->is('dashboard/auth*');

        if ($isStaffOrAdmin && $isAdminRoute) {
            // Admin jeno nijer panel-e kokhono lock-out na hon ebong visitor threat logs-e count na hon
            return $next($request);
        }

        // 3. IP ba CIDR network blocklist jachai
        if ($this->blockService->isBlocked($ip)) {
            // Payment gateway webhook ba IPN bypass
            $isPaymentWebhook = $request->is('*ipn*') || $request->is('*callback*') || $request->is('*payment*');
            if ($isPaymentWebhook && SecurityAllowlist::isAllowed($ip, 'firewall_only', $currentPath)) {
                return $next($request);
            }

            // Admin panel safe-access bypass (jodi admin login thaken)
            if ($isStaffOrAdmin && $isAdminRoute) {
                return $next($request);
            }

            $blockDetails = $this->blockService->getBlockReasonDetails($ip);

            // Blocked request forensic logging
            SecurityIpLog::record([
                'ip_address'      => $ip,
                'ip_version'      => $this->ipDetector->getIpVersion($ip),
                'country'         => $this->ipDetector->getCountry($request) ?: 'Unknown',
                'user_id'         => $request->user()?->id,
                'user_type'       => $this->resolveUserType($request),
                'session_id'      => $request->hasSession() ? $request->session()->getId() : null,
                'device_type'     => $this->ipDetector->getDeviceType($request),
                'browser'         => $this->ipDetector->getBrowser($request),
                'platform'        => $this->ipDetector->getPlatform($request),
                'user_agent'      => substr((string) $request->header('User-Agent', ''), 0, 255),
                'request_method'  => $request->method(),
                'request_url'     => $request->fullUrl(),
                'url_path'        => $currentPath,
                'referrer_url'    => $request->header('referer'),
                'response_status' => 403,
                'activity_type'   => 'firewall_blocked',
                'risk_score'      => 100,
                'security_status' => 'blocked',
                'metadata'        => [
                    'block_type'   => $blockDetails['type'] ?? 'unknown',
                    'block_reason' => $blockDetails['reason'] ?? null,
                ],
            ]);

            // Attacked URL tracking
            SecurityUrlAttack::recordAttack(
                $currentPath,
                $request->method(),
                true,
                true,
                $ip
            );

            return $this->respondBlockedAccess($request, $ip, $blockDetails);
        }

        // 4. Allowlist o trusted endpoints jachai
        if (SecurityAllowlist::isAllowed($ip, 'firewall_only', $currentPath)) {
            return $next($request);
        }

        // 5. User account restriction jachai (Staff/Admin hole apply hobe na)
        if ($request->user() && !$isStaffOrAdmin) {
            $userId = $request->user()->id;

            if (SecurityUserRestriction::isUserRestricted($userId)) {
                return $this->respondRestrictedUser($request, 'account_locked');
            }

            if ($request->is('*checkout*') && SecurityUserRestriction::isUserRestricted($userId, 'checkout')) {
                return $this->respondRestrictedUser($request, 'checkout_restricted');
            }

            if (($request->is('*form*') || $request->is('*contact*')) && $request->isMethod('POST')) {
                if (SecurityUserRestriction::isUserRestricted($userId, 'form')) {
                    return $this->respondRestrictedUser($request, 'form_restricted');
                }
            }
        }

        return $next($request);
    }

    /**
     * User Admin ba Staff kina ta shothikbhabe identify korar robust method
     */
    public function isStaffOrAdminUser(Request $request): bool
    {
        $user = $request->user();
        if (!$user) {
            return false;
        }

        // 1. Boolean flag check
        if (!empty($user->is_admin) || !empty($user->is_staff)) {
            return true;
        }

        // 2. Role string ba Role relation object check
        $roleName = '';
        if (is_string($user->role ?? null)) {
            $roleName = strtolower(trim($user->role));
        } elseif (is_object($user->role ?? null)) {
            $roleName = strtolower(trim($user->role->slug ?? ($user->role->name ?? '')));
        } elseif (!empty($user->role_name)) {
            $roleName = strtolower(trim($user->role_name));
        } elseif (!empty($user->user_type)) {
            $roleName = strtolower(trim($user->user_type));
        }

        $staffRoles = [
            'admin',
            'super_admin',
            'superadmin',
            'staff',
            'manager',
            'editor',
            'moderator',
            'support',
            'employee',
            'cashier',
        ];

        return in_array($roleName, $staffRoles, true);
    }

    /**
     * User-er accurate category resolve kora (admin, staff, customer, guest)
     */
    protected function resolveUserType(Request $request): string
    {
        $user = $request->user();
        if (!$user) {
            return 'guest';
        }

        if (!empty($user->is_admin)) {
            return 'admin';
        }

        if (!empty($user->is_staff)) {
            return 'staff';
        }

        $role = '';
        if (is_string($user->role ?? null)) {
            $role = strtolower(trim($user->role));
        } elseif (is_object($user->role ?? null)) {
            $role = strtolower(trim($user->role->slug ?? ($user->role->name ?? '')));
        }

        if (in_array($role, ['admin', 'super_admin', 'superadmin'], true)) {
            return 'admin';
        }

        if (in_array($role, ['staff', 'manager', 'editor', 'moderator', 'support', 'employee', 'cashier'], true)) {
            return 'staff';
        }

        return 'customer';
    }

    /**
     * Blocked IP-r jonno response toiri
     */
    protected function respondBlockedAccess(Request $request, string $ip, array $details): Response
    {
        $message = "Access Denied: Your IP address ({$ip}) has been restricted due to security violations.";
        $reason = $details['reason'] ?? 'Suspicious activity or policy violation';
        $expires = $details['expires_at'] ?? 'Permanent';
        $blockType = ucfirst($details['type'] ?? 'Firewall Restriction');

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status'          => 'error',
                'security_status' => 'access_blocked',
                'message'         => $message,
                'reason'          => $reason,
                'expires_at'      => $expires,
            ], 403);
        }

        $html = <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>403 - Access Restricted | Network Security</title>
            <style>
                body { margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
                .card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; max-width: 520px; width: 90%; padding: 32px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5); text-align: center; }
                .icon { width: 64px; height: 64px; background: rgba(239, 68, 68, 0.15); color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 28px; }
                h1 { margin: 0 0 10px; font-size: 24px; color: #f1f5f9; }
                p { margin: 0 0 20px; color: #94a3b8; font-size: 15px; line-height: 1.5; }
                .details { background: #0f172a; border-radius: 8px; padding: 14px 18px; text-align: left; font-size: 13px; color: #cbd5e1; margin-bottom: 24px; }
                .details-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #1e293b; }
                .details-row:last-child { border-bottom: none; }
                .label { color: #64748b; font-weight: 500; }
                .val { font-family: monospace; font-weight: 600; color: #e2e8f0; }
                .footer { font-size: 12px; color: #64748b; margin-top: 16px; }
            </style>
        </head>
        <body>
            <div class="card">
                <div class="icon">✕</div>
                <h1>Access Denied</h1>
                <p>Your network request has been blocked by the enterprise security firewall.</p>
                <div class="details">
                    <div class="details-row"><span class="label">Client IP:</span><span class="val">{$ip}</span></div>
                    <div class="details-row"><span class="label">Block Type:</span><span class="val">{$blockType}</span></div>
                    <div class="details-row"><span class="label">Reason:</span><span class="val">{$reason}</span></div>
                    <div class="details-row"><span class="label">Restriction Expiry:</span><span class="val">{$expires}</span></div>
                </div>
                <div class="footer">If you believe this restriction is a mistake, please contact store support with your IP address.</div>
            </div>
        </body>
        </html>
        HTML;

        return response($html, 403)->header('Content-Type', 'text/html');
    }

    /**
     * Restricted user-er jonno error response
     */
    protected function respondRestrictedUser(Request $request, string $type): Response
    {
        $messages = [
            'account_locked'      => 'Your customer account is currently suspended due to security compliance review.',
            'checkout_restricted' => 'Order placement has been temporarily restricted on your account. Please contact customer care.',
            'form_restricted'     => 'Form submissions are temporarily restricted on your account.',
        ];

        $msg = $messages[$type] ?? 'Action restricted by account security policy.';

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status'          => 'error',
                'security_status' => $type,
                'message'         => $msg,
            ], 403);
        }

        return redirect()->back()->with('error', $msg);
    }
}