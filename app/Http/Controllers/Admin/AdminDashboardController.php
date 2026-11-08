<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\AuthController;
use App\Models\User;
use App\Services\Auth\AuthenticationSettingsService;
use App\Services\InventoryService;
use App\Services\Sms\SmsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    /**
     * User administrative/staff access ache ki na verify helper
     */
    protected function isStaffOrAdminUser($user): bool
    {
        if (!$user) {
            return false;
        }

        // ১. Super Admin check
        if (!empty($user->is_super_admin) || strtolower(trim((string)$user->role)) === 'super_admin') {
            return true;
        }

        $role = str_replace(['-', ' '], '_', strtolower(trim((string)$user->role)));

        // ২. Administrative staff roles check
        $staffRoles = [
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

        if (in_array($role, $staffRoles)) {
            return true;
        }

        // ৩. user_type check
        $userType = str_replace(['-', ' '], '_', strtolower(trim((string)($user->user_type ?? ''))));
        if (in_array($userType, ['admin', 'staff'])) {
            return true;
        }

        // ৪. role_route_permissions table dynamic check
        if (Schema::hasTable('role_route_permissions')) {
            return DB::table('role_route_permissions')
                ->where('role_name', $user->role)
                ->orWhere('role_name', $role)
                ->orWhere('role_name', str_replace('_', '-', $role))
                ->orWhere('role_name', str_replace('_', ' ', $role))
                ->exists();
        }

        return false;
    }

    /**
     * Admin Login Page
     */
    public function showLogin()
    {
        if (Auth::check() && $this->isStaffOrAdminUser(Auth::user())) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * Admin Login Process (Supports Email, Mobile Phone, Zero-Permission Guard & 2FA)
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string', // Accepts both email or phone number
            'password' => 'required|min:6',
        ]);

        $loginInput = trim((string) ($request->input('login') ?: $request->input('email')));
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);

        $smsService = app(SmsService::class);

        $cleanPhone = null;
        $localPhone = null;
        $last10 = null;

        if (!$isEmail) {
            $cleanDigits = preg_replace('/[^0-9]/', '', $loginInput);
            $last10 = strlen($cleanDigits) >= 10 ? substr($cleanDigits, -10) : $cleanDigits;
            $cleanPhone = $smsService->formatNumber($loginInput);
            $localPhone = '0' . $last10;
        }

        // Search Admin/Staff User by Email OR Phone
        $targetUserQuery = User::withTrashed();
        if ($isEmail) {
            $targetUserQuery->where('email', strtolower($loginInput));
        } else {
            $targetUserQuery->where(function ($q) use ($cleanPhone, $localPhone, $loginInput, $last10) {
                $q->where('phone', $cleanPhone)
                  ->orWhere('phone', $localPhone)
                  ->orWhere('phone', $loginInput)
                  ->orWhere('phone', 'like', '%' . $last10);
            });
        }
        $user = $targetUserQuery->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()
                ->withErrors(['email' => 'These credentials do not match our administrative records.'])
                ->withInput();
        }

        // 30 din purono trashed account check
        if ($user->trashed()) {
            if ($user->deleted_at <= now()->subDays(30)) {
                $user->forceDelete();
                return back()->withErrors(['email' => 'Account permanently deleted.']);
            }
            $user->restore();
            $user->update(['is_active' => 1]);
        }

        // ১. Account locked ba inactive check
        if (!empty($user->is_locked) || (isset($user->is_active) && !$user->is_active)) {
            $reason = $user->locked_reason ?: 'Your account is deactivated or locked.';
            return back()->withErrors(['email' => $reason]);
        }

        // ২. Shudhumatro sadharon customer hole block kora (Internal staff/admin access only)
        if (!$this->isStaffOrAdminUser($user)) {
            return back()->withErrors([
                'email' => 'Unauthorized account. Administrative staff access only.'
            ]);
        }

        $role = str_replace(['-', ' '], '_', strtolower(trim((string)$user->role)));

        // Zero permission kick-out guard
        if (!$user->isSuperAdmin() && $role !== 'admin' && Schema::hasTable('role_route_permissions')) {
            $hasAnyPerm = DB::table('role_route_permissions')
                ->where(function ($q) use ($user, $role) {
                    $q->where('role_name', $user->role)
                      ->orWhere('role_name', $role)
                      ->orWhere('role_name', str_replace('_', '-', $role))
                      ->orWhere('role_name', str_replace('_', ' ', $role));
                })
                ->where(function ($q) {
                    $q->where('can_read', 1)
                      ->orWhere('can_create', 1)
                      ->orWhere('can_update', 1)
                      ->orWhere('can_delete', 1);
                })
                ->exists();

            if (!$hasAnyPerm) {
                return back()->withErrors([
                    'email' => 'Access Revoked: Your assigned role currently has no active permissions.'
                ]);
            }
        }

        $remember = $request->boolean('remember');

        // ৩. Two-Factor Authentication Check & Dispatch (Main Security Engine)
        $authController = app(AuthController::class);
        if ($authController->shouldChallengeTwoFactor($user)) {
            $request->session()->put('auth_2fa_source', 'admin');
            return $authController->initiateTwoFactorChallenge($request, $user, $remember);
        }

        // ৪. Standard Login Completion
        Auth::login($user, $remember);
        $request->session()->regenerate();
        session()->forget('url.intended');

        return redirect()->route('admin.dashboard');
    }

    /**
     * Admin Dashboard - Comprehensive Sales Audit & Data Analytics
     */
    public function index(Request $request)
    {
        $now = Carbon::now();
        $deliveredStatuses = ['completed', 'delivered'];

        // ১. টাইমফ্রেম এবং ডেট রেঞ্জ রেজোলিউশন
        $period = $request->input('period', 'this_month');
        $compareMode = $request->input('compare_mode', 'default');

        switch ($period) {
            case 'today':
                $startDate = $now->copy()->startOfDay();
                $endDate   = $now->copy()->endOfDay();
                $prevStartDate = $now->copy()->subDay()->startOfDay();
                $prevEndDate   = $now->copy()->subDay()->endOfDay();
                $periodLabel   = "Today (" . $startDate->format('d M Y') . ")";
                $compareLabel  = "Yesterday (" . $prevStartDate->format('d M Y') . ")";
                break;

            case 'yesterday':
                $startDate = $now->copy()->subDay()->startOfDay();
                $endDate   = $now->copy()->subDay()->endOfDay();
                $prevStartDate = $now->copy()->subDays(2)->startOfDay();
                $prevEndDate   = $now->copy()->subDays(2)->endOfDay();
                $periodLabel   = "Yesterday (" . $startDate->format('d M Y') . ")";
                $compareLabel  = "Day Before Yesterday";
                break;

            case 'this_week':
                $startDate = $now->copy()->startOfWeek();
                $endDate   = $now->copy()->endOfWeek();
                $prevStartDate = $now->copy()->subWeek()->startOfWeek();
                $prevEndDate   = $now->copy()->subWeek()->endOfWeek();
                $periodLabel   = "This Week (" . $startDate->format('d M') . " - " . $endDate->format('d M') . ")";
                $compareLabel  = "Last Week";
                break;

            case 'last_week':
                $startDate = $now->copy()->subWeek()->startOfWeek();
                $endDate   = $now->copy()->subWeek()->endOfWeek();
                $prevStartDate = $now->copy()->subWeeks(2)->startOfWeek();
                $prevEndDate   = $now->copy()->subWeeks(2)->endOfWeek();
                $periodLabel   = "Last Week";
                $compareLabel  = "2 Weeks Ago";
                break;

            case 'this_month':
                $startDate = $now->copy()->startOfMonth();
                $endDate   = $now->copy()->endOfMonth();
                $prevStartDate = $now->copy()->subMonth()->startOfMonth();
                $prevEndDate   = $now->copy()->subMonth()->endOfMonth();
                $periodLabel   = "This Month (" . $startDate->format('F Y') . ")";
                $compareLabel  = "Last Month (" . $prevStartDate->format('F Y') . ")";
                break;

            case 'last_month':
                $startDate = $now->copy()->subMonth()->startOfMonth();
                $endDate   = $now->copy()->endOfMonth();
                $prevStartDate = $now->copy()->subMonths(2)->startOfMonth();
                $prevEndDate   = $now->copy()->subMonths(2)->endOfMonth();
                $periodLabel   = "Last Month (" . $startDate->format('F Y') . ")";
                $compareLabel  = "Prior Month";
                break;

            case 'first_6_months':
                $targetYear = (int) $request->input('year', $now->year);
                $startDate = Carbon::createFromDate($targetYear, 1, 1)->startOfDay();
                $endDate   = Carbon::createFromDate($targetYear, 6, 30)->endOfDay();
                $prevStartDate = Carbon::createFromDate($targetYear, 7, 1)->startOfDay();
                $prevEndDate   = Carbon::createFromDate($targetYear, 12, 31)->endOfDay();
                $periodLabel   = "First 6 Months {$targetYear} (Jan - Jun)";
                $compareLabel  = "Last 6 Months {$targetYear} (Jul - Dec)";
                break;

            case 'last_6_months':
                $targetYear = (int) $request->input('year', $now->year);
                $startDate = Carbon::createFromDate($targetYear, 7, 1)->startOfDay();
                $endDate   = Carbon::createFromDate($targetYear, 12, 31)->endOfDay();
                $prevStartDate = Carbon::createFromDate($targetYear, 1, 1)->startOfDay();
                $prevEndDate   = Carbon::createFromDate($targetYear, 6, 30)->endOfDay();
                $periodLabel   = "Last 6 Months {$targetYear} (Jul - Dec)";
                $compareLabel  = "First 6 Months {$targetYear} (Jan - Jun)";
                break;

            case 'this_year':
                $startDate = $now->copy()->startOfYear();
                $endDate   = $now->copy()->endOfYear();
                $prevStartDate = $now->copy()->subYear()->startOfYear();
                $prevEndDate   = $now->copy()->subYear()->endOfYear();
                $periodLabel   = "This Year ({$now->year})";
                $compareLabel  = "Last Year (" . ($now->year - 1) . ")";
                break;

            case 'last_year':
                $startDate = $now->copy()->subYear()->startOfYear();
                $endDate   = $now->copy()->endOfYear();
                $prevStartDate = $now->copy()->subYears(2)->startOfYear();
                $prevEndDate   = $now->copy()->subYears(2)->endOfYear();
                $periodLabel   = "Last Year (" . ($now->year - 1) . ")";
                $compareLabel  = "Year " . ($now->year - 2);
                break;

            case 'specific_date':
                $sDate = $request->input('specific_date', $now->toDateString());
                $parsed = Carbon::parse($sDate);
                $startDate = $parsed->copy()->startOfDay();
                $endDate   = $parsed->copy()->endOfDay();
                $prevStartDate = $parsed->copy()->subDay()->startOfDay();
                $prevEndDate   = $parsed->copy()->subDay()->endOfDay();
                $periodLabel   = "Date: " . $startDate->format('d M Y');
                $compareLabel  = "Previous Day (" . $prevStartDate->format('d M Y') . ")";
                break;

            case 'specific_month':
                $mVal = $request->input('specific_month', $now->format('Y-m'));
                $parsed = Carbon::parse($mVal . '-01');
                $startDate = $parsed->copy()->startOfMonth();
                $endDate   = $parsed->copy()->endOfMonth();
                $prevStartDate = $parsed->copy()->subMonth()->startOfMonth();
                $prevEndDate   = $parsed->copy()->subMonth()->endOfMonth();
                $periodLabel   = "Month: " . $startDate->format('F Y');
                $compareLabel  = "Previous Month (" . $prevStartDate->format('F Y') . ")";
                break;

            case 'specific_year':
                $yVal = (int) $request->input('specific_year', $now->year);
                $startDate = Carbon::createFromDate($yVal, 1, 1)->startOfDay();
                $endDate   = Carbon::createFromDate($yVal, 12, 31)->endOfDay();
                $prevStartDate = Carbon::createFromDate($yVal - 1, 1, 1)->startOfDay();
                $prevEndDate   = Carbon::createFromDate($yVal - 1, 12, 31)->endOfDay();
                $periodLabel   = "Year: {$yVal}";
                $compareLabel  = "Year " . ($yVal - 1);
                break;

            case 'custom':
                $startDate = Carbon::parse($request->input('start_date', $now->copy()->subDays(30)->toDateString()))->startOfDay();
                $endDate   = Carbon::parse($request->input('end_date', $now->toDateString()))->endOfDay();
                $diffDays  = max(1, $startDate->diffInDays($endDate) + 1);
                $prevEndDate = $startDate->copy()->subDay()->endOfDay();
                $prevStartDate = $prevEndDate->copy()->subDays($diffDays - 1)->startOfDay();
                $periodLabel   = "Custom (" . $startDate->format('d M Y') . " - " . $endDate->format('d M Y') . ")";
                $compareLabel  = "Previous {$diffDays} Days";
                break;

            default:
                $startDate = $now->copy()->startOfMonth();
                $endDate   = $now->copy()->endOfMonth();
                $prevStartDate = $now->copy()->subMonth()->startOfMonth();
                $prevEndDate   = $now->copy()->subMonth()->endOfMonth();
                $periodLabel   = "This Month (" . $startDate->format('F Y') . ")";
                $compareLabel  = "Last Month (" . $prevStartDate->format('F Y') . ")";
                break;
        }

        // কাস্টম কম্প্যারিজন ওভাররাইড
        if ($compareMode === 'yesterday_vs_today') {
            $startDate = $now->copy()->startOfDay();
            $endDate   = $now->copy()->endOfDay();
            $prevStartDate = $now->copy()->subDay()->startOfDay();
            $prevEndDate   = $now->copy()->subDay()->endOfDay();
            $periodLabel = "Today"; $compareLabel = "Yesterday";
        } elseif ($compareMode === 'last_week_vs_this_week') {
            $startDate = $now->copy()->startOfWeek();
            $endDate   = $now->copy()->endOfWeek();
            $prevStartDate = $now->copy()->subWeek()->startOfWeek();
            $prevEndDate   = $now->copy()->subWeek()->endOfWeek();
            $periodLabel = "This Week"; $compareLabel = "Last Week";
        } elseif ($compareMode === 'last_month_vs_this_month') {
            $startDate = $now->copy()->startOfMonth();
            $endDate   = $now->copy()->endOfMonth();
            $prevStartDate = $now->copy()->subMonth()->startOfMonth();
            $prevEndDate   = $now->copy()->subMonth()->endOfMonth();
            $periodLabel = "This Month"; $compareLabel = "Last Month";
        } elseif ($compareMode === 'last_year_vs_this_year') {
            $startDate = $now->copy()->startOfYear();
            $endDate   = $now->copy()->endOfYear();
            $prevStartDate = $now->copy()->subYear()->startOfYear();
            $prevEndDate   = $now->copy()->subYear()->endOfYear();
            $periodLabel = "This Year ({$now->year})"; $compareLabel = "Last Year (" . ($now->year - 1) . ")";
        } elseif ($compareMode === 'first_6_vs_last_6') {
            $targetYear = (int) $request->input('year', $now->year);
            $startDate = Carbon::createFromDate($targetYear, 7, 1)->startOfDay();
            $endDate   = Carbon::createFromDate($targetYear, 12, 31)->endOfDay();
            $prevStartDate = Carbon::createFromDate($targetYear, 1, 1)->startOfDay();
            $prevEndDate   = Carbon::createFromDate($targetYear, 6, 30)->endOfDay();
            $periodLabel = "Jul - Dec {$targetYear}"; $compareLabel = "Jan - Jun {$targetYear}";
        }

        // ২. ফিল্টারকৃত পিরিয়ডের মূল আর্থিক পরিসংখ্যান
        $periodOrdersQuery = DB::table('orders')->whereBetween('created_at', [$startDate, $endDate]);
        $prevOrdersQuery   = DB::table('orders')->whereBetween('created_at', [$prevStartDate, $prevEndDate]);

        $periodSales = (float) (clone $periodOrdersQuery)
            ->whereIn('order_status', $deliveredStatuses)
            ->sum('grand_total');

        $prevSales = (float) (clone $prevOrdersQuery)
            ->whereIn('order_status', $deliveredStatuses)
            ->sum('grand_total');

        $salesGrowth = 0;
        if ($prevSales > 0) {
            $salesGrowth = round((($periodSales - $prevSales) / $prevSales) * 100, 2);
        } elseif ($periodSales > 0) {
            $salesGrowth = 100;
        }

        $periodTotalOrders = (clone $periodOrdersQuery)->count();
        $prevTotalOrders   = (clone $prevOrdersQuery)->count();
        $ordersGrowth = 0;
        if ($prevTotalOrders > 0) {
            $ordersGrowth = round((($periodTotalOrders - $prevTotalOrders) / $prevTotalOrders) * 100, 2);
        } elseif ($periodTotalOrders > 0) {
            $ordersGrowth = 100;
        }

        // ৩. চ্যানেল সেলস অডিট (Online vs POS)
        $hasOrderSource = Schema::hasColumn('orders', 'order_source');
        $hasOrderType   = Schema::hasColumn('orders', 'order_type');

        if ($hasOrderSource) {
            $posSales = (float) (clone $periodOrdersQuery)->whereIn('order_status', $deliveredStatuses)->where('order_source', 'pos')->sum('grand_total');
            $posOrdersCount = (clone $periodOrdersQuery)->where('order_source', 'pos')->count();
            $onlineSales = (float) (clone $periodOrdersQuery)->whereIn('order_status', $deliveredStatuses)->where(function ($q) {
                $q->whereNull('order_source')->orWhere('order_source', '!=', 'pos');
            })->sum('grand_total');
            $onlineOrdersCount = (clone $periodOrdersQuery)->where(function ($q) {
                $q->whereNull('order_source')->orWhere('order_source', '!=', 'pos');
            })->count();
        } elseif ($hasOrderType) {
            $posSales = (float) (clone $periodOrdersQuery)->whereIn('order_status', $deliveredStatuses)->where('order_type', 'pos')->sum('grand_total');
            $posOrdersCount = (clone $periodOrdersQuery)->where('order_type', 'pos')->count();
            $onlineSales = (float) (clone $periodOrdersQuery)->whereIn('order_status', $deliveredStatuses)->where(function ($q) {
                $q->whereNull('order_type')->orWhere('order_type', '!=', 'pos');
            })->sum('grand_total');
            $onlineOrdersCount = (clone $periodOrdersQuery)->where(function ($q) {
                $q->whereNull('order_type')->orWhere('order_type', '!=', 'pos');
            })->count();
        } else {
            $posSales = 0; $posOrdersCount = 0;
            $onlineSales = $periodSales; $onlineOrdersCount = $periodTotalOrders;
        }

        // ৪. COGS ও নিট গ্রস প্রফিট অডিট
        $estimatedCogs = 0.00;
        if (Schema::hasTable('order_items')) {
            $qtyColName = Schema::hasColumn('order_items', 'quantity') ? 'order_items.quantity' : (Schema::hasColumn('order_items', 'qty') ? 'order_items.qty' : '1');
            $hasCogs = Schema::hasColumn('order_items', 'cogs');
            $hasPurchaseCost = Schema::hasColumn('order_items', 'purchase_cost');
            $hasProdCost = Schema::hasTable('products') && Schema::hasColumn('products', 'purchase_cost');
            $hasProdPrice = Schema::hasTable('products') && Schema::hasColumn('products', 'purchase_price');

            $cogsParts = [];
            if ($hasCogs) {
                $cogsParts[] = "NULLIF(order_items.cogs, 0)";
            }
            if ($hasPurchaseCost) {
                $cogsParts[] = "NULLIF(order_items.purchase_cost, 0) * {$qtyColName}";
            }
            if ($hasProdCost) {
                $cogsParts[] = "NULLIF(products.purchase_cost, 0) * {$qtyColName}";
            }
            if ($hasProdPrice) {
                $cogsParts[] = "NULLIF(products.purchase_price, 0) * {$qtyColName}";
            }
            $cogsParts[] = "0";

            $cogsExpr = "COALESCE(" . implode(', ', $cogsParts) . ")";

            $cogsQuery = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id');

            if ($hasProdCost || $hasProdPrice) {
                $cogsQuery->leftJoin('products', 'order_items.product_id', '=', 'products.id');
            }

            $estimatedCogs = (float) $cogsQuery
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->whereIn('orders.order_status', $deliveredStatuses)
                ->sum(DB::raw($cogsExpr));
        }

        $estimatedGrossProfit = max(0, $periodSales - $estimatedCogs);
        $grossProfitMargin = $periodSales > 0 ? round(($estimatedGrossProfit / $periodSales) * 100, 2) : 0;

        // ৫. কালেকশন (Paid) ও বাকি (Due) অডিট
        if (Schema::hasColumn('orders', 'paid_amount')) {
            $totalPaidAmount = (float) (clone $periodOrdersQuery)->sum(DB::raw("
                CASE 
                    WHEN LOWER(COALESCE(payment_status, '')) = 'paid' THEN COALESCE(NULLIF(paid_amount, 0), grand_total, 0)
                    WHEN COALESCE(paid_amount, 0) > 0 THEN paid_amount
                    ELSE 0 
                END
            "));
        } else {
            $totalPaidAmount = (float) (clone $periodOrdersQuery)->sum(DB::raw("
                CASE 
                    WHEN LOWER(COALESCE(payment_status, '')) = 'paid' THEN COALESCE(grand_total, 0)
                    ELSE 0 
                END
            "));
        }
        $totalDueAmount = max(0, (float)(clone $periodOrdersQuery)->sum('grand_total') - $totalPaidAmount);

        // ৬. নতুন ইউজার ও কাস্টমার রেজিস্ট্রেশন অডিট
        $newUsersCount = 0;
        $prevNewUsersCount = 0;
        if (Schema::hasTable('users')) {
            $newUsersCount = DB::table('users')
                ->where('role', '!=', 'admin')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $prevNewUsersCount = DB::table('users')
                ->where('role', '!=', 'admin')
                ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
                ->count();
        }
        $newUsersGrowth = 0;
        if ($prevNewUsersCount > 0) {
            $newUsersGrowth = round((($newUsersCount - $prevNewUsersCount) / $prevNewUsersCount) * 100, 2);
        } elseif ($newUsersCount > 0) {
            $newUsersGrowth = 100;
        }

        // ৭. ফেইল-সেফ প্রোডাক্ট সেলস লেজার
        $productSalesLedger = collect();
        if (Schema::hasTable('order_items') && Schema::hasTable('products')) {
            $hasSubtotal   = Schema::hasColumn('order_items', 'subtotal');
            $hasTotalPrice = Schema::hasColumn('order_items', 'total_price');
            $hasUnitPrice  = Schema::hasColumn('order_items', 'unit_price');
            $hasPrice      = Schema::hasColumn('order_items', 'price');
            $itemQtyCol    = Schema::hasColumn('order_items', 'quantity') ? 'order_items.quantity' : (Schema::hasColumn('order_items', 'qty') ? 'order_items.qty' : '1');

            if ($hasSubtotal) {
                $revenueExpr = 'COALESCE(order_items.subtotal, 0)';
            } elseif ($hasTotalPrice) {
                $revenueExpr = 'COALESCE(order_items.total_price, 0)';
            } elseif ($hasUnitPrice) {
                $revenueExpr = "COALESCE({$itemQtyCol}, 1) * COALESCE(order_items.unit_price, 0)";
            } elseif ($hasPrice) {
                $revenueExpr = "COALESCE({$itemQtyCol}, 1) * COALESCE(order_items.price, 0)";
            } else {
                $revenueExpr = "COALESCE({$itemQtyCol}, 1) * COALESCE(products.sale_price, products.regular_price, 0)";
            }

            $cogsParts = [];
            if (Schema::hasColumn('order_items', 'cogs')) {
                $cogsParts[] = "NULLIF(order_items.cogs, 0)";
            }
            if (Schema::hasColumn('order_items', 'purchase_cost')) {
                $cogsParts[] = "NULLIF(order_items.purchase_cost, 0) * {$itemQtyCol}";
            }
            if (Schema::hasColumn('products', 'purchase_cost')) {
                $cogsParts[] = "NULLIF(products.purchase_cost, 0) * {$itemQtyCol}";
            }
            if (Schema::hasColumn('products', 'purchase_price')) {
                $cogsParts[] = "NULLIF(products.purchase_price, 0) * {$itemQtyCol}";
            }
            $cogsParts[] = "0";
            $itemCostExpr = "COALESCE(" . implode(', ', $cogsParts) . ")";

            $nameCols = array_values(array_filter(['shipping_name', 'customer_name', 'name', 'billing_name'], fn($c) => Schema::hasColumn('orders', $c)));
            $custNameExpr = !empty($nameCols) ? 'COALESCE(' . implode(', ', array_map(fn($c) => "orders.$c", $nameCols)) . ", 'Guest Customer')" : "'Guest Customer'";
            $buyersListExpr = !empty($nameCols) 
                ? 'GROUP_CONCAT(DISTINCT COALESCE(' . implode(', ', array_map(fn($c) => "orders.$c", $nameCols)) . ", 'Guest') SEPARATOR ', ') as buyers_list" 
                : "'Guest' as buyers_list";

            $phoneCols = array_values(array_filter(['shipping_phone', 'customer_phone', 'phone', 'mobile'], fn($c) => Schema::hasColumn('orders', $c)));
            $custPhoneExpr = !empty($phoneCols) ? 'COALESCE(' . implode(', ', array_map(fn($c) => "orders.$c", $phoneCols)) . ", 'N/A')" : "'N/A'";

            $buyerIdCols = [];
            if (Schema::hasColumn('orders', 'user_id')) $buyerIdCols[] = 'orders.user_id';
            if (!empty($phoneCols)) $buyerIdCols[] = 'orders.' . $phoneCols[0];
            if (!empty($nameCols)) $buyerIdCols[] = 'orders.' . $nameCols[0];

            $uniqueBuyersExpr = !empty($buyerIdCols)
                ? 'COUNT(DISTINCT COALESCE(' . implode(', ', $buyerIdCols) . ')) as unique_buyers_count'
                : 'COUNT(DISTINCT orders.id) as unique_buyers_count';

            $selectFields = [
                'products.id as product_id',
                'products.title as product_title',
                DB::raw('COUNT(DISTINCT orders.id) as times_sold'),
                DB::raw("SUM({$itemQtyCol}) as total_qty"),
                DB::raw("SUM({$revenueExpr}) as total_revenue"),
                DB::raw("SUM({$itemCostExpr}) as total_cost"),
                DB::raw($buyersListExpr),
                DB::raw($uniqueBuyersExpr),
            ];

            $groupByFields = ['products.id', 'products.title'];

            if (Schema::hasColumn('products', 'sku')) {
                $selectFields[] = 'products.sku';
                $groupByFields[] = 'products.sku';
            } else {
                $selectFields[] = DB::raw("NULL as sku");
            }

            if (Schema::hasColumn('products', 'product_code')) {
                $selectFields[] = 'products.product_code';
                $groupByFields[] = 'products.product_code';
            } else {
                $selectFields[] = DB::raw("NULL as product_code");
            }

            if (Schema::hasColumn('products', 'thumbnail')) {
                $selectFields[] = 'products.thumbnail';
                $groupByFields[] = 'products.thumbnail';
            } else {
                $selectFields[] = DB::raw("NULL as thumbnail");
            }

            if (Schema::hasColumn('products', 'regular_price')) {
                $selectFields[] = DB::raw('COALESCE(products.regular_price, 0) as regular_price');
                $groupByFields[] = 'products.regular_price';
            } else {
                $selectFields[] = DB::raw("0 as regular_price");
            }

            if (Schema::hasColumn('products', 'purchase_cost')) {
                $selectFields[] = DB::raw('COALESCE(NULLIF(products.purchase_cost, 0), products.purchase_price, 0) as purchase_price');
                $groupByFields[] = 'products.purchase_cost';
                if (Schema::hasColumn('products', 'purchase_price')) {
                    $groupByFields[] = 'products.purchase_price';
                }
            } elseif (Schema::hasColumn('products', 'purchase_price')) {
                $selectFields[] = DB::raw('COALESCE(products.purchase_price, 0) as purchase_price');
                $groupByFields[] = 'products.purchase_price';
            } else {
                $selectFields[] = DB::raw("0 as purchase_price");
            }

            $productSalesLedger = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->select($selectFields)
                ->groupBy($groupByFields)
                ->orderByDesc('total_qty')
                ->get();
        }

        $bestSellingProduct = $productSalesLedger->first();

        // ৮. কাস্টমার পারফরম্যান্স ও শীর্ষ ক্রেতা অডিট
        $customerSalesLedger = collect();
        if (Schema::hasTable('orders')) {
            $hasPaidCol = Schema::hasColumn('orders', 'paid_amount');
            $paidSumExpr = $hasPaidCol
                ? "SUM(
                    CASE 
                        WHEN LOWER(COALESCE(orders.payment_status, '')) = 'paid' THEN COALESCE(NULLIF(orders.paid_amount, 0), orders.grand_total, 0)
                        WHEN COALESCE(orders.paid_amount, 0) > 0 THEN orders.paid_amount
                        ELSE 0 
                    END
                )"
                : "SUM(
                    CASE 
                        WHEN LOWER(COALESCE(orders.payment_status, '')) = 'paid' THEN COALESCE(orders.grand_total, 0)
                        ELSE 0 
                    END
                )";

            $hasUserIdCol = Schema::hasColumn('orders', 'user_id');
            $userIdSelect = $hasUserIdCol ? "MAX(orders.user_id) as user_id" : "NULL as user_id";

            $customerSalesLedger = (clone $periodOrdersQuery)
                ->select(
                    DB::raw("{$custNameExpr} as customer_name"),
                    DB::raw("{$custPhoneExpr} as customer_phone"),
                    DB::raw("{$userIdSelect}"),
                    DB::raw('COUNT(orders.id) as total_orders_placed'),
                    DB::raw('SUM(orders.grand_total) as total_spent'),
                    DB::raw("{$paidSumExpr} as total_paid"),
                    DB::raw("MAX(orders.created_at) as last_order_date")
                )
                ->groupBy('customer_name', 'customer_phone')
                ->orderByDesc('total_spent')
                ->take(10)
                ->get();
        }

        $bestCustomer = $customerSalesLedger->first();

        // ৯. অর্ডার স্ট্যাটাস ও পেমেন্ট গেটওয়ে ডিস্ট্রিবিউশন
        $orderStatusCounts = [
            'pending'    => (clone $periodOrdersQuery)->where('order_status', 'pending')->count(),
            'processing' => (clone $periodOrdersQuery)->where('order_status', 'processing')->count(),
            'completed'  => (clone $periodOrdersQuery)->whereIn('order_status', $deliveredStatuses)->count(),
            'cancelled'  => (clone $periodOrdersQuery)->where('order_status', 'cancelled')->count(),
            'returned'   => (clone $periodOrdersQuery)->where('order_status', 'returned')->count(),
        ];

        $paymentMethodStats = (clone $periodOrdersQuery)
            ->whereIn('order_status', $deliveredStatuses)
            ->select(
                DB::raw("COALESCE(payment_method, 'Cash on Delivery') as method_name"),
                DB::raw('count(*) as count'),
                DB::raw('sum(grand_total) as total_amount')
            )
            ->groupBy('method_name')
            ->get();

        // ১০. ট্রেন্ড চার্ট ডেটা
        $diffInDays = $startDate->diffInDays($endDate);
        $chartLabels = [];
        $chartCounts = [];
        $chartRevenues = [];

        if ($diffInDays <= 1) {
            $hourlyData = (clone $periodOrdersQuery)
                ->select(
                    DB::raw('HOUR(created_at) as hour_key'),
                    DB::raw('count(*) as count'),
                    DB::raw("SUM(CASE WHEN order_status IN ('completed', 'delivered') THEN grand_total ELSE 0 END) as revenue")
                )
                ->groupBy('hour_key')
                ->get()
                ->keyBy('hour_key');

            for ($h = 0; $h <= 23; $h++) {
                $chartLabels[] = sprintf('%02d:00', $h);
                $chartCounts[] = isset($hourlyData[$h]) ? (int) $hourlyData[$h]->count : 0;
                $chartRevenues[] = isset($hourlyData[$h]) ? (float) $hourlyData[$h]->revenue : 0;
            }
        } elseif ($diffInDays <= 62) {
            $dailyData = (clone $periodOrdersQuery)
                ->select(
                    DB::raw('DATE(created_at) as date_key'),
                    DB::raw('count(*) as count'),
                    DB::raw("SUM(CASE WHEN order_status IN ('completed', 'delivered') THEN grand_total ELSE 0 END) as revenue")
                )
                ->groupBy('date_key')
                ->get()
                ->keyBy('date_key');

            $currentIter = $startDate->copy();
            while ($currentIter <= $endDate) {
                $dKey = $currentIter->format('Y-m-d');
                $chartLabels[] = $currentIter->format('d M');
                $chartCounts[] = isset($dailyData[$dKey]) ? (int) $dailyData[$dKey]->count : 0;
                $chartRevenues[] = isset($dailyData[$dKey]) ? (float) $dailyData[$dKey]->revenue : 0;
                $currentIter->addDay();
            }
        } else {
            $monthlyData = (clone $periodOrdersQuery)
                ->select(
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month_key"),
                    DB::raw('count(*) as count'),
                    DB::raw("SUM(CASE WHEN order_status IN ('completed', 'delivered') THEN grand_total ELSE 0 END) as revenue")
                )
                ->groupBy('month_key')
                ->get()
                ->keyBy('month_key');

            $currentIter = $startDate->copy()->startOfMonth();
            while ($currentIter <= $endDate) {
                $mKey = $currentIter->format('Y-m');
                $chartLabels[] = $currentIter->format('M Y');
                $chartCounts[] = isset($monthlyData[$mKey]) ? (int) $monthlyData[$mKey]->count : 0;
                $chartRevenues[] = isset($monthlyData[$mKey]) ? (float) $monthlyData[$mKey]->revenue : 0;
                $currentIter->addMonth();
            }
        }

        // ১১. ইনভেন্টরি ডেটা
        $valuationData = InventoryService::getInventoryValuation();
        $activeProductsValue = (float) $valuationData['total_valuation'];

        $activeProductsCount = Schema::hasTable('products') 
            ? DB::table('products')->where('stock', '>', 0)->count() 
            : 0;

        $hasVariantsTable = Schema::hasTable('product_variants');
        $variableProductIds = $hasVariantsTable 
            ? DB::table('product_variants')->distinct()->pluck('product_id')->toArray() 
            : [];

        $outOfStockSimple = 0;
        $lowStockSimple = 0;
        if (Schema::hasTable('products')) {
            $simpleQuery = DB::table('products');
            if (!empty($variableProductIds)) {
                $simpleQuery->whereNotIn('id', $variableProductIds);
            }

            $outOfStockSimple = (clone $simpleQuery)
                ->where('stock', '<=', 0)
                ->count();

            $pAlertExpr = Schema::hasColumn('products', 'alert_quantity')
                ? 'stock <= COALESCE(alert_quantity, 5)'
                : 'stock <= 5';

            $lowStockSimple = (clone $simpleQuery)
                ->where('stock', '>', 0)
                ->whereRaw($pAlertExpr)
                ->count();
        }

        $outOfStockVariants = 0;
        $lowStockVariants = 0;
        if ($hasVariantsTable) {
            $outOfStockVariants = DB::table('product_variants')
                ->where('stock', '<=', 0)
                ->count();

            $vAlertExpr = Schema::hasColumn('product_variants', 'alert_quantity')
                ? 'stock <= COALESCE(alert_quantity, 5)'
                : 'stock <= 5';

            $lowStockVariants = DB::table('product_variants')
                ->where('stock', '>', 0)
                ->whereRaw($vAlertExpr)
                ->count();
        }

        $lowStockCount   = $lowStockSimple + $lowStockVariants;
        $outOfStockCount = $outOfStockSimple + $outOfStockVariants;

        $topCategories = collect();
        if (Schema::hasTable('categories') && Schema::hasTable('products')) {
            $topCategories = DB::table('categories')
                ->select(
                    'categories.id',
                    'categories.name',
                    'categories.image',
                    DB::raw('(SELECT count(*) FROM products WHERE products.category_id = categories.id) as product_count')
                )
                ->orderByDesc('product_count')
                ->take(4)
                ->get();
        }

        $topProducts = $productSalesLedger->take(5);
        $topCustomers = $customerSalesLedger->take(5);
        $recentOrders = (clone $periodOrdersQuery)->latest('id')->take(8)->get();

        // ১২. এক্সপোর্ট হ্যান্ডলার
        if ($request->input('export') === 'excel' || $request->input('export') === 'csv') {
            return $this->exportDashboardAnalyticsToCsv(
                $periodLabel,
                $compareLabel,
                $periodSales,
                $prevSales,
                $salesGrowth,
                $periodTotalOrders,
                $prevTotalOrders,
                $ordersGrowth,
                $estimatedCogs,
                $estimatedGrossProfit,
                $grossProfitMargin,
                $onlineSales,
                $posSales,
                $totalPaidAmount,
                $totalDueAmount,
                $newUsersCount,
                $productSalesLedger,
                $customerSalesLedger
            );
        }

        // ১৩. AJAX JSON রেসপন্স
        if ($request->ajax()) {
            return response()->json([
                'success'             => true,
                'periodLabel'         => $periodLabel,
                'compareLabel'        => $compareLabel,
                'periodSales'         => number_format($periodSales, 2),
                'prevSales'           => number_format($prevSales, 2),
                'salesGrowth'         => $salesGrowth,
                'periodTotalOrders'   => $periodTotalOrders,
                'prevTotalOrders'     => $prevTotalOrders,
                'ordersGrowth'        => $ordersGrowth,
                'estimatedCogs'       => number_format($estimatedCogs, 2),
                'estimatedGrossProfit'=> number_format($estimatedGrossProfit, 2),
                'grossProfitMargin'   => $grossProfitMargin,
                'onlineSales'         => number_format($onlineSales, 2),
                'posSales'            => number_format($posSales, 2),
                'onlineOrdersCount'   => $onlineOrdersCount,
                'posOrdersCount'      => $posOrdersCount,
                'totalPaidAmount'     => number_format($totalPaidAmount, 2),
                'totalDueAmount'      => number_format($totalDueAmount, 2),
                'newUsersCount'       => $newUsersCount,
                'newUsersGrowth'      => $newUsersGrowth,
                'activeProductsCount' => $activeProductsCount,
                'activeProductsValue' => number_format($activeProductsValue, 2),
                'lowStockCount'       => $lowStockCount,
                'outOfStockCount'     => $outOfStockCount,
                'bestSellingProduct'  => $bestSellingProduct,
                'bestCustomer'        => $bestCustomer,
                'chartLabels'         => $chartLabels,
                'chartCounts'         => $chartCounts,
                'chartRevenues'       => $chartRevenues,
                'orderStatusCounts'   => $orderStatusCounts,
                'ledgerHtml'          => view('admin.dashboard.partials.product_ledger_rows', ['ledger' => $productSalesLedger])->render(),
                'customerHtml'        => view('admin.dashboard.partials.customer_ledger_rows', ['customers' => $customerSalesLedger])->render(),
                'paymentMethodHtml'   => view('admin.dashboard.partials.payment_method_rows', ['paymentMethodStats' => $paymentMethodStats])->render(),
            ]);
        }

        return view('admin.dashboard', compact(
            'period',
            'periodLabel',
            'compareLabel',
            'periodSales',
            'prevSales',
            'salesGrowth',
            'periodTotalOrders',
            'prevTotalOrders',
            'ordersGrowth',
            'onlineSales',
            'posSales',
            'onlineOrdersCount',
            'posOrdersCount',
            'estimatedCogs',
            'estimatedGrossProfit',
            'grossProfitMargin',
            'totalPaidAmount',
            'totalDueAmount',
            'newUsersCount',
            'newUsersGrowth',
            'bestSellingProduct',
            'bestCustomer',
            'orderStatusCounts',
            'paymentMethodStats',
            'activeProductsCount',
            'activeProductsValue',
            'lowStockCount',
            'outOfStockCount',
            'chartLabels',
            'chartCounts',
            'chartRevenues',
            'topCategories',
            'topProducts',
            'topCustomers',
            'recentOrders',
            'productSalesLedger',
            'customerSalesLedger'
        ));
    }

    /**
     * ড্যাশবোর্ড সম্পূর্ণ বিজনেস অডিট ও সেলস রিপোর্ট CSV এক্সপোর্ট
     */
    protected function exportDashboardAnalyticsToCsv(
        $periodLabel,
        $compareLabel,
        $periodSales,
        $prevSales,
        $salesGrowth,
        $periodTotalOrders,
        $prevTotalOrders,
        $ordersGrowth,
        $estimatedCogs,
        $estimatedGrossProfit,
        $grossProfitMargin,
        $onlineSales,
        $posSales,
        $totalPaidAmount,
        $totalDueAmount,
        $newUsersCount,
        $productSalesLedger,
        $customerSalesLedger
    ) {
        $filename = "Business_Audit_Report_" . date('Y_m_d_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use (
            $periodLabel,
            $compareLabel,
            $periodSales,
            $prevSales,
            $salesGrowth,
            $periodTotalOrders,
            $prevTotalOrders,
            $ordersGrowth,
            $estimatedCogs,
            $estimatedGrossProfit,
            $grossProfitMargin,
            $onlineSales,
            $posSales,
            $totalPaidAmount,
            $totalDueAmount,
            $newUsersCount,
            $productSalesLedger,
            $customerSalesLedger
        ) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [config('app.name', 'Shop Now BD') . ' - Business Analytics & Sales Audit Report']);
            fputcsv($file, ['Report Period:', $periodLabel]);
            fputcsv($file, ['Comparison Base:', $compareLabel]);
            fputcsv($file, ['Generated At:', date('d M Y, h:i A')]);
            fputcsv($file, []);

            fputcsv($file, ['EXECUTIVE PERFORMANCE METRICS', 'CURRENT PERIOD', 'PREVIOUS PERIOD', 'GROWTH / CHANGE']);
            fputcsv($file, ['Delivered Gross Revenue (BDT)', number_format($periodSales, 2), number_format($prevSales, 2), $salesGrowth . '%']);
            fputcsv($file, ['Total Orders Placed', $periodTotalOrders, $prevTotalOrders, $ordersGrowth . '%']);
            fputcsv($file, ['Estimated COGS (Product Cost)', number_format($estimatedCogs, 2), '—', '—']);
            fputcsv($file, ['Estimated Gross Profit', number_format($estimatedGrossProfit, 2), '—', 'Margin: ' . $grossProfitMargin . '%']);
            fputcsv($file, ['Web / Online Sales (BDT)', number_format($onlineSales, 2), '—', '—']);
            fputcsv($file, ['POS Counter Sales (BDT)', number_format($posSales, 2), '—', '—']);
            fputcsv($file, ['Collected (Paid Amount)', number_format($totalPaidAmount, 2), '—', '—']);
            fputcsv($file, ['Customer Receivables (Due)', number_format($totalDueAmount, 2), '—', '—']);
            fputcsv($file, ['New User Registrations', $newUsersCount, '—', '—']);
            fputcsv($file, []);

            fputcsv($file, ['PRODUCT-WISE SALES & BUYERS AUDIT']);
            fputcsv($file, ['Product ID', 'Product Title', 'Code / SKU', 'Unit Price', 'Unit Cost', 'Orders Count', 'Total Sold (Qty)', 'Total Sales (BDT)', 'Gross Profit (BDT)', 'Buyers Count', 'Customers Who Bought']);

            foreach ($productSalesLedger as $pItem) {
                $profit = max(0, (float)$pItem->total_revenue - (float)$pItem->total_cost);
                $skuVal = $pItem->sku ?? ($pItem->product_code ?? '—');
                fputcsv($file, [
                    '#' . $pItem->product_id,
                    $pItem->product_title,
                    $skuVal,
                    number_format((float)$pItem->regular_price, 2),
                    number_format((float)$pItem->purchase_price, 2),
                    $pItem->times_sold,
                    $pItem->total_qty,
                    number_format((float)$pItem->total_revenue, 2),
                    number_format($profit, 2),
                    $pItem->unique_buyers_count,
                    $pItem->buyers_list
                ]);
            }
            fputcsv($file, []);

            fputcsv($file, ['CUSTOMER PURCHASE & PAYMENT LEDGER']);
            fputcsv($file, ['Customer Name', 'User ID', 'Phone Number', 'Total Orders', 'Total Spent (BDT)', 'Total Paid (BDT)', 'Last Order Date']);

            foreach ($customerSalesLedger as $cItem) {
                fputcsv($file, [
                    $cItem->customer_name,
                    !empty($cItem->user_id) ? '#' . $cItem->user_id : 'Guest',
                    $cItem->customer_phone,
                    $cItem->total_orders_placed,
                    number_format((float)$cItem->total_spent, 2),
                    number_format((float)$cItem->total_paid, 2),
                    date('d M Y, h:i A', strtotime($cItem->last_order_date))
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * ADMIN GLOBAL AJAX SEARCH
     */
    public function ajaxGlobalSearch(Request $request)
    {
        $query = trim((string) $request->input('q', ''));
        if (mb_strlen($query) < 2) {
            return response()->json(['status' => 'success', 'html' => '']);
        }

        $cleanQuery = ltrim($query, '#');
        $products = collect();

        if (Schema::hasTable('products')) {
            $products = DB::table('products')
                ->where(function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                      ->orWhere('product_code', 'LIKE', "%{$query}%");
                })
                ->orderBy('id', 'desc')
                ->limit(5)
                ->get();
        }

        $orders = collect();
        if (Schema::hasTable('orders')) {
            $orders = DB::table('orders')
                ->where(function ($q) use ($query, $cleanQuery) {
                    $q->where('invoice_no', 'LIKE', "%{$cleanQuery}%")
                      ->orWhere('shipping_phone', 'LIKE', "%{$query}%")
                      ->orWhere('customer_name', 'LIKE', "%{$query}%");
                })
                ->orderBy('id', 'desc')
                ->limit(5)
                ->get();
        }

        $customers = collect();
        if (Schema::hasTable('users')) {
            $customers = DB::table('users')
                ->where('role', '!=', 'admin')
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('phone', 'LIKE', "%{$query}%")
                      ->orWhere('email', 'LIKE', "%{$query}%");
                })
                ->orderBy('id', 'desc')
                ->limit(5)
                ->get();
        }

        $html = '';
        $safeQuery = e($query);

        if ($products->isNotEmpty()) {
            $html .= '<div class="px-3 py-2 text-uppercase fw-bold text-muted border-bottom bg-light" style="font-size:10px;">Products</div>';
            foreach ($products as $product) {
                try {
                    $editUrl = route('admin.product.edit', $product->id);
                } catch (\Throwable $e) {
                    $editUrl = '#';
                }
                $price = $product->sale_price ?? $product->regular_price ?? 0;
                $html .= '<a href="' . e($editUrl) . '" class="dropdown-item py-2 px-3 d-flex align-items-center justify-content-between border-bottom text-decoration-none">
                    <div class="me-2 overflow-hidden"><span class="fw-semibold text-dark d-block text-truncate" style="max-width:220px;">' . e($product->title ?? 'Untitled Product') . '</span></div>
                    <span class="badge bg-primary-subtle text-primary border">৳' . number_format((float)$price, 2) . '</span>
                </a>';
            }
        }

        if ($orders->isNotEmpty()) {
            $html .= '<div class="px-3 py-2 text-uppercase fw-bold text-muted border-bottom bg-light" style="font-size:10px;">Orders & Invoices</div>';
            foreach ($orders as $order) {
                try {
                    $viewUrl = route('admin.order.view', $order->id);
                } catch (\Throwable $e) {
                    $viewUrl = '#';
                }
                $html .= '<a href="' . e($viewUrl) . '" class="dropdown-item py-2 px-3 d-flex align-items-center justify-content-between border-bottom text-decoration-none">
                    <div class="me-2"><span class="fw-bold text-primary d-block">#' . e($order->invoice_no ?? $order->id) . '</span><small class="text-muted d-block" style="font-size:11px;">' . e($order->customer_name ?? 'Customer') . '</small></div>
                    <span class="badge bg-warning-subtle text-dark border">' . strtoupper($order->order_status ?? 'PENDING') . '</span>
                </a>';
            }
        }

        if ($customers->isNotEmpty()) {
            $html .= '<div class="px-3 py-2 text-uppercase fw-bold text-muted border-bottom bg-light" style="font-size:10px;">Customers</div>';
            foreach ($customers as $customer) {
                $html .= '<div class="dropdown-item py-2 px-3 d-flex align-items-center justify-content-between border-bottom bg-white">
                    <div class="me-2 overflow-hidden"><span class="fw-semibold text-dark d-block text-truncate" style="max-width:220px;">' . e($customer->name ?? 'Customer') . '</span><small class="text-muted d-block" style="font-size:11px;">' . e($customer->phone ?? $customer->email ?? '') . '</small></div>
                    <span class="badge bg-success-subtle text-success border">User</span>
                </div>';
            }
        }

        if ($products->isEmpty() && $orders->isEmpty() && $customers->isEmpty()) {
            $html = '<div class="p-3 text-muted small text-center">No results found for "<strong>' . $safeQuery . '</strong>"</div>';
        }

        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /**
     * Admin Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Logged out successfully.');
    }
}