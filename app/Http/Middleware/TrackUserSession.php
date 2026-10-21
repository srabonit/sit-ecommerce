<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class TrackUserSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // কাস্টমারদের ইন্টারনাল সেশন মনিটরে ট্র্যাক করা হবে না
        if (method_exists($user, 'isCustomer') && $user->isCustomer()) {
            return $next($request);
        }

        $sessionId = $request->session()->getId();

        // ১. ফোর্স লগআউট বা অ্যাকাউন্ট লক/সাসপেন্ড চেক[cite: 13]
        if (!empty($user->force_logout) || !empty($user->is_locked) || (isset($user->is_active) && !$user->is_active)) {
            $reason = $user->locked_reason ?: 'Your session was remotely revoked or account deactivated.';

            if (Schema::hasTable('active_user_sessions')) {
                DB::table('active_user_sessions')->where('user_id', $user->id)->delete();
            }

            if (!empty($user->force_logout)) {
                DB::table('users')->where('id', $user->id)->update(['force_logout' => false]);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $reason], 401);
            }
            return redirect()->route('admin.login')->with('error', $reason);
        }

        // ২. নির্দিষ্ট সেশন রিভোকড কি না যাচাই[cite: 13]
        if (Schema::hasTable('active_user_sessions') && !empty($sessionId)) {
            $sessionRecord = DB::table('active_user_sessions')
                ->where('session_id', $sessionId)
                ->first();

            if ($sessionRecord && $sessionRecord->is_revoked) {
                DB::table('active_user_sessions')->where('session_id', $sessionId)->delete();

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'This device session has been revoked.'], 401);
                }
                return redirect()->route('admin.login')->with('error', 'আপনার এই ডিভাইসের সেশনটি বাতিল করা হয়েছে।');
            }

            // ৩. ডিভাইস, ব্রাউজার ও ওএস ডিটেকশন[cite: 13]
            $userAgent  = $request->header('User-Agent', '');
            $deviceType = $this->detectDevice($userAgent);
            $browser    = $this->detectBrowser($userAgent);
            $platform   = $this->detectPlatform($userAgent);

            // ৪. সেশন ট্র্যাকিং ডাটা সিঙ্ক (Upsert)[cite: 13]
            if ($sessionRecord) {
                DB::table('active_user_sessions')
                    ->where('session_id', $sessionId)
                    ->update([
                        'user_id'       => $user->id,
                        'ip_address'    => $request->ip(),
                        'user_agent'    => substr($userAgent, 0, 500),
                        'device_type'   => $deviceType,
                        'browser'       => $browser,
                        'platform'      => $platform,
                        'last_activity' => now(),
                        'updated_at'    => now(),
                    ]);
            } else {
                DB::table('active_user_sessions')->insert([
                    'session_id'    => $sessionId,
                    'user_id'       => $user->id,
                    'ip_address'    => $request->ip(),
                    'user_agent'    => substr($userAgent, 0, 500),
                    'device_type'   => $deviceType,
                    'browser'       => $browser,
                    'platform'      => $platform,
                    'last_activity' => now(),
                    'login_at'      => now(),
                    'is_revoked'    => false,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            // ৫. ইউজারের লাস্ট অ্যাক্টিভিটি টাইম আপডেট[cite: 13]
            if (Schema::hasColumn('users', 'last_activity_at')) {
                DB::table('users')->where('id', $user->id)->update(['last_activity_at' => now()]);
            }
        }

        return $next($request);
    }

    protected function detectDevice(string $ua): string
    {
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $ua)) return 'Tablet';
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $ua)) return 'Mobile';
        return 'Desktop';
    }

    protected function detectBrowser(string $ua): string
    {
        if (str_contains($ua, 'Edg')) return 'Edge';
        if (str_contains($ua, 'Chrome')) return 'Chrome';
        if (str_contains($ua, 'Firefox')) return 'Firefox';
        if (str_contains($ua, 'Safari') && !str_contains($ua, 'Chrome')) return 'Safari';
        if (str_contains($ua, 'Opera') || str_contains($ua, 'OPR')) return 'Opera';
        return 'Other';
    }

    protected function detectPlatform(string $ua): string
    {
        if (str_contains($ua, 'Windows')) return 'Windows';
        if (str_contains($ua, 'Macintosh') || str_contains($ua, 'Mac OS X')) return 'macOS';
        if (str_contains($ua, 'Linux')) return 'Linux';
        if (str_contains($ua, 'Android')) return 'Android';
        if (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) return 'iOS';
        return 'Other';
    }
}