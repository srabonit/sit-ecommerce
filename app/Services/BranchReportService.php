<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductStock;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BranchReportService
{
    // =========================================================================
    // 1. DATE RANGE & PERIOD RESOLVER
    // =========================================================================

    /**
     * Resolve standard start and end timestamps based on filter period.
     */
    public static function resolveDatePeriod(?string $period = 'today', ?string $startDate = null, ?string $endDate = null): array
    {
        $now = Carbon::now();

        if ($period === 'custom' && $startDate && $endDate) {
            return [
                'start' => Carbon::parse($startDate)->startOfDay(),
                'end'   => Carbon::parse($endDate)->endOfDay(),
            ];
        }

        return match ($period) {
            'yesterday'  => [
                'start' => $now->copy()->subDay()->startOfDay(),
                'end'   => $now->copy()->subDay()->endOfDay(),
            ],
            'this_week'  => [
                'start' => $now->copy()->startOfWeek(),
                'end'   => $now->copy()->endOfWeek(),
            ],
            'last_week'  => [
                'start' => $now->copy()->subWeek()->startOfWeek(),
                'end'   => $now->copy()->subWeek()->endOfWeek(),
            ],
            'this_month' => [
                'start' => $now->copy()->startOfMonth(),
                'end'   => $now->copy()->endOfMonth(),
            ],
            'last_month' => [
                'start' => $now->copy()->subMonth()->startOfMonth(),
                'end'   => $now->copy()->subMonth()->endOfMonth(),
            ],
            'this_year'  => [
                'start' => $now->copy()->startOfYear(),
                'end'   => $now->copy()->endOfYear(),
            ],
            default      => [ // 'today'
                'start' => $now->copy()->startOfDay(),
                'end'   => $now->copy()->endOfDay(),
            ],
        };
    }

    // =========================================================================
    // 2. BRANCH DASHBOARD & FINANCIAL METRICS
    // =========================================================================

    /**
     * Calculate comprehensive financial and operational metrics for a specific branch.
     */
    public static function getBranchDashboardMetrics(
        int $branchId,
        ?string $period = 'today',
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $dates = self::resolveDatePeriod($period, $startDate, $endDate);
        $start = $dates['start'];
        $end   = $dates['end'];

        // Base Orders Query for this branch in date range
        $orderQuery = DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$start, $end])
            ->whereNotIn('order_status', ['cancelled', 'returned']);

        $summary = (clone $orderQuery)->selectRaw("
            COUNT(id) as total_orders,
            COALESCE(SUM(subtotal), 0) as total_subtotal,
            COALESCE(SUM(discount_amount), 0) as total_discount,
            COALESCE(SUM(tax_amount), 0) as total_tax,
            COALESCE(SUM(grand_total), 0) as total_sales,
            COALESCE(SUM(paid_amount), 0) as total_paid,
            COALESCE(SUM(due_amount), 0) as total_due
        ")->first();

        $totalOrders   = (int) ($summary->total_orders ?? 0);
        $totalSales    = (float) ($summary->total_sales ?? 0.00);
        $totalPaid     = (float) ($summary->total_paid ?? 0.00);
        $totalDue      = (float) ($summary->total_due ?? 0.00);
        $totalDiscount = (float) ($summary->total_discount ?? 0.00);
        $totalTax      = (float) ($summary->total_tax ?? 0.00);

        // Calculate Cost of Goods Sold (COGS) for gross profit calculation
        $orderIds = (clone $orderQuery)->pluck('id');
        $cogs = 0.00;
        $totalQtySold = 0;

        if ($orderIds->isNotEmpty()) {
            $hasPurchaseCostCol = Schema::hasColumn('order_items', 'purchase_cost');
            $costExpression = $hasPurchaseCostCol ? 'COALESCE(purchase_cost, 0)' : '0';

            $itemStats = DB::table('order_items')
                ->whereIn('order_id', $orderIds)
                ->selectRaw("
                    COALESCE(SUM(quantity), 0) as total_qty,
                    COALESCE(SUM(quantity * {$costExpression}), 0) as total_cogs
                ")->first();

            $totalQtySold = (int) ($itemStats->total_qty ?? 0);
            $cogs = (float) ($itemStats->total_cogs ?? 0.00);
        }

        $grossProfit = max(0.00, ($totalSales - $totalTax) - $cogs);
        $avgOrderValue = $totalOrders > 0 ? round($totalSales / $totalOrders, 2) : 0.00;

        // Payment Method Breakdown
        $paymentBreakdown = (clone $orderQuery)
            ->select('payment_method', DB::raw('COALESCE(SUM(paid_amount), 0) as total_amount'))
            ->groupBy('payment_method')
            ->pluck('total_amount', 'payment_method')
            ->toArray();

        $cashCollection = (float) ($paymentBreakdown['cash'] ?? ($paymentBreakdown['Cash'] ?? 0.00));
        $bkashCollection = (float) ($paymentBreakdown['bkash'] ?? ($paymentBreakdown['bKash'] ?? 0.00));
        $nagadCollection = (float) ($paymentBreakdown['nagad'] ?? ($paymentBreakdown['Nagad'] ?? 0.00));
        $bankCollection = (float) ($paymentBreakdown['bank_transfer'] ?? ($paymentBreakdown['bank'] ?? 0.00));
        $digitalCollection = round($totalPaid - $cashCollection, 2);

        // Branch-specific Operational Expenses (Schema Safe)
        $totalExpenses = 0.00;
        if (Schema::hasTable('expenses')) {
            $expAmountCol = collect(['amount', 'expense_amount', 'total_amount', 'total'])
                ->first(fn($c) => Schema::hasColumn('expenses', $c));

            if ($expAmountCol) {
                $expQuery = DB::table('expenses')
                    ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()]);

                if (Schema::hasColumn('expenses', 'branch_id')) {
                    $expQuery->where('branch_id', $branchId);
                }

                $totalExpenses = (float) $expQuery->sum($expAmountCol);
            }
        }

        $netProfit = round($grossProfit - $totalExpenses, 2);

        // Branch Total Inventory Asset Value
        $inventoryStats = DB::table('product_stocks')
            ->leftJoin('products', 'product_stocks.product_id', '=', 'products.id')
            ->leftJoin('product_variants', 'product_stocks.variant_id', '=', 'product_variants.id')
            ->where('product_stocks.branch_id', $branchId)
            ->selectRaw("
                COALESCE(SUM(product_stocks.quantity), 0) as total_stock_qty,
                COALESCE(SUM(
                    product_stocks.quantity * COALESCE(product_variants.purchase_cost, products.purchase_cost, products.purchase_price, 0)
                ), 0) as total_inventory_value
            ")->first();

        return [
            'period'               => $period,
            'start_date'           => $start->toDateTimeString(),
            'end_date'             => $end->toDateTimeString(),
            'total_orders'         => $totalOrders,
            'total_items_sold'     => $totalQtySold,
            'total_sales'          => round($totalSales, 2),
            'total_paid'           => round($totalPaid, 2),
            'total_due'            => round($totalDue, 2),
            'total_discount'       => round($totalDiscount, 2),
            'total_tax'            => round($totalTax, 2),
            'cogs'                 => round($cogs, 2),
            'gross_profit'         => round($grossProfit, 2),
            'total_expenses'       => round($totalExpenses, 2),
            'net_profit'           => $netProfit,
            'average_order_value'  => $avgOrderValue,
            'cash_collection'      => round($cashCollection, 2),
            'digital_collection'   => round(max(0, $digitalCollection), 2),
            'bkash_collection'     => round($bkashCollection, 2),
            'nagad_collection'     => round($nagadCollection, 2),
            'bank_collection'      => round($bankCollection, 2),
            'payment_breakdown'    => $paymentBreakdown,
            'current_stock_qty'    => (int) ($inventoryStats->total_stock_qty ?? 0),
            'inventory_value'      => round((float)($inventoryStats->total_inventory_value ?? 0.00), 2),
        ];
    }

    // =========================================================================
    // 3. FILTERABLE BRANCH SALES REPORT WITH ADVANCED SEARCH
    // =========================================================================

    /**
     * Get paginated sales report for a branch with detailed filters.
     */
    public static function getBranchSalesReport(int $branchId, array $filters = [], int $perPage = 25): array
    {
        $dates = self::resolveDatePeriod(
            $filters['period'] ?? 'this_month',
            $filters['start_date'] ?? null,
            $filters['end_date'] ?? null
        );

        $query = Order::with(['user', 'salesPerson', 'items.product', 'items.variant'])
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$dates['start'], $dates['end']]);

        if (!empty($filters['sales_person_id'])) {
            $query->where('sales_person_id', (int) $filters['sales_person_id']);
        }

        if (!empty($filters['payment_method']) && $filters['payment_method'] !== 'all') {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['order_status']) && $filters['order_status'] !== 'all') {
            $query->where('order_status', $filters['order_status']);
        }

        if (!empty($filters['order_type']) && $filters['order_type'] !== 'all') {
            $query->where('order_type', $filters['order_type']);
        }

        if (!empty($filters['search'])) {
            $term = trim($filters['search']);
            $query->where(function ($q) use ($term) {
                $q->where('invoice_no', 'LIKE', "%{$term}%")
                  ->orWhere('sender_number', 'LIKE', "%{$term}%")
                  ->orWhere('transaction_id', 'LIKE', "%{$term}%")
                  ->orWhereHas('user', function ($uq) use ($term) {
                      $uq->where('name', 'LIKE', "%{$term}%")
                         ->orWhere('phone', 'LIKE', "%{$term}%");
                  });
            });
        }

        $summary = (clone $query)->selectRaw("
            COUNT(id) as total_orders,
            COALESCE(SUM(grand_total), 0) as total_sales,
            COALESCE(SUM(paid_amount), 0) as total_paid,
            COALESCE(SUM(due_amount), 0) as total_due
        ")->first();

        $paginatedOrders = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return [
            'orders'       => $paginatedOrders,
            'total_orders' => (int) ($summary->total_orders ?? 0),
            'total_sales'  => (float) ($summary->total_sales ?? 0.00),
            'total_paid'   => (float) ($summary->total_paid ?? 0.00),
            'total_due'    => (float) ($summary->total_due ?? 0.00),
            'start_date'   => $dates['start']->toDateString(),
            'end_date'     => $dates['end']->toDateString(),
        ];
    }

    // =========================================================================
    // 4. SALES PERSON PERFORMANCE & COMMISSION ATTRIBUTION
    // =========================================================================

    /**
     * Compute sales performance metrics grouped by sales representative.
     */
    public static function getSalesPersonPerformance(int $branchId, array $filters = []): Collection
    {
        $dates = self::resolveDatePeriod(
            $filters['period'] ?? 'this_month',
            $filters['start_date'] ?? null,
            $filters['end_date'] ?? null
        );

        $records = DB::table('orders')
            ->join('users', 'orders.sales_person_id', '=', 'users.id')
            ->where('orders.branch_id', $branchId)
            ->whereBetween('orders.created_at', [$dates['start'], $dates['end']])
            ->whereNotIn('orders.order_status', ['cancelled', 'returned'])
            ->selectRaw("
                users.id as sales_person_id,
                users.name as sales_person_name,
                users.phone as sales_person_phone,
                users.email as sales_person_email,
                COUNT(orders.id) as total_orders,
                COALESCE(SUM(orders.grand_total), 0) as total_sales,
                COALESCE(SUM(orders.paid_amount), 0) as total_collected,
                COALESCE(SUM(orders.due_amount), 0) as total_due,
                COALESCE(SUM(orders.discount_amount), 0) as total_discount
            ")
            ->groupBy('users.id', 'users.name', 'users.phone', 'users.email')
            ->orderByDesc('total_sales')
            ->get();

        $hasPurchaseCostCol = Schema::hasColumn('order_items', 'purchase_cost');
        $costExpression = $hasPurchaseCostCol ? 'COALESCE(purchase_cost, 0)' : '0';

        return $records->map(function ($row) use ($branchId, $dates, $costExpression) {
            $orderIds = DB::table('orders')
                ->where('branch_id', $branchId)
                ->where('sales_person_id', $row->sales_person_id)
                ->whereBetween('created_at', [$dates['start'], $dates['end']])
                ->whereNotIn('order_status', ['cancelled', 'returned'])
                ->pluck('id');

            $qtySold = 0;
            $cogs = 0.00;

            if ($orderIds->isNotEmpty()) {
                $itemStats = DB::table('order_items')
                    ->whereIn('order_id', $orderIds)
                    ->selectRaw("
                        COALESCE(SUM(quantity), 0) as qty,
                        COALESCE(SUM(quantity * {$costExpression}), 0) as cogs
                    ")->first();

                $qtySold = (int) ($itemStats->qty ?? 0);
                $cogs = (float) ($itemStats->cogs ?? 0.00);
            }

            $totalSales = (float) $row->total_sales;
            $ordersCount = (int) $row->total_orders;

            $row->total_quantity_sold = $qtySold;
            $row->gross_profit = round(max(0, $totalSales - $cogs), 2);
            $row->avg_order_value = $ordersCount > 0 ? round($totalSales / $ordersCount, 2) : 0.00;

            return $row;
        });
    }

    // =========================================================================
    // 5. DAILY CASH REGISTER CLOSING & CASH AUDIT ENGINE
    // =========================================================================

    /**
     * Compute current live expected cash for an active register session today.
     * Formula:
     * Expected = (Opening + POS Cash Sales + Cash Due In + Cash In) - (Cash Out + Cash Expenses + Cash Refunds)
     */
    public static function calculateLiveSessionCash(int $branchId, float $openingCash = 0.00): array
    {
        $today = now()->toDateString();
        $start = now()->startOfDay();
        $end   = now()->endOfDay();

        // 1. POS Cash Sales Today (Case-insensitive match)
        $posCashSales = (float) DB::table('orders')
            ->where('branch_id', $branchId)
            ->where('order_type', 'pos')
            ->whereRaw("LOWER(payment_method) = 'cash'")
            ->whereBetween('created_at', [$start, $end])
            ->whereNotIn('order_status', ['cancelled', 'returned'])
            ->sum('paid_amount');

        // 2. POS Digital Sales Today (bKash, Nagad, Bank, Card)
        $digitalSales = (float) DB::table('orders')
            ->where('branch_id', $branchId)
            ->where('order_type', 'pos')
            ->whereRaw("LOWER(payment_method) != 'cash'")
            ->whereBetween('created_at', [$start, $end])
            ->whereNotIn('order_status', ['cancelled', 'returned'])
            ->sum('paid_amount');

        // 3. Cash Due Collections Today (Schema Safe Dynamic Column Resolver)
        $cashDueIn = 0.00;
        if (Schema::hasTable('payment_transactions')) {
            // কলামের নাম paid_amount, amount বা transaction_amount যা-ই হোক না কেন তা স্বয়ংক্রিয়ভাবে খুঁজে নেবে
            $amountCol = collect(['paid_amount', 'amount', 'transaction_amount', 'total_amount', 'pay_amount'])
                ->first(fn($c) => Schema::hasColumn('payment_transactions', $c));

            if ($amountCol) {
                $dueQuery = DB::table('payment_transactions')
                    ->whereRaw("LOWER(payment_method) = 'cash'")
                    ->whereBetween('created_at', [$start, $end]);

                if (Schema::hasColumn('payment_transactions', 'transaction_type')) {
                    $dueQuery->where('transaction_type', 'due_collection');
                }

                if (Schema::hasColumn('payment_transactions', 'branch_id')) {
                    $dueQuery->where('branch_id', $branchId);
                }

                $cashDueIn = (float) $dueQuery->sum($amountCol);
            }
        }

        // 4. Cash Expenses Today (Schema Safe Dynamic Column Resolver)
        $cashExpenses = 0.00;
        if (Schema::hasTable('expenses')) {
            $expAmountCol = collect(['amount', 'expense_amount', 'total_amount', 'total'])
                ->first(fn($c) => Schema::hasColumn('expenses', $c));

            if ($expAmountCol) {
                $expQuery = DB::table('expenses')
                    ->whereRaw("LOWER(payment_method) = 'cash'")
                    ->where('expense_date', $today);

                if (Schema::hasColumn('expenses', 'branch_id')) {
                    $expQuery->where('branch_id', $branchId);
                }

                $cashExpenses = (float) $expQuery->sum($expAmountCol);
            }
        }

        // 5. Cash In and Cash Out (from branch_daily_closings if session open)
        $session = DB::table('branch_daily_closings')
            ->where('branch_id', $branchId)
            ->where('closing_date', $today)
            ->first();

        $baseOpening = $session ? (float) $session->opening_cash : $openingCash;
        $cashIn      = $session ? (float) $session->cash_in : 0.00;
        $cashOut     = $session ? (float) $session->cash_out : 0.00;
        $cashRefunds = $session ? (float) $session->cash_refunds : 0.00;

        $expectedCash = ($baseOpening + $posCashSales + $cashDueIn + $cashIn) - ($cashExpenses + $cashRefunds + $cashOut);
        $expectedCash = round(max(0.00, $expectedCash), 2);

        $totalSalesAmount = round($posCashSales + $digitalSales, 2);

        $totalOrdersCount = (int) DB::table('orders')
            ->where('branch_id', $branchId)
            ->where('order_type', 'pos')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        return [
            'closing_date'         => $today,
            'opening_cash'         => round($baseOpening, 2),
            'pos_cash_sales'       => round($posCashSales, 2),
            'digital_sales_amount' => round($digitalSales, 2),
            'total_sales_amount'   => $totalSalesAmount,
            'total_orders_count'   => $totalOrdersCount,
            'cash_due_collection'  => round($cashDueIn, 2),
            'cash_in'              => round($cashIn, 2),
            'cash_out'             => round($cashOut, 2),
            'cash_expenses'        => round($cashExpenses, 2),
            'cash_refunds'         => round($cashRefunds, 2),
            'expected_cash'        => $expectedCash,
            'session_exists'       => (bool) $session,
            'session_status'       => $session?->status ?? 'unopened',
        ];
    }

    /**
     * Open daily cash register session for a branch.
     */
    public static function openDailyRegister(
        int $branchId,
        float $openingCash,
        ?string $note = null,
        ?int $userId = null
    ): object {
        $today = now()->toDateString();
        $userId = $userId ?? Auth::id();

        $existing = DB::table('branch_daily_closings')
            ->where('branch_id', $branchId)
            ->where('closing_date', $today)
            ->first();

        if ($existing && $existing->status === 'open') {
            throw new Exception("Cash register for today is already open.");
        }

        $calc = self::calculateLiveSessionCash($branchId, $openingCash);

        $data = [
            'branch_id'            => $branchId,
            'user_id'              => $userId,
            'closing_date'         => $today,
            'opening_cash'         => $openingCash,
            'pos_cash_sales'       => $calc['pos_cash_sales'],
            'digital_sales_amount' => $calc['digital_sales_amount'],
            'total_sales_amount'   => $calc['total_sales_amount'],
            'total_orders_count'   => $calc['total_orders_count'],
            'cash_due_collection'  => $calc['cash_due_collection'],
            'cash_expenses'        => $calc['cash_expenses'],
            'cash_refunds'         => $calc['cash_refunds'],
            'expected_cash'        => $calc['expected_cash'],
            'status'               => 'open',
            'opened_at'            => now(),
            'notes'                => $note,
            'updated_at'           => now(),
        ];

        if ($existing) {
            DB::table('branch_daily_closings')->where('id', $existing->id)->update($data);
            return (object) array_merge((array) $existing, $data);
        }

        $data['created_at'] = now();
        $id = DB::table('branch_daily_closings')->insertGetId($data);

        return (object) array_merge($data, ['id' => $id]);
    }

    /**
     * Close daily cash register session and record physical actual cash count.
     */
    public static function closeDailyRegister(
        int $branchId,
        float $actualCash,
        ?string $note = null,
        ?int $userId = null
    ): object {
        $today = now()->toDateString();
        $userId = $userId ?? Auth::id();

        $session = DB::table('branch_daily_closings')
            ->where('branch_id', $branchId)
            ->where('closing_date', $today)
            ->first();

        $calc = self::calculateLiveSessionCash($branchId, $session ? (float)$session->opening_cash : 0.00);

        $expectedCash = $calc['expected_cash'];
        $difference = round($actualCash - $expectedCash, 2);

        $updateData = [
            'user_id'              => $userId,
            'pos_cash_sales'       => $calc['pos_cash_sales'],
            'digital_sales_amount' => $calc['digital_sales_amount'],
            'total_sales_amount'   => $calc['total_sales_amount'],
            'total_orders_count'   => $calc['total_orders_count'],
            'cash_due_collection'  => $calc['cash_due_collection'],
            'cash_expenses'        => $calc['cash_expenses'],
            'cash_refunds'         => $calc['cash_refunds'],
            'expected_cash'        => $expectedCash,
            'actual_cash'          => round($actualCash, 2),
            'difference'           => $difference,
            'status'               => 'closed',
            'closed_at'            => now(),
            'notes'                => $note ?? $session?->notes,
            'updated_at'           => now(),
        ];

        if ($session) {
            DB::table('branch_daily_closings')->where('id', $session->id)->update($updateData);
            return (object) array_merge((array) $session, $updateData);
        }

        $updateData['branch_id']    = $branchId;
        $updateData['closing_date'] = $today;
        $updateData['opening_cash'] = $calc['opening_cash'];
        $updateData['created_at']   = now();

        $id = DB::table('branch_daily_closings')->insertGetId($updateData);

        return (object) array_merge($updateData, ['id' => $id]);
    }

    // =========================================================================
    // 6. MASTER BRANCH COMPARISON REPORT (SUPER ADMIN)
    // =========================================================================

    /**
     * Compare performance metrics across all active branches side-by-side.
     */
    public static function getMasterBranchComparisonReport(?string $startDate = null, ?string $endDate = null): Collection
    {
        $dates = self::resolveDatePeriod('custom', $startDate ?? now()->startOfMonth()->toDateString(), $endDate ?? now()->endOfMonth()->toDateString());

        $branches = Branch::active()->orderBy('name', 'asc')->get();

        return $branches->map(function ($branch) use ($dates) {
            $metrics = self::getBranchDashboardMetrics(
                branchId: $branch->id,
                period: 'custom',
                startDate: $dates['start']->toDateString(),
                endDate: $dates['end']->toDateString()
            );

            return (object) [
                'branch_id'        => $branch->id,
                'branch_name'      => $branch->name,
                'branch_code'      => $branch->branch_code ?? $branch->code,
                'branch_type'      => $branch->branch_type ?? $branch->type,
                'total_orders'     => $metrics['total_orders'],
                'total_sales'      => $metrics['total_sales'],
                'total_paid'       => $metrics['total_paid'],
                'total_due'        => $metrics['total_due'],
                'gross_profit'     => $metrics['gross_profit'],
                'expenses'         => $metrics['total_expenses'],
                'net_profit'       => $metrics['net_profit'],
                'stock_qty'        => $metrics['current_stock_qty'],
                'inventory_value'  => $metrics['inventory_value'],
            ];
        });
    }
}