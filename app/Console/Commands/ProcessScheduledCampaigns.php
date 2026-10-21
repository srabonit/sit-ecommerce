<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailSubscriber;
use App\Jobs\SendCampaignBatchJob;

class ProcessScheduledCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email-marketing:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches scheduled email marketing campaigns that have reached their trigger time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dueCampaigns = EmailCampaign::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($dueCampaigns->isEmpty()) {
            $this->info('No scheduled campaigns due for dispatch.');
            return Command::SUCCESS;
        }

        foreach ($dueCampaigns as $campaign) {
            $this->info("Processing scheduled campaign: {$campaign->name} (#{$campaign->id})");

            // Eligible subscribers query
            $subscribersQuery = EmailSubscriber::eligibleForMarketing();

            if ($campaign->segment_id && $campaign->segment) {
                $subscribers = $campaign->segment->isSnapshot()
                    ? $subscribersQuery->take($campaign->segment->matching_subscribers_count)->get()
                    : $subscribersQuery->get();
            } else {
                $subscribers = $subscribersQuery->get();
            }

            if ($subscribers->isEmpty()) {
                $campaign->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
                $this->warn("No eligible recipients found for campaign #{$campaign->id}. Marked as completed.");
                continue;
            }

            // Update campaign status
            $campaign->update([
                'status'           => 'sending',
                'started_at'       => now(),
                'total_recipients' => $subscribers->count(),
            ]);

            // Bulk prepare recipient records
            $recipientsData = [];
            foreach ($subscribers as $sub) {
                $recipientsData[] = [
                    'campaign_id'    => $campaign->id,
                    'subscriber_id'  => $sub->id,
                    'email'          => $sub->email,
                    'status'         => 'queued',
                    'tracking_token' => Str::random(64),
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }

            // Insert recipients in chunks
            foreach (array_chunk($recipientsData, 500) as $chunk) {
                EmailCampaignRecipient::insertOrIgnore($chunk);
            }

            // Dispatch recipient IDs to background Queue Batches
            $batchSize = $campaign->batch_size ?: 50;
            EmailCampaignRecipient::where('campaign_id', $campaign->id)
                ->where('status', 'queued')
                ->chunkById($batchSize, function ($recipientChunk) use ($campaign) {
                    $recipientIds = $recipientChunk->pluck('id')->toArray();
                    SendCampaignBatchJob::dispatch($campaign->id, $recipientIds)->onQueue('emails');
                });

            $this->info("Queued {$subscribers->count()} recipients for campaign: {$campaign->name}");
        }

        return Command::SUCCESS;
    }
}