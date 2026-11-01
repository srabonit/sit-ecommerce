<?php

namespace App\Http\Controllers\Admin\Tax;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TaxDashboardController extends Controller
{
    /**
     * Resolve the items table name safely (Supports order_items & order_details).
     */
    protected function resolveItemsTable(): ?string
    {
        if (Schema::hasTable('order_items')) {
            return 'order_items';
        }
        if (Schema::hasTable('order_details')) {
            return 'order_details';
        }
        return null;
    }

    /**
     * Display the Central Tax & VAT Analytics Dashboard.
     */
    public function index(Request $request)
    {
        $dates = $this->resolveDateRange(
            $request->get('period', 'this_month'), 
            $request->get('start_date'), 
            $request->get('end_date')
        );
        $startDate = $dates['start'];
        $endDate = $dates['end'];
        $branchId = $request->filled('branch_id') ? (int)$request->branch_id : null;

        // ১. কোর ট্যাক্স মেট্রিক্স ক্যালকুলেশন (রিয়েলটাইম সেলস ও ভ্যাট)
        $metrics = $this->calculateTaxMetrics($startDate, $endDate, $branchId);

        // ২. অনলাইন বনাম পিওএস ট্যাক্স পৃথকীকরণ
        $channelTax = $this->calculateChannelTax($startDate, $endDate, $branchId);

        // ৩. ব্রাঞ্চ-ভিত্তিক ভ্যাট কালেকশন সারসংক্ষেপ (ONLY_FULL_GROUP_BY Safe)
        $branchSummary = $this->calculateBranchTaxSummary($startDate, $endDate);

        // ৪. পণ্য-ভিত্তিক শীর্ষ ট্যাক্স কালেকশন তালিকা
        $topTaxProducts = $this->getTopTaxProducts($startDate, $endDate, $branchId);

        // ৫. সাম্প্রতিক সরকারি কোষাগার পেমেন্টস
        $recentPayments = [];
        if (Schema::hasTable('tax_payments')) {
            $recentPayments = DB::table('tax_payments')
                ->latest('payment_date')
                ->latest('id')
                ->take(5)
                ->get();
        }

        // ৬. সাম্প্রতিক ট্যাক্স সমন্বয় ও রিফান্ড
        $recentAdjustments = [];
        if (Schema::hasTable('tax_adjustments')) {
            $recentAdjustments = DB::table('tax_adjustments')
                ->leftJoin('orders', 'tax_adjustments.order_id', '=', 'orders.id')
                ->select(
                    'tax_adjustments.*',
                    'orders.invoice_no',
                    'orders.grand_total'
                )
                ->latest('tax_adjustments.id')
                ->take(5)
                ->get();
        }

        $branches = Schema::hasTable('branches') 
            ? DB::table('branches')->where('is_active', true)->orderBy('name')->get() 
            : collect();

        if ($request->ajax()) {
            return response()->json([
                'success'        => true,
                'metrics'        => $metrics,
                'channel_tax'    => $channelTax,
                'branch_summary' => $branchSummary,
                'top_products'   => $topTaxProducts,
            ]);
        }

        return view('admin.tax.dashboard', compact(
            'metrics',
            'channelTax',
            'branchSummary',
            'topTaxProducts',
            'recentPayments',
            'recentAdjustments',
            'branches',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get AJAX Chart Analytics for Daily / Monthly Trends.
     */
    public function chartDataAjax(Request $request): JsonResponse
    {
        $period = $request->get('period', 'this_month');
        $dates = $this->resolveDateRange($period, $request->get('start_date'), $request->get('end_date'));
        $branchId = $request->filled('branch_id') ? (int)$request->branch_id : null;

        $startDate = Carbon::parse($dates['start'])->startOfDay();
        $endDate = Carbon::parse($dates['end'])->endOfDay();

        $labels = [];
        $collectedData = [];
        $liabilityData = [];

        $excludedStatuses = ['cancelled', 'cancel', 'failed', 'incomplete', 'returned', 'return'];
        $itemsTable = $this->resolveItemsTable();

        // দিনভিত্তিক গ্রুপিং
        $current = clone $startDate;
        while ($current->lte($endDate)) {
            $dateStr = $current->format('Y-m-d');
            $label = $current->format('d M');

            $orderQuery = DB::table('orders')
                ->whereDate('created_at', $dateStr)
                ->where(function ($q) use ($excludedStatuses) {
                    $q->whereNotIn(DB::raw('LOWER(COALESCE(order_status, ""))'), $excludedStatuses)
                      ->orWhereNull('order_status');
                });

            if ($branchId && Schema::hasColumn('orders', 'branch_id')) {
                $orderQuery->where('branch_id', $branchId);
            }

            $collected = (float)(clone $orderQuery)->sum('tax_amount');
            if ($collected <= 0 && Schema::hasColumn('orders', 'product_tax_amount')) {
                $collected = (float)(clone $orderQuery)->sum('product_tax_amount');
            }

            // আইটেম টেবিল ফলব্যাক
            if ($collected <= 0 && $itemsTable && Schema::hasColumn($itemsTable, 'tax_amount')) {
                $collected = (float) DB::table($itemsTable)
                    ->join('orders', "{$itemsTable}.order_id", '=', 'orders.id')
                    ->whereDate('orders.created_at', $dateStr)
                    ->where(function ($q) use ($excludedStatuses) {
                        $q->whereNotIn(DB::raw('LOWER(COALESCE(orders.order_status, ""))'), $excludedStatuses)
                          ->orWhereNull('orders.order_status');
                    })
                    ->when($branchId && Schema::hasColumn('orders', 'branch_id'), function ($q) use ($branchId) {
                        $q->where('orders.branch_id', $branchId);
                    })
                    ->sum("{$itemsTable}.tax_amount");
            }

            $adjusted = 0.0;
            if (Schema::hasTable('tax_adjustments')) {
                $adjQ = DB::table('tax_adjustments')->whereDate('created_at', $dateStr);
                if ($branchId && Schema::hasColumn('orders', 'branch_id')) {
                    $adjQ->whereIn('order_id', function ($sq) use ($branchId) {
                        $sq->select('id')->from('orders')->where('branch_id', $branchId);
                    });
                }
                $adjusted = (float)$adjQ->sum('net_tax_change');
            }

            $labels[] = $label;
            $collectedData[] = round($collected, 2);
            $liabilityData[] = round(max(0, $collected - $adjusted), 2);

            $current->addDay();
        }

        return response()->json([
            'labels'    => $labels,
            'collected' => $collectedData,
            'liability' => $liabilityData,
        ]);
    }

    /**
     * Calculate core monetary tax indicators (Handles fallbacks so VAT never shows 0 when charged).
     */
    protected function calculateTaxMetrics(string $startDate, string $endDate, ?int $branchId = null): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        $excludedStatuses = ['cancelled', 'cancel', 'failed', 'incomplete', 'returned', 'return'];
        $itemsTable = $this->resolveItemsTable();

        $baseQuery = DB::table('orders')
            ->whereBetween('created_at', [$start, $end])
            ->where(function ($q) use ($excludedStatuses) {
                $q->whereNotIn(DB::raw('LOWER(COALESCE(order_status, ""))'), $excludedStatuses)
                  ->orWhereNull('order_status');
            });

        if ($branchId && Schema::hasColumn('orders', 'branch_id')) {
            $baseQuery->where('branch_id', $branchId);
        }

        // মোট বিক্রয়
        $totalGrossSales = (float)(clone $baseQuery)->sum('grand_total');

        // মোট সংগৃহীত ভ্যাট
        $totalCollectedTax = (float)(clone $baseQuery)->sum('tax_amount');
        if ($totalCollectedTax <= 0 && Schema::hasColumn('orders', 'product_tax_amount')) {
            $totalCollectedTax = (float)(clone $baseQuery)->sum('product_tax_amount');
        }

        // আইটেম টেবিল থেকে ব্যাকআপ সাম
        if ($totalCollectedTax <= 0 && $itemsTable && Schema::hasColumn($itemsTable, 'tax_amount')) {
            $totalCollectedTax = (float) DB::table($itemsTable)
                ->join('orders', "{$itemsTable}.order_id", '=', 'orders.id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->where(function ($q) use ($excludedStatuses) {
                    $q->whereNotIn(DB::raw('LOWER(COALESCE(orders.order_status, ""))'), $excludedStatuses)
                      ->orWhereNull('orders.order_status');
                })
                ->when($branchId && Schema::hasColumn('orders', 'branch_id'), function ($q) use ($branchId) {
                    $q->where('orders.branch_id', $branchId);
                })
                ->sum("{$itemsTable}.tax_amount");
        }

        // প্রোডাক্ট ট্যাক্স ও শিপিং ট্যাক্স
        $productTax = Schema::hasColumn('orders', 'product_tax_amount')
            ? (float)(clone $baseQuery)->sum(DB::raw("COALESCE(NULLIF(product_tax_amount, 0), tax_amount, 0)"))
            : $totalCollectedTax;

        $shippingTax = Schema::hasColumn('orders', 'shipping_tax_amount')
            ? (float)(clone $baseQuery)->sum(DB::raw("COALESCE(shipping_tax_amount, 0)"))
            : 0.0;

        // করযোগ্য ও করমুক্ত সেলস বেস নির্ণয়
        $taxableSalesBase = 0.0;
        $exemptSalesBase = 0.0;
        $zeroRatedSalesBase = 0.0;

        if ($itemsTable) {
            $itemsBase = DB::table($itemsTable)
                ->join('orders', "{$itemsTable}.order_id", '=', 'orders.id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->where(function ($q) use ($excludedStatuses) {
                    $q->whereNotIn(DB::raw('LOWER(COALESCE(orders.order_status, ""))'), $excludedStatuses)
                      ->orWhereNull('orders.order_status');
                });

            if ($branchId && Schema::hasColumn('orders', 'branch_id')) {
                $itemsBase->where('orders.branch_id', $branchId);
            }

            $hasBaseCol = Schema::hasColumn($itemsTable, 'taxable_base_amount');
            $hasSubtotal = Schema::hasColumn($itemsTable, 'subtotal');
            $baseExpr = $hasBaseCol 
                ? "COALESCE(NULLIF({$itemsTable}.taxable_base_amount, 0), " . ($hasSubtotal ? "{$itemsTable}.subtotal" : "{$itemsTable}.unit_price * {$itemsTable}.quantity") . ", 0)"
                : ($hasSubtotal ? "COALESCE({$itemsTable}.subtotal, 0)" : "COALESCE({$itemsTable}.unit_price * {$itemsTable}.quantity, 0)");

            $hasTaxRate = Schema::hasColumn($itemsTable, 'tax_rate');
            $hasTaxAmount = Schema::hasColumn($itemsTable, 'tax_amount');
            $hasStatusType = Schema::hasColumn($itemsTable, 'status_type');

            if ($hasTaxRate || $hasTaxAmount || $hasStatusType) {
                // ১. করযোগ্য সেলস (যেখানে ভ্যাট রেট বা ভ্যাট অ্যামাউন্ট > ০)
                $taxableSalesBase = (float)(clone $itemsBase)
                    ->where(function ($q) use ($hasTaxRate, $hasTaxAmount, $itemsTable) {
                        if ($hasTaxRate) $q->where("{$itemsTable}.tax_rate", '>', 0);
                        if ($hasTaxAmount) $q->orWhere("{$itemsTable}.tax_amount", '>', 0);
                    })
                    ->sum(DB::raw($baseExpr));

                // ২. শূন্য কর হার সেলস (Zero-Rated 0% Sales)
                $zeroRatedSalesBase = (float)(clone $itemsBase)
                    ->where(function ($q) use ($hasTaxRate, $hasTaxAmount, $hasStatusType, $itemsTable) {
                        if ($hasStatusType) {
                            $q->where("{$itemsTable}.status_type", 'zero_rated');
                        } else {
                            $q->where(function ($sub) use ($hasTaxRate, $hasTaxAmount, $itemsTable) {
                                if ($hasTaxRate) $sub->where("{$itemsTable}.tax_rate", 0);
                                if ($hasTaxAmount) $sub->where("{$itemsTable}.tax_amount", 0);
                            });
                        }
                    })
                    ->sum(DB::raw($baseExpr));

                // ৩. সম্পূর্ণ করমুক্ত সেলস (Exempt Sales)
                if ($hasStatusType) {
                    $exemptSalesBase = (float)(clone $itemsBase)
                        ->where("{$itemsTable}.status_type", 'exempt')
                        ->sum(DB::raw($baseExpr));
                } else {
                    $exemptSalesBase = max(0.0, round($totalGrossSales - ($taxableSalesBase + $zeroRatedSalesBase + $totalCollectedTax), 2));
                }
            }
        }

        // ফলব্যাক রেজোলিউশন
        if ($taxableSalesBase <= 0 && $totalCollectedTax > 0) {
            if (Schema::hasColumn('orders', 'taxable_base_amount')) {
                $taxableSalesBase = (float)(clone $baseQuery)
                    ->where(DB::raw("COALESCE(tax_amount, 0)"), '>', 0)
                    ->sum(DB::raw("COALESCE(NULLIF(taxable_base_amount, 0), subtotal, 0)"));
            } else {
                $taxableSalesBase = max(0.0, $totalGrossSales - $totalCollectedTax);
            }
        }

        // ট্যাক্স রিফান্ড ও এডজাস্টমেন্ট
        $totalTaxRefunded = 0.0;
        if (Schema::hasTable('tax_adjustments')) {
            $adjustmentQuery = DB::table('tax_adjustments')
                ->whereBetween('created_at', [$start, $end]);

            if ($branchId && Schema::hasColumn('orders', 'branch_id')) {
                $adjustmentQuery->whereIn('order_id', function ($q) use ($branchId) {
                    $q->select('id')->from('orders')->where('branch_id', $branchId);
                });
            }

            $totalTaxRefunded = (float)$adjustmentQuery->sum('net_tax_change');
        }

        // নেট কর দায় (Net Tax Liability)
        $netTaxLiability = max(0.0, round($totalCollectedTax - $totalTaxRefunded, 2));

        // সরকারি কোষাগারে জমাকৃত পেমেন্ট
        $totalTaxPaid = 0.0;
        if (Schema::hasTable('tax_payments')) {
            $paymentQuery = DB::table('tax_payments')
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->where('payment_status', 'completed');

            $totalTaxPaid = (float)$paymentQuery->sum('amount');
        }

        // বকেয়া কর দায় (Outstanding Tax)
        $outstandingTax = max(0.0, round($netTaxLiability - $totalTaxPaid, 2));

        return [
            'gross_sales'         => round($totalGrossSales, 2),
            'taxable_sales'       => round($taxableSalesBase, 2),
            'exempt_sales'        => round($exemptSalesBase, 2),
            'zero_rated_sales'    => round($zeroRatedSalesBase, 2),
            'total_tax_collected' => round($totalCollectedTax, 2),
            'product_tax'         => round($productTax, 2),
            'shipping_tax'        => round($shippingTax, 2),
            'total_tax_refunded'  => round($totalTaxRefunded, 2),
            'net_tax_liability'   => $netTaxLiability,
            'total_tax_paid'      => round($totalTaxPaid, 2),
            'outstanding_tax'     => $outstandingTax,
        ];
    }

    /**
     * Calculate online vs POS channel breakdown (Schema-Safe & High Performance).
     */
    protected function calculateChannelTax(string $startDate, string $endDate, ?int $branchId = null): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        $excludedStatuses = ['cancelled', 'cancel', 'failed', 'incomplete', 'returned', 'return'];

        $baseQuery = DB::table('orders')
            ->whereBetween('created_at', [$start, $end])
            ->where(function ($q) use ($excludedStatuses) {
                $q->whereNotIn(DB::raw('LOWER(COALESCE(order_status, ""))'), $excludedStatuses)
                  ->orWhereNull('order_status');
            });

        if ($branchId && Schema::hasColumn('orders', 'branch_id')) {
            $baseQuery->where('branch_id', $branchId);
        }

        $hasOrderSource = Schema::hasColumn('orders', 'order_source');
        $hasOrderType   = Schema::hasColumn('orders', 'order_type');
        $hasIsPos       = Schema::hasColumn('orders', 'is_pos');
        $hasPosOrder    = Schema::hasColumn('orders', 'pos_order');
        $hasBranchId    = Schema::hasColumn('orders', 'branch_id');

        // 1. POS Filter
        $posQuery = (clone $baseQuery)->where(function ($q) use ($hasOrderSource, $hasOrderType, $hasIsPos, $hasPosOrder, $hasBranchId) {
            $matched = false;
            if ($hasOrderSource) {
                $q->orWhere('order_source', 'pos');
                $matched = true;
            }
            if ($hasOrderType) {
                $q->orWhere('order_type', 'pos');
                $matched = true;
            }
            if ($hasIsPos) {
                $q->orWhere('is_pos', 1);
                $matched = true;
            }
            if ($hasPosOrder) {
                $q->orWhere('pos_order', 1);
                $matched = true;
            }
            if (!$matched && $hasBranchId) {
                $q->whereNotNull('branch_id');
                $matched = true;
            }
            if (!$matched) {
                $q->whereRaw('0 = 1');
            }
        });

        $posSales     = (float)(clone $posQuery)->sum('grand_total');
        $posCollected = (float)(clone $posQuery)->sum('tax_amount');
        if ($posCollected <= 0 && Schema::hasColumn('orders', 'product_tax_amount')) {
            $posCollected = (float)(clone $posQuery)->sum('product_tax_amount');
        }
        $posOrders    = (clone $posQuery)->count();

        // 2. Online Filter
        $onlineQuery = (clone $baseQuery)->where(function ($q) use ($hasOrderSource, $hasOrderType, $hasIsPos, $hasPosOrder, $hasBranchId) {
            if ($hasOrderSource) {
                $q->where(function ($sq) {
                    $sq->whereNull('order_source')->orWhere('order_source', '!=', 'pos');
                });
            }
            if ($hasOrderType) {
                $q->where(function ($sq) {
                    $sq->whereNull('order_type')->orWhere('order_type', '!=', 'pos');
                });
            }
            if ($hasIsPos) {
                $q->where(function ($sq) {
                    $sq->whereNull('is_pos')->orWhere('is_pos', 0);
                });
            }
            if ($hasPosOrder) {
                $q->where(function ($sq) {
                    $sq->whereNull('pos_order')->orWhere('pos_order', 0);
                });
            }
            if (!$hasOrderSource && !$hasOrderType && !$hasIsPos && !$hasPosOrder && $hasBranchId) {
                $q->whereNull('branch_id');
            }
        });

        $onlineSales     = (float)(clone $onlineQuery)->sum('grand_total');
        $onlineCollected = (float)(clone $onlineQuery)->sum('tax_amount');
        if ($onlineCollected <= 0 && Schema::hasColumn('orders', 'product_tax_amount')) {
            $onlineCollected = (float)(clone $onlineQuery)->sum('product_tax_amount');
        }
        $onlineOrders    = (clone $onlineQuery)->count();

        return [
            'pos' => [
                'collected_tax' => round($posCollected, 2),
                'total_sales'   => round($posSales, 2),
                'order_count'   => $posOrders,
            ],
            'online' => [
                'collected_tax' => round($onlineCollected, 2),
                'total_sales'   => round($onlineSales, 2),
                'order_count'   => $onlineOrders,
            ],
        ];
    }

    /**
     * Calculate branch-wise tax collection summary (100% ONLY_FULL_GROUP_BY Safe).
     */
    protected function calculateBranchTaxSummary(string $startDate, string $endDate): array
    {
        if (!Schema::hasTable('branches')) {
            return [];
        }

        $branches = DB::table('branches')->get();
        if ($branches->isEmpty()) {
            return [];
        }

        $hasBranchId = Schema::hasColumn('orders', 'branch_id');
        if (!$hasBranchId) {
            return [];
        }

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        $excludedStatuses = ['cancelled', 'cancel', 'failed', 'incomplete', 'returned', 'return'];
        $itemsTable = $this->resolveItemsTable();

        // Aggregate orders strictly grouped by branch_id (Eliminates Error 1055)
        $branchOrders = DB::table('orders')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('branch_id')
            ->where(function ($q) use ($excludedStatuses) {
                $q->whereNotIn(DB::raw('LOWER(COALESCE(order_status, ""))'), $excludedStatuses)
                  ->orWhereNull('order_status');
            })
            ->select(
                'branch_id',
                DB::raw('COUNT(id) as order_count'),
                DB::raw('COALESCE(SUM(grand_total), 0) as total_sales'),
                DB::raw('COALESCE(SUM(tax_amount), 0) as total_tax')
            )
            ->groupBy('branch_id')
            ->get()
            ->keyBy('branch_id');

        $result = [];
        foreach ($branches as $branch) {
            $stats = $branchOrders->get($branch->id);
            $totalTax   = (float)($stats->total_tax ?? 0);
            $totalSales = (float)($stats->total_sales ?? 0);
            $orderCount = (int)($stats->order_count ?? 0);

            // Fallback: If orders.tax_amount is 0, check order_items tax
            if ($totalTax <= 0 && $orderCount > 0 && $itemsTable && Schema::hasColumn($itemsTable, 'tax_amount')) {
                $totalTax = (float) DB::table($itemsTable)
                    ->join('orders', "{$itemsTable}.order_id", '=', 'orders.id')
                    ->where('orders.branch_id', $branch->id)
                    ->whereBetween('orders.created_at', [$start, $end])
                    ->where(function ($q) use ($excludedStatuses) {
                        $q->whereNotIn(DB::raw('LOWER(COALESCE(orders.order_status, ""))'), $excludedStatuses)
                          ->orWhereNull('orders.order_status');
                    })
                    ->sum("{$itemsTable}.tax_amount");
            }

            $binNumber = $branch->bin_number ?? ($branch->bin ?? '—');

            $result[] = (object) [
                'id'          => $branch->id,
                'branch_name' => $branch->name,
                'bin_number'  => $binNumber,
                'total_tax'   => round($totalTax, 2),
                'total_sales' => round($totalSales, 2),
                'order_count' => $orderCount,
            ];
        }

        // Sort descending by total_tax
        usort($result, fn($a, $b) => $b->total_tax <=> $a->total_tax);

        return $result;
    }

    /**
     * Get top products generating tax in the given period (Schema-Safe & GroupBy Clean).
     */
    protected function getTopTaxProducts(string $startDate, string $endDate, ?int $branchId = null): array
    {
        $itemsTable = $this->resolveItemsTable();
        if (!$itemsTable || !Schema::hasTable('orders') || !Schema::hasColumn($itemsTable, 'tax_amount')) {
            return [];
        }

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        $excludedStatuses = ['cancelled', 'cancel', 'failed', 'incomplete', 'returned', 'return'];

        $hasProductTitle = Schema::hasColumn($itemsTable, 'product_title');
        $hasTitle = Schema::hasColumn($itemsTable, 'title');
        $hasName = Schema::hasColumn($itemsTable, 'name');
        $titleCol = $hasProductTitle ? "{$itemsTable}.product_title" : ($hasTitle ? "{$itemsTable}.title" : ($hasName ? "{$itemsTable}.name" : "'Product'"));

        $hasQty = Schema::hasColumn($itemsTable, 'quantity') ? "{$itemsTable}.quantity" : (Schema::hasColumn($itemsTable, 'qty') ? "{$itemsTable}.qty" : '1');
        $hasTaxRate = Schema::hasColumn($itemsTable, 'tax_rate');
        $rateExpr = $hasTaxRate ? "AVG(COALESCE({$itemsTable}.tax_rate, 0))" : '0';

        $query = DB::table($itemsTable)
            ->join('orders', "{$itemsTable}.order_id", '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where(function ($q) use ($excludedStatuses) {
                $q->whereNotIn(DB::raw('LOWER(COALESCE(orders.order_status, ""))'), $excludedStatuses)
                  ->orWhereNull('orders.order_status');
            });

        if ($branchId && Schema::hasColumn('orders', 'branch_id')) {
            $query->where('orders.branch_id', $branchId);
        }

        return $query->select(
                "{$itemsTable}.product_id",
                DB::raw("MAX(COALESCE({$titleCol}, 'Product')) as product_title"),
                DB::raw("SUM(COALESCE({$itemsTable}.tax_amount, 0)) as total_tax_collected"),
                DB::raw("SUM(COALESCE({$hasQty}, 1)) as total_qty"),
                DB::raw("{$rateExpr} as avg_tax_rate")
            )
            ->groupBy("{$itemsTable}.product_id")
            ->having('total_tax_collected', '>', 0)
            ->orderByDesc('total_tax_collected')
            ->take(6)
            ->get()
            ->toArray();
    }

    /**
     * Resolve date range based on preset keys or custom inputs.
     */
    protected function resolveDateRange(string $period, ?string $customStart = null, ?string $customEnd = null): array
    {
        $now = Carbon::now();

        if (!empty($customStart) && !empty($customEnd)) {
            return [
                'start' => Carbon::parse($customStart)->toDateString(),
                'end'   => Carbon::parse($customEnd)->toDateString(),
            ];
        }

        return match ($period) {
            'all', 'all_time' => [
                'start' => Carbon::parse('2024-01-01')->toDateString(),
                'end'   => $now->copy()->addDay()->toDateString(),
            ],
            'today'          => ['start' => $now->toDateString(), 'end' => $now->toDateString()],
            'yesterday'      => ['start' => $now->copy()->subDay()->toDateString(), 'end' => $now->copy()->subDay()->toDateString()],
            'last_7_days'    => ['start' => $now->copy()->subDays(6)->toDateString(), 'end' => $now->toDateString()],
            'this_week'      => ['start' => $now->copy()->startOfWeek()->toDateString(), 'end' => $now->copy()->endOfWeek()->toDateString()],
            'last_week', 'previous_week' => ['start' => $now->copy()->subWeek()->startOfWeek()->toDateString(), 'end' => $now->copy()->subWeek()->endOfWeek()->toDateString()],
            'last_30_days'   => ['start' => $now->copy()->subDays(29)->toDateString(), 'end' => $now->toDateString()],
            'this_month'     => ['start' => $now->copy()->startOfMonth()->toDateString(), 'end' => $now->copy()->endOfMonth()->toDateString()],
            'last_month', 'previous_month' => ['start' => $now->copy()->subMonth()->startOfMonth()->toDateString(), 'end' => $now->copy()->subMonth()->endOfMonth()->toDateString()],
            'this_year'      => ['start' => $now->copy()->startOfYear()->toDateString(), 'end' => $now->copy()->endOfYear()->toDateString()],
            'custom'         => [
                'start' => !empty($customStart) ? Carbon::parse($customStart)->toDateString() : $now->copy()->subDays(29)->toDateString(),
                'end'   => !empty($customEnd) ? Carbon::parse($customEnd)->toDateString() : $now->toDateString(),
            ],
            default          => ['start' => $now->copy()->startOfMonth()->toDateString(), 'end' => $now->copy()->endOfMonth()->toDateString()],
        };
    }
}