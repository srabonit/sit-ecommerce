<?php

namespace App\Listeners;

use App\Jobs\SendSingleSmsJob;
use App\Models\SmsSetting;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendCustomerWelcomeSmsNotification implements ShouldQueue
{
    /**
     * Handle customer registration event.
     * Compatible with Laravel's default Registered event or custom user creation events.
     */
    public function handle(object $event): void
    {
        $user = $event->user ?? $event->customer ?? null;

        if (!$user) {
            return;
        }

        // 1. Verify Global and Specific Feature Toggles
        if (!SmsSetting::isEnabled('global_sms_enabled', true)) {
            return;
        }

        if (!SmsSetting::isEnabled('auto_sms_customer_welcome', true)) {
            return;
        }

        try {
            $phone = $user->phone_number ?? $user->phone ?? null;

            if (empty($phone)) {
                return;
            }

            $customerName = $user->name ?? $user->first_name ?? 'Customer';

            // 2. Dispatch Welcome SMS via Queue Job
            SendSingleSmsJob::dispatch(
                $phone,
                null,
                'transactional',
                [
                    'user_id' => $user->id ?? null,
                ],
                'customer_welcome',
                [
                    'customer_name' => $customerName,
                    'store_name'    => config('app.name', 'SHOP NOW BD'),
                    'website_url'   => url('/'),
                ]
            )->onQueue('sms-notifications');

        } catch (Exception $e) {
            Log::error("SendCustomerWelcomeSmsNotification error for User #{$user->id}: " . $e->getMessage());
        }
    }
}