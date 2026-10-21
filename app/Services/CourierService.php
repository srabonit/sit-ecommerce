<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CourierService
{
    /**
     * ১. সেন্ট্রাল কুরিয়ার বুকিং ডিসপ্যাচার (Steadfast, Pathao & Manual)
     */
    public static function bookCourier($order, $courierName, array $params = [])
    {
        $normalizedName = strtolower(trim($courierName));

        // Steadfast API বুকিং
        if (str_contains($normalizedName, 'steadfast')) {
            $result = self::sendToSteadfast($order, $params['note'] ?? null);
            if ($result['success']) {
                self::recordBookingEntry($order, 'steadfast', 'api', [
                    'tracking_code'  => $result['tracking_code'],
                    'consignment_id' => $result['consignment_id'] ?? null,
                    'service_type'   => $params['service_type'] ?? 'regular',
                    'parcel_weight'  => (float)($params['weight'] ?? 0.50),
                    'courier_cost'   => (float)($params['courier_cost'] ?? 0.00),
                    'delivery_status'=> $result['status'] ?? 'in_review',
                    'remarks'        => $params['note'] ?? null,
                ]);
            }
            return $result;
        }

        // Pathao API বুকিং
        if (str_contains($normalizedName, 'pathao')) {
            $result = PathaoCourierService::createOrder($order, $params);
            if ($result['success']) {
                self::recordBookingEntry($order, 'pathao', 'api', [
                    'tracking_code'  => $result['tracking_code'],
                    'consignment_id' => $result['consignment_id'] ?? null,
                    'service_type'   => $params['service_type'] ?? 'regular',
                    'parcel_weight'  => (float)($params['weight'] ?? 0.50),
                    'courier_cost'   => (float)($params['courier_cost'] ?? 0.00),
                    'delivery_status'=> $result['order_status'] ?? 'Created',
                    'remarks'        => $params['note'] ?? null,
                ]);
            }
            return $result;
        }

        // ম্যানুয়াল / লোকাল কুরিয়ার বুকিং (সুন্দরবন, করতোয়া, এসএ পরিবহন, নিজস্ব রাইডার ইত্যাদি)
        return self::bookManualCourier($order, $courierName, $params);
    }

    /**
     * ২. ম্যানুয়াল / লোকাল কুরিয়ার হ্যান্ডলার
     */
    public static function bookManualCourier($order, $courierName, array $params = [])
    {
        $trackingCode = trim($params['tracking_code'] ?? ($params['parcel_id'] ?? ''));
        if (empty($trackingCode)) {
            $cleanPrefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $courierName), 0, 3)) ?: 'MAN';
            $trackingCode = $cleanPrefix . '-' . mt_rand(100000, 999999);
        }

        $courierCost     = (float)($params['courier_cost'] ?? 0.00);
        $courierCostPaid = (float)($params['courier_cost_paid'] ?? 0.00);
        $courierCostDue  = max(0, $courierCost - $courierCostPaid);

        $booking = self::recordBookingEntry($order, $courierName, 'manual', [
            'tracking_code'    => $trackingCode,
            'consignment_id'   => $params['consignment_id'] ?? $trackingCode,
            'service_type'     => $params['service_type'] ?? 'manual_handover',
            'parcel_weight'    => (float)($params['weight'] ?? 0.50),
            'courier_cost'     => $courierCost,
            'courier_cost_paid'=> $courierCostPaid,
            'courier_cost_due' => $courierCostDue,
            'delivery_status'  => $params['delivery_status'] ?? 'in_transit',
            'handover_date'    => !empty($params['handover_date']) ? $params['handover_date'] : now(),
            'remarks'          => $params['note'] ?? 'Manual Courier Dispatch',
        ]);

        return [
            'success'       => true,
            'tracking_code' => $trackingCode,
            'courier_name'  => $courierName,
            'booking_id'    => $booking ? $booking->id : null,
            'message'       => "Order booked under manual courier '{$courierName}' successfully!"
        ];
    }

    /**
     * ৩. courier_bookings টেবিলে সুরক্ষিত ডেটা এন্ট্রি / স্ন্যাপশট তৈরি
     */
    public static function recordBookingEntry($order, $courierName, $courierType = 'api', array $details = [])
    {
        if (!Schema::hasTable('courier_bookings')) {
            return null;
        }

        $paidAmount = (float)($order->paid_amount ?? 0);
        $grandTotal = (float)($order->grand_total ?? 0);
        $codAmount  = max(0, $grandTotal - $paidAmount);

        $trackingCode = $details['tracking_code'] ?? ($order->courier_tracking_id ?? ($order->parcel_id ?? 'TRK-' . mt_rand(100000, 999999)));

        $bookingData = [
            'order_id'              => $order->id,
            'courier_name'          => ucfirst($courierName),
            'courier_type'          => $courierType,
            'consignment_id'        => $details['consignment_id'] ?? null,
            'tracking_code'         => $trackingCode,
            'service_type'          => $details['service_type'] ?? 'regular',
            'parcel_weight'         => (float)($details['parcel_weight'] ?? 0.50),
            'order_amount'          => $grandTotal,
            'cod_amount'            => $codAmount,
            'collected_amount'      => 0.00,
            'cod_charge'            => (float)($details['cod_charge'] ?? 0.00),
            'courier_cost'          => (float)($details['courier_cost'] ?? 0.00),
            'courier_cost_paid'     => (float)($details['courier_cost_paid'] ?? 0.00),
            'courier_cost_due'      => (float)($details['courier_cost_due'] ?? ($details['courier_cost'] ?? 0.00)),
            'return_cost'           => (float)($details['return_cost'] ?? 0.00),
            'booking_status'        => $details['booking_status'] ?? 'booked',
            'delivery_status'       => $details['delivery_status'] ?? 'in_review',
            'cod_settlement_status' => $codAmount > 0 ? 'unsettled' : 'settled',
            'settled_amount'        => 0.00,
            'settlement_date'       => null,
            'handover_date'         => $details['handover_date'] ?? now(),
            'remarks'               => $details['remarks'] ?? null,
            'updated_at'            => now(),
        ];

        // পূর্বে বুকিং করা থাকলে সেটি আপডেট করবে, না থাকলে নতুন রো তৈরি করবে
        $existingBooking = DB::table('courier_bookings')
            ->where('order_id', $order->id)
            ->first();

        if ($existingBooking) {
            DB::table('courier_bookings')->where('id', $existingBooking->id)->update($bookingData);
            $bookingId = $existingBooking->id;
        } else {
            $bookingData['created_at'] = now();
            $bookingId = DB::table('courier_bookings')->insertGetId($bookingData);
        }

        // মূল orders টেবিলে ট্র্যাকিং কোড ও কুরিয়ার নাম সিঙ্ক
        $orderUpdate = ['updated_at' => now()];
        if (Schema::hasColumn('orders', 'courier_name')) {
            $orderUpdate['courier_name'] = ucfirst($courierName);
        }
        if (Schema::hasColumn('orders', 'courier_tracking_id')) {
            $orderUpdate['courier_tracking_id'] = $trackingCode;
        }
        if (Schema::hasColumn('orders', 'parcel_id')) {
            $orderUpdate['parcel_id'] = $trackingCode;
        }
        if (Schema::hasColumn('orders', 'order_status') && !in_array(strtolower($order->order_status ?? ''), ['delivered', 'processing', 'packed'])) {
            $orderUpdate['order_status'] = 'shipped';
        }

        DB::table('orders')->where('id', $order->id)->update($orderUpdate);

        return DB::table('courier_bookings')->where('id', $bookingId)->first();
    }

    /**
     * ৪. দ্বিমুখী স্ট্যাটাস সিঙ্ক গার্ড (Two-Way Safe Status Synchronizer)
     * Enterprise Rule: 'delivered' হলে কুরিয়ার পার্সেল Delivered কিন্তু COD Settlement না হওয়া পর্যন্ত Order Paid হবে না!
     */
    public static function syncStatusWithOrder($bookingId, $newDeliveryStatus, $isWebhook = false)
    {
        $booking = DB::table('courier_bookings')->where('id', $bookingId)->first();
        if (!$booking) return false;

        $order = DB::table('orders')->where('id', $booking->order_id)->first();
        if (!$order) return false;

        $statusNormalized = strtolower(trim($newDeliveryStatus));
        $bookingUpdate = [
            'delivery_status' => $statusNormalized,
            'updated_at'      => now(),
        ];

        $orderUpdate = ['updated_at' => now()];

        if (in_array($statusNormalized, ['delivered', 'completed', 'delivered_approval_pending'])) {
            $bookingUpdate['booking_status'] = 'delivered';
            $bookingUpdate['collected_amount'] = $booking->cod_amount;

            $orderUpdate['order_status'] = 'delivered';

            // ENTERPRISE RULE: Decoupled Payment
            // যদি আগেই অনলাইনে Paid হয়ে থাকে, তাহলে Paid থাকবে।
            // কিন্তু COD অর্ডারের ক্ষেত্রে কুরিয়ার সেটেলমেন্ট ব্যাংকে জমা হওয়ার আগে সরাসরি 'paid' হবে না!
            $isAlreadyPaid = strtolower($order->payment_status ?? '') === 'paid';
            $isCOD = in_array(strtolower(trim($order->payment_method ?? '')), ['cash on delivery', 'cod']);

            if (!$isCOD || $isAlreadyPaid) {
                $orderUpdate['payment_status'] = 'paid';
                if (Schema::hasColumn('orders', 'paid_amount')) {
                    $orderUpdate['paid_amount'] = $order->grand_total;
                }
                if (Schema::hasColumn('orders', 'due_amount')) {
                    $orderUpdate['due_amount'] = 0;
                }
                $bookingUpdate['cod_settlement_status'] = 'settled';
                $bookingUpdate['settled_amount'] = $booking->cod_amount;
                $bookingUpdate['settlement_date'] = now();
            }
        } elseif (in_array($statusNormalized, ['cancelled', 'cancelled_approval_pending'])) {
            $bookingUpdate['booking_status'] = 'cancelled';
            $orderUpdate['order_status']     = 'cancelled';
        } elseif (in_array($statusNormalized, ['returned', 'return', 'partial_delivered'])) {
            $bookingUpdate['booking_status'] = 'returned';
            $orderUpdate['order_status']     = 'returned';
        } elseif (in_array($statusNormalized, ['attempt_to_deliver', 'attempted', 'attempt_delivery'])) {
            $bookingUpdate['booking_status'] = 'attempt_to_deliver';
            $orderUpdate['order_status']     = 'attempt_to_deliver';
        } elseif (in_array($statusNormalized, ['in_transit', 'shipped', 'picked'])) {
            $bookingUpdate['booking_status'] = 'in_transit';
            if ($order->order_status !== 'delivered') {
                $orderUpdate['order_status'] = 'shipped';
            }
        }

        DB::table('courier_bookings')->where('id', $bookingId)->update($bookingUpdate);
        DB::table('orders')->where('id', $order->id)->update($orderUpdate);

        return true;
    }

    /**
     * ৫. এন্টারপ্রাইজ COD সেটেলমেন্ট ও অ্যাকাউন্টস লেজার সিঙ্ক মেথড
     * কুরিয়ার কোম্পানি যখন ব্যাংকে বা বিকাশে টাকা পাঠায়, তখন এই মেথড কল হবে।
     */
    public static function processCodSettlement($bookingId, array $payload, $adminId = null)
    {
        $booking = DB::table('courier_bookings')->where('id', $bookingId)->first();
        if (!$booking) {
            return ['success' => false, 'message' => 'Courier booking record not found.'];
        }

        $order = DB::table('orders')->where('id', $booking->order_id)->first();
        if (!$order) {
            return ['success' => false, 'message' => 'Associated order not found.'];
        }

        $settledAmount  = (float) ($payload['settled_amount'] ?? $booking->cod_amount);
        $settlementDate = !empty($payload['settlement_date']) ? Carbon::parse($payload['settlement_date']) : now();
        $codCharge      = (float) ($payload['cod_charge'] ?? $booking->cod_charge);
        $courierCost    = (float) ($payload['courier_cost'] ?? $booking->courier_cost);
        $returnCost     = (float) ($payload['return_cost'] ?? $booking->return_cost);
        $paymentMethod  = $payload['payment_method'] ?? 'Bank Transfer (Courier COD)';
        $transactionId  = $payload['transaction_id'] ?? ('STLM-' . strtoupper(Str::random(8)));
        $remarks        = $payload['remarks'] ?? 'Courier COD settlement verified & deposited';

        $totalExpectedCod = (float) $booking->cod_amount;
        $status = ($settledAmount >= $totalExpectedCod && $totalExpectedCod > 0) 
            ? 'settled' 
            : ($settledAmount > 0 ? 'partial' : 'unsettled');

        DB::beginTransaction();
        try {
            // ১. courier_bookings টেবিল আপডেট
            $bookingUpdate = [
                'settled_amount'        => $settledAmount,
                'cod_settlement_status' => $status,
                'settlement_date'       => $settlementDate,
                'cod_charge'            => $codCharge,
                'courier_cost'          => $courierCost,
                'courier_cost_paid'     => $courierCost,
                'courier_cost_due'      => 0.00,
                'return_cost'           => $returnCost,
                'remarks'               => $remarks,
                'updated_at'            => now(),
            ];

            if ($booking->delivery_status !== 'delivered' && $status === 'settled') {
                $bookingUpdate['delivery_status'] = 'delivered';
                $bookingUpdate['booking_status']  = 'delivered';
                $bookingUpdate['collected_amount']= $totalExpectedCod;
            }

            DB::table('courier_bookings')->where('id', $bookingId)->update($bookingUpdate);

            // ২. orders টেবিল আপডেট
            $prevPaidAmount = (float) ($order->paid_amount ?? 0);
            $newPaidAmount  = min((float) $order->grand_total, $prevPaidAmount + $settledAmount);
            $newDueAmount   = max(0.00, (float)$order->grand_total - $newPaidAmount);

            $orderUpdate = [
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('orders', 'paid_amount')) {
                $orderUpdate['paid_amount'] = $newPaidAmount;
            }
            if (Schema::hasColumn('orders', 'due_amount')) {
                $orderUpdate['due_amount'] = $newDueAmount;
            }
            if (Schema::hasColumn('orders', 'actual_cod_received')) {
                $orderUpdate['actual_cod_received'] = $settledAmount;
            }
            if (Schema::hasColumn('orders', 'is_cod_settled')) {
                $orderUpdate['is_cod_settled'] = ($status === 'settled');
            }
            if (Schema::hasColumn('orders', 'payment_status')) {
                $orderUpdate['payment_status'] = ($newDueAmount <= 0) ? 'paid' : ($newPaidAmount > 0 ? 'partially_paid' : 'unpaid');
            }
            if (Schema::hasColumn('orders', 'order_status') && $order->order_status !== 'delivered' && $status === 'settled') {
                $orderUpdate['order_status'] = 'delivered';
            }

            DB::table('orders')->where('id', $order->id)->update($orderUpdate);

            // ৩. payment_transactions টেবিলে একাউন্টিং লেজার এন্ট্রি
            if ($settledAmount > 0 && Schema::hasTable('payment_transactions')) {
                $invoiceNo = $order->invoice_no ?? (string)$order->id;
                
                // Idempotency check: যাতে একই সেটেলমেন্টের টাকা দুইবার লেজারে যোগ না হয়
                $alreadyExists = DB::table('payment_transactions')
                    ->where('order_id', $order->id)
                    ->where('source_type', 'courier_cod')
                    ->where('submitted_amount', $settledAmount)
                    ->exists();

                if (!$alreadyExists) {
                    $txnData = [
                        'order_id'            => $order->id,
                        'invoice_no'          => $invoiceNo,
                        'source_type'         => 'courier_cod',
                        'payment_type'        => 'courier_settlement',
                        'payment_method'      => $paymentMethod,
                        'submitted_amount'    => $settledAmount,
                        'transaction_id'      => $transactionId,
                        'sender_number'       => $booking->courier_name,
                        'verification_status' => 'verified',
                        'verified_by'         => $adminId ?: auth()->id(),
                        'verified_at'         => now(),
                        'admin_note'          => "COD Settlement from {$booking->courier_name}. Tracking: {$booking->tracking_code}. " . $remarks,
                        'created_at'          => $settlementDate,
                        'updated_at'          => now(),
                    ];

                    $filteredTxn = [];
                    foreach ($txnData as $f => $v) {
                        if (Schema::hasColumn('payment_transactions', $f)) {
                            $filteredTxn[$f] = $v;
                        }
                    }

                    DB::table('payment_transactions')->insert($filteredTxn);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Order #{$order->id} successfully settled with BDT " . number_format($settledAmount, 2),
                'status'  => $status,
                'order_paid' => $newPaidAmount,
                'order_due'  => $newDueAmount,
            ];

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("COD Settlement Failed for Booking #{$bookingId}: " . $e->getMessage());
            return ['success' => false, 'message' => 'Settlement failed: ' . $e->getMessage()];
        }
    }

    /**
     * ৬. Steadfast Courier - পার্সেল বুকিং (বিদ্যমান মেথড অক্ষুণ্ণ)
     */
    public static function sendToSteadfast($order, $customNote = null)
    {
        $setting = DB::table('courier_settings')
            ->where(function ($q) {
                $q->where('courier_name', 'like', '%steadfast%')
                  ->orWhere('courier_code', 'steadfast');
            })
            ->first();

        if (!$setting || !$setting->is_active || empty($setting->api_key) || empty($setting->secret_key)) {
            return [
                'success' => false,
                'message' => 'Steadfast API is not configured or disabled. Switching to manual mode.',
                'manual'  => true
            ];
        }

        $apiUrl = rtrim($setting->api_url ?: 'https://portal.steadfast.com.bd/api/v1', '/');
        $invoiceNo = $order->invoice_no ?? ('INV-' . $order->id);

        $customerName    = $order->shipping_name ?? ($order->customer_name ?? ($order->name ?? 'Customer'));
        $customerPhone   = $order->shipping_phone ?? ($order->customer_phone ?? ($order->phone ?? '01700000000'));
        $customerAddress = $order->shipping_address ?? ($order->customer_address ?? ($order->address ?? 'Dhaka, Bangladesh'));

        $paidAmount = (float)($order->paid_amount ?? 0);
        $grandTotal = (float)($order->grand_total ?? 0);
        $codAmount  = max(0, $grandTotal - $paidAmount);

        $payload = [
            'invoice'           => (string) $invoiceNo,
            'recipient_name'    => (string) $customerName,
            'recipient_phone'   => (string) $customerPhone,
            'recipient_address' => (string) $customerAddress,
            'cod_amount'        => (float) $codAmount,
            'note'              => (string) ($customNote ?: ($order->admin_note ?? 'Handle with care')),
        ];

        try {
            $response = Http::withoutVerifying()
                ->timeout(25)
                ->withHeaders([
                    'Api-Key'      => trim($setting->api_key),
                    'Secret-Key'   => trim($setting->secret_key),
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ])
                ->post("{$apiUrl}/create_order", $payload);

            $resData = $response->json();

            if ($response->successful() && isset($resData['status']) && $resData['status'] == 200) {
                $consignment = $resData['consignment'] ?? [];
                return [
                    'success'        => true,
                    'tracking_code'  => $consignment['tracking_code'] ?? null,
                    'consignment_id' => $consignment['consignment_id'] ?? null,
                    'status'         => $consignment['status'] ?? 'in_review',
                    'raw'            => $resData,
                    'message'        => 'Parcel sent to Steadfast successfully!'
                ];
            }

            return [
                'success' => false,
                'message' => $resData['message'] ?? 'Failed to push order to Steadfast API.',
                'errors'  => $resData['errors'] ?? null
            ];
        } catch (\Throwable $e) {
            Log::error('Steadfast API Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Connection timeout or Steadfast server unreachable: ' . $e->getMessage(),
                'manual'  => true
            ];
        }
    }

    /**
     * ৭. Steadfast - ব্যালেন্স চেক (বিদ্যমান মেথড অক্ষুণ্ণ)
     */
    public static function getSteadfastBalance()
    {
        $setting = DB::table('courier_settings')
            ->where(function ($q) {
                $q->where('courier_name', 'like', '%steadfast%')
                  ->orWhere('courier_code', 'steadfast');
            })
            ->first();

        if (!$setting || empty($setting->api_key) || empty($setting->secret_key)) {
            return ['success' => false, 'message' => 'API Key or Secret missing.'];
        }

        $apiUrl = rtrim($setting->api_url ?: 'https://portal.steadfast.com.bd/api/v1', '/');

        try {
            $response = Http::withoutVerifying()
                ->timeout(15)
                ->withHeaders([
                    'Api-Key'      => trim($setting->api_key),
                    'Secret-Key'   => trim($setting->secret_key),
                    'Content-Type' => 'application/json',
                ])
                ->get("{$apiUrl}/get_balance");

            $data = $response->json();
            if ($response->successful() && isset($data['status']) && $data['status'] == 200) {
                return [
                    'success' => true,
                    'balance' => $data['current_balance'] ?? 0,
                ];
            }

            return ['success' => false, 'message' => $data['message'] ?? 'Unable to fetch balance.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * ৮. Steadfast - লাইভ ট্র্যাকিং স্ট্যাটাস চেক (বিদ্যমান মেথড অক্ষুণ্ণ)
     */
    public static function trackSteadfast($trackingCode)
    {
        $setting = DB::table('courier_settings')
            ->where(function ($q) {
                $q->where('courier_name', 'like', '%steadfast%')
                  ->orWhere('courier_code', 'steadfast');
            })
            ->first();

        if (!$setting) return ['success' => false, 'message' => 'Setting not found'];

        $apiUrl = rtrim($setting->api_url ?: 'https://portal.steadfast.com.bd/api/v1', '/');

        try {
            $response = Http::withoutVerifying()
                ->timeout(15)
                ->withHeaders([
                    'Api-Key'    => trim($setting->api_key),
                    'Secret-Key' => trim($setting->secret_key),
                ])
                ->get("{$apiUrl}/status_by_trackingcode/{$trackingCode}");

            $data = $response->json();
            if ($response->successful() && isset($data['status']) && $data['status'] == 200) {
                return [
                    'success'        => true,
                    'delivery_status'=> $data['delivery_status'] ?? 'unknown',
                    'raw'            => $data
                ];
            }

            return ['success' => false, 'message' => $data['message'] ?? 'Tracking not found.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}