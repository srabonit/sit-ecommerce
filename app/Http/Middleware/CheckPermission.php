<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * ইউজারের অনুমোদিত প্রথম রাউট খুঁজে বের করা (Fallback Redirection)
     */
    protected function getFirstPermittedRoute($user): ?string
    {
        $priorityRoutes = [
            'admin.pos.terminal',
            'admin.pos.index',
            'admin.order.online',
            'admin.product.index',
            'admin.courier.index',
            'admin.branch.orders',
            'admin.branch.products',
            'admin.support.index',
            'admin.accounts.transactions',
            'admin.report.sales',
            'admin.forms.index',
            'admin.content.pages.index',
            'admin.shipping.index',
            'admin.theme.setting.home_builder',
            'admin.roles.users',
        ];

        foreach ($priorityRoutes as $rName) {
            if ($user->hasAccess($rName)) {
                return $rName;
            }
        }

        return null;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $permission
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        if (!Auth::check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated session.'], 401);
            }
            return redirect()->route('admin.login');
        }

        $user = Auth::user();

        // সুপার অ্যাডমিনদের সকল ক্ষেত্রে পূর্ণ অনুমোদন
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // কাস্টমারদের কোনো ইন্টারনাল অ্যাক্সেস নেই
        if ($user->isCustomer()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Access Denied: Customer accounts are forbidden.'], 403);
            }
            abort(403, 'Unauthorized access.');
        }

        $routeName = $request->route() ? $request->route()->getName() : null;
        $targetPermission = $permission ?: $routeName;

        if (empty($targetPermission)) {
            return $next($request);
        }

        // প্রোফাইল ও সার্চ বাইপাস
        $commonInternalRoutes = [
            'admin.search.ajax',
            'admin.profile.index',
            'admin.profile.password',
        ];
        if (in_array($targetPermission, $commonInternalRoutes)) {
            return $next($request);
        }

        // পারমিশন সক্রিয় থাকলে অনুমোদন
        if ($user->hasAccess($targetPermission)) {
            return $next($request);
        }

        // পারমিশন না থাকলে তাৎক্ষণিক ব্যবস্থা
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => "Access Denied: You do not have permission to execute '{$targetPermission}'."
            ], 403);
        }

        // যদি ড্যাশবোর্ড পারমিশন অফ থাকা অবস্থায় ড্যাশবোর্ডে ঢুকতে চায়:
        if ($targetPermission === 'admin.dashboard') {
            $fallback = $this->getFirstPermittedRoute($user);
            if ($fallback) {
                return redirect()->route($fallback)->with('error', 'আপনার ড্যাশবোর্ড অ্যাক্সেস বন্ধ রয়েছে। আপনাকে অনুমোদিত পেজে পাঠানো হয়েছে।');
            }
            abort(403, 'Access Denied: You do not have permission to access Main Dashboard or any administrative modules.');
        }

        if ($request->isMethod('get')) {
            abort(403, "Access Denied: You do not have permission to access '{$targetPermission}'.");
        }

        return back()->with('error', "Access Denied: You do not have permission to perform this action ({$targetPermission}).");
    }
}