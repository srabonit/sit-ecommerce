<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * User-er onumodito prothom safe route khuje ber kora (Fallback Redirection)
     * Shokol existing o notun added modules er landing routes ekhane sequence kora hoyeche.
     */
    protected function getFirstPermittedRoute($user): ?string
    {
        // Strictly parameter-less landing routes priority matrix
        $priorityRoutes = [
            'admin.pos.index',
            'admin.order.online',
            'admin.inventory.dashboard',
            'admin.inventory.balance',
            'admin.suppliers.index',
            'admin.purchase-orders.index',
            'admin.purchases.index',
            'admin.purchase-receipts.index',
            'admin.courier.dashboard',
            'admin.courier.index',
            'admin.product.index',
            'admin.product.reviews.index',
            'admin.branch.orders',
            'admin.branch.products',
            'admin.branch.index',
            'admin.transactions.dashboard',
            'admin.transactions.index',
            'admin.tax.dashboard',
            'admin.security.dashboard',
            'admin.sms.campaigns.index',
            'admin.sms.settings.index',
            'admin.email_marketing.dashboard',
            'admin.support.index',
            'admin.contact.customer',
            'admin.contact.supplier',
            'admin.accounts.transactions',
            'admin.accounts.expenses.index',
            'admin.accounts.methods',
            'admin.report.sales',
            'admin.report.profit_loss',
            'admin.promo.coupons.index',
            'admin.promo.flashsale',
            'admin.forms.index',
            'admin.content.pages.index',
            'admin.content.blogs.index',
            'admin.shipping.index',
            'admin.theme.setting.home_builder',
            'admin.roles.users',
            'admin.roles.index',
            'admin.sessions.index',
            'admin.activity.logs',
            'admin.media.index',
            'admin.setting.general',
            'admin.settings.auth.index',
        ];

        foreach ($priorityRoutes as $rName) {
            if (Route::has($rName) && method_exists($user, 'hasAccess') && $user->hasAccess($rName)) {
                try {
                    $routeObj = Route::getRoutes()->getByName($rName);
                    // Ensure the route does NOT require any URI parameters to prevent UrlGenerationException
                    if ($routeObj && count($routeObj->parameterNames()) === 0) {
                        return $rName;
                    }
                } catch (\Throwable $e) {
                    continue;
                }
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

        // ১. Super Admin ebong Admin role-er shokol khette purna onumodon (Root Gate)
        $userRole = str_replace(['-', ' '], '_', strtolower(trim((string)($user->role ?? ''))));
        if (!empty($user->is_super_admin) || in_array($userRole, ['super_admin', 'admin'])) {
            return $next($request);
        }

        // ২. Customer-der internal admin panel-e probesh nishiddho
        if (method_exists($user, 'isCustomer') && $user->isCustomer()) {
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

        // ৩. Profile, global search ebong common internal routes bypass (admin.dashboard ke strict check e rakha hoyeche)
        $commonInternalRoutes = [
            'admin.search.ajax',
            'admin.profile.index',
            'admin.profile.update',
            'admin.profile.password',
        ];

        // Jodi Staff hoy ebong target permission check kora proyojon hoy
        $hasAccess = method_exists($user, 'hasAccess') ? $user->hasAccess($targetPermission) : true;

        if ($hasAccess || in_array($targetPermission, $commonInternalRoutes)) {
            return $next($request);
        }

        // ৪. Permission na thakle AJAX response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => "Access Denied: You do not have permission to execute '{$targetPermission}'."
            ], 403);
        }

        // ৫. Dashboard access off thaka obosthay onno permitted page-e safe redirect
        if ($targetPermission === 'admin.dashboard') {
            $fallback = $this->getFirstPermittedRoute($user);
            if ($fallback) {
                try {
                    return redirect()->route($fallback)->with('info', 'আপনার মেইন ড্যাশবোর্ড অ্যাক্সেস বন্ধ রয়েছে। আপনাকে অনুমোদিত মডিউলে পাঠানো হয়েছে।');
                } catch (\Throwable $e) {
                    // Safe bypass if route execution throws parameter mismatch
                }
            }
            abort(403, 'Access Denied: You do not have permission to access Main Dashboard or any administrative modules.');
        }

        if ($request->isMethod('get')) {
            abort(403, "Access Denied: You do not have permission to access '{$targetPermission}'.");
        }

        return back()->with('error', "Access Denied: You do not have permission to perform this action ({$targetPermission}).");
    }
}