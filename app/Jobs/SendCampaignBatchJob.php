<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailSubscriber;
use App\Models\EmailSuppressionList;
use App\Mail\CampaignMailable;

class SendCampaignBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $campaignId;
    public $recipientIds;

    public $tries = 3;
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(int $campaignId, array $recipientIds)
    {
        $this->campaignId   = $campaignId;
        $this->recipientIds = $recipientIds;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $campaign = EmailCampaign::with('template', 'coupon')->find($this->campaignId);
        if (!$campaign || in_array($campaign->status, ['paused', 'cancelled'])) {
            return;
        }

        $recipients = EmailCampaignRecipient::whereIn('id', $this->recipientIds)
            ->where('status', 'queued')
            ->get();

        foreach ($recipients as $recipient) {
            // Re-check suppression blacklist
            if (EmailSuppressionList::isSuppressed($recipient->email)) {
                $recipient->update([
                    'status'        => 'bounced',
                    'error_message' => 'Email address is on suppression blacklist.',
                ]);
                $campaign->increment('bounced_count');
                continue;
            }

            $subscriber = EmailSubscriber::find($recipient->subscriber_id);

            try {
                Mail::to($recipient->email)->send(new CampaignMailable($campaign, $recipient, $subscriber));

                $recipient->update([
                    'status'  => 'sent',
                    'sent_at' => now(),
                ]);

                $campaign->increment('sent_count');
            } catch (\Throwable $e) {
                $recipient->update([
                    'status'        => 'failed',
                    'error_message' => substr($e->getMessage(), 0, 250),
                ]);

                $campaign->increment('bounced_count');
            }

            // Small throttle sleep (20ms) to prevent SMTP rate-limit flooding
            usleep(20000);
        }

        // Check if all recipients for this campaign have completed
        $remaining = EmailCampaignRecipient::where('campaign_id', $campaign->id)
            ->where('status', 'queued')
            ->count();

        if ($remaining === 0) {
            $campaign->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);
        }
    }
}