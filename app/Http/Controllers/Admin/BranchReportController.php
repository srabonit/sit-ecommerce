<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\ProductStock;
use App\Models\User;
use App\Services\BranchAccessService;
use App\Services\BranchReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BranchReportController extends Controller
{
    /**
     * Resilient View Resolver across admin.branch_report and admin.report namespaces
     */
    protected function resolveReportView(string $viewName, array $data = [])
    {
        $candidates = [
            "admin.branch_report.{$viewName}",
            "admin.report.{$viewName}",
            "admin.reports.{$viewName}",
            "admin.branch.report.{$viewName}",
        ];

        foreach ($candidates as $view) {
            if (view()->exists($view)) {
                return view($view, $data);
            }
        }

        return view("admin.branch_report.{$viewName}", $data);
    }

    /**
     * ১. ব্রাঞ্চ পারফরম্যান্স ও অ্যানালিটিক্স ড্যাশবোর্ড
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $selectableBranches = BranchAccessService::getSelectableBranches($user);

        $defaultBranchId = BranchAccessService::getCurrentBranchId($user) 
            ?: ($selectableBranches->first()->id ?? null);

        $branchId = (int) $request->get('branch_id', $defaultBranchId);

        if (!$branchId || !BranchAccessService::canViewReports($user, $branchId)) {
            abort(403, 'Unauthorized access to the requested branch financial reports.');
        }

        $branch = Branch::findOrFail($branchId);

        $period    = $request->get('period', 'today');
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        $metrics = BranchReportService::getBranchDashboardMetrics(
            branchId: $branchId,
            period: $period,
            startDate: $startDate,
            endDate: $endDate
        );

        $recentOrders = Order::with(['user', 'salesPerson'])
            ->where('branch_id', $branchId)
            ->latest('id')
            ->take(10)
            ->get();

        // Schema-safe Top 5 Selling Products resolution (Eliminates products.name crash)
        $hasTitle = Schema::hasColumn('products', 'title');
        $hasName  = Schema::hasColumn('products', 'name');
        $hasSku   = Schema::hasColumn('products', 'sku');

        $selectFields = ['products.id'];
        $groupByFields = ['products.id'];

        if ($hasTitle) {
            $selectFields[] = 'products.title';
            $groupByFields[] = 'products.title';
        }

        if ($hasName) {
            $selectFields[] = 'products.name';
            $groupByFields[] = 'products.name';
        } elseif ($hasTitle) {
            $selectFields[] = 'products.title as name';
        }

        if ($hasSku) {
            $selectFields[] = 'products.sku';
            $groupByFields[] = 'products.sku';
        } elseif (Schema::hasColumn('products', 'product_code')) {
            $selectFields[] = 'products.product_code as sku';
            $selectFields[] = 'products.product_code';
            $groupByFields[] = 'products.product_code';
        }

        $selectRawQuery = implode(', ', $selectFields) . ',
            COALESCE(SUM(order_items.quantity), 0) as total_sold_qty,
            COALESCE(SUM(order_items.subtotal), 0) as total_sold_amount';

        $topProductsQuery = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.branch_id', $branchId)
            ->whereBetween('orders.created_at', [$metrics['start_date'], $metrics['end_date']])
            ->selectRaw($selectRawQuery)
            ->groupBy($groupByFields)
            ->orderByDesc('total_sold_qty')
            ->take(5);

        if (Schema::hasColumn('orders', 'order_status')) {
            $topProductsQuery->whereNotIn('orders.order_status', ['cancelled', 'returned']);
        } elseif (Schema::hasColumn('orders', 'status')) {
            $topProductsQuery->whereNotIn('orders.status', ['cancelled', 'returned']);
        }

        $topProducts = $topProductsQuery->get();

        return $this->resolveReportView('dashboard', compact(
            'branch',
            'selectableBranches',
            'metrics',
            'recentOrders',
            'topProducts',
            'period'
        ));
    }

    /**
     * ২. ফিল্টারযোগ্য বিস্তারিত ব্রাঞ্চ সেলস রিপোর্ট
     */
    public function salesReport(Request $request)
    {
        $user = Auth::user();
        $selectableBranches = BranchAccessService::getSelectableBranches($user);

        $defaultBranchId = BranchAccessService::getCurrentBranchId($user) 
            ?: ($selectableBranches->first()->id ?? null);

        $branchId = (int) $request->get('branch_id', $defaultBranchId);

        if (!$branchId || !BranchAccessService::canViewReports($user, $branchId)) {
            abort(403, 'Unauthorized access to the requested branch sales reports.');
        }

        $branch = Branch::findOrFail($branchId);

        $filters = [
            'period'          => $request->get('period', 'this_month'),
            'start_date'      => $request->get('start_date'),
            'end_date'        => $request->get('end_date'),
            'sales_person_id' => $request->get('sales_person_id'),
            'payment_method'  => $request->get('payment_method'),
            'order_status'    => $request->get('order_status'),
            'order_type'      => $request->get('order_type'),
            'search'          => $request->get('search'),
        ];

        $reportData = BranchReportService::getBranchSalesReport(
            branchId: $branchId,
            filters: $filters,
            perPage: (int) $request->get('per_page', 25)
        );

        $salesPersonsQuery = User::query();
        if (Schema::hasColumn('users', 'is_active')) {
            $salesPersonsQuery->where('is_active', true);
        } elseif (Schema::hasColumn('users', 'status')) {
            $salesPersonsQuery->where('status', 'active');
        }

        $salesPersons = $salesPersonsQuery
            ->where(function ($q) use ($branchId) {
                if (Schema::hasColumn('users', 'is_super_admin')) {
                    $q->where('is_super_admin', true);
                }
                if (Schema::hasColumn('users', 'primary_branch_id')) {
                    $q->orWhere('primary_branch_id', $branchId);
                }
                if (Schema::hasColumn('users', 'branch_id')) {
                    $q->orWhere('branch_id', $branchId);
                }
                if (Schema::hasTable('branch_user')) {
                    $q->orWhereHas('assignedBranches', function ($bq) use ($branchId) {
                        $bq->where('branches.id', $branchId);
                    });
                }
            })
            ->orderBy('name', 'asc')
            ->get();

        if ($request->ajax()) {
            if (view()->exists('admin.branch_report.partials.sales_report_rows')) {
                return view('admin.branch_report.partials.sales_report_rows', array_merge($reportData, compact('branch')))->render();
            }
            if (view()->exists('admin.report.partials.sales_report_rows')) {
                return view('admin.report.partials.sales_report_rows', array_merge($reportData, compact('branch')))->render();
            }
        }

        return $this->resolveReportView('sales', array_merge($reportData, compact(
            'branch',
            'selectableBranches',
            'salesPersons',
            'filters'
        )));
    }

    /**
     * ৩. সেলস পারসন পারফরম্যান্স ও কমিশন রিপোর্ট
     */
    public function salesPersonReport(Request $request)
    {
        $user = Auth::user();
        $selectableBranches = BranchAccessService::getSelectableBranches($user);

        $defaultBranchId = BranchAccessService::getCurrentBranchId($user) 
            ?: ($selectableBranches->first()->id ?? null);

        $branchId = (int) $request->get('branch_id', $defaultBranchId);

        if (!$branchId || !BranchAccessService::canViewReports($user, $branchId)) {
            abort(403, 'Unauthorized access.');
        }

        $branch = Branch::findOrFail($branchId);

        $filters = [
            'period'     => $request->get('period', 'this_month'),
            'start_date' => $request->get('start_date'),
            'end_date'   => $request->get('end_date'),
        ];

        $performance = BranchReportService::getSalesPersonPerformance($branchId, $filters);

        return $this->resolveReportView('sales_person', compact(
            'branch',
            'selectableBranches',
            'performance',
            'filters'
        ));
    }

    /**
     * ৪. সেন্ট্রাল ব্রাঞ্চ কম্প্যারিজন রিপোর্ট
     */
    public function comparisonReport(Request $request)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && strtolower(trim((string)$user->role)) !== 'admin' && empty($user->has_all_branch_access)) {
            abort(403, 'Access Restricted: Master comparison report requires central administration privileges.');
        }

        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->get('end_date', now()->endOfMonth()->toDateString());

        $comparisonData = BranchReportService::getMasterBranchComparisonReport($startDate, $endDate);

        $networkTotals = [
            'total_sales'     => $comparisonData->sum('total_sales'),
            'total_orders'    => $comparisonData->sum('total_orders'),
            'total_paid'      => $comparisonData->sum('total_paid'),
            'total_due'       => $comparisonData->sum('total_due'),
            'gross_profit'    => $comparisonData->sum('gross_profit'),
            'net_profit'      => $comparisonData->sum('net_profit'),
            'inventory_value' => $comparisonData->sum('inventory_value'),
        ];

        return $this->resolveReportView('comparison', compact(
            'comparisonData',
            'networkTotals',
            'startDate',
            'endDate'
        ));
    }

    /**
     * ৫. ব্রাঞ্চ ইনভেন্টরি ও স্টক অ্যাসেট ভ্যালু রিপোর্ট (100% Schema-Safe — No products.price)
     */
    public function inventoryReport(Request $request)
    {
        $user = Auth::user();
        $selectableBranches = BranchAccessService::getSelectableBranches($user);

        $defaultBranchId = BranchAccessService::getCurrentBranchId($user) 
            ?: ($selectableBranches->first()->id ?? null);

        $branchId = (int) $request->get('branch_id', $defaultBranchId);

        if (!$branchId || !BranchAccessService::canViewReports($user, $branchId)) {
            abort(403, 'Unauthorized access.');
        }

        $branch = Branch::findOrFail($branchId);

        $query = ProductStock::with(['product.category', 'variant'])
            ->where('branch_id', $branchId);

        if ($request->boolean('low_stock_only')) {
            $hasReserved = Schema::hasColumn('product_stocks', 'reserved_quantity');
            $hasAlert = Schema::hasColumn('product_stocks', 'alert_quantity');
            $reservedSql = $hasReserved ? 'COALESCE(`reserved_quantity`, 0)' : '0';
            $alertSql = $hasAlert ? 'COALESCE(`alert_quantity`, 5)' : '5';
            $query->whereRaw("(`quantity` - {$reservedSql}) <= {$alertSql}");
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                if (Schema::hasColumn('product_stocks', 'sku')) {
                    $q->where('sku', 'like', "%{$s}%");
                }
                $q->orWhereHas('product', function ($pq) use ($s) {
                    $pq->where(function ($subQ) use ($s) {
                        if (Schema::hasColumn('products', 'title')) {
                            $subQ->where('title', 'like', "%{$s}%");
                        }
                        if (Schema::hasColumn('products', 'name')) {
                            $subQ->orWhere('name', 'like', "%{$s}%");
                        }
                        if (Schema::hasColumn('products', 'sku')) {
                            $subQ->orWhere('sku', 'like', "%{$s}%");
                        }
                        if (Schema::hasColumn('products', 'product_code')) {
                            $subQ->orWhere('product_code', 'like', "%{$s}%");
                        }
                    });
                });
            });
        }

        $stocks = $query->orderBy('quantity', 'asc')->paginate(30)->withQueryString();

        // 100% Schema-Safe Valuation SQL (products.price removed completely)
        $hasReserved = Schema::hasColumn('product_stocks', 'reserved_quantity');
        $reservedSql = $hasReserved ? 'COALESCE(SUM(product_stocks.reserved_quantity), 0)' : '0';

        $variantCostCol = Schema::hasColumn('product_variants', 'purchase_cost') 
            ? 'product_variants.purchase_cost' 
            : (Schema::hasColumn('product_variants', 'purchase_price') ? 'product_variants.purchase_price' : 'NULL');

        $productCostCol = Schema::hasColumn('products', 'purchase_cost') 
            ? 'products.purchase_cost' 
            : (Schema::hasColumn('products', 'purchase_price') ? 'products.purchase_price' : '0');

        $variantPriceCol = Schema::hasColumn('product_variants', 'regular_price') 
            ? 'product_variants.regular_price' 
            : (Schema::hasColumn('product_variants', 'sale_price') ? 'product_variants.sale_price' : 'NULL');

        $productPriceCol = Schema::hasColumn('products', 'regular_price') 
            ? 'products.regular_price' 
            : (Schema::hasColumn('products', 'sale_price') ? 'products.sale_price' : '0');

        $summary = DB::table('product_stocks')
            ->leftJoin('products', 'product_stocks.product_id', '=', 'products.id')
            ->leftJoin('product_variants', 'product_stocks.variant_id', '=', 'product_variants.id')
            ->where('product_stocks.branch_id', $branchId)
            ->selectRaw("
                COUNT(product_stocks.id) as total_sku_count,
                COALESCE(SUM(product_stocks.quantity), 0) as total_physical_units,
                {$reservedSql} as total_reserved_units,
                COALESCE(SUM(
                    product_stocks.quantity * COALESCE({$variantCostCol}, {$productCostCol}, 0)
                ), 0) as total_cost_value,
                COALESCE(SUM(
                    product_stocks.quantity * COALESCE({$variantPriceCol}, {$productPriceCol}, 0)
                ), 0) as total_retail_value
            ")
            ->first();

        return $this->resolveReportView('inventory', compact(
            'branch',
            'selectableBranches',
            'stocks',
            'summary'
        ));
    }

    /**
     * ৬. ফিল্টারকৃত সেলস ডাটা সিএসভি (CSV) ফাইল হিসেবে ডাউনলোড
     */
    public function exportSalesCsv(Request $request): StreamedResponse
    {
        $user = Auth::user();
        $branchId = (int) $request->get('branch_id');

        if (!$branchId || !BranchAccessService::canViewReports($user, $branchId)) {
            abort(403, 'Unauthorized export access.');
        }

        $branch = Branch::findOrFail($branchId);

        $filters = [
            'period'          => $request->get('period', 'this_month'),
            'start_date'      => $request->get('start_date'),
            'end_date'        => $request->get('end_date'),
            'sales_person_id' => $request->get('sales_person_id'),
            'payment_method'  => $request->get('payment_method'),
            'order_status'    => $request->get('order_status'),
            'order_type'      => $request->get('order_type'),
            'search'          => $request->get('search'),
        ];

        $reportData = BranchReportService::getBranchSalesReport($branchId, $filters, 5000);
        $orders = $reportData['orders'];

        $fileName = 'sales_report_' . Str::slug($branch->name) . '_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Invoice No',
                'Date',
                'Channel',
                'Customer Name',
                'Customer Phone',
                'Sales Representative',
                'Subtotal (৳)',
                'Discount (৳)',
                'Tax (৳)',
                'Grand Total (৳)',
                'Paid (৳)',
                'Due (৳)',
                'Payment Method',
                'Status'
            ]);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->invoice_no,
                    $order->created_at->format('Y-m-d h:i A'),
                    strtoupper($order->order_type ?? 'ONLINE'),
                    $order->user?->name ?? 'Walk-in Customer',
                    $order->user?->phone ?? 'N/A',
                    $order->salesPerson?->name ?? 'Direct Sale',
                    number_format($order->subtotal, 2, '.', ''),
                    number_format($order->discount_amount, 2, '.', ''),
                    number_format($order->tax_amount, 2, '.', ''),
                    number_format($order->grand_total, 2, '.', ''),
                    number_format($order->paid_amount, 2, '.', ''),
                    number_format($order->due_amount, 2, '.', ''),
                    strtoupper($order->payment_method),
                    ucfirst($order->order_status),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}