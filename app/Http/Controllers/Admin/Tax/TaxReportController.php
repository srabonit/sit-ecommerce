<?php

namespace App\Http\Controllers\Admin\Tax;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TaxPayment;
use App\Models\TaxAdjustment;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Brand;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaxReportController extends Controller
{
    /**
     * Resolve the items table name safely (Supports order_items & order_details).
     */
    protected function resolveItemsTable(): string
    {
        if (Schema::hasTable('order_items')) {
            return 'order_items';
        }
        if (Schema::hasTable('order_details')) {
            return 'order_details';
        }
        return 'order_items';
    }

    /**
     * Display the Central Tax & VAT Reports Management Interface.
     */
    public function index(Request $request)
    {
        $reportType = $request->get('report_type', 'all_transactions');
        $dateFilter = $request->get('date_filter', 'this_month');
        $dates = $this->resolveDateRange($dateFilter, $request);
        $startDate = $dates['start'];
        $endDate = $dates['end'];

        $perPage = max(10, min(200, (int)$request->get('per_page', 20)));

        // Report data and summary
        $reportData = $this->getReportData($reportType, $startDate, $endDate, $request, $perPage);
        $records = $reportData['records'];
        $summary = $reportData['summary'];

        // Dropdown filtering data
        $branches = (Schema::hasTable('branches') && class_exists(Branch::class)) 
            ? Branch::where('is_active', true)->orderBy('name')->get() 
            : collect();
        $categories = (Schema::hasTable('categories') && class_exists(Category::class)) 
            ? Category::orderBy('name')->get() 
            : collect();
        $brands = (Schema::hasTable('brands') && class_exists(Brand::class)) 
            ? Brand::orderBy('name')->get() 
            : collect();

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'success'   => true,
                'html'      => view('admin.tax.reports.partials.table_rows', compact('records', 'reportType'))->render(),
                'summary'   => $summary,
                'pagination'=> (string)$records->links(),
            ]);
        }

        return view('admin.tax.reports.index', compact(
            'records',
            'summary',
            'reportType',
            'dateFilter',
            'startDate',
            'endDate',
            'perPage',
            'branches',
            'categories',
            'brands'
        ));
    }

    /**
     * Display Tax Audit Logs (Adjustment, Refund & Compliance History)
     */
    public function auditLogs(Request $request)
    {
        $request->merge(['report_type' => 'adjustment_history']);
        return $this->index($request);
    }

    /**
     * Stream memory-safe CSV Export for Large Filtered Tax Datasets.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $reportType = $request->get('report_type', 'all_transactions');
        $dateFilter = $request->get('date_filter', 'this_month');
        $dates = $this->resolveDateRange($dateFilter, $request);
        $startDate = $dates['start'];
        $endDate = $dates['end'];

        $fileName = 'Tax_Report_' . $reportType . '_' . Carbon::now()->format('Ymd_His') . '.csv';

        $reportData = $this->getReportData($reportType, $startDate, $endDate, $request, null);
        $records = $reportData['records'];

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($reportType, $records) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            $columns = $this->getCsvColumns($reportType);
            fputcsv($handle, $columns);

            foreach ($records as $row) {
                fputcsv($handle, $this->formatCsvRow($reportType, $row));
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Print-friendly tax report statement layout.
     */
    public function printReport(Request $request)
    {
        $reportType = $request->get('report_type', 'all_transactions');
        $dateFilter = $request->get('date_filter', 'this_month');
        $dates = $this->resolveDateRange($dateFilter, $request);
        $startDate = $dates['start'];
        $endDate = $dates['end'];

        $reportData = $this->getReportData($reportType, $startDate, $endDate, $request, null);
        $records = $reportData['records'];
        $summary = $reportData['summary'];

        return view('admin.tax.reports.print', compact(
            'records',
            'summary',
            'reportType',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get Report Data and Global Filtered Summaries based on Report Type.
     */
    protected function getReportData(string $reportType, string $startDate, string $endDate, Request $request, ?int $perPage = 20): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        return match ($reportType) {
            'product_wise'        => $this->buildProductWiseReport($start, $end, $request, $perPage),
            'variant_wise'        => $this->buildVariantWiseReport($start, $end, $request, $perPage),
            'category_wise'       => $this->buildCategoryWiseReport($start, $end, $request, $perPage),
            'brand_wise'          => $this->buildBrandWiseReport($start, $end, $request, $perPage),
            'branch_wise'         => $this->buildBranchWiseReport($start, $end, $request, $perPage),
            'pos_wise'            => $this->buildPosWiseReport($start, $end, $request, $perPage),
            'order_wise'          => $this->buildOrderWiseReport($start, $end, $request, $perPage),
            'customer_wise'       => $this->buildCustomerWiseReport($start, $end, $request, $perPage),
            'shipping_tax'        => $this->buildShippingTaxReport($start, $end, $request, $perPage),
            'collection_history'  => $this->buildCollectionHistoryReport($start, $end, $request, $perPage),
            'payment_history'     => $this->buildPaymentHistoryReport($start, $end, $request, $perPage),
            'refund_history'      => $this->buildRefundHistoryReport($start, $end, $request, $perPage),
            'adjustment_history'  => $this->buildAdjustmentHistoryReport($start, $end, $request, $perPage),
            default               => $this->buildAllTransactionsReport($start, $end, $request, $perPage),
        };
    }

    /**
     * Column Safekeeping Resolvers for Orders Table
     */
    protected function resolveIsPosExpr()
    {
        $hasIsPos = Schema::hasColumn('orders', 'is_pos');
        $hasPosOrder = Schema::hasColumn('orders', 'pos_order');
        $hasOrderSource = Schema::hasColumn('orders', 'order_source');
        $hasOrderType = Schema::hasColumn('orders', 'order_type');
        $hasBranchId = Schema::hasColumn('orders', 'branch_id');

        $clauses = [];
        if ($hasIsPos) $clauses[] = "orders.is_pos = 1";
        if ($hasPosOrder) $clauses[] = "orders.pos_order = 1";
        if ($hasOrderSource) $clauses[] = "LOWER(orders.order_source) = 'pos'";
        if ($hasOrderType) $clauses[] = "LOWER(orders.order_type) = 'pos'";
        if (empty($clauses) && $hasBranchId) $clauses[] = "orders.branch_id IS NOT NULL";

        if (!empty($clauses)) {
            return DB::raw("(CASE WHEN (" . implode(' OR ', $clauses) . ") THEN 1 ELSE 0 END) as is_pos");
        }
        return DB::raw("0 as is_pos");
    }

    protected function resolveCustomerNameCol()
    {
        if (Schema::hasColumn('orders', 'customer_name')) {
            return 'orders.customer_name';
        }
        if (Schema::hasColumn('orders', 'shipping_name')) {
            return 'orders.shipping_name as customer_name';
        }
        if (Schema::hasColumn('orders', 'name')) {
            return 'orders.name as customer_name';
        }
        return DB::raw("'Guest' as customer_name");
    }

    protected function resolveCustomerPhoneCol()
    {
        if (Schema::hasColumn('orders', 'customer_phone')) {
            return 'orders.customer_phone';
        }
        if (Schema::hasColumn('orders', 'shipping_phone')) {
            return 'orders.shipping_phone as customer_phone';
        }
        if (Schema::hasColumn('orders', 'phone')) {
            return 'orders.phone as customer_phone';
        }
        return DB::raw("NULL as customer_phone");
    }

    protected function resolveSubtotalCol()
    {
        if (Schema::hasColumn('orders', 'taxable_base_amount')) {
            return DB::raw("COALESCE(NULLIF(orders.taxable_base_amount, 0), orders.subtotal, orders.grand_total, 0) as taxable_amount");
        }
        if (Schema::hasColumn('orders', 'subtotal')) {
            return DB::raw("COALESCE(orders.subtotal, orders.grand_total, 0) as taxable_amount");
        }
        if (Schema::hasColumn('orders', 'product_subtotal')) {
            return DB::raw("COALESCE(orders.product_subtotal, orders.grand_total, 0) as taxable_amount");
        }
        return 'orders.grand_total as taxable_amount';
    }

    protected function resolveTaxAmountCol()
    {
        return DB::raw("CASE 
            WHEN COALESCE(orders.tax_amount, 0) > 0 THEN orders.tax_amount
            WHEN COALESCE(orders.product_tax_amount, 0) > 0 THEN orders.product_tax_amount
            ELSE 0.00 
        END as tax_amount");
    }

    protected function resolveProductTaxCol()
    {
        if (Schema::hasColumn('orders', 'product_tax_amount')) {
            return DB::raw("COALESCE(NULLIF(orders.product_tax_amount, 0), orders.tax_amount, 0.00) as product_tax_amount");
        }
        if (Schema::hasColumn('orders', 'tax_amount')) {
            return 'orders.tax_amount as product_tax_amount';
        }
        return DB::raw("0.00 as product_tax_amount");
    }

    protected function resolveShippingTaxCol()
    {
        return Schema::hasColumn('orders', 'shipping_tax_amount')
            ? DB::raw("COALESCE(orders.shipping_tax_amount, 0.00) as shipping_tax_amount")
            : DB::raw("0.00 as shipping_tax_amount");
    }

    protected function resolveBranchIdCol()
    {
        return Schema::hasColumn('orders', 'branch_id')
            ? 'orders.branch_id'
            : DB::raw("NULL as branch_id");
    }

    /**
     * Report: All Tax Transactions.
     */
    protected function buildAllTransactionsReport(Carbon $start, Carbon $end, Request $request, ?int $perPage): array
    {
        $excludedStatuses = ['cancelled', 'cancel', 'failed', 'incomplete', 'returned', 'return'];

        $query = DB::table('orders')
            ->whereBetween('orders.created_at', [$start, $end]);

        if (Schema::hasColumn('orders', 'order_status')) {
            $query->where(function ($q) use ($excludedStatuses) {
                $q->whereNotIn(DB::raw('LOWER(COALESCE(orders.order_status, ""))'), $excludedStatuses)
                  ->orWhereNull('orders.order_status');
            });
        }

        if ($request->filled('branch_id') && Schema::hasColumn('orders', 'branch_id')) {
            $query->where('orders.branch_id', $request->branch_id);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                if (Schema::hasColumn('orders', 'invoice_no')) {
                    $q->where('orders.invoice_no', 'like', "%{$s}%");
                }
                foreach (['customer_name', 'shipping_name', 'name'] as $nameCol) {
                    if (Schema::hasColumn('orders', $nameCol)) {
                        $q->orWhere("orders.{$nameCol}", 'like', "%{$s}%");
                    }
                }
                foreach (['customer_phone', 'shipping_phone', 'phone'] as $phoneCol) {
                    if (Schema::hasColumn('orders', $phoneCol)) {
                        $q->orWhere("orders.{$phoneCol}", 'like', "%{$s}%");
                    }
                }
            });
        }

        $subtotalField = Schema::hasColumn('orders', 'taxable_base_amount') 
            ? "COALESCE(NULLIF(orders.taxable_base_amount, 0), orders.subtotal, orders.grand_total, 0)" 
            : (Schema::hasColumn('orders', 'subtotal') ? "COALESCE(orders.subtotal, orders.grand_total, 0)" : "orders.grand_total");

        $taxColExpr = "CASE 
            WHEN COALESCE(orders.tax_amount, 0) > 0 THEN orders.tax_amount
            WHEN COALESCE(orders.product_tax_amount, 0) > 0 THEN orders.product_tax_amount
            ELSE 0 
        END";

        $summary = [
            'total_records'        => (clone $query)->count(),
            'total_taxable_amount' => (float)(clone $query)->sum(DB::raw($subtotalField)),
            'total_tax_amount'     => (float)(clone $query)->sum(DB::raw($taxColExpr)),
            'product_tax_amount'   => Schema::hasColumn('orders', 'product_tax_amount') ? (float)(clone $query)->sum('orders.product_tax_amount') : 0.00,
            'shipping_tax_amount'  => Schema::hasColumn('orders', 'shipping_tax_amount') ? (float)(clone $query)->sum('orders.shipping_tax_amount') : 0.00,
        ];

        $selectFields = [
            'orders.id',
            Schema::hasColumn('orders', 'invoice_no') ? 'orders.invoice_no' : DB::raw('orders.id as invoice_no'),
            'orders.created_at',
            $this->resolveCustomerNameCol(),
            $this->resolveCustomerPhoneCol(),
            $this->resolveSubtotalCol(),
            $this->resolveTaxAmountCol(),
            $this->resolveProductTaxCol(),
            $this->resolveShippingTaxCol(),
            'orders.grand_total',
            $this->resolveIsPosExpr(),
            $this->resolveBranchIdCol(),
        ];

        $query->select($selectFields)->latest('orders.created_at');

        $records = $perPage ? $query->paginate($perPage)->withQueryString() : $query->get();

        return ['records' => $records, 'summary' => $summary];
    }

    /**
     * Report: Product-wise Tax.
     */
    protected function buildProductWiseReport(Carbon $start, Carbon $end, Request $request, ?int $perPage): array
    {
        $itemsTable = $this->resolveItemsTable();
        $excludedStatuses = ['cancelled', 'cancel', 'failed', 'incomplete', 'returned', 'return'];

        $query = DB::table($itemsTable)
            ->join('orders', "{$itemsTable}.order_id", '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start, $end]);

        if (Schema::hasColumn('orders', 'order_status')) {
            $query->where(function ($q) use ($excludedStatuses) {
                $q->whereNotIn(DB::raw('LOWER(COALESCE(orders.order_status, ""))'), $excludedStatuses)
                  ->orWhereNull('orders.order_status');
            });
        }

        if ($request->filled('branch_id') && Schema::hasColumn('orders', 'branch_id')) {
            $query->where('orders.branch_id', $request->branch_id);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s, $itemsTable) {
                if (Schema::hasColumn($itemsTable, 'product_title')) {
                    $q->where("{$itemsTable}.product_title", 'like', "%{$s}%");
                } elseif (Schema::hasColumn($itemsTable, 'product_name')) {
                    $q->where("{$itemsTable}.product_name", 'like', "%{$s}%");
                }
                $q->orWhere("{$itemsTable}.product_id", $s);
            });
        }

        $hasQty = Schema::hasColumn($itemsTable, 'quantity') ? "{$itemsTable}.quantity" : (Schema::hasColumn($itemsTable, 'qty') ? "{$itemsTable}.qty" : '1');
        $hasSubtotal = Schema::hasColumn($itemsTable, 'subtotal');
        $qtyPriceExpr = $hasSubtotal ? "{$itemsTable}.subtotal" : "({$itemsTable}.unit_price * {$hasQty})";

        $taxableExpr = Schema::hasColumn($itemsTable, 'taxable_base_amount') 
            ? "COALESCE(NULLIF({$itemsTable}.taxable_base_amount, 0), {$qtyPriceExpr}, 0)" 
            : "COALESCE({$qtyPriceExpr}, 0)";

        $taxExpr = "CASE 
            WHEN COALESCE({$itemsTable}.tax_amount, 0) > 0 THEN {$itemsTable}.tax_amount
            WHEN COALESCE({$itemsTable}.tax_rate, 0) > 0 THEN ROUND(({$qtyPriceExpr} * {$itemsTable}.tax_rate) / 100, 2)
            ELSE 0.00 
        END";

        $summary = [
            'total_records'        => (clone $query)->distinct("{$itemsTable}.product_id")->count("{$itemsTable}.product_id"),
            'total_taxable_amount' => (float)(clone $query)->sum(DB::raw($taxableExpr)),
            'total_tax_amount'     => (float)(clone $query)->sum(DB::raw($taxExpr)),
        ];

        $titleCol = Schema::hasColumn($itemsTable, 'product_title') 
            ? "{$itemsTable}.product_title" 
            : (Schema::hasColumn($itemsTable, 'product_name') ? "{$itemsTable}.product_name" : DB::raw("'Product'"));

        $taxRateCol = Schema::hasColumn($itemsTable, 'tax_rate') ? "COALESCE({$itemsTable}.tax_rate, 0)" : '0';

        $query->select(
            "{$itemsTable}.product_id",
            DB::raw("MAX(COALESCE({$titleCol}, 'Product')) as title"),
            DB::raw("SUM({$hasQty}) as total_qty"),
            DB::raw("SUM({$taxableExpr}) as taxable_amount"),
            DB::raw("SUM({$taxExpr}) as total_tax"),
            DB::raw("AVG({$taxRateCol}) as avg_rate")
        )->groupBy("{$itemsTable}.product_id")
         ->orderByDesc('total_tax');

        $records = $perPage ? $query->paginate($perPage)->withQueryString() : $query->get();

        return ['records' => $records, 'summary' => $summary];
    }

    /**
     * Report: Variant-wise Tax.
     */
    protected function buildVariantWiseReport(Carbon $start, Carbon $end, Request $request, ?int $perPage): array
    {
        $itemsTable = $this->resolveItemsTable();
        if (!Schema::hasColumn($itemsTable, 'variant_id')) {
            $empty = $perPage ? new LengthAwarePaginator([], 0, $perPage) : collect();
            return ['records' => $empty, 'summary' => ['total_records' => 0, 'total_taxable_amount' => 0, 'total_tax_amount' => 0]];
        }

        $excludedStatuses = ['cancelled', 'cancel', 'failed', 'incomplete', 'returned', 'return'];

        $query = DB::table($itemsTable)
            ->join('orders', "{$itemsTable}.order_id", '=', 'orders.id')
            ->whereNotNull("{$itemsTable}.variant_id")
            ->whereBetween('orders.created_at', [$start, $end]);

        if (Schema::hasColumn('orders', 'order_status')) {
            $query->where(function ($q) use ($excludedStatuses) {
                $q->whereNotIn(DB::raw('LOWER(COALESCE(orders.order_status, ""))'), $excludedStatuses)
                  ->orWhereNull('orders.order_status');
            });
        }

        $hasQty = Schema::hasColumn($itemsTable, 'quantity') ? "{$itemsTable}.quantity" : (Schema::hasColumn($itemsTable, 'qty') ? "{$itemsTable}.qty" : '1');
        $hasSubtotal = Schema::hasColumn($itemsTable, 'subtotal');
        $qtyPriceExpr = $hasSubtotal ? "{$itemsTable}.subtotal" : "({$itemsTable}.unit_price * {$hasQty})";

        $taxableExpr = Schema::hasColumn($itemsTable, 'taxable_base_amount') 
            ? "COALESCE(NULLIF({$itemsTable}.taxable_base_amount, 0), {$qtyPriceExpr}, 0)" 
            : "COALESCE({$qtyPriceExpr}, 0)";

        $taxExpr = "CASE 
            WHEN COALESCE({$itemsTable}.tax_amount, 0) > 0 THEN {$itemsTable}.tax_amount
            WHEN COALESCE({$itemsTable}.tax_rate, 0) > 0 THEN ROUND(({$qtyPriceExpr} * {$itemsTable}.tax_rate) / 100, 2)
            ELSE 0.00 
        END";

        $summary = [
            'total_records'        => (clone $query)->distinct("{$itemsTable}.variant_id")->count("{$itemsTable}.variant_id"),
            'total_taxable_amount' => (float)(clone $query)->sum(DB::raw($taxableExpr)),
            'total_tax_amount'     => (float)(clone $query)->sum(DB::raw($taxExpr)),
        ];

        $titleCol = Schema::hasColumn($itemsTable, 'variant_title')
            ? "COALESCE({$itemsTable}.variant_title, {$itemsTable}.product_title, 'Variant')"
            : (Schema::hasColumn($itemsTable, 'product_title') ? "{$itemsTable}.product_title" : "'Variant'");

        $taxRateCol = Schema::hasColumn($itemsTable, 'tax_rate') ? "COALESCE({$itemsTable}.tax_rate, 0)" : '0';

        $query->select(
            "{$itemsTable}.variant_id",
            "{$itemsTable}.product_id",
            DB::raw("MAX({$titleCol}) as title"),
            DB::raw("SUM({$hasQty}) as total_qty"),
            DB::raw("SUM({$taxableExpr}) as taxable_amount"),
            DB::raw("SUM({$taxExpr}) as total_tax"),
            DB::raw("AVG({$taxRateCol}) as avg_rate")
        )->groupBy("{$itemsTable}.variant_id", "{$itemsTable}.product_id")
         ->orderByDesc('total_tax');

        $records = $perPage ? $query->paginate($perPage)->withQueryString() : $query->get();

        return ['records' => $records, 'summary' => $summary];
    }

    /**
     * Report: Category-wise Tax (ONLY_FULL_GROUP_BY Safe).
     */
    protected function buildCategoryWiseReport(Carbon $start, Carbon $end, Request $request, ?int $perPage): array
    {
        $itemsTable = $this->resolveItemsTable();
        $excludedStatuses = ['cancelled', 'cancel', 'failed', 'incomplete', 'returned', 'return'];

        $query = DB::table($itemsTable)
            ->join('orders', "{$itemsTable}.order_id", '=', 'orders.id')
            ->leftJoin('products', "{$itemsTable}.product_id", '=', 'products.id');

        if (Schema::hasTable('categories')) {
            $query->leftJoin('categories', 'products.category_id', '=', 'categories.id');
        }

        $query->whereBetween('orders.created_at', [$start, $end]);

        if (Schema::hasColumn('orders', 'order_status')) {
            $query->where(function ($q) use ($excludedStatuses) {
                $q->whereNotIn(DB::raw('LOWER(COALESCE(orders.order_status, ""))'), $excludedStatuses)
                  ->orWhereNull('orders.order_status');
            });
        }

        if ($request->filled('category_id') && Schema::hasColumn('products', 'category_id')) {
            $query->where('products.category_id', $request->category_id);
        }

        $hasQty = Schema::hasColumn($itemsTable, 'quantity') ? "{$itemsTable}.quantity" : (Schema::hasColumn($itemsTable, 'qty') ? "{$itemsTable}.qty" : '1');
        $hasSubtotal = Schema::hasColumn($itemsTable, 'subtotal');
        $qtyPriceExpr = $hasSubtotal ? "{$itemsTable}.subtotal" : "({$itemsTable}.unit_price * {$hasQty})";

        $taxableExpr = Schema::hasColumn($itemsTable, 'taxable_base_amount') 
            ? "COALESCE(NULLIF({$itemsTable}.taxable_base_amount, 0), {$qtyPriceExpr}, 0)" 
            : "COALESCE({$qtyPriceExpr}, 0)";

        $taxExpr = "CASE 
            WHEN COALESCE({$itemsTable}.tax_amount, 0) > 0 THEN {$itemsTable}.tax_amount
            WHEN COALESCE({$itemsTable}.tax_rate, 0) > 0 THEN ROUND(({$qtyPriceExpr} * {$itemsTable}.tax_rate) / 100, 2)
            ELSE 0.00 
        END";

        $catIdCol = Schema::hasColumn('products', 'category_id') ? 'products.category_id' : "{$itemsTable}.product_id";

        $summary = [
            'total_records'        => (clone $query)->distinct($catIdCol)->count($catIdCol),
            'total_taxable_amount' => (float)(clone $query)->sum(DB::raw($taxableExpr)),
            'total_tax_amount'     => (float)(clone $query)->sum(DB::raw($taxExpr)),
        ];

        $catNameExpr = Schema::hasTable('categories') 
            ? "COALESCE(MAX(categories.name), 'Uncategorized')" 
            : "'Uncategorized'";

        $query->select(
            DB::raw("COALESCE({$catIdCol}, 0) as category_id"),
            DB::raw("{$catNameExpr} as category_name"),
            DB::raw("SUM({$hasQty}) as total_qty"),
            DB::raw("SUM({$taxableExpr}) as taxable_amount"),
            DB::raw("SUM({$taxExpr}) as total_tax")
        )->groupBy(DB::raw("COALESCE({$catIdCol}, 0)"))
         ->orderByDesc('total_tax');

        $records = $perPage ? $query->paginate($perPage)->withQueryString() : $query->get();

        return ['records' => $records, 'summary' => $summary];
    }

    /**
     * Report: Brand-wise Tax (ONLY_FULL_GROUP_BY Safe).
     */
    protected function buildBrandWiseReport(Carbon $start, Carbon $end, Request $request, ?int $perPage): array
    {
        $itemsTable = $this->resolveItemsTable();
        $excludedStatuses = ['cancelled', 'cancel', 'failed', 'incomplete', 'returned', 'return'];

        $query = DB::table($itemsTable)
            ->join('orders', "{$itemsTable}.order_id", '=', 'orders.id')
            ->leftJoin('products', "{$itemsTable}.product_id", '=', 'products.id');

        if (Schema::hasTable('brands')) {
            $query->leftJoin('brands', 'products.brand_id', '=', 'brands.id');
        }

        $query->whereBetween('orders.created_at', [$start, $end]);

        if (Schema::hasColumn('orders', 'order_status')) {
            $query->where(function ($q) use ($excludedStatuses) {
                $q->whereNotIn(DB::raw('LOWER(COALESCE(orders.order_status, ""))'), $excludedStatuses)
                  ->orWhereNull('orders.order_status');
            });
        }

        if ($request->filled('brand_id') && Schema::hasColumn('products', 'brand_id')) {
            $query->where('products.brand_id', $request->brand_id);
        }

        $hasQty = Schema::hasColumn($itemsTable, 'quantity') ? "{$itemsTable}.quantity" : (Schema::hasColumn($itemsTable, 'qty') ? "{$itemsTable}.qty" : '1');
        $hasSubtotal = Schema::hasColumn($itemsTable, 'subtotal');
        $qtyPriceExpr = $hasSubtotal ? "{$itemsTable}.subtotal" : "({$itemsTable}.unit_price * {$hasQty})";

        $taxableExpr = Schema::hasColumn($itemsTable, 'taxable_base_amount') 
            ? "COALESCE(NULLIF({$itemsTable}.taxable_base_amount, 0), {$qtyPriceExpr}, 0)" 
            : "COALESCE({$qtyPriceExpr}, 0)";

        $taxExpr = "CASE 
            WHEN COALESCE({$itemsTable}.tax_amount, 0) > 0 THEN {$itemsTable}.tax_amount
            WHEN COALESCE({$itemsTable}.tax_rate, 0) > 0 THEN ROUND(({$qtyPriceExpr} * {$itemsTable}.tax_rate) / 100, 2)
            ELSE 0.00 
        END";

        $brandIdCol = Schema::hasColumn('products', 'brand_id') ? 'products.brand_id' : "{$itemsTable}.product_id";

        $summary = [
            'total_records'        => (clone $query)->distinct($brandIdCol)->count($brandIdCol),
            'total_taxable_amount' => (float)(clone $query)->sum(DB::raw($taxableExpr)),
            'total_tax_amount'     => (float)(clone $query)->sum(DB::raw($taxExpr)),
        ];

        $brandNameExpr = Schema::hasTable('brands') ? "COALESCE(MAX(brands.name), 'No Brand')" : "'No Brand'";

        $query->select(
            DB::raw("COALESCE({$brandIdCol}, 0) as brand_id"),
            DB::raw("{$brandNameExpr} as brand_name"),
            DB::raw("SUM({$hasQty}) as total_qty"),
            DB::raw("SUM({$taxableExpr}) as taxable_amount"),
            DB::raw("SUM({$taxExpr}) as total_tax")
        )->groupBy(DB::raw("COALESCE({$brandIdCol}, 0)"))
         ->orderByDesc('total_tax');

        $records = $perPage ? $query->paginate($perPage)->withQueryString() : $query->get();

        return ['records' => $records, 'summary' => $summary];
    }

    /**
     * Report: Branch-wise Tax (ONLY_FULL_GROUP_BY Safe).
     */
    protected function buildBranchWiseReport(Carbon $start, Carbon $end, Request $request, ?int $perPage): array
    {
        if (!Schema::hasTable('branches') || !Schema::hasColumn('orders', 'branch_id')) {
            $empty = $perPage ? new LengthAwarePaginator([], 0, $perPage) : collect();
            return ['records' => $empty, 'summary' => ['total_records' => 0, 'total_taxable_amount' => 0, 'total_tax_amount' => 0]];
        }

        $excludedStatuses = ['cancelled', 'cancel', 'failed', 'incomplete', 'returned', 'return'];

        $subtotalCol = Schema::hasColumn('orders', 'taxable_base_amount') 
            ? "COALESCE(NULLIF(orders.taxable_base_amount, 0), orders.subtotal, orders.grand_total, 0)" 
            : (Schema::hasColumn('orders', 'subtotal') ? "COALESCE(orders.subtotal, orders.grand_total, 0)" : "orders.grand_total");

        $taxColExpr = "CASE 
            WHEN COALESCE(orders.tax_amount, 0) > 0 THEN orders.tax_amount
            WHEN COALESCE(orders.product_tax_amount, 0) > 0 THEN orders.product_tax_amount
            ELSE 0 
        END";

        $branches = DB::table('branches')->get();
        if ($branches->isEmpty()) {
            $empty = $perPage ? new LengthAwarePaginator([], 0, $perPage) : collect();
            return ['records' => $empty, 'summary' => ['total_records' => 0, 'total_taxable_amount' => 0, 'total_tax_amount' => 0]];
        }

        // Branch-wise clean grouped aggregates
        $orderStats = DB::table('orders')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('branch_id')
            ->where(function ($q) use ($excludedStatuses) {
                $q->whereNotIn(DB::raw('LOWER(COALESCE(orders.order_status, ""))'), $excludedStatuses)
                  ->orWhereNull('orders.order_status');
            })
            ->select(
                'branch_id',
                DB::raw('COUNT(id) as total_orders'),
                DB::raw("COALESCE(SUM({$subtotalCol}), 0) as taxable_amount"),
                DB::raw("COALESCE(SUM({$taxColExpr}), 0) as total_tax"),
                DB::raw('COALESCE(SUM(grand_total), 0) as total_sales')
            )
            ->groupBy('branch_id')
            ->get()
            ->keyBy('branch_id');

        $rows = [];
        $grandTaxable = 0.0;
        $grandTax = 0.0;

        foreach ($branches as $branch) {
            $st = $orderStats->get($branch->id);
            $taxable = (float)($st->taxable_amount ?? 0);
            $tax = (float)($st->total_tax ?? 0);
            $sales = (float)($st->total_sales ?? 0);
            $count = (int)($st->total_orders ?? 0);

            $grandTaxable += $taxable;
            $grandTax += $tax;

            $rows[] = (object) [
                'branch_id'      => $branch->id,
                'branch_name'    => $branch->name,
                'bin_number'     => $branch->bin_number ?? ($branch->bin ?? 'N/A'),
                'total_orders'   => $count,
                'taxable_amount' => round($taxable, 2),
                'total_tax'      => round($tax, 2),
                'total_sales'    => round($sales, 2),
            ];
        }

        usort($rows, fn($a, $b) => $b->total_tax <=> $a->total_tax);

        $summary = [
            'total_records'        => count($rows),
            'total_taxable_amount' => round($grandTaxable, 2),
            'total_tax_amount'     => round($grandTax, 2),
        ];

        if ($perPage) {
            $page = LengthAwarePaginator::resolveCurrentPage();
            $paginatedRows = array_slice($rows, ($page - 1) * $perPage, $perPage);
            $records = new LengthAwarePaginator($paginatedRows, count($rows), $perPage, $page, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
            ]);
        } else {
            $records = collect($rows);
        }

        return ['records' => $records, 'summary' => $summary];
    }

    /**
     * Report: POS-wise Tax (Schema-Safe & High Performance).
     */
    protected function buildPosWiseReport(Carbon $start, Carbon $end, Request $request, ?int $perPage): array
    {
        $excludedStatuses = ['cancelled', 'cancel', 'failed', 'incomplete', 'returned', 'return'];

        $query = DB::table('orders')
            ->whereBetween('orders.created_at', [$start, $end]);

        if (Schema::hasColumn('orders', 'order_status')) {
            $query->where(function ($q) use ($excludedStatuses) {
                $q->whereNotIn(DB::raw('LOWER(COALESCE(orders.order_status, ""))'), $excludedStatuses)
                  ->orWhereNull('orders.order_status');
            });
        }

        $hasIsPos = Schema::hasColumn('orders', 'is_pos');
        $hasPosOrder = Schema::hasColumn('orders', 'pos_order');
        $hasOrderSource = Schema::hasColumn('orders', 'order_source');
        $hasOrderType = Schema::hasColumn('orders', 'order_type');
        $hasBranchId = Schema::hasColumn('orders', 'branch_id');

        $query->where(function ($q) use ($hasIsPos, $hasPosOrder, $hasOrderSource, $hasOrderType, $hasBranchId) {
            $matched = false;
            if ($hasIsPos) {
                $q->orWhere('orders.is_pos', 1);
                $matched = true;
            }
            if ($hasPosOrder) {
                $q->orWhere('orders.pos_order', 1);
                $matched = true;
            }
            if ($hasOrderSource) {
                $q->orWhere(DB::raw('LOWER(orders.order_source)'), 'pos');
                $matched = true;
            }
            if ($hasOrderType) {
                $q->orWhere(DB::raw('LOWER(orders.order_type)'), 'pos');
                $matched = true;
            }
            if (!$matched && $hasBranchId) {
                $q->whereNotNull('orders.branch_id');
                $matched = true;
            }
            if (!$matched) {
                $q->whereRaw('0 = 1');
            }
        });

        $subtotalField = Schema::hasColumn('orders', 'taxable_base_amount') 
            ? "COALESCE(NULLIF(orders.taxable_base_amount, 0), orders.subtotal, orders.grand_total, 0)" 
            : (Schema::hasColumn('orders', 'subtotal') ? "COALESCE(orders.subtotal, orders.grand_total, 0)" : "orders.grand_total");

        $taxColExpr = "CASE 
            WHEN COALESCE(orders.tax_amount, 0) > 0 THEN orders.tax_amount
            WHEN COALESCE(orders.product_tax_amount, 0) > 0 THEN orders.product_tax_amount
            ELSE 0 
        END";

        $summary = [
            'total_records'        => (clone $query)->count(),
            'total_taxable_amount' => (float)(clone $query)->sum(DB::raw($subtotalField)),
            'total_tax_amount'     => (float)(clone $query)->sum(DB::raw($taxColExpr)),
        ];

        $selects = [
            'orders.id',
            Schema::hasColumn('orders', 'invoice_no') ? 'orders.invoice_no' : DB::raw('orders.id as invoice_no'),
            'orders.created_at',
            $this->resolveCustomerNameCol(),
            $this->resolveCustomerPhoneCol(),
            $this->resolveBranchIdCol(),
            $this->resolveSubtotalCol(),
            $this->resolveTaxAmountCol(),
            $this->resolveProductTaxCol(),
            $this->resolveShippingTaxCol(),
            'orders.grand_total',
            DB::raw('1 as is_pos'),
        ];

        $query->select($selects)->latest('orders.created_at');

        $records = $perPage ? $query->paginate($perPage)->withQueryString() : $query->get();

        return ['records' => $records, 'summary' => $summary];
    }

    /**
     * Report: Order-wise Tax.
     */
    protected function buildOrderWiseReport(Carbon $start, Carbon $end, Request $request, ?int $perPage): array
    {
        return $this->buildAllTransactionsReport($start, $end, $request, $perPage);
    }

    /**
     * Report: Customer-wise Tax.
     */
    protected function buildCustomerWiseReport(Carbon $start, Carbon $end, Request $request, ?int $perPage): array
    {
        $phoneCol = Schema::hasColumn('orders', 'customer_phone') 
            ? 'orders.customer_phone' 
            : (Schema::hasColumn('orders', 'shipping_phone') ? 'orders.shipping_phone' : (Schema::hasColumn('orders', 'phone') ? 'orders.phone' : null));

        if (!$phoneCol) {
            $empty = $perPage ? new LengthAwarePaginator([], 0, $perPage) : collect();
            return ['records' => $empty, 'summary' => ['total_records' => 0, 'total_taxable_amount' => 0, 'total_tax_amount' => 0]];
        }

        $excludedStatuses = ['cancelled', 'cancel', 'failed', 'incomplete', 'returned', 'return'];

        $query = DB::table('orders')
            ->whereBetween('orders.created_at', [$start, $end])
            ->whereNotNull($phoneCol)
            ->where(function ($q) use ($excludedStatuses) {
                $q->whereNotIn(DB::raw('LOWER(COALESCE(orders.order_status, ""))'), $excludedStatuses)
                  ->orWhereNull('orders.order_status');
            });

        $subtotalField = Schema::hasColumn('orders', 'taxable_base_amount') 
            ? "COALESCE(NULLIF(orders.taxable_base_amount, 0), orders.subtotal, orders.grand_total, 0)" 
            : (Schema::hasColumn('orders', 'subtotal') ? "COALESCE(orders.subtotal, orders.grand_total, 0)" : "orders.grand_total");

        $taxColExpr = "CASE 
            WHEN COALESCE(orders.tax_amount, 0) > 0 THEN orders.tax_amount
            WHEN COALESCE(orders.product_tax_amount, 0) > 0 THEN orders.product_tax_amount
            ELSE 0 
        END";

        $summary = [
            'total_records'        => (clone $query)->distinct($phoneCol)->count($phoneCol),
            'total_taxable_amount' => (float)(clone $query)->sum(DB::raw($subtotalField)),
            'total_tax_amount'     => (float)(clone $query)->sum(DB::raw($taxColExpr)),
        ];

        $nameCol = Schema::hasColumn('orders', 'customer_name') 
            ? 'orders.customer_name' 
            : (Schema::hasColumn('orders', 'shipping_name') ? 'orders.shipping_name' : (Schema::hasColumn('orders', 'name') ? 'orders.name' : DB::raw("'Guest'")));

        $query->select(
            DB::raw("{$phoneCol} as customer_phone"),
            DB::raw("MAX({$nameCol}) as customer_name"),
            DB::raw('COUNT(orders.id) as order_count'),
            DB::raw("SUM({$subtotalField}) as taxable_amount"),
            DB::raw("SUM({$taxColExpr}) as total_tax"),
            DB::raw('SUM(orders.grand_total) as total_spent')
        )->groupBy($phoneCol)
         ->orderByDesc('total_tax');

        $records = $perPage ? $query->paginate($perPage)->withQueryString() : $query->get();

        return ['records' => $records, 'summary' => $summary];
    }

    /**
     * Report: Shipping & Delivery Service Tax.
     */
    protected function buildShippingTaxReport(Carbon $start, Carbon $end, Request $request, ?int $perPage): array
    {
        $shipChargeCol = Schema::hasColumn('orders', 'shipping_charge') 
            ? 'shipping_charge' 
            : (Schema::hasColumn('orders', 'delivery_charge') ? 'delivery_charge' : (Schema::hasColumn('orders', 'shipping_cost') ? 'shipping_cost' : null));

        if (!$shipChargeCol) {
            $empty = $perPage ? new LengthAwarePaginator([], 0, $perPage) : collect();
            return ['records' => $empty, 'summary' => ['total_records' => 0, 'total_taxable_amount' => 0, 'total_tax_amount' => 0]];
        }

        $excludedStatuses = ['cancelled', 'cancel', 'failed', 'incomplete', 'returned', 'return'];

        $query = DB::table('orders')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where("orders.{$shipChargeCol}", '>', 0)
            ->where(function ($q) use ($excludedStatuses) {
                $q->whereNotIn(DB::raw('LOWER(COALESCE(orders.order_status, ""))'), $excludedStatuses)
                  ->orWhereNull('orders.order_status');
            });

        $shipTaxField = Schema::hasColumn('orders', 'shipping_tax_amount') ? 'orders.shipping_tax_amount' : null;

        $summary = [
            'total_records'        => (clone $query)->count(),
            'total_taxable_amount' => (float)(clone $query)->sum("orders.{$shipChargeCol}"),
            'total_tax_amount'     => $shipTaxField ? (float)(clone $query)->sum($shipTaxField) : 0.00,
        ];

        $cityExpr = Schema::hasColumn('orders', 'delivery_city') 
            ? 'orders.delivery_city' 
            : (Schema::hasColumn('orders', 'district') ? 'orders.district as delivery_city' : (Schema::hasColumn('orders', 'shipping_district') ? 'orders.shipping_district as delivery_city' : DB::raw("'Dhaka' as delivery_city")));

        $courierExpr = Schema::hasColumn('orders', 'courier_name') 
            ? 'orders.courier_name' 
            : DB::raw("'Standard Courier' as courier_name");

        $query->select(
            'orders.id',
            Schema::hasColumn('orders', 'invoice_no') ? 'orders.invoice_no' : DB::raw('orders.id as invoice_no'),
            'orders.created_at',
            "orders.{$shipChargeCol} as taxable_amount",
            $shipTaxField ? "orders.{$shipTaxField} as total_tax" : DB::raw("0.00 as total_tax"),
            $cityExpr,
            $courierExpr
        )->latest('orders.created_at');

        $records = $perPage ? $query->paginate($perPage)->withQueryString() : $query->get();

        return ['records' => $records, 'summary' => $summary];
    }

    /**
     * Report: Tax Collection History.
     */
    protected function buildCollectionHistoryReport(Carbon $start, Carbon $end, Request $request, ?int $perPage): array
    {
        return $this->buildAllTransactionsReport($start, $end, $request, $perPage);
    }

    /**
     * Report: Government Tax Payment History (Treasury Challan).
     */
    protected function buildPaymentHistoryReport(Carbon $start, Carbon $end, Request $request, ?int $perPage): array
    {
        if (!Schema::hasTable('tax_payments') || !class_exists(TaxPayment::class)) {
            $empty = $perPage ? new LengthAwarePaginator([], 0, $perPage) : collect();
            return ['records' => $empty, 'summary' => ['total_records' => 0, 'total_taxable_amount' => 0, 'total_tax_amount' => 0]];
        }

        $query = TaxPayment::query()
            ->whereBetween('payment_date', [$start->toDateString(), $end->toDateString()]);

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $summary = [
            'total_records'        => (clone $query)->count(),
            'total_taxable_amount' => 0.00,
            'total_tax_amount'     => (float)(clone $query)->sum('amount'),
        ];

        $query->latest('payment_date');

        $records = $perPage ? $query->paginate($perPage)->withQueryString() : $query->get();

        return ['records' => $records, 'summary' => $summary];
    }

    /**
     * Report: Refund Tax Reversal History.
     */
    protected function buildRefundHistoryReport(Carbon $start, Carbon $end, Request $request, ?int $perPage): array
    {
        if (!Schema::hasTable('tax_adjustments') || !class_exists(TaxAdjustment::class)) {
            $empty = $perPage ? new LengthAwarePaginator([], 0, $perPage) : collect();
            return ['records' => $empty, 'summary' => ['total_records' => 0, 'total_taxable_amount' => 0, 'total_tax_amount' => 0]];
        }

        $query = TaxAdjustment::query()
            ->with('order:id,invoice_no,customer_name')
            ->where('adjustment_type', 'refund')
            ->whereBetween('created_at', [$start, $end]);

        $summary = [
            'total_records'        => (clone $query)->count(),
            'total_taxable_amount' => (float)(clone $query)->sum('original_tax_amount'),
            'total_tax_amount'     => (float)(clone $query)->sum('net_tax_change'),
        ];

        $query->latest('id');

        $records = $perPage ? $query->paginate($perPage)->withQueryString() : $query->get();

        return ['records' => $records, 'summary' => $summary];
    }

    /**
     * Report: All Adjustments History (Refund, Cancel, Exchange).
     */
    protected function buildAdjustmentHistoryReport(Carbon $start, Carbon $end, Request $request, ?int $perPage): array
    {
        if (!Schema::hasTable('tax_adjustments') || !class_exists(TaxAdjustment::class)) {
            $empty = $perPage ? new LengthAwarePaginator([], 0, $perPage) : collect();
            return ['records' => $empty, 'summary' => ['total_records' => 0, 'total_taxable_amount' => 0, 'total_tax_amount' => 0]];
        }

        $query = TaxAdjustment::query()
            ->with(['order:id,invoice_no,customer_name', 'creator:id,name'])
            ->whereBetween('created_at', [$start, $end]);

        $summary = [
            'total_records'        => (clone $query)->count(),
            'total_taxable_amount' => (float)(clone $query)->sum('original_tax_amount'),
            'total_tax_amount'     => (float)(clone $query)->sum('net_tax_change'),
        ];

        $query->latest('id');

        $records = $perPage ? $query->paginate($perPage)->withQueryString() : $query->get();

        return ['records' => $records, 'summary' => $summary];
    }

    /**
     * Resolve Date Range across all requested filter modes.
     */
    protected function resolveDateRange(string $filter, Request $request): array
    {
        $now = Carbon::now();

        return match ($filter) {
            'all', 'all_time' => [
                'start' => Carbon::parse('2024-01-01')->toDateString(),
                'end'   => $now->copy()->addDay()->toDateString(),
            ],
            'today'          => ['start' => $now->toDateString(), 'end' => $now->toDateString()],
            'yesterday'      => ['start' => $now->copy()->subDay()->toDateString(), 'end' => $now->copy()->subDay()->toDateString()],
            'last_7_days'    => ['start' => $now->copy()->subDays(6)->toDateString(), 'end' => $now->copy()->toDateString()],
            'this_week'      => ['start' => $now->copy()->startOfWeek()->toDateString(), 'end' => $now->copy()->endOfWeek()->toDateString()],
            'previous_week'  => ['start' => $now->copy()->subWeek()->startOfWeek()->toDateString(), 'end' => $now->copy()->subWeek()->endOfWeek()->toDateString()],
            'last_30_days'   => ['start' => $now->copy()->subDays(29)->toDateString(), 'end' => $now->toDateString()],
            'this_month'     => ['start' => $now->copy()->startOfMonth()->toDateString(), 'end' => $now->copy()->endOfMonth()->toDateString()],
            'previous_month' => ['start' => $now->copy()->subMonth()->startOfMonth()->toDateString(), 'end' => $now->copy()->subMonth()->endOfMonth()->toDateString()],
            'specific_date'  => [
                'start' => !empty($request->specific_date) ? Carbon::parse($request->specific_date)->toDateString() : $now->toDateString(),
                'end'   => !empty($request->specific_date) ? Carbon::parse($request->specific_date)->toDateString() : $now->toDateString(),
            ],
            'custom_range'   => [
                'start' => !empty($request->start_date) ? Carbon::parse($request->start_date)->toDateString() : $now->copy()->startOfMonth()->toDateString(),
                'end'   => !empty($request->end_date) ? Carbon::parse($request->end_date)->toDateString() : $now->toDateString(),
            ],
            'specific_month' => [
                'start' => !empty($request->month_year) ? Carbon::parse($request->month_year)->startOfMonth()->toDateString() : $now->copy()->startOfMonth()->toDateString(),
                'end'   => !empty($request->month_year) ? Carbon::parse($request->month_year)->endOfMonth()->toDateString() : $now->copy()->endOfMonth()->toDateString(),
            ],
            'specific_year'  => [
                'start' => !empty($request->year) ? Carbon::createFromDate($request->year, 1, 1)->startOfYear()->toDateString() : $now->copy()->startOfYear()->toDateString(),
                'end'   => !empty($request->year) ? Carbon::createFromDate($request->year, 12, 31)->endOfYear()->toDateString() : $now->copy()->endOfYear()->toDateString(),
            ],
            default          => ['start' => $now->copy()->startOfMonth()->toDateString(), 'end' => $now->copy()->endOfMonth()->toDateString()],
        };
    }

    /**
     * CSV Column Definitions for Export.
     */
    protected function getCsvColumns(string $reportType): array
    {
        return match ($reportType) {
            'product_wise'       => ['Product ID', 'Product Title', 'Quantity Sold', 'Taxable Amount (BDT)', 'Tax Rate (%)', 'Tax Collected (BDT)'],
            'variant_wise'       => ['Variant ID', 'Product ID', 'Product Title', 'Quantity Sold', 'Taxable Amount (BDT)', 'Tax Rate (%)', 'Tax Collected (BDT)'],
            'category_wise'      => ['Category ID', 'Category Name', 'Items Sold', 'Taxable Amount (BDT)', 'Tax Collected (BDT)'],
            'brand_wise'         => ['Brand ID', 'Brand Name', 'Items Sold', 'Taxable Amount (BDT)', 'Tax Collected (BDT)'],
            'branch_wise'        => ['Branch ID', 'Branch Name', 'BIN Number', 'Total Orders', 'Taxable Sales (BDT)', 'Tax Collected (BDT)', 'Gross Sales (BDT)'],
            'customer_wise'      => ['Customer Phone', 'Customer Name', 'Total Orders', 'Taxable Amount (BDT)', 'Tax Paid (BDT)', 'Gross Spend (BDT)'],
            'shipping_tax'       => ['Order ID', 'Invoice No', 'Date', 'Delivery City', 'Shipping Charge (BDT)', 'Shipping Tax (BDT)'],
            'payment_history'    => ['Payment Date', 'Tax Period', 'Amount Paid (BDT)', 'Method', 'Challan Number', 'Mushak Reference', 'Status'],
            'adjustment_history', 'refund_history' => ['Date', 'Adjustment ID', 'Order Invoice', 'Type', 'Original Tax (BDT)', 'Adjusted Reversal (BDT)', 'Reference / Reason'],
            default              => ['Invoice No', 'Date', 'Customer Name', 'Customer Phone', 'Taxable Base (BDT)', 'Product Tax (BDT)', 'Shipping Tax (BDT)', 'Total Tax (BDT)', 'Grand Total (BDT)', 'Channel'],
        };
    }

    /**
     * Format a single CSV row data.
     */
    protected function formatCsvRow(string $reportType, object $row): array
    {
        return match ($reportType) {
            'product_wise'    => [
                $row->product_id,
                $row->title,
                $row->total_qty ?? 0,
                number_format((float)($row->taxable_amount ?? 0), 2, '.', ''),
                number_format((float)($row->avg_rate ?? 0), 2, '.', ''),
                number_format((float)($row->total_tax ?? 0), 2, '.', ''),
            ],
            'variant_wise'    => [
                $row->variant_id,
                $row->product_id,
                $row->title,
                $row->total_qty ?? 0,
                number_format((float)($row->taxable_amount ?? 0), 2, '.', ''),
                number_format((float)($row->avg_rate ?? 0), 2, '.', ''),
                number_format((float)($row->total_tax ?? 0), 2, '.', ''),
            ],
            'category_wise'   => [
                $row->category_id,
                $row->category_name,
                $row->total_qty ?? 0,
                number_format((float)($row->taxable_amount ?? 0), 2, '.', ''),
                number_format((float)($row->total_tax ?? 0), 2, '.', ''),
            ],
            'brand_wise'      => [
                $row->brand_id,
                $row->brand_name,
                $row->total_qty ?? 0,
                number_format((float)($row->taxable_amount ?? 0), 2, '.', ''),
                number_format((float)($row->total_tax ?? 0), 2, '.', ''),
            ],
            'branch_wise'     => [
                $row->branch_id,
                $row->branch_name,
                $row->bin_number ?? 'N/A',
                $row->total_orders ?? 0,
                number_format((float)($row->taxable_amount ?? 0), 2, '.', ''),
                number_format((float)($row->total_tax ?? 0), 2, '.', ''),
                number_format((float)($row->total_sales ?? 0), 2, '.', ''),
            ],
            'customer_wise'   => [
                $row->customer_phone,
                $row->customer_name ?? 'Guest',
                $row->order_count ?? 0,
                number_format((float)($row->taxable_amount ?? 0), 2, '.', ''),
                number_format((float)($row->total_tax ?? 0), 2, '.', ''),
                number_format((float)($row->total_spent ?? 0), 2, '.', ''),
            ],
            'shipping_tax'    => [
                $row->id,
                $row->invoice_no,
                !empty($row->created_at) ? Carbon::parse($row->created_at)->format('Y-m-d H:i:s') : 'N/A',
                $row->delivery_city ?? 'N/A',
                number_format((float)($row->taxable_amount ?? 0), 2, '.', ''),
                number_format((float)($row->total_tax ?? 0), 2, '.', ''),
            ],
            'payment_history' => [
                $row->payment_date,
                $row->tax_period,
                number_format((float)($row->amount ?? 0), 2, '.', ''),
                $row->payment_method,
                $row->challan_number ?? 'N/A',
                $row->mushak_reference ?? 'N/A',
                ucfirst($row->payment_status ?? 'completed'),
            ],
            'adjustment_history', 'refund_history' => [
                !empty($row->created_at) ? Carbon::parse($row->created_at)->format('Y-m-d H:i:s') : 'N/A',
                '#ADJ-' . $row->id,
                !empty($row->order) ? ($row->order->invoice_no ?? '#'.$row->order_id) : (!empty($row->invoice_no) ? $row->invoice_no : '#'.($row->order_id ?? '')),
                ucfirst(str_replace('_', ' ', $row->adjustment_type ?? 'Adjustment')),
                number_format((float)($row->original_tax_amount ?? 0), 2, '.', ''),
                number_format((float)($row->net_tax_change ?? 0), 2, '.', ''),
                $row->reason ?? ($row->reference_no ?? 'N/A'),
            ],
            default           => [
                $row->invoice_no,
                !empty($row->created_at) ? Carbon::parse($row->created_at)->format('Y-m-d H:i:s') : 'N/A',
                $row->customer_name ?? 'Guest',
                $row->customer_phone ?? 'N/A',
                number_format((float)($row->taxable_amount ?? 0), 2, '.', ''),
                number_format((float)($row->product_tax_amount ?? 0), 2, '.', ''),
                number_format((float)($row->shipping_tax_amount ?? 0), 2, '.', ''),
                number_format((float)($row->tax_amount ?? 0), 2, '.', ''),
                number_format((float)($row->grand_total ?? 0), 2, '.', ''),
                !empty($row->is_pos) ? 'POS' : 'Online',
            ],
        };
    }
}