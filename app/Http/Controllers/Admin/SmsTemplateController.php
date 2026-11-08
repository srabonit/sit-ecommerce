<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsProvider;
use App\Models\SmsTemplate;
use App\Services\Sms\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class SmsTemplateController extends Controller
{
    /**
     * Ensure database table sms_templates has the required 'key' column and synchronize legacy keys
     */
    protected function ensureTemplateSchemaCompatibility(): void
    {
        try {
            if (!Schema::hasTable('sms_templates')) {
                return;
            }

            if (!Schema::hasColumn('sms_templates', 'key')) {
                $existingCol = null;
                $columns = Schema::getColumnListing('sms_templates');

                foreach (['template_key', 'slug', 'code', 'identifier'] as $candidate) {
                    if (in_array($candidate, $columns, true)) {
                        $existingCol = $candidate;
                        break;
                    }
                }

                Schema::table('sms_templates', function ($table) {
                    $table->string('key', 100)->nullable()->after('name');
                });

                if ($existingCol) {
                    DB::statement("UPDATE `sms_templates` SET `key` = `{$existingCol}` WHERE `key` IS NULL OR `key` = ''");
                }
            }
        } catch (Throwable $e) {
            Log::warning('ensureTemplateSchemaCompatibility warning: ' . $e->getMessage());
        }
    }

    /**
     * Safely resolve the existing column name used for unique key checks
     */
    protected function resolveTemplateKeyColumn(): string
    {
        $this->ensureTemplateSchemaCompatibility();

        if (Schema::hasColumn('sms_templates', 'key')) {
            return 'key';
        }

        $columns = Schema::getColumnListing('sms_templates');
        foreach (['template_key', 'slug', 'code', 'identifier'] as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return $candidate;
            }
        }

        return 'name';
    }

    /**
     * Display a paginated listing of SMS templates with AJAX pagination (20 per page).
     */
    public function index(Request $request)
    {
        $this->ensureTemplateSchemaCompatibility();
        $keyCol = $this->resolveTemplateKeyColumn();

        $query = SmsTemplate::query();

        if ($request->filled('type')) {
            $eventTypeCol = Schema::hasColumn('sms_templates', 'event_type') ? 'event_type' : (Schema::hasColumn('sms_templates', 'type') ? 'type' : 'event_type');
            $query->where($eventTypeCol, $request->query('type'));
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search, $keyCol) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%");
                if (Schema::hasColumn('sms_templates', $keyCol)) {
                    $q->orWhere($keyCol, 'like', "%{$search}%");
                }
            });
        }

        if (Schema::hasColumn('sms_templates', 'is_system')) {
            $query->orderBy('is_system', 'desc');
        }

        // Exactly 20 templates per page
        $templates = $query->latest('id')->paginate(20)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'success'    => true,
                'html'       => view('admin.sms.templates.partials.table_rows', compact('templates'))->render(),
                'pagination' => $templates->hasPages() ? $templates->links('pagination::bootstrap-5')->render() : '',
                'total'      => $templates->total(),
                'from'       => $templates->firstItem() ?? 0,
                'to'         => $templates->lastItem() ?? 0,
            ]);
        }

        return view('admin.sms.templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new custom SMS template.
     */
    public function create(): View
    {
        $providers = SmsProvider::where('is_active', true)->get();
        $senderIds = $providers->pluck('sender_id')->filter()->unique();

        return view('admin.sms.templates.create', compact('senderIds'));
    }

    /**
     * Store a newly created SMS template in database.
     */
    public function store(Request $request, SmsService $smsService): RedirectResponse
    {
        $keyCol = $this->resolveTemplateKeyColumn();

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'key'         => [
                'required',
                'string',
                'max:80',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('sms_templates', $keyCol),
            ],
            'event_type'  => 'required|in:order,auth,marketing,general',
            'sender_id'   => 'nullable|string|max:30',
            'body'        => 'required|string',
            'variables'   => 'nullable|string',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);

        $metrics = $smsService->analyzeMessage($validated['body']);

        $parsedVariables = [];
        if (!empty($validated['variables'])) {
            $parsedVariables = array_values(array_filter(array_map('trim', explode(',', $validated['variables']))));
        }

        $keyValue = strtolower(trim($validated['key']));

        $templateData = [
            'name' => $validated['name'],
            'body' => $validated['body'],
        ];

        // Multi-schema key population
        if (Schema::hasColumn('sms_templates', 'key')) {
            $templateData['key'] = $keyValue;
        }
        if (Schema::hasColumn('sms_templates', 'template_key')) {
            $templateData['template_key'] = $keyValue;
        }
        if (Schema::hasColumn('sms_templates', 'slug')) {
            $templateData['slug'] = $keyValue;
        }
        if (Schema::hasColumn('sms_templates', 'code')) {
            $templateData['code'] = $keyValue;
        }

        // Category/Event Type
        if (Schema::hasColumn('sms_templates', 'event_type')) {
            $templateData['event_type'] = $validated['event_type'];
        } elseif (Schema::hasColumn('sms_templates', 'type')) {
            $templateData['type'] = $validated['event_type'];
        }

        if (Schema::hasColumn('sms_templates', 'sender_id')) {
            $templateData['sender_id'] = $validated['sender_id'] ?? null;
        }

        if (Schema::hasColumn('sms_templates', 'variables')) {
            $templateData['variables'] = $parsedVariables;
        }

        if (Schema::hasColumn('sms_templates', 'description')) {
            $templateData['description'] = $validated['description'] ?? null;
        }

        if (Schema::hasColumn('sms_templates', 'is_active')) {
            $templateData['is_active'] = $request->has('is_active');
        } elseif (Schema::hasColumn('sms_templates', 'status')) {
            $templateData['status'] = $request->has('is_active') ? 1 : 0;
        }

        if (Schema::hasColumn('sms_templates', 'is_unicode')) {
            $templateData['is_unicode'] = $metrics['is_unicode'];
        }

        if (Schema::hasColumn('sms_templates', 'estimated_parts')) {
            $templateData['estimated_parts'] = $metrics['sms_parts'];
        }

        if (Schema::hasColumn('sms_templates', 'is_system')) {
            $templateData['is_system'] = false;
        }

        SmsTemplate::create($templateData);

        return redirect()
            ->route('admin.sms.templates.index')
            ->with('modal_success', 'SMS template created successfully.');
    }

    /**
     * Show the form for editing an existing SMS template.
     */
    public function edit(SmsTemplate $template): View
    {
        $providers = SmsProvider::where('is_active', true)->get();
        $senderIds = $providers->pluck('sender_id')->filter()->unique();

        return view('admin.sms.templates.edit', compact('template', 'senderIds'));
    }

    /**
     * Update the specified SMS template in database.
     */
    public function update(Request $request, SmsTemplate $template, SmsService $smsService): RedirectResponse
    {
        $isSystem = Schema::hasColumn('sms_templates', 'is_system') ? (bool) $template->is_system : false;

        $keyCol = $this->resolveTemplateKeyColumn();

        $rules = [
            'name'        => 'required|string|max:100',
            'event_type'  => 'required|in:order,auth,marketing,general',
            'sender_id'   => 'nullable|string|max:30',
            'body'        => 'required|string',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ];

        if (!$isSystem) {
            $rules['key'] = [
                'required',
                'string',
                'max:80',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('sms_templates', $keyCol)->ignore($template->id),
            ];
            $rules['variables'] = 'nullable|string';
        }

        $validated = $request->validate($rules);

        $metrics = $smsService->analyzeMessage($validated['body']);

        $updateData = [
            'name' => $validated['name'],
            'body' => $validated['body'],
        ];

        if (Schema::hasColumn('sms_templates', 'event_type')) {
            $updateData['event_type'] = $validated['event_type'];
        } elseif (Schema::hasColumn('sms_templates', 'type')) {
            $updateData['type'] = $validated['event_type'];
        }

        if (Schema::hasColumn('sms_templates', 'sender_id')) {
            $updateData['sender_id'] = $validated['sender_id'] ?? null;
        }

        if (Schema::hasColumn('sms_templates', 'description')) {
            $updateData['description'] = $validated['description'] ?? null;
        }

        if (Schema::hasColumn('sms_templates', 'is_active')) {
            $updateData['is_active'] = $request->has('is_active');
        } elseif (Schema::hasColumn('sms_templates', 'status')) {
            $updateData['status'] = $request->has('is_active') ? 1 : 0;
        }

        if (Schema::hasColumn('sms_templates', 'is_unicode')) {
            $updateData['is_unicode'] = $metrics['is_unicode'];
        }

        if (Schema::hasColumn('sms_templates', 'estimated_parts')) {
            $updateData['estimated_parts'] = $metrics['sms_parts'];
        }

        if (!$isSystem && isset($validated['key'])) {
            $keyValue = strtolower(trim($validated['key']));
            if (Schema::hasColumn('sms_templates', 'key')) {
                $updateData['key'] = $keyValue;
            }
            if (Schema::hasColumn('sms_templates', 'template_key')) {
                $updateData['template_key'] = $keyValue;
            }
            if (Schema::hasColumn('sms_templates', 'slug')) {
                $updateData['slug'] = $keyValue;
            }
            if (Schema::hasColumn('sms_templates', 'code')) {
                $updateData['code'] = $keyValue;
            }
        }

        if (!$isSystem && isset($validated['variables']) && Schema::hasColumn('sms_templates', 'variables')) {
            $updateData['variables'] = array_values(array_filter(array_map('trim', explode(',', $validated['variables']))));
        }

        $template->update($updateData);

        return redirect()
            ->route('admin.sms.templates.index')
            ->with('modal_success', "Template '{$template->name}' updated successfully.");
    }

    /**
     * Remove the specified SMS template from database.
     */
    public function destroy(SmsTemplate $template): RedirectResponse
    {
        $isSystem = Schema::hasColumn('sms_templates', 'is_system') ? (bool) $template->is_system : false;

        if ($isSystem) {
            return redirect()
                ->route('admin.sms.templates.index')
                ->with('modal_error', 'System-critical templates cannot be deleted.');
        }

        $template->delete();

        return redirect()
            ->route('admin.sms.templates.index')
            ->with('modal_success', 'Custom SMS template deleted successfully.');
    }

    /**
     * Quick status toggle (AJAX).
     */
    public function toggleStatus(Request $request, SmsTemplate $template): JsonResponse
    {
        $currentStatus = $template->is_active ?? ($template->status ?? false);
        $newStatus = !$currentStatus;

        $update = [];
        if (Schema::hasColumn('sms_templates', 'is_active')) {
            $update['is_active'] = $newStatus;
        }
        if (Schema::hasColumn('sms_templates', 'status')) {
            $update['status'] = $newStatus ? 1 : 0;
        }

        $template->update($update);

        return response()->json([
            'success'   => true,
            'is_active' => (bool) $newStatus,
            'message'   => "Template '{$template->name}' is now " . ($newStatus ? 'Active' : 'Inactive') . '.',
        ]);
    }

    /**
     * Live preview helper endpoint.
     */
    public function previewMetrics(Request $request, SmsService $smsService): JsonResponse
    {
        $text = (string) $request->input('text', '');
        $metrics = $smsService->analyzeMessage($text);

        return response()->json([
            'success'          => true,
            'is_unicode'       => $metrics['is_unicode'],
            'characters_count' => $metrics['characters_count'],
            'sms_parts'        => $metrics['sms_parts'],
        ]);
    }
}