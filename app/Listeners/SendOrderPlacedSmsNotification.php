<?php

namespace App\Listeners;

use App\Jobs\SendSingleSmsJob;
use App\Models\SmsSetting;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendOrderPlacedSmsNotification implements ShouldQueue
{
    /**
     * Handle the event when an order is created.
     * Works with generic order events (e.g. OrderCreated, OrderPlaced)
     * expecting $event->order instance.
     */
    public function handle(object $event): void
    {
        $order = $event->order ?? null;

        if (!$order) {
            return;
        }

        // 1. Verify global SMS trigger
        if (!SmsSetting::isEnabled('global_sms_enabled', true)) {
            return;
        }

        try {
            $customerPhone = $order->phone_number 
                ?? $order->billing_phone 
                ?? $order->shipping_phone 
                ?? $order->user?->phone;

            $currency = config('app.currency', 'BDT');
            $payableAmount = number_format((float) ($order->total_amount ?? $order->grand_total ?? 0), 2) . ' ' . $currency;
            $orderNo = (string) ($order->order_number ?? $order->id);

            // 2. Dispatch Customer Order Confirmation SMS
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
                        'customer_name' => $order->customer_name ?? $order->name ?? 'Customer',
                        'order_number'  => $orderNo,
                        'order_amount'  => $payableAmount,
                    ]
                )->onQueue('sms-orders');
            }

            // 3. Dispatch Admin New Order Alert SMS
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
                        'order_number'  => $orderNo,
                        'order_amount'  => $payableAmount,
                        'customer_phone'=> $customerPhone ?? 'N/A',
                    ]
                )->onQueue('sms-orders');
            }
        } catch (Exception $e) {
            Log::error("SendOrderPlacedSmsNotification error for Order #{$order->id}: " . $e->getMessage());
        }
    }
}