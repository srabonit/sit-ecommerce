<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Services\ActivityLogger;
use App\Services\BranchAccessService;
use App\Services\BranchProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BranchProductController extends Controller
{
    /**
     * ১. ব্রাঞ্চভিত্তিক প্রোডাক্ট ক্যাটালগ ও স্টক তালিকা (নিরাপদ ফিল্টার ও AJAX সার্চ)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $selectableBranches = BranchAccessService::getSelectableBranches($user);

        $defaultBranchId = BranchAccessService::getCurrentBranchId($user) 
            ?: ($selectableBranches->first()->id ?? null);

        $branchId = (int) $request->get('branch_id', $defaultBranchId);

        if (!$branchId || !BranchAccessService::canAccessBranch($user, $branchId)) {
            abort(403, 'Unauthorized access to the requested branch product catalog.');
        }

        $branch = Branch::findOrFail($branchId);

        $query = Product::with([
            'category',
            'variants' => function ($vq) {
                if (Schema::hasColumn('product_variants', 'is_active')) {
                    $vq->where('is_active', true);
                } elseif (Schema::hasColumn('product_variants', 'status')) {
                    $vq->where('status', 'active');
                }
                $vq->orderBy('id', 'asc');
            },
            'stocks' => function ($sq) use ($branchId) {
                $sq->where('branch_id', $branchId);
            },
            'branches' => function ($bq) use ($branchId) {
                $bq->where('branches.id', $branchId);
            },
            'branchPrices' => function ($pq) use ($branchId) {
                $pq->where('branch_id', $branchId);
            },
        ]);

        // ১. Availability Mode / Policy Filter
        $rawMode = trim((string)($request->input('availability_mode', $request->input('mode', ''))));
        if (!empty($rawMode) && !in_array($rawMode, ['all_modes', 'all_policies', 'all_catalog'], true)) {
            if ($rawMode === 'all' || $rawMode === 'all_outlets') {
                $query->where(function ($mq) {
                    $mq->whereNull('products.branch_availability_mode')
                       ->orWhere('products.branch_availability_mode', 'all');
                });
            } elseif ($rawMode === 'main_only') {
                $query->whereIn('products.branch_availability_mode', ['main_only', 'head_office_only']);
            } elseif (in_array($rawMode, ['selected', 'restricted', 'pos_only', 'branch_only', 'head_office_only'], true)) {
                $query->where('products.branch_availability_mode', $rawMode);
            }
        }

        // ২. Intelligent Multi-Keyword Search (ID, SKU, Barcode, Title, Name, Variants)
        $searchTerm = trim((string)($request->input('search', $request->input('q', ''))));
        if ($searchTerm !== '') {
            $cleanId = ltrim($searchTerm, '#');
            $query->where(function ($q) use ($searchTerm, $cleanId) {
                if (is_numeric($cleanId)) {
                    $q->where('products.id', (int)$cleanId);
                }

                if (Schema::hasColumn('products', 'title')) {
                    $q->orWhere('products.title', 'like', "%{$searchTerm}%");
                }
                if (Schema::hasColumn('products', 'name')) {
                    $q->orWhere('products.name', 'like', "%{$searchTerm}%");
                }
                if (Schema::hasColumn('products', 'sku')) {
                    $q->orWhere('products.sku', 'like', "%{$searchTerm}%");
                }
                if (Schema::hasColumn('products', 'product_code')) {
                    $q->orWhere('products.product_code', 'like', "%{$searchTerm}%");
                }
                if (Schema::hasColumn('products', 'barcode')) {
                    $q->orWhere('products.barcode', 'like', "%{$searchTerm}%");
                }

                $q->orWhereHas('variants', function ($vq) use ($searchTerm) {
                    if (Schema::hasColumn('product_variants', 'sku')) {
                        $vq->where('sku', 'like', "%{$searchTerm}%");
                    }
                    if (Schema::hasColumn('product_variants', 'barcode')) {
                        $vq->orWhere('barcode', 'like', "%{$searchTerm}%");
                    }
                    if (Schema::hasColumn('product_variants', 'product_code')) {
                        $vq->orWhere('product_code', 'like', "%{$searchTerm}%");
                    }
                });
            });
        }

        $perPage = (int) $request->get('per_page', 20);
        $products = $query->latest('products.id')->paginate($perPage)->withQueryString();

        // লাইভ ব্রাঞ্চ প্রাইস এবং ব্রাঞ্চ স্টক যুক্ত করা
        $products->getCollection()->transform(function ($product) use ($branchId) {
            $product->is_sellable_in_branch = BranchProductService::isProductSellableInBranch($product, $branchId);
            $pricing = BranchProductService::resolvePrice($product, $branchId);
            $product->branch_effective_price = $pricing['effective_price'];
            $product->is_price_overridden = $pricing['is_branch_override'];
            $product->branch_stock = $product->getBranchStock($branchId);
            return $product;
        });

        // ৩. AJAX রেসপন্স (JSON ও Plain HTML উভয় ক্লায়েন্টের জন্য প্রস্তুত)
        if ($request->ajax() || $request->wantsJson()) {
            $tableHtml = view('admin.branch_product.partials.product_rows', compact('products', 'branch'))->render();
            $paginationHtml = $products->links()->render();
            $totalCount = $products->total();

            return response()->json([
                'success'         => true,
                'html'            => $tableHtml,
                'pagination_html' => $paginationHtml,
                'total'           => $totalCount,
                'showing_text'    => "Showing " . ($products->firstItem() ?? 0) . " to " . ($products->lastItem() ?? 0) . " of " . $totalCount . " items",
            ]);
        }

        return view('admin.branch_product.index', compact('products', 'branch', 'selectableBranches'));
    }

    /**
     * ২. প্রোডাক্টের ব্রাঞ্চ অ্যাক্সেস মোড ও অ্যাসাইনমেন্ট কনফিগারেশন ভিউ
     */
    public function editAvailability($productId)
    {
        $product = Product::with(['branches', 'variants'])->findOrFail($productId);
        $branches = Branch::active()->orderBy('name', 'asc')->get();

        $assignedBranchIds = $product->branches->pluck('id')->toArray();
        $pivotSettings = [];

        foreach ($product->branches as $b) {
            $pivotSettings[$b->id] = [
                'can_sell'        => (bool) $b->pivot->can_sell,
                'is_pos_enabled'  => (bool) $b->pivot->is_pos_enabled,
                'low_stock_alert' => $b->pivot->low_stock_alert,
                'branch_sku'      => $b->pivot->branch_sku,
            ];
        }

        return view('admin.branch_product.availability', compact('product', 'branches', 'assignedBranchIds', 'pivotSettings'));
    }

    /**
     * ৩. প্রোডাক্টের ব্রাঞ্চ অ্যাক্সেস পলিসি ও অ্যাসাইনমেন্ট সংরক্ষণ
     */
    public function updateAvailability(Request $request, $productId)
    {
        $request->validate([
            'branch_availability_mode' => 'required|in:all,selected,main_only,head_office_only,restricted',
            'branches'                 => 'nullable|array',
            'branches.*'               => 'exists:branches,id',
            'pivot_data'               => 'nullable|array',
        ]);

        $product = Product::findOrFail($productId);
        $mode = $request->input('branch_availability_mode');

        $product->branch_availability_mode = $mode;
        $product->save();

        if (in_array($mode, ['selected', 'restricted'])) {
            $selectedBranches = $request->input('branches', []);
            $pivotSettings = $request->input('pivot_data', []);

            $syncData = [];
            foreach ($selectedBranches as $bId) {
                $bId = (int) $bId;
                $row = $pivotSettings[$bId] ?? [];
                $syncData[$bId] = [
                    'is_active'       => true,
                    'can_sell'        => isset($row['can_sell']) ? (bool)$row['can_sell'] : true,
                    'is_pos_enabled'  => isset($row['is_pos_enabled']) ? (bool)$row['is_pos_enabled'] : true,
                    'low_stock_alert' => !empty($row['low_stock_alert']) ? (int)$row['low_stock_alert'] : ($product->alert_quantity ?? 5),
                    'branch_sku'      => !empty($row['branch_sku']) ? trim($row['branch_sku']) : null,
                    'updated_at'      => now(),
                ];
            }

            $product->branches()->sync($syncData);
        }

        if (class_exists(ActivityLogger::class)) {
            ActivityLogger::log('product_availability_updated', 'product', "Product #{$product->id} availability set to [{$mode}]", [
                'record_id'  => $product->id,
                'record_ref' => $product->sku ?? $product->title,
                'new_values' => ['mode' => $mode],
            ]);
        }

        return redirect()->back()->with('success', 'Product branch availability settings updated successfully.');
    }

    /**
     * ৪. ব্রাঞ্চভিত্তিক প্রাইস ওভাররাইড ম্যানেজমেন্ট ভিউ
     */
    public function editPricing($branchId, $productId)
    {
        $branch = Branch::findOrFail($branchId);
        $product = Product::with(['variants', 'branchPrices' => function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        }])->findOrFail($productId);

        $basePriceRecord = $product->branchPrices->firstWhere('variant_id', null);

        $variantPrices = [];
        foreach ($product->variants as $variant) {
            $vOverride = $product->branchPrices->firstWhere('variant_id', $variant->id);
            $variantPrices[$variant->id] = [
                'variant'  => $variant,
                'override' => $vOverride,
            ];
        }

        return view('admin.branch_product.pricing', compact('branch', 'product', 'basePriceRecord', 'variantPrices'));
    }

    /**
     * ৫. ব্রাঞ্চভিত্তিক প্রাইস ওভাররাইড সংরক্ষণ ও আপডেট
     */
    public function updatePricing(Request $request, $branchId, $productId)
    {
        $request->validate([
            'base_price'              => 'nullable|numeric|min:0',
            'base_sale_price'         => 'nullable|numeric|min:0',
            'base_wholesale_price'    => 'nullable|numeric|min:0',
            'base_min_selling_price'  => 'nullable|numeric|min:0',
            'variants'                => 'nullable|array',
            'variants.*.price'        => 'nullable|numeric|min:0',
            'variants.*.sale_price'   => 'nullable|numeric|min:0',
        ]);

        $product = Product::findOrFail($productId);

        BranchProductService::setBranchPrice($branchId, $productId, null, [
            'price'             => $request->base_price,
            'sale_price'        => $request->base_sale_price,
            'wholesale_price'   => $request->base_wholesale_price,
            'min_selling_price' => $request->base_min_selling_price,
        ]);

        if ($request->has('variants') && is_array($request->variants)) {
            foreach ($request->variants as $variantId => $vData) {
                BranchProductService::setBranchPrice($branchId, $productId, (int)$variantId, [
                    'price'             => $vData['price'] ?? null,
                    'sale_price'        => $vData['sale_price'] ?? null,
                    'wholesale_price'   => $vData['wholesale_price'] ?? null,
                    'min_selling_price' => $vData['min_selling_price'] ?? null,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Branch-specific prices updated successfully.');
    }

    /**
     * ৬. পিওএস টার্মিনাল অ্যাক্সেস টগল (AJAX)
     */
    public function togglePosStatus(Request $request, $branchId, $productId)
    {
        $pivot = DB::table('branch_product')
            ->where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->first();

        if (!$pivot) {
            DB::table('branch_product')->insert([
                'branch_id'      => $branchId,
                'product_id'     => $productId,
                'is_active'      => true,
                'can_sell'       => true,
                'is_pos_enabled' => false,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
            $newState = false;
        } else {
            $newState = !$pivot->is_pos_enabled;
            DB::table('branch_product')
                ->where('branch_id', $branchId)
                ->where('product_id', $productId)
                ->update([
                    'is_pos_enabled' => $newState,
                    'updated_at'     => now(),
                ]);
        }

        return response()->json([
            'success'        => true,
            'is_pos_enabled' => $newState,
            'message'        => $newState ? 'Product enabled on branch POS.' : 'Product hidden from branch POS.',
        ]);
    }

    /**
     * ৭. ব্রাঞ্চে প্রোডাক্ট বিক্রি অনুমোদন টগল (AJAX Can Sell)
     */
    public function toggleCanSell(Request $request, $branchId, $productId)
    {
        $pivot = DB::table('branch_product')
            ->where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->first();

        if (!$pivot) {
            DB::table('branch_product')->insert([
                'branch_id'      => $branchId,
                'product_id'     => $productId,
                'is_active'      => true,
                'can_sell'       => false,
                'is_pos_enabled' => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
            $newState = false;
        } else {
            $newState = !$pivot->can_sell;
            DB::table('branch_product')
                ->where('branch_id', $branchId)
                ->where('product_id', $productId)
                ->update([
                    'can_sell'   => $newState,
                    'updated_at' => now(),
                ]);
        }

        return response()->json([
            'success'  => true,
            'can_sell' => $newState,
            'message'  => $newState ? 'Product is now sellable in this branch.' : 'Product sale restricted in this branch.',
        ]);
    }

    /**
     * ৮. বাল্ক প্রোডাক্ট অ্যাসাইনমেন্ট (Bulk Assign Products to Branch)
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'branch_id'   => 'required|exists:branches,id',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
            'action'      => 'required|in:assign,remove',
        ]);

        $branchId = (int) $request->branch_id;
        $productIds = $request->product_ids;
        $action = $request->action;

        DB::transaction(function () use ($branchId, $productIds, $action) {
            foreach ($productIds as $prodId) {
                if ($action === 'assign') {
                    DB::table('branch_product')->updateOrInsert(
                        ['branch_id' => $branchId, 'product_id' => $prodId],
                        [
                            'is_active'      => true,
                            'can_sell'       => true,
                            'is_pos_enabled' => true,
                            'updated_at'     => now(),
                        ]
                    );
                } else {
                    DB::table('branch_product')
                        ->where('branch_id', $branchId)
                        ->where('product_id', $prodId)
                        ->delete();
                }
            }
        });

        $count = count($productIds);
        $msg = $action === 'assign' 
            ? "{$count} products successfully assigned to branch." 
            : "{$count} products removed from branch.";

        return response()->json(['success' => true, 'message' => $msg]);
    }
}