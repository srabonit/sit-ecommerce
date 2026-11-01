<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsProvider;
use App\Models\SmsTemplate;
use App\Services\Sms\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SmsTemplateController extends Controller
{
    /**
     * Display a paginated listing of SMS templates with type filtering.
     */
    public function index(Request $request): View
    {
        $query = SmsTemplate::query();

        if ($request->filled('type')) {
            $query->where('event_type', $request->query('type'));
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('key', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%");
            });
        }

        // is_system column thakle agey system template, nahole direct latest id diye sort korbe
        if (Schema::hasColumn('sms_templates', 'is_system')) {
            $query->orderBy('is_system', 'desc');
        }

        $templates = $query->latest('id')
            ->paginate(15)
            ->withQueryString();

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
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'key'         => 'required|string|max:80|unique:sms_templates,key|regex:/^[a-z0-9_]+$/',
            'event_type'  => 'required|in:order,auth,marketing,general',
            'sender_id'   => 'nullable|string|max:30',
            'body'        => 'required|string',
            'variables'   => 'nullable|string',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);

        $metrics = $smsService->analyzeMessage($validated['body']);

        // Convert comma-separated variables string to array
        $parsedVariables = [];
        if (!empty($validated['variables'])) {
            $parsedVariables = array_values(array_filter(array_map('trim', explode(',', $validated['variables']))));
        }

        $templateData = [
            'name'             => $validated['name'],
            'key'              => strtolower(trim($validated['key'])),
            'event_type'       => $validated['event_type'],
            'sender_id'        => $validated['sender_id'] ?? null,
            'body'             => $validated['body'],
            'variables'        => $parsedVariables,
            'description'      => $validated['description'] ?? null,
            'is_active'        => $request->has('is_active'),
            'is_unicode'       => $metrics['is_unicode'],
            'estimated_parts'  => $metrics['sms_parts'],
        ];

        if (Schema::hasColumn('sms_templates', 'is_system')) {
            $templateData['is_system'] = false;
        }

        SmsTemplate::create($templateData);

        return redirect()
            ->route('admin.sms.templates.index')
            ->with('success', 'SMS template created successfully.');
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

        $rules = [
            'name'        => 'required|string|max:100',
            'event_type'  => 'required|in:order,auth,marketing,general',
            'sender_id'   => 'nullable|string|max:30',
            'body'        => 'required|string',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ];

        if (!$isSystem) {
            $rules['key'] = "required|string|max:80|regex:/^[a-z0-9_]+$/|unique:sms_templates,key,{$template->id}";
            $rules['variables'] = 'nullable|string';
        }

        $validated = $request->validate($rules);

        $metrics = $smsService->analyzeMessage($validated['body']);

        $updateData = [
            'name'            => $validated['name'],
            'event_type'      => $validated['event_type'],
            'sender_id'       => $validated['sender_id'] ?? null,
            'body'            => $validated['body'],
            'description'     => $validated['description'] ?? null,
            'is_active'       => $request->has('is_active'),
            'is_unicode'      => $metrics['is_unicode'],
            'estimated_parts' => $metrics['sms_parts'],
        ];

        if (!$isSystem) {
            $updateData['key'] = strtolower(trim($validated['key']));
            if (isset($validated['variables'])) {
                $updateData['variables'] = array_values(array_filter(array_map('trim', explode(',', $validated['variables']))));
            }
        }

        $template->update($updateData);

        return redirect()
            ->route('admin.sms.templates.index')
            ->with('success', "Template '{$template->name}' updated successfully.");
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
                ->with('error', 'System-critical templates cannot be deleted.');
        }

        $template->delete();

        return redirect()
            ->route('admin.sms.templates.index')
            ->with('success', 'Custom SMS template deleted successfully.');
    }

    /**
     * Quick status toggle (AJAX).
     */
    public function toggleStatus(Request $request, SmsTemplate $template): JsonResponse
    {
        $newStatus = !$template->is_active;

        $template->update([
            'is_active' => $newStatus,
        ]);

        return response()->json([
            'success'   => true,
            'is_active' => (bool) $newStatus,
            'message'   => 'Template status updated successfully.',
        ]);
    }

    /**
     * Live preview helper endpoint for calculating parts and character metrics.
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