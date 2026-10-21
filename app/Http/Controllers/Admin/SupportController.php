<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class SupportController extends Controller
{
    // ১. সকল টিকিট লিস্ট ও স্ট্যাটাস অনুযায়ী ফিল্টারিং
    public function index(Request $request)
    {
        $query = DB::table('support_tickets');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->latest('id')->paginate(15)->withQueryString();

        $counts = [
            'all'      => DB::table('support_tickets')->count(),
            'pending'  => DB::table('support_tickets')->where('status', 'pending')->count(),
            'checked'  => DB::table('support_tickets')->where('status', 'checked')->count(),
            'resolved' => DB::table('support_tickets')->where('status', 'resolved')->count(),
            'closed'   => DB::table('support_tickets')->where('status', 'closed')->count(),
        ];

        return view('admin.support.index', compact('tickets', 'counts'));
    }

    // ২. নির্দিষ্ট টিকিটের বিস্তারিত পেজ
    public function show($id)
    {
        $ticket = DB::table('support_tickets')->where('id', $id)->first();
        if (!$ticket) {
            return redirect()->route('admin.support.index')->with('error', 'Ticket not found.');
        }

        return view('admin.support.show', compact('ticket'));
    }

  public function replyTicket(Request $request, $id)
    {
        $request->validate([
            'status'      => 'required|in:pending,checked,resolved,closed',
            'admin_reply' => 'nullable|string|max:3000',
        ]);

        $ticket = DB::table('support_tickets')->where('id', $id)->first();
        if (!$ticket) {
            return back()->with('error', 'Ticket not found.');
        }

        // ১. ডাটাবেজ আপডেট
        $updateData = [
            'status'     => $request->status,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('support_tickets', 'admin_reply')) {
            $updateData['admin_reply'] = $request->admin_reply;
        }

        DB::table('support_tickets')->where('id', $id)->update($updateData);

        // ২. ইউজার ইমেইল বের করা (টিকিট টেবিল অথবা সরাসরি users টেবিল থেকে)
        $userEmail = $ticket->user_email ?? null;

        if (empty($userEmail) && !empty($ticket->user_name)) {
            $matchedUser = DB::table('users')->where('name', $ticket->user_name)->first();
            if ($matchedUser && !empty($matchedUser->email)) {
                $userEmail = $matchedUser->email;
            }
        }

        $userEmail = trim((string)$userEmail);
        $isValidEmail = !empty($userEmail) && strtolower($userEmail) !== 'n/a' && filter_var($userEmail, FILTER_VALIDATE_EMAIL);

        // ৩. ইমেইল পাঠানো
        if ($isValidEmail) {
            try {
                $mailData = [
                    'ticket_no'   => $ticket->ticket_no,
                    'subject'     => $ticket->subject,
                    'status'      => ucfirst($request->status),
                    'admin_reply' => $request->admin_reply ?? 'No additional message provided.',
                    'user_name'   => $ticket->user_name ?? 'Customer',
                ];

                Mail::send([], [], function ($message) use ($mailData, $userEmail) {
                    $message->to($userEmail)
                        ->subject('💬 Support Ticket Update: [' . $mailData['ticket_no'] . '] - Status: ' . $mailData['status'])
                        ->html("
                            <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
                                <h2 style='color: #2563eb; margin-top: 0;'>Support Ticket Update</h2>
                                <p>Dear <strong>{$mailData['user_name']}</strong>,</p>
                                <p>There is an update on your support ticket <strong>({$mailData['ticket_no']})</strong>.</p>
                                
                                <div style='background: #f8fafc; padding: 12px 18px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #2563eb;'>
                                    <p style='margin: 0;'><strong>Current Status:</strong> <span style='color: #0284c7; font-weight: bold; text-transform: uppercase;'>{$mailData['status']}</span></p>
                                </div>

                                <p><strong>Management Reply:</strong></p>
                                <div style='background: #f1f5f9; padding: 15px; border-radius: 6px; border: 1px solid #cbd5e1;'>
                                    " . nl2br(e($mailData['admin_reply'])) . "
                                </div>

                                <p style='margin-top: 20px; font-size: 13px; color: #64748b;'>You can also check your ticket status anytime from your account dashboard.</p>
                            </div>
                        ");
                });
            } catch (\Exception $e) {
                Log::error('Support ticket reply email error: ' . $e->getMessage());
            }
        }

        $message = $isValidEmail 
            ? "Status updated and reply sent to customer email ({$userEmail}) successfully." 
            : 'Status and reply updated successfully (No email was sent as customer email is missing or invalid).';

        return redirect()->route('admin.support.index')->with('success', $message);
    }
}