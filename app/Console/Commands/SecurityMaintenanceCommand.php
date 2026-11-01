<?php

namespace App\Console\Commands;

use App\Models\SecurityBlockedIp;
use App\Models\SecurityBlockedNetwork;
use App\Models\SecurityIpLog;
use App\Models\SecurityUserRestriction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SecurityMaintenanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:maintenance {--retention-days=30}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release expired security blocks and safely purge old activity logs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();

        // ১. মেয়াদোত্তীর্ণ IP ব্লক স্বয়ংক্রিয়ভাবে রিলিজ
        $expiredIps = SecurityBlockedIp::where('is_active', true)
            ->where('block_type', 'temporary')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->get();

        foreach ($expiredIps as $ip) {
            $ip->update([
                'is_active'  => false,
                'admin_note' => trim(($ip->admin_note ?? '') . ' | Auto-released: Expiry reached.'),
            ]);
            Cache::forget('sec_blocked_ip_' . $ip->ip_address);
        }

        // ২. মেয়াদোত্তীর্ণ CIDR নেটওয়ার্ক সাবনেট রিলিজ
        $expiredNetworks = SecurityBlockedNetwork::where('is_active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->get();

        foreach ($expiredNetworks as $net) {
            $net->update([
                'is_active'  => false,
                'admin_note' => trim(($net->admin_note ?? '') . ' | Auto-released: Subnet expiry reached.'),
            ]);
        }
        if ($expiredNetworks->isNotEmpty()) {
            Cache::forget('security_active_networks');
        }

        // ৩. মেয়াদোত্তীর্ণ ইউজার রেস্ট্রিকশন প্রত্যাহার
        $expiredUsers = SecurityUserRestriction::where('is_active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->get();

        foreach ($expiredUsers as $userRes) {
            $userRes->update([
                'is_active'  => false,
                'admin_note' => trim(($userRes->admin_note ?? '') . ' | Auto-lifted: Time limit elapsed.'),
            ]);
            Cache::forget('sec_user_restriction_' . $userRes->user_id);
        }

        // ৪. সাধারণ লগ ক্লিনআপ (হাইফেন ম্যাচ ফিক্সড)
        $days = (int) ($this->option('retention-days') ?: 30);
        SecurityIpLog::where('created_at', '<', Carbon::now()->subDays($days))
            ->where('security_status', '!=', 'blocked')
            ->where('risk_score', '<', 50)
            ->delete();

        $this->info('Security maintenance completed successfully.');

        return Command::SUCCESS;
    }
}