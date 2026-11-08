<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use App\Services\ActivityLogger;
use App\Services\PaymentTransactionService;
use App\Services\InventoryService;
use App\Services\TaxEngineService;
use App\Models\Product;

class PosController extends Controller
{
    /**
     * Dhaf 1: Branch Select Screen / Auto Redirect
     */
    public function index()
    {
        $user = Auth::user();

        // Branch staff ba manager hole auto redirect
        if (!$user->isSuperAdmin() && !empty($user->branch_id)) {
            return redirect()->route('admin.pos.terminal', ['branch_id' => $user->branch_id]);
        }

        // Super Admin-er jonno shob shokriyo branch
        $branches = DB::table('branches')->where('is_active', true)->get();
        return view('admin.pos.select_branch', compact('branches'));
    }

    /**
     * Dhaf 2: Main POS Terminal (Security Guard shoho & Central VAT Settings Sync)
     */
    public function terminal($branch_id)
    {
        $user = Auth::user();

        // Branch security check
        if (!$user->isSuperAdmin() && !empty($user->branch_id) && (int)$user->branch_id !== (int)$branch_id) {
            ActivityLogger::log('unauthorized_branch_pos_access', 'pos', 'Attempted to open unauthorized POS terminal', [
                'record_id'  => $branch_id,
                'record_ref' => 'Branch #' . $branch_id,
                'old_values' => ['assigned_branch_id' => $user->branch_id],
                'new_values' => ['attempted_branch_id' => $branch_id],
                'result'     => 'failed',
            ]);
            abort(403, 'Access Denied: You are not authorized to operate POS Terminal for Branch #' . $branch_id);
        }

        $branch = DB::table('branches')->where('id', $branch_id)->first();
        if (!$branch) {
            abort(404, 'Branch not found');
        }

        $categories = DB::table('categories')->where('is_active', true)->orderBy('name', 'asc')->get();
        $brands = DB::table('brands')->where('is_active', true)->orderBy('name', 'asc')->get();
        $customers = DB::table('users')->where('role', 'customer')->latest('id')->take(60)->get();
        $paymentMethods = ['Cash', 'bKash', 'Nagad', 'Rocket', 'Bank Card'];

        // Central VAT & Tax Settings Extraction for Terminal Frontend
        $taxSettings = [
            'is_vat_enabled'           => false,
            'enable_pos_vat'           => true,
            'vat_label'                => 'VAT',
            'default_vat_rate'         => 5.00,
            'default_calculation_type' => 'exclusive',
            'pos_vat_display_mode'     => 'item_breakdown',
            'branch_tax_mode'          => 'taxable'
        ];

        if (Schema::hasTable('settings')) {
            $rawSettings = DB::table('settings')->whereIn('key', [
                'vat_enabled', 
                'enable_vat', 
                'enable_pos_vat', 
                'vat_label', 
                'default_vat_rate', 
                'default_tax_calculation_type', 
                'pos_vat_display_mode'
            ])->pluck('value', 'key')->toArray();

            $isVatActive = false;
            if (isset($rawSettings['vat_enabled'])) {
                $isVatActive = filter_var($rawSettings['vat_enabled'], FILTER_VALIDATE_BOOLEAN);
            } elseif (isset($rawSettings['enable_vat'])) {
                $isVatActive = filter_var($rawSettings['enable_vat'], FILTER_VALIDATE_BOOLEAN);
            }

            $taxSettings['is_vat_enabled']           = $isVatActive;
            $taxSettings['enable_pos_vat']           = isset($rawSettings['enable_pos_vat']) ? filter_var($rawSettings['enable_pos_vat'], FILTER_VALIDATE_BOOLEAN) : true;
            $taxSettings['vat_label']                = $rawSettings['vat_label'] ?? 'VAT';
            $taxSettings['default_vat_rate']         = isset($rawSettings['default_vat_rate']) ? (float)$rawSettings['default_vat_rate'] : 5.00;
            $taxSettings['default_calculation_type'] = $rawSettings['default_tax_calculation_type'] ?? 'exclusive';
            $taxSettings['pos_vat_display_mode']     = $rawSettings['pos_vat_display_mode'] ?? 'item_breakdown';
        }

        // Branch 0% / Exempt Rule Check for Terminal Interface
        if ($branch_id && Schema::hasTable('tax_rules') && Schema::hasTable('tax_rule_conditions')) {
            $now = now()->toDateString();
            $branchRuleQuery = DB::table('tax_rules')
                ->join('tax_rule_conditions', 'tax_rules.id', '=', 'tax_rule_conditions.tax_rule_id')
                ->where('tax_rules.is_active', true)
                ->where('tax_rule_conditions.condition_type', 'branch')
                ->where('tax_rule_conditions.condition_id', $branch_id)
                ->where('tax_rule_conditions.is_excluded', false);

            if (Schema::hasColumn('tax_rules', 'valid_from')) {
                $branchRuleQuery->where(function ($q) use ($now) {
                    $q->whereNull('tax_rules.valid_from')->orWhere('tax_rules.valid_from', '<=', $now);
                });
            }
            if (Schema::hasColumn('tax_rules', 'valid_to')) {
                $branchRuleQuery->where(function ($q) use ($now) {
                    $q->whereNull('tax_rules.valid_to')->orWhere('tax_rules.valid_to', '>=', $now);
                });
            }

            $branchRule = $branchRuleQuery->orderByDesc('tax_rules.priority')->select('tax_rules.*')->first();

            if ($branchRule) {
                if ($branchRule->status_type === 'exempt' || $branchRule->status_type === 'zero_rated' || (float)$branchRule->rate == 0) {
                    $taxSettings['default_vat_rate'] = 0.00;
                    $taxSettings['branch_tax_mode']  = $branchRule->status_type;
                } else {
                    $taxSettings['default_vat_rate'] = (float)$branchRule->rate;
                }
            }
        }

        return view('admin.pos.terminal', compact(
            'branch', 
            'categories', 
            'brands', 
            'customers', 
            'paymentMethods', 
            'taxSettings'
        ));
    }

    /**
     * Resolves effective tax rate based on strict hierarchy:
     * 1. Global Switch OFF -> 0%
     * 2. Branch Exemption / Zero-Rate Rule
     * 3. Product-Specific Direct VAT (0%, 2%, Flat, etc. - 0 is strictly honored)
     * 4. Category-Specific Direct VAT (0%, 10%, etc. - 0 is strictly honored)
     * 5. Tax Rules Table Cascade
     * 6. Central Settings Default VAT (5%)
     */
    protected function resolveEffectiveItemTax(int $productId, ?int $variantId = null, ?int $branchId = null): array
    {
        $rawSettings = [];
        if (Schema::hasTable('settings')) {
            $rawSettings = DB::table('settings')->whereIn('key', [
                'vat_enabled', 
                'enable_vat', 
                'enable_pos_vat', 
                'default_vat_rate', 
                'default_tax_calculation_type'
            ])->pluck('value', 'key')->toArray();
        }

        $isVatEnabled = false;
        if (isset($rawSettings['vat_enabled'])) {
            $isVatEnabled = filter_var($rawSettings['vat_enabled'], FILTER_VALIDATE_BOOLEAN);
        } elseif (isset($rawSettings['enable_vat'])) {
            $isVatEnabled = filter_var($rawSettings['enable_vat'], FILTER_VALIDATE_BOOLEAN);
        }

        $enablePosVat = isset($rawSettings['enable_pos_vat']) ? filter_var($rawSettings['enable_pos_vat'], FILTER_VALIDATE_BOOLEAN) : true;

        // 1. Global Switch Check
        if (!$isVatEnabled || !$enablePosVat) {
            return [
                'rate'         => 0.0,
                'is_inclusive' => false,
                'source'       => 'vat_disabled',
                'fixed_amount' => 0.0,
                'type'         => 'percent',
                'status_type'  => 'exempt',
            ];
        }

        $defaultRate = isset($rawSettings['default_vat_rate']) ? (float)$rawSettings['default_vat_rate'] : 5.00;
        $defaultCalcType = ($rawSettings['default_tax_calculation_type'] ?? 'exclusive') === 'inclusive';

        // 2. Branch-Level Exemption / Zero-Rate Rule
        if ($branchId && Schema::hasTable('tax_rules') && Schema::hasTable('tax_rule_conditions')) {
            $now = now()->toDateString();
            $branchRule = DB::table('tax_rules')
                ->join('tax_rule_conditions', 'tax_rules.id', '=', 'tax_rule_conditions.tax_rule_id')
                ->where('tax_rules.is_active', true)
                ->where('tax_rule_conditions.condition_type', 'branch')
                ->where('tax_rule_conditions.condition_id', $branchId)
                ->where('tax_rule_conditions.is_excluded', false)
                ->where(function ($q) use ($now) {
                    if (Schema::hasColumn('tax_rules', 'valid_from')) {
                        $q->whereNull('tax_rules.valid_from')->orWhere('tax_rules.valid_from', '<=', $now);
                    }
                })
                ->where(function ($q) use ($now) {
                    if (Schema::hasColumn('tax_rules', 'valid_to')) {
                        $q->whereNull('tax_rules.valid_to')->orWhere('tax_rules.valid_to', '>=', $now);
                    }
                })
                ->orderByDesc('tax_rules.priority')
                ->select('tax_rules.*')
                ->first();

            if ($branchRule) {
                if ($branchRule->status_type === 'exempt' || $branchRule->status_type === 'zero_rated' || (float)$branchRule->rate == 0) {
                    return [
                        'rate'         => 0.0,
                        'is_inclusive' => false,
                        'source'       => 'branch_exempt_rule',
                        'fixed_amount' => 0.0,
                        'type'         => 'percent',
                        'status_type'  => $branchRule->status_type ?: 'zero_rated',
                    ];
                }

                $bRate = (float)($branchRule->rate ?? 0.0);
                $bFixed = (float)($branchRule->fixed_amount ?? 0.0);
                $bType = $branchRule->tax_type === 'fixed' ? 'flat' : 'percent';
                $bInc = ($branchRule->calculation_type === 'inclusive');

                return [
                    'rate'         => $bType === 'flat' ? 0.0 : $bRate,
                    'is_inclusive' => $bInc,
                    'source'       => 'branch_rule',
                    'fixed_amount' => $bType === 'flat' ? $bFixed : 0.0,
                    'type'         => $bType,
                    'status_type'  => 'taxable',
                ];
            }
        }

        $product = DB::table('products')->where('id', $productId)->first();
        if (!$product) {
            return [
                'rate'         => $defaultRate, 
                'is_inclusive' => $defaultCalcType, 
                'source'       => 'default', 
                'fixed_amount' => 0.0, 
                'type'         => 'percent',
                'status_type'  => 'taxable',
            ];
        }

        // 3. Product-Level Direct VAT (Takes precedence over category & global; 0 is strictly honored)
        if (Schema::hasColumn('products', 'vat_amount') && !is_null($product->vat_amount) && trim((string)$product->vat_amount) !== '') {
            $rawVatAmt = (float)$product->vat_amount;
            $type = strtolower($product->vat_type ?? 'percent');
            $isFlat = in_array($type, ['flat', 'fixed']);

            if ($rawVatAmt == 0) {
                return [
                    'rate'         => 0.0,
                    'is_inclusive' => false,
                    'source'       => 'product_zero_vat',
                    'fixed_amount' => 0.0,
                    'type'         => 'percent',
                    'status_type'  => 'zero_rated',
                ];
            }

            return [
                'rate'         => $isFlat ? 0.0 : $rawVatAmt,
                'is_inclusive' => false,
                'source'       => 'product_direct',
                'fixed_amount' => $isFlat ? $rawVatAmt : 0.0,
                'type'         => $isFlat ? 'flat' : 'percent',
                'status_type'  => 'taxable',
            ];
        }

        // 4. Subcategory / Category Override (0 is strictly honored)
        $categoryId = (int)($product->category_id ?? 0);
        $subCategoryId = (int)($product->sub_category_id ?? 0);
        $catVatRecord = null;

        if ($subCategoryId && Schema::hasTable('sub_categories') && Schema::hasColumn('sub_categories', 'vat_amount')) {
            $subCat = DB::table('sub_categories')->where('id', $subCategoryId)->first();
            if ($subCat && !is_null($subCat->vat_amount) && trim((string)$subCat->vat_amount) !== '') {
                $catVatRecord = $subCat;
            }
        }

        if (!$catVatRecord && $categoryId && Schema::hasTable('categories') && Schema::hasColumn('categories', 'vat_amount')) {
            $cat = DB::table('categories')->where('id', $categoryId)->first();
            if ($cat && !is_null($cat->vat_amount) && trim((string)$cat->vat_amount) !== '') {
                $catVatRecord = $cat;
            }
        }

        if ($catVatRecord) {
            $rawCatVat = (float)$catVatRecord->vat_amount;
            $type = strtolower($catVatRecord->vat_type ?? 'percent');
            $isFlat = in_array($type, ['flat', 'fixed']);

            if ($rawCatVat == 0) {
                return [
                    'rate'         => 0.0,
                    'is_inclusive' => false,
                    'source'       => 'category_zero_vat',
                    'fixed_amount' => 0.0,
                    'type'         => 'percent',
                    'status_type'  => 'zero_rated',
                ];
            }

            return [
                'rate'         => $isFlat ? 0.0 : $rawCatVat,
                'is_inclusive' => false,
                'source'       => 'category_override',
                'fixed_amount' => $isFlat ? $rawCatVat : 0.0,
                'type'         => $isFlat ? 'flat' : 'percent',
                'status_type'  => 'taxable',
            ];
        }

        // 5. Tax Rules Cascade Lookup (Conditions for Product or Category)
        if (Schema::hasTable('tax_rules') && Schema::hasTable('tax_rule_conditions')) {
            $now = now()->toDateString();
            $matchedRule = DB::table('tax_rules')
                ->join('tax_rule_conditions', 'tax_rules.id', '=', 'tax_rule_conditions.tax_rule_id')
                ->where('tax_rules.is_active', true)
                ->where('tax_rule_conditions.is_excluded', false)
                ->where(function ($q) use ($productId, $categoryId) {
                    $q->where(function ($sub) use ($productId) {
                        $sub->where('tax_rule_conditions.condition_type', 'product')
                            ->where('tax_rule_conditions.condition_id', $productId);
                    })->orWhere(function ($sub) use ($categoryId) {
                        $sub->where('tax_rule_conditions.condition_type', 'category')
                            ->where('tax_rule_conditions.condition_id', $categoryId);
                    });
                })
                ->where(function ($q) use ($now) {
                    if (Schema::hasColumn('tax_rules', 'valid_from')) {
                        $q->whereNull('tax_rules.valid_from')->orWhere('tax_rules.valid_from', '<=', $now);
                    }
                })
                ->where(function ($q) use ($now) {
                    if (Schema::hasColumn('tax_rules', 'valid_to')) {
                        $q->whereNull('tax_rules.valid_to')->orWhere('tax_rules.valid_to', '>=', $now);
                    }
                })
                ->orderByDesc('tax_rules.priority')
                ->select('tax_rules.*')
                ->first();

            if ($matchedRule) {
                if ($matchedRule->status_type === 'exempt' || $matchedRule->status_type === 'zero_rated') {
                    return [
                        'rate'         => 0.0,
                        'is_inclusive' => false,
                        'source'       => 'rule_' . $matchedRule->status_type,
                        'fixed_amount' => 0.0,
                        'type'         => 'percent',
                        'status_type'  => $matchedRule->status_type,
                    ];
                }

                $rRate = (float)($matchedRule->rate ?? 0.0);
                $rFixed = (float)($matchedRule->fixed_amount ?? 0.0);
                $rType = $matchedRule->tax_type === 'fixed' ? 'flat' : 'percent';
                $rInc = ($matchedRule->calculation_type === 'inclusive');

                return [
                    'rate'         => $rType === 'flat' ? 0.0 : $rRate,
                    'is_inclusive' => $rInc,
                    'source'       => 'tax_rule',
                    'fixed_amount' => $rType === 'flat' ? $rFixed : 0.0,
                    'type'         => $rType,
                    'status_type'  => 'taxable',
                ];
            }
        }

        // 6. Central Settings Default VAT (5%)
        return [
            'rate'         => $defaultRate,
            'is_inclusive' => $defaultCalcType,
            'source'       => 'default_setting',
            'fixed_amount' => 0.0,
            'type'         => 'percent',
            'status_type'  => 'taxable',
        ];
    }

    /**
     * Dynamic POS Tax Assessment Engine (Net Taxable Base Synchronized)
     */
    protected function calculatePosTax(array $items, float $discount = 0.0, ?int $branchId = null, ?int $customerId = null): array
    {
        $totalSubtotal = 0.0;
        foreach ($items as $it) {
            $totalSubtotal += ((float)$it['price'] * (int)$it['qty']);
        }

        // 1. Check Central TaxEngineService first if available
        if (class_exists(TaxEngineService::class) && method_exists(TaxEngineService::class, 'calculateOrderTax')) {
            try {
                $formattedCart = [];
                foreach ($items as $idx => $it) {
                    $formattedCart[$idx] = [
                        'id'         => (int)$it['id'],
                        'product_id' => (int)$it['id'],
                        'variant_id' => !empty($it['variant_id']) ? (int)$it['variant_id'] : null,
                        'price'      => (float)$it['price'],
                        'quantity'   => (int)$it['qty'],
                    ];
                }

                $engineRes = TaxEngineService::calculateOrderTax(
                    $formattedCart,
                    $totalSubtotal,
                    $discount,
                    0.0,
                    $branchId,
                    null,
                    null,
                    $customerId
                );

                $mappedItems = [];
                foreach ($items as $idx => $it) {
                    $engItem = $engineRes['items'][$idx] ?? null;
                    $mappedItems[$idx] = [
                        'product_id'          => (int)$it['id'],
                        'variant_id'          => !empty($it['variant_id']) ? (int)$it['variant_id'] : null,
                        'rule_id'             => $engItem['tax_rule_id'] ?? null,
                        'class_id'            => $engItem['tax_class'] ?? null,
                        'tax_rate'            => (float)($engItem['tax_rate'] ?? 0.0),
                        'tax_amount'          => (float)($engItem['tax_amount'] ?? 0.0),
                        'taxable_base_amount' => (float)($engItem['calculated_base'] ?? ($engItem['net_taxable_base'] ?? ((float)$it['price'] * (int)$it['qty']))),
                        'is_tax_inclusive'    => (bool)($engItem['is_inclusive'] ?? false),
                        'status_type'         => $engItem['status_type'] ?? 'taxable',
                        'breakdown'           => [
                            'rule_name' => $engItem['tax_rule_name'] ?? 'VAT Rule',
                            'rule_code' => $engItem['tax_class'] ?? 'STANDARD',
                            'rate'      => (float)($engItem['tax_rate'] ?? 0.0),
                            'amount'    => (float)($engItem['tax_amount'] ?? 0.0),
                            'type'      => !empty($engItem['is_inclusive']) ? 'inclusive' : 'exclusive',
                        ],
                    ];
                }

                return [
                    'is_vat_enabled'        => (bool)($engineRes['is_vat_enabled'] ?? true),
                    'total_tax'             => (float)($engineRes['total_tax_amount'] ?? 0.0),
                    'exclusive_tax'         => (float)($engineRes['product_exclusive_tax'] ?? 0.0),
                    'taxable_base'          => (float)($engineRes['taxable_product_base'] ?? $totalSubtotal),
                    'is_inclusive'          => (bool)($engineRes['is_tax_inclusive'] ?? false),
                    'items'                 => $mappedItems,
                    'breakdown'             => [
                        'product_tax'          => (float)($engineRes['product_tax_amount'] ?? 0.0),
                        'shipping_tax'         => 0.00,
                        'exclusive_tax'        => (float)($engineRes['product_exclusive_tax'] ?? 0.0),
                        'is_product_inclusive' => (bool)($engineRes['is_tax_inclusive'] ?? false),
                    ],
                    'raw_tax_engine_result' => $engineRes,
                ];
            } catch (\Throwable $e) {
                Log::warning('POS TaxEngineService calculation fallback: ' . $e->getMessage());
            }
        }

        // 2. Comprehensive Local Fallback Engine
        $rawSettings = [];
        if (Schema::hasTable('settings')) {
            $rawSettings = DB::table('settings')->whereIn('key', [
                'vat_enabled', 
                'enable_vat', 
                'enable_pos_vat', 
                'default_vat_rate', 
                'default_tax_calculation_type'
            ])->pluck('value', 'key')->toArray();
        }

        $isVatEnabled = false;
        if (isset($rawSettings['vat_enabled'])) {
            $isVatEnabled = filter_var($rawSettings['vat_enabled'], FILTER_VALIDATE_BOOLEAN);
        } elseif (isset($rawSettings['enable_vat'])) {
            $isVatEnabled = filter_var($rawSettings['enable_vat'], FILTER_VALIDATE_BOOLEAN);
        }

        $enablePosVat = isset($rawSettings['enable_pos_vat']) ? filter_var($rawSettings['enable_pos_vat'], FILTER_VALIDATE_BOOLEAN) : true;

        if (!$isVatEnabled || !$enablePosVat) {
            $emptyItems = [];
            foreach ($items as $idx => $it) {
                $unitPrice = (float)$it['price'];
                $qty = (int)$it['qty'];
                $emptyItems[$idx] = [
                    'product_id'          => (int)$it['id'],
                    'variant_id'          => !empty($it['variant_id']) ? (int)$it['variant_id'] : null,
                    'rule_id'             => null,
                    'class_id'            => null,
                    'tax_rate'            => 0.0,
                    'tax_amount'          => 0.0,
                    'taxable_base_amount' => $unitPrice * $qty,
                    'is_tax_inclusive'    => false,
                    'status_type'         => 'exempt',
                    'breakdown'           => [
                        'rule_name' => 'VAT Disabled',
                        'rule_code' => 'NONE',
                        'rate'      => 0.0,
                        'amount'    => 0.0,
                        'type'      => 'exclusive',
                    ],
                ];
            }

            return [
                'is_vat_enabled'        => false,
                'total_tax'             => 0.00,
                'exclusive_tax'         => 0.00,
                'taxable_base'          => $totalSubtotal,
                'is_inclusive'          => false,
                'items'                 => $emptyItems,
                'breakdown'             => [
                    'product_tax'          => 0.00,
                    'shipping_tax'         => 0.00,
                    'exclusive_tax'        => 0.00,
                    'is_product_inclusive' => false,
                ],
                'raw_tax_engine_result' => [],
            ];
        }

        $discountRatio = ($totalSubtotal > 0 && $discount > 0) ? min(1.0, $discount / $totalSubtotal) : 0.0;

        $itemsTax = [];
        $totalProductTax = 0.0;
        $totalExclusiveTax = 0.0;
        $totalTaxableBase = 0.0;
        $hasInclusive = false;
        $hasExclusive = false;

        foreach ($items as $idx => $it) {
            $productId = (int)$it['id'];
            $variantId = !empty($it['variant_id']) ? (int)$it['variant_id'] : null;
            $qty = (int)$it['qty'];
            $unitPrice = (float)$it['price'];
            $lineTotal = $unitPrice * $qty;

            $lineDiscount = round($lineTotal * $discountRatio, 2);
            $netLineBase = max(0.0, lineTotal - $lineDiscount);

            $resolvedTax = $this->resolveEffectiveItemTax($productId, $variantId, $branchId);

            $taxRate = (float)$resolvedTax['rate'];
            $isInclusive = (bool)$resolvedTax['is_inclusive'];
            $statusType = $resolvedTax['status_type'] ?? 'taxable';
            $calcBase = $netLineBase;
            $taxAmount = 0.0;

            if ($statusType !== 'exempt' && $statusType !== 'zero_rated') {
                if ($resolvedTax['type'] === 'flat') {
                    $taxAmount = round((float)$resolvedTax['fixed_amount'] * $qty, 2);
                    $taxRate = ($netLineBase > 0) ? round(($taxAmount / $netLineBase) * 100, 2) : 0.0;
                    $isInclusive = false;
                } else {
                    if ($taxRate > 0) {
                        if ($isInclusive) {
                            $taxAmount = round($netLineBase - ($netLineBase / (1 + ($taxRate / 100))), 2);
                            $calcBase = round($netLineBase - $taxAmount, 2);
                        } else {
                            $taxAmount = round(($netLineBase * $taxRate) / 100, 2);
                            $calcBase = $netLineBase;
                        }
                    }
                }
            } else {
                $taxAmount = 0.0;
                $taxRate = 0.0;
            }

            if ($isInclusive) {
                $hasInclusive = true;
            } elseif ($taxAmount > 0) {
                $hasExclusive = true;
                $totalExclusiveTax += $taxAmount;
            }

            $totalProductTax += $taxAmount;
            $totalTaxableBase += $calcBase;

            $itemsTax[$idx] = [
                'product_id'          => $productId,
                'variant_id'          => $variantId,
                'rule_id'             => null,
                'class_id'            => null,
                'tax_rate'            => $taxRate,
                'tax_amount'          => $taxAmount,
                'taxable_base_amount' => $calcBase,
                'is_tax_inclusive'    => $isInclusive,
                'status_type'         => $statusType,
                'breakdown'           => [
                    'rule_name' => ucfirst(str_replace('_', ' ', $resolvedTax['source'])),
                    'rule_code' => strtoupper($resolvedTax['source']),
                    'rate'      => $taxRate,
                    'amount'    => $taxAmount,
                    'type'      => $isInclusive ? 'inclusive' : 'exclusive',
                ],
            ];
        }

        return [
            'is_vat_enabled'        => true,
            'total_tax'             => round($totalProductTax, 2),
            'exclusive_tax'         => round($totalExclusiveTax, 2),
            'taxable_base'          => round($totalTaxableBase, 2),
            'is_inclusive'          => $hasInclusive && !$hasExclusive,
            'items'                 => $itemsTax,
            'breakdown'             => [
                'product_tax'          => round($totalProductTax, 2),
                'shipping_tax'         => 0.00,
                'exclusive_tax'        => round($totalExclusiveTax, 2),
                'is_product_inclusive' => $hasInclusive,
            ],
            'raw_tax_engine_result' => [],
        ];
    }

    /**
     * Product Live Filtering & Grid (AJAX - Deep Search in Title, Code, Barcode, SKU, Variants)
     */
    public function filterProductsAjax(Request $request)
    {
        $user = Auth::user();
        
        $branchId = (!$user->isSuperAdmin() && !empty($user->branch_id)) 
            ? $user->branch_id 
            : $request->branch_id;

        $search = trim($request->get('search', ''));
        $categoryId = $request->get('category_id');
        $brandId = $request->get('brand_id');

        $query = DB::table('products')
            ->leftJoin('product_stocks', function ($join) use ($branchId) {
                $join->on('products.id', '=', 'product_stocks.product_id')
                     ->where('product_stocks.branch_id', '=', $branchId);
            })
            ->select(
                'products.id',
                'products.title',
                'products.product_code',
                'products.regular_price',
                'products.sale_price',
                'products.thumbnail',
                'products.category_id',
                'products.sub_category_id',
                DB::raw('COALESCE(product_stocks.quantity, products.stock) as branch_stock')
            );

        if (Schema::hasColumn('products', 'product_struct')) {
            $query->addSelect('products.product_struct');
        }
        if (Schema::hasColumn('products', 'barcode')) {
            $query->addSelect('products.barcode');
        }
        if (Schema::hasColumn('products', 'sku')) {
            $query->addSelect('products.sku');
        }
        if (Schema::hasColumn('products', 'vat_type')) {
            $query->addSelect('products.vat_type');
        }
        if (Schema::hasColumn('products', 'vat_amount')) {
            $query->addSelect('products.vat_amount');
        }

        // Live Deep Search: Title, Product Code, SKU, Barcode, ID & Variants
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                if (is_numeric($search)) {
                    $q->where('products.id', $search);
                }
                $q->orWhere('products.title', 'like', "%{$search}%")
                  ->orWhere('products.product_code', 'like', "%{$search}%");

                if (Schema::hasColumn('products', 'sku')) {
                    $q->orWhere('products.sku', 'like', "%{$search}%");
                }
                if (Schema::hasColumn('products', 'barcode')) {
                    $q->orWhere('products.barcode', 'like', "%{$search}%");
                }

                if (Schema::hasTable('product_variants')) {
                    $q->orWhereExists(function ($vSub) use ($search) {
                        $vSub->select(DB::raw(1))
                            ->from('product_variants')
                            ->whereColumn('product_variants.product_id', 'products.id')
                            ->where(function ($sq) use ($search) {
                                $sq->where('product_variants.color', 'like', "%{$search}%")
                                   ->orWhere('product_variants.size', 'like', "%{$search}%")
                                   ->orWhere('product_variants.product_code', 'like', "%{$search}%");
                                if (Schema::hasColumn('product_variants', 'sku')) {
                                    $sq->orWhere('product_variants.sku', 'like', "%{$search}%");
                                }
                                if (Schema::hasColumn('product_variants', 'barcode')) {
                                    $sq->orWhere('product_variants.barcode', 'like', "%{$search}%");
                                }
                            });
                    });
                }
            });
        }

        if (!empty($categoryId)) {
            $query->where('products.category_id', $categoryId);
        }

        if (!empty($brandId)) {
            $query->where('products.brand_id', $brandId);
        }

        $products = $query->latest('products.id')->paginate(16);

        // Variant Attachment & Tax Attribute Injections
        $pIds = $products->pluck('id')->toArray();
        $variantRows = Schema::hasTable('product_variants')
            ? DB::table('product_variants')->whereIn('product_id', $pIds)->get()->groupBy('product_id')
            : collect();

        $hasVarRegPrice  = Schema::hasColumn('product_variants', 'regular_price');
        $hasVarSalePrice = Schema::hasColumn('product_variants', 'sale_price');
        $hasVarCode      = Schema::hasColumn('product_variants', 'product_code');

        $products->getCollection()->transform(function ($p) use ($variantRows, $hasVarRegPrice, $hasVarSalePrice, $hasVarCode, $branchId) {
            $effectiveTax = $this->resolveEffectiveItemTax($p->id, null, $branchId);
            $p->resolved_tax_rate = $effectiveTax['rate'];
            $p->is_tax_inclusive  = $effectiveTax['is_inclusive'];
            $p->tax_type          = $effectiveTax['type'];
            $p->tax_fixed_amount  = $effectiveTax['fixed_amount'];

            $pVars = $variantRows->get($p->id, collect());
            $p->variants = $pVars->map(function ($v) use ($p, $hasVarRegPrice, $hasVarSalePrice, $hasVarCode, $branchId) {
                $parts = array_filter([$v->color ?? null, $v->size ?? null]);
                $vLabel = !empty($parts) ? implode(' / ', $parts) : ($v->name ?? ('Variant #' . $v->id));
                
                $mainPrice = (float) (!empty($p->sale_price) && $p->sale_price > 0 ? $p->sale_price : $p->regular_price);
                $vPrice = $mainPrice;

                if ($hasVarSalePrice && !empty($v->sale_price) && (float)$v->sale_price > 0) {
                    $vPrice = (float)$v->sale_price;
                } elseif ($hasVarRegPrice && !empty($v->regular_price) && (float)$v->regular_price > 0) {
                    $vPrice = (float)$v->regular_price;
                }

                $vCode = $hasVarCode ? ($v->product_code ?? '') : ('VAR-' . $v->id);
                $vStock = (int) ($v->stock ?? 0);

                $vTax = $this->resolveEffectiveItemTax($p->id, $v->id, $branchId);

                return (object) [
                    'id'               => $v->id,
                    'name'             => $vLabel,
                    'price'            => $vPrice,
                    'stock'            => $vStock,
                    'code'             => $vCode,
                    'tax_rate'         => $vTax['rate'],
                    'is_tax_inclusive' => $vTax['is_inclusive'],
                    'tax_type'         => $vTax['type'],
                    'tax_fixed_amount' => $vTax['fixed_amount']
                ];
            });

            return $p;
        });

        return view('admin.pos.partials.product_grid', compact('products'))->render();
    }

    /**
     * Dedicated Instant Product Search for POS AJAX Dropdown
     */
    public function searchProductsAjax(Request $request)
    {
        $q = trim((string)$request->get('q', ''));
        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $branchId = $request->get('branch_id');

        $products = DB::table('products')
            ->leftJoin('product_stocks', function ($join) use ($branchId) {
                $join->on('products.id', '=', 'product_stocks.product_id')
                     ->where('product_stocks.branch_id', '=', $branchId);
            })
            ->where(function ($sub) use ($q) {
                $sub->where('products.title', 'like', "%{$q}%")
                    ->orWhere('products.product_code', 'like', "%{$q}%")
                    ->orWhere('products.id', $q);
                if (Schema::hasColumn('products', 'sku')) {
                    $sub->orWhere('products.sku', 'like', "%{$q}%");
                }
                if (Schema::hasColumn('products', 'barcode')) {
                    $sub->orWhere('products.barcode', 'like', "%{$q}%");
                }
            })
            ->select(
                'products.id', 'products.title', 'products.product_code', 
                'products.regular_price', 'products.sale_price', 'products.thumbnail',
                DB::raw('COALESCE(product_stocks.quantity, products.stock) as branch_stock')
            )
            ->limit(15)
            ->get();

        $results = [];
        foreach ($products as $p) {
            $taxInfo = $this->resolveEffectiveItemTax($p->id, null, $branchId);
            $results[] = [
                'id'               => $p->id,
                'title'            => $p->title,
                'product_code'     => $p->product_code,
                'price'            => (float)(!empty($p->sale_price) && $p->sale_price > 0 ? $p->sale_price : $p->regular_price),
                'stock'            => (int)($p->branch_stock ?? 0),
                'thumbnail'        => $p->thumbnail,
                'tax_rate'         => $taxInfo['rate'],
                'is_tax_inclusive' => $taxInfo['is_inclusive'],
                'tax_type'         => $taxInfo['type'],
                'tax_fixed_amount' => $taxInfo['fixed_amount']
            ];
        }

        return response()->json($results);
    }

    /**
     * Barcode Scanner Lookup (AJAX - Branch Scoped with Resolved Tax Rate)
     */
    public function scanBarcodeAjax(Request $request)
    {
        $user = Auth::user();
        $barcode = trim((string) $request->input('barcode', ''));
        $branchId = (!$user->isSuperAdmin() && !empty($user->branch_id)) 
            ? $user->branch_id 
            : $request->input('branch_id');

        if (empty($barcode)) {
            return response()->json([
                'success' => false,
                'matched' => 'none',
                'message' => 'বারকোড খালি হতে পারে না।'
            ], 422);
        }

        // 1. Check Product Variants Table
        if (Schema::hasTable('product_variants')) {
            $hasVarBarcode = Schema::hasColumn('product_variants', 'barcode');
            $hasVarCode    = Schema::hasColumn('product_variants', 'product_code');
            $hasVarSku     = Schema::hasColumn('product_variants', 'sku');

            $variantQuery = DB::table('product_variants')
                ->join('products', 'product_variants.product_id', '=', 'products.id')
                ->leftJoin('product_stocks', function ($join) use ($branchId) {
                    $join->on('products.id', '=', 'product_stocks.product_id')
                         ->where('product_stocks.branch_id', '=', $branchId);
                })
                ->where(function ($q) use ($barcode, $hasVarBarcode, $hasVarCode, $hasVarSku) {
                    if ($hasVarBarcode) {
                        $q->where('product_variants.barcode', $barcode);
                    }
                    if ($hasVarCode) {
                        $q->orWhere('product_variants.product_code', $barcode);
                    }
                    if ($hasVarSku) {
                        $q->orWhere('product_variants.sku', $barcode);
                    }
                })
                ->select(
                    'products.id as product_id',
                    'products.title as main_title',
                    'products.thumbnail',
                    'product_variants.id as variant_id',
                    'product_variants.color',
                    'product_variants.size',
                    'product_variants.stock as variant_stock',
                    'products.regular_price as main_regular_price',
                    'products.sale_price as main_sale_price',
                    DB::raw('COALESCE(product_stocks.quantity, products.stock) as branch_stock')
                );

            if ($hasVarCode) {
                $variantQuery->addSelect('product_variants.product_code as variant_sku');
            } elseif ($hasVarSku) {
                $variantQuery->addSelect('product_variants.sku as variant_sku');
            }

            if (Schema::hasColumn('product_variants', 'regular_price')) {
                $variantQuery->addSelect('product_variants.regular_price as variant_regular_price');
            }
            if (Schema::hasColumn('product_variants', 'sale_price')) {
                $variantQuery->addSelect('product_variants.sale_price as variant_sale_price');
            }

            $matchedVariants = $variantQuery->get();

            if ($matchedVariants->count() > 1) {
                return response()->json([
                    'success'  => false,
                    'matched'  => 'multiple',
                    'message'  => 'একই বারকোডে একাধিক ভ্যারিয়েন্ট পাওয়া গেছে! অনুগ্রহ করে ম্যানুয়াল নির্বাচন করুন।',
                    'variants' => $matchedVariants
                ]);
            }

            if ($matchedVariants->count() === 1) {
                $v = $matchedVariants->first();
                $vPrice = (float) (!empty($v->variant_sale_price) && $v->variant_sale_price > 0 
                    ? $v->variant_sale_price 
                    : (!empty($v->variant_regular_price) && $v->variant_regular_price > 0 
                        ? $v->variant_regular_price 
                        : (!empty($v->main_sale_price) && $v->main_sale_price > 0 ? $v->main_sale_price : $v->main_regular_price)));

                $variantTitle = $v->main_title;
                $varDetails = array_filter([$v->color, $v->size]);
                if (!empty($varDetails)) {
                    $variantTitle .= ' (' . implode(' / ', $varDetails) . ')';
                }

                $availableStock = (int) ($v->variant_stock ?? 0);
                $taxInfo = $this->resolveEffectiveItemTax($v->product_id, $v->variant_id, $branchId);

                return response()->json([
                    'success'    => true,
                    'matched'    => 'single',
                    'is_variant' => true,
                    'product'    => [
                        'id'               => $v->product_id,
                        'variant_id'       => $v->variant_id,
                        'title'            => $variantTitle,
                        'product_code'     => $v->variant_sku ?? ('VAR-' . $v->variant_id),
                        'barcode'          => $v->variant_sku ?? '',
                        'price'            => $vPrice,
                        'stock'            => $availableStock,
                        'thumbnail'        => $v->thumbnail ?? '',
                        'tax_rate'         => $taxInfo['rate'],
                        'is_tax_inclusive' => $taxInfo['is_inclusive'],
                        'tax_type'         => $taxInfo['type'],
                        'tax_fixed_amount' => $taxInfo['fixed_amount']
                    ],
                    'message' => 'ভ্যারিয়েন্ট সফলভাবে পাওয়া গেছে।'
                ]);
            }
        }

        // 2. Check Main Products Table
        $productQuery = DB::table('products')
            ->leftJoin('product_stocks', function ($join) use ($branchId) {
                $join->on('products.id', '=', 'product_stocks.product_id')
                     ->where('product_stocks.branch_id', '=', $branchId);
            })
            ->where(function ($q) use ($barcode) {
                if (Schema::hasColumn('products', 'barcode')) {
                    $q->where('products.barcode', $barcode);
                }
                $q->orWhere('products.product_code', $barcode);
                if (Schema::hasColumn('products', 'sku')) {
                    $q->orWhere('products.sku', $barcode);
                }
                if (is_numeric($barcode)) {
                    $q->orWhere('products.id', $barcode);
                }
            })
            ->select(
                'products.id',
                'products.title',
                'products.product_code',
                'products.regular_price',
                'products.sale_price',
                'products.thumbnail',
                'products.stock',
                DB::raw('COALESCE(product_stocks.quantity, products.stock) as branch_stock')
            );

        if (Schema::hasColumn('products', 'product_struct')) {
            $productQuery->addSelect('products.product_struct');
        }

        $matchedProducts = $productQuery->get();

        if ($matchedProducts->count() > 1) {
            return response()->json([
                'success'  => false,
                'matched'  => 'multiple',
                'message'  => 'একই বারকোডে একাধিক প্রোডাক্ট রয়েছে! ম্যানুয়াল গ্রিড থেকে নির্বাচন করুন।',
                'products' => $matchedProducts
            ]);
        }

        if ($matchedProducts->count() === 1) {
            $p = $matchedProducts->first();
            $pPrice = (float) (!empty($p->sale_price) && $p->sale_price > 0 ? $p->sale_price : $p->regular_price);
            $availableStock = (int) ($p->branch_stock ?? ($p->stock ?? 0));
            $taxInfo = $this->resolveEffectiveItemTax($p->id, null, $branchId);

            return response()->json([
                'success'    => true,
                'matched'    => 'single',
                'is_variant' => false,
                'product'    => [
                    'id'               => $p->id,
                    'variant_id'       => null,
                    'title'            => $p->title,
                    'product_code'     => $p->product_code,
                    'barcode'          => $p->product_code,
                    'price'            => $pPrice,
                    'stock'            => $availableStock,
                    'thumbnail'        => $p->thumbnail ?? '',
                    'tax_rate'         => $taxInfo['rate'],
                    'is_tax_inclusive' => $taxInfo['is_inclusive'],
                    'tax_type'         => $taxInfo['type'],
                    'tax_fixed_amount' => $taxInfo['fixed_amount']
                ],
                'message' => 'পণ্য সফলভাবে পাওয়া গেছে।'
            ]);
        }

        return response()->json([
            'success' => false,
            'matched' => 'none',
            'message' => "বারকোড '{$barcode}' এর বিপরীতে কোনো পণ্য বা ভ্যারিয়েন্ট পাওয়া যায়নি।"
        ], 404);
    }

    /**
     * Customer Live AJAX Search
     */
    public function searchCustomersAjax(Request $request)
    {
        $q = trim($request->q);
        $customers = DB::table('users')
            ->where('role', 'customer')
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            })
            ->select('id', 'name', 'phone', 'email')
            ->take(15)
            ->get();

        return response()->json($customers);
    }

    /**
     * Customer Registration (AJAX)
     */
   public function createCustomerAjax(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'phone' => 'required|string|max:20',
        ]);

        $phone = trim($request->phone);
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // আগে একই ফোন দিয়ে কাস্টমার থাকলে সেটিই রিটার্ন করা
        $existing = DB::table('users')->where('phone', $phone)->first();
        if ($existing) {
            return response()->json([
                'success'  => true,
                'customer' => [
                    'id'    => $existing->id,
                    'name'  => $existing->name,
                    'phone' => $existing->phone,
                ]
            ]);
        }

        $inputEmail = $request->filled('email') ? strtolower(trim($request->email)) : null;
        if ($inputEmail && !DB::table('users')->where('email', $inputEmail)->exists()) {
            $email = $inputEmail;
        } else {
            $email = 'c_' . $cleanPhone . '_' . substr(uniqid(), -4) . '@customer.local';
        }

        $userData = [
            'name'           => trim($request->name),
            'email'          => $email,
            'phone'          => $phone,
            'password'       => Hash::make('123456'),
            'role'           => 'customer',
            'created_by'     => Auth::id(),
            'created_at'     => now(),
            'updated_at'     => now(),
        ];

        if (Schema::hasColumn('users', 'bio')) {
            $userData['bio'] = $request->address;
        } elseif (Schema::hasColumn('users', 'address')) {
            $userData['address'] = $request->address;
        }
        if (Schema::hasColumn('users', 'monthly_salary')) {
            $userData['monthly_salary'] = 0;
        }
        if (Schema::hasColumn('users', 'user_type')) {
            $userData['user_type'] = 'customer';
        }
        if (Schema::hasColumn('users', 'is_active')) {
            $userData['is_active'] = true;
        }

        $userId = DB::table('users')->insertGetId($userData);

        return response()->json([
            'success'  => true,
            'customer' => [
                'id'    => $userId,
                'name'  => $request->name,
                'phone' => $phone,
            ]
        ]);
    }

    /**
     * POS Order Place & Stock Adjustment (Tax Snapshotting & Strict Ledger Sync)
     */
    public function placeOrder(Request $request)
    {
        $user = Auth::user();

        // Branch security
        if (!$user->isSuperAdmin() && !empty($user->branch_id) && (int)$request->branch_id !== (int)$user->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied: You cannot place POS orders on another branch.'
            ], 403);
        }

        $request->validate([
            'branch_id'         => 'required|exists:branches,id',
            'items'             => 'required|array|min:1',
            'items.*.id'        => 'required|exists:products,id',
            'items.*.variant_id'=> 'nullable|integer',
            'items.*.qty'       => 'required|integer|min:1',
            'items.*.price'     => 'required|numeric|min:0',
            'payment_method'    => 'required|string',
            'paid_amount'       => 'required|numeric|min:0',
            'transaction_id'    => 'nullable|string|max:100',
        ]);

        // Duplicate Transaction Check
        if ($request->filled('transaction_id')) {
            $dupCheck = PaymentTransactionService::checkDuplicateTransactionId(
                $request->input('transaction_id'),
                $request->input('payment_method')
            );
            if ($dupCheck['is_duplicate']) {
                return response()->json([
                    'success' => false,
                    'message' => $dupCheck['admin_warning'] ?? $dupCheck['message']
                ], 422);
            }
        }

        // 1. Stock Pre-Check
        foreach ($request->items as $it) {
            $pId = (int) $it['id'];
            $vId = !empty($it['variant_id']) ? (int) $it['variant_id'] : null;
            $qty = (int) $it['qty'];

            $availability = InventoryService::checkStockAvailability($pId, $vId, $qty);
            if (!$availability['available']) {
                $itemTitle = $availability['title'] ?? 'পণ্য';
                return response()->json([
                    'success' => false,
                    'message' => "দুঃখিত, '{$itemTitle}'-এর পর্যাপ্ত স্টক নেই। বর্তমান অবশিষ্ট স্টক: {$availability['stock']} টি।"
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $invoiceNo = 'POS-' . date('Ymd') . '-' . mt_rand(1000, 9999);
            $subtotal = 0;

            foreach ($request->items as $it) {
                $subtotal += ($it['price'] * $it['qty']);
            }

            $shipping = (float) ($request->shipping_charge ?? 0);
            $discount = (float) ($request->discount ?? 0);

            // Dynamic NBR Compliant POS Tax Calculation
            $taxCalc = $this->calculatePosTax(
                $request->items,
                $discount,
                (int)$request->branch_id,
                $request->filled('customer_id') ? (int)$request->customer_id : null
            );

            // Grand Total Calculation
            $exclusiveTax = (float)($taxCalc['exclusive_tax'] ?? 0.0);
            $grandTotal = max(0, round(($subtotal + $shipping + $exclusiveTax) - $discount, 2));

            // Paid & Due Synchronization (Order Paid hole Due kokhono asbe na)
            $rawPaid = (float) $request->paid_amount;
            if ($rawPaid >= $grandTotal || $grandTotal <= 0) {
                $paid = $grandTotal;
                $due = 0.00;
                $paymentStatus = 'paid';
            } elseif ($rawPaid > 0) {
                $paid = $rawPaid;
                $due = max(0.00, round($grandTotal - $paid, 2));
                $paymentStatus = 'partial';
            } else {
                $paid = 0.00;
                $due = $grandTotal;
                $paymentStatus = 'unpaid';
            }

            $customerName = 'Walk-in Customer';
            $customerPhone = null;
            $customerEmail = null;
            $customerAddress = null;

            if ($request->filled('customer_id')) {
                $cust = DB::table('users')->where('id', $request->customer_id)->first();
                if ($cust) {
                    $customerName = $cust->name;
                    $customerPhone = $cust->phone;
                    $customerEmail = $cust->email;
                    $customerAddress = $cust->bio ?? null;
                }
            }

            $totalTaxCalculated = (float)($taxCalc['total_tax'] ?? 0.0);

            // Candidate Order Data for 100% Schema-Safe Persistence
            $candidateOrderData = [
                'invoice_no'          => $invoiceNo,
                'order_number'        => $invoiceNo,
                'user_id'             => $request->customer_id ?? null,
                'branch_id'           => $request->branch_id,
                'customer_name'       => $customerName,
                'name'                => $customerName,
                'shipping_name'       => $customerName,
                'customer_phone'      => $customerPhone,
                'phone'               => $customerPhone,
                'shipping_phone'      => $customerPhone,
                'customer_email'      => $customerEmail,
                'email'               => $customerEmail,
                'shipping_email'      => $customerEmail,
                'customer_address'    => $customerAddress,
                'address'             => $customerAddress,
                'shipping_address'    => $customerAddress,
                'subtotal'            => $subtotal,
                'sub_total'           => $subtotal,
                'product_subtotal'    => $subtotal,
                'shipping_charge'     => $shipping,
                'shipping_cost'       => $shipping,
                'delivery_charge'     => $shipping,
                'discount'            => $discount,
                'discount_amount'     => $discount,
                'tax_amount'          => $totalTaxCalculated,
                'tax'                 => $totalTaxCalculated,
                'total_tax'           => $totalTaxCalculated,
                'product_tax_amount'  => $totalTaxCalculated,
                'shipping_tax_amount' => 0.00,
                'taxable_base_amount' => (float)($taxCalc['taxable_base'] ?? $subtotal),
                'is_tax_inclusive'    => (bool)($taxCalc['is_inclusive'] ?? false),
                'tax_breakdown'       => json_encode($taxCalc['breakdown'] ?? []),
                'grand_total'         => $grandTotal,
                'total_amount'        => $grandTotal,
                'total'               => $grandTotal,
                'paid_amount'         => $paid,
                'due_amount'          => $due,
                'payment_method'      => $request->payment_method,
                'payment_status'      => $paymentStatus,
                'order_status'        => 'delivered',
                'status'              => 'delivered',
                'order_type'          => 'pos',
                'order_source'        => 'pos',
                'is_pos'              => true,
                'pos_order'           => true,
                'transaction_id'      => $request->filled('transaction_id') ? trim($request->transaction_id) : null,
                'created_at'          => now(),
                'updated_at'          => now(),
            ];

            // 100% Schema-Safe Filtering (Eliminates Error 1054 Unknown Column)
            $safeOrderData = [];
            foreach ($candidateOrderData as $col => $val) {
                if (Schema::hasColumn('orders', $col)) {
                    $safeOrderData[$col] = $val;
                }
            }

            $orderId = DB::table('orders')->insertGetId($safeOrderData);
            $itemsTable = Schema::hasTable('order_items') ? 'order_items' : (Schema::hasTable('order_details') ? 'order_details' : null);

            // 2. Order Item, Immutable Line Tax Snapshot & Central Stock Deductions
            foreach ($request->items as $idx => $item) {
                $productId = (int) $item['id'];
                $variantId = !empty($item['variant_id']) ? (int) $item['variant_id'] : null;
                $qty = (int) $item['qty'];
                $unitPrice = (float) $item['price'];

                $deduction = InventoryService::deductStock(
                    $productId,
                    $variantId,
                    $qty,
                    'pos_sale',
                    $invoiceNo,
                    $unitPrice,
                    'pos',
                    Auth::id(),
                    "POS Sale #{$invoiceNo} at Branch #{$request->branch_id}"
                );

                if (Schema::hasTable('product_stocks')) {
                    $bStockQuery = DB::table('product_stocks')
                        ->where('product_id', $productId)
                        ->where('branch_id', $request->branch_id);

                    if ($variantId && Schema::hasColumn('product_stocks', 'variant_id')) {
                        $bStockQuery->where('variant_id', $variantId);
                    }

                    $branchStock = $bStockQuery->first();
                    if ($branchStock) {
                        DB::table('product_stocks')->where('id', $branchStock->id)->decrement('quantity', $qty);
                    }
                }

                $variantTitle = null;
                $color = null;
                $size  = null;
                $sku   = $deduction['sku'] ?? null;

                $pTitle = DB::table('products')->where('id', $productId)->value('title') ?? 'Product';

                if ($variantId) {
                    $vRow = DB::table('product_variants')->where('id', $variantId)->first();
                    if ($vRow) {
                        $color = $vRow->color ?? null;
                        $size  = $vRow->size ?? null;
                        $parts = array_filter([$color, $size]);
                        $variantTitle = count($parts) > 0 ? implode(' / ', $parts) : ($vRow->name ?? null);
                        $sku = $vRow->product_code ?? ($vRow->sku ?? $sku);
                    }
                }

                // Line Tax Data
                $lineTax = $taxCalc['items'][$idx] ?? null;
                $itemTaxRate = $lineTax['tax_rate'] ?? 0.0;
                $itemTaxAmount = $lineTax['tax_amount'] ?? 0.0;
                $itemTaxableBase = $lineTax['taxable_base_amount'] ?? ($unitPrice * $qty);
                $itemIsInclusive = $lineTax['is_tax_inclusive'] ?? false;
                $itemRuleId = $lineTax['rule_id'] ?? null;
                $itemClassId = $lineTax['class_id'] ?? null;
                $itemBreakdown = $lineTax['breakdown'] ?? [];

                if ($itemsTable) {
                    $candidateItemData = [
                        'order_id'            => $orderId,
                        'product_id'          => $productId,
                        'variant_id'          => $variantId,
                        'product_title'       => $pTitle,
                        'product_name'        => $pTitle,
                        'title'               => $pTitle,
                        'name'                => $pTitle,
                        'variant_title'       => $variantTitle,
                        'color'               => $color,
                        'size'                => $size,
                        'sku'                 => $sku,
                        'quantity'            => $qty,
                        'qty'                 => $qty,
                        'unit_price'          => $unitPrice,
                        'price'               => $unitPrice,
                        'subtotal'            => $unitPrice * $qty,
                        'total'               => $unitPrice * $qty,
                        'total_price'         => $unitPrice * $qty,
                        'tax_rate'            => $itemTaxRate,
                        'tax_amount'          => $itemTaxAmount,
                        'taxable_base_amount' => $itemTaxableBase,
                        'is_tax_inclusive'    => $itemIsInclusive,
                        'is_inclusive'        => $itemIsInclusive,
                        'tax_rule_id'         => $itemRuleId,
                        'tax_class_id'        => null,
                        'tax_class'           => is_string($itemClassId) ? $itemClassId : 'STANDARD',
                        'tax_breakdown'       => json_encode($itemBreakdown),
                        'purchase_cost'       => $deduction['purchase_cost'],
                        'cogs'                => $deduction['cogs'],
                        'is_cost_estimated'   => $deduction['is_cost_estimated'],
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ];

                    $safeItemData = [];
                    foreach ($candidateItemData as $col => $val) {
                        if (Schema::hasColumn($itemsTable, $col)) {
                            $safeItemData[$col] = $val;
                        }
                    }

                    DB::table($itemsTable)->insert($safeItemData);
                }
            }

            // Permanently record immutable tax snapshot via TaxEngineService
            if (class_exists(TaxEngineService::class) && method_exists(TaxEngineService::class, 'recordOrderTaxSnapshot')) {
                try {
                    $snapData = !empty($taxCalc['raw_tax_engine_result']) ? $taxCalc['raw_tax_engine_result'] : $taxCalc;
                    TaxEngineService::recordOrderTaxSnapshot($orderId, $snapData);
                } catch (\Throwable $snapEx) {
                    // Safe ignore
                }
            }

            // 3. Payment Record
            $savedPosOrder = DB::table('orders')->where('id', $orderId)->first();
            if ($savedPosOrder) {
                PaymentTransactionService::recordPosTransaction(
                    $savedPosOrder,
                    $paid,
                    (string) $request->payment_method,
                    $request->filled('transaction_id') ? trim($request->transaction_id) : null
                );
            }

            ActivityLogger::log('pos_order_place', 'orders', "POS Order completed with Invoice #{$invoiceNo}", [
                'record_id'  => $orderId,
                'record_ref' => $invoiceNo,
                'branch_id'  => $request->branch_id,
                'new_values' => [
                    'grand_total'    => $grandTotal,
                    'tax_amount'     => $totalTaxCalculated,
                    'paid_amount'    => $paid,
                    'payment_method' => $request->payment_method,
                    'items_count'    => count($request->items),
                ],
            ]);

            DB::commit();

            // Multi-Format Direct Print URLs for Seamless Client-Side Autoprint
            $printUrl58 = Route::has('admin.pos.print.thermal58') ? route('admin.pos.print.thermal58', $orderId) : url("admin/pos/print-pos-receipt-58mm/{$orderId}");
            $printUrl80 = Route::has('admin.pos.print.thermal') ? route('admin.pos.print.thermal', $orderId) : url("admin/pos/print-pos-receipt/{$orderId}");
            $printUrlA5 = Route::has('admin.pos.print.a5') ? route('admin.pos.print.a5', $orderId) : url("admin/pos/print-a5-memo/{$orderId}");
            $printUrlA4 = Route::has('admin.pos.print.a4') ? route('admin.pos.print.a4', $orderId) : url("admin/pos/print-a4-memo/{$orderId}");

            return response()->json([
                'success'       => true,
                'message'       => 'Order placed successfully.',
                'invoice_no'    => $invoiceNo,
                'order_id'      => $orderId,
                'tax_amount'    => $totalTaxCalculated,
                'grand_total'   => $grandTotal,
                'print_url'     => $printUrl80,
                'memo_url'      => $printUrlA5,
                'print_url_58'  => $printUrl58,
                'print_url_80'  => $printUrl80,
                'print_url_a5'  => $printUrlA5,
                'print_url_a4'  => $printUrlA4,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * ৩-ইঞ্চি থার্মাল রিসিট প্রিন্ট ভিউ (POS Thermal Receipt 80mm - Mushak 6.3 / BIN Supported)
     */
    public function printPosThermal($id)
    {
        $user = Auth::user();

        $branchSelects = ['orders.*'];
        if (Schema::hasTable('branches')) {
            if (Schema::hasColumn('branches', 'name')) $branchSelects[] = 'branches.name as branch_name';
            if (Schema::hasColumn('branches', 'phone')) $branchSelects[] = 'branches.phone as branch_phone';
            if (Schema::hasColumn('branches', 'address')) $branchSelects[] = 'branches.address as branch_address';
            if (Schema::hasColumn('branches', 'bin_number')) {
                $branchSelects[] = 'branches.bin_number as branch_bin';
            } elseif (Schema::hasColumn('branches', 'bin')) {
                $branchSelects[] = 'branches.bin as branch_bin';
            }
        }
        if (Schema::hasTable('users')) {
            if (Schema::hasColumn('users', 'name')) $branchSelects[] = 'users.name as customer_name';
            if (Schema::hasColumn('users', 'phone')) $branchSelects[] = 'users.phone as customer_phone';
        }

        $orderQuery = DB::table('orders');
        if (Schema::hasTable('branches') && Schema::hasColumn('orders', 'branch_id')) {
            $orderQuery->leftJoin('branches', 'orders.branch_id', '=', 'branches.id');
        }
        if (Schema::hasTable('users') && Schema::hasColumn('orders', 'user_id')) {
            $orderQuery->leftJoin('users', 'orders.user_id', '=', 'users.id');
        }

        $order = $orderQuery->where('orders.id', $id)
            ->select($branchSelects)
            ->first();

        if (!$order) {
            abort(404, 'Order not found');
        }

        if (!$user->isSuperAdmin() && !empty($user->branch_id) && (int)($order->branch_id ?? 0) !== (int)$user->branch_id) {
            abort(403, 'Unauthorized access to this branch memo.');
        }

        $globalSettings = [];
        if (Schema::hasTable('settings')) {
            $globalSettings = DB::table('settings')->pluck('value', 'key')->toArray();
        }

        $siteTitle = $globalSettings['site_title'] ?? ($globalSettings['logo_title'] ?? config('app.name', 'Shop Now BD'));

        // Resolution for BIN & VAT Labels
        $companyBin = !empty($order->branch_bin) 
            ? $order->branch_bin 
            : ($globalSettings['company_bin'] ?? ($globalSettings['bin_number'] ?? '001293847-0101'));
        $vatLabel = $globalSettings['vat_label'] ?? 'VAT';

        $order->subtotal = $order->subtotal ?? ($order->sub_total ?? 0);
        $order->shipping_charge = $order->shipping_charge ?? ($order->shipping_cost ?? ($order->delivery_charge ?? 0));
        $order->discount = $order->discount ?? ($order->discount_amount ?? 0);
        $order->tax_amount = $order->tax_amount ?? 0;
        $order->grand_total = $order->grand_total ?? ($order->total_amount ?? ($order->total ?? 0));

        // Sync Paid & Due amounts
        $pStatus = strtolower($order->payment_status ?? '');
        if (in_array($pStatus, ['paid', 'completed']) || (float)($order->paid_amount ?? 0) >= (float)$order->grand_total) {
            $order->paid_amount = $order->grand_total;
            $order->due_amount  = 0.00;
        } else {
            $order->paid_amount = (float)($order->paid_amount ?? 0);
            $order->due_amount  = (float)($order->due_amount ?? max(0, $order->grand_total - $order->paid_amount));
        }

        $itemsTable = Schema::hasTable('order_items') ? 'order_items' : 'order_details';

        $itemsQuery = DB::table($itemsTable)
            ->leftJoin('products', "{$itemsTable}.product_id", '=', 'products.id');

        if (Schema::hasColumn($itemsTable, 'variant_id')) {
            $itemsQuery->leftJoin('product_variants', "{$itemsTable}.variant_id", '=', 'product_variants.id')
                ->select(
                    "{$itemsTable}.*",
                    'products.title as main_title', 
                    'products.product_code as main_sku',
                    'product_variants.color as pv_color',
                    'product_variants.size as pv_size',
                    'product_variants.product_code as pv_sku'
                );
        } else {
            $itemsQuery->select(
                "{$itemsTable}.*",
                'products.title as main_title', 
                'products.product_code as main_sku'
            );
        }

        $items = $itemsQuery->where("{$itemsTable}.order_id", $id)->get();

        $view = view()->exists('admin.pos.print_thermal_80mm') 
            ? 'admin.pos.print_thermal_80mm' 
            : 'admin.pos.print_thermal';

        return view($view, compact('order', 'items', 'siteTitle', 'companyBin', 'vatLabel'));
    }

    /**
     * ২-ইঞ্চি থার্মাল রিসিট প্রিন্ট ভিউ (POS Thermal Receipt 58mm - Edge-to-Edge Full Width)
     */
    public function printPosThermal58($id)
    {
        $user = Auth::user();

        $branchSelects = ['orders.*'];
        if (Schema::hasTable('branches')) {
            if (Schema::hasColumn('branches', 'name')) $branchSelects[] = 'branches.name as branch_name';
            if (Schema::hasColumn('branches', 'phone')) $branchSelects[] = 'branches.phone as branch_phone';
            if (Schema::hasColumn('branches', 'address')) $branchSelects[] = 'branches.address as branch_address';
            if (Schema::hasColumn('branches', 'bin_number')) {
                $branchSelects[] = 'branches.bin_number as branch_bin';
            } elseif (Schema::hasColumn('branches', 'bin')) {
                $branchSelects[] = 'branches.bin as branch_bin';
            }
        }
        if (Schema::hasTable('users')) {
            if (Schema::hasColumn('users', 'name')) $branchSelects[] = 'users.name as customer_name';
            if (Schema::hasColumn('users', 'phone')) $branchSelects[] = 'users.phone as customer_phone';
        }

        $orderQuery = DB::table('orders');
        if (Schema::hasTable('branches') && Schema::hasColumn('orders', 'branch_id')) {
            $orderQuery->leftJoin('branches', 'orders.branch_id', '=', 'branches.id');
        }
        if (Schema::hasTable('users') && Schema::hasColumn('orders', 'user_id')) {
            $orderQuery->leftJoin('users', 'orders.user_id', '=', 'users.id');
        }

        $order = $orderQuery->where('orders.id', $id)
            ->select($branchSelects)
            ->first();

        if (!$order) {
            abort(404, 'Order not found');
        }

        if (!$user->isSuperAdmin() && !empty($user->branch_id) && (int)($order->branch_id ?? 0) !== (int)$user->branch_id) {
            abort(403, 'Unauthorized access to this branch memo.');
        }

        $globalSettings = [];
        if (Schema::hasTable('settings')) {
            $globalSettings = DB::table('settings')->pluck('value', 'key')->toArray();
        }

        $siteTitle = $globalSettings['site_title'] ?? ($globalSettings['logo_title'] ?? config('app.name', 'Shop Now BD'));

        // Resolution for BIN & VAT Labels
        $companyBin = !empty($order->branch_bin) 
            ? $order->branch_bin 
            : ($globalSettings['company_bin'] ?? ($globalSettings['bin_number'] ?? '001293847-0101'));
        $vatLabel = $globalSettings['vat_label'] ?? 'VAT';

        $order->subtotal = $order->subtotal ?? ($order->sub_total ?? 0);
        $order->shipping_charge = $order->shipping_charge ?? ($order->shipping_cost ?? ($order->delivery_charge ?? 0));
        $order->discount = $order->discount ?? ($order->discount_amount ?? 0);
        $order->tax_amount = $order->tax_amount ?? 0;
        $order->grand_total = $order->grand_total ?? ($order->total_amount ?? ($order->total ?? 0));

        // Sync Paid & Due amounts
        $pStatus = strtolower($order->payment_status ?? '');
        if (in_array($pStatus, ['paid', 'completed']) || (float)($order->paid_amount ?? 0) >= (float)$order->grand_total) {
            $order->paid_amount = $order->grand_total;
            $order->due_amount  = 0.00;
        } else {
            $order->paid_amount = (float)($order->paid_amount ?? 0);
            $order->due_amount  = (float)($order->due_amount ?? max(0, $order->grand_total - $order->paid_amount));
        }

        $itemsTable = Schema::hasTable('order_items') ? 'order_items' : 'order_details';

        $itemsQuery = DB::table($itemsTable)
            ->leftJoin('products', "{$itemsTable}.product_id", '=', 'products.id');

        if (Schema::hasColumn($itemsTable, 'variant_id')) {
            $itemsQuery->leftJoin('product_variants', "{$itemsTable}.variant_id", '=', 'product_variants.id')
                ->select(
                    "{$itemsTable}.*",
                    'products.title as main_title', 
                    'products.product_code as main_sku',
                    'product_variants.color as pv_color',
                    'product_variants.size as pv_size',
                    'product_variants.product_code as pv_sku'
                );
        } else {
            $itemsQuery->select(
                "{$itemsTable}.*",
                'products.title as main_title', 
                'products.product_code as main_sku'
            );
        }

        $items = $itemsQuery->where("{$itemsTable}.order_id", $id)->get();

        $view = view()->exists('admin.pos.print_thermal_58mm') 
            ? 'admin.pos.print_thermal_58mm' 
            : 'admin.pos.print_thermal';

        return view($view, compact('order', 'items', 'siteTitle', 'companyBin', 'vatLabel'));
    }

    /**
     * A5 Memo Print View (Mushak 6.3 / BIN Supported)
     */
    public function printA5Memo($id)
    {
        $user = Auth::user();

        $branchSelects = ['orders.*'];
        if (Schema::hasTable('branches')) {
            if (Schema::hasColumn('branches', 'name')) $branchSelects[] = 'branches.name as branch_name';
            if (Schema::hasColumn('branches', 'phone')) $branchSelects[] = 'branches.phone as branch_phone';
            if (Schema::hasColumn('branches', 'address')) $branchSelects[] = 'branches.address as branch_address';
            if (Schema::hasColumn('branches', 'bin_number')) {
                $branchSelects[] = 'branches.bin_number as branch_bin';
            } elseif (Schema::hasColumn('branches', 'bin')) {
                $branchSelects[] = 'branches.bin as branch_bin';
            }
        }
        if (Schema::hasTable('users')) {
            if (Schema::hasColumn('users', 'name')) $branchSelects[] = 'users.name as customer_name';
            if (Schema::hasColumn('users', 'phone')) $branchSelects[] = 'users.phone as customer_phone';
            if (Schema::hasColumn('users', 'bio')) $branchSelects[] = 'users.bio as customer_address';
        }

        $orderQuery = DB::table('orders');
        if (Schema::hasTable('branches') && Schema::hasColumn('orders', 'branch_id')) {
            $orderQuery->leftJoin('branches', 'orders.branch_id', '=', 'branches.id');
        }
        if (Schema::hasTable('users') && Schema::hasColumn('orders', 'user_id')) {
            $orderQuery->leftJoin('users', 'orders.user_id', '=', 'users.id');
        }

        $order = $orderQuery->where('orders.id', $id)
            ->select($branchSelects)
            ->first();

        if (!$order) {
            abort(404, 'Order not found');
        }

        if (!$user->isSuperAdmin() && !empty($user->branch_id) && (int)($order->branch_id ?? 0) !== (int)$user->branch_id) {
            abort(403, 'Unauthorized access to this branch memo.');
        }

        $globalSettings = [];
        if (Schema::hasTable('settings')) {
            $globalSettings = DB::table('settings')->pluck('value', 'key')->toArray();
        }

        $siteTitle = $globalSettings['site_title'] ?? ($globalSettings['logo_title'] ?? config('app.name', 'Shop Now BD'));

        // Resolution for BIN & VAT Labels
        $companyBin = !empty($order->branch_bin) 
            ? $order->branch_bin 
            : ($globalSettings['company_bin'] ?? ($globalSettings['bin_number'] ?? '001293847-0101'));
        $vatLabel = $globalSettings['vat_label'] ?? 'VAT';

        $order->subtotal = $order->subtotal ?? ($order->sub_total ?? 0);
        $order->shipping_charge = $order->shipping_charge ?? ($order->shipping_cost ?? ($order->delivery_charge ?? 0));
        $order->discount = $order->discount ?? ($order->discount_amount ?? 0);
        $order->tax_amount = $order->tax_amount ?? 0;
        $order->grand_total = $order->grand_total ?? ($order->total_amount ?? ($order->total ?? 0));

        // Sync Paid & Due amounts
        $pStatus = strtolower($order->payment_status ?? '');
        if (in_array($pStatus, ['paid', 'completed']) || (float)($order->paid_amount ?? 0) >= (float)$order->grand_total) {
            $order->paid_amount = $order->grand_total;
            $order->due_amount  = 0.00;
        } else {
            $order->paid_amount = (float)($order->paid_amount ?? 0);
            $order->due_amount  = (float)($order->due_amount ?? max(0, $order->grand_total - $order->paid_amount));
        }

        $itemsTable = Schema::hasTable('order_items') ? 'order_items' : 'order_details';

        $itemsQuery = DB::table($itemsTable)
            ->leftJoin('products', "{$itemsTable}.product_id", '=', 'products.id');

        if (Schema::hasColumn($itemsTable, 'variant_id')) {
            $itemsQuery->leftJoin('product_variants', "{$itemsTable}.variant_id", '=', 'product_variants.id')
                ->select(
                    "{$itemsTable}.*",
                    'products.title as main_title', 
                    'products.product_code as main_sku',
                    'product_variants.color as pv_color',
                    'product_variants.size as pv_size',
                    'product_variants.product_code as pv_sku'
                );
        } else {
            $itemsQuery->select(
                "{$itemsTable}.*",
                'products.title as main_title', 
                'products.product_code as main_sku'
            );
        }

        $items = $itemsQuery->where("{$itemsTable}.order_id", $id)->get();

        $view = view()->exists('admin.pos.print_a5') 
            ? 'admin.pos.print_a5' 
            : 'admin.pos.print_thermal';

        return view($view, compact('order', 'items', 'siteTitle', 'companyBin', 'vatLabel'));
    }

    /**
     * A4 Memo Print View (Mushak 6.3 / BIN Supported)
     */
    public function printA4Memo($id)
    {
        $user = Auth::user();

        $branchSelects = ['orders.*'];
        if (Schema::hasTable('branches')) {
            if (Schema::hasColumn('branches', 'name')) $branchSelects[] = 'branches.name as branch_name';
            if (Schema::hasColumn('branches', 'phone')) $branchSelects[] = 'branches.phone as branch_phone';
            if (Schema::hasColumn('branches', 'address')) $branchSelects[] = 'branches.address as branch_address';
            if (Schema::hasColumn('branches', 'bin_number')) {
                $branchSelects[] = 'branches.bin_number as branch_bin';
            } elseif (Schema::hasColumn('branches', 'bin')) {
                $branchSelects[] = 'branches.bin as branch_bin';
            }
        }
        if (Schema::hasTable('users')) {
            if (Schema::hasColumn('users', 'name')) $branchSelects[] = 'users.name as customer_name';
            if (Schema::hasColumn('users', 'phone')) $branchSelects[] = 'users.phone as customer_phone';
            if (Schema::hasColumn('users', 'bio')) $branchSelects[] = 'users.bio as customer_address';
        }

        $orderQuery = DB::table('orders');
        if (Schema::hasTable('branches') && Schema::hasColumn('orders', 'branch_id')) {
            $orderQuery->leftJoin('branches', 'orders.branch_id', '=', 'branches.id');
        }
        if (Schema::hasTable('users') && Schema::hasColumn('orders', 'user_id')) {
            $orderQuery->leftJoin('users', 'orders.user_id', '=', 'users.id');
        }

        $order = $orderQuery->where('orders.id', $id)
            ->select($branchSelects)
            ->first();

        if (!$order) {
            abort(404, 'Order not found');
        }

        if (!$user->isSuperAdmin() && !empty($user->branch_id) && (int)($order->branch_id ?? 0) !== (int)$user->branch_id) {
            abort(403, 'Unauthorized access to this branch memo.');
        }

        $globalSettings = [];
        if (Schema::hasTable('settings')) {
            $globalSettings = DB::table('settings')->pluck('value', 'key')->toArray();
        }

        $siteTitle = $globalSettings['site_title'] ?? ($globalSettings['logo_title'] ?? config('app.name', 'Shop Now BD'));

        // Resolution for BIN & VAT Labels
        $companyBin = !empty($order->branch_bin) 
            ? $order->branch_bin 
            : ($globalSettings['company_bin'] ?? ($globalSettings['bin_number'] ?? '001293847-0101'));
        $vatLabel = $globalSettings['vat_label'] ?? 'VAT';

        $order->subtotal = $order->subtotal ?? ($order->sub_total ?? 0);
        $order->shipping_charge = $order->shipping_charge ?? ($order->shipping_cost ?? ($order->delivery_charge ?? 0));
        $order->discount = $order->discount ?? ($order->discount_amount ?? 0);
        $order->tax_amount = $order->tax_amount ?? 0;
        $order->grand_total = $order->grand_total ?? ($order->total_amount ?? ($order->total ?? 0));

        // Sync Paid & Due amounts
        $pStatus = strtolower($order->payment_status ?? '');
        if (in_array($pStatus, ['paid', 'completed']) || (float)($order->paid_amount ?? 0) >= (float)$order->grand_total) {
            $order->paid_amount = $order->grand_total;
            $order->due_amount  = 0.00;
        } else {
            $order->paid_amount = (float)($order->paid_amount ?? 0);
            $order->due_amount  = (float)($order->due_amount ?? max(0, $order->grand_total - $order->paid_amount));
        }

        $itemsTable = Schema::hasTable('order_items') ? 'order_items' : 'order_details';

        $itemsQuery = DB::table($itemsTable)
            ->leftJoin('products', "{$itemsTable}.product_id", '=', 'products.id');

        if (Schema::hasColumn($itemsTable, 'variant_id')) {
            $itemsQuery->leftJoin('product_variants', "{$itemsTable}.variant_id", '=', 'product_variants.id')
                ->select(
                    "{$itemsTable}.*",
                    'products.title as main_title', 
                    'products.product_code as main_sku',
                    'product_variants.color as pv_color',
                    'product_variants.size as pv_size',
                    'product_variants.product_code as pv_sku'
                );
        } else {
            $itemsQuery->select(
                "{$itemsTable}.*",
                'products.title as main_title', 
                'products.product_code as main_sku'
            );
        }

        $items = $itemsQuery->where("{$itemsTable}.order_id", $id)->get();

        $view = view()->exists('admin.pos.print_a4') 
            ? 'admin.pos.print_a4' 
            : 'admin.pos.print_a5';

        return view($view, compact('order', 'items', 'siteTitle', 'companyBin', 'vatLabel'));
    }

    /**
     * সার্বজনীন পেপার ফরম্যাট রাউটার (Dynamic Multi-Format Resolver)
     */
    public function printReceipt($id, $format = '80mm')
    {
        return match(strtolower((string)$format)) {
            '58mm', '2inch', '2' => $this->printPosThermal58($id),
            'a4'                 => $this->printA4Memo($id),
            'a5'                 => $this->printA5Memo($id),
            default              => $this->printPosThermal($id),
        };
    }

    /**
     * এলিয়াস মেথডসমূহ (BranchPOSController মেথড সামঞ্জস্যের জন্য)
     */
    public function printThermal($id)
    {
        return $this->printPosThermal($id);
    }

    public function printThermal58($id)
    {
        return $this->printPosThermal58($id);
    }

    public function printA5($id)
    {
        return $this->printA5Memo($id);
    }

    public function printA4($id)
    {
        return $this->printA4Memo($id);
    }
}