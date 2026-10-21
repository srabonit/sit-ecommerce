<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FormFieldController extends Controller
{
    /**
     * Store new field
     */
    public function store(Request $request, Form $form)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:50',
            'label' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'placeholder' => 'nullable|string|max:255',
            'default_value' => 'nullable|string',
            'help_text' => 'nullable|string',
            'options' => 'nullable|string',
            'css_class' => 'nullable|string|max:255',
            'wrapper_class' => 'nullable|string|max:255',
            'column_width' => 'nullable|integer|min:1|max:12',
            'sort_order' => 'nullable|integer|min:0',
            'min_length' => 'nullable|integer|min:0',
            'max_length' => 'nullable|integer|min:0',
            'min_value' => 'nullable|numeric',
            'max_value' => 'nullable|numeric',
        ]);

        $name = Str::slug($validated['name'], '_');

        $options = null;

        if (!empty($validated['options'])) {

            $options = collect(
                preg_split('/\r\n|\r|\n/', $validated['options'])
            )
                ->map(function ($line) {

                    $line = trim($line);

                    if ($line === '') {
                        return null;
                    }

                    if (str_contains($line, '|')) {

                        [$label, $value] = array_pad(
                            explode('|', $line, 2),
                            2,
                            ''
                        );

                        return [
                            'label' => trim($label),
                            'value' => trim($value ?: $label),
                        ];
                    }

                    return [
                        'label' => $line,
                        'value' => Str::slug($line, '_'),
                    ];
                })
                ->filter()
                ->values()
                ->toArray();
        }

        $field = $form->fields()->create([
            'type' => $validated['type'],
            'label' => $validated['label'],
            'name' => $name,
            'placeholder' => $validated['placeholder'] ?? null,
            'default_value' => $validated['default_value'] ?? null,
            'help_text' => $validated['help_text'] ?? null,
            'options' => $options,
            'css_class' => $validated['css_class'] ?? null,
            'wrapper_class' => $validated['wrapper_class'] ?? null,
            'column_width' => $validated['column_width'] ?? 12,
            'sort_order' => $validated['sort_order'] ?? ($form->fields()->count()),
            'is_required' => $request->boolean('is_required'),
            'is_active' => $request->boolean('is_active', true),
            'min_length' => $validated['min_length'] ?? null,
            'max_length' => $validated['max_length'] ?? null,
            'min_value' => $validated['min_value'] ?? null,
            'max_value' => $validated['max_value'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Field added successfully.',
            'field' => $field,
        ]);
    }

    /**
     * Update field
     */
    public function update(Request $request, Form $form, FormField $field)
    {
        abort_if($field->form_id !== $form->id, 404);

        $validated = $request->validate([
            'type' => 'required|string|max:50',
            'label' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'placeholder' => 'nullable|string|max:255',
            'default_value' => 'nullable|string',
            'help_text' => 'nullable|string',
            'options' => 'nullable|string',
            'css_class' => 'nullable|string|max:255',
            'wrapper_class' => 'nullable|string|max:255',
            'column_width' => 'nullable|integer|min:1|max:12',
            'min_length' => 'nullable|integer|min:0',
            'max_length' => 'nullable|integer|min:0',
            'min_value' => 'nullable|numeric',
            'max_value' => 'nullable|numeric',
        ]);

        $options = null;

        if (!empty($validated['options'])) {

            $options = collect(
                preg_split('/\r\n|\r|\n/', $validated['options'])
            )
                ->map(function ($line) {

                    $line = trim($line);

                    if (!$line) {
                        return null;
                    }

                    if (str_contains($line, '|')) {

                        [$label, $value] = array_pad(
                            explode('|', $line, 2),
                            2,
                            ''
                        );

                        return [
                            'label' => trim($label),
                            'value' => trim($value ?: $label),
                        ];
                    }

                    return [
                        'label' => $line,
                        'value' => Str::slug($line, '_'),
                    ];
                })
                ->filter()
                ->values()
                ->toArray();
        }

        $field->update([
            'type' => $validated['type'],
            'label' => $validated['label'],
            'name' => Str::slug($validated['name'], '_'),
            'placeholder' => $validated['placeholder'] ?? null,
            'default_value' => $validated['default_value'] ?? null,
            'help_text' => $validated['help_text'] ?? null,
            'options' => $options,
            'css_class' => $validated['css_class'] ?? null,
            'wrapper_class' => $validated['wrapper_class'] ?? null,
            'column_width' => $validated['column_width'] ?? 12,
            'is_required' => $request->boolean('is_required'),
            'is_active' => $request->boolean('is_active', true),
            'min_length' => $validated['min_length'] ?? null,
            'max_length' => $validated['max_length'] ?? null,
            'min_value' => $validated['min_value'] ?? null,
            'max_value' => $validated['max_value'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Field updated successfully.',
            'field' => $field->fresh(),
        ]);
    }

    /**
     * Delete field
     */
    public function destroy(Form $form, FormField $field)
    {
        abort_if($field->form_id !== $form->id, 404);

        $field->delete();

        return response()->json([
            'success' => true,
            'message' => 'Field deleted successfully.',
        ]);
    }

    /**
     * Reorder fields
     */
    public function reorder(Request $request, Form $form)
    {
        $request->validate([
            'fields' => 'required|array',
            'fields.*' => 'integer',
        ]);

        foreach ($request->fields as $index => $fieldId) {

            FormField::where('id', $fieldId)
                ->where('form_id', $form->id)
                ->update([
                    'sort_order' => $index
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Field order updated.',
        ]);
    }
}