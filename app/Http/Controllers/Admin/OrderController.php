<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\DigitalProductDeliveryMail;
use App\Services\DigitalProductService;
use App\Services\CourierService;
use App\Services\PaymentTransactionService;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * 1. Online o POS order talika, live AJAX filtering, dynamic pagination o 1-click Excel/PDF export
     * (Status + Date + Source + Payment Status + Payment Method + Search)
     */
    public function index(Request $request)
    {
        $baseQuery = DB::table('orders');

        // 1. Order Source filter (All, Web/Online Order, POS Order, Manual Order)
        if ($request->filled('order_source') && $request->order_source !== 'all') {
            $source = strtolower($request->order_source);
            $hasOrderSource = Schema::hasColumn('orders', 'order_source');
            $hasOrderType   = Schema::hasColumn('orders', 'order_type');

            if ($source === 'pos') {
                $baseQuery->where(function ($q) use ($hasOrderSource, $hasOrderType) {
                    if ($hasOrderSource) {
                        $q->where('order_source', 'pos');
                    } elseif ($hasOrderType) {
                        $q->where('order_type', 'pos');
                    }
                });
            } elseif ($source === 'manual') {
                $baseQuery->where(function ($q) use ($hasOrderSource, $hasOrderType) {
                    if ($hasOrderSource) {
                        $q->whereIn('order_source', ['manual', 'facebook', 'whatsapp', 'phone', 'walk_in', 'other']);
                    } elseif ($hasOrderType) {
                        $q->where('order_type', 'manual');
                    }
                });
            } elseif ($source === 'web' || $source === 'online') {
                $baseQuery->where(function ($q) use ($hasOrderSource, $hasOrderType) {
                    if ($hasOrderSource) {
                        $q->where(function ($sq) {
                            $sq->whereNull('order_source')
                               ->orWhere('order_source', 'web')
                               ->orWhere('order_source', 'online');
                        });
                    } elseif ($hasOrderType) {
                        $q->where(function ($sq) {
                            $sq->whereNull('order_type')
                               ->orWhere('order_type', 'online');
                        });
                    }
                });
            }
        }

        // 2. Payment Status filter (All, Paid Orders, Unpaid Orders)
        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $pStatus = strtolower($request->payment_status);
            if ($pStatus === 'paid') {
                $baseQuery->where('payment_status', 'paid');
            } elseif ($pStatus === 'unpaid') {
                $baseQuery->where(function ($q) {
                    $q->where('payment_status', 'unpaid')
                      ->orWhereNull('payment_status')
                      ->orWhere('payment_status', '!=', 'paid');
                });
            } elseif (in_array($pStatus, ['partially_paid', 'partial', 'pending_verification', 'refunded'])) {
                $baseQuery->where('payment_status', $pStatus);
            }
        }

        // 3. Payment Method filter (COD, bKash, Nagad, Rocket, Card, etc.)
        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $methodVal = strtolower(trim($request->payment_method));
            $baseQuery->where(function ($q) use ($methodVal) {
                $q->whereRaw('LOWER(COALESCE(payment_method, "")) = ?', [$methodVal])
                  ->orWhere('payment_method', 'LIKE', "%{$methodVal}%");
            });
        }

        // 4. Advance Date Range Filtering (Preset, Specific Single Date, Custom Range)
        if ($request->filled('date_filter') || $request->filled('date_range')) {
            $dateRange = $request->input('date_filter') ?: $request->input('date_range');

            switch ($dateRange) {
                case 'today':
                    $baseQuery->whereDate('created_at', Carbon::today());
                    break;

                case 'single_date':
                    if ($request->filled('single_date')) {
                        $baseQuery->whereDate('created_at', Carbon::parse($request->single_date)->toDateString());
                    }
                    break;

                case '3_days':
                    $baseQuery->where('created_at', '>=', Carbon::now()->subDays(3)->startOfDay());
                    break;

                case '7_days':
                    $baseQuery->where('created_at', '>=', Carbon::now()->subDays(7)->startOfDay());
                    break;

                case '15_days':
                    $baseQuery->where('created_at', '>=', Carbon::now()->subDays(15)->startOfDay());
                    break;

                case '1_month':
                    $baseQuery->where('created_at', '>=', Carbon::now()->subDays(30)->startOfDay());
                    break;

                case '3_months':
                    $baseQuery->where('created_at', '>=', Carbon::now()->subDays(90)->startOfDay());
                    break;

                case '6_months':
                    $baseQuery->where('created_at', '>=', Carbon::now()->subDays(180)->startOfDay());
                    break;

                case 'custom':
                    if ($request->filled('start_date')) {
                        $baseQuery->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
                    }
                    if ($request->filled('end_date')) {
                        $baseQuery->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
                    }
                    break;
            }
        } elseif ($request->filled('single_date')) {
            $baseQuery->whereDate('created_at', Carbon::parse($request->single_date)->toDateString());
        } elseif ($request->filled('start_date') || $request->filled('end_date')) {
            if ($request->filled('start_date')) {
                $baseQuery->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
            }
            if ($request->filled('end_date')) {
                $baseQuery->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
            }
        }

        // 5. Live Search Filter
        if ($request->filled('search')) {
            $searchTerm = trim($request->search);
            $cleanTerm  = ltrim($searchTerm, '#');

            $baseQuery->where(function ($q) use ($searchTerm, $cleanTerm) {
                $q->where('id', 'like', "%{$cleanTerm}%");
                if (Schema::hasColumn('orders', 'invoice_no')) {
                    $q->orWhere('invoice_no', 'like', "%{$cleanTerm}%");
                }
                if (Schema::hasColumn('orders', 'order_number')) {
                    $q->orWhere('order_number', 'like', "%{$cleanTerm}%");
                }

                foreach (['shipping_name', 'customer_name', 'name', 'billing_name'] as $col) {
                    if (Schema::hasColumn('orders', $col)) {
                        $q->orWhere($col, 'like', "%{$searchTerm}%");
                    }
                }

                foreach (['shipping_phone', 'customer_phone', 'phone', 'mobile'] as $col) {
                    if (Schema::hasColumn('orders', $col)) {
                        $q->orWhere($col, 'like', "%{$searchTerm}%");
                    }
                }

                foreach (['shipping_email', 'customer_email', 'email'] as $col) {
                    if (Schema::hasColumn('orders', $col)) {
                        $q->orWhere($col, 'like', "%{$searchTerm}%");
                    }
                }

                if (Schema::hasColumn('orders', 'user_id')) {
                    $q->orWhereIn('user_id', function ($sub) use ($searchTerm) {
                        $sub->select('id')->from('users')
                            ->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('email', 'like', "%{$searchTerm}%")
                            ->orWhere('phone', 'like', "%{$searchTerm}%");
                    });
                }
            });
        }

        // 6. Dynamic Filtered Status Tabs Counts
        $statusStats = (clone $baseQuery)
            ->selectRaw("
                COUNT(*) as total_all,
                SUM(CASE WHEN LOWER(order_status) = 'pending' THEN 1 ELSE 0 END) as count_pending,
                SUM(CASE WHEN LOWER(order_status) = 'received' THEN 1 ELSE 0 END) as count_received,
                SUM(CASE WHEN LOWER(order_status) = 'confirmed' THEN 1 ELSE 0 END) as count_confirmed,
                SUM(CASE WHEN LOWER(order_status) IN ('processing', 'packed') THEN 1 ELSE 0 END) as count_processing,
                SUM(CASE WHEN LOWER(order_status) = 'shipped' THEN 1 ELSE 0 END) as count_shipped,
                SUM(CASE WHEN LOWER(order_status) IN ('attempt_to_deliver', 'attempted', 'attempt_delivery') THEN 1 ELSE 0 END) as count_attempt_to_deliver,
                SUM(CASE WHEN LOWER(order_status) = 'delivered' THEN 1 ELSE 0 END) as count_delivered,
                SUM(CASE WHEN LOWER(order_status) IN ('returned', 'return') THEN 1 ELSE 0 END) as count_returned,
                SUM(CASE WHEN LOWER(order_status) IN ('exchange', 'exchanged', 'exchange_requested') THEN 1 ELSE 0 END) as count_exchange,
                SUM(CASE WHEN LOWER(order_status) = 'cancelled' THEN 1 ELSE 0 END) as count_cancelled
            ")
            ->first();

        $counts = [
            'all'                => (int) ($statusStats->total_all ?? 0),
            'pending'            => (int) ($statusStats->count_pending ?? 0),
            'received'           => (int) ($statusStats->count_received ?? 0),
            'confirmed'          => (int) ($statusStats->count_confirmed ?? 0),
            'processing'         => (int) ($statusStats->count_processing ?? 0),
            'shipped'            => (int) ($statusStats->count_shipped ?? 0),
            'attempt_to_deliver' => (int) ($statusStats->count_attempt_to_deliver ?? 0),
            'delivered'          => (int) ($statusStats->count_delivered ?? 0),
            'returned'           => (int) ($statusStats->count_returned ?? 0),
            'exchange'           => (int) ($statusStats->count_exchange ?? 0),
            'cancelled'          => (int) ($statusStats->count_cancelled ?? 0),
        ];

        // 7. Status Filter Apply
        $query = clone $baseQuery;
        if ($request->filled('status') && $request->status !== 'all') {
            $statusVal = strtolower(trim($request->status));
            if ($statusVal === 'exchange') {
                $query->whereIn('order_status', ['exchange', 'exchanged', 'exchange_requested']);
            } elseif ($statusVal === 'returned') {
                $query->whereIn('order_status', ['returned', 'return']);
            } elseif ($statusVal === 'processing') {
                $query->whereIn('order_status', ['processing', 'packed']);
            } elseif ($statusVal === 'attempt_to_deliver') {
                $query->whereIn('order_status', ['attempt_to_deliver', 'attempted', 'attempt_delivery']);
            } else {
                $query->where('order_status', $statusVal);
            }
        }

        // 8. Filter Summary
        $summaryQuery   = clone $query;
        $totalAmountSum = (float) $summaryQuery->sum('grand_total');

        if (Schema::hasColumn('orders', 'paid_amount')) {
            $totalPaidSum = (float) (clone $query)->sum(DB::raw("
                CASE 
                    WHEN LOWER(COALESCE(payment_status, '')) = 'paid' THEN COALESCE(NULLIF(paid_amount, 0), grand_total, 0)
                    WHEN COALESCE(paid_amount, 0) > 0 THEN paid_amount
                    ELSE 0 
                END
            "));
        } else {
            $totalPaidSum = (float) (clone $query)->sum(DB::raw("
                CASE 
                    WHEN LOWER(COALESCE(payment_status, '')) = 'paid' THEN COALESCE(grand_total, 0)
                    ELSE 0 
                END
            "));
        }

        $totalDueSum = max(0, $totalAmountSum - $totalPaidSum);

        if ($request->get('export') === 'excel' || $request->get('export') === 'csv') {
            return $this->exportOrdersToExcel($query, $totalAmountSum, $totalPaidSum, $totalDueSum);
        }

        if ($request->get('export') === 'pdf' || $request->get('export') === 'print') {
            $exportOrders = $query->latest('id')->get();
            return view('admin.orders.export_pdf', compact('exportOrders', 'totalAmountSum', 'totalPaidSum', 'totalDueSum', 'request'));
        }

        $perPageInput = $request->input('per_page', 20);
        $perPage = ($perPageInput === 'all') ? 100000 : max(5, (int) $perPageInput);

        $orders = $query->latest('id')->paginate($perPage)->withQueryString();

        $availablePaymentMethods = collect(['cod', 'cash', 'bkash', 'nagad', 'rocket']);
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'payment_method')) {
            $existingMethods = DB::table('orders')
                ->whereNotNull('payment_method')
                ->where('payment_method', '!=', '')
                ->distinct()
                ->pluck('payment_method');
            $availablePaymentMethods = $availablePaymentMethods->merge($existingMethods);
        }
        if (Schema::hasTable('payment_gateways')) {
            $gwMethods = DB::table('payment_gateways')->pluck('name');
            $availablePaymentMethods = $availablePaymentMethods->merge($gwMethods);
        }
        if (Schema::hasTable('manual_payment_methods')) {
            $manualMethods = DB::table('manual_payment_methods')->pluck('name');
            $availablePaymentMethods = $availablePaymentMethods->merge($manualMethods);
        }
        $availablePaymentMethods = $availablePaymentMethods
            ->map(fn($m) => strtolower(trim($m)))
            ->filter()
            ->unique()
            ->values();

        if ($request->ajax()) {
            if ($request->header('Accept') === 'application/json' || $request->get('format') === 'json') {
                return response()->json([
                    'success'         => true,
                    'html'            => view('admin.orders.partials.order_rows', compact('orders'))->render(),
                    'pagination_html' => ($perPageInput === 'all' || !$orders->hasPages()) ? '' : $orders->links('pagination::bootstrap-5')->render(),
                    'total_count'     => $orders->total(),
                    'entries_text'    => "Showing " . ($orders->firstItem() ?? 0) . " to " . ($orders->lastItem() ?? 0) . " of " . $orders->total() . " entries",
                    'counts'          => $counts,
                    'summary'         => [
                        'total_amount' => number_format($totalAmountSum, 2),
                        'total_paid'   => number_format($totalPaidSum, 2),
                        'total_due'    => number_format($totalDueSum, 2),
                    ]
                ]);
            }

            return view('admin.orders.partials.order_rows', compact('orders'))->render();
        }

        return view('admin.orders.index', compact(
            'orders',
            'counts',
            'totalAmountSum',
            'totalPaidSum',
            'totalDueSum',
            'perPage',
            'availablePaymentMethods'
        ));
    }

    protected function exportOrdersToExcel($query, $totalAmount, $totalPaid, $totalDue)
    {
        $orders = $query->latest('id')->get();
        $filename = "orders_export_" . date('Y_m_d_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Order #',
            'Invoice No',
            'Order Date',
            'Customer Name',
            'Phone',
            'Address',
            'Channel (Source)',
            'Courier',
            'Tracking ID',
            'Payment Method',
            'Payment Status',
            'Order Status',
            'Product Subtotal',
            'Delivery Charge',
            'Order Total (BDT)',
            'Paid Amount (BDT)',
            'Due Amount (BDT)'
        ];

        $callback = function () use ($orders, $columns, $totalAmount, $totalPaid, $totalDue) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            foreach ($orders as $order) {
                $pStatus = strtolower($order->payment_status ?? '');
                $orderPaid = ($pStatus === 'paid')
                    ? (!empty($order->paid_amount) && $order->paid_amount > 0 ? $order->paid_amount : ($order->grand_total ?? 0))
                    : ($order->paid_amount ?? 0);
                $orderDue  = max(0, ($order->grand_total ?? 0) - $orderPaid);
                $pSubtotal = (float)($order->product_subtotal ?? ($order->subtotal ?? 0));
                $dCharge   = (float)($order->delivery_charge ?? ($order->shipping_charge ?? 0));

                fputcsv($file, [
                    '#' . $order->id,
                    $order->invoice_no ?? $order->id,
                    date('d M Y, h:i A', strtotime($order->created_at)),
                    $order->shipping_name ?? ($order->customer_name ?? ($order->name ?? 'Guest Customer')),
                    $order->shipping_phone ?? ($order->customer_phone ?? ($order->phone ?? 'N/A')),
                    $order->shipping_address ?? ($order->customer_address ?? ($order->address ?? '')),
                    strtoupper($order->order_source ?? ($order->order_type ?? 'WEB')),
                    $order->courier_name ?? 'Not Assigned',
                    $order->courier_tracking_id ?? ($order->tracking_code ?? ($order->parcel_id ?? '')),
                    strtoupper($order->payment_method ?? 'COD'),
                    strtoupper($order->payment_status ?? 'UNPAID'),
                    strtoupper(str_replace('_', ' ', $order->order_status ?? 'PENDING')),
                    number_format($pSubtotal, 2, '.', ''),
                    number_format($dCharge, 2, '.', ''),
                    number_format($order->grand_total ?? 0, 2, '.', ''),
                    number_format($orderPaid, 2, '.', ''),
                    number_format($orderDue, 2, '.', ''),
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, [
                'SUMMARY TOTALS', '', '', '', '', '', '', '', '', '', '', '', '',
                'TOTAL STATS:',
                number_format($totalAmount, 2, '.', '') . ' BDT',
                number_format($totalPaid, 2, '.', '') . ' BDT',
                number_format($totalDue, 2, '.', '') . ' BDT',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * 2. Specific Order Detail View
     */
    public function show($id)
    {
        $order = DB::table('orders')->where('id', $id)->first();
        if (!$order) {
            return redirect()->route('admin.order.online')->with('error', 'Order not found.');
        }

        $itemsQuery = DB::table('order_items')->where('order_items.order_id', $order->id);

        if (Schema::hasTable('product_variants') && Schema::hasColumn('order_items', 'variant_id')) {
            $selectFields = ['order_items.*'];

            if (Schema::hasColumn('product_variants', 'color')) {
                $selectFields[] = 'product_variants.color as pv_color';
            }
            if (Schema::hasColumn('product_variants', 'size')) {
                $selectFields[] = 'product_variants.size as pv_size';
            }
            if (Schema::hasColumn('product_variants', 'product_code')) {
                $selectFields[] = 'product_variants.product_code as pv_code';
            }
            if (Schema::hasColumn('product_variants', 'sku')) {
                $selectFields[] = 'product_variants.sku as pv_sku';
            }

            $itemsQuery->leftJoin('product_variants', 'order_items.variant_id', '=', 'product_variants.id')
                ->select($selectFields);
        }

        $orderItems = $itemsQuery->get();

        $customerHistory = [
            'total_orders'  => !empty($order->user_id) ? DB::table('orders')->where('user_id', $order->user_id)->count() : 1,
            'total_revenue' => !empty($order->user_id) ? DB::table('orders')->where('user_id', $order->user_id)->sum('grand_total') : ($order->grand_total ?? 0),
        ];

        $couriers = Schema::hasTable('courier_settings')
            ? DB::table('courier_settings')->where('is_active', true)->get()
            : collect([
                (object) ['courier_name' => 'Steadfast Courier', 'courier_code' => 'steadfast'],
                (object) ['courier_name' => 'Pathao Courier', 'courier_code' => 'pathao'],
            ]);

        return view('admin.orders.show', compact('order', 'orderItems', 'customerHistory', 'couriers'));
    }

    /**
     * 3. Status update, Stock Deduction/Restoration, Transaction Sync, Courier Sync & Delivery Emails
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'order_status'   => 'required|string|max:50',
            'admin_note'     => 'nullable|string|max:1000',
            'payment_status' => 'nullable|string|max:50',
            'transaction_id' => 'nullable|string|max:100',
        ]);

        $order = DB::table('orders')->where('id', $id)->first();
        if (!$order) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
            }
            return back()->with('error', 'Order not found.');
        }

        $currentOrderStatus   = strtolower($order->order_status ?? 'pending');
        $currentPaymentStatus = strtolower($order->payment_status ?? 'unpaid');
        $explicitPayInput     = $request->filled('payment_status') ? strtolower($request->payment_status) : null;
        $targetPaymentStatus  = $explicitPayInput ?: $currentPaymentStatus;
        $targetOrderStatus    = strtolower(trim($request->order_status));

        $orderItems = DB::table('order_items')->where('order_id', $order->id)->get();
        $hasPhysical = false;
        $hasDigital = false;

        foreach ($orderItems as $item) {
            $p = DB::table('products')->where('id', $item->product_id)->first();
            if ($p) {
                $pType = strtolower(trim((string)($p->product_type ?? '')));
                $isDig = !empty($p->is_virtual) 
                    || !empty($p->is_downloadable) 
                    || !empty($p->virtual) 
                    || !empty($p->downloadable)
                    || str_contains($pType, 'virtual') 
                    || str_contains($pType, 'download') 
                    || str_contains($pType, 'digital') 
                    || str_contains($pType, 'license');

                if ($isDig) {
                    $hasDigital = true;
                } else {
                    $hasPhysical = true;
                }
            } else {
                $hasPhysical = true;
            }
        }
        $isPureDigital = ($hasDigital && !$hasPhysical);

        $isCOD = in_array(strtolower(trim($order->payment_method ?? '')), ['cash on delivery', 'cod']);
        if ($targetOrderStatus === 'delivered' && !$explicitPayInput && $isCOD) {
            $targetPaymentStatus = $currentPaymentStatus;
        }

        $syncCheck = PaymentTransactionService::syncFromOrderUpdate(
            (int) $id,
            $targetOrderStatus,
            $targetPaymentStatus,
            $request->filled('transaction_id') ? trim($request->transaction_id) : null,
            $request->input('admin_note')
        );

        if (!$syncCheck['allowed']) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $syncCheck['message']], 422);
            }
            return back()->withInput()->with('error', $syncCheck['message']);
        }

        if ($isPureDigital && $targetPaymentStatus === 'paid') {
            if (in_array($targetOrderStatus, ['pending', 'received'])) {
                $targetOrderStatus = 'delivered';
            }
        }

        try {
            $packedStatuses = ['processing', 'packed', 'shipped', 'attempt_to_deliver', 'delivered'];
            if (in_array($targetOrderStatus, $packedStatuses) && empty($order->is_stock_deducted)) {
                InventoryService::deductOrderStockAtProcessing($order, auth()->id());
            }

            if ($targetOrderStatus === 'cancelled' && $currentOrderStatus !== 'cancelled') {
                $this->restoreOrderStock($order, 'cancelled', $request->admin_note ?? 'Order cancelled');
            }

            if (in_array($targetOrderStatus, ['returned', 'return']) && !in_array($currentOrderStatus, ['returned', 'return'])) {
                $this->restoreOrderStock($order, 'returned', $request->admin_note ?? 'Order returned');
            }
        } catch (\Throwable $stockEx) {
            Log::error("Stock transition failed for Order #{$id} to {$targetOrderStatus}: " . $stockEx->getMessage());
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $stockEx->getMessage()], 422);
            }
            return back()->withInput()->with('error', $stockEx->getMessage());
        }

        $updateData = [
            'order_status' => $targetOrderStatus,
            'updated_at'   => now(),
        ];

        if (Schema::hasColumn('orders', 'payment_status')) {
            $updateData['payment_status'] = $targetPaymentStatus;
        }

        if ($targetPaymentStatus === 'paid') {
            if (Schema::hasColumn('orders', 'paid_amount')) {
                $updateData['paid_amount'] = $order->grand_total ?? 0;
            }
            if (Schema::hasColumn('orders', 'due_amount')) {
                $updateData['due_amount'] = 0;
            }
        } elseif ($targetPaymentStatus === 'unpaid') {
            if (Schema::hasColumn('orders', 'paid_amount')) {
                $updateData['paid_amount'] = 0;
            }
            if (Schema::hasColumn('orders', 'due_amount')) {
                $updateData['due_amount'] = $order->grand_total ?? 0;
            }
        }

        if ($request->filled('admin_note') && Schema::hasColumn('orders', 'admin_note')) {
            $updateData['admin_note'] = $request->admin_note;
        }

        if ($request->filled('transaction_id') && Schema::hasColumn('orders', 'transaction_id')) {
            $updateData['transaction_id'] = trim($request->transaction_id);
        }

        DB::table('orders')->where('id', $id)->update($updateData);

        if (Schema::hasTable('courier_bookings')) {
            $booking = DB::table('courier_bookings')->where('order_id', $id)->first();
            if ($booking) {
                $cUpdate = ['updated_at' => now()];

                if (in_array($targetOrderStatus, ['delivered', 'completed'])) {
                    $cUpdate['delivery_status']  = 'delivered';
                    $cUpdate['booking_status']   = 'delivered';
                    $cUpdate['collected_amount'] = $booking->cod_amount;

                    if ($targetPaymentStatus === 'paid') {
                        $cUpdate['cod_settlement_status'] = 'settled';
                        $cUpdate['settled_amount']        = $booking->cod_amount;
                        $cUpdate['settlement_date']       = now();
                    }
                } elseif (in_array($targetOrderStatus, ['cancelled', 'failed'])) {
                    $cUpdate['delivery_status'] = 'cancelled';
                    $cUpdate['booking_status']  = 'cancelled';
                } elseif (in_array($targetOrderStatus, ['returned', 'return', 'return_requested'])) {
                    $cUpdate['delivery_status'] = 'returned';
                    $cUpdate['booking_status']  = 'returned';
                } elseif ($targetOrderStatus === 'attempt_to_deliver') {
                    $cUpdate['delivery_status'] = 'attempt_to_deliver';
                    $cUpdate['booking_status']  = 'attempt_to_deliver';
                } elseif (in_array($targetOrderStatus, ['shipped', 'confirmed', 'processing'])) {
                    if ($booking->delivery_status !== 'delivered') {
                        $cUpdate['delivery_status'] = 'in_transit';
                        $cUpdate['booking_status']  = 'in_transit';
                    }
                }

                if ($targetPaymentStatus === 'paid') {
                    $cUpdate['cod_settlement_status'] = 'settled';
                    $cUpdate['settled_amount']        = $booking->cod_amount;
                    if (empty($booking->settlement_date)) {
                        $cUpdate['settlement_date']   = now();
                    }
                }

                DB::table('courier_bookings')->where('id', $booking->id)->update($cUpdate);
            }
        }

        $updatedOrder = DB::table('orders')->where('id', $id)->first();

        $userEmail = $updatedOrder->customer_email ?? ($updatedOrder->shipping_email ?? ($updatedOrder->email ?? null));
        if (empty($userEmail) && !empty($updatedOrder->user_id)) {
            $u = DB::table('users')->where('id', $updatedOrder->user_id)->first();
            $userEmail = $u->email ?? null;
        }
        $userEmail = trim((string)$userEmail);
        $isValidEmail = !empty($userEmail) && strtolower($userEmail) !== 'n/a' && filter_var($userEmail, FILTER_VALIDATE_EMAIL);

        $digitalMailSent = false;

        if ($hasDigital && $targetPaymentStatus === 'paid') {
            try {
                DigitalProductService::fulfillDigitalOrder($id);
                $digitalDetails = DigitalProductService::getOrderDigitalDetails($id);

                $alreadyDelivered = Schema::hasColumn('orders', 'digital_delivered_at') && !empty($updatedOrder->digital_delivered_at);

                if ($isValidEmail && !$alreadyDelivered) {
                    Mail::to($userEmail)->send(new DigitalProductDeliveryMail($updatedOrder, $orderItems, $digitalDetails));
                    $digitalMailSent = true;

                    if (Schema::hasColumn('orders', 'digital_delivered_at')) {
                        DB::table('orders')->where('id', $id)->update(['digital_delivered_at' => now()]);
                    }
                }
            } catch (\Throwable $ex) {
                Log::error("Digital Delivery Processing Error for Order #{$id}: " . $ex->getMessage());
            }
        }

        if ($isValidEmail && !$digitalMailSent) {
            try {
                $mailData = [
                    'invoice_no'   => $updatedOrder->invoice_no ?? $updatedOrder->id,
                    'status_title' => strtoupper(str_replace('_', ' ', $targetOrderStatus)),
                    'customer'     => $updatedOrder->shipping_name ?? ($updatedOrder->customer_name ?? ($updatedOrder->name ?? 'Valued Customer')),
                    'grand_total'  => number_format($updatedOrder->grand_total ?? 0, 2),
                    'admin_note'   => $request->admin_note ?? null,
                ];

                Mail::send([], [], function ($message) use ($mailData, $userEmail) {
                    $message->to($userEmail)
                        ->subject("📦 Order Update: [#{$mailData['invoice_no']}] - {$mailData['status_title']}")
                        ->html("
                            <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #1e293b; max-width: 600px; margin: auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff;'>
                                <h2 style='color: #0f172a; margin-top: 0;'>Order Status Update</h2>
                                <p>Dear <strong>{$mailData['customer']}</strong>,</p>
                                <p>Your order <strong>#{$mailData['invoice_no']}</strong> status is now: <strong style='color: #0284c7;'>{$mailData['status_title']}</strong></p>
                                <p>Total Amount: <strong>{$mailData['grand_total']} BDT</strong></p>
                                " . ($mailData['admin_note'] ? "<p><strong>Note:</strong> {$mailData['admin_note']}</p>" : "") . "
                            </div>
                        ");
                });
            } catch (\Exception $e) {
                Log::error('Order status mail error: ' . $e->getMessage());
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success'        => true,
                'message'        => 'Order status updated to ' . strtoupper(str_replace('_', ' ', $targetOrderStatus)) . ($digitalMailSent ? ' & Digital Delivery email sent!' : ''),
                'order_status'   => $targetOrderStatus,
                'payment_status' => $targetPaymentStatus,
                'status_badge'   => strtoupper(str_replace('_', ' ', $targetOrderStatus)),
            ]);
        }

        return back()->with('success', 'Order updated successfully' . ($digitalMailSent ? ' & Digital Delivery email sent.' : '.'));
    }

    /**
     * 4. Courier Dispatch o Tracking Code Save (Automated API + Safe Fallback + Courier Booking Table Sync)
     */
    public function sendToCourier(Request $request, $id)
    {
        $request->validate([
            'courier'   => 'required|string',
            'parcel_id' => 'nullable|string|max:100',
            'note'      => 'nullable|string|max:500',
        ]);

        $order = DB::table('orders')->where('id', $id)->first();
        if (!$order) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
            }
            return back()->with('error', 'Order not found.');
        }

        if (empty($order->is_stock_deducted)) {
            try {
                InventoryService::deductOrderStockAtProcessing($order, auth()->id());
            } catch (\Throwable $stkEx) {
                Log::error("Stock deduction failed on dispatch for Order #{$id}: " . $stkEx->getMessage());
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => "Stock deduction failed: " . $stkEx->getMessage()], 422);
                }
                return back()->with('error', "Stock deduction failed: " . $stkEx->getMessage());
            }
        }

        $courierName = ucfirst(trim($request->courier));
        $trackingCode = trim($request->parcel_id ?? '');
        $consignmentId = null;
        $apiSuccessMsg = null;

        if (str_contains(strtolower($courierName), 'steadfast') && empty($trackingCode)) {
            $apiResult = CourierService::sendToSteadfast($order, $request->note);

            if ($apiResult['success']) {
                $trackingCode = $apiResult['tracking_code'];
                $consignmentId = $apiResult['consignment_id'] ?? null;
                $apiSuccessMsg = "Steadfast Consignment ID: {$apiResult['consignment_id']}";
            } elseif (!empty($apiResult['manual'])) {
                $trackingCode = 'STF-' . mt_rand(100000, 999999);
            } else {
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $apiResult['message']], 422);
                }
                return back()->with('error', $apiResult['message']);
            }
        }

        if (empty($trackingCode)) {
            $trackingCode = strtoupper(substr($courierName, 0, 3)) . '-' . mt_rand(100000, 999999);
        }

        $updateData = [
            'order_status' => 'shipped',
            'updated_at'   => now(),
        ];

        if (Schema::hasColumn('orders', 'courier_name')) {
            $updateData['courier_name'] = $courierName;
        }
        if (Schema::hasColumn('orders', 'courier_tracking_id')) {
            $updateData['courier_tracking_id'] = $trackingCode;
        }
        if (Schema::hasColumn('orders', 'parcel_id')) {
            $updateData['parcel_id'] = $trackingCode;
        }

        DB::table('orders')->where('id', $id)->update($updateData);

        if (Schema::hasTable('courier_bookings')) {
            CourierService::recordBookingEntry($order, $courierName, str_contains(strtolower($courierName), 'steadfast') ? 'api' : 'manual', [
                'tracking_code'  => $trackingCode,
                'consignment_id' => $consignmentId ?: $trackingCode,
                'delivery_status'=> 'in_transit',
                'booking_status' => 'in_transit',
                'remarks'        => $request->note ?? 'Dispatched from order management'
            ]);
        }

        $msg = "Order #{$id} dispatched to {$courierName}! Tracking Code: {$trackingCode}" . ($apiSuccessMsg ? " ({$apiSuccessMsg})" : "");

        if ($request->ajax()) {
            return response()->json([
                'success'       => true,
                'message'       => $msg,
                'tracking_code' => $trackingCode,
                'courier_name'  => $courierName,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * 5. Invoice Print View
     */
    public function printInvoice($id)
    {
        $order = DB::table('orders')->where('id', $id)->first();
        if (!$order) {
            abort(404, 'Order not found');
        }

        $orderItems = DB::table('order_items')->where('order_id', $order->id)->get();
        return view('admin.orders.invoice', compact('order', 'orderItems'));
    }

    /**
     * 6. Single Order Delete (Safely cleans stock & bookings)
     */
    public function destroy(Request $request, $id)
    {
        $order = DB::table('orders')->where('id', $id)->first();
        if ($order) {
            try {
                if (!in_array(strtolower($order->order_status ?? ''), ['cancelled', 'returned', 'return'])) {
                    $this->restoreOrderStock($order, 'cancelled', "Stock cancelled on permanent Order #{$id} deletion");
                }
            } catch (\Throwable $e) {
                Log::error("Stock cancellation on deletion failed for Order #{$id}: " . $e->getMessage());
            }

            DB::table('order_items')->where('order_id', $id)->delete();
            if (Schema::hasTable('courier_bookings')) {
                DB::table('courier_bookings')->where('order_id', $id)->delete();
            }
            DB::table('orders')->where('id', $id)->delete();
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Order #' . $id . ' permanently deleted.'
            ]);
        }

        return back()->with('success', 'Order deleted successfully.');
    }

    /**
     * 7. Bulk Action Handler (Includes Enterprise Processing, Restocking, and Cancellation)
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'order_ids'   => 'required|array',
            'order_ids.*' => 'integer',
            'action_type' => 'required|string',
        ]);

        $orderIds = $request->order_ids;

        if ($request->action_type === 'delete') {
            foreach ($orderIds as $oId) {
                $order = DB::table('orders')->where('id', $oId)->first();
                if ($order && !in_array(strtolower($order->order_status ?? ''), ['cancelled', 'returned', 'return'])) {
                    try {
                        $this->restoreOrderStock($order, 'cancelled', "Bulk deletion of Order #{$oId}");
                    } catch (\Throwable $e) {
                        Log::error("Bulk delete stock cancellation failed for Order #{$oId}: " . $e->getMessage());
                    }
                }
            }

            DB::table('order_items')->whereIn('order_id', $orderIds)->delete();
            if (Schema::hasTable('courier_bookings')) {
                DB::table('courier_bookings')->whereIn('order_id', $orderIds)->delete();
            }
            DB::table('orders')->whereIn('id', $orderIds)->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => count($orderIds) . ' orders permanently deleted.'
                ]);
            }
            return back()->with('success', count($orderIds) . ' orders deleted successfully.');
        }

        if (str_starts_with($request->action_type, 'status_')) {
            $newStatus = strtolower(str_replace('status_', '', $request->action_type));

            foreach ($orderIds as $oId) {
                $orderObj = DB::table('orders')->where('id', $oId)->first();
                if (!$orderObj) continue;

                $prevStatus = strtolower($orderObj->order_status ?? 'pending');

                try {
                    if (in_array($newStatus, ['processing', 'packed', 'shipped', 'delivered', 'attempt_to_deliver']) && empty($orderObj->is_stock_deducted)) {
                        InventoryService::deductOrderStockAtProcessing($orderObj, auth()->id());
                    }

                    if ($newStatus === 'cancelled' && $prevStatus !== 'cancelled') {
                        $this->restoreOrderStock($orderObj, 'cancelled', "Bulk status update to cancelled");
                    }

                    if (in_array($newStatus, ['returned', 'return']) && !in_array($prevStatus, ['returned', 'return'])) {
                        $this->restoreOrderStock($orderObj, 'returned', "Bulk status update to returned");
                    }
                } catch (\Throwable $stkEx) {
                    Log::error("Bulk stock update failed for Order #{$oId} to {$newStatus}: " . $stkEx->getMessage());
                }
            }

            $updateData = ['order_status' => $newStatus, 'updated_at' => now()];
            DB::table('orders')->whereIn('id', $orderIds)->update($updateData);

            if (Schema::hasTable('courier_bookings')) {
                foreach ($orderIds as $oId) {
                    $b = DB::table('courier_bookings')->where('order_id', $oId)->first();
                    if ($b) {
                        $bUp = ['updated_at' => now()];
                        if ($newStatus === 'delivered') {
                            $bUp['delivery_status']  = 'delivered';
                            $bUp['booking_status']   = 'delivered';
                            $bUp['collected_amount'] = $b->cod_amount;
                        } elseif ($newStatus === 'cancelled') {
                            $bUp['delivery_status'] = 'cancelled';
                            $bUp['booking_status']  = 'cancelled';
                        } elseif (in_array($newStatus, ['returned', 'return'])) {
                            $bUp['delivery_status'] = 'returned';
                            $bUp['booking_status']  = 'returned';
                        } elseif ($newStatus === 'attempt_to_deliver') {
                            $bUp['delivery_status'] = 'attempt_to_deliver';
                            $bUp['booking_status']  = 'attempt_to_deliver';
                        } elseif (in_array($newStatus, ['shipped', 'processing'])) {
                            $bUp['delivery_status'] = 'in_transit';
                            $bUp['booking_status']  = 'in_transit';
                        }
                        DB::table('courier_bookings')->where('id', $b->id)->update($bUp);
                    }
                }
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => count($orderIds) . ' orders updated to ' . strtoupper(str_replace('_', ' ', $newStatus))
                ]);
            }

            return back()->with('success', count($orderIds) . ' orders updated to ' . strtoupper($newStatus));
        }

        if ($request->ajax()) {
            return response()->json(['success' => false, 'message' => 'Invalid bulk action.'], 422);
        }

        return back()->with('error', 'Invalid action selected.');
    }

    /**
     * Restore stock for all items in an order upon cancellation or return.
     * Delegates to central enterprise InventoryService for strict consistency and idempotency.
     */
    protected function restoreOrderStock($order, string $newStatus, ?string $note = null): void
    {
        $status = strtolower($newStatus);

        if (in_array($status, ['cancelled', 'cancel'])) {
            InventoryService::cancelOrderStock($order, auth()->id(), $note ?? 'Order cancelled');
        } elseif (in_array($status, ['returned', 'return'])) {
            InventoryService::processOrderReturn($order, [], auth()->id(), $note ?? 'Order returned');
        }
    }

    /**
     * Re-deduct stock if a previously cancelled or returned order is reopened.
     */
    protected function reDeductOrderStock($order, string $newStatus): void
    {
        $refId = $order->invoice_no ?? (string)$order->id;
        $items = DB::table('order_items')->where('order_id', $order->id)->get();

        foreach ($items as $item) {
            $productId = (int) $item->product_id;
            $variantId = !empty($item->variant_id) ? (int)$item->variant_id : null;
            $qty = (int) ($item->quantity ?? ($item->qty ?? 1));

            if ($qty <= 0 || !$productId) {
                continue;
            }

            $p = DB::table('products')->where('id', $productId)->first();
            if ($p) {
                $pType = strtolower(trim((string)($p->product_type ?? '')));
                $isDig = !empty($p->is_virtual)
                    || !empty($p->is_downloadable)
                    || !empty($p->virtual)
                    || !empty($p->downloadable)
                    || str_contains($pType, 'virtual')
                    || str_contains($pType, 'download')
                    || str_contains($pType, 'digital')
                    || str_contains($pType, 'license');

                if ($isDig) {
                    continue;
                }
            }

            $reDeductToken = "REDEDUCT-{$refId}-{$newStatus}-{$productId}-" . ($variantId ?? 0);

            try {
                InventoryService::deductStock(
                    $productId,
                    $variantId,
                    $qty,
                    'order_reopen',
                    $refId,
                    (float) ($item->unit_price ?? ($item->price ?? 0)),
                    'admin_order_reopen',
                    auth()->id(),
                    "Stock re-deducted upon Order #{$refId} reopened to {$newStatus}",
                    $reDeductToken
                );
            } catch (\Throwable $e) {
                Log::error("Failed to re-deduct stock for Order #{$refId}, item #{$productId}: " . $e->getMessage());
            }
        }

        DB::table('orders')->where('id', $order->id)->update([
            'is_stock_deducted' => true,
            'is_stock_restored' => false,
            'updated_at'        => now(),
        ]);
    }

    /**
     * 8. Incomplete / Failed Orders List
     */
    public function incompleteOrders(Request $request)
    {
        $query = DB::table('orders')->where(function ($q) {
            $q->whereIn('order_status', ['incomplete', 'failed', 'cancelled'])
                ->orWhere('payment_status', 'failed');
        });

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($sub) use ($s) {
                $sub->where('id', 'like', "%{$s}%");
                if (Schema::hasColumn('orders', 'invoice_no')) {
                    $sub->orWhere('invoice_no', 'like', "%{$s}%");
                }
                foreach (['shipping_phone', 'customer_phone', 'phone'] as $col) {
                    if (Schema::hasColumn('orders', $col)) {
                        $sub->orWhere($col, 'like', "%{$s}%");
                    }
                }
            });
        }

        $orders = $query->latest('id')->paginate(20)->withQueryString();
        return view('admin.orders.incomplete', compact('orders'));
    }

    /**
     * 9. Return Orders List
     */
    public function returnOrders(Request $request)
    {
        $query = DB::table('orders')->whereIn('order_status', ['returned', 'return', 'return_requested']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($sub) use ($s) {
                $sub->where('id', 'like', "%{$s}%");
                if (Schema::hasColumn('orders', 'invoice_no')) {
                    $sub->orWhere('invoice_no', 'like', "%{$s}%");
                }
            });
        }

        $orders = $query->latest('id')->paginate(20)->withQueryString();
        return view('admin.orders.return', compact('orders'));
    }

    /**
     * 10. Exchange Orders List
     */
    public function exchangeOrders(Request $request)
    {
        $query = DB::table('orders')->whereIn('order_status', ['exchange', 'exchanged', 'exchange_requested']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($sub) use ($s) {
                $sub->where('id', 'like', "%{$s}%");
                if (Schema::hasColumn('orders', 'invoice_no')) {
                    $sub->orWhere('invoice_no', 'like', "%{$s}%");
                }
            });
        }

        $orders = $query->latest('id')->paginate(20)->withQueryString();
        return view('admin.orders.exchange', compact('orders'));
    }

    /**
     * 11. Fraud Checker (Internal Database + FraudBD Official API)
     */
    public function fraudChecker(Request $request)
    {
        $internalReport = null;
        $externalReport = null;
        $apiError = null;
        $searchedPhone = $request->input('phone');

        if ($request->filled('phone')) {
            $rawPhone = trim($searchedPhone);
            $cleanDigits = preg_replace('/[^0-9]/', '', $rawPhone);

            if (str_starts_with($cleanDigits, '880') && strlen($cleanDigits) === 13) {
                $cleanPhone = substr($cleanDigits, 2);
            } elseif (strlen($cleanDigits) === 10 && str_starts_with($cleanDigits, '1')) {
                $cleanPhone = '0' . $cleanDigits;
            } else {
                $cleanPhone = $cleanDigits;
            }

            $phoneCol = 'shipping_phone';
            if (!Schema::hasColumn('orders', 'shipping_phone')) {
                $phoneCol = Schema::hasColumn('orders', 'phone') ? 'phone' : 'customer_phone';
            }

            $totalOrders = DB::table('orders')->where($phoneCol, 'like', "%{$cleanPhone}%")->count();
            $deliveredOrders = DB::table('orders')->where($phoneCol, 'like', "%{$cleanPhone}%")->where('order_status', 'delivered')->count();
            $cancelledOrders = DB::table('orders')->where($phoneCol, 'like', "%{$cleanPhone}%")->whereIn('order_status', ['cancelled', 'returned', 'failed'])->count();

            $internalSuccessRate = $totalOrders > 0 ? round(($deliveredOrders / $totalOrders) * 100) : 0;

            $internalRisk = 'Good Record';
            $internalBadge = 'bg-success';
            if ($totalOrders > 0 && $internalSuccessRate < 50) {
                $internalRisk = 'High Cancellation Rate';
                $internalBadge = 'bg-danger';
            } elseif ($cancelledOrders >= 2 && $internalSuccessRate < 70) {
                $internalRisk = 'Medium Risk';
                $internalBadge = 'bg-warning text-dark';
            }

            $orderList = DB::table('orders')
                ->where($phoneCol, 'like', "%{$cleanPhone}%")
                ->latest('id')
                ->take(10)
                ->get();

            $internalReport = [
                'phone'        => $cleanPhone,
                'total_orders' => $totalOrders,
                'delivered'    => $deliveredOrders,
                'cancelled'    => $cancelledOrders,
                'success_rate' => $internalSuccessRate,
                'risk_level'   => $internalRisk,
                'badge_class'  => $internalBadge,
                'history'      => $orderList
            ];

            $fraudSetting = Schema::hasTable('fraud_check_settings')
                ? DB::table('fraud_check_settings')->first()
                : null;

            if ($fraudSetting && $fraudSetting->is_active && !empty($fraudSetting->api_key)) {
                try {
                    $apiUrl = trim($fraudSetting->api_url);
                    $apiKey = trim($fraudSetting->api_key);

                    $response = Http::withoutVerifying()
                        ->timeout(20)
                        ->withHeaders([
                            'api_key'      => $apiKey,
                            'Content-Type' => 'application/json',
                            'Accept'       => 'application/json',
                        ])
                        ->withBody(json_encode([
                            'phone_number' => (string) $cleanPhone
                        ]), 'application/json')
                        ->post($apiUrl);

                    if ($response->successful()) {
                        $resData = $response->json();

                        if (isset($resData['status']) && $resData['status'] === true && isset($resData['data'])) {
                            $externalReport = [
                                'totalSummary' => $resData['data']['totalSummary'] ?? null,
                                'summaries'    => $resData['data']['Summaries'] ?? [],
                            ];
                        } elseif (isset($resData['status']) && $resData['status'] === false) {
                            $apiError = $resData['message'] ?? 'Fraud check failed for this number.';
                        } else {
                            $externalReport = [
                                'totalSummary' => $resData['totalSummary'] ?? null,
                                'summaries'    => $resData['Summaries'] ?? [],
                            ];
                        }
                    } else {
                        $resError = $response->json();
                        $apiError = 'FraudBD Server Error (' . $response->status() . '): ' . ($resError['message'] ?? $response->body());
                    }
                } catch (\Exception $e) {
                    $apiError = 'Connection failed: ' . $e->getMessage();
                    Log::error('FraudBD Exception: ' . $e->getMessage());
                }
            } else {
                $apiError = 'FraudBD API is disabled or API Key missing. Check API Configuration.';
            }

            if ($request->ajax()) {
                return response()->json([
                    'success'        => empty($apiError),
                    'internalReport' => $internalReport,
                    'externalReport' => $externalReport,
                    'apiError'       => $apiError,
                    'searchedPhone'  => $cleanPhone,
                ]);
            }
        }

        return view('admin.orders.fraud_checker', compact('internalReport', 'externalReport', 'apiError', 'searchedPhone'));
    }

    /**
     * 12. Fraud Checker Setting View
     */
    public function fraudSetting()
    {
        $setting = Schema::hasTable('fraud_check_settings')
            ? DB::table('fraud_check_settings')->first()
            : null;

        return view('admin.system_settings.fraud_check', compact('setting'));
    }

    /**
     * 13. Fraud Checker Setting Update
     */
    public function updateFraudSetting(Request $request)
    {
        $request->validate([
            'api_email' => 'required|email',
            'api_key'   => 'required|string',
            'api_url'   => 'required|url',
        ]);

        if (Schema::hasTable('fraud_check_settings')) {
            DB::table('fraud_check_settings')->updateOrInsert(
                ['id' => 1],
                [
                    'provider'   => 'fraudbd.com',
                    'api_email'  => trim($request->api_email),
                    'api_key'    => trim($request->api_key),
                    'api_url'    => trim($request->api_url),
                    'is_active'  => $request->has('is_active'),
                    'updated_at' => now(),
                ]
            );
        }

        return back()->with('success', 'FraudBD API settings updated successfully.');
    }

    // =========================================================================
    // 14. ENTERPRISE MANUAL ORDER BOOKING ENGINE (Facebook/Phone/WhatsApp/Walk-in)
    // =========================================================================

    public function createManual()
    {
        $districts = [];
        if (class_exists(\App\Http\Controllers\Web\CheckoutController::class)) {
            $districts = \App\Http\Controllers\Web\CheckoutController::getAllDistricts();
        }
        if (empty($districts)) {
            $districts = ['Dhaka', 'Gazipur', 'Narayanganj', 'Chattogram', 'Sylhet', 'Rajshahi', 'Khulna', 'Barishal', 'Rangpur', 'Mymensingh', 'Other Districts'];
        }

        $couriers = Schema::hasTable('courier_settings')
            ? DB::table('courier_settings')->where('is_active', true)->get()
            : collect([
                (object) ['courier_name' => 'Steadfast Courier', 'courier_code' => 'steadfast'],
                (object) ['courier_name' => 'Pathao Courier', 'courier_code' => 'pathao'],
            ]);

        $sources = [
            'facebook' => 'Facebook Page',
            'whatsapp' => 'WhatsApp Order',
            'phone'    => 'Phone Call',
            'walk_in'  => 'Showroom Walk-in',
            'website'  => 'Direct Entry (Admin)',
            'other'    => 'Other Channel',
        ];

        return view('admin.orders.create_manual', compact('districts', 'couriers', 'sources'));
    }

    public function searchProductsAjax(Request $request)
    {
        $q = trim((string)$request->get('q', ''));
        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $products = DB::table('products')
            ->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('product_code', 'like', "%{$q}%")
                    ->orWhere('id', $q);
            })
            ->select('id', 'title', 'sku', 'product_code', 'regular_price', 'sale_price', 'stock', 'thumbnail', 'product_type')
            ->limit(20)
            ->get();

        $results = [];

        foreach ($products as $p) {
            $finalPrice = (float) (!empty($p->sale_price) && $p->sale_price > 0 ? $p->sale_price : $p->regular_price);
            $variants = [];

            if (Schema::hasTable('product_variants')) {
                $varRows = DB::table('product_variants')->where('product_id', $p->id)->get();
                foreach ($varRows as $v) {
                    $parts = array_filter([$v->color ?? null, $v->size ?? null]);
                    $label = count($parts) > 0 ? implode(' / ', $parts) : ($v->name ?? ('Variant #' . $v->id));
                    $vPrice = (float) (!empty($v->sale_price) && $v->sale_price > 0 ? $v->sale_price : (!empty($v->regular_price) && $v->regular_price > 0 ? $v->regular_price : $finalPrice));
                    $vStock = (int) ($v->stock ?? 0);
                    $vSku   = $v->product_code ?? ($v->sku ?? ('VAR-' . $v->id));

                    $variants[] = [
                        'variant_id'   => $v->id,
                        'title'        => $label,
                        'color'        => $v->color ?? null,
                        'size'         => $v->size ?? null,
                        'sku'          => $vSku,
                        'price'        => $vPrice,
                        'stock'        => $vStock,
                    ];
                }
            }

            $img = '';
            if (!empty($p->thumbnail)) {
                $t = ltrim($p->thumbnail, '/');
                $img = str_starts_with($t, 'http') ? $t : asset($t);
            }

            $results[] = [
                'product_id'   => $p->id,
                'title'        => $p->title,
                'sku'          => $p->sku ?? ($p->product_code ?? ('PRD-' . $p->id)),
                'price'        => $finalPrice,
                'stock'        => (int) ($p->stock ?? 0),
                'thumbnail'    => $img,
                'has_variants' => count($variants) > 0,
                'variants'     => $variants,
            ];
        }

        return response()->json($results);
    }

    public function searchCustomersAjax(Request $request)
    {
        try {
            $q = trim((string)$request->get('q', ''));
            if (strlen($q) < 2) {
                return response()->json([]);
            }

            $cleanDigits = preg_replace('/[^0-9]/', '', $q);
            $results = collect();

            if (Schema::hasTable('orders')) {
                $ordersQuery = DB::table('orders');

                $ordersQuery->where(function ($sub) use ($q, $cleanDigits) {
                    $phoneCols = ['customer_phone', 'phone', 'shipping_phone', 'billing_phone'];
                    foreach ($phoneCols as $col) {
                        if (Schema::hasColumn('orders', $col)) {
                            $sub->orWhere($col, 'like', "%{$q}%");
                            if (strlen($cleanDigits) >= 3 && $cleanDigits !== $q) {
                                $sub->orWhere($col, 'like', "%{$cleanDigits}%");
                            }
                        }
                    }

                    $nameCols = ['customer_name', 'name', 'shipping_name', 'billing_name'];
                    foreach ($nameCols as $col) {
                        if (Schema::hasColumn('orders', $col)) {
                            $sub->orWhere($col, 'like', "%{$q}%");
                        }
                    }
                });

                $orderRows = $ordersQuery->latest('id')->limit(20)->get();

                foreach ($orderRows as $row) {
                    $name = $row->customer_name ?? ($row->name ?? ($row->shipping_name ?? ''));
                    $phone = $row->customer_phone ?? ($row->phone ?? ($row->shipping_phone ?? ''));
                    $address = $row->customer_address ?? ($row->address ?? ($row->shipping_address ?? ''));
                    $district = $row->district ?? ($row->shipping_district ?? 'Dhaka');
                    $email = $row->customer_email ?? ($row->email ?? ($row->shipping_email ?? ''));

                    if (!empty($phone) || !empty($name)) {
                        $results->push([
                            'name'     => trim((string)$name),
                            'phone'    => trim((string)$phone),
                            'address'  => trim((string)$address),
                            'district' => trim((string)$district),
                            'email'    => trim((string)$email),
                            'source'   => 'Order History'
                        ]);
                    }
                }
            }

            if (Schema::hasTable('users')) {
                $usersQuery = DB::table('users');

                $usersQuery->where(function ($sub) use ($q, $cleanDigits) {
                    if (Schema::hasColumn('users', 'phone')) {
                        $sub->orWhere('phone', 'like', "%{$q}%");
                        if (strlen($cleanDigits) >= 3 && $cleanDigits !== $q) {
                            $sub->orWhere('phone', 'like', "%{$cleanDigits}%");
                        }
                    }
                    if (Schema::hasColumn('users', 'name')) {
                        $sub->orWhere('name', 'like', "%{$q}%");
                    }
                    if (Schema::hasColumn('users', 'email')) {
                        $sub->orWhere('email', 'like', "%{$q}%");
                    }
                });

                $userRows = $usersQuery->latest('id')->limit(10)->get();

                foreach ($userRows as $u) {
                    $uDist = $u->district ?? ($u->city ?? 'Dhaka');
                    $results->push([
                        'name'     => trim((string)($u->name ?? '')),
                        'phone'    => trim((string)($u->phone ?? '')),
                        'address'  => trim((string)($u->address ?? '')),
                        'district' => trim((string)$uDist),
                        'email'    => trim((string)($u->email ?? '')),
                        'source'   => 'Registered User'
                    ]);
                }
            }

            $filtered = $results->filter(fn($c) => !empty($c['phone']) || !empty($c['name']))
                ->unique(function ($c) {
                    return !empty($c['phone']) ? $c['phone'] : $c['name'];
                })
                ->values()
                ->take(10);

            return response()->json($filtered);

        } catch (\Throwable $e) {
            Log::error('searchCustomersAjax Error: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    public function storeManual(Request $request)
    {
        $request->validate([
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'required|string|max:30',
            'customer_address' => 'required|string|max:500',
            'district'         => 'nullable|string|max:100',
            'order_source'     => 'required|string|max:50',
            'order_status'     => 'required|string|max:50',
            'payment_method'   => 'required|string|max:50',
            'paid_amount'      => 'nullable|numeric|min:0',
            'delivery_charge'  => 'nullable|numeric|min:0',
            'discount'         => 'nullable|numeric|min:0',
            'items'            => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.variant_id' => 'nullable|integer',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $items = $request->items;
        $orderSource = strtolower($request->order_source);
        $district = $request->input('district', 'Dhaka');
        $deliveryCharge = (float) $request->input('delivery_charge', 0.00);
        $discount = (float) $request->input('discount', 0.00);

        foreach ($items as $it) {
            $pId = (int) $it['product_id'];
            $vId = !empty($it['variant_id']) ? (int) $it['variant_id'] : null;
            $qty = (int) $it['quantity'];

            $avail = InventoryService::checkStockAvailability($pId, $vId, $qty);
            if (!$avail['available']) {
                $label = !empty($avail['title']) ? $avail['title'] : "Product #{$pId}";
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock for '{$label}'. Available: {$avail['stock']}, Requested: {$qty}."
                ], 422);
            }
        }

        $productSubtotal = 0.00;
        foreach ($items as $it) {
            $productSubtotal += ((float)$it['unit_price'] * (int)$it['quantity']);
        }

        $grandTotal = max(0.00, ($productSubtotal + $deliveryCharge) - $discount);
        $paidAmount = min($grandTotal, (float) $request->input('paid_amount', 0.00));
        $dueAmount  = max(0.00, $grandTotal - $paidAmount);

        if ($grandTotal <= 0 || $dueAmount <= 0) {
            $paymentStatus = 'paid';
        } elseif ($paidAmount > 0) {
            $paymentStatus = 'partially_paid';
        } else {
            $paymentStatus = 'unpaid';
        }

        $targetOrderStatus = strtolower($request->order_status);
        $invoiceNo = 'INV-M-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        $isCOD = in_array(strtolower(trim($request->payment_method)), ['cash on delivery', 'cod']);
        $expectedCOD = $isCOD ? $dueAmount : ($paymentStatus === 'paid' ? 0.00 : $dueAmount);

        $safeOrderType = 'online';
        if (Schema::hasColumn('orders', 'order_type')) {
            try {
                $colInfo = DB::select("SHOW COLUMNS FROM `orders` LIKE 'order_type'");
                if (!empty($colInfo)) {
                    $typeStr = strtolower($colInfo[0]->Type ?? '');
                    if (str_contains($typeStr, 'manual') || str_contains($typeStr, 'varchar') || str_contains($typeStr, 'text')) {
                        $safeOrderType = 'manual';
                    } elseif (str_contains($typeStr, 'web')) {
                        $safeOrderType = 'web';
                    } else {
                        $safeOrderType = 'online';
                    }
                }
            } catch (\Throwable $e) {
                $safeOrderType = 'online';
            }
        }

        $adminNoteText = $request->notes;
        if (!empty($orderSource) && $orderSource !== 'web') {
            $adminNoteText = trim(" " . ($adminNoteText ?? ''));
        }

        DB::beginTransaction();
        try {
            $orderData = [
                'invoice_no'          => $invoiceNo,
                'user_id'             => auth()->id(),
                'subtotal'            => $productSubtotal,
                'product_subtotal'    => $productSubtotal,
                'grand_total'         => $grandTotal,
                'delivery_charge'     => $deliveryCharge,
                'shipping_charge'     => $deliveryCharge,
                'discount'            => $discount,
                'paid_amount'         => $paidAmount,
                'due_amount'          => $dueAmount,
                'payment_method'      => $request->payment_method,
                'payment_status'      => $paymentStatus,
                'order_status'        => $targetOrderStatus,
                'order_source'        => $orderSource,
                'order_type'          => $safeOrderType,
                'customer_name'       => $request->customer_name,
                'name'                => $request->customer_name,
                'shipping_name'       => $request->customer_name,
                'customer_phone'      => $request->customer_phone,
                'phone'               => $request->customer_phone,
                'shipping_phone'      => $request->customer_phone,
                'customer_email'      => $request->customer_email,
                'shipping_address'    => $request->customer_address,
                'customer_address'    => $request->customer_address,
                'address'             => $request->customer_address,
                'district'            => $district,
                'shipping_district'   => $district,
                'courier_name'        => $request->courier ?: null,
                'expected_cod_amount' => $expectedCOD,
                'actual_cod_received' => 0.00,
                'is_stock_deducted'   => false,
                'is_stock_restored'   => false,
                'is_cod_settled'      => false,
                'admin_note'          => $adminNoteText,
                'created_at'          => now(),
                'updated_at'          => now(),
            ];

            $filteredData = [];
            foreach ($orderData as $col => $val) {
                if (Schema::hasColumn('orders', $col)) {
                    $filteredData[$col] = $val;
                }
            }

            $orderId = DB::table('orders')->insertGetId($filteredData);

            foreach ($items as $it) {
                $pId = (int) $it['product_id'];
                $vId = !empty($it['variant_id']) ? (int) $it['variant_id'] : null;
                $qty = (int) $it['quantity'];
                $unitPrice = (float) $it['unit_price'];

                $pRecord = DB::table('products')->where('id', $pId)->first();
                $vRecord = !empty($vId) ? DB::table('product_variants')->where('id', $vId)->first() : null;

                $pTitle = $it['product_title'] ?? ($pRecord->title ?? "Product #{$pId}");
                $vTitle = null;
                $color  = null;
                $size   = null;
                $sku    = $pRecord->sku ?? ($pRecord->product_code ?? ('PRD-' . $pId));
                $purchaseCost = (float) ($pRecord->purchase_cost ?? ($pRecord->purchase_price ?? 0.00));

                if ($vRecord) {
                    $color = $vRecord->color ?? null;
                    $size  = $vRecord->size ?? null;
                    $parts = array_filter([$color, $size]);
                    $vTitle = count($parts) > 0 ? implode(' / ', $parts) : ($vRecord->name ?? "Variant #{$vId}");
                    $sku   = $vRecord->product_code ?? ($vRecord->sku ?? ('VAR-' . $vId));
                    $rawCost = $vRecord->purchase_cost ?? ($vRecord->purchase_price ?? null);
                    if (!is_null($rawCost) && (float)$rawCost > 0) {
                        $purchaseCost = (float)$rawCost;
                    }
                }

                InventoryService::recordOrderEstimate($pId, $vId, $qty, $invoiceNo);

                $itemData = [
                    'order_id'          => $orderId,
                    'product_id'        => $pId,
                    'product_title'     => $pTitle,
                    'variant_id'        => $vId,
                    'variant_title'     => $vTitle,
                    'color'             => $color,
                    'size'              => $size,
                    'sku'               => $sku,
                    'quantity'          => $qty,
                    'unit_price'        => $unitPrice,
                    'subtotal'          => $unitPrice * $qty,
                    'purchase_cost'     => $purchaseCost,
                    'cogs'              => round($purchaseCost * $qty, 2),
                    'is_cost_estimated' => ($purchaseCost <= 0),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];

                $filteredItem = [];
                foreach ($itemData as $col => $val) {
                    if (Schema::hasColumn('order_items', $col)) {
                        $filteredItem[$col] = $val;
                    }
                }

                DB::table('order_items')->insert($filteredItem);
            }

            $savedOrder = DB::table('orders')->where('id', $orderId)->first();

            $immediatePacked = ['processing', 'packed', 'shipped', 'delivered', 'attempt_to_deliver'];
            if (in_array($targetOrderStatus, $immediatePacked)) {
                InventoryService::deductOrderStockAtProcessing($savedOrder, auth()->id());
            }

            if ($paidAmount > 0 && class_exists(PaymentTransactionService::class)) {
                PaymentTransactionService::recordCheckoutTransaction($savedOrder, [
                    'payment_type'     => 'manual_entry',
                    'payment_method'   => $request->payment_method,
                    'sender_number'    => $request->sender_number,
                    'transaction_id'   => $request->transaction_id ?: ('MTRX-' . strtoupper(Str::random(6))),
                    'submitted_amount' => $paidAmount,
                ]);
            }

            DB::commit();

            return response()->json([
                'success'    => true,
                'message'    => "Manual order #{$invoiceNo} created successfully!",
                'order_id'   => $orderId,
                'invoice_no' => $invoiceNo,
                'redirect'   => route('admin.order.view', $orderId)
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Manual order store failed: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create manual order: ' . $e->getMessage()
            ], 422);
        }
    }

    // =========================================================================
    // 15. COMPLETE RETURN, EXCHANGE & DAMAGE PROCESSING WORKFLOW
    // =========================================================================

    public function processReturnExchange(Request $request, $id)
    {
        $request->validate([
            'process_type'            => 'required|in:return,exchange',
            'return_items'            => 'required|array|min:1',
            'return_items.*.item_id'  => 'required|integer',
            'return_items.*.qty'      => 'required|integer|min:1',
            'return_items.*.condition'=> 'required|in:restock,damaged',
            'exchange_items'          => 'nullable|array',
            'exchange_items.*.product_id' => 'required_with:exchange_items|integer',
            'exchange_items.*.variant_id' => 'nullable|integer',
            'exchange_items.*.qty'        => 'required_with:exchange_items|integer|min:1',
            'exchange_items.*.unit_price' => 'required_with:exchange_items|numeric|min:0',
            'exchange_delivery_charge'=> 'nullable|numeric|min:0',
            'action_note'             => 'nullable|string|max:500',
        ]);

        $order = DB::table('orders')->where('id', $id)->first();
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        $processType = $request->process_type;
        $returnItemsInput = $request->return_items;
        $exchangeItemsInput = $request->input('exchange_items', []);
        $exchangeDelivery = (float) $request->input('exchange_delivery_charge', 0.00);
        $actionNote = $request->input('action_note', '');

        DB::beginTransaction();
        try {
            $totalReturnValue = 0.00;
            $adminId = auth()->id();
            $invoiceNo = $order->invoice_no ?? ('ORD-' . $order->id);

            // 1. Process Return Items & Restock / Damage Routing
            foreach ($returnItemsInput as $rItem) {
                $orderItem = DB::table('order_items')
                    ->where('id', $rItem['item_id'])
                    ->where('order_id', $order->id)
                    ->first();

                if (!$orderItem) {
                    continue;
                }

                $retQty = min((int) $rItem['qty'], (int) ($orderItem->quantity ?? 1));
                $unitPrice = (float) ($orderItem->unit_price ?? ($orderItem->price ?? 0.00));
                $lineReturnValue = $unitPrice * $retQty;
                $totalReturnValue +=$lineReturnValue;

                $pId = (int)$orderItem->product_id;
                $vId = !empty($orderItem->variant_id) ? (int)$orderItem->variant_id : null;
                $condition =$rItem['condition'];

                if ($condition === 'restock') {
                    // Safe call to restoreStock in InventoryService
                    InventoryService::restoreStock(
                        $pId,
                        $vId,$retQty,
                        'order_return_restock',
                        $invoiceNo,
                        'admin_return_inspection',
                        $adminId,
                        "Order #{$invoiceNo} inspected item restocked in good condition ({$retQty} pcs)"
                    );
                } else {
                    // Damaged item: Logged cleanly via InventoryService without raw SQL column crashes
                    InventoryService::recordDamagedReturn(
                        $pId,$vId,
                        $retQty,$invoiceNo,
                        (float) ($orderItem->purchase_cost ?? 0.00),
                        $adminId,
                        "Order #{$invoiceNo} item damaged: Scrapped/Write-off ({$retQty} pcs)"
                    );
                }
            }

            // 2. Process Replacement Exchange Items
            $totalExchangeValue = 0.00;
            if ($processType === 'exchange' && !empty($exchangeItemsInput)) {
                foreach ($exchangeItemsInput as $eItem) {$epId = (int) $eItem['product_id'];$evId = !empty($eItem['variant_id']) ? (int)$eItem['variant_id'] : null;
                    $eqty = (int)$eItem['qty'];
                    $ePrice = (float)$eItem['unit_price'];

                    $avail = InventoryService::checkStockAvailability($epId, $evId,$eqty);
                    if (!$avail['available']) {
                        throw new \Exception("Insufficient stock for exchange item '{$avail['title']}'. Available: {$avail['stock']}");
                    }

                    $totalExchangeValue += ($ePrice * $eqty);

                    InventoryService::recordOrderEstimate($epId,$evId, $eqty,$invoiceNo);

                    $pRec = DB::table('products')->where('id', $epId)->first();$vRec = !empty($evId) ? DB::table('product_variants')->where('id', $evId)->first() : null;

                    $pTitle =$pRec->title ?? "Exchange Product #{$epId}";
                    $color =$vRec->color ?? null;
                    $size =$vRec->size ?? null;
                    $parts = array_filter([$color,$size]);
                    $vTitle = count($parts) > 0 ? implode(' / ', $parts) : ($vRec->name ?? null);
                    $sku = $vRec->product_code ?? ($vRec->sku ?? ($pRec->sku ?? "EXC-{$epId}"));
                    $purchaseCost = (float) ($vRec->purchase_cost ?? ($pRec->purchase_cost ?? 0.00));

                    $newItemData = [
                        'order_id'          => $order->id,
                        'product_id'        => $epId,
                        'product_title'     => "[Exchange] " . $pTitle,
                        'variant_id'        => $evId,
                        'variant_title'     => $vTitle,
                        'color'             => $color,
                        'size'              => $size,
                        'sku'               => $sku,
                        'quantity'          => $eqty,
                        'unit_price'        => $ePrice,
                        'subtotal'          => $ePrice * $eqty,
                        'purchase_cost'     => $purchaseCost,
                        'cogs'              => round($purchaseCost * $eqty, 2),
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];

                    $filteredItem = [];
                    foreach ($newItemData as $k =>$v) {
                        if (Schema::hasColumn('order_items', $k)) {$filteredItem[$k] =$v;
                        }
                    }

                    if (Schema::hasColumn('order_items', 'price') && !isset($filteredItem['price'])) {
                        $filteredItem['price'] =$ePrice;
                    }
                    if (Schema::hasColumn('order_items', 'qty') && !isset($filteredItem['qty'])) {
                        $filteredItem['qty'] =$eqty;
                    }
                    if (Schema::hasColumn('order_items', 'total') && !isset($filteredItem['total'])) {$filteredItem['total'] = $ePrice * $eqty;
                    }

                    DB::table('order_items')->insert($filteredItem);
                }
            }

            // 3. Financial Rebalancing & Order Status Transition
            $prevPaid = (float) ($order->paid_amount ?? 0.00);
            $prevGrand = (float) ($order->grand_total ?? 0.00);

            $orderUpdate = [
                'order_status' => ($processType === 'return') ? 'returned' : 'exchange',
                'updated_at'   => now(),
            ];

            if ($processType === 'return') {
                if (Schema::hasColumn('orders', 'is_stock_restored')) {
                    $orderUpdate['is_stock_restored'] = true;
                }
                $notePrefix = "[Returned] Processed return for inspected items.";
            } else {
                $newSubtotal = max(0.00, ((float)($order->subtotal ?? $prevGrand) - $totalReturnValue) +$totalExchangeValue);
                $newGrand = max(0.00,$newSubtotal + $exchangeDelivery);$newDue = max(0.00, $newGrand -$prevPaid);

                if (Schema::hasColumn('orders', 'subtotal')) {
                    $orderUpdate['subtotal'] =$newSubtotal;
                }
                if (Schema::hasColumn('orders', 'product_subtotal')) {
                    $orderUpdate['product_subtotal'] =$newSubtotal;
                }
                if (Schema::hasColumn('orders', 'grand_total')) {
                    $orderUpdate['grand_total'] =$newGrand;
                }
                if (Schema::hasColumn('orders', 'due_amount')) {
                    $orderUpdate['due_amount'] =$newDue;
                }
                if (Schema::hasColumn('orders', 'payment_status')) {
                    $orderUpdate['payment_status'] = ($newDue <= 0.00) ? 'paid' : 'partially_paid';
                }

                $notePrefix = "[Exchange] Return Credit: BDT {$totalReturnValue}, New Replacement: BDT {$totalExchangeValue}, Exchange Shipping: BDT {$exchangeDelivery}.";
            }

            $currentNote =$order->admin_note ?? '';
            $finalNote = trim($notePrefix . ' ' . $actionNote . ($currentNote ? " | Previous: " . $currentNote : ''));
            if (Schema::hasColumn('orders', 'admin_note')) {
                $orderUpdate['admin_note'] =$finalNote;
            }

            $safeOrderUpdate = [];
            foreach ($orderUpdate as $col =>$val) {
                if (Schema::hasColumn('orders', $col)) {$safeOrderUpdate[$col] =$val;
                }
            }

            DB::table('orders')->where('id', $order->id)->update($safeOrderUpdate);

            // 4. Courier Booking Sync
            if (Schema::hasTable('courier_bookings')) {
                $cStat = ($processType === 'return') ? 'returned' : 'in_transit';
                DB::table('courier_bookings')
                    ->where('order_id', $order->id)
                    ->update([
                        'delivery_status' => $cStat,
                        'booking_status'  => $cStat,
                        'updated_at'      => now(),
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => ucfirst($processType) . " processed successfully! Inventory and financials rebalanced.",
                'redirect'=> route('admin.order.view', $order->id)
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Return/Exchange processing error for Order #{$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}