<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\EmailSubscriber;
use App\Models\EmailConsentLog;
use App\Models\EmailSuppressionList;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\FormSubmissionValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FormSubmissionController extends Controller
{
    /**
     * Display the specified standalone dynamic form page by slug or ID.
     */
    public function show($slug)
    {
        $form = Form::with([
            'fields' => function ($query) {
                $query->where('is_active', true)
                      ->orderBy('sort_order', 'asc')
                      ->orderBy('id', 'asc');
            }
        ])
        ->where(function ($q) use ($slug) {
            $q->where('slug', $slug);
            if (is_numeric($slug)) {
                $q->orWhere('id', (int) $slug);
            }
        })
        ->where('is_active', true)
        ->firstOrFail();

        return view('frontend.forms.show', compact('form'));
    }

    /**
     * Submit Dynamic Form with IP Tracking, Anti-Spam Security, Lead Sync & Autoresponder
     */
    public function submit(Request $request, $formIdentifier)
    {
        // 1. Resolve Form Model safely (supports both model binding & slug/id string)
        $form = ($formIdentifier instanceof Form)
            ? $formIdentifier
            : Form::where('id', $formIdentifier)->orWhere('slug', $formIdentifier)->firstOrFail();

        // Verify Active Status
        if (!$form->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This form is currently inactive or unavailable.',
            ], 403);
        }

        $userIp = $request->ip() ?: '127.0.0.1';

        // 2. Anti-Spam: Silent Honeypot Trap Verification
        if (
            $request->filled('_df_trap_honey') ||
            $request->filled('website_hp_check') ||
            $request->filled('_hp_security_check') ||
            $request->filled('website_url_trap')
        ) {
            Log::info('Spam Bot Trapped via Honeypot', [
                'form_id' => $form->id,
                'ip'      => $userIp,
            ]);

            return response()->json([
                'success' => true,
                'message' => $form->success_message ?: 'Your message has been sent successfully.',
            ]);
        }

        // 3. Anti-Spam: Dynamic IP Rate Limiting
        $maxPerHour = (int) ($form->rate_limit_per_ip ?: 30);
        $rateLimitKey = 'form_rate_' . $form->id . '_' . md5($userIp);
        $attempts = (int) Cache::get($rateLimitKey, 0);

        if ($attempts >= $maxPerHour) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests from your IP. Please try again later.',
            ], 429);
        }

        // 4. Anti-Spam: Rapid Duplicate Submission Blocker
        if (!empty($form->prevent_duplicates)) {
            $payloadHash = md5($form->id . '_' . $userIp . '_' . serialize($request->except(['_token', '_df_trap_honey', '_hp_security_check'])));
            $dupKey = 'form_dup_' . $payloadHash;

            if (Cache::has($dupKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate submission detected. Please wait a moment before trying again.',
                ], 429);
            }

            Cache::put($dupKey, true, 30);
        }

        // 5. Fetch Fields & Build Validation Rules
        $fields = $form->fields()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $rules = [];
        $messages = [];

        foreach ($fields as $field) {
            if ($field->type === 'html') {
                continue;
            }

            $fieldName = trim((string) $field->name);

            if (empty($fieldName) || !preg_match('/^[A-Za-z][A-Za-z0-9_\-]*$/', $fieldName)) {
                continue;
            }

            $fieldRules = [];

            if ($field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            switch ($field->type) {
                case 'email':
                    // Standard email validation (safe for localhost and live server)
                    $fieldRules[] = 'email';
                    break;

                case 'number':
                    $fieldRules[] = 'numeric';
                    if ($field->min_value !== null) {
                        $fieldRules[] = 'min:' . $field->min_value;
                    }
                    if ($field->max_value !== null) {
                        $fieldRules[] = 'max:' . $field->max_value;
                    }
                    break;

                case 'url':
                    $fieldRules[] = 'url';
                    break;

                case 'file':
                    $fieldRules[] = 'file';
                    if ($field->file_max_size !== null && (int) $field->file_max_size > 0) {
                        $fieldRules[] = 'max:' . (int) $field->file_max_size;
                    } else {
                        $fieldRules[] = 'max:5120';
                    }

                    $mimes = $this->normalizeList($field->file_allowed_mimes);
                    if (!empty($mimes)) {
                        $fieldRules[] = 'mimetypes:' . implode(',', $mimes);
                    }

                    $extensions = $this->normalizeExtensions($field->file_allowed_extensions);
                    if (!empty($extensions)) {
                        $fieldRules[] = 'extensions:' . implode(',', $extensions);
                    }
                    break;

                case 'checkbox':
                    $fieldRules[] = 'array';
                    break;

                case 'date':
                    $fieldRules[] = 'date';
                    break;

                case 'text':
                case 'phone':
                case 'textarea':
                    $fieldRules[] = 'string';
                    if ($field->min_length !== null) {
                        $fieldRules[] = 'min:' . $field->min_length;
                    }
                    if ($field->max_length !== null) {
                        $fieldRules[] = 'max:' . $field->max_length;
                    }
                    break;

                case 'select':
                case 'radio':
                    $fieldRules[] = 'string';
                    break;

                case 'time':
                    $fieldRules[] = 'date_format:H:i';
                    break;

                case 'hidden':
                    $fieldRules[] = 'string|max:500';
                    break;

                default:
                    $fieldRules[] = 'string|max:10000';
                    break;
            }

            $rules[$fieldName] = $fieldRules;

            $label = $field->label ?: ucfirst(str_replace(['_', '-'], ' ', $fieldName));
            $messages[$fieldName . '.required']   = $label . ' is required.';
            $messages[$fieldName . '.email']      = 'Please enter a valid email address.';
            $messages[$fieldName . '.url']        = 'Please enter a valid URL.';
            $messages[$fieldName . '.file']       = 'Please upload a valid file.';
            $messages[$fieldName . '.max']        = $label . ' exceeds the maximum limit.';
            $messages[$fieldName . '.min']        = $label . ' must meet minimum requirements.';
            $messages[$fieldName . '.mimetypes']  = $label . ' has an unsupported file format.';
            $messages[$fieldName . '.extensions'] = $label . ' has an invalid file extension.';
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please correct the highlighted validation errors.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // 6. Security Payload Size Check
        if (strlen(json_encode($request->except(['_token']), JSON_UNESCAPED_UNICODE)) > 500000) {
            return response()->json([
                'success' => false,
                'message' => 'Your submission payload exceeds the permissible limit.',
            ], 413);
        }

        try {
            DB::beginTransaction();

            $submission = null;

            if ($form->save_submissions) {
                $submissionData = [
                    'form_id'      => $form->id,
                    'ip_address'   => $userIp,
                    'user_agent'   => mb_substr((string) $request->userAgent(), 0, 1000),
                    'status'       => 'new',
                    'submitted_at' => now(),
                ];

                if (Schema::hasColumn('form_submissions', 'referer')) {
                    $submissionData['referer'] = mb_substr((string) $request->header('referer'), 0, 500);
                }

                $submission = FormSubmission::create($submissionData);

                foreach ($fields as $field) {
                    if ($field->type === 'html') {
                        continue;
                    }

                    $fieldName = trim((string) $field->name);

                    if (empty($fieldName) || !preg_match('/^[A-Za-z][A-Za-z0-9_\-]*$/', $fieldName)) {
                        continue;
                    }

                    $value = null;

                    if ($field->type === 'file') {
                        if ($request->hasFile($fieldName)) {
                            $file = $request->file($fieldName);

                            if (!$file || !$file->isValid()) {
                                throw new \RuntimeException('Uploaded file is corrupted or invalid.');
                            }

                            $extension = strtolower($file->extension() ?: $file->getClientOriginalExtension());
                            $safeFilename = bin2hex(random_bytes(24)) . '.' . $extension;

                            if (is_dir(base_path('../public_html'))) {
                                $baseUploadDir = base_path('../public_html/uploads/forms/' . $form->slug);
                            } else {
                                $baseUploadDir = public_path('uploads/forms/' . $form->slug);
                            }

                            if (!file_exists($baseUploadDir)) {
                                @mkdir($baseUploadDir, 0777, true);
                            }

                            $file->move($baseUploadDir, $safeFilename);
                            $value = 'uploads/forms/' . $form->slug . '/' . $safeFilename;
                        }
                    } elseif ($field->type === 'checkbox') {
                        $rawArray = $request->input($fieldName, []);
                        if (is_array($rawArray)) {
                            $sanitizedArray = array_map([$this, 'sanitizeInputText'], $rawArray);
                            $value = json_encode(array_values($sanitizedArray), JSON_UNESCAPED_UNICODE);
                        } else {
                            $value = $this->sanitizeInputText((string) $rawArray);
                        }
                    } else {
                        $rawVal = $request->input($fieldName);

                        if (is_array($rawVal)) {
                            $sanitizedArray = array_map([$this, 'sanitizeInputText'], $rawVal);
                            $value = json_encode(array_values($sanitizedArray), JSON_UNESCAPED_UNICODE);
                        } else {
                            $value = $this->sanitizeInputText((string) $rawVal);
                        }
                    }

                    FormSubmissionValue::create([
                        'submission_id' => $submission->id,
                        'field_id'      => $field->id,
                        'value'         => $value,
                    ]);
                }
            }

            DB::commit();

            Cache::put($rateLimitKey, $attempts + 1, now()->addHour());

            // 7. Auto-Sync Leads to Email Subscribers System
            $leadSyncResult = $this->syncLeadToEmailSubscriber($form, $fields, $request);

            // 8. Emails & Autoresponder Dispatch
            $this->dispatchEmailAlerts($form, $fields, $submission, $request);

            // 9. Outgoing Webhooks Dispatch
            $this->dispatchWebhookRelay($form, $fields, $submission, $request);

            // 10. Format JSON Response
            $responseData = [
                'success'      => true,
                'message'      => $form->success_message ?: 'Your message has been sent successfully.',
                'redirect_url' => $form->redirect_url ?: null,
            ];

            if (!empty($leadSyncResult['coupon_code'])) {
                $responseData['coupon_code'] = $leadSyncResult['coupon_code'];
            }

            return response()->json($responseData);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Please correct the errors below.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Dynamic Form Submission Failed', [
                'form_id' => $form->id,
                'error'   => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to submit the form right now. Please try again later.',
            ], 500);
        }
    }

    /**
     * Dispatch Admin Notification & User Confirmation Emails with View Fallbacks
     */
    protected function dispatchEmailAlerts(Form $form, $fields, ?FormSubmission $submission, Request $request): void
    {
        $emailSubmission = $submission ? $submission->load(['values.field']) : null;
        $submissionId = $submission ? $submission->id : Str::random(8);
        $dateStr = now()->format('Y-m-d h:i A');

        $fieldData = [];
        foreach ($fields as $field) {
            if ($field->type === 'html' || $field->type === 'hidden') continue;
            $val = $request->input($field->name);
            if (is_array($val)) {
                $val = implode(', ', $val);
            }
            $fieldData[$field->label ?: $field->name] = $val ?: 'N/A';
        }

        // A. Staff / Admin Notification Email
        if ($form->send_admin_email && !empty($form->admin_email)) {
            $adminSubject = str_replace(
                ['{form_name}', '{submission_id}', '{date}'],
                [$form->name, $submissionId, $dateStr],
                $form->email_subject ?: 'New Form Submission: {form_name}'
            );

            $adminEmails = array_map('trim', explode(',', $form->admin_email));
            $adminEmails = array_filter($adminEmails, fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL));

            if (!empty($adminEmails)) {
                try {
                    if (view()->exists('emails.dynamic-form')) {
                        Mail::send('emails.dynamic-form', [
                            'form'       => $form,
                            'submission' => $emailSubmission,
                            'fieldData'  => $fieldData,
                            'ip'         => $request->ip(),
                        ], function ($message) use ($adminEmails, $adminSubject) {
                            $message->to($adminEmails)->subject($adminSubject);
                        });
                    } else {
                        $htmlContent = $this->generateDefaultEmailHtml(
                            'New Submission Received: ' . $form->name,
                            'A new dynamic form submission has been logged with the following details:',
                            $fieldData,
                            $request->ip()
                        );

                        Mail::html($htmlContent, function ($message) use ($adminEmails, $adminSubject) {
                            $message->to($adminEmails)->subject($adminSubject);
                        });
                    }
                } catch (\Throwable $adminMailEx) {
                    Log::error('Dynamic Form Staff Email Failed', [
                        'form_id' => $form->id,
                        'error'   => $adminMailEx->getMessage(),
                    ]);
                }
            }
        }

        // B. Applicant Confirmation Autoresponder
        if ($form->send_user_email) {
            $userEmail = null;
            $emailField = $fields->firstWhere('type', 'email');

            if ($emailField && $request->filled($emailField->name)) {
                $userEmail = trim((string) $request->input($emailField->name));
            } else {
                foreach ($fields as $field) {
                    if (str_contains(strtolower($field->name), 'email')) {
                        $candidate = trim((string) $request->input($field->name));
                        if (filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                            $userEmail = $candidate;
                            break;
                        }
                    }
                }
            }

            if ($userEmail && filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
                $userSubject = str_replace(
                    ['{form_name}', '{submission_id}', '{date}'],
                    [$form->name, $submissionId, $dateStr],
                    $form->user_email_subject ?: 'Thank you for reaching out to us!'
                );

                try {
                    if (view()->exists('emails.dynamic-form-user')) {
                        Mail::send('emails.dynamic-form-user', [
                            'form'       => $form,
                            'submission' => $emailSubmission,
                            'fieldData'  => $fieldData,
                        ], function ($message) use ($userEmail, $userSubject) {
                            $message->to($userEmail)->subject($userSubject);
                        });
                    } else {
                        $userHtml = $this->generateDefaultEmailHtml(
                            'We Have Received Your Submission',
                            'Thank you for contacting us regarding ' . $form->name . '. Our support representative will review your request shortly.',
                            $fieldData,
                            null
                        );

                        Mail::html($userHtml, function ($message) use ($userEmail, $userSubject) {
                            $message->to($userEmail)->subject($userSubject);
                        });
                    }
                } catch (\Throwable $userMailEx) {
                    Log::error('Dynamic Form User Confirmation Email Failed', [
                        'form_id' => $form->id,
                        'error'   => $userMailEx->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Dispatch Outgoing Webhook Payload
     */
    protected function dispatchWebhookRelay(Form $form, $fields, ?FormSubmission $submission, Request $request): void
    {
        if (empty($form->webhook_url) || !filter_var($form->webhook_url, FILTER_VALIDATE_URL)) {
            return;
        }

        try {
            $payload = [
                'event'         => 'form.submitted',
                'form_id'       => $form->id,
                'form_name'     => $form->name,
                'form_slug'     => $form->slug,
                'submission_id' => $submission ? $submission->id : null,
                'ip_address'    => $request->ip(),
                'user_agent'    => $request->userAgent(),
                'submitted_at'  => now()->toIso8601String(),
                'data'          => $request->except(['_token', '_df_trap_honey', '_hp_security_check']),
            ];

            $httpRequest = Http::timeout(5);

            if (!empty($form->webhook_auth_header)) {
                $httpRequest->withHeaders(['Authorization' => $form->webhook_auth_header]);
            }

            $method = strtoupper($form->webhook_method ?? 'POST');
            if ($method === 'PUT') {
                $httpRequest->put($form->webhook_url, $payload);
            } else {
                $httpRequest->post($form->webhook_url, $payload);
            }
        } catch (\Throwable $webhookEx) {
            Log::warning('Dynamic Form Webhook Dispatch Failed', [
                'form_id' => $form->id,
                'url'     => $form->webhook_url,
                'error'   => $webhookEx->getMessage(),
            ]);
        }
    }

    /**
     * Capture leads into EmailSubscriber with full consent telemetry
     */
    protected function syncLeadToEmailSubscriber(Form $form, $fields, Request $request): array
    {
        $result = [
            'synced'      => false,
            'coupon_code' => null,
        ];

        try {
            if (!class_exists(EmailSubscriber::class) || !Schema::hasTable('email_subscribers')) {
                return $result;
            }

            // 1. Bulletproof Email Extraction
            $email = null;

            // Strategy A: Field with type="email"
            $emailField = $fields->firstWhere('type', 'email');
            if ($emailField && $request->filled($emailField->name)) {
                $val = strtolower(trim((string) $request->input($emailField->name)));
                if (filter_var($val, FILTER_VALIDATE_EMAIL)) {
                    $email = $val;
                }
            }

            // Strategy B: Any field whose name or label contains "email" or "mail"
            if (!$email) {
                foreach ($fields as $field) {
                    $fn = strtolower($field->name);
                    $fl = strtolower($field->label ?? '');
                    if (str_contains($fn, 'email') || str_contains($fl, 'email') || str_contains($fn, 'mail')) {
                        $val = strtolower(trim((string) $request->input($field->name)));
                        if (filter_var($val, FILTER_VALIDATE_EMAIL)) {
                            $email = $val;
                            break;
                        }
                    }
                }
            }

            // Strategy C: Check direct request keys
            if (!$email && $request->filled('email')) {
                $val = strtolower(trim((string) $request->input('email')));
                if (filter_var($val, FILTER_VALIDATE_EMAIL)) {
                    $email = $val;
                }
            }

            // Strategy D: Scan all submitted request fields for a valid email format
            if (!$email) {
                foreach ($request->except(['_token', '_method', 'form_id']) as $v) {
                    if (is_string($v) && filter_var(trim($v), FILTER_VALIDATE_EMAIL)) {
                        $email = strtolower(trim($v));
                        break;
                    }
                }
            }

            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $result;
            }

            // Fresh voluntary submission unsuppresses previously suppressed test accounts
            if (class_exists(EmailSuppressionList::class)) {
                EmailSuppressionList::unsuppress($email);
            }

            // 2. Bulletproof Name Detection
            $fullName = '';
            $firstName = '';
            $lastName  = '';

            foreach ($fields as $field) {
                $fn = strtolower(trim((string) $field->name));
                $fl = strtolower(trim((string) ($field->label ?? '')));
                if (in_array($fn, ['name', 'full_name', 'customer_name', 'your_name', 'user_name']) || str_contains($fl, 'name')) {
                    $val = trim((string) $request->input($field->name));
                    if (!empty($val) && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
                        $fullName = $val;
                        break;
                    }
                }
            }

            if (empty($fullName)) {
                $fnField = $fields->first(fn ($f) => str_contains(strtolower($f->name), 'first_name'));
                $lnField = $fields->first(fn ($f) => str_contains(strtolower($f->name), 'last_name'));
                if ($fnField) $firstName = trim((string) $request->input($fnField->name));
                if ($lnField) $lastName  = trim((string) $request->input($lnField->name));
                $fullName = trim($firstName . ' ' . $lastName);
            }

            if (empty($fullName) && $request->filled('name')) {
                $fullName = trim((string) $request->input('name'));
            }

            if (!empty($fullName)) {
                $parts = explode(' ', $fullName, 2);
                $firstName = $parts[0] ?? '';
                $lastName  = $parts[1] ?? '';
            }

            // 3. Bulletproof Phone Detection
            $phone = null;
            foreach ($fields as $field) {
                $n = strtolower($field->name);
                $l = strtolower($field->label ?? '');
                if ($field->type === 'phone' || str_contains($n, 'phone') || str_contains($n, 'mobile') || str_contains($l, 'phone') || str_contains($n, 'whatsapp')) {
                    $val = trim((string) $request->input($field->name));
                    if (!empty($val)) {
                        $phone = $val;
                        break;
                    }
                }
            }

            if (!$phone && $request->filled('phone')) {
                $phone = trim((string) $request->input('phone'));
            }

            // 4. Categorize Form Source & Tags
            $identifier = strtolower(($form->slug ?? '') . ' ' . ($form->name ?? ''));
            $formTag = Str::slug($form->name ?: 'form', '_');

            if (str_contains($identifier, 'coupon') || str_contains($identifier, 'discount') || str_contains($identifier, 'voucher') || str_contains($identifier, 'claim') || str_contains($identifier, 'offer')) {
                $source = 'coupon_claim';
                $tags = ['coupon_claim', $formTag];

                if (class_exists(Coupon::class) && Schema::hasTable('coupons')) {
                    $activeCoupon = Coupon::where('is_active', true)
                        ->where(function ($q) {
                            $q->whereNull('start_date')->orWhere('start_date', '<=', now());
                        })
                        ->where(function ($q) {
                            $q->whereNull('expire_date')->orWhere('expire_date', '>=', now());
                        })
                        ->latest('id')
                        ->first();

                    if ($activeCoupon) {
                        $result['coupon_code'] = $activeCoupon->code;
                    }
                }
            } elseif (str_contains($identifier, 'campaign') || str_contains($identifier, 'promo') || str_contains($identifier, 'lead')) {
                $source = 'campaign_lead';
                $tags = ['campaign_lead', $formTag];
            } elseif (str_contains($identifier, 'newsletter') || str_contains($identifier, 'subscribe')) {
                $source = 'newsletter';
                $tags = ['newsletter', 'subscriber'];
            } elseif (str_contains($identifier, 'contact') || str_contains($identifier, 'support') || str_contains($identifier, 'inquiry')) {
                $source = 'contact_form';
                $tags = ['contact_form', $formTag];
            } else {
                $source = 'form_' . ($form->slug ?: $form->id);
                $tags = ['form_lead', $formTag];
            }

            // 5. Store / Update Subscriber
            $subscriber = EmailSubscriber::firstOrNew(['email' => $email]);

            if (Schema::hasColumn('email_subscribers', 'first_name') && !empty($firstName)) {
                $subscriber->first_name = $firstName;
            }
            if (Schema::hasColumn('email_subscribers', 'last_name') && !empty($lastName)) {
                $subscriber->last_name = $lastName;
            }
            if (Schema::hasColumn('email_subscribers', 'name') && !empty($fullName)) {
                $subscriber->name = $fullName;
            }
            if (Schema::hasColumn('email_subscribers', 'phone') && !empty($phone)) {
                $subscriber->phone = $phone;
            }

            // Source & Consent Mapping
            if (Schema::hasColumn('email_subscribers', 'source')) {
                $subscriber->source = $source;
            }
            if (Schema::hasColumn('email_subscribers', 'consent_source')) {
                $subscriber->consent_source = $source;
            }
            if (Schema::hasColumn('email_subscribers', 'status')) {
                $subscriber->status = 'subscribed';
            }
            if (Schema::hasColumn('email_subscribers', 'marketing_consent')) {
                $subscriber->marketing_consent = true;
            }
            if (Schema::hasColumn('email_subscribers', 'consent_at')) {
                $subscriber->consent_at = now();
            }
            if (Schema::hasColumn('email_subscribers', 'consent_ip')) {
                $subscriber->consent_ip = $request->ip() ?: '127.0.0.1';
            }
            if (Schema::hasColumn('email_subscribers', 'user_id')) {
                if (auth()->check() && empty($subscriber->user_id)) {
                    $subscriber->user_id = auth()->id();
                }
            }
            if (Schema::hasColumn('email_subscribers', 'subscribed_at') && empty($subscriber->subscribed_at)) {
                $subscriber->subscribed_at = now();
            }
            if (Schema::hasColumn('email_subscribers', 'ip_address')) {
                $subscriber->ip_address = $request->ip();
            }
            if (Schema::hasColumn('email_subscribers', 'user_agent')) {
                $subscriber->user_agent = mb_substr((string) $request->userAgent(), 0, 500);
            }

            // Merge tags
            if (Schema::hasColumn('email_subscribers', 'tags')) {
                $existingTags = [];
                if (!empty($subscriber->tags)) {
                    $existingTags = is_array($subscriber->tags) ? $subscriber->tags : json_decode($subscriber->tags, true) ?? [];
                }
                $mergedTags = array_values(array_unique(array_filter(array_merge($existingTags, $tags))));
                $subscriber->tags = (method_exists($subscriber, 'hasCast') && $subscriber->hasCast('tags'))
                    ? $mergedTags
                    : json_encode($mergedTags);
            }

            // Explicitly update timestamp so subscriber moves to the top of the list
            $subscriber->updated_at = now();
            $subscriber->save();
            $result['synced'] = true;

            // Log Consent Entry
            if (class_exists(EmailConsentLog::class) && method_exists(EmailConsentLog::class, 'logConsent')) {
                EmailConsentLog::logConsent($subscriber->email, 'opt_in', $source, $subscriber->id);
            }
        } catch (\Throwable $leadEx) {
            Log::warning('Lead Auto-Sync to Email Subscriber Failed', [
                'form_id' => $form->id,
                'error'   => $leadEx->getMessage(),
            ]);
        }

        return $result;
    }

    /**
     * Anti-XSS Sanitizer for submitted user content
     */
    protected function sanitizeInputText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = str_replace(chr(0), '', (string) $value);
        $clean = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $clean);
        $clean = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $clean);
        $clean = preg_replace('/javascript:/i', '', $clean);
        $clean = preg_replace('/\b(onload|onerror|onclick|onmouseover|onfocus)\s*=/i', '', $clean);

        return strip_tags(trim($clean));
    }

    /**
     * Generate fallback inline responsive HTML email template
     */
    protected function generateDefaultEmailHtml(string $title, string $intro, array $data, ?string $ip = null): string
    {
        $rows = '';
        foreach ($data as $key => $val) {
            $safeKey = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
            $safeVal = nl2br(htmlspecialchars($val, ENT_QUOTES, 'UTF-8'));
            $rows .= "<tr>
                <td style='padding: 10px 14px; border: 1px solid #e2e8f0; font-weight: 700; color: #334155; width: 35%; background: #f8fafc;'>{$safeKey}</td>
                <td style='padding: 10px 14px; border: 1px solid #e2e8f0; color: #0f172a;'>{$safeVal}</td>
            </tr>";
        }

        $ipRow = '';
        if ($ip) {
            $ipRow = "<p style='font-size: 11px; color: #94a3b8; margin-top: 18px;'>Originating Client IP: {$ip} | Recorded at: " . now()->toDayDateTimeString() . "</p>";
        }

        return "<!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>{$title}</title>
        </head>
        <body style='font-family: Arial, sans-serif; background-color: #f1f5f9; padding: 25px; margin: 0;'>
            <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #cbd5e1; overflow: hidden;'>
                <div style='background: #0f172a; padding: 18px 24px; color: #ffffff;'>
                    <h3 style='margin: 0; font-size: 18px;'>{$title}</h3>
                </div>
                <div style='padding: 24px;'>
                    <p style='color: #475569; font-size: 14px; line-height: 1.5; margin-top: 0;'>{$intro}</p>
                    <table style='width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 13px;'>
                        <tbody>
                            {$rows}
                        </tbody>
                    </table>
                    {$ipRow}
                </div>
            </div>
        </body>
        </html>";
    }

    private function normalizeList(?string $value): array
    {
        if (!$value) {
            return [];
        }

        $items = preg_split('/[\s,]+/', $value);
        $items = array_map('trim', $items);
        $items = array_filter($items, fn ($item) => $item !== '');

        return array_values(array_unique($items));
    }

    private function normalizeExtensions(?string $value): array
    {
        if (!$value) {
            return [];
        }

        $items = preg_split('/[\s,]+/', $value);
        $items = array_map(function ($item) {
            return strtolower(ltrim(trim($item), '.'));
        }, $items);

        $items = array_filter($items, fn ($item) => preg_match('/^[a-z0-9]+$/', $item));

        return array_values(array_unique($items));
    }
}