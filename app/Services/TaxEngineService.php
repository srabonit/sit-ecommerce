<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TaxEngineService
{
    /**
     * Check if Global VAT is enabled from settings
     * Requirement: Settings-e vat_enabled off thakle shob calculation 0 hobe
     */
    public static function isVatEnabled(): bool
    {
        if (!Schema::hasTable('settings')) {
            return false;
        }

        $setting = DB::table('settings')->where('key', 'vat_enabled')->value('value');
        if ($setting === null) {
            $setting = DB::table('settings')->where('key', 'enable_vat')->value('value');
        }

        return filter_var($setting ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Central Order Tax & VAT Calculation Method (Cart, Checkout, Manual Order & POS)
     *
     * @param array $cartItems
     * @param float $subtotal
     * @param float $discount
     * @param float $deliveryCharge
     * @param int|null $branchId
     * @param string|null $district
     * @param int|null $zoneId
     * @param int|null $userId
     * @return array
     */
    public static function calculateOrderTax(
        array $cartItems,
        float $subtotal,
        float $discount = 0.0,
        float $deliveryCharge = 0.0,
        ?int $branchId = null,
        ?string $district = null,
        ?int $zoneId = null,
        ?int $userId = null
    ): array {
        $subtotal = max(0.0, round($subtotal, 2));
        $discount = max(0.0, min($subtotal, round($discount, 2)));
        $netProductSubtotal = max(0.0, $subtotal - $discount);
        $deliveryCharge = max(0.0, round($deliveryCharge, 2));

        $isVatActive = self::isVatEnabled();

        // 1. Global switch off thakle all tax & VAT strictly 0.00
        if (!$isVatActive) {
            $finalPayable = round($netProductSubtotal + $deliveryCharge, 2);
            $emptyItems = [];

            foreach ($cartItems as $key => $item) {
                $qty = max(1, (int)($item['quantity'] ?? ($item['qty'] ?? 1)));
                $rawPrice = $item['price'] ?? 0;
                if (is_string($rawPrice)) {
                    $rawPrice = preg_replace('/[^0-9.]/', '', $rawPrice);
                }
                $unitPrice = max(0.0, (float)$rawPrice);
                $lineGross = round($unitPrice * $qty, 2);

                $emptyItems[$key] = [
                    'product_id'          => (int)($item['id'] ?? ($item['product_id'] ?? 0)),
                    'variant_id'          => !empty($item['variant_id']) ? (int)$item['variant_id'] : null,
                    'product_title'       => $item['title'] ?? ($item['name'] ?? 'Product'),
                    'quantity'            => $qty,
                    'unit_price'          => $unitPrice,
                    'gross_total'         => $lineGross,
                    'discount_allocated'  => 0.0,
                    'net_taxable_base'    => $lineGross,
                    'calculated_base'     => $lineGross,
                    'taxable_base_amount' => $lineGross,
                    'tax_rule_id'         => null,
                    'tax_rule_name'       => 'VAT Disabled',
                    'tax_class'           => 'EXEMPT',
                    'tax_rate'            => 0.0,
                    'tax_amount'          => 0.0,
                    'calculation_type'    => 'exclusive',
                    'status_type'         => 'exempt',
                    'is_inclusive'        => false,
                    'is_tax_inclusive'    => false,
                    'applied_from'        => 'none',
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
                'is_vat_enabled'            => false,
                'subtotal'                  => $subtotal,
                'discount'                  => $discount,
                'net_product_subtotal'      => $netProductSubtotal,
                'delivery_charge'           => $deliveryCharge,
                'taxable_product_base'      => 0.0,
                'taxable_base'              => 0.0,
                'exempt_product_base'       => $netProductSubtotal,
                'zero_rated_product_base'   => 0.0,
                'product_exclusive_tax'     => 0.0,
                'product_inclusive_tax'     => 0.0,
                'product_tax_amount'        => 0.0,
                'product_tax'               => 0.0,
                'shipping_tax_amount'       => 0.0,
                'shipping_tax'              => 0.0,
                'shipping_tax_rate'         => 0.0,
                'shipping_tax_type'         => 'exclusive',
                'service_tax_amount'        => 0.0,
                'total_tax_amount'          => 0.0,
                'total_tax'                 => 0.0,
                'exclusive_tax'             => 0.0,
                'is_tax_inclusive'          => false,
                'is_inclusive'              => false,
                'has_mixed_tax_modes'       => false,
                'final_payable'             => $finalPayable,
                'items'                     => $emptyItems,
                'breakdown'                 => [
                    'product_tax'          => 0.00,
                    'shipping_tax'         => 0.00,
                    'exclusive_tax'        => 0.00,
                    'is_product_inclusive' => false,
                    'is_shipping_inclusive'=> false,
                ],
                'snapshot'                  => [
                    'calculated_at'         => now()->toDateTimeString(),
                    'is_vat_enabled'        => false,
                    'branch_id'             => $branchId,
                    'district'              => $district,
                    'zone_id'               => $zoneId,
                    'total_tax'             => 0.0,
                    'product_tax'           => 0.0,
                    'shipping_tax'          => 0.0,
                    'items_count'           => count($cartItems),
                ]
            ];
        }

        $itemBreakdowns = [];
        $totalProductExclusiveTax = 0.0;
        $totalProductInclusiveTax = 0.0;
        $totalTaxableProductBase = 0.0;
        $totalExemptProductBase = 0.0;
        $totalZeroRatedProductBase = 0.0;

        $discountRatio = ($subtotal > 0) ? ($discount / $subtotal) : 0.0;

        foreach ($cartItems as $key => $item) {
            $productId = (int)($item['id'] ?? ($item['product_id'] ?? 0));
            $variantId = !empty($item['variant_id']) ? (int)$item['variant_id'] : null;
            $qty = max(1, (int)($item['quantity'] ?? ($item['qty'] ?? 1)));
            
            $rawPrice = $item['price'] ?? 0;
            if (is_string($rawPrice)) {
                $rawPrice = preg_replace('/[^0-9.]/', '', $rawPrice);
            }
            $unitPrice = max(0.0, (float)$rawPrice);
            $lineGross = round($unitPrice * $qty, 2);

            $lineDiscount = round($lineGross * $discountRatio, 2);
            $lineNetBase = max(0.0, $lineGross - $lineDiscount);

            // Rule resolution with full strict hierarchy
            $ruleResult = self::resolveProductTaxRule($productId, $variantId, $branchId, $district, $zoneId, $userId);

            $taxRate = (float)($ruleResult['rate'] ?? 0.0);
            $taxType = $ruleResult['tax_type'] ?? 'percentage';
            $calcType = $ruleResult['calculation_type'] ?? 'exclusive';
            $statusType = $ruleResult['status_type'] ?? 'taxable';
            $fixedAmount = (float)($ruleResult['fixed_amount'] ?? 0.0);

            $lineTaxAmount = 0.0;
            $calculatedBase = $lineNetBase;

            if ($statusType === 'exempt') {
                $totalExemptProductBase += $lineNetBase;
            } elseif ($statusType === 'zero_rated') {
                $totalZeroRatedProductBase += $lineNetBase;
            } else {
                $totalTaxableProductBase += $lineNetBase;

                // Flat vs Percentage calculation
                if ($taxType === 'fixed') {
                    $lineTaxAmount = round($fixedAmount * $qty, 2);
                    $totalProductExclusiveTax += $lineTaxAmount;
                } else {
                    if ($calcType === 'inclusive') {
                        if ($taxRate > 0) {
                            $calculatedBase = round($lineNetBase / (1 + ($taxRate / 100)), 2);
                            $lineTaxAmount = round($lineNetBase - $calculatedBase, 2);
                        }
                        $totalProductInclusiveTax += $lineTaxAmount;
                    } else {
                        $lineTaxAmount = round($lineNetBase * ($taxRate / 100), 2);
                        $totalProductExclusiveTax += $lineTaxAmount;
                    }
                }
            }

            $itemBreakdowns[$key] = [
                'product_id'          => $productId,
                'variant_id'          => $variantId,
                'product_title'       => $item['title'] ?? ($item['name'] ?? 'Product'),
                'quantity'            => $qty,
                'unit_price'          => $unitPrice,
                'gross_total'         => $lineGross,
                'discount_allocated'  => $lineDiscount,
                'net_taxable_base'    => $lineNetBase,
                'calculated_base'     => $calculatedBase,
                'taxable_base_amount' => $calculatedBase,
                'tax_rule_id'         => $ruleResult['rule_id'] ?? null,
                'tax_rule_name'       => $ruleResult['rule_name'] ?? 'No Tax',
                'tax_class'           => $ruleResult['class_code'] ?? 'STANDARD',
                'tax_rate'            => $taxRate,
                'tax_amount'          => $lineTaxAmount,
                'calculation_type'    => $calcType,
                'status_type'         => $statusType,
                'is_inclusive'        => ($calcType === 'inclusive'),
                'is_tax_inclusive'    => ($calcType === 'inclusive'),
                'applied_from'        => $ruleResult['applied_from'] ?? 'rule',
                'breakdown'           => [
                    'rule_name' => $ruleResult['rule_name'] ?? 'No Tax',
                    'rule_code' => $ruleResult['class_code'] ?? 'STANDARD',
                    'rate'      => $taxRate,
                    'amount'    => $lineTaxAmount,
                    'type'      => $calcType,
                ]
            ];
        }

        // Delivery charge VAT check (only if setting explicitly allows VAT on shipping)
        $applyShippingVat = false;
        if (Schema::hasTable('settings')) {
            $shippingVatSetting = DB::table('settings')->where('key', 'apply_vat_on_shipping')->value('value');
            $applyShippingVat = filter_var($shippingVatSetting ?? false, FILTER_VALIDATE_BOOLEAN);
        }

        $deliveryTaxAmount = 0.0;
        $deliveryTaxRate = 0.0;
        $deliveryCalcType = 'exclusive';

        if ($applyShippingVat && $deliveryCharge > 0) {
            $deliveryTaxResult = self::resolveDeliveryTaxRule($district, $zoneId, $branchId);
            $deliveryTaxRate = (float)($deliveryTaxResult['rate'] ?? 0.0);
            $deliveryCalcType = $deliveryTaxResult['calculation_type'] ?? 'exclusive';
            $deliveryTaxStatus = $deliveryTaxResult['status_type'] ?? 'taxable';

            if ($deliveryTaxStatus === 'taxable' && $deliveryTaxRate > 0) {
                if ($deliveryCalcType === 'inclusive') {
                    $deliveryBase = round($deliveryCharge / (1 + ($deliveryTaxRate / 100)), 2);
                    $deliveryTaxAmount = round($deliveryCharge - $deliveryBase, 2);
                } else {
                    $deliveryTaxAmount = round($deliveryCharge * ($deliveryTaxRate / 100), 2);
                }
            }
        }

        $totalProductTax = round($totalProductExclusiveTax + $totalProductInclusiveTax, 2);
        $totalShippingTax = round($deliveryTaxAmount, 2);
        $totalServiceTax = 0.00;
        $totalTaxAmount = round($totalProductTax + $totalShippingTax + $totalServiceTax, 2);

        $exclusiveAdditions = round($totalProductExclusiveTax + ($deliveryCalcType === 'exclusive' ? $deliveryTaxAmount : 0.0), 2);
        $finalPayable = round($netProductSubtotal + $deliveryCharge + $exclusiveAdditions, 2);

        $isOverallInclusive = ($totalProductInclusiveTax > 0 && $totalProductExclusiveTax === 0.0);

        return [
            'is_vat_enabled'            => true,
            'subtotal'                  => $subtotal,
            'discount'                  => $discount,
            'net_product_subtotal'      => $netProductSubtotal,
            'delivery_charge'           => $deliveryCharge,
            'taxable_product_base'      => round($totalTaxableProductBase, 2),
            'taxable_base'              => round($totalTaxableProductBase, 2),
            'exempt_product_base'       => round($totalExemptProductBase, 2),
            'zero_rated_product_base'   => round($totalZeroRatedProductBase, 2),
            'product_exclusive_tax'     => round($totalProductExclusiveTax, 2),
            'product_inclusive_tax'     => round($totalProductInclusiveTax, 2),
            'product_tax_amount'        => $totalProductTax,
            'product_tax'               => $totalProductTax,
            'shipping_tax_amount'       => $totalShippingTax,
            'shipping_tax'              => $totalShippingTax,
            'shipping_tax_rate'         => $deliveryTaxRate,
            'shipping_tax_type'         => $deliveryCalcType,
            'service_tax_amount'        => $totalServiceTax,
            'total_tax_amount'          => $totalTaxAmount,
            'total_tax'                 => $totalTaxAmount,
            'exclusive_tax'             => $exclusiveAdditions,
            'is_tax_inclusive'          => $isOverallInclusive,
            'is_inclusive'              => $isOverallInclusive,
            'has_mixed_tax_modes'       => ($totalProductInclusiveTax > 0 && $totalProductExclusiveTax > 0),
            'final_payable'             => $finalPayable,
            'items'                     => $itemBreakdowns,
            'breakdown'                 => [
                'product_tax'          => $totalProductTax,
                'shipping_tax'         => $totalShippingTax,
                'exclusive_tax'        => $exclusiveAdditions,
                'is_product_inclusive' => $isOverallInclusive,
                'is_shipping_inclusive'=> ($deliveryCalcType === 'inclusive'),
            ],
            'snapshot'                  => [
                'calculated_at'         => now()->toDateTimeString(),
                'is_vat_enabled'        => true,
                'branch_id'             => $branchId,
                'district'              => $district,
                'zone_id'               => $zoneId,
                'total_tax'             => $totalTaxAmount,
                'product_tax'           => $totalProductTax,
                'shipping_tax'          => $totalShippingTax,
                'items_count'           => count($cartItems),
            ]
        ];
    }

    /**
     * Resolve Tax Rule for a Product
     * Priority:
     * 1. Global VAT Switch OFF -> 0%
     * 2. Branch Specific Rule (0%, 1%, Exempt, Flat - strictly applied if branch set)
     * 3. Product-Specific Direct VAT (0%, 2%, Flat, etc. - 0 is strictly honored)
     * 4. Category-Specific Direct VAT (0%, 10%, etc. - 0 is strictly honored)
     * 5. Tax Rules Table Cascade
     * 6. Central Settings Default VAT (5%)
     */
    public static function resolveProductTaxRule(
        int $productId,
        ?int $variantId = null,
        ?int $branchId = null,
        ?string $district = null,
        ?int $zoneId = null,
        ?int $userId = null
    ): array {
        if (!self::isVatEnabled()) {
            return self::emptyRuleResponse();
        }

        // Get system default calculation type (inclusive vs exclusive)
        $defaultCalcType = 'exclusive';
        if (Schema::hasTable('settings')) {
            $defaultCalcType = DB::table('settings')->where('key', 'default_tax_calculation_type')->value('value') ?? 'exclusive';
        }

        // 1. Branch-Level Rule (Priority 1: Branch rule ALWAYS applies to orders of this branch)
        if ($branchId && Schema::hasTable('tax_rules') && Schema::hasTable('tax_rule_conditions')) {
            $now = Carbon::now()->toDateString();
            $branchRule = DB::table('tax_rules')
                ->leftJoin('tax_classes', 'tax_rules.tax_class_id', '=', 'tax_classes.id')
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
                ->select('tax_rules.*', 'tax_classes.code as class_code')
                ->first();

            // FIXED: জিরো, এক্সেম্পট কিংবা ১% বা যেকোনো রেট থাকলে সরাসরি এই ব্রাঞ্চ রুল কার্যকর হবে!
            if ($branchRule) {
                $branchResponse = self::formatRuleResponse($branchRule);
                $branchResponse['applied_from'] = 'branch_rule';
                return $branchResponse;
            }
        }

        $product = null;
        if ($productId > 0 && Schema::hasTable('products')) {
            $product = DB::table('products')->where('id', $productId)->first();
        }

        if (!$product) {
            return self::resolveGlobalTaxRule();
        }

        if (!empty($product->is_tax_exempt)) {
            return [
                'rule_id'          => null,
                'rule_name'        => 'Exempt Product',
                'class_code'       => 'EXEMPT',
                'tax_type'         => 'percentage',
                'rate'             => 0.0000,
                'fixed_amount'     => 0.00,
                'calculation_type' => 'exclusive',
                'status_type'      => 'exempt',
                'applied_from'     => 'exempt',
            ];
        }

        // Determine calculation mode for product (respects product-level override or store default)
        $itemCalcMode = $defaultCalcType;
        if (isset($product->tax_type) && in_array($product->tax_type, ['inclusive', 'exclusive'])) {
            $itemCalcMode = $product->tax_type;
        } elseif (isset($product->calculation_type) && in_array($product->calculation_type, ['inclusive', 'exclusive'])) {
            $itemCalcMode = $product->calculation_type;
        } elseif (!empty($product->is_tax_inclusive)) {
            $itemCalcMode = 'inclusive';
        }

        // 2. Product-Level Direct VAT (Strictly honors 0 as 0%, 2 as 2%, or Flat)
        if (Schema::hasColumn('products', 'vat_amount') && !is_null($product->vat_amount) && trim((string)$product->vat_amount) !== '') {
            $rawVatAmt = (float)$product->vat_amount;
            $vatType = strtolower($product->vat_type ?? 'percent');
            $isFlat = in_array($vatType, ['flat', 'fixed']);

            if ($rawVatAmt == 0) {
                return [
                    'rule_id'          => null,
                    'rule_name'        => 'Product Zero VAT (0%)',
                    'class_code'       => 'PRODUCT_ZERO_VAT',
                    'tax_type'         => 'percentage',
                    'rate'             => 0.0000,
                    'fixed_amount'     => 0.00,
                    'calculation_type' => 'exclusive',
                    'status_type'      => 'zero_rated',
                    'applied_from'     => 'product_zero',
                ];
            }

            return [
                'rule_id'          => null,
                'rule_name'        => 'Product Specific VAT (' . ($isFlat ? 'Flat' : '%') . ')',
                'class_code'       => 'PRODUCT_VAT',
                'tax_type'         => $isFlat ? 'fixed' : 'percentage',
                'rate'             => $isFlat ? 0.0000 : $rawVatAmt,
                'fixed_amount'     => $isFlat ? $rawVatAmt : 0.00,
                'calculation_type' => $isFlat ? 'exclusive' : $itemCalcMode,
                'status_type'      => 'taxable',
                'applied_from'     => 'product',
            ];
        }

        // Legacy override_tax_rate check
        if (isset($product->override_tax_rate) && !is_null($product->override_tax_rate) && trim((string)$product->override_tax_rate) !== '') {
            $rawRate = (float)$product->override_tax_rate;
            return [
                'rule_id'          => null,
                'rule_name'        => 'Product Specific Override',
                'class_code'       => 'CUSTOM_OVERRIDE',
                'tax_type'         => 'percentage',
                'rate'             => $rawRate,
                'fixed_amount'     => 0.00,
                'calculation_type' => $itemCalcMode,
                'status_type'      => ($rawRate == 0) ? 'zero_rated' : 'taxable',
                'applied_from'     => 'product_override',
            ];
        }

        // 3. Category / Subcategory Direct VAT (Strictly honors 0 as 0%)
        $subCategoryId = (int)($product->sub_category_id ?? 0);
        $categoryId = (int)($product->category_id ?? 0);
        if (!$categoryId && Schema::hasTable('product_category')) {
            $categoryId = (int)DB::table('product_category')
                ->where('product_id', $productId)
                ->where('type', 'parent')
                ->value('category_id');
        }

        $catRecord = null;
        if ($subCategoryId && Schema::hasTable('sub_categories') && Schema::hasColumn('sub_categories', 'vat_amount')) {
            $subCat = DB::table('sub_categories')->where('id', $subCategoryId)->first();
            if ($subCat && !is_null($subCat->vat_amount) && trim((string)$subCat->vat_amount) !== '') {
                $catRecord = $subCat;
            }
        }

        if (!$catRecord && $categoryId && Schema::hasTable('categories') && Schema::hasColumn('categories', 'vat_amount')) {
            $cat = DB::table('categories')->where('id', $categoryId)->first();
            if ($cat && !is_null($cat->vat_amount) && trim((string)$cat->vat_amount) !== '') {
                $catRecord = $cat;
            }
        }

        if ($catRecord) {
            $rawCatVat = (float)$catRecord->vat_amount;
            $vatType = strtolower($catRecord->vat_type ?? 'percent');
            $isFlat = in_array($vatType, ['flat', 'fixed']);

            if ($rawCatVat == 0) {
                return [
                    'rule_id'          => null,
                    'rule_name'        => 'Category Zero VAT (0%)',
                    'class_code'       => 'CATEGORY_ZERO_VAT',
                    'tax_type'         => 'percentage',
                    'rate'             => 0.0000,
                    'fixed_amount'     => 0.00,
                    'calculation_type' => 'exclusive',
                    'status_type'      => 'zero_rated',
                    'applied_from'     => 'category_zero',
                ];
            }

            return [
                'rule_id'          => null,
                'rule_name'        => 'Category Specific VAT (' . ($isFlat ? 'Flat' : '%') . ')',
                'class_code'       => 'CATEGORY_VAT',
                'tax_type'         => $isFlat ? 'fixed' : 'percentage',
                'rate'             => $isFlat ? 0.0000 : $rawCatVat,
                'fixed_amount'     => $isFlat ? $rawCatVat : 0.00,
                'calculation_type' => $isFlat ? 'exclusive' : $itemCalcMode,
                'status_type'      => 'taxable',
                'applied_from'     => 'category',
            ];
        }

        // 4. Tax Rules Database Tables Cascade (Variant > Product > Category > Brand > Branch)
        if (!Schema::hasTable('tax_rules')) {
            return self::resolveGlobalTaxRule();
        }

        $now = Carbon::now()->toDateString();
        $baseRulesQuery = DB::table('tax_rules')
            ->leftJoin('tax_classes', 'tax_rules.tax_class_id', '=', 'tax_classes.id')
            ->where('tax_rules.is_active', true)
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
            ->select('tax_rules.*', 'tax_classes.code as class_code');

        if (!Schema::hasTable('tax_rule_conditions')) {
            return self::resolveGlobalTaxRule();
        }

        if ($variantId) {
            $varRule = (clone $baseRulesQuery)
                ->join('tax_rule_conditions', 'tax_rules.id', '=', 'tax_rule_conditions.tax_rule_id')
                ->where('tax_rule_conditions.condition_type', 'variant')
                ->where('tax_rule_conditions.condition_id', $variantId)
                ->where('tax_rule_conditions.is_excluded', false)
                ->orderByDesc('tax_rules.priority')
                ->first();

            if ($varRule) return self::formatRuleResponse($varRule);
        }

        $prodRule = (clone $baseRulesQuery)
            ->join('tax_rule_conditions', 'tax_rules.id', '=', 'tax_rule_conditions.tax_rule_id')
            ->where('tax_rule_conditions.condition_type', 'product')
            ->where('tax_rule_conditions.condition_id', $productId)
            ->where('tax_rule_conditions.is_excluded', false)
            ->orderByDesc('tax_rules.priority')
            ->first();

        if ($prodRule) return self::formatRuleResponse($prodRule);

        $categoryIds = array_filter([(int)($product->category_id ?? 0), (int)($product->sub_category_id ?? 0)]);
        if (Schema::hasTable('product_category')) {
            $pivotCats = DB::table('product_category')->where('product_id', $productId)->pluck('category_id')->toArray();
            $categoryIds = array_unique(array_merge($categoryIds, array_map('intval', $pivotCats)));
        }

        if (!empty($categoryIds)) {
            $catRule = (clone $baseRulesQuery)
                ->join('tax_rule_conditions', 'tax_rules.id', '=', 'tax_rule_conditions.tax_rule_id')
                ->where('tax_rule_conditions.condition_type', 'category')
                ->whereIn('tax_rule_conditions.condition_id', $categoryIds)
                ->where('tax_rule_conditions.is_excluded', false)
                ->orderByDesc('tax_rules.priority')
                ->first();

            if ($catRule) return self::formatRuleResponse($catRule);
        }

        if ($branchId) {
            $branchRule = (clone $baseRulesQuery)
                ->join('tax_rule_conditions', 'tax_rules.id', '=', 'tax_rule_conditions.tax_rule_id')
                ->where('tax_rule_conditions.condition_type', 'branch')
                ->where('tax_rule_conditions.condition_id', $branchId)
                ->where('tax_rule_conditions.is_excluded', false)
                ->orderByDesc('tax_rules.priority')
                ->first();

            if ($branchRule) return self::formatRuleResponse($branchRule);
        }

        return self::resolveGlobalTaxRule();
    }

    /**
     * Delivery tax rule resolver
     */
    public static function resolveDeliveryTaxRule(?string $district = null, ?int $zoneId = null, ?int $branchId = null): array
    {
        if (Schema::hasTable('tax_rules') && Schema::hasTable('tax_rule_conditions')) {
            $now = Carbon::now()->toDateString();
            $rule = DB::table('tax_rules')
                ->leftJoin('tax_classes', 'tax_rules.tax_class_id', '=', 'tax_classes.id')
                ->join('tax_rule_conditions', 'tax_rules.id', '=', 'tax_rule_conditions.tax_rule_id')
                ->where('tax_rules.is_active', true)
                ->where('tax_rule_conditions.condition_type', 'delivery_charge')
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
                ->select('tax_rules.*', 'tax_classes.code as class_code')
                ->orderByDesc('tax_rules.priority')
                ->first();

            if ($rule) {
                return self::formatRuleResponse($rule);
            }
        }

        // Fallback to Central Default VAT rate if setting allows shipping VAT
        if (Schema::hasTable('settings')) {
            $rawSettings = DB::table('settings')->whereIn('key', ['default_vat_rate', 'default_tax_calculation_type'])->pluck('value', 'key')->toArray();
            $defRate = isset($rawSettings['default_vat_rate']) ? (float)$rawSettings['default_vat_rate'] : 5.00;
            $defCalc = ($rawSettings['default_tax_calculation_type'] ?? 'exclusive') === 'inclusive' ? 'inclusive' : 'exclusive';

            if ($defRate > 0) {
                return [
                    'rule_id'          => null,
                    'rule_name'        => 'Standard Shipping VAT (' . (float)$defRate . '%)',
                    'class_code'       => 'SHIPPING_DEFAULT',
                    'tax_type'         => 'percentage',
                    'rate'             => $defRate,
                    'fixed_amount'     => 0.00,
                    'calculation_type' => $defCalc,
                    'status_type'      => 'taxable',
                    'priority'         => 0,
                    'applied_from'     => 'settings_default',
                ];
            }
        }

        return self::emptyRuleResponse();
    }

    /**
     * Global tax rule fallback (Rules Table > Central Settings Default 5%)
     */
    protected static function resolveGlobalTaxRule(): array
    {
        if (Schema::hasTable('tax_rules')) {
            $now = Carbon::now()->toDateString();
            $globalQuery = DB::table('tax_rules')
                ->leftJoin('tax_classes', 'tax_rules.tax_class_id', '=', 'tax_classes.id')
                ->where('tax_rules.is_active', true)
                ->where(function ($q) use ($now) {
                    if (Schema::hasColumn('tax_rules', 'valid_from')) {
                        $q->whereNull('tax_rules.valid_from')->orWhere('tax_rules.valid_from', '<=', $now);
                    }
                })
                ->where(function ($q) use ($now) {
                    if (Schema::hasColumn('tax_rules', 'valid_to')) {
                        $q->whereNull('tax_rules.valid_to')->orWhere('tax_rules.valid_to', '>=', $now);
                    }
                });

            if (Schema::hasTable('tax_rule_conditions')) {
                $globalQuery->where(function ($q) {
                    $q->whereExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('tax_rule_conditions')
                            ->whereColumn('tax_rule_conditions.tax_rule_id', 'tax_rules.id')
                            ->where('tax_rule_conditions.condition_type', 'global')
                            ->where('tax_rule_conditions.is_excluded', false);
                    })->orWhereNotExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('tax_rule_conditions')
                            ->whereColumn('tax_rule_conditions.tax_rule_id', 'tax_rules.id');
                    });
                });
            }

            $rule = $globalQuery->select('tax_rules.*', 'tax_classes.code as class_code')
                ->orderByDesc('tax_rules.priority')
                ->first();

            if ($rule) {
                return self::formatRuleResponse($rule);
            }
        }

        // Settings Fallback: If no rule in tax_rules, fallback to Central Settings Default VAT (5%)
        if (Schema::hasTable('settings')) {
            $rawSettings = DB::table('settings')->whereIn('key', [
                'default_vat_rate', 
                'default_tax_calculation_type', 
                'vat_label'
            ])->pluck('value', 'key')->toArray();

            $defRate = isset($rawSettings['default_vat_rate']) ? (float)$rawSettings['default_vat_rate'] : 5.00;
            $defCalc = ($rawSettings['default_tax_calculation_type'] ?? 'exclusive') === 'inclusive' ? 'inclusive' : 'exclusive';
            $vatLabel = $rawSettings['vat_label'] ?? 'VAT';

            if ($defRate > 0) {
                return [
                    'rule_id'          => null,
                    'rule_name'        => 'Standard ' . (float)$defRate . '% ' . $vatLabel,
                    'class_code'       => 'DEFAULT_SETTING',
                    'tax_type'         => 'percentage',
                    'rate'             => $defRate,
                    'fixed_amount'     => 0.00,
                    'calculation_type' => $defCalc,
                    'status_type'      => 'taxable',
                    'priority'         => 0,
                    'applied_from'     => 'settings_default',
                ];
            }
        }

        return self::emptyRuleResponse();
    }

    /**
     * Save tax snapshot to database (Permanently writes to orders & order_items for Dashboard sync)
     */
    public static function recordOrderTaxSnapshot(int $orderId, array $taxCalculation): void
    {
        if ($orderId <= 0 || !Schema::hasTable('orders')) {
            return;
        }

        DB::transaction(function () use ($orderId, $taxCalculation) {
            $orderUpdate = [];

            $totalTax    = $taxCalculation['total_tax_amount'] ?? ($taxCalculation['total_tax'] ?? 0.00);
            $productTax  = $taxCalculation['product_tax_amount'] ?? ($taxCalculation['product_tax'] ?? $totalTax);
            $shippingTax = $taxCalculation['shipping_tax_amount'] ?? ($taxCalculation['shipping_tax'] ?? 0.00);
            $taxableBase = $taxCalculation['taxable_product_base'] ?? ($taxCalculation['taxable_base'] ?? 0.00);
            $isInclusive = (bool)($taxCalculation['is_tax_inclusive'] ?? ($taxCalculation['is_inclusive'] ?? false));

            if (Schema::hasColumn('orders', 'tax_amount')) {
                $orderUpdate['tax_amount'] = (float)$totalTax;
            }
            if (Schema::hasColumn('orders', 'tax')) {
                $orderUpdate['tax'] = (float)$totalTax;
            }
            if (Schema::hasColumn('orders', 'total_tax')) {
                $orderUpdate['total_tax'] = (float)$totalTax;
            }
            if (Schema::hasColumn('orders', 'product_tax_amount')) {
                $orderUpdate['product_tax_amount'] = (float)$productTax;
            }
            if (Schema::hasColumn('orders', 'shipping_tax_amount')) {
                $orderUpdate['shipping_tax_amount'] = (float)$shippingTax;
            }
            if (Schema::hasColumn('orders', 'service_tax_amount')) {
                $orderUpdate['service_tax_amount'] = (float)($taxCalculation['service_tax_amount'] ?? 0.00);
            }
            if (Schema::hasColumn('orders', 'taxable_base_amount')) {
                $orderUpdate['taxable_base_amount'] = (float)$taxableBase;
            }
            if (Schema::hasColumn('orders', 'is_tax_inclusive')) {
                $orderUpdate['is_tax_inclusive'] = $isInclusive;
            }
            if (Schema::hasColumn('orders', 'tax_breakdown')) {
                $orderUpdate['tax_breakdown'] = json_encode($taxCalculation['breakdown'] ?? ($taxCalculation['items'] ?? []));
            }
            if (Schema::hasColumn('orders', 'tax_details_snapshot')) {
                $orderUpdate['tax_details_snapshot'] = json_encode([
                    'summary'   => $taxCalculation['snapshot'] ?? ($taxCalculation['breakdown'] ?? []),
                    'items_tax' => $taxCalculation['items'] ?? [],
                ]);
            }

            if (!empty($orderUpdate)) {
                DB::table('orders')->where('id', $orderId)->update($orderUpdate);
            }

            $itemsTable = Schema::hasTable('order_items') ? 'order_items' : (Schema::hasTable('order_details') ? 'order_details' : null);

            if ($itemsTable && !empty($taxCalculation['items'])) {
                foreach ($taxCalculation['items'] as $itemTax) {
                    $pId = $itemTax['product_id'] ?? ($itemTax['id'] ?? null);
                    $vId = $itemTax['variant_id'] ?? null;
                    $rowId = $itemTax['order_item_id'] ?? ($itemTax['item_id'] ?? null);

                    $itemQuery = DB::table($itemsTable);
                    if ($rowId) {
                        $itemQuery->where('id', $rowId);
                    } else {
                        if (!$pId) continue;
                        $itemQuery->where('order_id', $orderId)->where('product_id', $pId);
                        if ($vId && Schema::hasColumn($itemsTable, 'variant_id')) {
                            $itemQuery->where('variant_id', $vId);
                        }
                    }

                    $itemRowUpdate = [];
                    if (Schema::hasColumn($itemsTable, 'tax_rule_id')) {
                        $itemRowUpdate['tax_rule_id'] = $itemTax['tax_rule_id'] ?? ($itemTax['rule_id'] ?? null);
                    }
                    if (Schema::hasColumn($itemsTable, 'tax_class')) {
                        $itemRowUpdate['tax_class'] = $itemTax['tax_class'] ?? ($itemTax['class_id'] ?? 'STANDARD');
                    }
                    if (Schema::hasColumn($itemsTable, 'tax_rate')) {
                        $itemRowUpdate['tax_rate'] = (float)($itemTax['tax_rate'] ?? 0.0000);
                    }
                    if (Schema::hasColumn($itemsTable, 'tax_amount')) {
                        $itemRowUpdate['tax_amount'] = (float)($itemTax['tax_amount'] ?? 0.00);
                    }
                    if (Schema::hasColumn($itemsTable, 'is_tax_inclusive')) {
                        $itemRowUpdate['is_tax_inclusive'] = (bool)($itemTax['is_inclusive'] ?? ($itemTax['is_tax_inclusive'] ?? false));
                    }
                    if (Schema::hasColumn($itemsTable, 'is_inclusive')) {
                        $itemRowUpdate['is_inclusive'] = (bool)($itemTax['is_inclusive'] ?? ($itemTax['is_tax_inclusive'] ?? false));
                    }
                    if (Schema::hasColumn($itemsTable, 'taxable_base_amount')) {
                        $itemRowUpdate['taxable_base_amount'] = (float)($itemTax['calculated_base'] ?? ($itemTax['net_taxable_base'] ?? ($itemTax['taxable_base_amount'] ?? 0.00)));
                    }

                    if (!empty($itemRowUpdate)) {
                        $itemQuery->update($itemRowUpdate);
                    }
                }
            }
        });
    }

    /**
     * Adjust tax on order cancellation or return
     */
    public static function adjustTaxForReturnOrCancel(
        int $orderId,
        string $adjustmentType,
        float $refundedAmount,
        string $reason = '',
        ?int $adminId = null
    ): array {
        if (!Schema::hasTable('tax_adjustments') || !Schema::hasTable('orders')) {
            return ['success' => false, 'message' => 'Tax adjustment table missing'];
        }

        return DB::transaction(function () use ($orderId, $adjustmentType, $refundedAmount, $reason, $adminId) {
            $order = DB::table('orders')->where('id', $orderId)->first();
            if (!$order) {
                return ['success' => false, 'message' => 'Order not found'];
            }

            $originalTax = (float)($order->tax_amount ?? ($order->product_tax_amount ?? ($order->tax ?? 0.00)));
            $grandTotal = max(1.0, (float)($order->grand_total ?? 1.0));

            $refundRatio = min(1.0, max(0.0, $refundedAmount / $grandTotal));
            $taxToReverse = round($originalTax * $refundRatio, 2);

            $existingAdjusted = (float)DB::table('tax_adjustments')
                ->where('order_id', $orderId)
                ->where('adjustment_type', $adjustmentType)
                ->sum('net_tax_change');

            $taxToReverse = max(0.0, min($taxToReverse, $originalTax - $existingAdjusted));

            if ($taxToReverse <= 0.0) {
                return ['success' => true, 'reversed_tax' => 0.00, 'message' => 'No outstanding tax to adjust'];
            }

            DB::table('tax_adjustments')->insert([
                'order_id'            => $orderId,
                'adjustment_type'     => $adjustmentType,
                'original_tax_amount' => $originalTax,
                'adjusted_tax_amount' => round($originalTax - $taxToReverse, 2),
                'net_tax_change'      => $taxToReverse,
                'reference_no'        => 'TAX-ADJ-' . $orderId . '-' . strtoupper(substr(md5(uniqid()), 0, 6)),
                'reason'              => $reason ?: 'Order ' . $adjustmentType . ' tax reversal',
                'created_by'          => $adminId ?? auth()->id(),
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            return [
                'success'      => true,
                'reversed_tax' => $taxToReverse,
                'message'      => 'Tax adjustment recorded successfully',
            ];
        });
    }

    protected static function formatRuleResponse(object $rule): array
    {
        $statusType = $rule->status_type ?? 'taxable';
        $rate = ($statusType === 'exempt' || $statusType === 'zero_rated') ? 0.0 : (float)($rule->rate ?? 0.0);
        $fixed = ($statusType === 'exempt' || $statusType === 'zero_rated') ? 0.0 : (float)($rule->fixed_amount ?? 0.0);

        if ($rate == 0 && $fixed == 0 && $statusType === 'taxable') {
            $statusType = 'zero_rated';
        }

        return [
            'rule_id'          => $rule->id,
            'rule_name'        => $rule->name,
            'class_code'       => $rule->class_code ?? 'STANDARD',
            'tax_type'         => $rule->tax_type ?? 'percentage',
            'rate'             => $rate,
            'fixed_amount'     => $fixed,
            'calculation_type' => $rule->calculation_type ?? 'exclusive',
            'status_type'      => $statusType,
            'priority'         => (int)($rule->priority ?? 1),
            'applied_from'     => 'branch_or_rule',
        ];
    }

    protected static function emptyRuleResponse(): array
    {
        return [
            'rule_id'          => null,
            'rule_name'        => 'No Tax / Zero VAT',
            'class_code'       => 'NO_TAX',
            'tax_type'         => 'percentage',
            'rate'             => 0.0000,
            'fixed_amount'     => 0.00,
            'calculation_type' => 'exclusive',
            'status_type'      => 'exempt',
            'priority'         => 0,
            'applied_from'     => 'none',
        ];
    }
}