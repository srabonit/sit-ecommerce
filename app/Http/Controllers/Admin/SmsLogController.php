<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\CheckSmsDeliveryStatusJob;
use App\Jobs\SendSingleSmsJob;
use App\Models\SmsLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class SmsLogController extends Controller
{
    /**
     * Display a paginated listing of SMS audit logs with multi-parameter filtering.
     */
    public function index(Request $request): View
    {
        $query = SmsLog::query()->latest('id');

        // Filter by Status (sent, failed, blocked)
        if ($request->filled('status')) {
            $statusVal = strtolower(trim($request->query('status')));
            if ($statusVal === 'sent') {
                $query->whereIn('status', ['sent', 'delivered']);
            } else {
                $query->where('status', $statusVal);
            }
        }

        // Filter by SMS Type (transactional, otp, marketing)
        if ($request->filled('sms_type')) {
            $query->where('sms_type', $request->query('sms_type'));
        }

        // Filter by Provider Driver
        if ($request->filled('provider')) {
            $query->where('provider', $request->query('provider'));
        }

        // Search by Recipient Number, Message ID, or Sender ID
        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('recipient', 'like', "%{$search}%")
                  ->orWhere('message_id', 'like', "%{$search}%")
                  ->orWhere('sender_id', 'like', "%{$search}%")
                  ->orWhere('message_content', 'like', "%{$search}%");
            });
        }

        // Date Range Filter
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', Carbon::parse($request->query('start_date'))->startOfDay());
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', Carbon::parse($request->query('end_date'))->endOfDay());
        }

        $logs = $query->paginate(20)->withQueryString();

        // Accurate Sent, Failed, and Spend Statistics
        $stats = [
            'total_dispatched' => SmsLog::count(),
            'total_sent'       => SmsLog::whereIn('status', ['sent', 'delivered'])->count(),
            'total_failed'     => SmsLog::whereIn('status', ['failed', 'blocked'])->count(),
            'total_cost'       => (float) SmsLog::sum('cost'),
        ];

        return view('admin.sms.logs.index', compact('logs', 'stats'));
    }

    /**
     * Display detailed inspection modal/view for a single SMS audit log.
     */
    public function show(SmsLog $log): JsonResponse
    {
        $rawResponse = $log->gateway_response;
        $parsedResponse = is_string($rawResponse) ? (json_decode($rawResponse, true) ?? $rawResponse) : $rawResponse;

        return response()->json([
            'success' => true,
            'data'    => [
                'id'               => $log->id,
                'recipient'        => $log->recipient,
                'sms_type'         => ucfirst($log->sms_type ?? 'transactional'),
                'provider'         => $log->provider ?? 'onecodesoft',
                'sender_id'        => $log->sender_id ?: 'Default',
                'message_id'       => $log->message_id ?: 'N/A',
                'message_content'  => $log->message_content,
                'characters_count' => (int) ($log->characters_count ?? mb_strlen($log->message_content ?? '')),
                'sms_parts'        => (int) ($log->sms_parts ?? 1),
                'cost'             => number_format((float) ($log->cost ?? 0), 4) . ' BDT',
                'status'           => $log->status ?? 'sent',
                'status_code'      => $log->status_code ?: 'N/A',
                'gateway_response' => $parsedResponse,
                'is_sandbox'       => (bool) $log->is_sandbox,
                'sent_at'          => $log->sent_at ? Carbon::parse($log->sent_at)->format('M d, Y h:i:s A') : 'N/A',
                'created_at'       => $log->created_at ? $log->created_at->format('M d, Y h:i:s A') : 'N/A',
            ],
        ]);
    }

    /**
     * Delete a single SMS log permanently.
     */
    public function destroy(SmsLog $log): JsonResponse|RedirectResponse
    {
        try {
            $log->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'SMS log record deleted successfully.',
                ]);
            }

            return redirect()
                ->route('admin.sms.logs.index')
                ->with('success', 'SMS log record deleted successfully.');
        } catch (Throwable $e) {
            Log::error("SmsLog destroy error: " . $e->getMessage());

            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete log.'], 500);
            }

            return redirect()
                ->route('admin.sms.logs.index')
                ->with('error', 'Failed to delete log.');
        }
    }

    /**
     * Delete multiple selected SMS logs in bulk.
     */
    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
    {
        $ids = $request->input('ids', []);

        if (empty($ids) || !is_array($ids)) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'No logs selected for deletion.'], 422);
            }
            return redirect()->route('admin.sms.logs.index')->with('error', 'No logs selected.');
        }

        try {
            $count = SmsLog::whereIn('id', $ids)->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$count} SMS log(s) permanently deleted.",
                ]);
            }

            return redirect()
                ->route('admin.sms.logs.index')
                ->with('success', "{$count} SMS log(s) deleted successfully.");
        } catch (Throwable $e) {
            Log::error("SmsLog bulkDelete error: " . $e->getMessage());

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to perform bulk deletion.'], 500);
            }

            return redirect()
                ->route('admin.sms.logs.index')
                ->with('error', 'Failed to perform bulk deletion.');
        }
    }

    /**
     * Resend a failed or blocked SMS via background queue or instant dispatch.
     */
    public function resend(SmsLog $log): RedirectResponse
    {
        SendSingleSmsJob::dispatch(
            $log->recipient,
            $log->message_content,
            $log->sms_type ?? 'transactional',
            [
                'order_id'      => $log->order_id,
                'user_id'       => $log->user_id,
                'campaign_id'   => $log->campaign_id,
                'sender_id'     => $log->sender_id,
                'bypass_global' => true,
            ]
        )->onQueue('sms-notifications');

        return redirect()->route('admin.sms.logs.index')->with('success', "SMS to {$log->recipient} has been requeued for delivery.");
    }

    /**
     * Export filtered logs as a streaming CSV file to avoid memory overload.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $fileName = 'sms_logs_' . date('Y-m-d_His') . '.csv';

        $query = SmsLog::query()->latest('id');

        if ($request->filled('status')) {
            $statusVal = strtolower(trim($request->query('status')));
            if ($statusVal === 'sent') {
                $query->whereIn('status', ['sent', 'delivered']);
            } else {
                $query->where('status', $statusVal);
            }
        }
        if ($request->filled('sms_type')) {
            $query->where('sms_type', $request->query('sms_type'));
        }
        if ($request->filled('provider')) {
            $query->where('provider', $request->query('provider'));
        }
        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('recipient', 'like', "%{$search}%")
                  ->orWhere('message_id', 'like', "%{$search}%")
                  ->orWhere('sender_id', 'like', "%{$search}%")
                  ->orWhere('message_content', 'like', "%{$search}%");
            });
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', Carbon::parse($request->query('start_date'))->startOfDay());
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', Carbon::parse($request->query('end_date'))->endOfDay());
        }

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return Response::stream(function () use ($query) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'Log ID',
                'Recipient',
                'Type',
                'Provider',
                'Sender ID',
                'Message ID',
                'Message Content',
                'Characters',
                'Parts',
                'Cost (BDT)',
                'Status',
                'Sent At',
                'Created At',
            ]);

            $query->chunk(500, function ($logs) use ($file) {
                foreach ($logs as $log) {
                    fputcsv($file, [
                        $log->id,
                        $log->recipient,
                        $log->sms_type,
                        $log->provider,
                        $log->sender_id ?: 'Default',
                        $log->message_id ?: 'N/A',
                        $log->message_content,
                        $log->characters_count,
                        $log->sms_parts,
                        number_format((float) ($log->cost ?? 0), 4),
                        $log->status,
                        $log->sent_at ? Carbon::parse($log->sent_at)->toDateTimeString() : 'N/A',
                        $log->created_at ? $log->created_at->toDateTimeString() : 'N/A',
                    ]);
                }
            });

            fclose($file);
        }, 200, $headers);
    }
}