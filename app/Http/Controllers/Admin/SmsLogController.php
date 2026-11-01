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

        // Filter by Status (sent, delivered, failed, blocked)
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
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
                  ->orWhere('sender_id', 'like', "%{$search}%");
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

        // High-level statistics
        $stats = [
            'total_sent'      => SmsLog::whereIn('status', ['sent', 'delivered'])->count(),
            'total_delivered' => SmsLog::where('status', 'delivered')->count(),
            'total_failed'    => SmsLog::where('status', 'failed')->count(),
            'total_blocked'   => SmsLog::where('status', 'blocked')->count(),
            'total_cost'      => (float) SmsLog::sum('cost'),
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
                'delivered_at'     => $log->delivered_at ? Carbon::parse($log->delivered_at)->format('M d, Y h:i:s A') : 'Pending DLR',
                'created_at'       => $log->created_at ? $log->created_at->format('M d, Y h:i:s A') : 'N/A',
            ],
        ]);
    }

    /**
     * Trigger immediate DLR inquiry for a specific sent SMS log.
     */
    public function checkStatus(SmsLog $log): JsonResponse
    {
        if (empty($log->message_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot check delivery status: No Gateway Message ID associated with this log.',
            ], 422);
        }

        try {
            (new CheckSmsDeliveryStatusJob($log->id))->handle();
            $log->refresh();

            return response()->json([
                'success'      => true,
                'status'       => $log->status,
                'delivered_at' => $log->delivered_at ? Carbon::parse($log->delivered_at)->format('M d, Y h:i:s A') : null,
                'message'      => "Delivery status updated: {$log->status}",
            ]);
        } catch (Throwable $e) {
            Log::error("SmsLogController checkStatus exception for log #{$log->id}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Delivery status inquiry error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resend a failed or blocked SMS via background queue.
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

        return back()->with('success', "SMS to {$log->recipient} has been requeued for delivery.");
    }

    /**
     * Export filtered logs as a streaming CSV file to avoid memory overload.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $fileName = 'sms_logs_' . date('Y-m-d_His') . '.csv';

        $query = SmsLog::query()->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
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
                  ->orWhere('sender_id', 'like', "%{$search}%");
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

            // Add UTF-8 BOM for Microsoft Excel compatibility
            fputs($file, "\xEF\xBB\xBF");

            // CSV Header Row
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
                'Delivered At',
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
                        $log->delivered_at ? Carbon::parse($log->delivered_at)->toDateTimeString() : 'N/A',
                        $log->created_at ? $log->created_at->toDateTimeString() : 'N/A',
                    ]);
                }
            });

            fclose($file);
        }, 200, $headers);
    }
}