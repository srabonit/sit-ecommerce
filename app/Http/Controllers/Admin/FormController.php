<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\FormSubmissionValue;
use App\Models\EmailTemplate;
use App\Models\EmailSuppressionList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FormController extends Controller
{
    /**
     * Display a listing of the forms.
     */
    public function index()
    {
        $forms = Form::withCount('fields')
            ->withCount('submissions')
            ->latest()
            ->paginate(20);

        return view('admin.forms.index', compact('forms'));
    }

    /**
     * Show the form for creating a new form.
     */
    public function create()
    {
        return view('admin.forms.create');
    }

    /**
     * Store a newly created form in database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:forms,slug',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'logo' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'admin_email' => [
                'nullable',
                'string',
                'max:500',
            ],

            'email_subject' => [
                'nullable',
                'string',
                'max:255',
            ],

            'success_message' => [
                'nullable',
                'string',
            ],

            'redirect_url' => [
                'nullable',
                'string',
                'max:500',
            ],

            'custom_id' => [
                'nullable',
                'string',
                'max:100',
            ],

            'custom_class' => [
                'nullable',
                'string',
                'max:255',
            ],

            'styles' => [
                'nullable',
                'array',
            ],
        ]);

        // Auto-generate slug if not provided
        $validated['slug'] = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        // Explicitly capture logo path from Global Media Library
        $validated['logo'] = $request->filled('logo') ? trim($request->input('logo')) : null;

        // Display Header & Description toggles
        $validated['show_title'] = $request->boolean('show_title', true);
        $validated['show_description'] = $request->boolean('show_description', true);

        // Activation & Submission flags
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['save_submissions'] = $request->boolean('save_submissions', true);
        $validated['send_admin_email'] = $request->boolean('send_admin_email', true);
        $validated['send_user_email'] = $request->boolean('send_user_email', false);

        // Store custom container ID and Class into styles JSON safely
        $styles = $request->input('styles', []);
        if ($request->filled('custom_id')) {
            $styles['custom_id'] = trim($request->input('custom_id'));
        }
        if ($request->filled('custom_class')) {
            $styles['custom_class'] = trim($request->input('custom_class'));
        }
        if ($request->filled('custom_css')) {
            $styles['custom_css'] = $request->input('custom_css');
        }
        $validated['styles'] = $styles;

        // Safely filter columns to prevent unknown column SQL errors
        $existingColumns = Schema::getColumnListing('forms');
        $safeData = array_intersect_key($validated, array_flip($existingColumns));

        $form = Form::create($safeData);

        return redirect()
            ->route('admin.forms.edit', $form)
            ->with('success', 'Form created successfully.');
    }

    /**
     * Show the form builder studio for editing the specified form.
     */
    public function edit(Form $form)
    {
        $form->load('fields');

        return view('admin.forms.edit', compact('form'));
    }

    /**
     * Update the specified form settings, appearance styles, emails or integrations.
     */
    public function update(Request $request, Form $form)
    {
        // Flexible validation: 'sometimes' enables updating from independent tabs
        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'unique:forms,slug,' . $form->id,
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'logo' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'admin_email' => [
                'nullable',
                'string',
                'max:500',
            ],

            'email_subject' => [
                'nullable',
                'string',
                'max:255',
            ],

            'user_email_subject' => [
                'nullable',
                'string',
                'max:255',
            ],

            'success_message' => [
                'nullable',
                'string',
            ],

            'redirect_url' => [
                'nullable',
                'string',
                'max:500',
            ],

            'theme_preset' => [
                'nullable',
                'string',
                'max:100',
            ],

            'submit_button_text' => [
                'nullable',
                'string',
                'max:255',
            ],

            'submit_button_class' => [
                'nullable',
                'string',
                'max:255',
            ],

            'custom_css' => [
                'nullable',
                'string',
            ],

            'custom_id' => [
                'nullable',
                'string',
                'max:100',
            ],

            'custom_class' => [
                'nullable',
                'string',
                'max:255',
            ],

            'captcha_type' => [
                'nullable',
                'string',
                'max:50',
            ],

            'rate_limit_per_ip' => [
                'nullable',
                'integer',
                'min:1',
                'max:1000',
            ],

            'webhook_url' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'webhook_method' => [
                'nullable',
                'string',
                'max:10',
            ],

            'webhook_auth_header' => [
                'nullable',
                'string',
                'max:500',
            ],

            'styles' => [
                'nullable',
                'array',
            ],
        ]);

        // 1. General Settings Tab Processing
        if ($request->has('name') || $request->has('slug')) {
            if (!empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['slug']);
            }
            $validated['show_title'] = $request->boolean('show_title');
            $validated['show_description'] = $request->boolean('show_description');
            $validated['is_active'] = $request->boolean('is_active');
            $validated['save_submissions'] = $request->boolean('save_submissions');
        }

        // 2. Logo / Header Banner (Support both setting and clearing)
        if ($request->has('logo')) {
            $validated['logo'] = $request->filled('logo') ? trim($request->input('logo')) : null;
        }

        // 3. Email & Notifications Tab Processing
        if ($request->has('admin_email') || $request->has('email_subject') || $request->has('user_email_subject')) {
            $validated['send_admin_email'] = $request->boolean('send_admin_email');
            $validated['send_user_email'] = $request->boolean('send_user_email');
        }

        // 4. Security & Anti-Spam Tab Processing
        if ($request->has('captcha_type') || $request->has('rate_limit_per_ip')) {
            $validated['prevent_duplicates'] = $request->boolean('prevent_duplicates');
        }

        // 5. Appearance, Styles, Custom ID/Class & Layout Processing
        if ($request->has('styles') || $request->has('custom_css') || $request->has('theme_preset') || $request->has('custom_id') || $request->has('custom_class')) {
            $existingStyles = is_array($form->styles) 
                ? $form->styles 
                : (json_decode($form->styles ?? '', true) ?? []);

            $newStyles = $request->input('styles', []);

            // Handle custom form container ID and Class
            if ($request->has('custom_id')) {
                $newStyles['custom_id'] = trim($request->input('custom_id', ''));
            }
            if ($request->has('custom_class')) {
                $newStyles['custom_class'] = trim($request->input('custom_class', ''));
            }

            // Put custom_css into styles JSON safely
            if ($request->has('custom_css')) {
                $newStyles['custom_css'] = $request->input('custom_css');
            }

            // Sync button label into styles
            if ($request->has('submit_button_text')) {
                $newStyles['button_text'] = $request->input('submit_button_text');
            }

            // Merge new styles into existing styles without losing previous keys
            $validated['styles'] = array_merge($existingStyles, $newStyles);
        }

        // Safely update only columns that exist in the database table
        $existingColumns = Schema::getColumnListing('forms');
        $safeData = array_intersect_key($validated, array_flip($existingColumns));

        $form->update($safeData);

        return back()->with('success', 'Form updated successfully.');
    }

    /**
     * Remove the specified form and all related submissions from storage.
     */
    public function destroy(Form $form)
    {
        $form->delete();

        return redirect()
            ->route('admin.forms.index')
            ->with('success', 'Form deleted successfully.');
    }

    /**
     * Submissions & Leads Ledger with Complete AJAX Filter & Export Controls
     */
    public function submissions(Request $request)
    {
        $selectedFormId = $request->input('form_id');

        $query = FormSubmission::with(['form', 'values.field', 'repliedBy:id,name,email'])
            ->latest('id');

        // 1. Form Filter
        if ($request->filled('form_id')) {
            $query->where('form_id', $request->input('form_id'));
        }

        // 2. Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // 3. Search Filter (Lead info, IP, text, user agent, reply subject)
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('user_agent', 'like', "%{$search}%")
                  ->orWhere('reply_subject', 'like', "%{$search}%")
                  ->orWhereHas('values', function ($vq) use ($search) {
                      $vq->where('value', 'like', "%{$search}%");
                  })
                  ->orWhereHas('form', function ($fq) use ($search) {
                      $fq->where('name', 'like', "%{$search}%")
                         ->orWhere('slug', 'like', "%{$search}%");
                  });
            });
        }

        // 4. Date Range Filters (Date-wise, Month-wise, Year-wise)
        if ($request->filled('start_date')) {
            $startDate = $request->input('start_date');
            $query->where(function ($q) use ($startDate) {
                $q->whereDate('submitted_at', '>=', $startDate)
                  ->orWhereDate('created_at', '>=', $startDate);
            });
        }

        if ($request->filled('end_date')) {
            $endDate = $request->input('end_date');
            $query->where(function ($q) use ($endDate) {
                $q->whereDate('submitted_at', '<=', $endDate)
                  ->orWhereDate('created_at', '<=', $endDate);
            });
        }

        // 5. CSV Lead Export Handler
        if ($request->input('export') === 'csv') {
            return $this->exportSubmissionsToCsv($query);
        }

        // 6. Real-time AJAX Response
        if ($request->ajax() || $request->has('ajax') || $request->wantsJson()) {
            $perPage = (int) $request->input('per_page', 20);
            if ($perPage < 5 || $perPage > 200) {
                $perPage = 20;
            }
            $submissions = $query->paginate($perPage);

            // Append Customer email, name, and replied_by_name dynamically
            $submissions->getCollection()->transform(function ($sub) {
                $sub->customer_email = $sub->customer_email;
                $sub->customer_name  = $sub->customer_name;
                $sub->replied_by_name = $sub->repliedBy?->name;
                return $sub;
            });

            return response()->json($submissions);
        }

        // Normal View Boot
        $forms = Form::select('id', 'name', 'slug')->orderBy('name')->get();
        $emailTemplates = class_exists(EmailTemplate::class) 
            ? EmailTemplate::active()->get(['id', 'name', 'subject', 'html_content']) 
            : collect();

        return view('admin.forms.submissions', compact('forms', 'selectedFormId', 'emailTemplates'));
    }

    /**
     * Show single submission payload details via AJAX
     */
    public function showSubmission($id)
    {
        $submission = FormSubmission::with(['form', 'values.field', 'repliedBy:id,name,email'])->findOrFail($id);

        return response()->json([
            'success'        => true,
            'submission'     => $submission,
            'customer_email' => $submission->customer_email,
            'customer_name'  => $submission->customer_name,
            'replied_by'     => $submission->repliedBy?->name,
        ]);
    }

    /**
     * Instant 1-Click Email Reply to a specific lead
     */
    public function replySubmission(Request $request, $id)
    {
        $submission = FormSubmission::with(['form', 'values.field'])->findOrFail($id);

        $validated = $request->validate([
            'subject'     => 'required|string|max:255',
            'message'     => 'required|string',
            'template_id' => 'nullable|exists:email_templates,id',
        ]);

        $recipientEmail = $submission->customer_email;
        if (!$recipientEmail) {
            return response()->json([
                'success' => false, 
                'message' => 'Ei submission-e customer-er kono valid email address paowa jayni.'
            ], 422);
        }

        if (class_exists(EmailSuppressionList::class) && EmailSuppressionList::isSuppressed($recipientEmail)) {
            return response()->json([
                'success' => false, 
                'message' => 'Customer-er email address-ti suppression blacklist-e ache.'
            ], 422);
        }

        $customerName = $submission->customer_name;
        $storeName = config('app.name', 'Shop Now BD');
        $adminUser = auth()->user();
        $adminName = $adminUser ? $adminUser->name : 'Support Desk';

        // Prepare Original Submission Summary Reference
        $origLines = [];
        foreach ($submission->values as $v) {
            $lbl = $v->field?->label ?: ($v->field?->name ?: 'Field');
            $val = $v->value;
            if (!empty($val) && !str_starts_with((string)$val, 'uploads/forms/')) {
                $origLines[] = "<strong>" . e($lbl) . ":</strong> " . e(Str::limit($val, 100));
            }
        }
        $originalDetails = implode('<br>', $origLines);

        // Dynamic Tag Replacements
        $replyMessage = str_replace(
            ['{name}', '{customer_name}', '{email}', '{store_name}', '{form_name}'],
            [$customerName, $customerName, $recipientEmail, $storeName, $submission->form?->name ?? 'Form'],
            $validated['message']
        );

        $replySubject = str_replace(
            ['{name}', '{customer_name}', '{store_name}', '{form_name}'],
            [$customerName, $customerName, $storeName, $submission->form?->name ?? 'Form'],
            $validated['subject']
        );

        try {
            Mail::send('emails.form-reply', [
                'form'            => $submission->form,
                'submission'      => $submission,
                'customerName'    => $customerName,
                'replySubject'    => $replySubject,
                'replyMessage'    => $replyMessage,
                'originalDetails' => $originalDetails,
                'adminName'       => $adminName,
            ], function ($msg) use ($recipientEmail, $replySubject) {
                $msg->to($recipientEmail)
                    ->subject($replySubject);
            });

            // Update Submission Audit & Status
            $submission->update([
                'status'        => 'replied',
                'replied_by'    => auth()->id(),
                'replied_at'    => now(),
                'reply_subject' => $replySubject,
                'reply_message' => $replyMessage,
            ]);

            return response()->json([
                'success'    => true,
                'message'    => "Reply email {$recipientEmail} thikanay shofolbhabe pathano hoyeche.",
                'replied_by' => $adminName,
                'replied_at' => $submission->replied_at->format('d M, Y h:i A'),
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Email pathate shomossha hoyeche: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk Instant Reply to multiple selected leads
     */
    public function bulkReplySubmissions(Request $request)
    {
        $validated = $request->validate([
            'ids'     => 'required|array|min:1',
            'ids.*'   => 'integer|exists:form_submissions,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $submissions = FormSubmission::with(['form', 'values.field'])
            ->whereIn('id', $validated['ids'])
            ->get();

        if ($submissions->isEmpty()) {
            return response()->json([
                'success' => false, 
                'message' => 'Reply korar moto kono submission paowa jayni.'
            ], 422);
        }

        $storeName = config('app.name', 'Shop Now BD');
        $adminUser = auth()->user();
        $adminName = $adminUser ? $adminUser->name : 'Support Desk';

        $sentCount = 0;
        $failCount = 0;

        foreach ($submissions as $sub) {
            $email = $sub->customer_email;
            if (!$email || (class_exists(EmailSuppressionList::class) && EmailSuppressionList::isSuppressed($email))) {
                $failCount++;
                continue;
            }

            $customerName = $sub->customer_name;
            $replyMessage = str_replace(
                ['{name}', '{customer_name}', '{email}', '{store_name}', '{form_name}'],
                [$customerName, $customerName, $email, $storeName, $sub->form?->name ?? 'Form'],
                $validated['message']
            );

            $replySubject = str_replace(
                ['{name}', '{customer_name}', '{store_name}', '{form_name}'],
                [$customerName, $customerName, $storeName, $sub->form?->name ?? 'Form'],
                $validated['subject']
            );

            try {
                Mail::send('emails.form-reply', [
                    'form'            => $sub->form,
                    'submission'      => $sub,
                    'customerName'    => $customerName,
                    'replySubject'    => $replySubject,
                    'replyMessage'    => $replyMessage,
                    'originalDetails' => null,
                    'adminName'       => $adminName,
                ], function ($msg) use ($email, $replySubject) {
                    $msg->to($email)->subject($replySubject);
                });

                $sub->update([
                    'status'        => 'replied',
                    'replied_by'    => auth()->id(),
                    'replied_at'    => now(),
                    'reply_subject' => $replySubject,
                    'reply_message' => $replyMessage,
                ]);

                $sentCount++;
            } catch (\Throwable $e) {
                $failCount++;
            }

            usleep(25000); // Prevent SMTP flood
        }

        return response()->json([
            'success' => true,
            'message' => "Bulk reply shompurno hoyeche: {$sentCount} ti pathano hoyeche, {$failCount} ti skipped/failed."
        ]);
    }

    /**
     * Flag submission as SPAM and permanently block Client IP Address
     */
    public function blockIp(Request $request, $id)
    {
        $submission = FormSubmission::with('values.field')->findOrFail($id);
        $ip = $submission->ip_address;

        $submission->update([
            'status'        => 'spam',
            'is_ip_blocked' => true,
        ]);

        // Blacklist IP in Cache for high-performance instant check
        if (!empty($ip) && $ip !== '127.0.0.1') {
            Cache::forever('blocked_ip_' . md5($ip), true);
        }

        // Add email to suppression blacklist if exists
        $email = $submission->customer_email;
        if ($email && class_exists(EmailSuppressionList::class)) {
            EmailSuppressionList::suppress(
                $email, 
                'spam', 
                "Blocked from Form Lead #{$submission->id} by admin", 
                auth()->id()
            );
        }

        return response()->json([
            'success' => true,
            'message' => "IP [{$ip}] block kora hoyeche ebong submission #{$id} spam hishebe mark kora hoyeche."
        ]);
    }

    /**
     * Stream Filtered Leads to CSV Format
     */
    protected function exportSubmissionsToCsv($query): StreamedResponse
    {
        $filename = 'form_leads_export_' . date('Y_m_d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel Compatibility
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Submission ID',
                'Form Name',
                'Customer Email',
                'Customer Name',
                'Status',
                'Replied By',
                'Replied At',
                'Submitted Fields & Data',
                'Client IP Address',
                'Referer URL',
                'User Agent',
                'Submitted At',
            ]);

            $query->chunk(100, function ($submissions) use ($handle) {
                foreach ($submissions as $sub) {
                    $fieldsFormatted = [];
                    if ($sub->values) {
                        foreach ($sub->values as $v) {
                            $label = $v->field ? ($v->field->label ?: $v->field->name) : 'Field';
                            $val = $v->value;
                            if (is_string($val) && (str_starts_with($val, '[') || str_starts_with($val, '{'))) {
                                $decoded = json_decode($val, true);
                                if (is_array($decoded)) {
                                    $val = implode(', ', $decoded);
                                }
                            }
                            $fieldsFormatted[] = "{$label}: " . ($val ?: 'N/A');
                        }
                    }

                    fputcsv($handle, [
                        $sub->id,
                        $sub->form ? $sub->form->name : 'N/A',
                        $sub->customer_email ?: 'N/A',
                        $sub->customer_name ?: 'N/A',
                        strtoupper($sub->status ?: 'NEW'),
                        $sub->repliedBy?->name ?: 'N/A',
                        $sub->replied_at ? (string)$sub->replied_at->format('Y-m-d H:i') : 'No',
                        implode(" | ", $fieldsFormatted),
                        $sub->ip_address ?: 'N/A',
                        $sub->referer ?: 'Direct',
                        $sub->user_agent ?: 'N/A',
                        $sub->submitted_at ? (string)$sub->submitted_at : (string)$sub->created_at,
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Update single submission status.
     */
    public function updateSubmissionStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:new,read,reviewed,contacted,replied,closed,spam',
        ]);

        $submission = FormSubmission::findOrFail($id);
        $submission->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Submission status updated successfully.',
            'status'  => $submission->status,
        ]);
    }

    /**
     * Delete a single submission and its files.
     */
    public function destroySubmission($id)
    {
        $submission = FormSubmission::with('values')->findOrFail($id);

        // Delete uploaded files associated with this submission if any
        foreach ($submission->values as $val) {
            $this->removeUploadedFormFile($val->value);
        }

        $submission->values()->delete();
        $submission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Submission entry deleted successfully.',
        ]);
    }

    /**
     * Bulk update status of selected submissions.
     */
    public function bulkSubmissionStatus(Request $request)
    {
        $validated = $request->validate([
            'ids'    => 'required|array',
            'ids.*'  => 'integer',
            'status' => 'required|string|in:new,read,reviewed,contacted,replied,closed,spam',
        ]);

        FormSubmission::whereIn('id', $validated['ids'])
            ->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Selected submissions updated successfully.',
        ]);
    }

    /**
     * Bulk delete selected submissions.
     */
    public function bulkSubmissionDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer',
        ]);

        $submissions = FormSubmission::with('values')
            ->whereIn('id', $validated['ids'])
            ->get();

        foreach ($submissions as $sub) {
            foreach ($sub->values as $val) {
                $this->removeUploadedFormFile($val->value);
            }
            $sub->values()->delete();
            $sub->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Selected submissions deleted successfully.',
        ]);
    }

    /**
     * Helper to safely remove uploaded form attachments across localhost and cPanel environments.
     */
    protected function removeUploadedFormFile(?string $value): void
    {
        if (!$value || !is_string($value) || !str_contains($value, 'uploads/forms/')) {
            return;
        }

        $clean = ltrim(str_replace(['public_html/', 'public/'], '', $value), '/');

        $paths = [
            public_path($clean),
            is_dir(base_path('../public_html')) ? base_path('../public_html/' . $clean) : null,
        ];

        foreach (array_filter($paths) as $path) {
            if (is_file($path) && file_exists($path)) {
                @unlink($path);
            }
        }
    }
}