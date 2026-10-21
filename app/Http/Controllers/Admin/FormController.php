<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FormController extends Controller
{
    public function index()
    {
        $forms = Form::withCount('fields')
            ->withCount('submissions')
            ->latest()
            ->paginate(20);

        return view('admin.forms.index', compact('forms'));
    }

    public function create()
    {
        return view('admin.forms.create');
    }

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

            'admin_email' => [
                'nullable',
                'email',
                'max:255',
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
                'url',
                'max:500',
            ],
        ]);

        $validated['slug'] = $validated['slug']
            ?: Str::slug($validated['name']);

        $validated['is_active'] = $request->boolean('is_active', true);

        $validated['save_submissions'] =
            $request->boolean('save_submissions', true);

        $validated['send_admin_email'] =
            $request->boolean('send_admin_email', true);

        $validated['send_user_email'] =
            $request->boolean('send_user_email', false);

        $form = Form::create($validated);

        return redirect()
            ->route('admin.forms.edit', $form)
            ->with('success', 'Form created successfully.');
    }

    public function edit(Form $form)
    {
        $form->load('fields');

        return view('admin.forms.edit', compact('form'));
    }

    public function update(Request $request, Form $form)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'max:255',
                'unique:forms,slug,' . $form->id,
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'admin_email' => [
                'nullable',
                'email',
                'max:255',
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
                'url',
                'max:500',
            ],
        ]);

        $validated['is_active'] =
            $request->boolean('is_active');

        $validated['save_submissions'] =
            $request->boolean('save_submissions');

        $validated['send_admin_email'] =
            $request->boolean('send_admin_email');

        $validated['send_user_email'] =
            $request->boolean('send_user_email');

        $form->update($validated);

        return back()
            ->with('success', 'Form updated successfully.');
    }

    public function destroy(Form $form)
    {
        $form->delete();

        return redirect()
            ->route('admin.forms.index')
            ->with('success', 'Form deleted successfully.');
    }
}