<?php

namespace App\Jobs;

use App\Models\SmsLog;
use App\Services\Sms\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendSingleSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public array $backoff = [10, 30, 60];

    /**
     * Delete the job if its models no longer exist.
     */
    public bool $deleteWhenMissingModels = true;

    public string $number;
    public ?string $message;
    public string $type;
    public array $options;
    public ?string $templateKey;
    public array $templateVariables;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $number,
        ?string $message = null,
        string $type = 'transactional',
        array $options = [],
        ?string $templateKey = null,
        array $templateVariables = []
    ) {
        $this->number            = trim($number);
        $this->message           = $message;
        $this->type              = $type;
        $this->options           = $options;
        $this->templateKey       = $templateKey ? trim($templateKey) : null;
        $this->templateVariables = $templateVariables;

        // Default to dedicated sms-notifications queue if not pre-specified
        if (empty($this->queue)) {
            $this->onQueue('sms-notifications');
        }
    }

    /**
     * Execute the job.
     */
    public function handle(SmsService $smsService): void
    {
        try {
            if (!empty($this->templateKey)) {
                $result = $smsService->sendTemplate(
                    $this->templateKey,
                    $this->number,
                    $this->templateVariables,
                    $this->options
                );
            } else {
                $result = $smsService->send(
                    $this->number,
                    $this->message ?? '',
                    $this->type,
                    $this->options
                );
            }

            if (!($result['status'] ?? false)) {
                $reason = $result['message'] ?? 'SMS delivery rejected or failed';
                Log::warning("SendSingleSmsJob dispatch warning: {$reason}", [
                    'recipient' => $this->number,
                    'template'  => $this->templateKey,
                    'result'    => $result,
                ]);
            }
        } catch (Throwable $e) {
            Log::error("SendSingleSmsJob attempt {$this->attempts()} failed: " . $e->getMessage(), [
                'recipient' => $this->number,
                'template'  => $this->templateKey,
            ]);

            // Re-throw to trigger Laravel backoff retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a permanent job failure after exhausting all attempts.
     */
    public function failed(?Throwable $exception): void
    {
        $errorMsg = $exception ? $exception->getMessage() : 'Exhausted maximum retry attempts';

        Log::critical("SendSingleSmsJob permanently failed for {$this->number} after {$this->tries} attempts.", [
            'recipient' => $this->number,
            'template'  => $this->templateKey,
            'error'     => $errorMsg,
        ]);

        // If an audit log entry ID was passed in options, mark it failed permanently
        if (!empty($this->options['log_id'])) {
            try {
                SmsLog::where('id', $this->options['log_id'])->update([
                    'status'           => 'failed',
                    'gateway_response' => json_encode(['error' => 'Queue permanent failure: ' . $errorMsg]),
                ]);
            } catch (Throwable $e) {
                Log::warning('Could not update SmsLog on SendSingleSmsJob permanent failure: ' . $e->getMessage());
            }
        }
    }
}