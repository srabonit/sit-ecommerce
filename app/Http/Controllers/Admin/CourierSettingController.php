<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CourierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class CourierSettingController extends Controller
{
    public function index()
    {
        $this->ensureDefaultCouriers();
        $couriers = DB::table('courier_settings')->get();
        return view('admin.system_settings.courier', compact('couriers'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'api_key'    => 'nullable|string',
            'secret_key' => 'nullable|string',
            'api_url'    => 'nullable|string',
        ]);

        DB::table('courier_settings')->where('id', $id)->update([
            'api_key'    => trim($request->api_key),
            'secret_key' => trim($request->secret_key),
            'api_url'    => trim($request->api_url),
            'is_active'  => $request->has('is_active'),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Courier configuration updated successfully.');
    }

    /**
     * কুরিয়ার লাইভ ব্যালেন্স চেক (AJAX)
     */
    public function checkBalance(Request $request, $id)
    {
        $courier = DB::table('courier_settings')->where('id', $id)->first();
        if (!$courier) {
            return response()->json(['success' => false, 'message' => 'Courier not found'], 404);
        }

        if (str_contains(strtolower($courier->courier_name), 'steadfast')) {
            $result = CourierService::getSteadfastBalance();
            return response()->json($result);
        }

        return response()->json(['success' => false, 'message' => 'Live balance inquiry not supported for this provider yet.']);
    }

    /**
     * পার্সেল লাইভ ট্র্যাকিং স্ট্যাটাস চেক (AJAX)
     */
    public function trackOrder(Request $request, $orderId)
    {
        $order = DB::table('orders')->where('id', $orderId)->first();
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        $trackingCode = $order->courier_tracking_id ?? ($order->parcel_id ?? null);
        if (!$trackingCode) {
            return response()->json(['success' => false, 'message' => 'No tracking code assigned to this order.'], 422);
        }

        $courierName = strtolower($order->courier_name ?? '');

        if (str_contains($courierName, 'steadfast')) {
            $result = CourierService::trackSteadfast($trackingCode);
            return response()->json($result);
        }

        return response()->json([
            'success' => true,
            'delivery_status' => $order->order_status,
            'tracking_code' => $trackingCode,
            'message' => 'Current local status: ' . strtoupper($order->order_status)
        ]);
    }

    /**
     * অটোমেটিক ডেলিভারি স্ট্যাটাস সিঙ্ক ওয়েবহুক (Enterprise Decoupled Settlement Guard)
     */
    public function handleWebhook(Request $request, $provider)
    {
        $providerName = strtolower($provider);
        Log::info("Courier Webhook Received [{$providerName}]: ", $request->all());

        // Extract tracking identifier & status from multiple courier payload formats
        $trackingCode = $request->input('tracking_code') 
            ?? $request->input('consignment_id') 
            ?? $request->input('tracking_id') 
            ?? $request->input('order_id');

        $status = strtolower(trim(
            $request->input('status') 
            ?? $request->input('order_status') 
            ?? $request->input('delivery_status') 
            ?? ''
        ));

        if (empty($trackingCode) || empty($status)) {
            return response()->json(['status' => 'ignored', 'message' => 'Missing tracking code or status in webhook payload.']);
        }

        // 1. Try finding in courier_bookings table first (Centralized synchronization)
        if (Schema::hasTable('courier_bookings')) {
            $booking = DB::table('courier_bookings')
                ->where('tracking_code', $trackingCode)
                ->orWhere('consignment_id', $trackingCode)
                ->first();

            if ($booking) {
                CourierService::syncStatusWithOrder($booking->id, $status, true);
                return response()->json([
                    'status'  => 'success', 
                    'message' => 'Booking #' . $booking->id . ' status synced to ' . strtoupper($status) . ' via webhook.'
                ]);
            }
        }

        // 2. Fallback direct match in orders table (Adhering to strict decoupling policy)
        $order = DB::table('orders')
            ->where('courier_tracking_id', $trackingCode)
            ->orWhere('parcel_id', $trackingCode)
            ->first();

        if ($order) {
            $updateData = ['updated_at' => now()];

            if (in_array($status, ['delivered', 'delivered_approval_pending', 'completed'])) {
                $updateData['order_status'] = 'delivered';

                // ENTERPRISE DECOUPLING: COD orders do NOT become 'paid' until bank/cash settlement is verified
                $isCOD = in_array(strtolower(trim($order->payment_method ?? '')), ['cash on delivery', 'cod']);
                $isAlreadyPaid = strtolower($order->payment_status ?? '') === 'paid';

                if (!$isCOD || $isAlreadyPaid) {
                    $updateData['payment_status'] = 'paid';
                    if (Schema::hasColumn('orders', 'paid_amount')) {
                        $updateData['paid_amount'] = $order->grand_total;
                    }
                    if (Schema::hasColumn('orders', 'due_amount')) {
                        $updateData['due_amount'] = 0;
                    }
                }
            } elseif (in_array($status, ['cancelled', 'cancelled_approval_pending'])) {
                $updateData['order_status'] = 'cancelled';
            } elseif (in_array($status, ['returned', 'return', 'partial_delivered'])) {
                $updateData['order_status'] = 'returned';
            } elseif (in_array($status, ['in_transit', 'shipped', 'picked'])) {
                if (strtolower($order->order_status ?? '') !== 'delivered') {
                    $updateData['order_status'] = 'shipped';
                }
            }

            DB::table('orders')->where('id', $order->id)->update($updateData);

            return response()->json([
                'status'  => 'success', 
                'message' => 'Order #' . $order->id . ' status synced via webhook fallback.'
            ]);
        }

        return response()->json(['status' => 'not_found', 'message' => 'No order matched for tracking code: ' . $trackingCode]);
    }

    private function ensureDefaultCouriers()
    {
        $defaults = [
            [
                'courier_name' => 'Steadfast Courier',
                'courier_code' => 'steadfast',
                'api_url'      => 'https://portal.steadfast.com.bd/api/v1',
                'is_active'    => true,
            ],
            [
                'courier_name' => 'Pathao Courier',
                'courier_code' => 'pathao',
                'api_url'      => 'https://api-hermes.pathao.com',
                'is_active'    => false,
            ],
            [
                'courier_name' => 'RedX Courier',
                'courier_code' => 'redx',
                'api_url'      => 'https://openapi.redx.com.bd/v1.0.0-beta',
                'is_active'    => false,
            ]
        ];

        foreach ($defaults as $d) {
            $exists = DB::table('courier_settings')
                ->where('courier_name', $d['courier_name'])
                ->orWhere(function ($q) use ($d) {
                    if (Schema::hasColumn('courier_settings', 'courier_code')) {
                        $q->where('courier_code', $d['courier_code']);
                    }
                })
                ->exists();

            if (!$exists) {
                $insertData = [
                    'courier_name' => $d['courier_name'],
                    'api_url'      => $d['api_url'],
                    'is_active'    => $d['is_active'],
                    'api_key'      => null,
                    'secret_key'   => null,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];

                if (Schema::hasColumn('courier_settings', 'courier_code')) {
                    $insertData['courier_code'] = $d['courier_code'];
                }

                DB::table('courier_settings')->insert($insertData);
            }
        }
    }
}