<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CourierService;
use App\Services\PathaoCourierService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CourierManagementController extends Controller
{
    /**
     * Customer name o phone column dynamic expression builder (Fail-safe for any orders table schema)
     */
    protected function getCustomerSelectExpressions(): array
    {
        $nameCols = array_values(array_filter(
            ['shipping_name', 'customer_name', 'name', 'billing_name'],
            fn($col) => Schema::hasColumn('orders', $col)
        ));
        $nameExpr = !empty($nameCols)
            ? 'COALESCE(' . implode(', ', array_map(fn($c) => "orders.{$c}", $nameCols)) . ", 'Customer')"
            : "'Customer'";

        $phoneCols = array_values(array_filter(
            ['shipping_phone', 'customer_phone', 'phone', 'mobile'],
            fn($col) => Schema::hasColumn('orders', $col)
        ));
        $phoneExpr = !empty($phoneCols)
            ? 'COALESCE(' . implode(', ', array_map(fn($c) => "orders.{$c}", $phoneCols)) . ", 'N/A')"
            : "'N/A'";

        $addrCols = array_values(array_filter(
            ['shipping_address', 'customer_address', 'address', 'billing_address'],
            fn($col) => Schema::hasColumn('orders', $col)
        ));
        $addrExpr = !empty($addrCols)
            ? 'COALESCE(' . implode(', ', array_map(fn($c) => "orders.{$c}", $addrCols)) . ", 'N/A')"
            : "'N/A'";

        $invoiceCols = array_values(array_filter(
            ['invoice_no', 'order_number', 'id'],
            fn($col) => Schema::hasColumn('orders', $col)
        ));
        $invoiceExpr = !empty($invoiceCols)
            ? 'COALESCE(' . implode(', ', array_map(fn($c) => "orders.{$c}", $invoiceCols)) . ', courier_bookings.order_id)'
            : 'courier_bookings.order_id';

        return [
            'name'    => DB::raw("{$nameExpr} as shipping_name"),
            'phone'   => DB::raw("{$phoneExpr} as shipping_phone"),
            'address' => DB::raw("{$addrExpr} as shipping_address"),
            'invoice' => DB::raw("{$invoiceExpr} as invoice_no"),
        ];
    }

    /**
     * 1. Courier Central Dashboard (KPIs & Financial Summary)
     */
    public function dashboard(Request $request)
    {
        $bookingsQuery = DB::table('courier_bookings');

        if ($request->filled('date_filter')) {
            switch ($request->date_filter) {
                case 'today':
                    $bookingsQuery->whereDate('created_at', Carbon::today());
                    break;
                case 'this_week':
                    $bookingsQuery->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'this_month':
                    $bookingsQuery->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                    break;
                case 'custom':
                    if ($request->filled('start_date')) {
                        $bookingsQuery->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
                    }
                    if ($request->filled('end_date')) {
                        $bookingsQuery->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
                    }
                    break;
            }
        }

        $stats = [
            'total_bookings'      => (clone $bookingsQuery)->count(),
            'in_transit'          => (clone $bookingsQuery)->whereIn('delivery_status', ['in_transit', 'shipped', 'picked'])->count(),
            'delivered'           => (clone $bookingsQuery)->whereIn('delivery_status', ['delivered', 'completed'])->count(),
            'returned'            => (clone $bookingsQuery)->whereIn('delivery_status', ['returned', 'return'])->count(),
            'cancelled'           => (clone $bookingsQuery)->where('delivery_status', 'cancelled')->count(),
            'total_cod'           => (float) (clone $bookingsQuery)->sum('cod_amount'),
            'settled_cod'         => (float) (clone $bookingsQuery)->sum('settled_amount'),
            'unsettled_cod'       => max(0, (float)(clone $bookingsQuery)->sum('cod_amount') - (float)(clone $bookingsQuery)->sum('settled_amount')),
            'total_courier_cost'  => (float) (clone $bookingsQuery)->sum('courier_cost'),
            'courier_cost_paid'   => (float) (clone $bookingsQuery)->sum('courier_cost_paid'),
            'courier_cost_due'    => (float) (clone $bookingsQuery)->sum('courier_cost_due'),
        ];

        $courierBreakdown = (clone $bookingsQuery)
            ->select(
                'courier_name',
                DB::raw('COUNT(*) as total_count'),
                DB::raw("SUM(CASE WHEN LOWER(delivery_status) IN ('delivered', 'completed') THEN 1 ELSE 0 END) as delivered_count"),
                DB::raw("SUM(CASE WHEN LOWER(delivery_status) IN ('returned', 'return') THEN 1 ELSE 0 END) as returned_count"),
                DB::raw('SUM(cod_amount) as total_cod'),
                DB::raw('SUM(courier_cost) as total_cost')
            )
            ->groupBy('courier_name')
            ->get();

        $custExpr = $this->getCustomerSelectExpressions();

        $recentBookings = DB::table('courier_bookings')
            ->join('orders', 'courier_bookings.order_id', '=', 'orders.id')
            ->select(
                'courier_bookings.*',
                $custExpr['invoice'],
                $custExpr['name'],
                $custExpr['phone']
            )
            ->latest('courier_bookings.id')
            ->take(10)
            ->get();

        return view('admin.courier.dashboard', compact('stats', 'courierBreakdown', 'recentBookings'));
    }

    /**
     * 2. Shokol Courier Order Talika (Advanced Filters, Pagination & Summary)
     */
    public function index(Request $request)
    {
        $custExpr = $this->getCustomerSelectExpressions();

        $query = DB::table('courier_bookings')
            ->join('orders', 'courier_bookings.order_id', '=', 'orders.id')
            ->select(
                'courier_bookings.*',
                $custExpr['invoice'],
                $custExpr['name'],
                $custExpr['phone'],
                $custExpr['address'],
                DB::raw('COALESCE(orders.grand_total, 0) as order_grand_total'),
                DB::raw("COALESCE(orders.order_status, 'pending') as original_order_status")
            );

        if (Schema::hasColumn('orders', 'order_source')) {
            $query->addSelect('orders.order_source');
        } else {
            $query->addSelect(DB::raw("'online' as order_source"));
        }

        // 1. Search Filter
        if ($request->filled('search')) {
            $s = trim($request->search);
            $cleanTerm = ltrim($s, '#');

            $query->where(function ($q) use ($s, $cleanTerm) {
                $q->where('courier_bookings.tracking_code', 'like', "%{$s}%")
                  ->orWhere('courier_bookings.consignment_id', 'like', "%{$s}%")
                  ->orWhere('orders.id', $cleanTerm);

                if (Schema::hasColumn('orders', 'invoice_no')) {
                    $q->orWhere('orders.invoice_no', 'like', "%{$cleanTerm}%");
                }
                if (Schema::hasColumn('orders', 'order_number')) {
                    $q->orWhere('order_number', 'like', "%{$cleanTerm}%");
                }

                foreach (['shipping_phone', 'customer_phone', 'phone', 'mobile'] as $col) {
                    if (Schema::hasColumn('orders', $col)) {
                        $q->orWhere("orders.{$col}", 'like', "%{$s}%");
                    }
                }

                foreach (['shipping_name', 'customer_name', 'name', 'billing_name'] as $col) {
                    if (Schema::hasColumn('orders', $col)) {
                        $q->orWhere("orders.{$col}", 'like', "%{$s}%");
                    }
                }

                if (Schema::hasColumn('orders', 'user_id')) {
                    $q->orWhereIn('orders.user_id', function ($sub) use ($s) {
                        $sub->select('id')->from('users')
                            ->where('name', 'like', "%{$s}%")
                            ->orWhere('phone', 'like', "%{$s}%")
                            ->orWhere('email', 'like', "%{$s}%");
                    });
                }
            });
        }

        // 2. Courier Provider Filter
        if ($request->filled('courier') && $request->courier !== 'all') {
            $query->whereRaw('LOWER(courier_bookings.courier_name) = ?', [strtolower($request->courier)]);
        }

        // 3. Delivery / Booking Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            $stFilter = strtolower($request->status);
            $query->where(function ($sq) use ($stFilter) {
                $sq->where('courier_bookings.booking_status', $stFilter)
                   ->orWhere('courier_bookings.delivery_status', $stFilter);
            });
        }

        // 4. COD Settlement Status Filter
        if ($request->filled('cod_status') && $request->cod_status !== 'all') {
            $query->where('courier_bookings.cod_settlement_status', strtolower($request->cod_status));
        }

        // 5. Courier Cost Status Filter (Paid / Due)
        if ($request->filled('cost_status') && $request->cost_status !== 'all') {
            if ($request->cost_status === 'due') {
                $query->where('courier_bookings.courier_cost_due', '>', 0);
            } elseif ($request->cost_status === 'paid') {
                $query->where('courier_bookings.courier_cost_due', '<=', 0);
            }
        }

        // 6. Channel Filter (Online vs POS)
        if ($request->filled('order_source') && $request->order_source !== 'all') {
            $src = strtolower($request->order_source);
            if (Schema::hasColumn('orders', 'order_source')) {
                if ($src === 'pos') {
                    $query->where('orders.order_source', 'pos');
                } else {
                    $query->where(function ($sq) {
                        $sq->whereNull('orders.order_source')->orWhere('orders.order_source', '!=', 'pos');
                    });
                }
            }
        }

        // 7. Date Filtering
        if ($request->filled('date_filter')) {
            switch ($request->date_filter) {
                case 'today':
                    $query->whereDate('courier_bookings.created_at', Carbon::today());
                    break;
                case 'yesterday':
                    $query->whereDate('courier_bookings.created_at', Carbon::yesterday());
                    break;
                case '7_days':
                    $query->where('courier_bookings.created_at', '>=', Carbon::now()->subDays(7)->startOfDay());
                    break;
                case '15_days':
                    $query->where('courier_bookings.created_at', '>=', Carbon::now()->subDays(15)->startOfDay());
                    break;
                case 'this_month':
                    $query->whereBetween('courier_bookings.created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                    break;
                case 'last_month':
                    $query->whereBetween('courier_bookings.created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                    break;
                case 'custom':
                    if ($request->filled('start_date')) {
                        $query->where('courier_bookings.created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
                    }
                    if ($request->filled('end_date')) {
                        $query->where('courier_bookings.created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
                    }
                    break;
            }
        }

        // 8. Sorting
        $sortBy = $request->get('sort_by', 'latest');
        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('courier_bookings.id', 'asc');
                break;
            case 'cod_high':
                $query->orderByDesc('courier_bookings.cod_amount');
                break;
            case 'cod_low':
                $query->orderBy('courier_bookings.cod_amount', 'asc');
                break;
            case 'weight_high':
                $query->orderByDesc('courier_bookings.parcel_weight');
                break;
            case 'latest':
            default:
                $query->orderBy('courier_bookings.id', 'desc');
                break;
        }

        $summaryQuery = clone $query;
        $totalCodSum     = (float) $summaryQuery->sum('courier_bookings.cod_amount');
        $totalCostSum    = (float) (clone $query)->sum('courier_bookings.courier_cost');
        $totalSettledSum = (float) (clone $query)->sum('courier_bookings.settled_amount');
        $totalDueCodSum  = max(0, $totalCodSum - $totalSettledSum);

        if ($request->get('export') === 'excel' || $request->get('export') === 'csv') {
            return $this->exportCourierBookingsToCsv($query, $totalCodSum, $totalCostSum, $totalSettledSum, $totalDueCodSum);
        }

        if ($request->get('export') === 'pdf' || $request->get('export') === 'print') {
            $bookings = $query->get();
            return view('admin.courier.export_pdf', compact('bookings', 'totalCodSum', 'totalCostSum', 'totalSettledSum', 'totalDueCodSum', 'request'));
        }

        $perPageInput = $request->input('per_page', 20);
        $perPage = ($perPageInput === 'all') ? 100000 : max(5, (int)$perPageInput);

        $bookings = $query->paginate($perPage)->withQueryString();

        $couriersList = DB::table('courier_settings')->pluck('courier_name')->unique()->values();

        if ($request->ajax()) {
            return response()->json([
                'success'         => true,
                'html'            => view('admin.courier.partials.courier_order_rows', compact('bookings'))->render(),
                'pagination_html' => ($perPageInput === 'all' || !$bookings->hasPages()) ? '' : $bookings->links('pagination::bootstrap-5')->render(),
                'total_count'     => $bookings->total(),
                'summary'         => [
                    'total_cod'     => number_format($totalCodSum, 2),
                    'total_cost'    => number_format($totalCostSum, 2),
                    'total_settled' => number_format($totalSettledSum, 2),
                    'due_cod'       => number_format($totalDueCodSum, 2),
                ]
            ]);
        }

        return view('admin.courier.index', compact('bookings', 'totalCodSum', 'totalCostSum', 'totalSettledSum', 'totalDueCodSum', 'perPage', 'couriersList'));
    }

    /**
     * 3. Notun Courier Booking Form View
     */
    public function createBooking(Request $request)
    {
        $selectedOrderId = $request->input('order_id');
        $order = null;
        $orderItems = collect();

        if ($selectedOrderId) {
            $order = DB::table('orders')->where('id', $selectedOrderId)->first();
            if ($order) {
                $userName = null;
                $userPhone = null;
                $userAddress = null;

                if (!empty($order->user_id) && Schema::hasTable('users')) {
                    $u = DB::table('users')->where('id', $order->user_id)->first();
                    if ($u) {
                        $userName = $u->name ?? null;
                        $userPhone = $u->phone ?? null;
                        $userAddress = $u->bio ?: ($u->address ?? null);
                    }
                }

                $order->shipping_name = $order->shipping_name ?? ($order->customer_name ?? ($order->name ?? ($userName ?? 'Customer')));
                $order->shipping_phone = $order->shipping_phone ?? ($order->customer_phone ?? ($order->phone ?? ($userPhone ?? '')));
                $order->shipping_address = $order->shipping_address ?? ($order->customer_address ?? ($order->address ?? ($userAddress ?? '')));

                $itemsTable = Schema::hasTable('order_items') ? 'order_items' : (Schema::hasTable('order_details') ? 'order_details' : null);
                if ($itemsTable && Schema::hasTable('products')) {
                    $orderItems = DB::table($itemsTable)
                        ->leftJoin('products', "{$itemsTable}.product_id", '=', 'products.id')
                        ->where("{$itemsTable}.order_id", $order->id)
                        ->select(
                            'products.title',
                            DB::raw('COALESCE(products.weight, 0.5) as weight'),
                            DB::raw("COALESCE({$itemsTable}.quantity, 1) as quantity"),
                            DB::raw("COALESCE({$itemsTable}.price, {$itemsTable}.unit_price, products.sale_price, products.regular_price, 0) as price")
                        )
                        ->get();
                }
            }
        }

        $couriers = DB::table('courier_settings')->where('is_active', true)->orderBy('courier_name', 'asc')->get();

        return view('admin.courier.booking', compact('order', 'orderItems', 'couriers'));
    }

    /**
     * 4. Booking-er jonno Order Live Search (Delivered o Already Booked Order Filtered Out)
     */
    public function searchOrderForBooking(Request $request)
    {
        try {
            $q = trim((string) $request->input('q', ''));
            $clean = ltrim($q, '#');

            $query = DB::table('orders');

            if (Schema::hasColumn('orders', 'order_status')) {
                $query->whereNotIn(DB::raw('LOWER(orders.order_status)'), ['delivered', 'cancelled', 'returned']);
            }

            if (Schema::hasTable('courier_bookings')) {
                $bookedOrderIds = DB::table('courier_bookings')
                    ->whereNotIn('booking_status', ['cancelled'])
                    ->pluck('order_id')
                    ->toArray();

                if (!empty($bookedOrderIds)) {
                    $query->whereNotIn('orders.id', $bookedOrderIds);
                }
            }

            $hasUserId = Schema::hasColumn('orders', 'user_id');
            if ($hasUserId && Schema::hasTable('users')) {
                $query->leftJoin('users', 'orders.user_id', '=', 'users.id');
            }

            if (!empty($q)) {
                $query->where(function ($sub) use ($q, $clean, $hasUserId) {
                    $sub->where('orders.id', 'like', "%{$clean}%");

                    if (Schema::hasColumn('orders', 'invoice_no')) {
                        $sub->orWhere('orders.invoice_no', 'like', "%{$clean}%");
                    }
                    if (Schema::hasColumn('orders', 'order_number')) {
                        $sub->orWhere('orders.order_number', 'like', "%{$clean}%");
                    }

                    foreach (['shipping_phone', 'customer_phone', 'phone', 'mobile'] as $col) {
                        if (Schema::hasColumn('orders', $col)) {
                            $sub->orWhere("orders.{$col}", 'like', "%{$q}%");
                        }
                    }

                    foreach (['shipping_name', 'customer_name', 'name', 'billing_name'] as $col) {
                        if (Schema::hasColumn('orders', $col)) {
                            $sub->orWhere("orders.{$col}", 'like', "%{$q}%");
                        }
                    }

                    if ($hasUserId && Schema::hasTable('users')) {
                        $sub->orWhere('users.name', 'like', "%{$q}%")
                            ->orWhere('users.phone', 'like', "%{$q}%")
                            ->orWhere('users.email', 'like', "%{$q}%");
                    }
                });
            }

            $nameCols = array_values(array_filter(
                ['shipping_name', 'customer_name', 'name', 'billing_name'],
                fn($col) => Schema::hasColumn('orders', $col)
            ));
            $nameParts = array_map(fn($c) => "orders.{$c}", $nameCols);
            if ($hasUserId && Schema::hasTable('users')) {
                $nameParts[] = 'users.name';
            }
            $safeNameExpr = !empty($nameParts) ? 'COALESCE(' . implode(', ', $nameParts) . ", 'Customer')" : "'Customer'";

            $phoneCols = array_values(array_filter(
                ['shipping_phone', 'customer_phone', 'phone', 'mobile'],
                fn($col) => Schema::hasColumn('orders', $col)
            ));
            $phoneParts = array_map(fn($c) => "orders.{$c}", $phoneCols);
            if ($hasUserId && Schema::hasTable('users')) {
                $phoneParts[] = 'users.phone';
            }
            $safePhoneExpr = !empty($phoneParts) ? 'COALESCE(' . implode(', ', $phoneParts) . ", 'N/A')" : "'N/A'";

            $invoiceCols = array_values(array_filter(
                ['invoice_no', 'order_number', 'id'],
                fn($col) => Schema::hasColumn('orders', $col)
            ));
            $invoiceExpr = !empty($invoiceCols)
                ? 'COALESCE(' . implode(', ', array_map(fn($c) => "orders.{$c}", $invoiceCols)) . ', orders.id)'
                : 'orders.id';

            $totalCol = Schema::hasColumn('orders', 'grand_total') 
                ? 'orders.grand_total' 
                : (Schema::hasColumn('orders', 'total_amount') ? 'orders.total_amount' : 'orders.id');

            $orders = $query
                ->select(
                    'orders.id',
                    DB::raw("{$invoiceExpr} as invoice_no"),
                    DB::raw("{$safeNameExpr} as customer_name"),
                    DB::raw("{$safePhoneExpr} as customer_phone"),
                    DB::raw("COALESCE({$totalCol}, 0) as grand_total")
                )
                ->latest('orders.id')
                ->take(15)
                ->get();

            return response()->json($orders);
        } catch (\Throwable $e) {
            Log::error('Courier searchOrderForBooking Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 5. Order Detail Fetch (Customer details, Package items, weight, COD & shipping cost auto-sync)
     */
    public function getOrderDetailsForBooking($id)
    {
        try {
            $order = DB::table('orders')->where('id', $id)->first();
            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Order not found'], 404);
            }

            if (in_array(strtolower($order->order_status ?? ''), ['delivered', 'completed'])) {
                return response()->json([
                    'success' => false, 
                    'message' => 'This order is already marked as DELIVERED and cannot be booked again.'
                ], 422);
            }

            $userName = null;
            $userPhone = null;
            $userAddress = null;

            if (!empty($order->user_id) && Schema::hasTable('users')) {
                $u = DB::table('users')->where('id', $order->user_id)->first();
                if ($u) {
                    $userName = $u->name ?? null;
                    $userPhone = $u->phone ?? null;
                    $userAddress = $u->bio ?: ($u->address ?? null);
                }
            }

            $order->customer_name = $order->shipping_name ?? ($order->customer_name ?? ($order->name ?? ($order->billing_name ?? ($userName ?? 'Customer'))));
            $order->customer_phone = $order->shipping_phone ?? ($order->customer_phone ?? ($order->phone ?? ($order->mobile ?? ($userPhone ?? ''))));
            $order->customer_address = $order->shipping_address ?? ($order->customer_address ?? ($order->address ?? ($order->billing_address ?? ($userAddress ?? ''))));

            $itemsTable = Schema::hasTable('order_items') ? 'order_items' : (Schema::hasTable('order_details') ? 'order_details' : null);
            $items = collect();
            $totalWeight = 0;

            if ($itemsTable && Schema::hasTable('products')) {
                $itemsQuery = DB::table($itemsTable)
                    ->leftJoin('products', "{$itemsTable}.product_id", '=', 'products.id')
                    ->where("{$itemsTable}.order_id", $id);

                $hasWeight = Schema::hasColumn('products', 'weight');
                $weightCol = $hasWeight ? "COALESCE(products.weight, 0.5)" : "0.5";
                $qtyCol = Schema::hasColumn($itemsTable, 'quantity') ? "COALESCE({$itemsTable}.quantity, 1)" : "1";

                $priceCol = Schema::hasColumn($itemsTable, 'price') 
                    ? "{$itemsTable}.price" 
                    : (Schema::hasColumn($itemsTable, 'unit_price') ? "{$itemsTable}.unit_price" : "COALESCE(products.sale_price, products.regular_price, 0)");

                $selectCols = [
                    'products.title',
                    DB::raw("{$weightCol} as weight"),
                    DB::raw("{$qtyCol} as quantity"),
                    DB::raw("COALESCE({$priceCol}, 0) as price"),
                ];

                if (Schema::hasColumn('products', 'product_code')) {
                    $selectCols[] = 'products.product_code';
                } else {
                    $selectCols[] = DB::raw("NULL as product_code");
                }

                if (Schema::hasTable('product_variants') && Schema::hasColumn($itemsTable, 'variant_id')) {
                    $itemsQuery->leftJoin('product_variants', "{$itemsTable}.variant_id", '=', 'product_variants.id');
                    if (Schema::hasColumn('product_variants', 'color')) {
                        $selectCols[] = 'product_variants.color as pv_color';
                    }
                    if (Schema::hasColumn('product_variants', 'size')) {
                        $selectCols[] = 'product_variants.size as pv_size';
                    }
                    if (Schema::hasColumn('product_variants', 'sku')) {
                        $selectCols[] = 'product_variants.sku as pv_sku';
                    }
                }

                $items = $itemsQuery->select($selectCols)->get();

                foreach ($items as $item) {
                    $w = (float)($item->weight ?: 0.5);
                    $totalWeight += ($w * (int)$item->quantity);
                }
            }

            $paidAmount = (float)($order->paid_amount ?? 0);
            $grandTotal = (float)($order->grand_total ?? ($order->total_amount ?? 0));
            $codAmount  = max(0, $grandTotal - $paidAmount);

            $deliveryCharge = 0;
            foreach (['shipping_charge', 'delivery_charge', 'shipping_cost'] as $scCol) {
                if (Schema::hasColumn('orders', $scCol) && !empty($order->$scCol)) {
                    $deliveryCharge = (float) $order->$scCol;
                    break;
                }
            }

            return response()->json([
                'success'         => true,
                'order'           => $order,
                'items'           => $items,
                'total_weight'    => max(0.5, round($totalWeight, 2)),
                'cod_amount'      => $codAmount,
                'delivery_charge' => $deliveryCharge,
            ]);
        } catch (\Throwable $e) {
            Log::error('Courier getOrderDetailsForBooking Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Courier Hub o Phone number memory / auto-fetch
     */
    public function getCourierInfoAjax($name)
    {
        $decodedName = urldecode($name);
        $setting = DB::table('courier_settings')->where('courier_name', $decodedName)->first();

        $lastBooking = DB::table('courier_bookings')
            ->where('courier_name', $decodedName)
            ->whereNotNull('remarks')
            ->latest('id')
            ->first();

        $defaultBranch = $setting->api_url ?? '';
        $defaultPhone = $setting->api_key ?? '';

        if ($lastBooking && !empty($lastBooking->remarks)) {
            if (preg_match('/Hub:\s*([^|]+)/i', $lastBooking->remarks, $mBranch)) {
                $defaultBranch = trim($mBranch[1]);
            }
            if (preg_match('/Phone:\s*([0-9+\s-]+)/i', $lastBooking->remarks, $mPhone)) {
                $defaultPhone = trim($mPhone[1]);
            }
        }

        return response()->json([
            'success'        => true,
            'courier_name'   => $decodedName,
            'default_branch' => $defaultBranch,
            'default_phone'  => $defaultPhone,
        ]);
    }

    /**
     * 6. Courier Booking Execution o Courier Cost Auto-Fallback
     */
    public function storeBooking(Request $request)
    {
        $request->validate([
            'order_id'      => 'required|exists:orders,id',
            'courier_name'  => 'required|string',
            'parcel_weight' => 'required|numeric|min:0.1',
            'courier_cost'  => 'nullable|numeric|min:0',
        ]);

        $order = DB::table('orders')->where('id', $request->order_id)->first();

        if (in_array(strtolower($order->order_status ?? ''), ['delivered', 'completed', 'cancelled'])) {
            return back()->withInput()->with('error', 'Delivered ba Cancelled order courier-e book kora jabe na.');
        }

        $selectedCourier = trim($request->courier_name);

        if ($selectedCourier === 'manual_new' || $request->filled('new_manual_courier_name')) {
            $newCourierName = trim($request->new_manual_courier_name ?: 'Custom Courier');
            $selectedCourier = $newCourierName;

            $exists = DB::table('courier_settings')->where('courier_name', $newCourierName)->first();
            if (!$exists) {
                $slugCode = Str::slug($newCourierName) ?: 'manual-' . rand(100, 999);

                $insertData = [
                    'courier_name' => $newCourierName,
                    'api_url'      => $request->manual_branch ?? null,
                    'api_key'      => $request->manual_phone ?? null,
                    'secret_key'   => null,
                    'is_active'    => true,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];

                if (Schema::hasColumn('courier_settings', 'courier_code')) {
                    $insertData['courier_code'] = $slugCode;
                }

                DB::table('courier_settings')->insert($insertData);
            }
        } else {
            $setting = DB::table('courier_settings')->where('courier_name', $selectedCourier)->first();
            if ($setting && !str_contains(strtolower($selectedCourier), 'steadfast') && !str_contains(strtolower($selectedCourier), 'pathao') && !str_contains(strtolower($selectedCourier), 'redx')) {
                if ($request->filled('manual_branch') || $request->filled('manual_phone')) {
                    DB::table('courier_settings')->where('id', $setting->id)->update([
                        'api_url'    => $request->manual_branch ?: $setting->api_url,
                        'api_key'    => $request->manual_phone ?: $setting->api_key,
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        if ($request->filled('recipient_name')) {
            $orderUpdateData = [];
            foreach (['shipping_name', 'customer_name', 'name'] as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $orderUpdateData[$col] = $request->recipient_name;
                }
            }
            if ($request->filled('recipient_phone')) {
                foreach (['shipping_phone', 'customer_phone', 'phone'] as $col) {
                    if (Schema::hasColumn('orders', $col)) {
                        $orderUpdateData[$col] = $request->recipient_phone;
                    }
                }
            }
            if ($request->filled('recipient_address')) {
                foreach (['shipping_address', 'customer_address', 'address'] as $col) {
                    if (Schema::hasColumn('orders', $col)) {
                        $orderUpdateData[$col] = $request->recipient_address;
                    }
                }
            }

            if (!empty($orderUpdateData)) {
                DB::table('orders')->where('id', $order->id)->update($orderUpdateData);
                $order = DB::table('orders')->where('id', $request->order_id)->first();
            }
        }

        $courierCost = (float) ($request->courier_cost ?? 0);
        if ($courierCost <= 0) {
            foreach (['shipping_charge', 'delivery_charge', 'shipping_cost'] as $scCol) {
                if (Schema::hasColumn('orders', $scCol) && !empty($order->$scCol)) {
                    $courierCost = (float) $order->$scCol;
                    break;
                }
            }
        }

        $hubRemark = [];
        if ($request->filled('manual_branch')) $hubRemark[] = "Hub: " . trim($request->manual_branch);
        if ($request->filled('manual_phone'))  $hubRemark[] = "Phone: " . trim($request->manual_phone);
        if ($request->filled('note'))          $hubRemark[] = trim($request->note);
        $fullRemarks = !empty($hubRemark) ? implode(' | ', $hubRemark) : null;

        $params = [
            'weight'            => (float) $request->parcel_weight,
            'service_type'      => $request->service_type ?? 'regular',
            'courier_cost'      => $courierCost,
            'courier_cost_paid' => (float) ($request->courier_cost_paid ?? 0),
            'tracking_code'     => $request->tracking_code ?? null,
            'note'              => $fullRemarks,
        ];

        $result = CourierService::bookCourier($order, $selectedCourier, $params);

        if ($result['success']) {
            return redirect()->route('admin.courier.index')->with('success', $result['message']);
        }

        return back()->withInput()->with('error', $result['message'] ?? 'Failed to book courier parcel.');
    }

    /**
     * 7. Courier COD o Cost Settlement Ledger View (With Financial KPIs & Advanced Filters)
     */
    public function settlements(Request $request)
    {
        $custExpr = $this->getCustomerSelectExpressions();

        $query = DB::table('courier_bookings')
            ->join('orders', 'courier_bookings.order_id', '=', 'orders.id')
            ->select(
                'courier_bookings.*',
                $custExpr['invoice'],
                $custExpr['name'],
                $custExpr['phone'],
                'orders.grand_total as order_grand_total',
                'orders.payment_status as order_payment_status',
                'orders.paid_amount as order_paid_amount',
                'orders.due_amount as order_due_amount'
            );

        // 1. Search (Tracking, Consignment, Invoice, Phone, Customer)
        if ($request->filled('search')) {
            $s = trim($request->search);
            $cleanTerm = ltrim($s, '#');

            $query->where(function ($q) use ($s, $cleanTerm) {
                $q->where('courier_bookings.tracking_code', 'like', "%{$s}%")
                  ->orWhere('courier_bookings.consignment_id', 'like', "%{$s}%")
                  ->orWhere('orders.id', $cleanTerm);

                if (Schema::hasColumn('orders', 'invoice_no')) {
                    $q->orWhere('orders.invoice_no', 'like', "%{$cleanTerm}%");
                }
                foreach (['shipping_phone', 'customer_phone', 'phone'] as $col) {
                    if (Schema::hasColumn('orders', $col)) {
                        $q->orWhere("orders.{$col}", 'like', "%{$s}%");
                    }
                }
                foreach (['shipping_name', 'customer_name', 'name'] as $col) {
                    if (Schema::hasColumn('orders', $col)) {
                        $q->orWhere("orders.{$col}", 'like', "%{$s}%");
                    }
                }
            });
        }

        // 2. Courier Filter
        if ($request->filled('courier') && $request->courier !== 'all') {
            $query->whereRaw('LOWER(courier_bookings.courier_name) = ?', [strtolower($request->courier)]);
        }

        // 3. Status Filter (Settled, Unsettled, Partial)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('courier_bookings.cod_settlement_status', strtolower($request->status));
        }

        // 4. Date Filtering (Handover or Settlement date)
        if ($request->filled('date_filter')) {
            switch ($request->date_filter) {
                case 'today':
                    $query->whereDate('courier_bookings.created_at', Carbon::today());
                    break;
                case 'this_week':
                    $query->whereBetween('courier_bookings.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereBetween('courier_bookings.created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                    break;
                case 'custom':
                    if ($request->filled('start_date')) {
                        $query->where('courier_bookings.created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
                    }
                    if ($request->filled('end_date')) {
                        $query->where('courier_bookings.created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
                    }
                    break;
            }
        }

        // Settlement KPI Calculation
        $summaryQuery = clone $query;
        $totalExpectedCod = (float) $summaryQuery->sum('courier_bookings.cod_amount');
        $totalSettledCod  = (float) (clone $query)->sum('courier_bookings.settled_amount');
        $totalDueCod      = max(0.00, $totalExpectedCod - $totalSettledCod);
        $totalCourierCost = (float) (clone $query)->sum('courier_bookings.courier_cost');

        $statusCounts = (clone $query)
            ->select([
                DB::raw('COUNT(*) as count_all'),
                DB::raw("SUM(CASE WHEN LOWER(courier_bookings.cod_settlement_status) = 'settled' THEN 1 ELSE 0 END) as count_settled"),
                DB::raw("SUM(CASE WHEN LOWER(courier_bookings.cod_settlement_status) IN ('unsettled', 'pending') THEN 1 ELSE 0 END) as count_unsettled"),
                DB::raw("SUM(CASE WHEN LOWER(courier_bookings.cod_settlement_status) = 'partial' THEN 1 ELSE 0 END) as count_partial"),
            ])
            ->first();

        $counts = [
            'all'       => (int) ($statusCounts->count_all ?? 0),
            'settled'   => (int) ($statusCounts->count_settled ?? 0),
            'unsettled' => (int) ($statusCounts->count_unsettled ?? 0),
            'partial'   => (int) ($statusCounts->count_partial ?? 0),
        ];

        $couriersList = DB::table('courier_settings')->pluck('courier_name')->unique()->values();

        $bookings = $query->latest('courier_bookings.id')->paginate(20)->withQueryString();

        return view('admin.courier.settlement', compact(
            'bookings',
            'counts',
            'totalExpectedCod',
            'totalSettledCod',
            'totalDueCod',
            'totalCourierCost',
            'couriersList'
        ));
    }

    /**
     * 8. COD Settlement Update (Delegates to CourierService::processCodSettlement for 100% Ledger Accuracy)
     */
    public function updateSettlement(Request $request, $id)
    {
        $request->validate([
            'settled_amount'  => 'required|numeric|min:0',
            'settlement_date' => 'required|date',
            'cod_charge'      => 'nullable|numeric|min:0',
            'courier_cost'    => 'nullable|numeric|min:0',
            'return_cost'     => 'nullable|numeric|min:0',
            'payment_method'  => 'nullable|string|max:100',
            'transaction_id'  => 'nullable|string|max:100',
            'remarks'         => 'nullable|string|max:500',
        ]);

        $result = CourierService::processCodSettlement((int)$id, $request->all(), auth()->id());

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'status'  => strtoupper($result['status']),
                'order_paid' => number_format($result['order_paid'] ?? 0, 2),
                'order_due'  => number_format($result['order_due'] ?? 0, 2),
            ]);
        }

        return response()->json(['success' => false, 'message' => $result['message']], 422);
    }

    /**
     * 9. Live Tracking Status Sync (Steadfast / Pathao API)
     */
    public function syncStatus(Request $request, $id)
    {
        $booking = DB::table('courier_bookings')->where('id', $id)->first();
        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Booking record not found'], 404);
        }

        $courierName = strtolower($booking->courier_name);
        $newStatus = null;

        if (str_contains($courierName, 'steadfast') && $booking->tracking_code) {
            $trackRes = CourierService::trackSteadfast($booking->tracking_code);
            if ($trackRes['success']) {
                $newStatus = $trackRes['delivery_status'];
            }
        } elseif (str_contains($courierName, 'pathao') && $booking->consignment_id) {
            $trackRes = PathaoCourierService::trackOrder($booking->consignment_id);
            if ($trackRes['success']) {
                $newStatus = $trackRes['delivery_status'];
            }
        }

        if ($newStatus) {
            CourierService::syncStatusWithOrder($id, $newStatus);
            return response()->json([
                'success' => true,
                'status'  => strtoupper(str_replace('_', ' ', $newStatus)),
                'message' => 'Tracking status updated to ' . strtoupper($newStatus)
            ]);
        }

        $isManual = !str_contains($courierName, 'steadfast') && !str_contains($courierName, 'pathao');

        return response()->json([
            'success'        => false,
            'is_manual'      => $isManual,
            'current_status' => strtoupper($booking->delivery_status),
            'message'        => $isManual 
                ? 'Manual Courier has no live API. Use "Change Status" to update status manually.' 
                : 'Current status: ' . strtoupper($booking->delivery_status) . ' (No API change detected).'
        ]);
    }

    /**
     * 1-Click Status & Delivery Cost Update (Realtime sync with Orders & Financial ledger)
     */
    public function updateManualStatus(Request $request, $id)
    {
        $request->validate([
            'delivery_status'   => 'required|string',
            'courier_cost'      => 'nullable|numeric|min:0',
            'courier_cost_paid' => 'nullable|numeric|min:0',
        ]);

        $booking = DB::table('courier_bookings')->where('id', $id)->first();
        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Booking record not found'], 404);
        }

        $newStatus = strtolower(trim($request->delivery_status));

        // Update courier booking & auto-sync to orders table
        CourierService::syncStatusWithOrder($id, $newStatus);

        $updateData = ['updated_at' => now()];

        if ($request->has('courier_cost') && $request->courier_cost !== null) {
            $cost = (float) $request->courier_cost;
            $paid = (float) ($request->courier_cost_paid ?? ($booking->courier_cost_paid ?? 0));
            $updateData['courier_cost']      = $cost;
            $updateData['courier_cost_paid'] = $paid;
            $updateData['courier_cost_due']  = max(0, $cost - $paid);
        }

        DB::table('courier_bookings')->where('id', $id)->update($updateData);

        return response()->json([
            'success' => true,
            'status'  => strtoupper(str_replace('_', ' ', $newStatus)),
            'message' => 'Courier status & delivery cost updated successfully!'
        ]);
    }

    /**
     * 10. Single Courier Booking Delete (Fixed 100% JSON Response - No 302 Redirect!)
     * IMPORTANT: Deletes ONLY from courier_bookings. The original customer order in orders table is 100% UNTOUCHED!
     */
    public function destroy(Request $request, $id)
    {
        $booking = DB::table('courier_bookings')->where('id', $id)->first();
        if (!$booking) {
            return response()->json([
                'success' => false, 
                'message' => 'Courier record not found.'
            ], 404);
        }

        // Delete ONLY from courier_bookings table (Orders table remains completely intact!)
        DB::table('courier_bookings')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Courier record #' . $id . ' permanently deleted without affecting the order.'
        ]);
    }

    /**
     * 11. Bulk Action Handler (Bulk Delete, Bulk Delivery Status, & Bulk COD Settlement)
     * IMPORTANT: Bulk deleting removes ONLY courier_bookings records. Orders table is 100% SAFE!
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'booking_ids'   => 'required|array',
            'booking_ids.*' => 'integer',
            'action_type'   => 'required|string',
        ]);

        $bookingIds = $request->booking_ids;

        // 1. Bulk Delete Action
        if ($request->action_type === 'delete') {
            DB::table('courier_bookings')->whereIn('id', $bookingIds)->delete();

            return response()->json([
                'success' => true,
                'message' => count($bookingIds) . ' courier booking record(s) deleted successfully. Orders list remains completely unaffected!'
            ]);
        }

        // 2. Bulk Settlement Action (Settles full COD amount & syncs orders/ledger)
        if ($request->action_type === 'bulk_settle' || $request->action_type === 'settle_selected') {
            $successCount = 0;
            $adminId = auth()->id();

            foreach ($bookingIds as $bId) {
                $bRecord = DB::table('courier_bookings')->where('id', $bId)->first();
                if (!$bRecord) continue;

                $res = CourierService::processCodSettlement($bId, [
                    'settled_amount'  => (float) $bRecord->cod_amount,
                    'settlement_date' => now(),
                    'remarks'         => 'Bulk COD Settlement executed by Admin',
                ], $adminId);

                if ($res['success']) {
                    $successCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "{$successCount} courier parcel(s) successfully marked as SETTLED & synced with accounting ledger!"
            ]);
        }

        // 3. Bulk Delivery Status Update Action
        if (str_starts_with($request->action_type, 'status_')) {
            $newStatus = strtolower(str_replace('status_', '', $request->action_type));

            // Sync with each booking & order safely
            foreach ($bookingIds as $bId) {
                CourierService::syncStatusWithOrder($bId, $newStatus);
            }

            return response()->json([
                'success' => true,
                'message' => count($bookingIds) . ' courier parcel(s) updated to ' . strtoupper(str_replace('_', ' ', $newStatus))
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid bulk action requested.'], 422);
    }

    /**
     * 12. Shompurno Courier Booking Ledger CSV Export
     */
    protected function exportCourierBookingsToCsv($query, $totalCod, $totalCost, $totalSettled, $dueCod)
    {
        $records = $query->latest('courier_bookings.id')->get();
        $filename = "Courier_Orders_Audit_" . date('Y_m_d_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Booking #',
            'Invoice No',
            'Order Date',
            'Customer Name',
            'Phone',
            'Address',
            'Courier Provider',
            'Consignment / CN ID',
            'Tracking Code',
            'Weight (KG)',
            'Order Amount (BDT)',
            'COD Amount (BDT)',
            'Settled Amount (BDT)',
            'Settlement Status',
            'Courier Cost (BDT)',
            'Delivery Status',
            'Handover Date'
        ];

        $callback = function () use ($records, $columns, $totalCod, $totalCost, $totalSettled, $dueCod) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            foreach ($records as $row) {
                fputcsv($file, [
                    '#' . $row->id,
                    $row->invoice_no ?? $row->order_id,
                    date('d M Y, h:i A', strtotime($row->created_at)),
                    $row->shipping_name ?? 'Customer',
                    $row->shipping_phone ?? 'N/A',
                    $row->shipping_address ?? 'N/A',
                    strtoupper($row->courier_name),
                    $row->consignment_id ?? '—',
                    $row->tracking_code,
                    $row->parcel_weight,
                    number_format((float)$row->order_amount, 2, '.', ''),
                    number_format((float)$row->cod_amount, 2, '.', ''),
                    number_format((float)$row->settled_amount, 2, '.', ''),
                    strtoupper($row->cod_settlement_status),
                    number_format((float)$row->courier_cost, 2, '.', ''),
                    strtoupper($row->delivery_status),
                    $row->handover_date ? date('d M Y', strtotime($row->handover_date)) : '—',
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, [
                'SUMMARY TOTALS', '', '', '', '', '', '', '', '', '',
                'TOTAL COD: ' . number_format($totalCod, 2, '.', ''),
                'SETTLED: ' . number_format($totalSettled, 2, '.', ''),
                'DUE COD: ' . number_format($dueCod, 2, '.', ''),
                'TOTAL COURIER COST: ' . number_format($totalCost, 2, '.', '')
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}