<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OnlineOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductStock;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request; // Added missing Request import
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OnlineOrderController extends Controller
{
    /**
     * Place E-Commerce Online Order
     */
    public function placeOrder(OnlineOrderRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $user = $request->user();
            $subtotal = 0;
            $orderItemsData = [];

            // Handle Payment Screenshot Upload
            $screenshotPath = null;
            if ($request->hasFile('payment_screenshot')) {
                $screenshotPath = $request->file('payment_screenshot')->store('payment_screenshots', 'public');
            }

            // 1. Calculate & Lock Stock
            foreach ($request->items as $item) {
                $itemSubtotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $itemSubtotal;

                // Central Warehouse or First Available Stock Deduct
                $stock = ProductStock::where('product_id', $item['product_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$stock || $stock->quantity < $item['quantity']) {
                    throw new \Exception("Product ID {$item['product_id']} is out of stock for online orders.");
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

            $discount = $request->discount_amount ?? 0;
            $tax = $request->tax_amount ?? 0;
            $grandTotal = ($subtotal - $discount) + $tax;

            // Determine Status based on payment method
            $paymentStatus = str_contains($request->payment_method ?? '', 'manual') ? 'pending' : 'paid';
            $orderStatus = 'pending';

            // 2. Create Order Record
            $order = Order::create([
                'invoice_no'         => 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'user_id'            => $user->id,
                'order_type'         => 'online',
                'subtotal'           => $subtotal,
                'discount_amount'    => $discount,
                'tax_amount'         => $tax,
                'grand_total'        => $grandTotal,
                'paid_amount'        => $paymentStatus === 'paid' ? $grandTotal : 0,
                'due_amount'         => $paymentStatus === 'paid' ? 0 : $grandTotal,
                'payment_method'     => $request->payment_method,
                'transaction_id'     => $request->transaction_id,
                'sender_number'      => $request->sender_number,
                'payment_screenshot' => $screenshotPath,
                'payment_status'     => $paymentStatus,
                'order_status'       => $orderStatus,
                'notes'              => $request->notes,
            ]);

            // 3. Save Items
            foreach ($orderItemsData as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderItem::create($itemData);
            }

            // Trigger Notification to Customer upon Order Placement
            $customerName = $user->name ?? 'Customer';
            $phone = $user->phone ?? $request->sender_number;
            $msg = "Hello {$customerName}, your Order #{$order->invoice_no} has been placed successfully!";

            if ($phone) {
                NotificationService::sendSms($phone, $msg);
            }
            if ($user->email) {
                NotificationService::sendEmail($user->email, "Order Confirmation #{$order->invoice_no}", $msg);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Order placed successfully.',
                'data'    => $order->load('items.product')
            ], 201);
        });
    }

    /**
     * Customer Order History
     */
    public function myOrders(): JsonResponse
    {
        $orders = Order::with('items.product')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => true,
            'data'   => $orders
        ], 200);
    }

    /**
     * Update Order Status & Notify Customer
     */
    public function updateOrderStatus(Request $request, $orderId): JsonResponse
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $order = Order::with('user')->findOrFail($orderId);
        $order->update(['order_status' => $request->status]);

        $customerName = $order->user->name ?? 'Customer';
        $phone = $order->user->phone ?? $order->sender_number;
        $email = $order->user->email;

        $msg = "Hello {$customerName}, your Order #{$order->invoice_no} status is now: " . strtoupper($request->status) . ". Thank you!";

        // Trigger SMS
        if ($phone) {
            NotificationService::sendSms($phone, $msg);
        }

        // Trigger Email
        if ($email) {
            NotificationService::sendEmail($email, "Order #{$order->invoice_no} Status Update", $msg);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Order status updated and notification sent.'
        ], 200);
    }
}