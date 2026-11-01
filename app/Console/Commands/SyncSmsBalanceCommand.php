<?php

namespace App\Console\Commands;

use App\Models\SmsProvider;
use App\Models\SmsSetting;
use App\Services\Sms\SmsService;
use Exception;
use Illuminate\Console\Command;

class SyncSmsBalanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sms:sync-balance {--provider= : Specific provider ID to sync}';

    /**
     * The console command description.
     */
    protected $description = 'Sync real-time gateway balances for active SMS providers and verify balance health';

    /**
     * Execute the console command.
     */
    public function handle(SmsService $smsService): int
    {
        $providerId = $this->option('provider');

        $query = SmsProvider::where('is_active', true);
        if ($providerId) {
            $query->where('id', $providerId);
        }

        $providers = $query->get();

        if ($providers->isEmpty()) {
            $this->warn('No active SMS providers found to sync.');
            return self::SUCCESS;
        }

        $this->info("Initiating balance sync for {$providers->count()} provider(s)...");

        $tableRows = [];
        $lowThreshold = (float) SmsSetting::getVal('low_balance_threshold', 50.00);

        foreach ($providers as $provider) {
            try {
                $smsService->setProvider($provider);
                $result = $smsService->syncBalance();

                if ($result['status'] ?? false) {
                    $balance = (float) ($result['balance'] ?? 0.00);
                    $estimatedSms = (int) ($result['estimated_sms'] ?? 0);
                    $currency = $result['currency'] ?? 'BDT';
                    $isLow = $balance <= $lowThreshold;

                    $tableRows[] = [
                        'ID'            => $provider->id,
                        'Name'          => $provider->name,
                        'Driver'        => $provider->driver,
                        'Balance'       => number_format($balance, 2) . ' ' . $currency,
                        'Est. SMS'      => number_format($estimatedSms),
                        'Status'        => $isLow ? '<fg=red>LOW BALANCE</>' : '<fg=green>HEALTHY</>',
                        'Last Synced'   => now()->toDateTimeString(),
                    ];
                } else {
                    $tableRows[] = [
                        'ID'            => $provider->id,
                        'Name'          => $provider->name,
                        'Driver'        => $provider->driver,
                        'Balance'       => 'N/A',
                        'Est. SMS'      => 'N/A',
                        'Status'        => '<fg=red>FAILED: ' . ($result['message'] ?? 'Error') . '</>',
                        'Last Synced'   => $provider->last_balance_synced_at?->toDateTimeString() ?? 'Never',
                    ];
                }
            } catch (Exception $e) {
                $tableRows[] = [
                    'ID'            => $provider->id,
                    'Name'          => $provider->name,
                    'Driver'        => $provider->driver,
                    'Balance'       => 'ERROR',
                    'Est. SMS'      => 'ERROR',
                    'Status'        => '<fg=red>' . $e->getMessage() . '</>',
                    'Last Synced'   => 'Error',
                ];
            }
        }

        $this->table(
            ['ID', 'Name', 'Driver', 'Balance', 'Est. SMS', 'Health Status', 'Last Synced'],
            $tableRows
        );

        $this->info('SMS balance synchronization completed.');
        return self::SUCCESS;
    }
}