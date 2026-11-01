<?php

namespace App\Console\Commands;

use App\Jobs\ProcessBulkSmsCampaignJob;
use App\Models\SmsCampaign;
use App\Models\SmsSetting;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledCampaignsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sms:process-scheduled {--limit=10 : Maximum campaigns to queue per run}';

    /**
     * The console command description.
     */
    protected $description = 'Scan and dispatch due scheduled SMS and multi-channel campaigns to queue workers';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // 1. Check Global SMS System Setting
        if (!SmsSetting::isEnabled('global_sms_enabled', true) || !SmsSetting::isEnabled('bulk_sms_enabled', true)) {
            $this->warn('Bulk SMS processing is disabled in system settings. Skipping.');
            return self::SUCCESS;
        }

        $limit = (int) $this->option('limit');
        $now = Carbon::now();

        // 2. Query campaigns due for dispatch
        $dueCampaigns = SmsCampaign::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $now)
            ->orderBy('scheduled_at', 'asc')
            ->limit($limit)
            ->get();

        if ($dueCampaigns->isEmpty()) {
            $this->info('No pending scheduled campaigns due for dispatch.');
            return self::SUCCESS;
        }

        $this->info("Found {$dueCampaigns->count()} scheduled campaign(s) ready for execution.");

        foreach ($dueCampaigns as $campaign) {
            try {
                // Set status to 'queued' atomically to prevent duplicate runs
                $campaign->update([
                    'status' => 'queued',
                ]);

                // Dispatch to dedicated queue
                ProcessBulkSmsCampaignJob::dispatch($campaign->id)->onQueue('sms-campaigns');

                $this->line("• Campaign [ID: {$campaign->id}] '{$campaign->name}' queued successfully.");

                Log::info("Scheduled campaign [ID: {$campaign->id}] dispatched to queue.", [
                    'campaign_name' => $campaign->name,
                    'scheduled_at'  => $campaign->scheduled_at->toDateTimeString(),
                ]);
            } catch (Exception $e) {
                $campaign->update([
                    'status' => 'failed',
                    'description' => $campaign->description . ' [Dispatch Error: ' . $e->getMessage() . ']',
                ]);

                $this->error("Failed to queue campaign [ID: {$campaign->id}]: " . $e->getMessage());
                Log::error("Failed to queue scheduled campaign [ID: {$campaign->id}]: " . $e->getMessage());
            }
        }

        $this->info('Scheduled campaign processing completed.');
        return self::SUCCESS;
    }
}