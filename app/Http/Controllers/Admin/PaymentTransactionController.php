<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\PaymentVerificationHistory;
use App\Services\PaymentTransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PaymentTransactionController extends Controller
{
    /**
     * File Upload Helper for Manual Transaction Attachments
     */
    protected function saveAttachmentFile($file, string $subFolder = 'payment_proofs'): string
    {
        $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $relativeDir = trim($subFolder, '/');

        if (is_dir(base_path('../public_html'))) {
            $baseDir = base_path('../public_html/uploads/' . $relativeDir);
        } else {
            $baseDir = public_path('uploads/' . $relativeDir);
        }

        if (!file_exists($baseDir)) {
            @mkdir($baseDir, 0777, true);
        }

        $file->move($baseDir, $filename);

        return 'uploads/' . $relativeDir . '/' . $filename;
    }

    /**
     * Resolve Per-Page Pagination Options (20, 40, 60, 100, 200, 500, All with safe cap)
     */
    protected function resolvePerPage(Request $request, int $default = 20): int
    {
        $perPage = $request->input('per_page', $default);
        if ($perPage === 'all' || (int) $perPage >= 10000) {
            return 5000;
        }
        $val = (int) $perPage;
        return in_array($val, [20, 40, 60, 100, 200, 500]) ? $val : $default;
    }

    /**
     * Shortcut Section Entrypoints
     */
    public function dashboard(Request $request)
    {
        $request->merge(['section' => 'dashboard']);
        return $this->index($request);
    }

    public function checkoutRequests(Request $request)
    {
        $request->merge(['section' => 'checkout-requests']);
        return $this->index($request);
    }

    public function posCashOrders(Request $request)
    {
        $request->merge(['section' => 'pos-cash']);
        return $this->index($request);
    }

    public function codOrders(Request $request)
    {
        $request->merge(['section' => 'cod-orders']);
        return $this->index($request);
    }

    public function manualTransactions(Request $request)
    {
        $request->merge(['section' => 'manual']);
        return $this->index($request);
    }

    public function uncheckedTransactions(Request $request)
    {
        $request->merge(['section' => 'unchecked']);
        return $this->index($request);
    }

    public function verificationHistory(Request $request)
    {
        $request->merge(['section' => 'history']);
        return $this->index($request);
    }

    public function reports(Request $request)
    {
        $request->merge(['section' => 'reports']);
        return $this->index($request);
    }

    public function trashedTransactions(Request $request)
    {
        $request->merge(['section' => 'trash']);
        return $this->index($request);
    }

    /**
     * ১. প্রধান ট্রানজেকশন ম্যানেজমেন্ট ও মাল্টি-সেকশন হ্যান্ডলার (AJAX Filter, Summary, Pagination & Export)
     */
    public function index(Request $request, ?string $sectionParam = null)
    {
        PaymentTransactionService::ensureTrashColumnExists();

        $activeSection = $sectionParam ?: $request->input('section', 'all');
        $user = Auth::user();

        // যদি Audit History সেকশন হয় তবে ভেরিফিকেশন হিস্ট্রি টেবিল প্রদর্শন
        if ($activeSection === 'history') {
            return $this->renderVerificationHistorySection($request, $activeSection);
        }

        // Base Query
        $query = DB::table('payment_transactions')
            ->leftJoin('orders', 'payment_transactions.order_id', '=', 'orders.id')
            ->leftJoin('branches', 'payment_transactions.branch_id', '=', 'branches.id')
            ->leftJoin('users as checker', 'payment_transactions.checked_by', '=', 'checker.id')
            ->leftJoin('users as verifier', 'payment_transactions.verified_by', '=', 'verifier.id')
            ->leftJoin('users as updater', 'payment_transactions.updated_by', '=', 'updater.id');

        // ব্রাঞ্চ ম্যানেজার/স্টাফ হলে তার নির্ধারিত ব্রাঞ্চের ট্রানজেকশন ফিল্টার
        if (!$user->isSuperAdmin() && strtolower((string) $user->role) !== 'admin' && !empty($user->branch_id)) {
            $query->where('payment_transactions.branch_id', $user->branch_id);
        }

        // Trash vs Active Isolation (যাতে ডুপ্লিকেট বা রিপ্লেস হওয়া পুরোনো ট্রানজেকশন কখনোই মূল হিসাবে না আসে)
        if ($activeSection === 'trash') {
            $query->whereNotNull('payment_transactions.deleted_at');
        } else {
            $query->whereNull('payment_transactions.deleted_at');
        }

        // সেকশন অনুযায়ী বেস ফিল্টারিং (১০০% একক শর্ত যাতে ব্যাজ ও টেবিল কখনো ভিন্ন না দেখায়)
        switch ($activeSection) {
            case 'checkout-requests':
                $query->whereIn('payment_transactions.source_type', [
                    PaymentTransaction::SOURCE_CHECKOUT,
                    PaymentTransaction::SOURCE_GATEWAY,
                ]);
                break;

            case 'pos-cash':
                $query->where('payment_transactions.source_type', PaymentTransaction::SOURCE_POS_CASH);
                break;

            case 'cod-orders':
                $query->where('payment_transactions.source_type', PaymentTransaction::SOURCE_COD);
                break;

            case 'manual':
                $query->where('payment_transactions.source_type', PaymentTransaction::SOURCE_MANUAL);
                break;

            case 'unchecked':
                $query->whereIn('payment_transactions.verification_status', [
                    PaymentTransaction::STATUS_PENDING_VERIFICATION,
                    PaymentTransaction::STATUS_UNDER_REVIEW,
                ]);
                break;
        }

        // =====================================================================
        // MULTI-DIMENSIONAL AJAX FILTERS
        // =====================================================================

        // ১. Source Type Filter
        if ($request->filled('source_type') && $request->source_type !== 'all') {
            $query->where('payment_transactions.source_type', $request->source_type);
        }

        // ২. Payment Method Filter
        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $pm = strtolower(trim($request->payment_method));
            $query->where(function ($q) use ($pm) {
                $q->whereRaw('LOWER(payment_transactions.payment_method) = ?', [$pm])
                  ->orWhere('payment_transactions.payment_method', 'like', "%{$pm}%");
            });
        }

        // ৩. Verification Status / Quick Filter
        if ($request->filled('verification_status') && $request->verification_status !== 'all') {
            $vs = strtolower(trim($request->verification_status));
            if ($vs === 'unchecked') {
                $query->whereIn('payment_transactions.verification_status', [
                    PaymentTransaction::STATUS_PENDING_VERIFICATION,
                    PaymentTransaction::STATUS_UNDER_REVIEW,
                ]);
            } elseif ($vs === 'unpaid') {
                $query->whereNotIn('payment_transactions.verification_status', [
                    PaymentTransaction::STATUS_PAID,
                    PaymentTransaction::STATUS_PARTIALLY_PAID,
                ]);
            } elseif ($vs === 'duplicate') {
                $query->where('payment_transactions.is_duplicate_flagged', true);
            } else {
                $query->where('payment_transactions.verification_status', $vs);
            }
        }

        // ৪. Order Status Filter
        if ($request->filled('order_status') && $request->order_status !== 'all') {
            $query->where('orders.order_status', strtolower(trim($request->order_status)));
        }

        // ৫. Checked / Verified By Admin/Staff Filter
        if ($request->filled('checked_by') && $request->checked_by !== 'all') {
            $staffId = (int) $request->checked_by;
            $query->where(function ($q) use ($staffId) {
                $q->where('payment_transactions.checked_by', $staffId)
                  ->orWhere('payment_transactions.verified_by', $staffId)
                  ->orWhere('payment_transactions.updated_by', $staffId);
            });
        }

        // ৬. Branch Filter
        if ($request->filled('branch_id') && $request->branch_id !== 'all') {
            $query->where('payment_transactions.branch_id', (int) $request->branch_id);
        }

        // ৭. Amount Range Filter (Min & Max)
        if ($request->filled('min_amount') && is_numeric($request->min_amount)) {
            $query->where('payment_transactions.expected_amount', '>=', (float) $request->min_amount);
        }
        if ($request->filled('max_amount') && is_numeric($request->max_amount)) {
            $query->where('payment_transactions.expected_amount', '<=', (float) $request->max_amount);
        }

        // ৮. Date Range, Presets, Month & Year Filters
        if ($request->filled('date_preset') && $request->date_preset !== 'all') {
            $now = Carbon::now();
            switch ($request->date_preset) {
                case 'today':
                    $query->whereDate('payment_transactions.created_at', Carbon::today());
                    break;
                case 'yesterday':
                    $query->whereDate('payment_transactions.created_at', Carbon::yesterday());
                    break;
                case 'last_3_days':
                    $query->where('payment_transactions.created_at', '>=', $now->copy()->subDays(3)->startOfDay());
                    break;
                case 'this_week':
                    $query->whereBetween('payment_transactions.created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereBetween('payment_transactions.created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
                    break;
                case 'last_month':
                    $query->whereBetween('payment_transactions.created_at', [
                        $now->copy()->subMonth()->startOfMonth(),
                        $now->copy()->subMonth()->endOfMonth()
                    ]);
                    break;
                case 'custom':
                    if ($request->filled('start_date')) {
                        $query->where('payment_transactions.created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
                    }
                    if ($request->filled('end_date')) {
                        $query->where('payment_transactions.created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
                    }
                    break;
            }
        } else {
            if ($request->filled('start_date')) {
                $query->where('payment_transactions.created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
            }
            if ($request->filled('end_date')) {
                $query->where('payment_transactions.created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
            }
        }

        // Specific Month & Year Filter
        if ($request->filled('filter_month') && $request->filter_month !== 'all') {
            $query->whereMonth('payment_transactions.created_at', (int) $request->filter_month);
        }
        if ($request->filled('filter_year') && $request->filter_year !== 'all') {
            $query->whereYear('payment_transactions.created_at', (int) $request->filter_year);
        }

        // Specific Verification Date Filter
        if ($request->filled('verification_date')) {
            $vDate = Carbon::parse($request->verification_date)->toDateString();
            $query->where(function ($q) use ($vDate) {
                $q->whereDate('payment_transactions.verified_at', $vDate)
                  ->orWhereDate('payment_transactions.checked_at', $vDate);
            });
        }

        // ৯. Specific Field Searches & Global Search
        if ($request->filled('trx_id')) {
            $normSearch = PaymentTransaction::normalizeTrxId($request->trx_id);
            $rawSearch  = trim($request->trx_id);
            $query->where(function ($q) use ($normSearch, $rawSearch) {
                $q->where('payment_transactions.transaction_id', 'like', "%{$rawSearch}%");
                if ($normSearch) {
                    $q->orWhere('payment_transactions.normalized_trx_id', 'like', "%{$normSearch}%");
                }
            });
        }

        if ($request->filled('sender_number')) {
            $snd = trim($request->sender_number);
            $query->where('payment_transactions.sender_number', 'like', "%{$snd}%");
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $cleanS = ltrim($s, '#');
            $normS  = PaymentTransaction::normalizeTrxId($s);

            $query->where(function ($q) use ($s, $cleanS, $normS) {
                $q->where('payment_transactions.transaction_uid', 'like', "%{$s}%")
                  ->orWhere('payment_transactions.transaction_id', 'like', "%{$s}%")
                  ->orWhere('payment_transactions.sender_number', 'like', "%{$s}%")
                  ->orWhere('payment_transactions.invoice_no', 'like', "%{$cleanS}%")
                  ->orWhere('payment_transactions.order_number', 'like', "%{$cleanS}%")
                  ->orWhere('payment_transactions.order_id', $cleanS)
                  ->orWhere('payment_transactions.user_id', $cleanS)
                  ->orWhere('payment_transactions.customer_name', 'like', "%{$s}%")
                  ->orWhere('payment_transactions.customer_phone', 'like', "%{$s}%")
                  ->orWhere('payment_transactions.customer_email', 'like', "%{$s}%");

                if ($normS) {
                    $q->orWhere('payment_transactions.normalized_trx_id', 'like', "%{$normS}%");
                }
            });
        }

        // =====================================================================
        // SERVER-SIDE SUMMARY CALCULATION (Across Entire Filtered Dataset)
        // =====================================================================
        $summary = $this->calculateFilteredSummaries(clone $query);

        // Attach row-level SELECT columns for data fetching & pagination
        $query->select(
            'payment_transactions.*',
            'orders.order_status as linked_order_status',
            'orders.payment_status as linked_order_payment_status',
            'orders.grand_total as order_grand_total',
            'orders.paid_amount as order_paid_amount',
            'orders.due_amount as order_due_amount',
            'orders.courier_name as order_courier_name',
            'orders.courier_tracking_id as order_tracking_id',
            'branches.name as branch_name',
            'checker.name as checker_name',
            'verifier.name as verifier_name',
            'updater.name as updater_name'
        );

        // =====================================================================
        // EXCEL / CSV & PURE TEXT PDF DATA EXPORT HANDLERS
        // =====================================================================
        if ($request->input('export') === 'excel' || $request->input('export') === 'csv') {
            $exportRows = (clone $query)->orderByDesc('payment_transactions.id')->get();
            return $this->streamTransactionsCsvExport($exportRows, $summary, $activeSection);
        }

        if ($request->input('export') === 'pdf_data') {
            $pdfRows = (clone $query)->orderByDesc('payment_transactions.id')->limit(3000)->get()->map(function ($row, $idx) {
                return [
                    'sl'          => $idx + 1,
                    'uid'         => $row->transaction_uid,
                    'order_ref'   => $row->invoice_no ?: ($row->order_id ? '#' . $row->order_id : 'N/A'),
                    'customer'    => ($row->customer_name ?: 'Guest') . ($row->customer_phone ? ' (' . $row->customer_phone . ')' : ''),
                    'source'      => strtoupper(str_replace('_', ' ', (string) $row->source_type)),
                    'method'      => $row->payment_method,
                    'sender'      => $row->sender_number ?: '—',
                    'trx_id'      => $row->transaction_id ?: '—',
                    'expected'    => number_format((float) $row->expected_amount, 2),
                    'verified'    => number_format((float) $row->verified_amount, 2),
                    'status'      => strtoupper(str_replace('_', ' ', (string) $row->verification_status)),
                    'order_st'    => strtoupper(str_replace('_', ' ', (string) ($row->linked_order_status ?? 'N/A'))),
                    'verified_by' => $row->verifier_name ?: ($row->checker_name ?: 'Unchecked'),
                    'date'        => date('d M Y, h:i A', strtotime($row->created_at)),
                    'remarks'     => $row->admin_remarks ?: ($row->rejection_reason ?: '—'),
                ];
            });

            return response()->json([
                'success'      => true,
                'title'        => config('app.name', 'Shop Now BD') . ' - Payment Transactions Report (' . strtoupper($activeSection) . ')',
                'generated_at' => date('d M Y, h:i A'),
                'summary'      => $summary,
                'records'      => $pdfRows,
            ]);
        }

        // =====================================================================
        // SERVER-SIDE PAGINATION
        // =====================================================================
        $perPage = $this->resolvePerPage($request, 20);
        $transactions = $query->orderByDesc('payment_transactions.id')->paginate($perPage)->withQueryString();

        $alertCounters = PaymentTransactionService::getUncheckedAlertsSummary();

        // AJAX Request Response
        if ($request->ajax()) {
            return response()->json([
                'success'      => true,
                'html'         => view('admin.transactions.partials.table_rows', compact('transactions', 'activeSection'))->render(),
                'pagination'   => $transactions->hasPages() ? $transactions->links('pagination::bootstrap-5')->render() : '',
                'total'        => $transactions->total(),
                'firstItem'    => $transactions->firstItem() ?? 0,
                'lastItem'     => $transactions->lastItem() ?? 0,
                'summary'      => $summary,
                'alerts'       => $alertCounters,
            ]);
        }

        // Dropdown Lists for Filter Toolbar & Active Gateways for Manual Form
        $availableMethods     = $this->getAvailablePaymentMethods();
        $activeGatewayMethods = $this->getActiveGatewayMethods();

        $staffUsers = DB::table('users')
            ->whereNull('deleted_at')
            ->where('role', '!=', 'customer')
            ->select('id', 'name', 'role')
            ->orderBy('name')
            ->get();
        $branches = Schema::hasTable('branches') ? DB::table('branches')->where('is_active', true)->get() : collect();

        return view('admin.transactions.index', compact(
            'transactions',
            'activeSection',
            'summary',
            'alertCounters',
            'availableMethods',
            'activeGatewayMethods',
            'staffUsers',
            'branches',
            'perPage'
        ));
    }

    /**
     * ২. অডিট লগ ও ভেরিফিকেশন হিস্ট্রি সেকশন রেন্ডারিং
     */
    protected function renderVerificationHistorySection(Request $request, string $activeSection)
    {
        $hQuery = DB::table('payment_verification_histories')
            ->leftJoin('payment_transactions', 'payment_verification_histories.payment_transaction_id', '=', 'payment_transactions.id')
            ->select(
                'payment_verification_histories.*',
                'payment_transactions.transaction_uid',
                'payment_transactions.customer_name',
                'payment_transactions.customer_phone',
                'payment_transactions.source_type'
            );

        if ($request->filled('search')) {
            $s = trim($request->search);
            $hQuery->where(function ($q) use ($s) {
                $q->where('payment_verification_histories.invoice_no', 'like', "%{$s}%")
                  ->orWhere('payment_verification_histories.transaction_id', 'like', "%{$s}%")
                  ->orWhere('payment_verification_histories.performed_by_name', 'like', "%{$s}%")
                  ->orWhere('payment_transactions.transaction_uid', 'like', "%{$s}%")
                  ->orWhere('payment_transactions.customer_phone', 'like', "%{$s}%");
            });
        }

        if ($request->filled('start_date')) {
            $hQuery->where('payment_verification_histories.created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        }
        if ($request->filled('end_date')) {
            $hQuery->where('payment_verification_histories.created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        $perPage = $this->resolvePerPage($request, 20);
        $histories = $hQuery->orderByDesc('payment_verification_histories.id')->paginate($perPage)->withQueryString();

        $alertCounters = PaymentTransactionService::getUncheckedAlertsSummary();
        $summary = $this->calculateFilteredSummaries(DB::table('payment_transactions')->whereNull('deleted_at'));

        if ($request->ajax()) {
            return response()->json([
                'success'    => true,
                'html'       => view('admin.transactions.partials.history_rows', compact('histories'))->render(),
                'pagination' => $histories->hasPages() ? $histories->links('pagination::bootstrap-5')->render() : '',
                'total'      => $histories->total(),
                'firstItem'  => $histories->firstItem() ?? 0,
                'lastItem'   => $histories->lastItem() ?? 0,
                'summary'    => $summary,
                'alerts'     => $alertCounters,
            ]);
        }

        $availableMethods     = $this->getAvailablePaymentMethods();
        $activeGatewayMethods = $this->getActiveGatewayMethods();
        $staffUsers = DB::table('users')->whereNull('deleted_at')->where('role', '!=', 'customer')->select('id', 'name', 'role')->get();
        $branches = Schema::hasTable('branches') ? DB::table('branches')->where('is_active', true)->get() : collect();
        $transactions = $histories;

        return view('admin.transactions.index', compact(
            'transactions',
            'histories',
            'activeSection',
            'summary',
            'alertCounters',
            'availableMethods',
            'activeGatewayMethods',
            'staffUsers',
            'branches',
            'perPage'
        ));
    }

    /**
     * ৩. সার্ভার-সাইড সামারি ক্যালকুলেশন হেল্পার (ONLY_FULL_GROUP_BY Safe & Consistent Counter)
     */
    protected function calculateFilteredSummaries($baseQuery): array
    {
        $baseQuery->columns = [];
        $baseQuery->orders  = null;

        $stats = $baseQuery->selectRaw("
            COUNT(payment_transactions.id) as total_count,
            COALESCE(SUM(payment_transactions.expected_amount), 0) as total_expected_amount,
            COALESCE(SUM(CASE WHEN payment_transactions.verification_status IN ('paid', 'partially_paid') THEN payment_transactions.verified_amount ELSE 0 END), 0) as total_paid_amount,
            COALESCE(SUM(CASE WHEN payment_transactions.verification_status = 'partially_paid' THEN payment_transactions.verified_amount ELSE 0 END), 0) as total_partial_amount,
            COALESCE(SUM(CASE WHEN payment_transactions.verification_status = 'refunded' THEN payment_transactions.refunded_amount ELSE 0 END), 0) as total_refunded_amount,
            COALESCE(SUM(CASE WHEN payment_transactions.verification_status IN ('pending_verification', 'under_review') THEN 1 ELSE 0 END), 0) as count_pending,
            COALESCE(SUM(CASE WHEN payment_transactions.checked_by IS NOT NULL THEN 1 ELSE 0 END), 0) as count_checked,
            COALESCE(SUM(CASE WHEN payment_transactions.verification_status = 'approved' THEN 1 ELSE 0 END), 0) as count_approved,
            COALESCE(SUM(CASE WHEN payment_transactions.verification_status = 'paid' THEN 1 ELSE 0 END), 0) as count_paid,
            COALESCE(SUM(CASE WHEN payment_transactions.verification_status = 'partially_paid' THEN 1 ELSE 0 END), 0) as count_partial,
            COALESCE(SUM(CASE WHEN payment_transactions.verification_status = 'unmatched' THEN 1 ELSE 0 END), 0) as count_unmatched,
            COALESCE(SUM(CASE WHEN payment_transactions.verification_status = 'fake' THEN 1 ELSE 0 END), 0) as count_fake,
            COALESCE(SUM(CASE WHEN payment_transactions.verification_status = 'under_review' THEN 1 ELSE 0 END), 0) as count_under_review,
            COALESCE(SUM(CASE WHEN payment_transactions.verification_status = 'refunded' THEN 1 ELSE 0 END), 0) as count_refunded
        ")->first();

        $totalExpected = (float) ($stats->total_expected_amount ?? 0);
        $totalPaid     = (float) ($stats->total_paid_amount ?? 0);
        $totalRefunded = (float) ($stats->total_refunded_amount ?? 0);
        $totalUnpaid   = max(0.00, $totalExpected - $totalPaid);

        return [
            'total_transactions'     => (int) ($stats->total_count ?? 0),
            'total_expected_amount'  => number_format($totalExpected, 2, '.', ''),
            'total_paid_amount'      => number_format($totalPaid, 2, '.', ''),
            'total_unpaid_amount'    => number_format($totalUnpaid, 2, '.', ''),
            'total_partial_amount'   => number_format((float) ($stats->total_partial_amount ?? 0), 2, '.', ''),
            'total_refunded_amount'  => number_format($totalRefunded, 2, '.', ''),
            'count_pending'          => (int) ($stats->count_pending ?? 0),
            'count_checked'          => (int) ($stats->count_checked ?? 0),
            'count_approved'         => (int) ($stats->count_approved ?? 0),
            'count_paid'             => (int) ($stats->count_paid ?? 0),
            'count_partial'          => (int) ($stats->count_partial ?? 0),
            'count_unmatched'        => (int) ($stats->count_unmatched ?? 0),
            'count_fake'             => (int) ($stats->count_fake ?? 0),
            'count_under_review'     => (int) ($stats->count_under_review ?? 0),
            'count_refunded'         => (int) ($stats->count_refunded ?? 0),
        ];
    }

    /**
     * ৪. প্রতিটি ট্রানজেকশনের বিস্তারিত যাচাই মোডাল ডেটা (AJAX Endpoint)
     */
    public function show($id)
    {
        $txn = PaymentTransaction::with(['checker', 'approver', 'verifier', 'updater', 'branch', 'histories'])
            ->where('id', $id)
            ->first();

        if (!$txn) {
            return response()->json(['success' => false, 'message' => 'Transaction record not found.'], 404);
        }

        $order = null;
        $orderPaymentHistory = collect();

        if (!empty($txn->order_id)) {
            $order = DB::table('orders')->where('id', $txn->order_id)->first();
            $orderPaymentHistory = PaymentTransaction::where('order_id', $txn->order_id)
                ->where('id', '!=', $txn->id)
                ->orderByDesc('id')
                ->get()
                ->map(function ($item) {
                    return [
                        'id'               => $item->id,
                        'uid'              => $item->transaction_uid,
                        'method'           => $item->payment_method,
                        'trx_id'           => $item->transaction_id ?: '—',
                        'submitted_amount' => number_format((float) $item->submitted_amount, 2),
                        'verified_amount'  => number_format((float) $item->verified_amount, 2),
                        'status'           => $item->deleted_at ? 'Trashed (' . $item->status_label . ')' : $item->status_label,
                        'status_raw'       => $item->verification_status,
                        'is_trashed'       => !empty($item->deleted_at),
                        'date'             => date('d M Y, h:i A', strtotime($item->created_at)),
                    ];
                });
        }

        // লাইভ ডুপ্লিকেট TrxID চেক
        $duplicateInfo = [
            'is_duplicate'  => (bool) $txn->is_duplicate_flagged,
            'warning_text'  => null,
            'matched_order' => null,
        ];

        if (!empty($txn->transaction_id)) {
            $dupCheck = PaymentTransactionService::checkDuplicateTransactionId(
                $txn->transaction_id,
                $txn->payment_method,
                $txn->id,
                $txn->order_id
            );

            if ($dupCheck['is_duplicate']) {
                $duplicateInfo['is_duplicate'] = true;
                $duplicateInfo['warning_text'] = $dupCheck['admin_warning'] ?? $dupCheck['message'];
                if ($dupCheck['existing_transaction']) {
                    $duplicateInfo['matched_order'] = $dupCheck['existing_transaction']->invoice_no ?: ('#' . $dupCheck['existing_transaction']->order_id);
                } elseif ($dupCheck['existing_order']) {
                    $duplicateInfo['matched_order'] = $dupCheck['existing_order']->invoice_no ?? ('#' . $dupCheck['existing_order']->id);
                }
            }
        }

        $historiesFormatted = $txn->histories->map(function ($h) {
            return [
                'id'           => $h->id,
                'action_label' => $h->action_label,
                'old_status'   => $h->old_status ? ucwords(str_replace('_', ' ', $h->old_status)) : 'None',
                'new_status'   => $h->new_status ? ucwords(str_replace('_', ' ', $h->new_status)) : 'None',
                'amount'       => number_format((float) $h->amount, 2),
                'reason'       => $h->reason,
                'remarks'      => $h->remarks,
                'performer'    => $h->performed_by_name ?: 'System',
                'role'         => ucwords(str_replace('_', ' ', (string) $h->performed_by_role)),
                'ip'           => $h->ip_address,
                'created_at'   => date('d M Y, h:i:s A', strtotime($h->created_at)),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                    => $txn->id,
                'transaction_uid'       => $txn->transaction_uid,
                'order_id'              => $txn->order_id,
                'invoice_no'            => $txn->invoice_no ?: ($txn->order_id ? '#' . $txn->order_id : 'N/A'),
                'order_view_url'        => $txn->order_id ? route('admin.order.view', $txn->order_id) : null,
                'branch_name'           => $txn->branch->name ?? 'Head Office / Online',
                'customer_id'           => $txn->user_id,
                'customer_name'         => $txn->customer_name ?: 'Guest Customer',
                'customer_phone'        => $txn->customer_phone ?: 'N/A',
                'customer_email'        => $txn->customer_email ?: 'N/A',
                'source_type'           => $txn->source_type,
                'source_label'          => $txn->source_label,
                'payment_method'        => $txn->payment_method,
                'sender_number'         => $txn->sender_number ?: 'N/A',
                'transaction_id'        => $txn->transaction_id ?: 'N/A',
                'expected_amount'       => number_format((float) $txn->expected_amount, 2, '.', ''),
                'submitted_amount'      => number_format((float) $txn->submitted_amount, 2, '.', ''),
                'verified_amount'       => number_format((float) $txn->verified_amount, 2, '.', ''),
                'refunded_amount'       => number_format((float) $txn->refunded_amount, 2, '.', ''),
                'delivery_charge'       => number_format((float) $txn->delivery_charge, 2, '.', ''),
                'cod_amount'            => number_format((float) $txn->cod_amount, 2, '.', ''),
                'screenshot_url'        => $txn->screenshot_url,
                'verification_status'   => $txn->verification_status,
                'status_label'          => $txn->status_label,
                'cod_settlement_status' => $txn->cod_settlement_status,
                'is_trashed'            => !empty($txn->deleted_at),
                'checked_by_name'       => $txn->checker->name ?? 'Not Checked Yet',
                'checked_at'            => $txn->checked_at ? $txn->checked_at->format('d M Y, h:i A') : '—',
                'approved_by_name'      => $txn->approver->name ?? '—',
                'approved_at'           => $txn->approved_at ? $txn->approved_at->format('d M Y, h:i A') : '—',
                'verified_by_name'      => $txn->verifier->name ?? 'Not Verified Yet',
                'verified_at'           => $txn->verified_at ? $txn->verified_at->format('d M Y, h:i A') : '—',
                'updated_by_name'       => $txn->updater->name ?? '—',
                'updated_at'            => $txn->updated_at ? $txn->updated_at->format('d M Y, h:i A') : '—',
                'submitted_at'          => $txn->submitted_at ? $txn->submitted_at->format('d M Y, h:i A') : $txn->created_at->format('d M Y, h:i A'),
                'admin_remarks'         => $txn->admin_remarks,
                'rejection_reason'      => $txn->rejection_reason,
                'order_info'            => $order ? [
                    'id'             => $order->id,
                    'invoice_no'     => $order->invoice_no ?? ('#' . $order->id),
                    'order_status'   => strtoupper(str_replace('_', ' ', (string) $order->order_status)),
                    'payment_status' => strtoupper((string) $order->payment_status),
                    'grand_total'    => number_format((float) ($order->grand_total ?? 0), 2),
                    'paid_amount'    => number_format((float) ($order->paid_amount ?? 0), 2),
                    'due_amount'     => number_format((float) ($order->due_amount ?? 0), 2),
                ] : null,
                'duplicate_info'        => $duplicateInfo,
                'order_payment_history' => $orderPaymentHistory,
                'verification_history'  => $historiesFormatted,
            ],
        ]);
    }

    /**
     * ৫. অ্যাডমিন ভেরিফিকেশন অ্যাকশন এক্সিকিউটর (AJAX)
     */
    public function verifyAction(Request $request, $id)
    {
        $request->validate([
            'action'          => 'required|string|in:mark_checked,approve,mark_paid,mark_partially_paid,mark_unmatched,mark_fake,under_review,mark_failed,refund,add_note',
            'verified_amount' => 'nullable|numeric|min:0',
            'refund_amount'   => 'nullable|numeric|min:0',
            'reason'          => 'nullable|string|max:1000',
            'remarks'         => 'nullable|string|max:1000',
        ]);

        $result = PaymentTransactionService::executeVerificationAction(
            (int) $id,
            $request->input('action'),
            $request->only(['verified_amount', 'refund_amount', 'reason', 'remarks', 'force_amount_update'])
        );

        $statusCode = $result['success'] ? 200 : 422;
        $result['alerts'] = PaymentTransactionService::getUncheckedAlertsSummary();

        return response()->json($result, $statusCode);
    }

    /**
     * ৬. ম্যানুয়াল ট্রানজেকশন এন্ট্রি সংরক্ষণ (AJAX)
     * (শুধুমাত্র ডাটাবেজে থাকা Online Recorded Order-এর জন্য অনুমোদিত)
     */
    public function storeManual(Request $request)
    {
        $request->validate([
            'order_id'            => 'required_without:invoice_no|nullable|integer',
            'invoice_no'          => 'required_without:order_id|nullable|string|max:60',
            'payment_method'      => 'required|string|max:60',
            'amount'              => 'required|numeric|min:0.01',
            'transaction_id'      => 'nullable|string|max:100',
            'sender_number'       => 'nullable|string|max:30',
            'customer_name'       => 'nullable|string|max:150',
            'customer_phone'      => 'nullable|string|max:30',
            'customer_email'      => 'nullable|email|max:150',
            'verification_status' => 'required|string|in:pending_verification,checked,approved,paid,partially_paid',
            'transaction_date'    => 'nullable|date',
            'remarks'             => 'nullable|string|max:1000',
            'screenshot_file'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ], [
            'order_id.required_without'   => 'অনুগ্রহ করে ডাটাবেজে থাকা একটি Online Order সিলেক্ট করুন। অর্ডার ছাড়া ট্রানজেকশন অ্যাড করা যাবে না।',
            'invoice_no.required_without' => 'অনুগ্রহ করে ডাটাবেজে থাকা একটি Online Order সিলেক্ট করুন। অর্ডার ছাড়া ট্রানজেকশন অ্যাড করা যাবে না।',
        ]);

        $screenshotPath = null;
        if ($request->hasFile('screenshot_file')) {
            $screenshotPath = $this->saveAttachmentFile($request->file('screenshot_file'), 'payment_proofs');
        }

        $payload = $request->all();
        $payload['screenshot_path'] = $screenshotPath;
        $payload['customer_email']  = $request->input('customer_email', null);

        $result = PaymentTransactionService::createManualTransaction($payload);
        $statusCode = $result['success'] ? 200 : 422;
        $result['alerts'] = PaymentTransactionService::getUncheckedAlertsSummary();

        return response()->json($result, $statusCode);
    }

    /**
     * ৭. ম্যানুয়াল ট্রানজেকশনে অর্ডার লিংক করার জন্য লাইভ Online Recorded Order সার্চ (AJAX)
     */
    public function searchOrderAjax(Request $request)
    {
        PaymentTransactionService::ensureTrashColumnExists();

        $q = trim((string) $request->input('q', ''));
        $clean = ltrim($q, '#');

        $query = DB::table('orders');

        // শুধুমাত্র Online Recorded Orders (POS বাদে)
        if (Schema::hasColumn('orders', 'order_source')) {
            $query->where(function ($sq) {
                $sq->whereNull('order_source')->orWhere('order_source', '!=', 'pos');
            });
        } elseif (Schema::hasColumn('orders', 'order_type')) {
            $query->where(function ($sq) {
                $sq->whereNull('order_type')->orWhere('order_type', '!=', 'pos');
            });
        }

        if (mb_strlen($clean) >= 1) {
            $query->where(function ($sub) use ($q, $clean) {
                $sub->where('id', $clean)
                    ->orWhere('invoice_no', 'like', "%{$clean}%");
                if (Schema::hasColumn('orders', 'customer_phone')) {
                    $sub->orWhere('customer_phone', 'like', "%{$q}%");
                }
                if (Schema::hasColumn('orders', 'shipping_phone')) {
                    $sub->orWhere('shipping_phone', 'like', "%{$q}%");
                }
                if (Schema::hasColumn('orders', 'customer_name')) {
                    $sub->orWhere('customer_name', 'like', "%{$q}%");
                }
            });
        }

        $orders = $query->orderByDesc('id')
            ->limit(15)
            ->get()
            ->map(function ($o) {
                $grand = (float) ($o->grand_total ?? 0);
                $paid  = (float) ($o->paid_amount ?? 0);
                $existingActiveTxn = DB::table('payment_transactions')
                    ->where('order_id', $o->id)
                    ->whereNull('deleted_at')
                    ->orderByDesc('id')
                    ->first();

                return [
                    'id'                   => $o->id,
                    'invoice_no'           => $o->invoice_no ?? ('#' . $o->id),
                    'customer_name'        => $o->customer_name ?? ($o->shipping_name ?? 'Customer'),
                    'customer_phone'       => $o->customer_phone ?? ($o->shipping_phone ?? ''),
                    'customer_email'       => $o->customer_email ?? ($o->shipping_email ?? ''),
                    'grand_total'          => number_format($grand, 2, '.', ''),
                    'paid_amount'          => number_format($paid, 2, '.', ''),
                    'due_amount'           => number_format(max(0, $grand - $paid), 2, '.', ''),
                    'payment_status'       => strtoupper((string) ($o->payment_status ?? 'unpaid')),
                    'has_existing_txn'     => (bool) $existingActiveTxn,
                    'existing_txn_uid'     => $existingActiveTxn->transaction_uid ?? null,
                    'existing_txn_status'  => $existingActiveTxn ? strtoupper(str_replace('_', ' ', $existingActiveTxn->verification_status)) : null,
                ];
            });

        return response()->json($orders);
    }

    /**
     * ৮. Bulk Action Handler (Bulk Checked, Approve, Paid, Move to Trash, Restore, Permanent Delete)
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids'         => 'required|array|min:1',
            'ids.*'       => 'integer',
            'action_type' => 'required|string|in:mark_checked,approve,mark_paid,under_review,mark_unmatched,mark_fake,trash,restore,force_delete',
            'reason'      => 'nullable|string|max:500',
        ]);

        $result = PaymentTransactionService::executeBulkActions(
            $request->input('ids'),
            $request->input('action_type'),
            $request->input('reason')
        );

        $result['alerts'] = PaymentTransactionService::getUncheckedAlertsSummary();
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * ৯. Single Transaction Trash / Restore / Permanent Delete
     */
    public function destroy(Request $request, $id)
    {
        $mode = $request->input('mode', 'trash');
        if (!in_array($mode, ['trash', 'restore', 'force_delete'])) {
            $mode = 'trash';
        }

        $result = PaymentTransactionService::executeBulkActions([(int) $id], $mode, $request->input('reason'));
        $result['alerts'] = PaymentTransactionService::getUncheckedAlertsSummary();

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * ১০. Memory-Safe Excel/CSV Export Streamer
     */
    protected function streamTransactionsCsvExport($records, array $summary, string $section)
    {
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        $filename = 'Payment_Transactions_' . ucfirst($section) . '_' . date('Y_m_d_His') . '.csv';
        $handle = fopen('php://temp', 'r+');

        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [config('app.name', 'Shop Now BD') . ' - Payment Transactions & Verification Report']);
        fputcsv($handle, ['Section:', strtoupper($section), 'Generated At:', date('d M Y, h:i A')]);
        fputcsv($handle, [
            'Total Records: ' . $summary['total_transactions'],
            'Expected Total: ' . $summary['total_expected_amount'] . ' BDT',
            'Verified Paid: ' . $summary['total_paid_amount'] . ' BDT',
            'Unpaid/Due: ' . $summary['total_unpaid_amount'] . ' BDT',
            'Refunded: ' . $summary['total_refunded_amount'] . ' BDT',
        ]);
        fputcsv($handle, []);

        fputcsv($handle, [
            'SL',
            'Transaction UID',
            'Order ID',
            'Invoice Number',
            'Customer Name',
            'Customer Phone',
            'Customer Email',
            'Source Type',
            'Payment Method',
            'Sender Number',
            'Transaction ID (TrxID)',
            'Expected Amount (BDT)',
            'Submitted Amount (BDT)',
            'Verified Paid Amount (BDT)',
            'Refunded Amount (BDT)',
            'Transaction Status',
            'Order Status',
            'Checked / Verified By',
            'Checked At',
            'Verified At',
            'Last Updated By',
            'Transaction Date',
            'Remarks / Reason'
        ]);

        foreach ($records as $idx => $r) {
            $phoneSafe  = !empty($r->customer_phone) ? '="' . $r->customer_phone . '"' : 'N/A';
            $senderSafe = !empty($r->sender_number) ? '="' . $r->sender_number . '"' : 'N/A';
            $trxSafe    = !empty($r->transaction_id) ? '="' . $r->transaction_id . '"' : 'N/A';

            fputcsv($handle, [
                $idx + 1,
                $r->transaction_uid,
                $r->order_id ? '#' . $r->order_id : 'N/A',
                $r->invoice_no ?: 'N/A',
                $r->customer_name ?: 'Guest',
                $phoneSafe,
                $r->customer_email ?: 'N/A',
                strtoupper(str_replace('_', ' ', (string) $r->source_type)),
                $r->payment_method,
                $senderSafe,
                $trxSafe,
                number_format((float) $r->expected_amount, 2, '.', ''),
                number_format((float) $r->submitted_amount, 2, '.', ''),
                number_format((float) $r->verified_amount, 2, '.', ''),
                number_format((float) $r->refunded_amount, 2, '.', ''),
                strtoupper(str_replace('_', ' ', (string) $r->verification_status)),
                strtoupper(str_replace('_', ' ', (string) ($r->linked_order_status ?? 'N/A'))),
                $r->verifier_name ?: ($r->checker_name ?: 'Unchecked'),
                $r->checked_at ? date('d M Y, h:i A', strtotime($r->checked_at)) : '—',
                $r->verified_at ? date('d M Y, h:i A', strtotime($r->verified_at)) : '—',
                $r->updater_name ?: '—',
                date('d M Y, h:i A', strtotime($r->created_at)),
                $r->admin_remarks ?: ($r->rejection_reason ?: ''),
            ]);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return response($csvContent, 200, [
            'Content-Type'           => 'text/csv; charset=UTF-8',
            'Content-Disposition'    => 'attachment; filename="' . $filename . '"',
            'Content-Length'         => strlen($csvContent),
            'Cache-Control'          => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'                 => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * ১১. ম্যানুয়াল ট্রানজেকশন অ্যাড ফর্মের জন্য শুধুমাত্র Active Payment Gateways ও Active Manual Methods সংগ্রহ
     */
    protected function getActiveGatewayMethods()
    {
        $activeMethods = collect();

        if (Schema::hasTable('manual_payment_methods')) {
            $manualActive = DB::table('manual_payment_methods')
                ->where('is_active', true)
                ->pluck('name');
            $activeMethods = $activeMethods->merge($manualActive);
        }

        if (Schema::hasTable('payment_gateways')) {
            $gatewayActive = DB::table('payment_gateways')
                ->where('is_active', true)
                ->pluck('name');
            $activeMethods = $activeMethods->merge($gatewayActive);
        }

        $activeMethods = $activeMethods->map(fn($m) => trim((string) $m))->filter()->unique()->values();

        if ($activeMethods->isEmpty()) {
            return collect(['bKash', 'Nagad', 'Rocket', 'Upay', 'Bank Transfer', 'Cash on Delivery']);
        }

        return $activeMethods;
    }

    /**
     * ১২. ফিল্টার টুলবারের জন্য সকল পেমেন্ট মেথড
     */
    protected function getAvailablePaymentMethods()
    {
        $methods = $this->getActiveGatewayMethods()->merge(['Cash on Delivery', 'Cash']);

        if (Schema::hasTable('payment_transactions')) {
            $methods = $methods->merge(
                DB::table('payment_transactions')
                    ->whereNotNull('payment_method')
                    ->distinct()
                    ->pluck('payment_method')
            );
        }

        return $methods->map(fn($m) => trim((string) $m))->filter()->unique()->values();
    }
}