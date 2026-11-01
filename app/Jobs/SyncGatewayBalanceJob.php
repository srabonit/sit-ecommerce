<?php

namespace App\Jobs;

use App\Models\SmsProvider;
use App\Models\SmsSetting;
use App\Services\Sms\SmsService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncGatewayBalanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public ?int $providerId;

    /**
     * Create a new job instance.
     * If $providerId is null, all active providers will be synced.
     */
    public function __construct(?int $providerId = null)
    {
        $this->providerId = $providerId;
    }

    /**
     * Execute the job.
     */
    public function handle(SmsService $smsService): void
    {
        $providers = $this->providerId
            ? SmsProvider::where('id', $this->providerId)->where('is_active', true)->get()
            : SmsProvider::where('is_active', true)->get();

        if ($providers->isEmpty()) {
            return;
        }

        $lowThreshold = (float) SmsSetting::getVal('low_balance_threshold', 50.00);
        $adminPhone = (string) SmsSetting::getVal('admin_notification_phone', '');
        $autoAlertEnabled = SmsSetting::isEnabled('auto_sms_admin_low_balance', true);

        foreach ($providers as $provider) {
            try {
                $smsService->setProvider($provider);
                $syncResult = $smsService->syncBalance();

                if (!($syncResult['status'] ?? false)) {
                    Log::warning("Balance sync failed for provider [{$provider->name}]: " . ($syncResult['message'] ?? 'Unknown error'));
                    continue;
                }

                $currentBalance = (float) ($syncResult['balance'] ?? 0.00);

                // Low balance detection & rate-limited admin alerting
                if ($currentBalance <= $lowThreshold && $autoAlertEnabled && !empty($adminPhone)) {
                    $cacheKey = "low_balance_alert_sent_{$provider->id}";

                    // Alert at most once every 6 hours per provider to prevent spam loops
                    if (!Cache::has($cacheKey)) {
                        $smsService->sendTemplate('admin_low_balance_alert', $adminPhone, [
                            'balance'       => number_format($currentBalance, 2),
                            'provider_name' => $provider->name,
                        ]);

                        Cache::put($cacheKey, true, now()->addHours(6));

                        Log::notice("Low SMS balance alert dispatched to admin for provider: {$provider->name} (Balance: {$currentBalance})");
                    }
                }
            } catch (Exception $e) {
                Log::error("Exception syncing balance for provider [{$provider->name}]: " . $e->getMessage());
            }
        }
    }
}