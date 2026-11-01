<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TaxReconciliationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class ProcessTaxPeriodClose extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tax:close-period 
                            {--period= : নির্দিষ্ট পিরিয়ড কী (ফরম্যাট: YYYY-MM, যেমন: 2026-09)} 
                            {--force : সেটিংস কনফিগারেশন চেক বাইপাস করে সরাসরি ক্লোজ করার ফ্ল্যাগ}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'পূর্ববর্তী মাসের ভ্যাট হিসাব সম্পন্ন করে ট্যাক্স পিরিয়ড লক ও স্ন্যাপশট সংরক্ষণ করে';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting automated tax period reconciliation and closing process...');

        // ১. অটো-লক সেটিংস পরীক্ষা
        $isForce = $this->option('force');
        if (!$isForce && Schema::hasTable('settings')) {
            $autoLockSetting = DB::table('settings')->where('key', 'auto_lock_tax_periods')->value('value');
            $isAutoLockEnabled = filter_var($autoLockSetting, FILTER_VALIDATE_BOOLEAN);

            if (!$isAutoLockEnabled) {
                $this->warn('Notice: "auto_lock_tax_periods" is disabled in settings. Skipping automatic close.');
                $this->line('Use the --force option to close the period manually regardless of settings.');
                return Command::SUCCESS;
            }
        }

        // ২. ক্লোজিং পিরিয়ড নির্ধারণ (ডিফল্ট: বিগত মাস)
        $periodKey = $this->option('period');
        if (empty($periodKey)) {
            $periodKey = Carbon::now()->subMonth()->format('Y-m');
        }

        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periodKey)) {
            $this->error("Invalid period format '{$periodKey}'. Expected format: YYYY-MM (2026-09)");
            return Command::FAILURE;
        }

        $this->line("Target Tax Period: <fg=cyan>{$periodKey}</>");

        // ৩. পূর্বেই লক করা আছে কিনা যাচাই
        if (Schema::hasTable('tax_periods')) {
            $existing = DB::table('tax_periods')->where('period_key', $periodKey)->first();
            if ($existing && $existing->is_locked) {
                $this->warn("Tax period '{$periodKey}' is already closed and locked at {$existing->locked_at}.");
                return Command::SUCCESS;
            }
        }

        // ৪. রিকনসিলিয়েশন ও পিরিয়ড ক্লোজিং এক্সিকিউশন
        $remarks = "Automated monthly close executed via scheduler command at " . now()->toDateTimeString();
        $result = TaxReconciliationService::closePeriod($periodKey, null, $remarks);

        if (!$result['success']) {
            $this->error("Failed to close period: " . ($result['message'] ?? 'Unknown error'));
            Log::error("Automated Tax Close Failed for period {$periodKey}: " . ($result['message'] ?? ''));
            return Command::FAILURE;
        }

        // ৫. সামারি রিপোর্ট কনসোলে ডিসপ্লে
        $summary = $result['summary'];
        $this->info("✓ Tax Period [{$periodKey}] successfully closed and locked.");

        $this->newLine();
        $this->table(
            ['Metric', 'Value'],
            [
                ['Tax Period', $summary['period']['month_name'] . " ({$periodKey})"],
                ['Total Delivered Orders', number_format($summary['sales']['total_orders'])],
                ['Gross Sales', 'BDT ' . number_format($summary['sales']['gross_sales'], 2)],
                ['Taxable Base Amount', 'BDT ' . number_format($summary['sales']['taxable_base'], 2)],
                ['Gross VAT Collected', 'BDT ' . number_format($summary['output_vat']['gross_vat'], 2)],
                ['Adjusted / Reversed VAT', 'BDT ' . number_format($summary['output_vat']['reversed_vat'], 2)],
                ['Net VAT Liability', 'BDT ' . number_format($summary['output_vat']['net_vat'], 2)],
                ['Treasury Deposited', 'BDT ' . number_format($summary['treasury']['deposited'], 2)],
                ['Pending Challan Amount', 'BDT ' . number_format($summary['treasury']['pending'], 2)],
                ['Net Balance Payable / (Due)', 'BDT ' . number_format($summary['treasury']['balance_due'], 2)],
                ['Advance Balance Carried', 'BDT ' . number_format($summary['treasury']['advance_paid'], 2)],
            ]
        );

        Log::info("Tax period {$periodKey} successfully locked via scheduled cron command.", [
            'period_key'        => $periodKey,
            'net_vat_liability' => $summary['output_vat']['net_vat'],
            'treasury_paid'     => $summary['treasury']['deposited'],
        ]);

        return Command::SUCCESS;
    }
}