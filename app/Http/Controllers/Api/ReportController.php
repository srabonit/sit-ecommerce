<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Get POS Thermal Receipt Data
     */
    public function getReceiptData($invoiceNo): JsonResponse
    {
        $order = Order::with(['items.product', 'branch', 'user'])
            ->where('invoice_no', $invoiceNo)
            ->first();

        if (!$order) {
            return response()->json([
                'status'  => false,
                'message' => 'Invoice not found.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'receipt' => [
                'store_name'     => $order->branch?->name ?? 'Shop Now BD',
                'invoice_no'     => $order->invoice_no,
                'date'           => $order->created_at->format('Y-m-d h:i A'),
                'customer_name'  => $order->user?->name ?? 'Walk-in Customer',
                'items'          => $order->items->map(function ($item) {
                    return [
                        'name'       => $item->product?->name,
                        'qty'        => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'subtotal'   => $item->subtotal,
                    ];
                }),
                'subtotal'       => $order->subtotal,
                'discount'       => $order->discount_amount,
                'tax'            => $order->tax_amount,
                'grand_total'    => $order->grand_total,
                'paid_amount'    => $order->paid_amount,
                'due_amount'     => $order->due_amount,
                'payment_method' => strtoupper($order->payment_method),
            ]
        ], 200);
    }

    /**
     * Sales & Financial Report
     */
    public function salesReport(Request $request): JsonResponse
    {
        $query = Order::query()->where('order_status', 'completed');

        // Date Range Filtering
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        // Branch Filtering
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $totalSales = (clone $query)->sum('grand_total');
        $totalPaid  = (clone $query)->sum('paid_amount');
        $totalDue   = (clone $query)->sum('due_amount');
        $totalOrders = (clone $query)->count();

        // Payment Method Breakdown
        $paymentBreakdown = (clone $query)
            ->select('payment_method', DB::raw('SUM(grand_total) as total'))
            ->groupBy('payment_method')
            ->get();

        return response()->json([
            'status' => true,
            'summary' => [
                'total_orders'      => $totalOrders,
                'total_sales'       => $totalSales,
                'total_paid'        => $totalPaid,
                'total_due'         => $totalDue,
                'payment_breakdown' => $paymentBreakdown,
            ]
        ], 200);
    }
}