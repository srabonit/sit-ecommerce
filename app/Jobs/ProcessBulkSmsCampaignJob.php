<?php

namespace App\Jobs;

use App\Models\CommunicationPreference;
use App\Models\SmsBlacklist;
use App\Models\SmsCampaign;
use App\Models\SmsCampaignRecipient;
use App\Models\SmsLog;
use App\Models\SmsSetting;
use App\Services\Sms\Drivers\OnecodesoftDriver;
use App\Services\Sms\SmsService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ProcessBulkSmsCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Timeout after 60 minutes for large campaigns
     */
    public int $timeout = 3600;

    /**
     * Single attempt for whole campaign loop to prevent duplicate batch dispatching
     */
    public int $tries = 1;

    public int $campaignId;
    public int $batchChunkSize;

    /**
     * Create a new job instance.
     */
    public function __construct(int $campaignId, int $batchChunkSize = 100)
    {
        $this->campaignId = $campaignId;
        $this->batchChunkSize = $batchChunkSize;

        if (empty($this->queue)) {
            $this->onQueue('sms-campaigns');
        }
    }

    /**
     * Execute the job.
     */
    public function handle(SmsService $smsService): void
    {
        $campaign = SmsCampaign::with('provider')->find($this->campaignId);

        if (!$campaign || in_array($campaign->status, ['completed', 'cancelled', 'failed'], true)) {
            return;
        }

        // Global master toggles verification
        if (!$this->isSettingEnabled('global_sms_enabled') || !$this->isSettingEnabled('bulk_sms_enabled')) {
            $campaign->update([
                'status'      => 'failed',
                'description' => trim(($campaign->description ?? '') . ' [Failed: Bulk SMS or Global SMS is disabled in settings]'),
            ]);
            return;
        }

        // Mark campaign as running
        $campaign->update([
            'status'     => 'running',
            'started_at' => $campaign->started_at ?? Carbon::now(),
        ]);

        $driver = new OnecodesoftDriver($campaign->provider);
        $costPerSms = $campaign->provider ? (float) $campaign->provider->price_per_sms : 0.3500;

        // Process pending recipients in chunks
        $campaign->recipients()
            ->where('sms_status', 'pending')
            ->chunkById($this->batchChunkSize, function ($recipients) use ($campaign, $smsService, $driver, $costPerSms) {
                $batchPayload = [];
                $validRecipientMap = [];
                $processedCount = 0;
                $successCount = 0;
                $failedCount = 0;
                $batchCost = 0.0000;

                foreach ($recipients as $recipient) {
                    $phone = $smsService->formatNumber($recipient->phone_number);
                    $processedCount++;

                    // 1. Blacklist verification
                    if ($this->isPhoneBlacklisted($phone)) {
                        $recipient->update([
                            'sms_status'     => 'failed',
                            'failure_reason' => 'Blacklisted phone number',
                            'processed_at'   => Carbon::now(),
                        ]);
                        $failedCount++;
                        continue;
                    }

                    // 2. Marketing preference / consent verification
                    if (!$this->canReceiveMarketing($phone)) {
                        $recipient->update([
                            'sms_status'     => 'failed',
                            'failure_reason' => 'Recipient unsubscribed from SMS marketing',
                            'processed_at'   => Carbon::now(),
                        ]);
                        $failedCount++;
                        continue;
                    }

                    $messageText = (!empty($recipient->personalized_sms)) ? $recipient->personalized_sms : $campaign->sms_message;
                    $metrics = $smsService->analyzeMessage($messageText);
                    $itemCost = $metrics['sms_parts'] * $costPerSms;

                    $batchPayload[] = [
                        'Number' => $phone,
                        'Text'   => $messageText,
                    ];

                    $validRecipientMap[$phone] = [
                        'recipient' => $recipient,
                        'text'      => $messageText,
                        'parts'     => $metrics['sms_parts'],
                        'chars'     => $metrics['characters_count'],
                        'cost'      => $itemCost,
                    ];
                }

                // If no recipients passed the safety filter in this chunk
                if (empty($batchPayload)) {
                    $this->incrementCampaignCounters($campaign, $processedCount, 0, $failedCount, 0.0000);
                    return;
                }

                // 3. Dispatch batch to gateway driver
                $response = $driver->sendBulk($batchPayload, $campaign->sender_id);
                $isBatchSuccessful = (bool) ($response['status'] ?? false);
                $batchId = $response['message_id'] ?? null;

                $logEntries = [];
                $now = Carbon::now();

                foreach ($validRecipientMap as $phone => $meta) {
                    /** @var SmsCampaignRecipient $recModel */
                    $recModel = $meta['recipient'];

                    if ($isBatchSuccessful) {
                        $recModel->update([
                            'sms_status'     => 'sent',
                            'sms_message_id' => $batchId,
                            'processed_at'   => $now,
                        ]);
                        $successCount++;
                        $batchCost += $meta['cost'];
                    } else {
                        $recModel->update([
                            'sms_status'     => 'failed',
                            'failure_reason' => $response['message'] ?? 'Batch gateway rejection',
                            'processed_at'   => $now,
                        ]);
                        $failedCount++;
                    }

                    // Prepare consolidated audit log entry
                    $logEntries[] = [
                        'recipient'        => $phone,
                        'user_id'          => $recModel->customer_id ?? null,
                        'campaign_id'      => $campaign->id,
                        'sms_type'         => 'marketing',
                        'provider'         => $campaign->provider?->driver ?? 'onecodesoft',
                        'sender_id'        => $campaign->sender_id ?: 'Default',
                        'message_id'       => $batchId,
                        'message_content'  => $meta['text'],
                        'characters_count' => $meta['chars'],
                        'sms_parts'        => $meta['parts'],
                        'cost'             => $isBatchSuccessful ? $meta['cost'] : 0.0000,
                        'status'           => $isBatchSuccessful ? 'sent' : 'failed',
                        'gateway_response' => json_encode($response),
                        'is_sandbox'       => (bool) ($campaign->provider?->sandbox_mode ?? false),
                        'sent_at'          => $isBatchSuccessful ? $now : null,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ];
                }

                if (!empty($logEntries)) {
                    try {
                        SmsLog::insert($logEntries);
                    } catch (Throwable $e) {
                        Log::warning('SmsLog batch insert error in campaign: ' . $e->getMessage());
                    }
                }

                // 4. Increment aggregated campaign stats
                $this->incrementCampaignCounters($campaign, $processedCount, $successCount, $failedCount, $batchCost);
            });

        // Finalize campaign state
        $campaign->refresh();
        $successful = (int) ($campaign->successful_count ?? $campaign->sent_count ?? 0);
        $finalStatus = ($campaign->failed_count > 0 && $successful === 0) ? 'failed' : 'completed';

        $campaign->update([
            'status'       => $finalStatus,
            'completed_at' => Carbon::now(),
        ]);
    }

    /**
     * Check if a setting toggle is enabled safely
     */
    protected function isSettingEnabled(string $key): bool
    {
        if (class_exists(SmsSetting::class)) {
            if (method_exists(SmsSetting::class, 'isEnabled')) {
                return (bool) SmsSetting::isEnabled($key, true);
            }
            if (Schema::hasTable('sms_settings')) {
                $setting = SmsSetting::where('key', $key)->first();
                if ($setting) {
                    return !in_array((string) $setting->value, ['0', 'false', 'off'], true);
                }
            }
        }
        return true;
    }

    /**
     * Check if phone number is blacklisted safely
     */
    protected function isPhoneBlacklisted(string $phone): bool
    {
        try {
            if (class_exists(SmsBlacklist::class) && method_exists(SmsBlacklist::class, 'isPhoneBlocked')) {
                return SmsBlacklist::isPhoneBlocked($phone);
            }
        } catch (Throwable $e) {
            Log::warning('SmsBlacklist check bypassed in campaign job: ' . $e->getMessage());
        }
        return false;
    }

    /**
     * Check if customer can receive marketing SMS without crashing if column is missing
     */
    protected function canReceiveMarketing(string $phone): bool
    {
        try {
            if (class_exists(CommunicationPreference::class)) {
                if (method_exists(CommunicationPreference::class, 'canReceiveMarketingSms')) {
                    return (bool) CommunicationPreference::canReceiveMarketingSms($phone);
                }
                if (Schema::hasTable('communication_preferences') && Schema::hasColumn('communication_preferences', 'sms_marketing')) {
                    $phoneCol = Schema::hasColumn('communication_preferences', 'phone') ? 'phone' : 'phone_number';
                    $pref = CommunicationPreference::where($phoneCol, $phone)->first();
                    return $pref ? (bool) $pref->sms_marketing : true;
                }
            }
        } catch (Throwable $e) {
            Log::warning('CommunicationPreference check bypassed in campaign: ' . $e->getMessage());
        }
        return true;
    }

    /**
     * Safely increment campaign counters matching table schema
     */
    protected function incrementCampaignCounters(SmsCampaign $campaign, int $processed, int $success, int $failed, float $cost): void
    {
        if (Schema::hasColumn('sms_campaigns', 'processed_recipients')) {
            $campaign->increment('processed_recipients', $processed);
        }
        if (Schema::hasColumn('sms_campaigns', 'successful_count')) {
            $campaign->increment('successful_count', $success);
        }
        if (Schema::hasColumn('sms_campaigns', 'sent_count')) {
            $campaign->increment('sent_count', $success);
        }
        if (Schema::hasColumn('sms_campaigns', 'failed_count')) {
            $campaign->increment('failed_count', $failed);
        }
        if (Schema::hasColumn('sms_campaigns', 'actual_cost')) {
            $campaign->increment('actual_cost', $cost);
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(?Throwable $exception): void
    {
        $errorMsg = $exception ? $exception->getMessage() : 'Unknown fatal campaign execution error';
        Log::critical("ProcessBulkSmsCampaignJob failed for campaign ID: {$this->campaignId}. Error: {$errorMsg}");

        try {
            SmsCampaign::where('id', $this->campaignId)->update([
                'status'       => 'failed',
                'completed_at' => Carbon::now(),
            ]);
        } catch (Throwable $e) {
            Log::error('Could not update SmsCampaign status on failure: ' . $e->getMessage());
        }
    }
}