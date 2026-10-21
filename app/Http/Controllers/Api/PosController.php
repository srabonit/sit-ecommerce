<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\PosCheckoutRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    /**
     * Process Atomic POS Checkout
     */
    public function checkout(PosCheckoutRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $user = $request->user();
            $branchId = $request->branch_id ?? $user->branch_id;
            $warehouseId = $request->warehouse_id ?? $user->warehouse_id;

            $subtotal = 0;
            $orderItemsData = [];

            // 1. Process Stock & Calculate Subtotal with Lock
            foreach ($request->items as $item) {
                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $itemSubtotal;

                // Atomic Stock Reduction
                $stock = ProductStock::where('product_id', $item['product_id'])
                    ->where('branch_id', $branchId)
                    ->where('warehouse_id', $warehouseId)
                    ->lockForUpdate()
                    ->first();

                if (!$stock || $stock->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for Product ID: {$item['product_id']} in this branch/warehouse.");
                }

                $stock->quantity -= $item['quantity'];
                $stock->save();

                $orderItemsData[] = [
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal'   => $itemSubtotal,
                ];
            }

            // 2. Calculations
            $discount = $request->discount_amount ?? 0;
            $tax = $request->tax_amount ?? 0;
            $grandTotal = ($subtotal - $discount) + $tax;
            $paidAmount = $request->paid_amount;
            $dueAmount = max(0, $grandTotal - $paidAmount);

            $paymentStatus = 'paid';
            if ($dueAmount > 0) {
                $paymentStatus = $paidAmount > 0 ? 'partial' : 'unpaid';
            }

            // 3. Create Invoice Record
            $order = Order::create([
                'invoice_no'      => 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'branch_id'       => $branchId,
                'warehouse_id'    => $warehouseId,
                'user_id'         => $request->customer_id ?? $user->id,
                'order_type'      => 'pos',
                'subtotal'        => $subtotal,
                'discount_amount' => $discount,
                'tax_amount'      => $tax,
                'grand_total'     => $grandTotal,
                'paid_amount'     => $paidAmount,
                'due_amount'      => $dueAmount,
                'payment_method'  => $request->payment_method,
                'payment_status'  => $paymentStatus,
                'order_status'    => 'completed',
                'notes'           => $request->notes,
            ]);

            // 4. Save Items
            foreach ($orderItemsData as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderItem::create($itemData);
            }

            return response()->json([
                'status'  => true,
                'message' => 'POS Sale completed successfully.',
                'data'    => $order->load(['items.product', 'branch', 'user'])
            ], 201);
        });
    }
}