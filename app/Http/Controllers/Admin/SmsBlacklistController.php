<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsBlacklist;
use App\Services\Sms\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class SmsBlacklistController extends Controller
{
    /**
     * Display a paginated listing of blacklisted numbers with instant AJAX search.
     */
    public function index(Request $request)
    {
        $query = SmsBlacklist::query()->where('type', 'phone')->latest('id');

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('value', 'like', "%{$search}%");
                if (Schema::hasColumn('sms_blacklists', 'reason')) {
                    $q->orWhere('reason', 'like', "%{$search}%");
                }
            });
        }

        $blacklists = $query->paginate(20)->withQueryString();

        $stats = [
            'total_phones'   => SmsBlacklist::where('type', 'phone')->count(),
            'total_ips'      => SmsBlacklist::where('type', 'ip')->count(),
            'total_keywords' => SmsBlacklist::where('type', 'keyword')->count(),
        ];

        // Handle Live AJAX Search & Pagination without page reload
        if ($request->ajax()) {
            return response()->json([
                'success'    => true,
                'html'       => view('admin.sms.blacklist.partials.table_rows', compact('blacklists'))->render(),
                'pagination' => $blacklists->hasPages() ? $blacklists->links('pagination::bootstrap-5')->render() : '',
                'total'      => $blacklists->total(),
                'from'       => $blacklists->firstItem() ?? 0,
                'to'         => $blacklists->lastItem() ?? 0,
            ]);
        }

        return view('admin.sms.blacklist.index', compact('blacklists', 'stats'));
    }

    /**
     * Store single or multiple blacklist phone numbers in bulk safely without schema mismatch.
     */
    public function store(Request $request, SmsService $smsService): RedirectResponse
    {
        $request->validate([
            'numbers' => 'nullable|string',
            'value'   => 'nullable|string',
            'reason'  => 'nullable|string|max:255',
        ]);

        $rawInput = trim((string) ($request->input('numbers') ?? $request->input('value', '')));
        if (empty($rawInput)) {
            return back()->withInput()->with('error', 'Please enter at least one mobile number.');
        }

        // Split numbers by newline, comma, semicolon, or space
        $lines = preg_split('/[\r\n,; ]+/', $rawInput, -1, PREG_SPLIT_NO_EMPTY);
        $reason = $request->input('reason');
        $authId = Auth::id();

        $validNumbers = [];
        $invalidCount = 0;
        $alreadyExistsCount = 0;

        foreach ($lines as $line) {
            $raw = trim($line);
            if (empty($raw)) continue;

            $clean = $smsService->formatNumber($raw);
            if (!preg_match('/^8801[3-9]\d{8}$/', $clean)) {
                $invalidCount++;
                continue;
            }

            if (in_array($clean, $validNumbers, true)) {
                continue;
            }

            $exists = SmsBlacklist::where('type', 'phone')->where('value', $clean)->exists();
            if ($exists) {
                $alreadyExistsCount++;
                continue;
            }

            $validNumbers[] = $clean;
        }

        if (empty($validNumbers)) {
            $msg = 'No valid new Bangladeshi mobile numbers to block.';
            if ($alreadyExistsCount > 0) $msg .= " ({$alreadyExistsCount} already blocked).";
            if ($invalidCount > 0) $msg .= " ({$invalidCount} invalid format).";
            return back()->withInput()->with('error', $msg);
        }

        try {
            $now = now();
            $insertData = [];

            // Detect existing table columns to prevent 1054 Unknown column errors
            $columns = Schema::hasTable('sms_blacklists') ? Schema::getColumnListing('sms_blacklists') : [];
            $hasReason    = in_array('reason', $columns, true);
            $hasIsActive  = in_array('is_active', $columns, true);
            $hasStatus    = in_array('status', $columns, true);
            $hasBlockedBy = in_array('blocked_by', $columns, true);
            $hasUserId    = in_array('user_id', $columns, true);

            foreach ($validNumbers as $num) {
                $row = [
                    'type'       => 'phone',
                    'value'      => $num,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if ($hasReason) {
                    $row['reason'] = $reason;
                }
                if ($hasIsActive) {
                    $row['is_active'] = true;
                } elseif ($hasStatus) {
                    $row['status'] = 1;
                }
                if ($hasBlockedBy) {
                    $row['blocked_by'] = $authId;
                } elseif ($hasUserId) {
                    $row['user_id'] = $authId;
                }

                $insertData[] = $row;
            }

            SmsBlacklist::insert($insertData);

            $addedCount = count($validNumbers);
            $feedback = "{$addedCount} phone number(s) blocked successfully.";
            if ($alreadyExistsCount > 0) {
                $feedback .= " ({$alreadyExistsCount} duplicate(s) skipped).";
            }

            return redirect()->route('admin.sms.blacklist.index')->with('success', $feedback);
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Failed to block numbers: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified item from blacklist (Unblock).
     */
    public function destroy(SmsBlacklist $blacklist): RedirectResponse|JsonResponse
    {
        try {
            $phone = $blacklist->value;
            $blacklist->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Phone number '{$phone}' unblocked successfully.",
                ]);
            }

            return redirect()
                ->route('admin.sms.blacklist.index')
                ->with('success', "Phone number '{$phone}' unblocked successfully.");
        } catch (Throwable $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to unblock: ' . $e->getMessage()], 500);
            }

            return redirect()
                ->route('admin.sms.blacklist.index')
                ->with('error', 'Failed to unblock number: ' . $e->getMessage());
        }
    }
}