<?php

namespace App\Http\Controllers\Admin\Tax;

use App\Http\Controllers\Controller;
use App\Models\TaxRule;
use App\Models\TaxClass;
use App\Models\TaxRuleCondition;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Branch;
use App\Models\ShippingZone;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TaxRuleController extends Controller
{
    /**
     * Clear system tax caches whenever rules change.
     */
    protected function clearTaxCaches(): void
    {
        Cache::forget('tax_engine_active_rules');
        Cache::forget('global_settings_cache');
        Cache::forget('central_tax_settings');
    }

    /**
     * Display a listing of tax rules ordered by priority.
     */
    public function index(Request $request)
    {
        $query = TaxRule::query()
            ->with(['taxClass', 'creator:id,name'])
            ->withCount('conditions');

        // Search Filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('admin_note', 'like', "%{$search}%");
            });
        }

        // Tax Class Filter
        if ($request->filled('tax_class_id')) {
            $query->where('tax_class_id', $request->tax_class_id);
        }

        // Calculation Type Filter (Inclusive vs Exclusive)
        if ($request->filled('calculation_type')) {
            $query->where('calculation_type', $request->calculation_type);
        }

        // Status Type Filter (Taxable, Exempt, Zero-rated)
        if ($request->filled('status_type')) {
            $query->where('status_type', $request->status_type);
        }

        // Active State Filter
        if ($request->filled('status')) {
            $status = $request->status === 'active' ? 1 : 0;
            $query->where('is_active', $status);
        }

        $taxRules = $query->orderByDesc('priority')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $taxClasses = class_exists(TaxClass::class) 
            ? TaxClass::where('is_active', true)->orderBy('name')->get() 
            : collect();

        return view('admin.tax.rules.index', compact('taxRules', 'taxClasses'));
    }

    /**
     * Show the form for creating a new tax rule.
     */
    public function create()
    {
        $taxClasses = class_exists(TaxClass::class) ? TaxClass::where('is_active', true)->orderBy('name')->get() : collect();
        $branches = class_exists(Branch::class) ? Branch::where('is_active', true)->orderBy('name')->get() : collect();
        $shippingZones = class_exists(ShippingZone::class) ? ShippingZone::orderBy('name')->get() : collect();

        return view('admin.tax.rules.create', compact('taxClasses', 'branches', 'shippingZones'));
    }

    /**
     * Store a newly created tax rule in storage with conditions.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:150',
            'code'               => 'required|string|max:50|unique:tax_rules,code',
            'tax_class_id'       => 'nullable|exists:tax_classes,id',
            'tax_type'           => 'required|in:percentage,fixed',
            'rate'               => 'nullable|numeric|min:0|max:100',
            'fixed_amount'       => 'nullable|numeric|min:0',
            'calculation_type'   => 'required|in:exclusive,inclusive',
            'status_type'        => 'required|in:taxable,exempt,zero_rated',
            'priority'           => 'required|integer|min:1',
            'min_taxable_amount' => 'nullable|numeric|min:0',
            'max_taxable_amount' => 'nullable|numeric|min:0',
            'valid_from'         => 'nullable|date',
            'valid_to'           => 'nullable|date|after_or_equal:valid_from',
            'is_active'          => 'nullable|boolean',
            'admin_note'         => 'nullable|string|max:1000',
            'conditions'         => 'nullable|array',
            'conditions.*.type'  => 'required_with:conditions|string',
            'conditions.*.id'    => 'nullable',
            'conditions.*.value' => 'nullable|string|max:100',
            'conditions.*.is_ex' => 'nullable|boolean',
        ], [
            'name.required' => 'ট্যাক্স রুলের নাম দেওয়া আবশ্যক।',
            'code.required' => 'ট্যাক্স রুলের কোড দেওয়া আবশ্যক।',
            'code.unique'   => 'এই কোডটি ইতোমধ্যে ব্যবহৃত হয়েছে।',
        ]);

        try {
            DB::beginTransaction();

            $rate = ($validated['tax_type'] === 'percentage') ? (float)($validated['rate'] ?? 0.0) : 0.0;
            $fixedAmount = ($validated['tax_type'] === 'fixed') ? (float)($validated['fixed_amount'] ?? 0.0) : 0.0;

            // Zero-rated or exempt rules must strictly enforce 0 rate and fixed amount
            if ($validated['status_type'] === 'exempt' || $validated['status_type'] === 'zero_rated') {
                $rate = 0.0;
                $fixedAmount = 0.0;
            }

            $taxRule = TaxRule::create([
                'tax_class_id'       => $validated['tax_class_id'] ?? null,
                'name'               => $validated['name'],
                'code'               => strtoupper(Str::slug($validated['code'], '_')),
                'tax_type'           => $validated['tax_type'],
                'rate'               => $rate,
                'fixed_amount'       => $fixedAmount,
                'calculation_type'   => $validated['calculation_type'],
                'status_type'        => $validated['status_type'],
                'priority'           => (int)$validated['priority'],
                'min_taxable_amount' => (float)($validated['min_taxable_amount'] ?? 0.0),
                'max_taxable_amount' => !empty($validated['max_taxable_amount']) ? (float)$validated['max_taxable_amount'] : null,
                'valid_from'         => $validated['valid_from'] ?? null,
                'valid_to'           => $validated['valid_to'] ?? null,
                'is_active'          => $request->boolean('is_active', true),
                'admin_note'         => $validated['admin_note'] ?? null,
                'created_by'         => auth()->id(),
            ]);

            // Consolidate conditions (Supports both dynamic array and single dropdown fields)
            $conditions = $validated['conditions'] ?? [];

            if ($request->filled('branch_id')) {
                $hasBranch = collect($conditions)->contains(fn($c) => ($c['type'] ?? '') === 'branch');
                if (!$hasBranch) {
                    $conditions[] = [
                        'type'  => 'branch',
                        'id'    => (int)$request->branch_id,
                        'value' => null,
                        'is_ex' => false
                    ];
                }
            }

            if ($request->filled('category_id')) {
                $hasCat = collect($conditions)->contains(fn($c) => ($c['type'] ?? '') === 'category');
                if (!$hasCat) {
                    $conditions[] = [
                        'type'  => 'category',
                        'id'    => (int)$request->category_id,
                        'value' => null,
                        'is_ex' => false
                    ];
                }
            }

            if ($request->filled('product_id')) {
                $hasProd = collect($conditions)->contains(fn($c) => ($c['type'] ?? '') === 'product');
                if (!$hasProd) {
                    $conditions[] = [
                        'type'  => 'product',
                        'id'    => (int)$request->product_id,
                        'value' => null,
                        'is_ex' => false
                    ];
                }
            }

            // Save Target Conditions
            if (!empty($conditions)) {
                foreach ($conditions as $cond) {
                    if (empty($cond['type'])) continue;

                    TaxRuleCondition::create([
                        'tax_rule_id'     => $taxRule->id,
                        'condition_type'  => $cond['type'],
                        'condition_id'    => !empty($cond['id']) ? (int)$cond['id'] : null,
                        'condition_value' => !empty($cond['value']) ? trim($cond['value']) : null,
                        'is_excluded'     => !empty($cond['is_ex']),
                    ]);
                }
            } else {
                // Default Global Rule
                TaxRuleCondition::create([
                    'tax_rule_id'     => $taxRule->id,
                    'condition_type'  => 'global',
                    'condition_id'    => null,
                    'condition_value' => null,
                    'is_excluded'     => false,
                ]);
            }

            $this->clearTaxCaches();

            if (class_exists(ActivityLogger::class)) {
                ActivityLogger::log('tax_rule_created', "Created Tax Rule: {$taxRule->name} ({$taxRule->code}) Priority: {$taxRule->priority}", [
                    'id'   => $taxRule->id,
                    'rate' => $taxRule->rate,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.tax.rules.index')->with('success', 'ট্যাক্স রুল সফলভাবে তৈরি হয়েছে।');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'ট্যাক্স রুল তৈরিতে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified tax rule.
     */
    public function edit(int $id)
    {
        $taxRule = TaxRule::with(['conditions', 'taxClass'])->findOrFail($id);
        $taxClasses = class_exists(TaxClass::class) ? TaxClass::where('is_active', true)->orderBy('name')->get() : collect();
        $branches = class_exists(Branch::class) ? Branch::where('is_active', true)->orderBy('name')->get() : collect();
        $shippingZones = class_exists(ShippingZone::class) ? ShippingZone::orderBy('name')->get() : collect();

        return view('admin.tax.rules.edit', compact('taxRule', 'taxClasses', 'branches', 'shippingZones'));
    }

    /**
     * Update the specified tax rule in storage.
     */
    public function update(Request $request, int $id)
    {
        $taxRule = TaxRule::findOrFail($id);

        $validated = $request->validate([
            'name'               => 'required|string|max:150',
            'code'               => 'required|string|max:50|unique:tax_rules,code,' . $taxRule->id,
            'tax_class_id'       => 'nullable|exists:tax_classes,id',
            'tax_type'           => 'required|in:percentage,fixed',
            'rate'               => 'nullable|numeric|min:0|max:100',
            'fixed_amount'       => 'nullable|numeric|min:0',
            'calculation_type'   => 'required|in:exclusive,inclusive',
            'status_type'        => 'required|in:taxable,exempt,zero_rated',
            'priority'           => 'required|integer|min:1',
            'min_taxable_amount' => 'nullable|numeric|min:0',
            'max_taxable_amount' => 'nullable|numeric|min:0',
            'valid_from'         => 'nullable|date',
            'valid_to'           => 'nullable|date|after_or_equal:valid_from',
            'is_active'          => 'nullable|boolean',
            'admin_note'         => 'nullable|string|max:1000',
            'conditions'         => 'nullable|array',
            'conditions.*.type'  => 'required_with:conditions|string',
            'conditions.*.id'    => 'nullable',
            'conditions.*.value' => 'nullable|string|max:100',
            'conditions.*.is_ex' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $rate = ($validated['tax_type'] === 'percentage') ? (float)($validated['rate'] ?? 0.0) : 0.0;
            $fixedAmount = ($validated['tax_type'] === 'fixed') ? (float)($validated['fixed_amount'] ?? 0.0) : 0.0;

            if ($validated['status_type'] === 'exempt' || $validated['status_type'] === 'zero_rated') {
                $rate = 0.0;
                $fixedAmount = 0.0;
            }

            $taxRule->update([
                'tax_class_id'       => $validated['tax_class_id'] ?? null,
                'name'               => $validated['name'],
                'code'               => strtoupper(Str::slug($validated['code'], '_')),
                'tax_type'           => $validated['tax_type'],
                'rate'               => $rate,
                'fixed_amount'       => $fixedAmount,
                'calculation_type'   => $validated['calculation_type'],
                'status_type'        => $validated['status_type'],
                'priority'           => (int)$validated['priority'],
                'min_taxable_amount' => (float)($validated['min_taxable_amount'] ?? 0.0),
                'max_taxable_amount' => !empty($validated['max_taxable_amount']) ? (float)$validated['max_taxable_amount'] : null,
                'valid_from'         => $validated['valid_from'] ?? null,
                'valid_to'           => $validated['valid_to'] ?? null,
                'is_active'          => $request->boolean('is_active', false),
                'admin_note'         => $validated['admin_note'] ?? null,
                'updated_by'         => auth()->id(),
            ]);

            // Sync Conditions safely
            $taxRule->conditions()->delete();

            $conditions = $validated['conditions'] ?? [];

            if ($request->filled('branch_id')) {
                $hasBranch = collect($conditions)->contains(fn($c) => ($c['type'] ?? '') === 'branch');
                if (!$hasBranch) {
                    $conditions[] = [
                        'type'  => 'branch',
                        'id'    => (int)$request->branch_id,
                        'value' => null,
                        'is_ex' => false
                    ];
                }
            }

            if (!empty($conditions)) {
                foreach ($conditions as $cond) {
                    if (empty($cond['type'])) continue;

                    TaxRuleCondition::create([
                        'tax_rule_id'     => $taxRule->id,
                        'condition_type'  => $cond['type'],
                        'condition_id'    => !empty($cond['id']) ? (int)$cond['id'] : null,
                        'condition_value' => !empty($cond['value']) ? trim($cond['value']) : null,
                        'is_excluded'     => !empty($cond['is_ex']),
                    ]);
                }
            } else {
                TaxRuleCondition::create([
                    'tax_rule_id'     => $taxRule->id,
                    'condition_type'  => 'global',
                    'condition_id'    => null,
                    'condition_value' => null,
                    'is_excluded'     => false,
                ]);
            }

            $this->clearTaxCaches();

            if (class_exists(ActivityLogger::class)) {
                ActivityLogger::log('tax_rule_updated', "Updated Tax Rule: {$taxRule->name} ({$taxRule->code})", [
                    'id'   => $taxRule->id,
                    'rate' => $taxRule->rate,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.tax.rules.index')->with('success', 'ট্যাক্স রুল সফলভাবে আপডেট করা হয়েছে।');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'আপডেট ব্যর্থ হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Toggle active state of a tax rule.
     */
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $taxRule = TaxRule::findOrFail($id);
        $taxRule->is_active = !$taxRule->is_active;
        $taxRule->save();

        $this->clearTaxCaches();

        if (class_exists(ActivityLogger::class)) {
            ActivityLogger::log('tax_rule_status_toggled', "Toggled status for Tax Rule: {$taxRule->name} to " . ($taxRule->is_active ? 'Active' : 'Inactive'));
        }

        return response()->json([
            'success'   => true,
            'is_active' => $taxRule->is_active,
            'message'   => 'ট্যাক্স রুলের স্ট্যাটাস সফলভাবে পরিবর্তন করা হয়েছে।',
        ]);
    }

    /**
     * Delete the specified tax rule safely.
     */
    public function destroy(int $id)
    {
        $taxRule = TaxRule::findOrFail($id);

        try {
            DB::beginTransaction();

            $name = $taxRule->name;
            $code = $taxRule->code;

            $taxRule->conditions()->delete();
            $taxRule->delete();

            $this->clearTaxCaches();

            if (class_exists(ActivityLogger::class)) {
                ActivityLogger::log('tax_rule_deleted', "Deleted Tax Rule: {$name} ({$code})");
            }

            DB::commit();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'ট্যাক্স রুলটি সফলভাবে মুছে ফেলা হয়েছে।',
                ]);
            }

            return redirect()->route('admin.tax.rules.index')->with('success', 'ট্যাক্স রুলটি সফলভাবে মুছে ফেলা হয়েছে।');
        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return back()->with('error', 'ডিলিটেশন ব্যর্থ হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Dynamic AJAX Search for Rule Condition Picker (Products, Categories, Brands, Branches, Zones).
     */
    public function searchEntitiesAjax(Request $request): JsonResponse
    {
        $type = $request->get('type', 'product');
        $query = trim($request->get('q', ''));

        $results = [];

        if ($type === 'product' && class_exists(Product::class)) {
            $pQuery = Product::query()->select('id', 'title', 'sku', 'regular_price');
            if ($query) {
                $pQuery->where(function ($q) use ($query) {
                    $q->where('id', $query)
                      ->orWhere('title', 'like', "%{$query}%")
                      ->orWhere('sku', 'like', "%{$query}%");
                });
            }
            $results = $pQuery->take(20)->get()->map(fn($p) => [
                'id'    => $p->id,
                'text'  => "#{$p->id} - {$p->title}" . (!empty($p->sku) ? " (SKU: {$p->sku})" : ''),
                'extra' => '৳' . number_format($p->regular_price ?? 0, 2),
            ]);
        } elseif ($type === 'category' && class_exists(Category::class)) {
            $cQuery = Category::query()->select('id', 'name', 'slug');
            if ($query) {
                $cQuery->where('name', 'like', "%{$query}%")
                       ->orWhere('slug', 'like', "%{$query}%");
            }
            $results = $cQuery->take(20)->get()->map(fn($c) => [
                'id'    => $c->id,
                'text'  => $c->name . ($c->slug ? " (/{$c->slug})" : ''),
                'extra' => 'Category',
            ]);
        } elseif ($type === 'brand' && class_exists(Brand::class)) {
            $bQuery = Brand::query()->select('id', 'name');
            if ($query) {
                $bQuery->where('name', 'like', "%{$query}%");
            }
            $results = $bQuery->take(20)->get()->map(fn($b) => [
                'id'    => $b->id,
                'text'  => $b->name,
                'extra' => 'Brand',
            ]);
        } elseif ($type === 'branch' && class_exists(Branch::class)) {
            $brQuery = Branch::query()->select('id', 'name');
            if (Schema::hasColumn('branches', 'code')) {
                $brQuery->addSelect('code');
            }
            if (Schema::hasColumn('branches', 'bin_number')) {
                $brQuery->addSelect('bin_number');
            }
            if ($query) {
                $brQuery->where(function($q) use ($query) {
                    $q->where('id', $query)
                      ->orWhere('name', 'like', "%{$query}%");
                    if (Schema::hasColumn('branches', 'code')) {
                        $q->orWhere('code', 'like', "%{$query}%");
                    }
                });
            }
            $results = $brQuery->take(20)->get()->map(fn($b) => [
                'id'    => $b->id,
                'text'  => $b->name . (!empty($b->code) ? " ({$b->code})" : ''),
                'extra' => !empty($b->bin_number) ? "BIN: {$b->bin_number}" : 'Branch',
            ]);
        } elseif ($type === 'shipping_zone' && class_exists(ShippingZone::class)) {
            $szQuery = ShippingZone::query()->select('id', 'name');
            if ($query) {
                $szQuery->where('name', 'like', "%{$query}%");
            }
            $results = $szQuery->take(20)->get()->map(fn($z) => [
                'id'    => $z->id,
                'text'  => $z->name,
                'extra' => 'Zone',
            ]);
        }

        return response()->json(['results' => $results]);
    }
}