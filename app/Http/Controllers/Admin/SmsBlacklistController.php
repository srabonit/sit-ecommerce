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
     * Display a paginated listing of blacklisted items with type filtering.
     */
    public function index(Request $request): View
    {
        $query = SmsBlacklist::query()->latest('id');

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('value', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        $blacklists = $query->paginate(20)->withQueryString();

        $stats = [
            'total_phones'   => SmsBlacklist::where('type', 'phone')->count(),
            'total_ips'      => SmsBlacklist::where('type', 'ip')->count(),
            'total_keywords' => SmsBlacklist::where('type', 'keyword')->count(),
        ];

        return view('admin.sms.blacklist.index', compact('blacklists', 'stats'));
    }

    /**
     * Store a newly created blacklist entry in database.
     */
    public function store(Request $request, SmsService $smsService): RedirectResponse
    {
        $validated = $request->validate([
            'type'   => 'required|in:phone,ip,keyword',
            'value'  => 'required|string|max:150',
            'reason' => 'nullable|string|max:255',
        ]);

        $type = $validated['type'];
        $cleanValue = trim($validated['value']);

        // Normalize value based on blacklist type
        if ($type === 'phone') {
            $cleanValue = $smsService->formatNumber($cleanValue);
            if (!preg_match('/^8801[3-9]\d{8}$/', $cleanValue)) {
                return back()
                    ->withInput()
                    ->with('error', 'Invalid Bangladeshi mobile number format (e.g. 017XXXXXXXX or 88017XXXXXXXX).');
            }
        } elseif ($type === 'ip') {
            if (!filter_var($cleanValue, FILTER_VALIDATE_IP)) {
                return back()
                    ->withInput()
                    ->with('error', 'Invalid IPv4 or IPv6 address format.');
            }
        } else {
            $cleanValue = mb_strtolower($cleanValue, 'UTF-8');
        }

        // Check for existing duplicate entry
        $exists = SmsBlacklist::where('type', $type)
            ->where('value', $cleanValue)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', "This {$type} '{$cleanValue}' is already registered in the blacklist.");
        }

        try {
            $blacklistData = [
                'type'      => $type,
                'value'     => $cleanValue,
                'reason'    => $validated['reason'] ?? null,
                'is_active' => true,
            ];

            // Safely assign creator ID without schema errors
            if (Schema::hasColumn('sms_blacklists', 'blocked_by')) {
                $blacklistData['blocked_by'] = Auth::id();
            } elseif (Schema::hasColumn('sms_blacklists', 'user_id')) {
                $blacklistData['user_id'] = Auth::id();
            }

            SmsBlacklist::create($blacklistData);

            return redirect()
                ->route('admin.sms.blacklist.index')
                ->with('success', "Item added to {$type} blacklist successfully.");
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to add blacklist entry: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified item from blacklist.
     */
    public function destroy(SmsBlacklist $blacklist): RedirectResponse
    {
        try {
            $blacklist->delete();

            return redirect()
                ->route('admin.sms.blacklist.index')
                ->with('success', 'Entry removed from blacklist successfully.');
        } catch (Throwable $e) {
            return redirect()
                ->route('admin.sms.blacklist.index')
                ->with('error', 'Failed to delete blacklist entry: ' . $e->getMessage());
        }
    }

    /**
     * Quick status toggle (AJAX).
     */
    public function toggleStatus(Request $request, SmsBlacklist $blacklist): JsonResponse
    {
        try {
            $newStatus = !$blacklist->is_active;

            $blacklist->update([
                'is_active' => $newStatus,
            ]);

            return response()->json([
                'success'   => true,
                'is_active' => (bool) $newStatus,
                'message'   => 'Blacklist rule status updated successfully.',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle status: ' . $e->getMessage(),
            ], 500);
        }
    }
}