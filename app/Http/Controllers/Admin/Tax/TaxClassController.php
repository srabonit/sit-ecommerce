<?php

namespace App\Http\Controllers\Admin\Tax;

use App\Http\Controllers\Controller;
use App\Models\TaxClass;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TaxClassController extends Controller
{
    /**
     * Clear system tax caches whenever classes change.
     */
    protected function clearTaxCaches(): void
    {
        Cache::forget('tax_engine_active_rules');
        Cache::forget('global_settings_cache');
        Cache::forget('central_tax_settings');
    }

    /**
     * Display a listing of tax classes with related rules & product counts.
     */
    public function index(Request $request)
    {
        $taxClassInstance = new TaxClass();
        $withCounts = ['taxRules'];

        if (method_exists($taxClassInstance, 'products')) {
            $withCounts[] = 'products';
        }
        if (method_exists($taxClassInstance, 'categories')) {
            $withCounts[] = 'categories';
        }

        $query = TaxClass::query()
            ->withCount($withCounts)
            ->with('creator:id,name');

        // Search Filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $status = $request->status === 'active' ? 1 : 0;
            $query->where('is_active', $status);
        }

        $taxClasses = $query->latest('id')->paginate(15)->withQueryString();

        return view('admin.tax.classes.index', compact('taxClasses'));
    }

    /**
     * Store a newly created tax class in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'required|string|max:50|unique:tax_classes,code',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'nullable|boolean',
        ], [
            'name.required' => 'ট্যাক্স ক্লাসের নাম প্রদান করা আবশ্যক।',
            'code.required' => 'ট্যাক্স ক্লাসের কোড প্রদান করা আবশ্যক।',
            'code.unique'   => 'এই ট্যাক্স ক্লাস কোডটি ইতোমধ্যে ব্যবহৃত হয়েছে। নতুন কোড দিন।',
        ]);

        try {
            DB::beginTransaction();

            $taxClass = TaxClass::create([
                'name'        => $validated['name'],
                'code'        => strtoupper(Str::slug($validated['code'], '_')),
                'description' => $validated['description'] ?? null,
                'is_active'   => $request->boolean('is_active', true),
                'created_by'  => auth()->id(),
            ]);

            $this->clearTaxCaches();

            if (class_exists(ActivityLogger::class)) {
                ActivityLogger::log('tax_class_created', "Created Tax Class: {$taxClass->name} ({$taxClass->code})", [
                    'id'   => $taxClass->id,
                    'code' => $taxClass->code,
                ]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'ট্যাক্স ক্লাস সফলভাবে তৈরি হয়েছে।',
                    'data'    => $taxClass,
                ]);
            }

            return redirect()->route('admin.tax.classes.index')->with('success', 'ট্যাক্স ক্লাস সফলভাবে তৈরি হয়েছে।');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ত্রুটি: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'ট্যাক্স ক্লাস তৈরিতে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Get tax class details for edit modal / AJAX form.
     */
    public function edit(int $id): JsonResponse
    {
        $taxClass = TaxClass::findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $taxClass,
        ]);
    }

    /**
     * Update the specified tax class in storage.
     */
    public function update(Request $request, int $id)
    {
        $taxClass = TaxClass::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'required|string|max:50|unique:tax_classes,code,' . $taxClass->id,
            'description' => 'nullable|string|max:500',
            'is_active'   => 'nullable|boolean',
        ], [
            'name.required' => 'ট্যাক্স ক্লাসের নাম প্রদান করা আবশ্যক।',
            'code.required' => 'ট্যাক্স ক্লাসের কোড প্রদান করা আবশ্যক।',
            'code.unique'   => 'এই ট্যাক্স ক্লাস কোডটি ইতোমধ্যে অন্য ক্লাসে ব্যবহৃত হয়েছে।',
        ]);

        try {
            DB::beginTransaction();

            $taxClass->update([
                'name'        => $validated['name'],
                'code'        => strtoupper(Str::slug($validated['code'], '_')),
                'description' => $validated['description'] ?? null,
                'is_active'   => $request->boolean('is_active', false),
            ]);

            $this->clearTaxCaches();

            if (class_exists(ActivityLogger::class)) {
                ActivityLogger::log('tax_class_updated', "Updated Tax Class: {$taxClass->name} ({$taxClass->code})", [
                    'id'   => $taxClass->id,
                    'code' => $taxClass->code,
                ]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'ট্যাক্স ক্লাস সফলভাবে আপডেট করা হয়েছে।',
                    'data'    => $taxClass,
                ]);
            }

            return redirect()->route('admin.tax.classes.index')->with('success', 'ট্যাক্স ক্লাস সফলভাবে আপডেট করা হয়েছে।');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'আপডেট ব্যর্থ হয়েছে: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'আপডেট ব্যর্থ হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the active status of the tax class.
     */
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $taxClass = TaxClass::findOrFail($id);
        $taxClass->is_active = !$taxClass->is_active;
        $taxClass->save();

        $this->clearTaxCaches();

        if (class_exists(ActivityLogger::class)) {
            ActivityLogger::log('tax_class_status_toggled', "Toggled status for Tax Class: {$taxClass->name} to " . ($taxClass->is_active ? 'Active' : 'Inactive'), [
                'id'        => $taxClass->id,
                'is_active' => $taxClass->is_active,
            ]);
        }

        return response()->json([
            'success'   => true,
            'is_active' => $taxClass->is_active,
            'message'   => 'ট্যাক্স ক্লাসের স্ট্যাটাস সফলভাবে পরিবর্তন করা হয়েছে।',
        ]);
    }

    /**
     * Remove the specified tax class from storage safely.
     */
    public function destroy(int $id)
    {
        $taxClassInstance = new TaxClass();
        $withCounts = ['taxRules'];

        if (method_exists($taxClassInstance, 'products')) {
            $withCounts[] = 'products';
        }

        $taxClass = TaxClass::withCount($withCounts)->findOrFail($id);

        // Relational Integrity Guard
        if (($taxClass->tax_rules_count ?? 0) > 0) {
            $msg = "এই ট্যাক্স ক্লাসের অধীনে {$taxClass->tax_rules_count}টি সক্রিয় ট্যাক্স রুলস সংযুক্ত রয়েছে। প্রথমে রুলগুলো পরিবর্তন বা মুছে ফেলুন।";
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        if (isset($taxClass->products_count) && $taxClass->products_count > 0) {
            $msg = "এই ট্যাক্স ক্লাসের অধীনে {$taxClass->products_count}টি পণ্য সংযুক্ত রয়েছে। পণ্যগুলোর ট্যাক্স ক্লাস পরিবর্তন না করে এটি মোছা যাবে না।";
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        try {
            DB::beginTransaction();

            $name = $taxClass->name;
            $code = $taxClass->code;
            $taxClass->delete();

            $this->clearTaxCaches();

            if (class_exists(ActivityLogger::class)) {
                ActivityLogger::log('tax_class_deleted', "Deleted Tax Class: {$name} ({$code})", [
                    'id'   => $id,
                    'code' => $code,
                ]);
            }

            DB::commit();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'ট্যাক্স ক্লাসটি সফলভাবে মুছে ফেলা হয়েছে।',
                ]);
            }

            return redirect()->route('admin.tax.classes.index')->with('success', 'ট্যাক্স ক্লাসটি সফলভাবে মুছে ফেলা হয়েছে।');
        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ডিলিটেশন ব্যর্থ হয়েছে: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'ডিলিটেশন ব্যর্থ হয়েছে: ' . $e->getMessage());
        }
    }
}