<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinancialReportController extends Controller
{
    /**
     * Dynamic fail-safe column resolver for order_items table.
     * Prevents SQL 1054 Unknown Column errors across diverse schemas.
     */
    protected function getOrderItemColumnExpressions(): array
    {
        $hasQty = Schema::hasColumn('order_items', 'quantity');
        $hasAltQty = Schema::hasColumn('order_items', 'qty');
        $qtyExpr = $hasQty ? 'order_items.quantity' : ($hasAltQty ? 'order_items.qty' : '1');

        $hasUnitPrice = Schema::hasColumn('order_items', 'unit_price');
        $hasPrice = Schema::hasColumn('order_items', 'price');
        $priceExpr = $hasUnitPrice ? 'order_items.unit_price' : ($hasPrice ? 'order_items.price' : '0');

        $hasSubtotal = Schema::hasColumn('order_items', 'subtotal');
        $subtotalExpr = $hasSubtotal ? 'order_items.subtotal' : "({$qtyExpr} * {$priceExpr})";

        $hasCost = Schema::hasColumn('order_items', 'purchase_cost');
        $hasAltCost = Schema::hasColumn('order_items', 'cost');
        $costExpr = $hasCost ? 'order_items.purchase_cost' : ($hasAltCost ? 'order_items.cost' : '0');

        $hasCogs = Schema::hasColumn('order_items', 'cogs');
        $cogsExpr = $hasCogs 
            ? "CASE WHEN COALESCE(order_items.cogs, 0) > 0 THEN order_items.cogs ELSE ({$qtyExpr} * COALESCE({$costExpr}, 0)) END"
            : "({$qtyExpr} * COALESCE({$costExpr}, 0))";

        $hasTitle = Schema::hasColumn('order_items', 'product_title');
        $hasAltTitle = Schema::hasColumn('order_items', 'product_name');
        $titleExpr = $hasTitle ? 'order_items.product_title' : ($hasAltTitle ? 'order_items.product_name' : "'Product'");

        $hasSku = Schema::hasColumn('order_items', 'sku');
        $skuExpr = $hasSku ? 'order_items.sku' : "CONCAT('PRD-', order_items.product_id)";

        return [
            'qty'      => $qtyExpr,
            'price'    => $priceExpr,
            'subtotal' => $subtotalExpr,
            'cost'     => $costExpr,
            'cogs'     => $cogsExpr,
            'title'    => $titleExpr,
            'sku'      => $skuExpr,
        ];
    }

    /**
     * Resolve Date Range Filters based on request
     */
    protected function resolveDateRange(Request $request): array
    {
        $dateFilter = $request->input('date_filter', 'this_month');
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfDay();

        switch ($dateFilter) {
            case 'today':
                $start = Carbon::today()->startOfDay();
                $end   = Carbon::today()->endOfDay();
                break;
            case 'yesterday':
                $start = Carbon::yesterday()->startOfDay();
                $end   = Carbon::yesterday()->endOfDay();
                break;
            case 'this_week':
                $start = Carbon::now()->startOfWeek();
                $end   = Carbon::now()->endOfWeek();
                break;
            case 'last_month':
                $start = Carbon::now()->subMonth()->startOfMonth();
                $end   = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'this_year':
                $start = Carbon::now()->startOfYear();
                $end   = Carbon::now()->endOfYear();
                break;
            case 'all':
                $start = Carbon::parse('2020-01-01')->startOfDay();
                $end   = Carbon::now()->endOfDay();
                break;
            case 'custom':
                if ($request->filled('start_date')) {
                    $start = Carbon::parse($request->start_date)->startOfDay();
                }
                if ($request->filled('end_date')) {
                    $end = Carbon::parse($request->end_date)->endOfDay();
                }
                break;
            case 'this_month':
            default:
                $start = Carbon::now()->startOfMonth();
                $end   = Carbon::now()->endOfDay();
                break;
        }

        return [$start, $end, $dateFilter];
    }

    /**
     * Comprehensive Profit & Loss (P&L) Statement & Financial KPI Dashboard
     */
    public function profitLoss(Request $request)
    {
        // 1. Resolve Date Range Filters
        [$start, $end, $dateFilter] = $this->resolveDateRange($request);

        // 2. Base Query for Delivered / Completed Orders in the Period
        $ordersQuery = DB::table('orders')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotIn(DB::raw('LOWER(COALESCE(order_status, ""))'), ['cancelled', 'incomplete', 'failed']);

        // Safe Schema Column Checks for Orders
        $hasOrderSource = Schema::hasColumn('orders', 'order_source');
        $hasOrderType   = Schema::hasColumn('orders', 'order_type');

        // Channel Filter (All, Web, Manual, POS)
        if ($request->filled('channel') && $request->channel !== 'all') {
            $ch = strtolower($request->channel);
            if ($hasOrderSource) {
                if ($ch === 'pos') {
                    $ordersQuery->where('order_source', 'pos');
                } elseif ($ch === 'manual') {
                    $ordersQuery->whereIn('order_source', ['manual', 'facebook', 'whatsapp', 'phone', 'walk_in', 'other']);
                } elseif ($ch === 'web') {
                    $ordersQuery->where(function ($sq) {
                        $sq->whereNull('order_source')
                           ->orWhere('order_source', 'web')
                           ->orWhere('order_source', 'online');
                    });
                }
            } elseif ($hasOrderType) {
                if ($ch === 'pos') {
                    $ordersQuery->where('order_type', 'pos');
                } elseif ($ch === 'manual') {
                    $ordersQuery->where('order_type', 'manual');
                } elseif ($ch === 'web') {
                    $ordersQuery->where(function ($sq) {
                        $sq->whereNull('order_type')
                           ->orWhere('order_type', 'online')
                           ->orWhere('order_type', 'web');
                    });
                }
            } else {
                if ($ch !== 'web') {
                    $ordersQuery->whereRaw('1 = 0');
                }
            }
        }

        $deliveredOrders = (clone $ordersQuery)
            ->whereIn(DB::raw('LOWER(COALESCE(order_status, ""))'), ['delivered', 'completed'])
            ->get();

        $deliveredOrderIds = $deliveredOrders->pluck('id')->toArray();

        // 3. Sales & Revenue Calculations (Fail-Safe via Collection)
        $grossSales = (float) $deliveredOrders->sum(function ($o) {
            return (float) ($o->product_subtotal ?? ($o->subtotal ?? ($o->grand_total ?? 0)));
        });

        $discounts = (float) $deliveredOrders->sum(function ($o) {
            return (float) ($o->discount ?? ($o->discount_amount ?? 0));
        });

        $netProductSales = max(0.00, $grossSales - $discounts);

        // 4. Safe Cost of Goods Sold (COGS) Calculation from Order Items
        $cols = $this->getOrderItemColumnExpressions();
        $cogs = 0.00;

        if (!empty($deliveredOrderIds) && Schema::hasTable('order_items')) {
            $cogs = (float) DB::table('order_items')
                ->whereIn('order_id', $deliveredOrderIds)
                ->sum(DB::raw($cols['cogs']));
        }

        // Gross Profit from Products
        $grossProfit = max(0.00, $netProductSales - $cogs);
        $grossMargin = $netProductSales > 0 ? round(($grossProfit / $netProductSales) * 100, 2) : 0.00;

        // 5. Logistics / Courier Shipping Balance
        $shippingCollected = (float) $deliveredOrders->sum(function ($o) {
            return (float) ($o->delivery_charge ?? ($o->shipping_charge ?? 0));
        });

        $courierCostSpent   = 0.00;
        $courierCodCharges  = 0.00;
        $courierReturnCosts = 0.00;

        if (Schema::hasTable('courier_bookings')) {
            $courierQuery = DB::table('courier_bookings')->whereBetween('created_at', [$start, $end]);
            
            if (Schema::hasColumn('courier_bookings', 'courier_cost')) {
                $courierCostSpent = (float) (clone $courierQuery)->sum('courier_cost');
            }
            if (Schema::hasColumn('courier_bookings', 'cod_charge')) {
                $courierCodCharges = (float) (clone $courierQuery)->sum('cod_charge');
            }
            if (Schema::hasColumn('courier_bookings', 'return_cost')) {
                $courierReturnCosts = (float) (clone $courierQuery)->sum('return_cost');
            }
        }

        $logisticsNetProfit = $shippingCollected - ($courierCostSpent + $courierCodCharges + $courierReturnCosts);

        // 6. Damaged / Scrapped Product Losses
        $damageLoss = 0.00;
        if (Schema::hasTable('inventory_movements')) {
            $qtyField = Schema::hasColumn('inventory_movements', 'quantity_change') 
                ? 'quantity_change' 
                : (Schema::hasColumn('inventory_movements', 'quantity') ? 'quantity' : '0');

            $costField = Schema::hasColumn('inventory_movements', 'unit_cost') 
                ? 'unit_cost' 
                : (Schema::hasColumn('inventory_movements', 'cost') ? 'cost' : '0');

            if (Schema::hasColumn('inventory_movements', 'movement_type')) {
                $damageLoss = (float) DB::table('inventory_movements')
                    ->whereBetween('created_at', [$start, $end])
                    ->whereIn('movement_type', ['damaged_writeoff', 'damage_out'])
                    ->sum(DB::raw("ABS(COALESCE({$qtyField}, 0)) * COALESCE({$costField}, 0)"));
            }
        }

        // 7. Operational Expenses & Overheads Calculation (from expenses table)
        $operatingExpenses = 0.00;
        $expenseCategoryBreakdown = collect();

        if (Schema::hasTable('expenses')) {
            $dateCol = Schema::hasColumn('expenses', 'expense_date') ? 'expense_date' : 'created_at';
            $expenseQuery = DB::table('expenses')
                ->whereBetween($dateCol, [$start->toDateString(), $end->toDateString()]);

            $operatingExpenses = (float) (clone $expenseQuery)->sum('amount');

            if (Schema::hasTable('expense_categories')) {
                $expenseCategoryBreakdown = (clone $expenseQuery)
                    ->leftJoin('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
                    ->select(
                        DB::raw("COALESCE(expense_categories.name, 'Uncategorized') as category_name"),
                        DB::raw("SUM(expenses.amount) as total_amount")
                    )
                    ->groupBy('expense_categories.name')
                    ->orderByDesc('total_amount')
                    ->get();
            }
        }

        // 8. Other Business Inflows / Income (from incomes table)
        $otherIncome = 0.00;
        $incomeCategoryBreakdown = collect();

        if (Schema::hasTable('incomes')) {
            $incDateCol = Schema::hasColumn('incomes', 'income_date') ? 'income_date' : 'created_at';
            $incomeQuery = DB::table('incomes')
                ->whereBetween($incDateCol, [$start->toDateString(), $end->toDateString()]);

            $otherIncome = (float) (clone $incomeQuery)->sum('amount');

            if (Schema::hasTable('income_categories')) {
                $incomeCategoryBreakdown = (clone $incomeQuery)
                    ->leftJoin('income_categories', 'incomes.category_id', '=', 'income_categories.id')
                    ->select(
                        DB::raw("COALESCE(income_categories.name, 'Other Income') as category_name"),
                        DB::raw("SUM(incomes.amount) as total_amount")
                    )
                    ->groupBy('income_categories.name')
                    ->orderByDesc('total_amount')
                    ->get();
            }
        }

        // 9. Net Bottom-Line Profit Calculation
        $grossOperatingProfit = ($grossProfit + $logisticsNetProfit) - $damageLoss;
        $netOperatingProfit = ($grossOperatingProfit + $otherIncome) - $operatingExpenses;
        $netOperatingMargin = $netProductSales > 0 ? round(($netOperatingProfit / $netProductSales) * 100, 2) : 0.00;

        // 10. Dynamic Channel Performance Breakdown
        $webQuery = clone $ordersQuery;
        $manualQuery = clone $ordersQuery;
        $posQuery = clone $ordersQuery;

        if ($hasOrderSource) {
            $webQuery->where(fn($q) => $q->whereNull('order_source')->orWhereIn('order_source', ['web', 'online']));
            $manualQuery->whereIn('order_source', ['manual', 'facebook', 'whatsapp', 'phone', 'walk_in', 'other']);
            $posQuery->where('order_source', 'pos');
        } elseif ($hasOrderType) {
            $webQuery->where(fn($q) => $q->whereNull('order_type')->orWhereIn('order_type', ['web', 'online']));
            $manualQuery->where('order_type', 'manual');
            $posQuery->where('order_type', 'pos');
        } else {
            $manualQuery->whereRaw('1 = 0');
            $posQuery->whereRaw('1 = 0');
        }

        $channelStats = [
            'web' => [
                'count'   => $webQuery->count(),
                'revenue' => (float) $webQuery->sum('grand_total'),
            ],
            'manual' => [
                'count'   => $manualQuery->count(),
                'revenue' => (float) $manualQuery->sum('grand_total'),
            ],
            'pos' => [
                'count'   => $posQuery->count(),
                'revenue' => (float) $posQuery->sum('grand_total'),
            ],
        ];

        // 11. Top 8 High-Margin Products
        $topProfitableProducts = collect();
        if (!empty($deliveredOrderIds) && Schema::hasTable('order_items')) {
            $topProfitableProducts = DB::table('order_items')
                ->whereIn('order_id', $deliveredOrderIds)
                ->select(
                    'order_items.product_id',
                    DB::raw("MAX({$cols['title']}) as title"),
                    DB::raw("SUM({$cols['qty']}) as total_qty"),
                    DB::raw("SUM({$cols['subtotal']}) as total_revenue"),
                    DB::raw("SUM({$cols['cogs']}) as total_cogs")
                )
                ->groupBy('order_items.product_id')
                ->orderByRaw("(SUM({$cols['subtotal']}) - SUM({$cols['cogs']})) DESC")
                ->limit(8)
                ->get()
                ->map(function ($item) {
                    $item->profit = max(0.00, (float)$item->total_revenue - (float)$item->total_cogs);
                    $item->margin = (float)$item->total_revenue > 0 ? round(($item->profit / (float)$item->total_revenue) * 100, 1) : 0;
                    return $item;
                });
        }

        $summary = [
            'period_label'            => $start->format('d M Y') . ' — ' . $end->format('d M Y'),
            'delivered_orders'        => count($deliveredOrders),
            'gross_sales'             => $grossSales,
            'discounts'               => $discounts,
            'net_sales'               => $netProductSales,
            'cogs'                    => $cogs,
            'gross_profit'            => $grossProfit,
            'gross_margin'            => $grossMargin,
            'shipping_collected'      => $shippingCollected,
            'courier_cost_spent'      => $courierCostSpent,
            'courier_cod_fee'         => $courierCodCharges,
            'courier_return_loss'     => $courierReturnCosts,
            'logistics_balance'       => $logisticsNetProfit,
            'damage_loss'             => $damageLoss,
            'other_income'            => $otherIncome,
            'operating_expenses'      => $operatingExpenses,
            'gross_operating_profit'  => $grossOperatingProfit,
            'net_profit'              => $netOperatingProfit,
            'net_margin'              => $netOperatingMargin,
        ];

        return view('admin.reports.profit_loss', compact(
            'summary',
            'channelStats',
            'topProfitableProducts',
            'expenseCategoryBreakdown',
            'incomeCategoryBreakdown',
            'dateFilter',
            'start',
            'end'
        ));
    }

    /**
     * 1-Click Dynamic P&L Statement CSV Export with Real Operating Numbers
     */
    public function exportPnl(Request $request)
    {
        [$start, $end] = $this->resolveDateRange($request);
        $filename = "PnL_Statement_" . $start->format('Y_m_d') . "_to_" . $end->format('Y_m_d') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($start, $end) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['STATEMENT OF PROFIT AND LOSS (P&L AUDIT REPORT)', $start->format('d M Y') . ' — ' . $end->format('d M Y')]);
            fputcsv($file, []);
            fputcsv($file, ['Financial Ledger Account / Performance Metric', 'Accounting Impact', 'Amount (BDT)']);

            $deliveredOrders = DB::table('orders')
                ->whereBetween('created_at', [$start, $end])
                ->whereIn(DB::raw('LOWER(COALESCE(order_status, ""))'), ['delivered', 'completed'])
                ->get();

            $grossSales = (float) $deliveredOrders->sum(fn($o) => (float)($o->product_subtotal ?? ($o->subtotal ?? ($o->grand_total ?? 0))));
            $discounts  = (float) $deliveredOrders->sum(fn($o) => (float)($o->discount ?? ($o->discount_amount ?? 0)));
            $netSales   = max(0.00, $grossSales - $discounts);

            $deliveredIds = $deliveredOrders->pluck('id')->toArray();
            $cols = $this->getOrderItemColumnExpressions();
            $cogs = 0.00;
            if (!empty($deliveredIds) && Schema::hasTable('order_items')) {
                $cogs = (float) DB::table('order_items')->whereIn('order_id', $deliveredIds)->sum(DB::raw($cols['cogs']));
            }
            $grossProfit = max(0.00, $netSales - $cogs);

            $shippingCollected = (float) $deliveredOrders->sum(fn($o) => (float)($o->delivery_charge ?? ($o->shipping_charge ?? 0)));
            $courierCost = Schema::hasTable('courier_bookings') 
                ? (float) DB::table('courier_bookings')->whereBetween('created_at', [$start, $end])->sum('courier_cost') 
                : 0.00;
            $netLogistics = $shippingCollected - $courierCost;

            $damageLoss = 0.00;
            if (Schema::hasTable('inventory_movements')) {
                $qtyField = Schema::hasColumn('inventory_movements', 'quantity_change') ? 'quantity_change' : 'quantity';
                $costField = Schema::hasColumn('inventory_movements', 'unit_cost') ? 'unit_cost' : 'cost';
                $damageLoss = (float) DB::table('inventory_movements')
                    ->whereBetween('created_at', [$start, $end])
                    ->whereIn('movement_type', ['damaged_writeoff', 'damage_out'])
                    ->sum(DB::raw("ABS(COALESCE({$qtyField}, 0)) * COALESCE({$costField}, 0)"));
            }

            $otherIncome = Schema::hasTable('incomes')
                ? (float) DB::table('incomes')->whereBetween(Schema::hasColumn('incomes', 'income_date') ? 'income_date' : 'created_at', [$start->toDateString(), $end->toDateString()])->sum('amount')
                : 0.00;

            $operatingExpenses = Schema::hasTable('expenses')
                ? (float) DB::table('expenses')->whereBetween(Schema::hasColumn('expenses', 'expense_date') ? 'expense_date' : 'created_at', [$start->toDateString(), $end->toDateString()])->sum('amount')
                : 0.00;

            $netProfit = ($grossProfit + $netLogistics + $otherIncome) - ($damageLoss + $operatingExpenses);

            fputcsv($file, ['1. Gross Sales (Product Subtotals)', 'Revenue (+)', number_format($grossSales, 2, '.', '')]);
            fputcsv($file, ['2. Special Discounts Granted', 'Deduction (-)', number_format($discounts, 2, '.', '')]);
            fputcsv($file, ['3. Net Sales Revenue', 'Net Sales', number_format($netSales, 2, '.', '')]);
            fputcsv($file, ['4. Cost of Goods Sold (COGS)', 'Product Cost (-)', number_format($cogs, 2, '.', '')]);
            fputcsv($file, ['5. Gross Product Profit', 'Gross Margin', number_format($grossProfit, 2, '.', '')]);
            fputcsv($file, ['6. Customer Shipping Fees Collected', 'Logistics (+)', number_format($shippingCollected, 2, '.', '')]);
            fputcsv($file, ['7. Courier Dispatch Charges Paid', 'Logistics (-)', number_format($courierCost, 2, '.', '')]);
            fputcsv($file, ['8. Damaged & Written-Off Inventory Loss', 'Loss (-)', number_format($damageLoss, 2, '.', '')]);
            fputcsv($file, ['9. Other Business Inflow / Income', 'Inflow (+)', number_format($otherIncome, 2, '.', '')]);
            fputcsv($file, ['10. Operational Expenses (Rent, Ads, Salary, etc.)', 'Overheads (-)', number_format($operatingExpenses, 2, '.', '')]);
            fputcsv($file, []);
            fputcsv($file, ['FINAL NET BOTTOM-LINE PROFIT (EBIT)', 'Net Operating Income', number_format($netProfit, 2, '.', '') . ' BDT']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Comprehensive Statement of Financial Position (Balance Sheet)
     */
    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->input('as_of_date', Carbon::today()->toDateString());
        $asOfCarbon = Carbon::parse($asOfDate)->endOfDay();

        // 1. Current Assets
        $bankAccounts = collect();
        $totalBankCash = 0.00;

        if (Schema::hasTable('bank_accounts')) {
            $bankAccounts = DB::table('bank_accounts')
                ->where('is_active', true)
                ->orderByDesc('current_balance')
                ->get();

            $totalBankCash = (float) $bankAccounts->sum('current_balance');
        }

        $inventoryValuation = 0.00;
        if (Schema::hasTable('product_variants')) {
            $stockCol = Schema::hasColumn('product_variants', 'stock') ? 'stock' : (Schema::hasColumn('product_variants', 'quantity') ? 'quantity' : '0');
            $costCol  = Schema::hasColumn('product_variants', 'purchase_cost') ? 'purchase_cost' : (Schema::hasColumn('product_variants', 'cost') ? 'cost' : '0');
            $variantValuation = (float) DB::table('product_variants')->sum(DB::raw("GREATEST(0, COALESCE({$stockCol}, 0)) * COALESCE({$costCol}, 0)"));
            $inventoryValuation += $variantValuation;
        }

        if ($inventoryValuation <= 0 && Schema::hasTable('products')) {
            $prodStockCol = Schema::hasColumn('products', 'stock') ? 'stock' : (Schema::hasColumn('products', 'quantity') ? 'quantity' : '0');
            $prodCostCol  = Schema::hasColumn('products', 'purchase_cost') 
                ? 'purchase_cost' 
                : (Schema::hasColumn('products', 'purchase_price') ? 'purchase_price' : (Schema::hasColumn('products', 'cost') ? 'cost' : '0'));
            $inventoryValuation = (float) DB::table('products')->sum(DB::raw("GREATEST(0, COALESCE({$prodStockCol}, 0)) * COALESCE({$prodCostCol}, 0)"));
        }

        $courierReceivables = 0.00;
        if (Schema::hasTable('orders')) {
            $courierReceivables = (float) DB::table('orders')
                ->where('created_at', '<=', $asOfCarbon)
                ->whereIn(DB::raw('LOWER(COALESCE(order_status, ""))'), ['shipped', 'out_for_delivery', 'courier_in_transit', 'in_transit'])
                ->sum('grand_total');
        }

        $totalCurrentAssets = $totalBankCash + $inventoryValuation + $courierReceivables;

        // 2. Non-Current (Fixed) Assets
        $fixedAssets = collect();
        $totalFixedAssets = 0.00;

        if (Schema::hasTable('assets')) {
            $fixedAssetQuery = DB::table('assets')
                ->where('purchase_date', '<=', $asOfDate);

            $totalFixedAssets = (float) (clone $fixedAssetQuery)->sum('purchase_cost');

            $fixedAssets = (clone $fixedAssetQuery)
                ->select('asset_type', DB::raw('SUM(purchase_cost) as total_cost'), DB::raw('COUNT(id) as count'))
                ->groupBy('asset_type')
                ->get();
        }

        $totalAssets = $totalCurrentAssets + $totalFixedAssets;

        // 3. Liabilities
        $currentLiabilities = 0.00;

        // 4. Equity & Capital Reserves
        $totalInvestedCapital = 0.00;
        $investorBreakdown = collect();

        if (Schema::hasTable('investments')) {
            $invQuery = DB::table('investments')
                ->where('investment_date', '<=', $asOfDate);

            $totalInvestedCapital = (float) (clone $invQuery)->sum('amount');

            $investorBreakdown = (clone $invQuery)
                ->select('investor_name', DB::raw('SUM(amount) as total_capital'))
                ->groupBy('investor_name')
                ->orderByDesc('total_capital')
                ->get();
        }

        // Retained Earnings (Accumulated Net Operating Profit)
        $cols = $this->getOrderItemColumnExpressions();

        $allDeliveredOrders = DB::table('orders')
            ->where('created_at', '<=', $asOfCarbon)
            ->whereIn(DB::raw('LOWER(COALESCE(order_status, ""))'), ['delivered', 'completed'])
            ->get();

        $allDeliveredIds = $allDeliveredOrders->pluck('id')->toArray();

        $lifetimeGrossSales = (float) $allDeliveredOrders->sum(fn($o) => (float)($o->product_subtotal ?? ($o->subtotal ?? ($o->grand_total ?? 0))));
        $lifetimeDiscounts  = (float) $allDeliveredOrders->sum(fn($o) => (float)($o->discount ?? ($o->discount_amount ?? 0)));
        $lifetimeNetSales   = max(0.00, $lifetimeGrossSales - $lifetimeDiscounts);

        $lifetimeCogs = 0.00;
        if (!empty($allDeliveredIds) && Schema::hasTable('order_items')) {
            $lifetimeCogs = (float) DB::table('order_items')
                ->whereIn('order_id', $allDeliveredIds)
                ->sum(DB::raw($cols['cogs']));
        }

        $lifetimeShipping = (float) $allDeliveredOrders->sum(fn($o) => (float)($o->delivery_charge ?? ($o->shipping_charge ?? 0)));
        $lifetimeCourierCost = Schema::hasTable('courier_bookings') 
            ? (float) DB::table('courier_bookings')->where('created_at', '<=', $asOfCarbon)->sum('courier_cost') 
            : 0.00;
        $lifetimeLogistics = $lifetimeShipping - $lifetimeCourierCost;

        $lifetimeDamageLoss = 0.00;
        if (Schema::hasTable('inventory_movements')) {
            $lifetimeDamageLoss = (float) DB::table('inventory_movements')
                ->where('created_at', '<=', $asOfCarbon)
                ->whereIn('movement_type', ['damaged_writeoff', 'damage_out'])
                ->sum(DB::raw('ABS(quantity_change) * unit_cost'));
        }

        $lifetimeOtherIncome = Schema::hasTable('incomes')
            ? (float) DB::table('incomes')->where('income_date', '<=', $asOfDate)->sum('amount')
            : 0.00;

        $lifetimeExpenses = Schema::hasTable('expenses')
            ? (float) DB::table('expenses')->where('expense_date', '<=', $asOfDate)->sum('amount')
            : 0.00;

        $retainedEarnings = ($lifetimeNetSales - $lifetimeCogs) + $lifetimeLogistics + $lifetimeOtherIncome - ($lifetimeDamageLoss + $lifetimeExpenses);

        $totalEquity = $totalInvestedCapital + $retainedEarnings;
        $totalLiabilitiesAndEquity = $currentLiabilities + $totalEquity;
        $equationDifference = round($totalAssets - $totalLiabilitiesAndEquity, 2);

        $balanceSheetData = [
            'as_of_date'            => $asOfCarbon->format('d M Y'),
            'total_bank_cash'       => $totalBankCash,
            'inventory_valuation'   => $inventoryValuation,
            'courier_receivables'   => $courierReceivables,
            'total_current_assets'  => $totalCurrentAssets,
            'total_fixed_assets'    => $totalFixedAssets,
            'total_assets'          => $totalAssets,
            'current_liabilities'   => $currentLiabilities,
            'total_invested_capital'=> $totalInvestedCapital,
            'retained_earnings'     => $retainedEarnings,
            'total_equity'          => $totalEquity,
            'total_liab_equity'     => $totalLiabilitiesAndEquity,
            'difference'            => $equationDifference,
        ];

        return view('admin.reports.balance_sheet', compact(
            'balanceSheetData',
            'bankAccounts',
            'fixedAssets',
            'investorBreakdown',
            'asOfDate'
        ));
    }

    /**
     * 1-Click Balance Sheet Audit CSV Export
     */
    public function exportBalanceSheet(Request $request)
    {
        $asOfDate = $request->input('as_of_date', Carbon::today()->toDateString());
        $filename = "Balance_Sheet_" . str_replace('-', '_', $asOfDate) . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($asOfDate) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['STATEMENT OF FINANCIAL POSITION (BALANCE SHEET)', 'As of ' . date('d M Y', strtotime($asOfDate))]);
            fputcsv($file, []);
            fputcsv($file, ['Account Classification', 'Ledger Particulars', 'Amount (BDT)']);

            $cash = Schema::hasTable('bank_accounts') ? (float) DB::table('bank_accounts')->where('is_active', true)->sum('current_balance') : 0;
            $assetsVal = Schema::hasTable('assets') ? (float) DB::table('assets')->where('purchase_date', '<=', $asOfDate)->sum('purchase_cost') : 0;
            $capital = Schema::hasTable('investments') ? (float) DB::table('investments')->where('investment_date', '<=', $asOfDate)->sum('amount') : 0;

            fputcsv($file, ['CURRENT ASSETS', 'Liquid Cash & Bank Accounts', number_format($cash, 2, '.', '')]);
            fputcsv($file, ['NON-CURRENT ASSETS', 'Fixed Assets & Physical Property', number_format($assetsVal, 2, '.', '')]);
            fputcsv($file, ['TOTAL ASSETS', 'Combined Corporate Valuation', number_format($cash + $assetsVal, 2, '.', '')]);
            fputcsv($file, []);
            fputcsv($file, ['EQUITY & CAPITAL', 'Contributed Investor Capital', number_format($capital, 2, '.', '')]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Comprehensive Statement of Cash Flows (Operating, Investing, Financing)
     */
    public function cashFlow(Request $request)
    {
        [$start, $end, $dateFilter] = $this->resolveDateRange($request);

        // ==========================================
        // 1. CASH FLOWS FROM OPERATING ACTIVITIES
        // ==========================================
        $deliveredOrders = DB::table('orders')
            ->whereBetween('created_at', [$start, $end])
            ->whereIn(DB::raw('LOWER(COALESCE(order_status, ""))'), ['delivered', 'completed'])
            ->get();

        $customerCashReceived = (float) $deliveredOrders->sum(function ($o) {
            $sub  = (float) ($o->product_subtotal ?? ($o->subtotal ?? ($o->grand_total ?? 0)));
            $disc = (float) ($o->discount ?? ($o->discount_amount ?? 0));
            $ship = (float) ($o->delivery_charge ?? ($o->shipping_charge ?? 0));
            return max(0.00, $sub - $disc) + $ship;
        });

        $operatingInflows = 0.00;
        if (Schema::hasTable('incomes')) {
            $incCol = Schema::hasColumn('incomes', 'income_date') ? 'income_date' : 'created_at';
            $operatingInflows = (float) DB::table('incomes')->whereBetween($incCol, [$start->toDateString(), $end->toDateString()])->sum('amount');
        }

        $courierCostPaid = 0.00;
        if (Schema::hasTable('courier_bookings')) {
            $courierCostPaid = (float) DB::table('courier_bookings')->whereBetween('created_at', [$start, $end])->sum('courier_cost');
        }

        $operatingExpensesPaid = 0.00;
        if (Schema::hasTable('expenses')) {
            $expCol = Schema::hasColumn('expenses', 'expense_date') ? 'expense_date' : 'created_at';
            $operatingExpensesPaid = (float) DB::table('expenses')->whereBetween($expCol, [$start->toDateString(), $end->toDateString()])->sum('amount');
        }

        $netOperatingCash = ($customerCashReceived + $operatingInflows) - ($courierCostPaid + $operatingExpensesPaid);

        // ==========================================
        // 2. CASH FLOWS FROM INVESTING ACTIVITIES
        // ==========================================
        $assetPurchases = 0.00;
        if (Schema::hasTable('assets')) {
            $astCol = Schema::hasColumn('assets', 'purchase_date') ? 'purchase_date' : 'created_at';
            $assetPurchases = (float) DB::table('assets')->whereBetween($astCol, [$start->toDateString(), $end->toDateString()])->sum('purchase_cost');
        }

        $netInvestingCash = -1 * $assetPurchases;

        // ==========================================
        // 3. CASH FLOWS FROM FINANCING ACTIVITIES
        // ==========================================
        $capitalInjections = 0.00;
        if (Schema::hasTable('investments')) {
            $invCol = Schema::hasColumn('investments', 'investment_date') ? 'investment_date' : 'created_at';
            $capitalInjections = (float) DB::table('investments')->whereBetween($invCol, [$start->toDateString(), $end->toDateString()])->sum('amount');
        }

        $netFinancingCash = $capitalInjections;

        // ==========================================
        // 4. SUMMARY & RECONCILIATION
        // ==========================================
        $netChangeInCash = $netOperatingCash + $netInvestingCash + $netFinancingCash;

        $currentTotalBankCash = Schema::hasTable('bank_accounts')
            ? (float) DB::table('bank_accounts')->where('is_active', true)->sum('current_balance')
            : 0.00;

        $openingCashBalance = max(0.00, $currentTotalBankCash - $netChangeInCash);

        $cashFlowSummary = [
            'period_label'            => $start->format('d M Y') . ' — ' . $end->format('d M Y'),
            'customer_cash_received'  => $customerCashReceived,
            'operating_inflows'       => $operatingInflows,
            'courier_cost_paid'       => $courierCostPaid,
            'operating_expenses_paid' => $operatingExpensesPaid,
            'net_operating_cash'      => $netOperatingCash,
            'asset_purchases'         => $assetPurchases,
            'net_investing_cash'      => $netInvestingCash,
            'capital_injections'      => $capitalInjections,
            'net_financing_cash'      => $netFinancingCash,
            'net_change_in_cash'      => $netChangeInCash,
            'opening_balance'         => $openingCashBalance,
            'closing_balance'         => $currentTotalBankCash,
        ];

        return view('admin.reports.cash_flow', compact('cashFlowSummary', 'dateFilter', 'start', 'end'));
    }

    /**
     * 1-Click Statement of Cash Flows CSV Export
     */
    public function exportCashFlow(Request $request)
    {
        [$start, $end] = $this->resolveDateRange($request);
        $filename = "Cash_Flow_Statement_" . $start->format('Y_m_d') . "_to_" . $end->format('Y_m_d') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($start, $end) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['STATEMENT OF CASH FLOWS (AUDIT REPORT)', $start->format('d M Y') . ' — ' . $end->format('d M Y')]);
            fputcsv($file, []);
            fputcsv($file, ['Cash Flow Activity / Ledger Category', 'Flow Type', 'Amount (BDT)']);

            $deliveredOrders = DB::table('orders')
                ->whereBetween('created_at', [$start, $end])
                ->whereIn(DB::raw('LOWER(COALESCE(order_status, ""))'), ['delivered', 'completed'])
                ->get();

            $ordersCash = (float) $deliveredOrders->sum(function ($o) {
                $sub  = (float) ($o->product_subtotal ?? ($o->subtotal ?? ($o->grand_total ?? 0)));
                $disc = (float) ($o->discount ?? ($o->discount_amount ?? 0));
                $ship = (float) ($o->delivery_charge ?? ($o->shipping_charge ?? 0));
                return max(0.00, $sub - $disc) + $ship;
            });

            $incCol = Schema::hasColumn('incomes', 'income_date') ? 'income_date' : 'created_at';
            $incomes = Schema::hasTable('incomes') ? (float) DB::table('incomes')->whereBetween($incCol, [$start->toDateString(), $end->toDateString()])->sum('amount') : 0.00;

            $expCol = Schema::hasColumn('expenses', 'expense_date') ? 'expense_date' : 'created_at';
            $expenses = Schema::hasTable('expenses') ? (float) DB::table('expenses')->whereBetween($expCol, [$start->toDateString(), $end->toDateString()])->sum('amount') : 0.00;

            $courier = Schema::hasTable('courier_bookings') ? (float) DB::table('courier_bookings')->whereBetween('created_at', [$start, $end])->sum('courier_cost') : 0.00;

            $astCol = Schema::hasColumn('assets', 'purchase_date') ? 'purchase_date' : 'created_at';
            $assets = Schema::hasTable('assets') ? (float) DB::table('assets')->whereBetween($astCol, [$start->toDateString(), $end->toDateString()])->sum('purchase_cost') : 0.00;

            $invCol = Schema::hasColumn('investments', 'investment_date') ? 'investment_date' : 'created_at';
            $capital = Schema::hasTable('investments') ? (float) DB::table('investments')->whereBetween($invCol, [$start->toDateString(), $end->toDateString()])->sum('amount') : 0.00;

            $netCFO = ($ordersCash + $incomes) - ($expenses + $courier);
            $netCFI = -1 * $assets;
            $netCFF = $capital;

            fputcsv($file, ['Cash Received from Sales Orders', 'Operating (+)', number_format($ordersCash, 2, '.', '')]);
            fputcsv($file, ['Cash from Other Business Inflows', 'Operating (+)', number_format($incomes, 2, '.', '')]);
            fputcsv($file, ['Operating Overheads & Expenses Paid', 'Operating (-)', number_format($expenses, 2, '.', '')]);
            fputcsv($file, ['Courier Delivery Charges Paid', 'Operating (-)', number_format($courier, 2, '.', '')]);
            fputcsv($file, ['NET CASH FROM OPERATING ACTIVITIES (CFO)', 'Net CFO', number_format($netCFO, 2, '.', '')]);
            fputcsv($file, []);
            fputcsv($file, ['Acquisition of Fixed Assets & Equipment', 'Investing (-)', number_format($assets, 2, '.', '')]);
            fputcsv($file, ['NET CASH FROM INVESTING ACTIVITIES (CFI)', 'Net CFI', number_format($netCFI, 2, '.', '')]);
            fputcsv($file, []);
            fputcsv($file, ['Capital Contributed by Investors', 'Financing (+)', number_format($capital, 2, '.', '')]);
            fputcsv($file, ['NET CASH FROM FINANCING ACTIVITIES (CFF)', 'Net CFF', number_format($netCFF, 2, '.', '')]);
            fputcsv($file, []);
            fputcsv($file, ['NET SURPLUS / DEFICIT CASH FLOW', 'Net Change', number_format($netCFO + $netCFI + $netCFF, 2, '.', '') . ' BDT']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Itemized COGS Analysis per SKU / Product
     */
    public function cogsAnalysis(Request $request)
    {
        $cols = $this->getOrderItemColumnExpressions();

        $query = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn(DB::raw('LOWER(COALESCE(orders.order_status, ""))'), ['delivered', 'completed'])
            ->select(
                'order_items.product_id',
                DB::raw("MAX({$cols['title']}) as product_title"),
                DB::raw("MAX({$cols['sku']}) as sku"),
                DB::raw("SUM({$cols['qty']}) as units_sold"),
                DB::raw("AVG(COALESCE({$cols['cost']}, 0)) as avg_cost"),
                DB::raw("AVG(COALESCE({$cols['price']}, 0)) as avg_price"),
                DB::raw("SUM({$cols['subtotal']}) as total_sales"),
                DB::raw("SUM({$cols['cogs']}) as total_cogs")
            )
            ->groupBy('order_items.product_id');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($sub) use ($s, $cols) {
                $sub->whereRaw("{$cols['title']} LIKE ?", ["%{$s}%"])
                    ->orWhereRaw("{$cols['sku']} LIKE ?", ["%{$s}%"]);
            });
        }

        $items = $query->orderByDesc(DB::raw("SUM({$cols['subtotal']})"))->paginate(25)->withQueryString();

        return view('admin.reports.cogs_analysis', compact('items'));
    }
}