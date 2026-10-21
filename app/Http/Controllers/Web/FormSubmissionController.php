<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\FormSubmissionValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FormSubmissionController extends Controller
{
    /**
     * Submit Dynamic Form
     */
    public function submit(Request $request, Form $form)
    {
        if (!$form->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This form is currently unavailable.',
            ], 403);
        }

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
                    $fieldRules[] = 'email:rfc';
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

            $messages[$fieldName . '.required'] = $label . ' is required.';
            $messages[$fieldName . '.email'] = 'Please enter a valid email address.';
            $messages[$fieldName . '.email:rfc'] = 'Please enter a valid email address.';
            $messages[$fieldName . '.url'] = 'Please enter a valid URL.';
            $messages[$fieldName . '.file'] = 'Please upload a valid file.';
            $messages[$fieldName . '.max'] = $label . ' exceeds the allowed size.';
            $messages[$fieldName . '.mimetypes'] = $label . ' has an unsupported file type.';
            $messages[$fieldName . '.extensions'] = $label . ' has an unsupported file extension.';
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please correct the errors below.',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (strlen(json_encode($request->except(['_token']), JSON_UNESCAPED_UNICODE)) > 500000) {
            return response()->json([
                'success' => false,
                'message' => 'Your submission is too large.',
            ], 413);
        }

        try {
            DB::beginTransaction();

            $submission = FormSubmission::create([
                'form_id'      => $form->id,
                'ip_address'   => $request->ip(),
                'user_agent'   => mb_substr((string) $request->userAgent(), 0, 1000),
                'status'       => 'new',
                'submitted_at' => now(),
            ]);

            foreach ($fields as $field) {
                if ($field->type === 'html') {
                    continue;
                }

                $fieldName = trim((string) $field->name);

                if (empty($fieldName) || !preg_match('/^[A-Za-z][A-Za-z0-9_\-]*$/', $fieldName)) {
                    continue;
                }

                if ($field->type === 'file') {
                    $value = null;

                    if ($request->hasFile($fieldName)) {
                        $file = $request->file($fieldName);

                        if (!$file || !$file->isValid()) {
                            throw new \RuntimeException('Uploaded file is invalid.');
                        }

                        $extension = strtolower($file->extension());
                        $safeFilename = bin2hex(random_bytes(24)) . '.' . $extension;

                        // Save file securely to forms directory
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
                    $value = $request->input($fieldName);

                    if (is_array($value)) {
                        $value = json_encode(
                            array_values(
                                array_map(
                                    function ($item) {
                                        return is_scalar($item) ? (string) $item : '';
                                    },
                                    $value
                                )
                            ),
                            JSON_UNESCAPED_UNICODE
                        );
                    }
                } else {
                    $value = $request->input($fieldName);

                    if (is_array($value)) {
                        $value = json_encode(
                            array_values(
                                array_map(
                                    function ($item) {
                                        return is_scalar($item) ? (string) $item : '';
                                    },
                                    $value
                                )
                            ),
                            JSON_UNESCAPED_UNICODE
                        );
                    }

                    if (is_string($value) && mb_strlen($value) > 50000) {
                        throw new \RuntimeException('A submitted field contains too much data.');
                    }
                }

                FormSubmissionValue::create([
                    'submission_id' => $submission->id,
                    'field_id'      => $field->id,
                    'value'         => $value,
                ]);
            }

            DB::commit();

            $emailSubmission = $submission->load(['values.field']);

            if ($form->send_admin_email && !empty($form->admin_email)) {
                try {
                    Mail::send(
                        'emails.dynamic-form',
                        [
                            'form'       => $form,
                            'submission' => $emailSubmission,
                        ],
                        function ($message) use ($form) {
                            $message->to($form->admin_email)
                                    ->subject($form->email_subject ?: 'New Form Submission - ' . $form->name);
                        }
                    );
                } catch (\Throwable $mailException) {
                    Log::error('Dynamic Form Admin Email Failed', [
                        'form_id' => $form->id,
                        'error'   => $mailException->getMessage(),
                    ]);
                }
            }

            if ($form->send_user_email) {
                $emailField = $fields->firstWhere('type', 'email');

                if ($emailField) {
                    $userEmail = $request->input($emailField->name);

                    if ($userEmail && filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
                        try {
                            Mail::send(
                                'emails.dynamic-form-user',
                                [
                                    'form'       => $form,
                                    'submission' => $emailSubmission,
                                ],
                                function ($message) use ($userEmail, $form) {
                                    $message->to($userEmail)
                                            ->subject('Thank you for contacting us - ' . $form->name);
                                }
                            );
                        } catch (\Throwable $mailException) {
                            Log::error('Dynamic Form User Email Failed', [
                                'form_id' => $form->id,
                                'error'   => $mailException->getMessage(),
                            ]);
                        }
                    }
                }
            }

            return response()->json([
                'success'      => true,
                'message'      => $form->success_message ?: 'Your message has been sent successfully.',
                'redirect_url' => $form->redirect_url ?: null,
            ]);
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
                'message' => 'Something went wrong. Please try again later.',
            ], 500);
        }
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