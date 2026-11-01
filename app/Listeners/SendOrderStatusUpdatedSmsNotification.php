<?php

namespace App\Listeners;

use App\Jobs\SendSingleSmsJob;
use App\Models\SmsSetting;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendOrderStatusUpdatedSmsNotification implements ShouldQueue
{
    /**
     * Handle order status updated event.
     * Expects $event->order and optionally $event->status or $event->previousStatus.
     */
    public function handle(object $event): void
    {
        $order = $event->order ?? null;

        if (!$order) {
            return;
        }

        // 1. Verify Global SMS System Toggle
        if (!SmsSetting::isEnabled('global_sms_enabled', true)) {
            return;
        }

        try {
            $customerPhone = $order->phone_number 
                ?? $order->billing_phone 
                ?? $order->shipping_phone 
                ?? $order->user?->phone;

            if (empty($customerPhone)) {
                return;
            }

            $currentStatus = strtolower(trim((string) ($event->status ?? $order->status ?? '')));
            $orderNo = (string) ($order->order_number ?? $order->id);
            $customerName = $order->customer_name ?? $order->name ?? 'Customer';
            $trackingCode = $order->tracking_number ?? $order->consignment_id ?? 'N/A';
            $courierName = $order->courier_name ?? $order->shipping_method ?? 'our courier partner';

            // 2. Map status to corresponding template keys and system toggles
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

            // Resolve specific config or fallback to generic status update
            $config = $statusConfigs[$currentStatus] ?? [
                'setting'  => 'auto_sms_order_status_updated',
                'template' => 'order_status_updated',
            ];

            // 3. Verify specific status toggle
            if (!SmsSetting::isEnabled($config['setting'], true)) {
                return;
            }

            // 4. Dispatch status notification job
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
                    'customer_name'   => $customerName,
                    'order_number'    => $orderNo,
                    'order_status'    => ucfirst($currentStatus),
                    'courier_name'    => $courierName,
                    'tracking_code'   => $trackingCode,
                    'track_url'       => url("/track-order/{$orderNo}"),
                ]
            )->onQueue('sms-orders');

        } catch (Exception $e) {
            Log::error("SendOrderStatusUpdatedSmsNotification error for Order #{$order->id}: " . $e->getMessage());
        }
    }
}