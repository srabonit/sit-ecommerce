<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\OrderReturnItem;
use App\Models\ProductStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReturnController extends Controller
{
    public function processReturn(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'reason'   => 'required|string',
            'items'    => 'required|array|min:1',
            'items.*.product_id'        => 'required|exists:products,id',
            'items.*.quantity'          => 'required|integer|min:1',
            'items.*.refund_price'      => 'required|numeric|min:0',
            'items.*.restock_condition' => 'required|boolean',
        ]);

        return DB::transaction(function () use ($request) {
            $order = Order::findOrFail($request->order_id);
            $totalRefund = 0;

            foreach ($request->items as $item) {
                $totalRefund += $item['quantity'] * $item['refund_price'];
            }

            $returnRecord = OrderReturn::create([
                'order_id'      => $order->id,
                'user_id'       => auth()->id(),
                'return_no'     => 'RET-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'refund_amount' => $totalRefund,
                'reason'        => $request->reason,
                'status'        => 'approved',
            ]);

            foreach ($request->items as $item) {
                OrderReturnItem::create([
                    'order_return_id'   => $returnRecord->id,
                    'product_id'        => $item['product_id'],
                    'quantity'          => $item['quantity'],
                    'refund_price'      => $item['refund_price'],
                    'restock_condition' => $item['restock_condition'],
                ]);

                if ($item['restock_condition']) {
                    $stock = ProductStock::where('product_id', $item['product_id'])
                        ->where('branch_id', $order->branch_id)
                        ->first();

                    if ($stock) {
                        $stock->increment('quantity', $item['quantity']);
                    }
                }
            }

            return response()->json(['status' => true, 'message' => 'Return processed and inventory adjusted.', 'data' => $returnRecord->load('items')], 201);
        });
    }
}