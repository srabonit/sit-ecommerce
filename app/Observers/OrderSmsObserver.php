<?php

namespace App\Observers;

use App\Jobs\SendSingleSmsJob;
use App\Models\Order;
use App\Models\SmsSetting;
use Exception;
use Illuminate\Support\Facades\Log;

class OrderSmsObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        if (!SmsSetting::isEnabled('global_sms_enabled', true)) {
            return;
        }

        try {
            $customerPhone = $this->resolveCustomerPhone($order);
            $orderNo = (string) ($order->order_number ?? $order->id);
            $currency = config('app.currency', 'BDT');
            $payableAmount = number_format((float) ($order->total_amount ?? $order->grand_total ?? 0), 2) . ' ' . $currency;
            $customerName = $order->customer_name ?? $order->name ?? 'Customer';

            // 1. Dispatch Customer Order Confirmation SMS
            if (!empty($customerPhone) && SmsSetting::isEnabled('auto_sms_order_placed_customer', true)) {
                SendSingleSmsJob::dispatch(
                    $customerPhone,
                    null,
                    'transactional',
                    [
                        'order_id' => $order->id,
                        'user_id'  => $order->user_id ?? null,
                    ],
                    'order_placed_customer',
                    [
                        'customer_name' => $customerName,
                        'order_number'  => $orderNo,
                        'order_amount'  => $payableAmount,
                    ]
                )->onQueue('sms-orders');
            }

            // 2. Dispatch Admin New Order Alert SMS
            $adminPhone = SmsSetting::getVal('admin_notification_phone');
            if (!empty($adminPhone) && SmsSetting::isEnabled('auto_sms_admin_new_order', true)) {
                SendSingleSmsJob::dispatch(
                    $adminPhone,
                    null,
                    'transactional',
                    [
                        'order_id' => $order->id,
                    ],
                    'admin_new_order_alert',
                    [
                        'order_number'   => $orderNo,
                        'order_amount'   => $payableAmount,
                        'customer_phone' => $customerPhone ?? 'N/A',
                    ]
                )->onQueue('sms-orders');
            }
        } catch (Exception $e) {
            Log::error("OrderSmsObserver created error for Order #{$order->id}: " . $e->getMessage());
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if (!SmsSetting::isEnabled('global_sms_enabled', true)) {
            return;
        }

        // Only trigger when the 'status' column has changed
        if (!$order->wasChanged('status')) {
            return;
        }

        try {
            $customerPhone = $this->resolveCustomerPhone($order);

            if (empty($customerPhone)) {
                return;
            }

            $currentStatus = strtolower(trim((string) $order->status));
            $orderNo = (string) ($order->order_number ?? $order->id);
            $customerName = $order->customer_name ?? $order->name ?? 'Customer';
            $trackingCode = $order->tracking_number ?? $order->consignment_id ?? 'N/A';
            $courierName = $order->courier_name ?? $order->shipping_method ?? 'our courier partner';

            $statusConfigs = [
                'confirmed' => [
                    'setting'  => 'auto_sms_order_confirmed',
                    'template' => 'order_confirmed',
                ],
                'processing' => [
                    'setting'  => 'auto_sms_order_processing',
                    'template' => 'order_processing',
                ],
                'shipped' => [
                    'setting'  => 'auto_sms_order_shipped',
                    'template' => 'order_shipped',
                ],
                'delivered' => [
                    'setting'  => 'auto_sms_order_delivered',
                    'template' => 'order_delivered',
                ],
                'cancelled' => [
                    'setting'  => 'auto_sms_order_cancelled',
                    'template' => 'order_cancelled',
                ],
            ];

            $config = $statusConfigs[$currentStatus] ?? [
                'setting'  => 'auto_sms_order_status_updated',
                'template' => 'order_status_updated',
            ];

            if (!SmsSetting::isEnabled($config['setting'], true)) {
                return;
            }

            SendSingleSmsJob::dispatch(
                $customerPhone,
                null,
                'transactional',
                [
                    'order_id' => $order->id,
                    'user_id'  => $order->user_id ?? null,
                ],
                $config['template'],
                [
                    'customer_name' => $customerName,
                    'order_number'  => $orderNo,
                    'order_status'  => ucfirst($currentStatus),
                    'courier_name'  => $courierName,
                    'tracking_code' => $trackingCode,
                    'track_url'     => url("/track-order/{$orderNo}"),
                ]
            )->onQueue('sms-orders');

        } catch (Exception $e) {
            Log::error("OrderSmsObserver updated error for Order #{$order->id}: " . $e->getMessage());
        }
    }

    /**
     * Resolve customer mobile number across potential attribute names
     */
    protected function resolveCustomerPhone(Order $order): ?string
    {
        return $order->phone_number 
            ?? $order->billing_phone 
            ?? $order->shipping_phone 
            ?? $order->phone 
            ?? $order->customer?->phone 
            ?? $order->user?->phone 
            ?? null;
    }
}