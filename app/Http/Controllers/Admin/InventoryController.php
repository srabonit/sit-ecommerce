<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\InventoryService;
use App\Services\ImagePathService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    /**
     * Universal Image Resolver Helper for Inventory Module
     */
    protected function resolveInventoryThumb(?string $path): string
    {
        $fallback = 'https://placehold.co/50x50?text=Item';
        if (empty($path)) {
            return $fallback;
        }
        if (class_exists(ImagePathService::class)) {
            return ImagePathService::resolveUrl($path, $fallback);
        }
        if (function_exists('show_image')) {
            return show_image($path, $fallback);
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        return asset(ltrim(str_replace(['public_html/', 'public/'], '', $path), '/'));
    }

    /**
     * 1. INVENTORY DASHBOARD
     * High-level executive metrics, valuation health & quick action insights
     */
    public function dashboard(Request $request)
    {
        $valuation = InventoryService::getInventoryValuation();
        $today = Carbon::today();

        // Today's Movements Metrics
        $todayMovements = DB::table('inventory_movements')
            ->whereDate('created_at', $today)
            ->selectRaw("
                SUM(CASE WHEN quantity_change > 0 THEN quantity_change ELSE 0 END) as stock_in_units,
                SUM(CASE WHEN quantity_change > 0 THEN quantity_change * COALESCE(unit_cost, 0) ELSE 0 END) as stock_in_val,
                SUM(CASE WHEN quantity_change < 0 THEN ABS(quantity_change) ELSE 0 END) as stock_out_units,
                SUM(CASE WHEN quantity_change < 0 THEN ABS(quantity_change) * COALESCE(unit_cost, 0) ELSE 0 END) as stock_out_val,
                SUM(CASE WHEN movement_type IN ('checkout_sale', 'pos_sale', 'order_processing_deduct') THEN ABS(quantity_change) ELSE 0 END) as sales_units,
                SUM(CASE WHEN movement_type IN ('checkout_sale', 'pos_sale', 'order_processing_deduct') THEN ABS(quantity_change) * COALESCE(unit_price, 0) ELSE 0 END) as sales_rev,
                SUM(CASE WHEN movement_type IN ('checkout_sale', 'pos_sale', 'order_processing_deduct') THEN ABS(quantity_change) * COALESCE(unit_cost, 0) ELSE 0 END) as sales_cogs
            ")
            ->first();

        // Catalog Counts
        $totalProducts = Schema::hasTable('products') ? DB::table('products')->count() : 0;
        $totalVariants = Schema::hasTable('product_variants') ? DB::table('product_variants')->count() : 0;

        // Stock Status Counts (Zero double-counting: only Simple Products + Variants)
        $hasVariantsTable = Schema::hasTable('product_variants');
        $variableProductIds = $hasVariantsTable 
            ? DB::table('product_variants')->distinct()->pluck('product_id')->toArray() 
            : [];

        $hasProdAlert = Schema::hasColumn('products', 'alert_quantity');
        $hasVarAlert  = $hasVariantsTable && Schema::hasColumn('product_variants', 'alert_quantity');

        $outOfStockSimple = 0;
        $lowStockSimple = 0;
        if (Schema::hasTable('products')) {
            $simpleQuery = DB::table('products');
            if (!empty($variableProductIds)) {
                $simpleQuery->whereNotIn('id', $variableProductIds);
            }
            $outOfStockSimple = (clone $simpleQuery)->where('stock', '<=', 0)->count();
            $pAlertExpr = $hasProdAlert ? 'stock <= COALESCE(alert_quantity, 5)' : 'stock <= 5';
            $lowStockSimple = (clone $simpleQuery)->where('stock', '>', 0)->whereRaw($pAlertExpr)->count();
        }

        $outOfStockVariants = 0;
        $lowStockVariants = 0;
        if ($hasVariantsTable) {
            $outOfStockVariants = DB::table('product_variants')->where('stock', '<=', 0)->count();
            $vAlertExpr = $hasVarAlert ? 'stock <= COALESCE(alert_quantity, 5)' : 'stock <= 5';
            $lowStockVariants = DB::table('product_variants')->where('stock', '>', 0)->whereRaw($vAlertExpr)->count();
        }

        $lowStockCount   = $lowStockSimple + $lowStockVariants;
        $outOfStockCount = $outOfStockSimple + $outOfStockVariants;

        // Recent 8 Movements
        $recentMovements = DB::table('inventory_movements')
            ->leftJoin('products', 'inventory_movements.product_id', '=', 'products.id')
            ->leftJoin('product_variants', 'inventory_movements.variant_id', '=', 'product_variants.id')
            ->leftJoin('users', 'inventory_movements.created_by', '=', 'users.id')
            ->select(
                'inventory_movements.*',
                'products.title as product_title',
                'products.thumbnail as product_thumbnail',
                'product_variants.color as variant_color',
                'product_variants.size as variant_size',
                'users.name as creator_name'
            )
            ->latest('inventory_movements.id')
            ->take(8)
            ->get();

        $warehouses = Schema::hasTable('warehouses') ? DB::table('warehouses')->get() : collect();
        $branches   = Schema::hasTable('branches') ? DB::table('branches')->get() : collect();

        return view('admin.inventory.dashboard', compact(
            'valuation',
            'todayMovements',
            'totalProducts',
            'totalVariants',
            'lowStockCount',
            'outOfStockCount',
            'recentMovements',
            'warehouses',
            'branches'
        ));
    }

    /**
     * 2. STOCK IN VIEW & HANDLER
     */
    public function stockIn(Request $request)
    {
        $suppliersQuery = DB::table('suppliers');
        if (Schema::hasTable('suppliers') && Schema::hasColumn('suppliers', 'status')) {
            $suppliersQuery->where('status', 'active');
        }
        $suppliers = Schema::hasTable('suppliers') ? $suppliersQuery->orderBy('name', 'asc')->get() : collect();

        $warehouses = Schema::hasTable('warehouses') ? DB::table('warehouses')->get() : collect();
        $branches   = Schema::hasTable('branches') ? DB::table('branches')->get() : collect();

        $defaultPoNumber = 'PO-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        return view('admin.inventory.stock_in', compact('suppliers', 'warehouses', 'branches', 'defaultPoNumber'));
    }

    public function storeStockIn(Request $request)
    {
        $request->validate([
            'product_id'          => 'required|integer|exists:products,id',
            'variant_id'          => 'nullable|integer',
            'quantity'            => 'required|integer|min:1',
            'purchase_cost'       => 'nullable|numeric|min:0',
            'selling_price'       => 'nullable|numeric|min:0',
            'supplier_id'         => 'nullable|integer',
            'warehouse_id'        => 'nullable|integer',
            'branch_id'           => 'nullable|integer',
            'batch_no'            => 'nullable|string|max:100',
            'expiry_date'         => 'nullable|date',
            'supplier_invoice_no' => 'nullable|string|max:100',
            'reference_id'        => 'nullable|string|max:100',
            'movement_type'       => 'required|string|in:purchase_in,opening_stock,customer_return,supplier_replacement,stock_correction,other_addition',
            'stock_in_date'       => 'nullable|date',
            'update_wac'          => 'nullable|boolean',
            'notes'               => 'nullable|string|max:1000',
        ]);

        $productId   = (int) $request->product_id;
        $variantId   = $request->filled('variant_id') ? (int) $request->variant_id : null;
        $quantity    = (int) $request->quantity;
        $cost        = $request->filled('purchase_cost') ? (float) $request->purchase_cost : null;
        $selling     = $request->filled('selling_price') ? (float) $request->selling_price : null;
        $warehouseId = $request->filled('warehouse_id') ? (int) $request->warehouse_id : null;
        $branchId    = $request->filled('branch_id') ? (int) $request->branch_id : null;
        $batchNo     = $request->input('batch_no');
        $expiryDate  = $request->input('expiry_date');

        $refId = $request->filled('reference_id') 
            ? trim($request->reference_id) 
            : ('IN-' . date('Ymd') . '-' . strtoupper(Str::random(5)));
            
        $token = 'STKIN-' . md5($productId . '-' . ($variantId ?? 0) . '-' . $refId . '-' . $quantity . '-' . ($cost ?? 0));

        try {
            $result = InventoryService::stockIn(
                $productId,
                $variantId,
                $quantity,
                $cost,
                $request->input('movement_type', 'purchase_in'),
                $refId,
                'stock_in_form',
                $selling,
                $request->filled('supplier_id') ? (int) $request->supplier_id : null,
                Auth::id(),
                $request->input('notes'),
                $request->boolean('update_wac', true),
                $token,
                $warehouseId,
                $branchId,
                $batchNo,
                $expiryDate
            );

            // Optional automated Purchase entry if supplier selected
            if (Schema::hasTable('purchases') && Schema::hasTable('purchase_items') && $request->filled('supplier_id')) {
                $purchaseCols = Schema::getColumnListing('purchases');
                $purchaseData = [
                    'supplier_id'         => (int) $request->supplier_id,
                    'supplier_invoice_no' => $request->input('supplier_invoice_no'),
                    'purchase_date'       => $request->input('stock_in_date', now()->toDateString()),
                    'total_amount'        => round(($cost ?? 0) * $quantity, 2),
                    'grand_total'         => round(($cost ?? 0) * $quantity, 2),
                    'paid_amount'         => 0.00,
                    'due_amount'          => round(($cost ?? 0) * $quantity, 2),
                    'payment_status'      => 'unpaid',
                    'status'              => 'received',
                    'notes'               => $request->input('notes'),
                    'created_by'          => Auth::id(),
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ];

                if (in_array('purchase_number', $purchaseCols)) {
                    $purchaseData['purchase_number'] = $refId;
                }
                if (in_array('purchase_no', $purchaseCols)) {
                    $purchaseData['purchase_no'] = $refId;
                }
                if (in_array('warehouse_id', $purchaseCols)) {
                    $purchaseData['warehouse_id'] = $warehouseId;
                }
                if (in_array('branch_id', $purchaseCols)) {
                    $purchaseData['branch_id'] = $branchId;
                }

                $purchaseId = DB::table('purchases')->insertGetId($purchaseData);

                $itemCols = Schema::getColumnListing('purchase_items');
                $itemData = [
                    'purchase_id' => $purchaseId,
                    'product_id'  => $productId,
                    'variant_id'  => $variantId,
                    'quantity'    => $quantity,
                    'unit_cost'   => round($cost ?? 0, 2),
                    'subtotal'    => round(($cost ?? 0) * $quantity, 2),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];

                if (in_array('batch_no', $itemCols)) {
                    $itemData['batch_no'] = $batchNo;
                }
                if (in_array('expiry_date', $itemCols)) {
                    $itemData['expiry_date'] = $expiryDate;
                }

                DB::table('purchase_items')->insert($itemData);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully stocked in {$quantity} units for Reference #{$refId}.",
                    'data'    => $result,
                ]);
            }

            return redirect()->route('admin.inventory.stock_in')->with('success', "Stock In of {$quantity} units recorded successfully.");
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * 3. NON-SALE STOCK OUT VIEW & HANDLER
     */
    public function stockOut(Request $request)
    {
        $defaultOutRef = 'OUT-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        $warehouses    = Schema::hasTable('warehouses') ? DB::table('warehouses')->get() : collect();
        $branches      = Schema::hasTable('branches') ? DB::table('branches')->get() : collect();

        return view('admin.inventory.stock_out', compact('defaultOutRef', 'warehouses', 'branches'));
    }

    public function storeStockOut(Request $request)
    {
        $request->validate([
            'product_id'    => 'required|integer|exists:products,id',
            'variant_id'    => 'nullable|integer',
            'quantity'      => 'required|integer|min:1',
            'warehouse_id'  => 'nullable|integer',
            'branch_id'     => 'nullable|integer',
            'batch_no'      => 'nullable|string|max:100',
            'movement_type' => 'required|string|in:damage_out,expired_out,supplier_return,internal_use,lost_stock,adjustment_out,other_removal',
            'reference_id'  => 'nullable|string|max:100',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $productId   = (int) $request->product_id;
        $variantId   = $request->filled('variant_id') ? (int) $request->variant_id : null;
        $quantity    = (int) $request->quantity;
        $warehouseId = $request->filled('warehouse_id') ? (int) $request->warehouse_id : null;
        $branchId    = $request->filled('branch_id') ? (int) $request->branch_id : null;
        $batchNo     = $request->input('batch_no');

        $refId = $request->filled('reference_id') 
            ? trim($request->reference_id) 
            : ('OUT-' . date('Ymd') . '-' . strtoupper(Str::random(5)));
            
        $token = 'STKOUT-' . md5($productId . '-' . ($variantId ?? 0) . '-' . $refId . '-' . $quantity);

        try {
            $result = InventoryService::stockOut(
                $productId,
                $variantId,
                $quantity,
                $request->input('movement_type', 'damage_out'),
                $refId,
                'stock_out_form',
                Auth::id(),
                $request->input('notes'),
                $token,
                $warehouseId,
                $branchId,
                $batchNo
            );

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully removed {$quantity} units for Reference #{$refId}.",
                    'data'    => $result,
                ]);
            }

            return redirect()->route('admin.inventory.stock_out')->with('success', "Stock removal of {$quantity} units recorded successfully.");
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * 4. STOCK BALANCE / CURRENT STOCK SHEET
     * Comprehensive catalog valuation & itemized variant breakdown (Schema Safe)
     */
    public function balance(Request $request)
    {
        $hasVariants = Schema::hasTable('product_variants');
        $variableProductIds = $hasVariants ? DB::table('product_variants')->distinct()->pluck('product_id')->toArray() : [];

        // Safe column checks for products
        $hasProdAlert = Schema::hasColumn('products', 'alert_quantity');
        $prodAlertSelect = $hasProdAlert ? "COALESCE(products.alert_quantity, 5)" : "5";

        $hasProdPCost = Schema::hasColumn('products', 'purchase_cost');
        $hasProdPPrice = Schema::hasColumn('products', 'purchase_price');
        $prodCostSelect = match(true) {
            $hasProdPCost && $hasProdPPrice => "COALESCE(products.purchase_cost, products.purchase_price, 0)",
            $hasProdPCost => "COALESCE(products.purchase_cost, 0)",
            $hasProdPPrice => "COALESCE(products.purchase_price, 0)",
            default => "0"
        };

        $hasProdSPrice = Schema::hasColumn('products', 'regular_price');
        $hasProdSalePrice = Schema::hasColumn('products', 'sale_price');
        $prodSellingSelect = match(true) {
            $hasProdSPrice && $hasProdSalePrice => "COALESCE(products.regular_price, products.sale_price, 0)",
            $hasProdSPrice => "COALESCE(products.regular_price, 0)",
            $hasProdSalePrice => "COALESCE(products.sale_price, 0)",
            default => "0"
        };

        $hasProdCode = Schema::hasColumn('products', 'product_code');
        $hasProdSku = Schema::hasColumn('products', 'sku');
        $prodSkuSelect = match(true) {
            $hasProdCode && $hasProdSku => "COALESCE(products.product_code, products.sku, '')",
            $hasProdCode => "COALESCE(products.product_code, '')",
            $hasProdSku => "COALESCE(products.sku, '')",
            default => "''"
        };

        // 1. Fetch Simple Products
        $simpleQuery = DB::table('products')
            ->whereNotIn('id', $variableProductIds)
            ->select(
                'products.id as product_id',
                'products.title as product_title',
                DB::raw("{$prodSkuSelect} as sku"),
                'products.thumbnail',
                'products.stock',
                DB::raw("{$prodCostSelect} as purchase_cost"),
                DB::raw("{$prodSellingSelect} as selling_price"),
                DB::raw("{$prodAlertSelect} as alert_quantity"),
                DB::raw("'simple' as item_type"),
                DB::raw("NULL as variant_id"),
                DB::raw("NULL as variant_attributes")
            );

        // 2. Fetch Variable Product Variants (Schema Safe)
        $variantQuery = null;
        if ($hasVariants) {
            $hasVarSku = Schema::hasColumn('product_variants', 'sku');
            $hasVarCode = Schema::hasColumn('product_variants', 'product_code');
            $varSkuSelect = match(true) {
                $hasVarCode && $hasVarSku => "COALESCE(product_variants.product_code, product_variants.sku, products.product_code, '')",
                $hasVarCode => "COALESCE(product_variants.product_code, products.product_code, '')",
                $hasVarSku => "COALESCE(product_variants.sku, products.product_code, '')",
                default => "COALESCE(products.product_code, '')"
            };

            $hasVarPCost = Schema::hasColumn('product_variants', 'purchase_cost');
            $hasVarPPrice = Schema::hasColumn('product_variants', 'purchase_price');
            $varCostSelect = match(true) {
                $hasVarPCost && $hasVarPPrice => "COALESCE(product_variants.purchase_cost, product_variants.purchase_price, products.purchase_cost, products.purchase_price, 0)",
                $hasVarPCost => "COALESCE(product_variants.purchase_cost, products.purchase_cost, products.purchase_price, 0)",
                $hasVarPPrice => "COALESCE(product_variants.purchase_price, products.purchase_cost, products.purchase_price, 0)",
                default => "COALESCE(products.purchase_cost, products.purchase_price, 0)"
            };

            $hasVarSPrice = Schema::hasColumn('product_variants', 'regular_price');
            $varSellingSelect = $hasVarSPrice 
                ? "COALESCE(product_variants.regular_price, products.regular_price, products.sale_price, 0)" 
                : "COALESCE(products.regular_price, products.sale_price, 0)";

            $hasVarAlert = Schema::hasColumn('product_variants', 'alert_quantity');
            $varAlertSelect = $hasVarAlert ? "COALESCE(product_variants.alert_quantity, 5)" : "5";

            $variantQuery = DB::table('product_variants')
                ->join('products', 'product_variants.product_id', '=', 'products.id')
                ->select(
                    'products.id as product_id',
                    'products.title as product_title',
                    DB::raw("{$varSkuSelect} as sku"),
                    'products.thumbnail',
                    'product_variants.stock',
                    DB::raw("{$varCostSelect} as purchase_cost"),
                    DB::raw("{$varSellingSelect} as selling_price"),
                    DB::raw("{$varAlertSelect} as alert_quantity"),
                    DB::raw("'variant' as item_type"),
                    'product_variants.id as variant_id',
                    DB::raw("CONCAT_WS(' / ', NULLIF(product_variants.color, ''), NULLIF(product_variants.size, '')) as variant_attributes")
                );
        }

        // Union Simple and Variant datasets
        $unionQuery = $variantQuery ? $simpleQuery->unionAll($variantQuery) : $simpleQuery;
        $mainQuery = DB::query()->fromSub($unionQuery, 'balance_items');

        // Search Filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $mainQuery->where(function ($q) use ($search) {
                $q->where('product_title', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%")
                  ->orWhere('variant_attributes', 'LIKE', "%{$search}%")
                  ->orWhere('product_id', $search);
            });
        }

        // Stock Status Filter
        if ($request->filled('stock_status') && $request->stock_status !== 'all') {
            $st = $request->stock_status;
            if ($st === 'out_of_stock') {
                $mainQuery->where('stock', '<=', 0);
            } elseif ($st === 'low_stock') {
                $mainQuery->where('stock', '>', 0)->whereRaw('stock <= alert_quantity');
            } elseif ($st === 'in_stock') {
                $mainQuery->whereRaw('stock > alert_quantity');
            }
        }

        // Summary Calculations (On entire filtered dataset)
        $summary = (clone $mainQuery)->selectRaw("
            COUNT(*) as total_items,
            SUM(stock) as total_units,
            SUM(stock * purchase_cost) as total_purchase_value,
            SUM(stock * selling_price) as total_selling_value
        ")->first();

        // 1-Click CSV Export
        if ($request->get('export') === 'csv' || $request->get('export') === 'excel') {
            return $this->exportBalanceToCsv($mainQuery, $summary);
        }

        // Dynamic Pagination
        $perPageInput = $request->input('per_page', 25);
        $perPage = ($perPageInput === 'all') ? 100000 : max(5, (int) $perPageInput);

        $items = $mainQuery->orderBy('product_id', 'desc')->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'success'         => true,
                'table_html'      => view('admin.inventory.partials.balance_rows', compact('items'))->render(),
                'pagination_html' => ($perPageInput === 'all' || !$items->hasPages()) ? '' : $items->links('pagination::bootstrap-5')->render(),
                'total_count'     => $items->total(),
                'summary'         => [
                    'total_items'          => number_format($summary->total_items ?? 0),
                    'total_units'          => number_format($summary->total_units ?? 0),
                    'total_purchase_value' => number_format($summary->total_purchase_value ?? 0, 2),
                    'total_selling_value'  => number_format($summary->total_selling_value ?? 0, 2),
                ],
            ]);
        }

        return view('admin.inventory.stock_balance', compact('items', 'perPage', 'summary'));
    }

    /**
     * 5. INVENTORY MOVEMENTS AUDIT TRAIL
     */
    public function movements(Request $request)
    {
        if (!Schema::hasTable('inventory_movements')) {
            return back()->with('error', 'The inventory_movements table does not exist in the database.');
        }

        $query = DB::table('inventory_movements')
            ->leftJoin('products', 'inventory_movements.product_id', '=', 'products.id')
            ->leftJoin('product_variants', 'inventory_movements.variant_id', '=', 'product_variants.id')
            ->leftJoin('users', 'inventory_movements.created_by', '=', 'users.id')
            ->select(
                'inventory_movements.*',
                'products.title as product_title',
                'products.thumbnail as product_thumbnail',
                'products.product_code as parent_code',
                'product_variants.color as variant_color',
                'product_variants.size as variant_size',
                'product_variants.product_code as variant_sku',
                'users.name as creator_name'
            );

        if ($request->filled('movement_type') && $request->movement_type !== 'all') {
            $query->where('inventory_movements.movement_type', $request->movement_type);
        }

        if ($request->filled('product_id')) {
            $query->where('inventory_movements.product_id', (int) $request->product_id);
        }

        if ($request->filled('warehouse_id') && Schema::hasColumn('inventory_movements', 'warehouse_id')) {
            $query->where('inventory_movements.warehouse_id', (int) $request->warehouse_id);
        }

        if ($request->filled('branch_id') && Schema::hasColumn('inventory_movements', 'branch_id')) {
            $query->where('inventory_movements.branch_id', (int) $request->branch_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $cleanRef = ltrim($search, '#');
            $query->where(function ($q) use ($search, $cleanRef) {
                $q->where('inventory_movements.reference_id', 'LIKE', "%{$cleanRef}%")
                  ->orWhere('inventory_movements.sku', 'LIKE', "%{$search}%")
                  ->orWhere('inventory_movements.notes', 'LIKE', "%{$search}%")
                  ->orWhere('products.title', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('date_filter')) {
            $dFilter = $request->date_filter;
            switch ($dFilter) {
                case 'today':
                    $query->whereDate('inventory_movements.created_at', Carbon::today());
                    break;
                case 'yesterday':
                    $query->whereDate('inventory_movements.created_at', Carbon::yesterday());
                    break;
                case '7_days':
                    $query->where('inventory_movements.created_at', '>=', Carbon::now()->subDays(7)->startOfDay());
                    break;
                case '30_days':
                    $query->where('inventory_movements.created_at', '>=', Carbon::now()->subDays(30)->startOfDay());
                    break;
                case 'this_month':
                    $query->whereBetween('inventory_movements.created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                    break;
                case 'last_month':
                    $query->whereBetween('inventory_movements.created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                    break;
                case 'custom':
                    if ($request->filled('start_date')) $query->where('inventory_movements.created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
                    if ($request->filled('end_date')) $query->where('inventory_movements.created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
                    break;
            }
        } elseif ($request->filled('start_date') || $request->filled('end_date')) {
            if ($request->filled('start_date')) $query->where('inventory_movements.created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
            if ($request->filled('end_date')) $query->where('inventory_movements.created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }

        $summaryQuery = clone $query;
        $totalAdditions = (int) (clone $summaryQuery)->where('inventory_movements.quantity_change', '>', 0)->sum('inventory_movements.quantity_change');
        $totalDeductions = (int) abs((clone $summaryQuery)->where('inventory_movements.quantity_change', '<', 0)->sum('inventory_movements.quantity_change'));
        $netChange = $totalAdditions - $totalDeductions;
        $costImpact = (float) (clone $summaryQuery)->sum(DB::raw('ABS(inventory_movements.quantity_change) * COALESCE(inventory_movements.unit_cost, 0)'));

        if ($request->get('export') === 'csv' || $request->get('export') === 'excel') {
            return $this->exportMovementsToCsv($query, $totalAdditions, $totalDeductions, $costImpact);
        }

        $perPageInput = $request->input('per_page', 25);
        $perPage = ($perPageInput === 'all') ? 100000 : max(5, (int) $perPageInput);

        $movements = $query->orderBy('inventory_movements.id', 'desc')->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'success'         => true,
                'table_html'      => view('admin.inventory.partials.movement_rows', compact('movements'))->render(),
                'pagination_html' => ($perPageInput === 'all' || !$movements->hasPages()) ? '' : $movements->links('pagination::bootstrap-5')->render(),
                'total_count'     => $movements->total(),
                'summary'         => [
                    'additions'   => number_format($totalAdditions),
                    'deductions'  => number_format($totalDeductions),
                    'net_change'  => ($netChange >= 0 ? '+' : '') . number_format($netChange),
                    'cost_impact' => number_format($costImpact, 2),
                ],
            ]);
        }

        $valuation = InventoryService::getInventoryValuation();

        return view('admin.inventory.movements', compact(
            'movements',
            'perPage',
            'totalAdditions',
            'totalDeductions',
            'netChange',
            'costImpact',
            'valuation'
        ));
    }

    /**
     * 6. SINGLE PRODUCT & VARIANT DETAILED STOCK VIEW
     */
    public function productStockDetails(Request $request, $id)
    {
        $product = DB::table('products')->where('id', $id)->first();
        if (!$product) {
            return redirect()->route('admin.inventory.balance')->with('error', 'Product not found.');
        }

        $variants = Schema::hasTable('product_variants')
            ? DB::table('product_variants')->where('product_id', $product->id)->get()
            : collect();

        // Warehouse & Branch breakdowns if product_stocks table exists
        $warehouseStocks = collect();
        if (Schema::hasTable('product_stocks')) {
            $wsQuery = DB::table('product_stocks')
                ->where('product_stocks.product_id', $product->id);

            if (Schema::hasTable('warehouses')) {
                $wsQuery->leftJoin('warehouses', 'product_stocks.warehouse_id', '=', 'warehouses.id')
                        ->addSelect('warehouses.name as warehouse_name');
            }
            if (Schema::hasTable('branches')) {
                $wsQuery->leftJoin('branches', 'product_stocks.branch_id', '=', 'branches.id')
                        ->addSelect('branches.name as branch_name');
            }

            $warehouseStocks = $wsQuery->addSelect('product_stocks.*')->get();
        }

        $selectedVariantId = $request->filled('variant_id') ? (int) $request->variant_id : null;

        $movementsQuery = DB::table('inventory_movements')
            ->leftJoin('product_variants', 'inventory_movements.variant_id', '=', 'product_variants.id')
            ->leftJoin('users', 'inventory_movements.created_by', '=', 'users.id')
            ->where('inventory_movements.product_id', $product->id)
            ->select(
                'inventory_movements.*',
                'product_variants.color as variant_color',
                'product_variants.size as variant_size',
                'users.name as creator_name'
            );

        if (!empty($selectedVariantId)) {
            $movementsQuery->where('inventory_movements.variant_id', $selectedVariantId);
        }

        $movements = $movementsQuery->orderBy('inventory_movements.id', 'desc')->paginate(25)->withQueryString();

        $aggregates = DB::table('inventory_movements')
            ->where('product_id', $product->id)
            ->selectRaw("
                SUM(CASE WHEN quantity_change > 0 THEN quantity_change ELSE 0 END) as total_in,
                SUM(CASE WHEN quantity_change < 0 THEN ABS(quantity_change) ELSE 0 END) as total_out,
                SUM(CASE WHEN movement_type IN ('checkout_sale', 'pos_sale', 'order_processing_deduct') THEN ABS(quantity_change) ELSE 0 END) as total_sold,
                SUM(CASE WHEN movement_type IN ('checkout_sale', 'pos_sale', 'order_processing_deduct') THEN ABS(quantity_change) * COALESCE(unit_price, 0) ELSE 0 END) as total_revenue
            ")
            ->first();

        return view('admin.inventory.product_stock_details', compact(
            'product',
            'variants',
            'warehouseStocks',
            'movements',
            'aggregates',
            'selectedVariantId'
        ));
    }

    /**
     * 7. DATE-WISE INVENTORY & COGS PROFIT REPORTS
     */
    public function reports(Request $request)
    {
        $period = $request->input('period', 'this_month');
        $now = Carbon::now();

        switch ($period) {
            case 'today':
                $startDate = $now->copy()->startOfDay()->toDateString();
                $endDate   = $now->copy()->endOfDay()->toDateString();
                $periodLabel = "Today (" . $now->format('d M Y') . ")";
                break;
            case 'yesterday':
                $startDate = $now->copy()->subDay()->startOfDay()->toDateString();
                $endDate   = $now->copy()->subDay()->endOfDay()->toDateString();
                $periodLabel = "Yesterday (" . Carbon::parse($startDate)->format('d M Y') . ")";
                break;
            case 'this_week':
                $startDate = $now->copy()->startOfWeek()->toDateString();
                $endDate   = $now->copy()->endOfWeek()->toDateString();
                $periodLabel = "This Week";
                break;
            case 'last_week':
                $startDate = $now->copy()->subWeek()->startOfWeek()->toDateString();
                $endDate   = $now->copy()->subWeek()->endOfWeek()->toDateString();
                $periodLabel = "Last Week";
                break;
            case 'last_month':
                $startDate = $now->copy()->subMonth()->startOfMonth()->toDateString();
                $endDate   = $now->copy()->subMonth()->endOfMonth()->toDateString();
                $periodLabel = "Last Month (" . Carbon::parse($startDate)->format('F Y') . ")";
                break;
            case 'this_year':
                $startDate = $now->copy()->startOfYear()->toDateString();
                $endDate   = $now->copy()->endOfYear()->toDateString();
                $periodLabel = "This Year ({$now->year})";
                break;
            case 'custom':
                $startDate = $request->input('start_date', $now->copy()->startOfMonth()->toDateString());
                $endDate   = $request->input('end_date', $now->toDateString());
                $periodLabel = "Custom (" . date('d M Y', strtotime($startDate)) . " - " . date('d M Y', strtotime($endDate)) . ")";
                break;
            case 'this_month':
            default:
                $startDate = $now->copy()->startOfMonth()->toDateString();
                $endDate   = $now->copy()->endOfMonth()->toDateString();
                $periodLabel = "This Month (" . $now->format('F Y') . ")";
                break;
        }

        $ledger = InventoryService::getOpeningAndClosingBalance(null, null, $startDate, $endDate);

        $reportQuery = DB::table('inventory_movements')
            ->join('products', 'inventory_movements.product_id', '=', 'products.id')
            ->leftJoin('product_variants', 'inventory_movements.variant_id', '=', 'product_variants.id')
            ->whereBetween('inventory_movements.created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->select(
                'products.id as product_id',
                'products.title as product_title',
                'products.product_code as parent_sku',
                'product_variants.id as variant_id',
                'product_variants.product_code as variant_sku',
                'product_variants.color',
                'product_variants.size',
                DB::raw("SUM(CASE WHEN inventory_movements.quantity_change > 0 THEN inventory_movements.quantity_change ELSE 0 END) as stock_in_qty"),
                DB::raw("SUM(CASE WHEN inventory_movements.quantity_change > 0 THEN inventory_movements.quantity_change * COALESCE(inventory_movements.unit_cost, 0) ELSE 0 END) as stock_in_val"),
                DB::raw("SUM(CASE WHEN inventory_movements.quantity_change < 0 THEN ABS(inventory_movements.quantity_change) ELSE 0 END) as stock_out_qty"),
                DB::raw("SUM(CASE WHEN inventory_movements.movement_type IN ('checkout_sale', 'pos_sale', 'order_processing_deduct') THEN ABS(inventory_movements.quantity_change) ELSE 0 END) as sales_qty"),
                DB::raw("SUM(CASE WHEN inventory_movements.movement_type IN ('checkout_sale', 'pos_sale', 'order_processing_deduct') THEN ABS(inventory_movements.quantity_change) * COALESCE(inventory_movements.unit_price, 0) ELSE 0 END) as sales_rev"),
                DB::raw("SUM(CASE WHEN inventory_movements.movement_type IN ('checkout_sale', 'pos_sale', 'order_processing_deduct') THEN ABS(inventory_movements.quantity_change) * COALESCE(inventory_movements.unit_cost, 0) ELSE 0 END) as sales_cogs")
            )
            ->groupBy(
                'products.id', 'products.title', 'products.product_code',
                'product_variants.id', 'product_variants.product_code',
                'product_variants.color', 'product_variants.size'
            );

        if ($request->get('export') === 'csv' || $request->get('export') === 'excel') {
            return $this->exportReportToCsv($reportQuery->get(), $ledger, $periodLabel);
        }

        $reportItems = $reportQuery->paginate(25)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'success'    => true,
                'ledger'     => $ledger,
                'table_html' => view('admin.inventory.partials.report_rows', ['items' => $reportItems])->render(),
                'pagination' => $reportItems->links('pagination::bootstrap-5')->render(),
            ]);
        }

        return view('admin.inventory.reports', compact('ledger', 'reportItems', 'period', 'periodLabel', 'startDate', 'endDate'));
    }

    /**
     * 8. AJAX PRODUCT & VARIANT SEARCH ENGINE (For Stock In / Stock Out Forms)
     */
    public function ajaxSearchProducts(Request $request)
    {
        $term = trim((string) $request->input('q', $request->input('search', '')));
        if (mb_strlen($term) < 1) {
            return response()->json([]);
        }

        $hasVariants = Schema::hasTable('product_variants');

        $products = DB::table('products')
            ->where('title', 'LIKE', "%{$term}%")
            ->orWhere('product_code', 'LIKE', "%{$term}%")
            ->orWhere('sku', 'LIKE', "%{$term}%")
            ->orWhere('id', $term)
            ->take(15)
            ->get();

        $results = [];

        foreach ($products as $p) {
            $isVariable = ($p->product_struct === 'variable');
            $variantsList = [];

            if ($isVariable && $hasVariants) {
                $vRows = DB::table('product_variants')
                    ->where('product_id', $p->id)
                    ->get();

                foreach ($vRows as $v) {
                    $attrParts = array_filter([$v->color, $v->size]);
                    $attrLabel = count($attrParts) > 0 ? implode(' / ', $attrParts) : ($v->name ?? ('Variant #' . $v->id));
                    $variantsList[] = [
                        'id'            => $v->id,
                        'title'         => $attrLabel,
                        'sku'           => $v->product_code ?? ($v->sku ?? 'N/A'),
                        'stock'         => (int) ($v->stock ?? 0),
                        'purchase_cost' => (float) ($v->purchase_cost ?? ($v->purchase_price ?? 0.00)),
                        'selling_price' => (float) ($v->regular_price ?? ($p->regular_price ?? 0.00)),
                    ];
                }
            }

            $results[] = [
                'id'            => $p->id,
                'title'         => $p->title,
                'sku'           => $p->product_code ?? ($p->sku ?? 'N/A'),
                'thumbnail'     => $this->resolveInventoryThumb($p->thumbnail ?? null),
                'product_struct'=> $p->product_struct ?? 'simple',
                'current_stock' => (int) ($p->stock ?? 0),
                'purchase_cost' => (float) ($p->purchase_cost ?? ($p->purchase_price ?? 0.00)),
                'selling_price' => (float) ($p->sale_price ?? ($p->regular_price ?? 0.00)),
                'variants'      => $variantsList,
            ];
        }

        return response()->json($results);
    }

    /**
     * 9. QUICK STOCK ADJUSTMENT ACTION
     */
    public function adjustStock(Request $request)
    {
        $request->validate([
            'product_id'    => 'required|exists:products,id',
            'variant_id'    => 'nullable|integer',
            'new_stock'     => 'required|integer|min:0',
            'purchase_cost' => 'nullable|numeric|min:0',
            'apply_wac'     => 'nullable|boolean',
            'warehouse_id'  => 'nullable|integer',
            'branch_id'     => 'nullable|integer',
            'movement_type' => 'required|string|in:adjustment,purchase_receive,damage_loss,inventory_audit',
            'notes'         => 'nullable|string|max:500',
        ]);

        try {
            $result = InventoryService::adjustStock(
                (int) $request->product_id,
                $request->filled('variant_id') ? (int) $request->variant_id : null,
                (int) $request->new_stock,
                $request->filled('purchase_cost') ? (float) $request->purchase_cost : null,
                $request->boolean('apply_wac'),
                $request->input('movement_type', 'adjustment'),
                'ADJ-' . strtoupper(uniqid()),
                'admin_manual_adjustment',
                Auth::id(),
                $request->notes ?? 'Manual stock adjustment applied from inventory dashboard',
                null,
                $request->filled('warehouse_id') ? (int) $request->warehouse_id : null,
                $request->filled('branch_id') ? (int) $request->branch_id : null
            );

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stock successfully adjusted.',
                    'data'    => $result,
                ]);
            }

            return back()->with('success', 'Stock successfully adjusted.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * CSV EXPORT HELPERS
     */
    protected function exportBalanceToCsv($query, $summary)
    {
        $items = $query->orderBy('product_id', 'desc')->get();
        $filename = "Inventory_Balance_Sheet_" . date('Y_m_d_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0",
        ];

        $columns = ['ID', 'Type', 'Product Title', 'Variant / Attributes', 'SKU', 'Available Stock', 'Unit Cost (BDT)', 'Selling Price', 'Inventory Purchase Valuation', 'Stock Status'];

        $callback = function () use ($items, $columns, $summary) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $columns);

            foreach ($items as $item) {
                $status = ($item->stock <= 0) ? 'OUT OF STOCK' : (($item->stock <= $item->alert_quantity) ? 'LOW STOCK' : 'IN STOCK');
                $itemVal = (float)$item->stock * (float)$item->purchase_cost;
                fputcsv($file, [
                    '#' . $item->product_id,
                    strtoupper($item->item_type),
                    $item->product_title,
                    $item->variant_attributes ?? '—',
                    $item->sku ?? '—',
                    $item->stock,
                    number_format((float)$item->purchase_cost, 2, '.', ''),
                    number_format((float)$item->selling_price, 2, '.', ''),
                    number_format($itemVal, 2, '.', ''),
                    $status
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, [
                'TOTAL SUMMARY', '', '', '', '',
                number_format($summary->total_units ?? 0),
                '', '',
                number_format($summary->total_purchase_value ?? 0, 2, '.', '') . ' BDT',
                ''
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function exportMovementsToCsv($query, $totalAdditions, $totalDeductions, $costImpact)
    {
        $movements = $query->orderBy('inventory_movements.id', 'desc')->get();
        $filename = "Inventory_Movements_Ledger_" . date('Y_m_d_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0",
        ];

        $columns = [
            'Log ID', 'Date & Time', 'Product ID', 'Product Title', 'Variant / Attributes', 'SKU',
            'Movement Type', 'Qty Before', 'Qty Change', 'Qty After', 'Unit Cost (BDT)',
            'Total Cost Impact (BDT)', 'Reference / Invoice', 'Source', 'Initiated By', 'Notes',
        ];

        $callback = function () use ($movements, $columns, $totalAdditions, $totalDeductions, $costImpact) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $columns);

            foreach ($movements as $m) {
                $variantInfo = '—';
                $parts = array_filter([$m->variant_color, $m->variant_size]);
                if (count($parts) > 0) $variantInfo = implode(' / ', $parts);

                $qtyChangeFormatted = ($m->quantity_change > 0 ? '+' : '') . $m->quantity_change;
                $rowCostImpact = abs((int) $m->quantity_change) * (float) ($m->unit_cost ?? 0);

                fputcsv($file, [
                    '#' . $m->id,
                    date('d M Y, h:i A', strtotime($m->created_at)),
                    '#' . $m->product_id,
                    $m->product_title ?? 'Product #' . $m->product_id,
                    $variantInfo,
                    $m->sku ?? ($m->parent_code ?? '—'),
                    strtoupper(str_replace('_', ' ', $m->movement_type)),
                    $m->quantity_before,
                    $qtyChangeFormatted,
                    $m->quantity_after,
                    number_format((float) ($m->unit_cost ?? 0), 2, '.', ''),
                    number_format($rowCostImpact, 2, '.', ''),
                    $m->reference_id ?? '—',
                    $m->source ?? 'system',
                    $m->creator_name ?? 'System Automated',
                    $m->notes ?? '',
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, [
                'LEDGER SUMMARY TOTALS', '', '', '', '', '', '',
                'Total Added: +' . $totalAdditions,
                'Total Deducted: -' . $totalDeductions,
                'Net Change: ' . ($totalAdditions - $totalDeductions),
                '',
                'Total Valuation Impact: ' . number_format($costImpact, 2, '.', '') . ' BDT',
                '', '', '', '',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function exportReportToCsv($items, $ledger, $periodLabel)
    {
        $filename = "Inventory_Audit_Report_" . date('Y_m_d_His') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0",
        ];

        $columns = ['Product ID', 'Product Title', 'Variant', 'SKU', 'Stock In (Qty)', 'Stock In Value (BDT)', 'Stock Out (Qty)', 'Sales Qty', 'Revenue (BDT)', 'COGS (BDT)', 'Gross Profit (BDT)'];

        $callback = function () use ($items, $columns, $ledger, $periodLabel) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['INVENTORY PERIOD REPORT:', $periodLabel]);
            fputcsv($file, ['Opening Stock:', $ledger['opening_qty'] . ' Units', 'Opening Valuation:', number_format($ledger['opening_value'], 2) . ' BDT']);
            fputcsv($file, ['Total Stock In:', $ledger['stock_in_qty'] . ' Units', 'Stock In Valuation:', number_format($ledger['stock_in_value'], 2) . ' BDT']);
            fputcsv($file, ['Total Stock Out:', $ledger['stock_out_qty'] . ' Units', 'Stock Out Valuation:', number_format($ledger['stock_out_value'], 2) . ' BDT']);
            fputcsv($file, ['Total Sales Revenue:', number_format($ledger['sales_revenue'], 2) . ' BDT', 'Total COGS:', number_format($ledger['sales_cogs'], 2) . ' BDT', 'Gross Profit:', number_format($ledger['gross_profit'], 2) . ' BDT']);
            fputcsv($file, ['Closing Stock:', $ledger['closing_qty'] . ' Units', 'Closing Valuation:', number_format($ledger['closing_value'], 2) . ' BDT']);
            fputcsv($file, []);

            fputcsv($file, $columns);

            foreach ($items as $item) {
                $profit = max(0, (float)$item->sales_rev - (float)$item->sales_cogs);
                $varText = trim(($item->color ?? '') . ' ' . ($item->size ?? ''));
                fputcsv($file, [
                    '#' . $item->product_id,
                    $item->product_title,
                    !empty($varText) ? $varText : 'Simple Product',
                    $item->variant_sku ?? ($item->parent_sku ?? '—'),
                    $item->stock_in_qty,
                    number_format((float)$item->stock_in_val, 2, '.', ''),
                    $item->stock_out_qty,
                    $item->sales_qty,
                    number_format((float)$item->sales_rev, 2, '.', ''),
                    number_format((float)$item->sales_cogs, 2, '.', ''),
                    number_format($profit, 2, '.', '')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}