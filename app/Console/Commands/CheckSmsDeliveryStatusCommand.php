<?php

namespace App\Console\Commands;

use App\Jobs\CheckSmsDeliveryStatusJob;
use App\Models\SmsLog;
use Illuminate\Console\Command;

class CheckSmsDeliveryStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sms:check-delivery 
                            {--limit=100 : Maximum number of pending logs to inspect} 
                            {--sync : Run check immediately in the foreground instead of queuing}';

    /**
     * The console command description.
     */
    protected $description = 'Poll gateway delivery status reports (DLR) for pending sent SMS logs and update delivery timestamps';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $sync = (bool) $this->option('sync');

        $pendingQuery = SmsLog::where('status', 'sent')
            ->whereNotNull('message_id')
            ->where('created_at', '>=', now()->subHours(24));

        $pendingCount = $pendingQuery->count();

        if ($pendingCount === 0) {
            $this->info('No recent sent SMS logs awaiting delivery verification.');
            return self::SUCCESS;
        }

        $this->info("Found {$pendingCount} message(s) awaiting delivery confirmation.");

        if ($sync) {
            $this->info("Processing delivery status checks synchronously (limit: {$limit})...");
            (new CheckSmsDeliveryStatusJob(null, $limit))->handle();
            $this->info('Foreground delivery status verification completed.');
        } else {
            $this->info("Dispatching delivery verification job to queue [sms-dlr]...");
            CheckSmsDeliveryStatusJob::dispatch(null, $limit)->onQueue('sms-dlr');
            $this->info('Job successfully queued.');
        }

        return self::SUCCESS;
    }
}