<?php

namespace App\Http\Controllers\Admin\Tax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\TaxReconciliationService;
use App\Services\ActivityLogger;

class TaxPeriodController extends Controller
{
    /**
     * Display list of tax periods (locked and active) along with current period status
     */
    public function index(Request $request)
    {
        $currentMonthKey = Carbon::now()->format('Y-m');
        $prevMonthKey    = Carbon::now()->subMonth()->format('Y-m');

        // Current ongoing month live reconciliation
        $currentReconciliation = TaxReconciliationService::getPeriodReconciliation(
            Carbon::now()->startOfMonth()->toDateString(),
            Carbon::now()->endOfMonth()->toDateString()
        );

        $periodsQuery = DB::table('tax_periods')->orderByDesc('period_key');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $periodsQuery->where(function ($q) use ($s) {
                $q->where('period_key', 'like', "%{$s}%")
                  ->orWhere('period_name', 'like', "%{$s}%")
                  ->orWhere('remarks', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'locked') {
                $periodsQuery->where('is_locked', true);
            } elseif ($request->status === 'open') {
                $periodsQuery->where('is_locked', false);
            }
        }

        $periods = $periodsQuery->paginate(15)->withQueryString();

        // Calculate all-time closed metrics
        $allTimeStats = DB::table('tax_periods')
            ->selectRaw("
                COUNT(*) as total_closed_periods,
                SUM(taxable_amount) as total_taxable_base,
                SUM(tax_collected) as total_vat_collected,
                SUM(tax_refunded) as total_vat_refunded,
                SUM(net_tax_liability) as total_net_liability,
                SUM(treasury_paid) as total_treasury_deposited
            ")
            ->first();

        return view('admin.tax.periods.index', compact(
            'periods',
            'currentReconciliation',
            'currentMonthKey',
            'prevMonthKey',
            'allTimeStats'
        ));
    }

    /**
     * Lock and close a specific monthly tax period from Web UI
     */
    public function close(Request $request)
    {
        $request->validate([
            'period_key' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'remarks'    => 'nullable|string|max:500',
        ]);

        $periodKey = $request->input('period_key');
        $remarks   = $request->input('remarks', 'Manually closed by ' . (Auth::user()->name ?? 'Admin'));

        $result = TaxReconciliationService::closePeriod($periodKey, Auth::id(), $remarks);

        if (!$result['success']) {
            return back()->with('error', $result['message'] ?? 'পিরিয়ড লক করতে ব্যর্থ হয়েছে।');
        }

        if (class_exists(ActivityLogger::class)) {
            ActivityLogger::log('close', 'tax_period', "Tax period [{$periodKey}] locked and archived", [
                'period_key' => $periodKey,
                'summary'    => $result['summary']['output_vat'] ?? [],
            ]);
        }

        return redirect()->route('admin.tax.periods.index')
            ->with('success', "ভ্যাট পিরিয়ড [{$periodKey}] সফলভাবে হিসাব সম্পন্ন করে লক করা হয়েছে।");
    }

    /**
     * Show detailed closing snapshot JSON / breakdown for a period
     */
    public function show($id)
    {
        $period = DB::table('tax_periods')->where('id', $id)->first();
        if (!$period) {
            return response()->json(['success' => false, 'message' => 'Record not found.'], 404);
        }

        $snapshot = [];
        if (!empty($period->closing_snapshot)) {
            $snapshot = json_decode($period->closing_snapshot, true) ?? [];
        }

        $adminName = 'System Scheduler';
        if (!empty($period->locked_by) && Schema::hasTable('users')) {
            $adminName = DB::table('users')->where('id', $period->locked_by)->value('name') ?? $adminName;
        }

        return response()->json([
            'success'   => true,
            'period'    => $period,
            'snapshot'  => $snapshot,
            'locked_by' => $adminName,
        ]);
    }

    /**
     * Reopen / Unlock a locked tax period (Super Admin Access Only)
     */
    public function unlock(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || !method_exists($user, 'isSuperAdmin') || !$user->isSuperAdmin()) {
            return back()->with('error', 'শুধুমাত্র সুপার অ্যাডমিন বন্ধ থাকা ভ্যাট পিরিয়ড পুনরায় উন্মুক্ত করতে পারেন।');
        }

        $period = DB::table('tax_periods')->where('id', $id)->first();
        if (!$period) {
            return back()->with('error', 'পিরিয়ড রেকর্ডটি পাওয়া যায়নি।');
        }

        DB::table('tax_periods')->where('id', $id)->update([
            'is_locked'  => false,
            'remarks'    => trim(($period->remarks ?? '') . " | Reopened by {$user->name} on " . now()->format('d M Y, h:i A')),
            'updated_at' => now(),
        ]);

        if (class_exists(ActivityLogger::class)) {
            ActivityLogger::log('unlock', 'tax_period', "Tax period [{$period->period_key}] unlocked by Super Admin", [
                'period_id'  => $id,
                'period_key' => $period->period_key,
            ]);
        }

        return back()->with('success', "ভ্যাট পিরিয়ড [{$period->period_key}] সফলভাবে রি-ওপেন করা হয়েছে।");
    }
}