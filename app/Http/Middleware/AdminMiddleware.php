<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
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
        // ১. ইউজার লগইন করা আছে কি না যাচাই
        if (!Auth::check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated session. Please log in.'
                ], 401);
            }
            return redirect()->route('admin.login');
        }

        $user = Auth::user();

        // ২. সুপার অ্যাডমিনদের সর্বদা পূর্ণ গেটওয়ে পারমিশন প্রদান (Root Gate)
        if (!empty($user->is_super_admin) || strtolower(trim((string)$user->role)) === 'super_admin') {
            return $next($request);
        }

        // ৩. ইউজার অ্যাকাউন্ট লক করা কি না যাচাই (StaffUserController lock/unlock সাপোর্ট)
        if (!empty($user->is_locked)) {
            $reason = $user->locked_reason ?: 'নিরাপত্তাজনিত কারণে আপনার অ্যাকাউন্টটি সাময়িকভাবে লক করা হয়েছে।';
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $reason], 403);
            }
            return redirect()->route('admin.login')->with('error', $reason);
        }

        // ৪. অ্যাকাউন্ট নিষ্ক্রিয় বা ব্লকড কি না যাচাই (Active / Inactive)
        if (isset($user->is_active) && !$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Your account is deactivated.'], 403);
            }
            return redirect()->route('admin.login')->with('error', 'আপনার অ্যাকাউন্টটি নিষ্ক্রিয় (Deactivated) করা হয়েছে। কর্তৃপক্ষের সাথে যোগাযোগ করুন।');
        }

        // ৫. স্ট্যাটাস ফিল্ড চেক (Suspended, Frozen, Blocked, Inactive)
        if (isset($user->status) && in_array(strtolower((string)$user->status), ['inactive', 'suspended', 'frozen', 'blocked'])) {
            $statusText = ucfirst($user->status);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => "Your account has been {$statusText}."], 403);
            }
            return redirect()->route('admin.login')->with('error', "আপনার অ্যাকাউন্টটি {$statusText} অবস্থায় রয়েছে। অ্যাডমিনের সাথে যোগাযোগ করুন।");
        }

        // ৬. রিমোট ফোর্স লগআউট ফ্ল্যাগ চেক (Force Logout Signal)
        if (!empty($user->force_logout)) {
            if (Schema::hasColumn('users', 'force_logout')) {
                DB::table('users')->where('id', $user->id)->update(['force_logout' => false]);
            }
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Session terminated remotely.'], 401);
            }
            return redirect()->route('admin.login')->with('error', 'আপনার সেশনটি কর্তৃপক্ষ রিমোটলি বন্ধ করেছে।');
        }

        // ৭. রোল ও ইউজার টাইপ নরমালাইজেশন (Space ও Underscore সাপোর্ট)
        $rawRole  = trim((string)($user->role ?? ''));
        $userRole = str_replace(' ', '_', strtolower($rawRole));

        $rawType  = trim((string)($user->user_type ?? ''));
        $userType = str_replace(' ', '_', strtolower($rawType));

        // ৮. কাস্টমারদের অভ্যন্তরীণ অ্যাডমিন প্যানেলে প্রবেশ কঠোরভাবে নিষিদ্ধ
        if ($userRole === 'customer' && $userType === 'customer') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access Denied: Customer accounts are not allowed to access admin dashboard.'
                ], 403);
            }
            return redirect()->route('home')->with('error', 'গ্রাহক অ্যাকাউন্টের মাধ্যমে অ্যাডমিন ড্যাশবোর্ডে প্রবেশের অনুমতি নেই।');
        }

        // ৯. সিস্টেমের সাধারণ অভ্যন্তরীণ স্টাফ রোলসমূহ চেক
        $allowedInternalRoles = [
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

        $isStandardStaff = in_array($userRole, $allowedInternalRoles) || in_array($userType, ['admin', 'staff']);

        // ১০. ডেটাবেসে থাকা ডায়নামিক কাস্টম রোল অনুমোদন (role_route_permissions টেবিল)
        $hasDynamicRole = false;
        if (Schema::hasTable('role_route_permissions')) {
            $hasDynamicRole = DB::table('role_route_permissions')
                ->where('role_name', $rawRole)
                ->orWhere('role_name', $userRole)
                ->orWhere('role_name', str_replace('_', ' ', $userRole))
                ->exists();
        }

        if (!$isStandardStaff && !$hasDynamicRole) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized Access: You do not have valid staff permissions.'
                ], 403);
            }

            return redirect()->route('admin.login')->with('error', 'আপনার এই প্যানেলে প্রবেশের কোনো অনুমোদিত রোল নেই।');
        }

        // ১১. INSTANT KICK-OUT: রোলের কোনো পারমিশন সক্রিয় না থাকলে তাৎক্ষণিক লগআউট করে বের করে দেওয়া
        if (Schema::hasTable('role_route_permissions') && $userRole !== 'admin') {
            $hasActivePermissions = DB::table('role_route_permissions')
                ->where(function ($q) use ($rawRole, $userRole) {
                    $q->where('role_name', $rawRole)
                      ->orWhere('role_name', $userRole)
                      ->orWhere('role_name', str_replace('_', ' ', $userRole));
                })
                ->where(function ($q) {
                    $q->where('can_read', 1)
                      ->orWhere('can_create', 1)
                      ->orWhere('can_update', 1)
                      ->orWhere('can_delete', 1);
                })
                ->exists();

            if (!$hasActivePermissions) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access Revoked: Your assigned role currently has zero active permissions.'
                    ], 403);
                }

                return redirect()->route('admin.login')->with('error', 'আপনার রোলের সমস্ত পারমিশন বন্ধ করা হয়েছে। সিস্টেমে আপনার কোনো প্রবেশাধিকার নেই।');
            }
        }

        return $next($request);
    }
}