<?php

namespace App\Jobs;

use App\Models\SmsCampaign;
use App\Models\SmsCampaignRecipient;
use App\Models\SmsLog;
use App\Models\SmsProvider;
use App\Services\Sms\Drivers\OnecodesoftDriver;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class CheckSmsDeliveryStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public ?int $smsLogId;
    public int $batchLimit;

    /**
     * Create a new job instance.
     * If $smsLogId is passed, only that log is verified.
     * Otherwise, recent pending logs will be checked in batch.
     */
    public function __construct(?int $smsLogId = null, int $batchLimit = 50)
    {
        $this->smsLogId = $smsLogId;
        $this->batchLimit = $batchLimit;

        if (empty($this->queue)) {
            $this->onQueue('sms-dlr');
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $logs = $this->smsLogId
            ? SmsLog::where('id', $this->smsLogId)->whereNotNull('message_id')->get()
            : SmsLog::where('status', 'sent')
                ->whereNotNull('message_id')
                ->where('created_at', '>=', Carbon::now()->subHours(24))
                ->limit($this->batchLimit)
                ->get();

        if ($logs->isEmpty()) {
            return;
        }

        // Cache driver instances per provider to prevent redundant instantiations
        $driverPool = [];

        foreach ($logs as $log) {
            try {
                $providerName = $log->provider ?? 'onecodesoft';

                if (!isset($driverPool[$providerName])) {
                    $provider = SmsProvider::where('driver', $providerName)->first()
                        ?? SmsProvider::where('name', 'Onecodesoft')->first()
                        ?? SmsProvider::first();

                    $driverPool[$providerName] = new OnecodesoftDriver($provider);
                }

                /** @var OnecodesoftDriver $driver */
                $driver = $driverPool[$providerName];

                $response = $driver->checkDeliveryStatus($log->message_id);

                if (!($response['status'] ?? false)) {
                    continue;
                }

                $rawStatus = strtolower(trim((string) ($response['delivery_status'] ?? '')));
                $now = Carbon::now();

                // 1. Success / Delivered Status
                if (in_array($rawStatus, ['delivered', 'dlr_delivered', 'success', 'successful', 'sent_to_carrier'], true)) {
                    $log->update([
                        'status'           => 'delivered',
                        'delivered_at'     => $now,
                        'gateway_response' => json_encode($response['data'] ?? $response),
                    ]);

                    // Sync corresponding campaign recipient status if part of a campaign
                    if ($log->campaign_id) {
                        SmsCampaignRecipient::where('campaign_id', $log->campaign_id)
                            ->where('phone_number', $log->recipient)
                            ->where('sms_status', 'sent')
                            ->update([
                                'sms_status'   => 'delivered',
                                'processed_at' => $now,
                            ]);
                    }
                }
                // 2. Failure / Rejection Status
                elseif (in_array($rawStatus, ['failed', 'rejected', 'undelivered', 'expired', 'blacklisted'], true)) {
                    $oldStatus = $log->status;

                    $log->update([
                        'status'           => 'failed',
                        'gateway_response' => json_encode($response['data'] ?? $response),
                    ]);

                    if ($log->campaign_id) {
                        SmsCampaignRecipient::where('campaign_id', $log->campaign_id)
                            ->where('phone_number', $log->recipient)
                            ->update([
                                'sms_status'     => 'failed',
                                'failure_reason' => 'Carrier reported: ' . $rawStatus,
                                'processed_at'   => $now,
                            ]);

                        // If previously counted as successful in campaign, adjust counters safely
                        if ($oldStatus === 'sent') {
                            $campaign = SmsCampaign::find($log->campaign_id);
                            if ($campaign) {
                                if (Schema::hasColumn('sms_campaigns', 'successful_count') && $campaign->successful_count > 0) {
                                    $campaign->decrement('successful_count', 1);
                                }
                                if (Schema::hasColumn('sms_campaigns', 'failed_count')) {
                                    $campaign->increment('failed_count', 1);
                                }
                            }
                        }
                    }
                }
            } catch (Throwable $e) {
                Log::warning("CheckSmsDeliveryStatusJob error for SMS Log ID {$log->id}: " . $e->getMessage());
            }
        }
    }
}