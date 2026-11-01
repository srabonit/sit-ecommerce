<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ManualPaymentMethod;
use App\Models\ShippingZone;
use App\Models\ShippingClassRate;
use App\Models\ShippingZoneMethod;
use App\Models\Product;
use App\Models\EmailAbandonedCart;
use App\Models\EmailSubscriber;
use App\Models\EmailConsentLog;
use App\Models\EmailSuppressionList;
use App\Models\SmsSetting;
use App\Mail\OrderPlacedMail;
use App\Mail\AdminOrderNotificationMail;
use App\Services\DigitalProductService;
use App\Services\PaymentTransactionService;
use App\Services\ImagePathService;
use App\Services\InventoryService;
use App\Services\DiscountEngineService;
use App\Services\TaxEngineService;
use App\Services\Sms\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class CheckoutController extends Controller
{
    /**
     * Resolve the items table name safely (Supports order_items & order_details).
     */
    protected function resolveItemsTable(): string
    {
        if (Schema::hasTable('order_items')) {
            return 'order_items';
        }
        if (Schema::hasTable('order_details')) {
            return 'order_details';
        }
        return 'order_items';
    }

    /**
     * Universal Upload Helper for Checkout across all public roots & auto-registers in Media Library
     */
    protected function saveCheckoutFile($file, $subFolder): string
    {
        $originalName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $cleanBase = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'proof';
        $filename = time() . '_' . Str::random(8) . '_' . $cleanBase . '.' . $ext;
        $relativeDir = trim($subFolder, '/');
        $relativePath = 'uploads/' . $relativeDir . '/' . $filename;

        $primaryRoot = ImagePathService::resolvePublicRoot();
        $primaryDir = $primaryRoot . '/uploads/' . $relativeDir;
        if (!is_dir($primaryDir)) {
            @mkdir($primaryDir, 0777, true);
        }

        $primaryFullPath = $primaryDir . '/' . $filename;
        $file->move($primaryDir, $filename);

        // Mirror to all other candidate public roots (cPanel and XAMPP compatibility)
        foreach (ImagePathService::getAllPublicRoots() as $root) {
            $mirrorDir = $root . '/uploads/' . $relativeDir;
            $mirrorFile = $mirrorDir . '/' . $filename;
            if ($mirrorFile !== $primaryFullPath) {
                if (!is_dir($mirrorDir)) {
                    @mkdir($mirrorDir, 0777, true);
                }
                @copy($primaryFullPath, $mirrorFile);
            }
        }

        try {
            $storageDir = storage_path('app/public/' . $relativeDir);
            if (!is_dir($storageDir)) {
                @mkdir($storageDir, 0777, true);
            }
            @copy($primaryFullPath, $storageDir . '/' . $filename);
        } catch (\Throwable $e) {
            // ignore safely
        }

        // Auto-register user submitted file in media_files table
        if (Schema::hasTable('media_files')) {
            try {
                $mimeType = @mime_content_type($primaryFullPath) ?: 'image/jpeg';
                $fileSize = @filesize($primaryFullPath) ?: 0;
                $imgInfo  = @getimagesize($primaryFullPath);
                $dims     = $imgInfo ? ($imgInfo[0] . ' × ' . $imgInfo[1]) : null;

                $mediaData = [
                    'file_name'  => $originalName,
                    'file_path'  => $relativePath,
                    'file_type'  => $mimeType,
                    'file_size'  => $fileSize,
                    'dimensions' => $dims,
                    'title'      => 'Payment Proof - ' . $originalName,
                    'alt_text'   => 'Payment Proof',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('media_files', 'media_category')) {
                    $mediaData['media_category'] = 'user_submitted';
                }
                if (Schema::hasColumn('media_files', 'source_module')) {
                    $mediaData['source_module'] = 'payment_proof';
                }
                if (Schema::hasColumn('media_files', 'file_hash')) {
                    $mediaData['file_hash'] = @hash_file('sha256', $primaryFullPath) ?: md5($relativePath);
                }
                if (Schema::hasColumn('media_files', 'uploaded_by')) {
                    $mediaData['uploaded_by'] = auth()->id() ?? null;
                }

                DB::table('media_files')->insert($mediaData);
            } catch (\Throwable $e) {
                // ignore safely
            }
        }

        return $relativePath;
    }

    public static function getAllDistricts()
    {
        $districtsList = [];
        $zones = ShippingZone::with('locations')->orderBy('id', 'desc')->get();

        foreach ($zones as $zone) {
            foreach ($zone->locations as $loc) {
                if (!empty($loc->districts)) {
                    $split = array_map('trim', explode(',', $loc->districts));
                    foreach ($split as $d) {
                        if (!empty($d) && !in_array($d, $districtsList)) {
                            $districtsList[] = $d;
                        }
                    }
                }
            }
        }

        if (empty($districtsList)) {
            $districtsList = ['Dhaka', 'Gazipur', 'Narayanganj', 'Chattogram', 'Sylhet', 'Rajshahi', 'Khulna', 'Barishal', 'Rangpur', 'Mymensingh', 'Other Districts'];
        }

        sort($districtsList);
        return $districtsList;
    }

    /**
     * Calculate dynamic item-wise and shipping tax according to NBR compliance & priority cascade
     */
    public function calculateOrderTax($cart, float $deliveryCharge = 0.0, float $totalDiscount = 0.0, ?string $district = null, ?int $userId = null): array
    {
        // 1. Check Global Settings Switch first
        $isVatEnabled = false;
        if (class_exists(TaxEngineService::class) && method_exists(TaxEngineService::class, 'isVatEnabled')) {
            $isVatEnabled = TaxEngineService::isVatEnabled();
        } elseif (Schema::hasTable('settings')) {
            $setting = DB::table('settings')->where('key', 'vat_enabled')->value('value');
            if ($setting === null) {
                $setting = DB::table('settings')->where('key', 'enable_vat')->value('value');
            }
            $isVatEnabled = filter_var($setting ?? false, FILTER_VALIDATE_BOOLEAN);
        }

        $totalSubtotal = 0.0;
        $formattedCart = [];

        foreach ($cart as $key => $item) {
            $rawPrice = $item['price'] ?? 0;
            if (is_string($rawPrice)) {
                $rawPrice = preg_replace('/[^0-9.]/', '', $rawPrice);
            }
            $price = (float) $rawPrice;
            $qty = (int)($item['quantity'] ?? ($item['qty'] ?? 1));

            $productId = !empty($item['product_id']) ? (int)$item['product_id'] : (!empty($item['id']) && is_numeric($item['id']) ? (int)$item['id'] : (is_numeric($key) ? (int)$key : (int)explode('_', (string)$key)[0]));
            $variantId = !empty($item['variant_id']) ? (int)$item['variant_id'] : null;

            $totalSubtotal += ($price * $qty);

            $formattedCart[$key] = [
                'id'         => $productId,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'price'      => $price,
                'quantity'   => $qty,
                'title'      => $item['title'] ?? ($item['name'] ?? 'Product'),
            ];
        }

        // If Global VAT Switch is OFF, all calculations are strictly 0.00
        if (!$isVatEnabled) {
            $emptyItems = [];
            foreach ($cart as $cartKey => $item) {
                $pId = !empty($item['product_id']) ? (int)$item['product_id'] : (!empty($item['id']) && is_numeric($item['id']) ? (int)$item['id'] : (is_numeric($cartKey) ? (int)$cartKey : (int)explode('_', (string)$cartKey)[0]));
                $rawPrice = $item['price'] ?? 0;
                if (is_string($rawPrice)) {
                    $rawPrice = preg_replace('/[^0-9.]/', '', $rawPrice);
                }
                $price = (float) $rawPrice;
                $qty = (int)($item['quantity'] ?? 1);

                $emptyItems[$cartKey] = [
                    'product_id'          => $pId,
                    'variant_id'          => !empty($item['variant_id']) ? (int)$item['variant_id'] : null,
                    'rule_id'             => null,
                    'class_id'            => null,
                    'tax_rate'            => 0.0,
                    'tax_amount'          => 0.0,
                    'taxable_base_amount' => $price * $qty,
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
                'total_tax_amount'      => 0.00,
                'product_tax'           => 0.00,
                'product_tax_amount'    => 0.00,
                'shipping_tax'          => 0.00,
                'shipping_tax_amount'   => 0.00,
                'shipping_tax_rate'     => 0.00,
                'taxable_base'          => $totalSubtotal,
                'is_inclusive'          => false,
                'is_tax_inclusive'      => false,
                'exclusive_tax'         => 0.00,
                'product_exclusive_tax' => 0.00,
                'items'                 => $emptyItems,
                'breakdown'             => [
                    'product_tax'          => 0.00,
                    'shipping_tax'         => 0.00,
                    'exclusive_tax'        => 0.00,
                    'is_product_inclusive' => false,
                    'is_shipping_inclusive'=> false,
                ],
                'raw_tax_engine_result' => [],
            ];
        }

        // 2. Delegate to Central TaxEngineService (Main Source of Truth)
        if (class_exists(TaxEngineService::class) && method_exists(TaxEngineService::class, 'calculateOrderTax')) {
            try {
                $engineResult = TaxEngineService::calculateOrderTax(
                    $formattedCart,
                    $totalSubtotal,
                    $totalDiscount,
                    $deliveryCharge,
                    null,
                    $district,
                    null,
                    $userId
                );

                $productTax   = (float)($engineResult['product_tax_amount'] ?? 0.0);
                $shippingTax  = (float)($engineResult['shipping_tax_amount'] ?? 0.0);
                $totalTax     = (float)($engineResult['total_tax_amount'] ?? 0.0);
                $exclusiveTax = (float)($engineResult['exclusive_tax'] ?? (($engineResult['product_exclusive_tax'] ?? 0.0) + (($engineResult['shipping_tax_type'] ?? '') === 'exclusive' ? $shippingTax : 0.0)));

                // Map items key-compatibility for cart and order items
                $mappedItems = [];
                foreach ($cart as $cartKey => $cartItem) {
                    $cProductId = !empty($cartItem['product_id']) ? (int)$cartItem['product_id'] : (!empty($cartItem['id']) && is_numeric($cartItem['id']) ? (int)$cartItem['id'] : (is_numeric($cartKey) ? (int)$cartKey : (int)explode('_', (string)$cartKey)[0]));
                    $cVariantId = !empty($cartItem['variant_id']) ? (int)$cartItem['variant_id'] : null;

                    $matchedEngineItem = null;
                    if (!empty($engineResult['items'])) {
                        foreach ($engineResult['items'] as $engItem) {
                            if (($engItem['product_id'] ?? null) == $cProductId && ($engItem['variant_id'] ?? null) == $cVariantId) {
                                $matchedEngineItem = $engItem;
                                break;
                            }
                        }
                    }

                    $mappedItems[$cartKey] = [
                        'product_id'          => $cProductId,
                        'variant_id'          => $cVariantId,
                        'rule_id'             => $matchedEngineItem['tax_rule_id'] ?? null,
                        'tax_rule_id'         => $matchedEngineItem['tax_rule_id'] ?? null,
                        'class_id'            => $matchedEngineItem['tax_class'] ?? 'STANDARD',
                        'tax_class'           => $matchedEngineItem['tax_class'] ?? 'STANDARD',
                        'tax_rate'            => (float)($matchedEngineItem['tax_rate'] ?? 0.0),
                        'tax_amount'          => (float)($matchedEngineItem['tax_amount'] ?? 0.0),
                        'taxable_base_amount' => (float)($matchedEngineItem['calculated_base'] ?? ($matchedEngineItem['net_taxable_base'] ?? 0.0)),
                        'calculated_base'     => (float)($matchedEngineItem['calculated_base'] ?? ($matchedEngineItem['net_taxable_base'] ?? 0.0)),
                        'is_tax_inclusive'    => (bool)($matchedEngineItem['is_inclusive'] ?? false),
                        'is_inclusive'        => (bool)($matchedEngineItem['is_inclusive'] ?? false),
                        'status_type'         => $matchedEngineItem['status_type'] ?? 'taxable',
                        'breakdown'           => [
                            'rule_name' => $matchedEngineItem['tax_rule_name'] ?? 'VAT Rule',
                            'rule_code' => $matchedEngineItem['tax_class'] ?? 'STANDARD',
                            'rate'      => (float)($matchedEngineItem['tax_rate'] ?? 0.0),
                            'amount'    => (float)($matchedEngineItem['tax_amount'] ?? 0.0),
                            'type'      => !empty($matchedEngineItem['is_inclusive']) ? 'inclusive' : 'exclusive',
                        ],
                    ];
                }

                return [
                    'is_vat_enabled'        => true,
                    'total_tax'             => $totalTax,
                    'total_tax_amount'      => $totalTax,
                    'product_tax'           => $productTax,
                    'product_tax_amount'    => $productTax,
                    'shipping_tax'          => $shippingTax,
                    'shipping_tax_amount'   => $shippingTax,
                    'shipping_tax_rate'     => (float)($engineResult['shipping_tax_rate'] ?? 0.0),
                    'taxable_base'          => (float)($engineResult['taxable_product_base'] ?? $totalSubtotal),
                    'is_inclusive'          => (bool)($engineResult['is_tax_inclusive'] ?? false),
                    'is_tax_inclusive'      => (bool)($engineResult['is_tax_inclusive'] ?? false),
                    'exclusive_tax'         => $exclusiveTax,
                    'product_exclusive_tax' => (float)($engineResult['product_exclusive_tax'] ?? 0.0),
                    'items'                 => $mappedItems,
                    'breakdown'             => [
                        'product_tax'          => $productTax,
                        'shipping_tax'         => $shippingTax,
                        'exclusive_tax'        => $exclusiveTax,
                        'is_product_inclusive' => (bool)($engineResult['is_tax_inclusive'] ?? false),
                        'is_shipping_inclusive'=> (($engineResult['shipping_tax_type'] ?? '') === 'inclusive'),
                    ],
                    'raw_tax_engine_result' => $engineResult,
                ];
            } catch (\Throwable $e) {
                \Log::warning('TaxEngineService calculation fallback: ' . $e->getMessage());
            }
        }

        // 3. Complete Hierarchy Fallback (Product Specific > Category Override > Rules > Default Settings)
        $discountRatio = ($totalSubtotal > 0 && $totalDiscount > 0) ? min(1.0, $totalDiscount / $totalSubtotal) : 0.0;

        $itemsTax = [];
        $totalProductTax = 0.0;
        $totalExclusiveProductTax = 0.0;
        $totalTaxableBase = 0.0;
        $hasInclusive = false;
        $hasExclusive = false;

        $defaultCalcType = 'exclusive';
        if (Schema::hasTable('settings')) {
            $defaultCalcType = DB::table('settings')->where('key', 'default_tax_calculation_type')->value('value') ?? 'exclusive';
        }

        $taxRules = collect();
        if (Schema::hasTable('tax_rules')) {
            $ruleQuery = DB::table('tax_rules')->where('is_active', true);
            if (Schema::hasColumn('tax_rules', 'valid_from')) {
                $ruleQuery->where(function ($q) {
                    $q->whereNull('valid_from')->orWhere('valid_from', '<=', now()->toDateString());
                });
            }
            if (Schema::hasColumn('tax_rules', 'valid_to')) {
                $ruleQuery->where(function ($q) {
                    $q->whereNull('valid_to')->orWhere('valid_to', '>=', now()->toDateString());
                });
            }
            $taxRules = $ruleQuery->orderByDesc('priority')->orderBy('id')->get();
        }

        $ruleConditions = collect();
        if (Schema::hasTable('tax_rule_conditions')) {
            $ruleConditions = DB::table('tax_rule_conditions')->get()->groupBy('tax_rule_id');
        }

        foreach ($cart as $cartKey => $item) {
            $price = (float)preg_replace('/[^0-9.]/', '', (string)($item['price'] ?? 0));
            $qty = (int)($item['quantity'] ?? 1);
            $lineTotal = $price * $qty;

            $lineDiscount = round($lineTotal * $discountRatio, 2);
            $netLineBase = max(0.0, $lineTotal - $lineDiscount);

            $productId = !empty($item['product_id']) ? (int)$item['product_id'] : (!empty($item['id']) && is_numeric($item['id']) ? (int)$item['id'] : (is_numeric($cartKey) ? (int)$cartKey : (int)explode('_', (string)$cartKey)[0]));
            $variantId = !empty($item['variant_id']) ? (int)$item['variant_id'] : null;

            $product = Product::find($productId);
            $categoryId = $product ? (int)($product->category_id ?? 0) : 0;
            $brandId = $product ? (int)($product->brand_id ?? 0) : 0;
            $productTaxClassId = $product ? (int)($product->tax_class_id ?? 0) : 0;

            $productVatApplied = false;
            $directVatRate = 0.0;
            $directVatAmount = 0.0;
            $directVatType = 'percent';
            $isDirectInclusive = ($defaultCalcType === 'inclusive');

            if ($product && Schema::hasColumn('products', 'vat_amount') && !is_null($product->vat_amount) && trim((string)$product->vat_amount) !== '') {
                $rawVat = (float)$product->vat_amount;
                $productVatApplied = true;
                $directVatType = strtolower($product->vat_type ?? 'percent');
                if (isset($product->tax_type) && in_array($product->tax_type, ['inclusive', 'exclusive'])) {
                    $isDirectInclusive = ($product->tax_type === 'inclusive');
                }

                if (in_array($directVatType, ['flat', 'fixed'])) {
                    $directVatAmount = round($rawVat * $qty, 2);
                    $directVatRate = ($netLineBase > 0) ? round(($directVatAmount / $netLineBase) * 100, 4) : 0.0;
                    $isDirectInclusive = false;
                } else {
                    $directVatRate = $rawVat;
                    if ($directVatRate > 0) {
                        if ($isDirectInclusive) {
                            $directVatAmount = round($netLineBase - ($netLineBase / (1 + ($directVatRate / 100))), 2);
                        } else {
                            $directVatAmount = round(($netLineBase * $directVatRate) / 100, 2);
                        }
                    } else {
                        $directVatAmount = 0.0;
                    }
                }
            }

            $categoryVatApplied = false;
            if (!$productVatApplied) {
                $subCatId = (int)($product->sub_category_id ?? 0);
                $catRecord = null;
                if ($subCatId && Schema::hasTable('sub_categories') && Schema::hasColumn('sub_categories', 'vat_amount')) {
                    $subCat = DB::table('sub_categories')->where('id', $subCatId)->first();
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
                    $categoryVatApplied = true;
                    $rawCatVat = (float)$catRecord->vat_amount;
                    $directVatType = strtolower($catRecord->vat_type ?? 'percent');
                    if (in_array($directVatType, ['flat', 'fixed'])) {
                        $directVatAmount = round($rawCatVat * $qty, 2);
                        $directVatRate = ($netLineBase > 0) ? round(($directVatAmount / $netLineBase) * 100, 4) : 0.0;
                        $isDirectInclusive = false;
                    } else {
                        $directVatRate = $rawCatVat;
                        if ($directVatRate > 0) {
                            if ($isDirectInclusive) {
                                $directVatAmount = round($netLineBase - ($netLineBase / (1 + ($directVatRate / 100))), 2);
                            } else {
                                $directVatAmount = round(($netLineBase * $directVatRate) / 100, 2);
                            }
                        } else {
                            $directVatAmount = 0.0;
                        }
                    }
                }
            }

            $taxRate = 0.0;
            $taxAmount = 0.0;
            $isInclusive = false;
            $ruleId = null;
            $classId = $productTaxClassId ?: null;
            $statusType = 'taxable';
            $calcBase = $netLineBase;

            if ($productVatApplied || $categoryVatApplied) {
                $taxRate = $directVatRate;
                $taxAmount = $directVatAmount;
                $statusType = ($directVatRate == 0 && $directVatAmount == 0) ? 'zero_rated' : 'taxable';
                $isInclusive = $isDirectInclusive;
                if ($statusType === 'taxable') {
                    if ($isInclusive) {
                        $hasInclusive = true;
                        $calcBase = round($netLineBase - $taxAmount, 2);
                    } else {
                        $hasExclusive = true;
                        $totalExclusiveProductTax += $taxAmount;
                    }
                }
            } else {
                // 3. Tax Rules Database Cascade
                $matchedRule = null;
                foreach ($taxRules as $rule) {
                    $conditions = $ruleConditions->get($rule->id, collect());
                    $applies = true;
                    $hasSpecificCondition = false;

                    if ($conditions->isNotEmpty()) {
                        foreach ($conditions as $cond) {
                            $condType = $cond->condition_type;
                            $condId = (int)$cond->condition_id;
                            $isExcluded = (bool)$cond->is_excluded;

                            $isMatch = false;
                            if ($condType === 'product' && $condId === $productId) {
                                $isMatch = true;
                            } elseif ($condType === 'category' && $condId === $categoryId) {
                                $isMatch = true;
                            } elseif ($condType === 'brand' && $condId === $brandId) {
                                $isMatch = true;
                            }

                            if ($isMatch) {
                                $hasSpecificCondition = true;
                                if ($isExcluded) {
                                    $applies = false;
                                    break;
                                }
                            }
                        }
                    }

                    if ($applies && !empty($rule->tax_class_id) && $rule->tax_class_id != $productTaxClassId) {
                        $applies = false;
                    }

                    if ($applies && ($hasSpecificCondition || $conditions->isEmpty() || !empty($rule->tax_class_id))) {
                        $matchedRule = $rule;
                        break;
                    }
                }

                if ($matchedRule) {
                    $ruleId = $matchedRule->id;
                    $classId = $matchedRule->tax_class_id ?: $classId;
                    $statusType = $matchedRule->status_type ?? 'taxable';
                    $isInclusive = ($matchedRule->calculation_type === 'inclusive');

                    if ($statusType === 'taxable') {
                        if ($matchedRule->tax_type === 'fixed') {
                            $taxAmount = (float)($matchedRule->fixed_amount ?? 0.0) * $qty;
                            $taxRate = ($netLineBase > 0) ? round(($taxAmount / $netLineBase) * 100, 4) : 0.0;
                            $isInclusive = false;
                        } else {
                            $taxRate = (float)($matchedRule->rate ?? 0.0);
                            if ($isInclusive) {
                                $taxAmount = round($netLineBase - ($netLineBase / (1 + ($taxRate / 100))), 2);
                                $calcBase = round($netLineBase - $taxAmount, 2);
                            } else {
                                $taxAmount = round(($netLineBase * $taxRate) / 100, 2);
                                $calcBase = $netLineBase;
                            }
                        }
                    } else {
                        $taxRate = 0.0;
                        $taxAmount = 0.0;
                    }
                } else {
                    // 4. Central Settings Default VAT Fallback (5%)
                    if (Schema::hasTable('settings')) {
                        $rawSettings = DB::table('settings')->whereIn('key', ['default_vat_rate', 'default_tax_calculation_type'])->pluck('value', 'key')->toArray();
                        $defRate = isset($rawSettings['default_vat_rate']) ? (float)$rawSettings['default_vat_rate'] : 5.00;
                        $defCalc = ($rawSettings['default_tax_calculation_type'] ?? 'exclusive') === 'inclusive';

                        if ($defRate > 0) {
                            $taxRate = $defRate;
                            $isInclusive = $defCalc;
                            $statusType = 'taxable';
                            if ($isInclusive) {
                                $taxAmount = round($netLineBase - ($netLineBase / (1 + ($taxRate / 100))), 2);
                                $calcBase = round($netLineBase - $taxAmount, 2);
                            } else {
                                $taxAmount = round(($netLineBase * $taxRate) / 100, 2);
                                $calcBase = $netLineBase;
                            }
                        }
                    }
                }

                if ($isInclusive) {
                    $hasInclusive = true;
                } elseif ($taxAmount > 0) {
                    $hasExclusive = true;
                    $totalExclusiveProductTax += $taxAmount;
                }
            }

            $totalProductTax += $taxAmount;
            $totalTaxableBase += $calcBase;

            $itemsTax[$cartKey] = [
                'product_id'          => $productId,
                'variant_id'          => $variantId,
                'rule_id'             => $ruleId,
                'class_id'            => $classId,
                'tax_rate'            => $taxRate,
                'tax_amount'          => $taxAmount,
                'taxable_base_amount' => $calcBase,
                'is_tax_inclusive'    => $isInclusive,
                'status_type'         => $statusType,
                'breakdown'           => [
                    'rule_name' => $productVatApplied ? 'Product VAT' : ($categoryVatApplied ? 'Category VAT Override' : ($matchedRule->name ?? 'Standard VAT')),
                    'rule_code' => $productVatApplied ? 'PRODUCT_VAT' : ($categoryVatApplied ? 'CATEGORY_VAT' : ($matchedRule->code ?? 'DEFAULT')),
                    'rate'      => $taxRate,
                    'amount'    => $taxAmount,
                    'type'      => $isInclusive ? 'inclusive' : 'exclusive',
                ],
            ];
        }

        $shippingTax = 0.0;
        $shippingTaxRate = 0.0;
        $isShippingInclusive = false;
        $exclusiveShippingTax = 0.0;

        $applyShippingVat = false;
        if (Schema::hasTable('settings')) {
            $shippingVatSetting = DB::table('settings')->where('key', 'apply_vat_on_shipping')->value('value');
            $applyShippingVat = filter_var($shippingVatSetting ?? false, FILTER_VALIDATE_BOOLEAN);
        }

        if ($applyShippingVat && $deliveryCharge > 0) {
            $shippingRule = null;
            foreach ($taxRules as $rule) {
                $conditions = $ruleConditions->get($rule->id, collect());
                foreach ($conditions as $cond) {
                    if ($cond->condition_type === 'delivery_charge' || $cond->condition_type === 'shipping_zone') {
                        $shippingRule = $rule;
                        break 2;
                    }
                }
            }

            if ($shippingRule && ($shippingRule->status_type ?? 'taxable') === 'taxable') {
                $shippingTaxRate = (float)($shippingRule->rate ?? 0.0);
                $isShippingInclusive = ($shippingRule->calculation_type === 'inclusive');

                if ($shippingRule->tax_type === 'fixed') {
                    $shippingTax = (float)($shippingRule->fixed_amount ?? 0.0);
                } else {
                    if ($isShippingInclusive) {
                        $shippingTax = round($deliveryCharge - ($deliveryCharge / (1 + ($shippingTaxRate / 100))), 2);
                    } else {
                        $shippingTax = round(($deliveryCharge * $shippingTaxRate) / 100, 2);
                    }
                }

                if (!$isShippingInclusive) {
                    $exclusiveShippingTax = $shippingTax;
                }
            }
        }

        $totalTax = round($totalProductTax + $shippingTax, 2);
        $totalExclusiveTax = round($totalExclusiveProductTax + $exclusiveShippingTax, 2);

        return [
            'is_vat_enabled'        => true,
            'total_tax'             => $totalTax,
            'total_tax_amount'      => $totalTax,
            'product_tax'           => round($totalProductTax, 2),
            'product_tax_amount'    => round($totalProductTax, 2),
            'shipping_tax'          => round($shippingTax, 2),
            'shipping_tax_amount'   => round($shippingTax, 2),
            'shipping_tax_rate'     => $shippingTaxRate,
            'taxable_base'          => round($totalTaxableBase, 2),
            'is_inclusive'          => $hasInclusive && !$hasExclusive,
            'is_tax_inclusive'      => $hasInclusive && !$hasExclusive,
            'exclusive_tax'         => $totalExclusiveTax,
            'product_exclusive_tax' => round($totalExclusiveProductTax, 2),
            'items'                 => $itemsTax,
            'breakdown'             => [
                'product_tax'          => round($totalProductTax, 2),
                'shipping_tax'         => round($shippingTax, 2),
                'exclusive_tax'        => $totalExclusiveTax,
                'is_product_inclusive' => $hasInclusive,
                'is_shipping_inclusive'=> $isShippingInclusive,
            ],
            'raw_tax_engine_result' => [],
        ];
    }

    public function index()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty!');
        }

        $cartModified = false;
        $hasPhysical = false;
        $hasDigital = false;
        $subtotal = 0.00;

        foreach ($cart as $id => &$item) {
            $rawPrice = $item['price'] ?? 0;
            if (is_string($rawPrice)) {
                $rawPrice = preg_replace('/[^0-9.]/', '', $rawPrice);
            }
            $price = (float) $rawPrice;
            $qty = (int) ($item['quantity'] ?? 1);

            $productId = !empty($item['product_id']) ? (int)$item['product_id'] : (!empty($item['id']) && is_numeric($item['id']) ? (int)$item['id'] : (is_numeric($id) ? (int)$id : (int)explode('_', (string)$id)[0]));
            $prod = Product::find($productId);
            
            $isDownloadable = !empty($item['is_downloadable']) 
                || ($prod && (!empty($prod->is_downloadable) || !empty($prod->downloadable) || str_contains(strtolower($prod->product_type ?? ''), 'download')));

            $isItemDigital = ($item['is_digital'] ?? false) 
                || ($prod && ($prod->is_virtual || $prod->is_downloadable || in_array(strtolower($prod->product_type ?? ''), ['virtual', 'downloadable', 'license'])));
            
            if ($isDownloadable && ($item['quantity'] ?? 1) > 1) {
                $item['quantity'] = 1;
                $qty = 1;
                $cartModified = true;
            }

            $subtotal += ($price * $qty);

            if ($isItemDigital) {
                $hasDigital = true;
            } else {
                $hasPhysical = true;
            }
        }
        unset($item);

        if ($cartModified) {
            session()->put('cart', $cart);
        }

        $isMixed = ($hasPhysical && $hasDigital);

        $districts = self::getAllDistricts();
        $defaultDistrict = in_array('Dhaka', $districts) ? 'Dhaka' : ($districts[0] ?? 'Dhaka');
        $initialShipping = $this->calculateDynamicShipping($defaultDistrict, $cart);

        // কুপন ও ভাউচার ক্যালকুলেশন সিঙ্ক
        $appliedCoupon = Session::get('applied_coupon');
        $appliedVoucher = Session::get('applied_voucher');
        $couponCode = is_array($appliedCoupon) ? ($appliedCoupon['code'] ?? null) : $appliedCoupon;
        $voucherCode = is_array($appliedVoucher) ? ($appliedVoucher['code'] ?? ($appliedVoucher['voucher_code'] ?? null)) : $appliedVoucher;

        $calc = DiscountEngineService::calculateCartDiscounts(
            $subtotal,
            $cart,
            $couponCode,
            $voucherCode,
            Auth::id(),
            (float)($initialShipping['cost'] ?? 0.0)
        );

        // NBR Tax Engine Integration
        $taxCalc = $this->calculateOrderTax(
            $cart,
            (float)($calc['adjusted_delivery_charge'] ?? ($initialShipping['cost'] ?? 0.0)),
            (float)($calc['total_discount'] ?? 0.0),
            $defaultDistrict,
            Auth::id()
        );

        $calc['final_payable'] = round($calc['final_payable'] + ($taxCalc['exclusive_tax'] ?? 0.0), 2);

        $manualPayments = [];
        if (Schema::hasTable('manual_payment_methods')) {
            $manualPayments = DB::table('manual_payment_methods')->where('is_active', true)->get();
        }

        $gateways = [];
        if (Schema::hasTable('payment_gateways')) {
            $gateways = DB::table('payment_gateways')->where('is_active', true)->get();
        }

        // কাস্টমারের নিজস্ব সক্রিয় ভাউচার ওয়ালেট
        $userVouchers = collect();
        if (Auth::check() && Schema::hasTable('customer_vouchers') && Schema::hasTable('vouchers')) {
            $userVouchers = DB::table('customer_vouchers')
                ->join('vouchers', 'customer_vouchers.voucher_id', '=', 'vouchers.id')
                ->where('customer_vouchers.user_id', Auth::id())
                ->whereIn('customer_vouchers.status', ['claimed', 'restored'])
                ->where(function ($q) {
                    $q->whereNull('customer_vouchers.expires_at')
                      ->orWhere('customer_vouchers.expires_at', '>=', now());
                })
                ->select(
                    'customer_vouchers.*',
                    'vouchers.title as voucher_title',
                    'vouchers.voucher_type',
                    'vouchers.discount_amount',
                    'vouchers.max_discount',
                    'vouchers.min_spend'
                )
                ->get();
        }

        return view('frontend.checkout', compact(
            'cart',
            'districts',
            'initialShipping',
            'manualPayments',
            'gateways',
            'hasPhysical',
            'hasDigital',
            'isMixed',
            'calc',
            'taxCalc',
            'userVouchers'
        ));
    }

    public function calculateDynamicShipping($districtName, $cart)
    {
        $districtName = strtolower(trim($districtName));
        $totalWeight = 0.000;
        $customShippingTotal = 0.00;
        $hasCustomShipping = false;
        $hasPhysical = false;
        $subtotal = 0.00;

        foreach ($cart as $id => $item) {
            $rawPrice = $item['price'] ?? 0;
            if (is_string($rawPrice)) {
                $rawPrice = preg_replace('/[^0-9.]/', '', $rawPrice);
            }
            $price = (float) $rawPrice;
            $qty = (int) ($item['quantity'] ?? 1);

            $productId = !empty($item['product_id']) ? (int)$item['product_id'] : (!empty($item['id']) && is_numeric($item['id']) ? (int)$item['id'] : (is_numeric($id) ? (int)$id : (int)explode('_', (string)$id)[0]));
            $product = Product::find($productId);

            $isDownloadable = !empty($item['is_downloadable']) 
                || ($product && (!empty($product->is_downloadable) || !empty($product->downloadable) || str_contains(strtolower($product->product_type ?? ''), 'download')));
            if ($isDownloadable) {
                $qty = 1;
            }

            $subtotal += ($price * $qty);

            $isDigital = ($item['is_digital'] ?? false) || ($product && ($product->is_virtual || $product->is_downloadable || in_array(strtolower($product->product_type ?? ''), ['virtual', 'downloadable', 'license'])));

            if (!$isDigital) {
                $hasPhysical = true;
            }

            if ($product && !$isDigital) {
                if ($product->custom_delivery_charge !== null && $product->custom_delivery_charge > 0) {
                    $hasCustomShipping = true;
                    if ($product->multiply_qty_delivery_charge) {
                        $customShippingTotal += ((float)$product->custom_delivery_charge * $qty);
                    } else {
                        $customShippingTotal += (float)$product->custom_delivery_charge;
                    }
                }

                $itemWeight = (float) ($product->weight ?? 0.000);
                $totalWeight += ($itemWeight * $qty);
            }
        }

        if (!$hasPhysical) {
            return [
                'cost'         => 0.00,
                'zone_name'    => 'Digital Delivery',
                'is_digital'   => true,
                'total_weight' => 0,
                'is_free'      => true
            ];
        }

        $allZones = ShippingZone::with(['locations', 'methods'])->orderBy('id', 'desc')->get();
        $matchingZones = [];

        foreach ($allZones as $zone) {
            foreach ($zone->locations as $loc) {
                if (!empty($loc->districts)) {
                    $districtsArray = array_map('trim', explode(',', strtolower($loc->districts)));
                    if (in_array($districtName, $districtsArray)) {
                        $matchingZones[] = $zone;
                        break;
                    }
                }
            }
        }

        if (empty($matchingZones)) {
            $fallback = ShippingZone::with(['locations', 'methods'])->where('name', 'LIKE', '%Outside%')->first() ?? $allZones->first();
            if ($fallback) {
                $matchingZones[] = $fallback;
            }
        }

        $isFreeShipping = false;
        $selectedZone = !empty($matchingZones) ? $matchingZones[0] : null;

        foreach ($matchingZones as $zone) {
            foreach ($zone->methods as $method) {
                if ($method->method_type === 'free_shipping' && ($method->is_enabled == 1 || $method->is_enabled === true)) {
                    if ($method->free_shipping_requires === 'no_requirement') {
                        $isFreeShipping = true;
                        $selectedZone = $zone;
                        break 2;
                    } elseif ($method->free_shipping_requires === 'min_amount') {
                        $minAmount = (float) $method->min_amount;
                        if ($subtotal >= $minAmount) {
                            $isFreeShipping = true;
                            $selectedZone = $zone;
                            break 2;
                        }
                    }
                }
            }
        }

        if ($isFreeShipping) {
            return [
                'cost'         => 0.00,
                'zone_name'    => ($selectedZone ? $selectedZone->name : 'Standard') . ' (Free Shipping)',
                'is_digital'   => false,
                'total_weight' => $totalWeight,
                'is_free'      => true
            ];
        }

        $calculatedCost = 0.00;

        if ($selectedZone) {
            $rate = ShippingClassRate::where('shipping_zone_id', $selectedZone->id)
                ->where('weight_from', '<=', $totalWeight)
                ->where('weight_to', '>=', $totalWeight)
                ->first();

            if ($rate) {
                $calculatedCost = (float) $rate->cost;
            } else {
                $maxSlab = ShippingClassRate::where('shipping_zone_id', $selectedZone->id)
                    ->orderBy('weight_to', 'desc')
                    ->first();

                if ($maxSlab && $totalWeight > (float)$maxSlab->weight_to) {
                    $extraWeight = ceil($totalWeight - (float)$maxSlab->weight_to);
                    $perKgExtra = (float)($maxSlab->per_kg_extra > 0 ? $maxSlab->per_kg_extra : 20.00);
                    $calculatedCost = (float)$maxSlab->cost + ($extraWeight * $perKgExtra);
                } else {
                    $flatMethod = ShippingZoneMethod::where('shipping_zone_id', $selectedZone->id)
                        ->where('method_type', 'flat_rate')
                        ->where('is_enabled', 1)
                        ->first();
                    $calculatedCost = $flatMethod ? (float)$flatMethod->cost : (stripos($selectedZone->name, 'Inside') !== false ? 60.00 : 120.00);
                }
            }
        }

        if ($hasCustomShipping) {
            $calculatedCost += $customShippingTotal;
        }

        return [
            'cost'         => $calculatedCost,
            'zone_name'    => $selectedZone ? $selectedZone->name : 'Standard Delivery',
            'is_digital'   => false,
            'total_weight' => $totalWeight,
            'is_free'      => false
        ];
    }

    public function getDeliveryCharge(Request $request)
    {
        $cart = session()->get('cart', []);
        if ($request->has('cart') && is_array($request->input('cart'))) {
            $cart = $request->input('cart');
        }

        $district = $request->input('district', 'Dhaka');

        $subtotal = 0.00;
        foreach ($cart as $id => $item) {
            $rawPrice = $item['price'] ?? 0;
            if (is_string($rawPrice)) {
                $rawPrice = preg_replace('/[^0-9.]/', '', $rawPrice);
            }
            $qty = (int)($item['quantity'] ?? 1);
            $pId = !empty($item['product_id']) ? (int)$item['product_id'] : (!empty($item['id']) && is_numeric($item['id']) ? (int)$item['id'] : (is_numeric($id) ? (int)$id : (int)explode('_', (string)$id)[0]));
            $p = Product::find($pId);
            if (!empty($item['is_downloadable']) || ($p && (!empty($p->is_downloadable) || str_contains(strtolower($p->product_type ?? ''), 'download')))) {
                $qty = 1;
            }
            $subtotal += ((float)$rawPrice * $qty);
        }

        $shippingData = $this->calculateDynamicShipping($district, $cart);
        $deliveryCharge = (float) $shippingData['cost'];

        // ডিসকাউন্ট ক্যালকুলেশন সিঙ্ক
        $appliedCoupon = Session::get('applied_coupon');
        $appliedVoucher = Session::get('applied_voucher');
        $couponCode = is_array($appliedCoupon) ? ($appliedCoupon['code'] ?? null) : $appliedCoupon;
        $voucherCode = is_array($appliedVoucher) ? ($appliedVoucher['code'] ?? ($appliedVoucher['voucher_code'] ?? null)) : $appliedVoucher;

        $calc = DiscountEngineService::calculateCartDiscounts(
            $subtotal,
            $cart,
            $couponCode,
            $voucherCode,
            Auth::id(),
            $deliveryCharge
        );

        $effectiveDeliveryCharge = $calc['adjusted_delivery_charge'];

        // Dynamic Tax Assessment
        $taxCalc = $this->calculateOrderTax(
            $cart,
            $effectiveDeliveryCharge,
            (float)($calc['total_discount'] ?? 0.0),
            $district,
            Auth::id()
        );

        $grandTotal = round($calc['final_payable'] + ($taxCalc['exclusive_tax'] ?? 0.0), 2);

        return response()->json([
            'status'           => 'success',
            'zone'             => $shippingData['zone_name'],
            'total_weight'     => $shippingData['total_weight'],
            'is_free'          => ($shippingData['is_free'] || $effectiveDeliveryCharge === 0.0),
            'is_digital'       => $shippingData['is_digital'],
            'subtotal'         => number_format($subtotal, 2, '.', ''),
            'item_total'       => number_format($subtotal, 2, '.', ''),
            'coupon_discount'  => number_format($calc['coupon_discount'], 2, '.', ''),
            'voucher_discount' => number_format($calc['voucher_discount'], 2, '.', ''),
            'total_discount'   => number_format($calc['total_discount'], 2, '.', ''),
            'delivery_charge'  => number_format($effectiveDeliveryCharge, 2, '.', ''),
            'is_vat_enabled'   => (bool)($taxCalc['is_vat_enabled'] ?? false),
            'tax_amount'       => number_format($taxCalc['total_tax'], 2, '.', ''),
            'product_tax'      => number_format($taxCalc['product_tax'], 2, '.', ''),
            'shipping_tax'     => number_format($taxCalc['shipping_tax'], 2, '.', ''),
            'exclusive_tax'    => number_format($taxCalc['exclusive_tax'], 2, '.', ''),
            'is_tax_inclusive' => $taxCalc['is_inclusive'],
            'total'            => number_format($grandTotal, 2, '.', ''),
            'grand_total'      => number_format($grandTotal, 2, '.', '')
        ]);
    }

    /**
     * Apply Coupon in Checkout (With Target User Check & Product/Category Match)
     */
    public function applyCoupon(Request $request)
    {
        $code = trim((string)($request->input('coupon_code') ?? $request->input('code', '')));
        if (empty($code)) {
            $msg = 'Please enter a coupon code.';
            return $request->ajax() || $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $cart = Session::get('cart', []);
        if (empty($cart)) {
            $msg = 'Your cart is empty.';
            return $request->ajax() || $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        // Target Customer Group (All vs Registered vs Guest) Check
        if (Schema::hasTable('coupons')) {
            $couponRecord = DB::table('coupons')->where('code', $code)->first();
            if ($couponRecord && isset($couponRecord->target_user_type)) {
                $target = $couponRecord->target_user_type;
                if ($target === 'registered_only' && !Auth::check()) {
                    $msg = 'এই কুপনটি শুধুমাত্র রেজিস্টার্ড ও লগইন করা গ্রাহকদের জন্য প্রযোজ্য। অনুগ্রহ করে প্রথমে লগইন করুন।';
                    return $request->ajax() || $request->wantsJson()
                        ? response()->json(['status' => 'error', 'message' => $msg], 422)
                        : back()->with('error', $msg);
                } elseif ($target === 'guest_only' && Auth::check()) {
                    $msg = 'এই কুপনটি শুধুমাত্র গেস্ট গ্রাহকদের জন্য প্রযোজ্য।';
                    return $request->ajax() || $request->wantsJson()
                        ? response()->json(['status' => 'error', 'message' => $msg], 422)
                        : back()->with('error', $msg);
                }
            }
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += (float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 1);
        }

        $district = $request->input('district', 'Dhaka');
        $shippingInfo = $this->calculateDynamicShipping($district, $cart);
        $deliveryCharge = (float) $shippingInfo['cost'];

        $result = DiscountEngineService::validateAndCalculateCoupon($code, $subtotal, $cart, Auth::id(), $deliveryCharge);

        if (!$result['valid']) {
            return $request->ajax() || $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $result['message']], 422)
                : back()->with('error', $result['message']);
        }

        Session::put('applied_coupon', [
            'code'          => $result['code'],
            'discount'      => $result['discount'],
            'discount_type' => $result['discount_type'],
        ]);

        $appliedVoucher = Session::get('applied_voucher');
        $voucherCode = is_array($appliedVoucher) ? ($appliedVoucher['code'] ?? ($appliedVoucher['voucher_code'] ?? null)) : $appliedVoucher;

        $calc = DiscountEngineService::calculateCartDiscounts(
            $subtotal,
            $cart,
            $result['code'],
            $voucherCode,
            Auth::id(),
            $deliveryCharge
        );

        $taxCalc = $this->calculateOrderTax(
            $cart,
            $calc['adjusted_delivery_charge'],
            (float)($calc['total_discount'] ?? 0.0),
            $district,
            Auth::id()
        );

        $grandTotal = round($calc['final_payable'] + ($taxCalc['exclusive_tax'] ?? 0.0), 2);

        $msg = $result['message'] ?? 'Coupon applied successfully!';
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'           => 'success',
                'message'          => $msg,
                'coupon_code'      => $result['code'],
                'coupon_discount'  => number_format($calc['coupon_discount'], 2, '.', ''),
                'voucher_discount' => number_format($calc['voucher_discount'], 2, '.', ''),
                'total_discount'   => number_format($calc['total_discount'], 2, '.', ''),
                'delivery_charge'  => number_format($calc['adjusted_delivery_charge'], 2, '.', ''),
                'is_vat_enabled'   => (bool)($taxCalc['is_vat_enabled'] ?? false),
                'tax_amount'       => number_format($taxCalc['total_tax'], 2, '.', ''),
                'product_tax'      => number_format($taxCalc['product_tax'], 2, '.', ''),
                'shipping_tax'     => number_format($taxCalc['shipping_tax'], 2, '.', ''),
                'exclusive_tax'    => number_format($taxCalc['exclusive_tax'], 2, '.', ''),
                'subtotal'         => number_format($calc['subtotal'], 2, '.', ''),
                'grand_total'      => number_format($grandTotal, 2, '.', ''),
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Remove Applied Coupon from Checkout
     */
    public function removeCoupon(Request $request)
    {
        Session::forget('applied_coupon');

        $cart = Session::get('cart', []);
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += (float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 1);
        }

        $district = $request->input('district', 'Dhaka');
        $shippingInfo = $this->calculateDynamicShipping($district, $cart);
        $deliveryCharge = (float) $shippingInfo['cost'];

        $appliedVoucher = Session::get('applied_voucher');
        $voucherCode = is_array($appliedVoucher) ? ($appliedVoucher['code'] ?? ($appliedVoucher['voucher_code'] ?? null)) : $appliedVoucher;

        $calc = DiscountEngineService::calculateCartDiscounts(
            $subtotal,
            $cart,
            null,
            $voucherCode,
            Auth::id(),
            $deliveryCharge
        );

        $taxCalc = $this->calculateOrderTax(
            $cart,
            $calc['adjusted_delivery_charge'],
            (float)($calc['total_discount'] ?? 0.0),
            $district,
            Auth::id()
        );

        $grandTotal = round($calc['final_payable'] + ($taxCalc['exclusive_tax'] ?? 0.0), 2);

        $msg = 'Coupon removed from checkout.';
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'           => 'success',
                'message'          => $msg,
                'coupon_discount'  => '0.00',
                'voucher_discount' => number_format($calc['voucher_discount'], 2, '.', ''),
                'total_discount'   => number_format($calc['total_discount'], 2, '.', ''),
                'delivery_charge'  => number_format($calc['adjusted_delivery_charge'], 2, '.', ''),
                'is_vat_enabled'   => (bool)($taxCalc['is_vat_enabled'] ?? false),
                'tax_amount'       => number_format($taxCalc['total_tax'], 2, '.', ''),
                'product_tax'      => number_format($taxCalc['product_tax'], 2, '.', ''),
                'shipping_tax'     => number_format($taxCalc['shipping_tax'], 2, '.', ''),
                'exclusive_tax'    => number_format($taxCalc['exclusive_tax'], 2, '.', ''),
                'subtotal'         => number_format($calc['subtotal'], 2, '.', ''),
                'grand_total'      => number_format($grandTotal, 2, '.', ''),
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Apply Voucher in Checkout (With Target User Check)
     */
    public function applyVoucher(Request $request)
    {
        $code = trim((string)($request->input('voucher_code') ?? $request->input('code', '')));
        if (empty($code)) {
            $msg = 'Please enter or select a voucher code.';
            return $request->ajax() || $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $cart = Session::get('cart', []);
        if (empty($cart)) {
            $msg = 'Your cart is empty.';
            return $request->ajax() || $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        // Voucher Target Check
        if (Schema::hasTable('vouchers')) {
            $vRecord = DB::table('vouchers')
                ->where('code_prefix', $code)
                ->orWhere('title', $code)
                ->first();

            if (!$vRecord && Schema::hasTable('customer_vouchers')) {
                $cv = DB::table('customer_vouchers')->where('voucher_code', $code)->first();
                if ($cv) {
                    $vRecord = DB::table('vouchers')->where('id', $cv->voucher_id)->first();
                }
            }

            if ($vRecord && isset($vRecord->target_user_type)) {
                if ($vRecord->target_user_type === 'registered_only' && !Auth::check()) {
                    $msg = 'এই ভাউচারটি শুধুমাত্র রেজিস্টার্ড গ্রাহকদের ওয়ালেটের জন্য প্রযোজ্য। অনুগ্রহ করে লগইন করুন।';
                    return $request->ajax() || $request->wantsJson()
                        ? response()->json(['status' => 'error', 'message' => $msg], 401)
                        : back()->with('error', $msg);
                } elseif ($vRecord->target_user_type === 'guest_only' && Auth::check()) {
                    $msg = 'এই ভাউচারটি শুধুমাত্র গেস্ট গ্রাহকদের জন্য প্রযোজ্য।';
                    return $request->ajax() || $request->wantsJson()
                        ? response()->json(['status' => 'error', 'message' => $msg], 422)
                        : back()->with('error', $msg);
                }
            }
        }

        if (!Auth::check() && (!isset($vRecord) || ($vRecord->target_user_type ?? 'registered_only') === 'registered_only')) {
            $msg = 'Please log in to use your personal vouchers.';
            return $request->ajax() || $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 401)
                : back()->with('error', $msg);
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += (float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 1);
        }

        $district = $request->input('district', 'Dhaka');
        $shippingInfo = $this->calculateDynamicShipping($district, $cart);
        $deliveryCharge = (float) $shippingInfo['cost'];

        $result = DiscountEngineService::validateAndCalculateVoucher($code, $subtotal, $cart, Auth::id(), $deliveryCharge);

        if (!$result['valid']) {
            return $request->ajax() || $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $result['message']], 422)
                : back()->with('error', $result['message']);
        }

        Session::put('applied_voucher', [
            'code'         => $result['voucher_code'],
            'discount'     => $result['discount'],
            'voucher_type' => $result['voucher_type'],
        ]);

        $appliedCoupon = Session::get('applied_coupon');
        $couponCode = is_array($appliedCoupon) ? ($appliedCoupon['code'] ?? null) : $appliedCoupon;

        $calc = DiscountEngineService::calculateCartDiscounts(
            $subtotal,
            $cart,
            $couponCode,
            $result['voucher_code'],
            Auth::id(),
            $deliveryCharge
        );

        $taxCalc = $this->calculateOrderTax(
            $cart,
            $calc['adjusted_delivery_charge'],
            (float)($calc['total_discount'] ?? 0.0),
            $district,
            Auth::id()
        );

        $grandTotal = round($calc['final_payable'] + ($taxCalc['exclusive_tax'] ?? 0.0), 2);

        $msg = $result['message'] ?? 'Voucher applied successfully!';
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'           => 'success',
                'message'          => $msg,
                'voucher_code'     => $result['voucher_code'],
                'coupon_discount'  => number_format($calc['coupon_discount'], 2, '.', ''),
                'voucher_discount' => number_format($calc['voucher_discount'], 2, '.', ''),
                'total_discount'   => number_format($calc['total_discount'], 2, '.', ''),
                'delivery_charge'  => number_format($calc['adjusted_delivery_charge'], 2, '.', ''),
                'is_vat_enabled'   => (bool)($taxCalc['is_vat_enabled'] ?? false),
                'tax_amount'       => number_format($taxCalc['total_tax'], 2, '.', ''),
                'product_tax'      => number_format($taxCalc['product_tax'], 2, '.', ''),
                'shipping_tax'     => number_format($taxCalc['shipping_tax'], 2, '.', ''),
                'exclusive_tax'    => number_format($taxCalc['exclusive_tax'], 2, '.', ''),
                'subtotal'         => number_format($calc['subtotal'], 2, '.', ''),
                'grand_total'      => number_format($grandTotal, 2, '.', ''),
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Remove Applied Voucher from Checkout
     */
    public function removeVoucher(Request $request)
    {
        Session::forget('applied_voucher');

        $cart = Session::get('cart', []);
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += (float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 1);
        }

        $district = $request->input('district', 'Dhaka');
        $shippingInfo = $this->calculateDynamicShipping($district, $cart);
        $deliveryCharge = (float) $shippingInfo['cost'];

        $appliedCoupon = Session::get('applied_coupon');
        $couponCode = is_array($appliedCoupon) ? ($appliedCoupon['code'] ?? null) : $appliedCoupon;

        $calc = DiscountEngineService::calculateCartDiscounts(
            $subtotal,
            $cart,
            $couponCode,
            null,
            Auth::id(),
            $deliveryCharge
        );

        $taxCalc = $this->calculateOrderTax(
            $cart,
            $calc['adjusted_delivery_charge'],
            (float)($calc['total_discount'] ?? 0.0),
            $district,
            Auth::id()
        );

        $grandTotal = round($calc['final_payable'] + ($taxCalc['exclusive_tax'] ?? 0.0), 2);

        $msg = 'Voucher removed from checkout.';
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'           => 'success',
                'message'          => $msg,
                'voucher_discount' => '0.00',
                'coupon_discount'  => number_format($calc['coupon_discount'], 2, '.', ''),
                'total_discount'   => number_format($calc['total_discount'], 2, '.', ''),
                'delivery_charge'  => number_format($calc['adjusted_delivery_charge'], 2, '.', ''),
                'is_vat_enabled'   => (bool)($taxCalc['is_vat_enabled'] ?? false),
                'tax_amount'       => number_format($taxCalc['total_tax'], 2, '.', ''),
                'product_tax'      => number_format($taxCalc['product_tax'], 2, '.', ''),
                'shipping_tax'     => number_format($taxCalc['shipping_tax'], 2, '.', ''),
                'exclusive_tax'    => number_format($taxCalc['exclusive_tax'], 2, '.', ''),
                'subtotal'         => number_format($calc['subtotal'], 2, '.', ''),
                'grand_total'      => number_format($grandTotal, 2, '.', ''),
            ]);
        }

        return back()->with('success', $msg);
    }

    public function processOrder(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return response()->json(['status' => 'error', 'message' => 'Your cart is empty!'], 400);
        }

        $hasPhysical = false;
        $hasDigital = false;
        foreach ($cart as $id => $item) {
            $productId = !empty($item['product_id']) ? (int)$item['product_id'] : (!empty($item['id']) && is_numeric($item['id']) ? (int)$item['id'] : (is_numeric($id) ? (int)$id : (int)explode('_', (string)$id)[0]));
            $prod = Product::find($productId);
            $isItemDigital = ($item['is_digital'] ?? false) || ($prod && ($prod->is_virtual || $prod->is_downloadable || in_array(strtolower($prod->product_type ?? ''), ['virtual', 'downloadable', 'license'])));
            
            if ($isItemDigital) {
                $hasDigital = true;
            } else {
                $hasPhysical = true;
            }
        }

        if ($hasDigital && strtolower(trim($request->payment_method)) === 'cash on delivery') {
            return response()->json([
                'status'  => 'error',
                'message' => 'আপনার কার্টে ডিজিটাল পণ্য রয়েছে। ডিজিটাল পণ্যের অ্যাক্সেস পেতে সম্পূর্ণ অর্ডারটির পেমেন্ট অনলাইন বা ম্যানুয়াল মাধ্যমে পরিশোধ করতে হবে।'
            ], 422);
        }

        $rules = [
            'name'           => 'required|string|max:255',
            'phone'          => 'required|string|max:20',
            'payment_method' => 'required|string|max:100',
            'email'          => $hasDigital ? 'required|email|max:255' : 'nullable|email|max:255',
        ];

        if ($hasPhysical) {
            $rules['address'] = 'required|string|max:500';
            $rules['district'] = 'required|string|max:100';
        } else {
            $request->merge([
                'district' => $request->filled('district') ? $request->district : 'Digital',
                'address'  => $request->filled('address') ? $request->address : 'Digital Delivery',
            ]);
        }

        if ($request->input('payment_type') === 'manual') {
            $rules['sender_number'] = 'nullable|string|max:20';
            $rules['transaction_id'] = 'nullable|string|max:100';
            $rules['payment_screenshot_file'] = 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096';

            if (!$request->filled('sender_number') && !$request->filled('transaction_id') && !$request->hasFile('payment_screenshot_file')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sender Number, Transaction ID অথবা Payment Screenshot-এর যেকোনো একটি পূরণ করুন।'
                ], 422);
            }
        }

        $request->validate($rules);

        // Strict duplicate transaction protection
        if ($request->filled('transaction_id')) {
            $dupCheck = PaymentTransactionService::checkDuplicateTransactionId(
                $request->input('transaction_id'),
                $request->input('payment_method')
            );

            if ($dupCheck['is_duplicate']) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $dupCheck['message']
                ], 422);
            }
        }

        // =========================================================================
        // 1. PRE-CHECK STOCK AVAILABILITY (Compound Cart Keys Supported)
        // =========================================================================
        foreach ($cart as $id => $item) {
            $productId = !empty($item['product_id']) ? (int)$item['product_id'] : (!empty($item['id']) && is_numeric($item['id']) ? (int)$item['id'] : (is_numeric($id) ? (int)$id : (int)explode('_', (string)$id)[0]));
            $prod = Product::find($productId);
            $isItemDigital = ($item['is_digital'] ?? false) || ($prod && ($prod->is_virtual || $prod->is_downloadable || in_array(strtolower($prod->product_type ?? ''), ['virtual', 'downloadable', 'license'])));
            $qty = (int)($item['quantity'] ?? 1);
            if (!empty($item['is_downloadable']) || ($prod && (!empty($prod->is_downloadable) || str_contains(strtolower($prod->product_type ?? ''), 'download')))) {
                $qty = 1;
            }
            $variantId = !empty($item['variant_id']) ? (int)$item['variant_id'] : null;

            if (!$isItemDigital && $productId) {
                $availability = InventoryService::checkStockAvailability((int)$productId, $variantId, $qty);
                if (!$availability['available']) {
                    $itemLabel = !empty($availability['title']) ? $availability['title'] : ($prod->title ?? 'পণ্য');
                    return response()->json([
                        'status'  => 'error',
                        'message' => "দুঃখিত, '{$itemLabel}'-এর পর্যাপ্ত স্টক নেই। বর্তমান অবশিষ্ট স্টক: {$availability['stock']} টি।"
                    ], 422);
                }
            }
        }

        $subtotal = 0.00;
        foreach ($cart as $id => $item) {
            $rawPrice = $item['price'] ?? 0;
            if (is_string($rawPrice)) {
                $rawPrice = preg_replace('/[^0-9.]/', '', $rawPrice);
            }
            $qty = (int)($item['quantity'] ?? 1);
            $pId = !empty($item['product_id']) ? (int)$item['product_id'] : (!empty($item['id']) && is_numeric($item['id']) ? (int)$item['id'] : (is_numeric($id) ? (int)$id : (int)explode('_', (string)$id)[0]));
            $p = Product::find($pId);
            if (!empty($item['is_downloadable']) || ($p && (!empty($p->is_downloadable) || str_contains(strtolower($p->product_type ?? ''), 'download')))) {
                $qty = 1;
            }
            $subtotal += ((float)$rawPrice * $qty);
        }

        $district = $request->input('district', 'Dhaka');
        $shippingInfo = $this->calculateDynamicShipping($district, $cart);
        $deliveryCharge = (float) $shippingInfo['cost'];

        // কুপন ও ভাউচার রিক্যালকুলেশন
        $appliedCoupon = Session::get('applied_coupon');
        $appliedVoucher = Session::get('applied_voucher');
        $couponCode = is_array($appliedCoupon) ? ($appliedCoupon['code'] ?? null) : $appliedCoupon;
        $voucherCode = is_array($appliedVoucher) ? ($appliedVoucher['code'] ?? ($appliedVoucher['voucher_code'] ?? null)) : $appliedVoucher;

        $userId = auth()->check() ? auth()->id() : null;

        // Final Security Verification for Target Customer Group before order placement
        if ($couponCode && Schema::hasTable('coupons')) {
            $couponCheck = DB::table('coupons')->where('code', $couponCode)->first();
            if ($couponCheck && isset($couponCheck->target_user_type)) {
                if ($couponCheck->target_user_type === 'registered_only' && !$userId) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'প্রদত্ত কুপনটি শুধুমাত্র রেজিস্টার্ড গ্রাহকদের জন্য প্রযোজ্য।'
                    ], 422);
                } elseif ($couponCheck->target_user_type === 'guest_only' && $userId) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'প্রদত্ত কুপনটি শুধুমাত্র গেস্ট গ্রাহকদের জন্য প্রযোজ্য।'
                    ], 422);
                }
            }
        }

        $calc = DiscountEngineService::calculateCartDiscounts(
            $subtotal,
            $cart,
            $couponCode,
            $voucherCode,
            $userId,
            $deliveryCharge
        );

        $effectiveDeliveryCharge = $calc['adjusted_delivery_charge'];
        $couponDiscount = $calc['coupon_discount'];
        $voucherDiscount = $calc['voucher_discount'];

        // Dynamic Tax Assessment & Calculation for Checkout
        $taxCalc = $this->calculateOrderTax(
            $cart,
            $effectiveDeliveryCharge,
            (float)($calc['total_discount'] ?? 0.0),
            $district,
            $userId
        );

        $grandTotal = round($calc['final_payable'] + ($taxCalc['exclusive_tax'] ?? 0.0), 2);
        $totalTaxAmount = $taxCalc['total_tax'];
        $productTaxAmount = $taxCalc['product_tax'];
        $shippingTaxAmount = $taxCalc['shipping_tax'];

        $invoiceNo = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        $screenshotPath = null;
        if ($request->hasFile('payment_screenshot_file')) {
            $screenshotPath = $this->saveCheckoutFile($request->file('payment_screenshot_file'), 'payment_proofs');
        }

        $noteDetails = "Customer: {$request->name}, Phone: {$request->phone}, Address: {$request->address}, District: {$district}";
        if ($request->filled('order_note')) {
            $noteDetails .= " | Special Note: " . trim($request->order_note);
        }

        $isImmediatelyPaid = ($grandTotal <= 0);
        $isCOD = in_array(strtolower(trim($request->payment_method)), ['cash on delivery', 'cod']);
        $expectedCOD = $isCOD ? $grandTotal : ($isImmediatelyPaid ? 0.00 : $grandTotal);

        DB::beginTransaction();
        try {
            if ($request->filled('transaction_id')) {
                $raceCheck = PaymentTransactionService::checkDuplicateTransactionId(
                    $request->input('transaction_id'),
                    $request->input('payment_method')
                );
                if ($raceCheck['is_duplicate']) {
                    DB::rollBack();
                    return response()->json([
                        'status'  => 'error',
                        'message' => $raceCheck['message']
                    ], 422);
                }
            }

            $orderData = [
                'invoice_no'         => $invoiceNo,
                'user_id'            => $userId,
                'subtotal'           => $subtotal,
                'grand_total'        => $grandTotal,
                'payment_method'     => $request->payment_method,
                'payment_status'     => $isImmediatelyPaid ? 'paid' : 'unpaid',
                'order_status'       => $isImmediatelyPaid ? 'completed' : 'pending',
                'transaction_id'     => $request->transaction_id ? trim($request->transaction_id) : null,
                'sender_number'      => $request->sender_number ? trim($request->sender_number) : null,
                'payment_screenshot' => $screenshotPath,
                'created_at'         => now(),
                'updated_at'         => now(),
            ];

            $conditionalFields = [
                'order_type'          => 'online',
                'order_source'        => 'web',
                'product_subtotal'    => $subtotal,
                'delivery_charge'     => $effectiveDeliveryCharge,
                'shipping_charge'     => $effectiveDeliveryCharge,
                'tax_amount'          => $totalTaxAmount,
                'tax'                 => $totalTaxAmount,
                'total_tax'           => $totalTaxAmount,
                'product_tax_amount'  => $productTaxAmount,
                'shipping_tax_amount' => $shippingTaxAmount,
                'taxable_base_amount' => $taxCalc['taxable_base'],
                'is_tax_inclusive'    => $taxCalc['is_inclusive'],
                'tax_breakdown'       => json_encode($taxCalc['breakdown']),
                'coupon_discount'     => $couponDiscount,
                'coupon_code'         => $calc['coupon_code'],
                'voucher_code'        => $calc['voucher_code'],
                'voucher_discount'    => $voucherDiscount,
                'expected_cod_amount' => $expectedCOD,
                'actual_cod_received' => 0.00,
                'is_stock_deducted'   => false,
                'is_stock_restored'   => false,
                'is_cod_settled'      => false,
                'paid_amount'         => 0.00,
                'due_amount'          => $isImmediatelyPaid ? 0.00 : $grandTotal,
                'customer_name'       => $request->name,
                'name'                => $request->name,
                'customer_phone'      => $request->phone,
                'phone'               => $request->phone,
                'customer_email'      => $request->email,
                'email'               => $request->email,
                'customer_address'    => $request->address,
                'shipping_address'    => $request->address,
                'address'             => $request->address,
                'district'            => $district,
                'shipping_district'   => $district,
                'notes'               => $noteDetails,
                'order_note'          => $request->order_note,
            ];

            foreach ($conditionalFields as $col => $val) {
                if (Schema::hasColumn('orders', $col)) {
                    $orderData[$col] = $val;
                }
            }

            $orderId = DB::table('orders')->insertGetId($orderData);
            $itemsTable = $this->resolveItemsTable();

            // =========================================================================
            // 2. ORDER ITEMS INSERTION & IMMUTABLE TAX SNAPSHOT
            // =========================================================================
            foreach ($cart as $id => $item) {
                $rawPrice = $item['price'] ?? 0;
                if (is_string($rawPrice)) {
                    $rawPrice = preg_replace('/[^0-9.]/', '', $rawPrice);
                }
                $unitPrice = (float) $rawPrice;
                $qty = (int) ($item['quantity'] ?? 1);

                $productId = !empty($item['product_id']) ? (int)$item['product_id'] : (!empty($item['id']) && is_numeric($item['id']) ? (int)$item['id'] : (is_numeric($id) ? (int)$id : (int)explode('_', (string)$id)[0]));
                $pModel = Product::find($productId);

                $isDownloadable = !empty($item['is_downloadable']) 
                    || ($pModel && (!empty($pModel->is_downloadable) || !empty($pModel->downloadable) || str_contains(strtolower($pModel->product_type ?? ''), 'download')));
                
                if ($isDownloadable) {
                    $qty = 1;
                }

                $variantId = !empty($item['variant_id']) ? (int)$item['variant_id'] : null;
                $variantTitle = $item['variant_name'] ?? null;
                $color = $item['color'] ?? null;
                $size  = $item['size'] ?? null;
                $sku   = $item['sku'] ?? null;

                $pTitle = $item['title'] ?? null;
                if ((empty($pTitle) || $pTitle === 'Product') && !empty($productId)) {
                    $pTitle = DB::table('products')->where('id', $productId)->value('title');
                }

                if (!empty($variantId) && (empty($color) || empty($size) || empty($sku))) {
                    $vRecord = DB::table('product_variants')->where('id', $variantId)->first();
                    if ($vRecord) {
                        $color = $vRecord->color ?? $color;
                        $size  = $vRecord->size ?? $size;
                        $sku   = $vRecord->product_code ?? ($vRecord->sku ?? $sku);
                        $parts = array_filter([$color, $size]);
                        $variantTitle = count($parts) > 0 ? implode(' / ', $parts) : ($vRecord->name ?? $variantTitle);
                    }
                }

                $isItemDigital = ($item['is_digital'] ?? false) || ($pModel && ($pModel->is_virtual || $pModel->is_downloadable || in_array(strtolower($pModel->product_type ?? ''), ['virtual', 'downloadable', 'license'])));

                $purchaseCost = 0.00;
                $cogs = 0.00;
                $isCostEstimated = false;

                if (!$isItemDigital && $productId) {
                    InventoryService::recordOrderEstimate((int)$productId, $variantId, $qty, $invoiceNo);

                    if (!empty($variantId)) {
                        $vRecord = DB::table('product_variants')->where('id', $variantId)->first();
                        $rawVariantCost = $vRecord->purchase_cost ?? ($vRecord->purchase_price ?? null);
                        if (!is_null($rawVariantCost) && (float)$rawVariantCost > 0) {
                            $purchaseCost = (float)$rawVariantCost;
                        } else {
                            $parentCost = $pModel ? ($pModel->purchase_cost ?? ($pModel->purchase_price ?? 0.00)) : 0.00;
                            $purchaseCost = (float)$parentCost;
                            $isCostEstimated = true;
                        }
                        if (empty($sku) && $vRecord) {
                            $sku = $vRecord->product_code ?? ($vRecord->sku ?? ('VAR-' . $variantId));
                        }
                    } else {
                        $parentCost = $pModel ? ($pModel->purchase_cost ?? ($pModel->purchase_price ?? 0.00)) : 0.00;
                        $purchaseCost = (float)$parentCost;
                        $isCostEstimated = ($purchaseCost <= 0);
                        if (empty($sku) && $pModel) {
                            $sku = $pModel->sku ?? ($pModel->product_code ?? ('PRD-' . $productId));
                        }
                    }

                    $cogs = round($purchaseCost * $qty, 2);
                } else {
                    $costVal = $pModel ? ($pModel->purchase_cost ?? ($pModel->purchase_price ?? 0.00)) : 0.00;
                    $purchaseCost = (float)$costVal;
                    $cogs = round($purchaseCost * $qty, 2);
                    $isCostEstimated = ($purchaseCost <= 0);
                }

                // Resolve Item Tax Snapshot
                $itemTaxSnapshot = $taxCalc['items'][$id] ?? null;
                if (!$itemTaxSnapshot) {
                    foreach ($taxCalc['items'] as $itKey => $itVal) {
                        if (($itVal['product_id'] ?? null) == $productId && ($itVal['variant_id'] ?? null) == $variantId) {
                            $itemTaxSnapshot = $itVal;
                            break;
                        }
                    }
                }

                $itemTaxRate = $itemTaxSnapshot['tax_rate'] ?? 0.0;
                $itemTaxAmount = $itemTaxSnapshot['tax_amount'] ?? 0.0;
                $itemTaxableBase = $itemTaxSnapshot['calculated_base'] ?? ($itemTaxSnapshot['taxable_base_amount'] ?? ($unitPrice * $qty));
                $itemIsInclusive = $itemTaxSnapshot['is_inclusive'] ?? ($itemTaxSnapshot['is_tax_inclusive'] ?? false);
                $itemRuleId = $itemTaxSnapshot['tax_rule_id'] ?? ($itemTaxSnapshot['rule_id'] ?? null);
                $itemClassId = $itemTaxSnapshot['tax_class'] ?? ($itemTaxSnapshot['class_id'] ?? null);

                $itemData = [
                    'order_id'   => $orderId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $condItem = [
                    'product_id'          => $productId,
                    'product_title'       => $pTitle ?? 'Product',
                    'product_name'        => $pTitle ?? 'Product',
                    'name'                => $pTitle ?? 'Product',
                    'title'               => $pTitle ?? 'Product',
                    'variant_id'          => $variantId,
                    'variant_title'       => $variantTitle,
                    'color'               => $color,
                    'size'                => $size,
                    'sku'                 => $sku,
                    'quantity'            => $qty,
                    'qty'                 => $qty,
                    'unit_price'          => $unitPrice,
                    'price'               => $unitPrice,
                    'product_price'       => $unitPrice,
                    'subtotal'            => $unitPrice * $qty,
                    'total'               => $unitPrice * $qty,
                    'tax_rate'            => $itemTaxRate,
                    'tax_amount'          => $itemTaxAmount,
                    'taxable_base_amount' => $itemTaxableBase,
                    'is_tax_inclusive'    => $itemIsInclusive,
                    'is_inclusive'        => $itemIsInclusive,
                    'tax_rule_id'         => $itemRuleId,
                    'tax_class_id'        => null,
                    'tax_class'           => is_string($itemClassId) ? $itemClassId : 'STANDARD',
                    'purchase_cost'       => $purchaseCost,
                    'cogs'                => $cogs,
                    'is_cost_estimated'   => $isCostEstimated,
                ];

                foreach ($condItem as $col => $val) {
                    if (Schema::hasColumn('order_items', $col)) {
                        $itemData[$col] = $val;
                    }
                }

                DB::table('order_items')->insert($itemData);
            }

            // Permanently record immutable tax snapshot via TaxEngineService
            if (class_exists(TaxEngineService::class) && method_exists(TaxEngineService::class, 'recordOrderTaxSnapshot')) {
                TaxEngineService::recordOrderTaxSnapshot($orderId, !empty($taxCalc['raw_tax_engine_result']) ? $taxCalc['raw_tax_engine_result'] : $taxCalc);
            }

            // =========================================================================
            // 3. RECORD TRANSACTION & FULFILLMENT
            // =========================================================================
            $savedOrder = DB::table('orders')->where('id', $orderId)->first();
            if ($savedOrder) {
                PaymentTransactionService::recordCheckoutTransaction($savedOrder, [
                    'payment_type'     => $request->input('payment_type'),
                    'payment_method'   => $request->input('payment_method'),
                    'sender_number'    => $request->input('sender_number'),
                    'transaction_id'   => $request->input('transaction_id'),
                    'screenshot_path'  => $screenshotPath,
                    'submitted_amount' => $grandTotal,
                ]);
            }

            // =========================================================================
            // 4. RECORD COUPON & VOUCHER REDEMPTIONS (SNAPSHOT & ATOMIC AUDIT)
            // =========================================================================
            DiscountEngineService::recordOrderDiscountRedemptions(
                $orderId,
                $calc['coupon_code'],
                $calc['voucher_code'],
                $userId,
                $subtotal,
                $grandTotal,
                $deliveryCharge,
                $cart
            );

            // =========================================================================
            // 5. ABANDONED CART RECOVERY RECONCILIATION
            // =========================================================================
            if (Schema::hasTable('email_abandoned_carts')) {
                try {
                    $sessionRecoveryToken = session()->get('active_abandoned_cart_token');
                    $custEmail = !empty($request->email) ? strtolower(trim($request->email)) : null;
                    $custPhone = !empty($request->phone) ? trim($request->phone) : null;
                    $custCleanPhone = preg_replace('/[^0-9]/', '', $custPhone ?? '');

                    EmailAbandonedCart::where('is_recovered', false)
                        ->where(function ($q) use ($sessionRecoveryToken, $custEmail, $custPhone, $custCleanPhone, $userId) {
                            $hasCondition = false;
                            if ($sessionRecoveryToken) {
                                $q->orWhere('recovery_token', $sessionRecoveryToken);
                                $hasCondition = true;
                            }
                            if ($custEmail && filter_var($custEmail, FILTER_VALIDATE_EMAIL)) {
                                $q->orWhere('email', $custEmail);
                                $hasCondition = true;
                            }
                            if (!empty($custPhone)) {
                                $q->orWhere('phone', $custPhone);
                                $q->orWhere('email', $custPhone . '@guest.shopnowbd.online');
                                $hasCondition = true;
                            }
                            if (!empty($custCleanPhone) && $custCleanPhone !== $custPhone) {
                                $q->orWhere('phone', $custCleanPhone);
                                $q->orWhere('email', $custCleanPhone . '@guest.shopnowbd.online');
                                $hasCondition = true;
                            }
                            if ($userId) {
                                $q->orWhere('user_id', $userId);
                                $hasCondition = true;
                            }
                            if (!$hasCondition) {
                                $q->whereRaw('0 = 1');
                            }
                        })
                        ->update([
                            'is_recovered'       => true,
                            'recovered_at'       => now(),
                            'recovered_order_id' => $orderId,
                        ]);
                } catch (\Throwable $cartReconcileEx) {
                    \Log::warning('Abandoned cart reconciliation error: ' . $cartReconcileEx->getMessage());
                }
            }

            // =========================================================================
            // 6. AUTO-SYNC CHECKOUT CUSTOMER TO EMAIL MARKETING SUBSCRIBERS
            // =========================================================================
            $custEmail = !empty($request->email) ? strtolower(trim($request->email)) : null;
            if ($custEmail && filter_var($custEmail, FILTER_VALIDATE_EMAIL)) {
                try {
                    if (class_exists(EmailSubscriber::class) && Schema::hasTable('email_subscribers')) {
                        if (class_exists(EmailSuppressionList::class)) {
                            EmailSuppressionList::unsuppress($custEmail);
                        }

                        $custName = trim($request->name ?? '');
                        $fName = $custName;
                        $lName = null;
                        if (!empty($custName) && str_contains($custName, ' ')) {
                            $parts = explode(' ', $custName, 2);
                            $fName = $parts[0];
                            $lName = $parts[1] ?? null;
                        }

                        $custPhone = trim($request->phone ?? '');

                        $subscriber = EmailSubscriber::firstOrNew(['email' => $custEmail]);

                        if (Schema::hasColumn('email_subscribers', 'first_name') && empty($subscriber->first_name)) {
                            $subscriber->first_name = $fName;
                        }
                        if (Schema::hasColumn('email_subscribers', 'last_name') && empty($subscriber->last_name)) {
                            $subscriber->last_name = $lName;
                        }
                        if (Schema::hasColumn('email_subscribers', 'name') && empty($subscriber->name)) {
                            $subscriber->name = $custName;
                        }
                        if (Schema::hasColumn('email_subscribers', 'phone') && empty($subscriber->phone)) {
                            $subscriber->phone = $custPhone;
                        }

                        if (Schema::hasColumn('email_subscribers', 'status')) {
                            if (empty($subscriber->status) || $subscriber->status !== 'unsubscribed') {
                                $subscriber->status = 'subscribed';
                            }
                        }

                        if (Schema::hasColumn('email_subscribers', 'marketing_consent')) {
                            $subscriber->marketing_consent = true;
                        }

                        if (Schema::hasColumn('email_subscribers', 'source')) {
                            $subscriber->source = 'checkout';
                        }

                        if (Schema::hasColumn('email_subscribers', 'consent_source')) {
                            $subscriber->consent_source = 'checkout';
                        }

                        if (Schema::hasColumn('email_subscribers', 'consent_at')) {
                            $subscriber->consent_at = now();
                        }

                        if (Schema::hasColumn('email_subscribers', 'consent_ip')) {
                            $subscriber->consent_ip = $request->ip() ?: '127.0.0.1';
                        }

                        if (Schema::hasColumn('email_subscribers', 'user_id')) {
                            $subscriber->user_id = $userId ?? (auth()->check() ? auth()->id() : null);
                        }

                        if (Schema::hasColumn('email_subscribers', 'subscribed_at') && empty($subscriber->subscribed_at)) {
                            $subscriber->subscribed_at = now();
                        }

                        // Tags
                        if (Schema::hasColumn('email_subscribers', 'tags')) {
                            $existingTags = [];
                            if (!empty($subscriber->tags)) {
                                $existingTags = is_array($subscriber->tags) ? $subscriber->tags : json_decode($subscriber->tags, true) ?? [];
                            }
                            $mergedTags = array_values(array_unique(array_filter(array_merge($existingTags, ['customer', 'checkout']))));
                            $subscriber->tags = (method_exists($subscriber, 'hasCast') && $subscriber->hasCast('tags'))
                                ? $mergedTags
                                : json_encode($mergedTags);
                        }

                        $subscriber->updated_at = now();
                        $subscriber->save();

                        // Log Consent Entry
                        if (class_exists(EmailConsentLog::class) && method_exists(EmailConsentLog::class, 'logConsent')) {
                            EmailConsentLog::logConsent($custEmail, 'opt_in', 'checkout', $subscriber->id);
                        }
                    }
                } catch (\Throwable $subSyncEx) {
                    \Log::warning('Checkout Subscriber Sync Error: ' . $subSyncEx->getMessage());
                }
            }

            if ($isImmediatelyPaid) {
                DigitalProductService::fulfillDigitalOrder($orderId);
            }

            DB::commit();

            // কার্ট ও কুপন/ভাউচার সেশন সম্পূর্ণ পরিষ্কার করা
            session()->forget('cart');
            session()->forget('applied_coupon');
            session()->forget('applied_voucher');
            session()->forget('active_abandoned_cart_token');

            $savedOrderItems = DB::table($itemsTable)->where('order_id', $orderId)->get();
            $digitalDetails = DigitalProductService::getOrderDigitalDetails($orderId);

            // =========================================================================
            // 7. ORDER NOTIFICATIONS (CUSTOMER SMS, ADMIN ALERT SMS & CONTROLLED EMAILS)
            // =========================================================================
            $this->dispatchOrderPlacedNotifications($savedOrder, $savedOrderItems, $digitalDetails);

            $methodName = strtolower($request->payment_method ?? '');
            $isGateway = ($request->input('payment_type') === 'gateway') || str_contains($methodName, 'pgw') || str_contains($methodName, 'api') || str_contains($methodName, 'ssl') || str_contains($methodName, 'direct');

            if ($isGateway) {
                if (str_contains($methodName, 'ssl')) {
                    return response()->json(['status' => 'success', 'redirect' => route('payment.sslcommerz.pay', $invoiceNo)]);
                } elseif (str_contains($methodName, 'bkash')) {
                    return response()->json(['status' => 'success', 'redirect' => route('payment.bkash.pay', $invoiceNo)]);
                } elseif (str_contains($methodName, 'nagad')) {
                    return response()->json(['status' => 'success', 'redirect' => route('payment.nagad.pay', $invoiceNo)]);
                } elseif (str_contains($methodName, 'upay')) {
                    return response()->json(['status' => 'success', 'redirect' => route('payment.upay.pay', $invoiceNo)]);
                } elseif (str_contains($methodName, 'eps')) {
                    return response()->json(['status' => 'success', 'redirect' => route('payment.eps.pay', $invoiceNo)]);
                } elseif (str_contains($methodName, 'shurjo')) {
                    return response()->json(['status' => 'success', 'redirect' => route('payment.shurjopay.pay', $invoiceNo)]);
                } elseif (str_contains($methodName, 'stripe')) {
                    return response()->json(['status' => 'success', 'redirect' => route('payment.stripe.pay', $invoiceNo)]);
                } elseif (str_contains($methodName, 'paypal')) {
                    return response()->json(['status' => 'success', 'redirect' => route('payment.paypal.pay', $invoiceNo)]);
                }
            }

            return response()->json([
                'status'     => 'success',
                'invoice_no' => $invoiceNo,
                'redirect'   => route('order.confirmation', $invoiceNo)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Checkout process order failed: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Dispatch Customer Order Placed SMS, Admin Order Alert SMS, and Controlled Order Emails
     */
    protected function dispatchOrderPlacedNotifications($order, $orderItems, $digitalDetails): void
    {
        // -------------------------------------------------------------
        // A. CUSTOMER ORDER PLACED SMS (Stage 1 Notification)
        // -------------------------------------------------------------
        try {
            $customerPhone = $order->shipping_phone ?? ($order->customer_phone ?? ($order->phone ?? null));
            $isCustomerSmsEnabled = !class_exists(SmsSetting::class) || SmsSetting::isEnabled('auto_sms_order_placed_customer', true);

            if (!empty($customerPhone) && $isCustomerSmsEnabled && class_exists(SmsService::class)) {
                /** @var SmsService $smsService */
                $smsService = app(SmsService::class);
                $smsService->sendTemplate('order_placed', $customerPhone, [
                    'customer_name' => $order->shipping_name ?? ($order->customer_name ?? ($order->name ?? 'Valued Customer')),
                    'order_id'      => $order->id,
                    'invoice_no'    => $order->invoice_no ?? $order->id,
                    'order_total'   => number_format((float) ($order->grand_total ?? 0), 2),
                    'order_status'  => 'PLACED',
                    'courier_name'  => $order->courier_name ?? 'Courier',
                    'tracking_code' => $order->courier_tracking_id ?? ($order->parcel_id ?? 'N/A'),
                    'website_url'   => url('/'),
                ], [
                    'order_id' => $order->id,
                    'user_id'  => $order->user_id ?? null,
                ]);
            }
        } catch (Throwable $smsEx) {
            Log::warning("Customer Order Placed SMS failed for Order #{$order->id}: " . $smsEx->getMessage());
        }

        // -------------------------------------------------------------
        // B. ADMIN NEW ORDER ALERT SMS
        // -------------------------------------------------------------
        try {
            $isAdminAlertEnabled = class_exists(SmsSetting::class) && SmsSetting::isEnabled('auto_sms_admin_new_order', true);
            $adminPhone = class_exists(SmsSetting::class) ? trim((string) SmsSetting::getVal('admin_notification_phone', '')) : '';

            if ($isAdminAlertEnabled && !empty($adminPhone) && class_exists(SmsService::class)) {
                /** @var SmsService $smsService */
                $smsService = app(SmsService::class);
                $storeName = config('app.name', 'SHOP NOW BD');
                $custName = $order->shipping_name ?? ($order->customer_name ?? ($order->name ?? 'Customer'));
                $custPhone = $order->shipping_phone ?? ($order->customer_phone ?? ($order->phone ?? 'N/A'));
                $totalAmt = number_format((float) ($order->grand_total ?? 0), 2);
                $invoice = $order->invoice_no ?? $order->id;

                $adminMsg = "New Order Alert [{$storeName}]! Order: #{$invoice}, Total: ৳{$totalAmt}, Customer: {$custName} ({$custPhone}).";
                $smsService->send($adminPhone, $adminMsg, 'transactional', [
                    'order_id'      => $order->id,
                    'bypass_global' => true,
                ]);
            }
        } catch (Throwable $adminSmsEx) {
            Log::warning("Admin Order Alert SMS failed for Order #{$order->id}: " . $adminSmsEx->getMessage());
        }

        // -------------------------------------------------------------
        // C. CUSTOMER ORDER PLACED EMAIL (Controlled by Settings)
        // -------------------------------------------------------------
        $isGlobalEmailEnabled = !class_exists(SmsSetting::class) || SmsSetting::isEnabled('global_email_enabled', true);
        $isCustomerEmailEnabled = !class_exists(SmsSetting::class) || SmsSetting::isEnabled('auto_email_order_placed', true);

        $recipientEmail = $order->customer_email ?? ($order->shipping_email ?? ($order->email ?? null));
        if ($recipientEmail && filter_var($recipientEmail, FILTER_VALIDATE_EMAIL) && $isGlobalEmailEnabled && $isCustomerEmailEnabled) {
            try {
                Mail::to($recipientEmail)->send(new OrderPlacedMail($order, $orderItems, $digitalDetails));
            } catch (Throwable $mailEx) {
                Log::error('Customer order email failed: ' . $mailEx->getMessage());
            }
        }

        // -------------------------------------------------------------
        // D. ADMIN NEW ORDER ALERT EMAIL (Controlled by Settings)
        // -------------------------------------------------------------
        $isAdminEmailEnabled = !class_exists(SmsSetting::class) || SmsSetting::isEnabled('auto_email_admin_new_order', true);
        if ($isGlobalEmailEnabled && $isAdminEmailEnabled) {
            try {
                $adminEmail = config('mail.admin_address', 'info.srabonit@gmail.com');
                Mail::to($adminEmail)->send(new AdminOrderNotificationMail($order, $orderItems));
            } catch (Throwable $adminMailEx) {
                Log::error('Admin order alert email failed: ' . $adminMailEx->getMessage());
            }
        }
    }

    public function confirmation($invoiceNo)
    {
        $order = DB::table('orders')->where('invoice_no', $invoiceNo)->first();
        if (!$order) return redirect()->route('home')->with('error', 'Invoice not found!');

        // কনফার্মেশন পেজে সেশন নিশ্চিতভাবে রিমুভ করা
        session()->forget('cart');
        session()->forget('applied_coupon');
        session()->forget('applied_voucher');
        session()->forget('active_abandoned_cart_token');

        // সেফটি নেট: পেন্ডিং কার্ট থাকলে কনফার্মেশন থেকেও রিকভার্ড মার্ক করা
        if (Schema::hasTable('email_abandoned_carts')) {
            try {
                $phone = $order->customer_phone ?? ($order->phone ?? null);
                $cleanPhone = preg_replace('/[^0-9]/', '', $phone ?? '');
                $email = $order->customer_email ?? ($order->email ?? null);

                EmailAbandonedCart::where('is_recovered', false)
                    ->where(function ($q) use ($phone, $cleanPhone, $email) {
                        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $q->orWhere('email', $email);
                        }
                        if ($phone) {
                            $q->orWhere('phone', $phone);
                            $q->orWhere('email', $phone . '@guest.shopnowbd.online');
                        }
                        if ($cleanPhone && $cleanPhone !== $phone) {
                            $q->orWhere('phone', $cleanPhone);
                        }
                    })
                    ->update([
                        'is_recovered'       => true,
                        'recovered_at'       => now(),
                        'recovered_order_id' => $order->id,
                    ]);
            } catch (\Throwable $e) {}
        }

        // সেফটি নেট: কনফার্মেশন পেজ থেকেও সাবস্ক্রাইবার সিঙ্ক নিশ্চিত করা
        $email = $order->customer_email ?? ($order->email ?? null);
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL) && class_exists(EmailSubscriber::class)) {
            try {
                $email = strtolower(trim($email));
                $sub = EmailSubscriber::firstOrNew(['email' => $email]);
                $name = $order->customer_name ?? ($order->name ?? '');
                $phone = $order->customer_phone ?? ($order->phone ?? null);

                if (empty($sub->name)) $sub->name = $name;
                if (empty($sub->phone)) $sub->phone = $phone;
                $sub->source = 'checkout';
                $sub->consent_source = 'checkout';
                if ($sub->status !== 'unsubscribed') {
                    $sub->status = 'subscribed';
                }
                $sub->marketing_consent = true;
                $sub->consent_at = now();
                $sub->consent_ip = request()->ip() ?: '127.0.0.1';
                $sub->save();
            } catch (\Throwable $e) {}
        }

        if (in_array(strtolower($order->payment_status ?? ''), ['paid', 'completed'])) {
            DigitalProductService::fulfillDigitalOrder($order->id);
        }

        $digitalDetails = DigitalProductService::getOrderDigitalDetails($order->id);

        $itemsTable = $this->resolveItemsTable();
        $itemsQuery = DB::table($itemsTable)->where('order_id', $order->id);
        if (Schema::hasTable('products') && Schema::hasColumn($itemsTable, 'product_id')) {
            $itemsQuery->leftJoin('products', "{$itemsTable}.product_id", '=', 'products.id')
                ->select("{$itemsTable}.*", 'products.title as product_table_title');
        }
        $orderItems = $itemsQuery->get();

        $viewName = view()->exists('frontend.order-confirmation') ? 'frontend.order-confirmation' : 'frontend.confirmation';
        return view($viewName, compact('order', 'orderItems', 'digitalDetails'));
    }
}